<?php
session_start();
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

// Verificar si el usuario tiene el rol de administrador
if ($_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener el ID de la venta a editar
if (!isset($_GET['id'])) {
    header("Location: reporte_ventas.php");
    exit;
}

$ventaId = intval($_GET['id']);

// Procesar la edición si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descripcion = $_POST['descripcion'];
    $cantidad = floatval($_POST['cantidad']);
    $precioUnitario = floatval($_POST['precio_unitario']);
    $total = $cantidad * $precioUnitario;

    $updateQuery = "UPDATE registros_ingresos SET descripcion = ?, cantidad = ?, precio_unitario = ?, total = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sdddi", $descripcion, $cantidad, $precioUnitario, $total, $ventaId);

    if ($updateStmt->execute()) {
        $_SESSION['message'] = "Venta actualizada correctamente.";
        header("Location: reporte_ventas.php");
        exit;
    } else {
        $errorMessage = "Error al actualizar la venta.";
    }
}

// Obtener los datos actuales de la venta
$query = "SELECT * FROM registros_ingresos WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ventaId);
$stmt->execute();
$result = $stmt->get_result();
$venta = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Venta</title>
    <style>
        /* Estilos para el formulario */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        form {
            background-color: #fff;
            padding: 20px;
            max-width: 500px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>Editar Venta</h2>
    <?php if (isset($errorMessage)): ?>
        <p style="color: red;"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>
    <form method="post">
        <label for="descripcion">Descripción:</label>
        <input type="text" name="descripcion" id="descripcion" value="<?= htmlspecialchars($venta['descripcion']) ?>" required>
        <label for="cantidad">Cantidad:</label>
        <input type="number" name="cantidad" id="cantidad" value="<?= htmlspecialchars($venta['cantidad']) ?>" step="0.01" required>
        <label for="precio_unitario">Precio Unitario:</label>
        <input type="number" name="precio_unitario" id="precio_unitario" value="<?= htmlspecialchars($venta['precio_unitario']) ?>" step="0.01" required>
        <button type="submit">Actualizar Venta</button>
    </form>
</body>
</html>
