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

// Description de la base de données

// Table "acces"
$_DB_acces="acces";
$_DBC_acces_id="$_DB_acces.id";
$_DBC_acces_composante_id="$_DB_acces.composante_id";
$_DBC_acces_nom ="$_DB_acces.nom";
$_DBC_acces_prenom="$_DB_acces.prenom";
$_DBC_acces_login="$_DB_acces.login";
$_DBC_acces_pass="$_DB_acces.pass";
$_DBC_acces_courriel="$_DB_acces.courriel";
$_DBC_acces_niveau="$_DB_acces.niveau";
$_DBC_acces_filtre="$_DB_acces.filtre";
$_DBC_acces_reception_msg_scol="$_DB_acces.reception_msg_scol";
$_DBC_acces_absence_debut="$_DB_acces.absence_debut";
$_DBC_acces_absence_fin="$_DB_acces.absence_fin";
$_DBC_acces_absence_msg="$_DB_acces.absence_message";
$_DBC_acces_absence_active="$_DB_acces.absence_active";
$_DBC_acces_signature_txt="$_DB_acces.signature_txt";
$_DBC_acces_signature_active="$_DB_acces.signature_active";
$_DBC_acces_reception_msg_systeme="$_DB_acces.reception_msg_systeme";
$_DBC_acces_source="$_DB_acces.source";

$_DBU_acces_id="id";
$_DBU_acces_composante_id="composante_id";
$_DBU_acces_nom ="nom";
$_DBU_acces_prenom="prenom";
$_DBU_acces_login="login";
$_DBU_acces_pass="pass";
$_DBU_acces_courriel="courriel";
$_DBU_acces_niveau="niveau";
$_DBU_acces_filtre="filtre";
$_DBU_acces_reception_msg_scol="reception_msg_scol";
$_DBU_acces_absence_debut="absence_debut";
$_DBU_acces_absence_fin="absence_fin";
$_DBU_acces_absence_msg="absence_message";
$_DBU_acces_absence_active="absence_active";
$_DBU_acces_signature_txt="signature_txt";
$_DBU_acces_signature_active="signature_active";
$_DBU_acces_reception_msg_systeme="reception_msg_systeme";
$_DBU_acces_source="source";

// acces_candidats_lus : marquage pour le mode consultation
$_DB_acces_candidats_lus="acces_candidats_lus";
$_DBC_acces_candidats_lus_acces_id="$_DB_acces_candidats_lus.acces_id";
$_DBC_acces_candidats_lus_candidat_id="$_DB_acces_candidats_lus.candidat_id";
$_DBC_acces_candidats_lus_periode="$_DB_acces_candidats_lus.periode";

$_DBU_acces_candidats_lus_acces_id="acces_id";
$_DBU_acces_candidats_lus_candidat_id="candidat_id";
$_DBU_acces_candidats_lus_periode="periode";

// Accès_composante

$_DB_acces_comp="acces_composantes";
$_DBC_acces_comp_acces_id="$_DB_acces_comp.id_acces";
$_DBC_acces_comp_composante_id="$_DB_acces_comp.id_composante";

$_DBU_acces_comp_acces_id="id_acces";
$_DBU_acces_comp_composante_id="id_composante";

// Accès formations

$_DB_droits_formations="droits_formations";
$_DBC_droits_formations_acces_id="$_DB_droits_formations.acces_id";
$_DBC_droits_formations_propspec_id="$_DB_droits_formations.propspec_id";
$_DBC_droits_formations_droits="$_DB_droits_formations.droits";

$_DBU_droits_formations_acces_id="acces_id";
$_DBU_droits_formations_propspec_id="propspec_id";
$_DBU_droits_formations_droits="droits";


// TABLE "annees"
$_DB_annees="annees";
$_DBC_annees_id="$_DB_annees.id";
$_DBC_annees_annee="$_DB_annees.annee";
$_DBC_annees_annee_longue="$_DB_annees.annee_longue";
$_DBC_annees_ordre="$_DB_annees.ordre";

$_DBU_annees_id="id";
$_DBU_annees_annee="annee";
$_DBU_annees_annee_longue="annee_longue";
$_DBU_annees_ordre="ordre";


// TABLE "candidat"
$_DB_candidat="candidat";
$_DBC_candidat_id="$_DB_candidat.id";
$_DBC_candidat_civilite="$_DB_candidat.civilite";
$_DBC_candidat_nom="$_DB_candidat.nom";
$_DBC_candidat_nom_naissance="$_DB_candidat.nom_naissance";
$_DBC_candidat_prenom="$_DB_candidat.prenom";
$_DBC_candidat_date_naissance="$_DB_candidat.date_naissance";
$_DBC_candidat_nationalite="$_DB_candidat.nationalite";
$_DBC_candidat_telephone="$_DB_candidat.telephone";
$_DBC_candidat_telephone_portable="$_DB_candidat.telephone_portable";
$_DBC_candidat_adresse_1="$_DB_candidat.adresse_1";
$_DBC_candidat_adresse_2="$_DB_candidat.adresse_2";
$_DBC_candidat_adresse_3="$_DB_candidat.adresse_3";
$_DBC_candidat_numero_ine="$_DB_candidat.numero_ine";
$_DBC_candidat_email="$_DB_candidat.email";
$_DBC_candidat_identifiant="$_DB_candidat.identifiant";
$_DBC_candidat_code_acces="$_DB_candidat.code_acces";
$_DBC_candidat_connexion="$_DB_candidat.connexion";
$_DBC_candidat_lieu_naissance="$_DB_candidat.lieu_naissance";
$_DBC_candidat_prenom2="$_DB_candidat.prenom2";
$_DBC_candidat_derniere_ip="$_DB_candidat.derniere_ip";
$_DBC_candidat_dernier_host="$_DB_candidat.dernier_host";
$_DBC_candidat_pays_naissance="$_DB_candidat.pays_naissance";
$_DBC_candidat_adresse_cp="$_DB_candidat.adr_cp";
$_DBC_candidat_adresse_ville="$_DB_candidat.adr_ville";
$_DBC_candidat_adresse_pays="$_DB_candidat.adr_pays";
$_DBC_candidat_dernier_user_agent="$_DB_candidat.dernier_user_agent";
$_DBC_candidat_derniere_erreur_code="$_DB_candidat.derniere_erreur_code";
$_DBC_candidat_manuelle="$_DB_candidat.manuelle";
$_DBC_candidat_cursus_en_cours="$_DB_candidat.cursus_en_cours";
$_DBC_candidat_lock="$_DB_candidat.lock";
$_DBC_candidat_lockdate="$_DB_candidat.lockdate";
$_DBC_candidat_dpt_naissance="$_DB_candidat.dpt_naissance";
$_DBC_candidat_deja_inscrit="$_DB_candidat.deja_inscrit";
$_DBC_candidat_annee_premiere_inscr="$_DB_candidat.annee_premiere_inscription";
$_DBC_candidat_annee_bac="$_DB_candidat.annee_bac";
$_DBC_candidat_serie_bac="$_DB_candidat.serie_bac";

$_DBU_candidat_id="id";
$_DBU_candidat_civilite="civilite";
$_DBU_candidat_nom="nom";
$_DBU_candidat_nom_naissance="nom_naissance";
$_DBU_candidat_prenom="prenom";
$_DBU_candidat_date_naissance="date_naissance";
$_DBU_candidat_nationalite="nationalite";
$_DBU_candidat_telephone="telephone";
$_DBU_candidat_telephone_portable="telephone_portable";
$_DBU_candidat_adresse_1="adresse_1";
$_DBU_candidat_adresse_2="adresse_2";
$_DBU_candidat_adresse_3="adresse_3";
$_DBU_candidat_numero_ine="numero_ine";
$_DBU_candidat_email="email";
$_DBU_candidat_identifiant="identifiant";
$_DBU_candidat_code_acces="code_acces";
$_DBU_candidat_connexion="connexion";
$_DBU_candidat_lieu_naissance="lieu_naissance";
$_DBU_candidat_prenom2="prenom2";
$_DBU_candidat_derniere_ip="derniere_ip";
$_DBU_candidat_dernier_host="dernier_host";
$_DBU_candidat_pays_naissance="pays_naissance";
$_DBU_candidat_adresse_cp="adr_cp";
$_DBU_candidat_adresse_ville="adr_ville";
$_DBU_candidat_adresse_pays="adr_pays";
$_DBU_candidat_dernier_user_agent="dernier_user_agent";
$_DBU_candidat_derniere_erreur_code="derniere_erreur_code";
$_DBU_candidat_manuelle="manuelle";
$_DBU_candidat_cursus_en_cours="cursus_en_cours";
$_DBU_candidat_lock="lock";
$_DBU_candidat_lockdate="lockdate";
$_DBU_candidat_dpt_naissance="dpt_naissance";
$_DBU_candidat_deja_inscrit="deja_inscrit";
$_DBU_candidat_annee_premiere_inscr="annee_premiere_inscription";
$_DBU_candidat_annee_bac="annee_bac";
$_DBU_candidat_serie_bac="serie_bac";

