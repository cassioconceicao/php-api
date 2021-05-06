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
 * Formatação numérica
 * 
 * @returns {String}
 */
Number.prototype.format = function () {

    var str = this.toString();

    if (str === 'NaN') {
        return "";
    }

    if (str.length === 1) {
        str += ".00";
    } else if (str.length > 1) {
        var len = str.length - 2;
        str = str.substring(0, len) + "." + str.substring(len);
    }

    var num = parseFloat(str);

    return num.toFixed(2).replace('.', ',').replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
};

/**
 * Chamada AJAX com resposta arquivo JSON
 * 
 * @param {String} url
 * @param {String} params [param1=value1&param2=value2...]
 * @param {Function} callback Função para executar na resposta que precisa de tratar resposta [function(response){ ...implementar método }] [OPCIONAL]
 * @param {String} method POST|GET DEFAULT "POST" [OPCIONAL]
 */
function ajax(url, params, callback, method) {

    if (typeof method === 'undefined') {
        method = "POST";
    }

    var ajax = typeof XMLHttpRequest !== 'undefined' ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
    ajax.open(method, url);
    ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    ajax.responseType = 'json';

    ajax.onreadystatechange = function () {

        if (this.readyState === 4 && this.status === 200) {
            if (typeof ajax.response.message !== 'undefined') {
                alert(ajax.response.message);
            } else {
                callback(ajax.response);
            }
        }
    };

    ajax.send(params);
}

/**
 * Cria uma tabela HTML completa com colunas padrão "Código = value" e "Descrição = label"
 * 
 * @param {String} tableId Nome da tabela no banco de dados e ID do HTML
 * @param {String} url URL para consulta AJAX dessa tabela<br>
 * Formato retorno JSON:<br>
 * [{<br>
 * "value": "column_name",<br>
 * "label": "column_name",<br>
 * "column1": "value1",<br>
 * "column2": "value2", ...<br>
 * }, ...]<br>
 * <br>
 * Parâmetros requisição AJAX para registros paginação: table=[tableId]&action=find_all
 */
function tableHTML(tableId, url) {

    showSpinner();

    var limit = 100;
    var body = document.body;

    var table = document.createElement("TABLE");
    table.id = tableId;
    table.style = "margin-bottom: 12px;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 3px 10px 0 rgba(0,0,0,0.19)";
    table.appendChild(document.createElement("TBODY"));

    var search = createFilter(tableId, tableId + "_search", url, limit, function (response) {
        var tbody = table.getElementsByTagName("TBODY")[0];
        tbody.innerHTML = "";
        addRowsTable(response, table, limit);
    });

    body.appendChild(search);
    body.appendChild(table);

    addColumnTable(tableId, "Código", "value");
    addColumnTable(tableId, "Descrição", "label");

    setTimeout(function () {

        ajax(url, "table=" + table.id + "&action=find", function (response) {

            addRowsTable(response, table, limit);

            if (response.length > limit) {

                window.addEventListener("scroll", function () {

                    var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                    var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                    var scrolled = (winScroll / height) * 100;

                    if (scrolled === 100) {

                        var offset = table.getElementsByTagName("TBODY")[0].getElementsByTagName("TR").length;
                        var total = offset + limit;

                        addRowsTable(response, table, total, offset);
                    }
                });
            }

            hiddeSpinner();
        });

    }, 50);
}

/**
 * Cria um element HTML para filtrar através do AJAX
 * 
 * @param {String} tableId Identificador da tabela no banco de dados
 * @param {String} name ID e NAME do INPUT HTML
 * @param {String} url URL para consulta AJAX dessa tabela<br>
 * Formato retorno JSON:<br>
 * [{<br>
 * "value": "column_name",<br>
 * "label": "column_name",<br>
 * "column1": "value1",<br>
 * "column2": "value2", ...<br>
 * }, ...]<br>
 * <br>
 * Parâmetros requisição AJAX para registros paginação: table=[tableId]&action=find&term=[filter.value]&limit=[limit]
 * @param {int} limit Total de registros para retornar
 * @param {Function} callback function(response){ ... }
 * @returns {Element}
 */
