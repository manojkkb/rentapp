@extends('storefront.shop-layout')

@section('title', $vendor->name.' — '.__('vendor.online_store'))

@section('content')
@include('storefront.themes.'.$theme['template'].'-content')
@endsection
