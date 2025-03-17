<?php

namespace Core\Models;

class ResponseDTO
{
  public bool $success;
  public string $msj;
  public $data;
  public $status_code;
  
  public function __construct($success, $msj, $data, $status_code = null)
  {
    $this->success = $success;
    $this->msj = $msj;
    $this->data = $data;
    $this->status_code = $status_code;
    
  }

 
}
