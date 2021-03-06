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
   session_name("preinsc_gestion");
   session_start();

   include "../../configuration/aria_config.php";
   include "$__INCLUDE_DIR_ABS/vars.php";
   include "$__INCLUDE_DIR_ABS/fonctions.php";
   include "$__INCLUDE_DIR_ABS/db.php";

   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;

   verif_auth("$GLOBALS[__GESTION_DIR]/login.php");

   if(!in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
   {
      header("Location:$__GESTION_DIR/noaccess.php");
      exit();
   }

   $dbr=db_connect();

   // D�verrouillage, au cas o�
   if(isset($_SESSION["candidat_id"]))
      cand_unlock($dbr, $_SESSION["candidat_id"]);

   db_close($dbr);

   unset($_SESSION["modification"]);
   unset($_SESSION["suppression"]);
   unset($_SESSION["ajout"]);
   unset($_SESSION["ajout_user"]);
   unset($_SESSION["ajout_mention"]);
   unset($_SESSION["ajout_spec"]);
   unset($_SESSION["ajout_comp"]);
   unset($_SESSION["ajout_univ"]);
   unset($_SESSION["ajout_formation"]);

   unset($_SESSION["form_comp_id"]);
   unset($_SESSION["form_comp_nom"]);
   unset($_SESSION["current_annee"]);
   unset($_SESSION["current_annee_nom"]);
   unset($_SESSION["current_spec"]);
   unset($_SESSION["current_spec_nom"]);
   unset($_SESSION["current_finalite"]);
   unset($_SESSION["array_comp"]);
   unset($_SESSION["spec_id"]);
   unset($_SESSION["formation"]);
   unset($_SESSION["justif_doc_id"]);
   unset($_SESSION["dossiers_annee_id"]);
   unset($_SESSION["nom_annee"]);
   unset($_SESSION["info_doc_id"]);
   unset($_SESSION["acces_nom"]);
   unset($_SESSION["acces_prenom"]);
   unset($_SESSION["acces_id"]);

   unset($_SESSION["droits_user_id"]);
   unset($_SESSION["droits_comp_id"]);

   unset($_SESSION["user_periode"]);
   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();
?>

<div class='main'>
   <?php
      titre_page_icone("Administration de l'interface", "preferences_32x32_fond.png", 30, "L");
   ?>

   <table border="0" cellspacing="8" cellpadding="2" valign="top" align="center">
   <tr>
      <?php
         if($_SESSION['niveau']==$__LVL_ADMIN)
         {
      ?>
      <td align='left' valign='top' nowrap='true'>
         <font class='Texte3'>&#183;&nbsp;<i><b>Utilisateurs</b></i></font>
         <br>
         <table width='100%' border="0" cellspacing="0" cellpadding="2" valign="top" style='padding-top:10px;'>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><a href='users.php' target='_self' class='lien_bleu'><img src='<?php echo "$__ICON_DIR/kdmconfig_16x16_fond.png"; ?>' border='0'></a></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='users.php' target='_self' class='lien_bleu'>Gestion des utilisateurs</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><a href='droits_users.php' target='_self' class='lien_bleu'><img src='<?php echo "$__ICON_DIR/randr_16x16_fond.png"; ?>' border='0'></a></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='droits_users.php' target='_self' class='lien_bleu'>Gestion des droits d'acc�s</a></td>
         </tr>
         </table>
      </td>
      <?php
         }
      ?>
      <td align='left' valign='top' nowrap='true'>
         <font class='Texte3'>&#183;&nbsp;<i><b>Universit� et composantes</b></i></font>
         <br>
         <table width='100%' border="0" cellspacing="0" cellpadding="2" valign="top" style='padding-top:10px;'>
         <?php
            if($_SESSION['niveau']==$__LVL_ADMIN)
            {
         ?>
               <tr>
                  <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><a href='universites.php' target='_self' class='lien_bleu'><img src='<?php echo "$__ICON_DIR/universite_16x16_fond.png"; ?>' border='0'></a></td>
                  <td align='left' valign='middle' style='padding-bottom:6px;'><a href='universites.php' target='_self' class='lien_bleu'>Gestion des universit�s</a></td>
               </tr>
         <?php
            }
            if(in_array($_SESSION["niveau"], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
            {
         ?>
               <tr>
                  <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><a href='composantes.php' target='_self' class='lien_bleu'><img src='<?php echo "$__ICON_DIR/composante_16x16_fond.png"; ?>' border='0'></a></td>
                  <td align='left' valign='middle' style='padding-bottom:6px;'><a href='composantes.php' target='_self' class='lien_bleu'>Gestion des composantes</a></td>
               </tr>
         <?php
            }
            if($_SESSION['niveau']==$__LVL_ADMIN)
            {
         ?>
               <tr>
                  <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><a href='messages.php' target='_self' class='lien_bleu'><img src='<?php echo "$__ICON_DIR/application-msword_16x16_fond.png"; ?>' border='0'></a></td>
                  <td align='left' valign='middle' style='padding-bottom:6px;'><a href='messages.php' target='_self' class='lien_bleu'>Textes et messages</a></td>
               </tr>
         <?php
            }
         ?>
         <tr>
            <td width='18' align='left' valign='middle' style='padding-bottom:6px;'><a href='decisions.php' target='_self' class='lien_bleu'><img src='<?php echo "$__ICON_DIR/decisions_16x16_fond.png"; ?>' border='0'></a></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='decisions.php' class='lien_bleu'>D�cisions utilis�es</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='comp_infos/index.php' class='lien_bleu'>Editeur de page d'information</a></td>
         </tr>
         </table>
      </td>
      <?php
         if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
         {
      ?>
      <td align='left' valign='top' nowrap='true'>
         <font class='Texte3'>&#183;&nbsp;<i><b>Offre de Formations</b></i></font>
         <br>
         <table width='100%' border="0" cellspacing="0" cellpadding="2" valign="top" style='padding-top:10px;'>
         <?php
            if($_SESSION['niveau']==$__LVL_ADMIN)
            {
         ?>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='annees.php' class='lien_bleu'>Gestion des ann�es</a></td>
         </tr>
         <?php
            }
            if(in_array($_SESSION["niveau"], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
            {
         ?>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='mentions.php' class='lien_bleu'>Gestion des mentions</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='specialites.php' class='lien_bleu'>Gestion des sp�cialit�s</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><a href='formations.php' target='_self' class='lien_bleu'><img src='<?php echo "$__ICON_DIR/add_16x16_fond.png"; ?>' border='0'></a></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='formations.php' target='_self' class='lien_bleu'>Gestion des formations</a></td>
         </tr>
         <?php
            }
         ?>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px; padding-top:16px;'><a href='offre.php' target='_self' class='lien_bleu'><img src='<?php echo "$__ICON_DIR/view_text_16x16_fond.png"; ?>' border='0'></a></td>
            <td align='left' valign='middle' style='padding-bottom:6px; padding-top:16px;'><a href='offre.php' target='_self' class='lien_bleu'>Consulter la liste des formations</a></td>
         </tr>
         </table>
      </td>
      <?php
         }
         if(in_array($_SESSION["niveau"], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
         {
      ?>
      <td align='left' valign='top' nowrap='true'>
         <font class='Texte3'>&#183;&nbsp;<i><b>Param�tres des Formations</b></i></font>
         <br>
         <table width='100%' border="0" cellspacing="0" cellpadding="2" valign="top" style='padding-top:10px;'>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><a href='info_formations.php' target='_self' class='lien_bleu'><img src='<?php echo "$__ICON_DIR/help_16x16_fond.png"; ?>' border='0'></a></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='info_formations.php' target='_self' class='lien_bleu'>Infos Formations<br><i>(pour les candidats)</i></a></td>
         </tr>
         <?php
            if(in_array($_SESSION["niveau"], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
            {
         ?>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><a href='frais_dossiers.php' target='_self' class='lien_bleu'><img src='<?php echo "$__ICON_DIR/applications-science_16x16_fond.png"; ?>' border='0'></a></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='filtres/index.php' class='lien_bleu'>Gestion des filtres inter-formations</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='groupes_formations.php' class='lien_bleu'>Candidatures � choix multiples</a></td>
         </tr>
         <?php
            }
         ?>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><a href='frais_dossiers.php' target='_self' class='lien_bleu'><img src='<?php echo "$__ICON_DIR/xcalc_16x16_fond.png"; ?>' border='0'></a></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='frais_dossiers.php' class='lien_bleu'>Frais de dossiers</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><a href='formations_courriels.php' target='_self' class='lien_bleu'><img src='<?php echo "$__ICON_DIR/email_16x16_fond.png"; ?>' border='0'></a></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='formations_courriels.php' class='lien_bleu'>Courriels de Scolarit�</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><img src='<?php echo "$__ICON_DIR/clock_16x16_fond.png"; ?>' border='0'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='sessions/index.php' class='lien_bleu'>Sessions de candidatures</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><img src='<?php echo "$__ICON_DIR/clock_16x16_fond.png"; ?>' border='0'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='commissions/index.php' class='lien_bleu'>Commissions p�dagogiques</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><img src='<?php echo "$__ICON_DIR/document-export_16x16_fond.png"; ?>' border='0'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'>
               <?php
                  if(array_key_exists("affichage_decisions", $_SESSION) && $_SESSION["affichage_decisions"]==0)
                     print("<a href='affichage_decisions.php' class='lien_bleu'>Publication des d�cisions</a>");
                  elseif(array_key_exists("affichage_decisions", $_SESSION) && $_SESSION["affichage_decisions"]!=0)
                     print("<font class='Textegris'><i>
                              Publication des d�cisions : automatique par d�faut
                              <br>(Option du menu \"Modifier une composante\")
                              </i></font>");
               ?>
            </td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='dossiers/index.php' class='lien_bleu'>Constructeur de dossiers</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><img src='<?php echo "$__ICON_DIR/package_application_16x16_fond.png"; ?>' border='0'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='editeur/index.php' class='lien_bleu'>Editeur de lettres</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='justificatifs/index.php' class='lien_bleu'>Editeur de justificatifs</a></td>
         </tr>
         </table>
      </td>
      <?php
         }
      ?>
   </tr>
   <tr>
      <td colspan='3' height='15'></td>
   </tr>
   <tr>
      <?php
         if($_SESSION["niveau"]==$__LVL_ADMIN)
         {
      ?>
<!--
      <td align='left' valign='top' nowrap='true'>
         <font class='Texte3'>&#183;&nbsp;<i><b>Interface de pr�candidatures</b></i></font>
         <br>
         <table width='100%' border="0" cellspacing="0" cellpadding="2" valign="top" style='padding-top:10px;'>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'><img src='<?php echo "$__ICON_DIR/clock_16x16_fond.png"; ?>' border='0'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='dates.php' class='lien_bleu'>Dates : Ouverture / Fermeture globales</a></td>
         </tr>
         </table>
      </td>
-->
      <?php
         }
      ?>

      <?php
         if(in_array($_SESSION["niveau"], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
         {
      ?>
      <td align='left' valign='top' nowrap='true'>
         <font class='Texte3'>&#183;&nbsp;<i><b>Listes diverses</b></i></font>
         <br>
         <table width='100%' border="0" cellspacing="0" cellpadding="2" valign="top" style='padding-top:10px;'>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='motifs_refus.php' class='lien_bleu'>Motifs de refus ou de mises en attente</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='cursus_mentions.php' class='lien_bleu'>Mentions de cursus</a></td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'><a href='langues.php' class='lien_bleu'>Langues</a></td>
         </tr>
         </table>
      </td>
      <?php
         }
         if(isset($_SESSION["niveau"]) && $_SESSION["niveau"]==$__LVL_ADMIN)
         {
      ?>
      <td align='left' valign='top' nowrap='true'>
         <font class='Texte3'>&#183;&nbsp;<i><b>Autres</b></i></font>
         <br>
         <table width='100%' border="0" cellspacing="0" cellpadding="2" valign="top" style='padding-top:10px;'>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'>
               <a href='systeme.php' class='lien_bleu'>Param�trage syst�me</a>
            </td>
         </tr>
         <tr>
            <td width='16' align='left' valign='middle' style='padding-bottom:6px;'></td>
            <td align='left' valign='middle' style='padding-bottom:6px;'>
               <a href='install.php' target='_blank' class='lien_bleu'>V�rification de l'installation</a>
            </td>
         </tr>
         </table>
      </td>
      <?php
         }
         if(isset($_SESSION["PLUGINS"]) && count($_SESSION["PLUGINS"]))
         {
      ?>
      <td align='left' valign='top' nowrap='true'>
         <font class='Texte3'>&#183;&nbsp;<i><b>Modules additionnels</b></i></font>
         <br>
         <table width='100%' border="0" cellspacing="0" cellpadding="2" valign="top" style='padding-top:10px;'>
      <?php
         foreach($_SESSION["PLUGINS"] as $module_array)
         {
            print("<tr>
                     <td align='left' valign='middle' style='padding-bottom:6px;'>
                        <font class='Texte'><strong>$module_array[MOD_NAME] :</strong></font>
                     </td>
                  </tr>\n");

            foreach($module_array["MOD_CONFIG"] as $config_array)
            {
               if(array_key_exists("MOD_CONFIG_PAGE", $config_array))
               {
                  // TODO : �crire ce test autrement (in_array, etc)
                  if(array_key_exists("MOD_NIVEAU_MIN", $config_array) && !empty($config_array["MOD_NIVEAU_MIN"])
                     && $config_array["MOD_NIVEAU_MIN"]<=$_SESSION["niveau"])

                  print("<tr>
                           <td align='left' valign='middle' style='padding-bottom:6px;'>
                              <a href='$__PLUGINS_DIR/$module_array[MOD_DIR]/$config_array[MOD_CONFIG_PAGE]' class='lien_bleu'>- $config_array[MOD_CONFIG_TITLE]</a>
                           </td>
                        </tr>\n");
               }
               elseif(array_key_exists("MOD_CONFIG_SEP", $config_array))
               {
                  print("<tr>
                           <td align='left' valign='middle' style='padding-bottom:6px;'>
                              <font class='Texte'><strong>$config_array[MOD_CONFIG_SEP]</strong></font>
                           </td>
                        </tr>\n");
               }
            }
         }
      ?>
         </table>
      </td>
      <?php
         }
      ?>
   </tr>
   </table>
</div>
<?php
   pied_de_page();
?>
</body></html>
