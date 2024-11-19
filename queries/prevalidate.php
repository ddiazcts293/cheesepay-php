<?php

require __DIR__ . '/query_response.php';
require __DIR__ . '/../functions/mysql_connection.php';
require __DIR__ . '/../functions/helpers.php';
require __DIR__ . '/../dtos/found_student.php';

class PrevalidationResponse extends QueryResponse {
    private $is_registered;

    public function is_registered() : bool {
        return $this->is_registered;
    }
    
    public function to_json_string() : string {
        $json = [];
        $json['status'] = $this->get_status();
        $json['is_registered'] = $this->is_registered();
        
        if ($this->get_data() instanceof FoundStudent) {
            $json['student'] = json_decode($this->get_data()->to_json_string());
        }
        
        return json_encode($json);
    }
    
    public function __construct(bool $is_registered, $data) {
        // llamar al constructor de la clase padre
        parent::__construct(QueryResponse::OK, $data, null);
        $this->is_registered = $is_registered;
    }
}

// establece el tipo de respuesta como archivo JSON
header('Content-Type: text/json');
// declara una variable para almacenar la respuesta
$response = null;

// verifica que el método de la petición sea GET
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['curp'])) {
    // limpia la cadena que contiene el CURP recibido
    $curp = satinize($_GET['curp']);
    // abre una nueva conexión con la base de datos
    $conn = MySqlConnection::open_connection();
    // prepara la sentencia a ejecutar
    $stmt = $conn->prepare('CALL sp_prevalidar_curp(?, @is_registered)');
    // enlaza los parámetros de entrada
    $stmt->bind_param('s', $curp);

    // ejecuta el procedimiento verificando que se lleve a cabo exitosamente
    if ($stmt->execute()) {
        // declara una variable para almacenar al alumno encontrado
        $found_student = null;
        // verifica si se obtuvo un conjunto de resultados
        if ($result = $stmt->get_result()) {
            // obtiene un arreglo con los campos de la fila
            $row = $result->fetch_assoc();
            // crea un objeto que repretesenta un alumno encontrado
            $found_student = new FoundStudent(
                $row['matricula'],
                $row['nombre_completo'],
                $row['curp'],
                $row['estado_inscripcion'],
                $row['nivel_educativo'],
                $row['grupo']
            );

            // avanza al siguiente resultado, lo cual no es necesario, pero
            // falla si no se pone
            $stmt->next_result();
            // libera los recursos asociados al resultado obtenido
            $result->free();
        }
    
        // consulta y obtiene el valor del parámetro de salida
        $result = $conn->query('SELECT @is_registered');
        $is_registered = $result->fetch_column();
        // crea un objeto de respuesta
        $response = new PrevalidationResponse($is_registered, $found_student);
    } else {
        // crea un objeto de respuesta en caso de producirse un error durante la
        // conslta
        $response = QueryResponse::create_error('Error during query');
    }

    // libera los recursos y cierra la conexión
    $stmt->close();
    $conn->close();
} else {
    // crea un objeto de respuesta en caso de recibirse una petición erronea
    $response = QueryResponse::create_error('Malformed request');
}

// despliega el objeto de respuesta en formato JSON
echo $response->to_json_string();
