<?php

// -----------------------------------------------------------------------------------------------------------------
// Обзор.

// Получить прирост очков за указанный период дней.
function GetDeltaScore ($player_id, $lastscore, $days, &$pscores, $lastdate)
{
    $date_from = $lastdate - $days * 24 * 60 * 60;

    $nearest_date = PHP_INT_MAX;
    $nearest_score = 0;
    foreach ( $pscores[$player_id] as $date => $score ) {
        if ( $date >= $date_from && $date < $nearest_date) {
            $nearest_date = $date;
            $nearest_score = $score;
        }
    }
    if ($nearest_score == 0) return 0;
    return $lastscore - $nearest_score;
}

// Получить прирост флотов за указанный период дней.
function GetDeltaFleet ($player_id, $lastfleet, $days, &$pfleets, $lastdate)
{
    $date_from = $lastdate - $days * 24 * 60 * 60;

    $nearest_date = PHP_INT_MAX;
    $nearest_score = 0;
    if ($pfleets[$player_id] == NULL) return 0;
    foreach ( $pfleets[$player_id] as $date => $score ) {
        if ( $date >= $date_from && $date < $nearest_date ) {
            $nearest_date = $date;
            $nearest_score = $score;
        }
    }
    if ($nearest_score == 0) return 0;    
    return $lastfleet - $nearest_score;
}

// Проверить выборку на перепады в течении указанного периода дней.
function GetDeviationScore ($player_id, $lastscore, $days, &$pscores, $lastdate)
{
    $date_from = $lastdate - $days * 24 * 60 * 60;

    foreach ( $pscores[$player_id] as $date => $score ) {
    	if ( $date >= $date_from) {
        	if ( $score != $lastscore) return TRUE;
    	}
    }
    return FALSE;
}

// Получить прирост очков за указанный период дней для альянса.
function GetAllyDeltaScore ($ally_id, $lastscore, $days, &$ascores, $lastdate)
{
    $date_from = $lastdate - $days * 24 * 60 * 60;

    $nearest_date = PHP_INT_MAX;
    $nearest_score = 0;
    if ($ascores[$ally_id] == NULL) return 0;
    foreach ( $ascores[$ally_id] as $date => $score ) {
        if ( $date >= $date_from && $date < $nearest_date) {
            $nearest_date = $date;
            $nearest_score = $score;
        }
    }
    if ($nearest_score == 0) return 0;
    return $lastscore - $nearest_score;
}

// Выбор типа сортировки для вывода списка игроков.
function SelectSortMethod ($sort)
{
    $selected = array ();
    $selected[0] = $selected[1] = $selected[2] = "";
    $selected[$sort] = "SELECTED";
    $res = "";
    $res .= "<span id=\"sortby\" style=\"float: right;\">Сортировать по: <select onchange=\"OnOverviewSelect('".hostname().scriptname()."', '".$_GET['sig']."');\">";
    $res .= "<option value=\"0\" ".$selected[0].">Имя игрока</option>\n";
    $res .= "<option value=\"1\" ".$selected[1].">Рейтинг</option>\n";
    $res .= "<option value=\"2\" ".$selected[2].">Прирост/спад</option>\n";
    $res .= "</select></span>";
    return $res;
}

// Выбор количества дней для топ роста/спада.
function SelectTopDays ($topdays)
{
    $selected = array ();
    $selected[1] = $selected[2] = $selected[3] = $selected[5] = $selected[7] = $selected[14] = $selected[30] = "";
    $selected[$topdays] = "SELECTED";
    $res = "";
    $res .= "<span id=\"topdays\" style=\"float: right;\" >Топ рост/спад за: <select onchange=\"OnOverviewSelect('".hostname().scriptname()."', '".$_GET['sig']."');\">";
    $res .= "<option value=\"1\" ".$selected[1].">24 часа</option>";
    $res .= "<option value=\"2\" ".$selected[2].">2 дня</option>";
    $res .= "<option value=\"3\" ".$selected[3].">3 дня</option>";
    $res .= "<option value=\"5\" ".$selected[5].">5 дней</option>";
    $res .= "<option value=\"7\" ".$selected[7].">7 дней</option>";
    $res .= "<option value=\"14\" ".$selected[14].">14 дней</option>";
    $res .= "<option value=\"30\" ".$selected[30].">30 дней</option>";
    if ( $topdays != 1 && $topdays != 2 && $topdays != 3 && $topdays != 5 && $topdays != 7 && $topdays != 14 && $topdays != 30 )
        $res .= "<option value=\"$topdays\" SELECTED>$topdays дн.</option>";
    $res .= "</select></span>";
    return $res;
}

