<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php'; // Archivo que maneja la conexión a la base de datos

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar los datos del formulario
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['cedula'], $data['nombres'], $data['apellidos'], $data['direccion_residencia'], $data['direccion_trabajo'])) {
        echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios.']);
        exit;
    }

    $cedula = filter_var($data['cedula'], FILTER_SANITIZE_STRING);
    $nombres = filter_var($data['nombres'], FILTER_SANITIZE_STRING);
    $apellidos = filter_var($data['apellidos'], FILTER_SANITIZE_STRING);
    $direccion_residencia = filter_var($data['direccion_residencia'], FILTER_SANITIZE_STRING);
    $direccion_trabajo = filter_var($data['direccion_trabajo'], FILTER_SANITIZE_STRING);

    // Validar que las direcciones no estén vacías
    if (empty($direccion_residencia) || empty($direccion_trabajo)) {
        echo json_encode(['success' => false, 'error' => 'Las direcciones no pueden estar vacías.']);
        exit;
    }

    // Geocodificar las direcciones para obtener latitud y longitud
    $latitud_residencia = geocode($direccion_residencia)['lat'];
    $longitud_residencia = geocode($direccion_residencia)['lng'];
    $latitud_trabajo = geocode($direccion_trabajo)['lat'];
    $longitud_trabajo = geocode($direccion_trabajo)['lng'];

    // Verificar si las coordenadas son válidas antes de insertar en la base de datos
    if ($latitud_residencia && $longitud_residencia && $latitud_trabajo && $longitud_trabajo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO estudiantes (cedula, nombres, apellidos, direccion_residencia, direccion_trabajo, latitud_residencia, longitud_residencia, latitud_trabajo, longitud_trabajo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cedula, $nombres, $apellidos, $direccion_residencia, $direccion_trabajo, $latitud_residencia, $longitud_residencia, $latitud_trabajo, $longitud_trabajo]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error al insertar los datos en la base de datos.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al geocodificar las direcciones.']);
    }
    exit;
} elseif ($action === 'get') {
    try {
        $stmt = $pdo->query("SELECT * FROM estudiantes");
        $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($estudiantes);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Error al cargar estudiantes: ' . $e->getMessage()]);
    }
    exit;
}

function geocode($direccion) {
    $apiKey = getenv('GOOGLE_MAPS_API_KEY'); // Usar la variable de entorno para la API Key
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($direccion) . "&key=" . $apiKey;

    // Usar cURL para manejar la petición HTTP
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['status']) && $data['status'] === 'OK') {
        return [
            'lat' => $data['results'][0]['geometry']['location']['lat'],
            'lng' => $data['results'][0]['geometry']['location']['lng']
        ];
    }

    return ['lat' => null, 'lng' => null]; // Devuelve null si no se encuentra la dirección
}
?>
