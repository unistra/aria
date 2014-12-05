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
include "../admin/editeur/include/editeur_fonctions.php";

$php_self=$_SERVER['PHP_SELF'];
$_SESSION['CURRENT_FILE']=$php_self;

verif_auth("../login.php");

$dbr=db_connect();

$Y=date("Y");
$Z=$Y+1;

// Largeur max du corps, en mm
// $__LARGEUR_MAX_CORPS="135";

if(array_key_exists("jour_inf", $_GET) && $_GET["jour_inf"]!="" && ctype_digit($_GET["jour_inf"]) && array_key_exists("jour_sup", $_GET) && $_GET["jour_sup"]!="" && ctype_digit($_GET["jour_sup"]) && isset($_GET["id_form"]) && ctype_digit($_GET["id_form"]))
{
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
      $condition_jour="AND $_DBC_cand_date_statut='0'";
   else
      $condition_jour="AND $_DBC_cand_date_statut BETWEEN '$jour_inf' AND '$jour_sup'";

   // Vérification des paramètres
   $result=db_query($dbr, "SELECT $_DBC_cand_candidat_id, $_DBC_cand_id FROM $_DB_cand, $_DB_propspec
                              WHERE $_DBC_propspec_id=$_DBC_cand_propspec_id
                           AND $_DBC_cand_propspec_id='$formation'
                           AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                           AND $_DBC_cand_decision='$__DOSSIER_NON_TRAITE'
                           AND $_DBC_cand_statut='$__PREC_RECEVABLE'
                           $condition_jour");

   $rows=db_num_rows($result);

   if(!$rows) // aucune décision
   {
      db_free_result($result);
      db_close($dbr);
   
      mail($__EMAIL_ADMIN, "[Précandidatures] - Génération des formulaires de Commission", "=> Aucune lettre trouvée par la requête.\n\nJours : de $jour_inf à $jour_sup\nFormation : $formation");

/*
      print("requete :    SELECT $_DBC_cand_candidat_id, $_DBC_cand_id FROM $_DB_cand, $_DB_propspec
                              WHERE $_DBC_propspec_id=$_DBC_cand_propspec_id
                              AND $_DBC_cand_propspec_id='$formation'
                              AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                              AND $_DBC_cand_decision='$__DOSSIER_NON_TRAITE'
                              AND $_DBC_cand_statut='$__PREC_RECEVABLE'
                              $condition_jour");
*/
      header("Location:../masse_formulaire.php?erreur=1");
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
   // identifiant de l'étudiant
   if(isset($argv[1]))
      $candidat_id=$argv[1];
   elseif(isset($_SESSION["candidat_id"]))
      $candidat_id=$_SESSION["candidat_id"];
   elseif(isset($_GET["cid"]))
      $candidat_id=$_GET["cid"];

   // identifiant de la candidature, potentiellement
   if(isset($argv[2]))
      $cand_id=$argv[2];
   elseif(isset($_GET["cand_id"]))
      $cand_id=$_GET["cand_id"];

   if(isset($cand_id) && ($cand_id=="all" || ctype_digit($cand_id)) && isset($candidat_id) && ctype_digit($candidat_id))
      $ensemble_candidats=array($candidat_id => $cand_id);
}

// Tableau aidant à déterminer les candidatures multiples déjà traitées
$array_multiples=array();

if(isset($ensemble_candidats) && count($ensemble_candidats))
{
   // Paramètre de gestion des motifs pour la composante
   // 0 => motifs courts, affichés sur le formulaire
   // 1 => phrases complètes, non affichées
   if(!isset($_SESSION["gestion_motifs"]) && isset($_SESSION["comp_id"]))
   {
      $res_motifs=db_query($dbr,"SELECT $_DBC_composantes_gestion_motifs FROM $_DB_composantes
                                 WHERE $_DBC_composantes_id='$_SESSION[comp_id]'");

      if(db_num_rows($res_motifs))
         list($_SESSION["gestion_motifs"])=db_fetch_row($res_motifs, 0);
      else
         $_SESSION["gestion_motifs"]=0;

      db_free_result($res_motifs);
   }

   // Utilisation de la librairie fpdf (libre)
   require("$__FPDF_DIR_ABS/fpdf.php");

   // Création du PDF
   $formulaire=new FPDF("P","mm","A4");

   $formulaire->SetCreator("Application de Gestion des Candidatures de l'Université de Strasbourg");
   $formulaire->SetAuthor("Christophe BOCCHECIAMPE - UFR de Mathématique et d'Informatique - Université de Strasbourg");
   $formulaire->SetSubject("Formulaire de Commission Pédagogique");
   $formulaire->SetTitle("Formulaire de Commission Pédagogique");

   $formulaire->SetAutoPageBreak(1,11);

   // TODO : ATTENTION : NE PAS OUBLIER DE GENERER LA FONTE ARIBLK.TTF LORS D'UN CHANGEMENT DE MACHINE
   $formulaire->AddFont("arial_black");

   // Compteur pour savoir si tout s'est bien passé, à la fin
   $nb_pages=0;

   foreach($ensemble_candidats as $candidat_id => $candidature_id)
   {
      $candidat_array=__get_infos_candidat($dbr, $candidat_id);

      if($candidat_array!=FALSE)
      {
         if($candidat_array["civilite"]=="M")
         {
            $civ_texte=$candidat_array["civ_texte"]="Monsieur";
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

            if($candidat_array["civilite"]=="Mlle")
               $civ_texte=$candidat_array["civ_texte"]="Mademoiselle";
            else
               $civ_texte=$candidat_array["civ_texte"]="Madame";
         }

         // Cas particulier : générer tous les formulaires de Commission pour un candidat particulier
         if($candidature_id=="all")
         {
            $result_cand=db_query($dbr, "SELECT $_DBC_cand_id FROM $_DB_cand, $_DB_propspec
                                          WHERE $_DBC_cand_candidat_id='$candidat_id'
                                          AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                                          AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                          AND $_DBC_cand_periode='$__PERIODE'");

            $rows_cand=db_num_rows($result_cand);

            $array_cand=array();

            for($i=0; $i<$rows_cand; $i++)
               list($array_cand[$i])=db_fetch_row($result_cand, $i);

            db_free_result($result_cand);
         }
         elseif(ctype_digit($candidature_id)) // une seule candidature (ou traitement de plusieurs candidats, en masse)
            $array_cand=array("0" => $candidature_id);

         if(isset($array_cand) && count($array_cand))
         {
            foreach($array_cand as $cand_id)
            {
               // Récupération des informations sur la candidature
               $candidature_array=__get_candidature($dbr,$cand_id);

               // Paramètre capital : spécialités groupées ?
               if($candidature_array["groupe_spec"]>=0)
               {
                  if(!array_key_exists($candidat_id, $array_multiples))
                  {
                     $array_multiples[$candidat_id]=array("$candidature_array[groupe_spec]" => "$candidature_array[ordre_spec]");
                     $sub_formations=1;
                  }
                  elseif(!array_key_exists($candidature_array["groupe_spec"], $array_multiples[$candidat_id]))
                  {
                     $array_multiples[$candidat_id][$candidature_array["groupe_spec"]]=$candidature_array["ordre_spec"];
                     $sub_formations=1;
                  }
                  else
                     $sub_formations=0; // candidature multiple déjà traitée

                  if($sub_formations)
                  {
                     $res_multiples=db_query($dbr, "SELECT $_DBC_cand_id FROM $_DB_cand, $_DB_propspec
                                                      WHERE $_DBC_cand_candidat_id='$candidat_id'
                                                      AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                                                      AND $_DBC_cand_statut='$__PREC_RECEVABLE'
                                                      AND $_DBC_cand_groupe_spec='$candidature_array[groupe_spec]'
                                                      AND $_DBC_cand_periode='$__PERIODE'
                                                      AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                                    ORDER BY $_DBC_cand_ordre_spec");

                     $rows_multiples=db_num_rows($res_multiples);

                     $sub_array_cand=array();

                     for($m=0; $m<$rows_multiples; $m++)
                        list($sub_array_cand[$m])=db_fetch_row($res_multiples, $m);

                     db_free_result($res_multiples);
                  }
                  else
                     $sub_array_cand=array();   // Tableau vide : aucun traitement nécessaire
               }
               else   // tableau à un seul élément === cas particulier d'une candidature multiples
                  $sub_array_cand=array("0" => "$cand_id");

               if(count($sub_array_cand)) // Au moins une page à produire : le début est commun
               {
                  $formulaire->AddPage();

                  // Incrémentation du compteur pour savoir si tout s'est bien passé, à la fin
                  $nb_pages++;

                  // Création d'une nouvelle page
                  $formulaire->SetFont('arial','',10);
                  $formulaire->SetTextColor(0, 0, 0);

                  // Premier élément : position fixe (à affiner manuellement, sans doute)
                  // $formulaire->SetXY(60, 78);

                  $formulaire->SetXY(11, 11);
                  $formulaire->SetFont('arial',"IB",14);
                  
                  $formulaire->MultiCell(0, 5, "Commission Pédagogique ($__PERIODE-".($__PERIODE+1).")", 0, "C");

                  $formulaire->Ln(3);

                  $Y=$formulaire->GetY();

                  $formulaire->Line(11, $Y, 199, $Y);
                  $formulaire->Ln(3);
            
                  $formulaire->SetX(11);
                  $formulaire->SetFont('arial',"",9);

                  if(isset($_SESSION["niveau"]) && $_SESSION["niveau"]==$__LVL_CONSULT)
                     $candidat_adresse="$civ_texte " .  $candidat_array["nom"] . " " . $candidat_array["prenom"] . ", $ne le " . $candidat_array["naissance"] . " à " . $candidat_array["lieu_naissance"] .
                                       "   Nationalité : " . $candidat_array["nationalite"];
                  else
                     $candidat_adresse="$civ_texte " .  $candidat_array["nom"] . " " . $candidat_array["prenom"] . ", $ne le " . $candidat_array["naissance"] . " à " . $candidat_array["lieu_naissance"] .
                                       "   Nationalité : " . $candidat_array["nationalite"] .
                                       "\n" . $candidat_array["adresse"];

                  $formulaire->MultiCell(0,4,$candidat_adresse, 0, "L");

                  $formulaire->Ln(3);

                  $formulaire->SetFont('arial',"B",12);

                  // A partir d'ici, le document est traité différemment en fonction de la nature de la candidature
                  // Choix unique = document complet
                  // Multiple : document résumant les formations groupées

                  if(count($sub_array_cand) > 1)
                  {
                     // CANDIDATURE A CHOIX MULTIPLES

                     $nom_formation=ucwords(strtolower("$candidature_array[annee] - $candidature_array[mention_nom]"));
                     $formulaire->MultiCell(0, 5, "Candidature à choix multiples : $nom_formation", 1, "C");

                     $formulaire->Ln(2);

                     $formulaire->SetFont('arial',"B",10);

                     // Ajout des motifs UNIQUEMENT s'ils sont courts
                     if(isset($_SESSION["gestion_motifs"]) && $_SESSION["gestion_motifs"]==0)
                     {
                        $formulaire->MultiCell(0, 4, "Pour chaque voeu et en cas de refus, les motifs de cette liste peuvent être utilisés : ", 0, "L");

                        $formulaire->SetFont('arial',"",9);

                        $result=db_query($dbr,"SELECT $_DBC_motifs_refus_motif FROM $_DB_motifs_refus
                                                WHERE $_DBC_motifs_refus_comp_id=$_SESSION[comp_id]
                                                ORDER BY $_DBC_motifs_refus_motif");
                        $rows=db_num_rows($result);

                        $cnt_table=0;

                        $prev_width=0;
                        $prev_pos=1;

                        for($c=0; $c<$rows; $c++)
                        {
                           list($motif)=db_fetch_row($result,$c);
                           $value=htmlspecialchars($motif, ENT_QUOTES, $default_htmlspecialchars_encoding);

                           // Colonne gauche ou droite ?
                           $pos=$prev_pos ? 0 : 1;

                           // positions X, en mm : 11 77 143 (3 colonnes)

                           $value_width=$formulaire->GetStringWidth($value);

                           if(($prev_width>89 || $value_width > 89) && $pos==1)
                              $pos=0;

                           switch($pos)
                           { 
                              case 0 :   // nouvelle ligne : position = 11
                                       if($c)
                                          $formulaire->Ln(3);

                                       $X=11;
                                       break;

                              case 1 : // colonne droite : position = 100
                                       $X=100;
                                       break;
                           }

                           $Y=$formulaire->getY();
                           $formulaire->setX($X);

                           $formulaire->Cell(62, 4, "$motif", 0, 0, "L");

                           $prev_width=$value_width;
                           $prev_pos=$pos;
                        }
                        db_free_result($result);
                     }

                     // Sélection des décisions
                     $result2=db_query($dbr,"SELECT $_DBC_decisions_id, $_DBC_decisions_texte FROM $_DB_decisions
                                                WHERE $_DBC_decisions_id IN (SELECT distinct($_DBC_decisions_comp_dec_id) FROM $_DB_decisions_comp
                                                                              WHERE $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]')
                                             ORDER BY $_DBC_decisions_texte");

                     $rows2=db_num_rows($result2);

                     $compteur_voeux=0;

                     $formulaire->Ln(8);

                     // Boucle sur les voeux
                     foreach($sub_array_cand as $sub_cand_id)
                     {
                        $compteur_voeux++;

                        // Récupération des informations de ce voeux
                        $sub_candidature_array=__get_candidature($dbr,$sub_cand_id);

                        $Y=$formulaire->getY();
                        $formulaire->Line(11, $Y, 199, $Y);

                        $formulaire->SetFont('arial',"B",10);

                        $nom_formation=$sub_candidature_array["annee"]=="" ? $sub_candidature_array["spec_nom_court"] : "$sub_candidature_array[annee] $sub_candidature_array[spec_nom_court]";
                        $nom_formation.=$sub_candidature_array["finalite"]=="" ? "" : $tab_finalite[$sub_candidature_array["finalite"]];

                        $formulaire->MultiCell(0, 5, "Choix $compteur_voeux : $nom_formation", 0, "L");

                        $formulaire->SetFont('arial',"",9);

                        $position_cnt=0;

                        for($j=0; $j<$rows2; $j++)
                        {
                           list($decision_id,$decision_txt)=db_fetch_row($result2,$j);

                           // $value=htmlspecialchars($decision_txt, ENT_QUOTES, $default_htmlspecialchars_encoding);

                           if(!isset($candidature_array["entretiens"]) || $candidature_array["entretiens"]!=1 ||
                              (isset($candidature_array["entretiens"]) && $candidature_array["entretiens"]==1 && in_array($decision_id, $__DOSSIER_DECISIONS_AVANT_ENTRETIEN)))
                           {
                              $pos=$position_cnt%3;

                              // positions X, en mm : 11 77 143 (3 colonnes)

                              switch($pos)
                              { 
                                 case 0 :   // nouvelle ligne : position = 11
                                          if($position_cnt!=0) // Saut sauf pour la première ligne
                                             $formulaire->Ln(3);
                                          $X=11;
                                          break;

                                 case 1 : // colonne du milieu : position = 77
                                          $X=74;
                                          break;

                                 default : // =2, fin de ligne : position = 143
                                          $X=137;
                                          break;
                              }

                              $Y=$formulaire->getY();

                              $formulaire->image("case3.jpg", $X, $Y, 3);

                              // Décalage par rapport à l'icone
                              $X+=4;
                              $formulaire->setX($X);

                              $formulaire->Cell(62, 3, "$decision_txt", 0, 0, "L");

                              $position_cnt++;
                           }
                        }

                        $formulaire->Ln(5);

                        $formulaire->SetFont('arial',"IB",10);
                        $formulaire->MultiCell(0, 5, "Motif(s) parmi la liste et/ou motif(s) libre(s) :", 0, "L");

                        $formulaire->Ln(12);

                        $formulaire->SetFont('arial',"",9);
                        $formulaire->MultiCell(0, 4, "Signature du Responsable de formation : ", 0, "L");

                        $formulaire->Ln(5);
                     }

                     $Y=$formulaire->getY();
                     $formulaire->Line(11, $Y, 199, $Y);

                     db_free_result($result2);
                  }
                  else
                  {
                     // CANDIDATURE SIMPLE
                     // $formulaire->Ln(5);

                     $nom_formation=ucwords(strtolower($candidature_array["texte_formation"]));
                     $formulaire->MultiCell(0, 5, "Candidature : $nom_formation", 0, "C");

                     $formulaire->Ln(5);

                     $formulaire->SetFont('arial',"IB",10);

                     $formulaire->MultiCell(0, 5, "Décision de la commission pédagogique :", 0, "L");

                     $formulaire->SetFont('arial',"",9);

                     // Sélection des décisions
                     $result2=db_query($dbr,"SELECT $_DBC_decisions_id, $_DBC_decisions_texte FROM $_DB_decisions
                                                WHERE $_DBC_decisions_id IN (SELECT distinct($_DBC_decisions_comp_dec_id) FROM $_DB_decisions_comp
                                                                              WHERE $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]')
                                             ORDER BY $_DBC_decisions_texte");
                     $rows2=db_num_rows($result2);

                     $position_cnt=0;

                     for($j=0; $j<$rows2; $j++)
                     {
                        list($decision_id,$decision_txt)=db_fetch_row($result2,$j);

                        // $value=htmlspecialchars($decision_txt, ENT_QUOTES, $default_htmlspecialchars_encoding);

                        if(!isset($candidature_array["entretiens"]) || $candidature_array["entretiens"]!=1 ||
                              (isset($candidature_array["entretiens"]) && $candidature_array["entretiens"]==1 && in_array($decision_id, $__DOSSIER_DECISIONS_AVANT_ENTRETIEN)))
                        {
                           $pos=$position_cnt%3;

                           // positions X, en mm : 11 77 143 (3 colonnes)

                           switch($pos)
                           {
                              case 0 :   // nouvelle ligne : position = 11
                                       if($position_cnt!=0) // Saut sauf pour la première ligne
                                          $formulaire->Ln(5);
                                       $X=11;
                                       break;

                              case 1 : // colonne du milieu : position = 77
                                       $X=74;
                                       break;

                              default : // =2, fin de ligne : position = 143
                                       $X=137;
                                       break;
                           }

                           $Y=$formulaire->getY();

                           $formulaire->image("case3.jpg", $X, $Y, 3);

                           // Décalage par rapport à l'icone
                           $X+=4;
                           $formulaire->setX($X);

                           $formulaire->Cell(62, 3, "$decision_txt", 0, 0, "L");

                           $position_cnt++;
                        }
                     }

                     db_free_result($result2);

                     $formulaire->Ln(7);

                     $formulaire->SetFont('arial',"IB",10);
                     $formulaire->MultiCell(0, 5, "Motifs (tout refus DOIT être motivé) :", 0, "L");

                     $formulaire->SetFont('arial',"",9);

                     // Ajout des motifs UNIQUEMENT s'ils sont courts
                     if(isset($_SESSION["gestion_motifs"]) && $_SESSION["gestion_motifs"]==0)
                     {
                        $result=db_query($dbr,"SELECT $_DBC_motifs_refus_motif FROM $_DB_motifs_refus
                                                WHERE $_DBC_motifs_refus_comp_id='$_SESSION[comp_id]'
                                                ORDER BY $_DBC_motifs_refus_motif");
                        $rows=db_num_rows($result);

                        $cnt_table=0;

                        $prev_width=0;
                        $prev_pos=1;

                        for($c=0; $c<$rows; $c++)
                        {
                           list($motif)=db_fetch_row($result,$c);
                           $value=htmlspecialchars($motif, ENT_QUOTES, $default_htmlspecialchars_encoding);

                           // Colonne gauche ou droite ?
                           $pos=$prev_pos ? 0 : 1;

                           // positions X, en mm : 11 77 143 (3 colonnes)

                           $value_width=$formulaire->GetStringWidth($value);

                           if(($prev_width>89 || $value_width > 89) && $pos==1)
                              $pos=0;

                           switch($pos)
                           { 
                              case 0 :   // nouvelle ligne : position = 11
                                       if($c!=0) // Saut sauf pour la première ligne
                                          $formulaire->Ln(5);
                                       $X=11;
                                       break;

                              case 1 : // colonne droite : position = 100
                                       $X=100;
                                       break;
                           }

                           $Y=$formulaire->getY();
                           $formulaire->image("case3.jpg", $X, $Y, 3);

                           // Décalage par rapport à l'icone
                           $X+=4;
                           $formulaire->setX($X);

                           // $formulaire->MultiCell(62, 5, "$decision_txt", 0, "L");

                           $formulaire->Cell(62, 3, "$motif", 0, 0, "L");

                           $prev_width=$value_width;
                           $prev_pos=$pos;
                        }
                        db_free_result($result);
                     }
                     else
                        $formulaire->Ln(40);

                     $formulaire->Ln(7);
                     $formulaire->SetFont('arial',"IB",10);

                     if(isset($_SESSION["gestion_motifs"]) && $_SESSION["gestion_motifs"]==0)
                     {
                        $formulaire->MultiCell(0, 5, "Motifs libres :", 0, "L");
                        $formulaire->Ln(12);
                     }
                  }

                  // Si la formation n'a pas d'entretiens complémentaires, on met ici la précision "admission avec ou sans validation d'UE"
                  if(!isset($candidature_array["entretiens"]) || $candidature_array["entretiens"]!=1)
                  {
/*                  
                     $formulaire->SetFont('arial',"IB",10);
                     
                     $formulaire->MultiCell(0, 5, "Précision en cas d'admission : ", 0, "L");
                     
                     $formulaire->SetFont('arial',"",9);

                     $Y=$formulaire->getY();
                     $formulaire->image("case3.jpg", 11, $Y, 3);

                     // Léger décalage du texte par rapport à l'icone
                     $formulaire->setX(15);
                     $formulaire->Cell(62, 3, "Sans validation d'UE", 0, 0, "L");

                     // Même chose pour la ligne suivante
                     $formulaire->Ln(5);
                     $Y=$formulaire->getY();
                     $formulaire->image("case3.jpg", 11, $Y, 3);
                     $formulaire->setX(15);
                     $formulaire->Cell(62, 3, "Avec validation d'UE. Précisez :", 0, 0, "L");
                     $formulaire->Ln(10);
*/
                     $formulaire->SetFont('arial',"IB",10);

                     $str="En cas d'admission : ";
                     $formulaire->Cell($formulaire->GetStringWidth($str), 4, $str, 0, 0, "L");

                     $formulaire->SetFont('arial',"",9);

                     $formulaire->image("case3.jpg", $formulaire->getX()+2, $formulaire->getY(), 3);

                     $formulaire->setX($formulaire->getX()+5);
                     $str="Sans validation d'UE";
                     $formulaire->Cell($formulaire->GetStringWidth($str), 4, $str, 0, 0, "L");

                     $formulaire->image("case3.jpg", $formulaire->getX()+8, $formulaire->getY(), 3);
                     
                     $formulaire->setX($formulaire->getX()+11);                     
                     $str="Avec validation d'UE";
                     $formulaire->Cell($formulaire->GetStringWidth($str), 4, $str, 0, 1, "L");
                     
                     $formulaire->Cell(0, 4, "Précisez les UE en cas de validation :", 0, 1, "L");
                     $formulaire->Ln(15);
                  }
                  
                  $formulaire->Ln(10);
                  $formulaire->SetFont('arial',"IB",10);

                  $formulaire->Cell(0, 5, "Date et signature :", 0, 1, "L");
                  $formulaire->Cell(94, 5, "Président de la Commission Pédagogique", 0, 0, "L");
                  $formulaire->Cell(0, 5, "Responsable de formation (ou rapporteur)", 0, 1, "L");


                  // Seconde partie du formulaire pour les formations nécessitant un entretien complémentaire
                  // TODO : gérer le cas des candidatures à choix multiples avec entretiens
                  if(isset($candidature_array["entretiens"]) && $candidature_array["entretiens"]==1)
                  {
                     // Espace pour les signatures
                     $formulaire->Ln(20);
                     $formulaire->Line(11, $formulaire->GetY(), 199, $formulaire->GetY());
                     $formulaire->Ln(2);

                     $formulaire->MultiCell(0, 5, "Décision de la commission pédagogique après entretien :", 0, "L");

                     $formulaire->SetFont('arial',"",9);

                     // Sélection des décisions
                     $result2=db_query($dbr,"SELECT $_DBC_decisions_id, $_DBC_decisions_texte FROM $_DB_decisions
                                                WHERE $_DBC_decisions_id IN (SELECT distinct($_DBC_decisions_comp_dec_id) FROM $_DB_decisions_comp
                                                                              WHERE $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]')
                                             ORDER BY $_DBC_decisions_texte");
                     $rows2=db_num_rows($result2);

                     $position_cnt=0;

                     for($j=0; $j<$rows2; $j++)
                     {
                        list($decision_id,$decision_txt)=db_fetch_row($result2,$j);

                        // $value=htmlspecialchars($decision_txt, ENT_QUOTES, $default_htmlspecialchars_encoding);

                        if(!isset($candidature_array["entretiens"]) || $candidature_array["entretiens"]!=1 ||
                           (isset($candidature_array["entretiens"]) && $candidature_array["entretiens"]==1 && in_array($decision_id, $__DOSSIER_DECISIONS_APRES_ENTRETIEN)))
                        {
                           $pos=$position_cnt%3;

                           // positions X, en mm : 11 77 143 (3 colonnes)

                           switch($pos)
                           {
                              case 0 :   // nouvelle ligne : position = 11
                                       if($position_cnt!=0) // Saut sauf pour la première ligne
                                          $formulaire->Ln(5);
                                       $X=11;
                                       break;

                              case 1 : // colonne du milieu : position = 77
                                       $X=74;
                                       break;

                              default : // =2, fin de ligne : position = 143
                                       $X=137;
                                       break;
                           }

                           $Y=$formulaire->getY();

                           $formulaire->image("case3.jpg", $X, $Y, 3);

                           // Décalage par rapport à l'icone
                           $X+=4;
                           $formulaire->setX($X);

                           $formulaire->Cell(62, 3, "$decision_txt", 0, 0, "L");

                           $position_cnt++;
                        }
                     }

                     $formulaire->Ln(5);

                     $formulaire->SetFont('arial',"IB",10);
                     $formulaire->MultiCell(0, 5, "Motif(s) :", 0, "L");

                     db_free_result($result2);

                     $formulaire->Ln(16);

                     // Si la formation a un entretien complémentaire, on met ici la précision "admission avec ou sans validation d'UE"
/*                     
                     $formulaire->SetFont('arial',"IB",10);
                     $formulaire->MultiCell(0, 5, "Précision en cas d'admission : ", 0, "L");
                     
                     $formulaire->SetFont('arial',"",9);

                     $Y=$formulaire->getY();
                     $formulaire->image("case3.jpg", 11, $Y, 3);

                     // Léger décalage du texte par rapport à l'icone
                     $formulaire->setX(15);
                     $formulaire->Cell(62, 3, "Sans validation d'UE", 0, 0, "L");

                     // Même chose pour la ligne suivante
                     $formulaire->Ln(5);
                     $Y=$formulaire->getY();
                     $formulaire->image("case3.jpg", 11, $Y, 3);
                     $formulaire->setX(15);
                     $formulaire->Cell(62, 3, "Avec validation d'UE. Précisez :", 0, 0, "L");                               
                     $formulaire->Ln(20);
*/                     
                     $formulaire->SetFont('arial',"IB",10);

                     $str="En cas d'admission : ";
                     $formulaire->Cell($formulaire->GetStringWidth($str), 4, $str, 0, 0, "L");

                     $formulaire->SetFont('arial',"",9);

                     $formulaire->image("case3.jpg", $formulaire->getX()+2, $formulaire->getY(), 3);

                     $formulaire->setX($formulaire->getX()+5);
                     $str="Sans validation d'UE";
                     $formulaire->Cell($formulaire->GetStringWidth($str), 4, $str, 0, 0, "L");

                     $formulaire->image("case3.jpg", $formulaire->getX()+8, $formulaire->getY(), 3);
                     
                     $formulaire->setX($formulaire->getX()+11);                     
                     $str="Avec validation d'UE";
                     $formulaire->Cell($formulaire->GetStringWidth($str), 4, $str, 0, 1, "L");
                     
                     $formulaire->Cell(0, 4, "Précisez les UE en cas de validation :", 0, 1, "L");
                     $formulaire->Ln(15);

                     $formulaire->SetFont('arial',"IB",10);

//                     $formulaire->MultiCell(0, 5, "Date et signature du Président de la Commission Pédagogique et/ou du Responsable de formation : ", 0, "L");
                     $formulaire->Cell(0, 5, "Date et signature :", 0, 1, "L");
                     $formulaire->Cell(94, 5, "Président de la Commission Pédagogique", 0, 0, "L");
                     $formulaire->Cell(0, 5, "Responsable de formation (ou rapporteur)", 0, 1, "L");
                  }


                  // TODO
                  // Cas particuliers : ajouts gérés par des include
                  // ==>> Créer un editeur de Formulaire pour virer ça
                  if(is_file("$__ROOT_DIR/$__GESTION_DIR/lettres/include/include_$_SESSION[comp_id].php"))
                  {
                     $formulaire->Ln(15);   
                     include "$__ROOT_DIR/$__GESTION_DIR/lettres/include/include_$_SESSION[comp_id].php";
                  }

                  write_evt($dbr,$__EVT_ID_G_PREC, "Formulaire commission : $candidature_array[nom_complet]", $candidat_id, $cand_id);
               }
            } // Fin de la génération des pages
         } // Fin du if(isset(array_cand)
      } // Fin du if(candidat_array!=FALSE)
   }  // Fin du foreach(candidat)

   if(isset($nb_pages) && $nb_pages>0)
   {
      if(count($ensemble_candidats)==1) // élement unique
      {
         // Nettoyage du nom du candidat
         // TODO : Généraliser et créer une fonction

         $candidat_nom=$new_str=preg_replace("/[ '\"&#\/\\\]/", "_", clean_str(mb_strtolower($candidat_array["nom"])));
         $candidat_prenom=preg_replace("/[ '\"&#\/\\\]/", "_", clean_str(mb_strtolower($candidat_array["prenom"])));

         $nom_fichier=clean_str("Formulaire_commission_" . $candidat_nom . "_" . $candidat_prenom . "_$cand_id.pdf");
      }
      else
      {
         $date_fr=date_fr("j_F_Y_H_i", time());
         $nom_fichier=clean_str("Formulaires_commission_" . $_SESSION["auth_user"] . "_$date_fr.pdf");
      }

      $formulaire->Output("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$nom_fichier");

      // Attention : chemin relatif à www-root (document_root du serveur Apache)
      echo "<HTML><SCRIPT>document.location='$__GESTION_COMP_STOCKAGE_DIR/$_SESSION[comp_id]/$nom_fichier';</SCRIPT></HTML>";
   }


   // Génération du fichier et copie dans le répertoire
   $nom_fichier="Formulaire_commission_$_SESSION[auth_id]-" . time() . ".pdf";
   // $formulaire->Output("$nom_fichier", "I");

   $formulaire->Output("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/$nom_fichier");

   // Attention : chemin relatif à www-root (document_root du serveur Apache)
   echo "<HTML><SCRIPT>document.location='$__GESTION_COMP_STOCKAGE_DIR/$_SESSION[comp_id]/$nom_fichier';</SCRIPT></HTML>";

   db_close($dbr);
}

?>
