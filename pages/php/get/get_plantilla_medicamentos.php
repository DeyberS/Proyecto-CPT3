<?php
include('../../../cfg/conexion.php');
$busqueda = $conexion->real_escape_string($_POST['busqueda'] ?? '');

$sql = "SELECT 
            m.nombre_medicamento, 
            d.*,
            p.nombre_presentacion,
            
            /* Componentes para mostrar en la lista (Paracetamol 500mg + Cafeina 50mg) */
            (SELECT GROUP_CONCAT(CONCAT(IFNULL(pa2.nombre,''), ' ', IFNULL(dpm2.cantidad_unidad_medida,''), IFNULL(um2.unidad,'')) SEPARATOR ' + ') 
             FROM detalle_principio_medicamento dpm2 
             LEFT JOIN principio_activo pa2 ON dpm2.id_principio_activo = pa2.Id_principio_activo 
             LEFT JOIN unidad_medida um2 ON dpm2.id_tipo_unidad_medida = um2.Id_unidad_medida 
             WHERE dpm2.id_medicamento = d.Id) AS componentes,
            
            /* IDs exactos de la composición (id_pa,cant,id_uni | id_pa,cant,id_uni) */
            (SELECT GROUP_CONCAT(CONCAT(dpm3.id_principio_activo, ',', dpm3.cantidad_unidad_medida, ',', dpm3.id_tipo_unidad_medida) SEPARATOR '|') 
             FROM detalle_principio_medicamento dpm3 
             WHERE dpm3.id_medicamento = d.Id) AS composicion_ids,
             
            /* Nombres de los PA separados por el pipe (|) para que JS los separe bien */
            (SELECT GROUP_CONCAT(pa4.nombre SEPARATOR '|') 
             FROM detalle_principio_medicamento dpm4 
             LEFT JOIN principio_activo pa4 ON dpm4.id_principio_activo = pa4.Id_principio_activo 
             WHERE dpm4.id_medicamento = d.Id) AS nombres_pa,

            /* IDs de patologías separados por pipe (|) */
            (SELECT GROUP_CONCAT(dpm5.Id_patologia SEPARATOR '|') 
             FROM detalle_patologia_medicamento dpm5 
             WHERE dpm5.Id_medicamento = d.Id) AS patologias_ids,
             
            /* Nombres de patologías separados por pipe (|) */
            (SELECT GROUP_CONCAT(pato.nombre_patologia SEPARATOR '|') 
             FROM detalle_patologia_medicamento dpm6 
             JOIN patologias pato ON dpm6.id_patologia = pato.Id_patologia 
             WHERE dpm6.id_medicamento = d.Id) AS patologias_nombres

        FROM medicamento m 
        JOIN descripcion_medicamento d ON m.Id_medicamento = d.Id_medicamento
        INNER JOIN presentacion p ON d.Id_presentacion = p.Id_presentacion
        WHERE (m.nombre_medicamento LIKE '%$busqueda%' OR d.codigo_barras LIKE '%$busqueda%' OR p.nombre_presentacion LIKE '%$busqueda%')
        AND d.estatus = '1'
        ORDER BY m.nombre_medicamento ASC
        LIMIT 10";

$res = $conexion->query($sql);

if ($res && $res->num_rows > 0) {
    echo '<table class="table table-hover table-striped" style="margin-bottom:0;">';
    while ($r = $res->fetch_assoc()) {
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
                        data-cantidad_c='{$r['cantidad_concentracion']}'
                        data-tipo_c='{$r['Id_tipo_concentracion']}'
                        data-composicion='{$r['composicion_ids']}'
                        data-nombres_pa='{$r['nombres_pa']}'
                        data-patologias='{$r['patologias_ids']}'
                        data-nombres_pat='{$r['patologias_nombres']}'>
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