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

  $dbr=db_connect();

  // Déverrouillage, au cas où
  if(isset($_SESSION["candidat_id"]))
    cand_unlock($dbr, $_SESSION["candidat_id"]);
/*
  if(isset($_SESSION["liste_periode"]))
    $Y=$_SESSION["liste_periode"];
  else
    $Y=date('Y');

  $Z=$Y+1;
*/
  // changement de l'ordre de la liste complémentaire
  if(isset($_GET["niveau"]) && is_numeric($_GET["niveau"]) && isset($_SESSION["liste_propspec"]) && isset($_SESSION["liste_attente"]))
  {
    $resultat=1;

    $niveau_courant=$_GET["niveau"];

    if(isset($_GET["up"]) && is_numeric($_GET["up"]))
    {
      $inid=$_GET["up"];
      $nouveau_niveau=$niveau_courant-1;

      $id_insc_courante=$_SESSION["liste_attente"][$niveau_courant];

      db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_liste_attente='$nouveau_niveau' WHERE $_DBU_cand_id='$id_insc_courante'");

      // inversion uniquement s'il n'y a pas de trous :
      if(isset($_SESSION["liste_attente"][$nouveau_niveau]))
      {
        $id_insc_cible=$_SESSION["liste_attente"][$nouveau_niveau];
        db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_liste_attente='$niveau_courant' WHERE $_DBU_cand_id='$id_insc_cible'");
      }
      
    }
    elseif(isset($_GET["down"]) && is_numeric($_GET["down"]))
    {
      $inid=$_GET["down"];
      $nouveau_niveau=$niveau_courant+1;

      $id_insc_courante=$_SESSION["liste_attente"][$niveau_courant];
      $id_insc_cible=$_SESSION["liste_attente"][$nouveau_niveau];

      db_query($dbr,"UPDATE $_DB_cand SET $_DBU_cand_liste_attente='$niveau_courant' WHERE $_DBU_cand_id='$id_insc_cible';
                       UPDATE $_DB_cand SET $_DBU_cand_liste_attente='$nouveau_niveau' WHERE $_DBU_cand_id='$id_insc_courante'");
    }

    db_close($dbr);
  }
  else // nettoyage
  {
    unset($_SESSION["liste_propspec"]);
    unset($_SESSION["liste_attente"]);
  }

  if(isset($_POST["act"]) && $_POST["act"]==1)
  {
    if(isset($_POST["go"]) || isset($_POST["go_x"]))
    {
      $propspec=$_POST["formation"];

      if($propspec=="") // détection bug IE
        $formation_vide=1;
      else
      {
        $resultat=1;  
        $_SESSION["liste_propspec"]=$propspec;
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
    titre_page_icone("Gestion des listes complémentaires", "liste_32x32_fond.png", 30, "L");

    if(isset($formation_vide))
      message("Vous devez sélectionner une formation valide", $__ERREUR);

    if(!isset($resultat))
    {
      print("<form action='$php_self' method='POST'>
            <input type='hidden' name='act' value='1'>\n");
  ?>

  <table align='center'>
  <tr>
    <td class='td-gauche fond_menu2' colspan='2' style='padding:4px;'>
      <font class='Texte_menu2'><b>Sélection de la liste à traiter</b></font>
    </td>
  </tr>
  <tr>
    <td class='td-gauche fond_menu'>
      <font class='Texte_menu'><b>Formation</b></font>
    </td>
    <td class='td-droite fond_menu'>
      <select name='formation' size='1'>
        <?php
          $requete_droits_formations=requete_auth_droits($_SESSION["comp_id"]);
        
          $result=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_id, $_DBC_specs_mention_id,
                              $_DBC_mentions_nom, $_DBC_propspec_finalite
                            FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
                          WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
                          AND $_DBC_mentions_id=$_DBC_specs_mention_id
                          AND $_DBC_propspec_annee=$_DBC_annees_id
                          AND $_DBC_specs_comp_id='$_SESSION[comp_id]'
                          $requete_droits_formations
                            ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom, $_DBC_propspec_finalite");
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

  <div class='centered_box' style='padding-top:20px;'>
    <input type="image" src="<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>" alt="Afficher" name="go" value="Afficher">
    </form>
  </div>

  <?php
    }
    else // résultat de la recherche
    {
      if(isset($resultat) && $resultat==1)
      {
        $dbr=db_connect();

        // nom de la spécialité
        $result2=db_query($dbr,"SELECT $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite
                          FROM $_DB_specs, $_DB_annees, $_DB_propspec
                        WHERE $_DBC_propspec_annee=$_DBC_annees_id
                        AND $_DBC_propspec_id_spec=$_DBC_specs_id
                        AND $_DBC_propspec_id='$_SESSION[liste_propspec]'");

        list($nom_annee, $spec_nom, $finalite)=db_fetch_row($result2,0);
        db_free_result($result2);

        $insc_texte=($nom_annee=="") ? "$spec_nom $tab_finalite[$finalite]": "$nom_annee - $spec_nom $tab_finalite[$finalite]";

        // On trie par rang dans la file d'attente
        // TODO 1 : informations à afficher ? (numéro de téléphone, par exemple)
        // TODO 2 : ajouter la distinction entre les différentes sessions de candidatures

        $result=db_query($dbr,"SELECT DISTINCT $_DBC_cand_candidat_id, $_DBC_cand_id, CAST ($_DBU_cand_liste_attente AS int),
                                  $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom,
                                  $_DBC_candidat_date_naissance
                          FROM $_DB_candidat, $_DB_cand
                        WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
                        AND $_DBC_cand_propspec_id='$_SESSION[liste_propspec]'
                        AND ($_DBC_cand_decision='$__DOSSIER_LISTE' OR $_DBC_cand_decision='$__DOSSIER_LISTE_ENTRETIEN')
                        AND $_DBC_cand_periode='$__PERIODE'
                          ORDER BY CAST ($_DBU_cand_liste_attente AS int) ASC");

/*
  // Sur une session, un candidat ne peut poser qu'un dossier par formation, 
  // la clause suivante est donc inutile (voire fausse)
                          AND $_DBC_cand_candidat_id IN
                            (SELECT $_DBC_cand_candidat_id FROM $_DB_cand, $_DB_propspec
                              WHERE $_DBC_propspec_id='$_SESSION[liste_propspec]'
                              AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                              AND ($_DBC_cand_decision='$__DOSSIER_LISTE' OR $_DBC_cand_decision='$__DOSSIER_LISTE_ENTRETIEN'))
*/

        $rows=db_num_rows($result);

        print("<div class='centered_box'>
              <font style='font-family: arial;' size='3'>Liste des candidats sur liste complémentaire pour la formation : <b>$insc_texte</b></font>
             </div>\n");

        if($rows)
        {
          $_SESSION["liste_attente"]=array();

          print("<table width='50%' cellpadding='2' border='0' align='center'>
               <tr>
                <td class='td-gauche fond_menu2'></td>
                <td class='td-milieu fond_menu2'>
                  <font class='Texte_menu2'><strong>Candidats</strong></font>
                </td>
                <td class='td-droite fond_menu2' style='width:30px;'>
                  <font class='Texte_menu2'><strong>Rang</strong></font>
                </td>
              </tr>
              <tr>
                <td class='td-gauche fond_page' colspan='3'></td>
              </tr>\n");

          for($i=0; $i<$rows; $i++)
          {
            list($candidat_id,$inid,$liste_attente,$civilite,$nom,$prenom,$date_naiss)=db_fetch_row($result,$i);

            $_SESSION["liste_attente"][$liste_attente]=$inid;

            $naissance=date_fr("j/m/Y",$date_naiss);

            if($civilite=="M")
            {
              $civilite="M.";
              $naiss="né le $naissance";
            }
            else
              $naiss="née le $naissance";

            print("<tr>
                  <td class='td-gauche fond_page' style='text-align:center;' valign='middle'>\n");

            // Affichage des flèches permettant de réordonner la liste
            if($i!=0 && in_array($_SESSION["niveau"], array("$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
              print("<a href='$php_self?cid=$candidat_id&up=$inid&niveau=$liste_attente' target='_self' class='lien2'><img src='$__ICON_DIR/up_16x16_menu.png' alt='Monter' border='0'></a>");

            if($i!=($rows-1) && in_array($_SESSION["niveau"], array("$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
              print("<a href='$php_self?cid=$candidat_id&down=$inid&niveau=$liste_attente' target='_self' class='lien2'><img src='$__ICON_DIR/down_16x16_menu.png' alt='Descendre' border='0'></a>\n");

            print("</td>
                  <td class='td-gauche fond_page'>
                    <font class='Texte_menu'>
                      <a href='edit_candidature.php?cid=$candidat_id' target='_self' class='lien_menu_gauche'>$civilite $nom $prenom, $naiss</a>
                    </font>
                  </td>
                  <td class='td-droite fond_page' style='text-align:center; width:30px;'>
                    <font class='Texte_menu'>$liste_attente</font>
                  </td>
                </tr>
                <tr>
                  <td class='td-gauche fond_page' colspan='3'></td>
                </tr>\n");
          }
          print("</table>\n");
        }
        else
          message("Aucun candidat sur liste complémentaire dans cette formation", $__WARNING);

        db_free_result($result);
        db_close($dbr);
      }

      print("<div class='centered_box' style='padding-top:20px'>
            <a href='$php_self' target='_self' class='lien2'><img border='0' src='$__ICON_DIR/back_32x32_fond.png' alt='Nouvelle sélection' desc='Nouvelle sélection'></a>
          </div>\n");
    }
  ?>
</div>
<?php
  pied_de_page();
?>
</body>
</html>
