<?php

/*
    Транспортный Терминал.
    (c) Andorianin, 2009, 2010
*/
$version = "0.27";

ob_start ();

header('Pragma:no-cache');

$server = "ru";

$file = $_SERVER["SCRIPT_NAME"];
$break = Explode('/', $file);
$SELF = $break[count($break) - 1]; 

$loc = $_COOKIE["lang"."_".$server];
if ($loc == "") $loc = 'ru';
$SecretWord = "MadeInSpecnaz";
$debug = 0;

// Глобальный язык и скин. Заменяются настройками пользователя.
$GlobalLang = $_COOKIE["global_lang"];
if ($GlobalLang == "") $GlobalLang = "en";
$GlobalSkin = $_COOKIE["global_skin"];
if ($GlobalSkin == "") $GlobalSkin = "http://ogamespec.com/evolution/";

define ("CONFIGTABLE", "trade_config");
define ("USERTABLE", "trade_users");
define ("LISTTABLE", "trade_list");
define ("UNITABLE", "trade_unis");

require_once "tradelocal.php";

$Config = array ();

require_once "tradeconfig.php";
require_once "db.php";

function scriptname () {
    $break = explode('/', $_SERVER["SCRIPT_NAME"]);
    return $break[count($break) - 1]; 
}
function hostname () {
    $host = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
    $break = explode ('/', $host);
    $break[count($break)-1] = '';
    return implode ('/', $break); 
}

function nicenum ($number)
{
    return number_format($number,0,",",".");
}

function ValidateURL ($url)
{
    $urlregex = "^(https?|ftp)\:\/\/([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?\$";
    if (eregi($urlregex, $url)) return true;
    else return false; 
}

// Защита от PHP и SQL-инъекций.
function KillInjection ($str)
{
    $search = array ( "'<script[^>]*?>.*?</script>'si",  // Вырезает javaScript
                      "'<[\/\!]*?[^<>]*?>'si",           // Вырезает HTML-теги
                      "'([\r\n])[\s]+'" );             // Вырезает пробельные символы
    $replace = array ("", "", "\\1", "\\1" );
    $str = preg_replace($search, $replace, $str);
    $str = str_replace ("'", "", $str);
    $str = str_replace ("\"", "", $str);
    $str = str_replace ("%0", "", $str);
    return $str;
}

function ConnectDatabase ()
{
    global $db_host, $db_user, $db_pass, $db_name;
    dbconnect($db_host, $db_user, $db_pass, $db_name);
    dbquery("SET NAMES 'utf8';");
    dbquery("SET CHARACTER SET 'utf8';");
    dbquery("SET SESSION collation_connection = 'utf8_general_ci';");
    echo "<!-- MySQL connection established... -->\n";
}

// Вывод сообщения об ошибке и делаем автоматический редирект на главную.
function ErrorPage ($error, $seconds)
{
    global $SELF, $local, $loc, $GlobalLang, $GlobalSkin;
    echo "<HTML><HEAD><link rel='stylesheet' type='text/css' href='".$GlobalSkin."/formate.css'>\n";
    echo "<meta http-equiv='refresh' content='".$seconds.";url=".$SELF."' />\n";
    echo "<meta http-equiv='content-type' content='text/html; charset=utf-8' />\n";
    echo "<TITLE>".$local[$GlobalLang]['Terminal']."</TITLE>\n";
    echo "</HEAD><BODY>  <center><font size=3><b> <br /><br />\n";
    echo "<font color='#FF0000'>".$local[$GlobalLang]['Error']."</font><br /><br />\n";
    echo $error."<br/><br/>\n";
    echo "</BODY></HTML>\n";
    ob_end_flush ();
    exit ();
}

// Получение конфигурации.
function LoadConfig ($server)
{
    global $Config, $debug;
    $result = dbquery("SELECT * FROM ".CONFIGTABLE." WHERE server='".$server."'");
    if (dbrows($result) != 0)
    {
        $Config = dbarray($result);
        if ($debug)
        {
            echo "<pre>";
            print_r ($Config);
            echo "</pre>";
        }
    }
}

// Сохранить конфигурацию.
function SaveConfig ()
{
    global $Config;
    foreach ($Config as $i=>$entry)
    {
        if ($i === "user_base" || $i === "trade_base")
        {
            $query = "UPDATE ".CONFIGTABLE." SET ".$i." = '".$entry."' WHERE server='ru'";
            dbquery( $query);
        }
    }
}

//////////////////////////////////////////////////////////////////////////////
// Управление списком вселенных.

// Сервера Ogame.
$gameservers = array (
    'ae' => array ( 'country' => 'ОАЭ', 'host' => 'Ae.ogame.org', 'flag' => 0 ),
    'ar' => array ( 'country' => 'Аргентина', 'host' => 'Ogame.com.ar', 'flag' => -14 ),
    'ba' => array ( 'country' => 'Босния и Герцоговина', 'host' => 'Ba.ogame.org', 'flag' => -854 ),
    'br' => array ( 'country' => 'Бразилия', 'host' => 'Ogame.com.br', 'flag' => -56 ),
    'bg' => array ( 'country' => 'Болгария', 'host' => 'Bg.ogame.org', 'flag' => -42 ),
    'cz' => array ( 'country' => 'Чешская Республика', 'host' => 'Ogame.cz', 'flag' => -154 ),
    'de' => array ( 'country' => 'Германия', 'host' => 'Ogame.de', 'flag' => -168 ),
    'dk' => array ( 'country' => 'Дания', 'host' => 'Ogame.dk', 'flag' => -182 ),
    'en' => array ( 'country' => 'Великобритания', 'host' => 'Ogame.org', 'flag' => -224 ),
    'es' => array ( 'country' => 'Испания', 'host' => 'Ogame.com.es', 'flag' => -238 ),
    'fi' => array ( 'country' => 'Финляндия', 'host' => 'Fi.ogame.org', 'flag' => -266 ),
    'fr' => array ( 'country' => 'Франция', 'host' => 'Ogame.fr', 'flag' => -280 ),
    'gr' => array ( 'country' => 'Греция', 'host' => 'Ogame.gr', 'flag' => -294 ),
    'hr' => array ( 'country' => 'Хорватия', 'host' => 'Ogame.com.hr', 'flag' => -322 ),
    'hu' => array ( 'country' => 'Венгрия', 'host' => 'Ogame.hu', 'flag' => -336 ),
    'it' => array ( 'country' => 'Италия', 'host' => 'Ogame.it', 'flag' => -420 ),
    'jp' => array ( 'country' => 'Япония', 'host' => 'Ogame.jp', 'flag' => -434 ),
    'lt' => array ( 'country' => 'Литва', 'host' => 'Ogame.lt', 'flag' => -476 ),
    'lv' => array ( 'country' => 'Латвия', 'host' => 'Ogame.lv', 'flag' => -490 ),
    'mx' => array ( 'country' => 'Мексика', 'host' => 'Mx.ogame.org', 'flag' => -532 ),
    'nl' => array ( 'country' => 'Нидерланды', 'host' => 'Ogame.nl', 'flag' => -546 ),
    'no' => array ( 'country' => 'Норвегия', 'host' => 'Ogame.no', 'flag' => -560 ),
    'pl' => array ( 'country' => 'Польша', 'host' => 'Ogame.pl', 'flag' => -616 ),
    'pt' => array ( 'country' => 'Португалия', 'host' => 'Ogame.com.pt', 'flag' => -630 ),
    'ro' => array ( 'country' => 'Румыния', 'host' => 'Ogame.ro', 'flag' => -644 ),
    'rs' => array ( 'country' => 'Сербия', 'host' => 'Ogame.rs', 'flag' => -658 ),
    'ru' => array ( 'country' => 'Российская Федерация', 'host' => 'Ogame.ru', 'flag' => -672 ),
    'sk' => array ( 'country' => 'Словакия', 'host' => 'Ogame.sk', 'flag' => -714 ),
    'se' => array ( 'country' => 'Швеция', 'host' => 'Ogame.se', 'flag' => -686 ),
    'si' => array ( 'country' => 'Словения', 'host' => 'Si.ogame.org', 'flag' => -700 ),
    'tr' => array ( 'country' => 'Турция', 'host' => 'Tr.ogame.org', 'flag' => -742 ),
    'tw' => array ( 'country' => 'Тайвань', 'host' => 'Ogame.tw', 'flag' => -756 ),
    'us' => array ( 'country' => 'США', 'host' => 'Ogame.us', 'flag' => -798 )
);

// Названия вселенных из редизайна.
$reduninames = array (
 "Andromeda", "Barym", "Capella", "Draco", "Electra", "Fornax", "Gemini", "Hydra", "Io", "Jupiter"
);

// Имя Вселенной.
function UniverseName ($num)
{
    global $local, $loc, $reduninames, $GlobalLang;
    if ($num < 100) return $local[$GlobalLang]['Uni'] . " $num";
    else {
        if ( isset ( $reduninames[$num-101] ) ) return $reduninames[$num-101];
        else return $local[$GlobalLang]['Uni'] . " $num";
    }
}

// Загрузить список вселенных для выбранного сервера.
function LoadUnis ($server)
{
	return dbquery("SELECT * FROM ".UNITABLE." WHERE server='".$server."' ORDER BY num ASC");
}

function UniSelect ()
{
    global $lang, $local, $loc, $Config, $gameservers, $GlobalLang;
    $res = "<select name='uni' WIDTH='120' STYLE='width: 120px'>\n";

	$result = LoadUnis ($loc);
    $rows = dbrows($result);
	$res .= "<optgroup label='".$gameservers[$loc]['host']."' style='font-style: normal; font-weight: bold;'>\n";
    while ($rows--)
    {
        $s = dbarray($result);
		$res .= "<option style='font-style: normal; font-weight: normal;' value='".$s['server'].$s['num']."'>".UniverseName($s['num'])."</option>\n";
    }
	$res .= "</optgroup>\n";

    $res .= "</select>\n";
    return $res;
}

function DrawFlags()
{
	global $local, $loc, $gameservers, $GlobalLang;

    foreach ( $gameservers as $server => $obj ) {
        $bk = "";
        if ($server === $loc) $bk = "style='background-color: gold;'";
		echo "<td $bk ><a onclick='onFlagClick(\"$server\");' title='".$local[$GlobalLang][$server]."' href='#' style='text-decoration: none; background:url(\"../images/mmoflags.png\") no-repeat scroll 0 0 transparent; padding-left:23px; height:14px !important; background-position: left ".$gameservers[$server]['flag']."px !important;'>&nbsp;</a></td>\n";
	}
}

//////////////////////////////////////////////////////////////////////////////
// Функции управления пользователями.

function AddUser ($name, $pass, $uni, $email)
{
    global $Config;
    $user = array ();

    $Config['user_base']++;
    $user['id'] = $Config['user_base'];
    $user['login'] = $name;
    $user['passmd'] = md5($pass . $SecretWord);
    $user['uni'] = $uni;
    $user['email'] = $email;
    $user['admin'] = 0;
    $user['skin'] = "";
    $user['lang'] = "";
    $user['ally'] = "";
    $user['emailack'] = "";

    dbquery( "INSERT INTO ".USERTABLE." (id) VALUES ('".$user['id']."')" );
    foreach ($user as $i=>$entry)
    {
        if ($i != 'id')
        {
            $query = "UPDATE ".USERTABLE." SET ".$i." = '".$entry."' WHERE id='".$user['id']."'";
            dbquery( $query);
            if ($debug) echo "$query <br> ";
        }
    }

    SaveConfig ();
}

function IsUserExist ($name, $uni)
{
    if ($name === "guest") return true;
    if (GetUserID ($name, $uni) == 0) return false;
    else return true;
}

function IsPasswordCorrect ($name, $pass, $uni)
{
    if ($name === "guest") return true;
    if ( GetUserPassword ($name, $uni) === md5 ($pass . $SecretWord) ) return true;
    return false;
}

function GetUserEmail ($name, $uni)
{
    $result = dbquery("SELECT * FROM ".USERTABLE." WHERE login='".$name."' and uni='".$uni."'");
    if (dbrows($result) != 0)
    {
        $user = dbarray($result);
        return $user['email'];
    }
    return '';
}

function GetUserID ($name, $uni)
{
    if ($name === "guest") return 1;
    $result = dbquery("SELECT * FROM ".USERTABLE." WHERE login='".$name."' and uni='".$uni."'");
    if (dbrows($result) != 0)
    {
        $user = dbarray($result);
        return $user['id'];
    }
    return 0;
}

function GetUserPassword ($name, $uni)
{
    $result = dbquery("SELECT * FROM ".USERTABLE." WHERE login='".$name."' and uni='".$uni."'");
    if (dbrows($result) != 0)
    {
        $user = dbarray($result);
        return $user['passmd'];
    }
    return "";
}

function GetUserNameById ($id, $uni)
{
    if ($id == 1) return "guest";
    $result = dbquery("SELECT * FROM ".USERTABLE." WHERE id='".$id."' and uni='".$uni."'");
    if (dbrows($result) != 0)
    {
        $user = dbarray($result);
        return $user['login'];
    }
    return "";
}

function IsUserAdmin ($name, $uni)
{
    if ($name === "guest") return 0;
    $result = dbquery("SELECT * FROM ".USERTABLE." WHERE login='".$name."' and uni='".$uni."'");
    if (dbrows($result) != 0)
    {
        $user = dbarray($result);
        return $user['admin'];
    }
    return 0;
}

