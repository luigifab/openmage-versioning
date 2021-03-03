<?php
/**
 * Created S/03/12/2011
 * Updated D/07/02/2021
 *
 * Copyright 2011-2021 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/openmage/versioning
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

class Luigifab_Versioning_Block_Adminhtml_Repository_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {

		parent::__construct();

		$this->setId('versioning_grid');
		$this->setDefaultSort('date');
		$this->setDefaultDir('desc');

		$this->setUseAjax(false);
		$this->setSaveParametersInSession(false);
		$this->setPagerVisibility(false);
		$this->setFilterVisibility(false);
	}

	protected function _prepareCollection() {
		$this->setCollection(Mage::registry('current_collection'));
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn('revision', [
			'header'    => $this->__('Revision'),
			'index'     => 'revision',
			'align'     => 'center',
			'width'     => '100px',
			'filter'    => false,
			'sortable'  => false,
			'column_css_class' => 'revision',
			'frame_callback'   => [$this, 'decorateRevision']
		]);

		$this->addColumn('diff', [
			'header'    => $this->__('Diff'),
			'align'     => 'center',
			'width'     => '55px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true,
			'frame_callback' => [$this, 'decorateDiff']
		]);

		$this->addColumn('graph', [
			'header'    => $this->__('Graph'),
			'width'     => '200px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true
		]);

		$this->addColumn('author', [
			'header'    => $this->__('Author'),
			'index'     => 'author',
			'align'     => 'center',
			'filter'    => false,
			'sortable'  => false,
			'frame_callback' => [$this, 'decorateAuthor']
		]);

		$this->addColumn('description', [
			'header'    => $this->__('Description'),
			'index'     => 'description',
			'filter'    => false,
			'sortable'  => false,
			'frame_callback' => [$this, 'decorateDescription']
		]);

		$this->addColumn('date', [
			'header'    => $this->__('Date'),
			'index'     => 'date',
			'type'      => 'datetime',
			'format'    => Mage::getSingleton('core/locale')->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
			'align'     => 'center',
			'width'     => '150px',
			'filter'    => false,
			'sortable'  => false
		]);

		$this->addColumn('action', [
			'type'      => 'action',
			'align'     => 'center',
			'width'     => '85px',
			'filter'    => false,
			'sortable'  => false,
			'is_system' => true,
			'frame_callback' => [$this, 'decorateLink']
		]);

		return parent::_prepareColumns();
	}


	public function getRowClass($row) {
		return '';
	}

	public function getRowUrl($row) {
		return false;
	}

	public function canDisplayContainer() {
		return false;
	}

	public function getMessagesBlock() {
		return Mage::getBlockSingleton('adminhtml/template');
	}


	public function decorateRevision($value, $row, $column, $isExport) {
		return (!$isExport && ($value == $row->getData('current_revision'))) ? sprintf('<strong>%s</strong>', $value) : $value;
	}

	public function decorateDiff($value, $row, $column, $isExport) {
		return sprintf('<input type="radio" name="d1" value="%s" /> <input type="radio" name="d2" value="%1$s" />', $row->getData('revision'));
	}

	public function decorateAuthor($value, $row, $column, $isExport) {
		return $isExport ? $value : str_replace(' ', '&nbsp;', trim($value));
	}

	public function decorateDescription($value, $row, $column, $isExport) {
		$link = Mage::getStoreConfig('versioning/scm/bugtracker');
		$text = nl2br($row->getData('description'));
		return ($isExport || empty($link)) ? $text : preg_replace('/#(\d+)/', '<a href="'.$link.'$1" class="issue">$1</a>', $text);
	}

	public function decorateLink($value, $row, $column, $isExport) {
		return sprintf('<button type="button" class="slink" onclick="versioning.confirmUpgrade(\'%s\', \'%s\');">%s</button>',
			$this->getUrl('*/*/upgrade', ['revision' => $row->getData('revision')]),
			$this->__('Update to revision %s', '§'), $this->__('Deliver'));
	}


	protected function _toHtml() {
		return str_replace('class="data', 'class="data k k0', parent::_toHtml());
	}
}