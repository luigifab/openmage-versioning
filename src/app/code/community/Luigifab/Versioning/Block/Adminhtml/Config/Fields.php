<?php
/**
 * Created S/30/09/2017
 * Updated D/01/10/2017
 *
 * Copyright 2011-2020 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/openmage/versioning
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

class Luigifab_Versioning_Block_Adminhtml_Config_Fields extends Mage_Adminhtml_Block_System_Config_Form_Field {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		if ($element->getCanUseWebsiteValue() || $element->getCanUseDefaultValue())
			$element->unsScopeLabel();
		else
			$element->setScopeLabel($this->__('[SPECIAL]'));

		return parent::render($element);
	}
}