// TABLE "candidature"
$_DB_cand="candidature";
$_DBC_cand_id="$_DB_cand.id";
$_DBC_cand_candidat_id="$_DB_cand.candidat_id";
$_DBC_cand_propspec_id="$_DB_cand.propspec_id";
$_DBC_cand_ordre="$_DB_cand.ordre";
$_DBC_cand_statut="$_DB_cand.statut";
$_DBC_cand_motivation_decision="$_DB_cand.motivation_decision";
$_DBC_cand_traitee_par="$_DB_cand.traitee_par";
$_DBC_cand_ordre_spec="$_DB_cand.ordre_spec";
$_DBC_cand_groupe_spec="$_DB_cand.groupe_spec";
$_DBC_cand_date_decision="$_DB_cand.date_decision";
$_DBC_cand_decision="$_DB_cand.decision";
$_DBC_cand_recours="$_DB_cand.recours";
$_DBC_cand_liste_attente="$_DB_cand.liste_attente";
$_DBC_cand_transmission_dossier="$_DB_cand.transmission_dossier";
$_DBC_cand_vap_flag="$_DB_cand.vap_flag";
$_DBC_cand_masse="$_DB_cand.masse";
$_DBC_cand_talon_reponse="$_DB_cand.talon_reponse";
$_DBC_cand_statut_frais="$_DB_cand.statut_frais";
$_DBC_cand_entretien_date="$_DB_cand.entretien_date";
$_DBC_cand_entretien_heure="$_DB_cand.entretien_heure";
$_DBC_cand_entretien_lieu="$_DB_cand.entretien_lieu";
$_DBC_cand_entretien_salle="$_DB_cand.entretien_salle";
$_DBC_cand_date_statut="$_DB_cand.date_statut";
$_DBC_cand_date_prise_decision="$_DB_cand.date_prise_decision";
$_DBC_cand_periode="$_DB_cand.periode";
$_DBC_cand_session_id="$_DB_cand.session_id";
$_DBC_cand_lock="$_DB_cand.lock";
$_DBC_cand_lockdate="$_DB_cand.lockdate";
$_DBC_cand_rappels="$_DB_cand.rappels";
$_DBC_cand_notification_envoyee="$_DB_cand.notification_envoyee";

$_DBU_cand_id="id";
$_DBU_cand_candidat_id="candidat_id";
$_DBU_cand_propspec_id="propspec_id";
$_DBU_cand_ordre="ordre";
$_DBU_cand_statut="statut";
$_DBU_cand_motivation_decision="motivation_decision";
$_DBU_cand_traitee_par="traitee_par";
$_DBU_cand_ordre_spec="ordre_spec";
$_DBU_cand_groupe_spec="groupe_spec";
$_DBU_cand_date_decision="date_decision";
$_DBU_cand_decision="decision";
$_DBU_cand_recours="recours";
$_DBU_cand_liste_attente="liste_attente";
$_DBU_cand_transmission_dossier="transmission_dossier";
$_DBU_cand_vap_flag="vap_flag";
$_DBU_cand_masse="masse";
$_DBU_cand_talon_reponse="talon_reponse";
$_DBU_cand_statut_frais="statut_frais";
$_DBU_cand_entretien_date="entretien_date";
$_DBU_cand_entretien_heure="entretien_heure";
$_DBU_cand_entretien_lieu="entretien_lieu";
$_DBU_cand_entretien_salle="entretien_salle";
$_DBU_cand_date_statut="date_statut";
$_DBU_cand_date_prise_decision="date_prise_decision";
$_DBU_cand_periode="periode";
$_DBU_cand_session_id="session_id";
$_DBU_cand_lock="lock";
$_DBU_cand_lockdate="lockdate";
$_DBU_cand_rappels="rappels";
$_DBU_cand_notification_envoyee="notification_envoyee";

$_DB_commissions="commissions";
$_DBC_commissions_propspec_id="$_DB_commissions.propspec_id";
$_DBC_commissions_id="$_DB_commissions.id";
$_DBC_commissions_date="$_DB_commissions.date";
$_DBC_commissions_periode="$_DB_commissions.periode";

$_DBU_commissions_propspec_id="propspec_id";
$_DBU_commissions_id="id";
$_DBU_commissions_date="date";
$_DBU_commissions_periode="periode";

// Composantes

$_DB_composantes="composantes";
$_DBC_composantes_id="$_DB_composantes.id";
$_DBC_composantes_nom="$_DB_composantes.nom";
$_DBC_composantes_univ_id="$_DB_composantes.univ_id";
$_DBC_composantes_adresse="$_DB_composantes.adresse";
$_DBC_composantes_contact="$_DB_composantes.contact";
$_DBC_composantes_directeur="$_DB_composantes.directeur";
$_DBC_composantes_scolarite="$_DB_composantes.scolarite";
$_DBC_composantes_logo="$_DB_composantes.defaut_fichier_logo";
$_DBC_composantes_txt_scol="$_DB_composantes.defaut_texte_scol";
$_DBC_composantes_txt_sign="$_DB_composantes.defaut_texte_signature";
$_DBC_composantes_txt_logo="$_DB_composantes.defaut_texte_logo";
$_DBC_composantes_delai_lock="$_DB_composantes.delai_verrouillage";
$_DBC_composantes_largeur_logo="$_DB_composantes.defaut_largeur_logo";
$_DBC_composantes_courriel_scol="$_DB_composantes.courriel_scolarite";
$_DBC_composantes_limite_cand_nombre="$_DB_composantes.limite_cand_nombre";
$_DBC_composantes_limite_cand_annee="$_DB_composantes.limite_cand_annee";
$_DBC_composantes_limite_cand_annee_mention="$_DB_composantes.limite_cand_annee_mention";
$_DBC_composantes_gestion_motifs="$_DB_composantes.gestion_motifs";
$_DBC_composantes_ent_salle="$_DB_composantes.defaut_entretien_salle";
$_DBC_composantes_ent_lieu="$_DB_composantes.defaut_entretien_lieu";
$_DBC_composantes_www="$_DB_composantes.www";
$_DBC_composantes_affichage_decisions="$_DB_composantes.defaut_affichage_decisions";
$_DBC_composantes_adr_pos_x="$_DB_composantes.defaut_adr_pos_x";
$_DBC_composantes_adr_pos_y="$_DB_composantes.defaut_adr_pos_y";
$_DBC_composantes_corps_pos_x="$_DB_composantes.defaut_corps_pos_x";
$_DBC_composantes_corps_pos_y="$_DB_composantes.defaut_corps_pos_y";
$_DBC_composantes_avertir_decision="$_DB_composantes.avertir_decision";

$_DBU_composantes_id="id";
$_DBU_composantes_nom="nom";
$_DBU_composantes_univ_id="univ_id";
$_DBU_composantes_adresse="adresse";
$_DBU_composantes_contact="contact";
$_DBU_composantes_directeur="directeur";
$_DBU_composantes_scolarite="scolarite";
$_DBU_composantes_logo="defaut_fichier_logo";
$_DBU_composantes_txt_scol="defaut_texte_scol";
$_DBU_composantes_txt_sign="defaut_texte_signature";
$_DBU_composantes_txt_logo="defaut_texte_logo";
$_DBU_composantes_delai_lock="delai_verrouillage";
$_DBU_composantes_largeur_logo="defaut_largeur_logo";
$_DBU_composantes_courriel_scol="courriel_scolarite";
$_DBU_composantes_limite_cand_nombre="limite_cand_nombre";
$_DBU_composantes_limite_cand_annee="limite_cand_annee";
$_DBU_composantes_limite_cand_annee_mention="limite_cand_annee_mention";
$_DBU_composantes_gestion_motifs="gestion_motifs";
$_DBU_composantes_ent_salle="defaut_entretien_salle";
$_DBU_composantes_ent_lieu="defaut_entretien_lieu";
$_DBU_composantes_www="www";
$_DBU_composantes_affichage_decisions="defaut_affichage_decisions";
$_DBU_composantes_adr_pos_x="defaut_adr_pos_x";
$_DBU_composantes_adr_pos_y="defaut_adr_pos_y";
$_DBU_composantes_corps_pos_x="defaut_corps_pos_x";
$_DBU_composantes_corps_pos_y="defaut_corps_pos_y";
$_DBU_composantes_avertir_decision="avertir_decision";

