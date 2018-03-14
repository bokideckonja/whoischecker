<?php

namespace WhoisChecker;

use Exception;

class Domain{
    // Default TLD, used if not provided
    private $defaultTLD = "com";

    // List of valid TLDs
    private $validTLDs;

    // Provided domain
    public $domain;

    // Provided TLD
    private $TLD;

    public function __construct($domain){
        // Get valid tlds from extracted file
        $this->validTLDs = require __DIR__.'/TLDs.php';

        // Clean and set provided domain
        $this->domain = $this->cleanDomain($domain);

        // Set tld based on domain name
        $this->TLD = $this->extractTld();
    }

    // Get whois data for provided domain
    public function whois(){
        // Check if domain is valid
        $this->validateDomain();

        return $this->queryWhoisServer();
    }

    // Query the domain against correct whois server.
    protected function queryWhoisServer(){
        // Get whois server based on TLD
        $whoisServer = $this->validTLDs[$this->TLD]['server'];

        // If the server is not in provided list, throw error
        if(!$whoisServer){
            throw new Exception('There is no whois server for that TLD.');
        }

        $port = 43;
        $timeout = 20;
        // Open socket
        $fp = @fsockopen($whoisServer, $port, $errno, $errstr, $timeout);
        // If it fails to connect, throw error
        if(!$fp){
            throw new Exception("Error connecting to whois server.");
        }
        // Send domain
        fputs($fp, '' . $this->domain . "\r\n");
        // Get response
        $response = "";
        while(!feof($fp)){
            $response .= fgets($fp);
        }
        // Close socket
        fclose($fp);

        // Build return
        $return['available'] = $this->isAvailable($response);
        if(!$return['available']){
            $return['whois'] = $this->cleanWhois($response);
        }else{
            $return['whois'] = false;
        }

        return $return;
    }

    // Check if domain is available
    public function isAvailable($whois){
        // Check if the $whois contains not_found string(true means it's available)
        if( isset($this->validTLDs[$this->TLD]["not_found"]) ){
            if ( mb_strpos($whois, $this->validTLDs[$this->TLD]["not_found"]) === false ) {
                // Domain is not available
                return false;
            }
        // If we don't have not_found pattern, try less secure code
        }else{
            // Check if DNS records exist
            if ( gethostbyname($this->domain) !== $this->domain || checkdnsrr($this->domain.'.', 'NS') ) {
                // Domain is not avalable
                return false;
            }
        }

        return true;
    }

    // Clean whois response, and returns it
    private function cleanWhois($whois){
        $lines = explode("\n", $whois);
        $buffer = "";
        $end = false;
        foreach($lines as $line){
            if($end == true){
                continue;
            }
            // Clean line
            $line = trim($line);
            // Clean empty strings and commented header info
            if($line != '' && mb_substr($line, 0, 1) != '%' && mb_substr($line, 0, 1) != '#'){
                $buffer .= $line."\n";
            }

            // Clear after this, it's removing legal info
            if(mb_strpos($line, '>>> Last update') !== false){
                $end = true;
            }
        }

        return $buffer;
    }

    // Clean up entered domain and return it
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

        // Check if there is a whois server for given TLD
        if( !isset($this->validTLDs[$this->TLD]) ){
            throw new Exception('Unsupported TLD.');
        }

        return;
    }

    // Extract TLD from domain name, if there isn't any, use default
    protected function extractTld(){
        $fragments = explode( ".", $this->domain );
        if(count($fragments) >= 2){
            // Return last fragment as TLD
            return array_pop($fragments);
        }else{
            // Change domain to use default
            $this->domain .= ".".$this->defaultTLD;

            return $this->defaultTLD;
        }
    }
}