function AllyTop ($acc, $topdays)
{
    $alastdate = array ();
    $anames = array ();
    $ascores = array ();

    // Получить статистику за месяц для альянсов и рассортировать по ассоциативным массивам.
    $result = LoadAllyMonthStat ($acc);
    $rows = dbrows ($result);
    while ($rows--) {
        $row = dbarray ($result);
        if ($row['type'] == 1) {
            if ( ($row['date'] > $alastdate[$row['ally_id']]) || !isset ($alastdate[$row['ally_id']]) ) {
                $alastdate[$row['ally_id']] = $row['date'];
                $anames[$row['name']] = $row['ally_id'];
            }
            $ascores[$row['ally_id']][$row['date']] = $row['score'];
        }
    }
    dbfree ($result);

    ksort (&$anames);

    // Обработать данные
    $atopgrow = array ();

    foreach ( $anames as $name => $id ) {
        $lastdate = $alastdate[$id];
        $lastscore = $ascores[$id][$lastdate];
        $delta = GetAllyDeltaScore ($id, $lastscore, $topdays, &$ascores, $lastdate);
        $atopgrow[$delta] = "<a href=\"".scriptname()."?page=astat&ally_id=$id&sig=".$_GET['sig']."\">" . $name . "</a>";
    }

    krsort ( &$atopgrow );

    $i = 0;
    foreach ( $atopgrow as $grow => $name) {
        if ( $grow <= 0 ) echo "    <tr><td>".($i+1)."</td><td>-</td></tr>";
        else echo "    <tr><td>".($i+1)."</td><td><a>$name</a></td><td>+".nn($grow)."</td></tr>\n";
        $i++;
        if ($i >= 10) break;
    }
    for ($i; $i<10; $i++) echo "    <tr><td>".($i+1)."</td><td>-</td></tr>";
}

