<?php
session_start();
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

// Verificar si el usuario tiene el rol de administrador
if ($_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Verificar si se recibió el ID de la venta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $ventaId = intval($_POST['id']);

    // Eliminar la venta de la base de datos
    $deleteQuery = "DELETE FROM registros_ingresos WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $ventaId);

    if ($deleteStmt->execute()) {
        $_SESSION['message'] = "Venta eliminada correctamente.";
    } else {
        $_SESSION['message'] = "Error al eliminar la venta.";
    }
}

header("Location: reporte_ventas.php");
exit;
