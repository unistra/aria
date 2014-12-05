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

	verif_auth("$__GESTION_DIR/login.php");

	if(!isset($_SESSION["current_dossier"]))
	{
		header("Location:index.php");
		exit();
	}

	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p']))) // identifiant du message en paramètre crypté
	{
		if(isset($params["msg"]))
		{
			$file=$params["msg"];

			if(is_file("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$file"))
			{
				if($_SESSION["current_dossier"]!=$__MSG_TRASH) // déplacement vers le dossier "Corbeille"
				{
					if(!is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRASH"))
					{
						if(FALSE==mkdir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRASH", 0770, TRUE))
						{
							mail($__EMAIL_ADMIN, "[Précandidatures] - Erreur de création de répertoire", "Répertoire : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRASH\n\nUtilisateur : $_SESSION[auth_nom] $_SESSION[auth_prenom]");
							die("Erreur système lors de la création d'un répertoire. Un message a été envoyé à l'administrateur.");
						}
					}

					rename("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$file", "$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRASH/$file");
				}
				else // Suppression complète
					@unlink("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$file");
			}
		}
	}

	header("Location:index.php");
	exit();

?>
