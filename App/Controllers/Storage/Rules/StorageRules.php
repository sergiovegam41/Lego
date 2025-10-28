<?php
/**
 * StorageRules - Reglas de validación para operaciones de storage
 *
 * PROPÓSITO:
 * Define las reglas de validación para uploads y operaciones de archivos.
 * Usa Rakit Validator para validación consistente.
 */

namespace App\Controllers\Storage\Rules;

use Rakit\Validation\Validator;

class StorageRules
{
    private Validator $validator;

    public function __construct()
    {
        $this->validator = new Validator();
    }

    /**
     * Valida un upload de archivo
     */
    public function validateUpload(array $data): \Rakit\Validation\Validation
    {
        $rules = [
            'path' => 'required|max:500',
        ];

        $messages = [
            'path:required' => 'La ruta es requerida',
            'path:max' => 'La ruta no puede exceder 500 caracteres',
        ];

        $validation = $this->validator->make($data, $rules);
        $validation->setMessages($messages);
        $validation->validate();

        return $validation;
    }

    /**
     * Valida operación de eliminación
     */
    public function validateDelete(array $data): \Rakit\Validation\Validation
    {
        $rules = [
            'file' => 'required|max:500',
        ];

        $messages = [
            'file:required' => 'El archivo es requerido',
            'file:max' => 'La ruta del archivo no puede exceder 500 caracteres',
        ];

        $validation = $this->validator->make($data, $rules);
        $validation->setMessages($messages);
        $validation->validate();

        return $validation;
    }

    /**
     * Valida operación de listado
     */
    public function validateList(array $data): \Rakit\Validation\Validation
    {
        $rules = [
            'path' => 'max:500',
            'limit' => 'numeric|max:1000',
        ];

        $messages = [
            'path:max' => 'La ruta no puede exceder 500 caracteres',
            'limit:numeric' => 'El límite debe ser numérico',
            'limit:max' => 'El límite máximo es 1000',
        ];

        $validation = $this->validator->make($data, $rules);
        $validation->setMessages($messages);
        $validation->validate();

        return $validation;
    }

    /**
     * Valida operación de obtener archivo
     */
    public function validateGet(array $data): \Rakit\Validation\Validation
    {
        $rules = [
            'file' => 'required|max:500',
        ];

        $messages = [
            'file:required' => 'El archivo es requerido',
            'file:max' => 'La ruta del archivo no puede exceder 500 caracteres',
        ];

        $validation = $this->validator->make($data, $rules);
        $validation->setMessages($messages);
        $validation->validate();

        return $validation;
    }
}
