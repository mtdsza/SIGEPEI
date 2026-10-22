<?php

use App\Http\Controllers\AlunoController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\CalendarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeficienciaController;
use App\Http\Controllers\DisciplinaController;
use App\Http\Controllers\PeiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TurmaController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// redirecionamento da página inicial para o painel de controle
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// rotas autenticadas e verificadas do sistema
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // rotas restritas à equipe do nai e administradores
    Route::middleware('admin_or_nai')->group(function () {
        // gestão estrutural de planos educacionais individualizados
        Route::get('/peis/create', [PeiController::class, 'create'])->name('peis.create');
        Route::post('/peis', [PeiController::class, 'store'])->name('peis.store');
        Route::delete('/peis/{pei}', [PeiController::class, 'destroy'])->name('peis.destroy');
        Route::post('/peis/{pei}/ficha-aluno', [PeiController::class, 'updateFichaAluno'])->name('peis.update-ficha-aluno');
        Route::post('/peis/{pei}/ficha-pei', [PeiController::class, 'updateFichaPei'])->name('peis.update-ficha-pei');
        Route::post('/peis/{pei}/nai', [PeiController::class, 'updateNai'])->name('peis.update-nai');
        Route::post('/peis/{pei}/apoio', [PeiController::class, 'storeApoio'])->name('peis.store-apoio');
        Route::put('/peis/{pei}/apoio/{apoio}', [PeiController::class, 'updateApoio'])->name('peis.update-apoio');
        Route::delete('/peis/{pei}/apoio/{apoio}', [PeiController::class, 'destroyApoio'])->name('peis.destroy-apoio');

        // cadastro e gerenciamento de alunos e enturmação
        Route::get('/alunos/create', [AlunoController::class, 'create'])->name('alunos.create');
        Route::post('/alunos', [AlunoController::class, 'store'])->name('alunos.store');
        Route::get('/alunos/{aluno}/edit', [AlunoController::class, 'edit'])->name('alunos.edit');
        Route::put('/alunos/{aluno}', [AlunoController::class, 'update'])->name('alunos.update');
        Route::delete('/alunos/{aluno}', [AlunoController::class, 'destroy'])->name('alunos.destroy');
        Route::post('/alunos/{aluno}/associar-turma', [AlunoController::class, 'associarTurma'])->name('alunos.associar-turma');

        // cadastro e gerenciamento de turmas
        Route::get('/turmas/create', [TurmaController::class, 'create'])->name('turmas.create');
        Route::post('/turmas', [TurmaController::class, 'store'])->name('turmas.store');
        Route::get('/turmas/{turma}/edit', [TurmaController::class, 'edit'])->name('turmas.edit');
        Route::put('/turmas/{turma}', [TurmaController::class, 'update'])->name('turmas.update');
        Route::delete('/turmas/{turma}', [TurmaController::class, 'destroy'])->name('turmas.destroy');

        // cadastro e gerenciamento de deficiências
        Route::get('/deficiencias', [DeficienciaController::class, 'index'])->name('deficiencias.index');
        Route::get('/deficiencias/create', [DeficienciaController::class, 'create'])->name('deficiencias.create');
        Route::post('/deficiencias', [DeficienciaController::class, 'store'])->name('deficiencias.store');
        Route::get('/deficiencias/{deficiencia}/edit', [DeficienciaController::class, 'edit'])->name('deficiencias.edit');
        Route::put('/deficiencias/{deficiencia}', [DeficienciaController::class, 'update'])->name('deficiencias.update');
        Route::delete('/deficiencias/{deficiencia}', [DeficienciaController::class, 'destroy'])->name('deficiencias.destroy');

        // cadastro e gerenciamento de disciplinas
        Route::get('/disciplinas', [DisciplinaController::class, 'index'])->name('disciplinas.index');
        Route::get('/disciplinas/create', [DisciplinaController::class, 'create'])->name('disciplinas.create');
        Route::post('/disciplinas', [DisciplinaController::class, 'store'])->name('disciplinas.store');
        Route::get('/disciplinas/{disciplina}/edit', [DisciplinaController::class, 'edit'])->name('disciplinas.edit');
        Route::put('/disciplinas/{disciplina}', [DisciplinaController::class, 'update'])->name('disciplinas.update');
        Route::delete('/disciplinas/{disciplina}', [DisciplinaController::class, 'destroy'])->name('disciplinas.destroy');

        // gerenciamento do calendário escolar
        Route::get('/calendario', [CalendarioController::class, 'index'])->name('calendario.index');
        Route::put('/calendario', [CalendarioController::class, 'update'])->name('calendario.update');
        Route::delete('/calendario/{anoLetivo}', [CalendarioController::class, 'destroy'])->name('calendario.destroy');

        // auditoria de ações e registros do sistema
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('/auditoria/{id}', [AuditoriaController::class, 'show'])->name('auditoria.show');
    });

    // planos educacionais individualizados (consulta e preenchimento de atribuições)
    Route::get('/peis', [PeiController::class, 'index'])->name('peis.index');
    Route::get('/peis/{pei}', [PeiController::class, 'show'])->name('peis.show');
    Route::post('/peis/{pei}/disciplinas/{disciplina}/trimestre', [PeiController::class, 'updateDisciplinaTrimestre'])->name('peis.update-disciplina-trimestre');
    Route::post('/peis/{pei}/apoio/{apoio}/trimestre', [PeiController::class, 'updateApoioTrimestre'])->name('peis.update-apoio-trimestre');

    // consulta de discentes e turmas para acompanhamento docente
    Route::get('/alunos', [AlunoController::class, 'index'])->name('alunos.index');
    Route::get('/alunos/{aluno}', [AlunoController::class, 'show'])->name('alunos.show');
    Route::get('/alunos/{aluno}/foto', [AlunoController::class, 'foto'])->name('alunos.foto');
    Route::get('/turmas', [TurmaController::class, 'index'])->name('turmas.index');
    Route::get('/turmas/{turma}', [TurmaController::class, 'show'])->name('turmas.show');

    // gerenciamento de usuários do sistema
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/create', [UserController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{usuario}/edit', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{usuario}', [UserController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{usuario}', [UserController::class, 'destroy'])->name('usuarios.destroy');
});

// gerenciamento do perfil do usuário autenticado
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
