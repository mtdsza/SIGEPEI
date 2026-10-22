<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pei extends Model
{
    use HasFactory;

    protected $table = 'peis';

    protected $fillable = [
        'codigo',
        'aluno_id',
        'turma_id',
        'ano_letivo',
        'data_elaboracao',
        'diagnostico',
        'tempo_livre',
        'pratica_esporte',
        'caracteristicas_discente',
        'organizacao_e_prazos',
        'dificuldade_interacao_social',
        'sugestoes_recursos_estrategias',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'ano_letivo' => 'integer',
            'data_elaboracao' => 'date:Y-m-d',
            'is_active' => 'boolean',
        ];
    }

    /**
     * discente associado ao pei.
     */
    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'aluno_id');
    }

    /**
     * turma associada ao pei.
     */
    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    /**
     * responsáveis cadastrados em todas as seções do pei.
     */
    public function responsaveis(): HasMany
    {
        return $this->hasMany(PeiResponsavel::class, 'pei_id');
    }

    /**
     * responsáveis cadastrados na ficha inicial do pei.
     */
    public function responsaveisFichaInicial(): HasMany
    {
        return $this->hasMany(PeiResponsavel::class, 'pei_id')
            ->where('secao', 'ficha_inicial');
    }

    /**
     * identificações de habilidades, dificuldades e fatores contextuais do pei.
     */
    public function identificacoes(): HasMany
    {
        return $this->hasMany(PeiIdentificacao::class, 'pei_id')
            ->orderBy('ordem', 'asc');
    }

    /**
     * propostas de intervenção pedagógica do pei.
     */
    public function intervencoes(): HasMany
    {
        return $this->hasMany(PeiIntervencao::class, 'pei_id')
            ->orderBy('ordem', 'asc');
    }

    /**
     * ações e atividades trimestrais do acompanhamento do nai.
     */
    public function naiAtividades(): HasMany
    {
        return $this->hasMany(PeiNaiAtividade::class, 'pei_id')
            ->orderBy('trimestre', 'asc')
            ->orderBy('ordem', 'asc');
    }

    /**
     * equipes de apoio vinculadas ao pei.
     */
    public function apoios(): HasMany
    {
        return $this->hasMany(PeiApoio::class, 'pei_id')
            ->orderBy('ordem', 'asc');
    }

    /**
     * planejamentos pedagógicos e avaliações trimestrais das disciplinas.
     */
    public function disciplinasTrimestres(): HasMany
    {
        return $this->hasMany(PeiDisciplina::class, 'pei_id')
            ->orderBy('trimestre', 'asc');
    }
}
