<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeiResponsavel extends Model
{
    use HasFactory;

    protected $table = 'pei_responsaveis';

    protected $fillable = [
        'pei_id',
        'user_id',
        'nome_externo',
        'secao',
    ];

    /**
     * pei ao qual o responsável está vinculado.
     */
    public function pei(): BelongsTo
    {
        return $this->belongsTo(Pei::class, 'pei_id');
    }

    /**
     * usuário do sistema vinculado como responsável, quando aplicável.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * retorna o nome formatado do responsável (usuário do sistema ou externo).
     */
    public function getNomeExibicaoAttribute(): string
    {
        if ($this->user) {
            return "{$this->user->name} (" . ($this->user->cargo ?? 'Usuário') . ")";
        }

        return $this->nome_externo ?? 'Responsável não informado';
    }
}
