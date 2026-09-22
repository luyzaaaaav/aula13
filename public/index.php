<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();


$livros = [
    ['id' => 1, 'nome' => 'Alice no Pais das Maravilhas'],
    ['id' => 2, 'nome' => 'Coraline'],
    ['id' => 3, 'nome' => 'O Pequeno Principe'],
    ['id' => 4, 'nome' => 'O Principe Cruel'],
    ['id' => 5, 'nome' => 'Melhor Do Que Nos Filmes'],
    ['id' => 6, 'nome' => 'Harry Potter']
];

$app->get('/livros/{id}', function ($request, $response, $args) use (&$livros) {
    $id = (int) $args['id'];
    $livro = current(
        array_filter(
            $livros,
            fn($p) => $p['id'] === $id
        )
    );
    // livro não encontrado
    if (!$livro) {
        $response->getBody()->write(
            json_encode([
                'erro' => 'livro nao encontrado'
            ])
        );
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }
    // livro encontrado
    $response->getBody()->write(
        json_encode($livro)
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->post('/livros', function ($request, $response) use (&$livros) {
    // Recebe os dados enviados em JSON
    $dados = $request->getParsedBody();
    // Verifica se o nome foi informado
    if (!isset($dados['nome']) || empty($dados['nome'])) {
        $response->getBody()->write(
            json_encode([
                'erro' => 'O nome é obrigatório'
            ])
        );
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }
    // Cria um novo livro
    $novolivro = [
        'id' => count($livros) + 1,
        'nome' => $dados['nome']
    ];
    // Adiciona o livro ao array
    $livros[] = $novolivro;
    // Retorna o livro criado
    $response->getBody()->write(
        json_encode($novolivro)
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

$app->put('/livros/{id}', function ($request, $response, $args) use (&$livros) {
    $dados = $request->getParsedBody();

 
    foreach ($livros as &$livro) {
        if ($livro['id'] === (int) $args['id']) {
            $livro['nome'] = $dados['nome'];
            $response->getBody()->write(json_encode($livro));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    } 
    return $response->withStatus(404);
});

$app->get('/livros', function ($request, $response, $args) use (&$livros) {
    $queryParams = $request->getQueryParams();
    $nome = $queryParams['nome'] ?? null;
    if ($nome) {
        $filtrados = array_filter($livros, fn($item) => str_contains(mb_strtolower($item['nome']), mb_strtolower($nome)));
        $response->getBody()->write(json_encode($filtrados));
    } else {
        $response->getBody()->write(json_encode($livros));
    }
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->delete('/livros/{id}', function ($request, $response, $args) use (&$livros) {
    $livros = array_values(array_filter($livros, fn($p) => $p['id'] !== (int) $args['id']));
    return $response->withStatus(204);
});

$app->run();
