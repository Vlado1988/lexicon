<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Words') }}
        </h2>
    </x-slot>

    <div class="py-3 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        <a href="{{ request()->route('lang_code') ? url('/admin/vocabulary/' . request()->route('lang_code')) : route('admin.vocabulary.index') }}" class="btn btn-light dark:text-gray-600"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    </div>

                    <h4 class="text-xl font-semibold">Edit Words</h4>

                    <form class="my-4" action="{{ request()->route('lang_code') ? url('/admin/vocabulary/' . request()->route('lang_code') . '/' . $editWord->id . '/update') : route('admin.vocabulary.update', $editWord->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="lang_code" value="{{ request()->lang_code }}">
                        <div class="my-4">
                            <label for="language" class="block mb-1 cursor-pointer">Language</label>
                            <select name="language" id="language" class="border rounded w-full text-gray-900">
                                <option value="">-- Select --</option>
                                @foreach($languages as $language)
                                <option value="{{ $language->id }}" {{ strtolower($language->code) == $langCode || $editWord->lang_id == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="my-4">
                            <label for="word" class="block mb-1 cursor-pointer">Word</label>
                            <input type="text" name="word" id="word" class="border rounded w-full text-gray-900" value="{{ @$editWord->word }}">
                        </div>

                        <button type="submit" class="btn btn-primary my-2 btn-md">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
