<?php
/**
 * Created S/03/03/2012
 * Updated J/17/05/2012
 * Version 2
 *
 * Copyright 2012-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Model_Source_Number {

	public function toOptionArray() {

		return array(
			array('value' => '20', 'label' => '20'),
			array('value' => '30', 'label' => '30'),
			array('value' => '50', 'label' => '50'),
			array('value' => '100', 'label' => '100'),
			array('value' => '200', 'label' => '200')
		);
	}
}