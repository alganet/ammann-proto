<?php

// Boilerplate
namespace Ammann;
require 'src/Exceptions/InvalidPrefix.php';
require 'src/Exceptions/MissingKey.php';
require 'src/Exceptions/MissingParameter.php';
require 'src/Exceptions/InvalidCall.php';
require 'src/Injector.php';
require 'src/Container.php';


date_default_timezone_set('UTC');

{ // INI Configuration
    $iniConfig = new Container(
        parse_ini_file("etc/example.ini", true)
    );
    print_r($iniConfig->inject('examplePeriod'));
}

{ // JSON Configuration
    $jsonConfig = new Container(
        json_decode(file_get_contents("etc/example.json"), true)
    );
    print_r($jsonConfig->inject('examplePeriod'));
}

{ // MissingKey Exception
    $missingKeyConfig = new Container(
        parse_ini_file("etc/example.ini", true)
    );
    try {
        print_r($missingKeyConfig->inject('oan'));
    } catch (Exceptions\MissingKey $e) {
        print_r($e->getMessage() . PHP_EOL);
    }
}



{ // MissingKey Exception
    $missingKeyConfig = new Container(
        parse_ini_file("etc/example.ini", true)
    );
    try {

        print_r($missingKeyConfig->inject(''));
    } catch (Exceptions\MissingKey $e) {
        print_r($e->getMessage() . PHP_EOL);
    }
}


{ // MissingKey NotEmpty Exception
    $missingKeyConfig = new Container(
        parse_ini_file("etc/example.ini", true)
    );
    try {

        print_r($missingKeyConfig->inject('klebsbak'));
    } catch (Exceptions\MissingKey $e) {
        print_r($e->getMessage() . PHP_EOL);
    }
}

{ // InvalidPrefix Exception
    try {
        $invalidPrefixConfig = new Container(
            ['aaa' => 'bb']
        );
    } catch (Exceptions\InvalidPrefix $e) {
        print_r($e->getMessage() . PHP_EOL);
    }
}

{ // 
    try {
        $missingParameter = new Container(
            ['@invalidPDO' => [
                'PDO' => []
            ]]
        );
        print_r($missingParameter->inject('invalidPDO'));
    } catch (Exceptions\MissingParameter $e) {
        print_r($e->getMessage() . PHP_EOL);
    }
}

{ // 
    try {
        $interval = new Container(
            ['@interval' => [
                'DateInterval' => [
                ]
            ]]
        );
        print_r($interval->inject('interval'));
    } catch (Exceptions\InvalidCall $e) {
        print_r($e->getMessage() . PHP_EOL);
    }
}
