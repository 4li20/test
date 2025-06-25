<?php
session_start();

if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['rol'] == 'admin') {
    header("Location: admin/dashboard.php");
} else {
    header("Location: usuario/registro_ingreso.php");
}
?>