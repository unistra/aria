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

  $dbr=db_connect();
  
  if(isset($_GET["succes"]))
    $succes=$_GET["succes"];

  // Retour dans le formulaire : nettoyage de certaines variables
  if(isset($_GET["r"]))
  {
    unset($_SESSION["gestion_choix"]);

    if($_GET["r"]==1 && isset($_SESSION["ajout"]))
      unset($_SESSION["current_element_type"]);

    if($_GET["r"]==2)
      $resultat=1;
  }

  if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]) || (isset($_GET["eid"]) && ctype_digit($_GET["eid"])))
  {
    $_SESSION["element_id"]=isset($_GET["eid"]) ? $_GET["eid"] : $_POST["element_id"];

    $res_element=db_query($dbr,"SELECT $_DBC_dossiers_elems_type FROM $_DB_dossiers_elems
                       WHERE $_DBC_dossiers_elems_id='$_SESSION[element_id]'
                       AND $_DBC_dossiers_elems_comp_id='$_SESSION[comp_id]'");

    if(db_num_rows($res_element))
      list($_SESSION["current_element_type"])=db_fetch_row($res_element, 0);
    else
      $_SESSION["current_element_type"]=$__ELEM_TYPE_FORM;

    db_free_result($res_element);

    $resultat=1;
  }

  if(isset($_POST["suivant_type"]) || isset($_POST["suivant_type_x"]))
  {
    $_SESSION["current_element_type"]=isset($_POST["type"]) ? $_POST["type"] : $__ELEM_TYPE_FORM;
    $resultat=1;
  }

  if(isset($_POST["valider"]) || isset($_POST["valider_x"]) || isset($_POST["suivant_choix"]) || isset($_POST["suivant_choix_x"]))
  {
    $_SESSION["element_intitule"]=trim($_POST['intitule']);
    $element_para=trim($_POST['paragraphe']);
    $element_vap=$_POST["vap"];

    $element_unique=(isset($_POST["element_unique"]) && $_POST["element_unique"]!="") ? $_POST["element_unique"] : "t";

    $_SESSION["element_obligatoire"]=(isset($_POST["element_obligatoire"]) && $_POST["element_obligatoire"]!="") ? $_POST["element_obligatoire"] : "t";

    $element_recapitulatif=(isset($_POST["element_recapitulatif"]) && $_POST["element_recapitulatif"]!="") ? $_POST["element_recapitulatif"] : "t";

    $element_nouvelle_page=(isset($_POST["element_nouvelle_page"]) && $_POST["element_nouvelle_page"]!="") ? $_POST["element_nouvelle_page"] : "f";
    $element_extractions=(isset($_POST["element_extractions"]) && $_POST["element_extractions"]!="") ? $_POST["element_extractions"] : "f";

    if(!isset($_SESSION["current_element_type"]))
      $_SESSION["current_element_type"]=$_POST["type"];

    $dbr=db_connect();

    if(!isset($_SESSION["ajout"]) && isset($_SESSION["element_id"])) // Modification
    {
      // unicité  
      if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_dossiers_elems
                          WHERE ($_DBC_dossiers_elems_intitule ILIKE '".preg_replace("/[']+/", "''", stripslashes($_SESSION["element_intitule"]))."'
                                  AND $_DBC_dossiers_elems_para ILIKE '".preg_replace("/[']+/", "''", stripslashes($element_para))."')
                          AND $_DBC_dossiers_elems_comp_id='$_SESSION[comp_id]'
                          AND $_DBC_dossiers_elems_id!='$_SESSION[element_id]'")))
        $element_existe="1";
    }
    else // Ajout
    {
      // unicité
      if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_dossiers_elems
                          WHERE ($_DBC_dossiers_elems_intitule ILIKE '".preg_replace("/[']+/", "''", stripslashes($_SESSION["element_intitule"]))."'
                                  AND $_DBC_dossiers_elems_para ILIKE '".preg_replace("/[']+/", "''", stripslashes($element_para))."')
                          AND $_DBC_dossiers_elems_comp_id='$_SESSION[comp_id]'")))
      $element_existe="1";
    }

    // vérification des champs
    if($_SESSION["element_intitule"]=="") $intitule_vide=1;
    if($element_para=="") $para_vide=1;

    if(!isset($element_existe) && !isset($intitule_vide) && !isset($para_vide)) // on peut poursuivre
    {
      // Modification
      if(!isset($_SESSION["ajout"]) && isset($_SESSION["element_id"]))
        db_query($dbr,"UPDATE $_DB_dossiers_elems SET
            $_DBU_dossiers_elems_intitule='".preg_replace("/[']+/", "''", stripslashes($_SESSION["element_intitule"]))."',
            $_DBU_dossiers_elems_vap='$element_vap',
            $_DBU_dossiers_elems_type='$_SESSION[current_element_type]',
            $_DBU_dossiers_elems_para='".preg_replace("/[']+/", "''", stripslashes($element_para))."',
            $_DBU_dossiers_elems_unique='$element_unique',
            $_DBU_dossiers_elems_obligatoire='$_SESSION[element_obligatoire]',
            $_DBU_dossiers_elems_recapitulatif='$element_recapitulatif',
            $_DBU_dossiers_elems_nouvelle_page='$element_nouvelle_page',
            $_DBU_dossiers_elems_extractions='$element_extractions'
            WHERE $_DBU_dossiers_elems_id='$_SESSION[element_id]'");
      else
      {
        // Valeurs par défaut
        $min_choix=$_SESSION["element_obligatoire"]=="t" ? "1" : "0";
        $max_choix=$_SESSION["current_element_type"]==$__ELEM_TYPE_UN_CHOIX ? "1" : "0";
        
        $_SESSION["element_id"]=db_locked_query($dbr, $_DB_dossiers_elems, "INSERT INTO $_DB_dossiers_elems VALUES (
            '##NEW_ID##',
            '$_SESSION[current_element_type]', 
            '".preg_replace("/[']+/", "''", stripslashes($_SESSION["element_intitule"]))."', 
            '".preg_replace("/[']+/", "''", stripslashes($element_para))."', 
            '20', 
            '$element_vap',
            '$_SESSION[comp_id]', 
            '$element_unique', 
            '$_SESSION[element_obligatoire]', 
            '$element_recapitulatif', 
            '$min_choix', 
            '$max_choix', 
            '$element_nouvelle_page',
            '$element_extractions')");
      }

      db_close($dbr);

      // Si l'élément est à choix, on redirige vers la gestion des choix pour cet élément
      if($_SESSION["current_element_type"]!=$__ELEM_TYPE_UN_CHOIX && $_SESSION["current_element_type"]!=$__ELEM_TYPE_MULTI_CHOIX)
      {
        header("Location:element.php?succes=1&r=1");
        exit;
      }
      else
      {
        $resultat=1;
        $_SESSION["gestion_choix"]=1;
      }
    }
    else
    {
      $resultat=1;
      db_close($dbr);
    }
  }
  elseif(isset($_POST["terminer"]) || isset($_POST["terminer_x"]))  // Validation des min&max
  {
    $min_elements=(isset($_POST["min_choix"]) && ctype_digit($_POST["min_choix"])) ? $_POST["min_choix"] : "0";
    $max_elements=(isset($_POST["max_choix"]) && ctype_digit($_POST["max_choix"])) ? $_POST["max_choix"] : "0";

    if($_SESSION["element_obligatoire"]=='t' && $min_elements==0)
      $min_elements=1;

    // print("DBG : $min_elements");

    if($min_elements>0 && $max_elements>0 && $min_elements>$max_elements)
      switch_vals($min_elements, $max_elements);

    db_query($dbr,"UPDATE $_DB_dossiers_elems SET 
        $_DBU_dossiers_elems_nb_choix_min='$min_elements',
        $_DBU_dossiers_elems_nb_choix_max='$max_elements'
        WHERE $_DBU_dossiers_elems_id='$_SESSION[element_id]'");
    db_close($dbr);

    header("Location:element.php?succes=1&r=1");
    exit;
  }

  unset($_SESSION["ajout_choix"]);
  
  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    if(isset($_GET["a"]) || isset($_SESSION["ajout"]))
    {
      $_SESSION["ajout"]=1;
      titre_page_icone("Constructeur de dossiers : créer un élément", "add_32x32_fond.png", 15, "L");
    }
    else
      titre_page_icone("Constructeur de dossiers : modifier un élément existant", "edit_32x32_fond.png", 15, "L");

    if(isset($intitule_vide))
      message("Erreur : le champ 'Intitulé' ne doit pas être vide", $__ERREUR);

    if(isset($para_vide))
      message("Erreur : le champ 'Paragraphe' ne doit pas être vide", $__ERREUR);

    if(isset($element_existe))
      message("Erreur : cet élément existe déjà !", $__ERREUR);

    if(isset($succes))
    {
      if(!isset($_SESSION["ajout"]))
        message("L'élément a été modifié avec succès.", $__SUCCES);
      else
        message("L'élément a été créé avec succès.", $__SUCCES);
    }

    $dbr=db_connect();

    print("<form action='$php_self' method='POST' name='form1'>\n");

    // En cas d'ajout uniquement : on demande le type d'élément
    if(!isset($_SESSION["current_element_type"]) && isset($_SESSION["ajout"]))
    {
      message("<center>
              <strong>Etape 1 : sélection du type d'élément.</strong>
              <br>En fonction du type, les options seront différentes.
            </center>\n", $__INFO);

      print("<table align='center'>
          <tr>
            <td class='td-gauche fond_menu2'>
              <font class='Texte_menu2'><b>Type d'élément :</b></font>
            </td>
            <td class='td-droite fond_menu'>
              <select name='type'>\n");

      if(isset($element_type))
        $type=$element_type;
      elseif(isset($current_type))
        $type=$current_type;
      else
        $type=$__ELEM_TYPE_FORM;
  ?>
            <option <?php echo "value='$__ELEM_TYPE_FORM'"; if($type==$__ELEM_TYPE_FORM) echo "selected=1"; ?>>Formulaire standard (réponse libre)</option>
            <option <?php echo "value='$__ELEM_TYPE_UN_CHOIX'"; if($type==$__ELEM_TYPE_UN_CHOIX) echo "selected=1"; ?>>Un seul choix parmi ...</option>
            <option <?php echo "value='$__ELEM_TYPE_MULTI_CHOIX'"; if($type==$__ELEM_TYPE_MULTI_CHOIX) echo "selected=1"; ?>>Plusieurs choix parmi ...</option>
          </select>
        </td>
      </tr>
      </table>

      <div class='centered_icons_box'>
        <a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
        <input type='image' class='icone' src='<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>' alt='Suivant' name='suivant_type' value='Suivant'>
        </form>
      </div>
  <?php
    }
    if(!isset($resultat) && !isset($_SESSION["gestion_choix"]) && !isset($_GET["a"]) && !isset($_SESSION["ajout"]) && !isset($_GET["eid"])) // choix de l'élément à modifier
    {
      message("<strong>Etape 1 : sélection de l'élément à modifier.</strong>\n", $__INFO);

      $result=db_query($dbr, "SELECT $_DBC_dossiers_elems_id, $_DBC_dossiers_elems_intitule
                        FROM $_DB_dossiers_elems
                      WHERE $_DBC_dossiers_elems_comp_id='$_SESSION[comp_id]'
                      ORDER BY $_DBC_dossiers_elems_type, $_DBC_dossiers_elems_intitule ASC");

      $rows=db_num_rows($result);

      if($rows)
      {
        print("<center>
              <font class='Texte'>Elément à modifier : </font>
              <select name='element_id' size='1'>\n");

        $old_univ="";

        for($i=0; $i<$rows; $i++)
        {
          list($element_id, $intitule)=db_fetch_row($result,$i);

          $value=htmlspecialchars($intitule, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE);

          print("<option value='$element_id' label=\"$value\">$value</option>\n");
        }

        db_free_result($result);

        print("</select>\n
          </center>

          <div class='centered_icons_box'>
            <a href='index.php' target='_self'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
            <input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='suivant' value='Suivant'>
            </form>
          </div>

          <script language='javascript'>
            document.form1.element_id.focus()
          </script>\n");
      }
      else
      {
        message("Il n'y a aucun élément modifiable.", $__INFO);

        print("<div class='centered_box'>
              <a href='index.php' target='_self' class='lien_bleu_12'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
            </div>\n");
      }
    }
    elseif(isset($resultat) && !isset($_SESSION["gestion_choix"])) // élément choisi, on récupère les infos actuelles
    {
      if(isset($_GET["a"]) || isset($_SESSION["ajout"]))
      {
        if(!isset($intitule)) // un seul test devrait suffire
        {
          $current_intitule=$current_para="";
          $current_vap=-1;
          $_SESSION["nb_choix_min"]=$_SESSION["nb_choix_max"]=$current_type=0;
        }
      }
      else
      {
        $result=db_query($dbr,"SELECT $_DBC_dossiers_elems_intitule, $_DBC_dossiers_elems_para, $_DBC_dossiers_elems_vap,
                            $_DBC_dossiers_elems_type, $_DBC_dossiers_elems_unique, $_DBC_dossiers_elems_obligatoire,
                            $_DBC_dossiers_elems_recapitulatif, $_DBC_dossiers_elems_nb_choix_min,
                            $_DBC_dossiers_elems_nb_choix_max, $_DBC_dossiers_elems_nouvelle_page, 
                            $_DBC_dossiers_elems_extractions
                          FROM $_DB_dossiers_elems
                        WHERE $_DBC_dossiers_elems_id='$_SESSION[element_id]'");

        list($current_intitule, $current_para, $current_vap, $current_type, $current_element_unique,
            $current_element_obligatoire, $current_element_recapitulatif, $_SESSION["nb_choix_min"],
            $_SESSION["nb_choix_max"], $current_element_nouvelle_page, $current_element_extractions)=db_fetch_row($result,0);

        db_free_result($result);
      }

      if(isset($_SESSION["element_id"]))
        message("<center>Attention : la modification d'un élément sera valable
              <br>pour toutes les formations rattachées à ce dernier !</center>", $__WARNING);
  ?>
    <table align='center'>
    <tr>
      <td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
        <font class='Texte_menu2'>
          <b>&#8226;&nbsp;&nbsp;Données de l'élément</b>
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu2'>
        <font class='Texte_menu2'><b>Type d'élément sélectionné :</b></font>
      </td>
      <td class='td-droite fond_menu'>
        <font class='Texte_menu'>
          <strong>
          <?php
            switch($_SESSION["current_element_type"])
            {
              case $__ELEM_TYPE_FORM : print("Formulaire standard (réponse de type texte simple)\n");
                              break;

              case $__ELEM_TYPE_UN_CHOIX :  print("Un seul choix parmi ... <br>(l'étape suivante consistera à définir les choix possibles)\n");
                                  break;

              case $__ELEM_TYPE_MULTI_CHOIX :  print("Plusieurs choix possibles parmi ... <br>(l'étape suivante consistera à définir les choix possibles)\n");
                                    break;
            }
          ?>
          </strong>
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu2'>
        <font class='Texte_menu2'><b>Intitulé</b><br><i>(Non visible par le candidat)</i></font>
      </td>
      <td class='td-droite fond_menu'>
        <input type='text' name='intitule' value='<?php if(isset($element_intitule)) echo htmlspecialchars(stripslashes($element_intitule), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); else echo htmlspecialchars(stripslashes($current_intitule), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE);?>' maxlength='256' size='60'>
        <br><font class='Texte_menu'><i>Exemples : "Connaissances en Cuisine" ou "Titulaire d'un Master 1" ...</i></font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu2'>
        <font class='Texte_menu2'><b>Texte de la demande<br>faite au candidat</b></font>
      </td>
      <td class='td-droite fond_menu'>
        <font class='Texte_menu'>
          En cas de liste à choix, n'indiquez pas les réponses possibles dans ce champ (étape suivante).
          <br>
        </font>
        <textarea name='paragraphe' rows='10' cols='100'><?php
          if(isset($element_para)) echo htmlspecialchars(stripslashes($element_para), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE); else echo htmlspecialchars(stripslashes($current_para), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE);
        ?></textarea>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu2'>
        <font class='Texte_menu2'><b>Cet élément est-il obligatoire ? </b><br><i>Un élément obligatoire non complété par un candidat<br>empêche le verrouillage de sa fiche</i></font>
      </td>
      <td class='td-droite fond_menu'>
        <font class='Texte_menu'>
          <?php
            if(isset($element_obligatoire))
              $obligatoire=$element_obligatoire;
            elseif(isset($current_element_obligatoire))
              $obligatoire=$current_element_obligatoire;
            else
              $obligatoire="t";

            if($obligatoire=="f")
            {
              $no_checked="checked";
              $yes_checked="";
            }
            elseif($obligatoire=="t")
            {
              $yes_checked="checked";
              $no_checked="";
            }

            print("<input type='radio' name='element_obligatoire' value='t' $yes_checked>&nbsp;Oui
                  &nbsp;&nbsp;<input type='radio' name='element_obligatoire' value='f' $no_checked>&nbsp;Non\n");
          ?>
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu2'>
        <font class='Texte_menu2'><b>Cet élément doit-il être demandé : </b></font>
      </td>
      <td class='td-droite fond_menu' style='white-space:normal;'>
        <font class='Texte_menu'>
          <?php
            // Ce paramètre ne peut être modifié que s'il est absent de la table $_DB_dossiers_elements_contenu

            if(isset($element_unique))
              $unique=$element_unique;
            elseif(isset($current_element_unique))
              $unique=$current_element_unique;
            else
              $unique="t";

            if($unique=="f")
            {
              $statut="Pour chaque formation reliée à cet élément";
              $chaque_checked="checked";
              $unique_checked="";
            }
            elseif($unique=="t")
            {
              $statut="Une seule fois";
              $unique_checked="checked";
              $chaque_checked="";
            }

            if(!isset($_SESSION["element_id"]) || (isset($_SESSION["element_id"]) && !db_num_rows(db_query($dbr, "SELECT * FROM $_DB_dossiers_elems_contenu WHERE $_DBC_dossiers_elems_contenu_elem_id='$_SESSION[element_id]'"))))
              print("<input type='radio' name='element_unique' value='t' $unique_checked>&nbsp;Une seule fois
                    <br><input type='radio' name='element_unique' value='f' $chaque_checked>&nbsp;Pour chaque formation reliée à cet élément\n");
            else
              print("<b>$statut</b><br>(<i>cette option ne peut pas être modifiée manuellement car cet élément est utilisé par certains candidats</i>)
                      <input type='hidden' name='element_unique' value='$unique'>\n");
          ?>
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu2'>
        <font class='Texte_menu2'><b>La réponse du candidat doit-elle figurer sur le récapitulatif de sa fiche (PDF) ?</b></font>
      </td>
      <td class='td-droite fond_menu'>
        <font class='Texte_menu'>
          <?php
            if(isset($element_recapitulatif))
              $recapitulatif=$element_recapitulatif;
            elseif(isset($current_element_recapitulatif))
              $recapitulatif=$current_element_recapitulatif;
            else
              $recapitulatif="t";

            if($recapitulatif=="f")
            {
              $no_checked="checked";
              $yes_checked="";
            }
            elseif($recapitulatif=="t")
            {
              $yes_checked="checked";
              $no_checked="";
            }

            print("<input type='radio' name='element_recapitulatif' value='t' $yes_checked>&nbsp;Oui
                 &nbsp;&nbsp;<input type='radio' name='element_recapitulatif' value='f' $no_checked>&nbsp;Non\n");
          ?>
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu2'>
        <font class='Texte_menu2'><b>Si oui, la réponse doit-elle être imprimée sur une page à part ?</b></font>
      </td>
      <td class='td-droite fond_menu'>
        <font class='Texte_menu'>
          <?php
            if(isset($element_nouvelle_page))
              $nouvelle_page=$element_nouvelle_page;
            elseif(isset($current_element_nouvelle_page))
              $nouvelle_page=$current_element_nouvelle_page;
            else
              $nouvelle_page="f";

            if($nouvelle_page=="f")
            {
              $no_checked="checked";
              $yes_checked="";
            }
            elseif($nouvelle_page=="t")
            {
              $yes_checked="checked";
              $no_checked="";
            }

            print("<input type='radio' name='element_nouvelle_page' value='t' $yes_checked>&nbsp;Oui
                 &nbsp;&nbsp;<input type='radio' name='element_nouvelle_page' value='f' $no_checked>&nbsp;Non\n");
          ?>
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu2'>
        <font class='Texte_menu2'><b>La réponse du candidat doit-elle figurer dans les extractions CSV ?</b></font>
      </td>
      <td class='td-droite fond_menu'>
        <font class='Texte_menu'>
          <?php
            if(isset($element_extractions))
              $extractions=$element_extractions;
            elseif(isset($current_element_extractions))
              $extractions=$current_element_extractions;
            else
              $extractions="f";

            if($extractions=="f")
            {
              $no_checked="checked";
              $yes_checked="";
            }
            elseif($extractions=="t")
            {
              $yes_checked="checked";
              $no_checked="";
            }

            print("<input type='radio' name='element_extractions' value='t' $yes_checked>&nbsp;Oui
                 &nbsp;&nbsp;<input type='radio' name='element_extractions' value='f' $no_checked>&nbsp;Non\n");
          ?>
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu2'>
        <font class='Texte_menu2'><b>Cet élément est-il réservé à la VAP ?</b></font>
      </td>
      <td class='td-droite fond_menu'>
        <font class='Texte_menu'>
          <?php
            if(isset($element_vap))
              $vap=$element_vap;
            elseif(isset($current_vap))
              $vap=$current_vap;
            else
              $vap=-1;

            if($vap==0)
            {
              $yes_checked="";
              $ind_checked="";
              $no_checked="checked";
            }
            elseif($vap==1)
            {
              $yes_checked="checked";
              $ind_checked="";
              $no_checked="";
            }
            elseif($vap=="" || $vap==-1)
            {
              $yes_checked="";
              $ind_checked="checked";
              $no_checked="";
            }

            print("<input type='radio' name='vap' value='1' $yes_checked>&nbsp;Demander cet élément UNIQUEMENT pour les candidats en VAP/VAE
                  <br><input type='radio' name='vap' value='0' $no_checked>&nbsp;Ne PAS demander cet élément pour les candidats en VAP/VAE
                  <br><input type='radio' name='vap' value='-1' $ind_checked>&nbsp;Demander cet élément à tout le monde\n");
          ?>
        </font>
      </td>
    </tr>
    </table>

    <div class='centered_icons_box'>
      <?php
        print("<a href='element.php?r=1' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");
      ?>
      <a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
      <?php
        if($_SESSION["current_element_type"]==$__ELEM_TYPE_UN_CHOIX || $_SESSION["current_element_type"]==$__ELEM_TYPE_MULTI_CHOIX)
          print("<input type='image' class='icone' src='$__ICON_DIR/forward_32x32_fond.png' alt='Suivant' name='suivant_choix' value='Suivant'>\n");
        else
          print("<input type='image' class='icone' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='valider' value='Valider'>\n");
      ?>
      </form>
    </div>
    
    <?php
    }
    elseif(isset($_SESSION["gestion_choix"]))
    {
      message("<strong>Etape 3 : gestion des choix possibles pour le candidat</strong>", $__INFO);

      print("<div class='centered_box'>
            <a href='choix.php?a=1' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/add_22x22_fond.png' border='0' alt='Ajouter' desc='Ajouter' title='[Ajouter un choix]></a>
            <a href='choix.php?a=1' target='_self' class='lien2'>Ajouter un choix</a>
          </div>\n");

      
      $res_choix=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_id, $_DBC_dossiers_elems_choix_texte, $_DBC_dossiers_elems_choix_ordre
                          FROM $_DB_dossiers_elems_choix
                        WHERE $_DBC_dossiers_elems_choix_elem_id='$_SESSION[element_id]'
                          ORDER BY $_DBC_dossiers_elems_choix_ordre");

      $nb_choix=db_num_rows($res_choix);

      if($nb_choix)
      {
        // En cas de multi_choix, on demande les nombres min et max de choix

        print("<table align='center'>
            <tr>
              <td class='td-complet fond_menu2' colspan='5' style='padding:4px 20px 4px 20px;'>
                <font class='Texte_menu2'><strong>Choix existants</strong></font>
              </td>
            </tr>
            <tr>
              <td class='td-gauche fond_menu2' colspan='2'><font class='Texte_menu2'><strong>Ordre</strong></font></td>
              <td class='td-milieu fond_menu2'><font class='Texte_menu2'><strong>Textes vus par le candidat</strong></font></td>
              <td class='td-droite fond_menu2' colspan='2'></td>
            </tr>\n");


        for($i=0; $i<$nb_choix; $i++)
        {
          list($choix_id, $choix_texte, $choix_ordre)=db_fetch_row($res_choix, $i);

          if($choix_ordre<$nb_choix)
          {
            $crypt_params_down=crypt_params("cid=$choix_id&o=$choix_ordre&dir=0");
            $link_down="<a href='choix.php?p=$crypt_params_down' target='_self'><img src='$__ICON_DIR/down_16x16_menu.png' border='0' alt='Descendre' title='[Descendre]'></a>";
          }
          else
            $link_down="";

          if($choix_ordre>1)
          {
            $crypt_params_up=crypt_params("cid=$choix_id&o=$choix_ordre&dir=1");
            $link_up="<a href='choix.php?p=$crypt_params_up' target='_self'><img src='$__ICON_DIR/up_16x16_menu.png' border='0' alt='Descendre' title='[Descendre]'></a>";
          }
          else
            $link_up="";

          $sp=crypt_params("s=1&cid=$choix_id");
          $ep=crypt_params("e=1&cid=$choix_id");

          print("<tr>
                <td class='fond_menu' align='center' width='20'>$link_down</td>
                <td class='fond_menu' align='center' width='20'>$link_up</td>
                <td class='fond_menu'><font class='Texte_menu'>$choix_texte</font></td>
                <td class='fond_menu' align='center' width='20'><a href='choix.php?p=$ep' target='_self'><img src='$__ICON_DIR/edit_16x16_menu.png' border='0' alt='Modifier' title='[Modifier]'></a></td>
                <td class='fond_menu' align='center' width='20'><a href='choix.php?p=$sp' target='_self'><img src='$__ICON_DIR/trashcan_full_16x16_slick_menu.png' border='0' alt='Supprimer' title='[Supprimer]'></a></td>
              </tr>\n");
        }

        print("</table>\n");

        if($_SESSION["current_element_type"] == $__ELEM_TYPE_MULTI_CHOIX)
        {
          $liste_nb_choix_min=$liste_nb_choix_max="";

          for($i=0; $i<=$nb_choix; $i++)
          {
            $selected_min=(isset($_SESSION["nb_choix_min"]) && $_SESSION["nb_choix_min"]==$i) ? "selected" : "";
            $selected_max=(isset($_SESSION["nb_choix_max"]) && $_SESSION["nb_choix_max"]==$i) ? "selected" : "";

            $liste_nb_choix_min.="<option value='$i' $selected_min>$i</option>\n";
            $liste_nb_choix_max.="<option value='$i' $selected_max>$i</option>\n";
          }

          print("<table align='center' style='padding-top:15px;'>
              <tr>
                <td class='td-complet fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
                  <font class='Texte_menu2'><strong>Options</strong></font>
                </td>
              </tr>
              <tr>
                <td class='td-gauche fond_menu2'>
                  <font class='Texte_menu2'><b>Nombre minimum de choix qu'un candidat devra cocher :</b></font>
                </td>
                <td class='td-droite fond_menu'>
                  <select name='min_choix'>
                    $liste_nb_choix_min
                  </select>
                  <font class='Texte'>
                    <i>0 : pas de minimum
                    <br>(si l'élément est obligatoire, le minimum vaudra au moins 1)</i>
                  </font>
                </td>
              </tr>
              <tr>
                <td class='td-gauche fond_menu2'>
                  <font class='Texte_menu2'><b>Nombre maximum de choix qu'un candidat pourra cocher :</b></font>
                </td>
                <td class='td-droite fond_menu'>
                  <select name='max_choix'>
                    $liste_nb_choix_max
                  </select>
                  <font class='Texte'><i>0 : pas de maximum</i></font>
                </td>
              </tr>
              </table>\n");
        }
      }

      db_free_result($res_choix);

      print("<div class='centered_icons_box'>
            <a href='$php_self?r=2' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");

      if($_SESSION["current_element_type"]==$__ELEM_TYPE_MULTI_CHOIX)
        print("<input type='image' class='icone' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Terminer' name='terminer' value='Terminer'>\n");
      else
        print("<a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Retour' border='0'></a>\n");

      print(" </form>
          </div>\n");
    }

    db_close($dbr);
  ?>
</div>
<?php
  pied_de_page();
?>

</body></html>
