<?php
// Evita que cualquier espacio en blanco o error previo rompa el PDF
ob_start(); 

require_once('../../plugins/vendor/tcpdf/tcpdf.php'); // Verifica tu ruta
include('../conexion.php');

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'general';
$fecha_hoy = date('Y-m-d');

// --- 1. LÓGICA DE CONSULTAS SQL ---
if ($tipo == 'hoy') {
    $titulo = "CONSULTAS DEL DÍA";
    $subtitulo = "Fecha: " . date('d/m/Y', strtotime($fecha_hoy));
    $sql = "SELECT c.*, p.nombre, p.apellido 
            FROM consulta c 
            JOIN persona p ON c.Id_paciente = p.id 
            WHERE c.fecha_consulta = '$fecha_hoy' AND c.estatus = 1";

} elseif ($tipo == 'patologias') {
    $titulo = "REPORTE DE MORBILIDAD";
    $subtitulo = "Patologías de pacientes atendidos";
    $sql = "SELECT pa.nombre_patologia, COUNT(c.Id_consulta) as total 
            FROM consulta c 
            INNER JOIN historial_patologias hp ON c.Id_historial = hp.Id_Historial 
            INNER JOIN patologias pa ON hp.Id_patologia = pa.Id_patologia 
            WHERE c.estatus = 1 
            GROUP BY pa.nombre_patologia 
            ORDER BY total DESC";

} elseif ($tipo == 'riesgo') {
    $titulo = "ALERTA CLÍNICA";
    $subtitulo = "Pacientes con signos vitales fuera de rango";
    $sql = "SELECT c.*, p.nombre, p.apellido 
            FROM consulta c 
            JOIN persona p ON c.Id_paciente = p.id 
            WHERE (c.saturacion < 94 OR c.frecuencia_cardiaca > 100 OR c.tension > 140) AND c.estatus = 1";

} elseif ($tipo == 'diagnosticos') {
    $titulo = "DIAGNÓSTICOS MÁS FRECUENTES";
    $subtitulo = "Consolidado histórico de consultas";
    // Corrección: Como se usa GROUP BY, solo mostramos el diagnóstico y su total
    $sql = "SELECT diagnostico, COUNT(Id_consulta) as total 
            FROM consulta 
            WHERE estatus = 1 AND diagnostico IS NOT NULL AND diagnostico != ''
            GROUP BY diagnostico 
            ORDER BY total DESC";

} else {
    $titulo = "REPORTE GENERAL DE CONSULTAS";
    $subtitulo = "Histórico completo de atenciones";
    $sql = "SELECT c.*, p.nombre, p.apellido 
            FROM consulta c 
            JOIN persona p ON c.Id_paciente = p.id 
            WHERE c.estatus = 1
            ORDER BY c.fecha_consulta DESC";
}

$resultado = mysqli_query($conexion, $sql);

