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

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	// Données du configurator

	// Titre principal de la page
	$titre_page="Candidatures - Gestion de la liste des motifs de refus et de mises en attente";
/*
	// table concernée
	$nom_table="motifs_refus";
	
	// description de la table
	// la numérotation des colonnes commence à 1
	// TODO : intégrer la pkey dans la description des colonnes ?
	$table=array("pkey" => "id",
								"selection" => "motif",
								"colonnes" => array("1" => "motif"));

	// ordre d'affichage des champs de la table (syntaxe SQL : colonneX [asc|desc], colonneY [asc|desc] ...)
	$order_by="motif";

	// unicité pour certaines colonnes
	$unique=array("motif");
*/
	
	$table=array(	"nom" => "motifs_refus",
									"pkey" => "id",
									"selection" => "motif",
									"order" => "motif",
									"colonnes" => array(	"motif" => array(	"nom_complet" => "Intitulé court",
																						"unique" => "0",
																						"not_null" => "1" ),

																"motif_long" => array("nom_complet" => "Intitulé long (phrase complète)",
																							 "unique" => "0",
																							 "not_null" => "0",
																							 "type" => "textarea"),

																"exclusif" => array("nom_complet" => "Motif exclusif ?",
																						  "unique" => "0",
																						  "not_null" => "0",
																						  "type" => "ouinon")
															 )
					);

	$condition_composante=1;

	$warning="Attention, la modification/suppression d'un motif de refus se répercutera sur TOUS les dossiers de candidatures déjà traités.";

	include "configurator.php";
?>
