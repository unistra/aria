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
	// Consultation d'une pièce jointe (pertinent pour la partie gestion, alors que les candidats
	//	ne peuvent pas en joindre ?)
	session_name("preinsc_gestion");
	session_start();

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth();

	if(!isset($_SESSION["current_dossier"]))
	{
		header("Location:index.php");
		exit();
	}
	else
		$current_dossier=$_SESSION["current_dossier"];

	$dbr=db_connect();

	if(isset($_GET["p"]) && isset($_SESSION["msg_dir"]) && -1!=($params=get_params($_GET['p']))) // chemin complet du message, chiffré
	{
		if(isset($params["pj"]) && is_file("$_SESSION[msg_dir]/files/$params[pj]"))
		{
			if(!is_dir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$_SESSION[auth_id]"))
				mkdir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$_SESSION[auth_id]", 0770, true);
			else
			{
				// Nettoyage puis recréation (évite le cumul des fichiers)
				deltree("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$_SESSION[auth_id]");

				mkdir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$_SESSION[auth_id]", 0770, true);
			}

			copy("$_SESSION[msg_dir]/files/$params[pj]", "$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$_SESSION[auth_id]/$params[pj]");

			echo "<HTML><SCRIPT>document.location='$__GESTION_COMP_STOCKAGE_DIR/$_SESSION[comp_id]/$_SESSION[auth_id]/$params[pj]';</SCRIPT></HTML>";
		}
	}
?>
