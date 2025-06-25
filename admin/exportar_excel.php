<?php
require __DIR__ . '/../vendor/autoload.php'; // Ruta al autoloader de Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\config\config; // Ajusta la ruta según la estructura de tu proyecto


// Obtener filtros 
$usuarioId = isset($_POST['usuario_id']) ? intval($_POST['usuario_id']) : 0;
$fechaInicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] . " 00:00:00" : date('Y-m-d 00:00:00');
$fechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] . " 23:59:59" : date('Y-m-d 23:59:59');

// Construir la consulta dependiendo del usuario seleccionado
$query = "SELECT * FROM registros_ingresos WHERE fecha BETWEEN ? AND ?";
if ($usuarioId > 0) {
    $query .= " AND usuario_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $fechaInicio, $fechaFin, $usuarioId);
} else {
    // Si es "Todos", no se aplica filtro por usuario
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $fechaInicio, $fechaFin);
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error al obtener los datos: " . $conn->error);
}

// Crear la hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados de las columnas
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Descripción');
$sheet->setCellValue('C1', 'Cantidad');
$sheet->setCellValue('D1', 'Precio Unitario');
$sheet->setCellValue('E1', 'Total');
$sheet->setCellValue('F1', 'Fecha');

// Agregar datos a la hoja
$rowNumber = 2;
$totalGeneral = 0;
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNumber, $row['id']);
    $sheet->setCellValue('B' . $rowNumber, $row['descripcion']);
    $sheet->setCellValue('C' . $rowNumber, $row['cantidad']);
    $sheet->setCellValue('D' . $rowNumber, $row['precio_unitario']);
    $sheet->setCellValue('E' . $rowNumber, $row['total']);
    $sheet->setCellValue('F' . $rowNumber, $row['fecha']);
    $totalGeneral += $row['total']; // Sumar el total de cada fila
    $rowNumber++;
}

// Agregar el total general en la última fila
$sheet->setCellValue('D' . $rowNumber, 'Total General:');
$sheet->setCellValue('E' . $rowNumber, $totalGeneral);

// Configuración para la descarga
$filename = "historial_ventas_" . date('Ymd_His') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

exit;
