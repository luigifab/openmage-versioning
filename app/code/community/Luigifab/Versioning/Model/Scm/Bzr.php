<?php
/**
 * Created S/03/12/2011
 * Updated D/03/02/2013
 * Version 18
 *
 * Copyright 2011-2013 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Model_Scm_Bzr extends Mage_Core_Model_Abstract {

	// définition des attributs
	private $version = null;
	private $commitCollection = null;
	private $currentRevision = null;


	// #### Initialisation ############################################# exception ## public ### //
	// = révision : 26
	// » Indique si le gestionnaire de version est installé ou pas ainsi que sa version
	public function _construct() {

		if (Mage::app()->getRequest()->getControllerName() !== 'versioning_repository')
			return;

		if ((Mage::getStoreConfig('versioning/scm/enabled') !== '1') || (Mage::getStoreConfig('versioning/scm/type') !== 'bzr'))
			throw new Exception('Please <a href="'.Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'versioning')).'" style="text-decoration:none;">configure</a> the module before use it.');

		if (!$this->isSoftwareInstalled())
			throw new Exception('On this system, BZR command is not available.');

		if (is_null($this->getCurrentRevision()))
			throw new Exception('Magento directory is not versioned or you have a <a href="'.Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'versioning')).'" style="text-decoration:none;">configuration</a> problem.');
	}

	public function isSoftwareInstalled() {
		exec('bzr --version', $data);
		return (preg_match('#([0-9]+\.[0-9]+\.[0-9]+)#', implode($data), $this->version) !== 0) ? true : false;
	}

	public function getSoftwareVersion() {
		return (!is_null($this->version) && !empty($this->version)) ? trim($this->version[0]) : null;
	}

	public function getRepositoryType() {
		return 'bzr';
	}


	// #### Historique ######################################### exception ## i18n ## public ### //
	// = révision : 42
	// » Génère une collection à partir de l'historique des commits du dépôt
	// » Met en forme les données à partir de la réponse de la commande bzr log (requiert bzr-xmloutput)
	// » Utilise BZR_SSH si le fichier de configuration existe
	public function getCommitCollection() {

		$controller = Mage::app()->getRequest()->getControllerName();
		$network = 'ssh: Could not resolve hostname';

		// données du cache
		if (!is_null($this->commitCollection))
			return $this->commitCollection;

		// lecture de l'historique des commits
		// à noter qu'en cas de problème d'encodage avec le contenu des commits :
		// » dans /etc/apache2/envvars décommenter '. /etc/default/locale'
		// » ou utiliser 'export LANG=fr_FR.utf-8'
		$configsh = realpath('./.bzr/ssh/config.sh');

		if (!is_string($configsh))
			$configsh = realpath('../.bzr/ssh/config.sh');

		if (is_string($configsh) && is_executable($configsh) && is_file('./.bzr/branch/branch.conf')) {
			exec('
				export BZR_SSH="'.$configsh.'";
				export LANG=fr_FR.utf-8;
				bzr log --limit '.Mage::getStoreConfig('versioning/scm/number').' --show-ids --xml `cat ./.bzr/branch/branch.conf | grep bound_location | cut -c18-` | iconv -f UTF8//IGNORE -t UTF-8 -c 2>&1;
			', $data, $val);
		}
		else if (is_string($configsh) && is_executable($configsh)) {
			exec('
				export BZR_SSH="'.$configsh.'";
				export LANG=fr_FR.utf-8;
				bzr log --limit '.Mage::getStoreConfig('versioning/scm/number').' --show-ids --xml `cat ../.bzr/branch/branch.conf | grep bound_location | cut -c18-` | iconv -f UTF8//IGNORE -t UTF-8 -c 2>&1;
			', $data, $val);
		}
		else if (is_file('./.bzr/branch/branch.conf')) {
			exec('
				export LANG=fr_FR.utf-8;
				bzr log --limit '.Mage::getStoreConfig('versioning/scm/number').' --show-ids --xml `cat ./.bzr/branch/branch.conf | grep bound_location | cut -c18-` | iconv -f UTF8//IGNORE -t UTF-8 -c 2>&1;
			', $data, $val);
		}
		else {
			exec('
				export LANG=fr_FR.utf-8;
				bzr log --limit '.Mage::getStoreConfig('versioning/scm/number').' --show-ids --xml `cat ../.bzr/branch/branch.conf | grep bound_location | cut -c18-` | iconv -f UTF8//IGNORE -t UTF-8 -c 2>&1;
			', $data, $val);
		}

		$data = implode("\n", $data);
		$data = preg_replace('#<\!\[CDATA\[\s+\]\]>#', '', $data);
		$data = str_replace("\n\n", "\n", $data);

		// traitement de la réponse
		if (($val !== 0) || (strpos($data, '</log>') === false) ||
		    ((strpos($data, 'bzr: ERROR: ') !== false) && (strpos($data, $network) !== 0) && ($controller === 'versioning_repository')) ||
		    ((strpos($data, 'bzr: ERROR: ') !== false) && ($controller !== 'versioning_repository'))) {

			$data = is_array($data) ? implode("\n", $data) : $data;
			$data = (strpos($data, '<log') !== false) ? substr($data, 0, strpos($data, '<log')) : $data;

			throw new Exception('Can not get commit history, invalid response!'."\n\n".trim($data));
		}
		else {
			if ((strpos($data, $network) === 0) && ($controller === 'versioning_repository'))
				Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('versioning')->__('Unable to update the commit history from the remote repository.<br />This list corresponds to the history of the local repository.'));

			$data = (strpos($data, '<') !== 0) ? substr($data, strpos($data, '<')) : $data;

			$xml = new DOMDocument();
			$xml->loadXML($data);

			$this->commitCollection = new Varien_Data_Collection();

			foreach ($xml->getElementsByTagName('log') as $logentry) {

				$rev = trim($logentry->getElementsByTagName('revno')->item(0)->firstChild->nodeValue);
				$author = trim($logentry->getElementsByTagName('committer')->item(0)->firstChild->nodeValue);
				$timestamp = trim($logentry->getElementsByTagName('timestamp')->item(0)->firstChild->nodeValue);
				$description = trim($logentry->getElementsByTagName('message')->item(0)->firstChild->nodeValue);

				// branche
				$branch = (str_word_count($rev, 0, '.') > 1) ? substr(substr($rev, strpos($rev, '.') + 1), 0, strpos($rev, '.') - 1) : '';

				// tags
				$tags = array();
				if ($logentry->getElementsByTagName('tags')->item(0)) {
					foreach ($logentry->getElementsByTagName('tags')->item(0)->getElementsByTagName('tag') as $tag)
						$tags[] = trim($tag->firstChild->nodeValue);
				}

				// parents
				$parents = array();
				if ($logentry->getElementsByTagName('parents')->item(0)) {
					foreach ($logentry->getElementsByTagName('parents')->item(0)->getElementsByTagName('parent') as $parent) {
						$id = array();
						exec('bzr log -r '.trim($parent->firstChild->nodeValue).' | grep "revno:" | cut -c8-', $id);
						$parents[] = trim(array_pop($id));
					}

					$parents = array_unique($parents);
				}

				$commitEntry = new Varien_Object();
				$commitEntry->setRevision($rev);
				$commitEntry->setTags($tags);
				$commitEntry->setBranchName($branch);
				$commitEntry->setParents($parents);
				$commitEntry->setAuthor(preg_replace('#<[^>]+>#', '', $author));
				$commitEntry->setDate(date('c', strtotime($timestamp)));
				$commitEntry->setDescription($description);

				$this->commitCollection->addItem($commitEntry);
			}
		}

		return $this->commitCollection;
	}


	// #### Révision ################################################################ public ### //
	// = révision : 9
	// » Renvoie le numéro de la révision actuelle de la copie locale
	// » Extrait le numéro à partir de la réponse de la commande bzr version-info
	public function getCurrentRevision($cache = true) {

		// données du cache
		if (!is_null($this->currentRevision) && $cache)
			return $this->currentRevision;

		// recherche du numéro de révision
		exec('bzr version-info | grep revno', $data);
		$data = implode($data);
		$data = (strpos($data, 'revno') !== false) ? trim(substr($data, strpos($data, ':') + 1)) : null;

		$this->currentRevision = $data;
		return $this->currentRevision;
	}


	// #### État #################################################################### public ### //
	// = révision : 2
	// » Renvoie l'état de la copie locale à partir de la réponse des commandes bzr info et bzr status
	public function getCurrentStatus() {

		exec('bzr info', $dataInfo);
		exec('bzr status', $dataStatus);

		array_unshift($dataInfo, '<span>bzr info</span>');
		array_unshift($dataStatus, '<span>bzr status</span>');

		return implode("\n", $dataInfo)."\n\n".implode("\n", $dataStatus);
	}


	// #### Mise à jour ############################################################# public ### //
	// = révision : 12
	// » Met à jour la copie locale avec bzr update (après avoir annulé les éventuelles modifications avec bzr clean-tree et bzr revert)
	// » Prend soin de vérifier le code de retour de la commande bzr update et d'enregistrer les détails de la mise à jour
	// » Utilise BZR_SSH si le fichier de configuration existe
	public function upgradeToRevision($obj, $log, $revision) {

		$configsh = realpath('./.bzr/ssh/config.sh');

		if (!is_string($configsh))
			$configsh = realpath('../.bzr/ssh/config.sh');

		if (is_string($configsh) && is_executable($configsh)) {
			exec('
				export BZR_SSH="'.$configsh.'";

				echo "<span>bzr clean-tree --force --verbose</span>" >> '.$log.';
				bzr clean-tree --force --verbose >> '.$log.' 2>&1;

				echo "<span>bzr revert --no-backup</span>" >> '.$log.';
				bzr revert --no-backup >> '.$log.' 2>&1;

				echo "<span>bzr update -r '.$revision.'</span>" >> '.$log.';
				bzr update -r '.$revision.' >> '.$log.' 2>&1;
			', $data, $val);
		}
		else {
			exec('
				echo "<span>bzr clean-tree --force --verbose</span>" >> '.$log.';
				bzr clean-tree --force --verbose >> '.$log.' 2>&1;

				echo "<span>bzr revert --no-backup</span>" >> '.$log.';
				bzr revert --no-backup >> '.$log.' 2>&1;

				echo "<span>bzr update -r '.$revision.'</span>" >> '.$log.';
				bzr update -r '.$revision.' >> '.$log.' 2>&1;
			', $data, $val);
		}

		$data = trim(file_get_contents($log));
		$obj->writeCommand($data);

		foreach (explode("\n", $data) as $line) {
			if (strpos($line, 'bzr: ERROR: ') === 0)
				throw new Exception(str_replace('bzr: ERROR: ', '', $line));
		}

		if ($val !== 0)
			throw new Exception($data);
	}
}