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
echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=0.70, maximum-scale=1.0, minimum-scale=0.70\">";
$rs = Municipio::sortResultSet(Municipio::find());

$total = count($rs);
$limit = 1000;
$pages = ceil($total / $limit);
?>

<style type="text/css">

    #searchMunicipio {background-image: url(data:image/png;base64,<?php echo SEARCH_ICON ?>);background-position: 5px 5px;background-repeat: no-repeat;width: 100%;font-size: 16px;padding: 12px 20px 12px 40px;border: 1px solid #DDDDDD;margin-bottom: 12px}

    #tableMunicipio {border-collapse: collapse;width: 100%;border: 1px solid #DDDDDD;font-size: 18px;margin-bottom: 12px}
    #tableMunicipio thead th, #tableMunicipio tbody td {text-align: left;padding: 12px}
    #tableMunicipio tbody tr:nth-child(even) {background: #FFFFFF}
    #tableMunicipio tbody tr:nth-child(odd) {background: <?php echo ROW_COLOR ?>}
    #tableMunicipio tr {border-bottom: 1px solid #DDDDDD}
    #tableMunicipio thead tr, #tableMunicipio tbody tr:hover {cursor: pointer;background-color: <?php echo ROW_FOCUS_COLOR ?>}

    .paginationMunicipio {display: inline-block;margin-bottom: 12px}
    .paginationMunicipio a {color: black;float: left;padding: 8px 16px;text-decoration: none;margin: 0 4px;border: 1px solid #DDDDDD}
    .paginationMunicipio a {border-radius: 5px}
    .paginationMunicipio a.active {border-radius: 5px;background-color: <?php echo ROW_FOCUS_COLOR ?>}
    .paginationMunicipio a:hover:not(.active) {background-color: <?php echo ROW_FOCUS_COLOR ?>}
    .paginationMunicipio a:first-child {border-top-left-radius: 5px;border-bottom-left-radius: 5px}
    .paginationMunicipio a:last-child {border-top-right-radius: 5px;border-bottom-right-radius: 5px}

</style>

<script type="text/javascript">

    var offset, data;

    function initPaginationMunicipio() {

        offset = 0;
        var pages = <?php echo $pages ?>;
        var div = document.getElementsByClassName("paginationMunicipio");

        for (var i = 0; i < div.length; i++) {

            link = document.createElement("a");
            link.href = "javascript:setPageMunicipio(1)";
            link.innerHTML = "&laquo";
            div[i].appendChild(link);

            for (var j = 1; j < pages + 1; j++) {

                link = document.createElement("a");
                link.href = "javascript:setPageMunicipio(" + j + ")";
                link.innerHTML = j;
                if (j === 1) {
                    link.className = "active";
                }
                div[i].appendChild(link);
            }

            link = document.createElement("a");
            link.href = "javascript:setPageMunicipio(" + pages + ")";
            link.innerHTML = "&raquo;";
            div[i].appendChild(link);
        }

        setPageMunicipio(1);
    }

    function filterTableMunicipio(filter) {
        var rows = document.getElementById("tableMunicipio").getElementsByTagName("tbody")[offset].getElementsByTagName("tr");
        for (var i = 0; i < rows.length; i++) {
            rows[i].style.display = "none";
            var td = rows[i].getElementsByTagName("td");
            for (var j = 0; j < td.length; j++) {
                var cell = rows[i].getElementsByTagName("td")[j];
                if (cell) {
                    if (cell.innerHTML.toUpperCase().indexOf(filter.toUpperCase()) > -1) {
                        rows[i].style.display = "";
                        break;
                    }
                }
            }
        }
    }

    function setPageMunicipio(page) {

        offset = page - 1;

        var body = document.getElementById("tableMunicipio").getElementsByTagName("tbody");
        for (var i = 0; i < body.length; i++) {
            body[i].style.display = "none";
            if (i === offset) {
                body[i].style.display = "";
            }
        }

        var div = document.getElementsByClassName("paginationMunicipio");
        for (var i = 0; i < div.length; i++) {
            var href = div[i].getElementsByTagName("a");
            for (var j = 0; j < href.length; j++) {
                href[j].className = "";
                if (j === page) {
                    href[j].className = "active";
                }
            }
        }

        document.getElementById("searchMunicipio").focus();
    }
</script>

<input type="text" id="searchMunicipio" onkeyup="filterTableMunicipio(this.value)" placeholder="Pesquisar" />
<div class="paginationMunicipio"></div>
<table id="tableMunicipio">
    <thead>
        <tr>
            <th style="width:30px">Código</th><th>Descrição</th>
        </tr>
    </thead>
    <?php
    $index = 0;
    echo "<tbody>\n";
    foreach ($rs as $row) {
        if ($index == $limit) {
            echo "</tbody>\n";
            echo "<tbody>\n";
            $index = 0;
        }
        echo "<tr>\n";
        echo "<td>" . $row->getId() . "</td><td>" . strval($row) . "</td>\n";
        echo "</tr>\n";
        $index++;
    }
    echo "</tbody>\n";
    ?>
</table>
<div class="paginationMunicipio"></div>
<script type="text/javascript">
    initPaginationMunicipio();
</script>


