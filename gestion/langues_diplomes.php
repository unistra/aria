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

  include "../configuration/aria_config.php";
  include "$__INCLUDE_DIR_ABS/vars.php";
  include "$__INCLUDE_DIR_ABS/fonctions.php";
  include "$__INCLUDE_DIR_ABS/db.php";
  

  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

  verif_auth();

  if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
  {
    header("Location:$__MOD_DIR/gestion/noaccess.php");
    exit();
  }

  // Condition : la fiche doit être verrouillée ou être une fiche manuelle
  if((!isset($_SESSION["tab_candidat"]["lock"]) || $_SESSION["tab_candidat"]["lock"]!=1) && $_SESSION["tab_candidat"]["manuelle"]!=1)
  {
    header("Location:edit_candidature.php");
    exit;
  }

  // identifiant de l'étudiant
  $candidat_id=$_SESSION["candidat_id"];

  $dbr=db_connect();

  // Verrouillage exclusif
  $res=cand_lock($dbr, $candidat_id);

  if($res>0)
  {
    db_close($dbr);
    header("Location:fiche_verrouillee.php");
    exit;
  }
  elseif($res==-1)
  {
    db_close($dbr);
    header("Location:edit_candidature.php");
    exit;
  }

  if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p']))) // modification d'un élément existant : l'identifiant est en paramètre
  {
    if(isset($params["la_id"]) && is_numeric($params["la_id"]))
      $_SESSION["la_id"]=$params["la_id"];

    if(isset($params["suppr"]) && is_numeric($params["suppr"]))
    {
      $la_dip_id=$params["suppr"];

      if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_langues_dip WHERE $_DBC_langues_dip_id='$la_dip_id' AND $_DBC_langues_dip_langue_id='$_SESSION[la_id]'")))
        db_query($dbr,"DELETE FROM $_DB_langues_dip WHERE $_DBC_langues_dip_id='$la_dip_id' AND $_DBC_langues_dip_langue_id='$_SESSION[la_id]'");

      write_evt($dbr, $__EVT_ID_G_LANG, "Suppression diplôme langue (langue $_SESSION[la_id])", $candidat_id, $la_dip_id);

      db_close($dbr);

      header("Location:edit_candidature.php");
      exit();
    }
  }

  if(!isset($_SESSION["la_id"]))
  {
    header("Location:edit_candidature.php");
    exit();
  }

  if(isset($_POST["go"]) || isset($_POST["go_x"])) // validation du formulaire
  {
    // Diplôme
    // $diplome=trim($_POST["diplome"]);
    $diplome=stripslashes(str_replace("'","''", trim($_POST["diplome"])));
    $annee_obtention=stripslashes(str_replace("'","''", trim($_POST["annee_obtention"])));
    $resultat=stripslashes(str_replace("'","''", trim($_POST["resultat"])));

    // vérification du format de l'année (sauf si le champ est vide)
    if(empty($annee_obtention))
      $annee_obtention=0;
    elseif(!ctype_digit($annee_obtention) || $annee_obtention>date("Y"))
      $annee_format=1;

    if(empty($diplome))
      $champ_vide=1;

    if(!isset($champ_vide) && !isset($annee_format))
    {
      // vérification d'unicité
      if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_langues_dip
                          WHERE $_DBC_langues_dip_langue_id='$_SESSION[la_id]'
                          AND $_DBC_langues_dip_nom ILIKE '$diplome'
                          AND $_DBC_langues_dip_annee='$annee_obtention'")))
        $langue_dip_existe=1;
      else
      {
        /*
        $new_id=time();
        
        // vérification d'unicité
        while(db_num_rows(db_query($dbr,"SELECT $_DBC_langues_dip_id FROM $_DB_langues_dip WHERE $_DBC_langues_dip_id='$new_id'")))
          $new_id++;
        */
        $new_id=db_locked_query($dbr, $_DB_langues_dip, "INSERT INTO $_DB_langues_dip VALUES('##NEW_ID##','$_SESSION[la_id]','$diplome','$annee_obtention','$resultat')");

        write_evt($dbr, $__EVT_ID_G_LANG, "Ajout diplôme langue : $diplome (langue $_SESSION[la_id])", $candidat_id, $new_id, "INSERT INTO $_DB_langues_dip VALUES('$new_id','$_SESSION[la_id]','$diplome','$annee_obtention','$resultat')");

        db_close($dbr);

        header("Location:edit_candidature.php");
        exit();
      }
    }
  }

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    print("<div class='infos_candidat Texte'>
          <strong>" . $_SESSION["tab_candidat"]["etudiant"] ." : " . $_SESSION["tab_candidat"]["civ_texte"] . " " . $_SESSION["tab_candidat"]["nom"] . " " . $_SESSION["tab_candidat"]["prenom"] .", " . $_SESSION["tab_candidat"]["ne_le"] . " " . $_SESSION["tab_candidat"]["txt_naissance"] ."</strong>
         </div>

          <form action='$php_self' method='POST' name='form1'>");

    titre_page_icone("Langues : diplômes obtenus", "edu_languages_32x32_fond.png", 30, "L");

    if(isset($champ_vide))
      message("Formulaire incomplet : les champs en gras sont <u>obligatoires</u>", $__ERREUR);
    elseif(isset($annee_format))
      message("Le format de l'année d'obtention du diplôme est incorrect.", $__ERREUR);
    elseif(isset($langue_dip_existe))
      message("Ce diplôme existe déjà pour cette langue.", $__ERREUR);
    else
      message("Rappel : les champs en gras sont <u>obligatoires</u>", $__WARNING);
  ?>
    
  <table style="margin-left:auto; margin-right:auto;">
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'>Nom du diplôme :</font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='diplome' value='<?php if(isset($diplome)) echo htmlspecialchars(stripslashes($diplome),ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size="25" maxlength="128">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'>Année d'obtention :</font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='annee_obtention' value='<?php if(isset($annee_obtention)) echo htmlspecialchars(stripslashes($annee_obtention),ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size="25" maxlength="4">
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'>Résultat/mention :</font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='resultat' value='<?php if(isset($resultat)) echo htmlspecialchars(stripslashes($resultat),ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size="25" maxlength="128">
    </td>
  </tr>
  </table>

  <div class='centered_icons_box'>
    <a href='edit_candidature.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
    <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go" value="Valider">
    </form>
  </div>
</div>
<?php
  db_close($dbr);
  pied_de_page();
?>

<script language="javascript">
<!--
document.form1.diplome.focus()
//-->
</script>

</body></html>
