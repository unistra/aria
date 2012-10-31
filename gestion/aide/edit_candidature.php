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

	if(isset($_GET["aide_onglet"]) && ctype_digit($_GET["aide_onglet"]))
		$aide_onglet=$_GET["aide_onglet"];
	elseif(isset($_SESSION["onglet"]) && ctype_digit($_SESSION["onglet"]))
		$aide_onglet=$_SESSION["onglet"];
	else
		$aide_onglet=0;

	// EN-TETE SIMPLIFIEE (pas de composante, pas de menu : rien)
	en_tete_simple();

	// MENU SUPERIEUR SIMPLIFIE
	menu_sup_simple();

?>

<div class='main'>
	<?php
		titre_page_icone("[Aide] Traitement de la fiche d'un candidat", "help-browser_32x32_fond.png", 15, "L");
	?>
	
	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><b>Fonction principale</b></u></font>
		<p class='Texte'>
			<b>Afficher toutes les informations liées à un candidat</b>
		</p>
		<p class='Texte'>
			La fiche d'un candidat est commune à tous les établissements : à l'exception des menus 5 et 6, tous les gestionnaires 
			voient les mêmes informations (identité, cursus, langues, informations complémentaires).
		</p>
		<p class='Texte'>
			La colonne gauche présente un menu donnant accès aux différents éléments de la fiche. En haut de la partie centrale, 
			vous pouvez voir à tout moment le nom du candidat, sa date de naissance ainsi qu'un lien permettant de lui envoyer un
			message (le lien redirige vers la messagerie de l'application).
		</p>
		<p class='Texte'>
			Pour les menus 2 à 5, vous ne pouvez ajouter ou modifier des informations que si au moins un des voeux du candidat
			est verrouillé dans votre établissement. Un candidat ne pourra plus modifier les menus 2 à 5 si l'un de ses voeux
			a été verrouillé <b>quelle que soit la composante</b> (ceci évite de fournir des informations différentes aux
			composantes).
		</p>
		<p class='Texte_important'>
			Attention : les données entrées par les candidats sont personnelles et elles leur appartiennent. Il faut donc être
			prudent lors de leur manipulation et leur utilisation en dehors du cadre pédagogique est <b>strictement interdite</b>.
		</p>

		<?php
			if($aide_onglet!=1)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=1#onglet1' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Identité' desc='Détails'></a>
							<a href='$php_self?aide_onglet=1#onglet1' class='lien_bleu_12' target='_self'><b>Menu \"1 - Identité\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet1"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "1 - Identité"</b></u>
			<br>Informations entrées par le candidat lors de son enregistrement. Vous pouvez compléter ou corriger ces
			informations en cliquant sur "Modifier ces informations".
		</p>
		<p class='Texte'>
			Ces informations sont importantes : elles sont utilisées dans les modèles de lettres générées après décision de la
			Commission Pédagogique. En cas de problème avec ces modèles, il est conseillé de vérifier le format des données
			(adresse postale, par exemple).
		</p>
		<p class='Texte'>
			Le candidat pourra également modifier ces informations, même si ses voeux sont verrouillés.
		</p>
		<a name='onglet1'>

		<?php
			}

			if($aide_onglet!=2)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=2#onglet2' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Cursus' desc='Détails'></a>
							<a href='$php_self?aide_onglet=2#onglet2' class='lien_bleu_12' target='_self'><b>Menu \"2 - Cursus\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet2"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "2 - Cursus"</b></u>
			<br>Etudes et diplômes du candidat à partir du baccalauréat (en théorie).
		</p>
		<p class='Texte'>
			- au moins un voeu du candidat doit être verrouillé pour pouvoir modifer son cursus.
			<br>- chaque étape peut être modifiée en cliquant sur son intitulé ou sur l'année
			<br>- pour supprimer une étape, cliquez sur la poubelle sur la ligne correspondante
			<br>- lorsque vous recevez les justificatifs du candidat, vous devez indiquer le statut de chaque étape à l'aide
			des menus déroulants ("En attente des pièces", "Pièces manquantes", "Justificatifs validés" ...). Le champ
			"Précision" sert à indiquer la nature des pièces manquantes, si le candidat a omis de les joindre.
			<br>- le bouton "Valider" permet d'enregistrer <b>l'ensemble</b> du formulaire (toutes les étapes du cursus
			sont enregistrées d'un coup : il est inutile de valider chaque étape). Un courriel est alors automatiquement
			envoyé au candidat, lui indiquant le statut des justificatifs envoyés.
			<br>- <b>la validation du cursus est primordiale</b> : les lettres d'admissions s'appuient souvent sur le cursus
			du candidat, et seules les étapes justifiées (i.e validées sur l'interface) pourront être prises en compte. De plus, des
			rappels automatiques sont envoyés aux candidats lorsque leurs justificatifs ne sont pas validés. En l'absence de validation,
			certains candidats risquent donc de recevoir des rappels injustifiés.
		</p>
		<a name='onglet2'>

		<?php
			}

			if($aide_onglet!=3)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=3#onglet3' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Langues' desc='Détails'></a>
							<a href='$php_self?aide_onglet=3#onglet3' class='lien_bleu_12' target='_self'><b>Menu \"3 - Langues\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet3"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "3 - Langues"</b></u>
			<br>Langues maitrisées par le candidat, avec le niveau (lu / écrit / parlé / langue maternelle),
			le nombre d'années d'études pour cette langue ainsi que les éventuels tests de niveau et concours passés.
		</p>
		<p class='Texte'>
			Ces informations sont modifiables lorsqu'un voeu du candidat est verrouillé dans votre établissement.
		</p>
		<a name='onglet3'>

		<?php
			}

			if($aide_onglet!=4)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=4#onglet4' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Informations Complémentaires' desc='Détails'></a>
							<a href='$php_self?aide_onglet=4#onglet4' class='lien_bleu_12' target='_self'><b>Menu \"4 - Infos Complémentaires\"</b></a>
						 </p>\n");
			else
			{
		?>		
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet4"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "4 - Infos Complémentaires"</b></u>
			<br>Autres informations que le candidat souhaite fournir pour appuyer sa candidature : projets, formations, stages,
			d'emplois, ...
		</p>
		<p class='Texte'>
			Ces informations sont modifiables lorsqu'un voeu du candidat est verrouillé dans votre établissement.
		</p>
		<a name='onglet4'>

		<?php
			}

			if($aide_onglet!=5)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=5#onglet5' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Autres Renseignements' desc='Détails'></a>
							<a href='$php_self?aide_onglet=5#onglet5' class='lien_bleu_12' target='_self'><b>Menu \"5 - Autres Renseignements\"</b></a>
						 </p>\n");
			else
			{
		?>		
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet5"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "5 - Autres Renseignements"</b></u>
			<br>Ce menu est dynamique : si des éléments ont été créés dans le Constructeur de dossiers, les questions
			correspondantes et les réponses apportées par le candidat seront affichées dans ce menu. Dans le cas contraire,
			ce dernier restera vide.
		</p>
		<p class='Texte'>
			Si les questions sont posées pour chaque formation choisie par le candidat, vous verrez apparaitre plusieurs fois
			le même élément (une par formation choisie). Ce comportement ainsi que d'autres optiones peut être modifié dans
			les paramètres des élements (cf. Constructeur de dossiers).
		</p>
		<p class='Texte'>
			Ces informations sont modifiables lorsqu'un voeu du candidat est verrouillé dans votre établissement.
		</p>
		<a name='onglet5'>

		<?php
			}

			if($aide_onglet!=6)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=6#onglet6' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Précandidatures' desc='Détails'></a>
							<a href='$php_self?aide_onglet=6#onglet6' class='lien_bleu_12' target='_self'><b>Menu \"6 - Précandidatures\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a name='onglet6'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet6"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "6 - Précandidatures"</b></u>
			<br>Affichage des voeux du candidat dans l'établissement courant, triés par ordre de préférence décroissant. Pour
			chaque voeu, vous pouvez:
			<br>- modifier l'ordre de préférence et la formation choisie
			<br>- modifier la date du verrouillage (date modifiable à l'aide du mini-formulaire et du bouton "Changer la
			date" pour valider la nouvelle)
			<br>- le verrouiller/déverrouiller manuellement (si le candidat a oublié des informations sur sa fiche, par exemple)
			<br>- modifier le statut de la recevabilité (dossier complet ou non) et les motivations si le dossier est en attente
			<br>- générer le "Formulaire de Commission" lorsque la candidature est recevable
			<br>- modifier la décision de la commission pédagogique (cliquez sur "Commission" pour accéder à sa saisie)
			<br>- générer les documents officiels si une décision a été saisie et qu'un modèle de lettre existe pour la
			formation <b>et</b> la décision
			<br>- le supprimer
		</p>
		<p class='Texte'>
			<u><b>Traitement d'un voeu</b></u>
			<br>Une candidature se traite en deux étapes : la <b>recevabilité</b> et la <b>commission pédagogique</b>.
		</p>
		<p class='Texte'>
			<b>1 - Recevabilité</b>
			<br>Elle répond aux deux questions : "Le dossier est-il complet ? Les prérequis sont-ils satisfaits ?" Si la
			réponse est oui pour les deux, alors le dossier est recevable. Pour chaque voeu, plusieurs options sont
			disponibles pour la recevabilité :
			<br>&#8226; <u>Non traitée</u> : état par défaut (la recevabilité n'a pas été étudiée)
			<br>&#8226; <u>Recevable</u> : le dossier est complet, il peut passer en commission pédagogique
			<br>&#8226; <u>Non recevable</u> : les prérequis ne sont pas satisfaits, le dossier ne passera donc pas en
			commission. La motivation n'est pas nécessaire, une phrase type est envoyée au candidat. </font>
			<font class='Texte_important'><b>Lorsque vous validez ce choix, un message est envoyé au candidat, ce qui n'est pas
			le cas pour un dossier recevable</b></font>.
			<br>&#8226; <u>Plein droit</u> : statut pour les candidats qui n'auraient pas du déposer de dossier pour cette
			formation car ils entrent de plein droit dans cette dernière. <b>Un message est envoyé au candidat lors de la
			validation</b>.
			<br>&#8226; <u>Mettre en attente</u> : s'il manque une pièce au dossier ou si une condition n'est pas encore
			satisfaite, il peut être mis en attente. Le champ <b>motivation</b> doit être complété avec le motif de la mise
			en attente, et un message est envoyé au candidat. <b>Attention</b> : cette option ne doit pas être utilisée
			lorsque seuls les résultats de l'année en cours sont manquants (le candidat ne les possède pas encore). Dans ce
			cas, le dossier est recevable et le candidat pourra éventuellement être "Admis sous réserve" par la Commission
			Pédagogique.
		</p>
		<p class='Texte'>
			<b>2 - Commission Pédagogique</b>
			<br>lorsqu'un dossier est validé "recevable", deux liens apparaissent alors :
			<br>&#8226; <u>Form. Commission</u> : génération du formulaire de commission (format PDF) permettant à cette dernière
			d'écrire sa décision motivée, avec signature de son Président (et Vice-Président s'il y a lieu).
			<br>&#8226; <u>Commission</u> : après retour du formulaire, cliquez sur ce dernier pour accéder à la page de saisie
			de la décision de la Commission.
		</p>

		<?php
			}

			if($aide_onglet!=7)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=7#onglet7' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Mode Manuel' desc='Détails'></a>
							<a href='$php_self?aide_onglet=7#onglet7' class='lien_bleu_12' target='_self'><b>Menu \"7 - Mode Manuel\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet7"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "7 - Mode Manuel"</b></u>
			<br>Actions particulières sur la fiche du candidat :
			<br>- modifier son adresse électronique (<i>email</i>) en cas d'erreur ou de changement
			<br>- envoyer un courriel contenant son identifiant et son mot de passe
			<br>- envoyer un message (interne à l'application) contenant le récapitulatif de la fiche et la liste des
			justificatifs pour les voeux verrouillés
			<br>- supprimer entièrement la fiche (avec les précautions d'usage).
		</p>
		<a name='onglet7'>

		<?php
			}

			if($aide_onglet!=8)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=8#onglet8' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Documents PDF' desc='Détails'></a>
							<a href='$php_self?aide_onglet=8#onglet8' class='lien_bleu_12' target='_self'><b>Menu \"8 - Documents PDF\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet8"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "8 - Documents PDF"</b></u>
			<br>Ce menu vous permet de générer le récapitulatif et les listes de justificatifs du candidat ainsi que
			les formulaires de commission pédagogique (pour les voeux verrouillées uniquement).
		</p>
		<a name='onglet8'>

		<?php
			}
			
			if($aide_onglet!=9)
				print("<p class='Texte'>
							<a href='$php_self?aide_onglet=9#onglet9' target='_self'><img class='icone' src='$__ICON_DIR/plus_11x11.png' width='11' border='0' title='Historique' desc='Détails'></a>
							<a href='$php_self?aide_onglet=9#onglet9' class='lien_bleu_12' target='_self'><b>Menu \"9 - Historique\"</b></a>
						 </p>\n");
			else
			{
		?>
		<p class='Texte'>
			<a href='<?php echo "$php_self?aide_onglet=0#onglet9"; ?>' target='_self'><img class='icone' src='<?php echo "$__ICON_DIR/moins_11x11.png"; ?>' width='11' border='0' title='Fermer' desc='Fermer'></a><u><b>Menu "9 - Historique"</b></u>
			<br>Informations sur les événements liés à la fiche du candidat :
			<br>- candidatures dans les autres établissements liés à l'interface (avec les décisions)
			<br>- candidatures des années précédentes, également avec les décisions
			<br>- historique des actions (Gestion et Candidat) : date des décisions, génération des lettres, ...
		</p>
		<p class='Texte'>
			Seuls les évenéments communs ou relatifs à votre établissement sont visibles. Le candidat n'a pas accès à ce
			menu.
		</p>
		<a name='onglet9'>
		<?php
			}
		?>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
