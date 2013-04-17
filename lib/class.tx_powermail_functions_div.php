<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Mischa Hei�mann, Alexander Kellner <typo3.2008@heissmann.org, alexander.kellner@wunschtacho.de>
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
 * Class with collection of different functions (like string and array functions)
 *
 * @author	Mischa Hei�mann, Alexander Kellner <typo3.2008@heissmann.org, alexander.kellner@einpraegsam.net>
 * @package	TYPO3
 * @subpackage	tx_powermail
 */
class tx_powermail_functions_div {

	var $extKey = 'powermail';
	
	// Function clearName() to disable not allowed letters (only A-Z and 0-9 allowed) (e.g. Perfect Extension -> perfectextension)
	function clearName($string,$strtolower = 0,$cut = 0) {
		$string = preg_replace("/[^a-zA-Z0-9]/","",$string); // replace not allowed letters with nothing
		if($strtolower) $string = strtolower($string); // string to lower if active
		if($cut) $string = substr($string,0,$cut); // cut after X signs if active
		
		if(isset($string)) return $string;
	}
	
	// Function clearValue() to remove all " or ' from code
	function clearValue($string,$htmlentities = 1,$strip_tags = 0) {
		$notallowed = array('"',"'");
		$string = str_replace($notallowed,"",$string); // replace not allowed letters with nothing
		if($htmlentities) $string = htmlentities($string); // change code to ascii code
		if($strip_tags) $string = strip_tags($string); // disable html/php code
		
		if(isset($string)) return $string;
	}
	
	// Function validateValue() removes all vorbidden signs in piVars
	function validateValue($string) {
		//echo $this->conf['allow.']['signs'];
		//$string = htmlentities($string);
		//$string = preg_replace('/[^'.$this->conf['allow.']['signs'].']/','',$string); // replace not allowed letters with nothing
		echo $string;
		$string = preg_replace('/[^a-zA-Z0-9_ -,.;@!?=()�$%:+*�������]/','',$string); // replace not allowed letters with nothing
		echo $string;
		if(isset($string)) return $string;
	}
	
	// Function linker() generates link from pure email or url string
    function linker($link,$additinalParams = '') {
        $link = str_replace("http://www.","www.",$link);
        $link = str_replace("www.","http://www.",$link);
        $link = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a href=\"$1\"$additinalParams>$1</a>", $link);
        $link = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i","<a href=\"mailto:$1\"$additinalParams>$1</a>",$link);
    
        return $link;
    }
	
	// Function sec() is a security function against all bad guys :) 
	function sec($array) {
		if(isset($array) && is_array($array)) { // if array
			
			foreach ($array as $key => $value) { // one loop for every key in first level
				
				if(!is_numeric(str_replace('UID','',$key)) && !is_array($value)) { // all others piVars than UID34
					$array[$key] = intval($value); // the value should be integer
				}
					
				if(!is_array($value)) {	// if value is not an array
				
					$array[$key] = strip_tags($value); // strip_tags removes html and php code
					if(function_exists('mysql_real_escape_string')) $array[$key] = mysql_real_escape_string($value); // check against sql injection
					
				} else { // value is still an array (second level)
				
					foreach ($value as $key2 => $value2) { // one loop for every key in second level
					
						$array[$key][$key2] = strip_tags($value2); // strip_tags removes html and php code
						if(function_exists('mysql_real_escape_string')) $array[$key][$key2] = mysql_real_escape_string($value2); // check against sql injection
						
					}
					
				}
			}
			t3lib_div::addSlashesOnArray($array); // addslashes for every piVar (He'l"lo => He\'l\"lo)
			
			return $array;
			
		}
	}


	//function for initialisation.
	// to call cObj, make $this->pibase->pibase->cObj->function()
	function init(&$conf,&$pibase) {
		$this->conf = $conf;
		$this->pibase = $pibase;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail/lib/class.tx_powermail_functions_div.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail/lib/class.tx_powermail_functions_div.php']);
}

?>
