# SIGEPEI — Sistema de Gerenciamento de Planos Educacionais Individualizados

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13" />
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+" />
  <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3" />
  <img src="https://img.shields.io/badge/Inertia.js-2.0-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia.js" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/Vite-8.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite" />
  <img src="https://img.shields.io/badge/Database-SQLite%20%7C%20MySQL%20%7C%20PostgreSQL-4479A1?style=for-the-badge&logo=sqlite&logoColor=white" alt="Database" />
</p>

---

## Sobre o Sistema

O **SIGEPEI** é uma plataforma institucional para planejamento, acompanhamento, registro e emissão de relatórios de **Planos Educacionais Individualizados (PEI)**.

---

## Funcionalidades Principais

### 1. Gestão de Planos Educacionais Individualizados (PEI)
- **Ficha do Aluno:** Registro do perfil comportamental, rotina, organização, interação social e sugestões de recursos e estratégias de aprendizagem.
- **Ficha do PEI (Identificações e Intervenções):** Mapeamento de habilidades, dificuldades, facilitadores/dificultadores internos e externos, diagnóstico e quadro de propostas de intervenção com responsáveis.
- **Acompanhamento do NAI:** Registro e controle trimestral (1º, 2º e 3º trimestres) das ações, responsáveis, resultados e sugestões do Núcleo de Apoio Inclusivo.
- **Equipes de Apoio Especializado:** Abas dinâmicas dedicadas para serviços de apoio (AEE, Tradução e Interpretação de Libras, Monitoria, Tecnologia Assistiva, Apoio Psicológico, entre outros), com controle de responsáveis e ações por trimestre.
- **Adaptações Curriculares por Disciplina:** Planejamento trimestral estruturado com opções padronizadas pelo NAI:
  - Objetivos acadêmicos e conteúdos curriculares priorizados;
  - Estratégias de ensino e condições de avaliação (checklist padronizado + campos complementares);
  - Prazo/tempo estimado de realização;
  - Avaliação de desempenho/resultados e sugestões para melhor aproveitamento;
  - Instrumentos de avaliação adotados (prova escrita, trabalhos, prova prática, prova oral e outros) com registro de valores e notas.
- **Importação de PEI Anterior:** Ao criar um PEI para um discente que já possui histórico de anos anteriores, é possível importar automaticamente os dados base da ficha anterior.

### 2. Impressão e Exportação de Relatórios (PDF e DOCX)
- **Relatório Completo ou Seletivo do PEI:** Disponível diretamente na tela de visualização do PEI pelo botão **Imprimir Relatório**. Permite escolher o formato (**PDF** ou **DOCX**), selecionar exatamente quais abas devem compor o documento (**Ficha do Aluno**, **Ficha do PEI**, **NAI**, cada **Equipe de Apoio** e cada **Disciplina individualmente**) e filtrar os trimestres incluídos.
- **Relatório Individual de Disciplina pós-preenchimento:** Sempre que um professor conclui e salva o preenchimento de uma disciplina (seja na página do PEI ou no modal de preenchimento rápido do Painel de Controle), o sistema pergunta se deseja gerar e salvar o relatório daquela disciplina em **PDF** ou **DOCX** (apenas do trimestre preenchido ou de todos os trimestres da disciplina).
- Todas as exportações são registradas na trilha de auditoria.

### 3. Painel de Controle
- **Métricas e Indicadores:** Total de PEIs ativos, alunos, turmas e taxa de conclusão do trimestre selecionado.
- **Filtro por Trimestre:** Alternância entre 1º, 2º e 3º trimestres com detecção automática do trimestre vigente pelo calendário escolar.
- **Central de Pendências e Preenchimento Rápido:** Listagem de pendências pessoais e visão institucional do campus, com modal dedicado para o docente resolver pendências de disciplinas diretamente pelo painel.

### 4. Gestão de Discentes
- Cadastro de discentes com matrícula, data de nascimento, foto de perfil, deficiências/necessidades educacionais específicas e histórico de turmas e PEIs.

### 5. Gestão de Turmas, Disciplinas e Deficiências
- Cadastro de turmas por ano letivo e curso, matriz curricular de disciplinas com vinculação de docentes titulares e substitutos, e enturmação de discentes.
- Catálogos institucionais de deficiências/necessidades específicas (com códigos CID) e disciplinas, com controle de ativação/inativação.

