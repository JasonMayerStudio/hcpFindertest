@extends('layouts.admin_master')

@section('content')
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{{$api_key}}}" ></script>
    <h1>HCP Viewer</h1>

    <div>{{{$message}}}</div>
    @if ($hcp)
        <div>
            HCP ID:{{{$hcp->hcp_id}}} <br/>

            {{{$hcp->first_name}}}
            {{{$hcp->m_name}}}
            {{{$hcp->last_name}}}
            {{{$hcp->suffix}}}<br/>
            {{{$hcp->address_line1}}}<br/>
            @if ($hcp->address_line2!="")
                {{{$hcp->address_line2}}}<br/>
            @endif
            {{{$hcp->city}}},
            {{{$hcp->state}}}
            {{{$hcp->zip_code}}}<br/>
            {{{$hcp->phone}}}<br/>
            lat: {{{$hcp->lat}}} <br/>
            long: {{{$hcp->lng}}}
        </div>
        @if($geo)
            <div id="cache_object">
                geo_cache object:<br/>
                ID: {{{$geo->id}}}<br/>

                ADDRESS: {{{$geo->address}}}<br/>
                GEOCODED ADDRESS: {{{$geo->geocoded_address}}}<br/>
                LAT: {{{$geo->lat}}}<br/>
                LNG: {{{$geo->lng}}}<br/>
                LOCATION TYPE: {{{$geo->location_type}}}<br/>
                FORMATTED ADDRESS: {{{$geo->formatted_address}}}<br/>
                PARTIAL MATCH: {{{ $geo->partial_match==1?"true":"false"}}}
            </div>
            <div class="cache_action">
                {!! Form::open(array('route' => array( 'update_geocache', 'id' => $geo->id ) )) !!}
                {!! Form::hidden('hcp_id', $hcp->hcp_id) !!}
                {!! Form::text('address', $geo->geocoded_address, ['size' => '150']) !!}<br/>
                {!! Form::submit('Update Location') !!}
                {!! Form::close() !!}
            </div>
            <div id="map-canvas"></div>
            <script type="text/javascript">
                function initialize() {
                    var location = { lat: {{{$geo->lat}}}, lng: {{{$geo->lng}}} };
                    var mapOptions = {
                        center: location,
                        zoom: 14
                    };
                    var map = new google.maps.Map(document.getElementById('map-canvas'),
                            mapOptions);
                    var marker = new google.maps.Marker({
                        position: location,
                        title: '{{{$geo->address}}}',
                        animation: google.maps.Animation.DROP,
                    });
                    var contentString = '{{{$hcp->first_name}}} {{{$hcp->m_name}}} {{{$hcp->last_name}}} {{{$hcp->suffix}}}<br/>' +
                                '{{{$hcp->address_line1}}}<br/>' +
                    @if ($hcp->address_line2!="")
                        '{{{$hcp->address_line2}}}<br/>' +
                    @endif
                        '{{{$hcp->city}}}, {{{$hcp->state}}} {{{$hcp->zip_code}}}<br/>' +
                        '{{{$hcp->phone}}}';
                    var infowindow = new google.maps.InfoWindow({
                        content: contentString
                    });
                    marker.setMap(map);
                    google.maps.event.addListener(marker, 'click', function() {
                        infowindow.open(map,marker);
                    });
                }
                google.maps.event.addDomListener(window, 'load', initialize);
            </script>
        @else
            <div class="cache_action">
                {!! Form::open(array('route' => array( 'geolocate_hcp', 'id' => $hcp->hcp_id ) )) !!}
                {!! Form::submit('Geolocate') !!}
                {!! Form::close() !!}
            </div>
            <div class="cache_action">
                {!! Form::open(array('route' => array('new_geocache', 'id' => $hcp->hcp_id ) )) !!}
                {!! Form::hidden('hcp_id', $hcp->hcp_id) !!}
                {!! Form::text('address','', ['size' => '150']) !!}<br/>
                {!! Form::submit('Use this Location') !!}
                {!! Form::close() !!}
            </div>
        @endif
    @endif

@endsection