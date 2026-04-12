<?php
require_once __DIR__ . '/inc/bootstrap.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: dashboard-admin.php');
    } else {
        header('Location: buku.php');
    }
    exit;
}

header('Location: login.php');
exit;
