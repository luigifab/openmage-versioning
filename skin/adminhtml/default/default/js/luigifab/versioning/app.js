/**
 * Created J/22/12/2011
 * Updated V/11/05/2012
 * Version 10
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

// initialisation des traductions
function luigifabVersioningInit() {

	apijs.i18n.data.en.versioning_uptitle = "Upgrade to revision §";
	apijs.i18n.data.en.versioning_uptext = "Are you sure you want to run the upgrade process?[br]Be careful, you can't cancel this operation.";
	apijs.i18n.data.en.versioning_uptext_code = "[p]Are you sure you want to run the upgrade process?[br]Be careful, you can't cancel this operation.[/p][p][label][input type='checkbox' name='code' value='true'] Update the application code[/label][/p]";

	apijs.i18n.data.fr.versioning_uptitle = "Mise à jour vers la révision §";
	apijs.i18n.data.fr.versioning_uptext_code = "[p]Êtes-vous sûr de vouloir lancer le processus de mise à jour ?[br]Attention, cette opération ne peut pas être annulée.[/p][p][label][input type='checkbox' name='code' value='true'] Mettre à jour le code application[/label][/p]";
	apijs.i18n.data.fr.versioning_uptext = "Êtes-vous sûr de vouloir lancer le processus de mise à jour ?[br]Attention, cette opération ne peut pas être annulée.";

	apijs.i18n.data.en.versioning_deltitle = "Deleting";
	apijs.i18n.data.en.versioning_deltext = "Are you sure you want to delete this log?[br]Be careful, you can't cancel this operation.";

	apijs.i18n.data.fr.versioning_deltitle = "Suppression";
	apijs.i18n.data.fr.versioning_deltext = "Êtes-vous sûr de vouloir supprimer cet historique ?[br]Attention, cette opération ne peut pas être annulée.";
}

// demande de confirmation (livraison)
function luigifabVersioningUpgrade(url, go, compressor) {

	if (url === true)
		return true;

	if ((typeof apijs !== 'undefined') && (typeof apijs !== null)) {

		luigifabVersioningInit();

		if (go !== false) {
			location.href = url;
		}
		else {
			url.match(/revision\/([0-9a-z]+)\//);

			if (compressor === true) {
				apijs.dialogue.dialogFormOptions(
					apijs.i18n.translate('versioning_uptitle', RegExp.$1),
					apijs.i18n.translate('versioning_uptext_code'),
					luigifabVersioningUpgrade, true, url, 'versioning'
				);
				$('box').setAttribute('method', 'get');
				$$('#box button')[0].focus();
			}
			else {
				apijs.dialogue.dialogConfirmation(
					apijs.i18n.translate('versioning_uptitle', RegExp.$1),
					apijs.i18n.translate('versioning_uptext'),
					luigifabVersioningUpgrade, url, 'versioning'
				);
			}
		}

		return false;
	}
	else {
		url.match(/revision\/([0-9a-z]+)\//);
		return confirm('Are you sure (revision ' + RegExp.$1 + ')?');
	}
}

// demande de confirmation (suppression des historiques)
function luigifabVersioningDelete(url, go) {

	if ((typeof apijs !== 'undefined') && (typeof apijs !== null)) {

		luigifabVersioningInit();

		if (go !== false) {
			location.href = url;
		}
		else {
			apijs.dialogue.dialogConfirmation(
				apijs.i18n.translate('versioning_deltitle'),
				apijs.i18n.translate('versioning_deltext'),
				luigifabVersioningUpgrade, url, 'versioning'
			);
		}

		return false;
	}
	else {
		return confirm('Are you sure?');
	}
}


// génération du graphique SVG pour BZR et GIT
// testé avec Chrome 18 / Chromium 18 / Firefox 12 / Opera 11.62 / Safari 5.1 / IE 9
// ne fonctionne pas avec IE 8 même avec l'extension Adobe SVG Viewer
String.prototype.trim = function () { return this.replace(/^\s+|\s+$/g, ''); };

if ((typeof luigifab !== 'object') || (luigifab === null))
	var luigifab = {};

Event.observe(window, 'load', startVersioning);

function startVersioning() {

	if ($('versioningGrid') && ($$('#versioningGrid td.branch').length > 0)) {
		luigifab.branchmanager = new TheBranchManager();
		luigifab.branchmanager.init();
	}
}


// #### Gestion de la représentation des branches BZR/GIT ################### //
// = révision : 30
// » Crée une une balise object qui contiendra la représentation des branches grâce à une image SVG
// » Construit l'image SVG en fonction de la liste de commits
// » En cas de pépin retour à l'état initial
function TheBranchManager() {

	this.init = function () {

		var elem = document.createElement('object');
		elem.setAttribute('data', SKIN_URL.replace('enterprise/','default/') + 'images/luigifab/versioning/branch.svg.php');
		elem.setAttribute('type', 'image/svg+xml');
		elem.setAttribute('width', '97px');
		elem.setAttribute('style', 'display:none;');
		elem.setAttribute('onload', 'luigifab.branchmanager.create();');
		elem.setAttribute('id', 'svgbranch');

		$('page:main-container').appendChild(elem);
	};

	this.create = function () {

		try {
			// recherche de l'accès au graphique SVG
			// initilisation des variables
			var svgdoc = $('svgbranch').getSVGDocument().getElementById('root'),
				scm = $('scmtype').firstChild.nodeValue,
				allbranches = $$('td.branch').pluck('innerHTML').uniq(),
				size = 20 * allbranches.length + 50,
				elemText, elemTspan, elemCircle, elemLine,
				currentBranch, currentCommit, currentHeight, currentParents, currentColumn,
				testBranch, testCommit,
				fullHeight = 0,
				points = { },
				branchNames = [ ],
				branchColors = [ '', 'black', 'blue', 'red', 'limegreen', 'chocolate', 'orange', 'hotpink', 'silver', 'khaki']; // '' car column comme à 1

			// largeur du graphique SVG et de la colonne du tableau
			if (size > 97) {
				$$('col')[1].width = size + 3;
				$('svgbranch').setAttribute('width', size + 'px');
			}

			if (Prototype.Browser.Opera)
				$('svgbranch').setAttribute('width', parseInt($('svgbranch').getAttribute('width'), 10) + 10 + 'px');

			// hauteur du graphqiue SVG
			var firstTD = new Element.Layout($$('td.branch').first(), true), lastTD = new Element.Layout($$('td.branch').last(), true);
			$('svgbranch').setAttribute('height', (lastTD.get('top') - firstTD.get('top') + lastTD.get('height') + 3) + 'px');

			if (Prototype.Browser.WebKit)
				$('svgbranch').setAttribute('style', 'position:absolute; top:' + (firstTD.get('top') + 1) + 'px; left:' + (firstTD.get('left') + 2) + 'px;');
			else
				$('svgbranch').setAttribute('style', 'position:absolute; top:' + firstTD.get('top') + 'px; left:' + (firstTD.get('left') + 1) + 'px;');

			// création des points et des lignes
			// pour chaque commit
			$$('#versioningGrid tbody tr').each(function (elemTR) {

				currentBranch = elemTR.down('td.branch').firstChild.nodeValue;
				currentCommit = (elemTR.down('td.revision').firstChild.nodeName !== '#text') ? elemTR.down('td.revision').firstChild.firstChild.nodeValue.trim() : elemTR.down('td.revision').firstChild.nodeValue.trim();

				currentHeight = elemTR.getHeight();
				currentColumn = allbranches.indexOf(currentBranch) + 1;
				currentBranch = currentBranch.trim();

				// point au millieu de la case courante
				points.circleX = 20 * currentColumn;
				points.circleY = fullHeight + currentHeight / 2;

				// ligne du point précédent vers le millieu de la case courante
				points.lineX = 20 * currentColumn;
				points.lineY = fullHeight + currentHeight / 2;

				fullHeight += currentHeight;
				elemTR.down('td.branch').style.color = 'transparent';

				// texte (nom de la branche)
				if ((branchNames[currentColumn] !== true) && (scm !== 'bzr')) {

					elemText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
					elemText.setAttribute('x', points.circleX + 7);
					elemText.setAttribute('y', points.circleY + 2);
					elemText.setAttribute('fill', branchColors[currentColumn]);
					elemText.setAttribute('class', 'branch-' + currentBranch);
					elemText.setAttribute('style', 'font:0.65em sans-serif;');

						elemTspan = document.createElementNS('http://www.w3.org/2000/svg', 'tspan');
						elemTspan.appendChild(document.createTextNode(currentBranch));

					elemText.appendChild(elemTspan);
					svgdoc.appendChild(elemText);

					branchNames[currentColumn] = true;
				}

				// point
				elemCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
				elemCircle.setAttribute('cx', points.circleX);
				elemCircle.setAttribute('cy', points.circleY);
				elemCircle.setAttribute('r', 3.2);
				elemCircle.setAttribute('fill', branchColors[currentColumn]);
				elemCircle.setAttribute('class', 'branch-' + currentBranch); // pour connaître à quelle branche appartient ce commit
				elemCircle.setAttribute('id', 'commit-' + currentCommit);    // pour connaître le nom du commit
				svgdoc.appendChild(elemCircle);

				// ligne
				if (elemLine = svgdoc.getElementById('branch-' + currentBranch)) {
					elemLine.setAttribute('class', 'branch commit-' + currentCommit); // pour connaître le dernier (plus ancien) commit de la branche
					elemLine.setAttribute('x2', points.lineX);
					elemLine.setAttribute('y2', points.lineY);
				}
				else {
					elemLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
					elemLine.setAttribute('x1', points.lineX);
					elemLine.setAttribute('y1', points.lineY);
					elemLine.setAttribute('x2', points.lineX);
					elemLine.setAttribute('y2', points.lineY);
					elemLine.setAttribute('stroke', branchColors[currentColumn]);
					elemLine.setAttribute('stroke-width', 2);
					elemLine.setAttribute('class', 'branch commit-' + currentCommit); // pour connaître le dernier (plus ancien) commit de la branche
					elemLine.setAttribute('id', 'branch-' + currentBranch);           // pour connaître le nom de la branche
					svgdoc.appendChild(elemLine);
				}
			});

			// création des lignes interbranches
			// pour chaque commit
			$$('#versioningGrid tbody tr').each(function (elemTR) {

				currentParents = elemTR.down('input.parents').value;

				if (currentParents.length > 0) {

					currentParents = currentParents.split(' ');
					currentBranch = elemTR.down('td.branch').firstChild.nodeValue.trim();
					currentCommit = (elemTR.down('td.revision').firstChild.nodeName !== '#text') ? elemTR.down('td.revision').firstChild.firstChild.nodeValue.trim() : elemTR.down('td.revision').firstChild.nodeValue.trim();

					// pour chaque commit dit parent
					currentParents.each(function (currentParent) {
						$$('#versioningGrid tbody tr td.revision:contains("' + currentParent + '")').each(function (elemTD) {

							testBranch = elemTD.up().down('td.branch').firstChild.nodeValue.trim();
							testCommit = (elemTD.firstChild.nodeName !== '#text') ? elemTD.firstChild.firstChild.nodeValue.trim() : elemTD.firstChild.nodeValue.trim();

							// dans le cas ou on n'est pas sur la même branche
							if ((testCommit === currentParent) && (testBranch !== currentBranch)) {

								// ligne du point du commit courant vers le point du commit parent
								points.lineAx = svgdoc.getElementById('commit-' + currentCommit).getAttribute('cx');
								points.lineAy = svgdoc.getElementById('commit-' + currentCommit).getAttribute('cy');
								points.lineAcolor = svgdoc.getElementById('commit-' + currentCommit).getAttribute('fill');
								points.lineBcolor = svgdoc.getElementById('commit-' + testCommit).getAttribute('fill');
								points.lineBx = svgdoc.getElementById('commit-' + testCommit).getAttribute('cx');
								points.lineBy = svgdoc.getElementById('commit-' + testCommit).getAttribute('cy');

								// ligne
								elemLine = document.createElementNS('http://www.w3.org/2000/svg', 'path');
								elemLine.setAttribute('d', 'M' + points.lineAx + ',' + points.lineAy + 'L' + points.lineBx + ',' + points.lineBy);
								elemLine.setAttribute('stroke', 'url(#gradient-' + points.lineAcolor + '-' + points.lineBcolor + ')');
								elemLine.setAttribute('stroke-width', 2);
								svgdoc.appendChild(elemLine);
							}
						});
					});
				}
			});

			// prolongation des lignes sans parent
			// pour chaque branche
			allbranches.each(function (branch) {

				elemLine = svgdoc.getElementById('branch-' + branch.trim());
				currentCommit = elemLine.getAttribute('class');
				currentCommit = currentCommit.substring(currentCommit.indexOf('-') + 1);

				testCommit = $$('#versioningGrid tbody tr td.revision:contains("' + currentCommit + '")')[0].up().down('input.parents').value;

				// dans le cas ou le commit parent n'existe pas
				if ($$('#versioningGrid tbody tr td.revision:contains("' + testCommit + '")').length < 1)
					elemLine.setAttribute('y2', $('svgbranch').getHeight());
			});
		}
		catch (e) {
			$('svgbranch').remove();
			$$('#versioningGrid tbody tr td.branch').each(function (elemTD) {
				elemTD.removeAttribute('style');
			});
			alert(e);
		}
	};
}
