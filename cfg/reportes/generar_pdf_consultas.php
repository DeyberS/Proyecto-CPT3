<?php
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); // Verifica tu ruta a tcpdf.php
include('../conexion.php');

$tipo = $_GET['tipo'];
$fecha_hoy = date('Y-m-d');

// --- LÓGICA DE CONSULTAS SQL SEGÚN EL TIPO ---
if ($tipo == 'hoy') {
    $titulo = "CONSULTAS DEL DÍA: $fecha_hoy";
    $sql = "SELECT c.*, p.nombre, p.apellido FROM consulta c 
            JOIN persona p ON c.Id_paciente = p.id 
            WHERE c.fecha_consulta = '$fecha_hoy' AND c.estatus = 1";
} elseif ($tipo == 'patologias') {
    $titulo = "REPORTE DE MORBILIDAD (PATOLOGÍAS DE PACIENTES ATENDIDOS)";
    $sql = "SELECT pa.nombre_patologia, COUNT(c.Id_consulta) as total 
            FROM consulta c 
            INNER JOIN historial_patologias hp ON c.Id_historial = hp.Id_Historial 
            INNER JOIN patologias pa ON hp.Id_patologia = pa.Id_patologia 
            WHERE c.estatus = 1 
            GROUP BY pa.nombre_patologia 
            ORDER BY total DESC";
} elseif ($tipo == 'riesgo') {
    $titulo = "ALERTA: PACIENTES CON SIGNOS VITALES FUERA DE RANGO";
    // Ejemplo: Saturación menor a 94 o Tensión alta
    $sql = "SELECT c.*, p.nombre, p.apellido FROM consulta c 
            JOIN persona p ON c.Id_paciente = p.id 
            WHERE (c.saturacion < 94 OR c.frecuencia_cardiaca > 100) AND c.estatus = 1";
} elseif ($tipo == 'diagnosticos') {
    $titulo = "DIAGNÓSTICOS MÁS FRECUENTES EN CONSULTA";
    $sql = "SELECT diagnostico as nombre_patologia, COUNT(Id_consulta) as total 
            FROM consulta 
            WHERE estatus = 1 AND diagnostico IS NOT NULL AND diagnostico != ''
            GROUP BY diagnostico 
            ORDER BY total DESC";
} else {
    $titulo = "REPORTE GENERAL DE TODAS LAS CONSULTAS";
    $sql = "SELECT c.*, p.nombre, p.apellido FROM consulta c 
            JOIN persona p ON c.Id_paciente = p.id WHERE c.estatus = 1";
}

$resultado = mysqli_query($conexion, $sql);

// --- CONFIGURACIÓN TCPDF ---
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetTitle($titulo);
$pdf->setPrintHeader(false);
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// Estilos HTML
$html = '
<style>
    h1 { text-align: center; color: #333; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #337ab7; color: white; font-weight: bold; border: 1px solid #000; text-align: center; }
    td { border: 1px solid #ccc; text-align: center; font-size: 10pt; }
</style>
<h1>' . $titulo . '</h1>
<table cellpadding="5">';

if ($tipo == 'patologias') {
    $html .= '<thead><tr><th>Patología / Enfermedad</th><th>Cantidad de Casos</th></tr></thead><tbody>';
    while ($row = mysqli_fetch_assoc($resultado)) {
        $html .= '<tr><td>'.$row['nombre_patologia'].'</td><td>'.$row['total'].'</td></tr>';
    }  }
    else if ($tipo == 'diagnosticos') {
        $html .= '<thead>
                  <tr><th>Paciente</th>
                  <th>Diagnostico</th></tr>
                  </thead><tbody>';
        while ($row = mysqli_fetch_assoc($resultado)) {
            $html .= '<tr>
            <td>'.$row['nombre'].' '.$row['apellido'].'</td>
            <td>'.$row['dianostico'].'</td></tr>';
        }  }
        else if ($tipo == 'riesgo') {
            $html .= '<thead>
                      <tr><th>Paciente</th>
                      <th>Tensión</th>
                      <th>Saturación</th></tr>
                      </thead><tbody>';
            while ($row = mysqli_fetch_assoc($resultado)) {
                $html .= '<tr>
                <td>'.$row['nombre'].' '.$row['apellido'].'</td>
                <td>'.$row['tension'].'</td><
                <td>'.$row['saturacion'].'%</td>/tr>';
            }
        
} else {
    $html .= '<thead><tr>
                <th>Paciente</th>
                <th>Motivo</th>         
                <th>Diagnóstico</th>
                <th>Fecha</th> 
              </tr></thead><tbody>';
    while ($row = mysqli_fetch_assoc($resultado)) {
        $html .= '<tr>
                    <td>'.$row['nombre'].' '.$row['apellido'].'</td>
                    <td>'.$row['motivo_consulta'].'</td>  
                    <td>'.$row['fecha_consulta'].'</td>            
                    <td>'.$row['diagnostico'].'</td>
                  </tr>';
    }
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');
if (ob_get_contents()) ob_end_clean();
$pdf->Output('Reporte.pdf', 'I');


