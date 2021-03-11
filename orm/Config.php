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

session_start();

/**
 * Configurar Conexão com o Banco de Dados<br>
 * DB_DSN: "mysql" ou "pgsql"
 */
define("DB_DSN", "mysql");
define("DB_HOST", "localhost");
define("DB_NAME", "hobby");
define("DB_USER", "root");
define("DB_PASS", "root");

//define("DB_DSN", "pgsql");
//define("DB_HOST", "localhost");
//define("DB_NAME", "hobby");
//define("DB_USER", "postgres");
//define("DB_PASS", "postgres");

define("PDO_OPTIONS", serialize(array(
    PDO::ATTR_CASE => PDO::CASE_LOWER,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
)));

/**
 * Nome do diretório com as classes modelos que extendem de Model.class.php
 */
define("MODEL_DIR", "model");
