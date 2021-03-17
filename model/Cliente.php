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

require '../orm/index.php';

/**
 * Description of Cliente
 *
 * @author cassio
 */
class Cliente extends Model {

    //put your code here
    public function __toString() {
        return $this->get("nome");
    }

}

//var_dump($_SESSION);
//unset($_SESSION);
//try {
//
//    $rs = Cliente::sortResultSet(Cliente::find());
//
//    $column = "nome";
//    $selected = 0;
//    
//    //echo "<select id='{$column}' name='{$column}' onchange=\"javascript:document.getElementById('{$column}').value = this.value; if(this.value == '') { document.getElementById('{$column}_label').value = ''; } else { document.getElementById('{$column}_label').value = this.options[this.selectedIndex].text;}\">";
//    
//    echo "<select id='{$column}' name='{$column}'>";
//    echo "<option value=''> - Selecione - </option>";
//    foreach ($rs as $row) {
//        if ($row->getId() == $selected) {
//            echo "<option selected value='{$row->getId()}'>{$row}</option>";
//        } else {
//            echo "<option value='{$row->getId()}'>{$row}</option>";
//        }
//    }
//    echo "</select>";
//} catch (Exception $exc) {
//    echo $exc->getMessage();
//}

$table = "cliente";
$columnLabel = "nome";
$path = "../orm/autocomplete-search.php";
?>
<head>

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <style>
        .ui-autocomplete-loading {
            background: white url(../jquery/ui/ui-anim_basic_16x16.gif) right center no-repeat;
        }
    </style>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <script>
        $(function () {
            function log(message) {
                $("<div>").text(message).prependTo("#log");
                $("#log").scrollTop(0);
            }

            $("#birds").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "<?php echo $path ?>",
                        dataType: "jsonp",
                        data: {
                            term: request.term
                        },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    log("Selected: " + ui.item.value + " aka " + ui.item.id);
                }
            });
        });
    </script>
</head>
<body>

    <div class="ui-widget">
        <label for="birds">Birds: </label>
        <input id="birds">
    </div>

    <div class="ui-widget" style="margin-top:2em; font-family:Arial">
        Result:
        <div id="log" style="height: 200px; width: 300px; overflow: auto;" class="ui-widget-content"></div>
    </div>
</body>