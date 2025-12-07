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

        <script>
        (function() {
            const storedMode = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if(storedMode === 'dark' || (!storedMode && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
        </script>
    </head>
    <body class="font-sans antialiased">
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
                <div class="pt-6 pb-3 sm:py-12">
                    @include('admin.layouts.mode-toggler')
                    @include('admin.layouts.navbar')
                </div>
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

                $(document).on('submit', '.delete_language', function(e) {
                    e.preventDefault();

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
                                method: 'POST',
                                url: `/admin/language/check-relations`,
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content'),
                                    dataId,
                                },
                                success: function(response) {
                                    if(response.status === 'success') {
                                        console.log(response.relationsExists)
                                        if(response.relationsExists) {
                                            Swal.fire({
                                                title: "Are you sure?",
                                                text: "There are translations assigned to this language. All data related to this language will be lost. Do you want to proceed?",
                                                icon: "warning",
                                                showCancelButton: true,
                                                confirmButtonColor: "#3085d6",
                                                cancelButtonColor: "#d33",
                                                confirmButtonText: "Yes, delete it!"
                                                }).then((result) => {
                                                if (result.isConfirmed) {
                                                    deleteLanguage(dataId);
                                                }
                                            });
                                        }
                                        else {console.log('ja som tu')
                                            deleteLanguage(dataId);
                                        }
                                    }
                                    else if(response.status === 'error') {
                                        toastr.error(response.message);
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

                    function deleteLanguage(dataId) {
                        $.ajax({
                            method: 'DELETE',
                            url: `/admin/language/${dataId}`,
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
        </script>

        @stack('scripts')
    </body>
</html>
