<HTML>
<HEAD><link rel="stylesheet" type="text/css" href="../evolution/formate.css">
<meta http-equiv='content-type' content='text/html; charset=utf-8' />
<TITLE>Шпионский доклад</TITLE>
</HEAD>

<BODY><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<!-- Шпионский Доклад. Начало. -->

<?php

/*
    Обработчик шпионских докладов для Ogame.
    (c) Andorianin, 2009, 2010, 2011
*/
$version = 0.10;
$maxsheets = 12;

require_once "config.php";
require_once "db.php";

define ("SPYTABLE", "spy_reports");
define ("FLOODTIME", 4);

$secretword = "SpecnazRulit";
$debug = 0;
$spy = array ();

// Символьные описания объектов
$desc[1] = "Рудник по добыче металла";
$desc[] = "Рудник по добыче кристалла";
$desc[] = "Синтезатор дейтерия";
$desc[] = "Солнечная электростанция";
$desc[12] = "Термоядерная электростанция";
$desc[14] = "Фабрика роботов";
$desc[] = "Фабрика нанитов";
$desc[21] = "Верфь";
$desc[] = "Хранилище металла";
$desc[] = "Хранилище кристалла";
$desc[] = "Ёмкость для дейтерия";
$desc[31] = "Исследовательская лаборатория";
$desc[33] = "Терраформер";
$desc[] = "Склад альянса";
$desc[41] = "Лунная база";
$desc[] = "Сенсорная фаланга";
$desc[] = "Ворота";
$desc[] = "Ракетная шахта";
$buildmap = array (1, 2, 3, 4, 12, 14, 15, 21, 22, 23, 24, 31, 33, 34, 41, 42, 43, 44);
$desc[106] = "Шпионаж";
$desc[108] = "Компьютерная технология";
$desc[] = "Оружейная технология";
$desc[] = "Щитовая технология";
$desc[] = "Броня космических кораблей";
$desc[113] = "Энергетическая технология";
$desc[] = "Гиперпространственная технология";
$desc[] = "Реактивный двигатель";
$desc[117] = "Импульсный двигатель";
$desc[] = "Гиперпространственный двигатель";
$desc[120] = "Лазерная технология";
$desc[] = "Ионная технология";
$desc[] = "Плазменная технология";
$desc[] = "Межгалактическая исследовательская сеть";
$desc[] = "Экспедиционная технология";
$desc[199] = "Гравитационная технология";
$techmap = array (106, 108, 109, 110, 111, 113, 114, 115, 117, 118, 120, 121, 122, 123, 124, 199);
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
$fleetmap = array (202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215 );
$desc[401] = "Ракетная установка";
$desc[] = "Лёгкий лазер";
$desc[] = "Тяжёлый лазер";
$desc[] = "Пушка Гаусса";
$desc[] = "Ионное орудие";
$desc[] = "Плазменное орудие";
$desc[] = "Малый щитовой купол";
$desc[] = "Большой щитовой купол";
$desc[502] = "Ракета-перехватчик";
$desc[] = "Межпланетная ракета";
$defmap = array (401, 402, 403, 404, 405, 406, 407, 408, 502, 503 );

// Уровень шпионажа:
// 0-только ресы, 1-флот, 2-оборона, 3-постройки, 4-исследования

function timfmt ($n)
{
    if ($n == 0) return "00";
    if ($n < 10) return "0$n";
    else return "$n";
}

function nicenum ($number)
{
    global $spy;
    $pre = $post = "";
    if ($number >= $spy['e'] * 200 && $number)
    {
        $pre = "<font color=red>";
        $post = "</color>";
    }
    return $pre . number_format($number,0,",",".") . $post;
}

function nicenum2 ($number)
{
    return number_format($number,0,",",".");
}

// Выработка
function prod_metal ($lvl)
{
   $hourly = floor (30 * $lvl * pow (1.1, $lvl));
   return $hourly;
}
function prod_crys ($lvl)
{
   $hourly = floor (20 * $lvl * pow (1.1, $lvl));
   return $hourly;
}
function prod_deut ($lvl)
{
   $maxtemp = 50;
   $hourly = floor (10 * $lvl * pow (1.1, $lvl) * (-0.002 * $maxtemp + 1.28));
   return $hourly;
}

/*
 *************************************************************************************
 Расчёт стоимости.
*/

