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
 * Chamada AJAX com resposta arquivo JSON
 * 
 * @param {string} url
 * @param {string} params [param1=value1&param2=value2...]
 * @param {function} callbackFunction Função para executar na resposta [function(response){ ...implementar método }]
 * @param {string} method POST|GET DEFAULT "POST" [OPCIONAL]
 * @returns {undefined}
 */
function getJSON(url, params, callbackFunction, method) {

    if (typeof method === 'undefined') {
        method = "POST";
    }

    var ajax = typeof XMLHttpRequest !== 'undefined' ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
    ajax.open(method, url);
    ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    ajax.responseType = 'json';

    ajax.onreadystatechange = function () {

        if (this.readyState === 4 && this.status === 200) {

            if (typeof ajax.response.error !== 'undefined') {
                alert(ajax.response.error);
            } else {
                callbackFunction(ajax.response);
            }
        }
    };

    ajax.send(params);
}

/**
 * Ordena tabela pela coluna
 * 
 * @param {string} tableId Identificador da tabela
 * @param {int} columnIndex Índice da coluna
 * @returns {undefined}
 */
function sortTable(tableId, columnIndex) {

    var spinner = document.getElementById("spinner");

    if (spinner !== null) {
        spinner.scrollIntoView();
        spinner.style.display = "";
    }

    setTimeout(function () {

        var tBody = document.getElementById(tableId).getElementsByTagName("TBODY")[0];
        var rows = tBody.getElementsByTagName("TR");
        var data = [];

        for (var i = 0; i < rows.length; i++) {
            var row = [];
            for (var j = 0; j < rows[i].getElementsByTagName("TD").length; j++) {
                row[j] = rows[i].getElementsByTagName("TD")[j];
            }
            data.push(row);
        }

        data = data.sort(function (a, b) {
            if (a[columnIndex].innerHTML > b[columnIndex].innerHTML) {
                return 1;
            } else if (a[columnIndex].innerHTML < b[columnIndex].innerHTML) {
                return -1;
            } else {
                return 0;
            }
        });

        tBody.innerHTML = "";
        data.forEach(function (item) {
            var tr = document.createElement("TR");
            item.forEach(function (cell) {
                tr.appendChild(cell);
            });
            tBody.appendChild(tr);
        });

        if (spinner !== null) {
            spinner.scrollIntoView();
            spinner.style.display = "none";
        }
    }, 50);
}

/**
 * Filtro para tabela
 * 
 * @param {type} tableId Identificador da tabela
 * @param {string} filterValue Valor para pesquisa na tabela
 * @returns {undefined}
 */
function filterTable(tableId, filterValue) {

    var rows = document.getElementById(tableId).getElementsByTagName("TBODY")[0].getElementsByTagName("TR");

    for (var i = 0; i < rows.length; i++) {

        rows[i].style.display = "none";
        var td = rows[i].getElementsByTagName("TD");

        for (var j = 0; j < td.length; j++) {

            var cell = rows[i].getElementsByTagName("TD")[j];

            if (cell && cell.innerHTML.toUpperCase().indexOf(filterValue.toUpperCase()) > -1) {
                rows[i].style.display = "";
                break;
            }
        }
    }
}

/**
 * Inicia paginação de uma tabela
 * 
 * @param {string} tableId Identificador da tabela
 * @param {int} limit Número máximo de registro por página
 * @param {string} url
 * @param {string} filterId Identificador o campo de filtro da tabela [OPCIONAL]
 * @param {string} formURL URL para direcionar ao clicar sobre a linha da tabela passando como parâmetro <i>ID</i> do registro.[OPCIONAL]
 * @returns {undefined}
 */
