<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeiIdentificacao extends Model
{
    use HasFactory;

    protected $table = 'pei_identificacoes';

    protected $fillable = [
        'pei_id',
        'tipo',
        'descricao',
        'ordem',
    ];

    /**
     * pei ao qual a identificação pertence.
     */
    public function pei(): BelongsTo
    {
        return $this->belongsTo(Pei::class, 'pei_id');
    }
}
