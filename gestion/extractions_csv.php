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

   if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
   {
      header("Location:$__MOD_DIR/gestion/noaccess.php");
      exit();
   }

   $dbr=db_connect();

   // Déverrouillage, au cas où
   if(isset($_SESSION["candidat_id"]))
      cand_unlock($dbr, $_SESSION["candidat_id"]);

   if(isset($_POST["Valider"]) || isset($_POST["Valider_x"]))
   {
      // Récupération des informations du formulaire, construction de la requête et affiche du lien vers le fichier construit

      $cur_propspec_id=array_key_exists("formation", $_POST) ? $_POST["formation"] : "";

      $cur_desactivees=array_key_exists("desactivees", $_POST) ? $_POST["desactivees"] : 0;

      $cur_periode=(array_key_exists("periode", $_POST) && $_POST["periode"]!="")? $_POST["periode"] : $__PERIODE;

      $cur_affichage_formations=(array_key_exists("mode_affichage_formations", $_POST) && $_POST["mode_affichage_formations"]!="")? $_POST["mode_affichage_formations"] : 0;

      $cur_first_tri=array_key_exists("tri1", $_POST) ? $_POST["tri1"] : "tri_nom";
      $cur_sec_tri=array_key_exists("tri2", $_POST) ? $_POST["tri2"] : "";

      $cur_tri_rang=array_key_exists("tri_rang", $_POST) ? $_POST["tri_rang"] : "1";

      $cur_civilite=array_key_exists("civilite", $_POST) ? $_POST["civilite"] : 0;
      $cur_date_naissance=array_key_exists("date_naissance", $_POST) ? $_POST["date_naissance"] : 0;
      $cur_lieu_naissance=array_key_exists("lieu_naissance", $_POST) ? $_POST["lieu_naissance"] : 0;
      $cur_nationalite=array_key_exists("nationalite", $_POST) ? $_POST["nationalite"] : 0;
      $cur_adresse=array_key_exists("adresse", $_POST) ? $_POST["adresse"] : 0;
      $cur_email=array_key_exists("email", $_POST) ? $_POST["email"] : 0;
      $cur_telephone=array_key_exists("telephone", $_POST) ? $_POST["telephone"] : 0;
      $cur_telephone_portable=array_key_exists("telephone_portable", $_POST) ? $_POST["telephone_portable"] : 0;

      $cur_num_ine=array_key_exists("num_ine", $_POST) ? $_POST["num_ine"] : 0;

      $cur_mode_adresse=array_key_exists("mode_adresse", $_POST) ? $_POST["mode_adresse"] : 0;

      $cur_cursus=array_key_exists("cursus", $_POST) ? $_POST["cursus"] : 0;
      $cur_aff_cursus=array_key_exists("aff_cursus", $_POST) ? $_POST["aff_cursus"] : 0;
      
      $cur_dossier=array_key_exists("dossier", $_POST) ? $_POST["dossier"] : 0;
      $cur_decision=array_key_exists("decision", $_POST) ? $_POST["decision"] : 0;
      $cur_statut=array_key_exists("statut", $_POST) ? $_POST["statut"] : 0;
      $cur_motivation=array_key_exists("motivation", $_POST) ? $_POST["motivation"] : 0;
      $cur_entretien=array_key_exists("entretien", $_POST) ? $_POST["entretien"] : 0;
      $cur_frais=array_key_exists("statut_frais", $_POST) ? $_POST["statut_frais"] : 0;
      $cur_ordre_voeu=array_key_exists("ordre_voeu", $_POST) ? $_POST["ordre_voeu"] : 1;

      // Statut : boucle sur statut_prec[]
      if(array_key_exists("statut_prec", $_POST))
      {
         $cur_statut_prec=array();
         $condition_statut="AND $_DBC_cand_statut IN (";

         foreach($_POST["statut_prec"] as $statut_prec_id => $statut_prec_val)
         {
            $cur_statut_prec[$statut_prec_id]=$statut_prec_id;
            $condition_statut.="$statut_prec_id,";
         }
         $condition_statut=substr($condition_statut, 0, -1) . ")";
      }
      else
         $condition_statut="AND $_DBC_cand_statut='$__PREC_RECEVABLE'";

      // Décisions : boucle sur dec[]
      if(array_key_exists("dec", $_POST))
      {
         $cur_dec=array();
         $condition_decision="AND $_DBC_cand_decision IN (";

         foreach($_POST["dec"] as $dec_id => $dec_val)
         {
            $cur_dec[$dec_id]=$dec_id;
            $condition_decision.="$dec_id,";
         }
         $condition_decision=substr($condition_decision, 0, -1) . ")";
      }
      else
         $condition_decision="";

      // Méthode de tri
      switch($cur_first_tri)
      {
         case   "tri_nom"       :    $methode_tri="ORDER BY $_DBC_candidat_nom, $_DBC_candidat_nom_naissance, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance ";
                                 break;

         case   "tri_formation":    $methode_tri="ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom, $_DBC_propspec_finalite ";
                                 break;

         case   "tri_decision" :   $methode_tri="ORDER BY $_DBC_cand_decision ";
                                 if($cur_tri_rang)
                                    $methode_tri.=", $_DBC_cand_liste_attente";

                                 break;

         case   "tri_statut" :      $methode_tri="ORDER BY $_DBC_cand_statut ";
                                 break;
                                 
         case	"tri_date_asc" : $methode_tri="ORDER BY $_DBC_cand_id ";
                                 break;
                                 
         case	"tri_date_desc" : $methode_tri="ORDER BY $_DBC_cand_id DESC";
                                 break;                                 

         default               :   $methode_tri="ORDER BY $_DBC_candidat_nom, $_DBC_candidat_nom_naissance, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance ";
                                 break;
      }

      if($cur_sec_tri!="" && $cur_sec_tri!=$cur_first_tri && $cur_first_tri!="tri_date_asc" && $cur_first_tri!="tri_date_desc")
      {
         switch($cur_sec_tri)
         {
            case   "tri_nom"       :    $methode_tri.=", $_DBC_candidat_nom, $_DBC_candidat_nom_naissance, $_DBC_candidat_prenom, $_DBC_candidat_date_naissance ";
                                    break;

            case   "tri_formation":    $methode_tri.=", $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom, $_DBC_propspec_finalite";
                                    break;

            case   "tri_decision" :   $methode_tri.=", $_DBC_cand_decision ";
                                    if($cur_tri_rang)
                                       $methode_tri.=", $_DBC_cand_liste_attente";
                                    break;

            case   "tri_statut" :      $methode_tri.=", $_DBC_cand_statut ";
                                    break;

         case	"tri_date_asc" : $methode_tri.=", $_DBC_cand_id ";
                                 break;
                                 
         case	"tri_date_desc" : $methode_tri.=", $_DBC_cand_id DESC";
                                 break;                                 
            
                                    
            default               :   $methode_tri.=", $_DBC_cand_decision ";
                                    break;
         }
      }
   
      if($cur_propspec_id=="" || (!ctype_digit($cur_propspec_id) && $cur_propspec_id!="toutes"))
      {
         $erreur_formation=1;
         $link="";
      }
      else
      {
         // Formation pour laquelle extraire les données
         // Si toutes les formations sont demandées, il faut ajouter le nom de chaque formation
         if($cur_propspec_id!="toutes")
            $condition_formation="AND $_DBC_cand_propspec_id='$cur_propspec_id'";
         else
            $condition_formation="AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'";

         // Inclure les formations désactivées (utile pour les années précédentes)
         $condition_desactivees=$cur_desactivees ? "" : "AND $_DBC_propspec_active='1'";

         $result_ext=db_query($dbr, "SELECT $_DBC_candidat_id, $_DBC_candidat_civilite, $_DBC_candidat_nom, $_DBC_candidat_nom_naissance, $_DBC_candidat_prenom,
                                            $_DBC_candidat_date_naissance, $_DBC_candidat_lieu_naissance, 
                                            CASE WHEN $_DBC_candidat_pays_naissance IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance) 
                                                  THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_pays_naissance)
                                                  ELSE '' END as pays_naissance,
                                            CASE WHEN $_DBC_candidat_nationalite IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite) 
                                                  THEN (SELECT $_DBC_pays_nat_ii_nat FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_nationalite)
                                                  ELSE '' END as nationalite,
                                            $_DBC_candidat_numero_ine, $_DBC_candidat_email, $_DBC_candidat_adresse_1, $_DBC_candidat_adresse_2, $_DBC_candidat_adresse_3,
                                            $_DBC_candidat_adresse_cp, $_DBC_candidat_adresse_ville, 
                                            CASE WHEN $_DBC_candidat_adresse_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_adresse_pays) 
                                                  THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_candidat_adresse_pays)
                                                  ELSE '' END as adresse_pays,
                                            $_DBC_decisions_id,
                                            $_DBC_decisions_texte, $_DBC_cand_motivation_decision, $_DBC_cand_liste_attente,
                                            $_DBC_cand_entretien_date, $_DBC_cand_entretien_salle, $_DBC_cand_entretien_lieu,
                                            $_DBC_cand_vap_flag, $_DBC_cand_statut_frais, $_DBC_cand_transmission_dossier,
                                            $_DBC_propspec_frais, $_DBC_cand_statut, $_DBC_candidat_telephone, $_DBC_candidat_telephone_portable, 
                                            $_DBC_annees_annee, $_DBC_mentions_nom, $_DBC_specs_nom_court, $_DBC_propspec_finalite, $_DBC_propspec_id, 
					                             $_DBC_cand_ordre, $_DBC_cand_id
                                        FROM $_DB_candidat, $_DB_cand, $_DB_decisions, $_DB_propspec, $_DB_specs, $_DB_mentions, 
                                            $_DB_annees
                                     WHERE $_DBC_candidat_id=$_DBC_cand_candidat_id
                                     AND $_DBC_specs_id=$_DBC_propspec_id_spec
                                     AND $_DBC_propspec_annee=$_DBC_annees_id
                                     AND $_DBC_mentions_id=$_DBC_specs_mention_id
                                     AND $_DBC_cand_propspec_id=$_DBC_propspec_id
                                     AND $_DBC_cand_decision=$_DBC_decisions_id
                                     AND $_DBC_cand_periode='$cur_periode'
                                     $condition_formation
                                     $condition_desactivees
                                     $condition_decision
                                     $condition_statut
                                     $methode_tri");

         $rows_ext=db_num_rows($result_ext);

         if($rows_ext)
         {
            $array_motifs=array();

            if(!is_dir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/extractions/"))
               mkdir("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/extractions/", 0770, TRUE);

            $filename="Extraction_$_SESSION[auth_id]_" . time() . ".csv";

            if($fp=fopen("$__GESTION_COMP_STOCKAGE_DIR_ABS/$_SESSION[comp_id]/extractions/$filename","a"))
            {
               // En-tête du fichier, avec les noms de colonnes
               $string=($cur_affichage_formations==0) ? "\"FORMATION\";" : "";              
               $string.=($cur_first_tri=="tri_date_asc") || ($cur_first_tri=="tri_date_desc") || ($cur_sec_tri=="tri_date_asc") || ($cur_sec_tri=="tri_date_desc") ? "\"DATE\";" : "";
               $string.=$cur_civilite ? "\"CIV\";" : "";
               $string.="\"NOM\";\"PRENOM\";";
               $string.=$cur_date_naissance ? "\"NAISSANCE\";" : "";
               $string.=$cur_lieu_naissance ? "\"LIEU NAISSANCE\";" : "";
               $string.=$cur_nationalite ? "\"NATIONALITE\";" : "";

               // Option particulière pour l'adresse postale : champs regroupés ou distincts
               $string.=($cur_adresse && $cur_mode_adresse==0) ? "\"ADRESSE\";" : "";
               $string.=($cur_adresse && $cur_mode_adresse==1) ? "\"ADRESSE\";\"CODE POSTAL\";\"VILLE\";\"PAYS\";" : "";

               $string.=$cur_email ? "\"COURRIEL\";" : "";
               $string.=$cur_telephone ? "\"TELEPHONE FIXE\";" : "";
               $string.=$cur_telephone_portable ? "\"TELEPHONE PORTABLE\";" : "";
               $string.=$cur_num_ine ? "\"N° INE\";" : "";

               if(isset($cur_cursus) && $cur_cursus!=0)
               {               
                  if(isset($cur_aff_cursus) && $cur_aff_cursus==1)
                     $string.="\"CURSUS\";";
                  else
                  {
                     $string.="\"CUR_ANNEE\";\"CUR_INTITULE\";\"CUR_ETAB\";\"CUR_VILLE\";\"CUR_PAYS\";\"CUR_DETAILS\";";
                     
                     // Cursus sur plusieurs lignes et colonnes : on compte le nombre de champs vides à insérer pour les lignes supplémentaires
                     $nb_vides=0;
                     $nb_vides+=($cur_affichage_formations==0) ? 1 : 0;
                     $nb_vides+=$cur_civilite ? 3 : 2; // (civ ?)+nom+prenom
                     $nb_vides+=$cur_date_naissance ? 1 : 0;
                     $nb_vides+=$cur_lieu_naissance ? 1 : 0;
                     $nb_vides+=$cur_nationalite ? 1 : 0;
                     $nb_vides+=($cur_adresse && $cur_mode_adresse==0) ? 1 : 0;
                     $nb_vides+=($cur_adresse && $cur_mode_adresse==1) ? 4 : 0;
                     $nb_vides+=$cur_email ? 1 : 0;
                     $nb_vides+=$cur_telephone ? 1 : 0;
                     $nb_vides+=$cur_telephone_portable ? 1 : 0;
                     $nb_vides+=$cur_num_ine ? 1 : 0;
                     
                     $champs_vides=str_repeat("\"\";", $nb_vides);                     
                  }
               }
               
	            $string.=$cur_ordre_voeu ? "\"ORDRE VOEU\";" : "";
               $string.=$cur_statut ? "\"RECEVABILITE\";" : "";
               $string.=$cur_decision ? "\"DECISION\";" : "";
               $string.=$cur_motivation ? "\"MOTIF\";" : "";
               $string.=$cur_entretien ? "\"ENTRETIEN\";" : "";
               $string.=$cur_frais ? "\"FRAIS DOSSIER\";" : "";

               // Constructeur de dossiers : une colonne par question/réponse (attention au type)
               if($cur_dossier=="1")
               {
                  if($cur_propspec_id!="toutes")
                    $condition_formation_dossiers="$_DBC_dossiers_ef_propspec_id='$cur_propspec_id'";
                  else
                    $condition_formation_dossiers="$_DBC_dossiers_ef_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]')";                  
                  
                  $res_dossiers=db_query($dbr,"SELECT $_DBC_dossiers_elems_id, $_DBC_dossiers_elems_intitule, $_DBC_dossiers_elems_type
                                                FROM $_DB_dossiers_elems 
                                               WHERE $_DBC_dossiers_elems_id IN (SELECT $_DBC_dossiers_ef_elem_id FROM $_DB_dossiers_ef 
                                                                                 WHERE $condition_formation_dossiers)
                                               AND $_DBC_dossiers_elems_extractions is TRUE");
                  
                  $rows_dossiers=db_num_rows($res_dossiers);
                  
                  if($res_dossiers)
                  {
                     $array_dossiers=array();
                     
                     for($d=0; $d<$rows_dossiers; $d++)
                     {
                        list($dossier_elem_id, $dossier_elem_intitule, $dossier_elem_type)=db_fetch_row($res_dossiers, $d);
                        
                        $array_dossiers[$d]=array("id" => $dossier_elem_id, "intitule" => "$dossier_elem_intitule", "type" => "$dossier_elem_type");
                        $string.="\"$dossier_elem_intitule\";";  
                     }
                  }
                  
                  db_free_result($res_dossiers);
               }

               $evt_string=preg_replace("/, $/","", strtolower(str_replace("\"","",str_replace("\";", ", ", $string))));
               write_evt($dbr, $__EVT_ID_G_MASSE, "Extraction CSV, Formation(s) : $cur_propspec_id : $evt_string","", $_SESSION["auth_id"]);

               $string.="\n";

               fwrite($fp, $string, strlen($string));

               $prev_propspec_id="--";

               for($i=0; $i<$rows_ext; $i++)
               {
                  $entretien_txt="";
                  $frais_txt="";
                  $motivation="";

                  list($candidat_id, $candidat_civ, $candidat_nom, $candidat_nom_naissance, $candidat_prenom, $candidat_date_naissance, 
                       $candidat_lieu_naissance, $candidat_pays_naissance, $candidat_nationalite, 
                       $candidat_num_ine, $candidat_email, $candidat_adresse_1, $candidat_adresse_2, $candidat_adresse_3, $candidat_adr_cp, 
                       $candidat_adr_ville, $candidat_adr_pays, $cand_decision_id, $cand_decision, $cand_motivation, $cand_liste_attente, 
                       $cand_entretien_date, $cand_entretien_salle, $cand_entretien_lieu, $cand_vap, $cand_statut_frais, $cand_transmission,
                       $frais_dossiers, $cand_statut, $candidat_telephone, $candidat_telephone_portable, $annee, $mention, $spec_nom, $finalite,
                       $propspec_id, $cand_ordre, $cand_id)=db_fetch_row($result_ext, $i);

                  // Titres
/*                  
                  if($cur_propspec_id=="toutes" && $propspec_id!=$prev_propspec_id)
                  {
                     $nom_formation=$annee=="" ? "[$mention] $spec_nom" : "$annee [$mention] $spec_nom";
                     $nom_formation.=$tab_finalite[$finalite]=="" ? "" : " $tab_finalite[$finalite]";

                     if($cur_affichage_formations==1)
                     {
                        $str="\n\"$nom_formation\";\n;\n";
                        fwrite($fp, $str, strlen($str));
                     }

                     $prev_propspec_id=$propspec_id;
                  }
*/
                  $candidat_adresse=$candidat_adresse_1;
                  $candidat_adresse.=$candidat_adresse_2!="" ? "\n".$candidat_adresse_2 : "";
                  $candidat_adresse.=$candidat_adresse_3!="" ? "\n".$candidat_adresse_3 : "";


                  if($propspec_id!=$prev_propspec_id)
                  {
                     $nom_formation=$annee=="" ? "[$mention] $spec_nom" : "$annee [$mention] $spec_nom";
                     $nom_formation.=$tab_finalite[$finalite]=="" ? "" : " $tab_finalite[$finalite]";

                     if($cur_affichage_formations==1)
                     {
                        $str="\n\"$nom_formation\";\n;\n";
                        fwrite($fp, $str, strlen($str));
                     }

                     $prev_propspec_id=$propspec_id;
                  }

                  // Cursus
                  if($cur_cursus)
                  {
                     if($cur_cursus==1) // dernier diplôme
                        $limite_cursus="LIMIT 1";
                     elseif($cur_cursus==2) // Tout
                        $limite_cursus="";

                     $result_cursus=db_query($dbr, "SELECT $_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec, $_DBC_cursus_ecole,
                                                         $_DBC_cursus_ville, 
                                                         CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
                                                            THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
                                                            ELSE '' END as cursus_pays,
                                                         $_DBC_cursus_moyenne, $_DBC_cursus_mention,
                                                         $_DBC_cursus_rang, $_DBC_cursus_annee
                                                         FROM $_DB_cursus
                                                      WHERE $_DBC_cursus_candidat_id='$candidat_id'
                                                      AND $_DBC_cursus_mention!='Ajourné'
                                                         ORDER BY annee DESC
                                                         $limite_cursus");

                     $rows_cursus=db_num_rows($result_cursus);

                     if($rows_cursus)
                     {
                        $cursus="";

                        if($cur_aff_cursus==0)
                           $array_cursus=array();

                        for($j=0; $j<$rows_cursus; $j++)
                        {
                           list($cursus_libelle, $cursus_intitule, $cursus_spec, $cursus_ecole, $cursus_ville, $cursus_pays,
                              $cursus_moyenne, $cursus_mention, $cursus_rang, $cursus_annee)=db_fetch_row($result_cursus, $j);

                           $ligne_cursus="$cursus_annee : $cursus_libelle";
                           $lieu_cursus="";

                           // Informations ajoutées si elles ont été complétées

                           if($cursus_intitule!="")
                              $ligne_cursus.=" $cursus_intitule";

                           if($cursus_spec!="")
                              $ligne_cursus.=" - $cursus_spec";

                           if(!empty($cursus_moyenne) || !empty($cursus_mention) || !empty($cursus_rang))
                              $ligne_cursus.="\n";

                           if($cursus_moyenne!="")
                              $ligne_cursus.="Moyenne : $cursus_moyenne ";

                           if($cursus_mention!="")
                              $ligne_cursus.="Mention : $cursus_mention ";

                           if($cursus_rang!="")
                              $ligne_cursus.="Rang : $cursus_rang";

                           // Lieu d'obtention
                           if($cursus_ecole!="")
                              $lieu_cursus.="$cursus_ecole";

                           if($cursus_ville!="")
                           {
                              if(!empty($lieu_cursus))
                                 $lieu_cursus.=", ";

                              $lieu_cursus.="$cursus_ville";
                           }

                           if($cursus_pays!="" && strncasecmp($cursus_pays, "france", 6))
                           {
                              if(!empty($lieu_cursus))
                                 $lieu_cursus.=", ";

                              $lieu_cursus.="$cursus_pays";
                           }

                           if($lieu_cursus!="")
                              $ligne_cursus.="\n$lieu_cursus";

                           if($cursus!="")
                              $cursus.="\n";

                           $cursus.="$ligne_cursus";
                           
                           if($cur_aff_cursus==0)
                           {
                              $dip_nom=str_replace(";", ",", str_replace("\"", "'", $cursus_libelle));
                              $dip_nom.=trim($cursus_intitule)!="" ? " - " . str_replace(";", ",", str_replace("\"", "'", $cursus_intitule)) : "";
                              $dip_nom.=trim($cursus_spec)!="" ? " - " . str_replace(";", ",", str_replace("\"", "'", $cursus_spec)) : "";
                              
                              $rang_moyenne_mention="";
                              $rang_moyenne_mention.=trim($cursus_moyenne)!="" ? "Moyenne=" . str_replace(";", ",", str_replace("\"", "'", $cursus_moyenne)) . " " : "";
                              $rang_moyenne_mention.=trim($cursus_rang)!="" ? "Rang=" . str_replace(";", ",", str_replace("\"", "'", $cursus_rang)) . " " : "";
                              $rang_moyenne_mention.=trim($cursus_mention)!="" ? "Mention=" . str_replace(";", ",", str_replace("\"", "'", $cursus_mention)) : "";
                              
                              $array_cursus[$j]="\"$cursus_annee\";\"$dip_nom\";\"$cursus_ecole\";\"$cursus_ville\";\"$cursus_pays\";\"$rang_moyenne_mention\";";
                           }
                        }
                     }
                     else
                        $cursus="";

                     db_free_result($result_cursus);
                  }

                  // Décision
                  // Transmission de dossier : on ajoute la formation destination
                  if($cur_decision && $cand_decision_id==$__DOSSIER_TRANSMIS)
                     $cand_decision.=" : $cand_transmission";
                  elseif($cur_decision && ($cand_decision_id==$__DOSSIER_LISTE || $cand_decision_id==$__DOSSIER_LISTE_ENTRETIEN))
                     $cand_decision.=", rang $cand_liste_attente";

                  // Motif de la décision : dépend directement de la décision en question
/*                  
                  if($cur_motivation && ($cand_decision_id==$__DOSSIER_EN_ATTENTE || $cand_decision_id==$__DOSSIER_SOUS_RESERVE
                  || $cand_decision_id==$__DOSSIER_REFUS || $cand_decision_id==$__DOSSIER_REFUS_ENTRETIEN
                  || $cand_decision_id==$__DOSSIER_REFUS_RECOURS || $cand_decision_id==$__DOSSIER_TRANSMIS))
*/
                  if($cur_motivation)                  
                  {
                     if($cand_motivation!="")
                     {
                        $motivation_array=explode("|", $cand_motivation);

                        foreach($motivation_array as $current_motif)
                        {
                           if(ctype_digit($current_motif))
                           {
                              if(array_key_exists($current_motif, $array_motifs))
                              {
                                 $motivation.=$motivation!="" ? "\n" : "";
                                 $motivation.=$array_motifs[$current_motif];
                              }
                              else
                              {
                                 $res_motif=db_query($dbr, "SELECT $_DBC_motifs_refus_motif FROM $_DB_motifs_refus
                                                            WHERE $_DBC_motifs_refus_id='$current_motif'");

                                 if(db_num_rows($res_motif))
                                 {
                                    list($motif)=db_fetch_row($res_motif, 0);
                                    $array_motifs[$current_motif]=$motif;

                                    $motivation.=$motivation!="" ? "\n" : "";
                                    $motivation.=$motif;
                                 }

                                 db_free_result($res_motif);
                              }
                           }
                           else
                           {
                              $motivation.=$motivation!="" ? "\n" : "";
                              // $motivation.=str_replace("@", "", $current_motif);
                              $motivation.=preg_replace("/^@/","", $current_motif);
                           }
                        }
                     }
                  }

                  // Nettoyage de valeurs, au cas où
                  if($cur_entretien && ($cand_decision_id==$__DOSSIER_ENTRETIEN || $cand_decision_id==$__DOSSIER_ENTRETIEN_TEL) && $cand_entretien_date!=0)
                  {
                     $entretien_txt=date_fr("l jS F Y", $cand_entretien_date);
                     $ent_heure=date("H", $cand_entretien_date);

                     if($ent_heure!=0)
                     {
                        $ent_minute=date("i", $cand_entretien_date);
                        $entretien_txt.=" à $ent_heure" . "h$ent_minute";
                     }

                     $entretien_txt.=$cand_entretien_salle!="" ? " - $cand_entretien_salle" : "";
                     $entretien_txt.=$cand_entretien_lieu!="" ? " - $cand_entretien_lieu" : "";
                  }

                  // Statut des frais de dossiers
                  if($cur_frais)
                  {
                     if($frais_dossiers)
                     {
                        switch($cand_statut_frais)
                        {
                           case $__STATUT_FRAIS_EN_ATTENTE   :   // vide (en attente)
                                       $frais_txt="En attente";
                                       break;

                           case $__STATUT_FRAIS_ACQUITTES   :   // frais payés
                                       $frais_txt="Acquittés";
                                       break;

                           case $__STATUT_FRAIS_BOURSIER   :   // Candidat Boursier
                                       $frais_txt="Candidat boursier";
                                       break;

                           case $__STATUT_FRAIS_DISPENSE : // candidat dispensé des frais
                                       $frais_txt="Candidat dispensé";
                                       break;

                           case $__STATUT_FRAIS_NON_ACQUITTES   :   // non payés
                                       $frais_txt="Non acquittés";
                                       break;

                           default : // vide
                                       $frais_txt="En attente";
                                       break;
                        }
                     }
                     else
                        $frais_txt="Aucun pour cette formation";
                  }

                  if($candidat_nom_naissance!=$candidat_nom && $candidat_nom_naissance!="")
                  {
                     if($candidat_civ=="M")
                        $candidat_nom.=" (né $candidat_nom_naissance)";
                     else
                        $candidat_nom.=" (née $candidat_nom_naissance)";
                  }

                  $string=($cur_affichage_formations==0) ? "\"$nom_formation\";" : "";
                  $string.=($cur_first_tri=="tri_date_asc") || ($cur_first_tri=="tri_date_desc") || ($cur_sec_tri=="tri_date_asc") || ($cur_sec_tri=="tri_date_desc") ? "\"".date("j/m/Y", id_to_date($cand_id))."\";" : "";
                  $string.=$cur_civilite ? "\"$candidat_civ\";" : "";
                  $string.="\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_nom)) . "\";\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_prenom)) . "\";";
                  $string.=$cur_date_naissance ? "\"" . date_fr("j F Y", $candidat_date_naissance) . "\";" : "";
                  $string.=$cur_lieu_naissance ? "\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_lieu_naissance)) . "\";" : "";
                  $string.=$cur_nationalite ? "\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_nationalite)) . "\";" : "";

                  $string.=($cur_adresse && $cur_mode_adresse==0) ? "\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_adresse)) . "\n" . str_replace(";", ",", str_replace("\"", "'", $candidat_adr_cp)) . " " . str_replace(";", ",", str_replace("\"", "'", $candidat_adr_ville)) . "\n" . str_replace(";", ",", str_replace("\"", "'", $candidat_adr_pays)) . "\";" : "";
                  $string.=($cur_adresse && $cur_mode_adresse==1) ? "\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_adresse)) . "\";\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_adr_cp)) . "\";\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_adr_ville)) . "\";\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_adr_pays)) . "\";" : "";
                  
                  $string.=$cur_email ? "\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_email)) . "\";" : "";
                  $string.=$cur_telephone ? "\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_telephone)) . "\";" : "";
                  $string.=$cur_telephone_portable ? "\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_telephone_portable)) . "\";" : "";
                  $string.=$cur_num_ine ? "\"" . str_replace(";", ",", str_replace("\"", "'", $candidat_num_ine)) . "\";" : "";
                  
                  if($cur_cursus)
                  {
                     $string_cursus="";
                     
                     if($cur_aff_cursus==1)
                        $string.="\"" . str_replace(";", ",", str_replace("\"", "'", $cursus)) . "\";";
                     elseif(is_array($array_cursus) && count($array_cursus))
                     {
                        // Première ligne ...
                        $string.=$array_cursus["0"];
                        unset($array_cursus["0"]);
                        
                        // Puis les autres
                        foreach($array_cursus as $ligne_cursus)
                           $string_cursus.=$champs_vides . $ligne_cursus . "\n";
                     }
                     else // pas de cursus
                        $string.=str_repeat("\"\";", 6);
                  }                       

		  $string.=$cur_ordre_voeu ? "\"$cand_ordre\";" : "";
                  $string.=$cur_statut ? "\"" . str_replace(";", ",", str_replace("\"", "'", $tab_recevabilite[$cand_statut])) . "\";" : "";
                  $string.=$cur_decision ? "\"" . str_replace(";", ",", str_replace("\"", "'", $cand_decision)) . "\";" : "";
                  $string.=$cur_motivation ? "\"" . str_replace(";", ",", str_replace("\"", "'", $motivation)) . "\";" : "";
                  $string.=$cur_entretien ? "\"" . str_replace(";", ",", str_replace("\"", "'", $entretien_txt)) . "\";" : "";
                  $string.=$cur_frais ? "\"" . str_replace(";", ",", str_replace("\"", "'", $frais_txt)) . "\";" : "";

                  if($cur_dossier=="1" && isset($array_dossiers))
                  {
                     foreach($array_dossiers as $array_element)
                     {
                        $dossier_elem_id=$array_element["id"];                        

                        // Réponse du candidat en fonction du type d'élément :
                        // - type "standard" : $_DBC_dossiers_elems_contenu_para contient le texte de la réponse
                        // - type "Un choix" : on peut faire une seule requête avec jointure
                        // - type "plusieurs choix" : on doit faire plusieurs requêtes
                        
                        if($array_element["type"]==$__ELEM_TYPE_FORM || $array_element["type"]==$__ELEM_TYPE_MULTI_CHOIX)
                        {                     
                           $res_dossier_contenu=db_query($dbr, "SELECT $_DBC_dossiers_elems_contenu_para FROM $_DB_dossiers_elems_contenu 
                                                                  WHERE $_DBC_dossiers_elems_contenu_candidat_id='$candidat_id' 
                                                                  AND $_DBC_dossiers_elems_contenu_periode='$cur_periode'
                                                                  AND $_DBC_dossiers_elems_contenu_elem_id='$dossier_elem_id'
                                                                  AND $_DBC_dossiers_elems_contenu_propspec_id='$propspec_id'");
                        }
                        elseif($array_element["type"]==$__ELEM_TYPE_UN_CHOIX)
                        {
                           $res_dossier_contenu=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_texte 
                                                                   FROM $_DB_dossiers_elems_contenu, $_DB_dossiers_elems_choix
                                                                WHERE CAST($_DBC_dossiers_elems_contenu_para AS BIGINT)=$_DBC_dossiers_elems_choix_id
                                                                AND $_DBC_dossiers_elems_contenu_candidat_id='$candidat_id' 
                                                                AND $_DBC_dossiers_elems_contenu_periode='$cur_periode'
                                                                AND $_DBC_dossiers_elems_contenu_elem_id='$dossier_elem_id'
                                                                AND $_DBC_dossiers_elems_contenu_propspec_id='$propspec_id'");
                        }
                           
                        $rows_dossiers_contenu=db_num_rows($res_dossier_contenu);
                        
                        if($rows_dossiers_contenu)
                        {
                           list($dossier_contenu)=db_fetch_row($res_dossier_contenu, 0);
                         
                           if($array_element["type"]==$__ELEM_TYPE_MULTI_CHOIX)
                           {
                              $dossier_contenu_txt="";
                              
                              $array_choix=explode("|", $dossier_contenu);
                              
                              foreach($array_choix as $choix_id)
                              {
                                 $choix_id=trim($choix_id);
                                 
                                 if($choix_id!="" && ctype_digit($choix_id))
                                 {
                                    $res_choix=db_query($dbr, "SELECT $_DBC_dossiers_elems_choix_texte FROM $_DB_dossiers_elems_choix 
                                                                  WHERE $_DBC_dossiers_elems_choix_id='$choix_id'");
                                                       
                                    $nb_choix=db_num_rows($res_choix);
                                                                  
                                    if($nb_choix)
                                    {
                                       for($c=0; $c<$nb_choix; $c++)
                                       {
                                          list($nom_choix)=db_fetch_row($res_choix, $c);
                                          
                                          $dossier_contenu_txt.=$dossier_contenu_txt!="" ? "\n" : "";
                                          $dossier_contenu_txt.="- $nom_choix";
                                       }
                                    }
                                    
                                    db_free_result($res_choix);
                                 }
                              }
                           }
                           
                           $dossier_contenu_txt=str_replace(";", ",", str_replace("\"", "'", $dossier_contenu));
                        }
                        else
                           $dossier_contenu_txt="";
                        
                        db_free_result($res_dossier_contenu);
                      
                        $string.="\"$dossier_contenu_txt\";";
                     }                     
                  }

                  $string.="\n";
                  fwrite($fp, $string, strlen($string));
                  
                  if(isset($string_cursus) && trim($string_cursus)!="")
                     fwrite($fp, $string_cursus, strlen($string_cursus));
               }

               $link="<a href='$__GESTION_DIR/fichiers/composantes/$_SESSION[comp_id]/extractions/$filename' target='_blank' class='lien_bleu_12'><b>Fichier CSV prêt : cliquer sur ce lien pour l'ouvrir</b></a>";

               fclose($fp);
            }
            else
            {
               $erreur_fichier_csv=1;
               $link="";
            }
         }
         else
            $link="<font class='Texte_important'><b>Aucun candidat ne correspond à ces critères</b></font>";

         db_free_result($result_ext);
      }
   }

   // EN-TETE
   en_tete_gestion();

   // MENU SUPERIEUR
   menu_sup_gestion();
?>
<div class='main'>
   <?php
      titre_page_icone("Extractions de données au format CSV (pour import dans un tableur)", "xmag_32x32_fond.png", 30, "L");

      if(isset($link) && $link!="")
      {
         message("$link", $__SUCCES);
         message("Dans votre traitement de texte, indiquez le caractère ; (point virgule) comme séparateur de champs.", $__INFO);

      }

      print("<form action='$php_self' method='POST' name='form1'>\n");
   ?>

   <div style='max-width:80%; margin:0px auto 0px auto'>
      <table style='width:100%; margin-bottom:10px;'>
      <tr>
         <td class='td-gauche fond_menu2' colspan='3' style='padding:4px;'>
            <font class='Texte_menu2'><b>Recherche</b></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Formation : </b></font>
         </td>
         <td class='td-droite fond_menu'>
            <?php
               $result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_propspec_annee, $_DBC_annees_annee, $_DBC_propspec_id_spec,
                                             $_DBC_specs_nom_court, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_mentions_nom
                                          FROM $_DB_annees, $_DB_propspec, $_DB_specs, $_DB_mentions
                                       WHERE $_DBC_propspec_annee=$_DBC_annees_id
                                       AND $_DBC_propspec_id_spec=$_DBC_specs_id
                                       AND $_DBC_specs_mention_id=$_DBC_mentions_id
                                       AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                          ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_specs_nom_court");

               $rows=db_num_rows($result);

               if($rows)
               {
                  print("<select size='1' name='formation'>
                           <option value='toutes' label=\"Toutes les formations\">Toutes les formations</option>
                           <option value='' label='' disabled></option>\n");

                  $old_annee="-1";
                  $old_mention="-1";

                  for($i=0; $i<$rows; $i++)
                  {
                     list($form_propspec_id, $form_annee_id, $form_annee_nom, $form_spec_id, $form_spec_nom, $form_mention, $form_finalite, $form_mention_nom)=db_fetch_row($result, $i);

                     $finalite_txt=$tab_finalite[$form_finalite];

                     if($form_annee_id!=$old_annee)
                     {
                        if($i!=0)
                           print("</optgroup>
                                    <option value='' label='' disabled></option>\n");

                        if($form_annee_nom=="")
                           $form_annee_nom="Années particulières";

                        print("<optgroup label='$form_annee_nom'>\n");

                        $new_sep_annee=1;

                        $old_annee=$form_annee_id;
                        $old_mention="-1";
                     }
                     else
                        $new_sep_annee=0;

                     if($form_mention!=$old_mention)
                     {
                        if(!$new_sep_annee)
                           print("</optgroup>
                                    <option value='' label='' disabled></option>\n");

                        $val=htmlspecialchars($form_mention_nom, ENT_QUOTES, $default_htmlspecialchars_encoding);

                        print("<optgroup label='- $val'>\n");

                        $old_mention=$form_mention;
                     }

                     if(isset($cur_propspec_id) && $cur_propspec_id==$form_propspec_id)
                        $selected="selected";
                     else
                        $selected="";

                     print("<option value='$form_propspec_id' label=\"$form_spec_nom $finalite_txt\" $selected>$form_spec_nom $finalite_txt</option>\n");
                  }

                  print("</select>\n");
               }
               else
               {
                  print("<font class='Texte_important'>
                           <b>Aucune formation n'a encore été créée pour cet établissement.</b>
                           </font>\n");

                  $no_next=1;
               }

               db_free_result($result);
            ?>
         </td>
         <td class='td-droite fond_menu'>
            <font class='Texte_menu'>
               <?php
                  $selected=(isset($cur_desactivees) && $cur_desactivees==1) ? "checked" : "";

                  print("<input type='checkbox' name='desactivees' value='1' $selected>&nbsp;&nbsp;Inclure les formations désactivées");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Année universitaire : </b></font>
         </td>
         <td class='td-droite fond_menu' colspan='2'>
            <?php
               $res_periodes=db_query($dbr, "SELECT distinct($_DBC_cand_periode)
                                                FROM $_DB_cand, $_DB_propspec
                                             WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
                                             AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                                ORDER BY $_DBC_cand_periode DESC");

               $rows_periodes=db_num_rows($res_periodes);

               if($rows_periodes)
               {
                  print("<select size='1' name='periode'>\n");

                  for($i=0; $i<$rows_periodes; $i++)
                  {
                     list($periode)=db_fetch_row($res_periodes, $i);

                     $selected=(isset($cur_periode) && $cur_periode==$periode) ? "selected" : "";

                     print("<option value='$periode' $selected>$periode-" . ($periode+1) . "</option>\n");
                  }

                  print("</select>\n");
               }

               db_free_result($res_periodes);
            ?>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' rowspan='2'>
            <font class='Texte_menu'><b>Nom de la formation : </b></font>
         </td>         
         <td class='td-milieu fond_menu' colspan='2'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_affichage_formations))
                  {
                     switch($cur_affichage_formations)
                     {
                        case   0   :   $selected_0="checked";
                                    $selected_1="";
                                    break;
                        case   1   :   $selected_1="checked";
                                    $selected_0="";
                                    break;
                        default   :   $selected_0="checked";
                                    $selected_1="";
                                    break;
                     }
                  }
                  else
                  {
                     $selected_0="checked";
                     $selected_1="";
                  }

                  print("<input type='radio' name='mode_affichage_formations' value='0' $selected_0>&nbsp;&nbsp;La formation apparait sur chaque ligne (colonne distincte)");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-milieu fond_menu' colspan='2'>
            <font class='Texte_menu'>
               <?php
                  print("<input type='radio' name='mode_affichage_formations' value='1' $selected_1>&nbsp;&nbsp;Affichage par blocs : formation sur une ligne puis affichage de la liste des candidats");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Premier critère de tri : </b></font>
         </td>
         <td class='td-droite fond_menu' colspan='2'>
            <select name='tri1'>
               <option value='tri_nom' <?php if(isset($cur_first_tri) && $cur_first_tri=="tri_nom") echo "selected"; ?>>Par nom / prénom</option>
               <option value='tri_formation' <?php if(isset($cur_first_tri) && $cur_first_tri=="tri_formation") echo "selected"; ?>>Par formation</option>
               <option value='tri_decision' <?php if(isset($cur_first_tri) && $cur_first_tri=="tri_decision") echo "selected"; ?>>Par décision</option>
               <option value='tri_statut' <?php if(isset($cur_first_tri) && $cur_first_tri=="tri_statut") echo "selected"; ?>>Par statut de la recevabilité</option>
               <option value='tri_date_asc' <?php if(isset($cur_first_tri) && $cur_first_tri=="tri_date_asc") echo "selected"; ?>>Par date d'ajout de la candidature (croissante)</option>
               <option value='tri_date_desc' <?php if(isset($cur_first_tri) && $cur_first_tri=="tri_date_desc") echo "selected"; ?>>Par date d'ajout de la candidature (décroissante)</option>
            </select>
            <br>
            <font class='Texte_menu'>
               <i>
                  &#8226;&nbsp;Si vous sélectionnez "Toutes les formations", le tri "Par formation" en premier critère est FORTEMENT recommandé
                  <br>&#8226;&nbsp;Le tri par date en premier critère est exclusif (pas de second critère possible)
                  <br>&#8226;&nbsp;Le tri par date ajoute automatiquement la colonne "date" à l'extraction
               </i>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Second critère de tri : </b></font>
         </td>
         <td class='td-droite fond_menu' colspan='2'>
            <select name='tri2'>
               <option value=''></option>
               <option value='tri_nom' <?php if(isset($cur_sec_tri) && $cur_sec_tri=="tri_nom") echo "selected"; ?>>Par nom / prénom</option>
               <option value='tri_formation' <?php if(isset($cur_sec_tri) && $cur_sec_tri=="tri_formation") echo "selected"; ?>>Par formation</option>
               <option value='tri_decision' <?php if(isset($cur_sec_tri) && $cur_sec_tri=="tri_decision") echo "selected"; ?>>Par décision</option>
               <option value='tri_statut' <?php if(isset($cur_sec_tri) && $cur_sec_tri=="tri_statut") echo "selected"; ?>>Par statut de la recevabilité</option>
               <option value='tri_date_asc' <?php if(isset($cur_sec_tri) && $cur_sec_tri=="tri_date_asc") echo "selected"; ?>>Par date d'ajout de la candidature (croissante)</option>
               <option value='tri_date_desc' <?php if(isset($cur_sec_tri) && $cur_sec_tri=="tri_date_desc") echo "selected"; ?>>Par date d'ajout de la candidature (décroissante)</option>
            </select>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Liste complémentaire : </b></font>
         </td>
         <td class='td-droite fond_menu' colspan='2'>
            <font class='Texte_menu'>
               En cas de tri par décision, souhaitez vous que les décisions "Listes complémentaires" soient triées par rang ?
               <br>
               <?php
                  if(isset($cur_tri_rang))
                  {
                     switch($cur_tri_rang)
                     {
                        case   0   :   $selected_0="checked";
                                    $selected_1="";
                                    break;
                        case   1   :   $selected_1="checked";
                                    $selected_0="";
                                    break;
                        default   :   $selected_1="checked";
                                    $selected_0="";
                                    break;
                     }
                  }
                  else
                  {
                     $selected_1="checked";
                     $selected_0="";
                  }

                  print("<input style='vertical-align:middle; padding-right:5px;' type='radio' name='tri_rang' value='1' $selected_1>Oui
                         <input style='vertical-align:middle; padding-right:5px;' type='radio' name='tri_rang' value='0' $selected_0>Non");
               ?>
            </font>
         </td>
      </tr>
      </table>

      <table style='width:100%; margin-bottom:10px;'>
      <tr>
         <td class='td-gauche fond_menu2' colspan='4' style='padding:4px;'>
            <font class='Texte_menu2'><b>Données à extraire</b></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' rowspan='3'>
            <font class='Texte_menu'><b>Candidats<br>(nom et prénom toujours inclus)</b></font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_civilite) && $cur_civilite==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='civilite' value='1' $selected>&nbsp;&nbsp;Civilité");
               ?>
            </font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_date_naissance) && $cur_date_naissance==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='date_naissance' value='1' $selected>&nbsp;&nbsp;Date de naissance");
               ?>
            </font>
         </td>
         <td class='td-droite fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_lieu_naissance) && $cur_lieu_naissance==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='lieu_naissance' value='1' $selected>&nbsp;&nbsp;Lieu de naissance");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_nationalite) && $cur_nationalite==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='nationalite' value='1' $selected>&nbsp;&nbsp;Nationalité");
               ?>
            </font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_adresse) && $cur_adresse==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='adresse' value='1' $selected>&nbsp;&nbsp;Adresse postale");
               ?>
            </font>
         </td>
         <td class='td-droite fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_email) && $cur_email==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='email' value='1' $selected>&nbsp;&nbsp;Adresse électronique");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_telephone) && $cur_telephone==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='telephone' value='1' $selected>&nbsp;&nbsp;Téléphone fixe");
               ?>
            </font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_telephone_portable) && $cur_telephone_portable==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='telephone_portable' value='1' $selected>&nbsp;&nbsp;Téléphone portable");
               ?>
            </font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_num_ine) && $cur_num_ine==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='num_ine' value='1' $selected>&nbsp;&nbsp;Numéro INE");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Options</font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'><b>Adresse postale :</font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_mode_adresse))
                  {
                     switch($cur_mode_adresse)
                     {
                        case   0   :   $selected_0="checked";
                                    $selected_1="";
                                    break;
                        case   1   :   $selected_1="checked";
                                    $selected_0="";
                                    break;
                        default   :   $selected_0="checked";
                                    $selected_1="";
                                    break;
                     }
                  }
                  else
                  {
                     $selected_0="checked";
                     $selected_1="";
                  }

                  print("<input type='radio' name='mode_adresse' value='0' $selected_0>&nbsp;&nbsp;Colonne unique");
               ?>
            </font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  print("<input type='radio' name='mode_adresse' value='1' $selected_1>&nbsp;&nbsp;Plusieurs colonnes");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_page' colspan='4'></td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' rowspan='2'>
            <font class='Texte_menu'><b>Cursus</font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_cursus))
                  {
                     switch($cur_cursus)
                     {
                        case   0   :   $selected_0="checked";
                                    $selected_1=$selected_2="";
                                    break;
                        case   1   :   $selected_1="checked";
                                    $selected_0=$selected_2="";
                                    break;
                        case   2   :   $selected_2="checked";
                                    $selected_0=$selected_1="";
                                    break;
                        default   :   $selected_0="checked";
                                    $selected_1=$selected_2="";
                                    break;
                     }
                  }
                  else
                  {
                     $selected_0="checked";
                     $selected_1=$selected_2="";
                  }

                  print("<input type='radio' style='margin-right:8px;' name='cursus' value='0' $selected_0>Ne rien inclure");
               ?>
            </font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  print("<input type='radio' name='cursus' value='1' $selected_1>&nbsp;&nbsp;Dernière année du cursus");
               ?>
            </font>
         </td>
         <td class='td-droite fond_menu'>
            <font class='Texte_menu'>
               <?php
                  print("<input type='radio' name='cursus' value='2' $selected_2>&nbsp;&nbsp;Cursus complet");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' colspan='3'>
            <font class='Texte_menu'><i>Attention : l'extraction du cursus peut prendre du temps</i></font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Affichage du cursus</font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_aff_cursus) && $cur_aff_cursus==1)
                  {
                     $selected_1="checked";
                     $selected_0="";
                  }
                  else
                  {
                     $selected_0="checked";
                     $selected_1="";
                  }

                  print("<input type='radio' style='margin-right:8px;' name='aff_cursus' value='0' $selected_0>Colonnes distinctes (une par information) et lignes multiples");
               ?>
            </font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  print("<input type='radio' name='aff_cursus' value='1' $selected_1>&nbsp;&nbsp;Regrouper en une seule ligne et colonne");
               ?>
            </font>
         </td>
         <td class='td-droite fond_menu'></td>
      </tr>
      <tr>
         <td class='td-gauche fond_page' colspan='4'></td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu'>
            <font class='Texte_menu'><b>Inclure les réponses des candidats<br>aux éléments du Constructeur de Dossier ?</font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_dossier) && $cur_dossier=="1")
                  {
                     $yes_checked="checked";
                     $no_checked="";
                  }                  
                  else
                  {
                     $yes_checked="";
                     $no_checked="checked";
                  }

                  print("<input type='radio' style='margin-right:8px;' name='dossier' value='1' $yes_checked>Oui");
               ?>
            </font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  print("<input type='radio' style='margin-right:8px;' name='dossier' value='0' $no_checked>Non");
               ?>
            </font>
         </td>
         <td class='td-droite fond_menu'></td>
      </tr>
      <tr>
         <td class='td-gauche fond_page' colspan='4'></td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' rowspan='2'>
            <font class='Texte_menu'><b>Informations sur les candidatures</font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_statut) && $cur_statut==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='statut' value='1' $selected>&nbsp;&nbsp;Recevabilité");
               ?>
            </font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_decision) && $cur_decision==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='decision' value='1' $selected>&nbsp;&nbsp;Décision rendue");
               ?>
            </font>
         </td>
         <td class='td-droite fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_motivation) && $cur_motivation==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='motivation' value='1' $selected>&nbsp;&nbsp;Motivation (recevabilité ou commission)");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_entretien) && $cur_entretien==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='entretien' value='1' $selected>&nbsp;&nbsp;Entretien (date, heure, lieu)");
               ?>
            </font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_frais) && $cur_frais==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='statut_frais' value='1' $selected>&nbsp;&nbsp;Statut des frais de dossiers");
               ?>
            </font>
         </td>
	 <td class='td-droite fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_ordre_voeu) && $cur_ordre_voeu==1)
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='ordre_voeu' value='1' $selected>&nbsp;&nbsp;Ordre du voeu");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-gauche fond_page' colspan='2'></td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' rowspan='2'>
            <font class='Texte_menu'><b>Statut de la recevabilité : </b><br><i>Si aucun critère : recevables uniquement</i></font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_statut_prec) && array_key_exists($__PREC_NON_TRAITEE, $cur_statut_prec))
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='statut_prec[$__PREC_NON_TRAITEE]' value='$__PREC_NON_TRAITEE' $selected>&nbsp;&nbsp;Non traitée");
               ?>
            </font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_statut_prec) && array_key_exists($__PREC_PLEIN_DROIT, $cur_statut_prec))
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='statut_prec[$__PREC_PLEIN_DROIT]' value='$__PREC_PLEIN_DROIT' $selected>&nbsp;&nbsp;Plein droit");
               ?>
            </font>
         </td>
         <td class='td-droite fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_statut_prec) && array_key_exists($__PREC_EN_ATTENTE, $cur_statut_prec))
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='statut_prec[$__PREC_EN_ATTENTE]' value='$__PREC_EN_ATTENTE' $selected>&nbsp;&nbsp;En attente");
               ?>
            </font>
         </td>
      </tr>
      <tr>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_statut_prec) && array_key_exists($__PREC_RECEVABLE, $cur_statut_prec))
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='statut_prec[$__PREC_RECEVABLE]' value='$__PREC_RECEVABLE' $selected>&nbsp;&nbsp;Recevable");
               ?>
            </font>
         </td>
         <td class='td-milieu fond_menu'>
            <font class='Texte_menu'>
               <?php
                  if(isset($cur_statut_prec) && array_key_exists($__PREC_NON_RECEVABLE, $cur_statut_prec))
                     $selected="checked";
                  else
                     $selected="";

                  print("<input type='checkbox' name='statut_prec[$__PREC_NON_RECEVABLE]' value='$__PREC_NON_RECEVABLE' $selected>&nbsp;&nbsp;Non recevable");
               ?>
            </font>
         </td>
         <td class='td-gauche fond_menu'></td>
      </tr>
      <?php
         $result=db_query($dbr, "SELECT $_DBC_decisions_id, $_DBC_decisions_texte FROM $_DB_decisions
                                    WHERE $_DBC_decisions_id IN (SELECT $_DBC_decisions_comp_dec_id FROM $_DB_decisions_comp
                                                                  WHERE $_DBC_decisions_comp_comp_id='$_SESSION[comp_id]')
                                 ORDER BY $_DBC_decisions_texte");

         $rows=db_num_rows($result);

         if($rows)
         {
            $rowspan=($rows%3) ? ($rows/3)+1 : ($rows/3);
      ?>

      <tr>
         <td class='td-gauche fond_page' colspan='4'></td>
      </tr>
      <tr>
         <td class='td-gauche fond_menu' rowspan='<?php echo $rowspan; ?>'>
            <font class='Texte_menu'>
               <b>Décisions à inclure : </b>
               <br><i>Si aucun critère = toutes les décisions</i>
            </font>
         </td>
         <?php
            for($i=0; $i<$rows; $i++)
            {
               list($dec_id, $dec_texte)=db_fetch_row($result, $i);

               // Cas particulier pour la première ligne
               if(!($i%3) && $i>=3)
                  print("<tr>");

               $selected=(isset($cur_dec) && array_key_exists($dec_id, $cur_dec)) ? "checked" : "";

               print("<td class='td-milieu fond_menu'>
                        <font class='Texte_menu'>
                           <input type='checkbox' name='dec[$dec_id]' value='$dec_id' $selected>&nbsp;&nbsp;$dec_texte
                        </font>
                     </td>\n");

               if($i%3 == 2)
                  print("</tr>");
            }

            // fermeture propre du tableau
            for($complement=0; $complement<($i%3); $complement++)
               print("<td class='td-milieu fond_menu'></td>\n");
         }

         db_close($dbr);
      ?>
      </table>
   </div>

   <div class='centered_icons_box'>
      <a href='masse.php' target='_self' class='lien2'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour au menu précédent' border='0'></a>
      <input type='image' src='<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>' alt='Valider' name='Valider' value='Valider'>
      </form>
   </div>
</div>
<?php
   pied_de_page();
?>
</body></html>
