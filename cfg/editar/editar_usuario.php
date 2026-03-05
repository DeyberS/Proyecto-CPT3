<?php
// ====================================================================
// MODIFICACIÓN CLAVE 1: INICIAR LA SESIÓN
// Esto es esencial para poder usar $_SESSION y mostrar los modales.
// ====================================================================
session_start();

// Incluir la conexión a la base de datos
include('../conexion.php'); 

require '../../plugins/PHPMailer/src/Exception.php';
require '../../plugins/PHPMailer/src/PHPMailer.php';
require '../../plugins/PHPMailer/src/SMTP.php';

// IMPORTAR PHPMAILER PARA NOTIFICACIONES
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../plugins/vendor/autoload.php';

function validarEmailReal($email) {
    // 1. Validar formato sintáctico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // 2. Extraer el dominio
    $dominio = substr(strrchr($email, "@"), 1);

    // 3. Verificar si el dominio tiene registros MX (Mail Exchange)
    return checkdnsrr($dominio, "MX");
}

// Ejemplo de uso:
if (!validarEmailReal($_POST['email'])) {
    $_SESSION['mensaje_user_error'] = "❌ El dominio del correo no parece ser válido o no puede recibir mensajes.";
    header("Location: ../../pages/php/cfg_usuario_listado.php");
    exit();
}

// 1. VERIFICAR MÉTODO Y EXISTENCIA DE DATOS ESENCIALES
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar y sanear datos esenciales
    $id_usuario = isset($_POST['Id']) ? (int) $_POST['Id'] : 0;
    $nombre     = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email      = isset($_POST['email']) ? trim($_POST['email']) : '';
    $rol        = isset($_POST['rol']) ? (int) $_POST['rol'] : 0;
    $password   = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Si el ID o campos obligatorios faltan, redirigir con error
    if ($id_usuario === 0 || empty($nombre) || empty($email) || $rol === 0) {
        $_SESSION['mensaje_user_error'] = '❌ Error: Faltan datos esenciales (ID, Nombre, Email o Rol) para la actualización.';
        header('Location: ../../pages/php/cfg_usuario_listado.php');
        exit;
    }

    // 2. LÓGICA PARA DETERMINAR SI SE ACTUALIZA LA CONTRASEÑA
    // Solo si el campo password no está vacío, procesamos el cambio de clave.
    $password_update_sql = "";
    $cambio_password = false;

    if (!empty($password)) {
        if ($password === $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE persona SET nombre = ?, email = ?, password = ? WHERE id = ?";
            $cambio_password = true;
        } else {
            $_SESSION['mensaje_user_error'] = '⚠️ Error: Las contraseñas nuevas no coinciden.';
            header('Location: ../../pages/php/cfg_usuario_listado.php');
            exit;
        }
    } else {
        // Si el password está vacío, actualizamos solo nombre y email.
        $sql = "UPDATE persona SET nombre = ?, email = ? WHERE id = ?";
    }

    // 3. PREPARAR LA SENTENCIA PARA ACTUALIZAR PERSONA
    if ($stmt = $conexion->prepare($sql)) {
        
        // Vincular parámetros dinámicamente según si hay password o no
        if ($cambio_password) {
            $stmt->bind_param("sssi", $nombre, $email, $hashed_password, $id_usuario);
        } else {
            $stmt->bind_param("ssi", $nombre, $email, $id_usuario);
        }

        // Ejecutar la actualización de los datos personales
        if ($stmt->execute()) {
            
            // 4. ACTUALIZAR EL ROL DEL USUARIO
            $sql_rol = "UPDATE detalle_persona_rol SET Id_rol = ? WHERE Id_persona = ?";
            $stmt_rol = $conexion->prepare($sql_rol);
            $stmt_rol->bind_param("ii", $rol, $id_usuario);
            
            if ($stmt_rol->execute()) {

                // --- BLOQUE DE ENVÍO DE CORREO (SOLO SI SE CAMBIÓ LA CLAVE) ---
                $info_correo = ".";
                if ($cambio_password) {
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com'; 
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'cpt3sistema@gmail.com';
                        $mail->Password   = 'rqgltslfvazhjqix';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;

                        $mail->setFrom('no-reply@cpt3.com', 'Sistema CPT3');
                        $mail->addAddress($email, $nombre);

                        $mail->isHTML(true);
                        $mail->Subject = 'Notificacion de Seguridad - Cambio de Clave';
                        $mail->Body    = "<h3>Hola, $nombre</h3>
                                          <p>Se ha realizado un cambio en tu contraseña de acceso al sistema.</p>
                                          <p>Tu nueva clave es: <b>$password</b></p>
                                          <p>Si no realizaste este cambio, contacta a soporte inmediatamente.</p>";
                        $mail->send();
                        $info_correo = " y se envió la nueva clave al correo.";
                    } catch (Exception $e) {
                        $info_correo = ", pero hubo un error al enviar el correo de notificación.";
                    }
                }
                // --- FIN BLOQUE DE CORREO ---

                // Éxito: Redirigir al listado de usuarios con mensaje de éxito
                $_SESSION['mensaje_user_exito'] = '✅ Éxito: El usuario ' . $nombre . ' ha sido actualizado correctamente' . $info_correo;
                header('Location: ../../pages/php/cfg_usuario_listado.php');
                exit;

            } else {
                // Error al actualizar el rol
                $_SESSION['mensaje_user_error'] = '❌ Error: Los datos personales se actualizaron, pero falló la actualización del rol. Error: ' . $stmt_rol->error;
                header('Location: ../../pages/php/cfg_usuario_listado.php');
                exit;
            }

        } else {
            // Error en la ejecución de la persona (ej. email duplicado)
            $_SESSION['mensaje_user_error'] = '❌ Error de Ejecución: No se pudieron actualizar los datos personales. Error: ' . $stmt->error;
            header('Location: ../../pages/php/cfg_usuario_listado.php');
            exit;
        }

    } else {
        $_SESSION['mensaje_user_error'] = '❌ Error: No se pudo preparar la consulta SQL.';
        header('Location: ../../pages/php/cfg_usuario_listado.php');
        exit;
    }

} else {
    // MODIFICACIÓN CLAVE 2: Usar la variable de sesión para el acceso no autorizado
    $_SESSION['mensaje_user_error'] = '⚠️ Error de Petición: Acceso no autorizado al script de edición.';
    header('Location: ../../pages/php/cfg_usuario_listado.php');
    exit;
}
?>