<?php
/**
 * Created V/23/05/2014
 * Updated D/01/06/2014
 * Version 2
 *
 * Copyright 2011-2016 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Block_Adminhtml_Config_Help extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	public function render(Varien_Data_Form_Element_Abstract $element) {
		$url = 'https://redmine.luigifab.info/projects/magento/wiki/versioning';
		return '<p class="box">Luigifab/Versioning '.$this->helper('versioning')->getVersion().' <a href="'.$url.'" onclick="window.open(this.href); return false;" style="float:right;">'.$url.'</a></p>';
	}
}