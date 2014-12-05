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
   // Vérifications complémentaires au cas où ce fichier serait appelé directement
   verif_auth();

   if(!isset($_SESSION["candidat_id"]) || !isset($_SESSION["niveau"]) || !in_array($_SESSION['niveau'], array("$__LVL_SUPPORT","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
   {
      print("<div class='centered_box'>
               <font class='Texte_16'><strong>Vous n'avez pas accès à cette section.</strong></font>
             </div>");
   }
   else
   {
      print("<div class='centered_box'>
               <font class='Texte_16'><strong>$_SESSION[onglet] - Gestion Manuelle</strong></font>
             </div>");
   
      message("Attention : ce mode contourne certaines sécurités de l'application. <br>Soyez <b>extrêmement prudent(e)</b> lorsque vous manipulez ces informations !", $__WARNING);
   
      if(isset($envoi_ok) && $envoi_ok==1)
         message("Courriel envoyé avec succès", $__SUCCES);
   
      if(isset($identifiant_ok) && $identifiant_ok==1)
         message("Identifiant modifié avec succès.<br><strong>N'oubliez pas de signaler cette modification ".$_SESSION["tab_candidat"]["etudiant_coi"]." !", $__SUCCES);
      elseif(isset($identifiant_nok) && $identifiant_nok==1)
         message("Erreur : cet identifiant est déjà utilisé par un autre candidat", $__ERREUR);
   
      if(isset($email_ok) && $email_ok==1)
         message("Adresse électronique modifiée avec succès", $__SUCCES);
   
      if(isset($mode_ok) && $mode_ok==1)
         message("La fiche est maintenant en mode \"$mode_txt\"", $__SUCCES);
   
      if(isset($send_mail))
      {
         if($send_mail==1)
            message("Courriel envoyé avec succès", $__SUCCES);
         else
            message("Erreur lors de l'envoi du courriel - un message a été envoyé à l'administrateur.", $__ERREUR);
      }

?>

<table style='margin:0px auto 0px auto;' border='0'>
<tr>
   <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><b>Adresse électronique</b></font>
   </td>
   <td class='td-milieu fond_menu'>
      <input type='text' name="email" value="<?php echo $_SESSION["tab_candidat"]["email"]; ?>" size="32" maxlength="256">
   </td>
   <td colspan='2' class='td-droite fond_menu' style='text-align:center;'>
      <?php
         print("<input type='image' src='$__ICON_DIR/button_ok_22x22_menu.png' alt='Valider la modification' name='go_email' value='Valider la modification'>");
      ?>
   </td>
</tr>
<tr>
   <td colspan='4' class='fond_page' style='height:10px;'></td>
</tr>
<tr>
   <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><b>Identifiant</b></font>
   </td>
   <td class='td-milieu fond_menu'>
      <input type='text' name="identifiant" value="<?php if(isset($new_identifiant)) echo $new_identifiant; else echo $_SESSION["tab_candidat"]["identifiant"]; ?>" size="32" maxlength="256">
   </td>
   <td colspan='2' class='td-droite fond_menu' style='text-align:center;'>
      <?php
         print("<input type='image' src='$__ICON_DIR/button_ok_22x22_menu.png' alt='Valider la modification' name='go_identifiant' value='Valider la modification'>");
      ?>
   </td>
</tr>
<tr>
   <td colspan='4' class='fond_page' style='height:10px;'></td>
</tr>
<tr>
   <td class='td-gauche fond_menu2' rowspan='2'>
      <font class='Texte_menu2'><b>Renvoi de messages particuliers</b></font>
   </td>
   <td class='td-milieu fond_menu'>
      <font class='Texte_menu'>Renvoyer l'identifiant et un nouveau mot de passe</font>
   </td>
   <td colspan='2' class='td-droite fond_menu' style='text-align:center;'>
      <?php
         print("<input type='image' src='$__ICON_DIR/mail_send_22x22_menu.png' alt='Renvoyer les identifiants' name='go_send_id' value='Renvoyer les identifiants'>");
      ?>
   </td>
</tr>
<tr>
   <td class='td-milieu fond_menu'>
      <?php
         // Quelque chose à envoyer ?
         if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_cand,$_DB_propspec
                                          WHERE $_DBC_cand_candidat_id='$candidat_id'
                                          AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                                          AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                          AND $_DBC_cand_periode='$__PERIODE'
                                          AND $_DBC_cand_lock='1'
                                          AND ($_DBC_cand_statut='$__PREC_NON_TRAITEE' OR $_DBC_cand_statut='$__PREC_EN_ATTENTE')")))
         {
            print("<font class='Texte_menu'>
                     Renvoyer le récapitulatif et les listes de justificatifs
                     <br>pour les formations verrouillées
                  </font>");
            $send=1;
         }
         else
         {
            $send=0;
            print("<font class='Texte_important'>
                     Aucune formation verrouillée ou recevabilitée déjà validée
                  </font>");
         }
      ?>
   </td>
   <td colspan='2' class='td-droite fond_menu' style='text-align:center;'>
      <?php
         if($send==1)
            print("<input type='image' src='$__ICON_DIR/mail_send_22x22_menu.png' alt='Renvoyer le récapitulatif et la liste des justificatifs' name='go_send_recap' value='Renvoyer le récapitulatif et la liste des justificatifs'>");
         else
            print("<img src='$__ICON_DIR/stop_22x22_menu.png' alt='Stop' desc='Stop' border='0'>");
      ?>
   </td>
</tr>
<?php
   if($_SESSION["niveau"]!=$__LVL_SUPPORT)
   {
?>      
<tr>
   <td colspan='4' class='fond_page' style='height:10px;'></td>
<tr>
<tr>
   <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><b>Fiche normale / manuelle</b></font>
   </td>
   <td class='td-milieu fond_menu'>
      <?php
         // Possibilité de basculer une fiche du mode manuel (créée par la scol) au mode normal
         // Le mode normal est uniquement disponible si une adresse email est entrée
         if($_SESSION['tab_candidat']['manuelle']==1)
         {
            $manuelle_checked="checked";
            $normale_checked="";
            $font_class=$_SESSION["tab_candidat"]["email"]=="" ? "Textegris" : "Texte";
            $disabled=$_SESSION["tab_candidat"]["email"]=="" ? "disabled" : "";
         }
         else
         {
            $manuelle_checked="";
            $normale_checked="checked";
            $disabled="";
            $font_class="Texte";
         }

         print("<input type='radio' name='mode_manuel' value='0' $normale_checked $disabled><font class='$font_class'>&nbsp;Fiche normale&nbsp;&nbsp;</font><input type='radio' name='mode_manuel' value='1' $manuelle_checked><font class='Texte'>&nbsp;Fiche manuelle</font>
               <br><font class='Texte_10'><i>Une fiche ne peut être \"normale\" que si une adresse électronique est entrée et validée.\n");
      ?>
   </td>
   <td colspan='2' class='td-droite fond_menu' style='text-align:center;'>
      <?php
         print("<input type='image' src='$__ICON_DIR/button_ok_22x22_menu.png' alt='Valider la modification' name='go_mode' value='Valider la modification'>");
      ?>
   </td>
</tr>
<?php
   }
   if($_SESSION["niveau"]==$__LVL_ADMIN)
   {
?>
<tr>
   <td colspan='4' class='fond_page' style='height:10px;'></td>
<tr>
<tr>
   <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><b>Suppression de la fiche</b></font>
   </td>
   <td class='td-milieu fond_menu'>
      <font class='Texte_important_menu'>A manipuler avec une extrême prudence !</font>
   </td>
   <td colspan='2' class='td-droite fond_menu' style='text-align:center;'>
      <?php
         print("<input type='image' src='$__ICON_DIR/trashcan_full_22x22_slick_menu.png' alt='Supprimer' name='go_suppr_fiche' value='Supprimer'>");
      ?>
   </td>
</tr>
<?php
      }
   }
?>
</table>