// Стоимость первого уровня.
// Постройки.
$initial[14]['m'] = 400; $initial[14]['k'] = 120; $initial[14]['d'] = 200; $initial[14]['e'] = 0;
$initial[15]['m'] = 1000000; $initial[15]['k'] = 500000; $initial[15]['d'] = 100000; $initial[15]['e'] = 0;
$initial[21]['m'] = 400; $initial[21]['k'] = 200; $initial[21]['d'] = 100; $initial[21]['e'] = 0;
$initial[22]['m'] = 2000; $initial[22]['k'] = 0; $initial[22]['d'] = 0; $initial[22]['e'] = 0;
$initial[23]['m'] = 2000; $initial[23]['k'] = 1000; $initial[23]['d'] = 0; $initial[23]['e'] = 0;
$initial[24]['m'] = 2000; $initial[24]['k'] = 2000; $initial[24]['d'] = 0; $initial[24]['e'] = 0;
$initial[31]['m'] = 200; $initial[31]['k'] = 400; $initial[31]['d'] = 200; $initial[31]['e'] = 0;
$initial[33]['m'] = 0; $initial[33]['k'] = 50000; $initial[33]['d'] = 100000; $initial[33]['e'] = 1000;
$initial[34]['m'] = 20000; $initial[34]['k'] = 40000; $initial[34]['d'] = 0; $initial[34]['e'] = 0;
$initial[44]['m'] = 20000; $initial[44]['k'] = 20000; $initial[44]['d'] = 1000; $initial[44]['e'] = 0;
// Луна
$initial[41]['m'] = 20000; $initial[41]['k'] = 40000; $initial[41]['d'] = 20000; $initial[41]['e'] = 0;
$initial[42]['m'] = 20000; $initial[42]['k'] = 40000; $initial[42]['d'] = 20000; $initial[42]['e'] = 0;
$initial[43]['m'] = 2000000; $initial[43]['k'] = 4000000; $initial[43]['d'] = 2000000; $initial[43]['e'] = 0;

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
$initial[502]['m'] = 8000; $initial[502]['k'] = 0; $initial[502]['d'] = 2000;
$initial[503]['m'] = 12500; $initial[503]['k'] = 2500; $initial[503]['d'] = 10000;

// Исследования.
$initial[106]['m'] = 200; $initial[106]['k'] = 1000; $initial[106]['d'] = 200; $initial[106]['e'] = 0;
$initial[108]['m'] = 0; $initial[108]['k'] = 400; $initial[108]['d'] = 600; $initial[108]['e'] = 0;
$initial[109]['m'] = 800; $initial[109]['k'] = 200; $initial[109]['d'] = 0; $initial[109]['e'] = 0;
$initial[110]['m'] = 200; $initial[110]['k'] = 600; $initial[110]['d'] = 0; $initial[110]['e'] = 0;
$initial[111]['m'] = 1000; $initial[111]['k'] = 0; $initial[111]['d'] = 0; $initial[111]['e'] = 0;
$initial[113]['m'] = 0; $initial[113]['k'] = 800; $initial[113]['d'] = 400; $initial[113]['e'] = 0;
$initial[114]['m'] = 0; $initial[114]['k'] = 4000; $initial[114]['d'] = 2000; $initial[114]['e'] = 0;
$initial[115]['m'] = 400; $initial[115]['k'] = 0; $initial[115]['d'] = 600; $initial[115]['e'] = 0;
$initial[117]['m'] = 2000; $initial[117]['k'] = 4000; $initial[117]['d'] = 600; $initial[117]['e'] = 0;
$initial[118]['m'] = 10000; $initial[118]['k'] = 20000; $initial[118]['d'] = 6000; $initial[118]['e'] = 0;
$initial[120]['m'] = 200; $initial[120]['k'] = 100; $initial[120]['d'] = 0; $initial[120]['e'] = 0;
$initial[121]['m'] = 1000; $initial[121]['k'] = 300; $initial[121]['d'] = 100; $initial[121]['e'] = 0;
$initial[122]['m'] = 2000; $initial[122]['k'] = 4000; $initial[122]['d'] = 1000; $initial[122]['e'] = 0;
$initial[123]['m'] = 240000; $initial[123]['k'] = 400000; $initial[123]['d'] = 160000; $initial[123]['e'] = 0;
$initial[124]['m'] = 4000; $initial[124]['k'] = 8000; $initial[124]['d'] = 4000; $initial[124]['e'] = 0;
$initial[199]['m'] = 0; $initial[199]['k'] = 0; $initial[199]['d'] = 0; $initial[199]['e'] = 300000;

function BuildPrice ( $id, $lvl, &$m, &$k, &$d, &$e )
{
    global $initial;
    switch ($id)
    {
        case 1:   // Шахта металла
            $m = floor (60 * pow(1.5, $lvl-1));
            $k = floor (15 * pow(1.5, $lvl-1));
            $d = $e = 0;
            break;
        case 2:   // Шахта кристалла
            $m = floor (48 * pow(1.6, $lvl-1));
            $k = floor (24 * pow(1.6, $lvl-1));
            $d = $e = 0;
            break;
        case 3:   // Шахта дейта
            $m = floor (225 * pow(1.5, $lvl-1));
            $k = floor (75 * pow(1.5, $lvl-1));
            $d = $e = 0;
            break;
        case 4:   // СЭС
            $m = floor (75 * pow(1.5, $lvl-1));
            $k = floor (30 * pow(1.5, $lvl-1));
            $d = $e = 0;
            break;
        case 12:   // Терма
            $m = floor (900 * pow(1.8, $lvl-1));
            $k = floor (360 * pow(1.8, $lvl-1));
            $d = floor (180 * pow(1.8, $lvl-1));
            $e = 0;
            break;
        default:
            $m = $initial[$id]['m'] * pow(2, $lvl-1);
            $k = $initial[$id]['k'] * pow(2, $lvl-1);
            $d = $initial[$id]['d'] * pow(2, $lvl-1);
            $e = $initial[$id]['e'] * pow(2, $lvl-1);
            break;
    }
}

