<?php
/**
 * Created M/27/12/2011
 * Updated M/08/11/2016
 *
 * Copyright 2011-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Model_Source_Type extends Luigifab_Versioning_Helper_Data {

	public function toOptionArray() {

		$models = $this->searchFiles(BP.'/app/code/community/Luigifab/Versioning/Model/Scm');
		$options = array();

		foreach ($models as $model) {

			$model = Mage::getSingleton($model);

			$label = ($model->isSoftwareInstalled()) ?
				$this->__('%s (%s)', strtoupper($model->getType()), $model->getSoftwareVersion()) :
				$this->__('%s (not available)', strtoupper($model->getType()));

			$options[strtolower($model->getType())] = array('value' => strtolower($model->getType()), 'label' => $label);
		}

		ksort($options);
		return $options;
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