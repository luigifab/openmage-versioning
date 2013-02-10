<?php
/**
 * Created W/30/05/2012
 * Updated M/27/11/2012
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

if (is_file('./readme.txt')) {
	require_once('../processor.php');
	require_once('./processor.php');
}
else {
	require_once('./errors/processor.php');
	require_once('./errors/versioning/processor.php');
}

$processor = new Versioning_Processor();
$processor->process503();