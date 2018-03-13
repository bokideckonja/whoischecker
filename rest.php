<?php

header('Content-Type: application/json');
require __DIR__.'/vendor/autoload.php';

use WhoisChecker\Domain;

$domain = new Domain(@$_GET["domain"]);

$data = [];
$data['success'] = true;
$data['available'] = true;
$data['domain'] = $domain->domain;


try{
    // Check if domain is taken
    if( !$domain->isAvailable()){
        $data['available'] = false;
        // Query whois data
        $data['whois'] = $domain->whois();
    }
}catch(Exception $e){
    $data['success'] = false;
    $data['message'] = $e->getMessage();
}

echo json_encode($data);