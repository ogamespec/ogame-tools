<?php

// -----------------------------------------------------------------------------------------------------------------
// Отправление сообщения.

function PageShout ()
{
    if ( IPBanned () ) PageHome (10001);
    $acc = LoadAccountBySig ( $_GET['sig'] );
    if ($acc == null) PageHome (10002);
    if ( get_magic_quotes_gpc() ) $text = stripslashes( $_POST['text'] ) ;
    else $text =  $_POST['text']  ;
    AddMessage ( $acc['acc_id'], "Общее сообщение", $text );
    echo "<html><head><meta http-equiv='refresh' content='0;url=".scriptname()."?page=overview&sig=".$acc['sig']."' /></head><body></body></html>";
    AddTraffic ( $acc, strlen ($text), 0 );
}

?>