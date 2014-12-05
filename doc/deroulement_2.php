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
	include "$__INCLUDE_DIR_ABS/fonctions.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	en_tete_candidat_simple();
	menu_sup_simple();
?>

<div class='main'>
	<div class='centered_box'>
		<font class='Texte3'>
			<b>Dépôt de dossiers de précandidature
			<br><br>I - Déroulement d'une précandidature en ligne (2/5)</b>
		</font>
	</div>

	<div style='width:80%; text-align:justify; margin:0px auto 0px auto; padding-bottom:30px;'>
		<font class='Texte3'>
			<u><b>Etape 2 </b> : Présentation de l'interface de saisie</u>
		</font>
		<font class='Texte'>
			<br><br>Une fois vos identifiants reçus par courriel, vous pouvez accéder à l'interface de saisie.

			<br><br><u><b>Sélection de la composante</b></u>
			<br>Avant de compléter votre fiche, vous devez choisir dans quelle composante vous désirez déposer un dossier de précandidature (sauf si vous avez utilisé un lien
			direct : dans ce cas, la composante est automatiquement sélectionnée). Au sein de l'interface, vous pouvez à tout moment sélectionner une autre composante afin de
			déposer d'autres dossiers (cf. partie <b>II - <a href='composantes.php' class='lien2a'>Composantes</a></b>).
			<br>
			<br><u><b>Saisie de vos données</b></u>
			Après avoir sélectionné la composante et validé votre choix, vous pouvez commencer à compléter vos données. L'interface de	saisie est composée
			de plusieurs menus : <b>chaque menu doit être complété consciencieusement</b> :
		</font>
		<br><br>
		<font class='Texte3'>
			&#8226;&nbsp;<u><b>Menu 1 : Identité</b></u>
		</font>
		<font class='Texte'>
			<br>
			<br>Ce menu résume les informations entrées lors de votre enregistrement sur l'interface. Elles sont cruciales, vous pouvez les mettre à jour à tout moment.
			<br><br><br>
		</font>
		<font class='Texte3'>
			&#8226;&nbsp;<u><b>Menu 2 : Cursus</b></u>
		</font>
		<font class='Texte'>
			<br>
			<br>Ce menu vous permet de renseigner toutes les étapes de votre cursus scolaire, à partir du baccalauréat (ou équivalent) inclus.
			<br>
			<br>Lorsque vous devez compléter un champ texte, veillez à le remplir de la manière la plus exacte possible. Des informations
			incorrectes pourraient affecter le temps de traitement de votre fiche, et vos demandes pourraient ne pas être traitées.
			<br><br>
		</font>
		<font class='Texte_important_14'>
			Chaque étape devra par la suite être justifiée. Pour chaque formation demandée, vous recevrez une liste des pièces à fournir
			(par voie postale) au service de scolarité de chaque composante concernée par vos demandes.
			<br><br>Les diplômes en cours de préparation seront à justifier dès l'obtention des résultats, <b>mais vous devez envoyer les
			pièces déjà en votre possession sans attendre !</b>
			<br><br>	<br>
		</font>
		<font class='Texte3'>
			&#8226;&nbsp;<u><b>Menu 3 : Langues</b></u>
		</font>
		<font class='Texte'>
			<br>
			<br>Ce menu vous permet de renseigner votre niveau en langues étrangères. Vous pouvez préciser vos compétences (lu, écrit, parlé) pour chaque langue, ainsi que les
			éventuels diplômes obtenus (TOEIC, TOEFL, TCF, ...).
			<br><br><br>
		</font>
		<font class='Texte3'>
			&#8226;&nbsp;<u><b>Menu 4 : Informations complémentaires et expériences professionnelles</b></u>
		</font>
		<font class='Texte'>
			<br>
			<br>Renseignez ici vos expériences professionnelles (formations, stages, emplois), et autres informations (service national, arrêt et reprise d'études ...)
			susceptibles d'intéresser les scolarités ou les responsables de Commissions Pédagogiques.
			<br><br><br>
		</font>
		<font class='Texte3'>
			&#8226;&nbsp;<u><b>Menu 5 : Précandidatures pour la Composante sélectionnée</b></u>
		</font>
		<font class='Texte'>
			<br>
			<br>Ce menu vous permet de sélectionner les formations pour lesquelles vous souhaitez déposer une précandidature.
			<br>
			<br>Vous devez <b>trier</b> vos voeux </font><font class='Texte_important'><b>par ordre de préférence décroissant</b></font>
			<font class='Texte'>(en d'autres termes : ce que vous préférez tout en haut de la liste). Sur chaque ligne, des flèches sont présentes à droite pour
			réordonner vos précandidatures vers le haut ou vers le bas.
			<br><br>
		</font>
		<font class='Texte_important'><u><b>Remarque concernant les candidatures à choix multiples :</b></u></font>
		<font class='Texte'>
			<br><br>
			Dans certaines composantes, des formations sont automatiquement regroupées en une seule candidature.
			Celà signifie que vous devrez, pour ce voeu particulier, trier là encore par ordre de préférence décroissant les formations/spécialités choisies.
			D'autres flèches prévues à cet effet, situées cette fois sur le coté gauche, apparaîtront automatiquement.
			<br><br><b>Remarque :
			<br>Sauf instruction contraire de la scolarité, vous devrez envoyer un seul exemplaire des justificatifs demandés pour ces spécialités regroupées.
			<br><br><br>
		</font>
		<font class='Texte3'>
			&#8226;&nbsp;<u><b>Menu 6 : Autres Renseignements obligatoires</b></u>
		</font>
		<font class='Texte'>
			<br>
			<br><b>Ce menu n'apparaît que lorsqu'une composante demande des informations complémentaires.</b>
			<br><br>En fonction des formations sélectionnés, certaines composantes demandent que vous remplissiez des formulaires spéciaux,
			comme le contenu de vos précédents enseignements, par exemple.
			<br><br>
		</font>
		<font class='Texte_important_14'>
			<b>Ces champs ne sont pas optionnels, vérifiez bien la présence ou non de ce menu, pour chaque composante</b>
		</font>

		<font class='Texte_important_14'>
			<b>Important</b> : un dépôt de précandidature ne signifie en aucun cas que votre demande sera examinée par la Commission Pédagogique : votre
			précandidature doit d'abord être validée par les scolarités des composantes concernées (justificatifs de votre cursus et prérequis satisfaits
			pour les formations demandées).
		</font>
	</div>
	<div class='centered_box' style='padding-bottom:30px;'>
		<a href='deroulement_1.php' class='lien_bleu_12'><img class='icone icone_texte_d' src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' border='0'></a>
		<a href='deroulement_1.php' class='lien_bleu_12' style='padding-right:50px;'><b>Etape 1 : Identifiant et mot de passe</b></a>
		<a href='documentation.php' class='lien_bleu_10'>Retour au sommaire</a></td>
		<a href='deroulement_3.php' class='lien_bleu_12' style='padding-left:50px;'><b>Etape 3 : Délai de modification de votre fiche</b></a>
		<a href='deroulement_3.php' class='lien_bleu_12'><img class='icone icone_texte_g' src='<?php echo "$__ICON_DIR/forward_32x32_fond.png"; ?>' border='0'></a>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>

</body>
</html>

