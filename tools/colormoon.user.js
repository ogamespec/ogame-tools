// ==UserScript==
// @include http://*ogame*/game/*
// @name colormoon
// @author Mladen Pejakoviï¿_+
// @namespace http://superogame.sourceforge.net/
// @version 1.5.1
// @description SuperOgame colored moons.
// ==/UserScript==

var soSet = new Array();

soSet['soGen_Moons'] = 1; // Highlight Moons in planets list
soSet['soGen_MoonsC'] = '#993322'; // Highlighting color for Moons in planets list

function xpaths(path){
var xpathsR = document.evaluate(path,document,null,XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE,null);
return xpathsR;}

var opts = xpaths('//select[starts-with(@onchange,"haha(this)")]/option');
for (var i = opts.snapshotLength - 1; i >= 0; i--) {
    var opt = opts.snapshotItem(i);
    if (opt.innerHTML.match(/\(./)) opt.setAttribute('style','background-color:'+soSet['soGen_MoonsC']);
}
