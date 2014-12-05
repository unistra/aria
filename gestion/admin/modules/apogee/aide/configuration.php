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

   include "../../../../../configuration/aria_config.php";
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
		titre_page_icone("[Aide] Module Apogée : Configuration", "help-browser_32x32_fond.png", 15, "L");
	?>

	<div style='margin-left:auto; margin-right:auto; padding-bottom:20px; width:90%; text-align:justify;'>
		<font class='Texte_16'><u><strong>Fonction principale</strong></u></font>
		<p class='Texte' style='padding-bottom:15px;'>
			<strong>Modifier les paramètres du module APOGEE</strong>
		</p>
	
		<font class='Texte_16'><u><strong>Paramètres</strong></u></font>
      <p class='Texte' style='padding-bottom:15px;'>
         <u><strong>Première lettre du numéro d'autorisation d'inscription (avec prise de rendez-vous)</strong></u> :
         Lorsqu'un candidat est admis à s'inscrire (en présentiel), un code d'autorisation lui est fourni. Ce code est construit à partir de diverses données :
         <br>- une lettre correspondant à l'université
         <br>- les deux derniers chiffres de l'année en cours
         <br>- initiales du candidat
         <br>- date de naissance du candidat
         <br>- code étape de la formation (<strong>sans</strong> la Version d'Etape)
         <br><br>
         Ce menu sert à paramétrer la lettre correspondant à l'université sélectionnée, le reste du code est ensuite généré automatiquement (utilisation de
         la <strong>macro "%CODE%"</strong>).
      </p>
      <p class='Texte' style='padding-bottom:15px;'>
         <u><strong>Préfixe du code OPI généré pour les Primo-Entrants</strong></u> :
         Lorsque le script d'extraction des Primo Entrants est exécuté, un numéro d'inscription OPI (différent du numéro d'autorisation) est généré pour chaque
         admission. Ce code est un simple compteur incrémenté automatiquement et préfixé par une ou plusieurs lettres. C'est ce préfixe que vous devez entrer dans
         ce champ.
         <br><br>
         <strong>Exemple :</strong> si le préfixe est "AR", les codes générés auront pour format "AR00000001", "AR00000002", etc.
      </p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u><strong>Message envoyé à un candidat Primo Entrant</strong></u> :
         Lorsque le script d'extraction des Primo Entrants est exécuté, un message interne est automatiquement envoyé à chaque candidat et pour chaque formation pour
         laquelle il a été admis. Pour être utile, ce message doit au moins contenir le <strong>Numéro OPI</strong> généré par le script (macro <strong>%OPI%</strong>
         dans le message) ainsi que l'adresse du site sur lequel le candidat devra se rendre pour s'inscrire.
		</p>
		<p class='Texte' style='padding-bottom:15px;'>
			<u><strong>Message envoyé à un candidat Admis sous Réserve</strong></u> :
			Un candidat "Admis sous Réserve" n'est en théorie pas définitivement admis : il doit encore apporter des documents prouvant qu'il vérifie les dernières conditions 
			imposées par la scolarité (la "réserve" indiquée) pour être admis.
			<br /><br />
			Toutefois, la plupart de ces candidats vérifiant habituellement les réserves émises, on les autorise souvent à prendre un rendez-vous rapidement (à défaut de leur 
			permettre une (ré)inscription intégrale en ligne), d'où la présence d'un message spécifique pour ces derniers. Ce message doit normalement contenir le numéro 
			d'autorisation ("%CODE%") lui permettant de prendre ce rendez-vous.
			<br /><br />
			Ces candidats sont extraits via le même script que les primo-entrants, mais le voeu n'est cette fois pas enregistré (ce qui empêche l'inscription intégrale).
      </p>

      <font class='Texte_16'><u><strong>Détails</strong></u></font>
      <p class='Texte' style='padding-bottom:5px;'>
         <u><strong>Les macros suivantes sont utilisables dans le corps du message</strong></u> :
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>%OPI%</strong> : Numéro d'inscription OPI généré pour permettre l'inscription des Primo-Entrants (IA-Primo)
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>%Formation%</strong> : Nom de la formation à laquelle le candidat a été admis
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[Signature]</strong> : Signature du message : cette macro sera remplacée par la valeur du paramètre "Signature des messages de l'application" 
         (cf. Paramétrage système). Attention, ce paramètre est différent de la macro %signature% utilisée dans les modèles de lettres.
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[lien=adresse html]lien cliquable[/lien]</strong> : lien HTML
         <br>Exemple : [lien=http://www.google.fr]Recherche Google[/lien]
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[mail=adresse électronique]lien cliquable[/mail]</strong> : lien vers l'envoi d'un courriel
         <br>Exemple : [mail=admin@domaine.fr]cliquez ici pour envoyer un message[/mail]
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[gras]Texte[/gras]</strong> : Texte en gras
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[italique]Texte[/italique]</strong> : Texte en italique
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[souligner]Texte[/souligner]</strong> : Texte souligné
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[centrer]Texte[/centrer]</strong> : Texte centré
      </p>
      <p class='Texte' style='padding-bottom:5px;'>
         <strong>[important]Texte[/important]</strong> : Texte mis en valeur (dépend de la feuille de style)
      </p>
	</div>
</div>
<?php
	pied_de_page();
?>
</body></html>
