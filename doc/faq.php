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
		<p style='margin-top:0px; padding-top:4px;'><font class='Titre'>Dépôt de dossiers de précandidature</font></p>
		<p><strong>III - Questions / Réponses</strong>
		<p><a href='documentation.php' class='lien2a'><img src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' alt='Retour' border='0'></a></p>
	</div>

	<div style='width:90%; text-align:justify; margin:0px auto 0px auto; padding-bottom:30px;'>
		<ul style='list-style-type:none; padding-bottom:40px;'>
			<li><a href='#Q1' class='lien2a'>1 - Je me suis inscrit sur l'interface, mais je ne trouve pas les dossiers à télécharger. Où sont-ils ?</a></li>
			<li><a href='#Q2' class='lien2a'>2 - En déposant ma candidature en ligne, je dois tout de même envoyer des documents par courrier à la Scolarité. Pourquoi et quel est alors l'avantage de l'interface en ligne ?</a></li>
			<li><a href='#Q3' class='lien2a'>3 - Dans l'onglet "Cursus", je dois renseigner l'année en cours, comment la justifier puisque je n'ai pas encore le diplôme ?</a></li>
			<li><a href='#Q4' class='lien2a'>4 - Je n'ai accès à aucune imprimante, que dois-je faire avec les documents qui m'ont été envoyés sur la messagerie ?</a></li>
			<li><a href='#Q5' class='lien2a'>5 - Le délai imparti pour remplir ma fiche est terminé. Les formations sont verrouillées, mais j'avais encore des modifications à effectuer. Que dois-je faire ?</a></li>
			<li><a href='#Q6' class='lien2a'>6 - La date d'une formation est passée, mais je n'ai pas reçu le message comme annoncé. Que s'est-il passé ?</a></li>
			<li><a href='#Q7' class='lien2a'>7 - Quel est le format des pièces jointes au message récapitulatif ? Avec quel programme dois-je les ouvrir ?</a></li>
			<li><a href='#Q8' class='lien2a'>8 - J'ai effectué tout ou partie de ma scolarité dans cette université. Dois-je quand même renvoyer tous les justificatifs demandés ?</a></li>
			<li><a href='#Q9' class='lien2a'>9 - J'ai une question à poser mais elle n'apparaît ni dans la documentation, ni sur cette page, à qui dois-je m'adresser ?</a></li>
		</ul>

		<a name="Q1">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;1 - Q : Je me suis inscrit(e) sur l'interface, mais je ne trouve pas les dossiers à télécharger. Où sont-ils ?</p>
		<p class='Texte'>
			<strong>R : </strong>Sur l'Interface de Précandidatures, il n'est <u>plus nécessaire</u> de télécharger de dossier papier ou PDF.
		</p>
		<p class='Texte'>
			L'interface EST votre dossier, avec vos informations personnelles, votre cursus, votre niveau en langues étrangères, vos motivations, etc.
			Une fois tous les onglets consciencieusement remplis (identité, cursus, ... sans oublier les <u>FORMATIONS demandées</u>),
			il vous suffit ensuite d'attendre le verrouillage automatique de vos voeux (le délai par défaut est 48 heures). Ce délai vous est laissé
			pour que vous puissiez modifier tranquillement votre fiche, sans contrainte particulière.
			</font>
		</p>
		<p class='Texte' style='padding-bottom:20px;'>
			Une fois les formations verrouillées, vous recevrez (sur la messagerie de l'interface, avec notification par courriel) la liste des justificatifs à renvoyer à la scolarité PAR VOIE POSTALE.
		</p>

		<a name="Q2">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;2 - Q : En déposant ma candidature en ligne, je dois tout de même envoyer des documents par courrier à la Scolarité. Pourquoi et quel est alors l'avantage de l'interface en ligne ?</p>
		<p class='Texte'>
			<strong>R : </strong>Les pièces à fournir par courrier sont nécessaires car elles servent à justifier les informations que vous avez entrées sur l'interface, notamment au niveau de
			votre cursus. Ces pièces seront conservées dans votre dossier, et à ce titre, aucun justificatif ne sera accepté par voie électronique (<i>e-mail</i>).
			L'avantage du dépôt de précandidatures est qu'il accélère considérablement le temps de traitement de l'ensemble des dossiers.
		</p>
		<p class='Texte' style='padding-bottom:20px;'>
			Les premières conséquences évidentes sont les suivantes :
			<br>- vous saurez rapidement si votre précandidature est <b>recevable ou non</b> (avant même de passer par la Commission Pédagogique)
			<br>- après la Commission Pédagogique, vous recevrez une réponse (admission, liste d'attente, admission sous réserve, refus, ...) plus rapidement.
		</p>

		<a name="Q3">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;3 - Q : Dans l'onglet "Cursus", je dois renseigner l'année en cours, comment la justifier puisque je n'ai pas encore le diplôme ?</p>
		<p class='Texte' style='padding-bottom:20px;'>
			<strong>R : </strong>Les justificatifs seront à transmettre à la Scolarité le plus rapidement possible. En effet, dans la mesure où les admissions
			dépendent des diplômes obtenus, votre dossier pourra selon les cas être "Admis sous réserve", c'est à dire en attente des
			derniers justificatifs de votre part. <b>N'attendez surtout pas d'obtenir ce diplôme pour envoyer les justificatifs que vous possédez déjà !</b>.
		</p>

		<a name="Q4">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;4 - Q : Je n'ai accès à aucune imprimante, que dois-je faire avec les documents qui m'ont été envoyés sur la messagerie ?</strong></p>
		<p class='Texte' style='padding-bottom:20px;'>
			<strong>R : </strong>Ces documents doivent <b>impérativement</b> être imprimés afin d'être renvoyés au service de Scolarité. Si vous n'avez accès à aucune imprimante, vous pouvez
			faire une demande de dossier papier (dans les différents services de Scolarité), mais vous perdrez les avantages des précandidatures en ligne.
		</p>

		<a name="Q5">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;5 - Q : Le délai imparti pour remplir ma fiche est terminé. Les formations sont verrouillées, mais j'avais encore des modifications à effectuer. Que dois-je faire ?</strong></p>
		<p class='Texte' style='padding-bottom:20px;'>
			<strong>R : </strong>Si vous avez une requête particulière, vous pouvez passer par le menu "Contacter la Scolarité" (menu supérieur de l'interface
			de saisie) afin de poser une question au service de Scolarité de la Composante sélectionnée. La réponse vous sera envoyée
			via la messagerie interne à l'application (vous recevrez normalement une notification de ce message par courriel).
		</p>

		<a name="Q6">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;6 - Q : La date d'une formation est passée, mais je n'ai pas reçu le message comme annoncé. Que s'est-il passé ?</strong></p>
		<p class='Texte'>
			<strong>R : </strong>Vous devriez normalement recevoir le message au plus tard 24 heures après le verrouillage d'une formation, ce message s'accompagnant d'une
			notification envoyée par courriel (les courriels sont envoyés à 5 heures du matin, heure GMT+1). Il arrive parfois que ces courriels de notification
			soient <b>considérés comme des pourriels</b> (<i>spams</i>) et effacés automatiquement. Dans tous les cas, consultez régulièrement <b>votre messagerie
			ainsi que celle de l'interface</b> !
		</p>
		<p class='Texte' style='padding-bottom:20px;'>Si en revanche vous ne recevez pas les messages sur la <b>messagerie de l'interface</b>, vous pouvez envoyer un courriel
			<a href='mailto:<?php echo $__EMAIL_SUPPORT; ?>?subject=[Précandidatures - Récapitulatif non reçu]' class='lien2a'>à cette adresse</a>, en précisant
			bien :
			<br>- vos nom, prénom et date de naissance
			<br>- l'identifiant utilisé pour vous connecter à l'interface en ligne
			<br>- la Composante pour laquelle le document n'a pas été reçu
		</p>

		<a name="Q7">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;7 - Q : Quel est le format des pièces jointes au message récapitulatif ? Avec quel programme dois-je les ouvrir ?</strong></p>
		<p class='Texte' style='padding-bottom:20px;'>
			<strong>R : </strong>Les pièces jointes sont au format <b>PDF</b> :
			<br>- sous Unix/Linux, vous pouvez ouvrir ces documents grâce à des outils tels que <b>xpdf</b>, <b>kpdf</b> (KDE), <b>gpdf</b>
			(Gnome), <b>Adobe Acroread</b>, ...
			<br>- sous Microsoft Windows, vous pouvez télécharger <b>Adobe Reader</b><a href='http://www.adobe.fr/products/acrobat/readstep2_allversions.html' class="lien2a" target="_blank">
			sur cette page</a>
		</p>

		<a name="Q8">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;8 - Q : J'ai effectué tout ou partie de ma scolarité dans cette université. Dois-je quand même renvoyer tous les justificatifs demandés ?</strong></p>
		<p class='Texte'>
			<strong>R : </strong>Oui.
		</p>
		<p class='Texte' style='padding-bottom:20px;'>
			Ces pièces étant systématiquement archivées d'une année sur l'autre, tout l'avantage des précandidatures en ligne (notamment au niveau des délais de
			traitement) serait perdu compte tenu du temps nécessaire à la recherche de votre dossier dans ces archives.
		</p>

		<a name="Q9">
		<p class='fond_menu Texte_menu' style='font-weight:bold;'>&nbsp;&nbsp;9 - Q : J'ai une question à poser mais elle n'apparaît ni dans la documentation, ni sur cette page, à qui dois-je m'adresser ?</strong></p>
		<p class='Texte'>
			<strong>R : </strong>L'adresse à laquelle envoyer votre question dépend de la nature de cette dernière :
			<br>- pour toute question d'ordre administratif (conditions d'admission, dépôt des dossiers, ...), veuillez passer par le lien
			"Contacter la scolarité" après avoir sélectionné la composante voulue.
			<br>- pour signaler une erreur ou problème technique avec l'interface, merci d'utiliser plutôt <a href="mailto:<?php echo $__EMAIL_SUPPORT; ?>subject=[Précandidatures - Problème technique]" class='lien2a'>cette adresse</a>
		</p>
	</div>
</div>
<?php
	pied_de_page_candidat();
?>

</body>
</html>

