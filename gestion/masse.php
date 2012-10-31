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

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	unset($_SESSION["mails_masse"]);

	verif_auth();

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>
<div class='main'>
	<?php
		titre_page_icone("Traitement de décisions et génération de documents en masse", "kpersonalizer_32x32_fond.png", 15, "L");
	?>

	<table align='center'>
	<tr>
		<td class='fond_menu2' style='padding:4px;'>
			<font class='Texte_menu2'><b>Vous souhaitez  ... </b></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu' style='padding-bottom:15px;'>
			<?php
				$txt=(isset($_SESSION["composante_entretiens"]) && $_SESSION["composante_entretiens"]==1) ? " (et les dates d'entretiens)" : "";

				print("<a href='masse_traitement.php' target='_self' class='lien_menu_gauche'>- Saisir des décisions en masse$txt<br>- Consulter vos saisies antérieures<br>- Générer les lettres correspondant à vos saisies en masse</a>\n");
			?>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu' style='padding-bottom:15px;'>
			<a href='masse_recap.php' target='_self' class='lien_menu_gauche'>- Générer les Récapitulatifs de fiches en fonction d'une formation (ou d'une année entière) et d'un intervalle de temps</a>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu' style='padding-bottom:15px;'>
			<a href='masse_formulaire.php' target='_self' class='lien_menu_gauche'>- Générer les Formulaires de Commissions en fonction d'une formation et d'un intervalle de temps</a>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu' style='padding-bottom:15px;'>
			<a href='masse_listes_entretiens.php' target='_self' class='lien_menu_gauche'>- Générer les listes de candidats convoqués aux entretiens, en fonction d'une formation, d'une date et d'une salle</a>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu' style='padding-bottom:15px;'>
			<a href='masse_pdf.php' target='_self' class='lien_menu_gauche'>- Générer les lettres officielles en fonction d'une formation et de la date de saisie de la décision<br>&nbsp;&nbsp;(fonction indépendante de la saisie en masse)</a>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu' style='padding-bottom:15px;'>
			<a href='extractions_csv.php' target='_self' class='lien_menu_gauche'>- Exporter des données brutes au format CSV (pour import dans un tableur)</a>
		</td>
	</tr>
	</table>
</div>
<?php
	pied_de_page();
?>
</body></html>
