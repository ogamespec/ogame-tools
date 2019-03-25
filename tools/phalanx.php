<?php

/*
    Обработчик докладов фаланги/обзора.
    (c) Andorianin, 2009, 2010
*/
$version = 0.21;

$break = Explode('/', $_SERVER["SCRIPT_NAME"]);
$SELF = $break[count($break) - 1]; 
$ScriptAddr = "http://" . $_SERVER['HTTP_HOST'] . "/tools/" . $SELF;

ob_start ();

header('Pragma:no-cache');

$skin = $_COOKIE["phalanx_skin"];
if ($skin == "") $skin = '../evolution/';
else
{
    // Убрать тэги и скрипты из строки.
    $search = array ( "'<script[^>]*?>.*?</script>'si",  // Вырезает javaScript
                      "'<[\/\!]*?[^<>]*?>'si",           // Вырезает HTML-теги
                      "'([\r\n])[\s]+'" );               // Вырезает пробельные символы
    $replace = array ("", "", "\\1", "\\1" );
    $skin = preg_replace($search, $replace, $skin);
    if (ValidateURL ($skin) == false) $skin = '../evolution/';
}
//$skin = "http://localhost/evolution/";

$showfleet = $_COOKIE["phalanx_showfleet"];
if ($showfleet == "") $showfleet = 0;
$showfleet = $showfleet === "true";
$websim = $_COOKIE["phalanx_websim"];
if ($websim == "") $websim = 1;
$websim = $websim === "true";

?>

<HTML>
<HEAD>
<link rel="stylesheet" type="text/css" href="../css/default.css">
<link rel="stylesheet" type="text/css" href="../css/formate2.css">
<link rel="stylesheet" type="text/css" href="<?=$skin?>formate.css">
<meta http-equiv='content-type' content='text/html; charset=utf-8' />
<TITLE>Доклад фаланги/обзора</TITLE>
<SCRIPT src="../includes/jscripts/overlib.js" type="text/javascript" language="JavaScript"></SCRIPT>

<script language='JavaScript'>
var timezone = readCookie ('phalanx_tz');
if (timezone == null)
{
    timezone = 1;
    createCookie ('phalanx_tz', timezone);
}
var summertime = readCookie ('phalanx_summertime');
if (summertime == null)
{
    d = new Date ();
    month = d.getMonth ();
    if (month >= 3 && month <= 9) summertime = 1;
    else summertime = 0;
    createCookie ('phalanx_summertime', summertime ? "true" : "false");
}
else summertime = summertime == "true" ? 1 : 0;
var timedelta = -7;

Date.prototype.getDOY = function() {
    var onejan = new Date(this.getUTCFullYear(),0,1);
    return Math.ceil((this - onejan) / 86400000);
}

function t()
{
    var now = new Date ();

    for (c=1; c<=anz; c++)
    {
        var str = '';
        bxx = document.getElementById('bxx' + c);
        star = bxx.getAttribute ("star");
        end = new Date( star * 1000 + parseInt(timezone)*60*60*1000 + parseInt(summertime)*60*60*1000);
        endUTC = new Date( star * 1000 );

        // Обратный отсчет.
        diff = end - now;
        if (diff < 0) str += '-';
        else
        {
            dt = new Date (diff);
            h = Math.floor (dt / (1000 * 60 * 60));
            dt -= h * (1000 * 60 * 60);
            m = Math.floor (dt / (1000 * 60));
            dt -= m * (1000 * 60);
            s = Math.floor (dt / 1000);
            if (m < 10) m = '0' + m;
            if (s < 10) s = '0' + s;
            str += h + ":" + m + ":" + s;
        }

        // Вычилсить дату окончания.
        doy = end.getDOY ();
        day = end.getUTCDate ();
        month = end.getUTCMonth () + 1;
        h = parseInt(end.getUTCHours ());
        m = end.getUTCMinutes ();
        s = end.getUTCSeconds ();
        if (day < 10) day = '0' + day;
        if (month < 10) month = '0' + month;
        if (m < 10) m = '0' + m;
        if (s < 10) s = '0' + s;
        if (h < 10) h = '0' + h;
        str += '<br><font color=lime>';
        if (doy != now.getDOY ()) str += day + "/" + month + " ";
        str += h + ":" + m + ":" + s;
        str += '</font>';

        bxx.innerHTML = str;
        bxx.title = "Время прибытия (UTC): " + endUTC.toUTCString();
    }
    window.setTimeout("t();", 999);
}

// Управление Печеньками.

function createCookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
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

function eraseCookie(name) {
    createCookie(name,"",-1);
}

function onBodyLoad()
{
    document.option.skinpath.value = readCookie ('phalanx_skin');
    document.option.showfleet.checked = readCookie ('phalanx_showfleet') == "true" ? true : false;
    document.option.websim.checked = readCookie ('phalanx_websim') == "true" ? true : false;
    document.option.tz.value = readCookie ('phalanx_tz');
    document.option.summertime.checked = readCookie ('phalanx_summertime') == "true" ? true : false;
    document.option.sendmail2.checked = false;
}

function onSkinChange()
{
    createCookie ( 'phalanx_skin', document.option.skinpath.value, 9999 );
    window.location.reload ();
}

function onShowFleet()
{
    createCookie ( 'phalanx_showfleet', document.option.showfleet.checked, 9999 );
    window.location.reload ();
}

function onWebSim()
{
    createCookie ( 'phalanx_websim', document.option.websim.checked, 9999 );
    window.location.reload ();
}

function onTimeZone()
{
    createCookie ( 'phalanx_tz', document.option.tz.value, 9999 );
    window.location.reload ();
}

function onSummerTime()
{
    createCookie ( 'phalanx_summertime', document.option.summertime.checked, 9999 );
    window.location.reload ();
}

function onSendMail ()
{
    sendform.sendmail.value = document.option.sendmail2.checked == true ? 1 : 0;
}

</script>

</HEAD>

<BODY onload='onBodyLoad();'><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<!-- Доклад Фаланги/Обзора (версия <?=$version?>). Начало. -->

<?php

require_once "config.php";
require_once "db.php";

define ("EVENTTABLE", "event_reports");
define ("FLOODTIME", 1);

$secretword = "SpecnazPhalanxRulitSexWithKateIsGood!";
$debug = 1;
$report = array ();

$desc[202] = "Малый транспорт";
$desc[] = "Большой транспорт";
$desc[] = "Лёгкий истребитель";
$desc[] = "Тяжёлый истребитель";
$desc[] = "Крейсер";
$desc[] = "Линкор";
$desc[] = "Колонизатор";
$desc[] = "Переработчик";
$desc[] = "Шпионский зонд";
$desc[] = "Бомбардировщик";
$desc[] = "Солнечный спутник";
$desc[] = "Уничтожитель";
$desc[] = "Звезда смерти";
$desc[] = "Линейный крейсер";

// Цель для ракетной атаки.
$desc[401] = "Ракетная установка";
$desc[] = "Лёгкий лазер";
$desc[] = "Тяжёлый лазер";
$desc[] = "Пушка Гаусса";
$desc[] = "Ионное орудие";
$desc[] = "Плазменное орудие";
$desc[] = "Малый щитовой купол";
$desc[] = "Большой щитовой купол";

function ValidateURL ($url)
{
    $urlregex = "^(https?|ftp)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
    if (eregi($urlregex, $url)) return true;
    else return false; 
}

function nicenum ($number)
{
    return number_format($number,0,",",".");
}

function parseInt($string) {
	if(preg_match('/(\d+)/', $string, $array)) {
		return $array[1];
	} else {
		return 0;
	}
}

// bool/array str_between( string str, string start_str, string end_str )
function str_between($str,$start,$end) {
  if (preg_match_all('/' . preg_quote($start) . '(.*?)' . preg_quote($end) . '/',$str,$matches)) {
   return $matches[1];
  }
  // no matches
  return false;
}

function str_squeeze($test) {
    return trim(ereg_replace( ' +', ' ', $test));
}

// Защита от флуда.
// Открываем файл, проверяем его дату. Если его возраст меньше FLOODTIME, то нас флудят.
// Возвращает количество секунд до окончания попытки флуда, или 0, если всё в порядке.
function floodprotect ()
{
    $floodfile = "phxflood.txt";
    $now = time ();
    $old = filemtime ($floodfile);
    if ( ($now - $old) <= FLOODTIME) return FLOODTIME - ($now - $old);
    $f = fopen ($floodfile, 'w');
    fwrite ($f, $now);
    fclose ($f);
    return 0;
}

function ConnectDatabase ()
{
    global $db_host, $db_user, $db_pass, $db_name;
    dbconnect ($db_host, $db_user, $db_pass, $db_name);
    dbquery("SET NAMES 'utf8';");
    dbquery("SET CHARACTER SET 'utf8';");
    dbquery("SET SESSION collation_connection = 'utf8_general_ci';");
    echo "<!-- MySQL connection established... -->\n";
}

function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
  $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
  mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
}

// Подготовить доклад из SQL-базы для вывода в HTML.
// *******************

