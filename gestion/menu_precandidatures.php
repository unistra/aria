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

   if(!isset($_SESSION["candidat_id"]))
   {
      header("Location:index.php");
      exit;
   }

   print("<div class='centered_box'>
            <font class='Texte_16'><strong>$_SESSION[onglet] - Candidatures pour l'année universitaire $__PERIODE - " . ($__PERIODE+1) . " - Triées par ordre de préférence décroissant</strong></font>
         </div>");

   if(in_array($_SESSION["niveau"],array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
      print("<div class='centered_box'>
               <a href='ajout_candidature.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter' title='[Ajouter une précandidature]></a>
               <a href='ajout_candidature.php' target='_self' class='lien2'>Ajouter manuellement une précandidature</a>
            </div>\n");

   if(isset($motivation_vide))
      message("Erreur : la mise en attente d'une précandidature doit obligatoirement être motivée", $__ERREUR);

   if(isset($cand_success) && $cand_success>0)
   {
      if(isset($cand_messages) && $cand_messages>0)
      {
         $au_candidat=$_SESSION['tab_candidat']['civ_texte']=="M." ? "au candidat" : "à la candidate";
            
         $cand_messages_txt=$cand_messages==1 ? "<br><strong>Un message a été envoyé $au_candidat</strong>" : "<br><strong>$cand_messages messages ont été envoyés $au_candidat</strong>";
      }
      else
         $cand_messages_txt="";

      if($cand_success==1)
         message("<center>
                     La précandidature a été modifiée avec succès.
                     $cand_messages_txt
                  </center>", $__SUCCES);
      else
         message("<center>
                     $cand_success précandidatures modifiées avec succès.
                     $cand_messages_txt
                  </center>", $__SUCCES);
   }

   if(isset($rest_succes) && $rest_succes==1)
      message("La précandidature a été restaurée avec succès", $__SUCCES);

   if(isset($succes_date) && $succes_date==1)
      message("Date mise à jour.", $__SUCCES);
?>

<table style='margin:0px auto 0px auto;'>

<?php
   // nombre total de candidatures (une candidature à choix multiples compte comme une seule candidature) pour cette période
   $result=db_query($dbr,"SELECT max($_DBC_cand_ordre) FROM $_DB_cand, $_DB_propspec
                           WHERE $_DBC_cand_candidat_id='$candidat_id'
                           AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                           AND $_DBC_propspec_comp_id=$_SESSION[comp_id]
                           AND $_DBC_cand_periode='$__PERIODE'");

   // on aura un résultat, même vide
   list($nb_cand)=db_fetch_row($result,0);
   db_free_result($result);

   if($nb_cand=="")
      $nb_cand=0;

   // candidatures
   $result=db_query($dbr,"SELECT $_DBC_cand_id, $_DBC_annees_annee, $_DBC_annees_annee_longue, $_DBC_specs_nom,
                                 $_DBC_cand_motivation_decision, $_DBC_cand_statut, $_DBC_cand_ordre_spec, $_DBC_cand_groupe_spec,
                                 $_DBC_cand_ordre, $_DBC_decisions_id, $_DBC_decisions_texte, $_DBC_cand_liste_attente,
                                 $_DBC_cand_transmission_dossier, $_DBC_cand_recours, $_DBC_cand_vap_flag, $_DBC_cand_talon_reponse,
                                 $_DBC_propspec_id, $_DBC_propspec_finalite, $_DBC_propspec_frais, $_DBC_cand_statut_frais,
                                 $_DBC_cand_session_id, $_DBC_propspec_affichage_decisions, $_DBC_cand_entretien_date,
                                 $_DBC_cand_entretien_heure, $_DBC_cand_entretien_lieu, $_DBC_cand_entretien_salle, 
                                 $_DBC_cand_notification_envoyee
                              FROM $_DB_cand, $_DB_specs, $_DB_annees, $_DB_decisions, $_DB_propspec
                           WHERE $_DBC_cand_candidat_id='$candidat_id'
                           AND $_DBC_propspec_annee=$_DBC_annees_id
                           AND $_DBC_propspec_id_spec=$_DBC_specs_id
                           AND $_DBC_cand_decision=$_DBC_decisions_id
                           AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                           AND $_DBC_propspec_comp_id=$_SESSION[comp_id]
                           AND $_DBC_cand_periode='$__PERIODE'
                              ORDER BY $_DBC_cand_ordre, $_DBC_cand_ordre_spec ASC");
                              
   $rows=db_num_rows($result);

   // compteur pour le calcul des frais
   $total_frais_dossiers=0;

   // on a des candidatures, on créé un tableau en variable de session pour accéder rapidement à certains paramètres
   $_SESSION["tab_candidatures"]=array();

   $old_groupe_spec=-1; // initialisation à une valeur négative (positive = numéro de groupe)

   // Options d'affichage
   $colspan_global=3;
   $colspan_annee="colspan='2'";
   $td_class="td-milieu";

   // ================================
   //    Boucle sur les candidatures
   // ================================

   for($i=0; $i<$rows; $i++)
   {
      list($cand_id, $annee_courte, $annee_longue, $nom_specialite, $motivation_decision,$statut,$ordre_spec, $groupe_spec, $ordre,
            $decision_id, $decision_texte, $rang_liste_attente, $transmission_dossier, $recours,$vap, $talon_reponse, $propspec_id,
            $finalite, $frais_dossiers, $statut_frais, $session_id, $affichage_decisions, $ent_date, $ent_heure,
            $ent_lieu, $ent_salle, $notification_envoyee)=db_fetch_row($result,$i);

      // Options de gestion en fonction des droits d'accès de l'utilisateur et de la formations
      if(in_array($_SESSION["niveau"],array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
      {
         // Options de gestion : ajout, suppression, modification de l'ordre et traitement

         // les niveaux "$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN" ont toujours tous les droits sur la composante         
         $options_gestion=1;
         $options_saisie=1;
      }
      elseif($_SESSION["niveau"]=="$__LVL_SCOL_MOINS" && verif_droits_formations($_SESSION["comp_id"], $propspec_id))
      {
         // Scol "moins" : si l'accès est paramétré : gestion complète
         $options_gestion=1;
         $options_saisie=1;
      }
      elseif($_SESSION["niveau"]=="$__LVL_SAISIE" && verif_droits_formations($_SESSION["comp_id"], $propspec_id))
      {   
         // Options de saisie : traitement uniquement
         $options_gestion=0;
         $options_saisie=1;
      }
      else // Droits d'accès insuffisants (consultation uniquement)
      {
         // Colonnes
         // Si la fiche est verrouillée et que les droits sont corrects :
         // 1 : Nom de la formation (spécialité) et (en dessous) Statut de la précandidature
         // 2 : Frais de dossiers
         $options_gestion=0;
         $options_saisie=0;
      }

      // Paramètres d'affichage différents en fonction du verrouillage de la candidature et des droits d'accès
      $lock=(isset($_SESSION['tab_candidat']['array_lock'][$cand_id]) && $_SESSION['tab_candidat']['array_lock'][$cand_id]["lock"]==1) ? "1" : "0";
         
      // Détermination de la session de candidature
      $res_session=db_query($dbr, "SELECT $_DBC_session_id FROM $_DB_session
                                   WHERE $_DBC_session_propspec_id='$propspec_id'
                                   AND $_DBC_session_periode='$__PERIODE'
                                   ORDER BY $_DBC_session_ouverture, $_DBC_session_fermeture");

      $nb_sessions=db_num_rows($res_session);

      if($nb_sessions)
      {
         $array_sessions=db_fetch_all($res_session);
         $session_num=array_search(array("id" => $session_id), $array_sessions);

         if($session_num!==FALSE)
            $session_num="Session " . ($session_num+1);
         else
            $session_num="Session : inconnue";
/*
         else // Problème : aucune session définie pour cette candidature
              // TODO : écrire une fonction pour envoyer un mail d'erreur à l'administrateur
*/         
      }
      else
         $session_num="Session : inconnue";

      db_free_result($res_session);         

      if(!$vap)
         $vap_flag="";
      else
         $vap_flag="<b>VAP/VAE</b> ";

      if($i==($rows-1))
         $derniere_candidature=1;
      else
         $derniere_candidature=0;

      $_SESSION["tab_candidatures"][$cand_id]=array("propspec_id" => $propspec_id,
                                                    "notification_envoyee" => $notification_envoyee);
      if(empty($annee_courte))
         $_SESSION["tab_candidatures"][$cand_id]["filiere"]="$nom_specialite $tab_finalite[$finalite]";
      else
         $_SESSION["tab_candidatures"][$cand_id]["filiere"]="$annee_courte - $nom_specialite $tab_finalite[$finalite]";

      $_SESSION["tab_candidatures"][$cand_id]["groupe_spec"]=$groupe_spec;

      // Tableau : Initialisation des bords

      $choix_multiples_txt="";
   
      // si groupe_spec est >= 0, on a une candidature à choix multiples : il faut afficher l'ordre différemment (en définissant un rowspan dans le tableau)
      // Note 1 : on n'effectue la requête qu'une fois
      // Note 2 : s'il n'y a qu'une candidature dans ce groupe, on ne met pas l'ordre (il faut aller chercher groupe_spec de la réponse suivante dans la requete

      // Candidature à choix multiples
      if($groupe_spec>=0)
      {
         if(!$derniere_candidature) // on regarde le groupe de la précandidature suivante, s'il y en a une
         {
            list($next_groupe_spec)=db_fetch_result($result, ($i+1), 7);

            // La candidature suivante est dans le même groupe : on n'affiche pas le bord inférieur
            if($next_groupe_spec==$groupe_spec)
            {
               $choix_multiples_txt="- Candidature à choix multiples";
               $colspan_suppr="";
            }
         }
         else // toute dernière candidature
            $next_groupe_spec="-1";

         // Par rapport à la candidature précédente :
         if($groupe_spec==$old_groupe_spec) // même groupe
         {
            $nouveau_groupe=0;

            // On affiche l'ordre de la précandidature au sein du groupe
            $ordre_spec_txt="$ordre_spec - ";

            $choix_multiples_txt="- Candidature à choix multiples";

            $colspan_suppr="";
         }
         elseif(!$derniere_candidature) // nouveau groupe et pas la dernière candidature
         {
            $nouveau_groupe=1;

            $result2=db_query($dbr,"SELECT $_DBC_cand_statut FROM $_DB_cand, $_DB_propspec
                                    WHERE $_DBC_cand_candidat_id='$candidat_id'
                                    AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                                    AND $_DBC_propspec_comp_id=$_SESSION[comp_id]
                                    AND $_DBC_cand_groupe_spec='$groupe_spec'
                                    AND $_DBC_cand_periode='$__PERIODE'");

            $nb_choix=db_num_rows($result2);

            // Ordre global : la taille (en nombre de lignes de tableau) dépend du nombre de candidatures dans ce groupe
            $rowspan_ordre_global=3*$nb_choix;

            db_free_result($result2);

            if($next_groupe_spec==$groupe_spec)
            {
               $ordre_spec_txt="$ordre_spec - ";
               $colspan_suppr="";
            }
            else // candidature choix multiples, mais isolée
            {
               $ordre_spec_txt="";
               $colspan_suppr="colspan='2'";
            }

            // On crée un espace entre la nouvelle précandidature et la précédente
            if($i!=0)
               print("</table>
                    </td>
                  </tr>
                  <tr>
                     <td class='fond_page' colspan='$colspan_global' style='height:15px;'></td>
                  </tr>\n");
         }
         else // nouveau groupe et dernière candidature : considéré comme une candidature normale
         {
            $nouveau_groupe=1;
            $colspan_suppr="colspan='2'";

            $ordre_spec_txt="";

            if($i!=0)
               print("</table>
                    </td>
                  </tr>
                  <tr>
                     <td class='fond_page' colspan='$colspan_global' style='height:15px;'></td>
                   </tr>\n");
         }
      }
      else // Groupe = -1 : candidature à choix unique
      {
         $nouveau_groupe=1;
         $colspan_suppr="colspan='2'";

         $rowspan_ordre_global=3;

         // Pas d'affichage de l'ordre de la spécialité
         $ordre_spec_txt="";

         // On crée un espace entre la nouvelle précandidature et la précédente
         if($i!=0)
            print("</table>
                    </td>
                  </tr>
                  <tr>
                     <td class='fond_page' colspan='$colspan_global' style='height:15px;'></td>
                   </tr>\n");
      }

      // stockage de chaque identifiant de candidature avec son statut et sa motivation actuelle
      $_SESSION["tab_candidatures"][$cand_id]["statut"]=$statut;
      $_SESSION["tab_candidatures"][$cand_id]["motivation"]=$motivation_decision;

      switch($statut)
      {
         case $__PREC_NON_TRAITEE      :      // précandidature non traitée
                                             $non_traitee_selected="selected";
                                             $plein_droit_selected=$en_attente_selected=$acceptee_selected=$refusee_selected="";
                                             $font_class='Texte_menu';
                                             $statut_txt="Non traitée";
                                             break;

         case $__PREC_PLEIN_DROIT      :      // entrée de plein droit
                                             $plein_droit_selected="selected";
                                             $non_traitee_selected=$en_attente_selected=$acceptee_selected=$refusee_selected="";
                                             $font_class='Texte_menu';
                                             $statut_txt="Plein droit";
                                             break;

         case $__PREC_RECEVABLE   :            // précandidature recevable
                                             $acceptee_selected="selected";
                                             $plein_droit_selected=$en_attente_selected=$non_traitee_selected=$refusee_selected="";
                                             $font_class='Texte_menu';
                                             $statut_txt="Recevable";
                                             // $motivation_decision="";
                                             break;

         case $__PREC_EN_ATTENTE   :         // précandidature en attente
                                          $en_attente_selected="selected";
                                          $plein_droit_selected=$acceptee_selected=$non_traitee_selected=$refusee_selected="";
                                          $font_class='Texte_menu';
                                          $statut_txt="Dossier mis en attente";
                                          break;

         case $__PREC_NON_RECEVABLE   :      // précandidature non recevable
                                          $refusee_selected="selected";
                                          $plein_droit_selected=$en_attente_selected=$acceptee_selected=$non_traitee_selected="";
                                          $font_class='Texte_menu';
                                          $statut_txt="Non recevable";
                                          break;

         case $__PREC_ANNULEE   :            // précandidature annulée par le candidat
                                          $font_class='Textegris';
                                          $statut_txt="Annulée par le candidat";
                                          break;

         default   :   // par défaut : précandidature non traitée
                     $statut_txt="<font class='Texte_menu'>Non traitée</font>";
                     $font_class='Texte_menu';
                     $statut_txt="Non traitée";
                     break;
      }

      // On commence à remplir la ligne du tableau

      // ==========================
      //    Année et ordre global
      // ==========================
      if($nouveau_groupe)
      {
         print("<tr>
                  <td class='td-gauche fond_menu2'>");
         if($options_gestion)
         {
            if($ordre>1)
               print("<a href='cand_up.php?cand_id=$cand_id' target='_self' class='lien2' style='vertical-align:middle'><img style='vertical-align:middle' src='$__ICON_DIR/up_16x16_menu2.png' alt='Monter' border='0'></a> \n");

            if($ordre<$nb_cand)
               print("<a href='cand_down.php?cand_id=$cand_id' target='_self' class='lien2' style='vertical-align:middle'><img style='vertical-align:middle' src='$__ICON_DIR/down_16x16_menu2.png' alt='Descendre' border='0'></a>\n");
         }

         print("</td>
                  <td class='$td_class fond_menu2'>
                  <font class='Texte_menu2'>
                     <b>Choix n°$ordre - $annee_longue</b> $choix_multiples_txt <b>- $session_num</b>
                  </font>
               </td>\n");

         $lockdate_txt=$_SESSION['tab_candidat']['array_lock'][$cand_id]["lockdate_txt"];

         $lock_j=date("d", $_SESSION['tab_candidat']['array_lock'][$cand_id]["lockdate"]);
         $lock_m=date("m", $_SESSION['tab_candidat']['array_lock'][$cand_id]["lockdate"]);
         $lock_a=date("Y", $_SESSION['tab_candidat']['array_lock'][$cand_id]["lockdate"]);

         if($lock && $options_gestion)
         {
            print("<td class='td-droite fond_menu2'>
                     <img class='icone' src='$__ICON_DIR/button_ok_22x22_menu2.png' border='0'>
                     <font class='Textevert_menu'>Traitable depuis le (J/M/A) :
                        <input type='text' name='jour_verr_$cand_id' value='$lock_j' size='2' maxlength='2'>&nbsp;
                        <input type='text' name='mois_verr_$cand_id' value='$lock_m' size='2' maxlength='2'>&nbsp;
                        <input type='text' name='annee_verr_$cand_id' value='$lock_a' maxlength='4' size='4'>
                        <input type='submit' name='newlockdate_$cand_id' value='Changer la date'>
                        <input type='submit' name='unlock_$cand_id' value='Déverrouiller'>
                      </font>
                  </td>\n");
         }
         elseif($options_gestion)
         {
            print("<td class='td-droite fond_menu2'>
                     <img class='icone' src='$__ICON_DIR/stop_22x22_menu2.png' border='0'>
                     <font class='Texte_important_menu'>Verrouillage prévu le (J/M/A) :
                        <input type='text' name='jour_verr_$cand_id' value='$lock_j' size='2' maxlength='2'>&nbsp;
                        <input type='text' name='mois_verr_$cand_id' value='$lock_m' size='2' maxlength='2'>&nbsp;
                        <input type='text' name='annee_verr_$cand_id' value='$lock_a' maxlength='4' size='4'>
                        <input type='submit' name='newlockdate_$cand_id' value='Changer la date'>
                        <input type='submit' name='lock_$cand_id' value='Verrouiller'>
                     </font>
                  </td>\n");
         }
         else
            print("<td class='td-droite fond_menu2'></td>\n");

         print("</tr>
                <tr>
                  <td colspan='$colspan_global'>
                     <table width='100%'>\n");
      }

      // ========================
      //       Spécialité
      // ========================

      if($options_gestion)
      {
         // Suppression
         print("<tr>
                  <td class='td-gauche fond_menu' rowspan='3' width='20' $colspan_suppr>\n");

         if($lock)
            print("<a href='suppr_cand.php?cand_id=$cand_id&groupe=$groupe_spec&ordre_spec=$ordre_spec' target='_self' class='lien2'><img style='vertical-align:middle;' src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' alt='Supprimer' width='18' height='18' border='0'></a>\n");

         print("</td>\n");

         // Groupe à choix multiples (avec plusieurs choix) ? => flèches
         if($groupe_spec>-1 && ((isset($next_groupe_spec) && $next_groupe_spec==$groupe_spec) || (isset($old_groupe_spec) && $old_groupe_spec==$groupe_spec)))
         {
            print("<td class='td-milieu fond_menu' rowspan='3'>\n");

            if($ordre_spec!=1)
               print("<a href='cand_up.php?cand_id=$cand_id&groupe=$groupe_spec' target='_self' class='lien2'><img style='vertical-align:middle;' src='$__ICON_DIR/up_16x16_menu.png' alt='Monter' width='16' height='16' border='0'></a> \n");

            if($ordre_spec!=$nb_choix)
               print("<a href='cand_down.php?cand_id=$cand_id&groupe=$groupe_spec' target='_self' class='lien2'><img style='vertical-align:middle;' src='$__ICON_DIR/down_16x16_menu.png' alt='Descendre' width='16' height='16' border='0'></a> \n");

            print("</td>\n");
         }
      }
      else      
         print("<tr>
                  <td class='td-gauche fond_menu' rowspan='3' width='20' $colspan_suppr></td>\n");

      $annee=($annee_courte=="") ? "" : "$annee_courte ";

      print("<td class='$td_class fond_menu' style='white-space:normal;'>
               <font class='$font_class'>
                  <b><u>$ordre_spec_txt$annee$nom_specialite " . $tab_finalite[$finalite] . " $vap_flag</u></b>
               </font>
            </td>\n");

      // Affichage des Frais de dossiers
      if($frais_dossiers!="" && $frais_dossiers>0 && $statut!=$__PREC_ANNULEE)
      {
         $frais_dossiers_txt="<font class='$font_class'>Frais : $frais_dossiers eur</font>";
         $total_frais_dossiers+=$frais_dossiers;
      }
      else
         // $frais_dossiers_txt="<font class='$font_class'>Frais : Aucun</font>";
         $frais_dossiers_txt="";
   
      print("<td class='td-droite fond_menu' colspan='2'>
               $frais_dossiers_txt
            </td>
         </tr>\n");

      // ================================================
      //       RECEVABILITE
      // ================================================

      if($statut==$__PREC_NON_RECEVABLE || $statut==$__PREC_EN_ATTENTE)
      {
         if(isset($new_motivation[$cand_id]))
         {
            $mot=$new_motivation[$cand_id];
            $motivation_txt=htmlspecialchars(stripslashes($mot), ENT_QUOTES, $default_htmlspecialchars_encoding);
         }
         else
            $motivation_txt=htmlspecialchars(stripslashes($motivation_decision), ENT_QUOTES, $default_htmlspecialchars_encoding);
      }
      else
         $motivation_txt="";

      // Fiche non verrouillée ou mode consultation : lecture seule
      if((!$options_gestion && !$options_saisie) || !$lock)
      {
         $motivation=$motivation_txt=="" ? "" : "(Motivation : $motivation_txt)";

         print("<tr>
                  <td class='$td_class fond_menu'>
                     <font class='$font_class'>Recevabilité : $statut_txt $motivation</font>
                  </td>\n");
      }
      elseif(($options_gestion || $options_saisie) && $lock && ($decision_id==$__DOSSIER_NON_TRAITE || $statut==$__PREC_NON_TRAITEE) && $statut != $__PREC_ANNULEE)
      {
         // Si la décision de commission n'a pas encore été rendue, on peut encore modifier la recevabilité
         print("<tr>
                  <td class='$td_class fond_menu'>
                     <font class='$font_class'>
                        Recevabilité :
                        <select name='statut[$cand_id]' size='1'>
                           <option value='$__PREC_NON_TRAITEE' $non_traitee_selected>Non traitée</option>
                           <option value='$__PREC_PLEIN_DROIT' $plein_droit_selected>Plein droit</option>
                           <option value='$__PREC_EN_ATTENTE' $en_attente_selected>Mettre en attente</option>
                           <option value='$__PREC_RECEVABLE' $acceptee_selected>Recevable</option>
                           <option value='$__PREC_NON_RECEVABLE' $refusee_selected>Non recevable</option>
                        </select>&nbsp;
                        Motivation : 
                        <input type='text' name='motivation[$cand_id]' value='$motivation_txt' size='40' maxlength='1024'>
                     </font>
                  </td>\n");
      }
      else // Fiche verrouillée et décision de commission rendue
      {
         $motivation=$motivation_txt=="" ? "" : "(Motivation : $motivation_txt)";

         print("<tr>
                  <td class='$td_class fond_menu'>
                     <font class='$font_class'>Recevabilité : $statut_txt $motivation</font>\n");

         if($statut==$__PREC_ANNULEE) // possibilité de supprimer l'annulation
         {
            $crypt_params=crypt_params("cand_id=$cand_id&r=1");
            print("<a href='$php_self?p=$crypt_params' target='_self' class='lien_menu_gauche' style='padding-left:10px;'>Restaurer cette candidature</a>");
         }

         print("</td>\n");
      }

      // ===========================================
      //    Statut des frais de dossiers
      // ===========================================

      print("<td class='td-droite fond_menu' colspan='2'>
               <font class='$font_class'>");

      if($options_gestion && !$lock && $frais_dossiers)
      {
         switch($statut_frais)
         {
            case $__STATUT_FRAIS_EN_ATTENTE   :   // vide (en attente)
                        print("<b>Frais</b> : En attente");
                        break;

            case $__STATUT_FRAIS_ACQUITTES   :   // frais payés
                        print("<b>Frais</b> : Acquittés");
                        break;

            case $__STATUT_FRAIS_BOURSIER   :   // Candidat Boursier
                        print("<b>Frais</b> : Candidat boursier");
                        break;

            case $__STATUT_FRAIS_DISPENSE : // candidat dispensé des frais
                        print("<b>Frais</b> : Candidat dispensé");
                        break;

            case $__STATUT_FRAIS_NON_ACQUITTES   :   // non payés
                        print("<b>Frais</b> : Non acquittés");
                        break;

            default : // vide
                        print("<b>Frais</b> : En attente");
                        break;
         }
      }
      elseif($options_gestion && $lock && $frais_dossiers)
      {
         print("<b>Frais</b> : <select name='statut_frais[$cand_id]' size='1'>\n");

         switch($statut_frais)
         {
            case $__STATUT_FRAIS_EN_ATTENTE   :   // vide (en attente)
                        $selected_en_attente="selected=1";
                        $selected_dispense=$selected_boursier=$selected_payes=$selected_non_payes="";
                        break;

            case $__STATUT_FRAIS_ACQUITTES   :   // frais payés
                        $selected_payes="selected=1";
                        $selected_dispense=$selected_boursier=$selected_en_attente=$selected_non_payes="";
                        break;

            case $__STATUT_FRAIS_BOURSIER   :   // Boursier
                        $selected_boursier="selected=1";
                        $selected_dispense=$selected_en_attente=$selected_payes=$selected_non_payes="";
                        break;

            case $__STATUT_FRAIS_DISPENSE : // dispensé
                        $selected_dispense="selected=1";
                        $selected_boursier=$selected_en_attente=$selected_payes=$selected_non_payes="";
                        break;

            case $__STATUT_FRAIS_NON_ACQUITTES   :   // non payés
                        $selected_non_payes="selected=1";
                        $selected_dispense=$selected_boursier=$selected_payes=$selected_en_attente="";
                        break;

            default :    // vide
                        $selected_en_attente="selected=1";
                        $selected_dispense=$selected_boursier=$selected_payes=$selected_non_payes="";
                        break;
         }

         print("<option value='$__STATUT_FRAIS_EN_ATTENTE'  $selected_en_attente>En attente</option>
                  <option value='$__STATUT_FRAIS_ACQUITTES'  $selected_payes>Frais acquittés</option>
                  <option value='$__STATUT_FRAIS_BOURSIER'  $selected_boursier>Candidat boursier</option>
                  <option value='$__STATUT_FRAIS_DISPENSE'  $selected_dispense>Candidat dispensé</option>
                  <option value='$__STATUT_FRAIS_NON_ACQUITTES' $selected_non_payes>Frais non acquittés</option>
               </select>\n");
      }

      print("</font>
            </td>
         </tr>\n");

      // ===================================================================
      //       SECTION COMPEDA POUR LES PRE-CANDIDATURES RECEVABLES
      // ===================================================================

      if($statut == $__PREC_RECEVABLE)
      {
         switch($talon_reponse)
         {
            case 0   :   // talon non renvoyé (par défaut)
                        $talon_txt="Inexistant ou non renvoyé";
                        break;

            case 1   :   // talon renvoyé, inscription confirmée
                        $talon_txt="Admission confirmée";
                        break;

            case -1   :   // talon renvoyé, inscription refusée
                        $talon_txt="Admission refusée";
                        break;

            default :    // talon non renvoyé (par défaut)
                        $talon_txt="Inexistant ou non renvoyé";
                        break;
         }

         if($decision_id<0) // pour les dossiers nécessitant encore un traitement
            $font='Texteorange';
         elseif($decision_id>0) // dossiers traités
            $font='Textevert_menu';
         else
            $font='Texte_important_menu';

         if($recours)
            $decision_texte .= " (sur recours)";

         if($decision_id==$__DOSSIER_LISTE || $decision_id==$__DOSSIER_LISTE_ENTRETIEN)
            $rang="- <b>Rang : $rang_liste_attente</b>";
         else
            $rang="";

         if(($options_gestion || $options_saisie) && $lock)
         {
            // Affichage du statut de publication des résultats
            if(array_key_exists("affichage_decisions", $_SESSION) && $_SESSION["affichage_decisions"]==0)
            {
               // décisions à afficher en permanence
               if($decision_id==$__DOSSIER_ENTRETIEN || $decision_id==$__DOSSIER_ENTRETIEN_TEL)
                  $statut_publication="";
               elseif($affichage_decisions==1)
                  $statut_publication="(décision visible)";
               elseif($affichage_decisions==2)
                  $statut_publication="(décision visible, accès aux lettres éventuelles activé)";
               else
                  $statut_publication="(décision <b>masquée</b> pour le candidat)";
            }
            elseif(array_key_exists("affichage_decisions", $_SESSION) && $_SESSION["affichage_decisions"]==1) // publiées par défaut
               $statut_publication="(décision publiée)";
            elseif(array_key_exists("affichage_decisions", $_SESSION) && $_SESSION["affichage_decisions"]==2) // publiées par défaut, accès à la lettre
               $statut_publication="(décision publiée + lettre)";
            else
             // Variable inconnue ou décisions publiées par défaut
               $statut_publication="";

            // Entretien ? => Affichage de la date, de l'heure, de la salle et du lieu (=adresse)
            if($decision_id==$__DOSSIER_ENTRETIEN || $decision_id==$__DOSSIER_ENTRETIEN_TEL)
            {
               if($ent_date!="" && $ent_date!=0)
                  $ent_date_txt=date_fr("l jS F Y", $ent_date);
               else
                  $ent_date_txt="";

               $ent_heure=date("H", $ent_date);

               if($ent_heure!=0)
               {
                  $ent_minute=date("i", $ent_date);

                  $ent_heure_txt=" à $ent_heure" . "h$ent_minute";
               }
               else
                  $ent_heure_txt="";

               $autres_infos="<b>Date et lieu : </b>$ent_date_txt$ent_heure_txt, $ent_salle<br>$ent_lieu";

               // $autres_infos="<b>Date et lieu : </b>$ent_date, $ent_heure, $ent_salle<br>$ent_lieu";
            }
            elseif($decision_id==$__DOSSIER_EN_ATTENTE || $decision_id==$__DOSSIER_SOUS_RESERVE)
            {
               if(!empty($motivation_decision))
               {
                  $autres_infos="";

                  $motif_array=explode("|",$motivation_decision);
                  $cnt=count($motif_array);

                  for($j=0; $j<$cnt; $j++)
                  {
                     $motif_id=$motif_array[$j];

                     if(is_numeric($motif_id)) // motif provenant de la table motifs_refus
                     {
                        $result2=db_query($dbr,"SELECT $_DBC_motifs_refus_motif, $_DBC_motifs_refus_motif_long
                                                   FROM $_DB_motifs_refus
                                                WHERE $_DBC_motifs_refus_id='$motif_id'");
                        $rows2=db_num_rows($result2);

                        if($rows2)
                           list($txt,$txt_long)=db_fetch_row($result2,0);
                        else
                           $txt=$txt_long="";

                        db_free_result($result2);
                     }
                     else // motif libre
                     {
                        // nettoyage
                        $txt_long="";
                        //$txt=str_replace("@","",$motif_array[$j]);
                        $txt=preg_replace("/^@/","", $motif_array[$j]);
                     }

                     if(!empty($txt_long))
                        $txt=$txt_long;

                     if(!$j)
                        $autres_infos="<br><b>Motif(s) : </b><br>- $txt";
                     else
                        $autres_infos.="<br>- $txt";
                  }
               }
               else
                  $autres_infos="";
            }
            else
               $autres_infos="";

            print("<tr>
                     <td class='$td_class fond_menu' style='vertical-align:top; padding-bottom:15px; white-space:normal;'>
                        <i><a href='decision_candidature.php?cand_id=$cand_id&groupe=$groupe_spec&ordre_spec=$ordre_spec' target='_self' class='lien_menu_gauche'>Commission : </a></i>
                        <font class='$font' style='vertical-align:middle;'>
                           <b>$decision_texte</b> $rang </font><font class='Texte_menu'>$statut_publication
                           <br>$autres_infos
                        </font>
                        <!-- <font class='Texte_menu'>Talon réponse : $talon_txt</font> -->
                     </td>
                     <td class='td-droite fond_menu' style='padding-top:5px; padding-bottom:15px;' colspan='2'>
                        <a href='$__GESTION_DIR/lettres/formulaire_commission.php?cand_id=$cand_id' target='_blank' class='lien_menu_gauche'><img style='vertical-align:middle;' src='$__ICON_DIR/player_fwd_16x16_menu.png' border='0'> Form. Commission</a>\n");

               // Formulaire de commission et Lettres

               if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_lettres_dec, $_DB_lettres, $_DB_lettres_propspec
                                                WHERE $_DBC_lettres_id=$_DBC_lettres_dec_lettre_id
                                                AND $_DBC_lettres_propspec_lettre_id=$_DBC_lettres_id
                                                AND $_DBC_lettres_propspec_propspec_id='$propspec_id'
                                                AND $_DBC_lettres_comp_id='$_SESSION[comp_id]'
                                                AND $_DBC_lettres_dec_dec_id='$decision_id'"))
                  || (db_num_rows(db_query($dbr, "SELECT * FROM $_DB_lettres_dec, $_DB_lettres
                                                WHERE $_DBC_lettres_id=$_DBC_lettres_dec_lettre_id
                                                AND $_DBC_lettres_comp_id='$_SESSION[comp_id]'
                                                AND $_DBC_lettres_dec_dec_id='$decision_id'
                                                AND $_DBC_lettres_choix_multiples='1'
                                                AND $_DBC_lettres_id IN (SELECT $_DBC_lettres_groupes_lettre_id 
                                                                            FROM $_DB_lettres_groupes, $_DB_groupes_spec
                                                                         WHERE $_DBC_lettres_groupes_groupe_id=$_DBC_groupes_spec_groupe 
                                                                         AND $_DBC_groupes_spec_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec 
                                                                                                                WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]' 
                                                                                                                AND $_DBC_propspec_active='1'))
                                                "))
                      // && ($groupe_spec==$old_groupe_spec || $groupe_spec==$old_groupe_spec)
                      && $groupe_spec!=-1))
                  print("<br><a href='$__GESTION_DIR/lettres/generateur_lettres.php?cand_id=$cand_id' target='_blank' class='lien_menu_gauche'><img style='vertical-align:middle;' src='$__ICON_DIR/player_fwd_16x16_menu.png' border='0'> Lettre(s)</a>");
            }
            else
               print("<tr>
                        <td class='$td_class fond_menu' style='vertical-align:top; padding-bottom:15px;'>
                           <font class='Texte menu'>Commission : </font>
                           <font class='$font' style='vertical-align:middle;'>
                              <b>$decision_texte</b> $rang
                              <br>
                           </font>
                           <!-- <font class='Texte_menu'>Talon réponse : $talon_txt</font> -->
                        </td>
                        <td class='td-droite fond_menu' style='padding-top:5px; padding-bottom:15px;' colspan='2'>\n");

            print("</td>
                  </tr>\n");
      }
      else
         print("<tr>
                  <td class='$td_class fond_menu' style='padding-bottom:15px;'>
                     <font class='$font_class'>
                        Commission : Actuellement sans objet
                     </font>
                  </td>
                  <td class='td-droite fond_menu' style='padding-bottom:15px;' colspan='2'></td>
               </tr>\n");

      $old_groupe_spec=$groupe_spec;
   } // fin de la boucle sur les candidatures

   if(($options_gestion || $options_saisie) && $total_frais_dossiers)
      print("    </table>
              </td>
            </tr>
            <tr>
               <td colspan='2'></td>
               <td colspan='3' style='padding-right:20px; padding-left:10px;'>
                  <font class='Texte'><b>Total : $total_frais_dossiers euros</b></font>
               </td>
            </tr>\n");
   elseif($total_frais_dossiers)
      print("      </table>
                </td>
            </tr>
            <tr>
               <td></td>
               <td style='padding-right:20px; padding-left:10px;' colspan='3'>
                  <font class='Texte'><b>Total : $total_frais_dossiers euros</b></font>
               </td>
            </tr>\n");
   elseif($rows)
      print("   </table>
             </td>
            </tr>\n");

   print("</table>
            <br><br>\n");

   if($options_gestion || $options_saisie)
      message("Les statuts 'Plein droit', 'En attente' et 'Non recevable' entraînent automatiquement l'envoi d'un courriel au candidat<br><center>(un courriel par précandidature)</center>", $__WARNING);

   db_free_result($result);

   if($_SESSION['tab_candidat']['lock']!=0 && $_SESSION['tab_candidat']["lock"]!=-1 && ($options_saisie || $options_gestion))
      print("<div class='centered_box'>
               <input type='image' src='$__ICON_DIR/bouton_valider_128x32_fond.png' alt='Valider les modifications' name='go_prec' value='Valider les modifications'>
             </div>");
?>