// Анализ статистики игроков по очкам.
function ScoreTopGrowFall ( $acc, &$topgrow, &$topfall,
        &$growPlayers, &$growPlayersCount,
        &$fallPlayers, &$fallPlayersCount,
        &$deviPlayers, &$deviPlayersCount,
        &$noobPlayers, &$noobPlayersCount,
        &$inactivePlayers1, &$inactivePlayers1Count,
        &$inactivePlayers3, &$inactivePlayers3Count,
        &$inactivePlayers5, &$inactivePlayers5Count,
        &$inactivePlayers7, &$inactivePlayers7Count,
        &$inactivePlayers28, &$inactivePlayers28Count,
        $sortby, $topdays )
{
    global $timeshift;

    $baseage = max ( 0, floor ( ( time () + ($timeshift * 60 * 60) - $acc['firsthit'] ) / (24 * 60 * 60) ) );
    $lastupdate = GetLastUpdate ($acc);
    $hours24 = $lastupdate - 24 * 60 * 60;

    $plastdate = array ();
    $pnames = array ();
    $pscores = array ();
    $pstatus = array ();
    $ids = array ();

    // Получить статистику за месяц и рассортировать по ассоциативным массивам.
    $result = LoadMonthStat ($acc);
    $rows = dbrows ($result);
    while ($rows--) {
        $row = dbarray ($result);
        if ($row == false) break;
        if ($row['type'] == 1) {
            if ( ($row['date'] > $plastdate[$row['player_id']]) || !isset ($plastdate[$row['player_id']]) ) {
                $plastdate[$row['player_id']] = $row['date'];
                $pnames[$row['name']] = $row['player_id'];
                if ( $ids[$row['player_id']] !== $row['name'] && isset ($ids[$row['player_id']]) ) $pnames[$ids[$row['player_id']]] = 0;    // Вырезать старое имя.
                $ids[$row['player_id']] = $row['name'];
            }
            $pscores[$row['player_id']][$row['date']] = $row['score'];
            $pstatus[$row['player_id']][$row['date']] = $row['status'];
        }
    }
    dbfree ($result);

    ksort (&$pnames);
    unset ( $ids );

    // Обработать данные.

    foreach ( $pnames as $name => $id ) {
        if ( $id == 0 ) continue;    // Пропустить вырезанные повторы имён.
        $lastdate = $plastdate[$id];
        $lastscore = $pscores[$id][$lastdate];
        $laststatus = $pstatus[$id][$lastdate];

        if ( $lastscore < 5000 ) {    // Новички и забанненные.
            $noobPlayers[$noobPlayersCount]['id'] = $id;
            $noobPlayers[$noobPlayersCount]['status'] = $laststatus;
            $noobPlayers[$noobPlayersCount++]['name'] = $name;
            continue;
        }

        $delta = GetDeltaScore ($id, $lastscore, $topdays, &$pscores, $lastdate);
        $topgrow[$delta] = $topfall[$delta] = "<a href=\"".scriptname()."?page=pstat&player_id=$id&sig=".$_GET['sig']."\">" . $name . "</a>";
        if ( $delta > 0) {        // Прирост за 24 часа.
            $growPlayers[$growPlayersCount]['id'] = $id;
            $growPlayers[$growPlayersCount]['status'] = $laststatus;
            $growPlayers[$growPlayersCount]['name'] = $name;
            $growPlayers[$growPlayersCount]['score'] = $lastscore;
            $growPlayers[$growPlayersCount++]['delta'] = $delta;
            continue;
        }
        if ( $delta < 0) {        // Спад за 24 часа.
            $fallPlayers[$fallPlayersCount]['id'] = $id;
            $fallPlayers[$fallPlayersCount]['status'] = $laststatus;
            $fallPlayers[$fallPlayersCount]['name'] = $name;
            $fallPlayers[$fallPlayersCount]['score'] = $lastscore;
            $fallPlayers[$fallPlayersCount++]['delta'] = $delta;
            continue;
        }

        $devi = GetDeviationScore ($id, $lastscore, 1, &$pscores, $lastdate);    // Перепады за 24 часа.
        if ($devi) { 
            $deviPlayers[$deviPlayersCount]['id'] = $id;
            $deviPlayers[$deviPlayersCount]['status'] = $laststatus;
            $deviPlayers[$deviPlayersCount]['score'] = $lastscore;
            $deviPlayers[$deviPlayersCount++]['name'] = $name;
            continue;
        }

        if ($baseage >= 28) {
        	$delta = GetDeltaScore ($id, $lastscore, 28, &$pscores, $lastdate);
        	if ( $delta == 0) {        // Неактивные 28 дней.
            	$inactivePlayers28[$inactivePlayers28Count]['id'] = $id;
                $inactivePlayers28[$inactivePlayers28Count]['status'] = $laststatus;
                $inactivePlayers28[$inactivePlayers28Count]['score'] = $lastscore;
            	$inactivePlayers28[$inactivePlayers28Count++]['name'] = $name;
            	continue;
        	}
        }
        if ($baseage >= 7) {
			$delta = GetDeltaScore ($id, $lastscore, 7, &$pscores, $lastdate);
        	if ( $delta == 0) {        // Неактивные 7 дней.
            	$inactivePlayers7[$inactivePlayers7Count]['id'] = $id;
                $inactivePlayers7[$inactivePlayers7Count]['status'] = $laststatus;
                $inactivePlayers7[$inactivePlayers7Count]['score'] = $lastscore;
            	$inactivePlayers7[$inactivePlayers7Count++]['name'] = $name;
            	continue;
        	}
        }
        if ($baseage >= 5) {
        	$delta = GetDeltaScore ($id, $lastscore, 5, &$pscores, $lastdate);
        	if ( $delta == 0) {        // Неактивные 5 дней.
            	$inactivePlayers5[$inactivePlayers5Count]['id'] = $id;
                $inactivePlayers5[$inactivePlayers5Count]['status'] = $laststatus;
                $inactivePlayers5[$inactivePlayers5Count]['score'] = $lastscore;
            	$inactivePlayers5[$inactivePlayers5Count++]['name'] = $name;
            	continue;
        	}        
        }
        if ($baseage >= 3) {
        	$delta = GetDeltaScore ($id, $lastscore, 3, &$pscores, $lastdate);
        	if ( $delta == 0) {        // Неактивные 3 дня.
            	$inactivePlayers3[$inactivePlayers3Count]['id'] = $id;
                $inactivePlayers3[$inactivePlayers3Count]['status'] = $laststatus;
                $inactivePlayers3[$inactivePlayers3Count]['score'] = $lastscore;
            	$inactivePlayers3[$inactivePlayers3Count++]['name'] = $name;
            	continue;
        	}
        }
        if ($baseage >= 1) {
        	$delta = GetDeltaScore ($id, $lastscore, 1, &$pscores, $lastdate);
        	if ( $delta == 0) {        // Неактивные 1 день.
            	$inactivePlayers1[$inactivePlayers1Count]['id'] = $id;
                $inactivePlayers1[$inactivePlayers1Count]['status'] = $laststatus;
                $inactivePlayers1[$inactivePlayers1Count]['score'] = $lastscore;
            	$inactivePlayers1[$inactivePlayers1Count++]['name'] = $name;
            	continue;
        	}        
        }

    }

    unset ($plastdate);
    unset ($pnames);
    unset ($pscores);
    unset ($pstatus);

    krsort ( &$topgrow );
    ksort ( &$topfall );
}

