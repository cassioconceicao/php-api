<!DOCTYPE html>
<!--
Copyright (C) 2021 ctecinf.com.br

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
-->
<html>
    <head>
        <title>CTecInf - Sistemas gerenciais :: LOGIN</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=0.70, maximum-scale=1.0, minimum-scale=0.70">
        <style type="text/css">
            .ctecinf-input {background-color: #FFF;color: #333;border-radius: 5px;border: 1px solid #DDD;font-size: 18px;padding: 12px;margin-bottom: 12px;box-shadow: inset 0 3px 6px rgba(0,0,0,0.2)}
            .ctecinf-form fieldset {background-color: #EFEFEF;color: #333;border-radius: 5px;border: 1px solid #DDD;margin-bottom: 12px;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 3px 10px 0 rgba(0,0,0,0.19)}
            .ctecinf-form fieldset legend {background-color: #008B8B;color: #FFF;border-radius: 5px;border: 1px solid #DDD;padding: 5px;font-weight: bold}
            .ctecinf-button {color: #FFF;border-radius: 5px;padding: 8px;background-color: #008B8B;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 3px 10px 0 rgba(0,0,0,0.19);transition-duration: 0.4s;margin-right: 12px}
            .ctecinf-button:hover {opacity: 0.8; cursor: pointer}
        </style>
        <script type="text/javascript">

            function ajax() {

                var cnpj = document.getElementById("cnpj").value;
                var senha = document.getElementById("senha").value;
                var url = "index.php";
                var method = "POST";
                var params = "data[cnpj]=" + cnpj + "&data[senha]=" + senha;

                var ajax = typeof XMLHttpRequest !== 'undefined' ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
                ajax.open(method, url);
                ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                ajax.responseType = 'json';

                ajax.onreadystatechange = function () {

                    if (this.readyState === 4 && this.status === 200) {

                        var ok = ajax.response.return === 'true';
                        var message = ajax.response.message;
                        var type = ajax.response.type;

                        if (ok) {
                            window.location = 'session.php?cnpj=' + cnpj;
                        } else {
                            alert(type + ":" + message);
                        }
                    }
                };

                ajax.send(params);
            }
        </script>
    </head>
    <body>
        <form id="login" class="ctecinf-form" style="width: 200px">

            <fieldset>

                <legend>Login</legend>

                <label for="cnpj">CNPJ</label><br />
                <input type="tel" id="cnpj" />

                <br />

                <label for="senha">Senha</label><br />
                <input type="password" id="senha" />

            </fieldset>

        </form>

        <button type="button" class="ctecinf-button" onclick="ajax()">
            Acessar
        </button>

    </body>
</html>
