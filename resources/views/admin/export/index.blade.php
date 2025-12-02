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
                        <form id="exportForm" action="{{ route('admin.export.init') }}" method="POST">
                            @csrf

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

                        <div class="progressbar" id="exportProgressbar">
                            <div class="progress_percentage">0%</div>
                            <div class="progress"></div>
                        </div>

                        <div id="downloadBtnContainer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                sendExportForm();
            });
        </script>
    @endpush
</x-app-layout>
