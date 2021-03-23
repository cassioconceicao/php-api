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
     * Construtor
     */
    public function __construct() {
        $this->getTableName();
    }

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
    protected function getTableName($class = false) {

        if (!$class) {
            $class = get_called_class();
        }

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
            throw new Exception("Erro ao carregar metadados de colunas: [{$err[2]}]");
        }

        $columns = array();
        for ($index = 0; $index < $st->columnCount(); $index++) {

            if (strpos(serialize($st->getColumnMeta($index)), "primary_key")) {
                $_SESSION["metadata"][$class]["primary_key"] = $st->getColumnMeta($index)["name"];
            }

            $columns[$st->getColumnMeta($index)["name"]] = self::parseType($st->getColumnMeta($index)["native_type"]);
        }

        if (DB_DSN == "pgsql") {
            self::setPrimaryKeyPGSQL($class, $table, $conn);
        }

        self::setForeignKeys($class, $table, $conn);

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

        $query = "SELECT c.column_name FROM information_schema.table_constraints tc JOIN information_schema.constraint_column_usage AS ccu USING (constraint_schema, constraint_name) JOIN information_schema.columns AS c ON c.table_schema = tc.constraint_schema AND tc.table_name = c.table_name AND ccu.column_name = c.column_name WHERE constraint_type = 'PRIMARY KEY' and c.table_name = '{$table}'";

        $st = $conn->query($query);

        $err = $st->errorInfo();
        if ($err[2]) {
            throw new Exception("Erro ao carregar metadados de chave primária: [{$err[2]}]");
        }

        $_SESSION["metadata"][$class]["primary_key"] = $st->fetchColumn();
    }

    private function setForeignKeys($class, $table, $conn) {

        // Colunas "col_name", "ref_tab_name" e "ref_col_name"
        if (DB_DSN == "mysql") {
            $query = "SELECT information_schema.KEY_COLUMN_USAGE.COLUMN_NAME AS col_name, information_schema.KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME AS ref_tab_name,information_schema.KEY_COLUMN_USAGE.REFERENCED_COLUMN_NAME AS ref_col_name FROM information_schema.KEY_COLUMN_USAGE WHERE information_schema.KEY_COLUMN_USAGE.TABLE_NAME = '{$table}' AND information_schema.KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME IS NOT NULL ";
        } else if (DB_DSN == "pgsql") {
            $query = "SELECT kcu.column_name AS col_name,ccu.table_name AS ref_tab_name,ccu.column_name AS ref_col_name FROM information_schema.table_constraints AS tc JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name = '{$table}'";
        } else {
            throw new Exception("Banco de dados não suportado.");
        }

        $st = $conn->query($query);

        $err = $st->errorInfo();
        if ($err[2]) {
            throw new Exception("Erro ao carregar metadados de chave extrangeira: [{$err[2]}]");
        }

        $fk = array();
        foreach ($st->fetchAll() as $row) {
            $fk[$row["col_name"]] = $row["ref_tab_name"];
        }

        $_SESSION["metadata"][$class]["foreign_key"] = $fk;
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
            throw new Exception("Erro ao preparar SQL: [{$err[2]}]");
        }

        $rs = $st->execute($params);

        $err2 = $st->errorInfo();

        if ($err2[2]) {
            throw new Exception("Erro ao executar SQL: [{$err[2]}]");
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
            throw new Exception("Erro na consulta: [{$err[2]}]");
        }

        return $st->fetchAll();
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
            self::getTableName();
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

        return !$rs ? false : $rs[0];
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
                $cols[] = "LOWER({$table}.{$column}) LIKE '" . strtolower($filter) . "%'";
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
