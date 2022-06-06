/**
 * Created J/22/12/2011
 * Updated J/26/05/2022
 *
 * Copyright 2011-2022 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
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

if (window.NodeList && !NodeList.prototype.forEach) {
	NodeList.prototype.forEach = function (callback, that, i) {
		that = that || window;
		for (i = 0; i < this.length; i++)
			callback.call(that, this[i], i, this);
	};
}

var versioning = new (function () {

	"use strict";
	this.svg   = null;
	this.width = 197;

	this.init = function () {

		if (document.getElementById('versioning_grid_table') && document.querySelector('table.data tbody input')) {
			console.info('versioning.app - hello');
			this.drawGraph(self.versioningIds, self.versioningCols).initDiff();
		}
		else if (document.getElementById('versioning_history_grid_table') && document.querySelector('table.data tbody button')) {
			console.info('versioning.app - hello');
			document.querySelector('table.data tbody button').click();
		}
	};

	this.loader = function () {
		document.querySelector('body').classList.add('fabload');
	};

	this.decode = function (data) {
		// utf-8 avec Webkit (https://stackoverflow.com/q/3626183)
		return decodeURIComponent(escape(atob(data)));
	};

	// confirmation des pages de maintenance
	this.confirmFlag = function (url, title, content) {

		try {
			// avec l'apijs
			// utilise une jolie boîte de dialogue
			apijs.dialog.dialogConfirmation(
				title, // title
				this.decode(content),         // text
				versioning.actionConfirmFlag, // callback
				url,   // args
				'versioning ' + document.getElementById('scmtype').textContent // icon
			);

			return false;
		}
		catch (e) {
			console.error(e);
			try { apijs.dialog.actionClose(); } catch (ignore) { }

			try {
				// sans l'apijs
				// demande de confirmation
				if (confirm(this.decode(content).replace(/\[[^\]]+]/g, ''))) {
					this.loader();
					self.location.href = url;
					return true;
				}
				else {
					return false;
				}
			}
			catch (ee) {
				console.error(ee);

				// en dernier recours
				// demande de confirmation
				if (confirm(Translator.translate('Are you sure?'))) {
					this.loader();
					self.location.href = url;
					return true;
				}
				else {
					return false;
				}
			}
		}
	};

	this.actionConfirmFlag = function (url) {
		versioning.loader();
		apijs.dialog.remove('waiting', 'lock'); // obligatoire sinon demande de confirmation de quitter la page
		self.location.href = url;
	};

	this.cancelFlag = function (url) {
		this.loader();
		self.location.href = url;
	};

	// confirmation de mise à jour
	this.confirmUpgrade = function (url, title) {

		try {
			// avec l'apijs
			// utilise une jolie boîte de dialogue
			url.match(/revision\/(\w+)\//);
			apijs.dialog.dialogFormOptions(
				title.replace('§', RegExp.$1),    // title
				this.decode(self.versioningText), // text
				url,  // action
				versioning.actionConfirmUpgrade,  // callback (en deux temps, vérification puis redirection)
				null, // args
				'versioning ' + document.getElementById('scmtype').textContent // icon
			);

			return false;
		}
		catch (e) {
			console.error(e);
			try { apijs.dialog.actionClose(); } catch (ignore) { }

			try {
				// sans l'apijs
				// simule la boîte de dialogue de l'apijs
				var data = document.createElement('div'),
				    text = this.decode(self.versioningText).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\[/g, '<').replace(/]/g, '>'),
				    icon = document.getElementById('scmtype').textContent;

				url.match(/revision\/(\w+)\//);
				data.innerHTML =
					'<div class="fake options versioning ' + icon + ' ready" id="apijsDialog">' +
						'<form method="get" action="' + url + '" class="fake options versioning ' + icon + ' ready" onsubmit="return versioning.actionConfirmUpgrade(true);" id="apijsBox">' +
							'<h1>' + title.replace('§', RegExp.$1) + '</h1>' +
							'<div class="bbcode">' + text + '</div>' +
							'<div class="control">' +
								'<button type="submit" class="confirm">Valider</button>' +
								'<button type="button" class="cancel" onclick="versioning.closeConfirmUpgrade();">Annuler</button>' +
							'</div>' +
						'</form>' +
					'</div>';

				document.querySelector('body').appendChild(data);
				document.querySelector('div.bbcode input').focus();

				return false;
			}
			catch (ee) {
				console.error(ee);

				// en dernier recours
				// demande de confirmation
				if (confirm(Translator.translate('Are you sure?'))) {
					this.loader();
					return true;
				}
				else {
					return false;
				}
			}
		}
	};

	this.closeConfirmUpgrade = function () {
		document.getElementById('apijsDialog').remove();
	};

	this.actionConfirmUpgrade = function (action) {

		// avec l'apijs, en deux temps
		// validation du formulaire si la fonction callback avec son paramètre args renvoie true, callback(false, args)
		// appelle la fonction callback avec ses paramètres action et args après la validation du dialogue, callback(action, args)
		if (action === false)
			return true;

		versioning.loader();

		// sans l'apijs
		if (action === true) {
			document.querySelector('div.bbcode').setAttribute('style', 'visibility:hidden;');
			document.querySelector('div.control').setAttribute('style', 'visibility:hidden;');
		}
		// avec l'apijs, en deux temps
		else {
			apijs.dialog.remove('waiting', 'lock'); // obligatoire sinon demande de confirmation de quitter la page
			self.location.href = action + apijs.serialize(document.getElementById('apijsBox')).replace(/[=&]/g, '/');
		}

		return true;
	};

	// affichage de l'historique
	this.history = function (elem, content) {

		document.querySelectorAll('table.data tbody tr[class]').forEach(function (elem) { elem.classList.remove('current'); });
		document.querySelector('pre').innerHTML = this.decode(content) + "\n\n";

		elem.parentNode.parentNode.classList.add('current');
		return false;
	};

	// Représentation des branches
	// » Utilise Raphael.js 2.3.0 (93,4 ko) pour la création de l'image SVG - https://github.com/DmitryBaranovskiy/raphael
	// » Utilise la fonction innerSVG (1,4 ko) pour l'ajout des dégradés - https://code.google.com/p/innersvg/
	// » Pour chaque commit crée un point éventuellement suivi d'une étiquette avec le nom de la branche
	// » Crée ensuite les lignes entres les points
	this.drawGraph = function (data, cols) {

		var x, y, pX, pY, elem, gradients = '',
			commits   = Object.keys(data).map(function (key) { return data[key]; }), // les commits sous forme d'un array
			tableRows = document.querySelectorAll('table.data tbody tr'), rows = tableRows.length - 1, // les lignes du tableau html
			colors = [], styles = [], names = [], tops = [], bottoms = [],
			grad = 0, offsetTop = 0, graphHeight = 0, topPoint = 0, miHeight = 0, dMiHeight = 0;

		// https://stackoverflow.com/a/1129270
		// inverse l'ordre du tableau
		commits.sort(function (a, b) {
			if (a.row > b.row)
				return -1;
			if (a.row < b.row)
				return 1;
			return 0;
		});

		// recheche de la hauteur et de la position du graphique
		// offsetTop = la position du haut du graphique (à partir du haut du document)
		//  topPoint = la position du haut de la première ligne dans le tableau (à partir du haut du document)
		offsetTop   = this.getTopPosition(tableRows[0]) - 1;
		graphHeight = this.getTopPosition(tableRows[rows]) + tableRows[rows].offsetHeight - offsetTop - 1;

		// initialisation du graphique
		// canvas = l'élément svg
		this.svg = new Raphael(document.getElementById('versioning_grid_table').parentNode);
		this.svg.setSize(this.width, graphHeight);
		this.svg.canvas.setAttribute('style', 'top:' + offsetTop + 'px;');
		this.svg.canvas.setAttribute('class', 'k k0');
		this.svg.canvas.setAttribute('id', 'versioning_graph');
		this.svg.canvas.setAttribute('onmouseover', 'versioning.mouseOver(true);');
		this.svg.canvas.parentNode.setAttribute('onmouseleave', 'versioning.mouseOver(false);');

		// génération des couleurs
		// mémorise en même temps le point le plus haut/bas de chaque branche
		Raphael.getColor.reset();
		Raphael.getColor();
		colors.push(Raphael.getColor());

		for (x = 0; x <= cols; x++) {

			Raphael.getColor();
			Raphael.getColor();
			colors.push(Raphael.getColor());

			styles.push('svg.k' + x + ' .k:not(.k' + x + ') { opacity:0.4; }');
			styles.push('table.k' + x + ' .k:not(.k' + x + ') { color:#CCC; }');
			styles.push('table.k' + x + ' .k:not(.k' + x + ') button { opacity:0; visibility:hidden; }');
		}

		commits.forEach(function (commit) {

			commit.color = colors[commit.col];
			commit.klass = 'k k' + commit.col;

			bottoms[commit.col] = commit.revision;
			if (!tops[commit.col])
				tops[commit.col] = commit.revision;
		});

		// Pour chaque commit (du haut vers le bas)
		// offsetTop = la position du haut du graphique (à partir du haut du document)
		//  topPoint = la position du haut de la première ligne dans le tableau (à partir du haut du document)
		//  miHeight = le milieu de la ligne dans le tableau
		//      rows = le nombre de ligne dans le tableau (de 0 à tableRows-1)
		//       row = le numéro de la ligne dans le tableau (max = la première ligne, 0 = la dernière ligne)
		commits.forEach(function (commit) {

			// recherche de la position du point
			topPoint = this.getTopPosition(tableRows[rows - commit.row]) - offsetTop;
			miHeight = tableRows[rows - commit.row].offsetHeight / 2;

			// sur X (position horizontale) le  25 correspond à l'espace entre les colonnes, donc entre deux branches
			// sur X (position horizontale) le +20 permet de ne pas coller la première branche au bord
			y = topPoint + miHeight;
			x = 25 * commit.col + 20;

			// dessine un point
			this.svg.circle(x, y, 3.5)
				.attr('fill', commit.color)
				.attr('stroke', 'none')
				.attr('class', commit.klass);

			// écrit un texte dans une étiquette
			// en profite également pour vérifier la largeur du graphique
			if ((commit.branch.length > 0) && (names.indexOf(commit.branch) < 0)) {

				names.push(commit.branch);

				elem = this.svg.text(x + 13, y - 0.3, commit.branch)
					.attr('fill', commit.color)
					.attr('text-anchor', 'start')
					.attr('class', commit.klass);

				pX = x + 3.2 + 8 + elem.getBBox().width + 7;       // variable temporaire
				this.svg.path(
					'M ' + (x + 3.2) + ',' + (y - 0.4) +          // point de départ au niveau du point
					' L ' + (x + 3.2 + 8) + ',' + (y - 0.4 - 8) + // en haut à gauche
					' L ' + (pX) + ',' + (y - 0.4 - 8) +          // en haut à droite
					' L ' + (pX) + ',' + (y - 0.4 + 8) +          // en bas à droite
					' L ' + (x + 3.2 + 8) + ',' + (y - 0.4 + 8) + // en bas à gauche
					' Z')
					.attr('stroke', commit.color)
					.attr('fill', 'white')
					.attr('fill-opacity', '0.7')
					.attr('stroke-opacity', '0.2')
					.attr('class', commit.klass)
					.toFront();
				elem.toFront(); // repasse le texte au dessus de l'étiquette

				if (pX > this.width)
					this.width = pX;
			}

			// ligne vers le parent (donc un peu plus bas)
			// s'il existe, sinon vers le bas du graphique
			commit.parents.forEach(function (ref, parent) {

				parent = (ref.length > 0) ? data[ref] : undefined;

				if (typeof parent == 'object') {

					// recherche de la position du point
					topPoint = this.getTopPosition(tableRows[rows - parent.row]) - offsetTop;
					miHeight = tableRows[rows - parent.row].offsetHeight / 2;

					// sur X (position horizontale) le 25 correspond à l'espace entre les colonnes, donc entre deux branches
					// sur X (position horizontale) le +20 permet de ne pas coller la première branche au bord
					pY = topPoint + miHeight;
					pX = 25 * parent.col + 20;

					if (parent.col === commit.col) {
						// dessine une ligne verticale
						this.svg.path(['M', x, y, 'V', pY])
							.attr('stroke', commit.color)
							.attr('stroke-width', 1.7)
							.attr('class', commit.klass)
							.toBack();
					}
					else {
						// dégradé manuel car Raphael.js ne permet pas de définir un dégradé sur un path sur stroke
						// dans un sens ou dans l'autre, bref on veut pas savoir
						// attention pour les lignes en travers pas de kX
						gradients += '<linearGradient id="manGrad' + grad + '" x1="0" y1="0" x2="100%" y2="0">' +
							'<stop offset="0" stop-color="' + ((x > pX) ? parent.color : commit.color) + '"></stop>' +
							'<stop offset="100%" stop-color="' + ((x > pX) ? commit.color : parent.color) + '"></stop>' +
						'</linearGradient>';

						dMiHeight = tableRows[rows - parent.row].offsetHeight / 2;
						dMiHeight += miHeight;

						if ((parent.revision === tops[parent.col]) && (y + dMiHeight < pY)) {
							// dessine une ligne en travers
							elem = this.svg.path(['M', x, y, 'T', pX, y + dMiHeight]);
							elem.node.setAttribute('stroke', 'url(#manGrad' + grad + ')');
							elem.attr('stroke-width', 1.6).attr('class', 'k').toBack();
							// dessise une ligne verticale
							this.svg.path(['M', pX, y + dMiHeight, 'V', pY])
								.attr('stroke', parent.color)
								.attr('stroke-width', 1.7)
								.attr('class', 'k')
								.toBack();
						}
						else if ((commit.revision === bottoms[commit.col]) && (pY > y + dMiHeight) && (commit.parents.length === 1)) {
							// dessise une ligne verticale
							this.svg.path(['M', x, y, 'V', pY - dMiHeight])
								.attr('stroke', commit.color)
								.attr('stroke-width', 1.7)
								.attr('class', 'k')
								.toBack();
							// dessine une ligne en travers
							elem = this.svg.path(['M', x, pY - dMiHeight, 'T', pX, pY]);
							elem.node.setAttribute('stroke', 'url(#manGrad' + grad + ')');
							elem.attr('stroke-width', 1.6).attr('class', 'k').toBack();
						}
						else {
							// dessine une ligne en travers
							elem = this.svg.path(['M', x, y, 'T', pX, pY]);
							elem.node.setAttribute('stroke', 'url(#manGrad' + grad + ')');
							elem.attr('stroke-width', 1.6).attr('class', 'k').toBack();
						}

						grad += 1;
					}
				}
				else if (ref.length > 0) {
					// dessine une ligne verticale vers le bas du graphique
					this.svg.path(['M', x, y, 'V', graphHeight])
						.attr('stroke', commit.color)
						.attr('stroke-width', 1.7)
						.attr('class', commit.klass)
						.toBack();
				}

			}, this); // pour que ci-dessus this = this

			elem = tableRows[rows - commit.row];
			elem.setAttribute('onclick', "versioning.updateClass(this.getAttribute('class'));");
			elem.setAttribute('class', ((commit.row % 2) < 1) ? commit.klass : 'even ' + commit.klass);
			elem.removeAttribute('title');

		}, this); // pour que ci-dessus this = this

		// une seule fois sinon fonctionne que pour le dernier ajout avec Edge 14
		if (gradients.length > 0)
			document.querySelector('svg defs').innerSVG = gradients;

		// ajoute les styles pour les animations
		elem = document.createElement('style');
		elem.setAttribute('type', 'text/css');
		elem.setAttribute('id', 'versioning_styles');
		elem.appendChild(document.createTextNode(styles.join("\n")));
		document.querySelector('head').appendChild(elem);

		return this;
	};

	this.getTopPosition = function (elem) {
		var bodyRect = document.querySelector('body').getBoundingClientRect(), elemRect = elem.getBoundingClientRect();
		return elemRect.top - bodyRect.top;
	};

	this.mouseOver = function (show) {
		this.svg.canvas.style.width = show ? this.width + 'px' : '197px';
		this.svg.canvas.style.pointerEvents = show ? 'none' : 'inherit';
	};

	this.updateClass = function (klass) {
		this.svg.canvas.setAttribute('class', klass);
		document.getElementById('versioning_grid_table').setAttribute('class', 'data ' + klass);
	};

	// gestion des cases du diff
	this.initDiff = function () {

		var a = 1, b = 1, c = true;
		document.querySelectorAll('table.data input[type="radio"]').forEach(function (elem) {
			elem.setAttribute('onchange', 'versioning.goDiff(' + (c ? a++ : b++) + ', ' + (c ? 'false' : 'true') + ');');
			elem.removeAttribute('disabled'); // lors d'un F5 c'est utile
			c = !c;
		});

		document.querySelector('table.data tr:last-child input[name="d1"]').setAttribute('disabled', 'disabled');
		document.querySelector('table.data tr:first-child input[name="d2"]').setAttribute('disabled', 'disabled');
		document.querySelector('table.data input[name="d1"]:not([disabled])').checked = true;
		document.querySelector('table.data input[name="d2"]:not([disabled])').checked = true;

		return this.goDiff();
	};

	this.goDiff = function (url, second) {

		var ia = document.querySelector('table.data input[name="d1"]:checked'),
		    ib = document.querySelector('table.data input[name="d2"]:checked'),
		    pa = parseInt(ia.getAttribute('onchange').replace(/\D/g, ''), 10),
		    pb = parseInt(ib.getAttribute('onchange').replace(/\D/g, ''), 10),
		    onclick;

		if (typeof url == 'string') {
			this.loader();
			self.location.href = url;
		}
		else {
			if (second === true) {
				if (pa >= pb) {
					ia = ib.parentNode.parentNode.previousElementSibling.querySelector('input[name="d1"]');
					ia.checked = true;
				}
			}
			else {
				if (pa >= pb) {
					ib = ia.parentNode.parentNode.nextElementSibling.querySelector('input[name="d2"]');
					ib.checked = true;
				}
			}

			onclick = document.querySelector('div.content-header td.form-buttons button').getAttribute('onclick');
			onclick = onclick.replace(/\/from\/[^\/]+/, '/from/' + ib.value);
			onclick = onclick.replace(/\/to\/[^\/]+/, '/to/' + ia.value);

			document.querySelector('div.content-header td.form-buttons button').setAttribute('onclick', onclick);
			document.querySelector('div.content-header-floating td.form-buttons button').setAttribute('onclick', onclick);
		}

		return this;
	};

})();

if (typeof self.addEventListener == 'function')
	self.addEventListener('load', versioning.init.bind(versioning));