<?php
require '../vendor/autoload.php'; // Asegúrate de ajustar la ruta a autoload.php
use App\config\config; // Ajusta la ruta según la estructura de tu proyecto

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Obtener las fechas seleccionadas en los filtros
$fechaInicio = isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] . " 00:00:00" : date('Y-m-d 00:00:00');
$fechaFin = isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] . " 23:59:59" : date('Y-m-d 23:59:59');

// Consultar ingresos
$queryIngresos = "SELECT descripcion, cantidad, precio_unitario, total, fecha FROM registros_ingresos WHERE fecha BETWEEN ? AND ?";
$stmtIngresos = $conn->prepare($queryIngresos);
$stmtIngresos->bind_param("ss", $fechaInicio, $fechaFin);
$stmtIngresos->execute();
$resultIngresos = $stmtIngresos->get_result();

// Consultar egresos
$queryEgresos = "SELECT descripcion, cantidad, precio_unitario, total, fecha FROM registros_egresos WHERE fecha BETWEEN ? AND ?";
$stmtEgresos = $conn->prepare($queryEgresos);
$stmtEgresos->bind_param("ss", $fechaInicio, $fechaFin);
$stmtEgresos->execute();
$resultEgresos = $stmtEgresos->get_result();

// Crear la hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezado general
$sheet->setCellValue('A1', 'Reporte de Balance General');
$sheet->mergeCells('A1:E1');
$sheet->getStyle('A1:E1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1:E1')->getAlignment()->setHorizontal('center');

$sheet->setCellValue('A2', 'Rango de Fechas: ' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)));
$sheet->mergeCells('A2:E2');
$sheet->getStyle('A2:E2')->getAlignment()->setHorizontal('center');

// Ingresos
$sheet->setCellValue('A4', 'Ingresos');
$sheet->mergeCells('A4:E4');
$sheet->getStyle('A4:E4')->getFont()->setBold(true);
$sheet->getStyle('A4:E4')->getAlignment()->setHorizontal('center');

$sheet->fromArray(['Descripción', 'Cantidad', 'Precio Unitario', 'Total', 'Fecha'], NULL, 'A5');
$sheet->getStyle('A5:E5')->getFont()->setBold(true);

$row = 6;
$totalIngresos = 0;
while ($ingreso = $resultIngresos->fetch_assoc()) {
    $sheet->fromArray([
        $ingreso['descripcion'],
        $ingreso['cantidad'],
        number_format($ingreso['precio_unitario'], 2),
        number_format($ingreso['total'], 2),
        date('d/m/Y H:i', strtotime($ingreso['fecha']))
    ], NULL, 'A' . $row);
    $totalIngresos += $ingreso['total'];
    $row++;
}
$sheet->setCellValue('D' . $row, 'Total de Ingresos:');
$sheet->setCellValue('E' . $row, number_format($totalIngresos, 2));
$sheet->getStyle('D' . $row . ':E' . $row)->getFont()->setBold(true);

// Egresos
$row += 2;
$sheet->setCellValue('A' . $row, 'Egresos');
$sheet->mergeCells('A' . $row . ':E' . $row);
$sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
$sheet->getStyle('A' . $row . ':E' . $row)->getAlignment()->setHorizontal('center');

$row++;
$sheet->fromArray(['Descripción', 'Cantidad', 'Precio Unitario', 'Total', 'Fecha'], NULL, 'A' . $row);
$sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);

$row++;
$totalEgresos = 0;
while ($egreso = $resultEgresos->fetch_assoc()) {
    $sheet->fromArray([
        $egreso['descripcion'],
        $egreso['cantidad'],
        number_format($egreso['precio_unitario'], 2),
        number_format($egreso['total'], 2),
        date('d/m/Y H:i', strtotime($egreso['fecha']))
    ], NULL, 'A' . $row);
    $totalEgresos += $egreso['total'];
    $row++;
}
$sheet->setCellValue('D' . $row, 'Total de Egresos:');
$sheet->setCellValue('E' . $row, number_format($totalEgresos, 2));
$sheet->getStyle('D' . $row . ':E' . $row)->getFont()->setBold(true);

// Balance Final
$row += 2;
$balanceFinal = $totalIngresos - $totalEgresos;
$sheet->setCellValue('D' . $row, $balanceFinal >= 0 ? 'Ganancia:' : 'Pérdida:');
$sheet->setCellValue('E' . $row, number_format($balanceFinal, 2));
$sheet->getStyle('D' . $row . ':E' . $row)->getFont()->setBold(true);

// Descargar el archivo
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="balance_general.xlsx"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
