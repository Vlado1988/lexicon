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
                    <div class="flex justify-end">
                        <a class="btn btn-primary" href="{{ route('admin.translation.create', ['source_lang_id' => request()->source_lang_id, 'target_lang_id' => request()->target_lang_id]) }}" id="createTranslationBtn"><i class="fa-solid fa-plus"></i> Create Translations</a>
                    </div>
                    <div class="body">
                        <form>
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

                                <div class="translate-y-[-7px] cursor-pointer">
                                    <span class="switch_language_order text-2xl">
                                        &#11020;
                                    </span>
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
                        </form>
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

        <script>
            $(document).ready(function () {
                getDataOnLanguageSelect();
                switchLanguages();
            });
        </script>
    @endpush
</x-app-layout>