function IsUserBanned ($name, $uni)
{
    if ($name === "guest") return 0;
    $result = dbquery("SELECT * FROM ".USERTABLE." WHERE login='".$name."' and uni='".$uni."'");
    if (dbrows($result) != 0)
    {
        $user = dbarray($result);
        return $user['banned'];
    }
    return 0;
}

function GetUserBids ($id)
{
    $result = dbquery("SELECT * FROM ".LISTTABLE." WHERE user_id='".$id."'");
    return dbrows($result);
}

function BanUser ($id, $banned)
{
    $query = "UPDATE ".USERTABLE." SET banned = '".$banned."' WHERE id='".$id."'";
    dbquery( $query);
}

function WipeUser ($id)
{
    dbquery( "DELETE FROM ".LISTTABLE." WHERE user_id='".$id."'" );
}

function GetUserIdFromTradeId ($trade_id)
{
    $result = dbquery("SELECT * FROM ".LISTTABLE." WHERE trade_id='".$trade_id."'");
    if (dbrows($result) != 0)
    {
        $trade = dbarray($result);
        return $trade['user_id'];
    }
    return 0;
}

function RenameUser ($id, $login)
{
    if (IsUserExist ($login, $_SESSION['uni']) == true) return false;
    $query = "UPDATE ".USERTABLE." SET login = '".$login."' WHERE id='".$id."'";
    dbquery( $query);
    return true;
}

function SetUserSkin ($skin)
{
    $query = "UPDATE ".USERTABLE." SET skin = '".$skin."' WHERE id='".$_SESSION['user_id']."'";
    dbquery( $query);
}

function SetUserLang ($lang)
{
    $query = "UPDATE ".USERTABLE." SET lang = '".$lang."' WHERE id='".$_SESSION['user_id']."'";
    dbquery( $query);
}

function GetUserCount ()
{
    $result = dbquery("SELECT * FROM ".USERTABLE);
    return dbrows($result);
}

function GetBidsCount ()
{
    $result = dbquery("SELECT * FROM ".LISTTABLE);
    return dbrows($result);
}

function GetUserSkin ()
{
    global $GlobalSkin;
    $result = dbquery("SELECT * FROM ".USERTABLE." WHERE id='".$_SESSION['user_id']."'");
    if (dbrows($result) != 0)
    {
        $user = dbarray($result);
        if (strlen ($user['skin']) > 5) return $user['skin'];
    }
    return $GlobalSkin;
}

function GetUserLang ()
{
    global $GlobalLang;
    $result = dbquery("SELECT * FROM ".USERTABLE." WHERE id='".$_SESSION['user_id']."'");
    if (dbrows($result) != 0)
    {
        $user = dbarray($result);
        return $user['lang'];
    }
    return $GlobalLang;
}

function SetUserPassword ($pass, $id)
{
    $md = md5($pass . $SecretWord);
    $query = "UPDATE ".USERTABLE." SET passmd = '".$md."' WHERE id='".$id."'";
    dbquery( $query);
}

function LoadUser ($id)
{
	$result = dbquery("SELECT * FROM ".USERTABLE." WHERE id=$id");
    return dbarray ($result);
}

// Загрузить настройки курсов вселенной.
function LoadRates ($uni)
{
    $default_rates = array ( 'rate_mk_min'=>1.5, 'rate_mk_max'=>2, 'rate_md_min'=>2, 'rate_md_max'=>3, 'rate_kd_min'=>1, 'rate_kd_max'=>2);
    $server = substr ($uni, 0, 2);
    $uninum = substr ($uni, 2);
    $result = dbquery("SELECT * FROM ".UNITABLE." WHERE num=$uninum AND server='".$server."'");
    $rates = dbarray ($result);
    if ($rates == null) return $default_rates;
    else return $rates;
}

function GetUserAlly ($id)
{
    global $GlobalLang;
    $result = dbquery("SELECT * FROM ".USERTABLE." WHERE id='".$id."'");
    if (dbrows($result) != 0)
    {
        $user = dbarray($result);
        return $user['ally'];
    }
    return "";
}

function SetUserAlly ($id, $ally)
{
    $query = "UPDATE ".USERTABLE." SET ally = '".$ally."' WHERE id='".$id."'";
    dbquery( $query);
}

// Сгенерировать код активации для восстановления пароля. Возвращает код активации.
function GenEmailActivation ($user_id)
{
    global $SecretWord;
    $user = LoadUser ($user_id);
    $ack = sha1 ( $user['login'] . $user['email'] . mt_rand (1,999) . $SecretWord );
    $query = "UPDATE ".USERTABLE." SET emailack = '".$ack."' WHERE id='".$user_id."'";
    dbquery( $query);
    return $ack;
}

//////////////////////////////////////////////////////////////////////////////
// Восстановление пароля.

$LostError = "";

function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
  $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
  mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
}

function GeneratePassword ($length = 8)
{
    $password = "";
    $possible = "0123456789bcdfghjkmnpqrstvwxyz"; 
    $i = 0; 

    while ($i < $length)
    { 
        $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
        if (!strstr($password, $char)) { 
            $password .= $char;
            $i++;
        }
    }
    return $password;
}

// Возвращает TRUE, если пароль успешно изменен, или строку с ошибкой.
function RestorePassword ($user, $uni, $email)
{
    global $local, $loc, $GlobalLang;
    if ( IsUserAdmin ($user, $uni) ) return $local[$GlobalLang]['LostPassE0'];
    if(!preg_match("/^[\d\w-_\.]+@[\d\w-\.]+\.[\w]{2,4}/i",$email)) return $local[$GlobalLang]['LostPassE1'];
    $id = GetUserID ($user, $uni);
    if ($id == 0) return $local[$GlobalLang]['LostPassE2'];
    if ( GetUserEmail ($user, $uni) !== $email) return $local[$GlobalLang]['LostPassE3'];

    $ack = GenEmailActivation ($id);
    mail_utf8 ( "$email", $local[$GlobalLang]['LostPassSubj'], 
        $local[$GlobalLang]['LostPassBody'].":\n".
        $local[$GlobalLang]['Login'].": $user\n".
        $local[$GlobalLang]['Uni'].": $uni\n\n".
        sprintf($local[$GlobalLang]['LostPassBody2'], hostname().scriptname()."?page=genpass&ack=$ack&email=$email"),
        "From: admin <ogamespec@gmail.com>" );
    return "TRUE";
}

function LostPassForm ()
{
    global $SELF, $local, $loc, $GlobalLang, $GlobalSkin;
    global $LostError, $Config;
    echo "<HTML>\n";
    echo "<HEAD>";
    echo "<link rel='stylesheet' type='text/css' href='../css/formate.css'>\n";
    echo "<link rel='stylesheet' type='text/css' href='".$GlobalSkin."/formate.css'>\n";
    echo "<meta http-equiv='content-type' content='text/html; charset=utf-8' />\n";
    echo "<TITLE>".$local[$GlobalLang]['Terminal']."</TITLE>\n";
    echo "</HEAD>\n";
    echo "<BODY>\n";
    echo "<br><br><br>\n";
    echo "<center>\n";
    echo "<form action='".$SELF."' method='POST' name='lost_form'>\n";
    echo "<input type='hidden' name='page' value='lost'>\n";
    echo "<table cellpadding=0 cellspacing=0 border=0 style='background-color: #344566; margin-top:200px; width:250px;' align='center'>\n";
    echo "<tr><td colspan=2 align='center' class='c'>".$local[$GlobalLang]['LostPassHead']."</td></tr>\n";
    echo "<tr><td colspan=2 align='center'>&nbsp; </td></tr>\n";
    echo "<tr><td style='padding-top:5px; padding-left:5px;'>".$local[$GlobalLang]['Uni'].":</td>\n";
    echo "<td style='padding-top:5px;'>\n";
    echo UniSelect ();
    echo "</td></tr>\n";
    echo "<tr ><td style='padding-top:5px; padding-left:5px;'>".$local[$GlobalLang]['Login'].":</td><td style='padding-top:5px;'><input type='text' name='login' size='20' maxlength='20' /></td></tr>\n";
    echo "<tr ><td style='padding-top:5px; padding-left:5px;'>".$local[$GlobalLang]['Email'].":</td><td style='padding-top:5px;'><input type='text' name='email' size='20' maxlength='20' /></td></tr>\n";
    echo "<tr><td colspan=2 align='center'>&nbsp; </td></tr>\n";
    echo "<tr ><td colspan=2><center>&nbsp;<font color=red><b>".$LostError."</b></font></center></td></tr>\n";
    echo "<tr ><td colspan=2 align='center' style='padding-top:5px;'><input class='button' type='submit' name='OK' value='".$local[$GlobalLang]['RegNext']."' /></td></tr>\n";
    echo "<tr><td class='c' colspan=2><a href='trade.php'>".$local[$GlobalLang]['Home']."</a></td></tr>\n";
    echo "</tr></table></form></center>\n";
    echo "</BODY></HTML>\n";
    ob_end_flush ();
    exit ();
}

function LostPassSuccess ($msg, $seconds)
{
    global $SELF, $local, $loc, $GlobalLang, $GlobalSkin;
    echo "<HTML><HEAD><link rel='stylesheet' type='text/css' href='".$GlobalSkin."/formate.css'>\n";
    echo "<meta http-equiv='content-type' content='text/html; charset=utf-8' />\n";
    echo "<meta http-equiv='refresh' content='".$seconds.";url=".$SELF."' />\n";
    echo "<TITLE>".$local[$GlobalLang]['Terminal']."</TITLE>\n";
    echo "</HEAD><BODY>  <center><font size=3><b> <br /><br />\n";
    echo "<font color='#00FF00'>$msg</font><br /><br />\n";
    echo $local[$GlobalLang]['LostPassRedir']."<br/><br/>\n";
    echo "</BODY></HTML>\n";
    ob_end_flush ();
    exit ();
}

//////////////////////////////////////////////////////////////////////////////
// Форма регистрации

$RegError = "";

function RegisterForm ()
{
    global $SELF, $local, $loc, $GlobalLang, $GlobalSkin;
    global $RegError, $Config;
    echo "<HTML>\n";
    echo "<HEAD>";
    echo "<link rel='stylesheet' type='text/css' href='../css/formate.css'>\n";
    echo "<link rel='stylesheet' type='text/css' href='".$GlobalSkin."/formate.css'>\n";
    echo "<meta http-equiv='content-type' content='text/html; charset=utf-8' />\n";
    echo "<TITLE>".$local[$GlobalLang]['Terminal']."</TITLE>\n";
    echo "</HEAD>\n";
    echo "<BODY>\n";
    echo "<br><br><br>\n";
    echo "<center>\n";
    echo "<form action='".$SELF."' method='POST' name='login_form'>\n";
    echo "<input type='hidden' name='page' value='reg'>\n";
    echo "<table cellpadding=0 cellspacing=0 border=0 style='background-color: #344566; margin-top:200px; width:250px;' align='center'>\n";
    echo "<tr><td colspan=2 align='center' class='c'>".$local[$GlobalLang]['RegNew']."</td></tr>\n";
    echo "<tr><td colspan=2 align='center'>&nbsp; ".$local[$GlobalLang]['RegReminder']."</td></tr>\n";
    echo "<tr><td colspan=2 align='center'>&nbsp; </td></tr>\n";
    echo "<tr><td style='padding-top:5px; padding-left:5px;'>".$local[$GlobalLang]['Uni'].":</td>\n";
    echo "<td style='padding-top:5px;'>\n";
    echo UniSelect ();
    echo "<small> *</small>\n";
    echo "</td></tr>\n";
    echo "<tr ><td style='padding-top:5px; padding-left:5px;'>".$local[$GlobalLang]['Login'].":</td><td style='padding-top:5px;'><input type='text' name='login' size='20' maxlength='20' /><small> *</small></td></tr>\n";
    echo "<tr ><td style='padding-top:5px; padding-left:5px;'>".$local[$GlobalLang]['Pass'].":</td><td style='padding-top:5px;'><input type='password' name='pass' size='20' maxlength='20' /><small> *</small></td></tr>\n";
    echo "<tr ><td style='padding-top:5px; padding-left:5px;'>".$local[$GlobalLang]['RegRepeat'].":</td><td style='padding-top:5px;'><input type='password' name='pass2' size='20' maxlength='20' /><small> *</small></td></tr>\n";
    echo "<tr ><td style='padding-top:5px; padding-left:5px;'>".$local[$GlobalLang]['Email'].":</td><td style='padding-top:5px;'><input type='text' name='email' size='20' maxlength='20' /></td></tr>\n";
    echo "<tr><td colspan=2 align='center'>&nbsp; </td></tr>\n";
    echo "<tr ><td colspan=2><center>&nbsp;<font color=red><b>".$RegError."</b></font></center></td></tr>\n";
    echo "<tr ><td colspan=2 align='center' style='padding-top:5px;'><input class='button' type='submit' name='OK' value='".$local[$GlobalLang]['RegNext']."' /></td></tr>\n";
    echo "</tr></table></form></center>\n";
    echo "</BODY></HTML>\n";
    ob_end_flush ();
    exit ();
}

function RegisterSuccess ($seconds, $login, $pass, $uni)
{
    global $SELF, $local, $loc, $GlobalLang, $GlobalSkin;
    echo "<HTML><HEAD><link rel='stylesheet' type='text/css' href='".$GlobalSkin."/formate.css'>\n";
    echo "<meta http-equiv='content-type' content='text/html; charset=utf-8' />\n";
    echo "<meta http-equiv='refresh' content='".$seconds.";url=".$SELF."?page=login&login=".$login."&pass=".$pass."&uni=".$uni."' />\n";
    echo "<TITLE>".$local[$GlobalLang]['Terminal']."</TITLE>\n";
    echo "</HEAD><BODY>  <center><font size=3><b> <br /><br />\n";
    echo "<font color='#00FF00'>".$local[$GlobalLang]['RegOK']."</font><br /><br />\n";
    echo $local[$GlobalLang]['RegRedir']."<br/><br/>\n";
    echo "</BODY></HTML>\n";
    ob_end_flush ();
    exit ();
}