$_DB_cursus="cursus";
$_DBC_cursus_id="$_DB_cursus.id";
$_DBC_cursus_candidat_id="$_DB_cursus.candidat_id";
$_DBC_cursus_diplome="$_DB_cursus.diplome";
$_DBC_cursus_intitule="$_DB_cursus.intitule";
$_DBC_cursus_spec="$_DB_cursus.specialite";
$_DBC_cursus_annee="$_DB_cursus.annee";
$_DBC_cursus_ecole="$_DB_cursus.ecole";
$_DBC_cursus_ville="$_DB_cursus.ville";
$_DBC_cursus_pays="$_DB_cursus.pays";
$_DBC_cursus_moyenne="$_DB_cursus.note_moyenne";
$_DBC_cursus_mention="$_DB_cursus.mention";
$_DBC_cursus_rang="$_DB_cursus.rang";

$_DBU_cursus_id="id";
$_DBU_cursus_candidat_id="candidat_id";
$_DBU_cursus_diplome="diplome";
$_DBU_cursus_intitule="intitule";
$_DBU_cursus_spec="specialite";
$_DBU_cursus_annee="annee";
$_DBU_cursus_ecole="ecole";
$_DBU_cursus_ville="ville";
$_DBU_cursus_pays="pays";
$_DBU_cursus_moyenne="note_moyenne";
$_DBU_cursus_mention="mention";
$_DBU_cursus_rang="rang";

$_DB_cursus_diplomes="cursus_diplomes";
$_DBC_cursus_diplomes_id="$_DB_cursus_diplomes.id";
$_DBC_cursus_diplomes_intitule="$_DB_cursus_diplomes.intitule";
$_DBC_cursus_diplomes_niveau="$_DB_cursus_diplomes.niveau";

$_DBU_cursus_diplomes_id="id";
$_DBU_cursus_diplomes_intitule="intitule";
$_DBU_cursus_diplomes_niveau="niveau";

/* OBSOLETE : retour à la liste des diplômes lisibles par tous
$_DB_cursus_apogee="cursus_diplomes_apogee";
$_DBC_cursus_apogee_code="$_DB_cursus_apogee.code";
$_DBC_cursus_apogee_libelle_long="$_DB_cursus_apogee.libelle_long";
$_DBC_cursus_apogee_libelle_court="$_DB_cursus_apogee.libelle_court";

$_DBU_cursus_apogee_code="code";
$_DBU_cursus_apogee_libelle_long="libelle_long";
$_DBU_cursus_apogee_libelle_court="libelle_court";
*/


// Justification des étapes du cursus
$_DB_cursus_justif="cursus_justificatifs";
$_DBC_cursus_justif_cursus_id="$_DB_cursus_justif.cursus_id";
$_DBC_cursus_justif_comp_id="$_DB_cursus_justif.composante_id";
$_DBC_cursus_justif_statut="$_DB_cursus_justif.statut";
$_DBC_cursus_justif_precision="$_DB_cursus_justif.precision";
$_DBC_cursus_justif_periode="$_DB_cursus_justif.periode";

$_DBU_cursus_justif_cursus_id="cursus_id";
$_DBU_cursus_justif_comp_id="composante_id";
$_DBU_cursus_justif_statut="statut";
$_DBU_cursus_justif_precision="precision";
$_DBU_cursus_justif_periode="periode";


$_DB_cursus_mentions="cursus_mentions";
$_DBC_cursus_mentions_id="$_DB_cursus_mentions.id";
$_DBC_cursus_mentions_intitule="$_DB_cursus_mentions.intitule";

$_DBU_cursus_mentions_id="id";
$_DBU_cursus_mentions_intitule="intitule";

// Codes bac

$_DB_diplomes_bac="diplomes_bac";
$_DBC_diplomes_bac_code="$_DB_diplomes_bac.code_bac";
$_DBC_diplomes_bac_intitule="$_DB_diplomes_bac.intitule";

$_DBU_diplomes_bac_code="code_bac";
$_DBU_diplomes_bac_intitule="intitule";

// =======================================================================

$_DB_decisions="decisions";
$_DBC_decisions_id="$_DB_decisions.id";
$_DBC_decisions_texte="$_DB_decisions.texte";
$_DBC_decisions_selective="$_DB_decisions.selective";
$_DBC_decisions_non_selective="$_DB_decisions.non_selective";

$_DBU_decisions_id="id";
$_DBU_decisions_texte="texte";
$_DBU_decisions_selective="selective";
$_DBU_decisions_non_selective="non_selective";


$_DB_decisions_comp="decisions_composantes";
$_DBC_decisions_comp_comp_id="$_DB_decisions_comp.composante_id";
$_DBC_decisions_comp_dec_id="$_DB_decisions_comp.decision_id";

$_DBU_decisions_comp_comp_id="composante_id";
$_DBU_decisions_comp_dec_id="decision_id";


// ============================================================
//                   EDITEUR DE DOSSIERS
// ============================================================


$_DB_dossiers_elems="dossiers_elements";
$_DBC_dossiers_elems_id="$_DB_dossiers_elems.id";
$_DBC_dossiers_elems_type="$_DB_dossiers_elems.type";
$_DBC_dossiers_elems_intitule="$_DB_dossiers_elems.intitule";
$_DBC_dossiers_elems_para="$_DB_dossiers_elems.paragraphe";
$_DBC_dossiers_elems_nb_lignes="$_DB_dossiers_elems.nb_lignes";
$_DBC_dossiers_elems_vap="$_DB_dossiers_elems.condition_vap";
$_DBC_dossiers_elems_comp_id="$_DB_dossiers_elems.composante_id";
$_DBC_dossiers_elems_unique="$_DB_dossiers_elems.demande_unique";
$_DBC_dossiers_elems_obligatoire="$_DB_dossiers_elems.obligatoire";
$_DBC_dossiers_elems_recapitulatif="$_DB_dossiers_elems.recapitulatif";
$_DBC_dossiers_elems_nb_choix_min="$_DB_dossiers_elems.nb_choix_min";
$_DBC_dossiers_elems_nb_choix_max="$_DB_dossiers_elems.nb_choix_max";
$_DBC_dossiers_elems_nouvelle_page="$_DB_dossiers_elems.nouvelle_page";
$_DBC_dossiers_elems_extractions="$_DB_dossiers_elems.extractions";

$_DBU_dossiers_elems_id="id";
$_DBU_dossiers_elems_type="type";
$_DBU_dossiers_elems_intitule="intitule";
$_DBU_dossiers_elems_para="paragraphe";
$_DBU_dossiers_elems_nb_lignes="nb_lignes";
$_DBU_dossiers_elems_vap="condition_vap";
$_DBU_dossiers_elems_comp_id="composante_id";
$_DBU_dossiers_elems_unique="demande_unique";
$_DBU_dossiers_elems_obligatoire="obligatoire";
$_DBU_dossiers_elems_recapitulatif="recapitulatif";
$_DBU_dossiers_elems_nb_choix_min="nb_choix_min";
$_DBU_dossiers_elems_nb_choix_max="nb_choix_max";
$_DBU_dossiers_elems_nouvelle_page="nouvelle_page";
$_DBU_dossiers_elems_extractions="extractions";


$_DB_dossiers_ef="dossiers_ef";
$_DBC_dossiers_ef_elem_id="$_DB_dossiers_ef.element_id";
$_DBC_dossiers_ef_propspec_id="$_DB_dossiers_ef.propspec_id";
$_DBC_dossiers_ef_ordre="$_DB_dossiers_ef.ordre";

$_DBU_dossiers_ef_elem_id="element_id";
$_DBU_dossiers_ef_propspec_id="propspec_id";
$_DBU_dossiers_ef_ordre="ordre";

$_DB_dossiers_elems_contenu="dossiers_elements_contenu";
$_DBC_dossiers_elems_contenu_candidat_id="$_DB_dossiers_elems_contenu.candidat_id";
$_DBC_dossiers_elems_contenu_elem_id="$_DB_dossiers_elems_contenu.element_id";
$_DBC_dossiers_elems_contenu_comp_id="$_DB_dossiers_elems_contenu.composante_id";
$_DBC_dossiers_elems_contenu_para="$_DB_dossiers_elems_contenu.paragraphe";
$_DBC_dossiers_elems_contenu_propspec_id="$_DB_dossiers_elems_contenu.propspec_id";
$_DBC_dossiers_elems_contenu_periode="$_DB_dossiers_elems_contenu.periode";

$_DBU_dossiers_elems_contenu_candidat_id="candidat_id";
$_DBU_dossiers_elems_contenu_elem_id="element_id";
$_DBU_dossiers_elems_contenu_comp_id="composante_id";
$_DBU_dossiers_elems_contenu_para="paragraphe";
$_DBU_dossiers_elems_contenu_propspec_id="propspec_id";
$_DBU_dossiers_elems_contenu_periode="periode";

