<?php // -*- tab-width: 3; indent-tabs-mode: 1; -*- 

/*  
 * $Id: showContactProgs.php,v 1.3 2003/03/05 09:11:40 andras Exp $
 * Created for the StreamOnTheFly project (IST-2001-32226)
 * Authors: Andr�s Micsik, M�t� Pataki, Tam�s D�ri 
 *          at MTA SZTAKI DSD, http://dsd.sztaki.hu
 */


require("init.inc.php");

$contactId = sotf_Utils::getParameter('id');

$contact = & new sotf_Contact($contactId);
$smarty->assign('PAGETITLE', $page->getlocalizedWithParams('programs_by', $contact->get('name')));
$smarty->assign('CONTACT_ID',$contactId);
$smarty->assign('CONTACT_NAME',$contact->get('name'));
$smarty->assign('CONTACT_DATA',$contact->getAllWithIcon());

$limit = $page->splitList($contact->countProgrammes(), myGetenv('REQUEST_URI'));
$progs = $contact->listProgrammes($limit["from"] , $limit["maxresults"]);

for($i=0; $i<count($progs); $i++) {
  $progs[$i]['icon'] = sotf_Blob::cacheIcon($progs[$i]['id']);
}

$smarty->assign('PROGS',$progs);

$page->send();

?>
