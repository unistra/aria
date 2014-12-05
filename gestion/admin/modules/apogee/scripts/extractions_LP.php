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
   elseif($load_config==-1) // paramètre(s) manquant(s) : avertissement
      $warn_config=1;

	// partie du schéma de la base spécifique au module apogee
	include "../include/db.php";
   include "../include/vars.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

   // Chargement des modules
   if(function_exists("add_modules"))
      add_modules();

	// Requête principale : sélection des candidats admis
	// Attention : les décisions doivent avoir été publiées pour pouvoir être extraites
	/*
	$result=db_query($dbr, "SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_email, 
                                  $_DBC_candidat_numero_ine, $_module_apogee_DBC_formations_cet, $_module_apogee_DBC_formations_vet, $_DBC_cand_id,
                                  $_module_apogee_DBC_config_message_lp, $_DBC_candidat_nom_naissance
										FROM $_DB_candidat, $_module_apogee_DB_formations, $_DB_cand, $_DB_propspec, $_DB_composantes,
                                   $_module_apogee_DB_activ, $_module_apogee_DB_config
									WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
                           AND $_DBC_composantes_id=$_module_apogee_DBC_config_comp_id
									AND $_DBC_cand_propspec_id=$_DBC_propspec_id
									AND $_DBC_composantes_id=$_DBC_propspec_comp_id
									AND $_module_apogee_DBC_formations_propspec_id=$_DBC_cand_propspec_id
                           AND $_module_apogee_DBC_activ_comp_id=$_DBC_composantes_id
									AND ($_DBC_propspec_affichage_decisions IN ('1','2') OR $_DBC_composantes_affichage_decisions IN ('1','2'))
									AND $_DBC_cand_periode='$__PERIODE'
                           AND $_module_apogee_DBC_activ_lp='t'
									AND $_DBC_candidat_deja_inscrit='1'
									AND $_DBC_candidat_annee_premiere_inscr!=''
									AND $_DBC_cand_decision IN ('$__DOSSIER_ADMIS', '$__DOSSIER_ADMIS_ENTRETIEN', '$__DOSSIER_ADMISSION_CONFIRMEE',
																		 '$__DOSSIER_ADMIS_LISTE_COMP', '$__DOSSIER_ADMIS_RECOURS')
									AND $_DBC_cand_id NOT IN (SELECT $_module_apogee_DBC_codes_LP_cand_id FROM $_module_apogee_DB_codes_LP)");
   */

   if($TESTMODE==1)
      $condition="";
   else
      $condition="AND $_DBC_cand_id NOT IN (SELECT $_module_apogee_DBC_codes_LP_cand_id FROM $_module_apogee_DB_codes_LP)";

   $result=db_query($dbr, "SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_email, 
                                  $_DBC_candidat_numero_ine, $_module_apogee_DBC_formations_cet, $_module_apogee_DBC_formations_vet, $_DBC_cand_id,
                                  CASE WHEN $_DBC_propspec_id IN (SELECT $_module_apogee_DBC_messages_formations_propspec_id FROM $_module_apogee_DB_messages_formations, $_module_apogee_DB_messages
                                                                  WHERE $_module_apogee_DBC_messages_formations_msg_id=$_module_apogee_DBC_messages_msg_id
                                                                  AND $_module_apogee_DBC_messages_type='$_MOD_APOGEE_MSG_REINS')
                                       THEN (SELECT $_module_apogee_DBC_messages_contenu FROM $_module_apogee_DB_messages_formations, $_module_apogee_DB_messages
                                             WHERE $_module_apogee_DBC_messages_formations_msg_id=$_module_apogee_DBC_messages_msg_id
                                             AND $_module_apogee_DBC_messages_formations_propspec_id=$_DBC_propspec_id
                                             AND $_module_apogee_DBC_messages_type='$_MOD_APOGEE_MSG_REINS')
                                       ELSE (SELECT $_module_apogee_DBC_config_message_lp FROM $_module_apogee_DB_config WHERE $_module_apogee_DBC_config_comp_id=$_DBC_composantes_id)
                                       END, $_DBC_candidat_nom_naissance
										FROM $_DB_candidat, $_module_apogee_DB_formations, $_DB_cand, $_DB_propspec, $_DB_composantes,
                                   $_module_apogee_DB_activ, $_module_apogee_DB_config
									WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
                           AND $_DBC_composantes_id=$_module_apogee_DBC_config_comp_id
									AND $_DBC_cand_propspec_id=$_DBC_propspec_id
									AND $_DBC_composantes_id=$_DBC_propspec_comp_id
									AND $_module_apogee_DBC_formations_propspec_id=$_DBC_cand_propspec_id
                           AND $_module_apogee_DBC_activ_comp_id=$_DBC_composantes_id
									AND ($_DBC_propspec_affichage_decisions IN ('1','2') OR $_DBC_composantes_affichage_decisions IN ('1','2'))
									AND $_DBC_cand_periode='$__PERIODE'
                           AND $_module_apogee_DBC_activ_lp='t'
									AND $_DBC_candidat_deja_inscrit='1'
									AND $_DBC_candidat_annee_premiere_inscr!=''
									AND $_DBC_cand_decision IN ('$__DOSSIER_ADMIS', '$__DOSSIER_ADMIS_ENTRETIEN', '$__DOSSIER_ADMISSION_CONFIRMEE',
																		 '$__DOSSIER_ADMIS_LISTE_COMP', '$__DOSSIER_ADMIS_RECOURS')
                           $condition");
   
	$nb_result=db_num_rows($result);

	if($nb_result)
	{
		$date=date("jFY", time());

      if(!is_dir("extractions")) // on peut utiliser un chemin relatif car on a fait le chdir en début de script
         mkdir("extractions", 0770);

		$fichier_LP=fopen("extractions/laisser_passer_$date.opi","w") or die("Impossible de créer le fichier \"extractions/laisser_passer_$date.opi\"");
	
		for($i=0; $i<$nb_result; $i++)
		{
			list($c_id, $cand_civ, $cand_nom, $cand_prenom, $cand_email, $c_ine, $f_cet, $f_vet, $cand_id, $corps_message, $cand_nom_naissance)=db_fetch_row($result, $i);

			// Extraction uniquement si les informations minimales sont complètes
			if($f_cet!="" && $f_vet!="" && !check_ine_bea($c_ine))
			{
				$ine_bea=strtoupper($c_ine);

				$ligne_candidat=str_replace("'","''", stripslashes("$ine_bea:$f_cet:$f_vet:$__PERIODE:"));
				fwrite($fichier_LP, preg_replace("/[']+/","'",$ligne_candidat)."\n");
				
				$candidature_array=__get_candidature($dbr, $cand_id);
				$candidat_array=__get_infos_candidat($dbr, $c_id);
				
				$cursus_array=array(); // cursus inutile : tableau vide pour appel à la fonction
				$lang="FR";

				// réinitialisation du code d'autorisation
				unset($code_lp);

            if(is_array($candidature_array) && is_array($candidat_array))        
            {   
				   $corps_message=pdf_traitement_macros($dbr, $corps_message, $candidat_array, $candidature_array, $cursus_array, $lang);
				 
				   // dirty hack : ré-extraction directe du code pour stockage dans la base
				   $code_lp=trim(pdf_traitement_macros($dbr, "%code%", $candidat_array, $candidature_array, $cursus_array, $lang));
            }
            else
            {
               if(!is_array($candidature_array))
                  print("Erreur : impossible de récupérer les informations de la candidature (__get_candidature_array(), #id $cand_id)\n");
                  
               if(!is_array($candidat_array))
                  print("Erreur : impossible de récupérer les informations du candidat (__get_candidat_array(), #id $c_id)\n");
            }
				
				// Si le message n'est pas vide, on enregistre et on envoie
				if($TESTMODE==0 && trim($corps_message)!="")
            {
               $dest_array=array("0" => array("id"       => "$c_id",
                                              "civ"      => "$cand_civ.",
                                              "nom"      => "$cand_nom",
                                              "prenom"   => "$cand_prenom",
                                              "email"    => "$cand_email"));

               write_msg("", array("id" => "0", "nom" => "Système", "prenom" => ""), $dest_array, "Inscription administrative - ".$candidature_array["texte_formation"],
                        $corps_message, "$cand_nom $cand_prenom");

               // Dernière étape : insertion dans la table si le code n'est pas vide
               if(isset($code_lp) && $code_lp!="")
                  db_query($dbr,"INSERT INTO $_module_apogee_DB_codes_LP VALUES ('$code_lp','$cand_id','$ligne_candidat')");
            }
            elseif($TESTMODE==1) // envoi d'un seul message à l'admin
            {
               $TEST_CAND.="$ligne_candidat\n";
               $TEST_VOEUX.="$ligne_voeu\n";

               if(!$TESTCNT)
               {
                  $sent=write_msg_2($dbr, array("id" => "0", "nom" => "Système", "prenom" => "", "src_type" => "gestion", "composante" => "", "universite" => "$GLOBALS[__SIGNATURE_COURRIELS]"),
                                    array("0" => array("id" => 0, "dest_type" => "gestion")), "[Extractions : Test Laisser-Passer]", $corps_message);
                  $TESTCNT=1;
               }
            }

			}
		}
			
		fclose($fichier_LP);
	}

	db_free_result($result);

	db_close($dbr);
?>
