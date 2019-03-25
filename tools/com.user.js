// ==UserScript==
// @name           com
// @namespace      andorianin
// @description    User script for updating OGame Commander
// @include        http://*.ogame.ru/game/index.php?page=galaxy*
// @include        http://*.ogame.ru/game/index.php?page=statistics*
// @include        http://*.ogame.ru/game/index.php?page=messages*
// @include        http://*.ogame.ru/game/index.php?page=options*
// @require        http://ogamespec.com/tools/jquery/jquery-1.3.2.min.js
// @require        http://ogamespec.com/tools/jquery/jquery.jgrowl_minimized.js
// ==/UserScript==

// Use Unicode text editor.


// Скрипт для Командира.
// Сюда входят процедуры обработки страниц, а также скрипт для GreaseMonkey для автоматического обновления Командира (наподобии GalaxyPlugin)

// Формат "сырого" текста для базы:
//
// Галактика:
// pd < time g s p act >                                                    Уничтоженная планета
// pe < time g s p >                                                         Пустое место
// pl < time g s p act typ (name) diam temp dm dk owner >    Обычная планета или луна
// 
// Типы планет:
// 1-3: trockenplanet    типы 101...110
// 4-6: dschjungelplanet  типы 201...210
// 7-9: normaltempplanet типы 301...307
// 10-12: wasserplanet типы 401...409
// 13-15: eisplanet типы 501...510
// Луна тип 0.
//
// Статистика (allyid/pid=-1-своя статистика/свой альянс, allyid:0 - без альянса, type: 1-очки, 2-флот, 3-исследования):
// as < /(name)/ allyid members type place score date >                                Альянс
// ps < /(name)/ pid allyid home_g home_s home_p type place score date >      Игрок
//
// Шпионский доклад:
// sp < /(player)/ (planet) g s p moon level m k d e counter o1 .... o503 date >

// Глобальные переменные.

var ptypes = { trockenplanet: 100, dschjungelplanet: 200, normaltempplanet: 300, wasserplanet: 400, eisplanet: 500 };

// Символьные описания объектов
var desc = new Array ();
desc[202] = "Малый транспорт";
desc[203] = "Большой транспорт";
desc[204] = "Лёгкий истребитель";
desc[205] = "Тяжёлый истребитель";
desc[206] = "Крейсер";
desc[207] = "Линкор";
desc[208] = "Колонизатор";
desc[209] = "Переработчик";
desc[210] = "Шпионский зонд";
desc[211] = "Бомбардировщик";
desc[212] = "Солнечный спутник";
desc[213] = "Уничтожитель";
desc[214] = "Звезда смерти";
desc[215] = "Линейный крейсер";

desc[401] = "Ракетная установка";
desc[402] = "Лёгкий лазер";
desc[403] = "Тяжёлый лазер";
desc[404] = "Пушка Гаусса";
desc[405] = "Ионное орудие";
desc[406] = "Плазменное орудие";
desc[407] = "Малый щитовой купол";
desc[408] = "Большой щитовой купол";
desc[502] = "Ракета-перехватчик";
desc[503] = "Межпланетная ракета";

desc[1] = "Рудник по добыче металла";
desc[2] = "Рудник по добыче кристалла";
desc[3] = "Синтезатор дейтерия";
desc[4] = "Солнечная электростанция";
desc[12] = "Термоядерная электростанция";
desc[14] = "Фабрика роботов";
desc[15] = "Фабрика нанитов";
desc[21] = "Верфь";
desc[22] = "Хранилище металла";
desc[23] = "Хранилище кристалла";
desc[24] = "Ёмкость для дейтерия";
desc[31] = "Исследовательская лаборатория";
desc[33] = "Терраформер";
desc[34] = "Склад альянса";
desc[41] = "Лунная база";
desc[42] = "Сенсорная фаланга";
desc[43] = "Ворота";
desc[44] = "Ракетная шахта";

