<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnoLetivo extends Model
{
    protected $table = 'anos_letivos';

    /**
     * campos de datas dos trimestres, na ordem cronológica esperada.
     */
    public const CAMPOS_DATAS = [
        'trimestre1_inicio',
        'trimestre1_fim',
        'trimestre2_inicio',
        'trimestre2_fim',
        'trimestre3_inicio',
        'trimestre3_fim',
    ];

    protected $fillable = [
        'ano',
        'concluido',
        ...self::CAMPOS_DATAS,
    ];

    protected function casts(): array
    {
        return [
            'ano' => 'integer',
            'concluido' => 'boolean',
            'trimestre1_inicio' => 'date:Y-m-d',
            'trimestre1_fim' => 'date:Y-m-d',
            'trimestre2_inicio' => 'date:Y-m-d',
            'trimestre2_fim' => 'date:Y-m-d',
            'trimestre3_inicio' => 'date:Y-m-d',
            'trimestre3_fim' => 'date:Y-m-d',
        ];
    }

    /**
     * serializa o ano letivo no formato consumido pela página de calendário.
     */
    public function toCalendarioArray(): array
    {
        $dados = [
            'id' => $this->id,
            'ano' => $this->ano,
            'concluido' => $this->concluido,
        ];

        foreach (self::CAMPOS_DATAS as $campo) {
            $dados[$campo] = $this->{$campo}?->format('Y-m-d') ?? '';
        }

        return $dados;
    }
}
