--
-- PostgreSQL database dump
--

SET client_encoding = 'LATIN1';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

ALTER TABLE ONLY public.traitement_masse DROP CONSTRAINT traitement_masse_candidature_id_fkey;
ALTER TABLE ONLY public.specialites DROP CONSTRAINT speclialites_mention_id_fkey;
ALTER TABLE ONLY public.specialites DROP CONSTRAINT specialites_composante_id_fkey;
ALTER TABLE ONLY public.sessions DROP CONSTRAINT sessions_propspec_id_fkey;
ALTER TABLE ONLY public.propspec DROP CONSTRAINT proprietes_specialites_composante_id_fkey;
ALTER TABLE ONLY public.msg_modeles DROP CONSTRAINT msg_modeles_acces_id_fkey;
ALTER TABLE ONLY public.motifs_refus DROP CONSTRAINT motifs_refus_composante_id_fkey;
ALTER TABLE ONLY public.moduleapogee_numeros_opi DROP CONSTRAINT moduleapogee_numeros_opi_candidature_id_fkey;
ALTER TABLE ONLY public.moduleapogee_formations DROP CONSTRAINT moduleapogee_formations_propspec_id_fkey;
ALTER TABLE ONLY public.moduleapogee_codes_laisser_passer DROP CONSTRAINT moduleapogee_codes_laisser_passer_candidature_id_fkey;
ALTER TABLE ONLY public.moduleapogee_config DROP CONSTRAINT moduleapogee_code_universite_composante_id_fkey;
ALTER TABLE ONLY public.moduleapogee_centres_gestion DROP CONSTRAINT moduleapogee_centres_gestion_composante_id_fkey;
ALTER TABLE ONLY public.moduleapogee_activation DROP CONSTRAINT moduleapogee_activation_composante_id_fkey;
ALTER TABLE ONLY public.messages DROP CONSTRAINT messages_composante_id_fkey;
ALTER TABLE ONLY public.mentions DROP CONSTRAINT mentions_composante_id_fkey;
ALTER TABLE ONLY public.lettres_separateurs DROP CONSTRAINT lettres_separateurs_lettre_id_fkey;
ALTER TABLE ONLY public.lettres_propspec DROP CONSTRAINT lettres_propspec_propspec_id_fkey;
ALTER TABLE ONLY public.lettres_propspec DROP CONSTRAINT lettres_propspec_lettre_id_fkey;
ALTER TABLE ONLY public.lettres_paragraphes DROP CONSTRAINT lettres_paragraphes_lettre_id_fkey;
ALTER TABLE ONLY public.lettres_encadres DROP CONSTRAINT lettres_encadres_lettre_id_fkey;
ALTER TABLE ONLY public.lettres DROP CONSTRAINT lettres_composante_id_fkey;
ALTER TABLE ONLY public.lettres_decisions DROP CONSTRAINT lettre_decisions_lettre_id_fkey;
ALTER TABLE ONLY public.lettres_decisions DROP CONSTRAINT lettre_decisions_decision_id_fkey;
ALTER TABLE ONLY public.langues_diplomes DROP CONSTRAINT langues_diplomes_langue_id_fkey;
ALTER TABLE ONLY public.justifs_formations DROP CONSTRAINT justifs_formations_propspec_id_fkey;
ALTER TABLE ONLY public.justifs_formations DROP CONSTRAINT justifs_formations_justif_id_fkey;
ALTER TABLE ONLY public.justifs_fichiers_formations DROP CONSTRAINT justifs_fichiers_formations_propspec_id_fkey;
ALTER TABLE ONLY public.justifs_fichiers_formations DROP CONSTRAINT justifs_fichiers_formations_fichier_id_fkey;
ALTER TABLE ONLY public.justifs_fichiers DROP CONSTRAINT justifs_fichiers_composante_id_fkey;
ALTER TABLE ONLY public.justificatifs DROP CONSTRAINT justificatifs_composante_id_fkey;
ALTER TABLE ONLY public.propspec DROP CONSTRAINT id_spec_fk;
ALTER TABLE ONLY public.propspec DROP CONSTRAINT id_annee_fk;
ALTER TABLE ONLY public.groupes_specialites DROP CONSTRAINT groupes_spec_fkey;
ALTER TABLE ONLY public.filtres DROP CONSTRAINT filtres_composante_id_fkey;
ALTER TABLE ONLY public.droits_formations DROP CONSTRAINT droits_formations_propspec_id_fkey;
ALTER TABLE ONLY public.droits_formations DROP CONSTRAINT droits_formations_acces_id_fkey;
ALTER TABLE ONLY public.dossiers_elements_contenu DROP CONSTRAINT dossiers_elements_contenu_element_id_fkey;
ALTER TABLE ONLY public.dossiers_elements_contenu DROP CONSTRAINT dossiers_elements_contenu_composante_id_fkey;
ALTER TABLE ONLY public.dossiers_elements_contenu DROP CONSTRAINT dossiers_elements_contenu_candidat_id_fkey;
ALTER TABLE ONLY public.dossiers_elements DROP CONSTRAINT dossiers_elements_composante_id_fkey;
ALTER TABLE ONLY public.dossiers_elements_choix DROP CONSTRAINT dossiers_elements_choix_element_id_fkey;
ALTER TABLE ONLY public.dossiers_ef DROP CONSTRAINT dossiers_ef_propspec_id_fkey;
ALTER TABLE ONLY public.dossiers_ef DROP CONSTRAINT dossiers_ef_element_id_fkey;
ALTER TABLE ONLY public.decisions_composantes DROP CONSTRAINT decisions_composantes_decision_id_fkey;
ALTER TABLE ONLY public.decisions_composantes DROP CONSTRAINT decisions_composantes_composante_id_fkey;
ALTER TABLE ONLY public.cursus_justificatifs DROP CONSTRAINT cursus_justificatifs_cursus_id_fkey;
ALTER TABLE ONLY public.cursus_justificatifs DROP CONSTRAINT cursus_justificatifs_composante_id_fkey;
ALTER TABLE ONLY public.courriels_formations DROP CONSTRAINT courriels_formations_acces_id_fkey;
ALTER TABLE ONLY public.composantes DROP CONSTRAINT composantes_univ_id_fkey;
ALTER TABLE ONLY public.composantes_infos_separateurs DROP CONSTRAINT composantes_infos_separateurs_info_id_fkey;
ALTER TABLE ONLY public.composantes_infos_paragraphes DROP CONSTRAINT composantes_infos_paragraphes_info_id_fkey;
ALTER TABLE ONLY public.composantes_infos_fichiers DROP CONSTRAINT composantes_infos_fichiers_info_id_fkey;
ALTER TABLE ONLY public.composantes_infos_encadres DROP CONSTRAINT composantes_infos_encadres_info_id_fkey;
ALTER TABLE ONLY public.composantes_infos DROP CONSTRAINT composantes_infos_composante_id_fkey;
ALTER TABLE ONLY public.commissions DROP CONSTRAINT commissions_propspec_id_fkey;
ALTER TABLE ONLY public.candidature DROP CONSTRAINT candidature_propspec_fkey;
ALTER TABLE ONLY public.candidature DROP CONSTRAINT candidature_decision_fkey;
ALTER TABLE ONLY public.acces_composantes DROP CONSTRAINT acces_composantes_id_composante_fkey;
ALTER TABLE ONLY public.acces_composantes DROP CONSTRAINT acces_composantes_id_acces_fkey;
ALTER TABLE ONLY public.acces DROP CONSTRAINT acces_composante_id_fkey;
ALTER TABLE ONLY public.acces_candidats_lus DROP CONSTRAINT acces_candidats_lus_candidat_id_fkey;
ALTER TABLE ONLY public.acces_candidats_lus DROP CONSTRAINT acces_candidats_lus_acces_id_fkey;
ALTER TABLE ONLY public.langues DROP CONSTRAINT "$1";
ALTER TABLE ONLY public.infos_complementaires DROP CONSTRAINT "$1";
ALTER TABLE ONLY public.cursus DROP CONSTRAINT "$1";
ALTER TABLE ONLY public.candidature DROP CONSTRAINT "$1";
DROP TRIGGER "RI_ConstraintTrigger_49485" ON public.cursus;
DROP TRIGGER "RI_ConstraintTrigger_49484" ON public.cursus;
DROP TRIGGER "RI_ConstraintTrigger_49483" ON public.note;
DROP TRIGGER "RI_ConstraintTrigger_49477" ON public.cursus;
DROP TRIGGER "RI_ConstraintTrigger_49476" ON public.cursus;
DROP TRIGGER "RI_ConstraintTrigger_49475" ON public.note;
DROP INDEX public.unique_intitule;
DROP INDEX public.moduleapogee_numeros_opi_index_cand_id;
DROP INDEX public.moduleapogee_codes_laisser_passer_index_cand_id;
DROP INDEX public.index_historique_candidat_id;
DROP INDEX public.index_historique;
DROP INDEX public.historique_ip_index;
DROP INDEX public.historique_g_nom_index;
DROP INDEX public.historique_date_index;
DROP INDEX public.historique_candidat_id_index;
DROP INDEX public.historique_acces_id_index;
DROP INDEX public.cursus_mentions_intitule_key;
DROP INDEX public.candidature_candidat_id_index;
DROP INDEX public.annees_annee_key;
ALTER TABLE ONLY public.universites DROP CONSTRAINT universites_pkey;
ALTER TABLE ONLY public.traitement_masse DROP CONSTRAINT traitement_masse_pkey;
ALTER TABLE ONLY public.specialites DROP CONSTRAINT specialites_pkey;
ALTER TABLE ONLY public.sessions DROP CONSTRAINT sessions_pkey;
ALTER TABLE ONLY public.lettres_separateurs DROP CONSTRAINT separateurs_pkey;
ALTER TABLE ONLY public.propspec DROP CONSTRAINT proprietes_specialites_pkey;
ALTER TABLE ONLY public.lettres_paragraphes DROP CONSTRAINT paragraphes_pkey;
ALTER TABLE ONLY public.note DROP CONSTRAINT note_pkey;
ALTER TABLE ONLY public.msg_modeles DROP CONSTRAINT msg_modeles_pkey;
ALTER TABLE ONLY public.motifs_refus DROP CONSTRAINT motifs_refus_pkey;
ALTER TABLE ONLY public.moduleapogee_numeros_opi DROP CONSTRAINT moduleapogee_numeros_opi_pkey;
ALTER TABLE ONLY public.moduleapogee_formations DROP CONSTRAINT moduleapogee_formations_pkey;
ALTER TABLE ONLY public.moduleapogee_centres_gestion DROP CONSTRAINT moduleapogee_centres_gestion_pkey;
ALTER TABLE ONLY public.messages DROP CONSTRAINT messages_pkey;
ALTER TABLE ONLY public.mentions DROP CONSTRAINT mentions_pkey;
ALTER TABLE ONLY public.liste_langues DROP CONSTRAINT liste_langues_pkey;
ALTER TABLE ONLY public.lettres DROP CONSTRAINT lettres_pkey;
ALTER TABLE ONLY public.langues DROP CONSTRAINT langues_pkey;
ALTER TABLE ONLY public.langues_diplomes DROP CONSTRAINT langues_diplomes_pkey;
ALTER TABLE ONLY public.justifs_fichiers DROP CONSTRAINT justifs_fichiers_pkey;
ALTER TABLE ONLY public.justificatifs DROP CONSTRAINT justificatifs_pkey;
ALTER TABLE ONLY public.infos_complementaires DROP CONSTRAINT infos_complementaires_pkey;
ALTER TABLE ONLY public.filtres DROP CONSTRAINT filtres_pkey;
ALTER TABLE ONLY public.lettres_encadres DROP CONSTRAINT encadres_pkey;
ALTER TABLE ONLY public.droits_formations DROP CONSTRAINT droits_formations_pkey;
ALTER TABLE ONLY public.dossiers_elements DROP CONSTRAINT dossiers_elements_pkey;
ALTER TABLE ONLY public.dossiers_elements_choix DROP CONSTRAINT dossiers_elements_choix_pkey;
ALTER TABLE ONLY public.dossiers_ef DROP CONSTRAINT dossiers_ef_pkey;
ALTER TABLE ONLY public.diplomes_bac DROP CONSTRAINT diplomes_bac_pkey;
ALTER TABLE ONLY public.departements_fr DROP CONSTRAINT departements_fr_pkey;
ALTER TABLE ONLY public.decisions DROP CONSTRAINT decisions_pkey;
ALTER TABLE ONLY public.cursus DROP CONSTRAINT cursus_pkey;
ALTER TABLE ONLY public.cursus_mentions DROP CONSTRAINT cursus_mentions_pkey;
ALTER TABLE ONLY public.cursus_diplomes DROP CONSTRAINT cursus_diplomes_pkey;
ALTER TABLE ONLY public.cursus_diplomes_apogee DROP CONSTRAINT cursus_diplomes_apogee_pkey;
ALTER TABLE ONLY public.composantes DROP CONSTRAINT composantes_pkey;
ALTER TABLE ONLY public.composantes_infos DROP CONSTRAINT composantes_infos_pkey;
ALTER TABLE ONLY public.commissions DROP CONSTRAINT commissions_pkey;
ALTER TABLE ONLY public.candidature DROP CONSTRAINT candidature_pkey;
ALTER TABLE ONLY public.candidat DROP CONSTRAINT candidat_pkey;
ALTER TABLE ONLY public.candidat DROP CONSTRAINT candidat_identifiant_key;
ALTER TABLE ONLY public.annees DROP CONSTRAINT annees_pkey;
ALTER TABLE ONLY public.acces DROP CONSTRAINT acces_pkey;
ALTER TABLE ONLY public.acces DROP CONSTRAINT acces_login_key;
ALTER TABLE ONLY public.acces_composantes DROP CONSTRAINT acces_composantes_pkey;
DROP TABLE public.universites;
DROP TABLE public.traitement_masse;
DROP TABLE public.systeme;
DROP TABLE public.specialites;
DROP TABLE public.sessions;
DROP TABLE public.propspec;
DROP TABLE public.pays_nationalites_iso_insee;
DROP TABLE public.note;
DROP TABLE public.msg_modeles;
DROP SEQUENCE public.motifs_refus_id_seq;
DROP TABLE public.motifs_refus;
DROP TABLE public.moduleapogee_numeros_opi;
DROP TABLE public.moduleapogee_formations;
DROP TABLE public.moduleapogee_config;
DROP TABLE public.moduleapogee_codes_laisser_passer;
DROP TABLE public.moduleapogee_centres_gestion;
DROP TABLE public.moduleapogee_activation;
DROP TABLE public.messages;
DROP TABLE public.mentions;
DROP SEQUENCE public.liste_langues_id_seq;
DROP TABLE public.liste_langues;
DROP TABLE public.lettres_separateurs;
DROP TABLE public.lettres_propspec;
DROP TABLE public.lettres_paragraphes;
DROP TABLE public.lettres_encadres;
DROP TABLE public.lettres_decisions;
DROP TABLE public.lettres;
DROP TABLE public.langues_diplomes;
DROP TABLE public.langues;
DROP TABLE public.justifs_formations;
DROP TABLE public.justifs_fichiers_formations;
DROP TABLE public.justifs_fichiers;
DROP TABLE public.justificatifs;
DROP TABLE public.infos_complementaires;
DROP TABLE public.historique;
DROP TABLE public.groupes_specialites;
DROP TABLE public.filtres;
DROP TABLE public.droits_formations;
DROP TABLE public.dossiers_elements_contenu;
DROP TABLE public.dossiers_elements_choix;
DROP TABLE public.dossiers_elements;
DROP TABLE public.dossiers_ef;
DROP TABLE public.diplomes_bac;
DROP TABLE public.departements_fr;
DROP TABLE public.decisions_composantes;
DROP TABLE public.decisions;
DROP SEQUENCE public.cursus_mentions_id_seq;
DROP TABLE public.cursus_mentions;
DROP TABLE public.cursus_justificatifs;
DROP SEQUENCE public.cursus_diplomes_id_seq;
DROP TABLE public.cursus_diplomes_apogee;
DROP TABLE public.cursus_diplomes;
DROP TABLE public.cursus;
DROP TABLE public.courriels_formations;
DROP TABLE public.composantes_infos_separateurs;
DROP TABLE public.composantes_infos_paragraphes;
DROP TABLE public.composantes_infos_fichiers;
DROP TABLE public.composantes_infos_encadres;
DROP TABLE public.composantes_infos;
DROP TABLE public.composantes;
DROP TABLE public.commissions;
DROP TABLE public.candidature;
DROP TABLE public.candidat;
DROP SEQUENCE public.annees_id_seq;
DROP TABLE public.annees;
DROP TABLE public.acces_composantes;
DROP TABLE public.acces_candidats_lus;
DROP TABLE public.acces;
DROP FUNCTION public.new_id();
DROP FUNCTION public.max_annee(bigint, text);
DROP SCHEMA public;
--
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA public;


