<?php
/**
 * Created D/12/02/2012
 * Updated M/24/02/2015
 * Version 5
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

class Luigifab_Versioning_Block_Adminhtml_Widget_Revision extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

	public function render(Varien_Object $row) {
		return ($row->getRevision() === $row->getCurrentRevision()) ? '<strong>'.$row->getRevision().'</strong>' : $row->getRevision();
	}
}