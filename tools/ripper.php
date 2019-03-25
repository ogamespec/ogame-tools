<?php

// Ripper - программа для слежения за неактивными игроками в помощь Звездоводам.
// (c) 2010, 2012 Bayonet

// Алгоритм работы программы.
// Программа ищет давно неактивных игроков (скрытых ишек) путём слежения за приростом статистики.
// Если на протяжении нескольких дней статистика игрока не изменяется, то вероятней всего он уже неактивен.
// Это позволяет атаковать такого игрока Звёздами Смерти, не дожидаясь момента когда он станет явно неактивным (с пометкой (i))
// Сбор данных статистики осуществляется через специальный скрипт для браузера, который парсит страницу статистики в момент её открытия
// и посылает данные на сервер (на манер Galaxy Toolbar).
// Скрипт не совершает никаких автоматических действий с сервером OGame, он по сути дела является упрощенной версией разрешённой Galaxy Tool.
// Идентификация данных к опредлённому пользователю производится по Сигнатуре - уникальной сгенерированной ссылке, которую пользователи
// должны держать в секрете.

// TODO:
// + Сохранение и загрузка настроек (кукисы)
// + Изменение игровой страницы Настройки
// + Парсер статистики 0.84 в промежуточный формат
// + Парсер статистики 1.0 в промежуточный формат
// + Сохранение статистики
// + Диалог входа в программу
// + Отправка и отображение сообщений по страницам (shoutbox)
// + Отображение общей статистики (statsbox)
// - Страница поиска
// + Страница информации об игроке
// - Страница информации об альянсе
// - Кнопка перехода на риппер в главном меню
// - Адаптация скрипта для Firefox и Google Chrome(+)
// + Переход на OGame 4.0+

$version = "2.04";

// Сдвиг времени хоста относительно времени игрового сервера.
$timeshift = 0;

// -----------------------------------------------------------------------------------------------------------------
// Список ошибок.

$errors = array (
    10001 => "Ваш IP-адрес заблокирован за нарушение системы безопасности.",
    10002 => "Неверная Сигнатура. Такого аккаунта не существует.",
    10003 => "Создавать аккаунты можно не чаще одного раза в час.",
    10004 => "Неизвестная Вселенная.",
    10005 => "Вы добавляете данные не из той Вселенной.",
    10006 => "Администратор запретил создание аккаунтов на этом сервере.",
    10007 => "Недостаточно прав для обновления."
);

// -----------------------------------------------------------------------------------------------------------------
// Сервера Ogame.

$gameservers = array (
    'ae' => array ( 'country' => 'ОАЭ', 'host' => 'ae.ogame.org', 'flag' => 0 ),
    'ar' => array ( 'country' => 'Аргентина', 'host' => 'ogame.com.ar', 'flag' => -14 ),
    'ba' => array ( 'country' => 'Босния и Герцоговина', 'host' => 'ba.ogame.org', 'flag' => -854 ),
    'br' => array ( 'country' => 'Бразилия', 'host' => 'ogame.com.br', 'flag' => -56 ),
    'bg' => array ( 'country' => 'Болгария', 'host' => 'bg.ogame.org', 'flag' => -42 ),
    'cz' => array ( 'country' => 'Чешская Республика', 'host' => 'ogame.cz', 'flag' => -154 ),
    'de' => array ( 'country' => 'Германия', 'host' => 'ogame.de', 'flag' => -168 ),
    'dk' => array ( 'country' => 'Дания', 'host' => 'ogame.dk', 'flag' => -182 ),
    'en' => array ( 'country' => 'Великобритания', 'host' => 'ogame.org', 'flag' => -224 ),
    'es' => array ( 'country' => 'Испания', 'host' => 'ogame.com.es', 'flag' => -238 ),
    'fi' => array ( 'country' => 'Финляндия', 'host' => 'fi.ogame.org', 'flag' => -266 ),
    'fr' => array ( 'country' => 'Франция', 'host' => 'ogame.fr', 'flag' => -280 ),
    'gr' => array ( 'country' => 'Греция', 'host' => 'ogame.gr', 'flag' => -294 ),
    'hr' => array ( 'country' => 'Хорватия', 'host' => 'ogame.com.hr', 'flag' => -322 ),
    'hu' => array ( 'country' => 'Венгрия', 'host' => 'ogame.hu', 'flag' => -336 ),
    'it' => array ( 'country' => 'Италия', 'host' => 'ogame.it', 'flag' => -420 ),
    'jp' => array ( 'country' => 'Япония', 'host' => 'ogame.jp', 'flag' => -434 ),
    'lt' => array ( 'country' => 'Литва', 'host' => 'ogame.lt', 'flag' => -476 ),
    'lv' => array ( 'country' => 'Латвия', 'host' => 'ogame.lv', 'flag' => -490 ),
    'mx' => array ( 'country' => 'Мексика', 'host' => 'mx.ogame.org', 'flag' => -532 ),
    'nl' => array ( 'country' => 'Нидерланды', 'host' => 'ogame.nl', 'flag' => -546 ),
    'no' => array ( 'country' => 'Норвегия', 'host' => 'ogame.no', 'flag' => -560 ),
    'pl' => array ( 'country' => 'Польша', 'host' => 'ogame.onet.pl', 'flag' => -616 ),
    'pl2' => array ( 'country' => 'Польша', 'host' => 'ogame.pl', 'flag' => -616 ),
    'pt' => array ( 'country' => 'Португалия', 'host' => 'ogame.com.pt', 'flag' => -630 ),
    'ro' => array ( 'country' => 'Румыния', 'host' => 'ogame.ro', 'flag' => -644 ),
    'rs' => array ( 'country' => 'Сербия', 'host' => 'ogame.rs', 'flag' => -658 ),
    'ru' => array ( 'country' => 'Российская Федерация', 'host' => 'ogame.ru', 'flag' => -672 ),
    'sk' => array ( 'country' => 'Словакия', 'host' => 'ogame.sk', 'flag' => -714 ),
    'se' => array ( 'country' => 'Швеция', 'host' => 'ogame.se', 'flag' => -686 ),
    'si' => array ( 'country' => 'Словения', 'host' => 'si.ogame.org', 'flag' => -700 ),
    'tr' => array ( 'country' => 'Турция', 'host' => 'tr.ogame.org', 'flag' => -742 ),
    'tr2' => array ( 'country' => 'Турция', 'host' => 'ogame.tr', 'flag' => -742 ),
    'tw' => array ( 'country' => 'Тайвань', 'host' => 'ogame.tw', 'flag' => -756 ),
    'us' => array ( 'country' => 'США', 'host' => 'ogame.us', 'flag' => -798 )
);

