<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeiDisciplinaPlanejamento extends Model
{
    use HasFactory;

    protected $table = 'pei_disciplina_planejamentos';

    protected $fillable = [
        'pei_disciplina_id',
        'objetivos',
        'conteudos',
        'metodologias_recursos',
        'estrategias_ensino',
        'estrategias_ensino_outros',
        'estrategias_avaliacao',
        'estrategias_avaliacao_outros',
        'tempo',
        'tempo_opcao',
        'tempo_outro',
        'avaliacao_resultados',
        'avaliacao_desempenho',
        'avaliacao_desempenho_outros',
        'sugestoes_aproveitamento',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
            'estrategias_ensino' => 'array',
            'estrategias_avaliacao' => 'array',
            'avaliacao_desempenho' => 'array',
        ];
    }

    /**
     * registro disciplinar trimestral ao qual o planejamento pertence.
     */
    public function peiDisciplina(): BelongsTo
    {
        return $this->belongsTo(PeiDisciplina::class, 'pei_disciplina_id');
    }
}
