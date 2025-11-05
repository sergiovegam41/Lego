<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Matches extends Model
{
    // Especificar la conexión de MongoDB
    protected $connection = 'mongodb';

    // Nombre de la colección en MongoDB
    protected $collection = 'Matches';

    // Deshabilitar timestamps si no existen en la colección
    public $timestamps = false;

    // Campos que se pueden asignar masivamente (opcional)
    protected $fillable = [];

    // Campos ocultos (opcional)
    protected $hidden = [];
}
