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

require_once '../orm/index.php';

$name = "municipio_id";
$reflection = new ReflectionClass(Municipio::class);

$class = $reflection->getName();
$url = str_replace($_SERVER["DOCUMENT_ROOT"], "http://".$_SERVER["SERVER_NAME"], str_replace($class, $class . "AutoComplete", str_replace("/" . MODEL_DIR . "/", "/" . CONTROLLER_DIR . "/", $reflection->getFileName())));
$fileName = str_replace($class, $class . "AutoComplete", str_replace("/" . MODEL_DIR . "/", "/" . CONTROLLER_DIR . "/", $reflection->getFileName()));

if (!file_exists($fileName)) {
    $handle = fopen($fileName, "w");
    fwrite($handle, str_replace("\$className", "{$class}::class", file_get_contents(ORM_PATH . "AutoCompleteModel.txt")));
    fclose($handle);
}

$content = file_get_contents(ORM_PATH . "AutoCompleteField.txt");
$html = str_replace("\$url", $url, str_replace("\$name", $name, str_replace("\$class", $class, $content)));
echo $html;
