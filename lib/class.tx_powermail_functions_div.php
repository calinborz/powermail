<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Alexander Kellner, Mischa Hei�mann <alexander.kellner@einpraegsam.net, typo3.2008@heissmann.org>
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
 
//require_once(t3lib_extMgm::extPath('powermail').'lib/class.tx_powermail_removexss.php'); // file for removexss function class


class tx_powermail_functions_div {

	var $extKey = 'powermail';
	
	// Function sec() is a security function against all bad guys :) 
	function sec($array) {
		if(isset($array) && is_array($array)) { // if array
			//$this->removeXSS = t3lib_div::makeInstance('tx_powermail_removexss'); // New object: removeXSS function
			//t3lib_div::addSlashesOnArray($array); // addslashes for every piVar (He'l"lo => He\'l\"lo)
			
			foreach ($array as $key => $value) { // one loop for every key in first level
				
				if (!is_numeric(str_replace('UID', '', $key)) && !is_array($value)) { // all others piVars than UID34
					$array[$key] = intval(trim($value)); // the value should be integer
				}
					
				if (!is_array($value)) {	// if value is not an array
				
					$array[$key] = strip_tags(trim($value)); // strip_tags removes html and php code
					$array[$key] = addslashes($array[$key]); // use addslashes
					//$array[$key] = $this->removeXSS->RemoveXSS($array[$key]); // use remove XSS for piVars
					
				} else { // value is still an array (second level)
					
					if (!is_array($key2)) {	// if value is not an array
						foreach ($value as $key2 => $value2) { // one loop for every key in second level
						
							$array[$key][$key2] = strip_tags(trim($value2)); // strip_tags removes html and php code
							$array[$key][$key2] = addslashes($array[$key][$key2]); // use addslashes
							//$array[$key][$key2] = $this->removeXSS->RemoveXSS($array[$key][$key2]); // use remove XSS for piVars
							
						}
					} else unset($array[$key][$key2]); // if array with 3 or more dimensions - delete this value
					
				}
			}
			
			return $array;
			
		}
	}
	
	
	// Add debug view for any array
	function debug($array, $msg = 'Debug output') {
		#echo '<b>'.$msg.':</b>'; // title output
		#t3lib_div::print_array($array); // debug output of sessiondata
		#echo '<hr /><br />'; // separator after debug output
		t3lib_div::debug($array, $this->extKey.': '.$msg); // debug output
	}
	
	
	/**
	 * Function clearName() to disable not allowed letters (only A-Z and 0-9 allowed) (e.g. Perfect Extension -> perfectextension)
	 *
	 * @param	string		$string: String to change
	 * @param	boolean		$strtolower: Should the string be changed to lower characters?
	 * @param	int			$cut: Should the string cutted after X signs?
	 * @return	string		$string: Manipulated string
	 */
	function clearName($string, $strtolower = 0, $cut = 0) {
		$string = preg_replace('/[^a-zA-Z0-9]/' ,'', $string); // replace not allowed letters with nothing
		if ($strtolower) $string = strtolower($string); // string to lower if active
		if ($cut) $string = substr($string, 0, $cut); // cut after X signs if active
		
		if (!empty($string)) return $string;
	}
	
	
	// Function clearValue() to remove all " or ' from code
	function clearValue($string, $htmlentities = 1, $strip_tags = 0) {
		$notallowed = array('"',"'");
		$string = str_replace($notallowed,"",$string); // replace not allowed letters with nothing
		if($htmlentities) $string = htmlentities($string); // change code to ascii code
		if($strip_tags) $string = strip_tags($string); // disable html/php code
		
		if(isset($string)) return $string;
	}
	
	
	// Function linker() generates link (email and url) from pure text string within an email or url ('test www.test.de test' => 'test <a href="http://www.test.de">www.test.de</a> test')
    function linker($link, $additinalParams = '') {
        $link = str_replace("http://www.","www.",$link);
        $link = str_replace("www.","http://www.",$link);
        $link = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<a href=\"$1\"$additinalParams>$1</a>", $link);
        $link = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i","<a href=\"mailto:$1\"$additinalParams>$1</a>",$link);
    
        return $link;
    }
	
	
	// Function nl2br2() changes breakes to html breakes
	function nl2br2($string) {
		return str_replace(array('\r\n','\n','\r','\n\r',"\r\n","\n","\r","\n\r"),"<br />",$string);
	}
	
	
	// Function nl2br2() changes breakes to real breakes
	function nl2nl2($string) {
		return str_replace('\r\n',"\r\n",$string);
	}
	
	
	// Function br2nl is the opposite of nl2br
	function br2nl($content) {
		$content = str_replace(array("<br >","<br>","<br/>","<br />"), "\n", $content);
		
		if (!empty($content)) return $content;
	}
	
	
	// Function correctPath() checks if the link is like "fileadmin/test/ and not "/fileadmin/test"
	function correctPath($value) {
		// If there is no Slash at the end of the picture folder, add a slash and if there is a slash at the beginning, remove this slash
		if (substr($value, -1, 1) != '/') $value .= '/'; // add a slash at the end if there is no slash
		if (substr($value, 0, 1) == '/') $value = substr($value, 1); // remove slash from the front
		
		if ($value) return $value;
	}
	
	
	/**
	 * Function marker2value() replaces ###UID3### with its value from session
	 *
	 * @param	string		$string: Any string
	 * @param	array		$sessiondata: Values from session an an array
	 * @param	boolean		$int: Should the string transformed to an integer (or an integer list)
	 * @return	string		replaced string
	 */
	function marker2value($string, $sessiondata, $int = 0) {
		$this->sessiondata = $sessiondata; // make session array available in other functions
		$this->activateIntList = $int; // activate intList
		
		$string = preg_replace_callback ( // Automaticly replace ###UID55### with value from session to use markers in query strings
			'#\#\#\#UID(.*)\#\#\##Uis', // regulare expression
			array($this, 'uidReplaceIt'), // open function
			$string // current string
		);
	
		return $string;
	}
	
	
	/**
	 * Function uidReplaceIt is used for the callback function to replace ###UID55## with it's value
	 *
	 * @param	string		$uid: field uid
	 * @return	string		it's value from the session
	 */
	function uidReplaceIt($uid) {
		if (strpos($uid[1], '_')  === false) { // if this is a field like ###UID55### and not like ###UID55_1###
			if (isset($this->sessiondata['uid' . $uid[1]])) { // if there is a value in the session like uid32 = bla
				if (!is_array($this->sessiondata['uid' . $uid[1]])) { // value is not an array
					
					if (!$this->activateIntList) { // return without int transformation
						return $this->sessiondata['uid' . $uid[1]]; // return bla (e.g.)
					} else { // transform to intList
						return $this->makeIntList($this->sessiondata['uid' . $uid[1]]); // return bla (e.g.)
					}
					
				} else { // value is an array
				
					$return = ''; $i=0; // init counter
					foreach ($this->sessiondata['uid' . $uid[1]] as $key => $value) { // one loop for every value
						if ($value != '') {
							$return .= ($return != '' ? ',' : '') . (!$this->activateIntList ? $value : $this->makeIntList($value)); // add a value (commaseparated)
							$i++; // increase counter
						}
					}
					return $return; // return 44,45,46 (e.g.)
					
				}
			}
		} else { // if this is a field like ###UID55_1###
			$tmp_uid = t3lib_div::trimExplode('_', $uid[1], 1); // spilt at _ to get 55 and 1
			if ($this->sessiondata['uid' . $tmp_uid[0]][$tmp_uid[1]]) { // value is not an array
				if (!$this->activateIntList) { // return without transformation
					return $this->sessiondata['uid' . $tmp_uid[0]][$tmp_uid[1]]; // return bla (e.g.)
				} else { // transform to intList
					return $this->makeIntList($this->sessiondata['uid' . $tmp_uid[0]][$tmp_uid[1]]); // return bla (e.g.)
				}
			}
		}
	}
	
	
	/**
	 * Function makeIntList() to transfor string list to an integer list
	 *
	 * @param	string		$string: string like "1,2,3,4"
	 * @return	string		it's integer values
	 */
	function makeIntList($string) {
		$str_arr = explode(',', $string); // split on ,
		for ($i=0; $i<count($str_arr); $i++) { // one loop for every part of the list
			$str_arr[$i] = intval($str_arr[$i]); // transform to integer
		}
		$list = implode(',', $str_arr); // merge with , as glue
		return $list;
	}
	
	
	// Function checkMX() checks if a domain exists
	function checkMX($email, $record = 'MX') {
		if (function_exists('checkdnsrr')) { // if function checkdnsrr() exist (not available on windows systems)
			list($user, $domain) = split('@', $email); // split email in user and domain
			
			if (checkdnsrr($domain, $record) == 1) return TRUE; // return true if mx record exist
			else return FALSE; // return false if not
		
		} else {
			return TRUE; // function checkdnsrr() don't exist, so always return TRUE
		}
	}
	
	
	// Function charset() changes content with utf8_decode or utf8_encode or nothing
	function charset($content, $function = '') {
		if ($function == 'utf8_encode') $content = utf8_encode($content);
		elseif ($function == 'utf8_decode') $content = utf8_decode($content);
		
		if (!empty($content)) return $content;
	}
	
	
	// Function makePlain() removes html tags and add linebreaks
	function makePlain($content) {
		
		// config
		$htmltagarray = array ( // This tags will be added with linebreaks
			'</p>',
			'</tr>',
			'</li>',
			'</h1>',
			'</h2>',
			'</h3>',
			'</h4>',
			'</h5>',
			'</h6>',
			'</div>',
			'</legend>',
			'</fieldset>', 
			'</dd>',
			'</dt>'
		);
		$notallowed = array ( // This array contains not allowed signs which will be removed
			'&nbsp;',
			'&szlig;',
			'&Uuml;',
			'&uuml;',
			'&Ouml;',
			'&ouml;',
			'&Auml;',
			'&auml;',
		);
		
		// let's go
		$content = str_replace($htmltagarray, $htmltagarray[0].'<br />', $content); // 1. add linebreaks on some parts (</p> => </p><br />)
		$content = strip_tags($content, '<br>'); // 2. remove all tags but not linebreak (<b>bla</b><br /> => bla<br />)
		$content = preg_replace('/\s+/', ' ', $content); // 3. removes tabs and whitespaces
		$content = $this->br2nl($content); // 4. <br /> to \n
		$content = implode("\n", t3lib_div::trimExplode("\n", $content)); // 5. explode and trim each line and implode again (" bla \n blabla " => "bla\nbla")
		$content = str_replace($notallowed, '', $content); // 6. remove not allowed signs
		
		if (!empty($content)) return $content;
	}
	
	
	// Function subpartsExists() checks if every part of the array contains min one sign
	function subpartsExists($array) {
		if (count($array) > 0) { // if there are values
			foreach ($array as $key => $value) { // one loop for every array part
				if (!is_array($value)) { // first level
					if (strlen($value) == 0) return false; // error
				} else { // second level
					foreach ($value as $key2 => $value2) { // one loop for every array part in second level
						if (strlen($value2) == 0) return false; // error
					}
				}
			}
		}
		return true; // ok
	}
	
	
	// Function TSmanipulation() manipulates session values before output with typoscript
	function TSmanipulation($array, $mode, $conf, $cObj) {
		// config
		$this->conf = $conf;
		$this->cObj = $cObj;
		
		// let's go
		if (is_array($this->conf['mode.'][$mode.'.']) && count($this->conf['mode.'][$mode.'.']) > 0) { // if there are configurations in typoscript
			foreach ($this->conf['mode.'][$mode.'.'] as $key => $value) { // one loop for every ts configuration
				if (strpos($key, '.') === false) { // if no point in the key
					if (strpos($key, '_') === false) { // like uid43
						$this->cObj->start(array_merge((array) $this->arraytwo2arrayone($array), (array) $this->cObj->data), 'tx_powermail_fields'); // enable .field in typoscript
						$array[$key] = $this->cObj->cObjGetSingle($this->conf['mode.'][$mode.'.'][$key], $this->conf['mode.'][$mode.'.'][$key.'.']); // overwrite value with ts manipulation
					} else { // like uid43_0
						$tmpkey = t3lib_div::trimExplode('_', $key); // split key at underscore
						$array[$tmpkey[0]][$tmpkey[1]] = $this->cObj->cObjGetSingle($this->conf['mode.'][$mode.'.'][$key], $this->conf['mode.'][$mode.'.'][$key.'.']); // overwrite value with ts manipulation
					}
				}
			}
		}
		
		return $array; // return array
	}
	
	
	// Function arraytwo2arrayone() changes: array('v1', array('v2')) to array('v1', 'v1_v2)
	function arraytwo2arrayone($array) {
		$newarray = array();
		
		if (count($array) > 0 && is_array($array)) {
			foreach ($array as $k => $v) {
				if (!is_array($v)) { // first level
					
					$newarray[$k] = $v; // no change
				
				} else { // second level
					if (count($v) > 0) {
						
						foreach ($v as $k2 => $v2) {
							if (!is_array($v2)) $newarray[$k.'_'.$k2] = $v2; // change to first level
						}
					
					}
				}
			}
		}
		
		return $newarray;
	}
	
	
	// Function parseFunc() parses a string to support LINK syntax
	function parseFunc($str, $cObj, $act = 1) {
		$this->cObj = $cObj;
		$parseFunc = $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.']; // Get parseFunc array from Globals
		$parseFunc['nonTypoTagStdWrap.']['encapsLines.']['removeWrapping'] = 1; // add removeWrapping to this array (we don't want the p-tags)

		if ($act == 1) return $this->cObj->parseFunc($str, $parseFunc); // return string
		else return $str; // return string
	}
	
	
	// Function alternate() checks if a number is odd or not
	function alternate($int = 0) {
		if ($int % 2 != 0) { // odd or even
			return false; // return false
		} else { 
			return true; // return true
		}
	}
	
	
	// Function mimecheck() returns true or false if file fits mime check
	function mimecheck($filename, $origfilename) {
		$ok = 0; // disallow on begin
		$ext = strtolower(array_pop(explode('.', $origfilename))); // get the extension of the upload

		$mime_types = array( // mime-type definition of files
		
			// basic mime-types
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => array (
				'application/zip',
				'application/x-compressed',
				'application/x-zip-compressed',
				'application/x-zip',
				'multipart/x-zip'
			),
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'docx' => 'application/x-zip',
			'xlsx' => 'application/x-zip',
			'pptx' => 'application/x-zip',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
		);
		
		if (array_key_exists($ext, $mime_types)) { // if there is a mimetype definition of current uploaded file
			
			// 1. get mimetype
			if (function_exists('finfo_open')) { // Get mimetype via finfo (PECL-Extension needed)
				$finfo = finfo_open(FILEINFO_MIME);
				$mimetype = finfo_file($finfo, $filename);
				finfo_close($finfo);
			} elseif (function_exists('mime_content_type')) { // Get mimetype via mime_content_type (Deprecated function, but sometimes still active)
				$mimetype = mime_content_type($filename);
			} elseif (file_exists('/usr/bin/file')) { // Use file-command with unix to determine
				$mimetype = exec('/usr/bin/file -bi '. $filename);
			} else { // If no method above applies, shrug with your shoulders and make the result true
				$ok = 1; // allow upload
			}
			
			// 2. set variable $ok if ok
			if (!$ok) { // if it's not yet ok to upload files
				if (!is_array($mime_types[$ext])) { // if definition is not an array
					if ($mimetype == $mime_types[$ext]) { // if mimetype is correct
						$ok = 1; // allow upload
					}
				} else { // defintion is array
					if (in_array($mimetype, $mime_types[$ext])) { // if mimetype is in array
						$ok = 1; // allow upload
					}
				}
			}
			
			// 3. return 0/1
			if ($ok) { // if upload allowed
				return true; // upload allowed, stop further process
			} else { // if upload not allowed
				return false; // upload disallowed
			}
			
		} else { // no mime type definition in array above
			return true; // upload allowed
		}
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail/lib/class.tx_powermail_functions_div.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail/lib/class.tx_powermail_functions_div.php']);
}

?>