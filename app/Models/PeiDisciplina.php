<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeiDisciplina extends Model
{
    use HasFactory;

    protected $table = 'pei_disciplinas';

    protected $fillable = [
        'pei_id',
        'disciplina_id',
        'trimestre',
        'grau_autonomia',
        'especifique_outros',
    ];

    protected function casts(): array
    {
        return [
            'trimestre' => 'integer',
            'grau_autonomia' => 'array',
        ];
    }

    /**
     * pei associado ao registro disciplinar.
     */
    public function pei(): BelongsTo
    {
        return $this->belongsTo(Pei::class, 'pei_id');
    }

    /**
     * disciplina curricular vinculada.
     */
    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    /**
     * planejamentos pedagógicos da disciplina no trimestre.
     */
    public function planejamentos(): HasMany
    {
        return $this->hasMany(PeiDisciplinaPlanejamento::class, 'pei_disciplina_id')
            ->orderBy('ordem', 'asc');
    }

    /**
     * instrumentos e notas de avaliações da disciplina no trimestre.
     */
    public function avaliacoes(): HasMany
    {
        return $this->hasMany(PeiDisciplinaAvaliacao::class, 'pei_disciplina_id');
    }
}