function ShipyardPrice ( $id, &$m, &$k, &$d, &$e )
{
    global $initial;
    $m = $initial[$id]['m'];
    $k = $initial[$id]['k'];
    $d = $initial[$id]['d'];
    $e = 0;
}

function ResearchPrice ( $id, $lvl, &$m, &$k, &$d, &$e )
{
    global $initial, $spy;

    if ($spy['redesign'] && $id == 124) {
        $m = 100 * floor ( 0.5 + 40 * pow (1.75, $lvl-1) );
        $k = 100 * floor ( 0.5 + 80 * pow (1.75, $lvl-1) );
        $d = 100 * floor ( 0.5 + 40 * pow (1.75, $lvl-1) );
        $e = 0;
        return;
    }

    if ($id == 199) {
        $m = $initial[$id]['m'] * pow(3, $lvl-1);
        $k = $initial[$id]['k'] * pow(3, $lvl-1);
        $d = $initial[$id]['d'] * pow(3, $lvl-1);
        $e = $initial[$id]['e'] * pow(3, $lvl-1);
    }
    else {
        $m = $initial[$id]['m'] * pow(2, $lvl-1);
        $k = $initial[$id]['k'] * pow(2, $lvl-1);
        $d = $initial[$id]['d'] * pow(2, $lvl-1);
        $e = $initial[$id]['e'] * pow(2, $lvl-1);
    }
}

/*
 ***********************************************************************************
*/

// bool/array str_between( string str, string start_str, string end_str )
function str_between($str,$start,$end) {
  if (preg_match_all('/' . preg_quote($start) . '(.*?)' . preg_quote($end) . '/',$str,$matches)) {
   return $matches[1];
  }
  // no matches
  return false;
}

// Защита от флуда.
// Открываем файл, проверяем его дату. Если его возраст меньше FLOODTIME, то нас флудят.
// Возвращает количество секунд до окончания попытки флуда, или 0, если всё в порядке.
function floodprotect ()
{
    $floodfile = "spyflood.txt";
    $now = time ();
    $old = filemtime ($floodfile);
    if ( ($now - $old) <= FLOODTIME) return FLOODTIME - ($now - $old);
    $f = fopen ($floodfile, 'w');
    fwrite ($f, $now);
    fclose ($f);
    return 0;
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

    $result = dbquery ( "show tables like '".SPYTABLE."'" );
    if ( dbrows ($result) == 0 )
    {
        $query = "create table ".SPYTABLE." ( " .
                      "spyid CHAR(64), sheets INT, sheetnum INT, player CHAR(64), planet CHAR(64), g INT, s INT, p INT, level INT, date_m INT, date_d INT, date_hr INT, date_min INT, date_sec INT, m CHAR(64), k CHAR(64), d CHAR(64), e bigint(20), ".
                      "counter INT, comment TEXT, " .
                      "o202 bigint(20), o203 bigint(20), o204 bigint(20), o205 bigint(20), o206 bigint(20), o207 bigint(20), o208 bigint(20), o209 bigint(20), o210 bigint(20), o211 bigint(20), o212 bigint(20), o213 bigint(20), o214 bigint(20), o215 bigint(20), " .
                      "o401 bigint(20), o402 bigint(20), o403 bigint(20), o404 bigint(20), o405 bigint(20), o406 bigint(20), o407 bigint(20), o408 bigint(20), o502 bigint(20), o503 bigint(20), " .
                      "o1 INT, o2 INT, o3 INT, o4 INT, o12 INT, o14 INT, o15 INT, o21 INT, o22 INT, o23 INT, o24 INT, o31 INT, o33 INT, o34 INT, o44 INT, o106 INT, o108 INT, o109 INT, o110 INT, o111 INT, o113 INT, o114 INT, o115 INT, o117 INT, o118 INT, o120 INT, o121 INT, o122 INT, o123 INT, o124 INT, o199 INT, o41 INT, o42 INT, o43 INT, " .
                      "activity INT, redesign INT, " .
                      "PRIMARY KEY (spyid) );";
        dbquery  ($query);
    }
}

/*
 ****************************************************************
 * Процессор шпионских докладов.
*/

