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
 * *********** NÃO EDITAR ******************************************************
 */
$path = "orm/";
while (!file_exists($path)) {
    $path = "../" . $path;
}

/**
 * Define PATH
 */
define("ORM_PATH", $path);
define("MODEL_PATH", "{$path}model/");
define("CONTROLLER_PATH", "{$path}controller/");
define("VIEW_PATH", "{$path}view/");

/**
 * Incluí configuração e superclasse das modelos
 */
require_once "{$path}Config.php";
require_once "{$path}DataType.class.php";
require_once "{$path}Connection.class.php";
require_once "{$path}Model.class.php";
require_once "{$path}FormHelper.class.php";
require_once "{$path}TableHelper.class.php";

if (isset($_GET["create"])) {

    if (DB_DSN == "pgsql") {
        $query = "SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema NOT IN ('pg_catalog', 'information_schema')";
    } else {
        $query = "SHOW TABLES";
    }

    try {

        $pdo = new PDO(DB_DSN . ":host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, unserialize(PDO_OPTIONS));
        $st = $pdo->query($query);
        $rs = $st->fetchAll();

        foreach ($rs as $row) {

            $table = trim($row[array_keys($row)[0]]);
            $name = "";
            $isUpper = true;

            foreach (str_split($table) as $c) {

                if ($c == "_") {
                    $isUpper = true;
                } else {
                    if ($isUpper) {
                        $name .= strtoupper($c);
                        $isUpper = false;
                    } else {
                        $name .= strtolower($c);
                    }
                }
            }

            $fileName = MODEL_PATH . "{$name}.class.php";

            if (!file_exists($fileName)) {
                $handle = fopen($fileName, "w");
                fwrite($handle, str_replace("\$year", date("Y"), str_replace("\$className", "{$name}", file_get_contents(ORM_PATH . "doc/model.txt"))));
                chmod($fileName, 0777);
                fclose($handle);
            }

            $fileName = CONTROLLER_PATH . "{$name}.php";

            if (!file_exists($fileName)) {
                $handle = fopen($fileName, "w");
                fwrite($handle, str_replace("\$year", date("Y"), str_replace("\$className", "{$name}", file_get_contents(ORM_PATH . "doc/controller.txt"))));
                chmod($fileName, 0777);
                fclose($handle);
            }

            $fileName = VIEW_PATH . "{$name}/";

            if (mkdir($fileName, 0777)) {

                chmod($fileName, 0777);
                
                if (!file_exists($fileName . "index.php")) {
                    $handle = fopen($fileName . "index.php", "w");
                    fwrite($handle, str_replace("\$year", date("Y"), str_replace("\$className", "{$name}", file_get_contents(ORM_PATH . "doc/viewlist.txt"))));
                    chmod($fileName . "index.php", 0777);
                    fclose($handle);
                }

                if (!file_exists($fileName . "form.php")) {
                    $handle = fopen($fileName . "form.php", "w");
                    fwrite($handle, str_replace("\$year", date("Y"), str_replace("\$className", "{$name}", file_get_contents(ORM_PATH . "doc/viewform.txt"))));
                    chmod($fileName . "form.php", 0777);
                    fclose($handle);
                }
            }
        }

        echo "Modelo(s), Controle(s) e 'View(s)' gerado(s) com sucesso!";
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
} else {

    /**
     * Incluí classes modelos
     */
    $handle = opendir(MODEL_PATH);
    if ($handle) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "index.php") {
                require_once MODEL_PATH . $file;
            }
        }
        closedir($handle);
    }
}

define("ACTION_SAVE", "save");
define("ACTION_DELETE", "delete");
define("ACTION_FIND", "find");
define("ACTION_COUNT", "count");

/**
 * Funções para controller
 */
function getAction() {
    if (isset($_POST["action"])) {
        return strtolower($_POST["action"]);
    } else if (isset($_GET["action"])) {
        return strtolower($_GET["action"]);
    } else {
        return false;
    }
}

function getTerm() {
    if (isset($_POST["term"])) {
        return $_POST["term"];
    } else if (isset($_GET["term"])) {
        return $_GET["term"];
    } else {
        return "";
    }
}

function getMaxResults() {
    if (isset($_POST["limit"])) {
        return $_POST["limit"];
    } else if (isset($_GET["limit"])) {
        return $_GET["limit"];
    } else {
        return -1;
    }
}

function getOffSet() {
    if (isset($_POST["offset"])) {
        return $_POST["offset"];
    } else if (isset($_GET["offset"])) {
        return $_GET["offset"];
    } else {
        return 0;
    }
}

function getId() {
    if (isset($_POST["id"])) {
        return $_POST["id"];
    } else if (isset($_GET["id"])) {
        return $_GET["id"];
    } else {
        return "";
    }
}

function getData() {
    if (isset($_POST["data"])) {
        return $_POST["data"];
    } else if (isset($_GET["data"])) {
        return $_GET["data"];
    } else {
        return false;
    }
}

function isMobile() {
    return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $_SERVER["HTTP_USER_AGENT"]) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($_SERVER["HTTP_USER_AGENT"], 0, 4));
}

function getHTMLHead($spinner = true, $charset = "UTF-8") {

    $html = "<!DOCTYPE html>\n";
    $html .= "<meta charset=\"{$charset}\" />\n";
    $html .= "<title>" . TITLE . "</title>\n";
    $html .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=0.70, maximum-scale=1.0, minimum-scale=0.70\" />\n";

    if ($spinner) {
        $html .= file_get_contents(ORM_PATH . "doc/spinner.txt");
    }

    return $html;
}

function getMenu() {

    $menu = unserialize(MENU_OPTIONS);
    $menuList = "";
    foreach ($menu as $label => $url) {
        $menuList .= "<a href=\"" . VIEW_PATH . "{$url}\">{$label}</a>\n";
    }

    $html = file_get_contents(ORM_PATH . "doc/menu.txt");
    $html = str_replace("\$menuList", $menuList, $html);
    $html = str_replace("\$title", TITLE, $html);
    $html = str_replace("\$borderColor", BORDER_COLOR, $html);
    $html = str_replace("\$textColor", TEXT_COLOR, $html);
    $html = str_replace("\$fontSize", FONT_SIZE, $html);
    $html = str_replace("\$backgroundColor", BACKGROUND_COLOR, $html);
    $html = str_replace("\$highlightColor", HIGHLIGHT_COLOR, $html);

    return $html;
}