function createFilter(tableId, name, url, limit, callback) {

    var input = document.createElement("INPUT");
    input.id = name;
    input.name = name;
    input.style = "background-position: 5px 5px;background-repeat: no-repeat;padding: 12px 12px 12px 40px;width: 85%;margin-bottom: 12px;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2);background-image: url(data:image/png;base64,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)";
    input.className = "ui-widget ui-widget-content ui-corner-all";
    input.onkeyup = function () {

        ajax(url, "table=" + tableId + "&action=find&term=" + this.value + "&limit=" + limit, function (response) {
            if (response.length > 0) {
                callback(response);
            } else {
                alert("Nenhum registro encontrado.");
            }
        });
    };
    input.placeholder = "Pesquisar";

    return input;
}

/**
 * Adiciona uma coluna na tabela
 * 
 * @param {String} tableId Identificador da tabela
 * @param {String} label Rótulo da coluna
 * @param {String} id Identificador da coluna
 */
function addColumnTable(tableId, label, id) {

    var table = document.getElementById(tableId);

    var thead = table.getElementsByTagName("THEAD")[0];

    if (typeof thead === 'undefined') {
        thead = document.createElement("THEAD");
        table.appendChild(thead);
    }

    var tr = thead.getElementsByTagName("TR")[0];

    if (typeof tr === 'undefined') {
        tr = document.createElement("TR");
        thead.appendChild(tr);
    }

    var th = document.createElement("TH");
    th.id = id;
    th.style = "text-align: left;padding: 12px";
    th.className = "ui-widget ui-widget-header";
    th.innerHTML = label;

    tr.appendChild(th);
}

/**
 * Adiciona linhas na tabela
 * 
 * @param {JSON} json [{"value":"value", "label":"label", "column1":"value1", "column2":"value2", ...}, ...]<br>
 * ou <br>
 * [{column1:"value1", column2:"value2", ...}, ...]
 * @param {Element} table elemento tabela
 * @param {int} limit fim do laço FOR no JSON [OPCIONAL, DEFAULT: json.length]
 * @param {int} offset inicio do laço FOR no JSON [OPCIONAL, DEFAULT: 0]
 */
function addRowsTable(json, table, limit, offset) {

    if (typeof offset === 'undefined') {
        offset = 0;
    }

    if (typeof limit === 'undefined' || json.length < limit) {
        limit = json.length;
    }

    var thead = table.getElementsByTagName("THEAD")[0];
    var tbody = table.getElementsByTagName("TBODY")[0];

    if (typeof thead !== 'undefined' && typeof tbody !== 'undefined') {

        for (var index = offset; index < limit; index++) {

            var tr = document.createElement("TR");
            tr.style = "cursor: pointer";
            tr.className = "ui-widget" + (index % 2 === 0 ? "" : " ui-state-highlight");

            if (typeof json[index]["value"] !== 'undefined') {
                tr.id = "row_id_" + json[index]["value"];
                tr.onclick = function () {
                    openSideNav("side_nav_" + this.id);
                };
            }

            var columns = thead.getElementsByTagName("TH");

            for (var i = 0; i < columns.length; i++) {

                var td = document.createElement("TD");

                if (columns[i].id === 'value') {
                    td.id = "cell_id_" + json[index].value;
                }

                td.style = "text-align: left;padding: 12px";
                td.innerHTML = json[index][columns[i].id];
                tr.appendChild(td);
            }

            tbody.appendChild(tr);

            if (typeof json[index]["value"] !== 'undefined') {

                var form = document.createElement("DIV");
                form.innerHTML = json[index]["value"] + ": " + json[index]["label"];

                addSideNav("side_nav_" + tr.id, form);
            }
        }
    }
}

/**
 * Ordena tabela pela coluna
 * 
 * @param {String} tableId Identificador da tabela
 * @param {int} columnIndex Índice da coluna
 */
function sortTable(tableId, columnIndex) {

    showSpinner();

    setTimeout(function () {

        var tbody = document.getElementById(tableId).getElementsByTagName("TBODY")[0];
        var rows = tbody.getElementsByTagName("TR");
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

        tbody.innerHTML = "";

        data.forEach(function (item, index) {

            var tr = document.createElement("TR");
            tr.style = "cursor: pointer";
            tr.className = "ui-widget" + (index % 2 === 0 ? "" : " ui-state-highlight");

            item.forEach(function (cell) {

                tr.appendChild(cell);

                if (cell.id !== null) {
                    var sidenav = document.getElementById("side_nav_" + cell.id.replace("cell_id_", "row_id_"));
                    if (sidenav !== null) {
                        tr.onclick = function () {
                            openSideNav(sidenav.id);
                        };
                    }
                }
            });

            tbody.appendChild(tr);
        });

        hiddeSpinner();

    }, 50);
}

