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
	session_name("preinsc");
	session_start();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/db.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	en_tete_candidat_simple();
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("Conditions d'utilisation de l'interface", "", 15, "C");
	?>

	<div style='width:85%; margin-left:auto; margin-right:auto; white-space:nowrap;'>
		<div>
			<font class='Texte3'><strong><u>Cette interface permet :</u></strong></font>
			<ul class='Texte'>
				<li>d'effectuer une ou plusieurs précandidatures dans cette université</li>
				<li>d'accélérer le traitement de votre candidature</li>
				<li>de modifier votre fiche jusqu'à 48h après avoir saisi votre premier voeu</li>
				<li>de suivre l'évolution de vos demandes même après verrouillage de votre fiche</li>
			</ul>
		</div>
		<div>
			<font class='Texte3'><strong><u>Cependant, cette interface :</u></strong></font>
			<ul class='Texte'>
				<li>ne donne pas accès à toutes les formations pour certaines composantes (dossiers papier uniquement)</li>
				<li>est inutile pour un accès de plein droit à une formation (pas de commission pédagogique)</li>
				<li>ne garantit <font class='Texte_important'><strong>EN AUCUN CAS</strong></font> une admission dans quelque formation que ce soit</li>
				<li>ne vous dispense pas de fournir les justificatifs de votre cursus et autres pièces par <strong>voie postale</strong>.
			</ul>
		</div>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>

</body>
</html>

