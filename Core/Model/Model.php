<?php

namespace Core\Model;

abstract class Model
{
    public $table;
    public $filables;
    protected $arrayMethods = ['create', 'read', 'update', 'delete'];
    public $sql;
    private $ultimoMetodoLlamado;
    private $request;
    private $select = "";
    private $join  = "";
    private $where = "";
    private $limit = "";
    private $ofset = "";
    private $group = "";
    private $frist = true;
    

    public function getMethod($request, $accion)
    {
        return $this->validateMethod($accion) ? $this->validateMethod($accion) : $this->$accion($request);
    }

    private function validateMethod($accion)
    {

        return !in_array($accion, $this->arrayMethods) ? (['status' => 'error', 'msg' => 'accion no permitida']) : false;
    }

    public function create($request)
    {
        $this->request = $request;
        $campos = $request;
        $keys = '';
        $values = '';
        $coma = ',';

        foreach ($campos as $key => $val) {
            $coma = array_key_last($campos) == $key ? '' : $coma;
            $keys .= $this->filables[$key][0] . $coma;
            $values .= "'$val' $coma";
        }

        $sql = <<<SQL
        INSERT INTO {$this->table} ({$keys}) VALUES ({$values})
SQL;

        $this->setUltimoMetodoLlamado(__METHOD__);

        $this->sql = $sql;

        return $this;
    }

    public function createGet()
    {
        $result = array();
        $id = insertarSinError($this->sql);
        if ($id) {
            $result['status'] = 'ok';
            $result['id'] = $id;
        } else {
            $result['status'] = 'error';
        }
        return $result;
    }

    public function read($request)
    {
        $this->request = $request;
        $campos = $this->filables;
        $keys = '';
        $coma = ',';
        $where = '';
        foreach ($campos as $key => $val) {
            $coma = end($campos) == $campos[$key] ? '' : $coma;
            $keys .= $this->filables[$key][0] . " as " . $key . $coma;
        }

        foreach ($request as $campo => $valor) {
            $where .= " AND {$this->filables[$campo][0]} = '$valor'";
        }

        $sql = <<<SQL
        SELECT {$keys} FROM {$this->table} WHERE 1 = 1 {$where} 
SQL;
        $this->setUltimoMetodoLlamado(__METHOD__);

        $this->sql = $sql;

        return $this;
    }

    public function readGet()
    {
        return consultarSinError($this->sql);
    }

    public function update($request)
    {
        $campos = $request;
        $keys = '';
        $coma = ',';
        $id_valor = '';
        $id_campo = '';

        foreach ($campos as $key => $val) {

            $coma = array_key_last($campos) == $key ? '' : $coma;

            if ($key == 'id') {
                $id_valor = $val;
                $id_campo = $this->filables[$key][0];
                continue;
            }


            $keys .=  $this->filables[$key][0] . " = '$val'" . $coma;
        }

        $keys = $this->removeTrailingComma($keys);

        $sql = <<<SQL
        UPDATE {$this->table} SET {$keys} WHERE {$id_campo} = '{$id_valor}'
SQL;

        $this->setUltimoMetodoLlamado(__METHOD__);

        $this->sql = $sql;

        return $this;
    }

    function removeTrailingComma($string) {
        if (substr($string, -1) === ',') {
            $string = substr($string, 0, -1);
        }
        return $string;
    }

    public function updateGet()
    {
        $result = array();
        if (consultarSinError($this->sql)) {
            $result['status'] = 'ok';
        } else {
            $result['status'] = 'error';
        }
        return $result;
    }

    public function delete($request)
    {
        $campos = $request;
        $id_valor = $campos['id'];
        $id_campo = $this->filables['id'][0];
        $sql = <<<SQL
        DELETE FROM {$this->table} WHERE {$id_campo} = '{$id_valor}'
SQL;

        $this->setUltimoMetodoLlamado(__METHOD__);

        $this->sql = $sql;

        return $this;
    }

    public function deleteGet()
    {
        return consultarSinError($this->sql);
    }

    private function setUltimoMetodoLlamado($metodo)
    {
        $this->ultimoMetodoLlamado = $metodo;
    }

    private function getUltimoMetodoLlamado()
    {
        return $this->ultimoMetodoLlamado . 'Get';
    }

    public function get()
    {

        $metodoGet = $this->getUltimoMetodoLlamado();
        $metodoGet = explode('::', $metodoGet);
        $metodoGet = $metodoGet[1];
    
        if (method_exists($this, $metodoGet)) {
            $resultado = $this->$metodoGet();
    
            if (is_array($resultado)) {
                return $resultado;
            } else {
                
                return consultarSinError($this->sql);
            }
        } else {
            return consultarSinError($this->sql);
        }
    }

