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

   include "../../../configuration/aria_config.php";
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

   // Période définie par l'utilisateur
   if(isset($_GET["np"]) && $_GET["np"]==1 && isset($_SESSION["user_periode"]))
      $_SESSION["user_periode"]++;
   elseif(isset($_GET["pp"]) && $_GET["pp"]==1 && isset($_SESSION["user_periode"]))
      $_SESSION["user_periode"]--;
   elseif(!isset($_SESSION["user_periode"]))   // Par défaut, on considère la période actuelle
      $_SESSION["user_periode"]=$__PERIODE;

   $dbr=db_connect();

   // Nombre de sessions et de périodes, pour l'affichage
   $result=db_query($dbr, "SELECT count(*) FROM $_DB_session, $_DB_propspec
                              WHERE $_DBC_propspec_id=$_DBC_session_propspec_id
                              AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                              AND $_DBC_session_periode='$_SESSION[user_periode]'
                              AND $_DBC_propspec_active='1'
                           GROUP BY $_DBC_session_propspec_id
                           ORDER BY count DESC
                           LIMIT 1");

   if(db_num_rows($result))
      list($max_session)=db_fetch_row($result, 0);
   else
      $max_session=0;

   $colspan_annee=$max_session+1;

   db_free_result($result);

   // $_DBC_session_ouverture, $_DBC_session_fermeture, $_DBC_session_reception                  

   $result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_annees_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
                                  $_DBC_mentions_nom,
                                  CASE WHEN $_DBC_propspec_id IN (SELECT $_DBC_groupes_spec_propspec_id FROM $_DB_groupes_spec) 
                                     THEN (SELECT $_DBC_groupes_spec_groupe FROM $_DB_groupes_spec WHERE $_DBC_groupes_spec_propspec_id=$_DBC_propspec_id)
                                     ELSE '-1'
                                  END as groupe_id
                              FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
                           WHERE $_DBC_propspec_annee=$_DBC_annees_id
                           AND $_DBC_propspec_id_spec=$_DBC_specs_id
                           AND $_DBC_specs_mention_id=$_DBC_mentions_id
                           AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                           AND $_DBC_propspec_active='1'
                              ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, groupe_id, $_DBC_specs_nom_court, $_DBC_propspec_finalite");

   $rows=db_num_rows($result);

   if(!$rows)
      $aucune_specialite=1;
   
   // Nettoyage
   unset($_SESSION["new_session_periode"]);
   unset($_SESSION["suppr_session_periode"]);

   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();

   $res_formations=db_query($dbr, "SELECT count(*) FROM $_DB_propspec
                                    WHERE $_DBC_propspec_active='1'
                                    AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'");

   if(!db_num_rows($res_formations))
      $nb_formations=0;
   else
   {
      list($nb_formations)=db_fetch_row($res_formations, 0);

      if($nb_formations=="")
         $nb_formations=0;
   }

   db_free_result($res_formations);
   
?>

<div class='main'>
   <div class='menu_haut_2'>
   <?php
      if($nb_formations!=0)
      {
   ?>
   <a href='session.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/add_16x16_menu2.png"; ?>' alt='+'></a>
   <a href='session.php' target='_self' class='lien_menu_haut_2'>Ajouter une session</a>
   <?php
      }
      if(isset($aucune_specialite) && $aucune_specialite!=1)
      {
   ?>
   <a href='suppr_session.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/trashcan_full_16x16_slick_menu2.png"; ?>' alt='+'></a>
   <a href='suppr_session.php' target='_self' class='lien_menu_haut_2'>Supprimer une session</a>
   <?php
      }

      // Navigation entre les périodes
      // Sessions existantes dans les périodes précédentes / suivantes ?
/*      
      $res_periodes=db_query($dbr, "SELECT count(*) FROM $_DB_session, $_DB_propspec
                                       WHERE $_DBC_propspec_id=$_DBC_session_propspec_id
                                       AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                       AND $_DBC_session_periode='".($_SESSION["user_periode"]-1)."'
                                       AND $_DBC_propspec_active='1'
                                    LIMIT 1");
      if(db_num_rows($res_periodes))
         list($nb_sessions_periode_precedente)=db_fetch_row($res_periodes, 0);
      else
         $nb_sessions_periode_precedente=0;

      $res_periodes=db_query($dbr, "SELECT count(*) FROM $_DB_session, $_DB_propspec
                                       WHERE $_DBC_propspec_id=$_DBC_session_propspec_id
                                       AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                       AND $_DBC_session_periode='".($_SESSION["user_periode"]+1)."'
                                       AND $_DBC_propspec_active='1'
                                    LIMIT 1");
      if(db_num_rows($res_periodes))
         list($nb_sessions_periode_suivante)=db_fetch_row($res_periodes, 0);
      else
         $nb_sessions_periode_suivante=0;


      db_free_result($res_periodes);

      if($nb_sessions_periode_precedente || $nb_sessions_periode_suivante)
      {
*/      
         print("<span style='margin-top:2px; position:absolute; right:4px;'>\n");

//       if($nb_sessions_periode_precedente)

         if($_SESSION["user_periode"]>=$__PERIODE-1)
         {
         
            print("<span>
                     <a href='$php_self?pp=1' target='_self' class='lien_navigation_10'><img style='vertical-align:middle; padding-bottom:4px;' border='0' src='$__ICON_DIR/back_16x16_menu2.png'></a>
                     <a href='$php_self?pp=1' target='_self' class='lien_navigation_10' style='padding-right:10px;'><strong>Année ". ($_SESSION["user_periode"]-1) ."-$_SESSION[user_periode]</strong></a>
                  </span>\n");
         }

//       if($nb_sessions_periode_suivante)

         if($_SESSION["user_periode"]<=$__PERIODE)
         {
            print("<span>
                     <a href='$php_self?np=1' target='_self' class='lien_navigation_10' style='padding-left:10px;'><strong>Année ".($_SESSION["user_periode"]+1)."-".($_SESSION["user_periode"]+2)."</strong></a>
                     <a href='$php_self?np=1' target='_self' class='lien_navigation_10'><img style='vertical-align:middle; padding-bottom:4px' border='0' src='$__ICON_DIR/forward_16x16_menu2.png'></a>
                  </span>\n");
         }

         print("</span>\n");
/*         
      }
*/      
   ?>
   </div>
   <?php
      titre_page_icone("Gestion des sessions de candidatures pour l'année $_SESSION[user_periode]-".($_SESSION["user_periode"]+1), "clock_32x32_fond.png", 15, "L");

      if(isset($_GET["succes"]) && $_GET["succes"]==1)
         message("Informations mises à jour avec succès.", $__SUCCES);

      if($nb_formations==0)
         message("<center>Il n'y a actuellement aucune formation créée (ou activée) pour cette composante.
                  <br>Vous devez d'abord créer (ou activer) des formations, puis des sessions.</center>", $__INFO);
      elseif(isset($aucune_specialite) && $aucune_specialite==1)
         message("<center>Il n'y a actuellement aucune session de candidatures.
                  <br>Cliquez sur \"Ajouter une session\" pour en créer une.</center>", $__INFO);
      else
         message("Cliquez sur les numéros de sessions pour en modifier les dates", $__INFO);
   ?>

   <table cellpadding='0' cellspacing='0' border='0' align='center'>
   <tr>
      <td>
         <?php
            $old_annee_id="===="; // on initialise à n'importe quoi (sauf année existante et valeur vide)
            $old_propspec_id="";
            $old_mention="--";

            $current_session=1; // par défaut

            $_SESSION["all_sessions"]=array();
            $_SESSION["all_sessions_groups"]=array();

            $old_groupe="-1";

            for($i=0; $i<$rows; $i++)
            {
               list($propspec_id, $annee_id, $annee, $spec_nom, $finalite, $mention, $groupe_id)=db_fetch_row($result, $i);

               $rowspan="";
               $nom_finalite=$tab_finalite[$finalite];
               
               if($annee=="")
                  $annee="Années particulières";

               if($groupe_id!="-1" && $groupe_id!=$old_groupe)
               {
                 $res_groupes=db_query($dbr, "SELECT $_DBC_groupes_spec_nom, $_DBC_groupes_spec_dates_communes, count(*) 
                                                 FROM $_DB_groupes_spec, $_DB_propspec
                                              WHERE $_DBC_propspec_id=$_DBC_groupes_spec_propspec_id
                                              AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                              AND $_DBC_propspec_active='1'
                                              AND $_DBC_groupes_spec_groupe='$groupe_id' 
                                                 GROUP BY $_DBC_groupes_spec_nom, $_DBC_groupes_spec_dates_communes");
                 
                 list($nom_groupe, $dates_communes, $nb_groupes)=db_fetch_row($res_groupes, 0);
                 
                 if($nb_groupes!="")
                 {
                    if($dates_communes=='t')
                    {
                       if($nom_groupe=="")
                          $nom_groupe="<i>inconnu</i>";
                       
                       $rowspan_count=$nb_groupes;
                          /*
                       $rowspan="<td class='td-gauche fond_menu' rowspan='$rowspan_count'>
                                   <font class='Texte'>Formations<br>groupées</font>
                                 </td>\n";
                              */
                       $rowspan="";
                              
                       $nom_formation="Groupe \"$nom_groupe\"";
                              
                       // $colspan="colspan='2'";
                       $colspan="";
                       // $colspan_annee++;
                    
                       $group_by="GROUP BY $_DBC_session_id, $_DBC_session_ouverture, $_DBC_session_fermeture, $_DBC_session_reception, $_DBC_session_periode";
                    }
                    else
                    {
                       $colspan=$group_by="";
                       $nom_formation="$spec_nom $nom_finalite";
                    }                    
                 }
                 else
                    $colspan=$group_by="";
               }
               else
               {
                  $colspan="";
                  $nom_formation="$spec_nom $nom_finalite";
                  $group_by="";
               }
                              

               if($annee_id!=$old_annee_id)
               {
                  if($i!=0)
                     print("</tr>
                            </table>\n");

                  print("<table align='center' style='width:100%; margin-bottom:30px;'>
                         <tr>
                           <td class='fond_menu2' align='center' colspan='$colspan_annee' style='padding:4px 20px 4px 20px;'>
                              <font class='Texte_menu2'><b>$annee</b></font>
                           </td>
                         </tr>
                         <tr>
                           <td class='fond_menu2' style='padding:4px 20px 4px 20px;' $colspan>
                              <font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
                           </td>\n");

                  for($s=1; $s<=$max_session; $s++)
                     print("<td class='fond_menu2' style='padding:4px 20px 4px 20px; white-space:nowrap;'>
                              <a href='edit_session.php?n=$s' class='lien_rouge12'><b>Session n°$s</b></font>
                           </td>\n");

                  $current_session=1;
                  $first_spec=1;
                  $old_mention="--";
               }
               else
                  $first_spec=0;

               if($mention!=$old_mention)
               {
                  $span=$max_session+1;
                  
                  if($colspan!="")
                     $span++;

                  if(!$first_spec)
                     print("<tr>
                              <td class='fond_menu2' colspan='$span' style='padding:4px 20px 4px 20px;'>
                                 <font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
                              </td>
                           </tr>\n");

                  $old_mention=$mention;
               }
              
               if($groupe_id=="-1" || $groupe_id!=$old_groupe || ($groupe_id==$old_groupe && $dates_communes=='f'))
               {
                  print("</tr>
                        <tr>
                           $rowspan
                           <td class='td-gauche fond_menu'>
                              <font class='Texte_menu'>$nom_formation</font>
                           </td>\n");

                  // Boucles sur les sessions pour cette formation ou ce groupe
                  $res_sessions=db_query($dbr, "SELECT $_DBC_session_id, $_DBC_session_ouverture, $_DBC_session_fermeture,
                                                       $_DBC_session_reception, $_DBC_session_periode
                                                   FROM $_DB_session
                                                WHERE $_DBC_session_propspec_id='$propspec_id'
                                                AND $_DBC_session_periode='$_SESSION[user_periode]'
                                                $group_by
                                                ORDER BY $_DBC_session_ouverture, $_DBC_session_fermeture, $_DBC_session_reception ");
   
                  $nb_sessions=db_num_rows($res_sessions);
   
                  if(!$nb_sessions)
                  {
                     for($j=0; $j<$max_session; $j++)
                     {
                        print("<td class='td-milieu fond_menu' style='text-align:center; width:10%;'>
                                 <font class='Texte_menu'><img src='$__ICON_DIR/stop_22x22_menu.png' border='0'></font>
                              </td>\n");
                     }
                     
                     $s_ouverture=$s_fermeture=$s_reception=0;
                  }
                  else
                  {
                     for($j=0; $j<$nb_sessions; $j++)
                     {
                        list($session_id, $s_ouverture, $s_fermeture, $s_reception, $s_periode)=db_fetch_row($res_sessions, $j);
   
                        if($s_ouverture>0 && $s_fermeture>0 && $s_reception>0)
                        {
                           $date_ouv_txt=date("Y")==date("Y", $s_ouverture) ? date_fr("j F", $s_ouverture) : date_fr("j M Y", $s_ouverture);
                           $date_ferm_txt=date("Y")==date("Y", $s_fermeture) ? date_fr("j F", $s_fermeture) : date_fr("j M Y", $s_fermeture);
                           $date_rec_txt=date("Y")==date("Y", $s_reception) ? date_fr("j F", $s_reception) : date_fr("j M Y", $s_reception);
   
                           $dates_txt="$date_ouv_txt - $date_ferm_txt<br>Réception dossiers : $date_rec_txt";
                        }
                        else
                           $dates_txt="<img src='$__ICON_DIR/stop_22x22_menu.png' border='0'>";
   
                        print("<td class='td-milieu fond_menu' style='width:10%;'>
                                 <font class='Texte_menu'>$dates_txt</font>
                              </td>\n");
   
                        $current_session=$j+1;
                        
                        if($s_ouverture!="0" && $s_fermeture!="0" && $s_reception!="0")
                        {
                           if(!array_key_exists($propspec_id, $_SESSION["all_sessions"]))
                              $_SESSION["all_sessions"][$propspec_id]=array();
                           
                           $_SESSION["all_sessions"][$propspec_id][$current_session]=array("s_id" => "$session_id",
                                                                                           "ouv" => "$s_ouverture",
                                                                                           "ferm" => "$s_fermeture",
                                                                                           "rec" => "$s_reception",
                                                                                           "periode=" => "$s_periode");
                        }
         
                        if($groupe_id!="-1" && $groupe_id!=$old_groupe_id && $dates_communes=="t" && $s_ouverture!="0" && $s_fermeture!="0" && $s_reception!="0")
                        {
                           if(!array_key_exists($groupe_id, $_SESSION["all_sessions_groups"]))
                              $_SESSION["all_sessions_groups"][$groupe_id]=array();
                           
                           $_SESSION["all_sessions_groups"][$groupe_id][$current_session]=array("s_id" => "$session_id",
                                                                                                "ouv" => "$s_ouverture",
                                                                                                "ferm" => "$s_fermeture",
                                                                                                "rec" => "$s_reception",
                                                                                                "periode=" => "$s_periode");
                                                                                                
                           $res_groupes=db_query($dbr, "SELECT $_DBC_groupes_spec_propspec_id FROM $_DB_groupes_spec, $_DB_propspec 
                                              WHERE $_DBC_groupes_spec_propspec_id=$_DBC_propspec_id
                                              AND $_DBC_propspec_active='1' 
                                              AND $_DBC_groupes_spec_groupe='$groupe_id'
                                              AND $_DBC_groupes_spec_propspec_id!='$propspec_id'");
                                              
                  $nb_specs=db_num_rows($res_groupes);
                  
                  if($nb_specs)
                  {
                     for($s=0; $s<$nb_specs; $s++)
                     {
                        list($grp_propspec_id)=db_fetch_row($res_groupes, $s);
                        
                        $_SESSION["all_sessions"][$grp_propspec_id][$current_session]=array("s_id" => "$session_id",
                                                                                                  "ouv" => "$s_ouverture",
                                                                                                  "ferm" => "$s_fermeture",
                                                                                                  "rec" => "$s_reception",
                                                                                                  "periode=" => "$s_periode");
                            }
                         }
                         
                         db_free_result($res_groupes);
                        }
                     }
   
                     // La formation n'a pas autant de sessions que le nombre maximum : on complète proprement le tableau
   
                     for($j=$nb_sessions; $j<$max_session; $j++)
                     {
                        print("<td class='td-milieu fond_menu' style='text-align:center; width:10%;'>
                                 <font class='Texte_menu'><img src='$__ICON_DIR/stop_22x22_menu.png' border='0'></font>
                              </td>\n");
                     }
                  }
                  
                  db_free_result($res_sessions);
               }

               $old_groupe=$groupe_id;
               $old_annee_id=$annee_id;
            }

            print("</tr>
                   </table>\n");

            db_free_result($result);
            db_close($dbr);

         ?>
      </td>
   </tr>
   </table>
</div>
<?php
   // print_r($_SESSION["all_sessions"]);
   pied_de_page();
?>
</form>

</body></html>
