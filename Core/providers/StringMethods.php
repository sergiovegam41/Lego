<?php

namespace Core\providers;
use DateTime;
trait StringMethods {

    public function showString(string $srt) : string
    {
        $srt = str_replace('&amp;','&',htmlentities($srt));
        return $srt;
    }

    public function toCamelCase(string $string): string 
    {
        $result = '';
        $string = trim($string);
        $string = explode(' ', $string);
        foreach($string as $v)
        {
            $lowStr = strtolower($v);
            $capStr = ucfirst($lowStr);
            $result .= $capStr . ' ';
        }
        
        $result = rtrim($result);
        
        return $result;
    }

    public function dateToString($fecha)
    {
        $fecha_datetime = new DateTime($fecha);
        setlocale(LC_TIME, 'es_ES.UTF-8');
        $hora = $fecha_datetime->format('h:i');
        $ampm = $fecha_datetime->format('A') == 'AM' ? 'AM' : 'PM';
        $dia = $fecha_datetime->format('d');
        $anio = $fecha_datetime->format('Y');


        $mes = $fecha_datetime->format('F');

        return $this->toSpanis("$dia de $mes del $anio - $hora $ampm");
    }

    public function toSpanis($fecha)
    {
        $dias = array("lunes", "martes", "miercoles", "jueves", "viernes", "sabado", "domingo");
        $meses = array("enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre");

        $fecha_formateada = str_replace("Monday", $dias[0], $fecha);
        $fecha_formateada = str_replace("Tuesday", $dias[1], $fecha_formateada);
        $fecha_formateada = str_replace("Wednesday", $dias[2], $fecha_formateada);
        $fecha_formateada = str_replace("Thursday", $dias[3], $fecha_formateada);
        $fecha_formateada = str_replace("Friday", $dias[4], $fecha_formateada);
        $fecha_formateada = str_replace("Saturday", $dias[5], $fecha_formateada);
        $fecha_formateada = str_replace("Sunday", $dias[6], $fecha_formateada);

        $fecha_formateada = str_replace("January", $meses[0], $fecha_formateada);
        $fecha_formateada = str_replace("February", $meses[1], $fecha_formateada);
        $fecha_formateada = str_replace("March", $meses[2], $fecha_formateada);
        $fecha_formateada = str_replace("April", $meses[3], $fecha_formateada);
        $fecha_formateada = str_replace("May", $meses[4], $fecha_formateada);
        $fecha_formateada = str_replace("June", $meses[5], $fecha_formateada);
        $fecha_formateada = str_replace("July", $meses[6], $fecha_formateada);
        $fecha_formateada = str_replace("August", $meses[7], $fecha_formateada);
        $fecha_formateada = str_replace("September", $meses[8], $fecha_formateada);
        $fecha_formateada = str_replace("October", $meses[9], $fecha_formateada);
        $fecha_formateada = str_replace("November", $meses[10], $fecha_formateada);
        $fecha_formateada = str_replace("December", $meses[11], $fecha_formateada);

        return $fecha_formateada;
    }
}