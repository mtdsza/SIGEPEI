# SIGEPEI — Sistema de Gerenciamento de Planos Educacionais Individualizados

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12" />
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+" />
  <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3" />
  <img src="https://img.shields.io/badge/Inertia.js-2.0-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia.js" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/Vite-6.x%2F8.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite" />
  <img src="https://img.shields.io/badge/Database-SQLite%20%7C%20MySQL%20%7C%20PostgreSQL-4479A1?style=for-the-badge&logo=sqlite&logoColor=white" alt="Database" />
</p>

---

## Sobre o Sistema

O **SIGEPEI** é uma plataforma institucional para planejamento, acompanhamento e registro de **Planos Educacionais Individualizados (PEI)**. 

---

## Funcionalidades Principais

### 1. Gestão de Planos Educacionais Individualizados (PEI)
- **Ficha de Identificação do Discente:** Registro detalhado de histórico acadêmico, laudos médicos, diagnósticos, terapias externas, rotina familiar, medicações e características comportamentais.
- **Ficha do PEI (Intervenções & Habilidades):** Mapeamento de habilidades consolidadas, dificuldades, barreiras de aprendizagem e estratégias de intervenção pedagógica recomendadas.
- **Acompanhamento do NAI:** Registro e controle trimestral das ações, atendimentos e orientações desenvolvidas pelo Núcleo de Apoio Inclusivo.
- **Equipes e Serviços de Apoio Especializado:** Módulo dedicado para serviços de apoio (Atendimento Educacional Especializado - AEE, Tradução e Interpretação de Libras, Monitoria, Tecnologia Assistiva e Cuidadores), com registro de ações e responsáveis por trimestre.
- **Adaptações Curriculares por Disciplina:** Planejamento trimestral estruturado para cada matéria da matriz curricular:
  - Objetivos de aprendizagem e habilidades prioritárias;
  - Metodologias diferenciadas e recursos didáticos acessíveis;
  - Critérios de avaliação flexibilizados;
  - Registro de notas/conceitos e parecer descritivo de desempenho por trimestre (1º, 2º e 3º Trimestres).
- **Emissão e Visualização Padronizada:** Interface com navegação por abas dinâmicas e visualização completa para impressão institucional.

### 2. Painel de Controle
- **Métricas e Indicadores Globais:** Total de PEIs vigentes, estudantes atendidos e distribuição por deficiência e por curso/turma.
- **Filtro por Trimestre:** Alternância entre 1º, 2º e 3º Trimestres com detecção automática do trimestre vigente pelo calendário.
- **Central de Pendências por Usuário:** Identificação automática de pendências pedagógicas atribuídas ao usuário autenticado (planejamentos não cadastrados, avaliações trimestrais em aberto, ações pendentes do NAI ou equipe de apoio).

### 3. Gestão de Discentes
- Cadastro completo de discentes com número de matrícula, data de nascimento e cálculo automático de idade.
- Vinculação com uma ou múltiplas deficiências/necessidades educacionais específicas.
- Histórico de turmas e anos letivos vinculados ao aluno.
- Histórico e acesso direto aos PEIs elaborados para o discente.

### 4. Gestão de Turmas e Matrizes Curriculares
- Cadastro de turmas por ano letivo, curso, modalidade e turno.
- Associação de matriz curricular de disciplinas por turma com atribuição dos docentes responsáveis.
- Enturmação e associação de discentes.

### 5. Cadastros Institucionais
- **Deficiências e Necessidades Específicas:** Cadastro categorizado de deficiências, transtornos globais do desenvolvimento (TEA), TDAH, deficiência visual, auditiva, física, múltipla e altas habilidades/superdotação com códigos CID.
- **Disciplinas:** Gerenciamento da matriz de disciplinas institucionais.

