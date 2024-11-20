<?php

namespace Core\Dtos;

class ScriptCoreDTO
{
    public $path;

    /**  @var array */

    public $arg;

    public function __construct($path, $arg)
    {
        $this->path = $path;
        $this->arg = $arg;
    }
}
