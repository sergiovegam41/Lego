<?php 
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../Core/bootstrap.php';

return Migrations::execute();
 
class Migrations {

static function check(){



    $need = self::verifyBaseMigrations(true);    

   
    if($need){
        return true;
    }

    $migrations = self::getMigrations();
    $LocalMigrations = self::getLocalMigrations();
    $diferencias = array_diff(array_column($LocalMigrations, 'sql'), array_column($migrations, 'migration'));


    if(count($diferencias) > 0){
       return true;
    }

    return false;

}

    static function execute(){
        $executedMmigrationsBase = Migrations::verifyBaseMigrations();
     
        $executedMmigrationsJson = Migrations::getMigrationsUnSend();
    
        
        return json_encode([
            "base_execute_migrations" => $executedMmigrationsBase,
            "execute_migrations" => $executedMmigrationsJson    
        ]);
    }
static $jsonFile = __DIR__.'/migrations.json'; 
static $SQLPathMigrations = 'database/sql/';

static $BaseSQLs = [
    'migrations'=> 'database/sql/base/migrations.sql',
    'auth_users'=> 'database/sql/base/inital_structure.sql'
];

static function verifyBaseMigrations( $check = false ){


    $executedMmigrationsBase = [];
    $table = self::getTablesAllTables();

    foreach( self::$BaseSQLs as $key => $value ) {
    
        
        if(!in_array($key,  $table)) {
            if( true == $check) {
                	
                return true;
            }

            try { 

                $SQLData = file_get_contents($value);
           


                $cleanedStatements = removeCommentsFromSQL( $SQLData );

                $respuestas = [];
                foreach ($cleanedStatements as $key => $statement) {
                    
                    
                    if($statement != "\r\n\r\n\r\n\r\n"){
                        $result = consultarSinErrorRetornaEstado( str_replace("\ufeff", "", $statement ) );

                        
                        array_push($respuestas, $result);
                    }

                }


                array_push($executedMmigrationsBase, [
                    "archivo"=>$value,
                    "comandos" => $respuestas
                ] );
            } catch (\Throwable $th) {

                array_push($executedMmigrationsBase, [
                    "archivo"=>$value,
                    "comandos" => $respuestas
                ] );
                //throw $th;
            }

        } 

        

    }

    return $executedMmigrationsBase;
   
}

static function getTablesAllTables(){
    $query=<<<QUERY
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public';
    QUERY;
        
    $result=consultar($query);

    $nombreColecciones = array();
    foreach ( $result as $coleccion) {
        $nombreColecciones[] = $coleccion["table_name"];
    }
    return $nombreColecciones;
}
static function getMigrationsUnSend(){
    $executedMmigrations = [];

    $migrations = self::getMigrations();
    $LocalMigrations = self::getLocalMigrations();

    // p($LocalMigrations);

    $diferencias = array_diff(array_column($LocalMigrations, 'sql'), array_column($migrations, 'migration'));


    foreach ($diferencias as $value) {



        $SQLData = file_get_contents(Migrations::$SQLPathMigrations.$value);
        
        if( $SQLData ){
            
            
            $cleanedStatements = removeCommentsFromSQL( $SQLData );
            
            $respuestas = [];
            
            
            foreach ($cleanedStatements as $key => $statement) {
                
                if($statement != ""){
                  
                    $result = consultarSinErrorRetornaEstado( str_replace("\ufeff", "", $statement ) );
                    array_push($respuestas, $result);
                }
         
            }
          
            array_push($executedMmigrations, [
                "archivo"=>$value,
                "comandos" => $respuestas
            ] );

            self::saveMigrationByFileSQLName( $value );


        }
    }

    return $executedMmigrations;
    
}

static function saveMigrationByFileSQLName($fileName){

    $migration = self::getOneMigrationsByName($fileName);

    if(!$migration){

        $query=<<<QUERY
            INSERT INTO migrations (migration) 
            VALUES ('$fileName');
        QUERY;
        
        $result=consultar($query);

        return  $result;
        
    }
    
  
    
    return  $migration;

}

static function getLocalMigrations(){


    $jsonData = file_get_contents(Migrations::$jsonFile);
    if ( $jsonData ) {
        $jsonData = json_decode($jsonData, true);   
       return $jsonData;
    }

    return false;

}
static function getOneMigrationsByName($name){
    $query=<<<QUERY
    SELECT * 
    FROM migrations
    WHERE migration = '$name';
    QUERY;
    
    $result=consultar($query);

    if(count($result) > 0){
        return  $result[0];
    }
    
    return  false;


}
static function getMigrations(){
    $query=<<<QUERY
      SELECT x.* FROM public.migrations x
    QUERY;
    
    $result=consultar($query);

    return  $result;
}


}

function ver($data, $die = true){
    echo  json_encode($data);
    if($die){
        die;
    }
}

function saveLog($m){
    $texto = json_encode($m);
    $file = fopen("Logs/data.txt", "a+");
    fwrite($file, PHP_EOL . PHP_EOL . date('[Y-m-d H:i:s]') . PHP_EOL);
    fwrite($file, "DATA: $texto" . PHP_EOL);
    fwrite($file, PHP_EOL);
    fwrite($file, "----------------------------------------" . PHP_EOL);
    fclose($file);
}


function removeCommentsFromSQL($sql) {
    // Elimina los comentarios de la cadena SQL
    $pattern = '/--.*?(\n|$)|\/\*.*?\*\//s';
    $sql = preg_replace($pattern, '', $sql);

    // Divide el script en instrucciones individuales
    $statements = preg_split('/;\s*(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $sql);
    // $statements[0] = preg_replace('/\ufeff/u', '', $statements[0], 1);

    // echo json_encode($sql);
    $statements[0] = json_decode(str_replace("\ufeff", '', json_encode($statements[0]) ));

    // Retorna las instrucciones sin comentarios como un array
    return $statements;
}
