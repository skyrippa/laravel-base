@extends('emails.layouts.mail')

@section('template_title')
    Redefinição de senha aprovada
@endsection

@section('title')
    {{$name}}, você realizou a redefinição de sua senha.
@endsection

@section('text')
    A redefinição de sua senha foi feita com sucesso.
@endsection

@section('button_url')
    {{config('appconfig.app.url_site')}}
@endsection

@section('button_text')
    Entrar
@endsection


