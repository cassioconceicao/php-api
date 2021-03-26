<?php

/*
 * Copyright (C) 2021 ctecinf.com.br
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of Model
 * 
 * @author Cássio Conceição
 * @since 2021
 * @version 2021
 * @see http://ctecinf.com.br/
 */
class Model extends Connection {

    /**
     * Dados das colunas da tabela
     * @var type array
     */
    protected $data;

    /**
     * Construtor
     */
    public function __construct() {
        $this->getTableName();
    }

    /**
     * Cria um objeto
     * 
     * @param array $value
     * @return Model
     */
    private function makeObject($value, $class = false) {

        if (!$class) {
            $class = new ReflectionClass(get_called_class());
        }

        $instance = $class->newInstanceWithoutConstructor();

        $prop1 = $class->getProperty("data");
        $prop1->setAccessible(true);
        $prop1->setValue($instance, $value);

        return $instance;
    }

    /**
     * Identificador do registro
     * 
     * @return int
     */
    public function getId() {

        $class = get_called_class();

        if (!isset($_SESSION["metadata"][$class])) {
            $this->getTableName();
        }

        return $this->data[$_SESSION["metadata"][$class]["primary_key"]];
    }

    /**
     * Pega valor da coluna
     * 
     * @param string $column Nome da coluna
     * @param boolean $getReferencedObject Retornar objeto referenciado de chave estrangeira caso coluna for <i>Foreign Key</i>.
     * @return mixed
     * @throws Exception
     */
    public function get($column, $getReferencedObject = true) {

        $class = get_called_class();

        if (array_key_exists($column, $_SESSION["metadata"][$class]["foreign_key"]) && $getReferencedObject) {

            try {

                $name = $_SESSION["metadata"][$class]["foreign_key"][$column];

                $class = new ReflectionClass($name);
                $instance = $class->newInstanceWithoutConstructor();

                return $instance->findById($this->data[$column]);
            } catch (Exception $exc) {
                throw new Exception("Classe modelo não encontrada para a tabela.$exc");
            }
        }

        return $this->data[$column];
    }

    /**
     * Configura valor da coluna
     * 
     * @param string $column Nome da coluna
     * @param mixed $value Valor
     */
    public function set($column, $value) {
        $this->data[$column] = $value;
    }

    /**
     * Consulta por ID
     * 
     * @param int $id
     * @return Model
     */
    public static function findById($id) {

        $class = get_called_class();

        if (!isset($_SESSION["metadata"][$class])) {
            self::getTableName();
        }

        $rs = self::find($id, 1, $_SESSION["metadata"][$class]["primary_key"]);

        return !$rs ? false : $rs[array_keys($rs)[0]];
    }

    /**
     * Ordena <i>array</i> com <i>ResultSet</i>
     * 
     * @param array $rs ResultSet
     * @param boolean $asc Ordenação ascendente ou descendente
     * @param string $column Nome da coluna para ordenar, na ausência desse parâmetro utiliza método <i>__toString</i>
     * @return array ResultSet
     */
    public static function sortResultSet($rs, $asc = true, $column = false) {

        uasort($rs, function($obj1, $obj2) use ($asc, $column) {

            $class = new ReflectionClass($obj1);
            $param = null;
            if ($column) {
                $method = $class->getMethod('get');
                $param = $column;
            } else {
                $method = $class->getMethod('__toString');
            }
            $value1 = $method->invoke($obj1, $param);
            $value2 = $method->invoke($obj2, $param);

            if ($asc) {
                return $value1 > $value2;
            } else {
                return $value1 < $value2;
            }
        });

        return $rs;
    }

    /**
     * Consulta
     * 
     * @param string $filter
     * @param int $limit default -1
     * @param array $columns
     * @return array
     */
    public static function find($filter = false, $limit = -1, $columns = false) {

        $class = get_called_class();
        $table = self::getTableName();
        $pkColumn = $_SESSION["metadata"][$class]["primary_key"];

        //"SELECT {$table}.* FROM {$table} JOIN municipio ON CASE WHEN {$table}.municipio_id IS NULL THEN (SELECT MIN(municipio.id) FROM municipio) ELSE {$table}.municipio_id END = municipio.id"
        $query = "SELECT {$table}.* FROM {$table}";

        if ($filter && !$columns) {
            $columns = array_keys($_SESSION["metadata"][$class]["columns"]);
        }

        if (is_array($columns)) {

            $query .= " WHERE ";

            $cols = array();
            foreach ($columns as $column) {
                if (DB_DSN == "pgsql") {
                    $cols[] = "LOWER(CAST({$table}.{$column} AS VARCHAR)) LIKE '" . strtolower($filter) . "%'";
                } else {
                    $cols[] = "LOWER({$table}.{$column}) LIKE '" . strtolower($filter) . "%'";
                }
            }

            $query .= implode(" OR ", $cols);
        } else if ($columns) {
            $query .= " WHERE {$table}.{$columns} = '{$filter}'";
        }

        if ($limit > 0) {
            $query .= " LIMIT {$limit}";
        }

        $rs = array();
        foreach (self::executeQuery($query) as $row) {
            $rs[$row[$pkColumn]] = self::makeObject($row);
        }

        return count($rs) == 0 ? false : $rs;
    }

    /**
     * Cria ou altera registro no banco de dados
     * 
     * @param type $params
     */
    public static function save($params = false) {
        
    }

    /**
     * Apaga registro
     */
    public function delete() {
        
    }

}