/**
 * Filtro para tabela
 * 
 * @param {String} tableId Identificador da tabela
 * @param {String} filterValue Valor para pesquisa na tabela
 */
function filterTable(tableId, filterValue) {

    showSpinner();

    setTimeout(function () {

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

        hiddeSpinner();

    }, 50);
}

/**
 * Adiciona uma DIV SideNav no HTML
 * 
 * @param {String} id Identificador da DIV
 * @param {Element} content Elemento com o conteúdo
 */
function addSideNav(id, content) {

    var sidenav = document.getElementById("ctecinf_sidenav_panel");

    if (sidenav === null) {
        sidenav = document.createElement("DIV");
        sidenav.id = "ctecinf_sidenav_panel";
        document.body.appendChild(sidenav);
    }

    if (document.getElementById(id) === null) {

        var close = document.createElement("A");
        close.style = "color: #FF0000;position: absolute;top: 20px;right: 15px;font-size: 30px;padding: 8px;text-decoration: none";
        close.innerHTML = "&times;";
        close.href = "javascript:void(0)";
        close.onclick = function () {
            closeSideNav(id);
        };

        var panel = document.createElement("DIV");
        panel.style = "position: relative;top: 50px;padding-left: 20px;margin-top: 30px";
        panel.appendChild(content);

        var div = document.createElement("DIV");
        div.id = id;
        div.style = "height: 100%;width: 0;position: fixed;z-index: 1;top: 0;left: 0;overflow-x: hidden;padding-top: 20px;transition: 0.5s";
        div.className = "ui-widget ui-widget-content";
        div.appendChild(close);
        div.appendChild(panel);

        sidenav.appendChild(div);
    }
}

/**
 * Abre DIV lateral
 * 
 * @param {String} id
 * @param {String} width
 */
function openSideNav(id, width) {
    if (typeof width === "undefined") {
        document.getElementById(id).style.width = "98%";
    } else {
        document.getElementById(id).style.width = width;
    }
}

/**
 * Fecha DIV lateral
 * 
 * @param {String} id
 */
function closeSideNav(id) {
    document.getElementById(id).style.width = "0";
}

/**
 * AutoComplete "onkeyup='autoComplete(this, url)'"<br>
 * Para iniciar automaticamente utilize type='search' no INPUT HTML
 * 
 * @param {Element} input INPUT para autocomplete. Atributo ID do INPUT deve ser o nome da tabela e o atributo NAME o nome da coluna da tabela
 * @param {String} url URL para consulta AJAX. Parâmetros "table=[nome_tabela]&action=find&term=[filtro]&limit=8"
 */
function autoComplete(input, url) {

    var table = input.id;
    var body = document.body;

    var value = document.getElementById(input.id + "_value");

    if (value === null) {

        var hidden = document.createElement("INPUT");
        hidden.type = "hidden";
        hidden.id = input.id + "_value";
        hidden.name = input.name;
        body.appendChild(hidden);

        input.name = "";
    }

    var target = document.getElementById(input.id + "_list");

    if (target === null) {

        var coords = getElementCoords(input);

        target = document.createElement("DIV");
        target.id = input.id + "_list";
        target.className = "ui-widget ui-widget-content";
        target.style = "display: none;position: fixed;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 3px 10px 0 rgba(0,0,0,0.19);margin-top: -12px;padding: 5px;margin-bottom: 12px";
        target.style.left = coords.left + "px";
        target.style.top = coords.bottom + "px";

        body.appendChild(target);
    }

    ajax(url, "table=" + table + "&action=find&term=" + input.value + "&limit=8", function (response) {

        if (response.length > 0 && input.value.length > 0) {

            target.innerHTML = "";

            response.forEach(function (item, index) {

                var href = document.createElement("A");
                href.href = "javascript:void(0)";
                href.className = "ui-widget" + (index % 2 === 0 ? "" : " ui-state-highlight");
                href.style = "display: block;padding: 10px;text-decoration: none";
                href.innerHTML = item.label;
                href.onclick = function () {
                    value.value = item.value;
                    input.value = this.innerHTML;
                    target.innerHTML = "";
                    target.style.display = "none";
                };

                target.appendChild(href);
            });

            target.style.display = "block";

        } else {
            target.innerHTML = "";
            target.style.display = "none";
        }
    });
}

