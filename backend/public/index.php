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
$settings = require __DIR__ . '/../src/config/settings.php';
$app = new \Slim\App($settings);

//Register db
require __DIR__ . '/../src/components/db.php';

// Set up dependencies
require __DIR__ . '/../src/components/dependencies.php';

// Register middleware
require __DIR__ . '/../src/components/middleware.php';

// Register routes
require __DIR__ . '/../src/routes/routes.php';

require __DIR__.'/../src/routes/project/reviewRoutes.php';



// Run app
$app->run();
