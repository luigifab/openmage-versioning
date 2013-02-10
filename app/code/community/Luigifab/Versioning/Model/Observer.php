<?php
/**
 * Created J/31/05/2012
 * Updated J/07/02/2013
 * Version 7
 *
 * Copyright 2011-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

	// #### Mise à jour de la configuration ################################# i18n ## public ### //
	// = révision : 9
	// » Met à jour les fichiers de traduction par rapport à la configuration
	// » Met aussi à jour les adresses IP à exclure (en ajoutant un tiret avant et après chaque adresse)
	// » S'assure que les dossiers de destination sont accessibles
	public function updateConfig() {

		// *** Vérification des dossiers ************************ //
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

		// *** Traductions (vue magasin par vue magasin) ******** //
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

		// *** Adresses IP page upgrade ************************* //
		$target = BP.'/errors/versioning/config/upgrade.ip';
		$ips = trim(Mage::getStoreConfig('versioning/downtime/upgrade_byip'));

		if (strlen($ips) > 0)
			file_put_contents($target, '-'.str_replace(' ', "-\n-", $ips).'-');
		else if (is_file($target))
			unlink($target);

		// *** Adresses IP page 503 ***************************** //
		$target = BP.'/errors/versioning/config/503.ip';
		$ips = trim(Mage::getStoreConfig('versioning/downtime/error503_byip'));

		if (strlen($ips) > 0)
			file_put_contents($target, '-'.str_replace(' ', "-\n-", $ips).'-');
		else if (is_file($target))
			unlink($target);
	}


	// #### Génération des fichiers CSV ############################################ private ### //
	// = révision : 12
	// » Met à jour le fichier de traduction à partir des données du backend
	// » Formate le texte de la description au format HTML
	private function updateStoreTranslation($lang, $storeid) {

		$text = array();
		$target = BP.'/errors/versioning/locale/'.$lang.'2.csv';

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