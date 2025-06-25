<?php
session_start();
use Inclu\includes\layout;
use Inclu\includes\includes;
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

// Verificar si el usuario tiene el rol de administrador
if ($_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener el nombre del archivo actual
$current_page = basename($_SERVER['PHP_SELF']);

// Manejar eliminación si se recibe una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'eliminar') {
    $id = intval($_POST['id']);
    $deleteQuery = "DELETE FROM registros_ingresos WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $id);
    $deleteStmt->execute();
    echo json_encode(['success' => $deleteStmt->affected_rows > 0]);
    exit;
}

// Manejar actualización si se recibe una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'editar') {
    $id = intval($_POST['id']);
    $descripcion = $_POST['descripcion'];
    $cantidad = intval($_POST['cantidad']);
    $precio_unitario = floatval($_POST['precio_unitario']);
    $total = $cantidad * $precio_unitario;

    $updateQuery = "UPDATE registros_ingresos SET descripcion = ?, cantidad = ?, precio_unitario = ?, total = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("siddi", $descripcion, $cantidad, $precio_unitario, $total, $id);
    $updateStmt->execute();
    echo json_encode(['success' => $updateStmt->affected_rows > 0]);
    exit;
}

// Obtener los registros del usuario seleccionado y rango de tiempo
$usuarioSeleccionado = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] . " 00:00:00" : date('Y-m-d 00:00:00');
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] . " 23:59:59" : date('Y-m-d 23:59:59');

$query = "SELECT * FROM registros_ingresos WHERE fecha BETWEEN ? AND ?" . ($usuarioSeleccionado > 0 ? " AND usuario_id = ?" : "");
$stmt = $conn->prepare($query);
if ($usuarioSeleccionado > 0) {
    $stmt->bind_param("ssi", $fechaInicio, $fechaFin, $usuarioSeleccionado);
} else {
    $stmt->bind_param("ss", $fechaInicio, $fechaFin);
}
$stmt->execute();
$result = $stmt->get_result();

$totalIngresos = 0;
while ($row = $result->fetch_assoc()) {
    $totalIngresos += $row['total'];
}
$result->data_seek(0); // Reiniciar el puntero para iterar en la tabla

$pageTitle = "Historial de Ventas";
ob_start();


?>

<link rel="stylesheet" href="../includes/styles.css?v=1.0">

<h2>Historial de Ventas</h2>
<form method="get" class="filters">
    <label for="usuario_id">Usuario:</label>
    <select name="usuario_id">
        <option value="0">Todos</option>
        <?php
        $usuarios = $conn->query("SELECT id, nombre FROM usuarios");
        while ($usuario = $usuarios->fetch_assoc()): ?>
            <option value="<?= $usuario['id'] ?>" <?= $usuario['id'] == $usuarioSeleccionado ? 'selected' : '' ?>>
                <?= htmlspecialchars($usuario['nombre']) ?>
            </option>
        <?php endwhile; ?>
    </select>
    <label for="fecha_inicio">Desde:</label>
    <input type="date" name="fecha_inicio" value="<?= isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '' ?>">
    <label for="fecha_fin">Hasta:</label>
    <input type="date" name="fecha_fin" value="<?= isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '' ?>">
    <button type="submit">Filtrar</button>
</form>

<div class="table-container">
    <table id="ventas-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr id="row-<?= $row['id'] ?>">
                    <td><?= $row['id'] ?></td>
                    <td class="descripcion"><?= htmlspecialchars($row['descripcion']) ?></td>
                    <td class="cantidad"><?= htmlspecialchars($row['cantidad']) ?></td>
                    <td class="precio"><?= htmlspecialchars($row['precio_unitario']) ?></td>
                    <td class="total"><?= htmlspecialchars($row['total']) ?></td>
                    <td><?= htmlspecialchars($row['fecha']) ?></td>
                    <td>
                        <button class="btn-edit" 
                            data-id="<?= $row['id'] ?>" 
                            data-descripcion="<?= htmlspecialchars($row['descripcion']) ?>" 
                            data-cantidad="<?= $row['cantidad'] ?>" 
                            data-precio="<?= $row['precio_unitario'] ?>">Editar</button>
                        <button class="btn-delete" data-id="<?= $row['id'] ?>">Eliminar</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="4"><strong>Total:</strong></td>
                <td colspan="3" id="total-ingresos"><?= number_format($totalIngresos, 2) ?></td>
            </tr>
        </tbody>
    </table>
