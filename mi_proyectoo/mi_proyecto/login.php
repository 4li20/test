<?php
session_start();
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

$error = ""; // Variable para almacenar el mensaje de error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Consulta solo por el username
    $query = "SELECT * FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Contraseña correcta, iniciar sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['rol'] = $user['rol'];
        header("Location: index.php");
        exit;
    } else {
        // Usuario o contraseña incorrectos
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <style>
        body {
            font-family: Arial, sans-serif;  /* Establece la fuente del texto a Arial, con una fuente alternativa sans-serif si no está disponible */
            height: 100vh;                   /* La altura del body ocupa el 100% de la altura de la ventana del navegador (viewport) */
            display: flex;                   /* Utiliza el modelo de diseño Flexbox para alinear y distribuir el contenido dentro del body */
            justify-content: center;         /* Centra el contenido de izquierda a derecha (en este caso, el contenedor de login) */
            align-items: center;             /* Centra el contenido de arriba a abajo (el formulario se centra verticalmente) */
            background: url('img/fondo1.jpg') no-repeat center center fixed;  /* Establece una imagen de fondo, sin repetirse, centrada en la pantalla, y fija (no se mueve al hacer scroll) */
            background-size: cover;          /* La imagen de fondo cubre toda la pantalla, ajustándose al tamaño del viewport sin distorsionarse */
            position: relative;              /* Establece la posición del body como relativa, útil para el posicionamiento de elementos hijos */
            color: #fff;                     /* Establece el color del texto en blanco (blanco para contrastar con el fondo oscuro) */
        }


        .login-container {
            background-color: rgba(0, 0, 0, 0.5); /* Fondo semitransparente para el formulario */
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            width: 30%;
            
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .login-title {
                    font-size: 24px;
                    font-weight: bold;
                    margin-bottom: 10px;
                    color: #dcdcdc;
                }
        .login-container img {
                    width: 90px;
                    margin-bottom: 20px;
                }
        .login-container label {
                    font-size: 18px;
                    font-weight: bold;
                
        }
        .login-container button {
            width: 100%;
            padding: 10px;
            background-color:#3c3c3c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 18px;
            border:3px solid #ddd;
        }
        .login-container button:hover {
            background-color:#dcdcdc;
            color:black;
            border:3px solid #ddd;
        }
        

        /* Estilos para inputs */
        input[type="text"],
        input[type="password"] {
            width: 90%;
            padding: 10px;
            margin: 20px 0;
            border-radius: 5px;
            border: 3px solid #ddd;
            font-size: 18px;
        }

        /* Contenedor del campo de contraseña */
        .password-container {
            position: relative;
            width: 90%;
            margin: 20px auto;
        }

        /* Botón del ícono */
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
        }

        .password-toggle img {
            width: 20px;
            height: 20px;
        }

        @media (max-width: 468px) {
            .login-container {
                width: 90%;
                padding: 20px;
            }

            .login-title {
                font-size: 20px;
            }

            input[type="text"],
            input[type="password"] {
                font-size: 16px;
            }
        }

        .login-container {
            padding: 50px;
            height: 400px; /* Asegura que el cuerpo siga ocupando toda la altura en dispositivos pequeños */
            width: 300px; / /* Aumenta el ancho del formulario para pantallas más pequeñas */
            
        }
        .login-container label {
            font-size: 19px;
            font-weight: bold;
        }
        .login-title {
            font-size: 2em; /* Reduce el tamaño del título en móviles */
        }
        .login-container button{
            font-size: 18px;
        }
        .login-img {
            width: 100px; /* Ajusta el tamaño de la imagen del login */
        }

        input[type="text"],
input[type="password"] {
    font-size: 20px; /* Ajusta el tamaño de los campos de entrada */
}

input[type="submit"] {
    font-size: 16px; /* Ajusta el tamaño del botón */
}

    


</style>

</head>
<body>
    <div class="overlay"></div>
    <div class="login-container">
        <div class="login-title">Iniciar Sesión</div>
        <img src="img/lirios.png" alt="Centro de Negocios Lirios">
        <form action="login.php" method="POST">
            <label>Usuario</label>
            <input type="text" name="username" required>
            <label>Contraseña</label>
            <div style="position: relative; width: 100%;">
            <!-- Input para la contraseña -->
            <input type="password" id="password" name="password" required 
                style="width: 100%; padding-right: 50px; box-sizing: border-box; font-size: 18px;">

            <!-- Botón con un tamaño ligeramente mayor -->
            <button type="button" onclick="togglePassword()" 
                    style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
                        background: transparent; border: none; cursor: pointer; z-index: 10; width: 40px; height: 40px;">
                <img id="toggleIcon" src="img/eye-closed.png" alt="Mostrar/Ocultar" 
                    style="width: 100%; height: 100%;">
            </button>
        </div>
        <button type="submit">Ingresar</button>




    <script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.src = 'img/eye-open.png'; // Cambia el ícono al ojo abierto
    } else {
        passwordInput.type = 'password';
        toggleIcon.src = 'img/eye-closed.png'; // Cambia el ícono al ojo cerrado
    }
}

</script>

</body>
</html>

