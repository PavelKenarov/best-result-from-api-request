<?php
require_once 'includes/config.php';

$page = empty($_GET['p']) ? 1 : (int)$_GET['p'];
$perPage = 30;
$offset = ($page - 1) * $perPage;
if($offset < 0)
	$offset = 0;

$db = db::getInstance();
$mysqli = $db->getConnection(); 

$employees = [];
$get = $mysqli->query(' SELECT `avatar`, `name`, `title`, `company`, `bio` FROM `employees` 
WHERE `status` = "finished" ORDER BY ts LIMIT  ' . $offset . ',' . $perPage);
while ($e = $get->fetch_assoc()) {
	
	if(empty($e['bio']))
		unset($e['bio']);
	
	$employees[] = $e;
}

header('Content-Type: application/json');
exit(json_encode($employees));
?>