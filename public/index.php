<?php

// Подавляем deprecated предупреждения из-за несовместимости DiDom\Document с php 8.3
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use DI\Container;
use App\UrlValidator;
use App\Url;
use App\UrlsRepository;
use App\Check;
use App\ChecksRepository;
use DiDom\Document;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

$container = new Container();

// У строки следующий формат: {provider}://{user}:{password}@{host}:{port}/{db}
// export DATABASE_URL=postgresql://janedoe:mypassword@localhost:5432/mydb

$env = getenv('DATABASE_URL');
if ($env === false) {
    throw new \InvalidArgumentException('DATABASE_URL is not set');
}
$databaseUrl = parse_url($env);
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

// Set view in Container
$container->set(Twig::class, function () {
    return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
}); // Флеш сообщения в контейнер добавляем

// Create App from container
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $container->get(Twig::class)));

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function (Request $request, Response $response) {

    $messages = $this->get('flash')->getMessages();

    $params = [
        'flash' => $messages
    ];
    return $this->get(Twig::class)->render($response, 'index.twig', $params);
})->setName('main');

// обработчик на urls
$app->get('/urls', function (Request $request, Response $response) {

    $urlsRepository = $this->get(UrlsRepository::class);
    $urls = $urlsRepository->all();//возвращает массив всех url
    $messages = $this->get('flash')->getMessages();
    $table = [];
    foreach ($urls as $url) {
        $row['id'] = $url->getId();
        $row['name'] = $url->getName();
        $row['last_check'] = $this
            ->get(ChecksRepository::class)
            ->findLastCreatedAtByUrlId($url->getId());
        $row['last_status_code'] = $this
            ->get(ChecksRepository::class)
            ->findLastStatusCodeByUrlId($url->getId());
        $table[] = $row;
    }
    $params = [
        'flash' => $messages,
        'urls' => $urls,
        'table' => $table
    ];

    return $this->get(Twig::class)->render($response, 'urls/index.twig', $params);
})->setName('urls.index');

$app->post('/urls', function (Request $request, Response $response) use ($router) {
    $data = $request->getParsedBody(); // [[url] => [name => 'https://example.com']]
    $data = is_array($data) ? $data : [];
    $urlData = $data['url'] ?? ['name' => null]; // [name => 'https://example.com']

    $errors = UrlValidator::validate($urlData); // array c ошибками

    if (!empty($errors)) {
        $params = [
            'url' => $urlData,
            'errors' => $errors,
        ];
        return $this->get(Twig::class)->render($response->withStatus(422), 'index.twig', $params);
    }

    $url = new Url($urlData['name']);

    $urlsRepository = $this->get(UrlsRepository::class);

    if ($urlsRepository->findByName($url->getName()) !== null) {
        $id = $urlsRepository->findByName($url->getName())->getId();
        $this->get('flash')->addMessage('success', 'Страница уже существует');
        return $response
            ->withHeader('Location', $router->urlFor('urls.show', ['id' => (string) $id]))
            ->withStatus(302);
    }
    //Возвращает объект Url с обновлённым ID и временем создания
    $urlFromDb = $urlsRepository->save($url);

    $id = $urlFromDb->getId();

    $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    return $response
        ->withHeader('Location', $router->urlFor('urls.show', ['id' => (string) $id]))
        ->withStatus(302);
});

$app->get('/urls/{id}', function (Request $request, Response $response, $args) {

    // получаем id из адреса
    $id = (int) $args['id'];
    $urlsRepository = $this->get(UrlsRepository::class);
    $url = $urlsRepository->find($id);

    if (!$url) {
        // 404 если не нашли
        $response->getBody()->write('Not found');
        return $response->withStatus(404);
    }

    $checksRepository = $this->get(ChecksRepository::class);
    // все проверки в формате Check массивом возвращаем
    $allChecksUrlId = $checksRepository->findAllByUrlId($id);
    $params = [
        'url' => $url,
        'checks' => $allChecksUrlId,
        'flash' => $this->get('flash')->getMessages()
    ];
    return $this->get(Twig::class)->render($response, 'urls/show.twig', $params);
})->setName('urls.show');

$app->post('/urls/{url_id}/checks', function (Request $request, Response $response, $args) use ($router) {

    $urlId = (int) $args['url_id'];
    // проверяем есть ли такой сайт по айди
    $urlsRepository = $this->get(UrlsRepository::class);
    $urlIdFromBd = $urlsRepository->find($urlId);
    if (!$urlIdFromBd) {
        $this->get('flash')->addMessage('errors', 'Произошла ошибка, проверка не выполнена');
        return $response
        ->withHeader('Location', $router->urlFor('urls.show', ['id' => (string) $urlId]))
        ->withStatus(302);
    }
    // получает status_code, обрабатывая исключения
    $client = new \GuzzleHttp\Client();
    $nameUrl = $urlIdFromBd->getName();
    try {
        $res = $client->request('GET', $nameUrl);
        $statusCode = $res->getStatusCode();
        $html = (string) $res->getBody();

        $document = new Document($html);
    } catch (\GuzzleHttp\Exception\ClientException $e) {
        $this->get('flash')->addMessage('errors', 'Сетевая ошибка, проверка не выполнена');
        return $response
            ->withHeader('Location', $router->urlFor('urls.show', ['id' => (string) $urlId]))
            ->withStatus(302);
    }
    // получаем h1, title, description
    $h1 = optional($document->first('h1'))->text();
    $title = optional($document->first('title'))->text();
    $desc = $document->first('meta[name=description]')?->getAttribute('content') ?? null;

    // создаем новую сущность - id и created_at само создается
    $check = new Check(urlId: $urlId, statusCode: $statusCode, h1: $h1, title: $title, description: $desc);

    // эту сущность добавляем в БД и возвращаем объект Check с обновлённым ID и временем создания
    $checksRepository = $this->get(ChecksRepository::class);
    $checksRepository->save($check);
    $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    return $response
        ->withHeader('Location', $router->urlFor('urls.show', ['id' => (string) $urlId]))
        ->withStatus(302);
});

$app->run();
