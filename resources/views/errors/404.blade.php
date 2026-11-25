@extends('errors.layout')

@section('code', '404')
@section('title', 'Página Não Encontrada')
@section('message', ($exception && $exception->getMessage()) ? $exception->getMessage() : 'O recurso ou página solicitada não foi encontrado.')

