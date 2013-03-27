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

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth();

	if(!isset($_SESSION['tab_candidat']))
	{
		header("Location:login.php");
		exit();
	}

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__MOD_DIR/gestion/noaccess.php");
		exit();
	}

	// La fiche doit être verrouillée pour pouvoir ajouter une candidature manuellement
/*
	if($_SESSION['tab_candidat']['lock']!=1)
	{
		header("Location:edit_candidature.php");
		exit();
	}
*/	
	if(isset($_SESSION['tab_candidat']['civilite']))
	{
		switch($_SESSION['tab_candidat']['civilite'])
		{
			case "M" : 	$candidat="candidat";
							$article="le";
							$pronom="il";
							break;
	
			default	:	$candidat="candidate";
							$article="la";
							$pronom="elle";
							break;
		}
	}
	else
	{
		header("Location:index.php");
		exit();
	}

	if(isset($_POST["Suivant"]) || isset($_POST["Suivant_x"])) // validation du choix de la formation
	{
		$candidat_id=$_SESSION["candidat_id"];
		$candidature=$_POST["candidature"];

		if($candidature=="")
			$champ_vide=1;
		else
			$resultat=1;
	}
	elseif(isset($_POST["Valider"]) || isset($_POST["Valider_x"])) // validation du choix de la session
	{
		$candidat_id=$_SESSION["candidat_id"];
		$candidature=$_POST["candidature"];

		$vap_flag=$_POST["vap"];
		
		if($vap_flag=="")
			$vap_flag="0";

		if(!array_key_exists("session_id", $_POST))
		{
			$no_session=1;
			$resultat=1;
		}
		else
		{
			$session_id=$_POST["session_id"];

			if(array_key_exists("commission", $_POST))
				$com_date=$_POST["commission"];
			else
				$com_date=0;

			$dbr=db_connect();

			// vérification de l'unicité de la candidature pour ce candidat et cette période
			// Todo : ajouter l'unicité sur la session
			if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_cand
													WHERE	$_DBC_cand_propspec_id='$candidature'
													AND 	$_DBC_cand_candidat_id='$candidat_id'
													AND 	$_DBC_cand_periode='$__PERIODE'")))
			{
				$candidature_existe=1;
				db_close($dbr);
			}
			else
			{
				// détermination de l'ordre max des candidatures, ou de la spécialité si c'est une candidature à choix multiple
				// 1 : on détermine si on a une candidature à choix multiples

				$result=db_query($dbr,"SELECT $_DBC_groupes_spec_groupe
												FROM $_DB_groupes_spec
												WHERE $_DBC_groupes_spec_propspec_id='$candidature'");

				if(db_num_rows($result)) // un groupe a été trouvé. La contrainte fait qu'un seul groupe peut contenir ce couple
				{
					list($groupe_spec)=db_fetch_row($result,0);

					// 2 : ordre_spec max dans la table des précandidatures, pour le groupe donné
					// si l'ordre du groupe est déjà connu, on en profite pour le prendre en même temps (requête un peu barbare ?)

					$result2=db_query($dbr,"SELECT max($_DBC_cand_ordre_spec)+1, $_DBC_cand_ordre
														FROM $_DB_cand
														WHERE $_DBC_cand_candidat_id='$candidat_id'
														AND 	$_DBC_cand_groupe_spec='$groupe_spec'
														AND 	$_DBC_cand_periode='$__PERIODE'
													GROUP BY $_DBC_cand_ordre");

					$rows2=db_num_rows($result2);

					// list($ordre_spec)=db_fetch_row($result2,0); 	// si le max n'existe pas, la requête renvoie quand même un résultat, mais il est vide.

					if($rows2)
						list($ordre_spec, $ordre)=db_fetch_row($result2,0); 	// si le max n'existe pas, la requête renvoie quand même un résultat, mais il est vide.
					elseif(!isset($ordre_spec) || empty($ordre_spec)) // 1er ajout pour ce groupe de spécialités : ordre_spec=1
					{
						db_free_result($result2);
						$ordre_spec=1;

						// l'ordre global doit aussi être déterminé pour l'ajout de la candidature
						$result2=db_query($dbr,"SELECT max($_DBC_cand_ordre)+1 FROM $_DB_cand, $_DB_propspec
														WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
														AND $_DBC_cand_candidat_id='$candidat_id'
														AND $_DBC_cand_periode='$__PERIODE'
														AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'");
						if(db_num_rows($result2))
						{
							list($ordre)=db_fetch_row($result2,0);
		
							if(empty($ordre)) // 1ere candidature
								$ordre=1;
						}
						db_free_result($result2);

						// Formation manuelle ? => verrouillage immédiat

						$result2=db_query($dbr,"SELECT $_DBC_propspec_manuelle FROM $_DB_propspec
														WHERE $_DBC_propspec_id='$candidature'
														AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'");

						list($manuelle)=db_fetch_row($result2, 0);
						
						if($manuelle)
							$new_lock=1;

						db_free_result($result2);
					}
					else // ce groupe existe déjà
					{
						db_free_result($result2);

						// On doit alors récupérer la date de verrouillage (alignement des dates pour les choix multiples)
						$result2=db_query($dbr, "SELECT min($_DBC_cand_lockdate),$_DBC_cand_lock FROM $_DB_cand
														 WHERE $_DBC_cand_candidat_id='$candidat_id'
														 AND 	 $_DBC_cand_groupe_spec='$groupe_spec'
														 AND 	 $_DBC_cand_periode='$__PERIODE'
														 GROUP BY $_DBC_cand_lock");

						list($min_lockdate, $new_lock)=db_fetch_row($result2, 0);
						db_free_result($result2);

					}
				}
				else // précandidature à choix unique
				{
					$groupe_spec=$ordre_spec=-1;	// pas d'ordre pour le groupe de spécialité

					// on détermine l'ordre de la nouvelle précandidature
		
					$result2=db_query($dbr,"SELECT max($_DBC_cand_ordre)+1 FROM $_DB_cand, $_DB_propspec 
													WHERE $_DBC_cand_candidat_id='$candidat_id' 
													AND $_DBC_cand_propspec_id=$_DBC_propspec_id 
													AND $_DBC_propspec_comp_id='$_SESSION[comp_id]' 
													AND $_DBC_cand_periode='$__PERIODE'");

					list($ordre)=db_fetch_row($result2,0); // même s'il n'y a pas encore de candidature, la requête renvoie un résultat (vide)

					if(empty($ordre)) // 1ere candidature
						$ordre=1;

					db_free_result($result2);

					// Formation manuelle ? => verrouillage immédiat

					$result2=db_query($dbr,"SELECT $_DBC_propspec_manuelle FROM $_DB_propspec
													WHERE $_DBC_propspec_id='$candidature'
													AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'");

					list($manuelle)=db_fetch_row($result2, 0);

					if($manuelle)
						$new_lock=1;

					db_free_result($result2);

				}
					
				db_free_result($result);

				// Date de décision = date de la commission sélectionnée
				$date_decision=$com_date; 

				// $date_decision=0;

				// pour s'y retrouver dans le INSERT (TODO : changer la syntaxe de la requête en précisant l'ordre d'insertion)
				// $statut=$__PREC_EN_ATTENTE; <=== ???

				$statut=$__PREC_NON_TRAITEE;
				$lock=isset($new_lock) ? $new_lock : 0;
				$lockdate=isset($min_lockdate) ? $min_lockdate : maketime(5, 0, 0);

				$new_entretien_salle=$new_entretien_heure=$new_entretien_lieu=$motivation_decision=$liste_attente=$transmission_dossier="";
				$traitee_par="-2";

				// Date de modification du statut de recevabilité - initialisé à 0
				$new_date_prise_decision=$new_date_statut=$new_entretien_date=0;

				$decision=$recours=$masse=$talon_reponse=$statut_frais=$nb_rappels=$notification_envoyee=0;

				$candidature_id=db_locked_query($dbr, $_DB_cand, "INSERT INTO $_DB_cand VALUES('##NEW_ID##','$candidat_id','$candidature','$ordre','$statut','$motivation_decision','$traitee_par','$ordre_spec','$groupe_spec','$date_decision','$decision','$recours','$liste_attente','$transmission_dossier','$vap_flag','$masse','$talon_reponse','$statut_frais','$new_entretien_date','$new_entretien_heure','$new_entretien_lieu','$new_entretien_salle','$new_date_statut','$new_date_prise_decision','$__PERIODE','$session_id','$lock','$lockdate','$nb_rappels','$notification_envoyee')");

				write_evt($dbr, $__EVT_ID_G_PREC, "Ajout candidature $candidature_id (Formation $candidature)", $candidat_id, $candidature_id, "INSERT INTO $_DB_cand VALUES('$candidature_id','$candidat_id','$candidature','$ordre','$statut','$motivation_decision','$traitee_par','$lock','$ordre_spec','$groupe_spec','$date_decision','$decision','$recours','$liste_attente','$transmission_dossier','$vap_flag','$masse','$talon_reponse','$statut_frais','$new_entretien_date','$new_entretien_heure','$new_entretien_lieu','$new_entretien_salle','$new_date_statut','$new_date_prise_decision','$__PERIODE','$nb_rappels','$notification_envoyee')");

				db_close($dbr);
				header("Location:edit_candidature.php");
				exit();
			}
		}
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		print("<div class='infos_candidat Texte'>
					<strong>" . $_SESSION["tab_candidat"]["etudiant"] ." : " . $_SESSION["tab_candidat"]["civ_texte"] . " " . $_SESSION["tab_candidat"]["nom"] . " " . $_SESSION["tab_candidat"]["prenom"] .", " . $_SESSION["tab_candidat"]["ne_le"] . " " . $_SESSION["tab_candidat"]["txt_naissance"] ."</strong>
				 </div>

				<form action=\"$php_self\" method=\"POST\" name=\"form1\">\n");

		titre_page_icone("Ajouter manuellement une candidature pour l'année $__PERIODE-".($__PERIODE+1), "add_32x32_fond.png", 15, "L");

		message("Dans ce mode, les formations dont la <b>date limite de depôt</b> du dossier est <b>depassée</b> apparaissent <b>toujours</b> dans la liste.", $__WARNING);

		if(isset($no_session))
			message("Erreur : vous devez sélectionner une session de candidatures.", $__ERREUR);
	?>

	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'><b>Candidature</b></font>
		</td>
		<?php
			$dbr=db_connect();

			if(!isset($resultat))
			{
				print("<td class='td-droite fond_menu'>\n");

				$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_frais,
														$_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_mentions_nom, $_DBC_propspec_manuelle
												FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
											WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
											AND	$_DBC_propspec_annee=$_DBC_annees_id
											AND	$_DBC_mentions_id=$_DBC_specs_mention_id
											AND	$_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND	$_DBC_propspec_active='1'
											AND 	$_DBC_propspec_id NOT IN (SELECT $_DBC_cand_propspec_id FROM $_DB_cand
																						WHERE $_DBC_cand_candidat_id='$_SESSION[candidat_id]'
																						AND $_DBC_cand_periode='$__PERIODE')
												ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom");
				$rows=db_num_rows($result);

				// variables initialisées à n'importe quoi
				$prev_annee="--";
				$prev_mention="";

				if($rows)
				{
					print("<select size='1' name='candidature'>\n");

					for($i=0; $i<$rows; $i++)
					{
						list($propspec_id, $annee, $nom,$frais_dossiers, $mention, $finalite, $mention_nom, $manuelle)=db_fetch_row($result,$i);

						$nom_finalite=$tab_finalite[$finalite];

						$mention_nom=htmlspecialchars($mention_nom, ENT_QUOTES, $default_htmlspecialchars_encoding);

						if($annee!=$prev_annee)
						{
							if($i!=0)
								print("</optgroup>\n");

							if(empty($annee))
								print("<option value='' disabled=1></option>
											<optgroup label='-------------- Années particulières --------------'>\n");
							else
								print("<option value='' disabled=1></option>
											<optgroup label='-------------- $annee -------------- '>\n");

							print("<optgroup label='$mention_nom'>\n");

							$prev_annee=$annee;
						}
						elseif($prev_mention!=$mention)
							print("<option value='' disabled=1></option>
										<optgroup label='$mention_nom'>\n");

						if($frais_dossiers!="" && $frais_dossiers!=0)
							$frais_txt=" ($frais_dossiers euros)";
						else
							$frais_txt="";

						if(isset($candidature) && $candidature==$propspec_id)
							$selected="selected=1";
						else
							$selected="";

						if($manuelle)
							$manuelle_txt="(M)";
						else
							$manuelle_txt="";

						print("<option value='$propspec_id' label=\"$annee - $nom $nom_finalite $manuelle_txt$frais_txt\" $selected>$annee - $nom $nom_finalite $manuelle_txt$frais_txt</option>\n");

						$prev_mention=$mention;
					}

					print("</select>\n");
				}
				else
				{
					print("<font class='Texte_menu'><b>Aucune formation disponible.</b></font>\n");
					$no_formation=1;
				}

				db_free_result($result);

				print("</td>
						</tr>
						</table>

						<div class='centered_icons_box'>
							<a href='edit_candidature.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>\n");

					if(!isset($no_formation))
						print("<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='Suivant' value='Suivant'>\n");

					print("</form>
						</div>\n");

					if(isset($champ_vide))
						message("Formulaire incomplet : vous devez choisir une formation valide pour la candidature.", $__ERREUR);
					elseif(isset($candidature_existe))
						message("Erreur : une candidature pour cette formation existe déjà !", $__ERREUR);
			}
			else
			{
				$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite
												FROM $_DB_propspec, $_DB_annees, $_DB_specs
											WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
											AND	$_DBC_propspec_annee=$_DBC_annees_id
											AND 	$_DBC_propspec_id='$candidature'");

				list($propspec_id, $annee, $nom, $finalite)=db_fetch_row($result,0);

				$nom_finalite=$tab_finalite[$finalite];

				$formation=$annee=="" ? "$nom $nom_finalite" : "$annee $nom $nom_finalite";

				print("<td class='td-droite fond_menu2'>
							<input type='hidden' name='candidature'  value='$candidature'>
							<font class='Texte_menu2'><b>$formation</b></font>\n");
		?>
		</td>
	</tr>
		<?php
			$res_sessions=db_query($dbr, "SELECT $_DBC_session_id, $_DBC_session_ouverture, $_DBC_session_fermeture
														FROM $_DB_session
													WHERE $_DBC_session_propspec_id='$candidature'
													AND $_DBC_session_periode='$__PERIODE'
														ORDER BY $_DBC_session_ouverture");

			$rows_sessions=db_num_rows($res_sessions);

			// TODO : Réécrire le bloc session et commission

			if($rows_sessions)
			{
				$rowspan=$rows_sessions+1;

				print("<tr>
							<td class='td-gauche fond_menu2' rowspan='$rowspan' valign='top'>
								<font class='Texte_menu2'>
									<b>Sélection de la session de candidature : </b><br><i>Session grisée : candidature déjà existante</i>
								</font>
							</td>\n");

				for($i=0; $i<$rows_sessions; $i++)
				{
					list($session_id, $session_ouverture, $session_fermeture)=db_fetch_row($res_sessions, $i);

					$num_session=$i+1;
					$ouv_txt=date_fr("j F Y", $session_ouverture);
					$ferm_txt=date_fr("j F Y", $session_fermeture);

					// Par défaut, on sélectionne la session en cours
					if(time() >= $session_ouverture && time() <= $session_fermeture)
					{
						$checked="checked";
						$session_trouvee=1;
					}
					elseif($i==($rows_sessions-1) && !isset($session_trouvee)) // Aucune session trouvée : on garde la dernière
					{
						$checked="checked";
						$date_commission_probable=$session_fermeture+(10*86400);
						$date_commission_probable_txt=date_fr("j F Y", $date_commission_probable);
					}
					else
						$checked="";

					print("<tr>
								<td class='td-droite fond_menu' style='vertical-align:middle;'>
									<input style='padding-right:10px;' type='radio' name='session_id' value='$session_id' $checked>
									<font class='Texte_menu'>
										Session $num_session : du $ouv_txt au $ferm_txt
									</font>
								</td>
							</tr>\n");

					// Recherche de la fermeture de session la plus proche (pour déterminer une date de commission, au cas où)
					if(time()<$session_fermeture)
					{
						if(!isset($date_commission_probable))
						{
							$date_commission_probable=$session_fermeture+(10*86400);
							$date_commission_probable_txt=date_fr("j F Y", $date_commission_probable);
						}
					}
				}

				print("</tr>\n");

				if(!isset($session_trouvee)) // Aucune session n'a été trouvée
					print("<tr>
								<td class='td-gauche fond_menu2' valign='top'></td>
								<td class='td-droite fond_menu'>
									<font class='Texte_important_menu'>
										Attention : aucune session n'est actuellement ouverte.
										<br>Vous pouvez soit en forcer une, soit en créer une nouvelle via le menu \"Administration => Sessions de candidatures\".
									</font>
								</td>
								</tr>\n");
			}
			else // erreur : il FAUT une session pour chaque candidature, même pour les candidatures manuelles
				print("<tr>
							<td class='td-gauche fond_menu2' valign='top'>
								<font class='Texte_menu2'>
									<b>Sélection de la session de candidature : </b><br><i>Session grisée : candidature déjà existante</i>
								</font>
							</td>
							<td class='td-droite fond_menu'>
								<font class='Texte_important_menu'>
									<b>Erreur : aucune session disponible pour cette formation.</b>
									<br>Vous <b>devez</b> en créer une nouvelle via le menu \"Administration => Sessions de candidatures\".
								</font>
							</td>
						</tr>\n");

			// Idem pour la commission pédagogique

			$res_commissions=db_query($dbr, "SELECT $_DBC_commissions_id, $_DBC_commissions_date
														FROM $_DB_commissions
													WHERE $_DBC_commissions_propspec_id='$candidature'
													AND $_DBC_commissions_periode='$__PERIODE'
														ORDER BY $_DBC_commissions_date");

			$rows_commissions=db_num_rows($res_commissions);

			
			if($rows_commissions)
			{
				print("<tr>
							<td class='td-gauche fond_menu2' rowspan='$rows_commissions' valign='top'>
								<font class='Texte_menu2'>
									<b>Sélection de la Commission : </b>
								</font>
							</td>\n");

				for($i=0; $i<$rows_commissions; $i++)
				{
					list($commission_id, $commission_date)=db_fetch_row($res_commissions, $i);

					$num_commission=$i+1;
					$commission_txt=date_fr("j F Y", $commission_date);

					// Par défaut, on sélectionne la prochaine date
					if(time() <= $commission_date && !isset($commission_trouvee))
					{
						$checked="checked";
						$commission_trouvee=1;
					}
					elseif($i==($rows_sessions-1) && !isset($commission_trouvee))
						$checked="checked";
					else
						$checked="";

					if($i)
						print("<tr>\n");

					print("<td class='td-droite fond_menu' style='vertical-align:middle;'>
								<input style='padding-right:10px;' type='radio' name='commission' value='$commission_date' $checked>
								<font class='Texte_menu'>Commission $num_commission : $commission_txt</font>
							</td>
						</tr>\n");
				}
			}
			elseif(!$rows_commissions || !isset($commission_trouvee)) // Aucune commission n'a été trouvée
			{
				print("<tr>
							<td class='td-gauche fond_menu2' valign='top'>
								<font class='Texte_menu2'>
									<b>Sélection de la Commission : </b>
								</font>
							</td>
							<td class='td-droite fond_menu'>
								<font class='Texte_important_menu'>
									<b>Attention</b> : aucune commission à venir n'est actuellement paramétrée\n");

				if(isset($date_commission_probable_txt))
					print("<br>Par défaut, la date prise en compte sera le <b>$date_commission_probable_txt</b>
								<br><input type='hidden' name='commission' value='$date_commission_probable'>\n");
				else
					print("<br>Aucune date de commission n'a pu être trouvée (aucune session de candidatures).\n");

				print("<br>Vous pouvez en créer une nouvelle via le menu \"Administration => Dates de Commissions Pédagogiques\".
					</font>
				</td>
				</tr>\n");
			}

		?>
	<tr>
		<td class='td-gauche fond_menu2' style='border:1px;'>
			<font class='Texte_menu2'>
				<b>Candidat en situation de VAE ou VAP pour ce voeu ?</b>
			</font>
		</td>
		<td class='td-droite fond_menu'>
			<?php
				if(isset($vap_flag))
					$vap=$vap_flag;
				else
					$vap=0;

				if($vap=="" || $vap==0)
				{
					$yes_checked="";
					$no_checked="checked";
				}
				else
				{
					$yes_checked="checked";
					$no_checked="";
				}

				print("<input style='padding-right:10px;' type='radio' name='vap' value='1' style='vertical-align:middle;' $yes_checked>
						<font class='Texte_menu' style='vertical-align:bottom;'>Oui</font>
						<input style='padding-left:10px; padding-right:10px;' type='radio' name='vap' value='0' style='vertical-align:middle;' $no_checked>
						<font class='Texte_menu' style='vertical-align:bottom;'>Non</font>\n");
			?>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='edit_candidature.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type='image' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='Valider' value='Valider'>
		</form>
	</div>
	<?php
		} // Fin du else($resultat)
	?>
</div>
<?php
	pied_de_page();
?>
<script language="javascript">
	document.form1.candidature.focus()
</script>
</body></html>
