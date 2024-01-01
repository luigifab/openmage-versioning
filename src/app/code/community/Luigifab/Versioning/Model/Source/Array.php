<?php
/**
 * Created S/25/11/2023
 * Updated M/19/12/2023
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

class Luigifab_Versioning_Model_Source_Array extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array {

	// @deprecated
	protected function _afterLoad() {

		$data = $this->getValue();

		if (!is_array($data)) {
			if (empty($data) || ($data == 'a:0:{}')) {
				$this->setValue(false);
			}
			else if (str_contains($data, '{')) {
				try {
					$this->setValue(@unserialize($data, ['allowed_classes' => false]));
				}
				catch (Throwable $t) {
					Mage::logException($t);
					$this->setValue(false);
				}
			}
			else {
				$ukey   = '_'.substr(md5($this->getPath()), 0, 10).'_'; // not mb_substr
				$uuid   = round(microtime(true) * 1000 - 10000);
				// compatibility with previous version
				$values = [];
				$isAddr = str_contains($this->getPath(), 'byip');
				$array  = array_filter(preg_split('#\s+#', $data));
				foreach ($array as $idx => $value)
					$values[$ukey.($uuid + $idx)] = [$isAddr ? 'addr' : 'email' => $value];

				$this->setValue($values);
			}
		}

		return $this;
	}
}