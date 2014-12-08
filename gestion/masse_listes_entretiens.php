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
    {
      $resultat=1;
      $_SESSION["propspec_id"]=$propspec_id;
    }
    else
      $selection_invalide=1;
  }
  elseif(isset($_POST["valider"]) || isset($_POST["valider_x"]) && isset($_SESSION["propspec_id"]))
  {
    $propspec_id=$_SESSION["propspec_id"];
    $jour_salle_array=explode("_", $_POST["date_salle"]);

    if(array_key_exists(0, $jour_salle_array) && array_key_exists(1, $jour_salle_array))
    {
      $jour=$jour_salle_array[0];
      $salle=$jour_salle_array[1];

      if($jour=="")
        $date_invalide=1;
      else
      {
        $_SESSION["cur_entretien_salle"]=$salle;
        $lien_form="<a href='lettres/liste_entretiens.php?jour=$jour&id_form=$propspec_id' class='lien_bleu_10' target='_blank'>Document prêt - cliquez ici pour l'ouvrir (ouverture dans une nouvelle page)</a>";
        $resultat=2;
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
    titre_page_icone("Entretiens : générer une liste de candidats convoqués à un entretien", "kpersonalizer_32x32_fond.png", 15, "L");

    message("Seules les formations dont certains candidats sont convocables à l'entretien apparaîtront", $__INFO);

    if(isset($selection_invalide))
      message("Erreur : veuillez sélectionner une formation valide dans le menu déroulant.", $__ERREUR);

    if(isset($date_invalide))
      message("Erreur : veuillez sélectionner une date valide dans le menu déroulant.", $__ERREUR);

    if(isset($_GET["erreur"]) && $_GET["erreur"]==1)
      message("Erreur lors de la génération de la liste. Un message a été envoyé à l'administrateur.", $__ERREUR);

    if(isset($success) && $nb_success>0)
    {
      if($nb_success==1)
        message("$nb_success décision validée avec succès", $__SUCCES);
      else
        message("$nb_success décisions validées avec succès", $__SUCCES);
    }

    print("<form action='$php_self' method='POST' name='form1'>\n");

    if(!isset($resultat))
    {
      $dbr=db_connect();

      print("<table style='margin-left:auto; margin-right:auto'>
           <tr>
            <td class='td-gauche fond_menu2'>
              <font class='Texte_menu2'><b>Choix de la formation : </b></font>
            </td>
            <td class='td-droite fond_menu'>\n");

      $result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_propspec_id_spec,
                            $_DBC_specs_nom_court, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_mentions_nom,
                            $_DBC_propspec_manuelle
                        FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
                      WHERE $_DBC_propspec_annee=$_DBC_annees_id
                      AND $_DBC_propspec_id_spec=$_DBC_specs_id
                      AND $_DBC_specs_mention_id=$_DBC_mentions_id
                      AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                      AND $_DBC_propspec_id IN (SELECT distinct($_DBC_cand_propspec_id) FROM $_DB_cand
                                        WHERE $_DBC_cand_statut='$__PREC_RECEVABLE'
                                        AND $_DBC_cand_decision='$__DOSSIER_ENTRETIEN'
                                        AND $_DBC_cand_periode='$__PERIODE')
                        ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom_court");

      $rows=db_num_rows($result);

      if($rows)
      {
        print("<select size='1' name='formation'>
              <option value=''></option>\n");

        $old_annee="-1";
        $old_mention="-1";

        for($i=0; $i<$rows; $i++)
        {
          list($form_propspec_id, $form_annee_id, $form_annee_nom, $form_spec_id, $form_spec_nom, $form_mention,
              $form_finalite, $form_mention_nom, $form_manuelle)=db_fetch_row($result, $i);

          if($form_annee_id!=$old_annee)
          {
            if($i!=0)
              print("</optgroup>
                    <option value='' label='' disabled></option>\n");

            $annee_nom=$form_annee_nom=="" ? "Années particulières" : $form_annee_nom;

            print("<optgroup label='$annee_nom'>\n");

            $new_sep_annee=1;

            $old_annee=$form_annee_id;
            $old_mention="-1";
          }
          else
            $new_sep_annee=0;

          if($form_mention!=$old_mention)
          {
            if(!$new_sep_annee)
              print("</optgroup>
                    <option value='' label='' disabled></option>\n");

            $val=htmlspecialchars($form_mention_nom, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"]);

            print("<optgroup label='- $val'>\n");

            $old_mention=$form_mention;
          }

          $manuelle_txt=$form_manuelle ? "(M)" : "";

          if($form_annee_nom=="")
            print("<option value='$form_propspec_id' label=\"$form_spec_nom " . $tab_finalite[$form_finalite] . " $manuelle_txt\">$form_spec_nom " . $tab_finalite[$form_finalite] . " $manuelle_txt</option>\n");
          else
            print("<option value='$form_propspec_id' label=\"$form_annee_nom - $form_spec_nom " . $tab_finalite[$form_finalite] . " $manuelle_txt\">$form_annee_nom - $form_spec_nom " . $tab_finalite[$form_finalite] . " $manuelle_txt</option>\n");
        }

        print("</optgroup>
            </select>
          </td>
          <td class='td-droite fond_menu'>
            <input type='image' border='0' src='$__ICON_DIR/forward_22x22_menu.png' alt='Suivant' name='suivant' value='Suivant'>
          </td>
        </tr>
        </table>\n");
      }
      else
      {
        print("<font class='Texte_important'><b>
              Sélection impossible :
              <br>- soit aucun candidat n'est convocable à l'entretien
              <br>- soit aucune formation n'a été définie dans cette composante
            </b></font>
          </td>
        </tr>
        </table>\n");

        $no_next=1;
      }
    ?>

  <script language="javascript">
    document.form1.formation.focus()
  </script>

  <?php
    }
    elseif(isset($resultat) && $resultat==1) // résultat de la recherche : Choix de la date et de la salle
    {
      // Nom de la formation choisie
      $result=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite
                        FROM $_DB_propspec, $_DB_annees, $_DB_specs
                      WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
                      AND $_DBC_propspec_annee=$_DBC_annees_id
                      AND $_DBC_propspec_id='$propspec_id'
                        ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom");

      list($nom_annee, $spec_nom, $finalite)=db_fetch_row($result,0);

      if($nom_annee=="")
        $_SESSION["formation_txt"]=$formation_txt="$spec_nom " . $tab_finalite[$finalite];
      else
        $_SESSION["formation_txt"]=$formation_txt="$nom_annee $spec_nom " . $tab_finalite[$finalite];

      db_free_result($result);

      // Dates disponibles
      $result=db_query($dbr,"SELECT $_DBC_cand_entretien_salle,
                          CASE WHEN $_DBC_cand_date_statut='0'
                            THEN '0'
                            ELSE TO_CHAR(TIMESTAMP WITH TIME ZONE 'epoch' + CAST($_DBC_cand_entretien_date as INT) * INTERVAL '1 second', 'YYYY-MM-DD')
                          END as date_case
                        FROM $_DB_cand
                      WHERE $_DBC_cand_propspec_id='$propspec_id'
                      AND $_DBC_cand_periode='$__PERIODE'
                      AND $_DBC_cand_statut='$__PREC_RECEVABLE'
                      AND $_DBC_cand_entretien_date!='0'
                      AND $_DBC_cand_decision='$__DOSSIER_ENTRETIEN'
                        GROUP BY $_DBC_cand_entretien_salle, date_case
                      ORDER BY date_case, $_DBC_cand_entretien_salle");

      $rows=db_num_rows($result);

      if($rows)
      {
        $liste_options="";

        for($i=0; $i<$rows; $i++)
        {
          list($ent_salle, $date_base)=db_fetch_row($result, $i);
/*
          $date_array=explode("-", $date_base);

          $jour_debut=strtotime("$date_array[0]$date_array[1]$date_array[2],0100");
          $date_txt=date_fr("l j F Y", $jour_debut);
*/
          if($date_base==0)
          {
            $date_txt="Date indéterminée";
            $jour_debut="0";
          }
          else
          {
            $date_array=explode("-", $date_base);

            $jour_debut=maketime("1","0","0", $date_array[1], $date_array[2], $date_array[0]);

            // $jour_debut=strtotime("$date_array[0]$date_array[1]$date_array[2],0100");
            $date_txt=date_fr("l j F Y", $jour_debut);
          }

          $liste_options.="<option value='$jour_debut" . "_" . "$ent_salle'>$date_txt - $ent_salle</option>\n";

          // print("<option value='$jour_debut'>$date_txt</option>\n");
        }

        print("<table align='center'>
            <tr>
              <td class='td-gauche fond_menu2'>
                <font class='Texte_menu2'><b>Formation : </b></font>
              </td>
              <td class='td-milieu fond_menu2'>
                <font class='Texte_menu2'><b>$formation_txt</b></font>
              </td>
              <td class='td-droite fond_menu2'></td>
            </tr>
            <tr>
              <td class='td-gauche fond_menu2'>
                <font class='Texte_menu2'><b>Date des convocations : </b></font>
              </td>
              <td class='td-milieu fond_menu'>
                <select style='vertical-align:middle' name='date_salle' size='1'>
                  $liste_options
                </select>
              </td>
              <td class='td-droite fond_menu'>
                <input type='image' border='0' src='$__ICON_DIR/forward_22x22_menu.png' alt='Valider' name='valider' value='Valider'>
              </td>
            </tr>
            </table>\n");
      }

      db_free_result($result);

    }
    elseif(isset($resultat) && $resultat==2)
    {
      if($jour!=0)
        $date=date_fr("l j F Y", $jour);

      if(isset($salle) && $salle!="")
        $salle_txt=", salle \"$salle\"";
      else
        $salle_txt="";

      print("<table align='center'>
          <tr>
            <td class='td-gauche fond_menu2'>
              <font class='Texte_menu2'><b>Formation : </b></font>
            </td>
            <td class='td-milieu fond_menu2'>
              <font class='Texte_menu2'><b>$_SESSION[formation_txt]</b></font>
            </td>
          </tr>
          <tr>
            <td class='td-gauche fond_menu2'>
              <font class='Texte_menu2'><b>Date sélectionnée : </b></font>
            </td>
            <td class='td-milieu fond_menu2'>
              <font class='Texte_menu2'><b>$date</b>$salle_txt</font>
            </td>
          </tr>
          </table>\n");
    }

    db_close($dbr);

    if(isset($lien_form) && $lien_form!="")
      print("<br>
           <center>$lien_form</center>\n");
  ?>

  <div class='centered_icons_box'>
    <a href='masse.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/rew_32x32_fond.png"; ?>' alt='Retour au menu précédent' border='0'></a>
    <?php
      if(isset($resultat))
        print("<a href='$php_self' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour au menu précédent' border='0'></a>");
    ?>
    </form>
  </div>
</div>
<?php
  pied_de_page();
?>
</body></html>
