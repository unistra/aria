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
  unset($_SESSION["from"]);

  verif_auth();

  $dbr=db_connect();

  if(isset($_POST["recherche"]) || isset($_POST["recherche_x"]))
  {
    $formation=mb_strtolower(trim($_POST["formation"]), "UTF-8");
    $mention=mb_strtolower(trim($_POST["mention"]), "UTF-8");

    if($formation=="" && $mention=="")
      $champs_vides=1;
    elseif(preg_match("/([a-z\'\ ]+)/i", $formation) || preg_match("/([a-z\'\ ]+)/i", $mention))
    {
      $formation=clean_str_requete($formation);
      $mention=clean_str_requete($mention);

      // Second test après nettoyage des chaines
      if($formation=="" && $mention=="")
        $champs_vides=1;
      else
      {
        if($formation=="")
          $critere_recherche="AND lower(unaccent($_DBC_mentions_nom)) ILIKE unaccent('%".preg_replace("/[']+/", "''", stripslashes($mention))."%') ";
        elseif($mention=="")
          $critere_recherche="AND lower(unaccent($_DBC_specs_nom)) ILIKE unaccent('%".preg_replace("/[']+/", "''", stripslashes($formation))."%') ";
        else
          $critere_recherche="AND (lower(unaccent($_DBC_mentions_nom)) ILIKE unaccent('%".preg_replace("/[']+/", "''", stripslashes($mention))."%') 
              AND lower(unaccent($_DBC_specs_nom)) ILIKE unaccent('%".preg_replace("/[']+/", "''", stripslashes($formation))."%')) ";

        $result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_mentions_nom, $_DBC_specs_nom,
                            $_DBC_propspec_finalite, $_DBC_universites_nom, $_DBC_composantes_id, $_DBC_composantes_nom
                        FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_universites, $_DB_composantes,
                            $_DB_mentions
                      WHERE $_DBC_propspec_annee=$_DBC_annees_id
                      AND $_DBC_specs_mention_id=$_DBC_mentions_id
                      AND $_DBC_propspec_id_spec=$_DBC_specs_id
                      AND $_DBC_propspec_comp_id=$_DBC_composantes_id
                      AND $_DBC_composantes_univ_id=$_DBC_universites_id
                      AND $_DBC_propspec_active='1'
                      AND $_DBC_propspec_manuelle='0'
                      $critere_recherche
                        ORDER BY $_DBC_universites_nom, $_DBC_composantes_nom, $_DBC_mentions_nom,
                              $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite");

        $rows=db_num_rows($result);
        $nb_trouves=$rows;
      }
    }
    else
      $format=1;
  }
  
  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
      
