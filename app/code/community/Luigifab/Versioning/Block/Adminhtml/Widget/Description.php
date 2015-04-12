<?php
/**
 * Created S/03/12/2011
 * Updated J/26/02/2015
 * Version 4
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

class Luigifab_Versioning_Block_Adminhtml_Widget_Description extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

	public function render(Varien_Object $row) {

		$bugtracker = trim(Mage::getStoreConfig('versioning/scm/bugtracker'));
		$description = nl2br($row->getDescription());

		if (strlen($bugtracker) > 0) {
			$description = preg_replace('/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/', '<a href="$1" onclick="window.open(this.href); return false;">$1</a>', $description);
			$description = preg_replace('#\#([0-9]+)#', '<a href="'.$bugtracker.'$1" class="issue" onclick="window.open(this.href); return false;">$1</a>', $description);
		}
		else {
			$description = preg_replace('/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/', '<a href="$1" onclick="window.open(this.href); return false;">$1</a>', $description);
		}

		return $description;
	}
}