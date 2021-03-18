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

require_once '../orm/index.php';

$name = "municipio_id";

$reflection = new ReflectionClass(Municipio::class);
$class = $reflection->getName();
$url = str_replace($_SERVER["DOCUMENT_ROOT"], "http://localhost", str_replace($class, $class . "AutoComplete", $reflection->getFileName()));
?>

<script type="text/javascript">

    function search<?php echo $class ?>(term) {

        var sel = document.getElementById('<?php echo $name ?>_list');
        sel.innerHTML = "";
        sel.setAttribute('style', 'display:none');

        var ajax = typeof XMLHttpRequest !== 'undefined' ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
        ajax.open('POST', '<?php echo $url ?>');
        ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        ajax.responseType = 'json';

        ajax.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                var options = "";
                for (const op of ajax.response) {
                    options += "<option value=" + op.value + ">" + op.label + "</option>";
                }
                sel.innerHTML = options;
                sel.setAttribute('style', 'display:block');
            } else {
                sel.setAttribute('style', 'display:none');
            }
        };

        ajax.send('term<?php echo $class ?>=' + term);
    }

    function setSeleceted<?php echo $class ?>(sel) {
        sel.setAttribute('style', 'display:none');
        document.getElementById('<?php echo $name ?>').value = sel.value;
        document.getElementById('<?php echo $name ?>_label').value = sel.options[sel.selectedIndex].text;
    }
</script>
<input id="<?php echo $name ?>_label" onkeyup="search<?php echo $class ?>(this.value)" />
<input type="hidden" id="<?php echo $name ?>" name="<?php echo $name ?>" />
<select id="<?php echo $name ?>_list" multiple style="display: none" ondblclick="setSeleceted<?php echo $class ?>(this)"></select>

