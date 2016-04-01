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
  session_name("preinsc");
  session_start();

  include "../configuration/aria_config.php";
  include "$__INCLUDE_DIR_ABS/vars.php";
  include "$__INCLUDE_DIR_ABS/fonctions.php";
  include "$__INCLUDE_DIR_ABS/access_functions.php";
  include "$__INCLUDE_DIR_ABS/db.php";

  $php_self=$_SERVER['PHP_SELF'];
  $_SESSION['CURRENT_FILE']=$php_self;

  if(!isset($_SESSION["authentifie"]))
  {
    session_write_close();
    header("Location:../index.php");
    exit();
  }

  $candidat_id=$_SESSION["authentifie"];
  $candidature_id="";
  $condition_candidature_id="";

  // candidature ou composante
  if(isset($_GET["cand_id"]) && ctype_digit($_GET["cand_id"])) {
    $candidature_id=$_GET["cand_id"];
    $condition_candidature_id="AND $_DBC_cand_id=$candidature_id";
  } 
  else { // message d'erreur
    die("Erreur : il n'est plus possible de générer les récapitulatifs des années antérieures");
  }
  
  if(isset($_GET["comp_id"]) && ctype_digit($_GET["comp_id"]))
    $comp_id=$_GET["comp_id"];
  elseif(isset($_SESSION["comp_id"]) && ctype_digit($_SESSION["comp_id"]))
    $comp_id=$_SESSION["comp_id"];
  else
    die("Paramètres incorrects : merci de contacter rapidement l'administrateur système (lien 'Signaler un problème technique')\n");

  
  $condition_comp="AND $_DBC_propspec_comp_id='$comp_id'";
  $condition_comp_autres="AND $_DBC_propspec_comp_id!='$comp_id'";

  $dbr=db_connect();

  // Utilisation de la librairie fpdf (libre)
  require("$__FPDF_DIR_ABS/fpdf.php");

  // Création du document. P = Portrait, unité = millimètre (mm), Page = A4
  $page_garde_pdf=new FPDF("P","mm","A4");
  $page_garde_pdf->SetCreator("Application de Gestion des Candidatures de l'Université de Strasbourg");
  $page_garde_pdf->SetAuthor("Christophe BOCCHECIAMPE - UFR de Mathématique et d'Informatique - Université de Strasbourg");
  $page_garde_pdf->SetSubject("Page de Garde à joindre au dossier");

  $page_garde_pdf->SetAutoPageBreak(1,11);

  // TODO : ATTENTION : NE PAS OUBLIER DE GENERER LA FONTE ARIBLK.TTF LORS D'UN CHANGEMENT DE MACHINE
  $page_garde_pdf->AddFont("arial_black");

  // Compteur pour savoir si tout s'est bien passé, à la fin
  $nb_pages=0;

  
  // TODO 6 mars 2006 : optimiser le code (appels à la base de données, switch() à simplifier, ...)

  $result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom,
                      $_DBC_candidat_date_naissance, 
                      $_DBC_candidat_nationalite as nat_code,
                      CASE WHEN $_DBC_candidat_nationalite IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite) 
                          THEN (SELECT $_DBC_pays_nat_ii_nat FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite)
                          ELSE '' END as nationalite,
                      $_DBC_candidat_telephone,
                      $_DBC_candidat_adresse_1, $_DBC_candidat_adresse_2, $_DBC_candidat_adresse_3, $_DBC_candidat_numero_ine, $_DBC_candidat_email,
                      $_DBC_candidat_lieu_naissance, 
                      $_DBC_candidat_pays_naissance as pays_code, 
                      CASE WHEN $_DBC_candidat_pays_naissance IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance) 
                          THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance)
                          ELSE '' END as pays_naissance,
                      $_DBC_candidat_lockdate,
                      $_DBC_candidat_adresse_cp, $_DBC_candidat_adresse_ville, 
                      $_DBC_candidat_adresse_pays as adresse_pays_code,
                      CASE WHEN $_DBC_candidat_adresse_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_adresse_pays) 
                          THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_adresse_pays)
                          ELSE '' END as adresse_pays
                FROM $_DB_candidat WHERE $_DBC_candidat_id='$candidat_id'");

  $rows=db_num_rows($result); // normalement, un seul résultat

  if($rows)
  {
    // Génération du PDF récapitulatif
    list($candidat_id,$cand_civ,$cand_nom,$cand_prenom,$cand_naissance,$cand_nat_code, $cand_nat,$cand_tel,$cand_adr_1,$cand_adr_2,$cand_adr_3,$cand_num_ine, 
        $cand_email,$cand_lieu_naissance, $pays_naissance_code, $pays_naissance, $cand_lockdate, $adr_cp,$adr_ville,
        $adr_pays_code, $adr_pays)=db_fetch_row($result,0);

    switch($cand_civ)
    {
      case "M" :    $ne_le="Né le";
                $civ_mail="M.";
                $inscrit="inscrit";
                break;

      case  "Mlle"  :   $ne_le="Née le";
                  $civ_mail="Mlle";
                  $inscrit="inscrite";
                  break;

      case  "Mme" :   $ne_le="Née le";
                $civ_mail="Mme";
                $inscrit="inscrite";
                break;

      default     : $ne_le="Né le";
                  $civ_mail="M.";
                  $inscrit="inscrit";
    }

    $naissance_txt=date_fr("j F Y",$cand_naissance);

    $identite="Candidat : $cand_nom $cand_prenom, $ne_le $naissance_txt ($cand_lieu_naissance, $pays_naissance)\nNationalité : $cand_nat";

    if($cand_num_ine!="")
      $identite.="\nNuméro INE : $cand_num_ine";

    $page_garde_pdf->AddPage();

    // Incrémentation du Compteur pour savoir si tout s'est bien passé, à la fin
    $nb_pages++;

    $page_garde_pdf->SetFont('Arial','B',12);

    $date=time();

    $titre="Précandidatures - ". date_fr("j F Y",$date);
    
    // Nom de la formation
    if(isset($candidature_id)) {
      $cand_array = __get_candidature($dbr, $candidature_id);
      $titre .= "\n".$cand_array["texte_formation"];
       
      $page_garde_pdf->MultiCell(0,10,$titre,1,'C');
    }
    else {
      $page_garde_pdf->Cell(0,10,$titre,1,1,'C');
    }

    $page_garde_pdf->SetFont('Arial','',10);
    $page_garde_pdf->Cell(0,10,'Merci d\'imprimer ce document et de le joindre à CHAQUE correspondance courrier.',0,1,'C');

    $page_garde_pdf->SetFont('Arial','B',12);
    $page_garde_pdf->Cell(0,10,'Vous',0,1,'L');

    $page_garde_pdf->SetFont('Arial','',10);
    $page_garde_pdf->MultiCell(0,6,$identite,0,'L');

    // Adresse : formatage un peu spécial : tableau
    $page_garde_pdf->Cell(20,6,'Adresse : ',0,0,'L');

      $adresse=$cand_adr_1;
      $adresse.=$cand_adr_2!="" ? "\n".$cand_adr_2 : "";
      $adresse.=$cand_adr_3!="" ? "\n".$cand_adr_3 : "";
    $adresse.="\n$adr_cp $adr_ville\n$adr_pays";
    $page_garde_pdf->MultiCell(0,6,$adresse,0,'L');

    $page_garde_pdf->Ln(6);


    // =============================
    //    Résumé du cursus
    // =============================

    $result2=db_query($dbr,"(SELECT   $_DBC_cursus_id, $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_annee, $_DBC_cursus_ecole,
                          $_DBC_cursus_ville, 
                          CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
                            THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
                            ELSE '' END as cursus_pays,
                          $_DBC_cursus_mention, $_DBC_cursus_moyenne,
                          $_DBC_cursus_spec
                      FROM $_DB_cursus
                    WHERE $_DBC_cursus_candidat_id='$candidat_id'
                    AND   $_DBC_cursus_annee='0')
                  UNION ALL
                    (SELECT   $_DBC_cursus_id, $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_annee, $_DBC_cursus_ecole,
                          $_DBC_cursus_ville, 
                          CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
                            THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
                            ELSE '' END as cursus_pays,
                          $_DBC_cursus_mention, $_DBC_cursus_moyenne,
                          $_DBC_cursus_spec
                      FROM $_DB_cursus
                    WHERE $_DBC_cursus_candidat_id='$candidat_id'
                    AND   $_DBC_cursus_annee!='0'
                      ORDER BY $_DBC_cursus_annee DESC)");
    $rows2=db_num_rows($result2);

    if($rows2)
    {
      $page_garde_pdf->SetFont('Arial','B',12);
      $page_garde_pdf->Cell(0,10,'Votre cursus :',0,1,'L');

      $page_garde_pdf->SetFont('Arial','',10);

      for($j=0; $j<$rows2; $j++)
      {
        list($cid, $dip, $int, $annee_obt,$ecole,$ville,$pays,$mention, $moyenne, $spec)=db_fetch_row($result2,$j);

        if($annee_obt==0 || $annee_obt==date("Y"))
          $annee_obt="En cours (" . date("Y") . ")";

        if(!empty($pays))
          $pays="- ". preg_replace("/_/","",$pays);
        else
          $pays="";

        if(!empty($mention))
        {
          if($mention=="Ajourné" || $mention=="Sans objet")
            $mention_txt=" - $mention";
          else
            $mention_txt=" - Mention " . mb_strtolower($mention, "UTF-8");
        }
        else
          $mention_txt="";

        $cursus="$annee_obt : $dip $int ($ecole, $ville $pays)$mention_txt";

        if($moyenne!="")
          $cursus.=" - Moyenne : $moyenne";

        $page_garde_pdf->MultiCell(0,6,$cursus,0,'L');

        // Spécialité : affichage sur une nouvelle ligne, avec l'alinéa adéquat (largeur = année d'obtention)
        if(trim($spec)!="")
        {
          $alinea_w=$page_garde_pdf->GetStringWidth("$annee_obt :");
          $spec_txt="Spécialité : $spec";

          $page_garde_pdf->SetX(11+$alinea_w);
          $page_garde_pdf->MultiCell(0,6,$spec_txt,0,'L');
        }
      }
    }
    db_free_result($result2);

    $page_garde_pdf->Ln(6);


    // ===================================================
    //        Langues
    // ===================================================

    $result2=db_query($dbr,"SELECT $_DBC_langues_id, $_DBC_langues_langue, $_DBC_langues_niveau, $_DBC_langues_annees
                      FROM $_DB_langues
                    WHERE $_DBC_langues_candidat_id='$candidat_id'
                      ORDER BY $_DBC_langues_langue ASC");

    $rows2=db_num_rows($result2);

    if($rows2)
    {
      $page_garde_pdf->SetFont('Arial','B',12);
      $page_garde_pdf->Cell(0,10,'Langues :',0,1,'L');

      $page_garde_pdf->SetFont('Arial','',10);

      for($j=0; $j<$rows2; $j++)
      {
        list($la_id, $langue, $niveau, $nb_annees)=db_fetch_row($result2,$j);

        // Diplômes obtenus dans cette langue
        $result3=db_query($dbr,"SELECT $_DBC_langues_dip_id, $_DBC_langues_dip_nom, $_DBC_langues_dip_annee, $_DBC_langues_dip_resultat
                          FROM $_DB_langues_dip WHERE $_DBC_langues_dip_langue_id='$la_id'
                        ORDER BY $_DBC_langues_dip_annee");

        $rows3=db_num_rows($result3);

        $niveau_langue=explode("|",$niveau);
        $niveau_txt="";

        if(array_key_exists("0", $niveau_langue) && $niveau_langue[0])
          $niveau_txt="Lu";

        if(array_key_exists("1", $niveau_langue) && $niveau_langue[1])
        {
          if(!empty($niveau_txt))
            $niveau_txt.=", ";

          $niveau_txt.="Ecrit";
        }

        if(array_key_exists("2", $niveau_langue) && $niveau_langue[2])
        {
          if(!empty($niveau_txt))
            $niveau_txt.=", ";

          $niveau_txt.="Parlé";
        }

        if(array_key_exists("3", $niveau_langue) && $niveau_langue[3])
        {
          if(!empty($niveau_txt))
            $niveau_txt.=", ";

          $niveau_txt.="Langue maternelle";
        }

        if(!empty($nb_annees))
          $nb_annees=" - Nombre d'années : $nb_annees";

        $page_garde_pdf->MultiCell(0,6,"- $langue ($niveau_txt$nb_annees)",0,'L');

        if($rows3)
        {
          $langue_txt="";

          for($k=0; $k<$rows3; $k++)
          {
            list($langue_dip_id, $langue_dip, $langue_dip_annee, $langue_dip_resultat)=db_fetch_row($result3, $k);

            if(!empty($langue_txt))
              $langue_txt.="\n   ";

            if(!empty($langue_dip_annee) && $langue_dip_annee!=0)
              $langue_txt="$langue_dip_annee : $langue_dip";
            else
              $langue_txt="$langue_dip";

            if(!empty($langue_dip_resultat))
              $langue_txt.="   Résultat / Mention : $langue_dip_resultat";

            $page_garde_pdf->MultiCell(0,6,"   $langue_txt",0,'L');
          }
        }

        db_free_result($result3);
      }
    }

    db_free_result($result2);

    $page_garde_pdf->Ln(6);

    // ==================================================================
    //  Informations complémentaires et expériences professionnelles
    // ==================================================================

    $result2=db_query($dbr,"SELECT $_DBC_infos_comp_id, $_DBC_infos_comp_texte, $_DBC_infos_comp_annee, $_DBC_infos_comp_duree
                      FROM $_DB_infos_comp
                    WHERE $_DBC_infos_comp_candidat_id='$candidat_id'
                      ORDER BY $_DBC_infos_comp_annee DESC");
    $rows2=db_num_rows($result2);

    if($rows2)
    {
      $page_garde_pdf->SetFont('Arial','B',12);
      $page_garde_pdf->Cell(0,10,'Informations complémentaires et expériences professionnelles',0,1,'L');

      $page_garde_pdf->SetFont('Arial','',10);

      for($j=0; $j<$rows2; $j++)
      {
        list($iid, $info,$annee,$duree)=db_fetch_row($result2,$j);
        $info=str_replace("\n","\n ",$info);

        if($duree=="")
          $dur="";
        else
          $dur="($duree)";

        $page_garde_pdf->MultiCell(0,6,$annee .' '.$dur.' '.$info,0,'L');

      }
      $page_garde_pdf->Ln(6);
    }
    db_free_result($result2);

    $page_garde_pdf->SetFont('Arial','',10);

    // ==========================================
    //        Précandidatures
    // ==========================================

    // Calcul des frais de dossiers
    $frais_dossiers_array=array();

    $result2=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee,
                         $_DBC_specs_nom, $_DBC_propspec_frais, $_DBC_propspec_finalite,
                         $_DBC_cand_periode, $_DBC_cand_vap_flag
                          FROM $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec
                        WHERE $_DBC_cand_candidat_id='$candidat_id'
                        AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                        AND $_DBC_propspec_annee=$_DBC_annees_id
                        AND $_DBC_propspec_id_spec=$_DBC_specs_id
                        AND $_DBC_cand_statut!='$__PREC_ANNULEE'
                        $condition_comp
                          ORDER BY $_DBC_cand_periode DESC, $_DBC_cand_ordre ASC, $_DBC_cand_ordre_spec ASC");
    $rows2=db_num_rows($result2);

    $old_periode="--";

    if($rows2)
    {
      // on conserve les formations, on les utilisera plus tard (évite une boucle pour les renseignements complémentaires)
      $array_propspec=array();

      $page_garde_pdf->SetFont('Arial','B',12);
      $page_garde_pdf->Cell(0,10,"Vos précandidatures pour cette composante :",0,1,'L');  

      for($j=0; $j<$rows2; $j++)
      {
        list($propspec_id, $nom_annee, $nom_specialite, $frais_dossiers, $finalite, $cand_periode, $vap_flag)=db_fetch_row($result2,$j);

        if($cand_periode!=$old_periode)
        {
          $page_garde_pdf->SetFont('Arial','B',10);
          $page_garde_pdf->Cell(0,6,"Année universitaire $cand_periode-".($cand_periode+1)." :",0,1,'L');
          $page_garde_pdf->SetFont('Arial','',10);
          $old_periode=$cand_periode;
        }

        // on stocke les frais de dossiers dans tableau
        if($frais_dossiers!="" && $frais_dossiers!=0)
        {
          if(array_key_exists("$frais_dossiers",$frais_dossiers_array))
            $frais_dossiers_array[$frais_dossiers]++;
          else
            $frais_dossiers_array[$frais_dossiers]=1;
        }

        $nom_finalite=$tab_finalite[$finalite];

        if(empty($nom_annee))
          $insc_texte="$nom_specialite $nom_finalite";
        else
          $insc_texte="$nom_annee - $nom_specialite $nom_finalite";

        if($vap_flag) {
          $insc_texte.=" - VAPP";
        }

        $array_propspec[$propspec_id]=$candidature="$insc_texte";

        $page_garde_pdf->Cell(0,6,$candidature,0,1,'L');
      }
    }

    db_free_result($result2);

    // ===================================================
    //    Renseignements supplémentaires
    // ===================================================

    // Transitoire : les anciens messages ne comportent pas l'identifiant de la candidature, on doit
    // conserver ce fonctionnement hybride

    $condition_periode = "AND $_DBC_dossiers_elems_contenu_periode='$cand_array[periode]'";
    $condition_periode2 = "AND $_DBC_cand_periode='$cand_array[periode]'";

    $result2 = db_query($dbr, "SELECT $_DBC_dossiers_ef_elem_id FROM $_DB_dossiers_ef
                                   WHERE $_DBC_dossiers_ef_propspec_id='$propspec_id'
                                   ORDER BY $_DBC_dossiers_ef_ordre");
                                   
    $rows2=db_num_rows($result2);

    if($rows2) {
      // Indicateur pour savoir à quel endroit afficher le titre de la section "Autres renseignements"
      $premier_element=1;
      
      // Initialisation du tableau qui contiendra les éléments devant être imprimés sur une page seule
      $array_elements_nouvelle_page=array();
        
      for($j=0; $j<$rows2; $j++)
      {
        list($elem_id)=db_fetch_row($result2,$j);
        
        $result3 = db_query($dbr,"SELECT $_DBC_dossiers_elems_para, $_DBC_dossiers_elems_contenu_para, 
                                         $_DBC_dossiers_elems_type, $_DBC_dossiers_elems_nouvelle_page
                                  FROM $_DB_dossiers_elems, $_DB_dossiers_elems_contenu
                                  WHERE $_DBC_dossiers_elems_id='$elem_id'
                                  AND $_DBC_dossiers_elems_contenu_candidat_id='$candidat_id'
                                  AND $_DBC_dossiers_elems_contenu_elem_id=$_DBC_dossiers_elems_id
                                  AND $_DBC_dossiers_elems_recapitulatif='t'
                                  $condition_periode
                                  group by $_DBC_dossiers_elems_para, $_DBC_dossiers_elems_contenu_para, 
                                         $_DBC_dossiers_elems_type, $_DBC_dossiers_elems_nouvelle_page");

        $rows3 = db_num_rows($result3);

        $prev_propspec_id="--";

        for($k=0; $k<$rows3; $k++) {
          list($para, $contenu, $elem_type, $nouvelle_page)=db_fetch_row($result3,$k);
      
          $para=str_replace("\r\n\r\n","\r\n", $para);

          if($contenu!="") {
            $contenu_txt_final="";

            // En fonction du contenu
            switch($elem_type) {
              case $__ELEM_TYPE_FORM : // Formulaire simple
                $contenu_txt_final=$contenu;
                break;

              case $__ELEM_TYPE_UN_CHOIX :
                // Traitement du contenu : normalement une seule réponse : id du choix
                if(ctype_digit($contenu)) {
                  $res_choix=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_texte
                                      FROM $_DB_dossiers_elems_choix
                                    WHERE $_DBC_dossiers_elems_choix_id='$contenu'");

                  if(db_num_rows($res_choix)) {
                    list($contenu_txt)=db_fetch_row($res_choix, 0);
                    $contenu_txt_final=$contenu_txt;
                  }

                  db_free_result($res_choix);
                }

                break;

              case $__ELEM_TYPE_MULTI_CHOIX :
                // Traitement du contenu : plusieurs réponses possibles séparées par "|" (id du ou des choix)
                $contenu_txt="";
                $choix_array=explode("|",$contenu);
                
                if(is_array($choix_array) && count($choix_array)) {
                  $liste_choix="";
                  foreach($choix_array as $choix_id) {
                    if(ctype_digit($choix_id))
                      $liste_choix.="$choix_id,";
                  }

                  if($liste_choix!="") {
                    $liste_choix=mb_substr($liste_choix, 0, -1, "UTF-8");

                    $res_choix=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_texte FROM $_DB_dossiers_elems_choix
                                      WHERE $_DBC_dossiers_elems_choix_id IN ($liste_choix)
                                      ORDER BY $_DBC_dossiers_elems_choix_ordre");

                    $nb_choix=db_num_rows($res_choix);

                    if($nb_choix) {
                      for($c=0; $c<$nb_choix; $c++) {
                        list($choix_texte)=db_fetch_row($res_choix, $c);
                        $contenu_txt.="- $choix_texte\n";
                      }
                    }
                
                    db_free_result($res_choix);

                    $contenu_txt_final=$contenu_txt;
                  }
                }

                break;
            } // fin du switch
          } // fin du if($contenu)

          // Le contenu est prêt : si l'élément ne doit pas figurer sur une page à part, on l'ajoute tel quel
          // sinon, on le stocke et on l'affichera en dernier (prévoir une fonction ?)
          if($nouvelle_page!="t") {
            if($premier_element) {
              $page_garde_pdf->SetFont('Arial','B',12);
              $page_garde_pdf->Cell(0,10,'Autres renseignements demandés par la Scolarité (texte en italique : énoncé)',0,1,'L');
              $premier_element=0;
            }

            $page_garde_pdf->SetFont('Arial','I',8);
            $page_garde_pdf->MultiCell(0,6, "$para",0,'J');
            $page_garde_pdf->Ln(3);
            $page_garde_pdf->SetFont('Arial','',10);

            if(isset($contenu_txt_final) && !empty($contenu_txt_final)) {
              $page_garde_pdf->MultiCell(0,6, $contenu_txt_final, 0, 'J');
            }
            else {
              $page_garde_pdf->MultiCell(0,6, "Champ non complété.", 0, 'J');
            }

            $page_garde_pdf->Ln(3);
          }
          else {
            if(!isset($contenu_txt_final) || empty($contenu_txt_final)) {
              $contenu_txt_final="Champ non complété";
            }

            $array_elements_nouvelle_page["$j"]=array("formation_enonce" => "$texte_formation\n$para",
                                        "contenu" => "$contenu_txt_final");
          }

        } // fin du for(rows3)
        db_free_result($result3);
      } // fin du for(rows2)
    }
    db_free_result($result2);
      
      
    /*
    if(!empty($condition_candidature_id)) {
      $result2=db_query($dbr,"SELECT $_DBC_dossiers_elems_para, $_DBC_dossiers_elems_contenu_para,
                              $_DBC_dossiers_elems_type, $_DBC_dossiers_ef_ordre as ordre, 
                              $_DBC_dossiers_elems_contenu_propspec_id, $_DBC_dossiers_elems_nouvelle_page
                          FROM $_DB_dossiers_elems, $_DB_dossiers_elems_contenu, $_DB_dossiers_ef
                        WHERE $_DBC_dossiers_elems_contenu_candidat_id='$candidat_id'
                        AND $_DBC_dossiers_elems_contenu_elem_id=$_DBC_dossiers_elems_id
                        AND $_DBC_dossiers_ef_elem_id=$_DBC_dossiers_elems_id
                        AND $_DBC_dossiers_elems_contenu_periode='$__PERIODE'
                        AND $_DBC_dossiers_elems_comp_id='$comp_id'
                        AND $_DBC_dossiers_elems_recapitulatif='t'
                        AND $_DBC_dossiers_ef_propspec_id=(SELECT $_DBC_cand_propspec_id FROM $_DB_cand
                                                           WHERE $_DBC_cand_id='$candidature_id')
                        ORDER BY ordre");
    }
    else {
        $result2=db_query($dbr,"(SELECT distinct($_DBC_dossiers_elems_para), $_DBC_dossiers_elems_contenu_para,
                              $_DBC_dossiers_elems_type, CAST('0' AS smallint),
                              $_DBC_dossiers_elems_contenu_propspec_id, $_DBC_dossiers_elems_nouvelle_page
                          FROM $_DB_dossiers_elems, $_DB_dossiers_elems_contenu, $_DB_dossiers_ef
                        WHERE $_DBC_dossiers_elems_contenu_candidat_id='$candidat_id'
                        AND $_DBC_dossiers_elems_contenu_elem_id=$_DBC_dossiers_elems_id
                        AND $_DBC_dossiers_ef_elem_id=$_DBC_dossiers_elems_id
                        AND $_DBC_dossiers_elems_contenu_periode='$__PERIODE'
                        AND $_DBC_dossiers_elems_comp_id='$comp_id'
                        AND $_DBC_dossiers_elems_unique='t'
                        AND $_DBC_dossiers_elems_recapitulatif='t')
                      UNION ALL
                        (SELECT $_DBC_dossiers_elems_para, $_DBC_dossiers_elems_contenu_para, 
                              $_DBC_dossiers_elems_type, $_DBC_dossiers_ef_ordre, 
                              $_DBC_dossiers_elems_contenu_propspec_id,
                              $_DBC_dossiers_elems_nouvelle_page
                          FROM $_DB_dossiers_elems, $_DB_dossiers_elems_contenu, $_DB_dossiers_ef
                        WHERE $_DBC_dossiers_elems_contenu_candidat_id='$candidat_id'
                        AND $_DBC_dossiers_elems_contenu_elem_id=$_DBC_dossiers_elems_id
                        AND $_DBC_dossiers_ef_elem_id=$_DBC_dossiers_elems_id
                        AND $_DBC_dossiers_ef_propspec_id=$_DBC_dossiers_elems_contenu_propspec_id
                        AND $_DBC_dossiers_elems_contenu_periode='$__PERIODE'
                        AND $_DBC_dossiers_elems_comp_id='$comp_id'
                        AND $_DBC_dossiers_elems_unique='f'
                        AND $_DBC_dossiers_elems_recapitulatif='t'
                        AND $_DBC_dossiers_ef_propspec_id IN (SELECT $_DBC_cand_propspec_id FROM $_DB_cand, $_DB_propspec
                                                  WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
                                                  AND $_DBC_propspec_comp_id='$comp_id'
                                                  AND $_DBC_cand_candidat_id='$candidat_id'
                                                  AND $_DBC_cand_periode='$__PERIODE'
                                                  AND $_DBC_cand_statut!='$__PREC_ANNULEE')
                          ORDER BY $_DBC_dossiers_ef_propspec_id, $_DBC_dossiers_ef_ordre)");
    }
    
    $rows2=db_num_rows($result2);

    if($rows2)
    {
      // Indicateur pour savoir à quel endroit afficher le titre de la section "Autres renseignements"
      $premier_element=1;

      // Initialisation du tableau qui contiendra les éléments devant être imprimés sur une page seule
      $array_elements_nouvelle_page=array();

      $prev_propspec_id="--";

      for($j=0; $j<$rows2; $j++)
      {
        list($para, $contenu, $elem_type, $elem_ordre, $propspec_id, $nouvelle_page)=db_fetch_row($result2,$j);

        if($propspec_id!=$prev_propspec_id)
        {
          if($propspec_id==0)
            $texte_formation="Question(s) relative(s) à toutes les formations choisies :";
          elseif(array_key_exists($propspec_id, $array_propspec))
            $texte_formation="Question(s) relative(s) au ".$array_propspec[$propspec_id]." :";

          $prev_propspec_id=$propspec_id;
        }

        $para=str_replace("\r\n\r\n","\r\n", $para);

        if($contenu!="")
        {
          $contenu_txt_final="";

          // En fonction du contenu
          switch($elem_type)
          {
            case $__ELEM_TYPE_FORM : // Formulaire simple
              $contenu_txt_final=$contenu;
              break;

            case $__ELEM_TYPE_UN_CHOIX :
              // Traitement du contenu : normalement une seule réponse : id du choix
              if(ctype_digit($contenu))
              {
                $res_choix=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_texte
                                    FROM $_DB_dossiers_elems_choix
                                  WHERE $_DBC_dossiers_elems_choix_id='$contenu'");

                if(db_num_rows($res_choix))
                {
                  list($contenu_txt)=db_fetch_row($res_choix, 0);
                  $contenu_txt_final=$contenu_txt;
                }

                db_free_result($res_choix);
              }

              break;

            case $__ELEM_TYPE_MULTI_CHOIX :
              // Traitement du contenu : plusieurs réponses possibles séparées par "|" (id du ou des choix)
              $contenu_txt="";
              $choix_array=explode("|",$contenu);
              
              if(is_array($choix_array) && count($choix_array))
              {
                $liste_choix="";
                foreach($choix_array as $choix_id)
                {
                  if(ctype_digit($choix_id))
                    $liste_choix.="$choix_id,";
                }

                if($liste_choix!="")
                {
                  $liste_choix=mb_substr($liste_choix, 0, -1, "UTF-8");

                  $res_choix=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_texte FROM $_DB_dossiers_elems_choix
                                    WHERE $_DBC_dossiers_elems_choix_id IN ($liste_choix)
                                    ORDER BY $_DBC_dossiers_elems_choix_ordre");

                  $nb_choix=db_num_rows($res_choix);

                  if($nb_choix)
                  {
                    for($c=0; $c<$nb_choix; $c++)
                    {
                      list($choix_texte)=db_fetch_row($res_choix, $c);
                      $contenu_txt.="- $choix_texte\n";
                    }
                  }
              
                  db_free_result($res_choix);

                  $contenu_txt_final=$contenu_txt;
                }
              }

              break;
          } // fin du switch
        } // fin du if(contenu)

        // Le contenu est prêt : si l'élément ne doit pas figurer sur une page à part, on l'ajoute tel quel
        // sinon, on le stocke et on l'affichera en dernier (prévoir une fonction ?)
        if($nouvelle_page!="t")
        {
          if($premier_element)
          {
            $page_garde_pdf->SetFont('Arial','B',12);
            $page_garde_pdf->Cell(0,10,'Autres renseignements demandés par la Scolarité (texte en italique : énoncé)',0,1,'L');
            $premier_element=0;
          }

          $page_garde_pdf->SetFont('Arial','I',8);
          
          if(empty($condition_candidature_id)) {
            $page_garde_pdf->MultiCell(0,6, "$texte_formation\n$para",0,'J');
          }
          else {
            $page_garde_pdf->MultiCell(0,6, "$para",0,'J');
          }
          
          // $page_garde_pdf->Ln(1);
          $page_garde_pdf->SetFont('Arial','',10);

          if(isset($contenu_txt_final) && !empty($contenu_txt_final))
            $page_garde_pdf->MultiCell(0,6, $contenu_txt_final, 0, 'J');
          else
            $page_garde_pdf->MultiCell(0,6, "Champ non complété.", 0, 'J');

          $page_garde_pdf->Ln(1);
        }
        else
        {
          if(!isset($contenu_txt_final) || empty($contenu_txt_final))
            $contenu_txt_final="Champ non complété";

          $array_elements_nouvelle_page["$j"]=array("formation_enonce" => "$texte_formation\n$para",
                                      "contenu" => "$contenu_txt_final");
        }
      } // fin du for
    }
    db_free_result($result2);
    */
    
    // ==========================================
    //  Autres candidatures
    // ==========================================

    $result2=db_query($dbr,"SELECT $_DBC_cand_id, $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite,
                         $_DBC_composantes_nom
                      FROM $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec, $_DB_composantes
                    WHERE $_DBC_cand_candidat_id='$candidat_id'
                    AND $_DBC_composantes_id=$_DBC_propspec_comp_id
                    AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                    AND $_DBC_propspec_annee=$_DBC_annees_id
                    AND $_DBC_propspec_id_spec=$_DBC_specs_id
                    AND $_DBC_cand_periode='$cand_array[periode]'
                    AND $_DBC_cand_statut!='$__PREC_ANNULEE'
                    $condition_comp_autres
                      ORDER BY $_DBC_cand_ordre, $_DBC_cand_ordre_spec ASC");
    $rows2=db_num_rows($result2);

    $old_comp_nom="";

    if($rows2)
    {
      $page_garde_pdf->Ln(6);

      $page_garde_pdf->SetFont('Arial','B',12);
      $page_garde_pdf->Cell(0,10,"Autres précandidatures pour l'année $__PERIODE-".($__PERIODE+1)." :",0,1,'L');

      $page_garde_pdf->SetFont('Arial','',10);

      for($j=0; $j<$rows2; $j++)
      {
        list($candidature_id, $propspec_id, $nom_annee, $nom_specialite, $finalite, $comp_nom)=db_fetch_row($result2,$j);

        $nom_finalite=$tab_finalite[$finalite];

        if(empty($nom_annee))
          $insc_texte="$nom_specialite $nom_finalite";
        else
          $insc_texte="$nom_annee - $nom_specialite $nom_finalite";

        $candidature="$insc_texte";

        if($comp_nom!=$old_comp_nom)
        {
          if($j)
            $page_garde_pdf->Ln(3);

          $page_garde_pdf->SetFont('Arial','B',10);
          $page_garde_pdf->Cell(0,6,"$comp_nom",0,1,'L');
          $page_garde_pdf->SetFont('Arial','',10);

          $old_comp_nom=$comp_nom;
        }

        $page_garde_pdf->Cell(0,6,$candidature,0,1,'L');
      }
    }

    db_free_result($result2);
  }

  // Eléments sur une seule page
  if(isset($array_elements_nouvelle_page) && is_array($array_elements_nouvelle_page) && count($array_elements_nouvelle_page))
  {
    $count=count($array_elements_nouvelle_page);
    $cnt=1;

    foreach($array_elements_nouvelle_page as $element_nouvelle_page)
    {
      $page_garde_pdf->AddPage();

      $page_garde_pdf->SetFont('Arial','IB',10);
      $page_garde_pdf->MultiCell(0,6, "[Autres renseignements sur page(s) séparée(s) - $cnt/$count]", 0, 'J');

      $page_garde_pdf->SetFont('Arial','I',8);
      $page_garde_pdf->MultiCell(0,6, $element_nouvelle_page["formation_enonce"],0,'J');

      $page_garde_pdf->Ln(3);
      $page_garde_pdf->SetFont('Arial','',10);

      $page_garde_pdf->MultiCell(0,6, $element_nouvelle_page["contenu"], 0, 'J');

      $page_garde_pdf->Ln(3);

      $cnt++;
    }
  }

  if(isset($nb_pages) && $nb_pages>0)
  {
    if(!is_dir("$__CAND_COMP_STOCKAGE_DIR_ABS/$comp_id/$_SESSION[authentifie]"))
      mkdir("$__CAND_COMP_STOCKAGE_DIR_ABS/$comp_id/$_SESSION[authentifie]", 0770, true);

    $nom_fichier=clean_str($_SESSION["authentifie"] . "_" . time() . "_Recapitulatif.pdf");

    $page_garde_pdf->Output("$__CAND_COMP_STOCKAGE_DIR_ABS/$comp_id/$_SESSION[authentifie]/$nom_fichier");

    write_evt($dbr, $__EVT_ID_C_DOC, "Génération du récapitulatif", $candidat_id, $comp_id);

    // Attention aux variables utilisées : chemin relatif à www-root (document_root du serveur Apache)
    echo "<HTML><SCRIPT>document.location='$__CAND_COMP_STOCKAGE_DIR/$comp_id/$_SESSION[authentifie]/$nom_fichier';</SCRIPT></HTML>";
  }
  db_free_result($result);
  db_close($dbr);
?>