// Возвращает количество кораблей, вместимостью cargo, которыми нужно вывезсти m металла, k кристалла и d дейтерия.
function shipCount ($m, $k, $d, $cargo)
{
    $total = $m + $k + $d;
    $count = 1;
    $oldm = $m; $oldk = $k; $oldd = $d;

    if ($cargo == 0 || $total == 0) return 0;
    if ($total < $cargo) return 1;

    while ($count < (($total / $cargo) * 2))
    {
        $m = $oldm; $k = $oldk; $d = $oldd;

        $oldcargo = $cargo;
        $cargo = $count * $cargo;
        $mc = $cargo / 3;
        if ($m < $mc) $mc = $m;
        $cargo = $cargo - $mc;
        $kc = $cargo / 2;
        if ($k < $kc) $kc = $k;
        $cargo = $cargo - $kc;
        $dc = $cargo;
        if ($d < $dc)
        {
            $dc = $d;
            $cargo = $cargo - $dc;
            $m = $m - $mc;
            $half = $cargo / 2;
            $bonus = $half;
            if ($m < $half) $bonus = $m;
            $mc += $bonus;
            $cargo = $cargo - $bonus;
            $k = $k - $kc;
            if ($k < $cargo) $kc += $k;
            else $kc += $cargo;
        }
        $totc = $mc + $kc + $dc;
        $prc = ceil (($totc * 100) / $total);
        $cargo = $oldcargo;
        
        if ($prc >= 100) return $count;
        $count ++;
    }
}

// Сгенерировать уникальный ID.
function genid ()
{
    global $spy;
    $title = "Сырьё на ".$spy['planet']." [".$spy['g'].":".$spy['s'].":".$spy['p']."] (Игрок '".$spy['player']."')";
    $date = " на ".timfmt($spy['date_m'])."-".timfmt($spy['date_d'])." ".timfmt($spy['date_hr']).":".timfmt($spy['date_min']).":".timfmt($spy['date_sec']);
    $id = md5 ($title . $date . $secretword);
    return $id;
}

// Получить количество листов в докладе.
function reportsheets ($id)
{
    $result = dbquery("SELECT * FROM ".SPYTABLE." WHERE spyid='".$id."' AND sheetnum='0'");
    if (dbrows($result) != 0)
    {
        $row = dbarray($result);
        if ($row['sheets'] == 0) $row['sheets'] = 1;    // Для совместимости.
        return $row['sheets'];
    }
    else return 0;
}

// Сохранить шпионский доклад в базе. Возвращает id доклада.
function savereport ($id, $sheet)
{
    global $spy, $debug;

    if ($debug) echo "Save spy report (".$id.", sheet ".$sheet."): <br>";

/*
    DELETE FROM SPYTABLE WHERE spyid='xxx' AND sheetnum='xxx'
    INSERT INTO SPYTABLE (spyid, sheetnum) VALUES ('555', '555')
    UPDATE SPYTABLE SET xxx = 'xxx' WHERE spyid='xxx' AND sheetnum='xxx'
*/

    dbquery( "DELETE FROM ".SPYTABLE." WHERE spyid='".$id."' AND sheetnum='".$sheet."'" );
    dbquery( "INSERT INTO ".SPYTABLE." (spyid, sheetnum) VALUES ('".$id."', '".$sheet."')" );
    foreach ($spy as $i=>$entry)
    {
        if ($i != 'spyid')
        {
            $query = "UPDATE ".SPYTABLE." SET ".$i." = '".$entry."' WHERE spyid='".$id."' AND sheetnum='".$sheet."'";
            dbquery( $query);
            if ($debug) echo "$query <br> ";
        }
    }
}

// Загрузить шпионский доклад. Возвращает 1, если ок, или 0 если нет такого id в базе.
function loadreport ($id, $sheet)
{
    global $spy, $debug;
    if ($debug) echo "Load spy report (".$id."): <br>";
    $result = dbquery("SELECT * FROM ".SPYTABLE." WHERE spyid='".$id."' AND sheetnum='".$sheet."'");
    if (dbrows($result) != 0)
    {
        $spy = dbarray($result);
        if ($debug)
        {
            echo "<pre>";
            print_r ($spy);
            echo "</pre>";
        }
        return true;
    }
    else return false;
}

