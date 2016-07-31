<?php
/**
 * Created J/07/02/2013
 * Updated V/08/07/2016
 * Version 10
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

class Luigifab_Versioning_Block_Adminhtml_Config_Heading extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		$lang = Mage::getStoreConfig('general/locale/code', $this->getStoreId());

		// exemple d'une adresse de base : http://mario/sites/14/web/(xyz/)(index.php/)
		// exemple d'une adresse finale  : http://mario/sites/14/web/errors/upgrade.php?lang=fr_FR
		$url = Mage::app()->getDefaultStoreView()->getBaseUrl();
		$url = preg_replace('#/[^/]+\.php#', '', $url);

		if (Mage::getStoreConfigFlag('web/url/use_store'))
			$url = str_replace('/'.Mage::app()->getDefaultStoreView()->getCode().'/', '/', $url);

		// versioning_downtime_error503.php versioning_downtime_error404.php
		// versioning_downtime_upgrade.php versioning_downtime_report.php
		$url = $url.'errors/'.$element->getHtmlId().'.php?lang='.$lang;
		$url = str_replace(array('versioning_downtime_error', 'versioning_downtime_'), '', $url);

		if ($element->getHtmlId() === 'versioning_downtime_report')
			$url .= '&amp;demo';

		return sprintf('<tr class="system-fieldset-sub-head"><td colspan="5"><h4>%s <a href="%s" onclick="window.open(this.href); return false;">%s</a></h4></td></tr>', $element->getLabel(), $url, $this->__('Preview in %s', $this->getLocaleName($lang)));
	}

	private function getStoreId() {

		$website = $this->getRequest()->getParam('website');
		$store = $this->getRequest()->getParam('store');

		if (strlen($store) > 0)
			$storeId = Mage::getModel('core/store')->load($store)->getStoreId();
		else if (strlen($website) > 0)
			$storeId = Mage::getModel('core/website')->load($website)->getDefaultStore()->getStoreId();
		else
			$storeId = Mage::app()->getDefaultStoreView()->getStoreId();

		return $storeId;
	}

	private function getLocaleName($lang) {

		$locales = Mage::app()->getLocale()->getOptionLocales();

		foreach ($locales as $locale) {

			if ($locale['value'] === $lang)
				return $locale['label'];
		}
	}
}