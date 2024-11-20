<?php

namespace Core;

class Response
{
    static function json($status,array $json)
    {
        header("Content-type: application/json; charset=utf-8"); 
        http_response_code($status);
        echo json_encode($json);
        die;
    }


    static function uri( string $html, string $title = "" )
    {
        retornar_json_servicios(null, "", $html, null, '', null, null,  $title); 
        die;
    }


}