/**
 * Funções de formulário
 * *****************************************************************************
 */
/**
 * Inicia valores do formulário
 * 
 * @param {String} formId
 */
function initForm(formId) {

    showSpinner();

    setTimeout(function () {

        var form = document.getElementById(formId);

        if (document.getElementById("id").value > 0) {

            ajax(form.action, 'table=' + formId + '&action=find&id=' + document.getElementById("id").value, function (response) {

                for (var i = 0; i < form.elements.length; i++) {
                    if (form.elements[i].name.indexOf('data') !== -1) {
                        form.elements[i].value = response.data[form.elements[i].id];
                    }
                }

                hiddeSpinner();
            });

        } else {
            hiddeSpinner();
        }

    }, 50);
}

/**
 * AJAX com data[colunas] do formulário
 * 
 * @param {String} formId Identificador do formulário e nome da tabela no banco de dados
 * @param {String} action "save", "delete", ...
 */
function actionForm(formId, action) {

    var form = document.getElementById(formId);

    if (form.action.length > 0) {

        var params = [];
        var elems = form.elements;

        for (var i = 0; i < elems.length; i++) {

            if (elems[i].name !== "undefined" && elems[i].name.trim() !== "") {

                var index = elems[i].name.indexOf("[");

                if (index !== -1) {
                    params[params.length] = "data[" + elems[i].name.substring(0, index) + "]" + elems[i].name.substring(index) + "=" + elems[i].value;
                } else {
                    params[params.length] = "data[" + elems[i].name + "]=" + elems[i].value;
                }
            }
        }

        if (params.length === 0) {
            alert("Falta parâmetros.");
        } else {
            ajax(form.action, "table=" + formId + "&action=" + action + "&" + params.join("&"));
        }
    }
}

/**
 * Coordenadas de um elemento
 * 
 * @param {Element} elem
 * @returns {top:[int], left:[int], bottom:[int], right:[int]}
 */
function getElementCoords(elem) {

    var box = elem.getBoundingClientRect();

    var style = elem.currentStyle || window.getComputedStyle(elem);

    var body = document.body;
    var docEl = document.documentElement;

    var scrollTop = window.pageYOffset || docEl.scrollTop || body.scrollTop;
    var scrollLeft = window.pageXOffset || docEl.scrollLeft || body.scrollLeft;

    var clientTop = docEl.clientTop || body.clientTop || 0;
    var clientLeft = docEl.clientLeft || body.clientLeft || 0;

    var top = box.top + scrollTop - clientTop;
    var left = box.left + scrollLeft - clientLeft;
    var bottom = box.bottom + parseInt(style.paddingBottom);
    var right = box.right + parseInt(style.paddingRigth);

    return {top: Math.round(top), left: Math.round(left), bottom: Math.round(bottom), right: Math.round(right)};
}

/**
 * Teclado numérico<br>
 * Para iniciar automaticamente utilize type='number' para decimal e type='tel' para somente números no INPUT HTML
 * 
 * @param {Element} input Element INPUT
 * @param {boolean} onlyNumber Somente números sem pontos e vírgulas [OPCIONAL] DEFAULT decimal numeric
 */
