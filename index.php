<?php
// ============================================================
//  ГЛАВНАЯ. Слайдер (4 фото, авто 3с) + описание + карточки + контакты.
//  ВСЕ ТЕКСТЫ МЕНЯЮТСЯ ПРЯМО ЗДЕСЬ, в этом файле (ищи пометки ✏️).
// ============================================================

require_once __DIR__ . '/includes/header.php'; // подключаем общую шапку: <head>, Bootstrap, меню
$active = 'home';                               // помечаем активный пункт меню (подсветка «Главная»)
?>

<!-- СЛАЙДЕР: 4 изображения, автопереключение каждые 3 секунды.
     ✏️ Замени файлы img/slide1.jpg … slide4.jpg на свои фото. -->
<!-- id="slider" — имя слайдера, на него ссылаются все кнопки ниже.
     data-bs-ride="carousel" — запустить автопрокрутку; data-bs-interval="3000" — интервал 3000 мс = 3 сек -->
<div id="slider" class="carousel slide rounded shadow-sm mb-3" data-bs-ride="carousel" data-bs-interval="3000">

  <!-- ТОЧКИ-ИНДИКАТОРЫ внизу слайдера (кружочки). По клику прыгаем на нужный слайд -->
  <div class="carousel-indicators">
    <!-- data-bs-slide-to="0" — на какой слайд перейти (нумерация с 0); class="active" — текущий выделен -->
    <button data-bs-target="#slider" data-bs-slide-to="0" class="active"></button>
    <button data-bs-target="#slider" data-bs-slide-to="1"></button>
    <button data-bs-target="#slider" data-bs-slide-to="2"></button>
    <button data-bs-target="#slider" data-bs-slide-to="3"></button>
  </div>

  <!-- САМИ СЛАЙДЫ. carousel-inner — контейнер с картинками -->
  <div class="carousel-inner rounded">
    <!-- carousel-item — один слайд; класс active обязателен ровно у ОДНОГО (стартовый, виден первым) -->
    <div class="carousel-item active"><img src="img/slide1.jpg" class="slide-img" alt=""></div>
    <div class="carousel-item"><img src="img/slide2.jpg" class="slide-img" alt=""></div>
    <div class="carousel-item"><img src="img/slide3.jpg" class="slide-img" alt=""></div>
    <div class="carousel-item"><img src="img/slide4.jpg" class="slide-img" alt=""></div>
  </div>

  <!-- СТРЕЛКИ по бокам. prev — назад, next — вперёд; span внутри рисует значок стрелки -->
  <button class="carousel-control-prev" data-bs-target="#slider" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
  <button class="carousel-control-next" data-bs-target="#slider" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
</div>

<!-- ✏️ НАЗВАНИЕ И ОПИСАНИЕ -->
<h1 class="h2 mb-2">Название вашей компании</h1>          <!-- заголовок страницы; класс h2 — размер шрифта -->
<p class="text-muted">Описание вашей экскурсии</p>        <!-- text-muted — приглушённый серый текст -->

<!-- УМНАЯ КНОПКА: если пользователь вошёл (isLoggedIn() == true) -> ведём на форму заявки,
     иначе (гость) -> отправляем сначала зарегистрироваться -->
<a href="<?= isLoggedIn() ? 'application.php' : 'register.php' ?>" class="btn btn-sea w-100 mb-3">Оставить заявку</a>

<!-- КАРТОЧКИ ТОВАРА (3 шт).
     ✏️ Поменяй фото (img/card1.png …) и подписи под ними.
     Подписи должны совпадать с вариантами в форме заявки (application.php). -->
<!-- row — строка сетки Bootstrap; g-2 — отступы между колонками -->
<div class="row g-2 mb-3">
  <!-- col-4 — ширина 1/3 (12/3=4), поэтому в строку влезает ровно 3 карточки -->
  <div class="col-4 mb-2">
    <!-- card-anim — анимация при наведении; text-center — текст по центру; h-100 — высота на весь столбец (чтобы карточки были вровень) -->
    <div class="card card-anim text-center p-2 h-100">
      <img src="img/card1.png" class="card-photo" alt="">
      <div class="text-help mt-1">Название карточки 1</div>   <!-- подпись под фото; mt-1 — отступ сверху -->
    </div>
  </div>
  <div class="col-4 mb-2">
    <div class="card card-anim text-center p-2 h-100">
      <img src="img/card2.png" class="card-photo" alt="">
      <div class="text-help mt-1">Название карточки 2</div>
    </div>
  </div>
  <div class="col-4 mb-2">
    <div class="card card-anim text-center p-2 h-100">
      <img src="img/card3.png" class="card-photo" alt="">
      <div class="text-help mt-1">Название карточки 3</div>
    </div>
  </div>
</div>

<!-- ✏️ КОНТАКТЫ -->
<div class="card card-anim p-3 mb-2">                <!-- p-3 — внутренний отступ; mb-2 — отступ снизу -->
  <h3>Контакты</h3>
  <p class="mb-1 small">📍 г. Москва, ул. Большая Ордынка, д. 15</p>  <!-- small — мелкий шрифт -->
  <p class="mb-0 small">📞 +7 (495) 123-45-67</p>                     <!-- mb-0 — без отступа снизу (последний абзац) -->
</div>

<!-- ✏️ СПОСОБЫ ОПЛАТЫ -->
<div class="card card-anim p-3">
  <h3>Способы оплаты</h3>
  <ul class="small mb-0">                            <!-- маркированный список -->
    <li>Предоплата по QR-коду</li>
    <li>Оплата картой МИР</li>
    <li>Постоплата в офисе</li>
  </ul>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>  <!-- общий подвал: закрытие тегов + скрипты Bootstrap (без них слайдер не поедет!) -->