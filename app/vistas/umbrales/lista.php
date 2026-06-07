<!DOCTYPE html>
<html>
<head>
    <title>Gestión de umbrales</title>
    <link rel="stylesheet" href="/css/estilos.css">
</head>
<body>
<div class="contenedor">
    <h1>Gestión de umbrales</h1>
    <p>Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></p>
    <table>
        <thead>
            <tr><th>ID</th><th>Tipo</th><th>Operador</th><th>Valor</th><th>Activo</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($umbrales as $u): ?>
        <tr>
            <td><?php echo $u['id']; ?></td>
            <td><?php echo $u['tipo']; ?></td>
            <td><?php echo $u['operador']; ?></td>
            <td><?php echo $u['valor']; ?></td>
            <td><?php echo $u['activo'] ? 'Sí' : 'No'; ?></td>
            <td>
                <a href="/umbrales/editar?id=<?php echo $u['id']; ?>" class="enlace-boton">Editar</a>
                <a href="/umbrales/eliminar?id=<?php echo $u['id']; ?>" class="enlace-boton eliminar" onclick="return confirm('¿Eliminar?')">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="acciones">
        <a href="/umbrales/crear" class="enlace-boton">Crear nuevo umbral</a>
        <a href="/panel" class="enlace-boton">Volver al panel</a>
        <a href="/logout" class="enlace-boton">Cerrar sesión</a>
    </div>
</div>
</body>
</html>