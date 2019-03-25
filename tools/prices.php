<HTML>
<HEAD><link rel="stylesheet" type="text/css" href="../evolution/formate.css">
<meta http-equiv='content-type' content='text/html; charset=utf-8' />
<link rel="stylesheet" type="text/css" href="../css/default.css">
<TITLE>Расчет стоимости</TITLE>
</HEAD>

<BODY><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<!-- Расчет Стоимости. Начало. -->

<SCRIPT src="../includes/jscripts/overlib.js" type="text/javascript" language="JavaScript"></SCRIPT>

<?php
/*
    Расчет стоимости и времени постройки для Ogame.
    (c) Andorianin, 2009
*/

$version = 0.02;
$debug = 0;

// Таблица стоимости 1-го уровня.

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

// ----------------------------------------
// Вспомогательные функции.

function issel($id)
{
  global $selected_obj;
  if ($id == $selected_obj) echo "SELECTED";
  else echo "";
}

function speedlist($max)   // Вывести список возможных ускорений.
{
  global $speed;
  for ($i=1; $i<=$max; $i++)
  {
    echo "<option value='$i'";
    if ($i==$speed) echo "SELECTED";
    echo ">".$i."x</option>\n";
  }
}

function checkgeologist()
{
  global $geologist;
  if ($geologist=="on") echo "CHECKED";
  else echo "";
}

function checkengineer()
{
  global $engineer;
  if ($engineer=="on") echo "CHECKED";
  else echo "";
}

function timfmt ($n)
{
  if ($n < 10) return "0$n";
  else return "$n";
}

function printtime($seconds)
{
  $days = floor($seconds / (24*3600));
  $hours = floor($seconds / 3600 % 24);
  $mins = floor($seconds  / 60 % 60);
  $secs = floor($seconds / 1 % 60);
  if ($days) echo "$days"."дн ";
  echo timfmt($hours). ":" . timfmt($mins) . ":" . timfmt($secs);
}

function nicenum ($number)
{
    return number_format($number,0,",",".");
}

function aprox ($n) { return floor($n); }

// Выработка
function prod_metal ($lvl)
{
   global $geologist;
   $hourly = floor (30 * $lvl * pow (1.1, $lvl));
   if ($geologist) $hourly += floor($hourly * 0.1);
   return $hourly;
}
function prod_crys ($lvl)
{
   global $geologist;
   $hourly = floor (20 * $lvl * pow (1.1, $lvl));
   if ($geologist) $hourly += floor($hourly * 0.1);
   return $hourly;
}
function prod_deut ($lvl)
{
   global $geologist;
   global $maxtemp; 
   $hourly = floor (10 * $lvl * pow (1.1, $lvl) * (-0.002 * $maxtemp + 1.28));
   if ($geologist) $hourly += floor($hourly * 0.1);
   return $hourly;
}
function prod_solar ($lvl)
{
    global $engineer;
    $prod = aprox (20 * $lvl * pow (1.1, $lvl));
    if ($engineer) $prod += floor($prod * 0.1);
    return $prod;
}
function prod_fusion ($lvl)
{
    global $energo, $engineer;
    $prod = aprox (30 * $lvl * pow (1.05 + $energo*0.01, $lvl));
    if ($engineer) $prod += floor($prod * 0.1);
    return $prod;
}
function prod_sat ()
{
    global $maxtemp, $engineer;
    $prod = floor (($maxtemp / 4) + 20);
    if ($prod > 50) $prod = 50;
    return $prod;
}

// Потребление
function cons_metal ($lvl) { return ceil (10 * $lvl * pow (1.1, $lvl)); }
function cons_crys ($lvl) { return ceil (10 * $lvl * pow (1.1, $lvl)); }
function cons_deut ($lvl) { return ceil (20 * $lvl * pow (1.1, $lvl)); }
function cons_fusion ($lvl) { return ceil (10 * $lvl * pow (1.1, $lvl)); }