?>
<div class='main'>
  <?php
    titre_page_icone("Rechercher une formation", "xmag_32x32_fond.png", 15, "L");

    if(isset($$champs_vides))
      message("Le formulaire ne doit pas être vide", $__ERREUR);

    if(isset($format))
      message("Le format du texte recherché est incorrect", $__ERREUR);

    if(!isset($nb_trouves))
    {
      message("<center>
              Si les deux champs sont complétés, la recherche portera sur les formations
              <br>appartenant explicitement à la mention indiquée.
            </center>", $__INFO);

      print("<form action='$php_self' method='POST' name='form1'>\n");
  ?>

  <table align='center'>
  <tr>
    <td class='td-complet fond_menu2' colspan='2'>
      <font class='Texte_menu2'><b>Recherche ... </b></font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu'>
      <font class='Texte_menu'><b>Intitulé ou partie de l'intitulé de la formation : </b><br>(N'entrez PAS l'année L2, M1 ..)</font>
    </td>
    <td class='td-milieu fond_menu'>
      <input type='text' name='formation' value='' maxlength='60' size='30'>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu'>
      <font class='Texte_menu'><b>Mention ou partie de la mention : </font>
    </td>
    <td class='td-milieu fond_menu'>
      <input type='text' name='mention' value='' maxlength='60' size='30'>
    </td>
  </tr>
  </table>  

  <div class='centered_icons_box'>
    <a href='recherche.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour au menu précédent' border='0'></a>
    <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Rechercher" name="recherche" value="Rechercher">
    </form>
  </div>

  <script language="javascript">
    document.form1.formation.focus()
  </script>
    <?php
    }
    else // résultat de la recherche  
    {
      if(isset($nb_trouves) && $nb_trouves!=0)
      {
        if($nb_trouves>1)     
          print("<div class='centered_box'>
                <font class='Texte'><i>$nb_trouves formations trouvées :</i></font>
              </div>\n");
        else
          print("<div class='centered_box'>
                <font class='Texte'><i>$nb_trouves formation trouvée :</i></font>
              </div>\n");

        print("<table align='center'>\n");

        $old_univ=$old_comp=$old_mention="--";
      
        for($i=0; $i<$rows;$i++)
        {
          list($propspec_id, $annee_nom, $mention_nom, $spec_nom, $finalite, $univ_nom, $comp_id, $comp_nom)=db_fetch_row($result,$i);

          $formation=$annee_nom=="" ? "$spec_nom" : "$annee_nom - $spec_nom";

          if($univ_nom!=$old_univ)
          {
            if($i)
              print("<tr>
                    <td class='td-separation' height='15' colspan='3'></td>
                   </tr>\n");

            print("<tr>
                  <td class='td-gauche fond_menu2' colspan='3'>
                    <font class='Texte_menu3'><strong>$univ_nom</strong></font>
                  </td>
                </tr>
                <tr>
                  <td class='td-gauche fond_menu2'>
                    <font class='Texte_menu2'><strong>Composante / Formation</strong></font>
                  </td>
                  <td class='td-milieu fond_menu2'>
                    <font class='Texte_menu2'><strong>Finalité</strong></font>
                  </td>
                  <td class='td-droite fond_menu2'>
                    <font class='Texte_menu2'><strong>Session</strong></font>
                  </td>
                </tr>\n");

            $old_univ=$univ_nom;
            $old_comp=$old_mention="--";
          }

          // Dates
          $res_session=db_query($dbr,"SELECT $_DBC_session_ouverture, $_DBC_session_fermeture 
                                FROM $_DB_session
                              WHERE $_DBC_session_propspec_id='$propspec_id'
                              AND $_DBC_session_periode='$__PERIODE'      
                            GROUP BY $_DBC_session_ouverture, $_DBC_session_fermeture
                            ORDER BY $_DBC_session_ouverture, $_DBC_session_fermeture");

          $nb_sessions=db_num_rows($res_session);

          if($nb_sessions)
          {
            // Une seule date ? on affiche
            if($nb_sessions==1)
            {
              list($ouv,$ferm)=db_fetch_row($res_session, 0);

              if($ouv!="" && $ferm!="" )
              {
                if($ouv<time() && $ferm>time())
                  $dates_txt="<font class='Textevert_menu'>du " . date_fr("j F Y", $ouv) . " au " . date_fr("j F Y", $ferm) . "</font>";
                else
                  $dates_txt="<font class='Texte_important_menu'>du " . date_fr("j F Y", $ouv) . " au " . date_fr("j F Y", $ferm) . "</font>";
              }
              else
                $dates_txt="<font class='Texte_menu'>Dates non déterminées pour les candidatures $__PERIODE-".($__PERIODE+1)."</font>";
            }
            else // plusieurs dates : si une session est ouverte, on l'affiche, sinon on indique la plus proche
            {
              for($j=0; $j<$nb_sessions; $j++)
              {
                list($ouv,$ferm)=db_fetch_row($res_session, $j);

                if($ouv<time() && $ferm>time())
                {
                  $dates_txt="<font class='Textevert_menu'>du " . date_fr("j F Y", $ouv) . " au " . date_fr("j F Y", $ferm) . "</font>";
                  $j=$nb_sessions;
                }
                elseif($ouv>time()) // la plus proche dans le futur
                {
                  $dates_txt="<font class='Texteorange'>du " . date_fr("j F Y", $ouv) . " au " . date_fr("j F Y", $ferm) . "</font>";
                  $j=$nb_sessions;
                }
                else // La dernière session (déjà fermée)
                  $dates_txt="<font class='Texte_important'>du " . date_fr("j F Y", $ouv) . " au " . date_fr("j F Y", $ferm) . "</font>";
              }
            }
          }
          else
            $dates_txt="<font class='Texte_menu'>Dates non déterminées pour les candidatures $__PERIODE-".($__PERIODE+1)."</font>";

          db_free_result($res_session);

          if($comp_nom!=$old_comp)
          {
            if($i && $univ_nom==$old_univ)
              print("<tr>
                    <td class='td-separation' height='15' colspan='3'></td>
                    </tr>\n");

            // Composante sélectionnable uniquement si les droits d'accès sont corrects

            if(db_num_rows(db_query($dbr, "SELECT $_DBC_acces_id FROM $_DB_acces
                                  WHERE ($_DBC_acces_composante_id='$comp_id'
                                       AND $_DBC_acces_id='$_SESSION[auth_id]')
                                  OR $_DBC_acces_id IN (SELECT $_DBC_acces_comp_acces_id FROM $_DB_acces_comp
                                                WHERE $_DBC_acces_comp_composante_id='$comp_id'
                                                AND $_DBC_acces_comp_acces_id='$_SESSION[auth_id]')")))
            {
              $crypt_params=crypt_params("co=$comp_id");
              $comp_txt="<a href='select_composante.php?p=$crypt_params' class='lien_bleu_12'><b>$comp_nom</b></a>";
            }
            else
              $comp_txt="<font class='Texte_menu'><strong>$comp_nom</strong></font>";

            print("<tr>
                  <td class='td-gauche fond_menu' colspan='3'>$comp_txt</td>
                 </tr>\n");

            $old_comp=$comp_nom;
            $old_mention="--";
          }

          if($mention_nom!=$old_mention)
          {
            print("<tr>
                  <td class='td-gauche fond_menu' colspan='3'>
                    <font class='Texte_menu'><strong>Mention : $mention_nom</strong></font>
                  </td>
                 </tr>\n");

            $old_mention=$mention_nom; 
          }

          print("<tr>
                <td class='td-gauche fond_page'>
                  <font class='Texte'>$formation</font>
                </td>
                <td class='td-milieu fond_page'>
                  <font class='Texte'>$tab_finalite[$finalite]</font>
                </td>
                <td class='td-droite fond_page'>$dates_txt</td>
              </tr>\n");
        }

        print("</table>\n");
      }
      else
        message("Aucune formation ne correspond à votre recherche", $__WARNING);
      
      print("<div class='centered_icons_box'>
            <a href='$php_self' target='_self' class='lien2'><img src='$__ICON_DIR/xmag_32x32_fond.png' alt='Retour' border='0'></a>
            <a href='recherche.php' target='_self' class='lien2'><img border='0' src='$__ICON_DIR/back_32x32.png' alt='Nouvelle recherche' desc='Nouvelle recherche'></a>
          </div>\n");
      
      db_free_result($result);
    }

    db_close($dbr);
  ?>
</div>
<?php
  pied_de_page();
?>
</body></html>