$_DB_dossiers_elems_choix="dossiers_elements_choix";
$_DBC_dossiers_elems_choix_id="$_DB_dossiers_elems_choix.id";
$_DBC_dossiers_elems_choix_elem_id="$_DB_dossiers_elems_choix.element_id";
$_DBC_dossiers_elems_choix_texte="$_DB_dossiers_elems_choix.texte";
$_DBC_dossiers_elems_choix_ordre="$_DB_dossiers_elems_choix.ordre";

$_DBU_dossiers_elems_choix_id="id";
$_DBU_dossiers_elems_choix_elem_id="element_id";
$_DBU_dossiers_elems_choix_texte="texte";
$_DBU_dossiers_elems_choix_ordre="ordre";

// ============================================================

$_DB_groupes_spec="groupes_specialites";
$_DBC_groupes_spec_propspec_id="$_DB_groupes_spec.propspec_id";
$_DBC_groupes_spec_groupe="$_DB_groupes_spec.groupe";
$_DBC_groupes_spec_auto="$_DB_groupes_spec.ajout_automatique";
$_DBC_groupes_spec_nom="$_DB_groupes_spec.nom";
$_DBC_groupes_spec_dates_communes="$_DB_groupes_spec.dates_communes";

$_DBU_groupes_spec_propspec_id="propspec_id";
$_DBU_groupes_spec_groupe="groupe";
$_DBU_groupes_spec_auto="ajout_automatique";
$_DBU_groupes_spec_nom="nom";
$_DBU_groupes_spec_dates_communes="dates_communes";

// ====================== Historique  =========================

$_DB_hist="historique";

$_DBC_hist_date="$_DB_hist.date";
$_DBC_hist_ip="$_DB_hist.ip";
$_DBC_hist_host="$_DB_hist.host";
$_DBC_hist_g_id="$_DB_hist.acces_id";
// gestionnaires
$_DBC_hist_g_nom="$_DB_hist.g_nom";
$_DBC_hist_g_prenom="$_DB_hist.g_prenom";
$_DBC_hist_g_email="$_DB_hist.g_email";
$_DBC_hist_comp_id="$_DB_hist.composante_id";
$_DBC_hist_niveau="$_DB_hist.niveau";
// candidats
$_DBC_hist_c_id="$_DB_hist.candidat_id";
$_DBC_hist_c_nom="$_DB_hist.c_nom";
$_DBC_hist_c_prenom="$_DB_hist.c_prenom";
$_DBC_hist_c_email="$_DB_hist.c_email";
// évenement
$_DBC_hist_element_id="$_DB_hist.element_id";
$_DBC_hist_type_evt="$_DB_hist.type_evenement";
$_DBC_hist_evt="$_DB_hist.evenement";
$_DBC_hist_query="$_DB_hist.requete";

$_DBU_hist_date="date";
$_DBU_hist_ip="ip";
$_DBU_hist_host="host";
$_DBU_hist_g_id="acces_id";
$_DBU_hist_g_nom="g_nom";
$_DBU_hist_g_prenom="g_prenom";
$_DBU_hist_g_email="g_email";
$_DBU_hist_comp_id="composante_id";
$_DBU_hist_niveau="niveau";
$_DBU_hist_candidat_id="candidat_id";
$_DBU_hist_c_nom="c_nom";
$_DBU_hist_c_prenom="c_prenom";
$_DBU_hist_c_email="c_email";
$_DBU_hist_element_id="element_id";
$_DBU_hist_type_evt="type_evenement";
$_DBU_hist_evt="evenement";
$_DBU_hist_query="requete";

// ============================================================

$_DB_infos_comp="infos_complementaires";
$_DBC_infos_comp_id="$_DB_infos_comp.id";
$_DBC_infos_comp_candidat_id="$_DB_infos_comp.candidat_id";
$_DBC_infos_comp_texte="$_DB_infos_comp.texte";
$_DBC_infos_comp_annee="$_DB_infos_comp.annee";
$_DBC_infos_comp_duree="$_DB_infos_comp.duree";

$_DBU_infos_comp_id="id";
$_DBU_infos_comp_candidat_id="candidat_id";
$_DBU_infos_comp_texte="texte";
$_DBU_infos_comp_annee="annee";
$_DBU_infos_comp_duree="duree";


// ============================================================
//                PAGE INFO DES COMPOSANTES
// ============================================================


// Table de base
$_DB_comp_infos="composantes_infos";
$_DBC_comp_infos_id="$_DB_comp_infos.id";
$_DBC_comp_infos_comp_id="$_DB_comp_infos.composante_id";

$_DBU_comp_infos_id="id";
$_DBU_comp_infos_comp_id="composante_id";

// Paragraphes
$_DB_comp_infos_para="composantes_infos_paragraphes";
$_DBC_comp_infos_para_info_id="$_DB_comp_infos_para.info_id";
$_DBC_comp_infos_para_ordre="$_DB_comp_infos_para.ordre";
$_DBC_comp_infos_para_texte="$_DB_comp_infos_para.texte";
$_DBC_comp_infos_para_gras="$_DB_comp_infos_para.gras";
$_DBC_comp_infos_para_italique="$_DB_comp_infos_para.italique";
$_DBC_comp_infos_para_align="$_DB_comp_infos_para.alignement";
$_DBC_comp_infos_para_taille="$_DB_comp_infos_para.taille";

$_DBU_comp_infos_para_info_id="info_id";
$_DBU_comp_infos_para_ordre="ordre";
$_DBU_comp_infos_para_texte="texte";
$_DBU_comp_infos_para_gras="gras";
$_DBU_comp_infos_para_italique="italique";
$_DBU_comp_infos_para_align="alignement";
$_DBU_comp_infos_para_taille="taille";

// Encadrés
$_DB_comp_infos_encadre="composantes_infos_encadres";
$_DBC_comp_infos_encadre_info_id="$_DB_comp_infos_encadre.info_id";
$_DBC_comp_infos_encadre_texte="$_DB_comp_infos_encadre.texte";
$_DBC_comp_infos_encadre_txt_align="$_DB_comp_infos_encadre.txt_align";
$_DBC_comp_infos_encadre_ordre="$_DB_comp_infos_encadre.ordre";

$_DBU_comp_infos_encadre_info_id="info_id";
$_DBU_comp_infos_encadre_texte="texte";
$_DBU_comp_infos_encadre_txt_align="txt_align";
$_DBU_comp_infos_encadre_ordre="ordre";

// Fichiers

$_DB_comp_infos_fichiers="composantes_infos_fichiers";
$_DBC_comp_infos_fichiers_info_id="$_DB_comp_infos_fichiers.info_id";
$_DBC_comp_infos_fichiers_texte="$_DB_comp_infos_fichiers.texte";
$_DBC_comp_infos_fichiers_fichier="$_DB_comp_infos_fichiers.fichier";
$_DBC_comp_infos_fichiers_ordre="$_DB_comp_infos_fichiers.ordre";

$_DBU_comp_infos_fichiers_info_id="info_id";
$_DBU_comp_infos_fichiers_texte="texte";
$_DBU_comp_infos_fichiers_txt_align="fichier";
$_DBU_comp_infos_fichiers_ordre="ordre";


// Séparateurs
$_DB_comp_infos_sepa="composantes_infos_separateurs";
$_DBC_comp_infos_sepa_info_id="$_DB_comp_infos_sepa.info_id";
$_DBC_comp_infos_sepa_ordre="$_DB_comp_infos_sepa.ordre";

$_DBU_comp_infos_sepa_info_id="info_id";
$_DBU_comp_infos_sepa_ordre="ordre";


// ============================================================
//             EDITEUR DE JUSTIFICATIFS
// ============================================================

// Justificatifs
$_DB_justifs="justificatifs";

$_DBC_justifs_id="$_DB_justifs.id";
$_DBC_justifs_comp_id="$_DB_justifs.composante_id";
$_DBC_justifs_intitule="$_DB_justifs.intitule";
$_DBC_justifs_titre="$_DB_justifs.titre";
$_DBC_justifs_texte="$_DB_justifs.texte";

$_DBU_justifs_id="id";
$_DBU_justifs_comp_id="composante_id";
$_DBU_justifs_intitule="intitule";
$_DBU_justifs_titre="titre";
$_DBU_justifs_texte="texte";

// Relation Justificatifs/Formations

$_DB_justifs_jf="justifs_formations";

$_DBC_justifs_jf_justif_id="$_DB_justifs_jf.justif_id";
$_DBC_justifs_jf_propspec_id="$_DB_justifs_jf.propspec_id";
$_DBC_justifs_jf_ordre="$_DB_justifs_jf.ordre";
$_DBC_justifs_jf_nationalite="$_DB_justifs_jf.condition_nationalite";

