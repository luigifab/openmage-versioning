<?php
/**
 * Created S/05/05/2012
 * Updated D/06/05/2012
 * Version 3
 *
 * Copyright 2012-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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
<svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" version="1.1" id="root" style="background-color:rgba(0,0,0,0.1); fill:rgba(0,0,0,0.1);">
	<defs>
		<linearGradient id="gradient-black-blue" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="black" /><stop offset="100%" stop-color="blue" />
		</linearGradient>
		<linearGradient id="gradient-black-red" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="black" /><stop offset="100%" stop-color="red" />
		</linearGradient>
		<linearGradient id="gradient-black-limegreen" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="black" /><stop offset="100%" stop-color="limegreen" />
		</linearGradient>
		<linearGradient id="gradient-black-chocolate" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="black" /><stop offset="100%" stop-color="chocolate" />
		</linearGradient>
		<linearGradient id="gradient-black-orange" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="black" /><stop offset="100%" stop-color="orange" />
		</linearGradient>
		<linearGradient id="gradient-black-hotpink" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="black" /><stop offset="100%" stop-color="hotpink" />
		</linearGradient>
		<linearGradient id="gradient-black-silver" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="black" /><stop offset="100%" stop-color="silver" />
		</linearGradient>
		<linearGradient id="gradient-black-khaki" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="black" /><stop offset="100%" stop-color="khaki" />
		</linearGradient>

		<linearGradient id="gradient-blue-black" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="blue" /><stop offset="100%" stop-color="black" />
		</linearGradient>
		<linearGradient id="gradient-blue-red" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="blue" /><stop offset="100%" stop-color="red" />
		</linearGradient>
		<linearGradient id="gradient-blue-limegreen" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="blue" /><stop offset="100%" stop-color="limegreen" />
		</linearGradient>
		<linearGradient id="gradient-blue-chocolate" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="blue" /><stop offset="100%" stop-color="chocolate" />
		</linearGradient>
		<linearGradient id="gradient-blue-orange" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="blue" /><stop offset="100%" stop-color="orange" />
		</linearGradient>
		<linearGradient id="gradient-blue-hotpink" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="blue" /><stop offset="100%" stop-color="hotpink" />
		</linearGradient>
		<linearGradient id="gradient-blue-silver" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="blue" /><stop offset="100%" stop-color="silver" />
		</linearGradient>
		<linearGradient id="gradient-blue-khaki" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="blue" /><stop offset="100%" stop-color="khaki" />
		</linearGradient>

		<linearGradient id="gradient-red-black" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="red" /><stop offset="100%" stop-color="black" />
		</linearGradient>
		<linearGradient id="gradient-red-blue" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="red" /><stop offset="100%" stop-color="blue" />
		</linearGradient>
		<linearGradient id="gradient-red-limegreen" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="red" /><stop offset="100%" stop-color="limegreen" />
		</linearGradient>
		<linearGradient id="gradient-red-chocolate" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="red" /><stop offset="100%" stop-color="chocolate" />
		</linearGradient>
		<linearGradient id="gradient-red-orange" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="red" /><stop offset="100%" stop-color="orange" />
		</linearGradient>
		<linearGradient id="gradient-red-hotpink" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="red" /><stop offset="100%" stop-color="hotpink" />
		</linearGradient>
		<linearGradient id="gradient-red-silver" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="red" /><stop offset="100%" stop-color="silver" />
		</linearGradient>
		<linearGradient id="gradient-red-khaki" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="red" /><stop offset="100%" stop-color="khaki" />
		</linearGradient>

		<linearGradient id="gradient-limegreen-black" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="limegreen" /><stop offset="100%" stop-color="black" />
		</linearGradient>
		<linearGradient id="gradient-limegreen-blue" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="limegreen" /><stop offset="100%" stop-color="blue" />
		</linearGradient>
		<linearGradient id="gradient-limegreen-red" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="limegreen" /><stop offset="100%" stop-color="red" />
		</linearGradient>
		<linearGradient id="gradient-limegreen-chocolate" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="limegreen" /><stop offset="100%" stop-color="chocolate" />
		</linearGradient>
		<linearGradient id="gradient-limegreen-orange" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="limegreen" /><stop offset="100%" stop-color="orange" />
		</linearGradient>
		<linearGradient id="gradient-limegreen-hotpink" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="limegreen" /><stop offset="100%" stop-color="hotpink" />
		</linearGradient>
		<linearGradient id="gradient-limegreen-silver" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="limegreen" /><stop offset="100%" stop-color="silver" />
		</linearGradient>
		<linearGradient id="gradient-limegreen-khaki" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="limegreen" /><stop offset="100%" stop-color="khaki" />
		</linearGradient>

		<linearGradient id="gradient-chocolate-black" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="chocolate" /><stop offset="100%" stop-color="black" />
		</linearGradient>
		<linearGradient id="gradient-chocolate-blue" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="chocolate" /><stop offset="100%" stop-color="blue" />
		</linearGradient>
		<linearGradient id="gradient-chocolate-red" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="chocolate" /><stop offset="100%" stop-color="red" />
		</linearGradient>
		<linearGradient id="gradient-chocolate-limegreen" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="chocolate" /><stop offset="100%" stop-color="limegreen" />
		</linearGradient>
		<linearGradient id="gradient-chocolate-orange" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="chocolate" /><stop offset="100%" stop-color="orange" />
		</linearGradient>
		<linearGradient id="gradient-chocolate-hotpink" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="chocolate" /><stop offset="100%" stop-color="hotpink" />
		</linearGradient>
		<linearGradient id="gradient-chocolate-silver" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="chocolate" /><stop offset="100%" stop-color="silver" />
		</linearGradient>
		<linearGradient id="gradient-chocolate-khaki" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="chocolate" /><stop offset="100%" stop-color="khaki" />
		</linearGradient>

		<linearGradient id="gradient-orange-black" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="orange" /><stop offset="100%" stop-color="black" />
		</linearGradient>
		<linearGradient id="gradient-orange-blue" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="orange" /><stop offset="100%" stop-color="blue" />
		</linearGradient>
		<linearGradient id="gradient-orange-red" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="orange" /><stop offset="100%" stop-color="red" />
		</linearGradient>
		<linearGradient id="gradient-orange-limegreen" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="orange" /><stop offset="100%" stop-color="limegreen" />
		</linearGradient>
		<linearGradient id="gradient-orange-chocolate" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="orange" /><stop offset="100%" stop-color="chocolate" />
		</linearGradient>
		<linearGradient id="gradient-orange-hotpink" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="orange" /><stop offset="100%" stop-color="hotpink" />
		</linearGradient>
		<linearGradient id="gradient-orange-silver" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="orange" /><stop offset="100%" stop-color="silver" />
		</linearGradient>
		<linearGradient id="gradient-orange-khaki" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="orange" /><stop offset="100%" stop-color="khaki" />
		</linearGradient>

		<linearGradient id="gradient-silver-black" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="silver" /><stop offset="100%" stop-color="black" />
		</linearGradient>
		<linearGradient id="gradient-silver-blue" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="silver" /><stop offset="100%" stop-color="blue" />
		</linearGradient>
		<linearGradient id="gradient-silver-red" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="silver" /><stop offset="100%" stop-color="red" />
		</linearGradient>
		<linearGradient id="gradient-silver-limegreen" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="silver" /><stop offset="100%" stop-color="limegreen" />
		</linearGradient>
		<linearGradient id="gradient-silver-chocolate" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="silver" /><stop offset="100%" stop-color="chocolate" />
		</linearGradient>
		<linearGradient id="gradient-silver-orange" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="silver" /><stop offset="100%" stop-color="orange" />
		</linearGradient>
		<linearGradient id="gradient-silver-khaki" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="silver" /><stop offset="100%" stop-color="khaki" />
		</linearGradient>

		<linearGradient id="gradient-khaki-black" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="khaki" /><stop offset="100%" stop-color="black" />
		</linearGradient>
		<linearGradient id="gradient-khaki-blue" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="khaki" /><stop offset="100%" stop-color="blue" />
		</linearGradient>
		<linearGradient id="gradient-khaki-red" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="khaki" /><stop offset="100%" stop-color="red" />
		</linearGradient>
		<linearGradient id="gradient-khaki-limegreen" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="khaki" /><stop offset="100%" stop-color="limegreen" />
		</linearGradient>
		<linearGradient id="gradient-khaki-chocolate" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="khaki" /><stop offset="100%" stop-color="chocolate" />
		</linearGradient>
		<linearGradient id="gradient-khaki-orange" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="khaki" /><stop offset="100%" stop-color="orange" />
		</linearGradient>
		<linearGradient id="gradient-khaki-silver" x1="50%" y1="0%" x2="50%" y2="50%">
			<stop offset="0" stop-color="khaki" /><stop offset="100%" stop-color="silver" />
		</linearGradient>
	</defs>
</svg>