function numericKeyboard(input, onlyNumber) {

    if (typeof onlyNumber === 'undefined') {
        onlyNumber = false;
    }

    var keyboard = document.getElementById(input.id + "_keyboard");

    if (keyboard === null) {

        var coords = getElementCoords(input);

        keyboard = document.createElement("DIV");
        keyboard.id = input.id + "_keyboard";
        keyboard.className = "ui-widget ui-widget-content";
        keyboard.style = "display: none;position: fixed;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 3px 10px 0 rgba(0,0,0,0.19);margin-top: -12px;padding: 5px;margin-bottom: 12px";
        keyboard.style.left = coords.left + "px";
        keyboard.style.top = coords.bottom + "px";

        document.body.appendChild(keyboard);
    }

    if (keyboard.innerHTML === "") {

        var table = document.createElement("TABLE");
        table.style.width = "200px";

        var tr = document.createElement("TR");

        var td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "7";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, this.innerHTML);
            } else {
                setDecimalValue(input, this.innerHTML);
            }
        };
        tr.appendChild(td);

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "8";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, this.innerHTML);
            } else {
                setDecimalValue(input, this.innerHTML);
            }
        };
        tr.appendChild(td);

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "9";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, this.innerHTML);
            } else {
                setDecimalValue(input, this.innerHTML);
            }
        };
        tr.appendChild(td);

        table.appendChild(tr);

        tr = document.createElement("TR");

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "4";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, this.innerHTML);
            } else {
                setDecimalValue(input, this.innerHTML);
            }
        };
        tr.appendChild(td);

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "5";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, this.innerHTML);
            } else {
                setDecimalValue(input, this.innerHTML);
            }
        };
        tr.appendChild(td);

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "6";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, this.innerHTML);
            } else {
                setDecimalValue(input, this.innerHTML);
            }
        };
        tr.appendChild(td);

        table.appendChild(tr);

        tr = document.createElement("TR");

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "1";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, this.innerHTML);
            } else {
                setDecimalValue(input, this.innerHTML);
            }
        };
        tr.appendChild(td);

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "2";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, this.innerHTML);
            } else {
                setDecimalValue(input, this.innerHTML);
            }
        };
        tr.appendChild(td);

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "3";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, this.innerHTML);
            } else {
                setDecimalValue(input, this.innerHTML);
            }
        };
        tr.appendChild(td);

        table.appendChild(tr);

        tr = document.createElement("TR");

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "+/-";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, this.innerHTML);
            } else {
                setDecimalValue(input, this.innerHTML);
            }
        };
        tr.appendChild(td);

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "0";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, this.innerHTML);
            } else {
                setDecimalValue(input, this.innerHTML);
            }
        };
        tr.appendChild(td);

        table.appendChild(tr);

        tr = document.createElement("TR");

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "&#10006;";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, 'limpar');
            } else {
                setDecimalValue(input, 'limpar');
            }
        };
        tr.appendChild(td);

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "&#9756;";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, 'voltar');
            } else {
                setDecimalValue(input, 'voltar');
            }
        };
        tr.appendChild(td);

        td = document.createElement("TD");
        td.style = "cursor: pointer;font-size: 33px;width: 33%;padding: 10px;padding-bottom: 3px;padding-top: 3px;text-align: center;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)";
        td.className = "ui-widget ui-widget-content";
        td.innerHTML = "&#10004;";
        td.onclick = function () {
            if (onlyNumber) {
                setIntegerValue(input, 'ok');
            } else {
                setDecimalValue(input, 'ok');
            }
        };
        tr.appendChild(td);

        table.appendChild(tr);

        keyboard.appendChild(table);

        if (!onlyNumber) {
            var digits = document.createElement("INPUT");
            digits.id = input.id + "_digits";
            digits.type = "hidden";
            keyboard.appendChild(digits);
        }
    }

    if (keyboard.style.display === "block") {
        keyboard.style.display = "none";
    } else {
        keyboard.style.display = "block";
    }
}

/**
 * Seta valor no input via teclado numérico
 * 
 * @param {Element} input
 * @param {String} value 
 */
function setDecimalValue(input, value) {

    var digits = document.getElementById(input.id + "_digits");
    var keyboard = document.getElementById(input.id + "_keyboard");

    if (value === "+/-") {
        input.value = input.value.substring(0, 1) === "-" ? input.value.substring(1) : "-" + input.value;
    } else if (value === "limpar") {
        digits.value = "";
        input.value = "";
        keyboard.style.display = "none";
    } else if (value === "voltar") {
        digits.value = digits.value.substring(0, digits.value.length - 1);
        var num = parseFloat(digits.value);
        input.value = num.format();
    } else if (value === "ok") {
        keyboard.style.display = "none";
    } else {
        if (input.value.length > 17) {
            return;
        } else {
            digits.value += value;
            var num = parseFloat(digits.value);
            input.value = num.format();
        }
    }
}

/**
 * Seta valor no input via teclado numérico
 * 
 * @param {Element} input
 * @param {String} value 
 */
