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

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(isset($_POST["go"]) || isset($_POST["go_x"]))
	{
		$login=strtolower(trim($_POST["login"]));
		$pass=trim($_POST["current_pass"]);
		$new_pass=trim($_POST["pass"]);
		$new_pass_conf=trim($_POST["conf_pass"]);

		// vérification des champs
		if($pass=="" || $new_pass=="" || $new_pass_conf=="")
			$champs_vides=1;
		else
		{
			// récupération des valeurs courantes et vérification du login
			$dbr=db_connect();
			$result=db_query($dbr,"SELECT $_DBC_acces_id, $_DBC_acces_pass FROM $_DB_acces WHERE $_DBC_acces_login like '$login'");
			$rows=db_num_rows($result);
			if(!$rows)
			{
				$login_existe_pas=1;
				db_free_result($result);
				db_close($dbr);
			}
			else
			{
				list($current_id,$current_pass)=db_fetch_row($result,0);
				// vérification de la validité du pass actuel
				if(md5($pass)!=$current_pass)
				{
					$old_pass_error=1;
					db_free_result($result);
					db_close($dbr);
				}
				else
				{
					// vérification des nouveaux pass
					if($new_pass!=$new_pass_conf)
						$pass_dont_match=1;
					else
					{
						$md5_pass=md5($new_pass);

						// on peut mettre à jour
						db_query($dbr,"UPDATE $_DB_acces SET $_DBU_acces_pass='$md5_pass' WHERE $_DBU_acces_id='$current_id'");
						db_close($dbr);
						$succes=1;
					}
				}
			}
		}
	}

	en_tete_simple();
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("Changer son mot de passe", "password2_32x32_fond.png", 15, "C");

		if(isset($champs_vides))
			message("Erreur : Tous les champs doivent être remplis.", $__ERREUR);

		if(isset($login_existe_pas))
			message("Erreur : identifiant incorrect.", $__ERREUR);

		if(isset($old_pass_error))
			message("Erreur : mot de passe actuel incorrect.", $__ERREUR);

		if(isset($pass_dont_match))
			message("Erreur : les nouveaux mots de passe sont différents.", $__ERREUR);

		if(isset($succes) && $succes==1)
			message("Mot de passe modifié avec succès.", $__SUCCES);

		print("<form action='$php_self' method='POST' name='form1'>
					<input type='hidden' name='act' value='1'>\n");
	?>

	<table border="0" cellpadding="4" align='center' valign="top">
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'>Identifiant (en minuscules) : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='text' name='login' value='<?php if(isset($login)) print($login); ?>' size='20'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'>Votre mot de passe actuel : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='password' name='current_pass' value='' size='40'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'>Votre nouveau mot de passe : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='password' name='pass' value='' size='40'>
		</td>
	</tr>
	<tr>
		<td class='td-gauche fond_menu2'>
			<font class='Texte_menu2'>Confirmation du nouveau mot de passe : </font>
		</td>
		<td class='td-droite fond_menu'>
			<input type='password' name='conf_pass' value='' size='40'>
		</td>
	</tr>
	</table>

	<div class='centered_icons_box'>
		<?php
			if(isset($succes))
				print("<a href='login.php' target='_self'><img src='$__ICON_DIR/back_32x32_fond.png' alt='Retour' border='0'></a>&nbsp;&nbsp;\n");
			else
				print("<a href='login.php' target='_self'><img src='$__ICON_DIR/button_cancel_32x32_fond.png' alt='Retour' border='0'></a>\n");
		?>
		<input type="image" src="<?php echo "$__ICON_DIR/button_ok_32x32.png"; ?>" alt="Confirmer" name="go" value="Confirmer">
		</form>
	</div>
	
</div>
<?php
	pied_de_page();
?>

</body></html>

