@extends('vendor.layouts.app')

@section('title', __('vendor.items_management'))
@section('page-title', __('vendor.items'))

@section('content')
    <livewire:vendor.items.index />
@endsection
