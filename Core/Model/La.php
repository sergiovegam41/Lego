<?php
namespace Core\Model;

class La
{
  // [Informational 1xx]
  const ES = "ES";
  const EN = "EN";

  static function eval(string $la ):string|false
  {

    if($la != self::EN && $la != self::ES ){
      return false;
    }

    return $la;
  }

}
