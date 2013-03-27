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
	session_name("preinsc");
	session_start();

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../../index.php");
		exit();
	}

	if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
	{
		session_write_close();
		header("Location:../composantes.php");
		exit();
	}

	$dbr=db_connect();

	// identifiant du destinataire en paramètre crypté
	if(isset($_GET["p"]) || ((isset($_POST["Suivant"]) || isset($_POST["Suivant_x"])) && isset($_POST["formation"]) && ctype_digit($_POST["formation"])))
	{
		if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
		{
			if(isset($params["to"]))
			{
				$_SESSION["to"]=$params["to"];

				// On vérifie que le destinataire existe bien
				$result=db_query($dbr,"SELECT $_DBC_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel,
														$_DBC_acces_absence_debut, $_DBC_acces_absence_fin, $_DBC_acces_absence_msg,
														$_DBC_acces_absence_active
												FROM $_DB_acces
												WHERE $_DBC_acces_id='$_SESSION[to]'");

				if(!db_num_rows($result))
				{
					db_free_result($result);
					$location="index.php?erreur=1";
				}
			}
			else
				$location="index.php";
		}
		elseif(isset($_POST["formation"]))
		{
			$formation=$_POST["formation"];

			// Une formation a été choisie : on regarde les personnes rattachées à cette dernière
			if($formation!=0)
			{
				// Nom de la formation
				$res_formation=db_query($dbr, "SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite
															 FROM $_DB_propspec, $_DB_specs, $_DB_annees
														 WHERE $_DBC_propspec_id='$formation'
														 AND $_DBC_propspec_annee=$_DBC_annees_id
														 AND $_DBC_specs_id=$_DBC_propspec_id_spec");

				if(db_num_rows($res_formation))
				{
					list($nom_annee, $nom_spec, $finalite)=db_fetch_row($res_formation, 0);
					$formation_txt=$nom_annee=="" ? htmlspecialchars("$nom_spec", ENT_QUOTES, $default_htmlspecialchars_encoding) : htmlspecialchars("$nom_annee $nom_spec", ENT_QUOTES, $default_htmlspecialchars_encoding);
					$formation_txt.=$finalite ? " - $tab_finalite[$finalite]" : "";
				}

				db_free_result($res_formation);

				// Récupération des informations du destinataire (avec l'éventuel message d'absence)
				$result=db_query($dbr, "SELECT $_DBC_courriels_propspec_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel,
														 $_DBC_acces_absence_debut, $_DBC_acces_absence_fin, $_DBC_acces_absence_msg,
														 $_DBC_acces_absence_active
													FROM $_DB_courriels_propspec, $_DB_acces
												WHERE $_DBC_courriels_propspec_acces_id=$_DBC_acces_id
												AND $_DBC_courriels_propspec_propspec_id='$formation'
												AND $_DBC_courriels_propspec_type='F'");
			}
			// Si aucun résultat ou si aucune formation n'a été sélectionnée, on sélectionne :
			// 1/ soit les utilisateurs attachés aux messages génériques
			// 2/ en cas d'echec au 1/, ceux ayant un niveau d'accès supérieur à la consultation et qui désirent recevoir les messages des scol (booléen dans la table acces)
			// TODO 11/01/08 : Créer un système de gestion d'aliases ?

			if((isset($result) && !db_num_rows($result)) || $formation==0)
			{
			   $result=db_query($dbr, "SELECT $_DBC_courriels_propspec_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel,
														 $_DBC_acces_absence_debut, $_DBC_acces_absence_fin, $_DBC_acces_absence_msg,
														 $_DBC_acces_absence_active
													FROM $_DB_courriels_propspec, $_DB_acces
												WHERE $_DBC_courriels_propspec_acces_id=$_DBC_acces_id
												AND $_DBC_courriels_propspec_propspec_id='$_SESSION[comp_id]'
												AND $_DBC_courriels_propspec_type='C'");
												
			   if(isset($result) && !db_num_rows($result) || !isset($result))
			   {
				  $result=db_query($dbr, "SELECT $_DBC_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel,
					 									   $_DBC_acces_absence_debut, $_DBC_acces_absence_fin, $_DBC_acces_absence_msg,
					 									   $_DBC_acces_absence_active
													  FROM $_DB_acces
												   WHERE ($_DBC_acces_composante_id='$_SESSION[comp_id]'
														    OR $_DBC_acces_id IN (SELECT $_DBC_acces_comp_acces_id FROM $_DB_acces_comp
																					   WHERE $_DBC_acces_comp_composante_id='$_SESSION[comp_id]'))
												   AND $_DBC_acces_niveau IN ('$__LVL_SCOL_MOINS','$__LVL_SCOL_PLUS','$__LVL_RESP','$__LVL_SUPER_RESP','$__LVL_ADMIN')
												   AND $_DBC_acces_reception_msg_scol='t'
												   GROUP BY $_DBC_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_courriel,
															   $_DBC_acces_absence_debut, $_DBC_acces_absence_fin, $_DBC_acces_absence_msg,
														 	  $_DBC_acces_absence_active");
				}
			}

			// Si on n'a toujours aucun destinataire potentiel, on redirige vers l'index de la messagerie
			if(isset($result) && !db_num_rows($result) || !isset($result))
			{
				db_free_result($result);

				$location="index.php?erreur=1";
			}
		}

		// Réponse à un message ? (paramètre facultatif)
		$reponse=(isset($params["r"]) && $params["r"]==1) ? "1" : "0";
	}

	if(isset($location)) // paramètre manquant : retour à l'index
	{
		db_close($dbr);
		
		session_write_close();
		header("Location:$location");
		exit();
	}

	if(isset($_POST["go"]) || isset($_POST["go_x"]))
	{
		$sujet=$_POST["sujet"];
		$corps=$_POST["corps"];

		if(!isset($_SESSION["to"]))
		{
			session_write_close();
			header("Location:index.php");
			exit();
		}

		if(ctype_digit($_SESSION["to"])) // destinataire unique : on complète artificiellement avec un séparateur ";"
			$_SESSION["to"].=";";

		$dests=explode(";", $_SESSION["to"]);

		$array_dests=array();

		foreach($dests as $i => $dest_id)
		{
			if(ctype_digit($dest_id))
			{
				$array_dests[$i]=array("id" => $dest_id);

				// Absences et réponses automatiques
				if(isset($_SESSION["absences"]) && array_key_exists($dest_id, $_SESSION["absences"]))
				{
					// on force le sens du message : candidat => gestion
					// TODO : réécrire la fonction write_msg pour éviter ça
					$_SESSION["auth_id"]=$dest_id;
					write_msg($dbr, array("id" => $_SESSION["auth_id"], "nom" => $_SESSION["absences"][$dest_id]["nom"], "prenom" => $_SESSION["absences"][$dest_id]["prenom"]),
										 array("0" => array("id" => $_SESSION["authentifie"])), "Absence : réponse automatique",
										 $_SESSION["absences"][$dest_id]["message"]);

					unset($_SESSION["auth_id"]);
				}
			}

			unset($_SESSION["absences"]);
		}

		$count_sent=write_msg($dbr, array("id" => $_SESSION["authentifie"], "nom" => $_SESSION["nom"], "prenom" => $_SESSION["prenom"]), $array_dests, $sujet, $corps);

		// db_close($dbr);
		session_write_close();
		header("Location:index.php?sent=1");
		exit();
	}

	en_tete_candidat();
	menu_sup_candidat($__MENU_MSG);
?>

<div class='main'>
	<div class='menu_gauche'>
		<ul class='menu_gauche'>
			<?php
				if(!isset($_SESSION["current_dossier"]))
					$current_dossier=$_SESSION["current_dossier"]=$__MSG_INBOX;
				else
					$current_dossier=$_SESSION["current_dossier"];

				dossiers_messagerie();
			?>
		</ul>
	</div>
	<div class='corps'>
		<?php
			titre_page_icone("Messagerie interne : Nouveau message", "email_32x32_fond.png", 15, "L");

			if(isset($result))
			{
				// Date et contenu du message source, en cas de réponse
				if($reponse && isset($_SESSION["msg_message"]) && isset($_SESSION["msg_id"]) && isset($_SESSION["msg_exp"]))
				{
					// Destinataire unique
					list($msg_dest_id, $msg_dest_nom, $msg_dest_prenom, $msg_dest_email, $absence_debut, $absence_fin,
						  $absence_msg, $absence_active)=db_fetch_row($result, 0);

					// Absence éventuelle du destinataire
					$_SESSION["absences"]=array();

					$now=time();

					if($absence_active=="t" && $now>=$absence_debut && $now<=$absence_fin)
						$_SESSION["absences"][$msg_dest_id]=array("nom" => $msg_dest_nom,
																				"prenom" => $msg_dest_prenom,
																				"message" => $absence_msg);

					if(strlen($_SESSION["msg_id"])==16) // Année sur un caractère
					{
						$date_offset=0;
						$annee_len=1;
						$leading_zero="0";
					}
					else
					{
						$date_offset=1;
						$annee_len=2;
						$leading_zero="";
					}

					// On convertit la date en temps Unix : plus simple ensuite pour l'affichage et les conversions
					$unix_date=mktime(substr($_SESSION["msg_id"], 5+$date_offset, 2), substr($_SESSION["msg_id"], 7+$date_offset, 2),
											substr($_SESSION["msg_id"], 9+$date_offset, 2), substr($_SESSION["msg_id"], 1+$date_offset, 2),
											substr($_SESSION["msg_id"], 3+$date_offset, 2), $leading_zero . substr($_SESSION["msg_id"], 0, $annee_len));

					$date_txt=$date_txt=date_fr("d/m/y à H\hi", $unix_date);;

					$msg_dest="$_SESSION[msg_exp]";

					$sujet=htmlspecialchars("Re: $_SESSION[msg_sujet]", ENT_QUOTES, $default_htmlspecialchars_encoding);
					$corps="\r\n\r\n\r\n\r\n\r\nLe $date_txt, $_SESSION[msg_exp] a écrit : \r\n";

					foreach($_SESSION["msg_message"] as $line)
						$corps.="> " . stripslashes($line);
				}
				else
				{
					$rows_destinataires=db_num_rows($result);

					// $msg_dest="$msg_dest_nom $msg_dest_prenom";
					$msg_dest="";
					$_SESSION["to"]="";
					$corps="\r\n";
					$sujet=isset($formation_txt) ? "[$formation_txt]" : "";

					$now=time();
					$_SESSION["absences"]=array();

					// Récupération du ou des destinataires
					for($i=0; $i<$rows_destinataires; $i++)
					{
						list($msg_dest_id, $msg_dest_nom, $msg_dest_prenom, $msg_dest_email, $absence_debut, $absence_fin,
							  $absence_msg, $absence_active)=db_fetch_row($result, $i);

						$_SESSION["to"].="$msg_dest_id;";

						// Absences
						if($absence_active=="t" && $now>=$absence_debut && $now<=$absence_fin)
							$_SESSION["absences"][$msg_dest_id]=array("nom" => $msg_dest_nom,
																					"prenom" => $msg_dest_prenom,
																					"message" => $absence_msg);

						$msg_dest.="$msg_dest_nom $msg_dest_prenom;";
					}

				}

				print("<form action='$php_self' method='POST' name='form1'>

							<table class='encadre_messagerie' width='95%' align='center'>
							<tr>
								<td class='td-msg-menu fond_menu2' style='padding:4px 2px 4px 2px;'>
									<font class='Texte_menu2'><b>Expéditeur :</b></font>
								</td>
								<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;'>
									<font class='Texte_menu'>$_SESSION[prenom] $_SESSION[nom]</font>
								</td>
							</tr>
							<tr>
								<td class='td-msg-menu fond_menu2' style='padding:4px 2px 4px 2px;'>
									<font class='Texte_menu2'><b>Destinataire :</b></font>
								</td>
								<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;'>
									<font class='Texte_menu'>$msg_dest</font>
								</td>
							</tr>
							<tr>
								<td class='td-msg-menu fond_menu2' style='padding:4px 2px 4px 2px;'>
									<font class='Texte_menu2'><b>Sujet :</b></font>
								</td>
								<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;'>
									<input type='text' name='sujet' value='$sujet' size='150' maxlength='140'>
								</td>
							</tr>
							<tr>
								<td class='td-msg fond_menu' style='vertical-align:top; background-color:white;' colspan='2'>
									<textarea name='corps' class='textArea' rows='18'>$corps</textarea>
								</td>
							</tr>
							</table>

							<div class='centered_icons_box'>
								<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
								<input type='image' src='$__ICON_DIR/mail_send_32x32_fond.png' alt='Envoyer' name='go' value='Envoyer'>
								</form>
							</div>\n");

				db_free_result($result);
			}
			else	// Le destinataire n'a pas été passé en paramètre, on propose la liste des destinataires potentiels dans cette composante
			{
				message("<center>Votre message sera adressé à la scolarité de l'établissement suivant :
							<br><b>$_SESSION[composante]</b>
							<br>Si ce n'est pas ce que vous souhaitez, vous pouvez sélectionner un autre établissement via le lien du menu supérieur.
							</center>", $__INFO);

				$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_id, $_DBC_annees_annee, $_DBC_specs_nom,
														$_DBC_specs_mention_id, $_DBC_mentions_nom, $_DBC_propspec_finalite
													FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
												WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
												AND $_DBC_propspec_annee=$_DBC_annees_id
												AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
												AND $_DBC_mentions_id=$_DBC_specs_mention_id
												AND $_DBC_propspec_active='1'
												AND $_DBC_propspec_manuelle='0'
													ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom");
				$rows=db_num_rows($result);

				// variables initialisées à n'importe quoi
				$prev_annee_id="--";
				$prev_mention="";

				if($rows)
				{
					print("<form action='$php_self' method='POST' name='form1'>
							<center>
								<font class='Texte'>Sélectionnez la formation concernée par votre message : </font>

								<select size='1' name='formation'>
									<option value='' disabled=1></option>
									<option value='0'>Message général ou formation hors liste de cet établissement</option>\n");

					for($i=0; $i<$rows; $i++)
					{
						list($propspec_id, $annee_id, $annee, $nom, $mention, $mention_nom, $finalite)=db_fetch_row($result,$i);

						$nom_finalite=$tab_finalite[$finalite];

						if($annee_id!=$prev_annee_id)
						{
							if($i!=0)
								print("</optgroup>\n");

							if($annee=="")
								$annee="Années particulières";

							print("<option value='' disabled=1></option>
										<optgroup label='-------------- $annee -------------- '>
										<optgroup label='$mention_nom'>\n");

							$prev_annee_id=$annee_id;
						}
						elseif($prev_mention!=$mention)
						{
							print("<option value='' disabled=1></option>
										<optgroup label='$mention_nom'>\n");
						}

						if(isset($candidature) && $candidature==$propspec_id)
							$selected="selected=1";
						else
							$selected="";

						if($annee=="Années particulières")
							print("<option value='$propspec_id' label=\"$nom $nom_finalite\" $selected>$nom $nom_finalite</option>\n");
						else
							print("<option value='$propspec_id' label=\"$annee $nom $nom_finalite\" $selected>$annee $nom $nom_finalite</option>\n");

						$prev_mention=$mention;
					}

					print("</select>
							</center>

							<div class='centered_box' style='padding-top:20px';>
								<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='Suivant' value='Valider'>
								</form></div>
						");
				}
				else
					message("Aucune formation disponible dans cette composante", $__ERREUR);

				db_free_result($result);
			}

			db_close($dbr);
		?>
		<script language="javascript">
			document.form1.corps.focus()
		</script>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>
</body></html>
