// Скрипт для разбора статистики Ogame.ru.
// Входная информация - HTML-код страницы Статистика, можно несколько страниц, идущих подряд или перемешанных.
// Выходная информация - массив статистики по игрокам/альянсам.

var PlayerStat = new Array ();        // статистика игроков.
var AllyStat = new Array ();        // статистика альянсов.

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

// Разобрать одну или более страниц Статистики.
function parseStat (text)
{
    var player_stats = true;
    var player_stat_type = 1;
    var debug = document.getElementById ('debug');

    while (1)
    {

    // Получить номер вселенной.
    // <title>Вселенная 5 ОГейм</title>
    start = text.indexOf ("<title>Вселенная");
    if (start == -1) break;
    end = text.indexOf ("ОГейм</title>");
    var uni = text.substr (start+16, end-start-16);
    uni = parseInt (uni);

    // Получить дату статистики
    // Статистика (по состоянию на: 2009-06-25, 11:35:37)
    start = text.indexOf ("Статистика (по состоянию на:");
    var date = text.substr (start+29, 20);

    // Получить тип статистики Альянс/Игрок.
    /*
          <option value="player" selected>Игрок</option>
          <option value="ally" >Альянс</option>
    */
    start = text.indexOf ("<option value=\"player\"");
    end = text.indexOf ("Альянс</option>");
    var tmp = text.substr (start, end-start);
    if (tmp.indexOf ("value=\"player\" selected>") >= 0 ) player_stats = true;
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
    if (tmp.indexOf ("value=\"ressources\" selected>") >= 0) player_stat_type = 1;
    if (tmp.indexOf ("value=\"fleet\" selected>") >= 0 ) player_stat_type = 2;
    if (tmp.indexOf ("value=\"research\" selected>") >= 0 ) player_stat_type = 3;

    if (player_stats)
    {
        enduser = text.indexOf ("<!-- end user -->");
        endtext = text;
        text = text.substr (0, enduser);

        while (1)
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
            start = tmp.indexOf ("style='color:FFFFFF' >");
            if (start == -1) start = tmp.indexOf ("style='color:87CEEB' >");    // союзник
            if (start == -1) start = tmp.indexOf ("\" style='color:lime;'>");    // сам игрок
            end = tmp.indexOf ("</a>");
            tmp = tmp.substr (start+22, end-start-22);
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

            // ally + allyid.
            start = row.indexOf ("<!--  ally -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("?allyid=");
            end = tmp.indexOf ("' target='_ally'>");
            tmp = tmp.substr (start+8, end);
            tmp = php_str_replace (".", "", tmp);
            var ally_id = parseInt (tmp);

            start = row.indexOf ("<!--  ally -->");
            tmp = row.substr (start);
            var delta = 15;
            start = tmp.indexOf ("target='_ally'>");
            if (start == -1) { start = tmp.indexOf ("\">"); delta = 2; }    // свой альянс.
            end = tmp.indexOf ("</a>");
            tmp = tmp.substr (start+delta, end-start-delta);
            var ally = trim (tmp);

            // poins.
            start = row.indexOf ("<!-- points -->");
            tmp = row.substr (start);
            start = tmp.indexOf ("<th>");
            end = tmp.indexOf ("</th>");
            tmp = tmp.substr (start+4, end-start+4);
            tmp = php_str_replace (".", "", tmp);
            var points = parseInt (tmp);

            //alert (rank + ":" + nick + ":" + player_id + ":[" + coord_g + ":" + coord_s + ":" + coord_p + "]:" + ally + ":" + ally_id + ":" +points);
        
            player_id = player_id + "_ru" + uni;
            if (PlayerStat[player_id] == undefined ) PlayerStat[player_id] = new Array ();
            PlayerStat[player_id]['rank'+player_stat_type] = rank;
            PlayerStat[player_id]['nick'] = nick;
            PlayerStat[player_id]['g'] = coord_g;
            PlayerStat[player_id]['s'] = coord_s;
            PlayerStat[player_id]['p'] = coord_p;
            PlayerStat[player_id]['ally'] = ally;
            PlayerStat[player_id]['ally_id'] = ally_id;
            PlayerStat[player_id]['points'+player_stat_type] = points;
            PlayerStat[player_id]['uni'] = uni;
            PlayerStat[player_id]['date'+player_stat_type] = date;

            text = text.substr (10);
        /* Цикл по игрокам */ }

        text = endtext.substr (enduser+10);
        //debug.innerHTML = "<pre>" + text + "</pre>";
    }
    else
    {
        alert ("Статистика по альянсам не доделана");
        break;
    }
    
    /* Цикл по страницам */ }
}

// Вывести статистику в указанный элемент.
function printStat (elem)
{
    var res ='';
    
    res = "Статистика игроков: <br>\n<table><tr><td class='c'>PlayerID</td><td class='c'>Nick</td><td class='c'>Ranks</td><td class='c'>HomePlanet</td><td class='c'>Ally</td><td class='c'>AllyID</td><td class='c'>Points</td></tr>";

    for (var player_id in PlayerStat)
    {
        res += "<tr>";
        res += "<td>"+player_id+"</td>";
        res += "<td>"+PlayerStat[player_id]['nick']+"</td>";
        res += "<td>"+PlayerStat[player_id]['rank1']+"/"+PlayerStat[player_id]['rank2']+"/"+PlayerStat[player_id]['rank3']+"</td>";
        res += "<td>["+PlayerStat[player_id]['g']+":"+PlayerStat[player_id]['s']+":"+PlayerStat[player_id]['p']+"]</td>";
        res += "<td>"+PlayerStat[player_id]['ally']+"</td>";
        res += "<td>"+PlayerStat[player_id]['ally_id']+"</td>";
        res += "<td>"+"<a title='"+PlayerStat[player_id]['date1']+"'>"+PlayerStat[player_id]['points1']+"</a>/<a title='"+PlayerStat[player_id]['date2']+"'>"+PlayerStat[player_id]['points2']+"</a>/<a title='"+PlayerStat[player_id]['date3']+"'>"+PlayerStat[player_id]['points3']+"</a></td>";
        res += "</tr>\n";
    }
    res += "</table>";
    
    elem.innerHTML = res;
}
