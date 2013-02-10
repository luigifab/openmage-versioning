<?php
/**
 * Created M/25/01/2011
 * Updated L/23/04/2012
 * Version 10
 *
 * Copyright 2011-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://redmine.luigifab.info/projects/magento/wiki/versioning
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL) as published
 * by the free software foundation, either version 2 of the license, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but without any warranty, without even the implied warranty of
 * merchantability or fitness for a particular purpose. See the
 * GNU General Public License (GPL) for more details.
 */

date_default_timezone_set('UTC');

header('Pragma: public');
header('Cache-Control: public');
header('Content-Type: image/svg+xml; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+1 week')).' GMT');

echo '<?xml version="1.0" encoding="utf-8" standalone="yes"?>',"\n";

?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="100" height="70" xmlns="http://www.w3.org/2000/svg" version="1.1">
<svg x="5" y="5">
<rect width="90" height="60" fill="#111" />
<circle cx="29" cy="45" r="6" fill="#222" id="exclamB" />
<polyline points="22,10 36,10 34,35 24,35" fill="#222" id="exclamA" />
<polyline points="60,10 70,30 60,50 50,30" fill="#222" id="losange" />
</svg>
<script type="text/javascript"><![CDATA[
var exclam = '#222', losange = 'orange';
window.addEventListener('load', go, false);
function go() {
if ((document.getElementById('losange').getAttribute('fill') !== 'orange') && (document.getElementById('losange').getAttribute('fill') !== '#222')) {
losange = document.getElementById('losange').getAttribute('fill');
}
if (document.getElementById('exclamA').getAttribute('fill') !== '#222') {
exclam = document.getElementById('exclamA').getAttribute('fill');
}
if (document.getElementById('losange').getAttribute('fill') === '#222') {
document.getElementById('losange').setAttribute('fill', losange);
document.getElementById('exclamA').setAttribute('fill', exclam);
document.getElementById('exclamB').setAttribute('fill', exclam);
}
else {
document.getElementById('losange').setAttribute('fill', '#222');
document.getElementById('exclamA').setAttribute('fill', '#222');
document.getElementById('exclamB').setAttribute('fill', '#222');
}
window.setTimeout(go, 700);
}
]]></script>
</svg>