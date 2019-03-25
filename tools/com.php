<?php

// Командир.

// Глобальные переменные.
$version = "0.01";
$GlobalUser = array ();

// Символьные описания объектов
$desc[1] = "Рудник по добыче металла";
$desc[] = "Рудник по добыче кристалла";
$desc[] = "Синтезатор дейтерия";
$desc[] = "Солнечная электростанция";
$desc[12] = "Термоядерная электростанция";
$desc[14] = "Фабрика роботов";
$desc[] = "Фабрика нанитов";
$desc[21] = "Верфь";
$desc[] = "Хранилище металла";
$desc[] = "Хранилище кристалла";
$desc[] = "Ёмкость для дейтерия";
$desc[31] = "Исследовательская лаборатория";
$desc[33] = "Терраформер";
$desc[] = "Склад альянса";
$desc[41] = "Лунная база";
$desc[] = "Сенсорная фаланга";
$desc[] = "Ворота";
$desc[] = "Ракетная шахта";
$buildmap = array (1, 2, 3, 4, 12, 14, 15, 21, 22, 23, 24, 31, 33, 34, 41, 42, 43, 44);
$desc[106] = "Шпионаж";
$desc[108] = "Компьютерная технология";
$desc[] = "Оружейная технология";
$desc[] = "Щитовая технология";
$desc[] = "Броня космических кораблей";
$desc[113] = "Энергетическая технология";
$desc[] = "Гиперпространственная технология";
$desc[] = "Реактивный двигатель";
$desc[117] = "Импульсный двигатель";
$desc[] = "Гиперпространственный двигатель";
$desc[120] = "Лазерная технология";
$desc[] = "Ионная технология";
$desc[] = "Плазменная технология";
$desc[] = "Межгалактическая исследовательская сеть";
$desc[] = "Экспедиционная технология";
$desc[199] = "Гравитационная технология";
$techmap = array (106, 108, 109, 110, 111, 113, 114, 115, 117, 118, 120, 121, 122, 123, 124, 199);
$desc[202] = "Малый транспорт";
$desc[] = "Большой транспорт";
$desc[] = "Лёгкий истребитель";
$desc[] = "Тяжёлый истребитель";
$desc[] = "Крейсер";
$desc[] = "Линкор";
$desc[] = "Колонизатор";
$desc[] = "Переработчик";
$desc[] = "Шпионский зонд";
$desc[] = "Бомбардировщик";
$desc[] = "Солнечный спутник";
$desc[] = "Уничтожитель";
$desc[] = "Звезда смерти";
$desc[] = "Линейный крейсер";
$fleetmap = array (202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215 );
$desc[401] = "Ракетная установка";
$desc[] = "Лёгкий лазер";
$desc[] = "Тяжёлый лазер";
$desc[] = "Пушка Гаусса";
$desc[] = "Ионное орудие";
$desc[] = "Плазменное орудие";
$desc[] = "Малый щитовой купол";
$desc[] = "Большой щитовой купол";
$desc[502] = "Ракета-перехватчик";
$desc[] = "Межпланетная ракета";
$defmap = array (401, 402, 403, 404, 405, 406, 407, 408, 502, 503 );

// Ранги пользователей: 0x20 Дипломат, 0x10 Обновлять галактику, 0x08 Обновлять статистику, 0x04 - Добавлять шпионские доклады,
// 0x02 - Поиск игроков и альянсов, 0x01 - Поиск докладов
// Дипломатические ранги: 0 - без статуса, 1 - свой, 2 - академия, 3 - враг, 4 - союзник
// Режимы пользователей: 8 - (i), 4 - (iI), 2 - (РО), 1 - (з)

// *******************************************************************************
// Сервисные и вспомогательные функции

header('Pragma:no-cache');

function nicenum ($number) { return number_format($number,0,",","."); }
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

function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
  $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
  mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
}

// Работа с базой данных.
//

// Подключиться к базе данных MySQL.
require_once "db.php";
if ( file_exists ("config.php") )
{
    require_once "config.php";
    dbconnect ($db_host, $db_user, $db_pass, $db_name);
    dbquery("SET NAMES 'utf8';");
    dbquery("SET CHARACTER SET 'utf8';");
    dbquery("SET SESSION collation_connection = 'utf8_general_ci';");
}

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
    foreach ($row as $i=>$entry)
    {
        if ($i != 0) $opt .= ", ";
        $opt .= "'".$row[$i]."'";
    }
    $opt .= ")";
    $query = "INSERT INTO ".$db_prefix."$tabname VALUES".$opt;
    dbquery( $query);
}

// Установка скрипта.
//