// Анализ статистики игроков по флотам.
function FleetTopGrowFall ( $acc, &$topgrowf, &$topfallf, $sortby, $topdays )
{
    $plastdate = array ();
    $pnames = array ();
    $plastfleet = array ();
    $pfleets = array ();

    // Получить статистику за месяц и рассортировать по ассоциативным массивам.
    $result = LoadMonthStat ($acc);
    $rows = dbrows ($result);
    while ($rows--) {
        $row = dbarray ($result);
        if ($row == false) break;
        if ($row['type'] == 1) {
            if ( ($row['date'] > $plastdate[$row['player_id']]) || !isset ($plastdate[$row['player_id']]) ) {
                $plastdate[$row['player_id']] = $row['date'];
                $pnames[$row['name']] = $row['player_id'];
            }
        }
        if ($row['type'] == 2) {
            if ( ($row['date'] > $plastfleet[$row['player_id']]) || !isset ($plastfleet[$row['player_id']]) ) {
                $plastfleet[$row['player_id']] = $row['date'];
            }
            $pfleets[$row['player_id']][$row['date']] = $row['score'];
        }
    }
    dbfree ($result);

    ksort (&$pnames);

    // Обработать данные.

    foreach ( $pnames as $name => $id ) {
        $lastfleet = $pfleets[$id][$plastfleet[$id]];

        $delta = GetDeltaFleet ($id, $lastfleet, $topdays, &$pfleets, $plastfleet[$id]);
        $topgrowf[$delta] = $topfallf[$delta] = "<a href=\"".scriptname()."?page=pstat&player_id=$id&sig=".$_GET['sig']."\">" . $name . "</a>";
    }

    unset ($plastdate);
    unset ($pnames);
    unset ($plastfleet);
    unset ($pfleets);

    krsort ( &$topgrowf );
    ksort ( &$topfallf );
}

function CompareByName ($a, $b) { return strcmp($a['name'], $b['name']); }
function CompareByScore ($a, $b) { return $a['score'] < $b['score']; }
function CompareByDelta ($a, $b) { return abs($a['delta']) < abs($b['delta']); }

