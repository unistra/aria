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

  // Nettoyages des variables inutiles
  unset($_SESSION["checked_message"]);
  unset($_SESSION["requete"]);

  // Fichier
  if(isset($_POST["ajouter"]) || isset($_POST["ajouter_x"]))
  {
    $contenu=trim($_POST["corps"]);
    $sujet=$_POST["sujet"];

    // informations liées au fichier envoyé
    $file_name=$_FILES["fichier"]["name"];
    $file_size=$_FILES["fichier"]["size"];
    $file_tmp_name=$_FILES["fichier"]["tmp_name"];
    $file_error=$_FILES["fichier"]["error"]; // PHP > 4.2.0 uniquement

    $realname=html_entity_decode(validate_filename(mb_convert_encoding("$file_name", "UTF-8", mb_detect_encoding($file_name))), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);

    if($file_size>2097152)
      $trop_gros=1;
    elseif($file_name=="none" || $file_name=="" || !is_uploaded_file($_FILES["fichier"]["tmp_name"]))
      $fichier_vide=1;
    else
    {
      if(!is_dir("$__ROOT_DIR/$__MOD_DIR/tmp/$_SESSION[auth_id]"))
        mkdir("$__ROOT_DIR/$__MOD_DIR/tmp/$_SESSION[auth_id]", 0770, 1);

      $destination_path="$__ROOT_DIR/$__MOD_DIR/tmp/$_SESSION[auth_id]/$file_name";

      $x=0;

      while(is_file("$destination_path")) // le fichier existe deja : on change le nom en ajoutant un numéro
      {
        $test_file_name=$x. "-$file_name";
        $destination_path="$__ROOT_DIR/$__MOD_DIR/tmp/$_SESSION[auth_id]/$test_file_name";

        $x++;
      }

      // DEBUG Uniquement
      // print("$file_tmp_name / $destination_path");

      if(!move_uploaded_file($file_tmp_name, $destination_path))
        $erreur_copie_fichier=1;
      else
      {
        $copie_ok=1;

        if(isset($test_file_name) && $test_file_name!="")
          $file_name=$test_file_name;

        if(isset($_SESSION["tmp_message_fichiers"]))
        {
          sort($_SESSION["tmp_message_fichiers"]);
          $cnt=count($_SESSION["tmp_message_fichiers"]);

          // Comparaison avec les fichiers déjà joints, pour éviter la duplication
          foreach($_SESSION["tmp_message_fichiers"] as $array_file)
          {
            if($array_file["sha1"]==sha1_file("$destination_path") && $array_file["size"]=="$file_size")
            {
              $dupe=1;
              break;
            }
          }
        }
        else
        {
          $_SESSION["tmp_message_fichiers"]=array();
          $cnt=0;
        }
        
        if(!isset($dupe))
          $_SESSION["tmp_message_fichiers"][$cnt]=array("file" => "$destination_path",
                                         "realname" => "$realname",
                                         "size" => "$file_size",
                                         "sha1" => sha1_file("$destination_path"));
      }
    }
  }
  elseif((isset($_POST["suppr"]) || isset($_POST["suppr_x"])) && isset($_SESSION["tmp_message_fichiers"]))
  {
    $contenu=trim($_POST["corps"]);
    $sujet=$_POST["sujet"];

    // Suppression d'une pièce jointe
    foreach($_POST["suppr"] as $file_num => $foo)
    {
      if(array_key_exists($file_num, $_SESSION["tmp_message_fichiers"]))
      {
        $filename=$_SESSION["tmp_message_fichiers"][$file_num]["file"];
        @unlink("$filename");
        unset($_SESSION["tmp_message_fichiers"][$file_num]);
      }
    }

    sort($_SESSION["tmp_message_fichiers"]);
  }

  // Précaution : vérification de la liste des destinataires
  if(!isset($_SESSION["mail_masse"]) || (isset($_SESSION["mail_masse"]) && !count($_SESSION["mail_masse"])))
  {
    header("Location:edit_candidature.php");
    exit();
  }

  $dbr=db_connect();

  if(isset($_POST["go"]) || isset($_POST["go_x"])) // validation du formulaire : envoi du message
  {
    // vérification des valeurs entrées dans le formulaire
    // TODO : vérifications poussées

    $contenu=trim($_POST["corps"]);
    $sujet=$_POST["sujet"];

    if(empty($contenu))
      $champ_vide=1;
    else
    {
      $corps=stripslashes("\n$contenu\n\n");
/*
      if(isset($_SESSION["tmp_message_fichiers"]))
      {
        $new_corps="Pièce(s) jointe(s) :<br>";

        foreach($_SESSION["tmp_message_fichiers"] as $array_file)
          $new_corps.="- <a href='files/$array_file[realname]' target='_blank' class='lien_bleu_12'>$array_file[realname]</a><br>";

        $array_pj=$_SESSION["tmp_message_fichiers"];
      }
      else
        $array_pj="";

      if(isset($new_corps))
        $corps=$new_corps . "<br>" . $corps;
*/
      if(isset($_SESSION["tmp_message_fichiers"]))
        $array_pj=$_SESSION["tmp_message_fichiers"];
      else
        $array_pj="";

      $count_sent=0;

      // envoi du mail à toute la liste
      /*
      foreach($_SESSION["mail_masse"] as $mail_candidat_id => $mail_candidat_array)
      {
        $array_dest=array("0" => array("id"   => $mail_candidat_id,
                             "civ"  => $mail_candidat_array['civ'],
                             "nom"  => $mail_candidat_array['nom'],
                             "prenom"=> $mail_candidat_array['prenom'],
                             "email"  => $mail_candidat_array['courriel']));

        write_msg("", array("id" => $_SESSION["auth_id"], "nom" => $_SESSION["auth_nom"], "prenom" => $_SESSION["auth_prenom"]),
               $array_dest, $sujet, $corps, "$mail_candidat_array[nom] $mail_candidat_array[prenom]", 
               $__FLAG_MSG_NOTIFICATION, $array_pj);

        $count_sent++;
      }
      */
      $dests_array=array();

       foreach($_SESSION["mail_masse"] as $mail_candidat_id => $mail_candidat_array)
         {
            $dests_array["$mail_candidat_id"] = array("id"    => $mail_candidat_id,
                                                      "civ"   => $mail_candidat_array['civ'],
                                                      "nom"   => $mail_candidat_array['nom'],   
                                                      "prenom"=> $mail_candidat_array['prenom'],
                                                      "email" => $mail_candidat_array['courriel']);
         }

         if(count($dests_array))
            $count_sent=write_msg("", array("id" => $_SESSION["auth_id"], "nom" => $_SESSION["auth_nom"], "prenom" => $_SESSION["auth_prenom"]),
                                  $dests_array, $sujet, $corps, "", $__FLAG_MSG_NOTIFICATION, $array_pj);
         else
            $count_sent=0;
      

      if(isset($_SESSION["tmp_message_fichiers"]))
      {
        foreach($_SESSION["tmp_message_fichiers"] as $array_file)
          @unlink("$array_file[file]");

        unset($_SESSION["tmp_message_fichiers"]);
      }

      // Nettoyage puis redirection
      unset($_SESSION["requete"]);
      unset($_SESSION["mail_masse"]);
      unset($_SESSION["checked_message"]);

      header("Location:recherche.php?s=$count_sent");
      exit();
    }
  }

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    titre_page_icone("Envoyer un message à plusieurs candidats (messagerie interne)", "mail_forward_32x32_fond.png", 2, "L");

    // POUR TESTS UNIQUEMENT
