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

require_once '../index.php';

$return = false;

switch (getAction()) {

    case ACTION_FIND:

        try {

            $list = array();

            foreach (Municipio::find(getTerm(), getMaxResults()) as $row) {
                $list[] = array(
                    "value" => $row->getId(),
                    "label" => strval($row)
                );
            }

            $return = json_encode($list);
        } catch (Exception $ex) {
            $return = json_encode(array("error" => $ex->getMessage()));
        }

        break;

    case ACTION_SAVE:

        try {
            $return = Municipio::save(getData()) ? json_encode(array("success" => "Registro salvo com sucesso.")) : json_encode(array("error" => "Algo deu errado, tente novamente."));
        } catch (Exception $ex) {
            $return = json_encode(array("error" => $ex->getMessage()));
        }

        break;

    case ACTION_DELETE:

        try {

            $obj = Municipio::findById(getId());

            if (!$obj) {
                $return = json_encode(array("error" => "Registro não encontrado."));
            } else {
                $return = $obj->delete() ? json_encode(array("success" => "Registro apagado com sucesso.")) : json_encode(array("error" => "Algo deu errado, tente novamente."));
            }
        } catch (Exception $ex) {
            $return = json_encode(array("error" => $ex->getMessage()));
        }

        break;

    default:
        $return = json_encode(array("error" => "Falta parâmetro 'action'."));
        break;
}

echo $return;