// Вместимость
function store_capacity ($lvl) { return 100000 + 50000 * (ceil (pow (1.6, $lvl) - 1)); }

// Расчет количества рабов.
function recycles_need ($met, $crys) { return ceil (($met + $crys) / 20000); }

function addinfo ($title, $num)
{
    echo "<tr><th colspan=4><div  align=left>$title</div></th><th>".nicenum($num)."</th></tr>\n";
}

// ----------------------------------------
// Главная часть

// Выставить значения по умолчанию.
// TODO: брать из базы данных пользователя и сохранять каждый раз при нажатии на 'ok'

if ($_POST['obj'] == "") $selected_obj = 1;   // Выбранный объект для постройки
else $selected_obj = $_POST['obj'];
if ($_POST['level'] == "") $level = 1;           // Уровень
else $level = $_POST['level'];
if ($level == 0) $level = 1;
if ($level > 99 && $selected_obj < 200) $level = 99;
if ($selected_obj == 407 || $selected_obj == 408) $level = 1; // Кумполы
if ($_POST['rf'] == "") $robots = 0;           // Уровень фабрики роботов
else $robots = $_POST['rf'];
if ($_POST['nf'] == "") $nanits = 0;           // Уровень фабрики нанитов
else $nanits = $_POST['nf'];
if ($_POST['shp'] == "") $shipyard = 0;           // Уровень верфи
else $shipyard = $_POST['shp'];
if ($_POST['rl'] == "") $reslab = 0;           // Уровень ИЛ
else $reslab = $_POST['rl'];
if ($_POST['mt'] == "") $maxtemp = 50;           // Максимальная температура 
else $maxtemp = $_POST['mt'];
if ($_POST['energo'] == "") $energo = 0;           // Энергетическая технология
else $energo = $_POST['energo'];
if ($_POST['speedfactor'] == "") $speed = 1;           // Ускорение
else $speed = $_POST['speedfactor'];
$geologist = $_POST['geologist'];             // Геолог
$engineer = $_POST['engineer'];               // Инженер

if ($debug)
{
  echo "Selected=$selected_obj; Level=$level<br>";
  echo "Robots=$robots; Nanite=$nanits; Shipyard=$shipyard; Reslab=$reslab; Max temp=$maxtemp; Energo: $energo; Speed=$speed; Geologist=$geologist; Engineer=$engineer";
}

// -----------------------------------------------------
// Вычислить стоимость.

$price['m'] = $price['k'] = $price['d'] = $price['e'] = 0;
$totalprice['m'] = $totalprice['k'] = $totalprice['d'] = $totalprice['e'] = 0;
$time = $totaltime = 0;

