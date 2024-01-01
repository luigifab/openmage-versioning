<?php
/**
 * Created S/25/11/2023
 * Updated S/25/11/2023
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

class Luigifab_Versioning_Block_Adminhtml_Config_Addresses extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {

    public function _prepareToRender() {

		if (str_contains($this->getElement()->getHtmlId(), 'email')) {
			$this->addColumn('email', [
				'label' => $this->__('Email Address'),
				'style' => 'width:95%;',
				'class' => 'input-text required-entry validate-email',
			]);
			unset($this->_columns['addr']);
		}
		else {
			$this->addColumn('addr', [
				'label' => 'IPv4 / IPv6',
				'style' => 'width:95%;',
				'class' => 'input-text required-entry',
			]);
			unset($this->_columns['email']);
		}

		$this->_addAfter = false;
	}

	protected function _toHtml() {
		$html = parent::_toHtml();
		$this->_isPreparedToRender = false;
		return $html;
	}
}