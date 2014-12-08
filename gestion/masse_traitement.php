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
  if(isset($_SESSION["candidat_id"]))
    $candidat_id=$_SESSION["candidat_id"];

  $dbr=db_connect();

  // Déverrouillage, au cas où
  if(isset($_SESSION["candidat_id"]))
    cand_unlock($dbr, $_SESSION["candidat_id"]);

  if(isset($_POST["suivant"]) || isset($_POST["suivant_x"]))
  {
    unset($_SESSION["candidatures_array"]);

    $propspec_id=$_POST["formation"];

    if($propspec_id!="")
      $resultat=1;
    else
      $selection_invalide=1;
  }
  elseif((isset($_POST["valider"]) || isset($_POST["valider_x"])) && isset($_SESSION["candidatures_array"]))
  {
    $propspec_id=$_POST["propspec"];

    // on traite les candidatures :
    $nb_success=0;
    $deja_traitee=0;
    $non_traitees=0;

    $partie=1;

    // On prépare un tableau pour les candidatures dont la décision est saisie de manière incomplète ou erronée
    $array_erreurs=array();

    // La méthode de construction de l'identifiant devrait en assurer l'unicité en cas de traitement simultané
    $masse_id=$_SESSION['auth_id'] . "_" . time();

    // Tri pour les rangs sur la liste complémentaire
    // => l'ordre de traitement détermine la manière dont les rangs existants sont décalés
    // il faut donc parcourir la liste de manière ordonnée

    // TODO : essayer de faire plus propre pour limiter les boucles

    // 1/ on complète les candidatures à traiter
    foreach($_SESSION["candidatures_array"] as $inid => $current_decision_array)
    {
      if(array_key_exists("liste_attente", $_POST) && array_key_exists("$inid", $_POST["liste_attente"]) && $_POST["liste_attente"][$inid]!="0" && $_POST["liste_attente"][$inid]!="")
        $_SESSION["candidatures_array"]["$inid"]["rang_liste"]=$_POST["liste_attente"]["$inid"];
      else
        $_SESSION["candidatures_array"]["$inid"]["rang_liste"]="0";
    }

    // 2/ on trie en fonction du nouveau rang (pas de rang ou 0 : en début de liste)
    $bool=uasort($_SESSION["candidatures_array"],"cmp_rangs_liste_complementaire");

    foreach($_SESSION["candidatures_array"] as $inid => $current_decision_array)
    {
      if(array_key_exists("decision", $_POST) && array_key_exists("$inid", $_POST["decision"]))
        $new_decision=$_POST["decision"]["$inid"];
      else
        $new_decision="";
          
      if(array_key_exists("motivation_decision", $_POST) && array_key_exists("$inid", $_POST["motivation_decision"])) 
        $mot=$_POST["motivation_decision"]["$inid"];
      else
        $mot="";
          
      if(array_key_exists("motivation_decision_libre", $_POST) && array_key_exists("$inid", $_POST["motivation_decision_libre"]))
        $mot_libre=$_POST["motivation_decision_libre"][$inid];
      else
        $mot_libre="";
        
      $new_motivation="";
/*
      $entretien_date="0";
      $entretien_jour=$entretien_mois=$entretien_annee=$entretien_heure=$entretien_h=$entretien_m=$entretien_salle=$entretien_lieu="";
*/

      $entretien_jour=(isset($_POST["entretien_jour"][$inid]) && ctype_digit($_POST["entretien_jour"][$inid])) ? trim($_POST["entretien_jour"][$inid]) : "";
      $entretien_mois=(isset($_POST["entretien_mois"][$inid]) && ctype_digit($_POST["entretien_mois"][$inid]))? trim($_POST["entretien_mois"][$inid]) : "";
      $entretien_annee=(isset($_POST["entretien_annee"][$inid]) && ctype_digit($_POST["entretien_annee"][$inid])) ? trim($_POST["entretien_annee"][$inid]) : "$__PERIODE";

      $entretien_h=isset($_POST["entretien_heure"][$inid]) ? trim($_POST["entretien_heure"][$inid]) : "00";
      $entretien_m=isset($_POST["entretien_minute"][$inid]) ? trim($_POST["entretien_minute"][$inid]) : "00";

      // $entretien_date=isset($_POST["entretien_date"][$inid]) ? trim($_POST["entretien_date"][$inid]) : "";
      // $entretien_heure=isset($_POST["entretien_heure"][$inid]) ? trim($_POST["entretien_heure"][$inid]) : "";
      $entretien_salle=isset($_POST["entretien_salle"][$inid]) ? trim($_POST["entretien_salle"][$inid]) : "";
      $entretien_lieu=isset($_POST["entretien_lieu"][$inid]) ? trim($_POST["entretien_lieu"][$inid]) : "";

      if($new_decision==$__DOSSIER_ENTRETIEN || $new_decision==$__DOSSIER_ENTRETIEN_TEL 
         || $current_decision_array["decision"]==$__DOSSIER_ENTRETIEN || $current_decision_array["decision"]==$__DOSSIER_ENTRETIEN_TEL)
      {
        if($entretien_lieu=="" || $entretien_salle=="")
        {
          if(!isset($defaut_ent_salle) || !isset($defaut_ent_lieu))
          {
            $res_defaut=db_query($dbr,"SELECT $_DBC_composantes_ent_salle, $_DBC_composantes_ent_lieu
                              FROM $_DB_composantes WHERE $_DBC_composantes_id='$_SESSION[comp_id]'");

            if(db_num_rows($res_defaut))
              list($defaut_ent_salle, $defaut_ent_lieu)=db_fetch_row($res_defaut, 0);
            else
              $defaut_ent_salle=$defaut_ent_lieu="";

            db_free_result($res_defaut);
          }

          if($entretien_lieu=="")
            $entretien_lieu=str_replace("'","''", $defaut_ent_lieu);

          if($entretien_salle=="")
            $entretien_salle=str_replace("'","''", $defaut_ent_salle);
        }

        if($entretien_jour!="" && ctype_digit($entretien_jour) && $entretien_mois!="" && ctype_digit($entretien_mois)
          && $entretien_h!="" && ctype_digit($entretien_h) && $entretien_m!="" && ctype_digit($entretien_m))
        {
          $entretien_date=MakeTime($entretien_h,$entretien_m,0,$entretien_mois, $entretien_jour, $entretien_annee);
          $entretien_heure_texte=$entretien_h . "h" . $entretien_m;
        }
        else  // date manquante ou incomplète : erreur
        {
          $array_erreurs[$inid]=$current_decision_array;
          $array_erreurs[$inid]["decision"]=$new_decision;
          $array_erreurs[$inid]["motivation"]=$mot;
          $array_erreurs[$inid]["motivation_libre"]=$mot_libre;
          $array_erreurs[$inid]["entretien_jour"]=$entretien_jour;
          $array_erreurs[$inid]["entretien_mois"]=$entretien_mois;
          $array_erreurs[$inid]["entretien_annee"]=$entretien_annee;
          $array_erreurs[$inid]["entretien_heure"]=$entretien_h;
          $array_erreurs[$inid]["entretien_minutes"]=$entretien_m;
          $array_erreurs[$inid]["entretien_salle"]=$entretien_salle;
          $array_erreurs[$inid]["entretien_lieu"]=$entretien_lieu;
          $array_erreurs[$inid]["erreur_motif"]="entretien";
        }
      }

      // Initialisation de variables pour l'entretien, au cas où ça n'aurait pas été fait dans le bloc précédent
      // Todo : à traiter plus proprement
      if(!isset($entretien_date))
      {
        if($entretien_jour!="" && ctype_digit($entretien_jour) && $entretien_mois!="" && ctype_digit($entretien_mois)
          && $entretien_h!="" && ctype_digit($entretien_h) && $entretien_m!="" && ctype_digit($entretien_m))
          $entretien_date=MakeTime($entretien_h,$entretien_m,0,$entretien_mois, $entretien_jour, $entretien_annee);
        elseif(isset($current_decision_array["entretien_date"]))
          $entretien_date=$current_decision_array["entretien_date"];
        else
          $entretien_date="0";
      }

      if($entretien_h=="00" && $entretien_m=="00")
        $entretien_heure_texte="";
      elseif(ctype_digit($entretien_h) && ctype_digit($entretien_m) && $entretien_h<24 && $entretien_h>=0 && $entretien_m>=0 && $entretien_m<=60)
        $entretien_heure_texte=$entretien_h . "h" . $entretien_m;
      else
        $entretien_heure_texte="";

      if($mot!="")
        $new_motivation .= $mot;

      if($mot_libre!="")
      {
        if($new_motivation!="")
          $new_motivation .= "|";

        $new_motivation.="@$mot_libre";
      }

      // Condition du traitement : décision non vide et différente de la précédente (ou motif différent)
      // Attention : pour les entretiens, même si la décision est identique, on enregistre les modifications

      if($new_decision!="" && $new_decision!=$__DOSSIER_NON_TRAITE &&
        ($new_decision!=$current_decision_array["decision"]
          || $current_decision_array["rang_liste"]!=$current_decision_array["liste_comp"]
          || stripslashes(trim($_SESSION["candidatures_array"]["$inid"]["motif"]))!=stripslashes(trim($new_motivation))
          || $current_decision_array["entretien_date"]!="$entretien_date"
          ||  stripslashes(trim($current_decision_array["entretien_lieu"]))!=stripslashes(trim("$entretien_lieu"))
          ||  stripslashes(trim($current_decision_array["entretien_salle"]))!=stripslashes(trim("$entretien_salle"))))
      {
        if($current_decision_array["decision"]<=$__DOSSIER_NON_TRAITE)
        {
          // On met la décision à jour puis on insère une ligne dans la table des traitements de masse pour produire les lettres
          // Au cas où, on supprime toute trace du "traitement de masse" de cette candidature avant l'insertion

          $date_prise_decision=time();

          if($new_decision==$__DOSSIER_LISTE || $new_decision==$__DOSSIER_LISTE_ENTRETIEN)
          {
            // rang vide : on prend automatiquement le max dans la base, ou 1 si pas de max
            // on n'oublie pas les années pour les recherches sur l'identifiant d'inscription (timestamp)

            if($current_decision_array["rang_liste"]=="" || !ctype_digit($current_decision_array["rang_liste"]) || $current_decision_array["rang_liste"]<1)
            {
              $result=db_query($dbr,"SELECT max(CAST($_DBC_cand_liste_attente AS int)) FROM $_DB_cand
                              WHERE $_DBC_cand_propspec_id='$propspec_id'
                              AND ($_DBC_cand_decision='$__DOSSIER_LISTE' OR $_DBC_cand_decision='$__DOSSIER_LISTE_ENTRETIEN')
                              AND $_DBC_cand_periode='$__PERIODE'
                              AND $_DBC_cand_liste_attente!=''");

              list($max_rang)=db_fetch_row($result,0);

              if($max_rang=="") // personne dans la liste complémentaire
                $current_decision_array["rang_liste"]=1;
              else
                $current_decision_array["rang_liste"]=$max_rang+1;

              db_free_result($result);
            }
            else
            {
              // Si une candidature est déjà à ce rang là, il faudra tout décaler

              if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_cand
                                    WHERE $_DBC_cand_propspec_id='$propspec_id'
                                    AND $_DBC_cand_periode='$__PERIODE'
                                    AND ($_DBC_cand_decision='$__DOSSIER_LISTE' OR $_DBC_cand_decision='$__DOSSIER_LISTE_ENTRETIEN')
                                    AND $_DBC_cand_liste_attente='$current_decision_array[rang_liste]'
                                    AND $_DBC_cand_candidat_id!='$inid'")))
              {
                $res_rangs=db_query($dbr, "SELECT $_DBC_cand_id, $_DBC_cand_liste_attente FROM $_DB_cand
                                    WHERE $_DBU_cand_propspec_id='$propspec_id'
                                    AND $_DBU_cand_periode='$__PERIODE'
                                    AND ($_DBU_cand_decision='$__DOSSIER_LISTE' OR $_DBU_cand_decision='$__DOSSIER_LISTE_ENTRETIEN')
                                    AND $_DBU_cand_liste_attente!=''
                                    AND CAST($_DBU_cand_liste_attente AS int)>= '$current_decision_array[rang_liste]'");

                $rows_rangs=db_num_rows($res_rangs);

                for($r=0; $r<$rows_rangs; $r++)
                {
                  list($dec_cand_id, $dec_rang)=db_fetch_row($res_rangs, $r);

                  if($r!=($rows_rangs-1))
                  {
                    list($next_cand_id, $next_rang)=db_fetch_row($res_rangs, ($r+1));

                    db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_liste_attente='$next_rang' WHERE $_DBU_cand_id='$dec_cand_id'");

                    // Le rang suivant n'est pas consécutif (=trou) : on sort de la boucle
                    if($next_rang==($dec_rang+1))
                      $r=$rows_rangs;
                  }
                  else
                  {
                    $next_rang=$dec_rang+1;
                    db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_liste_attente='$next_rang' WHERE $_DBU_cand_id='$dec_cand_id'");
                  }
                }
              }

              // Il ne reste plus qu'à mettre le rang de notre candidature en cours.
              db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_liste_attente='$current_decision_array[rang_liste]' WHERE $_DBU_cand_id='$inid'");
            }
          }
          else
            $current_decision_array["rang_liste"]=0;

          // Refus, réserve ou mise en attente : saisie obligatoire du motif
          if(($new_decision==$__DOSSIER_EN_ATTENTE || $new_decision==$__DOSSIER_SOUS_RESERVE
            || $new_decision==$__DOSSIER_REFUS || $new_decision==$__DOSSIER_REFUS_ENTRETIEN
            || $new_decision==$__DOSSIER_REFUS_RECOURS) && trim($new_motivation)=="")
          {
            $array_erreurs[$inid]=$current_decision_array;
            $array_erreurs[$inid]["decision"]=$new_decision;
            $array_erreurs[$inid]["motivation"]=$mot;
            $array_erreurs[$inid]["motivation_libre"]=$mot_libre;
            $array_erreurs[$inid]["entretien_jour"]=$entretien_jour;
            $array_erreurs[$inid]["entretien_mois"]=$entretien_mois;
            $array_erreurs[$inid]["entretien_annee"]=$entretien_annee;
            $array_erreurs[$inid]["entretien_heure"]=$entretien_h;
            $array_erreurs[$inid]["entretien_minutes"]=$entretien_m;
            $array_erreurs[$inid]["entretien_salle"]=$entretien_salle;
            $array_erreurs[$inid]["entretien_lieu"]=$entretien_lieu;
            $array_erreurs[$inid]["erreur_motif"]="motif";
          }

          if(!array_key_exists($inid, $array_erreurs) && db_num_rows(db_query($dbr, "SELECT * FROM $_DB_cand WHERE $_DBC_cand_id='$inid'")))
          {
            $nb_success=$nb_success+1;

            if(!($nb_success%$__MAX_CAND_MASSE)) // Nouvelle "partie"
              $partie++;

            db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_decision='$new_decision',
                                    $_DBU_cand_motivation_decision='$new_motivation',
                                    $_DBU_cand_liste_attente='$current_decision_array[rang_liste]',
                                    $_DBU_cand_masse='1',
                                    $_DBU_cand_date_prise_decision='$date_prise_decision',
                                    $_DBU_cand_entretien_date='$entretien_date',
                                    $_DBU_cand_entretien_heure='$entretien_heure_texte',
                                    $_DBU_cand_entretien_salle='$entretien_salle',
                                    $_DBU_cand_entretien_lieu='$entretien_lieu',
                                    $_DBU_cand_traitee_par='$_SESSION[auth_id]'
                                WHERE $_DBU_cand_id='$inid';
                        DELETE FROM $_DB_traitement_masse WHERE $_DBC_traitement_masse_cid='$inid';
                        INSERT INTO $_DB_traitement_masse VALUES ('$masse_id', '$partie', '$inid', '$_SESSION[auth_id]')");
                  
            write_evt($dbr, $__EVT_ID_G_PREC, "Décision en Masse : \"$_SESSION[nom_formation]\" : " . $_SESSION["decision_array"]["$new_decision"], $current_decision_array["candidat_id"], $inid, "");     
            
            // Si : 
                // 1 - les décisions sont publiées 
                // 2 - la notification est activée
                // 3 - aucune notification n'a encore été envoyée 
                  //     - OU la décision est passée d'une décision "partielle" à une décision fixe 
                  //     - OU la nouvelle décision est "admission confirmée"
                // alors on envoie un message au candidat (le message ne contient pas la décision en elle même)
            
                if((array_key_exists("affichage_decisions", $_SESSION) && (($_SESSION["affichage_decisions"]==0 
                   && db_num_rows(db_query($dbr, "SELECT * FROM $_DB_propspec WHERE $_DBC_propspec_id=(SELECT $_DBC_cand_propspec_id FROM $_DB_cand WHERE $_DBC_cand_id='$inid') AND $_DBC_propspec_affichage_decisions!='0'")))
                   || $_SESSION["affichage_decisions"]==1 || $_SESSION["affichage_decisions"]==2))
                   && $_SESSION["avertir_decision"]==1 
                   && ($current_decision_array["notification_envoyee"]!=1 || $new_decision==$__DOSSIER_ADMISSION_CONFIRMEE || ($current_decision_array["decision"]<=$__DOSSIER_NON_TRAITE && $new_decision>$__DOSSIER_NON_TRAITE)))
                {
                   $message="Bonjour,\n
La Commission Pédagogique a rendu une décision pour votre candidature à la formation suivante : \n
[gras]$current_decision_array[formation][/gras]\n
Pour consulter cette décision : 
- sélectionnez si besoin l'établissement adéquat (menu \"Choisir une autre composante\")
- dans votre fiche, rendez vous dans le menu \"Précandidatures\".

Cordialement,\n\n
--
$_SESSION[adr_scol]\n
$_SESSION[composante]
$_SESSION[universite]";

                   $dest_array=array("0" => array("id"    => $current_decision_array["candidat_id"],
                                                 "civ"    => $current_decision_array["civilite"],
                                                 "nom"    => $current_decision_array["nom"],
                                                 "prenom" => $current_decision_array["prenom"],
                                                 "email"  => $current_decision_array["email"]));

                   write_msg("", array("id" => "0", "nom" => "Système", "prenom" => ""), $dest_array, "$_SESSION[composante] - Décision", $message, $current_decision_array["nom"]." ".$current_decision_array["prenom"]);
                   write_evt($dbr, $__EVT_ID_G_PREC, "Notification de décision envoyée", $$current_decision_array["candidat_id"], $inid);
               
                   db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_notification_envoyee='1' WHERE $_DBU_cand_id='$inid'");
            }
          }
        }
        else // La candidature a une décision supérieure à 0 : elle a déjà été traitée
          $deja_traitee=$deja_traitee+1;
      }
      else
        $non_traitees+=1;
    }

    $success=1;
  }

  unset($_SESSION["candidatures_array"]);

  // Décisions saisies de manière incorrecte ou erronée : on affiche de nouveau ces candidatures
  if(isset($array_erreurs) && count($array_erreurs))
    $resultat=1;

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>
<div class='main'>
  <?php
    titre_page_icone("Gestion en masse (Commissions Pédagogiques)", "kpersonalizer_32x32_fond.png", 15, "L");

    if(isset($selection_invalide))
      message("Erreur : veuillez sélectionner une formation valide.", $__ERREUR);

    if(isset($success))
    {
      $rapport="";

      if($nb_success>0)
      {
        if($nb_success==1)
          $rapport="- $nb_success décision validée avec succès";
        else
          $rapport="- $nb_success décisions validées avec succès";
      }

      if(isset($deja_traitees) && $deja_traitees>0)
      {
        if($rapport!="")
          $rapport.="<br>";

        if($deja_traitees==1)
          $rapport.="- 1 décision non validée (candidature déjà traitée)";
        else
          $rapport.="- $deja_traitees décisions non validées (candidatures déjà traitées)";
      }

      if(isset($non_traitees) && $non_traitees>0)
      {
        if($rapport!="")
          $rapport.="<br>";

        if($non_traitees==1)
          $rapport.="- Une décision non modifiée";
        else
          $rapport.="- $non_traitees décisions non modifiées";
      }

      message("$rapport", $__SUCCES);
    }
    /*
    elseif(isset($deja_traitee) && $deja_traitee!=0)
    {
      if($deja_traitee==1)
        message("ATTENTION : une décision n'a pas été validée car elle semblait déjà traitée. $nb_success ont été validées avec succès.", $__ERREUR);
      else
        message("ATTENTION : $deja_traitee décisions n'ont pas été validées car elles semblaient déjà traitées. $nb_success ont été validées avec succès.", $__ERREUR);
    }
    */

    if(!isset($resultat))
    {
      print("<form action='$php_self' method='POST' name='form1'>\n");
  ?>
  <br>
  <table align='center'>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><b>Formation à traiter : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <select name='formation' size='1'>
        <?php
          $result=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_id, $_DBC_specs_mention_id,
                              $_DBC_mentions_nom, $_DBC_propspec_finalite
                            FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
                          WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
                          AND $_DBC_mentions_id=$_DBC_specs_mention_id
                          AND $_DBC_propspec_annee=$_DBC_annees_id
                          AND $_DBC_specs_comp_id='$_SESSION[comp_id]'
                          AND $_DBC_propspec_active='1'
                            ORDER BY $_DBC_annees_annee, $_DBC_specs_mention_id, $_DBC_specs_nom, $_DBC_propspec_finalite");
          $rows=db_num_rows($result);

          // variables initialisées à n'importe quoi
          $prev_annee="--";
          $prev_mention="";

          // TODO : dans la base compeda, revoir l'utilisation de la table annee (intégration de annees.id dans
          // proprietes_specialites, par exemple) et répercuter les changements ici
          for($i=0; $i<$rows; $i++)
          {
            list($annee, $nom,$propspec_id, $mention, $mention_nom, $finalite)=db_fetch_row($result,$i);

            $nom_finalite=$tab_finalite[$finalite];

            if($annee!=$prev_annee)
            {
              if($i!=0)
                print("</optgroup>\n");

              if(empty($annee))
                print("<optgroup label='Années particulières'>\n");
              else
                print("<optgroup label='$annee'>\n");

              $prev_annee=$annee;
              $prev_mention="";
            }

            if($prev_mention!=$mention)
              print("<option value='' label='' disabled>-- $mention_nom --</option>\n");

            if(isset($formation) && $formation==$propspec_id)
              $selected="selected=1";
            else
              $selected="";

            print("<option value='$propspec_id' label=\"$nom $nom_finalite\" $selected>$nom $nom_finalite</option>\n");

            $prev_mention=$mention;
          }

          db_free_result($result);
        ?>
      </select>
    </td>
  </tr>
  </table>

  <div class='centered_icons_box'>
    <a href='masse.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour au menu précédent' border='0'></a>
    <input type="image" border='0' src="<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>" alt="Suivant" name="suivant" value="Suivant">
    </form>
  </div>

  <script language="javascript">
    document.form1.formation.focus()
  </script>

  <table width='90%' cellpadding='4' align='center'>
  <tr>
    <td align='left' valign='top' width='100%'>
      <font class='Titre' face="Arial" size="4" style="font-weight: bold;">
        Documents correspondant aux traitements de masse
      </font>
      <br><br>

      <?php
        $result=db_query($dbr, "SELECT $_DBC_traitement_masse_id, $_DBC_traitement_masse_partie, $_DBC_traitement_masse_acces_id,
                            $_DBC_acces_nom, $_DBC_acces_prenom
                          FROM $_DB_traitement_masse, $_DB_acces, $_DB_cand, $_DB_propspec
                        WHERE $_DBC_acces_id=$_DBC_traitement_masse_acces_id
                        AND $_DBC_cand_id=$_DBC_traitement_masse_cid
                        AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                        AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                        AND $_DBC_cand_periode='$__PERIODE'
                          GROUP BY $_DBC_traitement_masse_id, $_DBC_traitement_masse_partie, $_DBC_traitement_masse_acces_id,
                                $_DBC_acces_nom, $_DBC_acces_prenom
                          ORDER BY substring($_DBC_traitement_masse_id FROM '[0-9]*$') DESC,
                                $_DBC_traitement_masse_partie ASC");

        $rows=db_num_rows($result);

        if($rows)
        {
          for($i=0; $i<$rows; $i++)
          {
            list($masse_id, $masse_partie, $masse_acces_id, $masse_nom, $masse_prenom)=db_fetch_row($result, $i);

            // Récupération de l'année / spécialité correspondante

            $result2=db_query($dbr, "SELECT $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite
                              FROM $_DB_annees, $_DB_specs, $_DB_propspec, $_DB_cand
                            WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                            AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                            AND $_DBC_propspec_annee=$_DBC_annees_id
                            AND $_DBC_propspec_id_spec=$_DBC_specs_id
                            AND $_DBC_cand_id IN (SELECT $_DBC_traitement_masse_cid FROM $_DB_traitement_masse
                                          WHERE $_DBC_traitement_masse_id='$masse_id'
                                          LIMIT 1)");

            if(db_num_rows($result2))
            {
              list($annee_nom, $spec_nom, $finalite)=db_fetch_row($result2, 0);

              $nom_finalite=$tab_finalite[$finalite];

              if(empty($annee_nom))
                $formation="$spec_nom $nom_finalite";
              else
                $formation="$annee_nom $spec_nom $nom_finalite";
            }
            else // Ne devrait jamais arriver, en théorie
              $formation="[Formation indéterminée]";

            db_free_result($result2);

            $masse_array=explode("_", $masse_id); // 1er champ : ID de l'utilisateur, second : date de création

            if(isset($masse_array[1]))
              $date_txt=date_fr("j F Y, H\hi:s", $masse_array[1]);
            else
              $date_txt="";

            if($_SESSION['auth_id']==$masse_acces_id || $_SESSION["niveau"]==$__LVL_ADMIN)
              print("<a href='lettres/generateur_lettres.php?mid=$masse_id&mp=$masse_partie' class='lien_bleu_10' target='_blank'>&#8226;&nbsp;&nbsp;$date_txt - $masse_prenom $masse_nom - $formation - Partie $masse_partie</a>
                    <br>\n");
            else
              print("<font class='Textegris'>&#8226;&nbsp;&nbsp;<i>$date_txt - $masse_prenom $masse_nom - $formation - Partie $masse_partie</i></font>
                    <br>\n");
          }
        }
        else
          print("<font class='Texte3'><b>Aucun pour l'année universitaire en cours</b></font>\n");

        db_free_result($result);
      ?>
    </td>
  </tr>
  </table>

  <?php
    }
    elseif(isset($resultat)) // résultat de la recherche : saisie des décisions
    {
      // on détermine si la filière est sélective ou non et on récupère le nom de l'année et de la spécialité
      $result=db_query($dbr,"SELECT $_DBC_propspec_selective, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite,
                          $_DBC_propspec_entretiens
                        FROM $_DB_propspec, $_DB_annees, $_DB_specs
                      WHERE $_DBC_propspec_id='$propspec_id'
                      AND $_DBC_propspec_id_spec=$_DBC_specs_id
                      AND $_DBC_propspec_annee=$_DBC_annees_id");
      $rows=db_num_rows($result);

      list($filiere_selective, $nom_annee, $spec_nom, $finalite, $entretiens)=db_fetch_row($result,0);

      $nom_finalite=$tab_finalite[$finalite];

      db_free_result($result);

      // On regarde si on doit re-saisir des décisions erronées ou non
      if(isset($array_erreurs) && count($array_erreurs))
      {
        $liste_id_erreurs="(";

        foreach($array_erreurs as $inid => $inid_array)
          $liste_id_erreurs.="$inid,";

        $liste_id_erreurs=substr($liste_id_erreurs, 0, -1) . ")";

        $condition_erreurs="AND $_DBC_cand_id IN $liste_id_erreurs";

        $message="Erreur : des renseignements sont manquants pour les décisions suivantes : ";
        $message_type=$__ERREUR;
      }
      else
      {
        $condition_erreurs="";
        $message="Attention : seules les candidatures <b>nécessitant encore un traitement</b> apparaîssent.
                      <br>En fonction des paramètres de configuration, un <strong>message automatique</strong> pourra être envoyé aux candidats lors de la validation du formulaire.";
        $message_type=$__WARNING;
      }

      $result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_email, 
                                       $_DBC_candidat_date_naissance, $_DBC_cand_id, $_DBC_cand_decision, $_DBC_cand_motivation_decision, $_DBC_cand_liste_attente,
                          $_DBC_cand_entretien_date, $_DBC_cand_entretien_lieu, $_DBC_cand_entretien_salle, $_DBC_cand_notification_envoyee
                      FROM $_DB_cand, $_DB_candidat, $_DB_propspec
                    WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
                    AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                    AND $_DBC_cand_propspec_id='$propspec_id'
                    AND $_DBC_cand_statut='$__PREC_RECEVABLE'
                    AND $_DBC_cand_periode='$__PERIODE'
                    AND $_DBC_cand_decision <= '$__DOSSIER_NON_TRAITE'
                    $condition_erreurs
                      ORDER by $_DBC_candidat_nom");

      $rows=db_num_rows($result);

      $_SESSION["nom_formation"]=$insc_txt=$nom_annee=="" ? trim("$spec_nom $nom_finalite") : trim("$nom_annee - $spec_nom $nom_finalite");

      // Erreur ou avertissement
      message("$message", $message_type);

      print("<div class='centered_box Texte3'>
            Décisions pour la formation : <b>$insc_txt</b> ($rows)
          </div>\n");

      if($rows)
      {
        $_SESSION["candidatures_array"]=array();

        print("<form action='$php_self' method='POST'>

        <table style='margin-left:auto; margin-right:auto;'>
        <tr>
          <td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Candidat(e)</b></td>
          <td class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Décision</b></td>
          <td class='td-milieu fond_menu2'><font class='Texte_menu2'><b>Motivation</b></td>");

        if($entretiens)
        {
          $colspan_motifs="colspan='4'";
          $colspan_complet="colspan='5'";
          print("<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Entretien</b></td>\n");
        }
        else
        {
          $colspan_motifs="colspan='3'";
          $colspan_complet="colspan='4'";
        }

        print("<td class='td-droite fond_menu2'><font class='Texte_menu2'><b>Rang sur<br>liste complémentaire</b></td>
          </tr>
          <tr>
            <td $colspan_complet height='15' class='fond_page'></td>
          </tr>\n");

        // Récupération des décisions
        $result2=db_query($dbr,"SELECT $_DBC_decisions_id, $_DBC_decisions_texte FROM $_DB_decisions
                          WHERE $_DBC_decisions_id IN (SELECT distinct($_DBC_decisions_comp_dec_id) FROM $_DB_decisions_comp
                                              WHERE $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]')
                          AND $_DBC_decisions_id!='$__DOSSIER_TRANSMIS'
                        ORDER BY $_DBC_decisions_texte");

        $rows2=db_num_rows($result2);

        $_SESSION["decision_array"]=$decisions_array=array();

        for($i_decision=0; $i_decision<$rows2; $i_decision++)
        {
          list($decision_id,$decision_txt)=db_fetch_row($result2,$i_decision);
          $_SESSION["decision_array"]["$decision_id"]=$decisions_array[$decision_id]=$decision_txt;
        }

        db_free_result($result2);

        // Idem pour les motifs

        $result2=db_query($dbr,"SELECT $_DBC_motifs_refus_id, $_DBC_motifs_refus_motif FROM $_DB_motifs_refus
                          WHERE $_DBC_motifs_refus_comp_id='$_SESSION[comp_id]'
                        ORDER BY $_DBC_motifs_refus_motif");
        $rows2=db_num_rows($result2);

        $motifs_array=array();

        for($i_motif=0; $i_motif<$rows2; $i_motif++)
        {
          list($motif_id, $motif)=db_fetch_row($result2,$i_motif);

          $value=htmlspecialchars($motif, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);
          $motifs_array[$motif_id]=$motif;
        }
        db_free_result($result2);

        $fond1="fond_menu";
        $font1="Texte_menu_10";
        $lien1="lien_menu_gauche_10";

        $fond1="fond_blanc";
        $font2="Texte_10";
        $lien2="lien_bleu_10";

        for($i=0; $i<$rows; $i++)
        {
          list($candidat_id, $civilite, $nom, $prenom, $candidat_email, $date_naiss, $inid, $decision,$motivation,$liste_attente, $entretien_date,
              $entretien_lieu, $entretien_salle, $notification_envoyee)=db_fetch_row($result,$i);

          // Conservation des informations sur la candidature pour les comparer lors de la validation.
          if(trim($liste_attente)=="")
            $liste_attente="0";

          $_SESSION["candidatures_array"][$inid]=array("candidat_id" => "$candidat_id",
                                        "civilite" => "$civilite",
                                        "nom" => "$nom",
                                        "prenom" => "$prenom",
                                        "email" => "$candidat_email",
                                        "decision"=>"$decision",
                                        "motif"=>"$motivation",
                                        "liste_comp"=>"$liste_attente",
                                        "notification_envoyee" => "$notification_envoyee",
                                        "entretien_date"=>"$entretien_date",
                                        "entretien_lieu"=>"$entretien_lieu",
                                        "entretien_salle"=>"$entretien_salle",
                                        "formation" => "$insc_txt");

          $naissance=date_fr("d/m/Y",$date_naiss);

          $motivation_txt=str_replace("|","<br>\n",$motivation);
          // $motivation_txt=str_replace("@","",$motivation_txt);
          $motivation_txt=preg_replace("/^@/","", $motivation);

          // Style d'affichage des erreurs, s'il y en a
          $style="";

          print("<tr>
                <td class='$fond1' valign='top'>
                  <a href='edit_candidature.php?cid=$candidat_id' target='_self' class='$lien1'>$nom $prenom<br>$naissance</a></font>
                </td>
                <td class='$fond1' valign='top' nowrap='true' width='50'>
                  <select class='select_small' name='decision[$inid]' size='1'>\n");

          if(isset($array_erreurs) && array_key_exists($inid, $array_erreurs))
          {
            $decision=$array_erreurs[$inid]["decision"];

            if($array_erreurs[$inid]["motivation"]!="" || $array_erreurs[$inid]["motivation_libre"]!="")
              $motivation=$array_erreurs[$inid]["motivation"] . "|" . $array_erreurs[$inid]["motivation_libre"];

            if($array_erreurs[$inid]["erreur_motif"]=="motif")
              $style="style='color:#CC0000; font-weight:bold;'";
            else
              $style="";
          }

          foreach($decisions_array as $decision_id => $decision_txt)
          {
            if($entretiens || (!$entretiens && in_array($decision_id, $__DOSSIER_DECISIONS_SANS_ENTRETIEN)))
            {
              $selected=$decision_id==$decision ? "selected=1" : "";
              print("<option value='$decision_id' $selected>$decision_txt</option>\n");
            }
          }

          print("</select>
              </td>
              <td class='$fond1' valign='top' nowrap='true'>
                <table cellpadding='0' cellspacing='0' border='0'>\n");

          // Gestion des motifs
          $motif_libre="";

          if(!empty($motivation))
          {
            $motif_candidat_array=explode("|", $motivation);

            foreach($motif_candidat_array as $motif)
            {
              if(!strncmp('@', $motif, 1)) // motif libre
                $motif_libre=substr($motif, 1);
            }
          }
          else
            $motif_candidat_array=array();

          // Motifs dans une colonne, s'ils sont courts
          if($_SESSION["gestion_motifs"]!=1)
          {
            print("<tr>
                  <td align='left' valign='top' nowrap='true'>
                    <font class='$font1' $style>Motif :&nbsp;</font>
                  </td>
                  <td align='left' valign='top'>
                    <select class='select_small' name='motivation_decision[$inid]' size='1'>
                      <option value=''></option>\n");

            foreach($motifs_array as $motif_id => $motifs_txt)
            {
              // $selected=(FALSE!=array_search($motif_id, $motif_candidat_array)) ? "selected=1" : "";

              $selected=in_array($motif_id, $motif_candidat_array) ? "selected=1" : "";

              print("<option value='$motif_id' $selected>$motifs_txt</option>\n");
            }

            print("</select>
                </td>
              </tr>\n");
          }

          print("<tr>
                <td align='left' valign='top' nowrap>
                  <font class='$font1' $style>Motif libre :&nbsp;</font>
                </td>
                <td align='left' valign='top'>
                  <textarea class='textarea_small' cols='25' rows='1' name='motivation_decision_libre[$inid]'>$motif_libre</textarea>
                </td>
              </tr>
              </table>
            </td>\n");

          if($entretiens)
          {
            if(isset($array_erreurs) && array_key_exists($inid, $array_erreurs))
            {
              $entretien_jour=$array_erreurs[$inid]["entretien_jour"];
              $entretien_mois=$array_erreurs[$inid]["entretien_mois"];
              $entretien_annee=$array_erreurs[$inid]["entretien_annee"];
              $entretien_heure=$array_erreurs[$inid]["entretien_heure"];
              $entretien_minute=$array_erreurs[$inid]["entretien_minutes"];
              $entretien_salle=$array_erreurs[$inid]["entretien_salle"];
              $entretien_lieu=$array_erreurs[$inid]["entretien_lieu"];

              if($array_erreurs[$inid]["erreur_motif"]=="entretien")
                $style="style='color:#CC0000; font-weight:bold;'";
              else
                $style="";
            }
            elseif($entretien_date!=0 && $entretien_date!="")
            {
              $entretien_jour=date("j", $entretien_date);
              $entretien_mois=date("m", $entretien_date);
              $entretien_annee=date("Y", $entretien_date);
              $entretien_heure=date("H", $entretien_date);
              $entretien_minute=date("i", $entretien_date);

              if($entretien_heure==0)
                $entretien_heure=$entretien_minute="";
            }
            else
            {
              $entretien_jour=$entretien_mois=$entretien_heure=$entretien_minute="";
              $entretien_annee="$__PERIODE";
            }

            print("<td class='$fond1' valign='top'>
                  <table cellpadding='0' cellspacing='0' border='0'>
                  <tr>
                    <td nowrap>
                      <font class='$font1' $style>Date :&nbsp;</font>
                    </td>
                    <td nowrap><font class='$font1' $style>JJ :</font></td>
                    <td>
                      <input class='input_small' type='text' name='entretien_jour[$inid]' value='$entretien_jour' size='3' maxlength='2'>
                    </td>
                    <td nowrap><font class='$font1' $style>MM :</font></td>
                    <td>
                      <input class='input_small' type='text' name='entretien_mois[$inid]' value='$entretien_mois' size='3' maxlength='2'>
                    </td>
                    <td nowrap><font class='$font1' $style>AAAA :</font></td>
                    <td>
                      <input class='input_small' type='text' name='entretien_annee[$inid]' value='$entretien_annee' size='5' maxlength='4'>
                    </td>
                  </tr>
                  <tr>
                    <td nowrap>
                      <font class='$font1' $style>Heure :&nbsp;</font>
                    </td>
                    <td nowrap><font class='$font1' $style>h :</font></td>
                    <td>
                      <input class='input_small' type='text' name='entretien_heure[$inid]' value='$entretien_heure' size='3' maxlength='2'>
                    </td>
                    <td nowrap><font class='$font1' $style>min :</font></td>
                    <td>
                      <input type='text' name='entretien_minute[$inid]' value='$entretien_minute' size='3' maxlength='2'>
                    </td>
                    <td colspan='2'></td>
                  </tr>
                  <tr>
                    <td nowrap>
                      <font class='$font1'>Salle :&nbsp;</font>
                    </td>
                    <td colspan='6'>
                      <input class='input_small' type='text' name='entretien_salle[$inid]' value='" . htmlspecialchars(stripslashes($entretien_salle), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]) . "' size='25' maxlength='50'>
                    </td>
                  </tr>
                  <tr>
                    <td nowrap>
                      <font class='$font1'>Lieu :&nbsp;</font>
                    </td>
                    <td colspan='6'>
                      <input class='input_small' type='text' name='entretien_lieu[$inid]' value='" . htmlspecialchars(stripslashes($entretien_lieu), ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]) . "' size='40' maxlength='128'>
                    </td>
                  </tr>
                  </table>
                </td>\n");
          }

          // Si le rang vaut 0, on ne l'affiche pas
          if(trim($liste_attente)=="0")
            $liste_attente="";

          print("<td class='$fond1' valign='top'>
                <input class='input_small' type='text' name='liste_attente[$inid]' value='$liste_attente' maxlength='10' size='8'>
              </td>
            </tr>\n");

          // Motifs sur une ligne pleine, s'ils sont trop longs
          if($_SESSION["gestion_motifs"]==1)
          {

            print("<td class='$fond1' style='padding-bottom:10px;' nowrap>
                  <font class='$font1' $style><b>Motif complet :&nbsp;</b></font>
                  </td>
                  <td class='$fond1' $colspan_motifs style='padding-bottom:10px;'>
                  <select class='select_small' name='motivation_decision[$inid]' size='1'>
                    <option class='Texte' value=''></option>\n");

            foreach($motifs_array as $motif_id => $motifs_txt)
            {
              if(FALSE!=array_search($motif_id, $motif_candidat_array))
                $selected="selected=1";
              else
                $selected="";

              print("<option value='$motif_id' $selected>$motifs_txt</option>\n");
            }

            print("</select>
                </td>
              </tr>\n");

          }

          switch_vals($fond1, $fond2);
          switch_vals($font1, $font2);
          switch_vals($lien1, $lien2);
        }

        print("</table>

            <input type='hidden' name='propspec' value='$propspec_id'>

            <div class='centered_icons_box'>
              <a href='$php_self' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
              <input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Valider' name='valider' value='Valider'>
              </form>
            </div>\n");
      }
      else
      {
        message("Aucune candidature à traiter dans cette formation", $__INFO);

        print("<div class='centered_box'>
              <a href='$php_self' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
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
