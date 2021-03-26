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

session_start();

/**
 * Configurar Conexão com o Banco de Dados<br>
 * DB_DSN: "mysql" ou "pgsql"
 */
define("DB_DSN", "mysql");
define("DB_HOST", "localhost");
define("DB_NAME", "hobby");
define("DB_USER", "root");
define("DB_PASS", "root");

//define("DB_DSN", "pgsql");
//define("DB_HOST", "localhost");
//define("DB_NAME", "hobby");
//define("DB_USER", "postgres");
//define("DB_PASS", "postgres");

define("PDO_OPTIONS", serialize(array(
    PDO::ATTR_CASE => PDO::CASE_LOWER,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
)));

/**
 * Definição de cores CSS
 */
define("ROW_COLOR", "#DDFFDD");
define("ROW_FOCUS_COLOR", "#CCFFFF");

/**
 * Ícones Base 64
 */
define("SAVE_ICON", "iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAN1wAADdcBQiibeAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAATdEVYdFRpdGxlAE9wdGljYWwgRHJpdmU+Z7oMAAAEVklEQVRYhcWXz49TVRzFP9/77nsznR/tOAUxmggiMIg6atiIaFy4xJ0xunLBRoMLF2jCQmLYuHKF/gXujDtiDAsXJhiEoAQchyCJCTEROhkSpzrlvbb33q+L23amVGYmaQ3fpO3t+3XOveecb29FVXmQZR4oOmDXf3nzrTdOishJIL1bNFl6+O2RAbV/+ozgHSGElgZOLS4ufjpAQEQ+3rlrT1oul/n58hWOv/MyLgwPbg18Wfuaubl91Ov17Py57z8BBgkA2XS5zLdnz9Jqtbl6c4WWG94jmRVu1W5zq1bjlcOHAbIeuXsvVlUunDvHwRcPcezVClNT00MTWF39h7On4cqlixx+6RAhrC1rnwlVFVXloxMnhgYdLOGD4x+C6v0JhBDQEPjx4gVEZLTwAj+cP48qfQT6JAghgGoPvN5Yod5YGRUFjAigGxNQVYwIIlCZnKFSmRkaul5fQQREBNXNCBAvFOIqGDO6XhUJhM1WIEQJpHvTaAiIRAmiB9aifV8JQEbsARCzFQlUERMFqEzOsH37jqGBl5eXoqwisJkE3RR0UzhKDxgRdOMUKKrxwi6D0XlgyynQXgoajQa12p9DgzcaDURMR4JNG1FMwXgp4+i7x6CzYRn4SdK1o9p3bGCIAFlqOybcNIbRhKWxMUpjY+h6An3j+NYdaxdy3bi32dLeWVTBe78BAbRjllj79j7J/qfmUFW894QQCN7jQyD4gA+e4OOsuud98FxbvM716zdQoqfK5TITU1Mgm3iAsJYCVdi9exfPzD9NdXYbWZqRphmZjZ+JMTjvcM7jvANVrE0pipzPvzhNs+0QhGarydLtGpPT02jYogm72jVbLWZnZrn8y0Vu/bFMM28RQqBUGufI60fI85y8yMnzu2RpxsTEJHue2Eue5zjnQGGiVEKM2UoM44meBAJFUWCMIUks9b/qLCxcA2Bubg/NZhPn2rh2mxACSZKQJAmIkOdFfI7QOy4iiCq6USsO3UbU8VmRFxiTkNqUoih6BsuLAu8cznuc91ibxleSEkKgKIrOc5QkSbBJElNAfwwHNiTSbcUimHUrkKaRgOm01KIocN7jvcN7h7WWJLFYa1FVnHO99pvYhCTpSHDP/5ABAj54SuPjvZsjqCG1WW9WRoQiLyK48xgx2A54CKFPShHBJglJYjve+g8CEisV9OrCwq/NanUbpdL4GgFjSG1KmmaUSuNkWUqapijgOrO31mITi/cO1UhATCQbPbDWCTuYqYhI1wMCTN347ff3ms32V865R+fnn7Uihjt3lkGEA/vmmT91sAfSzb3zDpukPRLOueh+oDpbRTVQqcxQ3VZlenKav1f/BnDAQ8Ad0Wi6BHgE2G+tfW3nrsePAjtCCDz/wnNYO7B737RazRZnznzT5/jOzL0x5jvv/fvAzS4BA1SAx4ADwPS9/hhheWAJuMS6FRBiJMeA8f8RvFttYBVwsj4WMuo/AxuUdoD/BeaOg3cw+7FtAAAAAElFTkSuQmCC");
define("DELETE_ICON", "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");
define("SEARCH_ICON", "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");
define("HOME_ICON", "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");
