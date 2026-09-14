<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" lang="{{app()->getLocale()}}" xml:lang="{{config('app.locale')}}" itemscope itemtype="http://schema.org/WebSite">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@lang('env-editor::env-editor.menuTitle')</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css"
          integrity="sha384-zCbKRCUGaJDkqS1kPbPd7TveP5iyJE0EjAuZQTgFLD2ylzuqKfdKlfG/eSrtxUkn"
          crossorigin="anonymous">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
          crossorigin="anonymous"
          referrerpolicy="no-referrer"/>

    @stack('styles')
</head>
<body>
<div id="app" class="container-fluid ">

    <div id="body-wrapper" class="py-5 px-2">
        <h2 class="mb-4">@stack('documentTitle')</h2>
        <main class="" role="main" itemprop="mainContentOfPage" itemscope itemtype="http://schema.org/Table">
            @yield('content')
        </main>
    </div>

</div>


<span class="javascripts">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
            integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.16/dist/vue.min.js"
            integrity="sha384-YVYXhPGIH/Gmcr0W5Rin4PcpcsG1a4pcdUUod1CnbDEJut7XiUaJtSlNKeRLJBPk"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-fQybjgWLrvvRgtW6bFlB7jaZrFsaBXjsOMm/tB9LTS58ONXgqbR9W8oWht/amnpF"
            crossorigin="anonymous"></script>


    @stack('scripts')

</span>
</body>
</html>
