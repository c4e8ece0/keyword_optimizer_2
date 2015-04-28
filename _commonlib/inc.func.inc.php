<?php

$MORPHY_OBJECT = '';

function __get_morphy()
{
	global $MORPHY_OBJECT;
	static $flag=0;

	if(!$flag)
	{
		$opts = array('storage' => PHPMORPHY_STORAGE_FILE, 'with_gramtab' => false, 'predict_by_suffix' => true, 'predict_by_db' => true);
		$dir = dirname(__FILE__) . '/phpmorphy-0.2.3.1/dicts';
		$dict_bundle = new phpMorphy_FilesBundle($dir, 'rus');
		$MORPHY_OBJECT = new phpMorphy($dict_bundle, $opts);
		$flag = 1;
	}

	return $MORPHY_OBJECT;
	
}

?>