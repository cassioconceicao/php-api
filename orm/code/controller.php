<?php

/*
 * Copyright (C) $year ctecinf.com.br
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

            if (!getId()) {

                $list = array();

                foreach ($className::find(getTerm(), getOffSet(), getMaxResults()) as $row) {
                    $list[] = array(
                        "value" => $row->getId(),
                        "label" => strval($row),
                        "data" => $row->getData()
                    );
                }

                $return = json_encode($list);
            } else {

                $obj = $className::findById(getId());
                if (!$obj) {
                    $return = json_encode(array("message" => "Registro não encontrado."));
                } else {
                    $return = json_encode(array("value" => $obj->getId(), "label" => strval($obj), "data" => $obj->getData()));
                }
            }
        } catch (Exception $ex) {
            $return = json_encode(array("message" => $ex->getMessage()));
        }

        break;

    case ACTION_SAVE:

        try {
            $obj = new $className();
            $obj->setData(getData());
            $return = $obj->save() ? json_encode(array("message" => "Registro salvo com sucesso.")) : json_encode(array("message" => "Algo deu errado, tente novamente."));
        } catch (Exception $ex) {
            $return = json_encode(array("message" => $ex->getMessage()));
        }

        break;

    case ACTION_DELETE:

        try {

            if (!getId()) {

                foreach (getSelectedsId() as $id) {

                    $obj = $className::findById($id);

                    if (!$obj || !$obj->delete()) {
                        throw new Exception("Registro(s) não encontrado(s).");
                    }
                }

                $return = json_encode(array("message" => "Registro(s) apagado(s) com sucesso."));
            } else {

                $obj = $className::findById(getId());

                if (!$obj || !$obj->delete()) {
                    throw new Exception("Registro não encontrado.");
                }
                
                $return = json_encode(array("message" => "Registro apagado com sucesso."));
            }
        } catch (Exception $ex) {
            $return = json_encode(array("message" => $ex->getMessage()));
        }

        break;

    case ACTION_COUNT:

        try {
            $return = json_encode(array("total" => $className::count()));
        } catch (Exception $ex) {
            $return = json_encode(array("message" => $ex->getMessage()));
        }

        break;

    default:
        $return = json_encode(array("message" => "Falta parâmetro 'action'."));
        break;
}

echo $return;