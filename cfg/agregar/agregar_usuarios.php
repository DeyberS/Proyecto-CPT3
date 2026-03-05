<?php
 session_start();
 require_once "../conexion.php";
 
 // IMPORTAR PHPMAILER
 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;

require '../../plugins/PHPMailer/src/Exception.php';
require '../../plugins/PHPMailer/src/PHPMailer.php';
require '../../plugins/PHPMailer/src/SMTP.php';

 require '../../plugins/vendor/autoload.php';

 $nombres = $_POST['nombre'];
 $email = $_POST['email'];
 $password = $_POST['password']; // Contraseña plana para el correo
 $confirm_password = $_POST['confirm_password'];
 $estado = 2;
 $rol = $_POST['rol'];

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

 if ($confirm_password == $password){

    $param_password = password_hash($password, PASSWORD_DEFAULT);

    $sql_usuario = "INSERT INTO persona(nombre, email, password, estatus) 
    VALUES('$nombres','$email','$param_password','$estado')";
    
    $resultado_persona = mysqli_query($conexion, $sql_usuario);
    
    if ($resultado_persona) {
        $id_usuario = $conexion->insert_id;

        $sql_rol = "INSERT INTO detalle_persona_rol(Id_persona, Id_rol, estatus)
        VALUES('$id_usuario','$rol', '$estado')";
        
        $resultado_rol = mysqli_query($conexion, $sql_rol);
        
        if ($resultado_rol) {
            
            // --- INICIO ENVÍO DE CORREO ---
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; // Cambiar por tu servidor
                $mail->SMTPAuth   = true;
                $mail->Username   = 'cpt3sistema@gmail.com'; // Tu correo
                $mail->Password   = 'rqgltslfvazhjqix'; // Tu clave de aplicación
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('no-reply@cpt3.com', 'Sistema CPT3');
                $mail->addAddress($email, $nombres . ' ' . $apellidos);

                $mail->isHTML(true);
                $mail->Subject = 'Bienvenido al Sistema - Credenciales de Acceso';
                $mail->Body    = "<h3>Hola, $nombres!</h3>
                                  <p>Tu cuenta ha sido creada exitosamente. Aquí tienes tus datos de acceso:</p>
                                  <ul>
                                    <li><b>Usuario:</b> $email</li>
                                    <li><b>Contraseña:</b> $password</li>
                                  </ul>
                                  <p>Por seguridad, cambia tu contraseña al ingresar.</p>";

                $mail->send();
                $mensaje_correo = " y credenciales enviadas.";
            } catch (Exception $e) {
                $mensaje_correo = ", pero no se pudo enviar el correo.";
            }
            // --- FIN ENVÍO DE CORREO ---

            $_SESSION['mensaje_user_exito'] = '✅ Éxito: El Usuario ' . $nombres . ' ' . $apellidos . ' fue agregado correctamente' . $mensaje_correo;
            header("location: ../../pages/php/cfg_usuario_listado.php");
            exit;
        } else {
            $_SESSION['mensaje_user_error'] = '❌ Error: Usuario agregado, pero falló la asignación de rol.';
            header("location: ../../pages/php/cfg_usuario_listado.php");
            exit;
        }
    } else {
        $_SESSION['mensaje_user_error'] = '❌ Error: No se pudo agregar al usuario. Email posiblemente ya registrado.';
        header("location: ../../pages/php/cfg_usuario_listado.php");
        exit;
    }

 } else {
   $_SESSION['mensaje_user_error'] = '⚠️ Error de Contraseña: Las contraseñas no coinciden.';
   header("location: ../../pages/php/cfg_usuario_listado.php");
   exit;
 }
?>