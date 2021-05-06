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
 * Description of Metadata
 *
 * @author Cássio Conceição
 * @since 2021
 * @version 2021
 * @see http://ctecinf.com.br/
 */
class Metadata {

    //Firebird
    const FIREBIRD_TABLES = "SELECT TRIM(LOWER(RDB\$RELATION_NAME)) AS name FROM RDB\$RELATIONS WHERE RDB\$SYSTEM_FLAG IS NULL OR RDB\$SYSTEM_FLAG=0";
    const FIREBIRD_SEQUENCE = "SELECT TRIM(LOWER(RDB\$GENERATOR_NAME)) AS sequence FROM RDB\$GENERATORS WHERE LOWER(RDB\$GENERATOR_NAME) LIKE '%{tableName}%'";
    const FIREBIRD_PRIMARY_KEY = "SELECT TRIM(LOWER(i.RDB\$FIELD_NAME)) AS id FROM RDB\$RELATION_CONSTRAINTS r LEFT JOIN RDB\$INDEX_SEGMENTS i ON i.RDB\$INDEX_NAME = r.RDB\$INDEX_NAME WHERE LOWER(r.RDB\$RELATION_NAME) = '{tableName}' AND LOWER(r.RDB\$CONSTRAINT_TYPE) = 'primary key'";
    const FIREBIRD_COLUMNS = "SELECT TRIM(LOWER(r.RDB\$FIELD_NAME)) AS name, (SELECT TRIM(LOWER(t.RDB\$TYPE_NAME)) FROM RDB\$TYPES t WHERE t.RDB\$FIELD_NAME = 'RDB\$FIELD_TYPE' AND t.RDB\$TYPE = f.RDB\$FIELD_TYPE) AS type, f.RDB\$FIELD_LENGTH AS length, CASE LOWER(r.RDB\$NULL_FLAG) WHEN 1 THEN 'true' ELSE 'false' END AS not_null FROM RDB\$RELATION_FIELDS r LEFT JOIN RDB\$FIELDS f ON r.RDB\$FIELD_SOURCE = f.RDB\$FIELD_NAME LEFT JOIN RDB\$COLLATIONS coll ON f.RDB\$COLLATION_ID = coll.RDB\$COLLATION_ID LEFT JOIN RDB\$CHARACTER_SETS cset ON f.RDB\$CHARACTER_SET_ID = cset.RDB\$CHARACTER_SET_ID WHERE LOWER(r.RDB\$RELATION_NAME)='{tableName}' AND (coll.RDB\$COLLATION_NAME IS NULL OR coll.RDB\$COLLATION_NAME = 'NONE') ORDER BY r.RDB\$FIELD_POSITION";
    const FIREBIRD_FOREIGN_KEY = "SELECT TRIM(LOWER((SELECT col.RDB\$FIELD_NAME FROM RDB\$INDEX_SEGMENTS col WHERE col.RDB\$INDEX_NAME = c.RDB\$INDEX_NAME))) AS \"column\", TRIM(LOWER((SELECT ref.RDB\$RELATION_NAME FROM RDB\$RELATION_CONSTRAINTS ref WHERE ref.RDB\$INDEX_NAME = i.RDB\$FOREIGN_KEY))) AS \"reference\" FROM RDB\$RELATION_CONSTRAINTS c LEFT JOIN RDB\$INDEX_SEGMENTS s ON s.RDB\$INDEX_NAME = c.RDB\$INDEX_NAME LEFT JOIN RDB\$INDICES i ON i.RDB\$INDEX_NAME = c.RDB\$INDEX_NAME WHERE LOWER(c.RDB\$RELATION_NAME) = '{tableName}' AND LOWER(c.RDB\$CONSTRAINT_TYPE) = 'foreign key'";
    //Postgres
    const POSTGRES_TABLES = "SELECT LOWER(table_name) AS name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema NOT IN ('pg_catalog', 'information_schema')";
    const POSTGRES_PRIMARY_KEY = "SELECT LOWER(c.column_name) AS id FROM information_schema.table_constraints tc JOIN information_schema.constraint_column_usage AS ccu USING (constraint_schema, constraint_name) JOIN information_schema.columns AS c ON c.table_schema = tc.constraint_schema AND tc.table_name = c.table_name AND ccu.column_name = c.column_name WHERE LOWER(constraint_type) = 'primary key' AND tc.table_name = '{tableName}'";
    const POSTGRES_COLUMNS = "SELECT LOWER(column_name) AS name, LOWER(udt_name) AS type, CASE WHEN character_maximum_length IS NOT NULL THEN character_maximum_length ELSE 255 END AS length, CASE LOWER(is_nullable) WHEN 'no' THEN 'true' ELSE 'false' END AS not_null FROM information_schema.columns WHERE table_schema NOT IN ('information_schema', 'pg_catalog') AND table_name = '{tableName}' ORDER BY table_schema, table_name, ordinal_position";
    const POSTGRES_FOREIGN_KEY = "SELECT LOWER(kcu.column_name) AS column, LOWER(ccu.table_name) AS reference FROM information_schema.table_constraints AS tc JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name AND tc.table_schema = kcu.table_schema JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name AND ccu.table_schema = tc.table_schema WHERE LOWER(tc.constraint_type) = 'foreign key' AND tc.table_name='{tableName}'";
    //MySQL
    const MYSQL_TABLES = "SELECT LOWER(table_name) AS name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema = '{tableSchema}'";
    const MYSQL_PRIMARY_KEY = "SELECT LOWER(k.column_name) AS id FROM information_schema.table_constraints t JOIN information_schema.key_column_usage k USING(constraint_name,table_schema, table_name) WHERE LOWER(t.constraint_type) = 'primary key' AND t.table_schema = '{tableSchema}' AND t.table_name = '{tableName}'";
    const MYSQL_COLUMNS = "SELECT LOWER(column_name) AS name, LOWER(data_type) AS type, CASE WHEN character_maximum_length IS NOT NULL THEN character_maximum_length ELSE 255 END AS length, CASE LOWER(is_nullable) WHEN 'no' THEN 'true' ELSE 'false' END AS not_null FROM information_schema.columns WHERE table_schema = '{tableSchema}' AND table_name = '{tableName}' ORDER BY ordinal_position";
    const MYSQL_FOREIGN_KEY = "SELECT LOWER(column_name) AS \"column\", LOWER(referenced_table_name) AS \"reference\" FROM information_schema.key_column_usage WHERE table_schema = '{tableSchema}' AND table_name = '{tableName}' AND referenced_table_name IS NOT NULL";

