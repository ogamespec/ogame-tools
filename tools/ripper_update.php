<?php

// Разобрать "сырой" текст статистики и добавить информацию в базу.
function ParseStat ($acc, $text)
{
    global $errors;
    global $MaxPlace;

    // Найти и проверить название вселенной.
    $s = strstr ($text, "uni<");
    if ($s == FALSE) {
        echo "<font color=\"red\">".$errors[10004]."</font>";
        return;
    }
    $s = strstr ($s, "<");
    $tmp = str_between ($s, "<", ">");
    if ($tmp == false) {
        echo "<font color=\"red\">".$errors[10004]."</font>";
        return;
    }
    $s = strstr ($s, ">");
    $uni = trim ($tmp[0]);
    if ( CheckUniverse ($acc, $uni) == FALSE ) {
        echo "<font color=\"red\">".$errors[10005]."</font>";
        return;
    }

    $anum = $pnum = 0;            // Статистика альянсов
    $s = $text;
    while (1) {
        $stat = array ();
        $s = strstr ($s, "as<");
        if ($s == FALSE) break;
        $s = strstr ($s, "<");
        $tmp = str_between ($s, "<", ">");
        if ($tmp == false) break;
        $s = strstr ($s, ">");
        $fs = $tmp[0];

        $start = strpos ($fs, "/(") + 2;    // Название альянса.
        $end = strpos ($fs, ")/");
        $stat["name"] = trim (substr ($fs, $start, $end-$start));

        $part = substr ($fs, $end+2);
        $stat["allyid"] = strtok ($part, " ");
        $stat["members"] = strtok (" ");
        $stat["type"] = strtok (" ");
        $stat["place"] = strtok (" ");
        $stat["score"] = strtok (" ");
        $stat["date"] = time () - 2*60 *60;

        $astat = array ( $acc['acc_id'], $stat["allyid"], $stat["name"], $stat["members"], $stat["type"], $stat["place"], $stat["score"], $stat["date"] );
        AddDBRow ( $astat, "astat");
        $anum++;
    }

    $i = 0;                            // Статистика игроков
    $s = $text;
    while (1) {
        $stat = array ();
        $s = strstr ($s, "ps<");
        if ($s == FALSE) break;
        $s = strstr ($s, "<");
        $tmp = str_between ($s, "<", ">");
        if ($tmp == false) break;
        $s = strstr ($s, ">");
        $fs = $tmp[0];

        $start = strpos ($fs, "/(") + 2;    // Имя игрока.
        $end = strpos ($fs, ")/");
        $stat["name"] = trim (substr ($fs, $start, $end-$start));

        $part = substr ($fs, $end+2);
        $stat["playerid"] = strtok ($part, " ");
        $stat["allyid"] = strtok (" ");
        $stat["g"] = strtok (" ");
        $stat["s"] = strtok (" ");
        $stat["p"] = strtok (" ");
        $stat["type"] = strtok (" ");
        $stat["place"] = strtok (" ");
        $stat["score"] = strtok (" ");
        $stat["date"] = time () - 2*60 *60;

        // Игроки ниже MaxPlace не добавляются.
        if ( $stat["playerid"] != -1 && $stat["place"] <= $MaxPlace ) { 
            $status = GetLastStatus ( $stat["playerid"] );
            $pstat = array ( $acc['acc_id'], $stat["playerid"], $stat["name"], $stat["allyid"], $stat["g"], $stat["s"], $stat["p"], $stat["type"], $stat["place"], $stat["score"], $stat["date"], $status );
            AddDBRow ( $pstat, "pstat");
            $pnum++;
        }
    }

    if ( ($anum + $pnum) == 0) return "";
    else {
        $res = "<font color=\"lime\">Статистика добавлена (";
        if ($anum) $res .= "$anum альянсов";
        if ($anum && $pnum) $res .= ", ";
        if ($pnum) $res .= "$pnum игроков";
        $res .= ")</font>";
        return $res;
    }
}

// -----------------------------------------------------------------------------------------------------------------
// Ручное обновление.
// Исходные данные - XML-файлы OGame API.