</div>


<div id="edit-form-container" style="display: none;">
    <h3>Editar Venta</h3>
    <form id="edit-form">
        <input type="hidden" name="id" id="edit-id">
        <label for="edit-descripcion">Descripción:</label>
        <input type="text" name="descripcion" id="edit-descripcion">
        <label for="edit-cantidad">Cantidad:</label>
        <input type="number" name="cantidad" id="edit-cantidad">
        <label for="edit-precio">Precio Unitario:</label>
        <input type="number" step="0.01" name="precio_unitario" id="edit-precio">
        <button type="submit">Actualizar</button>
        <button type="button" id="cancel-edit">Cancelar</button>
    </form>
</div>
<!-- Botón para exportar a Excel -->
<form method="post" action="exportar_excel.php" class="export-button">
    <input type="hidden" name="usuario_id" value="<?= $usuarioSeleccionado ?>">
    <input type="hidden" name="fecha_inicio" value="<?= isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '' ?>">
    <input type="hidden" name="fecha_fin" value="<?= isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '' ?>">
    <button type="submit" class="btn-export">Exportar a Excel</button>
</form>
<script>
    // Editar venta
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const descripcion = this.dataset.descripcion;
            const cantidad = this.dataset.cantidad;
            const precio = this.dataset.precio;

            document.getElementById('edit-id').value = id;
            document.getElementById('edit-descripcion').value = descripcion;
            document.getElementById('edit-cantidad').value = cantidad;
            document.getElementById('edit-precio').value = precio;

            document.getElementById('edit-form-container').style.display = 'block';
        });
    });

    // Cancelar edición
    document.getElementById('cancel-edit').addEventListener('click', function () {
        document.getElementById('edit-form-container').style.display = 'none';
    });

    // Guardar edición
    document.getElementById('edit-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'editar',
                id: formData.get('id'),
                descripcion: formData.get('descripcion'),
                cantidad: formData.get('cantidad'),
                precio_unitario: formData.get('precio_unitario'),
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.querySelector(`#row-${formData.get('id')}`);
                row.querySelector('.descripcion').textContent = formData.get('descripcion');
                row.querySelector('.cantidad').textContent = formData.get('cantidad');
                row.querySelector('.precio').textContent = parseFloat(formData.get('precio_unitario')).toFixed(2);
                row.querySelector('.total').textContent = (formData.get('cantidad') * formData.get('precio_unitario')).toFixed(2);
                document.getElementById('edit-form-container').style.display = 'none';
            } else {
                alert('Error al actualizar la venta.');
            }
        });
    });

    // Eliminar venta
document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', function () {
        const id = this.dataset.id;
        
        // Confirmación antes de eliminar
        if (confirm('¿Está seguro de que desea eliminar esta venta? Esta acción no se puede deshacer.')) {
            fetch('', {
                method: 'POST',
                body: new URLSearchParams({ action: 'eliminar', id }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`row-${id}`).remove();
                    alert('Venta eliminada correctamente.');
                } else {
                    alert('Error al eliminar la venta.');
                }
            })
            .catch(error => {
                console.error('Error al eliminar la venta:', error);
                alert('Ocurrió un error inesperado.');
            });
        }
    });
});
</script>

<?php
$pageContent = ob_get_clean();
include '../includes/layout.php';
?>
