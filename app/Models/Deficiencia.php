<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Deficiencia extends Model
{
    use HasFactory;

    protected $table = 'deficiencias';

    protected $fillable = [
        'codigo',
        'nome',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * alunos vinculados a esta deficiência ou necessidade educacional.
     */
    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Aluno::class, 'aluno_deficiencia')
            ->withTimestamps();
    }
}
