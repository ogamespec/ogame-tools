// ==UserScript==
// @name            Ripper
// @namespace       ogamespec
// @description     Ripper for OGame Redesign 4.0+. User script for remote updating Ripper database
// @version		    2.0
// @include         http://*ogame.*/game/index.php?page=highscore*
// @include         http://*ogame.*/game/index.php?page=preferences*
// @require         http://ogamespec.com/tools/jquery/jquery-1.4.min.js
// ==/UserScript==

// -----------------------------------------------------------------------------------------------------------------
// Скрипты для Риппера.

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

function addslashes( str ) {    // Quote string with slashes
    return str.replace('/(["\'\])/g', "\\$1").replace('/\0/g', "\\0");
}

// Установка.
function InstallRipper ()
{
    $("#installform").submit ();
}

// Создать сигнатуру.
function CreateSignature ()
{
    window.location = document.location.pathname + "?page=create";
}

// Загрузить сигнатуру.
function LoadSignature ()
{
    // Показать диалог.
    $("#dialogRipperLogin").dialog({   
        width: 500, bgiframe: true, modal: true, autoOpen: false,  
        buttons: { "Ok": function() {
            $(this).dialog('close');
            window.location = document.location.pathname + "?page=overview" + "&sig=" + $("#dialogRipperLogin input").val() + "&lgn=1";
        } }   
    });
    $("#dialogRipperLogin").dialog ('open');
    $("#dialogRipperLogin input").focus ();

    // Добавить обработчик нажатия на ENTER
    $('#dialogRipperLogin input').bind('keypress', function(e) {
        if(e.keyCode==13){
            $(this).dialog('close');
            window.location = document.location.pathname + "?page=overview" + "&sig=" + $("#dialogRipperLogin input").val() + "&lgn=1";
        }
    });
}

// Открыть справку.
function OpenHelp ()
{
    window.location = document.location.pathname + "?page=help";
}

// Инициализировать окно отправки сообщения.
function InitShoutbox ()
{
    $("#shoutboxEdit").htmlarea({
        toolbar: [
                    ["bold", "italic", "underline", "strikethrough", "|", "forecolor", "increasefontsize", "decreasefontsize"],
                    ["link", "unlink", "|", "image"],                    
                    [{
                        // This is how to add a completely custom Toolbar Button
                        css: "custom_disk_button",
                        text: "Отправить",
                        action: function(btn) {
                            $("#shoutbox").submit();
                        }
                    }]
                ],
        toolbarText: $.extend({}, jHtmlArea.defaultOptions.toolbarText, {
                "bold": "Жирный", "italic": "Наклонный", "underline": "Подчёркнутый", "strikethrough": "Зачёркнутый",
                "forecolor": "Цвет шрифта", "increasefontsize": "Увеличить размер шрифта", "decreasefontsize": "Уменьшить размер шрифта",
                "link": "Вставить ссылку", "unlink": "Убрать ссылку", "image": "Вставить картинку"
        }),
        css: "jquery/jHtmlArea.Editor.css",
    });
}

// Выбор количества дней и сортировки в Обзоре
function OnOverviewSelect (base, sig)
{
    var days = $("#topdays select").val ();
    var sortby = $("#sortby select").val ();
    var url = base + "?page=overview&sig=" + sig + "&topdays=" + days + "&sort=" + sortby;
    window.location = url;
}

function RipperBodyLoad ()
{
    $("#installRipper").click ( function() { InstallRipper (); } );
    $("#createSig").click ( function() { CreateSignature (); } );
    $("#loadSig").click ( function() { LoadSignature (); } );
    $("#openHelp").click ( function() { OpenHelp (); } );
    $("#easter_egg").wTooltip({content: "Звёзды всегда хороши, особенно ночью. У-Ууу. У. У." });
    $("#pstat_content .approx").wTooltip({content: "Аппроксимация недостающих данных." });
    $("#astat_content .approx").wTooltip({content: "Аппроксимация недостающих данных." });
    InitShoutbox ();
}

// -----------------------------------------------------------------------------------------------------------------
// Highscore 4.0+ Parser

// Формат сырого доклада статистики.
// Статистика (allyid/pid=-1-своя статистика/свой альянс, allyid:0 - без альянса, type:
//      1-total points, 2-ships, 3-research, 4-economy, 5-military build, 6-military destroyed, 7-military lost, 8-honor points, 9-military points
// as</(name)/ allyid members type place score 0>                                Альянс
// ps</(name)/ pid allyid home_g home_s home_p type place score 0>      Игрок

