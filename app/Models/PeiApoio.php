<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeiApoio extends Model
{
    use HasFactory;

    protected $table = 'pei_apoios';

    protected $fillable = [
        'pei_id',
        'nome',
        'ordem',
    ];

    /**
     * pei ao qual a equipe de apoio pertence.
     */
    public function pei(): BelongsTo
    {
        return $this->belongsTo(Pei::class, 'pei_id');
    }

    /**
     * responsáveis atribuídos à equipe de apoio nos trimestres.
     */
    public function responsaveis(): HasMany
    {
        return $this->hasMany(PeiApoioResponsavel::class, 'pei_apoio_id');
    }

    /**
     * atividades e ações trimestrais desenvolvidas pela equipe de apoio.
     */
    public function atividades(): HasMany
    {
        return $this->hasMany(PeiApoioAtividade::class, 'pei_apoio_id')
            ->orderBy('trimestre', 'asc')
            ->orderBy('ordem', 'asc');
    }
}
