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

  // récupération de variables


  if(isset($_GET["e"]))
    unset($_SESSION["element_id"]);

  $dbr=db_connect();

  if(isset($_POST["selection"]) || isset ($_POST["selection_x"]))
  {
    $element_id=$_POST["element_id"];
  
    $result=db_query($dbr,"SELECT $_DBC_dossiers_elems_intitule, $_DBC_dossiers_elems_vap
                      FROM $_DB_dossiers_elems
                    WHERE $_DBC_dossiers_elems_id='$element_id'");
    $rows=db_num_rows($result);

    if($rows)
    {
      list($element_intitule, $element_vap)=db_fetch_row($result, 0);

      if($element_vap==1)
        $element_intitule="(<i>VAP</i>) $element_intitule";

      $result2=db_query($dbr, "SELECT $_DBC_dossiers_ef_propspec_id FROM $_DB_dossiers_ef, $_DB_propspec
                      WHERE $_DBC_dossiers_ef_elem_id='$element_id'
                      AND $_DBC_propspec_id=$_DBC_dossiers_ef_propspec_id
                      AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'");

      $rows2=db_num_rows($result2);

      if($rows2)
      {
        $array_formations=array();

        for($i=0; $i<$rows2; $i++)
          list($array_formations[$i])=db_fetch_row($result2, $i);
      }

      db_free_result($result2);

      $_SESSION["element_id"]=$element_id;
      $resultat=1;
    }

    db_free_result($result);
  }

  if(isset($_POST["rattacher"]) || isset ($_POST["rattacher_x"]))
  {
    $element_id=$_SESSION["element_id"];

    // $cond_nationalite=$_POST["cond_nat"];

    // Nettoyage avant insertion : on supprime les rattachements entre cet élément et les formations de la composante courante
    // (en conservant l'ordre correct)

    // Sélection de toutes les formations de la composante courante
    $propspec_array=array();

    $result=db_query($dbr, "SELECT $_DBC_propspec_id FROM $_DB_propspec
                    WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                    AND $_DBC_propspec_active='1'");
    $rows=db_num_rows($result);

    $requete="";

    // Insertion uniquement si l'élément n'était pas déjà rattaché, en queue de liste
    for($i=0; $i<$rows; $i++)
      list($propspec_array[$i])=db_fetch_row($result, $i);

    db_free_result($result);

    if(isset($_POST["toutes_formations"]))
    {
      $requete="";

      // Insertion uniquement si l'élément n'était pas déjà rattaché, en queue de liste
      foreach($propspec_array as $propspec_id)
      {
        if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_dossiers_ef WHERE $_DBC_dossiers_ef_propspec_id='$propspec_id'
                                                  AND $_DBC_dossiers_ef_elem_id='$element_id'")))
        {
          $requete.="INSERT INTO $_DB_dossiers_ef VALUES('$element_id', '$propspec_id',
                                        (SELECT CASE WHEN max($_DBC_dossiers_ef_ordre) IS NULL THEN 0 
                                                 ELSE max($_DBC_dossiers_ef_ordre)+1 END
                                          FROM $_DB_dossiers_ef
                                          WHERE $_DBC_dossiers_ef_propspec_id='$propspec_id')); ";

        }
      }

      if(!empty($requete))
        db_query($dbr,"$requete");
    }
    else
    {
      $requete="";

      foreach($propspec_array as $propspec_id)
      {
        if(array_key_exists("formation", $_POST) && in_array($propspec_id, $_POST["formation"]))
        {
          if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_dossiers_ef WHERE $_DBC_dossiers_ef_propspec_id='$propspec_id'
                                                    AND $_DBC_dossiers_ef_elem_id='$element_id'")))

          $requete.="INSERT INTO $_DB_dossiers_ef VALUES('$element_id', '$propspec_id',
                                          (SELECT CASE WHEN max($_DBC_dossiers_ef_ordre) IS NULL THEN 0
                                                ELSE max($_DBC_dossiers_ef_ordre)+1 END
                                            FROM $_DB_dossiers_ef
                                            WHERE $_DBC_dossiers_ef_propspec_id='$propspec_id'));";
        }
        else // suppression si la formation n'est plus sélectionnée
        {
          db_query($dbr, "UPDATE $_DB_dossiers_ef SET $_DBU_dossiers_ef_ordre=$_DBU_dossiers_ef_ordre-1
                      WHERE $_DBU_dossiers_ef_ordre>(SELECT $_DBC_dossiers_ef_ordre FROM $_DB_dossiers_ef
                                           WHERE $_DBC_dossiers_ef_elem_id='$element_id'
                                           AND $_DBC_dossiers_ef_propspec_id='$propspec_id')
                      AND $_DBC_dossiers_ef_propspec_id='$propspec_id';
                     DELETE FROM $_DB_dossiers_ef WHERE $_DBC_dossiers_ef_propspec_id='$propspec_id'
                                        AND $_DBC_dossiers_ef_elem_id='$element_id'");
        }
      }

      if(!empty($requete))
        db_query($dbr,"$requete");
    }

    db_close($dbr);
    header("Location:index.php");
    exit;
  }
  
  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main'>
  <?php
    titre_page_icone("Constructeur de dossiers : rattacher un élément à une formation", "randr_32x32_fond.png", 30, "L");
  ?>

  <form method='POST' action='<?php echo $php_self; ?>'>

  <table border='0' cellspacing='0' cellpadding="0" align='center'>
  <tr>
    <td>
      <table align='center' width='100%' style='padding-bottom:20px;'>
      <tr>
        <td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
          <font class='Texte_menu2'>
            <b>&#8226;&nbsp;&nbsp;Elément à rattacher</b>
          </font>
        </td>
      </tr>
      <tr>
        <td class='td-gauche fond_menu2' width='10%' nowrap>
          <font class='Texte_menu2'><b>Sélection :</b></font>
        </td>
        <td class='td-droite fond_menu'>
          <?php
            if(!isset($resultat))
            {
              $result=db_query($dbr,"SELECT $_DBC_dossiers_elems_id, $_DBC_dossiers_elems_intitule, $_DBC_dossiers_elems_vap
                                FROM $_DB_dossiers_elems
                              WHERE $_DBC_dossiers_elems_comp_id='$_SESSION[comp_id]'
                              ORDER BY $_DBC_dossiers_elems_type, $_DBC_dossiers_elems_intitule, $_DBC_dossiers_elems_vap");
              $rows=db_num_rows($result);

              if($rows)
              {
                print("<select name='element_id'>\n");

                for($i=0; $i<$rows; $i++)
                {
                  list($element_id, $element_intitule, $element_vap)=db_fetch_row($result, $i);
                  $val=htmlspecialchars($element_intitule, ENT_QUOTES, $GLOBALS["default_htmlspecialchars_encoding"], FALSE);

                  if($element_vap==1)
                    $vap="(<i>VAP</i>) ";
                  else
                    $vap="";

                  print("<option value='$element_id'>$vap$val</option>\n");
                }

                print("</select>\n");
              }
              else
              {
                $no_element=1;
                message("Vous devez d'abord créer des éléments avant de les rattacher.", $__ERREUR);
              }
            }
            else
              print("<font class='Texte_menu'><strong>$element_intitule</strong></font>\n");
          ?>
        </td>
      </tr>
      <?php
        if(isset($resultat))
        {
      ?>
      <tr>
        <td class='td-gauche fond_menu2' width='10%' nowrap>
          <font class='Texte_menu2'><b>Rattacher à toutes les formations :</b></font>
        </td>
        <td class='td-droite fond_menu'>
          <input type='checkbox' name='toutes_formations' value='1'>
        </td>
      </tr>
      <?php
        }
      ?>
      </table>

      <?php
        if(!isset($resultat))
        {
          print("</td>
            </tr>
            </table>

            <div class='centered_icons_box'>
              <a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Annuler' border='0'></a>\n");

          if(!isset($no_element))
            print("<input class='icone' type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Valider' name='selection' value='Valider'>\n");

          print("</form>
              </div>\n");
        }
        else
        {
          // Nombre max de mentions pour les années de cette composantes (pour affichage)
          $res_mentions=db_query($dbr, "SELECT count(distinct($_DBC_specs_mention_id)) FROM $_DB_specs,$_DB_propspec
                              WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
                              AND $_DBC_propspec_comp_id ='$_SESSION[comp_id]'
                              AND $_DBC_propspec_active='1'
                            GROUP BY $_DBC_propspec_annee
                            ORDER BY count DESC");

          list($max_mentions)=db_fetch_row($res_mentions, 0);

          $max_mentions=$max_mentions=="" ? 0 : $max_mentions;

          if($max_mentions>1)
          {
            $colspan_annee=2;
            $colwidth="50%";
          }
          else
          {
            $colspan_annee=1;
            $colwidth="100%";
          }

          db_free_result($res_mentions);

          $result=db_query($dbr,"(SELECT $_DBC_propspec_id, $_DBC_annees_id, $_DBC_annees_annee, $_DBC_specs_nom_court,
                                $_DBC_propspec_finalite, $_DBC_mentions_id, $_DBC_mentions_nom
                            FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
                          WHERE $_DBC_propspec_annee=$_DBC_annees_id
                          AND $_DBC_propspec_id_spec=$_DBC_specs_id
                          AND $_DBC_specs_mention_id=$_DBC_mentions_id
                          AND $_DBC_propspec_active='1'
                          AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                            ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_specs_nom_court)");

          $rows=db_num_rows($result);
          $old_annee="===="; // idem

          if($rows)
          {
            $old_propspec_id="--"; // on initialise à n'importe quoi (sauf vide)
            $old_annee_id="--"; // idem
            $old_mention="--"; // idem
            $j=0;

            print("<table align='center'>\n");

            for($i=0; $i<$rows; $i++)
            {
              list($propspec_id, $annee_id, $annee, $spec_nom, $finalite, $mention, $mention_nom)=db_fetch_row($result, $i);

              $nom_finalite=$tab_finalite[$finalite];

              if($annee_id!=$old_annee_id)
              {
                $annee=$annee=="" ? "Années particulières" : $annee;

                if($i) // Le premier résultat du tableau est particulier (i=0)
                {
                  print("</table>
                      </td>\n");

                  if(!$j)
                    print("<td width='$colwidth' valign='top'></td>");

                  print("</tr>
                      <tr>
                        <td class='fond_page' height='10' colspan='4'></td>
                      </tr>\n");
                }

                print("<tr>
                      <td class='fond_menu2' colspan='$colspan_annee' style='padding:4px 20px 4px 20px;'>
                        <font class='Texte_menu2'><b>$annee</b></font>
                      </td>
                    </tr>
                    <tr>
                      <td class='fond_menu2' width='$colwidth' valign='top'>
                        <table width='100%'>
                        <tr>
                          <td colspan='2' class='fond_menu2' align='center' height='20'>
                            <font class='Texte_menu2'><b>$mention_nom</b></font>
                          </td>
                        </tr>\n");

                $old_mention="$mention";
                $old_annee_id=$annee_id;
                $j=0;
              }

              if($old_mention!=$mention)
              {
                if($i)
                  print("</table>
                      </td>\n");

                if($j)
                  print("</tr>
                      <tr>\n");

                print("<td class='fond_menu2' width='$colwidth' valign='top'>
                      <table width='100%'>
                      <tr>
                        <td class='fond_menu2' colspan='2' height='20' align='center'>
                          <font class='Texte_menu2'><b>$mention_nom</b></font>
                        </td>
                      </tr>\n");

                $j=$j ? 0 : 1;

                $old_mention=$mention;
              }

              if(isset($array_formations) && in_array($propspec_id, $array_formations))
                $checked="checked=1";
              else
                $checked="";

              print("<tr>
                    <td class='td-gauche fond_menu' style='padding:4px 2px 0px 2px;' width='15'>
                      <input type='checkbox' name='formation[]' value='$propspec_id' $checked style='vertical-align:middle;'>
                    </td>
                    <td class='td-droite fond_menu' style='padding:4px 2px 0px 2px;'>
                      <font class='Texte_menu'>$spec_nom $nom_finalite</font>
                    </td>
                  </tr>\n");
            }

            db_free_result($result);

            print("</table>
                </td>\n");

            if(!$j)
              print("<td width='$colwidth' valign='top'></td>\n");

            print("</tr>
                 </table>

                <div class='centered_icons_box'>
                  <a href='index.php' target='_self'><img class='icone' src='$__ICON_DIR/rew_32x32_fond.png' alt='Annuler' border='0'></a>
                  <a href='$php_self?e=1' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
                  <input type='image' class='icone' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='rattacher' value='Valider'>
                  </form>
                </div>\n");
          }
        }

        db_close($dbr);
      ?>
    </td>
  </tr>
  </table>

</div>
<?php
  pied_de_page();
?>
</body></html>
