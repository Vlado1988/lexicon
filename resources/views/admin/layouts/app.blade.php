<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeSwitcher()" x-bind:class="theme">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- FontAwesome Styles -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
        <!-- DataTables Styles -->
        <link rel="stylesheet" href="//cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
        <!-- Toastr Styles -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

        <!-- JQuery -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <!-- Scripts -->
        @vite(['resources/css/admin/app.css', 'resources/js/admin/app.js'])
    </head>
    <body class="font-sans antialiased">
        <button @click="toggleTheme()" class="p-2 rounded bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800">
            <span x-text="modeText"></span>
            <span x-text="modeIcon"></span>
        </button>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('admin.layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="sm:flex">
                <aside>
                    @include('admin.layouts.navbar')
                </aside>
                <article class="flex-1 overflow-hidden">
                    {{ $slot }}
                </article>
            </main>
        </div>

        <!-- FontAwesome Scripts -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/js/all.min.js" data-fa-i2svg="false"></script>
        <!-- DataTables Scripts -->
        <script src="//cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
        <!-- Toastr Scripts -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <!-- SweetAlert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @if ($errors->any())
        <script>
            @foreach ($errors->all() as $error)
                toastr.error(@json($error));
            @endforeach
        </script>
        @endif

        <script>
            @if(session('success'))
                toastr.success("{{ session('success') }}");
            @endif

            @if(session('error'))
                toastr.error("{{ session('error') }}");
            @endif

            @if(session('info'))
                toastr.info("{{ session('info') }}");
            @endif

            @if(session('warning'))
                toastr.warning("{{ session('warning') }}");
            @endif
        </script>

        <script>
            function themeSwitcher() {
                return {
                    theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
                    // modeText: localStorage.getItem('theme') === 'light' ? 'Dark Mode' : 'Light Mode',
                    // modeIcon: localStorage.getItem('theme') === 'light' ? 'fa-regular fa-moon' : 'fa-regular fa-sun',
                    modeText: '',
                    modeIcon: '',

                    init() {
                        this.setModeValues();
                        document.documentElement.classList.toggle('dark', this.theme === 'dark');
                    },

                    toggleTheme() {
                        this.theme = this.theme === 'dark' ? 'light' : 'dark';
                        localStorage.setItem('theme', this.theme);

                        document.documentElement.classList.toggle('dark', this.theme === 'dark');
                        this.setModeValues();
                    },

                    setModeValues() {
                        if(this.theme === 'dark') {
                            this.modeText = 'Light Mode';
                            this.modeIcon =  '☀️';
                        } else {
                            this.modeText = 'Dark Mode';
                            this.modeIcon = '🌙';
                        }
                    }
                }
            }
        </script>

        <script type="modul">
            import Swal from 'sweetalert2';
            const Swal = require('sweetalert2');
        </script>

        <script>
            $(document).ready(function() {
                $(document).on('change', '.change_status', function() {
                    const url = $(this).data('url');
                    const status = $(this).prop('checked');
                    const id = $(this).data('id');

                    $.ajax({
                        method: 'POST',
                        url: url,
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            status,
                            id
                        },
                        success: function(data) {
                            if(data.status === 'success') {
                                toastr.success(data.message);
                            }
                            else if(data.status === 'error') {
                                toastr.error(data.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            toastr.error(error);
                        }
                    });
                });

                $(document).on('submit', '.delete_item', function(e) {
                    e.preventDefault();

                    const url = $(this).attr('action');
                    const dataId = $(this).data('id');

                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, delete it!"
                        }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                method: 'DELETE',
                                url: url,
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    dataId,
                                },
                                success: function(response) {
                                    if(response.status === 'success') {
                                        Swal.fire({
                                            title: "Deleted!",
                                            text: response.message,
                                            icon: "success"
                                        }).then((result) => {
                                            window.location.reload();
                                        });
                                    }
                                    else if(response.status === 'error') {
                                        Swal.fire({
                                            title: "ERROR!",
                                            text: response.message,
                                            icon: "error"
                                        });
                                    }
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire({
                                        title: "ERROR!",
                                        text: error,
                                        icon: "error"
                                    });
                                }
                            });
                        }
                    });
                });
            });
        </script>

        @stack('scripts')
    </body>
</html>
