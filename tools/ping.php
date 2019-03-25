<?php

// Утилита для проверки пинга до серверов OGame.
// (c) Andorianin, 2010.

$version = "0.01";
$shell = "linux";        // Возможные варианты: winxp-ru, linux

// Сервера OGame
// ---------------------------------------------------------------------------------------------------------

$server_ru = array (
 "uni1.ogame.ru", 
 "uni2.ogame.ru", 
 "uni3.ogame.ru", 
 "uni4.ogame.ru", 
 "uni5.ogame.ru", 
 "uni6.ogame.ru", 
 "uni7.ogame.ru", 
 "uni8.ogame.ru", 
 "uni9.ogame.ru", 
 "uni10.ogame.ru", 
 "uni11.ogame.ru", 
 "uni12.ogame.ru", 
 "uni13.ogame.ru", 
 "uni14.ogame.ru", 
 "uni15.ogame.ru", 
 "uni16.ogame.ru", 
 "uni17.ogame.ru", 
 "uni18.ogame.ru", 
 "uni19.ogame.ru", 
 "andromeda.ogame.ru", 
 "barym.ogame.ru", 
 "capella.ogame.ru", 
 "draco.ogame.ru", 
 "electra.ogame.ru", 
 "fornax.ogame.ru", 
 "gemini.ogame.ru", 
 "hydra.ogame.ru", 
);

// ---------------------------------------------------------------------------------------------------------

function PageHeader ()
{
    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
    echo "    <meta name=\"keywords\" content=\"OGame ping servers\" />\n";
    echo "    <meta name=\"description\" content=\"OGame Ping\" />\n";
    echo "    <meta name=\"author\" content=\"Andorianin\" />\n";
    echo "    <title>Состояние серверов</title>\n";
    echo "    <link type=\"text/css\" href=\"../css/com.css\" rel=\"stylesheet\" /> \n";
    echo "    <script type=\"text/javascript\" src=\"jquery/jquery-1.3.2.min.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/wtooltip.min.js\"></script> \n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.core.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.draggable.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.resizable.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.dialog.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.tabs.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.slider.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.accordion.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/effects.core.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/effects.slide.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/effects.drop.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/external/bgiframe/jquery.bgiframe.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/jquery-buttons.js\"></script>\n\n";
    echo "    <link rel=\"stylesheet\" href=\"jquery/themes/trontastic/ui.all.css\" type=\"text/css\">\n";
    echo "</head>\n\n";
    echo "<body>\n\n";
}

function PageFooter ()
{
    echo "\n</body>\n";
    echo "</html>\n";
}

function win2utf($s)    {
   for($i=0, $m=strlen($s); $i<$m; $i++)    {
       $c=ord($s[$i]);
       if ($c<=127) {$t.=chr($c); continue; }
       if ($c>=192 && $c<=207)    {$t.=chr(208).chr($c-48); continue; }
       if ($c>=208 && $c<=239) {$t.=chr(208).chr($c-48); continue; }
       if ($c>=240 && $c<=255) {$t.=chr(209).chr($c-112); continue; }
       if ($c==184) { $t.=chr(209).chr(209); continue; };
            if ($c==168) { $t.=chr(208).chr(129);  continue; };
            if ($c==184) { $t.=chr(209).chr(145); continue; };
            if ($c==168) { $t.=chr(208).chr(129); continue; };
            if ($c==179) { $t.=chr(209).chr(150); continue; };
            if ($c==178) { $t.=chr(208).chr(134); continue; };
            if ($c==191) { $t.=chr(209).chr(151); continue; };
            if ($c==175) { $t.=chr(208).chr(135); continue; };
            if ($c==186) { $t.=chr(209).chr(148); continue; };
            if ($c==170) { $t.=chr(208).chr(132); continue; };
            if ($c==180) { $t.=chr(210).chr(145); continue; };
            if ($c==165) { $t.=chr(210).chr(144); continue; };
            if ($c==184) { $t.=chr(209).chr(145); continue; };
   }
   return $t;
}

// Пингует хост и возвращает true, если хост доступен и false если недоступен.
// В переменные ipaddr попадает IP-адрес хоста, а в msec - средний пинг в миллисекундах.
// hopes задаёт количество пинг-запросов.
function PHP_Ping ($host, &$ipaddr, &$msec, $hopes)
{
    global $shell;
    $matches = array ();
    $sum = 0;

    if ($hopes > 5) $hopes = 5;

    if ($shell === "winxp-ru") {
        ob_start ();
        system("ping -n $hopes $host");
        $pingstr = ob_get_clean ();
        $pingstr = convert_cyr_string ($pingstr, "d", "w");
        $pingstr = win2utf ($pingstr);
        $count = preg_match_all ( "/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}.*=[0-9]*.*=[0-9]*мс/", $pingstr, &$matches );
        if ($count == 0) return false;

        for ($i=0; $i<$count; $i++) {
            $ss = preg_replace ( "/[^0-9]/", " ", $matches[0][$i] );
            $pp = preg_split ( "/\s+/", $ss );
            $ipaddr = $pp[0] . "." . $pp[1] . "." . $pp[2] . "." . $pp[3];
            $sum += $pp[5];
        }
        $msec = ceil ( $sum / $count );
        return true;
    }
    else if ($shell === "linux") {
        ob_start ();
        system ("ping -c$hopes -w$hopes $host");
        system("killall ping");
        $pingstr = ob_get_clean ();
        $count = preg_match_all ( "/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}.*=[0-9]*.*=[0-9]*.*=[0-9]*\./", $pingstr, &$matches );
        if ($count == 0) return false;

        for ($i=0; $i<$count; $i++) {
            $ss = preg_replace ( "/[^0-9]/", " ", $matches[0][$i] );
            $pp = preg_split ( "/\s+/", $ss );
            $ipaddr = $pp[0] . "." . $pp[1] . "." . $pp[2] . "." . $pp[3];
            $sum += $pp[6];
        }
        $msec = ceil ( $sum / $count );
        return true;
    }

    return false;
}

{
    PageHeader ();

    echo "<! ----------------- Тело страницы ------------------------ !>\n";
    echo "<div class=\"ui-widget-content ui-corner-all\">\n";
    echo "<center>\n";
    echo "<table class=\"ui-widget ui-widget-content\">\n";

    echo "<tr><h2>Состояние серверов OGame на ". date ("d-m-Y H:i") ."</h2></tr>\n";
    echo "<tr class=\"ui-widget-header\"><th width=\"200px\">Сервер</th><th>IP</th><th>Статус</th><th>Пинг</th></tr>\n";

    set_time_limit (0);
    foreach ($server_ru as $host )
    {
        $ipaddr = "0.0.0.0";
        $msec = 0;
        $status = PHP_Ping ( $host, &$ipaddr, &$msec, 3);
        if ($status) echo "<tr><td>$host</th><td>$ipaddr</td><th><font color=\"lime\">On</font></th><td>$msec мс</td></tr>\n";
        else echo "<tr><td>$host</th><td>-</td><th><font color=\"red\">Off</font></th><td>-</td></tr>\n";
    }

    echo "</table>\n";
    echo "</center>\n";
    echo "</div>\n";

    PageFooter ();
}

?>