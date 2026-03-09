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

$carpeta = __DIR__ . "/email";
if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true) && !is_dir($carpeta)) {
    http_response_code(500);
    echo "Hubo un error al preparar el almacenamiento.";
    exit;
}

$archivo = $carpeta . "/" . time() . "_contacto.txt";

if (file_put_contents($archivo, $contenido) !== false) {
    echo "Formulario enviado correctamente. Gracias por contactarnos.";
    exit;
}

http_response_code(500);
echo "Hubo un error al guardar los datos.";
exit;
?>