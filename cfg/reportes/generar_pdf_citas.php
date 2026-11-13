<?php
require_once('../../plugins/vendor/tcpdf/tcpdf.php'); 
include('../conexion.php');

$tipo = $_GET['tipo'] ?? 'todos';
$fecha_hoy = date('Y-m-d');

// 1. LÓGICA DE CONSULTA SQL CORREGIDA
// El error estaba en el JOIN del médico. c.Id_medico apunta a detalle_medico, no directamente a persona.
$base_sql = "SELECT 
                c.fecha_cita, c.hora_cita, c.motivo, c.estado, 
                p.nombre as p_nom, p.apellido as p_ape, p.tipo_cedula, p.cedula, 
                pm.nombre as m_nom, pm.apellido as m_ape 
             FROM citas c 
             JOIN persona p ON c.Id_paciente = p.id 
             JOIN detalle_medico dm ON c.Id_medico = dm.Id_detalle_medico 
             JOIN persona pm ON dm.Id_persona = pm.id 
             WHERE c.estatus = 1";

switch ($tipo) {
    case 'hoy':
        $titulo = "Citas programadas para hoy";
        $subtitulo = "Fecha: " . date('d/m/Y');
        $sql = "$base_sql AND c.fecha_cita = '$fecha_hoy' ORDER BY c.hora_cita ASC";
        break;

    case 'proximas':
        $titulo = "Próximas citas programadas";
        $subtitulo = "A partir de: " . date('d/m/Y', strtotime('+1 day'));
        $sql = "$base_sql AND c.fecha_cita > '$fecha_hoy' ORDER BY c.fecha_cita ASC, c.hora_cita ASC";
        break;

    default:
        $titulo = "Historial General de Citas";
        $subtitulo = "Todas las fechas registradas";
        $sql = "$base_sql ORDER BY c.fecha_cita DESC, c.hora_cita DESC";
        break;
}

$resultado = mysqli_query($conexion, $sql);

// 2. EXTENDIENDO TCPDF PARA UN DISEÑO MÁS PROFESIONAL (HEADER/FOOTER)
class MYPDF extends TCPDF {
    public function Header() {
        $this->SetY(10);
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(41, 128, 185); // Azul profesional
        $this->Cell(0, 10, 'CONSULTORIO POPULAR TIPO 3 - REPORTE DE CITAS', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Line(15, 20, 195, 20); // Línea divisoria
    }

    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(127, 140, 141); // Gris
        $this->Cell(0, 10, 'Generado el: ' . date('d/m/Y H:i:s') . ' | Página '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

// 3. CONFIGURACIÓN DEL PDF
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema Clínico');
$pdf->SetTitle('Reporte de Citas');
$pdf->SetMargins(15, 25, 15); // Márgenes: Izq, Sup, Der
$pdf->SetAutoPageBreak(TRUE, 20); // Salto de página automático con 20mm de margen inferior
$pdf->AddPage();

// 4. DISEÑO CSS Y HTML DEL CUERPO
$html = '
<style>
    h1 { text-align: center; color: #2c3e50; font-size: 16pt; margin-bottom: 0px; text-transform: uppercase;}
    h3 { text-align: center; color: #7f8c8d; font-size: 11pt; margin-top: 5px; font-weight: normal;}
    table.table-citas { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th { background-color: #2980b9; color: #ffffff; font-weight: bold; border: 1px solid #1f618d; text-align: center; padding: 8px; line-height: 1.5; font-size: 10pt; }
    td { border: 1px solid #bdc3c7; text-align: center; padding: 8px; font-size: 9pt; line-height: 1.5; }
    .estado-text { font-weight: bold; }
</style>

<h1>' . $titulo . '</h1>
<h3>' . $subtitulo . '</h3>
<br>

<table class="table-citas" cellpadding="4">
    <thead>
        <tr>
            <th width="15%">Fecha/Hora</th>
            <th width="30%">Paciente</th>
            <th width="25%">Médico Asignado</th>
            <th width="18%">Motivo</th>
            <th width="12%">Estado</th>
        </tr>
    </thead>
    <tbody>';

if (mysqli_num_rows($resultado) > 0) {
    // Variable para alternar colores de fila (Zebra striping)
    $fill = false; 

    while ($row = mysqli_fetch_assoc($resultado)) {
        $color_fila = $fill ? '#f9f9f9' : '#ffffff';
        $fill = !$fill;

        // Formatear Fecha y Hora para que sea más legible
        $fecha_fmt = date('d/m/Y', strtotime($row['fecha_cita']));
        $hora_fmt = date('h:i A', strtotime($row['hora_cita']));
        $motivo = !empty($row['motivo']) ? $row['motivo'] : '<span style="color:#999;">No especificado</span>';

        // Estilos dinámicos para la columna de estado
        $estado = $row['estado'];
        $bg_estado = '#ffffff';
        $color_estado = '#333333';
        
        switch($estado) {
            case 'Confirmada': case 'Finalizada': 
                $bg_estado = '#d4edda'; $color_estado = '#155724'; // Verde
                break;
            case 'Cancelada': case 'Vencida': case 'Inasistente': 
                $bg_estado = '#f8d7da'; $color_estado = '#721c24'; // Rojo
                break;
            case 'Pendiente': case 'Reprogramada': 
                $bg_estado = '#fff3cd'; $color_estado = '#856404'; // Amarillo/Naranja
                break;
        }

        $html .= '<tr bgcolor="'.$color_fila.'">
                    <td width="15%"><b>'.$fecha_fmt.'</b><br><span style="color:#555555; font-size:8pt;">'.$hora_fmt.'</span></td>
                    <td width="30%" align="left">
                        <b>'.$row['p_nom'].' '.$row['p_ape'].'</b><br>
                        <span style="color:#555555; font-size:8pt;">C.I: '.$row['tipo_cedula'].'-'.$row['cedula'].'</span>
                    </td>
                    <td width="25%" align="left">Dr(a). '.$row['m_nom'].' '.$row['m_ape'].'</td>
                    <td width="18%">'.$motivo.'</td>
                    <td width="12%" bgcolor="'.$bg_estado.'" style="color:'.$color_estado.';">
                        <br><b class="estado-text">'.$estado.'</b>
                    </td>
                  </tr>';
    }
} else {
    $html .= '<tr><td colspan="5" style="color: #7f8c8d; padding: 20px;">No se encontraron registros de citas para esta selección.</td></tr>';
}

$html .= '</tbody></table>';

// Imprimir el HTML y salir limpiamente
$pdf->writeHTML($html, true, false, true, false, '');

// Evitar errores de "headers already sent"
if (ob_get_contents()) ob_end_clean();

// Salida al navegador
$pdf->Output('Reporte_Citas_'.date('Ymd_Hi').'.pdf', 'I');
?>