<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

use Dotenv\Dotenv;
 // Cargar las variables de entorno desde .env
 $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
 $dotenv->load();
 

// Configurar la conexión a PostgreSQL usando variables del .env
$capsule->addConnection([
    'driver'    => 'pgsql', // Cambiado a PostgreSQL
    'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port'      => $_ENV['DB_PORT'] ?? 5432,
    'database'  => $_ENV['DB_DATABASE'] ?? 'test',
    'username'  => $_ENV['DB_USERNAME'] ?? 'postgres',
    'password'  => $_ENV['DB_PASSWORD'] ?? '',
    'charset'   => 'utf8',
    'prefix'    => '',
    'schema'    => 'public', // Puedes cambiarlo si usas un esquema diferente
]);

// Hace que el ORM esté disponible globalmente
$capsule->setAsGlobal();
$capsule->bootEloquent();


$usuario_db_web_PDO = env('DB_USERNAME');
$bd_proyecto = env('DB_DATABASE');
$clave_usuario_PDO = env('DB_PASSWORD');
$puerto_db = env('DB_PORT');
$host_db = env('DB_HOST');
$url_servidor = env('APP_URL');

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
    // echo $transaccion.'<br/>'.'<br/>';
    global $usuario_db_web_PDO, $bd_proyecto, $clave_usuario_PDO, $puerto_db, $host_db;

    $dsn = "pgsql:host=$host_db;port=$puerto_db;dbname=$bd_proyecto";
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
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

        $vec_resul = $resultado->fetchAll(PDO::FETCH_ASSOC);
        return $vec_resul;
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        return false;
    }
}

function consultarSinError($transaccion)
{

    // echo $transaccion.'<br/>'.'<br/>';

    global $usuario_db_web_PDO, $bd_proyecto, $clave_usuario_PDO, $puerto_db, $host_db;

    $dsn = "pgsql:host=$host_db;port=$puerto_db;dbname=$bd_proyecto";
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
    } catch (PDOException $e) {
        //echo "No se pudo conectar a la BD: " . $e->getMessage();
        return false;
    }

    try {
        $resultado = $conexion->prepare($transaccion);
        
        $resultado->execute();
        if (!$resultado) {
            return false;
        }

        $vec_resul = $resultado->fetchAll(PDO::FETCH_ASSOC);

        return $vec_resul;
    } catch (PDOException $e) {
        // echo "Error en la consulta: " . $e->getMessage();
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
    // echo $transaccion.'<br/>'.'<br/>';
    global $usuario_db_web_PDO, $bd_proyecto, $clave_usuario_PDO, $puerto_db, $host_db;

       $dsn = "pgsql:host=$host_db;port=$puerto_db;dbname=$bd_proyecto";

    $opciones = [
     
        PDO::ATTR_ERRMODE, 
        PDO::ERRMODE_EXCEPTION
    ];

    try {
        $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
    } catch (PDOException $e) {
        return [
            "success"=>false,
            "message"=>"No se pudo conectar a la BD: " . $e->getMessage(),
            "date"=>date("d-m-Y H:i:s"),
            "comando"=> $transaccion,
            "output"=>null
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
            "success"=>true,
            "message"=>"Ok",
            "comando"=> $transaccion,
            "date"=>date("d-m-Y H:i:s"),
            "output"=>$vec_resul
        ];

    } catch (PDOException $e) {

        
        return [
            "success"=>false,
            "message"=>"Error en la consulta: " . $e->getMessage(),
            "comando"=> $transaccion,
            "date"=>date("d-m-Y H:i:s"),
            "output"=>null,
            
        ];
    }
}

function consultarError($transaccion)
{
    global $usuario_db_web_PDO, $bd_proyecto, $clave_usuario_PDO, $puerto_db, $host_db;

       $dsn = "pgsql:host=$host_db;port=$puerto_db;dbname=$bd_proyecto";

    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
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

function consultar_geolocation($transaccion)
{
    global $usuario_db_web_PDO, $bd_geolocation, $clave_usuario_PDO, $puerto_db, $host_db;

    $dsn = "pgsql:$host_db;$puerto_db;$bd_geolocation";
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
    } catch (PDOException $e) {
        echo "No se pudo conectar a la BD: " . $e->getMessage();
        return false;
    }

    try {
        $resultado = $conexion->query($transaccion);

        if (!$resultado) {
            return false;
        }

        $vec_resul = $resultado->fetchAll(PDO::FETCH_ASSOC);
        return $vec_resul;
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        return false;
    }
}