// Названия вселенных из редизайна.
$reduninames = array (
 "Andromeda", "Barym", "Capella", "Draco", "Electra", "Fornax", "Gemini", "Hydra", "Io", "Jupiter",
 "Kassiopeia", "Leo", "Mizar", "Nekkar", "Orion"
);

// -----------------------------------------------------------------------------------------------------------------
// Вспомогательные функции.

header('Pragma:no-cache');

function nn ($number) { return number_format($number,0,",","."); }
function timfmt ($n) {
    if ($n == 0) return "00";
    if ($n < 10) return "0$n";
    else return "$n";
}

// bool/array str_between( string str, string start_str, string end_str )
function str_between($str,$start,$end) {
  if (preg_match_all('/' . preg_quote($start) . '(.*?)' . preg_quote($end) . '/',$str,$matches)) {
   return $matches[1];
  }
  return false;
}

function method () { return $_SERVER['REQUEST_METHOD']; }
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

// Подключиться к базе данных MySQL.
require_once "db.php";
if ( file_exists ("ripper_config.php") )
{
    require_once "ripper_config.php";
    dbconnect ($db_host, $db_user, $db_pass, $db_name);
    dbquery("SET NAMES 'utf8';");
    dbquery("SET CHARACTER SET 'utf8';");
    dbquery("SET SESSION collation_connection = 'utf8_general_ci';");
}

