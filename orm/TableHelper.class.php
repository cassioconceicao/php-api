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
    private $controllerPath;
    private $html;

    /**
     * 
     * @param string $className Nome da classe modelo
     */
    function __construct($className) {

        $reflection = new ReflectionClass($className);

        $this->className = $reflection->getName();
        $this->controllerPath = CONTROLLER_PATH . $this->className . ".php";
        $this->html = getCodeFile("table-helper.html", $this->className, $this->controllerPath);
    }

    /**
     * Cria assintente de tabela HTML
     * 
     * @param string $className
     * @return TableHelper
     */
    public static function create($className) {
        return new TableHelper($className);
    }

    public function getModelClassName() {
        return $this->className;
    }

    public function getControllerPath() {
        return $this->controllerPath;
    }

    public function __toString() {
        return $this->html;
    }

}