    /**
     * Configura sessão de metadados do banco de dados
     * 
     * @param PDO $connection Conexão com o banco de dados
     * @throws DatabaseException
     */
    public static function create($connection) {

        try {

            if (DB_DSN == "pgsql") {
                $queryTables = Metadata::POSTGRES_TABLES;
                $queryPrimaryKey = Metadata::POSTGRES_PRIMARY_KEY;
                $queryColumns = Metadata::POSTGRES_COLUMNS;
                $queryForeignKey = Metadata::POSTGRES_FOREIGN_KEY;
            } else if (DB_DSN == "firebird") {
                $queryTables = Metadata::FIREBIRD_TABLES;
                $queryPrimaryKey = Metadata::FIREBIRD_PRIMARY_KEY;
                $queryColumns = Metadata::FIREBIRD_COLUMNS;
                $queryForeignKey = Metadata::FIREBIRD_FOREIGN_KEY;
            } else if (DB_DSN == "mysql") {
                $queryTables = str_replace("{tableSchema}", DB_NAME, Metadata::MYSQL_TABLES);
                $queryPrimaryKey = str_replace("{tableSchema}", DB_NAME, Metadata::MYSQL_PRIMARY_KEY);
                $queryColumns = str_replace("{tableSchema}", DB_NAME, Metadata::MYSQL_COLUMNS);
                $queryForeignKey = str_replace("{tableSchema}", DB_NAME, Metadata::MYSQL_FOREIGN_KEY);
            }

            $_SESSION["metadata"] = Metadata::get($connection, $queryTables, $queryPrimaryKey, $queryColumns, $queryForeignKey);
        } catch (Exception $ex) {
            throw new DatabaseException($ex);
        }
    }

