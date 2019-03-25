<?php

// -----------------------------------------------------------------------------------------------------------------
// Информация по альянсу.

function PageAllyMembers ($acc)
{
    $result = EnumAllyMembers ($acc, $_GET['ally_id'] );
    $rows = dbrows ( $result );

    $members = array ();
    while ($rows--) {
        $row = dbarray ($result);
        $id = $row['player_id'];
        if ( $row['type'] == 1 ) { 
            if ( $row['date'] < $members[$id]['min_date'] || !isset($members[$id]['min_date']) ) {
                $members[$id]['min_date'] = $row['date'];
                $members[$id]['min_score'] = $row['score'];
                $members[$id]['min_place'] = $row['place'];
            }
            if ( $row['date'] > $members[$id]['max_date'] || !isset($members[$id]['max_date']) ) {
                $members[$id]['max_date'] = $row['date'];
                $members[$id]['max_score'] = $row['score'];
                $members[$id]['max_place'] = $row['place'];
            }
        }
        else if ( $row['type'] == 2 ) {
            if ( $row['date'] < $members[$id]['min_fdate'] || !isset($members[$id]['min_fdate']) ) {
                $members[$id]['min_fdate'] = $row['date'];
                $members[$id]['min_fscore'] = $row['score'];
            }
            if ( $row['date'] > $members[$id]['max_fdate'] || !isset($members[$id]['max_fdate']) ) {
                $members[$id]['max_fdate'] = $row['date'];
                $members[$id]['max_fscore'] = $row['score'];
            }
        }
        else if ( $row['type'] == 3 ) $members[$id]['rscore'] = $row['score'];
        $members[$id]['name'] = $row['name'];
        $members[$id]['id'] = $id;
    }

    $mrows = array ();
    $bbrows = array ();
    foreach ( $members as $id => $member ) {
        $place = $member['max_place'];
        if ($place <= 0) continue;
        $mrows[$place] = "";
        $mrows[$place] .= "<tr>";
        $mrows[$place] .= "<td ><a href=\"".scriptname()."?page=pstat&player_id=".$member['id']."&sig=".$_GET['sig']."\">" . $member['name'] . "</a></td>";
        $mrows[$place] .= "<td >" . nn($member['max_place']) . "</td>";
        $mrows[$place] .= "<td >" . nn($member['max_score']) . "</td>";
        $mrows[$place] .= "<td >" . nn($member['max_fscore']) . "</td>";
        $mrows[$place] .= "<td >" . nn($member['rscore']) . "</td>";
        $bbrows[$place] = "[tr][td]".$member['name']."[/td][td]".nn($member['max_place'])."[/td][td]".nn($member['max_score'])."[/td][td]".nn($member['max_fscore'])."[/td]";

        $delta = $member['max_score'] - $member['min_score'];
        if ($delta > 0) { $mrows[$place] .= "<td><font color=\"lime\">+" . nn($delta) . "</font></td>"; $bbrows[$place] .= "[td][color=lime]+".nn($delta)."[/color][/td]"; }
        else if ( $delta < 0 ) { $mrows[$place] .= "<td><font color=\"red\">-" . nn(abs($delta)) . "</font></td>"; $bbrows[$place] .= "[td][color=red]-".nn(abs($delta))."[/color][/td]]"; }
        else { $mrows[$place] .= "<td >0</td>"; $bbrows[$place] .= "[td]0[/td]"; }

        $delta = $member['max_fscore'] - $member['min_fscore'];
        if ($delta > 0) { $mrows[$place] .= "<td><font color=\"lime\">+" . nn($delta) . "</font></td>"; $bbrows[$place] .= "[td][color=lime]+".nn($delta)."[/color][/td]"; }
        else if ( $delta < 0 ) { $mrows[$place] .= "<td><font color=\"red\">-" . nn(abs($delta)) . "</font></td>"; $bbrows[$place] .= "[td][color=red]-".nn(abs($delta))."[/color][/td]"; }
        else { $mrows[$place] .= "<td >0</td>"; $bbrows[$place] .= "[td]0[/td]"; }
        echo "</tr>\n";
        $bbrows[$place] .= "[/tr]";
    }

    ksort (&$mrows);
    ksort (&$bbrows);

    $ally_stat = LastAllyStat ($acc, $_GET['ally_id']);
    echo "<h2>Альянс ".$ally_stat['name']."</h2>";

    echo "<b>Состав</b>\n";
    echo "<table style=\"width: 30%\">\n";
    echo "<tr><th align=\"left\">Игрок</th><th align=\"left\">Место</th><th align=\"left\">Очки</th><th align=\"left\">Флот</th><th align=\"left\">Иссл.</th><th align=\"left\">Прирост за 7 дн. (Очки)</th><th align=\"left\">Прирост за 7 дн. (Флот)</th></tr>";
    foreach ( $mrows as $i => $value ) {
        echo $value;
    }
    echo "</table>\n\n";

    echo "<br/><b>BB-код для добавления на форум</b>\n";
    echo "<table style=\"width: 100%\">\n";
    echo "[table][tr][td][b]Игрок[/b][/td][td][b]Место[/b][/td][td][b]Очки[/b][/td][td][b]Флот[/b][/td][td][b]Прирост за 7 дн. (Очки)[/b][/td][td][b]Прирост за 7 дн. (Флот)[/b][/td][/tr]";
    foreach ( $bbrows as $i => $value ) {
        echo $value;
    }
    echo "[/table]";
    echo "<tr><td>$bbcode</td></tr>\n";
    echo "</table>\n\n";
}

