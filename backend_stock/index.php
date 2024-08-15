<?php

// ---------------- Debug
define('DEBUG', true);

$mode = (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) ? 'development' : 'local';

define('ENV_TYPE', $mode);

date_default_timezone_set("America/Chicago");
//date_default_timezone_set("UTC");

// -----------------------------

//ob_implicit_flush(true);
//ob_end_flush();

// ---------------- Linux or NOT ------------
$is_linux = strtoupper(substr(PHP_OS, 0, 3)) === 'LIN';
define('IS_LINUX', $is_linux);

// ---------------- Settings -------------
define('ERROR_REPORTING', E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

// ---------- Error reporting
if (DEBUG) {
  error_reporting(ERROR_REPORTING);
  ini_set("display_errors", 1);
} else {
  error_reporting(0);
  ini_set("display_errors", 0);
}


// ----------- Folders ----------------
define('HD', __DIR__);
define('DS', DIRECTORY_SEPARATOR);

require_once(HD . DS . 'classes' . DS . 'autoloader.php');

JDotEnv::parseEnvData();

if (isset($_SERVER['HTTP_ORIGIN'])) {
  $http_origin = $_SERVER['HTTP_ORIGIN'];
  $allowed_domains = JDotEnv::get('HOSTS_ALLOWED', [], 'HOSTS');
  if (in_array($http_origin, $allowed_domains)) {
    header("Access-Control-Allow-Origin: $http_origin");
  }
}


header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Node-Env, Authorization, access-control-app-auth');
header('Access-Control-Allow-Credentials: true');
//authorization,content-type,node-env

define('API_METHOD', $_SERVER['REQUEST_METHOD']);

if (API_METHOD === 'OPTIONS') {
  exit;
}

(new ProQuery())->setDb('DB');

require_once(HD . DS . 'Router.php');
Router::run();