// Сохранить файл конфигурации.
function SaveConfigFile ()
{
    $file = fopen ("config.php", "wb");
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
    $tabs = array ('globals', 'unis', 'users', 'galaxy', 'fleet', 'spy', 'astat', 'pstat', 'pmode');
    $globalcols = array ( 'nextuser', 'nextuni', 'nextfleet' );
    $globaltype = array (  'INT', 'INT', 'INT' );
    $unicols = array ( 'uni_id', 'name', 'owner_id', 'tag' );
    $unitype = array (  'INT', 'CHAR(33)', 'INT', 'CHAR(9)' );
    $usercols = array ( 'user_id', 'com', 'login', 'gamename', 'password', 'email', 'uni_id', 'session', 'prsession', 'ipaddr', 'logintime', 'logins', 'lastclick',
                              'validated', 'validate_until', 'ack', 'rank', 'invite', 'invite_until', 'invite_id', 'signature', 'remove', 'remove_until', 'last_g', 'last_s' );
    $usertype = array (  'INT', 'INT', 'CHAR(21)', 'CHAR(21)', 'CHAR(33)', 'TEXT', 'INT', 'CHAR(13)', 'CHAR(33)', 'TEXT', 'INT UNSIGNED', 'INT', 'INT UNSIGNED',
                              'INT', 'INT UNSIGNED', 'CHAR(33)', 'INT', 'CHAR(33)', 'INT UNSIGNED', 'INT', 'CHAR(33)', 'INT', 'INT UNSIGNED', 'INT', 'INT' );
    $galacols = array ( 'uni_id', 'owner_id', 'g', 's', 'p', 'type', 'name', 'diam', 'temp', 'dm', 'dk', 'destroyed', 'activity', 'date', 'added_by' );
    $galatype = array (  'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'CHAR(32)', 'INT', 'INT', 'DOUBLE', 'DOUBLE', 'INT', 'INT', 'INT UNSIGNED', 'INT' );
    $fleetcols = array ( 'fleet_id', 'owner_id', 'public_id', 'g', 's', 'p', 'moon', 'f202', 'f203', 'f204', 'f205', 'f206', 'f207', 'f208', 'f209', 'f210', 'f211', 'f212', 'f213', 'f214', 'f215' );
    $fleettype = array ( 'INT', 'INT', 'CHAR(32)', 'INT', 'INT', 'INT', 'INT', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED', 'INT UNSIGNED' );
    $spycols = array ( 'uni_id', 'added_by', 'spyid', 'player', 'planet', 'g', 's', 'p', 'moon', 'level', 'date', 'm', 'k', 'd', 'e', 'counter', 
                             'o202', 'o203', 'o204', 'o205', 'o206', 'o207', 'o208', 'o209', 'o210', 'o211', 'o212', 'o213', 'o214', 'o215',
                             'o401', 'o402', 'o403', 'o404', 'o405', 'o406', 'o407', 'o408', 'o502', 'o503', 
                             'o1', 'o2', 'o3', 'o4', 'o12', 'o14', 'o15', 'o21', 'o22', 'o23', 'o24', 'o31', 'o33', 'o34', 'o41', 'o42', 'o43', 'o44',
                             'o106', 'o108', 'o109', 'o110', 'o111', 'o113', 'o114', 'o115', 'o117', 'o118', 'o120', 'o121', 'o122', 'o123', 'o124', 'o199' );
    $spytype = array ( 'INT', 'INT', 'CHAR(32)', 'CHAR(32)', 'CHAR(32)', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT UNSIGNED', 'DOUBLE', 'DOUBLE', 'DOUBLE', 'INT', 'INT', 
                              'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 
                              'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 
                              'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 
                              'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT' );
    $astatcols = array ( 'uni_id', 'allyid', 'name', 'members', 'type', 'place', 'score', 'date', 'added_by'  );
    $astattype = array ( 'INT', 'INT', 'CHAR(32)', 'INT', 'INT', 'INT', 'INT UNSIGNED', 'INT UNSIGNED', 'INT' );
    $pstatcols = array ( 'uni_id', 'playerid', 'name', 'allyid', 'g', 's', 'p', 'type', 'place', 'score', 'date', 'added_by' );
    $pstattype = array ( 'INT', 'INT', 'CHAR(32)', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT', 'INT UNSIGNED', 'INT UNSIGNED', 'INT' );
    $pmodecols = array ( 'uni_id', 'playerid', 'status', 'diplo', 'date', 'added_by' );
    $pmodetype = array ( 'INT', 'INT', 'INT', 'INT', 'INT UNSIGNED', 'INT' );
    $tabrows = array (&$globalcols, &$unicols, &$usercols, &$galacols, &$fleetcols, &$spycols, &$astatcols, &$pstatcols, &$pmodecols);
    $tabtypes = array (&$globaltype, &$unitype, &$usertype, &$galatype, &$fleettype, &$spytype, &$astattype, &$pstattype, &$pmodetype);

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

// Вселенные.
//

function LoadUni ($uni_id)
{
    global $db_prefix;
    $result = dbquery ( "SELECT * FROM ".$db_prefix."unis WHERE uni_id = $uni_id" );
    return dbarray ($result);
}

function RenameMyAlly ( $name )
{
    global $GlobalUser, $db_prefix;
    if (!$GlobalUser['com']) return "";
    if ( mb_strlen ($name, "UTF-8") < 3 ) return "Аббревиатура альянса должна иметь длину 3-8 символов.";
    if ( mb_strlen ($name, "UTF-8") > 8 ) return "Аббревиатура альянса должна иметь длину 3-8 символов.";
    dbquery ( "UPDATE ".$db_prefix."unis SET tag = '".$name."' WHERE uni_id = ".$GlobalUser['uni_id'] );
    return "";
}

function RenameMyUni ( $name )
{
    global $GlobalUser, $db_prefix;
    if (!$GlobalUser['com']) return "";
    if ( mb_strlen ($name, "UTF-8") == 0 ) return "Командир должен указать название Вселенной.";
    if ( mb_strlen ($name, "UTF-8") > 32 ) return "Длина названия Вселенной не должна превышать 32 символа";
    dbquery ( "UPDATE ".$db_prefix."unis SET name = '".$name."' WHERE uni_id = ".$GlobalUser['uni_id'] );
    return "";
}

// Управление пользовательскими аккаунтами.
//

// Выслать приветственное письмо с ссылкой для активации аккаунта.
function SendGreetingsMail ($login, $pass, $email, $allytag, $uniname, $com, $ack)
{
    if ($com === "on") { 
        $comstr = "командирский";
        $comstr2 = "Вы создали Вселенную $uniname и закрепили за ней альянс $allytag\n\n";
    }
    $text = "Приветствуем, $login!\n\n" .
               "Вы зарегистрировали $comstr аккаунт на сервере ".hostname().scriptname()."\n" .
               "Ваш пароль: $pass\n" .
               $comstr2 .
               "Ваша ссылка для активации аккаунта: ".hostname().scriptname()."?page=validate&ack=$ack\n" .
               "Неактивированный аккаунт будет удален через три дня!\n\n" .
               "Удачной охоты!";
     mail_utf8 ( $email, "Добро пожаловать в Командиры ", $text, "From: Командир <ogamespec@gmail.com>");
}

function IsUserExist ($login)
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."users WHERE login = '".$login."'";
    $result = dbquery ($query);
    return dbrows ($result);
}

function IsEmailExist ($email)
{
    global $db_prefix;
    $email = mb_strtolower ($email, 'UTF-8');
    $query = "SELECT * FROM ".$db_prefix."users WHERE email = '".$email."'";
    $result = dbquery ($query);
    return dbrows ($result);
}

// Добавить пользователя.
// Проверок на правильность не делается! Этим занимается процедура регистрации.
// Возвращает ID созданного пользователя.
function AddUser ( $login, $pass, $email, $gamename, $allytag, $uniname, $com )
{
    global $db_prefix, $db_secret;
    $email = mb_strtolower ($email, 'UTF-8');
    $md = md5 ($pass . $db_secret);
    $ack = md5(time ().$db_secret);
    $sig = md5(time ().$db_secret.$ack);
    if ( $com === "on" ) $comander = 1;
    else $comander = 0;

    // Если регистрируется командирский аккаунт - создать для него Вселенную.
    $query = "SELECT * FROM ".$db_prefix."globals";
    $result = dbquery ($query);
    $globals = dbarray ($result);
    if ( $comander )
    {
        $uid = $globals['nextuni']++;
        $query = "UPDATE ".$db_prefix."globals SET nextuni = ".$globals['nextuni'];
        dbquery ($query);
    }
    else $uid = 0;

    // Получить следующий уникальный номер и увеличить его на 1 для следующего пользователя.
    $id = $globals['nextuser']++;
    $query = "UPDATE ".$db_prefix."globals SET nextuser = ".$globals['nextuser'];
    dbquery ($query);

    if ( $comander )
    {
        $uni = array( $uid, $uniname, $id, $allytag );
        AddDBRow ( $uni, "unis");
        $rank = 0x3f;    // Командир автоматически имеет все права.
    }
    else $rank = 0;
    $user = array( $id, $comander, $login, $gamename, $md, $email, $uid, "", "", "0.0.0.0", 0, 0, 0,
                        0, time()+3*24*60*60, $ack, $rank, "0", 0, 0, $sig, 0, 0, 1, 1 );
    AddDBRow ( $user, "users");

    // Выслать письмо с активационной ссылкой.
    SendGreetingsMail ($login, $pass, $email, $allytag, $uniname, $com, $ack);

    return $id;
}

// Загрузить пользователя.
function LoadUser ( $user_id )
{
    global $db_prefix;
    $result = dbquery ( "SELECT * FROM ".$db_prefix."users WHERE user_id = $user_id" );
    return dbarray ($result);
}

// Перечислить пользователей вселенной.
function EnumUsers ( $uni_id )
{
    global $db_prefix;
    return dbquery ( "SELECT * FROM ".$db_prefix."users WHERE uni_id = $uni_id" );
}

// Удалить пользователя.
function RemoveUser ( $user_id )
{
    global $db_prefix;
    $user = LoadUser ( $user_id );
    dbquery ( "DELETE FROM ".$db_prefix."users WHERE user_id = $user_id" );
}

// Проверить пароль. Возвращает 0, или ID пользователя.
function CheckPassword ($name, $pass, $passmd="")
{
    global $db_prefix, $db_secret;
    if ( $passmd === "") $md = md5 ($pass . $db_secret);
    else $md = $passmd;
    $query = "SELECT * FROM ".$db_prefix."users WHERE login = '".$name."' AND password = '".$md."'";
    $result = dbquery ($query);
    if (dbrows ($result) == 0) return 0;
    $user = dbarray ($result);
    return $user['user_id'];
}

// Обновить активность.
function UpdateLastClick ( $user_id )
{
    global $db_prefix;
    dbquery ( "UPDATE ".$db_prefix."users SET lastclick = ".time()." WHERE user_id = $user_id" );
}

// Сменить адрес электронной почты.
function ChangeEmail ( $email, $pass )
{
    global $GlobalUser, $db_prefix, $db_secret;
    $email = mb_strtolower ($email, 'UTF-8');
    $user = $GlobalUser; $user_id = $GlobalUser['user_id'];
    if ($user['email'] === $email) return "";
    if ( IsEmailExist ($email) ) return "Такой адрес уже используется";
    if ( !CheckPassword ($user['login'], $pass)) return "Чтобы сменить адрес электронной почты нужно указать пароль";
    if ( !eregi ("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email) ) return "Введите правильный почтовый адрес, например john@doe.com";
    $ack = md5(time ().$db_secret); $until = time()+3*24*60*60;
    dbquery ( "UPDATE ".$db_prefix."users SET email = '".$email."', validated = 0, validate_until = $until, ack = '".$ack."' WHERE user_id = $user_id" );
    $GlobalUser['email'] = $email;
    return "Электронный адрес изменен. Необходимо повторно пройти активацию.";
}

// Сменить пароль.
function ChangePassword ( $oldpass, $newpass1, $newpass2 )
{
    global $GlobalUser, $db_prefix, $db_secret;
    if ( $newpass1 === "" || $newpass2 === "" ) return "";
    if ( $newpass1 !== $newpass2 || !CheckPassword ($GlobalUser['login'], $oldpass)  ) return "Неверный старый пароль, или новый пароль неверно подтвержден.";
    if ( mb_strlen ($newpass1, "UTF-8") < 8 ) return "Минимальная длина пароля должна составлять 8 символов";
    if ( mb_strlen ($newpass1, "UTF-8") > 32 ) return "Длина пароля не должна превышать 32 символа";
    if ( !preg_match ( "/^[_a-zA-Z0-9]+$/", $newpass1 )) return "Пароль может содержать только символы a-z, A-Z, цифры 0-9 и подчеркивание _";
    $md = md5 ($newpass1 . $db_secret);
    dbquery ( "UPDATE ".$db_prefix."users SET password = '".$md."' WHERE user_id = " . $GlobalUser['user_id'] );
    Logout ( $_GET['session'] );
    return "Пароль изменен. Перезайдите.";
}

// Сменить игровое имя.
function ChangeGamename ( $gamename )
{
    global $GlobalUser, $db_prefix;
    if ( mb_strlen ($gamename, "UTF-8") > 20 ) return "Длина игрового имени не должна превышать 20 символов";
    dbquery ( "UPDATE ".$db_prefix."users SET gamename = '".$gamename."' WHERE user_id = " . $GlobalUser['user_id'] );
    $GlobalUser['gamename'] = $gamename;
    return "";
}

// Поставить или снять аккаунт с удаления
function RemoveAccount ( $remove, $date, $pass )
{
    global $GlobalUser, $db_prefix;
    if ( $GlobalUser['remove'] == $remove ) return "";
    if ( $remove && !CheckPassword ($GlobalUser['login'], $pass) ) return "Чтобы поставить аккаунт на удаление вы должны указать старый пароль";
    dbquery ( "UPDATE ".$db_prefix."users SET remove = $remove, remove_until = $date WHERE user_id = " . $GlobalUser['user_id'] );
    $GlobalUser['remove'] = $remove;
    $GlobalUser['remove_until'] = $date;
    if ($remove) return "Аккаунт поставлен на удаление.";
    else return "";
}

// Выслать активационную ссылку.
function SendActivationMail ( $user_id )
{
    global $db_prefix, $db_secret;
    $user = LoadUser ($user_id);
    if ( $user['validated'] ) return;
    $ack = md5(time ().$db_secret);
    dbquery ( "UPDATE ".$db_prefix."users SET ack = '".$ack."' WHERE user_id = $user_id" );
    $uni = LoadUni ( $user['uni_id'] );
    SendGreetingsMail ($user['login'], "***", $user['email'], $uni['tag'], $uni['name'], $user['com'] ? "on" : "off", $ack);
}

// Пригласить пользователя в альянс (логин).
function InviteUser ($name)
{
    global $GlobalUser, $db_prefix, $db_secret;
    if (!$GlobalUser['com']) return "";
    $query = "SELECT * FROM ".$db_prefix."users WHERE login = '".$name."' AND uni_id = 0";
    $result = dbquery ($query);
    if ( dbrows ($result) == 0) return "Такой пользователь не зарегистрирован";
    $user = dbarray ($result);
    if ( $user['uni_id'] ) return "Пользователь $name уже в альянсе.";
    if ( $user['invite'] !== "0" ) return "Пользователь $name уже получил приглашение присоединиться в альянс.";
    $ack = md5(time ().$db_secret); $until = time() + 3*24*60*60;
    $query = "UPDATE ".$db_prefix."users SET invite = '".$ack."', invite_until = $until, invite_id = ".$GlobalUser['user_id'].", rank = 0 WHERE user_id = ".$user['user_id'];
    dbquery ($query);
    return "Приглашение пользователю $name выслано.";
}

// Подтвердить приглашение.
function AcceptInvite ($user_id)
{
    global $db_prefix;
    $user = LoadUser ($user_id);
    $com = LoadUser ($user['invite_id']);
    if (!$com['com']) return;
    $query = "UPDATE ".$db_prefix."users SET rank = 63, invite = '0', invite_until = 0, invite_id = 0, uni_id = ".$com['uni_id']." WHERE user_id = $user_id";
    dbquery ($query);
}

// Отменить приглашение.
function RejectInvite ($user_id)
{
    global $db_prefix;
    $query = "UPDATE ".$db_prefix."users SET invite = '0', invite_until = 0, invite_id = 0 WHERE user_id = $user_id";
    dbquery ($query);
}

// Получить пользователя по сигнатуре.
function LoadUserBySig ($sig)
{
    global $GlobalUser, $db_prefix;
    if ($sig === "") return FALSE;
    $result = dbquery ( "SELECT * FROM ".$db_prefix."users WHERE signature = '".$sig."'" );
    if ( dbrows ($result) == 0 ) return FALSE;
    $GlobalUser = dbarray ($result);
    return TRUE;
}

// Обновить сигнатуру.
function UpdateSignature ()
{
    global $GlobalUser, $db_prefix, $db_secret;
    $sig = md5 (time().$db_secret);
    $query = "UPDATE ".$db_prefix."users SET signature = '".$sig."' WHERE user_id = ".$GlobalUser['user_id'];
    dbquery ($query);
    $GlobalUser['signature'] = $sig;
}

// Покинуть альянс.
function LeaveAlly ()
{
    global $GlobalUser, $db_prefix;
    $query = "UPDATE ".$db_prefix."users SET com = 0, uni_id = 0 WHERE user_id = ".$GlobalUser['user_id'];
    dbquery ($query);
    $GlobalUser['com'] = $GlobalUser['uni_id'] = 0;
}

// Перенять альянс.
function TakeoverAlly ($id)
{
    global $GlobalUser, $db_prefix;
    $user = LoadUser ($id);
    if ($user['uni_id'] != $GlobalUser['uni_id'] || $user['com'] || $GlobalUser['user_id'] == $id) return;
    dbquery ( "UPDATE ".$db_prefix."users SET com = 1 WHERE user_id = ".$user['user_id'] );
    dbquery ( "UPDATE ".$db_prefix."users SET com = 0 WHERE user_id = ".$GlobalUser['user_id'] );
    $GlobalUser['com'] = 0;
}

// Сессии.
//

// Вызывается при нажатии на "Выход" в меню.
function Logout ( $session )
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."users WHERE session = '".$session."'";
    $result = dbquery ($query);
    if (dbrows ($result) == 0) return;
    $user = dbarray ($result);
    $user_id = $user['user_id'];
    $query = "UPDATE ".$db_prefix."users SET session = '' WHERE user_id = $user_id";
    dbquery ($query);
    setcookie ( "prsess_".$user_id, '');
}

// Login - Вызывается с главной страницы, после регистрации или активации нового пользователя.
function Login ( $login, $pass, $passmd="" )
{
    global $db_prefix;
    if  ( $user_id = CheckPassword ( $login, $pass, $passmd ) )
    {
        $lastlogin = time ();
        // Создать приватную сессию.
        $prsess = md5 ( $login . $lastlogin . $db_secret);
        // Создать публичную сессию
        $sess = substr (md5 ( $prsess . sha1 ($pass) . $db_secret . $lastlogin), 0, 12);

        // Записать приватную сессию в кукисы и обновить БД.
        setcookie ( "prsess_".$user_id, $prsess, time()+60*60*24);
        $query = "UPDATE ".$db_prefix."users SET logintime = $lastlogin, session = '".$sess."', prsession = '".$prsess."' WHERE user_id = $user_id";
        dbquery ($query);

        // Записать IP-адрес.
        $ip = $_SERVER['REMOTE_ADDR'];
        dbquery ( "UPDATE ".$db_prefix."users SET ipaddr = '".$ip."' WHERE user_id = $user_id" );

        // Увеличить счетчик посещений.
        $user = LoadUser ( $user_id );
        $user['logins']++;
        dbquery ( "UPDATE ".$db_prefix."users SET logins = ".$user['logins']." WHERE user_id = $user_id" );

        //echo "ID пользователя: $user_id<br>Приватная сессия: $prsess<br>Публичная сессия: $sess<br>IP-адрес: $ip";
        // Редирект на Обзор.
        echo "<html><head><meta http-equiv='refresh' content='0;url=".scriptname()."?page=overview&session=".$sess."&lgn=1' /></head><body></body>";
    }
    else HomePage (true, $login);
}

// Вызывается при загрузке каждой игровой страницы.
function CheckSession ( $session )
{
    global $db_prefix, $GlobalUser;
    // Получить ID-пользователя из публичной сессии.
    $query = "SELECT * FROM ".$db_prefix."users WHERE session = '".$session."'";
    $result = dbquery ($query);
    if (dbrows ($result) == 0) { InvalidSessionPage (); return FALSE; }
    $GlobalUser = dbarray ($result);
    // Удалить неактивированных пользователей.
    if ( !$GlobalUser['validated'] && time () >= $GlobalUser['validate_until'] )  {
        RemoveUser ( $GlobalUser['user_id'] );
        RedirectHome ();
        return FALSE;
    }
    // Удалить пользователей, поставленных на удаление.
    if ( $GlobalUser['remove'] && time () >= $GlobalUser['remove_until'] )  {
        RemoveUser ( $GlobalUser['user_id'] );
        RedirectHome ();
        return FALSE;
    }
    // Удалить просроченные приглашения.
    if ( $GlobalUser['invite'] !== "0" && time () >= $GlobalUser['invite_until'] )  {
        RejectInvite ( $GlobalUser['user_id'] );
        $GlobalUser['invite'] = "0";
    }
    $prsess = $_COOKIE ['prsess_'.$GlobalUser['user_id']];
    if ( $prsess !== $GlobalUser['prsession']) { InvalidSessionPage (); return FALSE; }
    //$ip = $_SERVER['REMOTE_ADDR'];
    //if ( $ip !== $GlobalUser['ipaddr']) { InvalidSessionPage (); return FALSE; }
    UpdateLastClick ($GlobalUser['user_id']);
    return TRUE;
}

// Регистрация нового пользователя (проверки).
//

function RegisterNew ()
{
    $_POST['email'] = mb_strtolower ($_POST['email'], 'UTF-8');
    if ( mb_strlen ($_POST['login'], "UTF-8") < 3 ) RegPage ( $_POST['login'], $_POST['pass'], $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                                 "Неверный логин", "Минимальная длина имени пользователя должна составлять 3 символа" );
    if ( mb_strlen ($_POST['login'], "UTF-8") > 20 ) RegPage ( $_POST['login'], $_POST['pass'], $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                                    "Неверный логин", "Имя пользователя не может превышать 20 символов" );
    if ( preg_match ( '/[<>()\[\]{}\\\\\/\`\"\'.,:;*+]/', $_POST['login'] )) RegPage ( $_POST['login'], $_POST['pass'], $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                                                              "Неверный логин", "Запрещенные символы в имени пользователя" );
    if ( IsUserExist ($_POST['login']) ) RegPage ( $_POST['login'], $_POST['pass'], $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                "Неверный логин", "Такой пользователь уже зарегистрирован" );
    if ( mb_strlen ($_POST['pass'], "UTF-8") < 8 ) RegPage ( $_POST['login'], "", $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                                 "Неверный пароль.", "Минимальная длина пароля должна составлять 8 символов" );
    if ( mb_strlen ($_POST['pass'], "UTF-8") > 32 ) RegPage ( $_POST['login'], "", $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                                    "Неверный пароль.", "Длина пароля не должна превышать 32 символа" );
    if ( !preg_match ( "/^[_a-zA-Z0-9]+$/", $_POST['pass'] )) RegPage ( $_POST['login'], "", $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                                        "Неверный пароль.", "Пароль может содержать только символы a-z, A-Z, цифры 0-9 и подчеркивание _" );
    if ( !eregi ("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['email']) ) RegPage ( $_POST['login'], $_POST['pass'], $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                                                                                                             "Неверный почтовый адрес.", "Введите правильный почтовый адрес, например john@doe.com" );
    if ( IsEmailExist ($_POST['email']) ) RegPage ( $_POST['login'], $_POST['pass'], $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                "Неверный почтовый адрес.", "Такой адрес уже используется" );

    if ( mb_strlen ($_POST['gamename'], "UTF-8") > 20 ) RegPage ( $_POST['login'], $_POST['pass'], $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                                              "Неверное игровое имя", "Длина игрового имени не должна превышать 20 символов" );
    if ( mb_strlen ($_POST['uni'], "UTF-8") == 0 &&  $_POST['com'] === "on" ) RegPage ( $_POST['login'], $_POST['pass'], $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                                                                          "Название Вселенной", "Командир должен указать название Вселенной." );
    if ( mb_strlen ($_POST['allytag'], "UTF-8") < 3 &&  $_POST['com'] === "on" ) RegPage ( $_POST['login'], $_POST['pass'], $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                                                                            "Альянс", "Аббревиатура альянса должна иметь длину 3-8 символов." );
    if ( mb_strlen ($_POST['allytag'], "UTF-8") > 8 &&  $_POST['com'] === "on" ) RegPage ( $_POST['login'], $_POST['pass'], $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                                                                            "Альянс", "Аббревиатура альянса должна иметь длину 3-8 символов." );
    if ( mb_strlen ($_POST['uni'], "UTF-8") > 32 ) RegPage ( $_POST['login'], $_POST['pass'], $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'], 
                                                                                    "Название Вселенной", "Длина названия Вселенной не должна превышать 32 символа" );

    // Добавить пользователя и произвести автоматический вход в программу.
    AddUser ( $_POST['login'], $_POST['pass'], $_POST['email'], $_POST['gamename'], $_POST['allytag'], $_POST['uni'], $_POST['com'] );
    Login ( $_POST['login'], $_POST['pass'] );
}

function ValidateUser ()
{
    global $db_prefix;
    if ( !key_exists ('ack', $_GET)) return;
    $ack = $_GET['ack'];
    $result = dbquery ( "SELECT * FROM ".$db_prefix."users WHERE ack = '".$ack."' AND validated = 0" );
    $rows = dbrows ($result);
    if ($rows == 0) RedirectHome ();
    $user = dbarray ($result);
    dbquery ( "UPDATE ".$db_prefix."users SET validated = 1 WHERE ack = '".$ack."'" );
    Logout ( $user['session'] );
    Login ( $user['login'], "", $user['password'] );
}

// Мой флот.
//

// Добавить (когда $fleet_id = 0) или изменить флот. Возвратить ID добавленного/измененного флота.
function AddFleet ( $g, $s, $p, $moon, $f, $owner_id, $fleet_id=0 )
{
    if ( $fleet_id == 0) {
        $id = IncrementDBGlobal ("nextfleet");
        $pid = "";
    }
    else {
        $id = $fleet_id;
        $oldf = LoadFleet ($fleet_id, $owner_id);
        $pid = $oldf['public_id'];
        DeleteFleet ( $fleet_id, $owner_id );
    }
    $fleet = array( $id, $owner_id, $pid, $g, $s, $p, $moon, $f[202], $f[203], $f[204], $f[205], $f[206], $f[207], $f[208], $f[209], $f[210], $f[211], $f[212], $f[213], $f[214], $f[215] );
    AddDBRow ( $fleet, "fleet");
    return $id;
}

// Загрузить флот.
function LoadFleet ( $fleet_id )
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."fleet WHERE fleet_id = $fleet_id";
    $result = dbquery ($query);
    return dbarray ($result);
}

// Перечислить флоты, возвратить результат SQL-запроса.
function EnumFleet ()
{
    global $db_prefix;
    return dbquery ("SELECT * FROM ".$db_prefix."fleet");
}

// Удалить флот.
function DeleteFleet ( $fleet_id, $owner_id )
{
    global $db_prefix;
    $query = "DELETE FROM ".$db_prefix."fleet WHERE fleet_id = $fleet_id AND owner_id = $owner_id";
    dbquery ($query);
}

// Галактика.
//

function AddDestroyedPlanet ( $uni_id, $time, $g, $s, $p, $act )
{
    global $GlobalUser;
    $planet = array ( $uni_id, 0, $g, $s, $p, 0, "", 0, 0, 0, 0, 1, $act, $time, $GlobalUser['user_id'] );
    AddDBRow ( $planet, "galaxy");
}

function AddPlanet ( $uni_id, $time, $g, $s, $p, $act, $type, $name, $diam, $temp, $dm, $dk, $owner_id )
{
    global $GlobalUser;
    $planet = array ( $uni_id, $owner_id, $g, $s, $p, $type, $name, $diam, $temp, $dm, $dk, 0, $act, $time, $GlobalUser['user_id'] );
    AddDBRow ( $planet, "galaxy");
}

// Загрузить последние параметры планеты или луны на координатах [g:s:p]
function LoadPlanet ( $uni_id, $g, $s, $p, $moon=0 )
{
    global $db_prefix;
    if ($moon) $query = "SELECT * FROM ".$db_prefix."galaxy WHERE uni_id = $uni_id AND g = $g AND s = $s AND p = $p AND type = 0 ORDER BY date DESC";
    else $query = "SELECT * FROM ".$db_prefix."galaxy WHERE uni_id = $uni_id AND g = $g AND s = $s AND p = $p AND type <> 0 ORDER BY date DESC";
    $result = dbquery ($query);
    return dbarray ($result);
}

// Загрузить список планет в системе [g:s]. Выбираются только самые свежие данные.
function EnumPlanets ( $uni_id, $g, $s )
{
    global $db_prefix;
    return dbquery ("SELECT * FROM ".$db_prefix."galaxy WHERE uni_id = $uni_id AND g = $g AND s = $s ORDER BY date DESC, g ASC, s ASC, p ASC LIMIT 30");
}

// Загрузить список всех планет игрока.
function EnumPlayerPlanets ($uni_id, $player_id )
{
    global $db_prefix;
    return dbquery ("SELECT * FROM ".$db_prefix."galaxy WHERE uni_id = $uni_id AND owner_id = $player_id ORDER BY date DESC, g ASC, s ASC, p ASC");
}

// Получить возраст системы.
function GetSystemAge ($uni_id, $g, $s, &$added_by)
{
    global $db_prefix;
    $result = dbquery ("SELECT * FROM ".$db_prefix."galaxy WHERE uni_id = $uni_id AND g = $g AND s = $s AND p = 1 ORDER BY date DESC LIMIT 1");
    if ( dbrows ($result) == 0) { $added_by = 0; return 0; }
    $pl = dbarray ($result);
    $added_by = $pl['added_by'];
    return $pl['date'];
}

// Разобрать "сырой" текст и добавить информацию в базу.
function ParsePlanets ($text)
{
    global $GlobalUser;
    $pnum = $mnum = 0;
    $s = $text;
    while (1) {
        $planet = array ();
        $s = strstr ($s, "pl <");
        if ($s == FALSE) break;
        $s = strstr ($s, "<");
        $tmp = str_between ($s, "<", ">");
        if ($tmp == false) break;
        $s = strstr ($s, ">");

        $fs = $tmp[0];
        $end = strpos ($fs, "<") + 1;
        $part = substr ($fs, $end);
        $planet["time"] = strtok ($part, " ");
        $planet["g"] = strtok (" ");
        $planet["s"] = strtok (" ");
        $planet["p"] = strtok (" ");
        $planet["act"] = strtok (" ");
        $planet["type"] = strtok (" ");

        $start = strpos ($fs, "(", $end+1) + 1;
        $end = strpos ($fs, ")", $end+1);
        $planet["name"] = trim (substr ($fs, $start, $end-$start));

        $part = substr ($fs, $end+1);
        $planet["diam"] = strtok ($part, " ");
        $planet["temp"] = strtok (" ");
        $planet["dm"] = strtok (" ");
        $planet["dk"] = strtok (" ");
        $planet["owner_id"] = strtok (" ");
        if ($planet["owner_id"] == 0) $planet["owner_id"] = $GlobalUser['user_id'];

        AddPlanet ( $GlobalUser['uni_id'], $planet["time"], $planet["g"], $planet["s"], $planet["p"], $planet["act"], $planet["type"],
                        $planet["name"], $planet["diam"], $planet["temp"], $planet["dm"], $planet["dk"], $planet["owner_id"] );
        if ($planet["type"] == 0) $mnum++;
        else $pnum++;
    }

    $s = $text;
    while (1) {
        $planet = array ();
        $s = strstr ($s, "pd <");
        if ($s == FALSE) break;
        $s = strstr ($s, "<");
        $tmp = str_between ($s, "<", ">");
        if ($tmp == false) break;
        $s = strstr ($s, ">");

        $fs = $tmp[0];
        $end = strpos ($fs, "<") + 1;
        $part = substr ($fs, $end);
        $planet["time"] = strtok ($part, " ");
        $planet["g"] = strtok (" ");
        $planet["s"] = strtok (" ");
        $planet["p"] = strtok (" ");
        $planet["act"] = strtok (" ");

        AddDestroyedPlanet ( $GlobalUser['uni_id'], $planet["time"], $planet["g"], $planet["s"], $planet["p"], $planet["act"] );
    }

    $s = $text;
    while (1) {
        $planet = array ();
        $s = strstr ($s, "pe <");
        if ($s == FALSE) break;
        $s = strstr ($s, "<");
        $tmp = str_between ($s, "<", ">");
        if ($tmp == false) break;
        $s = strstr ($s, ">");

        $fs = $tmp[0];
        $end = strpos ($fs, "<") + 1;
        $part = substr ($fs, $end);
        $planet["time"] = strtok ($part, " ");
        $planet["g"] = strtok (" ");
        $planet["s"] = strtok (" ");
        $planet["p"] = strtok (" ");

        AddPlanet ( $GlobalUser['uni_id'], $planet["time"], $planet["g"], $planet["s"], $planet["p"], 60, 999,
                        "", 0, 0, 0, 0, 0 );
    }

    if ( ($pnum + $num) == 0) return "";
    else {
        $res = "Добавлено ";
        if ($pnum) $res .= "$pnum планет";
        if ($pnum && $mnum) $res .= ", ";
        if ($mnum) $res .= "$mnum лун";
        $res .= "<br/>";
        return $res;
    }
}

function SaveLastGalaxy ( $user_id, $g ) { global $db_prefix; dbquery ( "UPDATE ".$db_prefix."users SET last_g = $g WHERE user_id = $user_id" ); }
function SaveLastSystem ( $user_id, $s ) { global $db_prefix; dbquery ( "UPDATE ".$db_prefix."users SET last_s = $s WHERE user_id = $user_id" ); }
function LoadLastGalaxy ( $user_id ) { $user = LoadUser ($user_id); return $user['last_g']; }
function LoadLastSystem ( $user_id ) { $user = LoadUser ($user_id); return $user['last_s']; }

// Статистика.
//

function GetPlayerName ($uni_id, $user_id)
{
    global $db_prefix;
    if ( $user_id < 100000) {
        if ( $user_id == 1 ) return "Legor";
        else {
            $user = LoadUser ( $user_id );
            return $user['gamename'];
        }
    }
    else
    {
        $query = "SELECT * FROM ".$db_prefix."pstat WHERE uni_id = $uni_id AND playerid = $user_id ORDER BY date DESC LIMIT 1";
        $result = dbquery ($query);
        $stat = dbarray ($result);
        if ( $stat == false ) return $user_id;
        else return $stat['name'];
    }
}

function GetPlayerAlly ($uni_id, $user_id)
{
    global $db_prefix;
    if ( $user_id < 100000) {
        if ( $user_id == 1) return 0;
        else return -1;
    }
    else
    {
        $query = "SELECT * FROM ".$db_prefix."pstat WHERE uni_id = $uni_id AND playerid = $user_id ORDER BY date DESC LIMIT 1";
        $result = dbquery ($query);
        $stat = dbarray ($result);
        if ( $stat == false ) return 0;
        else return $stat['allyid'];
    }
}

function GetAllyName ($uni_id, $ally_id)
{
    global $db_prefix;
    if ($ally_id == 0) return "-";
    else if ($ally_id == -1) {
        $result = dbquery ( "SELECT * FROM ".$db_prefix."unis WHERE uni_id = $uni_id" );
        $uni = dbarray ($result);
        return $uni['tag'];
    }
    else
    {
        $query = "SELECT * FROM ".$db_prefix."astat WHERE uni_id = $uni_id AND allyid = $ally_id ORDER BY date DESC LIMIT 1";
        $result = dbquery ($query);
        $stat = dbarray ($result);
        if ( $stat == false ) return "-";
        else return $stat['name'];
    }
}

function LoadPlayerStat ( $uni_id, $player_id, $type )
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."pstat WHERE uni_id = $uni_id AND playerid = $player_id AND type = $type ORDER BY date DESC LIMIT 1";
    $result = dbquery ($query);
    return dbarray ($result);
}

// Разобрать "сырой" текст и добавить информацию в базу.
function ParseStat ($text)
{
    global $GlobalUser;
    $anum = $pnum = 0;            // Статистика альянсов
    $s = $text;
    while (1) {
        $stat = array ();
        $s = strstr ($s, "as <");
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
        $stat["date"] = strtok (" ");

        $astat = array ( $GlobalUser['uni_id'], $stat["allyid"], $stat["name"], $stat["members"], $stat["type"], $stat["place"], $stat["score"], $stat["date"],  $GlobalUser['user_id']);
        AddDBRow ( $astat, "astat");
        $anum++;
    }

    $i = 0;                            // Статистика игроков
    $s = $text;
    while (1) {
        $stat = array ();
        $s = strstr ($s, "ps <");
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
        $stat["date"] = strtok (" ");
        if ( $stat["playerid"] == -1 ) $stat["playerid"] = $GlobalUser['user_id'];

        $pstat = array ( $GlobalUser['uni_id'], $stat["playerid"], $stat["name"], $stat["allyid"], $stat["g"], $stat["s"], $stat["p"], $stat["type"], $stat["place"], $stat["score"], $stat["date"],  $GlobalUser['user_id']);
        AddDBRow ( $pstat, "pstat");
        $pnum++;
    }

    if ( ($anum + $pnum) == 0) return "";
    else {
        $res = "Статистика добавлена (";
        if ($anum) $res .= "$anum альянсов";
        if ($anum && $pnum) $res .= ", ";
        if ($pnum) $res .= "$pnum игроков";
        $res .= ")<br/>";
        return $res;
    }
}

// Шпионские доклады.
//

function GenSpyID ($spy)
{
    global $db_secret;
    $title = "Сырьё на ".$spy['planet']." [".$spy['g'].":".$spy['s'].":".$spy['p']."] (Игрок '".$spy['player']."')";
    $date = " на ".timfmt($spy['date_m'])."-".timfmt($spy['date_d'])." ".timfmt($spy['date_hr']).":".timfmt($spy['date_min']).":".timfmt($spy['date_sec']);
    $id = md5 ($title . $date . $db_secret);
    return $id;
}

function AddSpy ( $uni_id, $added_by, $spy )
{
    $result = EnumSpy ( $uni_id, $spy['g'], $spy['s'], $spy['p'], $spy['moon']);        // Не дублировать одинаковые доклады.
    if ( dbrows ($result) ) {
        $report = dbarray ($result);
        if ( $report['date'] == $spy['date'] && $report['g'] == $spy['g'] && $report['s'] == $spy['s'] && $report['p'] == $spy['p'] && $report['moon'] == $spy['moon'] ) return "0";
    }
    $id = GenSpyID($spy);
    $report = array ( $uni_id, $added_by, $id, $spy['player'], $spy['planet'], $spy['g'], $spy['s'], $spy['p'], $spy['moon'], $spy['level'], $spy['date'], $spy['m'], $spy['k'], $spy['d'], $spy['e'], $spy['counter'],
                             $spy['o202'], $spy['o203'], $spy['o204'], $spy['o205'], $spy['o206'], $spy['o207'], $spy['o208'], $spy['o209'], $spy['o210'], $spy['o211'], $spy['o212'], $spy['o213'], $spy['o214'], $spy['o215'],
                             $spy['o401'], $spy['o402'], $spy['o403'], $spy['o404'], $spy['o405'], $spy['o406'], $spy['o407'], $spy['o408'], $spy['o502'], $spy['o503'], 
                             $spy['o1'], $spy['o2'], $spy['o3'], $spy['o4'], $spy['o12'], $spy['o14'], $spy['o15'], $spy['o21'], $spy['o22'], $spy['o23'], $spy['o24'], $spy['o31'], $spy['o33'], $spy['o34'], $spy['o41'], $spy['o42'], $spy['o43'], $spy['o44'],
                             $spy['o106'], $spy['o108'], $spy['o109'], $spy['o110'], $spy['o111'], $spy['o113'], $spy['o114'], $spy['o115'], $spy['o117'], $spy['o118'], $spy['o120'], $spy['o121'], $spy['o122'], $spy['o123'], $spy['o124'], $spy['o199'] );
    AddDBRow ( $report, "spy");
    return $id;
}

function LoadSpy ( $id )
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."spy WHERE spyid = '".$id."'";
    $result = dbquery ($query);
    return dbarray ($result);
}

function EnumSpy ( $uni_id, $g, $s, $p, $moon=0 )
{
    global $db_prefix;
    $query = "SELECT * FROM ".$db_prefix."spy WHERE uni_id = $uni_id AND g = $g AND s = $s AND p = $p";
    if ($moon) $query .= " AND moon = 1";
    else $query .= " AND moon = 0";
    $query .= " ORDER BY date DESC";
    return dbquery ($query);
}

function DeleteSpy ( $id )
{
    global $db_prefix;
    $query = "DELETE FROM ".$db_prefix."spy WHERE spyid = '".$id."'";
    dbquery ($query);
}

// Разобрать "сырой" текст и добавить информацию в базу.
function ParseSpy ($text)
{
    global $GlobalUser, $desc, $fleetmap, $defmap, $buildmap, $techmap;
    $map = array ( 'fleetmap', 'defmap', 'buildmap', 'techmap' );
    $i = 0;
    $s = $text;
    while (1) {
        $spy = array ();
        $s = strstr ($s, "sp <");
        if ($s == FALSE) break;
        $s = strstr ($s, "<");
        $tmp = str_between ($s, "<", ">");
        if ($tmp == false) break;
        $s = strstr ($s, ">");
        $fs = $tmp[0];

        $start = strpos ($fs, "/(") + 2;    // Игрок.
        $end = strpos ($fs, ")/");
        $spy["player"] = trim (substr ($fs, $start, $end-$start));

        $start = strpos ($fs, "(", $end) + 1;        // Название планеты/луны.
        $end = strpos ($fs, ")", $end+1);
        $spy["planet"] = trim (substr ($fs, $start, $end-$start));

        $part = substr ($fs, $end+1);
        $spy["g"] = strtok ($part, " ");
        $spy["s"] = strtok (" ");
        $spy["p"] = strtok (" ");
        $spy["moon"] = strtok (" ");
        $spy["level"] = strtok (" ");
        $spy["m"] = strtok (" "); $spy["k"] = strtok (" "); $spy["d"] = strtok (" "); $spy["e"] = strtok (" ");
        $spy["counter"] = strtok (" ");
        for ($level=1; $level<=4; $level++)    {
            for ($i=0; $i<sizeof($$map[$level-1]); $i++)     {
                $tab = $$map[$level-1];
                $spy['o'.$tab[$i]] = strtok (" ");
            }
        }
        $spy["date"] = strtok (" ");

        if ( AddSpy ( $GlobalUser['uni_id'], $GlobalUser['user_id'], $spy ) !== "0" ) $i++;
    }
    if ($i == 0) return "";
    else return "Добавлено $i шпионских докладов<br/>";
}

// *******************************************************************************
// Функции отрисовки страниц и генерации HTML-кода.

function RedirectHome ()
{
    echo "<html><head><meta http-equiv='refresh' content='0;url=".scriptname()."' /></head><body></body>";
    exit ();
}

function SpyReport ($spy)
{
    $res = "";
    global $desc, $fleetmap, $defmap, $buildmap, $techmap;
    $map = array ( 'fleetmap', 'defmap', 'buildmap', 'techmap' );
    $leveldesc = array ( "Флоты", "Оборона", "Постройки", "Исследования" );
    $res .= "<table class=\"ui-widget-content ui-corner-all\">\n\n<tr><td colspan=3 class=b>\n\n";
    $moonstr = "";
    if ($spy['moon']) $moonstr = " (Луна)";
    $galaxyurl = "\"".scriptname()."?page=galaxy&session=".$_GET['session']."&g=".$spy['g']."&s=".$spy['s']."&p=".$spy['p']."\"";
    $title = "Сырьё на ".$spy['planet']."$moonstr <a href=$galaxyurl>[".$spy['g'].":".$spy['s'].":".$spy['p']."]</a> (Игрок '".$spy['player']."')";
    $date = " на ".$spy['date'].", добавил <a href=\"".scriptname()."?page=pinfo&session=".$_GET['session']."&id=".$spy['added_by']."\">".GetPlayerName ($spy['uni_id'], $spy['added_by'])."</a>";
    $res .= "<table width=400>\n";
    $res .= "<tr><td class=\"ui-widget-header\" colspan=4>".$title."<br />".$date."</td></tr>\n";
    $res .= "<tr><td>металла:</td><td>".nicenum($spy['m'])."</td><td>кристалла:</td><td>".nicenum($spy['k'])."</td></tr>\n";
    $res .= "<tr><td>дейтерия:</td><td>".nicenum($spy['d'])."</td><td>энергии:</td><td>".nicenum($spy['e'])."</td></tr>\n";
    $res .= "</table>\n\n";
    for ($level=1; $level<=4; $level++)    {
        if ($spy['level'] >= $level)        {
            $res .= "<table width=400><tr><td class=\"ui-widget-header\" colspan=4>".$leveldesc[$level-1]."     </td></tr>  </tr>\n";
            for ($i=0,$shown=0; $i<sizeof($$map[$level-1]); $i++)            {
                $tab = $$map[$level-1];
                if ($spy['o'.$tab[$i]] > 0)                {
                    $res .= "<td>".$desc[$tab[$i]]."</td><td>".nicenum($spy['o'.$tab[$i]])."</td>";
                    $shown++;
                    if (!($shown & 1)) $res .= " </tr>\n";
                }
            }
            $res .= "</table>\n\n";
        }
        else        {
//            $res .= "<table width=400><tr><td class=\"ui-widget-header\" colspan=4><font color=red>".$leveldesc[$level-1]."     </font></td></tr>  </tr>\n";
//            $res .= "</table>\n\n";
        }
    }
    $res .= "<center> Шанс на защиту от шпионажа:".$spy['counter']."%</center>\n";
    $res .= "</td></tr>\n</table>\n\n";
    return $res;
}

function PageHeader ($title="Командир")
{
    ob_start ();
    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
    echo "    <meta name=\"keywords\" content=\"Командир OGame Ogame Спецназ\" />\n";
    echo "    <meta name=\"description\" content=\"Командир - программа для управления альянсом, обработки статистики, сохранении обзора галактики и шпионских докладов\" />\n";
    echo "    <meta name=\"author\" content=\"Альянс Спецназ ru5\" />\n";
    echo "    <title>$title</title>\n";
    echo "    <link type=\"text/css\" href=\"jquery/themes/trontastic/ui.all.css\" rel=\"stylesheet\" />\n";
    echo "    <link type=\"text/css\" href=\"../css/com.css\" rel=\"stylesheet\" />\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/jquery-1.3.2.min.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/wtooltip.min.js\"></script> \n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.core.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.draggable.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.resizable.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.dialog.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.tabs.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/ui.slider.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/effects.core.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/ui/effects.slide.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/external/bgiframe/jquery.bgiframe.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"jquery/jquery-buttons.js\"></script>\n";
    echo "    <script type=\"text/javascript\" src=\"com.user.js\"></script>\n";
    echo "    <script type=\"text/javascript\">\n";
    echo "    function ni () { $(function() { $(\"#dialogNI\").dialog({ close: function(event, ui) { location.reload(); }, bgiframe: true, modal: true, width: 450, height: 50 }); }); }\n";
    echo "    </script>\n";
    echo "</head>\n";
    echo "<body>\n\n";
}

function PageFooter ()
{
    echo "\n\n<div id=\"dialogNI\" title=\"<font color='#e00'>Данная функция не реализована</font>\" style=\"display: none;\">\n";
    echo "	<p><span class=\"ui-icon ui-icon-wrench\" style=\"float:left; margin:0 7px 50px 0;\"></span>\n";
    echo "      Программа находится в стадии разработки.	</p></div>\n";
    echo "</body>\n";
    echo "</html>";
    ob_end_flush ();
    die ();
}

function LeftMenu ($anim=1)
{
    global $GlobalUser;
    if ( $anim ) {
        echo "\n<script type=\"text/javascript\">   \n";
        echo "function slide(navigation_id, pad_out, pad_in, time, multiplier)   \n";
        echo "{   \n";
        echo "	var list_elements = navigation_id + \" li.sliding-element\";   \n";
        echo "	var link_elements = list_elements + \" a\";   \n";
        echo "	var timer = 0;   \n";
        echo "	$(list_elements).each(function(i)   \n";
        echo "	{   \n";
        echo "		$(this).css(\"margin-left\",\"-180px\");   \n";
        echo "		timer = (timer*multiplier + time);   \n";
        echo "		$(this).animate({ marginLeft: \"0\" }, timer);   \n";
        echo "		$(this).animate({ marginLeft: \"15px\" }, timer);   \n";
        echo "		$(this).animate({ marginLeft: \"0\" }, timer);   \n";
        echo "	});   \n";
        echo "	$(link_elements).each(function(i)   \n";
        echo "	{   \n";
        echo "		$(this).hover(   \n";
        echo "		function() { $(this).animate({ paddingLeft: pad_out }, 150); },		   \n";
        echo "		function() { $(this).animate({ paddingLeft: pad_in }, 150); });   \n";
        echo "	});   \n";
        echo "}   \n";
        echo "</script>   \n";
    }
    echo "<div id=\"menu\">   \n";
    echo "    <img src=\"../images/slide_background.jpg\"  id=\"slideborder\"/>   \n";
    echo "    <ul id=\"sliding-navigation\">   \n";
    echo "        <li class=\"sliding-element\"> &nbsp; </li>\n\n";
    echo "        <li class=\"sliding-element\"> <h3> <span class=\"ui-icon ui-icon-home\" style=\"float:left;\"></span> Просмотр</h3></li>   \n";
    echo "        <li class=\"sliding-element\"><a href=\"".scriptname()."?page=overview&session=".$_GET['session']."\">Обзор</a></li>   \n";
    echo "        <li class=\"sliding-element\"><a href=\"".scriptname()."?page=galaxy&session=".$_GET['session']."&anim=1\">Галактика</a></li>   \n";
    echo "        <li class=\"sliding-element\"> &nbsp; </li>\n\n";
    echo "        <li class=\"sliding-element\"> <h3> <span class=\"ui-icon ui-icon-plus\" style=\"float:left;\"></span> Обновление</h3></li>   \n";
    if ($GlobalUser['uni_id']) {
        echo "        <li class=\"sliding-element\"><a href=\"".scriptname()."?page=update&session=".$_GET['session']."&canim=0\">Обновить базу</a></li>   \n";
        echo "        <li class=\"sliding-element\"><a href=\"".scriptname()."?page=status&session=".$_GET['session']."\">Статус базы</a></li>   \n";
    }
    echo "        <li class=\"sliding-element\"><a href=\"".scriptname()."?page=myfleet&session=".$_GET['session']."\">Мой флот</a></li>   \n";
    echo "        <li class=\"sliding-element\"><a href=\"".scriptname()."?page=options&session=".$_GET['session']."\">Настройки</a></li>   \n";
    echo "        <li class=\"sliding-element\"> &nbsp; </li>\n\n";
    echo "        <li class=\"sliding-element\"> <h3> <span class=\"ui-icon ui-icon-help\" style=\"float:left;\"></span> Прочее</h3></li>   \n";
    echo "        <li class=\"sliding-element\"><a href=\"#\">Форум</a></li>   \n";
    echo "        <li class=\"sliding-element\"><a href=\"".scriptname()."?page=tutorial&session=".$_GET['session']."\">Туториал</a></li>   \n";
    echo "        <li class=\"sliding-element\"><a href=\"".scriptname()."?page=about&session=".$_GET['session']."\">О программе</a></li>   \n";
    echo "        <li class=\"sliding-element\"><a href=\"".scriptname()."?page=logout&session=".$_GET['session']."\">Выход</a></li>   \n";
    if ( !$GlobalUser['validated'] ) {
        echo "        <li class=\"sliding-element\"><a href=\"".scriptname()."?page=options&session=".$_GET['session']."\">  \n";
        echo "               <table><tr><td><img src=\"../images/com_caution_red.gif\" style=\"border: 0px;\"></td><td><font color=red>Аккаунт не активирован</font></td></tr></table></a></li>   \n";
    }
    echo "    </ul>   \n";
    echo "</div>   \n";
    if ($anim) echo "<script type=\"text/javascript\">slide(\"#sliding-navigation\", 25, 15, 75, .8);</script>\n\n";
}

// Страница установки скрипта.
function InstallPage ()
{
    PageHeader ("Командир - Установка");
    echo "<script type=\"text/javascript\">\n";
    if ( method() === "GET" )
    {
        echo "    $(function() {   \n";
        echo "        $(\"#dialogInstall\").dialog({   \n";
        echo "            bgiframe: true,   \n";
        echo "            modal: true, zIndex: 1, width: 400, height: 320,  \n";
        echo "            buttons: {   \n";
        echo "                \"Установить\": function() { $(\"#installform\").submit(); $(this).dialog('close');  }   \n";
        echo "            }   \n";
        echo "        });   \n";
        echo "    });   \n";
    }
    else if ( method() === "POST" )
    {
        // Попробовать соединиться с базой данных.
        $db_connect = @mysql_connect($_POST['db_host'], $_POST['db_user'], $_POST['db_pass']);
        $db_select = @mysql_select_db($_POST['db_name']);
        echo "    $(function() {\n";
        if ( $db_connect && $db_select  )
        {
            echo "        $(\"#dialogSuccess\").dialog({   \n";
            echo "            bgiframe: true,   \n";
            echo "            modal: true,   \n";
            echo "            close: function(event, ui) { window.location = \"".scriptname()."\"; },  \n";
            echo "            buttons: { \"На главную\": function() { $(this).dialog('close'); window.location = \"".scriptname()."\"; } }   \n";
            ResetDatabase ();
            SaveConfigFile ();
        }
        else
        {
            echo "        $(\"#dialogFailed\").dialog({   \n";
            echo "            width: 400,   \n";
            echo "            bgiframe: true,   \n";
            echo "            modal: true,   \n";
            echo "            close: function(event, ui) { window.location = \"".scriptname()."\"; },  \n";
            echo "            buttons: { \"Ok\": function() { $(this).dialog('close'); window.location = \"".scriptname()."\"; } }   \n";
        }
        echo "        });\n";
        echo "    });\n";
    }
    echo "</script>\n\n";
    echo "<div id=\"dialogInstall\" title=\"Установка Командира\" style=\"display: none;\">\n";
    echo "    <p>Настройки базы данных. Вы можете навести мышку на некоторые настройки, чтобы получить небольшую подсказку.</p> \n";
    echo "	<p>\n";
    echo "		<form id=\"installform\" action=\"".scriptname()."\" method=\"POST\"> <table>\n";
    echo "		<tr><td><b id=\"db_host\" style=\"z-index: 9999\">Хост</b> </td><td> <input name=\"db_host\" type=\"text\" size=\"12\"></td></tr> \n";
    echo "		<tr><td><b id=\"db_user\" style=\"z-index: 9999\">Пользователь</b> </td><td> <input name=\"db_user\" type=\"text\" size=\"12\"></td></tr> \n";
    echo "		<tr><td><b id=\"db_pass\" style=\"z-index: 9999\">Пароль</b> </td><td> <input name=\"db_pass\" type=\"password\" size=\"12\"></td></tr> \n";
    echo "		<tr><td><b id=\"db_name\" style=\"z-index: 9999\">Название БД</b> </td><td> <input name=\"db_name\" type=\"text\" size=\"12\"></td></tr> \n";
    echo "		<tr><td><b id=\"db_prefix\" style=\"z-index: 9999\">Префикс таблиц</b> </td><td> <input name=\"db_prefix\" type=\"text\" size=\"12\" value=\"com_\"></td></tr> \n";
    echo "		<tr><td><b id=\"db_secret\" style=\"z-index: 9999\">Секретное слово</b> </td><td> <input name=\"db_secret\" type=\"text\" size=\"12\"></td></tr>  \n";
    echo "		</table></form>\n";
    echo "	</p>\n";
    echo "</div>\n\n";
    echo "<div id=\"dialogSuccess\" title=\"Установка Командира\" style=\"display: none;\">\n";
    echo "	<p>\n";
    echo "		<span class=\"ui-icon ui-icon-circle-check\" style=\"float:left; margin:0 7px 50px 0;\"></span>\n";
    echo "		Установка успешно завершена.\n";
    echo "	</p>\n";
    echo "</div>\n\n";
    echo "<div id=\"dialogFailed\" title=\"Установка Командира\" style=\"display: none;\">\n";
    echo "	<p>\n";
    echo "		<span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 50px 0;\"></span>\n";
    echo "		<b><font color=red>Невозможно соединиться с базой данных MySQL.</font></b>\n";
    echo "          <p> " . mysql_error() . " </p> \n";
    echo "	</p>\n";
    echo "</div>\n\n";
    echo "<script type=\"text/javascript\">   \n";
    echo "    $(\"#db_prefix\").wTooltip({content: \"Чтобы было легко найти все таблицы, задайте им общий префикс\" });    \n";
    echo "    $(\"#db_secret\").wTooltip({content: \"Используется при генерации паролей и сессий\" });    \n";
    echo "</script>   \n";
    PageFooter ();
}

// Главная страница - Вход в игру.
function HomePage ($badlogin=false, $login="")
{
    global $version;
    PageHeader ("Командир - Главная");
    echo "<script type=\"text/javascript\">   \n";
    echo "    $(function() {   \n";
    echo "        $(\"#dialogLogin\").dialog({   \n";
    echo "            bgiframe: true,   \n";
    echo "            modal: true, zIndex: 2, height: 190,  \n";
    echo "            buttons: {   \n";
    echo "                \"Регистрация\": function() { $(this).dialog('close'); window.location = \"".scriptname()."?page=reg\"; },   \n";
    echo "                \"Вход\": function() { $(\"#loginform\").submit();  $(this).dialog('close'); }   \n";
    echo "            }   \n";
    echo "        });   \n";
    echo "    });   \n";
    if ( $badlogin )
    {
        echo "    $(function() {   \n";
        echo "        $(\"#dialogLoginErr\").dialog({   \n";
        echo "            bgiframe: true,   \n";
        echo "            modal: true, height: 200, width: 380,  \n";
        echo "            zIndex: 3,   \n";
        echo "            buttons: { \"Ok\": function() { $(this).dialog('close'); } }   \n";
        echo "        });   \n";
        echo "    });    \n";
    }
    echo "</script> \n\n";
    echo "<div id=\"dialogLogin\" title=\"Главное Меню (Командир $version)\" style=\"display: none;\">\n";
    echo "	<p>\n";
    echo "        <form id=\"loginform\" action=\"".scriptname()."?page=login\" method=\"POST\"><center><table>\n";
    echo "		<tr><td><b>Логин</b> </td><td> <input type=\"text\" name=\"login\" size=\"14\" value=\"".$login."\"> </td></tr>\n";
    echo "		<tr><td><b>Пароль</b> </td><td> <input type=\"password\" name=\"pass\" size=\"14\"> </td></tr>\n";
    echo "            <tr><td>&nbsp;</td><td><small><a href=\"".scriptname()."?page=lostpass\">Забыли пароль?</a></small></td></tr>\n";
    echo "        </table></center></form>\n";
    echo "	</p>\n";
    echo "</div>\n";
    if ( $badlogin )
    {
        echo "<div id=\"dialogLoginErr\" title=\"Ошибка\" style=\"display: none;\">\n";
        echo "	<p><span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 50px 0;\"></span>\n";
        echo "     <b><font color=red>Неверный логин или пароль.</font></b> <p>\n";
        echo "            Попробуйте сменить раскладку клавиатуры, проверьте чтобы не был включен CAPS LOCK. Если вам не удается вспомнить пароль, вы можете создать новый на главной странице. </p></p>\n";
        echo "</div>  \n";
    }
    PageFooter ();
}

// Страница регистрации.
function RegPage ($login="", $pass="", $email="", $gamename="", $allytag="", $uni="", $com="off", $error="", $errorNote="")
{
    PageHeader ("Командир - Регистрация");
    $comcheck = "";
    if ($com === "on") $comcheck = "CHECKED";
    echo "<script type=\"text/javascript\">   \n";
    echo "    $(function() {   \n";
    echo "        $(\"#dialogReg\").dialog({   \n";
    echo "            bgiframe: true,   \n";
    echo "            modal: true,   \n";
    echo "            width: 450, height:350,  \n";
    echo "            zIndex: 2,   \n";
    echo "            buttons: { \"На главную\": function() { $(this).dialog('close'); window.location = \"".scriptname()."\"; },   \n";
    echo "                          \"Отправить\": function() { $(\"#regform\").submit();  $(this).dialog('close'); } }   \n";
    echo "        });   \n";
    echo "    });   \n";
    if ( $error !== "")
    {
        echo "    $(function() {   \n";
        echo "        $(\"#dialogRegErr\").dialog({   \n";
        echo "            bgiframe: true,   \n";
        echo "            modal: true, height: 180,  \n";
        echo "            zIndex: 3,   \n";
        echo "            buttons: { \"Ok\": function() { $(this).dialog('close'); } }   \n";
        echo "        });   \n";
        echo "    });    \n";
    }
    echo "</script>\n   \n";
    echo "<div id=\"dialogReg\" title=\"Создание нового аккаунта\" style=\"display: none;\">   \n";
    echo "	<p>   \n";
    echo "        <form id=\"regform\" action=\"".scriptname()."?page=reg\" method=\"POST\"><center><table>   \n";
    echo "            <tr><td colspan=2>Обязательные:</td>  <td colspan=2>Необязательные:</td> </tr>   \n";
    echo "		<tr><td width='20%' ><b id=\"login\" style=\"z-index: 9999\">Логин</b> </td><td> <input tabindex=\"1\" type=\"text\" name=\"login\" size=\"14\" value=\"".$login."\"> </td>  <td width='20%' ><b id=\"gamename\" style=\"z-index: 9999\">Имя в игре</b> </td><td> <input tabindex=\"4\" type=\"text\" name=\"gamename\" size=\"14\" value=\"".$gamename."\"> </td> </tr>   \n";
    echo "		<tr><td width='20%' ><b id=\"pass\" style=\"z-index: 9999\">Пароль</b> </td><td> <input tabindex=\"2\" type=\"password\" name=\"pass\" size=\"14\" value=$pass> <td width='20%' ><b id=\"allytag\" style=\"z-index: 9999\">Альянс</b> </td><td> <input tabindex=\"5\" type=\"text\" name=\"allytag\" size=\"14\" value=\"".$allytag."\"> </td> </tr>   \n";
    echo "		<tr><td width='20%' ><b id=\"email\" style=\"z-index: 9999\">Почта</b> </td><td> <input tabindex=\"3\" type=\"text\" name=\"email\" size=\"14\" value=\"".$email."\"> </td> <td width='20%' ><b id=\"uni\" style=\"z-index: 9999\">Вселенная</b> </td><td> <input tabindex=\"6\" type=\"text\" name=\"uni\" size=\"14\" value=\"".$uni."\"> </td> </tr>   \n";
    echo "            <tr><td colspan=4><hr></td></tr>   \n";
    echo "            <tr><td colspan=4><input tabindex=\"6\" type=\"checkbox\" name=\"com\" $comcheck> Командирский аккаунт</td></tr>   \n";
    echo "            <tr><td colspan=4><small style=\"font-size: 10px;\">Командирский аккаунт предназначен для создания собственной Вселенной для вашего альянса. Только командиры могут назначать права остальным пользователям.<br/>   \n";
    echo "            <br/>Обычные пользователи, у которых нет командира могут только редактировать и показывать всем состав своего флота и уровни шахт. Чтобы получить возможность просматривать и обновлять Вселенную, командир должен выслать вам приглашение.</small></td></tr>   \n";
    echo "        </table></center></form>   \n";
    echo "	</p>   \n";
    echo "</div>\n   \n";
    if ( $error !== "")
    {
        echo "<div id=\"dialogRegErr\" title=\"Ошибка\" style=\"display: none;\">\n";
        echo "	<p><span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 50px 0;\"></span>\n";
        echo "     <b><font color=red>$error</font></b> <p>\n";
        echo "            $errorNote </p></p>\n";
        echo "</div>  \n";
    }
    echo "<script type=\"text/javascript\">   \n";
    echo "    $(\"#login\").wTooltip({content: \"Логин для входа в программу. На сервере не может быть двух пользователей с одинаковыми логинами.<br/>\"+   \n";
    echo "                                            \"В логине не допускается использование следующих символов: <b>&lt;</b> <b>&gt;</b> <b>(</b> <b>)</b> <b>[</b> <b>]</b> <b>{</b> <b>}</b> <b>\\\\</b> <b>/</b> <b>`</b> <b>\\\"</b> <b>'</b> <b>.</b> <b>,</b> <b>:</b> <b>;</b> <b>*</b> <b>+</b><br> \"+   \n";
    echo "                                            \"Возможно использование кириллицы и других символов Юникода. Максимальная длина логина 20 символов, минимальная 3.\"     });    \n";
    echo "    $(\"#pass\").wTooltip({content: \"Пароль для входа на ваш аккаунт. Минимальная длина пароля 8 символов, максимальная 32.<br/>В пароле могут содержаться только символы a-z, A-Z, цифры 0-9 и подчеркивание <b>_</b>\" });    \n";
    echo "    $(\"#email\").wTooltip({content: \"Действительный электронный адрес. После создания аккаунта на почту будет отправлена ссылка для активации.<br/>Если пользователь не активирует аккаунт в течении 3-х дней, он автоматически удаляется.\" });    \n";
    echo "    $(\"#gamename\").wTooltip({content: \"Ваш ник-нейм в игровой Вселенной, чтобы система могла идентифицировать вас. Может быть изменено в настройках аккаунта в любой момент.\" });    \n";
    echo "    $(\"#allytag\").wTooltip({content: \"Название вашего альянса в игре. Пользователям, приглашенным командиром автоматически назначается тот-же альянс.<br/><i>Только для командира</i>\" });    \n";
    echo "    $(\"#uni\").wTooltip({content: \"Название вашей вселенной, например <b>5</b>. <br/>Для разных командиров имена Вселенных не конфликтуют, и нужны только для того, чтобы пользователи могли видеть в какой Вселенной они находятся в данный момент.<br/><i>Только для командира</i>\" });    \n";
    echo "</script>   \n";
    PageFooter ();
}

// Страница восстановления пароля.

function InvalidSessionPage ()
{
    PageHeader ("Командир - Сессия недействительна");
    echo "<script type=\"text/javascript\">   \n";
    echo "    $(function() {   \n";
    echo "        $(\"#dialogInvalidSession\").dialog({   \n";
    echo "            bgiframe: true,   \n";
    echo "            modal: true, width: 450, height: 230,  \n";
    echo "            buttons: { \"На главную\": function() { $(this).dialog('close'); window.location = \"".scriptname()."\"; } }   \n";
    echo "        });   \n";
    echo "    });   \n";
    echo "</script>\n   \n";
    echo "<div id=\"dialogInvalidSession\" title=\"Произошла ошибка\" style=\"display: none;\">   \n";
    echo "           <p>    \n";
    echo "                <span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 50px 0;\"></span>   \n";
    echo "                <strong><font color=red>Сессия недействительна.</font></strong> Это может быть вызвано несколькими причинами:    \n";
    echo "                <ul>   \n";
    echo "                <li> Вы несколько раз зашли на один и тот же аккаунт </li>   \n";
    echo "                <li> Ваш IP-адрес изменился с момента последнего входа </li>   \n";
    echo "                <li> Вы пользуетесь интернетом через мобильный телефон, Wi-Fi или прокси </li>   \n";
    echo "                </ul>   \n";
    echo "            </p>   \n";
    echo "</div>   \n";
    PageFooter ();
}

// Обзор.
function PageOverview ()
{
    global $GlobalUser;
    if (CheckSession ( $_GET['session'] ) == FALSE) die ();
    PageHeader ("Командир - Обзор");
    LeftMenu ();
    echo "<div id=\"appcontent\" class=\"ui-widget-content ui-corner-all\">   \n";
    if ( $GlobalUser['invite'] !== "0") {
        echo "<script type=\"text/javascript\">   \n";
        echo "    $(function() {   \n";
        echo "        $(\"#invite\").dialog({   \n";
        echo "            bgiframe: true, modal: true, zIndex: 3,   \n";
        echo "            buttons: { \"Отклонить\": function() { $(this).dialog('close'); $(\"#invitereject\").submit(); },   \n";
        echo "                           \"Принять\": function() { $(this).dialog('close'); $(\"#inviteaccept\").submit(); } }   \n";
        echo "        });   \n";
        echo "    });   \n";
        echo "</script>   \n";
    }

    $logins = 0;
    $result = EnumUsers ( $GlobalUser['uni_id'] );
    $rows = dbrows ($result);
    while ($rows--) {
        $user = dbarray ($result);
        $logins += $user['logins'];
//        print_r ($user); echo "<br>";
    }
    echo "Всего посещений: $logins<br/>\n";

//    $spy = LoadSpy ("27ca4aabc15d02d467bfdacf8f81a50c");
//    echo SpyReport ($spy);

    if ( $GlobalUser['invite'] !== "0" ) {
        $com = LoadUser ( $GlobalUser['invite_id'] );
        $uni = LoadUni ( $com['uni_id'] );
        echo "<div id=\"invite\" title=\"Приглашение\" style=\"display: none;\">   \n";
        echo "<form id=\"inviteaccept\" action=\"".scriptname()."?page=invite&mode=accept&session=".$_GET['session']."\" method=\"POST\"></form>   \n";
        echo "<form id=\"invitereject\" action=\"".scriptname()."?page=invite&mode=reject&session=".$_GET['session']."\" method=\"POST\"></form>   \n";
        echo "<p><span class=\"ui-icon ui-icon-person\" style=\"float:left; margin:0 7px 50px 0;\"></span>   \n";
        echo "    Командир ".$com['login']." приглашает Вас присоединиться к альянсу ".$uni['tag']." (вселенная ".$uni['name'].").</p></div>   \n";
        echo "</div>   \n";
    }
    echo "</div>   \n";
    PageFooter ();
}

function empty_row ($p) {
    echo "        <tr><td>$p</td><td>-</td><td>-</td><td id=\"debris\">-</td><td>-</td><td>-</td><td>-</td><td>-</td>   \n";
};

// Галактика.
function PageGalaxy ()
{
    global $GlobalUser;
    if (CheckSession ( $_GET['session'] ) == FALSE) die ();
    PageHeader ("Командир - Галактика");
    LeftMenu ( $_GET['anim'] );
    if ( key_exists ('g', $_GET)) {         // Запомнить выбор галактики.
        if ( $_GET['g'] <= 0 ) $_GET['g'] = 1;
        if ( $_GET['g'] > 9 ) $_GET['g'] = 9;
        SaveLastGalaxy ( $GlobalUser['user_id'], $_GET['g'] );
    }
    else $_GET['g'] = LoadLastGalaxy ($GlobalUser['user_id']);
    if ( key_exists ('s', $_GET)) {
        if ( $_GET['s'] <= 0 ) $_GET['s'] = 1;
        if ( $_GET['s'] > 499 ) $_GET['s'] = 499;
        SaveLastSystem ( $GlobalUser['user_id'], $_GET['s'] );
    }
    else $_GET['s'] = LoadLastSystem ($GlobalUser['user_id']);
    echo "<script type=\"text/javascript\">   \n";
    echo "	$(function() {   \n";
    echo "          document.getElementById('cg').value = ".$_GET['g'].";   \n";
    echo "          document.getElementById('cs').value = ".$_GET['s'].";   \n";
    echo "		$(\"#slider\").slider( {   \n";
    echo "                value: 499*(".$_GET['g']."-1) + ".$_GET['s'].",   \n";
    echo "                min: 0, max: 9*499-1,   \n";
    echo "                change: function(event, ui) {   \n";
    echo "                    g = parseInt ((ui.value / 499) + 1);   \n";
    echo "                    s = ui.value % 499+1;   \n";
    echo "                    $(\"#galaxyBrowse\").submit();   \n";
    echo "                },   \n";
    echo "                slide: function(event, ui) {   \n";
    echo "                    g = parseInt ((ui.value / 499) + 1);   \n";
    echo "                    s = ui.value % 499+1;   \n";
    echo "                    document.getElementById('cg').value = g;   \n";
    echo "                    document.getElementById('cs').value = s;   \n";
    echo "                }   \n";
    echo "            });   \n";
    echo "	});   \n";

    echo "  function cursorevent(evt) {   \n";
    echo "      if(evt.keyCode == 37) {   \n";
    echo "        g = document.getElementById('cg').value;   \n";
    echo "        s = document.getElementById('cs').value;   \n";
    echo "        s--; if (s == 0) { s = 499; g--; }   \n";
    echo "        if (g == 0) { g = 1; s = 1; }   \n";
    echo "        document.getElementById('cg').value = g;   \n";
    echo "        document.getElementById('cs').value = s;   \n";
    echo "        document.getElementById('an').value = 0;   \n";
    echo "        $(\"#galaxyBrowse\").submit();   \n";
    echo "      }   \n";
    echo "      if(evt.keyCode == 39) {   \n";
    echo "        g = document.getElementById('cg').value;   \n";
    echo "        s = document.getElementById('cs').value;   \n";
    echo "        s++; if (s == 500) { s = 1; g++; }   \n";
    echo "        if (g == 10) g = 9;   \n";
    echo "        document.getElementById('cg').value = g;   \n";
    echo "        document.getElementById('cs').value = s;   \n";
    echo "        document.getElementById('an').value = 0;   \n";
    echo "        $(\"#galaxyBrowse\").submit();   \n";
    echo "      }   \n";
    echo "      if(evt.keyCode == 38) {   \n";
    echo "        g = document.getElementById('cg').value;   \n";
    echo "        g--; if (g == 0) g = 1;  \n";
    echo "        document.getElementById('cg').value = g;   \n";
    echo "        document.getElementById('an').value = 0;   \n";
    echo "        $(\"#galaxyBrowse\").submit();   \n";
    echo "      }   \n";
    echo "      if(evt.keyCode == 40) {   \n";
    echo "        g = document.getElementById('cg').value;   \n";
    echo "        g++; if (g == 10) g = 9;  \n";
    echo "        document.getElementById('cg').value = g;   \n";
    echo "        document.getElementById('an').value = 0;   \n";
    echo "        $(\"#galaxyBrowse\").submit();   \n";
    echo "      }   \n";
    echo "  }   \n";
    echo "  document.onkeydown = cursorevent;   \n";
    echo "</script>    \n";

    echo "<div id=\"appcontent\" class=\"ui-widget-content ui-corner-all\">   \n";
    $result = EnumPlanets ( $GlobalUser['uni_id'], $_GET['g'], $_GET['s'] );
    $rows = dbrows ($result);
    echo "    <center><p><table id=\"galaxytab\" class=\"ui-widget ui-widget-content\" >   \n";
    echo "    <tr><th colspan=5><table><tr><td>Солнечная система:</td>   \n";
    echo "    <td><form id=\"galaxyBrowse\" action=\"".scriptname()."\" method=\"GET\"> <input type=\"hidden\" name=\"page\" value=\"galaxy\"> <input id=\"an\" type=\"hidden\" name=\"anim\" value=\"1\"> <input type=\"hidden\" name=\"session\" value=\"".$_GET['session']."\">   \n";
    echo "            <input id=\"cg\" name=\"g\" size=\"1\"> <input id=\"cs\" name=\"s\" size=\"1\">  \n";
    echo "          <button class=\"fg-button ui-state-default ui-corner-all\" type=\"submit\">Go</button></form></td></tr></table></th>   \n";
    echo "    </tr>   \n";
    echo "    <tr class=\"ui-widget-header\">   \n";
    echo "        <th colspan=2>Планета</th> <th>Название</th> <th>Луна</th> <th>ПО</th> <th>Игрок (статус)</th> <th>Альянс</th> <th>&nbsp;</th>    \n";
    echo "    </tr>   \n";
    $num = $mnum = 0; $p = 1; $reftime = 0;
    while ($rows--)
    {
        $planet = dbarray ($result);
        if ( $planet['type'] == 0 && !$planet['destroyed']) continue;
        if ( $reftime == 0 ) $reftime = $planet['date'];
        else if ( $planet['date'] < $reftime )  break;
        if ( $planet['type'] == 999 ) { empty_row ($p); $p = $planet['p']+1; continue; }
        if ( !$planet['destroyed'] ) $planetimg = "<img src=\"../images/com_p".$planet['type'].".gif\">";
        else $planetimg = "-";
        if ( $planet['activity'] < 15 ) $act = "<img src=\"../images/com_activity.gif\">";
        else if ( $planet['activity'] < 30 ) $act = "<font color=lime>(".$planet['activity']." min)</font>";
        else if ( $planet['activity'] < 45 ) $act = "<font color=yellow>(".$planet['activity']." min)</font>";
        else if ( $planet['activity'] < 60 ) $act = "<font color=red>(".$planet['activity']." min)</font>";
        else $act = "";
        if ($planet['dm'] + $planet['dk']) $debris = "M:".nicenum($planet['dm'])."<br/>K:".nicenum($planet['dk']);
        else $debris = "-";
        $moon = LoadPlanet ( $planet['uni_id'], $planet['g'], $planet['s'], $planet['p'], 1 );
        if ( $moon['uni_id'] == $planet['uni_id'] && !$planet['destroyed']) {
            $moonstr = "<img src=\"../images/com_mond.gif\">";
            $mnum++;
        }
        else $moonstr = "-";
        $username = "<a href=\"".scriptname()."?page=pinfo&session=".$_GET['session']."&id=".$planet['owner_id']."\">".GetPlayerName ( $GlobalUser['uni_id'], $planet['owner_id'] )."</a>";
        $allyname = GetAllyName( $GlobalUser['uni_id'], GetPlayerAlly ( $GlobalUser['uni_id'], $planet['owner_id'] ) );
        if ( $planet['destroyed'] ) { $planet['name'] = "Уничтоженная планета"; $username = $allyname = "-"; }
        echo "        <tr><td>$p</td><td>$planetimg</td><td>".$planet['name']." $act</td><td>$moonstr</td>   \n";
        echo "              <td id=\"debris\">$debris</td><td>$username</td><td>$allyname</td><td>".$planet['date']."</td> </tr>   \n";
        $num++; $p = $planet['p']+1;
    }
    if ( $num + $mnum ) $colstr = "Заселено $num планет, $mnum лун";
    else $colstr = "";
    $added_by = "";
    $age = GetSystemAge ($GlobalUser['uni_id'], $_GET['g'], $_GET['s'], &$added_by);
    if ( $age ) $datestr = date( "j M Y G:i:s", $age) . " (".GetPlayerName($GlobalUser['uni_id'],$added_by).")";
    else $datestr = "";
    echo "        <tr><th colspan=5>$colstr</th><th colspan=3>$datestr</th></tr>   \n";
    echo "        <tr><td colspan=8><div id=\"slider\"></div></td></tr>   \n";
    echo "    </table></p>   \n";
    echo "    </center>   \n";
    echo "</div>   \n";
    PageFooter ();
}

// Обновить базу.
function PageUpdate ()
{
    global $GlobalUser;
    if ( key_exists ("sig", $_POST) && method() === "POST" )
    {
        if (LoadUserBySig ($_POST['sig']) == FALSE) { echo "<font color=red>Ошибка аутентификации!</font>"; die (); }
        if ($GlobalUser['uni_id'] == 0) { echo "<font color=red>Ошибка аутентификации!</font>"; die (); }
    }
    else { if (CheckSession ( $_GET['session'] ) == FALSE) die (); }
    if ($GlobalUser['uni_id'] == 0) { PageOverview (); return; }
    if ( method() === "POST" )
    {
        $res = "";
        $res .= ParsePlanets ($_POST['text']);        // Галактика.
        $res .= ParseStat ($_POST['text']);            // Статистика.
        $res .= ParseSpy ($_POST['text']);            // Шпионские доклады.
        if ( key_exists ("sig", $_POST) ) {    // Если обновляется через дополнение, не генерировать HTML, а сразу выйти.
            echo $res;
            die ();
        }
    }
    PageHeader ("Командир - Обновить базу");
    LeftMenu ();
    echo "<script type=\"text/javascript\">   \n";
    echo "$(function() {   \n";
    if ( $_GET['canim'] ) {
        echo "    $(\"#appcontent\").hide();   \n";
        echo "    $(\"#appcontent\").show(\"slide\",{direction:'up'},250);   \n";
    }
    echo "});   \n";
    echo "</script>   \n";
    echo "<script type=\"text/javascript\">   \n";
    echo "$(function() {   \n";
    echo "    $('#parse').click ( function() {   \n";
    echo "        var text = $(\"#updatesource\").val();   \n";
    echo "        var raw = ParsePage (text);   \n";
    echo "        document.updateform.text.value = raw;   \n";
    echo "        document.updateform.submit ();   \n";
    echo "    } );   \n";
    echo "});   \n";
    echo "</script>  \n";
    echo "<div id=\"appcontent\" class=\"ui-widget-content ui-corner-all\">   \n";
//        echo "<pre>"; print_r ($_POST); echo "</pre>";
    echo "            <p>   \n";
    echo "		Чтобы вручную обновить базу, нужно вставить <b><font color=red>HTML-код</font></b> одной или более страниц с Галактикой, Статистикой или Разведданными.<br/>   \n";
    echo "            Программа сама разберется что вы ей предложили и автоматически определит тип добавляемых данных.</p>   \n";
    echo "            <form name=\"updateform\" action=\"".scriptname()."?page=update&session=".$_GET['session']."\" method=\"POST\">   \n";
    echo "            <input type=\"hidden\" name=\"text\">   \n";
    echo "            </form>   \n";
    echo "            <form>   \n";
    echo "            <table class=\"ui-widget ui-widget-content\"><tr><td colspan=2><textarea id=\"updatesource\" name=\"source\" rows=\"25\" cols=\"150\"></textarea></td></tr>  \n";
    echo "            <tr><td><button id=\"parse\" class=\"fg-button ui-state-default ui-corner-all\" type=\"button\" >Обработать</button>   \n";
    echo "            <button class=\"fg-button ui-state-default ui-corner-all\" type=\"reset\">Отменить</button></td></tr>   \n";
    echo "            </form>   \n";
    echo "</div>   \n";
    PageFooter ();
}

function agecolor ($age, $now)
{
    if ($age == 0) return "Silver";
    if ($now > $age + 30*24*60*60) return "DarkRed";
    if ($now > $age + 14*24*60*60) return "Red";
    if ($now > $age + 7*24*60*60) return "Orange";
    if ($now > $age + 3*24*60*60) return "Yellow";
    return "Lime";
}

// Статус базы.
function PageStatus ()
{
    global $GlobalUser;
    if (CheckSession ( $_GET['session'] ) == FALSE) die ();
    if ($GlobalUser['uni_id'] == 0) { PageOverview (); return; }
    PageHeader ("Командир - Статус базы");
    LeftMenu ();
    echo "<div id=\"appcontent\" class=\"ui-widget-content ui-corner-all\">   \n";

    $now = time ();

    echo "<table class=\"ui-widget ui-widget-content\" width=\"65%\">\n";
    echo "<tr><td><font color=Lime>Цвет1</font></td><td>Актуальность 1 - 3 дня.</td></tr>\n";
    echo "<tr><td><font color=Yellow>Цвет2</font></td><td>Актуальность 4 - 7 дней.</td></tr>\n";
    echo "<tr><td><font color=Orange>Цвет3</font></td><td>Актуальность 1 - 2 недели.</td></tr>\n";
    echo "<tr><td><font color=Red>Цвет4</font></td><td>Актуальность 2 - 4 недели.</td></tr>\n";
    echo "<tr><td><font color=DarkRed>Цвет5</font></td><td>Данные старше месяца!</td></tr>\n";
    echo "<tr><td><font color=Silver>Цвет6</font></td><td>Ещё нет данных.</td></tr>\n";
    echo "</table><br/>\n";

    echo "<table class=\"ui-widget ui-widget-content\" width=\"65%\">\n";
    $g = 1;
    for ($n = 1; $n<=50; $n++) {
        echo "<tr>\n";
        for ($m=0; $m<10; $m++) {
            $s = 50*$m+$n;
            if ($s == 500) break;
            $added_by = 0;
            $age = GetSystemAge ( $GlobalUser['uni_id'], 1, $s, &$added_by);
            echo "<td><font color=".agecolor($age, $now)."><a href=\"".scriptname()."?page=galaxy&session=".$_GET['session']."&g=$g&s=$s&p=1\" style=\"text-decoration: none; color: inherit !important;\">$g:$s</a></font></td>";
        }
        echo "</tr>\n";
    }
    echo "</trable>\n";

    echo "</div>   \n";
    PageFooter ();
}

// Мой флот.
function PageFleet ()
{
    global $GlobalUser;
    if (CheckSession ( $_GET['session'] ) == FALSE) die ();
    if ( key_exists ("mode", $_GET) || key_exists ("mode", $_POST) )
    {
        if ( $_POST['g'] === "" || $_POST['s'] === "" || $_POST['p'] === "" ) { $_POST['g'] = $_POST['s'] = $_POST['p'] = 0; }
        if ( $_POST['moon'] === "on" ) $_POST['moon'] = 1;
        else $_POST['moon'] = 0;
        if ( key_exists ("f", $_POST) ) foreach ( $_POST['f'] as $i=>$val ) { if ($_POST['f'][$i] === "") $_POST['f'][$i] = 0; }
        if ($_POST['mode'] === "add" && method() === "POST" )
        {
            for ($i=202,$ships=0; $i<=215; $i++) $ships += $_POST['f'][$i];
            if ($ships) AddFleet ( $_POST['g'], $_POST['s'], $_POST['p'], $_POST['moon'], $_POST['f'], $_POST['owner_id'] );
        }
        else if ($_GET['mode'] === "delete" && method() === "GET" ) {
            if ( $GlobalUser['user_id'] == $_GET['owner_id'] ) {
                DeleteFleet ( $_GET['fleet_id'], $_GET['owner_id'] );
            }
        }
        else if ($_GET['mode'] === "edit" && method() === "GET" ) {
            if ( $GlobalUser['user_id'] == $_GET['owner_id'] ) {
                $fleet = LoadFleet ( $_GET['fleet_id'] );
                for ($i=202,$emitedit=""; $i<=215; $i++) $emitedit .= $fleet["f$i"] . ", ";
                $emitedit .= " " . $fleet['g'] . ", " . $fleet['s'] . ", " . $fleet['p'] . ", " . $fleet['moon'];
                $showedit = 1;
            }
            else { $emitedit = "0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,  0, 0, 0, 0"; $showedit = 0; }
        }
        else if ($_POST['mode'] === "edit" && method() === "POST" ) {
            if ( $GlobalUser['user_id'] == $_POST['owner_id'] ) {
                $fleet = LoadFleet ($_POST['fleet_id']);
                if ( $fleet['owner_id'] == $GlobalUser['user_id'] ) AddFleet ( $_POST['g'], $_POST['s'], $_POST['p'], $_POST['moon'], $_POST['f'], $_POST['owner_id'], $_POST['fleet_id'] );
            }
        }
    }
    PageHeader ("Командир - Мой флот");
    LeftMenu ();

    // Скрипты
    echo "<script type=\"text/javascript\">   \n";
    echo "$(function() {   \n";
    echo "    $(\"#appcontent\").hide();   \n";
    echo "    $(\"#appcontent\").show(\"slide\",{direction:'up'},250);   \n";
    echo "});   \n";
    echo "var EditFleet = new Array ($emitedit); \n";
    echo "function SelectFleet (mode, owner_id, fleet_id)   \n";
    echo "{   \n";
    echo "    if (mode == \"edit\") {   \n";
    echo "        for (id=0; id<14; id++) {   \n";
    echo "            for(i=0; i<formMyFleet.elements.length; i++){   \n";
    echo "                if ( formMyFleet.elements[i].name == 'f['+(202+id)+']' && EditFleet[id]) formMyFleet.elements[i].value = EditFleet[id];   \n";
    echo "            }   \n";
    echo "        }   \n";
    echo "        if (EditFleet[14]) { formMyFleet.g.value = EditFleet[14]; formMyFleet.s.value = EditFleet[15]; formMyFleet.p.value = EditFleet[16]; }  \n";
    echo "        if (EditFleet[17]) formMyFleet.moon.checked = true;   \n";
    echo "    }   \n";
    echo "    document.formMyFleet.mode.value = mode;   \n";
    echo "    document.formMyFleet.owner_id.value = owner_id;   \n";
    echo "    document.formMyFleet.fleet_id.value = fleet_id;   \n";
    echo "    $(function() { $(\"#dialogMyFleet\").dialog({   \n";
    echo "        bgiframe: true, modal: true, width: 660, height: 370, zIndex: 3,   \n";
    echo "        close: function(event, ui) { window.location = \"".scriptname()."?page=myfleet&session=".$_GET['session']."\"; },   \n";
    echo "        buttons: { \"Сохранить\": function() {   \n";
    echo "            $(this).dialog('close');   \n";
    echo "            document.formMyFleet.submit ();   \n";
    echo "        }}   \n";
    echo "    }); });   \n";
    echo "}   \n";
    echo "$(function() {   \n";
    echo "    $(\".fleetimg\").click ( function () {   \n";
    echo "        $(this).children(\"input\").focus ();   \n";
    echo "    });   \n";
    echo "    $(\"#addfleet\").click ( function() {   \n";
    echo "        SelectFleet('add', ".$GlobalUser['user_id'].", 0);  \n";
    echo "    });   \n";
    echo "});   \n";
    echo "</script>   \n\n";

    // Контент.
    echo "<div id=\"appcontent\" class=\"ui-widget-content ui-corner-all\">   \n";
//        echo "<pre>"; print_r ($_GET); echo "</pre>";
    echo "    <!-- Таблица флотов -->   \n";
    echo "    <center><p><table id=\"fleettab\" class=\"ui-widget ui-widget-content\" >   \n";
    echo "    <thead>   \n";
    echo "    <tr class=\"ui-widget-header\">   \n";
    echo "        <th>Игрок</th> <th>Базируется</th> <th>&nbsp;</th> <th>&nbsp;</th>   \n";
    echo "        <th>М. трансп.</th> <th>Б. трансп.</th> <th>Л. истр.</th> <th>Т. истр.</th> <th>Крейсер</th> <th>Линк</th> <th>Колонизатор</th> <th>Переработчик</th> <th>Шп. зонд</th> <th>Бомб.</th> <th>Солн. спутник</th> <th>Уничт.</th> <th>ЗС</th> <th>Лин. Кр.</th> <th>&nbsp;</th>   \n";
    echo "    </tr>   \n";
    echo "    </thead>   \n";
    echo "    <tbody>   \n";
    $result = EnumFleet ();
    $rows = dbrows ($result);
    while ($rows--) {
        $fleet = dbarray ($result);
        $user = LoadUser ( $fleet['owner_id'] );
        if ( $GlobalUser['uni_id'] == 0 && $fleet['owner_id'] != $GlobalUser['user_id'] ) continue;       // Пользователям без командира показывать только свой флот.
        if ( $user['uni_id'] != $GlobalUser['uni_id'] ) continue;                                                     // Пользователям альянса, показывать только флот своего альянса.
        {
            if ( $fleet['g'] == 0 || $fleet['s'] == 0 || $fleet['p'] == 0 ) { $galaxyurl = ""; $deploy = "[?]"; }
            else { $galaxyurl = "\"".scriptname()."?page=galaxy&session=".$_GET['session']."&g=".$fleet['g']."&s=".$fleet['s']."&p=".$fleet['p']."\""; $deploy = "[".$fleet['g'].":".$fleet['s'].":".$fleet['p']."]"; }
            if ( $fleet['moon'] ) $moonimg = "<img src=\"../images/com_mond.gif\">";
            else $moonimg = "";
            if ( $fleet['f214'] > 0 ) $typeimg = "<img src=\"../images/com_mission_destroy.png\">";
            else if ( ($fleet['f204'] + $fleet['f205'] + $fleet['f206'] + $fleet['f207'] + $fleet['f215'] + $fleet['f211'] + $fleet['f213'] ) > 0 ) $typeimg = "<img src=\"../images/com_mission_acs.png\">";
            else $typeimg = "<img src=\"../images/com_mission_transport.png\">";
            echo "    <tr>   \n";
            echo "        <td><nobr><a href=\"".scriptname()."?page=pinfo&session=".$_GET['session']."&id=".$user['user_id']."\">".$user['gamename']."</a></nobr></td> <td><a href=$galaxyurl>$deploy</a></td> <td>$moonimg</td> <td>$typeimg</td>   \n        ";
            for ($i=202; $i<=215; $i++) {
                $n = $fleet["f$i"];
                if ($n) echo "<td>".nicenum($n)."</td>";
                else echo "<td>-</td>";
            }
            echo "\n";
            if ( $user['user_id'] == $GlobalUser['user_id'] ) {
                echo "        <td><table><tr><td><a href=\"".scriptname()."?page=myfleet&mode=edit&owner_id=".$user['user_id']."&fleet_id=".$fleet['fleet_id']."&session=".$_GET['session']."\" class=\"ui-icon ui-icon-pencil\"></a></td>";
                echo "<td><a href=\"".scriptname()."?page=myfleet&mode=delete&owner_id=".$user['user_id']."&fleet_id=".$fleet['fleet_id']."&session=".$_GET['session']."\" class=\"ui-icon ui-icon-trash\"></a></td></tr></table></td>   \n";
            }
            echo "    </tr>   \n";
        }
    }
    echo "    </tbody>   \n";
    echo "    </table></p>   \n";
    echo "    <p><table width=\"96%\">   \n";
    echo "    <tr><td align=right><button id=\"addfleet\" class=\"fg-button ui-state-default ui-corner-all\" type=\"button\" >Добавить флот</button></td>   \n";
    echo "    </table></p>   \n";
    echo "    </center><p> </p>   \n\n";

    echo "    <!-- Диалог редактирования флота -->   \n";
    echo "    <div id=\"dialogMyFleet\" title=\"Выбор флота\" style=\"display: none;\">   \n";
    echo "    <center><form name=\"formMyFleet\" action=\"".scriptname()."?page=myfleet&session=".$_GET['session']."\" method=\"POST\">   \n";
    echo "    <input type=\"hidden\" name=\"mode\"> <input type=\"hidden\" name=\"owner_id\"> <input type=\"hidden\" name=\"fleet_id\"> \n";
    echo "    <table><tr><td colspan=4><h3 class=\"ui-widget-header ui-corner-all\"><small>Боевой флот</small></h3></td><td>&nbsp;</td>   \n";
    echo "    <td colspan=3><h3 class=\"ui-widget-header ui-corner-all\"><small>Небоевой флот</small></h3></tr>   \n";
    echo "    <tr>     <td><div class=\"fleetimg ship204\"><input type=\"text\" name=\"f[204]\"></div></td>   \n";
    echo "    <td><div class=\"fleetimg ship205\"><input type=\"text\" name=\"f[205]\"></div></td>   \n";
    echo "    <td><div class=\"fleetimg ship206\"><input type=\"text\" name=\"f[206]\"></div></td>   \n";
    echo "    <td><div class=\"fleetimg ship207\"><input type=\"text\" name=\"f[207]\"></div></td>   \n";
    echo "    <td>&nbsp;</td>   \n";
    echo "    <td><div class=\"fleetimg ship202\"><input type=\"text\" name=\"f[202]\"></div></td>   \n";
    echo "    <td><div class=\"fleetimg ship203\"><input type=\"text\" name=\"f[203]\"></div></td>   \n";
    echo "    <td><div class=\"fleetimg ship208\"><input type=\"text\" name=\"f[208]\"></div></td>    </tr>   \n";
    echo "    <tr>    <td><div class=\"fleetimg ship215\"><input type=\"text\" name=\"f[215]\"></div></td>   \n";
    echo "    <td><div class=\"fleetimg ship211\"><input type=\"text\" name=\"f[211]\"></div></td>   \n";
    echo "    <td><div class=\"fleetimg ship213\"><input type=\"text\" name=\"f[213]\"></div></td>   \n";
    echo "    <td><div class=\"fleetimg ship214\"><input type=\"text\" name=\"f[214]\"></div></td>   \n";
    echo "    <td>&nbsp;</td>   \n";
    echo "    <td><div class=\"fleetimg ship209\"><input type=\"text\" name=\"f[209]\"></div></td>   \n";
    echo "    <td><div class=\"fleetimg ship210\"><input type=\"text\" name=\"f[210]\"></div></td>   \n";
    echo "    <td><div class=\"fleetimg ship212\"><input type=\"text\" name=\"f[212]\"></div></td>    </tr>   \n";
    echo "    <tr><td>&nbsp;</td></tr>   \n";
    echo "    <tr><td colspan=10>Базирование флота &nbsp; <input type=\"text\" name=\"g\" size=1>:<input type=\"text\" name=\"s\" size=1>:<input type=\"text\" name=\"p\" size=1>   \n";
    echo "    &nbsp; &nbsp; <img src=\"../images/com_mond.gif\" width=16 height=16> <input type=\"checkbox\" name=\"moon\"></td></tr>   \n";
    echo "    </table></form></center>   \n";
    echo "    </div>   \n";
    echo "</div>   \n\n";
    if ($showedit) echo "<script type=\"text/javascript\">SelectFleet('edit', ".$_GET['owner_id'].", ".$_GET['fleet_id']."); </script>\n";

    // Всплывающие подсказки.
    echo "<script type=\"text/javascript\">   \n";
    echo "    $(\".ship202\").wTooltip({content: \"Малый транспорт\" });   \n";
    echo "    $(\".ship203\").wTooltip({content: \"Большой транспорт\" });   \n";
    echo "    $(\".ship204\").wTooltip({content: \"Лёгкий истребитель\" });   \n";
    echo "    $(\".ship205\").wTooltip({content: \"Тяжёлый истребитель\" });   \n";
    echo "    $(\".ship206\").wTooltip({content: \"Крейсер\" });   \n";
    echo "    $(\".ship207\").wTooltip({content: \"Линкор\" });   \n";
    echo "    $(\".ship208\").wTooltip({content: \"Колонизатор\" });   \n";
    echo "    $(\".ship209\").wTooltip({content: \"Переработчик\" });   \n";
    echo "    $(\".ship210\").wTooltip({content: \"Шпионский зонд\" });   \n";
    echo "    $(\".ship211\").wTooltip({content: \"Бомбардировщик\" });   \n";
    echo "    $(\".ship212\").wTooltip({content: \"Солнечный спутник\" });   \n";
    echo "    $(\".ship213\").wTooltip({content: \"Уничтожитель\" });   \n";
    echo "    $(\".ship214\").wTooltip({content: \"Звезда смерти\" });   \n";
    echo "    $(\".ship215\").wTooltip({content: \"Линейный крейсер\" });   \n";
    echo "    jQuery.each ($(\"img[src*=mond]\"), function () { $(this).wTooltip({content: \"Луна\" }) });   \n";
    echo "    jQuery.each ($(\"img[src*=mission_acs]\"), function () { $(this).wTooltip({content: \"Боевой флот\" }) });   \n";
    echo "    jQuery.each ($(\"img[src*=mission_transport]\"), function () { $(this).wTooltip({content: \"Небоевой флот\" }) });   \n";
    echo "    jQuery.each ($(\"img[src*=mission_destroy]\"), function () { $(this).wTooltip({content: \"Флот ЗС\" }) });   \n";
    echo "    jQuery.each ($(\"a[class*=ui-icon-pencil]\"), function () { $(this).wTooltip({content: \"Изменить\" }) });   \n";
    echo "    jQuery.each ($(\"a[class*=ui-icon-trash]\"), function () { $(this).wTooltip({content: \"Удалить\" }) });   \n";
    echo "    jQuery.each ($(\"a[href*=uinfo]\"), function () { $(this).wTooltip({content: \"Информация о пользователе\" }) });   \n\n";
    echo "</script>   \n";
    PageFooter ();
}

// Настройки.
function PageOptions ()
{
    global $GlobalUser;
    if (CheckSession ( $_GET['session'] ) == FALSE) die ();
    $optres = "";
    if ( method() == "POST" )
    {
        if (!$GlobalUser['validated']) {
            if ( $_GET['mode'] === "sendack" ) SendActivationMail ($GlobalUser['user_id'] );
            else if ( $_GET['mode'] === "changemail" ) $optres = ChangeEmail ( $_POST['email'], $_POST['pass'] );
        }
        else {
            if ($GlobalUser['com']) {
                if ($_GET['mode'] === "saveally") $optres = RenameMyAlly ($_POST['name']);
                else if ($_GET['mode'] === "saveuni") $optres = RenameMyUni ($_POST['name']);
                else if ($_GET['mode'] === "invite") $optres = InviteUser ($_POST['name']);
            }
            if ( $_GET['mode'] === "saveuser" ) {
                $res = ChangeGamename ( $_POST['gamename'] );  if ($res !== "") $optres .= "<br/>$res";
                $res = ChangePassword ( $_POST['oldpass'], $_POST['newpass1'], $_POST['newpass2'] ); if ($res !== "") $optres .= "<br/>$res";
                $res = ChangeEmail ( $_POST['email'], $_POST['oldpass'] ); if ($res !== "") $optres .= "<br/>$res";
                if ($_POST['remove'] === "on" ) $res = RemoveAccount ( 1, time() + 7*24*60*60, $_POST['oldpass'] );
                else $res = RemoveAccount ( 0, 0, "" );
                if ($res !== "") $optres .= "<br/>$res";
            }
            else if ( $_GET['mode'] === "gensig" && $GlobalUser['uni_id']) UpdateSignature ();
            else if ( $_GET['mode'] === "leaveally" && $GlobalUser['uni_id']) LeaveAlly ();
            else if ( $_GET['mode'] === "takeover" && $GlobalUser['uni_id'] && $GlobalUser['com']) TakeoverAlly ($_POST['id']);
        }
    }
    PageHeader ("Командир - Настройки");
    LeftMenu ();
    if ($optres !== "" ) {
        echo "<script type=\"text/javascript\">   \n";
        echo "    $(function() {   \n";
        echo "        $(\"#optionsResult\").dialog({   \n";
        echo "            bgiframe: true, modal: true, zIndex: 3,   \n";
        echo "            close: function() { window.location = window.location; },   \n";
        echo "            buttons: { \"Ok\": function() { $(this).dialog('close'); window.location = window.location; } }   \n";
        echo "        });   \n";
        echo "    });   \n";
        echo "</script>   \n";
    }
    echo "<script type=\"text/javascript\">$(function() { $(\"#tabs\").tabs(); }); </script>   \n";
    echo "<div id=\"appcontent\" class=\"ui-widget-content ui-corner-all\">      \n";
//        echo "<pre>"; print_r ($_POST); print_r ($_GET);  echo "</pre>";   
    echo "<div id=\"tabs\">   \n";
    echo "    <ul>   \n";
    echo "        <li><a href=\"#tabs-1\">Аккаунт</a></li>   \n";
    if ( $GlobalUser['uni_id'] ) echo "		<li><a href=\"#tabs-2\">Альянс</a></li>   \n";
    echo "    </ul>   \n";
    echo "    <div id=\"tabs-1\">   \n";
    if ( !$GlobalUser['validated'] ) {
        echo "            <p> <font color=red>Аккаунт не активирован!</font>   \n";
        echo "            Для активации воспользуйтесь ссылкой, отправленной вам на почту при регистрации, или закажите новую.<br/>   \n";
        echo "            Аккаунт будет автоматически удален ".date ("j M Y G:i:s", $GlobalUser['validate_until'])." </p>   \n";
        echo "            <p><form action=\"".scriptname()."?page=options&mode=sendack&session=".$_GET['session']."\" method=\"POST\"><button class=\"fg-button ui-state-default ui-corner-all\"  type=\"submit\">Выслать активационную ссылку</button> </form>  \n";
        echo "            <p><br/>   \n";
        echo "                    <p> Вы также можете сменить адрес электронной почты, если вы указали его неправильно при регистрации. Для этого нужно указать свой пароль. </p>   \n";
        echo "                    <form action=\"".scriptname()."?page=options&mode=changemail&session=".$_GET['session']."\" method=\"POST\"> <table>    \n";
        echo "                    <tr><td>Текущий адрес</td><td><input type=\"text\" name=\"email\" size=20 value=\"".$GlobalUser['email']."\"></td></tr>   \n";
        echo "                    <tr><td>Ваш пароль</td><td><input type=\"password\" name=\"pass\" size=20></td></tr>   \n";
        echo "                    <tr><td colspan><button class=\"fg-button ui-state-default ui-corner-all\"  type=\"submit\">Сменить почту</button></td></tr>   \n";
        echo "                    </table> </form>  \n";
        echo "            </p>   \n";
    }
    else {
        echo "            <form action=\"".scriptname()."?page=options&mode=saveuser&session=".$_GET['session']."#tabs-1\" method=\"POST\">   \n";
        echo "                    <table>   \n";
        echo "                    <tr><td>Логин</td><td><input type=\"text\" value=\"".$GlobalUser['login']."\" size=20 disabled ></td></tr>   \n";
        echo "                    <tr><td>Игровое имя</td><td><input type=\"text\" name=\"gamename\" size=20 value=\"".$GlobalUser['gamename']."\"></td></tr>   \n";
        echo "                    <tr><td>Старый пароль</td><td><input type=\"password\" name=\"oldpass\" size=20></td></tr>   \n";
        echo "                    <tr><td>Новый пароль</td><td><input type=\"password\" name=\"newpass1\" size=20></td></tr>   \n";
        echo "                    <tr><td>Новый пароль (подтв.)</td><td><input type=\"password\" name=\"newpass2\" size=20></td></tr>   \n";
        echo "                    <tr><td>Электронный адрес</td><td><input type=\"text\" name=\"email\" size=20 value=\"".$GlobalUser['email']."\"></td></tr>   \n";
        echo "                    <tr><td>&nbsp;</td></tr>   \n";
        if ( $GlobalUser['remove'] ) { $chk = "CHECKED"; $remstr = "<font color=red>Аккаунт будет удален ".date ("j M Y G:i:s", $GlobalUser['remove_until'])."</font>"; }
        else { $chk = ""; $remstr = "Аккаунт будет удален через 7 дней."; }
        echo "                    <tr><td>$remstr</td><td><input type=\"checkbox\" name=\"remove\" $chk><b>Удалить аккаунт</b></td></tr>   \n";
        echo "                    <tr><td>&nbsp;</td></tr>   \n";
        echo "                    </table>   \n";
        echo "            <hr/><button class=\"fg-button ui-state-default ui-corner-all\"  type=\"submit\">Сохранить настройки</button> <font color=red>Все настройки применяются одновременно!</font>   \n";
        echo "            </form>   \n";
    }
    echo "    </div>   \n";
    if ( $GlobalUser['uni_id'] ) {
        $uni = LoadUni ($GlobalUser['uni_id']);
        $rank = $GlobalUser['rank'];
        $ranks = array ( "minus", "plus" );
        echo "	<div id=\"tabs-2\">   \n";
        echo "            <p>   \n";
        echo "            <form action=\"".scriptname()."?page=options&mode=gensig&session=".$_GET['session']."#tabs-2\" method=\"POST\">   \n";
        echo "            Сигнатура для Командирского плагина <input type=\"text\" name=\"sig\" size=35 value=\"".$GlobalUser['signature']."\" onclick=\"this.select();\"><button class=\"fg-button ui-state-default ui-corner-all\" type=\"submit\">Изменить</button></form>   \n";
        echo "            <font color=red>Важно!</font> Никому не доверяйте свою сигнатуру, иначе злоумышленник сможет обновлять базу под Вашим именем.   \n";
        echo "            После каждого изменения сигнатуры её необходимо также обновить в Настройках OGame.<hr/>   \n";
        echo "            </p>   \n";
        echo "            <p> Ваши привилегии: <br/><table class=\"ui-widget ui-widget-content\">   \n";
        echo "                <tr><td><span class=\"ui-icon ui-icon-".$ranks[($rank >> 5)&1]."\" style=\"float: left;\"></span> Дипломат </td></tr>   \n";
        echo "                <tr><td><span class=\"ui-icon ui-icon-".$ranks[($rank >> 4)&1]."\" style=\"float: left;\"></span> Обновлять галактику </td></tr>   \n";
        echo "                <tr><td><span class=\"ui-icon ui-icon-".$ranks[($rank >> 3)&1]."\" style=\"float: left;\"></span>Обновлять статистику </td></tr>   \n";
        echo "                <tr><td><span class=\"ui-icon ui-icon-".$ranks[($rank >> 2)&1]."\" style=\"float: left;\"></span>Добавлять шпионские доклады </td></tr>   \n";
        echo "                <tr><td><span class=\"ui-icon ui-icon-".$ranks[($rank >> 1)&1]."\" style=\"float: left;\"></span>Поиск игроков и альянсов </td></tr>   \n";
        echo "                <tr><td><span class=\"ui-icon ui-icon-".$ranks[($rank >> 0)&1]."\" style=\"float: left;\"></span>Поиск в шпионских докладах </td></tr> </table>   \n";
        echo "            </p><br/>   \n";
        if ( $GlobalUser['com'] ) {
            echo "            <table><tr>   \n";
            echo "            <td><p>Изменить аббревиатуру альянса <br/>   \n";
            echo "            <form action=\"".scriptname()."?page=options&mode=saveally&session=".$_GET['session']."#tabs-2\" method=\"POST\">   \n";
            echo "            <input type=\"text\" name=\"name\" size=20 value=\"".$uni['tag']."\"><button class=\"fg-button ui-state-default ui-corner-all\"  type=\"submit\">Ok</button></form></p><br/></td>   \n";
            echo "            <td><p>Изменить название вселенной <br/>   \n";
            echo "            <form action=\"".scriptname()."?page=options&mode=saveuni&session=".$_GET['session']."#tabs-2\" method=\"POST\">   \n";
            echo "            <input type=\"text\" name=\"name\" size=20 value=\"".$uni['name']."\"><button class=\"fg-button ui-state-default ui-corner-all\"  type=\"submit\">Ok</button></form></p><br/></td>   \n";
            echo "            <td><p>Пригласить игрока (укажите логин)<br/>   \n";
            echo "            <form action=\"".scriptname()."?page=options&mode=invite&session=".$_GET['session']."#tabs-2\" method=\"POST\">   \n";
            echo "            <input type=\"text\" name=\"name\" size=20><button class=\"fg-button ui-state-default ui-corner-all\"  type=\"submit\">Ok</button></form></p><br/></td></tr></table>   \n";
            echo "            <table><tr><td> <form action=\"".scriptname()."?page=options&mode=takeover&session=".$_GET['session']."#tabs-2\" method=\"POST\"> \n";
            $result = EnumUsers ($GlobalUser['uni_id']);
            $total = $num = dbrows ($result);
            if ($total > 1) echo "            <select name=\"id\">   \n";
            while ($num--) {
                $user = dbarray ($result);
                if ( $user['com'] ) continue;
                echo "<option value=\"".$user['user_id']."\">".$user['gamename']."</value>   \n";
            }
            if ($total > 1) {
                echo "            </select>   \n";
                echo "            <button class=\"fg-button ui-state-default ui-corner-all\"  type=\"submit\">Передать управление</button></td> </form>  \n";
            }
        }
        echo "            <td><form action=\"".scriptname()."?page=options&mode=leaveally&session=".$_GET['session']."#tabs-2\" method=\"POST\">   \n";
        echo "            <button class=\"fg-button ui-state-default ui-corner-all\"  type=\"submit\">Покинуть альянс</button> </form></td></tr></table>  \n";
        echo "	</div>   \n";
    }
    echo "</div>   \n";
    echo "<div id=\"optionsResult\" title=\"Настройки\" style=\"display: none;\">   \n";
    echo "<p><span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 50px 0;\"></span>$optres</p></div>   \n";
    echo "</div>   \n";
    PageFooter ();
}

// Информация об игроке.
function PagePlayerInfo ()
{
    global $GlobalUser;
    if (CheckSession ( $_GET['session'] ) == FALSE) die ();
    PageHeader ("Командир - Информация об игроке");
    LeftMenu ();
    echo "<div id=\"appcontent\" class=\"ui-widget-content ui-corner-all\">   \n";

    $uni_id = $GlobalUser['uni_id']; $pid = $_GET['id'];
    $pname = GetPlayerName ($uni_id, $pid);
    echo "Игрок - $pname<br/>";
    $aname = GetAllyName ( $uni_id, GetPlayerAlly ($uni_id, $pid) );
    echo "Альянс - $aname<br/>";

    $stat1 = LoadPlayerStat ( $uni_id, $pid, 1 );
    $stat2 = LoadPlayerStat ( $uni_id, $pid, 2 );
    $stat3 = LoadPlayerStat ( $uni_id, $pid, 3 );
    echo "Статистика по очкам - Место ".nicenum($stat1['place']).", Очки ".nicenum($stat1['score'])."<br/>";
    echo "Статистика по флотам - Место ".nicenum($stat2['place']).", Очки ".nicenum($stat2['score'])."<br/>";
    echo "Статистика по исследованиям - Место ".nicenum($stat3['place']).", Очки ".nicenum($stat3['score'])."<br/>";

    $planets = EnumPlayerPlanets ( $uni_id, $pid );
    $num = dbrows ($planets); echo "<br>$num<br>";
    echo "<table class=\"ui-widget ui-widget-content\"><tr><th>Координаты</th><th>Название</th><th>ПО</th><th>Активность</th><th>Добавлена</th></tr>";
    while ($num--) {
        $pl = dbarray ($planets);
//        print_r ($pl); echo "<br/>";
        echo "<tr>";
        echo "<td>" . $pl['g'] . ":" . $pl['s'] . ":" . $pl['p'] . "</td>";
        if ( $pl['type'] == 0 ) echo "<td>" . $pl['name'] . " (Луна)</td>";
        else echo "<td>" . $pl['name'] . "</td>";
        if ( $pl['dm'] + $pl['dk'] ) {
            echo "<td>M: " . $pl['dm'] . " K: " . $pl['dk'] . "</td>";
        }
        else echo "<td>&nbsp;</td>";
        echo "<td>". $pl['activity'] . "</td>";
        echo "<td>" . date ( "D M j G:i:s", $pl['date']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "</div>   \n\n";
    PageFooter ();
}

// О программе.
function PageAbout ()
{
    global $version;
    if (CheckSession ( $_GET['session'] ) == FALSE) die ();
    PageHeader ("Командир - О программе");
    LeftMenu ();
    echo "<script type=\"text/javascript\">   \n";
    echo "$(function() {   \n";
    echo "    $(\"#appcontent\").hide();   \n";
    echo "    $(\"#appcontent\").show(\"slide\",{direction:'up'},250);   \n";
    echo "});   \n";
    echo "</script>   \n";
    echo "<div id=\"appcontent\" class=\"ui-widget-content ui-corner-all\">   \n";
    echo "<p><h2>О программе \"Командир\" $version</h2></p>\n";
    echo "<p><h3>\n";
    echo "Командир предназначен для управления альянсом, для сохранения и анализирования Галактики, Статистики и шпионских докладов в браузерной игре <a href=\"http://ogame.ru\" target=_blank>OGame</a>.<br>\n";
    echo "&copy; Andorianin, 2009. Все права на исходный код принадлежат автору и команде <a href=\"http://ogame.ru\" target=_blank>OGame.ru</a>.<br>\n";
    echo "<br>\n";
    echo "По всем вопросам, касательно перевода, модифицировании исходного кода, новых предложениях и обнаруженных ошибках обращаться по электронной почте:<br> <a href=\"mailto:ogamespec@gmail.com\">ogamespec at gmail dot com</a><br/>\n";
    echo "<br>\n";
    echo "Все права на исходную графику и текст, заимствованные из игры OGame принадлежат <a href=\"http://gameforge.de\" target=_blank>GameForge GmbH</a>.<br>\n";
    echo "<i>Gameforge Productions GmbH<br>\n";
    echo "Albert Nestler-Strasse 8 <br>\n";
    echo "D-76131 Karlsruhe<br></i>\n";
    echo "<br>\n";
    echo "</h3></p>\n";
    echo "<p><h3>\n";
    echo "В программе используется технология jQuery. <br>\n";
    echo "<a href=\"http://jqueryui.com\" target=_blank><img src=\"../images/com_jqueryui.png\" style=\"border: 0px;\"></a>\n";
    echo "</h3></p>\n";
    echo "</div>   \n";
    PageFooter ();
}

// Выход.
function PageLogout ()
{
    if (CheckSession ( $_GET['session'] ) == FALSE) die ();
    Logout ( $_GET['session'] );
    RedirectHome ();
}

// Приглашение пользователя.
function PageInvite ()
{
    global $GlobalUser;
    if (CheckSession ( $_GET['session'] ) == FALSE) die ();
    if ( $_GET['mode'] === "accept" ) AcceptInvite ($GlobalUser['user_id']);
    else if ( $_GET['mode'] === "reject" ) RejectInvite ($GlobalUser['user_id']);
    PageOverview ();
}

// *******************************************************************************
// Выбор страницы.

if ( !file_exists ("config.php") ) InstallPage ();
if ( key_exists ("page", $_GET) && $_GET["page"] === "reg" && method() === "GET") RegPage ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "reg" && method() === "POST") RegisterNew ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "validate" && method() === "GET") ValidateUser ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "invite" && method() === "POST") PageInvite ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "lostpass" && method() === "GET" ) echo "LostPass";
else if ( key_exists ("page", $_GET) && $_GET["page"] === "overview" && method() === "GET" ) PageOverview ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "galaxy") PageGalaxy ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "update") PageUpdate ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "status") PageStatus ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "myfleet") PageFleet ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "options") PageOptions ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "pinfo") PagePlayerInfo ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "about") PageAbout ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "logout" && method() === "GET" ) PageLogout ();
else if ( key_exists ("page", $_GET) && $_GET["page"] === "login" && method() === "POST" ) Login ( $_POST['login'], $_POST['pass'] );
else HomePage ();

?>