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
    $formation=$_POST["formation"];
    $user_periode=ctype_digit($_POST["periode"]) ? $_POST["periode"] : date("Y");

    $critere_periode="AND $_DBC_cand_periode='$user_periode'";

    // Autres critères

    // Statut de la précandidature
    $critere_prec="";

    if(isset($_POST["statut_prec_$__PREC_NON_TRAITEE"]) && $_POST["statut_prec_$__PREC_NON_TRAITEE"]==$__PREC_NON_TRAITEE)
      $critere_prec="AND ($_DBC_cand_statut='$__PREC_NON_TRAITEE' ";

    if(isset($_POST["statut_prec_$__PREC_PLEIN_DROIT"]) && $_POST["statut_prec_$__PREC_PLEIN_DROIT"]==$__PREC_PLEIN_DROIT)
    {
      if($critere_prec=="")
        $critere_prec="AND ($_DBC_cand_statut='$__PREC_PLEIN_DROIT' ";
      else
        $critere_prec.="OR $_DBC_cand_statut='$__PREC_PLEIN_DROIT' ";
    }

    if(isset($_POST["statut_prec_$__PREC_EN_ATTENTE"]) && $_POST["statut_prec_$__PREC_EN_ATTENTE"]==$__PREC_EN_ATTENTE)
    {
      if($critere_prec=="")
        $critere_prec="AND ($_DBC_cand_statut='$__PREC_EN_ATTENTE' ";
      else
        $critere_prec.="OR $_DBC_cand_statut='$__PREC_EN_ATTENTE' ";
    }

    if(isset($_POST["statut_prec_$__PREC_RECEVABLE"]) && $_POST["statut_prec_$__PREC_RECEVABLE"]==$__PREC_RECEVABLE)
    {
      if($critere_prec=="")
        $critere_prec="AND ($_DBC_cand_statut='$__PREC_RECEVABLE' ";
      else
        $critere_prec.="OR $_DBC_cand_statut='$__PREC_RECEVABLE' ";
    }

    if(isset($_POST["statut_prec_$__PREC_NON_RECEVABLE"]) && $_POST["statut_prec_$__PREC_NON_RECEVABLE"]==$__PREC_NON_RECEVABLE)
    {
      if($critere_prec=="")
        $critere_prec="AND ($_DBC_cand_statut='$__PREC_NON_RECEVABLE' ";
      else
        $critere_prec.="OR $_DBC_cand_statut='$__PREC_NON_RECEVABLE' ";
    }

    if($critere_prec!="")
      $critere_prec.=") ";

    // Décision de la Commission pédagogique
    $critere_dec="";

    foreach($_POST as $key => $val)
    {
      // if(!strncmp($key, "dec_", 4) && $val!="" && ctype_digit(abs($val)))
      if(!strncmp($key, "dec_", 4) && $val!="")
      {
        // Une décision a été trouvée : le critère de Recevabilité est mis à "Recevable" automatiquement
        $critere_prec="AND $_DBC_cand_statut='$__PREC_RECEVABLE'";

        if($critere_dec=="")
          $critere_dec="AND ($_DBC_cand_decision='$val' ";
        else
          $critere_dec.="OR $_DBC_cand_decision='$val' ";
      }
    }

    // fermeture de la condition
    if($critere_dec!="")
      $critere_dec.=") ";

    // Options

    // $anciennes=array_key_exists("anciennes", $_POST) ? $_POST["anciennes"] : 1;

    if(array_key_exists("selection", $_POST) && ($_POST["selection"]==0 || $_POST["selection"]==1))
    {
      if($_POST["selection"]==1)
        $_SESSION["checked_message"]=$checked_message="checked='1'";
      else
        $_SESSION["checked_message"]=$checked_message="";
    }
    else
      $_SESSION["checked_message"]=$checked_message="checked='1'";

    if($formation!="")
    {
/*
      if($anciennes)
        $criteres_anciennes="";
      else
        $criteres_anciennes="AND $_DBC_cand_periode='$__PERIODE'";
*/

         if(!strncmp("mention_", $formation, 8) && preg_match("/mention_[[:alnum:]]+_[[:alnum:]]+/", $formation)) // format attendu : mention_$annee_$mention
         {
            $temp_form_array=explode("_", $formation);
                         
            if(is_array($temp_form_array) && isset($temp_form_array["1"]) && isset($temp_form_array["2"])) // le ctype_digit est normalement inutile : la chaine a été contrôlée par preg_match
            {
               $cond_annee_id=$temp_form_array["1"];
               $cond_mention_id=$temp_form_array["2"];
               
               $condition_formation="AND $_DBC_propspec_annee='$cond_annee_id' AND $_DBC_specs_mention_id='$cond_mention_id' ";
            }
         }        
      elseif($formation!="-1" && $formation!="orph" && ctype_digit($formation))
            $condition_formation="AND $_DBC_cand_propspec_id='$formation' ";
      else // Toutes les formations de la composante courante, pourvu que l'utilisateur y ait accès
      {
        $requete_droits_formations=requete_auth_droits($_SESSION["comp_id"]);
      
         $condition_formation=$requete_droits_formations;
        
        /*
        $condition_formation="AND $_DBC_cand_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
                                            WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')
                                            $requete_droits_formations";
            */
      }
  
      // Note : on conserve la requête dans une variable de session, au cas où

      if($_SESSION["niveau"]==$__LVL_ADMIN)
      {
        // Option réservée : filtre sur les dates de création des fiches
        if(isset($_POST["jour_inf"]) && isset($_POST["mois_inf"]) && isset($_POST["annee_inf"])
          && ctype_digit($_POST["jour_inf"]) && ctype_digit($_POST["mois_inf"]) && ctype_digit($_POST["annee_inf"]))
        {
          $date_inf=MakeTime(0, 0, 0, $_POST["mois_inf"], $_POST["jour_inf"], $_POST["annee_inf"]);
          $critere_id_inf="AND $_DBC_candidat_id >= '" . new_id($date_inf) . "'";
        }
        else
          $critere_id_inf="";

        if(isset($_POST["jour_sup"]) && isset($_POST["mois_sup"]) && isset($_POST["annee_sup"])
          && ctype_digit($_POST["jour_sup"]) && ctype_digit($_POST["mois_sup"]) && ctype_digit($_POST["annee_sup"]))
        {
          $date_sup=MakeTime(23, 59, 59, $_POST["mois_sup"], $_POST["jour_sup"], $_POST["annee_sup"]);
          $critere_id_sup="AND $_DBC_candidat_id <= '" . new_id($date_sup) . "'";
        }
        else
          $critere_id_sup="";

        // Sélection des fiches orphelines : les autres critères sont inutiles (sauf celui concernant les années précédentes)
        if($formation=="orph")
        {
          // Option réservée : inclure les candidats qui ne se sont jamais connectés (candidat.connexion='0') ?
          if(isset($_POST["jamais_connectes"]) && $_POST["jamais_connectes"]==1)
            $critere_connexion="";
          else
            $critere_connexion="AND $_DBC_candidat_connexion!='0'";

          // critère spécial dans ce cas : on ne peut pas se fier au champ "Periode" car inexistant
          // => recherche sur les candidats enregistrés cette année => date("y")
          $debut_recherche_id=preg_replace("/^0*/", "", date("y")); 
          $critere_periode="AND CAST($_DBC_candidat_id AS TEXT) LIKE '".preg_replace("/[']+/", "''", stripslashes($debut_recherche_id))."%'";
          
          $requete=$_SESSION["requete"]="SELECT distinct($_DBC_candidat_id), $_DBC_candidat_civilite, unaccent($_DBC_candidat_nom), unaccent($_DBC_candidat_nom_naissance), 
                                    $_DBC_candidat_prenom, $_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance,
                                    CASE WHEN $_DBC_candidat_nationalite IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite) 
                                        THEN (SELECT $_DBC_pays_nat_ii_nat FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite)
                                        ELSE '' END as nationalite,
                                    $_DBC_candidat_email, $_DBC_candidat_dernier_user_agent,
                                    $_DBC_candidat_dernier_host, $_DBC_candidat_derniere_ip, $_DBC_candidat_identifiant,
                                    $_DBC_candidat_code_acces, $_DBC_candidat_manuelle
                                FROM $_DB_candidat
                            WHERE $_DBC_candidat_id NOT IN (SELECT distinct($_DBC_cand_candidat_id) FROM $_DB_cand)
                            AND $_DBC_candidat_manuelle='0'
                            $critere_periode
                            $critere_connexion
                            $critere_id_inf
                            $critere_id_sup
                              ORDER BY unaccent($_DBC_candidat_nom), unaccent($_DBC_candidat_nom_naissance), $_DBC_candidat_prenom, $_DBC_candidat_date_naissance";
        }
        else
          $requete=$_SESSION["requete"]="SELECT distinct($_DBC_candidat_id), $_DBC_candidat_civilite, unaccent($_DBC_candidat_nom), unaccent($_DBC_candidat_nom_naissance),
                                    $_DBC_candidat_prenom, $_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance,
                                    CASE WHEN $_DBC_candidat_nationalite IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite) 
                                      THEN (SELECT $_DBC_pays_nat_ii_nat FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite)
                                        ELSE '' END as nationalite,
                                    $_DBC_candidat_email, $_DBC_candidat_dernier_user_agent,
                                    $_DBC_candidat_dernier_host, $_DBC_candidat_derniere_ip, $_DBC_candidat_identifiant,
                                    $_DBC_candidat_code_acces, $_DBC_candidat_manuelle
                                FROM $_DB_candidat, $_DB_cand, $_DB_propspec, $_DB_annees, $_DB_specs
                            WHERE $_DBC_cand_candidat_id=$_DBC_candidat_id
                            AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                            AND $_DBC_propspec_annee=$_DBC_annees_id
                            AND $_DBC_propspec_id_spec=$_DBC_specs_id
                            AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                            $condition_formation
                            $critere_prec
                            $critere_dec
                            $critere_periode
                            $critere_id_inf
                            $critere_id_sup
                              ORDER BY unaccent($_DBC_candidat_nom), unaccent($_DBC_candidat_nom_naissance), $_DBC_candidat_prenom, $_DBC_candidat_date_naissance";
      }
      else
        $requete=$_SESSION["requete"]="SELECT distinct($_DBC_candidat_id), $_DBC_candidat_civilite, unaccent($_DBC_candidat_nom), unaccent($_DBC_candidat_nom_naissance),
                                  $_DBC_candidat_prenom, $_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance,
                                  CASE WHEN $_DBC_candidat_nationalite IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite) 
                                    THEN (SELECT $_DBC_pays_nat_ii_nat FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite)
                                      ELSE '' END as nationalite,
                                  $_DBC_candidat_email, $_DBC_candidat_manuelle
                            FROM $_DB_candidat, $_DB_cand, $_DB_propspec, $_DB_annees, $_DB_specs
                        WHERE $_DBC_cand_candidat_id=$_DBC_candidat_id
                        AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                        AND $_DBC_propspec_annee=$_DBC_annees_id
                        AND $_DBC_propspec_id_spec=$_DBC_specs_id
                        AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                        $condition_formation
                        $critere_prec
                        $critere_dec
                        $critere_periode
                          ORDER BY unaccent($_DBC_candidat_nom), unaccent($_DBC_candidat_nom_naissance), $_DBC_candidat_prenom, $_DBC_candidat_date_naissance";

      $result=db_query($dbr, $requete);

      $rows=db_num_rows($result);
      $nb_trouves=$rows;
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

  // Nettoyage de la liste des pièces jointes, si elle existe
  if(isset($_SESSION["tmp_message_fichiers"]))
  {
    foreach($_SESSION["tmp_message_fichiers"] as $array_file)
      @unlink("$array_file[file]");

    unset($_SESSION["tmp_message_fichiers"]);
  }

  unset($_SESSION["from"]);

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    titre_page_icone("Recherche générale", "xmag_32x32_fond.png", 30, "L");

    if(isset($empty_nom))
      message("Le formulaire ne doit pas être vide", $__ERREUR);

    if(isset($_GET["masse"]) && $_GET["masse"]==1)
      message("Le message de masse a été envoyé avec succès.", $__SUCCES);

    if(isset($liste_vide) && $liste_vide==1)
      message("Envoi de message impossible : aucun candidat sélectionné", $__ERREUR);

    if(!isset($nb_trouves))
    {
      print("<form action='$php_self' method='POST' name='form1'>\n");
  ?>

  <div style='max-width:80%; margin:0px auto 0px auto'>
    <table style='width:100%; margin-bottom:10px;'>
    <tr>
      <td class='td-gauche fond_menu2' colspan='2' style='padding:4px;'>
        <font class='Texte_menu2'><b>Recherche</b></font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu'>
        <font class='Texte_menu'><b>Formation : </b></font>
      </td>
      <td class='td-droite fond_menu'>
        <?php
          $requete_droits_formations=requete_auth_droits($_SESSION["comp_id"]);
          
          $result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_propspec_id_spec,
                              $_DBC_specs_nom_court, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_mentions_nom
                            FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
                          WHERE $_DBC_propspec_annee=$_DBC_annees_id
                          AND $_DBC_propspec_id_spec=$_DBC_specs_id
                          AND $_DBC_specs_mention_id=$_DBC_mentions_id
                          AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                          AND $_DBC_propspec_id IN (SELECT distinct($_DBC_cand_propspec_id) FROM $_DB_cand WHERE $_DBC_cand_periode='$__PERIODE')
                          $requete_droits_formations
                            ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom_court");

          $rows=db_num_rows($result);

          if($rows)
          {
            print("<select size='1' name='formation'>\n");

            // admin : option spéciale : sélection des fiches orphelines
            if($_SESSION["niveau"]==$__LVL_ADMIN)
              print("<option value='orph'>Uniquement les fiches orphelines</option>
                    <option value='-1' disabled='1'></option>\n");

            print("<option value='-1'>Toutes les formations de cette composante</option>
                  <option value='-1' disabled='1'></option>\n");

            $old_annee="-1";
            $old_mention="-1";

            for($i=0; $i<$rows; $i++)
            {
              list($form_propspec_id, $form_annee_id, $form_annee_nom, $form_spec_id, $form_spec_nom, $form_mention, $form_finalite, $form_mention_nom)=db_fetch_row($result, $i);

              $finalite_txt=$tab_finalite[$form_finalite];

              if($form_annee_id!=$old_annee)
              {
                if($i!=0)
                  print("</optgroup>
                        <option value='' label='' disabled></option>\n");

                $form_annee=$form_annee_nom=="" ? "Années particulières" : $form_annee_nom;

                print("<optgroup label='$form_annee'>\n");

                $new_sep_annee=1;

                $old_annee=$form_annee_id;
                $old_mention="-1";
              }
              else
                $new_sep_annee=0;

              if($form_mention!=$old_mention)
              {
                if(!$new_sep_annee)
                  print("<!-- </optgroup> -->
                        <option value='' label='' disabled></option>\n");

                $val=htmlspecialchars($form_mention_nom, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE);

                print("<!-- <optgroup label='- $val'> -->\n");
                
                $label=$form_annee_nom=="" ? "** Mention $val **" : "** $form_annee_nom Mention $val **";
                
                print("<option value='mention_".$form_annee_id."_".$form_mention."' label=\"$label\">$label</option>\n");

                $old_mention=$form_mention;
              }

              print("<option value='$form_propspec_id' label=\"$form_spec_nom $finalite_txt\">$form_spec_nom $finalite_txt</option>\n");
            }

            print("</select>
                <br><font class='Texte'><i>Seules les formations auxquelles vous avez accès et pour lesquelles des dossiers existent sont proposées</i>\n");
          }
          else
          {
            print("<font class='Texte_important_menu'>
                  <b>Aucun dossier n'a encore été déposé pour cette année universitaire.</b>
                  </font>\n");

            $no_next=1;
          }
        ?>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu'>
        <font class='Texte_menu'><b>Année universitaire : </b></font>
      </td>
      <td class='td-droite fond_menu'>
        <select name='periode' size='1'>
        <?php
          $result=db_query($dbr, "SELECT distinct($_DBC_cand_periode) FROM $_DB_cand
                          ORDER BY $_DBC_cand_periode DESC");

          $rows=db_num_rows($result);

          for($i=0; $i<$rows; $i++)
          {
            list($liste_periode)=db_fetch_row($result,$i);
            
            if(isset($_SESSION["current_user_periode"]))
              $selected=($_SESSION["current_user_periode"]==$liste_periode) ? "selected" : "";
            else
              $selected=($liste_periode==$__PERIODE) ? "selected" : "";
            
            print("<option value='$liste_periode' $selected>$liste_periode-".($liste_periode+1)."</option>\n");
          }

          db_free_result($result);
        ?>
      </td>
    </tr>
    </table>

    <table style='width:100%; margin-bottom:10px;'>
    <tr>
      <td class='td-gauche fond_menu2' colspan='3' style='padding:4px;'>
        <font class='Texte_menu2'><b>Critères supplémentaires</b></font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu' rowspan='3'>
        <font class='Texte_menu'><b>Statut de la recevabilité : </b><br><i>Si aucun critère : paramètre ignoré</i></font>
      </td>
      <td class='td-milieu fond_menu'>
        <font class='Texte_menu'>
          <?php print("<input type='checkbox' name='statut_prec_$__PREC_NON_TRAITEE' value='$__PREC_NON_TRAITEE'>&nbsp;&nbsp;Non traitée"); ?>
        </font>
      </td>
      <td class='td-milieu fond_menu'>
        <font class='Texte_menu'>
          <?php print("<input type='checkbox' name='statut_prec_$__PREC_PLEIN_DROIT' value='$__PREC_PLEIN_DROIT'>&nbsp;&nbsp;Plein droit"); ?>
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-milieu fond_menu'>
        <font class='Texte_menu'>
          <?php print("<input type='checkbox' name='statut_prec_$__PREC_EN_ATTENTE' value='$__PREC_EN_ATTENTE'>&nbsp;&nbsp;En attente"); ?>
        </font>
      </td>
      <td class='td-milieu fond_menu'>
        <font class='Texte_menu'>
          <?php print("<input type='checkbox' name='statut_prec_$__PREC_RECEVABLE' value='$__PREC_RECEVABLE'>&nbsp;&nbsp;Recevable"); ?>
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-milieu fond_menu'>
        <font class='Texte_menu'>
          <?php print("<input type='checkbox' name='statut_prec_$__PREC_NON_RECEVABLE' value='$__PREC_NON_RECEVABLE'>&nbsp;&nbsp;Non recevable"); ?>
        </font>
      </td>
      <td class='td-milieu fond_menu'></td>
    </tr>
    <?php
      $result=db_query($dbr, "SELECT $_DBC_decisions_id, $_DBC_decisions_texte FROM $_DB_decisions
                        WHERE $_DBC_decisions_id IN (SELECT distinct($_DBU_cand_decision) FROM $_DB_cand WHERE $_DBU_cand_periode='$__PERIODE'
                              AND $_DBU_cand_propspec_id IN (SELECT $_DBU_propspec_id FROM $_DB_propspec WHERE $_DBU_propspec_comp_id='$_SESSION[comp_id]'))
                      ORDER BY $_DBC_decisions_texte");

      $rows=db_num_rows($result);

      if($rows)
      {
        $rowspan=($rows/2)+($rows%2);
    ?>
    <tr>
      <td class='fond_page' colspan='3'></td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu' style='padding-bottom:4px;' valign=middle; rowspan='<?php echo $rowspan; ?>'>
        <font class='Texte_menu'>
          <b>Décision de la Commission : </b>
          <br><i>Si aucun critère : paramètre ignoré</i>
          <br><i>Si critère présent : statut "Recevable" automatique</i>
        </font>
      </td>
      <?php
        for($i=0; $i<$rows; $i++)
        {
          list($dec_id, $dec_texte)=db_fetch_row($result, $i);

          // Cas particulier pour la première ligne
          if(!($i%2) && $i>=2)
            print("<tr>");

          print("<td class='td-milieu fond_menu' align='left' nowrap='true' style='padding-bottom:4px;'>
                <font class='Texte_menu'>
                  <input type='checkbox' name='dec_$dec_id' value='$dec_id'>&nbsp;&nbsp;$dec_texte
                </font>
                </td>\n");

          if($i%2)
            print("</tr>");
        }

        if($i%2)
          print("<td class='td-milieu fond_menu'></td>
              </tr>\n");
      }

      print("</table>\n");

      if(isset($_SESSION["niveau"]) && in_array($_SESSION["niveau"],array("$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
      {
    ?>

    <table align='center' width='100%'>
    <tr>
      <td class='td-gauche fond_menu2' colspan='3' style='padding:4px;'>
        <font class='Texte_menu2'><b>Options</b></font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu'>
        <font class='Texte_menu'><b>Sélection par défaut des candidats<br>pour l'envoi d'un message</b></font>
      </td>
      <td class='td-droite fond_menu' colspan='2'>
        <font class='Texte_menu'>
          <input type='radio' name='selection' value='1' checked='1'>&nbsp;&nbsp;Sélectionner <b>tous</b> les candidats
          <br>
          <input type='radio' name='selection' value='0'>&nbsp;&nbsp;Ne sélectionner <b>aucun</b> candidat
        </font>
      </td>
    </tr>
    <?php
      // autre critère réservé à l'admin :
      // - inclure/exclure les candidats qui ne se sont jamais connectés
      // - intervalle de dates de création des fiches : utile pour envoyer des rappels à un groupe ciblé de candidats

      if($_SESSION["niveau"]==$__LVL_ADMIN)
      {
    ?>
    <tr>
      <td class='td-gauche fond_menu'>
        <font class='Texte_menu'><b>Fiches orphelines : inclure les candidats<br>qui ne se sont jamais connectés ?</b></font>
      </td>
      <td class='td-droite fond_menu' colspan='2'>
        <font class='Texte_menu' style='vertical-align:bottom;'>
          <input type='radio' name='jamais_connectes' value='1' style='vertical-align:top;'>&nbsp;Oui
          &nbsp;&nbsp;<input type='radio' name='jamais_connectes' value='0' checked='1' style='vertical-align:top;'>&nbsp;Non
        </font>
      </td>
    </tr>
    <tr>
      <td class='td-gauche fond_menu'>
        <font class='Texte_menu'><b>Intervalle de dates de création des fiches</b><br><i>(facultatif - format JJ : MM : AAAA)</font>
      </td>
      <td class='td-droite fond_menu' colspan='2'>
        <font class='Texte_menu' style='vertical-align:bottom;'>
          <?php
            $y=mb_substr($__PERIODE, 2,2, "UTF-8"); // passage de la période de 4 à 2 chiffres (2008 => 08)         

            if(mb_substr($y, 0, 1, "UTF-8") == "0") // réduction à un chiffre si le premier vaut 0
              $y=mb_substr($y, 1, 1, "UTF-8");
              
            $annee_sup=date("Y")>$__PERIODE ? date("Y") : $__PERIODE;

            $res_min_max=db_query($dbr,"SELECT min($_DBC_candidat_id), max($_DBC_candidat_id) FROM $_DB_candidat
                                WHERE CAST($_DBC_candidat_id AS TEXT) LIKE '$y%'");

            if(db_num_rows($res_min_max))
            {
              list($id_inf, $id_sup)=db_fetch_row($res_min_max, 0);

              // Pour le moment, la date limite inférieure n'est pas utilisée
              if($id_inf!="")
              {
                $timestamp_inf=id_to_date($id_inf);
                $jour_inf=date("j", $timestamp_inf);
                $mois_inf=date("m", $timestamp_inf);
              }
              else
                $jour_inf=$mois_inf="";

              if($id_sup!="")
              {
                $timestamp_sup=id_to_date($id_sup);
                $jour_sup=date("j", $timestamp_sup);
                $mois_sup=date("m", $timestamp_sup);
              }
              else
                $jour_sup=$mois_sup="";
            }
            else
              $jour_inf=$mois_inf=$jour_sup=$mois_sup="";

            db_free_result($res_min_max);
          ?>

          <b>Du</b>&nbsp;&nbsp;
          J : <input class='input_small' type="text" name='jour_inf' value='' size='3' maxlength='2'>&nbsp;
          M : <input class='input_small' type="text" name='mois_inf' value='' size='3' maxlength='2'>&nbsp;
          A : <input class='input_small' type="text" name='annee_inf' value='' size='5' maxlength='4'>&nbsp;&nbsp;&nbsp;&nbsp;
          <b>Au</b>&nbsp;&nbsp;
          J : <input type="text" name='jour_sup' value='<?php echo $jour_sup; ?>' size='3' maxlength='2'>&nbsp;
          M : <input class='input_small' type="text" name='mois_sup' value='<?php echo $mois_sup; ?>' size='3' maxlength='2'>&nbsp;
          A : <input class='input_small' type="text" name='annee_sup' value='<?php echo $annee_sup; ?>' size='5' maxlength='4'>&nbsp;&nbsp;
        </font>
      </td>
    </tr>
    <?php
      }
    ?>
    </table>

    <?php
      }
    ?>
  </div>

  <div class='centered_icons_box'>
    <a href='recherche.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour au menu précédent' border='0'></a>
    <?php
      if(!isset($no_next))
        print("<input type='image' src='$__ICON_DIR/button_ok_32x32_fond.png'; alt='Rechercher' name='recherche' value='Rechercher'>");
    ?>
    </form>
  </div>
  
  <script language="javascript">
    document.form1.nom.focus()
  </script>

  <?php
    }
    else // résultat de la recherche
    {
      // Formulaire pour envoyer un message de masse
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
        if(in_array($_SESSION["niveau"],array("$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
          print("<td class='td-gauche fond_menu2' colspan='2' style='text-align:center;'><font class='Texte_menu2'><b>Sélection pour l'envoi<br>d'un message de masse</b></font></td>\n");
        else
          print("<td class='td-gauche fond_menu2' style='text-align:center;'>\n");

        print("<td class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Candidat(e)</b></font></td>
             <td class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Naissance</b></font></td>
             <td class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Nationalité</b></font></td>\n");

        if(in_array($_SESSION["niveau"],array("$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
          print("<td class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Courriel</b></font></td>\n");

        if($_SESSION["niveau"]==$__LVL_ADMIN)
          print("<td colspan='2' class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Autres</b></font></td>\n");

        print("</tr>\n");

        $fond2="fond_menu";
        $icone_manuelle2="contact-new_22x22_menu.png";
        $texte2="Texte_menu";
        $lien2="lien_menu_gauche";

        $fond1="fond_blanc";
        $icone_manuelle1="contact-new_22x22_blanc.png";
        $texte1="Texte";
        $lien1="lien_bleu_12";

        $_SESSION["mail_masse"]=array();

        for($i=0; $i<$rows;$i++)
        {
          if($_SESSION["niveau"]==$__LVL_ADMIN)
            list($candidat_id, $civilite, $nom, $nom_naissance, $prenom, $date_naiss, $lieu, $nationalite, $courriel, $user_agent, $dernier_host, $derniere_ip, $identifiant, $code_acces, $manuelle)=db_fetch_row($result,$i);
          else
            list($candidat_id, $civilite, $nom, $nom_naissance, $prenom, $date_naiss, $lieu, $nationalite, $courriel, $manuelle)=db_fetch_row($result,$i);

          $_SESSION["mail_masse"][$candidat_id]=array("civ" => "$civilite", "nom" => "$nom", "prenom" => "$prenom", "courriel" => "$courriel");

          $naissance=date_fr("j F Y",$date_naiss);

          $current_letter=strtolower(mb_substr($nom,0,1, "UTF-8"));

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

          if(in_array($_SESSION["niveau"],array("$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
            print("<td class='td-gauche $fond2' style='text-align:center;'>
                  <input type='checkbox' name='selectmail_$candidat_id' value='$courriel' $checked_message>
                </td>\n");

          if($manuelle)
            print("<td class='$fond2' width='22'>
                  <img src='$__ICON_DIR/$icone_manuelle2' alt='Fiche manuelle' desc='Fiche créée manuellement' border='0'>
                </td>\n");
          else
            print("<td class='$fond2' width='22'></td>\n");

          print("<td class='td-milieu $fond2'>
                <a href='edit_candidature.php?rech=1&cid=$candidat_id' target='_self' class='$lien2'><b>$civilite. $nom $prenom</b></a>
              </td>
              <td class='td-milieu $fond2'>
                <font class='$texte2'>$naissance à $lieu</font>
              </td>
              <td class='td-milieu $fond2'>
                <font class='$texte2'>$nationalite</font>
              </td>\n");

          if(in_array($_SESSION["niveau"],array("$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
          {
            $to=crypt_params("to=$candidat_id");

            print("<td class='td-gauche $fond2'>
                  <a href='messagerie/compose.php?p=$to' class='$lien2'><b>$courriel</b></a>
                </td>\n");
          }

          if($_SESSION["niveau"]==$__LVL_ADMIN)
            print("<td class='td-milieu $fond2' valign='top'>
                  <font class='$texte2'>
                    <b>ID</b> :
                    <br><b>Accès</b> :
                    <br><b>IP / Host</b> :
                    <br><b>User Agent</b> :
                  </font>
                </td>
                <td class='td-milieu $fond2' valign='top'>
                  <font class='$texte2'>
                    $candidat_id
                    <br>$identifiant - $code_acces
                    <br>$derniere_ip - $dernier_host
                    <br>$user_agent
                  </font>
                </td>\n");

          print("</tr>\n");

          switch_vals($fond1, $fond2);
          switch_vals($texte1, $texte2);
          switch_vals($lien1, $lien2);
          switch_vals($icone_manuelle1, $icone_manuelle2);
        }

        print("</table>

            <div class='centered_icons_box'>
              <a href='recherche_generale.php' target='_self' class='lien2'><img border='0' src='$__ICON_DIR/back_32x32.png' alt='Nouvelle recherche' desc='Nouvelle recherche'></a>\n");

        if(in_array($_SESSION["niveau"],array("$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
          print("<input type='image' src='$__ICON_DIR/mail_forward_32x32_fond.png' alt='Courriels de masse' name='courriels_masse' value='Courriels_masse'>\n");

        print("</form>
            </div>\n");
      }
      else
      {
        message("Aucun candidat ne correspond à votre recherche", $__WARNING);

        print("<div class='centered_box'>
              <a href='recherche.php' target='_self' class='lien2'><img border='0' src='$__ICON_DIR/back_32x32.png' alt='Nouvelle recherche' desc='Nouvelle recherche'></a>
             </div>\n");
      }

      db_free_result($result);
      print("</center>\n");
    }

    db_close($dbr);
  ?>
</div>
<?php
  pied_de_page();
?>
</body></html>
