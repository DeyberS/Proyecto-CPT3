<?php
 session_start();
 require_once "../conexion.php";

 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;

require '../../plugins/PHPMailer/src/Exception.php';
require '../../plugins/PHPMailer/src/PHPMailer.php';
require '../../plugins/PHPMailer/src/SMTP.php';

 require '../../plugins/vendor/autoload.php';

 $nombres = $_POST['nombre'];
 $email = $_POST['email'];
 $password = $_POST['password']; 
 $confirm_password = $_POST['confirm_password'];
 $rol = $_POST['rol'];
 
 $estado_usuario = 3; 
 $estado_rol = 2;

 $codigo_verificacion = sprintf("%06d", mt_rand(1, 999999));

function validarEmailReal($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    $dominio = substr(strrchr($email, "@"), 1);
    return checkdnsrr($dominio, "MX");
}

if (!validarEmailReal($_POST['email'])) {
    $_SESSION['mensaje_user_error'] = "⚠️ El dominio del correo no parece ser válido o no puede recibir mensajes.";
    header("Location: ../../pages/php/cfg_usuario_listado.php");
    exit();
}

 if ($confirm_password == $password){

    $param_password = password_hash($password, PASSWORD_DEFAULT);

    $sql_usuario = "INSERT INTO persona(nombre, email, password, estatus, token_activacion) 
    VALUES('$nombres','$email','$param_password','$estado_usuario', '$codigo_verificacion')";
    
    $resultado_persona = mysqli_query($conexion, $sql_usuario);
    
    if ($resultado_persona) {
        $id_usuario = $conexion->insert_id;

        $sql_rol = "INSERT INTO detalle_persona_rol(Id_persona, Id_rol, estatus)
        VALUES('$id_usuario','$rol', '$estado_rol')";
        
        $resultado_rol = mysqli_query($conexion, $sql_rol);
        
        if ($resultado_rol) {
            
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
                $mail->addAddress($email, $nombres); 

                $mail->isHTML(true);
                $mail->Subject = 'Activa tu cuenta en el Sistema';
                $mail->Body    = "<h3>¡Hola, $nombres!</h3>
                                  <p>Tu cuenta ha sido creada exitosamente. Para poder ingresar al sistema, primero debes verificar que este correo es tuyo.</p>
                                  <p>Tu código de verificación es:</p>
                                  <h2 style='letter-spacing: 5px; color: #00a65a; font-size: 28px; background: #f4f4f4; padding: 10px; display: inline-block; border-radius: 5px;'>$codigo_verificacion</h2>
                                  <p>Ingresa este código en la ventana de confirmación del sistema para activar tu cuenta.</p>
                                  <p>Si tú no solicitaste este registro, simplemente ignora este mensaje.</p>";

                $mail->send();
                $mensaje_correo = " Se ha enviado un código de activación a su correo.";
            } catch (Exception $e) {
                $mensaje_correo = ", pero no se pudo enviar el correo de activación.";
            }

            $_SESSION['mensaje_user_exito'] = '✅ Éxito: El Usuario ' . $nombres . ' fue registrado.' . $mensaje_correo;
            $_SESSION['requiere_verificacion'] = true;
            $_SESSION['email_verificacion'] = $email;

            header("location: ../../pages/php/cfg_usuario_listado.php");
            exit;
        } else {
            $_SESSION['mensaje_user_error'] = '⚠️ Error: Usuario agregado, pero falló la asignación de rol.';
            header("location: ../../pages/php/cfg_usuario_listado.php");
            exit;
        }
    } else {
        $_SESSION['mensaje_user_error'] = '⚠️ Error: No se pudo agregar al usuario. Email posiblemente ya registrado.';
        header("location: ../../pages/php/cfg_usuario_listado.php");
        exit;
    }

 } else {
   $_SESSION['mensaje_user_error'] = '⚠️ Error de Contraseña: Las contraseñas no coinciden.';
   header("location: ../../pages/php/cfg_usuario_listado.php");
   exit;
 }
?>