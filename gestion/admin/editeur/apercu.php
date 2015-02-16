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
  include "$__INCLUDE_DIR_ABS/access_functions.php";

  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

  verif_auth("../../login.php");

  if(!isset($_SESSION["lettre_id"]))
    die("Impossible d'afficher l'aperçu : lettre non déterminée (utilisation de la touche \"Retour\" (Back) de votre navigateur ?)");

  $Y=date("Y");
  $Z=$Y+1;

  // Largeur max du corps, en mm
  $__LARGEUR_MAX_CORPS="135";

  $dbr=db_connect();

  // On prend le premier étudiant qu'on trouve dans cette composante
  $result=db_query($dbr, "SELECT $_DBC_candidat_id, $_DBC_cand_id FROM $_DB_candidat, $_DB_cand, $_DB_propspec
                    WHERE $_DBC_cand_candidat_id=$_DBC_candidat_id
                    AND  $_DBC_propspec_id=$_DBC_cand_propspec_id
                    AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                    AND $_DBC_cand_periode='$__PERIODE'
                  LIMIT 1");

  if(db_num_rows($result))
  {
    list($candidat_id, $cand_id)=db_fetch_row($result, 0);
    db_free_result($result);

    $candidat_array=__get_infos_candidat($dbr, $candidat_id);
    $cursus_array=__get_cursus($dbr, $candidat_id);
    $candidature_array=__get_candidature($dbr, $cand_id);
  }
  else // on fabrique un candidat de toute pièce
  {
    $fake=1;

    $candidat_array=array();

    $candidat_array["civilite"]="Mme";
    $candidat_array["civ_texte"]="Madame";
    $candidat_array["nom"]="EXEMPLE";
    $candidat_array["prenom"]="Test";
    $candidat_array["naissance_unix"]=345639600;
    $adresse="24 Rue des Peupliers\n67000 Strasbourg";
    $candidat_array["lieu_naissance"]="Strasbourg";
    $candidat_array["pays_naissance"]="France";
    $candidat_array["nationalite"]="Française";
    $candidat_array["telephone"]="01 23 45 67 89";

    $candidat_array["naissance"]=date_fr("j/m/Y",$candidat_array["naissance_unix"]);
    $candidat_array["adresse"]=$adresse;

    // Cursus
    $cursus_array=array();

    $cursus_array[0]["cursus"]="- Baccalauréat S";
    $cursus_array[0]["lieu"]="- Lycée Kleber, Strasbourg, France";
    $cursus_array[0]["date"]="1997";

    $cursus_array[1]["cursus"]="- CPGE 1ère année";
    $cursus_array[1]["lieu"]="- INSA, Rennes, France";
    $cursus_array[1]["date"]="1999";

    $cursus_array[2]["cursus"]="- IUP GMI 1ère année";
    $cursus_array[2]["lieu"]="- ULP, Strasbourg, France";
    $cursus_array[2]["date"]="2000";

    // Candidature
    $candidature_array=array();

      // Formation : on essaye d'abord de trouver une formation liée à la lettre
      $result_fake=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_specs_nom_court,
                            $_DBC_propspec_selective, $_DBC_propspec_resp, $_DBC_propspec_mailresp
                          FROM $_DB_annees, $_DB_specs, $_DB_propspec, $_DB_lettres_propspec
                       WHERE $_DBC_propspec_annee=$_DBC_annees_id
                       AND $_DBC_propspec_id_spec=$_DBC_specs_id
                       AND $_DBC_lettres_propspec_propspec_id=$_DBC_propspec_id
                       AND $_DBC_lettres_propspec_lettre_id='$_SESSION[lettre_id]'
                       AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                       AND $_DBC_propspec_active='1'
                       LIMIT 1");

      // Sinon, n'importe laquelle fera l'affaire
      if(!db_num_rows($result_fake))
      {
       $result_fake=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_specs_nom_court,
                              $_DBC_propspec_selective, $_DBC_propspec_resp, $_DBC_propspec_mailresp
                          FROM $_DB_annees, $_DB_specs, $_DB_propspec
                         WHERE $_DBC_propspec_annee=$_DBC_annees_id
                         AND $_DBC_propspec_id_spec=$_DBC_specs_id
                         AND $_DBC_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec
                                          WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                          LIMIT 1)");
    }

    // Aperçu impossible si les données minimales sont absentes
    // TODO : ce message devrait plutôt apparaître dans le menu de l'éditeur (et rendre le lien "Aperçu" inopérant)
    if(!db_num_rows($result_fake))
         die("Impossible de créer un aperçu : les données de la base sont insuffisantes pour créer un modèle cohérent.
              <br>Il est conseillé de configurer l'offre de formations de la composante avant de manipuler l'Editeur de Lettres.");

    list($candidature_array["propspec_id"],
        $candidature_array["annee"],
        $candidature_array["spec_nom"],
        $candidature_array["spec_nom_court"],
        $candidature_array["selective"],
        $candidature_array["responsable"],
        $candidature_array["responsable_email"])=db_fetch_row($result_fake, 0);

    db_free_result($result_fake);
    $candidature_array["comp_id"]=$_SESSION["comp_id"];
    $candidature_array["id"]=new_id();
    $candidature_array["rang_attente"]="";
    $candidature_array["ordre_spec"]=-1;
    $candidature_array["groupe_spec"]=-1;
    $candidature_array["decision"]=0;
    $candidature_array["transmission"]="";
    $candidature_array["vap"]=0;
    $candidature_array["date_decision_unix"]=mktime(12,0,0,6,15,date("Y"));
    $candidature_array["date_decision"]=date_fr("j F Y",$candidature_array["date_decision_unix"]);
    $candidature_array["session_commission_unix"]=mktime(12,0,0,6,15,date("Y"));
    $candidature_array["session_commission"]=date_fr("j F Y",$candidature_array["session_commission_unix"]);
    $candidature_array["entretien_date_unix"]=time();
    $candidature_array["entretien_date"]="lundi 11 juillet " . date("Y");
    $candidature_array["entretien_heure"]="15h30";
    $candidature_array["entretien_lieu"]=$_SESSION["composante"];
    $candidature_array["entretien_salle"]="en salle 212";
    $candidature_array["periode"]=$__PERIODE;
    $motivation="";

    if(!empty($candidature_array["annee"]))
      $candidature_array["texte_formation"]="$candidature_array[annee] - $candidature_array[spec_nom]";
    else
      $candidature_array["texte_formation"]="$candidature_array[spec_nom]";
  }

  $candidature_array["motif_txt"]="les motifs suivants";
  $candidature_array["motivation"]="- motif 1\n- motif 2\n- ...";

  if($candidature_array!=FALSE)
  {
    // Informations sur la lettre
    $result2=db_query($dbr,"SELECT $_DBC_lettres_logo, $_DBC_lettres_txt_logo, $_DBC_lettres_txt_scol, $_DBC_lettres_txt_sign,
                        $_DBC_lettres_largeur_logo, $_DBC_lettres_flag_logo, $_DBC_lettres_flag_txt_logo, $_DBC_lettres_flag_txt_scol,
                        $_DBC_lettres_flag_txt_sign, $_DBC_lettres_flag_adr_cand, $_DBC_lettres_flag_date,
                        $_DBC_lettres_flag_adr_pos, $_DBC_lettres_adr_pos_x, $_DBC_lettres_adr_pos_y,
                        $_DBC_lettres_flag_corps_pos, $_DBC_lettres_corps_pos_x, $_DBC_lettres_corps_pos_y,
                        $_DBC_lettres_langue
                      FROM $_DB_lettres
                    WHERE $_DBC_lettres_id='$_SESSION[lettre_id]'");

    $rows2=db_num_rows($result2);

    if($rows2)
      list($logo, $txt_logo, $txt_scol, $txt_sign, $largeur_logo, $flag_logo, $flag_txt_logo, $flag_txt_scol,
          $flag_txt_sign, $flag_adr_cand, $flag_date, $flag_adr_pos, $adr_pos_x, $adr_pos_y, $flag_corps_pos,
          $corps_pos_x, $corps_pos_y,$lettre_lang)=db_fetch_row($result2,0);
    else
      die("Lettre non trouvée : merci de contacter rapidement l'administrateur.");

    // Modification des paramètres en fonction de la langue de la lettre
    /*
    $candidat_array["civilite"]=civ_lang($candidat_array["civilite"], $lettre_lang, 0);
    $candidat_array["civ_texte"]=civ_lang($candidat_array["civilite"], $lettre_lang, 1);
    */
    $candidat_array["civ_adresse"]=civ_lang($candidat_array["civilite"], $lettre_lang, 2);
    
    $candidature_array["date_decision"]=date_lang($candidature_array["date_decision_unix"], $lettre_lang, 0, 0);
    $candidature_array["session_commission"]=date_lang($candidature_array["session_commission_unix"], $lettre_lang, 0, 0);
    $candidature_array["entretien_date"]="Monday 11th of July " . date("Y");
    $candidature_array["entretien_salle"]="en salle 212";


    db_free_result($result2);

    // Informations par défaut, si besoin
    // ==> Si un flag vaut "t" (TRUE), ça signifie qu'on doit prendre la valeur par défaut
    // ==> S'il faut "f" (FALSE), on prend l'info spécifique à cette lettre

    if($flag_logo=='t' || $flag_txt_logo=='t' || $flag_txt_scol=='t' || $flag_txt_sign=='t' || $flag_adr_pos=='t' || $flag_corps_pos=='t')
    {
      $result2=db_query($dbr,"SELECT $_DBC_composantes_logo, $_DBC_composantes_txt_logo, $_DBC_composantes_txt_scol,
                           $_DBC_composantes_txt_sign, $_DBC_composantes_largeur_logo, $_DBC_universites_couleur_texte_lettres,
                           $_DBC_composantes_adr_pos_x, $_DBC_composantes_adr_pos_y, $_DBC_composantes_corps_pos_x,
                           $_DBC_composantes_corps_pos_y
                        FROM $_DB_composantes, $_DB_universites
                      WHERE $_DBC_composantes_id='$_SESSION[comp_id]'
                      AND $_DBC_composantes_univ_id=$_DBC_universites_id");

      $rows2=db_num_rows($result2);

      if($rows2)
        list($logo_defaut, $txt_logo_defaut, $txt_scol_defaut, $txt_sign_defaut, $largeur_logo_defaut, $univ_couleur_texte,
            $adr_pos_x_defaut, $adr_pos_y_defaut, $corps_pos_x_defaut, $corps_pos_y_defaut)=db_fetch_row($result2,0);
      else
        die("Composante non trouvée : merci de contacter rapidement l'administrateur.");

      db_free_result($result2);

      if($flag_logo=='t')
      {
        $logo=$logo_defaut;
        $largeur_logo=$largeur_logo_defaut;
      }

      if($flag_txt_logo=='t') $txt_logo=$txt_logo_defaut;
      if($flag_txt_scol=='t') $txt_scol=$txt_scol_defaut;
      if($flag_txt_sign=='t') $txt_sign=$txt_sign_defaut;
      if($flag_corps_pos=='t')
      {
        $corps_pos_x=$corps_pos_x_defaut;
        $corps_pos_y=$corps_pos_y_defaut;
      }
    }

    if(!empty($candidature_array["spec_nom"]))
    {
      // Utilisation de la librairie fpdf (libre)
      require("$__FPDF_DIR_ABS/fpdf.php");

      // Création du PDF
      $lettre_decision=new FPDF("P","mm","A4");

      $lettre_decision->SetCreator("Application de Gestion des Candidatures de l'Université de Strasbourg");
      $lettre_decision->SetAuthor("Christophe BOCCHECIAMPE - UFR de Mathématique et d'Informatique - Université de Strasbourg");
      $lettre_decision->SetSubject("Décision de la Commission Pédagogique");
      $lettre_decision->SetTitle("Décision de la Commission Pédagogique");

      // saut de page automatique, à 15mm du bas
      $lettre_decision->SetAutoPageBreak(1,11);
      // $lettre_decision->SetMargins(11,11,11);

      $lettre_decision->AddPage();

      $lettre_decision->SetXY(13, 10);
      // TODO : ATTENTION : NE PAS OUBLIER DE GENERER LA FONTE ARIBLK.TTF LORS D'UN CHANGEMENT DE MACHINE
      $lettre_decision->AddFont("arial_black");

      if(!empty($txt_logo))
      {
        $lettre_decision->SetXY(11, 10);
        $lettre_decision->SetFont('arial_black','',12);

        if(!empty($univ_couleur_texte))
        {
          // La couleur doit être convertie hexa (#112233) => décimal
          $texte_R=hexdec(mb_substr($univ_couleur_texte, 1, 2, "UTF-8"));
          $texte_V=hexdec(mb_substr($univ_couleur_texte, 3, 2, "UTF-8"));
          $texte_B=hexdec(mb_substr($univ_couleur_texte, 5, 2, "UTF-8"));
/*
          $texte_couleur=explode(",", $univ_couleur_texte);

          if(array_key_exists("0", $texte_couleur) && is_numeric($texte_couleur[0]) &&
            array_key_exists("1", $texte_couleur) && is_numeric($texte_couleur[1]) &&
            array_key_exists("2", $texte_couleur) && is_numeric($texte_couleur[2]))

          $texte_R=$texte_couleur[0];
          $texte_V=$texte_couleur[1];
          $texte_B=$texte_couleur[2];
*/
          $lettre_decision->SetTextColor($texte_R, $texte_V, $texte_B);
        }
        else
          $lettre_decision->SetTextColor(0, 0, 0);

        $lettre_decision->MultiCell(44, 5,$txt_logo, 0, "R");

        $hauteur_min_line=$lettre_decision->getY();

        $lettre_decision->Line(11, $hauteur_min_line, 54, $hauteur_min_line);
        $lettre_decision->Ln(5);

        $hauteur_min_logo=$lettre_decision->getY();
      }
      else
        $hauteur_min_logo=10;

      if(!empty($logo))
      {
        $logo_img="$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$logo";

        if(is_file($logo_img))
        {
          $array_image=getimagesize($logo_img);

          // Largeur max du logo : 44mm (colonne = 55mm / marge min = 11mm)
          if($array_image)
          {
            if($array_image[0] < 44)
            {
              $largeur_logo=$array_image[0];
              $X_logo=55-$largeur_logo;
            }
            elseif($largeur_logo>60)
            {
              $largeur_logo=60;
              $X_logo=11;
            }
            else
              $X_logo=55-$largeur_logo;
          }
          elseif($largeur_logo>60)
          {
            $largeur_logo=60;
            $X_logo=11;
          }
          else
            $X_logo=55-$largeur_logo;

          $lettre_decision->image($logo_img, $X_logo, $hauteur_min_logo, $largeur_logo);
        }
      }

      // Hauteur de référence pour le texte écrit dans la colonne gauche
      $txt_scol_hauteur_courante=$lettre_decision->GetY();

      $lettre_decision->SetFont('arial','',10);
      $lettre_decision->SetTextColor(0, 0, 0);

      if($flag_date!=0) // 1 = date de commission || -1 : date du jour
      {
        if($flag_date==1)
          $date=date_lang($candidature_array["session_commission_unix"], $lettre_lang, 1, 0);
        else
          $date=date_lang(time(), $lettre_lang, 1, 0);

        if($lettre_lang=="EN")
          $date_txt="$__VILLE, $date";
        else
          $date_txt="$__VILLE, le $date";

        $lettre_decision->SetXY(124, 15);
        $lettre_decision->MultiCell(0,5,$date_txt, 0, "L");
      }

      // Adresse postale
      if($flag_adr_cand=="t")
      {
        if($flag_adr_pos=="t")
          $lettre_decision->SetXY($adr_pos_x_defaut, $adr_pos_y_defaut);
        else
          $lettre_decision->SetXY($adr_pos_x, $adr_pos_y);

        $candidat_adresse=$candidat_array["civ_adresse"] . " " .  $candidat_array["nom"] . " " . $candidat_array["prenom"] . "\n" . $candidat_array["adresse"];

        $lettre_decision->MultiCell(0,5,$candidat_adresse, 0, "L");
      }

      // A partir d'ici, on extrait les informations de la lettre correspondant à la décision

      $elements_corps=get_all_elements($dbr, $_SESSION["lettre_id"]);
      $nb_elem_corps=count($elements_corps);

      if($nb_elem_corps)
      {
        $lettre_decision->SetXY($corps_pos_x, $corps_pos_y);

        for($j=0; $j<$nb_elem_corps; $j++)
        {
          // variables communes à tous les types d'éléments

          $element_id=$elements_corps["$j"]["id"];
          $element_type=$elements_corps["$j"]["type"];

          switch($element_type)
          {
            case 2  : // encadré (toujours centré par rapport à la page)
                    $txt=$elements_corps["$j"]["texte"];
                    // $align=$elements_corps["$i"]["alignement"];
                    $txt_align=$elements_corps["$j"]["txt_align"];

                    // alignement du texte dans le tableau
                    $cell_align=get_fpdf_align($txt_align);

                    $largeur_texte_encadre=$lettre_decision->GetStringWidth($txt);

                    // Macros prédéfinies
                    $txt=pdf_traitement_macros($dbr, $txt, $candidat_array, $candidature_array, $cursus_array, $lettre_lang);

                    // si ça dépasse, on force la taille de la cellule (au max : 0)
                    if($largeur_texte_encadre>=$__LARGEUR_MAX_CORPS)
                    {
                      $largeur_encadre=0;
                      $lettre_decision->SetX($corps_pos_x);
                    }
                    else
                    {
                      $marge=($__LARGEUR_MAX_CORPS-$largeur_texte_encadre)/2;
                      $lettre_decision->SetX($marge);
                      $largeur_encadre=$largeur_texte_encadre;
                    }

                    $lettre_decision->SetX($corps_pos_x);
                    $lettre_decision->MultiCell($largeur_encadre, 5,$txt, 1, "$cell_align");

                    // $lettre_decision->Ln(5);

                    break;

            case 5  : // paragraphe
                    $txt=$elements_corps["$j"]["texte"];
                    $txt_align=$elements_corps["$j"]["txt_align"];
                    $txt_gras=$elements_corps["$j"]["gras"];
                    $txt_italique=$elements_corps["$j"]["italique"];
                    $txt_taille=$elements_corps["$j"]["taille"];
                    $txt_marge_gauche=$elements_corps["$j"]["marge_gauche"];

                    // Macros prédéfinies
                    $txt=pdf_traitement_macros($dbr, $txt, $candidat_array, $candidature_array, $cursus_array, $lettre_lang);

                    // alignement du texte du paragraphe
                    $cell_align=get_fpdf_align($txt_align);

                    $gras=$txt_gras ? "B" : "";
                    $italique=$txt_italique ? "I" : "";

                    $lettre_decision->SetFont('arial',"$gras$italique",$txt_taille);

                    if($txt_taille>14)
                      $hauteur_cell="6";
                    elseif($txt_taille>=10 && $txt_taille <=12)
                      $hauteur_cell="5";
                    elseif($txt_taille<10)
                      $hauteur_cell="4";
                    else
                      $hauteur_cell="5";

                    // Marge gauche 
                    if($txt_marge_gauche && $txt_marge_gauche<200 && $txt_marge_gauche>0)
                      $X=$corps_pos_x+floor(($txt_marge_gauche/210)*100);
                    else
                      $X=$corps_pos_x;

                    $lettre_decision->SetX($X);
                    $lettre_decision->MultiCell(0, $hauteur_cell, $txt, 0, "$cell_align");

                    // $lettre_decision->Ln(5);

                    break;

            case 8  : // séparateur
                    $hauteur=ctype_digit($elements_corps["$j"]["nb_lignes"]) && $elements_corps["$j"]["nb_lignes"]>1 ? $elements_corps["$j"]["nb_lignes"]*5 : 5;

                    $lettre_decision->Ln($hauteur);

                    break;
          }
        }
      }

      // Scolarité
      if(!empty($txt_scol))
      {
        // Calcul de la hauteur du texte, en nombre de lignes (on compte les \n)
        $txt_scol=pdf_traitement_macros($dbr, $txt_scol, $candidat_array, $candidature_array, $cursus_array, $lettre_lang);

        $hauteur_txt=substr_count($txt_scol, "\n");
        $hauteur_txt_mm=5*$hauteur_txt;
        
        $hauteur_disponible=275-$txt_scol_hauteur_courante; // (en millimètres)

        $Y_txt_scol=$txt_scol_hauteur_courante+($hauteur_disponible-$hauteur_txt_mm);

        // $lettre_decision->SetXY(0, 225);
        $lettre_decision->SetXY(0, $Y_txt_scol);
        $lettre_decision->SetFont('arial','',8);

        $array_txt_scol=explode("\n", $txt_scol);

        foreach($array_txt_scol as $ligne_scol)
        {
          $base_size=8;
          $lettre_decision->SetFont('arial','',$base_size);

          while($lettre_decision->GetStringWidth($ligne_scol) > 42)
          {
            $base_size--;
            $lettre_decision->SetFont('arial','',$base_size);
          }

          $lettre_decision->SetX(11);
          $lettre_decision->MultiCell(44,5,$ligne_scol, 0, "R");
        }

        // $lettre_decision->MultiCell(51,5,$txt_scol, 0, "R");
      }

      // Génération du fichier et copie dans le répertoire

      $nom_fichier=$_SESSION["auth_user"] . "_apercu_decision.pdf";

      // TODO : centraliser ces fonctions de création automatique de chemins
      if(!is_dir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]"))
        mkdir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]", 0770);

      $lettre_decision->Output("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$nom_fichier");

      // Attention : chemin relatif à www-root (document_root du serveur Apache)
      echo "<HTML><SCRIPT>document.location='$__GESTION_COMP_STOCKAGE_DIR/$_SESSION[comp_id]/$nom_fichier';</SCRIPT></HTML>";
    }
  }

  db_close($dbr);
?>
