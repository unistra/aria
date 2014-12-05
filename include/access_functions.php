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

// FONCTIONS D'ACCES AUX DONNEES DES DIFFERENTES BASES
// Ces fonctions permettent d'éviter de réécrire toujours les mêmes requêtes / boucles

// PRE-REQUIS POUR CES FONCTIONS : LES FICHIERS "db.php" ET "vars.php" DOIVENT DEJA ETRE INCLUS

// ============================================================
//             Chargement de la configuration
// ============================================================

function __get_config($dbr)
{
   $res_config=db_query($dbr, "SELECT $GLOBALS[_DBC_systeme_titre_html], $GLOBALS[_DBC_systeme_titre_page], $GLOBALS[_DBC_systeme_ville],
                                      $GLOBALS[_DBC_systeme_url_candidat], $GLOBALS[_DBC_systeme_url_gestion], $GLOBALS[_DBC_systeme_meta], $GLOBALS[_DBC_systeme_admin],
                                      $GLOBALS[_DBC_systeme_signature_courriels], $GLOBALS[_DBC_systeme_signature_admin], $GLOBALS[_DBC_systeme_courriel_admin],
                                      $GLOBALS[_DBC_systeme_info_liberte], $GLOBALS[_DBC_systeme_limite_periode], $GLOBALS[_DBC_systeme_limite_masse],
                                      $GLOBALS[_DBC_systeme_defaut_decision], $GLOBALS[_DBC_systeme_defaut_motifs], $GLOBALS[_DBC_systeme_max_rappels],
                                      $GLOBALS[_DBC_systeme_rappel_delai_sup], $GLOBALS[_DBC_systeme_debug], $GLOBALS[_DBC_systeme_debug_rappel_id],
                                      $GLOBALS[_DBC_systeme_debug_cursus], $GLOBALS[_DBC_systeme_debug_statut_prec], $GLOBALS[_DBC_systeme_debug_lock], 
                                      $GLOBALS[_DBC_systeme_debug_enregistrement], $GLOBALS[_DBC_systeme_debug_sujet], $GLOBALS[_DBC_systeme_erreur_sujet],
                                      $GLOBALS[_DBC_systeme_arg_key], $GLOBALS[_DBC_systeme_assistance], $GLOBALS[_DBC_systeme_ldap_actif], 
                                      $GLOBALS[_DBC_systeme_ldap_host], $GLOBALS[_DBC_systeme_ldap_port], $GLOBALS[_DBC_systeme_ldap_proto], 
                                      $GLOBALS[_DBC_systeme_ldap_id], $GLOBALS[_DBC_systeme_ldap_pass], $GLOBALS[_DBC_systeme_ldap_basedn],
                                      $GLOBALS[_DBC_systeme_ldap_attr_login], $GLOBALS[_DBC_systeme_ldap_attr_nom],
                                      $GLOBALS[_DBC_systeme_ldap_attr_prenom], $GLOBALS[_DBC_systeme_ldap_attr_mail],
                                      $GLOBALS[_DBC_systeme_courriel_support], $GLOBALS[_DBC_systeme_courriel_noreply]
                               FROM $GLOBALS[_DB_systeme]");

   if(!db_num_rows($res_config))
      return FALSE;
   else
   {
      $_SESSION["config"]=array();

      list($_SESSION["config"]["__TITRE_HTML"],  $_SESSION["config"]["__TITRE_PAGE"], $_SESSION["config"]["__VILLE"], 
           $_SESSION["config"]["__URL_CANDIDAT"],  $_SESSION["config"]["__URL_GESTION"], $_SESSION["config"]["__META"], $_SESSION["config"]["__NOM_ADMIN"],
           $_SESSION["config"]["__SIGNATURE_COURRIELS"], $_SESSION["config"]["__SIGNATURE_ADMIN"], $_SESSION["config"]["__EMAIL_ADMIN"],
           $_SESSION["config"]["__INFORMATIQUE_ET_LIBERTES"], $_SESSION["config"]["__MOIS_LIMITE_CANDIDATURE"], $_SESSION["config"]["__MAX_CAND_MASSE"],
           $_SESSION["config"]["__DEFAUT_DECISIONS"], $_SESSION["config"]["__DEFAUT_MOTIFS"], $_SESSION["config"]["__MAX_RAPPELS"],
           $_SESSION["config"]["__AJOUT_VERROUILLAGE_JOURS"], $_SESSION["config"]["__DEBUG"], $_SESSION["config"]["__DEBUG_RAPPEL_IDENTIFIANTS"],
           $_SESSION["config"]["__DEBUG_CURSUS"], $_SESSION["config"]["__DEBUG_STATUT_PREC"], $_SESSION["config"]["__DEBUG_LOCK"],
           $_SESSION["config"]["__DEBUG_ENREGISTREMENT"], $_SESSION["config"]["__DEBUG_SUJET"], $_SESSION["config"]["__ERREUR_SUJET"],
           $_SESSION["config"]["arg_key"], $_SESSION["config"]["__ASSISTANCE"], $_SESSION["config"]["__LDAP_ACTIF"], 
           $_SESSION["config"]["__LDAP_HOST"], $_SESSION["config"]["__LDAP_PORT"], $_SESSION["config"]["__LDAP_PROTO"], 
           $_SESSION["config"]["__LDAP_ID"], $_SESSION["config"]["__LDAP_PASS"], $_SESSION["config"]["__LDAP_BASEDN"],
           $_SESSION["config"]["__LDAP_ATTR_LOGIN"], $_SESSION["config"]["__LDAP_ATTR_NOM"],
           $_SESSION["config"]["__LDAP_ATTR_PRENOM"], $_SESSION["config"]["__LDAP_ATTR_MAIL"],
           $_SESSION["config"]["__EMAIL_SUPPORT"], $_SESSION["config"]["__EMAIL_NOREPLY"])=db_fetch_row($res_config, 0);

      // Initialisation des variables globales depuis $_SESSION["config"] (en attendant de modifier le code ?)
      $GLOBALS["__TITRE_HTML"]=array_key_exists("__TITRE_HTML", $_SESSION["config"]) ? $_SESSION["config"]["__TITRE_HTML"] : "";
      $GLOBALS["__TITRE_PAGE"]=array_key_exists("__TITRE_PAGE", $_SESSION["config"]) ? $_SESSION["config"]["__TITRE_PAGE"] : "";
      $GLOBALS["__VILLE"]=array_key_exists("__VILLE", $_SESSION["config"]) ? $_SESSION["config"]["__VILLE"]  : "";
      $GLOBALS["__URL_CANDIDAT"]=array_key_exists("__URL_CANDIDAT", $_SESSION["config"]) ? $_SESSION["config"]["__URL_CANDIDAT"] : "http://" . $_SERVER["SERVER_NAME"] . str_replace("configuration/aria_config.php", "", str_replace($_SERVER["DOCUMENT_ROOT"], "", __FILE__));
      $GLOBALS["__URL_GESTION"]=array_key_exists("__URL_GESTION", $_SESSION["config"]) ? $_SESSION["config"]["__URL_GESTION"] : "http://" . $_SERVER["SERVER_NAME"] . str_replace("configuration/aria_config.php", "gestion/", str_replace($_SERVER["DOCUMENT_ROOT"], "", __FILE__));
      $GLOBALS["__META"]=array_key_exists("__META", $_SESSION["config"]) ? $_SESSION["config"]["__META"] : "";
      $GLOBALS["__NOM_ADMIN"]=array_key_exists("__NOM_ADMIN", $_SESSION["config"]) ? $_SESSION["config"]["__NOM_ADMIN"] : "";
      $GLOBALS["__SIGNATURE_COURRIELS"]=array_key_exists("__SIGNATURE_COURRIELS", $_SESSION["config"]) ? $_SESSION["config"]["__SIGNATURE_COURRIELS"] : "";
      $GLOBALS["__SIGNATURE_ADMIN"]=array_key_exists("__SIGNATURE_ADMIN", $_SESSION["config"]) ? $_SESSION["config"]["__SIGNATURE_ADMIN"]  : "";
      $GLOBALS["__EMAIL_ADMIN"]=array_key_exists("__EMAIL_ADMIN", $_SESSION["config"]) ? $_SESSION["config"]["__EMAIL_ADMIN"] : "";
      $GLOBALS["__EMAIL_SUPPORT"]=array_key_exists("__EMAIL_SUPPORT", $_SESSION["config"]) ? $_SESSION["config"]["__EMAIL_SUPPORT"] : "";
      $GLOBALS["__EMAIL_NOREPLY"]=array_key_exists("__EMAIL_NOREPLY", $_SESSION["config"]) ? $_SESSION["config"]["__EMAIL_NOREPLY"] : "";
      $GLOBALS["__INFORMATIQUE_ET_LIBERTES"]=array_key_exists("__INFORMATIQUE_ET_LIBERTES", $_SESSION["config"]) ? $_SESSION["config"]["__INFORMATIQUE_ET_LIBERTES"] : "Les informations vous concernant font l''objet d''un traitement informatique destiné à gérer les précandidatures en ligne. L''unique destinataire des données est (Nom de l''établissement). Conformément à la loi ''Informatique et Libertés'' du 6 janvier 1978, vous bénéficiez d''un droit d''accès et de rectification à ces informations. Si vous souhaitez exercer ce droit et obtenir communication de ces dernières, veuillez vous adresser à (Nom de l''administrateur) (par courriel : adresse@administrateur). Vous pouvez également, pour des motifs légitimes, vous opposer au traitement des données vous concernant.";
      $GLOBALS["__MOIS_LIMITE_CANDIDATURE"]=array_key_exists("__MOIS_LIMITE_CANDIDATURE", $_SESSION["config"]) ? $_SESSION["config"]["__MOIS_LIMITE_CANDIDATURE"]  : "03";
      $GLOBALS["__MAX_CAND_MASSE"]=array_key_exists("__MAX_CAND_MASSE", $_SESSION["config"]) ? $_SESSION["config"]["__MAX_CAND_MASSE"] : "40";
      $GLOBALS["__DEFAUT_DECISIONS"]=array_key_exists("__DEFAUT_DECISIONS", $_SESSION["config"]) ? $_SESSION["config"]["__DEFAUT_DECISIONS"] : "t";
      $GLOBALS["__DEFAUT_MOTIFS"]=array_key_exists("__DEFAUT_MOTIFS", $_SESSION["config"]) ? $_SESSION["config"]["__DEFAUT_MOTIFS"] : "t";
      $GLOBALS["__MAX_RAPPELS"]=array_key_exists("__MAX_RAPPELS", $_SESSION["config"]) ? $_SESSION["config"]["__MAX_RAPPELS"] : "3";
      $GLOBALS["__AJOUT_VERROUILLAGE_JOURS"]=array_key_exists("__AJOUT_VERROUILLAGE_JOURS", $_SESSION["config"]) ? $_SESSION["config"]["__AJOUT_VERROUILLAGE_JOURS"] : "2";
      $GLOBALS["__DEBUG"]=array_key_exists("__DEBUG", $_SESSION["config"]) ? $_SESSION["config"]["__DEBUG"] : "t";
      $GLOBALS["__DEBUG_RAPPEL_IDENTIFIANTS"]=array_key_exists("__DEBUG_RAPPEL_IDENTIFIANTS", $_SESSION["config"]) ? $_SESSION["config"]["__DEBUG_RAPPEL_IDENTIFIANTS"] : "f";
      $GLOBALS["__DEBUG_CURSUS"]=array_key_exists("__DEBUG_CURSUS", $_SESSION["config"]) ? $_SESSION["config"]["__DEBUG_CURSUS"] : "f";
      $GLOBALS["__DEBUG_STATUT_PREC"]=array_key_exists("__DEBUG_STATUT_PREC", $_SESSION["config"]) ? $_SESSION["config"]["__DEBUG_STATUT_PREC"] : "f";
      $GLOBALS["__DEBUG_LOCK"]=array_key_exists("__DEBUG_LOCK", $_SESSION["config"]) ? $_SESSION["config"]["__DEBUG_LOCK"] : "f";
      $GLOBALS["__DEBUG_ENREGISTREMENT"]=array_key_exists("__DEBUG_ENREGISTREMENT", $_SESSION["config"]) ? $_SESSION["config"]["__DEBUG_ENREGISTREMENT"] : "t";
      $GLOBALS["__DEBUG_SUJET"]=array_key_exists("__DEBUG_SUJET", $_SESSION["config"]) ? $_SESSION["config"]["__DEBUG_SUJET"] : "[DBG - ARIA]";
      $GLOBALS["__ERREUR_SUJET"]=array_key_exists("__ERREUR_SUJET", $_SESSION["config"]) ? $_SESSION["config"]["__ERREUR_SUJET"] : "[Erreur ARIA]";
      $GLOBALS["__ASSISTANCE"]=array_key_exists("__ASSISTANCE", $_SESSION["config"]) ? $_SESSION["config"]["__ASSISTANCE"] : "t";
      $GLOBALS["__LDAP_ACTIF"]=array_key_exists("__LDAP_ACTIF", $_SESSION["config"]) ? $_SESSION["config"]["__LDAP_ACTIF"] : "f";      
      $GLOBALS["__LDAP_HOST"]=array_key_exists("__LDAP_HOST", $_SESSION["config"]) ? $_SESSION["config"]["__LDAP_HOST"] : "";
      $GLOBALS["__LDAP_PORT"]=array_key_exists("__LDAP_PORT", $_SESSION["config"]) ? $_SESSION["config"]["__LDAP_PORT"] : "389";
      $GLOBALS["__LDAP_PROTO"]=array_key_exists("__LDAP_PROTO", $_SESSION["config"]) ? $_SESSION["config"]["__LDAP_PROTO"] : "3";
      $GLOBALS["__LDAP_ID"]=array_key_exists("__LDAP_ID", $_SESSION["config"]) ? $_SESSION["config"]["__LDAP_ID"] : "";
      $GLOBALS["__LDAP_PASS"]=array_key_exists("__LDAP_PASS", $_SESSION["config"]) ? $_SESSION["config"]["__LDAP_PASS"] : "";
      $GLOBALS["__LDAP_BASEDN"]=array_key_exists("__LDAP_BASEDN", $_SESSION["config"]) ? $_SESSION["config"]["__LDAP_BASEDN"] : "";
      $GLOBALS["__LDAP_ATTR_LOGIN"]=array_key_exists("__LDAP_ATTR_LOGIN", $_SESSION["config"]) ? $_SESSION["config"]["__LDAP_ATTR_LOGIN"] : "";
      $GLOBALS["__LDAP_ATTR_NOM"]=array_key_exists("__LDAP_ATTR_NOM", $_SESSION["config"]) ? $_SESSION["config"]["__LDAP_ATTR_NOM"] : "";
      $GLOBALS["__LDAP_ATTR_PRENOM"]=array_key_exists("__LDAP_ATTR_PRENOM", $_SESSION["config"]) ? $_SESSION["config"]["__LDAP_ATTR_PRENOM"] : "";
      // $GLOBALS["__LDAP_ATTR_PASS"]=array_key_exists("__LDAP_ATTR_PASS", $_SESSION["config"]) ? $_SESSION["config"]["__LDAP_ATTR_PASS"] : "";
      $GLOBALS["__LDAP_ATTR_MAIL"]=array_key_exists("__LDAP_ATTR_MAIL", $_SESSION["config"]) ? $_SESSION["config"]["__LDAP_ATTR_MAIL"] : "";
      
      
      if(isset($_SESSION["config"]["arg_key"]) && $_SESSION["config"]["arg_key"]!="")
         $GLOBALS["arg_key"]=$_SESSION["config"];
      else
      {
         srand((double)microtime()*1000000);
         $GLOBALS["arg_key"]=$_SESSION["config"]["arg_key"]=substr(md5(rand(0,9999)), 12, 8);
      }

      // ===============================================================================
      // Période "absolue" = année universitaire pour les candidats actuels
      // ===============================================================================

      $_SESSION["config"]["__PERIODE_ABSOLUE"]=$GLOBALS["__PERIODE_ABSOLUE"]=(date('n') < $GLOBALS["__MOIS_LIMITE_CANDIDATURE"]) ? date('Y')-1 : date('Y');
      
      if(isset($_SESSION["current_user_periode"]) && ctype_digit($_SESSION["current_user_periode"]))
         $_SESSION["config"]["__PERIODE"]=$GLOBALS["__PERIODE"]=$_SESSION["current_user_periode"];
      else
         $_SESSION["config"]["__PERIODE"]=$GLOBALS["__PERIODE"]=$GLOBALS["__PERIODE_ABSOLUE"];

      // ===============================================================================
      // Construction de l'arborescence en fonction des paramètres récupérés
      // ===============================================================================

      // Les chemins absolus ne doivent être utilisés que pour la lecture/écriture de fichiers (i.e : pas les php classiques)

      // Feuilles de styles et autres fichiers statiques
      // (Disparition de "$__CSS_DIR")
      $GLOBALS["__STATIC_DIR"]=$_SESSION["config"]["__STATIC_DIR"]="$GLOBALS[__MOD_DIR]/static";

      // Documentation en ligne pour les candidats (différente de l'aide contextuelle coté gestion)
      $GLOBALS["__DOC_DIR"]=$_SESSION["config"]["__DOC_DIR"]="$GLOBALS[__MOD_DIR]/doc";

      // Gestion et candidats
      $GLOBALS["__CAND_DIR"]=$_SESSION["config"]["__CAND_DIR"]="$GLOBALS[__MOD_DIR]/interface";
      $GLOBALS["__GESTION_DIR"]=$_SESSION["config"]["__GESTION_DIR"]="$GLOBALS[__MOD_DIR]/gestion";

      // Aide contextuelle pour la gestion
      $GLOBALS["__GESTION_AIDE_DIR"]=$_SESSION["config"]["__GESTION_AIDE_DIR"]="$GLOBALS[__GESTION_DIR]/aide";

      // Fichiers communs
      $GLOBALS["__INCLUDE_DIR"]=$_SESSION["config"]["__INCLUDE_DIR"]="$GLOBALS[__MOD_DIR]/include";

      // Librairie FPDF
      $GLOBALS["__FPDF_DIR"]=$_SESSION["config"]["__FPDF_DIR"]="$GLOBALS[__INCLUDE_DIR]/fpdf";

      // Fichiers Candidats
      $GLOBALS["__CAND_COMP_STOCKAGE_DIR"]=$_SESSION["config"]["__CAND_COMP_STOCKAGE_DIR"]="$GLOBALS[__CAND_DIR]/fichiers/composantes";

      // Module de messagerie / Candidats
      $GLOBALS["__CAND_MSG_DIR"]=$_SESSION["config"]["__CAND_MSG_DIR"]="$GLOBALS[__CAND_DIR]/messagerie";

      // Messagerie Candidats / stockage des messages
      $GLOBALS["__CAND_MSG_STOCKAGE_DIR"]=$_SESSION["config"]["__CAND_MSG_STOCKAGE_DIR"]="$GLOBALS[__CAND_DIR]/fichiers/messagerie";

      // Système d'assistance pour les candidats
      $GLOBALS["__CAND_ASSISTANCE_DIR"]=$_SESSION["config"]["__CAND_ASSISTANCE_DIR"]="$GLOBALS[__CAND_DIR]/assistance";

      // Fichiers des composantes (gestion)
      $GLOBALS["__GESTION_COMP_STOCKAGE_DIR"]=$_SESSION["config"]["__GESTION_COMP_STOCKAGE_DIR"]="$GLOBALS[__GESTION_DIR]/fichiers/composantes";

      // Fichiers publics
      $GLOBALS["__PUBLIC_DIR"]=$_SESSION["config"]["__PUBLIC_DIR"]="$GLOBALS[__MOD_DIR]/fichiers/composantes"; // Réservé aux fichiers téléchargeables : justificatifs

      // Messagerie Gestion
      $GLOBALS["__GESTION_MSG_DIR"]=$_SESSION["config"]["__GESTION_MSG_DIR"]="$GLOBALS[__GESTION_DIR]/messagerie";
      $GLOBALS["__GESTION_MSG_STOCKAGE_DIR"]=$_SESSION["config"]["__GESTION_MSG_STOCKAGE_DIR"]="$GLOBALS[__GESTION_DIR]/fichiers/messagerie";

      // Modules (plugins) additionnels
      $GLOBALS["__PLUGINS_DIR"]=$_SESSION["config"]["__PLUGINS_DIR"]="$GLOBALS[__GESTION_DIR]/admin/modules";

      // Images, icônes et logo par défaut
      // __IMG_DIR est particulière : si une université dispose d'autres icônes dans un autre répertoire (cf Menu Administration / Universités),
      // alors ce répertoire est prioritaire sur celui-ci.

      $GLOBALS["__IMG_DIR"]=$_SESSION["config"]["__IMG_DIR"]=isset($_SESSION["img_dir"]) ? "$GLOBALS[__MOD_DIR]/images/$_SESSION[img_dir]" : "$GLOBALS[__MOD_DIR]/images";
      $GLOBALS["__ICON_DIR"]=$_SESSION["config"]["__ICON_DIR"]="$GLOBALS[__IMG_DIR]/icones";
      $GLOBALS["__LOGO_DEFAUT"]=$_SESSION["config"]["__LOGO_DEFAUT"]="$GLOBALS[__ICON_DIR]/logo.png";

      // ==================================================================================
      //                   CHEMINS ABSOLUS POUR LES REPERTOIRES PRECEDENTS
      //      Automatiquement générés - aucune modification ne devrait être nécessaire
      // ==================================================================================

      $GLOBALS["__MOD_DIR_ABS"]=$_SESSION["config"]["__MOD_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__MOD_DIR]";
      $GLOBALS["__STATIC_DIR_ABS"]=$_SESSION["config"]["__STATIC_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__STATIC_DIR]";
      $GLOBALS["__INCLUDE_DIR_ABS"]=$_SESSION["config"]["__INCLUDE_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__INCLUDE_DIR]";
      $GLOBALS["__FPDF_DIR_ABS"]=$_SESSION["config"]["__FPDF_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__FPDF_DIR]";
      $GLOBALS["__CAND_COMP_STOCKAGE_DIR_ABS"]=$_SESSION["config"]["__CAND_COMP_STOCKAGE_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__CAND_COMP_STOCKAGE_DIR]";
      $GLOBALS["__CAND_MSG_STOCKAGE_DIR_ABS"]=$_SESSION["config"]["__CAND_MSG_STOCKAGE_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__CAND_MSG_STOCKAGE_DIR]";
      $GLOBALS["__CAND_ASSISTANCE_DIR_ABS"]=$_SESSION["config"]["__CAND_ASSISTANCE_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__CAND_ASSISTANCE_DIR]";
      $GLOBALS["__PUBLIC_DIR_ABS"]=$_SESSION["config"]["__PUBLIC_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__PUBLIC_DIR]";
      $GLOBALS["__GESTION_COMP_STOCKAGE_DIR_ABS"]=$_SESSION["config"]["__GESTION_COMP_STOCKAGE_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__GESTION_COMP_STOCKAGE_DIR]";
      $GLOBALS["__GESTION_MSG_STOCKAGE_DIR_ABS"]=$_SESSION["config"]["__GESTION_MSG_STOCKAGE_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__GESTION_MSG_STOCKAGE_DIR]";
      $GLOBALS["__IMG_DIR_ABS"]=$_SESSION["config"]["__IMG_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__IMG_DIR]";
      $GLOBALS["__ICON_DIR_ABS"]=$_SESSION["config"]["__ICON_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__ICON_DIR]";
      $GLOBALS["__LOGO_DEFAUT_ABS"]=$_SESSION["config"]["__LOGO_DEFAUT_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__LOGO_DEFAUT]";
      $GLOBALS["__PLUGINS_DIR_ABS"]=$_SESSION["config"]["__PLUGINS_DIR_ABS"]="$GLOBALS[__ROOT_DIR]/$GLOBALS[__PLUGINS_DIR]";

      // ===============================================================================

      // Vérification : si un seul des paramètres est vide, on avertit l'utilisateur
      $vide=FALSE;

      // Certains paramètres peuvent rester vides, on avertit l'utilisateur pour les autres

      $tab_non_vides=array("__TITRE_HTML","__TITRE_PAGE","__VILLE","__URL_CANDIDAT","__URL_GESTION","__NOM_ADMIN","__SIGNATURE_COURRIELS","__SIGNATURE_ADMIN",
                           "__EMAIL_ADMIN","__INFORMATIQUE_ET_LIBERTES","__MOIS_LIMITE_CANDIDATURE","__MAX_CAND_MASSE","__DEFAUT_DECISIONS","__DEFAUT_MOTIFS",
                           "__MAX_RAPPELS","__AJOUT_VERROUILLAGE_JOURS","__DEBUG","__DEBUG_RAPPEL_IDENTIFIANTS","__DEBUG_CURSUS","__DEBUG_STATUT_PREC", 
                           "__DEBUG_LOCK","__DEBUG_ENREGISTREMENT", "__EMAIL_SUPPORT", "__EMAIL_NOREPLY");

      foreach($tab_non_vides as $value)
         $vide=$vide || (trim($GLOBALS["$value"])=="");

      if($vide===FALSE)
         return 0;
      else
         return -1;
   }
}

// ============================================================
// Fonctions utilisées dans les lettres et dans les messages
// ============================================================

// Extraction des information du candidat

function __get_infos_candidat($dbr, $id)
{
  $result=db_query($dbr,"SELECT $GLOBALS[_DBC_candidat_civilite], $GLOBALS[_DBC_candidat_nom], $GLOBALS[_DBC_candidat_prenom],
                      $GLOBALS[_DBC_candidat_date_naissance], $GLOBALS[_DBC_candidat_adresse_1], $GLOBALS[_DBC_candidat_adresse_2], $GLOBALS[_DBC_candidat_adresse_3],
                      $GLOBALS[_DBC_candidat_lieu_naissance], 
                      CASE WHEN $GLOBALS[_DBC_candidat_nationalite] IN (SELECT $GLOBALS[_DBC_pays_nat_ii_iso] FROM $GLOBALS[_DB_pays_nat_ii] WHERE $GLOBALS[_DBC_pays_nat_ii_iso]=$GLOBALS[_DBC_candidat_nationalite]) 
                          THEN (SELECT $GLOBALS[_DBC_pays_nat_ii_nat] FROM $GLOBALS[_DB_pays_nat_ii] WHERE $GLOBALS[_DBC_pays_nat_ii_iso]=$GLOBALS[_DBC_candidat_nationalite])
                          ELSE '' END as nationalite,
                      $GLOBALS[_DBC_candidat_telephone], 
                      CASE WHEN $GLOBALS[_DBC_candidat_pays_naissance] IN (SELECT $GLOBALS[_DBC_pays_nat_ii_iso] FROM $GLOBALS[_DB_pays_nat_ii] WHERE $GLOBALS[_DBC_pays_nat_ii_iso]=$GLOBALS[_DBC_candidat_pays_naissance]) 
                            THEN (SELECT $GLOBALS[_DBC_pays_nat_ii_pays] FROM $GLOBALS[_DB_pays_nat_ii] WHERE $GLOBALS[_DBC_pays_nat_ii_iso]=$GLOBALS[_DBC_candidat_pays_naissance])
                            ELSE '' END as pays_naissance,
                      $GLOBALS[_DBC_candidat_adresse_cp], $GLOBALS[_DBC_candidat_adresse_ville],
                      CASE WHEN $GLOBALS[_DBC_candidat_adresse_pays] IN (SELECT $GLOBALS[_DBC_pays_nat_ii_iso] FROM $GLOBALS[_DB_pays_nat_ii] WHERE $GLOBALS[_DBC_pays_nat_ii_iso]=$GLOBALS[_DBC_candidat_adresse_pays]) 
                          THEN (SELECT $GLOBALS[_DBC_pays_nat_ii_pays] FROM $GLOBALS[_DB_pays_nat_ii] WHERE $GLOBALS[_DBC_pays_nat_ii_iso]=$GLOBALS[_DBC_candidat_adresse_pays])
                          ELSE '' END as adresse_pays
                  FROM $GLOBALS[_DB_candidat] WHERE $GLOBALS[_DBC_candidat_id]='$id'");
  $rows=db_num_rows($result);

  if($rows)
  {
    // Tableau qui sera retourné par la fonction
    $candidat_array=array();

    list($candidat_array["civilite"],$candidat_array["nom"],$candidat_array["prenom"],$candidat_array["naissance_unix"], $adresse_1, $adresse_2, $adresse_3,
           $candidat_array["lieu_naissance"],$candidat_array["nationalite"],$candidat_array["telephone"],
           $candidat_array["pays_naissance"], $adr_cp, $adr_ville, $adr_pays)=db_fetch_row($result,0);

    db_free_result($result);

    if($candidat_array["civilite"]=="M")
      $candidat_array["civ_texte"]="Monsieur";
    elseif($candidat_array["civilite"]=="Mlle")
      $candidat_array["civ_texte"]="Mademoiselle";
    else
      $candidat_array["civ_texte"]="Madame";

    // Traitement des informations et stockage dans le tableau
    $candidat_array["naissance"]=date_fr("j/m/Y",$candidat_array["naissance_unix"]);

      $candidat_array["adr"]=$adresse_1;
      $candidat_array["adr"].=$adresse_2 != "" ? "\n".$adresse_2 : "";
      $candidat_array["adr"].=$adresse_3 != "" ? "\n".$adresse_3 : "";
      
      $candidat_array["adr_cp"]=$adr_cp;
      $candidat_array["adr_ville"]=$adr_ville;
      $candidat_array["adr_pays"]=$adr_pays;
      
    // Ville en majuscules, pays avec juste la première lettre
    $adr_ville=mb_strtoupper($adr_ville);
    // format inchangé pour le pays (majuscules)
    // $adr_pays=ucwords(mb_strtolower($adr_pays));

    $candidat_array["adresse"]=$candidat_array["adr"]."\n".$adr_cp." ".ucwords(mb_strtolower($adr_ville));

    if(strcasecmp($adr_pays, "france")) // candidat étranger : on précise le pays
      $candidat_array["adresse"].="\n".$adr_pays;

    // On a tout, on sort
    return $candidat_array;
  }
  else // Candidat non trouvé : on sort
  {
    db_free_result($result);
    return FALSE;
  }
}

// Extraction d'une candidature avec décision prise

function __get_candidature($dbr, $cand_id)
{
  $result=db_query($dbr,"SELECT $GLOBALS[_DBC_cand_id], $GLOBALS[_DBC_propspec_id], $GLOBALS[_DBC_annees_id],
                      $GLOBALS[_DBC_annees_annee], $GLOBALS[_DBC_mentions_nom], $GLOBALS[_DBC_specs_id],
                      $GLOBALS[_DBC_specs_nom], $GLOBALS[_DBC_specs_nom_court],
                      $GLOBALS[_DBC_cand_decision], $GLOBALS[_DBC_decisions_texte], $GLOBALS[_DBC_cand_motivation_decision],
                      $GLOBALS[_DBC_propspec_selective], $GLOBALS[_DBC_cand_transmission_dossier],
                      $GLOBALS[_DBC_cand_vap_flag], $GLOBALS[_DBC_cand_groupe_spec],
                      $GLOBALS[_DBC_cand_ordre_spec], $GLOBALS[_DBC_cand_recours], $GLOBALS[_DBC_propspec_resp],
                      $GLOBALS[_DBC_propspec_mailresp], $GLOBALS[_DBC_cand_liste_attente], $GLOBALS[_DBC_propspec_entretiens],
                      $GLOBALS[_DBC_cand_entretien_date], $GLOBALS[_DBC_cand_entretien_heure],
                      $GLOBALS[_DBC_cand_entretien_lieu], $GLOBALS[_DBC_cand_entretien_salle],
                      $GLOBALS[_DBC_propspec_finalite], $GLOBALS[_DBC_cand_date_decision],
                      $GLOBALS[_DBC_propspec_frais], $GLOBALS[_DBC_cand_talon_reponse],
                      $GLOBALS[_DBC_cand_session_id], $GLOBALS[_DBC_cand_lockdate], $GLOBALS[_DBC_cand_periode],
                      $GLOBALS[_DBC_composantes_id], $GLOBALS[_DBC_universites_id]
                    FROM  $GLOBALS[_DB_cand], $GLOBALS[_DB_annees], $GLOBALS[_DB_specs], $GLOBALS[_DB_propspec],
                        $GLOBALS[_DB_decisions], $GLOBALS[_DB_mentions], $GLOBALS[_DB_universites],
                        $GLOBALS[_DB_composantes]
                  WHERE $GLOBALS[_DBC_propspec_annee]=$GLOBALS[_DBC_annees_id]
                  AND $GLOBALS[_DBC_cand_decision]=$GLOBALS[_DBC_decisions_id]
                  AND $GLOBALS[_DBC_propspec_id]=$GLOBALS[_DBC_cand_propspec_id]
                  AND $GLOBALS[_DBC_propspec_id_spec]=$GLOBALS[_DBC_specs_id]
                  AND $GLOBALS[_DBC_specs_mention_id]=$GLOBALS[_DBC_mentions_id]
                  AND $GLOBALS[_DBC_propspec_comp_id]=$GLOBALS[_DBC_composantes_id]
                  AND $GLOBALS[_DBC_composantes_univ_id]=$GLOBALS[_DBC_universites_id]
                  AND $GLOBALS[_DBC_cand_id]='$cand_id'");
  $rows=db_num_rows($result);

  if($rows)
  {
    // Tableau qui sera retourné par la fonction
    $candidature_array=array();

    list($candidature_array["id"],
        $candidature_array["propspec_id"],
        $candidature_array["annee_id"],
        $candidature_array["annee"],
        $candidature_array["mention_nom"],
        $candidature_array["spec_id"],
        $candidature_array["spec_nom"],
        $candidature_array["spec_nom_court"],
        $candidature_array["decision"],
        $candidature_array["decision_txt"],
        $candidature_array["motivations_id"],
        $candidature_array["selective"],
        $candidature_array["transmission"],
        $candidature_array["vap"],
        $candidature_array["groupe_spec"],
        $candidature_array["ordre_spec"],
        $candidature_array["recours"],
        $candidature_array["responsable"],
        $candidature_array["responsable_email"],
        $candidature_array["rang_attente"],
        $candidature_array["entretiens"],
        $candidature_array["entretien_date_unix"],
        $candidature_array["entretien_heure"],
        $candidature_array["entretien_lieu"],
        $candidature_array["entretien_salle"],
        $candidature_array["finalite"],
        $candidature_array["date_decision_unix"],
        $candidature_array["frais"],
        $candidature_array["talon_reponse"],
        $candidature_array["session_id"],
        $candidature_array["lockdate"],
        $candidature_array["periode"],
        $candidature_array["comp_id"],
        $candidature_array["univ_id"])=db_fetch_row($result,0);

    db_free_result($result);

    $finalite=$candidature_array["finalite"];
    $candidature_array["nom_finalite"]=$GLOBALS["tab_finalite"][$finalite];
  
    // Texte de la formation
    if(!empty($candidature_array["annee"]))
      $candidature_array["texte_formation"]=trim("$candidature_array[annee] - $candidature_array[spec_nom] " . $GLOBALS["tab_finalite_lettres"][$finalite]);
    else
      $candidature_array["texte_formation"]=trim("$candidature_array[spec_nom] " . $GLOBALS["tab_finalite_lettres"][$finalite]);

    // ================================
    // TODO : DATES A VERIFIER
    // $candidature_array["session_commission"]=date_fr("j F Y",$candidature_array["session_commission_unix"]);
    $candidature_array["session_commission_unix"]=$candidature_array["date_decision_unix"];

    if(ctype_digit($candidature_array["session_commission_unix"]))
      $candidature_array["session_commission"]=date_fr("j F Y",$candidature_array["session_commission_unix"]);
    else
      $candidature_array["session_commission"]="";

    // ================================

    // Et de la décision
    if(ctype_digit($candidature_array["date_decision_unix"]))
      $candidature_array["date_decision"]=date_fr("j F Y",$candidature_array["date_decision_unix"]);
    else // ne dois normalement jamais être vide
      $candidature_array["date_decision"]="";

    // $candidature_array["date_commission"]=$candidature_array["date_decision"];

    // Entretien

    if($candidature_array["entretien_date_unix"]!="" && $candidature_array["entretien_date_unix"]!=0)
    {
      $candidature_array["entretien_date"]=date_fr("l jS F Y", $candidature_array["entretien_date_unix"]);

      $heure=date("H", $candidature_array["entretien_date_unix"]);
      $minute=date("i", $candidature_array["entretien_date_unix"]);

      if($heure!=0)
        $candidature_array["entretien_heure"]=$heure . "h$minute";
    }
    else
      $candidature_array["entretien_date"]="";

    // Traitement de la motivation : dépend de la décision
    $motivation_txt="";     

    $motif_array=explode("|", $candidature_array["motivations_id"]);

    $cnt=count($motif_array);

    if($cnt)
    {
      if($cnt>1)
        $candidature_array["motif_txt"]="les motifs suivants";
      else
        $candidature_array["motif_txt"]="le motif suivant";

      for($j=0; $j<$cnt; $j++)
      {
        $txt=$motif_array[$j];

        if(ctype_digit($txt)) // motif provenant de la table motifs_refus
        {
          $result2=db_query($dbr,"SELECT $GLOBALS[_DBC_motifs_refus_motif], $GLOBALS[_DBC_motifs_refus_motif_long]
                            FROM $GLOBALS[_DB_motifs_refus]
                          WHERE $GLOBALS[_DBC_motifs_refus_id]='$txt'");
          $rows2=db_num_rows($result2);

          if($rows2)
            list($txt,$txt_long)=db_fetch_row($result2,0);
          else
            $txt=$txt_long="";

          db_free_result($result2);
        }
        else // motif libre
        {
          // nettoyage
          $txt_long="";
          // $txt=str_replace("@","",$motif_array[$j]);
          $txt=preg_replace("/^@/","", $motif_array[$j]);
        }

        if(!empty($txt_long))
          $txt="$txt_long";

        if(!$j)
          $candidature_array["motivation"]="$txt";
        else
          $candidature_array["motivation"].="\n$txt";
      }
    }
    else
      $candidature_array["motivation"]=$candidature_array["motif_txt"]="";

    // Nom complet de la candidature
    if($candidature_array["annee"]=="")
      $candidature_array["nom_complet"]=trim("$candidature_array[spec_nom] $candidature_array[nom_finalite]");
    else
      $candidature_array["nom_complet"]=trim("$candidature_array[annee] - $candidature_array[spec_nom] $candidature_array[nom_finalite]");

    // On a tout, on sort
    return $candidature_array;
  }
  else // Candidature non trouvée : on sort
  {
    db_free_result($result);
    return FALSE;
  }
}

// Même chose avec les candidatures à choix multiples, à partir de l'ID de l'une d'entre elles
// TODO : généraliser l'appli avec cette fonction

function __get_candidatures_multiples($dbr, $cand_id)
{
  $result=db_query($dbr,"SELECT $GLOBALS[_DBC_cand_id], $GLOBALS[_DBC_propspec_id], $GLOBALS[_DBC_annees_id],
                      $GLOBALS[_DBC_annees_annee], $GLOBALS[_DBC_mentions_nom], $GLOBALS[_DBC_specs_id],
                      $GLOBALS[_DBC_specs_nom], $GLOBALS[_DBC_specs_nom_court],
                      $GLOBALS[_DBC_cand_decision], $GLOBALS[_DBC_decisions_texte], $GLOBALS[_DBC_cand_motivation_decision],
                      $GLOBALS[_DBC_propspec_selective], $GLOBALS[_DBC_cand_transmission_dossier],
                      $GLOBALS[_DBC_cand_vap_flag], $GLOBALS[_DBC_cand_groupe_spec],
                      $GLOBALS[_DBC_cand_ordre_spec], $GLOBALS[_DBC_cand_recours], $GLOBALS[_DBC_propspec_resp],
                      $GLOBALS[_DBC_propspec_mailresp], $GLOBALS[_DBC_cand_liste_attente], $GLOBALS[_DBC_propspec_entretiens],
                      $GLOBALS[_DBC_cand_entretien_date], $GLOBALS[_DBC_cand_entretien_heure],
                      $GLOBALS[_DBC_cand_entretien_lieu], $GLOBALS[_DBC_cand_entretien_salle],
                      $GLOBALS[_DBC_propspec_finalite], $GLOBALS[_DBC_cand_date_decision],
                      $GLOBALS[_DBC_propspec_frais], $GLOBALS[_DBC_cand_talon_reponse],
                      $GLOBALS[_DBC_cand_session_id], $GLOBALS[_DBC_cand_lockdate], $GLOBALS[_DBC_cand_id],
                      $GLOBALS[_DBC_composantes_id], $GLOBALS[_DBC_universites_id]
                    FROM  $GLOBALS[_DB_cand], $GLOBALS[_DB_annees], $GLOBALS[_DB_specs], $GLOBALS[_DB_propspec],
                        $GLOBALS[_DB_decisions], $GLOBALS[_DB_mentions], $GLOBALS[_DB_universites],
                        $GLOBALS[_DB_composantes]
                  WHERE $GLOBALS[_DBC_propspec_annee]=$GLOBALS[_DBC_annees_id]
                  AND $GLOBALS[_DBC_cand_decision]=$GLOBALS[_DBC_decisions_id]
                  AND $GLOBALS[_DBC_propspec_id]=$GLOBALS[_DBC_cand_propspec_id]
                  AND $GLOBALS[_DBC_propspec_id_spec]=$GLOBALS[_DBC_specs_id]
                  AND $GLOBALS[_DBC_specs_mention_id]=$GLOBALS[_DBC_mentions_id]
                  AND $GLOBALS[_DBC_propspec_comp_id]=$GLOBALS[_DBC_composantes_id]
                  AND $GLOBALS[_DBC_composantes_univ_id]=$GLOBALS[_DBC_universites_id]
                  AND $GLOBALS[_DBC_cand_groupe_spec]=(SELECT $GLOBALS[_DBC_groupes_spec_groupe] FROM $GLOBALS[_DB_groupes_spec], $GLOBALS[_DB_cand]
                                     WHERE $GLOBALS[_DBC_groupes_spec_propspec_id]=$GLOBALS[_DBC_cand_propspec_id]
                                     AND $GLOBALS[_DBC_cand_id]='$cand_id')
                  AND $GLOBALS[_DBC_cand_periode]=(SELECT $GLOBALS[_DBC_cand_periode] FROM $GLOBALS[_DB_cand] WHERE $GLOBALS[_DBC_cand_id]='$cand_id')
                  AND $GLOBALS[_DBC_propspec_comp_id]=(SELECT $GLOBALS[_DBC_propspec_comp_id] FROM $GLOBALS[_DB_propspec], $GLOBALS[_DB_cand]
                                      WHERE $GLOBALS[_DBC_propspec_id]=$GLOBALS[_DBC_cand_propspec_id]
                                      AND $GLOBALS[_DBC_cand_id]='$cand_id')
                  AND $GLOBALS[_DBC_cand_candidat_id]=(SELECT $GLOBALS[_DBC_cand_candidat_id] FROM $GLOBALS[_DB_cand] WHERE $GLOBALS[_DBC_cand_id]='$cand_id')
                    ORDER BY $GLOBALS[_DBC_cand_ordre_spec]");
  $rows=db_num_rows($result);

  if($rows)
  {
    // Tableau de tableaux qui sera retourné par la fonction
    $candidatures_multiples_array=array();

    for($i=0; $i<$rows; $i++)
    {
      $candidatures_multiples_array[$i]=array();

      list($candidatures_multiples_array[$i]["id"],
          $candidatures_multiples_array[$i]["propspec_id"],
          $candidatures_multiples_array[$i]["annee_id"],
          $candidatures_multiples_array[$i]["annee"],
          $candidatures_multiples_array[$i]["mention_nom"],
          $candidatures_multiples_array[$i]["spec_id"],
          $candidatures_multiples_array[$i]["spec_nom"],
          $candidatures_multiples_array[$i]["spec_nom_court"],
          $candidatures_multiples_array[$i]["decision"],
          $candidatures_multiples_array[$i]["decision_txt"],
          $candidatures_multiples_array[$i]["motivations_id"],
          $candidatures_multiples_array[$i]["selective"],
          $candidatures_multiples_array[$i]["transmission"],
          $candidatures_multiples_array[$i]["vap"],
          $candidatures_multiples_array[$i]["groupe_spec"],
          $candidatures_multiples_array[$i]["ordre_spec"],
          $candidatures_multiples_array[$i]["recours"],
          $candidatures_multiples_array[$i]["responsable"],
          $candidatures_multiples_array[$i]["responsable_email"],
          $candidatures_multiples_array[$i]["rang_attente"],
          $candidatures_multiples_array[$i]["entretiens"],
          $candidatures_multiples_array[$i]["entretien_date_unix"],
          $candidatures_multiples_array[$i]["entretien_heure"],
          $candidatures_multiples_array[$i]["entretien_lieu"],
          $candidatures_multiples_array[$i]["entretien_salle"],
          $candidatures_multiples_array[$i]["finalite"],
          $candidatures_multiples_array[$i]["date_decision_unix"],
          $candidatures_multiples_array[$i]["frais"],
          $candidatures_multiples_array[$i]["talon_reponse"],
          $candidatures_multiples_array[$i]["session_id"],
          $candidatures_multiples_array[$i]["lockdate"],
          $candidatures_multiples_array[$i]["comp_id"],
          $candidatures_multiples_array[$i]["univ_id"])=db_fetch_row($result,$i);

      $finalite=$candidatures_multiples_array[$i]["finalite"];
      $candidatures_multiples_array[$i]["nom_finalite"]=$GLOBALS["tab_finalite"][$finalite];
    
      // Texte de la formation
      if(!empty($candidatures_multiples_array[$i]["annee"]))
        $candidatures_multiples_array[$i]["texte_formation"]=trim($candidatures_multiples_array[$i]["annee"] . " - " .$candidatures_multiples_array[$i]["spec_nom"] . " " . $GLOBALS["tab_finalite_lettres"][$finalite]);
      else
        $candidatures_multiples_array[$i]["texte_formation"]=trim($candidatures_multiples_array[$i]["spec_nom"] . " " . $GLOBALS["tab_finalite_lettres"][$finalite]);

      // ================================
      // TODO : DATES A VERIFIER
      // $candidatures_multiples_array[$i]["session_commission"]=date_fr("j F Y",$candidatures_multiples_array[$i]["session_commission_unix"]);
      $candidatures_multiples_array[$i]["session_commission_unix"]=$candidatures_multiples_array[$i]["date_decision_unix"];

      if(ctype_digit($candidatures_multiples_array[$i]["session_commission_unix"]))
        $candidatures_multiples_array[$i]["session_commission"]=date_fr("j F Y",$candidatures_multiples_array[$i]["session_commission_unix"]);
      else
        $candidatures_multiples_array[$i]["session_commission"]="";

      // ================================

      // Et de la décision
      if(ctype_digit($candidatures_multiples_array[$i]["date_decision_unix"]))
        $candidatures_multiples_array[$i]["date_decision"]=date_fr("j F Y",$candidatures_multiples_array[$i]["date_decision_unix"]);
      else // ne dois normalement jamais être vide
        $candidatures_multiples_array[$i]["date_decision"]="";

      // $candidatures_multiples_array[$i]["date_commission"]=$candidatures_multiples_array[$i]["date_decision"];

      // Entretien

      if($candidatures_multiples_array[$i]["entretien_date_unix"]!="" && $candidatures_multiples_array[$i]["entretien_date_unix"]!=0)
      {
        $candidatures_multiples_array[$i]["entretien_date"]=date_fr("l jS F Y", $candidatures_multiples_array[$i]["entretien_date_unix"]);

        $heure=date("H", $candidatures_multiples_array[$i]["entretien_date_unix"]);
        
        if($heure!=0)
        {
          $minute=date("i", $candidatures_multiples_array[$i]["entretien_date_unix"]);
          $candidatures_multiples_array[$i]["entretien_heure"]=$heure . "h$minute";
        }
        else
          $candidatures_multiples_array[$i]["entretien_heure"]="";
      }
      else
        $candidatures_multiples_array[$i]["entretien_date"]="";

      // Traitement de la motivation : dépend de la décision
      $motivation_txt="";     

      $motif_array=explode("|", $candidatures_multiples_array[$i]["motivations_id"]);

      $cnt=count($motif_array);

      if($cnt)
      {
        if($cnt>1)
          $candidatures_multiples_array[$i]["motif_txt"]="les motifs suivants";
        else
          $candidatures_multiples_array[$i]["motif_txt"]="le motif suivant";

        for($j=0; $j<$cnt; $j++)
        {
          $txt=$motif_array[$j];

          if(ctype_digit($txt)) // motif provenant de la table motifs_refus
          {
            $result2=db_query($dbr,"SELECT $GLOBALS[_DBC_motifs_refus_motif], $GLOBALS[_DBC_motifs_refus_motif_long]
                              FROM $GLOBALS[_DB_motifs_refus]
                            WHERE $GLOBALS[_DBC_motifs_refus_id]='$txt'");
            $rows2=db_num_rows($result2);

            if($rows2)
              list($txt,$txt_long)=db_fetch_row($result2,0);
            else
              $txt=$txt_long="";

            db_free_result($result2);
          }
          else // motif libre
          {
            // nettoyage
            $txt_long="";
            // $txt=str_replace("@","",$motif_array[$j]);
            $txt=preg_replace("/^@/","", $motif_array[$j]);
          }

          if(!empty($txt_long))
            $txt="$txt_long";

          if(!$j)
            $candidatures_multiples_array[$i]["motivation"]="$txt";
          else
            $candidatures_multiples_array[$i]["motivation"].="\n$txt";
        }
      }
      else
        $candidatures_multiples_array[$i]["motivation"]=$candidatures_multiples_array[$i]["motif_txt"]="";

      // Nom complet de la candidature
      if($candidatures_multiples_array[$i]["annee"]=="")
        $candidatures_multiples_array[$i]["nom_complet"]=trim($candidatures_multiples_array[$i]["spec_nom"] . " " . $candidatures_multiples_array[$i]["nom_finalite"]);
      else
        $candidatures_multiples_array[$i]["nom_complet"]=trim($candidatures_multiples_array[$i]["annee"] . " - " . $candidatures_multiples_array[$i]["spec_nom"] . " " . $candidatures_multiples_array[$i]["nom_finalite"]);
    }

    db_free_result($result);

    // On a tout, on sort
    return $candidatures_multiples_array;
  }
  else // Candidature non trouvée : on sort
  {
    db_free_result($result);
    return FALSE;
  }
}

// Cursus complet d'un candidat

function __get_cursus($dbr,$candidat_id)
{
  $Y=date("Y");

  // TODO : "CASE WHEN ... " à modifier avec un flag "en cours" supplémentaire
  $result=db_query($dbr,"SELECT $GLOBALS[_DBC_cursus_id], $GLOBALS[_DBC_cursus_diplome], $GLOBALS[_DBC_cursus_intitule],
                      CASE  WHEN $GLOBALS[_DBC_cursus_annee]='0'
                          THEN '9999' ELSE $GLOBALS[_DBC_cursus_annee]
                      END AS annee_obtention,
                      $GLOBALS[_DBC_cursus_ecole], $GLOBALS[_DBC_cursus_ville], 
                      CASE WHEN $GLOBALS[_DBC_cursus_pays] IN (SELECT $GLOBALS[_DBC_pays_nat_ii_iso] FROM $GLOBALS[_DB_pays_nat_ii] WHERE $GLOBALS[_DBC_pays_nat_ii_iso]=$GLOBALS[_DBC_cursus_pays]) 
                        THEN (SELECT $GLOBALS[_DBC_pays_nat_ii_pays] FROM $GLOBALS[_DB_pays_nat_ii] WHERE $GLOBALS[_DBC_pays_nat_ii_iso]=$GLOBALS[_DBC_cursus_pays])
                        ELSE '' END as cursus_pays,
                      $GLOBALS[_DBC_cursus_justif_statut]
                  FROM $GLOBALS[_DB_cursus], $GLOBALS[_DB_cursus_justif]
                  WHERE $GLOBALS[_DBC_cursus_candidat_id]='$candidat_id'
                  AND   $GLOBALS[_DBC_cursus_justif_cursus_id]=$GLOBALS[_DBC_cursus_id]
                  AND   $GLOBALS[_DBC_cursus_justif_comp_id]='$_SESSION[comp_id]'
                  AND   $GLOBALS[_DBC_cursus_justif_periode]='$GLOBALS[__PERIODE]'
                  AND   $GLOBALS[_DBC_cursus_mention]!='Ajourné'
                  ORDER BY $GLOBALS[_DBC_cursus_annee] ASC");
  $rows=db_num_rows($result);

  if($rows)
  {
    // Tableau qui sera retourné par la fonction
    $cursus_array=array();

    for($i=0; $i<$rows; $i++)
    {   
      list($cursus_id, $dip, $int, $annee, $ecole, $ville, $pays, $statut)=db_fetch_row($result,$i);
    
      // Traitement
/*
      $dip=str_replace("_","",htmlspecialchars($dip,ENT_QUOTES, $default_htmlspecialchars_encoding));
      $int=str_replace("_","",htmlspecialchars($int,ENT_QUOTES, $default_htmlspecialchars_encoding));
      $ecole=str_replace("_","",htmlspecialchars($ecole,ENT_QUOTES, $default_htmlspecialchars_encoding));
      $ville=str_replace("_","",htmlspecialchars($ville,ENT_QUOTES, $default_htmlspecialchars_encoding));
      $pays=str_replace("_","",htmlspecialchars($pays,ENT_QUOTES, $default_htmlspecialchars_encoding));
*/
/*
      $dip=str_replace("_","", $dip);
      $int=str_replace("_","", $int);
      $ecole=str_replace("_","", $ecole);
      $ville=str_replace("_","", $ville);
      $pays=str_replace("_","", $pays);
*/
      $cursus_array[$i]["cursus"]="- $dip $int";

      if($statut==$GLOBALS["__CURSUS_DES_OBTENTION"])
        $cursus_array[$i]["cursus"].=" (en cours)";

      if(!empty($ecole))
        $cursus_array[$i]["lieu"]="- $ecole";

      if(!empty($ville))
      {
        if(isset($cursus_array[$i]["lieu"]))
          $cursus_array[$i]["lieu"].=", $ville";
        else
          $cursus_array[$i]["lieu"]="- $ville";
      }

      if(!empty($pays))
      {
        if(isset($cursus_array[$i]["lieu"]))
          $cursus_array[$i]["lieu"].=", $pays";
        else
          $cursus_array[$i]["lieu"]="- $pays";
      }

      // TODO : à modifier avec un flag "en cours" supplémentaire
      if($annee==9999) // marqueur pour les années =0 (=en cours) : l'identifiant donne la date exacte
      {
        if(strlen($cursus_id)==16) // => année sur un seul chiffre (7 => 2007)
          $cursus_array[$i]["date"]="200" . substr($cursus_id, 0, 1);
        else
          $cursus_array[$i]["date"]="20" . substr($cursus_id, 0, 2);
      }
      else
        $cursus_array[$i]["date"]="$annee";
    }

    db_free_result($result);

    // On a tout, on sort
    return $cursus_array;
  }
  else // cursus non trouvé : on sort
  {
    db_free_result($result);
    return array();
  }
}
?>