//////////////////////////////////////////////////////////////////////////////
// Вход и выход.

function Login ($login, $uni)
{
    global $Config;
    session_cache_expire (1440);
    session_start ();
    header("Set-Cookie: PHPSESSID=" . session_id() . "; path=/");
    $_SESSION['user_id'] = GetUserID ($login, $uni);
    $_SESSION['login'] = $login;
    $_SESSION['uni'] = $uni;
	$_SESSION['uninum'] = substr ( $uni, 2 );
    $_SESSION['admin'] = IsUserAdmin ($login, $uni);
}

// Удалить номер пользователя из текущей сессии.
function Logout ($page)
{
    global $SELF, $local, $loc, $GlobalLang, $GlobalSkin;
    session_start ();
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-42000, '/');
    }
    session_destroy ();
    if ($page)
    {
        echo "<HTML><HEAD><link rel='stylesheet' type='text/css' href='".$GlobalSkin."/formate.css'>\n";
        echo "<meta http-equiv='content-type' content='text/html; charset=utf-8' />\n";
        echo "<meta http-equiv='refresh' content='2;url=".$SELF."' />\n";
        echo "<TITLE>".$local[$GlobalLang]['Terminal']."</TITLE>\n";
        echo "</HEAD><BODY>  <center><font size=3><b> <br /><br />\n";
        echo "<font color='#00FF00'>".$local[$GlobalLang]['Bye']."</font><br /><br />\n";
        echo "<br/><br/>\n";
        echo "</BODY></HTML>\n";
        ob_end_flush ();
        exit ();
    }
}

//////////////////////////////////////////////////////////////////////////////
// Управление сделками

// Возвращает строку ошибки.
function AddTrader ($trade_id, $g, $s, $p, $moon, $tradewhat, $amount, $tradefor, $rate, $comment, $until)
{
    global $local, $loc, $GlobalLang;
    global $Config;
    $tid = $trade_id;

    // Вырезать инъекции.
    $g = KillInjection ($g);
    $s = KillInjection ($s);
    $p = KillInjection ($p);
    $rate = KillInjection ($rate);
    $amount = KillInjection ($amount);
    $comment = KillInjection ($comment);
    $until = KillInjection ($until);

    // Удалить лишнюю информацию из курса.
    $semi = strpos ($rate, ':');
    if ($semi != FALSE)
    {
        switch ($tradewhat)
        {
            case 0:
                if ($tradefor == 1) $rate = substr ($rate, 0, $semi); break;
                if ($tradefor == 2) $rate = substr ($rate, 0, $semi); break;
                break;
            case 1:
                if ($tradefor == 0) $rate = substr ($rate, $semi+1); break;
                if ($tradefor == 2) $rate = substr ($rate, 0, $semi); break;
                break;
            case 2:
                if ($tradefor == 0) $rate = substr ($rate, $semi+1); break;
                if ($tradefor == 1) $rate = substr ($rate, $semi+1); break;
                break;
        }
    }

    // Проверка неверных условий.
    if ($tradewhat < 0 || $tradewhat > 3) return $local[$GlobalLang]['TErr0'];
    if ($tradefor < 0 || $tradefor > 3) return $local[$GlobalLang]['TErr0'];
    if ($tradewhat == $tradefor && $tradewhat != 3) return $local[$GlobalLang]['TErr0'];
    if ($amount <= 0 || $amount >= 1000000000) return $local[$GlobalLang]['TErr1'];
    if ($rate <= 0)
    {
        if (! ($tradewhat == 3 || $tradefor == 3)) return $local[$GlobalLang]['TErr2'];
    }

    $rates = LoadRates ($_SESSION['uni']);

    // Проверка курсов торговли.
    switch ($tradewhat)
    {
        case 0:
            if ($tradefor == 1 && $rate < $rates['rate_mk_min']) return $local[$GlobalLang]['TErr3'].$rates['rate_mk_min'].":1";
            if ($tradefor == 1 && $rate > $rates['rate_mk_max']) return $local[$GlobalLang]['TErr4'].$rates['rate_mk_max'].":1";
            if ($tradefor == 2 && $rate < $rates['rate_md_min']) return $local[$GlobalLang]['TErr5'].$rates['rate_md_min'].":1";
            if ($tradefor == 2 && $rate > $rates['rate_md_max']) return $local[$GlobalLang]['TErr6'].$rates['rate_md_max'].":1";
            break;
        case 1:
            if ($tradefor == 0 && $rate < $rates['rate_mk_min']) return $local[$GlobalLang]['TErr7'].$rates['rate_mk_min'];
            if ($tradefor == 0 && $rate > $rates['rate_mk_max']) return $local[$GlobalLang]['TErr8'].$rates['rate_mk_max'];
            if ($tradefor == 2 && $rate < $rates['rate_kd_min']) return $local[$GlobalLang]['TErr9'].$rates['rate_kd_min'].":1";
            if ($tradefor == 2 && $rate > $rates['rate_kd_max']) return $local[$GlobalLang]['TErr10'].$rates['rate_kd_max'].":1";
            break;
        case 2:
            if ($tradefor == 0 && $rate < $rates['rate_md_min']) return $local[$GlobalLang]['TErr11'].$rates['rate_md_min'];
            if ($tradefor == 0 && $rate > $rates['rate_md_max']) return $local[$GlobalLang]['TErr12'].$rates['rate_md_max'];
            if ($tradefor == 1 && $rate < $rates['rate_kd_min']) return $local[$GlobalLang]['TErr13'].$rates['rate_kd_min'];
            if ($tradefor == 1 && $rate > $rates['rate_kd_max']) return $local[$GlobalLang]['TErr14'].$rates['rate_kd_max'];
            break;
    }

    // Проверка актуальности заявки.
    if ($until < 0) $until = 0;
    if ($until > 100) $until = 100;

    // Заменить псевдо BB-код (для админов) в комментариях.
    if ($_SESSION['admin'])
    {
        $comment = str_replace ("[red]", "<font color=red>", $comment);
        $comment = str_replace ("[/red]", "</font>", $comment);
    }

    if ($trade_id == 0)
    {
        $Config['trade_base']++;
        $trade['trade_id'] = $Config['trade_base'];
    }
    else $trade['trade_id'] = $trade_id;
    if ($_SESSION['admin'])
    {
        if ($tid == 0) $trade['user_id'] = $_SESSION['user_id'];
        else $trade['user_id'] = GetUserIdFromTradeId ($trade_id);
    }
    else $trade['user_id'] = $_SESSION['user_id'];
    $trade['g'] = $g; $trade['s'] = $s; $trade['p'] = $p; $trade['moon'] = $moon;
    $trade['comment'] = $comment;
    $trade['tradewhat'] = $tradewhat;
    $trade['tradefor'] = $tradefor;
    $trade['amount'] = $amount;
    $trade['rate'] = $rate;
    $trade['hidden'] = 0;
    $trade['date'] = time ();
    if ($until) $trade['until'] = $trade['date'] + $until*24*60*60 + 5*60;
    else $trade['until'] = 0;

    if ($trade_id == 0)
    {
        dbquery( "INSERT INTO ".LISTTABLE." (trade_id) VALUES ('".$trade['trade_id']."')" );
    }
    foreach ($trade as $i=>$entry)
    {
        if ($i != 'trade_id')
        {
            $query = "UPDATE ".LISTTABLE." SET ".$i." = '".$entry."' WHERE trade_id='".$trade['trade_id']."'";
            dbquery( $query);
            if ($debug) echo "$query <br> ";
        }
    }

    if ($trade_id == 0) SaveConfig ();
    return "OK";
}

function RemoveTrader ($trade_id)
{
    global $local, $loc, $GlobalLang;
    // Удалить запись, если текущий user_id совпадает с user_id в заявке.
    $result = dbquery("SELECT * FROM ".LISTTABLE." WHERE trade_id='".$trade_id."'");
    if (dbrows($result) != 0)
    {
        $trade = dbarray($result);
        if ($trade['user_id'] == $_SESSION['user_id'] || $_SESSION['admin'])
        {
            dbquery( "DELETE FROM ".LISTTABLE." WHERE trade_id='".$trade_id."'" );
            return $local[$GlobalLang]['BuyRemoved'];
        }
    }
    return $local[$GlobalLang]['AdminNotice'];
}

// Удалить неактуальные заявки
function DeleteExpiredTrades ()
{
    $now = time ();
    dbquery("DELETE FROM ".LISTTABLE." WHERE $now >= until AND until > 0");
}

//////////////////////////////////////////////////////////////////////////////
// Админка

function DrawAdminRow ($id, $name, $mail, $admin, $bids, $banned)
{
    global $SELF, $local, $loc, $GlobalLang;
    $as = "";
    if ($admin) $as = "A";

    echo "<tr>\n";
    echo " <th>".$id."</th> <th>".$name."</th> <th>".$mail."</th> <th>".$as."</th> <th>".$bids."</th>\n";
    echo " <th> <form action='".$SELF."' method='POST' style='margin: 0px;'>\n";
    echo "    <input type='hidden' name='page' value='wipeuser'>\n";
    echo "    <input type='hidden' name='user_id' value='".$id."'>\n";
    echo "    <input type='submit' value='".$local[$GlobalLang]['AdminWipe']."'></form></th>\n";
    echo " <th> <form action='".$SELF."' method='POST' style='margin: 0px;'>\n";
    echo "    <input type='hidden' name='page' value='banuser'>\n";
    echo "    <input type='hidden' name='user_id' value='".$id."'>\n";
    if ($banned) echo "    <input type='hidden' name='ban' value='0'>\n";
    else echo "    <input type='hidden' name='ban' value='1'>\n";
    if ($banned) echo "    <input type='submit' value='".$local[$GlobalLang]['AdminUnban']."'></form></th>\n";
    else echo "    <input type='submit' value='".$local[$GlobalLang]['AdminBan']."'></form></th>\n";
    echo "</tr>\n\n";
}

function DrawAdminTable ()
{
    global $local, $loc, $GlobalLang;
    echo "<table width='555'>\n";
    echo "<tr height='16px'></tr>\n";
    echo "<tr><td class='c' colspan='7'>".$local[$GlobalLang]['Admin']."</td></tr>\n";
    echo " <tr align=center>\n";
    echo "  <td class='c'>ID</td>\n";
    echo "  <td class='c'><nobr>".$local[$GlobalLang]['AdminLogin']."</nobr></td>\n";
    echo "  <td class='c'>".$local[$GlobalLang]['AdminMail']."</td>\n";
    echo "  <td class='c'>".$local[$GlobalLang]['AdminLevel']."</td>\n";
    echo "  <td class='c'>".$local[$GlobalLang]['AdminBids']."</td>\n";
    echo "  <td class='c'></td>\n";
    echo "  <td class='c'></td></tr>\n\n";

    $result = dbquery("SELECT * FROM ".USERTABLE." WHERE uni='".$_SESSION['uni']."'");
    $rows = dbrows($result);
    while ($rows--) 
    {
        $row = dbarray ($result);
        DrawAdminRow ( $row['id'], $row['login'], $row['email'], $row['admin'],
                       GetUserBids ($row['id']),
                       $row['banned'] );
    }
    echo "</table>\n";
}

//////////////////////////////////////////////////////////////////////////////
// Рисование таблицы торговцев

$SellFormError = "";

function RateTable ()
{
    global $local, $loc, $GlobalLang;
    global $Config;

    $rates = LoadRates ($_SESSION['uni']);
    $minmk = $rates['rate_mk_min'];
    $minmd = $rates['rate_md_min'];
    $minkd = $rates['rate_kd_min'];
    $maxmk = $rates['rate_mk_max'];
    $maxmd = $rates['rate_md_max'];
    $maxkd = $rates['rate_kd_max'];

    $res = "";
    $res .= "<table style='border: 4px solid green;'>";
    $res .= "<tr><td class='c' colspan='7'><center>".$local[$GlobalLang]['SellRates']."</center></td></tr>";
    $res .= "<tr><td> </td><td class='c' colspan='3'>".$local[$GlobalLang]['SellMinRate']."</td> <td class='c' colspan='3'>".$local[$GlobalLang]['SellMaxRate']."</td></tr>";
    $res .= "<tr>";
    $res .= "<th>&nbsp;</th>";
    $res .= "<th><center><a title='".$local[$GlobalLang]['M']."'><img width='21' height='11' src='../images/metall_sm.gif' ></a></center></th>";
    $res .= "<th><center><a title='".$local[$GlobalLang]['K']."'><img width='21' height='11' src='../images/kristall_sm.gif' ></a></center></th>";
    $res .= "<th><center><a title='".$local[$GlobalLang]['D']."'><img width='21' height='11' src='../images/deuterium_sm.gif' ></a></center></th>";
    $res .= "<th><center><a title='".$local[$GlobalLang]['M']."'><img width='21' height='11' src='../images/metall_sm.gif' ></a></center></th>";
    $res .= "<th><center><a title='".$local[$GlobalLang]['K']."'><img width='21' height='11' src='../images/kristall_sm.gif' ></a></center></th>";
    $res .= "<th><center><a title='".$local[$GlobalLang]['D']."'><img width='21' height='11' src='../images/deuterium_sm.gif' ></a></center></th>";
    $res .= "</tr>";
    $res .= "<tr align=center>";
    $res .= "<th><center><a title='".$local[$GlobalLang]['M']."'><img width='21' height='11' src='../images/metall_sm.gif' ></a></center></th>";
    $res .= "<th>-</th><th>".$minmk.":1</th><th>".$minmd.":1</th>";
    $res .= "<th>-</th><th>".$maxmk.":1</th><th>".$maxmd.":1</th>";
    $res .= "</tr>";
    $res .= "<tr align=center>";
    $res .= "<th><center><a title='".$local[$GlobalLang]['K']."'><img width='21' height='11' src='../images/kristall_sm.gif' ></a></center></th>";
    $res .= "<th>1:".$minmk."</th><th>-</th><th>".$minkd.":1</th>";
    $res .= "<th>1:".$maxmk."</th><th>-</th><th>".$maxkd.":1</th>";
    $res .= "</tr>";
    $res .= "<tr align=center>";
    $res .= "<th><center><a title='".$local[$GlobalLang]['D']."'><img width='21' height='11' src='../images/deuterium_sm.gif' ></a></center></th>";
    $res .= "<th>1:".$minmd."</th><th>1:".$minkd."</th><th>-</th>";
    $res .= "<th>1:".$maxmd."</th><th>1:".$maxkd."</th><th>-</th>";
    $res .= "</tr>";
    $res .= "</table>";
    return $res;
}

