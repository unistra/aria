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

  // Suppression d'un élément
  // Arguments :
  // o : ordre de l'objet (dans le tableau) à supprimer (il faudra décaler tous les objets suivants).
  // récupération des variables cryptées
  
  if(isset($_GET["o"]) && isset($_SESSION["info_doc_id"]))
  {
    $info_doc_id=$_SESSION["info_doc_id"];
    $o=$_GET["o"];
  }
  elseif(isset($_POST["o"]) && isset($_SESSION["info_doc_id"]))
  { 
    $info_doc_id=$_SESSION["info_doc_id"];
    $o=$_POST["o"];
  }
  else
  {
    header("Location:index.php");
    exit;
  }

  if(isset($_POST["confirmer"]) || isset($_POST["confirmer_x"]))
  {
    $o=$_POST["o"];

    $dbr=db_connect();

    $a=get_all_elements($dbr, $info_doc_id);

    // à priori, tout est bon, on supprime et on décale les éléments restants

    $nb_elements=count($a);
    $suppr_id=$a["$o"]["id"];
    $suppr_type=$a["$o"]["type"];

    // Fichier ?
    if($suppr_type==6)
    {
      $result=db_query($dbr, "SELECT $_DBC_comp_infos_fichiers_fichier FROM $_DB_comp_infos_fichiers
                                  WHERE $_DBC_comp_infos_fichiers_info_id='$suppr_id'
                                  AND $_DBC_comp_infos_fichiers_ordre='$o'");

      if(db_num_rows($result))
      {
        list($fichier_nom)=db_fetch_row($result, 0); // un seul résultat, normalement

        if(is_file("$__CAND_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$fichier_nom"))
          unlink("$__CAND_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$fichier_nom");
      }

      db_free_result($result);
    }

    $suppr_table_name=get_table_name($suppr_type);
    $col_ordre=$suppr_table_name["ordre"];
    $col_id=$suppr_table_name["id"];
    $table=$suppr_table_name["table"];

    db_query($dbr,"DELETE FROM $table WHERE $col_id='$suppr_id' AND $col_ordre='$o'");

    for($i=($o+1); $i<$nb_elements; $i++)
    {
      $current_ordre=$i;
      $new_ordre=$i-1;
      $current_type=$a["$i"]["type"]; // le type sert juste à savoir dans quelle table on doit modifier l'élément courant
      $current_id=$a["$i"]["id"];

      $current_table_name=get_table_name($current_type);
      $col_ordre=$current_table_name["ordre"];
      $col_id=$current_table_name["id"];
      $table=$current_table_name["table"];

      db_query($dbr,"UPDATE $table SET $col_ordre='$new_ordre' WHERE $col_id='$current_id' AND $col_ordre='$current_ordre'");
    }

    // décalage terminé
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
    titre_page_icone("Supprimer un élément", "trashcan_full_32x32_slick_fond.png", 30, "L");

    message("La suppression d'un élément est <strong>définitive</strong>.", $__WARNING);

    message("Souhaitez-vous vraiment supprimer cet élément ?", $__QUESTION);

    print("<form method='post' action='$php_self'>\n
         <input type='hidden' name='o' value='$o'>

         <div class='centered_icons_box'>
          <a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
          <input type='image' src='$__ICON_DIR/trashcan_full_32x32_slick_fond.png' alt='Confirmer' name='confirmer' value='Confirmer'>
         </div>

         </form>\n");
  ?>
</div>
<?php
  pied_de_page();
?>
</body></html>
