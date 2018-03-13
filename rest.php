<?php

require __DIR__.'/vendor/autoload.php';

use WhoisChecker\Domain;

$domain = new Domain(@$_GET["domain"]);

$data = [];
$data['success'] = true;
$data['available'] = true;

// Check if domain is taken
if( !$domain->isAvailable()){
	$data['available'] = false;

	// Query whois data
	try{
		$data['whois'] = $domain->whois();
	}catch(Exception $e){
		$data['success'] = false;
		$data['message'] = $e->getMessage();
	}
}

echo json_encode($data);