function DrawHeader ()
{
    global $SELF, $local, $loc, $GlobalSkin, $GlobalLang;
    echo "<HTML><HEAD>\n";
    echo "<link rel='stylesheet' type='text/css' href='../css/formate2.css'>\n";
    echo "<link rel='stylesheet' type='text/css' href='".$GlobalSkin."/formate.css'>\n";
    echo "<meta http-equiv='content-type' content='text/html; charset=utf-8' />\n";
    echo "<script type='text/javascript' src='jscripts/overlib.js'></script>\n";
    echo "<script type='text/javascript' src='jscripts/php.js'></script>\n";
    echo "<script type='text/javascript' src='jscripts/tw-sack.js'></script>\n";
    echo "<TITLE>".$local[$GlobalLang]['Terminal']."</TITLE>\n";
    echo "<script language='JavaScript'>\n";
    echo "var ajax = new sack();\n";
    echo "function createCookie(name,value,days) {\n";
    echo "    if (days) {\n";
    echo "        var date = new Date();\n";
    echo "        date.setTime(date.getTime()+(days*24*60*60*1000));\n";
    echo "        var expires = '; expires='+date.toGMTString();\n";
    echo "    }\n";
    echo "    else var expires = '';\n";
    echo "    document.cookie = name+'='+value+expires+'; path=/';\n";
    echo "}\n";
    echo "function readCookie(name) {\n";
    echo "    var nameEQ = name + '=';\n";
    echo "    var ca = document.cookie.split(';');\n";
    echo "    for(var i=0;i < ca.length;i++) {\n";
    echo "        var c = ca[i];\n";
    echo "        while (c.charAt(0)==' ') c = c.substring(1,c.length);\n";
    echo "        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);\n";
    echo "    }\n";
    echo "    return null;\n";
    echo "}\n";
    echo "function ShowHideParams ()\n";
    echo "{\n";
    echo "    if (document.getElementById('Param').checked)     {\n";
    echo "        createCookie ('trade_hidden_options"."_".$server."', '1', 9999);\n";
    echo "        document.getElementById('options').style.display = 'block';\n";
    echo "        document.getElementById('options2').style.display = 'block';\n";
    echo "        document.getElementById('options3').style.display = 'block';\n";
    echo "        document.getElementById('cg').value = readCookie ('trade_g"."_".$server."');\n";
    echo "        document.getElementById('cs').value = readCookie ('trade_s"."_".$server."');\n";
    echo "        document.getElementById('cp').value = readCookie ('trade_p"."_".$server."');\n";
    echo "        document.getElementById('moon').checked = readCookie ('trade_moon"."_".$server."');\n";
    echo "    }\n";
    echo "    else    {\n";
    echo "        createCookie ('trade_hidden_options"."_".$server."', '0', 9999);\n";
    echo "        document.getElementById('options').style.display = 'none';\n";
    echo "        document.getElementById('options2').style.display = 'none';\n";
    echo "        document.getElementById('options3').style.display = 'none';\n";
    echo "        document.getElementById('cg').value = '';\n";
    echo "        document.getElementById('cs').value = '';\n";
    echo "        document.getElementById('cp').value = '';\n";
    echo "        document.getElementById('moon').checked = false;\n";
    echo "    }\n";
    echo "}\n";
    echo "function onBodyLoad ()\n";
    echo "{\n";
    echo "        if (!EditMode) {\n";
    echo "    document.getElementById('tradewhat').value = readCookie ('trade_what"."_".$server."');\n";
    echo "    document.getElementById('tradefor').value = readCookie ('trade_for"."_".$server."'); }\n";
    echo "    if (readCookie ('trade_hidden_options"."_".$server."') == 1) {\n";
    echo "        document.getElementById('Param').checked = true;\n";
    echo "        document.getElementById('options').style.display = 'block';\n";
    echo "        document.getElementById('options2').style.display = 'block';\n";
    echo "        document.getElementById('options3').style.display = 'block';\n";
    echo "        if (!EditMode) {\n";
    echo "        document.getElementById('cg').value = readCookie ('trade_g"."_".$server."');\n";
    echo "        document.getElementById('cs').value = readCookie ('trade_s"."_".$server."');\n";
    echo "        document.getElementById('cp').value = readCookie ('trade_p"."_".$server."');\n";
    echo "        document.getElementById('moon').checked = readCookie ('trade_moon"."_".$server."'); }\n";
    echo "    }\n";
    echo "    else {   \n";
    echo "        document.getElementById('Param').checked = false;\n";
    echo "        document.getElementById('options').style.display = 'none';\n";
    echo "        document.getElementById('options2').style.display = 'none';\n";
    echo "        document.getElementById('options3').style.display = 'none';\n";
    echo "        document.getElementById('cg').value = '';\n";
    echo "        document.getElementById('cs').value = '';\n";
    echo "        document.getElementById('cp').value = '';\n";
    echo "        document.getElementById('moon').checked = false;\n";
    echo "    }\n";
    echo "}\n";
    echo "function refreshPage ()\n";
    echo "{\n";
    echo "        window.location.href=window.location.href;\n";
    echo "}\n";
    echo "function onResponse ()\n";
    echo "{\n";
    echo "        document.getElementById('sellerror').innerHTML = this.response;\n";
    echo "        setTimeout(\"refreshPage()\", 2000);\n";
    echo "}\n";
    echo "function onSubmit (page)\n";
    echo "{\n";
    echo "        ajax.requestFile = 'trade.php?page='+page;\n";
    echo "        ajax.runResponse = onResponse;\n";
    echo "        ajax.execute = true;\n";
    echo "        ajax.setVar('page', 'sell');\n";
    echo "        ajax.setVar('trade_id', document.getElementById('trade_id').value);\n";
    echo "        ajax.setVar('tradewhat', document.getElementById('tradewhat').value);\n";
    echo "        ajax.setVar('tradefor', document.getElementById('tradefor').value);\n";
    echo "        ajax.setVar('amount', document.getElementById('amount').value);\n";
    echo "        ajax.setVar('rate', document.getElementById('rate').value);\n";
    echo "        ajax.setVar('cg', document.getElementById('cg').value);\n";
    echo "        ajax.setVar('cs', document.getElementById('cs').value);\n";
    echo "        ajax.setVar('cp', document.getElementById('cp').value);\n";
    echo "        ajax.setVar('moon', document.getElementById('moon').checked?'on':'');\n";
    echo "        ajax.setVar('comment', document.getElementById('comment').value);\n";
    echo "        ajax.setVar('until', document.getElementById('until').value);\n";
    echo "        ajax.runAJAX();\n";
    echo "    createCookie ('trade_what"."_".$server."', document.getElementById('tradewhat').value, 9999);\n";
    echo "    createCookie ('trade_for"."_".$server."', document.getElementById('tradefor').value, 9999);\n";
    echo "    if (readCookie ('trade_hidden_options"."_".$server."') == 1) {\n";
    echo "      createCookie ('trade_g"."_".$server."', document.getElementById('cg').value, 9999);\n";
    echo "      createCookie ('trade_s"."_".$server."', document.getElementById('cs').value, 9999);\n";
    echo "      createCookie ('trade_p"."_".$server."', document.getElementById('cp').value, 9999);\n";
    echo "      createCookie ('trade_moon"."_".$server."', document.getElementById('moon').checked, 9999);\n";
    echo "    }\n";
    echo "}\n";
    echo "function onOverRate ()\n";
    echo "{\n";
    echo "    return overlib (\"".RateTable()."\", STICKY, MOUSEOFF, DELAY, 250, CENTER, WIDTH, 100, OFFSETX, 20, OFFSETY, 20);\n";
    echo "}\n";
    echo "function calc ()\n";
    echo "{\n";
    echo "    var tradewhat = document.getElementById('tradewhat').value;\n";
    echo "    var tradefor = document.getElementById('tradefor').value;\n";
    echo "    var amount = document.getElementById('amount').value;\n";
    echo "    var rate = document.getElementById('rate').value;\n";
    echo "    var total = 0;\n";
    echo "\n";
    echo "    if (amount == '' || rate == '') return;\n";
    echo "    rate = php_str_replace (',', '.', rate);\n";
    echo "    var semi = php_strpos (rate, ':');\n";
    echo "    if (semi != false)\n";
    echo "    {\n";
    echo "        if (tradewhat == 0) {\n";
    echo "            if (tradefor == 1) rate = php_substr (rate, 0, semi);\n";
    echo "            if (tradefor == 2) rate = php_substr (rate, 0, semi);\n";
    echo "        }\n";
    echo "        else if (tradewhat == 1)  {\n";
    echo "            if (tradefor == 0) rate = php_substr (rate, semi+1);\n";
    echo "            if (tradefor == 2) rate = php_substr (rate, 0, semi);\n";
    echo "        }\n";
    echo "        else if (tradewhat == 2)  {\n";
    echo "            if (tradefor == 0) rate = php_substr (rate, semi+1);\n";
    echo "            if (tradefor == 1) rate = php_substr (rate, semi+1);\n";
    echo "        }\n";
    echo "    }\n";
    echo "    if (rate <= 0) return;\n";
    echo "    amount = php_str_replace (',', '', amount);\n";
    echo "    amount = php_str_replace ('.', '', amount);\n";
    echo "    amount = php_str_replace (' ', '', amount);\n";
    echo "\n";
    echo "    if (tradewhat == 0)    {\n";
    echo "        if (tradefor == 1) total = amount / rate;\n";
    echo "        if (tradefor == 2) total = amount / rate;\n";
    echo "    }\n";
    echo "    else if (tradewhat == 1)    {\n";
    echo "        if (tradefor == 0) total = amount * rate;\n";
    echo "        if (tradefor == 2) total = amount / rate;\n";
    echo "    }\n";
    echo "    else if (tradewhat == 2)    {\n";
    echo "        if (tradefor == 0) total = amount * rate;\n";
    echo "        if (tradefor == 1) total = amount * rate;\n";
    echo "    }\n";
    echo "    if(total) document.getElementById('res_calc').value = php_number_format(total,'0',',','.');\n";
    echo "    else document.getElementById('res_calc').value = '';\n";
    echo "}\n";
    echo "</script>\n";
    echo "<style>#cargo { border: 1px #415680 solid; }</style>\n";
    echo "</HEAD>\n";
    echo "<BODY onload='onBodyLoad();'>\n";
    echo "<div id='overDiv' style='position:absolute; visibility:hidden; z-index:1000;'></div>\n";
    echo "<table width=100%><tr><td class='c'>&nbsp;</td></tr></table>\n";
    echo "<div id='user' style='position: absolute; top: 5px; left: 10px;'>\n";
    echo $local[$GlobalLang]['MenuUser']." <a title='".$local[$GlobalLang]['Usermenu']."' href='".$SELF."?page=usermenu'><img src='../images/wrench.gif'>".$_SESSION['login']."</a> (".UniverseName($_SESSION['uninum']).")</div>\n";
    echo "<div id='logout' style='position: absolute; top: 5px; right: 15px;'>\n";
    echo "<a href='".$SELF."?page=logout'><font color=red>".$local[$GlobalLang]['Logout']."</font></a></div>\n";
    echo " <center><br><table width='555'>\n\n";
    echo " <tr>  <td class='c' colspan='13'>".$local[$GlobalLang]['Buy']."</td></tr>\n";
    echo " <tr>\n";
    echo "  <td class='c'>".$local[$GlobalLang]['BuyDate']."</td>\n";
    echo "  <td class='c'>".$local[$GlobalLang]['Until']."</td>\n";
    echo "  <td class='c'>".$local[$GlobalLang]['BuyLogin']."</td>\n";
    echo "  <td class='c'>".$local[$GlobalLang]['BuyCoord']."</td>\n";
    echo "  <td class='c'></td>\n";
    echo "  <td class='c'>".$local[$GlobalLang]['BuyTradeWhat']."</td>\n";
    echo "  <td class='c'>".$local[$GlobalLang]['BuyAmount']."</td>\n";
    echo "  <td class='c'>".$local[$GlobalLang]['BuyTradeFor']."</td>\n";
    echo "  <td class='c'>".$local[$GlobalLang]['BuyTotal']."</td>\n";
    echo "  <td class='c'>".$local[$GlobalLang]['BuyRate']."</td>\n";
    echo "  <td class='c'><nobr>".$local[$GlobalLang]['BuyComment']."</nobr></td>\n";
    echo "  <td class='c'></td>\n";
    echo " </tr>\n\n";
}

