<?php
/**
 * Created V/23/05/2014
 * Updated J/06/07/2017
 *
 * Copyright 2011-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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
		$url = 'luigifab.info/magento/versioning';
		return sprintf('<p class="box">Luigifab/Versioning %s <a href="https://www.%s" onclick="window.open(this.href); return false;" style="float:right;">%2$s</a></p>', $this->helper('versioning')->getVersion(), $url);
	}
}