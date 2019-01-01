<?php
/**
 * Created W/30/05/2012
 * Updated M/28/02/2017
 *
 * Copyright 2011-2019 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/magento/versioning
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

error_reporting(E_ALL);
ini_set('display_errors', 1);
define('ROOT', ((is_dir('./errors')) ? realpath('.') : realpath('..')));

if (is_file(ROOT.'/errors/config/processor.php')) {
	require_once(ROOT.'/errors/config/processor.php');
	require_once(ROOT.'/errors/processor.php');
	$processor = new UserProcessor();
}
else {
	require_once(ROOT.'/errors/processor.php');
	$processor = new Processor();
}

$processor->init('error404');
$processor->renderPage(404);