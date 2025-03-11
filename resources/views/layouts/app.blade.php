<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>{{ config('app.name') }}</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/jpg" href="">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css" integrity="sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog==" crossorigin="anonymous"/>

        <link href="{{ mix('css/app.css') }}" rel="stylesheet">
        
        <!-- SweetAlert2 -->
        <link rel="stylesheet" href="{{ asset('js/admin-lte/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">

        <!-- Toastr -->
        <link rel="stylesheet" href="{{ asset('js/admin-lte/plugins/toastr/toastr.min.css') }}">

        <!-- DataTables -->
        <link rel="stylesheet" href="{{ asset('js/admin-lte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('js/admin-lte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">

        <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
        
        <!-- Custom CSS -->
        <link rel="stylesheet" href="{{ asset('css/custom/custom.css') }}">

        @stack('third_party_stylesheets')

        @stack('page_css')
    </head>

    <body class="hold-transition sidebar-mini layout-fixed">
        
        <div class="wrapper">
            <!-- Main Header -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                    </li>
                </ul>

                
            </nav>

            <!-- Left side column. contains the logo and sidebar -->
            @include('layouts.sidebar')

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                @yield('content')
            </div>

            <!-- Main Footer -->
            <footer class="main-footer">
                <div class="float-right d-none d-sm-block">
                    <b>Version</b> 1.0.0
                </div>
                <strong>Copyright &copy; 2024-2025 <a href="#">{{ env('APP_NAME') }}</a>.</strong> All rights
                reserved.
            </footer>
        </div>

        <script src="{{ mix('js/app.js') }}"></script>

        <!-- SweetAlert2 -->
        <script src="{{ asset('js/admin-lte/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

        <!-- Toastr -->
        <script src="{{ asset('js/admin-lte/plugins/toastr/toastr.min.js') }}"></script>

        <script src="{{ asset('js/admin-lte/plugins/datatables/jquery.dataTables.min.js')}}"></script>
        <script src="{{ asset('js/admin-lte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
        <script src="{{ asset('js/admin-lte/plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
        <script src="{{ asset('js/admin-lte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
        <script src="{{ asset('js/admin-lte/plugins/jquery-validation/jquery.validate.min.js')}}"></script>
        <script src="{{ asset('js/admin-lte/plugins/jquery-validation/additional-methods.min.js')}}"></script>
        <script src="{{ asset('js/admin-lte/plugins/bs-custom-file-input/bs-custom-file-input.min.js')}}"></script>
        {{-- <script src="{{ asset('dist/js/adminlte.min.js')}}"></script> --}}
        {{-- <script src="{{ asset('dist/js/demo.js')}}"></script> --}}

        <script type="text/javascript">
            var base_url = "{{ url('/') }}";
            $(document).ready(function () {
                bsCustomFileInput.init();
            });
        </script>

        @stack('third_party_scripts')

        @stack('page_scripts')
    </body>
</html>