desc[106] = "Шпионаж";
desc[108] = "Компьютерная технология";
desc[109] = "Оружейная технология";
desc[110] = "Щитовая технология";
desc[111] = "Броня космических кораблей";
desc[113] = "Энергетическая технология";
desc[114] = "Гиперпространственная технология";
desc[115] = "Реактивный двигатель";
desc[117] = "Импульсный двигатель";
desc[118] = "Гиперпространственный двигатель";
desc[120] = "Лазерная технология";
desc[121] = "Ионная технология";
desc[122] = "Плазменная технология";
desc[123] = "Межгалактическая исследовательская сеть";
desc[124] = "Экспедиционная технология";
desc[199] = "Гравитационная технология";

var fleetmap = new Array (202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215 );
var defmap = new Array (401, 402, 403, 404, 405, 406, 407, 408, 502, 503 );
var buildmap = new Array (1, 2, 3, 4, 12, 14, 15, 21, 22, 23, 24, 31, 33, 34, 41, 42, 43, 44);
var techmap = new Array (106, 108, 109, 110, 111, 113, 114, 115, 117, 118, 120, 121, 122, 123, 124, 199);

// **************************************************************************************
// Парсер.

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

// Галактика.
function ParseGalaxy ()
{
    var timenow, coord_g, coord_s, coord_p, typ, pname, dm, dk, mname, mdiam, mtemp;
    var res = "";

    jQuery.each ( $("#parser table[width=569] tr"), function (tri, trval) {        // Перечислить все таблицы шириной "569"
        ss = $(trval).text ();
        matchtab = ss.match ( "Солнечная система ([0-9]):([0-9]{1,3})" );
        arr = jQuery.makeArray (matchtab);
        if ( typeof (arr[1]) != "undefined" ) {    // Синхронизировать на начало сс
            coord_g = arr[1]; coord_s = arr[2]; coord_p = 1;
            //res += "<Галактика " + coord_g + ":" + coord_s + ">\n";
            d = new Date ();
            timenow = parseInt (d.getTime() / 1000.0);
        }
        if ($(this).children("th").length == 8) {
            jQuery.each ( $(this).children("th"), function (i, val) {    // Перечислить все столбцы планеты
                html = $(val).html ();
                html = php_str_replace ( "&lt;", "<", html);
                html = php_str_replace ( "&gt;", ">", html);
                if ( i == 1) {    // G:S:P, тип планеты
                    pos = html.indexOf ( "planeten/small/s_" );
                    if (pos != -1) {
                        planeten = html.substr (pos, 50);
                        matchtab = planeten.match ( "planeten/small/s_([a-z]{3,})([0-9]{2}).jpg" );
                        arr = jQuery.makeArray (matchtab);
                        typ = ptypes[arr[1]] + parseInt(arr[2]);
                        pos = html.indexOf ( "a href=index.php?page=flotten1&amp;session=" ); gsp = html.substr (pos, 100);
                        matchtab = gsp.match ( "galaxy=([0-9])&amp;system=([0-9]{1,3})&amp;planet=([0-9]{1,2})" );
                        arr = jQuery.makeArray (matchtab);
                        coord_g = arr[1]; coord_s = arr[2]; coord_p = arr[3];
                    }
                    else { typ = 0; activity = 0; }
                }
                else if ( i == 2) {    // Имя планеты, активность
                    pname = trim($(val).text());
                    pos = pname.indexOf ( "(");
                    if (pos != -1) {
                        activ = trim (pname.substr (pos));
                        pname = trim (pname.substr (0, pos));
                        if ( activ.indexOf ( "*" ) != -1 ) activity = 0;
                        else {
                            matchtab = activ.match ( "([0-9.]{1,}) min" );
                            arr = jQuery.makeArray (matchtab);
                            activity = arr[1];
                        }
                    }
                    else activity = "60";
                }
                else if ( i == 3) {    // Имя луны, диаметр, температура
                    if ( html.indexOf ("planeten/small/s_mond.jpg") != -1 ) {
                        matchtab = html.match ( "(>Луна[\\s]+)([\\w|\\s|\\W]+)([\\[]([0-9]:[0-9]{1,3}:[0-9]{1,2}))");
                        arr = jQuery.makeArray (matchtab); mname = trim(arr[2]);
                        matchtab = html.match ( "<tr><th>размер:</td><th>([0-9.]{1,})</td></tr>" );
                        arr = jQuery.makeArray (matchtab);  mdiam = php_str_replace ( ".", "", arr[1]);
                        matchtab = html.match ( "<tr><th>температура:</td><th>([-0-9]{1,})</td></tr>" );
                        arr = jQuery.makeArray (matchtab);  mtemp = parseInt (arr[1]);
                    }
                    else { mname = ""; mdiam = mtemp = 0; }
                }
                else if ( i == 4) {    // Поля обломков (металл, кристалл)
                    pos = html.indexOf ( "<tr><th>металл:</th><th>" ); debris = html.substr (pos, 50);
                    matchtab = debris.match ( "<tr><th>металл:</th><th>([0-9.]{1,})</th></tr>" );
                    arr = jQuery.makeArray (matchtab);
                    dm = php_str_replace ( ".", "", arr[1]); if ( typeof (dm) == "undefined" ) dm = 0;
                    pos = html.indexOf ( "<tr><th>кристалл:</th><th>" ); debris = html.substr (pos, 50);
                    matchtab = debris.match ( "<tr><th>кристалл:</th><th>([0-9.]{1,})</th></tr>" );
                    arr = jQuery.makeArray (matchtab);
                    dk = php_str_replace ( ".", "", arr[1]); if ( typeof (dk) == "undefined" ) dk = 0;
                }
                else if ( i == 5) {    // ID пользователя (0 - сам)
                    pos = html.indexOf ( "<a href=index.php?page=writemessages" );
                    if (pos != -1) {
                        message = html.substr (pos, 100);
                        matchtab = message.match ( "&amp;messageziel=([0-9]{1,})" );
                        arr = jQuery.makeArray (matchtab);
                        user_id = arr[1];
                    }
                    else { user_id = 0; }
                }
            });
            if ( typ ) {            // Добавить планету.
                res += "pl < " + timenow + " " + coord_g + " " + coord_s + " " + coord_p + " " + activity + " " + typ + " (" + pname + ") 0 0 " + dm + " " + dk + " " + user_id + " >\n";
                if ( mdiam ) {    // Добавить луну.
                    res += "pl < " + timenow + " " + coord_g + " " + coord_s + " " + coord_p + " " + activity + " 0 (" + mname + ") " + mdiam + " " + mtemp + " 0 0 " + user_id + " >\n";
                }
            }
            else if (pname.length) {    // Уничтоженная планета
                res += "pd < " + timenow + " " + coord_g + " " + coord_s + " " + coord_p + " " + activity + " >\n";                
            }
            else res += "pe < " + timenow + " " + coord_g + " " + coord_s + " " + coord_p + " >\n";                 // Пустое место.
            coord_p++;
        }
    } );

    return res;
}

