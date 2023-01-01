<?php
/**
 * Created S/03/12/2011
 * Updated V/09/12/2022
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

class Luigifab_Versioning_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getVersion() {
		return (string) Mage::getConfig()->getModuleConfig('Luigifab_Versioning')->version;
	}

	public function _(string $data, ...$values) {
		$text = $this->__(' '.$data, ...$values);
		return ($text[0] == ' ') ? $this->__($data, ...$values) : $text;
	}

	public function escapeEntities($data, bool $quotes = false) {
		return empty($data) ? $data : htmlspecialchars($data, $quotes ? ENT_SUBSTITUTE | ENT_COMPAT : ENT_SUBSTITUTE | ENT_NOQUOTES);
	}

	public function formatDate($date = null, $format = Zend_Date::DATETIME_LONG, $showTime = false) {
		$object = Mage::getSingleton('core/locale');
		return str_replace($object->date($date)->toString(Zend_Date::TIMEZONE), '', $object->date($date)->toString($format));
	}

	public function getHumanEmailAddress($email) {
		return empty($email) ? '' : $this->escapeEntities(str_replace(['<', '>', ',', '"'], ['(', ')', ', ', ''], $email));
	}

	public function getHumanDuration($start, $end = null) {

		if (is_numeric($start) || (!in_array($start, ['', '0000-00-00 00:00:00', null]) && !in_array($end, ['', '0000-00-00 00:00:00', null]))) {

			$data    = is_numeric($start) ? $start : strtotime($end) - strtotime($start);
			$minutes = (int) ($data / 60);
			$seconds = $data % 60;

			if ($data > 599)
				$data = '<strong>'.(($seconds > 9) ? $minutes.':'.$seconds : $minutes.':0'.$seconds).'</strong>';
			else if ($data > 59)
				$data = '<strong>'.(($seconds > 9) ? '0'.$minutes.':'.$seconds : '0'.$minutes.':0'.$seconds).'</strong>';
			else if ($data > 1)
				$data = ($seconds > 9) ? '00:'.$data : '00:0'.$data;
			else
				$data = '⩽&nbsp;1';
		}

		return empty($data) ? '' : $data;
	}

	public function getNumber($value, array $options = []) {
		$options['locale'] = Mage::getSingleton('core/translate')->getLocale();
		return Zend_Locale_Format::toNumber($value, $options);
	}

	public function getNumberToHumanSize(int $number) {

		if ($number < 1) {
			$data = '';
		}
		else if (($number / 1024) < 1024) {
			$data = $number / 1024;
			$data = $this->getNumber($data, ['precision' => 2]);
			$data = $this->__('%s kB', preg_replace('#[.,]00[[:>:]]#', '', $data));
		}
		else if (($number / 1024 / 1024) < 1024) {
			$data = $number / 1024 / 1024;
			$data = $this->getNumber($data, ['precision' => 2]);
			$data = $this->__('%s MB', preg_replace('#[.,]00[[:>:]]#', '', $data));
		}
		else {
			$data = $number / 1024 / 1024 / 1024;
			$data = $this->getNumber($data, ['precision' => 2]);
			$data = $this->__('%s GB', preg_replace('#[.,]00[[:>:]]#', '', $data));
		}

		return $data;
	}

	public function getUsername() {

		$file = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$file = array_pop($file);
		$file = array_key_exists('file', $file) ? basename($file['file']) : '';

		// backend
		if ((PHP_SAPI != 'cli') && Mage::app()->getStore()->isAdmin() && Mage::getSingleton('admin/session')->isLoggedIn())
			$user = sprintf('admin %s', Mage::getSingleton('admin/session')->getData('user')->getData('username'));
		// cron
		else if (is_object($cron = Mage::registry('current_cron')))
			$user = sprintf('cron %d - %s', $cron->getId(), $cron->getData('job_code'));
		// xyz.php
		else if ($file != 'index.php')
			$user = $file;
		// full action name
		else if (is_object($action = Mage::app()->getFrontController()->getAction()))
			$user = $action->getFullActionName();
		// frontend
		else
			$user = sprintf('frontend %s', Mage::app()->getStore()->getData('code'));

		return $user;
	}


	public function getSystem() {

		$system = Mage::registry('versioning');
		if (!is_object($system)) {
			$current = Mage::getStoreConfig('versioning/scm/type');
			$config  = Mage::getConfig()->getNode('global/models/versioning/adaptators')->asArray();
			if (in_array($current, $config))
				$system = Mage::getSingleton($current);
			else if (!empty($config[$current]))
				$system = Mage::getSingleton($config[$current]);
		}

		return empty($system) ? new Varien_Object(['type' => 'GIT']) : $system;
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
		$ip = empty($ip) ? getenv('REMOTE_ADDR') : reset($ip);
		$ip = (preg_match('#^::f{4}:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $ip) === 1) ? substr($ip, 7) : $ip;

		return $ip;
	}

	public function getCssJsHtml() {

		$head = Mage::getBlockSingleton('adminhtml/page_head');
		$head->addItem('skin_css', 'css/luigifab/versioning/styles.min.css');    // évite que _data['items'] soit inexistant
		$head->removeItem('skin_css', 'css/luigifab/versioning/styles.min.css'); // sur le foreach du getCssJsHtml

		return (mb_strlen($data = trim($head->getCssJsHtml())) > 10) ? $data : null;
	}
}