if ($selected_obj >= 202)
{
    $price['m'] = $initial[$selected_obj]['m'];
    $price['k'] = $initial[$selected_obj]['k'];
    $price['d'] = $initial[$selected_obj]['d'];
    $price['e'] = 0;
    $totalprice['m'] = $price['m'] * $level;
    $totalprice['k'] = $price['k'] * $level;
    $totalprice['d'] = $price['d'] * $level;
    $totalprice['e'] = 0;
}
else for ($i=1; $i<=$level; $i++)
{
    if ($i == $level)  switch ($selected_obj)
    {
        case 1:   // Шахта металла
            $price['m'] = floor (60 * pow(1.5, $level-1));
            $price['k'] = floor (15 * pow(1.5, $level-1));
            $price['d'] = $price['e'] = 0;
            break;
        case 2:   // Шахта кристалла
            $price['m'] = floor (48 * pow(1.6, $level-1));
            $price['k'] = floor (24 * pow(1.6, $level-1));
            $price['d'] = $price['e'] = 0;
            break;
        case 3:   // Шахта дейта
            $price['m'] = floor (225 * pow(1.5, $level-1));
            $price['k'] = floor (75 * pow(1.5, $level-1));
            $price['d'] = $price['e'] = 0;
            break;
        case 4:   // СЭС
            $price['m'] = floor (75 * pow(1.5, $level-1));
            $price['k'] = floor (30 * pow(1.5, $level-1));
            $price['d'] = $price['e'] = 0;
            break;
        case 12:   // Терма
            $price['m'] = floor (900 * pow(1.8, $level-1));
            $price['k'] = floor (360 * pow(1.8, $level-1));
            $price['d'] = floor (180 * pow(1.8, $level-1));
            $price['e'] = 0;
            break;
        case 199:   // Грава
            $price['m'] = $initial[$selected_obj]['m'] * pow(3, $level-1);
            $price['k'] = $initial[$selected_obj]['k'] * pow(3, $level-1);
            $price['d'] = $initial[$selected_obj]['d'] * pow(3, $level-1);
            $price['e'] = $initial[$selected_obj]['e'] * pow(3, $level-1);
            break;

        default:
            $price['m'] = $initial[$selected_obj]['m'] * pow(2, $level-1);
            $price['k'] = $initial[$selected_obj]['k'] * pow(2, $level-1);
            $price['d'] = $initial[$selected_obj]['d'] * pow(2, $level-1);
            $price['e'] = $initial[$selected_obj]['e'] * pow(2, $level-1);
            break;
    }
    switch ($selected_obj)
    {
        case 1:   // Шахта металла
            $totalprice['m'] += floor (60 * pow(1.5, $i-1));
            $totalprice['k'] += floor (15 * pow(1.5, $i-1));
            $totalprice['d'] = $totalprice['e'] = 0;
            break;
        case 2:   // Шахта кристалла
            $totalprice['m'] += floor (48 * pow(1.6, $i-1));
            $totalprice['k'] += floor (24 * pow(1.6, $i-1));
            $totalprice['d'] = $totalprice['e'] = 0;
            break;
        case 3:   // Шахта дейта
            $totalprice['m'] += floor (225 * pow(1.5, $i-1));
            $totalprice['k'] += floor (75 * pow(1.5, $i-1));
            $totalprice['d'] = $totalprice['e'] = 0;
            break;
        case 4:   // СЭС
            $totalprice['m'] += floor (75 * pow(1.5, $i-1));
            $totalprice['k'] += floor (30 * pow(1.5, $i-1));
            $totalprice['d'] = $totalprice['e'] = 0;
            break;
        case 12:   // Терма
            $totalprice['m'] += floor (900 * pow(1.8, $i-1));
            $totalprice['k'] += floor (360 * pow(1.8, $i-1));
            $totalprice['d'] += floor (180 * pow(1.8, $i-1));
            $totalprice['e'] = 0;
        case 199:   // Грава
            $totalprice['m'] += $initial[$selected_obj]['m'] * pow(3, $i-1);
            $totalprice['k'] += $initial[$selected_obj]['k'] * pow(3, $i-1);
            $totalprice['d'] += $initial[$selected_obj]['d'] * pow(3, $i-1);
            $totalprice['e'] += $initial[$selected_obj]['e'] * pow(3, $i-1);
            break;

        default:
            $totalprice['m'] += $initial[$selected_obj]['m'] * pow(2, $i-1);
            $totalprice['k'] += $initial[$selected_obj]['k'] * pow(2, $i-1);
            $totalprice['d'] += $initial[$selected_obj]['d'] * pow(2, $i-1);
            $totalprice['e'] += $initial[$selected_obj]['e'] * pow(2, $i-1);
            break;
    }
}