function DrawFooter ()
{
    echo "</BODY></HTML>\n";
}

function CargoText ($amount)
{
    global $local, $GlobalLang;
    $res  = "return overlib (\"";
    $res .= "<table id=cargo width=160>";
    $res .= "<tr><td><nobr>".$local[$GlobalLang]['Cargo202'].nicenum(ceil($amount/5000))."</nobr></td></tr>";
    $res .= "<tr><td><nobr>".$local[$GlobalLang]['Cargo203'].nicenum(ceil($amount/25000))."</nobr></td></tr>";
//    $res .= "<tr><td><nobr>".$local[$GlobalLang]['Cargo208'].nicenum(ceil($amount/7500))."</nobr></td></tr>";
    $res .= "<tr><td><nobr>".$local[$GlobalLang]['Cargo209'].nicenum(ceil($amount/20000))."</nobr></td></tr>";
    $res .= "</table>";
    $res .= "\", WIDTH, 160);";
    return $res;
}

function DrawRow ($user, $ally, $g, $s, $p, $moon, $tradewhat, $amount, $tradefor, $rate, $comment, $trade_id, $date, $until)
{
    global $SELF, $local, $loc, $GlobalLang;
    $hidecoords = false;
    if ($g == 0 || $s == 0 || $p == 0 ) $hidecoords = true;

    $w1 = $w2 = 21; $h1 = $h2 = 11;
    switch ($tradewhat)
    {
        case 0: $whatimg = "../images/metall_sm.gif"; $wt = $local[$GlobalLang]['M']; break;
        case 1: $whatimg = "../images/kristall_sm.gif"; $wt = $local[$GlobalLang]['K']; break;
        case 2: $whatimg = "../images/deuterium_sm.gif"; $wt = $local[$GlobalLang]['D']; break;
        case 3: $whatimg = "../images/s_mond.jpg"; $wt = $local[$GlobalLang]['MoonShot']; $w1 = $h1 = 16; break;
    }
    switch ($tradefor)
    {
        case 0: $forimg = "../images/metall_sm.gif"; $ft = $local[$GlobalLang]['M']; break;
        case 1: $forimg = "../images/kristall_sm.gif"; $ft = $local[$GlobalLang]['K']; break;
        case 2: $forimg = "../images/deuterium_sm.gif"; $ft = $local[$GlobalLang]['D']; break;
        case 3: $forimg = "../images/s_mond.jpg"; $ft = $local[$GlobalLang]['MoonShot']; $w2 = $h2 = 16; break;
    }

    switch ($tradewhat)
    {
        case 0:
            if ($tradefor == 1) { $r = $rate . ":1"; $total = $amount / $rate; }
            if ($tradefor == 2) { $r = $rate . ":1"; $total = $amount / $rate; }
            if ($tradefor == 3) { $r = '-'; $total = 1; }
            break;
        case 1:
            if ($tradefor == 0) { $r = "1:" . $rate; $total = $amount * $rate; }
            if ($tradefor == 2) { $r = $rate . ":1"; $total = $amount / $rate; }
            if ($tradefor == 3) { $r = '-'; $total = 1; }
            break;
        case 2:
            if ($tradefor == 0) { $r = "1:" . $rate; $total = $amount * $rate; }
            if ($tradefor == 1) { $r = "1:" . $rate; $total = $amount * $rate; }
            if ($tradefor == 3) { $r = '-'; $total = 1; }
            break;
        case 3:
            $r = '-';
            $total = $amount;
            $amount = 1;
            break;
    }

    echo "<tr>\n";

    $now = getdate ();
    $old = getdate ($date);
    if ($date == NULL) echo " <th> ".$local[$GlobalLang]['BuyDateOld']." </th>\n";
    else
    {
        if ($now["year"] == $old["year"])
        {
            if ($now["yday"] == $old["yday"]) echo " <th> ".$local[$GlobalLang]['BuyDateToday']." </th>\n";
            else if ($now["yday"] == ($old["yday"] + 1)) echo " <th> ".$local[$GlobalLang]['BuyDateYesterday']." </th>\n";
            else echo " <th> ".date($local[$GlobalLang]['BuyDateFmt'], $date)." </th>\n";
        }
        else echo " <th> ".date($local[$GlobalLang]['BuyDateFmt'], $date)." </th>\n";
    }

    // Актуальность.
    $now = time ();
    $diff = $until - $now;
    if ($diff < 0) $diff = 0;
    if ($until == "" || $until == 0) {
        $untilstr = $local[$GlobalLang]['UntilInf'];
    }
    else {
        $minutes = floor ($diff / 60);
        $hours = floor ($diff / (60*60));
        $days = floor ($diff / (60*60*24));
        if ($minutes < 3) $untilstr = $local[$GlobalLang]['UntilNow'];
        else if ($minutes < 60) $untilstr = sprintf ($local[$GlobalLang]['UntilMinutes'], $minutes );
        else if ($hours < 24) $untilstr = sprintf ($local[$GlobalLang]['UntilHours'], $hours );
        else $untilstr = sprintf ( $local[$GlobalLang]['UntilDays'], $days );
    }
    echo " <th>$untilstr</th>\n";

    if ($ally) $ally = " [".$ally."]";

    echo " <th> <nobr>".$user.$ally." <nobr> </th>\n";
    if ($hidecoords) echo " <th> <a title='".$local[$GlobalLang]['BuyHidden']."'>-</a> </th>\n";
    else echo " <th> [".$g.":".$s.":".$p."] </th>\n";
    if ($moon) echo " <th><a title='".$local[$GlobalLang]['BuyMoon']."'><img width='16' height='16' src='../images/s_mond.jpg' border='0'  /></a></th>\n";
    else echo " <th></th>\n";
    echo " <th><a title='".$wt."'><img width='$w1' height='$h1' src='".$whatimg."' border='0'  /></a></th>\n";
    if ($tradewhat == 3) echo " <th> ".nicenum($amount)."</th>\n";
    else echo " <th> <a style='text-decoration: none;' onmouseover='".CargoText($amount)."' onmouseout='return nd();'>".nicenum($amount)."</a> </th>\n";
    echo " <th><a title='".$ft."'><img width='$w2' height='$h2' src='".$forimg."' border='0'  /></a></th>\n";
    if ($tradefor == 3) echo " <th> ".nicenum($total)."</th>\n";
    else echo " <th> <a style='text-decoration: none;' onmouseover='".CargoText($total)."' onmouseout='return nd();'>".nicenum($total)."</a> </th>\n";
    echo " <th> ".$r." </th>\n";
    echo " <th> ".$comment." </th>\n";
    if ($trade_id != 0)
    {
        echo " <th><table style='margin:0px;'><tr><td>\n";
        echo " <form action='".$SELF."' style='margin:0px;' method='POST'>\n";
        echo " <input type='hidden' name='page' value='edit'>\n";
        echo " <input type='hidden' name='trade_id' value='".$trade_id."'>\n";
        echo " <input type='submit' value='".$local[$GlobalLang]['BuyEdit']."'></form>\n";
        echo " </td><td><form action='".$SELF."' style='margin:0px;' method='POST'>\n";
        echo " <input type='hidden' name='page' value='remove'>\n";
        echo " <input type='hidden' name='trade_id' value='".$trade_id."'>\n";
        echo " <input type='submit' value='X'></form>\n";
        echo " </td></tr></table></th>\n";
    }
    else "<th> </th>\n";
    echo "</tr>\n";
}

function DrawSellForm ($tid)
{
    global $SELF, $local, $loc, $GlobalLang;
    global $SellFormError;

    $selfor[0] = $selfor[1] = $selfor[2] = '';
    $selwhat[0] = $selwhat[1] = $selwhat[2] = '';
    $amount = '';
    $rate = '';
    $g = $s = $p = '';
    $comment = '';
    $moon = '';

    if ($tid)       // Копирование значений заявки в поля формы.
    {
        $result = dbquery("SELECT * FROM ".LISTTABLE." WHERE trade_id='".$tid."'");
        if (dbrows($result) != 0)
        {
            $trade = dbarray($result);
            //print_r ($trade);
        }
        $selwhat[$trade['tradefor']] = 'SELECTED';
        $selfor[$trade['tradewhat']] = 'SELECTED';
        $amount = nicenum ($trade['amount']);
        $rate = $trade['rate'];
        $g = $trade['g']; $s = $trade['s']; $p = $trade['p'];
        $comment = $trade['comment'];
        $comment = str_replace ("<font color=red>", "[red]", $comment);
        $comment = str_replace ("</font>", "[/red]", $comment);
        if ($trade['moon'] == 1) $moon = 'CHECKED';
        setcookie ('trade_hidden_options'."_".$server, 1);
    }

    echo "</table><table><tr><td class='c' colspan='4'>".$local[$GlobalLang]['Sell']."</td></tr>\n";
    echo "<tr><th colspan='4'>\n";
    echo "<div id='SellForm'>\n";
    if ($tid != 0) echo "    <script>var EditMode=1;</script><input type='hidden' name='trade_id' value='".$tid."' id='trade_id'>\n";
    else echo "    <script>var EditMode=0;</script><input type='hidden' name='trade_id' value='0' id='trade_id'>\n";
    echo "<table>\n";
    echo "    <tr><td>".$local[$GlobalLang]['SellTradeWhat'].": &nbsp;\n";
    echo "        <select name='tradewhat' id='tradewhat' onchange='calc();'> \n";
    echo "    <option value='0' ".$selfor[0].">".$local[$GlobalLang]['M']."</option>\n";
    echo "    <option value='1' ".$selfor[1].">".$local[$GlobalLang]['K']."</option>\n";
    echo "    <option value='2' ".$selfor[2].">".$local[$GlobalLang]['D']."</option>\n";
    echo "    <option value='3' ".$selfor[3].">".$local[$GlobalLang]['MoonShot']."</option>\n";
    echo "        </select></td>\n";
    echo "        <td>".$local[$GlobalLang]['SellAmount'].": <input name='amount' id='amount' value='".$amount."' onchange='calc();'> </td>\n";
    echo "    </tr>\n";
    echo "    <tr><td>".$local[$GlobalLang]['SellTradeFor'].": \n";
    echo "        <select name='tradefor' id='tradefor' onchange='calc();'> \n";
    echo "    <option value='0' ".$selwhat[0].">".$local[$GlobalLang]['M']."</option>\n";
    echo "    <option value='1' ".$selwhat[1].">".$local[$GlobalLang]['K']."</option>\n";
    echo "    <option value='2' ".$selwhat[2].">".$local[$GlobalLang]['D']."</option>\n";
    echo "    <option value='3' ".$selwhat[3].">".$local[$GlobalLang]['MoonShot']."</option>\n";
    echo "        </select></td>\n";
    echo "        <td><a onmouseover='return onOverRate();' onmouseout='return nd();'>".$local[$GlobalLang]['SellRate']."</a>:\n";
    echo "        <input size='6' name='rate' id='rate' value='".$rate."' onchange='calc();'> &nbsp; <input size='15' name='res_calc' id='res_calc' style='background-color: transparent; border: none;'> </td>\n";
    echo "    </tr>\n";
    echo "    <tr><td>&nbsp;</td></tr>\n";
    echo "    <tr><td><input name='Param' id='Param' type='checkbox' onclick='ShowHideParams();'> ".$local[$GlobalLang]['SellParam']."</td></tr>\n";
    echo "    <tr><td><div id='options'>".$local[$GlobalLang]['SellCoord'].":\n";
    echo "        <input size='1' name='cg' id='cg' value='".$g."'>:<input size='1' name='cs' id='cs' value='".$s."'>:<input size='1' name='cp' id='cp' value='".$p."'>\n";
    echo "        <input type='checkbox' name='moon' id='moon' ".$moon."> ".$local[$GlobalLang]['SellMoon']." </div></td>\n";
    echo "    </tr>\n";
    echo "    <tr>\n";
    echo "        <td> <div id='options2'> ".$local[$GlobalLang]['SellComment'].":\n";
    echo "        <input name='comment' id='comment' value='".$comment."'> <div> </td>\n";

    echo "        <td> <div id='options3'>  ".$local[$GlobalLang]['Until'].":\n";
    echo "        <select name='until' id='until'> \n";
    echo "            <option value='1'>".sprintf($local[$GlobalLang]['UntilDays'], 1)."</option> \n";
    echo "            <option value='3'>".sprintf($local[$GlobalLang]['UntilDays'], 3)."</option> \n";
    echo "            <option value='5'>".sprintf($local[$GlobalLang]['UntilDays'], 5)."</option> \n";
    echo "            <option value='7' selected>".sprintf($local[$GlobalLang]['UntilDays'], 7)."</option> \n";
    echo "            <option value='14'>".sprintf($local[$GlobalLang]['UntilDays'], 14)."</option> \n";
    echo "            <option value='28'>".sprintf($local[$GlobalLang]['UntilDays'], 28)."</option> \n";
    echo "            <option value='100'>".sprintf($local[$GlobalLang]['UntilDays'], 100)."</option> \n";
    echo "        </select> </div> </td> \n";

    echo "    </tr>\n";
    echo "    <tr><td>\n";
    echo "    <div id='sellerror'></div>\n";
    if ($SellFormError === "OK") echo "    &nbsp; &nbsp; <font color=lime>".$local[$GlobalLang]['SellOK']."</font>";
    else echo "    &nbsp; &nbsp; <font color=red>".$SellFormError."</font>";
    echo "    </td><tr>\n";
    echo "    <tr>\n";
    echo "    <td colspan=4> <center><input type=button value='".$local[$GlobalLang]['SellButton']."' onclick='onSubmit(\"sell\");'></center></td>\n";
    echo "    </tr>\n";
    echo "</table></div></th></tr></table>\n";
}

