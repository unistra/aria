<?php
/*
=======================================================================================================
APPLICATION ARIA - UNIVERSITE DE STRASBOURG

LICENCE : CECILL-B
Copyright Universit� de Strasbourg
Contributeur : Christophe Boccheciampe - Janvier 2006
Adresse : cb@dpt-info.u-strasbg.fr

L'application utilise des �l�ments �crits par des tiers, plac�s sous les licences suivantes :

Ic�nes :
- CrystalSVG (http://www.everaldo.com), sous licence LGPL (http://www.gnu.org/licenses/lgpl.html).
- Oxygen (http://oxygen-icons.org) sous licence LGPL-V3
- KDE (http://www.kde.org) sous licence LGPL-V2

Librairie FPDF : http://fpdf.org (licence permissive sans restriction d'usage)

=======================================================================================================
[CECILL-B]

Ce logiciel est un programme informatique permettant � des candidats de d�poser un ou plusieurs
dossiers de candidatures dans une universit�, et aux gestionnaires de cette derni�re de traiter ces
demandes.

Ce logiciel est r�gi par la licence CeCILL-B soumise au droit fran�ais et respectant les principes de
diffusion des logiciels libres. Vous pouvez utiliser, modifier et/ou redistribuer ce programme sous les
conditions de la licence CeCILL-B telle que diffus�e par le CEA, le CNRS et l'INRIA sur le site
"http://www.cecill.info".

En contrepartie de l'accessibilit� au code source et des droits de copie, de modification et de
redistribution accord�s par cette licence, il n'est offert aux utilisateurs qu'une garantie limit�e.
Pour les m�mes raisons, seule une responsabilit� restreinte p�se sur l'auteur du programme, le titulaire
des droits patrimoniaux et les conc�dants successifs.

A cet �gard l'attention de l'utilisateur est attir�e sur les risques associ�s au chargement, �
l'utilisation, � la modification et/ou au d�veloppement et � la reproduction du logiciel par l'utilisateur
�tant donn� sa sp�cificit� de logiciel libre, qui peut le rendre complexe � manipuler et qui le r�serve
donc � des d�veloppeurs et des professionnels avertis poss�dant  des  connaissances informatiques
approfondies. Les utilisateurs sont donc invit�s � charger et tester l'ad�quation du logiciel � leurs
besoins dans des conditions permettant d'assurer la s�curit� de leurs syst�mes et ou de leurs donn�es et,
plus g�n�ralement, � l'utiliser et l'exploiter dans les m�mes conditions de s�curit�.

Le fait que vous puissiez acc�der � cet en-t�te signifie que vous avez pris connaissance de la licence
CeCILL-B, et que vous en avez accept� les termes.

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

	// Ajout d'un s�parateur

	if(isset($_SESSION["info_doc_id"]))
		$info_doc_id=$_SESSION["info_doc_id"];
	else
	{
		header("Location:index.php");
		exit;
	}

	// r�cup�ration de la position
	if(isset($_GET["a"]) && isset($_GET["o"])) // Nouvel �l�ment
	{
		$_SESSION["ordre"]=$ordre=$_GET["o"];
		$_SESSION["ordre_max"]=$_SESSION["cbo"];
	}
	else	// pas de variable position, on sort
	{
		header("Location:index.php");
		exit;
	}

	$dbr=db_connect();

	if($_SESSION["ordre"]!=$_SESSION["ordre_max"])
	{
		// 1 - Reconstruction des �l�ments (comme pour la suppression)
		$a=get_all_elements($dbr, $info_doc_id);
		$nb_elements=count($a);

		for($i=$nb_elements; $i>$_SESSION["ordre"]; $i--)
		{
			$current_ordre=$i-1;
			$new_ordre=$i;
			$current_type=$a["$current_ordre"]["type"]; // le type sert juste � savoir dans quelle table on doit modifier l'�l�ment courant
			$current_id=$a["$current_ordre"]["id"];

			$current_table_name=get_table_name($current_type);
			$col_ordre=$current_table_name["ordre"];
			$col_id=$current_table_name["id"];
			$table=$current_table_name["table"];


			db_query($dbr,"UPDATE $table SET $col_ordre='$new_ordre'
												WHERE $col_id='$current_id'
												AND $col_ordre='$current_ordre'");
		}
	}

	db_query($dbr,"INSERT INTO $_DB_comp_infos_sepa VALUES ('$info_doc_id', '$_SESSION[ordre]')");
	db_close($dbr);

	header("Location:index.php");
	exit;
					
?>
