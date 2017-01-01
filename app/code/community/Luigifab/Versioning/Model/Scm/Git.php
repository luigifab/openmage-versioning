<?php
/**
 * Created S/03/12/2011
 * Updated J/10/11/2016
 *
 * Copyright 2011-2017 | Fabrice Creuzot (luigifab) <code~luigifab~info>
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

class Luigifab_Versioning_Model_Scm_Git extends Mage_Core_Model_Abstract {

	private $version = null;
	private $revision = null;
	private $items = null;


	// indique si le gestionnaire de version est installé
	// le tout à partir de la réponse de la commande 'git'
	public function isSoftwareInstalled() {
		exec('git --version', $data);
		return (preg_match('#([0-9]+\.[0-9]+\.[0-9]+)#', implode($data), $this->version) !== 0);
	}

	public function getSoftwareVersion() {
		if (is_null($this->version))
			$this->isSoftwareInstalled();
		return (!empty($this->version)) ? trim($this->version[0]) : null;
	}

	public function getType() {
		return 'git';
	}


	// génère une collection à partir de l'historique des commits du dépôt
	// met en forme les données à partir de la réponse de la commande 'git log'
	// utilise GIT_SSH si le fichier de configuration existe
	public function getCommitCollection() {

		if (!is_null($this->items))
			return $this->items;

		// lecture de l'historique des commits
		if (!is_dir('./.git/') && !is_dir('../.git/'))
			throw new Exception('The .git directory does not exist!');

		$configsh = realpath('./.git/ssh/config.sh');
		if (!is_string($configsh))
			$configsh = realpath('../.git/ssh/config.sh');

		$desc = (version_compare($this->getSoftwareVersion(), '1.7.2', '>=')) ? '%B' : '%s%n%b';

		if (is_string($configsh) && is_executable($configsh)) {
			exec('
				export GIT_SSH="'.$configsh.'";
				git fetch 2>&1;
				git log "origin/`git branch | grep "*" | cut -c3-`" --all --pretty=format:"<log><revno>%h</revno><parents> %p </parents><committer>%an</committer><timestamp>%ai</timestamp><message><![CDATA['.$desc.']]></message></log>" -'.Mage::getStoreConfig('versioning/scm/number').' | iconv -f UTF8//IGNORE -t UTF-8 -c 2>&1;
			', $data, $val);
		}
		else {
			exec('
				git fetch 2>&1;
				git log "origin/`git branch | grep "*" | cut -c3-`" --all --pretty=format:"<log><revno>%h</revno><parents> %p </parents><committer>%an</committer><timestamp>%ai</timestamp><message><![CDATA['.$desc.']]></message></log>" -'.Mage::getStoreConfig('versioning/scm/number').' | iconv -f UTF8//IGNORE -t UTF-8 -c 2>&1;
			', $data, $val);
		}

		// nettoyage du résultat
		$data = implode("\n", $data);
		$data = preg_replace('#<\!\[CDATA\[\s+\]\]>#', '', $data);
		$data = str_replace("\n\n", "\n", $data);

		// traitement de la réponse
		if (($val !== 0) || (strpos($data, '</log>') === false) || (strpos($data, 'error: ') !== false) || (strpos($data, 'fatal: ') !== false)) {

			$data = (is_array($data)) ? implode("\n", $data) : $data;
			$data = (strpos($data, '<log') !== false) ? substr($data, 0, strpos($data, '<log')) : $data;
			$data = '<u>Response:</u>'."\n".$data;

			$config = '<u>The git/config file:</u>'."\n".htmlspecialchars(file_get_contents(is_file('./.git/config') ? './.git/config' : '../.git/config'));

			throw new Exception('Can not get commit history, invalid response!'."\n\n".trim($data)."\n\n".trim($config));
		}
		else {
			$data = (strpos($data, '<') !== 0) ? substr($data, strpos($data, '<')) : $data;
			$branchs = array($this->getCurrentBranch());

			$xml = new DOMDocument();
			$xml->loadXML('<root>'.$data.'</root>');

			// extraction des données
			// construction de la collection des commits
			$this->items = new Varien_Data_Collection();

			foreach ($xml->getElementsByTagName('log') as $logentry) {

				$revision = trim($logentry->getElementsByTagName('revno')->item(0)->firstChild->nodeValue);
				$parents = trim($logentry->getElementsByTagName('parents')->item(0)->firstChild->nodeValue);
				$author = trim($logentry->getElementsByTagName('committer')->item(0)->firstChild->nodeValue);
				$timestamp = trim($logentry->getElementsByTagName('timestamp')->item(0)->firstChild->nodeValue);
				$description = trim($logentry->getElementsByTagName('message')->item(0)->firstChild->nodeValue);

				preg_match('#\s*\{([^\}]+)\}\s*$#', $description, $branch);
				if (count($branch) >= 1) {
					$description = preg_replace('#\s*\{[^\}]+\}\s*$#', '', $description);
					$branch = trim(array_pop($branch));
				}
				else {
					$branch = 'unknown';
				}

				if (!in_array($branch, $branchs))
					$branchs[] = $branch;

				$item = new Varien_Object();
				$item->setCurrentRevision($this->getCurrentRevision());
				$item->setBranch($branch);
				$item->setRevision($revision);
				$item->setParents(explode(' ', $parents));
				$item->setDate(date('c', strtotime($timestamp)));
				$item->setAuthor(preg_replace('#<[^>]+>#', '', $author));
				$item->setDescription(htmlspecialchars($description));

				$this->items->addItem($item);
			}

			// gestion des colonnes
			// en fonction des branches
			foreach ($this->items as $item) {
				$item->setColumn(array_search($item->getBranch(), $branchs));
			}
		}

		return $this->items;
	}


	// renvoi le numéro de la révision actuelle de la copie locale (à partir de la réponse de la commande 'git log')
	// renvoi l'état de la copie locale à partir de la réponse des commandes 'git status' et 'git diff'
	// renvoi la branche actuelle à partir de la réponse de la commande 'git branch'
	public function getCurrentRevision() {

		if (!is_null($this->revision))
			return $this->revision;

		exec('git rev-parse --short HEAD', $data);
		$data = implode($data);
		$data = (strlen($data) > 0) ? trim($data) : null;

		$this->revision = $data;
		return $this->revision;
	}

	public function getCurrentDiff($from = null, $to = null) {

		// --diff-filter=[(A|C|D|M|R|T|U|X|B)...[*]]
		//   Select only files that are Added (A), Copied (C), Deleted (D), Modified (M), Renamed (R),
		//   have their Type changed (T), are Unmerged (U), are Unknown (X), or have had their pairing Broken (B)
		if (version_compare($this->getSoftwareVersion(), '1.8.4', '>='))
			$command = 'git diff -U1 --diff-filter=MTUXB --ignore-all-space --ignore-blank-lines';
		else if (version_compare($this->getSoftwareVersion(), '1.5', '>='))
			$command = 'git diff -U1 --diff-filter=MTUXB --ignore-all-space';
		else
			$command = 'git diff -U1 --diff-filter=MTUXB';

		if (!is_null($from) && !is_null($to))
			$command .= ' '.$from.'..'.$to;

		$i = 0;
		exec($command, $lines);

		foreach ($lines as &$line) {

			if (strlen($line) < 1)
				unset($lines[$i]);
			else if (strpos($line, '--- a/') === 0)
				unset($lines[$i]);
			else if (strpos($line, '+++ b/') === 0)
				unset($lines[$i]);
			else if ($line === '\\ No newline at end of file')
				unset($lines[$i]);
			else if (strpos($line ,'diff --git a') === 0)
				$line = "\n".'<strong>=== modified file '.substr(htmlspecialchars($line), 13, strpos($line, ' b/') - 13).'</strong>'; //13 strlen('diff --git a/')
			else if ($line[0] === '+')
				$line = '<ins>'.htmlspecialchars($line).' </ins>';
			else if ($line[0] === '-')
				$line = '<del>'.htmlspecialchars($line).' </del>';
			else
				$line = htmlspecialchars($line);

			$i++;
		}

		return '<span>'.$command.'</span>'."\n".str_replace("\t", '    ', implode("\n", $lines));
	}

	public function getCurrentStatus() {
		exec('git status', $data);
		return '<span>git status</span>'."\n".htmlspecialchars(implode("\n", $data));
	}

	public function getCurrentBranch() {
		exec('git branch | grep "*" | cut -c3-', $data);
		return (strlen(implode($data)) > 0) ? trim(implode($data)) : null;
	}


	// met à jour la copie locale avec 'git reset' (après avoir annulé les éventuelles modifications avec 'git clean')
	// prend soin de vérifier le code de retour de la commande 'git reset' et d'enregistrer les détails de la mise à jour
	// n'utilise pas GIT_SSH étant donnée que tout est disponible sur le dépôt local
	public function upgradeToRevision($obj, $log, $revision) {

		if (is_dir('../.git/')) {
			exec('
				echo "<span>git fetch</span>" >> '.$log.';
				git fetch;
				echo "<span>git clean -f -d</span>" >> '.$log.';
				git clean -f -d .. >> '.$log.' 2>&1;
				echo "<span>git reset --hard '.$revision.'</span>" >> '.$log.';
				git reset --hard '.$revision.' >> '.$log.' 2>&1;
			', $data, $val);
		}
		else {
			exec('
				echo "<span>git fetch</span>" >> '.$log.';
				git fetch;
				echo "<span>git clean -f -d</span>" >> '.$log.';
				git clean -f -d >> '.$log.' 2>&1;
				echo "<span>git reset --hard '.$revision.'</span>" >> '.$log.';
				git reset --hard '.$revision.' >> '.$log.' 2>&1;
			', $data, $val);
		}

		$data  = trim(file_get_contents($log));
		$lines = explode("\n", $data);

		$obj->writeCommand($data);

		foreach ($lines as $line) {
			if (strpos($line, 'fatal: ') === 0)
				throw new Exception(str_replace('fatal: ', '', $line));
		}

		if ($val !== 0)
			throw new Exception($data);
	}
}