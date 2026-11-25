<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aluno extends Model
{
    use HasFactory;

    protected $table = 'alunos';

    protected $fillable = [
        'nome',
        'matricula',
        'data_nascimento',
        'foto_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date:Y-m-d',
            'is_active' => 'boolean',
        ];
    }

    /**
     * calcula a idade atual do discente a partir da data de nascimento.
     */
    public function getIdadeAttribute(): ?int
    {
        if (!$this->data_nascimento) {
            return null;
        }

        return Carbon::parse($this->data_nascimento)->age;
    }

    /**
     * retorna a url autenticada da foto de perfil do discente, ou null quando não houver foto.
     * o parâmetro de versão evita que o navegador exiba uma foto antiga em cache após a troca.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto_path) {
            return null;
        }

        return route('alunos.foto', [
            'aluno' => $this->id,
            'v' => $this->updated_at?->timestamp,
        ]);
    }

    /**
     * turmas associadas ao aluno com histórico de anos letivos.
     */
    public function turmas(): BelongsToMany
    {
        return $this->belongsToMany(Turma::class, 'aluno_turma')
            ->withPivot('ano_letivo', 'is_atual')
            ->withTimestamps()
            ->orderByPivot('ano_letivo', 'desc');
    }

    /**
     * retorna a turma atual ou a mais recente vinculada ao aluno.
     */
    public function turmaAtual()
    {
        if ($this->relationLoaded('turmas')) {
            return $this->turmas->first(fn ($t) => (bool) ($t->pivot->is_atual ?? false))
                ?? $this->turmas->sortByDesc(fn ($t) => (int) ($t->pivot->ano_letivo ?? 0))->first();
        }

        return $this->turmas()->wherePivot('is_atual', true)->first()
            ?? $this->turmas()->first();
    }

    /**
     * deficiências ou necessidades educacionais vinculadas ao aluno.
     */
    public function deficiencias(): BelongsToMany
    {
        return $this->belongsToMany(Deficiencia::class, 'aluno_deficiencia')
            ->withTimestamps();
    }

    /**
     * peis cadastrados para o aluno.
     */
    public function peis(): HasMany
    {
        return $this->hasMany(Pei::class, 'aluno_id')
            ->orderBy('ano_letivo', 'desc');
    }

    /**
     * pei mais recente do aluno por ano letivo.
     */
    public function peiMaisRecente()
    {
        return $this->hasOne(Pei::class, 'aluno_id')
            ->latestOfMany('ano_letivo');
    }
}
