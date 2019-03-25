<?php

// -----------------------------------------------------------------------------------------------------------------
// Информация по игроку.

function ResearchPrice ( $id, $lvl, &$m, &$k, &$d, &$e )
{
    // Стоимость первого уровня исследований.
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
    //$initial[124]['m'] = 4000; $initial[124]['k'] = 8000; $initial[124]['d'] = 4000; $initial[124]['e'] = 0;
    $initial[199]['m'] = 0; $initial[199]['k'] = 0; $initial[199]['d'] = 0; $initial[199]['e'] = 300000;

    if ($id == 199) {
        $m = $k = $d = 0;
        $e = $initial[$id]['e'] * pow(3, $lvl-1);
    }
    else if ($id == 124 ) {    // Астрофизика
        $m = 100 * floor ( 0.5 + 40 * pow (1.75, $lvl-1) );
        $k = 100 * floor ( 0.5 + 80 * pow (1.75, $lvl-1) );
        $d = 100 * floor ( 0.5 + 40 * pow (1.75, $lvl-1) );
        $e = 0;
    }
    else {
        $m = $initial[$id]['m'] * pow(2, $lvl-1);
        $k = $initial[$id]['k'] * pow(2, $lvl-1);
        $d = $initial[$id]['d'] * pow(2, $lvl-1);
        $e = $initial[$id]['e'] * pow(2, $lvl-1);
    }
}

function GetResearchNameByPoints ($points)
{
    $LOCA["NAME_106"] = "Шпионаж";
    $LOCA["NAME_108"] = "Компьютерная технология";
    $LOCA["NAME_109"] = "Оружейная технология";
    $LOCA["NAME_110"] = "Щитовая технология";
    $LOCA["NAME_111"] = "Броня космических кораблей";
    $LOCA["NAME_113"] = "Энергетическая технология";
    $LOCA["NAME_114"] = "Гиперпространственная технология";
    $LOCA["NAME_115"] = "Реактивный двигатель";
    $LOCA["NAME_117"] = "Импульсный двигатель";
    $LOCA["NAME_118"] = "Гиперпространственный двигатель";
    $LOCA["NAME_120"] = "Лазерная технология";
    $LOCA["NAME_121"] = "Ионная технология";
    $LOCA["NAME_122"] = "Плазменная технология";
    $LOCA["NAME_123"] = "Межгалактическая исследовательская сеть";
    $LOCA["NAME_124"] = "Астрофизика";
    $LOCA["NAME_199"] = "Гравитационная технология";

    $resmap = array ( 106, 108, 109, 110, 111, 113, 114, 115, 117, 118, 120, 121, 122, 123, 124, 199 );

    if ( $points < 100 ) return "Низкий уровень";

    foreach ($resmap as $i=>$gid) {
        for ($lvl=1; $lvl<20; $lvl++) {
            $m = $k = $d = $e = 0;
            ResearchPrice ( $gid, $lvl, &$m, &$k, &$d, &$e );
            $pts = ($m + $k + $d) / 1000;
            if ( $pts >= ($points-3) && $pts <= ($points+3) ) return $LOCA["NAME_".$gid] . " " . $lvl;
        }
    }
    return "Неизвестно";
}

