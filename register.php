<?php
// ============================================================
//  РЕГИСТРАЦИЯ
//  Поля: логин, пароль, ФИО, телефон, e-mail.
//  Логин: латиница+цифры, минимум 6, уникальный. Пароль: минимум 8.
// ============================================================

require_once __DIR__ . '/includes/db.php';    // $pdo — соединение с базой
require_once __DIR__ . '/includes/auth.php';  // авторизация + session_start() (requireLogin тут НЕ нужен — это гость)

$errors = [];                                 // массив ошибок ПО ПОЛЯМ (ключ = имя поля), пустой = всё ок
// заготовка прежних значений: при ошибке вернём их в форму, чтобы не вводить заново
$old = ['login'=>'','fio'=>'','phone'=>'','email'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  // обрабатываем, только если форма отправлена

    $login    = trim($_POST['login'] ?? '');    // логин; trim — срезаем пробелы
    $password = $_POST['password'] ?? '';       // пароль (НЕ трогаем пробелы и не сохраняем в $old)
    $fio      = trim($_POST['fio'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    // compact — быстро собрать массив из переменных: ['login'=>$login, 'fio'=>$fio, ...]. Пароля тут нет специально
    $old = compact('login','fio','phone','email');

    // --- ПРОВЕРКИ КАЖДОГО ПОЛЯ (НЕ обрываемся на первой — собираем ВСЕ ошибки) ---
    // регулярка ^[A-Za-z0-9]{6,}$ — только латиница и цифры, длиной от 6 символов
    if (!preg_match('/^[A-Za-z0-9]{6,}$/', $login)) $errors['login'] = 'Логин: латиница и цифры, минимум 6';
    if (strlen($password) < 8) $errors['password'] = 'Пароль: минимум 8 символов';  // длина пароля
    if ($fio === '') $errors['fio'] = 'Укажите ФИО';                                 // ФИО не пустое
    if ($phone === '') $errors['phone'] = 'Укажите телефон';                         // телефон не пустой
    // filter_var с FILTER_VALIDATE_EMAIL — встроенная проверка корректности e-mail
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Неверный e-mail';

    // --- ПРОВЕРКА УНИКАЛЬНОСТИ ЛОГИНА ---
    // лезем в базу, только если формат логина уже верный (нет смысла проверять заведомо плохой)
    if (!isset($errors['login'])) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE login = ?');
        $stmt->execute([$login]);
        if ($stmt->fetch()) $errors['login'] = 'Такой логин уже занят'; // нашли запись -> логин занят
    }

    // --- ЕСЛИ ОШИБОК НЕТ — СОЗДАЁМ ПОЛЬЗОВАТЕЛЯ ---
    if (!$errors) {                            // пустой массив = false -> ошибок нет
        // password_hash — превращаем пароль в безопасный хэш (в базе НИКОГДА не храним пароль открыто)
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (login,password,fio,phone,email) VALUES (?,?,?,?,?)');
        $stmt->execute([$login, $hash, $fio, $phone, $email]); // в базу кладём ХЭШ, а не сам пароль
        header('Location: login.php?registered=1'); // уводим на вход, ?registered=1 -> покажет «Регистрация успешна»
        exit;                                  // обязательный стоп после header
    }
}

$active = 'reg';                              // активный пункт меню
require_once __DIR__ . '/includes/header.php'; // шапка сайта
?>

<h1 class="h2 mb-3">Регистрация</h1>

<!-- novalidate — отключаем встроенную проверку браузера, чтобы работала НАША (PHP + validation.js) -->
<form method="POST" id="regForm" novalidate>

  <div class="mb-2">
    <label class="form-label">Логин</label>
    <!-- value подставляет прежний ввод (sticky form); e() экранирует HTML (защита от XSS) -->
    <input type="text" name="login" class="form-control" value="<?= e($old['login']) ?>">
    <!-- сюда выводим ошибку именно этого поля; ?? '' — если ошибки нет, пусто -->
    <div class="text-danger text-help err"><?= $errors['login'] ?? '' ?></div>
  </div>

  <div class="mb-2">
    <label class="form-label">Пароль</label>
    <!-- у пароля НЕТ value — прежнее значение намеренно не возвращаем (безопасность) -->
    <input type="password" name="password" class="form-control">
    <div class="text-danger text-help err"><?= $errors['password'] ?? '' ?></div>
  </div>

  <div class="mb-2">
    <label class="form-label">ФИО</label>
    <input type="text" name="fio" class="form-control" value="<?= e($old['fio']) ?>">
    <div class="text-danger text-help err"><?= $errors['fio'] ?? '' ?></div>
  </div>

  <div class="mb-2">
    <label class="form-label">Телефон</label>
    <!-- placeholder — серая подсказка формата внутри поля -->
    <input type="text" name="phone" class="form-control" placeholder="+7 (___) ___-__-__" value="<?= e($old['phone']) ?>">
    <div class="text-danger text-help err"><?= $errors['phone'] ?? '' ?></div>
  </div>

  <div class="mb-3">
    <label class="form-label">E-mail</label>
    <input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>">
    <div class="text-danger text-help err"><?= $errors['email'] ?? '' ?></div>
  </div>

  <button class="btn btn-sea w-100">Зарегистрироваться</button>
</form>

<p class="text-center mt-3"><a href="login.php">Уже зарегистрированы? Вход</a></p>
<script src="js/validation.js"></script>  <!-- клиентская проверка в реальном времени (до отправки на сервер) -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>  <!-- подвал сайта -->