function setIntegerValue(input, value) {

    var keyboard = document.getElementById(input.id + "_keyboard");

    if (value === "+/-") {
        input.value = input.value.substring(0, 1) === "-" ? input.value.substring(1) : "-" + input.value;
    } else if (value === "limpar") {
        input.value = "";
        keyboard.style.display = "none";
    } else if (value === "voltar") {
        input.value = input.value.substring(0, input.value.length - 1);
    } else if (value === "ok") {
        keyboard.style.display = "none";
    } else {
        if (input.value.length > 17) {
            return;
        } else {
            input.value += value;
        }
    }
}

/**
 * Abre SPINNER
 */
function showSpinner() {

    var spinner = document.getElementById("ctecinf_spinner");

    if (spinner === null) {

        var img = document.createElement("IMG");
        img.style = "z-index: 1;margin: 30px;width: 80px;height: 80px;position: relative";
        img.src = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/Pgo8IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPgo8c3ZnIHdpZHRoPSI0MHB4IiBoZWlnaHQ9IjQwcHgiIHZpZXdCb3g9IjAgMCA0MCA0MCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWw6c3BhY2U9InByZXNlcnZlIiBzdHlsZT0iZmlsbC1ydWxlOmV2ZW5vZGQ7Y2xpcC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlLWxpbmVqb2luOnJvdW5kO3N0cm9rZS1taXRlcmxpbWl0OjEuNDE0MjE7IiB4PSIwcHgiIHk9IjBweCI+CiAgICA8ZGVmcz4KICAgICAgICA8c3R5bGUgdHlwZT0idGV4dC9jc3MiPjwhW0NEQVRBWwogICAgICAgICAgICBALXdlYmtpdC1rZXlmcmFtZXMgc3BpbiB7CiAgICAgICAgICAgICAgZnJvbSB7CiAgICAgICAgICAgICAgICAtd2Via2l0LXRyYW5zZm9ybTogcm90YXRlKDBkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICAgIHRvIHsKICAgICAgICAgICAgICAgIC13ZWJraXQtdHJhbnNmb3JtOiByb3RhdGUoLTM1OWRlZykKICAgICAgICAgICAgICB9CiAgICAgICAgICAgIH0KICAgICAgICAgICAgQGtleWZyYW1lcyBzcGluIHsKICAgICAgICAgICAgICBmcm9tIHsKICAgICAgICAgICAgICAgIHRyYW5zZm9ybTogcm90YXRlKDBkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICAgIHRvIHsKICAgICAgICAgICAgICAgIHRyYW5zZm9ybTogcm90YXRlKC0zNTlkZWcpCiAgICAgICAgICAgICAgfQogICAgICAgICAgICB9CiAgICAgICAgICAgIHN2ZyB7CiAgICAgICAgICAgICAgICAtd2Via2l0LXRyYW5zZm9ybS1vcmlnaW46IDUwJSA1MCU7CiAgICAgICAgICAgICAgICAtd2Via2l0LWFuaW1hdGlvbjogc3BpbiAxLjVzIGxpbmVhciBpbmZpbml0ZTsKICAgICAgICAgICAgICAgIC13ZWJraXQtYmFja2ZhY2UtdmlzaWJpbGl0eTogaGlkZGVuOwogICAgICAgICAgICAgICAgYW5pbWF0aW9uOiBzcGluIDEuNXMgbGluZWFyIGluZmluaXRlOwogICAgICAgICAgICB9CiAgICAgICAgXV0+PC9zdHlsZT4KICAgIDwvZGVmcz4KICAgIDxnIGlkPSJvdXRlciI+CiAgICAgICAgPGc+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMCwwQzIyLjIwNTgsMCAyMy45OTM5LDEuNzg4MTMgMjMuOTkzOSwzLjk5MzlDMjMuOTkzOSw2LjE5OTY4IDIyLjIwNTgsNy45ODc4MSAyMCw3Ljk4NzgxQzE3Ljc5NDIsNy45ODc4MSAxNi4wMDYxLDYuMTk5NjggMTYuMDA2MSwzLjk5MzlDMTYuMDA2MSwxLjc4ODEzIDE3Ljc5NDIsMCAyMCwwWiIgc3R5bGU9ImZpbGw6YmxhY2s7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNNS44NTc4Niw1Ljg1Nzg2QzcuNDE3NTgsNC4yOTgxNSA5Ljk0NjM4LDQuMjk4MTUgMTEuNTA2MSw1Ljg1Nzg2QzEzLjA2NTgsNy40MTc1OCAxMy4wNjU4LDkuOTQ2MzggMTEuNTA2MSwxMS41MDYxQzkuOTQ2MzgsMTMuMDY1OCA3LjQxNzU4LDEzLjA2NTggNS44NTc4NiwxMS41MDYxQzQuMjk4MTUsOS45NDYzOCA0LjI5ODE1LDcuNDE3NTggNS44NTc4Niw1Ljg1Nzg2WiIgc3R5bGU9ImZpbGw6cmdiKDIxMCwyMTAsMjEwKTsiLz4KICAgICAgICA8L2c+CiAgICAgICAgPGc+CiAgICAgICAgICAgIDxwYXRoIGQ9Ik0yMCwzMi4wMTIyQzIyLjIwNTgsMzIuMDEyMiAyMy45OTM5LDMzLjgwMDMgMjMuOTkzOSwzNi4wMDYxQzIzLjk5MzksMzguMjExOSAyMi4yMDU4LDQwIDIwLDQwQzE3Ljc5NDIsNDAgMTYuMDA2MSwzOC4yMTE5IDE2LjAwNjEsMzYuMDA2MUMxNi4wMDYxLDMzLjgwMDMgMTcuNzk0MiwzMi4wMTIyIDIwLDMyLjAxMjJaIiBzdHlsZT0iZmlsbDpyZ2IoMTMwLDEzMCwxMzApOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTI4LjQ5MzksMjguNDkzOUMzMC4wNTM2LDI2LjkzNDIgMzIuNTgyNCwyNi45MzQyIDM0LjE0MjEsMjguNDkzOUMzNS43MDE5LDMwLjA1MzYgMzUuNzAxOSwzMi41ODI0IDM0LjE0MjEsMzQuMTQyMUMzMi41ODI0LDM1LjcwMTkgMzAuMDUzNiwzNS43MDE5IDI4LjQ5MzksMzQuMTQyMUMyNi45MzQyLDMyLjU4MjQgMjYuOTM0MiwzMC4wNTM2IDI4LjQ5MzksMjguNDkzOVoiIHN0eWxlPSJmaWxsOnJnYigxMDEsMTAxLDEwMSk7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNMy45OTM5LDE2LjAwNjFDNi4xOTk2OCwxNi4wMDYxIDcuOTg3ODEsMTcuNzk0MiA3Ljk4NzgxLDIwQzcuOTg3ODEsMjIuMjA1OCA2LjE5OTY4LDIzLjk5MzkgMy45OTM5LDIzLjk5MzlDMS43ODgxMywyMy45OTM5IDAsMjIuMjA1OCAwLDIwQzAsMTcuNzk0MiAxLjc4ODEzLDE2LjAwNjEgMy45OTM5LDE2LjAwNjFaIiBzdHlsZT0iZmlsbDpyZ2IoMTg3LDE4NywxODcpOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTUuODU3ODYsMjguNDkzOUM3LjQxNzU4LDI2LjkzNDIgOS45NDYzOCwyNi45MzQyIDExLjUwNjEsMjguNDkzOUMxMy4wNjU4LDMwLjA1MzYgMTMuMDY1OCwzMi41ODI0IDExLjUwNjEsMzQuMTQyMUM5Ljk0NjM4LDM1LjcwMTkgNy40MTc1OCwzNS43MDE5IDUuODU3ODYsMzQuMTQyMUM0LjI5ODE1LDMyLjU4MjQgNC4yOTgxNSwzMC4wNTM2IDUuODU3ODYsMjguNDkzOVoiIHN0eWxlPSJmaWxsOnJnYigxNjQsMTY0LDE2NCk7Ii8+CiAgICAgICAgPC9nPgogICAgICAgIDxnPgogICAgICAgICAgICA8cGF0aCBkPSJNMzYuMDA2MSwxNi4wMDYxQzM4LjIxMTksMTYuMDA2MSA0MCwxNy43OTQyIDQwLDIwQzQwLDIyLjIwNTggMzguMjExOSwyMy45OTM5IDM2LjAwNjEsMjMuOTkzOUMzMy44MDAzLDIzLjk5MzkgMzIuMDEyMiwyMi4yMDU4IDMyLjAxMjIsMjBDMzIuMDEyMiwxNy43OTQyIDMzLjgwMDMsMTYuMDA2MSAzNi4wMDYxLDE2LjAwNjFaIiBzdHlsZT0iZmlsbDpyZ2IoNzQsNzQsNzQpOyIvPgogICAgICAgIDwvZz4KICAgICAgICA8Zz4KICAgICAgICAgICAgPHBhdGggZD0iTTI4LjQ5MzksNS44NTc4NkMzMC4wNTM2LDQuMjk4MTUgMzIuNTgyNCw0LjI5ODE1IDM0LjE0MjEsNS44NTc4NkMzNS43MDE5LDcuNDE3NTggMzUuNzAxOSw5Ljk0NjM4IDM0LjE0MjEsMTEuNTA2MUMzMi41ODI0LDEzLjA2NTggMzAuMDUzNiwxMy4wNjU4IDI4LjQ5MzksMTEuNTA2MUMyNi45MzQyLDkuOTQ2MzggMjYuOTM0Miw3LjQxNzU4IDI4LjQ5MzksNS44NTc4NloiIHN0eWxlPSJmaWxsOnJnYig1MCw1MCw1MCk7Ii8+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4K";

        var modal = document.createElement("DIV");
        modal.className = "ui-widget ui-widget-overlay";
        modal.style = "width: 100vw;height: 100vh;top: -10px;left: -10px";

        spinner = document.createElement("DIV");
        spinner.id = "ctecinf_spinner";
        spinner.style = "position: fixed;top: 0;left: 0";

        spinner.appendChild(modal);
        spinner.appendChild(img);

        document.body.appendChild(spinner);
    }

    spinner.style.display = "";
    spinner.scrollIntoView();
}

