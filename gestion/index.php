<?php
/*
=======================================================================================================
APPLICATION ARIA - UNIVERSITE DE STRASBOURG

LICENCE : CECILL-B
Copyright Universit� de Strasbourg
Contributeur : Christophe Boccheciampe - Janvier 2006
Adresse : cb@dpt-info.u-strasbg.fr

L'application utilise des �l�ments �crits par des tiers, plac�s sous les licences suivantes :

Ic�nes :
- CrystalSVG (http://www.everaldo.com), sous licence LGPL (http://www.gnu.org/licenses/lgpl.html).
- Oxygen (http://oxygen-icons.org) sous licence LGPL-V3
- KDE (http://www.kde.org) sous licence LGPL-V2

Librairie FPDF : http://fpdf.org (licence permissive sans restriction d'usage)

=======================================================================================================
[CECILL-B]

Ce logiciel est un programme informatique permettant � des candidats de d�poser un ou plusieurs
dossiers de candidatures dans une universit�, et aux gestionnaires de cette derni�re de traiter ces
demandes.

Ce logiciel est r�gi par la licence CeCILL-B soumise au droit fran�ais et respectant les principes de
diffusion des logiciels libres. Vous pouvez utiliser, modifier et/ou redistribuer ce programme sous les
conditions de la licence CeCILL-B telle que diffus�e par le CEA, le CNRS et l'INRIA sur le site
"http://www.cecill.info".

En contrepartie de l'accessibilit� au code source et des droits de copie, de modification et de
redistribution accord�s par cette licence, il n'est offert aux utilisateurs qu'une garantie limit�e.
Pour les m�mes raisons, seule une responsabilit� restreinte p�se sur l'auteur du programme, le titulaire
des droits patrimoniaux et les conc�dants successifs.

A cet �gard l'attention de l'utilisateur est attir�e sur les risques associ�s au chargement, �
l'utilisation, � la modification et/ou au d�veloppement et � la reproduction du logiciel par l'utilisateur
�tant donn� sa sp�cificit� de logiciel libre, qui peut le rendre complexe � manipuler et qui le r�serve
donc � des d�veloppeurs et des professionnels avertis poss�dant  des  connaissances informatiques
approfondies. Les utilisateurs sont donc invit�s � charger et tester l'ad�quation du logiciel � leurs
besoins dans des conditions permettant d'assurer la s�curit� de leurs syst�mes et ou de leurs donn�es et,
plus g�n�ralement, � l'utiliser et l'exploiter dans les m�mes conditions de s�curit�.

Le fait que vous puissiez acc�der � cet en-t�te signifie que vous avez pris connaissance de la licence
CeCILL-B, et que vous en avez accept� les termes.

=======================================================================================================
*/
?>
<?php
	session_name("preinsc_gestion");
	session_start();

	include "../configuration/aria_config.php";
   include "$__INCLUDE_DIR_ABS/vars.php";
	include "$__INCLUDE_DIR_ABS/fonctions.php";
	include "$__INCLUDE_DIR_ABS/db.php";

	$php_self=$_SERVER['PHP_SELF'];
	$_SESSION['CURRENT_FILE']=$php_self;

	verif_auth();

	$dbr=db_connect();

	// D�verrouillage, au cas o�
	if(isset($_SESSION["candidat_id"]))
		cand_unlock($dbr, $_SESSION["candidat_id"]);

   if($_SESSION["niveau"]==$__LVL_SUPPORT)
   {
      header("Location:recherche.php");
      exit();  
   }

	// tri & filtre
	if(isset($_GET["t"]) && is_numeric($_GET["t"]) && $_GET["t"]>=0 & $_GET["t"]<5) // tri
		$_SESSION['tri']=$_GET["t"];

	// Par d�faut : tri par date
	if(!isset($_SESSION["tri"]))
		$_SESSION["tri"]=0;

	// filtre sur une formation
	if(isset($_POST["valider"]) || isset($_POST["valider_x"]) || isset($_POST["defaut"]) || isset($_POST["defaut_x"]))
	{
		if(isset($_POST["filiere"]) && $_POST["filiere"]!="")
		{
			$_SESSION["filtre_propspec"]=$_POST["filiere"];

			if(isset($_POST["defaut"]) || isset($_POST["defaut_x"])) // on conserve la valeur par d�faut dans la base annuaire
			{
				$_SESSION["spec_filtre_defaut"]=$_SESSION["filtre_propspec"];
				db_query($dbr,"UPDATE $_DB_acces SET $_DBU_acces_filtre='$_SESSION[spec_filtre_defaut]' WHERE $_DBU_acces_id='$_SESSION[auth_id]'");
			}
		}
	}

	// Filtre par d�faut
	if(!isset($_SESSION["filtre_propspec"]) && isset($_SESSION['spec_filtre_defaut']))
		$_SESSION["filtre_propspec"]=$_SESSION['spec_filtre_defaut'];

	// Nettoyage de variables utilis�es ailleurs
	unset($_SESSION["cursus_a_valider"]);
	unset($_SESSION["cursus_transfert"]);
	unset($_SESSION["candidatures_transfert"]);
	// unset($_SESSION["candidat_id"]);
	unset($_SESSION["tab_candidatures"]);
	// unset($_SESSION["tab_candidat"]);
	unset($_SESSION["fiche_id"]);
	unset($_SESSION["dco"]);

	unset($_SESSION["filtre_dossier"]);
	unset($_SESSION["filtre_justif"]);

	$_SESSION["onglet"]=1; // onglet par d�faut : identit� du candidat


	// D�termination du mode d'affichage : pr�candidatures ou commission p�dagogique
	if(isset($_GET["mode"]) && ($_GET["mode"]==$__MODE_COMPEDA || $_GET["mode"]==$__MODE_PREC))
		$_SESSION["mode"]=$_GET["mode"];

	if(!isset($_SESSION["mode"]))
		$_SESSION["mode"]=$__MODE_PREC;

	if($_SESSION["mode"]==$__MODE_PREC)
		$mode_txt="Pr�candidatures : Recevabilit�";
	else
		$mode_txt="D�cisions de Commission P�dagogique";


	// EN-TETE
	en_tete_gestion();

	// MENU SUPERIEUR
	menu_sup_gestion();
?>

