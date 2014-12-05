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

	verif_auth("$__GESTION_DIR/login.php");

	// Déplacement des éléments
	// Arguments :
	// - co= ordre de l'objet à déplacer
	// - dir : direction (haut / bas)

	if(isset($_GET["dir"]) && ($_GET["dir"]==0 || $_GET["dir"]==1) && isset($_GET["co"]) && ctype_digit($_GET["co"]))
	{
		$dir=$_GET["dir"];
		$co=$_GET["co"];
	}
	else
	{
		// il manque des arguments : retour à l'index
		header("Location:index.php");
		exit;
	}

	$dbr=db_connect();

	if(isset($_SESSION["filtre_dossier"]) && $_SESSION["filtre_dossier"]!="-1")
		$propspec_id=$_SESSION["filtre_dossier"];
	elseif(isset($_GET["pid"]) && ctype_digit($_GET["pid"])
			 && db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_id='$_GET[pid]'
														AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'")))
		$propspec_id=$_GET["pid"];

	if(!isset($propspec_id))
	{
		// il manque des arguments : retour à l'index
		db_close($dbr);
		header("Location:index.php");
		exit;
	}

	if($dir==0)
		$ordre_cible=$co-1;
	else
		$ordre_cible=$co+1;

	$temp_ordre=rand(100, 32000);

	// var temporaire (contrainte sur id+ordre dans la table) puis échange

	db_query($dbr,"UPDATE $_DB_dossiers_ef SET $_DBU_dossiers_ef_ordre='$temp_ordre'
							WHERE $_DBU_dossiers_ef_propspec_id='$propspec_id'
							AND 	$_DBU_dossiers_ef_ordre='$ordre_cible';

						UPDATE $_DB_dossiers_ef SET $_DBU_dossiers_ef_ordre='$ordre_cible'
							WHERE $_DBU_dossiers_ef_propspec_id='$propspec_id'
							AND $_DBU_dossiers_ef_ordre='$co';

						UPDATE $_DB_dossiers_ef SET $_DBU_dossiers_ef_ordre='$co'
							WHERE $_DBU_dossiers_ef_propspec_id='$propspec_id'
							AND $_DBU_dossiers_ef_ordre='$temp_ordre'");

	db_close($dbr);
	
	header("Location:index.php");
	exit;
	
?>
