<?PHP
session_start();
include("../conexion.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../plugins/PHPMailer/src/Exception.php';
require '../../plugins/PHPMailer/src/PHPMailer.php';
require '../../plugins/PHPMailer/src/SMTP.php';

$estatus = $_SESSION['estatus'] ?? null;
$idUser = $_SESSION['id'] ?? null;
$Id = $_GET['Id'] ?? null; 

if ($estatus == "2") {
    $_SESSION['mensaje_user_error'] = '⚠️ Error de Acceso: Debe iniciar sesión.';
    header('location: ../../pages/php/papelera/cfg_usuario_papelera_listado.php');
    exit();
}

if ($idUser == $Id) {
    $_SESSION['mensaje_user_error'] = '⚠️ Prohibido: No puedes eliminar tu propia cuenta.';
    header('location: ../../pages/php/papelera/cfg_usuario_papelera_listado.php');
    exit();
}

$sql_info = "SELECT p.nombre, p.email, dpr.Id_rol 
             FROM persona p 
             LEFT JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
             WHERE p.id = ?";
$stmt_info = mysqli_prepare($conexion, $sql_info);
mysqli_stmt_bind_param($stmt_info, 'i', $Id);
mysqli_stmt_execute($stmt_info);
$res_info = mysqli_stmt_get_result($stmt_info);
$datos_borrado = mysqli_fetch_assoc($res_info);
mysqli_stmt_close($stmt_info);

if (!$datos_borrado) {
    $_SESSION['mensaje_user_error'] = '⚠️ Error: El usuario no existe.';
    header('location: ../../pages/php/papelera/cfg_usuario_papelera_listado.php');
    exit();
}

$exito_transaccion = false;
$es_medico = (isset($datos_borrado['Id_rol']) && $datos_borrado['Id_rol'] == 7);

mysqli_begin_transaction($conexion);

try {
    if ($es_medico) {
        $sql_update = "UPDATE persona SET password = '', estatus = 1 WHERE id = ?";
        $stmt_update = mysqli_prepare($conexion, $sql_update);
        mysqli_stmt_bind_param($stmt_update, 'i', $Id);
        mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
    } else {
        $sql_rol = "DELETE FROM detalle_persona_rol WHERE Id_persona = ?";
        $stmt_rol = mysqli_prepare($conexion, $sql_rol);
        mysqli_stmt_bind_param($stmt_rol, 'i', $Id);
        mysqli_stmt_execute($stmt_rol);
        mysqli_stmt_close($stmt_rol);

        $sql_persona = "DELETE FROM persona WHERE id = ?";
        $stmt_persona = mysqli_prepare($conexion, $sql_persona);
        mysqli_stmt_bind_param($stmt_persona, 'i', $Id);
        mysqli_stmt_execute($stmt_persona);
        mysqli_stmt_close($stmt_persona);
    }
    
    mysqli_commit($conexion);
    $exito_transaccion = true;
    
} catch (Exception $e) {
    mysqli_rollback($conexion);
    error_log("⚠️ Error en la eliminación/desactivación: " . $e->getMessage());
}

$info_correo = "";
if ($exito_transaccion) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'cpt3sistema@gmail.com';
        $mail->Password   = 'Jrjjfgomexsyyxqg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('no-reply@cpt3.com', 'Sistema CPT3');
        $mail->addAddress($datos_borrado['email'], $datos_borrado['nombre']);

        $mail->isHTML(true);
        $mail->Subject = 'Acceso de Usuario Removido - Sistema CPT3';
        
        if ($es_medico) {
            $mail->Body = "<h3>Hola, " . htmlspecialchars($datos_borrado['nombre']) . "</h3>
                           <p>Te informamos que tus credenciales de acceso al sistema han sido revocadas por un administrador.</p>";
        } else {
            $mail->Body = "<h3>Hola, " . htmlspecialchars($datos_borrado['nombre']) . "</h3>
                           <p>Te informamos que tu cuenta y todos tus datos asociados han sido <b>eliminados definitivamente</b> del sistema por un administrador.</p>
                           <p>Si consideras que esto es un error, contacta a soporte técnico.</p>";
        }
        
        $mail->send();
        $info_correo = " y se notificó al usuario.";
    } catch (Exception $e) {
        $info_correo = ", pero no se pudo enviar el correo de notificación.";
    }
}

// 5. REDIRECCIÓN FINAL
if ($exito_transaccion) {
    if ($es_medico) {
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: Se revocaron las credenciales del usuario médico correctamente' . $info_correo;
    } else {
        $_SESSION['mensaje_user_exito'] = '✅ Éxito: El usuario ha sido eliminado correctamente' . $info_correo;
    }
} else {
    $_SESSION['mensaje_user_error'] = '⚠️ Error: No se pudo completar la operación.';
}

header('location: ../../pages/php/papelera/cfg_usuario_papelera_listado.php');
exit();
?>