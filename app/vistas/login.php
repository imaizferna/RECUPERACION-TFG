<!DOCTYPE html>
<html>
<head>
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body>
<div class="contenedor">
    <h1>Iniciar sesión</h1>
    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="/login" class="formulario">
        <label>Usuario:</label>
        <input type="text" name="usuario" required>
        <label>Contraseña:</label>
        <input type="password" name="password" required>
        <button type="submit">Entrar</button>
    </form>
</div>
</body>
</html>