// Обработать шпионский доклад. Результат поместить в массив $spy.
// Возвращает остаток необработанной строки или "0", если данных больше нет.
function parsespy ($text)
{
    global $spy, $desc, $fleetmap, $defmap, $buildmap, $techmap;
    $map = array ( 'fleetmap', 'defmap', 'buildmap', 'techmap' );
    $leveldesc = array ( "Флоты", "Оборона", "Постройки", "Исследования" );

    for ($level=1; $level<=4; $level++)
    {
        for ($i=0; $i<sizeof($$map[$level-1]); $i++)
        {
            $tab = $$map[$level-1];
            $spy['o'.$tab[$i]] = 0;
        }
    }

    $spy['counter'] = 0;
    $spy['level'] = 0;

    $s = stripcslashes ($text);    
    $s = str_replace (":", " ", $s);
    $s = str_replace (".", "", $s);
    if ($debug) echo $s . "<hr>";

    // Найти начало шпионского доклада и вырезать имя планеты.
    $s = strstr ($s, "Сырьё на");
    if ($s == "") return "0";
    $tmp = str_between ($s, "Сырьё на", "[");
    if ($tmp == false) return "0";
    $spy['planet'] = trim ($tmp[0]);

    // Координаты
    $s = strstr ($s, "[");
    $tmp = str_between ($s, "[", "]");
    $coords = explode (" ", $tmp[0]);
    $spy['g'] = $coords[0];
    $spy['s'] = $coords[1];
    $spy['p'] = $coords[2];

    $spy['activity'] = 0;

    // Имя игрока
    $s = strstr ($s, "Игрок");
    $tmp = str_between ($s, " ", ")");
    if ($tmp == false) return "0";
    $spy['player'] = trim ($tmp[0]);

    // Дата
    $s = strstr ($s, ")"); $s = strpbrk ($s, "0123456789");
    sscanf ( $s, "%d-%d %d %d %d", 
             $spy['date_m'], $spy['date_d'], $spy['date_hr'], $spy['date_min'], $spy['date_sec'] );

    // Ресурсы Редизайн
    if ( strstr ($s, "Металл") != FALSE ) {
        $s = strstr ($s, "Металл"); $s = strstr ($s, " ");
        sscanf ( $s, "%d", $spy['m'] );
        $s = strstr ($s, "Кристалл"); $s = strstr ($s, " ");
        sscanf ( $s, "%d", $spy['k'] );
        $s = strstr ($s, "Дейтерий"); $s = strstr ($s, " ");
        sscanf ( $s, "%d", $spy['d'] );
        $s = strstr ($s, "Энергия"); $s = strstr ($s, " ");
        sscanf ( $s, "%d", $spy['e'] );
        $desc[124] = "Астрофизика";
        $spy['redesign'] = 1;
    }
    else {
        $s = strstr ($s, "металла"); $s = strstr ($s, " ");
        sscanf ( $s, "%d", $spy['m'] );
        $s = strstr ($s, "кристалла"); $s = strstr ($s, " ");
        sscanf ( $s, "%d", $spy['k'] );
        $s = strstr ($s, "дейтерия"); $s = strstr ($s, " ");
        sscanf ( $s, "%d", $spy['d'] );
        $s = strstr ($s, "энергии"); $s = strstr ($s, " ");
        sscanf ( $s, "%d", $spy['e'] );
        $desc[124] = "Экспедиционная технология";
        $spy['redesign'] = 0;
    }

    // Шанс на защиту от шпионажа.
    $ss = strstr ($s, "Шанс на защиту от шпионажа");  
    $ss = strpbrk ($ss, "0123456789");
    sscanf ( $ss, "%d", $spy['counter'] );

    $old = $s;
    $pos = strpos ($s, "Сырьё на");
    if ($pos != FALSE) $s = substr ($s, 0, $pos);

    // Флот, оборона, постройки, исследования. Хитровыебанный цикл.
    for ($level=1; $level<5; $level++)
    {
        $tab = $$map[$level-1];
        $s = strstr ($s, $leveldesc[$level-1]);
        if ($s == "") break;
        for ($i=0; $i<sizeof($$map[$level-1]); $i++)
        {
            $sub = strstr ($s, $desc[$tab[$i]]);
            if ($sub)
            {
                //echo "Found " . $desc[$tab[$i]] . "<br>";
                $value = strpbrk ($sub, "0123456789");
                sscanf ($value, "%d", $spy['o'.$tab[$i]]);
            }
        }
        $spy['level']++;
    }

    // Есть ещё доклады?
    return strstr ($old, "Сырьё на");
}