function DrawTable ($tid)
{
    global $SELF, $local, $loc, $GlobalSkin, $GlobalLang;
    global $SellFormError;

    // Заменить глобальные установки скина и языка для текущего пользователя.
    $s = GetUserSkin ();
    $l = GetUserLang ();
    if ($s != "") $GlobalSkin = $s;
    if ($l != "") $GlobalLang = $l;

    DrawHeader ();
    DeleteExpiredTrades ();

//    $_SESSION['user_id'] = GetUserID ($login, $uni);
//    $_SESSION['login'] = $login;
//    $_SESSION['uni'] = $uni;

    // SELECT * FROM ListTable, UserTable WHERE UserTable.uni = '5' and ListTable.user_id = UserTable.id;
    $query = "SELECT ".LISTTABLE.".* FROM " . LISTTABLE . ", " . USERTABLE . 
             " WHERE " . USERTABLE . ".uni = '".$_SESSION['uni']."'".
             " AND ".LISTTABLE.".user_id = ".USERTABLE.".id".
             ";";
    $result = dbquery ($query);
    //echo $query;
    $rows = dbrows($result);
    while ($rows--) 
    {
        $row = dbarray ($result);
        $login = GetUserNameById ($row['user_id'], $_SESSION['uni']);
        if ($row['user_id'] == $_SESSION['user_id'] || $_SESSION['admin']) $trade_id = $row['trade_id'];
        else $trade_id = 0;

        DrawRow ( $login, GetUserAlly ($row['user_id']), 
                  $row['g'], $row['s'], $row['p'], $row['moon'],
                  $row['tradewhat'], $row['amount'],
                  $row['tradefor'], $row['rate'], $row['comment'], $trade_id, $row['date'], $row['until'] );
    }

    // Кнопка Обновить.
    echo "<tr><td colspan=12 style='background-color: transparent;'><center><form action='".$SELF."' method='GET'><input type='submit' value='".$local[$GlobalLang]['Refresh']."'></form></center></td></tr>";

    if ($_SESSION['user_id'] != 1) DrawSellForm ($tid);
    if ($_SESSION['admin']) DrawAdminTable ();
    DrawFooter ();

    ob_end_flush ();
    exit ();
}

//////////////////////////////////////////////////////////////////////////////
// Меню пользователя.

$Skin = array ();

$Skin[0]['name'] = $local[$GlobalLang]['Terminal'];
$Skin[0]['path'] = "http://ogamespec.com/evolution/";
$Skin[1]['name'] = "Vista Aero";
$Skin[1]['path'] = "http://ogamespec.com/skin/Vista_Aero/";
$Skin[2]['name'] = "Phyve Lite";
$Skin[2]['path'] = "http://www.freewebs.com/phyve/ogamelite/";
$Skin[3]['name'] = "Phyve Animated";
$Skin[3]['path'] = "http://www.freewebs.com/phyve/ogame/";
$Skin[4]['name'] = "Born To Recycle";
$Skin[4]['path'] = "http://ogamespec.com/skin/BTRv77/";
$Skin[5]['name'] = "Neotux-2";
$Skin[5]['path'] = "http://membres.lycos.fr/sheenaha/Neotux-2-maj-light-1440-ai/";
$Skin[6]['name'] = "FirstSoul-V2";
$Skin[6]['path'] = "http://www.sebe.us/ogame/FirstSoul-V2/";
$Skin[7]['name'] = "Lunaris";
$Skin[7]['path'] = "http://ogamespec.com/skin/lunaris/";
$Skin[8]['name'] = "spskin.v.1.0";
$Skin[8]['path'] = "http://uhate.jino.ru/skin/spskin/";
$Skin[9]['name'] = "uLtravioLet";
$Skin[9]['path'] = "http://ogamespec.com/skin/uV/";
$Skin[10]['name'] = "Venator 2.0";
$Skin[10]['path'] = "http://freenet-homepage.de/Veantor_rip/skin/";
$Skin[11]['name'] = "Manger";
$Skin[11]['path'] = "http://ogamespec.com/skin/manger2/";
$Skin[12]['name'] = "Aquila";
$Skin[12]['path'] = "http://lyistra.net/library/ogame/skins/aquila/";
$Skin[13]['name'] = "Cataclysm";
$Skin[13]['path'] = "http://ogamespec.com/skin/Cataclysm/";
$Skin[14]['name'] = "Planets";
$Skin[14]['path'] = "http://ogamespec.com/skin/Planets1024x768/";
$Skin[15]['name'] = "Reloaded";
$Skin[15]['path'] = "http://ogamespec.com/skin/reloaded/";
$Skin[16]['name'] = "lightgold";
$Skin[16]['path'] = "http://ogamespec.com/skin/lightgold/";
$Skin[17]['name'] = "Spire";
$Skin[17]['path'] = "http://terminator01.funpic.de/spireogame/";
$Skin[18]['name'] = "Epic Blue";
$Skin[18]['path'] = "http://ogamespec.com/skin/epicblue/";

$Skins = 19;

function DrawSkinSelect ($sel)
{
    global $Skin, $Skins;
    $res = "";
    for ($i=0; $i<$Skins; $i++)
    {
        $res .= "<option value='".$Skin[$i]['path']."' ";
        if ($Skin[$i]['path'] === $sel) $res .= "selected";
        $res .= " >".$Skin[$i]['name']."</option>\n";
    }
    return $res;
}

function DrawLangSelect ($sel)
{
    global $lang, $gameservers;
    $res = "";
    foreach ($lang as $i => $value )
    {
        //$res .= "<option value='$i' class='imagebacked' style=\"background:url(../images/mmoflags.png) no-repeat scroll 0 0 transparent; padding-left:23px; height:14px !important; background-position: left ".$gameservers[$i]['flag']."px !important;\" ";
        $res .= "<option value='$i'  ";
        if ($i === $sel) $res .= "selected";
        $res .= " >" . $lang[$i]."</option>\n";
    }
    return $res;
}

function DrawUserMenu ()
{
    global $server, $SELF, $local, $loc, $lang, $GlobalLang, $GlobalSkin;

    // Заменить глобальные установки скина и языка для текущего пользователя.
    $s = GetUserSkin ();
    $l = GetUserLang ();
    if ($s != "") $GlobalSkin = $s;
    if ($l != "") $GlobalLang = $l;

    echo "<HTML><HEAD>\n";
    echo "<link rel='stylesheet' type='text/css' href='../css/formate2.css'>\n";
    echo "<link rel='stylesheet' type='text/css' href='".$GlobalSkin."/formate.css'>\n";
    echo "<TITLE>".$local[$GlobalLang]['Terminal']."</TITLE>\n";
    echo "<script language='JavaScript'>\n";
    echo "function createCookie(name,value,days) {\n";
    echo "    if (days) {\n";
    echo "        var date = new Date();\n";
    echo "        date.setTime(date.getTime()+(days*24*60*60*1000));\n";
    echo "        var expires = '; expires='+date.toGMTString();\n";
    echo "    }\n";
    echo "    else var expires = '';\n";
    echo "    document.cookie = name+'='+value+expires+'; path=/';\n";
    echo "}\n";
    echo "function readCookie(name) {\n";
    echo "    var nameEQ = name + '=';\n";
    echo "    var ca = document.cookie.split(';');\n";
    echo "    for(var i=0;i < ca.length;i++) {\n";
    echo "        var c = ca[i];\n";
    echo "        while (c.charAt(0)==' ') c = c.substring(1,c.length);\n";
    echo "        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);\n";
    echo "    }\n";
    echo "    return null;\n";
    echo "}\n";
    echo "function onBodyLoad ()\n";
    echo "{\n";
    echo "    lang = readCookie ('lang_".$server."');\n";
    echo "    document.user_form.lang.value = lang;\n";
    echo "    document.user_form.preset.value = '".GetUserSkin()."';\n";
    echo "}\n";
    echo "function onLangChange()\n";
    echo "{\n";
    echo "}\n";
    echo "function onPresetChange()\n";
    echo "{\n";
    echo "    document.user_form.skin.value = document.user_form.preset.value;\n";
    echo "}\n";
    echo "</script>\n";
    echo "</HEAD>\n";
    echo "<BODY onload='onBodyLoad();'>\n";
    echo "<table width=100%><tr><td class='c'>&nbsp;</td></tr></table>\n";
    echo "<div id='user' style='position: absolute; top: 5px; left: 10px;'>\n";
    echo $local[$GlobalLang]['Usermenu']." <b>".$_SESSION['login']."</b> (".UniverseName($_SESSION['uninum']).")</div>\n";
    echo "<div id='logout' style='position: absolute; top: 5px; right: 15px;'>\n";
    echo "<a href='".$SELF."'><b>".$local[$GlobalLang]['UserBack']."</b></a></div>\n";
    echo "<center><form action=".$SELF." method='POST' name='user_form'>\n";
    echo "<input type='hidden' name='page' value='save'>\n";
    echo "<table cellpadding='0' cellspacing='0' border='0' style='background-color: #344566; margin-top:50px; width:300px;' align='center'>\n";
    echo "<tr><td colspan='3' align='center' class='c'>".$local[$GlobalLang]['Usermenu']."</td></tr>\n";
    echo "<tr><td colspan='3' align='center'>&nbsp;</td></tr>\n";
    echo "<tr ><td style='padding-top:5px; padding-left:5px;'>".$local[$GlobalLang]['UserChangeName'].":</td>\n";
    echo "<td style='padding-top:5px;'><input type='text' name='login' size='28' maxlength='20' /></td></tr>\n";
    echo "<tr ><td style='padding-top:5px; padding-left:5px;'>".$local[$GlobalLang]['Ally'].":</td>\n";
    echo "<td style='padding-top:5px;'><input type='text' name='ally' size='28' maxlength='20' value=\"".GetUserAlly($_SESSION['user_id'])."\"/></td></tr>\n";
    echo "<tr ><td style='padding-top:5px; padding-left:5px;'>".$local[$GlobalLang]['UserSkin'].":</td>\n";
    echo "<td style='padding-top:5px;'><input type='text' name='skin' size='28' maxlength='128' value='".GetUserSkin()."'/></td></tr>\n";
    echo "<tr ><td style='padding-top:5px; padding-left:5px;'></td>\n";
    echo "<td style='padding-top:5px;'> <select onchange='onPresetChange();' name='preset'>".DrawSkinSelect($GlobalSkin)."</select></td></tr>\n";
    echo "<tr ><td style='padding-top:5px; padding-left:5px;'>".$local[$GlobalLang]['Lang'].":</td>\n";
    echo "<td style='padding-top:5px;'><select name='language' onchange='onLangChange();'>\n";
    echo DrawLangSelect ($GlobalLang);
    echo "</select></td></tr>\n";
    echo "<tr ><td colspan='3' align='center'>&nbsp;</td></tr>\n";
    echo "<tr ><td colspan='3' align='center' style='padding-top:5px;'><input class='button' type='submit' value='".$local[$GlobalLang]['UserApply']."' /></td></tr>\n";
    echo "</table></form></center></BODY></HTML>\n";
    ob_end_flush ();
    exit ();
}

//////////////////////////////////////////////////////////////////////////////
// Заявки за сегодня.

function selected ($i, $v)
{
    if ( $i == $v ) return "selected";
    else return "";
}