function PageAllyHistoryBox ($acc)
{
    $wdays = array ( "Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб" );
    $result = LoadAllyStat ( $acc, $_GET['ally_id'] );
    $rows = dbrows ( $result );
    $historyrows = array ();
    while ( $rows--) { 
        $row = dbarray ( $result );
        $d = getdate ( $row['date'] );
        $idx = timfmt($d['mday']) . "." . timfmt($d['mon']) . "." . $d['year'] . " " . timfmt($d['hours']) . ":00 " . $wdays[$d['wday']];
        if ( $row['type'] == 1) $historyrows [$idx]['points'] = $row['score'];
        else if ( $row['type'] == 2) $historyrows [$idx]['fpoints'] = $row['score'];
        else if ( $row['type'] == 3) $historyrows [$idx]['rpoints'] = $row['score'];
        $historyrows [$idx]['members'] = $row['members'];
    }

    $tabrow = array ();  $tabrows = 0;

    $firstentry = TRUE;
    $oldpoints = $oldfpoints = $oldrpoints = 0;
    foreach ( $historyrows as $day => $value ) {
        $members = $historyrows[$day]['members'];

        $res  = "<tr>";
        $res .= "<td class=\"centered\">" . $day . "</td>";
        $res .= "<td class=\"centered\">" . $members . "</td>";

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

        if ($rpoints == NULL) $res .= "<td class=\"centered\"><font color=\"grey\" class=\"approx\">".nn($oldrpoints)."</font></td>";
        else $res .= "<td class=\"centered\">".nn($rpoints)."</td>";

        if ($points == NULL) $res .= "<td class=\"centered\"><font color=\"grey\" class=\"approx\">".nn($oldpoints / $members)."</font></td>";
        else $res .= "<td class=\"centered\">".nn($points / $members)."</td>";
        if ($fpoints == NULL) $res .= "<td class=\"centered\"><font color=\"grey\" class=\"approx\">".nn($oldfpoints / $members)."</font></td>";
        else $res .= "<td class=\"centered\">".nn($fpoints / $members)."</td>";
        if ($rpoints == NULL) $res .= "<td class=\"centered\"><font color=\"grey\" class=\"approx\">".nn($oldrpoints / $members)."</font></td>";
        else $res .= "<td class=\"centered\">".nn($rpoints / $members)."</td>";

        if ($points != NULL) $oldpoints = $points;
        if ($fpoints != NULL) $oldfpoints = $fpoints;
        if ($rpoints != NULL) $oldrpoints = $rpoints;
        $res .= "</tr>\n";

        $tabrow[$tabrows++] = $res;
        if ($firstentry) $firstentry = FALSE;
    }

    echo "<b>История развития</b>\n";
    echo "<table style=\"width: 100%\">\n";
    echo "<tr><th>Дата (Серверное время)</th><th>Игроков</th><th>Очки (Общ.)</th><th>Разн. (Общ.)</th><th>Очки (Флот)</th><th>Разн. (Флот)</th><th>Очки (Иссл.)</th><th>На игрока (Общ.)</th><th>На игрока (Флот)</th><th>На игрока (Иссл.)</th></tr>";
    for ($i=$tabrows-1; $i>=0; $i--) echo $tabrow[$i];
    echo "</table>\n\n";
}

function PageAllyStat ()
{
    if ( IPBanned () ) PageHome (10001);
    $acc = LoadAccountBySig ( $_GET['sig'] );
    if ($acc == null) PageHome (10002);

    PageHeader ();
    PageMenu ($acc);
    PageSignature ($acc);
    PageUniverse ($acc);

    echo "<div id=\"astat_content\" class=\"ui-widget-content\">\n";
        PageAllyMembers ($acc);
        echo "<br/>";
        PageAllyHistoryBox ($acc);
    echo "</div>\n";

    PageFooter ($acc);
}

?>