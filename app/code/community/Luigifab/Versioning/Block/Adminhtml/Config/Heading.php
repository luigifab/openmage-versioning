<?php
/**
 * Created J/07/02/2013
 * Updated S/17/01/2015
 * Version 7
 *
 * Copyright 2011-2015 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

		$code = Mage::getStoreConfig('general/locale/code', $this->getStoreId());

		// exemple d'une adresse de base : http://mario/sites/14/web/(index.php/)
		// exemple d'une adresse finale  : http://mario/sites/14/web/errors/upgrade.php?lang=fr_FR
		$url = Mage::app()->getDefaultStoreView()->getBaseUrl();
		$url = preg_replace('#/[^/]+\.php#', '', $url);
		$url = $url.'errors/'.$element->getHtmlId().'.php?lang='.$code;
		$url = str_replace(array('versioning_downtime_error', 'versioning_downtime_'), '', $url);

		if ($element->getHtmlId() == 'versioning_downtime_report')
			$url .= '&amp;demo';

		return sprintf('<tr class="system-fieldset-sub-head"><td colspan="5"><h4>%s <a href="%s" onclick="window.open(this.href); return false;">%s</a></h4></td></tr>', $element->getLabel(), $url, $this->__('Preview in %s', $this->getLocaleName($code)));
	}

	private function getStoreId() {

		$pWebsite = Mage::app()->getRequest()->getParam('website');
		$pStore = Mage::app()->getRequest()->getParam('store');

		if (strlen($pStore) > 0)
			$storeId = Mage::getModel('core/store')->load($pStore)->getStoreId();
		else if (strlen($pWebsite) > 0)
			$storeId = Mage::getModel('core/website')->load($pWebsite)->getDefaultStore()->getStoreId();
		else
			$storeId = Mage::app()->getDefaultStoreView()->getStoreId();

		return $storeId;
	}

	private function getLocaleName($code) {

		foreach (Mage::app()->getLocale()->getOptionLocales() as $locale) {

			if ($locale['value'] == $code)
				return $locale['label'];
		}
	}
}