$_DBU_justifs_jf_justif_id="justif_id";
$_DBU_justifs_jf_propspec_id="propspec_id";
$_DBU_justifs_jf_ordre="ordre";
$_DBU_justifs_jf_nationalite="condition_nationalite";

// Fichiers attachés aux courriels

$_DB_justifs_fichiers="justifs_fichiers";

$_DBC_justifs_fichiers_id="$_DB_justifs_fichiers.id";
$_DBC_justifs_fichiers_comp_id="$_DB_justifs_fichiers.composante_id";
$_DBC_justifs_fichiers_nom="$_DB_justifs_fichiers.nom";

$_DBU_justifs_fichiers_id="id";
$_DBU_justifs_fichiers_comp_id="composante_id";
$_DBU_justifs_fichiers_nom="nom";

// Relation Fichiers/Formations

$_DB_justifs_ff="justifs_fichiers_formations";

$_DBC_justifs_ff_fichier_id="$_DB_justifs_ff.fichier_id";
$_DBC_justifs_ff_propspec_id="$_DB_justifs_ff.propspec_id";
$_DBC_justifs_ff_nationalite="$_DB_justifs_ff.condition_nationalite";

$_DBU_justifs_ff_fichier_id="fichier_id";
$_DBU_justifs_ff_propspec_id="propspec_id";
$_DBU_justifs_ff_nationalite="condition_nationalite";

// =======================================================================
// LANGUES

$_DB_langues="langues";
$_DBC_langues_id="$_DB_langues.id";
$_DBC_langues_candidat_id="$_DB_langues.candidat_id";
$_DBC_langues_langue="$_DB_langues.langue";
$_DBC_langues_niveau="$_DB_langues.niveau";
$_DBC_langues_annees="$_DB_langues.nb_annees";

$_DBU_langues_id="id";
$_DBU_langues_candidat_id="candidat_id";
$_DBU_langues_langue="langue";
$_DBU_langues_niveau="niveau";
$_DBU_langues_annees="nb_annees";


$_DB_langues_dip="langues_diplomes";
$_DBC_langues_dip_id="$_DB_langues_dip.id";
$_DBC_langues_dip_langue_id="$_DB_langues_dip.langue_id";
$_DBC_langues_dip_nom="$_DB_langues_dip.diplome";
$_DBC_langues_dip_annee="$_DB_langues_dip.annee";
$_DBC_langues_dip_resultat="$_DB_langues_dip.resultat";

$_DBU_langues_dip_id="id";
$_DBU_langues_dip_langue_id="langue_id";
$_DBU_langues_dip_nom="diplome";
$_DBU_langues_dip_annee="annee";
$_DBU_langues_dip_resultat="resultat";


// ============================================================
//                LETTRES
// ============================================================

// Table de base
$_DB_lettres="lettres";
$_DBC_lettres_id="$_DB_lettres.id";
$_DBC_lettres_titre="$_DB_lettres.titre";
$_DBC_lettres_comp_id="$_DB_lettres.composante_id";
$_DBC_lettres_logo="$_DB_lettres.fichier_logo";
$_DBC_lettres_txt_logo="$_DB_lettres.texte_logo";
$_DBC_lettres_txt_scol="$_DB_lettres.texte_scol";
$_DBC_lettres_txt_sign="$_DB_lettres.texte_signature";
$_DBC_lettres_largeur_logo="$_DB_lettres.largeur_logo";
$_DBC_lettres_flag_logo="$_DB_lettres.flag_fichier_logo";
$_DBC_lettres_flag_txt_logo="$_DB_lettres.flag_texte_logo";
$_DBC_lettres_flag_txt_scol="$_DB_lettres.flag_texte_scol";
$_DBC_lettres_flag_txt_sign="$_DB_lettres.flag_texte_signature";
$_DBC_lettres_flag_adr_cand="$_DB_lettres.flag_adresse_candidat";
$_DBC_lettres_choix_multiples="$_DB_lettres.choix_multiples";
$_DBC_lettres_flag_date="$_DB_lettres.flag_date";
$_DBC_lettres_flag_adr_pos="$_DB_lettres.flag_adr_pos";
$_DBC_lettres_adr_pos_x="$_DB_lettres.adr_pos_x";
$_DBC_lettres_adr_pos_y="$_DB_lettres.adr_pos_y";
$_DBC_lettres_flag_corps_pos="$_DB_lettres.flag_corps_pos";
$_DBC_lettres_corps_pos_x="$_DB_lettres.corps_pos_x";
$_DBC_lettres_corps_pos_y="$_DB_lettres.corps_pos_y";
$_DBC_lettres_langue="$_DB_lettres.lang";

$_DBU_lettres_id="id";
$_DBU_lettres_titre="titre";
$_DBU_lettres_comp_id="composante_id";
$_DBU_lettres_logo="fichier_logo";
$_DBU_lettres_txt_logo="texte_logo";
$_DBU_lettres_txt_scol="texte_scol";
$_DBU_lettres_txt_sign="texte_signature";
$_DBU_lettres_largeur_logo="largeur_logo";
$_DBU_lettres_flag_logo="flag_fichier_logo";
$_DBU_lettres_flag_txt_logo="flag_texte_logo";
$_DBU_lettres_flag_txt_scol="flag_texte_scol";
$_DBU_lettres_flag_txt_sign="flag_texte_signature";
$_DBU_lettres_flag_adr_cand="flag_adresse_candidat";
$_DBU_lettres_choix_multiples="choix_multiples";
$_DBU_lettres_flag_date="flag_date";
$_DBU_lettres_flag_adr_pos="flag_adr_pos";
$_DBU_lettres_adr_pos_x="adr_pos_x";
$_DBU_lettres_adr_pos_y="adr_pos_y";
$_DBU_lettres_flag_corps_pos="flag_corps_pos";
$_DBU_lettres_corps_pos_x="corps_pos_x";
$_DBU_lettres_corps_pos_y="corps_pos_y";
$_DBU_lettres_langue="lang";

// Paragraphes
$_DB_para="lettres_paragraphes";
$_DBC_para_lettre_id="$_DB_para.lettre_id";
$_DBC_para_ordre="$_DB_para.ordre";
$_DBC_para_texte="$_DB_para.texte";
$_DBC_para_gras="$_DB_para.gras";
$_DBC_para_italique="$_DB_para.italique";
$_DBC_para_align="$_DB_para.alignement";
$_DBC_para_taille="$_DB_para.taille";
$_DBC_para_marge_g="$_DB_para.marge_gauche";

$_DBU_para_lettre_id="lettre_id";
$_DBU_para_ordre="ordre";
$_DBU_para_texte="texte";
$_DBU_para_gras="gras";
$_DBU_para_italique="italique";
$_DBU_para_align="alignement";
$_DBU_para_taille="taille";
$_DBU_para_marge_g="marge_gauche";


// Encadrés
$_DB_encadre="lettres_encadres";
$_DBC_encadre_lettre_id="$_DB_encadre.lettre_id";
$_DBC_encadre_texte="$_DB_encadre.texte";
$_DBC_encadre_txt_align="$_DB_encadre.txt_align";
$_DBC_encadre_ordre="$_DB_encadre.ordre";

$_DBU_encadre_lettre_id="lettre_id";
$_DBU_encadre_texte="texte";
$_DBU_encadre_txt_align="txt_align";
$_DBU_encadre_ordre="ordre";

// Séparateurs
$_DB_sepa="lettres_separateurs";
$_DBC_sepa_lettre_id="$_DB_sepa.lettre_id";
$_DBC_sepa_ordre="$_DB_sepa.ordre";
$_DBC_sepa_nb_lignes="$_DB_sepa.nb_lignes";

$_DBU_sepa_lettre_id="lettre_id";
$_DBU_sepa_ordre="ordre";
$_DBU_sepa_nb_lignes="nb_lignes";

// Rapport lettres / décisions
$_DB_lettres_dec="lettres_decisions";
$_DBC_lettres_dec_lettre_id="$_DB_lettres_dec.lettre_id";
$_DBC_lettres_dec_dec_id="$_DB_lettres_dec.decision_id";

$_DBU_lettres_dec_lettre_id="lettre_id";
$_DBU_lettres_dec_dec_id="decision_id";

// Rapport lettres / filières
$_DB_lettres_propspec="lettres_propspec";
$_DBC_lettres_propspec_lettre_id="$_DB_lettres_propspec.lettre_id";
$_DBC_lettres_propspec_propspec_id="$_DB_lettres_propspec.propspec_id";

$_DBU_lettres_propspec_lettre_id="lettre_id";
$_DBU_lettres_propspec_propspec_id="filiere_id";

