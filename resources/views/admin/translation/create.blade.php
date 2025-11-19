<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Translations') }}
        </h2>
    </x-slot>

    <div class="py-3 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        <a href="{{ route('admin.translation.index', ['source_lang_id' => request()->source_lang_id, 'target_lang_id' => request()->target_lang_id]) }}" class="btn btn-light dark:text-gray-600"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    </div>

                    <h4 class="text-xl font-semibold">Create Translations</h4>

                    <form action="{{ route('admin.translation.store', ['source_lang_id' => request()->source_lang_id, 'target_lang_id' => request()->target_lang_id]) }}" method="POST">
                        @csrf

                        <div class="my-2 flex items-end gap-2">
                            <div class="select_container">
                                <label for="source_word_language" class="block mb-1 cursor-pointer">Source Word Language</label>
                                <select name="source_word_language" id="source_word_language" class="select_word_language border rounded w-full text-gray-900" data-word-type="source">
                                    <option value="">-- Select --</option>
                                    @foreach($languages as $language)
                                    <option value="{{ $language->id }}" {{ old('source_word_language') == $language->id || request()->source_lang_id == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="translate-y-[-7px]">
                                <span><i class="fa-solid fa-arrow-right"></i></span>
                            </div>

                            <div class="select_container">
                                <label for="target_word_language" class="block mb-1 cursor-pointer">Target Word Language</label>
                                <select name="target_word_language" id="target_word_language" class="select_word_language border rounded w-full text-gray-900" data-word-type="target">
                                    <option value="">-- Select --</option>
                                    @foreach($languages as $language)
                                    <option value="{{ $language->id }}" {{ old('target_word_language') == $language->id || request()->target_lang_id == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="my-2" id="source_word_container">
                            <label for="source_word" class="block mb-1 cursor-pointer">Source Word</label>
                            <input type="text" name="source_word" id="source_word" class="search_word_input border rounded w-full text-gray-900" autocomplete="off" value="{{ old('source_word') }}">
                            <ul class="search_results_list" data-for-input="source_word"></ul>
                        </div>

                        <div class="my-2">
                            <label for="target_word" class="block mb-1 cursor-pointer">Target Words</label>
                            <div class="target_words_container">
                                <div class="target_words_list">
                                    @if(session()->has('old_target_words'))
                                        @foreach (session()->get('old_target_words') as $old_target_word)
                                            <div class="target_word_list_item">
                                                <span class="target_word">{{ $old_target_word }}</span>
                                                <span class="delete_target_word"><i class="fa-solid fa-x"></i></span>
                                                <input type="hidden" name="target_words[]" value="{{ $old_target_word }}">
                                            </div>
                                        @endforeach
                                    @endif
                                    <div id="target_word_container" class="flex-1">
                                        <input type="text" id="target_word" class="search_word_input flex-1 min-w-[50px] appearance-none border-0 focus:ring-0 p-0 h-[24.4px] w-full text-gray-900" autocomplete="off">
                                        <ul class="search_results_list" data-for-input="target_word"></ul>
                                    </div>
                                </div>
                            </div>
                            <span class="italic">(Press enter or , to add word. For multiple words" separate words with ,)</span>
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                handleWordLanguageSelects();
                handleSearchWordInputFunctionality();
                handleTargetWordsInputFunctionality();
            });
        </script>
    @endpush
</x-app-layout>