function PageUpdate ()
{
    global $errors;
    global $MaxPlace;

    if ( IPBanned () ) PageHome (10001);
    $acc = LoadAccountBySig ( $_GET['sig'] );
    if ($acc == null) PageHome (10002);

    $result = "";

    if ($acc['u_update'] == false) $result = "<font color=\"red\">".$errors[10007]."</font>";

    else if ( method() === "POST" ) {
        $universe = "http://".$acc['uni']."/api/universe.xml";
        $players = "http://".$acc['uni']."/api/players.xml";
        $stat_total = "http://".$acc['uni']."/api/highscore.xml?category=1&type=0";
        $stat_fleet = "http://".$acc['uni']."/api/highscore.xml?category=1&type=3";
        $stat_research = "http://".$acc['uni']."/api/highscore.xml?category=1&type=2";

        // Загрузить Галактику
        $planets = $moons = 0;
        ClearGalaxy ($acc['acc_id']);
        $xml = simplexml_load_file ( $universe );
        foreach ( $xml->planet as $i=>$planet ) {
            list ($g,$s,$p) = split (':', $planet['coords'] );
            AddPlanet ( $acc['acc_id'], $planet['id'], $planet['player'], $g, $s, $p, $planet['name'], 0, 0 );
            $planets++;
            if ( key_exists ( 'moon', $planet ) ) {
                AddPlanet ( $acc['acc_id'], $planet->moon['id'], $planet['player'], $g, $s, $p, $planet->moon['name'], 1, $planet->moon['size'] );
                $moons++;
            }
        }
        $result .= "Галактика обновлена ($planets планет, $moons лун)<br>";
        unset ($xml);

        // Загрузить игроков
        $xml = simplexml_load_file ( $players );
        $users = array ();
        foreach ( $xml->player as $i=>$player ) {
            if ( !$player['status'] ) $player['status'] = '?';
            if ( !$player['alliance'] ) $player['alliance'] = 0;
            $users[intval($player['id'])] = array ( 'name' => strval($player['name']), 'status' => strval($player['status']), 'ally' => intval($player['alliance']) );
        }
        unset ($xml);

        // Загрузить статистику (Общие)
        $xml = simplexml_load_file ( $stat_total );
        $timestamp = intval($xml['timestamp']);
        foreach ( $xml->player as $i=>$row ) {
            $id = intval($row['id']);
            if ( $row['position'] < $MaxPlace ) {
                $pstat = array ( $acc['acc_id'], $id, $users[$id]['name'], $users[$id]['ally'], 0, 0, 0, 1, intval($row['position']), intval($row['score']), $timestamp, $users[$id]['status'] );
                AddDBRow ( $pstat, "pstat");
            }
        }
        $result .= "Общие очки обновлены!<br>";
        unset ($xml);

        // Загрузить статистику (Флот)
        $xml = simplexml_load_file ( $stat_fleet );
        $timestamp = intval($xml['timestamp']);
        foreach ( $xml->player as $i=>$row ) {
            $id = intval($row['id']);
            if ( $row['position'] < $MaxPlace ) {
                $pstat = array ( $acc['acc_id'], $id, $users[$id]['name'], $users[$id]['ally'], 0, 0, 0, 2, intval($row['position']), intval($row['ships']), $timestamp, $users[$id]['status'] );
                AddDBRow ( $pstat, "pstat");
            }
        }
        $result .= "Очки флота обновлены!<br>";
        unset ($xml);

        // Загрузить статистику (Исследования)
        $xml = simplexml_load_file ( $stat_research );
        $timestamp = intval($xml['timestamp']);
        foreach ( $xml->player as $i=>$row ) {
            $id = intval($row['id']);
            if ( $row['position'] < $MaxPlace ) {
                $pstat = array ( $acc['acc_id'], $id, $users[$id]['name'], $users[$id]['ally'], 0, 0, 0, 3, intval($row['position']), intval($row['score']), $timestamp, $users[$id]['status'] );
                AddDBRow ( $pstat, "pstat");
            }
        }
        $result .= "Очки исследований обновлены!<br>";
        unset ($xml);


    }

    PageHeader ();
    PageMenu ($acc);
    PageSignature ($acc);
    PageUniverse ($acc);

?>
<div id="update_content" class="ui-widget-content">

            <p>   
        Для обновления загрузите XML-файлы OGame API вашей вселенной : <br/>
Список игроков : <a href="http://<?=$acc['uni'];?>/api/players.xml">http://<?=$acc['uni'];?>/api/players.xml</a> (периодичность обновления 1 день)<br/>
Галактика : <a href="http://<?=$acc['uni'];?>/api/universe.xml">http://<?=$acc['uni'];?>/api/universe.xml</a> (периодичность обновления 1 неделя)<br/>
Статистика общая : <a href="http://<?=$acc['uni'];?>/api/highscore.xml?category=1&type=1">http://<?=$acc['uni'];?>/api/highscore.xml?category=1&type=1</a> (периодичность обновления 1 час)<br/>
Статистика Флот : <a href="http://<?=$acc['uni'];?>/api/highscore.xml?category=1&type=3">http://<?=$acc['uni'];?>/api/highscore.xml?category=1&type=3</a> (периодичность обновления 1 час)<br/>
Статистика Исследования : <a href="http://<?=$acc['uni'];?>/api/highscore.xml?category=1&type=2">http://<?=$acc['uni'];?>/api/highscore.xml?category=1&type=2</a> (периодичность обновления 1 час)<br/>
<br/>
</p>

<?=$result;?>
<br/>

            <form name="updateform" action="<?=scriptname();?>?page=update&sig=<?=$_GET['sig'];?>" method="POST">
            &nbsp; <button class="fg-button ui-state-default ui-corner-all" type="submit" >Обработать</button>
            </form>
            <br><br><br>
</div>
<?php

    PageFooter ($acc);
}

// -----------------------------------------------------------------------------------------------------------------
// Обновление статистики через Ajax.

function PageAutoUpdate ()
{
    global $errors;
    if ( IPBanned () ) {
        echo "<font color=\"red\">".$errors[10001]."</font>";
        die ();
    }
    $acc = LoadAccountBySig ( $_GET['sig'] );
    if ($acc == null) {
        echo "<font color=\"red\">".$errors[10002]."</font>";
        die ();
    }
    if ($acc['u_update'] == false) {
        echo "<font color=\"red\">".$errors[10007]."</font>";
        die ();
    }
    AddTraffic ( $acc, strlen ($_GET['text']), 0 );
    $text = str_replace ( "-", "+", $_GET['text']);
    $text = str_replace ( "_", "/", $text);
    $text = base64_decode ($text);
    echo ParseStat ($acc, $text);
}

?>