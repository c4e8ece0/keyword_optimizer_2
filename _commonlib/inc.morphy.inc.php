<?php

require_once(dirname(__FILE__) . '/phpmorphy-0.2.3.1/src/common.php');
$opts = array('storage' => PHPMORPHY_STORAGE_FILE, 'with_gramtab' => false, 'predict_by_suffix' => true, 'predict_by_db' => true);
$dir = dirname(__FILE__) . '/phpmorphy-0.2.3.1/dicts';
$dict_bundle = new phpMorphy_FilesBundle($dir, 'rus');
$MORPHY = new phpMorphy($dict_bundle, $opts);

?>