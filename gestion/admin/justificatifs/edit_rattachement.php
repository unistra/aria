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
  include "include/editeur_fonctions.php";

  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

  verif_auth("$__GESTION_DIR/login.php");

  // récupération de variables

  $dbr=db_connect();

  if(isset($_GET["jid"]) && db_num_rows(db_query($dbr, "SELECT * FROM $_DB_justifs WHERE $_DBC_justifs_id='$_GET[jid]'
                                      AND $_DBC_justifs_comp_id='$_SESSION[comp_id]'")))
    $_SESSION["justif_id"]=$_GET["jid"];
  elseif(!isset($_SESSION["justif_id"]) || !isset($_SESSION["filtre_justif"]) || $_SESSION["filtre_justif"]=="-1")
  {
    db_close($dbr);
    header("Location:index.php");
    exit();
  }
    
  if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
  {
    $cond_nationalite=$_POST["cond_nat"];

    db_query($dbr,"UPDATE $_DB_justifs_jf SET $_DBU_justifs_jf_nationalite='$cond_nationalite'
              WHERE $_DBU_justifs_jf_justif_id='$_SESSION[justif_id]'
              AND $_DBC_justifs_jf_propspec_id='$_SESSION[filtre_justif]'");

    db_close($dbr);
    header("Location:index.php");
    exit;
  }

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    titre_page_icone("Modifier les propriétés d'un justificatif", "edit_32x32_fond.png", 30, "L");
  ?>

  <form method='post' action='<?php echo $php_self; ?>'>

  <table align='center'>
  <tr>
    <td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
      <font class='Texte_menu2'>
        <b>&#8226;&nbsp;&nbsp;Informations</b>
      </font>
    </td>
  </tr>
  <?php
    // Formation filtrée (et donc forcée)
    if(isset($_SESSION["filtre_justif"]) && $_SESSION["filtre_justif"]!="-1")
    {
      $result=db_query($dbr,"(SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite
                        FROM $_DB_propspec, $_DB_annees, $_DB_specs
                      WHERE $_DBC_propspec_annee=$_DBC_annees_id
                      AND $_DBC_propspec_id_spec=$_DBC_specs_id
                      AND $_DBC_propspec_id='$_SESSION[filtre_justif]')");

      if(db_num_rows($result))
      {
        list($propspec_id, $annee, $spec_nom, $finalite)=db_fetch_row($result, 0);

        $formation=$annee=="" ? "$spec_nom $tab_finalite[$finalite]" : "$annee $spec_nom $tab_finalite[$finalite]";

        print("<tr>
              <td class='td-gauche fond_menu2'>
                <font class='Texte_menu2'><b>Formation sélectionnée : </b></font>
              </td>
              <td class='td-droite fond_menu'>
                <font class='Texte_menu'><b>$formation</b></font>
              </td>
            </tr>\n");
      }

      db_free_result($result);
    }
  ?>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><b>Justificatif :</b></font>
    </td>
    <td class='td-droite fond_menu'>
    <?php
      $result=db_query($dbr,"SELECT $_DBC_justifs_intitule, $_DBC_justifs_jf_nationalite
                      FROM $_DB_justifs, $_DB_justifs_jf
                      WHERE $_DBC_justifs_id=$_DBC_justifs_jf_justif_id
                      AND $_DBC_justifs_jf_propspec_id='$propspec_id'
                      AND $_DBC_justifs_id='$_SESSION[justif_id]'");
      $rows=db_num_rows($result);

      list($justif_intitule, $justif_nationalite)=db_fetch_row($result, 0);
      $val=htmlspecialchars($justif_intitule, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);

      print("<font class='Texte_menu'><b>$val</b></font>\n");
    ?>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><b>Condition de nationalité :</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <select name='cond_nat'>
        <option <?php echo "value='$__COND_NAT_TOUS'"; if($justif_nationalite==$__COND_NAT_TOUS) echo "selected=1"; ?>>Nationalité indifférente</option>
        <option <?php echo "value='$__COND_NAT_FR'"; if($justif_nationalite==$__COND_NAT_FR) echo "selected=1"; ?>>Candidats Français uniquement</option>
        <option <?php echo "value='$__COND_NAT_NON_FR'"; if($justif_nationalite==$__COND_NAT_NON_FR) echo "selected=1"; ?>>Candidats Non Français uniquement</option>
        <option <?php echo "value='$__COND_NAT_HORS_UE'"; if($justif_nationalite==$__COND_NAT_HORS_UE) echo "selected=1"; ?>>Candidats hors UE</option>
        <option <?php echo "value='$__COND_NAT_UE'"; if($justif_nationalite==$__COND_NAT_UE) echo "selected=1"; ?>>Candidats intra-UE uniquement</option>
      </select>
    </td>
  </table>
  
  <div class='centered_icons_box'>
    <a href='index.php' target='_self'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
    <input type='image' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='go_valider' value='Valider'>
    </form>
  </div>
  
</div>
<?php
  pied_de_page();
?>
<script language="javascript">
  document.form1.comp_id.focus()
</script>

</body></html>
