@extends('vendor.layouts.app')

@section('title', __('vendor.categories'))
@section('page-title', __('vendor.categories'))

@section('content')
    <livewire:vendor.categories.index />
@endsection