// Имя Вселенной.
function UniverseName ($num)
{
    global $reduninames;
    if ($num < 100) return "Вселенная $num";
    else {
        if ( isset ( $reduninames[$num-101] ) ) return $reduninames[$num-101];
        else return "Неизвестная Вселенная $num";
    }
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

// -----------------------------------------------------------------------------------------------------------------
// Установка таблиц и настройка файла конфигурации.

// Сохранить файл конфигурации.
function SaveConfigFile ()
{
    $file = fopen ("ripper_config.php", "wb");
    if ($file)
    {
        fwrite ($file, "<?php\r\n");
        fwrite ($file, "// Database settings. DO NOT EDIT!\r\n");
        fwrite ($file, "$"."db_host=\"". $_POST["db_host"] ."\";\r\n");
        fwrite ($file, "$"."db_user=\"". $_POST["db_user"] ."\";\r\n");
        fwrite ($file, "$"."db_pass=\"". $_POST["db_pass"] ."\";\r\n");
        fwrite ($file, "$"."db_name=\"". $_POST["db_name"] ."\";\r\n");
        fwrite ($file, "$"."db_prefix=\"". $_POST["db_prefix"] ."\";\r\n");
        fwrite ($file, "$"."db_secret=\"". $_POST["db_secret"] ."\";\r\n");
        fwrite ($file, "?>");
        fclose ($file);
    }
}

// Удалить все таблицы и создать новые пустые
function ResetDatabase ()
{
    $tabs = array ('globals', 'iptable', 'account', 'msg', 'astat', 'pstat', 'pinfo', 'galaxy');
    $globalcols = array ( 'nextacc', 'nextmsg', 'enabled' );
    $globaltype = array (  'INT', 'INT', 'INT' );
    $iptablecols = array ( 'ip', 'last_create', 'last_login', 'create_acc', 'login_acc', 'banned' );
    $iptabletype = array (  'CHAR(32)', 'INT UNSIGNED', 'INT UNSIGNED', 'INT', 'INT', 'INT' );
    $accountcols = array ( 'acc_id', 'sig', 'sig_up', 'sig_view', 'msg_per_page', 'ip', 'uni', 'firsthit', 'trafficIn', 'trafficOut', 'ownally', 'last_g', 'last_s' );
    $accounttype = array (  'INT', 'CHAR(32)', 'CHAR(32)', 'CHAR(32)', 'INT', 'CHAR(32)', 'CHAR(32)', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED', 'CHAR(32)', 'INT', 'INT' );
    $msgcols = array ( 'acc_id', 'msg_id', 'created', 'title', 'text' );
    $msgtype = array (  'INT', 'INT', 'INT UNSIGNED', 'TEXT', 'TEXT' );
    $astatcols = array ( 'acc_id', 'ally_id', 'name', 'members', 'type', 'place', 'score', 'date' );
    $astattype = array (  'INT', 'INT', 'CHAR(32)', 'INT', 'INT', 'INT', 'INT UNSIGNED', 'INT UNSIGNED' );
    $pstatcols = array ( 'acc_id', 'player_id', 'name', 'ally_id', 'g', 's', 'p', 'type', 'place', 'score', 'date', 'status' );
    $pstattype = array (  'INT', 'INT', 'CHAR(32)', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT UNSIGNED', 'INT UNSIGNED', 'TEXT' );
    $pinfocols = array ( 'acc_id', 'player_id', 'color', 'notes' );
    $pinfotype = array (  'INT', 'INT', 'CHAR(32)', 'TEXT' );
    $galaxycols = array ( 'id', 'acc_id', 'planet_id', 'player_id', 'g', 's', 'p', 'name', 'type', 'diam' );
    $galaxytype = array (  'INT AUTO_INCREMENT PRIMARY KEY', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'TEXT', 'INT', 'INT' );
    $tabrows = array (&$globalcols, &$iptablecols, &$accountcols, &$msgcols, &$astatcols, &$pstatcols, &$pinfocols, &$galaxycols );
    $tabtypes = array (&$globaltype, &$iptabletype, &$accounttype, &$msgtype, &$astattype, &$pstattype, &$pinfotype, &$galaxytype);

    // Удалить все таблицы и создать новые пустые.
    dbconnect ($_POST["db_host"], $_POST["db_user"], $_POST["db_pass"], $_POST["db_name"]);
    dbquery("SET NAMES 'utf8';");
    dbquery("SET CHARACTER SET 'utf8';");
    dbquery("SET SESSION collation_connection = 'utf8_general_ci';");
    foreach ($tabs as $i => $name)
    {
        $opt = " (";
        $rows = $tabrows[$i];
        $types = $tabtypes[$i];
        foreach ($rows as $row => $rowname)
        {
            if ($row != 0) $opt .= ", ";
            $opt .= $rows[$row] . " " . $types[$row];
        }
        $opt .= ")";

        $query = 'DROP TABLE IF EXISTS '.$_POST["db_prefix"].$tabs[$i];
        dbquery ($query, TRUE);
        $query = 'CREATE TABLE '.$_POST["db_prefix"].$tabs[$i].$opt." CHARACTER SET utf8 COLLATE utf8_general_ci";
        dbquery ($query, TRUE);
    }

    // Сбросить глобальные счетчики.
    $opt = " (";
    $global = array( 10, 1, 1 );
    foreach ($global as $i=>$entry)
    {
        if ($i != 0) $opt .= ", ";
        $opt .= "'".$global[$i]."'";
    }
    $opt .= ")";
    $query = "INSERT INTO ".$_POST["db_prefix"]."globals VALUES".$opt;
    dbquery( $query);
}

// -----------------------------------------------------------------------------------------------------------------
// Работа с базой данных.

// Увеличить глобальный счетчик и возвратить его последнее значение.
function IncrementDBGlobal ($name)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."globals";
    $result = dbquery ($query);
    $globals = dbarray ($result);
    $id = $globals[$name]++;
    $query = "UPDATE ".$db_prefix."globals SET $name = ".$globals[$name];
    dbquery ($query);
    return $id;
}

// Добавить строку в таблицу.
function AddDBRow ( $row, $tabname )
{
    global $db_prefix;
    $opt = " (";
    foreach ($row as $i=>$entry)  {
        if ($i != 0) $opt .= ", ";
        $opt .= "'".$row[$i]."'";
    }
    $opt .= ")";
    $query = "INSERT INTO ".$db_prefix."$tabname VALUES".$opt;
    dbquery( $query);
}

function ServerEnabled ()
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."globals";
    $result = dbquery ($query);
    if ($result == NULL) return 0;
    $globals = dbarray ($result);
    return $globals['enabled'];
}

// -----------------------------------------------------------------------------------------------------------------
// Управление аккаунтами.

// Создать новый аккаунт. Возвратить объект "аккаунт".
function CreateAccount ()
{
    global $db_secret;
    $now = time ();
    $sig = md5 ( $now . $db_secret );
    $sig_up = md5 ( $now . $db_secret . "UPDATE" );
    $sig_view = md5 ( $now . $db_secret . "VIEW" );
    $acc = array ( IncrementDBGlobal("nextacc"), $sig, $sig_up, $sig_view, 25, $_SERVER['REMOTE_ADDR'], "?", 0, 0, 0, "" );
    AddDBRow ( $acc, "account" );
    return LoadAccountBySig ( $sig );
}

// Загрузить аккаунт по сигнатуре.
function LoadAccountBySig ($sig)
{
    global $db_prefix;

    $sig = KillInjection ($sig);

    $result = dbquery ( "SELECT * FROM ".$db_prefix."account WHERE sig = '".$sig."'" );        // Админ
    $acc = dbarray ($result);
    if ( $acc ) {
        $acc['u_admin'] = $acc['u_update'] = $acc['u_view'] = true;
        return $acc;
    }

    $result = dbquery ( "SELECT * FROM ".$db_prefix."account WHERE sig_up = '".$sig."'" );        // Обновитель
    $acc = dbarray ($result);
    if ( $acc ) {
        $acc['u_admin'] = false;
        $acc['u_update'] = $acc['u_view'] = true;
        return $acc;
    }

    $result = dbquery ( "SELECT * FROM ".$db_prefix."account WHERE sig_view = '".$sig."'" );        // Обозреватель
    $acc = dbarray ($result);
    if ( $acc ) {
        $acc['u_admin'] = $acc['u_update'] = false;
        $acc['u_view'] = true;
        return $acc;
    }

    return null;
}

// Проверить - разрешено ли добавлять данные на этот аккаунт. Если вселенная ещё не назначена - назначить.
function CheckUniverse ($acc, $uni)
{
    global $db_prefix;
    if ( $acc['firsthit'] == 0 ) {
        dbquery ( "UPDATE ".$db_prefix."account SET uni = '".$uni."', firsthit = ".time()." WHERE acc_id = " . $acc['acc_id'] );
        return TRUE;
    }
    else return $acc['uni'] === $uni;
}

// Получить описание вселенной (для заголовка над меню).
function GetUniverseHTML ($acc)
{
    global $gameservers;
    
    if ( $acc['firsthit'] == 0 ) {
        echo "<h2>Вселенная не назначена</h2>";
        return;
    }

    foreach ( $gameservers as $server => $obj ) {
        $matches = array ();
        $match = preg_match ( '/uni[0-9]{1,}.'. $gameservers[$server]['host'] .'/', $acc['uni'], &$matches );
        if ($match) {
            $point = strpos ( $matches[0], '.' );
            $uninum = substr ( $matches[0], 3, $point-3 );
            $name = UniverseName ($uninum);
            
            echo "<h2 title=\"". $gameservers[$server]['country'] ."\" style='background:url(\"images/mmoflags.png\") no-repeat scroll 0 0 transparent; padding-left:23px; height:14px !important;";
            echo " background-position: left ". $gameservers[$server]['flag'] ."px !important;'>$name";
            if ( $acc['u_admin'] ) echo "<span class=\"ui-icon ui-icon-gear\" style=\"float:right;\"></span>";
            else if ( $acc['u_update'] ) echo "<span class=\"ui-icon ui-icon-refresh\" style=\"float:right;\"></span>";
            else if ( $acc['u_view'] ) echo "<span class=\"ui-icon ui-icon-search\" style=\"float:right;\"></span>";
            echo "</h2>";
            return;
        }
    }
    
    echo "<h2>Неизвестная Вселенная</h2>";
}

// Статистика по трафику. Обновляется при загрузке каждой страницы.
function AddTraffic ($acc, $bytes, $outcome)
{
    global $db_prefix;
    $traffic = "trafficIn";
    if ($outcome) $traffic = "trafficOut";
    $query = "UPDATE ".$db_prefix."account SET $traffic = $traffic + $bytes WHERE acc_id = '".$acc['acc_id']."'";
    dbquery ($query);
}

// Количество уникальных записей в статистике.
function TotalPlayers ($acc)
{
    global $db_prefix;
    $result = dbquery ("SELECT COUNT(DISTINCT player_id) AS total FROM ".$db_prefix."pstat WHERE acc_id = " . $acc['acc_id'] );
    $a = dbarray ($result);
    return $a['total'];
}

// Текущая статистика игрока.
function LastStat ($acc, $player_id)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."pstat WHERE player_id = $player_id AND type = 1 AND acc_id = " . $acc['acc_id'] . " ORDER BY date DESC LIMIT 1";
    $result = dbquery ($query);
    return dbarray ($result);
}

