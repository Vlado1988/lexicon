<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Languages') }}
        </h2>
    </x-slot>

    <div class="py-3 sm:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        <a href="{{ route('admin.language.index') }}" class="btn btn-light dark:text-gray-600"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    </div>

                    <h4 class="text-xl font-semibold">Create</h4>

                    <form class="my-4" action="{{ route('admin.language.store') }}" method="POST">
                        @csrf

                        <div class="my-4">
                            <label for="language" class="block mb-1 cursor-pointer">Language</label>
                            <input type="text" name="language" id="language" class="border rounded w-full text-gray-900">
                        </div>
                        <div class="my-4">
                            <label for="code" class="block mb-1 cursor-pointer">Code</label>
                            <input type="text" name="code" id="code" class="border rounded w-full text-gray-900">
                        </div>

                        <button type="submit" class="btn btn-primary my-2 btn-md">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
