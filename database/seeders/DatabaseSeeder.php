<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Deficiencia;
use App\Models\Disciplina;
use App\Models\Pei;
use App\Models\PeiApoio;
use App\Models\PeiApoioAtividade;
use App\Models\PeiApoioResponsavel;
use App\Models\PeiDisciplina;
use App\Models\PeiDisciplinaAvaliacao;
use App\Models\PeiDisciplinaPlanejamento;
use App\Models\PeiIdentificacao;
use App\Models\PeiIntervencao;
use App\Models\PeiNaiAtividade;
use App\Models\PeiResponsavel;
use App\Models\Turma;
use App\Models\TurmaDisciplina;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * popula o banco de dados com registros iniciais de demonstração e testes do sistema.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // 1. usuários do sistema com perfis de administrador, nai, docentes e equipe de apoio
        $admin = User::updateOrCreate(
            ['email' => 'admin@sigepei.edu.br'],
            [
                'name' => 'Profª Helena Guimarães (Coordenação / Admin)',
                'password' => $password,
                'cargo' => 'NAI',
                'is_admin' => true,
                'is_active' => true,
            ]
        );

        $naiBeatriz = User::updateOrCreate(
            ['email' => 'nai.beatriz@sigepei.edu.br'],
            [
                'name' => 'Beatriz Mendonça (Pedagoga NAI)',
                'password' => $password,
                'cargo' => 'NAI',
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        $naiRodrigo = User::updateOrCreate(
            ['email' => 'nai.rodrigo@sigepei.edu.br'],
            [
                'name' => 'Rodrigo Albuquerque (Psicólogo Escolar NAI)',
                'password' => $password,
                'cargo' => 'NAI',
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        $profCarlos = User::updateOrCreate(
            ['email' => 'prof.carlos@sigepei.edu.br'],
            [
                'name' => 'Prof. Carlos Eduardo (Matemática)',
                'password' => $password,
                'cargo' => 'Professor',
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        $profMariana = User::updateOrCreate(
            ['email' => 'prof.mariana@sigepei.edu.br'],
            [
                'name' => 'Profª Mariana Souza (Língua Portuguesa)',
                'password' => $password,
                'cargo' => 'Professor',
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        $profRicardo = User::updateOrCreate(
            ['email' => 'prof.ricardo@sigepei.edu.br'],
            [
                'name' => 'Prof. Ricardo Santos (Programação e Banco de Dados)',
                'password' => $password,
                'cargo' => 'Professor',
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        $profJuliana = User::updateOrCreate(
            ['email' => 'prof.juliana@sigepei.edu.br'],
            [
                'name' => 'Profª Juliana Costa (Física e Ciências)',
                'password' => $password,
                'cargo' => 'Professor',
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        $profFernando = User::updateOrCreate(
            ['email' => 'prof.fernando@sigepei.edu.br'],
            [
                'name' => 'Prof. Fernando Alves (História e Humanidades)',
                'password' => $password,
                'cargo' => 'Professor',
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        $apoioLarissa = User::updateOrCreate(
            ['email' => 'apoio.larissa@sigepei.edu.br'],
            [
                'name' => 'Larissa Viana (Tradutora/Intérprete de Libras)',
                'password' => $password,
                'cargo' => 'Outros',
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        $apoioGabriel = User::updateOrCreate(
            ['email' => 'apoio.gabriel@sigepei.edu.br'],
            [
                'name' => 'Gabriel Toledo (Monitor de Tecnologia Assistiva)',
                'password' => $password,
                'cargo' => 'Outros',
                'is_admin' => false,
                'is_active' => true,
            ]
        );

        // usuário inativo para validação de bloqueio de acesso
        User::updateOrCreate(
            ['email' => 'inativo@sigepei.edu.br'],
            [
                'name' => 'Prof. Marcelo Dias (Inativo)',
                'password' => $password,
                'cargo' => 'Professor',
                'is_admin' => false,
                'is_active' => false,
            ]
        );

        // 2. deficiências e necessidades educacionais específicas
        $defTea = Deficiencia::updateOrCreate(
            ['codigo' => 'F84.0'],
            ['nome' => 'Transtorno do Espectro Autista (TEA)', 'is_active' => true]
        );

        $defBaixaVisao = Deficiencia::updateOrCreate(
            ['codigo' => 'H54.0'],
            ['nome' => 'Baixa Visão / Cegueira', 'is_active' => true]
        );

        $defAuditiva = Deficiencia::updateOrCreate(
            ['codigo' => 'H90.0'],
            ['nome' => 'Deficiência Auditiva / Surdez', 'is_active' => true]
        );

        $defTdah = Deficiencia::updateOrCreate(
            ['codigo' => 'F90.0'],
            ['nome' => 'Transtorno do Déficit de Atenção e Hiperatividade (TDAH)', 'is_active' => true]
        );

        $defFisica = Deficiencia::updateOrCreate(
            ['codigo' => 'G80.0'],
            ['nome' => 'Paralisia Cerebral / Deficiência Física Motora', 'is_active' => true]
        );

        $defDislexia = Deficiencia::updateOrCreate(
            ['codigo' => 'F81.0'],
            ['nome' => 'Transtorno Específico de Leitura / Dislexia', 'is_active' => true]
        );

        $defAltasHabilidades = Deficiencia::updateOrCreate(
            ['codigo' => 'AH/SD'],
            ['nome' => 'Altas Habilidades / Superdotação', 'is_active' => true]
        );

        // 3. disciplinas da matriz curricular
        $discMat = Disciplina::updateOrCreate(
            ['codigo' => 'MAT-101'],
            ['nome' => 'Matemática e Raciocínio Lógico', 'is_active' => true]
        );

        $discPort = Disciplina::updateOrCreate(
            ['codigo' => 'POR-101'],
            ['nome' => 'Língua Portuguesa e Literatura', 'is_active' => true]
        );

        $discProgWeb = Disciplina::updateOrCreate(
            ['codigo' => 'INF-201'],
            ['nome' => 'Programação Web e Interfaces', 'is_active' => true]
        );

        $discBancoDados = Disciplina::updateOrCreate(
            ['codigo' => 'INF-202'],
            ['nome' => 'Modelagem e Banco de Dados', 'is_active' => true]
        );

        $discFisica = Disciplina::updateOrCreate(
            ['codigo' => 'FIS-101'],
            ['nome' => 'Física Aplicada', 'is_active' => true]
        );

        $discHistoria = Disciplina::updateOrCreate(
            ['codigo' => 'HIS-101'],
            ['nome' => 'História Geral e do Brasil', 'is_active' => true]
        );

        // 4. turmas e vinculação de disciplinas e docentes
        // turma de informática 1º ano (ano letivo 2026)
        $turmaInfo1 = Turma::updateOrCreate(
            ['nome_curso' => 'Técnico em Informática Integrado - 1º Ano A', 'ano_letivo' => 2026],
            ['is_active' => true]
        );
        $tdInfo1Mat = TurmaDisciplina::updateOrCreate(['turma_id' => $turmaInfo1->id, 'disciplina_id' => $discMat->id]);
        $tdInfo1Mat->professores()->sync([$profCarlos->id => ['is_substituto' => false]]);

        $tdInfo1Port = TurmaDisciplina::updateOrCreate(['turma_id' => $turmaInfo1->id, 'disciplina_id' => $discPort->id]);
        $tdInfo1Port->professores()->sync([$profMariana->id => ['is_substituto' => false]]);

        $tdInfo1Prog = TurmaDisciplina::updateOrCreate(['turma_id' => $turmaInfo1->id, 'disciplina_id' => $discProgWeb->id]);
        $tdInfo1Prog->professores()->sync([$profRicardo->id => ['is_substituto' => false], $profCarlos->id => ['is_substituto' => true]]);

        // turma de informática 2º ano (ano letivo 2026)
        $turmaInfo2 = Turma::updateOrCreate(
            ['nome_curso' => 'Técnico em Informática Integrado - 2º Ano B', 'ano_letivo' => 2026],
            ['is_active' => true]
        );
        $tdInfo2BD = TurmaDisciplina::updateOrCreate(['turma_id' => $turmaInfo2->id, 'disciplina_id' => $discBancoDados->id]);
        $tdInfo2BD->professores()->sync([$profRicardo->id => ['is_substituto' => false]]);

        $tdInfo2Fis = TurmaDisciplina::updateOrCreate(['turma_id' => $turmaInfo2->id, 'disciplina_id' => $discFisica->id]);
        $tdInfo2Fis->professores()->sync([$profJuliana->id => ['is_substituto' => false]]);

        // turma de administração 1º ano (ano letivo 2026)
        $turmaAdm1 = Turma::updateOrCreate(
            ['nome_curso' => 'Técnico em Administração Integrado - 1º Ano', 'ano_letivo' => 2026],
            ['is_active' => true]
        );
        $tdAdm1Mat = TurmaDisciplina::updateOrCreate(['turma_id' => $turmaAdm1->id, 'disciplina_id' => $discMat->id]);
        $tdAdm1Mat->professores()->sync([$profCarlos->id => ['is_substituto' => false]]);

        $tdAdm1Port = TurmaDisciplina::updateOrCreate(['turma_id' => $turmaAdm1->id, 'disciplina_id' => $discPort->id]);
        $tdAdm1Port->professores()->sync([$profMariana->id => ['is_substituto' => false]]);

        $tdAdm1Hist = TurmaDisciplina::updateOrCreate(['turma_id' => $turmaAdm1->id, 'disciplina_id' => $discHistoria->id]);
        $tdAdm1Hist->professores()->sync([$profFernando->id => ['is_substituto' => false]]);

        // turma de edificações 1º ano (ano letivo 2026)
        $turmaEdif1 = Turma::updateOrCreate(
            ['nome_curso' => 'Técnico em Edificações Integrado - 1º Ano', 'ano_letivo' => 2026],
            ['is_active' => true]
        );
        $tdEdif1Fis = TurmaDisciplina::updateOrCreate(['turma_id' => $turmaEdif1->id, 'disciplina_id' => $discFisica->id]);
        $tdEdif1Fis->professores()->sync([$profJuliana->id => ['is_substituto' => false]]);

        // turma histórica de informática 3º ano (ano letivo 2025)
        $turmaInfo2025 = Turma::updateOrCreate(
            ['nome_curso' => 'Técnico em Informática Integrado - 3º Ano (2025)', 'ano_letivo' => 2025],
            ['is_active' => true]
        );
        $tdInfo2025Mat = TurmaDisciplina::updateOrCreate(['turma_id' => $turmaInfo2025->id, 'disciplina_id' => $discMat->id]);
        $tdInfo2025Mat->professores()->sync([$profCarlos->id => ['is_substituto' => false]]);

        $tdInfo2025Port = TurmaDisciplina::updateOrCreate(['turma_id' => $turmaInfo2025->id, 'disciplina_id' => $discPort->id]);
        $tdInfo2025Port->professores()->sync([$profMariana->id => ['is_substituto' => false]]);

        $tdInfo2025Prog = TurmaDisciplina::updateOrCreate(['turma_id' => $turmaInfo2025->id, 'disciplina_id' => $discProgWeb->id]);
        $tdInfo2025Prog->professores()->sync([$profRicardo->id => ['is_substituto' => false]]);

        // 5. discentes e elaboração de planos educacionais individualizados (peis)
        // aluno 1: discente com tea e baixa visão (2026)
        $alunoLucas = Aluno::updateOrCreate(
            ['matricula' => '2026100123'],
            [
                'nome' => 'Lucas Ferreira da Silva',
                'data_nascimento' => '2009-04-15',
                'is_active' => true,
            ]
        );
        $alunoLucas->deficiencias()->sync([$defTea->id, $defBaixaVisao->id]);
        $alunoLucas->turmas()->sync([
            $turmaInfo1->id => ['ano_letivo' => 2026, 'is_atual' => true],
        ]);

        $peiLucas = Pei::updateOrCreate(
            ['codigo' => 'PEI-2026-001'],
            [
                'aluno_id' => $alunoLucas->id,
                'turma_id' => $turmaInfo1->id,
                'ano_letivo' => 2026,
                'data_elaboracao' => '2026-02-10',
                'diagnostico' => '[F84.0] Transtorno do Espectro Autista, [H54.0] Baixa Visão / Cegueira',
                'tempo_livre' => 'Gosta de programar pequenos jogos em Scratch/Python, jogar xadrez online e ouvir música instrumental com fones.',
                'pratica_esporte' => 'Pratica natação adaptada duas vezes por semana com acompanhamento técnico.',
                'caracteristicas_discente' => 'Estudante focado em raciocínio lógico, calmo, mas sensível a ruídos altos e mudanças súbitas na rotina.',
                'organizacao_e_prazos' => 'Extremamente organizado com ferramentas digitais. Entrega tarefas pontualmente quando os prazos são claros.',
                'dificuldade_interacao_social' => 'Timidez inicial em grandes grupos; interage de forma produtiva em duplas com roteiros bem definidos.',
                'sugestoes_recursos_estrategias' => 'Textos e códigos com alto contraste (tema escuro) e fonte 18+; tempo adicional de 50% em avaliações escritas.',
                'status' => 'Ativo',
                'is_active' => true,
            ]
        );

        // responsáveis pela ficha inicial e acompanhamento do nai
        PeiResponsavel::where('pei_id', $peiLucas->id)->delete();
        PeiResponsavel::create(['pei_id' => $peiLucas->id, 'user_id' => $admin->id, 'secao' => 'ficha_inicial']);
        PeiResponsavel::create(['pei_id' => $peiLucas->id, 'user_id' => $naiBeatriz->id, 'secao' => 'ficha_inicial']);
        PeiResponsavel::create(['pei_id' => $peiLucas->id, 'nome_externo' => 'Dra. Helena Martins (Terapeuta Ocupacional Externa)', 'secao' => 'ficha_inicial']);

        PeiResponsavel::create(['pei_id' => $peiLucas->id, 'user_id' => $naiBeatriz->id, 'secao' => 'nai_1']);
        PeiResponsavel::create(['pei_id' => $peiLucas->id, 'user_id' => $naiBeatriz->id, 'secao' => 'nai_2']);
        PeiResponsavel::create(['pei_id' => $peiLucas->id, 'user_id' => $naiBeatriz->id, 'secao' => 'nai_3']);

        // identificações de habilidades e dificuldades do estudante
        PeiIdentificacao::where('pei_id', $peiLucas->id)->delete();
        $identLucas = [
            ['tipo' => 'habilidade', 'descricao' => 'Excelente capacidade analítica e raciocínio lógico para programação.'],
            ['tipo' => 'habilidade', 'descricao' => 'Rigor conceitual e dedicação a tarefas estruturadas.'],
            ['tipo' => 'dificuldade', 'descricao' => 'Fadiga visual acelerada com textos impressos em letras pequenas ou baixo contraste.'],
            ['tipo' => 'dificuldade', 'descricao' => 'Sobrecarga sensorial com conversas paralelas em sala de aula.'],
            ['tipo' => 'dificultador_interno', 'descricao' => 'Apresentações de slides com textos densos e fontes finas sem versão digital prévia.'],
            ['tipo' => 'facilitador_interno', 'descricao' => 'Disponibilização antecipada de PDFs em modo escuro e uso de fones abafadores de ruído.'],
            ['tipo' => 'dificultador_externo', 'descricao' => 'Linhas de ônibus lotadas que aumentam a ansiedade matinal.'],
            ['tipo' => 'facilitador_externo', 'descricao' => 'Suporte familiar ativo e contato próximo com a equipe pedagógica.'],
        ];
        foreach ($identLucas as $i => $item) {
            PeiIdentificacao::create(['pei_id' => $peiLucas->id, 'tipo' => $item['tipo'], 'descricao' => $item['descricao'], 'ordem' => $i]);
        }

        // propostas e encaminhamentos de intervenção pedagógica
        PeiIntervencao::where('pei_id', $peiLucas->id)->delete();
        $intervLucas = [
            ['demanda' => 'Adequação de materiais para baixa visão', 'encaminhamento_estrategia' => 'Fornecer apostilas e enunciados em formato digital (PDF acessível) e fontes ampliadas (18+).', 'responsavel' => 'Professores das disciplinas e Equipe NAI', 'ordem' => 0],
            ['demanda' => 'Manejo sensorial e pausas de autorregulação', 'encaminhamento_estrategia' => 'Autorizar uso de fones com cancelamento de ruído e pausas curtas de 5 minutos.', 'responsavel' => 'Docentes e discente', 'ordem' => 1],
            ['demanda' => 'Adaptação de avaliações formais', 'encaminhamento_estrategia' => 'Aplicação em sala silenciosa com tempo adicional de 50%.', 'responsavel' => 'Professores titulares e NAI', 'ordem' => 2],
        ];
        foreach ($intervLucas as $int) {
            PeiIntervencao::create(array_merge(['pei_id' => $peiLucas->id], $int));
        }

        // ações trimestrais desenvolvidas pelo nai
        PeiNaiAtividade::where('pei_id', $peiLucas->id)->delete();
        PeiNaiAtividade::create([
            'pei_id' => $peiLucas->id,
            'trimestre' => 1,
            'objetivos' => 'Orientar o corpo docente sobre as adaptações visuais e sensoriais; acompanhar a adaptação inicial.',
            'recursos_acoes' => 'Reunião individualizada com os professores da turma; checagem dos computadores do laboratório.',
            'tempo' => '1º Trimestre (Quinzenal)',
            'avaliacao_resultados' => 'O estudante obteve excelente rendimento acadêmico e relata conforto com os monitores configurados.',
            'sugestoes_aproveitamento' => 'Manter o protocolo de envio prévio dos materiais didáticos.',
            'ordem' => 0,
        ]);
        PeiNaiAtividade::create([
            'pei_id' => $peiLucas->id,
            'trimestre' => 2,
            'objetivos' => 'Monitorar o andamento dos projetos práticos em grupo e o suporte de tecnologia assistiva.',
            'recursos_acoes' => 'Entrevistas de acompanhamento com os docentes das matérias técnicas.',
            'tempo' => '2º Trimestre',
            'avaliacao_resultados' => 'Excelente integração com o grupo nos projetos integradores e plena autonomia com os recursos.',
            'sugestoes_aproveitamento' => 'Reforçar divisão equilibrada de papéis nos projetos de desenvolvimento web.',
            'ordem' => 0,
        ]);

        // acompanhamento trimestral das equipes de apoio
        PeiApoio::where('pei_id', $peiLucas->id)->delete();
        $apoioPedLucas = PeiApoio::create(['pei_id' => $peiLucas->id, 'nome' => 'Apoio Pedagógico em Informática', 'ordem' => 0]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioPedLucas->id, 'trimestre' => 1, 'user_id' => $profMariana->id]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioPedLucas->id, 'trimestre' => 1, 'user_id' => $apoioGabriel->id]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioPedLucas->id, 'trimestre' => 2, 'user_id' => $apoioGabriel->id]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioPedLucas->id,
            'trimestre' => 1,
            'objetivos' => 'Auxiliar na configuração dos ambientes de desenvolvimento com temas de alto contraste.',
            'recursos_acoes' => 'Monitoria presencial de 2h semanais no laboratório.',
            'tempo' => '2h semanais',
            'avaliacao_resultados' => 'Discente plenamente autônomo nas ferramentas de código.',
            'sugestoes_aproveitamento' => 'Prosseguir com suporte em bibliotecas gráficas.',
            'ordem' => 0,
        ]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioPedLucas->id,
            'trimestre' => 2,
            'objetivos' => 'Acompanhar a elaboração da interface acessível do projeto semestral.',
            'recursos_acoes' => 'Sessões semanais de mentoria técnica para testes de usabilidade.',
            'tempo' => '2h semanais',
            'avaliacao_resultados' => 'Projeto desenvolvido com padrões internacionais de acessibilidade web (WCAG).',
            'sugestoes_aproveitamento' => 'Submeter trabalho para apresentação na semana de ciência e tecnologia.',
            'ordem' => 0,
        ]);

        // planejamentos pedagógicos e avaliações das disciplinas da turma no 1º e 2º trimestres
        PeiDisciplina::where('pei_id', $peiLucas->id)->delete();
        $this->seedDisciplinaTrimestre($peiLucas, $discMat, 1, 'Dominar funções polinomiais de 1º e 2º grau e gráficos cartesianos.', 'Funções, equações, interpretação de gráficos e modelagem de problemas.', 'Softwares gráficos (GeoGebra com alto contraste), listas digitais ampliadas e tempo estendido.', 'Excelente domínio dos cálculos analíticos; uso eficaz do software para visualização das parábolas.', 'Apresentar desafios de aprofundamento lógico.', '9.5', '9.0');
        $this->seedDisciplinaTrimestre($peiLucas, $discPort, 1, 'Compreender estruturas textuais argumentativas e gêneros técnicos.', 'Tipologias textuais, coesão, coerência e leitura crítica.', 'Textos em formato digital com espaçamento aumentado e tempo adicional de 50%.', 'Ótima interpretação e clareza na produção textual.', 'Incentivar escrita de artigos de opinião.', '9.0', '9.5');
        $this->seedDisciplinaTrimestre($peiLucas, $discProgWeb, 1, 'Desenvolver páginas estruturadas com HTML5 semântico e CSS3 acessível.', 'Tags semânticas, seletores CSS, contraste de cores e formulários.', 'Editor VS Code configurado com tema escuro e leitor de tela para testes.', 'Código limpo, organizado e perfeitamente estruturado.', 'Avançar para interatividade com JavaScript.', '10.0', '10.0');

        $this->seedDisciplinaTrimestre($peiLucas, $discMat, 2, 'Compreender trigonometria no triângulo retângulo e matrizes.', 'Razões trigonométricas, matrizes e determinantes.', 'Modelos geométricos táteis e listas com fonte 18+ ampliadas.', 'Compreensão sólida dos conceitos trigonométricos e operações com matrizes.', 'Trabalhar aplicações práticas em programação gráfica.', '9.0', '9.5');
        $this->seedDisciplinaTrimestre($peiLucas, $discPort, 2, 'Analisar literatura brasileira moderna e produção de relatórios técnicos.', 'Modernismo brasileiro, redação técnica e comunicação acadêmica.', 'Áudio-livros complementares e envio prévio das leituras em PDF.', 'Relatórios entregues no prazo com alto rigor conceitual.', 'Aprofundar análise crítica dos textos selecionados.', '9.5', '9.0');
        $this->seedDisciplinaTrimestre($peiLucas, $discProgWeb, 2, 'Criar layouts responsivos com Flexbox, CSS Grid e boas práticas de acessibilidade.', 'Design responsivo, media queries e validação de acessibilidade.', 'Projetos práticos no laboratório com tutoria individualizada.', 'Interfaces responsivas elegantes com excelente navegabilidade por teclado.', 'Integrar com APIs REST.', '10.0', '9.8');

        // aluno 2: discente com surdez/libras e altas habilidades (2026)
        $alunaAna = Aluno::updateOrCreate(
            ['matricula' => '2026100456'],
            [
                'nome' => 'Ana Clara Ribeiro de Menezes',
                'data_nascimento' => '2009-08-22',
                'is_active' => true,
            ]
        );
        $alunaAna->deficiencias()->sync([$defAuditiva->id, $defAltasHabilidades->id]);
        $alunaAna->turmas()->sync([
            $turmaAdm1->id => ['ano_letivo' => 2026, 'is_atual' => true],
        ]);

        $peiAna = Pei::updateOrCreate(
            ['codigo' => 'PEI-2026-002'],
            [
                'aluno_id' => $alunaAna->id,
                'turma_id' => $turmaAdm1->id,
                'ano_letivo' => 2026,
                'data_elaboracao' => '2026-02-15',
                'diagnostico' => '[H90.0] Deficiência Auditiva Bilateral Severa/Profunda, [AH/SD] Altas Habilidades em Raciocínio Lógico e Artes Visuais',
                'tempo_livre' => 'Ilustração digital, leitura de literatura gráfica em língua portuguesa e edição de vídeos.',
                'pratica_esporte' => 'Voleibol escolar e corrida.',
                'caracteristicas_discente' => 'Muito participativa, atenta, comunicativa por meio da Libras e com excelente velocidade de leitura.',
                'organizacao_e_prazos' => 'Cumpre prazos com excelência e proatividade.',
                'dificuldade_interacao_social' => 'Interage bem com auxílio da intérprete e incentiva os colegas a aprenderem sinais básicos.',
                'sugestoes_recursos_estrategias' => 'Presença contínua da intérprete de Libras; legendagem de todos os vídeos exibidos; provas escritas avaliadas com critério de L2 (segunda língua).',
                'status' => 'Ativo',
                'is_active' => true,
            ]
        );

        PeiResponsavel::where('pei_id', $peiAna->id)->delete();
        PeiResponsavel::create(['pei_id' => $peiAna->id, 'user_id' => $naiRodrigo->id, 'secao' => 'ficha_inicial']);
        PeiResponsavel::create(['pei_id' => $peiAna->id, 'user_id' => $apoioLarissa->id, 'secao' => 'ficha_inicial']);
        PeiResponsavel::create(['pei_id' => $peiAna->id, 'user_id' => $naiRodrigo->id, 'secao' => 'nai_1']);
        PeiResponsavel::create(['pei_id' => $peiAna->id, 'user_id' => $naiRodrigo->id, 'secao' => 'nai_2']);
        PeiResponsavel::create(['pei_id' => $peiAna->id, 'user_id' => $naiRodrigo->id, 'secao' => 'nai_3']);

        PeiIdentificacao::where('pei_id', $peiAna->id)->delete();
        PeiIdentificacao::create(['pei_id' => $peiAna->id, 'tipo' => 'habilidade', 'descricao' => 'Fluência completa em Libras e excelente compreensão leitora.', 'ordem' => 0]);
        PeiIdentificacao::create(['pei_id' => $peiAna->id, 'tipo' => 'habilidade', 'descricao' => 'Destacada habilidade de síntese gráfica e mapas mentais.', 'ordem' => 1]);
        PeiIdentificacao::create(['pei_id' => $peiAna->id, 'tipo' => 'dificuldade', 'descricao' => 'Aulas expositivas com vídeos sem legenda ou sem intérprete em posição visível.', 'ordem' => 2]);
        PeiIdentificacao::create(['pei_id' => $peiAna->id, 'tipo' => 'facilitador_interno', 'descricao' => 'Intérprete posicionada à frente da sala e iluminação adequada.', 'ordem' => 3]);

        PeiIntervencao::where('pei_id', $peiAna->id)->delete();
        PeiIntervencao::create(['pei_id' => $peiAna->id, 'demanda' => 'Acessibilidade linguística em sala de aula', 'encaminhamento_estrategia' => 'Garantir tradução e interpretação em Libras em todas as aulas teóricas e práticas.', 'responsavel' => 'Intérprete de Libras e corpo docente', 'ordem' => 0]);

        PeiNaiAtividade::where('pei_id', $peiAna->id)->delete();
        PeiNaiAtividade::create([
            'pei_id' => $peiAna->id,
            'trimestre' => 1,
            'objetivos' => 'Garantir a infraestrutura de interpretação em Libras e organizar oficina de Libras para a turma.',
            'recursos_acoes' => 'Escala diária de intérprete e dinâmica de sensibilização no início das aulas.',
            'tempo' => '1º Trimestre',
            'avaliacao_resultados' => 'Integração plena da estudante com a turma e excelente aproveitamento.',
            'sugestoes_aproveitamento' => 'Propor atividades de liderança em projetos da área de administração.',
            'ordem' => 0,
        ]);
        PeiNaiAtividade::create([
            'pei_id' => $peiAna->id,
            'trimestre' => 2,
            'objetivos' => 'Acompanhar a produção de projetos interdisciplinares com foco em acessibilidade empresarial.',
            'recursos_acoes' => 'Encontros mensais de alinhamento com a estudante e docentes.',
            'tempo' => '2º Trimestre',
            'avaliacao_resultados' => 'Desenvolvendo plano de negócios acessível com alto grau de qualidade.',
            'sugestoes_aproveitamento' => 'Incentivar submissão do trabalho à feira científica.',
            'ordem' => 0,
        ]);

        // ações e atividades da equipe de apoio
        PeiApoio::where('pei_id', $peiAna->id)->delete();
        $apoioLibrasAna = PeiApoio::create(['pei_id' => $peiAna->id, 'nome' => 'Intérprete de Libras e Acessibilidade Comunicacional', 'ordem' => 0]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioLibrasAna->id, 'trimestre' => 1, 'user_id' => $apoioLarissa->id]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioLibrasAna->id, 'trimestre' => 2, 'user_id' => $apoioLarissa->id]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioLibrasAna->id,
            'trimestre' => 1,
            'objetivos' => 'Tradução simultânea das aulas e mediação nas dúvidas dos trabalhos em grupo.',
            'recursos_acoes' => 'Atuação diária em sala de aula e glossário de termos técnicos de administração em Libras.',
            'tempo' => 'Horário integral das aulas',
            'avaliacao_resultados' => 'Comunicação fluida e sem ruídos pedagógicos.',
            'sugestoes_aproveitamento' => 'Continuar com o registro em vídeo dos novos sinais técnicos criados.',
            'ordem' => 0,
        ]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioLibrasAna->id,
            'trimestre' => 2,
            'objetivos' => 'Interpretação simultânea durante as apresentações orais de planos de negócio.',
            'recursos_acoes' => 'Preparação prévia com vocabulário de gestão e comércio exterior.',
            'tempo' => 'Horário integral das aulas',
            'avaliacao_resultados' => 'Apresentações impecáveis e elogios unânimes da banca avaliadora.',
            'sugestoes_aproveitamento' => 'Organizar registro audiovisual da apresentação para portfólio acadêmico.',
            'ordem' => 0,
        ]);

        // planejamentos pedagógicos e avaliações de administração 1º ano
        PeiDisciplina::where('pei_id', $peiAna->id)->delete();
        $this->seedDisciplinaTrimestre($peiAna, $discMat, 1, 'Compreender matemática financeira básica, juros simples e compostos.', 'Porcentagem, acréscimos, descontos e juros em contextos administrativos.', 'Mapas conceituais visuais, enunciados objetivos e interpretação em Libras.', 'Resolução rápida de problemas e domínio completo das fórmulas.', 'Avançar para simulações financeiras em planilhas.', '10.0', '10.0');
        $this->seedDisciplinaTrimestre($peiAna, $discPort, 1, 'Produzir correspondências oficiais e comunicação empresarial em L2.', 'Redação oficial, ofícios, memorandos e coesão textual.', 'Correção com foco discursivo-semântico segundo diretrizes de L2 para surdos.', 'Produção escrita clara, formal e muito bem argumentada.', 'Incentivar elaboração de manuais internos de gestão.', '9.0', '9.5');
        $this->seedDisciplinaTrimestre($peiAna, $discHistoria, 1, 'Compreender a evolução do comércio e das revoluções industriais.', 'Primeira e Segunda Revoluções Industriais e impactos sociais.', 'Linhas do tempo interativas, infográficos e recursos imagéticos ricos.', 'Excelente apreensão dos processos históricos e síntese visual dos períodos.', 'Estimular pesquisas sobre direitos do trabalhador com deficiência.', '9.8', '9.5');

        $this->seedDisciplinaTrimestre($peiAna, $discMat, 2, 'Dominar estatística descritiva e análise de gráficos gerenciais.', 'Média, mediana, moda, desvio padrão e interpretação de tabelas.', 'Softwares estatísticos e construção de dashboards visuais.', 'Criação de gráficos informativos de alto nível e precisão estatística.', 'Aplicar em projetos de pesquisa de mercado.', '10.0', '9.8');
        $this->seedDisciplinaTrimestre($peiAna, $discPort, 2, 'Desenvolver leitura crítica de relatórios de auditoria e contratos.', 'Linguagem contratual, termos técnicos e síntese executiva.', 'Glossário bilíngue Português-Libras e tempo adicional nas avaliações.', 'Capacidade crítica aguçada na revisão de termos e cláusulas.', 'Participar da redação de projetos de extensão.', '9.5', '9.5');
        $this->seedDisciplinaTrimestre($peiAna, $discHistoria, 2, 'Analisar o desenvolvimento econômico e industrial do Brasil no século XX.', 'Era Vargas, industrialização, urbanização e movimentos sociais.', 'Vídeos documentais legendados e pesquisas iconográficas.', 'Participação ativa nos debates mediados por Libras e redação de ensaios consistentes.', 'Aprofundar estudos sobre a história da comunidade surda no Brasil.', '9.5', '10.0');

        // aluno 3: discente com tdah e dislexia (2026)
        $alunoEnzo = Aluno::updateOrCreate(
            ['matricula' => '2026100789'],
            [
                'nome' => 'Enzo Gabriel Nogueira',
                'data_nascimento' => '2008-11-03',
                'is_active' => true,
            ]
        );
        $alunoEnzo->deficiencias()->sync([$defTdah->id, $defDislexia->id]);
        $alunoEnzo->turmas()->sync([
            $turmaInfo2->id => ['ano_letivo' => 2026, 'is_atual' => true],
        ]);

        $peiEnzo = Pei::updateOrCreate(
            ['codigo' => 'PEI-2026-003'],
            [
                'aluno_id' => $alunoEnzo->id,
                'turma_id' => $turmaInfo2->id,
                'ano_letivo' => 2026,
                'data_elaboracao' => '2026-02-18',
                'diagnostico' => '[F90.0] Transtorno do Déficit de Atenção com Hiperatividade (tipo combinado), [F81.0] Transtorno Específico de Leitura',
                'tempo_livre' => 'Robótica, montagem de computadores, esportes radicais (skate) e jogos de estratégia.',
                'pratica_esporte' => 'Skate e musculação funcional.',
                'caracteristicas_discente' => 'Muito dinâmico, criativo em soluções de hardware e software prático, porém dispersa com facilidade em leituras longas.',
                'organizacao_e_prazos' => 'Necessita de lembretes e fracionamento de etapas em entregas longas.',
                'dificuldade_interacao_social' => 'Sociável, comunicativo, bem integrado à turma.',
                'sugestoes_recursos_estrategias' => 'Uso de fontes amigáveis para dislexia (OpenDyslexic / Verdana), enunciados curtos, avaliações práticas e orais.',
                'status' => 'Ativo',
                'is_active' => true,
            ]
        );

        PeiResponsavel::where('pei_id', $peiEnzo->id)->delete();
        PeiResponsavel::create(['pei_id' => $peiEnzo->id, 'user_id' => $admin->id, 'secao' => 'ficha_inicial']);
        PeiResponsavel::create(['pei_id' => $peiEnzo->id, 'user_id' => $profRicardo->id, 'secao' => 'ficha_inicial']);
        PeiResponsavel::create(['pei_id' => $peiEnzo->id, 'user_id' => $admin->id, 'secao' => 'nai_1']);
        PeiResponsavel::create(['pei_id' => $peiEnzo->id, 'user_id' => $admin->id, 'secao' => 'nai_2']);
        PeiResponsavel::create(['pei_id' => $peiEnzo->id, 'user_id' => $admin->id, 'secao' => 'nai_3']);

        PeiIdentificacao::where('pei_id', $peiEnzo->id)->delete();
        PeiIdentificacao::create(['pei_id' => $peiEnzo->id, 'tipo' => 'habilidade', 'descricao' => 'Grande agilidade no raciocínio prático e montagem de arquiteturas de sistemas.', 'ordem' => 0]);
        PeiIdentificacao::create(['pei_id' => $peiEnzo->id, 'tipo' => 'dificuldade', 'descricao' => 'Textos densos sem divisão em tópicos e provas teóricas com muitas questões por página.', 'ordem' => 1]);

        PeiIntervencao::where('pei_id', $peiEnzo->id)->delete();
        PeiIntervencao::create(['pei_id' => $peiEnzo->id, 'demanda' => 'Adaptação de avaliações escritas e prazos', 'encaminhamento_estrategia' => 'Permitir leitura mediada das questões e fracionar tarefas grandes com entregas parciais.', 'responsavel' => 'Docentes titulares', 'ordem' => 0]);

        PeiNaiAtividade::where('pei_id', $peiEnzo->id)->delete();
        PeiNaiAtividade::create([
            'pei_id' => $peiEnzo->id,
            'trimestre' => 1,
            'objetivos' => 'Orientar professores de informática sobre adaptações em enunciados de laboratório e gestão de tempo.',
            'recursos_acoes' => 'Guia prático de adaptação pedagógica entregue aos docentes e alinhamento individual.',
            'tempo' => '1º Trimestre',
            'avaliacao_resultados' => 'Melhora evidente na entrega de atividades práticas no prazo.',
            'sugestoes_aproveitamento' => 'Manter o uso do aplicativo de tarefas sincronizado com o cronograma escolar.',
            'ordem' => 0,
        ]);
        PeiNaiAtividade::create([
            'pei_id' => $peiEnzo->id,
            'trimestre' => 2,
            'objetivos' => 'Acompanhar técnicas de estudos pomodoro e organização de cronograma de provas.',
            'recursos_acoes' => 'Sessões quinzenais de monitoramento psico-organizacional com o discente.',
            'tempo' => '2º Trimestre',
            'avaliacao_resultados' => 'Redução significativa da ansiedade pré-provas e notas consistentes.',
            'sugestoes_aproveitamento' => 'Recomendar manutenção de rotinas de pausas ativas nos estudos.',
            'ordem' => 0,
        ]);

        PeiApoio::where('pei_id', $peiEnzo->id)->delete();
        $apoioEnzoPed = PeiApoio::create(['pei_id' => $peiEnzo->id, 'nome' => 'Apoio Psicopedagógico e Organização de Estudos', 'ordem' => 0]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioEnzoPed->id, 'trimestre' => 1, 'user_id' => $naiRodrigo->id]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioEnzoPed->id, 'trimestre' => 2, 'user_id' => $naiRodrigo->id]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioEnzoPed->id,
            'trimestre' => 1,
            'objetivos' => 'Elaborar mapa visual de prazos e checklists para laboratório de informática.',
            'recursos_acoes' => 'Quadro kanban digital e reuniões semanais de 30 minutos.',
            'tempo' => 'Semanal',
            'avaliacao_resultados' => 'O estudante cumpriu 100% das entregas parciais no prazo.',
            'sugestoes_aproveitamento' => 'Incorporar notificações automáticas no calendário.',
            'ordem' => 0,
        ]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioEnzoPed->id,
            'trimestre' => 2,
            'objetivos' => 'Orientar estratégias de leitura dinâmica adaptada para enunciados de física e banco de dados.',
            'recursos_acoes' => 'Técnicas de sublinhado por cores e resumo esquemático.',
            'tempo' => 'Quinzenal',
            'avaliacao_resultados' => 'Maior precisão na interpretação das questões de múltipla escolha.',
            'sugestoes_aproveitamento' => 'Manter o método nos exames finais.',
            'ordem' => 0,
        ]);

        PeiDisciplina::where('pei_id', $peiEnzo->id)->delete();
        $this->seedDisciplinaTrimestre($peiEnzo, $discBancoDados, 1, 'Modelar bancos relacionais (Diagrama Entidade-Relacionamento) e comandos SQL DDL.', 'Entidades, relacionamentos, cardinalidade, CREATE TABLE e chaves primárias.', 'Enunciados curtos em tópicos, uso do MySQL Workbench com interface gráfica e tempo adicional.', 'Modelagens lógicas muito bem estruturadas com raciocínio relacional impecável.', 'Aprofundar comandos DML complexos e consultas aninhadas.', '9.0', '9.0');
        $this->seedDisciplinaTrimestre($peiEnzo, $discFisica, 1, 'Compreender cinemática escalar, movimento retilíneo uniforme e acelerado.', 'Posição, velocidade, aceleração, gráficos de movimento e queda livre.', 'Simulações interativas PhET, experimentos práticos no pátio e formulários impressos para consulta.', 'Ótimo desempenho em atividades práticas de laboratório.', 'Reforçar interpretação algébrica das equações horárias.', '8.5', '9.0');

        $this->seedDisciplinaTrimestre($peiEnzo, $discBancoDados, 2, 'Construir consultas SQL avançadas com joins, agregações e subconsultas.', 'SELECT, INNER JOIN, LEFT JOIN, GROUP BY, HAVING e funções de agregação.', 'Desafios práticos em ambiente gamificado e avaliações práticas no computador.', 'Execução de queries complexas com excelente lógica e rapidez.', 'Implementar gatilhos e stored procedures.', '9.5', '9.0');
        $this->seedDisciplinaTrimestre($peiEnzo, $discFisica, 2, 'Aplicar as Leis de Newton e noções de dinâmica e trabalho mecânico.', 'Força, atrito, plano inclinado, trabalho e energia cinética.', 'Montagem de experimentos com carrinhos e sensores de movimento.', 'Compreensão intuitiva dos vetores de força e bom rendimento nas provas fracionadas.', 'Introduzir princípios de conservação de energia.', '8.8', '9.2');

        // aluno 4: discente com paralisia cerebral / deficiência física motora (2026)
        $alunaSophia = Aluno::updateOrCreate(
            ['matricula' => '2026100912'],
            [
                'nome' => 'Sophia Helena Albuquerque',
                'data_nascimento' => '2009-12-10',
                'is_active' => true,
            ]
        );
        $alunaSophia->deficiencias()->sync([$defFisica->id]);
        $alunaSophia->turmas()->sync([
            $turmaEdif1->id => ['ano_letivo' => 2026, 'is_atual' => true],
        ]);

        $peiSophia = Pei::updateOrCreate(
            ['codigo' => 'PEI-2026-004'],
            [
                'aluno_id' => $alunaSophia->id,
                'turma_id' => $turmaEdif1->id,
                'ano_letivo' => 2026,
                'data_elaboracao' => '2026-02-25',
                'diagnostico' => '[G80.0] Paralisia Cerebral Espástica com acometimento dos membros inferiores e moderado em membros superiores',
                'tempo_livre' => 'Desenho digital com mesa digitalizadora, leitura de arquitetura e jogos de simulação urbana.',
                'pratica_esporte' => 'Fisioterapia motora e hidroterapia.',
                'caracteristicas_discente' => 'Aluna muito focada, excelente comunicação verbal e oralidade destacada.',
                'organizacao_e_prazos' => 'Cumpre prazos com uso de softwares de digitação por voz e prancheta ergonômica.',
                'dificuldade_interacao_social' => 'Muito sociável e participativa em discussões de sala.',
                'sugestoes_recursos_estrategias' => 'Mesa adaptada para cadeira de rodas; suporte ergonômico; aceitar trabalhos digitais em vez de caligrafia manual em pranchas físicas.',
                'status' => 'Ativo',
                'is_active' => true,
            ]
        );

        PeiResponsavel::where('pei_id', $peiSophia->id)->delete();
        PeiResponsavel::create(['pei_id' => $peiSophia->id, 'user_id' => $naiBeatriz->id, 'secao' => 'ficha_inicial']);
        PeiResponsavel::create(['pei_id' => $peiSophia->id, 'user_id' => $profJuliana->id, 'secao' => 'ficha_inicial']);
        PeiResponsavel::create(['pei_id' => $peiSophia->id, 'user_id' => $naiBeatriz->id, 'secao' => 'nai_1']);
        PeiResponsavel::create(['pei_id' => $peiSophia->id, 'user_id' => $naiBeatriz->id, 'secao' => 'nai_2']);
        PeiResponsavel::create(['pei_id' => $peiSophia->id, 'user_id' => $naiBeatriz->id, 'secao' => 'nai_3']);

        PeiIdentificacao::where('pei_id', $peiSophia->id)->delete();
        PeiIdentificacao::create(['pei_id' => $peiSophia->id, 'tipo' => 'habilidade', 'descricao' => 'Comunicação oral excelente e grande destreza em softwares CAD digitais.', 'ordem' => 0]);
        PeiIdentificacao::create(['pei_id' => $peiSophia->id, 'tipo' => 'dificuldade', 'descricao' => 'Desenho manual com régua T e caligrafia técnica em pranchetas analógicas.', 'ordem' => 1]);

        PeiIntervencao::where('pei_id', $peiSophia->id)->delete();
        PeiIntervencao::create(['pei_id' => $peiSophia->id, 'demanda' => 'Acessibilidade física e adequação de desenho técnico', 'encaminhamento_estrategia' => 'Substituir pranchas físicas por AutoCAD/Revit e assegurar mesa com regulagem de altura.', 'responsavel' => 'Professores de desenho e NAI', 'ordem' => 0]);

        PeiNaiAtividade::where('pei_id', $peiSophia->id)->delete();
        PeiNaiAtividade::create([
            'pei_id' => $peiSophia->id,
            'trimestre' => 1,
            'objetivos' => 'Viabilizar mesa ergonômica ajustável e orientar professores de edificações sobre entrega em formato CAD.',
            'recursos_acoes' => 'Instalação de posto de trabalho acessível no laboratório de desenho e sala de aula.',
            'tempo' => '1º Trimestre',
            'avaliacao_resultados' => 'Posto de trabalho 100% operacional com excelente postura e conforto relatados.',
            'sugestoes_aproveitamento' => 'Manter manutenção periódica da estação adaptada.',
            'ordem' => 0,
        ]);
        PeiNaiAtividade::create([
            'pei_id' => $peiSophia->id,
            'trimestre' => 2,
            'objetivos' => 'Acompanhar a participação da estudante em visitas técnicas a canteiros de obras com roteiros acessíveis.',
            'recursos_acoes' => 'Vistoria prévia de acessibilidade nos locais de visitação técnica.',
            'tempo' => '2º Trimestre',
            'avaliacao_resultados' => 'Participação plena e segura da aluna em todas as atividades externas.',
            'sugestoes_aproveitamento' => 'Continuar planejando rotas com antecedência mínima de 15 dias.',
            'ordem' => 0,
        ]);

        PeiApoio::where('pei_id', $peiSophia->id)->delete();
        $apoioSophiaAcess = PeiApoio::create(['pei_id' => $peiSophia->id, 'nome' => 'Tecnologia Assistiva e Ergonomia', 'ordem' => 0]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioSophiaAcess->id, 'trimestre' => 1, 'user_id' => $naiBeatriz->id]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioSophiaAcess->id, 'trimestre' => 2, 'user_id' => $naiBeatriz->id]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioSophiaAcess->id,
            'trimestre' => 1,
            'objetivos' => 'Configuração de teclado adaptado com colmeia acrílica e trackball ergonômico.',
            'recursos_acoes' => 'Testes de velocidade e precisão de digitação no ambiente acadêmico.',
            'tempo' => 'Início do ano letivo',
            'avaliacao_resultados' => 'Aumento de 60% no rendimento de digitação e eliminação de dores musculares.',
            'sugestoes_aproveitamento' => 'Manter acessórios sob cautela direta do discente.',
            'ordem' => 0,
        ]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioSophiaAcess->id,
            'trimestre' => 2,
            'objetivos' => 'Treinamento de atalhos e comandos avançados de voz para comandos de desenho no AutoCAD.',
            'recursos_acoes' => 'Oficina personalizada de 4 horas no NAI.',
            'tempo' => 'Maio',
            'avaliacao_resultados' => 'Autonomia completa na elaboração de plantas baixas e cortes arquitetônicos.',
            'sugestoes_aproveitamento' => 'Compartilhar boas práticas com outros alunos da instituição.',
            'ordem' => 0,
        ]);

        PeiDisciplina::where('pei_id', $peiSophia->id)->delete();
        $this->seedDisciplinaTrimestre($peiSophia, $discFisica, 1, 'Compreender estática dos corpos rígidos e equilíbrio de estruturas.', 'Centro de massa, momento de uma força, condições de equilíbrio estático em vigas.', 'Simuladores virtuais de treliças e substituição de escrita manual por digitação.', 'Excelente compreensão dos momentos de força e projetos estruturais impecáveis.', 'Aprofundar dimensionamento de tensões.', '9.5', '9.5');
        $this->seedDisciplinaTrimestre($peiSophia, $discFisica, 2, 'Analisar hidrostática, pressão e empuxo aplicados à construção civil.', 'Princípio de Pascal, vasos comunicantes, empuxo e dimensionamento de caixas d’água.', 'Softwares de cálculo hidráulico e avaliação teórica com resposta oral mediada.', 'Raciocínio fluido e alta precisão nos cálculos de pressão e vazão.', 'Integrar com projetos de instalações prediais.', '9.8', '9.2');

        // aluno 5: discente com histórico de pei anterior (2025) e pei corrente (2026)
        $alunoMatheus = Aluno::updateOrCreate(
            ['matricula' => '2025100088'],
            [
                'nome' => 'Matheus Henrique Silveira',
                'data_nascimento' => '2008-05-19',
                'is_active' => true,
            ]
        );
        $alunoMatheus->deficiencias()->sync([$defTea->id]);
        $alunoMatheus->turmas()->sync([
            $turmaInfo1->id => ['ano_letivo' => 2026, 'is_atual' => true],
            $turmaInfo2025->id => ['ano_letivo' => 2025, 'is_atual' => false],
        ]);

        // pei histórico de 2025 (100% preenchido nos 3 trimestres)
        $peiMatheus2025 = Pei::updateOrCreate(
            ['codigo' => 'PEI-2025-099'],
            [
                'aluno_id' => $alunoMatheus->id,
                'turma_id' => $turmaInfo2025->id,
                'ano_letivo' => 2025,
                'data_elaboracao' => '2025-03-01',
                'diagnostico' => '[F84.0] Transtorno do Espectro Autista Leve',
                'tempo_livre' => 'Leitura de ficção científica e robótica básica com Arduino.',
                'pratica_esporte' => 'Caminhada matinal e tênis de mesa.',
                'caracteristicas_discente' => 'Muito metódico, excelente memória de longo prazo para datas e fatos históricos.',
                'organizacao_e_prazos' => 'Precisa de rotinas fixas e avisos com 3 dias de antecedência para entregas de trabalhos.',
                'dificuldade_interacao_social' => 'Prefere trabalhar individualmente ou com colegas de confiança estabelecida.',
                'sugestoes_recursos_estrategias' => 'Instruções escritas passo a passo e ambiente previsível sem remanejamento súbito de cadeiras.',
                'status' => 'Concluído',
                'is_active' => true,
            ]
        );

        PeiResponsavel::where('pei_id', $peiMatheus2025->id)->delete();
        PeiResponsavel::create(['pei_id' => $peiMatheus2025->id, 'user_id' => $admin->id, 'secao' => 'ficha_inicial']);
        PeiResponsavel::create(['pei_id' => $peiMatheus2025->id, 'user_id' => $naiBeatriz->id, 'secao' => 'nai_1']);
        PeiResponsavel::create(['pei_id' => $peiMatheus2025->id, 'user_id' => $naiBeatriz->id, 'secao' => 'nai_2']);
        PeiResponsavel::create(['pei_id' => $peiMatheus2025->id, 'user_id' => $naiBeatriz->id, 'secao' => 'nai_3']);

        PeiIdentificacao::where('pei_id', $peiMatheus2025->id)->delete();
        PeiIdentificacao::create(['pei_id' => $peiMatheus2025->id, 'tipo' => 'habilidade', 'descricao' => 'Foco extremo e precisão lógica na escrita de algoritmos.', 'ordem' => 0]);
        PeiIdentificacao::create(['pei_id' => $peiMatheus2025->id, 'tipo' => 'dificuldade', 'descricao' => 'Desconforto com imprevistos no calendário acadêmico.', 'ordem' => 1]);

        PeiIntervencao::where('pei_id', $peiMatheus2025->id)->delete();
        PeiIntervencao::create(['pei_id' => $peiMatheus2025->id, 'demanda' => 'Previsibilidade da rotina de avaliações', 'encaminhamento_estrategia' => 'Divulgação do calendário de provas com antecedência mínima de uma semana.', 'responsavel' => 'Coordenação e docentes', 'ordem' => 0]);

        PeiNaiAtividade::where('pei_id', $peiMatheus2025->id)->delete();
        PeiNaiAtividade::create([
            'pei_id' => $peiMatheus2025->id,
            'trimestre' => 1,
            'objetivos' => 'Orientar a coordenação do curso sobre o acolhimento pedagógico e estruturação das rotinas.',
            'recursos_acoes' => 'Reunião de alinhamento com docentes do 3º ano.',
            'tempo' => '1º Trimestre',
            'avaliacao_resultados' => 'Adaptação tranquila com notas acima da média.',
            'sugestoes_aproveitamento' => 'Manter o protocolo no 2º trimestre.',
            'ordem' => 0,
        ]);
        PeiNaiAtividade::create([
            'pei_id' => $peiMatheus2025->id,
            'trimestre' => 2,
            'objetivos' => 'Acompanhar o trabalho de conclusão de curso (TCC) e mediação em duplas.',
            'recursos_acoes' => 'Supervisão do plano de trabalho do TCC junto ao orientador.',
            'tempo' => '2º Trimestre',
            'avaliacao_resultados' => 'Cronograma do TCC executado com pontualidade.',
            'sugestoes_aproveitamento' => 'Iniciar redação dos capítulos finais.',
            'ordem' => 0,
        ]);
        PeiNaiAtividade::create([
            'pei_id' => $peiMatheus2025->id,
            'trimestre' => 3,
            'objetivos' => 'Consolidar as avaliações finais e apoiar o discente na preparação para a banca de TCC.',
            'recursos_acoes' => 'Simulação de apresentação em ambiente protegido no NAI.',
            'tempo' => '3º Trimestre',
            'avaliacao_resultados' => 'Apresentação pública do TCC aprovada com nota 10.',
            'sugestoes_aproveitamento' => 'Transição bem-sucedida para o ciclo seguinte.',
            'ordem' => 0,
        ]);

        PeiApoio::where('pei_id', $peiMatheus2025->id)->delete();
        $apoioMatheus2025 = PeiApoio::create(['pei_id' => $peiMatheus2025->id, 'nome' => 'Acompanhamento Psicopedagógico', 'ordem' => 0]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioMatheus2025->id, 'trimestre' => 1, 'user_id' => $naiRodrigo->id]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioMatheus2025->id, 'trimestre' => 2, 'user_id' => $naiRodrigo->id]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioMatheus2025->id, 'trimestre' => 3, 'user_id' => $naiRodrigo->id]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioMatheus2025->id,
            'trimestre' => 1,
            'objetivos' => 'Construir rotina estruturada de estudos e controle de prazos.',
            'recursos_acoes' => 'Encontros quinzenais individuais.',
            'tempo' => 'Quinzenal',
            'avaliacao_resultados' => 'Estudante altamente engajado e organizado.',
            'sugestoes_aproveitamento' => 'Manter o acompanhamento.',
            'ordem' => 0,
        ]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioMatheus2025->id,
            'trimestre' => 2,
            'objetivos' => 'Mediação interpessoal nas etapas práticas do TCC.',
            'recursos_acoes' => 'Alinhamentos mensais com a dupla de projeto.',
            'tempo' => 'Mensal',
            'avaliacao_resultados' => 'Ambiente harmonioso e produtivo entre os discentes.',
            'sugestoes_aproveitamento' => 'Prosseguir com testes finais do protótipo.',
            'ordem' => 0,
        ]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioMatheus2025->id,
            'trimestre' => 3,
            'objetivos' => 'Treinamento de oratória e controle de ansiedade para a banca.',
            'recursos_acoes' => 'Ensaios práticos de apresentação.',
            'tempo' => 'Semanal em Novembro',
            'avaliacao_resultados' => 'Excelente autoconfiança demonstrada na apresentação.',
            'sugestoes_aproveitamento' => 'Conclusão com louvor.',
            'ordem' => 0,
        ]);

        PeiDisciplina::where('pei_id', $peiMatheus2025->id)->delete();
        $this->seedDisciplinaTrimestre($peiMatheus2025, $discMat, 1, 'Compreender matrizes e sistemas lineares.', 'Sistemas de equações, determinantes e escalonamento.', 'Listas estruturadas passo a passo.', 'Domínio impecável do método de escalonamento.', 'Manter rigor conceitual.', '10.0', '9.5');
        $this->seedDisciplinaTrimestre($peiMatheus2025, $discPort, 1, 'Desenvolver redação científica e estruturação de artigos.', 'Normas ABNT, citações, referências e escrita acadêmica.', 'Roteiros objetivos de escrita e checklists.', 'Texto do pré-projeto claro e bem estruturado.', 'Avançar para redação dos resultados.', '9.0', '9.5');
        $this->seedDisciplinaTrimestre($peiMatheus2025, $discProgWeb, 1, 'Construir arquitetura de software para o TCC em PHP/Laravel.', 'Rotas, controladores, modelos Eloquent e migrations.', 'Desenvolvimento orientado a testes em ambiente prático.', 'Código robusto e em conformidade com as boas práticas.', 'Integrar com camada de autenticação.', '10.0', '10.0');

        $this->seedDisciplinaTrimestre($peiMatheus2025, $discMat, 2, 'Dominar geometria analítica no espaço e vetores.', 'Retas, planos e distâncias no espaço tridimensional.', 'Visualização com GeoGebra 3D.', 'Resolução precisa de todos os exercícios propostos.', 'Aplicar em computação gráfica.', '9.5', '9.8');
        $this->seedDisciplinaTrimestre($peiMatheus2025, $discPort, 2, 'Redigir metodologia e análise de resultados do TCC.', 'Gênero monográfico e apresentação de dados experimentais.', 'Feedback quinzenal por escrito sobre as versões prévias.', 'Capítulos metodológicos detalhados e consistentes.', 'Preparar conclusões e resumo executivo.', '9.5', '9.5');
        $this->seedDisciplinaTrimestre($peiMatheus2025, $discProgWeb, 2, 'Implementar interfaces reativas e consumo de APIs assíncronas.', 'Vue.js, componentes e comunicação via Axios.', 'Workshops práticos no laboratório de programação.', 'Interface fluida e excelente experiência do usuário.', 'Preparar deploy em servidor de testes.', '10.0', '9.8');

        $this->seedDisciplinaTrimestre($peiMatheus2025, $discMat, 3, 'Compreender noções de cálculo diferencial e integral.', 'Limites, derivadas fundamentais e taxas de variação.', 'Interpretação geométrica das derivadas com gráficos interativos.', 'Compreensão intuitiva do cálculo e resolução de problemas.', 'Base sólida para cursos de nível superior.', '9.8', '9.5');
        $this->seedDisciplinaTrimestre($peiMatheus2025, $discPort, 3, 'Finalizar monografia e preparar slides de apresentação acadêmica.', 'Revisão textual, coesão geral e comunicação oral para bancas.', 'Ensaios e revisão gramatical estruturada.', 'Trabalho final com formatação impecável e alta qualidade.', 'Aprovado com nota máxima.', '10.0', '10.0');
        $this->seedDisciplinaTrimestre($peiMatheus2025, $discProgWeb, 3, 'Realizar testes de segurança, performance e homologação do software.', 'Auditoria de segurança, testes unitários e homologação.', 'Ambiente de produção em nuvem configurado.', 'Sistema entregue com 100% dos requisitos funcionais atendidos.', 'Parabéns pela conclusão do projeto.', '10.0', '10.0');

        // pei ativo do ano letivo corrente (2026)
        $peiMatheus2026 = Pei::updateOrCreate(
            ['codigo' => 'PEI-2026-005'],
            [
                'aluno_id' => $alunoMatheus->id,
                'turma_id' => $turmaInfo1->id,
                'ano_letivo' => 2026,
                'data_elaboracao' => '2026-02-28',
                'diagnostico' => '[F84.0] Transtorno do Espectro Autista Leve',
                'tempo_livre' => 'Leitura de ficção científica, robótica com Arduino e programação C++.',
                'pratica_esporte' => 'Caminhada e natação aos finais de semana.',
                'caracteristicas_discente' => 'Muito metódico, dedicado e rigoroso com a lógica das tarefas.',
                'organizacao_e_prazos' => 'Cumpre prazos com rigor quando fornecido checklist detalhado.',
                'dificuldade_interacao_social' => 'Apresenta maior abertura para trabalho em duplas.',
                'sugestoes_recursos_estrategias' => 'Checklists digitais passo a passo, ambiente com iluminação natural e tempo extra se necessário.',
                'status' => 'Ativo',
                'is_active' => true,
            ]
        );

        PeiResponsavel::where('pei_id', $peiMatheus2026->id)->delete();
        PeiResponsavel::create(['pei_id' => $peiMatheus2026->id, 'user_id' => $admin->id, 'secao' => 'ficha_inicial']);
        PeiResponsavel::create(['pei_id' => $peiMatheus2026->id, 'user_id' => $naiBeatriz->id, 'secao' => 'nai_1']);
        PeiResponsavel::create(['pei_id' => $peiMatheus2026->id, 'user_id' => $naiBeatriz->id, 'secao' => 'nai_2']);
        PeiResponsavel::create(['pei_id' => $peiMatheus2026->id, 'user_id' => $naiBeatriz->id, 'secao' => 'nai_3']);

        PeiIdentificacao::where('pei_id', $peiMatheus2026->id)->delete();
        PeiIdentificacao::create(['pei_id' => $peiMatheus2026->id, 'tipo' => 'habilidade', 'descricao' => 'Raciocínio dedutivo refinado e alta capacidade de abstração matemática.', 'ordem' => 0]);
        PeiIdentificacao::create(['pei_id' => $peiMatheus2026->id, 'tipo' => 'dificuldade', 'descricao' => 'Resistência pontual a alterações não comunicadas no cronograma semanal.', 'ordem' => 1]);

        PeiIntervencao::where('pei_id', $peiMatheus2026->id)->delete();
        PeiIntervencao::create(['pei_id' => $peiMatheus2026->id, 'demanda' => 'Manutenção do calendário pedagógico estruturado', 'encaminhamento_estrategia' => 'Fornecimento do planejamento trimestral detalhado na primeira semana de aula.', 'responsavel' => 'Docentes e NAI', 'ordem' => 0]);

        PeiNaiAtividade::where('pei_id', $peiMatheus2026->id)->delete();
        PeiNaiAtividade::create([
            'pei_id' => $peiMatheus2026->id,
            'trimestre' => 1,
            'objetivos' => 'Realizar acolhimento inicial no ano letivo e alinhamento com a nova turma de informática.',
            'recursos_acoes' => 'Entrevista de acompanhamento e suporte na escolha da dupla de laboratório.',
            'tempo' => '1º Trimestre',
            'avaliacao_resultados' => 'Excelente adaptação e liderança técnica positiva no grupo.',
            'sugestoes_aproveitamento' => 'Incentivar participação na maratona de programação.',
            'ordem' => 0,
        ]);
        PeiNaiAtividade::create([
            'pei_id' => $peiMatheus2026->id,
            'trimestre' => 2,
            'objetivos' => 'Monitorar o andamento dos projetos interdisciplinares e equilíbrio na carga de estudos.',
            'recursos_acoes' => 'Reunião mensal de checagem do plano de estudos.',
            'tempo' => '2º Trimestre',
            'avaliacao_resultados' => 'Desempenho exemplar e entrega de todas as atividades com notas excelentes.',
            'sugestoes_aproveitamento' => 'Apoiar candidatura a monitor de algoritmos.',
            'ordem' => 0,
        ]);

        PeiApoio::where('pei_id', $peiMatheus2026->id)->delete();
        $apoioMatheus2026 = PeiApoio::create(['pei_id' => $peiMatheus2026->id, 'nome' => 'Apoio em Habilidades de Programação e Monitoria', 'ordem' => 0]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioMatheus2026->id, 'trimestre' => 1, 'user_id' => $profRicardo->id]);
        PeiApoioResponsavel::create(['pei_apoio_id' => $apoioMatheus2026->id, 'trimestre' => 2, 'user_id' => $profRicardo->id]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioMatheus2026->id,
            'trimestre' => 1,
            'objetivos' => 'Mentoria técnica em desafios avançados de estruturas de dados.',
            'recursos_acoes' => 'Encontros semanais de resolução de problemas algorítmicos.',
            'tempo' => '1h semanal',
            'avaliacao_resultados' => 'Rendimento extraordinário na resolução de desafios lógicos complexos.',
            'sugestoes_aproveitamento' => 'Preparar inscrição na Olimpíada Brasileira de Informática (OBI).',
            'ordem' => 0,
        ]);
        PeiApoioAtividade::create([
            'pei_apoio_id' => $apoioMatheus2026->id,
            'trimestre' => 2,
            'objetivos' => 'Supervisão de projeto de extensão voltado ao ensino de lógica de programação para calouros.',
            'recursos_acoes' => 'Oficinas práticas mediadas pelo discente sob orientação docente.',
            'tempo' => '2h semanais',
            'avaliacao_resultados' => 'Excelente comunicação didática e acolhimento dos estudantes novatos.',
            'sugestoes_aproveitamento' => 'Publicar relato de experiência em evento institucional.',
            'ordem' => 0,
        ]);

        PeiDisciplina::where('pei_id', $peiMatheus2026->id)->delete();
        $this->seedDisciplinaTrimestre($peiMatheus2026, $discMat, 1, 'Aprofundar funções trigonométricas e modelagem matemática.', 'Funções seno, cosseno e modelagem de ondas periódicas.', 'Simulações no GeoGebra e listas com desafios adicionais.', 'Compreensão avançada das relações trigonométricas com notas máximas.', 'Introduzir números complexos.', '10.0', '10.0');
        $this->seedDisciplinaTrimestre($peiMatheus2026, $discPort, 1, 'Desenvolver leitura crítica de ensaios e produção de resenhas acadêmicas.', 'Gênero resenha crítica, coesão avançada e argumentação lógica.', 'Critérios objetivos de avaliação e checklists de revisão.', 'Resenhas de alto nível com fundamentação teórica sólida.', 'Incentivar publicação na revista científica da escola.', '9.5', '9.5');
        $this->seedDisciplinaTrimestre($peiMatheus2026, $discProgWeb, 1, 'Construir microsserviços e APIs RESTful seguras com autenticação JWT.', 'Arquitetura REST, verbos HTTP, middleware de autenticação e validação.', 'Desenvolvimento com testes automatizados no Postman/PHPUnit.', 'APIs totalmente documentadas, testadas e performáticas.', 'Avançar para consumo assíncrono via frontend moderno.', '10.0', '10.0');

        $this->seedDisciplinaTrimestre($peiMatheus2026, $discMat, 2, 'Dominar números complexos e polinômios.', 'Forma algébrica e trigonométrica de números complexos, teorema fundamental da álgebra.', 'Exemplos práticos aplicados a processamento de sinais e circuitos.', 'Resolução brilhante de equações polinomiais complexas.', 'Avançar para cálculo de determinantes de ordem n.', '10.0', '9.8');
        $this->seedDisciplinaTrimestre($peiMatheus2026, $discPort, 2, 'Analisar textos de divulgação científica e redação de artigos técnicos.', 'Divulgação científica, precisão terminológica e adequação de registro.', 'Orientação individualizada para escrita de resumos e palavras-chave.', 'Artigo técnico redigido com clareza, objetividade e excelência gramatical.', 'Submeter a congresso acadêmico.', '9.8', '9.5');
        $this->seedDisciplinaTrimestre($peiMatheus2026, $discProgWeb, 2, 'Integrar aplicações web com WebSockets para comunicação em tempo real.', 'Sockets, broadcast de eventos e gerenciamento de estado global.', 'Laboratório prático de desenvolvimento de painel de telemetria.', 'Aplicação em tempo real desenvolvida com baixa latência e alta escalabilidade.', 'Preparar documentação para entrega semestral.', '10.0', '10.0');

        // 6. trilha de auditoria completa com histórico de ações do sistema
        \App\Models\AuditLog::truncate();

        $auditEntries = [
            [
                'user' => $admin,
                'modulo' => 'Autenticação',
                'acao' => 'Login',
                'entidade_tipo' => null,
                'entidade_id' => null,
                'descricao' => 'Usuário autenticou-se com sucesso no painel administrativo.',
                'ip' => '10.0.1.15',
                'created_at' => now()->subDays(10)->setTime(8, 15, 20),
            ],
            [
                'user' => $admin,
                'modulo' => 'Deficiências',
                'acao' => 'Criação',
                'entidade_tipo' => 'Deficiencia',
                'entidade_id' => $defTea->id,
                'descricao' => 'Cadastrou a deficiência/necessidade [F84.0] Transtorno do Espectro Autista (TEA).',
                'ip' => '10.0.1.15',
                'dados_novos' => ['codigo' => 'F84.0', 'nome' => 'Transtorno do Espectro Autista (TEA)'],
                'created_at' => now()->subDays(10)->setTime(8, 30, 10),
            ],
            [
                'user' => $admin,
                'modulo' => 'Turmas',
                'acao' => 'Criação',
                'entidade_tipo' => 'Turma',
                'entidade_id' => $turmaInfo1->id,
                'descricao' => 'Criou a turma Técnico em Informática Integrado - 1º Ano A (2026) com 3 disciplinas e 3 docentes vinculados.',
                'ip' => '10.0.1.15',
                'dados_novos' => ['nome_curso' => 'Técnico em Informática Integrado - 1º Ano A', 'ano_letivo' => 2026],
                'created_at' => now()->subDays(9)->setTime(9, 45, 0),
            ],
            [
                'user' => $naiBeatriz,
                'modulo' => 'Alunos',
                'acao' => 'Criação',
                'entidade_tipo' => 'Aluno',
                'entidade_id' => $alunoLucas->id,
                'descricao' => 'Cadastrou o discente Lucas Ferreira da Silva (Matrícula: 2026100123) com vínculo à turma de Informática 1º Ano A.',
                'ip' => '10.0.2.40',
                'dados_novos' => ['nome' => 'Lucas Ferreira da Silva', 'matricula' => '2026100123', 'data_nascimento' => '15/04/2009'],
                'created_at' => now()->subDays(8)->setTime(10, 10, 35),
            ],
            [
                'user' => $naiBeatriz,
                'modulo' => 'PEIs',
                'acao' => 'Criação',
                'entidade_tipo' => 'Pei',
                'entidade_id' => $peiLucas->id,
                'descricao' => 'Elaborou o PEI PEI-2026-001 para o discente Lucas Ferreira da Silva (Ano Letivo: 2026).',
                'ip' => '10.0.2.40',
                'dados_novos' => ['codigo' => 'PEI-2026-001', 'aluno_id' => $alunoLucas->id, 'ano_letivo' => 2026],
                'created_at' => now()->subDays(7)->setTime(14, 20, 12),
            ],
            [
                'user' => $naiBeatriz,
                'modulo' => 'PEIs',
                'acao' => 'Atualização',
                'entidade_tipo' => 'Pei',
                'entidade_id' => $peiLucas->id,
                'descricao' => 'Atualizou a Ficha do PEI PEI-2026-001 (Identificações pedagógicas e 3 demandas de intervenção com responsáveis).',
                'ip' => '10.0.2.40',
                'dados_novos' => ['habilidades_qtd' => 2, 'dificuldades_qtd' => 2, 'intervencoes_qtd' => 3],
                'created_at' => now()->subDays(6)->setTime(11, 05, 50),
            ],
            [
                'user' => $profCarlos,
                'modulo' => 'Autenticação',
                'acao' => 'Login',
                'entidade_tipo' => null,
                'entidade_id' => null,
                'descricao' => 'Docente autenticou-se para preenchimento de planejamentos pedagógicos.',
                'ip' => '192.168.10.55',
                'created_at' => now()->subDays(5)->setTime(15, 30, 0),
            ],
            [
                'user' => $profCarlos,
                'modulo' => 'PEIs',
                'acao' => 'Atualização',
                'entidade_tipo' => 'Pei',
                'entidade_id' => $peiLucas->id,
                'descricao' => 'Atualizou o planejamento e avaliações da disciplina Matemática e Raciocínio Lógico (1º Trimestre) no PEI PEI-2026-001.',
                'ip' => '192.168.10.55',
                'dados_anteriores' => ['status' => 'Pendente', 'planejamentos_qtd' => 0],
                'dados_novos' => ['disciplina' => 'Matemática e Raciocínio Lógico', 'trimestre' => 1, 'grau_autonomia' => ['pouco_suporte', 'alta_compreensao'], 'planejamentos_qtd' => 1],
                'created_at' => now()->subDays(5)->setTime(16, 12, 44),
            ],
            [
                'user' => $naiRodrigo,
                'modulo' => 'PEIs',
                'acao' => 'Criação',
                'entidade_tipo' => 'Pei',
                'entidade_id' => $peiAna->id,
                'descricao' => 'Elaborou o PEI PEI-2026-002 para a discente Ana Clara Ribeiro de Menezes (Administração 1º Ano).',
                'ip' => '10.0.2.42',
                'dados_novos' => ['codigo' => 'PEI-2026-002', 'aluno_id' => $alunaAna->id, 'ano_letivo' => 2026],
                'created_at' => now()->subDays(4)->setTime(9, 0, 18),
            ],
            [
                'user' => $apoioLarissa,
                'modulo' => 'PEIs',
                'acao' => 'Atualização',
                'entidade_tipo' => 'Pei',
                'entidade_id' => $peiAna->id,
                'descricao' => 'Atualizou as ações da Equipe de Apoio "Intérprete de Libras e Acessibilidade Comunicacional" para o 1º Trimestre no PEI PEI-2026-002.',
                'ip' => '10.0.2.48',
                'dados_novos' => ['equipe' => 'Intérprete de Libras', 'trimestre' => 1, 'tempo' => 'Horário integral das aulas'],
                'created_at' => now()->subDays(3)->setTime(14, 45, 10),
            ],
            [
                'user' => $admin,
                'modulo' => 'Usuários',
                'acao' => 'Atualização',
                'entidade_tipo' => 'User',
                'entidade_id' => $profMariana->id,
                'descricao' => 'Atualizou as atribuições e o perfil da usuária Profa. Mariana Souza.',
                'ip' => '10.0.1.15',
                'dados_anteriores' => ['cargo' => 'Professor', 'is_admin' => 'Não'],
                'dados_novos' => ['cargo' => 'Professor', 'is_admin' => 'Não'],
                'created_at' => now()->subDays(2)->setTime(16, 20, 0),
            ],
            [
                'user' => $profRicardo,
                'modulo' => 'PEIs',
                'acao' => 'Atualização',
                'entidade_tipo' => 'Pei',
                'entidade_id' => $peiEnzo->id,
                'descricao' => 'Atualizou a Ficha do Aluno no PEI PEI-2026-003 (Enzo Gabriel Nogueira).',
                'ip' => '192.168.10.82',
                'dados_novos' => ['caracteristicas_discente' => 'Muito dinâmico, criativo em soluções de software prático.'],
                'created_at' => now()->subDays(1)->setTime(10, 50, 22),
            ],
            [
                'user' => $admin,
                'modulo' => 'Autenticação',
                'acao' => 'Login',
                'entidade_tipo' => null,
                'entidade_id' => null,
                'descricao' => 'Usuário autenticou-se para auditoria geral do campus.',
                'ip' => '10.0.1.15',
                'created_at' => now()->subHours(2),
            ],
        ];

        foreach ($auditEntries as $entry) {
            \App\Models\AuditLog::create([
                'user_id' => $entry['user']->id,
                'user_name' => $entry['user']->name,
                'user_email' => $entry['user']->email,
                'user_cargo' => $entry['user']->cargo,
                'modulo' => $entry['modulo'],
                'acao' => $entry['acao'],
                'entidade_tipo' => $entry['entidade_tipo'] ?? null,
                'entidade_id' => $entry['entidade_id'] ?? null,
                'descricao' => $entry['descricao'],
                'ip_address' => $entry['ip'],
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36',
                'dados_anteriores' => $entry['dados_anteriores'] ?? null,
                'dados_novos' => $entry['dados_novos'] ?? null,
                'created_at' => $entry['created_at'],
                'updated_at' => $entry['created_at'],
            ]);
        }

        // calendário escolar com os períodos trimestrais dos anos letivos
        AnoLetivo::updateOrCreate(['ano' => 2025], [
            'concluido' => true,
            'trimestre1_inicio' => '2025-02-10',
            'trimestre1_fim' => '2025-05-16',
            'trimestre2_inicio' => '2025-05-19',
            'trimestre2_fim' => '2025-08-29',
            'trimestre3_inicio' => '2025-09-01',
            'trimestre3_fim' => '2025-12-19',
        ]);
        AnoLetivo::updateOrCreate(['ano' => 2026], [
            'concluido' => false,
            'trimestre1_inicio' => '2026-02-09',
            'trimestre1_fim' => '2026-05-15',
            'trimestre2_inicio' => '2026-05-18',
            'trimestre2_fim' => '2026-08-28',
            'trimestre3_inicio' => '2026-09-01',
            'trimestre3_fim' => '2026-12-18',
        ]);
    }

    /**
     * cria o registro de acompanhamento, planejamento pedagógico e avaliações de uma disciplina para um trimestre.
     */
    private function seedDisciplinaTrimestre(Pei $pei, Disciplina $disciplina, int $trimestre, string $objetivos, string $conteudos, string $metodologias, string $avaliacaoResultados, string $sugestoes, string $nota1 = '9.0', string $nota2 = '9.5'): PeiDisciplina
    {
        $peiDisc = PeiDisciplina::create([
            'pei_id' => $pei->id,
            'disciplina_id' => $disciplina->id,
            'trimestre' => $trimestre,
            'grau_autonomia' => ['pouco_suporte', 'alta_compreensao'],
            'especifique_outros' => null,
        ]);

        PeiDisciplinaPlanejamento::create([
            'pei_disciplina_id' => $peiDisc->id,
            'objetivos' => $objetivos,
            'conteudos' => $conteudos,
            'metodologias_recursos' => $metodologias,
            'tempo' => $trimestre === 1 ? 'Fevereiro a Abril' : ($trimestre === 2 ? 'Maio a Agosto' : 'Setembro a Dezembro'),
            'avaliacao_resultados' => $avaliacaoResultados,
            'sugestoes_aproveitamento' => $sugestoes,
            'ordem' => 0,
        ]);

        PeiDisciplinaAvaliacao::create([
            'pei_disciplina_id' => $peiDisc->id,
            'tipo' => 'prova_escrita',
            'adotado' => true,
            'valor_1' => '10.0', 'nota_1' => $nota1,
            'valor_2' => '10.0', 'nota_2' => $nota2,
        ]);

        PeiDisciplinaAvaliacao::create([
            'pei_disciplina_id' => $peiDisc->id,
            'tipo' => 'trabalhos',
            'adotado' => true,
            'valor_1' => '10.0', 'nota_1' => '10.0',
        ]);

        return $peiDisc;
    }
}
