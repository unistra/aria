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
		titre_page_icone("[Aide] Création / modification d'une formation", "help-browser_32x32_fond.png", 15, "L");
	?>

	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><b>Fonction principale</b></u></font>
		<p class='Texte' style='padding-bottom:15px'>
			<b>Créer une formation à partir d'éléments prédéfinis (année, spécialité/mention, ...)</b>
		</p>

		<font class='Texte_16'><u><b>Options</b></u></font>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Année</b></u> : sélection du niveau de la formation, de la Licence 1ère année (L1) au Master 2ème année
			(M2) en passant par la Licence Professionnelle. Pour les formations particulières (préparations aux concours,
			Diplômes d'Universités, Capacité ...), vous pouvez utiliser la valeur "Année particulière".
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Mention / Spécialité</b></u> : intitulé de la formation parmi ceux créés précédemment.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Finalité</b></u> : Recherche, Professionnelle ou aucune.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Formation Activée ?</b></u> : vous permet de rendre visible/invisible cette formation sur l'interface,
			sans pour autant la supprimer complètement. Cette fonction est surtout utile si vous ne savez pas encore si
			la formation sera ouverte ou non, ou bien si la formation est supprimée mais que vous désirez conserver des
			statistiques sur les candidatures des années précédentes.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Formation gérée manuellement ?</b></u> : si "Oui", alors vous pourrez utiliser cette formation en ligne
			(i.e ajouter manuellement des candidatures et les traiter, générer des courriers, etc), mais les candidats ne
			la verront pas et ne pourront donc pas la sélectionner.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Code Etape Apogée</b></u> : si votre établissement est lié à Apogée pour les inscriptions administratives,
			vous aurez besoin de générer un code confidentiel pour chaque candidature retenue. Ce code secret s'appuie en général 
			sur le Code Etape de la formation, vous pouvez donc le renseigner ici.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Frais de dossiers</b></u> : montant des frais demandés au candidat, en euros. Dans l'éditeur de justificatifs,
			n'oubliez pas d'indiquer les modalités de paiement.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Responsable de la formation</b></u> : civilité, nom et prénom du ou de la responsable de cette formation.
			Ce champ pourra être utilisé dans les modèles de lettres via une macro prévue à cet effet.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Courriel du responsable</b></u> : adresse électronique (<i>email</i>) du ou de la responsable de la
			formation. Là encore, une macro existe pour les modèles de lettres.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Formation sélective</b></u> : indique si la formation est sélective ou non. Ce champ n'est actuellement pas
			exploité, mais il pourrait l'être à l'avenir, il est donc conseillé de bien renseigner ce champ.
		</p>
		<p class='Texte' style='padding-bottom:15px'>
			<u><b>Convocation à l'entretien</b></u> : indique si les candidats à cette formation doivent ou non passer un
			entretien complémentaire au dépôt du dossier. Ce paramètre a une influence sur le traitement des candidatures,
			il est donc importante de bien le renseigner.
		</p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
