<?php
/**
 * Created V/23/05/2014
 * Updated J/26/07/2018
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

class Luigifab_Versioning_Block_Adminhtml_Config_Help extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		if (($msg = $this->checkChanges()) === true) {
			return sprintf('<p class="box">Luigifab/Versioning %s <a href="https://www.%s" style="float:right;">%2$s</a></p>',
				$this->helper('versioning')->getVersion(), 'luigifab.info/magento/versioning');
		}
		else {
			return sprintf('<p class="box">Luigifab/Versioning %s <a href="https://www.%s" style="float:right;">%2$s</a></p>'.
				'<p class="box" style="margin-top:-5px; color:white; background-color:#E60000;"><strong>%s</strong><br />%s</p>',
				$this->helper('versioning')->getVersion(), 'luigifab.info/magento/versioning',
				$this->__('INCOMPLETE MODULE INSTALLATION'),
				$this->__('Changes in <em>%s</em> are not present. Please read the documentation.', $msg));
		}
	}

	private function checkChanges() {

		$index = file_get_contents(BP.'/index.php');
		if (strpos($index, 'trim(array_pop($ip))') === false)
			return 'index.php';
		if (strpos($index, 'trim(getenv(\'REMOTE_ADDR\'))') === false)
			return 'index.php';
		if (strpos($index, 'config/upgrade.ip') === false)
			return 'index.php';

		return true;
	}
}