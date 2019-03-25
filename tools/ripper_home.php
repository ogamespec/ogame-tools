<?php

// -----------------------------------------------------------------------------------------------------------------
// Главная страница.

function PageHome ($errorCode = 0)
{
    global $version, $errors;
    PageHeader ();

    if ( $errorCode ) {
        echo "<script type=\"text/javascript\">\n";
        echo "    $(function() {\n";
        echo "        $(\"#dialogRipperError\").dialog({   \n";
        echo "            width: 400, bgiframe: true, modal: true,  \n";
        echo "            buttons: { \"На главную\": function() { window.location = \"".scriptname()."\"; $(this).dialog('close'); } }   \n";
        echo "        });\n";
        echo "    });\n";
        echo "</script>\n\n";
    }

    echo "<div id=\"home_content\" class=\"ui-widget-content ui-corner-all\">\n";
    echo "    <table style=\"width: 100%\"><tr><td class=\"ui-widget-header\"><span class=\"header\">Ripper</span> - программный комплекс для слежения за онлайном игроков, для поиска скрытых ишек и безопасного полёта на Зв<span id=\"easter_egg\">ё</span>здах Смерти.</td></tr></table>\n";
    echo "        <table cellpadding=\"0\"><tr><td>Если у вас ещё нет аккаунта и Сигнатуры для сбора данных, вы можете создать новый аккаунт.</td>\n";
    echo "        <td><a href=\"#\" id=\"createSig\" class=\"fg-button ui-state-default ui-corner-all\">Создать аккаунт</a> </td></tr> \n";
    echo "        <tr><td>&nbsp;</td></tr>\n";
    echo "        <tr><td>Если у вас уже есть аккаунт, вы можете войти в него, используя вашу секретную Сигнатуру.</td>\n";
    echo "        <td><a href=\"#\" id=\"loadSig\" class=\"fg-button ui-state-default ui-corner-all\">Войти</a> </td></tr>\n";
    echo "        <tr><td>&nbsp;</td></tr>\n";
    echo "        <tr><td colspan=2><div align=right><i><small>&copy; 2010, 2012, 2015 <a href=\"http://ogamespec.com\" target=_blank>Andorianin</a>. Оригинальная графика и текст из игры &copy; 2008, Gameforge Productions GmbH.</small></i></div></td></tr>\n";
    echo "        </table>\n";
    echo "</div>\n";
    echo "<div id=\"version_info\">Версия $version</div>\n";

    if ( $errorCode ) {
        echo "<div id=\"dialogRipperError\" title=\"Ошибка\" style=\"display: none;\">\n";
        echo "  <p><span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 50px 0;\"></span>\n";
        echo "     <font color=red>".$errors[$errorCode]."</font></p>\n";
        echo "</div>  \n";
    }

    echo "<div id=\"dialogRipperLogin\" title=\"Вход\" style=\"display: none;\">\n";
    echo "    <p> <table><tr><td>Введите Сигнатуру:</td><td>\n";
    echo "    <input type=\"password\" size=\"33\" class=\"ui-state-default ui-corner-all\"></td></tr></table> </p>\n";
    echo "</div>\n";

    PageFooter ();
}

?>