@extends('emails.layouts.mail')

@section('template_title')
    Redefinição de senha
@endsection

@section('title')
    {{$name}}, você solicitou a redefinição de sua senha
@endsection

@section('text')
    Um link exclusivo para redefinir seu a senha foi gerada para você.<br>
    Para redefinir, click no link:
@endsection

@section('button_url')
    {{config('appconfig.app.url_site') . "/reset-password?token=". $token }}
@endsection

@section('button_text')
    Resetar senha
@endsection