if ($selected_obj == 199)   // Грава исследуется за секунду.
{
    $time = 1;
    $totaltime = $time * $level;
}
else if ($selected_obj > 100 && $selected_obj < 199) // Исследования
{
    $hrs = ($price['m'] + $price['k']) / (1000 * (1 + $reslab)) ;
    $totalhrs =  ($totalprice['m'] + $totalprice['k']) / (1000 * (1 + $reslab));
    $time = floor($hrs *60*60);
    $totaltime = floor($totalhrs *60*60);
}
else if ($selected_obj >= 202)     // Флот и оборона
{
    $time = ( ($price['m'] + $price['k']) / (2500 * (1 + $shipyard)) ) * pow (0.5, $nanits) * 60*60;
    $totaltime = ( ($totalprice['m'] + $totalprice['k']) / (2500 * (1 + $shipyard)) ) * pow (0.5, $nanits) * 60*60;
}
else         // Постройки
{
    $time = ( ($price['m'] + $price['k']) / (2500 * (1 + $robots)) ) * pow (0.5, $nanits) * 60*60;
    $totaltime = ( ($totalprice['m'] + $totalprice['k']) / (2500 * (1 + $robots)) ) * pow (0.5, $nanits) * 60*60;
}

$time /= $speed;
$totaltime /= $speed;

?>

<!-- Скрипт для сброса значений по умолчанию. -->
<script language="JavaScript">
function resetCost()
{
   prices.obj.value = 1;
   prices.level.value = 1;
   prices.rf.value = prices.nf.value = 0;
   prices.shp.value = prices.rl.value = 0;
   prices.mt.value = 50;
   prices.energo.value = 0;
   prices.speedfactor.value = 1;
   prices.geologist.checked = 0;
   prices.engineer.checked = 0;
}
</script>

<!-- Описание геолога и инженера. -->
<script language="JavaScript">
function GeologeDesc ()
{
    var html="<center><font size=1 color=white><b>Геолог</font><br><font size=1 color=skyblue>+10% доход от шахты</font></center>";
    ol_fgcolor = "#344566";
    ol_bgcolor = "#344566";
    overlib (html);
}
function EngineerDesc()
{
    var html="<center><font size=1 color=white><b>Инженер</font><br><font size=1 color=skyblue>+10% больше энергии</font></center>";
    ol_fgcolor = "#344566";
    ol_bgcolor = "#344566";
    overlib (html);
}
</script>


<!-- Шаблон формы расчета стоимости. -->

<center><br><br><br><br>
<form name="prices" action="prices.php" method="post">
<table><tr>