/**
 * Fecha SPINNER
 */
function hiddeSpinner() {

    var spinner = document.getElementById("ctecinf_spinner");

    if (spinner !== null) {
        spinner.style.display = "none";
    }

    topScroll();
}

/**
 * Posiciona no topo da página
 */
function topScroll() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
}

/**
 * Adiciona botão para voltar ao topo da página
 */
function addTopButton() {

    var top = document.getElementById("ctecinf_top_button");

    if (top === null) {

        top = document.createElement("BUTTON");

        top.id = "ctecinf_top_button";
        top.type = "button";
        top.style = "display: none;position: fixed;opacity: 0.4;bottom: 20px;right: 30px;z-index: 1;font-size: 24px;transition-duration: 0.4s;cursor: pointer;padding: 10px;outline: none;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 3px 10px 0 rgba(0,0,0,0.19)";
        top.className = "ui-widget ui-button ui-corner-all";
        top.innerHTML = "&#9757; Topo";
        top.onmouseover = function () {
            top.style.opacity = "0.9";
        };
        top.onmouseout = function () {
            top.style.opacity = "0.4";
        };
        top.onclick = function () {
            topScroll();
        };

        document.body.appendChild(top);
    }
}

/**
 * Configura eventos de inicialização
 * *****************************************************************************
 */