    /**
     * Extraí metadados do banco de dados
     * 
     * @param PDO $connection
     * @param string $queryTables
     * @param string $queryPrimaryKey
     * @param string $queryColumns
     * @param string $queryForeignKey
     * @return array
     * @throws DatabaseException
     */
    protected static function get($connection, $queryTables, $queryPrimaryKey, $queryColumns, $queryForeignKey) {

        try {

            $metadata = array();

            foreach ($connection->query($queryTables)->fetchAll() as $tables) {

                $sequence = DB_DSN == "firebird" ? $connection->query(str_replace("{tableName}", $tables["name"], Metadata::FIREBIRD_SEQUENCE))->fetchColumn() : false;

                $foreignKeys = array();

                foreach ($connection->query(str_replace("{tableName}", $tables["name"], $queryForeignKey))->fetchAll() as $rs) {
                    $foreignKeys[$rs["column"]] = $rs["reference"];
                }

                $metadata[$tables["name"]] = array(
                    "sequence" => !$sequence ? "" : $sequence,
                    "primary_key" => $connection->query(str_replace("{tableName}", $tables["name"], $queryPrimaryKey))->fetchColumn(),
                    "columns" => $connection->query(str_replace("{tableName}", $tables["name"], $queryColumns))->fetchAll(),
                    "foreign_key" => $foreignKeys
                );
            }

            return $metadata;
        } catch (Exception $ex) {
            throw new DatabaseException($ex);
        }
    }

    /**
     * GET
     * 
     * @return string
     */
    public static function getTables() {
        return array_keys($_SESSION["metadata"]);
    }

    /**
     * GET
     * 
     * @param string $table
     * @return string
     */
    public static function getPrimaryKeyName($table) {
        return $_SESSION["metadata"][strtolower($table)]["primary_key"];
    }

    /**
     * GET
     * 
     * @param string $table
     * @return string
     */
    public static function getSequenceName($table) {
        return $_SESSION["metadata"][strtolower($table)]["sequence"];
    }

    /**
     * GET
     * 
     * @param string $table
     * @return array [{"name" => "colum_name", "type" => "colum_type", "length" => "colum_length", "not_null" => "colum_not_null"}, ...]
     */
    public static function getColumns($table) {
        return $_SESSION["metadata"][strtolower($table)]["columns"];
    }

    /**
     * GET
     * 
     * @param string $table
     * @return array {"column_name1", "column_name2", ...}
     */
    public static function getColumnsName($table) {

        $columns = array();

        foreach ($_SESSION["metadata"][strtolower($table)]["columns"] as $value) {
            $columns[] = $value["name"];
        }

        return $columns;
    }

    /**
     * GET
     * 
     * @param string $table
     * @return array {"column_name1" => "column_type1", "column_name2" => "column_type2", ...}
     */
    public static function getColumnsDataType($table) {

        $columns = array();

        foreach ($_SESSION["metadata"][strtolower($table)]["columns"] as $value) {
            $columns[$value["name"]] = DataType::parse($value["type"]);
        }

        return $columns;
    }

    /**
     * GET
     * 
     * @param string $table
     * @return array {"column_name1" => "column_length1", "column_name2" => "column_length2", ...}
     */
    public static function getColumnsLength($table) {

        $columns = array();

        foreach ($_SESSION["metadata"][strtolower($table)]["columns"] as $value) {
            $columns[$value["name"]] = intval($value["length"]);
        }

        return $columns;
    }

    /**
     * GET
     * 
     * @param string $table
     * @return array {"column_name1" => "column_not_null1", "column_name2" => "column_not_null2", ...}
     */
    public static function getColumnsNotNull($table) {

        $columns = array();

        foreach ($_SESSION["metadata"][strtolower($table)]["columns"] as $value) {
            $columns[$value["name"]] = $value["not_null"];
        }

        return $columns;
    }

    /**
     * GET
     * 
     * @param type $table
     * @return array {"column_name1" => "table_reference", "column_name2" => "table_reference"}
     */
    public static function getReferencedTables($table) {
        return $_SESSION["metadata"][strtolower($table)]["foreign_key"];
    }

}