// Показать шпионский доклад. Использует в качестве входных данных - массив $spy.
function genspy ($ext)
{
    global $spy, $desc, $fleetmap, $defmap, $buildmap, $techmap;
    $map = array ( 'fleetmap', 'defmap', 'buildmap', 'techmap' );
    $leveldesc = array ( "Флоты", "Оборона", "Постройки", "Исследования" );
    echo "<table>\n\n<td colspan=3 class=b>\n\n";

    if ( $spy['redesign'] ) $desc[124] = "Астрофизика";
    else $desc[124] = "Экспедиционная технология";

    $restotal = $spy['m'] + $spy['k'] + $spy['d'];
    $mt = shipCount ($spy['m']/2, $spy['k']/2, $spy['d']/2, 5000);
    $bt = shipCount ($spy['m']/2, $spy['k']/2, $spy['d']/2, 25000);

    if ( $spy['redesign'] ) {
        $title = "Сырьё на ".$spy['planet']." [".$spy['g'].":".$spy['s'].":".$spy['p']."] (Игрок: ".$spy['player'].")";
        $date = " " . timfmt($spy['date_m'])."-".timfmt($spy['date_d'])." ".timfmt($spy['date_hr']).":".timfmt($spy['date_min']).":".timfmt($spy['date_sec']);
    }
    else {
        $title = "Сырьё на ".$spy['planet']." [".$spy['g'].":".$spy['s'].":".$spy['p']."] (Игрок: ".$spy['player'].")";
        $date = "<br /> на ".timfmt($spy['date_m'])."-".timfmt($spy['date_d'])." ".timfmt($spy['date_hr']).":".timfmt($spy['date_min']).":".timfmt($spy['date_sec']);
    }
    echo "<table width=400>\n";
    echo "<tr><td class=c colspan=4>".$title.$date."</td></tr>\n";
    if ( $spy['redesign'] ) {
        echo "<tr><td>Металл:</td><td>".nicenum($spy['m'])."</td><td>Кристалл:</td><td>".nicenum($spy['k'])."</td></tr>\n";
        echo "<tr><td>Дейтерий:</td><td>".nicenum($spy['d'])."</td><td>Энергия:</td><td>".nicenum($spy['e'])."</td></tr>\n";
    }
    else {
        echo "<tr><td>металла:</td><td>".nicenum($spy['m'])."</td><td>кристалла:</td><td>".nicenum($spy['k'])."</td></tr>\n";
        echo "<tr><td>дейтерия:</td><td>".nicenum($spy['d'])."</td><td>энергии:</td><td>".nicenum($spy['e'])."</td></tr>\n";
    }
    if ($ext)
    {
        echo "<tr><td>всего:</td><td>".nicenum($restotal)."</td><td>МТ:".$mt."</td><td>БТ:".$bt."</td></tr>\n";
    }
    echo "</table>\n\n";

    // Непонятная часть, вроде раньше возращала активность на планете.
    echo "<table width=400>\n";
    echo "<tr><td class=c colspan=4> </td></tr>\n";
    echo "<TR><TD colspan=4><div onmouseover='return overlib(\"&lt;font color=white&gt;Активность означает, что сканируемый игрок был активен на своей планете, либо на него был произведён вылет флота другого игрока.&lt;/font&gt;\", STICKY, MOUSEOFF, DELAY, 750, CENTER, WIDTH, 100, OFFSETX, -130, OFFSETY, -10);' onmouseout='return nd();'></TD></TR>\n";
    echo "</table>\n\n";

    for ($level=1; $level<=4; $level++)
    {
        if ($spy['level'] >= $level)
        {
            echo "<table width=400><tr><td class=c colspan=4>".$leveldesc[$level-1]."     </td></tr>  </tr>\n";
            for ($i=0,$shown=0; $i<sizeof($$map[$level-1]); $i++)
            {
                $tab = $$map[$level-1];
                if ($spy['o'.$tab[$i]] > 0)
                {
                    echo "<td>".$desc[$tab[$i]]."</td><td>".nicenum2($spy['o'.$tab[$i]])."</td>";
                    $shown++;
                    if (!($shown & 1)) echo " </tr>\n";
                }
            }
            echo "</table>\n\n";
        }
        else
        {
            echo "<table width=400><tr><td class=c colspan=4><font color=red>".$leveldesc[$level-1]."     </font></td></tr>  </tr>\n";
            echo "</table>\n\n";
        }
    }

    echo "<center> Шанс на защиту от шпионажа:".$spy['counter']."%</center>\n";

    if ($spy['comment'] && $ext)
    {
        echo "<table width=400>\n";
        echo "<tr><td class='c'>Комментарии</td></tr>\n";
        echo "<tr><td colspan=3 class=b>".$spy['comment']."</td></tr>\n";
        echo "</table>\n\n";
    }

    echo "</td></tr>\n</table>\n\n";

    echo "<center><form action='http://websim.speedsim.net/' method=get target='_blank'>\n";
    echo "<input type='hidden' name='lang' value='ru'>\n";
    echo "<input type='hidden' name='enemy_name' value='".$spy['planet']."'>\n";
    echo "<input type='hidden' name='enemy_pos' value='".$spy['g'].":".$spy['s'].":".$spy['p']."'>\n";
    echo "<input type='hidden' name='enemy_metal' value='".$spy['m']."'>\n";
    echo "<input type='hidden' name='enemy_crystal' value='".$spy['k']."'>\n";
    echo "<input type='hidden' name='enemy_deut' value='".$spy['d']."'>\n";
    $n = 0;
    for ($i=202; $i<=215; $i++, $n++) {
        if ($spy["o".$i] > 0) {
            echo "<input type='hidden' name='ship_d0_".$n."_b' value='".$spy["o".$i]."'>\n";
        }
    }
    for ($i=401; $i<=408; $i++, $n++) {
        if ($spy["o".$i] > 0) {
            echo "<input type='hidden' name='ship_d0_".$n."_b' value='".$spy["o".$i]."'>\n";
        }
    }
    if ($spy["o109"]) echo "<input type='hidden' name='tech_d0_0' value='".$spy["o109"]."'>\n";
    if ($spy["o110"]) echo "<input type='hidden' name='tech_d0_1' value='".$spy["o110"]."'>\n";
    if ($spy["o111"]) echo "<input type='hidden' name='tech_d0_2' value='".$spy["o111"]."'>\n";

    echo "<table><tr><td>\n";
    echo "<input type=submit value='Симулировать Вебсимом'>\n";
    echo "</td></tr></table>\n";
    echo "</form></center>\n";
}

