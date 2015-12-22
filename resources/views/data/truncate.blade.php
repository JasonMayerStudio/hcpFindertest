@extends('layouts.admin_master')

@section('content')
    <h1>HCP Data Import</h1>
    <p>Truncated: {{ $data['db'] }} </p>
    <p>pre count : {{ $data['pre_count'] }}</p>
    <p>post count : {{ $data['post_count'] }}</p>
@endsection