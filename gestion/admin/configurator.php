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
	// fichier include uniquement
	// prérequis : 
	//	- la session doit déjà etre démarrée par le script appelant
	// - les autres includes doivent déjà etre effectués (vars et db.php)
	// - verif_auth doit aussi avoir été lancé dans le script appelant

	// CE SCRIPT EST EXPERIMENTAL ET SERT A TESTER LE GENERATEUR DE GESTION DE BASES DE DONNEES, DONT LES
	// TABLES SONT RELATIVEMENT SIMPLES ET CONSTRUITES SUR UN MEME MODELE

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;
	
	$table_nom=$table["nom"];
	$table_pkey=$table["pkey"];

	if(isset($table["pkey_type"]))
		$table_pkey_type=$table["pkey_type"];

	$table_order=$table["order"];

	// TODO : écrire ce test autrement
	if(isset($niveau_min) && $niveau_min>$_SESSION['niveau'])
	{
		header("Location:index.php");
		exit;
	}

	$dbr=db_connect();

	if(isset($table["type"]))
	{
		$table_type=$table["type"];
		$back_url=($table_type==1) ? "index.php" : $php_self;
	}
	else
	{
		$table_type=0;
		$back_url=$php_self;
	}

	// type "Modification" uniquement : on force les valeurs pour entrer directement dans la 2nde partie du script
	if($table_type==1 && !isset($_POST["action"]))
		$_POST["modifier"]=$_POST["modifier_x"]=1;

	//	$cnt_champs=count($table["colonnes"]);
