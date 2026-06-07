<?php

# Conexión a la base de datos
function conectar_bd() {
    // Desactiva el lanzamiento de excepciones (modo procedural tradicional)
    mysqli_report(MYSQLI_REPORT_OFF);
    $host = getenv('MYSQL_HOST');
    $usuario = getenv('MYSQL_USER');
    $contrasena = getenv('MYSQL_PASSWORD');
    $base_datos = getenv('MYSQL_DATABASE');
    $conn = @mysqli_connect($host, $usuario, $contrasena, $base_datos);
    if (!$conn) {
        return false;
    }
    return $conn;
}

# ==================== PANEL PRINCIPAL ====================
function mostrar_panel() {
    $temperatura = $humedad = $presion = $viento = "No hay datos";
    $media_24h = "No disponible";

    // Intentar leer desde Redis
    $redis = @new Redis();
    if ($redis) {
        @$redis->connect('redis', 6379);
        $cached = @$redis->get('clima_actual');
        if ($cached) {
            $datos = json_decode($cached, true);
            $temperatura = $datos['temperatura'];
            $humedad = $datos['humedad'];
            $presion = $datos['presion'];
            $viento = $datos['viento'];
        }
    }

    // Si Redis no tenía datos, intentar MySQL
    if ($temperatura === "No hay datos") {
        $conn = conectar_bd();
        if ($conn !== false) {
            $sql = "SELECT temperatura, humedad, presion, velocidad_viento 
                    FROM datos_clima ORDER BY fecha_hora DESC LIMIT 1";
            $resultado = mysqli_query($conn, $sql);
            if ($resultado && mysqli_num_rows($resultado) > 0) {
                $fila = mysqli_fetch_assoc($resultado);
                $temperatura = $fila['temperatura'];
                $humedad = $fila['humedad'];
                $presion = $fila['presion'];
                $viento = $fila['velocidad_viento'];
            }
            mysqli_close($conn);
        }
    }

    // Calcular la media (solo si hay conexión, pero la función ya maneja false)
    $media_24h = obtener_media_24h();

    // ---------------- INFORMACIÓN DE SESIÓN ----------------
    session_start();
    $is_logged_in = isset($_SESSION['usuario_id']);
    $username = $_SESSION['usuario_nombre'] ?? '';

    require_once __DIR__ . '/../vistas/panel.php';
}

# Login
function procesar_login() {
    session_start();
    if (isset($_SESSION['usuario_id'])) {
        header('Location: /umbrales');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = $_POST['usuario'] ?? '';
        $password = $_POST['password'] ?? '';
        $conn = conectar_bd();
        $sql = "SELECT id, nombre_usuario FROM usuarios WHERE nombre_usuario = '$usuario' AND contrasena = '$password'";
        $resultado = mysqli_query($conn, $sql);
        if ($resultado && mysqli_num_rows($resultado) == 1) {
            $fila = mysqli_fetch_assoc($resultado);
            $_SESSION['usuario_id'] = $fila['id'];
            $_SESSION['usuario_nombre'] = $fila['nombre_usuario'];
            header('Location: /umbrales');
            exit;
        } else {
            $error = "Usuario o contraseña incorrectos";
        }
        mysqli_close($conn);
    }
    require_once __DIR__ . '/../vistas/login.php';
}

function cerrar_sesion() {
    session_start();
    session_destroy();
    header('Location: /login');
    exit;
}

# Gestión de umbrales
function listar_umbrales() {
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /login');
        exit;
    }
    $conn = conectar_bd();
    $sql = "SELECT * FROM umbrales ORDER BY id DESC";
    $resultado = mysqli_query($conn, $sql);
    $umbrales = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
    mysqli_close($conn);
    require_once __DIR__ . '/../vistas/umbrales/lista.php';
}

function crear_umbral() {
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /login');
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tipo = $_POST['tipo'];
        $operador = $_POST['operador'];
        $valor = $_POST['valor'];
        $activo = isset($_POST['activo']) ? 1 : 0;
        $conn = conectar_bd();
        $sql = "INSERT INTO umbrales (tipo, operador, valor, activo) VALUES ('$tipo', '$operador', '$valor', $activo)";
        mysqli_query($conn, $sql);
        mysqli_close($conn);
        header('Location: /umbrales');
        exit;
    }
    require_once __DIR__ . '/../vistas/umbrales/formulario.php';
}

function editar_umbral() {
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /login');
        exit;
    }
    $id = $_GET['id'] ?? 0;
    if (!$id) {
        header('Location: /umbrales');
        exit;
    }
    $conn = conectar_bd();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $tipo = $_POST['tipo'];
        $operador = $_POST['operador'];
        $valor = $_POST['valor'];
        $activo = isset($_POST['activo']) ? 1 : 0;
        $sql = "UPDATE umbrales SET tipo='$tipo', operador='$operador', valor='$valor', activo=$activo WHERE id=$id";
        mysqli_query($conn, $sql);
        mysqli_close($conn);
        header('Location: /umbrales');
        exit;
    }
    $sql = "SELECT * FROM umbrales WHERE id = $id";
    $resultado = mysqli_query($conn, $sql);
    $umbral = mysqli_fetch_assoc($resultado);
    mysqli_close($conn);
    if (!$umbral) {
        header('Location: /umbrales');
        exit;
    }
    require_once __DIR__ . '/../vistas/umbrales/formulario.php';
}

function eliminar_umbral() {
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /login');
        exit;
    }
    $id = $_GET['id'] ?? 0;
    if ($id) {
        $conn = conectar_bd();
        $sql = "DELETE FROM umbrales WHERE id = $id";
        mysqli_query($conn, $sql);
        mysqli_close($conn);
    }
    header('Location: /umbrales');
    exit;
}

# API para el mapa
function obtener_temperatura_json() {
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
}

#Obtener media de temperatura de las últimas 24 horas
function obtener_media_24h() {
    $conn = conectar_bd();
    if ($conn === false) {
        return "No disponible (BD caída)";
    }
    $sql = "SELECT AVG(temperatura) AS media FROM datos_clima WHERE fecha_hora >= NOW() - INTERVAL 24 HOUR";
    $resultado = mysqli_query($conn, $sql);
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $fila = mysqli_fetch_assoc($resultado);
        $media = round($fila['media'], 1);
        mysqli_close($conn);
        return $media;
    } else {
        mysqli_close($conn);
        return "Sin datos suficientes";
    }
}
