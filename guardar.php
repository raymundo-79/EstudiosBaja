<?php
// Verifica que se haya enviado por POST
/*
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST["email"]);
    $nombre = htmlspecialchars($_POST["name"]);
    $mensaje = htmlspecialchars($_POST["message"]);
    $telefono = htmlspecialchars($_POST["phone"]);
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $nombre = $_POST['name'] ?? '';
    $mensaje = $_POST['message'] ?? '';
    $telefono = $_POST['phone'] ?? '';

    // Formatear el contenido
    $contenido = "-------------------------------\n";
    $contenido .= "Nombre: $nombre\n";
    $contenido .= "Correo Electrónico: $email\n";
    $contenido .= "Teléfono: " . ($telefono ?: "No proporcionado") . "\n";
    $contenido .= "Mensaje: $mensaje\n";
    $contenido .= "-------------------------------\n\n";

    // Asegúrate de que la carpeta email exista
    $carpeta = __DIR__ . "/email";
    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    // Generar nombre de archivo único
    $archivo = $carpeta . "/" . time() . "_contacto.txt";

    // Guardar contenido
    if (file_put_contents($archivo, $contenido)) {
	    
		echo "Formulario enviado correctamente. Gracias por contactarnos.";
		//$mensaje = urlencode("Formulario enviado correctamente. Gracias por contactarnos.");
		//header("Location: contact.html?mensaje=$mensaje");
		exit;
    } else {
	    echo "Hubo un error al guardar los datos.";
	    //$mensaje = urlencode("Hubo un error al guardar los datos.");
		//header("Location: contact.html?mensaje=$mensaje");
		exit;
    }
} else {
	    echo "Acceso no permitido.";
	    //$mensaje = urlencode("Acceso no permitido.");
		//header("Location: contact.html?mensaje=$mensaje");
		exit;
}
?>
