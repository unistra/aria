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
	// MODIFICATION DU SCHEMA - MODULE APOGEE
	// Création des tables permettant d'entrer :
	// - le code de chaque université (lettre unique pour la génération du code d'inscription administrative)
	// - les codes et versions d'étape des formations (l'import automatique n'est pas encore faisable)
	// - les codes des composantes
	// Le but de ce module est de préparer les transferts automatiques des candidats admis vers Apogée
	// pour les Inscriptions Administratives

   // Message par défaut pour les extractions des Primo-Entrants
$defaut_PE="";

// Message par défaut pour les laisser-passer

$defaut_LP="";

// Message par défaut pour les admis sous réserve

$defaut_RESERVE="";


	$db_maj=db_connect();

   // CONFIGURATION (ex table "moduleapogee_code_universite")
	$res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_module_apogee_DB_code_univ'");

   if(db_num_rows($res_maj))
   {
      // Si une table "config" existe déjà, on la renomme
      if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_module_apogee_DB_config'")))
         db_query($db_maj, "ALTER TABLE $_module_apogee_DB_config RENAME TO moduleapogee_config_temp");
      
      db_query($db_maj, "ALTER TABLE $_module_apogee_DB_code_univ RENAME TO $_module_apogee_DB_config");

      if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$GLOBALS[__DB_BASE]' 
                                                                               AND table_name='$_module_apogee_DB_config'
                                                                               AND column_name='$_module_apogee_DBU_config_prefixe_opi'")))
      db_query($db_maj, "ALTER TABLE $_module_apogee_DB_config ADD COLUMN $_module_apogee_DBU_config_prefixe_opi text default '';
                         UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_prefixe_opi='';");

      if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$GLOBALS[__DB_BASE]'
                                                                               AND table_name='$_module_apogee_DB_config'
                                                                               AND column_name='$_module_apogee_DBU_config_message_primo'")))
      db_query($db_maj, "ALTER TABLE $_module_apogee_DB_config ADD COLUMN $_module_apogee_DBU_config_message_primo text default '';
                         UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_message_primo='$defaut_PE';");
   }
   elseif(!db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_module_apogee_DB_config'"))) // Création de la table
   {
      db_query($db_maj, "CREATE TABLE $_module_apogee_DB_config (
                           $_module_apogee_DBU_config_univ_id INT PRIMARY KEY REFERENCES $GLOBALS[_DB_universites]($GLOBALS[_DBU_universites_id]) ON UPDATE CASCADE ON DELETE CASCADE,
                           $_module_apogee_DBU_config_code TEXT default '',
                           $_module_apogee_DBU_config_prefixe_opi TEXT default '',
                           $_module_apogee_DBU_config_message_primo TEXT default '$defaut_PE')");

		// Si la colonne "code_apogee" existe toujours dans la table "universites", on peuple automatiquement la nouvelle table
		if(isset($GLOBALS[_DBU_universites_apogee]))
	      db_query($db_maj, "INSERT INTO $_module_apogee_DB_config (SELECT $GLOBALS[_DBC_universites_id], $GLOBALS[_DBU_universites_apogee], '', '$defaut_PE' FROM $GLOBALS[_DB_universites])");
   }

	db_free_result($res_maj);

	// ======================================

	$res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_module_apogee_DB_formations'");

	if(!db_num_rows($res_maj))
	{
		db_query($db_maj, "CREATE TABLE $_module_apogee_DB_formations (
									$_module_apogee_DBU_formations_propspec_id BIGINT PRIMARY KEY REFERENCES $GLOBALS[_DB_propspec]($GLOBALS[_DBU_propspec_id]) ON UPDATE CASCADE ON DELETE CASCADE,
									$_module_apogee_DBU_formations_cet TEXT, 
									$_module_apogee_DBU_formations_vet TEXT,
									$_module_apogee_DBU_formations_centre_gestion BIGINT)");
									
		// Si la colonne "code" existe toujours dans la table "propspec", on peuple automatiquement la table
		if(isset($GLOBALS[_DBU_propspec_code]))
			db_query($db_maj, "INSERT INTO $_module_apogee_DB_formations (SELECT $GLOBALS[_DBC_propspec_id], propspec.code, '', '0' FROM $GLOBALS[_DB_propspec] WHERE $GLOBALS[_DBC_propspec_active]='1')");
	}

	db_free_result($res_maj);

	// ======================================

	$res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_module_apogee_DB_centres_gestion'");

	if(!db_num_rows($res_maj))
	{
		db_query($db_maj, "CREATE TABLE $_module_apogee_DB_centres_gestion (
									$_module_apogee_DBU_centres_gestion_id BIGINT PRIMARY KEY,
									$_module_apogee_DBU_centres_gestion_comp_id BIGINT REFERENCES $GLOBALS[_DB_composantes]($GLOBALS[_DBU_composantes_id]) ON UPDATE CASCADE ON DELETE CASCADE,
									$_module_apogee_DBU_centres_gestion_code TEXT,
									$_module_apogee_DBU_centres_gestion_nom TEXT)");
	}

	// NUMEROS OPI GENERES LORS DES EXTRACTIONS QUOTIDIENNES

	$res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_module_apogee_DB_numeros_opi'");

	if(!db_num_rows($res_maj))
	{
		db_query($db_maj, "CREATE TABLE $_module_apogee_DB_numeros_opi (
									$_module_apogee_DBU_numeros_opi_num TEXT PRIMARY KEY NOT NULL,
									$_module_apogee_DBU_numeros_opi_cand_id BIGINT REFERENCES $GLOBALS[_DB_cand]($GLOBALS[_DBU_cand_id]) ON UPDATE CASCADE ON DELETE CASCADE,
									$_module_apogee_DBU_numeros_opi_ligne_candidat TEXT,
									$_module_apogee_DBU_numeros_opi_ligne_voeux TEXT)");

		db_query($db_maj, "CREATE INDEX $_module_apogee_DB_numeros_opi"."_index_cand_id ON $_module_apogee_DB_numeros_opi($_module_apogee_DBU_numeros_opi_cand_id)");
	}

	db_free_result($res_maj);

   // ACTIVATION DES EXTRACTIONS PAR COMPOSANTE

   $res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_module_apogee_DB_activ'");

   if(!db_num_rows($res_maj))
   {
      db_query($db_maj, "CREATE TABLE $_module_apogee_DB_activ (
                           $_module_apogee_DBU_activ_comp_id BIGINT REFERENCES $GLOBALS[_DB_composantes]($GLOBALS[_DBU_composantes_id]) ON UPDATE CASCADE ON DELETE CASCADE,
                           $_module_apogee_DBU_activ_pe boolean default 't',
                           $_module_apogee_DBU_activ_lp boolean default 't')");
   }

   db_free_result($res_maj);

   // Message par défaut pour les Laisser-Passer
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$GLOBALS[__DB_BASE]'
                                                                               AND table_name='$_module_apogee_DB_config'
                                                                               AND column_name='$_module_apogee_DBU_config_message_lp'")))
      db_query($db_maj, "ALTER TABLE $_module_apogee_DB_config ADD COLUMN $_module_apogee_DBU_config_message_lp text default '';
                         UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_message_lp='$defaut_LP';");

   // Création de la table qui contiendra les laisser-passer enregistrés
   $res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_module_apogee_DB_codes_LP'");

   if(!db_num_rows($res_maj))
   {
      db_query($db_maj, "CREATE TABLE $_module_apogee_DB_codes_LP (
									$_module_apogee_DBU_codes_LP_code TEXT NOT NULL,
									$_module_apogee_DBU_codes_LP_cand_id BIGINT REFERENCES $GLOBALS[_DB_cand]($GLOBALS[_DBU_cand_id]) ON UPDATE CASCADE ON DELETE CASCADE,
									$_module_apogee_DBU_codes_LP_ligne_candidat TEXT)");

      db_query($db_maj, "CREATE INDEX $_module_apogee_DB_codes_LP"."_index_cand_id ON $_module_apogee_DB_codes_LP($_module_apogee_DBU_codes_LP_cand_id)");
   }

   // 09/07/2010 - Suppression de la clé primaire de la table $_module_apogee_DB_codes_LP : le code d'autorisation peut ne pas être unique
   $res_conkey=db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='moduleapogee_codes_laisser_passer_pkey'");
   
   if(db_num_rows($res_conkey))
      db_query($db_maj, "ALTER TABLE $_module_apogee_DB_codes_LP DROP CONSTRAINT moduleapogee_codes_laisser_passer_pkey");
      
   db_free_result($res_conkey);

   // Mise à jour de la macro signature dans les messages par défaut (%signature% => [signature]) car le traitement n'est pas effectué par la même macro
   db_query($db_maj,"UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_message_lp=replace($_module_apogee_DBU_config_message_lp, '%Signature%','[Signature]')");
   db_query($db_maj,"UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_message_primo=replace($_module_apogee_DBU_config_message_primo, '%Signature%','[Signature]')");
   
   // Message par défaut pour les Admis sous Réserve
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$GLOBALS[__DB_BASE]'
                                                                               AND table_name='$_module_apogee_DB_config'
                                                                               AND column_name='$_module_apogee_DBU_config_message_reserve'")))
      db_query($db_maj, "ALTER TABLE $_module_apogee_DB_config ADD COLUMN $_module_apogee_DBU_config_message_reserve text default '';
                         UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_message_reserve='$defaut_RESERVE';");


   // TABLE moduleapogee_config : ajout de la possibilité d'avoir une configuration différente pour chaque composante, et non plus pour toute une université
   
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$GLOBALS[__DB_BASE]'
                                                                              AND table_name='$_module_apogee_DB_config'
                                                                              AND column_name='$_module_apogee_DBU_config_univ_id'")))
   {
   
      db_query($db_maj,"ALTER TABLE $_module_apogee_DB_config DROP CONSTRAINT moduleapogee_code_universite_pkey;
                        ALTER TABLE $_module_apogee_DB_config DROP CONSTRAINT moduleapogee_code_universite_univ_id_fkey;
                        ALTER TABLE $_module_apogee_DB_config RENAME univ_id TO composante_id;
                        ALTER TABLE $_module_apogee_DB_config ALTER COLUMN composante_id TYPE BIGINT;");
   
      $res_composantes=db_query($db_maj, "SELECT min($GLOBALS[_DBC_composantes_id]), $GLOBALS[_DBC_composantes_univ_id] FROM $GLOBALS[_DB_composantes] GROUP BY $GLOBALS[_DBC_composantes_univ_id]");
   
      if(db_num_rows($res_composantes)) // Si aucun résultat : problème ...
      {
         for($i=0 ; $i<db_num_rows($res_composantes); $i++)
         {
            list($min_comp_id, $univ_id)=db_fetch_row($res_composantes, $i);
      
            db_query($db_maj,"UPDATE $_module_apogee_DB_config SET composante_id = '$min_comp_id' WHERE composante_id='$univ_id'");
   
            // Copie de la configuration existante pour toutes les composantes de cette université
            db_query($db_maj, "INSERT INTO $_module_apogee_DB_config (SELECT id,code,prefixe_opi,message_primo,message_laisser_passer,message_admis_sous_reserve 
                                                                      FROM $GLOBALS[_DB_composantes], $_module_apogee_DB_config 
                                                                      WHERE $GLOBALS[_DBC_composantes_id]!='$min_comp_id'
                                                                      AND $_module_apogee_DBC_config_comp_id='$min_comp_id'
                                                                      AND $GLOBALS[_DBC_composantes_univ_id]='$univ_id')");
         }
      }
   
      // Mise en place de la nouvelle contrainte sur la composante
      db_query($db_maj, "ALTER TABLE $_module_apogee_DB_config ADD CONSTRAINT moduleapogee_code_universite_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES $GLOBALS[_DB_composantes]($GLOBALS[_DBU_composantes_id]) ON UPDATE CASCADE ON DELETE CASCADE");
   
      db_free_result($res_composantes);
   }
   
   // Table moduleapogee_numeros_opi : ajout de la colonne temoin_reserve pour envoyer un nouveau message lorsqu'un candidat passe de "Admis sous Réserve" à "Admis"
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$GLOBALS[__DB_BASE]'
                                                                               AND table_name='$_module_apogee_DB_numeros_opi'
                                                                               AND column_name='$_module_apogee_DBU_numeros_opi_temoin_reserve'")))
      db_query($db_maj, "ALTER TABLE $_module_apogee_DB_numeros_opi ADD COLUMN $_module_apogee_DBU_numeros_opi_temoin_reserve boolean default 'f';
                         UPDATE $_module_apogee_DB_numeros_opi SET $_module_apogee_DBU_numeros_opi_temoin_reserve='f';
                         UPDATE $_module_apogee_DB_numeros_opi SET $_module_apogee_DBU_numeros_opi_temoin_reserve='t' WHERE $_module_apogee_DBU_numeros_opi_ligne_voeux=''");

   // 24/08/2011
   // Configuration : ajout de plusieurs colonnes pour les adresses des différents sites
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$GLOBALS[__DB_BASE]' 
                                                                               AND table_name='$_module_apogee_DB_config'
                                                                               AND column_name='$_module_apogee_DBU_config_adr_primo'")))
      db_query($db_maj, "ALTER TABLE $_module_apogee_DB_config ADD COLUMN $_module_apogee_DBU_config_adr_primo text default '';
                         UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_adr_primo='';");
                         
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$GLOBALS[__DB_BASE]' 
                                                                               AND table_name='$_module_apogee_DB_config'
                                                                               AND column_name='$_module_apogee_DBU_config_adr_reins'")))
      db_query($db_maj, "ALTER TABLE $_module_apogee_DB_config ADD COLUMN $_module_apogee_DBU_config_adr_reins text default '';
                         UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_adr_reins='';");
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$GLOBALS[__DB_BASE]' 
                                                                               AND table_name='$_module_apogee_DB_config'
                                                                               AND column_name='$_module_apogee_DBU_config_adr_rdv'")))
      db_query($db_maj, "ALTER TABLE $_module_apogee_DB_config ADD COLUMN $_module_apogee_DBU_config_adr_rdv text default '';
                         UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_adr_rdv='';");
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$GLOBALS[__DB_BASE]' 
                                                                               AND table_name='$_module_apogee_DB_config'
                                                                               AND column_name='$_module_apogee_DBU_config_adr_conditions'")))
      db_query($db_maj, "ALTER TABLE $_module_apogee_DB_config ADD COLUMN $_module_apogee_DBU_config_adr_conditions text default '';
                         UPDATE $_module_apogee_DB_config SET $_module_apogee_DBU_config_adr_conditions='';");

   // Messages individuels par formation
   if(!db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_module_apogee_DB_messages'")))
      db_query($db_maj, "CREATE TABLE $_module_apogee_DB_messages (
                            $_module_apogee_DBU_messages_msg_id BIGINT PRIMARY KEY, 
                            $_module_apogee_DBU_messages_comp_id BIGINT REFERENCES $GLOBALS[_DB_composantes]($GLOBALS[_DBU_composantes_id]) ON UPDATE CASCADE ON DELETE CASCADE, 
                            $_module_apogee_DBU_messages_nom TEXT NOT NULL, 
                            $_module_apogee_DBU_messages_contenu TEXT NOT NULL, 
                            $_module_apogee_DBU_messages_type SMALLINT)");
   
   if(!db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_module_apogee_DB_messages_formations'")))
      db_query($db_maj, "CREATE TABLE $_module_apogee_DB_messages_formations (
                            $_module_apogee_DBU_messages_formations_propspec_id BIGINT REFERENCES $GLOBALS[_DB_propspec]($GLOBALS[_DBU_propspec_id]) ON UPDATE CASCADE ON DELETE CASCADE, 
                            $_module_apogee_DBU_messages_formations_msg_id BIGINT REFERENCES $_module_apogee_DB_messages($_module_apogee_DBU_messages_msg_id) ON UPDATE CASCADE ON DELETE CASCADE)");
                         
   db_close($db_maj);
?>
