<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeiApoioResponsavel extends Model
{
    use HasFactory;

    protected $table = 'pei_apoio_responsaveis';

    protected $fillable = [
        'pei_apoio_id',
        'trimestre',
        'user_id',
        'nome_externo',
    ];

    protected function casts(): array
    {
        return [
            'trimestre' => 'integer',
        ];
    }

    /**
     * equipe de apoio à qual o responsável está vinculado.
     */
    public function apoio(): BelongsTo
    {
        return $this->belongsTo(PeiApoio::class, 'pei_apoio_id');
    }

    /**
     * usuário do sistema associado como responsável, quando aplicável.
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
