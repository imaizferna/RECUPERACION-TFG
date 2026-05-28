<!DOCTYPE html>
<html>
<head>
    <title><?php echo isset($umbral) ? 'Editar umbral' : 'Nuevo umbral'; ?></title>
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body>
<div class="contenedor">
    <h1><?php echo isset($umbral) ? 'Editar umbral' : 'Nuevo umbral'; ?></h1>
    <form method="post" class="formulario">
        <label>Tipo:</label>
        <select name="tipo" required>
            <option value="temperatura" <?php if (isset($umbral) && $umbral['tipo'] == 'temperatura') echo 'selected'; ?>>Temperatura</option>
            <option value="humedad" <?php if (isset($umbral) && $umbral['tipo'] == 'humedad') echo 'selected'; ?>>Humedad</option>
            <option value="viento" <?php if (isset($umbral) && $umbral['tipo'] == 'viento') echo 'selected'; ?>>Viento</option>
        </select>

        <label>Operador:</label>
        <select name="operador" required>
            <option value=">" <?php if (isset($umbral) && $umbral['operador'] == '>') echo 'selected'; ?>>Mayor que</option>
            <option value="<" <?php if (isset($umbral) && $umbral['operador'] == '<') echo 'selected'; ?>>Menor que</option>
        </select>

        <label>Valor:</label>
        <input type="number" step="any" name="valor" value="<?php echo isset($umbral) ? $umbral['valor'] : ''; ?>" required>

        <label>Activo:</label>
        <input type="checkbox" name="activo" value="1" <?php if (isset($umbral) && $umbral['activo']) echo 'checked'; ?>>

        <button type="submit">Guardar</button>
        <a href="/umbrales" class="cancelar">Cancelar</a>
    </form>
</div>
</body>
</html>