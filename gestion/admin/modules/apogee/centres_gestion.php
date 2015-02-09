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
  // Ajout - Modification - Suppression des Centres de gestion (=codes composantes Apogée)

  session_name("preinsc_gestion");
  session_start();

  include "../../../../configuration/aria_config.php";
  include "$__INCLUDE_DIR_ABS/vars.php";
  include "$__INCLUDE_DIR_ABS/fonctions.php";
  include "$__INCLUDE_DIR_ABS/db.php";

  // includes spécifiques au module
  include "include/db.php"; // db.php appellera également update_db.php pour la mise à jour du schéma

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
    $_SESSION["ajout_centre"]=$_GET["a"]==1 ? 1 : 0;
  elseif(!isset($_SESSION["ajout_centre"]))
    $_SESSION["ajout_centre"]=0;

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

  if((isset($_POST["modifier"]) || isset($_POST["modifier_x"])) && array_key_exists("centre_id", $_POST) && ctype_digit($_POST["centre_id"]))
  {
    $centre_id=$_POST["centre_id"];
    $_SESSION["modification"]=1;
  }

  if((isset($_POST["supprimer"]) || isset($_POST["supprimer_x"])) && array_key_exists("centre_id", $_POST) && ctype_digit($_POST["centre_id"]))
  {
    $centre_id=$_POST["centre_id"];
    $_SESSION["suppression"]=1;
  }

  if(isset($_POST["conf_supprimer"]) || isset($_POST["conf_supprimer_x"]))
  {
    $centre_id=$_POST["centre_id"];

    if(db_num_rows(db_query($dbr, "SELECT * FROM $_module_apogee_DB_centres_gestion
                         WHERE $_module_apogee_DBU_centres_gestion_id='$centre_id'"))==1)
    {
      db_query($dbr,"DELETE FROM $_module_apogee_DB_centres_gestion WHERE $_module_apogee_DBU_centres_gestion_id='$centre_id'");

      header("Location:$php_self?succes=1");
    }
    else
      header("Location:$php_self?erreur_suppr=1");

    db_close($dbr);

    exit();
  }
  elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]))
  {
    if(isset($_POST["centre_id"]))
      $centre_id=$_POST["centre_id"];

    $new_nom=ucfirst(trim($_POST["nom"]));
    $new_code=mb_strtoupper(trim($_POST["code"]), "UTF-8");

    if($new_nom=="" || $new_code=="")
      $champs_vides=1;

    // récupération des valeurs courantes, en cas de modification
    if($_SESSION["ajout_centre"]==0 && isset($centre_id))
    {
      $result=db_query($dbr,"SELECT $_module_apogee_DBC_centres_gestion_nom, $_module_apogee_DBC_centres_gestion_code
                        FROM $_module_apogee_DB_centres_gestion
                      WHERE $_module_apogee_DBC_centres_gestion_id='$centre_id'
                      AND $_module_apogee_DBC_centres_gestion_comp_id='$_SESSION[comp_id]'");
      $rows=db_num_rows($result);

      if(!$rows)
      {
        $_SESSION["modification"]=1;
        $centre_id_existe_pas=1;
      }
      else
      {
        list($current_nom, $current_code)=db_fetch_row($result,0);
        db_free_result($result);

        // sinon, on regarde dans la composante actuelle
        if($current_nom!=$new_nom || $current_code!=$new_code)
        {
          if(db_num_rows(db_query($dbr,"SELECT $_module_apogee_DBC_centres_gestion_id
                                FROM $_module_apogee_DB_centres_gestion
                              WHERE ($_module_apogee_DBC_centres_gestion_nom ILIKE '$new_nom'
                                   OR $_module_apogee_DBC_centres_gestion_code ILIKE '$new_code')
                              AND $_module_apogee_DBC_centres_gestion_comp_id='$_SESSION[comp_id]'
                              AND $_module_apogee_DBC_centres_gestion_id!='$centre_id'")))
            $nom_existe=1;
        }
      }
    }
    // En cas d'ajout : vérification d'unicité dans cette composante
    // Pas de contraintes intercomposantes : deux composantes peuvent avoir le même centre de gestion
    elseif(db_num_rows(db_query($dbr,"SELECT $_module_apogee_DBC_centres_gestion_id
                            FROM $_module_apogee_DB_centres_gestion
                          WHERE ($_module_apogee_DBC_centres_gestion_nom ILIKE '$new_nom'
                               OR $_module_apogee_DBC_centres_gestion_code ILIKE '$new_code')
                          AND $_module_apogee_DBC_centres_gestion_comp_id='$_SESSION[comp_id]'")))
        $nom_existe=1;

    if(!isset($champs_vides) && !isset($nom_existe))
    {
      if($_SESSION["ajout_centre"]==0 && isset($centre_id))
        db_query($dbr,"UPDATE $_module_apogee_DB_centres_gestion SET $_module_apogee_DBU_centres_gestion_nom='$new_nom',
                                                 $_module_apogee_DBU_centres_gestion_code='$new_code'
                  WHERE $_module_apogee_DBU_centres_gestion_id='$centre_id'");
      else
        $new_centre_id=db_locked_query($dbr, $_module_apogee_DB_centres_gestion, "INSERT INTO $_module_apogee_DB_centres_gestion VALUES('##NEW_ID##', '$_SESSION[comp_id]', '$new_code', '$new_nom')");

      db_close($dbr);
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
    if($_SESSION["ajout_centre"]==1)
      titre_page_icone("Module Apogée : Ajouter un Centre de Gestion", "add_32x32_fond.png", 30, "L");
    elseif(isset($_SESSION["action"]) && $_SESSION["action"]=="modification")
      titre_page_icone("Module Apogée : Modifier un Centre de Gestion", "edit_32x32_fond.png", 30, "L");
    elseif(isset($_SESSION["action"]) && $_SESSION["action"]=="suppression")
      titre_page_icone("Module Apogée : Supprimer un Centre de Gestion", "trashcan_full_34x34_slick_fond.png", 30, "L");
    else
      titre_page_icone("Module Apogée : Centres de Gestion", "", 30, "L");

    // Messages d'erreur et de succès

    if(isset($centre_id_existe_pas) || isset($_GET["erreur_suppr"]))
      message("Erreur : l'identifiant demandé est incorrect (problème de cohérence de la base ?)", $__ERREUR);

    if(isset($champs_vides))
      message("Erreur : les champs en <strong>gras</strong> sont <strong>obligatoires</strong>.", $__ERREUR);

    if(isset($nom_existe))
      message("Erreur : un Centre de Gestion portant ce nom (ou ce code) existe déjà.", $__ERREUR);

    if(isset($succes) && $succes==1)
    {
      if($_SESSION["modification"]==1)
      {
        message("Le Centre de Gestion a été modifié avec succès.", $__SUCCES);
        $_SESSION["modification"]=0;
      }
      elseif($_SESSION["ajout_centre"]==1)
      {
        message("Le Centre de Gestion a été créé avec succès.", $__SUCCES);
        $_SESSION["ajout_centre"]=0;
      }
      elseif($_SESSION["suppression"]==1)
      {
        message("Le Centre de Gestion a été supprimé avec succès.", $__SUCCES);
        $_SESSION["suppression"]=0;
      }
    }

    print("<form action='$php_self' method='POST' name='form1'>\n");

    if($_SESSION["ajout_centre"]==0 && $_SESSION["modification"]==0 && $_SESSION["suppression"]==0)  // Choix de la mention à modifier
    {
      $result=db_query($dbr,"SELECT $_module_apogee_DBC_centres_gestion_id, $_module_apogee_DBC_centres_gestion_nom,
                          $_module_apogee_DBC_centres_gestion_code
                        FROM $_module_apogee_DB_centres_gestion
                      WHERE $_module_apogee_DBC_centres_gestion_comp_id='$_SESSION[comp_id]'
                        ORDER BY $_module_apogee_DBC_centres_gestion_nom");

      $rows=db_num_rows($result);

      if($rows)
      {
        print("<table cellpadding='4' cellspacing='0' border='0' align='center'>
            <tr>
              <td class='fond_menu2'>
                <font class='Texte_menu2' style='font-weight:bold;'>Centre(s) de Gestion pour cette composante : </font>
              </td>
              <td class='fond_menu'>
                <select name='centre_id' size='1'>
                  <option value=''></option>\n");

        $old_comp="";

        for($i=0; $i<$rows; $i++)
        {
          list($centre_id, $nom, $code)=db_fetch_row($result,$i);

          $val=htmlspecialchars($nom, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);

          print("<option value='$centre_id'>$val ($code)</option>\n");
        }

        print("</select>
            </td>
          </tr>
          </table>\n");
      }
      else
      {
        $no_elements=1;
        message("Aucun Centre de Gestion n'a encore été créé pour cette composante.", $__INFO);
      }

      print("<div class='centered_icons_box'>
            <a href='$__GESTION_DIR/admin/index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
            <a href='$php_self?a=1' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/add_32x32_fond.png' alt='Ajouter' title='[Ajouter un Centre de Gestion]' border='0'></a>\n");

      if(!isset($no_elements))
        print("<input type='image' class='icone' src='$__ICON_DIR/edit_32x32_fond.png' alt='Modifier' name='modifier' value='Modifier' title='[Modifier un Centre de Gestion]'>
             <input type='image' class='icone' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Supprimer' name='supprimer' value='Supprimer' title='[Supprimer un Centre de Gestion]'>\n");

      print("</form>
          </div>
          <script language='javascript'>
            document.form1.centre_id.focus()
          </script>\n");
    }
    elseif($_SESSION["suppression"]==1)
    {
      print("<form action='$php_self' method='POST' name='form1'>
            <input type='hidden' name='centre_id' value='$centre_id'>");

      $result=db_query($dbr,"SELECT $_module_apogee_DBC_centres_gestion_nom,$_module_apogee_DBC_centres_gestion_code
                      FROM $_module_apogee_DB_centres_gestion
                      WHERE $_module_apogee_DBC_centres_gestion_id='$centre_id'");

      list($nom, $code)=db_fetch_row($result,0);

      db_free_result($result);

      message("<center>
              <strong>Souhaitez vous vraiment supprimer le Centre de Gestion <strong>\"$nom ($code)\"</strong> ?
            </center>", $__QUESTION);

      print("<div class='centered_icons_box'>
            <a href='$php_self?s=0' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' title='[Annuler la suppression]' border='0'></a>
            <input type='image' class='icone' src='$__ICON_DIR/trashcan_full_34x34_slick_fond.png' alt='Supprimer' title='[Confirmer la suppression]' name='conf_supprimer' value='Supprimer'>
            </form>
           </div>\n");
    }
    elseif((isset($centre_id) && $_SESSION["modification"]==1) || $_SESSION["ajout_centre"]==1) // ajout ou modification (on récupère les infos actuelles)
    {
      if($_SESSION["ajout_centre"]==1)
      {
        if(!isset($new_nom)) // un seul test devrait suffire ...
          $new_nom=$new_code="";
      }
      else
      {
        $result=db_query($dbr,"SELECT $_module_apogee_DBC_centres_gestion_nom, $_module_apogee_DBC_centres_gestion_code
                          FROM $_module_apogee_DB_centres_gestion
                        WHERE $_module_apogee_DBC_centres_gestion_id='$centre_id'");

        list($new_nom,$new_code)=db_fetch_row($result,0);

        db_free_result($result);
      }

      print("<form action='$php_self' method='POST' name='form1'>\n");

      if(isset($centre_id))
      {
        print("<input type='hidden' name='centre_id' value='$centre_id'>\n");
      }
  ?>

  <table align='center'>
  <tr>
    <td colspan='2' class='td-gauche fond_menu2' style='padding:4px 20px 4px 20px;'>
      <font class='Texte_menu2'>
        <b>&#8226;&nbsp;&nbsp;Informations</b>
      </font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Nom du Centre de Gestion : </b></font></td>
    <td class='td-droite fond_menu'><input type='text' name='nom' value='<?php if(isset($new_nom)) echo htmlspecialchars($new_nom, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size='40'></td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Code Apogée du Centre : </b></font></td>
    <td class='td-droite fond_menu'><input type='text' name='code' value='<?php if(isset($new_code)) echo htmlspecialchars($new_code, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size='40'></td>
  </tr>
  </table>

  <script language='javascript'>
    document.form1.nom.focus()
  </script>

  <div class='centered_icons_box'>
    <a href='<?php echo "$php_self?m=0&a=0"; ?>' target='_self' class='lien_bleu_12'><img class='icone' src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
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

