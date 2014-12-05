<?php
/*
=======================================================================================================
APPLICATION ARIA - UNIVERSITE DE STRASBOURG

LICENCE : CECILL-B
Copyright Université de Strasbourg
Contributeur : Christophe Boccheciampe - Janvier 2006
Adresse : cb@dpt-info.u-strasbg.fr

L'application utilise des éléments écrits par des tiers, placés sous les licences suivantes :

Icônes :
- CrystalSVG (http://www.everaldo.com), sous licence LGPL (http://www.gnu.org/licenses/lgpl.html).
- Oxygen (http://oxygen-icons.org) sous licence LGPL-V3
- KDE (http://www.kde.org) sous licence LGPL-V2

Librairie FPDF : http://fpdf.org (licence permissive sans restriction d'usage)

=======================================================================================================
[CECILL-B]

Ce logiciel est un programme informatique permettant à des candidats de déposer un ou plusieurs
dossiers de candidatures dans une université, et aux gestionnaires de cette dernière de traiter ces
demandes.

Ce logiciel est régi par la licence CeCILL-B soumise au droit français et respectant les principes de
diffusion des logiciels libres. Vous pouvez utiliser, modifier et/ou redistribuer ce programme sous les
conditions de la licence CeCILL-B telle que diffusée par le CEA, le CNRS et l'INRIA sur le site
"http://www.cecill.info".

En contrepartie de l'accessibilité au code source et des droits de copie, de modification et de
redistribution accordés par cette licence, il n'est offert aux utilisateurs qu'une garantie limitée.
Pour les mêmes raisons, seule une responsabilité restreinte pèse sur l'auteur du programme, le titulaire
des droits patrimoniaux et les concédants successifs.

A cet égard l'attention de l'utilisateur est attirée sur les risques associés au chargement, à
l'utilisation, à la modification et/ou au développement et à la reproduction du logiciel par l'utilisateur
étant donné sa spécificité de logiciel libre, qui peut le rendre complexe à manipuler et qui le réserve
donc à des développeurs et des professionnels avertis possédant  des  connaissances informatiques
approfondies. Les utilisateurs sont donc invités à charger et tester l'adéquation du logiciel à leurs
besoins dans des conditions permettant d'assurer la sécurité de leurs systèmes et ou de leurs données et,
plus généralement, à l'utiliser et l'exploiter dans les mêmes conditions de sécurité.

Le fait que vous puissiez accéder à cet en-tête signifie que vous avez pris connaissance de la licence
CeCILL-B, et que vous en avez accepté les termes.

=======================================================================================================
*/
?>
<?php
	session_name("preinsc_gestion");
	session_start();

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	// EN-TETE SIMPLIFIEE (pas de composante, pas de menu, rien
	en_tete_simple();

	// MENU SUPERIEUR SIMPLIFIE
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("[Aide] Traitement de décisions et génération de documents en masse", "help-browser_32x32_fond.png", 15, "L");
	?>
		
	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><b>Fonction principale</b></u></font>
		<p class='Texte' style='padding-bottom:15px;'>
			<b>Effectuer des opérations simples sur un grand nombre de fiches.</b>
		</p>

		<font class='Texte_16'><u><b>Fonctionnalités et options</b></u></font>

		<p class='Texte'>
			<u><b>Saisie des décisions en masse</b></u> : lorsque les formulaires de Commissions sont retournés complétés 
			en scolarité, les décisions doivent être reportées sur l'interface afin de pouvoir poursuivre le traitement des
			candidatures. Lorsque les décisions sont simples (admission, refus avec un motif unique, ...), il est possible de
			les saisir à la chaîne pour une formation donnée.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u>Remarque :</u> cette saisie est plus efficace si les formulaires de Commissions sont préalablement triés par
			formation et par ordre alphabétique des candidats.
		</p>
		<p class='Texte'>
			<u><b>Consultation des saisies antérieures et génération des lettres (format PDF)</b></u> : lorsque vous validez une saisie en
			masse, le traitement est enregistré et un lien est créé sur la page. Ce lien vous permet de générer les lettres
			qui correspondent au traitement que vous venez de valider. Lorsque le nombre de décisions saisies dépasse un certain
			seuil, l'interface crée plusieurs liens afin de réduire le délai de génération des documents. Il faudra donc cliquer
			sur chaque lien "Partie 1", "Partie 2" , ..., "Partie N" pour obtenir tous les documents correspondants.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u>Remarque :</u> Lorsqu'une fiche est traitée plusieurs fois (par exemple : si la première décision est "Convoqué
			à l'entretien", et la seconde "Admis"), seul le <b>dernier traitement</b> est conservé dans l'historique des saisies
			en masse, ceci empêche donc de générer un nouveau courrier pour une décision obsolète.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u><b>Génération des Récapitulatifs (format PDF)</b></u> : certains candidats oublient de joindre le récapitulatif de leurs
			fiches au reste de leurs justificatifs. Cette fonctionnalité vous permet de générer ces documents en masse, en
			fonction d'une formation et d'un intervalle de dates.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u><b>Génération des Formulaires de Commissions (format PDF)</b></u> : les formulaires de Commissions doivent être
			générés avant les Commissions Pédagogiques : ils sont utilisés par leurs membres pour y écrire la décision (cases
			à cocher) avec le(s) motif(s) appropriés (motifs prédéfinis et/ou motifs libres si besoin). Ces formulaires doivent
			ensuite être retournés à la scolarité pour que les décisions soient reportées sur l'interface.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u><b>Génération d'une liste de passage à un entretien (format PDF)</b></u> : lorsque les candidats sont convoqués
			à un entretien complémentaire (avant décision finale de la Commission Pédagogique), il peut être utile de générer
			les listes de passage en fonction de la date et de la salle utilisée. Ces listes peuvent ensuite être collées aux
			portes de ces salles ou sur le tableau d'affichage destiné aux étudiants, par exemple.
		</p>
		<p class='Texte'>
			<u><b>Génération des lettres de décisions (format PDF)</b></u> : une fois les décisions saisies (via les saisies en
			masse ou individuelles), vous pouvez générer toutes les lettres relatives à ces dernières.
		</p>
		<p class='Texte'>
			<u>Remarque 1 :</u> vérifiez bien qu'au moins une lettre est associée à chaque décision et formation (dans l'Editeur
			de lettres). Si aucun document n'est relié, rien ne sera généré.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u>Remarque 2 :</u> plusieurs lettres peuvent être générées pour une même décision (par exemple : une lettre officielle
			d'admission peut être couplée aux modalités d'inscription administrative ainsi qu'à une note d'information sur la
			date et l'heure d'une éventuelle réunion de rentrée).
		</p>
		<p class='Texte'>
			<u><b>Export de données au format brut (format CSV)</b></u> : une fois les décisions saisies et les courriers envoyés,
			la scolarité peut avoir besoin de récupérer la liste des candidats admis pour efectuer d'autres traitements complémentaires
			non prévus par l'interface. L'interface offre la possibilité d'extraire certaines données dans un fichier au format
			"CSV" ("Comma Separated Values", i.e "valeurs séparées par une virgule"). Une fois les données à extraire sélectionnées,
			il suffit de télécharger le fichier et l'ouvrir à l'aide d'un tableur (OpenOffice, Microsoft Excel, ...) pour les
			manipuler.
		</p>
		<p class='Texte'>
			<u>Remarque 1 :</u> pour ces fichiers CSV, le séparateur de champs à indiquer au tableur est <b>le point virgule</b>.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u>Remarque 2 :</u></font> <font class='Texte_important'><b>ces données doivent être extraites à des fins pédagogiques
			uniquement. Leur exploitation est règlementée (ce sont des données nominatives et personnelles), <b>votre responsabilité
			peut donc être engagée</b>.</font>
		</p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