function RecentApplications ()
{
	global $local, $loc, $gameservers, $GlobalLang;
	echo "<table style='width: 700px;'>\n";

    DeleteExpiredTrades ();    // удалить просроченные заявки.

    $filter = "";
    if ( key_exists ( 'sort', $_GET ) ) {
        if ( $_GET['sort'] == 1 ) $filter = "AND tradewhat = 0";
        else if ( $_GET['sort'] == 2 ) $filter = "AND tradewhat = 1";
        else if ( $_GET['sort'] == 3 ) $filter = "AND tradewhat = 2";
        else if ( $_GET['sort'] == 4 ) $filter = "AND tradewhat = 3";
        else if ( $_GET['sort'] == 5 ) $filter = "AND tradefor = 0";
        else if ( $_GET['sort'] == 6 ) $filter = "AND tradefor = 1";
        else if ( $_GET['sort'] == 7 ) $filter = "AND tradefor = 2";
        else if ( $_GET['sort'] == 8 ) $filter = "AND tradefor = 3";
    }

	echo "<tr><td colspan=7 class=c>".$local[$GlobalLang]['LastApps']."</td>\n";
    echo "<td class=c>".$local[$GlobalLang]['Filter'].": <select id='sortby' onchange='onSortChange();'>\n";
    echo "<option value='0' ".selected(0, $_GET['sort']).">".$local[$GlobalLang]['Filter0']."</option>\n";
    echo "<option value='1' ".selected(1, $_GET['sort']).">".$local[$GlobalLang]['Filter1']."</option>\n";
    echo "<option value='2' ".selected(2, $_GET['sort']).">".$local[$GlobalLang]['Filter2']."</option>\n";
    echo "<option value='3' ".selected(3, $_GET['sort']).">".$local[$GlobalLang]['Filter3']."</option>\n";
    echo "<option value='4' ".selected(4, $_GET['sort']).">".$local[$GlobalLang]['Filter4']."</option>\n";
    echo "<option value='5' ".selected(5, $_GET['sort']).">".$local[$GlobalLang]['Filter5']."</option>\n";
    echo "<option value='6' ".selected(6, $_GET['sort']).">".$local[$GlobalLang]['Filter6']."</option>\n";
    echo "<option value='7' ".selected(7, $_GET['sort']).">".$local[$GlobalLang]['Filter7']."</option>\n";
    echo "<option value='8' ".selected(8, $_GET['sort']).">".$local[$GlobalLang]['Filter8']."</option>\n";
    echo "</select></td>";
    echo "</tr>\n";
	echo "<tr><th>".$local[$GlobalLang]['BuyDate']."</th><th>".$local[$GlobalLang]['Until']."</th><th>".$local[$GlobalLang]['Uni']."</th><th>".$local[$GlobalLang]['BuyLogin']."</th><th>".$local[$GlobalLang]['BuyTradeWhat']."</th><th>".$local[$GlobalLang]['BuyTradeFor']."</th><th>".$local[$GlobalLang]['BuyRate']."</th><th>".$local[$GlobalLang]['BuyComment']."</th></tr>\n";

    $limit = 20;
	$from = time () - 365*24*60*60;
	$query = "SELECT * FROM ".LISTTABLE." WHERE date >= $from $filter ORDER BY date DESC LIMIT 3000";
	$result = dbquery ($query);

	$rows = dbrows ($result);
    while ($rows-- && $limit--)
	{
		$app = dbarray ($result);
 
        $user = LoadUser ( $app['user_id'] );
        $server = substr ($user['uni'], 0, 2);
        $uninum = substr ($user['uni'], 2);

        if ( $server !== $loc ) continue;

		$tradewhat = $app['tradewhat'];
		$tradefor = $app['tradefor'];
		$amount = $app['amount'];
		$rate = $app['rate'];

    	$w1 = $w2 = 21; $h1 = $h2 = 11;
    	switch ($tradewhat)
    	{
			case 0: $whatimg = "../images/metall_sm.gif"; $wt = $local[$GlobalLang]['M']; break;
        	case 1: $whatimg = "../images/kristall_sm.gif"; $wt = $local[$GlobalLang]['K']; break;
        	case 2: $whatimg = "../images/deuterium_sm.gif"; $wt = $local[$GlobalLang]['D']; break;
        	case 3: $whatimg = "../images/s_mond.jpg"; $wt = $local[$GlobalLang]['MoonShot']; $w1 = $h1 = 16; break;
    	}
    	switch ($tradefor)
    	{
     		case 0: $forimg = "../images/metall_sm.gif"; $ft = $local[$GlobalLang]['M']; break;
        	case 1: $forimg = "../images/kristall_sm.gif"; $ft = $local[$GlobalLang]['K']; break;
        	case 2: $forimg = "../images/deuterium_sm.gif"; $ft = $local[$GlobalLang]['D']; break;
        	case 3: $forimg = "../images/s_mond.jpg"; $ft = $local[$GlobalLang]['MoonShot']; $w2 = $h2 = 16; break;
    	}

    	switch ($tradewhat)
    	{
        	case 0:
            	if ($tradefor == 1) { $r = $rate . ":1"; $total = $amount / $rate; }
            	if ($tradefor == 2) { $r = $rate . ":1"; $total = $amount / $rate; }
            	if ($tradefor == 3) { $r = '-'; $total = 1; }
            	break;
        	case 1:
            	if ($tradefor == 0) { $r = "1:" . $rate; $total = $amount * $rate; }
            	if ($tradefor == 2) { $r = $rate . ":1"; $total = $amount / $rate; }
            	if ($tradefor == 3) { $r = '-'; $total = 1; }
            	break;
        	case 2:
            	if ($tradefor == 0) { $r = "1:" . $rate; $total = $amount * $rate; }
            	if ($tradefor == 1) { $r = "1:" . $rate; $total = $amount * $rate; }
            	if ($tradefor == 3) { $r = '-'; $total = 1; }
            	break;
        	case 3:
            	$r = '-';
            	$total = $amount;
            	$amount = 1;
            	break;
    	}

        $flag = "<a onclick='1=1;' title='".$local[$GlobalLang][$server]."' href='#' style='text-decoration: none; background:url(\"../images/mmoflags.png\") no-repeat scroll 0 0 transparent; padding-left:23px; height:14px !important; background-position: left ".$gameservers[$server]['flag']."px !important;'>&nbsp;</a>";

        $now = time ();
        $diff = $now - $app['date'];
        $minutes = floor ($diff / 60);
        $hours = floor ($diff / (60*60));
        $days = floor ($diff / (60*60*24));
        if ($minutes < 3) $tim = $local[$GlobalLang]['LastAppsNow'];
        else if ($minutes < 60) $tim = sprintf ($local[$GlobalLang]['LastAppsMinutesAgo'], $minutes );
        else if ($hours < 24) $tim = sprintf ($local[$GlobalLang]['LastAppsHoursAgo'], $hours );
        else $tim = sprintf ( $local[$GlobalLang]['LastAppsDaysAgo'], $days );

        // Актуальность.
        $until = $app['until'];
        $diff = $until - $now;
        if ($until == "" || $until == 0) {
            $untilstr = $local[$GlobalLang]['UntilInf'];
        }
        else {
            $minutes = floor ($diff / 60);
            $hours = floor ($diff / (60*60));
            $days = floor ($diff / (60*60*24));
            if ($minutes < 3) $untilstr = $local[$GlobalLang]['UntilNow'];
            else if ($minutes < 60) $untilstr = sprintf ($local[$GlobalLang]['UntilMinutes'], $minutes );
            else if ($hours < 24) $untilstr = sprintf ($local[$GlobalLang]['UntilHours'], $hours );
            else $untilstr = sprintf ( $local[$GlobalLang]['UntilDays'], $days );
        }

        $ally = $user['ally'];
        if ($ally) $ally = " [".$ally."]";

		echo "<tr>";
		echo "<td><nobr>$tim</nobr></td>";
		echo "<td><nobr>$untilstr</nobr></td>";
		echo "<td>".$flag."<nobr>".UniverseName($uninum)."</nobr></td>";
		echo "<td><nobr>".$user['login'].$ally."</nobr></td>";
		echo "<td><nobr><a title='".$wt."'><img width='$w1' height='$h1' src='".$whatimg."' border='0'  /></a>";
    	if ($tradewhat == 3) echo "<b> ".nicenum($amount)."</b></nobr></td>\n";
    	else echo " <a style='text-decoration: none;' onmouseover='".CargoText($amount)."' onmouseout='return nd();'>".nicenum($amount)."</a> </nobr></td>\n";
		echo "<td><nobr><a title='".$ft."'><img width='$w2' height='$h2' src='".$forimg."' border='0'  /></a>";
    	if ($tradefor == 3) echo "<b> ".nicenum($total)."</b></nobr></td>\n";
    	else echo " <a style='text-decoration: none;' onmouseover='".CargoText($total)."' onmouseout='return nd();'>".nicenum($total)."</a> </nobr></td>\n";
		echo "<th>$r</th>";
		echo "<td>".$app['comment']."</td>";
		echo "</tr>\n";
	}

	echo "</table>\n";
}

//////////////////////////////////////////////////////////////////////////////

    ConnectDatabase ();
    LoadConfig ('ru');

///// Сервер выключен по техническим причинам.
    if (!$Config['enabled'])
    {
        echo $local[$GlobalLang]['Maintain'];
        ob_end_flush ();        
        exit ();
    }

///// Регистрация, форма
    if (!key_exists("page", $_GET)) $page = 0;
    else $page = $_GET['page'];
    if ($page === "reg")
    {
        $RegError = $local[$GlobalLang]['Red0'];
        RegisterForm ();
    }

///// Регистрация, обработка
    if (!key_exists("page", $_POST)) $page = 0;
    else $page = $_POST['page'];
    if ($page === "reg")
    {
        $login = $_POST['login'];
        $pass = $_POST['pass'];
        $pass2 = $_POST['pass2'];
        $uni = $_POST['uni'];
        $email = $_POST['email'];

        if ( strlen ($login) < 3 )
        {
            $RegError = $local[$GlobalLang]['Red1'];
            RegisterForm ();
        }

        if ( IsUserExist ($login, $uni) )
        {
            $RegError = $local[$GlobalLang]['Red2_1']."$login (".$local[$GlobalLang]['RedUni']." $uni) ".$local[$GlobalLang]['Red2_2'];
            RegisterForm ();
        }

        if ( strlen ($pass) < 5 )
        {
            $RegError = $local[$GlobalLang]['Red3'];
            RegisterForm ();
        }

        if ($pass !== $pass2)
        {
            $RegError = $local[$GlobalLang]['Red4'];
            RegisterForm ();
        }

        AddUser ($login, $pass, $uni, $email);
        RegisterSuccess (3, $login, $pass, $uni);
    }

///// Восстановление пароля. Шаг 1.
    if (!key_exists("page", $_GET)) $page = 0;
    else $page = $_GET['page'];
    if ($page === "lost")
    {
        $LostError = "";
        LostPassForm ();
    }
    if (!key_exists("page", $_POST)) $page = 0;
    else $page = $_POST['page'];
    if ($page === "lost")
    {
        $login = $_POST['login'];
        $uni = $_POST['uni'];
        $email = $_POST['email'];

        $LostError = RestorePassword ($login, $uni, $email);
        if ($LostError === "TRUE") LostPassSuccess ($local[$GlobalLang]['LostPassOK'], 3);
        else LostPassForm ();
    }

///// Восстановление пароля. Шаг 2.
    if ( $_GET['page'] === "genpass" )
    {
        $ack = $_GET['ack'];
        $ack = KillInjection ($ack);
        $email = $_GET['email'];
        $email = KillInjection ($email);

        // Найти пользователя с таким кодом активации.
        $query = "SELECT * FROM ".USERTABLE." WHERE emailack='".$ack."'";
        $result = dbquery ($query);
        $user = dbarray ($result);
        if ($user) {
            $string = GeneratePassword();        // сгенерировать новый пароль
            SetUserPassword ($string, $user['id']);

            mail_utf8 ( "$email", $local[$GlobalLang]['LostPassSubj'],     // отправить письмо с подтверждением
                        $local[$GlobalLang]['LostPassBody3'].":\n".
                        $local[$GlobalLang]['Login'].": ".$user['login']."\n".
                        $local[$GlobalLang]['Uni'].": ".$user['uni']."\n".
                        $local[$GlobalLang]['Pass'].": $string\n\n".
                        $local[$GlobalLang]['LostPassBody4'],
                        "From: admin <ogamespec@gmail.com>" );

            dbquery ("UPDATE ".USERTABLE." SET emailack = '' WHERE id='".$user['id']."'");    // сбросить код активации.
            LostPassSuccess ($local[$GlobalLang]['LostPassOK2'], 3);
        }
        else LostPassSuccess ($local[$GlobalLang]['LostPassE4'], 3);
    }

///// Логин
    if (!key_exists("page", $_POST)) $page = 0;
    else $page = $_POST['page'];
    if ($page === "login")
    {
        $login = $_POST['login'];
        $pass = $_POST['pass'];
        $uni = $_POST['uni'];

        if ( strlen ($pass) == 0) { $login = "guest"; $pass = "guest"; }
        
        if (IsUserExist ($login, $uni) == false)
            ErrorPage ( $local[$GlobalLang]['Red5_1']." $login (".$local[$GlobalLang]['RedUni']." $uni) ".$local[$GlobalLang]['Red5_2'] , 5);
        if (IsPasswordCorrect ($login, $pass, $uni) == false)
            ErrorPage ( $local[$GlobalLang]['Red7']." $login (".$local[$GlobalLang]['RedUni']." $uni).<br>".$local[$GlobalLang]['Red8'] , 5);
        if (IsUserBanned ($login, $uni))
            ErrorPage ( $local[$GlobalLang]['Red6']."<br>" , 5);

        Login ($login, $uni);
        $SellFormError = "";
        DrawTable (0);
    }

    if (!key_exists("page", $_GET)) $page = 0;
    else $page = $_GET['page'];
    if ($page === "login")
    {
        $login = $_GET['login'];
        $pass = $_GET['pass'];
        $uni = $_GET['uni'];

        if ( strlen ($pass) == 0) { $login = "guest"; $pass = "guest"; }

        if ( (strlen($login) >= 3) && (strlen($pass) >= 5) )
        {
            if (IsUserExist ($login, $uni) == false)
                ErrorPage ( $local[$GlobalLang]['Red5_1']." $login (".$local[$GlobalLang]['RedUni']." $uni) ".$local[$GlobalLang]['Red5_2'] , 5);
            if (IsPasswordCorrect ($login, $pass, $uni) == false)
                ErrorPage ( $local[$GlobalLang]['Red7']." $login (".$local[$GlobalLang]['RedUni']." $uni).<br>".$local[$GlobalLang]['Red8'] , 5);
            if (IsUserBanned ($login, $uni))
                ErrorPage ( $local[$GlobalLang]['Red6']."<br>" , 5);

            Login ($login, $uni);
            $SellFormError = "";
            DrawTable (0);
        }
    }

///// Выход
    if (!key_exists("page", $_GET)) $page = 0;
    else $page = $_GET['page'];
    if ($page === "logout")
    {
        Logout (1);
        exit ();
    }

