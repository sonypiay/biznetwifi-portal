<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@lang('metaheader.description')">
    <meta name="keywords" content="@lang('metaheader.keywords')" />
    <link rel="shortcut icon" href="{{ asset('images/logo/logo_biznet_wifi.ico') }}">
    <link rel="stylesheet" href="{{ asset('vendor/uikit/css/uikit.min.css') }}" media="screen" />
    <link rel="stylesheet" href="{{ asset('css/connected.css') }}" />
    <script src="{{ asset('vendor/uikit/js/uikit.min.js') }}"></script>
    <script src="{{ asset('vendor/uikit/js/uikit-icons.min.js') }}"></script>
    <script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','http://biznethotspot.qeon.co.id/js/analytic/biznet-analytics.js','ga');
        ga('create', 'UA-54510905-1', 'auto');
        ga('send', 'pageview');
    </script>
    <title>BiznetWifi | Layanan Wi-Fi Gratis dari Biznet</title>
</head>
<body>
    <div class="uk-container">
        <div class="uk-width-3-4 uk-align-center">
            <div class="uk-margin-large-top">
                <img class="uk-width-1-4@xl uk-width-1-4@l uk-width-1-3@m uk-width-1-3@s uk-align-center" src="{{ asset('images/logo/biznetwifi_primary.png') }}" alt="">
            </div>
            <hr class="uk-divider-icon">
            <div class="uk-card uk-card-body uk-card-small enjoy">
                <div class="uk-card-title uk-margin enjoy-heading">YOU ARE CONNECTED</div>
                <p>
                Enjoy the free Wi-Fi service from Biznet Hotspot for the next 30 minutes. <br>
                You can always reconnect to Biznet Hotspot by clicking again the Start Now button.
                </p>
            </div>
            <hr class="uk-divider-icon">
        </div>
    </div>
</body>
</html>