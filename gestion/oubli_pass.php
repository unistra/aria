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

	session_unset();

	if(is_file("../include/vars.php")) include "../include/vars.php";
	else die("Fichier \"include/vars.php\" non trouvé");
	            
	if(is_file("../include/fonctions.php")) include "../include/fonctions.php";
	else die("Fichier \"include/fonctions.php\" non trouvé");
	                  
	if(is_file("../include/db.php")) include "../include/db.php";
	else die("Fichier \"include/db.php\" non trouvé");
	                        
	if(is_file("../include/access_functions.php")) include "../include/access_functions.php";
   else die("Fichier \"include/access_functions.php\" non trouvé");

	$dbr=db_connect();

   // Chargement de la configuration
   $load_config=__get_config($dbr);

   if($load_config===FALSE) // config absente : erreur
      $erreur_config=1;
   elseif($load_config==-1) // paramètre(s) manquant(s) : avertissement
      $warn_config=1;

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(isset($_POST["go_valider"]) || isset($_POST["go_valider_x"]))
	{
		$_SESSION["auth_id"]="-1";
		$_SESSION["auth_prenom"]="";
		$_SESSION["auth_email"]="";
		$_SESSION["comp_id"]="-1";
		$_SESSION["niveau"]="-1";

		$_SESSION["auth_ip"]=$_SERVER['REMOTE_ADDR'];
		$_SESSION["auth_host"]=&gethostbyaddr($_SERVER['REMOTE_ADDR']);

		$_SESSION["auth_nom"]=$user=strtolower($_POST["user"]);

		// $user=strtolower($_POST["user"]);

		$result=db_query($dbr,"SELECT $_DBC_acces_id, $_DBC_acces_nom, $_DBC_acces_prenom, $_DBC_acces_composante_id, $_DBC_acces_niveau, 
																			$_DBC_acces_courriel
															FROM $_DB_acces WHERE $_DBC_acces_login='$user'");

		$rows=db_num_rows($result);

		if(!$rows)
		{
			$user_error=1;
			write_evt($dbr, $__EVT_ID_REINIT, "Echec de la réinitialisation : identifiant '$user' inconnu");
		}
		else
		{
			list($_SESSION["auth_id"],$_SESSION["auth_nom"], $_SESSION["auth_prenom"], $_SESSION["comp_id"], $_SESSION["niveau"], $_SESSION["auth_email"])=db_fetch_row($result,0);

			if(empty($_SESSION["auth_email"]))
			{
				write_evt($dbr, $__EVT_ID_REINIT, "Echec de la réinitialisation pour '$user' : courriel manquant");
				session_destroy();
				
				db_close($dbr);

				die("Erreur :\n\n
							Votre adresse électronique n'est pas renseignée. Merci d'envoyer un courriel <a href='mailto:$GLOBALS[__EMAIL_SUPPORT]'>à cette adresse</a> pour le signaler.
							\n\nVous pourrez reprendre cette procédure par la suite.");
			}

			// génération d'un nouveau mot de passe et changement dans la base
			$new_clear_pass=generate_pass();
			$new_pass=md5($new_clear_pass);

			db_query($dbr,"UPDATE $_DB_acces SET $_DBU_acces_pass='$new_pass' WHERE $_DBU_acces_id='$_SESSION[auth_id]'");

			// envoi d'un mail à l'utilisateur qui a réinitialisé son pass
			$corps_mail="Bonjour, \n\nLa réinitialisation de votre mot de passe a été demandée. Le nouveau mot de passe est \"$new_clear_pass\"
(sans les guillemets).\n\nVous pouvez soit conserver ce mot de passe, soit le changer via la page 'Changer son mot de passe' (vivement conseillé). \n\n
Cordialement,\n\nL'administrateur.";
			mail($_SESSION["auth_email"],"Votre nouveau mot de passe", "$corps_mail") or die("Impossible d'envoyer le courriel. Merci de contacter l'administrateur <a href='mailto:cb@dpt-info.u-strasbg.fr?subject=Précandidature - Adresse électronique' class='lien_bleu_12'>à cette adresse</a>.");
			$mail_sent=1;

			write_evt($dbr, $__EVT_ID_REINIT, "Réinitialisation réussie", "", $_SESSION["auth_id"]);
		}

		db_free_result($result);
	}

	db_close($dbr);

	en_tete_simple();
	menu_sup_simple();
?>
<div class='main'>
	<?php
		titre_page_icone("Oubli du mot de passe", "help2_32x32_fond.png", 15, "C");

		if(isset($user_error))
			message("Identifiant inconnu.", $__ERREUR);

		if(isset($mail_sent))
			message("<center>Un courriel a été envoyé à l'adresse enregistrée sur votre fiche.
						<br>Vous pouvez retenter de vous authentifier <a href='login.php' class='lien2'>sur cette page</a>.</center>", $__SUCCES);

		print("<form action='$php_self' method='POST' name='form1'>\n");
		
		message("<center>Pour obtenir un nouveau mot de passe, il vous suffit d'entrer votre identifiant dans le champ ci-dessous, puis de valider. 
		         <br>Un nouveau mot de passe sera généré aléatoirement et vous sera envoyé par courriel.
		         <br>Vous pourrez soit conserver ce mot de passe, soit le changer via la page \"Changer son mot de passe\" sur la page d'identification.</center>", $__INFO);
	?>

	<table border="0" cellpadding="4" align='center' valign="top">
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'>Identifiant :</font>
		</td>
		<td class='td-droite fond_menu'>
			<input type="text" name="user" value="" size=20 maxlength='30'>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<a href='login.php' target='_self'><img src='<?php echo "$__ICON_DIR/button_cancel_32x32_fond.png"; ?>' alt='Retour' border='0'></a>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32.png"; ?>" alt="Confirmer" name="go_valider" value="Confirmer">
		</form>
	</div>

	<script language="javascript">
		document.form1.user.focus()
	</script>
</div>
<?php
	pied_de_page();
?>
</body>
</html>