/*
    if(isset($file_name))
    {
      $converted=mb_convert_encoding($file_name, mb_internal_encoding(), 'auto');
      // $converted=utf8_decode($file_name);
      print("$converted\n<br>");

      $reconverted=iconv("UTF-8","LATIN1","PiÃ¨ces Ã  joindre.doc");
      print("$reconverted\n<br>");

      print("A/ [$file_name] B/ [PiÃ¨ces Ã  joindre.doc]<br>\n");

      if($file_name==="PiÃ¨ces Ã  joindre.doc")
        echo "Egales";
      else
        echo "NON";
    }
*/
    print("<form enctype='multipart/form-data' action='$php_self' method='POST' name='form1'>");

    message("<center>
            <b>Attention : soyez très vigilent(e) lorsque vous envoyez un message de masse !</b>
            <br>L'envoi peut prendre plusieurs minutes s'il y a beaucoup de destinataires.
          </center>", $__WARNING);

    if(isset($erreur_copie_fichier))
      message("Erreur : impossible de copier le fichier reçu. Merci de contacter l'administrateur.", $__ERREUR);

    if(isset($trop_gros))
      message("Erreur : le fichier envoyé est trop gros (max : 2 Mo)", $__ERREUR);

  ?>

  <table align='center'>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><b>Sujet :</b></font>
    </td>
    <td class='td-droite fond_menu2'>
      <input type='text' name='sujet' value='<?php if(isset($sujet)) echo htmlspecialchars(stripslashes($sujet), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?>' size='80' maxlength='140'>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2' valign='top'>
      <font class='Texte_menu2'><b>Joindre un fichier :</b></font>
    </td>
    <td class='td-droite fond_menu2' valign='top'>
      <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
      <input type='file' name='fichier' size='48'>
      &nbsp;&nbsp;<input type='submit' name='ajouter' value='Ajouter ce fichier'>
      &nbsp;&nbsp;<font class='Texte_menu2'>(<i>Limite : 2 Mo</i>)</font>
    </td>
  </tr>
  <?php
    if(isset($_SESSION["tmp_message_fichiers"]) && count($_SESSION["tmp_message_fichiers"]))
    {
      sort($_SESSION["tmp_message_fichiers"]);

      print("<tr>
            <td class='td-gauche fond_menu2'>
              <font class='Texte_menu2'><b>Pièces jointes :</b></font>
            </td>
            <td class='td-droite fond_menu2'>
              <table cellpadding='0' cellspacing='0' align='left' border='0'>\n");

      foreach($_SESSION["tmp_message_fichiers"] as $num_file => $array_file)
        print("<tr>
              <td class='td-gauche fond_menu2'>
                <font class='Texte_menu2'>&#8226;&nbsp;&nbsp;$array_file[realname]</font>
              </td>
              <td class='td-droite fond_menu2' style='padding-left:15px;'>
                <input type='image' src='$__ICON_DIR/trashcan_full_16x16_slick_menu2.png' alt='Supprimer' border='0' name='suppr[$num_file]' value='Supprimer'>
              </td>
            </tr>\n");

      print("</table>
          </td>
        </tr>\n");
    }
  ?>
  <tr>
    <td class='td-msg fond_blanc' style='vertical-align:top;' colspan='2'>
      <textarea name='corps' class='textArea' rows='12'><?php if(isset($contenu)) echo htmlspecialchars(stripslashes($contenu),ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]); ?></textarea>
    </td>
  </tr>
  </table>

  <br>

  <?php
    if(isset($champ_vide))
      message("Formulaire incomplet: vous ne pouvez pas envoyer un message vide", $__ERREUR);

    message("Lors de l'envoi, une copie sera automatiquement placée dans votre dossier \"Envoyés\" de la messagerie interne", $__INFO);
  ?>

  <div class='centered_icons_box'>
    <a href='<?php if(isset($_SESSION["from"])) echo $_SESSION["from"]; else echo "recherche_generale.php"; ?>' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' alt='Retour' border='0'></a>
    <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="go" value="Valider">
    </form>
  </div>

  <?php
    if(isset($_SESSION["mail_masse"]) && count($_SESSION["mail_masse"]))
    {
      print("<table cellpadding='4' cellspacing='0' align='center' width='90%'>
          <tr>
            <td align='left'>
              <font class='Texte'>
                Le message sera envoyé aux personnes suivantes :
              </font>
            </td>
          </tr>
          <tr>
          <td align='left'>
            <font class='Texte'>");

      foreach($_SESSION["mail_masse"] as $mail_candidat_id => $mail_candidat_array)
      {
        if(!strstr($mail_candidat_array["courriel"], "@"))
          $mail_candidat_array["courriel"]="<b>Fiche Manuelle</b>";

        print("$mail_candidat_array[civ] $mail_candidat_array[nom] $mail_candidat_array[prenom] - $mail_candidat_array[courriel]<br>\n");

      }
      print("</font>
            </td>
          </tr>
          </table>\n");
    }

  ?>
</div>
<?php
  pied_de_page();
?>
</body>
</html>

