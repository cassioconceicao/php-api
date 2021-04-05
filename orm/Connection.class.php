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
 * ****************** NÃO EDITAR ***********************************************
 * Carrega Metadados do banco de dados
 */
//unset($_SESSION);
//$_SESSION = null;
//session_unset();
if (!isset($_SESSION["metadata"])) {

    if (DB_DSN == "pgsql") {
        $query = "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema NOT IN ('pg_catalog', 'information_schema')";
    } else {
        $query = "SHOW TABLES";
    }

    try {

        $pdo = new PDO(DB_DSN . ":host=" . DB_HOST . ";dbname=" . DB_NAME . (DB_DSN == "mysql" ? ";charset=utf8" : ""), DB_USER, DB_PASS, unserialize(PDO_OPTIONS));
        $st = $pdo->query($query);

        foreach ($st->fetchAll() as $tab) {

            // Tabela
            $table = strtolower(trim($tab[array_keys($tab)[0]]));

            $class = "";
            $isUpper = true;
            foreach (str_split($table) as $c) {

                if ($c == "_") {
                    $isUpper = true;
                } else {
                    if ($isUpper) {
                        $class .= strtoupper($c);
                        $isUpper = false;
                    } else {
                        $class .= strtolower($c);
                    }
                }
            }

            $_SESSION["metadata"][$class]["table_name"] = $table;

            // Colunas
            $st2 = $pdo->query("SELECT * FROM {$table} LIMIT 1");

            $err = $st2->errorInfo();
            if ($err[2]) {
                throw new Exception("Erro ao carregar metadados das colunas: [{$err[2]}]");
            }

            $columns = array();
            for ($index = 0; $index < $st2->columnCount(); $index++) {
                if (strpos(serialize($st2->getColumnMeta($index)), "primary_key")) {
                    $_SESSION["metadata"][$class]["primary_key"] = $st2->getColumnMeta($index)["name"];
                }
                $columns[$st2->getColumnMeta($index)["name"]] = DataType::parse($st2->getColumnMeta($index)["native_type"]);
            }

            $_SESSION["metadata"][$class]["columns"] = $columns;

            // Chave primária banco de dados Postgres
            if (DB_DSN == "pgsql") {

                $st2 = $pdo->query("SELECT c.column_name FROM information_schema.table_constraints tc JOIN information_schema.constraint_column_usage AS ccu USING (constraint_schema, constraint_name) JOIN information_schema.columns AS c ON c.table_schema = tc.constraint_schema AND tc.table_name = c.table_name AND ccu.column_name = c.column_name WHERE constraint_type = 'PRIMARY KEY' and c.table_name = '{$table}'");

                $err = $st2->errorInfo();
                if ($err[2]) {
                    throw new Exception("Erro ao carregar metadados de chave primária: [{$err[2]}]");
                }

                $_SESSION["metadata"][$class]["primary_key"] = strtolower(trim($st2->fetchColumn()));
            }

            //Chave extrangeiras
            if (DB_DSN == "mysql") {
                $query = "SELECT information_schema.KEY_COLUMN_USAGE.COLUMN_NAME AS col_name, information_schema.KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME AS ref_tab_name,information_schema.KEY_COLUMN_USAGE.REFERENCED_COLUMN_NAME AS ref_col_name FROM information_schema.KEY_COLUMN_USAGE WHERE information_schema.KEY_COLUMN_USAGE.TABLE_NAME = '{$table}' AND information_schema.KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME IS NOT NULL ";
            } else if (DB_DSN == "pgsql") {
                $query = "SELECT kcu.column_name AS col_name,ccu.table_name AS ref_tab_name,ccu.column_name AS ref_col_name FROM information_schema.table_constraints AS tc JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name = '{$table}'";
            } else {
                throw new Exception("Banco de dados não suportado.");
            }

            $st2 = $pdo->query($query);

            $err = $st2->errorInfo();
            if ($err[2]) {
                throw new Exception("Erro ao carregar metadados de chave extrangeira: [{$err[2]}]");
            }

            $fk = array();
            foreach ($st2->fetchAll() as $row) {
                $fk[$row["col_name"]] = $row["ref_tab_name"];
            }

            $_SESSION["metadata"][$class]["foreign_key"] = $fk;
        }
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
}
/* * **************************************************************************** */

/**
 * Description of Connection
 *
 * @author Cássio Conceição
 * @since 2021
 * @version 2021
 * @see http://ctecinf.com.br/
 */
class Connection {

    /**
     * Abre conexão com banco de dados
     * 
     * @return PDO
     * @throws Exception
     */
    protected function openConnection() {
        try {
            return new PDO(DB_DSN . ":host=" . DB_HOST . ";dbname=" . DB_NAME . (DB_DSN == "mysql" ? ";charset=utf8" : ""), DB_USER, DB_PASS, unserialize(PDO_OPTIONS));
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
        return $_SESSION["metadata"][$class]["table_name"];
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
    public function executeUpdate($sql, $params = array()) {

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
    public function executeQuery($query) {

        $conn = self::openConnection();

        $st = $conn->query($query);

        $err = $st->errorInfo();

        if ($err[2]) {
            throw new Exception("Erro na consulta: [{$err[2]}]");
        }

        return $st->fetchAll();
    }

}
