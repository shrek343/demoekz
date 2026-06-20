<?php
// ============================================================
//  АВТОРИЗАЦИЯ И СЕССИЯ + пара вспомогательных функций.
//  Сессия = "память" сервера о вошедшем пользователе.
//  Тут менять ничего не нужно.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Вошёл ли пользователь?
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

// Это администратор?
function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Доступ только для вошедших (иначе -> вход)
function requireLogin(): void {
    if (!isLoggedIn()) { header('Location: login.php'); exit; }
}

// Доступ только для админа (иначе -> главная)
function requireAdmin(): void {
    if (!isAdmin()) { header('Location: index.php'); exit; }
}

// Безопасный вывод текста на страницу (защита от XSS)
function e(?string $text): string {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

// Цвет бейджа по статусу: первый статус — серый, последний — зелёный,
// средний — цветной. $statuses — список статусов с этой же страницы.
function badgeClass(string $status, array $statuses): string {
    $i = array_search($status, $statuses, true);
    if ($i === 0) return 'badge-new';                       // начальный
    if ($i === count($statuses) - 1) return 'badge-done';   // финальный
    return 'badge-mid';                                     // средний
}