// Название лога.
// Overview:0. Доклад фаланги игрока Name с Название [G:S:P] Day Month день/вечер/ночь
// Overview:1. Список событий игрока Name с Название [G:S:P] Day Month день/вечер/ночь
function reportTitle ()
{
    global $report;
    $title = '';
    $monstr = array ( 'Нульваря', 'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек' );
    $date = getdate ($report['Date']);
    $hr = $date['hours'];
    if ($report['Overview']) $title .= "Список событий ";
    else $title .= "Доклад фаланги ";
    $title .= "" . $report['Who'] . " ";
    $title .= "с " . $report['From_name'] . " ";
    $title .= "(".$report['From_g'].":".$report['From_s'].":".$report['From_p'].") ";
    $title .= $date['mday'] . " " . $monstr[$date['mon']];
    if ($hr >= 0 && $hr <5) $title .= " ночь";
    else if ($hr >= 5 && $hr <10) $title .= " утро";
    else if ($hr >= 10 && $hr <15) $title .= " день";
    else if ($hr >= 15 && $hr <20) $title .= " вечер";
    else if ($hr >= 20 && $hr <24) $title .= " ночь";
    $report['Title'] = $title;
}

// Обработать сырой доклад из базы и разместить всё в удобном виде в массиве $report.
function parsesource ()
{
    global $report, $desc, $debug;
    $singlevars = array ("Date", "Overview" );
    $src = $report['source'];
    $src = str_replace("\r\n", " ", $src);
    $src = str_squeeze ($src);

    if ($debug) echo "<font color=gold>Обработать доклад о передвижении флота: </font><br>\n";

    // Одиночные переменные.
    foreach ($singlevars as $i=>$val)
    {
        $s = strstr ($src, $val); $s = strstr ($s, "{");
        $tmp = str_between ($s, "{", "}");
        sscanf ( $tmp[0], "%i", $report[$val] );
    }

    // Откуда сделан доклад (координаты и название луны/планеты).
    $s = strstr ($src, "From"); $s = strstr ($s, "{");
    $tmp = str_between ($s, "{", "}");
    sscanf ( $tmp[0], "%i %i %i", $report['From_g'], $report['From_s'], $report['From_p'] );    
    $start = strpos ($tmp[0], "[") + 1;
    $end = strpos ($tmp[0], "]");
    $name = substr ($tmp[0], $start, $end-$start);
    $report['From_name'] = trim($name);

    // Кем сделан доклад (имя игрока).
    $s = strstr ($src, "Who"); $s = strstr ($s, "{");
    $tmp = str_between ($s, "{", "}");
    $start = strpos ($tmp[0], "[") + 1;
    $end = strpos ($tmp[0], "]");
    $name = substr ($tmp[0], $start, $end-$start);
    $report['Who'] = trim($name);

    // Название лога.
    reportTitle ();

    // Список событий.
    // EventN { sec time Fleet0 Fleet1 .... FleetN }

    $i = 0;
    $s = $src;
    while (1)
    {
        $idx = "Event".$i."_";

        $s = strstr ($s, "Event".$i);
        if ($s == FALSE) break;
        $s = strstr ($s, "{");
        $tmp = str_between ($s, "{", "}");
        if ($tmp == false) echo "Error parsing event ".$i."<br>";
        $s = strstr ($s, "}");

        $report[$idx."sec"] = strtok ($tmp[0], " ");
        $report[$idx."time"] = strtok (" ");

        $fl = 0;
        while (1)
        {
            $idf = "Event".$i."_F".$fl;
            $n = strtok (" ");
            if ( !is_numeric($n) ) break;
            else $report[$idf] = $n;
            $fl++;
        }
        $report[$idx."fleets"] = $fl;

        $i++;
    }
    $report['Events'] = $i;

    // Список флотов.
    // FleetN { [owner] task assignment direction  МТ БТ ЛИ ТИ КР ЛИНК КОЛ РАБ ШЗ БОМБ СС УН ЗС ЛК G_start S_start P_start Moon_start [start_name] G_target S_target P_target Moon_target [target_name] M K D }

    $i = 0;
    $s = $src;
    while (1)
    {
        $idx = "Fleet".$i."_";

        $s = strstr ($s, "Fleet".$i);
        if ($s == FALSE) break;
        $s = strstr ($s, "{");
        $tmp = str_between ($s, "{", "}");
        if ($tmp == false) echo "Error parsing fleet ".$i."<br>";
        $s = strstr ($s, "}");

        $fs = $tmp[0];
        $start = strpos ($fs, "[") + 1;
        $end = strpos ($fs, "]");
        $report[$idx."owner"] = trim (substr ($fs, $start, $end-$start));

        $part = substr ($fs, $end+1);
        $report[$idx."task"] = strtok ($part, " ");
        $report[$idx."assign"] = strtok (" ");
        $report[$idx."dir"] = strtok (" ");
        for ($n=202; $n<216; $n++) $report[$idx.$n] = strtok (" ");
        $report[$idx."gs"] = strtok (" ");
        $report[$idx."ss"] = strtok (" ");
        $report[$idx."ps"] = strtok (" ");
        $report[$idx."ms"] = strtok (" ");

        $start = strpos ($fs, "[", $end+1) + 1;
        $end = strpos ($fs, "]", $end+1);
        $report[$idx."ns"] = trim (substr ($fs, $start, $end-$start));

        $part = substr ($fs, $end+1);
        $report[$idx."gt"] = strtok ($part, " ");
        $report[$idx."st"] = strtok (" ");
        $report[$idx."pt"] = strtok (" ");
        $report[$idx."mt"] = strtok (" ");

        $start = strpos ($fs, "[", $end+1) + 1;
        $end = strpos ($fs, "]", $end+1);
        $report[$idx."nt"] = trim (substr ($fs, $start, $end-$start));

        $part = substr ($fs, $end+1);
        $report[$idx."m"] = strtok ($part, " ");
        $report[$idx."k"] = strtok (" ");
        $report[$idx."d"] = strtok (" ");

        $i++;
    }
    $report['Fleets'] = $i;

    DebugReport ();

    //print_r ($report);
}

function DebugReport ()
{
    global $report, $desc, $debug;

    if ($debug)
    {
        $shortfleet = array ( "М. трансп.", "Б. трансп.", "Л. истр.", "Т. истр.", "Крейсер", "Линк", "Колонизатор", "Переработчик", "Шп. зонд", "Бомб.", "Солн. спутник", "Уничт.", "ЗС", "Лин. Кр." );
        $assign = array ('Свой', 'Чужой', 'Союзный');
        $assignRak = array ('Своя', 'Чужая', 'Союзная');
        $direct = array ('убывает', 'возвращается', 'удерживает');
        $taskstr = array ('Бесцельно летать', 'Атаковать', 'Транспорт', 'Оставить', 'Шпионаж', 'Переработать', 'Держаться', 'Уничтожить', 'Колонизировать', 'Экспедиция', 'Совместная атака', 'Атаковать (САБ)');

        reportTitle ();
        echo "\n\n<br>Название: " . $report['Title'] . "<br>\n";

        echo "Список флотов (".$report['Fleets'].")<br>\n";
        for ($i=0; $i<$report['Fleets']; $i++)
        {
            $idx = "Fleet".$i."_";
            $task = $report[$idx."task"];
            $dir = $report[$idx."dir"];
            if ($task != 20)
            {
                echo $i . ": " . $assign[$report[$idx."assign"]] . " флот (";
                for ($n=202; $n<216; $n++)
                {
                    $num = $report[$idx.$n];
                    if ($num > 0) echo $shortfleet[$n-202] . " " . $num . " ";
                }
                echo ") игрока " . $report[$idx."owner"] . " " . $direct[$dir] . " с заданием " . $taskstr[$task] . ". ";
                echo "Отправлен с ";
                if ($report[$idx."ms"]) echo "луны ";
                else echo "планеты ";
                echo $report[$idx."ns"] . " [" .$report[$idx."gs"]. ":" .$report[$idx."ss"]. ":" .$report[$idx."ps"]. "]. ";
                echo "Пункт назначения ";
                if ($report[$idx."mt"]) echo "луна ";
                else echo "планета ";
                echo $report[$idx."nt"] . " [" .$report[$idx."gt"]. ":" .$report[$idx."st"]. ":" .$report[$idx."pt"]. "]. ";
                $cargo = $report[$idx."m"] + $report[$idx."k"] + $report[$idx."d"];
                if ($cargo > 0) echo "Груз: " . "Металл: " .$report[$idx."m"]. " Кристалл: " .$report[$idx."k"]. " Дейтерий: " . $report[$idx."d"];
                else echo "Пустой.";
            }
            else
            {
                echo $i+1 . ": " . $assignRak[$report[$idx."assign"]] . " ракетная атака (";
                echo $report[$idx."202"];
                echo ") игрока " . $report[$idx."owner"] . ". ";
                echo "С ";
                if ($report[$idx."ms"]) echo "луны ";
                else echo "планеты ";
                echo $report[$idx."ns"] . " [" .$report[$idx."gs"]. ":" .$report[$idx."ss"]. ":" .$report[$idx."ps"]. "]. ";
                echo "на ";
                if ($report[$idx."mt"]) echo "луну ";
                else echo "планету ";
                echo $report[$idx."nt"] . " [" .$report[$idx."gt"]. ":" .$report[$idx."st"]. ":" .$report[$idx."pt"]. "]. ";
                if ($dir) echo "Основная цель: " . $desc[$dir];
                else echo "Основная цель: Все";
            }
            echo "<br>\n";
        }

        echo "Список событий (".$report['Events'].")<br>\n";
        for ($i=0; $i<$report['Events']; $i++)
        {
            $idx = "Event".$i."_";
            echo $i . ": " . $report[$idx."sec"] . " " . $report[$idx."time"] . ". Флоты (".$report[$idx."fleets"]."): ";
            for ($fl=0; $fl<$report[$idx."fleets"]; $fl++) echo $report[$idx."F".$fl] . " ";
            echo "<br>\n";
        }
    }
}

