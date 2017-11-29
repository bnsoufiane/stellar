<?php $user = \User::find(Sentry::getUser()->id); ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Stellar</title>
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">

    <!-- bootstrap framework -->
    <link href="{{asset('assets/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" media="all">
    <!-- jquery ui -->
    <link href="{{asset('assets/css/jquery-ui.css')}}" rel="stylesheet" media="all">

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

</head>
<body>
<div id="page_wrapper">
    <div id="main_wrapper" style="background-color: rgb(252, 252, 252)">

        <div id="select_program" title="Error" style="display: none;">
            <p>You should select at least one program that patients will be assigned to.</p>
        </div>

        @yield('content')
    </div>
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

<!-- Yukon Admin functions -->
<script src="{{asset('assets/js/yukon_all.js')}}"></script>
<script src="{{asset('assets/js/stellar.js')}}"></script>
<!-- page specific plugins -->

@yield('scripts')

</body>
</html>
