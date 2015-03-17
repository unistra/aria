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

   $dbr=db_connect();

   if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
      $_SESSION["new_session_periode"]=$_POST["periode"];
   elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]))
   {
      $new_id=$_POST["new_id"];

      if((isset($_POST["all_jour_ouverture"]) && !empty($_POST["all_jour_ouverture"]) && isset($_POST["all_mois_ouverture"]) && !empty($_POST["all_mois_ouverture"]) && isset($_POST["all_annee_ouverture"]) && !empty($_POST["all_annee_ouverture"]))
         && (isset($_POST["all_jour_fermeture"]) && !empty($_POST["all_jour_fermeture"]) && isset($_POST["all_mois_fermeture"]) && !empty($_POST["all_mois_fermeture"]) && isset($_POST["all_annee_fermeture"]) && !empty($_POST["all_annee_fermeture"]))
         && (isset($_POST["all_jour_reception"]) && !empty($_POST["all_jour_reception"]) && isset($_POST["all_mois_reception"]) && !empty($_POST["all_mois_reception"]) && isset($_POST["all_annee_reception"]) && !empty($_POST["all_annee_reception"])))
      {
         $all_jour_ouverture=$_POST["all_jour_ouverture"];
         $all_mois_ouverture=$_POST["all_mois_ouverture"];
         $all_annee_ouverture=$_POST["all_annee_ouverture"];

         $all_jour_fermeture=$_POST["all_jour_fermeture"];
         $all_mois_fermeture=$_POST["all_mois_fermeture"];
         $all_annee_fermeture=$_POST["all_annee_fermeture"];

         $all_jour_reception=$_POST["all_jour_reception"];
         $all_mois_reception=$_POST["all_mois_reception"];
         $all_annee_reception=$_POST["all_annee_reception"];

         if(!ctype_digit($all_annee_fermeture) || $all_annee_fermeture<date("Y"))
            $all_annee_fermeture=date("Y");

         if(!ctype_digit($all_annee_ouverture) || $all_annee_ouverture<date("Y"))
            $all_annee_ouverture=date("Y");

         if(!ctype_digit($all_annee_reception) || $all_annee_reception<date("Y"))
            $all_annee_reception=date("Y");

         $new_date_all_fermeture=MakeTime(23,59,50,$all_mois_fermeture, $all_jour_fermeture, $all_annee_fermeture); // date au format unix : le jour même, le soir
         $new_date_all_ouverture=MakeTime(0,30,0,$all_mois_ouverture, $all_jour_ouverture, $all_annee_ouverture);
         $new_date_all_reception=MakeTime(0,30,0,$all_mois_reception, $all_jour_reception, $all_annee_reception);

         if($new_date_all_fermeture<=$new_date_all_ouverture || $new_date_all_reception<=$new_date_all_fermeture)
            $all_fond="fond_rouge";
         else
         {
            // Vérification : les dates de sessions ne DOIVENT PAS entrer en collision
            $res_collisions=db_query($dbr, "SELECT $_DBC_session_id, $_DBC_session_propspec_id
                                             FROM $_DB_session, $_DB_propspec
                                             WHERE $_DBC_session_propspec_id=$_DBC_propspec_id
                                             AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                             AND $_DBC_propspec_active='1'
                                             AND ($new_date_all_ouverture BETWEEN $_DBC_session_ouverture AND $_DBC_session_fermeture
                                                OR $new_date_all_fermeture BETWEEN $_DBC_session_ouverture AND $_DBC_session_fermeture
                                                OR ('$new_date_all_ouverture' <=$_DBC_session_ouverture AND '$new_date_all_fermeture'>=$_DBC_session_fermeture)
                                                OR ($new_date_all_ouverture<=$_DBC_session_ouverture AND $new_date_all_fermeture>=$_DBC_session_fermeture))");

            if(!db_num_rows($res_collisions))
            {
               db_free_result($res_collisions);

               // Insertion en masse dans la base, avec les valeurs saisies dans le formulaire
               db_query($dbr,"INSERT INTO $_DB_session (SELECT $_DBC_propspec_id, '$new_id', '$new_date_all_ouverture', '$new_date_all_fermeture', '$new_date_all_reception', '$_SESSION[new_session_periode]'
                                                      FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]' AND $_DBC_propspec_active='1')");

               write_evt($dbr, $__EVT_ID_G_SESSION, "Création session $new_id");

               db_close($dbr);

               header("Location:$php_self?succes=1");
               exit();
            }
            else
            {
               // Récupération des sessions qui entrent en collision, pour signalement (tableau encadré)
               $tab_collisions=array();
               $rows_collisions=db_num_rows($res_collisions);

               for($i=0; $i<$rows_collisions; $i++)
               {
                  list($col_session_id, $col_propspec_id)=db_fetch_row($res_collisions, $i);

                  if(!array_key_exists($col_propspec_id, $tab_collisions))
                     $tab_collisions["$col_propspec_id"]=array();

                  array_push($tab_collisions[$col_propspec_id], "$col_session_id");
               }
               
               db_free_result($res_collisions);
               $collision=1;
               $all_fond="fond_orange";
            }
         }
      }
      else
      {
         $global_erreurs_chrono=0;
         $global_erreurs_format=0;

         $req="";

         $result=db_query($dbr,"SELECT $_DBC_propspec_id, 
                                       CASE WHEN $_DBC_propspec_id IN (SELECT $_DBC_groupes_spec_propspec_id FROM $_DB_groupes_spec) 
                                          THEN (SELECT $_DBC_groupes_spec_groupe FROM $_DB_groupes_spec WHERE $_DBC_groupes_spec_propspec_id=$_DBC_propspec_id)
                                       ELSE '-1'
                                       END as groupe_id
                                 FROM $_DB_propspec 
                                 WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                 AND $_DBC_propspec_active='1'
                                 ORDER BY groupe_id");
         $rows=db_num_rows($result);

         $requete="";
         $total_erreurs_format=$total_erreurs_chrono=0;
         $old_groupe_id="--";
         
         $array_fond=array();
         $array_fond_groupe=array();
         
         $array_cur_values=array(); // Tableau à remplir pour conserver les valeurs erronées
         $array_cur_values_groupe=array();

         $erreur=0;

         for($i=0; $i<$rows; $i++)
         {
            list($propspec_id, $groupe_id)=db_fetch_row($result, $i);

            // Texte par défaut (cette valeur sera modifiée en cas d'erreur)
            if($groupe_id=="-1")
               $array_fond[$propspec_id]="fond_menu";
            elseif($groupe_id!=$old_groupe_id)
               $array_fond_groupe[$groupe_id]="fond_menu";

            unset($jour_ouv);
            unset($mois_ouv);
            unset($annee_ouv);
            unset($jour_ferm);
            unset($mois_ferm);
            unset($annee_ouv);
            unset($jour_rec);
            unset($mois_rec);
            unset($annee_rec);
            unset($new_date_ouv);
            unset($new_date_ferm);
            unset($new_date_rec);

            $req_set="";
            $erreur_format=$erreur_chrono=0;
            $array_comp=array();
            
            if($groupe_id=="-1" && array_key_exists($propspec_id, $_POST["jour_ouv"]) && $_POST["jour_ouv"][$propspec_id]!="" 
               && array_key_exists($propspec_id, $_POST["mois_ouv"]) && $_POST["mois_ouv"][$propspec_id]!=""
               && array_key_exists($propspec_id, $_POST["annee_ouv"]) && $_POST["annee_ouv"][$propspec_id]!="")
            {
               $jour_ouv=$_POST["jour_ouv"][$propspec_id];
               $mois_ouv=$_POST["mois_ouv"][$propspec_id];
               $annee_ouv=$_POST["annee_ouv"][$propspec_id];
            }
            elseif($groupe_id!="-1" && array_key_exists($groupe_id, $_POST["g_jour_ouv"]) && $_POST["g_jour_ouv"][$groupe_id]!="" 
               && array_key_exists($groupe_id, $_POST["g_mois_ouv"]) && $_POST["g_mois_ouv"][$groupe_id]!=""
               && array_key_exists($groupe_id, $_POST["g_annee_ouv"]) && $_POST["g_annee_ouv"][$groupe_id]!="")
            {
               $jour_ouv=$_POST["g_jour_ouv"][$groupe_id];
               $mois_ouv=$_POST["g_mois_ouv"][$groupe_id];
               $annee_ouv=$_POST["g_annee_ouv"][$groupe_id];
            }
            
            if(isset($jour_ouv) && isset($mois_ouv) && isset($annee_ouv))
            {
               if(!ctype_digit($jour_ouv) || $jour_ouv<1 || $jour_ouv>31 || !ctype_digit($mois_ouv) || $mois_ouv<1 || $mois_ouv>12)
               {
                  if($groupe_id=="-1")
                     $array_fond[$propspec_id]="#FF0000";
                  else
                     $array_fond_groupe[$groupe_id]="#FF0000";
                     
                  $erreur_format=1;
                  $new_date_ouv="";
               }
               else
               {
                  if(!ctype_digit($annee_ouv) || $annee_ouv<date("Y"))
                     $annee_ouv=date("Y");

                  $new_date_ouv=MakeTime(0,30,0,$mois_ouv, $jour_ouv, $annee_ouv); // date au format unix : le jour même, le matin

                  $req_set.="$_DBU_session_ouverture='$new_date_ouv'";

                  $array_comp[0]=$new_date_ouv;
               }
            }
            else
               $new_date_ouv="";

            if($groupe_id=="-1" && array_key_exists($propspec_id, $_POST["jour_ferm"]) && $_POST["jour_ferm"][$propspec_id]!=""
               && array_key_exists($propspec_id, $_POST["mois_ferm"]) && $_POST["mois_ferm"][$propspec_id]!=""
               && array_key_exists($propspec_id, $_POST["annee_ferm"]) && $_POST["annee_ferm"][$propspec_id]!="")
            {
               $jour_ferm=$_POST["jour_ferm"][$propspec_id];
               $mois_ferm=$_POST["mois_ferm"][$propspec_id];
               $annee_ferm=$_POST["annee_ferm"][$propspec_id];
            }
            elseif($groupe_id!="-1" && array_key_exists($groupe_id, $_POST["g_jour_ferm"]) && $_POST["g_jour_ferm"][$groupe_id]!=""
               && array_key_exists($groupe_id, $_POST["g_mois_ferm"]) && $_POST["g_mois_ferm"][$groupe_id]!=""
               && array_key_exists($groupe_id, $_POST["g_annee_ferm"]) && $_POST["g_annee_ferm"][$groupe_id]!="")
            {
               $jour_ferm=$_POST["g_jour_ferm"][$groupe_id];
               $mois_ferm=$_POST["g_mois_ferm"][$groupe_id];
               $annee_ferm=$_POST["g_annee_ferm"][$groupe_id];
            }
            
            if(isset($jour_ferm) && isset($mois_ferm) && isset($annee_ferm))
            {
               if(!ctype_digit($jour_ferm) || $jour_ferm<1 || $jour_ferm>31 || !ctype_digit($mois_ferm) || $mois_ferm<1 || $mois_ferm>12)
               {
                  if($groupe_id=="-1")
                     $array_fond[$propspec_id]="fond_rouge";
                  else
                     $array_fond_groupe[$groupe_id]="fond_rouge";
                     
                  $erreur_format=1;
                  $new_date_ferm="";
               }
               else
               {
                  if(!ctype_digit($annee_ferm) || $annee_ferm<date("Y"))
                     $annee_ferm=date("Y");

                  $new_date_ferm=MakeTime(23, 59, 50, $mois_ferm, $jour_ferm, $annee_ferm); // date au format unix : le jour même, le soir

                  if($req_set!="")
                     $req_set.=",";

                  $req_set.="$_DBU_session_fermeture='$new_date_ferm'";

                  $array_comp[1]=$new_date_ferm;
               }
            }
            else
               $new_date_ferm="";

            if($groupe_id=="-1" && array_key_exists($propspec_id, $_POST["jour_rec"]) && $_POST["jour_rec"][$propspec_id]!=""
               && array_key_exists($propspec_id, $_POST["mois_rec"]) && $_POST["mois_rec"][$propspec_id]!=""
               && array_key_exists($propspec_id, $_POST["annee_rec"]) && $_POST["annee_rec"][$propspec_id]!="")
            {
               $jour_rec=$_POST["jour_rec"][$propspec_id];
               $mois_rec=$_POST["mois_rec"][$propspec_id];
               $annee_rec=$_POST["annee_rec"][$propspec_id];
            }
            elseif($groupe_id!="-1" && array_key_exists($groupe_id, $_POST["g_jour_rec"]) && $_POST["g_jour_rec"][$groupe_id]!=""
               && array_key_exists($groupe_id, $_POST["g_mois_rec"]) && $_POST["g_mois_rec"][$groupe_id]!=""
               && array_key_exists($groupe_id, $_POST["g_annee_rec"]) && $_POST["g_annee_rec"][$groupe_id]!="")
            {
               $jour_rec=$_POST["g_jour_rec"][$groupe_id];
               $mois_rec=$_POST["g_mois_rec"][$groupe_id];
               $annee_rec=$_POST["g_annee_rec"][$groupe_id];
            }
            
            if(isset($jour_rec) && isset($mois_rec) && isset($annee_rec))
            {
               if(!ctype_digit($jour_rec) || $jour_rec<1 || $jour_rec>31 || !ctype_digit($mois_rec) || $mois_rec<1 || $mois_rec>12)
               {
                  if($groupe_id=="-1")
                     $array_fond[$propspec_id]="fond_rouge";
                  else
                     $array_fond_groupe[$groupe_id]="fond_rouge";
                     
                  $erreur_format=1;
                  $new_date_rec="";
               }
               else
               {
                  if(!ctype_digit($annee_rec) || $annee_rec<date("Y"))
                     $annee_rec=date("Y");

                  $new_date_rec=MakeTime(23, 59, 50, $mois_rec, $jour_rec, $annee_rec); // date au format unix : le jour même, le soir

                  if($req_set!="")
                     $req_set.=",";

                  $req_set.="$_DBU_session_reception='$new_date_rec'";

                  $array_comp[2]=$new_date_rec;
               }
            }
            else
               $new_date_rec="";

            if($new_date_ouv!="" && $new_date_ferm!="" && $new_date_rec!="")
            {
               // Tests chronologiques
               if(count($array_comp) > 1)
               {
                  $old_val="-1";

                  foreach($array_comp as $val)
                  {
                     if($old_val>=$val)
                     {
                        if($groupe_id=="-1")
                           $array_fond[$propspec_id]="fond_rouge";
                        else
                           $array_fond_groupe[$groupe_id]="fond_rouge";
                         
                        $erreur_chrono++;
                        break;
                     }
                     else
                        $old_val=$val;
                  }
               }

               // Vérification : les dates de sessions ne DOIVENT PAS entrer en collision
               $res_collisions=db_query($dbr, "SELECT $_DBC_session_id FROM $_DB_session
                                                WHERE $_DBC_session_propspec_id='$propspec_id'
                                                AND ('$new_date_ouv' BETWEEN $_DBC_session_ouverture AND $_DBC_session_fermeture
                                                   OR '$new_date_ferm' BETWEEN $_DBC_session_ouverture AND $_DBC_session_fermeture
                                                   OR ('$new_date_ouv' <=$_DBC_session_ouverture AND '$new_date_ferm'>=$_DBC_session_fermeture)
                                                   OR ('$new_date_rec'<=$_DBC_session_ouverture AND '$new_date_ferm'>=$_DBC_session_fermeture))");
               
               if($rows_collisions=db_num_rows($res_collisions))
               {
                  $collision=1;
                  
                  if($groupe_id=="-1")
                  {
                     $array_fond[$propspec_id]="fond_orange";
                     
                     if(!isset($tab_collisions))
                       $tab_collisions=array();

                     $tab_collisions[$propspec_id]=array();
                  }
                  else
                  {
                     $array_fond_groupe[$groupe_id]="fond_orange";
                     
                     if(!isset($tab_collisions_groupe))
                       $tab_collisions_groupe=array();

                     $tab_collisions_groupe[$groupe_id]=array();
                  }

                  for($j=0; $j<$rows_collisions; $j++)
                  {
                     list($col_session_id)=db_fetch_row($res_collisions, $j);

                     if($groupe_id=="-1")
                        array_push($tab_collisions[$propspec_id], "$col_session_id");
                     else
                        array_push($tab_collisions_groupe[$groupe_id], "$col_session_id");
                  }
               }
               else
                  $collision=0;

               db_free_result($res_collisions);

               if($erreur_format)
                  $total_erreurs_format++;

               if($erreur_chrono)
                  $total_erreurs_chrono++;

               // Conservation des valeurs en cas d'erreur(s)
               if($erreur_chrono || $erreur_format || $collision)
               {
                  if($groupe_id=="-1")
                     $array_cur_values[$propspec_id]=array("jour_ouv" => $jour_ouv, "mois_ouv" => $mois_ouv, "annee_ouv" => $annee_ouv,
                                                           "jour_ferm" => $jour_ferm, "mois_ferm" => $mois_ferm, "annee_ferm" => $annee_ferm,
                                                           "jour_rec" => $jour_rec, "mois_rec" => $mois_rec, "annee_rec" => $annee_rec);
                  else
                     $array_cur_values_groupe[$groupe_id]=array("jour_ouv" => $jour_ouv, "mois_ouv" => $mois_ouv, "annee_ouv" => $annee_ouv,
                                                                "jour_ferm" => $jour_ferm, "mois_ferm" => $mois_ferm, "annee_ferm" => $annee_ferm,
                                                                "jour_rec" => $jour_rec, "mois_rec" => $mois_rec, "annee_rec" => $annee_rec);
               }

               // Aucune erreur et champs à mettre à jour déterminés : on complète la requête globale
               if(!$erreur_format && !$erreur_chrono && !$collision && $req_set!="")
               {
                  // if(!array_key_exists("$propspec_id",$_SESSION["all_sessions"]) || !array_key_exists($n_intervalle, $_SESSION["all_sessions"][$propspec_id]))
                  $requete.="INSERT INTO $_DB_session VALUES ('$propspec_id','$new_id','$new_date_ouv','$new_date_ferm','$new_date_rec', '$_SESSION[new_session_periode]');\n";
/*
                  else
                     $requete.="UPDATE $_DB_session SET $req_set WHERE $_DBU_session_propspec_id='$propspec_id'
                                                               AND $_DBU_session_id='$new_id'
                                                               AND $_DBU_new_session_periode='$__PERIODE'; ";
*/
                  write_evt($dbr, $__EVT_ID_G_SESSION, "Ajout session $new_id ($propspec_id)");
               }
            }
         } // fin du if(isset($_POST...))
      } // fin du for
   } // fin du else

   // Boucle terminée : on exécute la requête globale pour les champs non erronés
   if(!empty($requete))
      db_query($dbr, $requete);

   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();
?>

<div class='main'>
   <div class='menu_haut_2'>
      <a href='index.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/kdeprint_report_16x16_menu2.png"; ?>' alt='+'></a>
      <a href='index.php' target='_self' class='lien_menu_haut_2'>Liste des sessions</a>
   </div>
   <?php
      print("<form action='$php_self' method='POST' name='form1'>\n");

      // TODO
      // Sélection de la période : temporaire pour les sessions ?
      // Ne faut-il pas préférer un système de sélection de période plus global ? (mais alors comment empêcher de paramétrer
      // des sessions 2009-2010 alors que __PERIODE=2008 ???)
      if(!isset($_SESSION["new_session_periode"]))
      {
         titre_page_icone("Ajout de sessions de candidatures : sélection de l'année", "clock_32x32_fond.png", 15, "L");

         message("<center>
                     Sélectionnez l'année universitaire pour laquelle la session sera valide.
                      <br>Attention : les sessions ne doivent pas se recouvrir, même si les années universitaires sont distinctes.
                  </center>", $__WARNING);
   ?>
      <table align='center'>
      <tr>
         <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Année universitaire concernée par la session : </b></font>
         </td>
         <td class='td-droite fond_menu'>
            <select name='periode'>
               <?php
                  if(isset($current_periode) && $current_periode==($__PERIODE+1))
                  {
                     $selected_suivante="selected";
                     $selected="";
                  }
                  else
                  {
                     $selected_suivante="";
                     $selected="selected";
                  }

                  print("<option value='".($__PERIODE+1)."' $selected_suivante>Année suivante (".($__PERIODE+1). "-" . ($__PERIODE+2) . ")</option>
                         <option value='$__PERIODE' $selected>Année actuelle ($__PERIODE-" . ($__PERIODE+1) . ")</option>\n");
               ?>
            </select>
         </td>
      </tr>
      </table>

      <div class='centered_icons_box'>
         <a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
         <input type="image" src="<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>" alt="Suivant" name="suivant" value="Suivant">
         </form>
      </div>
   <?php
      }
      else
      {
         titre_page_icone("Ajout de sessions de candidatures pour l'année $_SESSION[new_session_periode]-".($_SESSION["new_session_periode"]+1), "clock_32x32_fond.png", 15, "L");

         if((isset($erreur_chrono) && $erreur_chrono) || (isset($erreur_format) && $erreur_format) || (isset($collision) && $collision)
         || (isset($total_erreurs_format) && $total_erreurs_format) || (isset($total_erreurs_chrono) && $total_erreurs_chrono))
            message("Erreur : certaines dates sont incorrectes ou des sessions se recouvrent.", $__ERREUR);

         if(isset($_GET["succes"]) && $_GET["succes"]==1)
            message("Session ajoutée avec succès.", $__SUCCES);

         // Nombre de sessions, pour l'affichage
         $result=db_query($dbr, "SELECT count(*) FROM $_DB_session, $_DB_propspec
                                    WHERE $_DBC_propspec_id=$_DBC_session_propspec_id
                                    AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                    AND $_DBC_session_periode='$_SESSION[new_session_periode]'
                                    AND $_DBC_propspec_active='1'
                                 GROUP BY $_DBC_session_propspec_id
                                 ORDER BY count DESC
                                 LIMIT 1");

         if(db_num_rows($result))
            list($max_session)=db_fetch_row($result, 0);
         else
            $max_session=0;

         $colspan_annee=$max_session+2;

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


         message("<center>
                     Pour chaque formation, les sessions ne doivent <b>en aucun cas</b> se recouvrir.
                     <br>En fonction des dates entrées, les sessions seront automatiquement <b>triées chronologiquement</b>.
                  </center>", $__WARNING);

         $result_session_id=db_query($dbr, "SELECT max($_DBC_session_id) FROM $_DB_session, $_DB_propspec
                                             WHERE $_DBC_propspec_id=$_DBC_session_propspec_id
                                             AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                             AND $_DBC_session_periode='$_SESSION[new_session_periode]'");

         list($max_id)=db_fetch_row($result_session_id, 0);

         $new_id=$max_id=="" ? 1 : ($max_id+1);

         db_free_result($result_session_id);

         $all_class=isset($all_fond) ? $all_fond : "fond_menu";
      ?>

      <input type=hidden name='new_id' value='<?php echo $new_id; ?>'>

      <table align='center'>
      <tr>
         <td class='td-gauche fond_menu2' colspan='2'>
            <font class='Texte_menu2'>
               <b>Nouvelle session pour toutes les formations</b>
               <br><i>Format des dates : JJ / MM / AAAA. Vous pourrez ajuster ces dates par formation par la suite</i>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Date d'ouverture :</b></font>
         </td>
         <td class='td-droite <?php echo $all_class; ?>'>
            <font class='Texte_menu'>
               <?php
                  if(!isset($all_jour_ouverture))
                     $all_jour_ouverture="";

                  if(!isset($all_mois_ouverture))
                     $all_mois_ouverture="";

                  if(!isset($all_annee_ouverture))
                     $all_annee_ouverture=$_SESSION["new_session_periode"];

                  print("J :&nbsp;<input type='text' name='all_jour_ouverture' maxlength='2' size='4' value='$all_jour_ouverture'>&nbsp;
                           M :&nbsp;<input type='text' name='all_mois_ouverture' maxlength='2' size='4' value='$all_mois_ouverture'>&nbsp;
                           A :&nbsp;<input type='text' name='all_annee_ouverture' maxlength='4' size='6' value='$all_annee_ouverture'>");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Date limite de sélection en ligne (fermeture) : </b></font>
         </td>
         <td class='td-droite <?php echo $all_class; ?>'>
            <font class='Texte_menu'>
               <?php
                  if(!isset($all_jour_fermeture))
                     $all_jour_fermeture="";

                  if(!isset($all_mois_fermeture))
                     $all_mois_fermeture="";

                  if(!isset($all_annee_fermeture))
                     $all_annee_fermeture=$_SESSION["new_session_periode"];

                  print("J :&nbsp;<input type='text' name='all_jour_fermeture' maxlength='2' size='4' value='$all_jour_fermeture'>&nbsp;
                           M :&nbsp;<input type='text' name='all_mois_fermeture' maxlength='2' size='4' value='$all_mois_fermeture'>&nbsp;
                           A :&nbsp;<input type='text' name='all_annee_fermeture' maxlength='4' size='6' value='$all_annee_fermeture'>");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Date limite de réception des dossiers papiers (justificatifs) : </b></font>
         </td>
         <td class='td-droite <?php echo $all_class; ?>'>
            <font class='Texte_menu'>
               <?php
                  if(!isset($all_jour_reception))
                     $all_jour_reception="";

                  if(!isset($all_mois_reception))
                     $all_mois_reception="";

                  if(!isset($all_annee_reception))
                     $all_annee_reception=$_SESSION["new_session_periode"];

                  print("J :&nbsp;<input type='text' name='all_jour_reception' maxlength='2' size='4' value='$all_jour_reception'>&nbsp;
                           M :&nbsp;<input type='text' name='all_mois_reception' maxlength='2' size='4' value='$all_mois_reception'>&nbsp;
                           A :&nbsp;<input type='text' name='all_annee_reception' maxlength='4' size='6' value='$all_annee_reception'>");
               ?>
            </font>
         </td>
      </tr>
      </table>

      <br clear='all'><br>
      <table align='center' border='0'>
      <tr>
         <td class='fond_rouge' width='16' style='border:thin solid black;'>&nbsp;</td>
         <td class='fond_page' style='white-space:nowrap; padding-left:5px; padding-right:20px;'>
            <font class='Texte'>Erreur de format ou de cohérence des dates</font>
         </td>
         <td width='16' class='fond_orange' style='border:thin solid black;'>&nbsp;</td>
         <td class='fond_page' style='white-space:nowrap; padding-left:5px; padding-right:20px;'>
            <font class='Texte'>La nouvelle session en recouvre une autre</font>
         </td>
      </tr>
      </table>

      <br clear='all'>

      <table align='center'>
      <?php
         $old_annee_id="===="; // on initialise à n'importe quoi (sauf année existante et valeur vide)
         $old_propspec_id="";
         $old_mention="--";
         $old_groupe_id="--";

         $current_session=1; // par défaut

         $_SESSION["all_sessions"]=array();
         $_SESSION["all_sessions_groups"]=array();

         for($i=0; $i<$rows; $i++)
         {
            list($propspec_id, $annee_id, $annee, $spec_nom, $finalite, $mention, $groupe_id)=db_fetch_row($result, $i);

            if(isset($array_fond[$propspec_id]))
              $fond=$array_fond[$propspec_id];
            elseif(isset($array_fond_groupe[$groupe_id]))
              $fond=$array_fond_groupe[$groupe_id];
            else
              $fond="fond_menu";

            $nom_finalite=$tab_finalite[$finalite];

            $annee=($annee=="") ? "Années particulières" : $annee;
            
            if($groupe_id!="-1" && $groupe_id!=$old_groupe_id)
            {
               $res_groupes=db_query($dbr, "SELECT $_DBC_groupes_spec_nom, count(*) FROM $_DB_groupes_spec , $_DB_propspec
                                               WHERE $_DBC_propspec_id=$_DBC_groupes_spec_propspec_id
                                               AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                               AND $_DBC_propspec_active='1'
                                               AND $_DBC_groupes_spec_groupe='$groupe_id' 
                                            GROUP BY $_DBC_groupes_spec_nom");
                 
               list($nom_groupe, $nb_groupes)=db_fetch_row($res_groupes, 0);
                 
               if($nb_groupes!="")
               {
                  if($nom_groupe=="")
                     $nom_groupe="<i>inconnu</i>";
                       
                  $rowspan_count=$nb_groupes;
                  $rowspan="";
                              
                  $nom_formation="Groupe \"$nom_groupe\"";
                              
                  // $colspan="colspan='2'";
                  $colspan="";
                  // $colspan_annee++;
                    
                  $group_by="GROUP BY $_DBC_session_id, $_DBC_session_ouverture, $_DBC_session_fermeture, $_DBC_session_reception, $_DBC_session_periode";
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

            if(isset($array_cur_values) && array_key_exists($propspec_id, $array_cur_values))
            {
               $jour_ouv=array_key_exists("jour_ouv", $array_cur_values[$propspec_id]) ? $array_cur_values[$propspec_id]["jour_ouv"] : "";
               $mois_ouv=array_key_exists("mois_ouv", $array_cur_values[$propspec_id]) ? $array_cur_values[$propspec_id]["mois_ouv"] : "";
               $annee_ouv=array_key_exists("annee_ouv", $array_cur_values[$propspec_id]) ? $array_cur_values[$propspec_id]["annee_ouv"] : $_SESSION["new_session_periode"];

               $jour_ferm=array_key_exists("jour_ferm", $array_cur_values[$propspec_id]) ? $array_cur_values[$propspec_id]["jour_ferm"] : "";
               $mois_ferm=array_key_exists("mois_ferm", $array_cur_values[$propspec_id]) ? $array_cur_values[$propspec_id]["mois_ferm"] : "";
               $annee_ferm=array_key_exists("annee_ferm", $array_cur_values[$propspec_id]) ? $array_cur_values[$propspec_id]["annee_ferm"] : $_SESSION["new_session_periode"];

               $jour_rec=array_key_exists("jour_rec", $array_cur_values[$propspec_id]) ? $array_cur_values[$propspec_id]["jour_rec"] : "";
               $mois_rec=array_key_exists("mois_rec", $array_cur_values[$propspec_id]) ? $array_cur_values[$propspec_id]["mois_rec"] : "";
               $annee_rec=array_key_exists("annee_rec", $array_cur_values[$propspec_id]) ? $array_cur_values[$propspec_id]["annee_rec"] : $_SESSION["new_session_periode"];
            }
            else
            {
               $jour_ouv=$mois_ouv=$jour_ferm=$mois_ferm=$jour_rec=$mois_rec="";
               $annee_ouv=$annee_ferm=$annee_rec=$_SESSION["new_session_periode"];
            }

            if($annee_id!=$old_annee_id)
            {
               if($i!=0)
                  print("</tr>
                        <tr>
                           <td class='fond_page' colspan='$colspan_annee' height='20'></td>
                        </tr>\n");

               print("<tr>
                        <td class='fond_menu2' align='center' colspan='$colspan_annee' style='padding:4px 20px 4px 20px;'>
                           <font class='Texte_menu2'><b>$annee</b></font>
                        </td>
                     </tr>
                     <tr>
                        <td class='fond_menu2' align='left' style='padding:4px 20px 4px 20px;'>
                           <font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
                        </td>\n");

               for($s=1; $s<=($max_session+1); $s++)
                  print("<td class='fond_menu2' align='center' style='padding:4px 20px 4px 20px; white-space:nowrap;'>
                           <font class='Texte_menu2'>Session n°$s</font>
                        </td>\n");

               print("</tr>\n");

               $current_session=1;
               $first_spec=1;
               $old_mention="--";
            }
            else
            {
               if($i!=0)
                  print("</tr>\n");

               $first_spec=0;
            }

            if($mention!=$old_mention)
            {
               $span=$max_session+2;

               if(!$first_spec)
                  print("<tr>
                           <td class='fond_menu2' colspan='$span' style='white-space:nowrap; padding:4px 20px 4px 20px;'>
                              <font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
                           </td>
                        </tr>\n");

               $old_mention=$mention;
            }
            
            if($groupe_id=="-1" || $groupe_id!=$old_groupe_id)
               print("<tr>
                        <td class='td-gauche fond_menu'>
                           <font class='Texte_menu'>$nom_formation</font>
                        </td>\n");

            if($groupe_id!="-1")
               $_SESSION["all_sessions_groups"][$groupe_id]=array();
            else
               $_SESSION["all_sessions"][$propspec_id]=array();

            // Boucles sur les sessions pour cette formation
            $res_sessions=db_query($dbr, "SELECT $_DBC_session_id, $_DBC_session_ouverture, $_DBC_session_fermeture, $_DBC_session_reception
                                             FROM $_DB_session
                                          WHERE $_DBC_session_propspec_id='$propspec_id'
                                          AND $_DBC_session_periode='$_SESSION[new_session_periode]'
                                          $group_by
                                          ORDER BY $_DBC_session_ouverture, $_DBC_session_fermeture, $_DBC_session_reception");

            $nb_sessions=db_num_rows($res_sessions);

            if(!$nb_sessions)
            {
               if($groupe_id=="-1" || $groupe_id!=$old_groupe_id)
               {
                  $n_jour_ouv=$groupe_id!="-1" ? "g_jour_ouv[$groupe_id]" : "jour_ouv[$propspec_id]";
                  $n_mois_ouv=$groupe_id!="-1" ? "g_mois_ouv[$groupe_id]" : "mois_ouv[$propspec_id]";
                  $n_annee_ouv=$groupe_id!="-1" ? "g_annee_ouv[$groupe_id]" : "annee_ouv[$propspec_id]";
                  $n_jour_ferm=$groupe_id!="-1" ? "g_jour_ferm[$groupe_id]" : "jour_ferm[$propspec_id]";
                  $n_mois_ferm=$groupe_id!="-1" ? "g_mois_ferm[$groupe_id]" : "mois_ferm[$propspec_id]";
                  $n_annee_ferm=$groupe_id!="-1" ? "g_annee_ferm[$groupe_id]" : "annee_ferm[$propspec_id]";
                  $n_jour_rec=$groupe_id!="-1" ? "g_jour_rec[$groupe_id]" : "jour_rec[$propspec_id]";
                  $n_mois_rec=$groupe_id!="-1" ? "g_mois_rec[$groupe_id]" : "mois_rec[$propspec_id]";
                  $n_annee_rec=$groupe_id!="-1" ? "g_annee_rec[$groupe_id]" : "annee_rec[$propspec_id]";
                  
                  print("<td class='td-milieu fond_menu' style='text-align:center'>
                           <table cellpadding='0' cellspacing='0' border='0' align='center'>
                           <tr>
                              <td class='fond_menu' colspan='3' align='center'>
                                 <font class='Texte_important_menu'><b>Nouvelle session</b></font>
                              </td>
                           </tr>
                           <tr>
                              <td align='right' style='padding-right:10px;'><font class='Texte_important'><strong>Ouverture</strong></font></td>
                              <td class='$fond' nowrap='true'>
                                 <font class='Texte_menu'>
                                    J :&nbsp;<input type='text' name='$n_jour_ouv' maxlength='2' size='4' value='$jour_ouv'>&nbsp;
                                    M :&nbsp;<input type='text' name='$n_mois_ouv' maxlength='2' size='4' value='$mois_ouv'>&nbsp;
                                    A :&nbsp;<input type='text' name='$n_annee_ouv' maxlength='4' size='6' value='$annee_ouv'>
                                 </font>
                              </td>
                           </tr>
                           <tr>
                              <td align='right' style='padding-right:10px;'><font class='Texte_important'><strong>Fermeture</strong></font></td>
                              <td class='$fond' nowrap='true'>
                                 <font class='Texte'>
                                    J :&nbsp;<input type='text' name='$n_jour_ferm' maxlength='2' size='4' value='$jour_ferm'>&nbsp;
                                    M :&nbsp;<input type='text' name='$n_mois_ferm' maxlength='2' size='4' value='$mois_ferm'>&nbsp;
                                    A :&nbsp;<input type='text' name='$n_annee_ferm' maxlength='4' size='6' value='$annee_ferm'>
                                 </font>
                              </td>
                           </tr>
                           <tr>
                              <td align='right' style='padding-right:10px;'><font class='Texte_important'><strong>Date limite</strong></font></td>
                              <td class='$fond' nowrap='true'>
                                 <font class='Texte'>
                                    J :&nbsp;<input type='text' name='$n_jour_rec' maxlength='2' size='4' value='$jour_rec'>&nbsp;
                                    M :&nbsp;<input type='text' name='$n_mois_rec' maxlength='2' size='4' value='$mois_rec'>&nbsp;
                                    A :&nbsp;<input type='text' name='$n_annee_rec' maxlength='4' size='6' value='$annee_rec'>
                                 </font>
                              </td>
                           </tr>
                           </table>
                        </td>\n");
   
                  for($j=0; $j<$max_session; $j++)
                     print("<td class='td-milieu fond_menu' style='text-align:center'>
                              <font class='Texte_menu'><img src='$__ICON_DIR/stop_22x22_menu.png' border='0'></font>
                           </td>\n");
               }
            }
            else
            {
               if($groupe_id=="-1" || $groupe_id!=$old_groupe_id)
               {
                  $n_jour_ouv=$groupe_id!="-1" ? "g_jour_ouv[$groupe_id]" : "jour_ouv[$propspec_id]";
                  $n_mois_ouv=$groupe_id!="-1" ? "g_mois_ouv[$groupe_id]" : "mois_ouv[$propspec_id]";
                  $n_annee_ouv=$groupe_id!="-1" ? "g_annee_ouv[$groupe_id]" : "annee_ouv[$propspec_id]";
                  $n_jour_ferm=$groupe_id!="-1" ? "g_jour_ferm[$groupe_id]" : "jour_ferm[$propspec_id]";
                  $n_mois_ferm=$groupe_id!="-1" ? "g_mois_ferm[$groupe_id]" : "mois_ferm[$propspec_id]";
                  $n_annee_ferm=$groupe_id!="-1" ? "g_annee_ferm[$groupe_id]" : "annee_ferm[$propspec_id]";
                  $n_jour_rec=$groupe_id!="-1" ? "g_jour_rec[$groupe_id]" : "jour_rec[$propspec_id]";
                  $n_mois_rec=$groupe_id!="-1" ? "g_mois_rec[$groupe_id]" : "mois_rec[$propspec_id]";
                  $n_annee_rec=$groupe_id!="-1" ? "g_annee_rec[$groupe_id]" : "annee_rec[$propspec_id]";
                  
                  for($j=0; $j<$nb_sessions; $j++)
                  {
                     list($session_id, $s_ouverture, $s_fermeture, $s_reception)=db_fetch_row($res_sessions, $j);
   
                     if($s_ouverture>0 && $s_fermeture>0 && $s_reception>0)
                     {
                        $date_ouv_txt=date("Y")==date("Y", $s_ouverture) ? date_fr("j F", $s_ouverture) : date_fr("j M Y", $s_ouverture);
                        $date_ferm_txt=date("Y")==date("Y", $s_fermeture) ? date_fr("j F", $s_fermeture) : date_fr("j M Y", $s_fermeture);
                        $date_rec_txt=date("Y")==date("Y", $s_reception) ? date_fr("j F", $s_reception) : date_fr("j M Y", $s_reception);
   
                        $dates_txt="$date_ouv_txt - $date_ferm_txt<br>Réception dossiers : $date_rec_txt";
                     }
                     else
                        $dates_txt="<img src='$__ICON_DIR/stop_22x22_menu.png' border='0'>";
   
                     print("<td class='td-milieu fond_menu' style='text-align:center;'>
                              <font class='Texte_menu'>$dates_txt</font>
                           </td>\n");
   
                     $current_session=$j+1;
   
                     if($groupe_id=="-1")
                        $_SESSION["all_sessions_groups"][$groupe_id][$current_session]=array("s_id" => "$session_id",
                                                                                             "ouv" => "$s_ouverture",
                                                                                             "ferm" => "$s_fermeture",
                                                                                             "rec" => "$s_reception");
                     else
                        $_SESSION["all_sessions"][$propspec_id][$current_session]=array("s_id" => "$session_id",
                                                                                        "ouv" => "$s_ouverture",
                                                                                        "ferm" => "$s_fermeture",
                                                                                        "rec" => "$s_reception");
                  }
   
                  print("<td class='td-milieu fond_menu' style='text-align:center;'>
                           <table cellpadding='0' cellspacing='0' border='0' align='center'>
                           <tr>
                              <td class='fond_menu' colspan='3' align='center'>
                                 <font class='Texte_important_menu'><b>Nouvelle session</b></font>
                              </td>
                           </tr>
                           <tr>
                              <td align='right' style='padding-right:10px;'><font class='Texte_important'><strong>Ouverture</strong></font></td>
                              <td class='$fond' nowrap='true'>
                                 <font class='Texte_menu'>
                                    J :&nbsp;<input type='text' name='$n_jour_ouv' maxlength='2' size='4' value='$jour_ouv'>&nbsp;
                                    M :&nbsp;<input type='text' name='$n_mois_ouv' maxlength='2' size='4' value='$mois_ouv'>&nbsp;
                                    A :&nbsp;<input type='text' name='$n_annee_ouv' maxlength='4' size='6' value='$annee_ouv'>
                                 </font>
                              </td>
                           </tr>
                           <tr>
                              <td align='right' style='padding-right:10px;'><font class='Texte_important'><strong>Fermeture</strong></font></td>
                              <td class='$fond' nowrap='true'>
                                 <font class='Texte_menu'>
                                    J :&nbsp;<input type='text' name='$n_jour_ferm' maxlength='2' size='4' value='$jour_ferm'>&nbsp;
                                    M :&nbsp;<input type='text' name='$n_mois_ferm' maxlength='2' size='4' value='$mois_ferm'>&nbsp;
                                    A :&nbsp;<input type='text' name='$n_annee_ferm' maxlength='4' size='6' value='$annee_ferm'>
                                 </font>
                              </td>
                           </tr>
                           <tr>
                              <td align='right' style='padding-right:10px;'><font class='Texte_important'><strong>Date limite</strong></font></td>
                              <td class='$fond' nowrap='true'>
                                 <font class='Texte_menu'>
                                    J :&nbsp;<input type='text' name='$n_jour_rec' maxlength='2' size='4' value='$jour_rec'>&nbsp;
                                    M :&nbsp;<input type='text' name='$n_mois_rec' maxlength='2' size='4' value='$mois_rec'>&nbsp;
                                    A :&nbsp;<input type='text' name='$n_annee_rec' maxlength='4' size='6' value='$annee_rec'>
                                 </font>
                              </td>
                           </tr>
                           </table>
                        </td>\n");
   
                  // La formation n'a pas autant de sessions que le nombre maximum : on complète proprement le tableau
   
                  for($j=$nb_sessions; $j<$max_session; $j++)
                  {
                     print("<td class='td-milieu fond_menu' style='text-align:center;'>
                              <font class='Texte_menu'><img src='$__ICON_DIR/stop_22x22_menu.png' border='0'></font>
                           </td>\n");
                  }
               }
            }

            db_free_result($res_sessions);

            $old_annee_id=$annee_id;
            $old_groupe_id=$groupe_id;
         } // fin du for

         print("</tr>\n");

         db_free_result($result);
         db_close($dbr);
      ?>

      <tr>
         <td class='fond_page' colspan='<?php echo $colspan_annee; ?>' height='20'></td>
      </tr>
      </table>

      <div class='centered_icons_box'>
         <?php
            if(isset($succes))
               print("<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");
            else
               print("<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>\n");
         ?>
         <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
         </form>
      </div>
   <?php
      }
   ?>
</div>
<?php
   pied_de_page();
?>
</form>

</body></html>
