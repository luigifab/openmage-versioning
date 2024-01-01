<?php
/**
 * Created M/07/01/2020
 * Updated S/23/12/2023
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

abstract class Luigifab_Versioning_Model_Scm implements Luigifab_Versioning_Model_Interface {

	protected $_version;
	protected $_branch;
	protected $_revision;
	protected $_items;

	public function getType() {
		return $this->_code;
	}

	public function isSoftwareInstalled() {

		exec(escapeshellcmd($this->_code).' --version', $data, $return);

		if ($return == 0) {
			$data = preg_replace('#[^\d.]#', '', trim(implode($data)));
			$this->_version = implode('.', array_slice(preg_split('#\D#', $data), 0, 3));
		}

		return !empty($this->_version);
	}

	public function getSoftwareVersion() {

		if (empty($this->_version))
			$this->isSoftwareInstalled();

		return $this->_version;
	}

	public function getRootDir() {

		$dir = realpath(BP);
		if (!is_dir($dir.'/.'.$this->_code))
			$dir = realpath(BP.'/..');

		return $dir;
	}

	protected function markExcludedFile(string $line, array $excl, bool $check = false) {

		// min
		if ((mb_stripos($line, '.min.') !== false) && in_array('min', $excl)) {
			if ($check)
				return true;
			$line = str_replace('.min.', '.§{#{§min§}#}§.', $line);
		}

		// extension
		$ign = mb_strrpos($line, '.');
		$ign = mb_substr($line, ($ign > 0) ? $ign + 1 : mb_strrpos($line, '/') + 1);
		if (in_array($ign, $excl)) {
			if ($check)
				return true;
			$line = str_replace('.'.$ign, '.§{#{§'.$ign.'§}#}§', $line);
		}

		// file name
		$ign = basename($line);
		if (in_array($ign, $excl)) {
			$line = str_replace('/'.$ign, '/§{#{§'.$ign.'§}#}§', $line);
			if ($check)
				return true;
		}

		// directory name
		$ign = explode('/', $line);
		foreach ($ign as $itm) {
			$ign = in_array($itm, $excl);
			if ($ign) {
				if ($check)
					return true;
				$line = str_replace('/'.$itm.'/', '/§{#{§'.$itm.'§}#}§/', $line);
			}
		}

		return $check ? false : $line;
	}
}