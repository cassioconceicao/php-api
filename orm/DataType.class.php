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
 * Description of DataType
 *
 * @author Cássio Conceição
 * @since 2021
 * @version 2021
 * @see http://ctecinf.com.br/
 */
class DataType {

    const STRING = 0;
    const BOOLEAN = 1;
    const TEXT = 2;
    const DATE = 3;
    const TIME = 4;
    const TIMESTAMP = 5;
    const DECIMAL = 6;
    const TINYINT = 7;
    const INTEGER = 8;
    const BIGINT = 9;

    /**
     * Configura padrão de tipos de dados
     * 
     * @param string $type
     * @return string
     */
    public static function parse($type) {

        switch (strtolower($type)) {

            case "bool":
            case "boolean":
                return DataType::BOOLEAN;

            case "int2":
            case "tiny":
            case "short":
                return DataType::TINYINT;

            case "int4":
            case "long":
                return DataType::INTEGER;

            case "int8":
            case "longlong":
                return DataType::BIGINT;

            case "float":
            case "float2":
            case "float4":
            case "float8":
            case "numeric":
            case "real":
            case "double":
            case "newdecimal":
                return DataType::DECIMAL;

            case "text":
            case "blob":
            case "lob":
                return DataType::TEXT;

            case "date":
                return DataType::DATE;

            case "time":
                return DataType::TIME;

            case "timestamp":
            case "datetime":
                return DataType::TIMESTAMP;

            default:
                return DataType::STRING;
        }
    }

}
