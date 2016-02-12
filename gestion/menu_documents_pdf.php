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

  if(!isset($_SESSION["candidat_id"]))
  {
    header("Location:index.php");
    exit;
  }

  print("<div class='centered_box'>
        <font class='Texte_16'><strong>$_SESSION[onglet] - Documents PDF relatifs aux candidatures</strong></font>
       </div>");

  if($_SESSION["tab_candidat"]["lock"]==1)
  {
    message("Ces documents PDF sont générés lorsque vous cliquez sur les liens proposés.<br>Leur génération peut prendre quelques secondes.", $__INFO);

    $result=db_query($dbr,"SELECT $_DBC_cand_id, $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite,
                        $_DBC_cand_statut
                      FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_cand
                    WHERE $_DBC_propspec_annee=$_DBC_annees_id
                    AND $_DBC_propspec_id_spec=$_DBC_specs_id
                    AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                    AND $_DBC_cand_candidat_id='$candidat_id'
                    AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                    AND $_DBC_cand_periode='$__PERIODE'
                    AND $_DBC_cand_statut NOT IN ($__PREC_ANNULEE, $__PREC_PLEIN_DROIT)
                      ORDER BY $_DBC_cand_ordre, $_DBC_cand_ordre_spec");

    $rows=db_num_rows($result);

    $liste_options_justifs=$liste_options_page_garde=$liste_options_recap="";
    $liste_options_commission="";
    $count_recevables=0;

    for($i=0; $i<$rows; $i++)
    {
      list($cand_id,$propspec_id, $annee, $spec, $finalite, $statut)=db_fetch_row($result, $i);

      $formation=$annee=="" ? "$spec $tab_finalite[$finalite]" : "$annee - $spec $tab_finalite[$finalite]";

      $liste_options_recap.="<br>- <a href='recapitulatif.php?cand_id=$cand_id' class='lien_bleu_10' target='_blank'>Voeu ".($i+1)." : $formation</a>\n";
      $liste_options_justifs.="<br>- <a href='justificatifs.php?cand_id=$cand_id' class='lien_bleu_10' target='_blank'>Voeu ".($i+1)." : $formation</a>\n";
      $liste_options_page_garde.="<br>- <a href='page_garde_dossier.php?cand_id=$cand_id' class='lien_bleu_10' target='_blank'>Voeu ".($i+1)." : $formation</a>\n";

      if($statut==$__PREC_RECEVABLE)
      {
        $count_recevables++;
        $liste_options_commission.="<br>- <a href='$__GESTION_DIR/lettres/formulaire_commission.php?cand_id=$cand_id' class='lien_bleu_10' target='_blank'>Voeu ".($i+1)." : $formation</a>\n";
      }
    }

    db_free_result($result);

    print("<table cellpadding='4' cellspacing='0' align='center' border='0'>
          <tr>
            <td align='left' nowrap='true' width='40' valign='top' style='padding-bottom:20px;'>
              <a href='recapitulatif.php' class='lien_bleu_10' target='_blank'><img src='$__ICON_DIR/pdf_32x32_fond.png' alt='PDF' desc='PDF' border='0'></a>
            </td>
            <!--
            <td align='left' nowrap='true' valign='middle' style='padding-bottom:20px;'>
              <a href='recapitulatif.php' class='lien_bleu_10' target='_blank'>Récapitulatif des informations entrées par le candidat</a>
            </td>
            -->
            <td align='left' nowrap='true' valign='middle' style='padding-bottom:20px;'>
              <font class='Texte'>
                <strong>Générer les Récapitulatifs des informations entrées par le candidat :</strong>\n");
                
    print("$liste_options_recap\n");
    
    print("</td>
          </tr>
          <tr>
            <td align='left' nowrap='true' width='40' valign='top' style='padding-bottom:20px;'>
              <img src='$__ICON_DIR/pdf_32x32_fond.png' alt='PDF' desc='PDF' border='0'>
            </td>
            <td align='left' nowrap='true' valign='middle' style='padding-bottom:20px;'>
              <font class='Texte'>
                <strong>Générer les Formulaires de Commission Pédagogique :</strong>\n");

    if($count_recevables)
      print("$liste_options_commission\n");
    else
      print("<br>Aucun dossier recevable pour le moment.");

    if($count_recevables>1)
      print("<br>- <a href='$__GESTION_DIR/lettres/formulaire_commission.php?cand_id=all' class='lien_bleu_10' target='_blank'><b>Pour toutes les formations ci-dessus (un seul document contenant tous les formulaires)</b></a>\n");

    print(" </td>
        </tr>
        <tr>
          <td align='left' nowrap='true' width='40' valign='top' style='padding-bottom:20px;'>
            <img src='$__ICON_DIR/pdf_32x32_fond.png' alt='PDF' desc='PDF' border='0'>
          </td>
          <td align='left' nowrap='true' valign='middle' style='padding-bottom:20px;'>
            <font class='Texte'>
              <strong>Générer les Listes de Justificatifs :</strong>
              $liste_options_justifs\n");

    if($rows>1)
      print("<br>- <a href='justificatifs.php?cand_id=all' class='lien_bleu_10' target='_blank'><b>Pour toutes les formations ci-dessus</b></a>\n");

    print("   </font>
          </td>
        </tr>
        <tr>
          <td align='left' nowrap='true' width='40' valign='top' style='padding-bottom:20px;'>
            <img src='$__ICON_DIR/pdf_32x32_fond.png' alt='PDF' desc='PDF' border='0'>
          </td>
          <td align='left' nowrap='true' valign='middle' style='padding-bottom:20px;'>
            <font class='Texte'>
              <strong>Générer les pages de garde (liste des justificatifs manquants) :</strong>
                $liste_options_page_garde\n");
/*
    if($rows>1)
      print("<br>- <a href='page_garde_dossier.php?cand_id=all' class='lien_bleu_10' target='_blank'><b>Pour toutes les formations ci-dessus</b></a>\n");
*/
    print(" </font>
        </tr>
        </table>
        <br><br>\n");
  }
  else
    message("Ces documents ne sont pas disponibles car la fiche du candidat n'est pas encore verrouillée.", $__ERREUR);
?>
