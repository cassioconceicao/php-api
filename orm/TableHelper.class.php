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
 * Description of TableHelper
 *
 * @author Cássio Conceição
 * @since 2021
 * @version 2021
 * @see http://ctecinf.com.br/
 */
class TableHelper {

    private $className;
    private $url;
    private $html;

    /**
     * Cria assintente de tabela HTML
     * 
     * @param string $className
     * @return TableHelper
     */
    public static function create($className) {

        $reflection = new ReflectionClass($className);
        $url = CONTROLLER_PATH . $reflection->getName() . ".php";

        $html = file_get_contents(ORM_PATH . "doc/table.txt");
        $html = str_replace("\$className", $reflection->getName(), $html);
        $html = str_replace("\$url", $url, $html);
        $html = str_replace("\$paginationMaxResults", PAGINATION_MAX_RESULTS, $html);
        $html = str_replace("\$backgroundColor", BACKGROUND_COLOR, $html);
        $html = str_replace("\$rowColor", ROW_COLOR, $html);
        $html = str_replace("\$fontSize", FONT_SIZE, $html);
        $html = str_replace("\$textColor", TEXT_COLOR, $html);
        $html = str_replace("\$headColor", HEAD_COLOR, $html);
        $html = str_replace("\$highlightColor", HIGHLIGHT_COLOR, $html);
        $html = str_replace("\$borderColor", BORDER_COLOR, $html);
        $html = str_replace("\$searchIcon", SEARCH_ICON, $html);
        $html = str_replace("\$headTextColor", HEAD_TEXT_COLOR, $html);

        $table = new TableHelper();
        $table->html = $html;
        $table->url = $url;
        $table->className = $reflection->getName();

        return $table;
    }

    function getModelClassName() {
        return $this->className;
    }

    function getControllerPath() {
        return $this->url;
    }
    
    public function __toString() {
        return $this->html;
    }

}
