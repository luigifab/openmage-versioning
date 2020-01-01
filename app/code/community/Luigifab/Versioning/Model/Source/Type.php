<?php
/**
 * Created M/27/12/2011
 * Updated V/30/08/2019
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

class Luigifab_Versioning_Model_Source_Type {

	public function toOptionArray() {

		$config  = Mage::getConfig()->getNode('global/models/versioning/adaptators')->asArray();
		$options = [];

		foreach ($config as $code => $key) {
			$system = Mage::getSingleton($key);
			$options[$key] = ['value' => $key, 'label' => $system->isSoftwareInstalled() ?
				Mage::helper('versioning')->__('%s (%s)', mb_strtoupper($system->getType()), $system->getSoftwareVersion()) :
				Mage::helper('versioning')->__('%s (not available)', mb_strtoupper($system->getType()))];
		}

		ksort($options);
		return $options;
	}
}