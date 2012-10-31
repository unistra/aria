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

	$dbr=db_connect();

	// Validation du formulaire
	if(isset($_POST["publier_tout"]))
	{
		db_query($dbr, "UPDATE $_DB_propspec SET $_DBU_propspec_affichage_decisions='1'
								WHERE $_DBU_propspec_comp_id='$_SESSION[comp_id]'");

		$succes=1;
	}
	elseif(isset($_POST["publier_lettres"]))
	{
	   db_query($dbr, "UPDATE $_DB_propspec SET $_DBU_propspec_affichage_decisions='2'
	   					 WHERE $_DBU_propspec_comp_id='$_SESSION[comp_id]'");
	                                 
	   $succes=2;
	}
	elseif(isset($_POST["masquer_tout"]))
	{
		db_query($dbr, "UPDATE $_DB_propspec SET $_DBU_propspec_affichage_decisions='0'
								WHERE $_DBU_propspec_comp_id='$_SESSION[comp_id]'");

		$succes=3;
	}	
	elseif(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
	{
		foreach($_POST["formations"] as $propspec_id => $nouveau_statut)
		{
			if($nouveau_statut==0 || $nouveau_statut==1 || $nouveau_statut==2) // Décisions masquées / publiées / publiées avec courriers
				db_query($dbr, "UPDATE $_DB_propspec SET $_DBU_propspec_affichage_decisions='$nouveau_statut' WHERE $_DBU_propspec_id='$propspec_id'");
		}

		$succes=4;
	}
	
	if((isset($succes) && $succes!=3) || isset($_POST["envoyer_tout"]))
	{
      // Si on a publié des décisions et que la composante est configurée pour envoyer des notifications, on propose d'envoyer toutes celles en attente
      if(isset($_SESSION["avertir_decision"]) && $_SESSION["avertir_decision"]==1 && !isset($_POST["masquer_tout"]))
      {
         if(isset($_POST["formations"]) && count($_POST["formations"])) // Liste des formations concernées
         {
            $liste_formations="";
               
            foreach($_POST["formations"] as $propspec_id => $nouveau_statut)
            {
               if($nouveau_statut==1 || $nouveau_statut==2)
                  $liste_formations.="$propspec_id,";
            }
            
            if($liste_formations!="")
               $requete_formations=substr("AND $_DBC_propspec_id IN (".$liste_formations, 0, -1).")";
         }
         elseif(isset($_POST["publier_tout"]) || isset($_POST["publier_lettres"]) || isset($_POST["envoyer_tout"])) // toutes les formations
            $requete_formations="";

         // Sélection des candidatures concernées
         $res_formations=db_query($dbr, "SELECT $_DBC_cand_id, $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_email,
                                                $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite
                                         FROM $_DB_cand, $_DB_propspec, $_DB_candidat, $_DB_annees, $_DB_specs
                                         WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
                                         AND $_DBC_cand_candidat_id=$_DBC_candidat_id
                                         AND $_DBC_propspec_annee=$_DBC_annees_id
                                         AND $_DBC_specs_id=$_DBC_propspec_id_spec
                                         AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                         AND $_DBC_cand_periode='$__PERIODE'
                                         AND $_DBC_propspec_active='1'
                                         AND $_DBC_cand_notification_envoyee='0'
                                         AND $_DBC_propspec_affichage_decisions IN ('1', '2')
                                         AND $_DBC_cand_decision!='$__DOSSIER_NON_TRAITE'
                                         $requete_formations");
                       
         $num_formations=db_num_rows($res_formations);           
            
         $_SESSION["tab_envoyer_notifications"]=array();
            
         for($i=0; $i<$num_formations; $i++)
         {
            list($cand_id, $candidat_id, $candidat_civ, $candidat_nom, $candidat_prenom, $candidat_email, $annee_nom, $spec_nom, $propspec_finalite)=db_fetch_row($res_formations, $i);
               
            $nom_formation=$annee_nom=="" ? "$spec_nom" : "$annee_nom - $spec_nom";
            $nom_formation.=$tab_finalite[$propspec_finalite]=="" ? "" : " $tab_finalite[$propspec_finalite]";
               
            $_SESSION["tab_envoyer_notifications"][$cand_id]=array("candidat_id" => "$candidat_id",
                                                                   "civilite" => "$candidat_civ",
                                                                   "nom" => "$candidat_nom",
                                                                   "prenom" => "$candidat_prenom",
                                                                   "email" => "$candidat_email",
                                                                   "nom_complet" => "$nom_formation");
         }
            
         db_free_result($res_formations);
      }
	}
	
	if(isset($_POST["aucun_envoi"])) // Envoi des messages ?
      $aucun_message=1;
   elseif(isset($_POST["envoyer"]) && isset($_SESSION["tab_envoyer_notifications"]) && is_array($_SESSION["tab_envoyer_notifications"]))
   {
      foreach($_SESSION["tab_envoyer_notifications"] as $cand_id => $cand_array)
      {   
         // TODO : Message à mettre dans la base de données avec les macros adéquates
      
         $message="Bonjour,\n
La Commission Pédagogique a rendu une décision pour votre candidature à la formation suivante : \n
[gras]$cand_array[nom_complet][/gras]\n
Pour consulter cette décision : 
- sélectionnez si besoin l'établissement adéquat (menu \"Choisir une autre composante\")
- dans votre fiche, rendez vous dans le menu \"Précandidatures\".

Cordialement,\n\n
--
$_SESSION[adr_scol]\n
$_SESSION[composante]
$_SESSION[universite]";

         $dest_array=array("0" => array("id"     => $cand_array["candidat_id"],
                                        "civ"    => $cand_array["civilite"],
                                        "nom"    => $cand_array["nom"],
                                        "prenom" => $cand_array["prenom"],
                                        "email"  => $cand_array["email"]));

         write_msg("", array("id" => "0", "nom" => "Système", "prenom" => ""), $dest_array, "$_SESSION[composante] - Décision", $message, $cand_array["nom"]." ".$cand_array["prenom"]);
         
         write_evt($dbr, $__EVT_ID_G_PREC, "Notification de décision envoyée", $cand_array["candidat_id"], $cand_id);
               
         db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_notification_envoyee='1' WHERE $_DBU_cand_id='$cand_id'");
      }   
      
      $count_envoyes=count($_SESSION["tab_envoyer_notifications"]);
      
      unset($_SESSION["tab_envoyer_notifications"]);
   }
   
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Commissions Pédagogiques : affichage des décisions", "ksysv_32x32_fond.png", 15, "L");

		if(isset($succes))
		{
		   switch($succes)
		   {		   
		      case 1 : message("Les décisions sont maintenant <strong>publiées</strong> (lettres non accessibles).", $__SUCCES);
		               break;
		      
		      case 2 : message("Les décisions sont maintenant <strong>publiées</strong> et les lettres sont <strong>accessibles aux candidats</strong>.", $__SUCCES);
		               break;
		      
		      case 3 : message("Les décisions sont maintenant <strong>masquées</strong>.", $__SUCCES);
		               break;
		      
		      case 4 : message("Paramètres des formations validés avec succès.", $__SUCCES);
		               break;
         }
      }
   
      if(isset($num_formations) && $num_formations>0)
      {
         if($num_formations==1)
         {
            $s=$x="";
            $ce="ce";
         }
         else
         {
            $s="s";
            $ce="ces";
            $x="x";
         }
               
         message("Il y a <strong>$num_formations décision$s</strong> en attente de notification au$x candidat$s concerné$s (aucun message n'a encore été envoyé).
                  <br><br><center><strong>Souhaitez-vous envoyer $ce message$s immédiatement ?</strong></center>
                  <br><strong>Important :</strong>
                  <br>- vous pouvez déclencher cet envoi à tout moment en cliquant sur \"Envoyer les notifications ...\" sur l'écran précédent ;
                  <br>- en fonction du nombre de notifications, les envois peuvent prendre plusieurs minutes. En cas de validation, merci de <strong>patienter</strong> jusqu'au retour à l'écran précédent ;
                  <br>- les messages automatiques ne <strong>contiennent pas</strong> les décisions, seul un message standard est envoyé ;
                  <br>- conformément à la configuration de l'établissement, les futures décisions seront <strong>automatiquement notifiées</strong> si les décisions sont en mode \"publiées\".", $__QUESTION);
                        
         print("<form action='$php_self' method='POST' name='form2'>
                
                <div class='centered_box'>
                   <input type='submit' alt='Ne pas envoyer les messages' name='aucun_envoi' value='Je ne souhaite PAS envoyer les messages'>
                   <input type='submit' alt='Envoyer les messages' name='envoyer' value='Oui : envoyer les messages immédiatement'>
                </div>
   	          </form>\n");                        
      }      
      else
      {
         if(isset($aucun_message))
            message("Les messages de notification n'ont pas été envoyés", $__INFO);
         elseif(isset($count_envoyes))
            message("$count_envoyes message(s) de notification envoyé(s).", $__INFO);
      
		message("1/ Seules les décisions publiées sont visibles par les candidats. Les décisions prises <b>après</b> publication seront automatiquement publiées.
					<br>2/ En cas de sélection individuelle des formations, n'oubliez pas de <b>valider les modifications</b> grâce à l'icône située sous les tableaux.", $__INFO);

		// Option particulière pour l'envoi des messages de notification
		if(isset($_SESSION["avertir_decision"]) && $_SESSION["avertir_decision"]==1 && 
		   $count_envois=db_num_rows(db_query($dbr, "SELECT * FROM $_DB_cand,$_DB_propspec WHERE $_DBC_propspec_id=$_DBC_cand_propspec_id 
		                                                                                   AND $_DBC_cand_periode='$__PERIODE' 
		                                                                                   AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
		                                                                                   AND $_DBC_propspec_active='1'
		                                                                                   AND $_DBC_cand_notification_envoyee='0'
		                                                                                   AND $_DBC_propspec_affichage_decisions IN ('1', '2')   
		                                                                                   AND $_DBC_cand_decision!='$__DOSSIER_NON_TRAITE'")))
      {
         $bottom_border="0px";
         $option_envoi=1;
      }
      else
      {
         $bottom_border="1px";
         $option_envoi=0;
      }
      
      print("<form action='$php_self' method='POST' name='form1'>

					<table cellpadding='0' cellspacing='0' border='0' align='center'>
					<tr>
						<td class='fond_menu2' colspan='3' align='center' style='padding:4px 10px 4px 10px; border-style:solid; border-color:black; border-width:1px 1px 0px 1px;'>
							<font class='Texte_menu2'><b>Options spéciales (à effet immédiat)</b></font>
						</td>
					</tr>
					<tr>
						<td class='fond_menu2' align='center' width='33%' style='padding:4px 10px 4px 10px; border-style:solid; border-width:0px 0px $bottom_border 1px;'>
						   <input type='submit' name='masquer_tout' value='Masquer toutes les décisions'>	
						</td>
						<td class='fond_menu2' align='center' width='33%' style='padding:4px 10px 4px 10px; border-style:solid; border-width:0px 0px $bottom_border 0px;'>
                     <input type='submit' name='publier_tout' value='Publier toutes les décisions'>
						</td>
						<td class='fond_menu2' align='center' width='34%' style='padding:4px 10px 4px 10px; border-style:solid; border-width:0px 1px $bottom_border 0px;'>
							<input type='submit' name='publier_lettres' value='Publier les décisions + Lettres accessibles aux candidats'>
						</td>
					</tr>\n");
					
		if($option_envoi==1)
		{
		   print("<tr>
					   <td class='fond_menu2' align='center' colspan='3' style='padding:4px 0px 4px 0px; border-style:solid; border-width:0px 1px 1px 1px;'>
					      <input type='submit' alt='Envoyer les messages' name='envoyer_tout' value='Envoyer les notifications de prises de décisions aux candidats'>
					      <br><font class='Texte'><i>(Il y a $count_envois message(s) en attente)</i></font>
					   </td>
               </tr>\n");
      }
      
      print("</table>

				 <br clear='all'>\n");
	?>
	<table cellpadding='0' cellspacing='0' border='0' align='center'>
	<?php
		$result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_annees_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
												 $_DBC_mentions_nom, $_DBC_propspec_affichage_decisions
											FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
										WHERE $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_specs_mention_id=$_DBC_mentions_id
										AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										AND $_DBC_propspec_active='1'
											ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom_court, $_DBC_propspec_finalite");

		$rows=db_num_rows($result);

		$old_annee_id="===="; // on initialise à n'importe quoi (sauf année existante et valeur vide)
		$old_propspec_id="";
		$old_mention="--";

		for($i=0; $i<$rows; $i++)
		{
			list($propspec_id, $annee_id, $annee, $spec_nom, $finalite, $mention, $affichage_decisions)=db_fetch_row($result, $i);

			$nom_finalite=$tab_finalite[$finalite];

			$annee=$annee=="" ? "Années particulières" : $annee;

			$masquer_checked=$publier_checked=$publier_lettres_checked="";

			if($affichage_decisions==0)
			{
				$statut_actuel="<font class='Texte_important_menu'>Masquées</font>";
				$masquer_checked="checked";
			}
			elseif($affichage_decisions==1)
			{
            $statut_actuel="<font class='Textevert_menu'>Publiées</font>";
            $publier_checked="checked";
			}
         elseif($affichage_decisions==2)
         {
         	$statut_actuel="<font class='Textevert_menu'>Publiées et lettres accessibles</font>";
         	$publier_lettres_checked="checked";
         	
			}

			if($annee_id!=$old_annee_id)
			{
				if($i!=0)
					print("</tr>
							 <tr>
							    <td colspan='8' height='25' style='border-style:solid; border-width:1px 0px 0px 0px;'></font>
							 </tr>\n");

				print("<tr>
							<td class='fond_menu2' colspan='8' align='center' style='padding:4px 20px 4px 20px; border-style:solid; border-color:black; border-width:1px 1px 0px 1px;'>
								<font class='Texte_menu2'><b>$annee</b></font>
							</td>
						 </tr>
						 <tr>
							<td class='fond_menu2' style='padding:4px 20px 4px 20px; border-style:solid; border-width:0px 0px 0px 1px;'>
								<font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
							</td>
							<td class='fond_menu2' style='padding:4px 10px 4px 10px; white-space:nowrap;'>
								<font class='Texte_menu2'><b>Statut actuel des décisions</b></font>
							</td>
							<td class='fond_menu2' colspan='6' style='padding:4px 10px 4px 10px; white-space:nowrap; border-style:solid; border-width:0px 1px 0px 0px;'>
								<font class='Texte_menu2'><b>Nouveau statut</b></font>
							</td>\n");

				$first_spec=1;
				$old_mention="--";
			}
			else
				$first_spec=0;

			if($mention!=$old_mention)
			{
				if(!$first_spec)
					print("<tr>
								<td class='fond_menu2' colspan='8' style='padding:4px 20px 4px 20px; border-style:solid; border-width:0px 1px 0px 1px;'>
									<font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
								</td>
							</tr>\n");

				$old_mention=$mention;
			}

			print("</tr>
					 <tr>
						<td class='td-gauche fond_menu' style='border-style:solid; border-width:0px 0px 0px 1px;'>
							<font class='Texte_menu'>$spec_nom $nom_finalite</font>
						</td>
						<td class='td-milieu fond_menu' align='center'>
							$statut_actuel
						</td>
						<td class='td-milieu fond_menu' style='width:10px'>
							<input type='radio' name='formations[$propspec_id]' value='0' $masquer_checked>
						</td>
						<td class='td-milieu fond_menu'>
							<font class='Texte_menu'>Masquer</font>
						</td>
						<td class='td-milieu fond_menu' style='width:10px'>
							<input type='radio' name='formations[$propspec_id]' value='1' $publier_checked>
						</td>
						<td class='td-milieu fond_menu'>
							<font class='Texte_menu'>Publier</font>
						</td>
						<td class='td-milieu fond_menu' style='width:10px'>
							<input type='radio' name='formations[$propspec_id]' value='2' $publier_lettres_checked>
						</td>
						<td class='td-droite fond_menu' style='border-style:solid; border-width:0px 1px 0px 0px;'>
							<font class='Texte_menu'>Publier+Lettres</font>
						</td>\n");

			$old_annee_id=$annee_id;
		}

		print("</tr>
				 <tr>
				    <td colspan='8' height='25' style='border-style:solid; border-width:1px 0px 0px 0px;'></font>
				 </tr>\n");

		db_free_result($result);
	?>
	</table>

	<div class='centered_box'>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go_valider" value="Valider">
	</div>
	
	</form>
	
	<?php
      }
      db_close($dbr);
   ?>
</div>
<?php
	pied_de_page();
?>

</body></html>
