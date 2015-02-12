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

  verif_auth("../../login.php");

  $dbr=db_connect();

  // edition d'un paragraphe

  // article et filiere
  if(isset($_SESSION["lettre_id"]))
    $lettre_id=$_SESSION["lettre_id"];
  else
  {
    header("Location:index.php");
    exit;
  }

  if(isset($_GET["a"]) && isset($_GET["o"])) // Nouvel élément
  {
    $_SESSION["ordre"]=$ordre=$_GET["o"];
    $_SESSION["ordre_max"]=$_SESSION["cbo"];

    $_SESSION["ajout"]=1;
    $action="Ajouter";
  }
  elseif(isset($_SESSION["ordre"]) && isset($_SESSION["ordre_max"]) && isset($_SESSION["ajout"]))
  {
    $ordre=$_SESSION["ordre"];
    $action="Ajouter";
  }
  elseif(isset($_GET["o"])) // Modification
  {
    $_SESSION["ordre"]=$ordre=$_GET["o"];

    $action="Modifier";

    // Récupération des infos actuelles
    $result=db_query($dbr,"SELECT $_DBC_para_texte, $_DBC_para_align, $_DBC_para_gras, $_DBC_para_italique,
                        $_DBC_para_taille, $_DBC_para_marge_g
                      FROM $_DB_para
                    WHERE $_DBC_para_lettre_id='$lettre_id'
                    AND $_DBC_para_ordre='$ordre'");
    $rows=db_num_rows($result);
    if($rows)
    {
      list($texte, $alignement, $gras, $italique, $taille, $marge_g)=db_fetch_row($result,0);
      db_free_result($result);
    }
    else
    {
      db_close($dbr);
      header("Location:editeur.php");
      exit();
    }
  }
  elseif(isset($_SESSION["ordre"]))
  {
    $ordre=$_SESSION["ordre"];
    $action="Modifier";
  }

  if(isset($_SESSION["ajout"]) && $_SESSION["ajout"]==1)
    $action="Ajouter";
  else
    $action="Modifier";

  // section exécutée lorsque le formulaire est validé
  if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
  {
    $texte=$_POST['new_para'];
    $alignement=$_POST['alignement'];
    $gras=$_POST["gras"];
    $italique=$_POST["italique"];
    $taille=trim($_POST["taille"]);
    $marge_g=trim($_POST["marge_g"]);

    $marge_g=($marge_g=="" || !ctype_digit($marge_g) || $marge_g<0 || $marge_g>200) ? 0 : $marge_g;

    $taille=($taille=="" || !ctype_digit($taille) || $taille<4) ? "10" : $taille;

    if($texte=="")
      $para_vide=1;
    else
    {
      if(!isset($_SESSION["ajout"]))
        db_query($dbr,"UPDATE $_DB_para SET $_DBU_para_texte='".preg_replace("/[']+/", "''", stripslashes($texte))."',
                                $_DBU_para_align='$alignement',
                                $_DBU_para_gras='$gras',
                                $_DBU_para_italique='$italique',
                                $_DBU_para_taille='$taille',
                                $_DBU_para_marge_g='$marge_g'
                  WHERE $_DBU_para_lettre_id='$lettre_id'
                  AND $_DBU_para_ordre='$_SESSION[ordre]'");
      else
      {
        if($_SESSION["ordre"]!=$_SESSION["ordre_max"]) // On n'insère pas l'élément en dernier : décallage
        {
          // 1 - Reconstruction des éléments (comme pour la suppression)
          $a=get_all_elements($dbr, $lettre_id);
          $nb_elements=count($a);

          for($i=$nb_elements; $i>$_SESSION["ordre"]; $i--)
          {
            $current_ordre=$i-1;
            $new_ordre=$i;
            $current_type=$a["$current_ordre"]["type"]; // le type sert juste à savoir dans quelle table on doit modifier l'élément courant
            $current_id=$a["$current_ordre"]["id"];

            $current_table_name=get_table_name($current_type);
            $col_ordre=$current_table_name["ordre"];
            $col_id=$current_table_name["id"];
            $table=$current_table_name["table"];

            db_query($dbr,"UPDATE $table SET $col_ordre='$new_ordre'
                                WHERE $col_id='$current_id'
                                AND $col_ordre='$current_ordre'");
          }
        }

        // Insertion du nouvel élément
        db_query($dbr,"INSERT INTO $_DB_para VALUES (
            '$lettre_id', 
            '$_SESSION[ordre]',
            '".preg_replace("/[']+/", "''", stripslashes($texte))."',
            '$gras',
            '$italique',
            '$alignement',
            '$taille',
            '$marge_g')");
      }

      db_close($dbr);

      header("Location:editeur.php");
      exit;
    }
  }

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_simple();
?>

<div class='main'>
  <?php
    titre_page_icone("$action un paragraphe", "abiword_32x32_fond.png", 15, "L");

    if(isset($para_pas_clean))
      message("<center>Erreur : le texte contient des caractères non autorisés.
            <br>Les caractères autorisés sont : a-z A-Z 0-9 - ' ! ? _ : . / @ ( ) les caractères accentués, la virgule et l'espace.</center>", $__ERREUR);

    if(isset($para_vide))
      message("Erreur : le texte du paragraphe ne doit pas être vide.", $__ERREUR);

    $texte=isset($texte) ? htmlspecialchars(stripslashes($texte),ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE) : "";
    $current_taille=isset($taille) ? $taille : "10";
    $current_marge_g=isset($marge_g) ? $marge_g : "0";

    if(isset($alignement))
    {
      switch($alignement)
      {
        case 0:   $c0="checked"; $c1=$c2=$c3=""; // A gauche
              break;
        case 1:   $c1="checked"; $c0=$c2=$c3=""; // Centré
              break;
        case 2:   $c2="checked"; $c0=$c1=$c3=""; // A droite
              break;
        case 3:   $c3="checked"; $c0=$c1=$c2=""; // Justifié (non supporté par IE 6)
              break;

        default:  $c3="checked"; $c0=$c1=$c2=""; // Justifié (non supporté par IE 6)
              break;
      }
    }
    else
    {
      $c3="checked";
      $c1=$c2=$c0="";
    }

    if(isset($gras) && $gras)
    {
      $gras_1="checked"; $gras_0="";
    }
    else
    {
      $gras_1=""; $gras_0="checked";
    }

    if(isset($italique) && $italique)
    {
      $italique_1="checked"; $italique_0="";
    }
    else
    {
      $italique_1=""; $italique_0="checked";
    }

    print("<form method='post' action='$php_self'>\n
         <input type='hidden' name='act' value='1'>

        <table align='center'>
        <tr>
          <td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
            <font class='Texte_menu2'>
              <b>&#8226;&nbsp;&nbsp;Données du paragraphe</b>
            </font>
          </td>
        </tr>
        <tr>
          <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Texte du paragraphe :</b></font>
          </td>
          <td class='td-droite fond_menu'>
            <textarea  name='new_para' rows='10' cols='85' class='input'>$texte</textarea>
          </td>
        </tr>
        <tr>
          <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Marge gauche supplémentaire : </b></font>
          </td>
          <td class='td-droite fond_menu'>
            <input type='text' name='marge_g' size='10' maxlength='3' value='$current_marge_g'>
            <font class='Texte'><i>(en mm, par rapport au bord gauche du cadre délimitant le corps de la lettre)</i></font>
          </td>
        </tr>
        <tr>
          <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Taille du texte (en points) : </b></font>
          </td>
          <td class='td-droite fond_menu'>
            <input type='text' name='taille' size='10' maxlength='2' value='$current_taille'>
          </td>
        </tr>
        <tr>
          <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Alignement du texte :</b></font>
          </td>
          <td class='td-droite fond_menu'>
            <font class='Texte_menu'>
              A gauche <input type='radio' name='alignement' value='0' $c0>
              &nbsp;&nbsp;Centr&eacute <input type='radio' name='alignement' value='1' $c1>
              &nbsp;&nbsp;A droite <input type='radio' name='alignement' value='2' $c2>
              &nbsp;&nbsp;Justifié <input type='radio' name='alignement' value='3' $c3>
            </font>
          </td>
        </tr>
        <tr>
          <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Afficher en gras ?</b></font>
          </td>
          <td class='td-droite fond_menu'>
            <font class='Texte_menu'>
              Oui <input type='radio' name='gras' value='1' $gras_1>
              &nbsp;&nbsp;Non <input type='radio' name='gras' value='0' $gras_0>
            </font>
          </td>
        </tr>
        <tr>
          <td class='td-gauche fond_menu2'>
            <font class='Texte_menu2'><b>Afficher en italique ?</b></font>
          </td>
          <td class='td-droite fond_menu'>
            <font class='Texte_menu'>
              Oui <input type='radio' name='italique' value='1' $italique_1>
              &nbsp;&nbsp;Non <input type='radio' name='italique' value='0' $italique_0>
            </font>
          </td>
        </tr>
        </table>\n");

        db_close($dbr);
    ?>

  </font>
  <div class='centered_icons_box'>
    <a href='editeur.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
    <input type='image' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='go_valider' value='Valider'>
    </form>
  </div>

  <table cellpadding='2' align='center' style='padding-bottom:30px;'>
  <tr>
    <td class='fond_menu2' align='justify' colspan='2' style='padding:4px;'>
      <font class='Texte_menu2'><b>Les lettres peuvent s'adapter aux informations de chaque candidat(e) grâce aux macros suivantes : </b></font>
    </td>
  </tr>
  <tr>
    <td class='fond_menu' align='justify' colspan='2' style='padding:4px;'>
      <font class='Texte_menu'><b>Informations relatives au candidat : </b></font>
    </td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%Civilité%</b></font></td>
    <td align='justify'><font class='Texte'>Civilité du candidat (Monsieur, Madame, Mademoiselle - sensible aux majuscules)</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%Civ%</b></font></td>
    <td align='justify'><font class='Texte'>Civilité abbrégée (M., Mme., Mlle. - sensible aux majuscules)</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%Nom%</b></font></td>
    <td align='justify'><font class='Texte'>Nom du candidat (sensible aux majuscules : %NOM%, %Nom%, %nom%)</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%Prénom%</b></font></td>
    <td align='justify'><font class='Texte'>Prénom du candidat (même remarque)</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%naissance%</b></font></td>
    <td align='justify'><font class='Texte'>Date de naissance du candidat</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%ville_naissance%</b></font></td>
    <td align='justify'><font class='Texte'>Ville de naissance du candidat</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%pays_naissance%</b></font></td>
    <td align='justify'><font class='Texte'>Pays de naissance du candidat</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%cursus%</b></font></td>
    <td align='justify'><font class='Texte'>Cursus du candidat (limité aux dernières années pour ne pas surcharger les lettres)</font></td>
  </tr>
  <tr>
    <td class='fond_menu' align='justify' colspan='2' style='padding:4px;'>
      <font class='Texte_menu'><b>Informations relatives à la formation : </b></font>
    </td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%année_universitaire%</b></font></td>
    <td align='justify'><font class='Texte'>Année universitaire concernée par la lettre (par exemple : "<?php echo $__PERIODE . "/" . ($__PERIODE+1); ?>")</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%Formation%</b></font></td>
    <td align='justify'><font class='Texte'>Formation demandée par le candidat (sensible aux majuscules)</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%Responsable%</b></font></td>
    <td align='justify'><font class='Texte'>Responsable de la Formation (champ complété dans les propriétés de chaque formation)</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%courriel_responsable%</b></font></td>
    <td align='justify'><font class='Texte'>Responsable de la Formation (champ complété dans les propriétés de chaque formation)</font></td>
  </tr>
  <tr>
    <td class='fond_menu' align='justify' colspan='2' style='padding:4px;'>
      <font class='Texte_menu'><b>Informations relatives à la décision : </b></font>
    </td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%Transmission%</b></font></td>
    <td align='justify'><font class='Texte'>Nouvelle formation en cas de transfert de dossier (sensible aux majuscules)</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%rang_attente%</b></font></td>
    <td align='justify'><font class='Texte'>Rang du candidat sur la liste complémentaire</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%motifs%</b></font></td>
    <td align='justify'><font class='Texte'>Motifs de Refus, de Mise en Attente ou d'Admission sous Réserve</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%entretien_date%</b></font></td>
    <td align='justify'><font class='Texte'>Date de la convocation à un entretien (complète, sans article, ex : "lundi 11 juillet <?php echo date("Y"); ?>")</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%entretien_heure%</b></font></td>
    <td align='justify'><font class='Texte'>Heure de la convocation à un entretien (sans préposition, ex : "11h30")</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%entretien_salle%</b></font></td>
    <td align='justify'><font class='Texte'>Salle dans laquelle l'entretien aura lieu<br>(en fonction des valeurs entrées dans les formulaires)</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%entretien_lieu%</b></font></td>
    <td align='justify'><font class='Texte'>Adresse du bâtiment (composante) dans lequel l'entretien aura lieu<br>(également en fonction des valeurs entrées)</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%code%</b></font></td>
    <td align='justify'><font class='Texte'>Code personnel pour l'inscription administrative (APOGEE)</font></td>
  </tr>
  <tr>
    <td class='fond_menu' align='justify' colspan='2' style='padding:4px;'>
      <font class='Texte_menu'><b>Autres : </b></font>
    </td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%signature%</b></font></td>
    <td align='justify'><font class='Texte'>Signature (dépend de la configuration de la lettre et des données par défaut)</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%date%</b></font></td>
    <td align='justify'><font class='Texte'>Date de génération de la lettre, en toutes lettres</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%date_commission%</b></font></td>
    <td align='justify'><font class='Texte'>Date de la commission pédagogique, en toutes lettres</font></td>
  </tr>
  <tr>
    <td align='justify'><font class='Texte'><b>%aaaa/bbbb%</b></font></td>
    <td align='justify'><font class='Texte'>En fonction du candidat : affiche "aaaa" si c'est un homme, "bbbb" si c'est une femme (exemple : %admis/admise%)</font>
  </tr>
  </table>
</div>
<?php
  pied_de_page();
?>
</body></html>
