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
 * Description of Update
 *
 * @author Cássio Conceição
 * @since 2021
 * @version 2021
 * @see http://ctecinf.com.br/
 */
class Clause {

    private $table;
    private $clause;

    /**
     * Construtor
     * @param string $table
     */
    function __construct($table) {
        $this->table = $table;
        $this->clause = "";
    }

    /**
     * Cria claúsula
     * @param string $table
     * @return Clause
     */
    public function create($table) {
        return new Clause($table);
    }

    /**
     * Abre parenteses
     * @return Clause
     */
    public function openParentheses() {
        $this->clause .= "(";
        return $this;
    }

    /**
     * Fecha parenteses
     * @return Clause
     */
    public function closeParentheses() {
        $this->clause .= ")";
        return $this;
    }

    /**
     * Igual
     * @param string $column
     * @param mixed $value
     * @return Clause
     */
    public function equal($column, $value = false) {
        $this->clause = $this->table . "." . $column . " = " . (!$value ? " NULL " : "'" . $value . "'");
        return $this;
    }

    /**
     * Não igual
     * @param string $column
     * @param mixed $value
     * @return Clause
     */
    public function notEqual($column, $value = false) {
        $this->clause = $this->table . "." . $column . " <> " . (!$value ? " NULL " : "'" . $value . "'");
        return $this;
    }

    /**
     * Menor
     * @param string $column
     * @param mixed $value
     * @return Clause
     */
    public function less($column, $value = false) {
        $this->clause = $this->table . "." . $column . " < " . (!$value ? " NULL " : "'" . $value . "'");
        return $this;
    }

    /**
     * Maior
     * @param string $column
     * @param mixed $value
     * @return Clause
     */
    public function greater($column, $value = false) {
        $this->clause = $this->table . "." . $column . " > " . (!$value ? " NULL " : "'" . $value . "'");
        return $this;
    }

    /**
     * Menor igual
     * @param string $column
     * @param mixed $value
     * @return Clause
     */
    public function lessEqual($column, $value = false) {
        $this->clause = $this->table . "." . $column . " <= " . (!$value ? " NULL " : "'" . $value . "'");
        return $this;
    }

    /**
     * Maior igual
     * @param string $column
     * @param mixed $value
     * @return Clause
     */
    public function greaterEqual($column, $value = false) {
        $this->clause = $this->table . "." . $column . " >= " . (!$value ? " NULL " : "'" . $value . "'");
        return $this;
    }

    /**
     * Nulo
     * @param string $column
     * @return Clause
     */
    public function isNull($column) {
        $this->clause = $this->table . "." . $column . " IS NULL";
        return $this;
    }

    /**
     * Não nulo
     * @param string $column
     * @return Clause
     */
    public function isNotNull($column) {
        $this->clause = $this->table . "." . $column . " IS NOT NULL";
        return $this;
    }

    /**
     * Entre
     * @param string $column
     * @param mixed $value1
     * @param mixed $value2
     * @return Clause
     */
    public function between($column, $value1 = false, $value2 = false) {
        $this->clause = $this->table . "." . $column . " BETWEEN " . (!$value1 ? " NULL " : "'" . $value1 . "'") . " AND " . (!$value2 ? " NULL " : "'" . $value2 . "'");
        return $this;
    }

    /**
     * Lista
     * @param string $column
     * @param array $values
     * @return Clause
     */
    public function in($column, $values = array()) {
        $this->clause = $this->table . "." . $column . " IN (" . implode(", ", $values) . ")";
        return $this;
    }

    /**
     * Não lista
     * @param string $column
     * @param array $values
     * @return Clause
     */
    public function notIn($column, $values = array()) {
        $this->clause = $this->table . "." . $column . " NOT IN (" . implode(", ", $values) . ")";
        return $this;
    }

