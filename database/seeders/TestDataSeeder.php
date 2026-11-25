<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\AuditLog;
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

class TestDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * seeder completo e coerente para testes e demonstração de todos os módulos do sigepei.
     *
     * execução: php artisan db:seed --class=TestDataSeeder
     */
    public function run(): void
    {
        // garante o usuário administrador padrão
        $this->call(DatabaseSeeder::class);

        $password = Hash::make('password');
        $verifiedAt = now();

        $admin = User::where('email', 'admin@sigepei.edu.br')->firstOrFail();

        // =====================================================================
        // 1. calendário escolar (anos letivos)
        // =====================================================================
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

        // =====================================================================
        // 2. usuários (nai, professores, equipe de apoio e inativo)
        // =====================================================================
        $naiBeatriz = $this->upsertUser('nai.beatriz@sigepei.edu.br', 'Beatriz Mendonça (Pedagoga NAI)', 'NAI', false, true, $password, $verifiedAt);
        $naiRodrigo = $this->upsertUser('nai.rodrigo@sigepei.edu.br', 'Rodrigo Albuquerque (Psicólogo Escolar NAI)', 'NAI', false, true, $password, $verifiedAt);

        $profCarlos = $this->upsertUser('prof.carlos@sigepei.edu.br', 'Prof. Carlos Eduardo (Matemática)', 'Professor', false, true, $password, $verifiedAt);
        $profMariana = $this->upsertUser('prof.mariana@sigepei.edu.br', 'Profª Mariana Souza (Língua Portuguesa)', 'Professor', false, true, $password, $verifiedAt);
        $profRicardo = $this->upsertUser('prof.ricardo@sigepei.edu.br', 'Prof. Ricardo Santos (Informática)', 'Professor', false, true, $password, $verifiedAt);
        $profJuliana = $this->upsertUser('prof.juliana@sigepei.edu.br', 'Profª Juliana Costa (Física e Biologia)', 'Professor', false, true, $password, $verifiedAt);
        $profFernando = $this->upsertUser('prof.fernando@sigepei.edu.br', 'Prof. Fernando Alves (História e Gestão)', 'Professor', false, true, $password, $verifiedAt);
        $profPatricia = $this->upsertUser('prof.patricia@sigepei.edu.br', 'Profª Patrícia Lima (Substituta)', 'Professor', false, true, $password, $verifiedAt);

        $apoioLarissa = $this->upsertUser('apoio.larissa@sigepei.edu.br', 'Larissa Viana (Intérprete de Libras)', 'Outros', false, true, $password, $verifiedAt);
        $apoioGabriel = $this->upsertUser('apoio.gabriel@sigepei.edu.br', 'Gabriel Toledo (Monitor de Tecnologia Assistiva)', 'Outros', false, true, $password, $verifiedAt);

        // usuário inativo para testes de bloqueio de autenticação e filtros
        $this->upsertUser('inativo@sigepei.edu.br', 'Prof. Marcelo Dias (Inativo)', 'Professor', false, false, $password, $verifiedAt);

        // =====================================================================
        // 3. deficiências e necessidades educacionais específicas
        // =====================================================================
        $defTea = Deficiencia::updateOrCreate(['codigo' => 'F84.0'], ['nome' => 'Transtorno do Espectro Autista (TEA)', 'is_active' => true]);
        $defBaixaVisao = Deficiencia::updateOrCreate(['codigo' => 'H54.0'], ['nome' => 'Baixa Visão / Cegueira', 'is_active' => true]);
        $defAuditiva = Deficiencia::updateOrCreate(['codigo' => 'H90.0'], ['nome' => 'Deficiência Auditiva / Surdez', 'is_active' => true]);
        $defTdah = Deficiencia::updateOrCreate(['codigo' => 'F90.0'], ['nome' => 'Transtorno do Déficit de Atenção e Hiperatividade (TDAH)', 'is_active' => true]);
        $defFisica = Deficiencia::updateOrCreate(['codigo' => 'G80.0'], ['nome' => 'Paralisia Cerebral / Deficiência Física Motora', 'is_active' => true]);
        $defDislexia = Deficiencia::updateOrCreate(['codigo' => 'F81.0'], ['nome' => 'Transtorno Específico de Leitura / Dislexia', 'is_active' => true]);
        $defIntelectual = Deficiencia::updateOrCreate(['codigo' => 'F70.0'], ['nome' => 'Deficiência Intelectual', 'is_active' => true]);
        $defAltasHab = Deficiencia::updateOrCreate(['codigo' => 'AH/SD'], ['nome' => 'Altas Habilidades / Superdotação', 'is_active' => true]);
        Deficiencia::updateOrCreate(['codigo' => 'Z99.0'], ['nome' => 'Registro Legado Descontinuado', 'is_active' => false]);

        // =====================================================================
        // 4. disciplinas
        // =====================================================================
        $discMat = Disciplina::updateOrCreate(['codigo' => 'MAT-101'], ['nome' => 'Matemática e Raciocínio Lógico', 'is_active' => true]);
        $discPort = Disciplina::updateOrCreate(['codigo' => 'POR-101'], ['nome' => 'Língua Portuguesa e Literatura', 'is_active' => true]);
        $discProgWeb = Disciplina::updateOrCreate(['codigo' => 'INF-201'], ['nome' => 'Programação Web e Interfaces', 'is_active' => true]);
        $discBD = Disciplina::updateOrCreate(['codigo' => 'INF-202'], ['nome' => 'Modelagem e Banco de Dados', 'is_active' => true]);
        $discFisica = Disciplina::updateOrCreate(['codigo' => 'FIS-101'], ['nome' => 'Física Aplicada', 'is_active' => true]);
        $discHistoria = Disciplina::updateOrCreate(['codigo' => 'HIS-101'], ['nome' => 'História Geral e do Brasil', 'is_active' => true]);
        $discBio = Disciplina::updateOrCreate(['codigo' => 'BIO-101'], ['nome' => 'Biologia Geral', 'is_active' => true]);
        $discAdm = Disciplina::updateOrCreate(['codigo' => 'ADM-101'], ['nome' => 'Fundamentos de Administração', 'is_active' => true]);
        Disciplina::updateOrCreate(['codigo' => 'OPT-001'], ['nome' => 'Optativa Desativada', 'is_active' => false]);

        // =====================================================================
        // 5. turmas e vínculos com disciplinas e docentes
        // =====================================================================
        $turmaInfo1 = Turma::updateOrCreate(
            ['nome_curso' => 'Técnico em Informática Integrado - 1º Ano A', 'ano_letivo' => 2026],
            ['is_active' => true]
        );
        $this->vincularDisciplinaTurma($turmaInfo1, $discMat, [$profCarlos->id => false]);
        $this->vincularDisciplinaTurma($turmaInfo1, $discPort, [$profMariana->id => false]);
        $this->vincularDisciplinaTurma($turmaInfo1, $discProgWeb, [$profRicardo->id => false, $profPatricia->id => true]);

        $turmaInfo2 = Turma::updateOrCreate(
            ['nome_curso' => 'Técnico em Informática Integrado - 2º Ano B', 'ano_letivo' => 2026],
            ['is_active' => true]
        );
        $this->vincularDisciplinaTurma($turmaInfo2, $discBD, [$profRicardo->id => false]);
        $this->vincularDisciplinaTurma($turmaInfo2, $discFisica, [$profJuliana->id => false]);
        $this->vincularDisciplinaTurma($turmaInfo2, $discMat, [$profCarlos->id => false, $profPatricia->id => true]);

        $turmaAdm1 = Turma::updateOrCreate(
            ['nome_curso' => 'Técnico em Administração Integrado - 1º Ano', 'ano_letivo' => 2026],
            ['is_active' => true]
        );
        $this->vincularDisciplinaTurma($turmaAdm1, $discMat, [$profCarlos->id => false]);
        $this->vincularDisciplinaTurma($turmaAdm1, $discPort, [$profMariana->id => false]);
        $this->vincularDisciplinaTurma($turmaAdm1, $discHistoria, [$profFernando->id => false]);
        $this->vincularDisciplinaTurma($turmaAdm1, $discAdm, [$profFernando->id => false]);

        $turmaEdif1 = Turma::updateOrCreate(
            ['nome_curso' => 'Técnico em Edificações Integrado - 1º Ano', 'ano_letivo' => 2026],
            ['is_active' => true]
        );
        $this->vincularDisciplinaTurma($turmaEdif1, $discFisica, [$profJuliana->id => false]);
        $this->vincularDisciplinaTurma($turmaEdif1, $discBio, [$profJuliana->id => false]);
        $this->vincularDisciplinaTurma($turmaEdif1, $discMat, [$profCarlos->id => false]);

        // turma histórica de 2025 (para histórico longitudinal)
        $turmaInfo1_2025 = Turma::updateOrCreate(
            ['nome_curso' => 'Técnico em Informática Integrado - 1º Ano B (2025)', 'ano_letivo' => 2025],
            ['is_active' => true]
        );
        $this->vincularDisciplinaTurma($turmaInfo1_2025, $discMat, [$profCarlos->id => false]);
        $this->vincularDisciplinaTurma($turmaInfo1_2025, $discPort, [$profMariana->id => false]);
        $this->vincularDisciplinaTurma($turmaInfo1_2025, $discProgWeb, [$profRicardo->id => false]);

        // turma inativa para testes de filtro
        Turma::updateOrCreate(
            ['nome_curso' => 'Técnico em Química Subsequente - Encerrada', 'ano_letivo' => 2024],
            ['is_active' => false]
        );

        // =====================================================================
        // 6. alunos e peis (cenários completos, parciais, pendentes e históricos)
        // =====================================================================

        // ---------------------------------------------------------------------
        // cenário 1: lucas ferreira (tea + baixa visão) — pei completo nos 3 trimestres
        // ---------------------------------------------------------------------
        $alunoLucas = Aluno::updateOrCreate(
            ['matricula' => '2026100101'],
            ['nome' => 'Lucas Ferreira da Silva', 'data_nascimento' => '2009-04-15', 'is_active' => true]
        );
        $alunoLucas->deficiencias()->sync([$defTea->id, $defBaixaVisao->id]);
        $alunoLucas->turmas()->sync([$turmaInfo1->id => ['ano_letivo' => 2026, 'is_atual' => true]]);

        $peiLucas = Pei::updateOrCreate(
            ['codigo' => 'PEI-2026-001'],
            [
                'aluno_id' => $alunoLucas->id,
                'turma_id' => $turmaInfo1->id,
                'ano_letivo' => 2026,
                'data_elaboracao' => '2026-02-12',
                'diagnostico' => '[F84.0] Transtorno do Espectro Autista (Nível 1 de Suporte), [H54.0] Baixa Visão Moderada',
                'tempo_livre' => 'Desenvolve jogos simples em Python, joga xadrez online e ouve música instrumental com fones abafadores.',
                'pratica_esporte' => 'Pratica natação adaptada duas vezes por semana.',
                'caracteristicas_discente' => 'Estudante analítico, calmo e metódico; sensível a ruídos intensos e mudanças abruptas de rotina.',
                'organizacao_e_prazos' => 'Muito organizado com agendas digitais; cumpre prazos rigorosamente quando as instruções são objetivas.',
                'dificuldade_interacao_social' => 'Prefere trabalhar em duplas ou trios definidos previamente; demonstra boa comunicação técnica.',
                'sugestoes_recursos_estrategias' => 'Materiais digitais com alto contraste (tema escuro) e fonte >= 18pt; tempo adicional de 50% nas avaliações; permissão para fones abafadores.',
                'status' => 'Ativo',
                'is_active' => true,
            ]
        );

        $this->seedFichaPei($peiLucas, [
            'responsaveis_ficha' => [
                ['user_id' => $admin->id],
                ['user_id' => $naiBeatriz->id],
                ['nome_externo' => 'Dra. Helena Martins (Terapeuta Ocupacional Externa)'],
            ],
            'responsaveis_nai' => [
                1 => [['user_id' => $naiBeatriz->id]],
                2 => [['user_id' => $naiBeatriz->id], ['user_id' => $naiRodrigo->id]],
                3 => [['user_id' => $naiBeatriz->id]],
            ],
            'identificacoes' => [
                'habilidade' => [
                    'Excelente raciocínio lógico-matemático e facilidade com algoritmos.',
                    'Alta concentração em atividades estruturadas com etapas claras.',
                ],
                'dificuldade' => [
                    'Fadiga visual em textos impressos com fonte reduzida ou baixo contraste.',
                    'Desconforto sensorial em salas com alto nível de ruído.',
                ],
                'dificultador_interno' => [
                    'Slides projetados com excesso de texto sem disponibilização prévia em PDF.',
                ],
                'facilitador_interno' => [
                    'Laboratórios com monitores de 24 polegadas e suporte ativo do NAI.',
                ],
                'dificultador_externo' => [
                    'Deslocamento em transporte público lotado no horário de entrada.',
                ],
                'facilitador_externo' => [
                    'Família participativa e acompanhamento terapêutico contínuo.',
                ],
            ],
            'intervencoes' => [
                [
                    'demanda' => 'Acessibilidade visual em provas e materiais didáticos',
                    'encaminhamento_estrategia' => 'Enviar apostilas em PDF acessível com 48h de antecedência e imprimir provas em fonte 18pt.',
                    'responsavel' => 'Corpo docente e NAI',
                ],
                [
                    'demanda' => 'Regulação sensorial em sala e avaliações',
                    'encaminhamento_estrategia' => 'Permitir uso de fones abafadores e aplicação de provas em sala reservada com tempo adicional.',
                    'responsavel' => 'NAI e Coordenação de Curso',
                ],
            ],
            'nai_atividades' => [
                1 => [[
                    'objetivos' => 'Acolher o estudante, alinhar adaptações visuais com todos os docentes e configurar estações de laboratório.',
                    'recursos_acoes' => 'Reunião pedagógica com docentes da turma e instalação de temas de alto contraste nos computadores.',
                    'tempo' => '1º Trimestre (Quinzenal)',
                    'avaliacao_resultados' => 'Adaptação excelente; estudante demonstrou segurança e alto engajamento nas aulas.',
                    'sugestoes_aproveitamento' => 'Manter rotina de envio antecipado dos roteiros de aula.',
                ]],
                2 => [[
                    'objetivos' => 'Acompanhar desenvolvimento de projetos em equipe e autonomia nos recursos de tecnologia assistiva.',
                    'recursos_acoes' => 'Encontros mensais de escuta com o aluno e feedback com professores técnicos.',
                    'tempo' => '2º Trimestre (Mensal)',
                    'avaliacao_resultados' => 'Evolução expressiva na interação em duplas e manutenção de médias superiores a 9,0.',
                    'sugestoes_aproveitamento' => 'Incentivar participação na Mostra Tecnológica do campus.',
                ]],
                3 => [[
                    'objetivos' => 'Consolidar autonomia acadêmica e avaliar resultados anuais para transição ao 2º ano.',
                    'recursos_acoes' => 'Avaliação conjunta NAI, família e coordenação; relatório síntese anual.',
                    'tempo' => '3º Trimestre',
                    'avaliacao_resultados' => 'Objetivos plenamente alcançados em todas as áreas curriculares.',
                    'sugestoes_aproveitamento' => 'Replicar protocolo de acessibilidade visual no PEI do próximo ano letivo.',
                ]],
            ],
        ]);

        // duas equipes de apoio para o discente lucas
        PeiApoio::where('pei_id', $peiLucas->id)->delete();
        $this->seedEquipeApoio($peiLucas, 'Monitoria de Tecnologia Assistiva', 0, [
            1 => [
                'responsaveis' => [['user_id' => $apoioGabriel->id]],
                'atividades' => [[
                    'objetivos' => 'Configurar ampliador de tela e tema de alto contraste no VS Code e navegador.',
                    'recursos_acoes' => 'Sessões presenciais de 2h semanais no laboratório de informática.',
                    'tempo' => '2h semanais',
                    'avaliacao_resultados' => 'Ambiente totalmente personalizado e domínio pleno dos atalhos de teclado.',
                    'sugestoes_aproveitamento' => 'Documentar configurações em perfil portátil para uso em qualquer máquina.',
                ]],
            ],
            2 => [
                'responsaveis' => [['user_id' => $apoioGabriel->id]],
                'atividades' => [[
                    'objetivos' => 'Apoiar testes de contraste e acessibilidade WCAG no projeto web da turma.',
                    'recursos_acoes' => 'Revisão conjunta com ferramentas de auditoria de acessibilidade.',
                    'tempo' => '2h semanais',
                    'avaliacao_resultados' => 'Projeto entregue com 100% de conformidade de contraste e navegação por teclado.',
                    'sugestoes_aproveitamento' => 'Convidar o aluno para atuar como multiplicador de acessibilidade.',
                ]],
            ],
            3 => [
                'responsaveis' => [['user_id' => $apoioGabriel->id]],
                'atividades' => [[
                    'objetivos' => 'Preparar documentação de transição das ferramentas assistivas para o 2º ano.',
                    'recursos_acoes' => 'Checklist de softwares e extensões utilizadas ao longo do ano.',
                    'tempo' => '1h semanal',
                    'avaliacao_resultados' => 'Autonomia consolidada; dispensa mediação contínua na configuração dos recursos.',
                    'sugestoes_aproveitamento' => 'Realizar checagem pontual no início de 2027.',
                ]],
            ],
        ]);

        $this->seedEquipeApoio($peiLucas, 'Apoio Psicológico Escolar', 1, [
            1 => [
                'responsaveis' => [['user_id' => $naiRodrigo->id]],
                'atividades' => [[
                    'objetivos' => 'Desenvolver estratégias de autorregulação diante de estímulos sonoros intensos.',
                    'recursos_acoes' => 'Atendimentos quinzenais individuais e combinado de pausas breves com os docentes.',
                    'tempo' => 'Quinzenal',
                    'avaliacao_resultados' => 'Redução significativa de episódios de sobrecarga sensorial.',
                    'sugestoes_aproveitamento' => 'Manter canal aberto com a família.',
                ]],
            ],
            2 => [
                'responsaveis' => [['user_id' => $naiRodrigo->id]],
                'atividades' => [[
                    'objetivos' => 'Fortalecer habilidades de comunicação assertiva em trabalhos em grupo.',
                    'recursos_acoes' => 'Roteiros de divisão de tarefas e mediação pontual com colegas.',
                    'tempo' => 'Mensal',
                    'avaliacao_resultados' => 'Participação mais ativa e segura nas apresentações em dupla.',
                    'sugestoes_aproveitamento' => 'Estimular protagonismo na apresentação da feira científica.',
                ]],
            ],
            3 => [
                'responsaveis' => [['user_id' => $naiRodrigo->id]],
                'atividades' => [[
                    'objetivos' => 'Avaliar bem-estar socioemocional ao encerramento do ano letivo.',
                    'recursos_acoes' => 'Entrevista de encerramento com o estudante e devolutiva à família.',
                    'tempo' => 'Mensal',
                    'avaliacao_resultados' => 'Estudante relata pertencimento à turma e satisfação com o curso.',
                    'sugestoes_aproveitamento' => 'Acompanhamento sob demanda no ano seguinte.',
                ]],
            ],
        ]);

        // disciplinas do discente lucas nos 3 trimestres com campos estruturados
        PeiDisciplina::where('pei_id', $peiLucas->id)->delete();
        foreach ([1, 2, 3] as $tri) {
            $this->seedDisciplinaEstruturada($peiLucas, $discMat, $tri, [
                'grau_autonomia' => ['pouco_suporte', 'alta_compreensao'],
                'planejamentos' => [[
                    'objetivos' => match ($tri) {
                        1 => 'Compreender funções polinomiais de 1º e 2º grau e interpretar gráficos cartesianos.',
                        2 => 'Aplicar razões trigonométricas no triângulo retângulo e operações com matrizes.',
                        3 => 'Resolver problemas de análise combinatória e probabilidade básica.',
                    },
                    'conteudos' => match ($tri) {
                        1 => 'Funções afim e quadrática, zeros da função, vértice e leitura gráfica.',
                        2 => 'Seno, cosseno, tangente, matrizes e sistemas lineares.',
                        3 => 'Princípio fundamental da contagem, permutações e probabilidade.',
                    },
                    'estrategias_ensino' => [
                        'Resolução de problemas',
                        'Disponibilidade de material de estudo',
                        'Utilização de diferentes formas de apresentação do conteúdo',
                    ],
                    'estrategias_ensino_outros' => 'Uso do GeoGebra em modo de alto contraste.',
                    'estrategias_avaliacao' => [
                        'Uso de material ampliado',
                        'Tempo adicional para realização da avaliação',
                        'Realização da avaliação em ambiente separado',
                    ],
                    'estrategias_avaliacao_outros' => 'Fonte Arial 18pt com espaçamento 1,5.',
                    'tempo_opcao' => 'Todo o trimestre',
                    'avaliacao_desempenho' => [
                        'Atingiu o objetivo proposto com autonomia.',
                        'Demonstrou facilidade na compreensão e aplicação dos conteúdos.',
                    ],
                    'avaliacao_desempenho_outros' => 'Excelente precisão nos cálculos e participação constante.',
                    'sugestoes_aproveitamento' => 'Propor questões de olimpíada de matemática como enriquecimento curricular.',
                ]],
                'avaliacoes' => [
                    ['tipo' => 'prova_escrita', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '9.5', 'valor_2' => '10', 'nota_2' => '9.0'],
                    ['tipo' => 'trabalhos', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '10.0'],
                ],
            ]);

            $this->seedDisciplinaEstruturada($peiLucas, $discPort, $tri, [
                'grau_autonomia' => ['pouco_suporte', 'alta_compreensao'],
                'planejamentos' => [[
                    'objetivos' => match ($tri) {
                        1 => 'Interpretar gêneros textuais técnicos e argumentativos com clareza.',
                        2 => 'Produzir relatórios técnicos e resenhas críticas com coesão e coerência.',
                        3 => 'Analisar obras literárias brasileiras e elaborar redação dissertativo-argumentativa.',
                    },
                    'conteudos' => match ($tri) {
                        1 => 'Tipologias textuais, compreensão leitora e coesão referencial.',
                        2 => 'Redação técnica, documentação de projetos e concordância.',
                        3 => 'Literatura moderna brasileira e estrutura dissertativa.',
                    },
                    'estrategias_ensino' => [
                        'Aula expositiva',
                        'Disponibilização de materiais de apoio/complementares.',
                        'Orientações passo a passo',
                    ],
                    'estrategias_avaliacao' => [
                        'Uso de material ampliado',
                        'Uso de material acessível/adaptado',
                        'Tempo adicional para realização da avaliação',
                    ],
                    'tempo_opcao' => 'Todo o trimestre',
                    'avaliacao_desempenho' => [
                        'Atingiu o objetivo proposto com autonomia.',
                        'Apresentou avanço na compreensão e assimilação dos conteúdos.',
                    ],
                    'sugestoes_aproveitamento' => 'Continuar envio antecipado de textos longos em PDF acessível.',
                ]],
                'avaliacoes' => [
                    ['tipo' => 'prova_escrita', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '9.0'],
                    ['tipo' => 'trabalhos', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '9.5'],
                ],
            ]);

            $this->seedDisciplinaEstruturada($peiLucas, $discProgWeb, $tri, [
                'grau_autonomia' => ['pouco_suporte', 'alta_compreensao'],
                'planejamentos' => [[
                    'objetivos' => match ($tri) {
                        1 => 'Construir páginas web semânticas em HTML5 e estilização acessível em CSS3.',
                        2 => 'Implementar layouts responsivos com Flexbox e CSS Grid seguindo diretrizes WCAG.',
                        3 => 'Desenvolver interatividade no navegador utilizando JavaScript moderno e manipulação do DOM.',
                    },
                    'conteudos' => match ($tri) {
                        1 => 'Estrutura HTML5, tags semânticas, formulários acessíveis e seletores CSS.',
                        2 => 'Flexbox, CSS Grid, media queries e contraste de cores.',
                        3 => 'Variáveis, funções, eventos do DOM e consumo básico de JSON.',
                    },
                    'estrategias_ensino' => [
                        'Atividades práticas/experimentais',
                        'Exemplificação/modelagem',
                        'Divisão da atividade em etapas',
                    ],
                    'estrategias_avaliacao' => [
                        'Uso de computador ou outro recurso tecnológico',
                        'Utilização de recursos de tecnologia assistiva',
                    ],
                    'tempo_opcao' => 'Todo o trimestre',
                    'avaliacao_desempenho' => [
                        'Atingiu o objetivo proposto com autonomia.',
                        'Desenvolveu maior autonomia na realização das atividades.',
                    ],
                    'sugestoes_aproveitamento' => 'Incentivar participação em projetos de extensão voltados à acessibilidade digital.',
                ]],
                'avaliacoes' => [
                    ['tipo' => 'prova_pratica', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '10.0', 'valor_2' => '10', 'nota_2' => '9.8'],
                    ['tipo' => 'trabalhos', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '10.0'],
                ],
            ]);
        }

        // ---------------------------------------------------------------------
        // cenário 2: ana clara (surdez/libras + altas habilidades)
        // 1º e 2º trimestres completos; 3º trimestre parcial com pendências
        // ---------------------------------------------------------------------
        $alunaAna = Aluno::updateOrCreate(
            ['matricula' => '2026100202'],
            ['nome' => 'Ana Clara Ribeiro de Menezes', 'data_nascimento' => '2009-08-22', 'is_active' => true]
        );
        $alunaAna->deficiencias()->sync([$defAuditiva->id, $defAltasHab->id]);
        $alunaAna->turmas()->sync([$turmaAdm1->id => ['ano_letivo' => 2026, 'is_atual' => true]]);

        $peiAna = Pei::updateOrCreate(
            ['codigo' => 'PEI-2026-002'],
            [
                'aluno_id' => $alunaAna->id,
                'turma_id' => $turmaAdm1->id,
                'ano_letivo' => 2026,
                'data_elaboracao' => '2026-02-16',
                'diagnostico' => '[H90.0] Deficiência Auditiva Bilateral Profunda (usuária de Libras), [AH/SD] Altas Habilidades em Artes Visuais e Gestão',
                'tempo_livre' => 'Ilustração digital, edição de vídeo legendado e leitura de histórias em quadrinhos.',
                'pratica_esporte' => 'Voleibol escolar.',
                'caracteristicas_discente' => 'Proativa, comunicativa em Libras, excelente compreensão visual e liderança positiva em equipe.',
                'organizacao_e_prazos' => 'Pontual e muito organizada na entrega das atividades.',
                'dificuldade_interacao_social' => 'Boa integração com colegas; ensina sinais básicos de Libras à turma.',
                'sugestoes_recursos_estrategias' => 'Presença da intérprete de Libras em todas as aulas; vídeos sempre legendados; correção de língua portuguesa como L2.',
                'status' => 'Ativo',
                'is_active' => true,
            ]
        );

        $this->seedFichaPei($peiAna, [
            'responsaveis_ficha' => [
                ['user_id' => $naiRodrigo->id],
                ['user_id' => $apoioLarissa->id],
            ],
            'responsaveis_nai' => [
                1 => [['user_id' => $naiRodrigo->id]],
                2 => [['user_id' => $naiRodrigo->id]],
                3 => [['user_id' => $naiRodrigo->id]],
            ],
            'identificacoes' => [
                'habilidade' => [
                    'Fluência plena em Libras e destacada capacidade de síntese visual.',
                    'Facilidade em planejamento e organização de projetos empresariais.',
                ],
                'dificuldade' => [
                    'Vídeos didáticos sem legenda ou explicações rápidas sem pausas para interpretação.',
                ],
                'facilitador_interno' => [
                    'Atuação contínua da Tradutora/Intérprete de Libras em sala.',
                ],
            ],
            'intervencoes' => [
                [
                    'demanda' => 'Acessibilidade linguística e avaliação em L2',
                    'encaminhamento_estrategia' => 'Garantir intérprete em todas as aulas e aplicar critérios de correção de Português como segunda língua.',
                    'responsavel' => 'Intérprete de Libras e Docentes',
                ],
            ],
            'nai_atividades' => [
                1 => [[
                    'objetivos' => 'Organizar escala de interpretação em Libras e oficina de sensibilização com a turma.',
                    'recursos_acoes' => 'Oficina introdutória de Libras e alinhamento com todos os docentes.',
                    'tempo' => '1º Trimestre',
                    'avaliacao_resultados' => 'Turma engajada e comunicação fluida desde a primeira semana.',
                    'sugestoes_aproveitamento' => 'Ampliar glossário de sinais técnicos de Administração.',
                ]],
                2 => [[
                    'objetivos' => 'Acompanhar desenvolvimento de plano de negócios acessível na disciplina técnica.',
                    'recursos_acoes' => 'Reuniões mensais de monitoramento com a estudante e docente de Administração.',
                    'tempo' => '2º Trimestre',
                    'avaliacao_resultados' => 'Projeto de destaque, selecionado para apresentação institucional.',
                    'sugestoes_aproveitamento' => 'Submeter projeto para edital de iniciação científica júnior.',
                ]],
                // 3º trimestre intencionalmente sem atividade do NAI (pendência do NAI no 3º tri)
            ],
        ]);

        PeiApoio::where('pei_id', $peiAna->id)->delete();
        $this->seedEquipeApoio($peiAna, 'Tradução e Interpretação em Libras', 0, [
            1 => [
                'responsaveis' => [['user_id' => $apoioLarissa->id]],
                'atividades' => [[
                    'objetivos' => 'Garantir mediação linguística integral em sala de aula e elaboração de glossário técnico.',
                    'recursos_acoes' => 'Interpretação simultânea e gravação de sinais-termo em vídeo.',
                    'tempo' => 'Integral',
                    'avaliacao_resultados' => 'Acompanhamento pleno de todas as aulas e avaliações.',
                    'sugestoes_aproveitamento' => 'Compartilhar glossário com outros estudantes surdos do campus.',
                ]],
            ],
            2 => [
                'responsaveis' => [['user_id' => $apoioLarissa->id]],
                'atividades' => [[
                    'objetivos' => 'Apoiar apresentações orais/sinalizadas nos seminários de Administração e História.',
                    'recursos_acoes' => 'Preparação prévia de roteiro bilíngue Libras/Português.',
                    'tempo' => 'Integral',
                    'avaliacao_resultados' => 'Apresentações de excelência com reconhecimento da banca.',
                    'sugestoes_aproveitamento' => 'Manter preparação antecipada dos seminários.',
                ]],
            ],
            3 => [
                // responsável definido no 3º trimestre sem atividade registrada (pendência)
                'responsaveis' => [['user_id' => $apoioLarissa->id]],
                'atividades' => [],
            ],
        ]);

        PeiDisciplina::where('pei_id', $peiAna->id)->delete();
        // 1º e 2º trimestres completos em todas as 4 disciplinas da turma Adm 1º Ano
        foreach ([1, 2] as $tri) {
            foreach ([$discMat, $discPort, $discHistoria, $discAdm] as $disc) {
                $this->seedDisciplinaEstruturada($peiAna, $disc, $tri, [
                    'grau_autonomia' => ['pouco_suporte', 'alta_compreensao'],
                    'planejamentos' => [[
                        'objetivos' => "Desenvolver competências essenciais de {$disc->nome} no {$tri}º trimestre com mediação bilíngue.",
                        'conteudos' => "Conteúdos programáticos do {$tri}º trimestre de {$disc->nome} apoiados por recursos visuais.",
                        'estrategias_ensino' => [
                            'Utilização de diferentes formas de apresentação do conteúdo',
                            'Disponibilização de materiais de apoio/complementares.',
                        ],
                        'estrategias_ensino_outros' => 'Mediação integral com Intérprete de Libras e vídeos legendados.',
                        'estrategias_avaliacao' => [
                            'Utilização de recursos visuais',
                            'Questões apresentadas de forma objetiva e direta',
                        ],
                        'tempo_opcao' => 'Todo o trimestre',
                        'avaliacao_desempenho' => [
                            'Atingiu o objetivo proposto com autonomia.',
                            'Apresentou maior participação e envolvimento nas atividades.',
                        ],
                        'sugestoes_aproveitamento' => 'Propor atividades de aprofundamento devido ao perfil de altas habilidades.',
                    ]],
                    'avaliacoes' => [
                        ['tipo' => 'prova_escrita', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '9.5'],
                        ['tipo' => 'trabalhos', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '10.0'],
                    ],
                ]);
            }
        }
        // no 3º trimestre, apenas matemática preenchida; demais disciplinas pendentes
        $this->seedDisciplinaEstruturada($peiAna, $discMat, 3, [
            'grau_autonomia' => ['pouco_suporte', 'alta_compreensao'],
            'planejamentos' => [[
                'objetivos' => 'Aplicar matemática financeira em cenários de fluxo de caixa e investimentos.',
                'conteudos' => 'Juros compostos, amortização e análise de gráficos financeiros.',
                'estrategias_ensino' => ['Resolução de problemas', 'Exemplificação/modelagem'],
                'estrategias_avaliacao' => ['Utilização de recursos visuais', 'Questões apresentadas de forma objetiva e direta'],
                'tempo_opcao' => 'Todo o trimestre',
                'avaliacao_desempenho' => ['Atingiu o objetivo proposto com autonomia.'],
                'sugestoes_aproveitamento' => 'Integrar com planilhas eletrônicas no laboratório.',
            ]],
            'avaliacoes' => [
                ['tipo' => 'prova_escrita', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '10.0'],
            ],
        ]);

        // ---------------------------------------------------------------------
        // cenário 3: enzo gabriel (tdah + dislexia)
        // 1º trimestre completo; 2º trimestre parcial; 3º trimestre pendente
        // ---------------------------------------------------------------------
        $alunoEnzo = Aluno::updateOrCreate(
            ['matricula' => '2026100303'],
            ['nome' => 'Enzo Gabriel Nogueira', 'data_nascimento' => '2008-11-03', 'is_active' => true]
        );
        $alunoEnzo->deficiencias()->sync([$defTdah->id, $defDislexia->id]);
        $alunoEnzo->turmas()->sync([$turmaInfo2->id => ['ano_letivo' => 2026, 'is_atual' => true]]);

        $peiEnzo = Pei::updateOrCreate(
            ['codigo' => 'PEI-2026-003'],
            [
                'aluno_id' => $alunoEnzo->id,
                'turma_id' => $turmaInfo2->id,
                'ano_letivo' => 2026,
                'data_elaboracao' => '2026-02-19',
                'diagnostico' => '[F90.0] TDAH (tipo combinado), [F81.0] Dislexia',
                'tempo_livre' => 'Montagem de computadores, robótica educacional e skate.',
                'pratica_esporte' => 'Skate e corrida.',
                'caracteristicas_discente' => 'Criativo, excelente em atividades práticas de laboratório, dispersa com textos extensos.',
                'organizacao_e_prazos' => 'Necessita divisão de projetos em entregas curtas e lembretes visuais.',
                'dificuldade_interacao_social' => 'Muito sociável e bem integrado à turma.',
                'sugestoes_recursos_estrategias' => 'Enunciados curtos em tópicos, leitura mediada quando solicitada, avaliações práticas e fracionamento de provas longas.',
                'status' => 'Ativo',
                'is_active' => true,
            ]
        );

        $this->seedFichaPei($peiEnzo, [
            'responsaveis_ficha' => [
                ['user_id' => $admin->id],
                ['user_id' => $profRicardo->id],
            ],
            'responsaveis_nai' => [
                1 => [['user_id' => $naiBeatriz->id]],
                2 => [['user_id' => $naiBeatriz->id]],
            ],
            'identificacoes' => [
                'habilidade' => [
                    'Raciocínio prático ágil em laboratório de banco de dados e hardware.',
                ],
                'dificuldade' => [
                    'Leitura lenta de textos longos sem divisão em tópicos.',
                    'Gestão do tempo em avaliações extensas.',
                ],
                'dificultador_interno' => [
                    'Provas com enunciados extensos e múltiplas cobranças na mesma semana.',
                ],
                'facilitador_interno' => [
                    'Aulas práticas em laboratório e feedback imediato.',
                ],
            ],
            'intervencoes' => [
                [
                    'demanda' => 'Adaptação de avaliações escritas',
                    'encaminhamento_estrategia' => 'Dividir provas extensas em duas etapas, permitir leitura das questões e priorizar atividades práticas.',
                    'responsavel' => 'Docentes da turma',
                ],
            ],
            'nai_atividades' => [
                1 => [[
                    'objetivos' => 'Estabelecer rotina de checklists semanais e alinhar adaptações de leitura com os docentes.',
                    'recursos_acoes' => 'Quadro semanal de entregas e reunião com professores de Informática e Física.',
                    'tempo' => '1º Trimestre',
                    'avaliacao_resultados' => 'Melhora consistente na pontualidade das entregas práticas.',
                    'sugestoes_aproveitamento' => 'Manter entregas fracionadas no 2º trimestre.',
                ]],
            ],
        ]);

        PeiDisciplina::where('pei_id', $peiEnzo->id)->delete();
        // 1º trimestre completo em todas as disciplinas da turma Info 2º B
        foreach ([$discBD, $discFisica, $discMat] as $disc) {
            $this->seedDisciplinaEstruturada($peiEnzo, $disc, 1, [
                'grau_autonomia' => ['pouco_suporte', 'alta_compreensao'],
                'planejamentos' => [[
                    'objetivos' => "Consolidar fundamentos de {$disc->nome} por meio de roteiros curtos e prática guiada.",
                    'conteudos' => "Tópicos essenciais do 1º trimestre de {$disc->nome}.",
                    'estrategias_ensino' => [
                        'Divisão da atividade em etapas',
                        'Orientações passo a passo',
                        'Atividades práticas/experimentais',
                    ],
                    'estrategias_avaliacao' => [
                        'Questões divididas em etapas',
                        'Leitura das questões',
                        'Tempo adicional para realização da avaliação',
                    ],
                    'tempo_opcao' => '3 a 4 semanas',
                    'avaliacao_desempenho' => [
                        'Atingiu o objetivo proposto com apoio/orientação.',
                        'Apresentou avanço na realização das atividades.',
                    ],
                    'sugestoes_aproveitamento' => 'Continuar fracionamento de atividades e reforço visual.',
                ]],
                'avaliacoes' => [
                    ['tipo' => 'prova_pratica', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '8.8'],
                    ['tipo' => 'prova_oral', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '9.0'],
                ],
            ]);
        }
        // 2º trimestre apenas Banco de Dados preenchido (Física e Matemática pendentes no 2º e 3º tri)
        $this->seedDisciplinaEstruturada($peiEnzo, $discBD, 2, [
            'grau_autonomia' => ['pouco_suporte', 'alta_compreensao'],
            'planejamentos' => [[
                'objetivos' => 'Construir consultas SQL com junções e agregações em ambiente prático.',
                'conteudos' => 'SELECT, JOIN, GROUP BY e consultas parametrizadas.',
                'estrategias_ensino' => ['Atividades práticas/experimentais', 'Exemplificação/modelagem'],
                'estrategias_avaliacao' => ['Uso de computador ou outro recurso tecnológico', 'Questões apresentadas de forma objetiva e direta'],
                'tempo_opcao' => '1 a 2 meses',
                'avaliacao_desempenho' => ['Atingiu o objetivo proposto com autonomia.'],
                'sugestoes_aproveitamento' => 'Avançar para modelagem física de projeto integrador.',
            ]],
            'avaliacoes' => [
                ['tipo' => 'prova_pratica', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '9.2'],
            ],
        ]);

        // ---------------------------------------------------------------------
        // cenário 4: sophia helena (deficiência física motora) — pei inicial
        // ficha do aluno e ficha do pei preenchidas; trimestres e disciplinas pendentes
        // ---------------------------------------------------------------------
        $alunaSophia = Aluno::updateOrCreate(
            ['matricula' => '2026100404'],
            ['nome' => 'Sophia Helena Albuquerque', 'data_nascimento' => '2009-12-10', 'is_active' => true]
        );
        $alunaSophia->deficiencias()->sync([$defFisica->id]);
        $alunaSophia->turmas()->sync([$turmaEdif1->id => ['ano_letivo' => 2026, 'is_atual' => true]]);

        $peiSophia = Pei::updateOrCreate(
            ['codigo' => 'PEI-2026-004'],
            [
                'aluno_id' => $alunaSophia->id,
                'turma_id' => $turmaEdif1->id,
                'ano_letivo' => 2026,
                'data_elaboracao' => '2026-02-26',
                'diagnostico' => '[G80.0] Paralisia Cerebral Espástica com uso de cadeira de rodas',
                'tempo_livre' => 'Desenho digital com mesa digitalizadora e leitura sobre arquitetura acessível.',
                'pratica_esporte' => 'Hidroterapia e fisioterapia motora.',
                'caracteristicas_discente' => 'Excelente oralidade, liderança natural e grande interesse por desenho técnico digital.',
                'organizacao_e_prazos' => 'Cumpre prazos utilizando computador e ditado por voz.',
                'dificuldade_interacao_social' => 'Excelente relacionamento com colegas e docentes.',
                'sugestoes_recursos_estrategias' => 'Mesa adaptada para cadeira de rodas; aceitar entregas digitais (CAD/editores de texto) no lugar de escrita manual extensa; priorizar provas orais ou digitais.',
                'status' => 'Ativo',
                'is_active' => true,
            ]
        );

        $this->seedFichaPei($peiSophia, [
            'responsaveis_ficha' => [['user_id' => $naiBeatriz->id]],
            'identificacoes' => [
                'habilidade' => ['Excelente argumentação oral e domínio de softwares de desenho assistido.'],
                'dificuldade' => ['Fadiga motora em atividades manuscritas extensas.'],
                'facilitador_interno' => ['Campus com rampas, elevadores e mobiliário acessível em sala.'],
            ],
            'intervencoes' => [
                [
                    'demanda' => 'Adaptação de registro escrito nas disciplinas',
                    'encaminhamento_estrategia' => 'Permitir uso de notebook pessoal e avaliações digitais ou orais.',
                    'responsavel' => 'Corpo docente e NAI',
                ],
            ],
        ]);

        // ---------------------------------------------------------------------
        // cenário 5: mateus henrique (deficiência intelectual) — histórico 2025 + ativo 2026
        // ---------------------------------------------------------------------
        $alunoMateus = Aluno::updateOrCreate(
            ['matricula' => '2025100505'],
            ['nome' => 'Mateus Henrique Costa', 'data_nascimento' => '2008-05-18', 'is_active' => true]
        );
        $alunoMateus->deficiencias()->sync([$defIntelectual->id]);
        $alunoMateus->turmas()->sync([
            $turmaInfo1_2025->id => ['ano_letivo' => 2025, 'is_atual' => false],
            $turmaInfo2->id => ['ano_letivo' => 2026, 'is_atual' => true],
        ]);

        $peiMateus2025 = Pei::updateOrCreate(
            ['codigo' => 'PEI-2025-001'],
            [
                'aluno_id' => $alunoMateus->id,
                'turma_id' => $turmaInfo1_2025->id,
                'ano_letivo' => 2025,
                'data_elaboracao' => '2025-02-20',
                'diagnostico' => '[F70.0] Deficiência Intelectual Leve',
                'tempo_livre' => 'Música, jardinagem e jogos educativos no tablet.',
                'pratica_esporte' => 'Caminhada e futsal recreativo.',
                'caracteristicas_discente' => 'Esforçado, afetivo, aprende melhor com exemplos concretos e repetição guiada.',
                'organizacao_e_prazos' => 'Necessita acompanhamento próximo para organização de cadernos e datas.',
                'dificuldade_interacao_social' => 'Tímido inicialmente, mas cria vínculos positivos em grupos acolhedores.',
                'sugestoes_recursos_estrategias' => 'Linguagem simples e direta, apoio visual, mediação individual em plantões e atividades graduadas.',
                'status' => 'Concluído',
                'is_active' => true,
            ]
        );

        $this->seedFichaPei($peiMateus2025, [
            'responsaveis_ficha' => [['user_id' => $naiBeatriz->id]],
            'responsaveis_nai' => [
                1 => [['user_id' => $naiBeatriz->id]],
                2 => [['user_id' => $naiBeatriz->id]],
                3 => [['user_id' => $naiBeatriz->id]],
            ],
            'identificacoes' => [
                'habilidade' => ['Persistência, assiduidade exemplar e boa memória visual.'],
                'dificuldade' => ['Abstração conceitual sem apoio concreto e resolução de problemas com múltiplas etapas simultâneas.'],
            ],
            'intervencoes' => [
                [
                    'demanda' => 'Adequação curricular com foco nos objetivos essenciais',
                    'encaminhamento_estrategia' => 'Priorizar habilidades nucleares com roteiros passo a passo e plantões semanais.',
                    'responsavel' => 'Docentes e NAI',
                ],
            ],
            'nai_atividades' => [
                1 => [[
                    'objetivos' => 'Acompanhar inserção no ensino médio técnico e mediar adaptações curriculares.',
                    'recursos_acoes' => 'Atendimentos semanais no AEE.',
                    'tempo' => '1º Trimestre',
                    'avaliacao_resultados' => 'Boa adaptação escolar.',
                    'sugestoes_aproveitamento' => 'Manter plantões de Matemática e Português.',
                ]],
                2 => [[
                    'objetivos' => 'Monitorar consolidação da leitura compreensiva e operações básicas.',
                    'recursos_acoes' => 'Jogos pedagógicos e mapas visuais.',
                    'tempo' => '2º Trimestre',
                    'avaliacao_resultados' => 'Progresso contínuo e maior confiança em sala.',
                    'sugestoes_aproveitamento' => 'Reforçar autonomia nas entregas.',
                ]],
                3 => [[
                    'objetivos' => 'Avaliar avanços anuais e planejar transição para o 2º ano em 2026.',
                    'recursos_acoes' => 'Conselho de classe inclusivo e reunião com a família.',
                    'tempo' => '3º Trimestre',
                    'avaliacao_resultados' => 'Aprovado com avanços significativos em autonomia e participação.',
                    'sugestoes_aproveitamento' => 'Importar histórico de estratégias bem-sucedidas no PEI 2026.',
                ]],
            ],
        ]);

        PeiDisciplina::where('pei_id', $peiMateus2025->id)->delete();
        foreach ([1, 2, 3] as $tri) {
            foreach ([$discMat, $discPort, $discProgWeb] as $disc) {
                $this->seedDisciplinaEstruturada($peiMateus2025, $disc, $tri, [
                    'grau_autonomia' => ['muito_suporte', 'pouca_compreensao'],
                    'especifique_outros' => 'Portfólio de atividades práticas',
                    'planejamentos' => [[
                        'objetivos' => "Alcançar objetivos essenciais adaptados de {$disc->nome} ({$tri}º trimestre/2025).",
                        'conteudos' => "Conteúdos nucleares priorizados de {$disc->nome}.",
                        'estrategias_ensino' => [
                            'Repetição e retomada de conteúdos',
                            'Orientações passo a passo',
                            'Plantão individualizado',
                        ],
                        'estrategias_avaliacao' => [
                            'Questões apresentadas de forma objetiva e direta',
                            'Uso de material acessível/adaptado',
                            'Realização da avaliação em etapas',
                        ],
                        'tempo_opcao' => 'Todo o trimestre',
                        'avaliacao_desempenho' => [
                            'Atingiu o objetivo proposto com apoio/orientação.',
                            'Apresentou avanço na compreensão e assimilação dos conteúdos.',
                        ],
                        'sugestoes_aproveitamento' => 'Manter mediação estruturada no próximo período.',
                    ]],
                    'avaliacoes' => [
                        ['tipo' => 'trabalhos', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '8.0'],
                        ['tipo' => 'outros', 'adotado' => true, 'valor_1' => '10', 'nota_1' => '8.5'],
                    ],
                ]);
            }
        }

        // pei 2026 do discente mateus (em andamento)
        $peiMateus2026 = Pei::updateOrCreate(
            ['codigo' => 'PEI-2026-005'],
            [
                'aluno_id' => $alunoMateus->id,
                'turma_id' => $turmaInfo2->id,
                'ano_letivo' => 2026,
                'data_elaboracao' => '2026-02-23',
                'diagnostico' => '[F70.0] Deficiência Intelectual Leve',
                'tempo_livre' => 'Música, jardinagem e jogos educativos no tablet.',
                'pratica_esporte' => 'Caminhada e futsal recreativo.',
                'caracteristicas_discente' => 'Mais seguro após o 1º ano; mantém boa resposta a exemplos concretos e apoio visual.',
                'organizacao_e_prazos' => 'Utiliza agenda ilustrada com apoio semanal do NAI.',
                'dificuldade_interacao_social' => 'Bem acolhido pela turma de 2º ano.',
                'sugestoes_recursos_estrategias' => 'Manter roteiros passo a passo, plantão individualizado e avaliações adaptadas com enunciados curtos.',
                'status' => 'Ativo',
                'is_active' => true,
            ]
        );
        $this->seedFichaPei($peiMateus2026, [
            'responsaveis_ficha' => [['user_id' => $naiBeatriz->id], ['user_id' => $naiRodrigo->id]],
            'identificacoes' => [
                'habilidade' => ['Assiduidade, dedicação e ótima resposta a recursos visuais.'],
                'dificuldade' => ['Conceitos abstratos extensos sem exemplificação prática.'],
            ],
            'intervencoes' => [
                [
                    'demanda' => 'Continuidade das adaptações curriculares no 2º ano',
                    'encaminhamento_estrategia' => 'Manter plantões individuais e atividades graduadas.',
                    'responsavel' => 'Docentes e NAI',
                ],
            ],
        ]);

        // ---------------------------------------------------------------------
        // cenário 6: beatriz oliveira — aluna ativa sem pei cadastrado
        // ---------------------------------------------------------------------
        $alunaBeatriz = Aluno::updateOrCreate(
            ['matricula' => '2026100606'],
            ['nome' => 'Beatriz Oliveira Santos', 'data_nascimento' => '2009-09-14', 'is_active' => true]
        );
        $alunaBeatriz->deficiencias()->sync([$defTdah->id]);
        $alunaBeatriz->turmas()->sync([$turmaAdm1->id => ['ano_letivo' => 2026, 'is_atual' => true]]);

        // ---------------------------------------------------------------------
        // cenário 7: rafael mendes — aluno inativo para filtros de listagem
        // ---------------------------------------------------------------------
        $alunoInativo = Aluno::updateOrCreate(
            ['matricula' => '2024100707'],
            ['nome' => 'Rafael Mendes Pereira (Transferido)', 'data_nascimento' => '2007-07-02', 'is_active' => false]
        );
        $alunoInativo->deficiencias()->sync([$defDislexia->id]);

        // =====================================================================
        // 7. trilha de auditoria coerente
        // =====================================================================
        if (AuditLog::count() === 0) {
            $logs = [
                [$admin, 'Autenticação', 'Login', null, null, 'Administrador autenticou-se no sistema.', now()->subDays(12)],
                [$admin, 'Calendário', 'Atualização', 'AnoLetivo', null, 'Configurou os períodos trimestrais do ano letivo de 2026.', now()->subDays(11)],
                [$naiBeatriz, 'Alunos', 'Criação', 'Aluno', $alunoLucas->id, 'Cadastrou o discente Lucas Ferreira da Silva (Matrícula: 2026100101).', now()->subDays(10)],
                [$naiBeatriz, 'PEIs', 'Criação', 'Pei', $peiLucas->id, 'Elaborou o PEI PEI-2026-001 para Lucas Ferreira da Silva.', now()->subDays(9)],
                [$profCarlos, 'PEIs', 'Atualização', 'Pei', $peiLucas->id, 'Atualizou o planejamento e avaliações de Matemática (1º Trimestre) no PEI-2026-001.', now()->subDays(7)],
                [$profRicardo, 'PEIs', 'Atualização', 'Pei', $peiLucas->id, 'Atualizou o planejamento e avaliações de Programação Web (1º Trimestre) no PEI-2026-001.', now()->subDays(6)],
                [$apoioGabriel, 'PEIs', 'Atualização', 'Pei', $peiLucas->id, 'Registrou ações da Monitoria de Tecnologia Assistiva (1º Trimestre) no PEI-2026-001.', now()->subDays(5)],
                [$naiRodrigo, 'PEIs', 'Criação', 'Pei', $peiAna->id, 'Elaborou o PEI PEI-2026-002 para Ana Clara Ribeiro de Menezes.', now()->subDays(4)],
                [$apoioLarissa, 'PEIs', 'Atualização', 'Pei', $peiAna->id, 'Registrou ações de Tradução e Interpretação em Libras no PEI-2026-002.', now()->subDays(3)],
                [$admin, 'PEIs', 'Exportação', 'Pei', $peiLucas->id, 'Gerou relatório completo do PEI PEI-2026-001 em formato PDF.', now()->subDays(1)],
            ];

            foreach ($logs as [$user, $modulo, $acao, $entTipo, $entId, $desc, $quando]) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'user_cargo' => $user->cargo,
                    'modulo' => $modulo,
                    'acao' => $acao,
                    'entidade_tipo' => $entTipo,
                    'entidade_id' => $entId,
                    'descricao' => $desc,
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'SIGEPEI TestDataSeeder',
                    'created_at' => $quando,
                    'updated_at' => $quando,
                ]);
            }
        }
    }

    private function upsertUser(string $email, string $name, string $cargo, bool $isAdmin, bool $isActive, string $password, $verifiedAt): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'email_verified_at' => $verifiedAt,
                'cargo' => $cargo,
                'is_admin' => $isAdmin,
                'is_active' => $isActive,
            ]
        );
    }

    private function vincularDisciplinaTurma(Turma $turma, Disciplina $disciplina, array $professoresSubstitutoMap): void
    {
        $td = TurmaDisciplina::updateOrCreate([
            'turma_id' => $turma->id,
            'disciplina_id' => $disciplina->id,
        ]);

        $syncData = [];
        foreach ($professoresSubstitutoMap as $userId => $isSubstituto) {
            $syncData[$userId] = ['is_substituto' => $isSubstituto];
        }
        $td->professores()->sync($syncData);
    }

    private function seedFichaPei(Pei $pei, array $dados): void
    {
        PeiResponsavel::where('pei_id', $pei->id)->delete();
        foreach ($dados['responsaveis_ficha'] ?? [] as $resp) {
            PeiResponsavel::create([
                'pei_id' => $pei->id,
                'user_id' => $resp['user_id'] ?? null,
                'nome_externo' => $resp['nome_externo'] ?? null,
                'secao' => 'ficha_inicial',
            ]);
        }

        foreach ($dados['responsaveis_nai'] ?? [] as $tri => $lista) {
            foreach ($lista as $resp) {
                PeiResponsavel::create([
                    'pei_id' => $pei->id,
                    'user_id' => $resp['user_id'] ?? null,
                    'nome_externo' => $resp['nome_externo'] ?? null,
                    'secao' => "nai_{$tri}",
                ]);
            }
        }

        PeiIdentificacao::where('pei_id', $pei->id)->delete();
        $ordem = 0;
        foreach ($dados['identificacoes'] ?? [] as $tipo => $itens) {
            foreach ($itens as $descricao) {
                PeiIdentificacao::create([
                    'pei_id' => $pei->id,
                    'tipo' => $tipo,
                    'descricao' => $descricao,
                    'ordem' => $ordem++,
                ]);
            }
        }

        PeiIntervencao::where('pei_id', $pei->id)->delete();
        foreach ($dados['intervencoes'] ?? [] as $idx => $int) {
            PeiIntervencao::create([
                'pei_id' => $pei->id,
                'demanda' => $int['demanda'],
                'encaminhamento_estrategia' => $int['encaminhamento_estrategia'],
                'responsavel' => $int['responsavel'],
                'ordem' => $idx,
            ]);
        }

        PeiNaiAtividade::where('pei_id', $pei->id)->delete();
        foreach ($dados['nai_atividades'] ?? [] as $tri => $atividades) {
            foreach ($atividades as $idx => $atv) {
                PeiNaiAtividade::create([
                    'pei_id' => $pei->id,
                    'trimestre' => $tri,
                    'objetivos' => $atv['objetivos'],
                    'recursos_acoes' => $atv['recursos_acoes'],
                    'tempo' => $atv['tempo'],
                    'avaliacao_resultados' => $atv['avaliacao_resultados'] ?? null,
                    'sugestoes_aproveitamento' => $atv['sugestoes_aproveitamento'] ?? null,
                    'ordem' => $idx,
                ]);
            }
        }
    }

    private function seedEquipeApoio(Pei $pei, string $nome, int $ordem, array $trimestres): PeiApoio
    {
        $apoio = PeiApoio::create([
            'pei_id' => $pei->id,
            'nome' => $nome,
            'ordem' => $ordem,
        ]);

        foreach ($trimestres as $tri => $info) {
            foreach ($info['responsaveis'] ?? [] as $resp) {
                PeiApoioResponsavel::create([
                    'pei_apoio_id' => $apoio->id,
                    'trimestre' => $tri,
                    'user_id' => $resp['user_id'] ?? null,
                    'nome_externo' => $resp['nome_externo'] ?? null,
                ]);
            }

            foreach ($info['atividades'] ?? [] as $idx => $atv) {
                PeiApoioAtividade::create([
                    'pei_apoio_id' => $apoio->id,
                    'trimestre' => $tri,
                    'objetivos' => $atv['objetivos'],
                    'recursos_acoes' => $atv['recursos_acoes'],
                    'tempo' => $atv['tempo'],
                    'avaliacao_resultados' => $atv['avaliacao_resultados'] ?? null,
                    'sugestoes_aproveitamento' => $atv['sugestoes_aproveitamento'] ?? null,
                    'ordem' => $idx,
                ]);
            }
        }

        return $apoio;
    }

    private function seedDisciplinaEstruturada(Pei $pei, Disciplina $disciplina, int $trimestre, array $dados): PeiDisciplina
    {
        $peiDisc = PeiDisciplina::create([
            'pei_id' => $pei->id,
            'disciplina_id' => $disciplina->id,
            'trimestre' => $trimestre,
            'grau_autonomia' => $dados['grau_autonomia'] ?? [],
            'especifique_outros' => $dados['especifique_outros'] ?? null,
        ]);

        foreach ($dados['planejamentos'] ?? [] as $idx => $plan) {
            $ensino = $plan['estrategias_ensino'] ?? [];
            $ensinoOutros = $plan['estrategias_ensino_outros'] ?? null;
            $aval = $plan['estrategias_avaliacao'] ?? [];
            $avalOutros = $plan['estrategias_avaliacao_outros'] ?? null;
            $desempenho = $plan['avaliacao_desempenho'] ?? [];
            $desempenhoOutros = $plan['avaliacao_desempenho_outros'] ?? null;
            $tempoOpcao = $plan['tempo_opcao'] ?? 'Todo o trimestre';
            $tempoOutro = $plan['tempo_outro'] ?? null;
            $tempoFinal = $tempoOpcao === 'Outro' ? ($tempoOutro ?? 'Outro') : $tempoOpcao;

            $metPartes = [];
            if (!empty($ensino)) {
                $metPartes[] = "Estratégias de ensino:\n• " . implode("\n• ", $ensino);
            }
            if (!empty($ensinoOutros)) {
                $metPartes[] = "Outras estratégias de ensino: {$ensinoOutros}";
            }
            if (!empty($aval)) {
                $metPartes[] = "Estratégias e condições de avaliação:\n• " . implode("\n• ", $aval);
            }
            if (!empty($avalOutros)) {
                $metPartes[] = "Outras estratégias de avaliação: {$avalOutros}";
            }

            $avPartes = [];
            if (!empty($desempenho)) {
                $avPartes[] = '• ' . implode("\n• ", $desempenho);
            }
            if (!empty($desempenhoOutros)) {
                $avPartes[] = "Outras observações: {$desempenhoOutros}";
            }

            PeiDisciplinaPlanejamento::create([
                'pei_disciplina_id' => $peiDisc->id,
                'objetivos' => $plan['objetivos'],
                'conteudos' => $plan['conteudos'],
                'metodologias_recursos' => implode("\n\n", $metPartes),
                'estrategias_ensino' => $ensino,
                'estrategias_ensino_outros' => $ensinoOutros,
                'estrategias_avaliacao' => $aval,
                'estrategias_avaliacao_outros' => $avalOutros,
                'tempo' => $tempoFinal,
                'tempo_opcao' => $tempoOpcao,
                'tempo_outro' => $tempoOutro,
                'avaliacao_resultados' => implode("\n\n", $avPartes),
                'avaliacao_desempenho' => $desempenho,
                'avaliacao_desempenho_outros' => $desempenhoOutros,
                'sugestoes_aproveitamento' => $plan['sugestoes_aproveitamento'] ?? null,
                'ordem' => $idx,
            ]);
        }

        foreach ($dados['avaliacoes'] ?? [] as $av) {
            PeiDisciplinaAvaliacao::create([
                'pei_disciplina_id' => $peiDisc->id,
                'tipo' => $av['tipo'],
                'adotado' => (bool) ($av['adotado'] ?? true),
                'valor_1' => $av['valor_1'] ?? null,
                'nota_1' => $av['nota_1'] ?? null,
                'valor_2' => $av['valor_2'] ?? null,
                'nota_2' => $av['nota_2'] ?? null,
                'valor_3' => $av['valor_3'] ?? null,
                'nota_3' => $av['nota_3'] ?? null,
            ]);
        }

        return $peiDisc;
    }
}

