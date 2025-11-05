<?php
// Configure error reporting - Hide deprecation warnings in production
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

$capsule = new Capsule;

use Dotenv\Dotenv;
// Cargar las variables de entorno desde .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Iniciar sesión después de cargar el entorno
if (!headers_sent()) {
    session_start();
}

// Configurar la conexión a PostgreSQL usando variables del .env
$capsule->addConnection([
    'driver'    => 'pgsql',
    'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port'      => $_ENV['DB_PORT'] ?? 5432,
    'database'  => $_ENV['DB_DATABASE'] ?? 'test',
    'username'  => $_ENV['DB_USERNAME'] ?? 'postgres',
    'password'  => $_ENV['DB_PASSWORD'] ?? '',
    'charset'   => 'utf8',
    'prefix'    => '',
    'schema'    => 'public',
]);

// Hace que el ORM esté disponible globalmente
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Registrar el driver de MongoDB para Laravel MongoDB
$capsule->getDatabaseManager()->extend('mongodb', function($config, $name) {
    $config['name'] = $name;
    return new \MongoDB\Laravel\Connection($config);
});

// Configurar la conexión a MongoDB Cloud (Atlas)
$capsule->addConnection([
    'driver'   => 'mongodb',
    'dsn'      => $_ENV['MONGO_CLOUD_DSN'] ?? '',
    'database' => $_ENV['MONGO_CLOUD_DATABASE'] ?? 'Analysis',
], 'mongodb');

// Configurar el Paginator para paginación de Eloquent
Paginator::currentPathResolver(function () {
    return strtok($_SERVER['REQUEST_URI'], '?');
});

Paginator::currentPageResolver(function ($pageName = 'page') {
    return $_GET[$pageName] ?? 1;
});

// Registrar componentes dinámicos
\Core\Bootstrap\RegisterDynamicComponents::register();

// Definir variables globales para compatibilidad
$DB_USERNAME = $_ENV['DB_USERNAME'];
$DB_DATABASE = $_ENV['DB_DATABASE'];
$DB_PASSWORD = $_ENV['DB_PASSWORD'];
$DB_PORT = $_ENV['DB_PORT'];
$DB_HOST = $_ENV['DB_HOST'];
$APP_URL = $_ENV['HOST_NAME'];

$url_servidor = $APP_URL;



function isCommandLineInterface() {
    return (php_sapi_name() === 'cli');
}

function f($array):array|false
{

  if(is_array($array)){
    
    if( count($array) > 0 ){
          return $array[0];
      }

  }

  return false;
  
}
function has($array):bool
{

  if(is_array($array)){
    
    if( count($array) > 0 ){
        return true;
    }

  }

  return false;
}

    
function p() {
    // Limpiar cualquier salida previa
    while (ob_get_level()) {
        ob_end_clean();
    }

    $args = func_get_args();
    $i = 1;
    echo '<code>';
    foreach ($args as $arg) {
        // Mostrar el argumento, como texto o JSON
        echo is_string($arg) ? $arg : json_encode($arg);
        $i++;

        if (isCommandLineInterface()) {
            // Espaciado en CLI
            if ($i <= count($args)) {
                echo "\n\n\n\n";
            }
        } else {
            // Espaciado en navegador
            if ($i <= count($args)) {
                echo "<br><br><br><br>";
            }
        }
    }
    echo '</code>';
    // Detener el script después de mostrar el mensaje
    die;
}



    function plog($data, $file_path = null) {
    
        if ($file_path == null) {
            $file_path = __DIR__."/../../logs/general_log.txt";
        }else{
            
            $file_path = __DIR__."/../../logs/".$file_path;
        }

        if (!is_dir(dirname($file_path))) {
            mkdir(dirname($file_path), 0777, true);
        }
    
        $json = json_encode($data);
        $file = fopen($file_path, "a+");
        fwrite($file, PHP_EOL . PHP_EOL . date('Y-m-d H:i:s') . PHP_EOL);
        fwrite($file, "DATA:  $json" . PHP_EOL);
        fwrite($file, PHP_EOL);
        fwrite($file, "--------------------------------" . PHP_EOL);
        fclose($file);

    }


    function dd(){
        $args = func_get_args();
        $i = 1;
        foreach ($args as $arg) {

            var_dump($arg);
            $i++;

            if (isCommandLineInterface()) {
                
                if( $i <= count($args) ){
                    echo "\n\n\n\n";
                }

            } else {

                if( $i <= count($args)  ){
                    echo "<br<br><br><br>";
                }        
            
            }
        
        }
        die;
    }

