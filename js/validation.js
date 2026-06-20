// ============================================================
//  КЛИЕНТСКАЯ ВАЛИДАЦИЯ РЕГИСТРАЦИИ (подсказки сразу, без перезагрузки).
//  Дублирует серверную проверку в register.php.
// ============================================================
const form = document.getElementById('regForm');

if (form) {
  form.addEventListener('submit', function (event) {
    const f = form.elements;
    let ok = true;

    function setError(field, text) {           // пишет подсказку под полем
      field.nextElementSibling.textContent = text;
      if (text) ok = false;
    }

    setError(f['login'], /^[A-Za-z0-9]{6,}$/.test(f['login'].value) ? '' : 'Логин: латиница и цифры, минимум 6');
    setError(f['password'], f['password'].value.length < 8 ? 'Пароль: минимум 8 символов' : '');
    setError(f['fio'], f['fio'].value.trim() === '' ? 'Укажите ФИО' : '');
    setError(f['phone'], f['phone'].value.trim() === '' ? 'Укажите телефон' : '');
    setError(f['email'], /^\S+@\S+\.\S+$/.test(f['email'].value) ? '' : 'Неверный e-mail');

    if (!ok) event.preventDefault();
  });
}
