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
 * Description of Connection
 *
 * @author Cássio Conceição
 * @since 2021
 * @version 2021
 * @see http://ctecinf.com.br/
 */
class Connection {

    /**
     * Abre conexão com o banco de dados
     * 
     * @return PDO
     * @throws DatabaseException
     */
    public static function open() {

        try {

            $connection = new PDO(DB_DSN . ":host=" . DB_HOST . ";dbname=" . DB_NAME . (DB_DSN == "mysql" ? ";charset=utf8" : ""), DB_USER, DB_PASS, unserialize(PDO_OPTIONS));

            if (!isset($_SESSION["metadata"])) {
                Metadata::create($connection);
            }

            return $connection;
        } catch (Exception $ex) {
            throw new DatabaseException($ex);
        }
    }

    /**
     * Update no banco de dados
     * 
     * @param string $sql
     * @return string If a sequence name was not specified for the name parameter, PDO::lastInsertId returns a string representing the row ID of the last row that was inserted into the database.
     * If a sequence name was specified for the name parameter, PDO::lastInsertId returns a string representing the last value retrieved from the specified sequence object.
     * If the PDO driver does not support this capability, PDO::lastInsertId triggers an IM001 SQLSTATE.
     * @throws DatabaseException
     */
    public static function executeUpdate($sql, $params = null) {

        $connection = Connection::open();

        $statement = $connection->prepare($sql);

        $error = $statement->errorInfo();

        if ($error[2]) {
            throw new DatabaseException($error[2]);
        }

        try {
            $statement->execute($params);
        } catch (Exception $ex) {
            throw new DatabaseException($ex);
        }

        if (DB_DSN == "firebird") {

            $table = str_replace("insert into ", "", strtolower($sql));
            $table = str_replace("update ", "", $table);
            $table = str_replace("delete from ", "", $table);
            $table = substr($table, 0, strpos($table, " "));

            $sequenceName = Metadata::getSequenceName($table);

            try {
                $rs = $connection->query("SELECT GEN_ID({$sequenceName}, 0) FROM RDB\$DATABASE");
                return $rs->fetchColumn();
            } catch (Exception $ex) {
                throw new DatabaseException($ex);
            }
        }

        try {
            return $connection->lastInsertId();
        } catch (Exception $ex) {
            throw new DatabaseException($ex);
        }
    }

}