function PageStatsBox ($acc)
{
    global $timeshift;

    if ( $acc['firsthit'] == 0 ) {
        echo "<table style=\"width: 100%\">\n";
        echo "<tr><td><font color=\"red\"><b>Аккаунту ещё не назначена Вселенная.</b></font></td></tr>\n";
        echo "<tr><td>Вселенная будет назначена автоматически, после первого обновления базы.</td></tr>\n";
        echo "<tr><td><span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 50px 0;\"></span> Будьте внимательны, после первого обновлении базы переназначить вселенную невозможно.</td></tr>\n";
        echo "</table>\n";
        return;
    }
    
    $baseage = max ( 0, floor ( ( time () + ($timeshift * 60 * 60) - $acc['firsthit'] ) / (24 * 60 * 60) ) );
    $lastupdate = GetLastUpdate ($acc);

    // Получить количество дней для статистики топ роста/спада.
    if ( key_exists ( "topdays", $_GET) ) {
        $topdays = $_GET['topdays'];
        if ( $topdays <= 0 ) $topdays = 1;
        //if ( $topdays > 30 ) $topdays = 30;
    }
    else $topdays = 1;

    // Получить метод сортировки списка игроков.
    if ( key_exists ( "sort", $_GET) ) {
        $sort = $_GET['sort'];
        if ( $sort < 0 ) $sort = 0;
        if ( $sort > 2 ) $sort = 2;
    }
    else $sort = 1;

    $topgrow = array ();
    $topfall = array ();
    $topgrowf = array ();
    $topfallf = array ();

    $growPlayers = array ();         $growPlayersCount = 0;
    $fallPlayers = array ();         $fallPlayersCount = 0;
    $deviPlayers = array ();         $deviPlayersCount = 0;
    $noobPlayers = array ();         $noobPlayersCount = 0;
    $inactivePlayers1 = array ();    $inactivePlayers1Count = 0;
    $inactivePlayers3 = array ();    $inactivePlayers3Count = 0;
    $inactivePlayers5 = array ();    $inactivePlayers5Count = 0;
    $inactivePlayers7 = array ();    $inactivePlayers7Count = 0;
    $inactivePlayers28 = array ();   $inactivePlayers28Count = 0;

    ScoreTopGrowFall ( $acc, &$topgrow, &$topfall,
        &$growPlayers, &$growPlayersCount,
        &$fallPlayers, &$fallPlayersCount,
        &$deviPlayers, &$deviPlayersCount,
        &$noobPlayers, &$noobPlayersCount,
        &$inactivePlayers1, &$inactivePlayers1Count,
        &$inactivePlayers3, &$inactivePlayers3Count,
        &$inactivePlayers5, &$inactivePlayers5Count,
        &$inactivePlayers7, &$inactivePlayers7Count,
        &$inactivePlayers28, &$inactivePlayers28Count,
        $sort, $topdays
    );
    FleetTopGrowFall ( $acc, &$topgrowf, &$topfallf, $sort, $topdays );

    echo "<table style=\"width: 100%\">\n";
    echo "<tr><td>Статистика ведется с: ".date("d.m.Y", $acc['firsthit'])." ($baseage дн.)" . SelectTopDays($topdays) . SelectSortMethod($sort) . "</td></tr>\n";
    $diff = time () + ($timeshift * 60 * 60) - $lastupdate;
    $hours = floor ( $diff / (60 * 60) );
    $hoursMod = $hours % 24;
    $days = floor ( $diff / (24 * 60 * 60) );
    if ($days > 0) {
        if ($hoursMod) $laststr = " ($days дн., $hoursMod ч. назад)";
        else $laststr = " ($days дн. назад)";
    }
    else { 
        if ($hours > 0) $laststr = " ($hours ч. назад)";
        else $laststr = " (только что)";
    }
    if ($lastupdate) echo "<tr><td>Последнее обновление: ".date("d.m.Y H:i", $lastupdate)." $laststr</td></tr>\n";
    echo "<tr><td>Всего игроков в базе: ".TotalPlayers($acc)."</td></tr>\n";

    // Топ рост/спад.
    echo "<tr><td><table style=\"width: 100%\">\n";

    echo "    <tr>\n";
    echo "    <td>\n";
    echo "        <table>\n";
    echo "        <tr><td colspan=\"3\"><span class=\"ui-icon ui-icon-help top-helper\" style=\"float:left;\"></span> Топ рост по очкам</td></tr>\n";
    $i = 0;
    foreach ( $topgrow as $grow => $name) {
        if ( $grow <= 0 ) echo "    <tr><td>".($i+1)."</td><td>-</td></tr>";
        else echo "    <tr><td>".($i+1)."</td><td><a>$name</a></td><td>+".nn($grow)."</td></tr>\n";
        $i++;
        if ($i >= 10) break;
    }
	for ($i; $i<10; $i++) echo "    <tr><td>".($i+1)."</td><td>-</td></tr>";    
    echo "        </table>\n";
    echo "    </td>\n";
    echo "    <td>\n";
    echo "        <table>\n";
    echo "        <tr><td colspan=\"3\"><span class=\"ui-icon ui-icon-help top-helper\" style=\"float:left;\"></span> Топ рост по флотам</td></tr>\n";
    $i = 0;
    foreach ( $topgrowf as $grow => $name) {
        if ( $grow <= 0 ) echo "    <tr><td>".($i+1)."</td><td>-</td></tr>";
        else echo "    <tr><td>".($i+1)."</td><td><a>$name</a></td><td>+".nn($grow)."</td></tr>\n";
        $i++;
        if ($i >= 10) break;
    }
    for ($i; $i<10; $i++) echo "    <tr><td>".($i+1)."</td><td>-</td></tr>";
    echo "        </table>\n";
    echo "    </td>\n";
    echo "    <td>\n";
    echo "        <table>\n";
    echo "        <tr><td colspan=\"3\"><span class=\"ui-icon ui-icon-help top-helper\" style=\"float:left;\"></span> Топ спад по очкам</td></tr>\n";
    $i = 0;
    foreach ( $topfall as $fall => $name) {
        if ( $fall >= 0 ) echo "    <tr><td>".($i+1)."</td><td>-</td></tr>";
        else echo "    <tr><td>".($i+1)."</td><td><a>$name</a></td><td>".nn($fall)."</td></tr>\n";
        $i++;
        if ($i >= 10) break;
    }
    for ($i; $i<10; $i++) echo "    <tr><td>".($i+1)."</td><td>-</td></tr>";
    echo "        </table>\n";
    echo "    </td>\n";
    echo "    <td>\n";
    echo "        <table>\n";
    echo "        <tr><td colspan=\"3\"><span class=\"ui-icon ui-icon-help top-helper\" style=\"float:left;\"></span> Топ спад по флотам</td></tr>\n";
    $i = 0;
    foreach ( $topfallf as $fall => $name) {
        if ( $fall >= 0 ) echo "    <tr><td>".($i+1)."</td><td>-</td></tr>";
        else echo "    <tr><td>".($i+1)."</td><td><a>$name</a></td><td>".nn($fall)."</td></tr>\n";
        $i++;
        if ($i >= 10) break;
    }
	for ($i; $i<10; $i++) echo "    <tr><td>".($i+1)."</td><td>-</td></tr>";
    echo "        </table>\n";
    echo "    </td>\n";
    echo "    <td>\n";
    echo "        <table>\n";
    echo "        <tr><td colspan=\"3\"><span class=\"ui-icon ui-icon-help top-helper\" style=\"float:left;\"></span> Топ рост альянсов</td></tr>\n";
    AllyTop ( $acc, $topdays );
    echo "        </table>\n";
    echo "    </td>\n";
    echo "    </tr>\n";

    echo "    </table></td></tr>\n";

    // Полотно со списками активных и неактивных игроков.

    if ( $topdays <= 1) $period  = "24 часа";
    else $period = "$topdays дн.";

    if ($growPlayersCount) {
        if ($sort == 1) usort ( $growPlayers, "CompareByScore" );
        else if ($sort == 2) usort ( $growPlayers, "CompareByDelta" );
    
        echo "<tr><td>Прирост за последние $period ($growPlayersCount)</td></tr>\n";
        echo "<tr><td>";
        for ($i=0; $i<$growPlayersCount; $i++) {
            if ($i) echo ", ";
            echo "<a href=\"".scriptname()."?page=pstat&player_id=".$growPlayers[$i]['id']."&sig=".$_GET['sig']."\" title=\"+".nn($growPlayers[$i]['delta'])."\">" . $growPlayers[$i]['name'] . ColorStatusString($growPlayers[$i]['status'])."</a>";
			if ( abs($growPlayers[$i]['delta']) > 100000 ) echo " <font color=\"lime\">(+".nn($growPlayers[$i]['delta']).")</font>";
        }
        echo "</td></tr>";
    }

    if ($fallPlayersCount) {
        if ($sort == 1) usort ( $fallPlayers, "CompareByScore" );
        else if ($sort == 2) usort ( $fallPlayers, "CompareByDelta" );

        echo "<tr><td>Спад за последние $period ($fallPlayersCount)</td></tr>\n";
        echo "<tr><td>";
        for ($i=0; $i<$fallPlayersCount; $i++) {
            if ($i) echo ", ";
            echo "<a href=\"".scriptname()."?page=pstat&player_id=".$fallPlayers[$i]['id']."&sig=".$_GET['sig']."\" title=\"".nn($fallPlayers[$i]['delta'])."\">" . $fallPlayers[$i]['name'] . ColorStatusString($fallPlayers[$i]['status']). "</a>";
        	if ( abs($fallPlayers[$i]['delta']) > 100000 ) echo " <font color=\"red\">(".nn($fallPlayers[$i]['delta']).")</font>";
        }
        echo "</td></tr>";
    }

    if ($deviPlayersCount) {
        if ($sort == 1) usort ( $deviPlayers, "CompareByScore" );
        else if ($sort == 2) usort ( $deviPlayers, "CompareByDelta" );

        echo "<tr><td>Перепад за последние 24 часа ($deviPlayersCount)</td></tr>\n";
        echo "<tr><td>";
        for ($i=0; $i<$deviPlayersCount; $i++) {
            if ($i) echo ", ";
            echo "<a href=\"".scriptname()."?page=pstat&player_id=".$deviPlayers[$i]['id']."&sig=".$_GET['sig']."\" title=\"".nn($deviPlayers[$i]['delta'])."\">" . $deviPlayers[$i]['name'] . ColorStatusString($deviPlayers[$i]['status']). "</a>";
        }
        echo "</td></tr>";
    }

    if ($inactivePlayers1Count) {
        if ($sort == 1) usort ( $inactivePlayers1, "CompareByScore" );

        echo "<tr><td>Неактивные 1 день ($inactivePlayers1Count)</td></tr>\n";
        echo "<tr><td>";
        for ($i=0; $i<$inactivePlayers1Count; $i++) {
            if ($i) echo ", ";
            echo "<a href=\"".scriptname()."?page=pstat&player_id=".$inactivePlayers1[$i]['id']."&sig=".$_GET['sig']."\">" . $inactivePlayers1[$i]['name'] . ColorStatusString($inactivePlayers1[$i]['status']). "</a>";
        }
        echo "</td></tr>";
    }
    if ($inactivePlayers3Count) {
        if ($sort == 1) usort ( $inactivePlayers3, "CompareByScore" );

        echo "<tr><td>Неактивные 3 дня ($inactivePlayers3Count)</td></tr>\n";
        echo "<tr><td>";
        for ($i=0; $i<$inactivePlayers3Count; $i++) {
            if ($i) echo ", ";
            echo "<a href=\"".scriptname()."?page=pstat&player_id=".$inactivePlayers3[$i]['id']."&sig=".$_GET['sig']."\">" . $inactivePlayers3[$i]['name'] . ColorStatusString($inactivePlayers3[$i]['status']). "</a>";
        }
        echo "</td></tr>";
    }
    if ($inactivePlayers5Count) {
        if ($sort == 1) usort ( $inactivePlayers5, "CompareByScore" );

        echo "<tr><td>Неактивные 5 дней ($inactivePlayers5Count)</td></tr>\n";
        echo "<tr><td>";
        for ($i=0; $i<$inactivePlayers5Count; $i++) {
            if ($i) echo ", ";
            echo "<a href=\"".scriptname()."?page=pstat&player_id=".$inactivePlayers5[$i]['id']."&sig=".$_GET['sig']."\">" . $inactivePlayers5[$i]['name'] . ColorStatusString($inactivePlayers5[$i]['status']). "</a>";
        }
        echo "</td></tr>";
    }
    if ($inactivePlayers7Count) {
        if ($sort == 1) usort ( $inactivePlayers7, "CompareByScore" );

        echo "<tr><td>Неактивные 7 дней ($inactivePlayers7Count)</td></tr>\n";
        echo "<tr><td>";
        for ($i=0; $i<$inactivePlayers7Count; $i++) {
            if ($i) echo ", ";
            echo "<a href=\"".scriptname()."?page=pstat&player_id=".$inactivePlayers7[$i]['id']."&sig=".$_GET['sig']."\">" . $inactivePlayers7[$i]['name'] . ColorStatusString($inactivePlayers7[$i]['status']). "</a>";
        }
        echo "</td></tr>";
    }
    if ($inactivePlayers28Count) {
        if ($sort == 1) usort ( $inactivePlayers28, "CompareByScore" );

        echo "<tr><td>Неактивные более 28 дней ($inactivePlayers28Count)</td></tr>\n";
        echo "<tr><td>";
        for ($i=0; $i<$inactivePlayers28Count; $i++) {
            if ($i) echo ", ";
            echo "<a href=\"".scriptname()."?page=pstat&player_id=".$inactivePlayers28[$i]['id']."&sig=".$_GET['sig']."\">" . $inactivePlayers28[$i]['name'] . ColorStatusString($inactivePlayers28[$i]['status']). "</a>";
        }
        echo "</td></tr>";
    }

    if ($noobPlayersCount) {
        if ($sort == 1) usort ( $noobPlayers, "CompareByScore" );

        echo "<tr><td>Нубы и забаненные ($noobPlayersCount)</td></tr>\n";
        echo "<tr><td>";
        for ($i=0; $i<$noobPlayersCount; $i++) {
            if ($i) echo ", ";
            echo "<a href=\"".scriptname()."?page=pstat&player_id=".$noobPlayers[$i]['id']."&sig=".$_GET['sig']."\">" . $noobPlayers[$i]['name'] . ColorStatusString($noobPlayers[$i]['status']). "</a>";
        }
        echo "</td></tr>";
    }

    echo "</table>\n";
}

