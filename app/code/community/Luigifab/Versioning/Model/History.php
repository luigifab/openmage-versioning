<?php
/**
 * Created V/03/08/2012
 * Updated V/03/04/2015
 * Version 7
 *
 * Copyright 2011-2015 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Model_History extends Varien_Data_Collection {

	protected $_pageSize = true;
	protected $_isCollectionLoaded = true;

	// collection spécifique (ou pas...) avec un simple array()
	// avec un jolie bricolage mais c'est pas très important
	public function init($page, $size) {

		$file = Mage::helper('versioning')->getHistoryLog();

		if (is_file($file) && is_readable($file)) {

			// recherche des données
			// construction d'un premier tableau
			$ressource = fopen($file, 'r');

			while (($line = fgetcsv($ressource, 50000, ',', '`')) !== false) {

				if (strlen($line[0]) > 1) {

					$item = new Varien_Object();
					$item->setDate($line[0]);
					$item->setFrom($line[1]);
					$item->setTo($line[2]);
					$item->setRemoteAddr($line[3]);
					$item->setUser($line[4]);
					$item->setDuration($line[5]);

					// modifié en version 1.1.0
					// la 7ème case contient désormais le statut suivi d'un saut de ligne suivi des détails de la mise à jour
					if (strpos($line[6], "\n") !== false) {
						$pos = strpos($line[6], "\n");
						$item->setStatus(substr($line[6], 0, $pos));
						$item->setDetails(substr($line[6], $pos + 1));
					}
					else {
						$item->setStatus($line[6]);
						$item->setDetails($line[6]);
					}

					// ajouté en version 1.1.0
					// la 8ème case contient l'éventuel nom de la branche
					$item->setBranch((isset($line[7])) ? $line[7] : '');

					// an upgrade is already...
					// remplace le texte par sa version traduite
					if (strpos($item->getDetails(), 'An upgrade is already underway') !== false)
						$item->setDetails(Mage::helper('versioning')->__('Stop! Stop! Stop! An upgrade is already underway.'));

					array_push($this->_items, $item);
				}
			}

			fclose($ressource);

			// première sauvegarde
			$this->_items = array_reverse($this->_items);
			$this->_totalRecords = count($this->_items);

			// collection finale
			// construction de second tableau
			$items = array();

			$current = 0;
			$from = ($page - 1) * $size;
			$to = ($page - 1) * $size + $size;

			foreach ($this->_items as $item) {
				if (($current >= $from) && ($current < $to))
					$items[] = $item;
				$current++;
			}

			// seconde sauvegarde
			$this->_items = $items;
		}

		return $this;
	}
}