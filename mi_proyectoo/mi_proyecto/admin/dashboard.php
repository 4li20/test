<?php
session_start();
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

// Verificar si la sesión está iniciada y si el rol es 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener la página actual
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', Arial, sans-serif;
            display: flex;
            background-color: #f4f4f9;
            color: #333;
        }
        /* Menú lateral */
        .sidebar {
            width: 250px;
            background-color: #1E1C1C;
            color: #fff;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
        }
        .sidebar h2 {
            font-size: 20px;
            color: #E0B653;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            margin: 5px 0;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #333;
            color: #E0B653;
        }
        .sidebar a.active {
            background-color: #E0B653;
            color: #1E1C1C;
            font-weight: bold;
        }
        /* Contenido principal */
        .content {
            margin-left: 270px;
            padding: 40px;
            flex: 1;
            text-align: center;
        }
        .content h2 {
            color: #1E1C1C;
            font-size: 28px;
            margin-bottom: 20px;
        }
        .content img {
            max-width: 150px;
            margin-bottom: 20px;
        }
        .content p {
            font-size: 18px;
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>
    <!-- Menú lateral -->
    <div class="sidebar">
        <h2>Panel de Administración</h2>
        <a href="perfil.php" class="<?= $current_page == 'perfil.php' ? 'active' : '' ?>">Perfil</a>
        <a href="crear_usuario.php" class="<?= $current_page == 'crear_usuario.php' ? 'active' : '' ?>">Crear Usuario</a>
        <a href="gestionar_usuarios.php" class="<?= $current_page == 'gestionar_usuarios.php' ? 'active' : '' ?>">Modificar o Eliminar Usuario</a>
        <a href="gestionar_registro.php" class="<?= $current_page == 'gestionar_registro.php' ? 'active' : '' ?>">Habilitar Venta</a>
        <a href="registro_ingreso2.php" class="<?= $current_page == 'registro_ingreso2.php' ? 'active' : '' ?>">Registrar Venta</a>
        <a href="reporte_ventas.php" class="<?= $current_page == 'reporte_ventas.php' ? 'active' : '' ?>">Reporte de Ventas por Usuario</a>
        <a href="registro_egreso.php" class="<?= $current_page == 'registro_egreso.php' ? 'active' : '' ?>">Registrar Egreso</a>
        <a href="reporte_balance.php" class="<?= $current_page == 'reporte_balance.php' ? 'active' : '' ?>">Reporte General</a>
        <a href="../logout.php" class="<?= $current_page == 'logout.php' ? 'active' : '' ?>">Cerrar Sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <img src="../img/lirios.png" alt="Logo Lirios">
        <h2>LISTOS PARA TRABAJAR</h2>
        <p>Seleccione una opción en el menú para comenzar.</p>
    </div>
</body>
</html>
