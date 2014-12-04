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
		case 0 : return "left";
					break;
		case 1 : return "center";
					break;
		case 2 : return "right";
					break;
		case 3 : return "justify";
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
		case 0 : return "L";
					break;
		case 1 : return "C";
					break;
		case 2 : return "R";
					break;
		case 3 : return "J";
					break;
							
		default : return "J";
	}
}

// Définition des fonctions utilisées pour les macros
// La redéfinition des macros n'est pas nécessaire à chaque inclusion du fichier editeur_fonctions, d'où le test d'existence

if(!isset($_SESSION["__MACROS"]) || !is_array($_SESSION["__MACROS"]) || !count($_SESSION["__MACROS"]))
{
	/* Les macros sont utilisables dans le corps des modèles de lettres. Elles doivent être encadrées par le caractère "%" pour
	pouvoir être reconnue => %macro%

	Liste des macros prédéfinies :
	
	"civilite" : M. Mme ou Mlle, en fonction du candidat (sensible aux majuscules : Civilite => Mme | CIVILITE => MME)
	"nom" : nom du candidat (sensible aux majuscules : Nom => Durand | NOM => DURAND)
	"prenom" : prénom du candidat (sensible aux majuscules)
	"date_naissance" : date de naissance (ex: "18 janvier 1990")
	"ville_naissance" : ville de naissance telle que l'a entrée le candidat
	"pays_naissance" : pays de naissance (même remarque)
	"annee_universitaire" : année concernée par la candidature (ex: "2009-2010")
	"formation" : nom de la formation, en toute lettre (ex: "L3 Mathématique Parcours Magistère 1ère année")
	"responsable" : nom du responsable de la formation lorsqu'il est renseigné (sensible aux majuscules)
						 -> s'il n'est pas renseigné, on indique seulement "Le Responsable"
	"courriel_responsable" : adresse électronique du responsable de la formation si renseignée
	"date_commission" : date de la commission pédagogique pour la formation concernée. La date est enregistrée dans la
							  candidature au moment de son dépôt, donc si la date a changé depuis, le changement ne sera pas
							  répercuté ici.
	"rang_liste_complementaire" : rang du candidat sur la liste complémentaire
	"entretien" : date, heure et lieu de la convocation à un entretien complémentaire
	"cursus" : cursus du candidat (attention : n'apparaissent que les étapes marquées "justifiées" par la scolarité dans
				  le menu "2-Cursus" des fiches)
	"transmission" : en cas de transmission de dossier, année vers laquelle le dossier du candidat est transmis
	"motivation" : motif(s) de refus, de mise en attente ou d'admission sous réserve
	"masculin_feminin" : accord masculin/féminin d'un nom, adjectif, en fonction du genre du candidat.
								ex : %le candidat/la candidate%, %admis/admise%
	"date" : date à laquelle la lettre est générée ("5 mai 2009")
	"decisions_multiples" : pour les candidatures à choix multiples (i.e plusieurs formations regroupées en une candidature),
									cette macro affiche les décisions de chaque "sous-choix". L'affichage s'arrête dès qu'une
									admission est trouvée (car le traitement en Commission s'arrête dans ce cas là).
	"transmission_multiple" : cas particulier de la macro précédente : transfert de dossier dans une candidature à choix
									  multiples
	"signature" : cette macro est remplacée soit par le champ "signature" par défaut (appliqué à toutes les lettres), soit
					  au champ "signature" propre à la lettre générée (i.e lorsqu'on ne veut pas du champ par défaut).
	
	*/


	$_SESSION["__MACROS"]=array(
"civ" =>	'$txt=preg_replace("/%Civ%/", ucfirst(strtolower(civ_lang($cand_array["civilite"], $lettre_lang, 0))), $txt);
			$txt=preg_replace("/%civ%/", strtolower(civ_lang($cand_array["civilite"], $lettre_lang, 0)), $txt);
			$txt=preg_replace("/%CIV%/", strtoupper(civ_lang($cand_array["civilite"], $lettre_lang, 0)), $txt);',

"civilite" =>	'$txt=preg_replace("/%Civilit.%/", ucfirst(strtolower(civ_lang($cand_array["civilite"], $lettre_lang, 1))), $txt);
					$txt=preg_replace("/%civilit.%/", strtolower(civ_lang($cand_array["civilite"], $lettre_lang, 1)), $txt);
					$txt=preg_replace("/%CIVILIT.%/", strtoupper(civ_lang($cand_array["civilite"], $lettre_lang, 1)), $txt);',

"nom" => '$txt=preg_replace("/%Nom%/", ucfirst(mb_strtolower($cand_array["nom"])), $txt);
			$txt=preg_replace("/%nom%/", mb_strtolower($cand_array["nom"]), $txt);
			$txt=preg_replace("/%NOM%/", mb_strtoupper($cand_array["nom"]), $txt);',

"prenom" => '$txt=preg_replace("/%Pr.nom%/", ucfirst(mb_strtolower($cand_array["prenom"])), $txt);
				$txt=preg_replace("/%pr.nom%/", mb_strtolower($cand_array["prenom"]), $txt);
				$txt=preg_replace("/%PR.NOM%/", mb_strtoupper($cand_array["prenom"]), $txt);',

"date_naissance" => '$txt=str_ireplace("%naissance%", $cand_array["naissance"], $txt);',

"ville_naissance" => '$txt=str_ireplace("%ville_naissance%", $cand_array["lieu_naissance"], $txt);',

"pays_naissance" => '$txt=str_ireplace("%pays_naissance%", $cand_array["pays_naissance"], $txt);',

"annee_universitaire" => '$cand_periode="$candidature_array[periode]-" . ($candidature_array["periode"]+1);
								  $txt=preg_replace("/%ann.e_universitaire%/", $cand_periode, $txt);',

"formation" => '$txt=preg_replace("/%Formation%/", $candidature_array["texte_formation"], $txt);
					$txt=preg_replace("/%formation%/", $candidature_array["texte_formation"], $txt);
					$txt=preg_replace("/%FORMATION%/", mb_strtoupper($candidature_array["texte_formation"]), $txt);',

"responsable" => '$txt=preg_replace("/%Responsable%/", ucwords(mb_strtolower($candidature_array["responsable"])), $txt);
					$txt=preg_replace("/%responsable%/", ucwords(mb_strtolower($candidature_array["responsable"])), $txt);
					$txt=preg_replace("/%RESPONSABLE%/", mb_strtoupper($candidature_array["responsable"]), $txt);',

"courriel_responsable" => '$txt=preg_replace("/%courriel_responsable%/", mb_strtolower($candidature_array["responsable_email"]), $txt);',

"date_commission" => 'if($candidature_array["date_decision_unix"]==0 || $candidature_array["date_decision_unix"]=="")
								$txt=str_ireplace("%date_commission%", date_lang($candidature_array["session_commission_unix"], $lettre_lang, 1, 0), $txt);
							else
								$txt=str_ireplace("%date_commission%", date_lang($candidature_array["date_decision_unix"], $lettre_lang, 1, 0), $txt);',

"rang_liste_complementaire" => '$txt=str_ireplace("%rang_attente%", $candidature_array["rang_attente"], $txt);',


"entretien" => '$txt=str_ireplace("%entretien_date%", date_lang($candidature_array["entretien_date_unix"], $lettre_lang, 1, 1), $txt);
					$txt=str_ireplace("%entretien_heure%", $candidature_array["entretien_heure"], $txt);
					$txt=str_ireplace("%entretien_lieu%", $candidature_array["entretien_lieu"], $txt);
					$txt=str_ireplace("%entretien_salle%", $candidature_array["entretien_salle"], $txt);',

"cursus" => '$count_cursus=count($cursus_array);

				if($count_cursus)
				{
					// on ne prend que les 2 derniers diplomes obtenus
					// TODO : à vérifier
					$texte_cursus="";

					if($count_cursus>2)
						$i=$count_cursus-2;
					else
						$i=0;

					for(; $i<$count_cursus; $i++)
					{
						if(isset($cursus_array[$i]["lieu"]))
							$texte_cursus .=$cursus_array[$i]["cursus"] . " " . $cursus_array[$i]["lieu"] . " (". $cursus_array[$i]["date"] . ")\n";
						else
							$texte_cursus .=$cursus_array[$i]["cursus"] . " (". $cursus_array[$i]["date"] . ")\n";
					}

					$txt=str_ireplace("%cursus%", $texte_cursus, $txt);
				}
				else
					$txt=str_ireplace("%cursus%", "- Néant", $txt);',

"transmission" => '$txt=preg_replace("/%Transmission%/", $candidature_array["transmission"], $txt);
						$txt=preg_replace("/%transmission%/", mb_strtolower($candidature_array["transmission"]), $txt);
						$txt=preg_replace("/%TRANSMISSION%/", mb_strtoupper($candidature_array["transmission"]), $txt);',

"motivation" => '$txt=str_ireplace("%motifs%", $candidature_array["motivation"], $txt);
					 $txt=str_ireplace("%motif%", $candidature_array["motivation"], $txt);',

"masculin_feminin" => 'if(preg_match_all("/%[a-zA-Zàáâãäåçèéêëìíîïñðòóôõöùúûüýÿ]+\/[a-zA-Zàáâãäåçèéêëìíîïñðòóôõöùúûüýÿ]+%/", $txt, $resultats))
							{
								foreach($resultats[0] as $valeur)
								{
									$vals=explode("/", $valeur);

									$masculin=str_replace("%","", $vals[0]);
									$feminin=str_replace("%","", $vals[1]);
									
									if($cand_array["civilite"]=="M" || $cand_array["civilite"]=="M." || $cand_array["civilite"]=="Monsieur" || $cand_array["civilite"]=="Mr" || $cand_array["civilite"]=="Mr." || $cand_array["civilite"]=="Mister")
										$txt=str_replace($valeur, $masculin, $txt);
									else
										$txt=str_replace($valeur, $feminin, $txt);
								}
							}',

"date" => '$txt=str_ireplace("%date%", date_lang(time(), $lettre_lang, 1, 0), $txt);',

"decisions_multiples" => 'if(stristr($txt, "%decisions_multiples%"))
								{
									$decisions_multiples_texte="";

									$candidatures_multiples_array=__get_candidatures_multiples($dbr, $candidature_array["id"]);

									if($candidatures_multiples_array!=FALSE)
									{
										$ordre_dernier_choix=count($candidatures_multiples_array)-1;

										foreach($candidatures_multiples_array as $ordre_cand => $cand_m_array)
										{
											// Dossier transmis : la décision réelle est "refus"
											if($cand_m_array["decision"]==$GLOBALS["__DOSSIER_TRANSMIS"])
												$decisions_multiples_texte.="Choix " . ($ordre_cand+1) . " : " . str_replace("\n", " ", $cand_m_array["texte_formation"]) . "\nDécision : Refus";
											else {
												$decisions_multiples_texte.="Choix " . ($ordre_cand+1) . " : " . str_replace("\n", " ", $cand_m_array["texte_formation"]) . "\nDécision : $cand_m_array[decision_txt]";
												
												if(trim($cand_m_array["motivation"])=="") {
												   $decisions_multiples_texte.="\n\n";
												}
										   }

											if($cand_m_array["decision"]==$GLOBALS["__DOSSIER_REFUS"] || $cand_m_array["decision"]==$GLOBALS["__DOSSIER_TRANSMIS"])
												$decisions_multiples_texte.=" (" . str_replace("\n",", ", $cand_m_array["motivation"]) . ").\n\n"; // Todo : affichage des motifs à peaufiner
											elseif($cand_m_array["decision"]==$GLOBALS["__DOSSIER_LISTE"])
												$decisions_multiples_texte.=" (rang actuel : $cand_m_array[rang_attente]).\n";
											elseif($cand_m_array["decision"]==$GLOBALS["__DOSSIER_ADMIS"] || $cand_m_array["decision"]==$GLOBALS["__DOSSIER_ADMIS_AVANT_CONFIRMATION"] 
											       || $cand_m_array["decision"]==$GLOBALS["__DOSSIER_ADMISSION_CONFIRMEE"] || $cand_m_array["decision"]==$GLOBALS["__DOSSIER_SOUS_RESERVE"])
											{
												if($cand_m_array["decision"]==$GLOBALS["__DOSSIER_SOUS_RESERVE"])
													$decisions_multiples_texte.=" (" . str_replace("\n",", ", $cand_m_array["motivation"]) . ").\n\n";

                                    /*
												// admission trouvée : on s\'arrête là et on affiche un message s\'il reste des voeux
												if($ordre_cand!=$ordre_dernier_choix)
													$decisions_multiples_texte.="\n\nDans la mesure où vos candidatures ont été classées par ordre de préférence et qu\'un voeu a été retenu, la Commission pédagogique n\'a pas examiné les voeux placés après celui-ci.";

												break;
												*/
											}
											else
											    $decisions_multiples_texte.="";
										}  
									}

									$txt=preg_replace("/%d.cisions_multiples%/i", $decisions_multiples_texte, $txt);
								}',

"transmission_multiple" => 'if(stristr($txt, "%transmission_multiple%"))
									{
										$transmission_multiple_texte="";

										$candidatures_multiples_array=__get_candidatures_multiples($dbr, $candidature_array["id"]);

										if($candidatures_multiples_array!=FALSE)
										{
											foreach($candidatures_multiples_array as $ordre_cand => $cand_m_array)
											{
												if($cand_m_array["decision"]==$GLOBALS["__DOSSIER_TRANSMIS"])
													$transmission_multiple_texte.=$cand_m_array["transmission"] . "\n";
											}
										}

										$txt=preg_replace("/%transmission_multiple%/i", $transmission_multiple_texte, $txt);
									}',

"signature" => 'if(stristr($txt, "%signature%"))
					{
						// Récupération de la signature de la lettre (ou celle par défaut)
						// TODO : passer par un paramètre de la fonction ($lettre_array) ?
						$result2=db_query($dbr,"SELECT $GLOBALS[_DBC_lettres_txt_sign], $GLOBALS[_DBC_lettres_flag_txt_sign]
																		FROM $GLOBALS[_DB_lettres]
																		WHERE $GLOBALS[_DBC_lettres_id]=\'$_SESSION[lettre_id]\'");

						if(db_num_rows($result2))
						{
							list($texte_signature, $flag_signature)=db_fetch_row($result2, 0);

							if($flag_signature=="t") // signature par défaut ; il faut récupérer celle de la table "composantes"
							{
								$res_comp=db_query($dbr, "SELECT $GLOBALS[_DBC_composantes_txt_sign] FROM $GLOBALS[_DB_composantes]
																				WHERE $GLOBALS[_DBC_composantes_id]=\'$_SESSION[comp_id]\'");

								if(db_num_rows($res_comp))
									list($texte_signature)=db_fetch_row($res_comp, 0);

								db_free_result($res_comp);
							}

							// Il faut appliquer cette fonction sur la signature (en supprimant les champs %signature% éventuels,
							// sinon on obtient une boucle infinie)

							$txt=preg_replace("/%signature%/i", $texte_signature, $txt);
							// $txt=pdf_traitement_macros($dbr, str_ireplace("%signature%", "", $texte_signature), $cand_array, $candidature_array, $cursus_array, $lettre_lang);
						}

						db_free_result($result2);
					}'
	);
}

// Traitement des macros, version 2 :
// Les macros sont définies dans un tableau : à chacune, on associe une fonction anonyme (via "create_function")
// pour son traitement. Ceci permet à des modules complémentaires d'en ajouter de nouvelles ou de redéfinir des
// macros existantes

// TODO : réfléchir sur la nécessité de passer $dbr en paramètre

function pdf_traitement_macros($dbr, $txt, $cand_array, $candidature_array, $cursus_array, $lettre_lang)
{
	// On applique chaque portion de code (ce ne sont pas réellement des fonctions) au texte
	// Inconvénient : avec une fonction par macro, on ne traite qu'une macro à la fois.
	foreach($_SESSION["__MACROS"] as $nom_macro => $code_macro)
		eval($code_macro);

	// Macros définies par les utilisateurs (modules)
	if(isset($_SESSION["__MACROS_USERS"]) && is_array($_SESSION["__MACROS_USERS"]) && count($_SESSION["__MACROS_USERS"]))
	{
		foreach($_SESSION["__MACROS_USERS"] as $nom_macro => $code_macro)
			eval($code_macro);
	}

	return $txt;
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


// décide si les boutons monter/descendre doivent être affichés

function show_up_down($i,$pos,$nb_elem,$element_type,$target_type,$target_type2)
{
	$return_str="";

	if($i!=0)
		$return_str.="<a href='move_element.php?co=$i&pos=$pos&ct=$element_type&tt=$target_type&dir=0' target='_self'><img src='$GLOBALS[__ICON_DIR]/up_16x16.png' alt='Monter' border='0'></a> ";

	if($i!=($nb_elem-1))
		$return_str.="<a href='move_element.php?co=$i&pos=$pos&ct=$element_type&tt=$target_type2&dir=1' target='_self'><img src='$GLOBALS[__ICON_DIR]/down_16x16.png' alt='Descendre' border='0'></a> ";

	return $return_str;
}

// idem version 2
function show_up_down2($i,$nb_elem,$element_type,$target_type,$target_type2)
{
	if($i!=0)
		print("<a href='move_element.php?co=$i&ct=$element_type&tt=$target_type&dir=0' target='_self'><img src='$GLOBALS[__ICON_DIR]/up_16x16.png' alt='Monter' border='0'></a> ");

	if($i!=($nb_elem-1))
		print("<a href='move_element.php?co=$i&&ct=$element_type&tt=$target_type2&dir=1' target='_self'><img src='$GLOBALS[__ICON_DIR]/down_16x16.png' alt='Descendre' border='0'></a> ");
}


function menu_editeur_3($chemin)
{
	print("<table border='0' cellpadding='2' cellspacing='0' width='100%' align='center'>
					<tr>
						<td height='24' background='$GLOBALS[__IMG_DIR]/fond_menu_haut.jpg' align='left' valign='middle' nowrap='true'>");

	$cnt_path=count($chemin);

	while($cnt_path=count($chemin))
	{
		$nom=key($chemin);
		$lien=current($chemin);

		if(!empty($lien))		
			print("<a href='$lien' target='_self' class='lien_blanc'><b>$nom</b></a>");
		else
			print("<font class='Texteblanc'>$nom</font>");

		// ! dernier élément
		if($cnt_path!=1)
			print("<font class='Texteblanc'>&nbsp;<b>></b>&nbsp;</font>");
		array_shift($chemin);
	}

	print("</td>
				<td height='24' background='$GLOBALS[__IMG_DIR]/fond_menu_haut.jpg' align='right' valign='middle' nowrap='true'>
					<font class='Texteblanc'>
						<a href='$GLOBALS[__GESTION_DIR]/login.php' class='lien_blanc'>Déconnecter</a>&nbsp;&nbsp;
					</font>
				</td>
			</tr>
			</table>");
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
									FROM	 $GLOBALS[_DB_encadre]
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
										  $GLOBALS[_DBC_para_italique], $GLOBALS[_DBC_para_taille], $GLOBALS[_DBC_para_marge_g]
								  FROM $GLOBALS[_DB_para] WHERE $GLOBALS[_DBC_para_lettre_id]='$lettre_id'
									ORDER BY $GLOBALS[_DBC_para_ordre] ASC");

	$rows=db_num_rows($result);

	// on met chaque paragraphe dans le tableau
	for($i=0; $i<$rows ; $i++)
	{
		list($id,$texte,$txt_align,$ordre, $gras, $italique, $taille, $marge_gauche)=db_fetch_row($result, $i);
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
			$elements["$ordre"]=array("type" => 5, "id" => $id, "texte" => $texte, "txt_align" => $txt_align,
											  "gras" => $gras, "italique" => $italique, "taille" => $taille,
											  "marge_gauche" => $marge_gauche);
	}
	db_free_result($result);

	// Séparateurs (type 8)
	$result=db_query($db,"SELECT $GLOBALS[_DBC_sepa_lettre_id], $GLOBALS[_DBC_sepa_ordre], $GLOBALS[_DBC_sepa_nb_lignes]
									FROM $GLOBALS[_DB_sepa]
									WHERE $GLOBALS[_DBC_sepa_lettre_id]='$lettre_id'
								 ORDER BY $GLOBALS[_DBC_sepa_ordre] ASC");

	$rows=db_num_rows($result);

	// on met chaque séparateur dans le tableau
	for($i=0; $i<$rows ; $i++)
	{
		list($id,$ordre,$nb_lignes)=db_fetch_row($result, $i);
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
			$elements["$ordre"]=array("type" => 8, "id" => $id, "nb_lignes" => $nb_lignes);
	}
	db_free_result($result);
	
	return($elements);
}


?>
