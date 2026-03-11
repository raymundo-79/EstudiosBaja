<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Acceso no permitido.";
    exit;
}

const MAX_EMAIL_LENGTH = 254;
const MAX_NAME_LENGTH = 120;
const MIN_NAME_LENGTH = 2;
const MAX_MESSAGE_LENGTH = 4000;
const MIN_MESSAGE_LENGTH = 10;
const MAX_PHONE_LENGTH = 30;
const MIN_PHONE_LENGTH = 7;

const RATE_LIMIT_MAX_REQUESTS = 5;
const RATE_LIMIT_WINDOW_SECONDS = 600;
const MIN_FORM_AGE_SECONDS = 3;
const MAX_FORM_AGE_SECONDS = 86400;

function textLength(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    return strlen($value);
}

function normalizeText(string $value): string
{
    $value = trim($value);
    $value = preg_replace("/\r\n?|\n/u", "\n", $value) ?? '';
    $value = preg_replace("/\n{3,}/u", "\n\n", $value) ?? '';
    return $value;
}

function getClientIp(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return preg_replace('/[^0-9a-fA-F:\\.]/', '', (string) $ip) ?: '0.0.0.0';
}

function checkRateLimit(string $ip): bool
{
    $rateDir = __DIR__ . '/email/.rate_limit';
    if (!is_dir($rateDir) && !mkdir($rateDir, 0755, true) && !is_dir($rateDir)) {
        return false;
    }

    $file = $rateDir . '/' . hash('sha256', $ip) . '.json';
    $now = time();
    $windowStart = $now - RATE_LIMIT_WINDOW_SECONDS;

    $entries = [];
    if (is_file($file)) {
        $decoded = json_decode((string) file_get_contents($file), true);
        if (is_array($decoded)) {
            $entries = array_values(array_filter($decoded, static function ($timestamp) use ($windowStart) {
                return is_int($timestamp) && $timestamp >= $windowStart;
            }));
        }
    }

    if (count($entries) >= RATE_LIMIT_MAX_REQUESTS) {
        return false;
    }

    $entries[] = $now;
    file_put_contents($file, json_encode($entries));
    return true;
}

$clientIp = getClientIp();
if (!checkRateLimit($clientIp)) {
    http_response_code(429);
    echo "Demasiadas solicitudes. Intenta de nuevo en unos minutos.";
    exit;
}

$honeypot = trim((string) ($_POST['website'] ?? ''));
if ($honeypot !== '') {
    http_response_code(400);
    echo "Solicitud inválida.";
    exit;
}

$formTimestamp = (int) ($_POST['form_ts'] ?? 0);
$formAge = time() - $formTimestamp;
if ($formTimestamp <= 0 || $formAge < MIN_FORM_AGE_SECONDS || $formAge > MAX_FORM_AGE_SECONDS) {
    http_response_code(400);
    echo "Formulario inválido o expirado.";
    exit;
}

$email = trim((string) ($_POST['email'] ?? ''));
$nombre = normalizeText((string) ($_POST['name'] ?? ''));
$mensaje = normalizeText((string) ($_POST['message'] ?? ''));
$telefono = normalizeText((string) ($_POST['phone'] ?? ''));

if ($email === '' || $nombre === '' || $mensaje === '') {
    http_response_code(400);
    echo "Faltan campos obligatorios.";
    exit;
}

if (textLength($email) > MAX_EMAIL_LENGTH || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "El correo electrónico no es válido.";
    exit;
}

$nameLength = textLength($nombre);
if ($nameLength < MIN_NAME_LENGTH || $nameLength > MAX_NAME_LENGTH) {
    http_response_code(400);
    echo "El nombre debe tener entre 2 y 120 caracteres.";
    exit;
}

$messageLength = textLength($mensaje);
if ($messageLength < MIN_MESSAGE_LENGTH || $messageLength > MAX_MESSAGE_LENGTH) {
    http_response_code(400);
    echo "El mensaje debe tener entre 10 y 4000 caracteres.";
    exit;
}

if ($telefono !== '') {
    $phoneLength = textLength($telefono);
    if ($phoneLength < MIN_PHONE_LENGTH || $phoneLength > MAX_PHONE_LENGTH) {
        http_response_code(400);
        echo "El teléfono debe tener entre 7 y 30 caracteres.";
        exit;
    }

    if (!preg_match('/^[0-9+()\-\s]+$/', $telefono)) {
        http_response_code(400);
        echo "El teléfono contiene caracteres no permitidos.";
        exit;
    }
}

$contenido = "-------------------------------\n";
$contenido .= "Nombre: $nombre\n";
$contenido .= "Correo Electrónico: $email\n";
$contenido .= "Teléfono: " . ($telefono !== '' ? $telefono : "No proporcionado") . "\n";
$contenido .= "Mensaje:\n$mensaje\n";
$contenido .= "-------------------------------\n\n";

$carpeta = __DIR__ . '/email';
if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true) && !is_dir($carpeta)) {
    http_response_code(500);
    echo "Hubo un error al preparar el almacenamiento.";
    exit;
}

$archivo = $carpeta . '/' . time() . '_contacto.txt';

if (file_put_contents($archivo, $contenido) !== false) {
    echo "Formulario enviado correctamente. Gracias por contactarnos.";
    exit;
}

http_response_code(500);
echo "Hubo un error al guardar los datos.";
exit;
?>
