<?php

// -----------------------------------------------------------------------------------------------------------------
// Легенда.

function PagePlayersLegend ($acc)
{
    $plastdate = array ();
    $pids = array ();
    $pscores = array ();
    $plastfleet = array ();
    $pfleets = array ();
    $plastresearch = array ();
    $presearch = array ();    

    // Получить статистику за месяц и рассортировать по ассоциативным массивам.
    $result = LoadMonthStat ($acc);
    $rows = dbrows ($result);
    while ($rows--) {
        $row = dbarray ($result);
        if ($row['type'] == 1) {
            if ( ($row['date'] > $plastdate[$row['player_id']]) || !isset ($plastdate[$row['player_id']]) ) {
                $plastdate[$row['player_id']] = $row['date'];
                $pids[$row['player_id']] = $row['name'];
            }
            $pscores[$row['player_id']][$row['date']]['score'] = $row['score'];
            $pscores[$row['player_id']][$row['date']]['place'] = $row['place'];
            $pscores[$row['player_id']][$row['date']]['ally_id'] = $row['ally_id'];
        }
        if ($row['type'] == 2) {
            if ( ($row['date'] > $plastfleet[$row['player_id']]) || !isset ($plastfleet[$row['player_id']]) ) {
                $plastfleet[$row['player_id']] = $row['date'];
            }
            $pfleets[$row['player_id']][$row['date']]['score'] = $row['score'];
            $pfleets[$row['player_id']][$row['date']]['place'] = $row['place'];
        }
        if ($row['type'] == 3) {
            if ( ($row['date'] > $plastresearch[$row['player_id']]) || !isset ($plastresearch[$row['player_id']]) ) {
                $plastresearch[$row['player_id']] = $row['date'];
            }
            $presearch[$row['player_id']][$row['date']]['score'] = $row['score'];
            $presearch[$row['player_id']][$row['date']]['place'] = $row['place'];
        }        
    }

    ksort (&$pids);
    
    $n = 1;
    echo "<table style=\"width: 100%;\">\n";
    echo "<tr><td colspan=\"10\">Игроки в порядке регистрации</td></tr>\n";    
	echo "<tr><th>N</th><th>ID</th><td>Имя</td><td>Альянс</td><th>Очки</th><th>Флот</th><th>Иссл.</th><th>Очки(Место)</th><th>Флот(Место)</th><th>Иссл.(Место)</th></tr>\n";
	foreach ( $pids as $id => $name ) {
		$scores = $pscores[$id][$plastdate[$id]]['score'];
		$fscores = $pfleets[$id][$plastfleet[$id]]['score'];
		$rscores = $presearch[$id][$plastresearch[$id]]['score'];
		
		$place = $pscores[$id][$plastdate[$id]]['place'];
		$fplace = $pfleets[$id][$plastfleet[$id]]['place'];
		$rplace = $presearch[$id][$plastresearch[$id]]['place'];
		
		$ally_id = $pscores[$id][$plastdate[$id]]['ally_id'];
		$astat = LastAllyStat ($acc, $ally_id);

		echo "<tr>";
		echo "<td class=\"centered\">$n</td>";
		echo "<td class=\"centered\">$id</td>";
		echo "<td><a href=\"".scriptname()."?page=pstat&player_id=$id&sig=".$_GET['sig']."\">$name</a></td>";
		echo "<td><a href=\"".scriptname()."?page=astat&ally_id=$ally_id&sig=".$_GET['sig']."\">".$astat['name']."</a></td>";
		echo "<td class=\"centered\">".nn($scores)."</td>";
		echo "<td class=\"centered\">".nn($fscores)."</td>";
		echo "<td class=\"centered\">".nn($rscores)."</td>";
		echo "<td class=\"centered\">".nn($place)."</td>";		
		echo "<td class=\"centered\">".nn($fplace)."</td>";
		echo "<td class=\"centered\">".nn($rplace)."</td>";
		echo "</tr>\n";		
		$n++;
	}
	echo "</table>\n";

}

function PageLegend ()
{
    if ( IPBanned () ) PageHome (10001);
    $acc = LoadAccountBySig ( $_GET['sig'] );
    if ($acc == null) PageHome (10002);

    PageHeader ();
    PageMenu ($acc);
    PageSignature ($acc);
    PageUniverse ($acc);

    echo "<div id=\"legend_content\" class=\"ui-widget-content\">\n";
		PagePlayersLegend ($acc);
    echo "</div>\n";

    PageFooter ($acc);
}

?>