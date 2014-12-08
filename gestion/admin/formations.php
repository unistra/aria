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
  // Ajout - Modification - Suppression des formations

  session_name("preinsc_gestion");
  session_start();

  include "../../configuration/aria_config.php";
  include "$__INCLUDE_DIR_ABS/vars.php";
  include "$__INCLUDE_DIR_ABS/fonctions.php";
  include "$__INCLUDE_DIR_ABS/db.php";


  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

  verif_auth("$__GESTION_DIR/login.php");

  if(!in_array($_SESSION['niveau'], array("$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
  {
    session_write_close();
    header("Location:$__MOD_DIR/gestion/noaccess.php");
    exit();
  }

  // Ajout, Modification ou suppression
  if(array_key_exists("a", $_GET) && ctype_digit($_GET["a"]))
    $_SESSION["ajout_formation"]=$_GET["a"]==1 ? 1 : 0;
  elseif(!isset($_SESSION["ajout_formation"]))
    $_SESSION["ajout_formation"]=0;

  if(array_key_exists("s", $_GET) && ctype_digit($_GET["s"]))
    $_SESSION["suppression"]=$_GET["s"]==1 ? 1 : 0;
  elseif(!isset($_SESSION["suppression"]))
    $_SESSION["suppression"]=0;

  if(array_key_exists("m", $_GET) && ctype_digit($_GET["m"]))
    $_SESSION["modification"]=$_GET["m"]==1 ? 1 : 0;
  elseif(!isset($_SESSION["modification"]))
    $_SESSION["modification"]=0;

  if(isset($_GET["succes"]))
    $succes=$_GET["succes"];

  $dbr=db_connect();

  // Argument chiffré pour un accès direct à la modification d'une formation
  if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
  {
    if(isset($params["propspec"]) && ctype_digit($params["propspec"])
      && db_num_rows(db_query($dbr,"SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_id='$params[propspec]'
                          AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'")))
    {
      $mod_propspec_id=$params["propspec"];

      // En cas d'annulation ou de validation, on revient à l'offre de formation (plus rapide)
      $adresse_retour="offre.php";
    }
  }

  if(((isset($_POST["modifier"]) || isset($_POST["modifier_x"])) && array_key_exists("propspec_id", $_POST) && ctype_digit($_POST["propspec_id"]))
    || isset($mod_propspec_id))
  {
    $propspec_id=isset($mod_propspec_id) ? $mod_propspec_id : $_POST["propspec_id"];
    $_SESSION["modification"]=1;
  }

  if((isset($_POST["supprimer"]) || isset($_POST["supprimer_x"])) && array_key_exists("propspec_id", $_POST) && ctype_digit($_POST["propspec_id"]))
  {
    $propspec_id=$_POST["propspec_id"];
    $_SESSION["suppression"]=1;
  }

  if(isset($_POST["conf_supprimer"]) || isset($_POST["conf_supprimer_x"]))
  {
    $propspec_id=$_POST["propspec_id"];

    if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec WHERE $_DBU_propspec_id='$propspec_id'"))==1)
    {
      db_query($dbr,"DELETE FROM $_DB_propspec WHERE $_DBU_propspec_id='$propspec_id'");

      write_evt($dbr, $__EVT_ID_G_ADMIN, "SUPPR Formation $propspec_id", "", $propspec_id);

      header("Location:$php_self?succes=1");
    }
    else
      header("Location:$php_self?erreur_suppr=1");

    db_close($dbr);

    exit();
  }
  elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]))
  {
    if(isset($_POST["propspec_id"]))
      $propspec_id=$_POST["propspec_id"];

    // IDENTIFIANT LIE A CELUI DE LA COMPOSANTE : OBSOLETE (remplacé par un identifiant de type 'bigint' comme les autres
    // Motif : lorsqu'il y a trop de formations, l'identifiant peut "déborder" sur ceux utilisés par une autre composante

    /*
    else // Génération d'un nouvel identifiant
    {
      $result=db_query($dbr, "SELECT max($_DBU_propspec_id)+1 FROM $_DB_propspec
                      WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'");

      list($propspec_id)=db_fetch_row($result, 0);

      db_free_result($result);

      if($propspec_id=="")
        $propspec_id=($_SESSION["comp_id"]*100)+1;
    }
    */
    $new_annee_id=$_POST["annee"];
    $new_spec_id=$_POST["specialite"];
    $new_finalite=$_POST["finalite"];

    // $new_apogee=$_POST["apogee"];
    $new_resp=$_POST["responsable"];
    $new_resp_email=$_POST["resp_email"];
    $new_frais=$_POST["frais"];
    $new_selective=$_POST['selective'];
    $new_entretiens=$_POST['entretiens'];

    $new_active=$_POST["active"];

    $new_manuelle=$_POST["manuelle"];

    $new_flag_pass=isset($_POST["flag_pass"]) && ($_POST["flag_pass"]=='t' || $_POST["flag_pass"]=='f') ? $_POST["flag_pass"] : "f";

    // on récupère la valeur courante pour voir si le mot de passe doit être mis à jour
    $current_flag_pass=isset($_POST["current_flag_pass"]) && ($_POST["current_flag_pass"]=='t' || $_POST["current_flag_pass"]=='f') ? $_POST["current_flag_pass"] : "f";

    $new_pass=trim($_POST["pass"]);
    $new_pass_conf=trim($_POST["pass_conf"]);
/*
    if($new_flag_pass=='t' && $current_flag_pass=="f")
    {
      if(strlen($new_pass)>=6)
      {
        if($new_pass==$new_pass_conf)
          $md5_pass=md5($new_pass);
        else
        {
          $erreur_pass_confirmation=1;
          $new_pass=$new_pass_conf="";
        }
      }
      else
        $erreur_longueur_pass="1";
    }
    else
      $md5_pass=$new_pass=$new_pass_conf="";
*/
    if($new_flag_pass=='t')
    {
      if(strlen($new_pass)>=6)
      {
        if($new_pass==$new_pass_conf)
          $md5_pass=md5($new_pass);
        else
        {
          $erreur_pass_confirmation=1;
          $new_pass=$new_pass_conf="";
        }
      } 
      else
        $erreur_longueur_pass="1";
    }
    else
      $md5_pass=$new_pass=$new_pass_conf="";

    if($new_resp=="")
      $new_resp="Le Responsable";

    // Champs vides
    if($new_annee_id=="" || $new_spec_id=="" || $new_selective=="" || $new_entretiens=="" || $new_finalite=="")
      $champs_vides=1;

    // Validité des champs
    if($new_frais=="")
      $new_frais=0;
    elseif(!is_numeric($new_frais))
      $format_frais=1;

    // récupération des valeurs courantes, en cas de modification

    if(!isset($champs_vides) && !isset($erreur_longueur_pass) && !isset($erreur_pass_confirmation))
    {
      if($_SESSION["ajout_formation"]==0 && isset($propspec_id))
      {
        $result=db_query($dbr,"SELECT $_DBC_propspec_annee, $_DBC_propspec_id_spec, $_DBC_propspec_finalite
                      FROM $_DB_propspec
                      WHERE $_DBC_propspec_id='$propspec_id'");
        $rows=db_num_rows($result);

        if(!$rows)
        {
          $_SESSION["modification"]=1;
          $propspec_id_existe_pas=1;
        }
        else
        {
          list($current_annee, $current_id_spec, $current_finalite)=db_fetch_row($result,0);
          db_free_result($result);

          if($current_annee!=$new_annee_id || $current_id_spec!=$new_spec_id || $new_finalite!=$current_finalite)
          {
            // Unicité de la formation : année + specialité + finalité
            if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec
                                  WHERE $_DBC_propspec_annee='$new_annee_id'
                                  AND $_DBC_propspec_id_spec='$new_spec_id'
                                  AND $_DBC_propspec_finalite='$new_finalite'
                                  AND $_DBC_propspec_id!='$propspec_id'")))
              $formation_existe=1;
          }
        }
      }
      // En cas d'ajout : vérification d'unicité
      elseif(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec
                              WHERE $_DBC_propspec_annee='$new_annee_id'
                              AND $_DBC_propspec_id_spec='$new_spec_id'
                              AND $_DBC_propspec_finalite='$new_finalite'")))
        $formation_existe=1;
    }

    if(!isset($champs_vides) && !isset($formation_existe) && !isset($propspec_id_existe_pas) && !isset($erreur_longueur_pass) && !isset($erreur_pass_confirmation))
    {
      if((!isset($_SESSION["ajout_formation"]) || $_SESSION["ajout_formation"]==0) && isset($propspec_id))
      {
        db_query($dbr,"UPDATE $_DB_propspec SET $_DBU_propspec_annee='$new_annee_id',
                                   $_DBU_propspec_id_spec='$new_spec_id',
                                   $_DBU_propspec_finalite='$new_finalite',
                                   $_DBU_propspec_selective='$new_selective',
                                   $_DBU_propspec_resp='$new_resp',
                                   $_DBU_propspec_mailresp='$new_resp_email',
                                   $_DBU_propspec_frais='$new_frais',
                                   $_DBU_propspec_entretiens='$new_entretiens',
                                   $_DBU_propspec_active='$new_active',
                                   $_DBU_propspec_manuelle='$new_manuelle',
                                   $_DBU_propspec_flag_pass='$new_flag_pass',
                                   $_DBU_propspec_pass='$md5_pass'
                  WHERE $_DBU_propspec_id='$propspec_id'");

        write_evt($dbr, $__EVT_ID_G_ADMIN, "MAJ Formation $propspec_id", "", $propspec_id);
      }
      else
      {
        $new_propspec_id=db_locked_query($dbr, $_DB_propspec, "INSERT INTO $_DB_propspec VALUES('$new_spec_id', '$new_selective', '$new_resp', '$new_resp_email', '$new_frais', '$new_annee_id', '$_SESSION[comp_id]', '$new_entretiens', '$new_finalite', '##NEW_ID##', '$new_active', '','$new_manuelle', '0','$new_flag_pass','$md5_pass')");

        write_evt($dbr, $__EVT_ID_G_ADMIN, "Nouvelle formation $new_propspec_id", "", $new_propspec_id);
      }

      db_close($dbr);

      // Retour dépendant de la page depuis laquelle on vient
      if(isset($_POST["retour"]))
        header("Location:$_POST[retour]?succes=1");
      else
        header("Location:$php_self?succes=1");

      exit;
    }
  }
  
  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    if($_SESSION["ajout_formation"]==1)
      titre_page_icone("Ajouter une formation", "add_32x32_fond.png", 30, "L");
    elseif(isset($_SESSION["action"]) && $_SESSION["action"]=="modification")
      titre_page_icone("Modifier une formation existante", "edit_32x32_fond.png", 30, "L");
    elseif(isset($_SESSION["action"]) && $_SESSION["action"]=="suppression")
      titre_page_icone("Supprimer une formation", "trashcan_full_34x34_slick_fond.png", 30, "L");
    else
      titre_page_icone("Gestion des formations", "", 30, "L");

    // Messages d'erreur et de succès

    if(isset($propspec_id_existe_pas) || isset($_GET["erreur_suppr"]))
      message("Erreur : l'identifiant de formation demandé est incorrect (problème de cohérence de la base ?)", $__ERREUR);

    if(isset($champs_vides))
      message("Erreur : les champs en <strong>gras</strong> sont <strong>obligatoires</strong>.", $__ERREUR);

    if(isset($formation_existe))
      message("Erreur : cette formation existe déjà !", $__ERREUR);

    if(isset($format_frais))
      message("Erreur : le champ \"Frais de dossier\" doit être une valeur numérique positive.", $__ERREUR);

    if(isset($formation_vide))
      message("Erreur : vous devez sélectionner une formation valide", $__ERREUR);

    if(isset($erreur_longueur_pass))
      message("Erreur : le mot de passe sélectionné est trop court (minimum 6 caractères)", $__ERREUR);

    if(isset($erreur_pass_confirmation))
      message("Erreur : les mots de passe sont différents", $__ERREUR);

    if(isset($succes) && $succes==1)
    {
      if($_SESSION["modification"]==1)
      {
        message("La formation a été modifiée avec succès.", $__SUCCES);
        unset($_SESSION["modification"]);
      }
      elseif($_SESSION["ajout_formation"]==1)
      {
        message("La formation a été créée avec succès.", $__SUCCES);
        unset($_SESSION["ajout_formation"]);
      }
      elseif($_SESSION["suppression"]==1)
      {
        message("La formation a été supprimée avec succès.", $__SUCCES);
        unset($_SESSION["suppression"]);
      }
    }

    print("<form action='$php_self' method='POST' name='form1'>\n");

    if((!isset($_SESSION["ajout_formation"]) || $_SESSION["ajout_formation"]==0) && (!isset($_SESSION["modification"]) || $_SESSION["modification"]==0) && (!isset($_SESSION["suppression"]) || $_SESSION["suppression"]==0))
    {
      // Choix de la formation à modifier
      $result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_propspec_id_spec,
                            $_DBC_specs_nom_court, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_mentions_nom,
                            $_DBC_propspec_manuelle, $_DBC_propspec_active
                        FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
                      WHERE $_DBC_propspec_annee=$_DBC_annees_id
                      AND $_DBC_propspec_id_spec=$_DBC_specs_id
                      AND $_DBC_specs_mention_id=$_DBC_mentions_id
                      AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                        ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_propspec_active DESC, $_DBC_specs_nom_court");

      $rows=db_num_rows($result);

      if($rows)
      {
        print("<table cellpadding='4' cellspacing='0' align='center'>
            <tr>
              <td class='fond_menu2' align='right'>
                <font class='Texte_menu2' style='font-weight:bold;'>Formation : </font>
              </td>
              <td class='fond_menu'>
                <select name='propspec_id' size='1'>
                  <option value=''></option>\n");

        $old_annee="-1";
        $old_mention="-1";

        for($i=0; $i<$rows; $i++)
        {
          list($form_propspec_id, $form_annee_id, $form_annee_nom, $form_spec_id, $form_spec_nom, $form_mention_id,
              $form_finalite, $form_mention_nom, $form_manuelle, $form_active)=db_fetch_row($result, $i);

          if($form_annee_id!=$old_annee)
          {
            if($i!=0)
              print("</optgroup>
                    <option value='' label='' disabled></option>\n");

            if($form_annee_nom=="")
              $annee_nom="Années particulières";
            else
              $annee_nom=$form_annee_nom;

            print("<optgroup label='$annee_nom'>\n");

            $new_sep_annee=1;

            $old_annee=$form_annee_id;
            $old_mention="-1";
          }
          else
            $new_sep_annee=0;

          if($form_mention_id!=$old_mention)
          {
            if(!$new_sep_annee)
              print("</optgroup>
                    <option value='' label='' disabled></option>\n");

            $val=htmlspecialchars($form_mention_nom, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);

            print("<optgroup label='- $val'>\n");

            $old_mention=$form_mention_id;
          }

          $manuelle_txt=$form_manuelle ? "(M) " : "";
          $active_txt=$form_active ? "" : "(D) ";

          if($form_annee_nom=="")
            print("<option value='$form_propspec_id' label=\"$active_txt$manuelle_txt$form_spec_nom $tab_finalite[$form_finalite]\">$active_txt$manuelle_txt$form_spec_nom  $tab_finalite[$form_finalite]</option>\n");
          else
            print("<option value='$form_propspec_id' label=\"$active_txt$manuelle_txt$form_annee_nom - $form_spec_nom  $tab_finalite[$form_finalite]\">$active_txt$manuelle_txt$form_annee_nom - $form_spec_nom  $tab_finalite[$form_finalite]</option>\n");
        }

        print("   </optgroup>
              </select>
              </td>
            </tr>
            </table>\n");
      }
      else
      {
        $no_elements=1;
            $message="Il n'y a actuellement aucune formation enregistrée dans cette composante.";

            // Vérification du nombre de spécialités disponibles pour la création

            if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_specs WHERE $_DBC_specs_comp_id='$_SESSION[comp_id]'")))
            {
               $no_specs=1;
               $message.="<br>De plus, vous devez dans un premier temps créer une ou plusieurs spécialités avant de pouvoir créer une formation.";
            }

            message($message, $__INFO);
      }

      print("<div class='centered_icons_box'>
            <a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");

         if(!isset($no_specs))
        print("<a href='$php_self?a=1' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/add_32x32_fond.png' alt='Ajouter' title='[Ajouter une formation]' border='0'></a>\n");

      if(!isset($no_elements))
        print("<input type='image' class='icone' src='$__ICON_DIR/edit_32x32_fond.png' alt='Modifier' name='modifier' value='Modifier' title='[Modifier un utilisateur]'>
             <input type='image' class='icone' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Supprimer' name='supprimer' value='Supprimer' title='[Supprimer une formation]'>\n");

      print("</form>
          </div>
          <script language='javascript'>
            document.form1.propspec_id.focus()
          </script>\n");
    }
    elseif(isset($_SESSION["suppression"]) && $_SESSION["suppression"]==1)
    {
      print("<form action='$php_self' method='POST' name='form1'>
            <input type='hidden' name='propspec_id' value='$propspec_id'>");

      $result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_propspec_id_spec,
                           $_DBC_specs_nom_court, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_mentions_nom,
                           $_DBC_propspec_manuelle, $_DBC_propspec_active
                        FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
                      WHERE $_DBC_propspec_annee=$_DBC_annees_id
                      AND $_DBC_propspec_id_spec=$_DBC_specs_id
                      AND $_DBC_specs_mention_id=$_DBC_mentions_id
                      AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                      AND $_DBC_propspec_id='$propspec_id'");

      list($form_propspec_id, $form_annee_id, $form_annee_nom, $form_spec_id, $form_spec_nom, $form_mention_id,
          $form_finalite, $form_mention_nom, $form_manuelle, $form_active)=db_fetch_row($result, 0);

      db_free_result($result);

      $nom_formation=$form_annee_nom=="" ? "$form_spec_nom " . $tab_finalite[$form_finalite] : "$form_annee_nom $form_spec_nom " . $tab_finalite[$form_finalite];

      message("<center>
              <strong>Attention : </strong> La suppression entrainera également celle de <strong>toutes les candidatures correspondantes</strong>.
              <br>Souhaitez vous vraiment supprimer la formation \"$nom_formation\" ?
            </center>", $__QUESTION);

      print("<div class='centered_icons_box'>
            <a href='$php_self?s=0' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' title='[Annuler la suppression]' border='0'></a>
            <input type='image' class='icone' src='$__ICON_DIR/trashcan_full_34x34_slick_fond.png' alt='Supprimer' title='[Confirmer la suppression]' name='conf_supprimer' value='Supprimer'>
            </form>
           </div>\n");
    }
    elseif((isset($propspec_id) && isset($_SESSION["modification"]) && $_SESSION["modification"]==1) || (isset($_SESSION["ajout_formation"]) && $_SESSION["ajout_formation"]==1))
    {
       // ajout ou modification (on récupère les infos actuelles)

      if($_SESSION["ajout_formation"]==1)
      {
        if(!isset($new_annee_id)) // un seul test devrait suffire ...
        {
          $new_annee_id=$new_spec_id=$new_resp=$new_resp_email="";
          $new_finalite=$__FIN_CLASSIQUE;
          $new_active=1;
          $new_frais=$new_manuelle=$new_selective=$new_entretiens=0;
          $new_flag_pass=$current_flag_pass="f";
        }
      }
      else
      {
        $result=db_query($dbr, "SELECT $_DBC_propspec_annee, $_DBC_propspec_id_spec, $_DBC_propspec_finalite,
                             $_DBC_propspec_manuelle, $_DBC_propspec_active,
                             $_DBC_propspec_resp, $_DBC_propspec_mailresp, $_DBC_propspec_frais,
                             $_DBC_propspec_selective, $_DBC_propspec_entretiens, $_DBC_propspec_flag_pass,
                             $_DBC_propspec_pass
                        FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
                      WHERE $_DBC_propspec_annee=$_DBC_annees_id
                      AND $_DBC_propspec_id_spec=$_DBC_specs_id
                      AND $_DBC_specs_mention_id=$_DBC_mentions_id
                      AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                      AND $_DBC_propspec_id='$propspec_id'");

        list($current_annee_id, $current_spec_id, $current_finalite, $current_manuelle, $current_active, $current_resp,
            $current_resp_email, $current_frais, $current_selective, $current_entretiens, $current_flag_pass,
            $current_pass)=db_fetch_row($result,0);

        db_free_result($result);
      }

      $form_retour=isset($adresse_retour) ? "<input type='hidden' name='retour' value='$adresse_retour'>" : "";

      print("<form action='$php_self' method='POST' name='form1'>
           $form_retour\n");

      if(isset($propspec_id))
        print("<input type='hidden' name='propspec_id' value='$propspec_id'>\n");
  ?>

  <table style='margin-left:auto; margin-right:auto'>
  <tr>
    <td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
      <font class='Texte_menu2'>
        <?php
          if($_SESSION["ajout_formation"]==1)
            print("<strong>Nouvelle formation</strong>\n");
          else
            print("<strong>Saisie des nouvelles données</strong>\n");
        ?>
      </font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><strong>Composante : </strong></font>
    </td>
    <td class='td-droite fond_menu'>
      <font class='Texte_menu'><strong><?php echo $_SESSION["composante"]; ?><strong></font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><strong>Année : </strong></font>
    </td>
    <td class='td-droite fond_menu'>
      <select size="1" name="annee">
        <option value=""></option>
        <?php
          if(isset($new_annee_id))
            $form_annee_id=$new_annee_id;
          elseif(isset($current_annee_id))
            $form_annee_id=$current_annee_id;
          else
            $form_annee_id="";

          $result=db_query($dbr, "SELECT $_DBC_annees_id, $_DBC_annees_annee FROM $_DB_annees
                          ORDER BY $_DBC_annees_ordre");

          $rows=db_num_rows($result);

          for($i=0; $i<$rows; $i++)
          {
            list($annee_id, $annee_nom)=db_fetch_row($result, $i);

            $annee_nom=$annee_nom=="" ? "Années particulières" : $annee_nom;

            $selected=($form_annee_id==$annee_id) ? "selected='1'" : "";
            
            print("<option value='$annee_id' $selected>$annee_nom</option>\n");
          }

          db_free_result($result);

          print("</select>\n");
      ?>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><strong>Mention / Spécialité : </strong></font>
    </td>
    <td class='td-droite fond_menu'>
      <select size="1" name="specialite">
        <option value=""></option>
        <?php
          if(isset($new_spec_id))
            $form_spec_id=$new_spec_id;
          elseif(isset($current_spec_id))
            $form_spec_id=$current_spec_id;
          else
            $form_spec_id="";

          $result=db_query($dbr, "SELECT $_DBC_specs_id, $_DBC_specs_nom, $_DBC_specs_mention_id, $_DBC_mentions_nom
                            FROM $_DB_specs, $_DB_mentions
                          WHERE $_DBC_specs_comp_id='$_SESSION[comp_id]'
                          AND $_DBC_specs_mention_id=$_DBC_mentions_id
                            ORDER BY $_DBC_mentions_nom, $_DBC_specs_nom");

          $rows=db_num_rows($result);

          $old_mention="-1";

          for($i=0; $i<$rows; $i++)
          {
            list($spec_id, $spec_nom, $mention, $mention_nom)=db_fetch_row($result, $i);

            if($mention!=$old_mention)
            {
              if($i!=0)
                print("</optgroup>
                     <option value='' label='' disabled></option>\n");

              $val=htmlspecialchars($mention_nom, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);

              print("<optgroup label='$val'>\n");

              $old_mention=$mention;
            }

            $selected=($form_spec_id==$spec_id) ? "selected='1'" : "";

            print("<option value='$spec_id' label=\"$spec_nom\" $selected>$spec_nom</option>\n");
          }

          db_free_result($result);

          print("</select>\n");
      ?>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><strong>Finalité :</strong></font>
    </td>
    <td class='td-droite fond_menu'>
      <select name='finalite'>
        <?php
          if(isset($new_finalite))
            $finalite=$new_finalite;
          elseif(isset($current_finalite))
            $finalite=$current_finalite;
          else
            $finalite=$__FIN_CLASSIQUE;

          switch($finalite)
          {
            case $__FIN_CLASSIQUE : $sel_classique="selected=1"; $sel_pro=""; $sel_rech="";
                            break;

            case $__FIN_RECH :  $sel_classique=""; $sel_pro=""; $sel_rech="selected=1";
                          break;

            case $__FIN_PRO : $sel_classique=""; $sel_pro="selected=1"; $sel_rech="";
                        break;
          }

          print("<option value='$__FIN_CLASSIQUE' $sel_classique>Aucune</option>
               <option value='$__FIN_RECH' $sel_rech>Recherche</option>
               <option value='$__FIN_PRO' $sel_pro>Professionnelle</option>\n");
        ?>
      </select>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><strong>Formation activée ?</strong></font>
    </td>
    <td class='td-droite fond_menu'>
      <font class='Texte_menu'>
        <?php
          if(isset($new_active))
            $active=$new_active;
          elseif(isset($current_active))
            $active=$current_active;
          else
            $active=1;

          if($active=="" || $active==0)
          {
            $yes_checked="";
            $no_checked="checked";
          }
          else
          {
            $yes_checked="checked";
            $no_checked="";
          }

          print("<input type='radio' name='active' value='1' $yes_checked>&nbsp;Oui
                &nbsp;&nbsp;<input type='radio' name='active' value='0' $no_checked>&nbsp;Non\n");
        ?>
      </font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><strong>Formation gérée manuellement ?</strong><br>(uniquement par la scolarité)</font>
    </td>
    <td class='td-droite fond_menu'>
      <font class='Texte_menu'>
        <?php
          if(isset($new_manuelle))
            $manuelle=$new_manuelle;
          elseif(isset($current_manuelle))
            $manuelle=$current_manuelle;
          else
            $manuelle=1;

          if($manuelle=="" || $manuelle==0)
          {
            $yes_checked="";
            $no_checked="checked";
          }
          else
          {
            $yes_checked="checked";
            $no_checked="";
          }

          print("<input type='radio' name='manuelle' value='1' $yes_checked>&nbsp;Oui
                &nbsp;&nbsp;<input type='radio' name='manuelle' value='0' $no_checked>&nbsp;Non\n");
        ?>
      </font>
    </td>
  </tr>
<!--
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'>Code APOGEE :</font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='apogee' value='<?php /* if(isset($current_apogee)) echo htmlspecialchars(stripslashes($current_apogee), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); elseif(isset($new_apogee)) echo htmlspecialchars(stripslashes($new_apogee), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); */ ?>' maxlength='92' size='80'>
    </td>
  </tr>
-->
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><strong>Frais de dossiers :</strong></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='frais' value='<?php if(isset($current_frais)) echo htmlspecialchars(stripslashes($current_frais), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); elseif(isset($new_frais)) echo htmlspecialchars(stripslashes($new_frais), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);?>' maxlength='92' size='80'>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><strong>Responsable de la formation :</strong></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='responsable' value='<?php if(isset($current_resp)) echo htmlspecialchars(stripslashes($current_resp), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); elseif(isset($new_resp)) echo htmlspecialchars(stripslashes($new_resp), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);?>' maxlength='92' size='80'>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'>Courriel du responsable :</font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='resp_email' value='<?php if(isset($current_resp_email)) echo htmlspecialchars(stripslashes($current_resp_email), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); elseif(isset($new_resp_email)) echo htmlspecialchars(stripslashes($new_resp_email), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);?>' maxlength='92' size='80'>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><strong>Formation sélective ?</strong></font>
    </td>
    <td class='td-droite fond_menu'>
      <font class='Texte_menu'>
        <?php
          if(isset($new_selective))
            $selective=$new_selective;
          elseif(isset($current_selective))
            $selective=$current_selective;
          else
            $selective=1;

          if($selective=="" || $selective==0)
          {
            $yes_checked="";
            $no_checked="checked";
          }
          else
          {
            $yes_checked="checked";
            $no_checked="";
          }

          print("<input type='radio' name='selective' value='1' $yes_checked>&nbsp;Oui
                &nbsp;&nbsp;<input type='radio' name='selective' value='0' $no_checked>&nbsp;Non\n");
        ?>
      </font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><strong>Les candidats sont-ils convoqués à un entretien ?</strong></font>
    </td>
    <td class='td-droite fond_menu'>
      <font class='Texte_menu'>
        <?php
          if(isset($new_entretiens))
            $entretiens=$new_entretiens;
          elseif(isset($current_entretiens))
            $entretiens=$current_entretiens;
          else
            $entretiens=1;

          if($entretiens=="" || $entretiens==0)
          {
            $yes_checked="";
            $no_checked="checked";
          }
          else
          {
            $yes_checked="checked";
            $no_checked="";
          }

          print("<input type='radio' name='entretiens' value='1' $yes_checked>&nbsp;Oui
               &nbsp;&nbsp;<input type='radio' name='entretiens' value='0' $no_checked>&nbsp;Non\n");
        ?>
      </font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'>Protéger la formation par un mot de passe ?</font>
    </td>
    <td class='td-droite fond_menu'>
      <font class='Texte_menu'>
        <?php
          if(isset($new_flag_pass))
            $flag_pass=$new_flag_pass;
          elseif(isset($current_flag_pass))
            $flag_pass=$current_flag_pass;
          else
            $flag_pass='f';

          if($flag_pass=="" || $flag_pass=='f')
          {
            $yes_checked="";
            $no_checked="checked";
          }
          else
          {
            $yes_checked="checked";
            $no_checked="";
          }

          print("<input type='hidden' name='current_flag_pass' value='$current_flag_pass'>
               <input type='radio' name='flag_pass' value='t' $yes_checked>&nbsp;Oui
               &nbsp;&nbsp;<input type='radio' name='flag_pass' value='f' $no_checked>&nbsp;Non\n");
        ?>
      <i>&nbsp;&nbsp;Le mot de passe ne devra être transmis qu'aux candidats autorisés à sélectionner cette formation.
      </font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'>Nouveau mot de passe :</font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='password' name='pass' value='<?php if((!isset($current_flag_pass) || $current_flag_pass=='f') && isset($new_pass)) echo htmlspecialchars(stripslashes($new_pass), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);?>' maxlength='24' size='32'>
      <font class='Texte_menu'><i>(6 caractères minimum)</i></font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'>Confirmation du nouveau mot de passe :</font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='password' name='pass_conf' value='<?php if((!isset($current_flag_pass) || $current_flag_pass=='f') && isset($new_pass_conf)) echo htmlspecialchars(stripslashes($new_pass_conf), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);?>' maxlength='24' size='32'>
    </td>
  </tr>
  </table>

  <script language='javascript'>
    document.form1.annee.focus()
  </script>

  <div class='centered_icons_box'>
    <?php
      // Retour en fonction de la page depuis laquelle on vient
      $retour=isset($adresse_retour) ? $adresse_retour : "$php_self?m=0&a=0";
    ?>
    <a href='<?php echo "$retour"; ?>' target='_self' class='lien_bleu_12'><img class='icone' src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
    <input type="image" class='icone' src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
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
</body></html>

