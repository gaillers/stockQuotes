<?php
Headers::parseHeaders();

class Router
{
  static public $uri;
  static public $method;

  static private function execute()
  {
    // Инициализация приложения и маршрутизация
    $app = new App();

    $php_self = preg_replace('/index.php$/i', '', $_SERVER['PHP_SELF']);
    $php_self = rtrim($php_self, '/');

    // Обработка маршрутов
    $app->get($php_self . '/get-stats', 'StatsController::getStats');
    $app->post($php_self . '/save-stats', 'StatsController::saveStats');

    // Если ни один маршрут не сработал, возвращаем ошибку 400
    // App::json([
    //   'reason' => 'Bad request',
    //   'message' => 'Bad request',
    // ], 400);
    // exit;
  }

  static public function run()
  {
    // Извлекаем URI и метод запроса
    self::$uri = preg_replace('/\?.*$/', '', $_SERVER['REQUEST_URI']);
    self::$method = $_SERVER['REQUEST_METHOD'];

    // Запуск маршрутизации
    self::execute();
  }

  static public function getData()
  {
    return [
      'uri' => self::$uri,
      'method' => self::$method
    ];
  }
}
