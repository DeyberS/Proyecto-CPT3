<?php
session_start();
include('../conexion.php');

if ($_POST) {
    // Recibimos el ID del rol y el nuevo nombre
    $id_rol = mysqli_real_escape_string($conexion, $_POST['Id']);
    $nombre_rol = mysqli_real_escape_string($conexion, $_POST['nombre_rol']);
    
    // Recibimos el array de permisos (si no viene ninguno, inicializamos array vacío)
    $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : [];

    // Iniciamos la transacción
    mysqli_begin_transaction($conexion);

    try {
        // 1. Actualizar el nombre del rol
        $sqlActualizarRol = "UPDATE rol SET nombre_rol = '$nombre_rol' WHERE Id_rol = '$id_rol'";
        if (!mysqli_query($conexion, $sqlActualizarRol)) {
            throw new Exception("Error al actualizar el nombre del rol");
        }

        // 2. Eliminar los permisos antiguos de este rol
        // Esto es más sencillo que comparar cuáles quitar y cuáles dejar
        $sqlEliminarViejos = "DELETE FROM rol_permiso WHERE Id_rol = '$id_rol'";
        if (!mysqli_query($conexion, $sqlEliminarViejos)) {
            throw new Exception("Error al limpiar roles antiguos");
        }

        // 3. Insertar los nuevos permisos seleccionados
        if (!empty($permisos)) {
            foreach ($permisos as $id_permiso) {
                $id_p = mysqli_real_escape_string($conexion, $id_permiso);
                $sqlInsertarNuevo = "INSERT INTO rol_permiso (Id_rol, Id_permiso) VALUES ('$id_rol', '$id_p')";
                
                if (!mysqli_query($conexion, $sqlInsertarNuevo)) {
                    throw new Exception("Error al asignar el rol ID: $id_p");
                }
            }
        }

        // Si todo salió bien, confirmamos los cambios en la BD
        mysqli_commit($conexion);
        
        // Redireccionamos con éxito
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El rol ha sido actualizado correctamente.';
        header("Location: ../../pages/php/cfg_roles_listado.php");

    } catch (Exception $e) {
        // Si algo falla, deshacemos todo para no dejar datos inconsistentes
        mysqli_rollback($conexion);
        $_SESSION['mensaje_user_error'] = '❌ Error de Actualización: ' . $e->getMessage();
        header("Location: ../../pages/php/cfg_roles_listado.php");
    }
}
?>