// Загрузить и сохранить обработанный доклад в SQL-базе
// *******************

// Загрузить доклад. Возвращает 1, если ок, или 0 если такого доклада нет.
function loadreport ($id)
{
    global $report, $debug;
    if ($debug) echo "<font color=gold>Загрузить доклад о передвижении флота №".$id.": </font><br>\n";
    $result = dbquery("SELECT * FROM ".EVENTTABLE." WHERE id='".$id."'");
    if (dbrows($result) != 0)
    {
        $report = dbarray($result);
        $rep = $report['source'];
        $report['source'] = strip_tags ($report['source']); // Удалить комментарии.
        $rep = str_replace ("<!", " <font color=#00FF00>&lt;", $rep);
        $rep = str_replace ("!>", "&gt;</font> ", $rep);
        if ($debug)
        {
            echo nl2br ($rep) ."<br>\n";
        }
        return true;
    }
    else return false;
}

// Сохранить доклад. Возвращает id, или 0, если не удалось сохранить.
function savereport ($text)
{
    global $debug, $secretword;
    $id = md5 ($text . $secretword);

    if ($debug)
    {
        echo "<font color=gold>Сохранить доклад о передвижении флота №".$id.": </font><br>\n";
        $rep = $text;
        $rep = str_replace ("<!", " <font color=#00FF00>&lt;", $rep);
        $rep = str_replace ("!>", "&gt;</font> ", $rep);
        echo nl2br ($rep) ."<br>\n";
    }

    dbquery( "DELETE FROM ".EVENTTABLE." WHERE id='".$id."'" );
    dbquery( "INSERT INTO ".EVENTTABLE." (id) VALUES ('".$id."')" );
    $query = "UPDATE ".EVENTTABLE." SET "."source"." = '". $text."' WHERE id='".$id."'";
    dbquery( $query);
    return $id;
}

/*
 ****************************************************************
 * Разбор HTML обзора/фаланги для получения списка событий.
*/

// Исходный текст является Обзором или сканом фаланги.
function IsOverview ($text)
{
    if (strpos ($text, "game/index.php") && strpos ($text, "renameplanet")) return true;
    else return false;
}

// Получить авторство скана
function getAuthority ($s)
{
    global $report;

    if ( IsOverview ($s) )
    {
        // Название планеты/луны и координаты.
        $pos = strpos ($s, "&mode=&gid=&messageziel=&re=0\\\" selected>");
        $s = substr ($s, $pos);
        $planetname = str_between ($s, ">", "<");
        $report["From_name"] = trim ($planetname[0]);
        parseCoords ($s, &$report["From_g"], &$report["From_s"], &$report["From_p"]);

        // Имя игрока.
        $pos = strpos ($s, "renameplanet");
        $s = substr ($s, $pos);
        $pos = strpos ($s, ">");
        $s = substr ($s, $pos+1);
        $name = str_between ($s, "(", ")");
        $report["Who"] = trim ($name[0]);
        $report["Overview"] = 1;
    }
    else
    {
        // Доклад сенсора с луны на координатах <a href="javascript:showGalaxy(1,260,4)" >[1:260:4]</a> (Andorianin)  </td>
        $pos = strpos ($s, "Доклад сенсора с луны");
        $s = strip_tags (substr ($s, $pos));
        $report["From_name"] = "луна";
        parseCoords ($s, &$report["From_g"], &$report["From_s"], &$report["From_p"]);
        $name = str_between ($s, "(", ")");
        $report["Who"] = trim ($name[0]);
        $report["Overview"] = 0;
    }
}

// Разобрать координаты [G:S:P]
function parseCoords ($str, &$g, &$s, &$p)
{
    $g = $s = $p = 0;
    $pattern = "/[0-9]{1}:[0-9]{1,3}:[0-9]{1,2}/";
    preg_match ($pattern, $str, $match);
    $coords = preg_split ("/:/", $match[0]);
    $g = $coords[0]; $s = $coords[1]; $p = $coords[2];
}

// Получить состав флота.
function parseFleet ($s, $idx)
{
    global $report, $desc;
    $s = html_entity_decode ($s, ENT_COMPAT, 'UTF-8');
    for ($i=202; $i<216; $i++)
    {
        $fleet = strstr ($s, $desc[$i]);
        if ($fleet) { $fleet = str_replace (".", "", $fleet); $report[$idx.$i] = parseInt ($fleet); }
        else $report[$idx.$i] = 0;
    }
}

// Получить основную цель.
function parseTarget ($s, $idx)
{
    global $report, $desc;
    $s = html_entity_decode ($s, ENT_COMPAT, 'UTF-8');
    $report[$idx."dir"] = 0;
    for ($i=401; $i<409; $i++)
    {
        if (strpos ($s, "> Основная цель ".$desc[$i])) { $report[$idx."dir"] = $i; break; }
    }
}

// Получить ресурсы которые везет флот.
function parseCargo ($s, $idx)
{
    global $report;
    $s = html_entity_decode ($s, ENT_COMPAT, 'UTF-8');
    $res = strstr ($s, "Металл:");
    if ($res) { $res = str_replace (".", "", $res); $report[$idx."m"] = parseInt ($res); }
    else $report[$idx."m"] = 0;
    $res = strstr ($s, "Кристалл:");
    if ($res) { $res = str_replace (".", "", $res); $report[$idx."k"] = parseInt ($res); }
    else $report[$idx."k"] = 0;
    $res = strstr ($s, "Дейтерий:");
    if ($res) { $res = str_replace (".", "", $res); $report[$idx."d"] = parseInt ($res); }
    else $report[$idx."d"] = 0;
    $report[$idx."cargo"] = $report[$idx."m"] + $report[$idx."k"] + $report[$idx."d"];
}

// Получить направление движения флота и принадлежность.
function parseDirAssign ($s, $idx)
{
    global $report;
    $tasklist = array ( "none", "attack", "transport", "deploy", "espionage", "harvest", "hold", "destroy", "colony", "transport", "federation" );
    $task = $report[$idx."task"];
    if (strpos ($s, "flight own".$tasklist[$task])) { $dir = 0; $as = 0; }
    else if (strpos ($s, "return own".$tasklist[$task]))  { $dir = 1; $as = 0; }
    else if (strpos ($s, "holding own".$tasklist[$task]))  { $dir = 2; $as = 0; }
    else if (strpos ($s, "flight ".$tasklist[$task]))
    {
        $dir = 0;
        if ($task == 6) $as = 2;    // Летит держаться союзник
        else $as = 1;
    }
    else if (strpos ($s, "holding ".$tasklist[$task])) { $dir = 2; $as = 2; }

    // САБы
    else if (strpos ($s, "federation\\'>Ваш")) { $dir = 0; $as = 0; }
    else if (strpos ($s, "attack\\'>Ваш")) { $dir = 0; $as = 0; }
    else if (strpos ($s, "federation\\'>Боевой")) { $dir = 0; $as = 1; }
    else if (strpos ($s, "attack\\'>Боевой")) { $dir = 0; $as = 1; }
    else if (strpos ($s, "ownfederation\\'>Альянсовый")) { $dir = 0; $as = 2; }
    else if (strpos ($s, "ownattack\\'>Альянсовый")) { $dir = 0; $as = 2; }

    // Скан фаланги? Всегда чужой.
    if (!$report['Overview'])
    {
        if (strpos ($s, "flight phalanx_fleet")) $dir = 0;
        else if (strpos ($s, "return phalanx_fleet")) $dir = 1;
        else if (strpos ($s, "holding phalanx_fleet")) $dir = 2;
        else $dir = 0;
        $as = 1;
    }

    $report[$idx."dir"] = $dir;
    $report[$idx."assign"] = $as;
}

