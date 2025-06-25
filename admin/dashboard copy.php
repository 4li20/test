<?php
session_start();
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

// Verificar si la sesión está iniciada y si el rol es 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <style>
        /* Estilo general */
        body {
            font-family: 'Poppins', Arial, sans-serif;
            margin: 0;
            background-color: #F4F4F9;
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

        .sidebar a:active {
            background-color: #444;
        }

        /* Contenido principal */
        .content {
            margin-left: 270px; /* Ajustar el margen según el ancho del menú */
            padding: 40px;
        }

        .content h2 {
            color: #1E1C1C;
            margin-bottom: 20px;
        }

        .content p {
            color: #333;
            font-size: 16px;
            line-height: 1.6;
        }

        /* Encabezado destacado */
        .content h2 span {
            color: #E0B653;
        }

        /* Diseño responsivo */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Menú lateral -->
    <div class="sidebar">
        <h2>Panel de Administración</h2>
        <a href="perfil.php">Perfil</a>
        <a href="crear_usuario.php">Crear Usuario</a>
        <a href="gestionar_usuarios.php">Moficar o eliminar usuario</a>
        <a href="gestionar_registro.php">Habilitar Venta</a>
        <a href="registro_ingreso2.php">Registrar Venta</a>
        <a href="reporte_ventas.php">Reporte de Ventas por Usuario</a>
        <a href="registro_egreso.php">Registrar Egreso</a>
        <a href="reporte_balance.php">Reporte General</a>
        <a href="../logout.php">Cerrar Sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        <h2>Bienvenido al <span>Panel de Administración</span></h2>
        <p>Seleccione una opción en el menú para comenzar.</p>
    </div>
</body>
</html>
