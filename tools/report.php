<?php
/*
    Обработчик боевых докладов для Ogame 0.84.
    (c) Andorianin, 2009
*/
$version = 0.23;

$skin = $_COOKIE["battle_skin"];
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

?>

<HTML>
<HEAD><link rel="stylesheet" type="text/css" href="<?=$skin?>formate.css">
<meta http-equiv='content-type' content='text/html; charset=utf-8' />
<TITLE>Боевой доклад</TITLE>
<SCRIPT src="../includes/jscripts/overlib.js" type="text/javascript" language="JavaScript"></SCRIPT>
</HEAD>

<BODY onload="onBodyLoad();"><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<!-- Обработчик боевых докладов для Ogame. Версия <?=$version?> -->

<!-- Боевой Доклад. Начало. -->

<script language='JavaScript'>
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
    skin = readCookie ('battle_skin');
    document.skin_form.skinpath.value = skin;
}

function onSkinChange()
{
    createCookie ( 'battle_skin', document.skin_form.skinpath.value, 9999 );
    window.location.reload ();
}

</script>

<script language='JavaScript'>

var RoundsHidden;       // Выставить значение по умолчанию.
if (readCookie ("ShowRoundsToggle") == null) RoundsHidden = false;
else RoundsHidden = readCookie ("ShowRoundsToggle") ^ 1;

function ShowHideRounds ()
{
    if (RoundsHidden) rounds.style.display = "block";
    else rounds.style.display = "none";
    eraseCookie ("ShowRoundsToggle");
    RoundsHidden ^= 1;
    createCookie ("ShowRoundsToggle", RoundsHidden, 300);
}
</script>

<?php

require_once "config.php";
require_once "db.php";

define ("BATTLETABLE", "battle_reports");
define ("FLOODTIME", 1);

$secretword = "SpecnazFlotRulit";
$debug = 1;
$report = array ();
$aobj = $dobj = 0;              // Указатели в списках
$attackers = array ();          // Список атакеров
$defenders = array ();          // Список дефов

// Таблица стоимости

// Флот
$initial[202]['m'] = 2000; $initial[202]['k'] = 2000; $initial[202]['d'] = 0;
$initial[203]['m'] = 6000; $initial[203]['k'] = 6000; $initial[203]['d'] = 0;
$initial[204]['m'] = 3000; $initial[204]['k'] = 1000; $initial[204]['d'] = 0;
$initial[205]['m'] = 6000; $initial[205]['k'] = 4000; $initial[205]['d'] = 0;
$initial[206]['m'] = 20000; $initial[206]['k'] = 7000; $initial[206]['d'] = 2000;
$initial[207]['m'] = 45000; $initial[207]['k'] = 15000; $initial[207]['d'] = 0;
$initial[208]['m'] = 10000; $initial[208]['k'] = 20000; $initial[208]['d'] = 10000;
$initial[209]['m'] = 10000; $initial[209]['k'] = 6000; $initial[209]['d'] = 2000;
$initial[210]['m'] = 0; $initial[210]['k'] = 1000; $initial[210]['d'] = 0;
$initial[211]['m'] = 50000; $initial[211]['k'] = 25000; $initial[211]['d'] = 15000;
$initial[212]['m'] = 0; $initial[212]['k'] = 2000; $initial[212]['d'] = 500;
$initial[213]['m'] = 60000; $initial[213]['k'] = 50000; $initial[213]['d'] = 15000;
$initial[214]['m'] = 5000000; $initial[214]['k'] = 4000000; $initial[214]['d'] =1000000;
$initial[215]['m'] = 30000; $initial[215]['k'] = 40000; $initial[215]['d'] = 15000;

// Оборона.
$initial[401]['m'] = 2000; $initial[401]['k'] = 0; $initial[401]['d'] = 0;
$initial[402]['m'] = 1500; $initial[402]['k'] = 500; $initial[402]['d'] = 0;
$initial[403]['m'] = 6000; $initial[403]['k'] = 2000; $initial[403]['d'] = 0;
$initial[404]['m'] = 20000; $initial[404]['k'] = 15000; $initial[404]['d'] = 2000;
$initial[405]['m'] = 2000; $initial[405]['k'] = 6000; $initial[405]['d'] = 0;
$initial[406]['m'] = 50000; $initial[406]['k'] = 50000; $initial[406]['d'] = 30000;
$initial[407]['m'] = 10000; $initial[407]['k'] = 10000; $initial[407]['d'] = 0;
$initial[408]['m'] = 50000; $initial[408]['k'] = 50000; $initial[408]['d'] = 0;

function ValidateURL ($url)
{
    $urlregex = "^(https?|ftp)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
    if (eregi($urlregex, $url)) return true;
    else return false; 
}

function price ($obj)
{
    global $initial;
    return $initial[$obj]['m'] + $initial[$obj]['k'];
}

function pricem ($obj)
{
    global $initial;
    return $initial[$obj]['m'];
}

function pricek ($obj)
{
    global $initial;
    return $initial[$obj]['k'];
}

function priced ($obj)
{
    global $initial;
    return $initial[$obj]['d'];
}

// Сокращенные названия флота и обороны.
$shortfleet = array ( "М. трансп.", "Б. трансп.", "Л. истр.", "Т. истр.", "Крейсер", "Линк", "Колонизатор", "Переработчик", "Шп. зонд", "Бомб.", "Солн. спутник", "Уничт.", "ЗС", "Лин. Кр." );
$shortdef = array ( "РУ", "Лёг. лазер", "Тяж. лазер", "Гаусс", "Ион", "Плазма", "М. купол", "Б. купол" );

// Используется для вывода восстановленной обороны.
$defstr = array ( "Ракетная установка", "Лёгкий лазер", "Тяжёлый лазер", "Пушка Гаусса", "Ионное орудие", "Малый щитовой купол", "Плазменное орудие", "Большой щитовой купол" );

// ТТХ флота и обороны.
$fleettech = array ( 4000, 10, 5, 12000, 25, 5, 4000, 10, 50, 10000, 25, 150, 
                     27000, 50, 400, 60000, 200, 1000, 
                     30000, 100, 50, 16000, 10, 1, 1000, 0, 0,
                     75000, 500, 1000, 2000, 1, 1, 110000, 500, 2000,
                     9000000, 50000, 200000, 70000, 400, 700 );
$deftech = array ( 2000, 20, 80, 2000, 25, 100, 8000, 100, 250, 35000, 200, 1100,
                   8000, 500, 150, 100000, 300, 3000, 20000, 2000, 1, 100000, 10000, 1 );
$ftech = array ();
$dtech = array ();

// Вместимость флота
$fleetcargo = array ( 5000, 25000, 50, 100, 800, 1500, 7500, 20000, 5, 500, 0, 2000, 1000000, 750 );
// Потребление топлива
$fleetgas = array ( 10, 50, 20, 75, 300, 500, 1000, 300, 1, 1000, 0, 1000, 1, 250 );

