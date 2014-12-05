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

// en fonction de l'entier en paramètre, donne l'alignement d'un objet

function get_align($int_align)
{
	if(!is_numeric($int_align))
		return "left";
		
	switch($int_align)
	{
		case 0 : 	return "left";
							break;
		case 1 : 	return "center";
							break;
		case 2 : 	return "right";
							break;
		case 3 : 	return "justify";
							break;
							
		default : return "left";
	}
}

// Pareil mais avec les paramètres de FPDF
function get_fpdf_align($int_align)
{
	if(!is_numeric($int_align))
		return "J";
		
	switch($int_align)
	{
		case 0 : 	return "L";
							break;
		case 1 : 	return "C";
							break;
		case 2 : 	return "R";
							break;
		case 3 : 	return "J";
							break;
							
		default : return "J";
	}
}

// GET_TABLE_NAME
// Determine le nom de la table en fonction du type d'un élément
// ARGUMENT :
// - type d'élément (entier)
// RETOUR
//- nom de la table correspondante et des colonnes utiles
function get_table_name($type)
{
	$return_array=array();

	switch($type)
	{
		case 2:	 	$return_array["table"]="$GLOBALS[_DB_encadre]";
						$return_array["id"]="$GLOBALS[_DBU_encadre_lettre_id]";;
						$return_array["ordre"]="$GLOBALS[_DBU_encadre_ordre]";
						return $return_array;
						break;

		case 5:		$return_array["table"]="$GLOBALS[_DB_para]";
						$return_array["id"]="$GLOBALS[_DBU_para_lettre_id]";;
						$return_array["ordre"]="$GLOBALS[_DBU_para_ordre]";
						return $return_array;
						break;

		case 8:		$return_array["table"]="$GLOBALS[_DB_sepa]";
						$return_array["id"]="$GLOBALS[_DBU_sepa_lettre_id]";;
						$return_array["ordre"]="$GLOBALS[_DBU_sepa_ordre]";
						return $return_array;
						break;

		default: return FALSE; // normalement l'argument $type est vérifié AVANT l'appel à la fonction
	}

}


// idem version 2
function show_up_down2($i,$nb_elem,$element_type,$target_type,$target_type2)
{
	if($i!=0)
		print("<a href='move_element.php?co=$i&ct=$element_type&tt=$target_type&dir=0' target='_self'><img src='$GLOBALS[__ICON_DIR]/up_16x16.png' alt='Monter' border='0'></a> ");

	if($i!=($nb_elem-1))
		print("<a href='move_element.php?co=$i&&ct=$element_type&tt=$target_type2&dir=1' target='_self'><img src='$GLOBALS[__ICON_DIR]/down_16x16.png' alt='Descendre' border='0'></a> ");
}

// version pour les justificatifs
function show_up_down3($i,$nb_elem)
{
	if($i!=0)
		print("<a href='move_element.php?co=$i&dir=0' target='_self'><img src='$GLOBALS[__ICON_DIR]/up_16x16.png' alt='Monter' border='0'></a> ");

	if($i!=($nb_elem-1))
		print("<a href='move_element.php?co=$i&dir=1' target='_self'><img src='$GLOBALS[__ICON_DIR]/down_16x16.png' alt='Descendre' border='0'></a> ");
}

// GET_ALL_ELEMENTS
// Construction d'un tableau contenant les éléments composant une lettre
// ARGUMENTS :
// - db : ressource correspondant à une connexion à une bdd
// - lettre_id : identifiant de la lettre concernée
// RETOUR
// - array contenant les éléments (clés=ordre des éléments)

