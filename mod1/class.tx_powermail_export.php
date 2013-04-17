<?php
require_once('../lib/class.tx_powermail_functions_div.php'); // include div functions

class tx_powermail_export {

	var $extKey = 'powermail'; // Extension key
	var $dateformat = 'Y-m-d'; // timeformat for displaying date
	var $timeformat = 'H:i:s'; // timeformat for displaying date
	var $seperator = ';'; // separator for csv
	var $csvfilename = 'powermail_export.csv'; // filename of exported CSV file
	var $zip = 1; // activate CSV file compressing to .gz
	var $rowconfig = array('number'=>'#', 'date'=>'Date', 'time'=>'Time', 'uid'=>'all', 'sender'=>'Sender email', 'senderIP'=>'Sender IP address', 'recipient'=>'Recipient email', 'subject_r'=>'Email subject', 'formid'=>'Page ID', 'UserAgent'=>'UserAgent', 'Referer'=>'Referer', 'SP_TZ'=>'Sender location'); // set order for export

	// Function Main
	function main($export,$pid = 0,$LANG = '') {
		// config
		$this->pid = $pid; // Page ID
		$this->startdate = $_GET['startdate']; // startdate from GET var
		$this->enddate = $_GET['enddate']; // enddate from GET var
		$this->LANG = $LANG; // make $LANG global
		$content = ''; $i = 0; // init 
		$this->tsconfig = t3lib_BEfunc::getModTSconfig($this->pid,'tx_powermail_mod1'); // Get tsconfig from current page
		(!empty($this->tsconfig['properties']['config.']['export.']['dateformat']) ? $this->dateformat = $this->tsconfig['properties']['config.']['export.']['dateformat'] : ''); // set dateformat
		(!empty($this->tsconfig['properties']['config.']['export.']['timeformat']) ? $this->timeformat = $this->tsconfig['properties']['config.']['export.']['timeformat'] : ''); // set timeformat
		($this->tsconfig['properties']['config.']['export.']['useTitle'] == 0 && isset($this->tsconfig['properties']['config.']['export.']['useTitle']) ? $this->useTitle = $this->tsconfig['properties']['config.']['useTitle'] : $this->useTitle = 1); // titles should be set
		(count($this->tsconfig['properties']['export.']) > 0 ? $this->rowconfig = $this->tsconfig['properties']['export.'] : ''); // overwrite rowconfig if set

		// DB query
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'*',
			'tx_powermail_mails',
			$where_clause = 'pid = '.$this->pid.' AND hidden = 0 AND deleted = 0 AND crdate > '.strtotime($this->startdate).' AND crdate < '.strtotime($this->enddate),
			$groupBy = '',
			$orderBy = 'crdate DESC',
			$limit = ''
		);
		if ($res) { // If on current page is a result
			if($export == 'xls' || $export == 'table') {
				$table = '<table>'; // Init table
				$table .= $this->setTitle($export,$row); // Title
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // one loop for every db entry
					if($row['piVars']) {
						$values = t3lib_div::xml2array($row['piVars'],'pivars'); // xml2array
						$i++; // increase counter
						$table .= '<tr>';
						foreach ($this->rowconfig as $key => $value) { // every row from config
							if ($key == 'number') $table .= '<td>'.$i.'.</td>'; // if current row is number
							elseif ($key == 'date') $table .= '<td>'.date($this->dateformat, $row['crdate']).'</td>'; // if current row is date
							elseif ($key == 'time') $table .= '<td>'.date($this->timeformat, $row['crdate']).'</td>'; // if current row is time
							elseif ($key == 'uid') { // if current row should show all dynamic values (piVars)
								if(isset($values) && is_array($values)) {
									foreach ($values as $key => $value) { // one loop for every piVar
										if(!is_array($value)) $table .= '<td>'.$value.'</td>';
									}
								}
							}
							elseif (intval(str_replace('uid','',$key)) > 0) { // dynamic value like uid45
								if (!empty($values[$key])) { // if is set
									$table .= '<td>'.$values[$key].'</td>'; // fill cell with content
								} else {
									$table .= '<td></td>'; // empty cell
								}
							}
							else $table .= '<td>'.$row[$key].'</td>';
						}
						$table .= '</tr>';
					}
				}
				$table .= '</table>';
			} elseif ($export == 'csv') {
				//$table .= 'sep=,'."\n"; // write first line
				$table .= $this->setTitle($export,$row); // Title
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) { // one loop for every db entry
					if($row['piVars']) {
						$i++; // increase counter
						$values = t3lib_div::xml2array($row['piVars'],'pivars'); // xml2array
						foreach ($this->rowconfig as $key => $value) { // every row from config
							if ($key == 'number') $table .= '"'.$i.'."'.$this->seperator; // if current row is number
							elseif ($key == 'date') $table .= '"'.date($this->dateformat, $row['crdate']).'"'.$this->seperator; // if current row is date
							elseif ($key == 'time') $table .= '"'.date($this->timeformat, $row['crdate']).'"'.$this->seperator; // if current row is time
							elseif ($key == 'uid') { // if current row should show all dynamic values (piVars)
								if(isset($values) && is_array($values)) {
									foreach ($values as $key => $value) { // one loop for every piVar
										if(!is_array($value)) $table .= '"'.str_replace('"',"'",str_replace(array("\n\r","\r\n","\n","\r"),'',$value)).'"'.$this->seperator;
									}
								}
							}
							elseif (intval(str_replace('uid','',$key)) > 0) { // dynamic value like uid45
								if (!empty($values[$key])) { // if is set
									$table .= '"'.$values[$key].'"'.$this->seperator; // fill cell with content
								} else {
									$table .= '" "'.$this->seperator; // empty cell
								}
							}
							else $table .= '"'.$row[$key].'"'.$this->seperator;
						}
						$table = substr($table,0,-1); // delete last ,
						$table .= "\n"; // new line
					}
				}
			}
		}
		
		// What to show
		if($export == 'xls') {
			$content .= header("Content-type: application/vnd-ms-excel");
			$content .= header("Content-Disposition: attachment; filename=export.xls");
			$content .= $table; // add table to content
		
		} elseif($export == 'csv') {
		
			if(!t3lib_div::writeFileToTypo3tempDir(PATH_site.'typo3temp/'.$this->csvfilename,$table)) { // write to typo3temp and if success returns FALSE
				$content .= '<strong>'.$this->LANG->getLL('export_download_success').'</strong><br />';
				$this->gzcompressfile(PATH_site.'typo3temp/'.$this->csvfilename); // compress file
				$content .= '<a href="http://'.$_SERVER['HTTP_HOST'].'/typo3temp/'.$this->csvfilename.'" target="_blank"><u>'.$this->LANG->getLL('export_download_download').'</u></a><br />'; // link to xx.csv.gz
				$content .= '<a href="http://'.$_SERVER['HTTP_HOST'].'/typo3temp/'.$this->csvfilename.'.gz" target="_blank"><u>'.$this->LANG->getLL('export_download_downloadZIP').'</u></a><br />'; // link to xx.csv
			} else {
				$content .= t3lib_div::writeFileToTypo3tempDir(PATH_site.'typo3temp/'.$this->csvfilename,$table);
			}
		
		} elseif($export == 'table') {
		
			$content .= $table; // add table to content
		
		} else { // not supported method
			$content = 'Wrong export method chosen!';
		}
		
		return $content;
	}

	// Compress a file
	function gzcompressfile($source,$level=false){ 
		$dest = $source.'.gz';
		$mode = 'wb'.$level;
		$error = false;
		if($fp_out=gzopen($dest,$mode)){
			if($fp_in=fopen($source,'rb')){
				while(!feof($fp_in))
				gzwrite($fp_out,fread($fp_in,1024*512));
				fclose($fp_in);
			}
			else $error=true;
			gzclose($fp_out);
		}
		else $error=true;
		
		if($error) return false;
		else return $dest;
	}
	
	
	// Set title
	function setTitle($export, $row) {
		if ($this->useTitle == 1 && isset($this->rowconfig)) {	// if title should be used
			$values = t3lib_div::xml2array($row['piVars'],'pivars'); // xml2array
			($export == 'csv' ? $table = '' : $table = '<tr>'); // init
			foreach ($this->rowconfig as $key => $value) { // one loop for every row
				if ($key != 'uid') { // static values
					if ($export == 'csv') { // CSV only
						$table .= '"'.$value.'"'.$this->seperator;
					} else { // HTML and EXCEL only
						$table .= '<td><b>'.$value.'</b></td>';
					}
				} else {
					if(isset($values) && is_array($values)) {
						foreach ($values as $key => $value) { // one loop for every piVar
							echo $key.': '.$value;
							if (!is_array($value) && $export == 'csv') $table .= '"'.str_replace('"',"'",str_replace(array("\n\r","\r\n","\n","\r"),'', $this->GetLabelfromBackend($key, $value))).'"'.$this->seperator;
							elseif (!is_array($value)) $table .= '<td>'.$this->GetLabelfromBackend($key, $value).'</td>';
						}
					}
				}
			}
			($export == 'csv' ? $table = substr($table,0,-1)."\n" : $table .= '</tr>'); // init
			if (!empty($table)) return $table;
		}
	}
    
	
    // Function GetLabelfromBackend() to get label to current field for emails and thx message
    function GetLabelfromBackend($name,$value) {
		if(strpos($name,'uid') !== FALSE) { // $name like uid55
			$uid = str_replace('uid','',$name);

			$where_clause = 'c.deleted=0 AND c.hidden=0 AND (c.starttime<='.time().') AND (c.endtime=0 OR c.endtime>'.time().') AND (c.fe_group="" OR c.fe_group IS NULL OR c.fe_group="0" OR (c.fe_group LIKE "%,0,%" OR c.fe_group LIKE "0,%" OR c.fe_group LIKE "%,0" OR c.fe_group="0") OR (c.fe_group LIKE "%,-1,%" OR c.fe_group LIKE "-1,%" OR c.fe_group LIKE "%,-1" OR c.fe_group="-1"))'; // enable fields for tt_content
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ( // GET title where fields.flexform LIKE <value index="vDEF">vorname</value>
				'f.title',
				'tx_powermail_fields f LEFT JOIN tx_powermail_fieldsets fs ON (f.fieldset = fs.uid) LEFT JOIN tt_content c ON (c.uid = fs.tt_content)',
				$where_clause .= ' AND f.uid = '.$uid.' AND f.hidden = 0 AND f.deleted = 0',
				$groupBy = '',
				$orderBy = '',
				$limit = ''
			);
			if ($res) $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

			if(isset($row['title'])) return $row['title']; // if title was found return ist
			else return 'POWERMAIL ERROR: No title to current field found in DB'; // if no title was found return 
		} else { // no uid55 so return $name
			return $name;
		}
    }

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail/mod1/class.tx_powermail_export.php']) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/powermail/mod1/class.tx_powermail_export.php']);
}
?>