    /**
     * Filtro
     * @param mixed $filter
     * @return Clause
     */
    public function like($filter) {

        if (strlen($filter) == 0) {
            return $this;
        }

        $columns = array();

        foreach (Metadata::getColumnsName($this->table) as $column) {
            if (DB_DSN == "postgres") {
                $columns[] = "LOWER (CAST(" . $this->table . "." . $column . ") AS VARCHAR) LIKE '" . strtolower($filter) . "%'";
            } else {
                $columns[] = "LOWER (" . $this->table . "." . $column . ") LIKE '" . strtolower($filter) . "%'";
            }
        }

        foreach (Metadata::getReferencedTables($this->table) as $referencedTable) {
            foreach (Metadata::getColumnsName($referencedTable) as $col) {
                if (DB_DSN == "postgres") {
                    $columns[] = "LOWER (CAST(" . $referencedTable . "." . $col . ") AS VARCHAR) LIKE '" . strtolower($filter) . "%'";
                } else {
                    $columns[] = "LOWER (" . $referencedTable . "." . $col . ") LIKE '" . strtolower($filter) . "%'";
                }
            }
        }

        $this->openParentheses();
        $this->clause .= implode(" OR ", $columns);
        $this->closeParentheses();

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param string $table
     * @return Clause
     */
    public function orEqual($column, $value = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->equal($column, $value);
        $this->clause .= " OR " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param string $table
     * @return Clause
     */
    public function orNotEqual($column, $value = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->notEqual($column, $value);
        $this->clause .= " OR " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param string $table
     * @return Clause
     */
    public function orLess($column, $value = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->less($column, $value);
        $this->clause .= " OR " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param string $table
     * @return Clause
     */
    public function orGreater($column, $value = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->greater($column, $value);
        $this->clause .= " OR " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param string $table
     * @return Clause
     */
    public function orLessEqual($column, $value = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->lessEqual($column, $value);
        $this->clause .= " OR " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param string $table
     * @return Clause
     */
    public function orGreaterEqual($column, $value = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->greaterEqual($column, $value);
        $this->clause .= " OR " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param string $table
     * @return Clause
     */
    public function orIsNull($column, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->isNull($column);
        $this->clause .= " OR " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param string $table
     * @return Clause
     */
    public function orIsNotNull($column, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->isNotNull($column);
        $this->clause .= " OR " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value1
     * @param mixed $value2
     * @param string $table
     * @return Clause
     */
    public function orBetween($column, $value1 = false, $value2 = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->between($column, $value1, $value2);
        $this->clause .= " OR " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param array $values
     * @param string $table
     * @return Clause
     */
    public function orIn($column, $values = array(), $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->in($column, $values);
        $this->clause .= " OR " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param array $values
     * @param string $table
     * @return Clause
     */
    public function orNotIn($column, $values = array(), $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->notIn($column, $values);
        $this->clause .= " OR " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param mixed $filter
     * @param string $table
     * @return Clause
     */
    public function orLike($filter = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->like($filter);
        $this->clause .= " OR " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param string $table
     * @return Clause
     */
    public function andEqual($column, $value = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->equal($column, $value);
        $this->clause .= " AND " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param string $table
     * @return Clause
     */
    public function andNotEqual($column, $value = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->notEqual($column, $value);
        $this->clause .= " AND " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param string $table
     * @return Clause
     */
    public function andLess($column, $value = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->less($column, $value);
        $this->clause .= " AND " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param string $table
     * @return Clause
     */
    public function andGreater($column, $value = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->greater($column, $value);
        $this->clause .= " AND " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param string $table
     * @return Clause
     */
    public function andLessEqual($column, $value = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->lessEqual($column, $value);
        $this->clause .= " AND " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value
     * @param string $table
     * @return Clause
     */
    public function andGreaterEqual($column, $value = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->greaterEqual($column, $value);
        $this->clause .= " AND " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param string $table
     * @return Clause
     */
    public function andIsNull($column, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->isNull($column);
        $this->clause .= " AND " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param string $table
     * @return Clause
     */
    public function andIsNotNull($column, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->isNotNull($column);
        $this->clause .= " AND " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param mixed $value1
     * @param mixed $value2
     * @param string $table
     * @return Clause
     */
    public function andBetween($column, $value1 = false, $value2 = false, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->between($column, $value1, $value2);
        $this->clause .= " AND " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param array $values
     * @param string $table
     * @return Clause
     */
    public function andIn($column, $values = array(), $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->in($column, $values);
        $this->clause .= " AND " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param string $column
     * @param array $values
     * @param string $table
     * @return Clause
     */
    public function andNotIn($column, $values = array(), $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->notIn($column, $values);
        $this->clause .= " AND " . $c->clause;

        return $this;
    }

    /**
     * 
     * @param mixed $filter
     * @param string $table
     * @return Clause
     */
    public function andLike($filter, $table = false) {

        $c = new Clause(!$table ? $this->table : $table);
        $c->like($filter);
        $this->clause .= " AND " . $c->clause;

        return $this;
    }

    /**
     * 
     * @return string
     */
    public function __toString() {
        return !$this->clause ? "" : $this->clause;
    }

}
