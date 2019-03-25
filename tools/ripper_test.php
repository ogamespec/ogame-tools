<?php

$text = $_GET['text'];

    $text = str_replace ( "-", "+", $_GET['text']);
    $text = str_replace ( "_", "/", $text);
    $text = base64_decode ($text) . "\n\n";

$f = fopen ('ripper_test.txt', 'a');
fwrite ( $f,  $text);
fclose ($f);

?><br>.