// Получить имена планет и их координаты.
function parsePlanets ($s, $idx)
{
    global $report;
    $notags = strip_tags ($s);
    $task = $report[$idx."task"];
    $dir = $report[$idx."dir"];
    $as = $report[$idx."assign"];
    $dir = $dir | ($as << 4);

    $pos = strpos ($s, "]</a>");
    $begin = substr ($s, 0, $pos+1);
    $end = substr ($s, $pos+1);

    // Все, кроме Держаться и Экспедиция.
    if ($task == 1 || $task == 2 || $task == 3 || $task == 4 || $task == 5 || $task == 7 || $task == 8 || $task == 10 || $task == 11)
    {
        if ($dir == 0 || $dir == 0x10)
        {
            parseCoords ($begin, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            parseCoords ($end, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            if (strpos ($begin, 'a> с планеты ')) { $name = str_between ($begin, "a> с планеты ", "<a "); $report[$idx."ms"] = 0; }
            else { $name = str_between ($begin, "a> с ", "<a "); $report[$idx."ms"] = 1; }
            $report[$idx."ns"] = trim ($name[0]);
            if (strpos ($end, 'a> отправлен на планету ')) { $name = str_between ($end, "a> отправлен на планету ", "<a "); $report[$idx."mt"] = 0; }
            else { $name = str_between ($end, "a> отправлен на ", "<a "); $report[$idx."mt"] = 1; }
            $report[$idx."nt"] = trim ($name[0]);
        }
        else if ($dir == 1)
        {
            parseCoords ($begin, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            parseCoords ($end, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            if (strpos ($begin, 'a>, отправленный с планеты')) { $name = str_between ($begin, "a>, отправленный с планеты ", "<a "); $report[$idx."mt"] = 0; }
            else { $name = str_between ($begin, "a>, отправленный с ", "<a "); $report[$idx."mt"] = 1; }
            $report[$idx."nt"] = trim ($name[0]);
            if (strpos ($end, 'a>, возвращается на планету')) { $name = str_between ($end, "a>, возвращается на планету ", "<a "); $report[$idx."ms"] = 0; }
            else { $name = str_between ($end, "a>, возвращается на ", "<a "); $report[$idx."ms"] = 1; }
            $report[$idx."ns"] = trim ($name[0]);
        }
        else if ($dir == 0x11)
        {
            parseCoords ($begin, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            parseCoords ($end, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            if (strpos ($begin, 'a> возвратится с планеты')) { $name = str_between ($begin, "a> возвратится с планеты ", "<a "); $report[$idx."mt"] = 0; }
            else { $name = str_between ($begin, "a> возвратится с ", "<a "); $report[$idx."mt"] = 1; }
            $report[$idx."nt"] = trim ($name[0]);
            if (strpos ($end, 'a> на планету')) { $name = str_between ($end, "a> на планету ", "<a "); $report[$idx."ms"] = 0; }
            else { $name = str_between ($end, "a> на ", "<a "); $report[$idx."ms"] = 1; }
            $report[$idx."ns"] = trim ($name[0]);
        }
        else if ($task >= 10 && $dir == 0x20)
        {
            parseCoords ($begin, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            parseCoords ($end, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            if (strpos ($begin, 'a> с планеты ')) { $name = str_between ($begin, "a> с планеты ", "<a "); $report[$idx."ms"] = 0; }
            else { $name = str_between ($begin, "a> с ", "<a "); $report[$idx."ms"] = 1; }
            $report[$idx."ns"] = trim ($name[0]);
            $name = str_between ($end, "a> отправлен на ", "<a "); $report[$idx."mt"] = 1;
            $report[$idx."nt"] = trim ($name[0]);
        }
    }
    else if ($task == 6)        // Держаться.
    {
        if ($dir == 0 || $dir == 0x10)
        {
            parseCoords ($begin, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            parseCoords ($end, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            if (strpos ($begin, 'a> с планеты ')) { $name = str_between ($begin, "a> с планеты ", "<a "); $report[$idx."ms"] = 0; }
            else { $name = str_between ($begin, "a> с ", "<a "); $report[$idx."ms"] = 1; }
            $report[$idx."ns"] = trim ($name[0]);
            if (strpos ($end, 'a> отправлен на планету ')) { $name = str_between ($end, "a> отправлен на планету ", "<a "); $report[$idx."mt"] = 0; }
            else { $name = str_between ($end, "a> отправлен на ", "<a "); $report[$idx."mt"] = 1; }
            $report[$idx."nt"] = trim ($name[0]);
        }
        else if ($dir == 1)
        {
            parseCoords ($begin, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            parseCoords ($end, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            if (strpos ($begin, 'a>, отправленный с планеты')) { $name = str_between ($begin, "a>, отправленный с планеты ", "<a "); $report[$idx."mt"] = 0; }
            else { $name = str_between ($begin, "a>, отправленный с ", "<a "); $report[$idx."mt"] = 1; }
            $report[$idx."nt"] = trim ($name[0]);
            if (strpos ($end, 'a>, возвращается на планету')) { $name = str_between ($end, "a>, возвращается на планету ", "<a "); $report[$idx."ms"] = 0; }
            else { $name = str_between ($end, "a>, возвращается на ", "<a "); $report[$idx."ms"] = 1; }
            $report[$idx."ns"] = trim ($name[0]);
        }
        else if ($dir == 2)
        {
            parseCoords ($begin, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            parseCoords ($end, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            if (strpos ($begin, 'a> с планету ')) { $name = str_between ($begin, "a> с планету ", "<a "); $report[$idx."ms"] = 0; }
            else { $name = str_between ($begin, "a> с ", "<a "); $report[$idx."ms"] = 1; }
            $report[$idx."ns"] = trim ($name[0]);
            if (strpos ($end, 'a> отправлен на планеты ')) { $name = str_between ($end, "a> отправлен на планеты ", "<a "); $report[$idx."mt"] = 0; }
            else { $name = str_between ($end, "a> отправлен на ", "<a "); $report[$idx."mt"] = 1; }
            $report[$idx."nt"] = trim ($name[0]);
        }
        else if ($dir == 0x11)
        {
            parseCoords ($begin, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            parseCoords ($end, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            if (strpos ($begin, 'a> возвратится с планеты')) { $name = str_between ($begin, "a> возвратится с планеты ", "<a "); $report[$idx."mt"] = 0; }
            else { $name = str_between ($begin, "a> возвратится с ", "<a "); $report[$idx."mt"] = 1; }
            $report[$idx."nt"] = trim ($name[0]);
            if (strpos ($end, 'a> на планету')) { $name = str_between ($end, "a> на планету ", "<a "); $report[$idx."ms"] = 0; }
            else { $name = str_between ($end, "a> на ", "<a "); $report[$idx."ms"] = 1; }
            $report[$idx."ns"] = trim ($name[0]);
        }
        else if ($dir == 0x20)
        {
            parseCoords ($begin, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            parseCoords ($end, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            $name = str_between ($begin, "a> с ", "<a ");
            $report[$idx."ns"] = trim ($name[0]);
            $name = str_between ($end, "a> отправлен на ", "<a ");
            $report[$idx."nt"] = trim ($name[0]);
            $report[$idx."ms"] = $report[$idx."mt"] = 1;
        }
        else if ($dir == 0x22 || $dir == 0x12)
        {
            parseCoords ($begin, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            parseCoords ($end, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            $name = str_between ($begin, "a> с ", "<a ");
            $report[$idx."ns"] = trim ($name[0]);
            $name = str_between ($end, "a> на орбите ", "<a ");
            $report[$idx."nt"] = trim ($name[0]);
            $report[$idx."ms"] = $report[$idx."mt"] = 1;
        }
    }
    else if ($task == 9)        // Экспедиция.
    {
        if ($dir == 0 || $dir == 2)
        {
            parseCoords ($begin, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            parseCoords ($end, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            if (strpos ($begin, 'a>, отправленный с планеты ')) { $name = str_between ($begin, "a>, отправленный с планеты ", "<a "); $report[$idx."ms"] = 0; }
            else { $name = str_between ($begin, "a>, отправленный с ", "<a "); $report[$idx."ms"] = 1; }
            $report[$idx."ns"] = trim ($name[0]);
            $report[$idx."nt"] = "";
        }
        else if ($dir == 1)
        {
            parseCoords ($begin, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            parseCoords ($end, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            $report[$idx."nt"] = "";
            if (strpos ($notags, "после приказа"))
            {
                parseCoords ($begin, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
                if (strpos ($s, 'a> возвращается на планету ')) { $name = str_between ($s, "a> возвращается на планету ", "<a "); $report[$idx."ms"] = 0; }
                else { $name = str_between ($s, "a> возвращается на ", "<a "); $report[$idx."ms"] = 1; }
                $report[$idx."ns"] = trim ($name[0]);
            }
            else
            {
                if (strpos ($s, 'a>, возвращается на планету ')) { $name = str_between ($s, "a>, возвращается на планету ", "<a "); $report[$idx."ms"] = 0; }
                else { $name = str_between ($s, "a>, возвращается на ", "<a "); $report[$idx."ms"] = 1; }
                $report[$idx."ns"] = trim ($name[0]);
            }
        }
        else if ($dir == 0x10 || $dir == 0x12)
        {
            parseCoords ($begin, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            parseCoords ($end, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            if (strpos ($begin, 'a> с планеты ')) { $name = str_between ($begin, "a> с планеты ", "<a "); $report[$idx."ms"] = 0; }
            else { $name = str_between ($begin, "a> с ", "<a "); $report[$idx."ms"] = 1; }
            $report[$idx."ns"] = trim ($name[0]);
            if (strpos ($end, 'a> отправлен на планету ')) { $name = str_between ($end, "a> отправлен на планету ", "<a "); $report[$idx."mt"] = 0; }
            else { $name = str_between ($end, "a> отправлен на ", "<a "); $report[$idx."mt"] = 1; }
            $report[$idx."nt"] = "";
            $report[$idx."pt"] = 16;
        }
        else if ($dir == 0x11)
        {
            parseCoords ($begin, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
            parseCoords ($end, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
            if (strpos ($begin, 'a> возвратится с планеты')) { $name = str_between ($begin, "a> возвратится с планеты ", "<a "); $report[$idx."mt"] = 0; }
            else { $name = str_between ($begin, "a> возвратится с ", "<a "); $report[$idx."mt"] = 1; }
            $report[$idx."nt"] = "";
            $report[$idx."pt"] = 16;
            if (strpos ($end, 'a> на планету')) { $name = str_between ($end, "a> на планету ", "<a "); $report[$idx."ms"] = 0; }
            else { $name = str_between ($end, "a> на ", "<a "); $report[$idx."ms"] = 1; }
            $report[$idx."ns"] = trim ($name[0]);
        }
    }
    else if ($task == 20)    // Ракетная атака
    {
        parseCoords ($begin, &$report[$idx."gs"], &$report[$idx."ss"], &$report[$idx."ps"]);
        parseCoords ($end, &$report[$idx."gt"], &$report[$idx."st"], &$report[$idx."pt"]);
        if (strpos ($begin, ') с планеты ')) { $name = str_between ($begin, ") с планеты ", "<a "); $report[$idx."ms"] = 0; }
        else { $name = str_between ($begin, ") с ", "<a "); $report[$idx."ms"] = 1; }
        $report[$idx."ns"] = trim ($name[0]);
        if (strpos ($end, 'a> на планету ')) { $name = str_between ($end, "a> на планету ", "<a "); $report[$idx."mt"] = 0; }
        else { $name = str_between ($end, "a> на  ", "<a "); $report[$idx."mt"] = 1; }
        $report[$idx."nt"] = trim ($name[0]);
    }
}

// Имя игрока.
function parsePlayer ($s, $idx)
{
    global $report;
    if ($report[$idx."task"] == 6 && $report[$idx."dir"] == 2 && $report[$idx."assign"] >= 1)        // Чужой флот удерживает.
    {
        $name = str_between ($s, ">", "<a href");
        $report[$idx."owner"] = trim ($name[0]);
    }
    else
    {
        $name = str_between ($s, "a> игрока ", "<a href");
        if ($name == FALSE)
        {
            if ($report['Overview']) $report[$idx."owner"] = $report["Who"];
            else $report[$idx."owner"] = '';
        }
        else $report[$idx."owner"] = trim ($name[0]);
    }
}

// Разобрать очередной флот и поместить его в массив 'report'.
function parseSpan ($span, $notags)
{
    global $report;
    $tasklist = array ("Атаковать", "Транспорт", "Оставить", "Шпионаж", "Переработать", "Держаться", "Уничтожить", "Колонизировать", "Экспедиция", "Совместная атака");
    $idx = "Fleet".$report['Fleets']."_";

    $report[$idx."gs"] = $report[$idx."ss"] = $report[$idx."ps"] = $report[$idx."gt"] = $report[$idx."st"] = $report[$idx."pt"]  = 0;    
    $report[$idx."ms"] = $report[$idx."mt"] = 0;

    if (strpos ($span, '>Ракетная атака'))
    {
        if (strpos ($span, "span class=\\'missile\\'>") ) { $report[$idx."task"] = 20; $report[$idx."assign"] = 1; }
        if (strpos ($span, "span class=\\'ownmissile\\'>") ) { $report[$idx."task"] = 20; $report[$idx."assign"] = 0; }
        parseTarget ($span, $idx);
        for ($n=202; $n<216; $n++) $report[$idx.$n] = 0;
        $amount = str_between ($notags, "(", ")");
        $report[$idx."202"] = $amount[0];
        parsePlanets ($span, $idx);
        if ($report[$idx."assign"] == 0) $report[$idx."owner"] = $report["Who"];
    }
    else
    {
        parseFleet ($span, $idx);
        foreach ($tasklist as $i => $value)
        {
            if (strpos ($notags, "Задание: ".$value)) { $report[$idx."task"] = $i+1; break; }
        }
        if (strpos ($notags, "приказа \\\"Экспедиция")) $report[$idx."task"] = 9;
        if (strpos ($span, "span class=\\'attack\\'>") ) $report[$idx."task"] = 11;    // Паровоз САБа.
        if (strpos ($span, "span class=\\'ownattack\\'>") ) $report[$idx."task"] = 11;
        parseDirAssign ($span, $idx);
        parsePlanets ($span, $idx);
        parseCargo ($span, $idx);
        parsePlayer ($span, $idx);
    }
}

// Ищет начало следующей строки события, разбирает текст и помещает его в массив 'report'.
// Вовращает остаток текста, или FALSE если событий больше не найдено.
function parseBlock ($s)
{
    global $report, $debug;

    // Найти div с id=bxx.
    $pos = strpos ($s, "id=\'bxx");
    if ($pos == FALSE) return FALSE;
    
    // Вырезать весь текст.
    $block = substr ($s, $pos);
    $end = strpos ($block, "</tr>");
    $block = substr ($block, 0, $end);
    $notags = strip_tags ($block);
    if ($debug) echo $notags . "<br>";

    $idx = "Event".$report['Events']."_";
    $tmp = strstr ($block, "title=");    // Время завершения
    $report[$idx."sec"] = parseInt ($tmp);
    $tmp = strstr ($block, "star=");
    $report[$idx."time"] = parseInt ($tmp);

    // Вырезать флоты.
    $old = $pos;
    $fcount = 0;
    while (1)
    {
        $pos = strpos ($block, "<span");
        if ($pos == FALSE) break;
        $span = substr ($block, $pos);
        $end = strpos ($span, "</span>");
        $span = substr ($span, 0, $end);
        $notags = strip_tags ($span);
        if (strlen ($notags) < 32)        // Пропустить мелкие span.
        {
            $block = substr ($block, $pos+3);
            continue;
        }

        // Обработать флот.
        parseSpan ($span, $notags);
        $report[$idx."F".$fcount] = $report['Fleets'];
        $report['Fleets']++;
        $fcount++;
        
        $block = substr ($block, $pos+3);
    }
    $pos = $old;
    $report[$idx."fleets"] = $fcount;

    return substr ($s, $pos+16);
}

/*
 ****************************************************************
 * Сгенерировать сырой текст события.
*/

function genraw ()
{
    global $report;

    if ($report['Events'] == 0) return FALSE;

    $res  = "Date {".time()."}\r\n";
    $res .= "From {".$report['From_g']." ".$report['From_s']." ".$report['From_p']." [".$report['From_name']."]}\r\n";
    $res .= "Who {[".$report['Who']."]}\r\n";
    $res .= "Overview {".$report['Overview']."}\r\n\r\n";

    // События.
    for ($i=0; $i<$report['Events']; $i++)
    {
        $idx = "Event".$i."_";
        $res .= "Event" . $i . " { " . $report[$idx."sec"] . " " . $report[$idx."time"] . " ";
        for ($fl=0; $fl<$report[$idx."fleets"]; $fl++) $res .= $report[$idx."F".$fl] . " ";
        $res .= "}\r\n";
    }

    // Флоты.
    $res .= "\r\n";
    for ($i=0; $i<$report['Fleets']; $i++)
    {
        $idx = "Fleet".$i."_";
        $res .= "<! " . strip_tags (FleetSpan ($i)) . " !>\r\n";
        $res .= "Fleet" . $i . " { [" . $report[$idx."owner"] . "] " . $report[$idx."task"] . " " . $report[$idx."assign"] . " " . $report[$idx."dir"] . " <! F !>";
        for ($n=202; $n<216; $n++) $res .= $report[$idx.$n] . " ";
        $res .= $report[$idx."gs"] . " " . $report[$idx."ss"] . " " . $report[$idx."ps"] . " " . $report[$idx."ms"] . " [" . $report[$idx."ns"] . "] ";
        $res .= $report[$idx."gt"] . " " . $report[$idx."st"] . " " . $report[$idx."pt"] . " " . $report[$idx."mt"] . " [" . $report[$idx."nt"] . "] ";
        $res .= $report[$idx."m"] . " " . $report[$idx."k"] . " " . $report[$idx."d"] . " ";
        $res .= "}\r\n";
    }

    return $res;
}

/*
 ****************************************************************
 * Скины.
*/

$Skin = array ();

$Skin[0]['name'] = "По умолчанию";
$Skin[0]['path'] = "../evolution/";
$Skin[1]['name'] = "Vista Aero";
$Skin[1]['path'] = "http://ogamespec.com/skin/Vista_Aero/";
$Skin[2]['name'] = "Phyve Lite";
$Skin[2]['path'] = "http://www.freewebs.com/phyve/ogamelite/";
$Skin[3]['name'] = "Phyve Animated";
$Skin[3]['path'] = "http://www.freewebs.com/phyve/ogame/";
$Skin[4]['name'] = "Born To Recycle";
$Skin[4]['path'] = "http://ogamespec.com/skin/BTRv77/";
$Skin[5]['name'] = "FirstSoul-V2";
$Skin[5]['path'] = "http://www.sebe.us/ogame/FirstSoul-V2/";
$Skin[6]['name'] = "Lunaris";
$Skin[6]['path'] = "http://ogamespec.com/skin/lunaris/";
$Skin[7]['name'] = "uLtravioLet";
$Skin[7]['path'] = "http://ogamespec.com/skin/uV/";
$Skin[8]['name'] = "Manger";
$Skin[8]['path'] = "http://ogamespec.com/skin/manger2/";
$Skin[9]['name'] = "Aquila";
$Skin[9]['path'] = "http://lyistra.net/library/ogame/skins/aquila/";
$Skin[10]['name'] = "Cataclysm";
$Skin[10]['path'] = "http://ogamespec.com/skin/Cataclysm/";
$Skin[11]['name'] = "Planets";
$Skin[11]['path'] = "http://digilander.libero.it/crowdp/OGame/Planets1024x768/";
$Skin[12]['name'] = "Reloaded";
$Skin[12]['path'] = "http://ogamespec.com/skin/reloaded/";
$Skin[13]['name'] = "lightgold";
$Skin[13]['path'] = "http://ogamespec.com/skin/lightgold/";
$Skin[14]['name'] = "EvolutionNeoUltimate";
$Skin[14]['path'] = "http://www.neogame.dk/EvolutionNeoUltimate/";
$Skin[15]['name'] = "EpicBlue";
$Skin[15]['path'] = "http://80.237.203.201/download/use/epicblue/";

$Skins = 16;

function DrawSkinSelect ()
{
    global $Skin, $Skins;
    $res = "";
    for ($i=0; $i<$Skins; $i++) $res .= "<option value='".$Skin[$i]['path']."'>".$Skin[$i]['name']."</option>\n";
    return $res;
}

// Генерация HTML-текста доклада.
// *******************

// http://websim.speedsim.net?lang=ru&enemy_name=zvereboy&enemy_pos=1:260:7&ship_d0_0_b=5
function SpeedSimURL ($idx)
{
    global $report, $websim;
    if ($websim == 0) return "'#'";
    $res = "'http://websim.speedsim.net?lang=ru";
    $res .= "&enemy_name=" . $report[$idx.'owner'];
    $res .= "&enemy_pos=".$report[$idx."gs"].":".$report[$idx."ss"].":".$report[$idx."ps"];
    for ($n=202; $n<216; $n++) 
    {
        $num = $report[$idx.$n];
        if ($num > 0) $res .= "&ship_d0_".($n-202)."_b=" . $num;
    }
    $res .= "' target='_blank' ";
    return $res;
}

function OverFleet ($idx)
{
    global $report, $desc;
    $total = 0;
    for ($n=202; $n<216; $n++) $total += $report[$idx.$n];
    $res = "return overlib(\"&lt;font color=white&gt;&lt;b&gt;Численность кораблей: " . $total . " &lt;br&gt;";
    for ($n=202; $n<216; $n++) 
    {
        $num = $report[$idx.$n];
        if ($num > 0) $res .= $desc[$n] . " " . $num . "&lt;br&gt;";
    }
    $res .= "&lt;/b&gt;&lt;/font&gt;\");";
    return $res;
}

function TitleFleet ($idx)
{
    global $report, $desc, $showfleet;
    if ($showfleet == 0) return "";
    $res = " ";
    for ($n=202,$f=0; $n<216; $n++) 
    {
        $num = $report[$idx.$n];
        if ($num > 0)
        {
            if ($f == 0)
            {
                $res .= "<font color=white>(" . $desc[$n] . ": " . $num;
                $f = 1;
            }
            else $res .= " " . $desc[$n] . ": " . $num;
        }
    }
    $res .= ")</font>";
    return $res;
}

function Cargo ($idx, $task)
{
    global $report, $showfleet;
    $cargo = $report[$idx."m"] + $report[$idx."k"] + $report[$idx."d"];
    if ($cargo > 0)
    {
        $res = "<a href='#' onmouseover='return overlib(\"&lt;font color=white&gt;&lt;b&gt;Транспорт: &lt;br /&gt; Металл: ".nicenum($report[$idx."m"])."&lt;br /&gt;Кристалл: ".nicenum($report[$idx."k"])."&lt;br /&gt;Дейтерий: ".nicenum($report[$idx."d"])."&lt;/b&gt;&lt;/font&gt;\");' onmouseout='return nd();' class='ownespionage'>";
        $res .= $task . "</a>";
        if ($showfleet) $res .= " <font color=white>(Транспорт: Металл: ".nicenum($report[$idx."m"])." Кристалл: ".nicenum($report[$idx."k"])." Дейтерий: ".nicenum($report[$idx."d"]).")</font>";
    }
    else $res = $task;
    return $res;
}

function PlanetFrom ($idx, $start)
{
    global $report;
    $res = "планеты";
    if ($report[$idx."m".$start]) $res = "";    // (Луна)
    return $res;
}

function PlanetTo ($idx, $start)
{
    global $report;
    $res = "планету";
    if ($report[$idx."m".$start]) $res = "";    // (Луна)
    return $res;
}

function FleetSpan ($n)
{
    global $report, $desc;
    $idx = "Fleet".$n."_";
    $task = $report[$idx."task"];
    $assign = $report[$idx."assign"];
    $dir = $report[$idx."dir"];
    $dir = $dir | ($assign << 4);
    $owner = $report[$idx."owner"];
    $start = $report[$idx."ns"]." [".$report[$idx."gs"].":".$report[$idx."ss"].":".$report[$idx."ps"]."]";
    $target = $report[$idx."nt"]." [".$report[$idx."gt"].":".$report[$idx."st"].":".$report[$idx."pt"]."]";

    if ($task == 1)            // Атаковать
    {
        if ($dir == 0) $res =  "<span class='flight ownattack'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownattack'>флот</a>".TitleFleet($idx)." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Атаковать")."</span>";
        else if ($dir == 1) $res =  "<span class='return ownattack'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownattack'>флот</a>".TitleFleet($idx).", отправленный с ".PlanetFrom($idx,"t")." ".$target.", возвращается на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Атаковать")."</span>";
        else if ($dir == 0x10) $res = "<span class='flight attack'>Боевой <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='attack'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Атаковать")."</span>";
        else if ($dir == 0x11) $res =  "<span class='return phalanx_fleet'>Боевой <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='phalanx_fleet'>флот</a>".TitleFleet($idx)." возвратится с ".PlanetFrom($idx,"t")." ".$target." на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Атаковать")."</span>";
    }
    else if ($task == 2)        // Транспорт
    {
        if ($dir == 0) $res =  "<span class='flight owntransport'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='owntransport'>флот</a>".TitleFleet($idx)." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Транспорт")."</span>";
        else if ($dir == 1) $res =  "<span class='return owntransport'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='owntransport'>флот</a>".TitleFleet($idx).", отправленный с ".PlanetFrom($idx,"t")." ".$target.", возвращается на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Транспорт")."</span>";
        else if ($dir == 0x10) $res =  "<span class='flight transport'>Мирный <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='transport'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Транспорт")."</span>";
        else if ($dir == 0x11) $res =  "<span class='return phalanx_fleet'>Мирный <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='phalanx_fleet'>флот</a>".TitleFleet($idx)." возвратится с ".PlanetFrom($idx,"t")." ".$target." на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Транспорт")."</span>";
    }
    else if ($task == 3)        // Оставить.
    {
        if ($dir == 0) $res =  "<span class='flight owndeploy'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='owndeploy'>флот</a>".TitleFleet($idx)." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Оставить")."</span>";
        else if ($dir == 1) $res =  "<span class='return owndeploy'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='owndeploy'>флот</a>".TitleFleet($idx).", отправленный с ".PlanetFrom($idx,"t")." ".$target.", возвращается на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Оставить")."</span>";
        else if ($dir == 0x10) $res =  "<span class='flight phalanx_fleet'>Мирный <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='phalanx_fleet'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Оставить")."</span>";
    }
    else if ($task == 4)        // Шпионаж.
    {
        if ($dir == 0) $res = "<span class='flight ownespionage'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownespionage'>флот</a>".TitleFleet($idx)." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Шпионаж")."</span>";
        else if ($dir == 1) $res = "<span class='return ownespionage'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownespionage'>флот</a>".TitleFleet($idx).", отправленный с ".PlanetFrom($idx,"t")." ".$target.", возвращается на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Шпионаж")."</span>";
        else if ($dir == 0x10) $res = "<span class='flight espionage'>Боевой <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='espionage'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Шпионаж")."</span>";
    }
    else if ($task == 5)        // Переработать.
    {
        if ($dir == 0) $res = "<span class='flight ownharvest'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownharvest'>флот</a>".TitleFleet($idx)." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".$target.". Задание: ".Cargo($idx, "Переработать")."</span>";
        else if ($dir == 1) $res = "<span class='return ownharvest'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownharvest'>флот</a>".TitleFleet($idx).", отправленный с  ".$target.", возвращается на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Переработать")."</span>";
        else if ($dir == 0x11) $res =  "<span class='return phalanx_fleet'>Мирный <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='phalanx_fleet'>флот</a>".TitleFleet($idx)." возвратится с  ".$target." на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Переработать")."</span>";
    }
    else if ($task == 6)        // Держаться.
    {
        if ($dir == 0) $res = "<span class='flight ownhold'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownhold'>флот</a>".TitleFleet($idx)." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Держаться")."</span>";
        else if ($dir == 1) $res = "<span class='return ownhold'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownhold'>флот</a>".TitleFleet($idx).", отправленный с ".PlanetFrom($idx,"t")." ".$target.", возвращается на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Держаться")."</span>";
        else if ($dir == 2) $res = "<span class='holding ownhold'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownhold'>флот</a>".TitleFleet($idx)." отправленный с ".PlanetFrom($idx,"s")." ".$start." находится на орбите ".PlanetFrom($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Держаться")."</span>";
        else if ($dir == 0x10) $res = "<span class='flight phalanx_fleet'>Мирный <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='phalanx_fleet'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на  ".PlanetFrom($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Держаться")."</span>";
        else if ($dir == 0x11) $res = "<span class='return phalanx_fleet'>Мирный <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='phalanx_fleet'>флот</a>".TitleFleet($idx)." возвратится с  ".$target." на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Держаться")."</span>";
        else if ($dir == 0x12) $res = "<span class='holding phalanx_fleet'>".$owner." удерживает альянсовый <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='phalanx_fleet'>флот</a>".TitleFleet($idx)." с ".PlanetFrom($idx,"s")." ".$start." на орбите ".PlanetFrom($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Держаться")."</span>";
        else if ($dir == 0x20) $res = "<span class='flight hold'>Мирный <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='hold'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на  ".PlanetFrom($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Держаться")."</span>";
        else if ($dir == 0x22) $res = "<span class='holding hold'>".$owner." удерживает альянсовый <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='hold'>флот</a>".TitleFleet($idx)." с ".PlanetFrom($idx,"s")." ".$start." на орбите ".PlanetFrom($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Держаться")."</span>";
    }
    else if ($task == 7)            // Уничтожить
    {
        if ($dir == 0) $res = "<span class='flight owndestroy'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='owndestroy'>флот</a>".TitleFleet($idx)." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Уничтожить")."</span>";
        else if ($dir == 1) $res = "<span class='return owndestroy'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='owndestroy'>флот</a>".TitleFleet($idx).", отправленный с ".PlanetFrom($idx,"t")." ".$target.", возвращается на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Уничтожить")."</span>";
        else if ($dir == 0x10) $res = "<span class='flight destroy'>Боевой <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='destroy'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Уничтожить")."</span>";
        else if ($dir == 0x20) $res = "<span class='flight destroy'>Боевой <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='destroy'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Уничтожить")."</span>";
    }
    else if ($task == 8)        // Колонизировать
    {
        if ($dir == 0) $res = "<span class='flight owncolony'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='owncolony'>флот</a>".TitleFleet($idx)." с ".PlanetFrom($idx,"s")." ".$start." отправлен на позицию ".$target.". Задание: ".Cargo($idx, "Колонизировать")."</span>";
        else if ($dir == 1) $res = "<span class='return owncolony'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='owncolony'>флот</a>".TitleFleet($idx).", отправленный с позиции ".$target.", возвращается на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Колонизировать")."</span>";
        else if ($dir == 0x11) $res = "<span class='return phalanx_fleet'>Мирный <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='phalanx_fleet'>флот</a>".TitleFleet($idx)." возвратится с ".$target." на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Колонизировать")."</span>";
    }
    else if ($task == 9)        // Экспедиция.
    {
        if ($dir == 0) $res = "<span class='flight ownexpedition'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownexpedition'>флот</a>".TitleFleet($idx)." отправленный с ".PlanetFrom($idx,"s")." ".$start." достигает позиции ".$target.". Задание: ".Cargo($idx, "Экспедиция")."</span>";
        else if ($dir == 2) $res = "<span class='holding ownexpedition'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownexpedition'>флот</a>".TitleFleet($idx)." отправленный с ".PlanetFrom($idx,"s")." ".$start." исследует позицию ".$target.". Задание: ".Cargo($idx, "Экспедиция")."</span>";
        else if ($dir == 1) $res = "<span class='return ownexpedition'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownexpedition'>флот</a>".TitleFleet($idx)." возвращается на ".PlanetTo($idx,"s")." ".$start." после приказа ".Cargo($idx, "\"Экспедиция\"")."</span>";
        else if ($dir == 0x10) $res = "<span class='flight phalanx_fleet'>Мирный <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='phalanx_fleet'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на позицию ".$target.". Задание: ".Cargo($idx, "Экспедиция")."</span>";
        else if ($dir == 0x12) $res = "<span class='holding phalanx_fleet'>Мирный <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='phalanx_fleet'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." исследует позицию ".$target.". Задание: ".Cargo($idx, "Экспедиция")."</span>";
        else if ($dir == 0x11) $res = "<span class='return phalanx_fleet'>Мирный <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='phalanx_fleet'>флот</a>".TitleFleet($idx)." возвратится с позиции ".$target." на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Экспедиция")."</span>";
    }
    else if ($task == 10)    // Совместная атака.
    {
        if ($dir == 0) $res =  "<span class='federation'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownfederation'>флот</a>".TitleFleet($idx)." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Совместная атака")."</span>";
        else if ($dir == 1) $res =  "<span class='return ownfederation'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownfederation'>флот</a>".TitleFleet($idx).", отправленный с ".PlanetFrom($idx,"t")." ".$target.", возвращается на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Совместная атака")."</span>";
        else if ($dir == 0x10) $res = "<span class='federation'>Боевой <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='federation'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Совместная атака")."</span>";
        else if ($dir == 0x11) $res = "<span class='return phalanx_fleet'>Боевой <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='phalanx_fleet'>флот</a>".TitleFleet($idx)." возвратится с ".PlanetFrom($idx,"t")." ".$target." на ".PlanetTo($idx,"s")." ".$start.". Задание: ".Cargo($idx, "Совместная атака")."</span>";
        else if ($dir == 0x20) $res = "<span class='ownfederation'>Альянсовый <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownfederation'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Совместная атака")."</span>";
    }
    else if ($task == 11)     // Атаковать САБ (паровоз).
    {
        if ($dir == 0) $res =  "<span class='attack'>Ваш <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownattack'>флот</a>".TitleFleet($idx)." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Атаковать")."</span>";
        else if ($dir == 0x10) $res = "<span class='attack'>Боевой <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='attack'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Атаковать")."</span>";
        else if ($dir == 0x20) $res = "<span class='ownattack'>Альянсовый <a href=".SpeedSimURL($idx)." onmouseover='".OverFleet($idx)."' onmouseout='return nd();' class='ownattack'>флот</a>".TitleFleet($idx)." игрока ".$owner." с ".PlanetFrom($idx,"s")." ".$start." отправлен на ".PlanetTo($idx,"t")." ".$target.". Задание: ".Cargo($idx, "Атаковать")."</span>";
    }
    else if ($task == 20)        // Ракетная атака.
    {
        $dir = $report[$idx."dir"];
        $mistarget = "";
        if ($dir) $mistarget = "Основная цель ".$desc[$dir];
        if ($assign == 0) $res = "<span class='ownmissile'>";
        else if ($assign == 1) $res = "<span class='missile'>";
        $res .= "Ракетная атака (".$report[$idx."202"].") с ".PlanetFrom($idx,"s")." ".$start." на ".PlanetTo($idx,"t")." ".$target." ".$mistarget."</span>";
    }

    return $res;
}

function AsOverview ()
{
    global $report;
    echo "<tr><td class='c' colspan='4'>\n";
    echo "Список событий на ".$report['From_name']." [".$report['From_g'].":".$report['From_s'].":".$report['From_p']."] (".$report['Who'].")  </td></tr>\n\n";

    for ($i=0; $i<$report['Events']; $i++)
    {
        $idx = "Event".$i."_";
        $num = $report[$idx."F0"];
        $dir = $report["Fleet".$num."_dir"];
        if ($report[$idx."fleets"] > 1) echo "<tr class=''>\n";
        else if ($dir == 0) echo "<tr class='flight'>\n";
        else if ($dir == 1) echo "<tr class='return'>\n";
        else if ($dir == 2) echo "<tr class='holding'>\n";
        echo "<th><div id='bxx".($i+1)."' title='".$report[$idx."sec"]."' star='".$report[$idx."time"]."'></div></th>\n";
        echo "<th colspan='3'>";
        for ($fl=0; $fl<$report[$idx."fleets"]; $fl++)
        {
            $num = $report[$idx."F".$fl];
            echo FleetSpan ($num);
            if ($report[$idx."fleets"] > 1) echo "\n<br /><br />";
        }
        echo "</th></tr>\n\n";
    }
}

function AsMoonScan ()
{
    global $report;
    echo "<tr><td class='c' colspan='5'>\n";
    echo "Доклад сенсора с луны на координатах [".$report['From_g'].":".$report['From_s'].":".$report['From_p']."] (".$report['Who'].")  </td></tr>\n\n";

    for ($i=0; $i<$report['Events']; $i++)
    {
        $idx = "Event".$i."_";
        $num = $report[$idx."F0"];
        $dir = $report["Fleet".$num."_dir"];
        if ($report[$idx."fleets"] > 1) echo "<tr class=''>\n";
        else if ($dir == 0) echo "<tr class='flight'>\n";
        else if ($dir == 1) echo "<tr class='return'>\n";
        else if ($dir == 2) echo "<tr class='holding'>\n";
        echo "<th>".($i+1)."</th>\n";
        echo "<th><div id='bxx".($i+1)."' title='".$report[$idx."sec"]."' star='".$report[$idx."time"]."'></div></th>\n";
        echo "<th colspan='3'>";
        for ($fl=0; $fl<$report[$idx."fleets"]; $fl++)
        {
            $num = $report[$idx."F".$fl];
            echo FleetSpan ($num);
            if ($report[$idx."fleets"] > 1) echo "\n<br /><br />";
        }
        echo "</th></tr>\n\n";
    }
}

function genreport ()
{
    global $report;
    echo "<div id='phalanx_content'><br><br><center><table width='519'>\n";
    if ($report['Overview']) AsOverview ();
    else AsMoonScan ();
    echo "<script language=javascript>anz=".$report['Events'].";t();</script>\n";
    echo "</table></center></div>\n";
}

function pagetitle ($s)
{
    echo "<script language='JavaScript'>\n";
    echo "document.title = '".$s."';\n";
    echo "</script>\n\n";
}

function TimeZone ()
{
    $res = "";
    $res .= "<option value='-12'>(GMT-12:00) Меридиан смены дат (запад)</option>\n";
    $res .= "<option value='-11'>(GMT-11:00) о. Мидуэй, Самоа</option>\n";
    $res .= "<option value='-10'>(GMT-10:00) Гавайи</option>\n";
    $res .= "<option value='-9'>(GMT-9:00) Аляска</option>\n";
    $res .= "<option value='-8'>(GMT-8:00) Тихоокеанское время (США), Тихуана</option>\n";
    $res .= "<option value='-7'>(GMT-7:00) Горное время (США), Аризона, Чихуахуа</option>\n";
    $res .= "<option value='-6'>(GMT-6:00) Гвадалахара, Мехико, Центральное время (США)</option>\n";
    $res .= "<option value='-5'>(GMT-5:00) Восточное время (США), Богота, Лима</option>\n";
    $res .= "<option value='-4'>(GMT-4:00) Атлантическое время (Канада), Сантьяго</option>\n";
    $res .= "<option value='-3'>(GMT-3:00) Ньюфанудленд, Бразилия, Гренландия</option>\n";
    $res .= "<option value='-2'>(GMT-2:00) Среднеатлантическое время</option>\n";
    $res .= "<option value='-1'>(GMT-1:00) Азорские о-ва, о-ва Зеленого мыса</option>\n";
    $res .= "<option value='0'>(GMT) Время по Гринвичу</option>\n";
    $res .= "<option value='1' style='color: lime;'>(GMT+1:00) Время сервера, Западная Европа</option>\n";
    $res .= "<option value='2'>(GMT+2:00) Восточная Европа, Греция, Киев</option>\n";
    $res .= "<option value='3'>(GMT+3:00) Москва, Санкт-Петербург, Волгоград</option>\n";
    $res .= "<option value='4'>(GMT+4:00) Баку, Ереван, Тбилиси</option>\n";
    $res .= "<option value='5'>(GMT+5:00) Екатеринбург, Ташкент, Бомбей</option>\n";
    $res .= "<option value='6'>(GMT+6:00) Новосибирск, Алма-Ата</option>\n";
    $res .= "<option value='7'>(GMT+7:00) Красноярск, Бангкок, Ханой</option>\n";
    $res .= "<option value='8'>(GMT+8:00) Иркутск, Гонконг, Пекин</option>\n";
    $res .= "<option value='9'>(GMT+9:00) Якутск, Сеул, Токио</option>\n";
    $res .= "<option value='10'>(GMT+10:00) Владивосток, Мельбурн, Сидней</option>\n";
    $res .= "<option value='11'>(GMT+11:00) Магадан, Сахалин</option>\n";
    $res .= "<option value='12'>(GMT+12:00) Камчатка, Фиджи</option>\n";
    $res .= "<option value='13'>(GMT+13:00) Нуку-алофа</option>\n";
    return $res;
}

function OptionsPane ($mainpage)
{
    if ($mainpage)
    {
        echo "<div id='optpane'>\n";
        echo "<br><center><table><tr><th>\n";
        echo "<form name='option'>\n";
        echo "<table width='500px'>\n";
        echo "<tr><td class='c' colspan=2>Интерфейс</td></tr>\n";
        echo "<tr><td>Выберите стиль:</td><td><select name='skinpath' onchange='onSkinChange();'>" . DrawSkinSelect() . "</select></td></tr>\n";
        echo "<tr><td><a title='Дописывать текст всплывающих окон для флота и Транспорта, чтобы его можно было скопировать'>Разворот флота:</a></td><td><input type=checkbox name='showfleet' onclick='onShowFleet();'></td></tr>\n";
        echo "<tr><td><a title='При нажатии на слово «флот» происходит переход на Вебсим'>Ссылка на Вебсим:</a></td><td><input type=checkbox name='websim' onclick='onWebSim();'></td></tr>\n";
        echo "<tr height='16px'></tr>\n";
        echo "<tr><td class='c' colspan=2>Настройки времени</td></tr>\n";
        echo "<tr><td>Часовой пояс:</td><td><select name='tz' onchange='onTimeZone();'>".TimeZone()."</select></td></tr>\n";
        echo "<tr><td><a href='#' title='+1 час'>Летнее время:</a></td><td><input type=checkbox name='summertime' onclick='onSummerTime();'></td></tr>\n";
        echo "<tr height='16px'></tr>\n";
        echo "<tr><td class='c' colspan=2>Бета-тестирование</td></tr>\n";
        echo "<tr><td><a title='Включите эту опцию, если вы обнаружили ошибку, и тогда все доклады будут отправляться разработчику по email.'>Отправить доклад:</a></td><td><input type=checkbox name='sendmail2' onclick='onSendMail();'></td></tr>\n";
        echo "</table></form></th></tr></table></center></div>\n";
    }
    else
    {
        echo "<div id='optpane' style='position: absolute; top:10px; right:10px;'>\n";
        echo "<form name='option'>\n";
        echo "Скин: <select name='skinpath' onchange='onSkinChange();'>\n";
        echo DrawSkinSelect();
        echo "</select></form>\n";
        echo "</div>\n";
    }
}

function footer ($title, $id)
{
    // Ссылка на доклад.
    global $ScriptAddr;
    if ($id != FALSE)
    {
        echo "<br><center><table width='519'><tr><td class='c'><a href=\"phalanx.php?id=".$id."\">Ссылка на доклад:</a></td></tr>\n";
        echo "<tr><th><input onclick='this.select();' style='width: 100%;' size=120 value='[url=".$ScriptAddr."?id=".$id."]". $title ."[/url]' type='text'></th></tr>\n";
        echo "<tr><th><input onclick='this.select();' style='width: 100%;' size=120 value='".$ScriptAddr."?id=".$id."' type='text'></th></tr></table></center>\n\n";
    }
    echo "<!-- Доклад Фаланги/Обзора. Конец. -->\n\n";

    // Настройки.
    OptionsPane ($id == FALSE);

    echo "</BODY>\n";
    echo "</HTML>\n";
}

// Обработка параметров
// *******************

{
    if (!key_exists("id", $_GET)) $id = 0;
    else $id = $_GET['id'];
    if (!key_exists("text", $_POST)) $text = 0;
    else $text = $_POST['text'];

    if ($text || $id) ConnectDatabase ();

    // Обработка и сохранение доклада.
    if ($text)
    {
        if (!key_exists("debug", $_POST)) $debug = 0;
        else $debug = $_POST['debug'];
        if (!key_exists("sendmail", $_POST)) $sendmail = 0;
        else $sendmail = $_POST['sendmail'];

        if ($sendmail)
        {
            echo "Письмо отправлено.<br>\n";
            mail_utf8 ('ogamespec@gmail.com', 'Доклад фаланги', $text);
        }

        if (get_magic_quotes_gpc () == 0) $text = addslashes ($text);

        getAuthority ($text);
        $report['Events'] = $report['Fleets'] = 0;
        while ($text = parseBlock ($text)) $report['Events']++;

        DebugReport ();

        $raw = genraw ();
        if ($raw != FALSE) $id = savereport ( $raw );
    }

    // Вывод доклада.
    if ($id)
    {
        if (!key_exists("debug", $_GET)) $debug = 0;
        else $debug = $_GET['debug'];
        if (loadreport ($id) == true)
        {
            parsesource ();
            pagetitle ($report['Title']);
            genreport ();
            footer ($report['Title'], $id);
            ob_end_flush ();
            exit ();
        }
    }
}

?>

<table width="99%"><tr><td><center>
<br>
<br>
<form action="phalanx.php" method=post name="sendform">
<input type="hidden" name="debug" value="0">
<input type="hidden" name='sendmail' value="0">
<table>
<tr><td><b><small>
Чтобы добавить доклад, правой кнопкой мыши нажмите на окно с докладом фаланги или Обзором, выберите "Исходный код HTML", скопируйте весь текст и вставьте в поле.<br>
</small></b></td></tr>
<tr><td>&nbsp; </td></tr>
<tr><td class='c'>Вставьте Обзор или доклад фаланги:</td></tr>
<tr><th><textarea cols='150' rows='20' name='text'></textarea></th></tr>
<tr><td><input type=submit value='Обработать'></td></td></tr>
</table>
</form>
</center></td></tr></table>

<?php
footer ("", FALSE);
?>