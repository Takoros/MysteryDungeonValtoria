<?php

namespace App\Service;

class APIService
{
    private $discordBotToken;

    public function __construct($discordBotToken)
    {
        $this->discordBotToken = $discordBotToken;
    }

    public function getApiToken(){
        return $this->discordBotToken;
    }

    /**
     * Verifies if the token is valid
     */
    public function isCorrectToken($token){
        return $this->discordBotToken === $token ? true : false;
    }

    /**
     * Verifies that the data sent correspond that to what is awaited
     */
    public function hasCorrectData($awaitedData, $receivedData){
        $hasCorrectData = true;

        if(is_object($receivedData)){
            $receivedData = (array) $receivedData;
            foreach($awaitedData as $awaitedKey){
                if(!array_key_exists($awaitedKey, $receivedData)){
                    $hasCorrectData = false;
                }
            }
        }
        else {
            $hasCorrectData = false;
        }

        return $hasCorrectData;
    }
}