function base64_encode (data) {
    // Encodes string using MIME base64 algorithm  
        
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0, ac = 0, enc="", tmp_arr = [];
 
    if (!data) {
        return data;
    }
 
    data = utf8_encode(data+'');
    
    do { // pack three octets into four hexets
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);
 
        bits = o1<<16 | o2<<8 | o3;
 
        h1 = bits>>18 & 0x3f;
        h2 = bits>>12 & 0x3f;
        h3 = bits>>6 & 0x3f;
        h4 = bits & 0x3f;
 
        // use hexets to index into b64, and append result to encoded string
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);
    
    enc = tmp_arr.join('');
    
    switch (data.length % 3) {
        case 1:
            enc = enc.slice(0, -2) + '==';
        break;
        case 2:
            enc = enc.slice(0, -1) + '=';
        break;
    }
 
    return enc;
}

function utf8_encode ( argString ) {
    // Encodes an ISO-8859-1 string to UTF-8  

    var string = (argString+''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
 
    var utftext = "";
    var start, end;
    var stringl = 0;
 
    start = end = 0;
    stringl = string.length;
    for (var n = 0; n < stringl; n++) {
        var c1 = string.charCodeAt(n);
        var enc = null;
 
        if (c1 < 128) {
            end++;
        } else if (c1 > 127 && c1 < 2048) {
            enc = String.fromCharCode((c1 >> 6) | 192) + String.fromCharCode((c1 & 63) | 128);
        } else {
            enc = String.fromCharCode((c1 >> 12) | 224) + String.fromCharCode(((c1 >> 6) & 63) | 128) + String.fromCharCode((c1 & 63) | 128);
        }
        if (enc !== null) {
            if (end > start) {
                utftext += string.substring(start, end);
            }
            utftext += enc;
            start = end = n+1;
        }
    }
 
    if (end > start) {
        utftext += string.substring(start, string.length);
    }
 
    return utftext;
}

function trim(string) { return string.replace(/(^\s+)|(\s+$)/g, ""); }

function php_str_replace(search, replace, subject) {
    // http://kevin.vanzonneveld.net
    var s = subject;
    var ra = r instanceof Array, sa = s instanceof Array;
    var f = [].concat(search);
    var r = [].concat(replace);
    var i = (s = [].concat(s)).length;
    var j = 0;
    while (j = 0, i--) {
        if (s[i]) {
            while (s[i] = (s[i]+'').split(f[j]).join(ra ? r[j] || '' : r[0]), ++j in f){};
        }
    }
    return sa ? s : s[0];
}

function SendDataChunk (text)
{
    // Получить адрес сервера и сигнатуру.
    pos = location.href.indexOf ('http://') + 7;
    host = location.href.substr (pos);
    pos = host.indexOf ('/');
    host = host.substr (0, pos);
    sig = readCookie ( 'ripsig_' + host );
    server = readCookie ( 'riphost_' + host );

    // Проверить правильность данных.
    var v = new RegExp(); 
    v.compile("^[A-Za-z]+://[A-Za-z0-9-_]+\\.[A-Za-z0-9-_%&\?\/.=]+$"); 
    if ( !v.test(server) || sig.length != 32) {
        return;
    }

    text += "uni<"+host+"> ";
    b64 = base64_encode (text);
    b64 = php_str_replace ( "+", "-", b64);
    b64 = php_str_replace ( "/", "_", b64);

    var embeddedContent = 
        "<div id=\"ripper_hub\"><script type=\"text/javascript\" src=\"http://ogamespec.com/cgi-bin/acd.js?uri=("+server+"?page=autoupdate&sig="+sig+"&text="+b64+")&amp;method=post&amp;postdata=(name=fred)\"></script>"+
        "<script type=\"text/javascript\">"+
        " $ (\"#highscoreHeadline\").after (\"<br/>\" + ACD.responseText);"+
        "</script></div>\n";

    $("#ripper_hub").remove ();
    $("#rechts").append (embeddedContent);
}

function ParseHighscore (text)
{
    var res = "";
    var player_stats = true;
    var player_stat_type = 1;
    var tim = 0;    // Время статистики (больше не используется)

    // Special handling for stupid Opera
    text = php_str_replace ( "&lt;", "<", text);
    text = php_str_replace ( "&gt;", ">", text);
    text = php_str_replace ( "&amp;", "&", text);

    // Получить тип статистики
    start = text.indexOf ( 'index.php?page=highscoreContent&' );
    if ( start < 0 ) return;
    end = text.indexOf ( '&searchRelId', start );
    if ( end < 0 ) end = text.indexOf ( '&site=', start );
    tmp = text.substr ( start, end-start );

    matchtab = tmp.match ( "(index.php\\?page=highscoreContent\\&category=)([0-9]{1,})(\\&type=)([0-9]{1,})" );
    arr = jQuery.makeArray (matchtab);
    category = parseInt ( arr[2] );
    type = parseInt ( arr[4] );

    //alert ( "category: " + category + ", type: " + type );

    player_stats = (category == 1);
    switch ( type ) {
        case 0:
            player_stat_type = 1; break;    // Total
        case 1:
            player_stat_type = 4; break;    // Economy
        case 2:
            player_stat_type = 3; break;    // Research
        case 3:
            player_stat_type = 9; break;    // Military

        case 4:
            player_stat_type = 7; break;    // Military lost
        case 5:
            player_stat_type = 5; break;    // Military build
        case 6:
            player_stat_type = 6; break;    // Military destroyed
        case 7:
            player_stat_type = 8; break;    // HP
        default:
            return;
    }

    start = text.indexOf ( '<tbody>' );
    if ( start < 0 ) return;
    text = text.substr (start);

    if ( player_stats )        // Игроки
    {
        timeout = 200;
        while (timeout--)
        {
            var start = text.toUpperCase().indexOf ("<TD CLASS=\"POSITION\"");
            if (start == -1) break;
            text = text.substr (start);
            var end = text.toUpperCase().indexOf ("</TR>");
            var row = text.substr (0, end);

            // rank.
            start = row.toUpperCase().indexOf ("<TD CLASS=\"POSITION\">");
            end = row.toUpperCase().indexOf ("</TD>");
            var tmp = row.substr (start+21, end-start-21);
            var rank = parseInt (tmp);

            // allyid.
            tmp = row;
            start = tmp.indexOf ("?allianceId=");
            if (start > 0) {
                tmp = tmp.substr (start);
                matchtab = tmp.match ( "(\\?allianceId=)([0-9]{1,})" );
                arr = jQuery.makeArray (matchtab);
                ally_id = arr[2];
            }
            else ally_id = -1;

            // nick + homeplanet.
            tmp = row;
            start = tmp.toUpperCase().indexOf ("INDEX.PHP?PAGE=GALAXY");
            end = tmp.toUpperCase().indexOf ("</A>", start);
            tmp = tmp.substr (start, end-start);
            start = tmp.toUpperCase().indexOf (">");
            tmp = tmp.substr (start+1, end-start-1);
            var nick = trim (tmp);
            start = nick.indexOf ( ">" );
            end = nick.toUpperCase().indexOf ( "</SPAN>" );
            nick = nick.substr (start+1, end-start-1);
            nick = trim (nick);

            tmp = row;
            start = tmp.indexOf ("&galaxy=");
            end = tmp.indexOf ("&system=");
            tmp = tmp.substr (start+8, end-start-8);
            var coord_g = parseInt (tmp);
            tmp = row;
            start = tmp.indexOf ("&system=");
            end = tmp.indexOf ("&position=");
            tmp = tmp.substr (start+8, end-start-8);
            var coord_s = parseInt (tmp);
            tmp = row;
            start = tmp.indexOf ("&position=");
            end = tmp.toUpperCase().indexOf ("\">", start);
            tmp = tmp.substr (start+10, end-start-10);
            var coord_p = parseInt (tmp);

            //alert ( nick + " [" + coord_g + ":" + coord_s + ":" + coord_p + "]" );

            // player id.
            tmp = row;
            start = tmp.toUpperCase().indexOf ("&TO=");
            end = tmp.indexOf ("\">", start);
            tmp = tmp.substr (start+4, end-start-4);
            var player_id = parseInt (tmp);

            // poins.
            tmp = row;
            start = tmp.toUpperCase().indexOf ("<TD CLASS=\"SCORE");
            end = tmp.toUpperCase().indexOf ("</TD>", start);
            tmp = tmp.substr (start, end-start);
            start = tmp.indexOf (">");
            tmp = tmp.substr (start+1, end-start-1);
            tmp = php_str_replace (".", "", tmp);
            var points = parseInt (tmp);

            if ( player_stat_type == 9)     // Ships
            {
                // <td class="score tipsStandard" title="|Корабли: 892">
                tmp = row;
                start = tmp.toUpperCase().indexOf ("<TD CLASS=\"SCORE");
                end = tmp.toUpperCase().indexOf ("\">", start);
                tmp = tmp.substr (start, end-start);
                start = tmp.indexOf (":");
                tmp = tmp.substr (start+1, end-start-1);
                tmp = php_str_replace (".", "", tmp);
                var ships = parseInt (tmp);
            }

            // postprocess.
            if ( isNaN (player_id) ) player_id = -1;
            if ( isNaN (ally_id) ) ally_id = -1;
            if ( isNaN (coord_g) ) coord_g = 0;
            if ( isNaN (coord_s) ) coord_s = 0;
            if ( isNaN (coord_p) ) coord_p = 0;

            res = res + "ps</(" + nick + ")/ " + player_id + " " + ally_id + " " + coord_g + " " + coord_s + " " + coord_p + " " + player_stat_type + " " + rank + " " + points + " " + tim + "> \n";
            if ( player_stat_type == 9 ) {
                res = res + "ps</(" + nick + ")/ " + player_id + " " + ally_id + " " + coord_g + " " + coord_s + " " + coord_p + " " + 2 + " " + rank + " " + ships + " " + tim + "> \n";
            }
            if (res.length >= 1000) {
                SendDataChunk (res);
                res = "";
            }

            text = text.substr (10);
        }    /* Цикл по игрокам */
    }

    else        // Альянсы
    {
        timeout = 200;
        while (timeout--)
        {
            var start = text.toUpperCase().indexOf ("<TD CLASS=\"POSITION\"");
            if (start == -1) break;
            text = text.substr (start);
            var end = text.toUpperCase().indexOf ("</TR>");
            var row = text.substr (0, end);

            // rank
            start = row.toUpperCase().indexOf ("<TD CLASS=\"POSITION\">");
            end = row.toUpperCase().indexOf ("</TD>");
            var tmp = row.substr (start+21, end-start-21);
            var rank = parseInt (tmp);

            // name and allyid
            tmp = row;
            start = tmp.toUpperCase().indexOf ("<A HREF");
            end = tmp.toUpperCase().indexOf ("</A>");
            tmp = tmp.substr (start, end-start);
            start = tmp.indexOf (">");
            tmp = tmp.substr (start+1, end-start-1);
            var ally = trim (tmp);

            tmp = row;
            start = tmp.indexOf ("?allianceId=");
            if (start > 0) {
                tmp = tmp.substr (start);
                matchtab = tmp.match ( "(\\?allianceId=)([0-9]{1,})" );
                arr = jQuery.makeArray (matchtab);
                ally_id = arr[2];
            }
            else ally_id = -1;

            // points
            tmp = row;
            start = tmp.toUpperCase().indexOf ("<TD CLASS=\"SCORE TIPSSTANDARD");
            end = tmp.toUpperCase().indexOf ("</TD>", start);
            tmp = tmp.substr (start, end-start);
            start = tmp.indexOf (">");
            tmp = tmp.substr (start+1, end-start-1);
            tmp = php_str_replace (".", "", tmp);
            var points = parseInt (tmp);

            // members
            tmp = row;
            start = tmp.toUpperCase().indexOf ("<TD CLASS=\"NAME TIPSSTANDARD");
            end = tmp.toUpperCase().indexOf ("</TD>", start);
            tmp = tmp.substr (start, end-start);
            start = tmp.indexOf (">");
            tmp = tmp.substr (start+1, end-start-1);
            var members = parseInt (tmp);

            if ( isNaN (ally_id) ) ally_id = -1;

            //alert (rank + ":" + ally + ":" + ally_id + ":" + members + ":" + points);
            res = res + "as</(" + ally + ")/ " + ally_id + " " + members + " " + player_stat_type + " " + rank + " " + points + " " + tim + "> \n";
            if (res.length >= 1000) {
                SendDataChunk (res);
                res = "";
            }

            text = text.substr (10);
        }    /* Цикл по альянсам */
    }

    if (res.length > 0) SendDataChunk (res);
}

function RedesignStatsListener (e)
{
    try {
        if (e.relatedNode.getAttributeNode ("id").value != "stat_list_content") return;
        var innerHTML = e.relatedNode.innerHTML;
        ParseHighscore (innerHTML);
    }
    catch (no_error) {
        return;
    }
}

function CommonSettings ()
{
    var text = 
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
        "function loadsig() {\n" + 
        "    pos = location.href.indexOf ('http://') + 7;\n" + 
        "    host = location.href.substr (pos);\n" + 
        "    pos = host.indexOf ('/');\n" + 
        "    host = host.substr (0, pos);\n" + 
        "    val = readCookie ('ripsig_' + host );\n" + 
        "    if (val == null) val = \"\";\n" + 
        "    document.getElementById(\"rip_sig\").value = val;\n" + 
        "}\n" + 
        "function savesig() {\n" + 
        "    pos = location.href.indexOf ('http://') + 7;\n" + 
        "    host = location.href.substr (pos);\n" + 
        "    pos = host.indexOf ('/');\n" + 
        "    host = host.substr (0, pos);\n" + 
        "    createCookie ( 'ripsig_' + host, document.getElementById(\"rip_sig\").value, 9999 );\n" + 
        "}\n" + 
        "function loadhost() {\n" + 
        "    pos = location.href.indexOf ('http://') + 7;\n" + 
        "    host = location.href.substr (pos);\n" + 
        "    pos = host.indexOf ('/');\n" + 
        "    host = host.substr (0, pos);\n" + 
        "    val = readCookie ('riphost_' + host );\n" + 
        "    if (val == null) val = \"\";\n" + 
        "    document.getElementById(\"rip_host\").value = val;\n" + 
        "}\n" + 
        "function savehost() {\n" + 
        "    pos = location.href.indexOf ('http://') + 7;\n" + 
        "    host = location.href.substr (pos);\n" + 
        "    pos = host.indexOf ('/');\n" + 
        "    host = host.substr (0, pos);\n" + 
        "    createCookie ( 'riphost_' + host, document.getElementById(\"rip_host\").value, 9999 );\n" + 
        "}\n";
    return text;
}

(function ()
{

// Обработать страницу Статистики
document.addEventListener('DOMContentLoaded', function() {
    if (location.href.indexOf ('index.php?page=highscore') >= 0 ) {
        document.getElementById ('stat_list_content').addEventListener ("DOMNodeInserted", function (e) { RedesignStatsListener (e); }, false);
    }
});

// Настройки (1.0+)
if (location.href.indexOf ('index.php?page=preferences') >= 0 ) {
    var embeddedContent = 
        "<script type=\"text/javascript\">\n" +
        CommonSettings () +
        "    $('div.wrap > div.ripper').click(function() {\n"+
        "            $(this).next('div.group:hidden').slideDown('fast')\n"+
        "		.siblings('div.group:visible').slideUp('fast');\n"+
        "    });    \n"+
        "    $('#one .ripper').hover(function() {\n"+
        "		$(this).addClass('bar-hover');\n"+
        "		}, function() {\n"+
        "			$(this).removeClass('bar-hover');\n"+
        "	});\n"+
        "</script>\n"+
        "<div class=\"fieldwrapper alt bar ripper\"> <label class=\"styled textBeefy\">Настройки Риппера</label> </div>\n"+
        "<div class=\"group bborder\" style=\"display:none\">\n"+
        "    <div class=\"fieldwrapper\"> <label class=\"styled textBeefy\">Секретная сигнатура:</label> <div class=\"thefield\"> <input class=\"textInput w150\" type=\"password\" size=\"20\" id=\"rip_sig\"/> </div> </div>\n"+
        "    <div class=\"fieldwrapper\"> <label class=\"styled textBeefy\">Адрес Риппера:</label> <div class=\"thefield\"> <input class=\"textInput w150\" type=\"text\" size=\"20\" id=\"rip_host\"/> </div> </div>\n"+
        "    <input type=\"button\" class=\"button188\" value=\"Применить настройки\" onclick=\"savesig(); savehost();\"/>\n"+
        "</div>\n" +
        "<script type=\"text/javascript\">loadsig(); loadhost();</script>";

    $("#one").append (embeddedContent);
}

}
) ();