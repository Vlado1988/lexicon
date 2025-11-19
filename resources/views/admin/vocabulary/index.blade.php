<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Vocabulary') }}
        </h2>
    </x-slot>

    <div class="py-3 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div>
                        <a href="{{ url('admin/vocabulary') }}" class="btn btn-light text-gray-900 {{ request()->route('lang_code') == '' ? 'btn-active' : '' }}">All</a>
                        @foreach ($languages as $language)
                            <a href="{{ url('admin/vocabulary/' . strtolower($language->code)) }}" class="btn btn-light text-gray-900 {{ request()->route('lang_code') == strtolower($language->code) ? 'btn-active' : '' }}">{{ $language->code }}</a>
                        @endforeach
                    </div>
                    <div class="flex justify-end">
                        <a class="btn btn-primary" href="{{ url(request()->lang_code != '' ? '/admin/vocabulary/' . request()->lang_code . '/create' : '/admin/vocabulary/create') }}"><i class="fa-solid fa-plus"></i> Create</a>
                    </div>
                    <div class="body">
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-app-layout>
