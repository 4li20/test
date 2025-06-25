<?php
session_start();
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

// Verificar si la sesión está iniciada y el rol del usuario es 'admin' o 'usuario'
if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'usuario')) {
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
    $medio_pago = $_POST['medio_pago'];
    $cantidad = $_POST['cantidad'];
    $precio_unitario = $_POST['precio_unitario'];
    $total = $cantidad * $precio_unitario;

    $query = "INSERT INTO registros_ingresos (usuario_id, descripcion, medio_pago, cantidad, precio_unitario,  total) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisidd", $_SESSION['user_id'], $descripcion, $medio_pago,  $cantidad, $precio_unitario, $total);
    $stmt->execute();
}

// Obtener los registros del usuario o administrador
$registros = $conn->query("SELECT descripcion, medio_pago, cantidad, precio_unitario, total, fecha FROM registros_ingresos WHERE usuario_id = " . $_SESSION['user_id']);

// Contenido dinámico
ob_start();
?>

<div class="logo-container">
    <img src="../img/lirios.png" alt="Centro de Negocios Lirios">
</div>

<h2>Registrar Ingreso</h2>

<?php if (!$registroHabilitado): ?>
    <!-- Mostrar mensaje cuando el registro está deshabilitado -->
    <div class="disabled-message">LA OPCIÓN DE REGISTRAR VENTA ESTÁ DESHABILITADA</div>
<?php else: ?>
    <!-- Formulario de registro cuando está habilitado -->
    <form method="post" class="admin-form">
        <label>Descripción</label>
        <input type="text" name="descripcion" required>
        <label>Medio de Pago</label>
        <input type="text" name="medio_pago" required>
        <label>Cantidad</label>
        <input type="number" name="cantidad" step="1" required oninput="updateTotal()">
        <label>Precio Unitario</label>
        <input type="number" name="precio_unitario" step="0.01" required oninput="updateTotal()">
          <label>Total</label>
        <input type="number" name="total" step="0.01" readonly>
        <button type="submit">Registrar</button>
    </form>
<?php endif; ?>

<script>
    function updateTotal() {
        const cantidad = parseFloat(document.querySelector('input[name="cantidad"]').value) || 0;
        const precioUnitario = parseFloat(document.querySelector('input[name="precio_unitario"]').value) || 0;
        const total = cantidad * precioUnitario;
        document.querySelector('input[name="total"]').value = total.toFixed(2);
    }
</script>

<?php
$pageContent = ob_get_clean();
include '../includes/layout.php';
?>
