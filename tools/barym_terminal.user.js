// ==UserScript==
// @name           barym_terminal
// @namespace      andorianin
// @description    Link to Barym Trade Terminal
// @include        http://uni102.ogame.ru/*
// @include        http://barym.ogame.ru/*
// ==/UserScript==

var TerminalButton = "<li>\n" +
    "<span class=\"menu_icon\"> </span>\n" +
    "<a class=\"menubutton \"\n" +
    "    href=\"http://7658459.ru/trade/main.php\"\n" +
    "    accesskey=\"\"\n" +
    "    target=\"_blank\">\n" +
    "    <span class=\"textlabel\">Терминал</span>\n" +
    "</a>\n" +
    "</li>\n";

document.getElementById ("menuTable").innerHTML += TerminalButton;
