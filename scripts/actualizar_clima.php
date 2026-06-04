<?php
require_once __DIR__ . '/../app/core/funciones.php';
require_once __DIR__ . '/../vendor/autoload.php';  # Necesario para PHPMailer

use PHPMailer\PHPMailer\PHPMailer; # Una clase de PHPMailer para enviar correos
use PHPMailer\PHPMailer\Exception; # Manejo de excepciones de PHPMailer

# OBTENER DATOS DE LA API
$api_key = getenv('OPENWEATHER_API_KEY');
$ciudad = 'Pamplona';
$url = "https://api.openweathermap.org/data/2.5/weather?q=$ciudad&appid=$api_key&units=metric";

$respuesta = file_get_contents($url);
if ($respuesta === false) {
    die("Error: No se pudo conectar con la API de OpenWeatherMap");
}

$datos = json_decode($respuesta, true);
if (!isset($datos['main'])) {
    die("Error: La API no devolvió datos válidos.");
}

$temperatura = $datos['main']['temp'];
$humedad = $datos['main']['humidity'];
$presion = $datos['main']['pressure'];
$viento = $datos['wind']['speed'];

# GUARDAR EN BASE DE DATOS
$conn = conectar_bd();
$sql = "INSERT INTO datos_clima (temperatura, humedad, presion, velocidad_viento, fecha_hora) 
        VALUES ($temperatura, $humedad, $presion, $viento, NOW())";
if (mysqli_query($conn, $sql)) {
    echo "✅ Datos guardados: $temperatura °C, $humedad% humedad, $presion hPa, $viento m/s\n";
} else {
    echo "❌ Error al guardar: " . mysqli_error($conn) . "\n";
}

# COMPROBAR UMBRALES
$sql = "SELECT * FROM umbrales WHERE activo = 1";
$resultado = mysqli_query($conn, $sql);
$umbrales = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

$alertas_disparadas = [];  # Inicializar array

foreach ($umbrales as $umbral) {
    $tipo = $umbral['tipo'];
    $operador = $umbral['operador'];
    $valor_umbral = $umbral['valor'];
    $valor_real = null;

    if ($tipo == 'temperatura') $valor_real = $temperatura;
    elseif ($tipo == 'humedad') $valor_real = $humedad;
    elseif ($tipo == 'viento') $valor_real = $viento;

    if ($valor_real === null) continue;

    $disparar = false;
    if ($operador == '>' && $valor_real > $valor_umbral) $disparar = true;
    if ($operador == '<' && $valor_real < $valor_umbral) $disparar = true;
    if ($operador == '>=' && $valor_real >= $valor_umbral) $disparar = true;
    if ($operador == '<=' && $valor_real <= $valor_umbral) $disparar = true;
    if ($operador == '=' && $valor_real == $valor_umbral) $disparar = true;

    if ($disparar) {
        $alertas_disparadas[] = [
            'tipo' => $tipo,
            'valor' => $valor_real,
            'umbral' => $valor_umbral,
            'operador' => $operador
        ];
    }
}

# ENVÍO DE ALERTAS
if (count($alertas_disparadas) > 0) {
    $mensaje = "ALERTA METEOROLÓGICA\nSe han superado los siguientes umbrales:\n";
    foreach ($alertas_disparadas as $a) {
        $mensaje .= "- $a[tipo]: $a[valor] $a[operador] $a[umbral]\n";
    }

    # Enviar email
    $smtp_host = getenv('SMTP_HOST');
    $smtp_user = getenv('SMTP_USER');
    $smtp_pass = getenv('SMTP_PASS');
    $dest_email = getenv('ALERT_EMAIL');
    if ($smtp_host && $smtp_user && $smtp_pass && $dest_email) {
        $mail = new PHPMailer(true); # Crear instancia de PHPMailer
        try {
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_user;
            $mail->Password = $smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = getenv('SMTP_PORT') ?: 587;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($smtp_user, 'Panel Meteorológico');
            $mail->addAddress($dest_email);
            $mail->Subject = '⚠️ Alerta meteorológica';
            $mail->Body = $mensaje;
            $mail->send();
            echo "📧 Alerta enviada por email.\n";
        } catch (Exception $e) {
            echo "❌ Error al enviar email: {$mail->ErrorInfo}\n";
        }
    } else {
        echo "⚠️ Faltan credenciales de correo en el archivo .env\n";
    }

    # Enviar mensaje por Telegram
    $telegram_token = getenv('TELEGRAM_BOT_TOKEN');
    $chat_id = getenv('TELEGRAM_CHAT_ID');
    if ($telegram_token && $chat_id) {
        $url = "https://api.telegram.org/bot{$telegram_token}/sendMessage";
        $data = ['chat_id' => $chat_id, 'text' => $mensaje];
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data)
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result !== false) {
            echo "🤖 Alerta enviada por Telegram.\n";
        } else {
            echo "❌ Error al enviar Telegram.\n";
        }
    } else {
        echo "⚠️ Faltan credenciales de Telegram en el archivo .env\n";
    }
}

mysqli_close($conn);