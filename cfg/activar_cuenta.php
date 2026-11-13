<?php
session_start();
require_once "conexion.php"; 

if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conexion, $_GET['token']);

    // IMPORTANTE: Verifica si el estatus en tu BD es '3' o si debería ser '0' / '1'
    $sql_buscar = "SELECT Id FROM persona WHERE token_activacion = '$token' AND estatus = '3' LIMIT 1";
    $resultado_buscar = mysqli_query($conexion, $sql_buscar);

    if (mysqli_num_rows($resultado_buscar) > 0) {
        $sql_activar = "UPDATE persona SET estatus = '2', token_activacion = NULL WHERE token_activacion = '$token'";
        
        if (mysqli_query($conexion, $sql_activar)) {
            
            // Comprobamos si hay un administrador con sesión activa
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
                
                // 1. Establecemos el mensaje de éxito adaptado para el administrador
                $_SESSION['mensaje_user_exito'] = "✅ La cuenta del usuario ha sido activada correctamente.";
                
                // 2. Redirigimos al listado de usuarios sin destruir la sesión
                header("Location: ../pages/php/cfg_usuario_listado.php"); 
                exit();
                
            } else {
                // Flujo normal: el usuario nuevo activa su cuenta desde su propio equipo
                // 1. Limpiamos cualquier rastro de sesión anterior
                unset($_SESSION['loggedin']);
                unset($_SESSION['id']);
                unset($_SESSION['nombre']);
                unset($_SESSION['estatus']);
                unset($_SESSION['rol']);
                unset($_SESSION['nombre_rol']);
                unset($_SESSION['permisos']);

                // 2. Establecemos el mensaje de éxito
                $_SESSION['mensaje_user_exito'] = "✅ ¡Tu cuenta ha sido activada correctamente! Ya puedes iniciar sesión.";
                
                // 3. Redirigimos al login
                header("Location: ../index.php"); 
                exit();
            }
            
        } else {
            $_SESSION['mensaje_user_error'] = "❌ Hubo un error al activar tu cuenta. Intenta contactar al soporte.";
            header("Location: ../pages/php/cfg_usuario_listado.php");
            exit();
        }
    } else {
        $_SESSION['mensaje_user_error'] = "⚠️ El enlace de activación es inválido o tu cuenta ya fue activada anteriormente.";
        header("Location: ../pages/php/cfg_usuario_listado.php");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>