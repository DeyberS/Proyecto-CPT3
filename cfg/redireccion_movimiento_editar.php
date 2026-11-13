<?php
include("conexion.php");

if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    
    // Obtenemos el tipo de movimiento
    $sql = "SELECT Id_TipoMovimiento FROM detalle_inventario WHERE Id_detalle_inventario = '$id'";
    $res = mysqli_query($conexion, $sql);
    $mov = mysqli_fetch_assoc($res);

    if ($mov) {
        // Asumiendo: ID 1 = Entrada, ID 2 = Salida/Despacho (Ajusta según tu tabla tipo_movimiento)
        if ($mov['Id_TipoMovimiento'] == 1) {
            header("Location: ../pages/php/farmacia_inventario_movimiento_entrada_editar.php?id=$id");
        } else {
            header("Location: ../pages/php/farmacia_inventario_movimiento_salida_editar.php?id=$id");
        }
    } else {
        header("Location: ../pages/php/farmacia_inventario_listado.php?error=no_encontrado");
    }
}


