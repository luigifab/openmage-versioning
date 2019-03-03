/**
 * Created J/22/12/2011
 * Updated V/01/03/2019
 *
 * Copyright 2011-2019 | Fabrice Creuzot (luigifab) <code~luigifab~fr>
 * https://www.luigifab.fr/magento/versioning
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

var versioning = {

	svg: null,
	width: 197,

	// initialisation
	start: function () {

		if (document.querySelector('body[class*="adminhtml-versioning-repository-"]')) {

			console.info('versioning.app - hello');

			if (document.getElementById('versioning_history_grid') && document.querySelector('table.data tbody button'))
				document.querySelector('table.data tbody button').click();

			if (document.getElementById('versioning_grid_table') && (typeof self.versioningIds === 'object'))
				versioning.drawGraph(self.versioningIds, self.versioningCols).initDiff();
		}
	},

	enableLoader: function () {
		document.body.style.cursor = 'progress';
		document.querySelector('div.content-header td.form-buttons').setAttribute('style', 'visibility:hidden;');
		document.querySelector('div.content-header-floating td.form-buttons').setAttribute('style', 'visibility:hidden;');
	},

	decode: function (data) {
		// prise en charge de l'utf-8 avec Webkit - https://stackoverflow.com/q/3626183
		return decodeURIComponent(escape(self.atob(data)));
	},


	// #### Confirmation des pages de maintenance ############################### //
	// = révision : 25
	// » Demande confirmation avec ou sans l'apijs mais avec les mêmes informations
	// » Pour la désactivation redirige simplement sur l'action
	confirmFlag: function (url, title, content) {

		try {
			// avec l'apijs
			// utilise une jolie boîte de dialogue
			if (apijs.version < 530)
				throw new Error('Invalid apijs version');

			apijs.dialog.dialogConfirmation(
				title, // title
				this.decode(content), // text
				versioning.actionConfirmFlag, // callback
				url,   // args
				'notransition versioning ' + document.getElementById('scmtype').textContent // icon
			);

			var elem = document.createElement('p');
			elem.setAttribute('class', 'credits');
			elem.appendChild(document.createTextNode(self.versioningConfirm[1]));
			apijs.dialog.t1.appendChild(elem);
		}
		catch (e) {
			console.log(e);

			try {
				// sans l'apijs
				// demande de confirmation
				if (confirm(this.decode(content).replace(/\[[^\]]+]/g, ''))) {
					this.enableLoader();
					location.href = url;
				}
			}
			catch (ee) {
				console.log(ee);

				// en dernier recours
				// demande de confirmation
				if (confirm(Translator.translate('Are you sure?'))) {
					this.enableLoader();
					location.href = url;
				}
			}
		}
	},

	actionConfirmFlag: function (url) {
		versioning.enableLoader();
		apijs.dialog.styles.remove('waiting', 'lock'); // obligatoire sinon il y a une demande de confirmation de quitter la page
		location.href = url;
	},

	cancelFlag: function (url) {
		this.enableLoader();
		location.href = url;
	},


	// #### Confirmation de mise à jour ######################################### //
	// = révision : 45
	// » Demande confirmation avec ou sans l'apijs mais avec les mêmes informations
	// » Génère une boîte de dialogue si l'apijs n'est pas disponible
	confirmUpgrade: function (url, title) {

		var content = self.versioningConfirm[0], credits = self.versioningConfirm[1];

		try {
			// avec l'apijs
			// utilise une jolie boîte de dialogue
			if (apijs.version < 530)
				throw new Error('Invalid apijs version');

			url.match(/revision\/(\w+)\//);
			apijs.dialog.dialogFormOptions(
				title.replace('§', RegExp.$1), // title
				this.decode(content), // text
				url,  // action
				versioning.actionConfirmUpgrade, // callback (en deux temps, vérification puis redirection)
				null, // args
				'notransition versioning ' + document.getElementById('scmtype').textContent // icon
			);

			var elem = document.createElement('p');
			elem.setAttribute('class', 'credits');
			elem.appendChild(document.createTextNode(credits));
			apijs.dialog.t1.appendChild(elem);

			return false;
		}
		catch (e) {
			console.log(e);
			try { apijs.dialog.actionClose(); } catch (ee) { }

			try {
				// sans l'apijs
				// simule la boîte de dialogue de l'apijs
				var data = document.createElement('div'),
				    text = this.decode(content).replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\[/g, '<').replace(/]/g, '>'),
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
						'<p class="credits">' + credits + '</p>' +
					'</div>';

				document.body.appendChild(data);
				document.querySelector('div.bbcode input').focus();

				return false;
			}
			catch (ee) {
				console.log(ee);

				// en dernier recours
				// demande de confirmation
				return confirm(Translator.translate('Are you sure?'));
			}
		}
	},

	closeConfirmUpgrade: function () {
		document.getElementById('apijsDialog').parentNode.removeChild(document.getElementById('apijsDialog'));
	},

	actionConfirmUpgrade: function (action) {

		// avec l'apijs, en deux temps
		// validation du formulaire si la fonction callback avec son paramètre args renvoie true, callback(false, args)
		// appelle la fonction callback avec ses paramètres action et args après la validation du dialogue, callback(action, args)
		if (action === false)
			return true;

		versioning.enableLoader();

		// sans l'apijs
		if (action === true) {
			document.querySelector('div.bbcode').setAttribute('style', 'visibility:hidden;');
			document.querySelector('div.control').setAttribute('style', 'visibility:hidden;');
		}
		// avec l'apijs, en deux temps
		else {
			apijs.dialog.styles.remove('waiting', 'lock'); // obligatoire sinon il y a une demande de confirmation de quitter la page
			location.href = action + apijs.serialize(document.getElementById('apijsBox')).replace(/[=&]/g, '/');
		}

		return true;
	},


	// #### Affichage de l'historique ########################################### //
	// = révision : 11
	// » Affiche les détails d'une mise à jour dans la balise pre
	// » Marque la ligne active du tableau avec la classe current
	history: function (link, content) {

		var elem, elems = document.querySelectorAll('table.data tbody tr');
		for (elem in elems) if (elems.hasOwnProperty(elem) && !isNaN(elem)) {
			elem = elems[elem];
			if (elem.hasAttribute('class'))
				elem.setAttribute('class', elem.getAttribute('class').replace(/ ?current/, ''));
		}

		elem = link.parentNode.parentNode;
		elem.setAttribute('class', ((elem.hasAttribute('class')) ? elem.getAttribute('class') : '') + ' current');

		document.querySelector('pre').innerHTML = this.decode(content) + "\n\n";
		return false;
	},


	// #### Représentation des branches ######################################### //
	// = révision : 134
	// » Utilise Raphael.js 2.2.7 (93,5 ko) pour la création de l'image SVG - https://github.com/DmitryBaranovskiy/raphael
	// » Utilise la fonction innerSVG (1,4 ko) pour l'ajout des dégradés - https://code.google.com/p/innersvg/
	// » Pour chaque commit crée un point éventuellement suivi d'une étiquette avec le nom de la branche
	// » Crée ensuite les lignes entres les points sans utiliser les trucs dépréciés de prototype
	drawGraph: function (data, cols) {

		var elem, x, y, pX, pY, gradients = '', that = versioning,
			commitsHash  = new Hash(data),
			commitsArray = commitsHash.values(),
			tableRows    = $$('table.data tbody tr'), rows = tableRows.length - 1, // les lignes du tableau
			colors = [], styles = [], names = [], tops = [], bottoms = [],
			grad = 0, offsetTop = 0, graphHeight = 0, topPoint = 0, miHeight = 0, dMiHeight = 0;

		// https://stackoverflow.com/a/1129270
		// inverse l'ordre du tableau
		commitsArray.sort(function (a, b) {
			if (a.row > b.row)
				return -1;
			if (a.row < b.row)
				return 1;
			return 0;
		});

		// recheche de la hauteur et de la position du graphique (avec Prototype > 1.7 ou non)
		// offsetTop = la position du haut du graphique (à partir du haut du document)
		//  topPoint = la position du haut de la première ligne dans le tableau (à partir du haut du document)
		if (typeof Element.Layout === 'function') {
			offsetTop   = tableRows.first().getLayout().get('top') - 1;
			graphHeight = tableRows.last().getLayout().get('top') + tableRows.last().getLayout().get('height') - offsetTop - 1;
		}
		else {
			offsetTop   = Element.positionedOffset(tableRows.first())[1] - 1;
			graphHeight = Element.positionedOffset(tableRows.last())[1] + tableRows.last().getDimensions().height - offsetTop - 1;
		}

		// initialisation du graphique
		// canvas = l'élément svg
		that.svg = new Raphael(document.getElementById('versioning_grid_table').parentNode);
		that.svg.setSize(that.width, graphHeight);
		that.svg.canvas.setAttribute('style', 'top:' + offsetTop + 'px;');
		that.svg.canvas.setAttribute('class', 'k k0');
		that.svg.canvas.setAttribute('id', 'versioning_graph');
		that.svg.canvas.setAttribute('onmouseover', 'versioning.mouseOver(true);');
		that.svg.canvas.parentNode.setAttribute('onmouseleave', 'versioning.mouseOver(false);');

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

		commitsArray.each(function (commit) {

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
		commitsArray.each(function (commit) {

			// recherche de la position du point
			// avec Prototype > 1.7 ou non
			if (typeof Element.Layout === 'function') {
				topPoint = tableRows[rows - commit.row].getLayout().get('top') - offsetTop;
				miHeight = tableRows[rows - commit.row].getLayout().get('height') / 2;
			}
			else {
				topPoint = Element.positionedOffset(tableRows[rows - commit.row])[1] - offsetTop;
				miHeight = tableRows[rows - commit.row].getDimensions().height / 2;
			}

			// sur X (position horizontale) le  25 correspond à l'espace entre les colonnes, donc entre deux branches
			// sur X (position horizontale) le +20 permet de ne pas coller la première branche au bord
			y = topPoint + miHeight;
			x = 25 * commit.col + 20;

			// dessine un point
			that.svg.circle(x, y, 3.5)
				.attr('fill', commit.color)
				.attr('stroke', 'none')
				.attr('class', commit.klass);

			// écrit un texte dans une étiquette
			// en profite également pour vérifier la largeur du graphique
			if ((commit.branch.length > 0) && (names.indexOf(commit.branch) < 0)) {

				names.push(commit.branch);

				elem = that.svg.text(x + 13, y - 0.3, commit.branch)
					.attr('fill', commit.color)
					.attr('text-anchor', 'start')
					.attr('class', commit.klass);

				pX = x + 3.2 + 8 + elem.getBBox().width + 7;       // variable temporaire
				that.svg.path(
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

				if (pX > versioning.width)
					versioning.width = pX;
			}

			// ligne vers le parent (donc un peu plus bas)
			// s'il existe, sinon vers le bas du graphique
			commit.parents.each(function (ref, parent) {

				parent = (ref.length > 0) ? commitsHash.get(ref) : undefined;

				if (typeof parent === 'object') {

					// recherche de la position du point
					// avec Prototype > 1.7 ou non
					if (typeof Element.Layout === 'function') {
						topPoint = tableRows[rows - parent.row].getLayout().get('top') - offsetTop;
						miHeight = tableRows[rows - parent.row].getLayout().get('height') / 2;
					}
					else {
						topPoint = Element.positionedOffset(tableRows[rows - parent.row])[1] - offsetTop;
						miHeight = tableRows[rows - parent.row].getDimensions().height / 2;
					}

					// sur X (position horizontale) le 25 correspond à l'espace entre les colonnes, donc entre deux branches
					// sur X (position horizontale) le +20 permet de ne pas coller la première branche au bord
					pY = topPoint + miHeight;
					pX = 25 * parent.col + 20;

					if (parent.col === commit.col) {
						// dessine une ligne verticale
						that.svg.path(['M', x, y, 'V', pY])
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

						// avec Prototype > 1.7 ou non
						if (typeof Element.Layout === 'function')
							dMiHeight = tableRows[rows - parent.row].getLayout().get('height') / 2;
						else
							dMiHeight = tableRows[rows - parent.row].getDimensions().height / 2;
						dMiHeight += miHeight;

						if ((parent.revision === tops[parent.col]) && (y + dMiHeight < pY)) {
							// dessine une ligne en travers
							elem = that.svg.path(['M', x, y, 'T', pX, y + dMiHeight]);
							elem.node.setAttribute('stroke', 'url(#manGrad' + grad + ')');
							elem.attr('stroke-width', 1.6).attr('class', 'k').toBack();
							// dessise une ligne verticale
							that.svg.path(['M', pX, y + dMiHeight, 'V', pY])
								.attr('stroke', parent.color)
								.attr('stroke-width', 1.7)
								.attr('class', 'k')
								.toBack();
						}
						else if ((commit.revision === bottoms[commit.col]) && (pY > y + dMiHeight) && (commit.parents.length === 1)) {
							// dessise une ligne verticale
							that.svg.path(['M', x, y, 'V', pY - dMiHeight])
								.attr('stroke', commit.color)
								.attr('stroke-width', 1.7)
								.attr('class', 'k')
								.toBack();
							// dessine une ligne en travers
							elem = that.svg.path(['M', x, pY - dMiHeight, 'T', pX, pY]);
							elem.node.setAttribute('stroke', 'url(#manGrad' + grad + ')');
							elem.attr('stroke-width', 1.6).attr('class', 'k').toBack();
						}
						else {
							// dessine une ligne en travers
							elem = that.svg.path(['M', x, y, 'T', pX, pY]);
							elem.node.setAttribute('stroke', 'url(#manGrad' + grad + ')');
							elem.attr('stroke-width', 1.6).attr('class', 'k').toBack();
						}

						grad += 1;
					}
				}
				else if (ref.length > 0) {
					// dessine une ligne verticale vers le bas du graphique
					that.svg.path(['M', x, y, 'V', graphHeight])
						.attr('stroke', commit.color)
						.attr('stroke-width', 1.7)
						.attr('class', commit.klass)
						.toBack();
				}
			});

			elem = tableRows[rows - commit.row];
			elem.setAttribute('onclick', "versioning.updateClass(this.getAttribute('class'));");
			elem.setAttribute('class', ((commit.row % 2) < 1) ? commit.klass : 'even ' + commit.klass);
			elem.removeAttribute('title');
		});

		// une seule fois sinon ok que pour le dernier ajout avec Edge 14
		if (gradients.length > 0)
			document.querySelector('svg defs').innerSVG = gradients;

		// ajoute les styles pour les animations
		elem = document.createElement('style');
		elem.setAttribute('type', 'text/css');
		elem.setAttribute('id', 'versioning_styles');
		elem.appendChild(document.createTextNode(styles.join("\n")));
		document.querySelector('head').appendChild(elem);

		return that;
	},

	mouseOver: function (yes) {
		this.svg.canvas.style.width = (yes) ? versioning.width + 'px' : '197px';
		this.svg.canvas.style.pointerEvents = (yes) ? 'none' : 'inherit';
	},

	updateClass: function (klass) {
		this.svg.canvas.setAttribute('class', klass);
		document.getElementById('versioning_grid_table').setAttribute('class', 'data ' + klass);
	},


	// #### Gestion des cases du diff ########################################### //
	// = révision : 14
	// » Gère l'activation du lien vers la page du diff
	// » Active automatiquement les premières cases
	initDiff: function () {

		var elem, elems = document.querySelectorAll('table.data input[type="radio"]'), d1 = 1, d2 = 1, bis = true;
		for (elem in elems) if (elems.hasOwnProperty(elem) && !isNaN(elem)) {
			elems[elem].setAttribute('onchange', 'versioning.goDiff(' + (bis ? (d1++) : (d2++)) + ', ' + (bis ? 'false' : 'true') + ');');
			elems[elem].removeAttribute('disabled'); // lors d'un F5 c'est utile
			bis = !bis;
		}

		document.querySelector('table.data tr:last-child input[name="d1"]').setAttribute('disabled', 'disabled');
		document.querySelector('table.data tr:first-child input[name="d2"]').setAttribute('disabled', 'disabled');
		document.querySelector('table.data input[name="d1"]:not([disabled])').checked = true;
		document.querySelector('table.data input[name="d2"]:not([disabled])').checked = true;

		this.goDiff();
		return this;
	},

	goDiff: function (url, two) {

		var d1 = document.querySelector('table.data input[name="d1"]:checked'),
		    d2 = document.querySelector('table.data input[name="d2"]:checked'),
		    pos1 = parseInt(d1.getAttribute('onchange').replace(/\D/g, ''), 10),
		    pos2 = parseInt(d2.getAttribute('onchange').replace(/\D/g, ''), 10),
		    onclick;

		if (typeof url === 'string') {
			this.enableLoader();
			location.href = url;
		}
		else {
			if (two === true) {
				if (pos1 >= pos2) {
					d1 = d2.parentNode.parentNode.previousElementSibling.querySelector('input[name="d1"]');
					d1.checked = true;
				}
			}
			else {
				if (pos1 >= pos2) {
					d2 = d1.parentNode.parentNode.nextElementSibling.querySelector('input[name="d2"]');
					d2.checked = true;
				}
			}

			onclick = document.querySelector('div.content-header td.form-buttons button').getAttribute('onclick');
			onclick = onclick.replace(/\/from\/[^\/]+/, '/from/' + d2.value);
			onclick = onclick.replace(/\/to\/[^\/]+/, '/to/' + d1.value);

			document.querySelector('div.content-header td.form-buttons button').setAttribute('onclick', onclick);
			document.querySelector('div.content-header-floating td.form-buttons button').setAttribute('onclick', onclick);
		}
	}
};

if (typeof self.addEventListener === 'function')
	self.addEventListener('load', versioning.start, false);