<th colspan=4><div  align=left>
<select name="obj">
<optgroup label="Постройки">
<option value=1 <?issel(1);?>>Рудник по добыче металла</option>
<option value=2 <?issel(2);?>>Рудник по добыче кристалла</option>
<option value=3 <?issel(3);?>>Синтезатор дейтерия</option>
<option value=4 <?issel(4);?>>Солнечная электростанция</option>
<option value=12 <?issel(12);?>>Термоядерная электростанция</option>
<option value=14 <?issel(14);?>>Фабрика роботов</option>
<option value=15 <?issel(15);?>>Фабрика нанитов</option>
<option value=21 <?issel(21);?>>Верфь</option>
<option value=22 <?issel(22);?>>Хранилище металла</option>
<option value=23 <?issel(23);?>>Хранилище кристалла</option>
<option value=24 <?issel(24);?>>Ёмкость для дейтерия</option>
<option value=31 <?issel(31);?>>Исследовательская лаборатория</option>
<option value=33 <?issel(33);?>>Терраформер</option>
<option value=34 <?issel(34);?>>Склад альянса</option>
<option value=44 <?issel(44);?>>Ракетная шахта</option>
</optgroup>
<optgroup label="Исследования">
<option value=106 <?issel(106);?>>Шпионаж</option>
<option value=108 <?issel(108);?>>Компьютерная технология</option>
<option value=109 <?issel(109);?>>Оружейная технология</option>
<option value=110 <?issel(110);?>>Щитовая технология</option>
<option value=111 <?issel(111);?>>Броня космических кораблей</option>
<option value=113 <?issel(113);?>>Энергетическая технология</option>
<option value=114 <?issel(114);?>>Гиперпространственная технология</option>
<option value=115 <?issel(115);?>>Реактивный двигатель</option>
<option value=117 <?issel(117);?>>Импульсный двигатель</option>
<option value=118 <?issel(118);?>>Гиперпространственный двигатель</option>
<option value=120 <?issel(120);?>>Лазерная технология</option>
<option value=121 <?issel(121);?>>Ионная технология</option>
<option value=122 <?issel(122);?>>Плазменная технология</option>
<option value=123 <?issel(123);?>>Межгалактическая исследовательская сеть</option>
<option value=124 <?issel(124);?>>Экспедиционная технология</option>
<option value=199 <?issel(199);?>>Гравитационная технология</option>
</optgroup>
<optgroup label="Флот">
<option value=202 <?issel(202);?>>Малый транспорт</option>
<option value=203 <?issel(203);?>>Большой транспорт</option>
<option value=204 <?issel(204);?>>Лёгкий истребитель</option>
<option value=205 <?issel(205);?>>Тяжёлый истребитель</option>
<option value=206 <?issel(206);?>>Крейсер</option>
<option value=207 <?issel(207);?>>Линкор</option>
<option value=208 <?issel(208);?>>Колонизатор</option>
<option value=209 <?issel(209);?>>Переработчик</option>
<option value=210 <?issel(210);?>>Шпионский зонд</option>
<option value=211 <?issel(211);?>>Бомбардировщик</option>
<option value=212 <?issel(212);?>>Солнечный спутник</option>
<option value=213 <?issel(213);?>>Уничтожитель</option>
<option value=214 <?issel(214);?>>Звезда смерти</option>
<option value=215 <?issel(215);?>>Линейный крейсер</option>
</optgroup>
<optgroup label="Оборона">
<option value=401 <?issel(401);?>>Ракетная установка</option>
<option value=402 <?issel(402);?>>Лёгкий лазер</option>
<option value=403 <?issel(403);?>>Тяжёлый лазер</option>
<option value=404 <?issel(404);?>>Пушка Гаусса</option>
<option value=405 <?issel(405);?>>Ионное орудие</option>
<option value=406 <?issel(406);?>>Плазменное орудие</option>
<option value=407 <?issel(407);?>>Малый щитовой купол</option>
<option value=408 <?issel(408);?>>Большой щитовой купол</option>
<option value=502 <?issel(502);?>>Ракета-перехватчик</option>
<option value=503 <?issel(503);?>>Межпланетная ракета</option>
</optgroup>
<optgroup label="Луна">
<option value=41 <?issel(41);?>>Лунная база</option>
<option value=42 <?issel(42);?>>Сенсорная фаланга</option>
<option value=43 <?issel(43);?>>Ворота</option>
</optgroup>
</select></div></th>

<th><input type="text" name="level" value="<?=$level?>" size = 3></th></tr>

<tr><th colspan=4><div  align=left>Фабрика роботов</div></th><th><input type="text" name="rf" value="<?=$robots?>" size = 3></th></tr>
<tr><th colspan=4><div  align=left>Фабрика нанитов</div></th><th><input type="text" name="nf" value="<?=$nanits?>" size = 3></th></tr>
<tr><th colspan=4><div  align=left>Верфь</div></th><th><input type="text" name="shp" value="<?=$shipyard?>" size = 3></th></tr>
<tr><th colspan=4><div  align=left>Исследовательская лаборатория</div></th><th><input type="text" name="rl" value="<?=$reslab?>" size = 3></th></tr>
<tr><th colspan=4><div  align=left>Максимальная температура</div></th><th><input type="text" name="mt" value="<?=$maxtemp?>" size = 3></th></tr>
<tr><th colspan=4><div  align=left>Энергетическая технология</div></th><th><input type="text" name="energo" value="<?=$energo?>" size = 3></th></tr>
<tr><th colspan=4><div  align=left>Ускорение</div></th><th>
<select name="speedfactor">
<?speedlist(10);?></select></th></tr>
<tr><th colspan=4><div align=left>Геолог <img src="../images/geologe_ikon_sm.gif" onmouseover="GeologeDesc()" onmouseout="nd()"></div></th><th><input type="checkbox" name="geologist" <?checkgeologist();?>></th></tr>
<tr><th colspan=4><div align=left>Инженер <img src="../images/ingenieur_ikon_sm.gif" onmouseover="EngineerDesc()" onmouseout="nd()"></div></th><th><input type="checkbox" name="engineer" <?checkengineer();?>></th></tr>

