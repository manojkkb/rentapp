@extends('vendor.store.layout')

@section('store-content')
    <div class="store-section-panel">
        @include('vendor.store.partials.'.$sectionPartial)
    </div>
@endsection

