<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{csrf_token()}}">
    <meta name="shopify-domain" content="{{request()->get('shop')}}">
    <title>Avalara Tax Connector by HulkApps</title>
    <meta name="csrf-token" content="{{csrf_token()}}">
    <!--end::Page Vendors Styles -->
    <link rel="shortcut icon" href="/favicon.ico"/>

    <link rel="stylesheet" href="{{asset(mix('/css/app.css'), true)}}&version=1.2">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@voerro/vue-tagsinput@2.7.1/dist/style.css">
    <link href="https://assets.calendly.com/assets/external/widget.css" rel="stylesheet">


    <script>
        SHOP_DOMAIN = "{{request()->user()}}"
        console.log(SHOP_DOMAIN);
    </script>
</head>
<body>
<div id="app">
    <router-view></router-view>
</div>

<script src="{{asset(mix('/js/app.js'), true)}}&version=1.2"></script>
<script src="https://cdn.jsdelivr.net/npm/@voerro/vue-tagsinput@2.7.1/dist/voerro-vue-tagsinput.js"></script>
<script src="https://assets.calendly.com/assets/external/widget.js" type="text/javascript"></script>

</body>

</html>
