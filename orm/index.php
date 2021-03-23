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
 * *********** NÃO EDITAR ******************************************************
 */
$path = "orm/";
while (!file_exists($path)) {
    $path = "../" . $path;
}

/**
 * Define PATH
 */
define("ORM_PATH", $path);
define("MODEL_PATH", "{$path}model/");
define("CONTROLLER_PATH", "{$path}controller/");

/**
 * Incluí configuração e superclasse das modelos
 */
require_once "{$path}Config.php";
require_once "{$path}Model.class.php";

if (isset($_GET["create"])) {

    if(DB_DSN == "pgsql") {
        $query = "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema NOT IN ('pg_catalog', 'information_schema')";
    } else {
        $query = "SHOW TABLES";
    }
    
    $pdo = new PDO(DB_DSN . ":host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, unserialize(PDO_OPTIONS));
    $st = $pdo->query($query);
    $rs = $st->fetchAll();

    foreach ($rs as $row) {

        $table = trim($row[array_keys($row)[0]]);
        $name = "";
        $isUpper = true;

        foreach (str_split($table) as $c) {

            if ($c == "_") {
                $isUpper = true;
            } else {
                if ($isUpper) {
                    $name .= strtoupper($c);
                    $isUpper = false;
                } else {
                    $name .= strtolower($c);
                }
            }
        }

        echo $name;
    }
} else {

    /**
     * Incluí classes modelos
     */
    $handle = opendir(MODEL_PATH);
    if ($handle) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                require_once MODEL_PATH . $file;
            }
        }
        closedir($handle);
    }
}

define("ACTION_SAVE", "save");
define("ACTION_DELETE", "delete");
define("ACTION_FIND", "find");

/**
 * Funções para controller
 */
function getAction() {
    if (isset($_POST["action"])) {
        return strtolower($_POST["action"]);
    } else if (isset($_GET["action"])) {
        return strtolower($_GET["action"]);
    } else {
        return false;
    }
}

function getTerm() {
    if (isset($_POST["term"])) {
        return $_POST["term"];
    } else if (isset($_GET["term"])) {
        return $_GET["term"];
    } else {
        return "";
    }
}

function getMaxResults() {
    if (isset($_POST["limit"])) {
        return $_POST["limit"];
    } else if (isset($_GET["limit"])) {
        return $_GET["limit"];
    } else {
        return -1;
    }
}

function getId() {
    if (isset($_POST["id"])) {
        return $_POST["id"];
    } else if (isset($_GET["id"])) {
        return $_GET["id"];
    } else {
        return "";
    }
}

function getData() {
    if (isset($_POST["data"])) {
        return $_POST["data"];
    } else if (isset($_GET["data"])) {
        return $_GET["data"];
    } else {
        return false;
    }
}
