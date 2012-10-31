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

	include "../../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth("$__GESTION_DIR/login.php");

	if($_SESSION['niveau']!=$__LVL_ADMIN)
	{
		header("Location:$__GESTION_DIR/noaccess.php");
		exit();
	}

	$dbr=db_connect();

	// retour étape précédente : nettoyage de variables
	if(isset($_GET["e"]) && $_GET["e"]==1)
		unset($_SESSION["droits_comp_id"]);

	if(isset($_GET["e"]) && $_GET["e"]==2)
	{
		unset($_SESSION["droits_comp_id"]);
		unset($_SESSION["droits_user_id"]);
	}
	
	if(isset($_POST["modifier"]) || isset($_POST["modifier_x"]))
	{
		$_SESSION["droits_user_id"]=$user_id=$_POST["user_id"];
		$resultat=1;
	}
   elseif(isset($_POST["modifier_recherche"]) || isset($_POST["modifier_recherche_x"]))
	{
		$_SESSION["droits_user_id"]=$user_id=$_POST["user_id_recherche"];
		$resultat=1;
	}
   elseif((isset($_POST["recherche"]) || isset($_POST["recherche_x"])) && trim($_POST["recherche_nom"])!="")
	{
	   $recherche=1;
		$nom_recherche=trim($_POST["recherche_nom"]);
	}
	elseif(isset($_POST["clear_form"]) || isset($_POST["clear_form_x"]))
	{
	}
   else
   {
      unset($_SESSION["resultat_recherche_ldap"]);
      unset($_SESSION["current_recherche_ldap"]);
      unset($_SESSION["source"]);
   }
   
	// Validation de l'accès aux composantes
	if((isset($_POST["valider"]) || isset($_POST["valider_x"])) && isset($_SESSION["droits_user_id"]))
	{
		if(!isset($_POST["comp"]))
		{
			// Aucune composante sélectionnée : suppression des droits
			db_query($dbr,"DELETE FROM $_DB_acces_comp WHERE $_DBC_acces_comp_acces_id='$_SESSION[droits_user_id]'");
			
			// Suppression de tous les droits sur les formations
			db_query($dbr, "DELETE FROM $_DB_droits_formations WHERE $_DBC_droits_formations_acces_id='$_SESSION[droits_user_id]'"); 
		}
		elseif(isset($_SESSION["array_comp"])) // normalement toujours vrai à ce stade
		{
			foreach($_SESSION["array_comp"] as $selected_comp_id => $droits)
			{
				if(in_array($selected_comp_id, $_POST["comp"]) && !$droits)
					db_query($dbr, "INSERT INTO $_DB_acces_comp VALUES ('$_SESSION[droits_user_id]', '$selected_comp_id')");
				elseif(!in_array($selected_comp_id, $_POST["comp"]) && $droits)
				{
					db_query($dbr, "DELETE FROM $_DB_acces_comp WHERE $_DBC_acces_comp_acces_id='$_SESSION[droits_user_id]' 
																			  AND $_DBC_acces_comp_composante_id='$selected_comp_id'");
																			  
					// Suppression des droits sur les formations de cette composante
					db_query($dbr, "DELETE FROM $_DB_droits_formations WHERE $_DBC_droits_formations_acces_id='$_SESSION[droits_user_id]' 
																			  			AND $_DBC_droits_formations_propspec_id IN (SELECT $_DBC_propspec_id FROM $_DB_propspec WHERE $_DBC_propspec_comp_id='$selected_comp_id')");
				}
			}
		}

		db_close($dbr);
		header("Location:$php_self?e=2&succes=1");
		exit;
	}
	
	// Composante passée en paramêtre : on examine les droits de l'utilisateur sur chaque formation de cette dernière
	if(isset($_SESSION["droits_user_id"]) && isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
	{
		if(isset($params["comp_id"]) && ctype_digit($params["comp_id"]) && db_num_rows(db_query($dbr, "SELECT * FROM $_DB_composantes WHERE $_DBC_composantes_id='$params[comp_id]'")))
			$_SESSION["droits_comp_id"]=$params["comp_id"];
	}
	elseif(!isset($_SESSION["droits_user_id"]))
		unset($_SESSION["droits_comp_id"]);
	
	// Validation de l'accès aux formations de la composante sélectionnée
	if((isset($_POST["valider2"]) || isset($_POST["valider2_x"])) && isset($_SESSION["droits_user_id"]) && isset($_SESSION["droits_comp_id"]))
	{
		// Si l'utilisateur a accès à au moins une formation, il faut lui donner l'accès à la composante
		// Ce témoin va permettre de vérifier cet accès et de l'ajouter si besoin
		$droits_comp=0;
		
		if(!isset($_POST["propspec"]))
			db_query($dbr,"DELETE FROM $_DB_droits_formations WHERE $_DBC_droits_formations_acces_id='$_SESSION[droits_user_id]'");
		elseif(isset($_SESSION["all_propspec"])) // normalement toujours vrai à ce stade
		{
			foreach($_SESSION["all_propspec"] as $selected_propspec_id => $droits)
			{
				if(in_array($selected_propspec_id, $_POST["propspec"]) && !$droits)
				{
					db_query($dbr, "INSERT INTO $_DB_droits_formations VALUES ('$_SESSION[droits_user_id]', '$selected_propspec_id','1')");
					$droits_comp=1;
				}
				elseif(!in_array($selected_comp_id, $_POST["propspec"]) && $droits)
					db_query($dbr, "DELETE FROM $_DB_droits_formations WHERE $_DBC_droits_formations_acces_id='$_SESSION[droits_user_id]' 
																			  AND $_DBC_droits_formations_propspec_id='$selected_propspec_id'");
			}
			
			if(1==$droits_comp)
			{
				if(!db_num_rows(db_query($dbr, "SELECT * FROM $_DB_acces_comp WHERE $_DBC_acces_comp_acces_id='$_SESSION[droits_user_id]' 
																								  AND $_DBC_acces_comp_composante_id='$_SESSION[droits_comp_id]'")))
					db_query($dbr, "INSERT INTO $_DB_acces_comp VALUES ('$_SESSION[droits_user_id]', '$_SESSION[droits_comp_id]')");
			}
		}
				
		db_close($dbr);
		header("Location:$php_self?e=1&succes=1");
		exit;
	}
	
	// Composante passée en paramêtre : on examine les droits de l'utilisateur sur chaque formation de cette dernière
	if(isset($_SESSION["droits_user_id"]) && isset($_GET["p"]) && -1!=($params=get_params($_GET['p'])))
	{
		if(isset($params["comp_id"]) && ctype_digit($params["comp_id"]) && db_num_rows(db_query($dbr, "SELECT * FROM $_DB_composantes WHERE $_DBC_composantes_id='$params[comp_id]'")))
			$_SESSION["droits_comp_id"]=$params["comp_id"];
	}
	elseif(!isset($_SESSION["droits_user_id"]))
		unset($_SESSION["droits_comp_id"]);
	
	
	
   
	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main'>
	<?php
		titre_page_icone("Modifier les droits d'un utilisateur", "randr_32x32_fond.png", 15, "L");

		if(isset($id_existe_pas))
			message("Erreur : l'identifiant demandé est incorrect (problème de cohérence de la base)", $__ERREUR);

		if(isset($_GET["succes"]) && $_GET["succes"]==1)
			message("Droits d'accès modifiés avec succès.", $__SUCCES);

      if(!isset($recherche))
			   $nom_recherche="";
		
		if(!isset($_SESSION["droits_user_id"])) // Choix de l'utilisateur à modifier
		{	
		   unset($_SESSION["array_comp"]);
		   
         print("<form action='$php_self' method='POST' name='form1'>
         
                <table cellpadding='4' align='center'>
                <tr>
                  <td class='fond_menu2' align='right'>
                     <font class='Texte_menu2' style='font-weight:bold;'>Recherche par nom / identifiant : </font>
                  </td>
                  <td class='fond_menu'>
						   <input type='text' name='recherche_nom' value=\"".stripslashes($nom_recherche)."\" maxlength='30' size='30'>
						</td>
                  <td class='fond_menu' style='text-align:center;'>");
						
			if(isset($nom_recherche) && trim($nom_recherche!=""))
			   print("<input type='image' src='$__ICON_DIR/cancel_16x16_menu.png' alt='Effacer le formulaire' name='clear_form' value='Effacer le formulaire'>");
						
		   print("     <input type='image' src='$__ICON_DIR/forward_16x16_menu.png' alt='Rechercher' name='recherche' value='Rechercher'>
						</td>
				   </tr>");

			$critere_recherche=isset($nom_recherche) && trim($nom_recherche!="") ? "AND ($_DBC_acces_nom ILIKE '$nom_recherche"."%"."' OR $_DBC_acces_login ILIKE '$nom_recherche"."%"."')" : "";
			
			if(isset($nom_recherche) && trim($nom_recherche!=""))
			{		
   		   $result_recherche=db_query($dbr, "(SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                                      $_DBC_acces_prenom as aprenom, $_DBC_acces_login, '0' as cnom
                                                  FROM $_DB_acces
                                               WHERE $_DBC_acces_niveau IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]')
                                               AND ($_DBC_acces_nom ILIKE '$nom_recherche"."%"."' OR $_DBC_acces_login ILIKE '$nom_recherche"."%"."')
                                              )
                                             UNION   
                                                (SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                                        $_DBC_acces_prenom as aprenom, $_DBC_acces_login, $_DBC_composantes_nom as cnom
                                                   FROM $_DB_acces, $_DB_composantes
                                                WHERE $_DBC_acces_composante_id=$_DBC_composantes_id
                                                AND $_DBC_acces_niveau NOT IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]')
                                                AND ($_DBC_acces_nom ILIKE '$nom_recherche"."%"."' OR $_DBC_acces_login ILIKE '$nom_recherche"."%"."'))
                                             UNION
                                                (SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                                        $_DBC_acces_prenom as aprenom, $_DBC_acces_login, $_DBC_composantes_nom as cnom
                                                   FROM $_DB_acces, $_DB_acces_comp, $_DB_composantes
                                                WHERE $_DBC_acces_comp_composante_id=$_DBC_composantes_id
                                                AND $_DBC_acces_comp_acces_id=$_DBC_acces_id
                                                AND $_DBC_acces_niveau NOT IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]')
                                                AND ($_DBC_acces_nom ILIKE '$nom_recherche"."%"."' OR $_DBC_acces_login ILIKE '$nom_recherche"."%"."'))
                                             ORDER BY cnom, aniveau DESC, anom, aprenom");
                              
            $rows_recherche=db_num_rows($result_recherche);
         }

         $result=db_query($dbr, "(SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                         $_DBC_acces_prenom as aprenom, $_DBC_acces_login, '0' as cnom
                                    FROM $_DB_acces
                                    WHERE $_DBC_acces_niveau IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]')
                                 )
                              UNION   
                                 (SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                         $_DBC_acces_prenom as aprenom, $_DBC_acces_login, $_DBC_composantes_nom as cnom
                                    FROM $_DB_acces, $_DB_composantes
                                 WHERE $_DBC_acces_composante_id=$_DBC_composantes_id
                                 AND $_DBC_acces_niveau NOT IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]'))
                              UNION
                                 (SELECT $_DBC_acces_id, $_DBC_acces_niveau as aniveau, $_DBC_acces_nom as anom,
                                         $_DBC_acces_prenom as aprenom, $_DBC_acces_login, $_DBC_composantes_nom as cnom
                                    FROM $_DB_acces, $_DB_acces_comp, $_DB_composantes
                                 WHERE $_DBC_acces_comp_composante_id=$_DBC_composantes_id
                                 AND $_DBC_acces_comp_acces_id=$_DBC_acces_id
                                 AND $_DBC_acces_niveau NOT IN ('$GLOBALS[__LVL_ADMIN]','$GLOBALS[__LVL_SUPPORT]','$GLOBALS[__LVL_SUPER_RESP]'))
                              ORDER BY cnom, aniveau DESC, anom, aprenom");
         
         $rows=db_num_rows($result);
			
			if(isset($recherche))
         {
			   print("<tr>
                      <td class='fond_menu2' align='right'>
                         <font class='Texte_menu2' style='font-weight:bold;'>Résultat de la recherche : </font>
                      </td>
                      <td class='fond_menu' colspan='2'>");

   			if(!$rows_recherche)
   			   print("<font class='Texte_menu'>Aucun utilisateur ne correspond à votre recherche</font>");
   			else
   			{
               print("<select name='user_id_recherche' size='1'>
                        <option value=''></option>\n");
   
               $old_comp="--";
               $old_niveau="";
   
               for($i=0; $i<$rows_recherche; $i++)
               {
                  list($user_id, $login_niveau, $login_nom,$login_prenom,$login,$comp_nom)=db_fetch_row($result_recherche,$i);
   
                  if($comp_nom!=$old_comp)
                  {
                     if($i!=0)
                        print("</optgroup>
                                 <option value='' label='' disabled></option>\n");
                     if($comp_nom=="0")
                        print("<optgroup label='==== Administrateurs, support et accès étendus ===='>\n");
                     else
                        print("<optgroup label='==== ".htmlspecialchars($comp_nom, ENT_QUOTES)." ===='>\n");
   
                     $old_comp=$comp_nom;
                     $old_niveau="";
                  }
               
                  if($login_niveau!=$old_niveau)
                  {
                    if($i!=0)
                       print("</optgroup>
                              <option value='' label='' disabled></option>\n");
               
                    print("<optgroup label='".htmlspecialchars(stripslashes($GLOBALS["tab_niveau"]["$login_niveau"]), ENT_QUOTES)."'></optgroup>\n");
               
                    $old_niveau=$login_niveau;
                  }
   
                  print("<option value='$user_id'>" . htmlspecialchars("$login_nom $login_prenom", ENT_QUOTES) . "</option>\n");
               }
   
               print("</optgroup>
                     </select>
                     
                     <input type='image' class='icone' src='$__ICON_DIR/edit_16x16_menu.png' alt='Modifier' name='modifier_recherche' value='Modifier' title='[Modifier un utilisateur]'>");
   		   }
		   
		      print("</td>
		          </tr>\n");
		   
         }
         
         print("<tr>
                   <td colspan='3' class='fond_page' height='20'></td>
                </tr>
                <tr>
                   <td class='fond_menu2' align='right'>
                      <font class='Texte_menu2' style='font-weight:bold;'>Liste complète des utilisateurs : </font>
                   </td>
                   <td class='fond_menu' colspan='2'>

                   <select name='user_id' size='1'>
                     <option value=''></option>\n");

         $old_comp="--";
         $old_niveau="";

         for($i=0; $i<$rows; $i++)
         {
            list($user_id, $login_niveau, $login_nom,$login_prenom,$login,$comp_nom)=db_fetch_row($result,$i);

            if($comp_nom!=$old_comp)
            {
               if($i!=0)
                  print("</optgroup>
                              <option value='' label='' disabled></option>\n");
               if($comp_nom=="0")
                  print("<optgroup label='==== Administrateurs, support et accès étendus ===='>\n");
               else
                  print("<optgroup label='==== ".htmlspecialchars($comp_nom, ENT_QUOTES)." ===='>\n");

               $old_comp=$comp_nom;
               $old_niveau="";
            }
            
            if($login_niveau!=$old_niveau)
            {
               if($i!=0)
                  print("</optgroup>
                         <option value='' label='' disabled></option>\n");
            
               print("<optgroup label='".htmlspecialchars(stripslashes($GLOBALS["tab_niveau"]["$login_niveau"]), ENT_QUOTES)."'></optgroup>\n");
            
               $old_niveau=$login_niveau;
            }

            print("<option value='$user_id'>" . htmlspecialchars("$login_nom $login_prenom", ENT_QUOTES) . "</option>\n");
         }

         print("</optgroup>
            </select>
            
            <input type='image' class='icone' src='$__ICON_DIR/edit_16x16_menu.png' alt='Modifier' name='modifier' value='Modifier' title='[Modifier un utilisateur]'>
            
            </td>
         </tr>         
       
         </table>

         <div class='centered_icons_box'>
            <a href='index.php' target='_self' class='lien_bleu_12'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>\n");

         print("</form>
               </div>
               <script language='javascript'>
                  document.form1.id.focus()
               </script>\n");

/*

		if(!isset($_SESSION["droits_user_id"])) // Choix de l'utilisateur à modifier
		{
			// au cas où...
			unset($_SESSION["array_comp"]);

			$result=db_query($dbr, "(SELECT $_DBC_acces_id, $_DBC_acces_niveau, $_DBC_acces_nom as anom,
													  $_DBC_acces_prenom as aprenom, $_DBC_acces_login, $_DBC_composantes_nom as cnom
												FROM $_DB_acces, $_DB_composantes
											WHERE $_DBC_acces_composante_id=$_DBC_composantes_id)
										UNION
											(SELECT $_DBC_acces_id, $_DBC_acces_niveau, $_DBC_acces_nom as anom,
													  $_DBC_acces_prenom as aprenom, $_DBC_acces_login, $_DBC_composantes_nom as cnom
												FROM $_DB_acces, $_DB_acces_comp, $_DB_composantes
											WHERE $_DBC_acces_comp_composante_id=$_DBC_composantes_id
											AND $_DBC_acces_comp_acces_id=$_DBC_acces_id)
										ORDER BY cnom, anom, aprenom");

			$rows=db_num_rows($result);

			print("<form action='$php_self' method='POST' name='form1'>

					 <table cellpadding='4' cellspacing='0' border='0' align='center'>
					 <tr>
						<td class='fond_menu2' align='right'>
							<font class='Texte_menu2' style='font-weight:bold;'>Utilisateur : </font>
						</td>
						<td class='fond_menu'>
							<select name='user_id' size='1'>
								<option value=''></option>\n");

			$old_comp="--";

			for($i=0; $i<$rows; $i++)
			{
				list($login_id, $login_niveau, $login_nom,$login_prenom,$login,$comp_nom)=db_fetch_row($result,$i);

				if($comp_nom!=$old_comp)
				{
					if($i!=0)
						print("</optgroup>
									<option value='' label='' disabled></option>\n");

					print("<optgroup label='" . htmlspecialchars($comp_nom, ENT_QUOTES) . "'>\n");

					$old_comp=$comp_nom;
				}

				// Affichage du niveau (entre crochets) - A conserver ?
				// $menu_niveau=$tab_niveau_menu[$login_niveau];

				print("<option value='$login_id'>" . htmlspecialchars("$login_nom $login_prenom", ENT_QUOTES) . "</option>\n");
			}

			print("		</optgroup>
						</select>
						</td>
					</tr>
					</table>

					<div class='centered_icons_box'>
						<a href='index.php' target='_self' class='lien_bleu_12'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>
						<input type='image' src='$__ICON_DIR/forward_32x32_fond.png' alt='Modifier' name='modifier' value='Modifier'>
						</form>
					</div>

					<script language='javascript'>
						document.form1.user_id.focus()
					</script>\n");
*/					
		}
		elseif(isset($_SESSION["droits_user_id"]) && !isset($_SESSION["droits_comp_id"]))
		{
			$result=db_query($dbr,"SELECT $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_niveau, $_DBC_acces_composante_id, $_DBC_composantes_nom,
													$_DBC_universites_nom
										     FROM $_DB_acces, $_DB_composantes, $_DB_universites
										  WHERE $_DBC_acces_id='$_SESSION[droits_user_id]'
										  AND $_DBC_acces_composante_id=$_DBC_composantes_id
										  AND $_DBC_composantes_univ_id=$_DBC_universites_id");

			list($current_nom,$current_prenom, $current_niveau,$composante_id, $composante_nom, $univ_nom)=db_fetch_row($result,0);
			
			$_SESSION["droits_user_nom"]=$current_nom;
			$_SESSION["droits_user_prenom"]=$current_prenom;
			$_SESSION["droits_user_niveau"]=$tab_niveau["$current_niveau"];
			
			db_free_result($result);

			print("<form action='$php_self' method='POST' name='form1'>
						<input type='hidden' name='user_id' value='$user_id'>\n");
	?>
	<table align='center'>
	<tr>
		<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Informations</b>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Utilisateur : </b></font></td>
		<td class='td-droite fond_menu'><font class='Texte_menu'><?php print("$current_prenom $current_nom"); ?></font></td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Composante principale : </b></font></td>
		<td class='td-droite fond_menu'><font class='Texte_menu'><?php print("$univ_nom - $composante_nom"); ?></font></td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Niveau d'accès : </b></font></td>
		<td class='td-droite fond_menu'><font class='Texte_menu'><?php print("$_SESSION[droits_user_niveau]"); ?></font></td>
	</tr>
	<tr>
		<td class='fond_page' colspan='2' style='padding:10px 20px 5px 20px; white-space:normal;'>
			<?php 
				message("- Par défaut, l'utilisateur aura accès à <strong>toutes les formations</strong> des composantes sélectionnées. Pour attribuer les droits formation par formation, cliquez sur le nom de la composante.
							<br>- A partir du niveau <strong>\"Scolarité avec droits supplémentaires\"</strong>, les utilisateurs ont accès au traitement de toutes les formations.", $__INFO);
								
			?>
		</td>
	<tr>
		<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Droits d'accès</b>
			</font>
		</td>
	</tr>
	<?php
		$result=db_query($dbr,"SELECT $_DBC_composantes_univ_id, $_DBC_universites_nom, $_DBC_composantes_id, $_DBC_composantes_nom
											FROM $_DB_composantes, $_DB_universites
										WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
										ORDER BY $_DBC_composantes_univ_id, $_DBC_composantes_nom");

		$rows=db_num_rows($result);

		$old_univ="";

		$_SESSION["array_comp"]=array();

		for($i=0; $i<$rows; $i++)
		{
			list($univ_id, $univ_nom, $comp_id, $comp_nom)=db_fetch_row($result, $i);

			// On teste si l'accès est accordé pour cette composante

			if(db_num_rows(db_query($dbr, "SELECT * FROM $_DB_acces_comp WHERE $_DBC_acces_comp_acces_id='$_SESSION[droits_user_id]' AND $_DBC_acces_comp_composante_id='$comp_id'")))
			{
				$checked="checked='1'";
				$_SESSION["array_comp"][$comp_id]=1;
			}
			else
			{
				$_SESSION["array_comp"][$comp_id]=0;
				$checked="";
			}

			if($univ_id!=$old_univ)
			{
				print("<tr>
							<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
								<font class='Texte_menu2'><b>&#8226; $univ_nom</b></font>
							</td>
						</tr>\n");

				$old_univ=$univ_id;
			}

			$crypt_params=crypt_params("comp_id=$comp_id");

			print("<tr>
						<td class='fond_menu' colspan='2' style='padding:4px 20px 4px 20px;'>
							<input type='checkbox' name='comp[]' value='$comp_id' style='vertical-align:middle;' $checked>&nbsp;&nbsp;<a href='$php_self?p=$crypt_params' class='lien_bleu_12'>$comp_nom</a>
						</td>
					</tr>\n");
		}

		db_free_result($result);
	?>
	</table>

	<div class='centered_icons_box'>
		<a href='<?php echo "$php_self?e=2"; ?>' target='_self' class='lien_bleu_12'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<!-- <a href='index.php' target='_self' class='lien_bleu_12'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a> -->
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
		</form>
	</div>

	<?php
		}
		elseif(isset($_SESSION["droits_user_id"]) && isset($_SESSION["droits_comp_id"])) // Droits de l'utilisateur pour chaque formation de la composante sélectionnée
		{
			$result=db_query($dbr,"SELECT $_DBC_composantes_id, $_DBC_composantes_nom,$_DBC_universites_nom
										     FROM $_DB_composantes, $_DB_universites
										  WHERE $_DBC_composantes_univ_id=$_DBC_universites_id
										  AND $_DBC_composantes_id='$_SESSION[droits_comp_id]'");

			list($composante_id, $composante_nom, $univ_nom)=db_fetch_row($result,0);
			db_free_result($result);

			print("<form action='$php_self' method='POST' name='form1'>
						<input type='hidden' name='user_id' value='$user_id'>\n");
	?>
	<table align='center' style='margin-bottom:20px;'>
	<tr>
		<td class='fond_menu2' colspan='2' style='padding:4px 20px 4px 20px;'>
			<font class='Texte_menu2'>
				<b>&#8226;&nbsp;&nbsp;Informations</b>
			</font>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Utilisateur : </b></font></td>
		<td class='td-droite fond_menu'><font class='Texte_menu'><?php print("$_SESSION[droits_user_prenom] $_SESSION[droits_user_nom]"); ?></font></td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Composante sélectionnée : </b></font></td>
		<td class='td-droite fond_menu'><font class='Texte_menu'><?php print("$univ_nom - $composante_nom"); ?></font></td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'><font class='Texte_menu2'><b>Niveau d'accès : </b></font></td>
		<td class='td-droite fond_menu'><font class='Texte_menu'><?php print("$_SESSION[droits_user_niveau]"); ?></font></td>
	</tr>
	</table>
	
	<?php
	  message("Si aucune case n'est cochée, l'utilisateur aura accès à <strong>toutes les formations</strong>", $__WARNING); 
   ?>

	<?php
		// Nombre max de mentions pour les années de cette composantes (pour affichage)
		$res_mentions=db_query($dbr, "SELECT count(distinct($_DBC_specs_mention_id)) FROM $_DB_specs,$_DB_propspec
                                    WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
                                    AND $_DBC_propspec_comp_id ='$_SESSION[droits_comp_id]'
                                    AND $_DBC_propspec_active='1'
                                       GROUP BY $_DBC_propspec_annee
                                       ORDER BY count DESC");

      list($max_mentions)=db_fetch_row($res_mentions, 0);

      $max_mentions=$max_mentions=="" ? 0 : $max_mentions;

      if($max_mentions>1)
      {
         $colspan_annee=2;
         $colwidth="50%";
      }
      else
      {
         $colspan_annee=1;
         $colwidth="100%";
      }

      db_free_result($res_mentions);

      print("<table align='center'>
             <tr>
                <td class='fond_menu2' colspan='$colspan_annee' style='padding:4px 20px 4px 20px;'>
                   <font class='Texte_menu2'><b>&#8226;&nbsp;&nbsp;Droits d'accès</b></font>
                </td>
             </tr>\n");

      $result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_id, $_DBC_annees_annee, $_DBC_specs_nom_court,
                                     $_DBC_propspec_finalite, $_DBC_mentions_id, $_DBC_mentions_nom
                                FROM $_DB_propspec, $_DB_annees, $_DB_specs, $_DB_mentions
                             WHERE $_DBC_propspec_annee=$_DBC_annees_id
                             AND $_DBC_propspec_id_spec=$_DBC_specs_id
                             AND $_DBC_specs_mention_id=$_DBC_mentions_id
                             AND $_DBC_propspec_active='1'
                             AND $_DBC_propspec_comp_id='$_SESSION[droits_comp_id]'
                             ORDER BY $_DBC_annees_ordre, $_DBC_specs_mention_id, $_DBC_propspec_finalite, $_DBC_specs_nom_court");

      $rows=db_num_rows($result);

      if($rows)
      {
         $old_propspec_id="--"; // on initialise à n'importe quoi (sauf vide)
         $old_annee_id="--"; // idem
         $old_mention="--"; // idem
         $j=0;

         // print("<table align='center'>\n");
			
			$_SESSION["all_propspec"]=array();
			
         for($i=0; $i<$rows; $i++)
         {
            list($propspec_id, $annee_id, $annee, $spec_nom, $finalite, $mention, $mention_nom)=db_fetch_row($result, $i);

				// On teste si l'accès est accordé pour cette formation
				$res_droits=db_query($dbr, "SELECT $_DBC_droits_formations_droits FROM $_DB_droits_formations 
													 WHERE $_DBC_droits_formations_acces_id='$_SESSION[droits_user_id]' AND $_DBC_droits_formations_propspec_id='$propspec_id'");
													 
				if(db_num_rows($res_droits))
				{
					$checked="checked='1'";
					list($_SESSION["all_propspec"][$propspec_id])=db_fetch_row($res_droits,0);
				}
				else
				{
					$_SESSION["all_propspec"][$propspec_id]=0;
					$checked="";
				}

            $nom_finalite=$tab_finalite[$finalite];

            if($annee_id!=$old_annee_id)
            {
               $annee=$annee=="" ? "Années particulières" : $annee;

               if($i) // Le premier résultat du tableau est particulier (i=0)
               {
                  print("</table>
                       </td>\n");

                  if(!$j)
                     print("<td width='$colwidth' valign='top'></td>");

                  print("</tr>
                         <tr>
                            <td class='fond_page' height='10' colspan='$colspan_annee'></td>
                         </tr>\n");
               }

               print("<tr>
                         <td class='fond_menu2' colspan='$colspan_annee' style='padding:4px 20px 4px 20px;'>
                            <font class='Texte_menu2'><b>$annee</b></font>
                         </td>
                      </tr>
                      <tr>
                         <td class='fond_menu2' width='$colwidth' valign='top'>
                            <table width='100%'>
                            <tr>
                               <td colspan='2' class='fond_menu2' align='center' height='20'>
                                  <font class='Texte_menu2'><b>$mention_nom</b></font>
                               </td>
                            </tr>\n");

               $old_mention="$mention";
               $old_annee_id=$annee_id;
               $j=0;
            }

            if($old_mention!=$mention)
            {
               if($i)
                  print("</table>
						    </td>\n");

               if($j)
                  print("</tr>
                         <tr>\n");

               print("<td class='fond_menu2' width='$colwidth' valign='top'>
                         <table width='100%'>
                         <tr>
                            <td class='fond_menu2' colspan='2' height='20' align='center'>
                               <font class='Texte_menu2'><b>$mention_nom</b></font>
                            </td>
                         </tr>\n");

               $j=$j ? 0 : 1;

               $old_mention=$mention;
            }

            print("<tr>
                      <td class='td-gauche fond_menu' style='padding:4px 2px 0px 2px;' width='15'>
                         <input type='checkbox' name='propspec[]' value='$propspec_id' $checked style='vertical-align:middle;'>
                      </td>
                      <td class='td-droite fond_menu' style='padding:4px 2px 0px 2px;'>
                         <font class='Texte_menu'>$spec_nom $nom_finalite</font>
                      </td>
                   </tr>\n");
         }

         db_free_result($result);

         print("</table>
				 </td>\n");

         if(!$j)
            print("<td width='$colwidth' valign='top'></td>\n");

         print("</tr>
                </table>

                <div class='centered_icons_box'>
                   <a href='droits_users.php?r=1' target='_self'><img class='icone' src='$__ICON_DIR/rew_32x32_fond.png' alt='Annuler' border='0'></a>
                   <a href='$php_self?e=1' target='_self'><img class='icone' src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>
                   <input type='image' class='icone' src='$__ICON_DIR/button_ok_32x32_fond.png' alt='Valider' name='valider2' value='Valider'>
                   </form>
                </div>\n");
      }
	?>
	</table>
<!--
	<div class='centered_icons_box'>
		<a href='index.php' target='_self' class='lien_bleu_12'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Annuler' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>" alt="Valider" name="valider" value="Valider">
		</form>
	</div>
-->
	<?php
		}
		
		db_close($dbr);
	?>
</div>
<?php
	pied_de_page();
?>
</body></html>

