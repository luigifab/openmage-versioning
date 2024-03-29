<?php
/**
 * Created M/27/12/2011
 * Updated S/11/11/2023
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

class Luigifab_Versioning_Model_Source_Type {

	protected $_options;

	public function toOptionArray() {

		if (empty($this->_options)) {

			$this->_options = [];
			$helper = Mage::helper('versioning');
			$keys   = Mage::getConfig()->getNode('global/models/versioning/adaptators')->asArray();

			foreach ($keys as $key) {
				$system = Mage::getSingleton($key);
				$this->_options[$key] = ['value' => $key, 'label' => $system->isSoftwareInstalled() ?
					$helper->__('%s (%s)', strtoupper($system->getType()), $system->getSoftwareVersion()) : // not mb_strtoupper
					$helper->__('%s (not available)', strtoupper($system->getType()))]; // not mb_strtoupper
			}

			ksort($this->_options);
		}

		return $this->_options;
	}
}