<?php
require_once __DIR__ . '/../app/core/funciones.php';

$uri = $_SERVER['REQUEST_URI'];
$uri = strtok($uri, '?');

if ($uri === '/' || $uri === '/panel') {
    mostrar_panel();
} elseif ($uri === '/login') {
    procesar_login();
} elseif ($uri === '/logout') {
    cerrar_sesion();
} elseif ($uri === '/umbrales') {
    listar_umbrales();
} elseif ($uri === '/umbrales/crear') {
    crear_umbral();
} elseif ($uri === '/umbrales/editar') {
    editar_umbral();
} elseif ($uri === '/umbrales/eliminar') {
    eliminar_umbral();
} elseif ($uri === '/api/historico') {
    obtener_historico_json();
} else {
    http_response_code(404);
    echo "404 - Página no encontrada";
}