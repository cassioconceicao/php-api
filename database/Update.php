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
 * Description of Update
 *
 * @author Cássio Conceição
 * @since 2021
 * @version 2021
 * @see http://ctecinf.com.br/
 */
class Update {

    const INSERT = "INSERT";
    const UPDATE = "UPDATE";
    const DELETE = "DELETE";

    private $table;
    private $sql;

    /**
     * 
     * @param string $table Tabela a executar UPDATE
     */
    function __construct($table) {
        $this->table = $table;
        $this->createUpdateSQL();
    }

    /**
     * Pega o SQL com os valores
     * @param array $data
     * @return string
     * @throws DatabaseException
     */
    private function getSQL($data) {

        if (!$this->sql) {
            throw new DatabaseException("SQL not found.");
        }

        $sql = $this->sql;

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (strlen($value) == 0) {
                    $sql = str_replace(" :" . $key . ",", " NULL,", $sql);
                    $sql = str_replace(" :" . $key . ")", " NULL)", $sql);
                    $sql = str_replace(" :" . $key . " ", " NULL ", $sql);
                } else {
                    $sql = str_replace(" :" . $key . ",", " '" . $value . "',", $sql);
                    $sql = str_replace(" :" . $key . ")", " '" . $value . "')", $sql);
                    $sql = str_replace(" :" . $key . " ", " '" . $value . "' ", $sql);
                }
            }
        }

        foreach (Metadata::getColumnsName($this->table) as $column) {

            if (strpos($sql, " :" . $column . ",") != false) {
                $sql = str_replace(" :" . $column . ",", " NULL,", $sql);
            }

            if (strpos($sql, " :" . $column . ")") != false) {
                $sql = str_replace(" :" . $column . ")", " NULL)", $sql);
            }

            if (strpos($sql, " :" . $column . " ") != false) {
                $sql = str_replace(" :" . $column . " ", " NULL ", $sql);
            }
        }

        return $sql;
    }

    /**
     * Cria SQL padrão para inserir dados na tabela
     *
     * @throws DatabaseException
     * @see<br>
     * <code>
     * MySQL: id BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT<br>
     * Postgres: id SERIAL NOT NULL PRIMARY KEY<br>
     * Firebird: Criar sequenciador: id BIGINT NOT NULL PRIMARY KEY | CREATE
     * SEQUENCE seq_[table_name]_id
     * </code>
     */
    protected function createInsertSQL() {

        $columnId = Metadata::getPrimaryKeyName($this->table);
        $sequence = false;

        if (DB_DSN == "firebird") {
            $sequence = "GEN_ID(" . Metadata::getSequenceName($this->table) . ", 1)";
        }

        $columns = Metadata::getColumnsName($this->table);

        foreach ($columns as $key => $column) {
            if ($column == $columnId) {
                unset($columns[$key]);
                break;
            }
        }

        $this->sql = "";
        $this->sql .= "INSERT INTO ";
        $this->sql .= $this->table;
        $this->sql .= " (";
        $this->sql .= implode(", ", $columns);
        $this->sql .=!$sequence ? "" : ", " . $columnId;
        $this->sql .= ") VALUES ( :";
        $this->sql .= implode(", :", $columns);
        $this->sql .=!$sequence ? "" : ", " . $sequence;
        $this->sql .= ")";
    }

    /**
     * Cria SQL padrão para alterar dados na tabela
     *
     * @throws DatabaseException
     */
    protected function createUpdateSQL() {

        $columnId = Metadata::getPrimaryKeyName($this->table);
        $columns = Metadata::getColumnsName($this->table);

        foreach ($columns as $key => $column) {
            if ($column == $columnId) {
                unset($columns[$key]);
                break;
            }
        }

        $this->sql = "";
        $this->sql .= "UPDATE ";
        $this->sql .= $this->table;
        $this->sql .= " SET ";

        for ($i = 0; $i < count($columns); $i++) {

            if (strlen(trim($columns[$i]))) {

                $this->sql .= $columns[$i];
                $this->sql .= " = :";
                $this->sql .= $columns[$i];

                if ($i < count($columns) - 1) {
                    $this->sql .= ", ";
                }
            }
        }

        $this->sql .= " WHERE ";
        $this->sql .= $columnId;
        $this->sql .= " = :";
        $this->sql .= $columnId;
        $this->sql .= " ";
    }

    /**
     * Cria SQL padrão para apagar registro na tabela
     *
     * @throws DatabaseException
     */
    protected function createDeleteSQL() {

        $columnId = Metadata::getPrimaryKeyName($this->table);

        $this->sql = "";
        $this->sql .= "DELETE FROM ";
        $this->sql .= $this->table;
        $this->sql .= " WHERE ";
        $this->sql .= $columnId;
        $this->sql .= " = :";
        $this->sql .= $columnId;
        $this->sql .= " ";
    }

    /**
     * Tipo da operação com o banco de dados
     * 
     * @param string $str INSERT, UPDATE OR DELETE
     */
    public function setSQL($str) {

        switch (strtoupper($str)) {

            case Update::INSERT:
                $this->createInsertSQL();
                break;

            case Update::UPDATE:
                $this->createUpdateSQL();
                break;

            case Update::DELETE:
                $this->createDeleteSQL();
                break;
        }
    }

    /**
     * Executa SQL
     * 
     * @param array $data { "column1" => "value1", "column2" => "value2", ... }
     * @return string If a sequence name was not specified for the name parameter, PDO::lastInsertId returns a string representing the row ID of the last row that was inserted into the database.
     * If a sequence name was specified for the name parameter, PDO::lastInsertId returns a string representing the last value retrieved from the specified sequence object.
     * If the PDO driver does not support this capability, PDO::lastInsertId triggers an IM001 SQLSTATE.
     * @throws DatabaseException
     */
    public function execute($data) {
        $sql = $this->getSQL($data);
        return Connection::executeUpdate($sql);
    }

    public function __toString() {
        return $this->sql;
    }

}
