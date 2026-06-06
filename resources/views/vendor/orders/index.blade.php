@extends('vendor.layouts.app')

@section('title', __('vendor.orders'))
@section('page-title', __('vendor.orders'))

@section('content')
    <livewire:vendor.orders.index />
@endsection
