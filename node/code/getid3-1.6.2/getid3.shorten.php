<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                ///
//            or http://www.getid3.org                        ///
/////////////////////////////////////////////////////////////////
//                                                             //
// getid3.shorten.php - part of getID3()                       //
// See getid3.readme.txt for more details                      //
//                                                             //
/////////////////////////////////////////////////////////////////

function getShortenHeaderFilepointer(&$fd, &$ThisFileInfo) {

	$ThisFileInfo['fileformat'] = 'shn';

	$ThisFileInfo['error'] .= "\n".'Shorten parsing not enabled in this version of getID3()';
	return false;

}

?>