<div class='main' style='padding-left:4px; padding-right:4px;'>
	<!-- Attention : sur cette page, on ne peut pas encore utiliser la fonction "titre_page_icone" � cause du changement de mode Pr�candidatures / Com. P�da. -->
	<table cellpadding='8' border='0' align='left' cellspacing='0' width='100%'>
	<tr>
		<td align='center' width='44'>
			<img src='<?php echo "$__ICON_DIR/bell_32x32_fond.png" ?>' border='0' alt=''>
		</td>
		<td align='left'>
			<font class='TitrePage_16'>
				<?php echo "$mode_txt ($__PERIODE - ".($__PERIODE+1).")"; ?>
			</font>
		</td>
		<?php
			if(in_array($_SESSION["niveau"], array("$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
			{
		?>
		<td align='right'>
			<?php
				if($_SESSION["mode"]==$__MODE_PREC)
					print("<a href='$php_self?mode=$__MODE_COMPEDA'><img src='$__ICON_DIR/reload_32x32_fond.png' border='0' alt=''></a>\n");
				else
					print("<a href='$php_self?mode=$__MODE_PREC'><img src='$__ICON_DIR/reload_32x32_fond.png' border='0' alt=''></a>\n");
			?>
		</td>
		<td align='center' width='20'>
			<?php
				if($_SESSION["mode"]==$__MODE_PREC)
					print("<a href='$php_self?mode=$__MODE_COMPEDA' class='lien_bleu_14'><b>Passer&nbsp;en&nbsp;mode<br>Commission&nbsp;P�dagogique</b></a>\n");
				else
					print("<a href='$php_self?mode=$__MODE_PREC' class='lien_bleu_14'><b>Passer&nbsp;en&nbsp;mode<br>Pr�candidatures</b></a>\n");
			?>
		</td>
		<?php
			}
		?>
	</tr>
	</table>

	<br clear='all'>
	<?php
		if(in_array($_SESSION["niveau"], array("$__LVL_SAISIE","$__LVL_SCOL_MOINS","$__LVL_SCOL_PLUS","$__LVL_RESP","$__LVL_SUPER_RESP","$__LVL_ADMIN")))
			message("Ces listes contiennent <b>uniquement</b> les candidatures verrouill�es que vous pouvez traiter.
						<br>Pour voir toutes les fiches, cliquez sur <b>Toutes les fiches</b> dans le menu du bandeau sup�rieur.",$__WARNING);
		else
			print("<br>\n");
	?>

	<font class='Texte'>
	<b>Trier les fiches : </b></font>
	<?php
		// Les tris sont diff�rents en fonction du mode
		if($_SESSION["mode"]==$__MODE_PREC)
		{
			switch($_SESSION["tri"])
			{
				case 0:	print("<font class='Texte'><b>par date</b></font>&nbsp;&nbsp;<a href='index.php?t=1' class='lien_bleu'><b>par nom</b></a>&nbsp;&nbsp;<a href='index.php?t=2' class='lien_bleu'><b>par formation</b></a>&nbsp;&nbsp;<a href='index.php?t=3' class='lien_bleu'><b>par moyenne du dernier dipl�me</b></a>");
							$ordre_tri="date";
							$order_by="$_DBC_cand_id"; // l'identifiant d'une candidature est un timestamp = date � laquelle la candidature a �t� entr�e
							break;

				case 1:	print("<a href='index.php?t=0' class='lien_bleu'><b>par date</b></a>&nbsp;&nbsp;<font class='Texte'><b>par nom</b></font>&nbsp;&nbsp;<a href='index.php?t=2' class='lien_bleu'><b>par formation</b></a>&nbsp;&nbsp;<a href='index.php?t=3' class='lien_bleu'><b>par moyenne du dernier dipl�me</b></a>");
							$ordre_tri="nom";
							$order_by="$_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_propspec_annee, $_DBC_propspec_id_spec";
							break;

				case 2:	print("<a href='index.php?t=0' class='lien_bleu'><b>par date</b></a>&nbsp;&nbsp;<a href='index.php?t=1' class='lien_bleu'><b>par nom</b></a>&nbsp;&nbsp;<font class='Texte'><b>par formation</b></font>&nbsp;&nbsp;<a href='index.php?t=3' class='lien_bleu'><b>par moyenne du dernier dipl�me</b></a>");
							$ordre_tri="formation";
							$order_by="$_DBC_annees_ordre, $_DBC_propspec_id_spec, $_DBC_cand_id";
							break;

				case 3:	print("<a href='index.php?t=0' class='lien_bleu'><b>par date</b></a>&nbsp;&nbsp;<a href='index.php?t=1' class='lien_bleu'><b>par nom</b></a>&nbsp;&nbsp;<a href='index.php?t=2' class='lien_bleu'><b>par formation</b></a>&nbsp;&nbsp;<font class='Texte'><b>par moyenne du dernier dipl�me</b></font>");
							$ordre_tri="note moyenne";
							// cas particulier : le tri ne se fait pas dans la table Candidats (mais on doit quand m�me mettre l'ordre ici)
							$order_by="$_DBC_cand_id";
							break;

				default:	print("<font class='Texte'><b>par date</b></font>&nbsp;&nbsp;<a href='index.php?t=1' class='lien_bleu'><b>par nom</b></a>&nbsp;&nbsp;<a href='index.php?t=2' class='lien_bleu'><b>par formation</b></a>&nbsp;&nbsp;<a href='index.php?t=3' class='lien_bleu'><b>par moyenne du dernier dipl�me</b></a>");
							$ordre_tri="date";
							$order_by="$_DBC_cand_id"; // l'identifiant d'une candidature est un timestamp = date � laquelle la candidature a �t� entr�e
							break;
			}
		}
		else
		{
			switch($_SESSION["tri"])
			{
				case 0:	print("<font class='Texte'><b>par date</b></font>&nbsp;&nbsp;<a href='index.php?t=1' class='lien_bleu'><b>par nom</b></a>&nbsp;&nbsp;<a href='index.php?t=2' class='lien_bleu'><b>par formation</b></a>&nbsp;&nbsp;<a href='index.php?t=3' class='lien_bleu'><b>par moyenne du dernier dipl�me</b></a>&nbsp;&nbsp;<a href='index.php?t=4' class='lien_bleu'><b>par date de recevabilit� (d�cr.)</b></a>");
							$ordre_tri="date";
							$order_by="$_DBC_cand_id"; // l'identifiant d'une candidature est un timestamp = date � laquelle la candidature a �t� entr�e
							break;

				case 1:	print("<a href='index.php?t=0' class='lien_bleu'><b>par date</b></a>&nbsp;&nbsp;<font class='Texte'><b>par nom</b></font>&nbsp;&nbsp;<a href='index.php?t=2' class='lien_bleu'><b>par formation</b></a>&nbsp;&nbsp;<a href='index.php?t=3' class='lien_bleu'><b>par moyenne du dernier dipl�me</b></a>&nbsp;&nbsp;<a href='index.php?t=4' class='lien_bleu'><b>par date de recevabilit� (d�cr.)</b></a>");
							$ordre_tri="nom";
							$order_by="$_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_propspec_annee, $_DBC_propspec_id_spec";
							break;

				case 2:	print("<a href='index.php?t=0' class='lien_bleu'><b>par date</b></a>&nbsp;&nbsp;<a href='index.php?t=1' class='lien_bleu'><b>par nom</b></a>&nbsp;&nbsp;<font class='Texte'><b>par formation</b></font>&nbsp;&nbsp;<a href='index.php?t=3' class='lien_bleu'><b>par moyenne du dernier dipl�me</b></a>&nbsp;&nbsp;<a href='index.php?t=4' class='lien_bleu'><b>par date de recevabilit� (d�cr.)</b></a>");
							$ordre_tri="formation";
							$order_by="$_DBC_annees_ordre, $_DBC_propspec_id_spec, $_DBC_cand_id";
							break;

				case 3:	print("<a href='index.php?t=0' class='lien_bleu'><b>par date</b></a>&nbsp;&nbsp;<a href='index.php?t=1' class='lien_bleu'><b>par nom</b></a>&nbsp;&nbsp;<a href='index.php?t=2' class='lien_bleu'><b>par formation</b></a>&nbsp;&nbsp;<font class='Texte'><b>par moyenne du dernier dipl�me</b></font>&nbsp;&nbsp;<a href='index.php?t=4' class='lien_bleu'><b>par date de recevabilit� (d�cr.)</b></a>");
							$ordre_tri="note moyenne";
							// cas particulier : le tri ne se fait pas dans la table Candidats (mais on doit quand m�me mettre l'ordre ici)
							$order_by="$_DBC_cand_id";
							break;

				// Tri par date de recevabilit� d�croissante / valable uniquement pour le mode commission (aucun sens sinon)
				case 4:	print("<a href='index.php?t=0' class='lien_bleu'><b>par date</b></a>&nbsp;&nbsp;<a href='index.php?t=1' class='lien_bleu'><b>par nom</b></a>&nbsp;&nbsp;<a href='index.php?t=2' class='lien_bleu'><b>par formation</b></a>&nbsp;&nbsp;<a href='index.php?t=3' class='lien_bleu'><b>par moyenne du dernier dipl�me</b></a>&nbsp;&nbsp;<font class='Texte'><b>par date de recevabilit� (d�cr.)</b></font>");
							$ordre_tri="date de recevabilit� d�croissante";
							$order_by="$_DBC_cand_date_statut DESC";
							break;

				default:	print("<font class='Texte'><b>par date</b></font>&nbsp;&nbsp;<a href='index.php?t=1' class='lien_bleu'><b>par nom</b></a>&nbsp;&nbsp;<a href='index.php?t=2' class='lien_bleu'><b>par formation</b></a>&nbsp;&nbsp;<a href='index.php?t=3' class='lien_bleu'><b>par moyenne du dernier dipl�me</b></a>&nbsp;&nbsp;<a href='index.php?t=4' class='lien_bleu'><b>par date de recevabilit� (d�cr.)</b></a>");
							$ordre_tri="date";
							$order_by="$_DBC_cand_id"; // l'identifiant d'une candidature est un timestamp = date � laquelle la candidature a �t� entr�e
							break;
			}
		}

		// Filtre
		if($_SESSION["filtre_propspec"]!=-1)
		{
			$select_ordre_spec=", $_DBC_cand_ordre_spec";
			$filtre="AND $_DBC_propspec_id='$_SESSION[filtre_propspec]'";
			$filtre_statut="<font class='Texte_important'><b>(filtre activ�)</b></font>";
		}
		else
		{
			$filtre=$select_ordre_spec="";
			$filtre_statut="<font class='Textevert'><b>(filtre d�sactiv�)</b></font>";
		}
	?>
	<form action='index.php' method='POST' name='form1'>
	<br>
	<font class='Texte'><b>Filtrer par Formation : </b></font>
	<select size="1" name="filiere">
		<option value="-1">Montrer toutes les formations</option>
		<option value="-1" disabled='1'></option>
		<?php
			// On ne propose que les formations pour lesquelles des candidatures existent et auxquelles l'utilisateur a acc�s
			$requete_droits_formations=requete_auth_droits($_SESSION["comp_id"]);
			
			$result=db_query($dbr,"SELECT $_DBC_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
													$_DBC_propspec_manuelle
											FROM $_DB_propspec, $_DB_annees, $_DB_specs
										WHERE $_DBC_propspec_id_spec=$_DBC_specs_id
										AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
										AND $_DBC_propspec_annee=$_DBC_annees_id
										AND $_DBC_propspec_id IN (SELECT distinct($_DBC_cand_propspec_id) FROM $_DB_cand, $_DB_propspec
																			WHERE $_DBC_cand_propspec_id=$_DBC_propspec_id
																			AND $_DBC_propspec_comp_id='$_SESSION[comp_id]'
																			AND $_DBC_cand_periode='$__PERIODE')
										$requete_droits_formations
											ORDER BY $_DBC_annees_ordre, $_DBC_specs_nom");
			$rows=db_num_rows($result);

			$prev_annee="--"; // variable initialis�e � n'importe quoi (sauf ann�e existante �videmment)

			for($i=0; $i<$rows; $i++)
			{
				list($propspec_id, $annee, $nom,$finalite, $manuelle)=db_fetch_row($result,$i);

				if($annee!=$prev_annee)
				{
					if($i!=0)
						print("</optgroup>\n");

					if(empty($annee))
						print("<optgroup label='Ann�es particuli�res'>\n");
					else
						print("<optgroup label='$annee'>\n");

					$prev_annee=$annee;
				}

				$selected=$_SESSION["filtre_propspec"]==$propspec_id ? "selected=1" : "";

				$manuelle_txt=$manuelle ? "(M)" : "";

				print("<option value='$propspec_id' label=\"$annee - $nom $tab_finalite[$finalite] $manuelle_txt\" $selected>$annee - $nom $tab_finalite[$finalite] $manuelle_txt</option>\n");
			}
			db_free_result($result);
		?>
	</select>
	&nbsp;&nbsp;<input type='submit' name='valider' value='Valider'>&nbsp;&nbsp;<input type='submit' name='defaut' value='D�finir ce filtre par d�faut'>&nbsp;&nbsp;&nbsp;<?php print("$filtre_statut"); ?>
	<br><font class='Texte_10'><i>Seules les formations pour lesquelles des candidatures ont �t� d�pos�es sont propos�es.</i>
	</form>

	<br>

	<table width='100%' border='0' cellspacing='5' cellpadding='0' align='center'>
	<tr>
		<td valign='top' align='left' width='50%'>
		<?php
			$texte1="Texte";
			$texte2="Texte_menu";

			$fond1="fond_page";
			$fond2="fond_menu";

			$lien1="lien2a";
			$lien2="lien_menu_gauche";

			$icone_manuelle1="contact-new_16x16_fond.png";
			$icone_manuelle2="contact-new_16x16_menu.png";			

			if($_SESSION["tri"]!=3) // Tri 3 : par moyenne (n�cessite un traitement � part)
			{
				if($_SESSION["mode"]==$__MODE_PREC)
					// R�cup�ration des pr�candidatures non trait�es : (statut=0), en fonction de la m�thode de tri s�lectionn�e
					$result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_cand_id, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_cand_propspec_id,
															$_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite, $_DBC_candidat_manuelle,
															$_DBC_cand_date_statut $select_ordre_spec,
														CASE WHEN $_DBC_candidat_id IN (SELECT $_DBC_acces_candidats_lus_candidat_id
																									FROM $_DB_acces_candidats_lus
																									WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
																									AND $_DBC_acces_candidats_lus_periode='$__PERIODE')
																THEN '1' ELSE '0' END AS vu
														FROM $_DB_candidat, $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec
													WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
													AND $_DBC_propspec_annee=$_DBC_annees_id
													AND $_DBC_propspec_id_spec=$_DBC_specs_id
													AND $_DBC_propspec_id=$_DBC_cand_propspec_id
													AND $_DBC_candidat_id=$_DBC_cand_candidat_id
													AND $_DBC_cand_statut='$__PREC_NON_TRAITEE'
													AND $_DBC_cand_lock='1'
													AND $_DBC_cand_periode='$__PERIODE'
													$requete_droits_formations
													$filtre
														ORDER BY $order_by");
				elseif($_SESSION["mode"]==$__MODE_COMPEDA)
					$result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_cand_id, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_cand_propspec_id,
															$_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite, $_DBC_candidat_manuelle,
															$_DBC_cand_date_statut $select_ordre_spec,
															CASE WHEN $_DBC_candidat_id IN (SELECT $_DBC_acces_candidats_lus_candidat_id
																									FROM $_DB_acces_candidats_lus
																									WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
																									AND $_DBC_acces_candidats_lus_periode='$__PERIODE')
																	THEN '1' ELSE '0' END AS vu
														FROM $_DB_candidat, $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec
													WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
													AND $_DBC_propspec_annee=$_DBC_annees_id
													AND $_DBC_propspec_id_spec=$_DBC_specs_id
													AND $_DBC_propspec_id=$_DBC_cand_propspec_id
													AND $_DBC_candidat_id=$_DBC_cand_candidat_id
													AND $_DBC_cand_statut='$__PREC_RECEVABLE'
													AND $_DBC_cand_decision='$__DOSSIER_NON_TRAITE'
													AND $_DBC_cand_lock='1'
													AND $_DBC_cand_periode='$__PERIODE'
													$requete_droits_formations
													$filtre
														ORDER BY $order_by");

				$rows=db_num_rows($result);

				if($rows)
				{
					$s=($rows>1) ? "s" : "";

					if($_SESSION["mode"]==$__MODE_PREC)
						print("<font class='Texte3'><b>$rows pr�candidature$s non trait�e$s (tri par $ordre_tri) : </b></font><br><br>
									<table width='100%' border='0' cellspacing='0' cellpadding='4' align='left'>");
					else
						print("<font class='Texte3'><b>$rows fiche$s en attente de d�cision (tri par $ordre_tri) : </b></font><br><br>
									<table width='100%' border='0' cellspacing='0' cellpadding='4' align='left'>");

					// variables initialis�es � n'importe quoi
					$old_prenom=$old_nom=$old_date="0";

					// Affichage des pr�candidatures si le tri n'est pas par note moyenne

					$tab_finalite=array("0" => "", "1" => "- Recherche", "2" => "- Pro");

					for($i=0; $i<$rows; $i++)
					{
						if($select_ordre_spec!="")
						{
							list($candidat_id,$candidature_id,$nom,$prenom,$propspec_id, $nom_annee, $nom_spec, $finalite,
								  $fiche_manuelle, $date_statut, $ordre_spec, $vu)=db_fetch_row($result,$i);

							$ordre_spec_txt=$ordre_spec!=-1 ? "$ordre_spec - " : "";
						}
						else
						{
							list($candidat_id,$candidature_id,$nom,$prenom,$propspec_id, $nom_annee, $nom_spec, $finalite,
							$fiche_manuelle, $date_statut, $vu)=db_fetch_row($result,$i);

							$ordre_spec_txt="";
						}

						// En fonction du tri, on n'affiche pas la m�me date
						if(isset($_SESSION["tri"]) && $_SESSION["tri"]!=4)
							$date_creation=date_fr("j F y", id_to_date($candidature_id));
						else
							$date_creation=date_fr("j F y", $date_statut);

						// affichage uniquement si informations diff�rentes

						if($nom==$old_nom && $prenom==$old_prenom)
							$nom=$prenom=$date_creation="";
						else
						{
							$date_creation="- $date_creation";

							switch_vals($fond1, $fond2);
							switch_vals($lien1, $lien2);
							switch_vals($texte1, $texte2);
							switch_vals($icone_manuelle1, $icone_manuelle2);

							$old_date=$date_creation;
							$old_nom=$nom;
							$old_prenom=$prenom;
						}

						// $nom_finalite=$tab_finalite[$finalite];

						print("<tr>
									<td class='$fond1' nowrap='true'>
										<font class='$texte1'>$date_creation</font>
									</td>\n");

						if($fiche_manuelle)
							$td_manuelle="<td class='$fond1' align='center' width='22'>
													<img src='$__ICON_DIR/$icone_manuelle1' alt='Fiche manuelle' desc='Fiche cr��e manuellement' border='0'>
												</td>\n";
						else
							$td_manuelle="<td class='$fond1'></td>\n";

						$link_class=isset($vu) && $vu ? "lien_vu_12" : "$lien1";

						print("$td_manuelle
								<td class='$fond1' nowrap='true'>
									<a href='edit_candidature.php?cid=$candidat_id' class='$link_class'><b>$nom $prenom</b></a>
								</td>
								<td class='$fond1'>
									<a href='edit_candidature.php?cid=$candidat_id' class='$link_class'>$ordre_spec_txt$nom_annee $nom_spec $tab_finalite[$finalite]</a>
								</td>
							</tr>\n");
					}

					db_free_result($result);

					print("</table>\n");
				}
				else
				{
					if($_SESSION["mode"]==$__MODE_PREC)
						print("<font class='Texte3'><b>Aucune pr�candidature non trait�e.</b></font><br>");
					else
						print("<font class='Texte3'><b>Aucune d�cision de Commission non rendue.</b></font><br>");
				}
			}
			else
			{
				if($filtre!="")
				{
					if($_SESSION["mode"]==$__MODE_PREC)
						// R�cup�ration des pr�candidatures non trait�es : (statut=0), en fonction de la m�thode de tri s�lectionn�e
						$result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_cand_id, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_manuelle,
																$_DBC_cand_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
																$_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec, $_DBC_cursus_annee,
																$_DBC_cursus_mention, $_DBC_cursus_ecole, $_DBC_cursus_ville, 
																CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
																	THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
																	ELSE '' END as cursus_pays,
																$_DBC_cursus_moyenne,
														CASE WHEN $_DBC_cursus_annee='0' THEN '9999' ELSE $_DBC_cursus_annee END AS ordre,
														CASE WHEN $_DBC_candidat_id IN (SELECT $_DBC_acces_candidats_lus_candidat_id
																										FROM $_DB_acces_candidats_lus
																									WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
																									AND $_DBC_acces_candidats_lus_periode='$__PERIODE')
																		THEN '1' ELSE '0' END AS vu
															FROM $_DB_candidat, $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec, $_DB_cursus
														WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
														AND $_DBC_cursus_candidat_id=$_DBC_candidat_id
														AND $_DBC_propspec_annee=$_DBC_annees_id
														AND $_DBC_propspec_id_spec=$_DBC_specs_id
														AND $_DBC_propspec_id=$_DBC_cand_propspec_id
														AND $_DBC_candidat_id=$_DBC_cand_candidat_id
														AND $_DBC_cand_statut='$__PREC_NON_TRAITEE'
														AND $_DBC_cand_lock='1'
														AND $_DBC_cand_periode='$__PERIODE'
														$requete_droits_formations
														$filtre
															ORDER BY $_DBC_candidat_id, $_DBC_cand_ordre DESC");

					elseif($_SESSION["mode"]==$__MODE_COMPEDA)
						$result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_cand_id, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_manuelle,
																$_DBC_cand_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
																$_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec, $_DBC_cursus_annee,
																$_DBC_cursus_mention, $_DBC_cursus_ecole, $_DBC_cursus_ville, 
																CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
																	THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
																	ELSE '' END as cursus_pays,
																$_DBC_cursus_moyenne,
															CASE WHEN $_DBC_cursus_annee='0' THEN '9999' ELSE $_DBC_cursus_annee END AS ordre,
															CASE WHEN $_DBC_candidat_id IN (SELECT $_DBC_acces_candidats_lus_candidat_id
																										FROM $_DB_acces_candidats_lus
																										WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
																										AND $_DBC_acces_candidats_lus_periode='$__PERIODE')
																	THEN '1' ELSE '0' END AS vu
															FROM $_DB_candidat, $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec, $_DB_cursus
														WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
														AND $_DBC_cursus_candidat_id=$_DBC_candidat_id
														AND $_DBC_propspec_annee=$_DBC_annees_id
														AND $_DBC_propspec_id_spec=$_DBC_specs_id
														AND $_DBC_propspec_id=$_DBC_cand_propspec_id
														AND $_DBC_candidat_id=$_DBC_cand_candidat_id
														AND $_DBC_cand_statut='$__PREC_RECEVABLE'
														AND $_DBC_cand_decision='$__DOSSIER_NON_TRAITE'
														AND $_DBC_cand_lock='1'
														AND $_DBC_cand_periode='$__PERIODE'
														$requete_droits_formations
														$filtre
															ORDER BY $_DBC_candidat_id, $_DBC_cand_ordre DESC");

					$rows=db_num_rows($result);

					if($rows)
					{
						$s=$rows>1 ? "s" : "";

						// variables initialis�es � n'importe quoi
						$old_prenom=$old_nom=$old_date="0";

						// Affichage des pr�candidatures si le tri n'est pas par note moyenne

						$candidats_array=array();

						$old_candidature="--";
						$old_candidat_id="--";

						for($i=0; $i<$rows; $i++)
						{
							list($candidat_id,$candidature_id,$nom,$prenom, $fiche_manuelle, $propspec_id, $nom_annee, $nom_spec,
									$finalite, $cursus_diplome, $cursus_intitule,$cursus_spec,$cursus_annee_obtention, $cursus_mention,
									$cursus_ecole, $cursus_ville, $cursus_pays, $cursus_moyenne,$ordre,$vu)=db_fetch_row($result,$i);

							// $date_creation=date_fr("j F y", id_to_date($candidature_id));

							// affichage uniquement si informations diff�rentes

							if($candidat_id!=$old_candidat_id)
							{
								$candidats_array[$candidat_id]=array();
								$candidats_array[$candidat_id]['id']=$candidat_id;
								$candidats_array[$candidat_id]['nom']=$nom;
								$candidats_array[$candidat_id]['prenom']=$prenom;
								$candidats_array[$candidat_id]['fiche_manuelle']=$fiche_manuelle;
								$candidats_array[$candidat_id]['moyenne']=$cursus_moyenne;
								$candidats_array[$candidat_id]['vu']=$vu;

								if($cursus_annee_obtention=="0" || $cursus_annee_obtention=="")
									$cursus_annee_obtention="En cours";

								$mention=$cursus_mention!="" ? "- <b>Mention</b> : $cursus_mention" : "";

								$lieu=ucwords(mb_strtolower($cursus_ecole)) . ", " . ucwords(mb_strtolower($cursus_ville)) . ", " . ucwords(mb_strtolower($cursus_pays));

								$cursus_intitule=ucwords(mb_strtolower($cursus_intitule));

								$candidats_array[$candidat_id]['diplome']="<br>
																						<font class='Texte'>
																							<b>$cursus_annee_obtention</b> : $cursus_diplome
																							<br><b>Intitul�</b> : $cursus_intitule
																							<br><b>Lieu</b> : $lieu
																							<br><b>Moyenne</b> : $cursus_moyenne $mention
																						</font>";
							}

							$old_candidat_id=$candidat_id;
						}

						db_free_result($result);

						if($cnt=count($candidats_array))
						{
							// Attention : avec usort, les cl�s (id des candidats) du tableau $candidats_array sont perdues
							usort($candidats_array,"cmp_moyenne_diplome");

							// $nom_finalite=$tab_finalite[$finalite];
							$formation="$nom_annee, $nom_spec, $tab_finalite[$finalite]";

							if($_SESSION["mode"]==$__MODE_PREC)
								print("<font class='Texte3'><b>$cnt pr�candidature$s non trait�e$s (tri par $ordre_tri) : </b></font><br><br>
											<table width='100%' border='0' cellspacing='0' cellpadding='4' align='left'>");
							else
								print("<font class='Texte3'><b>$cnt fiche$s en attente de d�cision (tri par $ordre_tri) : </b></font><br><br>
											<table width='100%' border='0' cellspacing='0' cellpadding='4' align='left'>");

							foreach($candidats_array as $key_candidat => $candidat)
							{
								// print("<tr>\n");

								if($candidat["fiche_manuelle"])
									$td_manuelle="<td class='$fond1' align='center' width='22'>
															<img src='$__ICON_DIR/$icone_manuelle1' alt='Fiche manuelle' desc='Fiche cr��e manuellement' border='0'>
														</td>\n";
								else
									$td_manuelle="<td class='$fond1'></td>\n";

								$link_class=isset($candidat["vu"]) && $candidat["vu"] ? "lien_vu_12" : "$lien1";

								print("<tr>
											$td_manuelle
											<td class='$fond1'>
												<a href='edit_candidature.php?cid=$candidat[id]' class='$link_class'><b>$candidat[nom] $candidat[prenom]</b></a>
												$candidat[diplome]
											</td>
											<td class='$fond1'>
												<a href='edit_candidature.php?cid=$candidat[id]' class='$link_class'>$nom_annee $nom_spec $tab_finalite[$finalite]</a>
											</td>
										</tr>\n");

								switch_vals($fond1, $fond2);
								switch_vals($lien1, $lien2);
								switch_vals($texte1, $texte2);
								switch_vals($icone_manuelle1, $icone_manuelle2);
							}
						}

						print("</table>\n");
					}
					else
					{
						if($_SESSION["mode"]==$__MODE_PREC)
							print("<font class='Texte3'><b>Aucune pr�candidature partiellement trait�e.</b></font><br>");
						else
							print("<font class='Texte3'><b>Aucune d�cision de Commission partiellement rendue.</b></font><br>");
					}
				}
			}
		?>
		</td>
		<td valign='top' align='left' width='50%'>
		<?php
			// r�initialisation de l'ordre des couleurs
			$texte1="Texte_menu";
			$texte2="Texte";

			$fond1="fond_menu";
			$fond2="fond_page";

			$lien1="lien_menu_gauche";
			$lien2="lien2a";

			$icone_manuelle1="contact-new_16x16_menu.png";
			$icone_manuelle2="contact-new_16x16_fond.png";

			if($_SESSION["tri"]!=3)
			{
				if($_SESSION["mode"]==$__MODE_PREC)
					// R�cup�ration des pr�candidatures EN ATTENTE (statut = 2), en fonction de la m�thode de tri s�lectionn�e
					$result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_cand_id, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_cand_propspec_id,
															$_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite, $_DBC_candidat_manuelle,
															$_DBC_decisions_texte,
													CASE WHEN $_DBC_candidat_id IN (SELECT $_DBC_acces_candidats_lus_candidat_id
																								FROM $_DB_acces_candidats_lus
																								WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
																								AND $_DBC_acces_candidats_lus_periode='$__PERIODE')
															THEN '1' ELSE '0' END AS vu
													FROM $_DB_candidat, $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec, $_DB_decisions
													WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
													AND $_DBC_propspec_annee=$_DBC_annees_id
													AND $_DBC_decisions_id=$_DBC_cand_decision
													AND $_DBC_propspec_id_spec=$_DBC_specs_id
													AND $_DBC_propspec_id=$_DBC_cand_propspec_id
													AND $_DBC_candidat_id=$_DBC_cand_candidat_id
													AND $_DBC_cand_statut='$__PREC_EN_ATTENTE'
													AND $_DBC_cand_lock='1'
													AND $_DBC_cand_periode='$__PERIODE'
													$requete_droits_formations
													$filtre
												ORDER BY $order_by");

				elseif($_SESSION["mode"]==$__MODE_COMPEDA)
					$result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_cand_id, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_cand_propspec_id,
															$_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite, $_DBC_candidat_manuelle,
															$_DBC_decisions_texte,
														CASE WHEN $_DBC_candidat_id IN (SELECT $_DBC_acces_candidats_lus_candidat_id
																									FROM $_DB_acces_candidats_lus
																									WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
																									AND $_DBC_acces_candidats_lus_periode='$__PERIODE')
																THEN '1' ELSE '0' END AS vu
													FROM $_DB_candidat, $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec, $_DB_decisions
													WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
														AND $_DBC_propspec_annee=$_DBC_annees_id
														AND $_DBC_decisions_id=$_DBC_cand_decision
														AND $_DBC_propspec_id_spec=$_DBC_specs_id
														AND $_DBC_propspec_id=$_DBC_cand_propspec_id
														AND $_DBC_candidat_id=$_DBC_cand_candidat_id
														AND $_DBC_cand_statut='$__PREC_RECEVABLE'
														AND $_DBC_cand_decision<'$__DOSSIER_NON_TRAITE'
														AND $_DBC_cand_lock='1'
														AND $_DBC_cand_periode='$__PERIODE'
														$requete_droits_formations
														$filtre
													ORDER BY $order_by");


				$rows=db_num_rows($result);

				if($rows)
				{
					if($rows>1)
						$s="s";
					else
						$s="";

					if($_SESSION["mode"]==$__MODE_PREC)
						print("<font class='Texte3'><b>$rows pr�candidature$s partiellement trait�e$s (tri par $ordre_tri) : </b></font><br><br>
									<table width='100%' border='0' cellspacing='0' cellpadding='4' align='left'>");
					else
						print("<font class='Texte3'><b>$rows d�cision$s de Commission partielle$s (tri par $ordre_tri) : </b></font><br><br>
									<table width='100%' border='0' cellspacing='0' cellpadding='4' align='left'>");

					// Affichage des pr�candidatures
					for($i=0; $i<$rows; $i++)
					{
						list($candidat_id, $candidature_id, $nom,$prenom, $propspec_id, $nom_annee, $nom_spec, $finalite, $fiche_manuelle, $decision, $vu)=db_fetch_row($result,$i);

						$date_creation=date_fr("j F y", id_to_date($candidature_id));

						// $nom_finalite=$tab_finalite[$finalite];

						if($fiche_manuelle)
							$td_manuelle="<td class='$fond1' align='center' width='22'>
													<img src='$__ICON_DIR/$icone_manuelle1' alt='Fiche manuelle' desc='Fiche cr��e manuellement' border='0'>
												</td>\n";
						else
							$td_manuelle="<td class='$fond1'></td>\n";

						print("<tr>
									<td class='$fond1' nowrap='true'>
										<font class='$texte1'>- $date_creation</font>
									</td>\n");

						$link_class=isset($vu) && $vu ? "lien_vu_12" : "$lien1";

						print("$td_manuelle
								<td class='$fond1' nowrap='true'>
									<a href='edit_candidature.php?cid=$candidat_id' class='$link_class'><b>$nom $prenom</b></a>
								</td>
								<td class='$fond1'>
									<a href='edit_candidature.php?cid=$candidat_id' class='$link_class'>$nom_annee $nom_spec $tab_finalite[$finalite]</a>
								</td>
							</tr>\n");

						if($_SESSION["mode"]==$__MODE_COMPEDA)
							print("<tr>
										<td class='$fond1' nowrap='true' colspan='2'></td>
										<td class='$fond1' nowrap='true'>
											<font class='$texte1'><i>D�cision actuelle : </i></font>
										</td>
										<td class='$fond1' nowrap='true' colspan='2'>
											<font class='$texte1'><i>$decision</i></font>
										</td>
									</tr>\n");

						// inversion des couleurs pour la ligne suivante
						switch_vals($fond1, $fond2);
						switch_vals($lien1, $lien2);
						switch_vals($texte1, $texte2);
						switch_vals($icone_manuelle1, $icone_manuelle2);
					}

					db_free_result($result);

					print("</table>\n");
				}
				else
				{
					if($_SESSION["mode"]==$__MODE_PREC)
						print("<font class='Texte3'><b>Aucune pr�candidature partiellement trait�e.</b></font><br>");
					else
						print("<font class='Texte3'><b>Aucune d�cision de Commission partiellement rendue.</b></font><br>");
				}
			}
			else
			{
				if($filtre!="")
				{
					if($_SESSION["mode"]==$__MODE_PREC)
						// R�cup�ration des pr�candidatures non trait�es : (statut=0), en fonction de la m�thode de tri s�lectionn�e
						$result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_cand_id, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_manuelle,
																$_DBC_cand_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
																$_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec,
																$_DBC_cursus_annee, $_DBC_cursus_mention, $_DBC_cursus_ecole, $_DBC_cursus_ville,
																CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
																	THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
																	ELSE '' END as cursus_pays,
																$_DBC_cursus_moyenne,
														CASE WHEN $_DBC_cursus_annee='0' THEN '9999' ELSE $_DBC_cursus_annee END AS ordre,
														CASE WHEN $_DBC_candidat_id IN (SELECT $_DBC_acces_candidats_lus_candidat_id
																									FROM $_DB_acces_candidats_lus
																								WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
																								AND $_DBC_acces_candidats_lus_periode='$__PERIODE')
																THEN '1' ELSE '0' END AS vu
														FROM $_DB_candidat, $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec, $_DB_cursus
														WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
														AND $_DBC_cursus_candidat_id=$_DBC_candidat_id
														AND $_DBC_propspec_annee=$_DBC_annees_id
														AND $_DBC_propspec_id_spec=$_DBC_specs_id
														AND $_DBC_propspec_id=$_DBC_cand_propspec_id
														AND $_DBC_candidat_id=$_DBC_cand_candidat_id
														AND $_DBC_cand_statut='$__PREC_EN_ATTENTE'
														AND $_DBC_cand_lock='1'
														AND $_DBC_cand_periode='$__PERIODE'
														$requete_droits_formations
														$filtre
															ORDER BY $_DBC_candidat_id, $_DBC_cand_ordre DESC");

					elseif($_SESSION["mode"]==$__MODE_COMPEDA)
						$result=db_query($dbr,"SELECT $_DBC_candidat_id, $_DBC_cand_id, $_DBC_candidat_nom, $_DBC_candidat_prenom, $_DBC_candidat_manuelle,
																$_DBC_cand_propspec_id, $_DBC_annees_annee, $_DBC_specs_nom_court, $_DBC_propspec_finalite,
																$_DBC_cursus_diplome, $_DBC_cursus_intitule, $_DBC_cursus_spec,
																$_DBC_cursus_annee, $_DBC_cursus_mention, $_DBC_cursus_ecole, $_DBC_cursus_ville,
																CASE WHEN $_DBC_cursus_pays IN (SELECT $_DBC_pays_nat_ii_iso FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays) 
																	THEN (SELECT $_DBC_pays_nat_ii_pays FROM $_DB_pays_nat_ii WHERE $_DBC_pays_nat_ii_iso=$_DBC_cursus_pays)
																	ELSE '' END as cursus_pays,
																$_DBC_cursus_moyenne,
														CASE WHEN $_DBC_cursus_annee='0' THEN '9999' ELSE $_DBC_cursus_annee END AS ordre,
														CASE WHEN $_DBC_candidat_id IN (SELECT $_DBC_acces_candidats_lus_candidat_id
																									FROM $_DB_acces_candidats_lus
																									WHERE $_DBC_acces_candidats_lus_acces_id='$_SESSION[auth_id]'
																									AND $_DBC_acces_candidats_lus_periode='$__PERIODE')
																THEN '1' ELSE '0' END AS vu
														FROM $_DB_candidat, $_DB_cand, $_DB_annees, $_DB_specs, $_DB_propspec, $_DB_cursus
														WHERE $_DBC_propspec_comp_id='$_SESSION[comp_id]'
														AND $_DBC_cursus_candidat_id=$_DBC_candidat_id
														AND $_DBC_propspec_annee=$_DBC_annees_id
														AND $_DBC_propspec_id_spec=$_DBC_specs_id
														AND $_DBC_propspec_id=$_DBC_cand_propspec_id
														AND $_DBC_candidat_id=$_DBC_cand_candidat_id
														AND $_DBC_cand_statut='$__PREC_RECEVABLE'
														AND $_DBC_cand_decision<'$__DOSSIER_NON_TRAITE'
														AND $_DBC_cand_lock='1'
														AND $_DBC_cand_periode='$__PERIODE'
														$requete_droits_formations
														$filtre
															ORDER BY $_DBC_candidat_id, ordre DESC");

					$rows=db_num_rows($result);

					if($rows)
					{
						if($rows>1)
							$s="s";
						else
							$s="";

						// variables initialis�es � n'importe quoi
						$old_prenom=$old_nom=$old_date="0";

						// Affichage des pr�candidatures si le tri n'est pas par note moyenne

						$candidats_array=array();

						$old_candidature="--";
						$old_candidat_id="--";

						for($i=0; $i<$rows; $i++)
						{
							list($candidat_id,$candidature_id,$nom,$prenom, $fiche_manuelle, $propspec_id, $nom_annee, $nom_spec, $finalite,
									$cursus_diplome, $cursus_intitule,$cursus_spec,$cursus_annee_obtention, $cursus_mention, $cursus_ecole,
									$cursus_ville, $cursus_pays, $cursus_moyenne, $vu)=db_fetch_row($result,$i);

							// $date_creation=date_fr("j F y", id_to_date($candidature_id));

							// affichage uniquement si informations diff�rentes

							if($candidat_id!=$old_candidat_id)
							{
								$candidats_array[$candidat_id]=array();
								$candidats_array[$candidat_id]['id']=$candidat_id;
								$candidats_array[$candidat_id]['nom']=$nom;
								$candidats_array[$candidat_id]['prenom']=$prenom;
								$candidats_array[$candidat_id]['fiche_manuelle']=$fiche_manuelle;
								$candidats_array[$candidat_id]['vu']=$vu;

								$candidats_array[$candidat_id]['moyenne']=$cursus_moyenne;

								if($cursus_annee_obtention=="0" || $cursus_annee_obtention=="")
									$cursus_annee_obtention="En cours";

								if($cursus_mention!="")
									$mention="- <b>Mention</b> : $cursus_mention";

								$lieu=ucwords(mb_strtolower($cursus_ecole)) . ", " . ucwords(mb_strtolower($cursus_ville)) . ", " . ucwords(mb_strtolower($cursus_pays));

								$candidats_array[$candidat_id]['diplome']="<br>
																							<font class='Texte'>
																								<b>$cursus_annee_obtention</b> : $cursus_diplome
																								<br><b>Intitul�</b> : $cursus_intitule
																								<br><b>Lieu</b> : $lieu
																								<br><b>Moyenne</b> : $cursus_moyenne $mention
																							</font>";
							}

							$old_candidat_id=$candidat_id;
						}

						db_free_result($result);

						if($cnt=count($candidats_array))
						{
							// Attention : avec usort, les cl�s (id des candidats) du tableau $candidats_array sont perdues
							usort($candidats_array,"cmp_moyenne_diplome");

							// $nom_finalite=$tab_finalite[$finalite];
							$formation="$nom_annee, $nom_spec, $tab_finalite[$finalite]";

							if($_SESSION["mode"]==$__MODE_PREC)
								print("<font class='Texte3'><b>$cnt pr�candidature$s non trait�e$s (tri par $ordre_tri) : </b></font><br><br>
											<table width='100%' border='0' cellspacing='0' cellpadding='4' align='left'>");
							else
								print("<font class='Texte3'><b>$cnt fiche$s en attente de d�cision (tri par $ordre_tri) : </b></font><br><br>
											<table width='100%' border='0' cellspacing='0' cellpadding='4' align='left'>");

							foreach($candidats_array as $key_candidat => $candidat)
							{
								if($candidat["fiche_manuelle"])
									$td_manuelle="<td class='$fond1' align='center' width='22'>
															<img src='$__ICON_DIR/$icone_manuelle1' alt='Fiche manuelle' desc='Fiche cr��e manuellement' border='0'>
														</td>\n";
								else
									$td_manuelle="<td class='$fond1'></td>\n";

								$link_class=isset($candidat["vu"]) && $candidat["vu"] ? "lien_vu_12" : "$lien1";

								print("<tr>
											$td_manuelle
											<td class='$fond1'>
												<a href='edit_candidature.php?cid=$candidat[id]' class='$link_class'><b>$candidat[nom] $candidat[prenom]</b></a>
												$candidat[diplome]
											</td>
											<td class='$fond1'>
												<a href='edit_candidature.php?cid=$candidat[id]' class='$link_class'>$nom_annee $nom_spec $tab_finalite[$finalite]</a>
											</td>
										</tr>\n");

								switch_vals($fond1, $fond2);
								switch_vals($lien1, $lien2);
								switch_vals($texte1, $texte2);
								switch_vals($icone_manuelle1, $icone_manuelle2);
							}

						}

						print("</table>\n");
					}
					else
					{
						if($_SESSION["mode"]==$__MODE_PREC)
							print("<font class='Texte3'><b>Aucune pr�candidature partiellement trait�e.</b></font><br>");
						else
							print("<font class='Texte3'><b>Aucune d�cision de Commission partiellement rendue.</b></font><br>");
					}
				}
			}

			db_close($dbr);
		?>
		</td>
	</tr>
	</table>

	<?php
		// Tri par moyenne : il faut s�lectionner une formation
		if(isset($_SESSION["tri"]) && $_SESSION["tri"]==3 && isset($filtre) && $filtre=="")
			message("Le tri par moyenne n'est possible que si <b>un filtre sur une formation</b> a �t� s�lectionn�", $__ERREUR);
	?>
</div>
<?php
	pied_de_page();
?>
<br>

</body>
</html>

