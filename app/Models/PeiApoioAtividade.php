<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeiApoioAtividade extends Model
{
    use HasFactory;

    protected $table = 'pei_apoio_atividades';

    protected $fillable = [
        'pei_apoio_id',
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
     * equipe de apoio à qual a atividade pertence.
     */
    public function apoio(): BelongsTo
    {
        return $this->belongsTo(PeiApoio::class, 'pei_apoio_id');
    }
}
