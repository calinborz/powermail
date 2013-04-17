<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Mischa Hei√ümann, Alexander Kellner <typo3.2008@heissmann.org, alexander.kellner@wunschtacho.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/




/**
 * Class/Function which manipulates the item-array for table/field tx_powermail_forms_recip_table.
 *
 * @author	Mischa Hei√ümann, Alexander Kellner <typo3.2008@heissmann.org, alexander.kellner@einpraegsam.net>
 * @package	TYPO3
 * @subpackage	tx_powermail
 */
class user_powermail_tx_powermail_forms_recip_table {
							function main(&$params,&$pObj)	{
/*								
								debug('Hello World!',1);
								debug('$params:',1);
								debug($params);
								debug('$pObj:',1);
								debug($pObj);
*/						//		print_r($params);
								$tables = $GLOBALS['TYPO3_DB']->admin_get_tables();

								foreach($tables as $v) {
									$params['items'][] = array($pObj->sL($v),$v);
								}
									// Adding an item!
//								$params['items'][] = array($pObj->sL("Added label by PHP function|Tilf¯jet Dansk tekst med PHP funktion"), 999);

								// No return - the $params and $pObj variables are passed by reference, so just change content in then and it is passed back automatically...
							}
						}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail/lib/class.user_powermail_tx_powermail_forms_recip_table.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail/lib/class.user_powermail_tx_powermail_forms_recip_table.php']);
}

?>
