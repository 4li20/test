<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Sistema Web' ?></title>
    <link rel="stylesheet" href="../includes/styles.css">

    <script>
    // Función para alternar el menú hamburguesa
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const hamburger = document.querySelector('.hamburger-menu');
        sidebar.classList.toggle('active');
        hamburger.classList.toggle('open');
    }
</script>

<script>
    // Manejo del menú hamburguesa
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const sidebar = document.querySelector('.sidebar');

    hamburgerMenu.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });
</script>
<script> // Selecciona los elementos
document.addEventListener('DOMContentLoaded', () => {
        const hamburgerMenu = document.querySelector('.hamburger-menu');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('#overlay');

  // Alternar el sidebar
  hamburgerMenu.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active'); // Muestra/oculta el fondo oscuro
        });
// Función para ocultar el sidebar
// Ocultar el sidebar al hacer clic fuera (en el overlay)
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active'); // Oculta el fondo oscuro
        });
// Evento para el botón de hamburguesa
hamburgerMenu.addEventListener('click', toggleSidebar);

// Evento para el clic fuera del menú
overlay.addEventListener('click', hideSidebar); });
</script>
</head>
<body>
    <!-- Botón menú hamburguesa (visible solo en pantallas pequeñas) -->
    <button class="hamburger-menu" onclick="toggleSidebar()">☰</button>

    <!-- Sidebar común -->
    <div class="sidebar">
    <h2>Panel de Administración</h2>
    <nav>
        <a href="perfil.php" class="<?= $current_page == 'perfil.php' ? 'active' : '' ?>">Perfil</a>
        <a href="crear_usuario.php" class="<?= $current_page == 'crear_usuario.php' ? 'active' : '' ?>">Crear Usuario</a>
        <a href="gestionar_usuarios.php" class="<?= $current_page == 'gestionar_usuarios.php' ? 'active' : '' ?>">Modificar o Eliminar Usuario</a>
        <a href="gestionar_registro.php" class="<?= $current_page == 'gestionar_registro.php' ? 'active' : '' ?>">Habilitar Venta</a>
        <a href="registro_ingreso2.php" class="<?= $current_page == 'registro_ingreso2.php' ? 'active' : '' ?>">Registrar Venta</a>
        <a href="reporte_ventas.php" class="<?= $current_page == 'reporte_ventas.php' ? 'active' : '' ?>">Reporte de Ventas</a>
        <a href="registro_egreso.php" class="<?= $current_page == 'registro_egreso.php' ? 'active' : '' ?>">Registrar Egreso</a>
        <a href="reporte_balance.php" class="<?= $current_page == 'reporte_balance.php' ? 'active' : '' ?>">Reporte General</a>
        <a href="../logout.php" class="<?= $current_page == 'logout.php' ? 'active' : '' ?>">Cerrar Sesión</a>
    </nav>
</div>
    <!-- Contenido principal -->
    <div class="content">
    <?= $pageContent ?? '' ?>
</div>
<div id="overlay" class="overlay hidden"></div>
</body>
</html>