// Текущая статистика альянса.
function LastAllyStat ($acc, $ally_id)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."astat WHERE ally_id = $ally_id AND type = 1 AND acc_id = " . $acc['acc_id'] . " ORDER BY date DESC LIMIT 1";
    $result = dbquery ($query);
    return dbarray ($result);
}

// Текущий флот игрока.
function LastFleet ($acc, $player_id)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."pstat WHERE player_id = $player_id AND type = 2 AND acc_id = " . $acc['acc_id'] . " ORDER BY date DESC LIMIT 1";
    $result = dbquery ($query);
    return dbarray ($result);
}

// Текущий флот альянса.
function LastAllyFleet ($acc, $ally_id)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."astat WHERE ally_id = $ally_id AND type = 2 AND acc_id = " . $acc['acc_id'] . " ORDER BY date DESC LIMIT 1";
    $result = dbquery ($query);
    return dbarray ($result);
}

// Текущие исследования игрока.
function LastResearch ($acc, $player_id)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."pstat WHERE player_id = $player_id AND type = 3 AND acc_id = " . $acc['acc_id'] . " ORDER BY date DESC LIMIT 1";
    $result = dbquery ($query);
    return dbarray ($result);
}

// Текущие исследования альянса.
function LastAllyResearch ($acc, $ally_id)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."astat WHERE ally_id = $ally_id AND type = 3 AND acc_id = " . $acc['acc_id'] . " ORDER BY date DESC LIMIT 1";
    $result = dbquery ($query);
    return dbarray ($result);
}

