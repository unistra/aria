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
  session_name("preinsc");
  session_start();

  include "../configuration/aria_config.php";
  include "$__INCLUDE_DIR_ABS/vars.php";
  include "$__INCLUDE_DIR_ABS/fonctions.php";
  include "$__INCLUDE_DIR_ABS/db.php";

  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

  // Arrivée directe sur cette page avec le paramètre composante : on le stocke
  if(isset($_GET["co"]) && ctype_digit($_GET["co"]) && $_GET["co"]>0)
  {
    $comp=str_replace(" ", "", $_GET["co"]);

    $dbr=db_connect();

    if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_composantes  WHERE $_DBC_composantes_id='$comp'")))
      $_SESSION["comp_id"]=$comp;

    // on garde le paramètre et on redirige vers l'authentification

    session_write_close();
    header("Location:../index.php");
    exit();
  }

  if(!isset($_SESSION["authentifie"]))
  {
    session_write_close();
    header("Location:../index.php");
    exit();
  }

  if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
  {
    session_write_close();
    header("Location:composantes.php");
    exit();
  }

  $dbr=db_connect();

  $candidat_id=$_SESSION["authentifie"];

  $txt_naissance=date_fr("j F Y",$_SESSION["naissance"]);
  
  switch($_SESSION["civilite"])
  {
    case "M" :      $civ_texte="M.";
                $etudiant="Candidat";
                $ne_le="Né le";
                $inscrit="inscrit";
                break;

    case  "Mlle"  :   $civ_texte="Mlle";
                $etudiant="Candidate";
                $ne_le="Née le";
                $inscrit="inscrite";
                break;

    case  "Mme" :   $civ_texte="Mme";
              $etudiant="Candidate";
              $ne_le="Née le";
              $inscrit="inscrite";
              break;

    default     : $civ_texte="M.";
                $etudiant="Candidat";
                $ne_le="Né le";
                $inscrit="inscrit";
  }

  // Quelques paramètres
  $Y=date('Y');
  $Z=$Y+1;

  // nettoyage des variables de session utilisées ailleurs
  unset($_SESSION["cid"]);
  unset($_SESSION["ctid"]);
  unset($_SESSION["la_id"]);
  unset($_SESSION["la_txt"]);
  unset($_SESSION["iid"]);
  unset($_SESSION["cand_id"]);
  unset($_SESSION["suppr"]);
  unset($_SESSION["annuler"]);
  unset($_SESSION["groupe"]);
  unset($_SESSION["ordre_spec"]);
  unset($_SESSION["modif_inid"]);
  unset($_SESSION["ce_id"]);
  unset($_SESSION["form_comp_id"]);
  unset($_SESSION["elem_contenu_id"]);
  unset($_SESSION["elem_id"]);
  unset($_SESSION["elem_type"]);
        unset($_SESSION["array_formations_groupe"]);
   
  // Onglet par défaut : documentation (0)
  if(isset($_GET["onglet"]) && is_numeric($_GET["onglet"]) && $_GET["onglet"]>=0 && $_GET["onglet"]<10)
    $_SESSION["onglet"]=$_GET["onglet"];
  elseif(!isset($_SESSION["onglet"]))
    $_SESSION["onglet"]=0;

  // Validation du formulaire
  if(isset($_POST["go"]) || isset($_POST["go_x"]))
  {
    // Nettoyage avant insertion

    db_query($dbr, "DELETE FROM $_DB_dossiers_elems_contenu
               WHERE $_DBC_dossiers_elems_contenu_candidat_id='$candidat_id'
               AND $_DBC_dossiers_elems_contenu_comp_id='$_SESSION[comp_id]'
               AND $_DBC_dossiers_elems_contenu_periode='$__PERIODE'");

    // Récupération des éléments remplis et insertion dans la base

    if(isset($_SESSION["elements_dossier"]) && count($_SESSION["elements_dossier"]))
    {
      $requete="";

      foreach($_SESSION["elements_dossier"] as $element_id)
      {
        $key="elem_$element_id";

        if(isset($_POST["$key"]))
        {
          $para=trim($_POST["$key"]);

          $requete.="INSERT INTO $_DB_dossiers_elems_contenu VALUES (
                      '$candidat_id', 
                      '$element_id', 
                      '$_SESSION[comp_id]', 
                      '".preg_replace("/[']+/", "''", stripslashes($para))."', 
                      (SELECT max($_DBC_cand_periode) FROM $_DB_cand 
                       WHERE $_DBC_cand_candidat_id='$candidat_id'
                       AND $_DBC_cand_propspec_id IN (SELECT $_DBC_dossiers_ef_propspec_id FROM $_DB_dossiers_ef 
                                                      WHERE $_DBC_dossiers_ef_elem_id='$element_id'))); ";
        }
      }

      if(!empty($requete))
      {
        db_query($dbr, $requete);
        $modifs_ok=1;
      }
    }
  }

  unset($_SESSION["elements_dossier"]);


  $adresse=$_SESSION["adresse_1"];
  $adresse.=$_SESSION["adresse_2"]!="" ? "\n".$_SESSION["adresse_2"] : "";
   $adresse.=$_SESSION["adresse_3"]!="" ? "\n".$_SESSION["adresse_3"] : "";
   $adresse.="\n". $_SESSION["adresse_cp"] 
            ." ".  $_SESSION["adresse_ville"] 
            ."\n". $_SESSION["adresse_pays"];
            
  $adresse=nl2br($adresse);

  // ================================== VERROUILLAGE ===========================================
  // Les tableaux sont maintenus en permanence en cas de changements en direct

  $result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_cand_lock, $_DBC_cand_lockdate, $_DBC_cand_id
                    FROM $_DB_cand, $_DB_propspec
                  WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
                  AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                  AND $_DBC_cand_candidat_id='$_SESSION[authentifie]'
                  AND $_DBC_cand_periode='$__PERIODE'
                  ORDER BY $_DBC_cand_lockdate, $_DBC_cand_propspec_id");

  $rows=db_num_rows($result);

  $_SESSION["array_lock"]=array();

  for($i=0; $i<$rows; $i++)
  {
    list($propspec_id, $lock, $lockdate, $candidature_id)=db_fetch_row($result, $i);

    $lockdate_txt=date_fr("l j F Y H:i", $lockdate);

    // Double index : on a parfois besoin de rechercher sur l'identifiant de la formation, et non de la candidature
    // Aucun risque de collision : les identifiants ne sont pas construits de la même façon
    $_SESSION["array_lock"][$candidature_id]=array("propspec_id" => $propspec_id,
                                    "cand_id" => $propspec_id,
                                    "lock" => $lock,
                                    "lockdate" => $lockdate,
                                    "lockdate_txt" => $lockdate_txt);

    $_SESSION["array_lock"][$propspec_id]=array("propspec_id" => $propspec_id,
                                  "cand_id" => $propspec_id,
                                  "lock" => $lock,
                                  "lockdate" => $lockdate,
                                  "lockdate_txt" => $lockdate_txt);
  }

  db_free_result($result);

  // Verrouillage global (toutes composantes confondues)
  if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_cand WHERE $_DBC_cand_candidat_id='$_SESSION[authentifie]'
                                       AND $_DBC_cand_periode='$__PERIODE'
                                       AND $_DBC_cand_lock='1'")))
    $__lock=$_SESSION["lock"]=1;
  else
    $__lock=$_SESSION["lock"]=0;

  // ============================================================================================================
  // Bandeau supérieur
  $nb_messages=en_tete_candidat();

  // Menu
  menu_sup_candidat($__MENU_FICHE);

  if(isset($_GET["q"]) && $_GET["q"]==0)
    $_SESSION["q_messages_non_lus"]=1;
