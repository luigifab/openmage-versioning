<?php
/**
 * Created V/02/11/2012
 * Updated V/04/08/2017
 *
 * Copyright 2011-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * https://www.luigifab.info/magento/versioning
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

class Luigifab_Versioning_Model_Demo {

	// example for EVENT admin_versioning_add_fields
	// - $observer => ['fields' => $fields]
	// - fields = (native php object) ArrayObject
	public function addFieldsEvent($observer) {

		$observer->getData('fields')->append('<label><input type="checkbox" name="test" value="1" /> Simple test</label>');
	}

	// example for EVENT admin_versioning_upgrade_before
	// - $observer => ['repository' => $repository, 'revision' => $targetRevision, 'controller' => $this]
	// - repository = (object) Luigifab_Versioning_Model_Scm_Xxx
	// - revision   = (string) xyzxyzxyz
	// - controller = (object) Luigifab_Versioning_Model_Upgrade
	public function beforeUpgradeEvent($observer) {

		$observer->getData('controller')->writeCommand('before event example');
		Mage::log('Luigifab_Versioning_Model_Demo::beforeUpgradeEvent, revision: '.$observer->getData('revision'));

		// Mage::app()->getRequest()->getParam('revision', '') => string (xyzxyzxyz) = $observer->getData('revision')
		// Mage::app()->getRequest()->getParam('use_flag', '') => string = '1' or ''
		// Mage::app()->getRequest()->getParam('test', '')     => string = '1' or '' (addFieldsEvent())
	}

	// example for EVENT admin_versioning_upgrade_after
	// - $observer => ['repository' => $repository, 'revision' => $targetRevision, 'controller' => $this, 'exception' => $exception]
	// - repository = (object) Luigifab_Versioning_Model_Scm_Xxx
	// - revision   = (string) xyzxyzxyz
	// - controller = (object) Luigifab_Versioning_Model_Upgrade
	// - exception  = (native php object) Exception
	public function afterUpgradeEvent($observer) {

		if (!empty($observer->getData('exception'))) {
			$observer->getData('controller')->writeCommand('after event example');
			Mage::log('Luigifab_Versioning_Model_Demo::afterUpgradeEvent, revision: '.$observer->getData('revision').
				', exception: '.$observer->getData('exception')->getMessage());
		}
		else {
			$observer->getData('controller')->writeCommand('after event example');
			Mage::log('Luigifab_Versioning_Model_Demo::afterUpgradeEvent, revision: '.$observer->getData('revision'));
		}

		// Mage::app()->getRequest()->getParam('revision', '') => string = xyzxyzxyz = $observer->getData('revision')
		// Mage::app()->getRequest()->getParam('use_flag', '') => string = '1' or ''
		// Mage::app()->getRequest()->getParam('test', '')     => string = '1' or '' (addFieldsEvent())
	}
}