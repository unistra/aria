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
  // Vérifications complémentaires au cas où ce fichier serait appelé directement
  verif_auth();

  if(!isset($_SESSION["candidat_id"]) || !isset($_SESSION["niveau"]) || $_SESSION["niveau"]!=$__LVL_ADMIN)
  {
    header("Location:index.php");
    exit;
  }

  if(isset($_GET["dossier"]) && ctype_digit($_GET["dossier"]) && array_key_exists($_GET["dossier"], $__MSG_DOSSIERS))
    $current_dossier=$_SESSION["current_dossier"]=$_GET["dossier"];

  // Offset
  if(isset($_GET["offset"]) && ctype_digit($_GET["offset"]))
    $offset=$_GET["offset"];
  elseif(isset($_SESSION["msg_offset"]))
    $offset=$_SESSION["msg_offset"];

  print("<div class='centered_box'>
        <font class='Texte_16'><strong>$_SESSION[onglet] - Messagerie du candidat</strong></font>
       </div>\n");

  message("<center>Attention : ces données sont <u>confidentielles</u> et sont <strong>EXCLUSIVEMENT</strong> destinées à l'administrateur.
             <br />Soyez <strong>extrêmement prudent(e)</strong> lorsque vous manipulez ces informations.
        </center>", $__WARNING);

  if(!isset($_SESSION["current_dossier"]))
    $current_dossier=$_SESSION["current_dossier"]=$__MSG_INBOX;
  else
    $current_dossier=$_SESSION["current_dossier"];

  // AFFICHAGE D'UN MESSAGE

  if(isset($_GET["p"]) && -1!=($params=get_params($_GET['p']))) // chemin complet du message, chiffré
  {
    if(isset($params["dir"]) && $params["dir"]==1)
      $flag_pj=1;

    if(isset($params["msg"]))
    {
      $fichier=$params["msg"];

      // Test d'ouverture du fichier
      if(($array_file=@file("$fichier"))==FALSE)
      {
        // On tente en modifiant la fin du nom du fichier (flag read)
        if(mb_substr($fichier, -1, NULL, "UTF-8")=="0")
          $fichier=preg_replace("/\.0$/", ".1", $fichier);
        else
          $fichier=preg_replace("/\.1$/", ".0", $fichier);

        if(($array_file=@file("$fichier"))==FALSE)
          $location="index.php";
      }

      if(!isset($location))
      {
        // Nom du fichier sans le répertoire
        $complete_path=explode("/", $fichier);
        $rang_fichier=count($complete_path)-1;
        
        // Nom du fichier
        $_SESSION["msg"]=$complete_path[$rang_fichier];
        
        // Répertoire
        unset($complete_path[$rang_fichier]);
        $_SESSION["msg_dir"]=implode("/", $complete_path);

        if(strlen($_SESSION["msg"])==18) // Année sur un caractère (16 pour l'identifiant + ".0" ou ".1" pour le flag "read")
        {
          $date_offset=0;
          $annee_len=1;
          $leading_zero="0";
          $_SESSION["msg_id"]=$msg_id=mb_substr($_SESSION["msg"], 0, 16, "UTF-8");
          $msg_read=mb_substr($_SESSION["msg"], 17, 1, "UTF-8");
        }
        else // Année sur 2 caractères (chaine : 19 caractères)
        {
          $date_offset=1;
          $annee_len=2;
          $leading_zero="";
          $_SESSION["msg_id"]=$msg_id=mb_substr($_SESSION["msg"], 0, 17, "UTF-8");
          $msg_read=mb_substr($_SESSION["msg"], 18, 1, "UTF-8");
        }

        $_SESSION['msg_exp_id']=trim($array_file["0"]);
        $_SESSION['msg_exp']=trim($array_file["1"]);
        $_SESSION['msg_to_id']=trim($array_file["2"]);
        $_SESSION['msg_to']=trim($array_file["3"]);
        $_SESSION['msg_sujet']=stripslashes(trim($array_file["4"]));

        $_SESSION['msg_message']=array_slice($array_file, 5);
        $_SESSION['msg_message_txt']=stripslashes(implode($_SESSION['msg_message']));
      }

      $date_today=date("ymd") . "00000000000"; // on s'aligne sur le format des identifiants

      // Identifiant du message = date
      // Format : AA(1 ou 2) MM JJ HH Mn SS µS(5)

      if(strlen($_SESSION["msg_id"])==16) // Année sur un caractère
      {
        $date_offset=0;
        $annee_len=1;
        $leading_zero="0";
      }
      else
      {
        $date_offset=1;
        $annee_len=2;
        $leading_zero="";
      }

      // On convertit la date en temps Unix : plus simple ensuite pour l'affichage et les conversions
      $unix_date=mktime(mb_substr($_SESSION["msg_id"], 5+$date_offset, 2, "UTF-8"), 
            mb_substr($_SESSION["msg_id"], 7+$date_offset, 2, "UTF-8"), 
            mb_substr($_SESSION["msg_id"], 9+$date_offset, 2, "UTF-8"),
            mb_substr($_SESSION["msg_id"], 1+$date_offset, 2, "UTF-8"),
            mb_substr($_SESSION["msg_id"], 3+$date_offset, 2, "UTF-8"), 
            $leading_zero . mb_substr($_SESSION["msg_id"], 0, $annee_len, "UTF-8"));

      $date_txt=ucfirst(date_fr("l d F Y - H\hi", $unix_date));

      $crypt_params_to=crypt_params("to=$_SESSION[msg_exp_id]&r=1");
      $crypt_params_suppr=crypt_params("msg=$_SESSION[msg_id]");
      // $crypt_params=crypt_params("msg=$_SESSION[msg_id]");

      print("<table class='encadre_messagerie' width='95%' align='center'>
            <tr>
              <td class='td-msg-titre fond_menu' style='padding:4px 2px 4px 2px;'>
                <a href='$php_self' class='lien_menu_gauche' style='font-size:14px;'><b>$__MSG_DOSSIERS[$current_dossier]</b>
                <font class='Texte_menu'><b> : message de $_SESSION[msg_exp]</b></font>
              </td>
            </tr>
            <tr>
              <td class='td-msg-menu fond_menu2' style='white-space:normal; padding:4px 2px 4px 2px;'>
                <font class='Texte_menu2'>
                  <b>$date_txt - Sujet : $_SESSION[msg_sujet]</b>
                </font>
              </td>
            </tr>
            <tr>
              <td class='td-msg fond_page' style='white-space:normal; vertical-align:top' height='400'>
                <font class='Texte'><br>\n");

      // Pièces jointes ?
      if(isset($flag_pj) && $flag_pj==1 && is_dir("$_SESSION[msg_dir]/files"))
      {
        $array_pj=scandir("$_SESSION[msg_dir]/files");
        
        // 4 éléments à ne pas inclure dans la recherche : ".", "..", le message et "index.php"

        if(FALSE!==($key=array_search("$_SESSION[msg]", $array_pj)))
          unset($array_pj[$key]);

        if(FALSE!==($key=array_search(".", $array_pj)))
          unset($array_pj[$key]);

        if(FALSE!==($key=array_search("..", $array_pj)))
          unset($array_pj[$key]);

        if(FALSE!==($key=array_search("index.php", $array_pj)))
          unset($array_pj[$key]);
        // **************** //

        if(count($array_pj))
          print("Pièce(s) jointe(s) : <br>\n");

            $sous_rep=sous_rep_msg($_SESSION["candidat_id"]);

        foreach($array_pj as $pj_name)
        {
          $crypt_params_pj=crypt_params("pj=$pj_name");
          
          print("- <a href='$GLOBALS[__CAND_MSG_STOCKAGE_DIR]/$sous_rep/$_SESSION[candidat_id]/$_SESSION[current_dossier]/$_SESSION[msg_id]/files/$pj_name' class='lien_bleu_12' target='_blank'>$pj_name</a><br>\n");
        }
      }

      $clean_msg_txt=preg_replace("/<a [^>]*>/","", $_SESSION["msg_message_txt"]);
      $clean_msg_txt=preg_replace("/<\/a>/","", $clean_msg_txt);

      print(nl2br(parse_macros($clean_msg_txt)) . "</font>
              </td>
            </tr>
            </table>
            <br><br>\n");
    }
    else
      $affichage_liste=1;
  }

  // Si on n'affiche pas le contenu d'un message, on affiche la liste des messages disponibles
  if(isset($affichage_liste) || !isset($_GET["p"]))
  {
     // sous répertoire
     $sous_rep=sous_rep_msg($_SESSION["candidat_id"]);

      if(!is_dir("$__CAND_MSG_STOCKAGE_DIR_ABS/$sous_rep/$_SESSION[candidat_id]/$current_dossier"))
         mkdir("$__CAND_MSG_STOCKAGE_DIR_ABS/$sous_rep/$_SESSION[candidat_id]/$current_dossier", "0770", TRUE);
          
    $contenu_repertoire  = scandir("$__CAND_MSG_STOCKAGE_DIR_ABS/$sous_rep/$_SESSION[candidat_id]/$current_dossier", 1);

    if(FALSE!==($key=array_search(".", $contenu_repertoire)))
      unset($contenu_repertoire[$key]);

    if(FALSE!==($key=array_search("..", $contenu_repertoire)))
      unset($contenu_repertoire[$key]);

    if(FALSE!==($key=array_search("index.php", $contenu_repertoire)))
      unset($contenu_repertoire[$key]);

    rsort($contenu_repertoire);
    $nb_msg=$nb_fichiers=count($contenu_repertoire);

    if($nb_msg==1)
      $nb_msg_texte="1 message";
    elseif($nb_msg==0)
      $nb_msg_texte="Aucun message";
    else
      $nb_msg_texte="$nb_msg messages";

    if(!isset($offset) || !ctype_digit($offset) || $offset>$nb_msg)
      $offset=0;

    $_SESSION["msg_offset"]=$offset;

    // Calcul des numéros de messages et de la présence/absence de flèches pour aller à la page suivante/précédente
    if($_SESSION["msg_offset"]>0)  // lien vers la page précédente
    {
      $prev_offset=$_SESSION["msg_offset"]-20;

      $prev_offset=$prev_offset < 0 ? 0 : $prev_offset;

      $prev="<a href='$php_self?offset=$prev_offset'><img src='$__ICON_DIR/back_16x16_menu.png' border='0'></a>";
      $prev_txt="[$prev_offset - $_SESSION[msg_offset]] ";

      $limite_inf_msg=$_SESSION["msg_offset"];
    }
    else
    {
      $prev=$prev_txt="";
      $limite_inf_msg=0;
    }

    if(($_SESSION["msg_offset"]+20)<$nb_msg) // encore des messages
    {
      // texte affiché
      if(($_SESSION["msg_offset"]+40)<$nb_msg)
        $limite_texte=$_SESSION["msg_offset"]+40;
      else
        $limite_texte=$nb_msg;

      // Offset suivant et limite de la boucle for() suivante
      $limite_sup_msg=$next_offset=$_SESSION["msg_offset"]+20;

      $next="<a href='$php_self?offset=$next_offset'><img src='$__ICON_DIR/forward_16x16_menu.png' border='0'></a>";
      $next_txt="[$next_offset - $limite_texte]";
    }
    else
    {
      $next=$next_txt="";

      // Pour la limite de la boucle for() suivante
      $limite_sup_msg=$nb_msg;
    }

    // changement de nom pour la colonne "Expéditeur" si le dossier est "Envoyés"
    if($current_dossier==$__MSG_SENT)
      $col_name="Destinataire";
    else
      $col_name="Expéditeur";

    print("<form action='$php_self' method='POST' name='form1'>
          <table class='encadre_messagerie' width='95%' align='center'>
          <tr>
            <td class='td-msg-titre fond_page' style='border-right:0px; padding:1px 2px 1px 2px;' colspan='2'>
              <font class='Texte_menu'>
                <a href='$php_self' class='lien_menu_gauche'>Rafraîchir</a>
              </font>
            </td>
            <td class='td-msg-titre fond_page' style='vertical-align:middle; text-align:right; border-left:0px; padding:1px 2px 1px 2px;' colspan='2'>
              <table cellpadding='0' cellspacing='0' align='right'>
              <tr>
                <td class='fond_menu' style='padding-right:4px;'><font class='Texte_menu'>$prev_txt</font></td>
                <td class='fond_menu' style='padding-right:4px;''>$prev</td>
                <td class='fond_menu' style='padding-left:4px;''>$next</td>
                <td class='fond_menu' style='padding-left:4px;'><font class='Texte_menu'>$next_txt</font></td>
              </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td class='td-msg-titre fond page' style='border-right:0px; border-left:0px; height:10px;' colspan='4'></td>
          <tr>
            <td class='td-msg-titre fond_menu' style='padding:4px 2px 4px 2px;' colspan='4'>
              <font class='Texte_menu'>
                <b>$__MSG_DOSSIERS[$current_dossier]</b> ($nb_msg_texte)
              </font>
            </td>
          </tr>
          <tr>
            <td class='td-msg-menu fond_gris_E' style='padding:4px 2px 4px 2px;' width='10%'><font class='Texte_menu'><b>Date</b></font></td>
            <td class='td-msg-menu fond_gris_E' style='padding:4px 2px 4px 2px;' width='30%'><font class='Texte_menu'><b>$col_name</b></font></td>
            <td class='td-msg-menu fond_gris_E' style='padding:4px 2px 4px 2px;' width='60%'><font class='Texte_menu'><b>Sujet</b></font></td>
          </tr>\n");

    for($i=$limite_inf_msg; $i<$limite_sup_msg; $i++)
    {
      // TODO : ajouter tests de retour des fonctions
      // $fichier=$_SESSION["repertoire"] . "/" . $contenu_repertoire[$i];

         // sous répertoire
         $sous_rep=sous_rep_msg($_SESSION["candidat_id"]);

      $fichier="$__CAND_MSG_STOCKAGE_DIR_ABS/$sous_rep/$_SESSION[candidat_id]/$current_dossier/" . $contenu_repertoire[$i];
      $nom_fichier=$contenu_repertoire[$i];

      if(is_dir($fichier)) // Répertoire : message avec pièce(s) jointe(s)
      {
        // On regarde le contenu du répertoire. Normalement, le message a le même nom que ce dernier, terminé par .0 ou .1
        if(is_file("$fichier/$nom_fichier.0"))
        {
          $fichier.="/$nom_fichier.0";
          $nom_fichier="$nom_fichier.0";
        }
        elseif(is_file("$fichier/$nom_fichier.1"))
        {
          $fichier.="/$nom_fichier.1";
          $nom_fichier="$nom_fichier.1";
        }

        $crypt_params=crypt_params("dir=1&msg=$fichier");
      }
      else
        $crypt_params=crypt_params("msg=$fichier");

      // Identifiant du message = date
      // Format : AA(1 ou 2) MM JJ HH Mn SS µS(5)

      if(strlen($nom_fichier)==18) // Année sur un caractère (16 pour l'identifiant + ".0" ou ".1" pour le flag "read")
      {
        $date_offset=0;
        $annee_len=1;
        $leading_zero="0";
        $msg_id=mb_substr($nom_fichier, 0, 16, "UTF-8");
        $msg_read=mb_substr($nom_fichier, 17, 1, "UTF-8");
      }
      else // Année sur 2 caractères (chaine : 19 caractères)
      {
        $date_offset=1;
        $annee_len=2;
        $leading_zero="";
        $msg_id=mb_substr($nom_fichier, 0, 17, "UTF-8");
        $msg_read=mb_substr($nom_fichier, 18, 1, "UTF-8");
      }

      if(($array_file=file("$fichier"))==FALSE)
      {
        mail($__EMAIL_ADMIN, "[Précandidatures] - Erreur d'ouverture de mail", "Fichier : $fichier\n\nUtilisateur : $_SESSION[auth_prenom] $_SESSION[auth_nom]");

        die("Erreur d'ouverture du fichier. Un message a été envoyé à l'administrateur.");
      }

      $msg_exp_id=$array_file["0"];
      $msg_exp=$array_file["1"];
      $msg_to_id=$array_file["2"];
      $msg_to=$array_file["3"];
      $msg_sujet=stripslashes($array_file["4"]);

      $date_today=date("ymd") . "00000000000"; // on s'aligne sur le format des identifiants

      // On convertit la date en temps Unix : plus simple ensuite pour l'affichage et les conversions
      $unix_date=mktime(mb_substr($msg_id, 5+$date_offset, 2, "UTF-8"), 
            mb_substr($msg_id, 7+$date_offset, 2, "UTF-8"),
            mb_substr($msg_id, 9+$date_offset, 2, "UTF-8"),
            mb_substr($msg_id, 1+$date_offset, 2, "UTF-8"), 
            mb_substr($msg_id, 3+$date_offset, 2, "UTF-8"), 
            $leading_zero . mb_substr($msg_id, 0, $annee_len, "UTF-8"));

      if($msg_id<$date_today) // le message n'est pas du jour : on affiche la date entière (date + heure)
        $date_txt=date_fr("d/m/y - H\hi", $unix_date);
      else // message du jour : on n'affiche que l'heure
        $date_txt=date_fr("H\hi", $unix_date);

      if(!$msg_read)
      {
        $style_bold="style='font-weight:bold;'";
        $style_bg="fond_gris_E";
      }
      else
      {
        $style_bold="";
        $style_bg="fond_gris_E";
      }

      $col_value=$current_dossier==$__MSG_SENT ? $msg_to : $msg_exp;

      print("<tr>
            <td class='td-msg fond_gris_E' style='text-align:left;' width='10%'>
              <font class='Texte' $style_bold>$date_txt</font>
            </td>
            <td class='td-msg fond_gris_E' style='text-align:left;' width='30%'>
              <font class='Texte' $style_bold>
                <a href='edit_candidature.php?p=$crypt_params' class='lien_bleu_12'>$col_value</a>
              </font>
            </td>
            <td class='td-msg fond_gris_E' style='text-align:left;' width='55%' $style_bold>
              <a href='edit_candidature.php?p=$crypt_params' class='lien_bleu_12' $style_bold>$msg_sujet</a>
            </td>
          </tr>\n");
    }

    print("</table>
          <br><br>");
  }
?>
