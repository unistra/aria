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
  // La page générée ici peut être utilisée comme page de garde d'un dossier, imprimée après le premier traitement des
  // pièces envoyées par le candidat.
  // Elle indique, en plus du nom du candidat et de la formation concernée :
  // - les pièces manquantes du cursus (si des précisions existent dans le menu 2-Cursus)
  // - les autres pièces manquantes indiquées dans le menu 5-Précandidatures
  // - un champ libre laissant la possibilité à la scolarité d'écrire les autres pièces manquantes

  session_name("preinsc_gestion");
  session_start();

  include "../configuration/aria_config.php"; 
  include "$__INCLUDE_DIR_ABS/vars.php";
  include "$__INCLUDE_DIR_ABS/fonctions.php";
  include "$__INCLUDE_DIR_ABS/db.php";
  include "$__INCLUDE_DIR_ABS/access_functions.php";
  
  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

  verif_auth();

  $candidat_id=$_SESSION["candidat_id"];

  if((isset($_POST["Valider"]) || isset($_POST["Valider_x"])) && isset($_POST["cand_id"]) && ctype_digit($_POST["cand_id"]))
  {
    $cand_id=$_POST["cand_id"];
    $autres=trim(stripslashes($_POST["autres"]));
    
    $dbr=db_connect();

    // Utilisation de la librairie fpdf (libre)
    require("$__FPDF_DIR_ABS/tcpdf.php");

    $result2=db_query($dbr, "SELECT $_DBC_cand_id, $_DBC_cand_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom,
                        $_DBC_propspec_finalite, $_DBC_propspec_frais, $_DBC_composantes_nom, $_DBC_composantes_scolarite,
                        $_DBC_universites_nom, $_DBC_cand_statut, $_DBC_cand_motivation_decision
                      FROM $_DB_cand, $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_composantes, $_DB_universites
                    WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
                    AND $_DBC_propspec_annee=$_DBC_annees_id
                    AND $_DBC_propspec_id_spec=$_DBC_specs_id
                    AND $_DBC_composantes_id=$_DBC_propspec_comp_id
                    AND $_DBC_composantes_univ_id=$_DBC_universites_id
                    AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                    AND $_DBC_cand_candidat_id='$candidat_id'
                    AND $_DBC_cand_id='$cand_id'");

    $rows2=db_num_rows($result2);

    // Boucle sur les candidatures
    if($rows2)
    {
      // Ici, on créé UN SEUL FICHIER avec UNE PAGE PAR FORMATION

      $page_garde=new TCPDF("P","mm","A4", true, 'UTF-8', false);

      $page_garde->SetCreator("Application ARIA : Gestion des Candidatures");
      $page_garde->SetAuthor("Christophe BOCCHECIAMPE - UFR de Mathématique et d'Informatique - Université de Strasbourg");
      $page_garde->SetSubject("Page de Garde");
      $page_garde->SetTitle("Page de Garde");

      // saut de page automatique, à 15mm du bas
      $page_garde->SetAutoPageBreak(1,11);
      // $page_garde->SetMargins(11,11,11);

      for($k=0; $k<$rows2; $k++)
      {
        list($candidature_id, $propspec_id, $annee, $spec, $finalite, $frais, $comp_nom, $adr_scol, $univ_nom,
          $cand_statut, $cand_motivation)=db_fetch_row($result2, $k);

        $nom_finalite=$tab_finalite[$finalite];

        // Création du PDF

        $page_garde->AddPage();

        // TODO : ATTENTION : NE PAS OUBLIER DE GENERER LA FONTE ARIBLK.TTF LORS D'UNE NOUVELLE INSTALLATION

        $page_garde->SetFont('freesans','',10);
        $page_garde->SetTextColor(0, 0, 0);

        $formation=$annee=="" ? $spec : "$annee $spec";
        $formation.=$nom_finalite=="" ? " ($__PERIODE - " . ($__PERIODE+1).")" : " $nom_finalite ($__PERIODE - " . ($__PERIODE+1).")";

        $page_garde->SetY(15);

        $page_garde->SetFont('freesans',"B",12);

        $page_garde->MultiCell(0, 5, "$formation", 0, "C");

        $page_garde->Ln(8);

        $page_garde->SetFont('freesans',"B",10,'','false');

        $page_garde->MultiCell(0, 5, $_SESSION["tab_candidat"]["civ_texte"] . " " . $_SESSION["tab_candidat"]["nom"] . " " . $_SESSION["tab_candidat"]["prenom"], 0, "L");
        $page_garde->MultiCell(0, 5, $_SESSION["tab_candidat"]["ne_le"] . " " . $_SESSION["tab_candidat"]["txt_naissance"] . " à " . $_SESSION["tab_candidat"]["lieu_naissance"], 0, "L");

        $page_garde->Ln(8);

        // Statut de la candidature
        switch($cand_statut)
        {
          case $__PREC_NON_RECEVABLE  : $statut_txt="Non recevable";
                              $motif_texte=$cand_motivation!="" ? ": $cand_motivation" : "";
                              break;

          case $__PREC_NON_TRAITEE  : $statut_txt="Non traitée";
                              $motif_texte="";
                              break;

          case $__PREC_RECEVABLE    : $statut_txt="Recevable";
                              $motif_texte="";
                              break;

          case $__PREC_EN_ATTENTE   : $statut_txt="En attente";
                              $motif_texte=$cand_motivation!="" ? ": $cand_motivation" : "";
                              break; 
        }

        $page_garde->SetFont('freesans',"",10);
        $page_garde->MultiCell(0, 5, "Recevabilité : $statut_txt $motif_texte\n", 0, "J");

        $result=db_query($dbr, "SELECT $_DBC_cursus_id, $_DBC_cursus_annee, $_DBC_cursus_diplome, $_DBC_cursus_intitule,
                            $_DBC_cursus_spec
                          FROM $_DB_cursus
                        WHERE $_DBC_cursus_candidat_id='$candidat_id'
                          ORDER BY $_DBC_cursus_annee DESC");

        $rows=db_num_rows($result);

        if($rows)
        {
          $page_garde->SetFont('freesans',"B",12);
          $page_garde->MultiCell(0, 5, "\nEtapes non justifiées du cursus :", 0, "L");

          // Boucle sur le cursus
          for($j=0; $j<$rows; $j++)
          {
            list($cursus_id, $cursus_annee, $cursus_diplome, $cursus_intitule, $cursus_spec)=db_fetch_row($result, $j);

            $cursus_txt="$cursus_annee - $cursus_diplome - $cursus_intitule";
            $cursus_txt.=$cursus_spec=="" ? "" : " - $cursus_spec";

            $page_garde->SetFont('freesans',"",10);
            /*
            $page_garde->Multicell(0, 5, "$cursus_txt", 0, "J");

            $page_garde->SetX($page_garde->GetX()+10);

            $page_garde->SetFont('arial',"I",10);
                  */
            // Statut et précisions (si disponibles) - on ne prend que les étapes non validées
            $res_statut_cursus=db_query($dbr,"SELECT $_DBC_cursus_justif_statut, $_DBC_cursus_justif_precision
                                    FROM $_DB_cursus_justif
                                  WHERE $_DBC_cursus_justif_cursus_id='$cursus_id'
                                  AND $_DBC_cursus_justif_comp_id='$_SESSION[comp_id]'
                                  AND $_DBC_cursus_justif_periode='$__PERIODE'
                                  AND $_DBC_cursus_justif_statut NOT IN ('$__CURSUS_VALIDE', '$__CURSUS_NON_NECESSAIRE')");

            $rows_statut=db_num_rows($res_statut_cursus); // Normalement : zéro ou un résultat

            if($rows_statut)
            {
              list($cursus_statut, $cursus_precision)=db_fetch_row($res_statut_cursus, 0);

              $precision_txt=$cursus_precision!="" ? ": $cursus_precision" : "";

              switch($cursus_statut)
              {
                case $__CURSUS_NON_JUSTIFIE : $statut_txt="- Non justifié";
                                      break;

                case $__CURSUS_DES_OBTENTION  : $statut_txt="- A fournir dès l'obtention";
                                      break;

                case $__CURSUS_PIECES     : $statut_txt="- Pièces manquantes";
                                      break;

                case $__CURSUS_EN_ATTENTE   : $statut_txt="- En attente";
                                      break;

                default               : $statut_txt="- En attente";
                                      break;
              }
              
               $page_garde->Multicell(0, 5, "$cursus_txt\n", 0, "J");
               $page_garde->SetX($page_garde->GetX()+10);
               $page_garde->SetFont('freesans',"I",10); 
              $page_garde->Multicell(0, 5, "$statut_txt $precision_txt\n", 0, "J");
              $page_garde->Ln(5);
            }
            else
            {
              $statut_txt="- En attente"; // statut par défaut
              $precision_txt="";
            }
                 
            db_free_result($res_statut_cursus);

          } // Fin de la boucle for() sur le cursus
        } // fin du if($rows)

        db_free_result($result);

        $page_garde->Ln(10);

        // Même chose pour l'état de cette candidature ?

        $page_garde->SetFont('freesans',"B",12);
        $page_garde->MultiCell(0, 5, "Autres documents manquants (champ libre) :\n", 0, "J");

        if(isset($autres) && $autres!="")
        {
          $page_garde->SetFont('freesans',"I",10);
          $page_garde->MultiCell(0, 5, "$autres\n", 0, "J");
        }

      } // Fin du for sur les candidatures
      
      $nom_fichier=clean_str($_SESSION["auth_user"] . "_" . time() . "_Page_garde.pdf");
      // $page_garde->Output("$nom_fichier", "I");

      $page_garde->Output("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$nom_fichier");

      write_evt($dbr, $__EVT_ID_G_DOC, "Génération Page de garde", $candidat_id, $candidature_id);

      // Attention : chemin relatif à www-root (document_root du serveur Apache)
      echo "<HTML><SCRIPT>document.location='$__GESTION_COMP_STOCKAGE_DIR/$_SESSION[comp_id]/$nom_fichier';</SCRIPT></HTML>";
    } // fin du if($rows2)

    db_close($dbr);
  }
  elseif(isset($_GET["cand_id"]) && ctype_digit($_GET["cand_id"]))
    $cand_id=$_GET["cand_id"];
  else
  {
    header("Location:edit_candidature.php");
    exit();
  }

  // EN-TETE
  en_tete_gestion();

  // MENU SUPERIEUR
  menu_sup_gestion();
?>
<div class='main'>
<?php
    print("<div class='infos_candidat Texte'>
          <strong>" . $_SESSION["tab_candidat"]["etudiant"] ." : " . $_SESSION["tab_candidat"]["civ_texte"] . " " . $_SESSION["tab_candidat"]["nom"] . " " . $_SESSION["tab_candidat"]["prenom"] .", " . $_SESSION["tab_candidat"]["ne_le"] . " " . $_SESSION["tab_candidat"]["txt_naissance"] ."</strong>
         </div>

        <form action='$php_self' method='POST' name='form1'>
        <input type='hidden' name='cand_id' value='$cand_id'>\n");

    titre_page_icone("Edition d'une page de garde du dossier", "edu_languages_32x32_fond.png", 15, "L");

    message("<strong>Cette page contiendra par défaut :</strong>
          <br>- le nom du candidat et de la formation choisie
          <br>- les étapes du cursus encore non justifiées (avec le détail complété dans le menu 2-Cursus)
          <br>- le statut de la recevabilité de la précandidature (avec la motivation éventuelle)
          <br>
          <br>Le champ ci-dessous vous permet d'indiquer librement les éventuelles autres pièces manquantes (ces informations
          ne seront pas conservées une fois le formulaire validé).", $__INFO);

    message("<strong>Il ne doit en aucun cas contenir de remarques personnelles sur le candidat.</strong>", $__WARNING);
?>

  <table align='center'>
  <tr>
    <td class='td-complet fond_menu2'>
      <font class='Texte_menu2'><b>Indiquez si vous le souhaitez les autres pièces manquantes au dossier :</b></font>
    </td>
  </tr>
  <tr>
    <td class='td-milieu fond_menu'>
      <textarea cols="80" rows="10" name="autres" title="Autres pièces manquantes"></textarea>
    </td>
  </tr>
  </table>

  <div class='centered_icons_box'>
    <a href='edit_candidature.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
    <input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="Valider" value="Valider">
    </form>
  </div>

</div>
<?php
  // db_close($dbr);
  pied_de_page();
?>

<script language="javascript">
  document.form1.langue.focus()
</script>
</body></html>

