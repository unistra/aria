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


  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

  verif_auth("$__GESTION_DIR/login.php");

  if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
  {
    header("Location:$__GESTION_DIR/noaccess.php");
    exit();
  }

  // Le paramètre transmis est le numéro de la commission
  // (= numéro d'intervalle d'ouverture : commission 1 = premier intervalle, etc)
  if(isset($_GET["n"]) && ctype_digit($_GET["n"]) && isset($_SESSION["all_commissions"]))
    $n_intervalle=$_GET["n"];
  elseif(array_key_exists("intervalle_n", $_POST) && isset($_SESSION["all_commissions"]))
    $n_intervalle=$_POST["intervalle_n"];
  else
  {
    header("Location:index.php");
    exit();
  }

  $dbr=db_connect();

  if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
  {
    $global_erreurs_format=0;

    $req="";

    $maj_dates_propspec_array=array();

    if(isset($_POST["jour_com_all"]) && !empty($_POST["jour_com_all"]) && isset($_POST["mois_com_all"]) && !empty($_POST["mois_com_all"]) && isset($_POST["annee_com_all"]) && !empty($_POST["annee_com_all"]))
    {
      $jour_com=$_POST["jour_com_all"];
      $mois_com=$_POST["mois_com_all"];
      $annee_com=$_POST["annee_com_all"];

      if(!ctype_digit($annee_com) || $annee_com<date("Y"))
        $annee_com=date("Y");

      $new_date_commission=MakeTime(0,30,0,$mois_com, $jour_com, $annee_com);

      $result=db_query($dbr,"SELECT $_DBC_propspec_id FROM $_DB_propspec 
                      WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                      AND $_DBC_propspec_active='1'");
      $rows=db_num_rows($result);

      for($i=0; $i<$rows; $i++)
      {
        list($propspec_id)=db_fetch_row($result, $i);

        $commission_id=$_POST["com_id"][$propspec_id];

        if(isset($_POST["activ"]) && isset($_POST["activ"][$propspec_id]))
        {
          db_query($dbr, "UPDATE $_DB_commissions SET $_DBU_commissions_date='$new_date_commission'
                      WHERE $_DBU_commissions_propspec_id='$propspec_id'
                      AND $_DBU_commissions_id='$commission_id'
                      AND $_DBU_commissions_periode='$_SESSION[user_periode]'");

          // Vérification des candidatures existantes
          if(isset($_SESSION["all_commissions"]) && array_key_exists($propspec_id, $_SESSION["all_commissions"]) && array_key_exists($n_intervalle, $_SESSION["all_commissions"][$propspec_id]))
          {
            $cur_date=$_SESSION["all_commissions"][$propspec_id][$n_intervalle]["com"];

            // if($cur_date!=$new_date_commission)
            // {
              $res_existantes=db_query($dbr, "SELECT count(*) FROM $_DB_cand
                                    WHERE $_DBC_cand_propspec_id='$propspec_id'
                                    AND $_DBC_cand_periode='$_SESSION[user_periode]'
                                    AND $_DBC_cand_date_decision!='$new_date_commission'");

              // on aura toujours un résultat, même nul
              list($nb_existantes)=db_fetch_row($res_existantes, 0);

              if($nb_existantes)
              {
                $maj_dates_propspec_array[$propspec_id]["existantes"]=$nb_existantes;
                $maj_dates_propspec_array[$propspec_id]["cur_date"]=$cur_date;
                $maj_dates_propspec_array[$propspec_id]["nouvelle_date"]=$new_date_commission;
                $maj_dates_propspec_array[$propspec_id]["nouvelle_date_txt"]=date_fr("j F Y", $new_date_commission);
              }

              db_free_result($res_existantes);
            // }
          }
        }

        write_evt($dbr, $__EVT_ID_G_SESSION, "Modification globale commission $commission_id ($propspec_id)");

        // Des dates doivent peut-être être mises à jour dans les candidatures
        // On pose la question à l'utilisateur
      }
    }

    // Champs individuels si le champ "all" n'a pas été utilisé
    if(!isset($new_date_commission))
    {
      $result=db_query($dbr,"SELECT $_DBC_propspec_id FROM $_DB_propspec 
                      WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                      AND $_DBC_propspec_active='1'");
      $rows=db_num_rows($result);

      $requete="";
      $total_erreurs_format=0;
      $array_fond=array();
      $array_cur_values=array();

      for($i=0; $i<$rows; $i++)
      {
        list($propspec_id)=db_fetch_row($result, $i);

        // Texte par défaut (cette valeur sera modifiée en cas d'erreur)
        $array_fond[$propspec_id]="fond_menu";

        unset($new_date_com);

        $commission_id=$_POST["com_id"][$propspec_id];

        // Si tous les champs sont vides ou si la case est décochée : suppression de la commission pour cette formation
        if((array_key_exists($propspec_id, $_POST["jour_com"]) && array_key_exists($propspec_id, $_POST["mois_com"]) && array_key_exists($propspec_id, $_POST["annee_com"])
          && $_POST["jour_com"][$propspec_id]=="" && $_POST["mois_com"][$propspec_id]=="" && $_POST["annee_com"][$propspec_id]=="")
          || !isset($_POST["activ"]) || (isset($_POST["activ"]) && !array_key_exists($propspec_id, $_POST["activ"])))
          $requete.="DELETE FROM $_DB_commissions WHERE $_DBC_commissions_propspec_id='$propspec_id'
                                     AND $_DBC_commissions_id='$commission_id'
                                     AND $_DBC_commissions_periode='$_SESSION[user_periode]'; ";
        else
        {
          $erreur_format=0;

          if(array_key_exists($propspec_id, $_POST["jour_com"]) && array_key_exists($propspec_id, $_POST["mois_com"]) && array_key_exists($propspec_id, $_POST["annee_com"]))
          {
            $jour_com=$_POST["jour_com"][$propspec_id];
            $mois_com=$_POST["mois_com"][$propspec_id];
            $annee_com=$_POST["annee_com"][$propspec_id];

            if(!isset($array_cur_values[$propspec_id]))
              $array_cur_values[$propspec_id]=array("jour_com" => $jour_com, "mois_com" => $mois_com, "annee_com" => $annee_com);
            else
            {
              $array_cur_values[$propspec_id]["jour_com"]=$jour_com;
              $array_cur_values[$propspec_id]["mois_com"]=$mois_com;
              $array_cur_values[$propspec_id]["annee_com"]=$annee_com;
            }

            if(!ctype_digit($jour_com) || $jour_com<1 || $jour_com>31 || !ctype_digit($mois_com) || $mois_com<1 || $mois_com>12)
            {
              $array_fond[$propspec_id]="fond_rouge";
              $erreur_format=1;
            }
            else
            {
              if(!ctype_digit($annee_com) || $annee_com<date("Y"))
                $annee_com=date("Y");

              $new_date_com=MakeTime(0,30,0,$mois_com, $jour_com, $annee_com); // date au format unix : le jour même, le matin

              // print("DBG : $propspec_id => $new_date_com\n<br>");
            }
          }

          $array_cur_values[$propspec_id]["active"]=1;

          if($erreur_format)
            $total_erreurs_format++;

          // Aucune erreur et champs à mettre à jour déterminés : on complète la requête globale
          if(!$erreur_format)
          {
            if(isset($new_date_com) && (!array_key_exists("$propspec_id",$_SESSION["all_commissions"])
                               || (array_key_exists("$propspec_id",$_SESSION["all_commissions"])
                                  && !array_key_exists($n_intervalle, $_SESSION["all_commissions"][$propspec_id]))))
            {
              $requete.="INSERT INTO $_DB_commissions VALUES ('$propspec_id','$commission_id', '$new_date_com', '$_SESSION[user_periode]'); ";

              $res_existantes=db_query($dbr, "SELECT count(*) FROM $_DB_cand
                                        WHERE $_DBC_cand_propspec_id='$propspec_id'
                                        AND $_DBC_cand_periode='$_SESSION[user_periode]'
                                        AND $_DBC_cand_date_decision!='$new_date_com'");

              // on aura toujours un résultat, même nul
              list($nb_existantes)=db_fetch_row($res_existantes, 0);

              if($nb_existantes)
              {
                $maj_dates_propspec_array[$propspec_id]["existantes"]=$nb_existantes;
                $maj_dates_propspec_array[$propspec_id]["cur_date"]=0;
                $maj_dates_propspec_array[$propspec_id]["nouvelle_date"]=$new_date_com;
                $maj_dates_propspec_array[$propspec_id]["nouvelle_date_txt"]=date_fr("j F Y", $new_date_com);
              }

              db_free_result($res_existantes);
            }
            else
            {
              $requete.="UPDATE $_DB_commissions SET $_DBU_commissions_date='$new_date_com'
                      WHERE $_DBU_commissions_propspec_id='$propspec_id'
                      AND $_DBU_commissions_id='$commission_id'
                      AND $_DBU_commissions_periode='$_SESSION[user_periode]'; ";

              // Vérification des candidatures existantes
              if(isset($_SESSION["all_commissions"]) && array_key_exists($propspec_id, $_SESSION["all_commissions"]) && array_key_exists($n_intervalle, $_SESSION["all_commissions"][$propspec_id]))
              {
                $cur_date=$_SESSION["all_commissions"][$propspec_id][$n_intervalle]["com"];

                // if($cur_date!=$new_date_com)
                // {
                  $res_existantes=db_query($dbr, "SELECT count(*) FROM $_DB_cand
                                        WHERE $_DBC_cand_propspec_id='$propspec_id'
                                        AND $_DBC_cand_periode='$_SESSION[user_periode]'
                                        AND $_DBC_cand_date_decision!='$new_date_com'");

                  // on aura toujours un résultat, même nul
                  list($nb_existantes)=db_fetch_row($res_existantes, 0);

                  if($nb_existantes)
                  {
                    $maj_dates_propspec_array[$propspec_id]["existantes"]=$nb_existantes;
                    $maj_dates_propspec_array[$propspec_id]["cur_date"]=$cur_date;
                    $maj_dates_propspec_array[$propspec_id]["nouvelle_date"]=$new_date_com;
                    $maj_dates_propspec_array[$propspec_id]["nouvelle_date_txt"]=date_fr("j F Y", $new_date_com);
                  }

                  db_free_result($res_existantes);
                // }
              }
            }

            write_evt($dbr, $__EVT_ID_G_SESSION, "Modification commission $commission_id ($propspec_id)");
          }
        }
      } // fin du for

      // Boucle terminée : on exécute la requête globale pour les champs non erronés
      if(!empty($requete))
        db_query($dbr, $requete);

      db_free_result($result);
    } // fin du else(all)

    if((!isset($maj_dates_propspec_array) || (isset($maj_dates_propspec_array) && count($maj_dates_propspec_array)==0))
      && (!isset($total_erreurs_format) || ($total_erreurs_format==0)))
    {
      db_close($dbr);

      header("Location:index.php?succes=1");
      exit();
    }
  }
  elseif((isset($_POST["valider_maj"]) || isset($_POST["valider_maj_x"])) && isset($_SESSION["maj_dates"]) && count($_SESSION["maj_dates"]))
  {
    // MAJ DES DATES DANS LES CANDIDATURES
    foreach($_SESSION["maj_dates"] as $propspec_id => $propspec_array)
    {
      if(isset($_POST["maj"][$propspec_id]))
        $reponse=$_POST["maj"][$propspec_id];
      else
        $reponse="-1"; // aucune MAJ

      if($reponse!=-1)
      {     
        if(is_array($reponse)) // Liste de sessions
        {
          $critere_maj="AND $_DBU_cand_session_id IN (";

          foreach($reponse as $session_id)
            $critere_maj.="'$session_id',";

          $critere_maj=mb_substr($critere_maj, 0, -1, "UTF-8") . ")";
        }
        elseif($reponse==-2)  // toutes les candidatures => nouvelle date (toutes sessions confondues)
          $critere_maj="";
//        elseif($reponse==-3) // ancienne date => nouvelle date
//          $critere_maj="AND $_DBU_cand_date_decision='$cur_date'";

        db_query($dbr, "UPDATE $_DB_cand SET $_DBU_cand_date_decision='$propspec_array[nouvelle_date]'
                    WHERE $_DBU_cand_propspec_id='$propspec_id'
                    AND $_DBU_cand_periode='$_SESSION[user_periode]'
                    $critere_maj");
      }
    }

    db_close($dbr);

    header("Location:index.php");
    exit(); 
  }

  // Sélection des formations, pour affichage
  if(isset($maj_dates_propspec_array) && count($maj_dates_propspec_array))
  {
    $critere_existantes="AND $_DBC_propspec_id IN (";

    foreach($maj_dates_propspec_array as $propspec_id => $nb)
      $critere_existantes.="'$propspec_id', ";

    $critere_existantes=mb_substr($critere_existantes, 0, -2, "UTF-8") . ")";
  }
  else
    $critere_existantes="";

  $result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
                       $_DBC_mentions_nom
                    FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
                  WHERE $_DBC_propspec_annee=$_DBC_annees_id
                  AND $_DBC_propspec_id_spec=$_DBC_specs_id
                  AND $_DBC_specs_mention_id=$_DBC_mentions_id
                  AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                  AND $_DBC_propspec_active='1'
                  $critere_existantes
                    ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom_court, $_DBC_propspec_finalite");

  $rows=db_num_rows($result);

  if(!$rows)
    $aucune_specialite=1;

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    titre_page_icone("Modifier les dates de la commission n°$n_intervalle", "clock_32x32_fond.png", 15, "L");

    print("<form action='$php_self' method='POST' name='form1'>
        <input type='hidden' name='intervalle_n' value='$n_intervalle'>\n");

    if(isset($total_erreurs_format) && $total_erreurs_format==1)
      message("une ligne contient au moins une date dont <strong>le format est incorrect</strong>", $__ERREUR);
    elseif(isset($total_erreurs_format) && $total_erreurs_format>1)
      message("$total_erreurs lignes contiennent au moins une date dont <strong>le format est incorrect</strong>", $__ERREUR);

    // MAJ des dates dans les candidatures, éventuellement
    if(isset($maj_dates_propspec_array) && count($maj_dates_propspec_array) && isset($result) && $rows)
    {
      $_SESSION["maj_dates"]=$maj_dates_propspec_array;

      message("<center>
              Certaines candidatures ont été enregistrées avec une autre date de commission.
              <br>Souhaitez vous les mettre à jour avec la nouvelle date ?
            </center>", $__QUESTION);

      print("<table cellpadding='0' cellspacing='0' border='0' align='center'>
           <tr>
            <td>\n");


      $old_annee="===="; // on initialise à n'importe quoi (sauf vide)
      $old_propspec_id="";
      $old_mention="--";

      for($i=0; $i<$rows; $i++)
      {
        list($propspec_id, $annee, $spec_nom, $finalite, $mention)=db_fetch_row($result, $i);

        $nom_finalite=$tab_finalite[$finalite];
        $annee=$annee=="" ? "Années particulières" : $annee;

        if($annee!=$old_annee)
        {
          if($i!=0)
            print("</table>\n");

          print("<table align='center' style='width:100%; padding-bottom:20px;'>
              <tr>
                <td class='fond_menu2' align='center' colspan='2' style='padding:4px 20px 4px 20px;'>
                  <font class='Texte_menu2'><b>$annee</b></font>
                </td>
              </tr>
              <tr>
                <td class='fond_menu2' style='padding:4px 20px 4px 20px;'>
                  <font class='Texte_menu2'><b>Formation</b></font>
                </td>
                <td class='fond_menu2' style='padding:4px 20px 4px 20px; text-align:center;'>
                  <font class='Texte_menu2'><b>Candidatures à mettre à jour :</b></font>
                </td>
              </tr>
              <tr>
                <td class='fond_menu2' colspan='2'>
                  <font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
                </td>");

          $old_annee=$annee;
          $old_mention='--';
          $first_spec=1;
        }
        else
          $first_spec=0;

        if($mention!=$old_mention)
        {
          if(!$first_spec)
            print("<tr>
                  <td class='fond_menu2' colspan='2'>
                    <font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
                  </td>
                </tr>\n");

          $old_mention=$mention;
        }

        print("<tr>
              <td class='td-gauche fond_menu'>
                <font class='Texte_menu'>
                  <b>$spec_nom $nom_finalite</b>
                  <br><i>(nouvelle date : " . $maj_dates_propspec_array[$propspec_id]["nouvelle_date_txt"] . ")</i>
                </font>
              </td>
              <td class='td-milieu fond_menu'>
                <font class='Texte_menu'>\n");

        // On cherche les sessions auxquelles la commission est susceptible d'être "rattachée"

        $res_sessions=db_query($dbr, "SELECT distinct($_DBC_cand_session_id), $_DBC_session_ouverture,$_DBC_session_fermeture
                              FROM $_DB_cand, $_DB_session
                            WHERE $_DBC_cand_propspec_id='$propspec_id'
                            AND $_DBC_cand_periode='$_SESSION[user_periode]'
                            AND $_DBC_session_periode='$_SESSION[user_periode]'
                            AND $_DBC_cand_session_id=$_DBC_session_id
                            AND $_DBC_session_propspec_id='$propspec_id'
                              ORDER BY $_DBC_session_ouverture, $_DBC_session_fermeture");

        $rows_sessions=db_num_rows($res_sessions);

        for($s=0; $s<$rows_sessions; $s++)
        {
          list($session_id, $ouverture, $fermeture)=db_fetch_row($res_sessions, $s);

          $ouv_txt=date_fr("j F Y", $ouverture);
          $ferm_txt=date_fr("j F Y", $fermeture);

          // print("<input type='radio' name='maj[$propspec_id]' value='$session_id' checked>&nbsp;Les candidatures de la session " . ($s+1) . " ($ouv_txt - $ferm_txt)");
          print("<input style='padding-right:10px;' type='checkbox' name='maj[$propspec_id][$s]' value='$session_id'>Celles de la session " . ($s+1) . " ($ouv_txt - $ferm_txt)<br>");
        }

        db_free_result($res_sessions);

        print("<!-- <br><input style='padding-right:10px;' type='radio' name='maj[$propspec_id]' value='-3' checked>Uniquement celles enregistrées avec l'ancienne date -->
            <input style='padding-right:10px;' type='radio' name='maj[$propspec_id]' value='-1'>Aucune
            <br><input style='padding-right:10px;' type='radio' name='maj[$propspec_id]' value='-2'>Toutes (!)
          </font>
        </td>
      </tr>\n");
      }

      db_free_result($result);

      print("</table>\n");

    ?>
    </td>
  </tr>
  </table>

  <div class='centered_box'>
    <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider_maj" value="Valider">
    </form>
  </div>

  <?php
    }
    else
    {
  ?>

  <table align='center' style='padding-bottom:20px;'>
  <tr>
    <td class='td-gauche fond_menu2' colspan='2'>
      <font class='Texte_menu2'>
        <b>Modifier toutes les dates (prioritaire sur les modifications individuelles) ...</b>
        <br>Attention : seules les formations "actives" (cases cochées) seront prises en compte.
      </font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu2'>
      <font class='Texte_menu2'><b>Commission Pédagogique : </b></font>
    </td>
    <td class='td-droite fond_menu'>
      <font class='Texte_menu'>
        Jour :&nbsp;<input type='text' name='jour_com_all' value='' size='4' maxlength='2'>&nbsp;
        Mois :&nbsp;<input type='text' name='mois_com_all' value='' size='4' maxlength='2'>&nbsp;
        Année :&nbsp;<input type='text' name='annee_com_all' value='' maxlength='4' size='6'>
      </font>
    </td>
  </tr>
  </table>

  <table align='center' width='70%'>
    <?php
      $old_annee="===="; // on initialise à n'importe quoi (sauf vide)
      $old_propspec_id="";
      $old_mention="--";

      for($i=0; $i<$rows; $i++)
      {
        list($propspec_id, $annee, $spec_nom, $finalite, $mention)=db_fetch_row($result, $i);

        $nom_finalite=$tab_finalite[$finalite];
        $annee=$annee=="" ? "Années particulières" : $annee;

        if(array_key_exists($propspec_id, $_SESSION["all_commissions"]) && array_key_exists($n_intervalle, $_SESSION["all_commissions"][$propspec_id]))
        {
          $commission=$_SESSION["all_commissions"][$propspec_id][$n_intervalle]["com"];
          $commission_id=$_SESSION["all_commissions"][$propspec_id][$n_intervalle]["com_id"];
        }
        else
        {
          $commission=0;
          // $commission_id=$n_intervalle;
          
          $result_max = db_query($dbr, "SELECT max($_DBC_commissions_id) FROM $_DB_commissions
                                        WHERE $_DBC_commissions_propspec_id='$propspec_id' 
                                        AND $_DBC_commissions_periode='$_SESSION[user_periode]'");
                                        
          list($max_id) = db_fetch_row($result_max, 0);
          
          $commission_id = $max_id!="" ? ($max_id+1) : 1;
          
          db_free_result($result_max);
        }

        $date_com_txt=date_fr("j F Y", $commission);
        $date_com_jour=date("j", $commission);
        $date_com_mois=date("n", $commission);
        $date_com_annee=date("Y", $commission);

        if(strlen($date_com_jour)==1) $date_com_jour="0" . $date_com_jour;
        if(strlen($date_com_mois)==1) $date_com_mois="0" . $date_com_mois;

        $fond=isset($array_fond[$propspec_id]) ? $array_fond[$propspec_id] : "fond_menu";

        if($commission==0)
          $checked=$date_com_jour=$date_com_mois=$date_com_annee="";
        else
          $checked="checked='1'";

        if(isset($array_cur_values) && array_key_exists($propspec_id, $array_cur_values))
        {
          $date_com_jour=isset($array_cur_values[$propspec_id]["jour_com"]) ? $array_cur_values[$propspec_id]["jour_com"] : $date_com_jour;
          $date_com_mois=isset($array_cur_values[$propspec_id]["mois_com"]) ? $array_cur_values[$propspec_id]["mois_com"] : $date_com_mois;
          $date_com_annee=isset($array_cur_values[$propspec_id]["annee_com"]) ? $array_cur_values[$propspec_id]["annee_com"] : $date_com_annee;

          if(isset($array_cur_values[$propspec_id]["active"]) && $array_cur_values[$propspec_id]["active"]==1)
            $checked="checked='1'";
          else
            $checked="";
        }

        if($annee!=$old_annee)
        {
          if($i!=0)
            print("<tr>
                  <td class='fond_page' colspan='3' height='15px'></td>
                 </tr>\n");

          print("<tr>
                <td class='fond_menu2' align='center' colspan='3' style='padding:4px 20px 4px 20px;'>
                  <font class='Texte_menu2'><b>$annee</b></font>
                </td>
              </tr>
              <tr>
                <td class='fond_menu2' style='padding:4px 20px 4px 20px; white-space:nowrap;'>
                  <font class='Texte_menu2'><b>Active ?</b></font>
                </td>
                <td class='fond_menu2' style='padding:4px 20px 4px 20px;'>
                  <font class='Texte_menu2'><b>Formation</b></font>
                </td>
                <td class='fond_menu2' style='padding:4px 20px 4px 20px; text-align:center;'>
                  <font class='Texte_menu2'><b>Commission (JJ MM AAAA)</b></font>
                </td>
              </tr>
              <tr>
                <td class='fond_menu2' colspan='3'>
                  <font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
                </td>");

          $old_annee=$annee;
          $old_mention='--';
          $first_spec=1;
        }
        else
          $first_spec=0;

        if($mention!=$old_mention)
        {
          if(!$first_spec)
            print("<tr>
                  <td class='fond_menu2' colspan='3'>
                    <font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
                  </td>
                </tr>\n");

          $old_mention=$mention;
        }

        print("<tr>
              <td class='td-gauche $fond' style='text-align:center; width:2%;'>
                <input type='hidden' name='com_id[$propspec_id]' value='$commission_id'>
                <input type='checkbox' name='activ[$propspec_id]' value='1' $checked>
              </td>
              <td class='td-milieu $fond'>
                <font class='Texte_menu'><b>$spec_nom $nom_finalite</b></font>
              </td>
              <td class='td-milieu $fond' style='text-align:center;'>
                <input type='text' name='jour_com[$propspec_id]' value='$date_com_jour' size='2' maxlength='2'>&nbsp;
                <input type='text' name='mois_com[$propspec_id]' value='$date_com_mois' size='2' maxlength='2'>&nbsp;
                <input type='text' name='annee_com[$propspec_id]' value='$date_com_annee' maxlength='4' size='4'>
              </td>
            </tr>\n");
        }

        db_free_result($result);

        print("</table>\n");
    ?>
    </td>
  </tr>
  </table>

  <div class='centered_icons_box'>
    <?php
      if(isset($succes))
        print("<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");
      else
        print("<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>\n");
    ?>
    <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
    </form>
  </div>

  <?php
    }
    db_close($dbr);
  ?>

</div>
<?php
  pied_de_page();
?>

</body></html>
