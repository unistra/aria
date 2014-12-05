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

	include "../../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";


	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	if(!in_array($_SESSION['niveau'], array("$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	// Le paramètre transmis est le numéro de la session
	// (= numéro d'intervalle d'ouverture : session 1 = premier intervalle, etc)
	if(isset($_GET["n"]) && ctype_digit($_GET["n"]) && (isset($_SESSION["all_sessions"]) || isset($_SESSION["all_sessions_groups"])))
		$n_intervalle=$_GET["n"];
	elseif(array_key_exists("intervalle_n", $_POST) && (isset($_SESSION["all_sessions"]) || isset($_SESSION["all_sessions_groups"])))
		$n_intervalle=$_POST["intervalle_n"];
	else
	{
		header("Location:index.php");
		exit();
	}

	$dbr=db_connect();

	if(isset($_POST["valider"]) || isset($_POST["valider_x"]))
	{
		$global_erreurs_chrono=0;
		$global_erreurs_format=0;

		$req="";

		if(isset($_POST["jour_ouverture_all"]) && !empty($_POST["jour_ouverture_all"]) && isset($_POST["mois_ouverture_all"]) && !empty($_POST["mois_ouverture_all"]) && isset($_POST["annee_ouverture_all"]) && !empty($_POST["annee_ouverture_all"]))
		{
			$jour_ouverture=$_POST["jour_ouverture_all"];
			$mois_ouverture=$_POST["mois_ouverture_all"];
			$annee_ouverture=$_POST["annee_ouverture_all"];

			if(!ctype_digit($annee_ouverture))
				$annee_ouverture=date("Y");

			$new_date_ouverture=MakeTime(0,30,0,$mois_ouverture, $jour_ouverture, $annee_ouverture); // date au format unix : le jour même, le matin

			$req.="$_DBU_session_ouverture='$new_date_ouverture',";
		}
		if(isset($_POST["jour_fermeture_all"]) && !empty($_POST["jour_fermeture_all"]) && isset($_POST["mois_fermeture_all"]) && !empty($_POST["mois_fermeture_all"]) && isset($_POST["annee_fermeture_all"]) && !empty($_POST["annee_fermeture_all"]))
		{
			$jour_fermeture=$_POST["jour_fermeture_all"];
			$mois_fermeture=$_POST["mois_fermeture_all"];
			$annee_fermeture=$_POST["annee_fermeture_all"];

			if(!ctype_digit($annee_fermeture))
				$annee_fermeture=date("Y");

			$new_date_fermeture=MakeTime(23,59,50,$mois_fermeture, $jour_fermeture, $annee_fermeture);  // idem : le soir

			$req.="$_DBU_session_fermeture='$new_date_fermeture',";
		}
		if(isset($_POST["jour_reception_all"]) && !empty($_POST["jour_reception_all"]) && isset($_POST["mois_reception_all"]) && !empty($_POST["mois_reception_all"]) && isset($_POST["annee_reception_all"]) && !empty($_POST["annee_reception_all"]))
		{
			$jour_reception=$_POST["jour_reception_all"];
			$mois_reception=$_POST["mois_reception_all"];
			$annee_reception=$_POST["annee_reception_all"];

			if(!ctype_digit($annee_reception))
				$annee_reception=date("Y");

			$new_date_reception=MakeTime(23,59,50,$mois_reception, $jour_reception, $annee_reception);  // idem : le soir

			$req.="$_DBU_session_reception='$new_date_reception',";
		}

		//	$_SESSION["all_sessions"][$propspec_id]

		// Batterie de tests
		// TODO : A OPTIMISER

		if(isset($new_date_fermeture) && isset($new_date_ouverture))
		{
			if($new_date_fermeture<=$new_date_ouverture)
				$global_erreurs_chrono=1;
		}
		elseif(isset($new_date_fermeture))
		{
			foreach($_SESSION["all_sessions"] as $propspec => $intervalles)
			{
				if(array_key_exists($n_intervalle, $intervalles) && $new_date_fermeture<=$intervalles[$n_intervalle]["ouv"])
				{
					$global_erreurs_chrono=1;
					break;
				}
			}
			
			foreach($_SESSION["all_sessions_groups"] as $groupe => $intervalles)
			{
				if(array_key_exists($n_intervalle, $intervalles) && $new_date_fermeture<=$intervalles[$n_intervalle]["ouv"])
				{
					$global_erreurs_chrono=1;
					break;
				}
			}
		}
		elseif(isset($new_date_ouverture))
		{
			foreach($_SESSION["all_sessions"] as $propspec => $intervalles)
			{
				if(array_key_exists($n_intervalle, $intervalles) && $new_date_ouverture>=$intervalles[$n_intervalle]["ferm"])
				{
					$global_erreurs_chrono=1;
					break;
				}
			}
			
			foreach($_SESSION["all_sessions_groups"] as $groupe => $intervalles)
			{
				if(array_key_exists($n_intervalle, $intervalles) && $new_date_ouverture>=$intervalles[$n_intervalle]["ferm"])
				{
					$global_erreurs_chrono=1;
					break;
				}
			}
		}

		if(isset($new_date_reception) && isset($new_date_fermeture))
		{
			if($new_date_reception<=$new_date_fermeture)
				$global_erreurs_chrono=1;
		}
		elseif(isset($new_date_fermeture))
		{
			foreach($_SESSION["all_sessions"] as $propspec => $intervalles)
			{
				if(array_key_exists($n_intervalle, $intervalles) && $new_date_fermeture>=$intervalles[$n_intervalle]["rec"])
				{
					$global_erreurs_chrono=1;
					break;
				}
			}
			
			foreach($_SESSION["all_sessions_groups"] as $groupe => $intervalles)
			{
				if(array_key_exists($n_intervalle, $intervalles) && $new_date_fermeture>=$intervalles[$n_intervalle]["rec"])
				{
					$global_erreurs_chrono=1;
					break;
				}
			}
		}
		elseif(isset($new_date_reception))
		{
			foreach($_SESSION["all_sessions"] as $propspec => $intervalles)
			{
				if(array_key_exists($n_intervalle, $intervalles) && $new_date_reception<=$intervalles[$n_intervalle]["ferm"])
				{
     				$global_erreurs_chrono=1;
					break;
				}
			}
			
			foreach($_SESSION["all_sessions_groups"] as $groupe => $intervalles)
			{
				if(array_key_exists($n_intervalle, $intervalles) && $new_date_reception<=$intervalles[$n_intervalle]["ferm"])
				{
     				$global_erreurs_chrono=1;
					break;
				}
			}
		}

		// Fin des tests de validité des dates
		if($global_erreurs_chrono==0 && $req!="")
		{
			$result=db_query($dbr,"SELECT $_DBC_propspec_id, 
			                              CASE WHEN $_DBC_propspec_id IN (SELECT $_DBC_groupes_spec_propspec_id FROM $_DB_groupes_spec) 
                                       THEN (SELECT $_DBC_groupes_spec_groupe FROM $_DB_groupes_spec WHERE $_DBC_groupes_spec_propspec_id=$_DBC_propspec_id)
                                       ELSE '-1'
                                       END as groupe_id FROM $_DB_propspec 
											WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
											AND $_DBC_propspec_active='1'
											   ORDER BY groupe_id");

			$rows=db_num_rows($result);

			// suppression de la dernière virgule
			$req=substr($req, 0, -1);
			
			for($i=0; $i<$rows; $i++)
			{
				list($propspec_id, $groupe_id)=db_fetch_row($result, $i);

				if(($groupe_id=="-1" && array_key_exists("activ", $_POST) && array_key_exists($propspec_id, $_POST["activ"]))
				   || $groupe_id!="-1" && array_key_exists("g_activ", $_POST) && array_key_exists($groupe_id, $_POST["g_activ"]))
				{
					// Mise à jour en cas d'existence, insertion sinon
					if(db_num_rows(db_query($dbr,"SELECT * FROM $_DB_session WHERE $_DBU_session_propspec_id='$propspec_id'
																								AND $_DBU_session_id='".$_SESSION["all_sessions"]["$propspec_id"]["$n_intervalle"]["s_id"]."'
																								AND $_DBU_session_periode='$_SESSION[user_periode]'")))
						db_query($dbr, "UPDATE $_DB_session SET $req
												WHERE $_DBU_session_propspec_id='$propspec_id'
												AND $_DBU_session_id='".$_SESSION["all_sessions"]["$propspec_id"]["$n_intervalle"]["s_id"]."'
												AND $_DBU_session_periode='$_SESSION[user_periode]'");
					elseif(isset($new_date_ouverture) && isset($new_date_fermeture) && isset($new_date_reception))
					{
					   $res_max=db_query($dbr, "SELECT max($_DBC_session_id)+1 FROM $_DB_session 
					                               WHERE $_DBC_session_propspec_id='$propspec_id'
					                               AND $_DBU_session_periode='$_SESSION[user_periode]'");
					                               
					   list($max_id)=db_fetch_row($res_max, 0);
					   
					   if($max_id=="")
					      $max_id="1";
					                                        
						db_query($dbr,"INSERT INTO $_DB_session VALUES('$propspec_id', '$max_id', '$new_date_ouverture','$new_date_fermeture','$new_date_reception','$_SESSION[user_periode]')");
				   }
				}

				// print("UPDATE $_DB_session SET $req WHERE $_DBU_session_propspec_id='$propspec_id' AND $_DBU_session_id='$session_id' AND $_DBU_session_periode='$_SESSION[user_periode]'\n<br>");

				write_evt($dbr, $__EVT_ID_G_SESSION, "Modification globale session $session_id ($propspec_id)");
			}
		}

		// Champs individuels si au moins l'un des champs "all" n'a pas été utilisé
		if(!isset($new_date_fermeture) || !isset($new_date_ouverture) || !isset($new_date_reception))
		{
			$result=db_query($dbr,"SELECT $_DBC_propspec_id,
                                       CASE WHEN $_DBC_propspec_id IN (SELECT $_DBC_groupes_spec_propspec_id FROM $_DB_groupes_spec WHERE $_DBC_groupes_spec_dates_communes='t') 
                                          THEN (SELECT $_DBC_groupes_spec_groupe FROM $_DB_groupes_spec WHERE $_DBC_groupes_spec_propspec_id=$_DBC_propspec_id)
                                       ELSE '-1'
                                       END as groupe_id
                                   FROM $_DB_propspec 
										  WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										  AND $_DBC_propspec_active='1'
										  ORDER BY groupe_id, $_DBC_propspec_id");
			$rows=db_num_rows($result);

			$requete="";
			$total_erreurs_format=$total_erreurs_chrono=$total_collisions=0;
			$array_fond=array();
			$array_fond_groupe=array();
			$array_cur_values=array();
			$array_cur_values_groupe=array();
         $old_groupe_id="--";

			for($i=0; $i<$rows; $i++)
			{
			   $erreur=$collision=0;
			   
				list($propspec_id, $groupe_id)=db_fetch_row($result, $i);

				if($groupe_id=="-1" || $groupe_id!=$old_groupe_id)
				{
					// Texte par défaut (cette valeur sera modifiée en cas d'erreur)
					if($groupe_id=="-1")
					   $array_fond[$propspec_id]="fond_menu";
					elseif($groupe_id!=$old_groupe_id)
					   $array_fond_groupe[$groupe_id]="fond_menu";
					
					unset($new_date_ouv);
					unset($new_date_ferm);
					unset($new_date_rec);
	
	            if($groupe_id!="-1")
	            {
	               $session_id=isset($_POST["g_id"][$groupe_id]) ? $_POST["g_id"][$groupe_id] : "";
	               $array_fond[$groupe_id]="fond_menu";
	            }
	            else
	               $session_id=isset($_POST["s_id"][$propspec_id]) ? $_POST["s_id"][$propspec_id] : "";
	
					if($session_id!="")
					{
	//					$session_id=$_POST["s_id"][$propspec_id];
	
						// Si tous les champs sont vides ou si la case est décochée : suppression de la session pour cette formation
						if($groupe_id=="-1" && ((array_key_exists($propspec_id, $_POST["jour_ouv"]) && array_key_exists($propspec_id, $_POST["mois_ouv"]) && array_key_exists($propspec_id, $_POST["annee_ouv"])
							&& array_key_exists($propspec_id, $_POST["jour_ferm"]) && array_key_exists($propspec_id, $_POST["mois_ferm"]) && array_key_exists($propspec_id, $_POST["annee_ferm"])
							&& array_key_exists($propspec_id, $_POST["jour_rec"]) && array_key_exists($propspec_id, $_POST["mois_rec"]) && array_key_exists($propspec_id, $_POST["annee_rec"])
							&& $_POST["jour_ouv"][$propspec_id]=="" && $_POST["mois_ouv"][$propspec_id]=="" && $_POST["annee_ouv"][$propspec_id]==""
							&& $_POST["jour_ferm"][$propspec_id]=="" && $_POST["mois_ferm"][$propspec_id]=="" && $_POST["annee_ferm"][$propspec_id]==""
							&& $_POST["jour_rec"][$propspec_id]=="" && $_POST["mois_rec"][$propspec_id]=="" && $_POST["annee_rec"][$propspec_id]=="")
							|| !array_key_exists("activ", $_POST) || (array_key_exists("activ", $_POST) && !array_key_exists($propspec_id, $_POST["activ"]))))
							$requete.="DELETE FROM $_DB_session WHERE $_DBC_session_propspec_id='$propspec_id'
																			AND $_DBC_session_id='$session_id'
																			AND $_DBC_session_periode='$_SESSION[user_periode]'; ";
		            elseif($groupe_id!="-1" && ((array_key_exists($groupe_id, $_POST["g_jour_ouv"]) && array_key_exists($groupe_id, $_POST["g_mois_ouv"]) && array_key_exists($groupe_id, $_POST["g_annee_ouv"])
							&& array_key_exists($groupe_id, $_POST["g_jour_ferm"]) && array_key_exists($groupe_id, $_POST["g_mois_ferm"]) && array_key_exists($groupe_id, $_POST["g_annee_ferm"])
							&& array_key_exists($groupe_id, $_POST["g_jour_rec"]) && array_key_exists($groupe_id, $_POST["g_mois_rec"]) && array_key_exists($groupe_id, $_POST["g_annee_rec"])
							&& $_POST["g_jour_ouv"][$groupe_id]=="" && $_POST["g_mois_ouv"][$groupe_id]=="" && $_POST["g_annee_ouv"][$groupe_id]==""
							&& $_POST["g_jour_ferm"][$groupe_id]=="" && $_POST["g_mois_ferm"][$groupe_id]=="" && $_POST["g_annee_ferm"][$groupe_id]==""
							&& $_POST["g_jour_rec"][$groupe_id]=="" && $_POST["g_mois_rec"][$groupe_id]=="" && $_POST["g_annee_rec"][$groupe_id]=="")
							|| !array_key_exists("g_activ", $_POST) || (array_key_exists("g_activ", $_POST) && !array_key_exists($groupe_id, $_POST["g_activ"]))))
							$requete.="DELETE FROM $_DB_session WHERE $_DBC_session_propspec_id IN (SELECT $_DBC_groupes_spec_propspec_id WHERE $_DBC_groupes_spec_groupe='$groupe_id')
																			AND $_DBC_session_id='$session_id'
																			AND $_DBC_session_periode='$_SESSION[user_periode]'; ";
						else
						{
							$req_set="";
							$erreur_format=$erreur_chrono=0;
							$array_comp=array();
	
							if(!isset($new_date_ouverture))
							{
							   if($groupe_id=="-1" && array_key_exists($propspec_id, $_POST["jour_ouv"]) && array_key_exists($propspec_id, $_POST["mois_ouv"]) && array_key_exists($propspec_id, $_POST["annee_ouv"]))
							   {
	   							$jour_ouv=$_POST["jour_ouv"][$propspec_id];
	   							$mois_ouv=$_POST["mois_ouv"][$propspec_id];
	   							$annee_ouv=$_POST["annee_ouv"][$propspec_id];
	   							
	   							if(!ctype_digit($jour_ouv) || $jour_ouv<1 || $jour_ouv>31 || !ctype_digit($mois_ouv) || $mois_ouv<1 || $mois_ouv>12)
	      						{
	      							$array_fond[$propspec_id]="fond_rouge";
	      							$erreur_format=$erreur=1;
	      						}
	      						
	      						$array_cur_values[$propspec_id]=array("jour_ouv" => $jour_ouv, "mois_ouv" => $mois_ouv, "annee_ouv" => $annee_ouv);
	   				      }
	   				      elseif($groupe_id!="-1" && array_key_exists($groupe_id, $_POST["g_jour_ouv"]) && array_key_exists($groupe_id, $_POST["g_mois_ouv"]) && array_key_exists($groupe_id, $_POST["g_annee_ouv"]))
	                     {
	   							$jour_ouv=$_POST["g_jour_ouv"][$groupe_id];
	   							$mois_ouv=$_POST["g_mois_ouv"][$groupe_id];
	   							$annee_ouv=$_POST["g_annee_ouv"][$groupe_id];
	   							
	   							if(!ctype_digit($jour_ouv) || $jour_ouv<1 || $jour_ouv>31 || !ctype_digit($mois_ouv) || $mois_ouv<1 || $mois_ouv>12)
	      						{
	      							$array_fond_groupe[$groupe_id]="fond_rouge";
	      							$erreur_format=$erreur=1;
	      						}
	      						
	      						$array_cur_values_groupe[$groupe_id]=array("jour_ouv" => $jour_ouv, "mois_ouv" => $mois_ouv, "annee_ouv" => $annee_ouv);
	   				      }
	
								if($erreur==0)
								{
								  if(!ctype_digit($annee_ouv))
	   								$annee_ouv=date("Y");
	   
	   							$new_date_ouv=MakeTime(0,30,0,$mois_ouv, $jour_ouv, $annee_ouv); // date au format unix : le jour même, le matin
	   
	   							$req_set.="$_DBU_session_ouverture='$new_date_ouv'";
	   
	   							$array_comp[0]=$new_date_ouv;
	   					   }
							}
							
							$erreur=0;
	
							if(!isset($new_date_fermeture))
							{
							   if($groupe_id=="-1" && array_key_exists($propspec_id, $_POST["jour_ferm"]) && array_key_exists($propspec_id, $_POST["mois_ferm"]) && array_key_exists($propspec_id, $_POST["annee_ferm"]))
							   {
	   							$jour_ferm=$_POST["jour_ferm"][$propspec_id];
	   							$mois_ferm=$_POST["mois_ferm"][$propspec_id];
	   							$annee_ferm=$_POST["annee_ferm"][$propspec_id];
	   							
	   							if(!ctype_digit($jour_ferm) || $jour_ferm<1 || $jour_ferm>31 || !ctype_digit($mois_ferm) || $mois_ferm<1 || $mois_ferm>12)
	   							{
	   								$array_fond[$propspec_id]="fond_rouge";
	   								$erreur_format=$erreur=1;
	   							}
	   							
	   							if(!isset($array_cur_values[$propspec_id]))
	   								$array_cur_values[$propspec_id]=array("jour_ferm" => $jour_ferm, "mois_ferm" => $mois_ferm, "annee_ferm" => $annee_ferm);
	   							else
	   							{
	   								$array_cur_values[$propspec_id]["jour_ferm"]=$jour_ferm;
	   								$array_cur_values[$propspec_id]["mois_ferm"]=$mois_ferm;
	   								$array_cur_values[$propspec_id]["annee_ferm"]=$annee_ferm;
	   							}
	   					   }
	   					   elseif($groupe_id!="-1" && array_key_exists($groupe_id, $_POST["g_jour_ferm"]) && array_key_exists($groupe_id, $_POST["g_mois_ferm"]) && array_key_exists($groupe_id, $_POST["g_annee_ferm"]))
							   {
	   							$jour_ferm=$_POST["g_jour_ferm"][$groupe_id];
	   							$mois_ferm=$_POST["g_mois_ferm"][$groupe_id];
	   							$annee_ferm=$_POST["g_annee_ferm"][$groupe_id];
	   							
	   							if(!ctype_digit($jour_ferm) || $jour_ferm<1 || $jour_ferm>31 || !ctype_digit($mois_ferm) || $mois_ferm<1 || $mois_ferm>12)
	   							{
	   								$array_fond_groupe[$groupe_id]="fond_rouge";
	   								$erreur_format=$erreur=1;
	   							}
	   							
	   							if(!isset($array_cur_values_groupe[$groupe_id]))
	   								$array_cur_values_groupe[$groupe_id]=array("jour_ferm" => $jour_ferm, "mois_ferm" => $mois_ferm, "annee_ferm" => $annee_ferm);
	   							else
	   							{
	   								$array_cur_values_groupe[$groupe_id]["jour_ferm"]=$jour_ferm;
	   								$array_cur_values_groupe[$groupe_id]["mois_ferm"]=$mois_ferm;
	   								$array_cur_values_groupe[$groupe_id]["annee_ferm"]=$annee_ferm;
	   							}
	   					   }
	
								if($erreur==0)
								{
									if(!ctype_digit($annee_ferm))
										$annee_ferm=date("Y");
	
									$new_date_ferm=MakeTime(23, 59, 50, $mois_ferm, $jour_ferm, $annee_ferm); // date au format unix : le jour même, le soir
	
									if($req_set!="")
										$req_set.=",";
	
									$req_set.="$_DBU_session_fermeture='$new_date_ferm'";
	
									$array_comp[1]=$new_date_ferm;
								}
							}
	
	                  $erreur=0;
	
							if(!isset($new_date_reception))
							{
							   if($groupe_id=="-1" && array_key_exists($propspec_id, $_POST["jour_rec"]) && array_key_exists($propspec_id, $_POST["mois_rec"]) && array_key_exists($propspec_id, $_POST["annee_rec"]))
							   {
	   							$jour_rec=$_POST["jour_rec"][$propspec_id];
	   							$mois_rec=$_POST["mois_rec"][$propspec_id];
	   							$annee_rec=$_POST["annee_rec"][$propspec_id];
	   							
	   							if(!ctype_digit($jour_rec) || $jour_rec<1 || $jour_rec>31 || !ctype_digit($mois_rec) || $mois_rec<1 || $mois_rec>12)
	   							{
	   								$array_fond[$propspec_id]="fond_rouge";
	   								$erreur_format=$erreur=1;
	   							}
	   							
	   							if(!isset($array_cur_values[$propspec_id]))
	   								$array_cur_values[$propspec_id]=array("jour_rec" => $jour_rec, "mois_rec" => $mois_rec, "annee_rec" => $annee_rec);
	   							else
	   							{
	   								$array_cur_values[$propspec_id]["jour_rec"]=$jour_rec;
	   								$array_cur_values[$propspec_id]["mois_rec"]=$mois_rec;
	   								$array_cur_values[$propspec_id]["annee_rec"]=$annee_rec;
	   							}
	   					   }
	   					   elseif($groupe_id!="-1" && array_key_exists($groupe_id, $_POST["g_jour_rec"]) && array_key_exists($groupe_id, $_POST["g_mois_rec"]) && array_key_exists($groupe_id, $_POST["g_annee_rec"]))
							   {
	   							$jour_rec=$_POST["g_jour_rec"][$groupe_id];
	   							$mois_rec=$_POST["g_mois_rec"][$groupe_id];
	   							$annee_rec=$_POST["g_annee_rec"][$groupe_id];
	   							
	   							if(!ctype_digit($jour_rec) || $jour_rec<1 || $jour_rec>31 || !ctype_digit($mois_rec) || $mois_rec<1 || $mois_rec>12)
	   							{
	   								$array_fond_groupe[$groupe_id]="fond_rouge";
	   								$erreur_format=$erreur=1;
	   							}
	   							
	   							if(!isset($array_cur_values_groupe[$groupe_id]))
	   								$array_cur_values_groupe[$groupe_id]=array("jour_rec" => $jour_rec, "mois_rec" => $mois_rec, "annee_rec" => $annee_rec);
	   							else
	   							{
	   								$array_cur_values_groupe[$groupe_id]["jour_rec"]=$jour_rec;
	   								$array_cur_values_groupe[$groupe_id]["mois_rec"]=$mois_rec;
	   								$array_cur_values_groupe[$groupe_id]["annee_rec"]=$annee_rec;
	   							}
	   					   }
	
								if($erreur==0)
								{
									if(!ctype_digit($annee_rec))
										$annee_rec=date("Y");
	
									$new_date_rec=MakeTime(23, 59, 50, $mois_rec, $jour_rec, $annee_rec); // date au format unix : le jour même, le soir
	
									if($req_set!="")
										$req_set.=",";
	
									$req_set.="$_DBU_session_reception='$new_date_rec'";
	
									$array_comp[2]=$new_date_rec;
								}
							}
	
							// Tests chronologiques
							if(count($array_comp) > 1)				
							{
								$old_val="-1";
	
								foreach($array_comp as $val)
								{
									if($old_val>=$val)
									{
										$array_fond[$propspec_id]="fond_rouge";
										$erreur_chrono++;
										break;
									}
									else
										$old_val=$val;
								}
								
								if(count($array_comp)==3)
								{
									if($groupe_id=="-1")
									{
									   if(array_key_exists($propspec_id, $_SESSION["all_sessions"]) && count($_SESSION["all_sessions"][$propspec_id]))
									   {
										   foreach($_SESSION["all_sessions"][$propspec_id] as $all_n_intervalle => $intervalle)
   										{
	   										if($all_n_intervalle!=$n_intervalle)
		   									{
			   									if($array_comp[1]>=$intervalle["ouv"] && $array_comp[0]<=$intervalle["ferm"])
				   								{
					   								$array_fond[$propspec_id]="fond_orange";
						    							$collision=1;
							   						break;
								   				}
								   		   }
									   	}
                              }
								   }
								   else
								   {
								   	foreach($_SESSION["all_sessions_groups"][$groupe_id] as $all_n_intervalle => $intervalle)
										{
											if($all_n_intervalle!=$n_intervalle)
											{
												if($array_comp[1]>=$intervalle["ouv"] && $array_comp[0]<=$intervalle["ferm"])
												{
													$array_fond_groupe[$groupe_id]="fond_orange";
													$collision=1;
													break;
												}
										   }
										}
									}
							   }
							}
	
	                  if($groupe_id=="-1")
							   $array_cur_values[$propspec_id]["active"]=1;
							else
							   $array_cur_values_groupe[$groupe_id]["active"]=1;
	
							if($erreur_format)
								$total_erreurs_format++;
	
							if($erreur_chrono)
								$total_erreurs_chrono++;
								
							if($collision)
								$total_collisions++;
	
							// Aucune erreur et champs à mettre à jour déterminés : on complète la requête globale
							if(!$erreur_format && !$erreur_chrono && $req_set!="" && !$collision)
							{
								if(isset($new_date_ouv) && isset($new_date_ferm) && isset($new_date_rec))
								{
									if($groupe_id=="-1")
									{
										if(!array_key_exists("$propspec_id",$_SESSION["all_sessions"]) || !array_key_exists($n_intervalle, $_SESSION["all_sessions"][$propspec_id]))
											$requete.="INSERT INTO $_DB_session VALUES ('$propspec_id','$session_id','$new_date_ouv','$new_date_ferm','$new_date_rec', '$_SESSION[user_periode]');\n ";
										else
											$requete.="UPDATE $_DB_session SET $req_set WHERE $_DBU_session_propspec_id='$propspec_id' AND $_DBU_session_id='$session_id' AND $_DBU_session_periode='$_SESSION[user_periode]';\n ";
									
										write_evt($dbr, $__EVT_ID_G_SESSION, "Modification session $session_id ($propspec_id), période $_SESSION[user_periode]");
								   }
								   else
								   {
								   	$res_groupes=db_query($dbr, "SELECT $_DBC_groupes_spec_propspec_id FROM $_DB_groupes_spec WHERE $_DBC_groupes_spec_groupe='$groupe_id'");
								   	
								   	$nb_specs=db_num_rows($res_groupes);
								   	
								   	if($nb_specs)
								   	{
								   	   for($s=0; $s<$nb_specs; $s++)
								   	   {
								   	      list($grp_propspec_id)=db_fetch_row($res_groupes, $s);
								   	      
								   	      // if(!array_key_exists("$grp_propspec_id",$_SESSION["all_sessions"]) || !array_key_exists($n_intervalle, $_SESSION["all_sessions"][$grp_propspec_id]))
											   							   	      
								   	      if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_session WHERE $_DBC_session_propspec_id='$grp_propspec_id'
								   	      																			  AND $_DBC_session_id='$session_id'  
								   	      																			  AND $_DBC_session_periode='$_SESSION[user_periode]'")))
													$requete.="INSERT INTO $_DB_session VALUES ('$grp_propspec_id','$session_id','$new_date_ouv','$new_date_ferm','$new_date_rec', '$_SESSION[user_periode]');\n ";
												else
													$requete.="UPDATE $_DB_session SET $req_set WHERE $_DBU_session_propspec_id='$grp_propspec_id' AND $_DBU_session_id='$session_id' AND $_DBU_session_periode='$_SESSION[user_periode]';\n ";
											
												write_evt($dbr, $__EVT_ID_G_SESSION, "Modification session $session_id ($grp_propspec_id), période $_SESSION[user_periode]");
								   	   }
								      }
								      
								      db_free_result($res_groupes);	
								   }
								}
							}
						} // fin du else
					}
				}
				
				$old_groupe_id=$groupe_id;
			} // fin du for

			// Boucle terminée : on exécute la requête globale pour les champs non erronés
			if(!empty($requete))
				db_query($dbr, $requete);

			db_free_result($result);
		} // fin du else(all)

		if((!isset($total_collisions) || $total_collisions==0) && (!isset($global_erreurs_chrono) || $global_erreurs_chrono==0) && (!isset($total_erreurs_format) || ($total_erreurs_format==0)) && (!isset($total_erreurs_chrono) || $total_erreurs_chrono==0))
		{
			db_close($dbr);

			header("Location:index.php?succes=1");
			exit();
		}
	}

	// Sélection des formations, pour affichage
	$result=db_query($dbr, "SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
											 $_DBC_mentions_nom,
											 CASE WHEN $_DBC_propspec_id IN (SELECT $_DBC_groupes_spec_propspec_id FROM $_DB_groupes_spec)
                                     THEN (SELECT $_DBC_groupes_spec_groupe FROM $_DB_groupes_spec WHERE $_DBC_groupes_spec_propspec_id=$_DBC_propspec_id)
                                     ELSE '-1'
                                  END as groupe_id
										FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
									WHERE $_DBC_propspec_annee=$_DBC_annees_id
									AND $_DBC_propspec_id_spec=$_DBC_specs_id
									AND $_DBC_specs_mention_id=$_DBC_mentions_id
									AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
									AND $_DBC_propspec_active='1'
										ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, groupe_id, $_DBC_specs_nom_court, $_DBC_propspec_finalite");

	$rows=db_num_rows($result);

	if(!$rows)
		$aucune_specialite=1;

	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<div class='menu_haut_2'>
		<a href='index.php' target='_self'><img class='icone_menu_haut_2' border='0' src='<?php echo "$__ICON_DIR/kdeprint_report_16x16_menu2.png"; ?>' alt='+'></a>
		<a href='index.php' target='_self' class='lien_menu_haut_2'>Liste des sessions</a>
	</div>
	<?php
		titre_page_icone("Modifier les dates de la session n°$n_intervalle pour l'année $_SESSION[user_periode]-".($_SESSION["user_periode"]+1), "clock_32x32_fond.png", 15, "L");

		print("<form action='$php_self' method='POST' name='form1'>
				<input type='hidden' name='intervalle_n' value='$n_intervalle'>\n");

		if((isset($total_collisions) && $total_collisions>0) || (isset($global_erreurs_chrono) && $global_erreurs_chrono>0) || (isset($total_erreurs_format) && $total_erreurs_format>0) || (isset($total_erreurs_chrono) && $total_erreurs_chrono>0))
		{
			$cnt_erreurs=0;

			if(isset($total_erreurs_chrono) && $total_erreurs_chrono)
				$cnt_erreurs++;
			if(isset($total_erreurs_format) && $total_erreurs_format)
				$cnt_erreurs++;
			if(isset($global_erreurs_chrono) && $global_erreurs_chrono)
				$cnt_erreurs++;
		   if(isset($total_collisions) && $total_collisions)
				$cnt_erreurs++;

			if($cnt_erreurs > 1)
				$message="Erreurs : <br>- ";
			else
				$message="Erreur : ";

			if($global_erreurs_chrono!=0)
			{
				$message.="les dates globales ne respectent pas l'ordre chronologique !";

				if($cnt_erreurs>1)
					$message.="<br>- ";
			}

			if(isset($total_erreurs_format) && $total_erreurs_format==1)
			{
				$message.="une ligne contient au moins une date dont <b>le format est incorrect</b>";

				if($total_erreurs_chrono)
					$message.="<br>- ";
			}
			elseif(isset($total_erreurs_format) && $total_erreurs_format>1)
			{
				$message.="$total_erreurs_format lignes contiennent au moins une date dont <b>le format est incorrect</b>";

				if($total_erreurs_chrono)
					$message.="<br>- ";
			}

			if(isset($total_erreurs_chrono) && $total_erreurs_chrono==1)
				$message.="une ligne contient des dates ne respectant pas <b>l'ordre chronologique</b>";
			elseif(isset($total_erreurs_chrono) && $total_erreurs_chrono>1)
				$message.="$total_erreurs_chrono lignes contiennent des dates ne respectant pas <b>l'ordre chronologique</b>";

			if(isset($total_collisions) && $total_collisions==1)
            $message.="une ligne contient des dates recouvrant une ou plusieurs autres sessions.";
         elseif(isset($total_collisions) && $total_collisions>1)
         	$message.="$total_collisions lignes contiennent des dates recouvrant une ou plusieurs autres sessions.";

			message("$message", $__ERREUR);
		}
	?>

	<table align='center'>
	<tr>
		<td class='td-gauche fond_menu2' colspan='2'>
			<font class='Texte_menu2'>
				<strong>
					Modifier toutes les dates (prioritaires sur la sélection individuelle) ...
					<br>Attention : seules les formations "actives" (cases cochées) seront prises en compte.
				</strong>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<font class='Texte_menu'><b>ouverture : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				Jour :&nbsp;<input type='text' name='jour_ouverture_all' value='' size='4' maxlength='2'>&nbsp;
				Mois :&nbsp;<input type='text' name='mois_ouverture_all' value='' size='4' maxlength='2'>&nbsp;
				Année :&nbsp;<input type='text' name='annee_ouverture_all' value='' maxlength="4" size="6" >
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<font class='Texte_menu'><b>Fermeture : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				Jour :&nbsp;<input type='text' name='jour_fermeture_all' value='' size='4' maxlength='2'>&nbsp;
				Mois :&nbsp;<input type='text' name='mois_fermeture_all' value='' size='4' maxlength='2'>&nbsp;
				Année :&nbsp;<input type='text' name='annee_fermeture_all' value='' maxlength="4" size="6" >
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu'>
			<font class='Texte_menu'><b>Limite de réception des dossiers papiers : </b></font>
		</td>
		<td class='td-droite fond_menu'>
			<font class='Texte_menu'>
				Jour :&nbsp;<input type='text' name='jour_reception_all' value='' size='4' maxlength='2'>&nbsp;
				Mois :&nbsp;<input type='text' name='mois_reception_all' value='' size='4' maxlength='2'>&nbsp;
				Année :&nbsp;<input type='text' name='annee_reception_all' value='' maxlength="4" size="6" >
			</font>
		</td>
	</tr>
	</table>
	<br>

	<?php
		message("N'oubliez pas de vérifier que la case \"<strong>Active ?</strong>\" est bien cochée lorsque vous ajoutez des dates
					pour une formation !", $__WARNING);
	?>

	<table cellpadding='0' cellspacing='0' border='0' align='center'>
	<tr>
		<td>

		<?php
			$old_annee="===="; // on initialise à n'importe quoi (sauf vide)
			$old_propspec_id="";
			$old_mention="--";
         $old_groupe_id="--";

			for($i=0; $i<$rows; $i++)
			{
				list($propspec_id, $annee, $spec_nom, $finalite, $mention, $groupe_id)=db_fetch_row($result, $i);

            if($annee=="")
					$annee="Années particulières";

				$nom_finalite=$tab_finalite[$finalite];

				if($groupe_id!="-1" && $groupe_id!=$old_groupe)
            {
               $res_groupes=db_query($dbr, "SELECT $_DBC_groupes_spec_nom, $_DBC_groupes_spec_dates_communes, count(*) 
                                               FROM $_DB_groupes_spec , $_DB_propspec
                                            WHERE $_DBC_propspec_id=$_DBC_groupes_spec_propspec_id
                                            AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
                                            AND $_DBC_propspec_active='1'
                                            AND $_DBC_groupes_spec_groupe='$groupe_id' 
                                               GROUP BY $_DBC_groupes_spec_nom, $_DBC_groupes_spec_dates_communes");
                 
               list($nom_groupe, $dates_communes, $nb_groupes)=db_fetch_row($res_groupes, 0);
                 
               if($nb_groupes!="")
               {
                  if($dates_communes=='t')
                  {
                     if($nom_groupe=="")
                        $nom_groupe="<i>inconnu</i>";
                       
                     $rowspan_count=$nb_groupes;
                     $rowspan="";
                              
                     $nom_formation="Groupe \"$nom_groupe\"";
                                
                     // $colspan="colspan='2'";
                     $colspan="";
                     // $colspan_annee++;
                    
                     $group_by="GROUP BY $_DBC_session_id, $_DBC_session_ouverture, $_DBC_session_fermeture, $_DBC_session_reception, $_DBC_session_periode";
                  }
                  else
                  {
                     $colspan=$group_by="";
                     $nom_formation="$spec_nom $nom_finalite";
                  }
               }
               else
                  $colspan=$group_by="";
            }
            else
            {
               $colspan="";
               $nom_formation="$spec_nom $nom_finalite";
               $group_by="";
            }

				if(($groupe_id=="-1" || $dates_communes=="f") && array_key_exists($propspec_id, $_SESSION["all_sessions"]) && array_key_exists($n_intervalle, $_SESSION["all_sessions"][$propspec_id]))
				{
					$ouverture=$_SESSION["all_sessions"][$propspec_id][$n_intervalle]["ouv"];
					$fermeture=$_SESSION["all_sessions"][$propspec_id][$n_intervalle]["ferm"];
					$reception=$_SESSION["all_sessions"][$propspec_id][$n_intervalle]["rec"];
					$session_id=$_SESSION["all_sessions"][$propspec_id][$n_intervalle]["s_id"];
				}
				elseif(($groupe_id!="-1" && $dates_communes=="t") && array_key_exists($groupe_id, $_SESSION["all_sessions_groups"]) && array_key_exists($n_intervalle, $_SESSION["all_sessions_groups"][$groupe_id]))
				{
					$ouverture=$_SESSION["all_sessions_groups"][$groupe_id][$n_intervalle]["ouv"];
					$fermeture=$_SESSION["all_sessions_groups"][$groupe_id][$n_intervalle]["ferm"];
					$reception=$_SESSION["all_sessions_groups"][$groupe_id][$n_intervalle]["rec"];
					$session_id=$_SESSION["all_sessions_groups"][$groupe_id][$n_intervalle]["s_id"];
				}
				else
				{
					$ouverture=$fermeture=$reception=0;

					// Sélection de l'identifiant de la nouvelle session pour cette formation (ou ce groupe)
					$res_session_id=db_query($dbr, "SELECT max($_DBC_session_id)+1 FROM $_DB_session
																WHERE $_DBC_session_propspec_id='$propspec_id'");

					list($session_id)=db_fetch_row($res_session_id, 0);

					if($session_id=='')
						$session_id=1;

					db_free_result($res_session_id);
				}
            
            if($ouverture!="0")
            {
   				$date_ouv_jour=date("j", $ouverture);
   				$date_ouv_mois=date("n", $ouverture);
   				$date_ouv_annee=date("Y", $ouverture);
   				
   				if(strlen($date_ouv_jour)==1) $date_ouv_jour="0" . $date_ouv_jour;
               if(strlen($date_ouv_mois)==1) $date_ouv_mois="0" . $date_ouv_mois;
			   }
			  
			   if($fermeture!="0")
            {
   				$date_ferm_jour=date("j", $fermeture);
   				$date_ferm_mois=date("n", $fermeture);
   				$date_ferm_annee=date("Y", $fermeture);
   				
   				if(strlen($date_ferm_jour)==1) $date_ferm_jour="0" . $date_ferm_jour;
				   if(strlen($date_ferm_mois)==1) $date_ferm_mois="0" . $date_ferm_mois;
            }

            if($reception!="0")            
            {
   				$date_rec_jour=date("j", $reception);
   				$date_rec_mois=date("n", $reception);
   				$date_rec_annee=date("Y", $reception);

   				if(strlen($date_rec_jour)==1) $date_rec_jour="0" . $date_rec_jour;
   				if(strlen($date_rec_mois)==1) $date_rec_mois="0" . $date_rec_mois;
            }
            
				$fond=isset($array_fond[$propspec_id]) ? $array_fond[$propspec_id] : "fond_menu";
				
				if(isset($array_fond[$propspec_id]))
				  $fond=$array_fond[$propspec_id];
				elseif(isset($array_fond_groupe[$groupe_id]))
				  $fond=$array_fond_groupe[$groupe_id];
				else
				  $fond="fond_menu";

				if($ouverture==0 || $fermeture==0 || $reception==0)
					$checked=$date_ouv_jour=$date_ouv_mois=$date_ouv_annee=$date_ferm_jour=$date_ferm_mois=$date_ferm_annee=$date_rec_jour=$date_rec_mois=$date_rec_annee="";
				else
					$checked="checked='1'";

				if(($groupe_id=="-1" || $dates_communes=="f") && isset($array_cur_values) && array_key_exists($propspec_id, $array_cur_values))
				{
					$date_ouv_jour=isset($array_cur_values[$propspec_id]["jour_ouv"]) ? $array_cur_values[$propspec_id]["jour_ouv"] : $date_ouv_jour;
					$date_ouv_mois=isset($array_cur_values[$propspec_id]["mois_ouv"]) ? $array_cur_values[$propspec_id]["mois_ouv"] : $date_ouv_mois;
					$date_ouv_annee=isset($array_cur_values[$propspec_id]["annee_ouv"]) ? $array_cur_values[$propspec_id]["annee_ouv"] : $date_ouv_annee;

					$date_ferm_jour=isset($array_cur_values[$propspec_id]["jour_ferm"]) ? $array_cur_values[$propspec_id]["jour_ferm"] : $date_ferm_jour;
					$date_ferm_mois=isset($array_cur_values[$propspec_id]["mois_ferm"]) ? $array_cur_values[$propspec_id]["mois_ferm"] : $date_ferm_mois;
					$date_ferm_annee=isset($array_cur_values[$propspec_id]["annee_ferm"]) ? $array_cur_values[$propspec_id]["annee_ferm"] : $date_ferm_annee;

					$date_rec_jour=isset($array_cur_values[$propspec_id]["jour_rec"]) ? $array_cur_values[$propspec_id]["jour_rec"] : $date_rec_jour;
					$date_rec_mois=isset($array_cur_values[$propspec_id]["mois_rec"]) ? $array_cur_values[$propspec_id]["mois_rec"] : $date_rec_mois;
					$date_rec_annee=isset($array_cur_values[$propspec_id]["annee_rec"]) ? $array_cur_values[$propspec_id]["annee_rec"] : $date_rec_annee;

					$checked=(isset($array_cur_values[$propspec_id]["active"]) && $array_cur_values[$propspec_id]["active"]==1) ? "checked='1'" : ""; 
				}
				elseif($groupe_id!="-1" && isset($array_cur_values_groupe) && array_key_exists($groupe_id, $array_cur_values_groupe))
				{
					$date_ouv_jour=isset($array_cur_values_groupe[$groupe_id]["jour_ouv"]) ? $array_cur_values_groupe[$groupe_id]["jour_ouv"] : $date_ouv_jour;
					$date_ouv_mois=isset($array_cur_values_groupe[$groupe_id]["mois_ouv"]) ? $array_cur_values_groupe[$groupe_id]["mois_ouv"] : $date_ouv_mois;
					$date_ouv_annee=isset($array_cur_values_groupe[$groupe_id]["annee_ouv"]) ? $array_cur_values_groupe[$groupe_id]["annee_ouv"] : $date_ouv_annee;

					$date_ferm_jour=isset($array_cur_values_groupe[$groupe_id]["jour_ferm"]) ? $array_cur_values_groupe[$groupe_id]["jour_ferm"] : $date_ferm_jour;
					$date_ferm_mois=isset($array_cur_values_groupe[$groupe_id]["mois_ferm"]) ? $array_cur_values_groupe[$groupe_id]["mois_ferm"] : $date_ferm_mois;
					$date_ferm_annee=isset($array_cur_values_groupe[$groupe_id]["annee_ferm"]) ? $array_cur_values_groupe[$groupe_id]["annee_ferm"] : $date_ferm_annee;

					$date_rec_jour=isset($array_cur_values_groupe[$groupe_id]["jour_rec"]) ? $array_cur_values_groupe[$groupe_id]["jour_rec"] : $date_rec_jour;
					$date_rec_mois=isset($array_cur_values_groupe[$groupe_id]["mois_rec"]) ? $array_cur_values_groupe[$groupe_id]["mois_rec"] : $date_rec_mois;
					$date_rec_annee=isset($array_cur_values_groupe[$groupe_id]["annee_rec"]) ? $array_cur_values_groupe[$groupe_id]["annee_rec"] : $date_rec_annee;

					$checked=(isset($array_cur_values_groupe[$groupe_id]["active"]) && $array_cur_values_groupe[$groupe_id]["active"]==1) ? "checked='1'" : ""; 
				}

				if($annee!=$old_annee)
				{
					if($i!=0)
						print("</table>
									<br clear='all'><br>\n");

					print("<table style='width:100%; padding-bottom:20px;'>
							<tr>
								<td class='fond_menu2' align='center' colspan='5' style='padding:4px 20px 4px 20px;'>
									<font class='Texte_menu2'><b>$annee</b></font>
								</td>
							</tr>
							<tr>
								<td class='fond_menu2' style='padding:4px 20px 4px 20px; white-space:nowrap;'>
									<font class='Texte_menu2'><b>Active ?</b></font>
								</td>
								<td class='fond_menu2' style='padding:4px 20px 4px 20px;'>
									<font class='Texte_menu2'><b>Formation</b></font>
								</td>
								<td class='fond_menu2' style='padding:4px 20px 4px 20px; text-align:center;'>
									<font class='Texte_menu2'><b>Ouverture (JJ MM AAAA)</b></font>
								</td>
								<td class='fond_menu2' style='padding:4px 20px 4px 20px; text-align:center;'>
									<font class='Texte_menu2'><b>Fermeture (JJ MM AAAA)</b></font>
								</td>
								<td class='fond_menu2' style='padding:4px 20px 4px 20px; text-align:center;'>
									<font class='Texte_menu2'><b>Réception (JJ MM AAAA)</b></font>
								</td>
							</tr>
							<tr>
								<td class='fond_menu2' colspan='5'>
									<font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
								</td>");

					$old_annee=$annee;
					$old_mention='--';
					$first_spec=1;
				}
				else
					$first_spec=0;

				if($mention!=$old_mention)
				{
					if(!$first_spec)
						print("<tr>
									<td class='fond_menu2' colspan='5'>
										<font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;$mention</b></font>
									</td>
								</tr>\n");

					$old_mention=$mention;
				}

            if($groupe_id=="-1" || $groupe_id!=$old_groupe_id || ($groupe_id==$old_groupe_id && $dates_communes=='f'))
            {
               $name=$groupe_id!="-1" && $dates_communes=='t' ? "g_id[$groupe_id]" : "s_id[$propspec_id]";
					$activ=$groupe_id!="-1" && $dates_communes=='t' ? "g_activ[$groupe_id]" : "activ[$propspec_id]";
               $jour_ouv=$groupe_id!="-1" && $dates_communes=='t' ? "g_jour_ouv[$groupe_id]" : "jour_ouv[$propspec_id]";
               $mois_ouv=$groupe_id!="-1" && $dates_communes=='t' ? "g_mois_ouv[$groupe_id]" : "mois_ouv[$propspec_id]";
               $annee_ouv=$groupe_id!="-1" && $dates_communes=='t' ? "g_annee_ouv[$groupe_id]" : "annee_ouv[$propspec_id]";
               $jour_ferm=$groupe_id!="-1" && $dates_communes=='t' ? "g_jour_ferm[$groupe_id]" : "jour_ferm[$propspec_id]";
               $mois_ferm=$groupe_id!="-1" && $dates_communes=='t' ? "g_mois_ferm[$groupe_id]" : "mois_ferm[$propspec_id]";
               $annee_ferm=$groupe_id!="-1" && $dates_communes=='t' ? "g_annee_ferm[$groupe_id]" : "annee_ferm[$propspec_id]";
               $jour_rec=$groupe_id!="-1" && $dates_communes=='t' ? "g_jour_rec[$groupe_id]" : "jour_rec[$propspec_id]";
               $mois_rec=$groupe_id!="-1" && $dates_communes=='t' ? "g_mois_rec[$groupe_id]" : "mois_rec[$propspec_id]";
               $annee_rec=$groupe_id!="-1" && $dates_communes=='t' ? "g_annee_rec[$groupe_id]" : "annee_rec[$propspec_id]";
               
   				print("<tr>
   							<td class='td-gauche $fond' style='text-align:center; width:2%;'>
   								<input type='hidden' name='$name' value='$session_id'>
   								<input type='checkbox' name='$activ' value='1' $checked>
   							</td>
   							<td class='td-milieu $fond'>
   								<font class='Texte_menu'><b>$nom_formation</b></font>
   							</td>
   							<td class='td-milieu $fond' style='text-align:center;'>
   								<input type='text' name='$jour_ouv' value='$date_ouv_jour' size='2' maxlength='2'>&nbsp;
   								<input type='text' name='$mois_ouv' value='$date_ouv_mois' size='2' maxlength='2'>&nbsp;
   								<input type='text' name='$annee_ouv' value='$date_ouv_annee' maxlength='4' size='4'>
   							</td>
   							<td class='td-milieu $fond' style='text-align:center;'>
   								<input type='text' name='$jour_ferm' value='$date_ferm_jour' size='2' maxlength='2'>&nbsp;
   								<input type='text' name='$mois_ferm' value='$date_ferm_mois' size='2' maxlength='2'>&nbsp;
   								<input type='text' name='$annee_ferm' value='$date_ferm_annee' maxlength='4' size='4'>
   							</td>
   							<td class='td-milieu $fond' style='text-align:center;'>
   								<input type='text' name='$jour_rec' value='$date_rec_jour' size='2' maxlength='2'>&nbsp;
   								<input type='text' name='$mois_rec' value='$date_rec_mois' size='2' maxlength='2'>&nbsp;
   								<input type='text' name='$annee_rec' value='$date_rec_annee' maxlength='4' size='4'>
   							</td>
   						</tr>\n");
				}

            $old_groupe_id=$groupe_id;

         }
         
			db_free_result($result);

			print("</table>\n");
		?>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<?php
			if(isset($succes))
				print("<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");
			else
				print("<a href='index.php' target='_self' class='lien2'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>\n");
		?>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
	</div>
</div>
<?php
	db_close($dbr);
	pied_de_page();
?>
</form>

</body></html>