/*
	if(isset($_POST["act"]) || $table_type==1)
	{
*/

	if(isset($_POST["ajouter"]) || isset($_POST["ajouter_x"]))
		$action="ajouter";
	elseif(isset($_POST["suppr"]) || isset($_POST["suppr_x"]))
		$action="supprimer";
	elseif(isset($_POST["modifier"]) || isset($_POST["modifier_x"]))
		$action="modifier";
	elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$action=$_POST["action"];

		switch($action)
		{
			case "ajouter" :	$ordre_valeurs="(";
									$valeurs="(";
									$champs_existent=array();
									$mauvais_format=array();
									// $cnt_champs_existent=0;

									// Gestion de la clé primaire
									if(isset($table_pkey_type) && $table_pkey_type=="max")
									{
										$ordre_valeurs.="$table_pkey";
										$result=db_query($dbr,"SELECT max($table_pkey)+1 FROM $table_nom");

										list($max_id)=db_fetch_row($result, 0);
										db_free_result($result);

										if($max_id=="")
											$max_id=1;

										$valeurs .= "'$max_id'";
									}

									// récupération des valeurs du formulaire

									foreach($table["colonnes"] as $colonne => $array_colonne)
									{
										if($ordre_valeurs!="(")
										{
											$ordre_valeurs .= ",";
											$valeurs .= ",";
										}

										$val=trim($_POST["field_$colonne"]);

										if(isset($array_colonne["type"]))
										{
											if($array_colonne["type"]=="date") // cas particulier pour le type date
											{
												if(strlen($val)==8)
												{
													$jour=substr($val,0,2);
													$mois=substr($val,2,2);
													$annee=substr($val,4);
													$val=mktime(12,0,1, $mois, $jour, $annee); // date au format unix
												}
												else
													$mauvais_format[$colonne]=$val;
											}
										}

										if(($array_colonne["not_null"] == 1) && $val=="")	// si la valeur ne doit pas être nulle
											$valeur_vide=1;

										if(($array_colonne["unique"] == 1)) // vérification d'unicité (attention, ne gère pas les accents)
										{
											if(ctype_digit($val))
											   $result=db_query($dbr,"SELECT * FROM $table_nom WHERE $colonne='$val'");
											else										
												$result=db_query($dbr,"SELECT * FROM $table_nom WHERE $colonne ILIKE '$val'");
												
											$rows=db_num_rows($result);
											if($rows)
											{
												// on ajoute la colonne dont la valeur existe déjà, avec la valeur en question (pour la remettre dans le formulaire)
												$champs_existent[$colonne]=$val;
											}
											db_free_result($result);
										}

										$ordre_valeurs .= "$colonne";
										$val=str_replace("'","''", stripslashes($val));
										$valeurs .= "'$val'";
									}

									if(isset($condition_composante) && $condition_composante==1)
									{
										$ordre_valeurs .= ", composante_id";
										$valeurs .= ", '$_SESSION[comp_id]'";
									}

									// fermeture des chaines
									$ordre_valeurs .= ")";
									$valeurs .= ")";

									if(!isset($valeur_vide) && !count($champs_existent) && !count($mauvais_format))
										db_locked_query($dbr, $table_nom, "INSERT INTO $table_nom $ordre_valeurs VALUES $valeurs");

									break;

			case "modifier" :	if($table_type!=1)
										$val_pkey=$_POST["element"];

									$update_query="";
									$champs_existent=array();
									$mauvais_format=array();
									// $cnt_champs_existent=0;

									// récupération des valeurs du formulaire
									foreach($table["colonnes"] as $colonne => $array_colonne)
									{
										if(!empty($update_query))
											$update_query.=", ";

										$val=trim($_POST["field_$colonne"]);

										if(isset($array_colonne["type"]))
										{
											if($array_colonne["type"]=="date")
											{
												if(strlen($val)==8) // cas particulier pour le type date
												{
													$jour=substr($val,0,2);
													$mois=substr($val,2,2);
													$annee=substr($val,4);
													$val=mktime(0,0,1, $mois, $jour, $annee); // date au format unix
												}
												else
													$mauvais_format[$colonne]=$val;
											}
										}

										if(($array_colonne["not_null"] == 1) && $val=="")	// si la valeur ne doit pas être vide
											$valeur_vide=1;

										if(($array_colonne["unique"] == 1) && $table_type!=1) // vérification d'unicité (attention, ne gère pas les accents)
										{
											if(ctype_digit($val))
												$line_val="$colonne='$val'";
											else
												$line_val="$colonne ILIKE '$val'";
										
											if(ctype_digit($val_pkey))
												$line_val2="$table_pkey!='$val_pkey'";
											else
												$line_val2="$table_pkey NOT LIKE '$val_pkey'";
										
											$result=db_query($dbr,"SELECT * FROM $table_nom WHERE $line_val AND $line_val2");
											$rows=db_num_rows($result);
											if($rows)
												$champs_existent[$colonne]=$val;

											db_free_result($result);
										}

										$val=str_replace("'","''", stripslashes($val));
										$update_query .= "$colonne='$val'";
									}

									if(!isset($valeur_vide) && !count($champs_existent) && !count($mauvais_format))
									{
										if($table_type!=1) // requête un peu particulière si type=1
											db_query($dbr,"UPDATE $table_nom SET $update_query WHERE $table_pkey='$val_pkey'");
										else
											db_query($dbr,"UPDATE $table_nom SET $update_query");
									}
									break;

			case "supprimer" :	$val_pkey=$_POST["element"];
										if(!empty($val_pkey))
											db_query($dbr,"DELETE FROM $table_nom WHERE $table_pkey='$val_pkey'");
										else
											$cle_vide=1;
										break;
		}
		
		if(!isset($valeur_vide) && (!isset($champs_existent) || !count($champs_existent)) && (!isset($mauvais_format) || !count($mauvais_format)))
		{
			db_close($dbr);
			$redirect="$php_self?succes=1";
			header("Location:$redirect");
			exit;
		}
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		if(isset($titre_page))
			titre_page_icone("$titre_page", "", 15, "L");

		print("<form action='$php_self' method='POST' name='form1'>");

		// ==================================================================
		// PARTIE 2 : affichage en fonction de l'action choisie : ajouter, modifier ou supprimer
		// ==================================================================

		if(isset($action))
		{
			// paramètres communs
			print("<input type='hidden' name='action' value='$action'>");

			switch($action)
			{
				case "modifier" :	// cas particulier si le type de table est 1 (modification uniquement) : il n'y a qu'une ligne dans la table, donc pas besoin du post
										$selection="";

										if($table_type!=1)
										{
											$val_pkey=$_POST["element"];
											print("<input type='hidden' name='element' value='$val_pkey'>");
											$titre_section="Modifier un élément existant";

											// énumération des champs à récupérer dans la table
											$selection="$table_pkey";
										}
										else
											$titre_section="Modifier des paramètres existants";

										// énumération des champs à récupérer dans la table (suite)
										foreach($table["colonnes"] as $colonne => $array_colonne)
										{
											if(!empty($selection))
												$selection.=", ";

											$selection .= "$colonne";
										}

										if($table_type!=1)	// la requête est différente en fonction du type de table
											$result=db_query($dbr,"SELECT $selection FROM $table_nom WHERE $table_pkey='$val_pkey'");
										else
											$result=db_query($dbr,"SELECT $selection FROM $table_nom");

										$rows=db_num_rows($result);
										if($rows)
											// on se contente de stocker le résultat, tout sera traité dans la partie 'ajouter'
											// on force le fait qu'il n'y ait qu'un seul résultat à la requête précédente
											$result_array=db_fetch_array($result,0,PGSQL_ASSOC);
										elseif($table_type!=1)
											die("Erreur : la table $table_nom ne contient pas la valeur $table_pkey='$val_pkey'");
										else
											die("Erreur : la table ne contient aucun élément. Merci de contacter l'administrateur.");

										db_free_result($result);
										// PAS DE BREAK ICI

				case "ajouter" :	if(!isset($titre_section))
											$titre_section="Ajouter un nouvel élément";

													print("<center>
																<font class='Texte3'><b><i>$titre_section</i></b>
															 </center>
															 <br><br>
															 <table align='center'>");

													// focus pour le formulaire
													$nom_colonnes=array_keys($table["colonnes"]);
													if(isset($nom_colonnes[0]))
														$focus=$nom_colonnes[0];

													// boucle sur les champs de la table et affichage du tableau de saisie
													foreach($table["colonnes"] as $colonne => $array_colonne)
													{
														// Intitulé du champ
														$nom_complet=$array_colonne["nom_complet"];

														// modification : la valeur existe déjà
														if(isset($result_array))
															$val=htmlspecialchars($result_array[$colonne],ENT_QUOTES, $default_htmlspecialchars_encoding);
														else
															$val="";

														// si on a déjà tenté de remplir le formulaire mais qu'une erreur est apparue
														if(isset($champs_existent) && isset($champs_existent[$colonne]))
															$val=$champs_existent[$colonne];

														print("<tr>
																	<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>$nom_complet</b></font></td>
																	<td class='td-droite fond_menu'>");

														// affichage en fonction du type de champ
														if(isset($array_colonne["reference"])) // Affichage d'un <select>
														{
															// vérification des paramètres obligatoires
															if(isset($array_colonne["reference"]["table"]) && isset($array_colonne["reference"]["key"]) && isset($array_colonne["reference"]["description"]))
															{
																$ref_table=$array_colonne["reference"]["table"];
																$ref_key=$array_colonne["reference"]["key"];
																$ref_description=$array_colonne["reference"]["description"];

																if(isset($array_colonne["order"]))
																	$order_by=$array_colonne["order"];
																else
																	$order_by=$ref_description;

																$result=db_query($dbr,"SELECT $ref_key,$ref_description FROM $ref_table ORDER BY $order_by");
																$rows=db_num_rows($result);

																if($rows)
																{
																	print("<select name='field_$colonne' size='1'>\n");

																	for($j=0; $j<$rows; $j++)
																	{
																		list($ref_key_val, $ref_description_val)=db_fetch_row($result,$j);

																		if($ref_key_val==$val) // on sélectionne la valeur existante (modification ou erreur)
																			$selected="selected=1";
																		else
																			$selected="";

																		print("<option value='$ref_key_val' $selected>$ref_description_val</option>\n");
																	}
																	print("</select>\n");
																}
																else
																	message("La table référencée, '$ref_table', est vide : impossible de sélectionner une valeur.", $__INFO);

																db_free_result($result);

															}
															else
																die("Option '\$reference' : erreur de syntaxe : les paramètres 'table', 'key' et 'description' sont obligatoires");
														}
														elseif(isset($array_colonne["type"])) // si on a un champ de type 'textarea' ou 'checkbox'
														{
															switch($array_colonne["type"])
															{
																case "textarea" :	print("<textarea name='field_$colonne' class='input' cols='60' rows='5'>$val</textarea>");
																									break;

																case "ouinon" : 	if($val==1)
																						{
																							$yes_checked="checked";
																							$no_checked="";
																						}
																						else
																						{
																							$no_checked="checked";
																							$yes_checked="";
																						}

																						print("<input type='radio' name='field_$colonne' value='1' $yes_checked>
																									<font class='Texte_menu'>Oui</font>
																									&nbsp;&nbsp;
																									<input type='radio' name='field_$colonne' value='0' $no_checked>
																									<font class='Texte_menu'>Non</font>");
																						break;

																case "date" :		// format d'une date : JJMMYYYY
																						if(!empty($val) && is_numeric($val)) // teste si $val contient un timestamp UNIX
																							$date=date_fr("dmY",$val);
																						else
																							$date="";

																						print("<input type='text' name='field_$colonne' value='$date' size='9' maxlength='8'>&nbsp;<font class='Texte_menu'><i>Format strict JJMMAAAA Exemple: 01011980, 30102000, ...</i></font>");
																						break;

																default :	print("<input type='text' name='field_$colonne' value='$val' size='70'>");
															}
														}

														else // champ standard
															print("<input type='text' name='field_$colonne' value='$val' size='70'>");

														print("</td>
																</tr>");
													}

													print("</table>");

													if(isset($focus))
														print("<script language=\"javascript\">
																	document.form1.field_$focus.focus()
																</script>");

													break;

					case "supprimer" :	$val_pkey=$_POST["element"];
												print("<input type='hidden' name='element' value='$val_pkey'>");

												$titre_section="Supprimer un élément existant";

												$selection="$table_pkey";

												// si le champ 'selection' existe, on le prend pour le menu déroulant (en plus de la clé primaire)
												if(isset($table["selection"]))
												{
													$select_colonne=$table["selection"];
													$selection .= ", $select_colonne";
												}
												else // sinon on prend tous les champs de la table (affichage lourd)
													foreach($table["colonnes"] as $colonne => $array_colonne)
														$selection.= ", $colonne";

												$result=db_query($dbr,"SELECT $selection FROM $table_nom WHERE $table_pkey='$val_pkey'");
												$rows=db_num_rows($result);
												if($rows)
												{
													$result_array=db_fetch_array($result,0,PGSQL_ASSOC);

													print("<center>
																<font class='Texte3'><b><i>$titre_section</i></b></font>
																<br>\n");

													message("Souhaitez vous vraiment supprimer cet élément ?", $__QUESTION);

													// construction de la chaine affichée
													$description="";

													foreach($result_array as $key => $val)
													{
														if(!empty($description))
															$description.=" - ";

														if($key != $table_pkey)
															$description.= "$val";
													}

													print("$description</center>");
												}
												else
													die("Erreur : la table $table_nom ne contient pas la valeur $table_pkey='$val_pkey'");
												db_free_result($result);

												break;
			}

			print("<div class='centered_icons_box'>
						<a href='$back_url' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
						<input type='image' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='valider' value='Valider'>
						</form>
					</div>\n");

			if(isset($valeur_vide))
				message("Erreur : vous devez remplir tous les champs du formulaire.", $__ERREUR);
		/*
			if(isset($bad_date))
				print("<br>
							<center>
								<font class='Texte_important'>Erreur : le format de la date est incorrect : [$bad_date]</font>
							</center>");
		*/

			// affichage des erreurs
			if(isset($mauvais_format) && ($cnt_mauvais_format=count($mauvais_format)))
			{
				if($cnt_mauvais_format==1)
				{
					$format_keys=array_keys($mauvais_format);
					$key=$format_keys[0];

					$champ=$table["colonnes"][$key]["nom_complet"];

					message("Erreur : la valeur du champ '$champ' est incorrecte.", $__ERREUR);
				}
				else // plusieurs violations
				{
					$liste="";

					foreach($mauvais_format as $key => $val)
					{
						$nom_complet=$table["colonnes"][$key]["nom_complet"];
						$liste .= "<br>- $nom_complet";
					}

					message("Erreur : les valeurs suivantes sont incorrectes :  
								<br>$liste", $__ERREUR);
				}
			}

			// champs existants
			if(isset($champs_existent) && ($cnt_champs_existent=count($champs_existent)))
			{
				if($cnt_champs_existent==1) // une seule violation de contrainte "UNIQUE"
				{
					$existent_keys=array_keys($champs_existent);
					$key=$existent_keys[0];

					$champ=$table["colonnes"][$key]["nom_complet"];

					message("Erreur : la valeur du champ '$champ' existe déjà dans la base.", $__ERREUR);
				}
				else // plusieurs violations
				{
					$liste="";

					foreach($champs_existent as $key => $val)
					{
						if(!empty($liste))
							$liste .= ", ";

						$nom_complet=$table["colonnes"][$key]["nom_complet"];

						$liste .= "'$nom_complet'";
					}

					message("Erreur : les champs suivants existent déjà dans la base : 
								<br>$liste", $__ERREUR);
				}
			}

			if(isset($cle_vide))
				message("Erreur : l'élément à supprimer n'a pas été sélectionné correctement (clé vide)", $__ERREUR);
		}
		else
		{
			// =========================================================================
			// POINT DE DEPART : sélection des champs existants (pour la modification et la suppression)
			// =========================================================================

			// Type de table
			// 0 = "ajout/modification/suppression" d'éléments
			// 1 = 1 seul élément dans la table : modif uniquement (1 élément doit exister dans la table)

			switch($table_type)
			{
				case 0 :	print("<table cellpadding='4' cellspacing='0' border='0' align='center'>
									<tr>
										<td class='fond_menu2' align='right'>
											<font class='Texte_menu2'><b>Eléments existants : </b></font>
										</td>
										<td class='fond_menu'>\n");

									$selection="$table_nom.$table_pkey";

									// si le champ 'selection' existe, on le prend pour le menu déroulant (en plus de la clé primaire, évidemment)
									if(isset($table["selection"]))
									{
										$select_colonne=$table["selection"];
										$selection .= ", $table_nom.$select_colonne";
									}
									else // sinon on prend tous les champs de la table (affichage lourd)
									{
										foreach($table["colonnes"] as $colonne => $array_colonne)
											$selection .= ", $table_nom.$colonne";
									}

									// requete

									$array_order=explode(",",$table_order);

									$cnt=count($array_order);
									if($cnt>1) // on doit reformater la chaine
									{
										$table_order="";

										foreach($array_order as $nom_colonne)
										{
											if(!empty($table_order))
												$table_order.=", ";

											$table_order.="$nom_colonne";
										}
									}
									else
										$table_order="$table_order";

									// Séparateur pour la liste
									if(isset($table["separateur"]))
									{
										$old_sep_val="";
										$sep_colonne=$table["separateur"]["colonne"];

										if(isset($table["separateur"]["reference"]))
										{
											$sep_ref_table=$table["separateur"]["reference"]["table"];
											$sep_ref_table_key=$table["separateur"]["reference"]["key"];
											$sep_ref_table_texte=$table["separateur"]["reference"]["texte"];
										}

										if(isset($condition_composante) && $condition_composante==1)
											$cond_comp="AND composante_id='$_SESSION[comp_id]'";
										else
											$cond_comp="";

										$result=db_query($dbr,"SELECT $selection, $sep_ref_table.$sep_ref_table_texte as separateur
																								FROM $table_nom, $sep_ref_table
																							WHERE $sep_ref_table.$sep_ref_table_key=$table_nom.$sep_colonne
																							$cond_comp
																							ORDER BY separateur, $table_order ASC");
									}
									else
									{
										if(isset($condition_composante) && $condition_composante==1)
											$cond_comp="WHERE composante_id='$_SESSION[comp_id]'";
										else
											$cond_comp="";

										$result=db_query($dbr,"SELECT $selection FROM $table_nom $cond_comp ORDER BY $table_order ASC");
									}

									$rows=db_num_rows($result);

									if($rows)
									{
										print("<select name='element' size='1'>\n");

										for($i=0; $i<$rows; $i++)
										{
											$result_array=db_fetch_array($result,$i,PGSQL_ASSOC);

											$value=$result_array[$table_pkey];

											$cnt_result=count($result_array);

											// séparateurs éventuels
											if(isset($sep_ref_table))
											{
												$sep_val=$result_array["separateur"];

												// normalement, le séparateur est la dernière colonne sélectionnée
												if($sep_val != $old_sep_val)
												{
													if($i!=0)
														print("</optgroup>
															<option value='' label='' disabled></option>\n");

													print("<optgroup label='$sep_val'>\n");
												}

												$old_sep_val=$result_array["separateur"];
											}

											// construction de la chaine affichée
											$description="";

											foreach($result_array as $colonne => $valeur)
											{
												if(isset($sep_ref_table_texte))
												{
													if($colonne != $table_pkey && $colonne!="separateur")
													{
														if(!empty($description))
															$description.=" - ";

														$description.= "$valeur";
													}
												}
												elseif($colonne != $table_pkey)
												{
													if(!empty($description))
														$description.=" - ";

													$description.= "$valeur";
												}
											}

											print("<option value='$value' label=\"$description\">$description</option>\n");
										}

										print("</select>\n");
									}
									else
									{
										$no_elements=1;

										print("<font class='Texte_menu'><b>Aucun</b>");
									}

									print("</td>
											</tr>
											</table>\n");

									db_free_result($result);
									db_close($dbr);

									print("<div class='centered_icons_box'>
												<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a></a>
												<input type='image' src='$__ICON_DIR/add_32x32_fond.png' alt='Ajouter' name='ajouter' value='Ajouter'>\n");

									if(!isset($no_elements))
										print("<input type='image' src='$__ICON_DIR/edit_32x32_fond.png' alt='Modifier' name='modifier' value='Modifier'>
												 <input type='image' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Supprimer' name='suppr' value='Supprimer'>\n");

									print("</form>
											</div>\n");

									break;
			}
		}

		if(isset($warning))
			message("$warning", $__WARNING);

		if(isset($_GET["succes"]))
			message("Opération effectuée avec succès", $__SUCCES);

	?>
</div>
<?php
	pied_de_page();
?>
</body></html>
