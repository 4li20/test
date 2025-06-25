<?php
session_start();
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

// Verificar si el usuario tiene el rol de administrador
if ($_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);

// Procesar eliminación de usuario
if (isset($_GET['eliminar_id'])) {
    $idEliminar = $_GET['eliminar_id'];
    $deleteQuery = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $idEliminar);
    $stmt->execute();
    header("Location: gestionar_usuarios.php");
    exit;
}

// Procesar actualización de usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_usuario'])) {
    $idUsuario = $_POST['id_usuario'];
    $nombre = $_POST['nombre'];
    $username = $_POST['username'];
    $rol = $_POST['rol'];
    $estado = $_POST['estado'];
    $password = $_POST['password']; // Nueva contraseña

    if (!empty($password)) {
        // Actualizar con nueva contraseña
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE usuarios SET nombre = ?, username = ?, rol = ?, estado = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssisi", $nombre, $username, $rol, $estado, $passwordHash, $idUsuario);
    } else {
        // Actualizar sin modificar la contraseña
        $updateQuery = "UPDATE usuarios SET nombre = ?, username = ?, rol = ?, estado = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssii", $nombre, $username, $rol, $estado, $idUsuario);
    }

    $stmt->execute();
    header("Location: gestionar_usuarios.php");
    exit;
}

// Obtener todos los usuarios
$queryUsuarios = "SELECT id, nombre, username, rol, estado FROM usuarios";
$resultUsuarios = $conn->query($queryUsuarios);

$pageTitle = "Gestionar Usuarios";
ob_start();
?>

<h2>Gestionar Usuarios</h2>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Username</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($usuario = $resultUsuarios->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($usuario['id']) ?></td>
            <td><?= htmlspecialchars($usuario['nombre']) ?></td>
            <td><?= htmlspecialchars($usuario['username']) ?></td>
            <td><?= htmlspecialchars($usuario['rol']) ?></td>
            <td><?= $usuario['estado'] ? 'Activo' : 'Inactivo' ?></td>
            <td>
                <button class="btn-edit" onclick="editarUsuario(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['nombre']) ?>', '<?= htmlspecialchars($usuario['username']) ?>', '<?= $usuario['rol'] ?>', <?= $usuario['estado'] ?>)">Editar</button>
                <form method="get" style="display:inline;">
                    <input type="hidden" name="eliminar_id" value="<?= $usuario['id'] ?>">
                    <button type="submit" class="btn-delete" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Formulario para editar usuario -->
<div id="editar-form" class="form-container" style="display: none;">
    <h3>Editar Usuario</h3>
    <form method="post">
        <input type="hidden" name="id_usuario" id="id_usuario">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required>

        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>

        <label for="password">Nueva Contraseña (opcional):</label>
        <div style="position: relative; width: 95%;">
            <input type="password" name="password" id="password" style="width: 100%; padding-right: 40px;">
            <button type="button" onclick="togglePassword()" 
                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                        background: transparent; border: none; cursor: pointer; z-index: 10; width: 40px; height: 40px;">
                <img id="toggleIcon" src="../img/eye-closed.png" alt="Mostrar/Ocultar" 
                    style="width: 100%; height: 100%;">
            </button>
        </div>

        <label for="rol">Rol:</label>
        <select name="rol" id="rol">
            <option value="admin">Admin</option>
            <option value="usuario">Usuario</option>
        </select>

        <label for="estado">Estado:</label>
        <select name="estado" id="estado">
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>

        <button type="submit">Guardar Cambios</button>
    </form>
</div>

<script>
    function editarUsuario(id, nombre, username, rol, estado) {
        document.getElementById('id_usuario').value = id;
        document.getElementById('nombre').value = nombre;
        document.getElementById('username').value = username;
        document.getElementById('rol').value = rol;
        document.getElementById('estado').value = estado;
        document.getElementById('password').value = ''; // Vaciar el campo de contraseña
        document.getElementById('editar-form').style.display = 'block';
    }

    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.src = '../img/eye-open.png'; // Cambia al ícono de ojo abierto
        } else {
            passwordInput.type = 'password';
            toggleIcon.src = '../img/eye-closed.png'; // Cambia al ícono de ojo cerrado
        }
    }
</script>

<?php
$pageContent = ob_get_clean();
include '../includes/layout.php';
?>
