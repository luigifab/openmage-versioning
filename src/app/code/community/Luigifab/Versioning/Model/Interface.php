<?php
/**
 * Created M/21/01/2020
 * Updated D/31/05/2020
 *
 * Copyright 2011-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/openmage/versioning
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

interface Luigifab_Versioning_Model_Interface {

	public function getType();

	public function isSoftwareInstalled();

	public function getSoftwareVersion();

	public function getCommitsCollection($local = false, $error = null);

	public function getCurrentBranch();

	public function getCurrentRevision();

	public function getCurrentDiff($from = null, $to = null, $dir = null, $excl = null);

	public function getCurrentDiffStatus($from = null, $to = null, $dir = null);

	public function getCurrentStatus($dir = null);

	public function upgradeToRevision($object, $log, $revision);
}