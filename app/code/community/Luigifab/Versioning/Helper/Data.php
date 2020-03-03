<?php
/**
 * Created S/03/12/2011
 * Updated J/17/10/2019
 *
 * Copyright 2011-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/magento/versioning
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

	public function getSystem() {

		$system = Mage::registry('versioning');
		if (!is_object($system)) {
			$system = Mage::getStoreConfig('versioning/scm/type');
			$system = empty($system) ?
				new Varien_Object(['type' => 'GIT']) :
				Mage::getSingleton((mb_stripos($system, '/') === false) ? 'versioning/scm_'.$system : $system);
			Mage::register('versioning', $system);
		}

		return $system;
	}

	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Versioning')->version;
	}

	public function _(string $data, ...$values) {
		return (mb_stripos($txt = $this->__(' '.$data, ...$values), ' ') === 0) ? $this->__($data, ...$values) : $txt;
	}

	public function escapeEntities($data, bool $quotes = false) {
		return htmlspecialchars($data, $quotes ? ENT_SUBSTITUTE | ENT_COMPAT : ENT_SUBSTITUTE | ENT_NOQUOTES);
	}

	public function getHumanDuration($data) {

		$minutes = (int) ($data / 60);
		$seconds = $data % 60;

		if ($data > 599)
			$data = ($seconds > 9) ? $minutes.':'.$seconds : $minutes.':0'.$seconds;
		else if ($data > 59)
			$data = ($seconds > 9) ? '0'.$minutes.':'.$seconds : '0'.$minutes.':0'.$seconds;
		else if ($data > 1)
			$data = ($seconds > 9) ? '00:'.$data : '00:0'.$data;
		else
			$data = '⩽&nbsp;1';

		return $data;
	}


	public function getFields() {

		$fields = new ArrayObject();
		$fields->append('<label><input type="checkbox" name="use_flag" value="1" /> '.$this->__('Use update page').'</label>');

		Mage::dispatchEvent('admin_versioning_add_fields', ['fields' => $fields]);

		$html = $this->__('Are you sure you want to launch the update process?<br />Be careful, you can\'t cancel this operation.');
		$html = '<p>'.$html.'</p><ul><li>'.implode('</li><li>', $fields->getArrayCopy()).'</li></ul>';

		return base64_encode(str_replace(['<', '>'], ['[', ']'], $html));
	}

	public function getMaintenanceInfo() {

		$file   = BP.'/errors/config/error503.ip';
		$byip   = (is_file($file) && (mb_stripos(file_get_contents($file), '-'.$this->getIpAddr().'-') !== false));
		$nobody = (!is_file($file) || empty(Mage::getStoreConfig('versioning/downtime/error503_byip')));

		$html   = [];
		$html[] = '<p>'.$this->__('Are you sure you want to enable the maintenance page?').'</p>';
		$html[] = ''; // pour un saut de ligne supplémentaire sans apijs
		$html[] = '<p>'.str_replace('<strong>', '<strong class="ip">', $this->__('Your IP address: <strong>%s</strong>', $this->getIpAddr()));

		if ($nobody)
			$html[] = '<br />'.$this->__('<strong>Nobody</strong> will have access to the frontend.').'</p>';
		else if ($byip)
			$html[] = '<br />'.$this->__('<strong>You will have</strong> access to the frontend.').'</p>';
		else
			$html[] = '<br />'.$this->__('<strong>You will not have</strong> access to the frontend.').'</p>';

		$html = implode("\n", $html);
		return base64_encode(str_replace(['<', '>'], ['[', ']'], $html));
	}

	public function getUpgradeInfo() {

		$file   = BP.'/errors/config/upgrade.ip';
		$byip   = (is_file($file) && (mb_stripos(file_get_contents($file), '-'.$this->getIpAddr().'-') !== false));
		$nobody = (!is_file($file) || empty(Mage::getStoreConfig('versioning/downtime/upgrade_byip')));

		$html   = [];
		$html[] = '<p>'.$this->__('Are you sure you want to enable the update page?').'</p>';
		$html[] = ''; // pour un saut de ligne supplémentaire sans apijs
		$html[] = '<p>'.str_replace('<strong>', '<strong class="ip">', $this->__('Your IP address: <strong>%s</strong>', $this->getIpAddr()));

		if ($nobody)
			$html[] = '<br />'.$this->__('<strong>Nobody</strong> will have access to the frontend.').'</p>';
		else if ($byip)
			$html[] = '<br />'.$this->__('<strong>You will have</strong> access to the frontend.').'</p>';
		else
			$html[] = '<br />'.$this->__('<strong>You will not have</strong> access to the frontend.').'</p>';

		$html = implode("\n", $html);
		return base64_encode(str_replace(['<', '>'], ['[', ']'], $html));
	}

	public function getLock() {
		return Mage::getBaseDir('var').'/versioning.lock';
	}

	public function getHistoryLog() {
		return Mage::getBaseDir('log').'/versioning.csv';
	}

	public function getLastLog() {
		return Mage::getBaseDir('log').'/versioning.log';
	}

	public function getUpgradeFlag() {
		return BP.'/upgrade.flag';
	}

	public function getMaintenanceFlag() {
		return BP.'/maintenance.flag';
	}


	public function getIpAddr() {

		$ip = empty(getenv('HTTP_X_FORWARDED_FOR')) ? false : explode(',', getenv('HTTP_X_FORWARDED_FOR'));
		$ip = empty($ip) ? getenv('REMOTE_ADDR') : array_pop($ip);
		$ip = (preg_match('#^::f{4}:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $ip) === 1) ? mb_substr($ip, 7) : $ip;

		return $ip;
	}

	public function getCssJsHtml() {

		$head = Mage::getBlockSingleton('adminhtml/page_head');
		$head->addItem('skin_css', 'css/luigifab/versioning/styles.min.css');    // évite que _data['items'] soit inexistant
		$head->removeItem('skin_css', 'css/luigifab/versioning/styles.min.css'); // sur le foreach du getCssJsHtml

		return (mb_strlen($data = trim($head->getCssJsHtml())) > 10) ? $data : null;
	}
}