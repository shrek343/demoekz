<?php
// ============================================================
//  АДМИН-ПАНЕЛЬ (Admin26 / Demo20)
//  Все заявки + смена статуса.
//  Доп. инструменты (Модуль 2): фильтр, сортировка, пагинация, тост.
// ============================================================

require_once __DIR__ . '/includes/db.php';    // $pdo — соединение с базой
require_once __DIR__ . '/includes/auth.php';  // авторизация + session_start()
requireAdmin();                               // пускаем ТОЛЬКО админа; обычного юзера/гостя — отворот

// ✏️ СТАТУСЫ ЗАЯВКИ — те же три, что и в cabinet.php.
$statuses = ['Новая', 'В работе', 'Завершено']; // порядок и тексты обязаны совпадать с кабинетом
$allowed  = $statuses;   // допустимые статусы (отдельное имя — для наглядности проверок ниже)

// --- СМЕНА СТАТУСА -> редирект (сохраняем фильтры, показываем тост) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id     = (int)($_POST['id'] ?? 0);       // id заявки; (int) — приводим к числу (защита от мусора)
    $status = $_POST['status'] ?? '';         // новый статус из выпадающего списка

    // обновляем, только если статус из белого списка (нельзя записать произвольное значение)
    if (in_array($status, $allowed, true)) {
        $stmt = $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?'); // ? — заглушки (защита от инъекций)
        $stmt->execute([$status, $id]);
    }

    // собираем строку запроса, чтобы ВЕРНУТЬ админа на ту же страницу/фильтр + поднять флаг тоста
    // http_build_query превращает массив в строку вида status=...&sort=...&page=...&updated=1
    $q = http_build_query([
        'status' => $_POST['f_status'] ?? '', // какой фильтр был выбран (его прятали в hidden-поле)
        'sort'   => $_POST['f_sort'] ?? 'desc',
        'page'   => $_POST['f_page'] ?? 1,
        'updated'=> 1,                        // флаг «показать тост ✓»
    ]);
    header('Location: admin.php?' . $q);      // POST/Redirect/GET: уводим на GET, чтобы F5 не переотправил форму
    exit;                                     // обязательный стоп после header
}

// --- ФИЛЬТР / СОРТИРОВКА / СТРАНИЦА (читаем из GET) ---
$fStatus = $_GET['status'] ?? '';             // выбранный фильтр статуса (пусто = все)
// направление сортировки: разрешаем строго ASC или DESC (нельзя подставить что попало в SQL)
$sort    = (($_GET['sort'] ?? 'desc') === 'asc') ? 'ASC' : 'DESC';
$page    = max(1, (int)($_GET['page'] ?? 1)); // номер страницы; max(1, ...) — не меньше первой
$perPage = 10;                                // сколько заявок на одной странице
$offset  = ($page - 1) * $perPage;            // сколько строк пропустить (стр.1 -> 0, стр.2 -> 10, ...)

// --- УСЛОВИЕ ФИЛЬТРА (только если статус валиден) ---
$where = ''; $params = [];                    // по умолчанию условия нет, параметров нет
if (in_array($fStatus, $allowed, true)) {     // если фильтр из белого списка
    $where = 'WHERE a.status = ?';            // добавляем условие
    $params[] = $fStatus;                     // и значение для заглушки
}

// --- СЧИТАЕМ ОБЩЕЕ ЧИСЛО ЗАЯВОК (для пагинации) ---
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications a $where"); // COUNT(*) — сколько всего подходит под фильтр
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();           // fetchColumn — берём одно число (само количество)
$pages = max(1, (int)ceil($total / $perPage)); // сколько всего страниц; ceil — округляем ВВЕРХ (11 заявок -> 2 страницы)

// --- ДОСТАЁМ ЗАЯВКИ ТЕКУЩЕЙ СТРАНИЦЫ ---
// JOIN users — чтобы вместе с заявкой получить ФИО автора; LIMIT/OFFSET — выдаём только нужный кусок
$sql = "SELECT a.*, u.fio FROM applications a JOIN users u ON u.id = a.user_id
        $where ORDER BY a.id $sort LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$apps = $stmt->fetchAll();                    // все строки страницы в массив

$active = 'admin';                            // активный пункт меню
require_once __DIR__ . '/includes/header.php'; // шапка сайта
?>

<h1 class="h2 mb-3">Заявки (админ)</h1>

