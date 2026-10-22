<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TurmaDisciplina extends Model
{
    use HasFactory;

    protected $table = 'turma_disciplina';

    protected $fillable = [
        'turma_id',
        'disciplina_id',
    ];

    /**
     * turma à qual o vínculo pertence.
     */
    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class, 'turma_id');
    }

    /**
     * disciplina curricular associada à turma.
     */
    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class, 'disciplina_id');
    }

    /**
     * docentes atribuídos à disciplina na turma.
     */
    public function professores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'turma_disciplina_professor')
            ->withPivot('is_substituto')
            ->withTimestamps();
    }
}
