<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "powermail".
 *
 * Auto generated 17-04-2013 13:41
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'powermail',
	'description' => 'Powerful and easy mailform extension with many features like IRRE use, database storing (Excel and CSV export), different HTML templates, javascript validation, morestep forms, works with date2cal and static_info_tables and many more...',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '1.5.2',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => 'uploads/tx_powermail/files',
	'modify_tables' => 'tt_content',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Alex Kellner, Mischa Heissmann',
	'author_email' => 'alexander.kellner@einpraegsam.net, typo3.10@heissmann.org',
	'author_company' => '',
	'CGLcompliance' => NULL,
	'CGLcompliance_note' => NULL,
	'constraints' => 
	array (
		'depends' => 
		array (
			'php' => '5.0.0-0.0.0',
			'typo3' => '3.8.0-0.0.0',
		),
		'conflicts' => 
		array (
			'dbal' => '0.0.0-0.0.0',
		),
		'suggests' => 
		array (
		),
	),
);

?>