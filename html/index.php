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
 * Verifica se é tablet / Telefone ou Desktop
 * 
 * @return boolean
 */
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

/**
 * Retorna uma <i>String</i> com um cabeçalho HTML de título e <i>charset</i>
 * 
 * @param string $theme DEFAULT "base"
 * @return string HTML
 */
function getHTMLHead($title = "", $charset = "UTF-8") {

    $html = "<meta charset=\"{$charset}\">";
    $html .= "<title>{$title}</title>";
    $html .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">";

    return $html;
}

/**
 * Retorna uma <i>String</i> com um elemento <i>input</i> HTML com <i>label</i> associado
 * 
 * @param string $name Nome e ID do <i>input</i>
 * @param string $label Rótulo do <i>input</i>
 * @param mixed $value Valor
 * @return string HTML
 * @throws InvalidArgumentException
 */
function makeInput($name, $label, $value = null, $type = false) {

    if (!$type && isset($value)) {
        $type = gettype($value);
    } else if (!$type) {
        $type = 'string';
    }

    $html = "<label for=\"{$name}\">{$label}</label>\n";

    switch ($type) {

        case 'boolean':
            $input_type = 'checkbox';
            break;
        case 'integer':
            $input_type = 'number';
            break;
        case 'double':
            $input_type = 'number';
            $step = " step=\"any\"";
            break;
        case 'string':
            $input_type = 'text';
            break;
        case 'object':
            $input_type = 'text';
            break;
        default:
            throw new InvalidArgumentException($value);
    }

    $html .= "<input name=\"{$name}\" id=\"{$name}\" type=\"{$input_type}\"{$step} value=\"{$value}\" />\n\n";

    return $html;
}
