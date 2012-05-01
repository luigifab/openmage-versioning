<?php
/**
 * Created S/03/12/2011
 * Updated V/27/04/2012
 * Version 12
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

class Luigifab_Versioning_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Versioning')->version;
	}

	public function getLock() {
		return Mage::getBaseDir('var').'/versioning.lock';
	}

	public function getLastlogFile() {
		return Mage::getBaseDir('log').'/versioning.log';
	}

	public function getLastlogContent() {

		$file = $this->getLastlogFile();
		$offset = Mage::getModel('core/date')->timestamp(time()) - time();

		if (is_file($file) && is_readable($file)) {
			$timestamp = filemtime($file) + $offset;
			$html  = '<pre id="versioningLog">';
			$html .= '<em>'.$this->__('Log generated on %s at %s', Mage::helper('core')->formatDate(date('Y-m-d', $timestamp), 'long', false), date('H:i:s', $timestamp)).'</em>';
			$html .= "\n\n".file_get_contents($file);
			$html .= '</pre>';
		}
		else {
			$html  = '<pre id="versioningLog">';
			$html .= $this->__('Log is empty');
			$html .= '</pre>';
		}

		return $html;
	}

	public function getHistoryFile() {
		return Mage::getBaseDir('log').'/versioning.csv';
	}

	public function getHistoryCollection() {

		$file = $this->getHistoryFile();

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
					if (strpos($line[6], "\n") !== false) {
						$item->setStatus($this->__(substr($line[6], 0, strpos($line[6], "\n"))));
						$item->setDetails('<a href="#" onclick="alert(\''.str_replace("\n", '\n', strip_tags(substr($line[6], strpos($line[6], "\n") + 1))).'\');">'.Mage::helper('adminhtml')->__('Details').'</a>');
					}
					else {
						$item->setStatus($this->__($line[6]));
						$item->setDetails('');
					}

					$item->setBranch((isset($line[7])) ? $line[7] : ''); // ajouté en version 1.1.0
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