<?php
session_start();
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto
use Inclu\includes\layout;

// Verificar permisos
if ($_SESSION['rol'] != 'admin' && $_SESSION['rol'] != 'usuario') {
    header("Location: ../login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);

// Filtros para ingresos
$fechaInicioIngresos = isset($_GET['fecha_inicio_ingresos']) ? $_GET['fecha_inicio_ingresos'] . " 00:00:00" : date('Y-m-d 00:00:00');
$fechaFinIngresos = isset($_GET['fecha_fin_ingresos']) ? $_GET['fecha_fin_ingresos'] . " 23:59:59" : date('Y-m-d 23:59:59');
$usuarioFiltro = isset($_GET['usuario_ingresos']) ? $_GET['usuario_ingresos'] : 'todos';

// Filtros para egresos
$fechaInicioEgresos = isset($_GET['fecha_inicio_egresos']) ? $_GET['fecha_inicio_egresos'] . " 00:00:00" : date('Y-m-d 00:00:00');
$fechaFinEgresos = isset($_GET['fecha_fin_egresos']) ? $_GET['fecha_fin_egresos'] . " 23:59:59" : date('Y-m-d 23:59:59');

// Consultar usuarios para el filtro
$queryUsuarios = "SELECT id, username FROM usuarios";
$resultUsuarios = $conn->query($queryUsuarios);

// Consultar ingresos con filtros
$queryIngresos = "SELECT * FROM registros_ingresos WHERE fecha BETWEEN ? AND ?";
if ($usuarioFiltro !== 'todos') {
    $queryIngresos .= " AND usuario_id = ?";
}
$stmtIngresos = $conn->prepare($queryIngresos);
if ($usuarioFiltro === 'todos') {
    $stmtIngresos->bind_param("ss", $fechaInicioIngresos, $fechaFinIngresos);
} else {
    $stmtIngresos->bind_param("ssi", $fechaInicioIngresos, $fechaFinIngresos, $usuarioFiltro);
}
$stmtIngresos->execute();
$resultIngresos = $stmtIngresos->get_result();

// Consultar egresos con filtros
$queryEgresos = "SELECT * FROM registros_egresos WHERE fecha BETWEEN ? AND ?";
$stmtEgresos = $conn->prepare($queryEgresos);
$stmtEgresos->bind_param("ss", $fechaInicioEgresos, $fechaFinEgresos);
$stmtEgresos->execute();
$resultEgresos = $stmtEgresos->get_result();

// Calcular totales
$totalIngresos = 0;
$totalEgresos = 0;

while ($row = $resultIngresos->fetch_assoc()) {
    $totalIngresos += $row['total'];
}

while ($row = $resultEgresos->fetch_assoc()) {
    $totalEgresos += $row['total'];
}

// Calcular el balance final
$balanceFinal = $totalIngresos - $totalEgresos;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Balance</title>
    <link rel="stylesheet" href="../includes/styles.css"> <!-- Enlace al CSS global -->
</head>
<body>
<div class="balance-container">
    <h2 class="balance-title">Reporte de Balance</h2>

    <!-- Filtros -->
    <form method="get" class="balance-filters">
        <!-- Filtro de ingresos -->
        <div class="filter-section">
            <h3>Ingresos</h3>
            <label for="usuario_ingresos" class="filter-label">Usuario:</label>
            <select name="usuario_ingresos" id="usuario_ingresos" class="filter-select">
                <option value="todos" <?= $usuarioFiltro === 'todos' ? 'selected' : '' ?>>Todos</option>
                <?php while ($usuario = $resultUsuarios->fetch_assoc()): ?>
                    <option value="<?= $usuario['id'] ?>" <?= $usuarioFiltro == $usuario['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($usuario['username']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <label for="fecha_inicio_ingresos" class="filter-label">Desde:</label>
            <input type="date" name="fecha_inicio_ingresos" id="fecha_inicio_ingresos" class="filter-input"
                   value="<?= isset($_GET['fecha_inicio_ingresos']) ? $_GET['fecha_inicio_ingresos'] : '' ?>">
            <label for="fecha_fin_ingresos" class="filter-label">Hasta:</label>
            <input type="date" name="fecha_fin_ingresos" id="fecha_fin_ingresos" class="filter-input"
                   value="<?= isset($_GET['fecha_fin_ingresos']) ? $_GET['fecha_fin_ingresos'] : '' ?>">
        </div>

        <!-- Filtro de egresos -->
        <div class="filter-section">
            <h3>Egresos</h3>
            <label for="fecha_inicio_egresos" class="filter-label">Desde:</label>
            <input type="date" name="fecha_inicio_egresos" id="fecha_inicio_egresos" class="filter-input"
                   value="<?= isset($_GET['fecha_inicio_egresos']) ? $_GET['fecha_inicio_egresos'] : '' ?>">
            <label for="fecha_fin_egresos" class="filter-label">Hasta:</label>
            <input type="date" name="fecha_fin_egresos" id="fecha_fin_egresos" class="filter-input"
                   value="<?= isset($_GET['fecha_fin_egresos']) ? $_GET['fecha_fin_egresos'] : '' ?>">
        </div>

        <!-- Botón unificado para aplicar filtros -->
        <div class="filter-actions">
            <button type="submit" class="filter-button">Filtrar</button>
        </div>
    </form>

    <!-- Ingresos -->
    <h3>Ingresos</h3>
    <div class="table-container">
        <table class="table-balance">
            <tr>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
                <th>Fecha</th>
            </tr>
            <?php $resultIngresos->data_seek(0); while ($row = $resultIngresos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['descripcion']) ?></td>
                    <td><?= htmlspecialchars($row['cantidad']) ?></td>
                    <td><?= htmlspecialchars($row['precio_unitario']) ?></td>
                    <td><?= htmlspecialchars($row['total']) ?></td>
                    <td><?= htmlspecialchars($row['fecha']) ?></td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="3"><strong>Total de Ingresos</strong></td>
                <td colspan="2"><?= number_format($totalIngresos, 2) ?></td>
            </tr>
        </table>
    </div>

    <!-- Egresos -->
    <h3>Egresos</h3>
    <div class="table-container">
        <table class="table-balance">
            <tr>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
                <th>Fecha</th>
            </tr>
            <?php $resultEgresos->data_seek(0); while ($row = $resultEgresos->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['descripcion']) ?></td>
                    <td><?= htmlspecialchars($row['cantidad']) ?></td>
                    <td><?= htmlspecialchars($row['precio_unitario']) ?></td>
                    <td><?= htmlspecialchars($row['total']) ?></td>
                    <td><?= htmlspecialchars($row['fecha']) ?></td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="3"><strong>Total de Egresos</strong></td>
                <td colspan="2"><?= number_format($totalEgresos, 2) ?></td>
            </tr>
        </table>
    </div>

    <!-- Balance final -->
    <div class="balance-result <?= $balanceFinal >= 0 ? 'positive' : 'negative' ?>">
        <?= $balanceFinal >= 0 ? "Ganancia: " : "Pérdida: " ?><?= number_format($balanceFinal, 2) ?>
    </div>
    <!-- Botón para exportar-->
    <div class="export-button">
        <form action="exportar_balance.php" method="post">
            <input type="hidden" name="fecha_inicio_ingresos" value="<?= isset($_GET['fecha_inicio_ingresos']) ? $_GET['fecha_inicio_ingresos'] : '' ?>">
            <input type="hidden" name="fecha_fin_ingresos" value="<?= isset($_GET['fecha_fin_ingresos']) ? $_GET['fecha_fin_ingresos'] : '' ?>">
            <input type="hidden" name="usuario_ingresos" value="<?= isset($_GET['usuario_ingresos']) ? $_GET['usuario_ingresos'] : '' ?>">
            <input type="hidden" name="fecha_inicio_egresos" value="<?= isset($_GET['fecha_inicio_egresos']) ? $_GET['fecha_inicio_egresos'] : '' ?>">
            <input type="hidden" name="fecha_fin_egresos" value="<?= isset($_GET['fecha_fin_egresos']) ? $_GET['fecha_fin_egresos'] : '' ?>">
            <button type="submit" class="filter-button export-button">Exportar Balance a Excel</button>
        </form>
    </div>
</div>
</div>

</body>
</html>