--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'Standard public schema';


--
-- Name: max_annee(bigint, text); Type: FUNCTION; Schema: public; Owner: globdb
--

CREATE FUNCTION max_annee(bigint, text) RETURNS integer
    AS $_$select max(annee) from cursus where candidat_id=$1 and cursus.pays ilike $2$_$
    LANGUAGE sql;


--
-- Name: new_id(); Type: FUNCTION; Schema: public; Owner: globdb
--

CREATE FUNCTION new_id() RETURNS text
    AS $$select to_char(localtimestamp,'YYMMDDHH24MISSUS')$$
    LANGUAGE sql;


SET default_tablespace = '';

SET default_with_oids = true;

--
-- Name: acces; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE acces (
    id bigint NOT NULL,
    composante_id bigint,
    nom text NOT NULL,
    prenom text NOT NULL,
    login text NOT NULL,
    pass text NOT NULL,
    courriel text NOT NULL,
    niveau integer DEFAULT 0 NOT NULL,
    filtre text,
    reception_msg_scol boolean DEFAULT true,
    absence_debut integer DEFAULT 0,
    absence_fin integer DEFAULT 0,
    absence_message text DEFAULT ''::text,
    absence_active boolean DEFAULT false,
    signature_txt text DEFAULT ''::text,
    signature_active boolean DEFAULT true,
    reception_msg_systeme boolean DEFAULT false,
    source smallint DEFAULT '0'
);


