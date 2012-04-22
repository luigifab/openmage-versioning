<?php
/**
 * Created M/27/12/2011
 * Updated V/13/04/2012
 * Version 9
 *
 * Copyright 2011-2012 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Model_Source_Type {

	public function toOptionArray() {

		$list = array();
		$allrepo = $this->searchFiles(Mage::getBaseDir('app').'/code/community/Luigifab/Versioning/Model/Scm');

		foreach ($allrepo as $model) {

			$repo = Mage::getModel($model);

			$install = $repo->isSoftwareInstalled();
			$version = $repo->getSoftwareVersion();
			$type = strtoupper($repo->getRepositoryType());

			$list[strtolower($type)] = array('value' => strtolower($type), 'label' => ($install) ? Mage::helper('versioning')->__('%s (%s)', $type, $version) : Mage::helper('versioning')->__('%s (not available)', $type));
		}

		ksort($list);
		return $list;
	}

	private function searchFiles($source) {

		$files = array();
		$ressource = opendir($source);

		while (($file = readdir($ressource)) !== false) {

			if ((strpos($file, '.') !== 0) && is_file($source.'/'.$file))
				$files[] = 'versioning/scm_'.strtolower(substr($file, 0, -4));
		}

		closedir($ressource);
		return $files;
	}
}