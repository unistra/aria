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
			<br><br>I - Déroulement d'une précandidature en ligne (5/5)</b>
		</font>
	</div>

	<div style='width:80%; text-align:justify; margin:0px auto 0px auto; padding-bottom:30px;'>
		<font class='Texte3'>
			<u><b>Etape 5 </b> : Suivi et décision</u>
		</font>
		<font class='Texte'>
			<br><br>Pendant toute la durée du traitement, vous pourrez consulter votre fiche afin de suivre en temps réel
			l'évolution de son traitement (validation des justificatifs envoyés, suivi des précandidatures, ...). Vous recevrez également des
			messages vous informant du statut de vos demandes, <b>en particulier dans le cas où des pièces essentielles manqueraient à vos dossiers</b>.
			<br><br>
		<font class='Texte_important'>
			<b>Important</b> : en aucun cas un courriel ne peut se substituer à une lettre officielle de l'université. Aucun recours ne
			sera accepté sur seule présentation des courriels envoyés par l'interface de précandidatures en ligne.
			<br><br><br>
		</font>
		<font class='Texte3'>
			<u>Examen par la Commission Pédagogique et notification de la décision finale</u>
		</font>
		<font class='Texte'>
			<br><br>En cas de recevabilité de vos précandidatures (dossier complet et prérequis vérifiés), ces dernières seront examinées par les différentes Commissions Pédagogiques qui donneront
			un avis définitif sur votre admission dans la ou les formations demandées. Cette décision vous sera alors notifiée par courrier à l'adresse que vous aurez indiquée lors
			de votre enregistrement sur l'interface.

			<br><br>
			<u>Remarque sur l'affichage des décisions en ligne</u>
			<br><br>Certaines composantes diffèrent la publication des décisions de la Commission Pédagogique. Lorsque c'est le cas, la mention "<b>En attente de publication</b>" apparaît dans le menu
			"5 - Précandidatures", et vous devez attendre que la composante ait validé ces décisions. La date de publication peut varier en fonction des composantes et des formations.
			<br><br>
		</font>
		<font class='Texte_important'><b>Aucune date de publication ne sera donnée par courriel ou par téléphone.</b></font>
	</div>
	<div class='centered_box' style='padding-bottom:30px;'>
		<a href='deroulement_4.php' class='lien_bleu_12'><img class='icone icone_texte_d' src='<?php echo "$__ICON_DIR/back_32x32_fond.png"; ?>' border='0'></a>
		<a href='deroulement_4.php' class='lien_bleu_12' style='padding-right:50px;'><b>Etape 4 : Verrouillage et justificatifs</b></a>
		<a href='documentation.php' class='lien_bleu_10'>Retour au sommaire</a>
	</div>
</div>

<?php
	pied_de_page_candidat();
?>

</body>
</html>

