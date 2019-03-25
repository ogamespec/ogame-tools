<?php

// -----------------------------------------------------------------------------------------------------------------
// Создание аккаунта.

function PageCreate ()
{
    if ( !ServerEnabled() ) PageHome (10006);
    if ( IPBanned () ) PageHome (10001);
    $now = time ();
    $last = GetIPCreateTime ();
    if ( ($now - $last) < 60 * 60 ) PageHome (10003);
    else {
        $acc = CreateAccount ();
        UpdateIPCreateTime ($acc['acc_id']);
        echo "<html><head><meta http-equiv='refresh' content='0;url=".scriptname()."?page=overview&sig=".$acc['sig']."&lgn=1' /></head><body></body></html>";
    }
}

?>