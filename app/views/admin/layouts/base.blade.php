<?php $user = \User::find(Sentry::getUser()->id); ?>

        <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Stellar')</title>
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">

    <!-- jquery ui -->
    <link href="{{asset('assets/css/jquery-ui.css')}}" rel="stylesheet" media="all">
    <!-- bootstrap framework -->
    <link href="{{asset('assets/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" media="all">

    <!-- datatables -->
    <link href="{{asset('assets/lib/DataTables/media/css/jquery.dataTables.min.css')}}" rel="stylesheet" media="all">
    <link href="{{asset('assets/lib/DataTables/extensions/TableTools/css/dataTables.tableTools.min.css')}}"
          rel="stylesheet" media="all">

    <link href="{{asset('assets/lib/DataTables/media/css/jquery.dataTables.min.css')}}" rel="stylesheet" media="all">

    <!-- scrollbar -->
    <link rel="stylesheet" href="{{asset('assets/lib/date-range-picker/css/daterangepicker.css')}}">

    <!-- icon sets -->
    <!-- elegant icons -->
    <link href="{{asset('assets/icons/elegant/style.css')}}" rel="stylesheet" media="all">
    <!-- elusive icons -->
    <link href="{{asset('assets/icons/elusive/css/elusive-webfont.css')}}" rel="stylesheet" media="all">
    <!-- flags -->
    <link rel="stylesheet" href="{{asset('assets/icons/flags/flags.css')}}" media="all">
    <!-- scrollbar -->
    <link rel="stylesheet" href="{{asset('assets/lib/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css')}}">

    <!-- google webfonts -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans&amp;subset=latin,latin-ext' rel='stylesheet'
          type='text/css'>

    <!-- main stylesheet -->
    <link href="{{asset('assets/css/main.min.css')}}" rel="stylesheet" media="all" id="mainCss">
    <link href="{{asset('assets/css/stellar.css')}}" rel="stylesheet" media="all">

    <!-- print stylesheet -->
    <link href="{{asset('assets/css/print.css')}}" rel="stylesheet" media="print">
    <!-- jQuery -->
    <script src="{{asset('assets/js/jquery.min.js')}}"></script>
    <!-- moment.js (date library) -->
    <script src="{{asset('assets/js/moment-with-langs.min.js')}}"></script>

    <link rel="stylesheet" href="{{asset('assets/lib/bootstrap-select/bootstrap-select.min.css')}}">

</head>
<body class="side_menu_active side_menu_expanded">
<div id="page_wrapper">

    <!-- header -->
    <header id="main_header">
        <div class="container-fluid inheader">
            <div class="brand_section">
                <a href="{{ URL::route('index') }}"><img src="{{asset('assets/img/logo.png')}}" alt="site_logo"
                                                         width="123"></a>
            </div>
            <div class="header_user_actions dropdown">
                <a href="{{ URL::route('sign-out') }}" class="sign_out">Log Out</a>
            </div>

        </div>
    </header>


    @yield('breadcrumbs')

    <div id="main_wrapper">
        <div class="container-fluid" id="pcont">
            <?php
            $success = Session::get('success');
            $error = Session::get('error');
            ?>
            @if (isset($success))
                <div class="alert alert-success">
                    {{ $success }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                </div>
            @endif


            @if (isset($error))
                <div class="alert alert-danger">
                    {{ $error }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                </div>
            @endif
        </div>

        <div id="dialog-confirm" title="Delete this item?" style="display: none;">
            <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This item will be
                permanently deleted and cannot be recovered. Are you sure?</p>
        </div>

        <div id="dialog-confirm-visit" title="Delete this item?" style="display: none;">
            <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Do you want to delete
                this visit and all notes?</p>
        </div>

        <div id="couldnt_delete" title="Error" style="display: none;">
            <p>This item is already in use. It can not be deleted.</p>
        </div>

        @yield('content')
    </div>

    <!-- main menu -->
    <nav id="main_menu">
        <div class="menu_wrapper">
            @include('admin/layouts/side-bar')
        </div>
        <div class="menu_toggle">
            <span class="icon_menu_toggle">
                <i class="arrow_carrot-2left toggle_left"></i>
                <i class="arrow_carrot-2right toggle_right" style="display:none"></i>
            </span>
        </div>
    </nav>

</div>

<!-- Bootstrap Framework -->
<script src="{{asset('assets/bootstrap/js/bootstrap.min.js')}}"></script>
<!-- jQuery ui -->
<script src="{{asset('assets/js/jquery-ui.min.js')}}"></script>
<!-- jQuery Cookie -->
<script src="{{asset('assets/js/jqueryCookie.min.js')}}"></script>
<!-- retina images -->
<script src="{{asset('assets/js/retina.min.js')}}"></script>
<!-- switchery -->
<script src="{{asset('assets/lib/switchery/dist/switchery.min.js')}}"></script>
<!-- typeahead -->
<script src="{{asset('assets/lib/typeahead/typeahead.bundle.min.js')}}"></script>
<!-- fastclick -->
<script src="{{asset('assets/js/fastclick.min.js')}}"></script>
<!-- SimpleAjaxUploader -->
<script src="{{asset('assets/lib/SimpleAjaxUploader/SimpleAjaxUploader.js')}}"></script>
<!-- blockUI -->
<script src="{{asset('assets/lib/blockUI/jquery.blockUI.js')}}"></script>
<!-- match height -->
<script src="{{asset('assets/lib/jquery-match-height/jquery.matchHeight-min.js')}}"></script>
<!-- scrollbar -->
<script src="{{asset('assets/lib/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js')}}"></script>

<script src="{{asset('assets/lib/bootstrap-select/bootstrap-select.min.js')}}"></script>

<!-- Yukon Admin functions -->
<script src="{{asset('assets/js/yukon_all.js')}}"></script>
<script src="{{asset('assets/js/stellar.js')}}"></script>
<!-- page specific plugins -->

@yield('scripts')
</body>
</html>
<?php die(); ?>
