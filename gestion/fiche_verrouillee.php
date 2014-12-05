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

	verif_auth("$__GESTION_DIR/login.php");

	// identifiant de l'étudiant
	$candidat_id=$_SESSION["candidat_id"];

	$dbr=db_connect();
	$result=db_query($dbr, "SELECT $_DBC_candidat_lockdate FROM $_DB_candidat WHERE $_DBC_candidat_id='$candidat_id'");

	if(!db_num_rows($result))
	{
		db_close($dbr);
		header("Location:edit_candidature.php");
		exit();
	}

	list($date)=db_fetch_row($result,0);

	$temps_restant=$date-time();

	if($temps_restant>0)
	{
		if($temps_restant>60)
			$temps=floor($temps_restant/60) . " minutes et " . ($temps_restant%60) . " secondes";
		else
			$temps="$temps_restant secondes";
	}
	else
	{
		db_close($dbr);
		header("Location:edit_candidature.php");
		exit();
	}

	db_close($dbr);

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>
<div class='main'>
	<?php
		titre_page_icone("La fiche de ce candidat est verrouillée par un autre utilisateur", "encrypted_32x32_fond.png", 30, "L");
	?>

	<div class='centered_box'>
		<font class='Texte3'>
			Elle sera déverrouillée au plus tard dans : <?php echo "$temps"; ?> si cet utilisateur ne la déverrouille pas avant.
		</font>
		<br><br>
		<a href='edit_candidature.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
	</div>

</div>
<?php
	pied_de_page();
?>
</body></html>
