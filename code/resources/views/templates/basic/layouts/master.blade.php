<!doctype html>
<html lang="{{ config('app.locale') }}" itemscope itemtype="http://schema.org/WebPage">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title> {{ $general->siteName(__($pageTitle)) }}</title>
    <link href="{{ asset('assets/global/css/bootstrap.min.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}" />

    <link rel="stylesheet" href="{{ asset($activeTemplateTrue . 'css/custom.css') }}">
    @stack('style-lib')

    @stack('style')


    <style>
        :root {
            --main: 229, 67, 34;
        }

        body {
            background-color: #f6f6f6;
        }

        .page-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-flow: column;
            background-color: #f6f6f6;
            min-height: 100vh;
        }

        .page-wrapper>div,
        .page-wrapper>section {
            width: 100%;
        }

        .navbar {
            width: 100%;
        }

        .custom--card {
            box-shadow: 0 3px 35px rgba(0, 0, 0, .1);
            border: 0;

        }


        .custom--card .card-header {
            padding: 13px 25px;
            background-color: transparent;
            border: 0;
            border-bottom: 2px solid rgb(232, 230, 230);
        }

        .custom--card .card-footer {
            background: #fff
        }

        .custom--card .card-header .title {
            margin-bottom: 0;
            color: #fff
        }

        .custom--card .card-body {
            padding: 25px;
            border: 0;
        }

        .pagination {
            margin-bottom: 0px;
        }

        .custom--card .card-footer p {
            margin-bottom: 0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            font-size: 15px;
            font-weight: 500;
            color: #555;
        }

        .form--control {
            border-width: 2px;
            border-color: #dce1e6;
        }

        input.form--control,
        select.form--control {
            height: 45px;
        }

        .form--control:focus {
            border-color: rgb(var(--main));
            box-shadow: 0 0 25px rgba(var(--main), 0.071);
            outline: 0;
        }

        .forgot-pass {
            font-size: 14px;
        }

        .btn--base {
            color: #fff;
            background-color: rgb(var(--main)) !important;
        }

        .btn--base:hover {
            color: #fff;
        }

        .btn {
            padding: 12px 30px;
            font-weight: 500;
        }

        /* table Css */
        .custom--table {
            background-color: #fff;
        }

        .custom--table thead {
            background-color: rgb(var(--main))
        }

        .custom--table thead tr th {
            color: #fff
        }

        .custom--table tbody tr td,
        .custom--table thead tr th {
            vertical-align: middle;
            padding: 10px 20px
        }

        .custom--table tbody tr td:last-child {
            text-align: right
        }

        .custom--table thead tr th:last-child {
            text-align: right
        }

        .custom--table tbody tr:last-child {
            border-bottom: none;
            border-bottom: 1px solid rgb(255, 255, 255);
        }

        .btn-sm {
            padding: 0.25rem 0.5rem !important;
        }

        .navbar-brand img {
            max-width: 220px;
        }

        label.required:after {
            content: '*';
            color: #DC3545 !important;
            margin-left: 2px;
        }


        .badge--pending,
        .badge--warning,
        .badge--success,
        .badge--primary,
        .badge--danger,
        .badge--dark {
            border-radius: 999px;
            padding: 2px 15px;
            position: relative;
            border-radius: 999px;
            -webkit-border-radius: 999px;
            -moz-border-radius: 999px;
            -ms-border-radius: 999px;
            -o-border-radius: 999px;
        }

        .badge--warning {
            background-color: rgba(255, 159, 67, 0.1);
            border: 1px solid #ff9f43;
            color: #ff9f43;
        }

        .badge--success {
            background-color: rgba(40, 199, 111, 0.1);
            border: 1px solid #28c76f;
            color: #28c76f;
        }

        .badge--danger {
            background-color: rgba(234, 84, 85, 0.1);
            border: 1px solid #ea5455;
            color: #ea5455;
        }

        .badge--primary {
            background-color: rgba(115, 103, 240, 0.1);
            border: 1px solid #4634ff;
            color: #4634ff;
        }

        .badge--dark {
            background-color: rgba(0, 0, 0, 0.1);
            border: 1px solid #000000;
            color: #000000;
        }

        .btn:focus {
            outline: 0;
            box-shadow: 0 0 0 0rem rgba(13,110,253,.25);
        }
    </style>

    <link rel="stylesheet"
        href="{{ asset($activeTemplateTrue . 'css/color.php') }}?color={{ $general->base_color }}&secondColor={{ $general->secondary_color }}">
</head>

<body>
    <div class="page-wrapper">
        @yield('content')
    </div>


    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="{{ asset('assets/global/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset($activeTemplateTrue . 'js/jquery.validate.js') }}"></script>

    @stack('script-lib')

    {{-- @include('partials.notify') --}}

    {{-- @include('partials.plugins') --}}


    @stack('script')


    <script>
        (function($) {
            "use strict";
            $(".langSel").on("change", function() {
                window.location.href = "{{ route('home') }}/change/" + $(this).val();
            });

        })(jQuery);
    </script>


    <script>
        (function($) {
            "use strict";

            $('form').on('submit', function() {
                if ($(this).valid()) {
                    $(':submit', this).attr('disabled', 'disabled');
                }
            });

            var inputElements = $('[type=text],[type=password],select,textarea');
            $.each(inputElements, function(index, element) {
                element = $(element);
                element.closest('.form-group').find('label').attr('for', element.attr('name'));
                element.attr('id', element.attr('name'))
            });

            $.each($('input, select, textarea'), function(i, element) {

                if (element.hasAttribute('required')) {
                    $(element).closest('.form-group').find('label').addClass('required');
                }

            });

            $('.showFilterBtn').on('click', function() {
                $('.responsive-filter-card').slideToggle();
            });


            Array.from(document.querySelectorAll('table')).forEach(table => {
                let heading = table.querySelectorAll('thead tr th');
                Array.from(table.querySelectorAll('tbody tr')).forEach((row) => {
                    Array.from(row.querySelectorAll('td')).forEach((colum, i) => {
                        colum.setAttribute('data-label', heading[i].innerText)
                    });
                });
            });

        })(jQuery);
    </script>
</body>
</html>
