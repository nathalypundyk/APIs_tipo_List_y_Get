<?php
/**
 * usuarios/api.php
 *
 * Este archivo actúa como el "endpoint" de la API para gestionar los usuarios.
 * Recibe peticiones HTTP (GET/POST), procesa la acción solicitada y devuelve
 * una respuesta en formato JSON.
 */

// 1. Incluir archivos de configuración y conexión a la base de datos.
//    Asegúrate de que estas rutas sean correctas para tu proyecto.
require_once '../lib/conf.php';
require_once '../lib/conexionDB.php';

// 2. Establecer el encabezado de respuesta.
//    Esto le dice al navegador (o a cualquier cliente que consuma la API)
//    que el contenido que se va a enviar es de tipo JSON.
header('Content-Type: application/json');

// 3. Inicializar la conexión a la base de datos.
//    Creamos una instancia de nuestra clase ConexionDB y obtenemos el objeto de conexión mysqli.
$conex = new ConexionDB();
$conex->conectar(); // Intentar establecer la conexión
$con = $conex->conex; // Obtener el objeto mysqli

// 4. Verificar si la conexión a la base de datos fue exitosa.
//    Si hay un error de conexión, devolvemos una respuesta JSON con un mensaje de error
//    y terminamos la ejecución del script.
if ($con->connect_error) {
    echo json_encode([
        'rows' => 0, // No hay filas de datos
        'data' => [], // No hay datos
        'msg' => 'Error de conexión a la base de datos ' . $con->connect_error,
        'status' => 'FAIL' // Estado de fallo
    ]);
    exit(); // Detener la ejecución
}

// 5. Obtener la acción solicitada de la petición.
//    Usamos $_REQUEST para poder capturar el parámetro 'action' tanto si viene por GET
//    (ej. ?action=list) como por POST (en el cuerpo de la petición).
//    Si 'action' no está definido, se asigna una cadena vacía.
$action = $_REQUEST['action'] ?? '';

// 6. Inicializar la estructura de respuesta estándar.
//    Definimos una estructura JSON base que usaremos para todas nuestras respuestas.
//    Esto asegura consistencia en cómo devolvemos los datos y mensajes.
$response = [
    'rows' => 0,     // Cantidad de filas de datos devueltas
    'data' => [],    // Los datos en sí (puede ser un array de objetos, un solo objeto, o vacío)
    'msg' => '',     // Mensaje descriptivo sobre el resultado de la operación
    'status' => 'FAIL' // Estado de la operación: 'OK' para éxito, 'FAIL' para error
];

// 7. Usar una estructura switch para manejar las diferentes acciones.
//    Dependiendo del valor de $action, ejecutaremos un bloque de código diferente.
switch ($action) {
case 'list':
        // Acción: list (Obtener todos los usuarios)
        // Esta acción se ejecuta cuando la petición es algo como:
        // GET http://tu_servidor/tu_proyecto/usuarios/api.php?action=list

        // 1. Construir la consulta SQL para seleccionar todos los usuarios.
        $sql = "SELECT * FROM usuarios";

        // 2. Ejecutar la consulta en la base de datos.
        $rs = $con->query($sql); // $con es el objeto mysqli de tu conexión

        // 3. Verificar si la consulta se ejecutó con éxito.
        if ($rs) {
            $usuarios = []; // Inicializamos un array vacío para guardar los usuarios.

            // 4. Recorrer los resultados y añadir cada usuario al array $usuarios.
            //    fetch_assoc() devuelve cada fila como un array asociativo (clave => valor).
            while ($fila = $rs->fetch_assoc()) {
                $usuarios[] = $fila;
            }

            // 5. Liberar la memoria asociada con el resultado de la consulta.
            $rs->free(); // Es una buena práctica liberar los recursos de resultados.

            // 6. Actualizar la estructura de respuesta para indicar éxito.
            $response['status'] = 'OK'; // La operación fue exitosa.
            $response['data'] = $usuarios; // Los datos son el array de usuarios.
            $response['rows'] = count($usuarios); // El número de filas es la cantidad de usuarios.
            $response['msg'] = 'Lista de usuarios obtenida con éxito.'; // Mensaje descriptivo.
        } else {
            // Si la consulta falló, actualizamos la respuesta con un mensaje de error.
            $response['msg'] = 'Error al leer usuarios: ' . $con->error; // Incluimos el error de MySQL.
            // El status ya está en 'FAIL' por defecto, no necesitamos cambiarlo.
        }
        break; // Importante: salir del switch después de manejar la acción.    
        
        case 'get':
        // Acción: get (Obtener un usuario específico por ID)
        // Esta acción se ejecuta cuando la petición es algo como:
        // GET http://tu_servidor/tu_proyecto/usuarios/api.php?action=get&id=1

        // 1. Obtener el ID del usuario de los parámetros GET.
        //    Si 'id' no está definido o es 0, se asigna 0.
        $id = $_GET['id'] ?? 0;

        // 2. Validar que el ID sea un número válido y mayor que 0.
        if ($id > 0) {
            // intval() convierte a entero, es una buena práctica de sanitización
            // para IDs numéricos que vienen de la URL.
            $id = intval($id);

            // 3. Construir la consulta SQL para seleccionar el usuario específico.
            $sql = "SELECT * FROM usuarios WHERE id = $id";

            // 4. Ejecutar la consulta en la base de datos.
            $rs = $con->query($sql);

            // 5. Verificar si la consulta se ejecutó con éxito.
            if ($rs) {
                // 6. Verificar si se encontró algún registro.
                if ($rs->num_rows > 0) {
                    $usuario = $rs->fetch_assoc(); // Obtener la única fila como array asociativo.

                    // 7. Actualizar la estructura de respuesta para indicar éxito.
                    $response['status'] = 'OK';
                    $response['data'] = $usuario; // Los datos son el objeto del usuario encontrado.
                    $response['rows'] = 1; // Se encontró 1 fila.
                    $response['msg'] = 'Usuario obtenido con éxito.';
                } else {
                    // Si num_rows es 0, significa que no se encontró un usuario con ese ID.
                    $response['msg'] = 'Usuario no encontrado.';
                }
                // 8. Liberar la memoria asociada con el resultado de la consulta.
                $rs->free();
            } else {
                // Si la consulta falló, actualizar la respuesta con un mensaje de error.
                $response['msg'] = 'Error al obtener usuario: ' . $con->error;
            }
        } else {
            // Si el ID no es válido o no se proporcionó.
            $response['msg'] = 'ID de usuario no proporcionado o inválido.';
        }
        break; // Importante: salir del switch después de manejar la acción.
        
        default:
        // Si la acción no es reconocida o no se proporciona, devolvemos un mensaje de error.
        $response['msg'] = 'Acción no reconocida o no válida.';
        break;
}

// 8. Enviar la respuesta JSON final.
//    Convertimos nuestro array $response a una cadena JSON y la imprimimos.
echo json_encode($response);


?>