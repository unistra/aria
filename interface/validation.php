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
	session_name("preinsc");
	session_start();

	include "../configuration/aria_config.php";
	include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	if(isset($_SESSION['email']))
	{
		$email=$_SESSION["email"];
		$_SESSION["auth"]=1; // pour ne pas revenir automatiquement à l'index
	}
	else
	{
		session_write_close();
		header("Location:../session.php"); // page d'erreur standard : session expirée
		exit();
	}
	
	en_tete_candidat();
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("Identifiants envoyés !", "idea_32x32_fond.png", 30, "L");

		message("<center>
						<p>Un courriel vient de vous être envoyé à l'adresse \"<strong>$email</strong>\".</p>
						<p>Une fois vos identifiants reçus, vous pourrez vous authentifier via le formulaire de la page précédente.</p>
				 		<p><strong>Conservez bien vos identifiants</strong> car ils vous seront utiles tout au long de la procédure.</p>				 						 		
					</center>", $__INFO);
					
		message("<strong>Rappels</strong> :
		         <br>- n'oubliez pas de vérifier le contenu du dossier <strong>\"Spams\"</strong> (ou <strong>\"Courriers indésirables\"</strong>) de votre messagerie,
		         <br>- les filtres de votre messagerie doivent autoriser les courriels provenant de l'adresse \"$__EMAIL_ADMIN\".", $__WARNING);		         
	?>

	<div class='centered_icons_box'>
		<a href='identification.php' target='_self' class='lien2'>
			<img src='<?php echo "$__ICON_DIR/back_32x32.png"; ?>' alt='Retour' border='0'>
		</a>
	</div>
</div>

<?php
	pied_de_page_candidat();
?>

</body>
</html>

