<?php
session_start();
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

// Verificar si la sesión está iniciada y el rol del usuario es 'admin' o 'usuario'
if (!isset($_SESSION['user_id']) || ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'usuario')) {
    header("Location: ../login.php");
    exit;
}

// Manejo de solicitudes POST para editar o eliminar registros
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    $action = $_POST['action'] ?? null;

    if ($action === 'registrar' && isset($_POST['descripcion'], $_POST['cantidad'], $_POST['precio_unitario'])) {
        $descripcion = $_POST['descripcion'];
        $cantidad = floatval($_POST['cantidad']);
        $precio_unitario = floatval($_POST['precio_unitario']);
        $total = $cantidad * $precio_unitario;
        $usuario_id = $_SESSION['user_id'];

        $query = $conn->prepare("INSERT INTO registros_egresos (descripcion, cantidad, precio_unitario, total, usuario_id) VALUES (?, ?, ?, ?, ?)");
        $query->bind_param('sdddi', $descripcion, $cantidad, $precio_unitario, $total, $usuario_id);
        $response['success'] = $query->execute();
        $response['message'] = $response['success'] ? 'Registro creado correctamente' : 'Error al crear el registro';
        echo json_encode($response);
        exit;
    }

    if ($action === 'eliminar' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $query = $conn->prepare("DELETE FROM registros_egresos WHERE id = ?");
        $query->bind_param('i', $id);
        $response['success'] = $query->execute();
        $response['message'] = $response['success'] ? 'Registro eliminado correctamente' : 'Error al eliminar el registro';
        echo json_encode($response);
        exit;
    }

    if ($action === 'editar' && isset($_POST['id'], $_POST['descripcion'], $_POST['cantidad'], $_POST['precio_unitario'], $_POST['fecha'])) {
        $id = intval($_POST['id']);
        $descripcion = $_POST['descripcion'];
        $cantidad = floatval($_POST['cantidad']);
        $precio_unitario = floatval($_POST['precio_unitario']);
        $total = $cantidad * $precio_unitario;
        $fecha = $_POST['fecha'];
    
        $query = $conn->prepare("UPDATE registros_egresos SET descripcion = ?, cantidad = ?, precio_unitario = ?, total = ?, fecha = ? WHERE id = ?");
        $query->bind_param('sdddsi', $descripcion, $cantidad, $precio_unitario, $total, $fecha, $id);
        $response['success'] = $query->execute();
        $response['message'] = $response['success'] ? 'Registro actualizado correctamente' : 'Error al actualizar el registro';
        echo json_encode($response);
        exit;
    }
    
}

// Verificar el estado del registro de egresos
$estadoRegistro = $conn->query("SELECT habilitar_registro FROM configuraciones")->fetch_assoc()['habilitar_registro'];
$registroHabilitado = $estadoRegistro ? true : false;

// Obtener los registros del usuario o administrador
$registros = $conn->query("SELECT id, descripcion, cantidad, precio_unitario, total, fecha FROM registros_egresos WHERE usuario_id = " . $_SESSION['user_id']);

$pageTitle = "Registrar Egreso";
ob_start();
?>

<div class="logo-container">
    <img src="../img/lirios.png" alt="Centro de Negocios Lirios">
</div>
<h2>Registrar Egreso</h2>

<?php if (!$registroHabilitado): ?>
    <div class="disabled-message">LA OPCIÓN DE REGISTRAR EGRESO ESTÁ DESHABILITADA</div>
<?php else: ?>
    <form method="post" class="admin-form" id="formRegistro">
    <input type="hidden" name="id" id="registro_id" value="">
    <input type="hidden" name="action" id="form_action" value="registrar">
    <label>Descripción</label>
    <input type="text" name="descripcion" id="descripcion" required>
    <label>Cantidad</label>
    <input type="number" name="cantidad" id="cantidad" step="1" required oninput="updateTotal()">
    <label>Precio Unitario</label>
    <input type="number" name="precio_unitario" id="precio_unitario" step="0.01" required oninput="updateTotal()">
    <label>Total</label>
    <input type="number" name="total" id="total" step="0.01" readonly>
    <label>Fecha</label>
    <input type="date" name="fecha" id="fecha" required>
    <button type="submit" id="submitButton" class="btn-save">Registrar</button>
</form>


<?php endif; ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $registros->fetch_assoc()): ?>
            <tr id="registro-<?= $row['id'] ?>">
                <td><?= htmlspecialchars($row['descripcion']) ?></td>
                <td><?= htmlspecialchars($row['cantidad']) ?></td>
                <td><?= htmlspecialchars($row['precio_unitario']) ?></td>
                <td><?= htmlspecialchars($row['total']) ?></td>
                <td><?= htmlspecialchars($row['fecha']) ?></td>
                <td>
                    <button class="btn-edit" onclick="editarRegistro(<?= $row['id'] ?>)">Editar</button>
                    <button class="btn-delete" onclick="eliminarRegistro(<?= $row['id'] ?>)">Eliminar</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    function updateTotal() {
        const cantidad = parseFloat(document.getElementById('cantidad').value) || 0;
        const precioUnitario = parseFloat(document.getElementById('precio_unitario').value) || 0;
        const total = cantidad * precioUnitario;
        document.getElementById('total').value = total.toFixed(2);
    }

    function editarRegistro(id) {
    const row = document.getElementById(`registro-${id}`);
    document.getElementById('registro_id').value = id;
    document.getElementById('descripcion').value = row.cells[0].innerText;
    document.getElementById('cantidad').value = row.cells[1].innerText;
    document.getElementById('precio_unitario').value = row.cells[2].innerText;
    document.getElementById('total').value = row.cells[3].innerText;

    // Obtener y asignar la fecha
    const fecha = row.cells[4].innerText;
    document.getElementById('fecha').value = new Date(fecha).toISOString().split('T')[0];

    document.getElementById('form_action').value = 'editar'; // Cambiar la acción
    document.getElementById('submitButton').innerText = 'Guardar cambios';
}

    function eliminarRegistro(id) {
        if (confirm('¿Estás seguro de eliminar este registro?')) {
            fetch('registro_egreso.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'eliminar', id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`registro-${id}`).remove();
                        alert('Registro eliminado correctamente');
                    } else {
                        alert('Error al eliminar el registro.');
                    }
                });
        }
    }

    document.getElementById('formRegistro').addEventListener('submit', function (event) {
    event.preventDefault();
    const formData = new FormData(this);

    fetch('registro_egreso.php', {
        method: 'POST',
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert(data.message);
                window.location.reload(); // Recargar la página para mostrar los datos actualizados
            } else {
                alert('Error al guardar el registro: ' + data.message);
            }
        });
});

function editarRegistro(id) {
    const row = document.getElementById(`registro-${id}`);
    document.getElementById('registro_id').value = id;
    document.getElementById('descripcion').value = row.cells[0].innerText;
    document.getElementById('cantidad').value = row.cells[1].innerText;
    document.getElementById('precio_unitario').value = row.cells[2].innerText;
    document.getElementById('total').value = row.cells[3].innerText;
    document.getElementById('form_action').value = 'editar'; // Cambiar la acción
    document.getElementById('submitButton').innerText = 'Guardar cambios';
}

function updateTotal() {
    const cantidad = parseFloat(document.getElementById('cantidad').value) || 0;
    const precioUnitario = parseFloat(document.getElementById('precio_unitario').value) || 0;
    document.getElementById('total').value = (cantidad * precioUnitario).toFixed(2);
}

</script>

<?php
$pageContent = ob_get_clean();
include '../includes/layout.php';
?>
