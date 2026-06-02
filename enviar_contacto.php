<?php
// 1. Importar los espacios de nombres oficiales de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// 2. Cargar el autoloader mágico de Composer
// Esto incluye automáticamente todas las librerías instaladas por la terminal
require 'vendor/autoload.php';

// 3. Verificar que la petición venga realmente desde el botón del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_contacto'])) {
    
    // Capturamos los datos del formulario usando los 'name' de tu HTML
    $nombre  = $_POST['nombre'];
    $email   = $_POST['correo'];  // Correo ingresado por el visitante
    $asunto  = $_POST['asunto'];
    $mensaje = $_POST['mensaje'];

    // Instanciamos el objeto de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // 4. CONFIGURACIÓN DEL SERVIDOR SMTP
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com';                       // Servidor SMTP de Gmail
        $mail->SMTPAuth   = true;                                   // Activar autenticación SMTP
        $mail->Username   = 'vicentemauricio3003@gmail.com';           // TU GMAIL (El que despachará los correos)
        $mail->Password   = 'kglp imlm rqbt qcns';          // TU CONTRASEÑA DE APLICACIÓN DE GMAIL
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Cifrado de seguridad TLS
        $mail->Port       = 587;                                    // Puerto para TLS
        $mail->setLanguage('es');                                   // Configurar errores en español

        // 5. REMITENTES Y DESTINATARIOS
        // El emisor debe ser el mismo correo SMTP configurado arriba para evitar bloqueos
        $mail->setFrom('vicentemauricio3003@gmail.com', 'Contacto Portafolio');
        
        // A dónde llegará el correo (Tu cuenta personal donde lees tus cosas)
        $mail->addAddress('vicentemauricio2002@gmail.com', 'Vicente Ortiz');

        // LA MAGIA: Cuando le des a "Responder" en Gmail, le responderás al visitante
        $mail->addReplyTo($email, $nombre);

        // 6. CONTENIDO DEL MENSAJE
        $mail->isHTML(false); // Texto plano para asegurar que llegue limpio y sin problemas
        $mail->Subject = "Contacto Portafolio: " . $asunto;
        
        // Estructuramos el cuerpo del texto para que lo leas ordenado en tu bandeja
        $cuerpo  = "Has recibido un nuevo mensaje desde el formulario de tu Portafolio Web:\n\n";
        $cuerpo .= "---------------------------------------------------------\n";
        $cuerpo .= "Nombre del Remitente: " . $nombre . "\n";
        $cuerpo .= "Correo de Contacto:   " . $email . "\n";
        $cuerpo .= "Asunto Original:       " . $asunto . "\n";
        $cuerpo .= "---------------------------------------------------------\n\n";
        $cuerpo .= "Mensaje:\n" . $mensaje . "\n";
        
        $mail->Body = $cuerpo;

        // 7. ENVIAR CORREO Y REDIRECCIONAR
        $mail->send();
        
        // Alerta de éxito y volvemos al portafolio
        echo "<script>
                alert('¡Mensaje enviado con éxito! Llegará directo a la bandeja de entrada.'); 
                window.location.href='index.php#contacto';
              </script>";

    } catch (Exception $e) {
        // Si hay un error (ej. sin internet o datos de acceso malos), te avisa sin caerse la página
        echo "<script>
                alert('Ocurrió un error al intentar enviar el mensaje: {$mail->ErrorInfo}'); 
                window.location.href='index.php#contacto';
              </script>";
    }
} else {
    // Si alguien intenta ingresar directo a enviar_contacto.php por la URL, lo rebota al index
    header("Location: index.php");
    exit();
}