<?php

namespace Core\providers;

use Rakit\Validation\Validator;

use Core\Response;


class Request
{
    public $request;
    private $rules = [];
    function __construct()
    {
        $phpInput = file_get_contents('php://input');
        $this->request = array_merge($_REQUEST, is_array(json_decode($phpInput, true)) ? json_decode($phpInput, true) : []);
        $this->validateMake();
    }
    public static function all()
    {
        $phpInput = file_get_contents('php://input');
        $request = array_merge($_REQUEST, is_array(json_decode($phpInput, true)) ? json_decode($phpInput, true) : []);
        return $request;
    }

    public function rules()
    {
        return $this->rules;
    }

    public function setRules($rules)
    {
        $this->rules = $rules;
        return $this;
    }

    function validateMake()
    {
        $validator = new Validator();

        $validation = $validator->make($this->request, $this->rules());

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            $result = array();
            $result['status'] = false;
            $result['errors'] = $errors->firstOfAll();
            Response::json(400,$result);
            die;
        } else {
            return true;
        }
    }
}