### 6. Gestão de Usuários e Controle de Acesso
- Suporte a múltiplos perfis de acesso:
  - **Administrador:** Acesso irrestrito a configurações, cadastros, usuários e auditoria.
  - **NAI:** Gestão integral de PEIs, alunos, apoios e relatórios.
  - **Professores:** Preenchimento de planejamentos e avaliações das suas disciplinas atribuídas.
  - **Apoio / Outros:** Registro de ações específicas de apoio especializado.
- Controle de ativação/desativação de contas de usuário.

### 7. Trilha de Auditoria Completa
- Registro detalhado de todas as operações sensíveis no sistema (criação, edição e exclusão de PEIs, alunos, turmas, notas, planejamentos e logins).
- Rastreamento de Modificações: Visualização exata do valor anterior e do novo valor para cada atributo modificado.
- Metadados completos: data/hora, endereço IP, navegador e usuário responsável.
- Filtros por intervalo de datas, tipo de ação, módulo e usuário.

### 8. Calendário e Usabilidade
- **Calendário Acadêmico:** Visualização de datas comemorativas da inclusão e prazos de entrega trimestrais.
- **Tema Claro e Escuro :** Alternância instantânea de tema com persistência local no navegador.
- **Design Responsivo:** Interface otimizada para computadores, tablets e dispositivos móveis.

---

## Tecnologias Utilizadas

