--
-- DONNEES INITIALES DE LA BASE : INFORMATIONS INDISPENSABLES ET JEU D'ESSAI
--

SET client_encoding = 'LATIN1';
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: aria; Type: DATABASE; Schema: -; Owner: globdb
--

-- CREATE DATABASE aria WITH TEMPLATE = template0 ENCODING = 'LATIN1';


-- \connect aria

SET client_encoding = 'LATIN1';
SET check_function_bodies = false;
SET client_min_messages = warning;


--
-- Données de base indispensables : une université, une composante et l'administrateur 
--

INSERT INTO universites VALUES (1, 'Université Démonstration', '[Adresse postale]', '', 'typo.css', '0,0,0');
INSERT INTO composantes VALUES (101, 'UFR Démo', 1, '[Adresse Postale]', 'Civilité Nom Prénom', 'Civilité Nom Prénom', 'Service Scolarité de l''UFR
(Adresse complète de la scolarité)', '', 'Service Scolarité de l''UFR
(Adresse complète de la scolarité)
Tél. : (33) .. .. .. .. ..
Fax. : (33) .. .. .. .. ..
adresse@domaine.fr', 'Le Président de l''Université,
Par délégation  ...
M. ...', 'UFR Démo', 172800, 32, 'adresse@domaine.fr', 0, 0, 0, 0, '', '', 'http://adresse_de_la_composante.fr/', 1, 109, 42, 60, 78, 0);

INSERT INTO acces VALUES ('0','101','Système','','administrateur','19ad89bc3e3c9d7ef68b89523eff1987','administrateur@domaine.fr','60','','f','0','0','','f','0');

-- Motifs de refus pour la première composante

INSERT INTO motifs_refus VALUES (1, 'Résultats insuffisants', NULL, 0, 101);
INSERT INTO motifs_refus VALUES (2, 'Résultats insuffisants en mathématiques', NULL, 0, 101);
INSERT INTO motifs_refus VALUES (3, 'Résultats insuffisants à l''examen du BTS', NULL, 0, 101);
INSERT INTO motifs_refus VALUES (4, 'Résultats insuffisants en 1er cycle', NULL, 0, 101);
INSERT INTO motifs_refus VALUES (5, 'Pas d''avis favorable de poursuite d''études', NULL, 0, 101);
INSERT INTO motifs_refus VALUES (6, 'Prérequis en informatique non satisfaits', NULL, 0, 101);
INSERT INTO motifs_refus VALUES (7, 'Cursus inadapté', NULL, 0, 101);
INSERT INTO motifs_refus VALUES (9, 'Nombre maximum d''inscriptions atteint', 'Le dossier n''a pu etre retenu compte tenu du nombre de dossiers soumis et de leur qualité.', 1,101);
INSERT INTO motifs_refus VALUES (10, 'Non présentation à l''entretien de sélection', '', 0, 101);
INSERT INTO motifs_refus VALUES (11, 'Pas d''offre apprentissage adaptée', 'Pas de proposition de contrat d''apprentissage en adéquation avec votre parcours', 0, 101);
INSERT INTO motifs_refus VALUES (12, 'Candidat sans contrat d''apprentissage', 'Vous ne justifiez pas de la signature d''un contrat d''apprentissage.', 0, 101);

-- Années

INSERT INTO annees VALUES (0, '', 'Année particulière', 0);
INSERT INTO annees VALUES (23, 'Probatoire Capacité', 'Probatoire Capacité', 1);
INSERT INTO annees VALUES (22, '1A Capacité', '1ère Année Capacité', 2);
INSERT INTO annees VALUES (25, '2A Capacité', '2ème Année Capacité', 3);
INSERT INTO annees VALUES (24, 'MEM Capacité', 'Mémoire - Capacité', 4);
INSERT INTO annees VALUES (26, '1A DU', '1ère Année Diplôme Universitaire', 5);
INSERT INTO annees VALUES (27, '2A DU', '2ème Année Diplôme Universitaire', 6);
INSERT INTO annees VALUES (34, '3A DU', '3ème Année Diplôme Universitaire', 7);
INSERT INTO annees VALUES (32, 'MEM DU', 'Mémoire - Diplôme Universitaire', 8);
INSERT INTO annees VALUES (35, 'Probatoire DIU', 'Probatoire Diplôme Inter-Universitaire', 9);
INSERT INTO annees VALUES (28, '1A DIU', '1ère Année Diplôme Inter-Universitaire', 10);
INSERT INTO annees VALUES (29, '2A DIU', '2ème Année Diplôme Inter-Universitaire', 11);
INSERT INTO annees VALUES (33, 'MEM DIU', 'Mémoire - Diplôme Inter-Universitaire', 12);
INSERT INTO annees VALUES (1, 'L1', 'Licence 1ère année', 13);
INSERT INTO annees VALUES (2, 'L2', 'Licence 2ème année', 14);
INSERT INTO annees VALUES (3, 'L3', 'Licence 3ème année', 15);
INSERT INTO annees VALUES (36, 'DU', 'Diplôme d''Université', 16);
INSERT INTO annees VALUES (4, 'L-Pro', 'Licence Professionnelle', 17);
INSERT INTO annees VALUES (5, 'M1', 'Master 1ère année', 18);
INSERT INTO annees VALUES (6, 'M2', 'Master 2ème année', 19);
INSERT INTO annees VALUES (30, '2A Qualification', '2ème Année Qualification', 20);
INSERT INTO annees VALUES (31, '3A Qualification', '3ème Année Qualification', 21);
INSERT INTO annees VALUES (7, '1A DES', '1ère Année Diplôme d''Etudes Spécialisées', 22);
INSERT INTO annees VALUES (8, '2A DES', '2ème Année Diplôme d''Etudes Spécialisées', 23);
INSERT INTO annees VALUES (9, '3A DES', '3ème Année Diplôme d''Etudes Spécialisées', 24);
INSERT INTO annees VALUES (10, '4A DES', '4ème Année Diplôme d''Etudes Spécialisées', 25);
INSERT INTO annees VALUES (11, '5A DES', '5ème Année Diplôme d''Etudes Spécialisées', 26);
INSERT INTO annees VALUES (12, 'MEM DES', 'Mémoire - Diplôme d''Etudes Spécialisées', 27);
INSERT INTO annees VALUES (13, 'Thèse', 'Thèse', 28);
INSERT INTO annees VALUES (14, '1A DESC', '1ère Année Diplôme d''Etudes Spécialisées Complémentaire', 29);
INSERT INTO annees VALUES (15, '2A DESC', '2ème Année Diplôme d''Etudes Spécialisées Complémentaire', 30);
INSERT INTO annees VALUES (17, '3A DESC', '3ème Année Diplôme d''Etudes Spécialisées Complémentaire', 31);
INSERT INTO annees VALUES (16, 'MEM DESC', 'Mémoire - Diplôme d''Etudes Spécialisées Complémentaire', 32);
INSERT INTO annees VALUES (18, '1A DESCQ', '1ère Année Diplôme d''Etudes Spécialisées Complémentaire Qualifiant', 33);
INSERT INTO annees VALUES (19, '2A DESCQ', '2ème Année Diplôme d''Etudes Spécialisées Complémentaire Qualifiant', 34);
INSERT INTO annees VALUES (20, '3A DESCQ', '3ème Année Diplôme d''Etudes Spécialisées Complémentaire Qualifiant', 35);
INSERT INTO annees VALUES (21, 'MEM DESCQ', 'Mémoire - Diplôme d''Etudes Spécialisées Complémentaire Qualifiant', 36);
INSERT INTO annees VALUES (37, 'Doctorat', 'Doctorat', 37);



-- NOUVELLE TABLE
insert into pays_nationalites_iso_insee values ('00','995','','Apatride');
insert into pays_nationalites_iso_insee values ('AA','990','AUTRE','Autre');
insert into pays_nationalites_iso_insee values ('AF','212','AFGHANISTAN','Afghane');
insert into pays_nationalites_iso_insee values ('ZA','303','AFRIQUE DU SUD','Sud-africaine');
insert into pays_nationalites_iso_insee values ('AX','','ÎLES ÅLAND','');
insert into pays_nationalites_iso_insee values ('AL','125','ALBANIE','Albanaise');
insert into pays_nationalites_iso_insee values ('DZ','352','ALGÉRIE','Algérienne');
insert into pays_nationalites_iso_insee values ('DE','109','ALLEMAGNE','Allemande');
insert into pays_nationalites_iso_insee values ('AD','130','ANDORRE','Andorrane');
insert into pays_nationalites_iso_insee values ('AO','395','ANGOLA','Angolaise');
insert into pays_nationalites_iso_insee values ('AI','425','ANGUILLA','');
insert into pays_nationalites_iso_insee values ('AQ','','ANTARCTIQUE','');
insert into pays_nationalites_iso_insee values ('AG','441','ANTIGUA-ET-BARBUDA','');
insert into pays_nationalites_iso_insee values ('AN','431','ANTILLES NÉERLANDAISES','');
insert into pays_nationalites_iso_insee values ('SA','201','ARABIE SAOUDITE','Saoudienne');
insert into pays_nationalites_iso_insee values ('AR','415','ARGENTINE','Argentine');
insert into pays_nationalites_iso_insee values ('AM','252','ARMÉNIE','Arménienne');
insert into pays_nationalites_iso_insee values ('AW','431','ARUBA','');
insert into pays_nationalites_iso_insee values ('AU','501','AUSTRALIE','Australienne');
insert into pays_nationalites_iso_insee values ('AT','110','AUTRICHE','Autrichienne');
insert into pays_nationalites_iso_insee values ('AZ','253','AZERBAÏDJAN','Azerbaïdjanaise');
insert into pays_nationalites_iso_insee values ('BS','436','BAHAMAS','');
insert into pays_nationalites_iso_insee values ('BH','249','BAHREÏN','Bahreïnienne');
insert into pays_nationalites_iso_insee values ('BD','246','BANGLADESH','Bangladaise');
insert into pays_nationalites_iso_insee values ('BB','434','BARBADE','Barbadienne');
insert into pays_nationalites_iso_insee values ('BY','148','BÉLARUS','Bélarussienne');
insert into pays_nationalites_iso_insee values ('BE','131','BELGIQUE','Belge');
insert into pays_nationalites_iso_insee values ('BZ','429','BELIZE','Bélizienne');
insert into pays_nationalites_iso_insee values ('BJ','327','BÉNIN','Béninoise');
insert into pays_nationalites_iso_insee values ('BM','425','BERMUDES','');
insert into pays_nationalites_iso_insee values ('BT','214','BHOUTAN','Bhoutanaise');
insert into pays_nationalites_iso_insee values ('BO','418','BOLIVIE','Bolivienne');
insert into pays_nationalites_iso_insee values ('BA','118','BOSNIE-HERZÉGOVINE','Bosniaque');
insert into pays_nationalites_iso_insee values ('BW','347','BOTSWANA','Botswanaise');
insert into pays_nationalites_iso_insee values ('BV','103','ÎLE BOUVET','');
insert into pays_nationalites_iso_insee values ('BR','416','BRÉSIL','Brésilienne');
insert into pays_nationalites_iso_insee values ('BN','225','BRUNÉI DARUSSALAM','');
insert into pays_nationalites_iso_insee values ('BG','111','BULGARIE','Bulgare');
insert into pays_nationalites_iso_insee values ('BF','331','BURKINA FASO','Burkinabè');
insert into pays_nationalites_iso_insee values ('BI','321','BURUNDI','Burundaise');
insert into pays_nationalites_iso_insee values ('KY','425','ÎLES CAÏMANES','');
insert into pays_nationalites_iso_insee values ('KH','234','CAMBODGE','Cambodgienne');
insert into pays_nationalites_iso_insee values ('CM','322','CAMEROUN','Camerounaise');
insert into pays_nationalites_iso_insee values ('CA','401','CANADA','Canadienne');
insert into pays_nationalites_iso_insee values ('CV','396','CAP-VERT','Cap-verdienne');
insert into pays_nationalites_iso_insee values ('CF','323','RÉPUBLIQUE CENTRAFRICAINE','Centrafricaine');
insert into pays_nationalites_iso_insee values ('CL','417','CHILI','Chilienne');
insert into pays_nationalites_iso_insee values ('CN','216','CHINE','Chinoise');
insert into pays_nationalites_iso_insee values ('CX','501','ÎLE CHRISTMAS','');
insert into pays_nationalites_iso_insee values ('CY','254','CHYPRE','Chypriote');
insert into pays_nationalites_iso_insee values ('CC','501','ÎLES COCOS (KEELING)','');
insert into pays_nationalites_iso_insee values ('CO','419','COLOMBIE','Colombienne');
insert into pays_nationalites_iso_insee values ('KM','397','COMORES','Comorienne');
insert into pays_nationalites_iso_insee values ('CG','324','CONGO','Congolaise');
insert into pays_nationalites_iso_insee values ('CD','312','CONGO (RÉPUBLIQUE DÉMOCRATIQUE)','Congolaise');
insert into pays_nationalites_iso_insee values ('CK','502','ÎLES COOK','');
insert into pays_nationalites_iso_insee values ('KR','239','CORÉE DU SUD','Sud-coréenne');
insert into pays_nationalites_iso_insee values ('KP','238','CORÉE DU NORD','Nord-coréenne');
insert into pays_nationalites_iso_insee values ('CR','406','COSTA RICA','Costaricienne');
insert into pays_nationalites_iso_insee values ('CI','326','CÔTE D''IVOIRE','Ivoirienne');
insert into pays_nationalites_iso_insee values ('HR','119','CROATIE','Croate');
insert into pays_nationalites_iso_insee values ('CU','407','CUBA','Cubaine');
insert into pays_nationalites_iso_insee values ('DK','101','DANEMARK','Danoise');
insert into pays_nationalites_iso_insee values ('DJ','399','DJIBOUTI','Djiboutienne');
insert into pays_nationalites_iso_insee values ('DO','438','RÉPUBLIQUE DOMINICAINE','Dominicaine');
insert into pays_nationalites_iso_insee values ('DM','408','DOMINIQUE','Dominiquaise');
insert into pays_nationalites_iso_insee values ('EG','301','ÉGYPTE','Egyptienne');
insert into pays_nationalites_iso_insee values ('SV','414','EL SALVADOR','Salvadorienne');
insert into pays_nationalites_iso_insee values ('AE','247','ÉMIRATS ARABES UNIS','Emirats Arabes Unis');
insert into pays_nationalites_iso_insee values ('EC','420','ÉQUATEUR','Equatorienne');
insert into pays_nationalites_iso_insee values ('ER','317','ÉRYTHRÉE','Erythréenne');
insert into pays_nationalites_iso_insee values ('ES','134','ESPAGNE','Espagnole');
insert into pays_nationalites_iso_insee values ('EE','106','ESTONIE','Estonienne');
insert into pays_nationalites_iso_insee values ('US','404','ÉTATS-UNIS','Américaine');
insert into pays_nationalites_iso_insee values ('ET','315','ÉTHIOPIE','Ethiopienne');
insert into pays_nationalites_iso_insee values ('FK','427','ÎLES FALKLAND (MALOUINES)','');
insert into pays_nationalites_iso_insee values ('FO','101','ÎLES FÉROÉ','');
insert into pays_nationalites_iso_insee values ('FJ','508','FIDJI','');
insert into pays_nationalites_iso_insee values ('FI','105','FINLANDE','Finlandaise');
insert into pays_nationalites_iso_insee values ('FR','100','FRANCE','Française');
insert into pays_nationalites_iso_insee values ('GA','328','GABON','Gabonaise');
insert into pays_nationalites_iso_insee values ('GM','304','GAMBIE','Gambienne');
insert into pays_nationalites_iso_insee values ('GE','255','GÉORGIE','Géorgienne');
insert into pays_nationalites_iso_insee values ('GS','427','GÉORGIE DU SUD ET LES ÎLES SANDWICH DU SUD','');
insert into pays_nationalites_iso_insee values ('GH','329','GHANA','Ghanéenne');
insert into pays_nationalites_iso_insee values ('GI','133','GIBRALTAR','');
insert into pays_nationalites_iso_insee values ('GR','126','GRÈCE','Grecque');
insert into pays_nationalites_iso_insee values ('GD','435','GRENADE','');
insert into pays_nationalites_iso_insee values ('GL','430','GROENLAND','');
insert into pays_nationalites_iso_insee values ('GP','100','GUADELOUPE','');
insert into pays_nationalites_iso_insee values ('GU','505','GUAM','');
insert into pays_nationalites_iso_insee values ('GT','409','GUATEMALA','Guatémaltèque');
insert into pays_nationalites_iso_insee values ('GG','132','GUERNESEY','');
insert into pays_nationalites_iso_insee values ('GN','330','GUINÉE','Guinéenne');
insert into pays_nationalites_iso_insee values ('GW','392','GUINÉE-BISSAU','Bissau-guinéenne');
insert into pays_nationalites_iso_insee values ('GQ','314','GUINÉE ÉQUATORIALE','Guinéo-équatorienne');
insert into pays_nationalites_iso_insee values ('GY','428','GUYANA','Guyanienne');
insert into pays_nationalites_iso_insee values ('GF','100','GUYANE FRANÇAISE','');
insert into pays_nationalites_iso_insee values ('HT','410','HAÏTI','Haïtienne');
insert into pays_nationalites_iso_insee values ('HM','501','ÎLE HEARD et ÎLES MCDONALD','');
insert into pays_nationalites_iso_insee values ('HN','411','HONDURAS','Hondurienne');
insert into pays_nationalites_iso_insee values ('HK','230','HONG-KONG','');
insert into pays_nationalites_iso_insee values ('HU','112','HONGRIE','Hongroise');
insert into pays_nationalites_iso_insee values ('IM','132','ÎLE DE MAN','');
insert into pays_nationalites_iso_insee values ('UM','','ÎLES MINEURES ÉLOIGNÉES DES ÉTATS-UNIS','');
insert into pays_nationalites_iso_insee values ('VG','425','ÎLES VIERGES BRITANNIQUES','');
insert into pays_nationalites_iso_insee values ('VI','432','ÎLES VIERGES DES ÉTATS-UNIS','');
insert into pays_nationalites_iso_insee values ('IN','223','INDE','Indienne');
insert into pays_nationalites_iso_insee values ('ID','231','INDONÉSIE','Indonésienne');
insert into pays_nationalites_iso_insee values ('IR','204','IRAN','Iranienne');
insert into pays_nationalites_iso_insee values ('IQ','203','IRAQ','Iraquienne');
insert into pays_nationalites_iso_insee values ('IE','136','IRLANDE','Irlandaise');
insert into pays_nationalites_iso_insee values ('IS','102','ISLANDE','Islandaise');
insert into pays_nationalites_iso_insee values ('IL','207','ISRAËL','Israëlienne');
insert into pays_nationalites_iso_insee values ('IT','127','ITALIE','Italienne');
insert into pays_nationalites_iso_insee values ('JM','426','JAMAÏQUE','Jamaïquaine');
insert into pays_nationalites_iso_insee values ('JP','217','JAPON','Japonaise');
insert into pays_nationalites_iso_insee values ('JE','132','JERSEY','');
insert into pays_nationalites_iso_insee values ('JO','222','JORDANIE','Jordanienne');
insert into pays_nationalites_iso_insee values ('KZ','256','KAZAKHSTAN','Kazakhe');
insert into pays_nationalites_iso_insee values ('KE','332','KENYA','Kenyane');
insert into pays_nationalites_iso_insee values ('KG','257','KIRGHIZISTAN','Kirghize');
insert into pays_nationalites_iso_insee values ('KI','513','KIRIBATI','');
insert into pays_nationalites_iso_insee values ('KW','240','KOWEÏT','Koweïtienne');
insert into pays_nationalites_iso_insee values ('LA','241','LAOS','Laotienne');
insert into pays_nationalites_iso_insee values ('LS','348','LESOTHO','Lesothane');
insert into pays_nationalites_iso_insee values ('LV','107','LETTONIE','Lettone');
insert into pays_nationalites_iso_insee values ('LB','205','LIBAN','Libanaise');
insert into pays_nationalites_iso_insee values ('LR','302','LIBÉRIA','Libérienne');
insert into pays_nationalites_iso_insee values ('LY','316','LIBYE','Libyenne');
insert into pays_nationalites_iso_insee values ('LI','113','LIECHTENSTEIN','');
insert into pays_nationalites_iso_insee values ('LT','108','LITUANIE','Lituanienne');
insert into pays_nationalites_iso_insee values ('LU','137','LUXEMBOURG','Luxembourgeoise');
insert into pays_nationalites_iso_insee values ('MO','232','MACAO','');
insert into pays_nationalites_iso_insee values ('MK','156','MACÉDOINE','Macédonienne');
insert into pays_nationalites_iso_insee values ('MG','333','MADAGASCAR','Malgache');
insert into pays_nationalites_iso_insee values ('MY','227','MALAISIE','Malaisienne');
insert into pays_nationalites_iso_insee values ('MW','334','MALAWI','Malawienne');
insert into pays_nationalites_iso_insee values ('MV','229','MALDIVES','');
insert into pays_nationalites_iso_insee values ('ML','335','MALI','Malienne');
insert into pays_nationalites_iso_insee values ('MT','144','MALTE','Maltaise');
insert into pays_nationalites_iso_insee values ('MP','505','ÎLES MARIANNES DU NORD','');
insert into pays_nationalites_iso_insee values ('MA','350','MAROC','Marocaine');
insert into pays_nationalites_iso_insee values ('MH','515','ÎLES MARSHALL','');
insert into pays_nationalites_iso_insee values ('MQ','100','MARTINIQUE','');
insert into pays_nationalites_iso_insee values ('MU','390','ÎLE MAURICE','Mauricienne');
insert into pays_nationalites_iso_insee values ('MR','336','MAURITANIE','Mauritanienne');
insert into pays_nationalites_iso_insee values ('YT','100','MAYOTTE','');
insert into pays_nationalites_iso_insee values ('MX','405','MEXIQUE','Mexicaine');
insert into pays_nationalites_iso_insee values ('FM','516','MICRONÉSIE (ÉTATS FÉDÉRÉS)','Micronésienne');
insert into pays_nationalites_iso_insee values ('MD','151','MOLDOVA','Moldove');
insert into pays_nationalites_iso_insee values ('MC','138','MONACO','Monégasque');
insert into pays_nationalites_iso_insee values ('MN','242','MONGOLIE','Mongole');
insert into pays_nationalites_iso_insee values ('ME','120','MONTÉNÉGRO','Monténégrine');
insert into pays_nationalites_iso_insee values ('MS','425','MONTSERRAT','');
insert into pays_nationalites_iso_insee values ('MZ','393','MOZAMBIQUE','Mozambique');
insert into pays_nationalites_iso_insee values ('MM','','MYANMAR','');
insert into pays_nationalites_iso_insee values ('NA','311','NAMIBIE','Namibienne');
insert into pays_nationalites_iso_insee values ('NR','507','NAURU','Nauruane');
insert into pays_nationalites_iso_insee values ('NP','215','NÉPAL','Népalaise');
insert into pays_nationalites_iso_insee values ('NI','412','NICARAGUA','Nicaraguayenne');
insert into pays_nationalites_iso_insee values ('NE','337','NIGER','Nigerienne');
insert into pays_nationalites_iso_insee values ('NG','338','NIGÉRIA','Nigériane');
insert into pays_nationalites_iso_insee values ('NU','502','NIUÉ','');
insert into pays_nationalites_iso_insee values ('NF','501','ÎLE NORFOLK','');
insert into pays_nationalites_iso_insee values ('NO','103','NORVÈGE','Norvégienne');
insert into pays_nationalites_iso_insee values ('NC','100','NOUVELLE-CALÉDONIE','');
insert into pays_nationalites_iso_insee values ('NZ','502','NOUVELLE-ZÉLANDE','Néo-zélandaise');
insert into pays_nationalites_iso_insee values ('IO','308','OCÉAN INDIEN (TERRITOIRE BRITANNIQUE)','');
insert into pays_nationalites_iso_insee values ('OM','250','OMAN','Omanaise');
insert into pays_nationalites_iso_insee values ('UG','339','OUGANDA','Ougandaise');
insert into pays_nationalites_iso_insee values ('UZ','258','OUZBÉKISTAN','Ouzbèke');
insert into pays_nationalites_iso_insee values ('PK','213','PAKISTAN','Pakistanaise');
insert into pays_nationalites_iso_insee values ('PW','517','PALAOS (ÎLES)','');
insert into pays_nationalites_iso_insee values ('PS','261','PALESTINE','Palestinienne');
insert into pays_nationalites_iso_insee values ('PA','413','PANAMA','Panaméenne');
insert into pays_nationalites_iso_insee values ('PG','510','PAPOUASIE-NOUVELLE-GUINÉE','');
insert into pays_nationalites_iso_insee values ('PY','421','PARAGUAY','Paraguayenne');
insert into pays_nationalites_iso_insee values ('NL','135','PAYS-BAS','Néerlandaise');
insert into pays_nationalites_iso_insee values ('PE','422','PÉROU','Péruvienne');
insert into pays_nationalites_iso_insee values ('PH','220','PHILIPPINES','Philippine');
insert into pays_nationalites_iso_insee values ('PN','503','PITCAIRN','');
insert into pays_nationalites_iso_insee values ('PL','122','POLOGNE','Polonaise');
insert into pays_nationalites_iso_insee values ('PF','100','POLYNÉSIE FRANÇAISE','');
insert into pays_nationalites_iso_insee values ('PR','432','PUERTO RICO','Portoricaine');
insert into pays_nationalites_iso_insee values ('PT','139','PORTUGAL','Portugaise');
insert into pays_nationalites_iso_insee values ('QA','248','QATAR','Qatarienne');
insert into pays_nationalites_iso_insee values ('RE','100','RÉUNION','');
insert into pays_nationalites_iso_insee values ('RO','114','ROUMANIE','Roumaine');
insert into pays_nationalites_iso_insee values ('GB','132','ROYAUME-UNI','Britannique');
insert into pays_nationalites_iso_insee values ('RU','123','RUSSIE','Russe');
insert into pays_nationalites_iso_insee values ('RW','340','RWANDA','Rwandaise');
insert into pays_nationalites_iso_insee values ('EH','389','SAHARA OCCIDENTAL','');
insert into pays_nationalites_iso_insee values ('BL','100','SAINT-BARTHÉLEMY','');
insert into pays_nationalites_iso_insee values ('SH','306','SAINTE-HÉLÈNE','');
insert into pays_nationalites_iso_insee values ('LC','439','SAINTE-LUCIE','');
insert into pays_nationalites_iso_insee values ('KN','','SAINT-KITTS-ET-NEVIS','');
insert into pays_nationalites_iso_insee values ('SM','128','SAINT-MARIN','');
insert into pays_nationalites_iso_insee values ('MF','','SAINT-MARTIN','');
insert into pays_nationalites_iso_insee values ('PM','100','SAINT-PIERRE-ET-MIQUELON','');
insert into pays_nationalites_iso_insee values ('VA','129','SAINT-SIÈGE (ÉTAT DE LA CITÉ DU VATICAN)','');
insert into pays_nationalites_iso_insee values ('VC','440','SAINT-VINCENT-ET-LES GRENADINES','');
insert into pays_nationalites_iso_insee values ('SB','512','ÎLES SALOMON','');
insert into pays_nationalites_iso_insee values ('WS','506','SAMOA','');
insert into pays_nationalites_iso_insee values ('AS','505','SAMOA AMÉRICAINES','');
insert into pays_nationalites_iso_insee values ('ST','394','SAO TOMÉ-ET-PRINCIPE','Santoméenne');
insert into pays_nationalites_iso_insee values ('SN','341','SÉNÉGAL','Sénégalaise');
insert into pays_nationalites_iso_insee values ('RS','121','SERBIE','Serbe');
insert into pays_nationalites_iso_insee values ('SC','398','SEYCHELLES','');
insert into pays_nationalites_iso_insee values ('SL','342','SIERRA LEONE','Sierra-léonaise');
insert into pays_nationalites_iso_insee values ('SG','226','SINGAPOUR','Singapourienne');
insert into pays_nationalites_iso_insee values ('SK','117','SLOVAQUIE','Slovaque');
insert into pays_nationalites_iso_insee values ('SI','145','SLOVÉNIE','Slovène');
insert into pays_nationalites_iso_insee values ('SO','318','SOMALIE','Somalienne');
insert into pays_nationalites_iso_insee values ('SD','343','SOUDAN','Soudanaise');
insert into pays_nationalites_iso_insee values ('LK','235','SRI LANKA','Sri-lankaise');
insert into pays_nationalites_iso_insee values ('SE','104','SUÈDE','Suédoise');
insert into pays_nationalites_iso_insee values ('CH','140','SUISSE','Suisse');
insert into pays_nationalites_iso_insee values ('SR','437','SURINAME','Surinamaise');
insert into pays_nationalites_iso_insee values ('SJ','103','SVALBARD ET ÎLE JAN MAYEN','');
insert into pays_nationalites_iso_insee values ('SZ','391','SWAZILAND','Swazie');
insert into pays_nationalites_iso_insee values ('SY','206','SYRIE','Syrienne');
insert into pays_nationalites_iso_insee values ('TJ','259','TADJIKISTAN','Tadjike');
insert into pays_nationalites_iso_insee values ('TW','236','TAÏWAN','Taïwanaise');
insert into pays_nationalites_iso_insee values ('TZ','309','TANZANIE','Tanzanienne');
insert into pays_nationalites_iso_insee values ('TD','344','TCHAD','Tchadienne');
insert into pays_nationalites_iso_insee values ('CZ','116','TCHÈQUE (RÉPUBLIQUE)','Tchèque');
insert into pays_nationalites_iso_insee values ('TF','100','TERRES AUSTRALES FRANÇAISES','');
insert into pays_nationalites_iso_insee values ('TH','219','THAÏLANDE','Thaïlandaise');
insert into pays_nationalites_iso_insee values ('TL','','TIMOR-LESTE','');
insert into pays_nationalites_iso_insee values ('TG','345','TOGO','Togolaise');
insert into pays_nationalites_iso_insee values ('TK','502','TOKELAU','');
insert into pays_nationalites_iso_insee values ('TO','509','TONGA','');
insert into pays_nationalites_iso_insee values ('TT','433','TRINITÉ-ET-TOBAGO','Trinidadienne');
insert into pays_nationalites_iso_insee values ('TN','351','TUNISIE','Tunisienne');
insert into pays_nationalites_iso_insee values ('TM','260','TURKMÉNISTAN','Turkmène');
insert into pays_nationalites_iso_insee values ('TC','425','ÎLES TURKS ET CAÏQUES','');
insert into pays_nationalites_iso_insee values ('TR','208','TURQUIE','Turque');
insert into pays_nationalites_iso_insee values ('TV','511','TUVALU','');
insert into pays_nationalites_iso_insee values ('UA','155','UKRAINE','Ukrainienne');
insert into pays_nationalites_iso_insee values ('UY','423','URUGUAY','Urugayenne');
insert into pays_nationalites_iso_insee values ('VU','514','VANUATU','Vanuatuane');
insert into pays_nationalites_iso_insee values ('VE','424','VENEZUELA','Vénézuélienne');
insert into pays_nationalites_iso_insee values ('VN','243','VIET NAM','Vietnamienne');
insert into pays_nationalites_iso_insee values ('WF','100','WALLIS ET FUTUNA','');
insert into pays_nationalites_iso_insee values ('YE','251','YÉMEN','Yéménite');
insert into pays_nationalites_iso_insee values ('ZM','346','ZAMBIE','Zambienne');
insert into pays_nationalites_iso_insee values ('ZW','310','ZIMBABWE','Zimbabwéenne');


--
-- Table diplomes_bac
-- 

INSERT INTO diplomes_bac VALUES ('C','C-mathématiques et sciences physiques');
INSERT INTO diplomes_bac VALUES ('D','D-mathématiques et sciences de la nature');
INSERT INTO diplomes_bac VALUES ('A','A-philosophie-lettres X');
INSERT INTO diplomes_bac VALUES ('B','B-économique et social');
INSERT INTO diplomes_bac VALUES ('A1','A1-lettres-sciences');
INSERT INTO diplomes_bac VALUES ('A2','A2-lettres-langues');
INSERT INTO diplomes_bac VALUES ('A3','A3-lettres-arts plastiques');
INSERT INTO diplomes_bac VALUES ('DP','D''-sciences agronomiques et techniques');
INSERT INTO diplomes_bac VALUES ('E','E-Mathématiques et techniques');
INSERT INTO diplomes_bac VALUES ('0001','0001-bac international');
INSERT INTO diplomes_bac VALUES ('F1','F1-construction mécanique');
INSERT INTO diplomes_bac VALUES ('F2','F2-électronique');
INSERT INTO diplomes_bac VALUES ('F3','F3-électrotechnique');
INSERT INTO diplomes_bac VALUES ('F4','F4-génie civil');
INSERT INTO diplomes_bac VALUES ('F5','F5-physique');
INSERT INTO diplomes_bac VALUES ('F6','F6-chimie');
INSERT INTO diplomes_bac VALUES ('F7','F7-biologie option biochimie');
INSERT INTO diplomes_bac VALUES ('F8','F8-sciences médico-sociales');
INSERT INTO diplomes_bac VALUES ('F9','F9-équipement technique-bâtiment');
INSERT INTO diplomes_bac VALUES ('F10','F10-microtechnique (avant 1984)');
INSERT INTO diplomes_bac VALUES ('F10A','F10A-microtechnique option appareillage');
INSERT INTO diplomes_bac VALUES ('F10B','F10B-microtechnique option optique');
INSERT INTO diplomes_bac VALUES ('F11','F11-musique');
INSERT INTO diplomes_bac VALUES ('F11P','F11P-danse');
INSERT INTO diplomes_bac VALUES ('F12','F12-arts appliqués');
INSERT INTO diplomes_bac VALUES ('F','F-spécialité non précisée');
INSERT INTO diplomes_bac VALUES ('G1','G1-techniques administratives');
INSERT INTO diplomes_bac VALUES ('G2','G2-techniques quantitatives de gestion');
INSERT INTO diplomes_bac VALUES ('G3','G3-techniques commerciales');
INSERT INTO diplomes_bac VALUES ('G','G-spécialité non précisée');
INSERT INTO diplomes_bac VALUES ('H','H-techniques informatiques');
INSERT INTO diplomes_bac VALUES ('0021','0021-bacs professionnels industriels');
INSERT INTO diplomes_bac VALUES ('0022','0022-bacs professionnels tertiaires');
INSERT INTO diplomes_bac VALUES ('0031','0031-titre étranger admis en équivalence');
INSERT INTO diplomes_bac VALUES ('0032','0032-titre français admis en dispense');
INSERT INTO diplomes_bac VALUES ('0033','0033-ESEU A');
INSERT INTO diplomes_bac VALUES ('0034','0034-ESEU B');
INSERT INTO diplomes_bac VALUES ('0035','0035-promotion sociale');
INSERT INTO diplomes_bac VALUES ('0036','0036-validation études expériences prof.');
INSERT INTO diplomes_bac VALUES ('0037','0037-autres cas de non bacheliers');
INSERT INTO diplomes_bac VALUES ('0000','0000-sans bac');
INSERT INTO diplomes_bac VALUES ('L','L-Littérature');
INSERT INTO diplomes_bac VALUES ('S','S-Scientifique');
INSERT INTO diplomes_bac VALUES ('ES','ES-Economique et social');
INSERT INTO diplomes_bac VALUES ('STT','STT-Sciences et technologies tertiaires');
INSERT INTO diplomes_bac VALUES ('STI','STI-Sciences et techniques industrielles');
INSERT INTO diplomes_bac VALUES ('STL','STL-Sciences et techno. de laboratoire');
INSERT INTO diplomes_bac VALUES ('SMS','SMS-Sciences Médico-Sociales');
INSERT INTO diplomes_bac VALUES ('DAEA','Dip. d''accès aux études universitaires A');
INSERT INTO diplomes_bac VALUES ('DAEB','Dip. d''accès aux études universitaires B');
INSERT INTO diplomes_bac VALUES ('STPA','STPA-Sciences et techno. prod agro-alim.');
INSERT INTO diplomes_bac VALUES ('STAE','STAE-Sciences et techno. agronomie-env.');
INSERT INTO diplomes_bac VALUES ('F7P','F7P-biologie option biologie');
INSERT INTO diplomes_bac VALUES ('HOT','HOT-Hôtellerie');
INSERT INTO diplomes_bac VALUES ('0030','0030-capacité de droit');
INSERT INTO diplomes_bac VALUES ('0023','0023-bacs professionnels agricoles');
INSERT INTO diplomes_bac VALUES ('A4','A4 -langues mathématiques (avant 1984)');
INSERT INTO diplomes_bac VALUES ('A5','A5-Langues (avant 1984)');
INSERT INTO diplomes_bac VALUES ('STG','STG-Sciences et technologies de gestion');
INSERT INTO diplomes_bac VALUES ('T4','STT-Informatique');
INSERT INTO diplomes_bac VALUES ('B1','STL-Biochimie');
INSERT INTO diplomes_bac VALUES ('B2','STL-Chimie');
INSERT INTO diplomes_bac VALUES ('B3','STL-Physique');
INSERT INTO diplomes_bac VALUES ('ES1','ES1 sciences éco. et sociales');
INSERT INTO diplomes_bac VALUES ('ES3','ES3 Eco soc - langue vivante renforcée');
INSERT INTO diplomes_bac VALUES ('ES4','ES4 Eco soc - langue vivante 3');
INSERT INTO diplomes_bac VALUES ('I1','STI-Genie mecanique');
INSERT INTO diplomes_bac VALUES ('I2','STI-Genie civil');
INSERT INTO diplomes_bac VALUES ('I3','STI-Genie energie');
INSERT INTO diplomes_bac VALUES ('I6','STI-Genie materiaux');
INSERT INTO diplomes_bac VALUES ('L1','L1-littérature - langue vivante 3');
INSERT INTO diplomes_bac VALUES ('L2','L2-littérature -langue vivante renforcée');
INSERT INTO diplomes_bac VALUES ('L3','L3-littérature - langue régionale');
INSERT INTO diplomes_bac VALUES ('L6','L6-littérature - grec ancien');
INSERT INTO diplomes_bac VALUES ('S1','S1-Scientifique-Vie et Terre-Math');
INSERT INTO diplomes_bac VALUES ('S3','S3-Scientifique-Sciences Vie et Terre');
INSERT INTO diplomes_bac VALUES ('STIA','STI-Sc.Tecno.Ind.arts appliqués');
INSERT INTO diplomes_bac VALUES ('T1','STT-Action commerciale');
INSERT INTO diplomes_bac VALUES ('T2','STT-Action administrative');
INSERT INTO diplomes_bac VALUES ('T3','STT-Comptabilite');
INSERT INTO diplomes_bac VALUES ('ES2','ES2 Eco soc - math appliquées');
INSERT INTO diplomes_bac VALUES ('I4','STI-Genie electronique');
INSERT INTO diplomes_bac VALUES ('I5','STI-Genie electrotechnique');
INSERT INTO diplomes_bac VALUES ('L4','L4-littérature - mathématique');
INSERT INTO diplomes_bac VALUES ('L5','L5-littérature - latin');
INSERT INTO diplomes_bac VALUES ('L7','L7-littérature - arts');
INSERT INTO diplomes_bac VALUES ('S2','S2-Scientifique-V&T-Physique Chimie');
INSERT INTO diplomes_bac VALUES ('S4','S4-Scientifique-V&T-Techno.Industrielle');
INSERT INTO diplomes_bac VALUES ('S5','S5-Scientifique-V&T-Biologie écologie');
INSERT INTO diplomes_bac VALUES ('SCI','Sciences de l''Ingénieur');
INSERT INTO diplomes_bac VALUES ('ST2S','ST2S-Sciences et techno. Santé et Social');
INSERT INTO diplomes_bac VALUES ('STAV','STAV-Sciences et techno. Agronom.Vivant');

--
-- Data for Name: cursus_diplomes; Type: TABLE DATA; Schema: public; Owner: globdb
--

INSERT INTO cursus_diplomes VALUES (3, 'CPGE 1ère année', 1);
INSERT INTO cursus_diplomes VALUES (43, 'CPGE 2ème année', 2);
INSERT INTO cursus_diplomes VALUES (44, 'DU', 3);
INSERT INTO cursus_diplomes VALUES (1, 'Baccalauréat', 0);
INSERT INTO cursus_diplomes VALUES (33, 'Bachelor', 0);
INSERT INTO cursus_diplomes VALUES (36, 'Année spéciale DUT', 2);
INSERT INTO cursus_diplomes VALUES (2, 'BTS', 2);
INSERT INTO cursus_diplomes VALUES (22, 'DEA', 5);
INSERT INTO cursus_diplomes VALUES (23, 'DESS', 5);
INSERT INTO cursus_diplomes VALUES (6, 'Deug 1', 1);
INSERT INTO cursus_diplomes VALUES (7, 'Deug 2', 2);
INSERT INTO cursus_diplomes VALUES (11, 'Deug IUP 1', 2);
INSERT INTO cursus_diplomes VALUES (25, 'Diplôme Bac+1', 1);
INSERT INTO cursus_diplomes VALUES (26, 'Diplôme Bac+2', 2);
INSERT INTO cursus_diplomes VALUES (27, 'Diplôme Bac+3', 3);
INSERT INTO cursus_diplomes VALUES (28, 'Diplôme Bac+4', 4);
INSERT INTO cursus_diplomes VALUES (29, 'Diplôme Bac+5', 5);
INSERT INTO cursus_diplomes VALUES (30, 'Diplôme Bac+6', 6);
INSERT INTO cursus_diplomes VALUES (40, 'Diplôme Bac+7', 7);
INSERT INTO cursus_diplomes VALUES (24, 'Diplôme d''ingénieur', 5);
INSERT INTO cursus_diplomes VALUES (10, 'DUT', 2);
INSERT INTO cursus_diplomes VALUES (14, 'L1', 1);
INSERT INTO cursus_diplomes VALUES (15, 'L2', 2);
INSERT INTO cursus_diplomes VALUES (16, 'L3', 3);
INSERT INTO cursus_diplomes VALUES (17, 'Licence', 3);
INSERT INTO cursus_diplomes VALUES (12, 'Licence IUP 2', 3);
INSERT INTO cursus_diplomes VALUES (18, 'M1', 4);
INSERT INTO cursus_diplomes VALUES (19, 'M2', 5);
INSERT INTO cursus_diplomes VALUES (21, 'Master of Science', 4);
INSERT INTO cursus_diplomes VALUES (20, 'Maîtrise', 4);
INSERT INTO cursus_diplomes VALUES (13, 'Maîtrise IUP 3', 4);
INSERT INTO cursus_diplomes VALUES (4, 'CUES', 2);
INSERT INTO cursus_diplomes VALUES (8, 'DEUA', 3);
INSERT INTO cursus_diplomes VALUES (39, 'DES', 4);
INSERT INTO cursus_diplomes VALUES (5, 'DUES', 2);
INSERT INTO cursus_diplomes VALUES (38, 'Diplôme de Technicien Spécialisé', 2);
INSERT INTO cursus_diplomes VALUES (9, 'DEUST', 2);
INSERT INTO cursus_diplomes VALUES (37, 'DEUTEC', 2);
INSERT INTO cursus_diplomes VALUES (34, 'Licence professionnelle', -1);
INSERT INTO cursus_diplomes VALUES (31, 'Diplôme de fin d''études secondaires', -1);
INSERT INTO cursus_diplomes VALUES (32, 'Doctorat', -1);


--
-- Data for Name: cursus_diplomes_apogee; Type: TABLE DATA; Schema: public; Owner: globdb
--

INSERT INTO cursus_diplomes_apogee VALUES ('A', 'Baccalauréat (Français)', 'Baccalauréat');
INSERT INTO cursus_diplomes_apogee VALUES ('B', 'BTS', 'BTS');
INSERT INTO cursus_diplomes_apogee VALUES ('C', 'DUT', 'DUT');
INSERT INTO cursus_diplomes_apogee VALUES ('D', 'Attestation délivrée à la suite d''un cursus en CPGE', 'Attestation suite CPGE');
INSERT INTO cursus_diplomes_apogee VALUES ('E', 'Diplôme d''Ingénieur (universitaire ou non)', 'Diplôme d''Ingénieur');
INSERT INTO cursus_diplomes_apogee VALUES ('F', 'Diplôme universitaire d''entrée en 1er cycle (DAEU, etc...)', 'Dip univ avant 1er cycle');
INSERT INTO cursus_diplomes_apogee VALUES ('G', 'Diplôme universitaire de 1er cycle (hors DUT)', 'Dip univ 1er cycle');
INSERT INTO cursus_diplomes_apogee VALUES ('H', 'Diplôme universitaire de 2nd cycle (hors ingénieur université)', 'Dip univ 2nd cycle');
INSERT INTO cursus_diplomes_apogee VALUES ('I', 'DEUG (y compris DEUG intermédiaire IUP et DEUP)', 'DEUG');
INSERT INTO cursus_diplomes_apogee VALUES ('J', 'Diplôme universitaire de 3ème cycle (hors ingénieur université)', 'Dip univ 3ème cycle');
INSERT INTO cursus_diplomes_apogee VALUES ('K', 'Attestation 1ère année médecine pharmacie odontologie', 'Attes 1ère an méd pharmac');
INSERT INTO cursus_diplomes_apogee VALUES ('L', 'Diplôme du secteur paramédical et social', 'Dip secteur paramédical');
INSERT INTO cursus_diplomes_apogee VALUES ('M', 'Autre diplôme universtaire de 1er cycle hors DUT', 'Autr dip 1er cycle');
INSERT INTO cursus_diplomes_apogee VALUES ('N', 'Licence y compris licence professionnelle et IUP ET LMD', 'Licence');
INSERT INTO cursus_diplomes_apogee VALUES ('P', 'Diplôme d''établissement étranger secondaire ou supérieur', 'Dip étranger sup ou second');
INSERT INTO cursus_diplomes_apogee VALUES ('Q', 'Maîtrise et maîtrise intermé. y compris IUP MST MSG et MIAGE', 'Maîtrise');
INSERT INTO cursus_diplomes_apogee VALUES ('R', 'Autre diplôme de 2ème cycle et magistère', 'Autr dip 2ème cycle');
INSERT INTO cursus_diplomes_apogee VALUES ('S', 'Autre diplôme supérieur', 'Autre diplôme supérieur');
INSERT INTO cursus_diplomes_apogee VALUES ('T', 'Aucun diplôme supérieur', 'Aucun diplôme supérieur');
INSERT INTO cursus_diplomes_apogee VALUES ('U', 'Diplôme de 3ème cycle, Master LMD (hors diplôme d''ingénieur', 'Diplôme 3ème cycle');
INSERT INTO cursus_diplomes_apogee VALUES ('V', 'Diplôme fin 2ème cycle études médicales, pharmaceutiques', 'Dip fin 2ème cycle médic');
INSERT INTO cursus_diplomes_apogee VALUES ('X', 'Diplôme d''établissement étranger secondaire ou supérieur', 'Dip étranger sup ou second');

--
-- Data for Name: cursus_mentions; Type: TABLE DATA; Schema: public; Owner: globdb
--

INSERT INTO cursus_mentions VALUES (1, '');
INSERT INTO cursus_mentions VALUES (2, 'Ajourné');
INSERT INTO cursus_mentions VALUES (3, 'Passable');
INSERT INTO cursus_mentions VALUES (4, 'Assez bien');
INSERT INTO cursus_mentions VALUES (5, 'Bien');
INSERT INTO cursus_mentions VALUES (6, 'Très bien');
INSERT INTO cursus_mentions VALUES (7, 'En cours');
INSERT INTO cursus_mentions VALUES (8, 'Sans objet');
INSERT INTO cursus_mentions VALUES (11, 'Honorable');
INSERT INTO cursus_mentions VALUES (12, 'Très honorable');
INSERT INTO cursus_mentions VALUES (13, 'Admis');

--
-- Data for Name: decisions; Type: TABLE DATA; Schema: public; Owner: globdb
--

INSERT INTO decisions VALUES (-7, 'Admis : attente de confirmation', 1, 1);
INSERT INTO decisions VALUES (-6, 'Convocable à un entretien téléphonique', 1, 1);
INSERT INTO decisions VALUES (-5, 'Liste complémentaire après entretien', 1, 1);
INSERT INTO decisions VALUES (-4, 'Convocable à l''entretien', 1, 1);
INSERT INTO decisions VALUES (-3, 'Liste complémentaire', 1, 1);
INSERT INTO decisions VALUES (-2, 'En attente', 1, 1);
INSERT INTO decisions VALUES (-1, 'Admis sous réserve', 1, 1);
INSERT INTO decisions VALUES (0, 'Non traitée', 1, 1);
INSERT INTO decisions VALUES (1, 'Admis', 1, 1);
INSERT INTO decisions VALUES (2, 'Refus', 1, 1);
INSERT INTO decisions VALUES (3, 'Dossier Transmis', 1, 1);
INSERT INTO decisions VALUES (4, 'Refus après entretien', 1, 1);
INSERT INTO decisions VALUES (5, 'Admis après entretien', 1, 1);
INSERT INTO decisions VALUES (6, 'Admis depuis la Liste complémentaire', 1, 1);
INSERT INTO decisions VALUES (7, 'Admis après recours', 1, 1);
INSERT INTO decisions VALUES (8, 'Refus après recours', 1, 1);
INSERT INTO decisions VALUES (9, 'Désistement', 1, 1);
INSERT INTO decisions VALUES (10, 'Admission confirmée', 1, 1);

--
-- Data for Name: liste_langues; Type: TABLE DATA; Schema: public; Owner: globdb
--

INSERT INTO liste_langues VALUES (1, 'Anglais');
INSERT INTO liste_langues VALUES (4, 'Arabe');
INSERT INTO liste_langues VALUES (5, 'Chinois');
INSERT INTO liste_langues VALUES (6, 'Espagnol');
INSERT INTO liste_langues VALUES (7, 'Français');
INSERT INTO liste_langues VALUES (8, 'Russe');
INSERT INTO liste_langues VALUES (9, 'Japonais');
INSERT INTO liste_langues VALUES (10, 'Italien');
INSERT INTO liste_langues VALUES (11, 'Luxembourgeois');
INSERT INTO liste_langues VALUES (2, 'Allemand');
INSERT INTO liste_langues VALUES (12, 'Lithuanien');


-- Initialisation des Séquences
SELECT setval('annees_id_seq', (SELECT max(id) FROM annees));
SELECT setval('cursus_diplomes_id_seq', (SELECT max(id) FROM cursus_diplomes));
SELECT setval('cursus_mentions_id_seq', (SELECT max(id) FROM cursus_mentions));
SELECT setval('liste_langues_id_seq', (SELECT max(id) FROM liste_langues));
SELECT setval('motifs_refus_id_seq', (SELECT max(id) FROM motifs_refus));


-- Décisions par défaut pour la composante du jeu d'essai
INSERT INTO decisions_composantes (SELECT '101', decisions.id FROM decisions ORDER BY id);


-- Départements français

INSERT INTO departements_fr VALUES('01','Ain');
INSERT INTO departements_fr VALUES('02','Aisne');
INSERT INTO departements_fr VALUES('03','Allier');
INSERT INTO departements_fr VALUES('04','Alpes-de-Haute-Provence');
INSERT INTO departements_fr VALUES('05','Hautes-Alpes');
INSERT INTO departements_fr VALUES('06','Alpes-Maritimes');
INSERT INTO departements_fr VALUES('07','Ardèche');
INSERT INTO departements_fr VALUES('08','Ardennes');
INSERT INTO departements_fr VALUES('09','Ariège');
INSERT INTO departements_fr VALUES('10','Aube');
INSERT INTO departements_fr VALUES('11','Aude');
INSERT INTO departements_fr VALUES('12','Aveyron');
INSERT INTO departements_fr VALUES('13','Bouches-du-Rhône');
INSERT INTO departements_fr VALUES('14','Calvados');
INSERT INTO departements_fr VALUES('15','Cantal');
INSERT INTO departements_fr VALUES('16','Charente');
INSERT INTO departements_fr VALUES('17','Charente-Maritime');
INSERT INTO departements_fr VALUES('18','Cher');
INSERT INTO departements_fr VALUES('19','Corrèze');
INSERT INTO departements_fr VALUES('2A','Corse-du-Sud');
INSERT INTO departements_fr VALUES('2B','Haute-Corse');
INSERT INTO departements_fr VALUES('21','Côte-d''Or');
INSERT INTO departements_fr VALUES('22','Côtes-d''Armor');
INSERT INTO departements_fr VALUES('23','Creuse');
INSERT INTO departements_fr VALUES('24','Dordogne');
INSERT INTO departements_fr VALUES('25','Doubs');
INSERT INTO departements_fr VALUES('26','Drôme');
INSERT INTO departements_fr VALUES('27','Eure');
INSERT INTO departements_fr VALUES('28','Eure-et-Loir');
INSERT INTO departements_fr VALUES('29','Finistère');
INSERT INTO departements_fr VALUES('30','Gard');
INSERT INTO departements_fr VALUES('31','Haute-Garonne');
INSERT INTO departements_fr VALUES('32','Gers');
INSERT INTO departements_fr VALUES('33','Gironde');
INSERT INTO departements_fr VALUES('34','Hérault');
INSERT INTO departements_fr VALUES('35','Ille-et-Vilaine');
INSERT INTO departements_fr VALUES('36','Indre');
INSERT INTO departements_fr VALUES('37','Indre-et-Loire');
INSERT INTO departements_fr VALUES('38','Isère');
INSERT INTO departements_fr VALUES('39','Jura');
INSERT INTO departements_fr VALUES('40','Landes');
INSERT INTO departements_fr VALUES('41','Loir-et-Cher');
INSERT INTO departements_fr VALUES('42','Loire');
INSERT INTO departements_fr VALUES('43','Haute-Loire');
INSERT INTO departements_fr VALUES('44','Loire-Atlantique');
INSERT INTO departements_fr VALUES('45','Loiret');
INSERT INTO departements_fr VALUES('46','Lot');
INSERT INTO departements_fr VALUES('47','Lot-et-Garonne');
INSERT INTO departements_fr VALUES('48','Lozère');
INSERT INTO departements_fr VALUES('49','Maine-et-Loire');
INSERT INTO departements_fr VALUES('50','Manche');
INSERT INTO departements_fr VALUES('51','Marne');
INSERT INTO departements_fr VALUES('52','Haute-Marne');
INSERT INTO departements_fr VALUES('53','Mayenne');
INSERT INTO departements_fr VALUES('54','Meurthe-et-Moselle');
INSERT INTO departements_fr VALUES('55','Meuse');
INSERT INTO departements_fr VALUES('56','Morbihan');
INSERT INTO departements_fr VALUES('57','Moselle');
INSERT INTO departements_fr VALUES('58','Nièvre');
INSERT INTO departements_fr VALUES('59','Nord');
INSERT INTO departements_fr VALUES('60','Oise');
INSERT INTO departements_fr VALUES('61','Orne');
INSERT INTO departements_fr VALUES('62','Pas-de-Calais');
INSERT INTO departements_fr VALUES('63','Puy-de-Dôme');
INSERT INTO departements_fr VALUES('64','Pyrénées-Atlantiques');
INSERT INTO departements_fr VALUES('65','Hautes-Pyrénées');
INSERT INTO departements_fr VALUES('66','Pyrénées-Orientales');
INSERT INTO departements_fr VALUES('67','Bas-Rhin');
INSERT INTO departements_fr VALUES('68','Haut-Rhin');
INSERT INTO departements_fr VALUES('69','Rhône');
INSERT INTO departements_fr VALUES('70','Haute-Saône');
INSERT INTO departements_fr VALUES('71','Saône-et-Loire');
INSERT INTO departements_fr VALUES('72','Sarthe');
INSERT INTO departements_fr VALUES('73','Savoie');
INSERT INTO departements_fr VALUES('74','Haute-Savoie');
INSERT INTO departements_fr VALUES('75','Paris');
INSERT INTO departements_fr VALUES('76','Seine-Maritime');
INSERT INTO departements_fr VALUES('77','Seine-et-Marne');
INSERT INTO departements_fr VALUES('78','Yvelines');
INSERT INTO departements_fr VALUES('79','Deux-Sèvres');
INSERT INTO departements_fr VALUES('80','Somme');
INSERT INTO departements_fr VALUES('81','Tarn');
INSERT INTO departements_fr VALUES('82','Tarn-et-Garonne');
INSERT INTO departements_fr VALUES('83','Var');
INSERT INTO departements_fr VALUES('84','Vaucluse');
INSERT INTO departements_fr VALUES('85','Vendée');
INSERT INTO departements_fr VALUES('86','Vienne');
INSERT INTO departements_fr VALUES('87','Haute-Vienne');
INSERT INTO departements_fr VALUES('88','Vosges');
INSERT INTO departements_fr VALUES('89','Yonne');
INSERT INTO departements_fr VALUES('90','Territoire de Belfort');
INSERT INTO departements_fr VALUES('91','Essonne');
INSERT INTO departements_fr VALUES('92','Hauts-de-Seine');
INSERT INTO departements_fr VALUES('93','Seine-Saint-Denis');
INSERT INTO departements_fr VALUES('94','Val-de-Marne');
INSERT INTO departements_fr VALUES('95','Val-d''Oise');
INSERT INTO departements_fr VALUES('971','Guadeloupe');
INSERT INTO departements_fr VALUES('972','Martinique');
INSERT INTO departements_fr VALUES('973','Guyane');
INSERT INTO departements_fr VALUES('974','La Réunion');
INSERT INTO departements_fr VALUES('987','Polynésie Française');


--