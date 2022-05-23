<?php

$files = array('../01_header', '../02_footer');

foreach($files as $file) {
	$doneSomething = FALSE;
	if(@file_exists($file.'.html') && @file_exists($file.'_new.html')) {
		rename($file.'.html', $file.'_old_'.date("Y-m-d").'.html');
		echo 'Successfully moved old '.$file.' file to '.$file.'_old_'.date("Y-m-d").'.html.<p>';
		$doneSomething = TRUE;
	}
	if(@file_exists($file.'_new.html')) {
		rename($file.'_new.html', $file.'.html');
		echo 'Successfully put '.$file.' live.<p>';
		$doneSomething = TRUE;
	}

	if(!$doneSomething) {
		echo 'Nothing done for '.$file.'. ';
	}
}

