<?php

// Configuration
/*
TODO : 
- date de début / fin d'extractions
- possibilité de ne pas mettre de dates
- gestion de recouvrement de périodes : plusieurs scripts avec l'année universitaire en paramètre ?

*/
// Activation des extractions par composante

$_module_apogee_DB_activ="moduleapogee_activation";

$_module_apogee_DBC_activ_comp_id="$_module_apogee_DB_activ.composante_id";
$_module_apogee_DBC_activ_pe="$_module_apogee_DB_activ.primo_entrants";
$_module_apogee_DBC_activ_lp="$_module_apogee_DB_activ.laisser_passer";

$_module_apogee_DBU_activ_comp_id="composante_id";
$_module_apogee_DBU_activ_pe="primo_entrants";
$_module_apogee_DBU_activ_lp="laisser_passer";


// Configuration du module : codes et messages

$_module_apogee_DB_config="moduleapogee_config";

$_module_apogee_DBC_config_univ_id="$_module_apogee_DB_config.univ_id"; // compat : remplacé par composante_id

$_module_apogee_DBC_config_comp_id="$_module_apogee_DB_config.composante_id";
$_module_apogee_DBC_config_code="$_module_apogee_DB_config.code";
$_module_apogee_DBC_config_prefixe_opi="$_module_apogee_DB_config.prefixe_opi";
$_module_apogee_DBC_config_message_primo="$_module_apogee_DB_config.message_primo";
$_module_apogee_DBC_config_message_lp="$_module_apogee_DB_config.message_laisser_passer";
$_module_apogee_DBC_config_message_reserve="$_module_apogee_DB_config.message_admis_sous_reserve";
$_module_apogee_DBC_config_adr_primo="$_module_apogee_DB_config.adresse_site_primo";
$_module_apogee_DBC_config_adr_reins="$_module_apogee_DB_config.adresse_site_reins";
$_module_apogee_DBC_config_adr_rdv="$_module_apogee_DB_config.adresse_site_rdv";
$_module_apogee_DBC_config_adr_conditions="$_module_apogee_DB_config.adresse_site_conditions";

$_module_apogee_DBU_config_univ_id="univ_id";

$_module_apogee_DBU_config_comp_id="composante_id";
$_module_apogee_DBU_config_code="code";
$_module_apogee_DBU_config_prefixe_opi="prefixe_opi";
$_module_apogee_DBU_config_message_primo="message_primo";
$_module_apogee_DBU_config_message_lp="message_laisser_passer";
$_module_apogee_DBU_config_message_reserve="message_admis_sous_reserve";
$_module_apogee_DBU_config_adr_primo="adresse_site_primo";
$_module_apogee_DBU_config_adr_reins="adresse_site_reins";
$_module_apogee_DBU_config_adr_rdv="adresse_site_rdv";
$_module_apogee_DBU_config_adr_conditions="adresse_site_conditions";


// COMPAT (pour le renommage de la table vers moduleapogee_config)
$_module_apogee_DB_code_univ="moduleapogee_code_universite";

// Codes et versions d'étapes des formations

$_module_apogee_DB_formations="moduleapogee_formations";

$_module_apogee_DBC_formations_propspec_id="$_module_apogee_DB_formations.propspec_id";
$_module_apogee_DBC_formations_cet="$_module_apogee_DB_formations.cet";
$_module_apogee_DBC_formations_vet="$_module_apogee_DB_formations.vet";
$_module_apogee_DBC_formations_centre_gestion="$_module_apogee_DB_formations.centre_id";

$_module_apogee_DBU_formations_propspec_id="propspec_id";
$_module_apogee_DBU_formations_cet="cet";
$_module_apogee_DBU_formations_vet="vet";
$_module_apogee_DBU_formations_centre_gestion="centre_id";

// Codes composantes

$_module_apogee_DB_centres_gestion="moduleapogee_centres_gestion";

