@extends('emails.layouts.mail')

@section('template_title')
    Confirmação de cadastro
@endsection

@section('title')
    Seja bem vindo ao Sistema, {{$name}}!
@endsection

@section('text')
    Geramos a senha abaixo pra você, porém ela <br>vai servir somente  para o primeiro acesso.<br>
    Logo após realizar o login, você poderá colocar a senha que preferir.
@endsection

@section('token')
    {{$password}}
@endsection

