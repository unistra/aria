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

	// EN-TETE SIMPLIFIEE (pas de composante, pas de menu, rien
	en_tete_simple();

	// MENU SUPERIEUR SIMPLIFIE
	menu_sup_simple();
?>

<div class='main'>
	<?php
		titre_page_icone("[Aide] Page d'accueil", "help-browser_32x32_fond.png", 15, "L");
	?>
		
	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><strong>Fonction principale</strong></u></font>
		<p class='Texte'>
			<strong>Afficher les précandidatures verrouillées en attente de recevabilité ou de décision de la Commission Pédagogique.</strong>
		</p>
		<p class='Texte'>
			L'affichage est séparé en deux colonnes. Dans la première, seules les précandidatures n'ayant reçu
			aucun traitement sont présentes, alors que dans la seconde, il s'agit des précandidatures partiellement
			traitées ("en attente", sur listes complémentaires, admission sous réserve, ...).
		</p>
		<p class='Texte'>
			Les précandidatures pour lesquelles une décision finale a été prise n'apparaissent plus dans ces listes,
			mais vous pourrez toujours retrouver la fiche d'un candidat via le menu "Recherche".
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			Vous pouvez accéder à n'importe quelle fiche en cliquant sur le nom du candidat.
		</p>

		<font class='Texte_16'><u><strong>Fonctionnalités et options</strong></u></font>
		<p class='Texte'>
			<u><strong>Modes "Recevabilité" et "Commission Pédagogique"</strong></u> : cette page peut afficher soit les précandidatures en
			attente de recevabilité (i.e si elles répondent à la question "le dossier est-il complet et les prérequis sont-ils
			satisfaits pour passer devant la Commission ?"), soit les précandidatures en attente de la décision de la 
			Commission Pédagogique.
		</p>
		<p class='Texte'>
			Lorsqu'une précandidature est validée Recevable, elle disparait du mode Recevabilité et passe automatiquement
			dans les listes du mode Commission.
		</p>

		<div class='centered_box'>
			<font class='Texte'>
				Pour passer d'un mode à l'autre, il suffit de cliquer sur l'icône suivante : 	<img style='vertical-align:middle;' src='<?php echo "$__ICON_DIR/reload_32x32_fond.png"; ?>' border='0' alt=''>
			</font>
		</div>

		<p class='Texte'>
			<u><strong>Filtre des fiches</strong></u> : si vous souhaitez afficher temporairement une seule formation, sélectionnez
			cette dernière dans le menu déroulant, puis validez. Si vous souhaitez que ce filtre soit actif lors de vos
			connexions suivantes, cliquez sur "Définir ce filtre par défaut".
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			Pour annuler le filtre, sélectionnez "Montrer toutes les formations" dans la liste, puis validez de nouveau.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u><strong>Tri des fiches</strong></u> : vous avez la possibilité de trier les listes par date croissante (tri par défaut),
			par nom, par formation et par moyenne du dernier diplôme mentionné par le candidat (attention à ce tri, car
			tous les candidats ne respectent pas la façon d'entrer cette moyenne).
		</p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
