<?php

namespace WhoisChecker;

use Exception;

class Domain{

	private $validTlds;
	private $domain;
	private $tld;

	public function __construct($domain){
		// Get valid tlds from extracted file
		$this->validTlds = require __DIR__.'/tlds.php';

		// Clean and set provided domain
		$this->domain = $this->cleanDomain($domain);

		// Set tld based on domain name
		$this->tld = $this->extractTld();
	}

	public function whois($domain){
		$this->validateDomain();
		$this->validateTld();

		return $this->queryWhoisServer();
	}

	// Query the domain against correct whois server.
	protected function queryWhoisServer($domain){
		$whoisServer = $this->validTlds[$this->tld];
		if(!$whoisServer){
			throw new Exception('There is no whois server for that TLD.');
		}

		// die(var_dump($tld));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $whoisServer.":43"); // Whois Server
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->domain."\r\n"); // Query
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$result = curl_exec ($ch);
		if( curl_error($ch) ){
			throw new Exception('Error checking domain.');
		}
		curl_close($ch);
		return $result;

		// curl session to get whois reposnse
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        // curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // $data = curl_exec($ch);
        // if (curl_error($ch)) {
        //     return "Connection error!";
        // } else {
        //     $string = strip_tags($data);
        // }
        // curl_close($ch);
	}

	// Check if domain is available
	public function isAvailable(){
		if( checkdnsrr($this->domain.'.', 'ANY')){
			return false;
		}
		return true;
	}

	// Clean up entered domain
	protected function cleanDomain($domain){

		// Trim string and convert to lowercase
		$clean = mb_strtolower( trim( $domain ) );

		// Remove leading "http://"
		if ( mb_substr($clean, 0, 7) == "http://" ) {
		    $clean = mb_substr($clean, 7);
		}

		// Remove leading "https://"
		if ( mb_substr($clean, 0, 8) == "https://" ) {
		    $clean = mb_substr($clean, 8);
		}

		// Remove leading "www."
		if ( mb_substr($clean, 0, 4) == "www." ) {
		    $clean = mb_substr($clean, 4);
		}

		// Remove everithing after "/"
		if ( mb_strpos( $clean, "/" ) !== false ) {
			$clean = mb_substr( $clean, 0, mb_strpos( $clean, "/" ) );
		}

		// Remove space characters
		$clean = preg_replace('/\s+/', '', $clean);

		return $clean;
	}

	// Check if domain is valid
	protected function validateDomain(){

		// Check if domain is in correct format
		if( preg_match( "/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/", $this->domain ) == false ){
			throw new Exception('Entered domain is not valid.');
		}

		// Check if domain is correct length
		if( mb_strlen($this->domain) >= 253 ){
			throw new Exception('Entered domain is not valid.');
		}

		return;
	}

	// Extract tld from domain name, if there isn't any, use .com
	protected function extractTld(){
		$fragments = explode( ".", $this->domain );
		if(count($fragments) >= 2){
			return array_pop($fragments);
		}else{
			return "com";
		}
	}

	// Check if there is a whois server for given TLD
	protected function validateTld(){
		return $this->validTlds[$this->tld] ?? false;
	}
}