// Загрузить всю историю статистики игрока.
function LoadStat ($acc, $player_id)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."pstat WHERE player_id = $player_id AND acc_id = " . $acc['acc_id'] . " ORDER BY date ASC";
    return dbquery ($query);
}

// Загрузить всю историю статистики альянса.
function LoadAllyStat ($acc, $ally_id)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."astat WHERE ally_id = $ally_id AND acc_id = " . $acc['acc_id'] . " ORDER BY date ASC";
    return dbquery ($query);
}

// Загрузить всю статистику аккаунта за последний месяц.
function LoadMonthStat ($acc)
{
    global $db_prefix;
    $time_from = GetLastUpdate ($acc) - 50 * 24 * 60 * 60;
    $query = "SELECT * FROM ".$db_prefix."pstat WHERE date >= $time_from AND acc_id = " . $acc['acc_id'];
    return dbquery ($query);
}

// Загрузить всю статистику аккаунта за последний месяц (альянсы).
function LoadAllyMonthStat ($acc)
{
    global $db_prefix;
    $time_from = GetLastUpdate ($acc) - 50 * 24 * 60 * 60;
    $query = "SELECT * FROM ".$db_prefix."astat WHERE date >= $time_from AND acc_id = " . $acc['acc_id'];
    return dbquery ($query);
}

// Получить дату последнего обновления базы.
function GetLastUpdate ($acc)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."pstat WHERE date = (SELECT MAX(date) FROM ".$db_prefix."pstat WHERE acc_id = " . $acc['acc_id'] . ")";
    $result = dbquery ($query);
    if ($result == null) return 0;
    $record = dbarray ($result);
    return $record['date'];
}

// Перечислить игроков альянса за последние 7 дней.
function EnumAllyMembers ($acc, $ally_id)
{
    global $db_prefix;
    $time_from = GetLastUpdate ($acc) - 7 * 24 * 60 * 60;
    $query = "SELECT * FROM ".$db_prefix."pstat WHERE date >= $time_from AND ally_id = $ally_id AND acc_id = " . $acc['acc_id'] ;
    return dbquery ($query);
}

// -----------------------------------------------------------------------------------------------------------------
// Сообщения.

function AddMessage ($acc_id, $title, $text)
{
    global $timeshift;
    $id = IncrementDBGlobal ("nextmsg");
    $time_now = time() + $timeshift * 60 * 60;
    $msg = array ( $acc_id, $id, $time_now, $title, $text );
    AddDBRow ( $msg, "msg");
    return $id;
}

function LoadMessage ($msg_id)
{
    global $db_prefix;
    $result = dbquery ( "SELECT * FROM ".$db_prefix."msg WHERE msg_id = $msg_id" );
    return dbarray ($result);
}

function EnumMessages ($acc_id, $page)
{
    global $db_prefix;
    return dbquery ( "SELECT * FROM ".$db_prefix."msg WHERE acc_id = " . $acc_id . " ORDER BY created DESC" );
}

function MessageHTML ($msg)
{
    global $timeshift;
    $now = getdate (time () + $timeshift * 60 * 60);
    $mtime = getdate ($msg['created']);
    if ( $now['year'] == $mtime['year'] && $now['mon'] == $mtime['mon'] && $now['mday'] == $mtime['mday'] ) $timfmt = date ("H:i", $msg['created']);
    else $timfmt = date ("d.m.Y H:i", $msg['created']);

    $res = "";
    $res .= "<tr><td><table class=\"ui-widget ui-widget-content\" style=\"width: 100%\">";
    $res .= "<tr> <td>$timfmt: ".$msg['title']."</td></tr>";
    $res .= "<tr><td >".$msg['text']."</td></tr>";
    $res .= "</table></td></tr>";
    return $res;
}

// Почистить слишком старые записи.
function WipeGarbage ()
{
    global $db_prefix;
    $before = time () - 3 * 30 * 24 * 60 * 60;  // удалить записи старше 3х месяцев.
    dbquery ( "DELETE FROM ".$db_prefix."pstat WHERE date <= $before LIMIT 1000" );
    dbquery ( "DELETE FROM ".$db_prefix."astat WHERE date <= $before LIMIT 200" );
}

// -----------------------------------------------------------------------------------------------------------------
// Ведение статистики по IP-адресам.

