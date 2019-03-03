<?php
/**
 * Created J/07/02/2013
 * Updated M/05/02/2019
 *
 * Copyright 2011-2019 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/magento/versioning
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

class Luigifab_Versioning_Block_Adminhtml_Config_Heading extends Mage_Adminhtml_Block_System_Config_Form_Field_Heading {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		$lang = Mage::getStoreConfig('general/locale/code', $this->getStoreId());

		// exemple d'une adresse de base : https://mario/sites/14/web/(xyz/)(index.php/)
		// exemple d'une adresse finale  : https://mario/sites/14/web/errors/upgrade.php?lang=fr_FR
		$url = Mage::app()->getDefaultStoreView()->getBaseUrl();
		$url = preg_replace('#/[^/]+\.php\d*/#', '/', $url);

		if (Mage::getStoreConfigFlag('web/url/use_store'))
			$url = str_replace('/'.Mage::app()->getDefaultStoreView()->getData('code').'/', '/', $url);

		// versioning_downtime_error503.php versioning_downtime_error404.php
		// versioning_downtime_upgrade.php versioning_downtime_report.php
		$url = $url.'errors/'.$element->getHtmlId().'.php?lang='.$lang;
		$url = str_replace(array('versioning_downtime_error', 'versioning_downtime_'), '', $url);

		if ($element->getHtmlId() == 'versioning_downtime_report')
			$url = str_replace('?lang', '?demo=1&amp;lang', $url);

		return sprintf('<tr class="system-fieldset-sub-head"><td colspan="5"><h4>%s <a href="%s">%s</a></h4></td></tr>',
			$element->getData('label'), $url, $this->__('Preview in %s', $this->getLocaleName($lang)));
	}

	private function getStoreId() {

		$website = $this->getRequest()->getParam('website');
		$store   = $this->getRequest()->getParam('store');

		if (!empty($store))
			$storeId = Mage::app()->getStore($store)->getId();
		else if (!empty($website))
			$storeId = Mage::getModel('core/website')->load($website)->getDefaultStore()->getId();
		else
			$storeId = Mage::app()->getDefaultStoreView()->getId();

		return $storeId;
	}

	private function getLocaleName($lang) {

		$locales = Mage::getSingleton('core/locale')->getOptionLocales();

		foreach ($locales as $locale) {
			if ($locale['value'] == $lang)
				return $locale['label'];
		}
	}
}