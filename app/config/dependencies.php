<?php

use League\Plates\Engine;
use Medoo\Medoo;
use Nofw\commands\DBCommand;
use Nofw\commands\DefaultCommand;
use Nofw\Core\Config;
use Nofw\Core\Constants;
use Nofw\Services\TranslationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Console\Application;
use Tuupola\Middleware\JwtAuthentication;
use Unicate\LanguageDetection\LanguageDetection;
use Unicate\Logger\Logger;
use Unicate\Translation\Translation;
use \Psr\Log\LoggerInterface;

return [

    Config::class =>
        \DI\autowire()->constructor(Constants::CONFIG_FILE),

    Application::class => function (Config $config) {
        $application = new Application('echo', '1.0.0');
        $command = new DBCommand();
        $application->add($command);

        $application->setDefaultCommand($command->getName(), true);

        return $application;
    },



];


 
