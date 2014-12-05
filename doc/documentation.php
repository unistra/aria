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
	include "$__INCLUDE_DIR_ABS/db.php";
	include "$__INCLUDE_DIR_ABS/access_functions.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;
		
        $dbr=db_connect();
        $load_config=__get_config($dbr);
		
	en_tete_candidat_simple();
	menu_sup_simple();
?>

<div class='main'>
	<div class='centered_box'>
		<font class='Titre'>Documentation</font>
	</div>

	<div style='width:80%; text-align:justify; margin:0px auto 0px auto; padding-bottom:30px;'>
		<table width='90%' align="center" border="0">
		<tr>
			<td align="left" style="padding:8px 0px 8px 0px;">
				<font class='Texte3'><u><b>Sommaire :</b></u></font>
			</td>
		</tr>
		<tr>
			<td align="left" style="padding:8px 0px 8px 0px;">
				<font class='Texte3'>
					<b>I - <a href='deroulement_1.php' class='lien_bleu_14'><strong>Déroulement d'une précandidature en ligne</strong></a></b>
				</font>
			</td>
		</tr>
		<tr>
			<td align="left" style="padding:8px 0px 8px 0px;">
				<font class='Texte3'>
					<b>II - <a href='composantes.php' class='lien_bleu_14'><strong>Composantes</strong></a></b>
				</font>
			</td>
		</tr>
		<tr>
			<td align="left" style="padding:8px 0px 8px 0px;">
				<font class='Texte3'>
					<b>III - <a href='faq.php' class='lien_bleu_14'><strong>Questions / Réponses</strong></a></b>
				</font>
			</td>
		</tr>
		<tr>
			<td align="left" style="padding:8px 0px 8px 0px;">
				<font class='Texte3'>
					<b>IV - Contacts</b>
				</font>

				<br><br>

				<table width='90%' align="center" border="0">
				<tr>
					<td align='left' nowrap="true" valign='top'>
						<font class='Texte'>
							<a href='mailto:<?php echo $GLOBALS["__EMAIL_SUPPORT"]; ?>?subject=Précandidatures - problème technique' class='lien2a'>Problèmes techniques avec l'interface Aria</a>
						</font>
					</td>
				</tr>
					<td align='left' nowrap="true" valign='top'>
						<font class='Texte'>
							<?php echo nl2br($GLOBALS["__SIGNATURE_ADMIN"]); ?>
						</font>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
	</div>
</div>
<?php
	pied_de_page_candidat();
	db_close($dbr);
?>

</body>
</html>