// Добавить новую запись в таблицу IP-адресов, если ещё не существует.
function AddIP ()
{
    global $db_prefix;
    $ip = $_SERVER['REMOTE_ADDR'];
    $query = "SELECT * FROM ".$db_prefix."iptable WHERE ip = '".$ip."'";
    $result = dbquery ($query);
    if ( dbrows ($result) == 0 ) {
        $iptab = array ( $ip, 0, 0, 0, 0, 0 );
        AddDBRow ( $iptab, "iptable" );
    }
}

// Получить последнее время создания аккаунта.
function GetIPCreateTime ()
{
    global $db_prefix;
    $ip = $_SERVER['REMOTE_ADDR'];
    $query = "SELECT * FROM ".$db_prefix."iptable WHERE ip = '".$ip."'";
    $result = dbquery ($query);
    $iptab = dbarray ($result);
    return $iptab['last_create'];
}

// Обновить время создания аккаунта.
function UpdateIPCreateTime ($acc_id)
{
    global $db_prefix;
    AddIP ();
    $ip = $_SERVER['REMOTE_ADDR'];
    $query = "UPDATE ".$db_prefix."iptable SET last_create = '".time()."', create_acc = $acc_id WHERE ip = '".$ip."'";
    dbquery ($query);
}

// Обновить время захода на аккаунт.
function UpdateIPLoginTime ($acc_id)
{
    global $db_prefix;
    AddIP ();
    $ip = $_SERVER['REMOTE_ADDR'];
    $query = "UPDATE ".$db_prefix."iptable SET last_login = '".time()."', login_acc = $acc_id WHERE ip = '".$ip."'";
    dbquery ($query);
}

// IP-адрес заблокирован?
function IPBanned ()
{
    global $db_prefix;
    AddIP ();
    $ip = $_SERVER['REMOTE_ADDR'];
    $query = "SELECT * FROM ".$db_prefix."iptable WHERE ip = '".$ip."'";
    $result = dbquery ($query);
    $iptab = dbarray ($result);
    return $iptab['banned'];
}

// -----------------------------------------------------------------------------------------------------------------
// Обновление Галактики и получения списка планет игрока

function ClearGalaxy ($acc_id)
{
    global $db_prefix;
    $query = "DELETE FROM ".$db_prefix."galaxy WHERE acc_id = $acc_id;";
    dbquery ( $query );
}

// type:0 - planet, type:1 - moon
function AddPlanet ($acc_id, $planet_id, $player_id, $g, $s, $p, $name, $type, $diam)
{
    global $db_prefix;
    $planet = array ( '', $acc_id, $planet_id, $player_id, $g, $s, $p, $name, $type, $diam );
    AddDBRow ( $planet, "galaxy" );
}

function GetUserPlanets ($player_id, $acc_id)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."galaxy WHERE player_id = $player_id AND acc_id = $acc_id ORDER BY g ASC, s ASC, p ASC, type ASC";
    $result = dbquery ($query);
    $planets = array ();
    while ( $row = dbarray ($result) ) { $planets[] = $row; }
    return $planets;
}

function EnumPlanets ( $acc_id, $g, $s )
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."galaxy WHERE acc_id = $acc_id AND g = $g AND s = $s ORDER BY g ASC, s ASC, p ASC, type ASC";
    return dbquery ($query);
}

// -----------------------------------------------------------------------------------------------------------------
// Статусы игроков

function GetLastStatus ($player_id, $acc_id)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."pstat WHERE status <> '?' ORDER BY date DESC LIMIT 1";
    $result = dbquery ($query);
    if ( dbrows ($result) == 0 ) return '?';
    $row = dbarray ($result);
    return $row['status'];
}

function ColorStatusString ($status)
{
    if ( $status === 'vI' ) return " (<font color=cyan>РО</font> <font color=gray>I</font>)";
    else if ( $status === 'vi' ) return " (<font color=cyan>РО</font> <font color=gray>i</font>)";
    else if ( $status === 'v' ) return " (<font color=cyan>РО</font>)";
    else if ( $status === 'i' ) return " (<font color=gray>i</font>)";
    else if ( $status === 'I' ) return " (<font color=gray>I</font>)";
    else return "";
}

// -----------------------------------------------------------------------------------------------------------------
// Декорирование страницы.

function PageHeader ($title="Звездовод")
{
    ob_start ();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="keywords" content="Звездовод Ripper OGame" />
    <meta name="description" content="Звездовод - утилита для слежения за онлайном неактивных игроков для браузерной игры OGame" />
    <meta name="author" content="Andorianin OGame.ru Team" />
    <title><?=$title;?></title>
    <script type="text/javascript">
        var sig = "<?=$_GET['sig'];?>";
    </script>

    <script type="text/javascript" src="jquery/jquery-1.8.0.min.js"></script>
    <script type="text/javascript" src="jquery/jquery-ui-1.8.23.custom.min.js"></script>
    <script type="text/javascript" src="jquery/wtooltip.min.js"></script> 
    <script type="text/javascript" src="jquery/jquery-buttons.js"></script>
    <script type="text/javascript" src="jquery/jHtmlArea-0.7.0.min.js"></script>
    <script type="text/javascript" src="jquery/jHtmlArea.ColorPickerMenu-0.7.0.min.js"></script>
    <script type="text/javascript" src="ripper.user.js"></script>

    <link rel="Stylesheet" type="text/css" href="jquery/jHtmlArea.css" />
    <link rel="Stylesheet" type="text/css" href="jquery/jHtmlArea.ColorPickerMenu.css" />
    <link type="text/css" href="css/ripper.css" rel="stylesheet" /> 
    
    <script type="text/javascript">
        comtheme = "trontastic";
        // Подключить стиль оформления.
        document.write ("<link type=\"text/css\" href=\"jquery/themes/"+comtheme+"/ui.all.css\" rel=\"stylesheet\" />");
    </script>
</head>
<body onload="RipperBodyLoad();">
<?
}