function get_all_elements($db, $lettre_id)
{
	// fonction qui recherche tous les éléments d'un article et qui retourne un tableau contenant ces éléments triés

	// initialisation du tableau d'éléments
	$elements=array();

	// ENCADRES (type_element = 2)
	$result=db_query($db,"SELECT $GLOBALS[_DBC_encadre_lettre_id], $GLOBALS[_DBC_encadre_texte], $GLOBALS[_DBC_encadre_txt_align], $GLOBALS[_DBC_encadre_ordre]
									FROM $GLOBALS[_DB_encadre]
								 WHERE $GLOBALS[_DBC_encadre_lettre_id]='$lettre_id'
								 ORDER BY $GLOBALS[_DBC_encadre_ordre] ASC");

	$rows=db_num_rows($result);

	// on met chaque encadré dans le tableau
	for($i=0; $i<$rows ; $i++)
	{
		list($id,$texte,$txt_align,$ordre)=db_fetch_row($result, $i);
		if(array_key_exists("$ordre",$elements)) // l'ordre existe deja : erreur
		{
			$err_file=realpath(__FILE__);
			$line=__LINE__;
			
			if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
			{
				mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur dans $err_file, ligne $line\n'Base de données incohérente'\nIdentifiant : $_SESSION[auth_user]");
				die("Erreur : base de données incohérente. Un courriel a été envoyé à l'administrateur.");
			}
			else
				die("Erreur : base de données incohérente. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
		}
		else
			$elements["$ordre"]=array("type" => 2, "id" => $id, "texte" => $texte, "txt_align" => $txt_align);
	}
	db_free_result($result);

	// PARAGRAPHES (type_element = 5)
	$result=db_query($db,"SELECT $GLOBALS[_DBC_para_lettre_id], $GLOBALS[_DBC_para_texte], $GLOBALS[_DBC_para_align], $GLOBALS[_DBC_para_ordre], $GLOBALS[_DBC_para_gras],
										  $GLOBALS[_DBC_para_italique], $GLOBALS[_DBC_para_taille]
								 FROM $GLOBALS[_DB_para] WHERE $GLOBALS[_DBC_para_lettre_id]='$lettre_id'
								 	ORDER BY $GLOBALS[_DBC_para_ordre] ASC");

	$rows=db_num_rows($result);

	// on met chaque paragraphe dans le tableau
	for($i=0; $i<$rows ; $i++)
	{
		list($id,$texte,$txt_align,$ordre, $gras, $italique, $taille)=db_fetch_row($result, $i);
		if(array_key_exists("$ordre",$elements)) // l'ordre existe deja : erreur
		{
			$err_file=realpath(__FILE__);
			$line=__LINE__;
			
			if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
			{
				mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur dans $err_file, ligne $line\n'Base de données incohérente'\nIdentifiant : $_SESSION[auth_user]");
				die("Erreur : base de données incohérente. Un courriel a été envoyé à l'administrateur.");
			}
			else
				die("Erreur : base de données incohérente. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
		}
		else
			$elements["$ordre"]=array("type" => 5, "id" => $id, "texte" => $texte, "txt_align" => $txt_align, "gras" => $gras, "italique" => $italique, "taille" => $taille);
	}
	db_free_result($result);

	// Séparateurs (type 8)
	$result=db_query($db,"SELECT $GLOBALS[_DBC_sepa_lettre_id], $GLOBALS[_DBC_sepa_ordre] FROM $GLOBALS[_DB_sepa]
													WHERE $GLOBALS[_DBC_sepa_lettre_id]='$lettre_id'
														ORDER BY $GLOBALS[_DBC_sepa_ordre] ASC");

	$rows=db_num_rows($result);

	// on met chaque séparateur dans le tableau
	for($i=0; $i<$rows ; $i++)
	{
		list($id,$ordre)=db_fetch_row($result, $i);
		if(array_key_exists("$ordre",$elements)) // l'ordre existe deja : erreur
		{
			$err_file=realpath(__FILE__);
			$line=__LINE__;
			
			if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
			{
				mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Erreur dans $err_file, ligne $line\n'Base de données incohérente'\nIdentifiant : $_SESSION[auth_user]");
				die("Erreur : base de données incohérente. Un courriel a été envoyé à l'administrateur.");
			}
			else
				die("Erreur : base de données incohérente. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
		}
		else
			$elements["$ordre"]=array("type" => 8, "id" => $id);
	}
	db_free_result($result);
	
	return($elements);
}


?>