function PagePlayerInfoBox ($acc)
{
    $player_id = intval($_GET['player_id']);
    $last = LastStat ($acc, $player_id);
    $name = $last['name'] ;//. ColorStatusString(GetLastStatus($player_id, $acc['acc_id']));
    $ally_stat = LastAllyStat ($acc, $last['ally_id']);
    if ($last['ally_id'] == 0) $ally = "";
    else if ($last['ally_id'] == -1) $ally = "свои";
    else $ally = "<a href=\"".scriptname()."?page=astat&ally_id=".$last['ally_id']."&sig=".$_GET['sig']."\">".$ally_stat['name']."</a>";
    $coord_g = $last['g']; $coord_s = $last['s']; $coord_p = $last['p']; 
    $score = nn($last['score']);
    $score_place = nn($last['place']);
    $score_date = date ( "d.m.Y H:i", $last['date']);
    $last = LastFleet ($acc, $player_id);
    $fleet = nn($last['score']);
    $fleet_place = nn($last['place']);
    $fleet_date = date ( "d.m.Y H:i", $last['date']);
    $last = LastResearch ($acc, $player_id);
    $research = nn($last['score']);
    $research_place = nn($last['place']);
    $research_date = date ( "d.m.Y H:i", $last['date']);

    // История имён и альянсов.
    $name_history = $ally_history = "";
    $lastname = NULL;
    $lastally = 0;
    $result = LoadStat ($acc, $player_id);
    $rows = dbrows ( $result );
    while ( $rows--) { 
        $row = dbarray ( $result );
        $date = $row['date'];
        if ($lastname !== $row['name'] || $lastname == NULL) {
            if ($lastname != NULL) $name_history .= ", ";
            $lastname = $row['name'];
            $name_history .= $lastname . " (с " . date ("d.m.Y", $date) . ")";
        }
        if ( ($lastally !== $row['ally_id'] || $lastally == 0) && $row['ally_id'] ) {
            if ($lastally != 0) $ally_history .= ", ";
            $lastally = $row['ally_id'];
            $allystat = LastAllyStat ($acc, $lastally);
            $ally_history .= "<a href=\"".scriptname()."?page=astat&ally_id=$lastally&sig=".$_GET['sig']."\">" . $allystat['name'] . "</a>" . " (с " . date ("d.m.Y", $date) . ")";
        }
    }
    if ($lastally == 0) $ally_history = "нет";

    echo "<table style=\"width: 100%\">\n";
    echo "<tr><td valign=top>";

    echo "<table>\n";
    echo "<tr><td width=\"20%\">Игрок:</td><td width=\"80%\">$name $ally</td></tr>\n";
    echo "<tr><td>Очки:</td><td>$score (место $score_place, на $score_date)</td></tr>\n";
    echo "<tr><td>Флот:</td><td>$fleet (место $fleet_place, на $fleet_date)</td></tr>\n";
    echo "<tr><td>Исследования:</td><td>$research (место $research_place, на $research_date)</td></tr>\n";
    echo "<tr><td>История имён:</td><td>$name_history</td></tr>\n";
    echo "<tr><td>Известные альянсы:</td><td>$ally_history</td></tr>\n";
    echo "<tr><td><nobr>Игроки одного уровня по очкам:</nobr></td><td>Name, Name, Name</td></tr>\n";
    echo "<tr><td><nobr>Игроки одного уровня по флотам:</nobr></td><td>Name, Name, Name</td></tr>\n";
    echo "</table><br/>\n\n";

    echo "</td><td valign=top align=left width=40%>";

    echo "<table>\n";
    $planets_num = $moons_num = 0;
    $planets = GetUserPlanets ($player_id, $acc['acc_id']);
    foreach ($planets as $i=>$planet ) {
        if ($planet['type'] == 0) $planets_num++;
        if ($planet['type'] == 1) $moons_num++;
    }
    echo "<tr><td><strong>Список планет (".$planets_num.")</strong></td><td><strong>Список лун (".$moons_num.")</strong></td></tr>\n";
    echo "<tr><td valign=top>";
    foreach ($planets as $i=>$planet ) {
        if ($planet['type'] == 0) echo $planet['name']." [".$planet['g'].":".$planet['s'].":".$planet['p']."]<br>";
    }
    echo "</td><td valign=top>";
    foreach ($planets as $i=>$planet ) {
        if ($planet['type'] == 1) echo $planet['name']." (Луна) [".$planet['g'].":".$planet['s'].":".$planet['p']."], ".$planet['diam']." км<br>";
    }
    echo "</td></tr>";
    echo "</table><br/>\n\n";

    echo "</td></tr></table>";
}