window.addEventListener("scroll", function () {

    var button = document.getElementById("ctecinf_top_button");

    if (button !== null) {

        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            button.style.display = "block";
        } else {
            button.style.display = "none";
        }
    }
});

window.addEventListener("load", function () {

    addTopButton();

    setTimeout(function () {

        var inputs = document.getElementsByTagName("INPUT");

        for (var i = 0; i < inputs.length; i++) {

            if (inputs[i].type.toLowerCase() === "number") {

                inputs[i].type = "tel";
                inputs[i].readonly = "readonly";
                inputs[i].addEventListener("click", function () {
                    numericKeyboard(this);
                });

            } else if (inputs[i].type.toLowerCase() === "tel") {

                inputs[i].readonly = "readonly";
                inputs[i].addEventListener("click", function () {
                    numericKeyboard(this, true);
                });

            } else if (inputs[i].type.toLowerCase() === "search") {
                inputs[i].style = "background-position: 5px 5px;background-repeat: no-repeat;padding: 12px 12px 12px 40px;width: 85%;margin-bottom: 12px;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2);background-image: url(data:image/png;base64,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)";
                inputs[i].placeholder = "Pesquisar";
            }

            inputs[i].className = "ui-widget ui-widget-content ui-corner-all";
        }
    }, 50);
});
