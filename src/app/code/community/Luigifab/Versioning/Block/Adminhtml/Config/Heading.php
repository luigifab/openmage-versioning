<?php
/**
 * Created J/07/02/2013
 * Updated S/03/12/2022
 *
 * Copyright 2011-2023 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

class Luigifab_Versioning_Block_Adminhtml_Config_Heading extends Mage_Adminhtml_Block_System_Config_Form_Field_Heading {

	public function render(Varien_Data_Form_Element_Abstract $element) {

		$locale = Mage::getStoreConfig('general/locale/code', $this->getStoreId());

		// exemple d'une adresse de base : https://mario/sites/14/web/(xyz/)(index.php/)
		// exemple d'une adresse finale  : https://mario/sites/14/web/errors/upgrade.php?lang=fr_FR
		$url = Mage::app()->getDefaultStoreView()->getBaseUrl();
		$url = preg_replace('#/[^/]+\.php\d*/#', '/', $url);

		if (Mage::getStoreConfigFlag('web/url/use_store'))
			$url = str_replace('/'.Mage::app()->getDefaultStoreView()->getData('code').'/', '/', $url);

		// versioning_downtime_error503.php versioning_downtime_error404.php
		// versioning_downtime_upgrade.php versioning_downtime_report.php
		$url .= 'errors/'.$element->getHtmlId().'.php?lang='.$locale;
		$url  = str_replace(['versioning_downtime_error', 'versioning_downtime_'], '', $url);

		if ($element->getHtmlId() == 'versioning_downtime_report')
			$url = str_replace('?lang', '?demo=1&amp;lang', $url);

		// getPath PR 2774
		$isDefault = !$element->getCanUseWebsiteValue() && !$element->getCanUseDefaultValue();
		return sprintf('<tr class="system-fieldset-sub-head"><td colspan="%d"><h4>%s <a href="%s">%s</a></h4></td></tr>',
			(empty($element->getPath()) ? ($isDefault ? 4 : 5) : ($isDefault ? 5 : 6)),
			$element->getLabel(), $url, $this->__('Preview in %s', $this->getLocaleName($locale)));
	}

	protected function getLocaleName(string $code) {

		$locales = Mage::getSingleton('core/locale')->getOptionLocales();

		foreach ($locales as $locale) {
			if ($locale['value'] == $code)
				return $locale['label'];
		}
	}

	protected function getStoreId() {

		$store   = $this->getRequest()->getParam('store');
		$website = $this->getRequest()->getParam('website');

		if (!empty($store))
			$storeId = Mage::app()->getStore($store)->getId();
		else if (!empty($website))
			$storeId = Mage::getModel('core/website')->load($website)->getDefaultStore()->getId();
		else
			$storeId = Mage::app()->getDefaultStoreView()->getId();

		return $storeId;
	}
}