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

require_once "Config.php";
require_once "DatabaseException.php";
require_once "DataType.php";
require_once "Metadata.php";
require_once "Connection.php";

$action = isset($_POST[AJAX::PARAM_NAME_ACTION]) ? $_POST[AJAX::PARAM_NAME_ACTION] : $_GET[AJAX::PARAM_NAME_ACTION];
$table = isset($_POST[AJAX::PARAM_NAME_TABLE]) ? $_POST[AJAX::PARAM_NAME_TABLE] : $_GET[AJAX::PARAM_NAME_TABLE];

switch ($action) {
    
    case AJAX::PARAM_VALUE_FIND_ALL:

        if (!$table) {
            echo json_encode(array("message" => "Parâmetro [table] inválido.", "type" => "error", "return" => "false"));
        } else {
            
            $pdo = Connection::open();
            
        }

        break;

    default:
        echo json_encode(array("message" => "Parâmetro [action] inválido.", "type" => "error", "return" => "false"));
        break;
}