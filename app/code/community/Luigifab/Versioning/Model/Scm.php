<?php
/**
 * Created M/07/01/2020
 * Updated M/21/01/2020
 *
 * Copyright 2011-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

abstract class Luigifab_Versioning_Model_Scm implements Luigifab_Versioning_Model_Interface {

	protected $version;
	protected $revision;
	protected $items;

	public function getType() {
		return mb_strtolower(mb_substr(get_class($this), mb_strripos(get_class($this), '_') + 1));
	}

	public function isSoftwareInstalled() {
		exec($this->getType().' --version', $data);
		return (preg_match('#(\d+\.\d+\.\d+)#', trim(implode($data)), $this->version) !== 0);
	}

	public function getSoftwareVersion() {
		if (empty($this->version))
			$this->isSoftwareInstalled();
		return empty($this->version) ? null : trim($this->version[0]);
	}
}