<?php

namespace App\Http\Controllers;

use App\Models\AnoLetivo;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CalendarioController extends Controller
{
    /**
     * rótulos legíveis dos campos de datas para as mensagens de validação.
     */
    private const ROTULOS = [
        'trimestre1_inicio' => 'início do 1º trimestre',
        'trimestre1_fim' => 'término do 1º trimestre',
        'trimestre2_inicio' => 'início do 2º trimestre',
        'trimestre2_fim' => 'término do 2º trimestre',
        'trimestre3_inicio' => 'início do 3º trimestre',
        'trimestre3_fim' => 'término do 3º trimestre',
    ];

    /**
     * exibe o calendário escolar com os anos letivos cadastrados.
     */
    public function index(): Response
    {
        $anos = AnoLetivo::orderByDesc('ano')
            ->get()
            ->map(fn (AnoLetivo $a) => $a->toCalendarioArray())
            ->values();

        return Inertia::render('Calendario/Index', [
            'anosLetivos' => $anos,
        ]);
    }

    /**
     * salva em lote os anos letivos e as datas dos trimestres enviados pela página de calendário.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'anos' => ['present', 'array'],
            'anos.*.id' => ['nullable', 'integer', 'exists:anos_letivos,id', 'distinct'],
            'anos.*.ano' => ['required', 'integer', 'between:2000,2100', 'distinct'],
            'anos.*.concluido' => ['boolean'],
            'anos.*.trimestre1_inicio' => ['nullable', 'date'],
            'anos.*.trimestre1_fim' => ['nullable', 'date'],
            'anos.*.trimestre2_inicio' => ['nullable', 'date'],
            'anos.*.trimestre2_fim' => ['nullable', 'date'],
            'anos.*.trimestre3_inicio' => ['nullable', 'date'],
            'anos.*.trimestre3_fim' => ['nullable', 'date'],
        ], [
            'anos.*.ano.required' => 'Informe o ano letivo.',
            'anos.*.ano.between' => 'O ano letivo deve estar entre 2000 e 2100.',
            'anos.*.ano.distinct' => 'Este ano letivo está duplicado.',
            'anos.*.*.date' => 'Data inválida.',
        ]);

        $anosPayload = collect($request->input('anos', []));
        $existentes = AnoLetivo::whereIn('id', $anosPayload->pluck('id')->filter())->get()->keyBy('id');
        $erros = [];

        foreach ($anosPayload as $i => $item) {
            $id = $item['id'] ?? null;
            $ano = (int) $item['ano'];

            // garante que o ano não colida com outro registro que não esteja sendo editado
            $conflito = AnoLetivo::where('ano', $ano)
                ->when($id, fn ($q) => $q->where('id', '!=', $id))
                ->whereNotIn('id', $anosPayload->pluck('id')->filter()->reject(fn ($v) => $v == $id))
                ->exists();
            if ($conflito) {
                $erros["anos.{$i}.ano"] = "O ano letivo {$ano} já está cadastrado.";
            }

            // valida a sequência cronológica das datas preenchidas
            $anterior = null;
            foreach (AnoLetivo::CAMPOS_DATAS as $campo) {
                $valor = $item[$campo] ?? null;
                if (!$valor) {
                    continue;
                }
                $data = Carbon::parse($valor);
                if ($anterior && $data->lte($anterior['data'])) {
                    $erros["anos.{$i}.{$campo}"] = 'Deve ser posterior ao ' . self::ROTULOS[$anterior['campo']] . '.';
                }
                $anterior = ['campo' => $campo, 'data' => $data];
            }

            // um ano letivo só pode ser concluído com todos os períodos definidos
            if (!empty($item['concluido'])) {
                foreach (AnoLetivo::CAMPOS_DATAS as $campo) {
                    if (empty($item[$campo])) {
                        $erros["anos.{$i}.{$campo}"] ??= 'Obrigatório para concluir o ano letivo.';
                    }
                }
            }
        }

        if ($erros) {
            throw ValidationException::withMessages($erros);
        }

        $alteracoes = [];

        DB::transaction(function () use ($anosPayload, $existentes, &$alteracoes) {
            // fotografa o estado anterior antes da troca temporária de anos
            $antesPorId = $existentes->map(fn (AnoLetivo $r) => $r->toCalendarioArray());

            // libera temporariamente os anos editados para evitar conflitos de unicidade ao trocar valores entre registros
            foreach ($anosPayload as $item) {
                if (!empty($item['id']) && $existentes->has($item['id'])) {
                    $registro = $existentes[$item['id']];
                    if ((int) $registro->ano !== (int) $item['ano']) {
                        $registro->update(['ano' => -1 * $registro->id]);
                    }
                }
            }

            foreach ($anosPayload as $item) {
                $dados = ['ano' => (int) $item['ano'], 'concluido' => (bool) ($item['concluido'] ?? false)];
                foreach (AnoLetivo::CAMPOS_DATAS as $campo) {
                    $dados[$campo] = ($item[$campo] ?? null) ?: null;
                }

                $registro = !empty($item['id']) ? $existentes->get($item['id']) : null;

                if ($registro) {
                    $antes = $antesPorId->get($registro->id);
                    $registro->update($dados);
                    $depois = $registro->fresh()->toCalendarioArray();
                    if ($antes != $depois) {
                        $alteracoes[] = ['acao' => 'Atualização', 'registro' => $registro, 'antes' => $antes, 'depois' => $depois];
                    }
                } else {
                    $registro = AnoLetivo::create($dados);
                    $alteracoes[] = ['acao' => 'Criação', 'registro' => $registro, 'antes' => null, 'depois' => $registro->toCalendarioArray()];
                }
            }
        });

        // registra cada ano letivo criado ou alterado na trilha de auditoria
        foreach ($alteracoes as $alt) {
            AuditService::log(
                'Calendário',
                $alt['acao'],
                ($alt['acao'] === 'Criação' ? 'Cadastrou' : 'Atualizou') . " o ano letivo {$alt['registro']->ano} no calendário escolar",
                $alt['registro'],
                $alt['antes'],
                $alt['depois']
            );
        }

        return redirect()->route('calendario.index')->with('success', 'Calendário salvo com sucesso.');
    }

    /**
     * remove um ano letivo que ainda não tenha sido marcado como concluído.
     */
    public function destroy(AnoLetivo $anoLetivo): RedirectResponse
    {
        if ($anoLetivo->concluido) {
            return back()->withErrors([
                'calendario' => "O ano letivo {$anoLetivo->ano} está concluído e não pode ser removido.",
            ]);
        }

        $dados = $anoLetivo->toCalendarioArray();
        $anoLetivo->delete();

        AuditService::log(
            'Calendário',
            'Exclusão',
            "Removeu o ano letivo {$dados['ano']} do calendário escolar",
            'AnoLetivo',
            $dados,
            null
        );

        return redirect()->route('calendario.index')->with('success', "Ano letivo {$dados['ano']} removido.");
    }
}
