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
  // fiches_traitees.php
  // Affiche toutes les fiches entièrement traitées pour la période courante
  // Question : double emploi avec stats_filieres_compeda.php ?
  // TODO : fusionner avec "candidats.php" avec un argument en option

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

  // filtre sur une formation
  if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
  {
    if(isset($_POST["formation"]) && $_POST["formation"]!="")
      $_SESSION["filtre_propspec"]=$_POST["formation"];

    if(isset($_POST["decision"]) && $_POST["decision"]!="")
      $_SESSION["filtre_decision"]=$_POST["decision"];
  }

  // Filtre par défaut, si aucun filtre n'a encore été sélectionné
  if(!isset($_SESSION["filtre_propspec"]) && isset($_SESSION['spec_filtre_defaut']))
    $_SESSION["filtre_propspec"]=$_SESSION['spec_filtre_defaut'];

  if(!isset($_SESSION["filtre_decision"]))
    $_SESSION["filtre_decision"]="-1";

  // Nettoyage de variables utilisées ailleurs
  unset($_SESSION["cursus_a_valider"]);
  unset($_SESSION["cursus_transfert"]);
  unset($_SESSION["candidatures_transfert"]);
  unset($_SESSION["tab_candidatures"]);

  $_SESSION["onglet"]=1; // onglet par défaut : identité du candidat

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>

