<?php

/*
    Descripción: realiza la búsqueda de registros
    Devuelve: arreglo de registros encontrados

    Parámetros:
    - type: tipo de búsqueda (student, tutor)
    - q: consulta
*/

require __DIR__ . '/../functions/mysql_connection.php';
require __DIR__ . '/../functions/query_response.php';
require __DIR__ . '/../dtos/found_student.php';
require __DIR__ . '/../dtos/found_tutor.php';

// establece el tipo de respuesta en fomato JSON
header('Content-Type: text/json');
// declara una variable para almacenar la respuesta
$response = null;

// verifica que el método de la petición sea GET 
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // verifica si los parámetros de tipo y consulta están definidos
    if (isset($_GET['type'], $_GET['q'])) {
        // obtiene y limpia los parámetros recibidos
        $type = sanitize($_GET['type']);
        $term = sanitize($_GET['q']);

        // verifica si el tipo de búsqueda corresponde a alumno o tutor
        if ($type === 'student' || $type === 'tutor') {
            // declara una variable para almacenar la lista de elementos encontrados
            $results = null;
        
            // realiza la selección del tipo de búsqueda
            switch ($type) {
                case 'student':
                    $results = search_students($term);
                    break;
                case 'tutor':
                    $results = search_tutors($term);
                    break;
                default:
                    break;
            }
        
            // crea un objeto de respuesta según sea el caso
            $response = $results !== null ? 
                new QueryResponse(QueryResponse::OK, $results) :
                QueryResponse::error('Error during query');
        } else {
            // crea un objeto de respuesta en caso de especificar un tipo de 
            // búsqueda no reconocida
            $response = QueryResponse::error('Invalid specified type');
        }
    } else {
        // crea un objeto de respuesta en caso de recibirse una petición sin el
        // formato requerido
        $response = QueryResponse::malformed_request();
    }
} else {
    // crea un objeto de respuesta para solicitudes con otro método
    $response = QueryResponse::invalid_method();
}

// despliega el objeto de respuesta en formato JSON
echo $response->to_json_string();

/* Funciones */

/**
 * Realiza la búsqueda de alumnos
 * @param string $term Término de búsqueda
 * @return array
 */
function search_students(string $term): array {
    // crea un arreglo vacío
    $list = [];
    // abre una nueva conexión con la base de datos
    $conn = MySqlConnection::open_connection();
    // prepara la sentencia a ejecutar
    $stmt = $conn->prepare('CALL sp_buscar_alumnos(?)');
    // enlaza los parámetros de entrada
    $stmt->bind_param('s', $term);

    // ejecuta el procedimiento verificando que se lleve a cabo exitosamente
    if ($stmt->execute()) {
        // procesa múltiples conjuntos de resultados
        do {
            // verifica si se obtuvo un conjunto de resultados
            if ($result = $stmt->get_result()) {
                // recorre cada fila del conjunto mientras se pueda obtener un 
                // arreglo con sus campos
                while ($row = $result->fetch_assoc()) {
                    // agrega un nuevo objeto en el arreglo
                    array_push(
                        $list, 
                        new FoundStudent(
                            $row['matricula'],
                            $row['nombre_completo'],
                            $row['curp'],
                            $row['estado_inscripcion'],
                            $row['nivel_educativo'],
                            $row['grupo']
                        )
                    );
                }

                // libera los recursos asociados al resultado obtenido
                $result->free();
            }
            // avanza al siguiente conjunto (si es que hay)
        } while ($stmt->more_results() && $stmt->next_result());
    }

    // libera los recursos y cierra la conexión
    $stmt->close();
    $conn->close();

    return $list;
}

/**
 * Realiza la búsqueda de tutores.
 * @param string $term Término de búsqueda
 * @return array
 */
function search_tutors(string $term): array {
    // crea un arreglo vacío
    $list = [];
    // abre una nueva conexión con la base de datos
    $conn = MySqlConnection::open_connection();
    // prepara la sentencia a ejecutar
    $stmt = $conn->prepare('CALL sp_buscar_tutores(?)');
    // enlaza los parámetros de entrada
    $stmt->bind_param('s', $term);

    // ejecuta el procedimiento verificando que se lleve a cabo exitosamente
    if ($stmt->execute()) {
        // procesa múltiples conjuntos de resultados
        do {
            // verifica si se obtuvo un conjunto de resultados
            if ($result = $stmt->get_result()) {
                // recorre cada fila del conjunto mientras se pueda obtener un 
                // arreglo con sus campos
                while ($row = $result->fetch_assoc()) {
                    // agrega un nuevo objeto en el arreglo
                    array_push(
                        $list, 
                        new FoundTutor(
                            $row['numero'],
                            $row['nombre_completo'],
                            $row['rfc']
                        )
                    );
                }

                // libera los recursos asociados al resultado obtenido
                $result->free();
            }
            // avanza al siguiente conjunto (si es que hay)
        } while ($stmt->more_results() && $stmt->next_result());
    }

    // libera los recursos y cierra la conexión
    $stmt->close();
    $conn->close();

    return $list;
}
