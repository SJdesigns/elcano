<?php
// Ruta del archivo a leer (ajusta esto a tu necesidad)

$rutaArchivo = $_POST['ruta'];

// Verificar si el archivo existe y es legible
if (file_exists($rutaArchivo) && is_readable($rutaArchivo)) {
    // Abrir el archivo en modo lectura
    $archivo = fopen($rutaArchivo, 'r');

    // Leer el archivo línea por línea y almacenar en un array
    $lineas = [];
    while (($linea = fgets($archivo)) !== false) {
        $lineas[] = $linea; // Eliminar espacios en blanco al inicio y final de cada línea
    }
    fclose($archivo);

    // Codificar el array como JSON para enviar a JavaScript
    $json = json_encode($lineas);

    // Enviar la respuesta como JSON
    header('Content-Type: application/json');
    echo $json;
} else {
    // Si el archivo no existe o no se puede leer, enviar un mensaje de error
    echo json_encode(['error' => 'No se pudo leer el archivo']);
}

?>