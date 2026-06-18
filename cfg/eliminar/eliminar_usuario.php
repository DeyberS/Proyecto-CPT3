<?PHP
session_start();
include("../conexion.php");

// IMPORTAR PHPMAILER
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../plugins/PHPMailer/src/Exception.php';
require '../../plugins/PHPMailer/src/PHPMailer.php';
require '../../plugins/PHPMailer/src/SMTP.php';

$estatus = $_SESSION['estatus'] ?? null;
$idUser = $_SESSION['id'] ?? null;
$Id = $_GET['Id'] ?? null; 

// 1. VALIDACIONES INICIALES (Mismo código que tenías)
if ($estatus == "2") {
    $_SESSION['mensaje_user_error'] = '❌ Error de Acceso: Debe iniciar sesión.';
    header('location: ../../pages/php/papelera/cfg_usuario_papelera_listado.php');
    exit();
}

if ($idUser == $Id) {
    $_SESSION['mensaje_user_error'] = '⚠️ Prohibido: No puedes eliminar tu propia cuenta.';
    header('location: ../../pages/php/papelera/cfg_usuario_papelera_listado.php');
    exit();
}

// 2. VALIDACIÓN DEL ROL Y OBTENCIÓN DE DATOS DEL USUARIO A ELIMINAR
$sql_info = "SELECT nombre, email FROM persona WHERE id = ?";
$stmt_info = mysqli_prepare($conexion, $sql_info);
mysqli_stmt_bind_param($stmt_info, 'i', $Id);
mysqli_stmt_execute($stmt_info);
$res_info = mysqli_stmt_get_result($stmt_info);
$datos_borrado = mysqli_fetch_assoc($res_info);
mysqli_stmt_close($stmt_info);

// Si no existe el usuario, abortar
if (!$datos_borrado) {
    $_SESSION['mensaje_user_error'] = '❌ Error: El usuario no existe.';
    header('location: ../../pages/php/papelera/cfg_usuario_papelera_listado.php');
    exit();
}

// 3. PROCESO DE ELIMINACIÓN CON TRANSACCIÓN
$exito_transaccion = false;
mysqli_begin_transaction($conexion);

try {
    // 3.1. Eliminar de detalle_persona_rol
    $sql_rol = "DELETE FROM detalle_persona_rol WHERE Id_persona = ?";
    $stmt_rol = mysqli_prepare($conexion, $sql_rol);
    mysqli_stmt_bind_param($stmt_rol, 'i', $Id);
    mysqli_stmt_execute($stmt_rol);

    // 3.2. Eliminar de persona
    $sql_persona = "DELETE FROM persona WHERE id = ?";
    $stmt_persona = mysqli_prepare($conexion, $sql_persona);
    mysqli_stmt_bind_param($stmt_persona, 'i', $Id);
    mysqli_stmt_execute($stmt_persona);
    
    mysqli_commit($conexion);
    $exito_transaccion = true;
    
} catch (Exception $e) {
    mysqli_rollback($conexion);
    error_log("Error en la eliminación: " . $e->getMessage());
}

// 4. ENVÍO DE CORREO (SOLO SI LA ELIMINACIÓN FUE EXITOSA)
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
        $mail->Subject = 'Cuenta Eliminada Definitivamente - Sistema CPT3';
        $mail->Body    = "<h3>Hola, " . $datos_borrado['nombre'] . "</h3>
                          <p>Te informamos que tu cuenta y todos tus datos asociados han sido <b>eliminados definitivamente</b> del sistema por un administrador.</p>
                          <p>Si consideras que esto es un error, contacta a soporte técnico.</p>";
        
        $mail->send();
        $info_correo = " y se notificó al usuario.";
    } catch (Exception $e) {
        $info_correo = ", pero no se pudo enviar el correo de notificación.";
    }
}

// 5. REDIRECCIÓN FINAL
if ($exito_transaccion) {
    $_SESSION['mensaje_user_exito'] = '✅ Éxito: El usuario ha sido eliminado correctamente' . $info_correo;
} else {
    $_SESSION['mensaje_user_error'] = '❌ Error: No se pudo completar la eliminación.';
}

header('location: ../../pages/php/papelera/cfg_usuario_papelera_listado.php');
exit();
?>