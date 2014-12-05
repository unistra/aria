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
		titre_page_icone("[Aide] Afficher toutes les fiches des candidats", "help-browser_32x32_fond.png", 15, "L");
	?>

	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><b>Fonction principale</b></u></font>
		<p class='Texte'>
			<b>Afficher tous les candidats ayant déposé au moins un voeu dans votre établissement.</b>
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			Tous les candidats sont listés, quelque soit l'état du verrouillage de leurs voeux. Vous pouvez accéder
			à n'importe quelle fiche en cliquant sur le nom du candidat.
		</p>

		<font class='Texte_16'><u><b>Fonctionnalités et options</b></u></font>

		<p class='Texte'>
			<u><b>Fiches orphelines</b></u> : il arrive que certains candidats ne sélectionnent aucune formation, quelque
			soit l'établissement proposé. On parle alors de <b>fiches orphelines</b> : elles "n'appartiennent" à aucun
			établissement.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			Pour voir ces fiches, cliquez sur "Montrer également ces fiches orphelines". Attention : lorsque ces fiches sont
			nombreuses, l'affichage peut prendre quelques minutes.
		</p>
		<p class='Texte'>
			<u><b>Filtre des fiches</b></u> : si vous souhaitez afficher temporairement les candidats à une seule formation,
			sélectionnez cette dernière dans le menu déroulant, puis validez. Si vous souhaitez que ce filtre soit actif
			lors de vos	connexions suivantes, cliquez sur "Définir ce filtre par défaut".
		</p>
		<p class='Texte'>
			Pour annuler le filtre, sélectionnez "Montrer toutes les formations" dans la liste, puis validez de nouveau.
		</p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
