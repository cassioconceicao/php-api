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
 * Verifica se é tablet ou celular
 * @return boolean
 */
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

/**
 * Retorna o cabeçalho HTML
 * @param string $theme DEFAULT "base"
 * @return string HTML
 */
function getHTMLHead($title = "", $theme = "base", $charset = "UTF-8") {
    return "<meta charset=\"{$charset}\">
        <title>{$title}</title>
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <link type=\"text/css\" rel=\"stylesheet\" href=\"" . JQUERY_PATH . "ui/1.12.1/themes/{$theme}/jquery-ui.min.css\"/>
        <script type=\"text/javascript\" src=\"" . JQUERY_PATH . "jquery-1.12.4.min.js\"></script>
        <script type=\"text/javascript\" src=\"" . JQUERY_PATH . "ui/1.12.1/jquery-ui.min.js\"></script>
        ";
}