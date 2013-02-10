<?php
/**
 * Created V/03/08/2012
 * Updated V/03/08/2012
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

class Luigifab_Versioning_Model_History {

	public function getCollection() {

		$helper = Mage::helper('versioning');
		$file = $helper->getHistoryFile();

		if (is_file($file) && is_readable($file)) {

			$items = array();
			$ressource = fopen($file, 'r');

			while (($line = fgetcsv($ressource, 50000, ',', '`')) !== false) {

				if (strlen($line[0]) > 1) {
					$item = new Varien_Object();
					$item->setDate($line[0]);
					$item->setCurrentRevision($line[1]);
					$item->setTargetRevision($line[2]);
					$item->setRemoteAddr($line[3]);
					$item->setUser($line[4]);
					$item->setDuration($line[5]);

					// modifié en version 1.1.0
					// la 7ème case contient désormais le statut suivi d'un saut de ligne suivi des détails de la mise à jour
					if (strpos($line[6], "\n") !== false) {

						$text = substr($line[6], strpos($line[6], "\n") + 1);
						$text = strip_tags($text);
						$text = addslashes($text);
						$text = str_replace(array("\r","\n"), '\n', $text);

						$item->setStatus($helper->__(substr($line[6], 0, strpos($line[6], "\n"))));
						$item->setDetails('<a href="#" onclick="alert(\''.$text.'\'); return false;">'.Mage::helper('adminhtml')->__('Details').'</a>');
					}
					else {
						$item->setStatus($helper->__($line[6]));
						$item->setDetails('');
					}

					// ajouté en version 1.1.0
					// la 8ème case contient l'éventuel nom de la branche actuelle
					$item->setBranch((isset($line[7])) ? $line[7] : '');

					$items[] = $item;
				}
			}

			fclose($ressource);

			$items = array_reverse($items);
			$collection = new Varien_Data_Collection();

			foreach ($items as $item)
				$collection->addItem($item);
		}
		else {
			$collection = new Varien_Data_Collection();
		}

		return $collection;
	}
}