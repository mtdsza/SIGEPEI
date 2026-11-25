@extends('errors.layout')

@section('code', '422')
@section('title', 'Dados Inválidos')
@section('message', ($exception && $exception->getMessage()) ? $exception->getMessage() : 'Os dados enviados não puderam ser processados. Verifique as informações e tente novamente.')