function nicenum ($number)
{
    return number_format($number,0,",",".");
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

if (!function_exists('strpbrk')) {
    function strpbrk($haystack, $char_list)
    {
        if (!is_scalar($haystack)) {
            user_error('strpbrk() expects parameter 1 to be string, ' .
                gettype($haystack) . ' given', E_USER_WARNING);
            return false;
        }

        if (!is_scalar($char_list)) {
            user_error('strpbrk() expects parameter 2 to be scalar, ' .
                gettype($needle) . ' given', E_USER_WARNING);
            return false;
        }

        $haystack  = (string) $haystack;
        $char_list = (string) $char_list;

        $len = strlen($haystack);
        for ($i = 0; $i < $len; $i++) {
            $char = substr($haystack, $i, 1);
            if (strpos($char_list, $char) === false) {
                continue;
            }
            return substr($haystack, $i);
        }

        return false;
    }
}

// Защита от флуда.
// Открываем файл, проверяем его дату. Если его возраст меньше FLOODTIME, то нас флудят.
// Возвращает количество секунд до окончания попытки флуда, или 0, если всё в порядке.
function floodprotect ()
{
    $floodfile = "repflood.txt";
    $now = time ();
    $old = filemtime ($floodfile);
    if ( ($now - $old) <= FLOODTIME) return FLOODTIME - ($now - $old);
    $f = fopen ($floodfile, 'w');
    fwrite ($f, $now);
    fclose ($f);
    return 0;
}

define('LOWERCASE',3);
define('UPPERCASE',1);

function detect_cyr_charset($str) {
    $charsets = Array(
                      'k' => 0,
                      'w' => 0,
                      'd' => 0,
                      'i' => 0,
                      'm' => 0
                      );
    for ( $i = 0, $length = strlen($str); $i < $length; $i++ ) {
        $char = ord($str[$i]);
        //non-russian characters
        if ($char < 128 || $char > 256) continue;
        
        //CP866
        if (($char > 159 && $char < 176) || ($char > 223 && $char < 242)) 
            $charsets['d']+=LOWERCASE;
        if (($char > 127 && $char < 160)) $charsets['d']+=UPPERCASE;
        
        //KOI8-R
        if (($char > 191 && $char < 223)) $charsets['k']+=LOWERCASE;
        if (($char > 222 && $char < 256)) $charsets['k']+=UPPERCASE;
        
        //WIN-1251
        if ($char > 223 && $char < 256) $charsets['w']+=LOWERCASE;
        if ($char > 191 && $char < 224) $charsets['w']+=UPPERCASE;
        
        //MAC
        if ($char > 221 && $char < 255) $charsets['m']+=LOWERCASE;
        if ($char > 127 && $char < 160) $charsets['m']+=UPPERCASE;
        
        //ISO-8859-5
        if ($char > 207 && $char < 240) $charsets['i']+=LOWERCASE;
        if ($char > 175 && $char < 208) $charsets['i']+=UPPERCASE;
        
    }
    arsort($charsets);
    return key($charsets);
}

function ConnectDatabase ()
{
    global $db_host, $db_user, $db_pass, $db_name;
    dbconnect ($db_host, $db_user, $db_pass, $db_name);
    dbquery("SET NAMES 'utf8';");
    dbquery("SET CHARACTER SET 'utf8';");
    dbquery("SET SESSION collation_connection = 'utf8_general_ci';");
}

// Расчет потребления дейтерия при полете $num кораблей класса $ship_id от [$gf:$sf:$pf] до [$gt:$st:$pt]
function FuelCons ($gf, $sf, $pf, $gt, $st, $pt, $ship_id, $num)
{
    global $fleetgas;

    // Вычислить расстояние.
    if ($gf == $gt) {
        if ($sf == $st) {
            if ($pf == $pt) $dist = 5;    // в ту же позицию
            else $dist = abs ($pt-$pf) * 5 + 1000;    // по системе
        }
        else $dist = abs ($st-$sf) * 95 + 2700;    // через систему
    }
    else $dist = abs ($gt-$gf) * 20000;    // через галактику

    // Время полёта.
    if ($gf == $gt) {
        if ($sf == $st) {
            if ($pf == $pt) $dist = 5;    // в ту же позицию
            else $dist = abs ($pt-$pf) * 5 + 1000;    // по системе
        }
        else $dist = abs ($st-$sf) * 95 + 2700;    // через систему
    }
    else $dist = abs ($gt-$gf) * 20000;    // через галактику    

    // Время подлета

    return $dist;
}

/*
 ****************************************************************
 * Процессор боевых докладов.
*/

// Инициализировать таблицы параметров флота и обороны.
function inittabs ()
{
    global $fleettech, $deftech, $ftech, $dtech;

    $t = 0; $n = 202;
    foreach ($fleettech as $i=>$val)
    {
        if ($t == 0) $ftech[$n]['str'] = $val;
        if ($t == 1) $ftech[$n]['shld'] = $val;
        if ($t == 2) $ftech[$n]['att'] = $val;
        $t++;
        if ($t == 3) { $t = 0; $n++; }
    }

    $t = 0; $n = 401;
    foreach ($deftech as $i=>$val)
    {
        if ($t == 0) $dtech[$n]['str'] = $val;
        if ($t == 1) $dtech[$n]['shld'] = $val;
        if ($t == 2) $dtech[$n]['att'] = $val;
        $t++;
        if ($t == 3) { $t = 0; $n++; }
    }
}

// Подготовить доклад из SQL-базы для вывода в HTML.
// *******************

// Обработать сырой доклад из базы и разместить всё в удобном виде в массиве $report.
function parsesource ()
{
    global $report;
    $singlevars = array ("Attackers", "Defenders", "Rounds", "Result", "HideDate", "HideCoords", "HideTech" );
    $src = $report['source'];
    $src = str_replace("\r\n", " ", $src);
    $src = str_squeeze ($src);

    // Названия лога.
    $s = strstr ($src, "Title");
    $start = strpos ($s, '{') + 1;
    $end = strpos ($s, '}');
    $report['Title'] = substr ($s, $start, $end-$start);

    // Одиночные переменные.
    foreach ($singlevars as $i=>$val)
    {
        $s = strstr ($src, $val); $s = strstr ($s, "{");
        $tmp = str_between ($s, "{", "}");
        sscanf ( $tmp[0], "%i", $report[$val] );
    }

    // Дата
    $s = strstr ($src, "Date"); $s = strstr ($s, "{");
    $tmp = str_between ($s, "{", "}");
    sscanf ( $tmp[0], "%i %i %i %i %i", 
             $report['date_m'], $report['date_d'], $report['date_hr'], $report['date_min'], $report['date_sec'] );

    // Он получает...
    $s = strstr ($src, "Captured"); $s = strstr ($s, "{");
    $tmp = str_between ($s, "{", "}");
    sscanf ( $tmp[0], "%i %i %i", $report['cm'], $report['ck'], $report['cd'] );

    // ПО
    $s = strstr ($src, "Debris"); $s = strstr ($s, "{");
    $tmp = str_between ($s, "{", "}");
    sscanf ( $tmp[0], "%i %i", $report['dm'], $report['dk'] );

    // Шанс луны и появление луны (0/1)
    $s = strstr ($src, "MoonChance"); $s = strstr ($s, "{");
    $tmp = str_between ($s, "{", "}");
    sscanf ( $tmp[0], "%i %i", $report['moonchance'], $report['moon'] );

    // Восстановленная оборона.
    $s = strstr ($src, "Repair"); $s = strstr ($s, "{");
    $tmp = str_between ($s, "{", "}");
    sscanf ( $tmp[0], "%i %i %i %i %i %i %i %i", 
             $report['r401'], $report['r402'], $report['r403'], $report['r404'],
             $report['r405'], $report['r406'], $report['r407'], $report['r408'] );

    // Список аттакеров и дефендеров.
    for ($i=0; $i<$report['Attackers']; $i++)
    {
        $idx = "a" . $i . "_";
        $s = strstr ($src, "Attacker".$i); $s = strstr ($s, "{");
        $tmp = str_between ($s, "{", "}");
        if ($tmp == false) echo "Error parsing attacker ".$i."<br>";

        $start = strpos ($tmp[0], "(") + 1;
        $end = strpos ($tmp[0], ")");
        $name = substr ($tmp[0], $start, $end-$start);
        $report[$idx."name"] = trim($name);
  
        $start = strpos ($tmp[0], ")");
        $tmp[0] = substr ($tmp[0], $start+1);

        sscanf ( $tmp[0], "%i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i", 
                 $report[$idx."g"], $report[$idx."s"], $report[$idx."p"], 
                 $report[$idx."at"], $report[$idx."sh"], $report[$idx."ar"], 
                 $report[$idx."202"], $report[$idx."203"], $report[$idx."204"], $report[$idx."205"], 
                 $report[$idx."206"], $report[$idx."207"], $report[$idx."208"], $report[$idx."209"], 
                 $report[$idx."210"], $report[$idx."211"], $report[$idx."212"], $report[$idx."213"],
                 $report[$idx."214"], $report[$idx."215"] );
    }

    for ($i=0; $i<$report['Defenders']; $i++)
    {
        $idx = "d" . $i . "_";
        $s = strstr ($src, "Defender".$i); $s = strstr ($s, "{");
        $tmp = str_between ($s, "{", "}");
        if ($tmp == false) echo "Error parsing defender ".$i."<br>";

        $start = strpos ($tmp[0], "(") + 1;
        $end = strpos ($tmp[0], ")");
        $name = substr ($tmp[0], $start, $end-$start);
        $report[$idx."name"] = trim($name);
  
        $start = strpos ($tmp[0], ")");
        $tmp[0] = substr ($tmp[0], $start+1);

        sscanf ( $tmp[0], "%i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i %i", 
                 $report[$idx."g"], $report[$idx."s"], $report[$idx."p"], 
                 $report[$idx."at"], $report[$idx."sh"], $report[$idx."ar"], 
                 $report[$idx."202"], $report[$idx."203"], $report[$idx."204"], $report[$idx."205"], 
                 $report[$idx."206"], $report[$idx."207"], $report[$idx."208"], $report[$idx."209"], 
                 $report[$idx."210"], $report[$idx."211"], $report[$idx."212"], $report[$idx."213"],
                 $report[$idx."214"], $report[$idx."215"],
                 $report[$idx."401"], $report[$idx."402"], $report[$idx."403"], $report[$idx."404"], 
                 $report[$idx."405"], $report[$idx."406"], $report[$idx."407"], $report[$idx."408"] );
    }

    // Раунды.
    for ($i=0; $i<$report['Rounds']; $i++)
    {
        $tokens = array ("afires", "apower", "dabsorb", "dfires", "dpower", "aabsorb" );
        $atokens = array ( "202", "203", "204", "205", "206", "207", "208", "209", "210", "211", "212", "213", "214", "215" );
        $dtokens = array ( "401", "402", "403", "404", "405", "406", "407", "408" );
        $idx = "round" . $i . "_";
        $s = strstr ($src, "Round".$i); $s = strstr ($s, "{");
        $tmp = str_between ($s, "{", "}");
        if ($tmp == false) echo "Error parsing round ".$i."<br>";

        $tok = strtok ($tmp[0], " ");

        foreach ($tokens as $j=>$val)
        {
            $report[$idx.$val] = $tok;
            $tok = strtok (" ");
        }

        for ($f=0; $f<$report['Attackers']; $f++)
        {
            $idx = "round" . $i . "_" . "a" . $f . "_";
            foreach ($atokens as $j=>$val)
            {
                $report[$idx.$val] = $tok;
                $tok = strtok (" ");
            }
            $report[$idx."name"] = $report["a".$f."_name"];
        }

        for ($f=0; $f<$report['Defenders']; $f++)
        {
            $idx = "round" . $i . "_" . "d" . $f . "_";
            foreach ($atokens as $j=>$val) {
                $report[$idx.$val] = $tok;
                $tok = strtok (" ");
            }
            foreach ($dtokens as $j=>$val) {
                $report[$idx.$val] = $tok;
                $tok = strtok (" ");
            }
            $report[$idx."name"] = $report["d".$f."_name"];
        }
    }

    //print_r ($report);
}

// Обработать исходный текст доклада.
// *******************

// Возвращает обработанную строку выстрелов.
function fireworks ($s)
{
    $af = $ap = $ds = $df = $dp = $as;
    $s = strstr ($s, "Атакующий флот делает"); $s = strpbrk ($s, "012345678");
    sscanf ($s, "%d", $af);
    $s = strstr ($s, "общей мощностью"); $s = strpbrk ($s, "012345678");
    sscanf ($s, "%d", $ap);
    $s = strstr ($s, "Щиты обороняющегося поглощают"); $s = strpbrk ($s, "012345678");
    sscanf ($s, "%d", $ds);
    $s = strstr ($s, "Обороняющийся флот делает"); $s = strpbrk ($s, "012345678");
    sscanf ($s, "%d", $df);
    $s = strstr ($s, "общей мощностью"); $s = strpbrk ($s, "012345678");
    sscanf ($s, "%d", $dp);
    $s = strstr ($s, "Щиты атакующего поглощают"); $s = strpbrk ($s, "012345678");
    sscanf ($s, "%d", $as);
    return sprintf ("%d %d %d %d %d %d", $af, $ap, $ds, $df, $dp, $as);
}

// Восстановлено обороны.
function defrepair ($s)
{
    global $shortdef, $defstr;
    $res = "";

    $n = 401;                               // Вернуть правильные слова вместо числовых индексов.
    foreach ($shortdef as $i=>$val)
    {
        $s = str_replace ($n, $val, $s);
        $n++;
    }

    $pos = strripos ($s, "были повреждены и находятся в ремонте");
    if ($pos == 0) return "0 0 0 0 0 0 0 0";

    for ($i=0; $i<8; $i++)
    {
        $pos = strpos ($s, " " . $defstr[$i]);
        if ($pos)
        {
            $ss = substr ($s, 0, $pos);
            $start = strripos ($ss, " ");
            $num = substr ($ss, $start);
        }
        else $num = 0;
        $res .= $num . " ";
    }

    return $res;
}

// Стоимость флота.
function fleetprice ($fleet)
{
    $price = 0;
    $tok = strtok ($fleet, " ");
    for ($i=202; $i<=215; $i++)
    {
        $price += price ($i) * $tok;
        $tok = strtok (" ");
    }
    return $price;
}

// Стоимость обороны.
function defprice ($def)
{
    $price = 0;
    $tok = strtok ($def, " ");
    for ($i=401; $i<=408; $i++)
    {
        $price += price ($i) * $tok;
        $tok = strtok (" ");
    }
    return $price;
}

function nextAttacker ($text, $round)
{
    global $aobj, $attackers, $debug;
    $tmp = $text;
    if ($tmp = strstr ($tmp, "Флот атакующего") )
    {
        $fleet = "";

        $tmp = strstr ($tmp, "атакующего");
        $start = strpos ($tmp, " ");
        $end = strpos ($tmp, "([");
        $name = trim (substr ($tmp, $start, $end-$start));

        $tmp = strstr ($tmp, "([");

        $gsp_g = strtok ($tmp, " ");
        $gsp_g = str_replace ("([", "", $gsp_g);
        $gsp_s = strtok (" ");
        $gsp_p = strtok (" ");
        $gsp_p = str_replace ("])", "", $gsp_p);

        $tok = trim (strtok (" "));
        if (!strcasecmp ($tok, "уничтожен"))
        {
            for ($i=202; $i<=215; $i++) $fleet .= "0 ";
            $weap = $shld = $armor = -1;
            $tmp = strstr ($tmp, "уничтожен") ;
        }
        else
        {
            if (!strcasecmp ($tok, "Вооружение"))           // Если есть техи, значит это изначальное состояние атакера.
            {
                $weap = strtok (" ");
                strtok (" ");
                $shld = strtok (" ");
                strtok (" ");
                $armor = strtok (" ");
                $tok = strtok (" ");
            }
            else $weap = $shld = $armor = -1;
            if (!strcasecmp ($tok, "уничтожен"))
            {
                for ($i=202; $i<=215; $i++) $fleet .= "0 ";
                $tmp = strstr ($tmp, "уничтожен") ;
            }
            else
            {
                if (strcasecmp ($tok, "Тип")) return 0;

                for ($i=0; $i<25; $i++) $fdlist[$i] = 0;
                $cnt = 0;
                while ( ($tok = strtok (" ")) > 200) $fdlist[$cnt++] = $tok;
                if (strcasecmp ($tok, "Кол-во")) return 0;
                //print_r ($fdlist);

                for ($i=202; $i<=215; $i++) $fleet1[$i] = 0;
                for ($n=0; $n<$cnt; $n++)
                {
                    if ( $fdlist[$n] < 400 )
                        $fleet1[$fdlist[$n]] = trim (strtok (" "));
                }
                for ($i=202; $i<=215; $i++) $fleet .= $fleet1[$i] . " ";

                $tmp = strstr ($tmp, "Кол-во");
            }
        }

        $attackers[$aobj]['name'] = $name;
        $attackers[$aobj]['g'] = $gsp_g;
        $attackers[$aobj]['s'] = $gsp_s;
        $attackers[$aobj]['p'] = $gsp_p;
        $attackers[$aobj]['weap'] = $weap;
        $attackers[$aobj]['shld'] = $shld;
        $attackers[$aobj]['armor'] = $armor;
        $attackers[$aobj]['inround'] = $round;
        $attackers[$aobj]['fleet'] = trim($fleet);
        if ($debug)
        {
            print_r ($attackers[$aobj]);
            echo "<br>";
        }
        $aobj++;
    }
    return strstr ($tmp, "Флот атакующего") ;
}

function nextDefender ($text, $round)
{
    global $dobj, $defenders, $debug;
    $tmp = $text;

    if ($tmp = strstr ($tmp, "Обороняющийся") )
    {
        $fleet = ""; $def = "";

        $start = strpos ($tmp, " ");
        $end = strpos ($tmp, "([");
        $name = trim (substr ($tmp, $start, $end-$start));

        $tmp = strstr ($tmp, "([");

        $gsp_g = strtok ($tmp, " ");
        $gsp_g = str_replace ("([", "", $gsp_g);
        $gsp_s = strtok (" ");
        $gsp_p = strtok (" ");
        $gsp_p = str_replace ("])", "", $gsp_p);

        $tok = trim (strtok (" "));
        if (!strcasecmp ($tok, "уничтожен"))
        {
            for ($i=202; $i<=215; $i++) $fleet .= "0 ";
            for ($i=401; $i<=408; $i++) $def .= "0 ";
            $weap = $shld = $armor = -1;
            $tmp = strstr ($tmp, "уничтожен") ;
        }
        else
        {
            if (!strcasecmp ($tok, "Вооружение"))           // Если есть техи, значит это изначальное состояние дефа.
            {
                $weap = strtok (" ");
                strtok (" ");
                $shld = strtok (" ");
                strtok (" ");
                $armor = strtok (" ");
                $tok = strtok (" ");
            }
            else $weap = $shld = $armor = -1;
            if (!strcasecmp ($tok, "уничтожен"))
            {
                for ($i=202; $i<=215; $i++) $fleet .= "0 ";
                for ($i=401; $i<=408; $i++) $def .= "0 ";
                $tmp = strstr ($tmp, "уничтожен") ;
            }
            else
            {
                if (strcasecmp ($tok, "Тип")) return 0;

                for ($i=0; $i<25; $i++) $fdlist[$i] = 0;
                $cnt = 0;
                while ( ($tok = strtok (" ")) > 200) $fdlist[$cnt++] = $tok;
                if (strcasecmp ($tok, "Кол-во")) return 0;
                //print_r ($fdlist);
                //echo "<br>";

                for ($i=202; $i<=215; $i++) $fleet1[$i] = 0;
                for ($n=0; $n<$cnt; $n++)
                {
                    if ( $fdlist[$n] < 400 )
                        $fleet1[$fdlist[$n]] = trim (strtok (" "));
                }
                for ($i=202; $i<=215; $i++) $fleet .= $fleet1[$i] . " ";
                for ($i=401; $i<=408; $i++) $def1[$i] = 0;
                for ($n=0; $n<$cnt; $n++)
                {
                    if ( $fdlist[$n] > 400 )
                        $def1[$fdlist[$n]] = trim (strtok (" "));
                }
                for ($i=401; $i<=408; $i++) $def .= $def1[$i] . " ";
                $tmp = strstr ($tmp, "Кол-во");
            }
        }

        // Сохранить информацию о дефе.
        $defenders[$dobj]['name'] = $name;
        $defenders[$dobj]['g'] = $gsp_g;
        $defenders[$dobj]['s'] = $gsp_s;
        $defenders[$dobj]['p'] = $gsp_p;
        $defenders[$dobj]['weap'] = $weap;
        $defenders[$dobj]['shld'] = $shld;
        $defenders[$dobj]['armor'] = $armor;
        $defenders[$dobj]['inround'] = $round;
        $defenders[$dobj]['fleet'] = trim($fleet);
        $defenders[$dobj]['def'] = trim($def);
        if ($debug)
        {
            print_r ($defenders[$dobj]);
            echo "<br>";
        }
        $dobj++;
    }
    return strstr ($tmp, "Обороняющийся") ;
}

// Генерировать сырой доклад из входящей строки. Возвратить 0, если ошибка и сырой доклад, если всё окей.
function gensource ($text, &$title)
{
    global $debug, $shortfleet, $shortdef, $aobj, $dobj, $attackers, $defenders;
    $aobj = $dobj = $rounds = $result = 0;
    $fdlist = array ();
    $anum = $dnum = 1;
    $pricetotal = $priceleft = 0;
    $atit = $dtit = " ";

    $s = stripcslashes ($text);     // Заменить сокращенные названия на числовые индексы
    $n = 202;
    $s = str_replace ($shortfleet[13], 215, $s);    // Сокращение для крйсера попадает в подстроку для линейного крейсера.
    foreach ($shortfleet as $i=>$val)
    {
        $s = str_replace ($val, $n, $s);
        $n++;
    }
    $n = 401;
    foreach ($shortdef as $i=>$val)
    {
        $s = str_replace ($val, $n, $s);
        $n++;
    }
    $s = str_replace (":", " ", $s);    // Вырезать всякий мусор
    $s = str_replace ("%", " ", $s);
    $s = str_replace (".", "", $s);
    //$s = str_replace (")", "", $s);
    $s = str_squeeze ($s);
    if ($debug) echo "<font color=gold>ОТЛАДОЧНАЯ ИНФОРМАЦИЯ</font><br><br>\n\nИсходный текст доклада (должен начинаться с даты): <br><br>\n\n".$s."<hr>\n";

    // Время.
    $s = strpbrk ($s, "012345678");
    sscanf ( $s, "%d-%d %d %d %d", 
             $date_m, $date_d, $date_hr, $date_min, $date_sec );    

    // Результаты боя.
    $capm = $capk = $capd = 0;
    if (strstr ($s, "Бой оканчивается вничью")) $result = 0;
    else if ($cap=strstr ($s, "Атакующий выиграл битву!"))
    {
        $cap = strstr ($cap, "Он получает");
        $cap = strpbrk ($cap, "0123456789");
        sscanf ($cap, "%d", $capm);
        $cap = strstr ($cap, "металла");
        $cap = strpbrk ($cap, "0123456789");
        sscanf ($cap, "%d", $capk);
        $cap = strstr ($cap, "кристалла");
        $cap = strpbrk ($cap, "0123456789");
        sscanf ($cap, "%d", $capd);
        $result = 1;
    }
    else if (strstr ($s, "Обороняющийся выиграл битву!")) $result = 2;
    if($debug) echo "Результат боя: <font color=gold>" . $result . "</font> (0: ничья, 1: победа атакера, 2: победа дефа)<br>\n";
    if($debug) echo "Он получает: <font color=gold>" . $capm . "</font> металла " . "<font color=gold>" . $capk . "</font> кристалла " . "<font color=gold>" . $capd . "</font> дейтерия<br>\n";
    if ($deb = strstr ($s, "Теперь на этих пространственных координатах находится"))
    {
        $deb = strpbrk ($deb, "0123456789");
        sscanf ($deb, "%d", $debm );
        $deb = strstr ($deb, "металла и");
        $deb = strpbrk ($deb, "0123456789");
        sscanf ($deb, "%d", $debk );
    }
    else $debm = $debk = 0;
    if($debug) echo "ПО: <font color=gold>" . $debm . "</font> металла " . "<font color=gold>" . $debk . "</font> кристалла<br>\n";
    if ($ms = strstr ($s, "Шанс появления луны составил"))
    {
        $ms = strpbrk ($ms, "0123456789");
        sscanf ($ms, "%d", $moonchance );
        if (strstr ($ms, "Невероятные массы свободного металла и кристалла") ) $moon = 1;
        else $moon = 0;
    }
    else $moonchance = $moon = 0;
    if($debug) echo "Шанс луны: <font color=gold>" . $moonchance . "</font><br>\n" . "Луна: <font color=gold>" . $moon . "</font><br>\n";

    $repair = defrepair ($s);
    if($debug) echo "Восстановлено обороны: <font color=gold>" . $repair . "</font><br>\n";

    // Количество раундов.
    $tmp = $s;
    while ( $tmp = strstr ($tmp, "Атакующий флот делает") )
    {
        $tmp = strstr ($tmp, "флот делает");
        $rounds++;
    }
    if($debug) echo "Количество раундов (вычисляется по количеству слов 'Атакующий флот делает'): <font color=gold>" . $rounds . "</font><hr>\n";

    // Список атакеров и дефов.
    if($debug) echo "Список атакеров и дефов. Вырезается до первого 'Атакующий флот делает'<br>\n";
    $start = strpos ($s, "Флот атакующего");
    $end = strpos ($s, "Обороняющийся");
    $ss = substr ($s, $start, $end-$start);
    if (strlen($ss) == 0) return 0;
    if($debug) echo "<font color=yellow>" . $ss . "</font><br>\n";
    while ($ss = nextAttacker ($ss, 0)) $anum++;

    $start = strpos ($s, "Обороняющийся");
    $end = strpos ($s, "Атакующий флот делает");
    if ($end == FALSE) $end = strpos ($s, "Он получает");
    $ss = substr ($s, $start, $end-$start);
    if (strlen($ss) == 0) return 0;
    if($debug) echo "<font color=yellow>" . $ss . "</font><br>\n";
    while ($ss = nextDefender ($ss, 0)) $dnum++;
    $s = strstr ($s, "Атакующий флот делает");

    // Раунды.
    for ($i=0; $i<$rounds; $i++)
    {
        $start = strpos ($s, "Атакующий флот делает");
        $end = strpos ($s, "Флот атакующего");
        $ss = substr ($s, $start, $end-$start);
        $fires[$i] = fireworks ($ss);
        if($debug)
        {
            printf ( "<hr><font color=gold>РАУНД %d</font><br>\n", $i+1);
            echo "<font color=yellow>" . $ss . "</font><br>" . $fires[$i] . "<br><br>\n\n";
        }

        $s = strstr ($s, "Флот атакующего");
        $start = strpos ($s, "Флот атакующего");
        $end = strpos ($s, "Обороняющийся");
        $ss = substr ($s, $start, $end-$start);
        if($debug) echo "<font color=yellow>" . $ss . "</font><br>\n";
        while ($ss = nextAttacker ($ss, $i+1));

        $tail = "Атакующий флот делает";
        if ($i == $rounds-1)
        {
            switch ($result)
            {
                case 0: $tail = "Бой оканчивается вничью"; break;
                case 1: $tail = "Атакующий выиграл битву!"; break;
                case 2: $tail = "Обороняющийся выиграл битву!"; break;
            }
        }
        $s = strstr ($s, "Обороняющийся");
        $start = strpos ($s, "Обороняющийся");
        $end = strpos ($s, $tail);
        $ss = substr ($s, $start, $end-$start);
        if($debug) echo "<font color=yellow>" . $ss . "</font><br>\n";
        while ($ss = nextDefender ($ss, $i+1));
    }

    // Сгенерировать сырой доклад.

    if ($debug) echo "<hr>\n";
    //if ($debug) echo "<hr> Обработанный доклад для сохранения в SQL-базе (&lt;комментарии&gt;):<br>\n\n";

    $res = "";
    $res .= "Date {".$date_m." ".$date_d." ".$date_hr." ".$date_min." ".$date_sec."}\r\n";
    $res .= "Attackers {".$anum."}\r\n"; 
    $res .= "Defenders {".$dnum."}\r\n";
    $res .= "Rounds {".$rounds."}\r\n"; 
    $res .= "Result {".$result."}\r\n"; 
    $res .= "Captured {".$capm." ".$capk." ".$capd."}\r\n"; 
    $res .= "Debris {".$debm." ".$debk."}\r\n"; 
    $res .= "MoonChance {".$moonchance." ".$moon."}\r\n"; 
    $res .= "Repair {" . $repair . "}\r\n";

    // Атакеры.
    $res .= "\r\n";
    for ($a=0,$i=0; $a<$aobj; $a++)
    {
        if ($attackers[$a]['inround'] == 0)
        {
            $res .= "Attacker".$i++." {";
            $res .= "(".$attackers[$a]['name'].") " ;
            $res .= $attackers[$a]['g']." ".$attackers[$a]['s']." ".$attackers[$a]['p']." ";
            $res .= $attackers[$a]['weap']." ".$attackers[$a]['shld']." ".$attackers[$a]['armor']." ";
            $res .= $attackers[$a]['fleet']."}\r\n";

            $pricetotal += fleetprice ( $attackers[$a]['fleet'] );

            if ( strpos ($atit, $attackers[$a]['name']) == 0 )
            {
                if ($a) $atit .= ", ";
                $atit .= $attackers[$a]['name'];
            }
        }
    }

    // Дефы.
    $res .= "\r\n";
    for ($a=0,$i=0; $a<$dobj; $a++)
    {
        if ($defenders[$a]['inround'] == 0)
        {
            $res .= "Defender".$i++." {";
            $res .= "(".$defenders[$a]['name'].") " ;
            $res .= $defenders[$a]['g']." ".$defenders[$a]['s']." ".$defenders[$a]['p']." ";
            $res .= $defenders[$a]['weap']." ".$defenders[$a]['shld']." ".$defenders[$a]['armor']." ";
            $res .= $defenders[$a]['fleet']." ".$defenders[$a]['def']."}\r\n";

            $pricetotal += fleetprice ( $defenders[$a]['fleet'] );
            $pricetotal += defprice ( $defenders[$a]['def'] );

            if ( strpos ($dtit, $defenders[$a]['name']) == 0 )
            {
                if ($a) $dtit .= ", ";
                $dtit .= $defenders[$a]['name'];
            }
        }
    }

    $title = trim($atit) . " vs " . trim($dtit);

    // Раунды.
    $res .= "\r\n";
    for ($r=0; $r<$rounds; $r++)
    {
        $res .= "Round".$r." {";
        $res .= $fires[$r] . " ";

        for ($a=0,$i=0; $a<$aobj; $a++)
        {
            if ($attackers[$a]['inround'] == $r+1)
            {
                $res .= "<AFleet".$i++."> ";
                $res .= $attackers[$a]['fleet'] . " ";
                if($r==$rounds-1) $priceleft += fleetprice ($attackers[$a]['fleet']);
            }
        }
        for ($a=0,$i=0; $a<$dobj; $a++)
        {
            if ($defenders[$a]['inround'] == $r+1)
            {
                $res .= "<DFleet".$i."> ";
                $res .= $defenders[$a]['fleet'] . " ";
                $res .= "<DDef".$i++."> ";
                $res .= $defenders[$a]['def'] . " ";
                if($r==$rounds-1)
                {
                    $priceleft += fleetprice ($defenders[$a]['fleet']);
                    $priceleft += defprice ($defenders[$a]['def']);
                }
            }
        }
        $res .= "}\r\n";
    }
    if ($rounds == 0) $priceleft = $pricetotal;

    $loss = $pricetotal - $priceleft;

    //if($debug) echo "<pre>".$res."</pre><hr>";
    $title = trim ($title) . " (П: " . str_replace(".", " ", nicenum($loss)) . ")";
    $res = "Title {" . $title . "}\r\n" . $res; 
    return $res;
}

// Загрузить и сохранить обработанный доклад в SQL-базе
// *******************

// Загрузить боевой доклад. Возвращает 1, если ок, или 0 если такого доклада нет.
function loadreport ($id)
{
    global $report, $debug, $comment;
    if ($debug) echo "<font color=gold>Загрузить боевой доклад №".$id.": </font><br>\n";
    $result = dbquery("SELECT * FROM ".BATTLETABLE." WHERE rid='".$id."'");
    if (dbrows($result) != 0)
    {
        $report = dbarray($result);
        $rep = $report['source'];
        $report['source'] = strip_tags ($report['source']); // Удалить комментарии.
        $rep = str_replace (" <", " <font color=#00FF00>&lt;", $rep);
        $rep = str_replace ("> ", "&gt;</font> ", $rep);
        $comment = $report['comment'];
        if ($debug)
        {
            echo nl2br ($rep) ."<br>\n";
            echo "Комментарии: $comment<br><hr>\n";
        }
        parsesource ();
        return true;
    }
    else return false;
}

// Сохранить боевой доклад. Возвращает id, или 0, если не удалось сохранить.
function savereport ($text)
{
    global $debug, $secretword, $comment;
    $id = md5 ($text . $secretword);

    if ($debug)
    {
        echo "<font color=gold>Сохранить боевой доклад №".$id.": </font><br>\n";
        $rep = $text;
        $rep = str_replace (" <", " <font color=#00FF00>&lt;", $rep);
        $rep = str_replace ("> ", "&gt;</font> ", $rep);
        echo nl2br ($rep) ."<br>\n";
        echo "Комментарии: $comment<br><hr>\n";
    }

    $result = dbquery("SELECT * FROM ".BATTLETABLE." WHERE rid='".$id."'");
    if (dbrows($result) != 0)
    {
        $rep = dbarray($result);
        $comment = $rep['comment'];
    }

    dbquery( "DELETE FROM ".BATTLETABLE." WHERE rid='".$id."'" );
    dbquery( "INSERT INTO ".BATTLETABLE." (rid) VALUES ('".$id."')" );
    $query = "UPDATE ".BATTLETABLE." SET "."source"." = '". $text."' WHERE rid='".$id."'";
    dbquery( $query);
    $query = "UPDATE ".BATTLETABLE." SET "."comment"." = '". $comment ."' WHERE rid='".$id."'";
    dbquery( $query);
    return $id;
}

// Вывести боевой доклад в оригинальном виде. Входные данные - массив $report.
// *******************

function genside ($i, $att, $round, $red)
{
    $html = "";
    global $report, $shortfleet, $shortdef, $ftech, $dtech;

    $prefix = array ( "Обороняющийся", "Флот атакующего" );
    $atokens = array ( "202", "203", "205", "204", "206", "207", "208", "209", "210", "211", "212", "213", "214", "215" );
    $dtokens = array ( "401", "402", "403", "404", "405", "406", "407", "408" );
    if ($att) $idx = "a" . $i . "_";
    else $idx = "d" . $i . "_";
    $html .= sprintf ( "<th><br><center>%s %s ", $prefix[$att], $report[$idx."name"] );
    if ($report['HideCoords'] == 0) $html .= sprintf ( "([%d:%d:%d])", $report[$idx."g"], $report[$idx."s"], $report[$idx."p"] );
    $html .= "\n";
    if ($round == -1)
    {
        if ($report['HideTech'] == 0) $html .= sprintf ( "<br>Вооружение: %s%% Щиты: %s%% Броня: %s%% \n", $report[$idx."at"], $report[$idx."sh"], $report[$idx."ar"]);
        else $html .= sprintf ( "<br>Технологии скрыты \n");
    }
    $ai = $idx;
    if ($round != -1) $ai = "round" . $round . "_" . $ai;
    if ($att) $p = "a"; else $p = "d";

    // Уничтожен?
    $total = $prev = 0;
    for ($n=0; $n<sizeof($atokens); $n++)  {
        $num = $atokens[$n];
        if ($report[$ai.$num] > 0) $total++;
        if ($round == 0) $prev += $report[$p.$i."_".$num];
        else $prev += $report["round".($round-1)."_".$p.$i."_".$num];
        if ($round == -1) $prev = -1;
    }
    for ($n=0; $n<sizeof($dtokens) && !$att; $n++)  {
        $num = $dtokens[$n];
        if ($report[$ai.$num] > 0) $total++;
        if ($round == 0) $prev += $report[$p.$i."_".$num];
        else $prev += $report["round".($round-1)."_".$p.$i."_".$num];
        if ($round == -1) $prev = -1;
    }
    if ($total == 0)
    {
        $html .= "<br><font color=red>уничтожен</font>\n";
        if ($prev <= 0)
        {
            $html .= "</center></th>\n";
            return $html;
        }
    }

    $html .= "<table border=1>\n";

    // Перечислить флот (и оборону для дефа).
    $html .= "<tr><th>Тип</th>";
    for ($n=0; $n<sizeof($atokens); $n++)  {
        $num = $atokens[$n];
        if ($report[$ai.$num] > 0) $html .= "<th>".$shortfleet[$num-202]."</th>";
        else
        {
            if ($round != -1 && $red)
            {
                if ($round == 0) $prev = $report[$p.$i."_".$num];
                else $prev = $report["round".($round-1)."_".$p.$i."_".$num];
                if ($prev > 0) $html .= "<th>".$shortfleet[$num-202]."</th>";
            }
        }
    }
    if (!$att)
    {
        for ($n=0; $n<sizeof($dtokens); $n++)  {
            $num = $dtokens[$n];
            if ($report[$ai.$num] > 0) $html .= "<th>".$shortdef[$num-401]."</th>";
            else
            {
                if ($round != -1 && $red)
                {
                    if ($round == 0) $prev = $report[$p.$i."_".$num];
                    else $prev = $report["round".($round-1)."_".$p.$i."_".$num];
                    if ($prev > 0) $html .= "<th>".$shortdef[$num-401]."</th>";
                }
            }
        }
    }
    $html .= "</tr>\n";

    $html .= "<tr><th>Кол-во.</th>";
    for ($n=0; $n<sizeof($atokens); $n++)  {
        $num = $atokens[$n];
        if ($report[$ai.$num] > 0)
        {
            if ($round != -1 && $red)
            {
                if ($round == 0) $prev = $report[$p.$i."_".$num];
                else $prev = $report["round".($round-1)."_".$p.$i."_".$num];
                $loss = $prev - $report[$ai.$num];
            }
            else $loss = 0;
            if ($loss) $html .= "<th>".nicenum($report[$ai.$num])."<font color=red>-".$loss."</font></th>";
            else $html .= "<th>".nicenum($report[$ai.$num])."</th>";
        }
        else
        {
            if ($round != -1 && $red)
            {
                if ($round == 0) $prev = $report[$p.$i."_".$num];
                else $prev = $report["round".($round-1)."_".$p.$i."_".$num];
                if ($prev > 0) $html .= "<th><font color=red>-".nicenum($prev)."</font></th>";
            }
        }
    }
    for ($n=0; $n<sizeof($dtokens) && !$att; $n++)  {
        $num = $dtokens[$n];
        if ($report[$ai.$num] > 0)
        {
            if ($round != -1 && $red)
            {
                if ($round == 0) $prev = $report[$p.$i."_".$num];
                else $prev = $report["round".($round-1)."_".$p.$i."_".$num];
                $loss = $prev - $report[$ai.$num];
            }
            else $loss = 0;
            if ($loss) $html .= "<th>".nicenum($report[$ai.$num])."<font color=red>-".$loss."</font></th>";
            else $html .= "<th>".nicenum($report[$ai.$num])."</th>";
        }
        else
        {
            if ($round != -1 && $red)
            {
                if ($round == 0) $prev = $report[$p.$i."_".$num];
                else $prev = $report["round".($round-1)."_".$p.$i."_".$num];
                if ($prev > 0) $html .= "<th><font color=red>-".nicenum($prev)."</font></th>";
            }
        }
    }
    $html .= "</tr>\n";

    $html .= "<tr><th>Воор.:</th>";
    for ($n=0; $n<sizeof($atokens); $n++)  {
        $num = $atokens[$n];
        $a = $ftech[$num]['att'] +  ($ftech[$num]['att'] * $report[$idx."at"] * 0.01);
        if ($report[$ai.$num] > 0) $html .= "<th>". nicenum($a) ."</th>";
        else
        {
            if ($round != -1 && $red)
            {
                if ($round == 0) $prev = $report[$p.$i."_".$num];
                else $prev = $report["round".($round-1)."_".$p.$i."_".$num];
                if ($prev > 0) $html .= "<th>0</th>";
            }
        }
    }
    for ($n=0; $n<sizeof($dtokens) && !$att; $n++)  {
        $num = $dtokens[$n];
        $a = $dtech[$num]['att'] + ($dtech[$num]['att'] * $report[$idx."at"] * 0.01);
        if ($report[$ai.$num] > 0) $html .= "<th>". nicenum($a) ."</th>";
        else
        {
            if ($round != -1 && $red)
            {
                if ($round == 0) $prev = $report[$p.$i."_".$num];
                else $prev = $report["round".($round-1)."_".$p.$i."_".$num];
                if ($prev > 0) $html .= "<th>0</th>";
            }
        }
    }
    $html .= "</tr>\n";

    $html .= "<tr><th>Щиты</th>";
    for ($n=0; $n<sizeof($atokens); $n++)  {
        $num = $atokens[$n];
        $a = $ftech[$num]['shld'] +  ($ftech[$num]['shld'] * $report[$idx."sh"] * 0.01);
        if ($report[$ai.$num] > 0) $html .= "<th>". nicenum($a) ."</th>";
        else
        {
            if ($round != -1 && $red)
            {
                if ($round == 0) $prev = $report[$p.$i."_".$num];
                else $prev = $report["round".($round-1)."_".$p.$i."_".$num];
                if ($prev > 0) $html .= "<th>0</th>";
            }
        }
    }
    for ($n=0; $n<sizeof($dtokens) && !$att; $n++)  {
        $num = $dtokens[$n];
        $a = $dtech[$num]['shld'] +  ($dtech[$num]['shld'] * $report[$idx."sh"] * 0.01);
        if ($report[$ai.$num] > 0) $html .= "<th>". nicenum($a) ."</th>";
        else
        {
            if ($round != -1 && $red)
            {
                if ($round == 0) $prev = $report[$p.$i."_".$num];
                else $prev = $report["round".($round-1)."_".$p.$i."_".$num];
                if ($prev > 0) $html .= "<th>0</th>";
            }
        }
    }
    $html .= "</tr>\n";

    $html .= "<tr><th>Броня</th>";
    for ($n=0; $n<sizeof($atokens); $n++)  {
        $num = $atokens[$n];
        $a = $ftech[$num]['str'] * 0.1 +  ($ftech[$num]['str'] * $report[$idx."ar"] * 0.001);
        if ($report[$ai.$num] > 0) $html .= "<th>". nicenum($a) ."</th>";
        else
        {
            if ($round != -1 && $red)
            {
                if ($round == 0) $prev = $report[$p.$i."_".$num];
                else $prev = $report["round".($round-1)."_".$p.$i."_".$num];
                if ($prev > 0) $html .= "<th>0</th>";
            }
        }
    }
    for ($n=0; $n<sizeof($dtokens) && !$att; $n++)  {
        $num = $dtokens[$n];
        $a = $dtech[$num]['str'] * 0.1 +  ($dtech[$num]['str'] * $report[$idx."ar"] * 0.001);
        if ($report[$ai.$num] > 0) $html .= "<th>". nicenum($a) ."</th>";
        else
        {
            if ($round != -1 && $red)
            {
                if ($round == 0) $prev = $report[$p.$i."_".$num];
                else $prev = $report["round".($round-1)."_".$p.$i."_".$num];
                if ($prev > 0) $html .= "<th>0</th>";
            }
        }
    }
    $html .= "</tr>\n";

    $html .= "</table></center></th>\n";
    return $html;
}

// Статистика по флотам и обороне
function fleetstats ()
{
    global $report, $shortfleet, $shortdef;
    $atokens = array ( "202", "203", "205", "204", "206", "207", "208", "209", "210", "211", "212", "213", "214", "215" );
    $dtokens = array ( "401", "402", "403", "404", "405", "406", "407", "408" );

    $r = $report['Rounds'] - 1;
    $anum = $report['Attackers'];
    $dnum = $report['Defenders'];
    if ($anum == 0 || $dnum == 0) return;

    echo "<br>\n\n<!-- Статистика по флотам -->\n\n";
    echo "<table border=0 width='99%'><tr><th>\n";
    echo "<table border=0 width='100%'><tr><td class=c>Статистика по флотам и обороне:</td></tr></table>\n\n";

    // Атакеры.
    echo "<table border=0 width='100%'><tr>\n\n";
    for ($i=0; $i<$anum; $i++)
    {
        $idx = "a".$i."_";
        printf ( "<th><br><center>\n%s ", $report[$idx."name"]);
        if ($report['HideCoords'] == 0) printf ( "(%d:%d:%d)", $report[$idx."g"], $report[$idx."s"], $report[$idx."p"] );
        echo "<br>\n";
        if ($report['HideTech'] == 0) printf ( "Вооружение: %s%% Щиты: %s%% Броня: %s%% \n", $report[$idx."at"], $report[$idx."sh"], $report[$idx."ar"]);
        else printf ( "Технологии скрыты \n");

        echo "<table border=1>\n";

        // Перечислить флот (и оборону для дефа).
        echo "<tr><th>Тип</th>";
        for ($n=0; $n<sizeof($atokens); $n++)  {
            $num = $atokens[$n];
            if ($report[$idx.$num] > 0) echo "<th>".$shortfleet[$num-202]."</th>";
        } 
        for ($n=0; $n<sizeof($dtokens); $n++)  {
            $num = $dtokens[$n];
            if ($report[$idx.$num] > 0) echo "<th>".$shortdef[$num-401]."</th>";
        }
        echo "</tr>\n";

        echo "<tr><th>Начало</th>";
        for ($n=0; $n<sizeof($atokens); $n++)  {
            $num = $atokens[$n];
            if ($report[$idx.$num] > 0) echo "<th>".nicenum($report[$idx.$num])."</th>";
        }
        for ($n=0; $n<sizeof($dtokens) && !$att; $n++)  {
            $num = $dtokens[$n];
            if ($report[$idx.$num] > 0) echo "<th>".nicenum($report[$idx.$num])."</th>";
        }
        echo "</tr>\n";

        echo "<tr><th>Потери</th>";
        for ($n=0; $n<sizeof($atokens); $n++)  {
            $num = $atokens[$n];
            if ($report[$idx.$num] > 0)
            {
                if($report['Rounds'] > 0) $lost = $report[$idx.$num] - $report["round".$r."_".$idx.$num];
                else $lost = 0;
                if ($lost) echo "<th><font color=red>-".nicenum($lost)."</font></th>";
                else echo "<th><font color=#00FF00>0</font></th>";
            }
        }
        for ($n=0; $n<sizeof($dtokens) && !$att; $n++)  {
            $num = $dtokens[$n];
            if ($report[$idx.$num] > 0)
            {
                if ($report['Rounds'] > 0) $lost = $report[$idx.$num] - $report["round".$r."_".$idx.$num];
                else $lost = 0;
                if ($lost) echo "<th><font color=red>-".nicenum()."</font></th>";
                else echo "<th><font color=#00FF00>0</font></th>";
            }
        }
        echo "</tr>\n";

        echo "<tr><th>Осталось</th>";
        for ($n=0; $n<sizeof($atokens); $n++)  {
            $num = $atokens[$n];
            if ($report[$idx.$num] > 0)
            {
                if ($report['Rounds'] > 0) $left = $report["round".$r."_".$idx.$num];
                else $left = $report[$idx.$num];
                echo "<th>".nicenum($left)."</th>";
            }
        }
        for ($n=0; $n<sizeof($dtokens) && !$att; $n++)  {
            $num = $dtokens[$n];
            if ($report[$idx.$num] > 0)
            {
                if ($report['Rounds'] > 0) $left = $report["round".$r."_".$idx.$num];
                else $left = $report[$idx.$num];
                echo "<th>".nicenum($report["round".$r."_".$idx.$num])."</th>";
            }
        }
        echo "</tr>\n";

        echo "</table></center></th>\n";
    }
    echo "</tr></table>\n";

    // Дефы.
    echo "<table border=0 width='100%'><tr>\n\n";
    for ($i=0; $i<$dnum; $i++)
    {
        $idx = "d".$i."_";
        printf ( "<th><br><center>\n%s ", $report[$idx."name"]);
        if ($report['HideCoords'] == 0) printf ( "(%d:%d:%d)", $report[$idx."g"], $report[$idx."s"], $report[$idx."p"] );
        echo "<br>\n";
        if ($report['HideTech'] == 0) printf ( "Вооружение: %s%% Щиты: %s%% Броня: %s%% \n", $report[$idx."at"], $report[$idx."sh"], $report[$idx."ar"]);
        else printf ( "Технологии скрыты \n");

        echo "<table border=1>\n";

        $total = 0;
        for ($n=0; $n<sizeof($atokens); $n++)  {
            $num = $atokens[$n];
            if ($report[$idx.$num] > 0) $total++;
        }
        for ($n=0; $n<sizeof($dtokens) && !$att; $n++)  {
            $num = $dtokens[$n];
            if ($report[$idx.$num] > 0) $total++;
        }
        if ($total == 0)
        {
            echo "</table></center></th>\n";
            continue;
        }

        // Перечислить флот (и оборону для дефа).
        echo "<tr><th>Тип</th>";
        for ($n=0; $n<sizeof($atokens); $n++)  {
            $num = $atokens[$n];
            if ($report[$idx.$num] > 0) echo "<th>".$shortfleet[$num-202]."</th>";
        } 
        for ($n=0; $n<sizeof($dtokens); $n++)  {
            $num = $dtokens[$n];
            if ($report[$idx.$num] > 0) echo "<th>".$shortdef[$num-401]."</th>";
        }
        echo "</tr>\n";

        echo "<tr><th>Начало</th>";
        for ($n=0; $n<sizeof($atokens); $n++)  {
            $num = $atokens[$n];
            if ($report[$idx.$num] > 0) echo "<th>".nicenum($report[$idx.$num])."</th>";
        }
        for ($n=0; $n<sizeof($dtokens) && !$att; $n++)  {
            $num = $dtokens[$n];
            if ($report[$idx.$num] > 0) echo "<th>".nicenum($report[$idx.$num])."</th>";
        }
        echo "</tr>\n";

        echo "<tr><th>Потери</th>";
        for ($n=0; $n<sizeof($atokens); $n++)  {
            $num = $atokens[$n];
            if ($report[$idx.$num] > 0)
            {
                $lost = $report[$idx.$num] - $report["round".$r."_".$idx.$num];
                if ($lost) echo "<th><font color=red>-".nicenum($lost)."</font></th>";
                else echo "<th><font color=#00FF00>0</font></th>";
            }
        }
        for ($n=0; $n<sizeof($dtokens) && !$att; $n++)  {
            $num = $dtokens[$n];
            if ($report[$idx.$num] > 0)
            {
                $lost = $report[$idx.$num]- $report["round".$r."_".$idx.$num];
                if ($lost) echo "<th><font color=red>-".nicenum($lost)."</font></th>";
                else echo "<th><font color=#00FF00>0</font></th>";
            }
        }
        echo "</tr>\n";

        echo "<tr><th>Осталось</th>";
        for ($n=0; $n<sizeof($atokens); $n++)  {
            $num = $atokens[$n];
            $left = $report["round".$r."_".$idx.$num];
            if ($report[$idx.$num] > 0) echo "<th>".nicenum($left)."</th>";
        }
        for ($n=0; $n<sizeof($dtokens) && !$att; $n++)  {
            $num = $dtokens[$n];
            $left = $report["round".$r."_".$idx.$num];
            if ($i == 0)
            {
                if ($num == 406) $left += $report["r407"];          // плазма и МЩК перепутаны местами
                else if ($num == 407) $left += $report["r406"];
                else $left += $report["r".$num];
            }
            if ($report[$idx.$num] > 0) echo "<th>".nicenum($left)."</th>";
        }
        echo "</tr>\n";

        echo "</table></center></th>\n";
    }
    echo "</tr></table>\n";

    echo "</th></tr></table>\n\n";
}

// Восстановлено обороны. Если есть строка восстановления - возвращаем инфу оттуда.
// Если такой строки нет - делаем аппроксимацию 70% обороны восстановлено.
function repaired ($num, $begin, $end)
{
    global $report;

    if ($report["r".$num] > 0)
    {
        if ($num == 406) $restored = $report["r407"];       // плазма и МЩК перепутаны
        else if ($num == 407) $restored = $report["r406"];
        else $restored = $report["r".$num];
    }
    else
    {
        // Защитные сооружения имеют вероятность на восстановление после боя в 70%.
        // При небольшом количестве юнитов (меньше чем 10) эта вероятность высчитывается для каждого сооружения отдельно.
        if ($begin < 10)
        {
            $restored = 0;
            for ($i=0; $i<$begin; $i++)
            {
                $dice = rand (1, 100);
                if ($dice > 70) $restored++;
            }
        }
        else
        {
            $lost = $begin - $end;
            $bias = rand (-10, 10) * 0.01;      // +/- 10%
            $restored = ceil ($lost * (0.7 + $bias));
        }
    }

    return $end + $restored;
}

// Статистика по потерям.
function losstats ()
{
    global $report, $shortfleet, $shortdef, $fleetcargo;
    $atokens = array ( "202", "203", "204", "205", "206", "207", "208", "209", "210", "211", "212", "213", "214", "215" );
    $dtokens = array ( "401", "402", "403", "404", "405", "406", "407", "408" );

    $anum = $report['Attackers'];
    $dnum = $report['Defenders'];
    if ($anum == 0 || $dnum == 0) return;
    $r = $report['Rounds'];
    $r1 = $report['Rounds'] - 1;
    $D0Name = $report['d0_name'];

    echo "<br>\n\n<!-- Статистика по потерям -->\n\n";
    echo "<table width='99%'><tr><th>\n";
    echo "<table border=0 width='100%'><tr><td class=c>Статистика по потерям:</td></tr></table>\n";

    // Атакеры
    for ($i=0; $i<$anum; $i++)
    {
        $name = $report["a".$i."_name"];
        for ($n=0; $n<sizeof($atokens); $n++)
        {
            $num = $atokens[$n];
            $cnt = $report["a".$i."_".$num];
            $begin = $cnt;
            if ($r > 0) $end = $report["round".$r1."_a".$i."_".$num]; 
            else $end = $begin;
            $lost = $begin - $end;
            if ($lost > 0) $present[$num] = true;
            $loss[$name]['total'] += $lost;
            $loss[$name][$num] += $lost;
            $loss[$name]['attacker'] = true;
            $loss[$name]['cargo'] += $fleetcargo[$n] * $end;
            $totalcargo += $fleetcargo[$n] * $end;
        }
    }

    for ($i=0; $i<$anum; $i++)
    {
        $name = $report["a".$i."_name"];
        if ($totalcargo == 0) $loss[$name]['cargo_prc'] = 0;
        else $loss[$name]['cargo_prc'] = ($loss[$name]['cargo'] * 100) / $totalcargo / 100;
    }

    // Дефы
    for ($i=0; $i<$dnum; $i++)
    {
        $name = $report["d".$i."_name"];
        for ($n=0; $n<sizeof($atokens); $n++)
        {
            $num = $atokens[$n];
            $cnt = $report["d".$i."_".$num];
            $begin = $cnt;
            if ($r > 0) $end = $report["round".$r1."_d".$i."_".$num]; 
            else $end = $begin;
            $lost = $begin - $end;
            if ($lost > 0) $present[$num] = true;
            $loss[$name]['total'] += $lost;
            $loss[$name][$num] += $lost;
        }
        if ($i == 0)        // Оборона.
        {
            for ($n=0; $n<sizeof($dtokens); $n++)
            {
                $num = $dtokens[$n];
                $cnt = $report["d".$i."_".$num];
                $begin = $cnt;
                if ($r > 0)
                {
                    $end = $report["round".$r1."_d".$i."_".$num]; 
                    $lost = $begin - repaired ($num, $begin, $end);
                }
                else
                {
                    $end = $begin;
                    $lost = 0;
                }
                if ($lost > 0) $dpresent[$num] = true;
                $loss[$name]['dtotal'] += $lost;
                $loss[$name][$num] += $lost;
            }
        }
    }

    //print_r ($loss);

    echo "<table border=0 width='100%'>\n";

    echo "<tr>\n";
    echo "    <td class='c'><center>Флот</center></td>\n";
    foreach ($loss as $name => $v1)
    {
        echo "    <td class='c'><center>$name</center></td>\n";
        echo "    <th><img src='../images/metall_sm.gif'> Мет</th>\n";
        echo "    <th><img src='../images/kristall_sm.gif'> Крис</th>\n";
        echo "    <th><img src='../images/deuterium_sm.gif'> Дейт</th>\n";
        echo "    <th>Всего</th>\n";
        echo "    <th>Очки</th>\n";
    }
    echo "</tr>\n";

    // Потери по флоту
    for ($n=0; $n<sizeof($atokens); $n++)
    {
        $num = 202 + $n;
        if ($present[$num] == true)
        {
            echo "<tr>\n";
            echo "    <th>".$shortfleet[$n]."</th>\n";
            foreach ($loss as $name => $v1)
            {
                $cnt = $loss[$name][$num];
                if ($cnt > 0)
                {
                    $m = pricem($num)*$cnt; $k = pricek($num)*$cnt; $d = priced($num)*$cnt;
                    $tot = $m + $k + $d;
                    $pts = (price($num)*$cnt)/1000;
                    $loss[$name]['total_m'] += $m;
                    $loss[$name]['total_k'] += $k;
                    $loss[$name]['total_d'] += $d;
                    $loss[$name]['total_tot'] += $tot;
                    $loss[$name]['total_pts'] += $pts;
                    echo "    <th>".nicenum($cnt)."</th>";
                    echo "<th>".nicenum($m)."</th>";
                    echo "<th>".nicenum($k)."</th>";
                    echo "<th>".nicenum($d)."</th>";
                    echo "<th>".nicenum($tot)."</th>";
                    echo "<th>".nicenum($pts)."</th>\n";
                }
                else
                {
                    echo "    ";
                    for ($th=0; $th<6; $th++) echo "<th>-</th>";
                    echo "\n";
                }
            }
            echo "</tr>\n";
        }
    }

    // Всего потери по флоту
    echo "<tr>\n";
    echo "    <th><font color=gold>Всего</font></th>\n";
    foreach ($loss as $name => $v1)
    {
        $cnt = $loss[$name]['total'];
        if ($cnt > 0)
        {
            $m = $loss[$name]['total_m'];
            $k = $loss[$name]['total_k'];
            $d = $loss[$name]['total_d'];
            $tot = $loss[$name]['total_tot'];
            $pts = $loss[$name]['total_pts'];
            echo "    <th><font color=gold>".nicenum($cnt)."</font></th>";
            echo "<th><font color=gold>".nicenum($m)."</font></th>";
            echo "<th><font color=gold>".nicenum($k)."</font></th>";
            echo "<th><font color=gold>".nicenum($d)."</font></th>";
            echo "<th><font color=gold>".nicenum($tot)."</font></th>";
            echo "<th><font color=gold>".nicenum($pts)."</font></th>\n";
        }
        else
        {
            echo "    ";
            for ($th=0; $th<6; $th++) echo "<th><font color=gold>-</font></th>";
            echo "\n";
        }
    }
    echo "</tr>\n";

    // Оборона
    if ($loss[$D0Name]['dtotal'] > 0)
    {
        echo "<tr>\n";
        echo "    <td class='c'><center>Оборона</center></td>\n";
        foreach ($loss as $name => $v1)
        {
            echo "    <th> </th>\n";
            echo "    <th> </th>\n";
            echo "    <th> </th>\n";
            echo "    <th> </th>\n";
            echo "    <th> </th>\n";
            echo "    <th> </th>\n";
        }
        echo "</tr>\n";

        // Потери по обороне
        for ($n=0; $n<sizeof($dtokens); $n++)
        {
            $num = 401 + $n;
            if ($dpresent[$num] == true)
            {
                echo "<tr>\n";
                echo "    <th>".$shortdef[$n]."</th>\n";
                foreach ($loss as $name => $v1)
                {
                    $cnt = $loss[$name][$num];
                    if ($cnt > 0)
                    {
                        $m = pricem($num)*$cnt; $k = pricek($num)*$cnt; $d = priced($num)*$cnt;
                        $tot = $m + $k + $d;
                        $pts = (price($num)*$cnt)/1000;
                        $loss[$name]['dtotal_m'] += $m;
                        $loss[$name]['dtotal_k'] += $k;
                        $loss[$name]['dtotal_d'] += $d;
                        $loss[$name]['dtotal_tot'] += $tot;
                        $loss[$name]['dtotal_pts'] += $pts;
                        echo "    <th>".nicenum($cnt)."</th>";
                        echo "<th>".nicenum($m)."</th>";
                        echo "<th>".nicenum($k)."</th>";
                        echo "<th>".nicenum($d)."</th>";
                        echo "<th>".nicenum($tot)."</th>";
                        echo "<th>".nicenum($pts)."</th>\n";
                    }
                    else
                    {
                        echo "    ";
                        for ($th=0; $th<6; $th++) echo "<th>-</th>";
                        echo "\n";
                    }
                }
                echo "</tr>\n";
            }
        }

        // Всего потери по обороне
        echo "<tr>\n";
        echo "    <th><font color=gold>Всего</font></th>\n";
        foreach ($loss as $name => $v1)
        {
            $cnt = $loss[$name]['dtotal'];
            if ($cnt > 0)
            {
                $m = $loss[$name]['dtotal_m'];
                $k = $loss[$name]['dtotal_k'];
                $d = $loss[$name]['dtotal_d'];
                $tot = $loss[$name]['dtotal_tot'];
                $pts = $loss[$name]['dtotal_pts'];
                echo "    <th><font color=gold>".nicenum($cnt)."</font></th>";
                echo "<th><font color=gold>".nicenum($m)."</font></th>";
                echo "<th><font color=gold>".nicenum($k)."</font></th>";
                echo "<th><font color=gold>".nicenum($d)."</font></th>";
                echo "<th><font color=gold>".nicenum($tot)."</font></th>";
                echo "<th><font color=gold>".nicenum($pts)."</font></th>\n";
            }
            else
            {
                echo "    ";
                for ($th=0; $th<6; $th++) echo "<th><font color=gold>-</font></th>";
                echo "\n";
            }
        }
        echo "</tr>\n";
    }

    // Прибыль
    echo "<tr>\n";
    echo "    <td class='c'><center>Прибыль</center></td>\n";
    foreach ($loss as $name => $v1)
    {
        echo "    <th> </th>\n";
        echo "    <th> </th>\n";
        echo "    <th> </th>\n";
        echo "    <th> </th>\n";
        echo "    <th> </th>\n";
        echo "    <th> </th>\n";
    }
    echo "</tr>\n";

    echo "<tr>\n";
    echo "    <th>Захвачено</th>\n";
    foreach ($loss as $name => $v1)
    {
        if ($loss[$name]['attacker'])
        {
            $m = $report['cm'] * $loss[$name]['cargo_prc'];
            $k = $report['ck'] * $loss[$name]['cargo_prc'];
            $d = $report['cd'] * $loss[$name]['cargo_prc'];
            $tot = $m + $k + $d;
            echo "    <th>-</th>";
            if ($m) echo "<th><font color=lime>+".nicenum($m)."</font></th>";
            else echo "<th>-</th>";
            if ($k) echo "<th><font color=lime>+".nicenum($k)."</font></th>";
            else echo "<th>-</th>";
            if ($d) echo "<th><font color=lime>+".nicenum($d)."</font></th>";
            else echo "<th>-</th>";
            if ($tot) echo "<th><font color=lime>+".nicenum($tot)."</font></th>";
            else echo "<th>-</th>";
            echo "<th>-</th>\n";
        }
        else
        {
            echo "    ";
            for ($th=0; $th<6; $th++) echo "<th>-</th>";
            echo "\n";
        }
    }
    echo "</tr>\n";

    echo "<tr>\n";
    echo "    <th>Лом</th>\n";
    foreach ($loss as $name => $v1)
    {
        $m = $report['dm'];
        $k = $report['dk'];
        echo "    <th>-</th>";
        if ($m) echo "<th><font color=lime>+".nicenum($m)."</font></th>";
        else echo "<th>-</th>";
        if ($k) echo "<th><font color=lime>+".nicenum($k)."</font></th>";
        else echo "<th>-</th>";
        echo "<th>-</th>\n";
        echo "<th>-</th>\n";
        echo "<th>-</th>\n";
    }
    echo "</tr>\n";

    echo "<tr>\n";
    echo "    <td class='c'><center>Всего</center></td>\n";
    foreach ($loss as $name => $v1)
    {
        $m = $report['dm'] - $loss[$name]['total_m'];
        $k = $report['dk'] - $loss[$name]['total_k'];
        $d = -$loss[$name]['total_d'];
        if ($loss[$name]['attacker'])
        {
            $m += $report['cm'] * $loss[$name]['cargo_prc'];
            $k += $report['ck'] * $loss[$name]['cargo_prc'];
            $d += $report['cd'] * $loss[$name]['cargo_prc'];
        }
        else
        {
            $m -= $loss[$name]['dtotal_m'];
            $k -= $loss[$name]['dtotal_k'];
            $d -= $loss[$name]['dtotal_d'];
        }
        $tot = $m + $k + $d;
        $pts = $loss[$name]['total_pts'] + $loss[$name]['dtotal_pts'];
        echo "    <th>-</th>";
        if ($m > 0) echo "<th><font color=lime>+".nicenum($m)."</font></th>";
        else if ($m < 0) echo "<th><font color=red>".nicenum($m)."</font></th>";
        else echo "<th>-</th>";
        if ($k > 0) echo "<th><font color=lime>+".nicenum($k)."</font></th>";
        else if ($k < 0) echo "<th><font color=red>".nicenum($k)."</font></th>";
        else echo "<th>-</th>";
        if ($d > 0) echo "<th><font color=lime>+".nicenum($d)."</font></th>";
        else if ($k < 0) echo "<th><font color=red>".nicenum($d)."</font></th>";
        else echo "<th>-</th>";
        if ($tot > 0) echo "<th><font color=lime>+".nicenum($tot)."</font></th>";
        else if ($tot < 0) echo "<th><font color=red>".nicenum($tot)."</font></th>";
        else echo "<th>-</th>";
        if ($pts > 0) echo "<th><font color=red>-".nicenum($pts)."</font></th>";
        else echo "<th><font color=lime>0</font></th>\n";
    }
    echo "</tr>\n";

    echo "</table>\n\n";

    echo "</th></tr></table>\n\n";
}

function piegenxml ($price, $max)
{
    $res = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
    $res .= "<pie>";
    foreach ($price as $name=>$prc)
    {
        if ($prc == $max) $res .= "  <slice title=\"".iconv("CP1251", "UTF-8", $name)."\" pull_out=\"true\">".$prc."</slice>";
        else $res .= "  <slice title=\"".iconv("CP1251", "UTF-8", $name)."\" pull_out=\"false\">".$prc."</slice>";
    }
    $res .= "</pie>";
    return $res;
}

// Генерация пирога
function piestats ()
{
    global $report, $shortfleet, $shortdef, $fleetcargo;
    $atokens = array ( "202", "203", "204", "205", "206", "207", "208", "209", "210", "211", "212", "213", "214", "215" );
    $dtokens = array ( "401", "402", "403", "404", "405", "406", "407", "408" );

    $anum = $report['Attackers'];
    $dnum = $report['Defenders'];
    $max = 0;

    // Атакеры
    for ($i=0; $i<$anum; $i++)
    {
        $name = $report["a".$i."_name"];
        for ($n=0; $n<sizeof($atokens); $n++)
        {
            $num = $atokens[$n];
            $cnt = $report["a".$i."_".$num];
            $price[$name] += $cnt * ( pricem($num) + pricek($num) + priced($num) );
        }
        if ($price[$name] > $max) $max = $price[$name];
    }

    // Дефы
    for ($i=0; $i<$dnum; $i++)
    {
        $name = $report["d".$i."_name"];
        for ($n=0; $n<sizeof($atokens); $n++)
        {
            $num = $atokens[$n];
            $cnt = $report["d".$i."_".$num];
            $price[$name] += $cnt * ( pricem($num) + pricek($num) + priced($num) );
        }
        if ($price[$name] > $max) $max = $price[$name];
    }

    echo "<br>\n\n<!-- Пирог -->\n\n";
    echo "<table width='99%'><tr><th>\n";
    echo "<table border=0 width='100%'><tr><td class=c>Соотношение стоимости флотов:</td></tr></table>\n";
    echo "<table border=0 width='100%'><tr><td align=center>\n";

    echo "<!-- ampie script-->";
    echo "<script type='text/javascript' src='ampie/swfobject.js'></script>";
    echo "<table><div id='flashcontent'>";
    echo "<strong>You need to upgrade your Flash Player</strong>";
    echo "</div></table>";
    echo "<script type='text/javascript'>";
    echo "var so = new SWFObject('ampie/ampie.swf', 'ampie', '100%', '400px', '8', '#000000');";
    echo "so.addVariable('path', 'ampie/');";
    echo "so.addVariable('settings_file', encodeURIComponent('ampie/ampie_settings.xml'));";
    echo "so.addVariable('chart_data', encodeURIComponent('".piegenxml($price, $max)."'));";
    echo "so.addVariable('preloader_color', '#ffffff');";
    echo "so.write('flashcontent');";
    echo "</script>";
    echo "<!-- end of ampie script -->";

    echo "</td></tr></table>\n\n";

    echo "</th></tr></table>\n\n";
}

// Возвращает сгенерированный HTML-код.
function genreport ($red)
{
    $html = "";
    global $report, $defstr;
    $html .= "<table width='99%'><tr><td>\n\n";

    if ($report['Rounds'] > 0)
    {
        $html .= "<table align='right'><tr><td><a onclick='ShowHideRounds()' style='cursor: pointer;'><font color=gold>Показать/скрыть раунды</font></a></td></tr></table>\n\n";
    }

    $html .= "<table width='100%'><tr><td class=c>";
    if ($report['HideDate']) $html .= sprintf ( " Время скрыто . Произошёл бой между следующими флотами:<br>" );
    else  
    $html .= sprintf ( "Дата/Время: %02d-%02d %02d:%02d:%02d . Произошёл бой между следующими флотами:<br>", 
                       $report['date_m'], $report['date_d'], $report['date_hr'], $report['date_min'], $report['date_sec'] );
    $html .= "</td></tr></table>";
    $html .= "\n\n";
    $html .= "<! FIGHT !>\n\n";

    // Флоты перед боем

    $html .= "<table border=1 width=100%><tr>\n";
    for ($i=0; $i<$report['Attackers']; $i++) $html .= genside ($i, true, -1, $red);
    $html .= "</tr></table>\n\n";
    $html .= "<table border=1 width=100%><tr>\n";
    for ($i=0; $i<$report['Defenders']; $i++) $html .= genside ($i, false, -1, $red);
    $html .= "</tr></table>\n\n";

    // Вывод результатов каждого раунда

    $html .= "<div id='rounds'>\n\n";

    for ($r=0; $r<$report['Rounds']; $r++)
    {
        $afires = $report["round".$r."_afires"];
        $apower = $report["round".$r."_apower"];
        $dabsorb = $report["round".$r."_dabsorb"];
        $dfires = $report["round".$r."_dfires"];
        $dpower = $report["round".$r."_dpower"];
        $aabsorb = $report["round".$r."_aabsorb"];

        $html .= sprintf ("<br>\n\n<! Round %d !>\n\n", $r+1);
        $html .= "<table width='100%'><tr><td class=c>";
        $html .= sprintf ("<center>Атакующий флот делает: %s выстрела(ов) общей мощностью %s по обороняющемуся. Щиты обороняющегося поглощают %s мощности выстрелов<br>\n", nicenum ($afires), nicenum ($apower), nicenum ($dabsorb) );
        $html .= sprintf ("Обороняющийся флот делает %s выстрела(ов) общей мощностью %s выстрела(ов) по атакующему. Щиты атакующего поглощают %s мощности выстрелов</center>\n\n", nicenum ($dfires), nicenum ($dpower), nicenum ($aabsorb) );
        $html .= "</td></tr></table>";

        $html .= "<table border=1 width=100%><tr>\n";
        for ($i=0; $i<$report['Attackers']; $i++) $html .= genside ($i, true, $r, $red);
        $html .= "</tr></table>\n\n";
        $html .= "<table border=1 width=100%><tr>\n";
        for ($i=0; $i<$report['Defenders']; $i++) $html .= genside ($i, false, $r, $red);
        $html .= "</tr></table>\n\n";
    }

    $html .= "</div>";
    if ($report['Rounds'] > 0)
    {
        $html .= "<table align='left'><tr><td><a onclick='ShowHideRounds()' style='cursor: pointer;'><font color=gold>Показать/скрыть раунды</font></a></td></tr></table><br>\n\n";
    }

    // Вывод результатов боя.
    $html .= "<! Finish Him !>\n\n<p>\n";
    switch ($report['Result'])
    {
        case 0:
            $html .= "Бой оканчивается вничью, оба флота возвращаются на свои планеты<br>\n";
            break;
        case 1:
            $html .= "Атакующий выиграл битву!<br>\n";
            $html .= sprintf ( "Он получает<br>%s металла, %s кристалла и %s дейтерия.<br>\n", 
                               nicenum($report['cm']), nicenum($report['ck']), nicenum($report['cd']));
            break;
        case 2:
            $html .= "Обороняющийся выиграл битву!<br>\n";
            break;
    }
    $html .= "<p><br>\n";

    // Базовая статистика по потерям.
    $aloss = $dloss = 0;
    if ($report['Rounds'] != 0)
    {
        $r = $report['Rounds'] - 1;
        for ($i=0; $i<$report['Attackers']; $i++)
        {
            $s = "a".$i."_";
            $e = "round".$r."_a".$i."_";
            for ($obj=202; $obj<=215; $obj++) $aloss += price ($obj) * ($report[$s.$obj]-$report[$e.$obj]);
        }
        for ($i=0; $i<$report['Defenders']; $i++)
        {
            $s = "d".$i."_";
            $e = "round".$r."_d".$i."_";
            for ($obj=202; $obj<=215; $obj++) $dloss += price ($obj) * ($report[$s.$obj]-$report[$e.$obj]);
            for ($obj=401; $obj<=408; $obj++) $dloss += price ($obj) * ($report[$s.$obj]-$report[$e.$obj]);
        }
    }
    else        // Первым залпом.
    {
        if ($report['Result'] == 1)
        {
            $s = "d".$i."_";
            for ($obj=202; $obj<=215; $obj++) $dloss += price ($obj) * $report[$s.$obj];
            for ($obj=401; $obj<=408; $obj++) $dloss += price ($obj) * $report[$s.$obj];
            $aloss = 0;
        }
        else
        {
            $s = "a".$i."_";
            for ($obj=202; $obj<=215; $obj++) $aloss += price ($obj) * $report[$s.$obj];
            $dloss = 0;
        }
    }
    $html .= "Атакующий потерял ".nicenum($aloss)." единиц.<br>\nОбороняющийся потерял ".nicenum($dloss)." единиц.<br>\n";

    //if ($report['dm'] + $report['dk'])      // Лом и луна
    {
        $html .= sprintf ( "Теперь на этих пространственных координатах находится %s металла и %s кристалла (%d перераб.).<br>\n", nicenum($report['dm']), nicenum($report['dk']), ceil(($report['dm']+$report['dk'])/20000));
        if ($report['moonchance'] > 0)
        {
            $html .= sprintf ( "Шанс появления луны составил %d %% <br>\n", $report['moonchance'] );
            if ($report['moon'])
            {
                $html .= sprintf ( "Невероятные массы свободного металла и кристалла сближаются и образуют форму некого спутника на орбите планеты. <br>\n");
            }
        }
    }

    // Восстановленная оборона.
    $sum = $comma = 0;
    for ($i=401; $i<=408; $i++) $sum += $report["r".$i];
    if ($sum > 0)
    {
        for ($i=0; $i<8; $i++)
        {
            $num = $report["r".(401+$i)];
            if ($num > 0)
            {
                if ($comma) $html .= ", ";
                $html .= sprintf ( "%s %s", nicenum ($num), $defstr[$i] );
                $comma = 1;
            }
        }
        $html .= " были повреждены и находятся в ремонте.<br>\n";
    }

    $html .= "\n</td></tr></table>\n";
    return $html;
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
$Skin[5]['name'] = "Neotux-2";
$Skin[5]['path'] = "http://membres.lycos.fr/sheenaha/Neotux-2-maj-light-1440-ai/";
$Skin[6]['name'] = "FirstSoul-V2";
$Skin[6]['path'] = "http://www.sebe.us/ogame/FirstSoul-V2/";
$Skin[7]['name'] = "Lunaris";
$Skin[7]['path'] = "http://ogamespec.com/skin/lunaris/";
$Skin[8]['name'] = "spskin.v.1.0";
$Skin[8]['path'] = "http://uhate.jino.ru/skin/spskin/";
$Skin[9]['name'] = "uLtravioLet";
$Skin[9]['path'] = "http://ogamespec.com/skin/uV/";
$Skin[10]['name'] = "Venator 2.0";
$Skin[10]['path'] = "http://freenet-homepage.de/Veantor_rip/skin/";
$Skin[11]['name'] = "Manger";
$Skin[11]['path'] = "http://ogamespec.com/skin/manger2/";
$Skin[12]['name'] = "Aquila";
$Skin[12]['path'] = "http://lyistra.net/library/ogame/skins/aquila/";
$Skin[13]['name'] = "Cataclysm";
$Skin[13]['path'] = "http://ogamespec.com/skin/Cataclysm/";
$Skin[14]['name'] = "Planets";
$Skin[14]['path'] = "http://digilander.libero.it/crowdp/OGame/Planets1024x768/";
$Skin[15]['name'] = "Reloaded";
$Skin[15]['path'] = "http://ogamespec.com/skin/reloaded/";
$Skin[16]['name'] = "lightgold";
$Skin[16]['path'] = "http://ogamespec.com/skin/lightgold/";
$Skin[17]['name'] = "Spire";
$Skin[17]['path'] = "http://terminator01.funpic.de/spireogame/";

$Skins = 18;

function DrawSkinSelect ()
{
    global $Skin, $Skins;
    $res = "";
    for ($i=0; $i<$Skins; $i++) $res .= "<option value='".$Skin[$i]['path']."'>".$Skin[$i]['name']."</option>\n";
    return $res;
}

/*
 ****************************************************************
 * Генерация страницы.
*/

function pagetitle ($s)
{
    echo "<script language='JavaScript'>\n";
    echo "document.title = '".$s."';\n";
    echo "</script>\n\n";

    echo "<form name='skin_form' style='margin-bottom:0;'>\n";
    echo "Скин: <select name='skinpath' onchange='onSkinChange();'>\n";
    echo DrawSkinSelect();
    echo "</select></form>\n";
}

function specsim ()
{
    global $report;

    $url = "m=".($report['cm']*2)."&k=".($report['ck']*2)."&d=".($report['cd']*2);
    $url .= "&anum=" . $report['Attackers'];
    $url .= "&dnum=" . $report['Defenders'];
    for ($i=0; $i<$report['Attackers']; $i++) {
        $pre = "a" . $i . "_";
        for ($n=202; $n<216; $n++) if ($report[$pre.$n]) $url .= "&" . $pre . "f" . ($n-202) . "=" . $report[$pre.$n];
        $url .= "&" . $pre . "weap=" . $report[$pre."at"] / 10;
        $url .= "&" . $pre . "shld=" . $report[$pre."sh"] / 10;
        $url .= "&" . $pre . "hull=" . $report[$pre."ar"] / 10;
        $url .= "&" . $pre . "g=" . $report[$pre."g"];
        $url .= "&" . $pre . "s=" . $report[$pre."s"];
        $url .= "&" . $pre . "p=" . $report[$pre."p"];
        $url .= "&" . $pre . "name=" . $report[$pre."name"];
    }

    for ($i=0; $i<$report['Defenders']; $i++) {
        $pre = "d" . $i . "_";
        for ($n=202; $n<216; $n++) if ($report[$pre.$n]) $url .= "&" . $pre . "f" . ($n-202) . "=" . $report[$pre.$n];
        if ($i == 0) {
            for ($n=401; $n<409; $n++) if ($report[$pre.$n]) $url .= "&d_" . "d" . ($n-401) . "=" . $report[$pre.$n];
        }
        $url .= "&" . $pre . "weap=" . $report[$pre."at"] / 10;
        $url .= "&" . $pre . "shld=" . $report[$pre."sh"] / 10;
        $url .= "&" . $pre . "hull=" . $report[$pre."ar"] / 10;
        $url .= "&" . $pre . "g=" . $report[$pre."g"];
        $url .= "&" . $pre . "s=" . $report[$pre."s"];
        $url .= "&" . $pre . "p=" . $report[$pre."p"];
        $url .= "&" . $pre . "name=" . $report[$pre."name"];
    }

    $url = "http://ogamespec.com/tools/specsim.htm?" . $url;
    echo "<a href='".$url."' target=_blank>Симулировать</a>\n";
}

function footer ($title, $id)
{
    global $comment;

    if ($comment)
    {
        echo "<br><table width='99%'><tr><td class='c'>Комментарии:</td></tr>\n";
        echo "<table border=0 width='100%' align=left><tr>";
        echo "<td><table><th><p align='left'>" . nl2br($comment). "</p></th></table></td>";
        echo "</table>";
    }

    echo "<br><table width='99%'><tr><td class='c'><a href=\"report.php?rid=".$id."\">Ссылка на доклад:</a></td></tr>\n";
    echo "<tr><th><input onclick='this.select();' style='width: 100%;' size=120 value='[url=http://ogamespec.com/tools/report.php?rid=".$id."]". $title ."[/url]' type='text'></th></tr>\n";
    echo "<tr><th><input onclick='this.select();' style='width: 100%;' size=120 value='http://ogamespec.com/tools/report.php?rid=".$id."' type='text'></th></tr></table>\n\n";

    echo "<form action='report.php' method=get>\n";
    echo "<input type='hidden' name='rid' value='".$id."'>\n";
    echo "<input type='hidden' name='edit' value='1'>\n";
    echo "<table><tr><td>\n";
    echo "<input type=submit value='Редактировать'>\n";
    echo "</td></tr></table>\n";
    echo "</form>\n";

    specsim ();

    echo "<script language='JavaScript'>ShowHideRounds ();</script>\n";
    echo "<!-- Боевой Доклад. Конец. >\n\n";

    echo "</BODY>\n";
    echo "</HTML>\n";
}

    if (!key_exists("rid", $_GET)) $rid = 0;
    else $rid = $_GET['rid'];
    if (!key_exists("edit", $_GET)) $edit = 0;
    else $edit = $_GET['edit'];

    $comment == "";
    
    if (!key_exists("report", $_POST)) $rep = 0;
    else
    {
        $rep =  $_POST['report'];
        $rep = str_replace ("Боевой доклад", "", $rep);
        $rep = str_replace ("<", " <", $rep);
        $rep = str_replace("\r\n", " ", $rep);          // Тупые виндовские переводы строк
        $rep = str_replace ("\t", " ", $rep);
        $search = array ( "'<script[^>]*?>.*?</script>'si",  // Вырезает javaScript
                          "'<[\/\!]*?[^<>]*?>'si",           // Вырезает HTML-теги
                          "'([\r\n])[\s]+'" );             // Вырезает пробельные символы
        $replace = array ("", "", "\\1", "\\1" );
        $rep = preg_replace($search, $replace, $rep);
        $rep = str_replace ("( [", "([", $rep);
        $rep = str_replace ("] )", "])", $rep);
        $rep = str_replace ("])", "]) ", $rep);
    }

    inittabs ();

    // Показать доклад.
    if ($rid && !$edit)
    {
        ConnectDatabase ();
        if (!key_exists("debug", $_GET)) $debug = 0;
        else $debug = $_GET['debug'];
        if (loadreport ($rid) == true)
        {
            $title = $report['Title'];
            pagetitle ($title);
            $html = genreport (1);
            echo $html;
            fleetstats ();      // Статистика по флотам
            //piestats ();        // Пирог
            losstats ();        // Статистика по потерям
            footer ($title, $rid);
            exit ();
        }
    }

    // Показать все доклады (секретная опция)
    if ( key_exists("all", $_GET) )
    {
        ConnectDatabase ();
        $result = dbquery("SELECT * FROM ".BATTLETABLE);
        $rows = dbrows($result);
        $num = 1;
        while ($rows--) {
            $r = dbarray ($result);
            $debug = 0;
            if (loadreport ($r['rid']) == true) {
                echo "$num: <a href='http://ogamespec.com/tools/report.php?rid=".$r['rid']."' target=_blank>".$report['Title']."</a><br/>";
                $num++;
            }
        }
        echo "</BODY>\n";
        echo "</HTML>\n";
        exit ();
    }

    // Редактировать доклад.
    if ($rid && $edit)
    {
        $debug = 0;
        ConnectDatabase ();
        if (loadreport ($rid) == true)
        {
            $title = "Редактировать боевой доклад " . $report['Title'];
            pagetitle ($title);
            $editrep = genreport (0);
        }
        else $editrep == "";
    }

    // Обработать и сохранить доклад.
    if ($rep)
    {
        if ($rest = floodprotect())
        {
            echo ("Попытка флуда! Ещё " . $rest . " секунд.");
            exit ();
        }

        if (detect_cyr_charset ($rep) == 'k')
        {
            $rep = convert_cyr_string ($rep, 'k', 'w');
        }

        ConnectDatabase ();

        if (!key_exists("debug", $_POST)) $debug = 0;
        else $debug = $_POST['debug'];
        if (!key_exists("comment", $_POST)) $comment = 0;
        else
        {
            $comment = strip_tags ($_POST['comment']);
            $comment = str_replace ("'", "", $comment);
            $comment = str_replace ("\"", "", $comment);
        }

        $source = gensource ($rep, &$title);
        if ($source)
        {
            if (loadreport ($id = savereport ($source)))
            {
                pagetitle ($title);
                $html = genreport (1);
                echo $html;
                fleetstats ();      // Статистика по флотам
                //piestats ();        // Пирог
                losstats ();        // Статистика по потерям
                footer ($title, $id);
                exit ();
            }
        }
    }
?>
<table width="99%"><tr><td><center>
<br>
<small>
Чтобы добавить доклад, скопируйте весь текст из окна с докладом (Ctrl+A) и вставьте в поле.<br>
Способ 2: правой кнопкой мыши нажмите на окно с докладом, выберите "Исходный код HTML", скопируйте весь код и вставьте в поле.<br>
</small>
<br>
<form action="report.php" method=post>
<input type="hidden" name="debug" value="0">
<table>
<tr><td class='c'>Вставьте боевой доклад:</td></tr>
<tr><th><textarea cols='150' rows='20' name='report'><?=$editrep?></textarea></th></tr>
<tr><td class='c'>Комментарии:</td></tr>
<tr><th><textarea cols='150' rows='3' name='comment'></textarea></th></tr>
<tr><td><input type=submit value='Обработать'>
<font size=1>&nbsp;&nbsp;&nbsp;Доклады из других логомолотилок не поддерживаются, т.к. оттуда вырезаются ключевые слова, необходимые для разбора.</font>
</td></tr>
</table>
</form>
</center></td></tr></table>

<div id='skin' style='position: absolute; top:10px; right:10px;'>
<form name='skin_form'>
Скин: <select name='skinpath' onchange='onSkinChange();'>
<?=DrawSkinSelect();?>
</select></form>
</div>

<script language='JavaScript'>ShowHideRounds ();</script>
<!-- Боевой Доклад. Конец. -->

</BODY>
</HTML>
