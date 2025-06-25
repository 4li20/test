<?php
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto
use Inclu\includes\layout;
$current_page = basename($_SERVER['PHP_SELF']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Cifra la contraseña
    $rol = $_POST['rol'];
    $estado = 1; // Activo por defecto

    $query = "INSERT INTO usuarios (nombre, username, password, rol, estado) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $nombre, $username, $password, $rol, $estado);

    if ($stmt->execute()) {
        $mensaje = "Usuario creado exitosamente.";
    } else {
        $mensaje = "Error al crear el usuario: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario</title>
    <link rel="stylesheet" href="../includes/styles.css">
</head>
<body>
    <div class="main-container">
        <h2 class="user-form-title">Crear Usuario</h2>
        <div class="logo-section">
            <img class="logo-img" src="../img/lirios.png" alt="Centro de Negocios Lirios">
        </div>
        <?php if (isset($mensaje)): ?>
            <p class="user-form-message"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>
        <form method="post" class="user-create-form">
            <label for="nombre" class="form-label">Nombre:</label>
            <input type="text" name="nombre" id="nombre" class="form-input" required>

            <label for="username" class="form-label">Username:</label>
            <input type="text" name="username" id="username" class="form-input" required>

            <label for="password" class="form-label">Password:</label>
            <input type="password" name="password" id="password" class="form-input" required>

            <label for="rol" class="form-label">Rol:</label>
            <select name="rol" id="rol" class="form-select">
                <option value="admin">Admin</option>
                <option value="usuario">Usuario</option>
            </select>

            <button type="submit" class="form-btn-submit">Crear Usuario</button>
        </form>
    </div>
</body>
</html>


