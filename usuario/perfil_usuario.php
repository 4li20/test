<?php
session_start();
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'usuario') {
    header("Location: ../login.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);

// Obtener datos del usuario
$query = "SELECT nombre, username FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Obtener el mensaje global
$queryMensaje = "SELECT mensaje_admin FROM configuraciones";
$resultMensaje = $conn->query($queryMensaje);
$mensajeGlobal = $resultMensaje->fetch_assoc()['mensaje_admin'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de Usuario</title>
    <style>
        /* Similar diseño al del administrador */
        body {
            font-family: 'Poppins', Arial, sans-serif;
            margin: 0;
            background-color: #F4F4F9;
            color: #333;
        }

        .sidebar {
            width: 250px;
            background-color:rgba(255, 0, 0, 0.93);
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
        }
        .sidebar a.active {
            background-color: #E0B653;
            color: #1E1C1C;
            font-weight: bold;
        }

        .sidebar a:hover {
            background-color: #333;
            color: #E0B653;
        }

        .content {
            margin-left: 270px;
            padding: 40px;
        }

        .content h2 {
            text-align: center;
            color: #1E1C1C;
        }

        .mensaje-global {
            background-color: #E0F7FA;
            color: #00796B;
            padding: 20px;
            border-radius: 10px;
            margin: 20px auto;
            max-width: 600px;
            text-align: center;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <!-- Menú lateral -->
    <div class="sidebar">
    <h2>Menu de usuario</h2>
    <a href="registro_ingreso.php" class="<?= $current_page == 'registro_ingreso.php' ? 'active' : '' ?>">Registrar Venta</a>
    <a href="perfil_usuario.php" class="<?= $current_page == 'perfil_usuario.php' ? 'active' : '' ?>">Perfil</a>
    <a href="../logout.php" class="<?= $current_page == 'logout.php' ? 'active' : '' ?>">Cerrar Sesión</a>
</div>

    <!-- Contenido principal -->
    <div class="content">
        <h2>Perfil de Usuario</h2>
        
        <div>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($user['nombre']) ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        </div>
        <div class="mensaje-global">
            <p><strong>Mensaje del Administrador:</strong></p>
            <p><?= htmlspecialchars($mensajeGlobal) ?></p>
        </div>
    </div>
</body>
</html>
