<HTML>
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="../evolution/formate.css">
<link rel="stylesheet" type="text/css" href="../css/default.css">
<TITLE>Подсчет добычи</TITLE>
</HEAD>

<BODY>

<center><br>

<?php
/*
    Подсчет добычи.
    (c) Andorianin, 2009.
*/
    $version = 0.01;

function nicenum ($number)
{
    return number_format($number,0,",",".");
}

    if (!key_exists("capture", $_POST)) $cap = 0;
    else
    {
        $cap =  $_POST['capture'];
        $cap = str_replace (".", "", $cap);
        
        $totalm = $totalk = $totald = 0;

        while (1)
        {
            $cap = strpbrk ($cap, "0123456789");
            if ($cap == null) break;
            sscanf ($cap, "%d", $capm);
            $cap = strstr ($cap, "металла");
            $cap = strpbrk ($cap, "0123456789");
            sscanf ($cap, "%d", $capk);
            $cap = strstr ($cap, "кристалла");
            $cap = strpbrk ($cap, "0123456789");
            sscanf ($cap, "%d", $capd);
            $cap = strstr ($cap, "дейтерия");
            
            $totalm += $capm;
            $totalk += $capk;
            $totald += $capd;

            echo nicenum($capm) . " металла, ". nicenum($capk) . " кристалла, " . nicenum($capd) . " дейтерия<br>";
        }

        echo "<br>Всего: ". nicenum($totalm) ." металла, ". nicenum($totalk) . " кристалла, ". nicenum($totald) ." дейтерия<br>";
        $totalres = $totalm + $totalk + $totald;
        echo "Всего ресурсов: " . nicenum($totalres) . "<br>";
        
        exit ();
    }

?>

<form action="capture.php" method=post>
<br><br><br><br><br><br><table>
<tr><td class='c'>Вставьте сообщения о захваченных ресурсах:</td></tr>
<tr><th><textarea cols='150' rows='20' name='capture'></textarea></th></tr>
<tr><td><input type=submit value='Подсчет'></td></tr>
</table>
</form>

</center>

</BODY>
</HTML>
