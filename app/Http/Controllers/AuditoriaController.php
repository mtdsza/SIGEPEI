<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuditoriaController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        if (!$user->is_admin && $user->cargo !== 'NAI') {
            abort(403, 'Acesso restrito à equipe gestora e administradores.');
        }

        $query = AuditLog::with('user:id,name,email,cargo')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        // aplica filtro de busca geral por termo
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('descricao', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('user_email', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('entidade_tipo', 'like', "%{$search}%");
            });
        }

        // filtra registros pelo módulo selecionado
        if ($request->filled('modulo')) {
            $query->where('modulo', $request->input('modulo'));
        }

        // filtra registros pelo tipo de ação realizada
        if ($request->filled('acao')) {
            $query->where('acao', $request->input('acao'));
        }

        // filtra registros por usuário autor da ação
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // filtra registros pelo intervalo de datas especificado
        if ($request->filled('data_inicio')) {
            $query->where('created_at', '>=', Carbon::parse($request->input('data_inicio'))->startOfDay());
        }
        if ($request->filled('data_fim')) {
            $query->where('created_at', '<=', Carbon::parse($request->input('data_fim'))->endOfDay());
        }

        $logs = $query->get()->map(function (AuditLog $log) {
            return [
                'id' => $log->id,
                'data_formatada' => $log->created_at->format('d/m/Y H:i:s'),
                'data_relativa' => $log->created_at->diffForHumans(),
                'usuario' => [
                    'id' => $log->user_id,
                    'nome' => $log->user_name,
                    'email' => $log->user_email,
                    'cargo' => $log->user_cargo,
                ],
                'modulo' => $log->modulo,
                'acao' => $log->acao,
                'entidade_tipo' => $log->entidade_tipo,
                'entidade_id' => $log->entidade_id,
                'descricao' => $log->descricao,
                'ip_address' => $log->ip_address,
                'tem_diff' => !empty($log->dados_anteriores) || !empty($log->dados_novos),
            ];
        });

        // calcula totais para exibição dos cards de estatísticas
        $hoje = Carbon::today();
        $stats = [
            'total' => AuditLog::count(),
            'hoje' => AuditLog::where('created_at', '>=', $hoje)->count(),
            'peis' => AuditLog::where('modulo', 'PEIs')->count(),
            'autenticacoes' => AuditLog::where('modulo', 'Autenticação')->count(),
        ];

        $modulos = AuditLog::select('modulo')->distinct()->pluck('modulo')->values()->toArray();
        $acoes = AuditLog::select('acao')->distinct()->pluck('acao')->values()->toArray();
        $usuarios = User::select('id', 'name', 'email')->orderBy('name')->get();

        return Inertia::render('Auditoria/Index', [
            'logs' => $logs,
            'stats' => $stats,
            'modulosDisponiveis' => $modulos,
            'acoesDisponiveis' => $acoes,
            'usuariosDisponiveis' => $usuarios,
            'filtros' => $request->only(['search', 'modulo', 'acao', 'user_id', 'data_inicio', 'data_fim']),
        ]);
    }

    public function show(int $id): Response
    {
        $user = Auth::user();
        if (!$user->is_admin && $user->cargo !== 'NAI') {
            abort(403, 'Acesso restrito à equipe gestora e administradores.');
        }

        $log = AuditLog::with('user')->findOrFail($id);

        // compara os dados anteriores e novos para gerar a diferença campo a campo
        $campoDiffs = [];
        $dadosAnt = $log->dados_anteriores ?? [];
        $dadosNov = $log->dados_novos ?? [];
        $allKeys = array_unique(array_merge(array_keys($dadosAnt), array_keys($dadosNov)));

        $formatVal = function ($val) {
            if (is_null($val) || $val === '') {
                return null;
            }
            if (is_bool($val)) {
                return $val ? 'Sim' : 'Não';
            }
            if (is_array($val)) {
                if (empty($val)) {
                    return null;
                }
                if (array_is_list($val)) {
                    return implode("\n", array_map(fn($item) => "• " . (is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : $item), $val));
                }
                return json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
            return (string) $val;
        };

        foreach ($allKeys as $key) {
            $ant = $dadosAnt[$key] ?? null;
            $nov = $dadosNov[$key] ?? null;

            $mudou = ($ant !== $nov);

            $campoDiffs[] = [
                'campo' => $key,
                'anterior' => $formatVal($ant),
                'novo' => $formatVal($nov),
                'alterado' => $mudou,
            ];
        }

        // define o link de redirecionamento para o recurso auditado
        $entidadeLink = null;
        if ($log->entidade_tipo === 'Pei' && $log->entidade_id) {
            $entidadeLink = route('peis.show', $log->entidade_id);
        } elseif ($log->entidade_tipo === 'Aluno' && $log->entidade_id) {
            $entidadeLink = route('alunos.show', $log->entidade_id);
        } elseif ($log->entidade_tipo === 'Turma' && $log->entidade_id) {
            $entidadeLink = route('turmas.show', $log->entidade_id);
        }

        return Inertia::render('Auditoria/Show', [
            'log' => [
                'id' => $log->id,
                'data_formatada' => $log->created_at->format('d/m/Y H:i:s'),
                'data_relativa' => $log->created_at->diffForHumans(),
                'usuario' => [
                    'id' => $log->user_id,
                    'nome' => $log->user_name,
                    'email' => $log->user_email,
                    'cargo' => $log->user_cargo,
                ],
                'modulo' => $log->modulo,
                'acao' => $log->acao,
                'entidade_tipo' => $log->entidade_tipo,
                'entidade_id' => $log->entidade_id,
                'entidade_link' => $entidadeLink,
                'descricao' => $log->descricao,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'dados_anteriores' => $log->dados_anteriores,
                'dados_novos' => $log->dados_novos,
                'campo_diffs' => $campoDiffs,
            ],
        ]);
    }
}
