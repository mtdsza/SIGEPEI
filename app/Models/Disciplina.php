<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Disciplina extends Model
{
    use HasFactory;

    protected $table = 'disciplinas';

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
     * turmas vinculadas a esta disciplina na matriz curricular.
     */
    public function turmas(): BelongsToMany
    {
        return $this->belongsToMany(Turma::class, 'turma_disciplina')
            ->withPivot('id')
            ->withTimestamps();
    }
}
