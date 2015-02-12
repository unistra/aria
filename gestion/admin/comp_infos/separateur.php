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

  // Ajout d'un séparateur

  if(isset($_SESSION["info_doc_id"]))
    $info_doc_id=$_SESSION["info_doc_id"];
  else
  {
    header("Location:index.php");
    exit;
  }

  // récupération de la position
  if(isset($_GET["a"]) && isset($_GET["o"])) // Nouvel élément
  {
    $_SESSION["ordre"]=$ordre=$_GET["o"];
    $_SESSION["ordre_max"]=$_SESSION["cbo"];
  }
  else  // pas de variable position, on sort
  {
    header("Location:index.php");
    exit;
  }

  $dbr=db_connect();

  if($_SESSION["ordre"]!=$_SESSION["ordre_max"])
  {
    // 1 - Reconstruction des éléments (comme pour la suppression)
    $a=get_all_elements($dbr, $info_doc_id);
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

  db_query($dbr,"INSERT INTO $_DB_comp_infos_sepa VALUES ('$info_doc_id', '$_SESSION[ordre]')");
  db_close($dbr);

  header("Location:index.php");
  exit;
          
?>
