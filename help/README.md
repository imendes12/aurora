# Mapinha - Arquitetura

Essa é a documentação das decisões tećnicas, voltada para desenvolvedores e/ou entusiastas do código.

<details>
    <summary>Acesso Rápido</summary>
    
[Instalação dos Pacotes](#Instalação)<br>
[Controller](#API)<br>
[Repository](#Repository)<br>
[Command](#Command)<br>
[Data Fixtures](#Data-Fixtures)<br>
[Testes](#Testes)<br>
[Console](#console-commands)<br>

</details>

## Getting Started

### Instalação dos pacotes

Para instalar as dependências e atualizar o autoload, entre no container da aplicação e execute:
```shell
composer install
```

--- 

## Controller

Os `Controllers` em conjunto com as `Routes` permitem criar endpoints para diversas finalidades.

<details>
<summary>Como criar um novo controller</summary>

#### 1 - Controller
Crie uma nova classe em `/app/Controller/Api/`, por exemplo, `EventApiController.php`:

```php
<?php

declare(strict_types=1);

namespace App\Controller\Api;

class EventApiController
{
    
}
```

#### 2 - Método/Action
Crie seu(s) método(s) com a lógica de resposta.

> Para gerar respostas em json, estamos utilizando a implementação da `JsonResponse` fornecida pelo pacote do Symfony:
> Para gerar respostas em HTML, estamos utilizando a implementação da `Response` (`Twig`) fornecida pelo pacote do Symfony:

```php
<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class EventApiController
{
    public function getList(): JsonResponse
    {
        $events = [
            ['id' => 1, 'name' => 'Palestra'],
            ['id' => 2, 'name' => 'Curso'],
        ];   
    
        return new JsonResponse($events);
    }
    
    public function getList(): Response
    {
        $events = [
            ['id' => 1, 'name' => 'Palestra'],
            ['id' => 2, 'name' => 'Curso'],
        ];   
    
        return $this->render('view.html.twig', $events);
    }
}
```

#### 3 - Rotas

Acesse os arquivos das rotas em `/config/routes` lá nós estamos separando as rotas em API e Web

```yaml
get:
  path: /example
  controller: App\Controller\Admin\ExampleAdminController::action
  methods: ['GET']
```

Atente-se para seguir o padrão, um arquivo `.yaml` por controller

#### 4 - Pronto

Feito isso, seu endpoint deverá estar disponivel em:
<http://localhost:8080/o-que-voce-definiu-como-path>

E deve estar retornando um JSON ou uma página web, dependendo da action que você criou.

</details>

---

## Repository

A camada responsável pela comunicação entre nosso código e o banco de dados.

<details>
<summary>Como criar um novo repository</summary>

Siga o passo a passo a seguir:

#### Passo 1 - Crie sua classe no `/app/src/Repository` e extenda a classe abstrata `AbstractRepository`

```php
<?php

declare(strict_types=1);

namespace App\Repository;

class MyRepository extends AbstractRepository
{
}
```

#### Passo 2 - Defina a Entity principal que esse repositório irá gerenciar

```php

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\MyEntity;
...

public function __construct(ManagerRegistry $registry)
{
    parent::__construct($registry, MyEntity::class);
}
```

</details>

---

## Migrations
Migrations são a forma (correta) de fazer um versionamento do banco de dados, nesta parte da aplicação isso é fornecido pela biblioteca `doctrine/migrations` mas no core do MapaCultural isso ainda é feito por uma decisão técnica interna chamada `db-updates.php`

<details>
<summary>Como criar uma nova migration</summary>

#### Passo 1 - Criar uma nova classe no diretório `/app/migrations`

```php
<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241231235959 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        //$this->addSql('CREATE TABLE ...');
    }
    
    public function down(Schema $schema): void
    {
        //$this->addSql('DROP TABLE ...');
    }
}
```

Note que o nome da classe deve informar o momento de sua criação, para que seja mantida uma sequencia temporal da evolução do esquema do banco de dados.

> Documentação oficial das migrations do Doctrine: <https://www.doctrine-project.org/projects/doctrine-migrations/en/3.8/reference/generating-migrations.html>
</details>

## Command
Comandos são entradas via CLI (linha de comando) que permitem automatizar alguns processos, como rodar testes, veririfcar estilo de código, e debugar rotas

<details>
<summary>Como criar um novo console command</summary>

#### Passo 1 - Criar uma nova classe em `app/src/Command/`:

```php
<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MyCommand extends Command
{
    protected static string $defaultName = 'app:my-command';
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello World!');
        
        return Command::SUCCESS;  
    }
} 
```

#### Passo 2 - Testar seu comando no CLI

Entre no container da aplicação PHP e execute isso

```shell
php bin/console app:my-command
```

Você deverá ver na tela o texto `Hello World!`

#### Passo 3 - Documentação do pacote
Para criar e gerenciar os nosso commands estamos utilizando o pacote `symfony/console`, para ver sua documentação acesse:

> Saiba mais em <https://symfony.com/doc/current/console.html>

Para ver outros console commands da aplicação acesse a seção [Console Commands](#console-commands)

</details>

---

## Data Fixtures
Data Fixtures são dados falsos, normalmente criados para testar a aplicação, algumas vezes são chamados de "Seeders".

<details>
<summary>Como criar uma DataFixture para uma Entidade</summary>

#### Passo 1 - Criar uma nova classe em `app/src/DataFixtures/`:

```php
<?php

namespace App\DataFixtures;

use Doctrine\Persistence\ObjectManager;
use MapasCulturais\Entities\Agent;

class AgentFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $agent = new Agent();
        $agent->name = 'Agente Teste da Silva';
        
        $manager->persist($agent);
        $manager->flush();
    }
} 
```

#### Passo 2 - Executar sua fixture no CLI

Entre no container da aplicação PHP e execute isso

```shell
php bin/console doctrine:fixtures:load
```

Pronto, você deverá ter um novo Agente criado de acordo com a sua Fixture.

> Saiba mais sobre DataFixtures em <https://www.doctrine-project.org/projects/doctrine-data-fixtures/en/1.7/index.html>

</details>

---

## Testes
Estamos utilizando o PHPUnit para criar e gerenciar os testes automatizados, focando principalmente nos testes funcionais, ao invés dos testes unitários.

Documentação do PHPUnit: <https://phpunit.de/index.html>

<details>
<summary>Como criar um novo teste</summary>

### Criar um novo teste
Para criar um no cenário de teste funcional, basta adicionar sua nova classe no diretório `/app/tests/functional/`, com o seguinte código:

```php
<?php

namespace App\Tests\Functional;

class MeuTest extends AbstractTestCase
{
    
}
```

Adicione dentro da classe os cenários que você precisa garantir que funcionem, caso precise imprimir algo na tela para "debugar", utilize o método `dump()` fornecido pela classe `AbstractTestCase`:

```php
public function testIfOneIsOne(): void
{
    $list = ['Mar', 'Minino'];
    
    $this->dump($list); // equivalente ao print_r
    
    $this->assertEquals(
        'MarMinino',
        implode('', $list)
    );
}
```

Para executar os testes veja a seção <a href="#console-commands">Console Commands</a>
</details>

---

## DI (Injeção de dependência)
A injeção de dependência já acontece automaticamente pela própria estrutura do Symfony

## Console Commands

<details>
<summary>CACHE CLEAR</summary>

### Limpar cache
Para executar o comando de limpar cache basta entrar no container da aplicação e executar o seguinte comando:

```shell
php bin/console cache:clear
```

</details>

<details>
<summary>COMMAND SQL</summary>

### Executar código SQL
Para executar um comando SQL basta entrar no container da aplicação e executar o seguinte comando:

```shell
php bin/console doctrin:query:sql {sql}
```
O argumento chamado de `sql` é requerido e é o comando a ser executar no banco de dados.
</details>

<details>
<summary>TESTS</summary>

### Testes Automatizados
Para executar os testes, entre no container da aplicação e execute o seguinte comando:

```shell
php bin/phpunit {path}
```

O `path` é opcional, o padrão é "/tests"
</details>

<details>
<summary>STYLE CODE</summary>

### Style Code
Para executar o PHP-CS-FIXER basta entrar no container da aplicação e executar

```shwll
php bin/console app:code-style
```
</details>

<details>
<summary>DATA FIXTURES</summary>

### Debug router
Para listas as routas basta entrar no container da aplicação e executar
```
php bin/console debug:router
```

> Podemos usar as flags --show-actions e --show-controllers
</details>

<details>
<summary>DOCTRINE</summary>

### Doctrine
Para listas todos os comandos disponiveis para gerenciamento do banco de dados através do doctrine basta entrar no container da aplicação e executar
```
php bin/doctrine
```

</details>
