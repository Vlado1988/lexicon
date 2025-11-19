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

                    <h4 class="text-xl font-semibold">Add Words</h4>

                    <form class="my-4" action="{{ route('admin.vocabulary.store') }}" method="POST">
                        @csrf

                        <input type="hidden" name="lang_code" value="{{ request()->lang_code }}">
                        <div class="my-4">
                            <label for="language" class="block mb-1 cursor-pointer">Language</label>
                            <select name="language" id="language" class="border rounded w-full text-gray-900">
                                <option value="">-- Select --</option>
                                @foreach($languages as $language)
                                <option value="{{ $language->id }}" {{ strtolower($language->code) == $langCode || old('language') == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="my-4">
                            <label for="words" class="block mb-1 cursor-pointer">Words</label>
                            <textarea type="text" name="words" id="words" class="border rounded w-full text-gray-900" rows="5" placeholder="word 1, word 2, word 3"></textarea>
                            <span class="italic">(Separate words with ,)</span>
                        </div>

                        <button type="submit" class="btn btn-primary my-2 btn-md">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