function PageShoutBox ($acc)
{
    $page = 1;
    if ( key_exists ("page", $_GET) ) $page = $_GET['page'];

    echo "<table style=\"width: 100%\">\n";

    // Вывод сообщений.
    $query = EnumMessages ($acc['acc_id'], $page);
    $count = dbrows ($query);
    while ($count--) { 
        $msg = dbarray ($query);
        echo MessageHTML ($msg);
    }

    // Вывод количества страниц.
    echo "<tr><td>1</td></tr>\n";

    // Вывод поля для отправки сообщений.
    echo "<tr><td><form id=\"shoutbox\" action=\"".scriptname()."?page=shout&sig=".$acc['sig']."\" method=\"POST\">";
    echo "<textarea id=\"shoutboxEdit\" cols=\"2\" rows=\"12\" name=\"text\" style=\"width: 100%; border: 0px !important;\"></textarea></form></td></tr>\n";

    echo "</table>";    
}

function PageOverview ()
{
    if ( IPBanned () ) PageHome (10001);
    $acc = LoadAccountBySig ( $_GET['sig'] );
    if ($acc == null) PageHome (10002);
    if ( key_exists ("lgn", $_GET) ) UpdateIPLoginTime ( $acc['acc_id'] );

	WipeGarbage ();

    PageHeader ();
    PageMenu ($acc);
    PageSignature ($acc);
    PageUniverse ($acc);

    echo "<div id=\"overview_content\" class=\"ui-widget-content\">\n";
        PageStatsBox ($acc);
        PageShoutBox ($acc);
    echo "</div>\n";

    PageFooter ($acc);
}

?>