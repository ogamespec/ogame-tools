<?php

// -----------------------------------------------------------------------------------------------------------------
// Обзор галактики

function SaveLastGalaxy ( $acc_id, $g ) { global $db_prefix; dbquery ( "UPDATE ".$db_prefix."account SET last_g = $g WHERE acc_id = $acc_id" ); }
function SaveLastSystem ( $acc_id, $s ) { global $db_prefix; dbquery ( "UPDATE ".$db_prefix."account SET last_s = $s WHERE acc_id = $acc_id" ); }

function get_planet ( $planets, $g, $s, $p )
{
    foreach ( $planets as $i=>$planet ) {
        if ( $planet['g'] == $g && $planet['s'] == $s && $planet['p'] == $p ) return $planet;
    }
    return null;
}

function get_moon ( $moons, $g, $s, $p )
{
    foreach ( $moons as $i=>$moon ) {
        if ( $moon['g'] == $g && $moon['s'] == $s && $moon['p'] == $p ) return $moon;
    }
    return null;
}

function empty_row ($p) {
    echo "        <tr><td>$p</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>  \n";
};

function PageGalaxy ()
{
    if ( IPBanned () ) PageHome (10001);
    $acc = LoadAccountBySig ( $_GET['sig'] );

    PageHeader ();
    PageMenu ($acc);
    if ($acc != null) {
        PageSignature ($acc);
        PageUniverse ($acc);
    }

    if ( key_exists ('g', $_GET)) {         // Запомнить выбор галактики.
        if ( $_GET['g'] <= 0 ) $_GET['g'] = 1;
        if ( $_GET['g'] > 9 ) $_GET['g'] = 9;
        SaveLastGalaxy ( $acc['acc_id'], intval($_GET['g']) );
    }
    else $_GET['g'] = $acc['last_g'];
    if ( key_exists ('s', $_GET)) {
        if ( $_GET['s'] <= 0 ) $_GET['s'] = 1;
        if ( $_GET['s'] > 499 ) $_GET['s'] = 499;
        SaveLastSystem ( $acc['acc_id'], intval($_GET['s']) );
    }
    else $_GET['s'] = $acc['last_s'];

?>

<script type="text/javascript"> 
    $(function() { 
          document.getElementById('cg').value = <?=$_GET['g'];?>;
          document.getElementById('cs').value = <?=$_GET['s'];?>;
		$("#slider").slider( { 
                value: 499*(<?=$_GET['g'];?>-1) + <?=$_GET['s'];?>, 
                min: 0, max: 9*499-1, 
                change: function(event, ui) { 
                    g = parseInt ((ui.value / 499) + 1); 
                    s = ui.value % 499+1; 
                    $("#galaxyBrowse").submit(); 
                }, 
                slide: function(event, ui) { 
                    g = parseInt ((ui.value / 499) + 1); 
                    s = ui.value % 499+1; 
                    document.getElementById('cg').value = g; 
                    document.getElementById('cs').value = s; 
                } 
            }); 
	}); 

  function cursorevent(evt) { 
      if(evt.keyCode == 37) { 
        g = $("#cg").val (); 
        s = $("#cs").val (); 
        s--; if (s == 0) { s = 499; g--; } 
        if (g == 0) { g = 1; s = 1; } 
        $("#cg").val(g);
        $("#cs").val(s);
        $("#an").val(0);
        $("#galaxyBrowse").submit(); 
      }  
      if(evt.keyCode == 39) { 
        g = $("#cg").val (); 
        s = $("#cs").val (); 
        s++; if (s == 500) { s = 1; g++; } 
        if (g == 10) g = 9; 
        $("#cg").val(g);
        $("#cs").val(s);
        $("#an").val(0);
        $("#galaxyBrowse").submit();
      } 
      if(evt.keyCode == 38) {
        g = $("#cg").val (); 
        g--; if (g == 0) g = 1;
        $("#cg").val(g);
        $("#an").val(0);
        $("#galaxyBrowse").submit(); 
      } 
      if(evt.keyCode == 40) { 
        g = $("#cg").val (); 
        g++; if (g == 10) g = 9; 
        $("#cg").val(g);
        $("#an").val(0);
        $("#galaxyBrowse").submit();  
      }
  }  
  document.onkeydown = cursorevent;  
</script>


<div id="galaxy_content" class="ui-widget-content">

    <center><p><table id="galaxytab" class="ui-widget ui-widget-content" > 
    <tr><th colspan=5><table><tr><td>Солнечная система:</td> 
    <td><form id="galaxyBrowse" action="<?=scriptname();?>" method="GET"> <input type="hidden" name="page" value="galaxy"> <input type="hidden" name="sig" value="<?=$_GET['sig'];?>">
            <input id="cg" name="g" size="1"> <input id="cs" name="s" size="1"> 
          <button class="fg-button ui-state-default ui-corner-all" type="submit">Go</button></form></td></tr></table></th> 
    </tr> 
    <tr class="ui-widget-header"> 
        <th colspan=2>Планета</th> <th>Название</th> <th>Луна</th> <th>Игрок (статус)</th> <th>Альянс</th>
    </tr> 

<?php
    $result = EnumPlanets ( $acc['acc_id'], $_GET['g'], $_GET['s'] );
    $planets = array ();
    $moons = array ();
    $num = count ($planets);
    $mnum = count ($moons);
    while ( $row = dbarray ($result) ) {
        if ( $row['type'] == 1 ) $moons[] = $row;
        else $planets[] = $row;
    }

    for ($p=1; $p<=15; $p++) {

        $planet = get_planet ( $planets, $_GET['g'], $_GET['s'], $p );
        if ( $planet ) {
            $planetimg = "<img src=\"images/com_p101.gif\">";

            $moon = get_moon ( $moons, $_GET['g'], $_GET['s'], $p );
            if ( $moon ) $moonstr = "<img src=\"images/com_mond.gif\"> <sup>".$moon['diam']." км.</sup>";
            else $moonstr = "-";

            $last = LastStat ($acc, $planet['player_id']);
            if ( $last ) {
                if ( $last['ally_id'] ) {
                    $ally_stat = LastAllyStat ($acc, $last['ally_id']);
                    $allyname = $ally_stat['name'];
                }
                else $allyname = "";
                $username = $last['name'];
            }
            else $username = "<i><font color=gray>нет данных</font></i>";

            echo "        <tr><td>$p</td><td>$planetimg</td><td>".$planet['name']."</td><td>$moonstr</td>   \n";
            echo "              <td>$username</td><td>$allyname</td> </tr>   \n";
        }
        else empty_row ($p);
    }

    if ( $num + $mnum ) $colstr = "Заселено $num планет, $mnum лун";
    else $colstr = "";
?>

        <tr><th colspan=5><?=$colstr;?></th><th colspan=3> </th></tr> 
        <tr><td colspan=8><div id="slider"></div></td></tr> 
    </table></p> 
    </center> 

</div>

<?php
    PageFooter ($acc);
}

?>