### 6. Calendário Escolar
- Gerenciamento de anos letivos com datas de início e fim de cada trimestre e marcação de encerramento do ano letivo, usado para determinar o trimestre vigente no painel.

### 7. Gestão de Usuários e Controle de Acesso
- Perfis de acesso:
  - **Administrador:** Acesso irrestrito a configurações, cadastros, usuários, calendário e auditoria.
  - **NAI:** Gestão de PEIs, alunos, turmas, deficiências, disciplinas, calendário e auditoria.
  - **Professor:** Consulta de PEIs/alunos/turmas, preenchimento de planejamentos e avaliações das disciplinas atribuídas e emissão de relatórios.
  - **Outros (Equipe de Apoio):** Consulta e preenchimento das ações trimestrais nas equipes de apoio em que está vinculado.
- Controle de ativação/desativação de contas e proteção contra descarte acidental de formulários com alterações não salvas.

### 8. Trilha de Auditoria
- Registro das operações do sistema (login, criação, atualização, inativação/reativação e exportação de relatórios), com comparação de valores anteriores e novos, IP, navegador, módulo e usuário responsável.

### 9. Interface e Usabilidade
- Tema claro e escuro com persistência local no navegador e layout responsivo.

---

## Tecnologias Utilizadas

### Backend
- **[PHP 8.3+](https://www.php.net/)**
- **[Laravel 13](https://laravel.com/)**
- **[Laravel Breeze](https://laravel.com/docs/starter-kits#laravel-breeze)**
- **[DomPDF (`barryvdh/laravel-dompdf`)](https://github.com/barryvdh/laravel-dompdf)** — geração de relatórios em PDF
- **[PhpWord (`phpoffice/phpword`)](https://github.com/PHPOffice/PHPWord)** — geração de relatórios em DOCX
- **[SQLite](https://www.sqlite.org/)** (compatível com MySQL / MariaDB / PostgreSQL)

### Frontend
- **[Vue.js 3](https://vuejs.org/)**
- **[Inertia.js 2.0](https://inertiajs.com/)**
- **[Tailwind CSS](https://tailwindcss.com/)**
- **[Vite 8](https://vitejs.dev/)**
- **[Ziggy](https://github.com/tighten/ziggy)**

---

## Pré-requisitos de Sistema

1. **PHP >= 8.3** (com extensões ativas: `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `xmlwriter`, `dom`, `gd`, `zip`, `ctype`, `json`, `curl`, `fileinfo`)
2. **[Composer](https://getcomposer.org/)** (versão 2.x+)
3. **[Node.js](https://nodejs.org/)** (versão 18.x LTS ou superior) e **NPM** (versão 9.x+)
4. **[Git](https://git-scm.com/)**

---

## Como Rodar o Projeto

### 1. Clonar o repositório
```bash
git clone <URL_DO_REPOSITORIO>
cd SIGEPEI
```

### 2. Instalar as dependências do Backend (PHP)
```bash
composer install
```

### 3. Configurar as variáveis de ambiente
```bash
cp .env.example .env
```
*(No Windows via Prompt de Comando: `copy .env.example .env`)*

### 4. Gerar a chave da aplicação
```bash
php artisan key:generate
```

### 5. Configurar o Banco de Dados e Escolher o Seeder
Por padrão, o projeto utiliza **SQLite**. Caso o arquivo ainda não exista, crie-o:
- **Linux / macOS:** `touch database/database.sqlite`
- **Windows (PowerShell):** `New-Item -ItemType File -Path database/database.sqlite -Force`
- **Windows (CMD):** `type nul > database\database.sqlite`

O sistema disponibiliza **dois seeders** distintos:

#### Opção A — Iniciar limpo (somente Administrador)
Cria apenas a conta inicial de administrador (`admin@sigepei.edu.br`, senha `password`), sem alunos, turmas ou PEIs cadastrados. Recomendado para uso real/produção:
```bash
php artisan migrate:fresh --seed
```

#### Opção B — Iniciar com massa completa de testes (`TestDataSeeder`)
Além do administrador, popula anos letivos (2025 e 2026), usuários de todos os perfis, deficiências, disciplinas, turmas, 7 alunos, 6 PEIs em diferentes estágios (completo, parcial, pendente e histórico), equipes de apoio e trilha de auditoria:
```bash
php artisan migrate:fresh --seeder=TestDataSeeder
```
*(Caso o banco já esteja migrado, basta executar `php artisan db:seed --class=TestDataSeeder`.)*

> **Nota:** Para utilizar **MySQL** ou **PostgreSQL**, configure `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` no `.env` antes das migrações.

### 6. Instalar as dependências do Frontend (Node.js)
```bash
npm install
```

### 7. Iniciar os servidores de desenvolvimento
```bash
composer run dev
```
Ou em dois terminais separados:
```bash
# Terminal 1 — Backend
php artisan serve

# Terminal 2 — Frontend
npm run dev
```

### 8. Acessar a aplicação
Abra **[http://localhost:8000](http://localhost:8000)** no navegador.

---

## Atalho de Inicialização Rápida

```bash
composer run setup
php artisan db:seed # ambiente limpo (apenas admin) ou --class=TestDataSeeder para dados de teste
composer run dev
```

---

## Credenciais de Acesso

### Seeder padrão (`DatabaseSeeder`)
| Nome | E-mail | Cargo / Perfil | Senha |
| :--- | :--- | :--- | :--- |
| **Administrador SIGEPEI** | `admin@sigepei.edu.br` | NAI (Administrador) | `password` |

### Contas adicionais do seeder de testes (`TestDataSeeder`)
Todas as contas utilizam a senha padrão **`password`**:

| Nome | E-mail | Cargo / Perfil | Observação |
| :--- | :--- | :--- | :--- |
| **Beatriz Mendonça** | `nai.beatriz@sigepei.edu.br` | NAI | Pedagoga |
| **Rodrigo Albuquerque** | `nai.rodrigo@sigepei.edu.br` | NAI | Psicólogo Escolar |
| **Prof. Carlos Eduardo** | `prof.carlos@sigepei.edu.br` | Professor | Matemática |
| **Profª Mariana Souza** | `prof.mariana@sigepei.edu.br` | Professor | Língua Portuguesa |
| **Prof. Ricardo Santos** | `prof.ricardo@sigepei.edu.br` | Professor | Informática |
| **Profª Juliana Costa** | `prof.juliana@sigepei.edu.br` | Professor | Física e Biologia |
| **Prof. Fernando Alves** | `prof.fernando@sigepei.edu.br` | Professor | História e Gestão |
| **Profª Patrícia Lima** | `prof.patricia@sigepei.edu.br` | Professor | Docente substituta |
| **Larissa Viana** | `apoio.larissa@sigepei.edu.br` | Outros (Apoio) | Intérprete de Libras |
| **Gabriel Toledo** | `apoio.gabriel@sigepei.edu.br` | Outros (Apoio) | Monitor de Tecnologia Assistiva |
| **Prof. Marcelo Dias** | `inativo@sigepei.edu.br` | Professor | Conta inativa (teste de bloqueio) |

---

## Testes Automatizados

Execute toda a suíte de testes com:
```bash
php artisan test
```

---

## Estrutura do Projeto

```text
SIGEPEI/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Controladores (Pei, PeiReport, Aluno, Turma, Calendario, Dashboard, Auditoria, etc.)
│   │   └── Middleware/        # Middlewares de autenticação e controle de acesso
│   ├── Models/                # Modelos Eloquent e relacionamentos
│   └── Services/              # Serviços (AuditService, PeiReportService)
├── database/
│   ├── migrations/            # Esquema do banco de dados
│   └── seeders/               # DatabaseSeeder (apenas admin) e TestDataSeeder (base de testes completa)
├── resources/
│   ├── js/
│   │   ├── Components/        # Componentes Vue (PendenciaProfessorModal, RelatorioPeiModal, ImprimirDisciplinaPromptModal, etc.)
│   │   ├── Layouts/           # Layouts (AuthenticatedLayout, GuestLayout)
│   │   ├── Pages/             # Páginas Inertia (Peis, Alunos, Turmas, Calendario, Dashboard, Auditoria, etc.)
│   │   ├── composables/       # Composables Vue (useUnsavedChanges)
│   │   └── constants/         # Opções padronizadas do NAI (peiProfessorOptions)
│   └── views/
│       ├── app.blade.php      # Template base HTML
│       └── reports/           # Template de relatório PDF (pei.blade.php)
├── routes/
│   ├── web.php                # Rotas da aplicação web e relatórios
│   └── auth.php               # Rotas de autenticação
├── public/                    # Arquivos públicos e assets compilados
└── tests/                     # Testes automatizados (Feature e Unit)
```
