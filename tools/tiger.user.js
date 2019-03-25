// ==UserScript==
// @name           TigerThumb
// @namespace    andorianin
// @description     Thumbnail icons for Tiger Alliance board
// @include        http://tigeralliance.flybb.ru/*
// ==/UserScript==


var img = document.getElementsByTagName("img");

for (var i = 0; i < img.length; i++)
{
    if ( img[i].src.indexOf ("images/thumb_up.gif") != -1 )
    {
        img[i].src = "http://ogamespec.com/imgstore/thumb_up.gif";
    }

    if ( img[i].src.indexOf ("images/thumb_dn.gif") != -1 )
    {
        img[i].src = "http://ogamespec.com/imgstore/thumb_down.gif";
    }
}
