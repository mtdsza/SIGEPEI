<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * registra uma entrada na trilha de auditoria do sistema.
     *
     * @param string $modulo módulo do sistema (ex: 'PEIs', 'Alunos', 'Turmas', 'Disciplinas', 'Deficiências', 'Usuários', 'Autenticação')
     * @param string $acao tipo de ação realizada (ex: 'Criação', 'Atualização', 'Inativação', 'Reativação', 'Login', 'Logout')
     * @param string $descricao resumo legível da ação executada
     * @param mixed|null $entidade instância do modelo ou identificador
     * @param array|null $dadosAnteriores estado anterior para comparação de alterações
     * @param array|null $dadosNovos estado novo para comparação de alterações
     * @return AuditLog
     */
    public static function log(
        string $modulo,
        string $acao,
        string $descricao,
        mixed $entidade = null,
        ?array $dadosAnteriores = null,
        ?array $dadosNovos = null
    ): AuditLog {
        $user = Auth::user();

        $entidadeTipo = null;
        $entidadeId = null;

        if (is_object($entidade)) {
            $entidadeTipo = class_basename($entidade);
            $entidadeId = $entidade->id ?? null;
        } elseif (is_string($entidade)) {
            $entidadeTipo = $entidade;
        }

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Sistema / Visitante',
            'user_email' => $user?->email ?? 'sistema@sigepei.local',
            'user_cargo' => $user?->cargo ?? ($user?->is_admin ? 'Admin' : 'Sistema'),
            'modulo' => $modulo,
            'acao' => $acao,
            'entidade_tipo' => $entidadeTipo,
            'entidade_id' => $entidadeId,
            'descricao' => $descricao,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'user_agent' => Request::userAgent() ?? 'SIGEPEI Engine',
            'dados_anteriores' => $dadosAnteriores,
            'dados_novos' => $dadosNovos,
        ]);
    }
}