--
-- Name: acces_candidats_lus; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE acces_candidats_lus (
    acces_id bigint,
    candidat_id bigint,
    periode text DEFAULT ''::text
);


--
-- Name: acces_composantes; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE acces_composantes (
    id_acces bigint NOT NULL,
    id_composante bigint NOT NULL
);


--
-- Name: annees; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE annees (
    id integer DEFAULT nextval('"annees_id_seq"'::text) NOT NULL,
    annee text,
    annee_longue text,
    ordre smallint
);


--
-- Name: annees_id_seq; Type: SEQUENCE; Schema: public; Owner: globdb
--

CREATE SEQUENCE annees_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: candidat; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE candidat (
    id bigint NOT NULL,
    civilite text NOT NULL,
    nom text NOT NULL,
    prenom text NOT NULL,
    date_naissance integer NOT NULL,
    nationalite text,
    telephone text,
    adresse text,
    numero_ine text,
    email text,
    identifiant text,
    code_acces text,
    connexion integer,
    lieu_naissance text,
    prenom2 text,
    derniere_ip text,
    dernier_host text,
    pays_naissance text,
    adr_cp text,
    adr_ville text,
    adr_pays text,
    dernier_user_agent text,
    derniere_erreur_code text DEFAULT ''::text,
    manuelle smallint,
    cursus_en_cours smallint,
    "lock" bigint,
    lockdate integer,
    dpt_naissance text DEFAULT ''::text,
    deja_inscrit smallint DEFAULT 0::smallint,
    annee_premiere_inscription text DEFAULT ''::text,
    annee_bac text DEFAULT ''::text,
    serie_bac text DEFAULT ''::text,
    nom_naissance text DEFAULT ''::text,
    telephone_portable text DEFAULT ''::text
);


--
-- Name: candidature; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE candidature (
    id bigint NOT NULL,
    candidat_id bigint NOT NULL,
    propspec_id bigint NOT NULL,
    ordre integer NOT NULL,
    statut integer NOT NULL,
    motivation_decision text,
    traitee_par bigint,
    ordre_spec integer,
    groupe_spec integer,
    date_decision text,
    decision integer,
    recours integer,
    liste_attente text,
    transmission_dossier text,
    vap_flag integer,
    masse integer,
    talon_reponse integer,
    statut_frais integer DEFAULT 0,
    entretien_date integer,
    entretien_heure text,
    entretien_lieu text,
    entretien_salle text,
    date_statut integer DEFAULT 0,
    date_prise_decision integer DEFAULT 0,
    periode text,
    session_id integer,
    "lock" smallint,
    lockdate integer,
    rappels smallint DEFAULT 0::smallint,
    notification_envoyee smallint DEFAULT 0::smallint
);


--
-- Name: commissions; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE commissions (
    propspec_id bigint NOT NULL,
    id integer NOT NULL,
    date integer NOT NULL,
    periode text NOT NULL
);