// Rapport lettres / groupes de formations à choix multiples
$_DB_lettres_groupes="lettres_groupes";
$_DBC_lettres_groupes_lettre_id="$_DB_lettres_groupes.lettre_id";
$_DBC_lettres_groupes_groupe_id="$_DB_lettres_groupes.groupe_id";

$_DBU_lettres_groupes_lettre_id="lettre_id";
$_DBU_lettres_groupes_groupe_id="groupe_id";


// ============================================================

$_DB_liste_langues="liste_langues";
$_DBC_liste_langues_id="$_DB_liste_langues.id";
$_DBC_liste_langues_langue="$_DB_liste_langues.langue";

$_DBU_liste_langues_id="id";
$_DBU_liste_langues_langue="langue";

// ============= Contenu des messages automatiques =======================

$_DB_messages="messages";
$_DBC_messages_comp_id="$_DB_messages.composante_id";
$_DBC_messages_type="$_DB_messages.type";
$_DBC_messages_statut="$_DB_messages.statut";
$_DBC_messages_decision_id="$_DB_messages.decision_id";
$_DBC_messages_contenu="$_DB_messages.contenu";
$_DBC_messages_actif="$_DB_messages.actif";

$_DBU_messages_comp_id="composante_id";
$_DBU_messages_type="type";
$_DBU_messages_statut="statut";
$_DBU_messages_decision_id="decision_id";
$_DBU_messages_contenu="contenu";
$_DBU_messages_actif="actif";

// =================== Messageries ============================

// Modèles des messages pour la partie gestion

$_DB_msg_modeles="msg_modeles";
$_DBC_msg_modeles_id="$_DB_msg_modeles.id";
$_DBC_msg_modeles_acces_id="$_DB_msg_modeles.acces_id";
$_DBC_msg_modeles_intitule="$_DB_msg_modeles.intitule";
$_DBC_msg_modeles_texte="$_DB_msg_modeles.texte";

$_DBU_msg_modeles_id="id";
$_DBU_msg_modeles_acces_id="acces_id";
$_DBU_msg_modeles_intitule="intitule";
$_DBU_msg_modeles_texte="texte";

// ================================

$_DB_motifs_refus="motifs_refus";
$_DBC_motifs_refus_id="$_DB_motifs_refus.id";
$_DBC_motifs_refus_motif="$_DB_motifs_refus.motif";
$_DBC_motifs_refus_motif_long="$_DB_motifs_refus.motif_long";
$_DBC_motifs_refus_exclusif="$_DB_motifs_refus.exclusif";
$_DBC_motifs_refus_comp_id="$_DB_motifs_refus.composante_id";

$_DBU_motifs_refus_id="id";
$_DBU_motifs_refus_motif="motif";
$_DBU_motifs_refus_motif_long="motif_long";
$_DBU_motifs_refus_exclusif="exclusif";
$_DBU_motifs_refus_comp_id="composante_id";

$_DB_note="note";
$_DBC_note_id="$_DB_note.id";
$_DBC_note_cursus_id="$_DB_note.cursus_id";
$_DBC_note_matiere="$_DB_note.matiere";
$_DBC_note_note="$_DB_note.note";
$_DBC_note_annee="$_DB_note.annee";
$_DBC_note_rang="$_DB_note.rang";

$_DBU_note_id="id";
$_DBU_note_cursus_id="cursus_id";
$_DBU_note_matiere="matiere";
$_DBU_note_note="note";
$_DBU_note_annee="annee";
$_DBU_note_rang="rang";

// Pays et nationalités avec codes INSEE et ISO 3166
$_DB_pays_nat_ii="pays_nationalites_iso_insee";
$_DBC_pays_nat_ii_iso="$_DB_pays_nat_ii.iso3166";
$_DBC_pays_nat_ii_insee="$_DB_pays_nat_ii.insee";
$_DBC_pays_nat_ii_pays="$_DB_pays_nat_ii.pays";
$_DBC_pays_nat_ii_nat="$_DB_pays_nat_ii.nationalite";

$_DBU_pays_nat_ii_iso="iso3166";
$_DBU_pays_nat_ii_insee="insee";
$_DBU_pays_nat_ii_pays="pays";
$_DBU_pays_nat_ii_nat="nationalite";

// Départements français
$_DB_departements_fr="departements_fr";

$_DBC_departements_fr_numero="$_DB_departements_fr.numero";
$_DBC_departements_fr_nom="$_DB_departements_fr.nom";

$_DBU_departements_fr_numero="numero";
$_DBU_departements_fr_nom="nom";


$_DB_propspec="propspec";
$_DBC_propspec_id="$_DB_propspec.id";
$_DBC_propspec_id_spec="$_DB_propspec.spec_id";
$_DBC_propspec_comp_id="$_DB_propspec.composante_id";
$_DBC_propspec_selective="$_DB_propspec.selective";
$_DBC_propspec_resp="$_DB_propspec.responsable";
$_DBC_propspec_mailresp="$_DB_propspec.responsable_email";
$_DBC_propspec_frais="$_DB_propspec.frais_dossiers";
$_DBC_propspec_annee="$_DB_propspec.annee_id";
$_DBC_propspec_entretiens="$_DB_propspec.entretiens";
$_DBC_propspec_finalite="$_DB_propspec.finalite";
$_DBC_propspec_active="$_DB_propspec.active";
$_DBC_propspec_info="$_DB_propspec.information";
$_DBC_propspec_manuelle="$_DB_propspec.manuelle";
$_DBC_propspec_affichage_decisions="$_DB_propspec.affichage_decisions";
$_DBC_propspec_flag_pass="$_DB_propspec.flag_pass";
$_DBC_propspec_pass="$_DB_propspec.pass";

$_DBU_propspec_id="id";
$_DBU_propspec_id_spec="spec_id";
$_DBU_propspec_comp_id="composante_id";
$_DBU_propspec_selective="selective";
$_DBU_propspec_resp="responsable";
$_DBU_propspec_mailresp="responsable_email";
$_DBU_propspec_frais="frais_dossiers";
$_DBU_propspec_annee="annee_id";
$_DBU_propspec_entretiens="entretiens";
$_DBU_propspec_finalite="finalite";
$_DBU_propspec_active="active";
$_DBU_propspec_info="information";
$_DBU_propspec_manuelle="manuelle";
$_DBU_propspec_affichage_decisions="affichage_decisions";
$_DBU_propspec_flag_pass="flag_pass";
$_DBU_propspec_pass="pass";

// =============================================================
//                     FILTRES INTER-FORMATIONS
// =============================================================

$_DB_filtres="filtres";

$_DBC_filtres_id="$_DB_filtres.id";
$_DBC_filtres_nom="$_DB_filtres.nom";
$_DBC_filtres_comp_id="$_DB_filtres.composante_id";
$_DBC_filtres_cond_propspec_id="$_DB_filtres.cond_propspec_id";
$_DBC_filtres_cond_annee_id="$_DB_filtres.cond_annee_id";
$_DBC_filtres_cond_mention_id="$_DB_filtres.cond_mention_id";
$_DBC_filtres_cond_spec_id="$_DB_filtres.cond_spec_id";
$_DBC_filtres_cond_finalite="$_DB_filtres.cond_finalite";
$_DBC_filtres_cible_propspec_id="$_DB_filtres.cible_propspec_id";
$_DBC_filtres_cible_annee_id="$_DB_filtres.cible_annee_id";
$_DBC_filtres_cible_mention_id="$_DB_filtres.cible_mention_id";
$_DBC_filtres_cible_spec_id="$_DB_filtres.cible_spec_id";
$_DBC_filtres_cible_finalite="$_DB_filtres.cible_finalite";
$_DBC_filtres_actif="$_DB_filtres.actif";

$_DBU_filtres_id="id";
$_DBU_filtres_nom="nom";
$_DBU_filtres_comp_id="composante_id";
$_DBU_filtres_cond_propspec_id="cond_propspec_id";
$_DBU_filtres_cond_annee_id="cond_annee_id";
$_DBU_filtres_cond_mention_id="cond_mention_id";
$_DBU_filtres_cond_spec_id="cond_spec_id";
$_DBU_filtres_cond_finalite="cond_finalite";
$_DBU_filtres_cible_propspec_id="cible_propspec_id";
$_DBU_filtres_cible_annee_id="cible_annee_id";
$_DBU_filtres_cible_mention_id="cible_mention_id";
$_DBU_filtres_cible_spec_id="cible_spec_id";
$_DBU_filtres_cible_finalite="cible_finalite";
$_DBU_filtres_actif="actif";




// =============================================================
//                           SESSIONS
// =============================================================

$_DB_session="sessions";

$_DBC_session_propspec_id="$_DB_session.propspec_id";
$_DBC_session_id="$_DB_session.id";
$_DBC_session_ouverture="$_DB_session.ouverture";
$_DBC_session_fermeture="$_DB_session.fermeture";
$_DBC_session_reception="$_DB_session.reception";
$_DBC_session_periode="$_DB_session.periode";

