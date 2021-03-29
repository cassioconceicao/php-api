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
 * Description of FormHelper
 *
 * @author Cássio Conceição
 * @since 2021
 * @version 2021
 * @see http://ctecinf.com.br/
 */
class FormHelper {

    private $className;
    private $url;
    private $html;

    /**
     * Cria assintente de tabela HTML
     * 
     * @param string $className
     * @return FormHelper
     */
    public static function create($className) {

        $reflection = new ReflectionClass($className);
        $url = CONTROLLER_PATH . $reflection->getName() . ".php";

        $html = "";

        $form = new FormHelper();
        $form->html = $html;
        $form->url = $url;
        $form->className = $reflection->getName();

        return $form;
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
