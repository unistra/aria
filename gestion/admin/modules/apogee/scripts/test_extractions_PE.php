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
   // Script d'extraction des admissions pour injections OPI
   // il doit être lancé via le shell

   session_name("preinsc_gestion");
   session_start();

   if(FALSE===chdir(dirname(__FILE__)))
      die("Impossible de changer le répertoire courant (". dirname(__FILE__) . ")");

   if(is_file("../../../../../configuration/aria_config.php")) include "../../../../../configuration/aria_config.php";
   else die("Fichier \"configuration/aria_config.php\" non trouvé");

   if(is_file("../../../../../include/vars.php")) include "../../../../../include/vars.php";
   else die("Fichier \"include/vars.php\" non trouvé");

   if(is_file("../../../../../include/fonctions.php")) include "../../../../../include/fonctions.php";
   else die("Fichier \"include/fonctions.php\" non trouvé");

   if(is_file("../../../../../include/db.php")) include "../../../../../include/db.php";
   else die("Fichier \"include/db.php\" non trouvé");

   if(is_file("../../../../../include/access_functions.php")) include "../../../../../include/access_functions.php";
   else die("Fichier \"include/access_functions.php\" non trouvé");

   if(is_file("../../../../../gestion/admin/editeur/include/editeur_fonctions.php")) include "../../../../../gestion/admin/editeur/include/editeur_fonctions.php";
   else die("Fichier \"gestion/admin/editeur/include/editeur_fonctions.php\" non trouvé");

   if(isset($argv[1]) && (!strcasecmp($argv[1], "test") || !strcasecmp($argv[1], "-test") || !strcasecmp($argv[1], "--test") || !strcasecmp($argv[1], "-t") || !strcasecmp($argv[1], "--t")))
   {
      print("Mode Test. Aucun enregistrement dans la base de données et aucun message envoyé (sauf au compte \"administrateur\")\n");
      $TESTMODE=1;
      $TESTCNT=0;
      $TEST_CAND=$TEST_VOEUX="";
   }
   else
      $TESTMODE=$TESTCNT=0;

   $dbr=db_connect();

   // Chargement de la configuration
   $load_config=__get_config($dbr);

   if($load_config==FALSE) // config absente : erreur
      $erreur_config=1;
   elseif($load_config==-1) // paramêtre(s) manquant(s) : avertissement
      $warn_config=1;

   // partie du schéma de la base spécifique au module apogee
   include "../include/db.php";

   $php_self=$_SERVER['PHP_SELF'];
   $_SESSION['CURRENT_FILE']=$php_self;

   // Chargement des modules
   if(function_exists("add_modules"))
      add_modules();

   // $__PERIODE="2009"; <== déterminée par $__INCLUDE_DIR_ABS/vars.php

   // Construction de la liste des pays et nationalités (codes ISO) pour son utilisation dans le formulaire
   $_SESSION["liste_pays_nat_iso"]=array();
   
   $res_pays_nat=db_query($dbr, "SELECT $_DBC_pays_nat_ii_iso, $_DBC_pays_nat_ii_insee, $_DBC_pays_nat_ii_pays, $_DBC_pays_nat_ii_nat
                                 FROM $_DB_pays_nat_ii
                                 ORDER BY to_ascii($_DBC_pays_nat_ii_pays)");
                                 
   $rows_pays_nat=db_num_rows($res_pays_nat);
   
   for($p=0; $p<$rows_pays_nat; $p++)
   {
      list($code_iso, $code_insee, $table_pays, $table_nationalite)=db_fetch_row($res_pays_nat, $p);
      
      // Construction uniquement si le code insee est présent (pour les exports APOGEE ou autres)
      if($code_insee!="")
      {
         $_SESSION["liste_pays_nat_iso"]["$code_iso"]=array("insee" => "$code_insee", "pays" => "$table_pays", "nationalite" => $table_nationalite);
         $_SESSION["liste_pays_nat_insee"]["$code_insee"]=array("pays" => "$table_pays", "nationalite" => $table_nationalite);
      }
   }

   db_free_result($res_pays_nat);

   // -----------

   // Requête principale : sélection des candidats admis
   // Attention : les décisions doivent avoir été "publiées" pour pouvoir être extraites

   // en mode test, on inclut aussi les candidats déjà extraits
   if($TESTMODE==1)
      $condition_normale="";
   else
      $condition_normale="AND ($_DBC_cand_id NOT IN (SELECT $_module_apogee_DBC_numeros_opi_cand_id FROM $_module_apogee_DB_numeros_opi) 
                               OR $_DBC_cand_id IN (SELECT $_module_apogee_DBC_numeros_opi_cand_id FROM $_module_apogee_DB_numeros_opi WHERE $_module_apogee_DBC_numeros_opi_ligne_voeu=''))";

   $result=db_query($dbr, "SELECT $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_prenom2, $_DBC_candidat_date_naissance,
                                 $_DBC_candidat_telephone, $_DBC_candidat_numero_ine, $_DBC_candidat_nationalite,
                                 $_DBC_candidat_email, $_DBC_candidat_lieu_naissance, $_DBC_candidat_dpt_naissance,
                                 $_DBC_candidat_pays_naissance,
                                 $_DBC_candidat_adresse,$_DBC_candidat_adresse_cp, $_DBC_candidat_adresse_ville,
                                 $_DBC_candidat_adresse_pays, $_module_apogee_DBC_formations_cet,
                                 $_module_apogee_DBC_formations_vet, $_module_apogee_DBC_centres_gestion_code,
                                 $_DBC_cand_ordre, $_DBC_candidat_civilite, $_DBC_candidat_id, $_DBC_candidat_annee_bac,
                                 $_DBC_candidat_serie_bac, $_DBC_candidat_deja_inscrit, $_DBC_candidat_annee_premiere_inscr,
                                 $_module_apogee_DBC_config_code, $_module_apogee_DBC_config_prefixe_opi,
                                 $_DBC_cand_id, $_DBC_annees_annee, $_DBC_specs_nom, $_DBC_propspec_finalite,
                                 $_module_apogee_DBC_config_message_primo, $_module_apogee_DBC_config_message_reserve, 
                                 $_DBC_cand_decision
                              FROM $_DB_candidat, $_module_apogee_DB_formations, $_module_apogee_DB_centres_gestion, $_DB_cand,
                                   $_DB_propspec, $_DB_composantes, $_module_apogee_DB_config, $_DB_annees, $_DB_specs,
                                   $_module_apogee_DB_activ
                           WHERE $_DBC_propspec_comp_id=$_DBC_composantes_id
                           AND $_DBC_propspec_id=$_DBC_cand_propspec_id
                           AND $_DBC_composantes_id=$_module_apogee_DBC_config_comp_id
                           AND $_DBC_candidat_id=$_DBC_cand_candidat_id
                           AND $_module_apogee_DBC_formations_propspec_id=$_DBC_cand_propspec_id
                           AND $_module_apogee_DBC_centres_gestion_id=$_module_apogee_DBC_formations_centre_gestion
                           AND $_DBC_propspec_annee=$_DBC_annees_id
                           AND $_DBC_propspec_id_spec=$_DBC_specs_id
                           AND $_module_apogee_DBC_activ_comp_id=$_DBC_composantes_id
                           AND $_DBC_cand_periode='$__PERIODE'
                           AND $_DBC_candidat_deja_inscrit='0'
                           AND $_module_apogee_DBC_activ_pe='t'
                           AND ($_DBC_propspec_affichage_decisions IN ('1','2') OR $_DBC_composantes_affichage_decisions IN ('1','2'))
                           AND $_DBC_cand_decision IN ('$__DOSSIER_ADMIS', '$__DOSSIER_ADMIS_ENTRETIEN', '$__DOSSIER_ADMISSION_CONFIRMEE', 
				                                           '$__DOSSIER_ADMIS_LISTE_COMP', '$__DOSSIER_ADMIS_RECOURS', '$__DOSSIER_SOUS_RESERVE')
                           $condition_normale
                           ORDER BY $_DBC_candidat_id, $_DBC_cand_date_prise_decision");

   $nb_result=db_num_rows($result);

   if($nb_result)
   {
      for($i=0; $i<$nb_result; $i++)
      {
         list($cand_nom, $cand_prenom, $c_prenom2, $c_date_naissance, $c_tel, $c_ine, $c_nat_iso, $cand_email, $c_lieu_naissance,
              $c_dpt_naissance, $c_pays_naissance_iso, $c_adresse, $c_adr_code_postal, $c_adr_ville,
              $c_adr_pays_iso, $f_cet, $f_vet, $f_centre_gestion, $f_ordre, $cand_civ, $c_id,
              $c_annee_bac, $c_serie_bac, $c_deja_inscrit, $c_premiere_inscr, $lettre_code_univ, $PREFIXE_OPI, $cand_id,
              $annee_nom, $spec_nom, $finalite, $message_opi, $message_reserve, $cand_decision)=db_fetch_row($result, $i);

         // Si la décision n'est pas "sous reserve", on regarde si la ligne n'était pas déjà  présente dans la table des numéros OPI
         // Si elle l'est et que la ligne "voeu" est vide : on complète seulement cette dernière
         if($cand_decision!=$__DOSSIER_SOUS_RESERVE 
            && !db_num_rows(db_query($dbr, "SELECT * FROM $_module_apogee_DB_numeros_opi 
                                            WHERE $_module_apogee_DBC_numeros_opi_cand_id='$cand_id' 
                                            AND $_module_apogee_DBC_numeros_opi_ligne_voeu=''")))
                                           
                                            

         // calcul de la longueur du préfixe pour pouvoir le supprimer dans la requête suivante
         // cette étape est dans la boucle FOR car le préfixe peut être différent en cas d'universités multiples
         // TODO : à optimiser (stockage dans un tableau ?) pour éviter les calculs inutiles
         $prefixe_len=strlen($PREFIXE_OPI)+1;

         // Initialisation du compteur OPI (numéro de référence, les suivants sont calculés en incrémentant le max)
         // Le calcul se fait en masquant le préfixe OPI configuré
         $res_num_opi=db_query($dbr, "SELECT max(CAST(substring($_module_apogee_DBU_numeros_opi_num from $prefixe_len) AS BIGINT))+1 FROM $_module_apogee_DB_numeros_opi");

         if(db_num_rows($res_num_opi))
         {
            list($max_opi_nb)=db_fetch_row($res_num_opi, 0);

            if($max_opi_nb=="")
               $max_opi_nb=1;
         }
         else
            $max_opi_nb=1;

         db_free_result($res_num_opi);

         // Extraction uniquement si les informations minimales sont complètes
         if($c_serie_bac!="" && $c_annee_bac!="" && $f_cet!="" && $f_vet!="" && $f_centre_gestion!="")
         {
            $c_nat_insee=$_SESSION["liste_pays_nat_iso"]["$c_nat_iso"]["insee"];
            $c_pays_naissance_insee=$_SESSION["liste_pays_nat_iso"]["$c_pays_naissance_iso"]["insee"];
            $c_adr_pays_insee=$_SESSION["liste_pays_nat_iso"]["$c_adr_pays_iso"]["insee"];

            // Numéro OPI
            // La longueur du numéro OPI est de 10 caractères (préfixe inclus), on complète avec des zéros : PREFIXE /0..0/ numéro.
            $opi="$PREFIXE_OPI".str_repeat("0", (10-strlen($PREFIXE_OPI)-strlen($max_opi_nb)))."$max_opi_nb";

            $max_opi_nb++; // on incrémente la référence pour la génération du numéro suivant

            // Traitement des champs
            $naissance_txt=date("dmY", $c_date_naissance);
            $c_civ=$cand_civ!="M" ? "F" : "M";

            if(!check_ine_bea($c_ine))
            {
               $ine_bea=strtoupper(substr($c_ine, 0, 10));
               $controle=strtoupper(substr($c_ine, 10, 1));
            }
            else
            {
               $ine_bea=str_repeat(" ", 10);
               $controle=" ";
            }

            if(strlen($cand_nom)>30)
               $c_nom=substr($cand_nom, 0, 30);
            elseif(strlen($cand_nom)<30)
               $c_nom=$cand_nom.str_repeat(" ", (30-strlen($cand_nom)));

            if(strlen($cand_prenom)>20)
               $c_prenom=substr($cand_prenom, 0, 20);
            elseif(strlen($cand_prenom)<20)
               $c_prenom=$cand_prenom.str_repeat(" ", (20-strlen($cand_prenom)));

            if(strlen($c_prenom2)>20)
               $c_prenom2=substr($c_prenom2, 0, 20);
            elseif(strlen($c_prenom2)<20)
               $c_prenom2.=str_repeat(" ", (20-strlen($c_prenom2)));

            if(strlen($c_tel)>15)
               $c_tel=substr($c_tel, 0, 15);
            elseif(strlen($c_tel)<15)
               $c_tel.=str_repeat(" ", (15-strlen($c_tel)));

            if(strlen($cand_email)>200)
               $c_email=substr($cand_email, 0, 200);
            elseif(strlen($cand_email)<200)
               $c_email=$cand_email.str_repeat(" ", (200-strlen($cand_email)));

            if(strlen($c_dpt_naissance)<3)
               $c_dpt_naissance.=str_repeat(" ", (3-strlen($c_dpt_naissance)));

            // Type et code pour le lieu de naissance (pays ou département)
            if($c_pays_naissance_iso=="FR") // France ==> on met le département
            {
               $c_code_naissance=$c_dpt_naissance;
               $type_code_naissance="D";
            }
            else // Pays
            {
               $c_code_naissance=$c_pays_naissance_insee;
               $type_code_naissance="P";
            }

            if(strlen($c_lieu_naissance)>30)
               $c_lieu_naissance=substr($c_lieu_naissance, 0, 30);
            elseif(strlen($c_lieu_naissance)<30)
               $c_lieu_naissance.=str_repeat(" ", (30-strlen($c_lieu_naissance)));

            $c_adresse=preg_replace("/[ ]+/", " ", str_replace("\r\n", " ", $c_adresse));

            if(strlen($c_adresse)>32)
            {
/*
               $c_adresse_array=str_split($c_adresse, 32);
               $c_adresse=array_key_exists("0", $c_adresse_array) ? $c_adresse_array[0] : $c_adresse;
*/
               $c_adresse=substr($c_adresse, 0, 32);

            }
            elseif(strlen($c_adresse)<32)
               $c_adresse.=str_repeat(" ", (32-strlen($c_adresse)));

            if($c_adr_pays_iso=="FR")
            {
               // Adresse en France : une ligne pour l'adresse et une ligne CP+ville
               $adresse_ligne1=$c_adresse;

               if(strlen("$c_adr_code_postal $c_adr_ville")<=32)
                  $adresse_cp_ville="$c_adr_code_postal $c_adr_ville" . str_repeat(" ", (32-strlen("$c_adr_code_postal $c_adr_ville")));
               else
                  $adresse_cp_ville=substr("$c_adr_code_postal $c_adr_ville", 0, 32);
            }
            else // adresse hors FR : une ligne avec tout
            {
               if(strlen("$c_adresse $c_adr_code_postal $c_adr_ville")<=32)
                  $adresse_ligne1="$c_adresse $c_adr_code_postal $c_adr_ville" . str_repeat(" ", (32-strlen("$c_adresse $c_adr_code_postal $c_adr_ville")));
               else
                  $adresse_ligne1=substr("$c_adresse $c_adr_code_postal $c_adr_ville", 0, 32);

               $adresse_cp_ville=str_repeat(" ", 32);
            }

            // Si l'adresse dépasse : utiliser adr_ligne_2 et adr_ligne_3 prévues dans les batchs OPI ?

            // Année et série du bac : si vides : ne pas inclure dans l'extraction ?
            if(($c_annee_bac!="" && strlen($c_annee_bac)!=4) || $c_annee_bac=="")
               $c_annee_bac=str_repeat(" ", 4);

            if($c_serie_bac=="")
               $c_serie_bac=str_repeat(" ", 4);
            elseif(strlen($c_serie_bac)<4)
               $c_serie_bac=$c_serie_bac.str_repeat(" ", (4-strlen($c_serie_bac)));


            // Pas obligatoire : à confirmer
            // Il faudra peut être ajouter l'année de première inscription dans l'enseignement supérieur
            if(!$c_deja_inscrit || ($c_premiere_inscr!="" && strlen($c_premiere_inscr)!=4))
               $c_premiere_inscr=str_repeat(" ", 4);

            $ligne_candidat=str_replace("'","''", preg_replace("/\\\/","", "$opi:$c_nat_insee:$ine_bea:$controle:$naissance_txt:N:$c_annee_bac:$c_serie_bac:$c_nom:$c_prenom:$c_prenom2:$c_lieu_naissance:$c_code_naissance:$type_code_naissance:$c_adr_pays_insee:$adresse_ligne1:$adresse_cp_ville:$c_tel:$c_civ:$c_email:"));

            // </ligne candidat>

            // <ligne du voeu correspondant>

            if(strlen($f_ordre)!=2)
               $f_ordre="0"."$f_ordre";

            if(strlen($f_cet)<6)
               $f_cet=$f_cet . str_repeat(" ", (6-strlen($f_cet)));

            if(strlen($f_vet)<3)
               $f_vet=$f_vet . str_repeat(" ", (3-strlen($f_vet)));

            if(strlen($f_centre_gestion)<3)
               $f_centre_gestion=$f_centre_gestion . str_repeat(" ", (3-strlen($f_centre_gestion)));

            $ligne_voeu=str_replace("'","''", stripslashes("$opi:$c_nom:$c_prenom:$f_centre_gestion:$f_cet:$f_vet:$f_ordre:"));

            $nom_formation=$annee_nom!="" ? "$annee_nom $spec_nom" : "$spec_nom";
            $nom_formation.=$tab_finalite[$finalite]!="" ? " $tab_finalite[$finalite]" : "";

            // Macros spécifiques à ce message
            // Si le candidat est admis sous réserve, on a un message différent et le voeu n'est pas enregistré (uniquement le candidat)            
            // Si le message type est vide, on ne fait rien
            if($cand_decision=="$__DOSSIER_SOUS_RESERVE")
            {
               $current_message=$message_reserve;
               $ligne_voeu="";
            }
            else
               $current_message=$message_opi;
            
            $corps=preg_replace("/%formation%/i", $nom_formation, $current_message);
            $corps_message=preg_replace("/%opi%/i", $opi, $corps);

            // Code d'autorisation
            $candidature_array=__get_candidature($dbr, $cand_id);
				$candidat_array=__get_infos_candidat($dbr, $c_id);
            $cursus_array=array(); // cursus inutile : tableau vide pour appel à la fonction
				$lang="FR";

				if(is_array($candidature_array) && is_array($candidat_array))        
            {   
				   $corps_message=pdf_traitement_macros($dbr, $corps_message, $candidat_array, $candidature_array, $cursus_array, $lang);
				 
				   // dirty hack : ré-extraction directe du code pour stockage dans la base
				   $code_lp=trim(pdf_traitement_macros($dbr, "%code%", $candidat_array, $candidature_array, $cursus_array, $lang));
            }
            else // TODO : ajouter des alertes par mail en cas d'erreur
            {
               if(!is_array($candidature_array))
                  print("Erreur : impossible de récupérer les informations de la candidature (__get_candidature_array(), #id $cand_id)\n");
                  
               if(!is_array($candidat_array))
                  print("Erreur : impossible de récupérer les informations du candidat (__get_candidat_array(), #id $c_id)\n");
            }

            // Si le message n'est pas vide, on enregistre et on envoie
            if($TESTMODE==0 && trim($corps_message)!="")
            {
               $dest_array=array("0" => array("id"     => "$c_id",
                                              "civ"    => "$cand_civ.",
                                              "nom"    => "$cand_nom",
                                              "prenom" => "$cand_prenom",
                                              "email"  => "$cand_email"));

               write_msg("", array("id" => "0", "nom" => "Système", "prenom" => ""), $dest_array, "Inscription administrative - $nom_formation",
                        $corps_message, "$cand_nom $cand_prenom");

               // Dernière étape : insertion dans la table
               db_query($dbr,"INSERT INTO $_module_apogee_DB_numeros_opi VALUES ('$opi','$cand_id','$ligne_candidat','$ligne_voeu')");
            }
            elseif($TESTMODE==1) // envoi d'un seul message à l'admin
            {
               $TEST_CAND.="$ligne_candidat\n";
               $TEST_VOEUX.="$ligne_voeu\n";

               if(!$TESTCNT)
               {
                  $sent=write_msg_2($dbr, array("id" => "0", "nom" => "Système", "prenom" => "", "src_type" => "gestion", "composante" => "", "universite" => "$GLOBALS[__SIGNATURE_COURRIELS]"),
                                    array("0" => array("id" => 0, "dest_type" => "gestion")), "[Extractions : Test Primo]", $corps_message);
                  $TESTCNT=1;
               }
            }

            // </ligne du voeu correspondant>
         }
      }

      // Extraction totale
      $date=date("jFY", time());

      if($TESTMODE==0)
      {
         $res_extraction=db_query($dbr, "SELECT $_module_apogee_DBC_numeros_opi_ligne_candidat, $_module_apogee_DBC_numeros_opi_ligne_voeux
                                             FROM $_module_apogee_DB_numeros_opi, $_DB_cand
                                          WHERE $_module_apogee_DBC_numeros_opi_cand_id=$_DBC_cand_id
                                          AND $_DBC_cand_periode='$__PERIODE'
                                          ORDER BY $_module_apogee_DBC_numeros_opi_num");

         $rows_extr=db_num_rows($res_extraction);

         if($rows_extr)
         {
            $fichier_candidats=fopen("extractions/candidats_$date.opi","w") or die("Impossible de créer le fichier \"candidats_$date.opi\"");
            $fichier_voeux=fopen("extractions/voeux_$date.opi","w") or die("Impossible de créer le fichier \"voeux_$date.opi\"");

            for($i=0; $i<$rows_extr; $i++)
            {
               list($ligne_cand, $ligne_voeu)=db_fetch_row($res_extraction, $i);

               fwrite($fichier_candidats, "$ligne_cand\n");
               
               if($ligne_voeu!="")
                  fwrite($fichier_voeux, "$ligne_voeu\n");
            }

            fclose($fichier_candidats);
            fclose($fichier_voeux);      
         }
      }
      else
      {
         $fichier_candidats=fopen("extractions/candidats_$date.opi","w") or die("Impossible de créer le fichier \"candidats_$date.opi\"");
         $fichier_voeux=fopen("extractions/voeux_$date.opi","w") or die("Impossible de créer le fichier \"voeux_$date.opi\"");

         fwrite($fichier_candidats, "$TEST_CAND\n");
         fwrite($fichier_voeux, "$TEST_VOEUX\n");

         fclose($fichier_candidats);
         fclose($fichier_voeux);
      }
   }

   db_free_result($result);

   db_close($dbr);
?>