function spytitle ($wnd)
{
    global $spy;

    if ($spy['sheets'] > 1)
    {
        return "Несколько докладов (".$spy['sheets'].")";
    }
    else
    {
        $resk = ($spy['m'] + $spy['k'] + $spy['d']) / 1000;
        $monstr = array ( 'Нульваря', 'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек' );
        $title = $spy['g'].":".$spy['s'].":".$spy['p']." ".$spy['planet']. " " .$spy['player'] . " ";
        $date = $spy['date_d'] . " " . $monstr[$spy['date_m']];
        $hr = $spy['date_hr'];
        if ($hr >= 0 && $hr <5) $date .= " ночь";
        else if ($hr >= 5 && $hr <10) $date .= " утро";
        else if ($hr >= 10 && $hr <15) $date .= " день";
        else if ($hr >= 15 && $hr <20) $date .= " вечер";
        else if ($hr >= 20 && $hr <24) $date .= " ночь";
        if ($resk >= 3000 && !$wnd) return $title . $date . " ([color=red]" . nicenum2($resk) . "к[/color])";
        else return $title . $date . " (" . nicenum2($resk) . "к)";
    }
}

?>

<SCRIPT src="../includes/jscripts/overlib.js" type="text/javascript" language="JavaScript"></SCRIPT>

<center><br>

<?php

function pagetitle ()
{
    echo "<script language='JavaScript'>\n";
    echo "document.title = '".spytitle(1)."';\n";
    echo "</script>\n";
}

function show ($spyid)
{
    global $spy, $fleetmap, $defmap, $buildmap, $techmap;
    $mine_m = $mine_k = $mine_d = $solar = $fusion = $fnum = 0;
    $prodm = $prodk = $prodd = 0;
    $score = $rscore = 0;
    $research_count = false;
    $sheets = reportsheets ($spyid);
    if ($sheets > 0)
    {
        echo "<table width=100% colspan='3'>";
        for ($sheet=0; $sheet<$sheets; $sheet++)
        {
            if ($sheet == 0 || $sheet == 3 || $sheet == 6) echo "<tr align=center>";
            if (loadreport ($spyid, $sheet) == true)
            {
                echo "<td>";
                genspy (1);
                $mine_m += $spy['o1'];
                $prodm += prod_metal ($spy['o1']);
                $mine_k += $spy['o2'];
                $prodk += prod_crys ($spy['o2']);
                $mine_d += $spy['o3'];
                $prodd += prod_deut ($spy['o3']);
                $solar += $spy['o4'];
                $fusion += $spy['o12'];
                for ($i=202; $i<=215; $i++) $fnum += $spy["o".$i];
                echo "</td>";

                // Посчитать стоимость.
                if ( $spy['level'] >= 1 )        // Флот
                {
                    foreach ($fleetmap as $i=>$gid)
                    {
                        $m = $k = $d = $e = 0;
                        ShipyardPrice ( $gid, &$m, &$k, &$d, &$e );
                        $score += floor ( ($m + $k + $d) / 1000 ) * $spy["o$gid"];
                    }
                }
                if ( $spy['level'] >= 2 )        // Оборона
                {
                    foreach ($defmap as $i=>$gid)
                    {
                        $m = $k = $d = $e = 0;
                        ShipyardPrice ( $gid, &$m, &$k, &$d, &$e );
                        $score += floor ( ($m + $k + $d) / 1000 ) * $spy["o$gid"];
                    }
                }
                if ( $spy['level'] >= 3 )        // Постройки
                {
                    foreach ($buildmap as $i=>$gid)
                    {
                        $level = $spy["o$gid"];
                        for ($lvl=1; $lvl<=$level; $lvl++) {
                            $m = $k = $d = $e = 0;
                            BuildPrice ( $gid, $lvl, &$m, &$k, &$d, &$e );
                            $score += floor ( ($m + $k + $d) / 1000 );
                        }
                    }
                }
                if ( $spy['level'] >= 4 && !$research_count)        // Исследования (считать только один раз)
                {
                    foreach ($techmap as $i=>$gid)
                    {
                        $level = $spy["o$gid"];
                        for ($lvl=1; $lvl<=$level; $lvl++) {
                            $m = $k = $d = $e = 0;
                            ResearchPrice ( $gid, $lvl, &$m, &$k, &$d, &$e );
                            $score += floor ( ($m + $k + $d) / 1000 );
                            $rscore += floor ( ($m + $k + $d) / 1000 );
                        }
                    }
                    $research_count = true;
                }
            }
            if ($sheet == 2 || $sheet == 5 || $sheet == 8) echo "</tr>";
            if ($sheet == 0) pagetitle ();
        }
        echo "</table>";

        if ($sheets > 1) 
        {
            $mine_m = round ($mine_m / $sheets);
            $mine_k = round ($mine_k / $sheets);
            $mine_d = round ($mine_d / $sheets);
            $solar = round ($solar / $sheets);
            $fusion = round ($fusion / $sheets);
            echo "<table>";
            echo "<tr><td class='c'>Средний уровень шахт (М-К-Д): $mine_m-$mine_k-$mine_d</td></tr>";
            echo "<tr><td class='c'><a title='Выработка дейтерия при температуре 50°C'>Выработка в сутки</a>: ".nicenum2($prodm*24)." металла, ".nicenum2($prodk*24)." кристалла и ".nicenum2($prodd*24)." дейтерия</td></tr>";
            echo "<tr><td class='c'>Средний уровень электростанций: CЭС $solar, ТЭС $fusion</td></tr>";
            echo "<tr><td class='c'>Общая стоимость: ".nicenum2($score)." оч. (из них в исследованиях ".nicenum2($rscore).")</td></tr>";
            echo "<tr><td class='c'>Всего флота: ".nicenum2($fnum)." ед.</td></tr>";
            echo "</table><br>";
        }
        else {
            echo "<table>";
            echo "<tr><td class='c'><a title='Выработка дейтерия при температуре 50°C'>Выработка в сутки</a>: ".nicenum2($prodm*24)." металла, ".nicenum2($prodk*24)." кристалла и ".nicenum2($prodd*24)." дейтерия</td></tr>";
            echo "<tr><td class='c'>Всего флота: ".nicenum2($fnum)." ед.</td></tr>";
            echo "</table><br>";
        }

        footer ($spyid);
        exit ();
    }
}

