# Femaqua API
API RESTful para gestão de ferramentas.
## Tecnologias
- Laravel 12+
- MySQL
- JWT Auth (Sistema de login, Autenticação/Autorização)
- Swagger (Testes facilitados)
- Docker & Docker Compose  
- Feature Tests e Unit Tests
- Migrations, factories e seeders para facilitar testes e popular banco

## Instalação e Execução com Docker

### Pré-requisitos

- Docker e Docker Compose
- Git

### ⚠️ Avisos importantes
- Container MySQL vai executar na porta 3306
- Container da aplicação vai executar na porta 3000
- O Docker precisa estar em execução
- Endpoint `api/tools/index` está sem autenticação propositalmente para testes de paginação e consulta por filtro

### 1. Clonar o repositório
```bash
git clone https://github.com/albertofelipe/femaqua-api.git
cd femaqua-api
```
### 2. Configurar permissões
```bash
chmod +x docker/entrypoint.sh
```
> **Importante:** Habilita a execução do script de inicialização automática.

### 3. Ambiente
```bash
cp .env.example .env
```

### 4. Baixar imagens Docker
```bash
docker pull php:8.3-cli
docker pull composer:2.8
```
> **Otimização:** Evita rebuilds desnecessários.

### 5. Iniciar containers
```bash
docker compose up
```
> **Processo automático:**
> 1. Cria container da aplicação
> 2. MySQL inicia em paralelo
> 3. Instala dependências via Composer
> 4. Gera chave da aplicação
> 5. Gera chave JWT da aplicação
> 6. Executa migrations + seeds
> 7. Gera documentação
> 8. Roda todos os testes
> 9. Inicia servidor na porta 3000

## Comandos Úteis Docker

| Comando                     | Descrição                                  |
|-----------------------------|--------------------------------------------|
| `docker compose down`       | Para e remove todos os containers          |
| `docker compose exec app bash` | Acessa o terminal do container da aplicação |
| `docker compose logs -f app`  | Monitora os logs em tempo real             |

## Acesso à Aplicação

## Endereços 

| Descrição          | URL                                  | Porta |
|--------------------|--------------------------------------|-------|
| **Aplicação**      | http://localhost:3000             | 3000  |
| **Documentação**   | http://localhost:3000/api/documentation       | 3000  |

## Endpoints da API

### 🔓 Rotas Públicas

| Método | Endpoint          | Controller          | Descrição                      |
|--------|-------------------|---------------------|--------------------------------|
| POST   | `/register`       | `AuthController`    | Registro de novo usuário e obtenção de token JWT     |
| POST   | `/login`          | `AuthController`    | Login e obtenção de token JWT  |
| GET    | `/tools`          | `ToolController`    | Listar todas as ferramentas    |

### 🔐 Rotas Protegidas (Requer JWT)
*Requirem header: `Authorization: Bearer {token}`*

| Método | Endpoint            | Controller          | Descrição                      |
|--------|---------------------|---------------------|--------------------------------|
| GET    | `/me`               | `AuthController`    | Dados do usuário autenticado   |
| POST   | `/logout`           | `AuthController`    | Invalida o token atual        |
| POST   | `/tools`            | `ToolController`    | Criar nova ferramenta          |
| POST   | `/tools/bulk`       | `ToolController`    | Criação em massa de ferramentas|
| GET    | `/tools/{id}`       | `ToolController`    | Detalhes de uma ferramenta (somente o usuário que criou pode ter acesso aos detalhes de uma ferramenta)     |
| PUT    | `/tools/{id}`       | `ToolController`    | Atualizar ferramenta (somente o usuário que criou pode editar uma ferramenta)           |
| PATCH      | `/tools/{id}`       | `ToolController`    | Atualizar ferramenta parcialmente (somente o usuário que criou pode editar uma ferramenta)         |
| DELETE | `/tools/{id}`       | `ToolController`    | Remover ferramenta (somente o usuário que criou pode ter deletar uma ferramenta)            |

---
# Instalação e Execução local

### 📋 Pré-requisitos
- PHP 8.2.12+
- Laravel 12.8.1+
- Composer 2.8.6+
- MySQL 8.0+
- Git

## Passo a Passo

### 1. Clone o repositório
   ```bash
   git clone https://github.com/albertofelipe/femaqua-api.git
   cd femaqua-api
   ```
### 2. Instale dependências
```bash
composer install
```

### 3. Configure o ambiente

```bash
cp .env.example .env
```

Edite o `.env` com:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=femaqua
DB_USERNAME=root
DB_PASSWORD=sua_senha_mysql
```
### 3. Gere chave da aplicação
```bash
php artisan key:generate
```

### 4. Gere a chave JWT 
```bash
php artisan jwt:secret --force
```

### 5. Execute as migrations
```bash
php artisan migrate --seed
```

### 6. Inicie o servidor
```bash
php artisan serve --port=3000
```

## Testes e Documentação

### 1. Execute os testes
```bash
php artisan test
```

### 2. Gere a documentação Swagger
```bash
php artisan l5-swagger:generate
```
## Acesso à Aplicação

## Endereços 

| Descrição          | URL                                  | Porta |
|--------------------|--------------------------------------|-------|
| **Aplicação**      | http://localhost:3000             | 3000  |
| **Documentação**   | http://localhost:3000/api/documentation       | 3000  |