?>

<div class='main' style='padding:0px;'>
  <div class='menu_gauche'>
    <div style='background-color:white; white-space:nowrap; border:thin solid black; text-align:center; margin-left:1em; margin-right:1em;'>
      <font class='Texte'>
        Suivez ce menu pour
        <br>compléter votre fiche
      </font>
      <br>
      <img src='<?php echo "$__ICON_DIR/go-down_32x32_blanc.png"; ?>' border='0'>
    </div>

    <ul class='menu_gauche'>

    <?php
      $cnt_menu=count($menu);

      if($_SESSION["onglet"]==0 && $nb_messages && !isset($_SESSION["q_messages_non_lus"]))
        $_SESSION["onglet"]=-1;
      elseif($_SESSION["onglet"]==-1)
        $_SESSION["onglet"]=0;

      for($i=0; $i<$cnt_menu; $i++)
      {
        $onglet=$i;
        $nom_onglet=$menu[$onglet];

        if($_SESSION["onglet"]!=$onglet)
          print("<li class='menu_gauche'><a href='$php_self?onglet=$onglet' class='lien_menu_gauche' target='_self'>$nom_onglet</a></li>");
        else
          print("<li class='menu_gauche_select'><strong>$nom_onglet</strong></li>");

      }

      // Le dernier onglet n'apparait que s'il y a des éléments supplémentaires à compléter
      //(CF. Constructeur de Dossiers dans la partie Gestion)
      $result_elems=db_query($dbr, "SELECT  $_DBC_dossiers_elems_id, $_DBC_dossiers_elems_type, $_DBC_dossiers_elems_para, $_DBC_propspec_id,
                                $_DBC_dossiers_elems_vap, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite,
                                $_DBC_dossiers_elems_unique
                          FROM $_DB_dossiers_elems, $_DB_dossiers_ef, $_DB_propspec, $_DB_specs, $_DB_annees
                        WHERE $_DBC_dossiers_elems_id=$_DBC_dossiers_ef_elem_id
                        AND $_DBC_propspec_id=$_DBC_dossiers_ef_propspec_id
                        AND $_DBC_propspec_annee=$_DBC_annees_id
                        AND $_DBC_propspec_id_spec=$_DBC_specs_id
                        AND $_DBC_dossiers_ef_propspec_id IN (SELECT $_DBC_cand_propspec_id FROM $_DB_cand, $_DB_propspec
                                                  WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
                                                  AND $_DBC_cand_candidat_id='$candidat_id'
                                                  AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                                  AND $_DBC_cand_periode>='$__PERIODE')
                        ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom, $_DBC_propspec_finalite,
                              $_DBC_dossiers_ef_ordre");

      $rows_elems=db_num_rows($result_elems);

      if($rows_elems)
      {
        $onglet=6;
        $nom_onglet="Autres renseignements";

        if($_SESSION["onglet"]!=6)
          print("<li class='menu_gauche'><a href='$php_self?onglet=$onglet' class='lien_menu_gauche' target='_self'>$onglet - $nom_onglet</a></li>\n");
        else
          print("<li class='menu_gauche_select'><strong>$onglet - $nom_onglet</strong></li>\n");
      }

      // Autres onglets en fin de liste (infos utiles et justificatifs à renvoyer par mail)

      // Récupération des infos actuelles pour modifier l'affichage du menu
      $res_candidat=db_query($dbr, "SELECT $_DBC_candidat_deja_inscrit, $_DBC_candidat_annee_premiere_inscr,
                              $_DBC_candidat_annee_bac, $_DBC_candidat_serie_bac, $_DBC_candidat_dpt_naissance
                            FROM $_DB_candidat
                          WHERE $_DBC_candidat_id='$_SESSION[authentifie]'");

      if(db_num_rows($res_candidat))
        list($cur_deja_inscrit, $cur_annee_premiere_inscr, $cur_annee_bac , $cur_serie_bac, $cur_dpt_naissance)=db_fetch_row($res_candidat, 0);
      else
        $cur_deja_inscrit=$cur_annee_premiere_inscr=$cur_annee_bac=$cur_serie_bac=$cur_dpt_naissance="";

      db_free_result($res_candidat);

      print("<li class='menu_gauche' style='margin:30px 0px 10px 0px;'><u><strong>Divers</strong></u></li>\n");

      // JUSTIFICATIFS
      if($_SESSION["onglet"]!=8)
        print("<li class='menu_gauche'><a href='$php_self?onglet=8' class='lien_menu_gauche' target='_self'>- Liste Justificatifs</a></li>\n");
      else
        print("<li class='menu_gauche_select'><strong>- Liste Justificatifs</strong></li>\n");

      // INFOS COMPOSANTES
      if($_SESSION["onglet"]!=9)
        print("<li class='menu_gauche'><a href='$php_self?onglet=9' class='lien_menu_gauche' target='_self'>- Infos Composante</a></li>\n");
      else
        print("<li class='menu_gauche_select'><strong>- Infos Composante</strong></li>\n");

    ?>
    </ul>
  </div>
  <div class='corps'>
    <?php
      if($nb_messages && !isset($_SESSION["q_messages_non_lus"]))
        include "menu_message.php";
      else
      {

        switch($_SESSION["onglet"])
        {
          case 0  : include "menu_documentation.php";
                  break;

          case 1  : include "menu_identite.php";
                  break;

          case 2  : include "menu_cursus.php";
                  break;

          case 3  : include "menu_langues.php";
                  break;

          case 4  : include "menu_infos_complementaires.php";
                  break;

          case 5  : include "menu_precandidatures.php";
                  break;

          case 6  : include "menu_autres_renseignements.php";
                  break;

          case 8  : include "menu_justificatifs.php";
                  break;

          case 9  : include "menu_infos_composante.php";
                  break;
        }

      }

      db_free_result($result_elems);
      db_close($dbr);
     ?>
  </div>
</div>
<?php
  pied_de_page_candidat();
?>
</body>
</html>