function footer ($spyid)
{
    global $spy;
    echo "<table width='70%'><tr><td class='c'><a href=\"spy.php?spyid=".$spyid."\">Ссылка на шпионский доклад:</a></td></tr>\n";
    echo "<tr><th><input onclick='this.select();' style='width: 100%;' size=120 value='[url=http://ogamespec.com/tools/spy.php?spyid=".$spyid."]". spytitle(0) ."[/url]' type='text'></th></tr>\n";
    echo "<tr><th><input onclick='this.select();' style='width: 100%;' size=120 value='http://ogamespec.com/tools/spy.php?spyid=".$spyid."' type='text'></th></tr></table>\n\n";

    echo "<!-- Шпионский Доклад. Конец. -->\n\n";
    echo "</BODY>\n";
    echo "</HTML>\n";
}

/*
 ****************************************************************
 * Генерация страницы.
*/
    if (!key_exists("spyid", $_GET)) $spyid = 0;
    else $spyid = $_GET['spyid'];
    if (!key_exists("report", $_POST)) $report = 0;
    else $report = strip_tags ($_POST['report']);
    if (!key_exists("comment", $_POST)) $comment = 0;
    else
    {
        $comment = strip_tags ($_POST['comment']);
        $comment = str_replace ("'", "", $comment);
        $comment = str_replace ("\"", "", $comment);
    }

    if ($spyid)
    {
        ConnectDatabase ();
        show ($spyid);
    }

    if ($report)
    {
        if ($rest = floodprotect())
        {
            echo ("Попытка флуда! Ещё " . $rest . " секунд.");
            exit ();
        }

        if (detect_cyr_charset ($report) == 'k')
        {
            $report = convert_cyr_string ($report, 'k', 'w');
        }

        if ($debug) echo $report . "<hr>" . $comment . "<hr>";
        ConnectDatabase ();
        $spy['comment'] = $comment;
        $spy['sheets'] = 0;
        $tmp = $report;
        while ($spy['sheets'] < $maxsheets)  // Посчитать количество докладов.
        {
            $tmp = parsespy ($tmp);
            if ($tmp === "0") break;
            $spy['sheets'] ++;
        }
        $sheet = 0;
        while ($sheet < $maxsheets)  // Не более $maxsheets докладов.
        {
            $report = parsespy ($report);
            if ($report === "0") break;
            if ($sheet == 0) $spyid = genid ();
            savereport ($spyid, $sheet);
            $sheet ++;
        }
        show ($spyid);
    }
?>
<form action="spy.php" method=post>
<br><br><br><br><br><br><table>
<tr><td class='c'>Вставьте один или <a title='не более <?=$maxsheets?>'>несколько</a> шпионских докладов:</td></tr>
<tr><th><textarea cols='150' rows='20' name='report'></textarea></th></tr>
<tr><td class='c'>Комментарии:</td></tr>
<tr><th><textarea cols='150' rows='3' name='comment'></textarea></th></tr>
<tr><td><input type=submit value='Обработать'></td></tr>
</table>
</form>

</center>

<!-- Шпионский Доклад. Конец. -->

</BODY>
</HTML>