$_DBU_session_propspec_id="propspec_id";
$_DBU_session_id="id";
$_DBU_session_ouverture="ouverture";
$_DBU_session_fermeture="fermeture";
$_DBU_session_reception="reception";
$_DBU_session_periode="periode";

// Association entre une formation (ou une composante en fonction du champ "type") et une adresse mail
$_DB_courriels_propspec="courriels_formations";
$_DBC_courriels_propspec_acces_id="$_DB_courriels_propspec.acces_id";
$_DBC_courriels_propspec_propspec_id="$_DB_courriels_propspec.propspec_id";
$_DBC_courriels_propspec_type="$_DB_courriels_propspec.type";

$_DBU_courriels_propspec_acces_id="acces_id";
$_DBU_courriels_propspec_propspec_id="propspec_id";
$_DBU_courriels_propspec_type="type";

$_DB_specs="specialites";
$_DBC_specs_id="$_DB_specs.id";
$_DBC_specs_nom="$_DB_specs.nom";
$_DBC_specs_nom_court="$_DB_specs.nom_court";
$_DBC_specs_mention_id="$_DB_specs.mention_id";
$_DBC_specs_comp_id="$_DB_specs.composante_id";

$_DBU_specs_id="id";
$_DBU_specs_nom="nom";
$_DBU_specs_nom_court="nom_court";
$_DBU_specs_mention_id="mention_id";
$_DBU_specs_comp_id="composante_id";


$_DB_systeme="systeme";

$_DBC_systeme_titre_html="$_DB_systeme.titre_html";
$_DBC_systeme_titre_page="$_DB_systeme.titre_page";
$_DBC_systeme_ville="$_DB_systeme.ville";
$_DBC_systeme_url_candidat="$_DB_systeme.url_candidat";
$_DBC_systeme_url_gestion="$_DB_systeme.url_gestion";
$_DBC_systeme_meta="$_DB_systeme.meta";
$_DBC_systeme_admin="$_DB_systeme.admin";
$_DBC_systeme_signature_courriels="$_DB_systeme.signature_courriels";
$_DBC_systeme_signature_admin="$_DB_systeme.signature_admin";
$_DBC_systeme_courriel_admin="$_DB_systeme.courriel_admin";
$_DBC_systeme_info_liberte="$_DB_systeme.info_liberte";
$_DBC_systeme_limite_periode="$_DB_systeme.limite_periode";
$_DBC_systeme_limite_masse="$_DB_systeme.limite_masse";
$_DBC_systeme_defaut_decision="$_DB_systeme.defaut_decision";
$_DBC_systeme_defaut_motifs="$_DB_systeme.defaut_motifs";
$_DBC_systeme_max_rappels="$_DB_systeme.max_rappels";
$_DBC_systeme_rappel_delai_sup="$_DB_systeme.rappel_delai_sup";
$_DBC_systeme_debug="$_DB_systeme.debug";
$_DBC_systeme_debug_rappel_id="$_DB_systeme.debug_rappel_id";
$_DBC_systeme_debug_cursus="$_DB_systeme.debug_cursus";
$_DBC_systeme_debug_statut_prec="$_DB_systeme.debug_statut_prec";
$_DBC_systeme_debug_lock="$_DB_systeme.debug_lock";
$_DBC_systeme_debug_enregistrement="$_DB_systeme.debug_enregistrement";
$_DBC_systeme_debug_sujet="$_DB_systeme.debug_sujet";
$_DBC_systeme_erreur_sujet="$_DB_systeme.erreur_sujet";
$_DBC_systeme_arg_key="$_DB_systeme.arg_key";
$_DBC_systeme_assistance="$_DB_systeme.assistance";
$_DBC_systeme_ldap_actif="$_DB_systeme.ldap_actif";
$_DBC_systeme_ldap_host="$_DB_systeme.ldap_host";
$_DBC_systeme_ldap_port="$_DB_systeme.ldap_port";
$_DBC_systeme_ldap_proto="$_DB_systeme.ldap_proto";
$_DBC_systeme_ldap_id="$_DB_systeme.ldap_id";
$_DBC_systeme_ldap_pass="$_DB_systeme.ldap_pass";
$_DBC_systeme_ldap_basedn="$_DB_systeme.ldap_basedn";
$_DBC_systeme_ldap_attr_login="$_DB_systeme.ldap_attr_login";
$_DBC_systeme_ldap_attr_nom="$_DB_systeme.ldap_attr_nom";
$_DBC_systeme_ldap_attr_prenom="$_DB_systeme.ldap_attr_prenom";
$_DBC_systeme_ldap_attr_pass="$_DB_systeme.ldap_attr_pass";
$_DBC_systeme_ldap_attr_mail="$_DB_systeme.ldap_attr_mail";
$_DBC_systeme_courriel_support="$_DB_systeme.courriel_support";
$_DBC_systeme_courriel_noreply="$_DB_systeme.courriel_noreply";

$_DBU_systeme_titre_html="titre_html";
$_DBU_systeme_titre_page="titre_page";
$_DBU_systeme_ville="ville";
$_DBU_systeme_url_candidat="url_candidat";
$_DBU_systeme_url_gestion="url_gestion";
$_DBU_systeme_meta="meta";
$_DBU_systeme_admin="admin";
$_DBU_systeme_signature_courriels="signature_courriels";
$_DBU_systeme_signature_admin="signature_admin";
$_DBU_systeme_courriel_admin="courriel_admin";
$_DBU_systeme_info_liberte="info_liberte";
$_DBU_systeme_limite_periode="limite_periode";
$_DBU_systeme_limite_masse="limite_masse";
$_DBU_systeme_defaut_decision="defaut_decision";
$_DBU_systeme_defaut_motifs="defaut_motifs";
$_DBU_systeme_max_rappels="max_rappels";
$_DBU_systeme_rappel_delai_sup="rappel_delai_sup";
$_DBU_systeme_debug="debug";
$_DBU_systeme_debug_rappel_id="debug_rappel_id";
$_DBU_systeme_debug_cursus="debug_cursus";
$_DBU_systeme_debug_statut_prec="debug_statut_prec";
$_DBU_systeme_debug_lock="debug_lock";
$_DBU_systeme_debug_enregistrement="debug_enregistrement";
$_DBU_systeme_debug_sujet="debug_sujet";
$_DBU_systeme_erreur_sujet="erreur_sujet";
$_DBU_systeme_arg_key="arg_key";
$_DBU_systeme_assistance="assistance";
$_DBU_systeme_ldap_actif="ldap_actif";
$_DBU_systeme_ldap_host="ldap_host";
$_DBU_systeme_ldap_port="ldap_port";
$_DBU_systeme_ldap_proto="ldap_proto";
$_DBU_systeme_ldap_id="ldap_id";
$_DBU_systeme_ldap_pass="ldap_pass";
$_DBU_systeme_ldap_basedn="ldap_basedn";
$_DBU_systeme_ldap_attr_login="ldap_attr_login";
$_DBU_systeme_ldap_attr_nom="ldap_attr_nom";
$_DBU_systeme_ldap_attr_prenom="ldap_attr_prenom";
$_DBU_systeme_ldap_attr_pass="ldap_attr_pass";
$_DBU_systeme_ldap_attr_mail="ldap_attr_mail";
$_DBU_systeme_courriel_support="courriel_support";
$_DBU_systeme_courriel_noreply="courriel_noreply";


$_DB_traitement_masse="traitement_masse";

$_DBC_traitement_masse_id="$_DB_traitement_masse.id";
$_DBC_traitement_masse_partie="$_DB_traitement_masse.partie";
$_DBC_traitement_masse_cid="$_DB_traitement_masse.candidature_id";
$_DBC_traitement_masse_acces_id="$_DB_traitement_masse.acces_id";

$_DBU_traitement_masse_id="id";
$_DBU_traitement_masse_partie="partie";
$_DBU_traitement_masse_cid="candidature_id";
$_DBU_traitement_masse_acces_id="acces_id";


$_DB_mentions="mentions";
$_DBC_mentions_id="$_DB_mentions.id";
$_DBC_mentions_nom="$_DB_mentions.nom";
$_DBC_mentions_nom_court="$_DB_mentions.nom_court";
$_DBC_mentions_comp_id="$_DB_mentions.composante_id";

$_DBU_mentions_id="id";
$_DBU_mentions_nom="nom";
$_DBU_mentions_nom_court="nom_court";
$_DBU_mentions_comp_id="composante_id";