--
-- Name: composantes; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE composantes (
    id bigint NOT NULL,
    nom text NOT NULL,
    univ_id integer,
    adresse text NOT NULL,
    contact text NOT NULL,
    directeur text,
    scolarite text,
    defaut_fichier_logo text,
    defaut_texte_scol text,
    defaut_texte_signature text,
    defaut_texte_logo text,
    delai_verrouillage integer,
    defaut_largeur_logo integer,
    courriel_scolarite text,
    limite_cand_nombre integer,
    limite_cand_annee integer,
    limite_cand_annee_mention integer,
    gestion_motifs integer,
    defaut_entretien_salle text,
    defaut_entretien_lieu text,
    www text,
    defaut_affichage_decisions smallint DEFAULT 1,
    defaut_adr_pos_x integer DEFAULT 109,
    defaut_adr_pos_y integer DEFAULT 42,
    defaut_corps_pos_x integer DEFAULT 60,
    defaut_corps_pos_y integer DEFAULT 78,
    avertir_decision smallint DEFAULT 0::smallint
);


--
-- Name: composantes_infos; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE composantes_infos (
    id bigint NOT NULL,
    composante_id bigint
);


--
-- Name: composantes_infos_encadres; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE composantes_infos_encadres (
    info_id bigint,
    texte text,
    txt_align integer DEFAULT 0,
    ordre integer
);


--
-- Name: composantes_infos_fichiers; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE composantes_infos_fichiers (
    info_id bigint,
    texte text,
    fichier text,
    ordre integer
);


--
-- Name: composantes_infos_paragraphes; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE composantes_infos_paragraphes (
    info_id bigint,
    ordre integer,
    texte text,
    gras integer DEFAULT 0,
    italique integer DEFAULT 0,
    alignement integer DEFAULT 0,
    taille integer DEFAULT 10
);


--
-- Name: composantes_infos_separateurs; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE composantes_infos_separateurs (
    info_id bigint,
    ordre integer
);


--
-- Name: courriels_formations; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE courriels_formations (
    acces_id bigint,
    propspec_id bigint,
    "type" text DEFAULT 'F'::text
);


--
-- Name: cursus; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE cursus (
    id bigint NOT NULL,
    candidat_id bigint NOT NULL,
    diplome text NOT NULL,
    intitule text NOT NULL,
    specialite text,
    annee integer,
    ecole text,
    ville text,
    pays text,
    note_moyenne text,
    mention text,
    rang text
);


--
-- Name: cursus_diplomes; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE cursus_diplomes (
    id integer DEFAULT nextval('"cursus_diplomes_id_seq"'::text) NOT NULL,
    intitule text,
    niveau integer
);


--
-- Name: cursus_diplomes_apogee; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE cursus_diplomes_apogee (
    code text NOT NULL,
    libelle_long text,
    libelle_court text
);


--
-- Name: cursus_diplomes_id_seq; Type: SEQUENCE; Schema: public; Owner: globdb
--

CREATE SEQUENCE cursus_diplomes_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: cursus_justificatifs; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE cursus_justificatifs (
    cursus_id bigint,
    composante_id bigint,
    statut integer DEFAULT 0,
    "precision" text,
    periode text
);


--
-- Name: cursus_mentions; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE cursus_mentions (
    id integer DEFAULT nextval('"cursus_mentions_id_seq"'::text) NOT NULL,
    intitule text
);


--
-- Name: cursus_mentions_id_seq; Type: SEQUENCE; Schema: public; Owner: globdb
--

CREATE SEQUENCE cursus_mentions_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: decisions; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE decisions (
    id integer NOT NULL,
    texte text NOT NULL,
    selective integer,
    non_selective integer
);


--
-- Name: decisions_composantes; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE decisions_composantes (
    composante_id bigint,
    decision_id integer
);


--
-- Name: departements_fr; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE departements_fr (
    numero text NOT NULL,
    nom text NOT NULL
);


--
-- Name: diplomes_bac; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE diplomes_bac (
    code_bac text NOT NULL,
    intitule text
);


--
-- Name: dossiers_ef; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE dossiers_ef (
    element_id bigint NOT NULL,
    propspec_id bigint NOT NULL,
    ordre smallint
);


--
-- Name: dossiers_elements; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE dossiers_elements (
    id bigint NOT NULL,
    "type" integer,
    intitule text,
    paragraphe text,
    nb_lignes integer,
    condition_vap integer,
    composante_id bigint,
    demande_unique boolean,
    obligatoire boolean,
    recapitulatif boolean DEFAULT true,
    nb_choix_min smallint DEFAULT 0::smallint,
    nb_choix_max smallint DEFAULT 0::smallint,
    nouvelle_page boolean DEFAULT false,
    extractions boolean DEFAULT false
);


--
-- Name: dossiers_elements_choix; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE dossiers_elements_choix (
    id bigint NOT NULL,
    element_id bigint,
    texte text,
    ordre integer
);


--
-- Name: dossiers_elements_contenu; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE dossiers_elements_contenu (
    candidat_id bigint,
    element_id bigint,
    composante_id bigint,
    paragraphe text,
    propspec_id bigint,
    periode text NOT NULL
);


--
-- Name: droits_formations; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE droits_formations (
    acces_id bigint NOT NULL,
    propspec_id bigint NOT NULL,
    droits text
);


--
-- Name: filtres; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE filtres (
    id bigint NOT NULL,
    nom text,
    composante_id bigint,
    cond_propspec_id bigint,
    cond_annee_id integer,
    cond_mention_id bigint,
    cond_spec_id bigint,
    cond_finalite smallint,
    cible_propspec_id bigint,
    cible_annee_id integer,
    cible_mention_id bigint,
    cible_spec_id bigint,
    cible_finalite smallint,
    actif smallint DEFAULT 1::smallint
);


--
-- Name: groupes_specialites; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE groupes_specialites (
    propspec_id bigint NOT NULL,
    groupe integer,
    ajout_automatique boolean DEFAULT false,
    nom text DEFAULT ''::text,
    dates_communes boolean DEFAULT false
);


--
-- Name: historique; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE historique (
    date bigint NOT NULL,
    ip text,
    host text,
    acces_id bigint,
    g_nom text,
    g_prenom text,
    g_email text,
    composante_id bigint,
    niveau integer,
    candidat_id bigint,
    c_nom text,
    c_prenom text,
    c_email text,
    element_id bigint,
    type_evenement text,
    evenement text,
    requete text
);


--
-- Name: infos_complementaires; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE infos_complementaires (
    id bigint NOT NULL,
    candidat_id bigint NOT NULL,
    texte text NOT NULL,
    annee text,
    duree text
);


--
-- Name: justificatifs; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE justificatifs (
    id bigint NOT NULL,
    composante_id bigint,
    intitule text,
    titre text,
    texte text
);


--
-- Name: justifs_fichiers; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE justifs_fichiers (
    id bigint NOT NULL,
    composante_id bigint,
    nom text
);


--
-- Name: justifs_fichiers_formations; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE justifs_fichiers_formations (
    fichier_id bigint,
    propspec_id bigint,
    condition_nationalite smallint
);


--
-- Name: justifs_formations; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE justifs_formations (
    justif_id bigint,
    propspec_id bigint,
    ordre smallint,
    condition_nationalite smallint
);


--
-- Name: langues; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE langues (
    id bigint NOT NULL,
    candidat_id bigint,
    langue text NOT NULL,
    niveau text NOT NULL,
    nb_annees text
);


--
-- Name: langues_diplomes; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE langues_diplomes (
    id bigint NOT NULL,
    langue_id bigint,
    diplome text,
    annee text,
    resultat text
);