function PagePlayerHistoryBox ($acc)
{
    $wdays = array ( "Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб" );
    $result = LoadStat ( $acc, $_GET['player_id'] );
    $rows = dbrows ( $result );
    $historyrows = array ();
    while ( $rows--) { 
        $row = dbarray ( $result );
        $d = getdate ( $row['date'] );
        $idx = timfmt($d['mday']) . "." . timfmt($d['mon']) . "." . $d['year'] . " " . timfmt($d['hours']) . ":00 " . $wdays[$d['wday']] ;
        if ( $row['type'] == 1) $historyrows [$idx]['points'] = $row['score'];
        else if ( $row['type'] == 2) $historyrows [$idx]['fpoints'] = $row['score'];
        else if ( $row['type'] == 3) $historyrows [$idx]['rpoints'] = $row['score'];
    }

    $tabrow = array ();  $tabrows = 0;

    $firstentry = TRUE;
    $oldpoints = $oldfpoints = $oldrpoints = 0;
    foreach ( $historyrows as $day => $value ) {
        $res  = "<tr>";
        $res .= "<td class=\"centered\">" . $day . "</td>";

        $points = $historyrows[$day]['points'];
        $fpoints = $historyrows[$day]['fpoints'];
        $rpoints = $historyrows[$day]['rpoints'];

        if ($points == NULL) $res .= "<td class=\"centered\"><font color=\"grey\" class=\"approx\">".nn($oldpoints)."</font></td>";
        else $res .= "<td class=\"centered\">".nn($points)."</td>";
        if ($points == NULL || $firstentry) $delta = 0;
        else $delta = $points - $oldpoints;
        if ($delta > 0) $delta = "+" . nn ($delta);
        else if ($delta < 0) $delta = "<font color=red>" . nn ($delta) . "</font>";
        else $delta = nn ($delta);
        $res .= "<td class=\"centered\">".$delta."</td>";

        if ($fpoints == NULL) $res .= "<td class=\"centered\"><font color=\"grey\" class=\"approx\">".nn($oldfpoints)."</font></td>";
        else $res .= "<td class=\"centered\">".nn($fpoints)."</td>";
        if ($fpoints == NULL || $firstentry) $delta = 0;
        else $delta = $fpoints - $oldfpoints;
        if ($delta > 0) $delta = "+" . nn ($delta);
        else if ($delta < 0) $delta = "<font color=red>" . nn ($delta) . "</font>";
        else $delta = nn ($delta);
        $res .= "<td class=\"centered\">".$delta."</td>";

        if ($rpoints == NULL) $res .= "<td class=\"centered\"><font color=\"grey\" class=\"approx\">".nn($oldrpoints)."</font>";
        else $res .= "<td class=\"centered\">".nn($rpoints);
        if ($rpoints == NULL || $firstentry) $delta = 0;
        else $delta = $rpoints - $oldrpoints;
        if ($delta > 0) {
            $res .= " +" . nn($delta);
            $res .= " (".GetResearchNameByPoints($delta).")";
        }
        $res .= "</td>";

        if ($points != NULL) $oldpoints = $points;
        if ($fpoints != NULL) $oldfpoints = $fpoints;
        if ($rpoints != NULL) $oldrpoints = $rpoints;
        $res .= "</tr>\n";

        $tabrow[$tabrows++] = $res;
        if ($firstentry) $firstentry = FALSE;
    }

    echo "<b>История развития</b>\n";
    echo "<table style=\"width: 100%\">\n";
    echo "<tr><th>Дата (Серверное время)</th><th>Очки (Общ.)</th><th>Разн. (Общ.)</th><th>Очки (Флот)</th><th>Разн. (Флот)</th><th>Очки (Иссл.)</th></tr>";
    for ($i=$tabrows-1; $i>=0; $i--) echo $tabrow[$i];
    echo "</table>\n\n";
}

function PagePlayerStat ()
{
    if ( IPBanned () ) PageHome (10001);
    $acc = LoadAccountBySig ( $_GET['sig'] );
    if ($acc == null) PageHome (10002);

    PageHeader ();
    PageMenu ($acc);
    PageSignature ($acc);
    PageUniverse ($acc);

    echo "<div id=\"pstat_content\" class=\"ui-widget-content\">\n";
        PagePlayerInfoBox ($acc);
        PagePlayerHistoryBox ($acc);
    echo "</div>\n";

    PageFooter ($acc);
}

?>