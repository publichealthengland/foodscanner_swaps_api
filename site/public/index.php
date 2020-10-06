<?php

/*
 */


require_once(__DIR__ . '/../bootstrap.php');

if (isset($_ENV['ENVIRONMENT']) && $_ENV['ENVIRONMENT'] === "dev")
{
    ini_set('display_errors', 1);
}
else
{
    ini_set('display_errors', 0);
}

$app = Slim\Factory\AppFactory::create();

if (isset($_ENV['ENVIRONMENT']) && $_ENV['ENVIRONMENT'] === "dev")
{
    $app->addErrorMiddleware($displayErrorDetails=true, $logErrors=true, $logErrorDetails=true);
}


// register controllers
SwapsController::registerRoutes($app);

$app->run();