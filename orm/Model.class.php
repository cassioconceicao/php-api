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

    /**
     * Dados das colunas da tabela
     * @var type array
     */
    protected $data;

    /**
     * Tipo de dados das colunas da tabela
     * @var type array
     */
    protected $type;

    /**
     * Abre conexão com banco de dados
     * 
     * @return PDO
     * @throws Exception
     */
    protected static function openConnection() {
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
     */
    protected static function getTable($class = false) {

        if (!$class) {
            $class = get_called_class();
        }

        if (!isset($_SESSION["meta"][$class]["table_name"])) {

            $reflection = new ReflectionClass($class);

            $name = "";

            foreach (str_split(trim($reflection->name)) as $c) {
                if (ctype_upper($c) && strlen($name) > 0) {
                    $name .= strtolower("_{$c}");
                } else {
                    $name .= strtolower($c);
                }
            }

            $_SESSION["meta"][$class]["table_name"] = $name;
        }

        return $_SESSION["meta"][$class]["table_name"];
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
    protected static function executeUpdate($sql, $params = array()) {

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
    protected static function executeQuery($query) {

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
    protected static function makeObject($value, $type) {

        $class = new ReflectionClass(get_called_class());
        $instance = $class->newInstanceWithoutConstructor();

        $prop1 = $class->getProperty("data");
        $prop1->setAccessible(true);
        $prop1->setValue($instance, $value);

        $prop2 = $class->getProperty("type");
        $prop2->setAccessible(true);
        $prop2->setValue($instance, $type);

        return $instance;
    }

    public function getValue($name) {
        return $this->data[$name];
    }

    public function setValue($name, $value) {
        return $this->data[$name] = $value;
    }

    public function getType($name) {
        return $this->type[$name];
    }

    public static function find() {

        $table = self::getTable();
        $conn = self::openConnection();
        $query = "SELECT * FROM {$table}";

        $st = $conn->query($query);
        $err = $st->errorInfo();
        if ($err[2]) {
            throw new Exception($err[2]);
        }

        $type = array();
        for ($index = 0; $index < $st->columnCount(); $index++) {
            
            var_dump($st->getColumnMeta($index));
            
            $type[$st->getColumnMeta($index)["name"]] = $st->getColumnMeta($index)["native_type"];
        }

        $rs = array();
        foreach ($st->fetchAll() as $value) {
            $rs[] = self::makeObject($value, $type);
        }

        return $rs;
    }

}
