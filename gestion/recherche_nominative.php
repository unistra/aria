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

  unset($_SESSION["mails_masse"]);

  verif_auth();

  $dbr=db_connect();

  // Déverrouillage, au cas où
  if(isset($_SESSION["candidat_id"]))
    cand_unlock($dbr, $_SESSION["candidat_id"]);

  if(isset($_POST["recherche"]) || isset($_POST["recherche_x"]))
  {
    if(array_key_exists("selection", $_POST) && ($_POST["selection"]==0 || $_POST["selection"]==1))
    {
      if($_POST["selection"]==1)
        $_SESSION["checked_message"]=$checked_message="checked='1'";
      else
        $_SESSION["checked_message"]=$checked_message="";
    }
    else
      $_SESSION["checked_message"]=$checked_message="checked='1'";

    $nom=mb_strtolower(trim($_POST["nom"]), "UTF-8");
    $rechmail=$_POST["email"];

    // Nettoyage de la chaine de caractères pour la requête à la base de données
    // caractères à traiter : à á â ã ä å  ç  è é ê ë  ì í î ï  ñ  ð ò ó ô õ ö  ù ú û ü  ý ÿ *
    $nom=clean_str_requete($nom);

    // les noms sont stockés en majuscule dans la base, on doit faire la conversion ici, sinon les majuscules accentuées ne sont pas trouvées avec ILIKE
    $nom=mb_strtoupper($nom, "UTF-8");

    if((empty($rechmail) || $rechmail=="") && (empty($nom) || $nom==""))
      $champs_vides=1;
    else
    {
      if($nom=="")
        $critere_recherche="$_DBC_candidat_email ILIKE '%$rechmail%' ";
      elseif($rechmail=="")
        $critere_recherche="unaccent($_DBC_candidat_nom) SIMILAR TO unaccent('$nom%') ";
      else
        $critere_recherche="(unaccent($_DBC_candidat_nom) SIMILAR TO unaccent('$nom%') OR $_DBC_candidat_email ILIKE '%$rechmail%') ";

      if(empty($rechmail) && !preg_match("/([a-zA-Z\'\ \-]+)/", $nom))
        $bad_rech=1;
      else
      {
        if($_SESSION["niveau"]==$__LVL_ADMIN || $_SESSION["niveau"]==$__LVL_SUPPORT)
        {
          $requete=$_SESSION["requete"]="SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_nom_naissance, $_DBC_candidat_prenom,
                                    $_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance, 
                                    CASE WHEN $_DBC_candidat_nationalite IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite) 
                                        THEN (SELECT $_DBC_pays_nat_ii_nat FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite)
                                        ELSE '' END as nationalite,
                                    $_DBC_candidat_email, $_DBC_candidat_dernier_user_agent, $_DBC_candidat_dernier_host,
                                    $_DBC_candidat_derniere_ip, $_DBC_candidat_identifiant, $_DBC_candidat_code_acces,
                                    $_DBC_candidat_connexion, $_DBC_candidat_manuelle
                                FROM $_DB_candidat
                              WHERE $critere_recherche
                              ORDER BY $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance";
        }
        else
          $requete=$_SESSION["requete"]="SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_nom_naissance, $_DBC_candidat_prenom,
                                    $_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance, 
                                    CASE WHEN $_DBC_candidat_nationalite IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite) 
                                        THEN (SELECT $_DBC_pays_nat_ii_nat FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite)
                                        ELSE '' END as nationalite,
                                    $_DBC_candidat_email, $_DBC_candidat_manuelle
                                FROM $_DB_candidat
                              WHERE $critere_recherche
                              ORDER BY $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance";

        $result=db_query($dbr, $requete);
        $rows=db_num_rows($result);
        $nb_trouves=$rows;
      }
    }
  }

  // Message de masse - Récupération des adresses destination et vérification de la liste
  if((isset($_POST["courriels_masse"]) || isset($_POST["courriels_masse_x"])) && isset($_POST["liste"]) && $_POST["liste"]==1 && isset($_SESSION["mail_masse"]))
  {
    foreach($_SESSION["mail_masse"] as $mail_candidat_id => $mail_candidat_array)
    {
      if(!isset($_POST["selectmail_$mail_candidat_id"]))
        unset($_SESSION["mail_masse"][$mail_candidat_id]);
    }

    if(!count($_SESSION["mail_masse"]))
    {
      $liste_vide=1;
      unset($_SESSION["mail_masse"]);

      // Rappel des paramètres pour rester sur la page de résultat
      $checked_message=$_SESSION["checked_message"];
      $requete=$_SESSION["requete"];

      $result=db_query($dbr, $requete);

      $rows=db_num_rows($result);
      $nb_trouves=$rows;
    }
    else
    {
      $_SESSION["from"]=$php_self;

      db_close($dbr);

      session_write_close();
      header("Location:message_masse.php");
      exit();
    }
  }

  unset($_SESSION["from"]);

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    titre_page_icone("Recherche nominative", "xmag_32x32_fond.png", 30, "L");

    if(isset($champs_vides))
      message("Le formulaire ne doit pas être vide", $__ERREUR);

    if(isset($bad_rech))
      message("La chaîne recherchée contient des caractères non autorisée", $__ERREUR);

    if(isset($_GET["masse"]) && $_GET["masse"]==1)
      message("Le message de masse a été envoyé avec succès.", $__SUCCES);

    if(!isset($nb_trouves))
    {
      print("<form action='$php_self' method='POST' name='form1'>\n");

      message("Seuls les candidats sans voeu ou ayant déposé un dossier dans votre composante apparaitront.", $__INFO);
  ?>

  <table align='center'>
  <tr>
    <td class='td-complet fond_menu2' nowrap='true' colspan='3' style='padding:4px;'>
      <font class='Texte_menu2'><b>Recherche ... </b></font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu'>
      <font class='Texte_menu'><b>Par nom ou début du nom : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='nom' value='' maxlength='30' size='30'>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu'>
      <font class='Texte_menu'><b>Par courriel ou partie du courriel : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <input type='text' name='email' value='' maxlength='128' size='30'>
    </td>
  </tr>
  <?php
      if($_SESSION["niveau"]!=$__LVL_SUPPORT)
     {
   ?>
  <tr>
    <td class='td-gauche fond_menu'>
      <font class='Texte_menu'><b>Sélection par défaut des candidats<br>pour l'envoi d'un message</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <font class='Texte_menu'>
        <input type='radio' name='selection' value='1' checked='1'>&nbsp;&nbsp;Sélectionner <b>tous</b> les candidats
        <br>
        <input type='radio' name='selection' value='0'>&nbsp;&nbsp;Ne sélectionner <b>aucun</b> candidat
      </font>
    </td>
  </tr>
   <?php
      }
   ?>
  </table>

  <div class='centered_icons_box'>
    <a href='recherche.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour au menu précédent' border='0'></a>
    <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Rechercher" name="recherche" value="Rechercher">
    </form>
  </div>

  <script language="javascript">
    document.form1.nom.focus()
  </script>

  <?php
    }
    else // résultat de la recherche
    {
      print("<form action='$php_self' method='POST' name='form1'>
            <input type='hidden' name='liste' value='1'>\n");

      if(isset($nb_trouves) && $nb_trouves!=0)
      {
        if($nb_trouves>1)
          print("<div class='centered_box'>
                <font class='Texte'><i>$nb_trouves candidat(e)s trouvé(e)s :</i></font>
               </div>\n");
        else
          print("<div class='centered_box'>
                <font class='Texte'><i>$nb_trouves candidat(e) trouvé(e) :</i></font>
               </div>\n");

        if(isset($flag_all))
        {
          for($i=97; $i<123 ; $i++)
            printf("<a href='#%c' class='lien2'>[%c] </a>",$i,$i);
          $current_letter='a';
          $old_letter='-1';
        }

        print("<br>
             <table width='90%' cellpadding='4' cellspacing='0' border='0' align='center'>
             <tr>\n");

        if(in_array($_SESSION["niveau"], array("$__LVL_SAISIE", "$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
          print("<td class='td-gauche fond_menu2' style='text-align:center'>
                <font class='Texte_menu2'><b>Sélection pour<br>message de masse</b></font>
              </td>\n");

        print("<td class='td-milieu fond_menu2' style='text-align:center' width='22'></td>
              <td class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Candidat(e)</b></font></td>
              <td class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Naissance</b></font></td>
              <td class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Nationalité</b></font></td>\n");

        if(in_array($_SESSION["niveau"], array("$__LVL_SUPPORT", "$__LVL_SAISIE", "$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
          print("<td class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Courriel</b></font></td>\n");

        if($_SESSION["niveau"]==$__LVL_ADMIN || $_SESSION["niveau"]==$__LVL_SUPPORT)
          print("<td colspan='2' class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Autres</b></font></td>\n");

        print("</tr>\n");

        $fond2='fond_menu';
        $icone_manuelle2="contact-new_22x22_menu.png";
        $texte2="Texte_menu";
        $lien2="lien_menu_gauche";

        $fond1='fond_blanc';
        $icone_manuelle1="contact-new_22x22_blanc.png";
        $texte1="Texte";
        $lien1="lien_bleu_12";

        $_SESSION["mail_masse"]=array();

        for($i=0; $i<$rows;$i++)
        {
          if($_SESSION["niveau"]==$__LVL_ADMIN || $_SESSION["niveau"]==$__LVL_SUPPORT)
            list($candidat_id, $civilite, $nom, $nom_naissance, $prenom, $date_naiss, $lieu, $nationalite, $courriel, $user_agent, $dernier_host, $derniere_ip, $identifiant, $code_acces, $connexion, $manuelle)=db_fetch_row($result,$i);
          else
            list($candidat_id, $civilite, $nom, $nom_naissance, $prenom, $date_naiss, $lieu, $nationalite, $courriel, $manuelle)=db_fetch_row($result,$i);

          $_SESSION["mail_masse"][$candidat_id]=array("civ" => "$civilite", "nom" => "$nom", "prenom" => "$prenom", "courriel" => "$courriel");

          $naissance=date_fr("j F Y",$date_naiss);

          $current_letter=strtolower(substr($nom,0,1));

          if(empty($lieu))
            $lieu="non renseigné";

          if(empty($nationalite))
            $nationalite="non renseignée";

          if($nom_naissance!=$nom && $nom_naissance!="")
          {
            // le nom de naissance peut-il être différent pour un homme ? (oui)
            if($civilite=="M")
               $nom.=" (né $nom_naissance)";
            else
               $nom.=" (née $nom_naissance)";
           }

          if(isset($flag_all) && $current_letter!=$old_letter)
          {
            printf("<tr>
                  <td align='left' style='padding-top:8px; padding-bottom:8px;'>
                    <font class='Texte'><a name='$current_letter'></a><b>%s</b>
                  </td>
                 </tr>\n", strtoupper($current_letter));

            $old_letter=$current_letter;
          }

          print("<tr>\n");

          if(in_array($_SESSION["niveau"], array("$__LVL_SAISIE", "$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
            print("<td class='td-gauche $fond2' style='text-align:center;'>
                  <input type='checkbox' name='selectmail_$candidat_id' value='$courriel' $checked_message>
                </td>\n");

          if($manuelle)
            print("<td class='td-gauche $fond2' style='text-align:center;' width='22'>
                  <img src='$__ICON_DIR/$icone_manuelle2' alt='Fiche manuelle' desc='Fiche créée manuellement' border='0'>
                </td>\n");
          else
            print("<td class='td-gauche $fond2' style='text-align:center;' width='22'></td>\n");

          print("<td class='td-milieu $fond2'>
                <a href='edit_candidature.php?rech=1&cid=$candidat_id' target='_self' class='$lien2'><b>$civilite. $nom $prenom</b></a>
              </td>
              <td class='td-milieu $fond2'>
                <font class='$texte2'>$naissance à $lieu</font>
              </td>
              <td class='td-milieu $fond2'>
                <font class='$texte2'>$nationalite</font>
              </td>\n");

          if(in_array($_SESSION["niveau"], array("$__LVL_SAISIE", "$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
          {
            $to=crypt_params("to=$candidat_id");

            print("<td class='td-milieu $fond2'>
                  <a href='messagerie/compose.php?p=$to' class='$lien2'><b>$courriel</b></a>
                </td>\n");
          }
          elseif($_SESSION["niveau"]==$__LVL_SUPPORT)
            print("<td class='td-milieu $fond2'>
                     <font class='$texte2'>$courriel</font>
                         </td>\n");

          if($_SESSION["niveau"]==$__LVL_ADMIN || $_SESSION["niveau"]==$__LVL_SUPPORT)
          {
            if($connexion!=0)
              $connexion_txt=date_fr("j F Y - H:i", $connexion);
            else
              $connexion_txt="Aucune";

            print("<td class='td-milieu $fond2' valign='top'>
                  <font class='$texte2'>
                    <b>ID</b> :
                    <br><b>Accès</b> :
                    <br><b>IP / Host</b> :
                    <br><b>User Agent</b> :
                  </font>
                </td>
                <td class='td-milieu $fond2' valign='top' nowrap='true'>
                  <font class='$texte2'>
                    $candidat_id
                    <br>$identifiant - $code_acces <b>Connexion</b> : $connexion_txt
                    <br>$derniere_ip - $dernier_host
                    <br>$user_agent
                  </font>
                </td>\n");
          }

          print("</tr>\n");

          switch_vals($fond1, $fond2);
          switch_vals($texte1, $texte2);
          switch_vals($lien1, $lien2);
          switch_vals($icone_manuelle1, $icone_manuelle2);
        }

        print("</table>
          
             <div class='centered_icons_box'>
              <a href='$php_self' target='_self' class='lien2'><img border='0' src='$__ICON_DIR/back_32x32.png' alt='Nouvelle recherche' desc='Nouvelle recherche'></a>\n");

        if(in_array($_SESSION["niveau"], array("$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
          print("<input type='image' src='$__ICON_DIR/mail_forward_32x32_fond.png' alt='Courriels de masse' name='courriels_masse' value='Courriels_masse'>\n");

        print("</form>
            </div>\n");
      }
      else
      {
        message("Aucun candidat ne correspond à votre recherche", $__WARNING);

        print("<div class='centered_box' style='padding-top:20px;'>
              <a href='$php_self' target='_self' class='lien2'><img border='0' src='$__ICON_DIR/back_32x32.png' alt='Nouvelle recherche' desc='Nouvelle recherche'></a>
            </div>\n");
      }

      db_free_result($result);
    }

    db_close($dbr);
  ?>
</div>
<?php
  pied_de_page();
?>
</body></html>