///// Продать
    if (!key_exists("page", $_POST)) $page = 0;
    else $page = $_POST['page'];
    if ($page === "sell" && isset($_REQUEST[session_name()]))
    {
        session_start();
        $amount = $_POST['amount'];

        $amount = str_replace (",", "", $amount);
        $amount = str_replace (".", "", $amount);
        $amount = str_replace (" ", "", $amount);

        $g = $_POST['cg']; if ( $g === "" ) $g = 0;
        $s = $_POST['cs']; if ( $s === "" ) $s = 0;
        $p = $_POST['cp']; if ( $p === "" ) $p = 0;
        $moon = 0;
        if ($_POST['moon'] === 'on') $moon = 1;
        $tradewhat = $_POST['tradewhat'];
        $tradefor = $_POST['tradefor'];
        $rate = $_POST['rate'];
        $rate = str_replace (",", ".", $rate);
        $comment = $_POST['comment'];
        if (!key_exists("until", $_POST)) $until = 1;
        else $until = $_POST['until'];
        if (isset ($_POST['trade_id'])) $tid = $_POST['trade_id'];
        else $tid = 0;
        $SellFormError = AddTrader ( $tid, $g, $s, $p, $moon, 
                                     $tradewhat, $amount,
                                     $tradefor, $rate, $comment, $until);
        if ($SellFormError === "OK") echo "    &nbsp; &nbsp; <font color=lime>".$local[$GlobalLang]['SellOK']."</font>";
        else echo "    &nbsp; &nbsp; <font color=red>".$SellFormError."</font>";
        ob_end_flush ();
        exit ();
    }

///// Редактировать заявку.
    if (!key_exists("page", $_POST)) $page = 0;
    else $page = $_POST['page'];
    if ($page === "edit" && isset($_REQUEST[session_name()]))
    {
        session_start();
        $tid = $_POST['trade_id'];
        $SellFormError = $local[$GlobalLang]['Red9'];
        DrawTable ($tid);
    }

///// Удалить заявку.
    if (!key_exists("page", $_POST)) $page = 0;
    else $page = $_POST['page'];
    if ($page === "remove" && isset($_REQUEST[session_name()]))
    {
        session_start();
        $tid = $_POST['trade_id'];
        $SellFormError = RemoveTrader ($tid);
        DrawTable (0);
    }

///// Меню пользователя
    if (!key_exists("page", $_GET)) $page = 0;
    else $page = $_GET['page'];
    if ($page === "usermenu" && isset($_REQUEST[session_name()]))
    {
        session_start();
        if ($_SESSION['user_id'] != 1) DrawUserMenu ();
    }

///// Изменить настройки пользователя
    if (!key_exists("page", $_POST)) $page = 0;
    else $page = $_POST['page'];
    if ($page === "save" && isset($_REQUEST[session_name()]))
    {
        session_start();
        $name = $_POST['login'];
        $ally = $_POST['ally'];
        $skin = $_POST['skin'];
        $language = $_POST['language'];

        // Заменить глобальные установки скина и языка для текущего пользователя.
        $s = GetUserSkin ();
        $l = GetUserLang ();
        if ($s != "") $GlobalSkin = $s;
        if ($l != "") $GlobalLang = $l;

        if ($_SESSION['user_id'] == 1)
        {
            $SellFormError = $local[$GlobalLang]['AdminNotice'];
            DrawTable (0);
        }

        // Изменить альянс.
        $ally = KillInjection ($ally);
        SetUserAlly ( $_SESSION['user_id'], $ally );

        $current_lang = GetUserLang ();
        if ( strlen ($language) > 0 && $language !== $current_lang)    // Поменять язык?
        {
            $language = KillInjection ($language);
            SetUserLang ($language);
            $SellFormError = "<font color=lime>".$local[$GlobalLang]['UserLangOK']."</font> ";
        }

        if ( strlen ($name) == 0 && strlen ($skin) > 0 )    // Поменять скин?
        {
            // Убрать тэги и скрипты из строки.
            $skin = KillInjection ($skin);
            if (strlen ($skin) > 5 && ValidateURL ($skin))  // Проверить ссылку на скин.
            {
                SetUserSkin ($skin);
                $SellFormError .= "<font color=lime>".$local[$GlobalLang]['UserSkinOK']."</font>";
            }
            else $SellFormError .= $local[$GlobalLang]['UserSkinFail'];
            DrawTable (0);
        }

        if ( strlen ($name) < 3 )
        {
            $SellFormError = $local[$GlobalLang]['Red1'];
            DrawTable (0);
        }
        
        if ( RenameUser ($_SESSION['user_id'], $name) == true )
        {
            $SellFormError = "<font color=lime>".$local[$GlobalLang]['UserChanged']."</font>";
            $_SESSION['login'] = $name;
            session_regenerate_id ();
        }
        else
        {
            $SellFormError = $local[$GlobalLang]['UserExist'];
        }
        DrawTable (0);
    }

///// АДМИН: Удалить все заявки пользователя
    if (!key_exists("page", $_POST)) $page = 0;
    else $page = $_POST['page'];
    if ($page === "wipeuser" && isset($_REQUEST[session_name()]))
    {
        session_start();
        $uid = $_POST['user_id'];
        if (!$_SESSION['admin'])
        {
            Logout (0);
            ErrorPage ( $local[$GlobalLang]['AdminNotAdmin']."<br>" , 5);
        }
        WipeUser ($uid);
        $SellFormError = $local[$GlobalLang]['AdminAllBids']." ".GetUserNameById($uid,$_SESSION['uni']).$local[$GlobalLang]['AdminRemoved'];
        DrawTable (0);
    }

///// АДМИН: Забанить/разбанить пользователя
    if (!key_exists("page", $_POST)) $page = 0;
    else $page = $_POST['page'];
    if ($page === "banuser" && isset($_REQUEST[session_name()]))
    {
        session_start();
        $uid = $_POST['user_id'];
        $banned = $_POST['ban'];
        if (!$_SESSION['admin'])
        {
            Logout (0);
            ErrorPage ( $local[$GlobalLang]['AdminNotAdmin']."<br>" , 5);
        }
        if ($uid < 10000)
        {
            $SellFormError = $local[$GlobalLang]['AdminCannotBan1'];
            DrawTable (0);
        }
        if ($_SESSION['user_id'] == $uid)
        {
            $SellFormError = $local[$GlobalLang]['AdminCannotBan2'];
            DrawTable (0);
        }
        BanUser ($uid, $banned);
        if ($banned) $SellFormError = $local[$GlobalLang]['AdminUser']." ".GetUserNameById($uid,$_SESSION['uni']).$local[$GlobalLang]['AdminBanned'];
        else $SellFormError = $local[$GlobalLang]['AdminUser']." ".GetUserNameById($uid,$_SESSION['uni']).$local[$GlobalLang]['AdminUnbanned'];
        DrawTable (0);
    }

///// Главная страница
    if (isset($_REQUEST[session_name()]))
    {
        session_start();
        if (IsUserBanned ($_SESSION['login'], $_SESSION['uni']))
        {
            Logout (0);
            ErrorPage ( $local[$GlobalLang]['Red6']."<br>" , 5);
        }
        $SellFormError = "";
        DrawTable (0);
    }
    ob_end_flush ();
?>

<!-- Стартовая страница -->

<HTML>
<link rel="stylesheet" type="text/css" href="../css/formate.css">
<HEAD><link rel="stylesheet" type="text/css" href="<?=$GlobalSkin?>formate.css">
<meta http-equiv='content-type' content='text/html; charset=utf-8' />
<TITLE><?=$local[$GlobalLang]['Terminal']?></TITLE>

<script language='JavaScript'>

function createCookie(name,value,days) {
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
    }
    else var expires = "";
    document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name,"",-1);
}

function onBodyLoad ()
{
    univalue = readCookie ("last_uni<?='_'.$server?>");
    if (univalue == null) univalue = 1;
    document.login_form.uni.value = univalue;
    loginvalue = readCookie ("last_login<?='_'.$server?>");
    if (loginvalue == null) loginvalue = "";
    lang = readCookie ("lang<?='_'.$server?>");
    if (lang == null)
    {
        createCookie ( "lang<?='_'.$server?>", "ru", 9999 );
        window.location.reload ();
    }
    document.login_form.lang.value = lang;
}

function onSubmit ()
{
    createCookie ( "last_uni<?='_'.$server?>", document.login_form.uni.value, 9999 );
    createCookie ( "last_login<?='_'.$server?>", document.login_form.login.value, 9999 );
}

function onLangChange()
{
    createCookie ( "global_lang", document.getElementById('GlobalLang').value, 9999 );
    window.location.reload ();
}

function onSkinChange()
{
    createCookie ( "global_skin", document.getElementById('GlobalSkin').value, 9999 );
    window.location.reload ();
}

function onFlagClick (loc)
{
    createCookie ( "lang<?='_'.$server?>", loc, 9999 );
    window.location.reload ();
}

function onSortChange()
{
    sort = document.getElementById('sortby').value;
    url = "<?php echo hostname().scriptname(); ?>?sort=" + sort;
    window.location = url;
}

</script>

<script type='text/javascript' src='jscripts/overlib.js'></script>
<script type='text/javascript' src='jscripts/php.js'></script>
<script type='text/javascript' src='jscripts/tw-sack.js'></script>

</HEAD>

<BODY onload="onBodyLoad();" style='overflow: hidden; scrollbars: none;'><div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<div id='flags' style='position: absolute; top: 0px; right: 0px; z-index: 7;'>
<table cellpadding=0 cellspacing=0>
<tr>
<?DrawFlags();?>
<td><a href='http://ogamespec.com/download/tradelocal.zip' title='<?=$local[$GlobalLang]['Donate']?>'><img src='../images/wrench.gif'></a></td>
</tr>
</table>
</div>

<style>
option.imagebacked {
background-repeat: no-repeat;
vertical-align: middle;
}
</style>
<div id='settings' style='position: absolute; top: 20px; right: 30px; z-index: 6;'>
<table cellpadding=0 cellspacing=0>
<tr>
<td><?=$local[$GlobalLang]['UserSkin']?>: <select id='GlobalSkin' onchange='onSkinChange();'>
<? echo DrawSkinSelect($GlobalSkin);?>
</select></td><td>&nbsp;</td>
<td><?=$local[$GlobalLang]['Lang']?>: <select id='GlobalLang' onchange='onLangChange();'>
<? echo DrawLangSelect($GlobalLang);?>
</select></td>
</tr>
</table>
</div>

<div id='news' style='position: absolute; top: 0px; z-index: 1;'>
<table ><tr><th>
<?php echo $local[$GlobalLang]['Announce']; ?>
</th></tr></table></div>

<div id='version' style='position: absolute; bottom: 0px; right: 0px; z-index: 5;'>
<table  ><tr><td><b>
<a href='tradelog.htm'><?=$local[$GlobalLang]['Ver']?> <?=$version?></a>
</b></td></tr></table></div>

<div id='stats' style='position: absolute; bottom: 0px; left: 0px; z-index: 4;'>
<table><tr><td style='background-color: transparent;'><b>
<?=$local[$GlobalLang]['StatUsers']?>: <?=GetUserCount()?> <?=$local[$GlobalLang]['StatBids']?>: <?=GetBidsCount()?>
</b></td></tr></table></div>

<div id='todayapps' style='position: absolute; top: 50px; left: 30px; z-index: 2;'>
<?RecentApplications();?>
</div>


<div id='enter' style='position: absolute; top: 50px; right: 30px; z-index: 3;'>

<center>

<form action="<?=$SELF?>" method="POST" name='login_form' onsubmit='onSubmit();'>
<input type='hidden' name='page' value='login'>
<table cellpadding="0" cellspacing="0" border="0" style="background-color: #344566; margin-top:0px; width:250px;" align="center">
<tr><td colspan="2" align="center" class="c"><?=$local[$GlobalLang]['EnterTerminal']?></td></tr>

<tr class="firstcolor"><td colspan="2" align="center">&nbsp;</td></tr>
<tr class="firstcolor"><td style="padding-top:5px; padding-left:5px;"><?=$local[$GlobalLang]['Uni']?>:</td>
<td style="padding-top:5px;">
<?=UniSelect()?>
</td></tr>
<tr ><td style="padding-top:5px; padding-left:5px;"><?=$local[$GlobalLang]['Login']?>:</td><td style="padding-top:5px;"><input class="textfield" type="text" name="login" size="20" maxlength="20" /></td></tr>
<tr ><td style="padding-top:5px; padding-left:5px;"><?=$local[$GlobalLang]['Pass']?>:</td><td style="padding-top:5px;"><input class="textfield" type="password" name="pass" size="20" maxlength="20" /></td></tr>

<tr ><td colspan="2" align="center" style="padding-top:5px;"><input class="button" type="submit" name="OK" value="<?=$local[$GlobalLang]['Enter']?>" /></td></tr>

<tr ><td colspan="2">&nbsp;</td></tr>
<tr ><td align="center"><a class="link" href="<?=$SELF?>?page=reg"><?=$local[$GlobalLang]['Register']?></a></td>
<td align="center"><a class="link" href="<?=$SELF?>?page=lost"><?=$local[$GlobalLang]['LostPass']?></a></td>
</tr>
</table>
</form>

</center>

</div>

<!--SpyLOG-->
<span id="spylog2007069"></span><script type="text/javascript"> var spylog = { counter: 2007069, image: undefined, next: spylog }; document.write(unescape('%3Cscript src%3D"http' + (('https:' == document.location.protocol) ? 's' : '') + '://counter.spylog.com/cnt.js" defer="defer"%3E%3C/script%3E')); </script>
<!--SpyLOG-->

</BODY></HTML>