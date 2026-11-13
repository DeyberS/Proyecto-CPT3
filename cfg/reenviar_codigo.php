<?php
session_start();
require_once "conexion.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../plugins/PHPMailer/src/Exception.php';
require '../plugins/PHPMailer/src/PHPMailer.php';
require '../plugins/PHPMailer/src/SMTP.php';
require '../plugins/vendor/autoload.php';

header('Content-Type: application/json');

$email = isset($_POST['email']) ? mysqli_real_escape_string($conexion, trim($_POST['email'])) : '';
$tipo  = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'usuario'; // 'medico' o 'usuario'

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'No se recibió el correo.']);
    exit;
}

// 1. Verificar que la persona exista e ingrese inactiva (estatus 3)
$sql_buscar = "SELECT nombre FROM persona WHERE email = '$email' AND estatus = '3' LIMIT 1";
$resultado = mysqli_query($conexion, $sql_buscar);

if (mysqli_num_rows($resultado) > 0) {
    $row = mysqli_fetch_assoc($resultado);
    $nombres = $row['nombre'];

    // 2. Generar un NUEVO código de 6 dígitos
    $nuevo_codigo = sprintf("%06d", mt_rand(1, 999999));

    // 3. Actualizar el código en la base de datos
    $sql_update = "UPDATE persona SET token_activacion = '$nuevo_codigo' WHERE email = '$email'";
    
    if (mysqli_query($conexion, $sql_update)) {
        
        // Configurar texto según el rol/pestaña
        $asuntoRole = ($tipo === 'medico') ? 'Verificación de Cuenta Médica' : 'Código de Activación de Usuario';
        $subtitulo  = ($tipo === 'medico') ? 'Has solicitado un nuevo código para tu cuenta médica.' : 'Has solicitado un nuevo código de verificación.';

        // 4. Enviar el nuevo correo
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
            $mail->Subject = "$asuntoRole - Sistema CPT3";
            $mail->Body    = "<h3>¡Hola, $nombres!</h3>
                              <p>$subtitulo</p>
                              <p>Tu nuevo código es:</p>
                              <h2 style='letter-spacing: 5px; color: #00a65a; font-size: 28px; background: #f4f4f4; padding: 10px; display: inline-block; border-radius: 5px;'>$nuevo_codigo</h2>
                              <p>Ingresa este código en el sistema antes de que expire el tiempo.</p>";

            $mail->send();
            
            echo json_encode(['success' => true, 'message' => '✅ Nuevo código enviado al correo.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => '❌ Error al enviar el correo.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => '❌ Error al actualizar el código en la base de datos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '⚠️ Persona no encontrada o la cuenta ya está activa.']);
}
?>