<?php
/**
 * Created S/03/12/2011
 * Updated D/04/03/2018
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

class Luigifab_Versioning_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Versioning')->version;
	}

	public function _($data, $a = null, $b = null) {
		return (strpos($txt = $this->__(' '.$data, $a, $b), ' ') === 0) ? $this->__($data, $a, $b) : $txt;
	}

	public function getHumanDuration($row) {

		$data = $row->getData('duration');
		$minutes = intval($data / 60);
		$seconds = intval($data % 60);

		if ($data > 599)
			$data = ($seconds > 9) ? $minutes.':'.$seconds : $minutes.':0'.$seconds;
		else if ($data > 59)
			$data = ($seconds > 9) ? '0'.$minutes.':'.$seconds : '0'.$minutes.':0'.$seconds;
		else if ($data > 1)
			$data = ($seconds > 9) ? '00:'.$data : '00:0'.$data;
		else
			$data = '⩽ 1';

		return $data;
	}


	public function getFields() {

		$fields = new ArrayObject();
		$fields->append('<label><input type="checkbox" name="use_flag" value="1" /> '.$this->__('Use update page').'</label>');

		Mage::dispatchEvent('admin_versioning_add_fields', array('fields' => $fields));

		$html = $this->__('Are you sure you want to launch the update process?<br />Be careful, you can\'t cancel this operation.');
		$html = '<p>'.$html.'</p><ul><li>'.implode('</li><li>', $fields->getArrayCopy()).'</li></ul>';

		return base64_encode(str_replace(array('<','>'), array('[',']'), $html));
	}

	public function getMaintenanceInfo() {

		$file   = BP.'/errors/config/error503.ip';
		$byip   = (is_file($file) && (strpos(file_get_contents($file), '-'.$this->getIpAddr().'-') !== false));
		$nobody = (!is_file($file) || empty(Mage::getStoreConfig('versioning/downtime/error503_byip')));

		$html   = array();
		$html[] = '<p>'.$this->__('Are you sure you want to enable the maintenance page?').'</p>';
		$html[] = ''; // pour un saut de ligne supplémentaire sans apijs
		$html[] = '<p>'.$this->__('Your IP address: <strong>%s</strong>', $this->getIpAddr());

		if ($nobody)
			$html[] = '<br />'.$this->__('<strong>Nobody</strong> will have access to the frontend.').'</p>';
		else if ($byip)
			$html[] = '<br />'.$this->__('<strong>You will have</strong> access to the frontend.').'</p>';
		else
			$html[] = '<br />'.$this->__('<strong>You will not have</strong> access to the frontend.').'</p>';

		$html = implode("\n", $html);
		return base64_encode(str_replace(array('<','>'), array('[',']'), $html));
	}

	public function getUpgradeInfo() {

		$file   = BP.'/errors/config/upgrade.ip';
		$byip   = (is_file($file) && (strpos(file_get_contents($file), '-'.$this->getIpAddr().'-') !== false));
		$nobody = (!is_file($file) || empty(Mage::getStoreConfig('versioning/downtime/upgrade_byip')));

		$html   = array();
		$html[] = '<p>'.$this->__('Are you sure you want to enable the update page?').'</p>';
		$html[] = ''; // pour un saut de ligne supplémentaire sans apijs
		$html[] = '<p>'.$this->__('Your IP address: <strong>%s</strong>', $this->getIpAddr());

		if ($nobody)
			$html[] = '<br />'.$this->__('<strong>Nobody</strong> will have access to the frontend.').'</p>';
		else if ($byip)
			$html[] = '<br />'.$this->__('<strong>You will have</strong> access to the frontend.').'</p>';
		else
			$html[] = '<br />'.$this->__('<strong>You will not have</strong> access to the frontend.').'</p>';

		$html = implode("\n", $html);
		return base64_encode(str_replace(array('<','>'), array('[',']'), $html));
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
		return Mage::getBaseDir().'/upgrade.flag';
	}

	public function getMaintenanceFlag() {
		return Mage::getBaseDir().'/maintenance.flag';
	}


	public function getIpAddr() {

		$ip = (!empty(getenv('HTTP_X_FORWARDED_FOR'))) ? explode(',', getenv('HTTP_X_FORWARDED_FOR')) : false;
		$ip = (!empty($ip)) ? array_pop($ip) : getenv('REMOTE_ADDR');
		$ip = (preg_match('#^::ffff:[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$#', $ip) === 1) ? substr($ip, 7) : $ip;

		return $ip;
	}

	public function getCssJsHtml() {

		$head = Mage::getBlockSingleton('adminhtml/page_head');
		$head->addItem('skin_css', 'css/luigifab/versioning/styles.min.css');    // évite que _data['items'] soit inexistant
		$head->removeItem('skin_css', 'css/luigifab/versioning/styles.min.css'); // sur le foreach du getCssJsHtml

		return (strlen($data = trim($head->getCssJsHtml())) > 10) ? $data : null;
	}
}