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
	session_name("preinsc");
	session_start();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	$dbr=db_connect();

	if(!isset($_SESSION["authentifie"]) || !isset($_SESSION["comp_id"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}

	$candidat_id=$_SESSION["authentifie"];

	// *********************************************************************************
	// CHARGEMENT DES FILTRES
	$res_filtres=db_query($dbr, "SELECT $_DBC_filtres_id, $_DBC_filtres_cond_propspec_id, $_DBC_filtres_cond_annee_id,
													$_DBC_filtres_cond_mention_id, $_DBC_filtres_cond_spec_id, $_DBC_filtres_cond_finalite,
													$_DBC_filtres_cible_propspec_id, $_DBC_filtres_cible_annee_id, $_DBC_filtres_cible_mention_id,
													$_DBC_filtres_cible_spec_id, $_DBC_filtres_cible_finalite
												FROM $_DB_filtres
											WHERE $_DBC_filtres_comp_id='$_SESSION[comp_id]'
											AND $_DBC_filtres_actif='1'");

	$nb_filtres=db_num_rows($res_filtres);

	if($nb_filtres)
	{
		// Chargement des formations d�j� ajout�es par le candidat
		$res_formations=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_propspec_annee, $_DBC_specs_mention_id, $_DBC_propspec_id_spec,
														  $_DBC_propspec_finalite
												 	FROM $_DB_propspec, $_DB_specs
												 WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
												 AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
												 AND $_DBC_propspec_id IN (SELECT $_DBC_cand_propspec_id FROM $_DB_cand
																					WHERE $_DBC_cand_candidat_id='$_SESSION[authentifie]'
																					AND $_DBC_cand_periode='$__PERIODE')");

		$nb_formations=db_num_rows($res_formations);

		if($nb_formations) // si aucune formation : aucun filtre applicable
		{
			$tab_formations=array();

			// Stockage des formations dans un tableau
			for($i=0; $i<$nb_formations; $i++)
			{
				list($propspec_id, $annee_id, $mention_id, $spec_id, $finalite_id)=db_fetch_row($res_formations, $i);
				$tab_formations[$i]="$propspec_id:$annee_id:$mention_id:$spec_id:$finalite_id";
			}

			$_SESSION["filtres_regexp"]=array();

			// Pour chaque filtre, on construit une requ�te pour d�terminer les "cibles" (formations non s�lectionnables)
			for($i=0; $i<$nb_filtres; $i++)
			{
				list($filtre_id, $cond_propspec_id, $cond_annee_id, $cond_mention_id, $cond_spec_id, $cond_finalite,
					$cible_propspec_id, $cible_annee_id, $cible_mention_id, $cible_spec_id, $cible_finalite)=db_fetch_row($res_filtres, $i);

				// construction de l'expression rationnelle propre � ce filtre : 5 champs s�par�s par ":"
				// id_formation:id_ann�e:id_mention:id_sp�cialit�:id_finalit�
				// si un �l�ment vaut "-1", tous les identifiants peuvent correspondre : "[-0-9]+"

				if($cond_propspec_id!="-1") // formation enti�re : prioritaire : les 4 champs suivants sont ignor�s
					$regexp_filtre="$cond_propspec_id:[-0-9]+:[-0-9]+:[-0-9]+:[-0-9]+";
				else
				{
					$regexp_filtre="[-0-9]+:"; // propspec_id

					$regexp_filtre.=$cond_annee_id=="-1" ? "[-0-9]+:" : "$cond_annee_id:";
					$regexp_filtre.=$cond_mention_id=="-1" ? "[-0-9]+:" : "$cond_mention_id:";
					$regexp_filtre.=$cond_spec_id=="-1" ? "[-0-9]+:" : "$cond_spec_id:";
					$regexp_filtre.=$cond_finalite=="-1" ? "[-0-9]+" : "$cond_finalite";
				}

				// Test du filtre sur chaque formation d�j� ajout�e
				foreach($tab_formations as $une_formation)
				{		
					// Si un test est positif : le filtre s'applique, on sort de la boucle
					if(preg_match("/$regexp_filtre/", $une_formation))
					{
						// on stocke l'expression rationnelle de la cible du filtre

						if($cible_propspec_id!="-1") // formation enti�re prioritaire : les 4 champs suivants sont ignor�s
							$_SESSION["filtres_regexp"][$filtre_id]="$cible_propspec_id:[-0-9]+:[-0-9]+:[-0-9]+:[-0-9]+";
						else
						{
							$_SESSION["filtres_regexp"][$filtre_id]="[-0-9]+:"; // propspec_id

							$_SESSION["filtres_regexp"][$filtre_id].=$cible_annee_id=="-1" ? "[-0-9]+:" : "$cible_annee_id:";
							$_SESSION["filtres_regexp"][$filtre_id].=$cible_mention_id=="-1" ? "[-0-9]+:" : "$cible_mention_id:";
							$_SESSION["filtres_regexp"][$filtre_id].=$cible_spec_id=="-1" ? "[-0-9]+:" : "$cible_spec_id:";
							$_SESSION["filtres_regexp"][$filtre_id].=$cible_finalite=="-1" ? "[-0-9]+" : "$cible_finalite";
						}

						break;
					}
				}
			}
		}
		else
			unset($_SESSION["filtres_regexp"]);

		db_free_result($res_formations);
	}
	else
		unset($_SESSION["filtres_regexp"]);

	db_free_result($res_filtres);

	// *********************************************************************************
	if(isset($_POST["candidature"]) && isset($_POST["vap"]))
	{
		$_SESSION["propspec_id"]=$_POST["candidature"];
		$_SESSION["vap_flag"]=$_POST["vap"];
	}

	if(isset($_POST["valider2"]) || isset($_POST["valider2_x"]))	// validation du 2nd formulaire
	{
		if(!isset($_SESSION["propspec_id"]) || !isset($_SESSION["vap_flag"]) || (isset($_SESSION["propspec_id"]) && !ctype_digit($_SESSION["propspec_id"])))
		{
			db_close($dbr);

			unset($_SESSION["propspec_id"]);
			unset($_SESSION["vap_flag"]);

			header("Location:precandidatures.php");
			exit();
		}

		if($_SESSION["vap_flag"]!="0" && $_SESSION["vap_flag"]!="1")
			$_SESSION["vap_flag"]="0";

		if($_SESSION["propspec_id"]=="")
			$champ_vide=1;

		if(!isset($champ_vide))
		{
			// ==============================================================================
			// Mot de passe requis et correspondant � ce que le candidat a entr� ?
			$res_pass=db_query($dbr,"SELECT $_DBC_propspec_flag_pass, $_DBC_propspec_pass FROM $_DB_propspec
												WHERE $_DBC_propspec_id='$_SESSION[propspec_id]'");

			if(db_num_rows($res_pass))
				list($flag_pass, $propspec_pass)=db_fetch_row($res_pass, 0);
			else
				$flag_pass="f";

			db_free_result($res_pass);

			if($flag_pass=="t" && isset($propspec_pass) && isset($_POST["protection"]))
			{
				if(md5($_POST["protection"])!=$propspec_pass)
				{
					$erreur_pass=1;
					$formulaire_pass=1;
				}
				else
					$pass_ok=1;
			}
			elseif($flag_pass=="t" && !isset($_POST["protection"]))
				$formulaire_pass=1;

			// ==============================================================================
			
			if($flag_pass=="f" || ($flag_pass=="t" && isset($pass_ok) && $pass_ok==1 && !isset($erreur_pass)))
			{
				// v�rification de l'unicit� de la candidature pour ce candidat
				// TODO 2008 : RAJOUTER LA VERIFICATION DE LA SESSION EN COURS 

				$result=db_query($dbr,"SELECT * FROM $_DB_cand
												WHERE $_DBC_cand_propspec_id='$_SESSION[propspec_id]'
												AND $_DBC_cand_candidat_id='$candidat_id'
												AND $_DBC_cand_periode='$__PERIODE'");
				if(db_num_rows($result))
					$candidature_existe=1;

				db_free_result($result);

				if(!isset($candidature_existe))
				{
					// d�termination de l'ordre max des candidatures, ou de la sp�cialit� si c'est une candidature � choix multiple

					// 1 : on d�termine si on a une candidature � choix multiples et si le mode automatique est actif
					
					$result=db_query($dbr,"SELECT $_DBC_groupes_spec_groupe, $_DBC_groupes_spec_auto
   					                       FROM $_DB_groupes_spec
	   										  WHERE $_DBC_groupes_spec_propspec_id='$_SESSION[propspec_id]'");

					if(db_num_rows($result)) // un groupe comportant le couple (annee/sp�cialit�) a �t� trouv�. La contrainte fait qu'un seul groupe peut contenir ce couple
					{
						list($groupe_spec, $auto, $dates_communes)=db_fetch_row($result,0);

						// 2 : ordre_spec max dans la table des pr�candidatures, pour le groupe donn�
						// si l'ordre du groupe est d�j� connu, on en profite pour le prendre en m�me temps
						$result2=db_query($dbr,"(SELECT max($_DBC_cand_ordre_spec)+1 FROM $_DB_cand
																WHERE $_DBC_cand_candidat_id='$candidat_id'
																AND $_DBC_cand_groupe_spec='$groupe_spec'
																AND $_DBC_cand_periode='$__PERIODE')
														UNION ALL
															(SELECT $_DBC_cand_ordre FROM $_DB_cand
																WHERE $_DBC_cand_candidat_id='$candidat_id'
																AND $_DBC_cand_groupe_spec='$groupe_spec'
																AND $_DBC_cand_periode='$__PERIODE')");

						$rows2=db_num_rows($result2);

						list($ordre_spec)=db_fetch_row($result2,0); 	// si le max n'existe pas, la requ�te renvoie quand m�me un r�sultat, mais il est vide.
							
						if(!isset($ordre_spec) || empty($ordre_spec)) // 1er ajout pour ce groupe de sp�cialit�s : ordre_spec=1
						{
							db_free_result($result2);
							$ordre_spec=1;

							// l'ordre au sein de la composante doit aussi �tre d�termin�
							$result2=db_query($dbr,"SELECT max($_DBC_cand_ordre)+1 FROM $_DB_cand, $_DB_propspec
															WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
															AND $_DBC_cand_candidat_id='$candidat_id'
															AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
															AND $_DBC_cand_periode='$__PERIODE'");
							if(db_num_rows($result2))
							{
								list($ordre)=db_fetch_row($result2,0);
			
								if(empty($ordre)) // 1ere candidature
									$ordre=1;
							}
							db_free_result($result2);
						}
						else // ce groupe existe d�j�, donc on a pu r�cup�rer l'ordre dans la requ�te avec l'UNION
						{
							list($ordre)=db_fetch_row($result2,1);
							db_free_result($result2);

							// On doit alors r�cup�rer la date de verrouillage (alignement des dates pour les choix multiples)
							$result2=db_query($dbr, "SELECT min($_DBC_cand_lockdate) FROM $_DB_cand
																WHERE $_DBC_cand_candidat_id='$candidat_id'
																AND 	 $_DBC_cand_groupe_spec='$groupe_spec'
																AND 	 $_DBC_cand_periode='$__PERIODE'");

							list($min_lockdate)=db_fetch_row($result2, 0);
							db_free_result($result2);

							if($min_lockdate<time())
								$new_lock=1;
						}
						
						// Mode automatique : l'ajout d'une formation du groupe ajoute toutes les autres
						// 1 : on liste les formations qui ne sont pas encore ajout�es (normalement aucune, sauf si la modification de l'option a �t� faite 
						// en cours de session de candidatures
							
						if(isset($auto) && $auto=="t")
						{
						   $time=time();
						
						   $result_formations_groupe=db_query($dbr, "SELECT $_DBC_groupes_spec_propspec_id, $_DBC_specs_nom 
						                                                FROM $_DB_groupes_spec, $_DB_specs, $_DB_propspec
						                                             WHERE $_DBC_groupes_spec_groupe='$groupe_spec'
						                                             AND $_DBC_groupes_spec_propspec_id=$_DBC_propspec_id
						                                             AND $_DBC_propspec_id_spec=$_DBC_specs_id
						                                             AND $_DBC_groupes_spec_propspec_id!='$_SESSION[propspec_id]'
						                                             AND $_DBC_propspec_active='1'
						                                             AND $_DBC_groupes_spec_propspec_id IN (SELECT $_DBC_session_propspec_id FROM $_DB_session
						                                                                                    WHERE $_DBC_session_ouverture<='$time'
						                                                                                    AND $_DBC_session_fermeture>='$time')");
						                                                                                 
   						$rows_formations_groupe=db_num_rows($result_formations_groupe);
						
	   					$array_formations_groupe=array();
		  				
		   				if($rows_formations_groupe)
			   			{
				   		   for($f=0; $f<$rows_formations_groupe; $f++)
				   		   {
					   	      list($groupe_formations_propspec_id, $groupe_formations_spec_nom)=db_fetch_row($result_formations_groupe, $f);
					   	      
					   	      $array_formations_groupe[$groupe_formations_propspec_id]=$groupe_formations_spec_nom;
								}
   						}

                     db_free_result($result_formations_groupe);						   
						}
					}
					else // pr�candidature � choix unique
					{
						$groupe_spec=$ordre_spec=-1;	// pas d'ordre pour le groupe de sp�cialit�

						// on d�termine l'ordre de la nouvelle pr�candidature
		
						$result2=db_query($dbr,"SELECT max($_DBC_cand_ordre)+1 FROM $_DB_cand, $_DB_propspec
														WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
														AND $_DBC_cand_candidat_id='$candidat_id'
														AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
														AND $_DBC_cand_periode='$__PERIODE'");
						list($ordre)=db_fetch_row($result2,0); // m�me s'il n'y a pas encore de candidature, la requ�te renvoie un r�sultat (vide)

						if(empty($ordre)) // 1ere candidature
							$ordre=1;

						db_free_result($result2);
					}
					
					db_free_result($result);

					// D�termination de la session (id) et de la prochaine commission pour cette formation

					$date_courante=time();

					$result=db_query($dbr, "SELECT $_DBC_session_id, $_DBC_session_fermeture, $_DBC_session_periode
														FROM $_DB_session
													WHERE $_DBC_session_propspec_id='$_SESSION[propspec_id]'
													AND $_DBC_session_ouverture<='$date_courante'
													AND $_DBC_session_fermeture>='$date_courante'");

	//												AND $_DBC_session_periode='$__PERIODE'");

					// Normalement, on a TOUJOURS un r�sultat (session ouverte)
					if(db_num_rows($result))
						list($session_id, $session_fermeture, $session_periode)=db_fetch_row($result, 0);
					else
					{
						// Redirection vers l'index
						// TODO : envoyer un mail et afficher un message d'erreur ad�quat
						db_close($dbr);
						session_write_close();
						header("Location:precandidatures.php");
						exit();
					}

					db_free_result($result);

					// Date de d�cision (=de commission)
					$result=db_query($dbr, "SELECT min($_DBC_commissions_date) FROM $_DB_commissions
													WHERE $_DBC_commissions_propspec_id='$_SESSION[propspec_id]'
													AND $_DBC_commissions_date>'$date_courante'
													AND $_DBC_commissions_periode='$session_periode'");

					// Normalement, on a un r�sultat 
					if(db_num_rows($result))
						list($date_decision)=db_fetch_row($result, 0);

					// Pas de date : on envoie un mail et on prend la date de fermeture + 10 jours
					if(!isset($date_decision) || $date_decision=="")
					{
						// TODO : A VERIFIER, plut�t mettre un champ "Ind�termin�"
						// MAIL : rediriger vers les gestionnaires ?
						// mail($__EMAIL_ADMIN, "[Pr�candidatures] - Date de Commission", "Aucune date trouv�e pour la formation $_SESSION[propspec_id]");
						$date_decision=$session_fermeture+(10*86400);
					}

					db_free_result($result);

					// CALCUL DE LA DATE DU VERROUILLAGE POUR CETTE FORMATION
					$result=db_query($dbr, "SELECT $_DBC_composantes_delai_lock FROM $_DB_composantes
													WHERE $_DBC_composantes_id='$_SESSION[comp_id]'");

					if(db_num_rows($result))
						list($comp_verr_delai)=db_fetch_row($result, 0);

					db_free_result($result);

					// Si la valeur est mauvaise ou trop faible, on met 48 heures par d�faut
					if(!isset($min_lockdate))
					{
						if(!isset($comp_verr_delai) || (isset($comp_verr_delai) && (!ctype_digit($comp_verr_delai) || $comp_verr_delai<86400)))
							$comp_verr_delai=172800; // = 48 * 3600 secondes

						// date courante + d�lai de verrouillage
						$new_lockdate=time()+$comp_verr_delai;

						// on prend le jour suivant, � 5h du matin
						$jour_suivant=date("j", $new_lockdate)+1; 
						$mois=date("n",$new_lockdate);
						$annee=date("Y",$new_lockdate);
						$new_lockdate2=MakeTime(2,00,00,$mois,$jour_suivant,$annee);
					}
					else
						$new_lockdate2=$min_lockdate;

					// initialisation des valeurs pour s'y retrouver dans le INSERT
					// TODO : changer la syntaxe de la requ�te en pr�cisant l'ordre d'insertion
					$statut=$lock=$new_entretien_date=0;
					$new_entretien_salle=$new_entretien_heure=$new_entretien_lieu=$motivation_decision=$liste_attente=$transmission_dossier="";
					$traitee_par="-2";

					$new_date_prise_decision=$new_date_statut=$decision=$recours=$masse=$talon_reponse=$statut_frais=$nb_rappels=$notification_envoyee=0;

					$candidature_id=db_locked_query($dbr, $_DB_cand, "INSERT INTO $_DB_cand VALUES('##NEW_ID##','$candidat_id','$_SESSION[propspec_id]','$ordre','$statut','$motivation_decision','$traitee_par','$ordre_spec','$groupe_spec','$date_decision','$decision','$recours','$liste_attente','$transmission_dossier','$_SESSION[vap_flag]','$masse','$talon_reponse','$statut_frais','$new_entretien_date','$new_entretien_heure','$new_entretien_lieu','$new_entretien_salle','$new_date_statut','$new_date_prise_decision', '$session_periode', '$session_id', '$lock', '$new_lockdate2','$nb_rappels','$notification_envoyee')");

               // ajout automatique des autres formations, s'il y en a
               // seul l'ordre dans le groupe doit �tre incr�ment�, les autres variables sont identiques
               if(isset($array_formations_groupe) && is_array($array_formations_groupe) && count($array_formations_groupe))
               {
                  $_SESSION["array_formations_groupe"]=$array_formations_groupe;
                  
                  foreach($array_formations_groupe as $groupe_propspec_id => $groupe_formations_spec_nom)
                  {
                     $ordre_spec++;
                     $groupe_candidature_id=db_locked_query($dbr, $_DB_cand, "INSERT INTO $_DB_cand VALUES('##NEW_ID##','$candidat_id','$groupe_propspec_id','$ordre','$statut','$motivation_decision','$traitee_par','$ordre_spec','$groupe_spec','$date_decision','$decision','$recours','$liste_attente','$transmission_dossier','$_SESSION[vap_flag]','$masse','$talon_reponse','$statut_frais','$new_entretien_date','$new_entretien_heure','$new_entretien_lieu','$new_entretien_salle','$new_date_statut','$new_date_prise_decision', '$session_periode', '$session_id', '$lock', '$new_lockdate2','$nb_rappels','$notification_envoyee')");
						}
               }

					// Nom de la formation, pour l'historique
					$res_formation=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite
																FROM $_DB_propspec, $_DB_annees, $_DB_specs
															WHERE $_DBC_annees_id=$_DBC_propspec_annee
															AND $_DBC_specs_id=$_DBC_propspec_id_spec
															AND $_DBC_propspec_id='$_SESSION[propspec_id]'");

					if(db_num_rows($res_formation)) // inqui�tant si Faux
					{
						list($nom_annee, $nom_spec, $finalite)=db_fetch_row($res_formation, 0);

						$formation=$nom_annee=="" ? "$nom_spec" : "$nom_annee $nom_spec";
						$formation=$tab_finalite[$finalite]=="" ? $formation : "$formation $tab_finalite[$finalite]";

						// $formation=stripslashes(str_replace("'","''", mb_strtoupper($formation)));

						write_evt("", $__EVT_ID_C_PREC, "Ajout candidature : $formation", $candidat_id, $candidature_id);
					}

					db_free_result($res_formation);

					// INFORMATIONS SUR CETTE FORMATION
					// Soit des informations propres (propspec.info), soit des renseignements compl�mentaires � compl�ter

					$result=db_query($dbr, "SELECT $_DBC_propspec_info FROM $_DB_propspec WHERE $_DBC_propspec_id='$_SESSION[propspec_id]'");
					list($propspec_info)=db_fetch_row($result, 0);
					db_free_result($result);

					// Renseignements compl�mentaires
					$count=db_num_rows(db_query($dbr, "SELECT * FROM $_DB_dossiers_ef
																WHERE $_DBC_dossiers_ef_propspec_id='$_SESSION[propspec_id]'"));
/*
					if(trim($propspec_info)!="" || $count)
					{
						// on conserve la date de verrouillage pour pr�venir le candidat, s'il y a des infos � compl�ter dans "Autres renseignements".
*/
						$_SESSION["info_lockdate"]=$new_lockdate2;

						db_close($dbr);

						header("Location:info_formation.php?vap=$_SESSION[vap_flag]");
						exit();
/*
					}

					db_close($dbr);
					header("Location:precandidatures.php");
					exit();
*/
				}
			} // fin du if(pass ...)
		}
	}

	$def=isset($_POST["initiale"]) && $_POST["initiale"]=="Formation Initiale" ? "1" : "0";
	$def=isset($_POST["continue"]) && $_POST["continue"]=="Formation Continue" ? "2" : $def;
	$def=isset($_POST["vae"]) && stripslashes($_POST["vae"])=="VAE : Validation des Acquis de l'Exp�rience" ? "3" : $def;
	$def=isset($_POST["vap"]) && $_POST["vap"]=="VAP : Validation des Acquis Professionnels" ? "4" : $def;

	en_tete_candidat();
	menu_sup_candidat($__MENU_FICHE);
?>

<div class='main'>
	<?php
		titre_page_icone("Ajouter une pr�candidature (attention � l'ann�e universitaire !)", "add_32x32_fond.png", 15, "L");

		// Formulaire permettant de demander le mot de passe prot�geant �ventuellement la formation choisie
		if(isset($formulaire_pass) && isset($flag_pass) && $flag_pass=="t")
		{
			message("<center>
							Cette formation est prot�g�e par un mot de passe.
							<br>Si vous ne le connaissez pas, merci de <strong>vous adresser � la scolarit� de la composante</strong>.
						</center>", $__INFO);

			if(isset($erreur_pass) && $erreur_pass==1)
				message("Erreur : mot de passe incorrect", $__ERREUR);

			print("<form action='$php_self' method='POST' name='form1'>
					<table cellpadding='4' align='center'>
					<tr>
						<td class='td-complet fond_menu2' colspan='2' nowrap='true'>
							<font class='Texte_menu2'><b>Formation prot�g�e : veuillez entrer le mot de passe</b></font>
						</td>
					</tr>
					<tr>
						<td class='td-gauche fond_menu2' align='center' nowrap='true'>
							<font class='Texte_menu2'>Mot de passe : </font>
						</td>
						<td class='td-gauche fond_menu2' align='center' nowrap='true'>
							<input type='password' name='protection' value='' maxlength='24' size='32'>
						</td>
					</tr>
					</table>

					<div class='centered_icons_box'>
						<a href='precandidatures.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
						<input type='image' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='valider2' value='Valider'>
						</form>
					</div>

					<script language='javascript'>
						document.form1.protection.focus()
					</script>\n");
		}
		else
		{
			if(isset($_SESSION["limite_nombre"]) && $_SESSION["limite_nombre"]>0)
			{
				// On calcule le nombre de candidatures d�j� d�pos�es (on ne compte pas celles qui ont �t� annul�es)
				$result=db_query($dbr, "SELECT count(*) FROM $_DB_cand, $_DB_propspec
												WHERE $_DBC_cand_candidat_id='$candidat_id'
												AND $_DBC_propspec_id=$_DBC_cand_propspec_id
												AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
												AND $_DBC_cand_periode='$__PERIODE'
												AND $_DBC_cand_statut!='$__PREC_ANNULEE'");

				list($nb_candidatures)=db_fetch_row($result, 0);

				db_free_result($result);

				if($nb_candidatures=="")
					$nb_candidatures=0;

				$nb_ajoutables=$_SESSION["limite_nombre"]-$nb_candidatures;

				if(!$nb_ajoutables)
				{
					message("Vous ne pouvez plus ajouter de voeu dans cette composante ; limite ($_SESSION[limite_nombre]) atteinte.</b>", $__ERREUR);
					$limite_atteinte=1;

					print("<div class='centered_box' style='padding-top:20px;'>
								<a href='precandidatures.php' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
							</div>");
				}
				else
				{
					$voeu=$nb_ajoutables==1 ? "voeu" : "voeux";

					$message="Vous pouvez encore ajouter <b>$nb_ajoutables $voeu</b> dans cette composante.";
					$type_message=$__INFO;
				}
			}

			if(isset($champ_vide))
				message("Formulaire incomplet : vous devez choisir une formation valide pour votre pr�candidature.", $__ERREUR);
			elseif(isset($candidature_existe))
				message("Erreur : votre pr�candidature pour cette formation existe d�j� !", $__ERREUR);
					
			if(!isset($limite_atteinte))
			{
				// Ajout possible si les dates limites ne sont pas d�pass�es
				$date_courante=time();

				if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_session, $_DB_propspec
															WHERE $_DBC_session_propspec_id=$_DBC_propspec_id
															AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
															AND $_DBC_session_ouverture<='$date_courante'
															AND $_DBC_session_fermeture>='$date_courante'")))

	//														AND $_DBC_session_periode='$__PERIODE'")))
				{
					message("Aucune pr�candidature ne peut �tre d�pos�e dans cette composante car toutes les dates limites sont d�pass�es", $__ERREUR);

					print("<div class='centered_box' style='padding-top:20px;'>
								<a href='precandidatures.php' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
							</div>");
				}
				else
				{
					$message="Les formations pour lesquelles aucune session de candidatures n'est actuellement ouverte n'apparaissent pas dans la liste.";

					if(isset($_SESSION["limite_annee"]) && $_SESSION["limite_annee"]==1)
						$message.="<br>- CANDIDATURES LIMITEES : dans cette composante, vous ne pouvez ajouter de candidatures que pour <b>une seule ann�e</b> (L3, M1, ...) au choix.";

					if(isset($_SESSION["limite_annee_mention"]) && $_SESSION["limite_annee_mention"]==1)
						$message.="<br>- CANDIDATURES LIMITEES : dans cette composante, les candidatures sont limit�es � <b>un seul Niveau</b> (L3, M1, ...) par <b>Mention</b>";

					message($message, $__WARNING);

					// message("Les formations dont la <b>date limite de dep�t</b> du dossier est <b>depass�e</b> n'apparaissent plus dans la liste.", $__INFO);
					// message("N'oubliez pas d'ordonner vos <b>Candidatures ext�rieures</b> (onglet 6) en fonction de vos candidatures � l'UFR !", $__WARNING);

					print("<br>
								<form action='$php_self' method='POST' name='form1'>\n");
				?>
				<br>
				<table cellpadding='4' align='center'>
				<tr>
					<td class='td-gauche fond_menu2' align='center' nowrap="true">
						<font class='Texte_menu2'><b>Composante : </b></font>
					</td>
					<td class='td-droite fond_menu2'>
						<font class='Texte_menu2'><b><?php echo $_SESSION["composante"]; ?><b></font>
					</td>
				</tr>
				<tr>
					<td class='td-gauche fond_menu2' align='center' nowrap="true">
						<font class='Texte_menu2'><b>Choix de la formation : </b></font>
					</td>
					<td class='td-droite fond_menu'>
						<?php
							// date courante pour ne selectionner que les formations actives
							// dont la date limite n'est pas encore depassee
							$date_courante=time();

							// Limites �ventuelles
							if(isset($_SESSION["limite_annee_mention"]) && $_SESSION["limite_annee_mention"]==1)
							{
								$result=db_query($dbr, "SELECT $_DBC_propspec_annee, $_DBC_specs_mention_id
																	FROM $_DB_propspec, $_DB_specs
																WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
																AND $_DBC_propspec_id IN (SELECT $_DBC_cand_propspec_id FROM $_DB_cand
																									WHERE $_DBC_cand_candidat_id='$candidat_id'
																									AND $_DBC_cand_periode='$__PERIODE'
																									AND $_DBC_cand_statut!='$__PREC_ANNULEE')
																AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'");

								// Si aucun r�sultat : la limite n'est pas encore applicable
								if($rows=db_num_rows($result))
								{
									$condition_annee_mention=array();

									// on examine chaque couple ann�e/mention des candidatures d�j� ajout�es
									for($i=0; $i<$rows; $i++)
									{
										list($limite_annee, $limite_mention)=db_fetch_row($result, $i);

										$condition_annee_mention[$limite_mention]=$limite_annee;
									}
								}

								db_free_result($result);
							}

							if(isset($_SESSION["limite_annee"]) && $_SESSION["limite_annee"]==1)
							{
								$result=db_query($dbr, "SELECT $_DBC_propspec_annee FROM $_DB_propspec
																WHERE $_DBC_propspec_id IN (SELECT $_DBC_cand_propspec_id FROM $_DB_cand
																										WHERE $_DBC_cand_candidat_id='$candidat_id'
																										AND $_DBC_cand_periode='$__PERIODE'
																										AND $_DBC_cand_statut!='$__PREC_ANNULEE')
																AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'");

								// Si aucun r�sultat : la limite n'est pas encore applicable
								if(db_num_rows($result))
								{
									list($annee_limite)=db_fetch_row($result, 0);
									$condition_annee="AND $_DBC_propspec_annee='$annee_limite' ";
								}
								else
									$condition_annee="";

								db_free_result($result);
							}
							else
								$condition_annee="";

							$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_id, $_DBC_annees_annee, $_DBC_specs_id,
																	$_DBC_specs_nom, $_DBC_propspec_frais, $_DBC_specs_mention_id, $_DBC_mentions_nom,
																	$_DBC_propspec_finalite, $_DBC_session_periode, $_DBC_propspec_flag_pass
																FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions, $_DB_session
															WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
															AND $_DBC_propspec_annee=$_DBC_annees_id
															AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
															AND $_DBC_propspec_id=$_DBC_session_propspec_id
															AND $_DBC_propspec_active='1'
															AND $_DBC_propspec_manuelle='0'
															AND $_DBC_mentions_id=$_DBC_specs_mention_id
															$condition_annee
															AND $_DBC_session_ouverture<='$date_courante'
															AND $_DBC_session_fermeture>='$date_courante'
															AND $_DBC_propspec_id NOT IN (SELECT $_DBC_cand_propspec_id FROM $_DB_cand
																									WHERE $_DBC_cand_candidat_id='$candidat_id'
																									AND $_DBC_cand_periode='$__PERIODE')
																ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom");

															// AND $_DBC_session_periode='$__PERIODE'

							$rows=db_num_rows($result);

							// variables initialis�es � n'importe quoi
							$prev_annee_id="--";
							$prev_mention="";

							// Pour info � destination du candidat
							$liste_formations_filtrees="";

							if($rows)
							{
								for($i=0; $i<$rows; $i++)
								{
									list($propspec_id, $annee_id, $annee, $spec_id, $nom, $frais_dossiers, $mention, $mention_nom,
										$finalite, $session_periode, $flag_pass)=db_fetch_row($result,$i);

									$nom_finalite=$tab_finalite[$finalite];

									if(isset($_SESSION["filtres_regexp"]))
									{
										// Premi�re �tape : applications des filtres d�finis par la composante
											
										$regexp_formation="$propspec_id:$annee_id:$mention:$spec_id:$finalite";

										$filtree=0;

										foreach($_SESSION["filtres_regexp"] as $filtre_id => $filtre_regexp)
										{
											if(preg_match("/$filtre_regexp/", $regexp_formation))
											{
												$filtree=1;
												$liste_formations_filtrees.=$annee=="" ? "$nom " . $tab_finalite[$finalite] : "$annee $nom " . $tab_finalite[$finalite] . "<br>\n";
												break;
											}
										}
									}
									else
										$filtree=0;

									if(!$filtree)
									{
										if(!isset($condition_annee_mention[$mention]) || (isset($condition_annee_mention[$mention]) && $condition_annee_mention[$mention]==$annee_id))
										{
											$mention_nom=htmlspecialchars($mention_nom, ENT_QUOTES, $default_htmlspecialchars_encoding);
										
											if(!isset($prem))
											{
												print("<select size='1' name='candidature'>\n");
												$prem=1;
											}

											if($annee_id!=$prev_annee_id)
											{
												if($i!=0)
													print("</optgroup>\n");

												if($annee=="")
													$annee="Ann�es particuli�res";

												print("<option value='' disabled=1></option>
															<optgroup label='-------------- $annee -------------- '></optgroup>
															<optgroup label='$mention_nom'>\n");

												$prev_annee_id=$annee_id;
											}
											elseif($prev_mention!=$mention)
											{
												print("<option value='' disabled=1></option>
															<optgroup label='$mention_nom'>\n");
											}

											$frais_txt=($frais_dossiers!="" && $frais_dossiers!=0) ? " (Frais : $frais_dossiers euros)" : "";

											$selected=(isset($_SESSION["propspec_id"]) && $_SESSION["propspec_id"]==$propspec_id) ? "selected=1" : "";

											// si la session concerne une autre ann�e, on l'indique
											$txt_periode=($session_periode!=$__PERIODE) ? " ($session_periode-".($session_periode+1).")" : "";
											
											// Si la formation est prot�g�e par un mot de passe, on l'indique aussi
											$formation_protegee=$flag_pass=="t" ? " (mot de passe n�cessaire)" : "";

											$nom_formation=($annee=="Ann�es particuli�res") ? "$nom" : "$annee $nom";

											print("<option value='$propspec_id' label=\"$nom_formation $nom_finalite$frais_txt$formation_protegee$txt_periode\" $selected>$nom $nom_finalite$frais_txt$formation_protegee$txt_periode</option>\n");

											$prev_mention=$mention;
										}
									} // fin du if($filtree)
								}
								if(isset($prem))
									print("</optgroup>
										</select>\n");
							}

							if((!$rows) || ($rows && !isset($prem)))
							{
								print("<font class='Texte_menu'><b>Aucune formation disponible.</b></font>\n");
								$no_formation=1;
							}

							db_free_result($result);
						?>
					</td>
				</tr>
				<tr>
					<td colspan='2' height='10'></td>
				</tr>
				<tr>
					<td class='td-gauche fond_menu2'>
						<font class='Texte_menu2'>
							<b>Etes-vous en situation de VAE ou VAP pour ce voeu ?</b>
							<br><i>(Validation des Acquis de l'Exp�rience
							<br>ou Validation des Acquis Professionnels)</i>
						</font>
					</td>
					<td class='td-droite fond_menu'>
						<font class='Texte_menu'>
							<?php
								$vap=isset($_SESSION["vap_flag"]) ? $_SESSION["vap_flag"] : "0";

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

								print("<input type='radio' name='vap' value='1' $yes_checked>&nbsp;Oui
										&nbsp;&nbsp;<input type='radio' name='vap' value='0' $no_checked>&nbsp;Non\n");
							?>
						</font>
					</td>
				</tr>
				<?php
					if(isset($liste_formations_filtrees) && $liste_formations_filtrees!="")
					{
						print("<tr>
									<td colspan='2' height='10'></td>
								</tr>
								<tr>
									<td class='td-gauche fond_page' colspan='2'>
										<font class='Texte'>
											<i><strong>Les choix d�j� effectu�s ne vous permettent plus de s�lectionner la ou les formations suivantes :</strong>
											<br>
											$liste_formations_filtrees
											</i>
										</font>
									</td>
								</tr>\n");
					}
				?>
				</table>

				<br><br>

				<table border='0' align='center' width='60%'>
				<tr>
					<td valign='top' align='justify'>
						<font class='Texte'>
							<b><u>D�finitions utiles :</u></b>
							<br>
							<?php
								$form_initiale="<input type='submit' class='texte_corps' style='font-weight:bold' name='initiale' alt='Formation Initiale' value='Formation Initiale'>";
								$form_continue="<input type='submit' class='texte_corps' style='font-weight:bold' name='continue' alt='Formation Continue' value='Formation Continue'>";
								$form_vae="<input type='submit' class='texte_corps' style='font-weight:bold' name='vae' alt=\"VAE : Validation des Acquis de l'Exp�rience\" value=\"VAE : Validation des Acquis de l'Exp�rience\">";
								$form_vap="<input type='submit' class='texte_corps' style='font-weight:bold' name='vap' alt='VAP : Validation des Acquis Professionnels' value='VAP : Validation des Acquis Professionnels'>";

								if(isset($def) && $def==1)
									print("<br>- <b>Formation Initiale</b>
											<div style='padding-left:20px'>
												Vous �tes �tudiant en <b>Formation Initiale</b> :
												<br>&#8226;&nbsp;&nbsp;si vous n'avez pas interrompu vos �tudes depuis plus de deux ans
												<br>&#8226;&nbsp;&nbsp;si vous n'avez pas interrompu vos �tudes et travaillez � temps partiel ou complet
											</div>
											<br>\n");
								else
									print("<br>- $form_initiale\n");

								if(isset($def) && $def==2)
									print("<br><br>- <b>Formation Continue</b>
											<div style='padding-left:20px'>
												Vous �tes �tudiant en <b>Formation Continue</b> :
												<br>&#8226;&nbsp;&nbsp;si vous avez interrompu vos �tudes depuis plus de deux ans
												<br>&#8226;&nbsp;&nbsp;si vous �tes salari� ou en cong� individuel de formation, ou en recherche d'emploi
												<br>&#8226;&nbsp;&nbsp;si vous candidatez sur une formation professionnalis�e
											</div>
											<br>\n");
								else
									print("<br>- $form_continue\n");

								if(isset($def) && $def==3)
									print("<br><br>- <b>VAE : Validation des Acquis de l'Exp�rience</b>
											<div style='padding-left:20px'>
												La validation des acquis de l'exp�rience (VAE) permet de faire reconna�tre son exp�rience (professionnelle
												ou non) afin d'obtenir un dipl�me, un titre ou un certificat de qualification professionnelle. Dipl�mes, titres
												et certificats sont ainsi accessibles gr�ce � l'exp�rience (et non uniquement par le biais de la formation initiale
												ou continue), selon d'autres modalit�s que l'examen.
												<div style='padding-left:20px'>
													<br><u>Pour plus d'informations :</u>
													<br>- &nbsp;<a href='http://www.vae.gouv.fr' target='_blank' class='lien_bleu_12'>Consulter le Portail de la VAE</a>
												</div>
											</div>
											<br>\n");
								else
									print("<br>- $form_vae\n");

								if(isset($def) && $def==4)
									print("<br><br>- <b>VAP : Validation des Acquis Professionnels</b>
											<div style='padding-left:20px'>
												Elle permet un acc�s d�rogatoire aux diff�rents niveaux de formations de l'enseignement, par validation des �tudes, des exp�riences professionnelles et/ou des acquis personnels.
												<br><br>Peuvent donner lieu � validation :
												<br>- &nbsp;toute formation suivie par le candidat dans un �tablissement ou une structure de formation publique ou priv�e, quels qu'en aient �t� les modalit�s, la dur�e et le mode de sanction;
												<br>- &nbsp;l'exp�rience professionnelle acquise au cours d'une activit� salari� ou non, ou d'un stage ;
												<br>- &nbsp;les connaissances et les aptitudes acquises lors de tout syst�me de formation.
											</div>\n");
								else
									print("<br>- $form_vap\n");
							?>
						</font>
					</td>
				</tr>
				</table>

				<div class='centered_icons_box'>
					<a href='precandidatures.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
					<?php
						if(!isset($no_formation))
							print("<input type='image' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='valider2' value='Valider'>\n");
					?>
					</form>
				</div>

				<script language="javascript">
					document.form1.candidature.focus()
				</script>

		<?php
				}
			}
		} // fin du else(flag_pass)
		db_close($dbr);
	?>
</div>
<?php
	pied_de_page_candidat();
?>
</body></html>
