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

require_once '../../index.php';

echo getHTMLHead();

?>
<style type="text/css">

    #menu-bar {color: <?php echo TEXT_COLOR ?>;border-bottom: 1px solid <?php echo BORDER_COLOR ?>; margin-bottom: 12px}
    #menu-bar span {font-size: 30px;cursor: pointer}
    .overlay {height: 100%;width: 0;position: fixed;z-index: 1;top: 0;left: 0;background-color: rgb(0,0,0);background-color: rgba(0,0,0, 0.9);overflow-x: hidden;transition: 0.5s}
    .overlay-content {position: relative;top: 25%;width: 100%;text-align: center;margin-top: 30px}
    .overlay a {padding: 8px;text-decoration: none;font-size: 36px;color: #818181;display: block;transition: 0.3s}
    .overlay a:hover, .overlay a:focus {color: #f1f1f1}
    .overlay .closebtn {position: absolute;top: 20px;right: 45px;font-size: 60px}
    @media screen and (max-height: 450px) {.overlay a {font-size: <?php echo FONT_SIZE ?>}.overlay .closebtn {font-size: 40px;top: 15px;right: 35px}}
    
</style>

<script type="text/javascript">
    function openMenu() {
        document.getElementById("menu").style.width = "80%";
    }
    function closeMenu() {
        document.getElementById("menu").style.width = "0%";
    }
</script>

<div id="menu-bar">
    <span onclick="openMenu()">&#9776;</span> Sistemas Gerenciais
</div>
<div id="menu" class="overlay">
    <a href="javascript:void(0)" class="closebtn" onclick="closeMenu()">&times;</a>
    <div class="overlay-content">
        <a href="#">About</a>
        <a href="#">Services</a>
        <a href="#">Clients</a>
        <a href="#">Contact</a>
    </div>
</div>
<?php

echo TableHelper::create(Municipio::class);
