<?php
// frontend/config.php - VERSIÓN CORREGIDA
// Verificar si la sesión ya está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de rutas
define('BASE_URL', '/redblog/frontend');

function url($path = '') {
    $base = BASE_URL;
    if (!empty($path) && $path[0] !== '/') {
        $path = '/' . $path;
    }
    return $base . $path;
}

function asset($path) {
    if ($path[0] !== '/') {
        $path = '/' . $path;
    }
    return BASE_URL . $path;
}
?>