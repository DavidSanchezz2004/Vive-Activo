@extends('layouts.app')

@section('title', 'Panel de Paciente')

@section('content')
<div style="padding: 2rem; font-family: 'Poppins', sans-serif;">
    <h1 style="color: #333;">Panel de Paciente</h1>
    <p>Bienvenido a tu panel personal.</p>
    
    <div style="margin-top: 20px;">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" style="padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Cerrar Sesión
            </button>
        </form>
    </div>
</div>
@endsection