$_DB_universites="universites";
$_DBC_universites_id="$_DB_universites.id";
$_DBC_universites_nom="$_DB_universites.nom";
$_DBC_universites_adresse="$_DB_universites.adresse";
$_DBC_universites_img_dir="$_DB_universites.img_dir";
$_DBC_universites_css="$_DB_universites.css";
$_DBC_universites_couleur_texte_lettres="$_DB_universites.couleur_texte_lettres";

$_DBU_universites_id="id";
$_DBU_universites_nom="nom";
$_DBU_universites_adresse="adresse";
$_DBU_universites_img_dir="img_dir";
$_DBU_universites_css="css";
$_DBU_universites_couleur_texte_lettres="couleur_texte_lettres";


// Connexion à une base de données
function db_connect()
{
   $ssl_config=(isset($GLOBALS["__DB_SSLMODE"]) && $GLOBALS["__DB_SSLMODE"]!="") ? "sslmode=$GLOBALS[__DB_SSLMODE]" : "";

   $dbr=pg_connect("host=$GLOBALS[__DB_HOST] port=$GLOBALS[__DB_PORT] dbname=$GLOBALS[__DB_BASE] user=$GLOBALS[__DB_USER] password=$GLOBALS[__DB_PASS] $ssl_config");

   if($dbr==FALSE)
   {
      $err_file=$_SESSION['CURRENT_FILE'];
      $error_msg=pg_errormessage();
      
      if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
      {
         mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Fichier : $err_file\nErreur de connexion à la base de données ($error_msg)");
         die("Erreur de connexion à la base de données. Un courriel a été envoyé à l'administrateur.");
      }
      else
         die("Erreur de connexion à la base de données. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
   }
   // die("Erreur de connexion à la base de données : " . pg_errormessage());
   pg_set_client_encoding($dbr, "UTF-8");
   return $dbr;
}

// Récupération du nombre d'éléments contenus dans un résultat de requête à une bdd
function db_num_rows($result)
{
   // php < 4.2.0
   // return (pg_numrows($result));

   // php > 4.2.0
   return (pg_num_rows($result));
}

// DB_QUERY
// requête à une base de données
// ARGUMENTS :
// - dbr : ressource correspondant à une connexion à une bdd
// - query : requête
// RETOUR :
// - ressource correspondant au résultat de la requête
function db_query($dbr,$query)
{
   // php > 4.2.0
   $result=pg_query($dbr,$query);

   if($result==FALSE)
   {
      $err_file=$_SESSION['CURRENT_FILE'];
      // $error_msg=pg_result_error($result);
      $error_msg=pg_last_error($dbr);

      // TODO : EXPERIMENTAL
      // En cas d''erreur de type "duplicate key", on retente la requête 1 seconde après
      if(strstr($error_msg,"duplicate key"))
      {
         db_free_result($result);
         sleep(1);
         $result=pg_query($dbr,$query);

         if($result!=FALSE) // 2ème essai concluant : on sort normalement
            return $result;
         else
            $error_msg=pg_last_error($dbr);
      }

      // formatage, pour que ce soit propre dans les mails (suppression des tabs)
      $query=str_replace("\t","",$query);

      if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
      {
         $headers = "MIME-Version: 1.0\r\nFrom: $GLOBALS[__EMAIL_ADMIN]\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-transfer-encoding: 8bit\r\n\r\n";
         mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Fichier : $err_file\npg_query : erreur de requête à la base de données ($error_msg)\nRequête fautive : $query", $headers);
         die("Erreur de requête à la base de données. Un courriel a été envoyé à l'administrateur.");
      }
      else
         die("Erreur de requête à la base de données. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
   }

   return $result;
}


// Même fonction mais gestion différente des erreurs :
// la table étant verrouillée, si une erreur survient, on doit déverrouiller la table par un rollback
// Attention, vars.php doit être inclus : utilisation de la fonction new_id()

function db_locked_query($dbr, $table, $query)
{
   // Génération d'un nouvel identifiant
   if(stripos($query, "##NEW_ID##"))
   {
      $new_id=new_id();
      $query=str_ireplace("##NEW_ID##","$new_id", $query);
   }

   // php > 4.2.0
   if(!pg_query($dbr, "BEGIN WORK;"))
   {
      $err_file=$_SESSION['CURRENT_FILE'];
      $error_msg=pg_last_error($dbr);

      // formatage, pour que ce soit propre dans les mails (suppression des tabs)
      $query=str_replace("\t","",$query);
      
      if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
      {
         mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Fichier : $err_file\ndb_locked_query : Erreur BEGIN (table $table)\nRequête fautive : $query");
         die("Base de données : Transaction impossible. Un courriel a été envoyé à l'administrateur.");
      }
      else
         die("Base de données : Transaction impossible. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
   }

   if(!pg_query($dbr, "LOCK $table;"))
   {
      $err_file=$_SESSION['CURRENT_FILE'];
      $error_msg=pg_last_error($dbr);

      pg_query($dbr,"ROLLBACK WORK;");

      // formatage, pour que ce soit propre dans les mails (suppression des tabs)
      $query=str_replace("\t","",$query);
      
      if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
      {
         mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Fichier : $err_file\ndb_locked_query : Erreur LOCK (table $table)\nRequête fautive : $query");
         die("Base de données : Transaction impossible. Un courriel a été envoyé à l'administrateur.");
      }
      else
         die("Base de données : Transaction impossible. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
   }

   // Verrouillage OK : on envoie la requête
   if(!pg_query($dbr, $query))
   {
      $err_file=$_SESSION['CURRENT_FILE'];
      $error_msg=pg_last_error($dbr);

      pg_query($dbr,"ROLLBACK WORK;");

      // formatage, pour que ce soit propre dans les mails (suppression des tabs)
      $query=str_replace("\t","",$query);
      
      if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
      {
         mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Fichier : $err_file\npg_query : erreur de requête à la base de données ($error_msg)\nRequête fautive : $query");
         die("Erreur de requête à la base de données. Un courriel a été envoyé à l'administrateur.");
      }
      else
         die("Erreur de requête à la base de données. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
   }

   // tout s'est bien passé : on commit et on envoie l'identifiant généré, si nécessaire
   if(!pg_query($dbr, "COMMIT WORK;"))
   {
      $err_file=$_SESSION['CURRENT_FILE'];
      $error_msg=pg_last_error($dbr);

      pg_query($dbr,"ROLLBACK WORK;");

      // formatage, pour que ce soit propre dans les mails (suppression des tabs)
      $query=str_replace("\t","",$query);
      
      if(array_key_exists("__EMAIL_ADMIN", $GLOBALS) && trim($GLOBALS["__EMAIL_ADMIN"])!="")
      {
         mail($GLOBALS["__EMAIL_ADMIN"],$GLOBALS["__ERREUR_SUJET"], "Fichier : $err_file\ndb_locked_query : Erreur COMMIT (table $table)\nRequête fautive : $query");
         die("Base de données : Transaction impossible. Un courriel a été envoyé à l'administrateur.");
      }
      else
         die("Base de données : Transaction impossible. Aucun courriel n'a pu être envoyé à l'administrateur car aucune adresse électronique n'a été configurée.");
   }
   

   if(isset($new_id))
      return $new_id;
}


// DB_FETCH_ROW
// récupération d'une partie du résultat d'une requête à une bdd
// ARGUMENTS :
// - result=variable contenant la totalité du résultat
// - i=ligne à récupérer
// RETOUR :
// - array contenant la ligne en question
function db_fetch_row($result, $i)
{
   return (pg_fetch_row($result, $i));
}

// Récupération d'un champ particulier (rang x, en comptant à partir de 0)
function db_fetch_result($result, $i, $x)
{
   return (pg_fetch_result($result, $i, $x));
}

// FETCH ARRAY
function db_fetch_array($result,$i,$method)
{
   return (pg_fetch_array($result,$i,$method));
}


// FETCH ALL
function db_fetch_all($result)
{
   return (pg_fetch_all($result));
}

// Libération des ressources allouées à une requête bdd
function db_free_result($result)
{
   // php < 4.2.0
   // pg_freeresult($result);

   // php > 4.2.0
   pg_free_result($result);
}

// Statut d'une connexion : 0 si OK, 1 sinon
function db_connection_status($db)
{
   if(pg_connection_status($db) == PGSQL_CONNECTION_OK)
      return 0;
   else
      return 1;
}

// Nettoyage des chaînes de caractères
function db_escape_string($db, $str)
{
   return pg_escape_string($db, $str);
}

// fermeture d'une connexion à une bdd
function db_close($db)
{
   pg_close($db);
}

// MISES A JOUR DU SCHEMA

if(is_file("$GLOBALS[__INCLUDE_DIR_ABS]/update_db.php"))
{
   include "$GLOBALS[__INCLUDE_DIR_ABS]/update_db.php";
   @unlink("$GLOBALS[__INCLUDE_DIR_ABS]/update_db.php");
}
?>
