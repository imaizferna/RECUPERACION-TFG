<?php
require_once __DIR__ . '/../app/core/funciones.php';

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

$conn = conectar_bd();
$sql = "INSERT INTO datos_clima (temperatura, humedad, presion, velocidad_viento, fecha_hora) 
        VALUES ($temperatura, $humedad, $presion, $viento, NOW())";
if (mysqli_query($conn, $sql)) {
    echo "Datos guardados correctamente: $temperatura °C, $humedad% humedad, $presion hPa, $viento m/s\n";
} else {
    echo "Error al guardar: " . mysqli_error($conn) . "\n";
}
mysqli_close($conn);