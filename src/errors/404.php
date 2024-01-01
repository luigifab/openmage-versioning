<?php
/**
 * Created W/30/05/2012
 * Updated D/12/11/2023
 *
 * Copyright 2011-2024 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://github.com/luigifab/openmage-versioning
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

chdir(defined('BP') ? BP.'/errors' : __DIR__);
if (!empty($_SERVER['MAGE_IS_DEVELOPER_MODE']) || !empty($_ENV['MAGE_IS_DEVELOPER_MODE'])) {
	error_reporting(E_ALL);
	ini_set('display_errors', (PHP_VERSION_ID < 80100) ? '1' : 1);
	ini_set('error_prepend_string', '<pre>');
	ini_set('error_append_string', '</pre>');
}

if (is_file('config/processor.php')) {
	require_once('config/processor.php');
	require_once('processor.php');
	$processor = new UserProcessor();
}
else {
	require_once('processor.php');
	$processor = new Processor();
}

$processor->init('error404');
$processor->renderPage(404);