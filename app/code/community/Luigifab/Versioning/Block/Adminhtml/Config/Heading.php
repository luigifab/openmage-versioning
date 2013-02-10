<?php
/**
 * Created J/07/02/2013
 * Updated S/09/02/2013
 * Version 2
 *
 * Copyright 2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Block_Adminhtml_Config_Heading extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		// store id
		$pWebsite = Mage::app()->getRequest()->getParam('website');
		$pStore = Mage::app()->getRequest()->getParam('store');
		$storeId = Mage::app()->getDefaultStoreView()->getStoreId();

		if (strlen($pStore) > 0)
			$storeId = Mage::getModel('core/store')->load($pStore)->getStoreId();
		else if (strlen($pWebsite) > 0)
			$storeId = Mage::getModel('core/website')->load($pWebsite)->getDefaultStore()->getStoreId();

		// code html
		$url = $this->helper('versioning')->getFrontendUrl();
		$url = $url.'errors/versioning/'.$element->getHtmlId().'.php?lang='.Mage::getStoreConfig('general/locale/code', $storeId);
		$url = str_replace(array('index.php/','versioning_downtime_error','versioning_downtime_'), '', $url);

		return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s <a href="%s" onclick="window.open(this.href); return false;">%s</a></h4></td></tr>', $element->getHtmlId(), $element->getHtmlId(), $element->getLabel(), $url, $this->__('Preview'));
	}
}