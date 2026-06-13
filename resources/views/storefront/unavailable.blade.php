<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $vendor->name ?? __('vendor.online_store') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-dvh items-center justify-center bg-gray-50 p-4">
    <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
            <i class="fas fa-store text-xl" aria-hidden="true"></i>
        </div>
        <h1 class="text-lg font-bold text-gray-900">{{ __('vendor.store_public_unavailable_title') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('vendor.store_public_unavailable_body') }}</p>
        @isset($vendor)
            <p class="mt-4 text-sm font-medium text-gray-800">{{ $vendor->name }}</p>
        @endisset
        <a href="{{ route('welcome') }}" class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 hover:text-emerald-800">
            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
            {{ __('vendor.store_public_back_home') }}
        </a>
    </div>
</body>
</html>
