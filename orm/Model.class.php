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
class Model {

    const STRING = 0;
    const BOOLEAN = 1;
    const TEXT = 2;
    const DATE = 3;
    const TIME = 4;
    const TIMESTAMP = 5;
    const DECIMAL = 6;
    const TINYINT = 7;
    const INTEGER = 8;
    const BIGINT = 9;

    /**
     * Dados das colunas da tabela
     * @var type array
     */
    protected $data;

    /**
     * Abre conexão com banco de dados
     * 
     * @return PDO
     * @throws Exception
     */
    protected function openConnection() {
        try {
            return new PDO(DB_DSN . ":host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, unserialize(PDO_OPTIONS));
        } catch (Exception $exc) {
            throw $exc;
        }
    }

    /**
     * Nome da Tabela
     * 
     * @param string $class Nome da classe
     * @return string Nome da tabela no banco de dados
     * @throws Exception
     */
    protected function getTableName() {

        $class = get_called_class();

        if (!isset($_SESSION["metadata"][$class])) {

            $reflection = new ReflectionClass($class);

            $name = "";

            foreach (str_split(trim($reflection->name)) as $c) {
                if (ctype_upper($c) && strlen($name) > 0) {
                    $name .= strtolower("_{$c}");
                } else {
                    $name .= strtolower($c);
                }
            }

            $_SESSION["metadata"][$class]["table_name"] = $name;
            self::setColumns($class, $name);
        }

        return $_SESSION["metadata"][$class]["table_name"];
    }

    /**
     * Configura colunas da tabela
     * 
     * @param Class $class
     * @param string $table
     * @throws Exception
     */
    private function setColumns($class, $table) {

        $conn = self::openConnection();

        $query = "SELECT * FROM {$table} LIMIT 1";
        $st = $conn->query($query);
        $err = $st->errorInfo();
        if ($err[2]) {
            throw new Exception($err[2]);
        }

        self::setPrimaryKeyPGSQL($class, $table, $conn);

        $columns = array();
        for ($index = 0; $index < $st->columnCount(); $index++) {

            if (strpos(serialize($st->getColumnMeta($index)), "primary_key")) {
                $_SESSION["metadata"][$class]["primary_key"] = $st->getColumnMeta($index)["name"];
            }

            $columns[$st->getColumnMeta($index)["name"]] = self::parseType($st->getColumnMeta($index)["native_type"]);
        }

        $_SESSION["metadata"][$class]["columns"] = $columns;
    }

    /**
     * Configura padrão de tipos de dados
     * 
     * @param string $type
     * @return string
     */
    private function parseType($type) {

        switch (strtolower($type)) {

            case "bool":
            case "boolean":
                return Model::BOOLEAN;

            case "int2":
            case "tiny":
            case "short":
                return Model::TINYINT;

            case "int4":
            case "long":
                return Model::INTEGER;

            case "int8":
            case "longlong":
                return Model::BIGINT;

            case "float":
            case "float2":
            case "float4":
            case "float8":
            case "numeric":
            case "real":
            case "double":
            case "newdecimal":
                return Model::DECIMAL;

            case "text":
            case "blob":
            case "lob":
                return Model::TEXT;

            case "date":
                return Model::DATE;

            case "time":
                return Model::TIME;

            case "timestamp":
            case "datetime":
                return Model::TIMESTAMP;

            default:
                return Model::STRING;
        }
    }

    /**
     * Verifica se Postegres e configura chave primária
     * 
     * @param type $class
     * @param type $table
     * @param type $conn
     * @throws Exception
     */
    private function setPrimaryKeyPGSQL($class, $table, $conn) {

        if (DB_DSN == "pgsql") {

            $query = "SELECT c.column_name
                FROM information_schema.table_constraints tc 
                JOIN information_schema.constraint_column_usage AS ccu USING (constraint_schema, constraint_name) 
                JOIN information_schema.columns AS c ON c.table_schema = tc.constraint_schema
                AND tc.table_name = c.table_name AND ccu.column_name = c.column_name
                WHERE constraint_type = 'PRIMARY KEY' and c.table_name = '{$table}'";

            $st = $conn->query($query);

            $err = $st->errorInfo();
            if ($err[2]) {
                throw new Exception($err[2]);
            }

            $_SESSION["metadata"][$class]["primary_key"] = $st->fetchColumn();
        }
    }

    /**
     * Executa SQL
     * 
     * @param string $sql
     * @param array $params
     * @return boolean
     * 
     * @throws Exception
     */
    protected function executeUpdate($sql, $params = array()) {

        $conn = self::openConnection();

        $st = $conn->prepare($sql);

        $err1 = $st->errorInfo();

        if ($err1[2]) {
            throw new Exception($err1[2]);
        }

        $rs = $st->execute($params);

        $err2 = $st->errorInfo();

        if ($err2[2]) {
            throw new Exception($err2[2]);
        }

        return $rs;
    }

    /**
     * Executa Query
     * 
     * @param string $query
     * @return array
     * 
     * @throws Exception
     */
    protected function executeQuery($query) {

        $conn = self::openConnection();

        $st = $conn->query($query);

        $err = $st->errorInfo();

        if ($err[2]) {
            throw new Exception($err[2]);
        }

        return $st->fetchAll();
    }

    /**
     * Cria um objeto
     * 
     * @param array $value
     * @return Model
     */
    protected function makeObject($value) {

        $class = new ReflectionClass(get_called_class());
        $instance = $class->newInstanceWithoutConstructor();

        $prop1 = $class->getProperty("data");
        $prop1->setAccessible(true);
        $prop1->setValue($instance, $value);

        return $instance;
    }

    /**
     * Pega valor da coluna
     * 
     * @param string $name
     * @return mixed
     */
    public function getValue($name) {
        return $this->data[$name];
    }

    /**
     * Configura valor da coluna
     * 
     * @param string $name
     * @param mixed $value
     */
    public function setValue($name, $value) {
        $this->data[$name] = $value;
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

        return self::find($id, $_SESSION["metadata"][$class]["primary_key"]);
    }

    /**
     * Consulta
     * 
     * @param string $filter
     * @param array $columns
     * @return array
     */
    public static function find($filter = false, $columns = false) {

        $class = get_called_class();
        $table = self::getTableName();
        $pkColumn = $_SESSION["metadata"][$class]["primary_key"];

        $query = "SELECT * FROM {$table}";

        if ($filter && !$columns) {
            $columns = array_keys($_SESSION["metadata"][$class]["columns"]);
        }

        if (is_array($columns)) {

            $query .= " WHERE ";

            $cols = array();
            foreach ($columns as $column) {
                $cols[] = "{$column} LIKE '$filter%'";
            }

            $query .= implode(" OR ", $cols);
        } else if ($columns) {
            $query .= " WHERE {$columns} = '{$filter}'";
        }

        $rs = array();
        foreach (self::executeQuery($query) as $row) {
            $rs[$row[$pkColumn]] = self::makeObject($row);
        }

        return count($rs) == 0 ? false : (count($rs) == 1 ? $rs[array_keys($rs)[0]] : $rs);
    }

}
