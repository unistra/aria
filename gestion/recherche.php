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
	unset($_SESSION["from"]);

	verif_auth();

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();

	// Nettoyages des variables inutiles
	unset($_SESSION["checked_message"]);
	unset($_SESSION["requete"]);
?>
<div class='main'>
	<?php
		titre_page_icone("Recherches diverses", "xmag_32x32_fond.png", 30, "L");

		if(isset($_GET["s"]) && ctype_digit($_GET["s"]))
		{
			if($_GET["s"]==0)
				$message="Aucun message envoyé";
			elseif($_GET["s"]==1)
				$message="1 message envoyé avec succès";
			elseif($_GET["s"]>1)
				$message="$_GET[s] messages envoyés avec succès";

			message($message, $__INFO);
		}
	?>

	<table align='center' style='padding-bottom:100px;'>
	<tr>
		<td class='td-complet fond_menu2' style='padding:4px;'>
			<font class='Texte_menu2'><b>Vous souhaitez chercher ... </b></font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<a href='recherche_nominative.php' target='_self' class='lien_menu_gauche'>&#8226;&nbsp;&nbsp;Des candidats, par nom ou par courriel</a>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<a href='recherche_generale.php' target='_self' class='lien_menu_gauche'>&#8226;&nbsp;&nbsp;Des candidats, par formations, statuts des fiches, ...</a>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<a href='recherche_formation.php' target='_self' class='lien_menu_gauche'>&#8226;&nbsp;&nbsp;Des formations, par intitulé ou par mention.</a>
		</td>
	</tr>
	</table>
</div>
<?php
	pied_de_page();
?>
</body></html>
