<?php
/**
 * Created V/23/05/2014
 * Updated S/01/02/2020
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

class Luigifab_Versioning_Block_Adminhtml_Config_Help extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		$msg = $this->checkChanges();
		if ($msg !== true)
			return sprintf('<p class="box">%s %s <span class="right"><a href="https://www.%s">%3$s</a> | ⚠ IPv6</span></p>'.
				'<p class="box" style="margin-top:-5px; color:white; background-color:#E60000;"><strong>%s</strong><br />%s</p>',
				'Luigifab/Versioning', $this->helper('versioning')->getVersion(), 'luigifab.fr/magento/versioning',
				$this->__('INCOMPLETE MODULE INSTALLATION'),
				$this->__('Changes in <em>%s</em> are not present. Please read the documentation.', $msg));

		return sprintf('<p class="box">%s %s <span class="right"><a href="https://www.%s">%3$s</a> | ⚠ IPv6</span></p>',
			'Luigifab/Versioning', $this->helper('versioning')->getVersion(), 'luigifab.fr/magento/versioning');
	}

	private function checkChanges() {

		$index = file_get_contents(BP.'/index.php');
		if (mb_stripos($index, '::f{4}:\\d{1,3}') === false)
			return 'index.php';
		if (mb_stripos($index, 'mb_substr($ip, 7)') === false)
			return 'index.php';
		if (mb_stripos($index, '$ip = empty($ip) ? getenv(\'REMOTE_ADDR\')') === false)
			return 'index.php';
		if (mb_stripos($index, 'config/upgrade.ip') === false)
			return 'index.php';

		return true;
	}
}