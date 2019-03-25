<?php

// Общая альянсовая заметка. Может править кто угодно.

$version = "0.04";

$SecretWord = "SpecnazNotesRulez";

function method () { return $_SERVER['REQUEST_METHOD']; }
function hostname () {
    $host = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER["SCRIPT_NAME"];
    $pos = strrpos ( $host, "/notes.php" );
    return substr ( $host, 0, $pos+1 );
}

function GenerateID ( $name )
{
    global $SecretWord;
    return md5 ( $name . $SecretWord . time() );
}

function StartPage ()
{
?>
<br> 
<br> 
<br> 

<table width="99%">
<tr><td><center> 
Создать новую заметку <br>
<form action="notes.php" method=post>
Название : <input type="text" name="title" size=80> <input type=submit value='Создать'>
</form>
</center></td></tr>
</table>

<table width="99%">
<tr><td><center> 
Последние заметки: <br>
<?php
    $query = "select * from specnotes order by created desc limit 30";
    $result = dbquery ( $query );
    $rows = dbrows ($result);
    while ($rows--) 
    {
        $note = dbarray ($result);
        echo "<tr><td><center><a href='notes.php?id=".$note['id']."'>".$note['title']."</a></center></td></tr>\n";
    }
?>
</center></td></tr>
</table>
<?php
}

function ShowNote ($note)
{
?>
<br> 
<br> 
<br> 

<table width="99%"><tr><td><center> 
Заметка : <b><?=stripslashes($note['title']);?></b> 
<br> 
<form action="notes.php?id=<?=$note['id'];?>" method=post> 
<table> 
<tr><th><textarea cols='150' rows='40' name='text'><?=stripslashes($note['text']);?></textarea></th></tr> 
<tr><th><input onclick='this.select();' style='width: 100%;' size=120 value='[url=<?=hostname();?>notes.php?id=<?=$note['id'];?>]Заметка: <?=$note['title'];?>[/url]' type='text'></th></tr>
<tr><th><input onclick='this.select();' style='width: 100%;' size=120 value='<?=hostname();?>notes.php?id=<?=$note['id'];?>' type='text'></th></tr>
<tr><td><input type=submit value='Сохранить'>
<font size=1>&nbsp;&nbsp;&nbsp;Чтобы удалить заметку оставьте это поле пустым.</font>
</td></tr> 
</table> 
</form> 
<a href="notes.php">На главную</a>
</center></td></tr></table> 
<?php
}

require_once "config.php";
require_once "db.php";

// Соединиться с БД.
dbconnect ($db_host, $db_user, $db_pass, $db_name);
dbquery("SET NAMES 'utf8';");
dbquery("SET CHARACTER SET 'utf8';");
dbquery("SET SESSION collation_connection = 'utf8_general_ci';");

// Проверить готовность БД. Если нет таблицы в базе - создать.
$result = dbquery ( "show tables like 'specnotes'" );
if ( dbrows ($result) == 0 )
{
    $query = "create table specnotes (id VARCHAR(32), title TEXT, text TEXT, created INT UNSIGNED, PRIMARY KEY (id) );";
    dbquery  ($query);
}

?>

<HTML> 
<HEAD><link rel="stylesheet" type="text/css" href="../evolution/formate.css"> 
<meta http-equiv='content-type' content='text/html; charset=utf-8' /> 
<TITLE>Заметки</TITLE> 
</HEAD> 
 
<BODY>

<?php

// Главная страница.
// ---------------------------------------------------------------------------------------------------------------------------------

    if ( !key_exists ( 'id', $_GET ) )
    {

        if ( method() === "POST" && $_POST['title'] !== "" )         // Создать новую заметку.
        {
            $now = time ();
            $title = addslashes ( htmlspecialchars ($_POST['title']) );
            $id = GenerateID ( $title );
            $query = "insert into specnotes (id,title,text,created) values ('".$id."','".$title."','Новая заметка',".$now.") ";
            dbquery ( $query );
            $note = array ( 'id'=>$id, 'title'=>$title, 'text'=>'Новая заметка', 'created'=>$now );
            ShowNote ( $note );
        }
        else StartPage ();

// Загрузить и показать заметку
// ---------------------------------------------------------------------------------------------------------------------------------

    }
    else
    {
        if ( method() === "POST" && key_exists ( 'text', $_POST ) )        // Сохранить изменения.
        {
            if ( $_POST['text'] === "" )
            {
                $query = "delete from specnotes where id = '".$_GET['id']."'";
                dbquery ($query);
            }
            else
            {
                $text = addslashes ( htmlspecialchars ( $_POST['text'] ) );
                $query = "update specnotes set text = '".$text."', created = ".time()." where id = '".$_GET['id']."'";
                dbquery ( $query );
            }
        }

        $query = "select * from specnotes where id = '" . $_GET['id'] . "';";
        $result = dbquery ( $query );
        if ( dbrows ($result) == 0 )
        {
            StartPage ();
        }
        else
        {
            $note = dbarray ( $result );
            ShowNote ($note);
        }
    
    }
?>

<div style='position: absolute; bottom: 0px; right: 0px; z-index: 5;'> 
<table  ><tr><td><b> 
<?=$version;?>
</b></td></tr></table></div>
 
</BODY> 
</HTML>