<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeiIntervencao extends Model
{
    use HasFactory;

    protected $table = 'pei_intervencoes';

    protected $fillable = [
        'pei_id',
        'demanda',
        'encaminhamento_estrategia',
        'responsavel',
        'ordem',
    ];

    /**
     * pei ao qual a proposta de intervenção pertence.
     */
    public function pei(): BelongsTo
    {
        return $this->belongsTo(Pei::class, 'pei_id');
    }
}
