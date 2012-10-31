<?php
	include "/www-root/uds_test/configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$PERIODE="2008";

	$file=fopen("stats_".time().".csv", "w+") or die("Erreur de création du fichier");

	$dbr=db_connect();

	$res_composantes=db_query($dbr,"SELECT $_DBC_composantes_id, $_DBC_composantes_nom FROM $_DB_composantes
												ORDER BY $_DBC_composantes_nom");

	$rows_comp=db_num_rows($res_composantes);

	for($c=0; $c<$rows_comp; $c++)
	{
		list($comp_id, $comp_nom)=db_fetch_row($res_composantes, $c);

		// Séparateur pour préparer la future mise en page
		if($c!=0)
			fwrite($file, str_repeat("\"\";", 14) . "\n");

		fwrite($file, "\"$comp_nom\";".str_repeat("\"\";", 13) . "\n");
		fwrite($file, "\"Intitulé\";\"Total candidatures\";\"Admis, confirmations attendues\";\"Admis\";\"Admissions confirmées\";\"Admis sous réserve\";\"Restants sur liste complémentaire\";\"Convoqués à l'entretien (sans suite)\";\"Dossiers transmis\";\"En attente\";\"Refus\";\"Désistements\";\"Décision non rendue\";\"Non recevables\";\"Dossiers non traités\";\n");

		// Formations dans cette composante
		$res_propspec=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_mentions_nom, $_DBC_specs_nom,
														$_DBC_propspec_finalite
													FROM $_DB_propspec,$_DB_mentions
												WHERE $_DBC_propspec_annee=$_DBC_annees_id
												AND $_DBC_specs_mention_id=$_DBC_mentions_id
												AND $_DBC_specs_id=$_DBC_propspec_id_spec
												AND $_DBC_propspec_comp_id='$comp_id'
												AND $_DBC_propspec_id IN (SELECT distinct($_DBC_cand_propspec_id) FROM $_DB_cand
																				  WHERE $_DBC_cand_periode='$PERIODE')
													ORDER BY $_DBC_annees_ordre, $_DBC_mentions_nom, $_DBC_specs_nom, $_DBC_propspec_finalite");

		$rows_propspec=db_num_rows($res_propspec);

		$old_mention="";

		for($p=0; $p<$rows_propspec; $p++)
		{
			list($propspec_id, $annee, $mention, $spec, $finalite)=db_fetch_row($res_propspec, $p);

			$spec_nom=$annee=="" ? "$spec" : "$annee $spec";
			$spec_nom.=$tab_finalite[$finalite]!="" ? " $tab_finalite[$finalite]" : "";

			if($mention!=$old_mention)
			{
				fwrite($file, "\"Mention : $mention\";".str_repeat("\"\";", 13) . "\n");

				$old_mention=$mention;
			}

			$res_counts=db_query($dbr, "SELECT $_DBC_cand_statut, $_DBC_cand_decision,count($_DBC_cand_id)
													FROM $_DB_cand
												WHERE $_DBC_cand_propspec_id='$propspec_id'
												AND $_DBC_cand_periode='$PERIODE'
												GROUP BY $_DBU_cand_statut, $_DBU_cand_decision");

			$all_array_decs=db_fetch_all($res_counts);

			$total=$admis_non_conf=$nb_non_recevables=$nb_rec_non_traitees=$nb_attente=$nb_desistement=$nb_transmis=$nb_entretien=$non_traites=$nb_admis=$nb_admis_conf=$nb_admis_recours=$nb_admis_entretiens=$nb_reserve=$nb_depuis_liste=$nb_liste=$nb_refus=0;

			if(!is_array($all_array_decs))
			{
				print("COMPOSANTE : $comp_id | PROPSPEC_ID : $propspec_id\n");
				var_dump($all_array_decs);
			}
			else
			{
				foreach($all_array_decs as $array_dec)
				{
					$total+=$array_dec["count"];

					if($array_dec["statut"]==-1) // Non recevables
						$nb_non_recevables+=$array_dec["count"];
					elseif($array_dec["statut"]==0) // Non traitées
						$nb_rec_non_traitees+=$array_dec["count"];
					elseif($array_dec["statut"]==1) // Recevables
					{
						switch($array_dec["decision"])
						{						  
						   case "$__DOSSIER_ADMIS_AVANT_CONFIRMATION" : $admis_non_conf=$array_dec["count"];
																				      break;
																				
							case "$__DOSSIER_NON_TRAITE" 	:  		$non_traites=$array_dec["count"];
																				break;

							case "$__DOSSIER_ADMIS"	 	:				$nb_admis+=$array_dec["count"];
																				break;
																				
							case "$__DOSSIER_ADMISSION_CONFIRMEE":	$nb_admis_conf+=$array_dec["count"];
																				break;

							case "$__DOSSIER_TRANSMIS"	 	:			$nb_transmis+=$array_dec["count"];
																				break;

							case "$__DOSSIER_ADMIS_RECOURS" 	:  	$nb_admis+=$array_dec["count"];
																				break;

							case "$__DOSSIER_ADMIS_ENTRETIEN" :  	$nb_admis+=$array_dec["count"];
																				break;

							case "$__DOSSIER_SOUS_RESERVE" 	:  	$nb_reserve+=$array_dec["count"];
																				break;

							case "$__DOSSIER_ADMIS_LISTE_COMP" 	:  $nb_admis+=$array_dec["count"];
																				break;

							case "$__DOSSIER_LISTE" 				:	$nb_liste+=$array_dec["count"];
																				break;

							case "$__DOSSIER_ENTRETIEN"			:  $nb_entretien+=$array_dec["count"];
																				break;
																				
							case "$__DOSSIER_ENTRETIEN_TEL"		:  $nb_entretien+=$array_dec["count"];
																				break;																				

							case "$__DOSSIER_LISTE_ENTRETIEN"	:  $nb_liste+=$array_dec["count"];
																				break;

							case "$__DOSSIER_EN_ATTENTE" 			:	$nb_attente+=$array_dec["count"];
																				break;

							case "$__DOSSIER_REFUS" 				:	$nb_refus+=$array_dec["count"];
																				break;

							case "$__DOSSIER_REFUS_RECOURS"		:	$nb_refus+=$array_dec["count"];
																				break;

							case "$__DOSSIER_REFUS_ENTRETIEN"	:	$nb_refus+=$array_dec["count"];
																				break;

							case "$__DOSSIER_DESISTEMENT"			:	$nb_desistement+=$array_dec["count"];
																				break;
						}
					}
				}

				fwrite($file,"\"$spec_nom\";\"$total\";\"$admis_non_conf\";\"$nb_admis\";\"$nb_admis_confirmes\";\"$nb_reserve\";\"$nb_liste\";\"$nb_entretien\";\"$nb_transmis\";\"$nb_attente\";\"$nb_refus\";\"$nb_desistement\";\"$non_traites\";\"$nb_non_recevables\";\"$nb_rec_non_traitees\";\n");
			}

			db_free_result($res_counts);
			
			// DBG
			// print("\"$code\";\"$spec_nom\";\n");
		}
		
		db_free_result($res_propspec);
	}
	
	db_free_result($res_composantes);
	fclose($file);
	db_close($dbr);
?>
