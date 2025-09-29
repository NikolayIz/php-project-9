<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;
use App\UrlValidator;
use App\Url;
use App\UrlsRepository;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$container = new Container();

// У строки следующий формат: {provider}://{user}:{password}@{host}:{port}/{db}
// export DATABASE_URL=postgresql://janedoe:mypassword@localhost:5432/mydb

$databaseUrl = parse_url(getenv('DATABASE_URL'));
$username = $databaseUrl['user']; // janedoe
$password = $databaseUrl['pass']; // mypassword
$host = $databaseUrl['host']; // localhost
$dbName = ltrim($databaseUrl['path'], '/'); // mydb

// Подключение к базе данных
$container->set(\PDO::class, function () use ($host, $dbName, $username, $password) {
    try {
        $dsn = "pgsql:host={$host};dbname={$dbName}";
        $conn = new PDO($dsn, $username, $password);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        // В продакшене: логируем и показываем что-то общее
        dump('Ошибка подключения к базе данных');
        exit;
    }
});

$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    $renderer = new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
    $renderer->setLayout('layout.php'); // сразу задаём общий layout
    return $renderer;
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
}); // Флеш сообщения в контейнер добавляем

// $app = AppFactory::create();
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function (Request $request, Response $response) {
    $messages = $this->get('flash')->getMessages();

    $params = [
        'flash' => $messages
    ];
    return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('main');

// обработчик на urls
$app->get('/urls', function (Request $request, Response $response) {
    $urlsRepository = $this->get(UrlsRepository::class);
    $urls = $urlsRepository->all();//возвращает массив всех url

    $messages = $this->get('flash')->getMessages();

    $params = [
        'flash' => $messages,
        'urls' => $urls
    ];

    return $this->get('renderer')->render($response, 'urls/index.phtml', $params);
})->setName('urls.index');

$app->post('/urls', function (Request $request, Response $response) use ($router) {
    $data = $request->getParsedBody(); // [[url] => [name => 'https://example.com']]
    $urlData = $data['url']; // [name => 'https://example.com']
    // dump($urlData);
    // посмотреть что прилетает из формы в локальном окружении НЕ РАБОТАЕТ ИЛИ КУДА ВЫВОДИТ ХЗ

    $errors = [];
    $errors = UrlValidator::validate($urlData); // url['name'] => 'https://example.com'

    if (!empty($errors)) {
        $params = [
            'url' => $urlData,
            'errors' => $errors
        ];
        return $this->get('renderer')->render($response, 'index.phtml', $params);
    }

    $url = new Url($urlData['name']);

    $urlsRepository = $this->get(UrlsRepository::class);

    if ($urlsRepository->findByName($url->getName()) !== null) {
        $id = $urlsRepository->findByName($url->getName())->getId();
        $this->get('flash')->addMessage('success', 'Страница уже существует');
        return $response
            ->withRedirect($router->urlFor('urls.show', ['id' => $id]))
            ->withStatus(302);
    }
    $urlFromDb = $urlsRepository->save($url);//Возвращает объект Url с обновлённым ID и временем создания.
    $id = $urlFromDb->getId();

    $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    return $response
        ->withRedirect($router->urlFor('urls.show', ['id' => $id]))
        ->withStatus(302);
});

$app->get('/urls/{id}', function (Request $request, Response $response, $args) use ($router) {
    $id = (int) $args['id'];             // получаем id из адреса
    $urlsRepository = $this->get(UrlsRepository::class);
    $url = $urlsRepository->find($id);

    if (!$url) {
        // 404 если не нашли
        $response->getBody()->write('Not found');
        return $response->withStatus(404);
    }

    $params = [
        'url' => $url,
        'flash' => $this->get('flash')->getMessages()
    ];

    return $this->get('renderer')->render($response, 'urls/show.phtml', $params);
})->setName('urls.show');

$app->run();
