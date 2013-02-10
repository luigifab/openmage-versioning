<?php
/**
 * Created S/03/12/2011
 * Updated S/09/02/2013
 * Version 24
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

class Luigifab_Versioning_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Versioning')->version;
	}

	public function getFrontendUrl() {
		return Mage::getUrl('', array('_store' => Mage::app()->getDefaultStoreView()->getStoreId(), '_type' => 'direct_link'));
	}

	public function isCompressorInstalled() {
		return (is_file(Mage::getBaseDir('code').'/community/Luigifab/Compressor/Block/Head.php') &&
		        (Mage::getConfig()->getNode('modules/Luigifab_Compressor') !== false)) ? true : false;
	}

	public function isCompressorEnabled() {
		return ((Mage::getStoreConfig('css/general/enabled') === '1') || (Mage::getStoreConfig('js/general/enabled') === '1')) ? true : false;
	}

	public function getLock() {
		return Mage::getBaseDir('var').'/versioning.lock';
	}

	public function getUpgradeFlag() {
		return Mage::getBaseDir().'/upgrade.flag';
	}

	public function getMaintenanceFlag() {
		return Mage::getBaseDir().'/maintenance.flag';
	}

	public function getStatusContent() {
		$data = Mage::getModel('versioning/scm_'.Mage::getStoreConfig('versioning/scm/type'));
		return '<pre id="versioningLog">'.$data->getCurrentStatus().'</pre>';
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

	public function checkIndexPhp() {
		$content = file_get_contents(BP.'/index.php');
		return ((strpos($content, 'upgrade.flag') !== false) && (strpos($content, '$ipFile') !== false)) ? true : false;
	}

	public function checkLocalXml() {

		if (is_file(BP.'/errors/local.xml')) {
			$content = file_get_contents(BP.'/errors/local.xml');
			return (strpos($content, '<skin>versioning</skin>') !== false) ? true : false;
		}
		else {
			return false;
		}
	}
}