<tr><th colspan=5><input type="submit" value="Посчитать">&nbsp;&nbsp;&nbsp;<input value="Сбросить" type=button onclick="resetCost()"></th></tr>

<!--- Вывести результаты расчета. -->


    <tr><td class='c' colspan=5>Результаты</td></tr>
    <tr><td class='c'></td><td class='c'><img src="../images/metall_sm.gif"> Металл</td><td class='c'><img src="../images/kristall_sm.gif"> Кристалл</td><td class='c'><img src="../images/deuterium_sm.gif"> Дейтерий</td><td class='c'><img src="../images/energie_sm.gif"> Энергия</td></tr>
    <tr><td class='c'>Стоимость</td><th><?=nicenum($price['m'])?></th><th><?=nicenum($price['k'])?></th><th><?=nicenum($price['d'])?></th><th><?=nicenum($price['e'])?></th></tr>

    <tr><td class='c'>Полная стоимость</td><th><?=nicenum($totalprice['m'])?></th><th><?=nicenum($totalprice['k'])?></th><th><?=nicenum($totalprice['d'])?></th><th><?=nicenum($totalprice['e'])?></th></tr>
    <tr><th colspan=4><div  align=left>Время:</div></th><th><?printtime($time);?></th></tr>
    <tr><th colspan=4><div  align=left>Полное время:</div></th><th><?printtime($totaltime);?></th></tr>

<!-- Дополнительная информация -->

<?php
   switch ($selected_obj)
   {
      case 1:
         addinfo ("Производство металла", prod_metal ($level));
         addinfo ("Потребление энергии", -cons_metal ($level));
         break;
      case 2:
         addinfo ("Производство кристалла", prod_crys ($level));
         addinfo ("Потребление энергии", -cons_crys ($level));
         break;
      case 3:
         addinfo ("Производство дейтерия", prod_deut ($level));
         addinfo ("Потребление энергии", -cons_deut ($level));
         break;
      case 4:
         addinfo ("Производство энергии", prod_solar ($level));
         break;
      case 12:
         addinfo ("Потребление дейтерия", -cons_fusion ($level));
         addinfo ("Производство энергии", prod_fusion ($level));
         break;

      // Хранилища.
      case 22:
      case 23:
      case 24:
         addinfo ("Вместимость", store_capacity ($level));
         break;

      // Лунная база.
      case 41:
         addinfo ("Количество полей", $level * 3 + 1);
         break;

      // Фаланга.
      case 42:
         addinfo ("Радиус фаланги", pow ($level, 2) - 1);
         break;

      // Солнечный спутник.
      case 212:
         $prod = prod_sat() * $level;
         if ($engineer) $prod += floor($prod * 0.1);
         addinfo ("Производство энергии", $prod);
      case 202:
      case 203:
      case 204:
      case 205:
      case 206:
      case 207:
      case 208:
      case 209:
      case 210:
      case 211:
      case 213:
      case 214:
      case 215:
         addinfo ("Переработчиков",  recycles_need ($totalprice['m'] * 0.3, $totalprice['k'] * 0.3));
         break;
   }
?>

</tr></table>
</form>
</center>

<!-- Расчет Стоимости. Конец. -->

</BODY>
</HTML>
