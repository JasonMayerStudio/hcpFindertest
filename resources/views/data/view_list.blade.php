@extends('layouts.admin_master')

@section('content')
    <h1>HCP</h1>
    @if(isset($types))
        <ul>
        @foreach ($types as $type)
            @if ($type == $selected_type)
                <li>{{$type}}</li>
            @else

                <li><a href="{{route('view_by_location_type', ['type' => $type])}}">{{$type}}</a></li>
            @endif
        @endforeach
        </ul>
    @endif

    @if (!is_array($hcps) && method_exists($hcps, 'render'))
        <div class="paginator">
            Total: {!! $hcps->total() !!}<br/>
            {!! $hcps->render() !!}
        </div>
    @endif

    <table class="hcp_list">
        @foreach ($hcps as $hcp)
            <tr>
                <td>
                    {{$hcp->hcp_id}}
                </td>
                <td>
                    {{$hcp->first_name}}
                    {{$hcp->m_name}}
                    {{$hcp->last_name}}
                    {{$hcp->suffix}}<br/>
                    {{$hcp->address_line1}}<br/>
                    @if ($hcp->address_line2!="")
                        {{{$hcp->address_line2}}}<br/>
                    @endif
                    {{$hcp->city}},
                    {{$hcp->state}}
                    {{$hcp->zip_code}}<br/>
                    {{$hcp->phone}}
                </td>
                @if(isset($hcp->location_type))
                    <td>
                        <span class="title">address:</span><br/>
                        {{$hcp->geocoded_address}}<br/>
                        <span class="title">google returned address</span><br/>
                        {{$hcp->formatted_address}}<br/>
                        <span class="title">location type:</span> <a
                                href="https://developers.google.com/maps/documentation/geocoding/#Results"
                                target="_blank">{{$hcp->location_type}}</a>

                    </td>
                @else
                    <td>
                        lat: {{$hcp->lat}} <br/>
                        long: {{$hcp->lng}}
                    </td>
                @endif
                <td>
                    <a href="{{route('view_hcp', ['id' => $hcp->hcp_id])}}">view detail</a>
                </td>
            </tr>
        @endforeach
    </table>
@endsection