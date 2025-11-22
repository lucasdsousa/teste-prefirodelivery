# Gerenciador de Categorias Hierárquicas

Uma aplicação web Fullstack para gerenciamento de categorias e subcategorias com níveis infinitos de profundidade (estrutura de árvore). O projeto utiliza Laravel para o backend/API e Vue.js 3 para a interface reativa, rodando totalmente em ambiente Docker.

# Tecnologias Utilizadas

Backend: PHP 8.2, Laravel 11

Frontend: Vue.js 3 (Composition API), Tailwind CSS

Banco de Dados: MySQL 8.0

Infraestrutura: Docker & Docker Compose (Nginx, PHP-FPM, MySQL)

# Funcionalidades

Autenticação Completa: Login, Registro e Logout utilizando Sessões do Laravel.

Árvore Recursiva: Visualização de categorias e subcategorias em n-níveis.

## CRUD Hierárquico:

Criar categorias raiz ou filhas.

Edição de nome e descrição.

Deleção em Cascata: Ao apagar um pai, todos os filhos/netos são removidos automaticamente.

Busca em Tempo Real: Filtra a árvore mantendo a estrutura visual dos pais.

Interface Responsiva: Painel adaptável para Mobile e Desktop.

# Pré-requisitos

A única dependência necessária é o Docker Desktop instalado e rodando.

Não é necessário ter PHP, Composer ou Node.js instalados na máquina local.

# Como Rodar o Projeto

Siga os passos abaixo para iniciar a aplicação do zero:

1. Configuração do Ambiente

Certifique-se de que o arquivo .env na raiz do projeto está configurado corretamente para o Docker:

DB_CONNECTION=mysql

`DB_HOST=db`

`DB_PORT=3306`

`DB_DATABASE=laravel`

`DB_USERNAME=admin`

`DB_PASSWORD=admin`


2. Subir os Containers

Na raiz do projeto, execute:

`docker-compose up --build -d`


Isso irá construir as imagens do PHP e Nginx e iniciar o banco de dados MySQL.

3. Instalar Dependências (Primeira vez)

Se você acabou de clonar o projeto e não tem a pasta vendor, instale as dependências do Laravel:

`docker-compose exec app composer install`

`docker-compose exec app php artisan key:generate`


# Como Usar

Acesse http://localhost:8000 no seu navegador.

Você será redirecionado para a tela de Login.

Clique em "Registre-se" para criar sua primeira conta.

Após o login, você verá o painel de gerenciamento.

# Estrutura do Projeto

Os arquivos principais modificados neste projeto são:

## Infraestrutura:

docker-compose.yml: Orquestração dos serviços.

Dockerfile: Imagem customizada do PHP com extensões.

## Backend (Laravel):

`app/Models/Categoria.php`: Lógica de relacionamento recursivo e booted() para deleção em cascata.

`app/Http/Controllers/CategoriaController.php`: API para CRUD e carregamento da árvore (children.children...).

`app/Http/Controllers/AuthController.php`: Lógica de Login/Registro manual.

`routes/web.php`: Rotas de autenticação e da API (protegidas por sessão).

## Frontend (Vue.js + Blade):

`resources/views/index.blade.php`: Aplicação Vue principal. Contém todo o código do frontend, incluindo o componente recursivo <tree-item> e a lógica do Axios.

`resources/views/auth/*.blade.ph`p: Telas de Login e Registro.

## Solução de Problemas Comuns

**Erro: "Connection Refused" no Banco de Dados**

Verifique se o container do banco está rodando: `docker ps`.

Confirme se DB_HOST=db no arquivo .env.

**Erro: "Public Key Retrieval is not allowed" (DBeaver/Navicat)**

Nas configurações da conexão do seu cliente SQL, ative a opção **allowPublicKeyRetrieval=true**.

**Erro de Permissão nas pastas storage/bootstrap**

Se tiver erros de escrita de log, rode:

docker-compose exec app chmod -R 777 storage bootstrap/cache
