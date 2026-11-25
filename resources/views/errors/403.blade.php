@extends('errors.layout')

@section('code', '403')
@section('title', 'Acesso Negado')
@section('message', ($exception && $exception->getMessage()) ? $exception->getMessage() : 'Você não tem permissão para acessar este recurso.')

