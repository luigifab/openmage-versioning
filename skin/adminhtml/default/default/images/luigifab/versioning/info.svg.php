<?php
/**
 * Created M/25/01/2011
 * Updated D/24/03/2013
 * Version 13
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

?><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="100" height="70" xmlns="http://www.w3.org/2000/svg" version="1.1">
	<svg x="5" y="5">
		<rect width="90" height="60" fill="#111" rx="7" ry="7" />
		<g fill="#222">
			<circle cx="29" cy="45" r="6" />
			<polyline points="22,10 36,10 34,35 24,35" />
			<animate attributeName="fill" dur="0.001s" begin="0.7s;b.end+0.7s" values="#222;#222" fill="freeze" id="a" />
			<animate attributeName="fill" dur="0.001s" begin="a.end+0.7s" values="#222;#222" fill="freeze" id="b" />
		</g>
		<g fill="#222">
			<polyline points="60,10 70,30 60,50 50,30" />
			<animate attributeName="fill" dur="0.001s" begin="0.7s;d.end+0.7s" values="#222;orange" fill="freeze" id="c" />
			<animate attributeName="fill" dur="0.001s" begin="c.end+0.7s" values="orange;#222" fill="freeze" id="d" />
		</g>
	</svg>
</svg>