<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Import') }}
        </h2>
    </x-slot>

    <div class="py-3 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="progressbar" id="importProgressbar">
                        <div class="progress_percentage">0%</div>
                        <div class="progress"></div>
                    </div>
                    <div class="my-2">
                        <p class="text-lg">Upload file (.csv, .json) to import new translations.</p>
                    </div>
                    <div class="body">
                        <form id="importForm" action="{{ route('admin.import.init') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="my-2">
                                <input type="file" name="file" id="" accept=".csv, .json">
                            </div>
                            <div class="my-2">
                                <label for="delimiter">Delimiter</label>
                                <input type="text" name="delimiter" id="delimiter" class="border rounded w-[50px] text-gray-900" maxlength="2">
                            </div>

                            <button type="submit" class="btn btn-primary my-2" id="importBtn">Import</button>
                        </form>

                        <div id="data_preview"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function () {
                importTranslations();
            });
        </script>
    @endpush
</x-app-layout>
