<?php
require_once __DIR__ . '/../app/core/funciones.php';
header('Content-Type: application/json');

$conn = conectar_bd();
$sql = "SELECT temperatura FROM datos_clima ORDER BY fecha_hora DESC LIMIT 1";
$resultado = mysqli_query($conn, $sql);
if (mysqli_num_rows($resultado) > 0) {
    $fila = mysqli_fetch_assoc($resultado);
    echo json_encode(['temperatura' => $fila['temperatura']]);
} else {
    echo json_encode(['error' => 'No hay datos']);
}
mysqli_close($conn);