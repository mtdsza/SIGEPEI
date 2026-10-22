<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeiNaiAtividade extends Model
{
    use HasFactory;

    protected $table = 'pei_nai_atividades';

    protected $fillable = [
        'pei_id',
        'trimestre',
        'objetivos',
        'recursos_acoes',
        'tempo',
        'avaliacao_resultados',
        'sugestoes_aproveitamento',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'trimestre' => 'integer',
        ];
    }

    /**
     * pei ao qual a atividade do nai pertence.
     */
    public function pei(): BelongsTo
    {
        return $this->belongsTo(Pei::class, 'pei_id');
    }
}