$_module_apogee_DBC_centres_gestion_id="$_module_apogee_DB_centres_gestion.id";
$_module_apogee_DBC_centres_gestion_comp_id="$_module_apogee_DB_centres_gestion.composante_id";
$_module_apogee_DBC_centres_gestion_code="$_module_apogee_DB_centres_gestion.code";
$_module_apogee_DBC_centres_gestion_nom="$_module_apogee_DB_centres_gestion.nom";

$_module_apogee_DBU_centres_gestion_id="id";
$_module_apogee_DBU_centres_gestion_comp_id="composante_id";
$_module_apogee_DBU_centres_gestion_code="code";
$_module_apogee_DBU_centres_gestion_nom="nom";

// Numéros OPI

$_module_apogee_DB_numeros_opi="moduleapogee_numeros_opi";

$_module_apogee_DBC_numeros_opi_num="$_module_apogee_DB_numeros_opi.num";
$_module_apogee_DBC_numeros_opi_cand_id="$_module_apogee_DB_numeros_opi.candidature_id";
$_module_apogee_DBC_numeros_opi_ligne_candidat="$_module_apogee_DB_numeros_opi.ligne_candidat";
$_module_apogee_DBC_numeros_opi_ligne_voeux="$_module_apogee_DB_numeros_opi.ligne_voeu";
$_module_apogee_DBC_numeros_opi_temoin_reserve="$_module_apogee_DB_numeros_opi.temoin_reserve";

$_module_apogee_DBU_numeros_opi_num="num";
$_module_apogee_DBU_numeros_opi_cand_id="candidature_id";
$_module_apogee_DBU_numeros_opi_ligne_candidat="ligne_candidat";
$_module_apogee_DBU_numeros_opi_ligne_voeux="ligne_voeu";
$_module_apogee_DBU_numeros_opi_temoin_reserve="temoin_reserve";

// Codes Laisser-Passer

$_module_apogee_DB_codes_LP="moduleapogee_codes_laisser_passer";

$_module_apogee_DBC_codes_LP_code="$_module_apogee_DB_codes_LP.code";
$_module_apogee_DBC_codes_LP_cand_id="$_module_apogee_DB_codes_LP.candidature_id";
$_module_apogee_DBC_codes_LP_ligne_candidat="$_module_apogee_DB_codes_LP.ligne_candidat";

$_module_apogee_DBU_codes_LP_code="num";
$_module_apogee_DBU_codes_LP_cand_id="candidature_id";
$_module_apogee_DBU_codes_LP_ligne_candidat="ligne_candidat";

// Messages spécifiques aux formations
$_module_apogee_DB_messages="moduleapogee_messages";

$_module_apogee_DBC_messages_msg_id="$_module_apogee_DB_messages.message_id";
$_module_apogee_DBC_messages_comp_id="$_module_apogee_DB_messages.composante_id";
$_module_apogee_DBC_messages_nom="$_module_apogee_DB_messages.nom_message";
$_module_apogee_DBC_messages_contenu="$_module_apogee_DB_messages.contenu_message";
$_module_apogee_DBC_messages_type="$_module_apogee_DB_messages.type_message";

$_module_apogee_DBU_messages_msg_id="message_id";
$_module_apogee_DBU_messages_comp_id="composante_id";
$_module_apogee_DBU_messages_nom="nom_message";
$_module_apogee_DBU_messages_contenu="contenu_message";
$_module_apogee_DBU_messages_type="type_message";

// Liens entre les messages et les formations
$_module_apogee_DB_messages_formations="moduleapogee_messages_formations";

$_module_apogee_DBC_messages_formations_propspec_id="$_module_apogee_DB_messages_formations.propspec_id";
$_module_apogee_DBC_messages_formations_msg_id="$_module_apogee_DB_messages_formations.message_id";

$_module_apogee_DBU_messages_formations_propspec_id="propspec_id";
$_module_apogee_DBU_messages_formations_msg_id="message_id";


if(is_file("$GLOBALS[__PLUGINS_DIR_ABS]/apogee/include/update_db.php"))
{
	include "$GLOBALS[__PLUGINS_DIR_ABS]/apogee/include/update_db.php";
	@unlink("$GLOBALS[__PLUGINS_DIR_ABS]/apogee/include/update_db.php");
}

?>
