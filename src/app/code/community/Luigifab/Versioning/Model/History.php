<?php
/**
 * Created V/03/08/2012
 * Updated V/24/06/2022
 *
 * Copyright 2011-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Versioning_Model_History extends Varien_Data_Collection {

	protected $_pageSize = true;
	protected $_isCollectionLoaded = true;

	// collection spécifique (ou pas...) avec un simple array
	// avec un jolie bricolage mais c'est pas très important
	public function init(int $page, int $size) {

		$help = Mage::helper('versioning');
		$file = $help->getHistoryLog();

		if (is_file($file)) {

			// recherche des données
			// construction d'un premier tableau
			$resource = fopen($file, 'rb');

			while (!empty($line = fgetcsv($resource, 50000, ',', '`'))) {

				if (!empty($line[0])) {

					$item = new Varien_Object();
					$item->setData('date', $line[0]);
					$item->setData('from', $line[1]);
					$item->setData('to', $line[2]);
					$item->setData('remote_addr', $line[3]);
					$item->setData('user', $line[4]);
					$item->setData('duration', $line[5]);

					// modifié en version 1.1.0
					// la 7ème case contient désormais le statut suivi d'un saut de ligne suivi des détails de la mise à jour
					if (mb_stripos($line[6], "\n") !== false) {
						$pos = mb_stripos($line[6], "\n");
						$item->setData('status', mb_substr($line[6], 0, $pos));
						$item->setData('details', mb_substr($line[6], $pos + 1));
					}
					else {
						$item->setData('status', $line[6]);
						$item->setData('details', $line[6]);
					}

					// ajouté en version 1.1.0
					// la 8ème case contient l'éventuel nom de la branche
					$item->setData('branch', empty($line[7]) ? '' : $line[7]);

					// an upgrade is already...
					// remplace le texte par sa version traduite
					if ((mb_stripos($item->getData('details'), 'An upgrade is already underway') === 0) ||
					    (mb_stripos($item->getData('details'), 'An update is in progress') === 0))
						$item->setData('details', $help->__('Stop! Stop! Stop! An update is in progress.'));

					$this->_items[] = $item;
				}
			}

			fclose($resource);

			// première sauvegarde
			$this->_items = array_reverse($this->_items);
			$this->_totalRecords = count($this->_items);

			// collection finale
			// construction de second tableau
			$items = [];
			$from  = ($page - 1) * $size;
			$to    = ($page - 1) * $size + $size;

			foreach ($this->_items as $idx => $item) {
				if (($idx >= $from) && ($idx < $to))
					$items[] = $item;
			}

			// seconde sauvegarde
			$this->_items = $items;
		}

		return $this;
	}
}