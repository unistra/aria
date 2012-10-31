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

	// EN-TETE SIMPLIFIEE (pas de composante, pas de menu, rien
	en_tete_simple();

	// MENU SUPERIEUR SIMPLIFIE
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("[Aide] Administration de l'interface", "help-browser_32x32_fond.png", 15, "L");
	?>

	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><b>Fonction principale</b></u></font>
		<p class='Texte' style='padding-bottom:15px'>
			<b>Modifier les paramètres de l'interface et les informations liées à votre établissement</b>
		</p>

		<font class='Texte_16'>
			<u><strong>Détail des menus (variables en fonction de votre niveau d'accès)</strong></u>
		</font>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Utilisateurs (administrateurs uniquement)</b></u> :
			<br>- Ajouter, modifier et supprimer un utilisateur de la partie gestion
			<br>- Renvoyer ses informations d'identification
			<br>- Modifier ses droits d'accès aux établissements et aux formations
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Composantes</b></u> :
			<br>- Créer, modifier et supprimer un établissement
			<br>- Modifier la page d'information visible par les candidats
			<br>- Modifier les décisions utilisées par les Commissions Pédagogiques
			<br>- Créer, modifier et supprimer des motifs de refus
		</p>
		<p class='Texte'>
			<u><b>Offre de formations (options variables en fonction du niveau)</b></u> :
			<br>- Création, modification et suppression des éléments utilisés pour construire les formations :
			<ul class='Texte' style='padding-bottom:5px;'>
				<li>Années : L1, L2, L3, Licence Professionnelle, M1, M2, Années particulières (Capacités, Concours, ...)</li>
				<li>Mentions</li>
				<li>Spécialités / parcours</li>
			</ul>
			<font class='Texte'>
			- Construction des formations à l'aide de tous ces éléments
			<br>- Consultation des formations enregistrées sur l'interface
			</font>
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Paramètres des formations</b></u> :
			<br>- Ajout d'informations à destination des candidats, pour chaque formation
			<br>- Gestion des frais de dossier
			<br>- Liens entre les formations et les membres de la scolarité (pour la messagerie)
			<br>- Gestion des dates de sessions de candidatures et des commissions pédagogiques
			<br>- Gestion de la publication des décisions de commissions pédagogiques
			<br>- Constructeur de dossiers : formulaires que les candidats pourront compléter en ligne
			<br>- Editeur de lettres : construction des modèles : admission, refus, modalités d'inscription ...
			<br>- Editeur de justificatifs : listes des pièces demandées aux candidats
		</p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
