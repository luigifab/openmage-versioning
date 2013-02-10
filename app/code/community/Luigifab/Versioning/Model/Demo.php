<?php
/**
 * Created V/02/11/2012
 * Updated V/02/11/2012
 * Version 2
 *
 * Copyright 2012-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Model_Demo {

	// event admin_versioning_upgrade_before
	public function beforeUpgradeEvent($observer) {

		//array('repository' => $repository, 'revision' => $targetRevision, 'controller' => $this)
		$event = $observer->getEvent();

		$event->getController()->writeTitle('X) Before event');
		$event->getController()->writeCommand('before event');

		Mage::log('Luigifab_Versioning_Model_Demo::beforeUpgradeEvent, revision: '.$event->getRevision());
	}

	// event admin_versioning_upgrade_after
	public function afterUpgradeEvent($observer) {

		//array('repository' => $repository, 'revision' => $targetRevision, 'controller' => $this, 'exception' => $e)
		$event = $observer->getEvent();

		if (!is_null($event->getException())) {
			$event->getController()->writeTitle('X) After event');
			$event->getController()->writeCommand('after event');
			Mage::log('Luigifab_Versioning_Model_Demo::afterUpgradeEvent, revision: '.$event->getRevision().', exception: '.$event->getException()->getMessage());
		}
		else {
			$event->getController()->writeTitle('X) After event');
			$event->getController()->writeCommand('after event');
			Mage::log('Luigifab_Versioning_Model_Demo::afterUpgradeEvent, revision: '.$event->getRevision());
		}
	}
}