function consultar($transaccion)
{
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $dbname = $_ENV['DB_DATABASE'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $username, $password, $opciones);
    } catch (PDOException $e) {
        echo "No se pudo conectar a la BD: " . $e->getMessage();
        return false;
    }

    try {
        $resultado = $conexion->prepare($transaccion);
        $resultado->execute();
        if (!$resultado) {
            return false;
        }

        return $resultado->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        return false;
    }
}

function consultarSinError($transaccion)
{
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $dbname = $_ENV['DB_DATABASE'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $username, $password, $opciones);
    } catch (PDOException $e) {
        return false;
    }

    try {
        $resultado = $conexion->prepare($transaccion);
        $resultado->execute();
        if (!$resultado) {
            return false;
        }

        return $resultado->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return false;
    }
}

function consultaSimple($campos,$nameTable,$short = '')
{
    if($short != ''){
        $pseudoCodigo = explode('|',$short);
        $orden = $pseudoCodigo[0];
        $campo = $pseudoCodigo[1];
        
        $short = " ORDER BY $campo $orden";
    }
    
    $sql=<<<SQL
    SELECT $campos FROM $nameTable $short
SQL;
    
    $result = consultar($sql);

    return $result;
}

 function consultaConParametros($campos, $nameTable, $nameColumn, $value )
{

    if($value == '' || !isset($value))
    {
        return [];
    }

    $sql=<<<SQL
    SELECT $campos FROM $nameTable WHERE $nameColumn = '$value';
SQL;
    $result = consultar( $sql );

    return $result;

}

function consultarSinErrorRetornaEstado($transaccion)
{
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $dbname = $_ENV['DB_DATABASE'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    try {
        $conexion = new PDO($dsn, $username, $password, $opciones);
    } catch (PDOException $e) {
        return [
            "success" => false,
            "message" => "No se pudo conectar a la BD: " . $e->getMessage(),
            "date" => date("d-m-Y H:i:s"),
            "comando" => $transaccion,
            "output" => null
        ];
    }

    try {
        $resultado = $conexion->prepare($transaccion);
        $resultado->execute();
        
        if (!$resultado) {
            return false;
        }

        $vec_resul = $resultado->fetchAll(PDO::FETCH_ASSOC);
        return [
            "success" => true,
            "message" => "Ok",
            "comando" => $transaccion,
            "date" => date("d-m-Y H:i:s"),
            "output" => $vec_resul
        ];
    } catch (PDOException $e) {
        return [
            "success" => false,
            "message" => "Error en la consulta: " . $e->getMessage(),
            "comando" => $transaccion,
            "date" => date("d-m-Y H:i:s"),
            "output" => null
        ];
    }
}

function consultarError($transaccion)
{
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $dbname = $_ENV['DB_DATABASE'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $username, $password, $opciones);
    } catch (PDOException $e) {
        echo "No se pudo conectar a la BD: " . $e->getMessage();
        return false;
    }

    try {
        $resultado = $conexion->query($transaccion);

        if (!$resultado) {
            return $conexion->errorInfo()[2];
        }

        return false;
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        return false;
    }
}



function insertar($transaccion)
{
    global $DB_USERNAME, $DB_DATABASE, $DB_PASSWORD, $DB_PORT, $DB_HOST;

       $dsn = "pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE";

    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $DB_USERNAME, $DB_PASSWORD, $opciones);
    } catch (PDOException $e) {
        echo "No se pudo conectar a la BD: " . $e->getMessage();
        return false;
    }

    try {
        $resultado = $conexion->prepare($transaccion);
        
        $resultado->execute();

        if (!$resultado) {
            return false;
        }
        $id = $conexion->lastInsertId();
        return $id;
    } catch (PDOException $e) {
        echo "Error en la consulta:" . $e->getMessage();
        return false;
    }
}


function insertarSinError($transaccion)
{
    global $DB_USERNAME, $DB_DATABASE, $DB_PASSWORD, $DB_PORT, $DB_HOST;

       $dsn = "pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE";

    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $DB_USERNAME, $DB_PASSWORD, $opciones);
    } catch (PDOException $e) {
        echo "No se pudo conectar a la BD: " . $e->getMessage();
        return false;
    }

    try {
        $resultado = $conexion->prepare($transaccion);
        
        $resultado->execute();

        if (!$resultado) {
            return false;
        }
        $id = $conexion->lastInsertId();
        return $id;
    } catch (PDOException $e) {
        return false;
    }
}



function insertar_traer_id($transaccion, $secuencia, $registrar = true)
{
    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $dbname = $_ENV['DB_DATABASE'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $username, $password, $opciones);
    } catch (PDOException $e) {
        echo "No se pudo conectar a la BD: " . $e->getMessage();
        return false;
    }

    try {
        $resultado = $conexion->query($transaccion);

        if (!$resultado) {
            return false;
        }

        $id = $conexion->lastInsertId($secuencia);
        return $id;
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        return false;
    }
}