--
-- Name: lettres; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE lettres (
    id bigint NOT NULL,
    composante_id bigint,
    titre text,
    fichier_logo text,
    texte_logo text,
    texte_scol text,
    texte_signature text,
    largeur_logo integer,
    flag_fichier_logo boolean DEFAULT false,
    flag_texte_logo boolean DEFAULT false,
    flag_texte_scol boolean DEFAULT false,
    flag_texte_signature boolean DEFAULT false,
    flag_adresse_candidat boolean DEFAULT false,
    choix_multiples smallint DEFAULT 0::smallint,
    flag_date smallint DEFAULT 1,
    flag_adr_pos boolean DEFAULT true,
    adr_pos_x integer DEFAULT 109,
    adr_pos_y integer DEFAULT 42,
    flag_corps_pos boolean DEFAULT true,
    corps_pos_x integer DEFAULT 60,
    corps_pos_y integer DEFAULT 78,
    lang text DEFAULT 'FR'::text
);


--
-- Name: lettres_decisions; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE lettres_decisions (
    lettre_id bigint,
    decision_id integer
);


--
-- Name: lettres_encadres; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE lettres_encadres (
    lettre_id bigint NOT NULL,
    ordre integer NOT NULL,
    texte text,
    txt_align integer DEFAULT 0
);


--
-- Name: lettres_paragraphes; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE lettres_paragraphes (
    lettre_id bigint NOT NULL,
    ordre integer NOT NULL,
    texte text,
    gras integer DEFAULT 0,
    italique integer DEFAULT 0,
    alignement integer DEFAULT 0,
    taille integer DEFAULT 10,
    marge_gauche integer DEFAULT 0
);


--
-- Name: lettres_propspec; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE lettres_propspec (
    lettre_id bigint,
    propspec_id bigint
);


--
-- Name: lettres_separateurs; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE lettres_separateurs (
    lettre_id bigint NOT NULL,
    ordre integer NOT NULL,
    nb_lignes integer DEFAULT 1
);


--
-- Name: liste_langues; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE liste_langues (
    id integer DEFAULT nextval('"liste_langues_id_seq"'::text) NOT NULL,
    langue text NOT NULL
);


--
-- Name: liste_langues_id_seq; Type: SEQUENCE; Schema: public; Owner: globdb
--

CREATE SEQUENCE liste_langues_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: mentions; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE mentions (
    id bigint NOT NULL,
    nom text NOT NULL,
    nom_court text,
    composante_id bigint
);


--
-- Name: messages; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE messages (
    composante_id bigint NOT NULL,
    "type" smallint NOT NULL,
    statut smallint NOT NULL,
    decision_id integer NOT NULL,
    contenu text,
    actif boolean DEFAULT false
);


--
-- Name: moduleapogee_activation; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE moduleapogee_activation (
    composante_id bigint,
    primo_entrants boolean DEFAULT true,
    laisser_passer boolean DEFAULT true
);


--
-- Name: moduleapogee_centres_gestion; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE moduleapogee_centres_gestion (
    id bigint NOT NULL,
    composante_id bigint,
    code text,
    nom text
);


--
-- Name: moduleapogee_codes_laisser_passer; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE moduleapogee_codes_laisser_passer (
    num text NOT NULL,
    candidature_id bigint,
    ligne_candidat text
);


--
-- Name: moduleapogee_config; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE moduleapogee_config (
    composante_id bigint NOT NULL,
    code text,
    prefixe_opi text DEFAULT ''::text,
    message_primo text DEFAULT ''::text,
    message_laisser_passer text DEFAULT ''::text,
    message_admis_sous_reserve text DEFAULT ''::text
);


--
-- Name: moduleapogee_formations; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE moduleapogee_formations (
    propspec_id bigint NOT NULL,
    cet text,
    vet text,
    centre_id bigint
);


--
-- Name: moduleapogee_numeros_opi; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE moduleapogee_numeros_opi (
    num text NOT NULL,
    candidature_id bigint,
    ligne_candidat text,
    ligne_voeu text
);


--
-- Name: motifs_refus; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE motifs_refus (
    id integer DEFAULT nextval('"motifs_refus_id_seq"'::text) NOT NULL,
    motif text,
    motif_long text,
    exclusif integer,
    composante_id bigint
);


--
-- Name: motifs_refus_id_seq; Type: SEQUENCE; Schema: public; Owner: globdb
--

CREATE SEQUENCE motifs_refus_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: msg_modeles; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE msg_modeles (
    id bigint NOT NULL,
    acces_id bigint,
    intitule text,
    texte text
);


--
-- Name: note; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE note (
    id bigint NOT NULL,
    cursus_id bigint NOT NULL,
    matiere text NOT NULL,
    note text NOT NULL,
    annee text,
    rang text
);


--
-- Name: pays_nationalites_iso_insee; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE pays_nationalites_iso_insee (
    iso3166 text,
    insee text,
    pays text,
    nationalite text
);


--
-- Name: propspec; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE propspec (
    spec_id bigint,
    selective integer,
    responsable text,
    responsable_email text,
    frais_dossiers double precision,
    annee_id integer,
    composante_id bigint,
    entretiens integer,
    finalite integer DEFAULT 0,
    id bigint NOT NULL,
    active integer DEFAULT 1,
    information text,
    manuelle smallint DEFAULT 0::smallint,
    affichage_decisions smallint DEFAULT 0,
    flag_pass boolean DEFAULT false,
    pass text DEFAULT ''::text
);


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE sessions (
    propspec_id bigint NOT NULL,
    id integer DEFAULT 1 NOT NULL,
    ouverture integer,
    fermeture integer,
    reception integer,
    periode text NOT NULL
);


--
-- Name: specialites; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE specialites (
    id bigint NOT NULL,
    nom text,
    nom_court text,
    composante_id bigint,
    mention_id bigint
);


--
-- Name: systeme; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE systeme (
    titre_html text,
    titre_page text,
    ville text,
    url_candidat text,
    url_gestion text,
    meta text,
    admin text,
    signature_courriels text,
    signature_admin text,
    courriel_admin text,
    info_liberte text,
    limite_periode smallint,
    limite_masse smallint,
    defaut_decision boolean DEFAULT true,
    defaut_motifs boolean DEFAULT true,
    max_rappels smallint,
    rappel_delai_sup smallint,
    debug boolean DEFAULT true,
    debug_rappel_id boolean DEFAULT false,
    debug_cursus boolean DEFAULT false,
    debug_statut_prec boolean DEFAULT false,
    debug_lock boolean DEFAULT false,
    debug_enregistrement boolean DEFAULT true,
    debug_sujet text,
    erreur_sujet text,
    arg_key text,
    assistance boolean DEFAULT false,
    ldap_actif boolean DEFAULT false,
    ldap_host text DEFAULT '',
    ldap_port smallint DEFAULT '389',
    ldap_proto smallint DEFAULT '3',
    ldap_id text DEFAULT '',
    ldap_pass text DEFAULT '',
    ldap_basedn text DEFAULT '',
    ldap_attr_login text DEFAULT '',
    ldap_attr_nom text DEFAULT '',
    ldap_attr_prenom text DEFAULT '',
    ldap_attr_mail text DEFAULT ''
);


--
-- Name: traitement_masse; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE traitement_masse (
    id text,
    partie integer,
    candidature_id bigint NOT NULL,
    acces_id bigint
);


