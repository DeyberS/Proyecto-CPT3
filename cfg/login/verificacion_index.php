<?php
session_start();

$mensaje_exito = "";
if (isset($_SESSION['mensaje_user_exito'])) {
    $mensaje_exito = $_SESSION['mensaje_user_exito'];
    unset($_SESSION['mensaje_user_exito']);
}

$requiere_verificacion = false;
$email_verificacion = '';
if (isset($_SESSION['requiere_verificacion'])) {
    $requiere_verificacion = true;
    $email_verificacion = $_SESSION['email_verificacion'];
    unset($_SESSION['requiere_verificacion']);
    unset($_SESSION['email_verificacion']);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'plugins/PHPMailer/src/Exception.php';
require 'plugins/PHPMailer/src/PHPMailer.php';
require 'plugins/PHPMailer/src/SMTP.php';

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: inicio.php");
    exit;
}

require_once "cfg/conexion.php";

$is_first_admin_setup = false;
$sql_check_users = "SELECT email FROM persona";
if ($result_check = mysqli_query($conexion, $sql_check_users)) {
    if (mysqli_num_rows($result_check) == 1) {
        $row = mysqli_fetch_assoc($result_check);
        if ($row['email'] === 'Admin@gmail.com') {
            $is_first_admin_setup = true;
        }
    }
}

$email = $password = "";
$email_err = $password_err = $login_err = "";
$MAX_ATTEMPTS = 3;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor ingrese su correo electronico.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingrese su contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT r.Id_rol, r.nombre_rol, p.id, p.email, p.password, p.nombre, p.estatus, p.login_attempts, p.last_login_attempt 
        FROM persona p 
        JOIN detalle_persona_rol dpr ON p.id = dpr.Id_persona 
        JOIN rol r ON dpr.Id_rol = r.Id_rol WHERE p.email = ? AND p.password IS NOT NULL AND p.password != '' AND p.estatus IN (1, 2)";

        if ($stmt = mysqli_prepare($conexion, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id_rol, $nombre_rol, $id, $email, $hashed_password, $nombre, $estatus, $login_attempts, $last_login_attempt);
                    if (mysqli_stmt_fetch($stmt)) {

                        if ($login_attempts >= $MAX_ATTEMPTS) {
                            $login_err = "Su cuenta ha sido bloqueada por exceder el límite de intentos fallidos. Por favor, notifique a soporte tecnico.";
                        } else {
                            if (password_verify($password, $hashed_password)) {
                                $reset_attempts_sql = "UPDATE persona SET estatus = 1, login_attempts = 0 WHERE id = ?";
                                if ($reset_stmt = mysqli_prepare($conexion, $reset_attempts_sql)) {
                                    mysqli_stmt_bind_param($reset_stmt, "i", $id);
                                    mysqli_stmt_execute($reset_stmt);
                                    mysqli_stmt_close($reset_stmt);
                                }

                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["nombre"] = $nombre;
                                $_SESSION["estatus"] = 1;
                                $_SESSION["rol"] = $id_rol;
                                $_SESSION["nombre_rol"] = $nombre_rol;

                                $sql_permisos = "SELECT p.nombre_permiso 
                                FROM permiso p 
                                INNER JOIN rol_permiso rp ON p.Id_permiso = rp.Id_permiso 
                                WHERE rp.Id_rol = ?";

                                if ($stmt_p = $conexion->prepare($sql_permisos)) {
                                    $stmt_p->bind_param("i", $id_rol);
                                    $stmt_p->execute();
                                    $res_p = $stmt_p->get_result();

                                    $lista_permisos = [];
                                    while ($row_p = $res_p->fetch_assoc()) {
                                        $lista_permisos[] = $row_p['nombre_permiso'];
                                    }
                                    $_SESSION["permisos"] = $lista_permisos;
                                    $stmt_p->close();
                                }

                                header("location: inicio.php");
                                exit;
                            } else {
                                $new_attempts = $login_attempts + 1;
                                $restant_attempts = $MAX_ATTEMPTS - $new_attempts;
                                $increment_attempts_sql = "UPDATE persona SET login_attempts = ?, last_login_attempt = NOW() WHERE id = ?";

                                if ($increment_stmt = mysqli_prepare($conexion, $increment_attempts_sql)) {
                                    mysqli_stmt_bind_param($increment_stmt, "ii", $new_attempts, $id);
                                    mysqli_stmt_execute($increment_stmt);
                                    mysqli_stmt_close($increment_stmt);
                                }

                                if ($new_attempts >= $MAX_ATTEMPTS) {
                                    $login_err = "Contraseña inválida. Su cuenta ha sido bloqueada por exceder el límite de intentos fallidos. Por favor, revise su correo para desbloquearla.";

                                    $mail = new PHPMailer(true);
                                    try {
                                        $mail->isSMTP();
                                        $mail->Host = 'smtp.gmail.com';
                                        $mail->SMTPAuth = true;
                                        $mail->Username = 'cpt3sistema@gmail.com';
                                        $mail->Password = 'Jrjjfgomexsyyxqg';
                                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                        $mail->Port = 587;

                                        $mail->setFrom('cpt3sistema@gmail.com', 'Sistema de Seguridad CPT3');
                                        $mail->addAddress($email);

                                        $mail->isHTML(true);
                                        $mail->Subject = 'AVISO: Cuenta Bloqueada por Seguridad';
                                        $mail->Body    = "
                                            <div style='font-family: sans-serif; border: 1px solid #ddd; padding: 20px; border-radius: 10px;'>
                                                <h2 style='color: crimson;'>Acceso Restringido</h2>
                                                <p>Hola, hemos bloqueado el acceso a tu cuenta debido a que se alcanzó el límite de <b>$MAX_ATTEMPTS intentos fallidos</b>.</p>
                                                <p>Si has sido tú y deseas recuperar el acceso, puedes asesorarte con el administrador o bien, desbloquear tu cuenta haciendo clic en el siguiente botón:</p>
                                                <a href='http://localhost/proyecto-cpt3/recuperacion_bloqueo.php' 
                                                   style='background: crimson; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>
                                                   Desbloquear mi cuenta
                                                </a>
                                            </div>";

                                        $mail->send();
                                    } catch (Exception $e) {
                                    }
                                } else {
                                    $login_err = "Correo o Contraseña Inválido, Tiene Solo" . "&nbsp" . $restant_attempts . "&nbsp" . "intentos restantes";
                                }
                            }
                        }
                    }
                } else {
                    $login_err = "Correo o Contraseña Inválido.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conexion);
}
?>