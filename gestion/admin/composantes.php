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

   if(is_file("../../configuration/donnees_defaut.php"))
      include "../../configuration/donnees_defaut.php";

   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;

   verif_auth("$__GESTION_DIR/login.php");
   
   if(!in_array($_SESSION['niveau'], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
   {
      header("Location:$__GESTION_DIR/noaccess.php");
      exit();
   }

   // Ajout, Modification ou suppression
   if(array_key_exists("a", $_GET) && ctype_digit($_GET["a"]))
      $_SESSION["ajout_comp"]=$_GET["a"]==1 ? 1 : 0;
   elseif(!isset($_SESSION["ajout_comp"]))
      $_SESSION["ajout_comp"]=0;

   if(array_key_exists("s", $_GET) && ctype_digit($_GET["s"]))
      $_SESSION["suppression"]=$_GET["s"]==1 ? 1 : 0;
   elseif(!isset($_SESSION["suppression"]))
      $_SESSION["suppression"]=0;

   if(array_key_exists("m", $_GET) && ctype_digit($_GET["m"]))
      $_SESSION["modification"]=$_GET["m"]==1 ? 1 : 0;
   elseif(!isset($_SESSION["modification"]))
      $_SESSION["modification"]=0;

   $dbr=db_connect();
   
   if(isset($_GET["succes"]) && ctype_digit($_GET["succes"]))
      $succes=$_GET["succes"];

   if((isset($_POST["modifier"]) || isset($_POST["modifier_x"])) && array_key_exists("comp_id", $_POST) && ctype_digit($_POST["comp_id"]))
   {
      $comp_id=$_POST["comp_id"];
      $_SESSION["modification"]=1;
   }

   if((isset($_POST["supprimer"]) || isset($_POST["supprimer_x"])) && array_key_exists("comp_id", $_POST) && ctype_digit($_POST["comp_id"]))
   {
      $comp_id=$_POST["comp_id"];
      $_SESSION["suppression"]=1;
   }

   if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
   {
      $new_comp_nom=trim($_POST['nom']);
      $new_comp_adresse=trim($_POST['adresse']);
      $new_comp_scolarite=trim($_POST['scolarite']);
      $new_comp_courriel_scolarite=trim($_POST['courriel_scolarite']);
      $new_comp_contact=trim($_POST['contact']);
      $new_comp_univ_id=$_POST['comp_univ_id'];

      $new_comp_www=trim($_POST['www']);

      $new_comp_verr_delai=trim($_POST['delai_verrouillage']);

      // Traitement particulier pour les noms composés
      $new_comp_directeur=str_replace("- ", "-", ucwords(str_replace("-", "- ", trim($_POST['directeur']))));

      $new_limite_nombre=(array_key_exists("limite_nombre", $_POST) && ctype_digit($_POST["limite_nombre"]) && $_POST["limite_nombre"]>=0) ? $_POST["limite_nombre"] : 0;
      $new_limite_annee=array_key_exists("limite_annee", $_POST) ? $_POST["limite_annee"] : 0;
      $new_limite_annee_mention=array_key_exists("limite_annee_mention", $_POST) ? $_POST["limite_annee_mention"] : 0;

      $new_gestion_motifs=$_POST["gestion_motifs"];

      $new_defaut_affichage_decisions=array_key_exists("affichage_decisions", $_POST) ? $_POST["affichage_decisions"] : 1;

      $new_avertir_decision=array_key_exists("avertir_decision", $_POST) ? $_POST["avertir_decision"] : 0;

      if(empty($new_comp_verr_delai)) // conversion en secondes
         $new_comp_verr_delai=172800; // 48 heures (48*3600)
      else
         $new_comp_verr_delai=$new_comp_verr_delai*3600;

      $new_comp_entretien_salle=trim($_POST['entretien_salle']);
      $new_comp_entretien_lieu=trim($_POST['entretien_lieu']);

      // Détermination de l'ID de la composante
      if(isset($_POST["comp_id"]))
         $comp_id=$_POST["comp_id"];

      // OBSOLETE : ID standards utilisés (bigint)
/*
      else
      {
         // limites pour l'identifiant de la composante (c'est simplement pour les trier facilement)
         $limite_inf=$new_comp_univ_id*100;
         $limite_sup=($new_comp_univ_id+1)*100;

         // identifiant
         $result=db_query($dbr,"SELECT max($_DBC_composantes_id)+1 as max FROM $_DB_composantes
                                 WHERE $_DBC_composantes_id BETWEEN $limite_inf AND $limite_sup");
         list($comp_id)=db_fetch_row($result,0);
         db_free_result($result);

         if(empty($comp_id))
            $comp_id=$limite_inf+1;
      }
*/
      // vérification des champs
      if($new_comp_nom=="") $nom_vide=1;
      if($new_comp_adresse=="") $adresse_vide=1;
      if($new_comp_contact=="") $contact_vide=1;
      if($new_comp_directeur=="") $directeur_vide=1;
      if($new_comp_courriel_scolarite=="") $courriel_scol_vide=1;

      // unicité de la composante
      if(isset($comp_id) && ctype_digit($comp_id))
      {
         if(db_num_rows(db_query($dbr,"SELECT $_DBC_composantes_id FROM $_DB_composantes
                                       WHERE $_DBC_composantes_nom ILIKE '".str_replace("'","''", $new_comp_nom)."'
                                       AND $_DBC_composantes_id!='$comp_id'")))
            $nom_existe="1";
      }

      if(!isset($nom_existe) && !isset($nom_vide) && !isset($adresse_vide) && !isset($contact_vide)) // on peut poursuivre
      {
         if($_SESSION["ajout_comp"]==0)
         {
            db_query($dbr,"UPDATE $_DB_composantes SET   $_DBU_composantes_nom='$new_comp_nom',
                                                         $_DBU_composantes_contact='$new_comp_contact',
                                                         $_DBU_composantes_adresse='$new_comp_adresse',
                                                         $_DBU_composantes_univ_id='$new_comp_univ_id',
                                                         $_DBU_composantes_directeur='$new_comp_directeur',
                                                         $_DBU_composantes_scolarite='".str_replace("'","''", $new_comp_scolarite)."',
                                                         $_DBU_composantes_delai_lock='$new_comp_verr_delai',
                                                         $_DBU_composantes_courriel_scol='$new_comp_courriel_scolarite',
                                                         $_DBU_composantes_limite_cand_nombre='$new_limite_nombre',
                                                         $_DBU_composantes_limite_cand_annee='$new_limite_annee',
                                                         $_DBU_composantes_limite_cand_annee_mention='$new_limite_annee_mention',
                                                         $_DBU_composantes_gestion_motifs='$new_gestion_motifs',
                                                         $_DBU_composantes_ent_salle='$new_comp_entretien_salle',
                                                         $_DBU_composantes_ent_lieu='$new_comp_entretien_lieu',
                                                         $_DBU_composantes_www='$new_comp_www',
                                                         $_DBU_composantes_affichage_decisions='$new_defaut_affichage_decisions',
                                                         $_DBU_composantes_avertir_decision='$new_avertir_decision'
                              WHERE $_DBU_composantes_id='$comp_id'");

            // MAJ du paramètre d'affichage des résultats, si besoin
            // TODO : écrire des fonctions de rafraichissement automatique de ce genre de paramètres
            if($comp_id==$_SESSION["comp_id"])
            {
               $_SESSION["affichage_decisions"]=$new_defaut_affichage_decisions;
               $_SESSION["avertir_decision"]=$new_avertir_decision;
            }

            write_evt($dbr, $__EVT_ID_G_ADMIN, "MAJ Composante $comp_id", "", $comp_id);
         }
         else
         {
            $comp_id=db_locked_query($dbr, $_DB_composantes, "INSERT INTO $_DB_composantes VALUES ('##NEW_ID##','$new_comp_nom', '$new_comp_univ_id', '$new_comp_adresse', '$new_comp_contact', '$new_comp_directeur', '$new_comp_scolarite', '','','','', '$new_comp_verr_delai', '32', '$new_comp_courriel_scolarite', '$new_limite_nombre', '$new_limite_annee', '$new_limite_annee_mention','$new_gestion_motifs', '$new_comp_entretien_salle','$new_comp_entretien_lieu','$new_comp_www', '$new_defaut_affichage_decisions','109','42','$new_avertir_decision')");

            // Une fois la composante créée, on regarde si des données par défaut doivent être renseignées (motifs de refus, ...)
            // TODO : faire ça coté application ou directement via la base ?
            if(isset($GLOBALS["__DEFAUT_MOTIFS"]) && $GLOBALS["__DEFAUT_MOTIFS"]=='t' && function_exists("insert_default_motifs"))
               insert_default_motifs($dbr, $comp_id);

            if(isset($GLOBALS["__DEFAUT_DECISIONS"]) && $GLOBALS["__DEFAUT_DECISIONS"]=='t' && function_exists("insert_default_decisions"))
               insert_default_decisions($dbr, $comp_id);

            write_evt($dbr, $__EVT_ID_G_ADMIN, "Nouvelle composante $comp_id", "", $comp_id);
         }

         // Création du répertoire dédié à la composante s'il n'existe pas
         if(!is_dir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$comp_id"))
            mkdir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$comp_id", 0770);

         db_close($dbr);

         header("Location:$php_self?succes=1");
         exit;
      }
   }
   elseif(isset($_POST["conf_supprimer"]) || isset($_POST["conf_supprimer_x"]))
   {
      $comp_id=$_POST["comp_id"];

      if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_composantes WHERE $_DBC_composantes_id='$comp_id'")))
      {
         db_query($dbr,"DELETE FROM $_DB_composantes WHERE $_DBC_composantes_id='$comp_id'");

         write_evt($dbr, $__EVT_ID_G_ADMIN, "Suppression composante $comp_id", "", $comp_id);

         db_close($dbr);

         header("Location:$php_self?succes=1");
         exit;
      }
      else
         $id_existe_pas=1;
   }

   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();
?>

<div class='main'>
   <?php
      if($_SESSION["ajout_comp"]==1)
         titre_page_icone("Ajouter une composante", "composante_32x32_fond.png", 15, "L");
      elseif($_SESSION["modification"]==1)
         titre_page_icone("Modifier une composante existante", "edit_32x32_fond.png", 15, "L");
      elseif($_SESSION["suppression"]==1)
         titre_page_icone("Supprimer une composante", "trashcan_full_32x32_slick_fond.png", 15, "L");
      else
         titre_page_icone("Gestion des composantes", "composante_32x32_fond.png", 15, "L");

      if(isset($nom_vide))
         message("Erreur : le champ 'nom' ne doit pas être vide", $__ERREUR);

      if(isset($adresse_vide))
         message("Erreur : le champ 'adresse' ne doit pas être vide", $__ERREUR);

      if(isset($contact_vide))
         message("Erreur : le champ 'contact' ne doit pas être vide", $__ERREUR);

      if(isset($nom_existe))
         message("Erreur : cette composante existe déjà !", $__ERREUR);

      if(isset($courriel_scol_vide))
         message("Erreur : le champ 'Adresse électronique de la scolarité' ne doit pas être vide", $__ERREUR);

      if(isset($succes))
      {
         if($_SESSION["modification"]==1)
         {
            message("La composante a été modifiée avec succès.", $__SUCCES);
            $_SESSION["modification"]=0;
         }
         elseif($_SESSION["ajout_comp"]==1)
         {
            message("La composante a été créée avec succès.", $__SUCCES);
            $_SESSION["ajout_comp"]=0;
         }
         elseif($_SESSION["suppression"]==1)
         {
            message("La composante a été supprimée avec succès.", $__SUCCES);
            $_SESSION["suppression"]=0;
         }
      }

      print("<form action='$php_self' method='POST' name='form1'>\n");

      if($_SESSION["ajout_comp"]==0 && $_SESSION["modification"]==0 && $_SESSION["suppression"]==0) // choix de la composante dans le menu
      {
         if($_SESSION["niveau"]!=$__LVL_ADMIN)
         {
            $result=db_query($dbr, "SELECT $_DBC_composantes_id, $_DBC_composantes_nom, $_DBC_composantes_univ_id, $_DBC_universites_nom
                                       FROM $_DB_composantes, $_DB_universites
                                    WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
                                    AND ($_DBC_composantes_id IN (SELECT $_DBC_acces_composante_id FROM $_DB_acces
                                                               WHERE $_DBC_acces_id='$_SESSION[auth_id]')
                                       OR $_DBC_composantes_id IN (SELECT $_DBC_acces_comp_composante_id FROM $_DB_acces_comp
                                                                     WHERE $_DBC_acces_comp_acces_id='$_SESSION[auth_id]'))
                                       ORDER BY $_DBC_composantes_univ_id, $_DBC_composantes_nom ASC");
         }
         else // Administrateurs : accès à toutes les composantes
         {
            $result=db_query($dbr, "SELECT $_DBC_composantes_id, $_DBC_composantes_nom, $_DBC_composantes_univ_id, $_DBC_universites_nom
                                       FROM $_DB_composantes, $_DB_universites
                                    WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
                                    ORDER BY $_DBC_composantes_univ_id, $_DBC_composantes_nom ASC");
         }
         

         $rows=db_num_rows($result);

         print("<table cellpadding='4' cellspacing='0' border='0' align='center'>
               <tr>
                  <td class='fond_menu2' align='right'>
                     <font class='Texte_menu2'><strong>Composante :</strong></font>
                  </td>
                  <td class='fond_menu' align='left'>
                     <select name='comp_id' size='1'>
                        <option value=''></option>\n");

         $old_univ="";

         for($i=0; $i<$rows; $i++)
         {
            list($comp_id, $comp_nom, $comp_univ_id, $univ_nom)=db_fetch_row($result,$i);

            if($comp_univ_id!=$old_univ)
            {
               if($i!=0)
                  print("</optgroup>
                           <option value='' label='' disabled></option>\n");

               print("<optgroup label='".htmlspecialchars(stripslashes($univ_nom), ENT_QUOTES)."'>\n");
            }

            $value=htmlspecialchars($comp_nom, ENT_QUOTES);

            if(isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==$comp_id)
               $selected="selected='1'";
            else
               $selected="";

            print("<option value='$comp_id' label=\"$value\" $selected>$value</option>\n");

            $old_univ=$comp_univ_id;
         }

         db_free_result($result);

         print("      </optgroup>
                  </select>
                  </td>
               </tr>
               </table>

               <div class='centered_icons_box'>
                  <a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");

         // Ajout : uniquement pour les admin
         if($_SESSION['niveau']==$__LVL_ADMIN)
            print("<a href='$php_self?a=1' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/add_32x32_fond.png' alt='Ajouter' title='[Ajouter une composante]' border='0'></a>\n");

         if($rows)
         {
            print("<input type='image' class='icone' src='$__ICON_DIR/edit_32x32_fond.png' alt='Modifier' name='modifier' value='Modifier' title='[Modifier une composante]'>\n");

            // Suppression : uniquement pour les admins
            if($_SESSION['niveau']==$__LVL_ADMIN)
               print("<input type='image' class='icone' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Supprimer' name='supprimer' value='Supprimer' title='[Supprimer une composante]'>\n");
         }

         print("</form>
            </div>

            <script language='javascript'>
               document.form1.comp_id.focus()
            </script>\n");
      }
      elseif($_SESSION["suppression"]==1)
      {
         // TODO : ajouter des mécanismes de protection pour :
         // - ne pas supprimer la composante lorsqu'il s'agit de la composante courante
         // - ne pas supprimer la dernière composante de la base (?)

         print("<input type='hidden' name='comp_id' value='$comp_id'>");

         $result=db_query($dbr,"SELECT $_DBC_composantes_nom FROM $_DB_composantes
                                 WHERE $_DBC_composantes_id='$comp_id'");

         list($comp_nom)=db_fetch_row($result,0);

         db_free_result($result);

         // TODO : actuellement, l'avertissement suivant est vrai. Faut-il préférer l'orphelinat pour ces éléments ?
         message("<center>
                     La suppression entrainera automatiquement l'effacement de tous les utilisateurs et formations rattachés à cette composante.
                     <br>ATTENTION, CECI EST LA DERNIERE CONFIRMATION !
                  </center>", $__WARNING);

         message("Souhaitez vous vraiment supprimer la composante \"$comp_nom\" ?", $__QUESTION);

         print("<div class='centered_icons_box'>
                  <a href='$php_self?s=0' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>
                  <input type='image' class='icone' src='$__ICON_DIR/trashcan_full_34x34_slick_fond.png' alt='Supprimer' title='[Confirmer la suppression]' name='conf_supprimer' value='supprimer'>
                  </form>
               </div>\n");
      }
      elseif((isset($comp_id) && $_SESSION["modification"]==1) || $_SESSION["ajout_comp"]==1) // composante choisie, on récupère les infos actuelles
      {
         if($_SESSION["ajout_comp"]==1)
         {
            if(!isset($new_comp_nom)) // un seul test devrait être suffisant
            {
               $new_comp_verr_delai=$new_comp_nom=$new_comp_contact=$new_comp_adresse=$new_comp_directeur=$new_comp_scolarite="";
               $new_comp_courriel_scolarite=$new_limite_nombre=$new_limite_annee=$new_limite_annee_mention=$new_gestion_motifs="";
               $new_comp_www=$new_affichage_decisions=$new_comp_entretien_salle=$new_comp_entretien_lieu=$new_avertir_decision="";
            }
         }
         else
         {
            $result=db_query($dbr,"SELECT $_DBC_composantes_nom, $_DBC_composantes_adresse, $_DBC_composantes_contact,
                                          $_DBC_composantes_univ_id, $_DBC_universites_nom, $_DBC_composantes_scolarite,
                                          $_DBC_composantes_directeur,$_DBC_composantes_delai_lock,
                                          $_DBC_composantes_courriel_scol, $_DBC_composantes_limite_cand_nombre,
                                          $_DBC_composantes_limite_cand_annee, $_DBC_composantes_limite_cand_annee_mention,
                                          $_DBC_composantes_gestion_motifs, $_DBC_composantes_www,
                                          $_DBC_composantes_affichage_decisions, $_DBC_composantes_ent_salle,
                                          $_DBC_composantes_ent_lieu, $_DBC_composantes_avertir_decision
                                    FROM $_DB_composantes, $_DB_universites
                                 WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
                                 AND $_DBC_composantes_id='$comp_id'");

            list($current_comp_nom, $current_comp_adresse, $current_comp_contact, $current_comp_univ_id, $current_univ_nom,
                 $current_comp_scolarite, $current_comp_directeur,$current_comp_verr_delai, $current_comp_courriel_scolarite,
                 $current_limite_nombre, $current_limite_annee, $current_limite_annee_mention, $current_gestion_motifs,
                 $current_comp_www, $current_affichage_decisions, $current_comp_entretien_salle, $current_comp_entretien_lieu,
                 $current_avertir_decision)=db_fetch_row($result,0);

            db_free_result($result);

            $current_comp_verr_delai=floor($current_comp_verr_delai/3600);
         }

         print("<form name='form1' enctype='multipart/form-data' method='POST' action='$php_self'>
                  <input type='hidden' name='MAX_FILE_SIZE' value='200000'>\n");

         if(isset($comp_id))
            print("<input type='hidden' name='comp_id' value='$comp_id'>\n");
   ?>
   <table align='center'>
   <tr>
      <td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
         <font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;Informations</b></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Université :</b></font>
      </td>
      <td class='td-droite fond_menu'>
         <select name='comp_univ_id'>
            <?php
               $result=db_query($dbr,"SELECT $_DBC_universites_id, $_DBC_universites_nom
                                       FROM $_DB_universites ORDER BY $_DBC_universites_nom");
               $rows=db_num_rows($result);

               for($i=0; $i<$rows; $i++)
               {
                  list($universite_id, $universite_nom)=db_fetch_row($result, $i);

                  if(isset($current_comp_univ_id) && $current_comp_univ_id==$universite_id)
                     print("<option value='$universite_id' selected>$universite_nom</option>\n");
                  else
                     print("<option value='$universite_id'>$universite_nom</option>\n");
               }
            ?>
         </select>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Nom de la composante :</b></font>
      </td>
      <td class='td-droite fond_menu'>
         <input type='text' name='nom' value='<?php if(isset($new_comp_nom)) echo htmlspecialchars(stripslashes($new_comp_nom), ENT_QUOTES); elseif(isset($current_comp_nom)) echo htmlspecialchars(stripslashes($current_comp_nom), ENT_QUOTES); ?>' maxlength='92' size='60'>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Adresse</b></font>
      </td>
      <td class='td-droite fond_menu'>
         <textarea name='adresse' rows='4' cols='60'><?php
            if(isset($new_comp_adresse)) echo htmlspecialchars(stripslashes($new_comp_adresse), ENT_QUOTES);
            elseif(isset($current_comp_adresse)) echo htmlspecialchars(stripslashes($current_comp_adresse), ENT_QUOTES);
         ?></textarea>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Directeur (avec civilité)</b></font>
      </td>
      <td class='td-droite fond_menu'>
         <input type='text' name='directeur' value='<?php if(isset($new_comp_directeur)) echo htmlspecialchars(stripslashes($new_comp_directeur), ENT_QUOTES); elseif(isset($current_comp_directeur)) echo htmlspecialchars(stripslashes($current_comp_directeur), ENT_QUOTES);?>' maxlength='92' size='60'>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Contact</b></font>
      </td>
      <td class='td-droite fond_menu'>
         <textarea name='contact' rows='4' cols='60'><?php
            if(isset($new_comp_contact)) echo htmlspecialchars(stripslashes($new_comp_contact), ENT_QUOTES);
            elseif(isset($current_comp_contact)) echo htmlspecialchars(stripslashes($current_comp_contact), ENT_QUOTES);
         ?></textarea>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Adresse postale du Service Scolarité</b></font>
      </td>
      <td class='td-droite fond_menu'>
         <textarea name='scolarite' rows='7' cols='60'><?php
            if(isset($new_comp_scolarite)) echo htmlspecialchars(stripslashes($new_comp_scolarite), ENT_QUOTES);
            elseif(isset($current_comp_scolarite)) echo htmlspecialchars(stripslashes($current_comp_scolarite), ENT_QUOTES);
         ?></textarea>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Adresse électronique de la Scolarité :</b></font>
      </td>
      <td class='td-droite fond_menu'>
         <input type='text' name='courriel_scolarite' value='<?php if(isset($new_comp_courriel_scolarite)) echo htmlspecialchars(stripslashes($new_comp_courriel_scolarite), ENT_QUOTES); elseif(isset($current_comp_courriel_scolarite)) echo htmlspecialchars(stripslashes($current_comp_courriel_scolarite), ENT_QUOTES); ?>' maxlength='128' size='60'>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Adresse du site Internet de la composante :</b><br><i>(N'oubliez pas http:// ou https:// ...)</i></font>
      </td>
      <td class='td-droite fond_menu'>
         <input type='text' name='www' value='<?php if(isset($new_comp_www)) echo htmlspecialchars(stripslashes($new_comp_www), ENT_QUOTES); elseif(isset($current_comp_www)) echo htmlspecialchars(stripslashes($current_comp_www), ENT_QUOTES); ?>' maxlength='128' size='60'>
      </td>
   </tr>
   </table>

   <br><br>

   <table align='center'>
   <tr>
      <td class='fond_menu2' colspan='3' style='padding:4px 20px 4px 20px;'>
         <font class='Texte_menu_3'>
            <b>&#8226;&nbsp;&nbsp;Options sur les candidatures</b>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'>Délai avant verrouillage automatique des fiches candidat</font></td>
      <td class='td-droite fond_menu' colspan='2'>
         <input type='text' name='delai_verrouillage' value='<?php if(isset($new_comp_verr_delai)) echo floor($new_comp_verr_delai/3600); elseif(isset($current_comp_verr_delai)) echo $current_comp_verr_delai; ?>' maxlength='5' size='9'>&nbsp;<font class='Texte_menu'>&nbsp;&nbsp;&nbsp;(en heures)&nbsp;&nbsp;<i>Valeur par défaut : 48 heures</i></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'><font class='Texte_menu2'>Limite du nombre de candidatures</font></td>
      <td class='td-droite fond_menu' colspan='2'>
         <input type='text' name='limite_nombre' value='<?php if(isset($new_limite_nombre)) echo $new_limite_nombre; elseif(isset($current_limite_nombre)) echo $current_limite_nombre; ?>' maxlength='5' size='9'>&nbsp;<font class='Texte_menu'>
         &nbsp;&nbsp;&nbsp;<i>0 : pas de limite</i></font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'>
            <b>Limiter les candidatures à une seule année ?</b>
         </font>
      </td>
      <td class='td-droite fond_menu' colspan='2'>
         <font class='Texte_menu'>
            <?php
               if(isset($new_limite_annee))
                  $limite_annee=$new_limite_annee;
               elseif(isset($current_limite_annee))
                  $limite_annee=$current_limite_annee;
               else
                  $limite_annee=0;

               if($limite_annee=="" || $limite_annee==0)
               {
                  $yes_checked="";
                  $no_checked="checked";
               }
               else
               {
                  $yes_checked="checked";
                  $no_checked="";
               }

               print("<input type='radio' name='limite_annee' value='1' $yes_checked>&nbsp;Oui
                        &nbsp;&nbsp;<input type='radio' name='limite_annee' value='0' $no_checked>&nbsp;Non\n");
            ?>
            &nbsp;&nbsp;&nbsp;(exemple : limiter au L3 dès qu'un L3 a été choisi)
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'>
            <b>Limiter les candidatures à une année et une mention ?</b>
         </font>
      </td>
      <td class='td-droite fond_menu' colspan='2'>
         <font class='Texte_menu'>
            <?php
               if(isset($new_limite_annee_mention))
                  $limite_annee_mention=$new_limite_annee_mention;
               elseif(isset($current_limite_annee_mention))
                  $limite_annee_mention=$current_limite_annee_mention;
               else
                  $limite_annee_mention=0;

               if($limite_annee_mention=="" || $limite_annee_mention==0)
               {
                  $yes_checked="";
                  $no_checked="checked";
               }
               else
               {
                  $yes_checked="checked";
                  $no_checked="";
               }

               print("<input type='radio' name='limite_annee_mention' value='1' $yes_checked>&nbsp;Oui
                        &nbsp;&nbsp;<input type='radio' name='limite_annee_mention' value='0' $no_checked>&nbsp;Non\n");
            ?>
            &nbsp;&nbsp;&nbsp;(pour une mention, limiter à l'année du premier choix)
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'>
            <b>Publication des décisions pour les candidats</b>
         </font>
      </td>
      <td class='td-droite fond_menu' colspan='2'>
         <font class='Texte_menu'>
            <?php
               if(isset($new_defaut_affichage_decisions))
                  $affichage=$new_defaut_affichage_decisions;
               elseif(isset($current_affichage_decisions))
                  $affichage=$current_affichage_decisions;
               else
                  $affichage=1;
                  
               $checked0=$checked1=$checked2="";

               if($affichage=="" || $affichage==0)
                  $checked0="checked";
               elseif($affichage==1)
                  $checked1="checked";
               else
                  $checked2="checked";

               print("<input type='radio' name='affichage_decisions' value='0' $checked0>&nbsp;Masquer les décisions jusqu'à la publication manuelle
                      <br><input type='radio' name='affichage_decisions' value='1' $checked1>&nbsp;Afficher automatiquement les décisions dès la saisie
                      <br><input type='radio' name='affichage_decisions' value='2' $checked2>&nbsp;Afficher automatiquement les décisions + Lettres accessibles aux candidats\n");
            ?>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'>
            <b>Décision : avertir automatiquement le candidat ?</b>
         </font>
      </td>
      <td class='td-droite fond_menu' colspan='2'>
         <font class='Texte_menu'>
            <?php
               if(isset($new_avertir_decision))
                  $avertir_decision=$new_avertir_decision;
               elseif(isset($current_avertir_decision))
                  $avertir_decision=$current_avertir_decision;
               else
                  $avertir_decision=0;

               if($avertir_decision=="" || $avertir_decision==0)
               {
                  $yes_checked="";
                  $no_checked="checked";
               }
               else
               {
                  $yes_checked="checked";
                  $no_checked="";
               }

               print("<input type='radio' name='avertir_decision' value='1' $yes_checked>&nbsp;Oui
                        &nbsp;&nbsp;<input type='radio' name='avertir_decision' value='0' $no_checked>&nbsp;Non\n");
            ?>
            &nbsp(valable uniquement pour les décisions <strong>publiées</strong> de la Commission Pédagogique)
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'>
            <b>Gestion des motifs de refus</b>
         </font>
      </td>
      <td class='td-droite fond_menu' colspan='2'>
         <font class='Texte_menu'>
            <?php
               if(isset($new_gestion_motifs))
                  $gestion_motifs=$new_gestion_motifs;
               elseif(isset($current_gestion_motifs))
                  $gestion_motifs=$current_gestion_motifs;
               else
                  $gestion_motifs=0;

               if($gestion_motifs=="" || $gestion_motifs==0)
               {
                  $select_0="checked";
                  $select_1="";
               }
               else
               {
                  $select_1="checked";
                  $select_0="";
               }

               print("<input type='radio' name='gestion_motifs' value='0' $select_0>&nbsp;Plusieurs motifs possibles, énumération courte
                        <br><input type='radio' name='gestion_motifs' value='1' $select_1>&nbsp;Sélection d'un seul motif (pouvant en regrouper plusieurs dans une même phrase)\n");
            ?>
         </font>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Entretiens : salle par défaut :</b></font>
      </td>
      <td class='td-milieu fond_menu'>
         <input type='text' name='entretien_salle' value='<?php if(isset($new_comp_entretien_salle)) echo htmlspecialchars(stripslashes($new_comp_entretien_salle), ENT_QUOTES); elseif(isset($comp_entretien_salle)) echo htmlspecialchars(stripslashes($current_comp_entretien_salle), ENT_QUOTES); ?>' maxlength='50' size='52'>
      </td>
      <td class='td-droite fond_menu' rowspan='2'>
         <font class='Texte_menu'>
            <i>Valeurs utiles uniquement si des<br>entretiens sont prévus</i>
      </td>
   </tr>
   <tr>
      <td class='td-gauche fond_menu2'>
         <font class='Texte_menu2'><b>Entretiens : lieu (adresse) par défaut :</b></font>
      </td>
      <td class='td-milieu fond_menu'>
         <input type='text' name='entretien_lieu' value='<?php if(isset($new_comp_entretien_lieu)) echo htmlspecialchars(stripslashes($new_comp_entretien_lieu), ENT_QUOTES); elseif(isset($comp_entretien_lieu)) echo htmlspecialchars(stripslashes($current_comp_entretien_lieu), ENT_QUOTES); ?>' maxlength='128' size='60'>
      </td>
   </tr>
   </table>

   <div class='centered_icons_box'>
      <?php
         if(isset($success))
            print("<a href='index.php' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>");
         else
            print("<a href='$php_self?m=0&a=0' target='_self'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>");
      ?>
      <input type='image' class='icone' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' title='[Confirmer la création]' name='valider' value='Valider'>
      </form>
   </div>
   <?php
      }

      db_close($dbr);
   ?>
   
</div>
<?php
   pied_de_page();
?>
<script language="javascript">
   document.form1.comp_id.focus()
</script>

</body></html>
