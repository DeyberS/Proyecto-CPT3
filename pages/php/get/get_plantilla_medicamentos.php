<?php
include('../../../cfg/conexion.php');
$busqueda = $conexion->real_escape_string($_POST['busqueda'] ?? '');

// Consulta con el formato exacto de componentes: Nombre Cantidad Unidad + ...
$sql = "SELECT 
            m.nombre_medicamento, 
            d.*,
            p.nombre_presentacion,
            /* Formato solicitado: Paracetamol 500mg + Cafeína 50mg */
            GROUP_CONCAT(
                CONCAT(IFNULL(pa.nombre,''), ' ', IFNULL(dpm.cantidad_unidad_medida,''), IFNULL(um.unidad,'')) 
                SEPARATOR ' + '
            ) AS componentes,
            /* IDs para la lógica del sistema */
            GROUP_CONCAT(
                CONCAT(dpm.id_principio_activo, ',', dpm.cantidad_unidad_medida, ',', dpm.id_tipo_unidad_medida) 
                SEPARATOR '|'
            ) AS composicion_ids
        FROM medicamento m 
        JOIN descripcion_medicamento d ON m.Id_medicamento = d.Id_medicamento
        INNER JOIN presentacion p ON d.Id_presentacion = p.Id_presentacion
        LEFT JOIN detalle_principio_medicamento dpm ON d.Id = dpm.id_medicamento
        LEFT JOIN principio_activo pa ON dpm.id_principio_activo = pa.Id_principio_activo
        LEFT JOIN unidad_medida um ON dpm.id_tipo_unidad_medida = um.Id_unidad_medida
        WHERE (m.nombre_medicamento LIKE '%$busqueda%' OR d.codigo_barras LIKE '%$busqueda%' OR p.nombre_presentacion LIKE '%$busqueda%' OR pa.nombre LIKE '%$busqueda%')
        AND d.estatus = '1'
        GROUP BY d.Id
        ORDER BY m.nombre_medicamento ASC
        LIMIT 10";

$res = $conexion->query($sql);

if ($res && $res->num_rows > 0) {
    echo '<table class="table table-hover table-striped" style="margin-bottom:0;">';
    while ($r = $res->fetch_assoc()) {
        // Si no hay componentes, mostramos un aviso simple
        $componentes_display = !empty($r['componentes']) ? $r['componentes'] : 'Sin componentes registrados';
        
        echo "<tr>
                <td style='vertical-align:middle;'>
                    <strong>{$r['nombre_medicamento']}</strong> 
                    <span class='text-right'>{$r['nombre_presentacion']}</span><br>
                    <small class='text-muted'>{$componentes_display}</small>
                </td>
                <td class='text-right' style='vertical-align:middle;'>
                    <button type='button' class='btn btn-xs btn-success btn-copiar-datos' 
                        data-nombre='{$r['nombre_medicamento']}'
                        data-id_presentacion='{$r['Id_presentacion']}'
                        data-contenido='{$r['contenido_neto']}'
                        data-via='{$r['via_aplicacion']}'
                        data-almacenamiento='{$r['almacenamiento']}'
                        data-id_lab='{$r['Id_laboratorio']}'
                        data-excipientes='{$r['excipientes']}'
                        data-codigo='{$r['codigo_barras']}'
                        data-composicion='{$r['composicion_ids']}'
                        data-nombres_pa='{$r['componentes']}'>
                        <i class='fa fa-copy'></i> Copiar Datos
                    </button>
                </td>
              </tr>";
    }
    echo '</table>';
} else {
    echo '<p class="text-muted text-center" style="padding:20px;">🚫 No se encontraron resultados.</p>';
}
?>