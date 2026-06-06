@extends('vendor.layouts.app')

@section('title', __('vendor.staff_management'))
@section('page-title', __('vendor.staff_management'))

@section('content')
    <livewire:vendor.staff.index />
@endsection
