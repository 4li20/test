<?php
session_start();
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

// Verificar si la sesión está iniciada y si el rol es 'admin'
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Obtener datos del administrador
$query = "SELECT nombre, username FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Obtener el mensaje actual
$queryMensaje = "SELECT mensaje_admin FROM configuraciones WHERE id = 1";
$resultMensaje = $conn->query($queryMensaje);
$mensajeActual = $resultMensaje->fetch_assoc()['mensaje_admin'];

// Actualizar perfil y mensaje global
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $username = $_POST['username'];
    $mensaje = $_POST['mensaje'];

    // Actualizar datos del administrador
    $updateQuery = "UPDATE usuarios SET nombre = ?, username = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $nombre, $username, $_SESSION['user_id']);
    $updateStmt->execute();

    // Actualizar el mensaje global
    $updateMensajeQuery = "UPDATE configuraciones SET mensaje_admin = ? WHERE id = 1";
    $updateMensajeStmt = $conn->prepare($updateMensajeQuery);
    $updateMensajeStmt->bind_param("s", $mensaje);
    $updateMensajeStmt->execute();

    $mensajeConfirmacion = "Perfil y mensaje actualizados con éxito.";
}

$pageTitle = "Perfil del Administrador";
ob_start();
?>

<div class="main-container">
    <h2>Perfil del Administrador</h2>
    <?php if (isset($mensajeConfirmacion)): ?>
        <p class="message"><?= $mensajeConfirmacion ?></p>
    <?php endif; ?>
    <form method="post" class="admin-form">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($user['nombre']) ?>" required>

        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label for="mensaje">Mensaje Global:</label>
        <textarea name="mensaje" id="mensaje" rows="5"><?= htmlspecialchars($mensajeActual) ?></textarea>

        <button type="submit">Actualizar Perfil</button>
    </form>
</div>


<?php
$pageContent = ob_get_clean();
include '../includes/layout.php';