// Статистика.
function ParseStat (text)
{
    var res = "";
    var player_stats = true;
    var player_stat_type = 1;
    var global_timeout = 100;

    text = php_str_replace ( "&lt;", "<", text);
    text = php_str_replace ( "&gt;", ">", text);
    text = php_str_replace ( "&amp;", "&", text);

    while (global_timeout--)
    {
    // Получить дату статистики
    // Статистика (по состоянию на: 2009-06-25, 11:35:37)
    start = text.indexOf ("Статистика (по состоянию на:");
    if ( start == -1) break;
    var date = text.substr (start+29, 20);
    var d = new Date (date);
    var tim = parseInt (d.getTime () / 1000.0);

    matchtab = date.match ( "([0-9]{1,})-([0-9]{1,2})-([0-9]{1,2}), ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})" );
    arr = jQuery.makeArray (matchtab);
    d = new Date ();
    d.setYear (parseInt(arr[1]));
    d.setMonth (parseInt(arr[2])-1);
    d.setDate (parseInt(arr[3]));
    d.setHours (parseInt(arr[4]));
    d.setMinutes (parseInt(arr[5]));
    d.setSeconds (parseInt(arr[6]));
    tim = parseInt (d.getTime () / 1000.0);

    // Получить тип статистики Альянс/Игрок.
    /*
          <option value="player" selected>Игрок</option>
          <option value="ally" >Альянс</option>
    */
    start = text.indexOf ("<option value=\"player\"");
    end = text.indexOf ("Альянс</option>");
    var tmp = text.substr (start, end-start);
    if (tmp.indexOf ("value=\"player\" selected") >= 0 ) player_stats = true;
    else player_stats = false;

    // Получить тип статистики Очки/Флоты/Иссл.
    /*
          <option value="ressources" selected>Очкам</option>
          <option value="fleet" >Флотам</option>
          <option value="research" >Исследованиям</option>
    */
    start = text.indexOf ("<option value=\"ressources\"");
    end = text.indexOf ("Исследованиям</option>");
    var tmp = text.substr (start, end-start);
    if (tmp.indexOf ("value=\"ressources\" selected") >= 0) player_stat_type = 1;
    if (tmp.indexOf ("value=\"fleet\" selected") >= 0 ) player_stat_type = 2;
    if (tmp.indexOf ("value=\"research\" selected") >= 0 ) player_stat_type = 3;

    if (player_stats)
    {
        enduser = text.indexOf ("<!-- end user -->");
        endtext = text;
        text = text.substr (0, enduser);
        timeout = 200;

        while (timeout--)
        {
            var start = text.indexOf ("<!-- rank -->");
            if (start == -1) break;
            text = text.substr (start);
            var end = text.indexOf ("</tr>");
            var row = text.substr (0, end);

            // rank.
            start = row.indexOf ("<th>");
            end = row.indexOf ("&");
            var tmp = row.substr (start+4, end-start-4);
            var rank = parseInt (tmp);

            // nick + homeplanet.
            start = row.indexOf ("<!-- nick -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("<a href");
            end = tmp.indexOf ("</a>");
            tmp = tmp.substr (start, end-start);
            start = tmp.indexOf (">");
            tmp = tmp.substr (start+1, end-start-1);
            var nick = trim (tmp);

            start = row.indexOf ("<!-- nick -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("&p1=");
            end = tmp.indexOf ("&p2=");
            tmp = tmp.substr (start+4, end-start-4);
            var coord_g = parseInt (tmp);
            start = row.indexOf ("<!-- nick -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("&p2=");
            end = tmp.indexOf ("&p3=");
            tmp = tmp.substr (start+4, end-start-4);
            var coord_s = parseInt (tmp);
            start = row.indexOf ("<!-- nick -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("&p3=");
            end = tmp.indexOf ("\" style='color");
            tmp = tmp.substr (start+4, end-start-4);
            var coord_p = parseInt (tmp);

            // player id.
            start = row.indexOf ("<!--  message-icon -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("&messageziel=");
            end = tmp.indexOf ("\">");
            tmp = tmp.substr (start+13, end-start-13);
            tmp = php_str_replace (".", "", tmp);
            var player_id = parseInt (tmp);

            // allyid.
            start = row.indexOf ("<!--  ally -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("?allyid=");
            if (start > 0) {
                tmp = tmp.substr (start);
                matchtab = tmp.match ( "(\\?allyid=)([0-9]{1,})" );
                arr = jQuery.makeArray (matchtab);
                ally_id = arr[2];
            }
            else ally_id = -1;

            // poins.
            start = row.indexOf ("<!-- points -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("<th>");
            end = tmp.indexOf ("</th>");
            tmp = tmp.substr (start+4, end-start+4);
            tmp = php_str_replace (".", "", tmp);
            var points = parseInt (tmp);

            // postprocess.
            if ( isNaN (player_id) ) player_id = -1;
            if ( isNaN (ally_id) ) ally_id = -1;
            if ( isNaN (coord_g) ) coord_g = 0;
            if ( isNaN (coord_s) ) coord_s = 0;
            if ( isNaN (coord_p) ) coord_p = 0;

            //alert (rank + ":" + nick + ":" + player_id + ":[" + coord_g + ":" + coord_s + ":" + coord_p + "]:" + ally + ":" + ally_id + ":" +points);
            res += "ps < /(" + nick + ")/ " + player_id + " " + ally_id + " " + coord_g + " " + coord_s + " " + coord_p + " " + player_stat_type + " " + rank + " " + points + " " + tim + " >\n";

            text = text.substr (10);
        /* Цикл по игрокам */ }

        text = endtext.substr (enduser+10);
    }
    else
    {
        enduser = text.indexOf ("<!-- end ally -->");
        endtext = text;
        text = text.substr (0, enduser);
        timeout = 200;

        while (timeout--)
        {
            var start = text.indexOf ("<!-- rank -->");
            if (start == -1) break;
            text = text.substr (start);
            var end = text.indexOf ("</tr>");
            var row = text.substr (0, end);

            // rank.
            start = row.indexOf ("<th>");
            end = row.indexOf ("&");
            var tmp = row.substr (start+4, end-start-4);
            var rank = parseInt (tmp);

            // ally + allyid.
            start = row.indexOf ("<!--  name -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("<a href");
            end = tmp.indexOf ("</a>");
            tmp = tmp.substr (start, end-start);
            start = tmp.indexOf (">");
            tmp = tmp.substr (start+1, end-start-1);
            var ally = trim (tmp);

            start = row.indexOf ("<!--  name -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("?allyid=");
            if (start > 0) {
                tmp = tmp.substr (start);
                matchtab = tmp.match ( "(\\?allyid=)([0-9]{1,})" );
                arr = jQuery.makeArray (matchtab);
                ally_id = arr[2];
            }
            else ally_id = -1;

            // amount members.
            start = row.indexOf ("<!-- amount members -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("<th>");
            end = tmp.indexOf ("</th>");
            tmp = tmp.substr (start+4, end-start+4);
            tmp = php_str_replace (".", "", tmp);
            var members = parseInt (tmp);

            // poins.
            start = row.indexOf ("<!-- points -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("<th>");
            end = tmp.indexOf ("</th>");
            tmp = tmp.substr (start+4, end-start+4);
            tmp = php_str_replace (".", "", tmp);
            var points = parseInt (tmp);

            if ( isNaN (ally_id) ) ally_id = -1;

            //alert (rank + ":" + ally + ":" + ally_id + ":" + members + ":" + points);
            res += "as < /(" + ally + ")/ " + ally_id + " " + members + " " + player_stat_type + " " + rank + " " + points + " " + tim + " >\n";

            text = text.substr (10);
        /* Цикл по альянсам */ }

        text = endtext.substr (enduser+10);
    }
    
    /* Цикл по страницам */ }
    return res;
}

// Шпионские доклады.
function ParseSpy ()
{
    var res = "";
    var leveldesc = new Array ( "Флоты", "Оборона", "Постройки", "Исследования" );
    var map = new Array ( fleetmap, defmap, buildmap, techmap );
    s = $("#parser").text();
    s = php_str_replace (":", " ", s);
    s = php_str_replace (".", "", s);
    timeout = 100;

    while (timeout--)
    {
        var spy = new Array ();
        spy['counter'] = spy['level'] = 0;
        
        pos = s.indexOf ( "Сырьё на" );
        if ( pos == -1 ) break;
        s = s.substr ( pos );

        pos = s.indexOf ( "Атака" );
        t = s.substr ( 0, pos );

        // Заголовок доклада
        pos = t.indexOf ( "(Игрок"); head = t.substr ( 0, pos+50 );
        matchtab = head.match ( "(Сырьё на[\\s]+)([\\w|\\s|\\W]+)([\\[])([0-9]) ([0-9]{1,3}) ([0-9]{1,})([\\]]) ([\\(])Игрок '(([\\w|\\s|\\W]+))'([\\)])" + 
                                            " на ([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}) ([0-9]{1,2}) ([0-9]{1,2})" );
        arr = jQuery.makeArray (matchtab);
        pname = trim(arr[2]);
        pos = pname.indexOf ("(Луна)");
        if (pos != -1) {
            pname = trim(pname.substr (0, pos));
            spy['moon'] = 1;
        }
        else spy['moon'] = 0;
        spy['planet'] = pname;
        spy['player'] = arr[9];
        spy['g'] = parseInt(arr[4]); spy['s'] = parseInt(arr[5]); spy['p'] = parseInt(arr[6]);
        d = new Date ();
        d.setMonth (parseInt(arr[12])-1);
        d.setDate (parseInt(arr[13]));
        d.setHours (parseInt(arr[14]));
        d.setMinutes (parseInt(arr[15]));
        d.setSeconds (parseInt(arr[16]));
        spy['date'] = parseInt (d.getTime () / 1000.0);

        // Ресурсы.
        pos = t.indexOf ( "металла" ); mkd = t.substr ( pos );
        matchtab = mkd.match ("(металла[\\s]+)([0-9]{1,})");
        arr = jQuery.makeArray (matchtab);
        spy['m'] = parseInt (arr[2]);
        pos = t.indexOf ( "кристалла" ); mkd = t.substr ( pos );
        matchtab = mkd.match ("(кристалла[\\s]+)([0-9]{1,})");
        arr = jQuery.makeArray (matchtab);
        spy['k'] = parseInt (arr[2]);
        pos = t.indexOf ( "дейтерия" ); mkd = t.substr ( pos );
        matchtab = mkd.match ("(дейтерия[\\s]+)([0-9]{1,})");
        arr = jQuery.makeArray (matchtab);
        spy['d'] = parseInt (arr[2]);
        pos = t.indexOf ( "энергии" ); mkd = t.substr ( pos );
        matchtab = mkd.match ("(энергии[\\s]+)([0-9]{1,})");
        arr = jQuery.makeArray (matchtab);
        spy['e'] = parseInt (arr[2]);

        // Шанс на защиту от шпионажа:x%
        pos = t.indexOf ( "Шанс на защиту от шпионажа" ); counter = t.substr ( pos );
        matchtab = counter.match ("(Шанс на защиту от шпионажа )([0-9]{1,})");
        arr = jQuery.makeArray (matchtab);
        spy['counter'] = parseInt (arr[2]);

        // Флот, оборона, постройки, исследования. Не пытайтесь понять этот цикл.
        for (level=1; level<=4; level++)   {
            tab = map[level-1];
            if ( t.indexOf ( leveldesc[level-1] ) == -1) break;
            for (var i in map[level-1])
            {
                pos = t.indexOf ( desc[map[level-1][i]] ); obj = t.substr ( pos );
                matchtab = obj.match ("("+desc[map[level-1][i]]+")([0-9]{1,})");
                arr = jQuery.makeArray (matchtab);
                if ( typeof (arr[2]) == "undefined" ) value = 0;
                else value = parseInt (arr[2]);
                spy['o'+map[level-1][i]] = value;
            }
            spy['level']++;
        }
        for (level; level<=4; level++)   {    // Остаток заполнить нулями.
            tab = map[level-1];
            for (var i in map[level-1]) spy['o'+map[level-1][i]] = 0;
        }

        if ( (spy['o41'] + spy['o42'] + spy['o43']) > 0 ) spy['moon'] = 1;

        // Сформировать "сырой" текст доклада для добавления в БД.
        res += "sp < /(" + spy['player'] + ")/ (" + spy['planet'] + ") " + spy['g'] + " " + spy['s'] + " " + spy['p'] + " " + spy['moon'] + " " + spy['level'] + " " +
                  spy['m'] + " " + spy['k'] + " " + spy['d'] + " " + spy['e'] + " " + spy['counter'] + " ";
        for (level=1; level<=4; level++)   {
            tab = map[level-1];
            for (var i in map[level-1]) res += spy['o'+map[level-1][i]] + " ";
            spy['level']++;
        }
        res += spy['date'] + " >\n";

        var n = s.substr (1);
        end = n.indexOf ( "Сырьё на" );
        s = n;
    }
    return res;
}

// Разобрать текст одной или более страниц Ogame (Галактика, Статистика и Сообщения с разведданными)
// На выходе разобранный текст.
function ParsePage (text)
{
    var res = "";
    $("#appcontent").append("<div id=\"parser\" style=\"display: none;\">" + text +"</div>");
    res = ParseGalaxy () + ParseStat (text) + ParseSpy ();
    $("#parser").remove();
    return res;
}

// **************************************************************************************
// Интерфейс для GreaseMonkey.

function embedJgrowlCSS () {
    var embedCss = 
        "<style>\n"+
        "div.jGrowl { padding: 10px; z-index: 9999; }\n"+
        "div.ie6 { position: absolute; }\n"+
        "div.ie6.top-right { right: auto; bottom: auto;\n"+
        "left: expression( ( 0 - jGrowl.offsetWidth + ( document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.clientWidth ) + ( ignoreMe2 = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft ) ) + 'px' );\n"+
        "top: expression( ( 0 + ( ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop ) ) + 'px' );\n"+
        "}\n"+
        "div.ie6.top-left {\n"+
        "left: expression( ( 0 + ( ignoreMe2 = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft ) ) + 'px' );\n"+
        "top: expression( ( 0 + ( ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop ) ) + 'px' );\n"+
        "}\n"+
        "div.ie6.bottom-right {\n"+
        "left: expression( ( 0 - jGrowl.offsetWidth + ( document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.clientWidth ) + ( ignoreMe2 = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft ) ) + 'px' );\n"+
        "top: expression( ( 0 - jGrowl.offsetHeight + ( document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight ) + ( ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop ) ) + 'px' );\n"+
        "}\n"+
        "div.ie6.bottom-left {\n"+
        "left: expression( ( 0 + ( ignoreMe2 = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft ) ) + 'px' );\n"+
        "top: expression( ( 0 - jGrowl.offsetHeight + ( document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight ) + ( ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop ) ) + 'px' );\n"+
        "}\n"+
        "div.ie6.center {\n"+
        "left: expression( ( 0 + ( ignoreMe2 = document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft ) ) + 'px' );\n"+
        "top: expression( ( 0 + ( ignoreMe = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop ) ) + 'px' );\n"+
        "width: 100%;\n"+
        "}\n"+
        "body > div.jGrowl { position: fixed; }\n"+
        "body > div.jGrowl.top-left { left: 0px; top: 0px; }\n"+
        "body > div.jGrowl.top-right { right: 0px; top: 0px; }\n"+
        "body > div.jGrowl.bottom-left { left: 0px; bottom: 0px; }\n"+
        "body > div.jGrowl.bottom-right { right: 0px; bottom: 0px; }\n"+
        "body > div.jGrowl.center { top: 0px; width: 50%; left: 25%; }\n"+
        "div.center div.jGrowl-notification, div.center div.jGrowl-closer { margin-left: auto; margin-right: auto; }\n"+
        "div.jGrowl div.jGrowl-notification, div.jGrowl div.jGrowl-closer { background-color: #000; color: #fff; opacity: .85; filter: alpha(opacity = 85); zoom: 1; width: 235px; padding: 10px; margin-top: 5px; margin-bottom: 5px; font-family: Tahoma, Arial, Helvetica, sans-serif; font-size: 12px; text-align: left; display: none; -moz-border-radius: 5px; -webkit-border-radius: 5px; }\n"+
        "div.jGrowl div.jGrowl-notification { min-height: 40px; }\n"+
        "div.jGrowl div.jGrowl-notification div.header { font-weight: bold; font-size: 10px; }\n"+
        "div.jGrowl div.jGrowl-notification div.close { float: right; font-weight: bold; font-size: 12px; cursor: pointer; }\n"+
        "div.jGrowl div.jGrowl-closer { height: 15px; padding-top: 4px; padding-bottom: 4px; cursor: pointer; font-size: 11px; font-weight: bold; text-align: center;}\n"+
        "</style>\n";
        $("#content").after (embedCss);
}

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

if (location.href.indexOf ('index.php?page=galaxy') >= 0 || location.href.indexOf ('index.php?page=statistics') >= 0 || location.href.indexOf ('index.php?page=messages') >= 0 ) {
    // Получить адрес сервера.
    pos = location.href.indexOf ('http://') + 7;
    host = location.href.substr (pos);
    pos = host.indexOf ('/');
    host = host.substr (0, pos);
    sig = readCookie ( 'comsig_' + host );
    compath = readCookie ( 'comhost_' + host );

    // Обработать страницу.
    $("#content").append("<div id=\"appcontent\" style=\"display: none;\"></div>");
    res = ParsePage ( $("#content").html() );
    $("#appcontent").remove();

    // Создать окно с результатами.
    embedJgrowlCSS ();

    // Послать Ajax-запрос.
    GM_xmlhttpRequest ({
        method:"POST", url:compath+"com.php?page=update",
        headers: { 'Content-type': 'application/x-www-form-urlencoded' },
        data: 'sig='+sig+'&text=' + res,
        onload:function(details) {
            $.jGrowl("<h3>Сообщение от Командира.</h3><b>" + details.responseText) + "</b>";
        }
    })
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
        "function loadsig() {\n" + 
        "    pos = location.href.indexOf ('http://') + 7;\n" + 
        "    host = location.href.substr (pos);\n" + 
        "    pos = host.indexOf ('/');\n" + 
        "    host = host.substr (0, pos);\n" + 
        "    val = readCookie ('comsig_' + host );\n" + 
        "    if (val == null) val = \"\";\n" + 
        "    document.getElementById(\"comsig\").value = val;\n" + 
        "}\n" + 
        "function savesig() {\n" + 
        "    pos = location.href.indexOf ('http://') + 7;\n" + 
        "    host = location.href.substr (pos);\n" + 
        "    pos = host.indexOf ('/');\n" + 
        "    host = host.substr (0, pos);\n" + 
        "    createCookie ( 'comsig_' + host, document.getElementById(\"comsig\").value, 9999 );\n" + 
        "}\n" + 
        "function loadhost() {\n" + 
        "    pos = location.href.indexOf ('http://') + 7;\n" + 
        "    host = location.href.substr (pos);\n" + 
        "    pos = host.indexOf ('/');\n" + 
        "    host = host.substr (0, pos);\n" + 
        "    val = readCookie ('comhost_' + host );\n" + 
        "    if (val == null) val = \"\";\n" + 
        "    document.getElementById(\"comhost\").value = val;\n" + 
        "}\n" + 
        "function savehost() {\n" + 
        "    pos = location.href.indexOf ('http://') + 7;\n" + 
        "    host = location.href.substr (pos);\n" + 
        "    pos = host.indexOf ('/');\n" + 
        "    host = host.substr (0, pos);\n" + 
        "    createCookie ( 'comhost_' + host, document.getElementById(\"comhost\").value, 9999 );\n" + 
        "}\n" + 
        "</script>\n" + 
        "<table width='519'><tr><td class=\"c\" colspan =\"2\">Настройки Командирского плагина</td></tr>\n" + 
        "<tr> <th><a title='Сигнатуру можно получить и поменять в Настройках Командира'>Сигнатура пользователя</a></th>\n" + 
        "     <th><input id=\"comsig\" type=\"text\" size =\"20\" />\n" + 
        "     <input type=\"button\" value=\"Установить\" onclick=\"savesig();\" /></th></tr>\n" +
        "<tr> <th><a title='Укажите путь к установленному Командиру. Проверьте, что в конце пути находится /'>Адрес Командира</a></th>\n" + 
        "     <th><input id=\"comhost\" type=\"text\" size =\"20\" />\n" + 
        "     <input type=\"button\" value=\"Установить\" onclick=\"savehost();\" /></th></tr></table>\n" + 
        "<script type=\"text/javascript\">loadsig(); loadhost();</script>";

    $("table[width*='519']").after (embeddedScript);
}