// --- 2. CONFIGURACIÓN DEL MEMBRETE Y PIE DE PÁGINA (TCPDF EXTENDIDO) ---
class MYPDF extends TCPDF {
    public function Header() {
        // Título del Centro Médico
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(0, 86, 179); // Azul institucional
        $this->Cell(0, 10, 'CONSULTORIO POPULAR TIPO 3', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(6);
        
        // Subtítulo del encabezado
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100); // Gris oscuro
        $this->Cell(0, 10, 'Sistema de Gestión Clínica e Inventario', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        
        // Línea separadora azul
        $style = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 86, 179));
        $this->Line(15, 22, 195, 22, $style);
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, 'Generado el: ' . date('d/m/Y h:i A') . ' | Página '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// --- 3. INICIALIZACIÓN DE TCPDF ---
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Sistema Medico');
$pdf->SetTitle($titulo);

// Configurar márgenes (Izquierda, Arriba, Derecha)
$pdf->SetMargins(15, 30, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15); // Salto de página automático
$pdf->AddPage();

// --- 4. DISEÑO HTML CON CSS ---
$html = '
<style>
    h2 { text-align: center; color: #333333; font-size: 14pt; margin-bottom: 2px; }
    h4 { text-align: center; color: #666666; font-size: 11pt; font-weight: normal; margin-top: 0px; margin-bottom: 15px; }
    table { border-collapse: collapse; width: 100%; }
    th { background-color: #0056b3; color: white; font-weight: bold; text-align: center; border: 1px solid #cccccc; }
    td { border: 1px solid #cccccc; text-align: center; font-size: 10pt; color: #333333; }
    .row-even { background-color: #f9f9f9; }
    .row-odd { background-color: #ffffff; }
</style>

<h2>' . $titulo . '</h2>
<h4>' . $subtitulo . '</h4>
<table cellpadding="6">'; // cellpadding da el espaciado interno a las celdas

// --- 5. GENERACIÓN DINÁMICA DE LA TABLA ---
$i = 0; // Contador para intercalar colores de fila

if ($tipo == 'patologias') {
    $html .= '<thead>
                <tr>
                    <th width="70%">Patología / Enfermedad</th>
                    <th width="30%">Cantidad de Casos</th>
                </tr>
              </thead><tbody>';
    while ($row = mysqli_fetch_assoc($resultado)) {
        $clase = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        $html .= '<tr class="'.$clase.'">
                    <td width="70%">'.htmlspecialchars($row['nombre_patologia']).'</td>
                    <td width="30%">'.$row['total'].'</td>
                  </tr>';
        $i++;
    }  

} elseif ($tipo == 'diagnosticos') {
    // CORRECCIÓN APLICADA: Ahora lista correctamente los diagnósticos agregados
    $html .= '<thead>
                <tr>
                    <th width="70%">Diagnóstico Clínico</th>
                    <th width="30%">Total de Casos</th>
                </tr>
              </thead><tbody>';
    while ($row = mysqli_fetch_assoc($resultado)) {
        $clase = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        $html .= '<tr class="'.$clase.'">
                    <td width="70%">'.htmlspecialchars($row['diagnostico']).'</td>
                    <td width="30%">'.$row['total'].'</td>
                  </tr>';
        $i++;
    }  

} elseif ($tipo == 'riesgo') {
    // CORRECCIÓN APLICADA: Cierre correcto de etiquetas HTML
    $html .= '<thead>
                <tr>
                    <th width="40%">Paciente</th>
                    <th width="20%">Tensión</th>
                    <th width="20%">Saturación</th>
                    <th width="20%">Frec. Cardíaca</th>
                </tr>
              </thead><tbody>';
    while ($row = mysqli_fetch_assoc($resultado)) {
        $clase = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        $html .= '<tr class="'.$clase.'">
                    <td width="40%">'.htmlspecialchars($row['nombre'].' '.$row['apellido']).'</td>
                    <td width="20%">'.(empty($row['tension']) ? 'N/A' : htmlspecialchars($row['tension'])).'</td>
                    <td width="20%">'.(empty($row['saturacion']) ? 'N/A' : htmlspecialchars($row['saturacion']).'%').'</td>
                    <td width="20%">'.(empty($row['frecuencia_cardiaca']) ? 'N/A' : htmlspecialchars($row['frecuencia_cardiaca']).' lpm').'</td>
                  </tr>';
        $i++;
    }
    
} else {
    // Consulta general o del día
    $html .= '<thead>
                <tr>
                    <th width="15%">Fecha</th>
                    <th width="30%">Paciente</th>
                    <th width="30%">Motivo</th>         
                    <th width="25%">Diagnóstico</th> 
                </tr>
              </thead><tbody>';
    while ($row = mysqli_fetch_assoc($resultado)) {
        $clase = ($i % 2 == 0) ? 'row-even' : 'row-odd';
        $fecha_f = date('d/m/Y', strtotime($row['fecha_consulta']));
        
        $html .= '<tr class="'.$clase.'">
                    <td width="15%">'.$fecha_f.'</td>
                    <td width="30%">'.htmlspecialchars($row['nombre'].' '.$row['apellido']).'</td>
                    <td width="30%">'.htmlspecialchars($row['motivo_consulta']).'</td>              
                    <td width="25%">'.htmlspecialchars($row['diagnostico']).'</td>
                  </tr>';
        $i++;
    }
}

$html .= '</tbody></table>';

// Escribir el contenido HTML
$pdf->writeHTML($html, true, false, true, false, '');

// Limpiar cualquier basura en el buffer antes de generar el PDF para evitar el error "TCPDF ERROR: Some data has already been output"
if (ob_get_length()) ob_end_clean();

// Generar PDF
$pdf->Output('Reporte_Consultas_'.date('Ymd_Hi').'.pdf', 'I');
?>