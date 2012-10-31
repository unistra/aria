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

	include "../../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";
	include "include/editeur_fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("../../login.php");

	// Déplacement des éléments
	// Arguments :
	// - cid=current_id : id de l'objet à déplacer
	// - ct=current_type : type de l'objet à déplacer
	// - tid=target_id : id de l'objet suivant (ou précédent) avec lequel on va échanger l'ordre
	// - tt=current_type : type de l'objet suivant (ou précédent) avec lequel on va échanger l'ordre
	
	if(isset($_GET["co"]) && isset($_GET["ct"]) && isset($_GET["dir"]) && isset($_GET["tt"]) && isset($_SESSION["lettre_id"]))
	{
		$lettre_id=$_SESSION["lettre_id"];
		
		// ordre de l'élément à déplacer
		$co=$_GET["co"];
			
		// type de l'élément à déplacer
		$ct=$_GET["ct"];
			
		// type de l'objet suivant/précédent
		$tt=$_GET["tt"];

		// direction du déplacement (0:vers le haut, 1:vers le bas)
		$dir=$_GET["dir"];
	}
	else
	{
		header("Location:index.php");
		exit;
	}
	
	if(!isset($co) || !isset($ct) || !isset($tt) || !isset($dir) || $ct<0 || $ct>8 || $tt<0 || $tt>8)
	{
		// il manque des arguments : retour à l'index
		header("Location:index.php");
		exit;
	}
	
	// détermine le nom de la table des éléments source &destination en fonction du type de chacun
	$current_table_name=get_table_name($ct);
	$current_table=$current_table_name["table"];
	$current_table_ordre=$current_table_name["ordre"];
	$current_table_id=$current_table_name["id"];

	$target_table_name=get_table_name($tt);
	$target_table=$target_table_name["table"];
	$target_table_ordre=$target_table_name["ordre"];
	$target_table_id=$target_table_name["id"];

	$dbr=db_connect();
	
	if($dir==0)
		$ordre_cible=$co-1;
	else
		$ordre_cible=$co+1;

	$temp_ordre=time();

	// var temporaire (contrainte sur id+ordre dans la table) puis échange
	db_query($dbr,"	UPDATE $target_table SET $target_table_ordre='$temp_ordre'
											WHERE $target_table_id='$lettre_id'
											AND $target_table_ordre='$ordre_cible';
										UPDATE $current_table SET $current_table_ordre='$ordre_cible'
											WHERE $current_table_id='$lettre_id'
											AND $current_table_ordre='$co';
										UPDATE $target_table SET $target_table_ordre='$co'
											WHERE $target_table_id='$lettre_id'
											AND $target_table_ordre='$temp_ordre'");

	db_close($dbr);
	
	header("Location:editeur.php");
	exit;
	
?>
