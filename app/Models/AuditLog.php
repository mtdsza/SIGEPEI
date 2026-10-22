<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'user_cargo',
        'modulo',
        'acao',
        'entidade_tipo',
        'entidade_id',
        'descricao',
        'ip_address',
        'user_agent',
        'dados_anteriores',
        'dados_novos',
    ];

    protected $casts = [
        'dados_anteriores' => 'array',
        'dados_novos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * usuário autor do registro de auditoria.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
