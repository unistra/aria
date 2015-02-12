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

  // identifiant de l'étudiant
  $candidat_id=$_SESSION["candidat_id"];

  $dbr=db_connect();

  if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p']))) // modification de l'élément existant : l'identifiant est en paramètre
  {
    if(isset($params["elem_id"]) && is_numeric($params["elem_id"]))
      $_SESSION["elem_id"]=$elem_id=$params["elem_id"];

    if(isset($params["elem_propspec"]) && ctype_digit($params["elem_propspec"]))
      $_SESSION["elem_propspec"]=$elem_propspec=$params["elem_propspec"];

    $result_elems=db_query($dbr, "SELECT  $_DBC_dossiers_elems_type, $_DBC_dossiers_elems_para, $_DBC_dossiers_elems_vap, 
                              $_DBC_dossiers_elems_unique
                          FROM $_DB_dossiers_elems
                        WHERE $_DBC_dossiers_elems_id='$elem_id'");
    if(db_num_rows($result_elems))
    {
      list($_SESSION["elem_type"], $elem_para, $elem_vap, $demande_unique)=db_fetch_row($result_elems, 0);
      db_free_result($result_elems);
    }
    else
    {
      db_free_result($result_elems);
      db_close($dbr);

      header("Location:login.php");
      exit();
    }
  }
  elseif(isset($_SESSION["elem_id"]))
  {
    $elem_id=$_SESSION["elem_id"];

    $result_elems=db_query($dbr, "SELECT $_DBC_dossiers_elems_type, $_DBC_dossiers_elems_para, $_DBC_dossiers_elems_vap,
                             $_DBC_dossiers_elems_unique, $_DBC_dossiers_elems_nb_choix_min,
                             $_DBC_dossiers_elems_nb_choix_max
                          FROM $_DB_dossiers_elems
                        WHERE $_DBC_dossiers_elems_id='$elem_id'");
    if(db_num_rows($result_elems))
    {
      list($_SESSION["elem_type"], $elem_para, $elem_vap, $demande_unique, $_SESSION["choix_min"], $_SESSION["choix_max"])=db_fetch_row($result_elems, 0);
      db_free_result($result_elems);
    }
    else
    {
      db_free_result($result_elems);
      db_close($dbr);

      header("Location:login.php");
      exit();
    }
  }
  else
  {
    header("Location:login.php");
    exit();
  }


  if(isset($_POST["valider"]) || isset($_POST["valider_x"])) // validation du formulaire
  {
    // Récupération et nettoyage
    // TODO : à faire sur tous les champs de tous les formulaires

    if(!isset($_SESSION["elem_propspec"]))
      $_SESSION["elem_propspec"]=0; // demande unique : reliée à toutes les formations

    if($_SESSION["elem_type"]==$__ELEM_TYPE_FORM)
    {
      // Si le contenu est de type texte, on nettoie les retours de ligne (maximum 2 pour éviter le texte exagéremment aéré)
      $contenu=preg_replace("/[\r\n]{3,50}/", "\r\n\r\n", strip_tags(trim($_POST["champ"])));
      $contenu=clean_word_str($contenu);
    }
    elseif($_SESSION["elem_type"]==$__ELEM_TYPE_UN_CHOIX)
    {
      $contenu="";

      if(isset($_POST["un_choix"]) && $_POST["un_choix"]!="" && ctype_digit($_POST["un_choix"]))
        $contenu=$_POST["un_choix"];
      elseif(isset($_SESSION["choix_min"]) && $_SESSION["choix_min"]!=0)
        $erreur_min_choix=1;
    }
    elseif($_SESSION["elem_type"]==$__ELEM_TYPE_MULTI_CHOIX)
    {
      $contenu="";

      if(isset($_POST["choix"]) && is_array($_POST["choix"]))
      {
        $_SESSION["current_choix"]=$_POST["choix"];

        if(isset($_SESSION["choix_min"]) && $_SESSION["choix_min"]!=0 && count($_SESSION["current_choix"])<$_SESSION["choix_min"])
          $erreur_min_choix=1;
        elseif(isset($_SESSION["choix_max"]) && $_SESSION["choix_max"]!=0 && count($_SESSION["current_choix"])>$_SESSION["choix_max"])
          $erreur_max_choix=1;

        if(!isset($erreur_min_choix) && !isset($erreur_max_choix))
        {
          foreach($_POST["choix"] as $choix_id)
            $contenu.="$choix_id|";
        }
      }
      elseif(isset($_SESSION["choix_min"]) && $_SESSION["choix_min"]!=0)
        $erreur_min_choix=1;
    }

    if(!isset($erreur_min_choix) && !isset($erreur_max_choix) && !isset($reponse_vide) && !isset($_SESSION["elem_contenu_id"])) // nouvel élement
    {
      $req="INSERT INTO $_DB_dossiers_elems_contenu VALUES (
          '$candidat_id',
          '$_SESSION[elem_id]',
          '$_SESSION[comp_id]',
          '".preg_replace("/[']+/", "''", stripslashes($contenu))."',
          '$_SESSION[elem_propspec]',
          '$__PERIODE');";

      db_query($dbr, $req);

      write_evt($dbr, $__EVT_ID_G_RENS, "Ajout contenu renseignement complémentaire", $candidat_id, $_SESSION["elem_id"], $req);

      db_close($dbr);

      header("Location:edit_candidature.php?succes=1");
      exit();
    }
    elseif(!isset($erreur_min_choix) && !isset($erreur_max_choix) && !isset($reponse_vide)) // mise à jour
    {
      {
        $req="UPDATE $_DB_dossiers_elems_contenu SET 
                $_DBU_dossiers_elems_contenu_para='".preg_replace("/[']+/", "''", stripslashes($contenu))."'
              WHERE $_DBU_dossiers_elems_contenu_candidat_id='$candidat_id'
              AND $_DBU_dossiers_elems_contenu_comp_id='$_SESSION[comp_id]'
              AND $_DBU_dossiers_elems_contenu_elem_id='$_SESSION[elem_id]'
              AND $_DBU_dossiers_elems_contenu_propspec_id='$_SESSION[elem_propspec]'
              AND $_DBU_dossiers_elems_contenu_periode='$__PERIODE'";

        db_query($dbr, $req);

        write_evt($dbr, $__EVT_ID_G_RENS, "Modification contenu renseignement", $candidat_id, $_SESSION["elem_id"], $req);

        db_close($dbr);

        header("Location:edit_candidature.php?succes=1");
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
    titre_page_icone("Autres renseignements", "edit_32x32_fond.png", 15, "L");

    if(isset($champs_vide))
      message("Erreur : le formulaire est vide.", $__ERREUR);

    if(isset($erreur_min_choix))
    {
      $txt_choix=$_SESSION["choix_min"]==1 ? "une réponse" : "$_SESSION[choix_min] réponses";
      message("Erreur : vous devez sélectionner au moins $txt_choix", $__ERREUR);
    }

    if(isset($erreur_max_choix))
    {
      $txt_choix=$_SESSION["choix_max"]==1 ? "d'une réponse" : "de $_SESSION[choix_max] réponses";
      message("Erreur : vous ne devez pas sélectionner plus $txt_choix", $__ERREUR);
    }

    print("<form action='$php_self' method='POST' name='form1'>\n");

    // Contenu ?
    $result=db_query($dbr, "SELECT $_DBC_dossiers_elems_contenu_para FROM $_DB_dossiers_elems_contenu
                    WHERE $_DBC_dossiers_elems_contenu_candidat_id='$candidat_id'
                    AND $_DBC_dossiers_elems_contenu_elem_id='$elem_id'
                    AND $_DBC_dossiers_elems_contenu_comp_id='$_SESSION[comp_id]'
                    AND $_DBC_dossiers_elems_contenu_periode='$__PERIODE'");

    $elem_para=nl2br($elem_para);

    if(db_num_rows($result))
    {
      $_SESSION["elem_contenu_id"]=1;
      list($contenu)=db_fetch_row($result, 0);
    }
    else
    {
      unset($_SESSION["elem_contenu_id"]); // juste au cas où ...
      $contenu="";
    }

    db_free_result($result);

  ?>

  <table align='center' width='80%'>
  <tr>
    <td class='td-gauche fond_menu2' style='text-align:justify; white-space:normal; padding:4px 20px 4px 20px;'>
      <font class='Texte_menu2'>
        <b>&#8226;&nbsp;&nbsp;<?php echo $elem_para; ?></b>
      </font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu' style='text-align:justify; padding:8px 20px 8px 20px;'>
      <?php
        // Le contenu proposé dépend du type d'élément : soit un formulaire simple, soit une liste à choix
        switch($_SESSION["elem_type"])
        {
          case $__ELEM_TYPE_FORM :    // Le plus simple : champ texte
            print("<textarea name='champ' class='textArea' rows='10'>$contenu</textarea>\n");
            break;

          case $__ELEM_TYPE_UN_CHOIX :  // Liste à choix, une réponse possible
            // Traitement du contenu : normalement une seule réponse : id du choix
            if($contenu!="" && ctype_digit($contenu))
            {
              if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_dossiers_elems_choix
                                    WHERE $_DBC_dossiers_elems_choix_id='$contenu'
                                    AND $_DBC_dossiers_elems_choix_elem_id='$elem_id'")))
                $contenu="";
            }

            // Sélection et affichage des choix
            $res_choix=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_id, $_DBC_dossiers_elems_choix_texte
                                FROM $_DB_dossiers_elems_choix
                              WHERE $_DBC_dossiers_elems_choix_elem_id='$elem_id'
                                ORDER BY $_DBC_dossiers_elems_choix_ordre");
            $nb_choix=db_num_rows($res_choix);

            if($nb_choix)
            {
              print("<table align='left' border='0' cellpadding='2'>\n");

              for($i=0; $i<$nb_choix; $i++)
              {
                list($choix_id, $choix_texte)=db_fetch_row($res_choix, $i);

                $checked=(isset($contenu) && $contenu==$choix_id) ? "checked" : "";


                print("<tr>
                      <td width='10' class='fond_menu'>
                        <input type='radio' name='un_choix' value='$choix_id' $checked style='vertical-align:middle; padding-right:8px'>
                      </td>
                      <td class='fond_menu'>
                        <font class='Texte_menu'>$choix_texte</font>
                      </td>
                    </tr>\n");
              }

              print("</table>\n");
            }

            db_free_result($res_choix);

            break;

          case $__ELEM_TYPE_MULTI_CHOIX : // Liste à choix, plusieurs réponses possibles
            // Traitement du contenu : plusieurs réponses possibles séparées par "|" (id du ou des choix)
            if($contenu!="")
            {
              $choix_array=explode("|",$contenu);
              $array_choix_ok=array();

              if(count($choix_array))
              {
                $liste_choix="";

                foreach($choix_array as $array_choix_id)
                {
                  if(ctype_digit($array_choix_id))
                    $liste_choix.="$array_choix_id,";
                }

                if($liste_choix!="")
                {
                  $liste_choix=substr($liste_choix, 0, -1);

                  // Validation des choix
                  $res_choix_valides=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_id FROM $_DB_dossiers_elems_choix
                                          WHERE $_DBC_dossiers_elems_choix_id IN ($liste_choix)
                                          AND $_DBC_dossiers_elems_choix_elem_id='$elem_id'");

                  $nb_choix_valides=db_num_rows($res_choix_valides);

                  for($i=0; $i<$nb_choix_valides; $i++)
                    list($array_choix_ok[$i])=db_fetch_row($res_choix_valides, $i);

                  db_free_result($res_choix_valides);
            
                }
              }
            }

            // Sélection et affichage des choix
            $res_choix=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_id, $_DBC_dossiers_elems_choix_texte
                                FROM $_DB_dossiers_elems_choix
                              WHERE $_DBC_dossiers_elems_choix_elem_id='$elem_id'
                                ORDER BY $_DBC_dossiers_elems_choix_ordre");
            $nb_choix=db_num_rows($res_choix);

            if($nb_choix)
            {
              print("<table align='left' border='0' cellpadding='2'>\n");

              for($i=0; $i<$nb_choix; $i++)
              {
                list($choix_id, $choix_texte)=db_fetch_row($res_choix, $i);

                if(isset($_SESSION["current_choix"]) && is_array($_SESSION["current_choix"]) && in_array($choix_id, $_SESSION["current_choix"]))
                  $checked="checked";
                elseif(isset($array_choix_ok) && in_array($choix_id, $array_choix_ok))
                  $checked="checked";
                else
                  $checked="";

                print("<tr>
                      <td width='10' class='fond_menu'>
                        <input type='checkbox' name='choix[]' value='$choix_id' $checked style='vertical-align:middle; padding-right:8px'>
                      </td>
                      <td class='fond_menu'>
                        <font class='Texte_menu'>$choix_texte</font>
                      </td>
                    </tr>\n");
              }

              print("</table>\n");
            }

            db_free_result($res_choix);

            break;
        }
      ?>
    </td>
  </tr>
  </table>

  <div class='centered_icons_box'>
    <a href='edit_candidature.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
    <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
    </form>
  </div>
  
</div>
<?php
  db_close($dbr);
  pied_de_page();
?>

<script language="javascript">
  document.form1.champ.focus()
</script>
</body></html>
