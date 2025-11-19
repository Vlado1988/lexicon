<div class="pt-6 pb-3 sm:py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <nav>
                    <ul class="text-gray-900 dark:text-gray-100">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="block rounded py-2 px-4 transition duration-300 hover:bg-gray-300 dark:hover:bg-gray-600">Dashboard</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.language.index') }}" class="block rounded py-2 px-4 transition duration-300 hover:bg-gray-300 dark:hover:bg-gray-600">Languages</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.vocabulary.index') }}" class="block rounded py-2 px-4 transition duration-300 hover:bg-gray-300 dark:hover:bg-gray-600">Vocabulary</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.translation.index') }}" class="block rounded py-2 px-4 transition duration-300 hover:bg-gray-300 dark:hover:bg-gray-600">Translations</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.import.index') }}" class="block rounded py-2 px-4 transition duration-300 hover:bg-gray-300 dark:hover:bg-gray-600">Import</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
