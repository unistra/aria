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

include "../../configuration/aria_config.php";
include "$__INCLUDE_DIR_ABS/vars.php";
include "$__INCLUDE_DIR_ABS/fonctions.php";
include "$__INCLUDE_DIR_ABS/db.php";
include "$__INCLUDE_DIR_ABS/access_functions.php";
// TODO : rassembler les includes pour éviter ce genre de chemins ...
include "../admin/editeur/include/editeur_fonctions.php";

$dbr=db_connect();

$Y=date("Y");
$Z=$Y+1;

// Largeur max du corps, en mm
$__LARGEUR_MAX_CORPS="135";


// Paramètres communs
if(isset($_GET["tri"]))
{
  switch($_GET["tri"])
  {
    case "dec" : $order_by="ORDER BY $_DBC_decisions_texte, $_DBC_candidat_nom, $_DBC_candidat_prenom ";
                break;
                   
    case "nom" : $order_by="ORDER BY $_DBC_candidat_nom, $_DBC_candidat_prenom ";
                 break;
                  
    default : $order_by="ORDER BY $_DBC_candidat_nom, $_DBC_candidat_prenom ";
   }
}
else
   $order_by="ORDER BY $_DBC_candidat_nom, $_DBC_candidat_prenom ";


// Identifiant de l'étudiant ou du traitement en masse
if(isset($_GET["mid"]) && $_GET["mid"]!="" && isset($_GET["mp"]) && is_numeric($_GET["mp"]))
{
  verif_auth("$__GESTION_DIR/login.php");

  $masse_id=$_GET["mid"];
  $partie_id=$_GET["mp"];

  if($_SESSION["niveau"]!=$__LVL_ADMIN)
    $condition_user="AND $_DBC_traitement_masse_acces_id='$_SESSION[auth_id]'";
  else
    $condition_user="";

  // Vérification des paramètres
  $result=db_query($dbr, "SELECT $_DBC_cand_candidat_id, $_DBC_cand_id FROM $_DB_cand, $_DB_candidat, $_DB_decisions
                  WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
                  AND $_DBC_cand_decision=$_DBC_decisions_id
                  AND $_DBC_cand_id IN (SELECT $_DBC_traitement_masse_cid FROM $_DB_traitement_masse
                                 WHERE $_DBC_traitement_masse_id='$masse_id'
                                 AND $_DBC_traitement_masse_partie='$partie_id'
                                 $condition_user)
                   $order_by");

  $rows=db_num_rows($result);

  if(!$rows)
  {
    db_free_result($result);
    db_close($dbr);

    header("Location:../masse_traitement.php");
    exit();
  }
  else
  {
    $ensemble_candidats=array();

    for($i=0; $i<$rows; $i++)
    {
      list($candidat_id, $cand_id)=db_fetch_row($result, $i);

      $ensemble_candidats["$candidat_id"]=$cand_id;
    }

    db_free_result($result);
  }
}
// Génération des résultats pour une formation précise, entre deux dates, avec éventuellement un filtre sur la décision
elseif(array_key_exists("jour_inf", $_GET) && $_GET["jour_inf"]!="" && ctype_digit($_GET["jour_inf"]) && array_key_exists("jour_sup", $_GET) && $_GET["jour_sup"]!="" && ctype_digit($_GET["jour_sup"]) && isset($_GET["id_form"]) && ctype_digit($_GET["id_form"]))
{
  verif_auth("$__GESTION_DIR/login.php");

  $formation=$_GET["id_form"];

  $jour_inf=$_GET["jour_inf"];
  $jour_sup=$_GET["jour_sup"];

  // Inversion, au cas où
  if($jour_inf>$jour_sup)
  {
    $temp=$jour_inf;
    $jour_inf=$jour_sup;
    $jour_sup=$temp;
  }

  if($jour_sup==0) // cas particulier pour les dates =0
    $condition_jour="AND $_DBC_cand_date_prise_decision='0'";
  else
    $condition_jour="AND $_DBC_cand_date_prise_decision BETWEEN '$jour_inf' AND '$jour_sup'";

  // Forcer la date des lettres ?
  // TODO : améliorer les tests de validité de cette date ou laisser la responsabilité à l'utilisateur ?
  if(isset($_GET["date"]) && ctype_digit($_GET["date"]) && date("Y", $_GET["date"])==date("Y", time()))
    $new_date=$_GET["date"];

  // Filtre sur une décision ?
  if(isset($_GET["decid"]) && array_key_exists($_GET["decid"], $__DOSSIER_DECISIONS_COURTES))
    $condition_decision="AND $_DBC_cand_decision='$_GET[decid]' ";

  // Vérification des paramètres
  $result=db_query($dbr, "SELECT $_DBC_cand_candidat_id, $_DBC_cand_id FROM $_DB_cand, $_DB_propspec, $_DB_candidat, $_DB_decisions
                  WHERE $_DBC_propspec_id=$_DBC_cand_propspec_id
                  AND $_DBC_cand_decision=$_DBC_decisions_id
                  AND $_DBC_candidat_id=$_DBC_cand_candidat_id
                  AND $_DBC_cand_propspec_id='$formation'
                  AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                  AND $_DBC_cand_decision!='$__DOSSIER_NON_TRAITE'
                  $condition_jour
                  $condition_decision
                  $order_by");

  $rows=db_num_rows($result);

  if(!$rows) // aucune décision
  {
    db_free_result($result);
    db_close($dbr);
  
    mail($__EMAIL_ADMIN, "[Précandidatures] - Erreur de génération de lettres", "=> Aucune lettre trouvée par la requête.\n\nJours : de $jour_inf à $jour_sup\nFormation : $formation");
/*
    print("requete : SELECT $_DBC_cand_candidat_id, $_DBC_cand_id FROM $_DB_cand, $_DB_propspec
                            WHERE $_DBC_propspec_id=$_DBC_cand_propspec_id
                            AND $_DBC_cand_propspec_id='$formation'
                            AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                            AND $_DBC_cand_decision!='$__DOSSIER_NON_TRAITE'
                            $condition_jour");
*/
    header("Location:../masse_pdf.php?erreur=1");
    exit();
  }
  else
  {
    $ensemble_candidats=array();

    for($i=0; $i<$rows; $i++)
    {
      list($candidat_id, $cand_id)=db_fetch_row($result, $i);

      $ensemble_candidats["$candidat_id"]=$cand_id;
    }

    db_free_result($result);
  }
}
else
{
  // Candidat unique
  if(isset($argv[1]))
    $candidat_id=$argv[1];
  elseif(isset($_SESSION["candidat_id"]))
    $candidat_id=$_SESSION["candidat_id"];
  elseif(isset($_GET["cid"]))
    $candidat_id=$_GET["cid"];

  // identifiant de la candidature
  if(isset($argv[2]))
    $cand_id=$argv[2];
  elseif(isset($_GET["cand_id"]))
    $cand_id=$_GET["cand_id"];

  if(isset($cand_id) && ($cand_id=="all" || ctype_digit($cand_id)) && isset($candidat_id) && ctype_digit($candidat_id))
    $ensemble_candidats=array($candidat_id => $cand_id);
}

if(isset($ensemble_candidats) && count($ensemble_candidats))
{
  // Utilisation de la librairie fpdf (libre)
  require("$__FPDF_DIR_ABS/fpdf.php");

  // Création du PDF
  $lettre_decision=new FPDF("P","mm","A4");

  $lettre_decision->SetCreator("Application de Gestion des Candidatures de l'Université de Strasbourg");
  $lettre_decision->SetAuthor("Christophe BOCCHECIAMPE - UFR de Mathématique et d'Informatique - Université de Strasbourg");
  $lettre_decision->SetSubject("Décision de la Commission Pédagogique");
  $lettre_decision->SetTitle("Décision de la Commission Pédagogique");

  $lettre_decision->SetAutoPageBreak(1,11);
  // $lettre_decision->SetMargins(11,11,11);

  // TODO : ATTENTION : NE PAS OUBLIER DE GENERER LA FONTE ARIBLK.TTF LORS D'UN CHANGEMENT DE MACHINE
  $lettre_decision->AddFont("arial_black");

  // Compteur pour savoir si tout s'est bien passé, à la fin
  $nb_pages=0;

  foreach($ensemble_candidats as $candidat_id => $cand_id)
  {
    $candidat_array=__get_infos_candidat($dbr, $candidat_id);

    if($candidat_array!=FALSE)
    {
      if($candidat_array["civilite"]=="M")
      {
        // $civ_texte=$candidat_array["civ_texte"]=civ_lang($candidat_array["civilite"], $lettre_lang, 1);
        $mis="mis";
        $ne="né le";
        $interesse="intéressé";
        $autorise="autorisé";
      }
      else
      {
        $mis="mise";
        $ne="née le";
        $interesse="intéressée";
        $autorise="autorisée";
/*
        if($candidat_array["civilite"]=="Mlle")
          $civ_texte=$candidat_array["civ_texte"]="Mademoiselle";
        else
          $civ_texte=$candidat_array["civ_texte"]="Madame";
*/
      }

      // Cursus
      $cursus_array=__get_cursus($dbr,$candidat_id);

      // Récupération des informations sur la candidature
      $candidature_array=__get_candidature($dbr,$cand_id);

      if($candidature_array!=FALSE)
      {
        $result=db_query($dbr,"SELECT $_DBC_composantes_logo, $_DBC_composantes_txt_logo, $_DBC_composantes_txt_scol,
                            $_DBC_composantes_txt_sign, $_DBC_composantes_largeur_logo,
                            $_DBC_universites_couleur_texte_lettres, $_DBC_composantes_adr_pos_x,
                            $_DBC_composantes_adr_pos_y, $_DBC_composantes_corps_pos_x, $_DBC_composantes_corps_pos_y
                          FROM $_DB_composantes, $_DB_universites
                        WHERE $_DBC_composantes_id='$_SESSION[comp_id]'
                        AND $_DBC_composantes_univ_id=$_DBC_universites_id");

        $rows=db_num_rows($result);
        
        if($rows)
          list($logo_defaut, $txt_logo_defaut, $txt_scol_defaut, $txt_sign_defaut, $largeur_logo_defaut,
              $univ_couleur_texte, $adr_pos_x_defaut, $adr_pos_y_defaut, $corps_pos_x_defaut,
              $corps_pos_y_defaut)=db_fetch_row($result,0);

        db_free_result($result);

        if(!empty($candidature_array["spec_nom"]))
        {
          // Sélection des lettres à produire
          // Cas particulier : candidatures à choix multiples : on doit d'abord vérifier les décisions

          $flag_multiple=0; // par défaut

          if($candidature_array["groupe_spec"]!="-1")
          {
            $candidatures_multiples=__get_candidatures_multiples($dbr,$cand_id);

            if(count($candidatures_multiples) > 1) // si =1 : lettre normale
            {
              $flag_multiple=1;
              $liste_trouvee=$admission_trouvee=$transmission_trouvee=0;

              foreach($candidatures_multiples as $cand_m_array)
              {
                if($cand_m_array["decision"]==$__DOSSIER_ADMIS || $cand_m_array["decision"]==$__DOSSIER_ADMIS_AVANT_CONFIRMATION || $cand_m_array["decision"]==$__DOSSIER_ADMISSION_CONFIRMEE || $cand_m_array["decision"]==$__DOSSIER_ADMIS_LISTE_COMP || $cand_m_array["decision"]==$__DOSSIER_SOUS_RESERVE)
                  $admission_trouvee=1;
                elseif($cand_m_array["decision"]==$__DOSSIER_TRANSMIS)
                  $transmission_trouvee=1;
                elseif($cand_m_array["decision"]==$__DOSSIER_LISTE)
                  $liste_trouvee=1;
              }

              // Application des critères de recherche, en fonction des décisions trouvées (l'ordre est capital)
              if($admission_trouvee)
                $critere_multiples="AND $_DBC_lettres_dec_dec_id IN ('$__DOSSIER_ADMIS_AVANT_CONFIRMATION', '$__DOSSIER_ADMIS', '$__DOSSIER_ADMISSION_CONFIRMEE', '$__DOSSIER_SOUS_RESERVE','$__DOSSIER_ADMIS_LISTE_COMP')";
              elseif($transmission_trouvee)
                $critere_multiples="AND $_DBC_lettres_dec_dec_id='$__DOSSIER_TRANSMIS'";
              elseif($liste_trouvee)
                $critere_multiples="AND $_DBC_lettres_dec_dec_id='$__DOSSIER_LISTE'";
              else
                $critere_multiples="AND $_DBC_lettres_dec_dec_id NOT IN ('$__DOSSIER_LISTE', '$__DOSSIER_ADMIS_AVANT_CONFIRMATION', '$__DOSSIER_ADMIS', '$__DOSSIER_ADMISSION_CONFIRMEE','$__DOSSIER_SOUS_RESERVE','$__DOSSIER_TRANSMIS','$__DOSSIER_ADMIS_LISTE_COMP')";
            }
          }

          if(!$flag_multiple)
            $result=db_query($dbr,"SELECT $_DBC_lettres_id, $_DBC_lettres_logo, $_DBC_lettres_txt_logo, $_DBC_lettres_txt_scol,
                                $_DBC_lettres_txt_sign, $_DBC_lettres_largeur_logo, $_DBC_lettres_flag_logo,
                                $_DBC_lettres_flag_txt_logo, $_DBC_lettres_flag_txt_scol, $_DBC_lettres_flag_txt_sign,
                                $_DBC_lettres_flag_adr_cand, $_DBC_lettres_flag_date, $_DBC_lettres_flag_adr_pos,
                                $_DBC_lettres_adr_pos_x, $_DBC_lettres_adr_pos_y, $_DBC_lettres_flag_corps_pos,
                                $_DBC_lettres_corps_pos_x, $_DBC_lettres_corps_pos_y, $_DBC_lettres_langue
                            FROM $_DB_lettres, $_DB_lettres_dec, $_DB_lettres_propspec
                              WHERE $_DBC_lettres_comp_id='$_SESSION[comp_id]'
                              AND $_DBC_lettres_id=$_DBC_lettres_dec_lettre_id
                              AND $_DBC_lettres_propspec_lettre_id=$_DBC_lettres_id
                              AND $_DBC_lettres_propspec_propspec_id='$candidature_array[propspec_id]'
                              AND $_DBC_lettres_dec_dec_id='$candidature_array[decision]'");
          else
            $result=db_query($dbr,"SELECT distinct($_DBC_lettres_id), $_DBC_lettres_logo, $_DBC_lettres_txt_logo, $_DBC_lettres_txt_scol,
                                $_DBC_lettres_txt_sign, $_DBC_lettres_largeur_logo, $_DBC_lettres_flag_logo,
                                $_DBC_lettres_flag_txt_logo, $_DBC_lettres_flag_txt_scol, $_DBC_lettres_flag_txt_sign,
                                $_DBC_lettres_flag_adr_cand, $_DBC_lettres_flag_date, $_DBC_lettres_flag_adr_pos,
                                $_DBC_lettres_adr_pos_x, $_DBC_lettres_adr_pos_y, $_DBC_lettres_flag_corps_pos,
                                $_DBC_lettres_corps_pos_x, $_DBC_lettres_corps_pos_y, $_DBC_lettres_langue
                            FROM $_DB_lettres, $_DB_lettres_dec, $_DB_lettres_groupes
                              WHERE $_DBC_lettres_comp_id='$_SESSION[comp_id]'
                              AND $_DBC_lettres_id=$_DBC_lettres_dec_lettre_id
                              AND $_DBC_lettres_choix_multiples='1'
                              AND $_DBC_lettres_id IN (SELECT $_DBC_lettres_groupes_lettre_id FROM $_DB_lettres_groupes 
                                               WHERE $_DBC_lettres_groupes_groupe_id IN (SELECT distinct($_DBC_groupes_spec_groupe) 
                                                                           FROM $_DB_groupes_spec 
                                                                           WHERE $_DBC_groupes_spec_propspec_id='$candidature_array[propspec_id]'))
                              $critere_multiples");

          $rows=db_num_rows($result);

          if($rows)
          {
            // A partir d'ici, on extrait les informations de la lettre correspondant à la décision

            for($i=0; $i<$rows; $i++)
            {
              list($lettre_id, $logo, $txt_logo, $txt_scol, $txt_sign, $largeur_logo, $flag_logo, $flag_txt_logo,
                  $flag_txt_scol, $flag_txt_sign, $flag_adr_cand, $flag_date, $flag_adr_pos, $adr_pos_x,
                  $adr_pos_y, $flag_corps_pos, $corps_pos_x, $corps_pos_y, $lettre_lang)=db_fetch_row($result, $i);

              // Nécessaire pour la fonction de traitement des macros : l'identifiant de la lettre va servir à récupérer sa "signature"
              $_SESSION["lettre_id"]=$lettre_id;

              // Informations par défaut, si besoin
              // ==> Si un flag vaut "t" (TRUE), ça signifie qu'on doit prendre la valeur par défaut
              // ==> S'il faut "f" (FALSE), on prend l'info spécifique à cette lettre

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

              $lettre_decision->AddPage();

              // Incrémentation du Compteur pour savoir si tout s'est bien passé, à la fin
              $nb_pages++;

              if(!empty($txt_logo))
              {
                // $txt_logo=str_replace("\r\n","\n", $txt_logo);

                // $lettre_decision->Cell(42,5,"$txt_logo",0, 2, "R");
                $lettre_decision->SetXY(11, 10);
                $lettre_decision->SetFont('arial_black','',12);

                if(!empty($univ_couleur_texte))
                {
                  // Conversion hexa (#112233) => décimal
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

                $hauteur_min_line=$lettre_decision->getY() + 3;

                $lettre_decision->Line(11, $hauteur_min_line, 55, $hauteur_min_line);
                $lettre_decision->Ln(5);

                $hauteur_min_logo=$lettre_decision->getY();
              }
              else
                $hauteur_min_logo=10;

              // LOGO
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
                    elseif($largeur_logo>44)
                    {
                      $largeur_logo=44;
                      $X_logo=11;
                    }
                    else
                      $X_logo=55-$largeur_logo;
                  }
                  elseif($largeur_logo>44)
                  {
                    $largeur_logo=44;
                    $X_logo=11;
                  }
                  else
                    $X_logo=55-$largeur_logo;

                  $lettre_decision->image($logo_img, $X_logo, $hauteur_min_logo, $largeur_logo);
                }
              }

              $txt_scol_hauteur_courante=$lettre_decision->GetY();

              $lettre_decision->SetFont('arial','',10);
              $lettre_decision->SetTextColor(0, 0, 0);

              // DATE ET INFOS DU CANDIDATS
              // Flag_date de chaque lettre : 1 = date de commission || -1 : date du jour || 0 : rien

              if($flag_date!=0) 
              {
                if($flag_date==1)
                {
                  if(isset($new_date)) // Si la date a été forcée
                    $date=date_lang($new_date, $lettre_lang, 1, 0);
                  else
                    $date=date_lang($candidature_array["date_decision_unix"], $lettre_lang, 1, 0);
                }
                else
                  $date=date_fr("j F Y");

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
                // $lettre_decision->SetXY(109, 42);
                if($flag_adr_pos=="t")
                  $lettre_decision->SetXY($adr_pos_x_defaut, $adr_pos_y_defaut);
                else
                  $lettre_decision->SetXY($adr_pos_x, $adr_pos_y);

                $candidat_adresse=civ_lang($candidat_array["civilite"], $lettre_lang, 2)." "
                                  .$candidat_array["nom"]." "
                                  .$candidat_array["prenom"]."\n"
                                  .$candidat_array["adresse"];
                 
                $lettre_decision->MultiCell(0,5,$candidat_adresse, 0, "L");
              }

              $elements_corps=get_all_elements($dbr, $lettre_id);
              $nb_elem_corps=count($elements_corps);

              if($nb_elem_corps)
              {
                // Premier élément : position fixe (à affiner manuellement, sans doute)
                $lettre_decision->SetXY($corps_pos_x, $corps_pos_y);

                for($j=0; $j<$nb_elem_corps; $j++)
                {
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

                              // Calcul de la hauteur de la ligne en fonction de la taille du texte
                              // >14          => 6 (et + ?)
                              // de 10 à 12       => 5
                              // inférieure à 8   => 4

                              if($txt_taille>14)
                                $hauteur_cell="6";
                              elseif($txt_taille >= 10 && $txt_taille <=12)
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

                              break;
                            
                    case 8  : // séparateur : hauteur en fonction du "nombre de lignes" (1 ligne = "5")

                            $hauteur=ctype_digit($elements_corps["$j"]["nb_lignes"]) && $elements_corps["$j"]["nb_lignes"]>1 ? $elements_corps["$j"]["nb_lignes"]*5 : 5;

                            $lettre_decision->Ln($hauteur);

                            break;
                  }
                }
              }

              // Signature : obsolète, remplacée par une macro
/*
              $lettre_decision->Ln(5);

              if(!empty($txt_sign))
              {
                $txt_sign=pdf_traitement_macros($txt_sign, $candidat_array, $candidature_array, $cursus_array, $lettre_lang);

                $Y=$lettre_decision->GetY();

                $lettre_decision->SetXY(105, $Y);

                $lettre_decision->SetFont('arial','',10);
                $lettre_decision->MultiCell(0,5,$txt_sign, 0, "J");
              }
*/
              // Scolarité
              if(!empty($txt_scol))
              {
                $txt_scol=pdf_traitement_macros($dbr, $txt_scol, $candidat_array, $candidature_array, $cursus_array, $lettre_lang);

                // Calcul de la hauteur du texte, en nombre de lignes (on compte les \n)
                $hauteur_txt=mb_substr_count($txt_scol, "\n", "UTF-8");
                $hauteur_txt_mm=5*$hauteur_txt;
                
                $hauteur_disponible=275-$txt_scol_hauteur_courante; // (en millimètres)

                $Y_txt_scol=$txt_scol_hauteur_courante+($hauteur_disponible-$hauteur_txt_mm);

                // print("DBG : hauteur_txt = 5 x $hauteur_txt = $hauteur_txt_mm mm<br>\nDBG : Y : $Y_txt_scol");

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
            }
          }
        }

        db_free_result($result);
      }
    }

    write_evt($dbr, $__EVT_ID_G_PREC, "Lettre : $candidature_array[nom_complet] : $candidature_array[decision_txt]", $candidat_id, $cand_id);
  }

  if(isset($nb_pages) && $nb_pages>0)
  {
    if(count($ensemble_candidats)==1) // élement unique
    {
      // Nettoyage du nom du candidat
      // TODO : Généraliser et créer une fonction

      $candidat_nom=$new_str=preg_replace("/[ '\"&#\/\\\]/", "_", clean_str(mb_strtolower($candidat_array["nom"], "UTF-8")));
      $candidat_prenom=preg_replace("/[ '\"&#\/\\\]/", "_", clean_str(mb_strtolower($candidat_array["prenom"], "UTF-8")));

      $nom_fichier=clean_str("Decision_" . $candidat_nom . "_" . $candidat_prenom . "_$cand_id.pdf");
    }
    else
    {
      $date=date("j_F_Y_H_i", time());
      $nom_fichier=clean_str($_SESSION["auth_user"] . "_Traitement_masse_$date.pdf");
    }

    // $lettre_decision->Output("$nom_fichier", "I");

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
