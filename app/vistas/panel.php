<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Meteorológico</title>
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body>
<div class="contenedor">
    <h1>🌤️ Panel Meteorológico en Tiempo Real</h1>
    <div class="datos">
        <div class="tarjeta">
            <h3>🌡️ Temperatura</h3>
            <div class="valor"><?php echo $temperatura; ?> <span class="unidad">°C</span></div>
        </div>
        <div class="tarjeta">
            <h3>💧 Humedad</h3>
            <div class="valor"><?php echo $humedad; ?> <span class="unidad">%</span></div>
        </div>
        <div class="tarjeta">
            <h3>⏲️ Presión</h3>
            <div class="valor"><?php echo $presion; ?> <span class="unidad">hPa</span></div>
        </div>
        <div class="tarjeta">
            <h3>💨 Viento</h3>
            <div class="valor"><?php echo $viento; ?> <span class="unidad">m/s</span></div>
        </div>
    </div>
    <footer>Datos actualizados automáticamente cada 10 minutos | <?php echo date('d/m/Y H:i:s'); ?></footer>
</div>
</body>
</html>