<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo "Acceso no permitido.";
	exit;
}

const MAX_NAME_LENGTH = 120;
const MAX_MESSAGE_LENGTH = 4000;
const MAX_PHONE_LENGTH = 30;

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
    // Normaliza saltos de línea (CRLF/CR -> LF) y colapsa múltiples líneas vacías.
    $value = preg_replace("/\r\n?|\n/u", "\n", $value) ?? '';
    $value = preg_replace("/\n{3,}/u", "\n\n", $value) ?? '';
    return $value;
}

$email = trim((string) ($_POST['email'] ?? ''));
$nombre = normalizeText((string) ($_POST['name'] ?? ''));
$mensaje = normalizeText((string) ($_POST['message'] ?? ''));
$telefono = normalizeText((string) ($_POST['phone'] ?? ''));

if ($email === '' || $nombre === '' || $mensaje === '') {
    echo "Faltan campos obligatorios.";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "El correo electrónico no es válido.";
    exit;
}

if (textLength($nombre) > MAX_NAME_LENGTH) {
    echo "El nombre excede la longitud permitida.";
    exit;
}

if (textLength($mensaje) > MAX_MESSAGE_LENGTH) {
    echo "El mensaje excede la longitud permitida.";
    exit;
}

if ($telefono !== '' && textLength($telefono) > MAX_PHONE_LENGTH) {
    echo "El teléfono excede la longitud permitida.";
    exit;
}

$contenido = "-------------------------------\n";
$contenido .= "Nombre: $nombre\n";
$contenido .= "Correo Electrónico: $email\n";
$contenido .= "Teléfono: " . ($telefono !== '' ? $telefono : "No proporcionado") . "\n";
$contenido .= "Mensaje:\n$mensaje\n";
$contenido .= "-------------------------------\n\n";

$carpeta = __DIR__ . "/email";
if (!is_dir($carpeta) && !mkdir($carpeta, 0755, true) && !is_dir($carpeta)) {
    echo "Hubo un error al preparar el almacenamiento.";
    exit;
}

$archivo = $carpeta . "/" . time() . "_contacto.txt";

if (file_put_contents($archivo, $contenido) !== false) {
    echo "Formulario enviado correctamente. Gracias por contactarnos.";
    exit;
}

echo "Hubo un error al guardar los datos.";
exit;
?>
