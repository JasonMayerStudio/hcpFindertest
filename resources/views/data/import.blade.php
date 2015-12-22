@extends('layouts.admin_master')

@section('content')
    <h1>HCP Data Import</h1>
    <p>imported : {{ $data['file'] }}</p>
    <p>sql statment : {{ $data['sql'] }}</p>
@endsection