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
	$titre_page="Candidatures - Gestion de la liste des spécialités";

	// V2
	$table=array("nom" => "$_DB_specs",
					 "pkey" => "$_DBU_specs_id",
					 "selection" => "$_DBU_specs_nom",
					 "order" => "$_DBU_specs_nom",
					 "separateur" => array("colonne" => "$_DBU_specs_mention_id",
												  "reference" => array(	"table" => "$_DB_mentions",
																				"key" => "$_DBU_mentions_id",
																				"texte" => "$_DBU_mentions_nom" )),

					 "colonnes" => array("$_DBU_specs_nom" => array("nom_complet" => "Intitulé de la spécialité",	// colonne 1
																					"unique" => "0",
																					"not_null" => "1" ),

												"$_DBU_specs_nom_court" => array("nom_complet" => "Intitulé court",	// colonne 2
																							"unique" => "0",
																							"not_null" => "1" ),

												"$_DBU_specs_mention_id" => array("nom_complet" => "Type de spécialité",	// colonne 3
																					 "unique" => "0",
																					 "not_null" => "1",
																					 "reference" => array("table" => "$_DB_mentions",
																												 "key" => "$_DBU_mentions_id",
																												 "description" => "$_DBU_mentions_nom")),

												"$_DBU_specs_comp_id" => array("nom_complet" => "Composante",
																						 "unique" => "0",
																						 "not_null" => "1",
																						 "order" => "$_DBU_composantes_univ_id, $_DBU_composantes_nom",
																						 "reference" => array("table" => "$_DB_composantes",
																													 "key" => "$_DBU_composantes_id",
																													 "description" => "$_DBU_composantes_nom"))
											  )
					);


	// ======================================================
	//	================	Fin des déclarations	===============
	//	====================================================== 

	$warning="Attention : cette liste est <strong>GLOBALE</strong>, ne la modifiez que si vous êtes sûr de ce que vous faites.";

	include "configurator.php";
?>
