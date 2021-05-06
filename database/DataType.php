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

    const STRING = "VARCHAR";
    const BOOLEAN = "BOOLEAN";
    const TEXT = "TEXT";
    const DATE = "DATE";
    const TIME = "TIME";
    const TIMESTAMP = "TIMESTAMP";
    const DECIMAL = "DECIMAL";
    const TINYINT = "TINYINT";
    const INTEGER = "INT";
    const BIGINT = "BIGINT";

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
            case "smallint":
                return DataType::TINYINT;

            case "int":
            case "integer":
            case "int4":
            case "long":
                return DataType::INTEGER;

            case "int8":
            case "longlong":
            case "bigint":
                return DataType::BIGINT;

            case "float":
            case "d_float":
            case "quad":
            case "int64":
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