--
-- Name: universites; Type: TABLE; Schema: public; Owner: globdb; Tablespace: 
--

CREATE TABLE universites (
    id integer NOT NULL,
    nom text NOT NULL,
    adresse text NOT NULL,
    img_dir text,
    css text,
    couleur_texte_lettres text
);


--
-- Name: acces_composantes_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY acces_composantes
    ADD CONSTRAINT acces_composantes_pkey PRIMARY KEY (id_acces, id_composante);


--
-- Name: acces_login_key; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY acces
    ADD CONSTRAINT acces_login_key UNIQUE (login);


--
-- Name: acces_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY acces
    ADD CONSTRAINT acces_pkey PRIMARY KEY (id);


--
-- Name: annees_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY annees
    ADD CONSTRAINT annees_pkey PRIMARY KEY (id);


--
-- Name: candidat_identifiant_key; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY candidat
    ADD CONSTRAINT candidat_identifiant_key UNIQUE (identifiant);


--
-- Name: candidat_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY candidat
    ADD CONSTRAINT candidat_pkey PRIMARY KEY (id);


--
-- Name: candidature_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY candidature
    ADD CONSTRAINT candidature_pkey PRIMARY KEY (id);


--
-- Name: commissions_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY commissions
    ADD CONSTRAINT commissions_pkey PRIMARY KEY (propspec_id, id, periode);


--
-- Name: composantes_infos_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY composantes_infos
    ADD CONSTRAINT composantes_infos_pkey PRIMARY KEY (id);


--
-- Name: composantes_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY composantes
    ADD CONSTRAINT composantes_pkey PRIMARY KEY (id);


--
-- Name: cursus_diplomes_apogee_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY cursus_diplomes_apogee
    ADD CONSTRAINT cursus_diplomes_apogee_pkey PRIMARY KEY (code);


--
-- Name: cursus_diplomes_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY cursus_diplomes
    ADD CONSTRAINT cursus_diplomes_pkey PRIMARY KEY (id);


--
-- Name: cursus_mentions_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY cursus_mentions
    ADD CONSTRAINT cursus_mentions_pkey PRIMARY KEY (id);


--
-- Name: cursus_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY cursus
    ADD CONSTRAINT cursus_pkey PRIMARY KEY (id);


--
-- Name: decisions_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY decisions
    ADD CONSTRAINT decisions_pkey PRIMARY KEY (id);


--
-- Name: departements_fr_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY departements_fr
    ADD CONSTRAINT departements_fr_pkey PRIMARY KEY (numero);


--
-- Name: diplomes_bac_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY diplomes_bac
    ADD CONSTRAINT diplomes_bac_pkey PRIMARY KEY (code_bac);


--
-- Name: dossiers_ef_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY dossiers_ef
    ADD CONSTRAINT dossiers_ef_pkey PRIMARY KEY (element_id, propspec_id);


--
-- Name: dossiers_elements_choix_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY dossiers_elements_choix
    ADD CONSTRAINT dossiers_elements_choix_pkey PRIMARY KEY (id);


--
-- Name: dossiers_elements_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY dossiers_elements
    ADD CONSTRAINT dossiers_elements_pkey PRIMARY KEY (id);


--
-- Name: droits_formations_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY droits_formations
    ADD CONSTRAINT droits_formations_pkey PRIMARY KEY (acces_id, propspec_id);


--
-- Name: encadres_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY lettres_encadres
    ADD CONSTRAINT encadres_pkey PRIMARY KEY (lettre_id, ordre);


--
-- Name: filtres_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY filtres
    ADD CONSTRAINT filtres_pkey PRIMARY KEY (id);


--
-- Name: infos_complementaires_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY infos_complementaires
    ADD CONSTRAINT infos_complementaires_pkey PRIMARY KEY (id);


--
-- Name: justificatifs_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY justificatifs
    ADD CONSTRAINT justificatifs_pkey PRIMARY KEY (id);


--
-- Name: justifs_fichiers_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY justifs_fichiers
    ADD CONSTRAINT justifs_fichiers_pkey PRIMARY KEY (id);


--
-- Name: langues_diplomes_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY langues_diplomes
    ADD CONSTRAINT langues_diplomes_pkey PRIMARY KEY (id);


--
-- Name: langues_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY langues
    ADD CONSTRAINT langues_pkey PRIMARY KEY (id);


--
-- Name: lettres_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY lettres
    ADD CONSTRAINT lettres_pkey PRIMARY KEY (id);


--
-- Name: liste_langues_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY liste_langues
    ADD CONSTRAINT liste_langues_pkey PRIMARY KEY (id);


--
-- Name: mentions_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY mentions
    ADD CONSTRAINT mentions_pkey PRIMARY KEY (id);


--
-- Name: messages_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (composante_id, "type", statut, decision_id);


--
-- Name: moduleapogee_centres_gestion_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY moduleapogee_centres_gestion
    ADD CONSTRAINT moduleapogee_centres_gestion_pkey PRIMARY KEY (id);


--
-- Name: moduleapogee_formations_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY moduleapogee_formations
    ADD CONSTRAINT moduleapogee_formations_pkey PRIMARY KEY (propspec_id);


--
-- Name: moduleapogee_numeros_opi_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY moduleapogee_numeros_opi
    ADD CONSTRAINT moduleapogee_numeros_opi_pkey PRIMARY KEY (num);


--
-- Name: motifs_refus_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY motifs_refus
    ADD CONSTRAINT motifs_refus_pkey PRIMARY KEY (id);


--
-- Name: msg_modeles_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY msg_modeles
    ADD CONSTRAINT msg_modeles_pkey PRIMARY KEY (id);


--
-- Name: note_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY note
    ADD CONSTRAINT note_pkey PRIMARY KEY (id);


--
-- Name: paragraphes_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY lettres_paragraphes
    ADD CONSTRAINT paragraphes_pkey PRIMARY KEY (lettre_id, ordre);


--
-- Name: proprietes_specialites_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY propspec
    ADD CONSTRAINT proprietes_specialites_pkey PRIMARY KEY (id);


--
-- Name: separateurs_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY lettres_separateurs
    ADD CONSTRAINT separateurs_pkey PRIMARY KEY (lettre_id, ordre);


--
-- Name: sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (propspec_id, id, periode);


--
-- Name: specialites_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY specialites
    ADD CONSTRAINT specialites_pkey PRIMARY KEY (id);


--
-- Name: traitement_masse_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY traitement_masse
    ADD CONSTRAINT traitement_masse_pkey PRIMARY KEY (candidature_id);


--
-- Name: universites_pkey; Type: CONSTRAINT; Schema: public; Owner: globdb; Tablespace: 
--

ALTER TABLE ONLY universites
    ADD CONSTRAINT universites_pkey PRIMARY KEY (id);


--
-- Name: annees_annee_key; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE UNIQUE INDEX annees_annee_key ON annees USING btree (annee);


--
-- Name: candidature_candidat_id_index; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE INDEX candidature_candidat_id_index ON candidature USING btree (candidat_id);


--
-- Name: cursus_mentions_intitule_key; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE UNIQUE INDEX cursus_mentions_intitule_key ON cursus_mentions USING btree (intitule);


