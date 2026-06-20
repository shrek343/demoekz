<?php
// ============================================================
//  ОФОРМЛЕНИЕ ЗАЯВКИ
//  Варианты выбора (карточка / время / оплата) заданы прямо в этом файле.
//  Дата — в формате ДД.ММ.ГГГГ. Новая заявка получает статус "Новая".
// ============================================================

require_once __DIR__ . '/includes/db.php';    // $pdo — соединение с базой
require_once __DIR__ . '/includes/auth.php';  // функции авторизации + session_start()
requireLogin();                               // гостя выкидываем на вход — заявку оставляет только вошедший

// ✏️ СПИСКИ ДЛЯ ПРОВЕРКИ. Названия должны совпадать с тем, что
//    написано в <option> ниже (и с карточками на главной).
// Это «белые списки»: только эти значения сервер примет как допустимые.
$cards    = ['Название карточки 1', 'Название карточки 2', 'Название карточки 3'];
$times    = ['Утро', 'День', 'Вечер'];
$payments = ['Предоплата по QR-коду', 'Оплата картой МИР', 'Постоплата в офисе'];

$error = '';                                  // переменная под текст ошибки (пустая = ошибок нет)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  // обрабатываем, только если форма реально отправлена

    $object  = $_POST['object'] ?? '';        // что выбрал пользователь; ?? '' — если поля нет, пустая строка
    $date    = trim($_POST['start_date'] ?? '');  // дата; trim — срезаем пробелы по краям
    $time    = $_POST['start_time'] ?? '';    // время
    $payment = $_POST['payment'] ?? '';       // способ оплаты

    // --- ПРОВЕРКИ ПО ЦЕПОЧКЕ (if/elseif): первая же ошибка прерывает остальные ---
    // in_array(..., $cards, true) — есть ли значение в списке; третий аргумент true = строгое сравнение (тип тоже важен)
    if (!in_array($object, $cards, true))                 $error = 'Выберите вариант из списка';
    // preg_match с регуляркой ^\d{2}\.\d{2}\.\d{4}$ — ровно ДД.ММ.ГГГГ (2 цифры . 2 цифры . 4 цифры)
    elseif (!preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) $error = 'Дата в формате ДД.ММ.ГГГГ';
    elseif (!in_array($time, $times, true))               $error = 'Выберите время';
    elseif (!in_array($payment, $payments, true))         $error = 'Выберите способ оплаты';
    else {
        // --- ВСЁ ПРОШЛО ПРОВЕРКУ -> вставляем заявку в базу ---
        // ? — заглушки (подготовленный запрос, защита от SQL-инъекций). Статус не указываем — база сама ставит "Новая"
        $stmt = $pdo->prepare('INSERT INTO applications (user_id,object,start_date,start_time,payment) VALUES (?,?,?,?,?)');
        $stmt->execute([$_SESSION['user_id'], $object, $date, $time, $payment]); // подставляем значения по порядку
        header('Location: cabinet.php?created=1'); // уводим в кабинет, ?created=1 — чтобы показать «Заявка отправлена!»
        exit;                                      // обязательно стоп после header
    }
}

$active = 'app';                              // активный пункт меню
require_once __DIR__ . '/includes/header.php'; // шапка сайта
?>

<h1 class="h2 mb-3">Новая заявка</h1>

<!-- если в $error есть текст — красное сообщение об ошибке; e() экранирует HTML (защита от XSS) -->
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<form method="POST">                          <!-- форма отправляется на этот же файл методом POST -->

  <!-- ✏️ Поле выбора карточки. Подписи option должны совпадать с массивом $cards выше. -->
  <div class="mb-2">
    <label class="form-label">Что вы выбираете</label>
    <select name="object" class="form-select" required>      <!-- required — нельзя отправить, не выбрав -->
      <option value="">— выберите —</option>                 <!-- пустой value: подсказка, не проходит проверку in_array -->
      <option>Название карточки 1</option>                   <!-- без value: отправится сам текст option -->
      <option>Название карточки 2</option>
      <option>Название карточки 3</option>
    </select>
  </div>

  <!-- Дата в формате ДД.ММ.ГГГГ -->
  <div class="mb-2">
    <label class="form-label">Дата начала</label>
    <!-- pattern — браузерная проверка формата; настоящая всё равно на сервере (preg_match выше) -->
    <input type="text" name="start_date" class="form-control" placeholder="ДД.ММ.ГГГГ" pattern="\d{2}\.\d{2}\.\d{4}" required>
  </div>

  <!-- ✏️ Время (подписи должны совпадать с массивом $times выше) -->
  <div class="mb-2">
    <label class="form-label">Предпочтительное время начала</label>
    <select name="start_time" class="form-select" required>
      <option value="">— выберите —</option>
      <option>Утро</option>
      <option>День</option>
      <option>Вечер</option>
    </select>
  </div>

  <!-- ✏️ Способ оплаты (подписи должны совпадать с массивом $payments выше) -->
  <div class="mb-3">
    <label class="form-label">Способ оплаты</label>
    <select name="payment" class="form-select" required>
      <option value="">— выберите —</option>
      <option>Предоплата по QR-коду</option>
      <option>Оплата картой МИР</option>
      <option>Постоплата в офисе</option>
    </select>
  </div>

  <button class="btn btn-green w-100">Отправить заявку</button>  <!-- кнопка отправки, w-100 — на всю ширину -->
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>  <!-- подвал сайта + скрипты Bootstrap -->