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
 * Inicia paginação de uma tabela
 * 
 * @param {string} tableId Identificador da tabela
 * @param {int} limit Número máximo de registro por página
 * @param {string} url
 * @param {string} filterId Identificador do campo de filtro da tabela [OPCIONAL]
 * @returns {undefined}
 */
function initPagination(tableId, limit, url, filterId) {

    var ajax = typeof XMLHttpRequest !== 'undefined' ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
    ajax.open('POST', url);
    ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    ajax.responseType = 'json';

    ajax.onreadystatechange = function () {

        if (this.readyState === 4 && this.status === 200) {

            if (typeof ajax.response.error !== 'undefined') {
                alert(ajax.response.error);
            } else {

                var pages = Math.ceil(ajax.response.total / limit);

                if (pages > 1) {

                    var div = document.getElementsByClassName("pagination");

                    for (var i = 0; i < div.length; i++) {

                        var link = document.createElement("a");
                        link.href = "javascript:setPage('" + tableId + "', 1, " + limit + ", '" + url + "', '" + filterId + "')";
                        link.innerHTML = "&laquo";
                        div[i].appendChild(link);

                        for (var j = 1; j < pages + 1; j++) {

                            link = document.createElement("a");
                            link.href = "javascript:setPage('" + tableId + "', " + j + ", " + limit + ", '" + url + "', '" + filterId + "')";
                            link.innerHTML = j;
                            if (j === 1) {
                                link.className = "active";
                            }
                            div[i].appendChild(link);
                        }

                        link = document.createElement("a");
                        link.href = "javascript:setPage('" + tableId + "', " + pages + ", " + limit + ", '" + url + "', '" + filterId + "')";
                        link.innerHTML = "&raquo;";
                        div[i].appendChild(link);
                    }
                }

                setPage(tableId, 1, limit, url, filterId);
            }
        }
    };

    ajax.send('action=count');
}

/**
 * Filtro para tabela
 * 
 * @param {type} tableId Identificador da tabela
 * @param {string} filterValue Valor para pesquisa na tabela
 * @returns {undefined}
 */
function filterTable(tableId, filterValue) {

    var rows = document.getElementById(tableId).getElementsByTagName("tbody")[0].getElementsByTagName("tr");

    for (var i = 0; i < rows.length; i++) {

        rows[i].style.display = "none";
        var td = rows[i].getElementsByTagName("td");

        for (var j = 0; j < td.length; j++) {

            var cell = rows[i].getElementsByTagName("td")[j];

            if (cell && cell.innerHTML.toUpperCase().indexOf(filterValue.toUpperCase()) > -1) {
                rows[i].style.display = "";
                break;
            }
        }
    }
}

/**
 * Atualiza página da tabela via AJAX
 * 
 * @param {string} tableId Identificador da tabela
 * @param {int} page Página para selecionar
 * @param {int} limit Número máximo de registros por página
 * @param {string} url URL para consulta via ajax. Parâmetros do <i>send</i> [action=find&offset=?&limit=?]
 * @param {string} filterId Identificador do campo de filtro da tabela [OPCIONAL]
 * @returns {undefined}
 */
function setPage(tableId, page, limit, url, filterId) {

    var spinner = document.getElementById("spinner");

    if (spinner !== null) {
        spinner.style.display = "";
        spinner.scrollIntoView();
    }

    var offset = page === 1 ? 0 : (page - 1) * limit;

    var ajax = typeof XMLHttpRequest !== 'undefined' ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
    ajax.open('POST', url);
    ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    ajax.responseType = 'json';

    ajax.onreadystatechange = function () {

        if (this.readyState === 4 && this.status === 200) {

            if (typeof ajax.response.error !== 'undefined') {
                alert(ajax.response.error);
            } else if (ajax.response.length > 0) {

                var rows = "";
                ajax.response.forEach(function (item) {
                    rows += "<tr onclick=\"window.location='form.php?id=" + item.value + "'\"><td>" + item.value + "</td><td>" + item.label + "</td></tr>";
                });

                document.getElementById(tableId).getElementsByTagName("tbody")[0].innerHTML = rows;

                var div = document.getElementsByClassName("pagination");

                for (var i = 0; i < div.length; i++) {
                    var href = div[i].getElementsByTagName("a");
                    for (var j = 0; j < href.length; j++) {
                        href[j].className = "";
                        if (j === page) {
                            href[j].className = "active";
                        }
                    }
                }

                var filter = document.getElementById(filterId);

                if (filter !== null) {
                    filterTable(tableId, filter.value);
                    filter.focus();
                }

                if (spinner !== null) {
                    spinner.style.display = "none";
                }
            }
        }
    };

    ajax.send('action=find&offset=' + offset + '&limit=' + limit);
}

/**
 * Abre Menu lateral
 * @returns {undefined}
 */
function openMenu() {
    document.getElementById("menu").style.width = "80%";
}

/**
 * Fecha Menu lateral
 * @returns {undefined}
 */
function closeMenu() {
    document.getElementById("menu").style.width = "0%";
}

function initForm(formId) {

    var form = document.getElementById(formId);

    if (document.getElementById("id").value > 0) {

        var ajax = typeof XMLHttpRequest !== 'undefined' ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
        ajax.open('POST', form.action);
        ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        ajax.responseType = 'json';

        ajax.onreadystatechange = function () {

            if (this.readyState === 4 && this.status === 200) {

                if (typeof ajax.response.error !== 'undefined') {
                    alert(ajax.response.error);
                } else {
                    for (var i = 0; i < form.elements.length; i++) {
                        if (form.elements[i].name.indexOf('data') !== -1) {
                            form.elements[i].value = ajax.response.data[document.formCliente.elements[i].id];
                        }
                    }

                    document.getElementById("spinner").style.display = "none";
                }
            }
        };

        ajax.send('action=find&id=' + document.getElementById("id").value);

    } else {
        document.getElementById("spinner").style.display = "none";
    }
}

function saveCliente() {

}

function deleteCliente(id) {
    if (confirm("Deseja realmente excluír registro ID[" + id + "]?")) {

    }
}