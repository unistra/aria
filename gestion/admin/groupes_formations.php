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

   if(isset($_GET["ajout"]) && $_GET["ajout"]==1)
      $ajout=1;
   elseif(isset($_GET["mod"]) && $_GET["mod"]==1)
      $mod=1;

   /*
   if(isset($_POST["valider_auto"]) || isset($_POST["valider_auto_x"]))
   {
      if(array_key_exists("auto", $_POST))
      {
         foreach($_POST["auto"] as $groupe => $auto)
         {
            if($auto=="f" || $auto=="t")
            {
               db_query($dbr, "UPDATE $_DB_groupes_spec SET $_DBU_groupes_spec_auto='$auto' WHERE $_DBU_groupes_spec_groupe='$groupe'");
               $succes=1;
            }
         }
      }
   }
   */
   
   if(isset($_POST["creer"]) || isset($_POST["creer_x"])) // création d'un nouveau groupe
   {
      $propspec_id=$_POST["selection"];
      $nom_groupe=trim($_POST["nom"]);
      
      if(array_key_exists("auto", $_POST))
         $auto=$_POST["auto"];
      else
         $auto="f";
         
        
      // TODO : réfléchir sur la notion de dates communes
      // Entretemps, la réponse est toujours non
      $dates_communes='f';  
         
      /*   
      if(array_key_exists("dates_c", $_POST))
         $dates_communes=$_POST["dates_c"];
      else
         $dates_communes="f";
      */

      if($propspec_id=="")
         $formation_vide=1;
      elseif($nom_groupe=="")
         $nom_vide=1;
      elseif(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_groupes_spec WHERE $_DBC_groupes_spec_nom ILIKE '$nom_groupe'")))
         $nom_existe=1;
      else
      {
         // unicité du couple année/specialité : un couple ne peut appartenir qu'à un seul groupe !
         $result=db_query($dbr,"SELECT $_DBC_groupes_spec_groupe FROM $_DB_groupes_spec
                                 WHERE $_DBC_groupes_spec_propspec_id='$propspec_id'");

         if(db_num_rows($result))
            $groupe_existe_deja=1;
         else
         {
            // sélection de l'identifiant du nouveau groupe
            $result=db_query($dbr,"SELECT max($_DBC_groupes_spec_groupe)+1 FROM $_DB_groupes_spec");
            list($new_id)=db_fetch_row($result,0);
            db_free_result($result);

            // si vide : 1er groupe (=0)
            if($new_id=="")
               $new_id=0;

            // Comportement spécial :
            // Si le $new_id est déjà présent dans la colonne groupe_spec de la table candidature, il faut incrémenter $new_id
            // jusqu'à ce qu'on trouve un identifiant non utilisé
            // TODO : pas terrible, à modifier

            while(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_cand WHERE $_DBC_cand_groupe_spec='$new_id'")))
               $new_id++;

            db_query($dbr,"INSERT INTO $_DB_groupes_spec VALUES('$propspec_id','$new_id','$auto', '$nom_groupe','$dates_communes')");
            db_close($dbr);
   
            header("Location:groupes_formations.php");
            exit();
         }
      }
      $ajout=1;
   }
   
   if(isset($_POST["modifier"]) || isset($_POST["modifier_x"])) // Modifier les propriétés d'un groupe
   {
      $nom_groupe=trim($_POST["nom"]);
      $auto=$_POST["auto"];
      
//    $dates_communes=$_POST["dates_c"];
      $dates_communes='f';
      
      $groupe=$_POST["groupe"];
            
      if($nom_groupe=="")
         $nom_vide=1;
      elseif(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_groupes_spec WHERE $_DBC_groupes_spec_nom ILIKE '$nom_groupe' AND $_DBC_groupes_spec_groupe!='$groupe'")))
         $nom_existe=1;
      else
      {
         db_query($dbr,"UPDATE $_DB_groupes_spec SET $_DBU_groupes_spec_auto='$auto',
                                                     $_DBU_groupes_spec_nom='$nom_groupe',
                                                     $_DBU_groupes_spec_dates_communes='$dates_communes'
                        WHERE $_DBU_groupes_spec_groupe='$groupe'");
         db_close($dbr);
   
         header("Location:groupes_formations.php");
         exit();
      }
      
      $mod=1;
   }
   
   if(isset($_POST["ajouter"]) || isset($_POST["ajouter_x"])) // Ajouter une spécialité à un groupe
   {
      $propspec_id=$_POST["selection"];
      $groupe=$_POST["groupe"];

      if($propspec_id=="")
         $formation_vide=1;
      else
      {
         // unicité du couple année/specialité : un couple ne peut appartenir qu'à un seul groupe !
/*
         $result=db_query($dbr,"SELECT groupe FROM $_DB_groupes_spec WHERE annee_id='$annee_id' AND spec_id='$spec'");
         if(db_num_rows($result))
            $groupe_existe_deja=1;
         else
         {
*/

         $res_nom_auto=db_query($dbr, "SELECT $_DBU_groupes_spec_nom, $_DBU_groupes_spec_auto, $_DBU_groupes_spec_dates_communes 
                                       FROM $_DB_groupes_spec 
                                       WHERE $_DBU_groupes_spec_groupe='$groupe' limit 1");
         
         if(!db_num_rows($res_nom_auto))
         {
            $auto="f";
            $dates_communes="f";
            $nom_groupe="";
         }
         else
            list($nom_groupe, $auto, $dates_communes)=db_fetch_row($res_nom_auto, 0);
            
         db_free_result($res_nom_auto);
         
         db_query($dbr,"INSERT INTO $_DB_groupes_spec VALUES('$propspec_id', '$groupe', '$auto', '$nom_groupe','$dates_communes')");
         db_close($dbr);

         header("Location:groupes_formations.php");
         exit();
      }
      $ajout=1;
   }
   elseif(isset($_POST["suppr"]) || isset($_POST["suppr_x"])) // Supprimer un groupe ou une spécialité (ou les 2)
   {
      $suppr_groupe=$_POST["suppr_groupe"];

      // si $_POST["suppr_spec"] est présent, on ne supprime que la spécialité au sein du groupe
      if(array_key_exists("suppr_propspec", $_POST))
         $suppr_propspec="AND $_DBC_groupes_spec_propspec_id='".$_POST["suppr_propspec"]."'";
      else
         $suppr_propspec="";

      db_query($dbr,"DELETE FROM $_DB_groupes_spec WHERE groupe='$suppr_groupe' $suppr_propspec");
      header("Location:groupes_formations.php");
      exit;
   }

   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();
?>

<div class='main'>
   <?php
      if(isset($ajout) && $ajout==1)
      {
         titre_page_icone("Créer un groupe de formations", "add_32x32_fond.png", 15, "L");
         $ajout=1;
      }
      elseif((isset($_GET["addspec"]) && $_GET["addspec"]==1)
            && (isset($_GET["groupe"]) || (isset($_GET["groupe"]) && $_GET["groupe"]==0) && ctype_digit($_GET["groupe"]))
            && (isset($_GET["annee"]) || (isset($_GET["annee"]) && $_GET["annee"]==0) && ctype_digit($_GET["annee"])))
         titre_page_icone("Ajouter une formation à un groupe", "edit_32x32_fond.png", 15, "L");
      elseif((isset($_GET["supgrp"]) || (isset($_GET["supgrp"]) && $_GET["supgrp"]==0)) && ctype_digit($_GET["supgrp"]))
         titre_page_icone("Supprimer un groupe de formations", "trashcan_full_32x32_slick_fond.png", 15, "L");
      elseif((isset($_GET["supspec"]) || (isset($_GET["supspec"]) && $_GET["supspec"]==0)) && ctype_digit($_GET["supspec"])
         &&  (isset($_GET["groupe"]) || (isset($_GET["groupe"]) && $_GET["groupe"]==0)) && ctype_digit($_GET["groupe"]))
         titre_page_icone("Sortir une formation d'un groupe", "trashcan_full_32x32_slick_fond.png", 15, "L");
      elseif(isset($mod) && $mod==1 && ((isset($groupe) && ctype_digit($groupe)) || (isset($_GET["groupe"]) && ctype_digit($_GET["groupe"]))))
      {
         titre_page_icone("Modifier les propriétés d'un groupe", "edit_32x32_fond.png", 15, "L");
         $mod=1;
      }
      else
         titre_page_icone("Formations à choix multiples", "liste_32x32_fond.png", 15, "L");

      if(isset($succes) && $succes==1)
         message("Option validée", $__SUCCES);

      if(isset($formation_vide))
         message("Erreur : vous devez choisir une formation à ajouter au groupe", $__ERREUR);
         
      if(isset($nom_vide))
         message("Erreur : le nom du groupe ne doit pas être vide", $__ERREUR);

      if(isset($nom_existe))
         message("Erreur : un groupe possédant ce nom existe déjà", $__ERREUR);

      if(isset($groupe_existe_deja))
         message("Erreur : cette formation appartient déjà à un groupe !", $__ERREUR);

      if(isset($groupe_existe_pas) || (isset($groupe_existe_pas) && $groupe_existe_pas==0))
         message("Erreur : le groupe #$groupe_existe_pas n'existe pas.", $__ERREUR);


      if(isset($ajout) && $ajout==1)
      {
         // =================================
         //         CREATION D'UN GROUPE
         // =================================

         message("Les candidatures existantes ne seront pas affectées par l'ajout d'un groupe.", $__INFO);

         print("<form action='$php_self' method='POST' name='form1'>\n");
         
         if(!isset($nom_groupe) && isset($current_nom_groupe))
            $nom_groupe=$current_nom_groupe;
         else
            $nom_groupe="";
            
         if(isset($auto) && $auto=="t")
         {
            $auto_yes_checked="checked='1'";
            $auto_no_checked="";
         }
         else
         {
            $auto="f";
            $auto_no_checked="checked='1'";
            $auto_yes_checked="";
         }
         
         $dates_communes='f';
         
         /*         
         if(isset($dates_communes) && $dates_communes=="t")
         {
            $dates_yes_checked="checked='1'";
            $dates_no_checked="";
         }
         else
         {
            $dates="f";
            $dates_no_checked="checked='1'";
            $dates_yes_checked="";
         }
         */
      ?>

      <table align='center'>
      <tr>
         <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Nom du groupe</b></font>
         </td>
         <td class='td-droite fond_menu'>
            <input type='text' name='nom' value='<?php echo htmlspecialchars(stripslashes($nom_groupe), ENT_QUOTES); ?>'>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Ajout d'une candidature : Ajout automatique<br />de toutes les formations du groupe ?</b></font>
         </td>
         <td class='td-droite fond_menu'>
            <font class='Texte'>
               <input type='radio' name='auto' value='t' <?php echo $auto_yes_checked; ?>>&nbsp;Oui
                <input type='radio' name='auto' value='f' <?php echo $auto_no_checked; ?>>&nbsp;Non
            </font>
         </td>         
      </tr>
      <!--
      <tr>
         <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Dates de sessions communes à toutes<br />les formations du groupe ?</b></font>
         </td>
         <td class='td-droite fond_menu'>
            <font class='Texte'>
               <input type='radio' name='dates_c' value='t' <?php /* echo $dates_yes_checked; */ ?>>&nbsp;Oui
                <input type='radio' name='dates_c' value='f' <?php /* echo $dates_no_checked; */ ?>>&nbsp;Non
            </font>
         </td>
      </tr>
      -->
      <tr>
         <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Formation à ajouter au nouveau groupe</b></font>
         </td>
         <td class='td-droite fond_menu'>
            <select size="1" name="selection">
               <option value=""></option>
               <?php
                  $result=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_id, $_DBC_propspec_finalite
                                             FROM $_DB_propspec, $_DB_specs, $_DB_annees
                                          WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
                                          AND $_DBC_propspec_annee=$_DBC_annees_id
                                          AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                          AND $_DBC_propspec_active='1'
                                          AND $_DBC_propspec_id NOT IN (SELECT $_DBC_groupes_spec_propspec_id FROM $_DB_groupes_spec)
                                          ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom, $_DBC_propspec_finalite");
                  $rows=db_num_rows($result);

                  $prev_annee="--"; // variable initialisée à n'importe quoi

                  // TODO : dans la base compeda, revoir l'utilisation de la table annee (intégration de annees.id dans
                  // proprietes_specialites, par exemple) et répercuter les changements ici
                  for($i=0; $i<$rows; $i++)
                  {
                     list($cur_annee, $spec_nom, $cur_propspec_id, $finalite)=db_fetch_row($result,$i);

                     $nom_finalite=$tab_finalite[$finalite];

                     if($cur_annee!=$prev_annee)
                     {
                        if($i!=0)
                           print("</optgroup>
                                    <option value='' label='' disabled></option>\n");

                        if(empty($cur_annee))
                           $annee_nom="Années particulières";
                        else
                           $annee_nom="$cur_annee";

                        print("<optgroup label='$annee_nom'>\n");

                        $prev_annee=$cur_annee;
                     }

                     if(isset($propspec_id) && $propspec_id==$cur_propspec_id)
                        $selected="selected=1";
                     else
                        $selected="";

                     print("<option value='$cur_propspec_id' label=\"$spec_nom $nom_finalite\" $selected>$spec_nom $nom_finalite</option>\n");
                  }
                  db_free_result($result);
               ?>
            </select>
         </td>
      </tr>
      </table>

      <div class='centered_icons_box'>
         <a href='groupes_formations.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
         <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Créer" name="creer" value="Créer">
         </form>
      </div>

   <?php
      }
      elseif(isset($mod) && $mod==1)
      {
         // =======================================================
         //         MODIFICATION DES PROPRIETES D'UN GROUPE
         // =======================================================

         message("Les candidatures existantes ne seront pas affectées par les modifications validées.", $__WARNING);

         if(isset($_GET["groupe"]))
            $groupe=$_GET["groupe"];

         print("<form action='$php_self' method='POST' name='form1'>
                <input type='hidden' name='groupe' value='$groupe'>\n");
         
         $result=db_query($dbr,"SELECT $_DBC_groupes_spec_auto, $_DBC_groupes_spec_nom, $_DBC_groupes_spec_dates_communes
                                    FROM $_DB_groupes_spec
                                 WHERE $_DBC_groupes_spec_groupe='$groupe'
                                 AND $_DBC_groupes_spec_propspec_id IN (SELECT distinct($_DBC_propspec_id) FROM $_DB_propspec 
                                                                        WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')");

         $rows=db_num_rows($result);
      
         if(!$rows)
            message("Erreur : le groupe passé en paramètre n'existe pas.", $__ERREUR);
         else
         {
            list($current_auto, $current_nom_groupe, $current_dates_communes)=db_fetch_row($result, 0);
            
            if(!isset($nom_groupe))
            {
               if(isset($current_nom_groupe))
                  $nom_groupe=$current_nom_groupe;
               else
                  $nom_groupe="";
            }
            
            if(!isset($auto) && isset($current_auto))
               $auto=$current_auto;
               
            if(!isset($dates_communes) && isset($current_dates_communes))
               $dates_communes=$current_dates_communes;
         
            if(isset($auto) && $auto=="t")
            {
               $auto_yes_checked="checked='1'";
               $auto_no_checked="";
            }
            else
            {
               $auto="f";
               $auto_no_checked="checked='1'";
               $auto_yes_checked="";
            }
         
            $dates_communes='f';
         
            /*
            if(isset($dates_communes) && $dates_communes=="t")
            {
               $dates_yes_checked="checked='1'";
               $dates_no_checked="";
            }
            else
            {
               $dates="f";
               $dates_no_checked="checked='1'";
               $dates_yes_checked="";
            }
            */
         ?>

         <table align='center'>
         <tr>
            <td class='td-gauche fond_menu2'>
               <font class='Texte_menu2'><b>Nom du groupe</b></font>
            </td>
            <td class='td-droite fond_menu'>
               <input type='text' name='nom' value='<?php echo htmlspecialchars(stripslashes($nom_groupe), ENT_QUOTES); ?>'>
            </td>
         </tr>
         <tr>
            <td class='td-gauche fond_menu2'>
               <font class='Texte_menu2'><b>Ajout d'une candidature : Ajout automatique<br />de toutes les formations du groupe ?</b></font>
            </td>
            <td class='td-droite fond_menu'>
               <font class='Texte'>
                  <input type='radio' name='auto' value='t' <?php echo $auto_yes_checked; ?>>&nbsp;Oui
                   <input type='radio' name='auto' value='f' <?php echo $auto_no_checked; ?>>&nbsp;Non
               </font>
            </td>
         </tr>
         <!--
         <tr>
         <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Dates de sessions communes à toutes<br />les formations du groupe ?</b></font>
         </td>
         <td class='td-droite fond_menu'>
            <font class='Texte'>
               <input type='radio' name='dates_c' value='t' <?php /* echo $dates_yes_checked; */ ?>>&nbsp;Oui
                <input type='radio' name='dates_c' value='f' <?php /* echo $dates_no_checked; */ ?>>&nbsp;Non
            </font>
         </td>
      </tr>
      -->
       </table>

         <div class='centered_icons_box'>
            <a href='groupes_formations.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
            <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="modifier" value="Modifier">
            </form>
         </div>
   <?php
         }
         
         db_free_result($result);
      }
      elseif((isset($_GET["addspec"]) && $_GET["addspec"]==1)
            && (isset($_GET["groupe"]) || (isset($_GET["groupe"]) && $_GET["groupe"]==0) && is_numeric($_GET["groupe"]))
            && (isset($_GET["annee"]) || (isset($_GET["annee"]) && $_GET["annee"]==0) && is_numeric($_GET["annee"])))
      {
         // ======================================
         //     AJOUT D'UNE FORMATION A UN GROUPE
         // ======================================
         $annee_id=$_GET["annee"];
         $groupe=$_GET["groupe"];

         print("<form action='$php_self' method='POST' name='form1'>\n");

         print("<input type='hidden' name='groupe' value='$groupe'>\n");
      ?>

      <table align='center'>
      <tr>
         <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Formation à ajouter au groupe</b></font>
         </td>
         <td class='td-droite fond_menu'>
            <select size="1" name="selection">
               <option value=""></option>
               <?php
                  // requete :
                  // - sélection des spécialités disponibles dans la même année
                  // - les spécialités ne sont pas encore dans le groupe (évite l'ajout de doublons)
                  // TODO : généraliser ces requêtes (exemple : ajout d'inscriptions)

                  $result=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_id, $_DBC_propspec_finalite
                                             FROM $_DB_propspec, $_DB_specs, $_DB_annees
                                          WHERE $_DBC_propspec_annee=$_DBC_annees_id
                                          AND $_DBC_propspec_id_spec=$_DBC_specs_id
                                          AND $_DBC_propspec_annee='$annee_id'
                                          AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                          AND $_DBC_propspec_active='1'
                                          AND $_DBC_propspec_id NOT IN (SELECT $_DBC_groupes_spec_propspec_id
                                                                        FROM $_DB_groupes_spec)
                                             ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom, $_DBC_propspec_finalite");
                  // normalement $rows est positif, sinon on n'aurait même pas du arriver là (lien désactivé si aucune formation disponible)
                  // TODO : généraliser aussi la désactivation des liens
                  $rows=db_num_rows($result);

                  $prev_annee="--"; // variable initialisée à n'importe quoi

                  // TODO : dans la base compeda, revoir l'utilisation de la table annee (intégration de annees.id dans
                  // proprietes_specialites, par exemple) et répercuter les changements ici
                  for($i=0; $i<$rows; $i++)
                  {
                     list($cur_annee, $spec_nom, $cur_propspec_id, $finalite)=db_fetch_row($result,$i);

                     $nom_finalite=$tab_finalite[$finalite];

                     if($cur_annee!=$prev_annee)
                     {
                        if($i!=0)
                           print("</optgroup>
                                    <option value='' label='' disabled></option>\n");

                        if(empty($cur_annee))
                           $annee_nom="Années particulières";
                        else
                           $annee_nom="$cur_annee";

                        print("<optgroup label='$annee_nom'>\n");

                        $prev_annee=$cur_annee;
                     }

                     if(isset($propspec_id) && $propspec_id==$cur_propspec_id)
                        $selected="selected=1";
                     else
                        $selected="";

                     print("<option value='$cur_propspec_id' label=\"$spec_nom $nom_finalite\" $selected>$spec_nom $nom_finalite</option>\n");
                  }
                  db_free_result($result);
               ?>
            </select>
         </td>
      </tr>
      </table>

      <div class='centered_icons_box'>
         <a href='groupes_formations.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
         <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Ajouter" name="ajouter" value="Ajouter">
         </form>
      </div>

   <?php
      }
      elseif((isset($_GET["supgrp"]) || (isset($_GET["supgrp"]) && $_GET["supgrp"]==0)) && is_numeric($_GET["supgrp"]))
      {
         // ==============================================
         //         SUPPRESSION COMPLETE D'UN GROUPE
         // ==============================================
         
         message("<center>
                     Les candidatures existantes ne seront pas affectées par la suppression d'un groupe.
                     <br>La suppression n'affecte que le groupe, les formations qu'il contient ne sont pas concernées.
                  </center>", $__INFO);

         print("<form action='$php_self' method='POST' name='form1'>\n");

         $suppr_groupe=$_GET["supgrp"];

         // vérification de l'existence du groupe

         $result=db_query($dbr,"SELECT * FROM $_DB_groupes_spec WHERE $_DBC_groupes_spec_groupe='$suppr_groupe'");
         if(db_num_rows($result)) // le groupe existe
         {
            db_free_result($result);

            // On vérifie que le groupe n'est pas utilisé dans une candidature et on affiche un avertissement au cas où
            if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_cand WHERE $_DBC_cand_periode='$__PERIODE' AND $_DBC_cand_groupe_spec='$suppr_groupe'")))
               $remarque="<br>(Remarque : ce groupe est utilisé dans plusieurs candidatures existantes)";
            else
               $remarque="<br>(Remarque : ce groupe n'est pas utilisé dans les candidatures existantes)";

            message("<center>Etes-vous sûr(e) de vouloir supprimer ce groupe de formations ?
                     $remarque</center>", $__QUESTION);

            print("</font>
                  <input type='hidden' name='suppr_groupe' value='$suppr_groupe'>

                  <div class='centered_icons_box'>
                     <a href='groupes_formations.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
                     <input type='image' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Confirmer la suppression' name='suppr' value='Confirmer la suppression'>
                     </form>
                  </div>\n");
         }
         else // ne devrait jamais arriver, mais par précaution ...
         {
            db_free_result($result);
            $groupe_existe_pas=$suppr_groupe;
         }
      }
      elseif((isset($_GET["supspec"]) || (isset($_GET["supspec"]) && $_GET["supspec"]==0)) && is_numeric($_GET["supspec"])
               &&
               (isset($_GET["groupe"]) || (isset($_GET["groupe"]) && $_GET["groupe"]==0)) && is_numeric($_GET["groupe"]))
      {
         // ==================================================
         //         SUPPRESSION D'UNE FORMATION D'UN GROUPE
         // =================================================

         $groupe=$_GET["groupe"];
         $spec=$_GET["supspec"];

         message("Les candidatures existantes ne seront pas affectées par cette suppression", $__INFO);

         print("<form action='$php_self' method='POST' name='form1'>\n");

         // vérification de l'existence du groupe

         $result=db_query($dbr,"SELECT count(*) FROM $_DB_groupes_spec WHERE $_DBC_groupes_spec_groupe='$groupe'");
         list($count)=db_fetch_row($result,0);

         if($count) // le groupe existe
         {
            db_free_result($result);

            if($count==1)
            {
               // On vérifie que le groupe n'est pas utilisé dans une inscription et on affiche un avertissement au cas où

               if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_cand WHERE $_DBC_cand_periode='$__PERIODE' AND $_DBC_cand_groupe_spec='$groupe'")))
                  $remarque="(Remarque : ce groupe est utilisé par plusieurs candidatures existantes)";
               else
                  $remarque="(Remarque : ce groupe n'est utilisé dans aucune candidature existante)";

               message("<center>Attention : la suppression de cette formation entraînera la suppression du groupe !
                        $remarque</center>", $__WARNING);
            }

            message("Etes-vous sûr(e) de vouloir sortir cette formation de ce groupe ?", $__QUESTION);

            print("<input type='hidden' name='suppr_propspec' value='$spec'>
                   <input type='hidden' name='suppr_groupe' value='$groupe'>

                  <div class='centered_icons_box'>
                     <a href='groupes_formations.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
                     <input type='image' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Confirmer la suppression' name='suppr' value='Confirmer la suppression'>
                     </form>
                  </div>\n");
         }
         else // ne devrait jamais arriver, mais par précaution ...
         {
            db_free_result($result);
            $groupe_existe_pas=$groupe;
         }
      }
      else
      {
         // ==============================================
         //   LISTE DES GROUPES EXISTANTS (menu par défaut)
         // ==============================================

         $result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_specs_mention_id,
                                       $_DBC_groupes_spec_groupe, $_DBC_propspec_finalite, $_DBC_groupes_spec_auto, $_DBC_groupes_spec_nom, 
                                       $_DBU_groupes_spec_dates_communes
                                    FROM $_DB_groupes_spec, $_DB_annees, $_DB_specs, $_DB_propspec
                                 WHERE $_DBC_propspec_annee=$_DBC_annees_id
                                 AND $_DBC_propspec_id_spec=$_DBC_specs_id
                                 AND $_DBC_propspec_id=$_DBC_groupes_spec_propspec_id
                                 AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                 AND $_DBC_propspec_active='1'
                                    ORDER BY $_DBC_groupes_spec_groupe, $_DBC_annees_ordre, $_DBC_propspec_id_spec, $_DBC_propspec_finalite");
         $rows=db_num_rows($result);
      
         if($rows)
         {
            print("<table cellpadding='4' cellspacing='0' border='0' align='center'>");
      
            // initialisation pour repérer les changements
            $old_groupe=-1;
            $old_annee=-1;
      
            for($i=0;$i<$rows;$i++)
            {
               list($propspec_id, $annee_id, $nom_annee, $nom_spec, $mention_id, $groupe, $finalite, $auto, $nom_groupe, $dates_communes)=db_fetch_row($result,$i);

               $nom_finalite=$tab_finalite[$finalite];
               
               if($nom_groupe=="")
                  $nom_groupe="#$groupe (sans nom)";
               else
                  $nom_groupe="\"$nom_groupe\"";

               // noms de l'année et de la spécialité
               if($annee_id!=$old_annee)
                  $old_annee=$annee_id;
         
               if($groupe!=$old_groupe) // nouveau groupe
               {
                  if($i!=0)
                     print("<tr>
                              <td height='20' class='fond_page'></td>
                           </tr>\n");

                  // pour savoir si on peut ajouter une spécialité à ce groupe
                  $result3=db_query($dbr,"SELECT count(*) FROM $_DB_propspec, $_DB_specs
                                             WHERE $_DBC_propspec_annee='$annee_id'
                                          AND $_DBC_specs_id=$_DBC_propspec_id_spec
                                          AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                          AND $_DBC_specs_mention_id='$mention_id'
                                          AND $_DBC_propspec_active='1'
                                          AND $_DBC_propspec_id NOT IN (SELECT $_DBC_groupes_spec_propspec_id FROM $_DB_groupes_spec
                                                                        WHERE $_DBC_groupes_spec_groupe='$groupe')");

                  list($count)=db_fetch_row($result3,0);
                  db_free_result($result3);

                  if($count)
                     $ajout_spec="<a href='$php_self?addspec=1&groupe=$groupe&annee=$annee_id' target='_self' class='lien_menu_gauche'>Ajouter une formation à ce groupe</a>";
                  else
                     $ajout_spec="<font class='Texte_menu2'><i>Aucune formation supplémentaire disponible pour ce groupe</i></font>";

                  if(isset($auto) && $auto=="t")
                  {
                    $auto="Candidature : ajout automatique de toutes les formations : <strong>activé</strong>";
                  /*
                    $yes_checked="checked='1'";
                    $no_checked="";
                  */
                  }
                  else
                  {
                    $auto="Candidature : ajout automatique de toutes les formations : <strong>désactivé</strong>";
                    /*
                    $auto="f";
                    $no_checked="checked='1'";
                    $yes_checked="";
                    */
                  }
                  
                  /*
                  if(isset($dates_communes) && $dates_communes=="t")
                     $dates_communes_txt="Dates de sessions communes : <strong>Oui</strong>";
                  else
                     $dates_communes_txt="Dates de sessions communes : <strong>Non</strong>";
                  */
                  
                  print("<tr>
                           <td class='fond_menu2' nowrap='true' width='16' valign='middle'>
                              <a href='$php_self?supgrp=$groupe' target='_self' class='lien_menu_gauche'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu2.png' alt='Supprimer' width='16' height='16' border='0'></a>
                           </td>
                           <td class='fond_menu2' nowrap='true' valign='middle' colspan='2'>
                              <a href='$php_self?mod=1&groupe=$groupe' target='_self' class='lien_menu_gauche'>Groupe $nom_groupe - Année : $nom_annee</a>
                           </td>
                           <td class='fond_menu2' align='right' nowrap='true' valign='middle'>$ajout_spec</td>
                        </tr>
                        <tr>
                           <td class='fond_menu2' nowrap='true' width='16' valign='middle'></td>
                           <td class='fond_menu2' nowrap='true' valign='middle' colspan='3' align='right'>
                              <font class='Texte_menu2'>
                                 $auto
                                 <!-- <br />$dates_communes_txt -->
                                 <!--
                                 <form action='$php_self' method='POST'>
                                 Mode automatique : l'ajout d'une formation ajoute automatiquement toutes les autres ? 
                                 <input type='radio' name='auto[$groupe]' value='t' $yes_checked>&nbsp;Oui
                                 <input type='radio' name='auto[$groupe]' value='f' $no_checked>&nbsp;Non
                                 <input type='submit' name='valider_auto[$groupe]' value='Valider'>
                                 </form>
                                 -->
                              </font>
                           </td>
                        <tr>
                        
                        </tr>\n");
               }
      
               print("<tr>
                        <td></td>
                        <td align='left' nowrap='true' width='16' valign='middle'>
                           <a href='$php_self?supspec=$propspec_id&groupe=$groupe' target='_self' class='lien2'><img src='$__ICON_DIR/trashcan_full_16x16_slick_fond.png' alt='Supprimer' width='16' height='16' border='0'></a>
                        </td>
                        <td align='left' nowrap='true'>
                           <font class='Texte'>- $nom_spec</font>
                        </td>
                        <td></td>
                     </tr>");
      
               $old_groupe=$groupe;
            }
      
            print("</table>");
         }
         else
         {
            print("<div class='centered_box'>
                     <font class='Texte3'><strong>Aucun groupe défini.</strong></font>
                   </div>");
         }
      
         print("<div class='centered_icons_box'>
                  <a href='index.php?ajout=1' class='lien_bleu'><img src='$__ICON_DIR/back_32x32_fond.png' border='0' alt='Retour au menu Configuration'></a>
                  <a href='$php_self?ajout=1' class='lien_bleu'><img src='$__ICON_DIR/add_32x32_fond.png' border='0' alt='Créer un groupe de formations'></a>
                </div>\n");
      
         db_free_result($result);
      }
      db_close($dbr);
   ?>
</div>
<?php
   pied_de_page();
?>
</body></html>