////////////////////////////////////////////////////////////////////////////////
// Funcion: insertar($transaccion)
// Objetivo: Establece una conexion a la base de datos e inserta datos
// Desarrollo: SmartInfo Ltda. (www.smartinfobusiness.com)
// Autor: AAM
// Fecha: 2006/01/23
// Modificacion: 2006/01/23
// Retorna: Arreglo si la expresion es ejecutada con exito o FALSE
////////////////////////////////////////////////////////////////////////////////
function insertar($transaccion)
{
    global $usuario_db_web_PDO, $bd_proyecto, $clave_usuario_PDO, $puerto_db, $host_db;

       $dsn = "pgsql:host=$host_db;port=$puerto_db;dbname=$bd_proyecto";

    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
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
    global $usuario_db_web_PDO, $bd_proyecto, $clave_usuario_PDO, $puerto_db, $host_db;

       $dsn = "pgsql:host=$host_db;port=$puerto_db;dbname=$bd_proyecto";

    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
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




////////////////////////////////////////////////////////////////////////////////
// Funcion: insertar_traer_id($transaccion, $secuencia)
// Objetivo: Establece una conexion a la base de datos e inserta datos
// Desarrollo: SmartInfo Ltda. (www.smartinfobusiness.com)
// Autor: AAM
// Fecha: 2006/01/23
// Modificacion: 2006/01/23
// Retorna: Arreglo si la expresion es ejecutada con exito o FALSE
////////////////////////////////////////////////////////////////////////////////
function insertar_traer_id($transaccion, $secuencia, $registrar = true)
{
    global $usuario_db_web_PDO, $bd_proyecto, $clave_usuario_PDO, $puerto_db, $host_db;

       $dsn = "pgsql:host=$host_db;port=$puerto_db;dbname=$bd_proyecto";

    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
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
        if ($registrar) {
            // actualizar_visita($transaccion, "Inserccion");
        }
        return $id;
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        return false;
    }
}

/**
 * Función: actualizar($transaccion)
 * Objetivo: Establece una conexión a la base de datos y actualiza datos
 * Desarrollo: SmartInfo Ltda. (www.smartinfobusiness.com)
 * Autor: AAM
 * Fecha: 2006/01/23
 * Modificación: 2006/01/23
 * Retorna: Arreglo si la expresión es ejecutada con éxito o FALSE
 */
function actualizar($transaccion, $registrar = true)
{
    global $dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones, $host_db, $puerto_db, $bd_proyecto;
       $dsn = "pgsql:host=$host_db;port=$puerto_db;dbname=$bd_proyecto";


    try {
        $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
    } catch (PDOException $e) {
        echo "No se pudo conectar a la BD: " . $e->getMessage();
        return false;
    }

    try {
        $resultado = $conexion->query($transaccion);
        $resError = $conexion->errorInfo();
        $conexion = null;

        if ($registrar) {
            // actualizar_visita($transaccion, "Actualizacion");
        }

        return $resultado;
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        return false;
    }
}

/**
 * Función: coneccion($transaccion)
 * Objetivo: Establece una conexión a la base de datos y actualiza datos
 * Desarrollo: SmartInfo Ltda. (www.smartinfobusiness.com)
 * Autor: AAM
 * Fecha: 2006/01/23
 * Modificación: 2006/01/23
 */
function coneccion()
{
    global $dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones, $host_db, $puerto_db, $bd_proyecto;
       $dsn = "pgsql:host=$host_db;port=$puerto_db;dbname=$bd_proyecto";


    try {
        $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
    } catch (PDOException $e) {
        echo "No se pudo conectar a la BD: " . $e->getMessage();
        return false;
    }

    $conexion = null;
    return true;
}

/**
 * Función: consultar_tabla($transaccion)
 * Objetivo: Establece una conexión a la base de datos y retorna resultados en arreglo
 * Desarrollo: SmartInfo Ltda. (www.smartinfobusiness.com)
 * Autor: AAM
 * Fecha: 2004/08/18
 * Modificación: 2004/08/18
 * Retorna: Arreglo si la expresión es ejecutada con éxito o FALSE
 */
function consultar_tabla($transaccion)
{
    global $dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones, $host_db, $puerto_db, $bd_proyecto;
       $dsn = "pgsql:host=$host_db;port=$puerto_db;dbname=$bd_proyecto";


    try {
        $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
    } catch (PDOException $e) {
        echo "No se pudo conectar a la BD: " . $e->getMessage();
        return false;
    }

    try {
        $resultado = $conexion->query($transaccion);
        if ($resultado === false) {
            $conexion = null;
            return false;
        } else {
            $vec_resul = array();
            $registros = $resultado->columnCount();
            for ($reg = 0; $reg < $registros; $reg++) {
                $fieldInfo = $resultado->getColumnMeta($reg);
                $vec_resul[] = array("nombre" => $fieldInfo['name'], "tipo" => $fieldInfo['native_type']);
            }
            $conexion = null;
            return $vec_resul;
        }
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        return false;
    }
}

/**
 * Función: consultar_tabla_tipo($transaccion)
 * Objetivo: Establece una conexión a la base de datos y retorna resultados en arreglo
 * Desarrollo: SmartInfo Ltda. (www.smartinfobusiness.com)
 * Autor: AAM
 * Fecha: 2004/08/18
 * Modificación: 2004/08/18
 * Retorna: Arreglo si la expresión es ejecutada con éxito o FALSE
 */