### Backend
- **[PHP 8.3+](https://www.php.net/)**
- **[Laravel 12 / 13](https://laravel.com/)**
- **[Laravel Breeze](https://laravel.com/docs/starter-kits#laravel-breeze)**
- **[SQLite](https://www.sqlite.org/)** (compatível com MySQL / MariaDB / PostgreSQL).

### Frontend
- **[Vue.js 3](https://vuejs.org/)**
- **[Inertia.js 2.0](https://inertiajs.com/)**
- **[Tailwind CSS](https://tailwindcss.com/)**
- **[Vite](https://vitejs.dev/)**
- **[Ziggy](https://github.com/tighten/ziggy)**

---

## Pré-requisitos de Sistema

Antes de iniciar, certifique-se de ter instalado em seu computador:

1. **PHP >= 8.3** (com extensões ativas: `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`, `fileinfo`)
2. **[Composer](https://getcomposer.org/)** (versão 2.x+)
3. **[Node.js](https://nodejs.org/)** (versão 18.x LTS ou superior) e **NPM** (versão 9.x+)
4. **[Git](https://git-scm.com/)**

---

## Como Rodar o Projeto

Siga o passo a passo abaixo para configurar e executar a aplicação localmente:

### 1. Clonar o repositório
```bash
git clone <URL_DO_REPOSITORIO>
cd "SIGEPEI"
```

### 2. Instalar as dependências do Backend (PHP)
```bash
composer install
```

### 3. Configurar as variáveis de ambiente
Copie o arquivo de exemplo `.env.example` para criar o seu `.env`:

```bash
cp .env.example .env
```

*(No Windows via Prompt de Comando: `copy .env.example .env`)*

### 4. Gerar a chave da aplicação
```bash
php artisan key:generate
```

### 5. Configurar o Banco de Dados e Rodar as Migrações
Por padrão, o projeto utiliza **SQLite** para facilitar o desenvolvimento local.

Crie o arquivo do banco de dados (caso não exista):
- **Linux / macOS:**
  ```bash
  touch database/database.sqlite
  ```
- **Windows (PowerShell):**
  ```powershell
  New-Item -ItemType File -Path database/database.sqlite -Force
  ```
- **Windows (CMD):**
  ```cmd
  type nul > database\database.sqlite
  ```

Execute as migrações estruturais e popule o banco com a massa de dados de teste:
```bash
php artisan migrate:fresh --seed
```

> **Nota:** Caso deseje utilizar **MySQL** ou **PostgreSQL**, altere as credenciais `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` no arquivo `.env` antes de executar o comando de migração.

### 6. Instalar as dependências do Frontend (Node.js)
```bash
npm install
```

### 7. Iniciar os servidores de desenvolvimento

Você pode iniciar o servidor backend e o bundler frontend simultaneamente com o script integrado:

```bash
composer run dev
```

Ou, se preferir, execute em dois terminais separados:

**Terminal 1 — Backend (Laravel):**
```bash
php artisan serve
```

**Terminal 2 — Frontend (Vite):**
```bash
npm run dev
```

### 8. Acessar a aplicação
Abra o navegador e acesse:
**[http://localhost:8000](http://localhost:8000)** (ou o endereço indicado pelo `php artisan serve`).

---

## Atalho de Inicialização Rápida

O projeto conta com um script de setup automatizado no `composer.json` que realiza a instalação de dependências, cópia de `.env`, geração de chave, migração do banco e build dos assets:

```bash
composer run setup
composer run dev
```

---

## Credenciais de Teste

O banco de dados de demonstração gerado pelo `php artisan migrate:fresh --seed` já inclui contas para todos os perfis de usuário do sistema. Todas as contas possuem a senha padrão: **`password`**

| Nome | E-mail | Cargo / Perfil | Tipo de Acesso |
| :--- | :--- | :--- | :--- |
| **Profª Helena Guimarães** | `admin@sigepei.edu.br` | Coordenação / NAI | Administrador |
| **Beatriz Mendonça** | `nai.beatriz@sigepei.edu.br` | Pedagoga NAI | Equipe NAI |
| **Rodrigo Albuquerque** | `nai.rodrigo@sigepei.edu.br` | Psicólogo Escolar NAI | Equipe NAI |
| **Prof. Carlos Eduardo** | `prof.carlos@sigepei.edu.br` | Professor (Matemática) | Docente |
| **Profª Mariana Souza** | `prof.mariana@sigepei.edu.br` | Professora (Português) | Docente |
| **Prof. Ricardo Santos** | `prof.ricardo@sigepei.edu.br` | Professor (Informática) | Docente |
| **Profª Juliana Costa** | `prof.juliana@sigepei.edu.br` | Professora (Física) | Docente |
| **Larissa Viana** | `apoio.larissa@sigepei.edu.br` | Tradutora/Intérprete Libras | Equipe de Apoio |
| **Gabriel Toledo** | `apoio.gabriel@sigepei.edu.br` | Monitor Tecnologia Assistiva | Equipe de Apoio |

> **Senha padrão para todas as contas:** `password`

---

## Estrutura do Projeto

Abaixo estão destacados os principais diretórios do SIGEPEI:

```text
SIGEPEI/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Controladores da aplicação (Pei, Aluno, Turma, Dashboard, Auditoria, etc.)
│   │   └── Middleware/        # Middlewares de autenticação e proteção de rotas
│   ├── Models/                # Modelos Eloquent e relacionamentos do banco de dados
│   └── Services/              # Serviços auxiliares (ex: AuditService para rastreamento de diffs)
├── database/
│   ├── migrations/            # Definições do esquema do banco de dados
│   └── seeders/               # Massa de dados de teste e usuários iniciais
├── resources/
│   ├── js/
│   │   ├── Components/        # Componentes Vue reutilizáveis (modais, tabelas, inputs, botões)
│   │   ├── Layouts/           # Layouts principais (AuthenticatedLayout, GuestLayout)
│   │   ├── Pages/             # Telas e visualizações do sistema (Peis, Alunos, Turmas, Dashboard, etc.)
│   │   └── app.js             # Entrada do Vue 3 e inicialização do Inertia.js
│   └── views/                 # Template base HTML (app.blade.php)
├── routes/
│   ├── web.php                # Rotas da aplicação web e do sistema PEI
│   └── auth.php               # Rotas de autenticação (login, logout, redefinição de senha)
├── public/                    # Arquivos públicos e assets compilados
└── tests/                     # Testes automatizados da aplicação
```