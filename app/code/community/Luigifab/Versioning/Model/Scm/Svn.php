<?php
/**
 * Created S/03/12/2011
 * Updated S/07/04/2012
 * Version 8
 *
 * Copyright 2011-2012 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Model_Scm_Svn extends Mage_Core_Model_Abstract {

	// définition des attributs
	private $version = null;
	private $commitCollection = null;
	private $currentRevision = null;


	// #### Initialisation ############################################# exception ## public ### //
	// = révision : 25
	// » Indique si le gestionnaire de version est installé ou pas ainsi que sa version
	public function _construct() {

		if (Mage::app()->getRequest()->getModuleName() !== 'versioning')
			return;

		if ((Mage::getStoreConfig('versioning/scm/enabled') !== '1') || (Mage::getStoreConfig('versioning/scm/type') !== 'svn'))
			throw new Exception('Please <a href="'.Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'versioning')).'" style="text-decoration:none;">configure</a> the module before use it.');

		if (!$this->isSoftwareInstalled())
			throw new Exception('On this system, SVN command is not available.');

		if (is_null($this->getCurrentRevision()))
			throw new Exception('Magento directory is not versioned or you have a <a href="'.Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'versioning')).'" style="text-decoration:none;">configuration</a> problem.');
	}

	public function isSoftwareInstalled() {
		exec('svn --version', $data);
		return (preg_match('#([0-9]+\.[0-9]+\.[0-9]+)#', implode($data), $this->version) !== 0) ? true : false;
	}

	public function getSoftwareVersion() {
		return (!is_null($this->version) && !empty($this->version)) ? trim($this->version[0]) : null;
	}

	public function getRepositoryType() {
		return 'svn';
	}


	// #### Historique ######################################### exception ## i18n ## public ### //
	// = révision : 22
	// » Génère une collection à partir de l'historique des commits du dépôt
	// » Met en forme les données à partir de la réponse de la commande svn log
	// » N'utilise pas SVN_SSH même si le fichier de configuration existe
	public function getCommitCollection() {

		$bugtracker = trim(Mage::getStoreConfig('versioning/tweak/bugtracker'));
		$moduleName = Mage::app()->getRequest()->getModuleName();
		$network = 'ssh: Could not resolve hostname';

		// données du cache
		if (!is_null($this->commitCollection))
			return $this->commitCollection;

		// lecture de l'historique
		// en cas de problème d'encodage dans /etc/apache2/envvars décommenter '. /etc/default/locale' ou utiliser 'export LANG=fr_FR.utf-8;'
		if (Mage::getStoreConfig('versioning/scm/fulllog') === '1')
			exec('svn log --xml `svn info | sed --quiet "s/URL.*\(h.*\)/\1/p"` 2>&1', $data, $val);
		else
			exec('svn log --limit 20 --xml `svn info | sed --quiet "s/URL.*\(h.*\)/\1/p"` 2>&1', $data, $val);

		$data = implode("\n", $data);
		$data = str_replace(array("\n\n",'<msg>','</msg>'), array("\n",'<msg><![CDATA[',']]></msg>'), $data);

		// traitement de la réponse
		if (($val !== 0) || (strpos($data, '</log>') === false) ||
		    ((strpos($data, 'svn: ') !== false) && (strpos($data, $network) !== 0) && ($moduleName === 'versioning')) ||
		    ((strpos($data, 'svn: ') !== false) && ($moduleName !== 'versioning'))) {

			$data = is_array($data) ? implode("\n", $data) : $data;
			$data = (strpos($data, '<log') !== false) ? substr($data, 0, strpos($data, '<log')) : $data;

			throw new Exception('Can not get commit history, invalid response!'."\n\n".trim($data));
		}
		else {
			if ((strpos($data, $network) === 0) && ($moduleName === 'versioning'))
				echo '<p id="noticeupdate">',Mage::helper('versioning')->__('Unable to update the commit history from the remote repository.<br />This list corresponds to the history of the local repository.'),'</p>';

			$data = (strpos($data, '<') !== 0) ? substr($data, strpos($data, '<')) : $data;
			$xml = new DOMDocument();
			$xml->loadXML($data);

			$this->commitCollection = new Varien_Data_Collection();

			foreach ($xml->getElementsByTagName('logentry') as $logentry) {

				$revision = trim($logentry->getAttribute('revision'));
				$author = trim($logentry->getElementsByTagName('author')->item(0)->firstChild->nodeValue);
				$timestamp = trim($logentry->getElementsByTagName('date')->item(0)->firstChild->nodeValue);
				$description = trim($logentry->getElementsByTagName('msg')->item(0)->firstChild->nodeValue);

				if (strlen($bugtracker) > 0) {
					$author = preg_replace('#<[^>]+>#', '', $author);
					$description = preg_replace('#\#([0-9]+)#', '<a href="'.$bugtracker.'$1" class="issue" onclick="window.open(this.href); return false;">$1</a>', $description);
				}
				else {
					$author = preg_replace('#<[^>]+>#', '', $author);
					$description = preg_replace('#\#([0-9]+)#', '<span class="issue">$1</span>', $description);
				}

				$commitEntry = new Varien_Object();
				$commitEntry->setRevision($revision);
				$commitEntry->setAuthor($author);
				$commitEntry->setDate($timestamp);
				$commitEntry->setDescription(nl2br($description));

				$this->commitCollection->addItem($commitEntry);
			}
		}

		return $this->commitCollection;
	}


	// #### Révision ################################################################ public ### //
	// = révision : 9
	// » Renvoie le numéro de la révision actuelle de la copie locale
	// » Extrait le numéro à partir de la réponse de la commande svn info
	public function getCurrentRevision($cache = true) {

		// données du cache
		if (!is_null($this->currentRevision) && $cache)
			return $this->currentRevision;

		// recherche du numéro de révision
		exec('svn info | grep Revision', $data);
		$data = implode($data);
		$data = (strpos($data, 'Revision') !== false) ? trim(substr($data, strpos($data, ':') + 1)) : null;

		$this->currentRevision = $data;
		return $this->currentRevision;
	}


	// #### Mise à jour ############################################################# public ### //
	// = révision : 8
	// » Met à jour la copie locale avec svn update (après avoir annulé les éventuelles modifications avec svn revert)
	// » Prend soin de vérifier le code de retour de la commande svn update et d'enregistrer les détails de la mise à jour
	// » N'utilise pas SVN_SSH même si le fichier de configuration existe
	public function upgradeToRevision($obj, $log, $revision) {

		exec('
			echo "<span>svn revert</span>" >> '.$log.'
			svn revert >> '.$log.' 2>&1

			echo "<span>svn update -r '.$revision.'</span>" >> '.$log.'
			svn update -r '.$revision.' >> '.$log.' 2>&1
		', $data, $val);

		$data = trim(file_get_contents($log));
		$obj->writeCommand($data);

		foreach (explode("\n", $data) as $line) {
			if (strpos($line, 'svn: ') === 0)
				throw new Exception($line);
		}

		if ($val !== 0)
			throw new Exception($data);
	}
}