  </main><!-- конец .app-body -->

  <!-- НИЖНЕЕ МЕНЮ (как в мобильном приложении) -->
  <nav class="bottom-nav">
    <a href="index.php"        class="<?= $active==='home'  ? 'active':'' ?>"><span class="ic">🏠</span>Главная</a>
    <?php if (isLoggedIn()): ?>
      <a href="application.php" class="<?= $active==='app'   ? 'active':'' ?>"><span class="ic">📝</span>Заявка</a>
      <a href="cabinet.php"     class="<?= $active==='cab'   ? 'active':'' ?>"><span class="ic">👤</span>Кабинет</a>
      <?php if (isAdmin()): ?>
        <a href="admin.php"     class="<?= $active==='admin' ? 'active':'' ?>"><span class="ic">⚙️</span>Админ</a>
      <?php endif; ?>
    <?php else: ?>
      <a href="register.php"    class="<?= $active==='reg'   ? 'active':'' ?>"><span class="ic">✍️</span>Регистрация</a>
      <a href="login.php"       class="<?= $active==='login' ? 'active':'' ?>"><span class="ic">🔑</span>Вход</a>
    <?php endif; ?>
  </nav>

</div><!-- конец .app-shell -->

<script src="js/bootstrap.bundle.min.js"></script><!-- для слайдера, тостов, модалок -->
</body>
</html>
