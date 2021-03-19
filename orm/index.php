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
$ormDir = "orm";
$path = ".." . DIRECTORY_SEPARATOR;

foreach (array_reverse(explode(DIRECTORY_SEPARATOR, dirname($_SERVER["PHP_SELF"]))) as $dir) {
    if (is_dir($path . $dir . DIRECTORY_SEPARATOR . $ormDir)) {
        $path = $path . $dir . DIRECTORY_SEPARATOR . $ormDir . DIRECTORY_SEPARATOR;
        break;
    } else {
        $path = ".." . DIRECTORY_SEPARATOR . $path;
    }
}

define("ORM_PATH", $path);

require_once "{$path}Config.php";
require_once "{$path}Model.class.php";

$path = ".." . DIRECTORY_SEPARATOR;

foreach (array_reverse(explode(DIRECTORY_SEPARATOR, dirname($_SERVER["PHP_SELF"]))) as $dir) {
    if (is_dir($path . $dir . DIRECTORY_SEPARATOR . MODEL_DIR)) {
        $path = $path . $dir . DIRECTORY_SEPARATOR . MODEL_DIR . DIRECTORY_SEPARATOR;
        break;
    } else {
        $path = ".." . DIRECTORY_SEPARATOR . $path;
    }
}

define("MODEL_PATH", $path);

$dir = dir($path);
while (($file = $dir->read()) !== false) {
    if (is_file($path . '/' . $file) and preg_match('/^(.+)\.php$/i', $file) and ! strpos($file, "AutoComplete.php")) {
        require_once($path . '/' . $file);
    }
}
$dir->close();
// *****************************************************************************