function PageFooter ($acc=null)
{
    echo "</body>\n";
    echo "</html>\n";
    if ($acc) AddTraffic ( $acc, strlen (ob_get_contents()), 1 );
    ob_end_flush ();
    die ();
}

function PageMenu ($acc)
{
    echo "<div id=\"pane_menu\" class=\"ui-widget-content ui-corner-all\">\n";
    echo "    <table>\n";
    if ($acc) {
        echo "    <tr><td> <a href=\"". scriptname()."?page=overview&sig=".$_GET['sig'] ."\" id=\"menuOverview\" class=\"fg-button ui-state-default ui-corner-all wide\">Обзор</a> </td></tr>\n";
        echo "    <tr><td> <a href=\"". scriptname()."?page=legend&sig=".$_GET['sig'] ."\" id=\"menuLegend\" class=\"fg-button ui-state-default ui-corner-all wide\">Легенда</a> </td></tr>\n"; 
        echo "    <tr><td> <a href=\"". scriptname()."?page=galaxy&sig=".$_GET['sig'] ."\" id=\"menuGalaxy\" class=\"fg-button ui-state-default ui-corner-all wide\">Галактика</a> </td></tr>\n";
        echo "    <tr><td> <a href=\"". scriptname()."?page=update&sig=".$_GET['sig'] ."\" id=\"menuUpdate\" class=\"fg-button ui-state-default ui-corner-all wide\">Обновить</a> </td></tr>\n";
        echo "    <tr><td> <a href=\"". scriptname()."?page=help&sig=".$_GET['sig'] ."\" id=\"menuHelp\" class=\"fg-button ui-state-default ui-corner-all wide\">Справка</a> </td></tr>\n";
        echo "    <tr><td> <a href=\"". scriptname() ."\" id=\"menuExit\" class=\"fg-button ui-state-default ui-corner-all wide\">Выход</a> </td></tr>\n";
    }
    else {
        echo "    <tr><td> <a href=\"". scriptname() ."\" id=\"menuExit\" class=\"fg-button ui-state-default ui-corner-all wide\">На главную</a> </td></tr>\n";
    }
    echo "    </table>\n";
    echo "</div>\n\n";
}

function PageSignature ($acc)
{
    echo "<div id=\"pane_sig\" class=\"ui-widget-content ui-corner-tl\">\n";
    echo "    <table>\n";
    if ( $acc['u_admin'] ) {
        echo "    <tr><td> <label><font color=\"red\">Сигнатура администратора</font></label></td><td> \n";
        echo "    <input type=\"text\" size=\"33\" class=\"ui-state-default ui-corner-all\" onclick=\"this.select();\" value=\"".$acc['sig']."\"> </td></tr>\n";
    }
    if ( $acc['u_update'] ) {
        echo "    <tr><td> <label><font color=\"gold\">Сигнатура для обновления</font></label></td><td> \n";
        echo "    <input type=\"text\" size=\"33\" class=\"ui-state-default ui-corner-all\" onclick=\"this.select();\" value=\"".$acc['sig_up']."\"> </td></tr>\n";
    }
    if ( $acc['u_view'] ) {
        echo "    <tr><td> <label><font color=\"lime\">Сигнатура для просмотра</font></label></td><td> \n";
        echo "    <input type=\"text\" size=\"33\" class=\"ui-state-default ui-corner-all\" onclick=\"this.select();\" value=\"".$acc['sig_view']."\"> </td></tr>\n";
    }
    echo "    </table>\n";
    echo "</div>\n";
    echo "<script type=\"text/javascript\">\n";
    echo "    if( location.href.indexOf ('&lgn') >= 0 ) $('#pane_sig').fadeTo (1500, 0.0);\n";
    echo "    else $('#pane_sig').fadeTo (0, 0.0);\n";
    echo "    $('#pane_sig').hover(\n";
    echo "        function() { $(this).fadeTo (\"slow\", 1.0); },\n";
    echo "        function() { $(this).fadeTo (\"slow\", 0.0); }\n";
    echo "    );\n";
    echo "</script>\n\n";
}

function PageUniverse ($acc)
{
    echo "<div id=\"pane_uni\">\n";
    echo GetUniverseHTML ( $acc );
    echo "\n</div>\n\n";
}

// -----------------------------------------------------------------------------------------------------------------
// Установка программы.

