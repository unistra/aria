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
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;
/*
	if(!isset($_SESSION["lock"]) || $_SESSION["lock"]==1)
	{
		session_write_close();
		header("Location:precandidatures.php");
		exit();
	}
*/

	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}
	else
		$candidat_id=$_SESSION["authentifie"];

	// récupération des paramètres cryptés passés en GET
	if(array_key_exists("p",$_GET) && -1!=($params=get_params($_GET['p'])))
	{
		// inid
		if(array_key_exists("cand_id",$params) && ctype_digit($params["cand_id"]))
			$cand_id=$params["cand_id"];
		else
		{
			session_write_close();
			header("Location:../index.php");
			exit;
		}

		// si on change l'ordre d'une spécialité au sein d'une candidature à choix multiples ...
		if(array_key_exists("groupe",$params) && ctype_digit($params["groupe"]))
			$groupe=$params["groupe"];

		// Vérification du verrouillage de la formation
		if(isset($_SESSION["array_lock"]) && isset($_SESSION["array_lock"][$cand_id])
			&& $_SESSION["array_lock"][$cand_id]["lock"]==1)
		{
			session_write_close();
			header("Location:precandidatures.php");
			exit();
		}
	}
	else
	{
		session_write_close();
		header("Location:../index.php");
		exit;
	}
	
	$dbr=db_connect();
	
	$result=db_query($dbr,"SELECT $_DBC_cand_ordre, $_DBC_cand_ordre_spec, $_DBC_cand_periode
										FROM $_DB_cand
									WHERE $_DBC_cand_candidat_id='$candidat_id'
									AND $_DBC_cand_id='$cand_id'");
	$rows=db_num_rows($result);

	if($rows)
	{
		list($ordre_actuel,$ordre_spec_actuel, $periode_actuelle)=db_fetch_row($result,0);

		// colonne différente selon qu'on réordonne une candidature ou une spécialité
		if(isset($groupe)) // spécialité
		{
			$ordre_actuel=$ordre_spec_actuel;
			$colonne_ordre="$_DBU_cand_ordre_spec";
			$condition_groupe="AND $_DBU_cand_groupe_spec='$groupe'";
		}
		else
		{
			$colonne_ordre="ordre";
			$condition_groupe="";
		}

		if($ordre_actuel!=1) // si =1, on ne  change rien (test de précaution - 1 = ordre minimal)
		{
			$ordre_cible=$ordre_actuel-1;
			// l'ordre 0 est utilisé comme swap
			db_query($dbr,"UPDATE $_DB_cand SET $colonne_ordre='0'
									WHERE $_DBU_cand_candidat_id='$candidat_id'
									AND $colonne_ordre='$ordre_cible'
									AND $_DBU_cand_periode='$periode_actuelle'
									AND $_DBU_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')
									$condition_groupe;
								UPDATE $_DB_cand SET $colonne_ordre='$ordre_cible'
									WHERE $_DBU_cand_candidat_id='$candidat_id'
									AND $colonne_ordre='$ordre_actuel'
									AND $_DBU_cand_periode='$periode_actuelle'
									AND $_DBU_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')
									$condition_groupe;
								UPDATE $_DB_cand SET $colonne_ordre='$ordre_actuel'
									WHERE $_DBU_cand_candidat_id='$candidat_id'
									AND $colonne_ordre='0'
									AND $_DBU_cand_periode='$periode_actuelle'
									AND $_DBU_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')
									$condition_groupe");
		}
	}
	
	db_free_result($result);
	db_close($dbr);
	
	session_write_close();
	header("Location:precandidatures.php");
	exit;
		
?>