<div class='main' style='padding-left:4px; padding-right:4px;'>
  <?php
    titre_page_icone("Fiches traitées ($__PERIODE - ".($__PERIODE+1).")", "flag-green_32x32_fond.png", 10, "L");

    message("<center>
            Cette liste montre tous les candidats dont la fiche contient au moins un voeu entièrement traité.
            <br>Certains peuvent encore avoir des voeux partiellement ou non traités.
          </center>",$__INFO);

    // Filtres
    if($_SESSION["filtre_propspec"]!=-1)
    {
      $filtre_formation="AND $_DBC_cand_propspec_id='$_SESSION[filtre_propspec]'";
    }
    else
    {
      $filtre_formation="";
    }

    $filtre_decision=$_SESSION["filtre_decision"]!=-1 ? "AND $_DBC_decisions_id='$_SESSION[filtre_decision]'" : "";
  ?>
  <form action='<?php echo $php_self; ?>' method='POST' name='form1'>

  
  <table cellpadding='2'>
  <tr>
    <td class='fond_menu2'>
      <font class='Texte_menu2'><b>Filtrer par Formation : </b></font>
    </td>
    <td class='fond_menu'>
      <select size="1" name="formation">
        <option value="-1">Montrer toutes les formations</option>
        <option value="-1" disabled='1'></option>
        <?php
          $requete_droits_formations=requete_auth_droits($_SESSION["comp_id"]);
        
          $result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
                              $_DBC_propspec_manuelle
                          FROM $_DB_propspec, $_DB_annees, $_DB_specs
                        WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
                        AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                        AND $_DBC_propspec_annee=$_DBC_annees_id
                        AND $_DBC_propspec_id IN (SELECT distinct($_DBC_cand_propspec_id) FROM $_DB_cand, $_DB_propspec
                                          WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
                                          AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                          AND $_DBC_cand_periode='$__PERIODE')
                        $requete_droits_formations
                          ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom");
          $rows=db_num_rows($result);

          $prev_annee="--"; // variable initialisée à n'importe quoi

          // TODO : revoir l'utilisation de la table annee (intégration de annees.id dans proprietes_specialites_v2, par exemple) et répercuter les changements ici ?
          for($i=0; $i<$rows; $i++)
          {
            list($propspec_id, $annee, $nom,$finalite, $manuelle)=db_fetch_row($result,$i);

            if($annee!=$prev_annee)
            {
              if($i!=0)
                print("</optgroup>\n");

              if(empty($annee))
                print("<optgroup label='Années particulières'>\n");
              else
                print("<optgroup label='$annee'>\n");

              $prev_annee=$annee;
            }

            $nom_finalite=$tab_finalite[$finalite];

            $selected=($_SESSION["filtre_propspec"]==$propspec_id) ? "selected=1" : "";

            $manuelle_txt=$manuelle ? "(M)" : "";

            // print("<option value='$annee_id|$spec_id' $selected>$annee - $nom</option>\n");
            print("<option value='$propspec_id' label=\"$annee - $nom $nom_finalite $manuelle_txt\" $selected>$annee - $nom $nom_finalite $manuelle_txt</option>\n");
          }
          db_free_result($result);
        ?>
      </select>
    </td>
    <td class='fond_menu' rowspan='2' valign='middle'>
      <input type='submit' name='go_valider' value='Valider'>
    </td>
  </tr>
  <tr>
    <td class='fond_menu2'>
      <font class='Texte_menu2'><b>Filtrer par Décision : </b></font>
    </td>
    <td class='fond_menu'>
      <select size="1" name="decision">
        <option value="-1">Montrer toutes les décisions</option>
        <option value="-1" disabled='1'></option>
        <?php
          $result=db_query($dbr,"SELECT $_DBC_decisions_id, $_DBC_decisions_texte
                            FROM $_DB_decisions
                            WHERE $_DBC_decisions_id IN 
                              (SELECT distinct($_DBU_cand_decision) FROM $_DB_cand WHERE $_DBU_cand_periode='$__PERIODE'
                              AND $_DBU_cand_propspec_id IN (SELECT $_DBU_propspec_id FROM $_DB_propspec WHERE $_DBU_propspec_comp_id='$_SESSION[comp_id]'))
                            AND $_DBC_decisions_id>'$__DOSSIER_NON_TRAITE'
                            ORDER BY $_DBC_decisions_texte");
          $rows=db_num_rows($result);

          for($i=0; $i<$rows; $i++)
          {
            list($decision_id, $decision_texte)=db_fetch_row($result,$i);

            $selected=$_SESSION["filtre_decision"]==$decision_id ? "selected=1" : "";

            print("<option value='$decision_id' label=\"$decision_texte\" $selected>$decision_texte</option>\n");
          }
          db_free_result($result);
        ?>
      </select>
    </td>
  </tr>
  </table>
  <br><font class='Texte_10'><i>Seules les formations pour lesquelles des candidatures ont été déposées sont proposées.</i>
  </form>

  <br>
  <?php
    // Récupération de toutes les fiches traitées en fonction du filtre sélectionné

    $result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom,
                         $_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance, 
                         CASE WHEN $_DBC_candidat_pays_naissance IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance) 
                            THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance)
                            ELSE '' END as pays_naissance,
                         $_DBC_candidat_manuelle, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
                         $_DBC_decisions_id, $_DBC_decisions_texte
                      FROM $_DB_candidat, $_DB_cand, $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_decisions
                    WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
                    AND   $_DBC_propspec_id=$_DBC_cand_propspec_id
                    AND $_DBC_annees_id=$_DBC_propspec_annee
                    AND $_DBC_specs_id=$_DBC_propspec_id_spec
                    AND   $_DBC_cand_decision=$_DBC_decisions_id
                    $requete_droits_formations
                    $filtre_formation
                    $filtre_decision
                    AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                    AND $_DBC_cand_periode='$__PERIODE'
                    AND $_DBC_cand_decision>'$__DOSSIER_NON_TRAITE'
                      ORDER BY $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance,
                            $_DBC_candidat_id, $_DBC_annees_ordre, $_DBC_specs_nom_court");

    $rows=db_num_rows($result);

    if($rows)
    {
      if($rows>1)
        $s="s";
      else
        $s="";

      if($_SESSION["filtre_propspec"]!=-1 || $_SESSION["filtre_decision"]!=-1)
      {
        $filtre_txt="Avec filtrage, il y a";

        if($_SESSION["filtre_propspec"]!=-1)
          $colonne_formation=0;
        else
          $colonne_formation=1;
      }
      else
      {
        $filtre_txt="Au total, il y a";
        $colonne_formation=1;
      }

      print("<font class='Texte3'><b>$filtre_txt $rows fiches (tri par nom, prénom et date de naissance) : </b></font><br><br>
            <table width='100%' border='0' cellspacing='0' cellpadding='4' style='padding-bottom:20px;'>
            <tr>
              <td class='fond_menu2'></td>
              <td class='fond_menu2'>
                <font class='Texte_menu2'><b>Candidat(e)s</b></font>
              </td>
              <td class='fond_menu2'>
                <font class='Texte_menu2'><b>Naissance</b></font>
              </td>\n");

      if($colonne_formation)
        print("<td class='fond_menu2'>
              <font class='Texte_menu2'><b>Formation</b></font>
             </td>\n");

      print("<td class='fond_menu2'>
            <font class='Texte_menu2'><b>Décision</b></font>
          </td>
        </tr>\n");

      // Initialisation du "candidat précédent"
      $old_candidat_id="";

      // Affichage des candidats
      for($i=0; $i<$rows; $i++)
      {
        list($candidat_id, $candidat_civ, $nom, $prenom, $date_naissance, $lieu_naissance, $pays_naissance, $fiche_manuelle,
            $annee, $spec_nom, $finalite, $decision_id, $decision)=db_fetch_row($result,$i);

        $nom_finalite=$tab_finalite[$finalite];

        $nom_formation=$annee=="" ? "$spec_nom $nom_finalite" : "$annee $spec_nom $nom_finalite";

        // si le candidat est différent du précédent, on affiche l'identité, sinon on ne met que la formation
        if($old_candidat_id!=$candidat_id)
        {
          $border="style='border-width:2px 0px 0px 0px; border-style:solid; border-color:white'";

          $naissance=date_fr("j F Y",$date_naissance);  

          if($fiche_manuelle)
            $td_manuelle="<td class='fond_menu' align='center' width='22' $border>
                        <img src='$__ICON_DIR/contact-new_16x16_menu.png' alt='Fiche manuelle' desc='Fiche créée manuellement' border='0'>
                      </td>\n";
          else
            $td_manuelle="<td class='fond_menu' $border></td>\n";
          
          print("<tr>
                $td_manuelle
                <td class='fond_menu' nowrap='true' $border>
                  <a href='edit_candidature.php?cid=$candidat_id' class='lien_menu_gauche'><b>$nom $prenom</b></a>
                </td>
                <td class='fond_menu' nowrap='true' $border>
                  <a href='edit_candidature.php?cid=$candidat_id' class='lien_menu_gauche'>$naissance à $lieu_naissance ($pays_naissance)</a>
                </td>\n");
        }
        else
        {
          $border="";

          print("<tr>
                <td class='fond_menu' colspan='3'></td>\n");
        }

        if($colonne_formation)
          print("<td class='fond_menu' nowrap='true' $border>
                <font class='Texte_menu'>$nom_formation</font>
              </td>\n");

        // Couleur pour la décision
        switch($decision_id)
        {
          case $__DOSSIER_ADMIS_ENTRETIEN:  $color="#00BB00"; // vert
                                break;

          case $__DOSSIER_ADMIS_LISTE_COMP: $color="#00BB00"; // vert
                                  break;

          case $__DOSSIER_ADMIS : $color="#00BB00"; // vert
                            break;

          case $__DOSSIER_ADMISSION_CONFIRMEE : $color="#00BB00"; // vert
                            break;

          case $__DOSSIER_ADMIS_RECOURS : $color="#00BB00"; // vert
                            break;

          case $__DOSSIER_REFUS : $color="#CC0000"; // rouge
                            break;

          case $__DOSSIER_DESISTEMENT : $color="#CC0000"; // rouge
                                break;

          case $__DOSSIER_REFUS_RECOURS : $color="#CC0000"; // rouge
                                break;

          default : $color='#FF8800'; // orange
        }

        print("<td class='fond_menu' nowrap='true' $border>
              <font class='Texte_menu' style='color:$color;'>$decision</font>
            </td>
          </tr>\n");

        $old_candidat_id=$candidat_id;
      }
      print("</table>\n");
    }
    else
      print("<font class='Texte3'><strong>Aucune fiche dans la base.</strong></font><br>\n");

    db_free_result($result);
    db_close($dbr);
  ?>
</div>
<?php
  pied_de_page();
?>
<br>

</body>
</html>

