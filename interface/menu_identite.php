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
	// Vérifications complémentaires au cas où ce fichier serait appelé directement
	if(!isset($_SESSION["authentifie"]))
	{
		session_write_close();
		header("Location:../index.php");
		exit();
	}

	if(!isset($_SESSION["comp_id"]) || (isset($_SESSION["comp_id"]) && $_SESSION["comp_id"]==""))
	{
		session_write_close();
		header("Location:composantes.php");
		exit();
	}

	print("<div class='centered_box' style='padding-bottom:10px;'>
				<font class='Texte_16'><strong>$_SESSION[onglet] - Vous</strong></font>
			 </div>\n");

	// La valeur de $__lock est déterminée dans le script precandidatures.php (qui inclut tous les menu_*.php)
	if($__lock)		
		message("<center>
						Certaines formations sélectionnées dans cette composante sont verrouillées.
						<br>Merci d'envoyer vos justificatifs le plus rapidement possible !
					</center>", $__WARNING);

	if(isset($_GET["sed"]) && $_GET["sed"]==1)
		message("Informations modifiées avec succès.", $__SUCCES);

	print("<div class='centered_box' style='padding-bottom:10px;'>
				<a href='edit_candidat.php' target='_self' class='lien2'><img class='icone' src='$__ICON_DIR/edit_22x22_fond.png' border='0' alt='Modifier' desc='Modifier'></a>
				<a href='edit_candidat.php' target='_self' class='lien2'>Modifier ces informations</a>
			</div>\n");
?>

<table align='center'>
<tr>
	<td nowrap='true' class='td-gauche fond_menu2'>
		<font class='Texte_menu2'><b>Vous : </b></font>
	</td>
	<td nowrap='true' class='td-droite fond_menu'>
		<font class='Texte_menu'>
		   <strong>
		   <?php 
		      print("$civ_texte $_SESSION[nom] $_SESSION[prenom]"); 
		      
		      if($_SESSION["prenom2"]!="")
		         echo " (".$_SESSION["prenom2"].")";
			?>
			</strong>
		</font>
	</td>
</tr>
<tr>
	<td nowrap='true' class='td-gauche fond_menu2'>
		<font class='Texte_menu2'><b>Nom de naissance</b> : </font>
	</td>
	<td nowrap='true' class='td-droite fond_menu'>
		<font class='Texte_menu'><?php print("$_SESSION[nom_naissance]"); ?></font>
	</td>
</tr>
<tr>
	<td nowrap='true' class='td-gauche fond_menu2'>
		<font class='Texte_menu2'><b>Numéro INE</b> : </font>
	</td>
	<td nowrap='true' class='td-droite fond_menu'>
		<font class='Texte_menu'><?php print("$_SESSION[numero_ine]"); ?></font>
	</td>
</tr>
<tr>
	<td nowrap='true' class='td-gauche fond_menu2'>
		<font class='Texte_menu2'><b><?php echo $ne_le; ?> : </b></font>
	</td>
	<td nowrap='true' class='td-droite fond_menu'>
		<font class='Texte_menu'><?php print("$txt_naissance à $_SESSION[lieu_naissance] - ".stripslashes($_SESSION["pays_naissance"])); ?></font>
	</td>
</tr>
<?php
	if(isset($_SESSION["dpt_naissance"]) && $_SESSION["dpt_naissance"]!="" && isset($_SESSION["nom_departement"]) && $_SESSION["nom_departement"]!="")
	{
?>
<tr>
   <td nowrap='true' class='td-gauche fond_menu2'>
   	<font class='Texte_menu2'><b>Département de naissance : </b></font>
	</td>
   <td nowrap='true' class='td-droite fond_menu'>
		<font class='Texte_menu'><?php print("$_SESSION[dpt_naissance] - $_SESSION[nom_departement]"); ?></font>
	</td>
</tr>
<?php	
	}
?>
<tr>
	<td nowrap='true' class='td-gauche fond_menu2'>
		<font class='Texte_menu2'><b>Nationalité</b> : </font>
	</td>
	<td nowrap='true' class='td-droite fond_menu'>
		<font class='Texte_menu'><?php echo preg_replace("/_/","",$_SESSION['nationalite']); ?></font>
	</td>
</tr>
<tr>
	<td nowrap='true' class='td-gauche fond_menu2'>
		<font class='Texte_menu2'><b>Téléphone</b> : </font>
	</td>
	<td nowrap='true' class='td-droite fond_menu'>
		<font class='Texte_menu'><?php print("$_SESSION[telephone]"); ?></font>
	</td>
</tr>
<tr>
	<td nowrap='true' class='td-gauche fond_menu2'>
		<font class='Texte_menu2'><b>Téléphone portable</b> : </font>
	</td>
	<td nowrap='true' class='td-droite fond_menu'>
		<font class='Texte_menu'><?php print("$_SESSION[telephone_portable]"); ?></font>
	</td>
</tr>
<tr>
	<td nowrap='true' class='td-gauche fond_menu2'>
		<font class='Texte_menu2'><b>Adresse</b> : </font>
	</td>
	<td nowrap='true' class='td-droite fond_menu'>
		<font class='Texte_menu'><?php echo html_entity_decode(stripslashes($adresse)); ?>&nbsp;</font>
	</td>
</tr>
<tr>
	<td nowrap='true' class='td-gauche fond_menu2'>
		<font class='Texte_menu2'><b>Adresse électronique (<i>email</i>)</b> : </font>
	</td>
	<td nowrap='true' class='td-droite fond_menu'>
		<font class='Texte_menu'><?php print("$_SESSION[email]"); ?>&nbsp;</font>
	</td>
</tr>
<tr>
	   <td class='fond_page' colspan='2' height='10'></td>
	</tr>
<tr>
	<td nowrap='true' class='td-gauche fond_menu2'>
		<font class='Texte_menu2'><b>Baccalauréat (ou équivalent)</b> : </font>
	</td>
	<td nowrap='true' class='td-droite fond_menu'>
		<font class='Texte_menu'>
			<?php  
				print("$_SESSION[annee_bac] - $_SESSION[nom_serie_bac]");
			?>
		</font>
	</td>
</tr>
<tr>
	<td nowrap='true' class='td-gauche fond_menu2'>
		<font class='Texte_menu2'>
		   <strong>
		      Inscription antérieure
		      <br />dans cette Université ? :
			</strong>
		</font>
	</td>
	<td nowrap='true' class='td-droite fond_menu'>
		<font class='Texte_menu'>
			<?php  
			   if($_SESSION["deja_inscrit"]==1)
			      print("Oui (".$_SESSION["annee_premiere_inscr"].")");
				else
				   print("Non");
			?>
		</font>
	</td>
</tr>
</table>

<br clear='all'>
