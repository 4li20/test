<?php
use App\config\config;


// Consulta para obtener todos los usuarios
$query = "SELECT id, password FROM usuarios";
$result = $conn->query($query);

while ($user = $result->fetch_assoc()) {
    $id = $user['id'];
    $password = $user['password'];

    // Detectar si la contraseña no está encriptada
    if (substr($password, 0, 4) !== '$2y$') {
        // Encripta la contraseña y actualiza la base de datos
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE usuarios SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $hashedPassword, $id);
        $stmt->execute();

        echo "Contraseña del usuario con ID $id ha sido encriptada correctamente.<br>";
    } else {
        echo "La contraseña del usuario con ID $id ya está encriptada.<br>";
    }
}

echo "Proceso de actualización completado.";
