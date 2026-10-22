<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeiDisciplinaAvaliacao extends Model
{
    use HasFactory;

    protected $table = 'pei_disciplina_avaliacoes';

    protected $fillable = [
        'pei_disciplina_id',
        'tipo',
        'adotado',
        'valor_1',
        'nota_1',
        'valor_2',
        'nota_2',
        'valor_3',
        'nota_3',
    ];

    protected function casts(): array
    {
        return [
            'adotado' => 'boolean',
        ];
    }

    /**
     * registro disciplinar trimestral ao qual a avaliação pertence.
     */
    public function peiDisciplina(): BelongsTo
    {
        return $this->belongsTo(PeiDisciplina::class, 'pei_disciplina_id');
    }
}