function consultar_tabla_tipo($transaccion)
{
    global $dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones, $host_db, $puerto_db, $bd_proyecto;
       $dsn = "pgsql:host=$host_db;port=$puerto_db;dbname=$bd_proyecto";


    try {
        $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
    } catch (PDOException $e) {
        echo "No se pudo conectar a la BD: " . $e->getMessage();
        return false;
    }

    try {
        $resultado = $conexion->query($transaccion);
        if ($resultado === false) {
            $conexion = null;
            return false;
        } else {
            $vec_resul = array();
            $registros = $resultado->columnCount();
            for ($reg = 0; $reg < $registros; $reg++) {
                $fieldInfo = $resultado->getColumnMeta($reg);
                if ($fieldInfo['native_type'] === 'date' || $fieldInfo['native_type'] === 'timestamp') {
                    $vec_resul[] = $fieldInfo['native_type'];
                }
            }
            $conexion = null;
            return $vec_resul;
        }
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        return false;
    }
}

/**
 * Función: traer_oid($archivo_tmp_name, $archivo_size, $band)
 * Objetivo: Establece una conexión a la base de datos y actualiza datos
 * Desarrollo: SmartInfo Ltda. (www.smartinfobusiness.com)
 * Autor: AAM
 * Fecha: 2006/01/23
 * Modificación: 2006/01/23
 * Retorna: Arreglo si la expresión es ejecutada con éxito o FALSE
 */
function traer_oid($archivo_tmp_name, $archivo_size, $band)
{
    global $dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones, $host_db, $puerto_db, $bd_proyecto;
       $dsn = "pgsql:host=$host_db;port=$puerto_db;dbname=$bd_proyecto";


    if (file_exists($archivo_tmp_name) && ($archivo_size > 0) && $band == true) {
        try {
            $conexion = new PDO($dsn, $usuario_db_web_PDO, $clave_usuario_PDO, $opciones);
        } catch (PDOException $e) {
            echo "No se pudo conectar a la BD: " . $e->getMessage();
            return 1;
        }

      try {
            $conexion->beginTransaction();
        try {
            $oid = $conexion->pgsqlLOBCreate();
            $lob = $conexion->pgsqlLOBOpen($oid, 'w');

            $file_contents = file_get_contents($archivo_tmp_name);

            fwrite($lob, $file_contents);
            fclose($lob);

            $conexion->commit();
            return $oid;
        } catch (PDOException $e) {
            $conexion->rollBack();
            echo "Error al importar el archivo: " . $e->getMessage();
            exit;
        }

           /* $stmt = @$conexion->prepare('SELECT lo_import(:archivo_tmp_name) AS imagen');
            $stmt->bindParam(':archivo_tmp_name', $archivo_tmp_name);
            @$stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $imagen = $resultado['imagen'];
            $conexion->commit();
            $conexion = null;
            //unlink($archivo_tmp_name);
            return $imagen;*/
        } catch (PDOException $e) {
            $conexion->rollBack();
            // echo "Error en la transacción: " . $e->getMessage();
            return 0;
        }
    } else {
        return 2;
    }
}

/**
 * Archivo: getThumbnail($size, $nombre_archivo, $tmp_name, $type, $anchura, $hmax)
 * Fecha: 2007/07/16
 * Autor: Yeison Pomares (yeison@smartinfo.com.co)
 * Descripción: Retorna una miniatura de un archivo de imagen
 */
function getThumbnail($size, $nombre_archivo, $tmp_name, $type, $anchura, $hmax)
{
    switch ($type) {
        case "image/jpeg":
        case "image/pjpeg":
        case "image/jpeg":
            $img = imagecreatefromjpeg($tmp_name);
            break;
        case "image/gif":
            $img = imagecreatefromgif($tmp_name);
            break;
        case "image/png":
            $img = imagecreatefrompng($tmp_name);
            break;
    }
    $datos = getimagesize($tmp_name);

    if ($datos[0] > $anchura || $datos[1] > $hmax) {
        $ratio = ($datos[0] / $anchura);
        $altura = ($datos[1] / $ratio);
        if ($altura > $hmax) {
            $anchura2 = $hmax * $anchura / $altura;
            $altura = $hmax;
            $anchura = $anchura2;
        }
    } else {
        $anchura = $datos[0];
        $altura = $datos[1];
    }
    $thumb = imagecreatetruecolor($anchura, $altura);
    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $anchura, $altura, $datos[0], $datos[1]);

    switch ($type) {
        case "image/jpeg":
        case "image/pjpeg":
        case "image/jpeg":
            imagejpeg($thumb, $nombre_archivo, 100);
            break;
        case "image/gif":
            imagegif($thumb, $nombre_archivo);
            break;
        case "image/png":
            imagepng($thumb, $nombre_archivo, 9);
            break;
    }

    $tthumb = file_get_contents($nombre_archivo);
    $tthumb = addslashes($tthumb);

    //@unlink($nombre_archivo);
    //return $tthumb;
    return $nombre_archivo;
}
