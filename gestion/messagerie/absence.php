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
  // include "include/editeur_fonctions.php";

  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

  verif_auth("$__GESTION_DIR/login.php");

  $dbr=db_connect();
  
  if(isset($_GET["succes"]))
    $succes=$_GET["succes"];

  if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
  {
    $dbr=db_connect();

    if(array_key_exists("activation", $_POST))
      $activation=$_POST["activation"];
    else
      $activation=='f';

    $texte=$_POST['texte'];

    if($activation=="t")
    {
      $absence_debut_jour=trim($_POST["absence_debut_jour"]);
      $absence_debut_mois=trim($_POST["absence_debut_mois"]);
      $absence_debut_annee=(trim($_POST["absence_debut_annee"])=="" || trim($_POST["absence_debut_annee"])==date("y")) ? date("Y") : trim($_POST["absence_debut_annee"]);

      $absence_fin_jour=trim($_POST["absence_fin_jour"]);
      $absence_fin_mois=trim($_POST["absence_fin_mois"]);
      $absence_fin_annee=(trim($_POST["absence_fin_annee"])=="" || trim($_POST["absence_fin_annee"])==date("y")) ? date("Y") : trim($_POST["absence_fin_annee"]);

      // vérification des champs

      if($absence_debut_jour!="" && ctype_digit($absence_debut_jour) && $absence_debut_mois!="" && ctype_digit($absence_debut_mois)
        && $absence_fin_jour!="" && ctype_digit($absence_fin_jour) && $absence_fin_mois!="" && ctype_digit($absence_fin_mois))
      {
        $absence_debut_date=MakeTime(0,0,0,$absence_debut_mois, $absence_debut_jour, $absence_debut_annee);
        $absence_fin_date=MakeTime(23,59,59,$absence_fin_mois, $absence_fin_jour, $absence_fin_annee);

        if($absence_debut_date>$absence_fin_date)
          switch_vals($absence_debut_date, $absence_fin_date);
      }
      else
        $erreur_format_date=1;

      if($texte=="")
        $texte_vide=1;
    }
    else
    {
      $absence_debut_jour=$absence_debut_mois=$absence_debut_annee=$absence_fin_jour=$absence_fin_mois=$absence_fin_annee="";
      $absence_debut_date=$absence_fin_date=0;
    }

    if(!isset($erreur_format_date) && !isset($texte_vide)) // on peut poursuivre
    {
      // Modification
      db_query($dbr,"UPDATE $_DB_acces SET $_DBU_acces_absence_debut='$absence_debut_date',
                               $_DBU_acces_absence_fin='$absence_fin_date',
                               $_DBU_acces_absence_msg='".preg_replace("/'/","''", stripslashes($texte))."',
                               $_DBU_acces_absence_active='$activation'
                WHERE $_DBU_acces_id='$_SESSION[auth_id]'");

      db_close($dbr);

      header("Location:$php_self?succes=1");
      exit;
    }

    db_close($dbr);
  }
  
  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    titre_page_icone("Absence : dates et message automatique", "aim_protocol_32x32_fond.png", 15, "L");

    message("Pendant votre absence, vous ne recevrez pas la notification quotidienne de messages en attente.", $__INFO);

    if(isset($texte_vide))
      message("Erreur : le message d'absence ne doit pas être vide", $__ERREUR);

    if(isset($erreur_format_date))
      message("Erreur : le format des dates est incorrect", $__ERREUR);

    if(isset($succes))
      message("Informations enregistrées avec succès", $__SUCCES);

    $dbr=db_connect();

    $result=db_query($dbr,"SELECT $_DBC_acces_absence_debut, $_DBC_acces_absence_fin, $_DBC_acces_absence_msg,
                        $_DBC_acces_absence_active
                      FROM $_DB_acces
                    WHERE $_DBC_acces_id='$_SESSION[auth_id]'");

    list($absence_debut, $absence_fin, $cur_texte, $current_active)=db_fetch_row($result,0);

    if($absence_debut!=0)
    {
      $cur_absence_debut_jour=date("j", $absence_debut);
      $cur_absence_debut_mois=date("m", $absence_debut);
      $cur_absence_debut_annee=date("Y", $absence_debut);
    }
    else
    {
      $cur_absence_debut_jour=$cur_absence_debut_mois="";
      $cur_absence_debut_annee=date("Y");
    }

    if($absence_fin!=0)
    {
      $cur_absence_fin_jour=date("j", $absence_fin);
      $cur_absence_fin_mois=date("m", $absence_fin);
      $cur_absence_fin_annee=date("Y", $absence_fin);
    }
    else
    {
      $cur_absence_fin_jour=$cur_absence_fin_mois="";
      $cur_absence_fin_annee=date("Y");
    }


    db_free_result($result);

    print("<form name='form1' enctype='multipart/form-data' method='POST' action='$php_self'>\n");
  ?>
  <table align='center'>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><b>Activer la période d'absence ?</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <?php
        if(isset($activation))
          $cur_active=$activation;
        elseif(isset($current_active))
          $cur_active=$current_active;
        else
          $cur_active='f';

        if($cur_active=='t')
        {
          $yes_checked="checked";
          $no_checked="";
        }
        else
        {
          $yes_checked="";
          $no_checked="checked";
        }

        print("<input type='radio' name='activation' value='t' style='vertical-align:middle;' $yes_checked>
            <font class='Texte_menu'>&nbsp;Oui</font>
            &nbsp;&nbsp;<input type='radio' name='activation' value='f' style='vertical-align:middle;' $no_checked>
            <font class='Texte_menu'>&nbsp;Non</font>\n");
      ?>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><b>Période <u>jours inclus</u> :</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <font class='Texte_menu'>
        JJ : <input type="text" name='absence_debut_jour' value='<?php if(isset($absence_debut_jour)) echo htmlspecialchars($absence_debut_jour, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); else echo htmlspecialchars($cur_absence_debut_jour, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size='3' maxlength='2'>&nbsp;
        MM : <input type="text" name='absence_debut_mois' value='<?php if(isset($absence_debut_mois)) echo htmlspecialchars($absence_debut_mois, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); else echo htmlspecialchars($cur_absence_debut_mois, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size='3' maxlength='2'>&nbsp;
        AAAA : <input type="text" name='absence_debut_annee' value='<?php if(isset($absence_debut_annee)) echo htmlspecialchars($absence_debut_annee, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); else echo htmlspecialchars($cur_absence_debut_annee, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size='5' maxlength='4'>
        &nbsp;&nbsp;<u><b>au</b></u>&nbsp;&nbsp;
        JJ : <input type="text" name='absence_fin_jour' value='<?php if(isset($absence_fin_jour)) echo htmlspecialchars($absence_fin_jour, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); else echo htmlspecialchars($cur_absence_fin_jour, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size='3' maxlength='2'>&nbsp;
        MM : <input type="text" name='absence_fin_mois' value='<?php if(isset($absence_fin_mois)) echo htmlspecialchars($absence_fin_mois, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); else echo htmlspecialchars($cur_absence_fin_mois, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size='3' maxlength='2'>&nbsp;
        AAAA : <input type="text" name='absence_fin_annee' value='<?php if(isset($absence_fin_annee)) echo htmlspecialchars($absence_fin_annee, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); else echo htmlspecialchars($cur_absence_fin_annee, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); ?>' size='5' maxlength='4'>&nbsp;&nbsp;
      </font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><b>Message d'absence</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <textarea name='texte' rows='8' cols='80'><?php
        if(isset($texte))
          echo htmlspecialchars(stripslashes($texte), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE);
        elseif(isset($cur_texte))
          echo htmlspecialchars(stripslashes($cur_texte), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE);
      ?></textarea>
    </td>
  </tr>
  </table>

  <div class='centered_icons_box'>
    <a href='index.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
    <input type='image' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='go_valider' value='Valider'>
    </form>
  </div>

</div>
<?php
  db_close($dbr);
  pied_de_page();
?>

<script language="javascript">
  document.form1.texte.focus()
</script>

</body></html>
