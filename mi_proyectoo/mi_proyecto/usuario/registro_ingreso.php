<?php
session_start();
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

// Verificar el rol del usuario
if ($_SESSION['rol'] != 'usuario') {
    header("Location: ../login.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);

// Verificar el estado del registro de ingresos
$estadoRegistro = $conn->query("SELECT habilitar_registro FROM configuraciones")->fetch_assoc()['habilitar_registro'];
$registroHabilitado = $estadoRegistro ? true : false;

// Procesar el formulario si se ha enviado y el registro está habilitado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $registroHabilitado) {
    $descripcion = $_POST['descripcion'];
    $cantidad = $_POST['cantidad'];
    $precio_unitario = $_POST['precio_unitario'];
    $total = $cantidad * $precio_unitario;

    $query = "INSERT INTO registros_ingresos (usuario_id, descripcion, cantidad, precio_unitario, total) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isidd", $_SESSION['user_id'], $descripcion, $cantidad, $precio_unitario, $total);
    $stmt->execute();
}

// Obtener los registros del usuario
$registros = $conn->query("SELECT descripcion, cantidad, precio_unitario, total, fecha FROM registros_ingresos WHERE usuario_id = " . $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Ingreso</title>
    <style>
        /* Estilos generales */
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #F4F4F9;
            margin: 0;
            display: flex;
            color: #333;
        }

        /* Menú lateral izquierdo */
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
        .sidebar a.active {
            background-color: #E0B653;
            color: #1E1C1C;
            font-weight: bold;
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

        /* Contenedor principal */
        .content {
            margin-left: 270px;
            padding: 40px;
            flex: 1;
        }

        .content h2 {
            color: #1E1C1C;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Formulario */
        form {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 20px auto;
            border-top: 5px solid #E0B653;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        input:focus {
            border-color: #E0B653;
            outline: none;
            box-shadow: 0 0 5px rgba(224, 182, 83, 0.5);
        }

        button {
            background-color: #E0C753;
            color: #1E1C1C;
            font-weight: bold;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        button:hover {
            background-color: #E0B653;
        }

        button:active {
            background-color: #C9A04C;
        }

        /* Mensaje de deshabilitado */
        .disabled-message {
            text-align: center;
            font-size: 18px;
            color: #D9534F;
            margin-top: 20px;
        }

        /* Tabla */
        .table-container {
            margin-top: 30px;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #E0B653;
            color: #fff;
        }

        td {
            background-color: #F9F9F9;
        }

        td:first-child {
            font-weight: bold;
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
        <h2>Registrar Ingreso</h2>
    

        <?php if (!$registroHabilitado): ?>
            <!-- Mostrar mensaje cuando el registro está deshabilitado -->
            <div class="disabled-message">LA OPCIÓN DE REGISTRAR VENTA ESTÁ DESHABILITADA</div>
        <?php else: ?>
            <!-- Formulario de registro cuando está habilitado -->
            <form method="post">
                <label>Descripción</label>
                <input type="text" name="descripcion" required>
                <label>Cantidad</label>
                <input type="number" name="cantidad" step="1" required oninput="updateTotal()">
                <label>Precio Unitario</label>
                <input type="number" name="precio_unitario" step="0.01" required oninput="updateTotal()">
                <label>Total</label>
                <input type="number" name="total" step="0.01" readonly>
                <button type="submit">Registrar</button>
            </form>
        <?php endif; ?>

        <!-- Tabla de registros -->
        <div class="table-container">
            <h2>Historial de Ingresos</h2>
            <table>
                <tr>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Total</th>
                    <th>Fecha</th>
                </tr>
                <?php while ($row = $registros->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['descripcion']) ?></td>
                    <td><?= htmlspecialchars($row['cantidad']) ?></td>
                    <td><?= htmlspecialchars($row['precio_unitario']) ?></td>
                    <td><?= htmlspecialchars($row['total']) ?></td>
                    <td><?= htmlspecialchars($row['fecha']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <!-- JavaScript para actualizar el total automáticamente -->
    <script>
        function updateTotal() {
            const cantidad = parseFloat(document.querySelector('input[name="cantidad"]').value) || 0;
            const precioUnitario = parseFloat(document.querySelector('input[name="precio_unitario"]').value) || 0;
            const total = cantidad * precioUnitario;
            document.querySelector('input[name="total"]').value = total.toFixed(2);
        }
    </script>
</body>
</html>
