<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Alexander Kellner, Mischa Heißmann <alexander.kellner@einpraegsam.net, typo3.2008@heissmann.org>
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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('powermail').'lib/class.tx_powermail_dynamicmarkers.php'); // file for dynamicmarker functions
if(t3lib_extMgm::isLoaded('xajax',0)) require (t3lib_extMgm::extPath('xajax') . 'class.tx_xajax.php'); // xajax

// This class saves powermail values in OTHER db tables if wanted (this class is not the main database class for storing)
class tx_powermail_countryzones extends tslib_pibase {

	var $extKey = 'powermail';
    var $scriptRelPath = 'pi1/class.tx_powermail_pi1.php'; // Path to pi1 to get locallang.xml from pi1 folder
	var $prefixId      = 'tx_powermail_pi1'; // Same as class name
	var $add = 100000; // workarround: add this number to countryselector uid for countryzone uid
	
	
	// Function preflight() prepares xajax use for countryzone selector
	function preflight($uid, $xml, &$markerArray, &$tmpl, $formtitle, $conf, $piVars, $cObj) {
		// config
		$this->tmpl = &$tmpl; // Template string
		$this->conf = $conf; // conf from ts
		$this->cObj = $cObj; // cObj
		$this->xml = $xml; // Flexform XML
		$this->piVars = &$piVars; // piVars
		$this->uid = $uid; // current field uid
		$this->formtitle = $formtitle; // form id
		$this->dynamicMarkers = t3lib_div::makeInstance('tx_powermail_dynamicmarkers'); // New object: TYPO3 marker function
		$this->markerArray = &$markerArray; // marker Array
		
		// let's go
		if (class_exists('tx_xajax') && $this->pi_getFFvalue(t3lib_div::xml2array($this->xml), 'countryzone')) { // make available if in backend selected and xajax is loaded
			# make XAJAX available
			$this->xajax = t3lib_div::makeInstance('tx_xajax');
			$this->xajax->decodeUTF8InputOn();
			$this->xajax->setCharEncoding('utf-8');
			$this->xajax->setWrapperPrefix($this->prefixId);
			$this->xajax->statusMessagesOn();
			$this->xajax->debugOff();
	
			// Register all my functions
			$this->xajax->registerFunction(array('addZoneSelector', &$this, 'addZoneSelector'));
	
			// Return to HTML header
			$this->xajax->processRequests();  
			$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = $this->xajax->getJavascript(t3lib_extMgm::siteRelPath('xajax'));

			if (!empty($this->piVars['uid' . ($this->uid + $this->add)])) { // if there is already a value in the session for countryzoneselect
				$this->tmpl['html_countryselect']['all'] = str_replace('<div id="powermail_countryzoneselect###POWERMAIL_FIELD_UID###" class="countryzone"></div>', '<div id="powermail_countryzoneselect###POWERMAIL_FIELD_UID###" class="countryzone">'.$this->codeForZoneSelector($this->piVars['uid' . $this->uid]).'</div>', $this->tmpl['html_countryselect']['all']); // add countryzoneselector code to html template
			}
			
			$this->markerArray['###JS###'] = 'onchange="tx_powermail_pi1addZoneSelector(this.value);"';
			
		}
		
	}
	

	// Function addZoneSelector() adds zone selectorbox to countryselectorbox via AJAX
	function addZoneSelector($value) {
		// config
		$objResponse = t3lib_div::makeInstance('tx_xajax_response');
		
		// return
		$objResponse->addClear('powermail_countryzoneselect'.$this->uid, "innerHTML"); // clear div container vor selector box
		if ($this->codeForZoneSelector($value)) $objResponse->addAppend('powermail_countryzoneselect'.$this->uid, "innerHTML", $this->codeForZoneSelector($value)); // add new content // if there are results 
		
		return $objResponse->getXML(); // return xml für javascript
	}
	
	
	// Function codeForZoneSelector() only generates code for zoneselctorbox
	function codeForZoneSelector($value) {
		// config
		$this->tmpl['html_countryzoneselect']['all'] = tslib_cObj::getSubpart($this->tmpl['all'],'###POWERMAIL_FIELDWRAP_HTML_COUNTRYZONESELECT###'); // work on subpart 1
		$this->tmpl['html_countryzoneselect']['item'] = tslib_cObj::getSubpart($this->tmpl['html_countryzoneselect']['all'],'###ITEM###'); // work on subpart 2
		$content_item = ''; $markerArray = array(); $outerMarkerArray = array(); $i=0;
			
		// change markers of countryzoneselect
		$outerMarkerArray['###NAME###'] = 'name="'.$this->prefixId.'[uid'.($this->add + $this->uid).']" '; // name in markerArray tx_powermail_pi1[55][1]
		$outerMarkerArray['###ID###'] = 'id="uid'.($this->add + $this->uid).'" '; // id in markerArray uid55_1
		$outerMarkerArray['###CLASS###'] = str_replace($this->uid, ($this->add + $this->uid), $this->markerArray['###CLASS###']); // change class="... uid55" -> class="... uid100055"
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'static_country_zones.zn_code, static_country_zones.zn_name_local',
			'static_countries LEFT JOIN static_country_zones ON (static_countries.cn_iso_2 = static_country_zones.zn_country_iso_2)',
			$where_clause = 'static_countries.cn_iso_2 = "'.$value.'" OR static_countries.cn_iso_3 = "'.$value.'" OR static_countries.cn_short_en = "'.$value.'"',
			$groupBy = '',
			$orderBy = 'static_country_zones.zn_code',
			$limit = ''
		);
		if ($res) { // If there is a result
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // One loop for every country_zone
				// markers
				$markerArray['###LONGVALUE###'] = $row['zn_name_local']; // Name of state
				$markerArray['###VALUE###'] = $row['zn_code']; // Shortage of state
				if ($this->piVars['uid' . ($this->uid + $this->add)] == $row['zn_code'] || $this->piVars['uid' . ($this->uid + $this->add)] == $row['zn_name_local']) $markerArray['###SELECTED###'] = ' selected="selected"'; // preselect one countryzone
				else $markerArray['###SELECTED###'] = ''; // clear selected marker
				
				// add code
				$content_item .= $this->cObj->substituteMarkerArrayCached($this->tmpl['html_countryzoneselect']['item'], $markerArray);
				$i++;
			}
		}
		$subpartArray['###CONTENT###'] = $content_item; // subpart 3
		
		$content = $this->cObj->substituteMarkerArrayCached($this->tmpl['html_countryzoneselect']['all'], $outerMarkerArray, $subpartArray); // substitute Marker in Template
		$content = $this->dynamicMarkers->main($this->conf, $this->cObj, $content); // Fill dynamic locallang or typoscript markers
		$content = preg_replace("|###.*?###|i", "", $content); // Finally clear not filled markers
	
		if ($i>1) return $content; // only if there are results
		else return false;
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail/lib/class.tx_powermail_countryzones.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail/lib/class.tx_powermail_countryzones.php']);
}

?>