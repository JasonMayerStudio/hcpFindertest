<!DOCTYPE html>
<html>
    <head>
        <title>@yield('title')</title>
        <link rel="stylesheet" type="text/css" HREF="/assets/css/admin.css"  />
    </head>
    <body>
        <navigation>
            <ul>
                <li><a href="{{route('data_home')}}">Home</a></li>
                <li><a href="{{route('data_import')}}">Import</a></li>
                <li><a href="{{route('data_truncate')}}">Truncate</a></li>
                <li><a href="{{route('data_view_all')}}">View All</a></li>
                <li><a href="{{route('batch_geocode')}}">Geocode All</a></li>
                <li><a href="{{route('view_partial_matches')}}">Partial Matches</a></li>
                <li><a href="{{route('view_by_location_type')}}">By Location Type</a></li>

            </ul>
        </navigation>
        <div class="main">
            @yield('content')
        </div>
    </body>
</html>