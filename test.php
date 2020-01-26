<?php
require_once 'includes/config.php';
//echo '<pre>'; var_dump(api::connect()); die;
foreach( api::connect() as $emp ){
	
	if( empty($emp->name) || empty($emp->title) || empty($emp->company) )
		continue;

	$avatar = '/assets/images/user.svg';
	if(!empty($emp->avatar))
		$avatar = $emp->avatar;
	
	$cv = htmlspecialchars(strip_tags(preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', "$1$3", $emp->bio)), ENT_QUOTES, 'UTF-8');
	
	echo '<p>Name: ' . htmlspecialchars($emp->name) . ' / ' . htmlspecialchars($emp->title, ENT_QUOTES, 'UTF-8') . '</p>';
	echo '<p>Company: ' . htmlspecialchars($emp->company, ENT_QUOTES, 'UTF-8') . '</p>';
	if(!empty($cv))
		echo '<p>CV: ' . $cv. '</p>';
	echo '<p><img src="' . $avatar . '" alt="user" height="50" /></p>';
	//echo '<p>Avatar: ' . htmlspecialchars($emp->avatar, ENT_QUOTES, 'UTF-8') . '</p>';
	echo '<hr>';
	
}
?>