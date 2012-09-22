<?php
/**
 * Created S/02/06/2012
 * Updated V/21/09/2012
 * Version 8
 *
 * Copyright 2012 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Block_Notice extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		$html = array();

		$html[] = '<tr>';
		$html[] = '<td colspan="5">';
		$html[] = '<ul>';

		$html[] = '<li class="doc"><a href="https://redmine.luigifab.info/projects/magento/wiki/versioning" onclick="window.open(this.href); return false;">'.$this->__('Documentation').'</a></li>';

		if ($this->helper('versioning')->checkIndexPhp())
			$html[] = '<li class="ok">'.$this->__('The %s file is correctly set.', '<em>index.php</em>').'</li>';
		else
			$html[] = '<li class="ko">'.$this->__('The %s file is not correctly set.', '<em>index.php</em>').'</li>';

		if ($this->helper('versioning')->checkLocalXml())
			$html[] = '<li class="ok">'.$this->__('The %s file is correctly set.', '<em>errors/local.xml</em>').'</li>';
		else
			$html[] = '<li class="ko">'.$this->__('The %s file is not correctly set.', '<em>errors/local.xml</em>').'</li>';

		$html[] = '</ul>';
		$html[] = '</td>';
		$html[] = '</tr>';

		return implode("\n", $html);
	}
}