function PageInstall ()
{
    PageHeader ("Звездовод - Установка");

    if ( method() == "POST") {
        // Попробовать соединиться с базой данных.
        $db_connect = @mysql_connect($_POST['db_host'], $_POST['db_user'], $_POST['db_pass']);
        $db_select = @mysql_select_db($_POST['db_name']);
        echo "<script type=\"text/javascript\">\n";
        echo "    $(function() {\n";
        if ( $db_connect && $db_select )
        {
            echo "        $(\"#install_content\").hide (); \n";
            echo "        $(\"#dialogSuccess\").dialog({   \n";
            echo "            bgiframe: true, modal: true, \n";
            echo "            close: function(event, ui) { window.location = \"".scriptname()."\"; },  \n";
            echo "            buttons: { \"На главную\": function() { $(this).dialog('close'); window.location = \"".scriptname()."\"; } }   \n";
            ResetDatabase ();
            SaveConfigFile ();
        }
        else
        {
            echo "        $(\"#dialogFailed\").dialog({   \n";
            echo "            width: 400, bgiframe: true, modal: true, \n";
            echo "            close: function(event, ui) { window.location = \"".scriptname()."\"; },  \n";
            echo "            buttons: { \"Ok\": function() { $(this).dialog('close'); window.location = \"".scriptname()."\"; } }   \n";
        }
        echo "        });\n";
        echo "    });\n";
        echo "</script>\n\n";
    }

    echo "<div id=\"install_content\" class=\"ui-widget-content ui-corner-all\">\n";
    echo "    <table style=\"width: 100%\"><tr><td class=\"ui-widget-header\"><span class=\"header\">Ripper</span> - Установка программы.</td></tr></table>\n";
    echo "    <form id=\"installform\" action=\"".scriptname()."\" method=\"POST\">\n";
    echo "    <table>\n";
    echo "    <tr><td>Хост</td>            <td><input name=\"db_host\" type=\"text\" size=\"12\" value=\"localhost\"></td></tr>\n";
    echo "    <tr><td>Пользователь</td>    <td><input name=\"db_user\" type=\"text\" size=\"12\"></td></tr>\n";
    echo "    <tr><td>Пароль</td>          <td><input name=\"db_pass\" type=\"password\" size=\"12\"></td></tr>\n";
    echo "    <tr><td>Название БД</td>     <td><input name=\"db_name\" type=\"text\" size=\"12\"></td></tr>\n";
    echo "    <tr><td>Префикс таблиц</td>  <td><input name=\"db_prefix\" type=\"text\" size=\"12\" value=\"rip_\"></td></tr>\n";
    echo "    <tr><td>Секретное слово</td> <td><input name=\"db_secret\" type=\"text\" size=\"12\"></td></tr>\n";
    echo "    <tr><td>&nbsp;</td><td><a href=\"#\" id=\"installRipper\" class=\"fg-button ui-state-default ui-corner-all\">Установить</a></td></tr>\n";
    echo "    </table>\n";
    echo "    </form>\n";
    echo "</div>\n";

    echo "<div id=\"dialogSuccess\" title=\"Установка Звездовода\" style=\"display: none;\">\n";
    echo "  <p>\n";
    echo "      <span class=\"ui-icon ui-icon-circle-check\" style=\"float:left; margin:0 7px 50px 0;\"></span>\n";
    echo "      Установка успешно завершена.\n";
    echo "  </p>\n";
    echo "</div>\n\n";
    echo "<div id=\"dialogFailed\" title=\"Установка Звездовода\" style=\"display: none;\">\n";
    echo "  <p>\n";
    echo "      <span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 50px 0;\"></span>\n";
    echo "      <b><font color=red>Невозможно соединиться с базой данных MySQL.</font></b>\n";
    echo "          <p> " . mysql_error() . " </p> \n";
    echo "  </p>\n";
    echo "</div>\n\n";

    PageFooter ();
}

// -----------------------------------------------------------------------------------------------------------------
// Остальные страницы.

require_once "ripper_home.php";
require_once "ripper_create.php";
require_once "ripper_overview.php";
require_once "ripper_shout.php";
require_once "ripper_pstat.php";
require_once "ripper_astat.php";
require_once "ripper_help.php";
require_once "ripper_legend.php";
require_once "ripper_update.php";
require_once "ripper_galaxy.php";

// -----------------------------------------------------------------------------------------------------------------
// Выбор страницы.

if ( !file_exists ( "ripper_config.php") ) PageInstall ();
if ( key_exists ("page", $_GET) && $_GET["page"] === "create" && method() === "GET" ) PageCreate ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "overview" && method() === "GET" ) PageOverview ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "pstat" && method() === "GET" ) PagePlayerStat ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "astat" && method() === "GET" ) PageAllyStat ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "update" ) PageUpdate ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "legend" && method() === "GET" ) PageLegend ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "help" && method() === "GET" ) PageHelp ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "shout" && method() === "POST" ) PageShout ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "galaxy" && method() === "GET" ) PageGalaxy ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "autoupdate" && method() === "POST" ) PageAutoUpdate ();
else PageHome ();

?>