--
-- Name: historique_acces_id_index; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE INDEX historique_acces_id_index ON historique USING btree (acces_id);


--
-- Name: historique_candidat_id_index; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE INDEX historique_candidat_id_index ON historique USING btree (candidat_id);


--
-- Name: historique_date_index; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE INDEX historique_date_index ON historique USING btree (date);


--
-- Name: historique_g_nom_index; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE INDEX historique_g_nom_index ON historique USING btree (g_nom) WHERE (g_nom <> ''::text);


--
-- Name: historique_ip_index; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE INDEX historique_ip_index ON historique USING btree (ip);


--
-- Name: index_historique; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE INDEX index_historique ON historique USING btree (date);


--
-- Name: index_historique_candidat_id; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE INDEX index_historique_candidat_id ON historique USING btree (candidat_id);


--
-- Name: moduleapogee_codes_laisser_passer_index_cand_id; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE INDEX moduleapogee_codes_laisser_passer_index_cand_id ON moduleapogee_codes_laisser_passer USING btree (candidature_id);


--
-- Name: moduleapogee_numeros_opi_index_cand_id; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE INDEX moduleapogee_numeros_opi_index_cand_id ON moduleapogee_numeros_opi USING btree (candidature_id);


--
-- Name: unique_intitule; Type: INDEX; Schema: public; Owner: globdb; Tablespace: 
--

CREATE UNIQUE INDEX unique_intitule ON cursus_diplomes USING btree (intitule);


--
-- Name: RI_ConstraintTrigger_49475; Type: TRIGGER; Schema: public; Owner: globdb
--

CREATE CONSTRAINT TRIGGER "<unnamed>"
    AFTER INSERT OR UPDATE ON note
    FROM cursus
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_check_ins"('<unnamed>', 'note', 'cursus', 'UNSPECIFIED', 'cursus_id', 'id');


--
-- Name: RI_ConstraintTrigger_49476; Type: TRIGGER; Schema: public; Owner: globdb
--

CREATE CONSTRAINT TRIGGER "<unnamed>"
    AFTER DELETE ON cursus
    FROM note
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_cascade_del"('<unnamed>', 'note', 'cursus', 'UNSPECIFIED', 'cursus_id', 'id');


--
-- Name: RI_ConstraintTrigger_49477; Type: TRIGGER; Schema: public; Owner: globdb
--

CREATE CONSTRAINT TRIGGER "<unnamed>"
    AFTER UPDATE ON cursus
    FROM note
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_cascade_upd"('<unnamed>', 'note', 'cursus', 'UNSPECIFIED', 'cursus_id', 'id');


--
-- Name: RI_ConstraintTrigger_49483; Type: TRIGGER; Schema: public; Owner: globdb
--

CREATE CONSTRAINT TRIGGER "<unnamed>"
    AFTER INSERT OR UPDATE ON note
    FROM cursus
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_check_ins"('<unnamed>', 'note', 'cursus', 'UNSPECIFIED', 'cursus_id', 'id');


--
-- Name: RI_ConstraintTrigger_49484; Type: TRIGGER; Schema: public; Owner: globdb
--

CREATE CONSTRAINT TRIGGER "<unnamed>"
    AFTER DELETE ON cursus
    FROM note
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_cascade_del"('<unnamed>', 'note', 'cursus', 'UNSPECIFIED', 'cursus_id', 'id');


--
-- Name: RI_ConstraintTrigger_49485; Type: TRIGGER; Schema: public; Owner: globdb
--

CREATE CONSTRAINT TRIGGER "<unnamed>"
    AFTER UPDATE ON cursus
    FROM note
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_cascade_upd"('<unnamed>', 'note', 'cursus', 'UNSPECIFIED', 'cursus_id', 'id');


