<?php
/**
 * Created V/03/08/2012
 * Updated D/20/05/2018
 *
 * Copyright 2011-2018 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Model_History extends Varien_Data_Collection {

	protected $_pageSize = true;
	protected $_isCollectionLoaded = true;

	// collection spécifique (ou pas...) avec un simple array()
	// avec un jolie bricolage mais c'est pas très important
	public function init($page, $size) {

		$help = Mage::helper('versioning');
		$file = $help->getHistoryLog();

		if (is_file($file)) {

			// recherche des données
			// construction d'un premier tableau
			$ressource = fopen($file, 'r');

			while (($line = fgetcsv($ressource, 50000, ',', '`')) !== false) {

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
					if (strpos($line[6], "\n") !== false) {
						$pos = strpos($line[6], "\n");
						$item->setData('status', substr($line[6], 0, $pos));
						$item->setData('details', substr($line[6], $pos + 1));
					}
					else {
						$item->setData('status', $line[6]);
						$item->setData('details', $line[6]);
					}

					// ajouté en version 1.1.0
					// la 8ème case contient l'éventuel nom de la branche
					$item->setData('branch', (!empty($line[7])) ? $line[7] : '');

					// an upgrade is already...
					// remplace le texte par sa version traduite
					if ((strpos($item->getData('details'), 'An upgrade is already underway') === 0) ||
					    (strpos($item->getData('details'), 'An update is in progress') === 0))
						$item->setData('details', $help->__('Stop! Stop! Stop! An update is in progress.'));

					array_push($this->_items, $item);
				}
			}

			fclose($ressource);

			// première sauvegarde
			$this->_items = array_reverse($this->_items);
			$this->_totalRecords = count($this->_items);

			// collection finale
			// construction de second tableau
			$items   = array();
			$current = 0;
			$from    = ($page - 1) * $size;
			$to      = ($page - 1) * $size + $size;

			foreach ($this->_items as $item) {
				if (($current >= $from) && ($current < $to))
					array_push($items,  $item);
				$current++;
			}

			// seconde sauvegarde
			$this->_items = $items;
		}

		return $this;
	}
}