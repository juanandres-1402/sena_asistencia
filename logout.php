<?php
/**
 * logout.php
 * Cierra la sesión activa del usuario y lo regresa al login.
 */
require_once __DIR__ . '/includes/sesion.php';
session_destroy();
header('Location: ' . rutaBase() . '/index.php');
exit;
