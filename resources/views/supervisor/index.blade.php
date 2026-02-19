@extends('layouts.app')

@section('title', 'Panel de Supervisor')

@section('content')
<div style="padding: 2rem; font-family: 'Poppins', sans-serif;">
    <h1 style="color: #333;">Panel de Supervisor</h1>
    <p>Bienvenido al área de supervisión.</p>
    
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