<!-- ФИЛЬТР по статусу + СОРТИРОВКА. Метод GET — параметры видны в адресе, ссылку можно сохранить/обновить -->
<form method="GET" class="row g-2 mb-3">
  <div class="col-7">
    <select name="status" class="form-select form-select-sm">
      <option value="">Все статусы</option>
      <!-- перебираем статусы; если он совпал с текущим фильтром — помечаем selected (остаётся выбранным после OK) -->
      <?php foreach ($allowed as $s): ?><option <?= $fStatus===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-3">
    <select name="sort" class="form-select form-select-sm">
      <!-- value desc/asc отправляется на сервер; selected — чтобы показать текущий выбор -->
      <option value="desc" <?= $sort==='DESC'?'selected':'' ?>>Новые</option>
      <option value="asc"  <?= $sort==='ASC'?'selected':'' ?>>Старые</option>
    </select>
  </div>
  <div class="col-2"><button class="btn btn-sm btn-sea w-100">OK</button></div>  <!-- применить фильтр -->
</form>

<?php if (!$apps): ?>
  <!-- под фильтр ничего не попало -->
  <p class="text-muted">Заявок нет.</p>
<?php else: foreach ($apps as $a): ?>
  <!-- по карточке на каждую заявку -->
  <div class="card card-anim p-3 mb-2">

    <!-- шапка: слева номер заявки и ФИО автора, справа цветной бейдж статуса -->
    <div class="d-flex justify-content-between">
      <strong>#<?= $a['id'] ?> <?= e($a['fio']) ?></strong>
      <span class="badge <?= badgeClass($a['status'], $statuses) ?>"><?= e($a['status']) ?></span>
    </div>

    <!-- детали заявки одной строкой: что выбрано · дата (время) · оплата -->
    <div class="small text-muted mb-2"><?= e($a['object']) ?> · <?= e($a['start_date']) ?> (<?= e($a['start_time']) ?>) · <?= e($a['payment']) ?></div>

    <!-- смена статуса: выбрал в списке -> форма сразу отправилась (onchange), сохранять кнопкой не нужно -->
    <form method="POST" class="d-flex gap-2">
      <input type="hidden" name="id" value="<?= $a['id'] ?>">              <!-- какой заявке меняем статус -->
      <!-- прячем текущие фильтры, чтобы после редиректа вернуться на ту же страницу/фильтр -->
      <input type="hidden" name="f_status" value="<?= e($fStatus) ?>">
      <input type="hidden" name="f_sort" value="<?= strtolower($sort) ?>">
      <input type="hidden" name="f_page" value="<?= $page ?>">
      <!-- onchange="this.form.submit()" — как только выбрали другой статус, форма уходит сама -->
      <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
        <!-- текущий статус заявки помечаем selected -->
        <?php foreach ($allowed as $s): ?><option <?= $a['status']===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?>
      </select>
    </form>
  </div>
<?php endforeach; endif; ?>

<!-- ПОСТРАНИЧНАЯ НАВИГАЦИЯ. Показываем, только если страниц больше одной -->
<?php if ($pages > 1): ?>
  <nav><ul class="pagination pagination-sm justify-content-center">
    <!-- цикл от 1 до $pages: рисуем кнопку на каждую страницу -->
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <li class="page-item <?= $p===$page?'active':'' ?>">   <!-- текущую страницу выделяем -->
        <!-- ссылка тащит с собой фильтр и сортировку, чтобы при листании они не сбрасывались -->
        <!-- urlencode — безопасно вставляем значение в адрес (например, статус с пробелом) -->
        <a class="page-link" href="?status=<?= urlencode($fStatus) ?>&sort=<?= strtolower($sort) ?>&page=<?= $p ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>
  </ul></nav>
<?php endif; ?>

<!-- ВСПЛЫВАЮЩЕЕ УВЕДОМЛЕНИЕ (тост). Показываем, только если пришли с ?updated=1 (после смены статуса) -->
<?php if (isset($_GET['updated'])): ?>
  <!-- position-fixed top-0 end-0 — закрепляем в правом верхнем углу -->
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="okToast" class="toast text-bg-success"><div class="toast-body">Статус обновлён ✓</div></div>
  </div>
  <script>
    // как только страница загрузилась — запускаем тост через Bootstrap; delay 2500 = исчезнет через 2.5 сек
    document.addEventListener('DOMContentLoaded', function () {
      new bootstrap.Toast(document.getElementById('okToast'), { delay: 2500 }).show();
    });
  </script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>  <!-- подвал + скрипты Bootstrap (нужны для тоста!) -->