<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turma extends Model
{
    use HasFactory;

    protected $table = 'turmas';

    protected $fillable = [
        'nome_curso',
        'ano_letivo',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'ano_letivo' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * alunos vinculados à turma com seus históricos.
     */
    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Aluno::class, 'aluno_turma')
            ->withPivot('ano_letivo', 'is_atual')
            ->withTimestamps();
    }

    /**
     * vínculos de disciplinas associadas à turma.
     */
    public function turmaDisciplinas(): HasMany
    {
        return $this->hasMany(TurmaDisciplina::class, 'turma_id');
    }

    /**
     * disciplinas associadas à matriz curricular da turma.
     */
    public function disciplinas(): BelongsToMany
    {
        return $this->belongsToMany(Disciplina::class, 'turma_disciplina')
            ->withPivot('id')
            ->withTimestamps();
    }
}
