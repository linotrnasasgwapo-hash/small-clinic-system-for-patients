<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

function requireAdmin() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? '' : '../admin/') . 'login.php');
        exit;
    }
}
function requireResident() {
    if (empty($_SESSION['resident_id'])) {
        header('Location: ../mobile/login.php');
        exit;
    }
}
function isAdmin()    { return !empty($_SESSION['admin_id']); }
function isResident() { return !empty($_SESSION['resident_id']); }
