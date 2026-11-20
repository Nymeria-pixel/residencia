<?php
header('Content-Type: application/json; charset=utf-8');

// Habilitar reporte de errores para depuración interna si es necesario, pero asegurar salida JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Capturar errores no controlados y formatearlos en JSON
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error fatal interno: ' . $error['message']
        ]);
        exit;
    }
});

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido. Se requiere POST.'
    ]);
    exit;
}

// Requerir conexión
if (!file_exists('conexion.php')) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se encuentra el archivo de conexión.'
    ]);
    exit;
}
require_once 'conexion.php';

// Validar que la conexión exista y esté activa
if (!isset($conexion) || $conexion->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al conectar con la base de datos.'
    ]);
    exit;
}

// Obtener y sanitizar campos de texto
$nombre = isset($_POST['nombre']) ? trim(htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8')) : '';
$apellido = isset($_POST['apellido']) ? trim(htmlspecialchars($_POST['apellido'], ENT_QUOTES, 'UTF-8')) : '';
$documento = isset($_POST['documento']) ? trim(htmlspecialchars($_POST['documento'], ENT_QUOTES, 'UTF-8')) : '';
$telefono = isset($_POST['telefono']) ? trim(htmlspecialchars($_POST['telefono'], ENT_QUOTES, 'UTF-8')) : '';
$email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
$estado = isset($_POST['estado']) ? trim(htmlspecialchars($_POST['estado'], ENT_QUOTES, 'UTF-8')) : '';
$ciudad = isset($_POST['ciudad']) ? trim(htmlspecialchars($_POST['ciudad'], ENT_QUOTES, 'UTF-8')) : '';
$sector = isset($_POST['sector']) ? trim(htmlspecialchars($_POST['sector'], ENT_QUOTES, 'UTF-8')) : '';
$vivienda = isset($_POST['vivienda']) ? trim(htmlspecialchars($_POST['vivienda'], ENT_QUOTES, 'UTF-8')) : '';
$tiempo_residencia = isset($_POST['tiempo_residencia']) ? intval($_POST['tiempo_residencia']) : 0;
$motivo = isset($_POST['motivo']) ? trim(htmlspecialchars($_POST['motivo'], ENT_QUOTES, 'UTF-8')) : '';

// Validar campos obligatorios
if (
    empty($nombre) || empty($apellido) || empty($documento) || empty($telefono) || empty($email) ||
    empty($estado) || empty($ciudad) || empty($sector) || empty($vivienda)
) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Por favor, complete todos los campos obligatorios del formulario.'
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'El correo electrónico no tiene un formato válido.'
    ]);
    exit;
}

// Asegurar que el directorio de subidas exista
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No se pudo crear el directorio de subida de archivos.'
        ]);
        exit;
    }
}

// Extensiones y tamaño máximo permitidos
$allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB

$ruta_dni = null;
$ruta_recibo = null;

// Función auxiliar para subir archivos
function subirArchivo($fileField, $uploadDir, $allowedExtensions, $maxFileSize)
{
    if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] === UPLOAD_ERR_NO_FILE) {
        return ['status' => 'empty', 'path' => null];
    }

    if ($_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
        return ['status' => 'error', 'message' => 'Error al subir el archivo (Código ' . $_FILES[$fileField]['error'] . ')'];
    }

    $fileSize = $_FILES[$fileField]['size'];
    if ($fileSize > $maxFileSize) {
        return ['status' => 'error', 'message' => 'El archivo supera el tamaño máximo permitido de 5 MB.'];
    }

    $fileName = $_FILES[$fileField]['name'];
    $fileTmpPath = $_FILES[$fileField]['tmp_name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowedExtensions)) {
        return ['status' => 'error', 'message' => 'Extensión de archivo no permitida. Solo se aceptan: ' . implode(', ', $allowedExtensions)];
    }

    // Nombre único para evitar colisiones
    $newFileName = uniqid($fileField . '_', true) . '.' . $fileExtension;
    $destination = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $destination)) {
        return ['status' => 'success', 'path' => $destination];
    } else {
        return ['status' => 'error', 'message' => 'No se pudo mover el archivo al directorio de destino.'];
    }
}

// Subir DNI
if (isset($_FILES['file-dni']) && $_FILES['file-dni']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadDni = subirArchivo('file-dni', $uploadDir, $allowedExtensions, $maxFileSize);
    if ($uploadDni['status'] === 'error') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Identificación: ' . $uploadDni['message']
        ]);
        exit;
    }
    $ruta_dni = $uploadDni['path'];
}

// Subir Recibo
if (isset($_FILES['file-recibo']) && $_FILES['file-recibo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadRecibo = subirArchivo('file-recibo', $uploadDir, $allowedExtensions, $maxFileSize);
    if ($uploadRecibo['status'] === 'error') {
        // Eliminar DNI previamente subido para evitar basura
        if ($ruta_dni && file_exists($ruta_dni)) {
            unlink($ruta_dni);
        }
        echo json_encode([
            'status' => 'error',
            'message' => 'Recibo de servicio: ' . $uploadRecibo['message']
        ]);
        exit;
    }
    $ruta_recibo = $uploadRecibo['path'];
}

// Insertar datos en la base de datos utilizando Prepared Statements
$sql = "INSERT INTO solicitudes_residencia (
            nombre, apellido, documento, telefono, email, estado, ciudad, sector, vivienda, tiempo_residencia, ruta_dni, ruta_recibo, motivo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    // Eliminar archivos subidos si falla la preparación
    if ($ruta_dni && file_exists($ruta_dni))
        unlink($ruta_dni);
    if ($ruta_recibo && file_exists($ruta_recibo))
        unlink($ruta_recibo);

    echo json_encode([
        'status' => 'error',
        'message' => 'Error al preparar la base de datos: ' . $conexion->error
    ]);
    exit;
}

$stmt->bind_param(
    "sssssssssisss",
    $nombre,
    $apellido,
    $documento,
    $telefono,
    $email,
    $estado,
    $ciudad,
    $sector,
    $vivienda,
    $tiempo_residencia,
    $ruta_dni,
    $ruta_recibo,
    $motivo
);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Solicitud guardada correctamente',
        'id' => $conexion->insert_id
    ]);
} else {
    // Eliminar archivos subidos si falla la ejecución
    if ($ruta_dni && file_exists($ruta_dni))
        unlink($ruta_dni);
    if ($ruta_recibo && file_exists($ruta_recibo))
        unlink($ruta_recibo);

    echo json_encode([
        'status' => 'error',
        'message' => 'Error al ejecutar el guardado: ' . $stmt->error
    ]);
}

$stmt->close();
$conexion->close();
?>