function initPagination(tableId, limit, url, filterId, formURL) {

    getJSON(url, 'action=count', function (response) {

        var pages = Math.ceil(response.total / limit);

        if (pages > 1) {

            var div = document.getElementsByClassName("pagination");

            for (var i = 0; i < div.length; i++) {

                var link = document.createElement("a");
                link.href = "javascript:loadPage('" + tableId + "', 1, " + limit + ", '" + url + "', '" + filterId + "'" + (typeof formURL !== 'undefined' && formURL.length > 0 ? ", '" + formURL + "'" : "") + ")";
                link.innerHTML = "&laquo";
                div[i].appendChild(link);

                for (var j = 1; j < pages + 1; j++) {

                    link = document.createElement("a");
                    link.href = "javascript:loadPage('" + tableId + "', " + j + ", " + limit + ", '" + url + "', '" + filterId + "'" + (typeof formURL !== 'undefined' && formURL.length > 0 ? ", '" + formURL + "'" : "") + ")";
                    link.innerHTML = j;
                    if (j === 1) {
                        link.className = "active";
                    }
                    div[i].appendChild(link);
                }

                link = document.createElement("a");
                link.href = "javascript:loadPage('" + tableId + "', " + pages + ", " + limit + ", '" + url + "', '" + filterId + "'" + (typeof formURL !== 'undefined' && formURL.length > 0 ? ", '" + formURL + "'" : "") + ")";
                link.innerHTML = "&raquo;";
                div[i].appendChild(link);
            }
        }

        loadPage(tableId, 1, limit, url, filterId, formURL);
    });
}

/**
 * Atualiza página da tabela via AJAX
 * 
 * @param {string} tableId Identificador da tabela
 * @param {int} page Página para selecionar
 * @param {int} limit Número máximo de registros por página
 * @param {string} url URL para consulta via ajax. Parâmetros do <i>send</i> [action=find&offset=?&limit=?]
 * @param {string} filterId Identificador do campo de filtro da tabela [OPCIONAL]
 * @param {string} formURL URL para direcionar ao clicar sobre a linha da tabela passando como parâmetro <i>ID</i> do registro.[OPCIONAL]
 * @returns {undefined}
 */
function loadPage(tableId, page, limit, url, filterId, formURL) {

    var spinner = document.getElementById("spinner");

    if (spinner !== null) {
        spinner.style.display = "";
        spinner.scrollIntoView();
    }

    var offset = page === 1 ? 0 : (page - 1) * limit;

    getJSON(url, 'action=find&offset=' + offset + '&limit=' + limit, function (response) {

        var rows = "";
        response.forEach(function (item) {
            rows += "<tr><td" + (typeof formURL !== 'undefined' ? " onclick=\"window.location='" + formURL + "?id=" + item.value + "'\"" : " onclick=\"window.location='form.php?id=" + item.value + "'\"") + ">" + item.value + "</td><td" + (typeof formURL !== 'undefined' ? " onclick=\"window.location='" + formURL + "?id=" + item.value + "'\"" : " onclick=\"window.location='form.php?id=" + item.value + "'\"") + ">" + item.label + "</td></tr>";
        });

        document.getElementById(tableId).getElementsByTagName("TBODY")[0].innerHTML = rows;

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
    });
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

//function autocomplete(searchId, targetId, url) {
//
//    var search = document.getElementById(searchId);
//    var target = document.getElementById(targetId);
//
//    getJSON(url, 'action=find&term=' + search.value + '&limit=' + 8, function (response) {
//
//        if (typeof response.error !== 'undefined') {
//            target.innerHTML = "";
//            alert(response.error);
//        } else if (response.length > 0) {
//            var list = "";
//            for (const op of ajax.response) {
//                list += "<a href=\"javascript:setSeleceted$className('" + op.value + "', '" + op.label + "')\">" + op.label + "</a>";
//            }
//            target.innerHTML = list;
//            target.style.border = "1px solid $borderColor";
//
//        } else {
//            target.innerHTML = "";
//            target.style.border = "none";
//        }
//    });
//
//}

function initForm(formId) {

    var spinner = document.getElementById("spinner");

    if (spinner !== null) {
        spinner.style.display = "";
        spinner.scrollIntoView();
    }

    var form = document.getElementById(formId);

    if (document.getElementById("id").value > 0) {

        getJSON(form.action, 'action=find&id=' + document.getElementById("id").value, function (response) {

            for (var i = 0; i < form.elements.length; i++) {
                if (form.elements[i].name.indexOf('data') !== -1) {
                    form.elements[i].value = response.data[form.elements[i].id];
                }
            }

            if (spinner !== null) {
                spinner.style.display = "none";
            }
        });

    } else {

        if (spinner !== null) {
            spinner.style.display = "none";
        }
    }
}

function saveCliente() {

}

function deleteCliente(id) {
    if (confirm("Deseja realmente excluír registro ID[" + id + "]?")) {

    }
}

window.onscroll = function () {

    var button = document.getElementById("top-button");

    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        button.style.display = "block";
    } else {
        button.style.display = "none";
    }
};

function topScroll() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
}
