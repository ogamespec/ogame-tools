#!/usr/local/bin/php

<?php
    $f = fopen ( "~/public_html/tools/galaxy.txt", "a" );
    fwrite ( $f, time () . "\n" );
    fclose ( $f );
?>