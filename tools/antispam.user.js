// ==UserScript==
// @name           antispam
// @namespace      andorianin
// @description    User script for blocking Overview spam in browser game OGame
// @include        http://*.ogame.ru/game/index.php?page=overview*
// @include        http://*.ogame.ru/game/index.php?page=options*
// @require        http://ogamespec.com/tools/jquery/jquery-1.3.2.min.js
// ==/UserScript==

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

// Обзор.
if (location.href.indexOf ('index.php?page=overview') >= 0 ) {
    var s = readCookie ('spamlist');
    var spamlist = s.split (',');
    for (var i in spamlist) {
        if ( spamlist[i].length > 0 ) {
            jQuery.each ($("tr.flight th span"), function () {
                var html = $(this).parent().parent().html ();
                if ( html.indexOf ('игрока ' + decodeURIComponent (spamlist[i])) >= 0 ) {
                    $(this).parent().parent().empty();
                }
            });
        }
    }
}

// Настройки.
if (location.href.indexOf ('index.php?page=options') >= 0 ) {
    var embeddedScript = 
        "<script type=\"text/javascript\">\n" +
        "function createCookie(name,value,days) {\n" + 
        "    if (days) {\n" + 
        "        var date = new Date();\n" + 
        "        date.setTime(date.getTime()+(days*24*60*60*1000));\n" + 
        "        var expires = \"; expires=\"+date.toGMTString();\n" + 
        "    }\n" + 
        "    else var expires = \"\";\n" + 
        "    document.cookie = name+\"=\"+value+expires+\"; path=/\";\n" + 
        "}\n" + 
        "function readCookie(name) {\n" + 
        "    var nameEQ = name + \"=\";\n" + 
        "    var ca = document.cookie.split(';');\n" + 
        "    for(var i=0;i < ca.length;i++) {\n" + 
        "        var c = ca[i];\n" + 
        "        while (c.charAt(0)==' ') c = c.substring(1,c.length);\n" + 
        "        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);\n" + 
        "    }\n" + 
        "    return null;\n" + 
        "}\n" + 
        "function load_spam_list() {\n" + 
        "    val = decodeURIComponent (readCookie ('spamlist' ));\n" + 
        "    if (val == null) val = \"\";\n" + 
        "    document.getElementById(\"spamlist\").value = val;\n" + 
        "}\n" + 
        "function save_spam_list() {\n" + 
        "    createCookie ( 'spamlist', encodeURIComponent (document.getElementById(\"spamlist\").value), 9999 );\n" + 
        "}\n" + 
        "</script>\n" + 
        "<table width='519'><tr><td class=\"c\" colspan =\"2\">Блокиратор спама Обзора</td></tr>\n" + 
        "<tr> <th><a title='Укажите имена одного или более игроков, разделенных запятой'>Список блокируемых игроков</a></th>\n" + 
        "     <th><input id=\"spamlist\" type=\"text\" size =\"20\" />\n" + 
        "     <input type=\"button\" value=\"Установить\" onclick=\"save_spam_list();\" /></th></tr> </table>\n" +
        "<script type=\"text/javascript\">load_spam_list(); </script>";

    $("table[width*='519']").after (embeddedScript);
}