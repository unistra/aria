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
   // MISES A JOUR PONCTUELLES DU SCHEMA DE LA BASE DE DONNEES

   $db_maj=db_connect();

   //   Le tout est effectué dans un bloc afin d'éviter les éventuelles MAJ simultanées
   db_query($db_maj, "BEGIN;");

   // Filtres - 20/02/2009
   $res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_DB_filtres'");

   if(!db_num_rows($res_maj))
      db_query($db_maj, "CREATE TABLE $_DB_filtres (
                        $_DBU_filtres_id bigint primary key,
                        $_DBU_filtres_nom text,
                        $_DBU_filtres_comp_id int REFERENCES $_DB_composantes($_DBU_composantes_id) ON UPDATE CASCADE ON DELETE CASCADE,
                        $_DBU_filtres_cond_propspec_id int,
                        $_DBU_filtres_cond_annee_id int,
                        $_DBU_filtres_cond_mention_id bigint,
                        $_DBU_filtres_cond_spec_id int,
                        $_DBU_filtres_cond_finalite smallint,
                        $_DBU_filtres_cible_propspec_id int,
                        $_DBU_filtres_cible_annee_id int,
                        $_DBU_filtres_cible_mention_id bigint,
                        $_DBU_filtres_cible_spec_id int,
                        $_DBU_filtres_cible_finalite smallint,
                        $_DBU_filtres_actif smallint default '1')");
   // </filtres>
   
   // COMMISSIONS PEDAGOGIQUES : MODIFICATION DE LA CLE PRIMAIRE (ajout de la période)
   $res_conkey=db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='commissions_pkey'");

   if(db_num_rows($res_conkey))
   {
      list($conkey)=db_fetch_row($res_conkey,0);

      // La clé doit avoir trois membres
      $key_array=explode(",", $conkey);

      if(count($key_array)<3)
      {
         db_query($db_maj,"ALTER TABLE $_DB_commissions DROP CONSTRAINT commissions_pkey");
         db_query($db_maj,"ALTER TABLE $_DB_commissions ADD CONSTRAINT  commissions_pkey PRIMARY KEY ($_DBU_commissions_propspec_id,$_DBU_commissions_id,$_DBU_commissions_periode)");
      }
   }

   db_free_result($res_conkey);
   // </commissions>

   // TABLE ACCES : Ajout de deux champs pour la signature des messages
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_acces'
                                                                               AND column_name='$_DBU_acces_signature_txt'")))
      db_query($db_maj, "ALTER TABLE $_DB_acces ADD COLUMN $_DBU_acces_signature_txt text default '';
                         UPDATE $_DB_acces SET $_DBU_acces_signature_txt='';");
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_acces'
                                                                               AND column_name='$_DBU_acces_signature_active'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_acces ADD COLUMN $_DBU_acces_signature_active boolean default 't'");
      db_query($db_maj, "UPDATE $_DB_acces SET $_DBU_acces_signature_active='t'");
   }

   // </acces>

   // AJOUT DES COLONNES "nb_choix_min" et "nb_choix_max" DANS LA TABLE "dossiers_elements"
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_dossiers_elems'
                                                                               AND column_name='$_DBU_dossiers_elems_nb_choix_min'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_dossiers_elems ADD COLUMN $_DBU_dossiers_elems_nb_choix_min smallint default '0';
                         UPDATE $_DB_dossiers_elems SET $_DBU_dossiers_elems_nb_choix_min='0';");
   }

   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_dossiers_elems'
                                                                               AND column_name='$_DBU_dossiers_elems_nb_choix_max'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_dossiers_elems ADD COLUMN $_DBU_dossiers_elems_nb_choix_max smallint default '0';
                         UPDATE $_DB_dossiers_elems SET $_DBU_dossiers_elems_nb_choix_max='0';");
   }

   // CREATION DE LA TABLE "dossiers_elements_choix"
   
   $res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_DB_dossiers_elems_choix'");

   if(!db_num_rows($res_maj))
      db_query($db_maj, "CREATE TABLE $_DB_dossiers_elems_choix (
                        $_DBU_dossiers_elems_choix_id bigint primary key,
                        $_DBU_dossiers_elems_choix_elem_id bigint REFERENCES $_DB_dossiers_elems($_DBU_dossiers_elems_id) ON UPDATE CASCADE ON DELETE CASCADE,
                        $_DBU_dossiers_elems_choix_texte text,
                        $_DBU_dossiers_elems_choix_ordre int)");

   db_free_result($res_maj);


   // LETTRES
   // Ajout de la position du corps (coordonnées x,y relatives au coin haut-gauche) + valeurs par défaut pour la composante
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_lettres'
                                                                               AND column_name='$_DBU_lettres_flag_corps_pos'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_lettres ADD COLUMN $_DBU_lettres_flag_corps_pos boolean default 't'");
      db_query($db_maj, "UPDATE $_DB_lettres SET $_DBU_lettres_flag_corps_pos='t'");
   }

   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_lettres'
                                                                               AND column_name='$_DBU_lettres_corps_pos_x'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_lettres ADD COLUMN $_DBU_lettres_corps_pos_x int default '60'");
      db_query($db_maj, "UPDATE $_DB_lettres SET $_DBU_lettres_corps_pos_x='60'");
   }

   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_lettres'
                                                                               AND column_name='$_DBU_lettres_corps_pos_y'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_lettres ADD COLUMN $_DBU_lettres_corps_pos_y int default '78'");
      db_query($db_maj, "UPDATE $_DB_lettres SET $_DBU_lettres_corps_pos_y='78'");
   }

   // Ajout de la colonne "lang" pour la langue de certains champs fixes
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_lettres'
                                                                               AND column_name='$_DBU_lettres_langue'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_lettres ADD COLUMN $_DBU_lettres_langue text default 'FR'");
      db_query($db_maj, "UPDATE $_DB_lettres SET $_DBU_lettres_langue='FR'");
   }

   // Composantes   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_composantes'
                                                                               AND column_name='$_DBU_composantes_corps_pos_x'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_composantes ADD COLUMN $_DBU_composantes_corps_pos_x int default '60'");
      db_query($db_maj, "UPDATE $_DB_composantes SET $_DBU_composantes_corps_pos_x='60'");
   }

   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_composantes'
                                                                               AND column_name='$_DBU_composantes_corps_pos_y'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_composantes ADD COLUMN $_DBU_composantes_corps_pos_y int default '78'");
      db_query($db_maj, "UPDATE $_DB_composantes SET $_DBU_composantes_corps_pos_x='78'");
   }

   // Paragraphes : ajout du décalage axe X (marge gauche)
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_para'
                                                                               AND column_name='$_DBU_para_marge_g'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_para ADD COLUMN $_DBU_para_marge_g int default '0'");
      db_query($db_maj, "UPDATE $_DB_para SET $_DBU_para_marge_g='0'");
   }

   // Séparateurs (=ligne vide) : ajout de la hauteur en nombre de lignes
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_sepa'
                                                                               AND column_name='$_DBU_sepa_nb_lignes'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_sepa ADD COLUMN $_DBU_sepa_nb_lignes int default '1'");
      db_query($db_maj, "UPDATE $_DB_sepa SET $_DBU_sepa_nb_lignes='1'");
   }

   // Formations : possibilité d'ajouter un mot de passe (deux colonnes : flag + mot de passe)
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_propspec'
                                                                               AND column_name='$_DBU_propspec_flag_pass'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_propspec ADD COLUMN $_DBU_propspec_flag_pass boolean default 'f'");
      db_query($db_maj, "UPDATE $_DB_propspec SET $_DBU_propspec_flag_pass='f'");
   }

   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_propspec'
                                                                               AND column_name='$_DBU_propspec_pass'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_propspec ADD COLUMN $_DBU_propspec_pass text default ''");
      db_query($db_maj, "UPDATE $_DB_propspec SET $_DBU_propspec_pass=''");
   }

   // TABLE SPECIALITES : ajout d'une clé étrangère sur la colonne "composante_id"
   $res_conkey=db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='specialites_composante_id_fkey'");

   if(!db_num_rows($res_conkey)) // la contrainte n'existe pas : il faut la créer
   {
      db_query($db_maj,"ALTER TABLE $_DB_specs ADD CONSTRAINT specialites_composante_id_fkey 
                                                   FOREIGN KEY ($_DBU_specs_comp_id) REFERENCES $_DB_composantes($_DBU_composantes_id) ON UPDATE CASCADE ON DELETE CASCADE");
   } 

   db_free_result($res_conkey);

   // SPECIALITES ET FORMATIONS : MODIFICATION DU TYPE DE L'IDENTIFIANT (int => bigint)

   // On doit d'abord supprimer la vue (non documentée mais présente dans le shéma)
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                              AND table_name='view_formations'")))
     db_query($db_maj, "DROP VIEW view_formations");

   // On supprime aussi la valeur par défaut (séquence) pour les identifiants de spécialités
   db_query($db_maj, "ALTER TABLE $_DB_specs ALTER COLUMN $_DBU_specs_id DROP DEFAULT");

   // Déclaration d'un tableau "table => colonne" à mettre à jour ("colonne" peut être un autre tableau si plusieurs colonnes
   // sont à modifier)

   $array_maj_type=array("$_DB_propspec" => array("$_DBU_propspec_id_spec",
                                                  "$_DBU_propspec_id",
                                                  "$_DBU_propspec_comp_id"),
                         "$_DB_specs" => array("$_DBU_specs_id",
                                                  "$_DBU_specs_comp_id"),
                         "$_DB_filtres" => array("$_DBU_filtres_cond_spec_id",
                                                 "$_DBU_filtres_cible_spec_id",
                                                 "$_DBU_filtres_cond_propspec_id",
                                                 "$_DBU_filtres_cible_propspec_id",
                                                 "$_DBU_filtres_comp_id"),
                         "$_DB_cand" => "$_DBU_cand_propspec_id",
                         "$_DB_commissions" => "$_DBU_commissions_propspec_id",
                         "$_DB_dossiers_ef" => "$_DBU_dossiers_ef_propspec_id",
                         "$_DB_dossiers_elems_contenu" => "$_DBU_dossiers_elems_contenu_propspec_id",
                         "$_DB_groupes_spec" => "$_DBU_groupes_spec_propspec_id",
                         "$_DB_justifs_jf" => "$_DBU_justifs_jf_propspec_id",
                         "$_DB_justifs_ff" => "$_DBU_justifs_ff_propspec_id",
                         "$_DB_session" => "$_DBU_session_propspec_id",
                         "$_DB_courriels_propspec" => "$_DBU_courriels_propspec_propspec_id",
                         "$_DB_composantes" => "$_DBU_composantes_id",
                         "$_DB_acces" => "$_DBU_acces_composante_id",
                         "$_DB_acces_comp" => "$_DBU_acces_comp_composante_id",
                         "$_DB_cursus_justif" => "$_DBU_cursus_justif_comp_id",
                         "$_DB_decisions_comp" => "$_DBU_decisions_comp_comp_id",
                         "$_DB_dossiers_elems" => "$_DBU_dossiers_elems_comp_id",
                         "$_DB_dossiers_elems_contenu" => "$_DBU_dossiers_elems_contenu_comp_id",
                         "$_DB_hist" => "$_DBU_hist_comp_id",
                         "$_DB_comp_infos" => "$_DBU_comp_infos_comp_id",
                         "$_DB_justifs" => "$_DBU_justifs_comp_id",
                         "$_DB_justifs_fichiers" => "$_DBU_justifs_fichiers_comp_id",
                         "$_DB_lettres" => "$_DBU_lettres_comp_id",
                         "$_DB_motifs_refus" => "$_DBU_motifs_refus_comp_id",
                         "$_DB_mentions" => "$_DBU_mentions_comp_id",
                         "$_DB_lettres_propspec" => "$_DBU_lettres_propspec_propspec_id");

   foreach($array_maj_type as $table => $colonne)
   {
      if(is_array($colonne))
      {
         foreach($colonne as $col)
         {
            if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                              AND table_name='$table'
                                                                              AND column_name='$col'
                                                                              AND data_type NOT ILIKE 'bigint'")))
            {
               db_query($db_maj, "ALTER TABLE $table ALTER COLUMN $col TYPE bigint");
            }
         }
      }
      elseif(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                              AND table_name='$table'
                                                                              AND column_name='$colonne'
                                                                              AND data_type NOT ILIKE 'bigint'")))
      {
         db_query($db_maj, "ALTER TABLE $table ALTER COLUMN $colonne TYPE bigint");
      }
   }

   // TABLE pays_nationalite_iso_insee
   // Pas de clé dans cette table, car chaque champ peut être vide
   // Les contrôles devront être intégralement effectués par l'application
   $res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_DB_pays_nat_ii'");

   if(!db_num_rows($res_maj))
   {
      db_query($db_maj, "CREATE TABLE $_DB_pays_nat_ii (
                        $_DBU_pays_nat_ii_iso text,
                        $_DBU_pays_nat_ii_insee text,
                        $_DBU_pays_nat_ii_pays text,
                        $_DBU_pays_nat_ii_nat text)");
      // Données :
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('00','995','','Apatride')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AA','990','AUTRE','Autre')");

      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AF','212','AFGHANISTAN','Afghane')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('ZA','303','AFRIQUE DU SUD','Sud-africaine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AX','','ÎLES ÅLAND','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AL','125','ALBANIE','Albanaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('DZ','352','ALGÉRIE','Algérienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('DE','109','ALLEMAGNE','Allemande')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AD','130','ANDORRE','Andorrane')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AO','395','ANGOLA','Angolaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AI','425','ANGUILLA','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AQ','','ANTARCTIQUE','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AG','441','ANTIGUA-ET-BARBUDA','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AN','431','ANTILLES NÉERLANDAISES','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SA','201','ARABIE SAOUDITE','Saoudienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AR','415','ARGENTINE','Argentine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AM','252','ARMÉNIE','Arménienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AW','431','ARUBA','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AU','501','AUSTRALIE','Australienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AT','110','AUTRICHE','Autrichienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AZ','253','AZERBAÏDJAN','Azerbaïdjanaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BS','436','BAHAMAS','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BH','249','BAHREÏN','Bahreïnienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BD','246','BANGLADESH','Bangladaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BB','434','BARBADE','Barbadienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BY','148','BÉLARUS','Bélarussienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BE','131','BELGIQUE','Belge')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BZ','429','BELIZE','Bélizienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BJ','327','BÉNIN','Béninoise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BM','425','BERMUDES','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BT','214','BHOUTAN','Bhoutanaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BO','418','BOLIVIE','Bolivienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BA','118','BOSNIE-HERZÉGOVINE','Bosniaque')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BW','347','BOTSWANA','Botswanaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BV','103','ÎLE BOUVET','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BR','416','BRÉSIL','Brésilienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BN','225','BRUNÉI DARUSSALAM','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BG','111','BULGARIE','Bulgare')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BF','331','BURKINA FASO','Burkinabè')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BI','321','BURUNDI','Burundaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('KY','425','ÎLES CAÏMANES','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('KH','234','CAMBODGE','Cambodgienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CM','322','CAMEROUN','Camerounaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CA','401','CANADA','Canadienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CV','396','CAP-VERT','Cap-verdienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CF','323','RÉPUBLIQUE CENTRAFRICAINE','Centrafricaine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CL','417','CHILI','Chilienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CN','216','CHINE','Chinoise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CX','501','ÎLE CHRISTMAS','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CY','254','CHYPRE','Chypriote')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CC','501','ÎLES COCOS (KEELING)','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CO','419','COLOMBIE','Colombienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('KM','397','COMORES','Comorienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CG','324','CONGO','Congolaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CD','312','CONGO (RÉPUBLIQUE DÉMOCRATIQUE)','Congolaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CK','502','ÎLES COOK','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('KR','239','CORÉE DU SUD','Sud-coréenne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('KP','238','CORÉE DU NORD','Nord-coréenne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CR','406','COSTA RICA','Costaricienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CI','326','CÔTE D''IVOIRE','Ivoirienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('HR','119','CROATIE','Croate')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CU','407','CUBA','Cubaine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('DK','101','DANEMARK','Danoise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('DJ','399','DJIBOUTI','Djiboutienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('DO','438','RÉPUBLIQUE DOMINICAINE','Dominicaine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('DM','408','DOMINIQUE','Dominiquaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('EG','301','ÉGYPTE','Egyptienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SV','414','EL SALVADOR','Salvadorienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AE','247','ÉMIRATS ARABES UNIS','Emirats Arabes Unis')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('EC','420','ÉQUATEUR','Equatorienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('ER','317','ÉRYTHRÉE','Erythréenne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('ES','134','ESPAGNE','Espagnole')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('EE','106','ESTONIE','Estonienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('US','404','ÉTATS-UNIS','Américaine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('ET','315','ÉTHIOPIE','Ethiopienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('FK','427','ÎLES FALKLAND (MALOUINES)','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('FO','101','ÎLES FÉROÉ','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('FJ','508','FIDJI','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('FI','105','FINLANDE','Finlandaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('FR','100','FRANCE','Française')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GA','328','GABON','Gabonaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GM','304','GAMBIE','Gambienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GE','255','GÉORGIE','Géorgienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GS','427','GÉORGIE DU SUD ET LES ÎLES SANDWICH DU SUD','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GH','329','GHANA','Ghanéenne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GI','133','GIBRALTAR','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GR','126','GRÈCE','Grecque')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GD','435','GRENADE','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GL','430','GROENLAND','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GP','100','GUADELOUPE','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GU','505','GUAM','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GT','409','GUATEMALA','Guatémaltèque')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GG','132','GUERNESEY','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GN','330','GUINÉE','Guinéenne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GW','392','GUINÉE-BISSAU','Bissau-guinéenne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GQ','314','GUINÉE ÉQUATORIALE','Guinéo-équatorienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GY','428','GUYANA','Guyanienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GF','100','GUYANE FRANÇAISE','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('HT','410','HAÏTI','Haïtienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('HM','501','ÎLE HEARD et ÎLES MCDONALD','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('HN','411','HONDURAS','Hondurienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('HK','230','HONG-KONG','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('HU','112','HONGRIE','Hongroise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('IM','132','ÎLE DE MAN','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('UM','','ÎLES MINEURES ÉLOIGNÉES DES ÉTATS-UNIS','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('VG','425','ÎLES VIERGES BRITANNIQUES','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('VI','432','ÎLES VIERGES DES ÉTATS-UNIS','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('IN','223','INDE','Indienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('ID','231','INDONÉSIE','Indonésienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('IR','204','IRAN','Iranienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('IQ','203','IRAQ','Iraquienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('IE','136','IRLANDE','Irlandaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('IS','102','ISLANDE','Islandaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('IL','207','ISRAËL','Israëlienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('IT','127','ITALIE','Italienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('JM','426','JAMAÏQUE','Jamaïquaine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('JP','217','JAPON','Japonaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('JE','132','JERSEY','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('JO','222','JORDANIE','Jordanienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('KZ','256','KAZAKHSTAN','Kazakhe')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('KE','332','KENYA','Kenyane')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('KG','257','KIRGHIZISTAN','Kirghize')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('KI','513','KIRIBATI','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('KW','240','KOWEÏT','Koweïtienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('LA','241','LAOS','Laotienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('LS','348','LESOTHO','Lesothane')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('LV','107','LETTONIE','Lettone')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('LB','205','LIBAN','Libanaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('LR','302','LIBÉRIA','Libérienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('LY','316','LIBYE','Libyenne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('LI','113','LIECHTENSTEIN','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('LT','108','LITUANIE','Lituanienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('LU','137','LUXEMBOURG','Luxembourgeoise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MO','232','MACAO','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MK','156','MACÉDOINE','Macédonienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MG','333','MADAGASCAR','Malgache')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MY','227','MALAISIE','Malaisienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MW','334','MALAWI','Malawienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MV','229','MALDIVES','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('ML','335','MALI','Malienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MT','144','MALTE','Maltaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MP','505','ÎLES MARIANNES DU NORD','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MA','350','MAROC','Marocaine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MH','515','ÎLES MARSHALL','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MQ','100','MARTINIQUE','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MU','390','ÎLE MAURICE','Mauricienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MR','336','MAURITANIE','Mauritanienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('YT','100','MAYOTTE','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MX','405','MEXIQUE','Mexicaine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('FM','516','MICRONÉSIE (ÉTATS FÉDÉRÉS)','Micronésienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MD','151','MOLDOVA','Moldove')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MC','138','MONACO','Monégasque')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MN','242','MONGOLIE','Mongole')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('ME','120','MONTÉNÉGRO','Monténégrine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MS','425','MONTSERRAT','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MZ','393','MOZAMBIQUE','Mozambique')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MM','','MYANMAR','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('NA','311','NAMIBIE','Namibienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('NR','507','NAURU','Nauruane')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('NP','215','NÉPAL','Népalaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('NI','412','NICARAGUA','Nicaraguayenne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('NE','337','NIGER','Nigerienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('NG','338','NIGÉRIA','Nigériane')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('NU','502','NIUÉ','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('NF','501','ÎLE NORFOLK','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('NO','103','NORVÈGE','Norvégienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('NC','100','NOUVELLE-CALÉDONIE','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('NZ','502','NOUVELLE-ZÉLANDE','Néo-zélandaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('IO','308','OCÉAN INDIEN (TERRITOIRE BRITANNIQUE)','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('OM','250','OMAN','Omanaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('UG','339','OUGANDA','Ougandaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('UZ','258','OUZBÉKISTAN','Ouzbèke')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PK','213','PAKISTAN','Pakistanaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PW','517','PALAOS (ÎLES)','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PS','261','PALESTINE','Palestinienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PA','413','PANAMA','Panaméenne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PG','510','PAPOUASIE-NOUVELLE-GUINÉE','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PY','421','PARAGUAY','Paraguayenne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('NL','135','PAYS-BAS','Néerlandaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PE','422','PÉROU','Péruvienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PH','220','PHILIPPINES','Philippine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PN','503','PITCAIRN','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PL','122','POLOGNE','Polonaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PF','100','POLYNÉSIE FRANÇAISE','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PR','432','PUERTO RICO','Portoricaine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PT','139','PORTUGAL','Portugaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('QA','248','QATAR','Qatarienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('RE','100','RÉUNION','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('RO','114','ROUMANIE','Roumaine')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('GB','132','ROYAUME-UNI','Britannique')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('RU','123','RUSSIE','Russe')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('RW','340','RWANDA','Rwandaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('EH','389','SAHARA OCCIDENTAL','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('BL','100','SAINT-BARTHÉLEMY','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SH','306','SAINTE-HÉLÈNE','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('LC','439','SAINTE-LUCIE','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('KN','','SAINT-KITTS-ET-NEVIS','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SM','128','SAINT-MARIN','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('MF','','SAINT-MARTIN','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('PM','100','SAINT-PIERRE-ET-MIQUELON','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('VA','129','SAINT-SIÈGE (ÉTAT DE LA CITÉ DU VATICAN)','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('VC','440','SAINT-VINCENT-ET-LES GRENADINES','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SB','512','ÎLES SALOMON','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('WS','506','SAMOA','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('AS','505','SAMOA AMÉRICAINES','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('ST','394','SAO TOMÉ-ET-PRINCIPE','Santoméenne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SN','341','SÉNÉGAL','Sénégalaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('RS','121','SERBIE','Serbe')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SC','398','SEYCHELLES','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SL','342','SIERRA LEONE','Sierra-léonaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SG','226','SINGAPOUR','Singapourienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SK','117','SLOVAQUIE','Slovaque')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SI','145','SLOVÉNIE','Slovène')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SO','318','SOMALIE','Somalienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SD','343','SOUDAN','Soudanaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('LK','235','SRI LANKA','Sri-lankaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SE','104','SUÈDE','Suédoise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CH','140','SUISSE','Suisse')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SR','437','SURINAME','Surinamaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SJ','103','SVALBARD ET ÎLE JAN MAYEN','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SZ','391','SWAZILAND','Swazie')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('SY','206','SYRIE','Syrienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TJ','259','TADJIKISTAN','Tadjike')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TW','236','TAÏWAN','Taïwanaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TZ','309','TANZANIE','Tanzanienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TD','344','TCHAD','Tchadienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('CZ','116','TCHÈQUE (RÉPUBLIQUE)','Tchèque')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TF','100','TERRES AUSTRALES FRANÇAISES','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TH','219','THAÏLANDE','Thaïlandaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TL','','TIMOR-LESTE','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TG','345','TOGO','Togolaise')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TK','502','TOKELAU','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TO','509','TONGA','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TT','433','TRINITÉ-ET-TOBAGO','Trinidadienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TN','351','TUNISIE','Tunisienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TM','260','TURKMÉNISTAN','Turkmène')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TC','425','ÎLES TURKS ET CAÏQUES','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TR','208','TURQUIE','Turque')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('TV','511','TUVALU','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('UA','155','UKRAINE','Ukrainienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('UY','423','URUGUAY','Urugayenne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('VU','514','VANUATU','Vanuatuane')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('VE','424','VENEZUELA','Vénézuélienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('VN','243','VIET NAM','Vietnamienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('WF','100','WALLIS ET FUTUNA','')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('YE','251','YÉMEN','Yéménite')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('ZM','346','ZAMBIE','Zambienne')");
      db_query($db_maj, "INSERT INTO $_DB_pays_nat_ii VALUES ('ZW','310','ZIMBABWE','Zimbabwéenne')");
   }

   // </pays_nat>

   // Liste des départements français

   $res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_DB_departements_fr'");

   if(!db_num_rows($res_maj))
   {
      db_query($db_maj, "CREATE TABLE $_DB_departements_fr (
                        $_DBU_departements_fr_numero text PRIMARY KEY,
                        $_DBU_departements_fr_nom text not null)");

      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('01','Ain')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('02','Aisne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('03','Allier')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('04','Alpes-de-Haute-Provence')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('05','Hautes-Alpes')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('06','Alpes-Maritimes')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('07','Ardèche')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('08','Ardennes')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('09','Ariège')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('10','Aube')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('11','Aude')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('12','Aveyron')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('13','Bouches-du-Rhône')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('14','Calvados')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('15','Cantal')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('16','Charente')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('17','Charente-Maritime')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('18','Cher')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('19','Corrèze')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('2A','Corse-du-Sud')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('2B','Haute-Corse')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('21','Côte-d''Or')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('22','Côtes-d''Armor')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('23','Creuse')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('24','Dordogne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('25','Doubs')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('26','Drôme')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('27','Eure')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('28','Eure-et-Loir')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('29','Finistère')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('30','Gard')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('31','Haute-Garonne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('32','Gers')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('33','Gironde')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('34','Hérault')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('35','Ille-et-Vilaine')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('36','Indre')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('37','Indre-et-Loire')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('38','Isère')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('39','Jura')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('40','Landes')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('41','Loir-et-Cher')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('42','Loire')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('43','Haute-Loire')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('44','Loire-Atlantique')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('45','Loiret')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('46','Lot')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('47','Lot-et-Garonne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('48','Lozère')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('49','Maine-et-Loire')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('50','Manche')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('51','Marne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('52','Haute-Marne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('53','Mayenne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('54','Meurthe-et-Moselle')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('55','Meuse')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('56','Morbihan')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('57','Moselle')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('58','Nièvre')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('59','Nord')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('60','Oise')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('61','Orne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('62','Pas-de-Calais')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('63','Puy-de-Dôme')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('64','Pyrénées-Atlantiques')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('65','Hautes-Pyrénées')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('66','Pyrénées-Orientales')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('67','Bas-Rhin')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('68','Haut-Rhin')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('69','Rhône')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('70','Haute-Saône')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('71','Saône-et-Loire')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('72','Sarthe')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('73','Savoie')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('74','Haute-Savoie')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('75','Paris')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('76','Seine-Maritime')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('77','Seine-et-Marne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('78','Yvelines')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('79','Deux-Sèvres')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('80','Somme')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('81','Tarn')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('82','Tarn-et-Garonne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('83','Var')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('84','Vaucluse')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('85','Vendée')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('86','Vienne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('87','Haute-Vienne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('88','Vosges')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('89','Yonne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('90','Territoire de Belfort')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('91','Essonne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('92','Hauts-de-Seine')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('93','Seine-Saint-Denis')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('94','Val-de-Marne')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('95','Val-d''Oise')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('971','Guadeloupe')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('972','Martinique')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('973','Guyane')");
      db_query($db_maj, "INSERT INTO $_DB_departements_fr VALUES('974','La Réunion')");
   }

   // SUPPRESSION DES TABLES OBSOLETES
   $array_table_suppr=array("justif_docs_elements",
                            "justif_element_filiere",
                            "justif_elements",
                            "justif_fichiers_filieres",
                            "justif_fichiers",
                            "justif_documents",
                            "msg_candidat_fichiers",
                            "msg_candidat",
                            "msg_gestion_fichiers",
                            "msg_gestion",
                            "php_sessions");
   
   foreach($array_table_suppr as $table)
   {
      if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                                 AND table_name='$table'")))
         db_query($db_maj, "DROP TABLE $table");
   }

   // TABLE CANDIDAT
   // Ajout du département de naissance (important pour les exports vers Apogée)
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_candidat'
                                                                               AND column_name='$_DBU_candidat_dpt_naissance'")))
      db_query($db_maj, "ALTER TABLE $_DB_candidat ADD COLUMN $_DBU_candidat_dpt_naissance text default '';
                         UPDATE $_DB_candidat SET $_DBU_candidat_dpt_naissance='';");

   // Flag et année si le candidat a déjà été inscrit dans cette université
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_candidat'
                                                                               AND column_name='$_DBU_candidat_deja_inscrit'")))
      db_query($db_maj, "ALTER TABLE $_DB_candidat ADD COLUMN $_DBU_candidat_deja_inscrit smallint default '0';
                         UPDATE $_DB_candidat SET $_DBU_candidat_deja_inscrit='0';");

   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_candidat'
                                                                               AND column_name='$_DBU_candidat_annee_premiere_inscr'")))
      db_query($db_maj, "ALTER TABLE $_DB_candidat ADD COLUMN $_DBU_candidat_annee_premiere_inscr text default '';
                         UPDATE $_DB_candidat SET $_DBU_candidat_annee_premiere_inscr='';");


   // Champs temporaires : ils seront rapatriés vers le cursus pour la version 2010-2011
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_candidat'
                                                                               AND column_name='$_DBU_candidat_annee_bac'")))
      db_query($db_maj, "ALTER TABLE $_DB_candidat ADD COLUMN $_DBU_candidat_annee_bac text default '';
                         UPDATE $_DB_candidat SET $_DBU_candidat_annee_bac='';");

   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_candidat'
                                                                               AND column_name='$_DBU_candidat_serie_bac'")))
      db_query($db_maj, "ALTER TABLE $_DB_candidat ADD COLUMN $_DBU_candidat_serie_bac text default '';
                         UPDATE $_DB_candidat SET $_DBU_candidat_serie_bac='';");

   // </CANDIDAT>

   // TABLE CANDIDATURE
   // Ajout d'un entier "rappels" : nombre de rappels déjà envoyés au cas où la candidature ne serait pas verrouillable
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_cand'
                                                                               AND column_name='$_DBU_cand_rappels'")))
      db_query($db_maj, "ALTER TABLE $_DB_cand ADD COLUMN $_DBU_cand_rappels smallint default '0';
                         UPDATE $_DB_cand SET $_DBU_cand_rappels='0';");   


   // TABLE DOSSIERS_ELEMENTS
   // Ajout d'un booléen "nouvelle_page" : indique si la réponse du candidat doit être imprimée sur une nouvelle page de son récapitulatif
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_dossiers_elems'
                                                                               AND column_name='$_DBU_dossiers_elems_nouvelle_page'")))
      db_query($db_maj, "ALTER TABLE $_DB_dossiers_elems ADD COLUMN $_DBU_dossiers_elems_nouvelle_page BOOLEAN DEFAULT 'f';
                         UPDATE $_DB_dossiers_elems SET $_DBU_dossiers_elems_nouvelle_page='f'");

   // Ajout d'un booléen "extractions" : indique si la réponse du candidat doit figurer dans les extractions CSV (une question/réponse par colonne)
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_dossiers_elems'
                                                                               AND column_name='$_DBU_dossiers_elems_extractions'")))
      db_query($db_maj, "ALTER TABLE $_DB_dossiers_elems ADD COLUMN $_DBU_dossiers_elems_extractions BOOLEAN DEFAULT 'f';
                         UPDATE $_DB_dossiers_elems SET $_DBU_dossiers_elems_extractions='f'");


   // acces_candidats_lus : ajout de la période
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_acces_candidats_lus'
                                                                               AND column_name='$_DBU_acces_candidats_lus_periode'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_acces_candidats_lus ADD COLUMN $_DBU_acces_candidats_lus_periode TEXT;
                         UPDATE $_DB_acces_candidats_lus SET $_DBU_acces_candidats_lus_periode='2009';");
   }

   // CODES BAC (pour les transferts vers apogée) - cette table doit être intégrée à l'application et non au plugin Apogée
   // car le cursus des candidats est un élément très imbriqué dans l'application

   $res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_DB_diplomes_bac'");

   if(!db_num_rows($res_maj))
   {
      db_query($db_maj, "CREATE TABLE $_DB_diplomes_bac (
                        $_DBU_diplomes_bac_code text PRIMARY KEY,
                        $_DBU_diplomes_bac_intitule text)");

      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('C','C-mathématiques et sciences physiques')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('D','D-mathématiques et sciences de la nature')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('A','A-philosophie-lettres X')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('B','B-économique et social')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('A1','A1-lettres-sciences')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('A2','A2-lettres-langues')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('A3','A3-lettres-arts plastiques')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('DP','D''-sciences agronomiques et techniques')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('E','E-Mathématiques et techniques')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0001','0001-bac international')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F1','F1-construction mécanique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F2','F2-électronique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F3','F3-électrotechnique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F4','F4-génie civil')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F5','F5-physique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F6','F6-chimie')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F7','F7-biologie option biochimie')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F8','F8-sciences médico-sociales')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F9','F9-équipement technique-bâtiment')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F10','F10-microtechnique (avant 1984)')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F10A','F10A-microtechnique option appareillage')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F10B','F10B-microtechnique option optique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F11','F11-musique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F11P','F11P-danse')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F12','F12-arts appliqués')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F','F-spécialité non précisée')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('G1','G1-techniques administratives')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('G2','G2-techniques quantitatives de gestion')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('G3','G3-techniques commerciales')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('G','G-spécialité non précisée')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('H','H-techniques informatiques')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0021','0021-bacs professionnels industriels')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0022','0022-bacs professionnels tertiaires')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0031','0031-titre étranger admis en équivalence')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0032','0032-titre français admis en dispense')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0033','0033-ESEU A')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0034','0034-ESEU B')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0035','0035-promotion sociale')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0036','0036-validation études expériences prof.')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0037','0037-autres cas de non bacheliers')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0000','0000-sans bac')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('L','L-Littérature')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('S','S-Scientifique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('ES','ES-Economique et social')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('STT','STT-Sciences et technologies tertiaires')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('STI','STI-Sciences et techniques industrielles')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('STL','STL-Sciences et techno. de laboratoire')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('SMS','SMS-Sciences Médico-Sociales')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('DAEA','Dip. d''accès aux études universitaires A')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('DAEB','Dip. d''accès aux études universitaires B')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('STPA','STPA-Sciences et techno. prod agro-alim.')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('STAE','STAE-Sciences et techno. agronomie-env.')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('F7P','F7P-biologie option biologie')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('HOT','HOT-Hôtellerie')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0030','0030-capacité de droit')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('0023','0023-bacs professionnels agricoles')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('A4','A4 -langues mathématiques (avant 1984)')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('A5','A5-Langues (avant 1984)')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('STG','STG-Sciences et technologies de gestion')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('T4','STT-Informatique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('B1','STL-Biochimie')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('B2','STL-Chimie')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('B3','STL-Physique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('ES1','ES1 sciences éco. et sociales')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('ES3','ES3 Eco soc - langue vivante renforcée')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('ES4','ES4 Eco soc - langue vivante 3')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('I1','STI-Genie mecanique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('I2','STI-Genie civil')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('I3','STI-Genie energie')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('I6','STI-Genie materiaux')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('L1','L1-littérature - langue vivante 3')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('L2','L2-littérature - langue vivante renforcée')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('L3','L3-littérature - langue régionale')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('L6','L6-littérature - grec ancien')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('S1','S1-Scientifique-Vie et Terre-Math')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('S3','S3-Scientifique-Sciences Vie et Terre')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('STIA','STI-Sc.Tecno.Ind.arts appliqués')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('T1','STT-Action commerciale')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('T2','STT-Action administrative')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('T3','STT-Comptabilite')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('ES2','ES2 Eco soc - math appliquées')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('I4','STI-Genie electronique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('I5','STI-Genie electrotechnique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('L4','L4-littérature - mathématique')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('L5','L5-littérature - latin')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('L7','L7-littérature - arts')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('S2','S2-Scientifique-V&T-Physique Chimie')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('S4','S4-Scientifique-V&T-Techno.Industrielle')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('S5','S5-Scientifique-V&T-Biologie écologie')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('SCI','Sciences de l''Ingénieur')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('ST2S','ST2S-Sciences et techno. Santé et Social')");
      db_query($db_maj, "INSERT INTO $_DB_diplomes_bac VALUES ('STAV','STAV-Sciences et techno. Agronom.Vivant')");
   }

   // Table "années" : ajout de la colonne "ordre"

   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_annees'
                                                                               AND column_name='$_DBU_annees_ordre'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_annees ADD COLUMN $_DBU_annees_ordre SMALLINT;
                         UPDATE $_DB_annees SET $_DBU_annees_ordre=$_DBU_annees_id;");
   }

   // TABLE systeme
   // Table intégrant certains paramètres du fichier "config.php"
   $res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_DB_systeme'");

   if(!db_num_rows($res_maj))
   {
      db_query($db_maj, "CREATE TABLE $_DB_systeme (
                        $_DBU_systeme_titre_html text,
                        $_DBU_systeme_titre_page text,
                        $_DBU_systeme_ville text,
                        $_DBU_systeme_url_candidat text,
                        $_DBU_systeme_url_gestion text,
                        $_DBU_systeme_meta text,
                        $_DBU_systeme_admin text,
                        $_DBU_systeme_signature_courriels text,
                        $_DBU_systeme_signature_admin text,
                        $_DBU_systeme_courriel_admin text,
                        $_DBU_systeme_info_liberte text,
                        $_DBU_systeme_limite_periode smallint,
                        $_DBU_systeme_limite_masse smallint,
                        $_DBU_systeme_defaut_decision boolean default 't',
                        $_DBU_systeme_defaut_motifs boolean default 't',
                        $_DBU_systeme_max_rappels smallint,
                        $_DBU_systeme_rappel_delai_sup smallint,
                        $_DBU_systeme_debug boolean default 't',
                        $_DBU_systeme_debug_rappel_id boolean default 'f',
                        $_DBU_systeme_debug_cursus boolean default 'f',
                        $_DBU_systeme_debug_statut_prec boolean default 'f',
                        $_DBU_systeme_debug_lock boolean default 'f',
                        $_DBU_systeme_debug_enregistrement boolean default 't',
                        $_DBU_systeme_debug_sujet text,
                        $_DBU_systeme_erreur_sujet text,
                        $_DBU_systeme_arg_key text)");
      // Données : tentative de récupération depuis une configuration existante, valeurs par défaut sinon
   }

   // Conversion de la configuration "fichier" vers la base
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM $_DB_systeme")))
   {
      if(is_file(dirname(__FILE__) . "/../configuration/config.php"))
         include dirname(__FILE__) . "/../configuration/config.php";

      $orig_conf_titre_html=isset($GLOBALS["__TITRE_HTML"]) ? preg_replace("/[']+/", "''", $GLOBALS["__TITRE_HTML"]) : "ARIA - Précandidatures en ligne";
      $orig_conf_titre_page=isset($GLOBALS["__TITRE_PAGE"]) ? preg_replace("/[']+/", "''", $GLOBALS["__TITRE_PAGE"]) : "<Nom de l''Université>";
      $orig_conf_ville=isset($GLOBALS["__VILLE"]) ? preg_replace("/[']+/", "''", $GLOBALS["__VILLE"]) : "Strasbourg";
      $orig_conf_url_candidat=isset($GLOBALS["__URL_CANDIDAT"]) ? preg_replace("/[']+/", "''", $GLOBALS["__URL_CANDIDAT"]) : "http://" . $_SERVER["SERVER_NAME"] . str_replace("include/update_db.php", "", str_replace($_SERVER["DOCUMENT_ROOT"], "", __FILE__));
      $orig_conf_url_gestion=isset($GLOBALS["__URL_GESTION"]) ? preg_replace("/[']+/", "''", $GLOBALS["__URL_GESTION"]) : "http://" . $_SERVER["SERVER_NAME"] . str_replace("include/update_db.php", "gestion/", str_replace($_SERVER["DOCUMENT_ROOT"], "", __FILE__));

      // $orig_conf_meta=isset($GLOBALS["__META"]) ? preg_replace("/[']+/", "''", $GLOBALS["__META"]) : "";

      if(isset($GLOBALS["__META"]))
         $orig_conf_meta=preg_replace("/<[[:alnum:][:space:]=']+ content='([[:alnum:][:punct:][:space:]-éèëêîïöôàä]*)'>/i", "\\1", $GLOBALS["__META"]);
      else
         $orig_conf_meta="";

      $orig_conf_admin=isset($GLOBALS["__NOM_ADMIN"]) ? preg_replace("/[']+/", "''", $GLOBALS["__NOM_ADMIN"]) : "";
      $orig_conf_signature_courriels=isset($GLOBALS["__SIGNATURE_COURRIELS"]) ? preg_replace("/[']+/", "''", $GLOBALS["__SIGNATURE_COURRIELS"]) : "";
      $orig_conf_signature_admin=isset($GLOBALS["__SIGNATURE_ADMIN"]) ? preg_replace("/[']+/", "''", $GLOBALS["__SIGNATURE_ADMIN"]) : "";
      $orig_conf_courriel_admin=isset($GLOBALS["__EMAIL_ADMIN"]) ? preg_replace("/[']+/", "''", $GLOBALS["__EMAIL_ADMIN"]) : "";
      $orig_conf_info_liberte=isset($GLOBALS["__INFORMATIQUE_ET_LIBERTES"]) ? preg_replace("/[']+/", "''", $GLOBALS["__INFORMATIQUE_ET_LIBERTES"]) : "Les informations vous concernant font l''objet d''un traitement  informatique destiné à gérer les précandidatures en ligne. L''unique destinataire des données est (Nom de l''établissement). Conformément à la loi ''Informatique et Libertés'' du 6 janvier 1978, vous bénéficiez d''un droit d''accès et de rectification à ces informations. Si vous souhaitez exercer ce droit et obtenir communication de ces dernières, veuillez vous adresser à <Nom de l''administrateur> (par courriel [mail=adresse_administrateur@domaine]à cette adresse[/mail]). Vous pouvez également, pour des motifs légitimes, vous opposer au traitement des données vous concernant.";
      $orig_conf_limite_periode=isset($GLOBALS["__MOIS_LIMITE_CANDIDATURE"]) ? preg_replace("/[']+/", "''", $GLOBALS["__MOIS_LIMITE_CANDIDATURE"]) : "03";
      $orig_conf_limite_masse=isset($GLOBALS["__MAX_CAND_MASSE"]) ? preg_replace("/[']+/", "''", $GLOBALS["__MAX_CAND_MASSE"]) : "40";
      $orig_conf_defaut_decision=isset($GLOBALS["__DEFAUT_DECISIONS"]) ? preg_replace("/[']+/", "''", $GLOBALS["__DEFAUT_DECISIONS"]) : "t";
      $orig_conf_defaut_motifs=isset($GLOBALS["__DEFAUT_MOTIFS"]) ? preg_replace("/[']+/", "''", $GLOBALS["__DEFAUT_MOTIFS"]) : "t";
      $orig_conf_max_rappels=isset($GLOBALS["__MAX_RAPPELS"]) ? preg_replace("/[']+/", "''", $GLOBALS["__MAX_RAPPELS"]) : "3";
      $orig_conf_rappel_delai_sup=isset($GLOBALS["__AJOUT_VERROUILLAGE_JOURS"]) ? preg_replace("/[']+/", "''", $GLOBALS["__AJOUT_VERROUILLAGE_JOURS"]) : "2";
      $orig_conf_debug=isset($GLOBALS["__DEBUG"]) ? preg_replace("/[']+/", "''", $GLOBALS["__DEBUG"]) : "t";
      $orig_conf_debug_rappel_id=isset($GLOBALS["__DEBUG_RAPPEL_IDENTIFIANTS"]) ? preg_replace("/[']+/", "''", $GLOBALS["__DEBUG_RAPPEL_IDENTIFIANTS"]) : "f";
      $orig_conf_debug_cursus=isset($GLOBALS["__DEBUG_CURSUS"]) ? preg_replace("/[']+/", "''", $GLOBALS["__DEBUG_CURSUS"]) : "f";
      $orig_conf_debug_statut_prec=isset($GLOBALS["__DEBUG_STATUT_PREC"]) ? preg_replace("/[']+/", "''", $GLOBALS["__DEBUG_STATUT_PREC"]) : "f";
      $orig_conf_debug_lock=isset($GLOBALS["__DEBUG_LOCK"]) ? preg_replace("/[']+/", "''", $GLOBALS["__DEBUG_LOCK"]) : "f";
      $orig_conf_debug_enregistrement=isset($GLOBALS["__DEBUG_ENREGISTREMENT"]) ? preg_replace("/[']+/", "''", $GLOBALS["__DEBUG_ENREGISTREMENT"]) : "t";
      $orig_conf_debug_sujet=isset($GLOBALS["__DEBUG_SUJET"]) ? preg_replace("/[']+/", "''", $GLOBALS["__DEBUG_SUJET"]) : "[DBG - ARIA]";
      $orig_conf_erreur_sujet=isset($GLOBALS["__ERREUR_SUJET"]) ? preg_replace("/[']+/", "''", $GLOBALS["__ERREUR_SUJET"]) : "[Erreur ARIA]";

      if(isset($arg_key))
         $orig_conf_arg_key=$arg_key;
      else
      {
         srand((double)microtime()*1000000);
         $arg_key=substr(md5(rand(0,9999)), 12, 8);
      }

      if(is_file(dirname(__FILE__)."/../configuration/aria_config.php"))
         include dirname(__FILE__)."/../configuration/aria_config.php";

      db_query($db_maj, "INSERT INTO $_DB_systeme VALUES('$orig_conf_titre_html', '$orig_conf_titre_page','$orig_conf_ville','$orig_conf_url_candidat',
         '$orig_conf_url_gestion','$orig_conf_meta','$orig_conf_admin','$orig_conf_signature_courriels','$orig_conf_signature_admin','$orig_conf_courriel_admin',
         '$orig_conf_info_liberte','$orig_conf_limite_periode','$orig_conf_limite_masse','$orig_conf_defaut_decision','$orig_conf_defaut_motifs','$orig_conf_max_rappels',
         '$orig_conf_rappel_delai_sup','$orig_conf_debug','$orig_conf_debug_rappel_id','$orig_conf_debug_cursus','$orig_conf_debug_statut_prec',
         '$orig_conf_debug_lock','$orig_conf_debug_enregistrement','$orig_conf_debug_sujet', '$orig_conf_erreur_sujet','$arg_key')");
   }

   // ==============================================================================

   // MISES A JOUR DES SEQUENCES
   // Comment être sûr que la table "pg_statio_all_sequences" existe bien ... ?

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_statio_all_sequences WHERE relname='annees_id_seq'")))
      db_query($db_maj, "SELECT setval('annees_id_seq', (SELECT max($_DBC_annees_id) FROM $_DB_annees))");

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_statio_all_sequences WHERE relname='cursus_diplomes_id_seq'")))
      db_query($db_maj, "SELECT setval('cursus_diplomes_id_seq', (SELECT max($_DBC_cursus_diplomes_id) FROM $_DB_cursus_diplomes))");

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_statio_all_sequences WHERE relname='cursus_mentions_id_seq'")))
      db_query($db_maj, "SELECT setval('cursus_mentions_id_seq', (SELECT max($_DBC_cursus_mentions_id) FROM $_DB_cursus_mentions))");

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_statio_all_sequences WHERE relname='liste_langues_id_seq'")))
      db_query($db_maj, "SELECT setval('liste_langues_id_seq', (SELECT max($_DBC_liste_langues_id) FROM $_DB_liste_langues))");

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_statio_all_sequences WHERE relname='motifs_refus_id_seq'")))
      db_query($db_maj, "SELECT setval('motifs_refus_id_seq', (SELECT max($_DBC_motifs_refus_id) FROM $_DB_motifs_refus))");

   // Cette séquence est obsolète avec les nouveaux identifiants : DROP
   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_statio_all_sequences WHERE relname='specialites_id_seq'")))
      db_query($db_maj, "DROP SEQUENCE specialites_id_seq");

   // 26/01/2010 - NETTOYAGE DU SCHEMA
   // Les noms de certaines tables et colonnes sont mentionnés directement (sans passer par la variable $_DB** car ces 
   // dernières ont également été supprimées
   
   // Table Candidat
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_candidat'
                                                                               AND column_name='numero_ulp'")))
      db_query($db_maj,"ALTER TABLE $_DB_candidat DROP COLUMN numero_ulp");
   
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_candidat'
                                                                               AND column_name='numero_umb'")))
      db_query($db_maj,"ALTER TABLE $_DB_candidat DROP COLUMN numero_umb");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_candidat'
                                                                               AND column_name='numero_urs'")))
      db_query($db_maj,"ALTER TABLE $_DB_candidat DROP COLUMN numero_urs");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_candidat'
                                                                               AND column_name='mode'")))
      db_query($db_maj,"ALTER TABLE $_DB_candidat DROP COLUMN mode");


   // Table Candidatures
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_cand'
                                                                               AND column_name='accepte_transmission'")))
      db_query($db_maj,"ALTER TABLE $_DB_cand DROP COLUMN accepte_transmission");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_cand'
                                                                               AND column_name='m2cci'")))   
      db_query($db_maj,"ALTER TABLE $_DB_cand DROP COLUMN m2cci");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_cand'
                                                                               AND column_name='avis'")))
      db_query($db_maj,"ALTER TABLE $_DB_cand DROP COLUMN avis");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_cand'
                                                                               AND column_name='imprime'")))
      db_query($db_maj,"ALTER TABLE $_DB_cand DROP COLUMN imprime");
   
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_cand'
                                                                               AND column_name='envoi_mail'")))
      db_query($db_maj,"ALTER TABLE $_DB_cand DROP COLUMN envoi_mail");
      
   // Candidatures extérieures
   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='candidatures_exterieures'")))
      db_query($db_maj,"DROP TABLE candidatures_exterieures");
   
   // Ancienne configuration   
   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='configuration'")))
      db_query($db_maj,"DROP TABLE configuration");

   // Concours
   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='concours'")))
      db_query($db_maj,"DROP TABLE concours");

   // Cursus
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_cursus'
                                                                               AND column_name='justifie'")))
      db_query($db_maj,"ALTER TABLE $_DB_cursus DROP COLUMN justifie");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_cursus'
                                                                               AND column_name='precision'")))
      db_query($db_maj,"ALTER TABLE $_DB_cursus DROP COLUMN precision");

   // Tables liées au cursus
   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_statio_all_sequences WHERE relname='cursus_concours_id_seq'")))
      db_query($db_maj,"DROP SEQUENCE cursus_concours_id_seq");
   
   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='cursus_concours'")))
      db_query($db_maj,"DROP TABLE cursus_concours");

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_statio_all_sequences WHERE relname='cursus_ecoles_id_seq'")))
      db_query($db_maj,"DROP SEQUENCE cursus_ecoles_id_seq");
   
   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='cursus_ecoles'")))
      db_query($db_maj,"DROP TABLE cursus_ecoles");

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_statio_all_sequences WHERE relname='cursus_filieres_id_seq'")))
      db_query($db_maj,"DROP SEQUENCE cursus_filieres_id_seq");
   
   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='cursus_filieres'")))
      db_query($db_maj,"DROP TABLE cursus_filieres");

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_statio_all_sequences WHERE relname='cursus_resultats_concour_id_seq'")))
      db_query($db_maj,"DROP SEQUENCE cursus_resultats_concour_id_seq");
   
   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='cursus_resultats_concours'")))
      db_query($db_maj,"DROP TABLE cursus_resultats_concours");

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='dates'")))
      db_query($db_maj,"DROP TABLE dates");

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_statio_all_sequences WHERE relname='inscriptions_avis_id_seq'")))
      db_query($db_maj,"DROP SEQUENCE inscriptions_avis_id_seq");
   
   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='inscriptions_avis'")))
      db_query($db_maj,"DROP TABLE inscriptions_avis");

   // Lettres : renommage de certains champs et des contraintes
   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='lettres_filieres'")))
      db_query($db_maj,"ALTER TABLE lettres_filieres RENAME TO lettres_propspec");
   
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='lettres_propspec'
                                                                               AND column_name='filiere_id'")))
      db_query($db_maj,"ALTER TABLE lettres_propspec RENAME filiere_id TO propspec_id");

   if(db_num_rows(db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='lettres_filieres_lettre_id_fkey'"))
      && !db_num_rows(db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='lettres_propspec_lettre_id_fkey'")))
   {
      db_query($db_maj,"ALTER TABLE lettres_propspec DROP CONSTRAINT lettres_filieres_lettre_id_fkey");
      db_query($db_maj,"ALTER TABLE lettres_propspec ADD CONSTRAINT lettres_propspec_lettre_id_fkey FOREIGN KEY (lettre_id) REFERENCES lettres(id) ON UPDATE CASCADE ON DELETE CASCADE");
   }
   
   if(db_num_rows(db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='lettres_filieres_filiere_id_fkey'"))
      && !db_num_rows(db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='lettres_propspec_propspec_id_fkey'")))
   {
      db_query($db_maj,"ALTER TABLE lettres_propspec DROP CONSTRAINT lettres_filieres_filiere_id_fkey");
      db_query($db_maj,"ALTER TABLE lettres_propspec ADD CONSTRAINT lettres_propspec_propspec_id_fkey FOREIGN KEY (propspec_id) REFERENCES propspec(id) ON UPDATE CASCADE ON DELETE CASCADE");
   }

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='pays_nationalites'")))
      db_query($db_maj,"DROP TABLE pays_nationalites");

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_statio_all_sequences WHERE relname='pays_nationalites_id_seq'")))
      db_query($db_maj,"DROP SEQUENCE pays_nationalites_id_seq");

   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_propspec'
                                                                               AND column_name='code'")))   
      db_query($db_maj,"ALTER TABLE $_DB_propspec DROP COLUMN code");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_propspec'
                                                                               AND column_name='lettre_information'")))   
      db_query($db_maj,"ALTER TABLE $_DB_propspec DROP COLUMN lettre_information");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_propspec'
                                                                               AND column_name='modalites_inscriptions'")))   
      db_query($db_maj,"ALTER TABLE $_DB_propspec DROP COLUMN modalites_inscriptions");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_propspec'
                                                                               AND column_name='date_ouverture'")))   
      db_query($db_maj,"ALTER TABLE $_DB_propspec DROP COLUMN date_ouverture");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_propspec'
                                                                               AND column_name='date_fermeture'")))   
      db_query($db_maj,"ALTER TABLE $_DB_propspec DROP COLUMN date_fermeture");
        
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_propspec'
                                                                               AND column_name='date_commission'")))   
      db_query($db_maj,"ALTER TABLE $_DB_propspec DROP COLUMN date_commission");

   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_propspec'
                                                                               AND column_name='annee'")))   
      db_query($db_maj,"ALTER TABLE $_DB_propspec RENAME annee TO annee_id");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_propspec'
                                                                               AND column_name='id_spec'")))   
      db_query($db_maj,"ALTER TABLE $_DB_propspec RENAME id_spec TO spec_id");

      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_specs'
                                                                               AND column_name='type'")))   
      db_query($db_maj,"ALTER TABLE $_DB_specs RENAME type TO mention_id");

   // Types spécialités => renommage en "Mentions"
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='types_specialites'
                                                                               AND column_name='type'")))   
      db_query($db_maj,"ALTER TABLE types_specialites RENAME type TO id");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='types_specialites'
                                                                               AND column_name='type_court'")))   
      db_query($db_maj,"ALTER TABLE types_specialites RENAME type_court TO nom_court");

   if(db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='types_specialites'")))
      db_query($db_maj,"ALTER TABLE types_specialites RENAME TO mentions");
      
   if(db_num_rows(db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='types_specialites_composante_id_fkey'"))
      && !db_num_rows(db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='mentions_composante_id_fkey'")))
   {
      db_query($db_maj,"ALTER TABLE mentions DROP CONSTRAINT types_specialites_composante_id_fkey");
      db_query($db_maj,"ALTER TABLE mentions ADD CONSTRAINT mentions_composante_id_fkey FOREIGN KEY (composante_id) REFERENCES composantes(id) ON UPDATE CASCADE ON DELETE CASCADE");
   }
   
   // Renommage de la clé primaire de la table
   if(db_num_rows(db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='types_pkey'"))
      && !db_num_rows(db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='mentions_pkey'"))
      && db_num_rows(db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='specialites_type_fkey'"))
      && !db_num_rows(db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='specialites_mention_id_fkey'")))
   {
      // Renommage en parallèle de la contrainte pour la table "specialites"
      db_query($db_maj,"ALTER TABLE specialites DROP CONSTRAINT specialites_type_fkey");
      
      db_query($db_maj,"ALTER TABLE mentions DROP CONSTRAINT types_pkey");
      db_query($db_maj,"ALTER TABLE mentions ADD CONSTRAINT mentions_pkey PRIMARY KEY (id)");      
      
      db_query($db_maj,"ALTER TABLE specialites ADD CONSTRAINT speclialites_mention_id_fkey FOREIGN KEY (mention_id) REFERENCES mentions(id) ON UPDATE CASCADE ON DELETE CASCADE");
   }
   
   // Table Universités : nettoyage
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_universites'
                                                                               AND column_name='code_apogee'")))
      db_query($db_maj,"ALTER TABLE $_DB_universites DROP COLUMN code_apogee");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_universites'
                                                                               AND column_name='couleur_menu'")))
      db_query($db_maj,"ALTER TABLE $_DB_universites DROP COLUMN couleur_menu");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_universites'
                                                                               AND column_name='couleur_menu2'")))   
      db_query($db_maj,"ALTER TABLE $_DB_universites DROP COLUMN couleur_menu2");
      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_universites'
                                                                               AND column_name='couleur_fond'")))
      db_query($db_maj,"ALTER TABLE $_DB_universites DROP COLUMN couleur_fond");
         
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_universites'
                                                                               AND column_name='fond_page'")))
      db_query($db_maj,"ALTER TABLE $_DB_universites DROP COLUMN fond_page");

   // Tables accès et historique : les valeurs des niveaux doivent être mises à jours
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM $_DB_acces WHERE $_DBC_acces_niveau IN ('-10','5','10','20','30','40','50','60')")))
   {
      db_query($db_maj,"UPDATE $_DB_acces SET $_DBU_acces_niveau=(10*$_DBU_acces_niveau) WHERE $_DBU_acces_niveau IN ('-1','1','2','3','4','5','6')");
      db_query($db_maj,"UPDATE $_DB_hist SET $_DBU_hist_niveau=(10*$_DBU_hist_niveau) WHERE $_DBU_hist_niveau IN ('-1','1','2','3','4','5','6')");
   }

   // Table droits_formations : on peut préciser les droits sur une ou plusieurs formations en particulier pour les utilisateurs
   if(!db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_DB_droits_formations'")))
   {
      db_query($db_maj, "CREATE TABLE $_DB_droits_formations (
                           $_DBU_droits_formations_acces_id bigint REFERENCES $_DB_acces($_DBU_acces_id) ON UPDATE CASCADE ON DELETE CASCADE, 
                           $_DBU_droits_formations_propspec_id bigint REFERENCES $_DB_propspec($_DBU_propspec_id) ON UPDATE CASCADE ON DELETE CASCADE, 
                           $_DBU_droits_formations_droits text,
                           CONSTRAINT droits_formations_pkey PRIMARY KEY ($_DBU_droits_formations_acces_id,$_DBU_droits_formations_propspec_id))");
   }

   // Table courriels_formations : ajout du champ "type" précisant s'il s'agit d'une formation ou d'une composante
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_courriels_propspec'
                                                                               AND column_name='$_DBU_courriels_propspec_type'")))
      db_query($db_maj, "ALTER TABLE $_DB_courriels_propspec ADD COLUMN $_DBU_courriels_propspec_type text default 'F';
                         UPDATE $_DB_courriels_propspec SET $_DBU_courriels_propspec_type='F';");

   // Suppression de la contrainte sur l'id de la formation (car peut maintenant aussi être un id de composante)
   if(db_num_rows(db_query($db_maj, "SELECT conkey FROM pg_constraint WHERE conname='courriels_formations_propspec_id_fkey'")))
     db_query($db_maj, "ALTER TABLE $_DB_courriels_propspec DROP CONSTRAINT courriels_formations_propspec_id_fkey");   

   // Table cusus_diplomes : correction du niveau de la Maitrise (bac+4 et non bac+3)
   db_query($db_maj, "UPDATE $_DB_cursus_diplomes SET $_DBU_cursus_diplomes_niveau='4' WHERE $_DBU_cursus_diplomes_intitule='Maîtrise'");

   // Idem pour la Maîtrise IUP3
   db_query($db_maj, "UPDATE $_DB_cursus_diplomes SET $_DBU_cursus_diplomes_niveau='4' WHERE $_DBU_cursus_diplomes_intitule='Maîtrise IUP 3'");

   // Ajout de la Décision "Convocable à un entretien téléphonique"
   if(!db_num_rows(db_query($db_maj,"SELECT * FROM $_DB_decisions WHERE $_DBC_decisions_id='-6'")))
      db_query($db_maj, "INSERT INTO $_DB_decisions VALUES ('-6','Convocable à un entretien téléphonique','1','1')");

   // Ajout de la Décision "Admis : attente de confirmation"
   if(!db_num_rows(db_query($db_maj,"SELECT * FROM $_DB_decisions WHERE $_DBC_decisions_id='-7'")))
      db_query($db_maj, "INSERT INTO $_DB_decisions VALUES ('-7','Admis : attente de confirmation','1','1')");

  // Ajout de la Décision "Admission confirmée"
   if(!db_num_rows(db_query($db_maj,"SELECT * FROM $_DB_decisions WHERE $_DBC_decisions_id='10'")))
      db_query($db_maj, "INSERT INTO $_DB_decisions VALUES ('10','Admission confirmée','1','1')");

   // Composantes : ajout de la colonne "avertir_decisions" : flag permettant de déterminer si un message est automatiquement envoyé aux candidats 
   // lorsqu'une décision est saisie
      
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_composantes'
                                                                               AND column_name='$_DBU_composantes_avertir_decision'")))
      db_query($db_maj, "ALTER TABLE $_DB_composantes ADD COLUMN $_DBU_composantes_avertir_decision smallint default '0';
                         UPDATE $_DB_composantes SET $_DBU_composantes_avertir_decision='0';");

   // Ajout d'un flag dans la table candidature pour voir si une notification a déjà été envoyée
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_cand'
                                                                               AND column_name='$_DBU_cand_notification_envoyee'")))
      db_query($db_maj, "ALTER TABLE $_DB_cand ADD COLUMN $_DBU_cand_notification_envoyee smallint default '0';
                         UPDATE $_DB_cand SET $_DBU_cand_notification_envoyee='0';");

   
   if(!db_num_rows(db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_DB_messages'")))
   {
      db_query($db_maj, "CREATE TABLE $_DB_messages (
                           $_DBU_messages_comp_id bigint REFERENCES $_DB_composantes($_DBU_composantes_id) ON UPDATE CASCADE ON DELETE CASCADE, 
                           $_DBU_messages_type smallint NOT NULL,
                           $_DBU_messages_statut smallint,
                           $_DBU_messages_decision_id int,
                           $_DBU_messages_contenu text,
                           $_DBU_messages_actif boolean default 'f',
                           CONSTRAINT messages_pkey PRIMARY KEY ($_DBU_messages_comp_id,$_DBU_messages_type,$_DBU_messages_statut,$_DBU_messages_decision_id))");
   }
   
   // Table Candidats : ajout du nom de naissance et du téléphone portable
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_candidat'
                                                                               AND column_name='$_DBU_candidat_nom_naissance'")))
      db_query($db_maj, "ALTER TABLE $_DB_candidat ADD COLUMN $_DBU_candidat_nom_naissance text default '';
                         UPDATE $_DB_candidat SET $_DBU_candidat_nom_naissance='';");
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_candidat'
                                                                               AND column_name='$_DBU_candidat_telephone_portable'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_candidat ADD COLUMN $_DBU_candidat_telephone_portable text default ''");
      db_query($db_maj, "UPDATE $_DB_candidat SET $_DBU_candidat_telephone_portable=''");
   }
   
   // Groupes de spécialités : ajout de l'option "ajout automatique" et du nom du groupe
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_groupes_spec'
                                                                               AND column_name='$_DBU_groupes_spec_auto'")))
      db_query($db_maj, "ALTER TABLE $_DB_groupes_spec ADD COLUMN $_DBU_groupes_spec_auto boolean default 'f';
                         UPDATE $_DB_groupes_spec SET $_DBU_groupes_spec_auto='f';");

   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_groupes_spec'
                                                                               AND column_name='$_DBU_groupes_spec_nom'")))
      db_query($db_maj, "ALTER TABLE $_DB_groupes_spec ADD COLUMN $_DBU_groupes_spec_nom text default '';
                         UPDATE $_DB_groupes_spec SET $_DBU_groupes_spec_nom='';");                            

   // Activation du formulaire d'assistance aux candidats
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_assistance'")))
   db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_assistance boolean default 'f';
                      UPDATE $_DB_systeme SET $_DBU_systeme_assistance='f';");

   // Table accès : ajout d'un champ pour l'activation de la réception des messages système (uniquement pour les admins)
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_acces'
                                                                               AND column_name='$_DBU_acces_reception_msg_systeme'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_acces ADD COLUMN $_DBU_acces_reception_msg_systeme boolean default 'f'");
      db_query($db_maj, "UPDATE $_DB_acces SET $_DBU_acces_reception_msg_systeme='f'");
   }
   
   
   // ========================================
   // Système : ajout des paramètres LDAP
   // ========================================

   // Nouvelle colonne "source" dans la table "acces" pour déterminer s'il s'agit d'un compte manuel ou LDAP
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_acces'
                                                                               AND column_name='$_DBU_acces_source'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_acces ADD COLUMN $_DBU_acces_source smallint default '0'");
      db_query($db_maj, "UPDATE $_DB_acces SET $_DBU_acces_source='0'");
   }

   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_ldap_actif'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_ldap_actif boolean default 'f'");
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_ldap_actif='f'");
   }
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_ldap_host'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_ldap_host text default ''");
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_ldap_host=''");
   }
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_ldap_port'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_ldap_port smallint default '389'");
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_ldap_port='389'");
   }
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_ldap_proto'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_ldap_proto smallint default '3'");
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_ldap_proto='3'");
   }
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_ldap_id'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_ldap_id text default ''");
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_ldap_id=''");
   }
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_ldap_pass'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_ldap_pass text default ''");
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_ldap_pass=''");
   }   
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_ldap_basedn'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_ldap_basedn text default ''");
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_ldap_basedn=''");
   }
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_ldap_attr_login'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_ldap_attr_login text default ''");
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_ldap_attr_login=''");
   }
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_ldap_attr_nom'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_ldap_attr_nom text default ''");
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_ldap_attr_nom=''");
   }
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_ldap_attr_prenom'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_ldap_attr_prenom text default ''");
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_ldap_attr_prenom=''");
   }
   
   
   
   
/*   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_ldap_attr_pass'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_ldap_attr_pass text default ''");
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_ldap_attr_pass=''");
   }
*/   

   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_ldap_attr_mail'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_ldap_attr_mail text default ''");
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_ldap_attr_mail=''");
   }
      
   // Groupes de spécialités : ajout de l'option pour les dates de sessions communes
   // TODO : pertinent ?
   
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE' 
                                                                               AND table_name='$_DB_groupes_spec'
                                                                               AND column_name='$_DBU_groupes_spec_dates_communes'")))
      db_query($db_maj, "ALTER TABLE $_DB_groupes_spec ADD COLUMN $_DBU_groupes_spec_dates_communes boolean default 'f';
                         UPDATE $_DB_groupes_spec SET $_DBU_groupes_spec_dates_communes='f';");
   
   // 25/02/2012 : table système : ajout d'un champ pour une seconde adresse mail 
   // Objectif : différencier les envois de rapports d'erreurs (techniques) et les mails envoyés au support informatique (administration)
   if(!db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                               AND table_name='$_DB_systeme'
                                                                               AND column_name='$_DBU_systeme_courriel_support'")))
   {
      db_query($db_maj, "ALTER TABLE $_DB_systeme ADD COLUMN $_DBU_systeme_courriel_support text default ''");
      
      // Par défaut, on recopie le champ "courriel_admin" de la même table
      db_query($db_maj, "UPDATE $_DB_systeme SET $_DBU_systeme_courriel_support=$_DBU_systeme_courriel_admin");
   }
      
   // 16/08/2012
   // Adresses postale : remplacement du textarea par 3 lignes adresse_1, adresse_2, adresse_3      
   if(db_num_rows(db_query($db_maj, "SELECT * FROM information_schema.columns WHERE table_catalog='$__DB_BASE'
                                                                            AND table_name='$_DB_candidat'
                                                                            AND column_name='adresse'")))
   {
      db_query($db_maj,"ALTER TABLE $_DB_candidat RENAME adresse TO adresse_1");
      db_query($db_maj,"ALTER TABLE $_DB_candidat ADD COLUMN $_DBU_candidat_adresse_2 text default ''");
      db_query($db_maj,"ALTER TABLE $_DB_candidat ADD COLUMN $_DBU_candidat_adresse_3 text default ''");
   }
         

   // 24/06/2013
   // Possibilité d'affecter des lettres différentes en fonction des groupes de formations à choix multiples
   $res_maj=db_query($db_maj,"SELECT * FROM pg_tables WHERE tablename ='$_DB_lettres_groupes'");

   if(!db_num_rows($res_maj))
      db_query($db_maj, "CREATE TABLE $_DB_lettres_groupes (
                        $_DBU_lettres_groupes_lettre_id bigint REFERENCES $_DB_lettres($_DBU_lettres_id) ON UPDATE CASCADE ON DELETE CASCADE,
                        $_DBU_lettres_groupes_groupe_id integer)");
   
      
   // Fin du bloc
   db_query($db_maj, "COMMIT TRANSACTION;");

   db_close($db_maj);
?>
