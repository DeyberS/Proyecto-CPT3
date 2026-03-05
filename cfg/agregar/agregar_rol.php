<?php
session_start();
include('../conexion.php');

if ($_POST) {
    $nombre_rol = mysqli_real_escape_string($conexion, $_POST['nombre_rol']);
    $permisos = isset($_POST['permisos']) ? $_POST['permisos'] : [];

    // Iniciamos una transacción para asegurar que se guarde TODO o NADA
    mysqli_begin_transaction($conexion);

    try {
        // 1. Insertar el Rol
        $sqlRol = "INSERT INTO rol (nombre_rol) VALUES ('$nombre_rol')";
        if (!mysqli_query($conexion, $sqlRol)) {
            throw new Exception("Error al crear el rol");
        }

        // 2. Obtener el ID asignado a ese rol
        $id_nuevo_rol = mysqli_insert_id($conexion);

        // 3. Insertar los permisos (si seleccionó alguno)
        if (!empty($permisos)) {
            foreach ($permisos as $id_permiso) {
                $id_p = mysqli_real_escape_string($conexion, $id_p);
                $sqlPermiso = "INSERT INTO rol_permiso (Id_rol, Id_permiso) 
                               VALUES ('$id_nuevo_rol', '$id_permiso')";
                
                if (!mysqli_query($conexion, $sqlPermiso)) {
                    throw new Exception("Error al asignar rol ID: $id_permiso");
                }
            }
        }

        // Si llegamos aquí, todo salió bien: Confirmamos los cambios
        mysqli_commit($conexion);
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El rol fue agregado correctamente.';
        header("Location: ../../pages/php/cfg_roles_listado.php");

    } catch (Exception $e) {
        // Si algo falló, deshacemos cualquier cambio en la base de datos
        mysqli_rollback($conexion);
        $_SESSION['mensaje_user_error'] = '❌ Error de Registro: No se pudo registrar el rol. Detalle: ' . $e->getMessage();
        header("Location: ../../pages/php/cfg_roles_listado.php");
    }
}
?>


