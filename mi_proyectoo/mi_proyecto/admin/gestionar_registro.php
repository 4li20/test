<?php
session_start();
include '../config/config.php'; // Configuración de la base de datos
include '../includes/layout.php'; // Incluye el layout común

if ($_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
$mensaje = ""; // Mensaje de confirmación

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $habilitar = isset($_POST['habilitar']) ? 1 : 0;
    $query = "UPDATE configuraciones SET habilitar_registro = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $habilitar);
    if ($stmt->execute()) {
        $mensaje = "Los cambios se han guardado exitosamente.";
    } else {
        $mensaje = "Error al guardar los cambios.";
    }
}

// Obtener el estado actual de habilitar_registro
$result = $conn->query("SELECT habilitar_registro FROM configuraciones");
$estado = ($result && $result->num_rows > 0) ? $result->fetch_assoc()['habilitar_registro'] : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Habilitar o Deshabilitar Registro de Ingresos</title>
    <link rel="stylesheet" href="../includes/styles.css"> <!-- Enlace al archivo de estilos -->
</head>
<body>
    <!-- Contenido principal -->
    <div class="main-container"> <!-- Clase principal del layout -->
        <div class="logo-container">
            <img src="../img/lirios.png" alt="Centro de Negocios Lirios"> <!-- Ajusta la ruta del logo -->
        </div>
        <h2 class="section-title">Habilitar o Deshabilitar Registro de Ingresos</h2>
        <form method="post" class="admin-form">
            <label class="form-label">
                <input type="checkbox" name="habilitar" <?= $estado ? 'checked' : '' ?>> Habilitar Registro de Ingresos
            </label>
            <button type="submit" class="btn-submit">Guardar Cambios</button>
        </form>
        <?php if ($mensaje): ?>
            <p class="form-message"><?= $mensaje ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
