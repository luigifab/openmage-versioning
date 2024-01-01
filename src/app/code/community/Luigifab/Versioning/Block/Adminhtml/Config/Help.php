<?php
/**
 * Created V/23/05/2014
 * Updated S/11/11/2023
 *
 * Copyright 2011-2024 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Versioning_Block_Adminhtml_Config_Help extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		$msg = $this->checkChanges();
		if ($msg !== true)
			return sprintf('<p class="box">%s %s <span class="right">Stop russian war. <b>🇺🇦 Free Ukraine!</b> | <a href="https://github.com/luigifab/%3$s">github.com</a> | <a href="https://www.%4$s">%4$s</a> - ⚠ IPv6</span></p><p class="box" style="margin-top:-5px; color:white; background-color:#E60000;"><strong>%5$s</strong><br />%6$s</p>',
				'Luigifab/Versioning', $this->helper('versioning')->getVersion(), 'openmage-versioning', 'luigifab.fr/openmage/versioning',
				$this->__('INCOMPLETE MODULE INSTALLATION'),
				$this->__('Changes in <em>%s</em> are not present. Please read the documentation.', $msg));

		return sprintf('<p class="box">%s %s <span class="right">Stop russian war. <b>🇺🇦 Free Ukraine!</b> | <a href="https://github.com/luigifab/%3$s">github.com</a> | <a href="https://www.%4$s">%4$s</a> - ⚠ IPv6</span></p>',
			'Luigifab/Versioning', $this->helper('versioning')->getVersion(), 'openmage-versioning', 'luigifab.fr/openmage/versioning');
	}

	protected function checkChanges() {

		$index = file_get_contents(getenv('SCRIPT_FILENAME') ?? BP.'/index.php');
		if (!str_contains($index, '::f{4}:\\d{1,3}'))
			return 'index.php';
		if (!str_contains($index, '? substr($ip, 7)')) // not mb_substr
			return 'index.php';
		if (!str_contains($index, '$ip = empty($ip) ? getenv(\'REMOTE_ADDR\')'))
			return 'index.php';
		if (!str_contains($index, 'config/upgrade.ip'))
			return 'index.php';

		return true;
	}
}