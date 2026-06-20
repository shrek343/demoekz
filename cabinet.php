<?php
// ============================================================
//  ЛИЧНЫЙ КАБИНЕТ: история заявок + слайдер + отзывы.
//  Отзыв доступен, только когда статус НЕ начальный (админ его сменил).
// ============================================================

require_once __DIR__ . '/includes/db.php';    // подключаем $pdo — соединение с базой
require_once __DIR__ . '/includes/auth.php';  // функции авторизации + session_start()
requireLogin();                               // если пользователь НЕ вошёл — выкидываем на login.php (стоп для гостей)

// ✏️ СТАТУСЫ ЗАЯВКИ. Первый ("Новая") — начальный, его ставит база
//    автоматически. Можешь переименовать средний и последний под свою тему.
//    Эти же три статуса должны стоять в admin.php.
$statuses = ['Новая', 'В работе', 'Завершено']; // массив возможных статусов; порядок важен (первый = стартовый)

$initialStatus = $statuses[0];   // начальный статус ("Новая") — берём первый элемент массива
$msg = '';                       // переменная под сообщение пользователю (пустая = сообщений нет)

// --- СОХРАНЕНИЕ ОТЗЫВА ---
// Срабатывает, только если форма отправлена (POST) И в ней есть id заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'])) {

    $appId = (int)$_POST['application_id'];   // id заявки; (int) — приводим к числу (защита: отбросит мусор)
    $text  = trim($_POST['review'] ?? '');    // текст отзыва; trim — срезаем пробелы по краям

    // Проверяем, что такая заявка есть И принадлежит ИМЕННО этому пользователю (нельзя комментить чужое)
    $stmt = $pdo->prepare('SELECT status FROM applications WHERE id = ? AND user_id = ?');
    $stmt->execute([$appId, $_SESSION['user_id']]);
    $app = $stmt->fetch();                    // получаем строку с её статусом (или false, если не нашли/чужая)

    // ТРОЙНОЕ УСЛОВИЕ: заявка найдена И статус уже сменён И текст не пустой
    if ($app && $app['status'] !== $initialStatus && $text !== '') {
        // INSERT IGNORE — вставит отзыв, но молча пропустит, если он уже есть (защита от дубля)
        $stmt = $pdo->prepare('INSERT IGNORE INTO reviews (application_id, text) VALUES (?, ?)');
        $stmt->execute([$appId, $text]);
        $msg = 'Спасибо за отзыв!';           // успех
    } else {
        // сюда попадём, если статус ещё начальный, текст пустой или заявка чужая
        $msg = 'Отзыв можно оставить только после смены статуса администратором.';
    }
}

// --- ЗАЯВКИ ПОЛЬЗОВАТЕЛЯ + ОТЗЫВ ---
// LEFT JOIN: берём все заявки, и если к заявке есть отзыв — подцепляем его текст; нет — будет NULL
$stmt = $pdo->prepare('SELECT a.*, r.text AS review_text FROM applications a
                       LEFT JOIN reviews r ON r.application_id = a.id
                       WHERE a.user_id = ? ORDER BY a.id DESC');  // ORDER BY id DESC — свежие заявки сверху
$stmt->execute([$_SESSION['user_id']]);       // подставляем id текущего пользователя
$apps = $stmt->fetchAll();                    // забираем ВСЕ строки в массив (fetchAll, не fetch)

$active = 'cab';                              // активный пункт меню (подсветка «Кабинет»)
require_once __DIR__ . '/includes/header.php'; // выводим шапку сайта
?>

<!-- Слайдер в кабинете (Модуль 2). Тот же carousel, что на главной, но без точек и стрелок — только автопрокрутка -->
<div id="cabSlider" class="carousel slide rounded shadow-sm mb-3" data-bs-ride="carousel" data-bs-interval="3000">
  <div class="carousel-inner rounded">         <!-- контейнер со слайдами -->
    <div class="carousel-item active"><img src="img/slide1.jpg" class="slide-img" alt=""></div> <!-- active — стартовый -->
    <div class="carousel-item"><img src="img/slide2.jpg" class="slide-img" alt=""></div>
    <div class="carousel-item"><img src="img/slide3.jpg" class="slide-img" alt=""></div>
    <div class="carousel-item"><img src="img/slide4.jpg" class="slide-img" alt=""></div>
  </div>
</div>

<h1 class="h2 mb-3">Мои заявки</h1>

<!-- если пришли сюда после создания заявки (?created=1) — зелёное сообщение -->
<?php if (isset($_GET['created'])): ?><div class="alert alert-success">Заявка отправлена!</div><?php endif; ?>
<!-- если в $msg есть текст (про отзыв) — синее информационное сообщение; e() экранирует HTML (защита от XSS) -->
<?php if ($msg): ?><div class="alert alert-info"><?= e($msg) ?></div><?php endif; ?>

<?php if (!$apps): ?>
  <!-- ВЕТКА 1: заявок нет вообще — показываем приглашение оставить первую -->
  <p class="text-muted">Заявок пока нет. <a href="application.php">Оставить заявку</a></p>
<?php else: foreach ($apps as $a): ?>
  <!-- ВЕТКА 2: заявки есть — крутим цикл, по карточке на каждую заявку $a -->
  <div class="card card-anim p-3 mb-2">

    <!-- шапка карточки: слева название объекта, справа цветной бейдж со статусом.
         d-flex + justify-content-between — расталкиваем по краям строки -->
    <div class="d-flex justify-content-between">
      <strong><?= e($a['object']) ?></strong>
      <!-- badgeClass() выбирает цвет бейджа по статусу (новая/в работе/завершено) -->
      <span class="badge <?= badgeClass($a['status'], $statuses) ?>"><?= e($a['status']) ?></span>
    </div>

    <!-- детали заявки мелким серым текстом: дата, время начала, способ оплаты -->
    <div class="small text-muted">Начало: <?= e($a['start_date']) ?> (<?= e($a['start_time']) ?>) · <?= e($a['payment']) ?></div>

    <!-- ТРИ ВАРИАНТА отображения блока отзыва: -->
    <?php if ($a['review_text']): ?>
      <!-- 1) отзыв уже оставлен — просто показываем его текст -->
      <div class="mt-2 small"><em>Ваш отзыв:</em> <?= e($a['review_text']) ?></div>

    <?php elseif ($a['status'] !== $initialStatus): ?>
      <!-- 2) отзыва ещё нет, НО статус уже сменён — показываем форму для отзыва -->
      <form method="POST" class="mt-2">
        <!-- hidden-поле: передаём id заявки, чтобы PHP знал, к какой заявке отзыв -->
        <input type="hidden" name="application_id" value="<?= $a['id'] ?>">
        <textarea name="review" class="form-control mb-2" rows="2" placeholder="Ваш отзыв об услуге" required></textarea>
        <button class="btn btn-sm btn-green">Оставить отзыв</button>
      </form>

    <?php else: ?>
      <!-- 3) статус ещё начальный — отзыв пока недоступен, объясняем почему -->
      <div class="mt-2 text-help">Отзыв станет доступен после обработки заявки.</div>
    <?php endif; ?>
  </div>
<?php endforeach; endif; ?>  <!-- закрываем и цикл foreach, и условие if -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>  <!-- подвал + скрипты Bootstrap (без них слайдер не поедет) -->