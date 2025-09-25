<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;
use App\UrlValidator;
use App\Url;
use App\UrlsRepository;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();

// У строки следующий формат: {provider}://{user}:{password}@{host}:{port}/{db}
// export DATABASE_URL=postgresql://janedoe:mypassword@localhost:5432/mydb

$databaseUrl = parse_url($_ENV['DATABASE_URL']);
$username = $databaseUrl['user']; // janedoe
$password = $databaseUrl['pass']; // mypassword
$host = $databaseUrl['host']; // localhost
$port = $databaseUrl['port']; // 5432
$dbName = ltrim($databaseUrl['path'], '/'); // mydb

// Подключение к базе данных
try {
    $container->set(\PDO::class, function () {
        $conn = new PDO("postgresql:host={$host};dbname={$dbName}", $username, $password);
        $conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        return $conn;
    });
} catch (PDOException $e) {
    // Например, через какое-то время повторить попытку соединения
}


$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
}); // Флеш сообщения в контейнер добавляем

// $app = AppFactory::create();
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
    return $this->get('renderer')->render($response, 'index.phtml');
});

// обработчик на urls
$app->get('/urls', function (Request $request, Response $response) {
    return $this->get('renderer')->render($response, 'urls/index.phtml');
});

$app->post('/urls', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    // var_dump($data); // посмотреть что прилетает из формы
    
    $errors = [];
    $errors = UrlValidator::validate($data);

    if (!empty($errors)) {
        // var_dump($errors); // посмотреть ошибки валидации
        $this->get('flash')->addMessage('errors', $errors);
        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }
    $url = new Url($data);
    $urlsRepository = $this->get(UrlsRepository::class);
    $urlsRepository->save($url);    
    // var_dump($url); // посмотреть валидный URL
    $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    return $response
        ->withHeader('Location', '/urls')
        ->withStatus(302);
});

$app->run();
