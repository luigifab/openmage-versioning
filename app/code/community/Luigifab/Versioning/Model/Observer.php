<?php
/**
 * Created J/31/05/2012
 * Updated D/28/10/2012
 * Version 4
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

	// vérification des dossiers
	private function checkAllDir() {

		$dir = Mage::getBaseDir().'/errors/versioning/locale';

		if (!is_writeable($dir))
			@chmod($dir, 0755);
		if (!is_dir($dir) || !is_writeable($dir))
			Throw new Exception(Mage::helper('versioning')->__('Directory <em>errors/versioning/locale</em> is not writable.'));

		$dir = Mage::getBaseDir().'/errors/versioning/config';

		if (!is_dir($dir))
			@mkdir($dir, 0755);
		if (!is_dir($dir) || !is_writeable($dir))
			Throw new Exception(Mage::helper('versioning')->__('Directory <em>errors/versioning/config</em> does not exist or is not writable.'));
	}


	// #### Mise à jour de la configuration ######################################### public ### //
	// = révision : 5
	// » Met à jour les fichiers de traduction pour chaque langue
	// » Met aussi à jour les adresses IP à exclure
	public function updateConfig() {

		$this->checkAllDir();

		// traductions (vue magasin par vue magasin)
		$storeids = array();

		if (strlen($store = Mage::app()->getRequest()->getParam('store')) > 0) {
			$storeid = Mage::getModel('core/store')->load($store)->getStoreId();
			$this->updateStoreTranslation(Mage::getStoreConfig('general/locale/code', $storeid), $storeid);
		}
		else {
			foreach (Mage::app()->getStores() as $store) {
				if ($store->getIsActive() === '1')
					$storeids[Mage::getStoreConfig('general/locale/code', $store->getStoreId())] = $store->getStoreId();
			}
			foreach ($storeids as $lang => $storeid) {
				$this->updateStoreTranslation($lang, $storeid);
			}
		}

		// adresses IP page upgrade
		$target = './errors/versioning/config/upgrade.ip';
		$ips = trim(Mage::getStoreConfig('versioning/downtime/upgrade_byip'));

		if (strlen($ips) > 0)
			file_put_contents($target, '-'.str_replace(' ', "-\n-", $ips).'-');
		else if (is_file($target))
			unlink($target);

		// adresses IP page 503
		$target = './errors/versioning/config/503.ip';
		$ips = trim(Mage::getStoreConfig('versioning/downtime/error503_byip'));

		if (strlen($ips) > 0)
			file_put_contents($target, '-'.str_replace(' ', "-\n-", $ips).'-');
		else if (is_file($target))
			unlink($target);
	}


	// #### Génération des fichiers CSV ############################################ private ### //
	// = révision : 10
	// » Met à jour le fichier de traduction à partir des données du backend
	// » Formate le texte de la description au fomat HTML
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
				$text[] =  '`'.$key.'_content`,`<p>'.str_replace("\n", '<br />', $content).'</p>`';
		}

		// sauvegarde des données
		// ou suppression du fichier
		if (count($text) > 0)
			file_put_contents($target, implode("\n", $text));
		else if (is_file($target))
			unlink($target);
	}
}