    public function joinGet()
    {
        return consultarSinError($this->sql);
    }

    private function setSelectValue($val)
    {
        $this->select = $this->select . $val;
    }
    private function setGroupValue($val)
    {
        $this->group = $this->group . $val;
    }

    public function setSelect()
    {
        $campos = $this->filables;
        $table = $this->table;
        $sql = "";
        foreach ($campos as $key => $val) 
        {
            $coma = ",";
            if(end($campos) == $val)
            {
                $coma = "";
            }
            $campo = $this->filables[$key][0];
            $como = $key;
            $sql .= <<<SQL
            $table.$campo as $como$coma
SQL;
        }
        $sql .= ","; 
        $this->setSelectValue($sql,false);
    }

    public function setSelectJoin(Model $modelo)
    {
        $campos = $modelo->filables;
        $table = $modelo->table;
        $sql = "";
        foreach ($campos as $key => $val) 
        {
            $coma = ",";
            if(end($campos) == $val)
            {
                $coma = "";
            }
            $campo = $modelo->filables[$key][0];
            
            $como = $key;
            if($key == 'id')
            {
                $como = $table . "_" . $como;
            }
            $sql .= <<<SQL
            $table.$campo as $como$coma
SQL;
        }
        $this->setSelectValue($sql);
    }

    public function setGroupJoin(Model $modelo)
    {
        $campos = $modelo->filables;
        $table = $modelo->table;
        $sql = "";
        foreach ($campos as $key => $val) 
        {
            $coma = ",";
            if(end($campos) == $val)
            {
                $coma = "";
            }
            $campo = $modelo->filables[$key][0];
            $como = $key;
            $sql .= <<<SQL
            $table.$campo$coma
SQL;
        }
        $this->setGroupValue($sql,true);

    }

    private function setJoin(Model $modeloSecundario,$campo1,$campo2)
    {        
        $sql = <<<SQL
        INNER JOIN
            {$modeloSecundario->table}
        ON
            {$modeloSecundario->table}.{$campo1}::varchar = {$this->table}.$campo2::varchar

SQL;
        $this->join .= $sql;

    }

    private function setGroup()
    {
        $campos = $this->filables;
        $table = $this->table;
        $sql = "";
        foreach ($campos as $key => $val) 
        {
            $coma = ",";
            if(end($campos) == $val)
            {
                $coma = "";
            }
            $campo = $this->filables[$key][0];
            $como = $key;
            $sql .= <<<SQL
            $table.$campo$coma
SQL;
        }
        $sql .= ","; 
        $this->setGroupValue($sql,false);
    }
    
    public function join(Model $modeloSecundario,$campo1,$campo2)
    {
        $keys = '';
        $coma = ',';
        $id_valor = '';
        $id_campo = '';
        $this->setSelect();
        $this->setSelectJoin($modeloSecundario);
        $this->setJoin($modeloSecundario,$campo1,$campo2);
        $this->setGroup();
        $this->setGroupJoin($modeloSecundario);

        $this->setUltimoMetodoLlamado(__METHOD__);

        $this->sql = <<<SQL
        
        SELECT
            {$this->select}
        FROM
            {$this->table}
            {$this->join}
            {$this->where}
        GROUP BY
            {$this->group}
            {$this->limit}
            {$this->ofset}
SQL;
        return $this;
    }


    public function orderBy($campo,$sort)
    {
        $this->sql .= " ORDER BY $campo $sort";
        return $this;
    }

    //metodos para manipular consultas

    public function find()
    {
        $campos = $this->request;
        $id = $campos['id'];
        $id_campo = $this->filables['id'][0];

        $this->sql .= <<<SQL
            WHERE {$id_campo} = '{$id}'
SQL;

        return $this;
    }

    public function fristWhere()
    {
        $this->frist = false;
    }

    public function where($campo,$condicion,$valor)
    {
        /*$where = "";
        if($this->frist)
        {
            $where = " where ";
            $this->fristWhere();

        } else {

            $where = " and ";
        
        }*/

        $this->sql .= " and $campo $condicion $valor ";
        
        return $this;
    }

    public function end()
    {
        $id_campo = $this->filables['id'][0];
        $this->sql .= " ORDER BY $id_campo DESC LIMIT 1";
        return $this;
    }

    public function getOrCreate($data,$key = 'id')
    {
        $object = [];
        if(isset($data[$key])){
            $object = $this->read([$key=>$data[$key]])->get();

        }
        if(!$object)
        {
            if($this->create($data)->get())
            {
                $object = $this->read($data)->get();
            }
        }else{

            if($data != $object)
            {
                $data['id'] = $object[0]['id'];
                $result = $this->update($data)->get();
                $object = $this->read($data)->get();
            }
            
        }
        return $object;

    }

}