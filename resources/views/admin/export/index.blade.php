<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Export') }}
        </h2>
    </x-slot>

    <div class="py-3 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="my-2">
                        <p class="text-lg">Select languages to export</p>
                    </div>
                    <div class="body">
                        <form id="loadFileForm" action="{{ route('admin.import.init') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            {{-- <div class="
                                lang-select-container
                                grid
                                sm:grid-cols-[max-content_50px_max-content]
                                w-fit
                                items-center
                                justify-center
                            ">
                                <div class="sm:order-1">
                                    <label for="source_word_language">source word language</label>
                                </div>
                                <div class="sm:order-2"></div>
                                <div class="order-5 sm:order-3">
                                    <label for="target_word_language">target word language</label>
                                </div>
                                <div class="flex flex-col order-3 sm:order-4">
                                    <select name="source_language" id="source_word_language" class="flex-1 min-w-[200px] text-gray-500">
                                        <option value="">-- Select --</option>
                                        @foreach ($languages as $language)
                                            <option value="{{ $language->id }}" {{ request()->source_language == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="text-center order-4 sm:order-5">
                                    <i class="fa-solid fa-arrow-right-long"></i>
                                </div>
                                <div class="flex flex-col order-6">
                                    <select name="target_language" id="target_word_language" class="flex-1 min-w-[200px] text-gray-500">
                                        <option value="">-- Select --</option>
                                        @foreach ($languages as $language)
                                            <option value="{{ $language->id }}" {{ request()->target_language == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}
                            <div class="flex flex-wrap justify-start items-center gap-2">
                                <div class="flex flex-col min-w-[200px] w-full sm:w-fit">
                                    <label for="source_word_language">source word language</label>
                                    <select name="source_language" id="source_word_language" class="text-gray-500">
                                        <option value="">-- Select --</option>
                                        @foreach ($languages as $language)
                                            <option value="{{ $language->id }}" {{ request()->source_language == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="text-center w-full sm:w-fit sm:mt-6">
                                    <i class="fa-solid fa-arrow-right-long"></i>
                                </div>

                                <div class="flex flex-col min-w-[200px] w-full sm:w-fit">
                                    <label for="target_word_language">target word language</label>
                                    <select name="target_language" id="target_word_language" class="text-gray-500">
                                        <option value="">-- Select --</option>
                                        @foreach ($languages as $language)
                                            <option value="{{ $language->id }}" {{ request()->target_language == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary my-2" id="loadBtn">Export</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                importTranslations();
                dragAndDropFunctionality();
            });
        </script>
    @endpush
</x-app-layout>
