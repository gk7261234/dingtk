<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../api/config/settings.php';
$app = new \Slim\App($settings);

//Register db
require __DIR__ . '/../api/components/db.php';

//service
require __DIR__ . '/../api/service/FProjectRequestService.php';
require __DIR__ . '/../api/service/ProjectService.php';
require __DIR__.'/../api/service/DDRemarkService.php';

//mailer
require __DIR__ .'/../api/components/mailer.php';

// Set up dependencies
require __DIR__ . '/../api/components/dependencies.php';

// Register routes
require __DIR__ . '/../api/routes/routes.php';
require __DIR__.'/../api/routes/project/reviewRoutes.php';
require __DIR__.'/../api/routes/project/loanRoutes.php';

// Register middleware
require __DIR__ . '/../api/components/middleware.php';





// Run app
$app->run();
