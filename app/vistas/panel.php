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
    <h1>Panel Meteorológico en Tiempo Real</h1>
    <div class="datos">
        <div class="tarjeta">
            <h3>Temperatura</h3>
            <div class="valor"><?php echo $temperatura; ?> <span class="unidad">°C</span></div>
        </div>
        <div class="tarjeta">
            <h3>Humedad</h3>
            <div class="valor"><?php echo $humedad; ?> <span class="unidad">%</span></div>
        </div>
        <div class="tarjeta">
            <h3>Presión</h3>
            <div class="valor"><?php echo $presion; ?> <span class="unidad">hPa</span></div>
        </div>
        <div class="tarjeta">
            <h3>Viento</h3>
            <div class="valor"><?php echo $viento; ?> <span class="unidad">m/s</span></div>
        </div>
    </div>
    <h2 style="text-align: center; margin-top: 30px; color: white;">🗺️ Ubicación: Pamplona</h2>
    <div id="map" style="height: 400px; width: 100%; max-width: 800px; margin: 0 auto; border-radius: 15px;"></div>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var lat = 42.8125;
        var lon = -1.6458;
        var map = L.map('map').setView([lat, lon], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        fetch('/api_temperatura.php')
            .then(response => response.json())
            .then(data => {
                var temp = data.temperatura ? data.temperatura : '?';
                L.marker([lat, lon]).addTo(map)
                    .bindPopup('Temperatura actual: ' + temp + ' °C')
                    .openPopup();
            })
            .catch(error => {
                console.error('Error al cargar temperatura:', error);
                L.marker([lat, lon]).addTo(map)
                    .bindPopup('Error al obtener temperatura')
                    .openPopup();
            });
    </script>
    <footer>Datos actualizados automáticamente cada 10 minutos | <?php echo date('d/m/Y H:i:s'); ?></footer>
</div>
</body>
</html>