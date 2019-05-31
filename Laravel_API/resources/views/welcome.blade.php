<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <div id="app"></div>
    <script>
        window.Laravel =
            <?php echo json_encode([
                'csrfToken' => csrf_token(),
            ]); ?>
    </script>
    <script src="{{ mix('/js/app.js') }}" async></script>
    <script type="text/javascript" async>
        var file = location.pathname.split("/").pop();
        var link = document.createElement("link");
        link.href = "{{ asset('css/app.css') }}";
        link.type = "text/css";
        link.rel = "stylesheet";
        link.media = "screen,print";
        document.getElementsByTagName("head")[0].appendChild(link);
    </script>
</body>

</html>