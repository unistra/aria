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
	include "$__INCLUDE_DIR_ABS/access_functions.php";
	include "modeles/include/modeles_fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	$dbr=db_connect();

	if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p']))) // identifiant du destinataire en paramètre crypté
	{
		if(isset($params["to"]))
		{
			$_SESSION["to"]=$params["to"];

			// On vérifie que le destinataire existe bien
			$result=db_query($dbr,"SELECT $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_email
												FROM $_DB_candidat
											WHERE $_DBC_candidat_id='$_SESSION[to]'");

			if(!db_num_rows($result))
				$location="index.php?erreur=1";
			else
			{
				list($_SESSION["msg_dest_civilite"],$_SESSION["msg_dest_nom"], $_SESSION["msg_dest_prenom"], $_SESSION["msg_dest_email"])=db_fetch_row($result,0);
				$resultat=1;
			}

			db_free_result($result);
		}
		else
			$location="index.php";

		// Réponse à un message ? (paramètre facultatif)
		if(isset($params["r"]) && $params["r"]==1)
			$_SESSION["reponse"]=1;
		else
			$_SESSION["reponse"]=0;
	}
	elseif(!isset($_SESSION["to"]))
		$location="index.php";

	if(isset($location)) // paramètre manquant : retour à l'index
	{
		db_close($dbr);
		header("Location:$location");
		exit();
	}

   // Ajout d'une pièce jointe
   if(isset($_POST["ajouter"]) || isset($_POST["ajouter_x"]))
	{
		$corps=preg_replace("/\\\/","", $_POST["corps"]);
		$sujet=preg_replace("/\\\/","", $_POST["sujet"]);

		// informations liées au fichier envoyé
		$file_name=$_FILES["fichier"]["name"];
		$file_size=$_FILES["fichier"]["size"];
		$file_tmp_name=$_FILES["fichier"]["tmp_name"];
		$file_error=$_FILES["fichier"]["error"]; // PHP > 4.2.0 uniquement

		$realname=html_entity_decode(validate_filename(mb_convert_encoding("$file_name", "UTF-8", mb_detect_encoding($file_name))), ENT_QUOTES, $default_htmlspecialchars_encoding);

		if($file_size>4194304)
			$trop_gros=1;
		elseif($file_name=="none" || $file_name=="" || !is_uploaded_file($_FILES["fichier"]["tmp_name"]))
			$fichier_vide=1;
		else
		{
		   // Répertoire temporaire dans lequel on va stocker les fichiers avant expédition
			if(!is_dir("$__ROOT_DIR/$__MOD_DIR/tmp/$_SESSION[auth_id]"))
				mkdir("$__ROOT_DIR/$__MOD_DIR/tmp/$_SESSION[auth_id]", 0770, 1);

			$destination_path="$__ROOT_DIR/$__MOD_DIR/tmp/$_SESSION[auth_id]/$file_name";

			$x=0;

			while(is_file("$destination_path")) // le fichier existe deja (très peu probable si le nettoyage est bien fait): on change le nom en ajoutant un numéro
			{
				$test_file_name=$x. "-$file_name";
				$destination_path="$__ROOT_DIR/$__MOD_DIR/tmp/$_SESSION[auth_id]/$test_file_name";

				$x++;
			}

			// DEBUG Uniquement
			// print("$file_tmp_name / $destination_path");

			if(!move_uploaded_file($file_tmp_name, $destination_path))
				$erreur_copie_fichier=1;
			else
			{
				$copie_ok=1;

				if(isset($test_file_name) && $test_file_name!="")
					$file_name=$test_file_name;

				if(isset($_SESSION["tmp_message_fichiers"]))
				{
					sort($_SESSION["tmp_message_fichiers"]);
					$cnt=count($_SESSION["tmp_message_fichiers"]);

					// Comparaison avec les fichiers déjà joints, pour éviter la duplication
					foreach($_SESSION["tmp_message_fichiers"] as $array_file)
					{
						if($array_file["sha1"]==sha1_file("$destination_path") && $array_file["size"]=="$file_size")
						{
							$dupe=1;
							break;
						}
					}
				}
				else
				{
					$_SESSION["tmp_message_fichiers"]=array();
					$cnt=0;
				}
				
				if(!isset($dupe))
					$_SESSION["tmp_message_fichiers"][$cnt]=array("file" => "$destination_path",
																				 "realname" => "$realname",
																				 "size" => "$file_size",
																				 "sha1" => sha1_file("$destination_path"));
			}
		}
		
		// Flag permettant d'éviter de remettre la signature en cas d'application d'un modèle
		$no_sign=1;
		$resultat=1;
	}
	elseif((isset($_POST["suppr"]) || isset($_POST["suppr_x"])) && isset($_SESSION["tmp_message_fichiers"]))
	{
		$corps=preg_replace("/\\\/","",$_POST["corps"]);
		$sujet=preg_replace("/\\\/","",$_POST["sujet"]);

		// Suppression d'une pièce jointe
		foreach($_POST["suppr"] as $file_num => $foo)
		{
			if(array_key_exists($file_num, $_SESSION["tmp_message_fichiers"]))
			{
				$filename=$_SESSION["tmp_message_fichiers"][$file_num]["file"];
				@unlink("$filename");
				unset($_SESSION["tmp_message_fichiers"][$file_num]);
			}
		}

		sort($_SESSION["tmp_message_fichiers"]);
		
		// Flag permettant d'éviter de remettre la signature
		$no_sign=1;
		$resultat=1;
	}


	if(isset($_POST["Appliquer"]) || isset($_POST["Appliquer_x"]))
	{
		$sujet=preg_replace("/\\\/","",$_POST["sujet"]);		

		// La variable current_corps conserve le texte d'origine, au cas où on voudrait annuler ou changer de modèle
		if(!isset($_SESSION["current_corps"]))
			$_SESSION["current_corps"]=$corps=$_POST["corps"];
		else
			$corps=$_SESSION["current_corps"];

		$new_modele_id=$_POST["modele_id"];

		if($new_modele_id!="")
		{
			$res_modele=db_query($dbr,"SELECT $_DBC_msg_modeles_intitule, $_DBC_msg_modeles_texte FROM $_DB_msg_modeles
												WHERE $_DBC_msg_modeles_id='$new_modele_id'
												AND $_DBC_msg_modeles_acces_id='$_SESSION[auth_id]'");

			if(db_num_rows($res_modele))
			{
				list($modele_intitule, $modele_texte)=db_fetch_row($res_modele, 0);

				$cand_array=__get_infos_candidat($dbr, $_SESSION["to"]);
				$cursus_array=__get_cursus($dbr, $_SESSION["to"]);

				$corps=traitement_macros($modele_texte, $cand_array, $cursus_array) . "\r\n" . $corps;

				// Si le sujet est encore vide, on le remplace par le titre du modèle sélectionné
				if(trim($sujet)=="")
					$sujet=$modele_intitule;
			}

			db_free_result($res_modele);
		}

		// Flag permettant d'éviter de remettre la signature en cas d'application d'un modèle
		$no_sign=1;
		$resultat=1;
	}
	elseif(isset($_POST["Annuler"]) || isset($_POST["Annuler_x"])) // Annulation du modèle
	{
		// unset($_SESSION["current_corps"]);
		$corps=$_SESSION["current_corps"];

		$no_sign=1;
		$resultat=1;
	}
	elseif((isset($_POST["valider"]) || isset($_POST["valider_x"]))
			&& isset($_SESSION["to"]) && isset($_SESSION["msg_dest_civilite"]) && isset($_SESSION["msg_dest_nom"])
			&& isset($_SESSION["msg_dest_prenom"]) && isset($_SESSION["msg_dest_email"]))
			
	{
		$sujet=$_POST["sujet"];
		$corps=$_POST["corps"];

		// Transformation des url en liens
      // Obsolète : transformé à l'affichage par la fonction parse_macros()
		// $corps=ereg_replace("[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]", "<a href=\"\\0\">\\0</a>", $corps);
      
      $array_pj=isset($_SESSION["tmp_message_fichiers"]) ? $_SESSION["tmp_message_fichiers"] : "";

		// Pour le moment : destinataire (candidat) unique
		$array_dest=array("0" => array("id" 	=> $_SESSION["to"],
												 "civ"	=> $_SESSION["msg_dest_civilite"],
												 "nom" 	=> $_SESSION["msg_dest_nom"],
												 "prenom"=> $_SESSION["msg_dest_prenom"],
												 "email"	=> $_SESSION["msg_dest_email"]));

		$count_sent=write_msg($dbr, array("id" => $_SESSION["auth_id"], "nom" => $_SESSION["auth_nom"], "prenom" => $_SESSION["auth_prenom"]), $array_dest, $sujet, $corps, $_SESSION["msg_dest"], $__FLAG_MSG_NOTIFICATION, $array_pj);

      // Suppression des fichiers joints temporaires
      if(isset($_SESSION["tmp_message_fichiers"]))
      {
         foreach($_SESSION["tmp_message_fichiers"] as $array_file)
            @unlink("$array_file[file]");

         unset($_SESSION["tmp_message_fichiers"]);
      }

		header("Location:index.php?sent=1");
		exit();
	}

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
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
			<li class='menu_gauche' style='margin-top:30px;'><a href='modeles/modele.php?a=1' class='lien_menu_gauche' target='_self'>Créer un modèle</a></li>
			<li class='menu_gauche'><a href='modeles/modele.php' class='lien_menu_gauche' target='_self'>Modifier un modèle</a></li>
			<li class='menu_gauche'><a href='modeles/suppr_modele.php' class='lien_menu_gauche' target='_self'>Supprimer un modèle</a></li>
			<li class='menu_gauche' style='margin-top:30px;'><a href='signature.php' class='lien_menu_gauche' target='_self'>Modifier votre signature</a></li>
			<li class='menu_gauche' style='margin-top:30px;'><a href='absence.php' class='lien_menu_gauche' target='_self'>Absence : répondeur</a></li>
		</ul>
	</div>
	<div class='corps'>
		<?php
			titre_page_icone("Messagerie interne : Nouveau message", "email_32x32_fond.png", 10, "L");
			
			if(isset($resultat))
			{
				if(!isset($corps))
					$corps="";

				// Signature de l'expéditeur, si complétée et activée
				if(!isset($no_sign))
				{
					$res_signature=db_query($dbr, "SELECT $_DBC_acces_signature_txt, $_DBC_acces_signature_active FROM $_DB_acces
															WHERE $_DBC_acces_id='$_SESSION[auth_id]'");

					if(db_num_rows($res_signature))
					{
						list($signature_txt, $signature_active)=db_fetch_row($res_signature, 0);

						if($signature_active=='t' && trim($signature_txt)!="")
							$corps="\r\n\r\n\r\n$signature_txt\r\n\r\n$corps";
					}

					db_free_result($res_signature);
				}

				// Date et contenu du message source, en cas de réponse
				if(isset($_SESSION["reponse"]) && $_SESSION["reponse"] && isset($_SESSION["msg_message"]) && isset($_SESSION["msg_id"]) && isset($_SESSION["msg_exp"]))
				{
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
					$unix_date=mktime(substr($_SESSION["msg_id"], 5+$date_offset, 2), substr($_SESSION["msg_id"], 7+$date_offset, 2), substr($_SESSION["msg_id"], 9+$date_offset, 2),
																substr($_SESSION["msg_id"], 1+$date_offset, 2), substr($_SESSION["msg_id"], 3+$date_offset, 2), $leading_zero . substr($_SESSION["msg_id"], 0, $annee_len));

					$date_txt=$date_txt=date_fr("d/m/y à H\hi", $unix_date);;

					// $_SESSION["msg_dest"]=$msg_dest="$_SESSION[msg_exp]";
					$_SESSION["msg_dest"]=$msg_dest="$_SESSION[msg_dest_prenom] $_SESSION[msg_dest_nom]";

					$sujet=htmlspecialchars("Re: $_SESSION[msg_sujet]", ENT_QUOTES, $default_htmlspecialchars_encoding);

					if(!isset($no_sign))
					{
						$corps.="\r\n\r\nLe $date_txt, $_SESSION[msg_exp] a écrit : \r\n";

						foreach($_SESSION["msg_message"] as $line)
							$corps.="> " . stripslashes($line);
					}
				}
				else
				{
					$_SESSION["msg_dest"]=$msg_dest="$_SESSION[msg_dest_nom] $_SESSION[msg_dest_prenom]";

					if(!isset($sujet))
						$sujet="";
				}

            if(isset($erreur_copie_fichier))
               message("Erreur : impossible de copier le fichier reçu. Merci de contacter l'administrateur.", $__ERREUR);
            elseif(isset($trop_gros))
               message("Erreur : le fichier envoyé est trop gros (max : 4 Mo)", $__ERREUR);
            else
               message("Attention au contenu du message envoyé : il engage <b>votre responsabilité</b> !", $__WARNING);

				print("<form enctype='multipart/form-data' action='$php_self' method='POST' name='form1'>
							<table class='encadre_messagerie' width='95%' align='center'>
							<tr>
								<td class='td-msg-menu fond_menu2' style='padding:4px 2px 4px 2px;'>
									<font class='Texte_menu2'><b>De :</b></font>
								</td>
								<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;'>
									<font class='Texte_menu'>$_SESSION[auth_prenom] $_SESSION[auth_nom]</font>
								</td>
							</tr>
							<tr>
								<td class='td-msg-menu fond_menu2' style='padding:4px 2px 4px 2px;'>
									<font class='Texte_menu2'><b>A :</b></font>
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
								<td class='td-msg-menu fond_menu2' style='padding:4px 2px 4px 2px;'>
									<font class='Texte_menu2'><b>Modèle réponse :</b></font>
								</td>
								<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;'>\n");

				// Utilisation des modèles de courriels
				$res_modeles=db_query($dbr, "SELECT $_DBC_msg_modeles_id, $_DBC_msg_modeles_intitule FROM $_DB_msg_modeles
															WHERE $_DBC_msg_modeles_acces_id='$_SESSION[auth_id]'
															ORDER BY $_DBC_msg_modeles_intitule");

				$rows_modeles=db_num_rows($res_modeles);

				if($rows_modeles)
				{
					print("<select name='modele_id'>
						<option value=''></option>\n");

					for($i=0; $i<$rows_modeles; $i++)
					{
						list($modele_id, $modele_intitule)=db_fetch_row($res_modeles, $i);

						if(isset($new_modele_id) && $new_modele_id==$modele_id)
						{
							$selected="selected='1'";
							$annulation_possible=1;
						}
						else
							$selected="";

						$modele_intitule=stripslashes(htmlspecialchars($modele_intitule, ENT_QUOTES, $default_htmlspecialchars_encoding));

						print("<option value='$modele_id' $selected>$modele_intitule</option>\n");
					}

					print("</select><input style='padding-right:20px;' type='submit' name='Appliquer' value='Appliquer'>\n");

					if(isset($annulation_possible))
						print("<input style='padding-right:20px;' type='submit' name='Annuler' value='Annuler ce modèle'>\n");

					print("<font class='Texte_menu' style='padding-right:20px;'><i>Attention : les modèles ne sont pas cumulables</i></font>\n");
			   }
				else
					print("<font class='Texte_menu'><i>Aucun modèle défini</i></font>\n");

				db_free_result($res_modeles);

				print("</td>
                </tr>
                <tr>
                   <td class='td-msg-menu fond_menu2' style='padding:4px 2px 4px 2px;'>
                      <font class='Texte_menu2'><b>Joindre un fichier :</b></font>
                   </td>
                   <td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;'>                   
                      <input type='hidden' name='MAX_FILE_SIZE' value='4194304'>
                      <input type='file' name='fichier' size='48'>
                      &nbsp;&nbsp;<input type='submit' name='ajouter' value='Ajouter ce fichier'>
                      &nbsp;&nbsp;<font class='Texte_menu2'>(<i>Limite : 4 Mo</i>)</font>
                   </td>
                </tr>");

             if(isset($_SESSION["tmp_message_fichiers"]) && count($_SESSION["tmp_message_fichiers"]))
             {
                sort($_SESSION["tmp_message_fichiers"]);

                print("<tr>
                          <td class='td-msg-menu fond_menu2' style='padding:4px 2px 4px 2px;'>
                             <font class='Texte_menu2'><b>Pièces jointes :</b></font>
                          </td>
                          <td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;'>
                             <table cellpadding='0' cellspacing='0' align='left' border='0'>\n");

                foreach($_SESSION["tmp_message_fichiers"] as $num_file => $array_file)
                   print("<tr>
                             <td class='fond_menu' style='padding:4px 2px 4px 2px;'>
                                <font class='Texte_menu2'>&#8226;&nbsp;&nbsp;$array_file[realname]</font>
                             </td>
                             <td class='fond_menu' style='padding:4px 2px 4px 2px;'>
                                <input type='image' src='$__ICON_DIR/trashcan_full_16x16_slick_menu2.png' alt='Supprimer' border='0' name='suppr[$num_file]' value='Supprimer'>
                             </td>
                          </tr>\n");

                print("</table>
                    </td>
                    </tr>\n");
             }
                
             print("<tr>
				          <td class='td-msg fond_blanc' style='vertical-align:top;' colspan='2'>
					          <textarea name='corps' class='textArea' rows='18'>$corps</textarea>
					       </td>
   				    </tr>
				       </table>

				       <div class='centered_icons_box'>
				          <a href='message.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
					       <input type='image' src='$__ICON_DIR/mail_send_32x32_fond.png' alt='Envoyer' name='valider' value='Envoyer'>
				       </div>
                   </form>\n");
         }  

         db_close($dbr);
		?>
		<script language="javascript">
			document.form1.corps.focus()
		</script>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
