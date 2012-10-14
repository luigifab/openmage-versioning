<?php
/**
 * Created J/31/05/2012
 * Updated S/13/10/2012
 * Version 3
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

class Luigifab_Versioning_Model_Observer {

	// event admin_versioning_upgrade_before
	public function beforeUpgradeEvent($observer) {

		//array('repository' => $repository, 'logger' => $logger, 'status' => $status, 'revision' => $targetRevision)
		$event = $observer->getEvent();

		Mage::log('Luigifab_Versioning_Model_Observer::beforeUpgradeEvent, revision: '.$event->getRevision());
	}

	// event admin_versioning_upgrade_after
	public function afterUpgradeEvent($observer) {

		//array('repository' => $repository, 'logger' => $logger, 'status' => $status, 'revision' => $targetRevision, 'exception' => $e)
		$event = $observer->getEvent();

		if (!is_null($event->getException())) {
			Mage::log('Luigifab_Versioning_Model_Observer::afterUpgradeEvent, revision: '.$event->getRevision().', exception: '.$event->getException()->getMessage());
		}
		else {
			Mage::log('Luigifab_Versioning_Model_Observer::afterUpgradeEvent, revision: '.$event->getRevision());
		}
	}


	// met à jour les fichiers de traduction pour chaque langue
	// donc pour chaque vue magasin (pour chaque langue)
	public function updateTranslations() {

		$storeids = array();
		$app = Mage::app();

		if (strlen($store = $app->getRequest()->getParam('store')) > 0) {
			$storeid = Mage::getModel('core/store')->load($store)->getStoreId();
			$this->updateStoreTranslation(Mage::getStoreConfig('general/locale/code', $storeid), $storeid);
		}
		else {
			foreach ($app->getStores() as $store) {
				if ($store->getIsActive() === '1')
					$storeids[Mage::getStoreConfig('general/locale/code', $store->getStoreId())] = $store->getStoreId();
			}
			foreach ($storeids as $lang => $storeid) {
				$this->updateStoreTranslation($lang, $storeid);
			}
		}
	}

	// met à jour le fichier de traduction à partir des données du backend
	// formate le texte de la description au fomat HTML
	private function updateStoreTranslation($lang, $storeid) {

		$text = array();
		$target = './errors/versioning/locale/'.$lang.'2.csv';

		// titre de la page
		$title = Mage::getStoreConfig('design/head/default_title', $storeid);

		if (strlen($title) > 0)
			$text[] = '`Oups!`,`'.$title.'`';

		// traduction des pages
		foreach (array('upgrade', 'report', 'error503', 'error404') as $key) {

			$title = Mage::getStoreConfig('versioning/downtime/'.$key.'_title', $storeid);

			if (strlen($title) > 0)
				$text[] = '`'.$key.'_title`,`'.$title.'`';

			$content = Mage::getStoreConfig('versioning/downtime/'.$key.'_content', $storeid);

			if ((strlen($content) > 0) && (strpos($content, '<') === 0))
				$text[] = '`'.$key.'_content`,`'.$content.'`';
			else if (strlen($content) > 0)
				$text[] =  '`'.$key.'_content`,`<p>'.str_replace("\n",'<br />',$content).'</p>`';
		}

		// sauvegarde des données
		if (count($text) > 0)
			file_put_contents($target, implode("\n", $text));
		else if (is_file($target))
			unlink($target);
	}
}