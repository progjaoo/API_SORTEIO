# API Sorteios Maravilha - Rádio 89 FM Maravilha

Esta é a API para o sistema de sorteios da Rádio 89 FM Maravilha. O sistema permite o cadastro de participantes, criação de sorteios, realização de inscrições e a execução dos sorteios com notificações por e-mail e push.

## 🚀 Tecnologias Utilizadas

- **PHP 8.2+**
- **MySQL**
- **Composer** (Gerenciamento de dependências)
- **PHPMailer** (Envio de e-mails)
- **OpenSpout** (Geração de arquivos Excel/CSV)
- **FPDF** (Geração de PDFs)
- **Firebase / Expo SDK** (Notificações Push)

## 📋 Pré-requisitos

- Servidor Web (Apache/Nginx)
- PHP 8.2 ou superior
- MySQL
- Composer instalado

## 🔧 Instalação e Configuração

1. **Clone o repositório:**
   ```bash
   git clone <url-do-repositorio>
   ```

2. **Instale as dependências via Composer:**
   ```bash
   composer install
   ```

3. **Configuração do Banco de Dados:**
   - O arquivo de conexão encontra-se em `config/conexao.php`.
   - Utilize o script `SCRIPT.SQL` na raiz do projeto para criar as tabelas necessárias.

4. **Configuração de E-mail:**
   - Configure os dados do servidor SMTP em `config/mail.php`.

## 📖 Referência da API

A API utiliza majoritariamente JSON para comunicação, exceto em endpoints que envolvem upload de arquivos (multipart/form-data).

### 🔐 Usuários

Endpoints para gestão de administradores e operadores do sistema.

- **`POST /usuarios/login.php`**
  - Autentica um usuário.
  - **Body (JSON):** `{"email": "...", "senha": "..."}`
  - **Retorno:** Dados do usuário (sem a senha).

- **`POST /usuarios/cadastrar.php`**
  - Cadastra um novo usuário.
  - **Body (JSON):** `{"nome": "...", "email": "...", "senha": "...", "perfil": "ADMIN|OPERADOR"}`

- **`GET /usuarios/listar.php`**
  - Lista todos os usuários cadastrados.

---

### 👥 Participantes

Gestão dos ouvintes/participantes que se inscrevem nos sorteios.

- **`POST /participantes/cadastrar.php`**
  - Cadastra um novo participante. Realiza validação de CPF.
  - **Body (JSON):** `{"nome_completo": "...", "email": "...", "telefone": "...", "cpf": "...", "cep": "...", "logradouro": "...", "numero": "...", "bairro": "...", "cidade": "...", "estado": "..."}`

- **`GET /participantes/listar.php`**
  - Lista todos os participantes.

- **`GET /participantes/buscar.php?id={id}`**
  - Busca detalhes de um participante específico.

- **`POST /participantes/atualizar.php`**
  - Atualiza os dados de um participante.
  - **Body (JSON):** `{"id": "...", "nome_completo": "...", "email": "...", "telefone": "...", "cpf": "...", "endereco": "..."}`

- **`GET /participantes/deletar.php?id={id}`**
  - Remove um participante do sistema.

- **`GET /participantes/consultar_por_cpf.php?cpf={cpf}`**
  - Consulta as inscrições vinculadas a um CPF.

- **`GET /participantes/sorteios_participante.php?id={id}`**
  - Lista os sorteios em que um participante está inscrito.

- **`GET /participantes/participantes_excel.php?sorteio_id={id}`**
  - Gera um arquivo CSV com todos os participantes de um sorteio específico.

---

### 🎡 Sorteios

Gerenciamento dos sorteios da rádio.

- **`POST /sorteios/criar.php`**
  - Cria um novo sorteio. Exige autenticação por sessão.
  - **Body (Form-Data):** `nome_sorteio`, `descricao`, `data_sorteio`, `data_final_cadastro`, `imagem` (arquivo).

- **`GET /sorteios/listar.php`**
  - Lista sorteios ativos e futuros.

- **`GET /sorteios/buscar.php?id={id}`**
  - Busca detalhes de um sorteio.

- **`POST /sorteios/atualizar.php`**
  - Atualiza um sorteio existente.
  - **Body (Form-Data):** `id`, `nome_sorteio`, `descricao`, `data_sorteio`, `data_final_cadastro`, `estado`, `imagem` (opcional).

- **`GET /sorteios/deletar.php?id={id}`**
  - Remove um sorteio.

- **`GET /sorteios/aberto.php`**
  - Retorna o sorteio atualmente aberto para inscrições.

- **`GET /sorteios/finalizados_publico.php`**
  - Lista sorteios finalizados nos últimos 60 dias para exibição pública.

- **`POST /sorteios/finalizar.php`**
  - Encerra um sorteio manualmente.
  - **Body (JSON):** `{"id": "..."}`

- **`GET /sorteios/historico.php`**
  - Lista o histórico completo de sorteios finalizados com seus respectivos vencedores.

- **`GET /sorteios/participantes_sorteio.php?id={id}`**
  - Lista os participantes inscritos em um sorteio.

- **`POST /sorteios/sortear.php`**
  - Realiza o sorteio aleatório entre os inscritos, define o vencedor e envia e-mail de notificação.
  - **Body (JSON):** `{"sorteio_id": "..."}`

---

### 📝 Inscrições

- **`POST /inscricoes/inscrever.php`**
  - Realiza a inscrição de um participante em um sorteio. Gera um código único de sorteio.
  - **Body (JSON):** `{"participante_id": "...", "sorteio_id": "..."}`

- **`GET /inscricoes/listar_por_sorteio.php?sorteio_id={id}`**
  - Lista detalhadamente os inscritos em um sorteio.

- **`GET /inscricoes/participantes_pdf.php?sorteio_id={id}`**
  - Gera um PDF com a lista de inscritos para um sorteio.

---

### 🔔 Notificações Push

- **`POST /push/register.php`**
  - Registra o token de um dispositivo (Expo Push Token) para receber notificações.
  - **Body (JSON):** `{"token": "...", "plataforma": "ANDROID|IOS|WEB"}`

- **`POST /push/enviar.php`**
  - Dispara uma notificação push para todos os dispositivos ativos.
  - **Body (JSON):** `{"mensagem": "...", "sorteio_id": "..."}`

## 🗄️ Estrutura de Pastas

- `/config`: Arquivos de configuração (BD, CORS, Mail).
- `/usuarios`: Endpoints de gestão de usuários.
- `/participantes`: Endpoints de gestão de participantes.
- `/sorteios`: Endpoints de gestão de sorteios.
- `/inscricoes`: Endpoints de inscrições.
- `/push`: Endpoints e serviços de notificação push.
- `/libs`: Bibliotecas externas (FPDF).
- `/emails`: templates de e-mail.
- `/services`: Serviços auxiliares (MailService).

## 📄 Licença

Este projeto é de uso restrito da Rádio 89 FM Maravilha.
