<?php
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); // Ajusta la ruta a tu carpeta de TCPDF
include('../conexion.php');

if (!isset($_GET['Id'])) {
    die("ID de cita no proporcionado.");
}

$id_cita = intval($_GET['Id']);

// 1. Consulta completa con todos los JOINs necesarios
$sql = "SELECT 
            c.*, c.Id_cita,
            p.nombre AS nom_p, p.apellido AS ape_p, p.cedula AS ced_p, p.tipo_cedula,
            per_med.nombre AS nom_m, per_med.apellido AS ape_m,
            esp.nombre_especialidad
        FROM citas c
        INNER JOIN persona p ON c.Id_paciente = p.id
        INNER JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico
        INNER JOIN persona per_med ON dm.Id_persona = per_med.id
        INNER JOIN especialidad esp ON c.Id_especialidad = esp.Id_especialidad
        WHERE c.Id_cita = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_cita);
$stmt->execute();
$resultado = $stmt->get_result();
$datos = $resultado->fetch_assoc();

if (!$datos) {
    die("Cita no encontrada.");
}

// 2. Configuración de TCPDF
$pdf = new TCPDF('P', 'mm', array(80, 150), true, 'UTF-8', false); // Formato Ticket (80mm ancho)
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('CPT3');
$pdf->SetTitle('Comprobante de Cita');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(5, 5, 5);
$pdf->AddPage();

// 3. Contenido del Ticket
$html = '
    <style>
        .titulo { text-align: center; font-weight: bold; font-size: 14pt; }
        .info { font-size: 10pt; }
        .separador { border-bottom: 1px dashed #000; margin: 5px 0; }
    </style>
    
    <div class="titulo">COMPROBANTE DE CITA</div>
    <div style="text-align: center; font-size: 8pt;">Consultorio Popular Tipo 3 (CPT3)</div>
    <div class="separador"></div>
    
    <table class="info" cellpadding="2">
        <tr><td><b>Nro. Cita:</b> #'.str_pad($datos['Id_cita'], 5, "0", STR_PAD_LEFT).'</td></tr>
        <tr><td><b>Fecha:</b> '.date("d/m/Y", strtotime($datos['fecha_cita'])).'</td></tr>
        <tr><td><b>Hora:</b> '.$datos['hora_cita'].'</td></tr>
        <tr><td><b>Paciente:</b> '.$datos['nom_p'].' '.$datos['ape_p'].'</td></tr>
        <tr><td><b>Cédula:</b> '.$datos['tipo_cedula'].'-'.$datos['ced_p'].'</td></tr>
        
        <tr><td class="separador"></td></tr>
        
        <tr><td><b>Especialidad:</b> '.$datos['nombre_especialidad'].'</td></tr>
        <tr><td><b>Médico:</b> Dr. '.$datos['nom_m'].' '.$datos['ape_m'].'</td></tr>
        
        <tr><td class="separador"></td></tr>
        
        <tr><td><b>Motivo:</b><br>'.$datos['motivo'].'</td></tr>
    </table>
    
    <div class="separador"></div>
    <div style="text-align: center; font-size: 8pt; margin-top: 10px;">
        Por favor llegue 15 minutos antes.<br>
        ¡Gracias por su confianza!
    </div>
';

$pdf->writeHTML($html, true, false, true, false, '');

// 4. Salida del PDF
$pdf->Output('Comprobante de cita'.$id_cita.'.pdf', 'I'); // 'I' para abrir en el navegador
?>