--
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY candidature
    ADD CONSTRAINT "$1" FOREIGN KEY (candidat_id) REFERENCES candidat(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY cursus
    ADD CONSTRAINT "$1" FOREIGN KEY (candidat_id) REFERENCES candidat(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY infos_complementaires
    ADD CONSTRAINT "$1" FOREIGN KEY (candidat_id) REFERENCES candidat(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY langues
    ADD CONSTRAINT "$1" FOREIGN KEY (candidat_id) REFERENCES candidat(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: acces_candidats_lus_acces_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY acces_candidats_lus
    ADD CONSTRAINT acces_candidats_lus_acces_id_fkey FOREIGN KEY (acces_id) REFERENCES acces(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: acces_candidats_lus_candidat_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY acces_candidats_lus
    ADD CONSTRAINT acces_candidats_lus_candidat_id_fkey FOREIGN KEY (candidat_id) REFERENCES candidat(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: acces_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY acces
    ADD CONSTRAINT acces_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: acces_composantes_id_acces_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY acces_composantes
    ADD CONSTRAINT acces_composantes_id_acces_fkey FOREIGN KEY (id_acces) REFERENCES acces(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: acces_composantes_id_composante_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY acces_composantes
    ADD CONSTRAINT acces_composantes_id_composante_fkey FOREIGN KEY (id_composante) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: candidature_decision_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY candidature
    ADD CONSTRAINT candidature_decision_fkey FOREIGN KEY (decision) REFERENCES decisions(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: candidature_propspec_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY candidature
    ADD CONSTRAINT candidature_propspec_fkey FOREIGN KEY (propspec_id) REFERENCES propspec(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: commissions_propspec_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY commissions
    ADD CONSTRAINT commissions_propspec_id_fkey FOREIGN KEY (propspec_id) REFERENCES propspec(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: composantes_infos_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY composantes_infos
    ADD CONSTRAINT composantes_infos_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: composantes_infos_encadres_info_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY composantes_infos_encadres
    ADD CONSTRAINT composantes_infos_encadres_info_id_fkey FOREIGN KEY (info_id) REFERENCES composantes_infos(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: composantes_infos_fichiers_info_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY composantes_infos_fichiers
    ADD CONSTRAINT composantes_infos_fichiers_info_id_fkey FOREIGN KEY (info_id) REFERENCES composantes_infos(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: composantes_infos_paragraphes_info_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY composantes_infos_paragraphes
    ADD CONSTRAINT composantes_infos_paragraphes_info_id_fkey FOREIGN KEY (info_id) REFERENCES composantes_infos(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: composantes_infos_separateurs_info_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY composantes_infos_separateurs
    ADD CONSTRAINT composantes_infos_separateurs_info_id_fkey FOREIGN KEY (info_id) REFERENCES composantes_infos(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: composantes_univ_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY composantes
    ADD CONSTRAINT composantes_univ_id_fkey FOREIGN KEY (univ_id) REFERENCES universites(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: courriels_formations_acces_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY courriels_formations
    ADD CONSTRAINT courriels_formations_acces_id_fkey FOREIGN KEY (acces_id) REFERENCES acces(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: cursus_justificatifs_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY cursus_justificatifs
    ADD CONSTRAINT cursus_justificatifs_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: cursus_justificatifs_cursus_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY cursus_justificatifs
    ADD CONSTRAINT cursus_justificatifs_cursus_id_fkey FOREIGN KEY (cursus_id) REFERENCES cursus(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: decisions_composantes_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY decisions_composantes
    ADD CONSTRAINT decisions_composantes_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: decisions_composantes_decision_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY decisions_composantes
    ADD CONSTRAINT decisions_composantes_decision_id_fkey FOREIGN KEY (decision_id) REFERENCES decisions(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dossiers_ef_element_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY dossiers_ef
    ADD CONSTRAINT dossiers_ef_element_id_fkey FOREIGN KEY (element_id) REFERENCES dossiers_elements(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dossiers_ef_propspec_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY dossiers_ef
    ADD CONSTRAINT dossiers_ef_propspec_id_fkey FOREIGN KEY (propspec_id) REFERENCES propspec(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dossiers_elements_choix_element_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY dossiers_elements_choix
    ADD CONSTRAINT dossiers_elements_choix_element_id_fkey FOREIGN KEY (element_id) REFERENCES dossiers_elements(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dossiers_elements_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY dossiers_elements
    ADD CONSTRAINT dossiers_elements_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dossiers_elements_contenu_candidat_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY dossiers_elements_contenu
    ADD CONSTRAINT dossiers_elements_contenu_candidat_id_fkey FOREIGN KEY (candidat_id) REFERENCES candidat(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dossiers_elements_contenu_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY dossiers_elements_contenu
    ADD CONSTRAINT dossiers_elements_contenu_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dossiers_elements_contenu_element_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY dossiers_elements_contenu
    ADD CONSTRAINT dossiers_elements_contenu_element_id_fkey FOREIGN KEY (element_id) REFERENCES dossiers_elements(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: droits_formations_acces_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY droits_formations
    ADD CONSTRAINT droits_formations_acces_id_fkey FOREIGN KEY (acces_id) REFERENCES acces(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: droits_formations_propspec_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY droits_formations
    ADD CONSTRAINT droits_formations_propspec_id_fkey FOREIGN KEY (propspec_id) REFERENCES propspec(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: filtres_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY filtres
    ADD CONSTRAINT filtres_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: groupes_spec_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY groupes_specialites
    ADD CONSTRAINT groupes_spec_fkey FOREIGN KEY (propspec_id) REFERENCES propspec(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: id_annee_fk; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY propspec
    ADD CONSTRAINT id_annee_fk FOREIGN KEY (annee_id) REFERENCES annees(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: id_spec_fk; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY propspec
    ADD CONSTRAINT id_spec_fk FOREIGN KEY (spec_id) REFERENCES specialites(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: justificatifs_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY justificatifs
    ADD CONSTRAINT justificatifs_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: justifs_fichiers_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY justifs_fichiers
    ADD CONSTRAINT justifs_fichiers_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: justifs_fichiers_formations_fichier_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY justifs_fichiers_formations
    ADD CONSTRAINT justifs_fichiers_formations_fichier_id_fkey FOREIGN KEY (fichier_id) REFERENCES justifs_fichiers(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: justifs_fichiers_formations_propspec_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY justifs_fichiers_formations
    ADD CONSTRAINT justifs_fichiers_formations_propspec_id_fkey FOREIGN KEY (propspec_id) REFERENCES propspec(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: justifs_formations_justif_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY justifs_formations
    ADD CONSTRAINT justifs_formations_justif_id_fkey FOREIGN KEY (justif_id) REFERENCES justificatifs(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: justifs_formations_propspec_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY justifs_formations
    ADD CONSTRAINT justifs_formations_propspec_id_fkey FOREIGN KEY (propspec_id) REFERENCES propspec(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: langues_diplomes_langue_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY langues_diplomes
    ADD CONSTRAINT langues_diplomes_langue_id_fkey FOREIGN KEY (langue_id) REFERENCES langues(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: lettre_decisions_decision_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY lettres_decisions
    ADD CONSTRAINT lettre_decisions_decision_id_fkey FOREIGN KEY (decision_id) REFERENCES decisions(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: lettre_decisions_lettre_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY lettres_decisions
    ADD CONSTRAINT lettre_decisions_lettre_id_fkey FOREIGN KEY (lettre_id) REFERENCES lettres(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: lettres_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY lettres
    ADD CONSTRAINT lettres_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: lettres_encadres_lettre_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY lettres_encadres
    ADD CONSTRAINT lettres_encadres_lettre_id_fkey FOREIGN KEY (lettre_id) REFERENCES lettres(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: lettres_paragraphes_lettre_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY lettres_paragraphes
    ADD CONSTRAINT lettres_paragraphes_lettre_id_fkey FOREIGN KEY (lettre_id) REFERENCES lettres(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: lettres_propspec_lettre_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY lettres_propspec
    ADD CONSTRAINT lettres_propspec_lettre_id_fkey FOREIGN KEY (lettre_id) REFERENCES lettres(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: lettres_propspec_propspec_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY lettres_propspec
    ADD CONSTRAINT lettres_propspec_propspec_id_fkey FOREIGN KEY (propspec_id) REFERENCES propspec(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: lettres_separateurs_lettre_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY lettres_separateurs
    ADD CONSTRAINT lettres_separateurs_lettre_id_fkey FOREIGN KEY (lettre_id) REFERENCES lettres(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: mentions_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY mentions
    ADD CONSTRAINT mentions_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: messages_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY messages
    ADD CONSTRAINT messages_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: moduleapogee_activation_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY moduleapogee_activation
    ADD CONSTRAINT moduleapogee_activation_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: moduleapogee_centres_gestion_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY moduleapogee_centres_gestion
    ADD CONSTRAINT moduleapogee_centres_gestion_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: moduleapogee_code_universite_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY moduleapogee_config
    ADD CONSTRAINT moduleapogee_code_universite_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: moduleapogee_codes_laisser_passer_candidature_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY moduleapogee_codes_laisser_passer
    ADD CONSTRAINT moduleapogee_codes_laisser_passer_candidature_id_fkey FOREIGN KEY (candidature_id) REFERENCES candidature(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: moduleapogee_formations_propspec_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY moduleapogee_formations
    ADD CONSTRAINT moduleapogee_formations_propspec_id_fkey FOREIGN KEY (propspec_id) REFERENCES propspec(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: moduleapogee_numeros_opi_candidature_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY moduleapogee_numeros_opi
    ADD CONSTRAINT moduleapogee_numeros_opi_candidature_id_fkey FOREIGN KEY (candidature_id) REFERENCES candidature(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: motifs_refus_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY motifs_refus
    ADD CONSTRAINT motifs_refus_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: msg_modeles_acces_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY msg_modeles
    ADD CONSTRAINT msg_modeles_acces_id_fkey FOREIGN KEY (acces_id) REFERENCES acces(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: proprietes_specialites_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY propspec
    ADD CONSTRAINT proprietes_specialites_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: sessions_propspec_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY sessions
    ADD CONSTRAINT sessions_propspec_id_fkey FOREIGN KEY (propspec_id) REFERENCES propspec(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: specialites_composante_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY specialites
    ADD CONSTRAINT specialites_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: speclialites_mention_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY specialites
    ADD CONSTRAINT speclialites_mention_id_fkey FOREIGN KEY (mention_id) REFERENCES mentions(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: traitement_masse_candidature_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: globdb
--

ALTER TABLE ONLY traitement_masse
    ADD CONSTRAINT traitement_masse_candidature_id_fkey FOREIGN KEY (candidature_id) REFERENCES candidature(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

