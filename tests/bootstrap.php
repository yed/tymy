<?php

require __DIR__ . '/../vendor/autoload.php';

$GLOBALS["username"] = "autotest";
$GLOBALS["password"] = "123456.";

Tester\Environment::setup();

$configurator = new Nette\Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(__DIR__ . '/../app')
        ->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/../app/config/config.neon');
$configurator->addConfig(__DIR__ . '/../app/config/config.local.neon');
return $configurator->createContainer();
