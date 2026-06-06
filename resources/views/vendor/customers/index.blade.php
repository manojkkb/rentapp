@extends('vendor.layouts.app')

@section('title', __('vendor.customers_management'))
@section('page-title', __('vendor.customers'))

@section('content')
    <livewire:vendor.customers.index />
@endsection
