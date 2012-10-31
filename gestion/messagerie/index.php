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

	$dbr=db_connect();

	// Déverrouillage, au cas où
	if(isset($_SESSION["candidat_id"]))
		cand_unlock($dbr, $_SESSION["candidat_id"]);

	// Nettoyage
	unset($_SESSION['msg_sujet']);
	unset($_SESSION['msg_exp_id']);
	unset($_SESSION['msg_exp']);
	unset($_SESSION['msg_to']);
	unset($_SESSION["msg_dest_civilite"]);
	unset($_SESSION["msg_dest_nom"]);
	unset($_SESSION["msg_dest_prenom"]);
	unset($_SESSION["msg_dest_email"]);
	unset($_SESSION['msg_message']);
	unset($_SESSION['msg_message_txt']);
	unset($_SESSION["msg_fichier"]);
	unset($_SESSION["msg_read"]);
	unset($_SESSION["ajout"]);
	unset($_SESSION["current_corps"]);

	// Suppression des fichiers joints temporaires éventuels
	unset($_SESSION["tmp_message_fichiers"]);
	
	if(isset($_SESSION["auth_id"]) && $_SESSION["auth_id"]!="" && is_dir("$__ROOT_DIR/$__MOD_DIR/tmp/$_SESSION[auth_id]"))
	   deltree("$__ROOT_DIR/$__MOD_DIR/tmp/$_SESSION[auth_id]");

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();

	if(isset($_GET["dossier"]) && ctype_digit($_GET["dossier"]) && array_key_exists($_GET["dossier"], $__MSG_DOSSIERS))
		$current_dossier=$_SESSION["current_dossier"]=$_GET["dossier"];

	// Offset
	if(isset($_GET["offset"]) && ctype_digit($_GET["offset"]))
		$offset=$_GET["offset"];
	elseif(isset($_SESSION["msg_offset"]))
		$offset=$_SESSION["msg_offset"];

	// Sélection / désélection des messages
	if(isset($_GET["sa"]) && $_GET["sa"]==1)
		$checked="checked";
	elseif((isset($_GET["sa"]) && $_GET["sa"]==0) || !isset($_GET["sa"]))
		$checked="";

   // Ouverture de l'aperçu d'un message en cliquant sur l'expéditeur ou le sujet
   $apercu_message="";   
   
   if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
   {
      if(isset($params["msg"]))
      {
         // Nom du fichier avec les répertoires
         $fichier_apercu=$params["msg"];
         
         if(array_key_exists("dir", $params)) // sauvegarde du paramètre pour l'ouverture
            $dir=$params["dir"];
                  
         // ouverture
         if(($array_file=@file("$fichier_apercu"))!==FALSE)
         {
            // On n'ouvre que le corps du message (à partir de la 6ème ligne)
            $corps_apercu_message=array_slice($array_file, 5);
            
            // Suppression des lignes vides
            $apercu_array=array();
            $i=0;
            foreach($corps_apercu_message as $ligne_apercu)
            {               
               if(trim($ligne_apercu)!="") // on ne garde que les 5 premières lignes vides, le reste est ignoré
               {
                  if($i<5)
                     $apercu_array[$i]=$ligne_apercu;
                  elseif($i==5)
                     $apercu_array[$i]="[...]";
                     
                  $i++; 
               }
            }
            $crypt_params=isset($dir) ? crypt_params("dir=$dir&msg=$fichier_apercu") : crypt_params("msg=$fichier_apercu");
            $apercu_message=stripslashes(implode($apercu_array)) . "\n<a href='message.php?p=$crypt_params' class='lien_bleu_12'><strong>[Ouvrir le message]</strong></a>";
         }
      }
   }
     
	// Suppression ou déplacement des messages sélectionnés

	if((isset($_POST["move"]) || isset($_POST["move_x"])) && isset($_SESSION["current_dossier"]) && isset($_POST["newfolder"]) && isset($_POST["selection"]))
	{
		$nouveau_dossier=$_POST["newfolder"];

		if($nouveau_dossier!="" && $nouveau_dossier!=$_SESSION["current_dossier"])
		{
			foreach($_POST["selection"] as $filename)
			{
				if(!is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$nouveau_dossier"))
				{
					if(FALSE==mkdir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$nouveau_dossier", 0770, TRUE))
					{
						mail($__EMAIL_ADMIN, "[Précandidatures] - Erreur de création de répertoire", "Répertoire : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$nouveau_dossier\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");
						die("Erreur système lors de la création du dossier destination. Un message a été envoyé à l'administrateur.");
					}
				}

				if((is_file("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename") || is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename"))
				    && is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$nouveau_dossier/"))
					rename("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename", "$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$nouveau_dossier/$filename");
				else
					mail($__EMAIL_ADMIN, "[Précandidatures] - Erreur de déplacement de message", "Source : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename\nDestination : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$nouveau_dossier/\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");
			}
		}
	}

	if((isset($_POST["suppr"]) || isset($_POST["suppr_x"])) && isset($_SESSION["current_dossier"]) && isset($_POST["selection"]))
	{
		foreach($_POST["selection"] as $filename)
		{
			if(is_file("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename") || is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename"))
			{
				if($_SESSION["current_dossier"]==$__MSG_TRASH) // dossier actuel = corbeille : suppression = effacement physique
				{
				   // Attention : fonctions différentes pour un répertoire et un fichier
				   if(is_file("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename"))
						unlink("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename");
					elseif(is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename"))
						deltree("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename");
				}
				else // Vers la corbeille
					rename("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$_SESSION[current_dossier]/$filename", "$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRASH/$filename");
			}
		}
	}

	// Vidage de la corbeille
	if(isset($_GET["trash"]) && $_GET["trash"]==1)
	{
		if($del=scandir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRASH/"))
		{
			$points=array(".", "..");

			$del_files=array_diff($del, $points); // suppression des répertoires . et .. de la liste des fichiers à supprimer
			
			foreach($del_files as $file)
				@unlink("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$__MSG_TRASH/$file");
		}
	}
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
			titre_page_icone("Messagerie interne", "email_32x32_fond.png", 10, "L");
			
			if(isset($_GET["sent"]) && $_GET["sent"]==1)
				message("Message envoyé", $__SUCCES);

			if(isset($_GET["form_adresse_succes"]) && $_GET["form_adresse_succes"]==1)
				message("Adresse électronique mise à jour et identifiants envoyés avec succès.", $__SUCCES);
				
			if(isset($_GET["form_adresse_candidat_inconnu"]) && $_GET["form_adresse_candidat_inconnu"]==1)
				message("Candidat(e) inconnu(e) : procédure d'enregistrement envoyée par courriel.", $__SUCCES);

         if(isset($_GET["form_dev_succes"]) && $_GET["form_dev_succes"]==1)
            message("Formulaire de déverrouillage validé - Message envoyé.", $__SUCCES);

			if(!is_dir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/"))
			{
				if(FALSE==mkdir("$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$current_dossier", 0770, TRUE))
				{
					mail($__EMAIL_ADMIN, "[Précandidatures] - Erreur de création de répertoire", "Répertoire : $__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$current_dossier\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");

					die("Erreur système lors de la création de votre répertoire personnel. Un message a été envoyé à l'administrateur.");
				}
			}

			$contenu_repertoire=scandir("$GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$current_dossier", 1);

         if($contenu_repertoire===FALSE)
         {
            mail($__EMAIL_ADMIN, "$GLOBALS[__ERREUR_SUJET] - Erreur de lecture d'un répertoire", "Répertoire : $GLOBALS[__GESTION_MSG_STOCKAGE_DIR_ABS]/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$current_dossier\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");
            die("Erreur de lecture d'un répertoire. Un message a été envoyé à l'administrateur.");
         }

			if(FALSE!==($key=array_search(".", $contenu_repertoire)))
				unset($contenu_repertoire[$key]);

			if(FALSE!==($key=array_search("..", $contenu_repertoire)))
				unset($contenu_repertoire[$key]);

			if(FALSE!==($key=array_search("index.php", $contenu_repertoire)))
				unset($contenu_repertoire[$key]);

         rsort($contenu_repertoire);

			// TRI DE LA LISTE DES FICHIERS
         // TODO : à écrire

/*			
			switch($methode_tri)
			{
				case	"date_asc"		:	// Tri de la liste des fichiers par date croissante (revient à trier par nom cr.)
												sort($contenu_repertoire);
												break;

				case	"date_desc"		:	// Tri de la liste des fichiers par date décroissante (revient à trier par nom décr.)
												rsort($contenu_repertoire);
												break;

				case	"exp_asc"		:	// Tri par expéditeur croissant
												break;

				case	"exp_desc"		:	// Tri par expéditeur décroissant
												break;

				case	"sujet_asc"		:	// Tri par sujet croissant
												break;

				case	"sujet_desc"	:	// Tri par sujet décroissant
												break;
			}
*/

			$nb_msg=$nb_fichiers=count($contenu_repertoire);

			if($nb_msg==1)
				$nb_msg_texte="1 message";
			elseif($nb_msg==0)
				$nb_msg_texte="Aucun message";
			else
				$nb_msg_texte="$nb_msg messages";
				
			if(!isset($offset) || !ctype_digit($offset) || $offset>$nb_msg)
				$offset=0;

			$_SESSION["msg_offset"]=$offset;

			// Calcul des numéros de messages et de la présence/absence de flèches pour aller à la page suivante/précédente
			if($_SESSION["msg_offset"]>0)	 // lien vers la page précédente
			{
				$prev_offset=$_SESSION["msg_offset"]-20;

				$prev_offset=$prev_offset < 0 ? 0 : $prev_offset;

				$prev="<a href='$php_self?offset=$prev_offset'><img src='$__ICON_DIR/back_16x16_menu2.png' border='0'></a>";
				$prev_txt="[$prev_offset - $_SESSION[msg_offset]] ";

				$limite_inf_msg=$_SESSION["msg_offset"];
			}
			else
			{
				$prev=$prev_txt="";
				$limite_inf_msg=0;
			}

			if(($_SESSION["msg_offset"]+20)<$nb_msg) // encore des messages
			{
				// texte affiché
				if(($_SESSION["msg_offset"]+40)<$nb_msg)
				 	$limite_texte=$_SESSION["msg_offset"]+40;
				else
					$limite_texte=$nb_msg;

				// Offset suivant et limite de la boucle for() suivante
				$limite_sup_msg=$next_offset=$_SESSION["msg_offset"]+20;

				$next="<a href='$php_self?offset=$next_offset' style='vertical-align:bottom;'><img src='$__ICON_DIR/forward_16x16_menu2.png' border='0'></a>";
				$next_txt="[$next_offset - $limite_texte]";
			}
			else
			{
				$next=$next_txt="";

				// Pour la limite de la boucle for() suivante
				$limite_sup_msg=$nb_msg;
			}

			if($limite_sup_msg) // Si on a des messages, on propose des liens supplémentaires
			{
				// Lien "Sélection / désélection"
				if(!empty($checked))
					$lien_select_deselect="<a href='$php_self?sa=0' class='lien_menu_gauche'>Tout désélectionner</a>";
				else
					$lien_select_deselect="<a href='$php_self?sa=1' class='lien_menu_gauche'>Tout sélectionner</a>";

				// Lien "Vider la corbeille"
				$lien_corbeille=($current_dossier==$__MSG_TRASH) ? "&nbsp;|&nbsp;<a href='$php_self?trash=1' class='lien_menu_gauche'>Vider la corbeille</a>" : "";
			}
			else
				$lien_select_deselect=$lien_corbeille="";

			// changement de nom pour la colonne "Expéditeur" si le dossier est "Envoyés"
			$col_name=($current_dossier==$__MSG_SENT) ? "Destinataire" : "Expéditeur";


			// *****************************************************************
			// Options / flèches de tri
			$icon_date_down="1downarrow_green_16x16_menu2.png";
			$icon_date_up="1uparrow_green_16x16_menu2.png";
			$icon_exp_down="1downarrow_green_16x16_menu2.png";
			$icon_exp_up="1uparrow_green_16x16_menu2.png";
			$icon_sujet_down="1downarrow_green_16x16_menu2.png";
			$icon_sujet_up="1uparrow_green_16x16_menu2.png";

         // TODO : à terminer
			if(isset($_GET["tri"]) && ctype_digit($_GET["tri"]) && ($_GET["tri"]>=1 || $_GET["tri"]<=6))
			{
				switch($_GET["tri"])
				{
					case	1	:	$tri_messages="$_DBC_hist_date DESC";
									$icon_date_down="2downarrow_green_16x16_menu2.png";
									break;

					case	2	:	$tri_messages="$_DBC_hist_date ASC";
									$icon_date_up="2uparrow_green_16x16_menu2.png";
									break;

					case	3	:	$tri_messages="$_DBC_hist_type_evt DESC, $_DBC_hist_date DESC";
									$icon_exp_down="2downarrow_green_16x16_menu2.png";
									break;

					case	4	:	$tri_messages="$_DBC_hist_type_evt ASC, $_DBC_hist_date DESC";
									$icon_exp_up="2uparrow_green_16x16_menu2.png";
									break;

					case	5	:	$tri_messages="$_DBC_hist_type_evt ASC, $_DBC_hist_date DESC";
									$icon_sujet_up="2uparrow_green_16x16_menu2.png";
									break;

					case	6	:	$tri_messages="$_DBC_hist_type_evt ASC, $_DBC_hist_date DESC";
									$icon_sujet_up="2uparrow_green_16x16_menu2.png";
									break;
				}
			}
			else
			{
				$tri_messages="$_DBC_hist_date DESC";
				$icon_date_down="2downarrow_green_16x16_menu2.png";
			}
			// *****************************************************************

			print("<form action='$php_self' method='POST' name='form1'>
						<table class='encadre_messagerie' width='95%' align='center''>
						<tr>
							<td class='td-msg-titre fond_menu2' style='border-right:0px; padding:1px 2px 1px 2px;' colspan='2'>
								<font class='Texte_menu2'>
									<a href='$php_self' class='lien_menu_gauche'>Rafraîchir</a>$lien_corbeille
								</font>
							</td>
							<td class='td-msg-titre fond_menu2' style='vertical-align:middle; text-align:right; border-left:0px; padding:1px 2px 1px 2px;' colspan='3'>
								<font class='Texte_menu2'>
									$lien_select_deselect
									<select name='newfolder'>
										<option value=''></option>
										<option value='$__MSG_INBOX'>$__MSG_DOSSIERS[$__MSG_INBOX]</option>
										<option value='$__MSG_SENT'>$__MSG_DOSSIERS[$__MSG_SENT]</option>
										<option value='$__MSG_TRAITES'>$__MSG_DOSSIERS[$__MSG_TRAITES]</option>
										<option value='$__MSG_TRASH'>$__MSG_DOSSIERS[$__MSG_TRASH]</option>
									</select>
									&nbsp;
									<input type='submit' name='move' value='Déplacer'>&nbsp;&nbsp;&nbsp;<input type='submit' name='suppr' value='Supprimer'>

									$prev_txt
									$prev
									$next
									$next_txt
								</font>
							</td>
						</tr>
						<tr>
							<td class='td-msg-titre fond_page' style='border-right:0px; border-left:0px; height:10px;' colspan='5'></td>
						<tr>
							<td class='td-msg-titre fond_menu' style='padding:4px 2px 4px 2px;' colspan='5'>
								<font class='Texte_menu'>
									<b>$__MSG_DOSSIERS[$current_dossier]</b> ($nb_msg_texte)
								</font>
							</td>
						</tr>
						<tr>
							<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;' width='10%'>
								<font class='Texte_menu'><b>Date</b></font>
							</td>
							<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;' width='30%'>
								<font class='Texte_menu'><b>$col_name</b></font>
							</td>
							<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;' width='55%' colspan='2'>
								<font class='Texte_menu'><b>Sujet</b></font>
							</td>
							<td class='td-msg-menu fond_menu' style='padding:4px 2px 4px 2px;' width='5%'></td>
						</tr>\n");
/*
			for($i=0; $i<$rows; $i++)
			{
				list($msg_id, $msg_sujet, $msg_exp_id, $msg_exp_nom, $msg_exp_prenom, $msg_read)=db_fetch_row($result, $i);
*/

			for($i=$limite_inf_msg; $i<$limite_sup_msg; $i++)
			{
				// TODO : ajouter tests de retour des fonctions
				// $fichier=$_SESSION["repertoire"] . "/" . $contenu_repertoire[$i];

				$fichier="$__GESTION_MSG_STOCKAGE_DIR_ABS/$_SESSION[MSG_SOUS_REP]/$_SESSION[auth_id]/$current_dossier/" . $contenu_repertoire[$i];
				$file_or_dir_name=$nom_fichier=$contenu_repertoire[$i];	

				if(is_dir($fichier)) // Répertoire : message avec pièce(s) jointe(s)
				{
					// On regarde le contenu du répertoire. Normalement, le message a le même nom que ce dernier, terminé par .0 ou .1
					if(is_file("$fichier/$nom_fichier.0"))
					{
						$fichier.="/$nom_fichier.0";
						$nom_fichier="$nom_fichier.0";
					}
					elseif(is_file("$fichier/$nom_fichier.1"))
					{
						$fichier.="/$nom_fichier.1";
						$nom_fichier="$nom_fichier.1";
					}

					$crypt_params=crypt_params("dir=1&msg=$fichier");
				}
				else
					$crypt_params=crypt_params("msg=$fichier");

				// Identifiant du message = date
				// Format : AA(1 ou 2) MM JJ HH Mn SS µS(5)

				if(strlen($nom_fichier)==18) // Année sur un caractère (16 pour l'identifiant + ".0" ou ".1" pour le flag "read")
				{
					$date_offset=0;
					$annee_len=1;
					$leading_zero="0";
					$msg_id=substr($nom_fichier, 0, 16);
					$msg_read=substr($nom_fichier, 17, 1);
				}
				else // Année sur 2 caractères (chaine : 19 caractères)
				{
					$date_offset=1;
					$annee_len=2;
					$leading_zero="";
					$msg_id=substr($nom_fichier, 0, 17);
					$msg_read=substr($nom_fichier, 18, 1);
				}

				if(($array_file=file("$fichier"))==FALSE)
				{
					mail($__EMAIL_ADMIN, "[Précandidatures] - Erreur d'ouverture de mail", "Fichier : $fichier\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");

					die("Erreur d'ouverture du fichier. Un message a été envoyé à l'administrateur.");
				}

				$msg_exp_id=$array_file["0"];
				$msg_exp=$array_file["1"];
				$msg_to_id=$array_file["2"];
				$msg_to=$array_file["3"];
				$msg_sujet=stripslashes($array_file["4"]);

				$date_today=date("ymd") . "00000000000"; // on s'aligne sur le format des identifiants

				// On convertit la date en temps Unix : plus simple ensuite pour l'affichage et les conversions
				$unix_date=mktime(substr($msg_id, 5+$date_offset, 2), substr($msg_id, 7+$date_offset, 2), substr($msg_id, 9+$date_offset, 2),
										substr($msg_id, 1+$date_offset, 2), substr($msg_id, 3+$date_offset, 2), $leading_zero . substr($msg_id, 0, $annee_len));

				if($msg_id<$date_today) // le message n'est pas du jour : on affiche la date entière (date + heure)
					$date_txt=date_fr("d/m/y - H\hi", $unix_date);
				else // message du jour : on n'affiche que l'heure
					$date_txt=date_fr("H\hi", $unix_date);

				if(!$msg_read)
				{
					$style_bold="style='font-weight:bold;'";
					$style_bg="background-color:#FFECBD;";
				}
				else
				{
					$style_bold="";
					$style_bg="";
				}

				$col_value=$current_dossier==$__MSG_SENT ? $msg_to : $msg_exp;

				print("<tr>
							<td class='td-msg' style='text-align:left; $style_bg' width='10%'>
								<font class='Texte' $style_bold>$date_txt</font>
							</td>
							<td class='td-msg' style='text-align:left; $style_bg' width='30%'>
								<font class='Texte' $style_bold>
									<a href='message.php?p=$crypt_params' class='lien_bleu_12'>$col_value</a>
									<!-- <a href='$php_self?p=$crypt_params' class='lien_bleu_12'>$col_value</a> -->
								</font>
							</td>\n");
							
				// Aperçu du message ?
				if($fichier_apercu==$fichier && isset($apercu_message) && trim($apercu_message)!="")
				{
               print("<td class='td-msg' width='13' style='text-align:center; $style_bg; border-width:1px 0px 1px 1px;'>
								<a href='$php_self' target='_self'><img src='$__ICON_DIR/moins_11x11.png' width='11' border='0' title='Apercu' desc='Apercu'></a>
							</td>
							<td class='td-msg' style='text-align:left; $style_bg; border-width:1px 1px 1px 0px;' width='55%' $style_bold>
								<a href='message.php?p=$crypt_params' class='lien_bleu_12' $style_bold>$msg_sujet</a>
								<!-- <a href='$php_self?p=$crypt_params' class='lien_bleu_12' $style_bold>$msg_sujet</a> -->
							</td>
							<td class='td-msg' width='5%' style='text-align:center; $style_bg'>
								<font class='Texte_menu'><input type='checkbox' name='selection[]' value='$file_or_dir_name' $checked>
							</td>
						</tr>
						<tr>
							<td class='td-msg' style='padding:4px 0px 4px 10px; white-space:normal; border-width:1px 0px 1px 1px; text-align:justify; background-color:#FFFFFF;' colspan='4'>
				           <font class='Texte_10'></i>".nl2br(parse_macros($apercu_message))."</i></font>
				         </td>
				         <td class='td-msg' style='border-width:1px 1px 1px 0px; background-color:#FFFFFF;'></td>
				      </tr>\n");
				}
				else
				{
				   print("<td class='td-msg' width='13' style='text-align:center; $style_bg; border-width:1px 0px 1px 1px;'>
								<a href='$php_self?p=$crypt_params' target='_self'><img src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Apercu' desc='Apercu'></a>
							</td>
				         <td class='td-msg' style='text-align:left; $style_bg; border-width:1px 1px 1px 0px;' width='55%' $style_bold>
								<a href='message.php?p=$crypt_params' class='lien_bleu_12' $style_bold>$msg_sujet</a>
								<!-- <a href='$php_self?p=$crypt_params' class='lien_bleu_12' $style_bold>$msg_sujet</a> -->
							</td>
							<td class='td-msg' width='5%' style='text-align:center; $style_bg'>
								<font class='Texte_menu'><input type='checkbox' name='selection[]' value='$file_or_dir_name' $checked>
							</td>
						</tr>\n");
				}
			}

			print("</table>\n");
							
			// db_free_result($result);
			db_close($dbr);
		?>
	</div>
</div>

<?php
	pied_de_page();
?>
</body></html>
