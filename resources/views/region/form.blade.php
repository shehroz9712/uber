<x-master-layout :assets="$assets ?? []">
    <div>
        <?php $id = $id ?? null; ?>
        @if (isset($id))
            {!! Form::model($data, [
                'route' => ['region.update', $id],
                'method' => 'patch',
                'enctype' => 'multipart/form-data',
            ]) !!}
        @else
            {!! Form::open(['route' => ['region.store'], 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}
        @endif
        <div class="row">
            <div class="col-12">
                <a href="{{ route('region.index') }}" class="btn border-radius-10 btn-dark float-right" role="button"><i
                        class="fas fa-arrow-circle-left"></i> {{ __('message.back') }}</a>
            </div>
            <div class="col-lg-12 mt-3">
                <div class="card border-radius-20">
                    <div class="card-header d-flex justify-content-between"
                        style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="new-user-info">
                            <div class="row">
                                {{ Form::hidden('coordinates', old('coordinates'), ['id' => 'coordinates']) }}
                                <div class="form-group col-md-4">
                                    {{ Form::label('name', __('message.name') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                    {{ Form::text('name', old('name'), ['placeholder' => __('message.name'), 'class' => 'form-control', 'required']) }}
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('distance_unit', __('message.distance_unit') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                    {{ Form::select('distance_unit', ['km' => __('message.km'), 'mile' => __('message.mile')], old('distance_unit'), ['class' => 'form-control select2js', 'required']) }}
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('timezone', __('message.timezone'), ['class' => 'form-control-label']) }}
                                    {{ Form::select('timezone', [], old('timezone'), [
                                        'data-ajax--url' => route('ajax-list', ['type' => 'timezone']),
                                        'data-placeholder' => __('message.select_field', ['name' => __('message.timezone')]),
                                        'class' => 'form-control select2js',
                                        'required',
                                    ]) }}
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('status', __('message.status') . ' <span class="text-danger">*</span>', ['class' => 'form-control-label'], false) }}
                                    {{ Form::select('status', ['1' => __('message.active'), '0' => __('message.inactive')], old('status'), ['class' => 'form-control select2js', 'required']) }}
                                </div>
                                <div class="form-group col-md-8">
                                    {{ Form::label('status', 'Search Location', ['class' => 'form-control-label'], false) }}

                                    <input id="search-box" type="text" class="form-control mb-3"
                                        placeholder="Search location..." />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <img class="w-100 border-radius-10" src="{{ asset('images/region.gif') }}"
                                        alt="">
                                    <p><i class="far fa-hand-paper"></i> {{ __('message.drag_map_area') }} </p>
                                    <p>{{ __('message.connect_dot_draw_area') }} </p>
                                </div>
                                <div class="form-group col-md-8" style="height:500px;">

                                    <div id="map-canvas" class="border-radius-20" style="height: 100%;"></div>
                                    <p id="address-output" class="mt-2"></p>
                                </div>
                            </div>
                            <hr>
                            {{ Form::button(
                                '<span id="button-loader" style="display:none;"><div class="spinner-border spinner-border-sm text-light" role="status"></div></span> ' .
                                    __('message.save'),
                                [
                                    'type' => 'submit',
                                    'class' => 'btn border-radius-10 btn-success float-right',
                                    'id' => 'submit-btn',
                                ],
                            ) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    @section('bottom_script')
        <script>
            let map; // Global map variable
            let drawingManager; // Drawing manager
            let last_latlong = null; // Initialize last_latlong as null globally
            let geocoder; // Geocoder instance

            function initialize() {
                if (!google || !google.maps || !google.maps.places) {
                    console.error("Google Maps Places library not loaded properly.");
                    return;
                }

                geocoder = new google.maps.Geocoder();

                const myLatlng = new google.maps.LatLng(20.947940, 72.955786);
                const mapOptions = {
                    zoom: 13,
                    center: myLatlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };

                map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

                drawingManager = new google.maps.drawing.DrawingManager({
                    drawingMode: google.maps.drawing.OverlayType.POLYGON,
                    drawingControl: true,
                    drawingControlOptions: {
                        position: google.maps.ControlPosition.TOP_CENTER,
                        drawingModes: [google.maps.drawing.OverlayType.POLYGON]
                    },
                    polygonOptions: {
                        editable: true
                    }
                });

                drawingManager.setMap(map);

                const input = document.getElementById('search-box');
                if (!input) {
                    console.error("Search input box not found in the DOM.");
                    return;
                }

                const searchBox = new google.maps.places.SearchBox(input);

                map.addListener('bounds_changed', () => {
                    searchBox.setBounds(map.getBounds());
                });

                searchBox.addListener('places_changed', () => {
                    const places = searchBox.getPlaces();

                    if (places.length === 0) {
                        alert("No places found!");
                        return;
                    }

                    const bounds = new google.maps.LatLngBounds();
                    places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            alert("Place does not have a valid location.");
                            return;
                        }

                        // Clear the previous marker if it exists
                        if (last_latlong) last_latlong.setMap(null);

                        // Place a new marker
                        last_latlong = new google.maps.Marker({
                            position: place.geometry.location,
                            map: map
                        });

                        // Set coordinates to hidden input in the desired format
                        document.getElementById('coordinates').value =
                            `(${place.geometry.location.lat()}, ${place.geometry.location.lng()})`;

                        // Adjust the map bounds
                        if (place.geometry.viewport) bounds.union(place.geometry.viewport);
                        else bounds.extend(place.geometry.location);
                    });

                    map.fitBounds(bounds);
                });

                google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
                    if (last_latlong) {
                        last_latlong.setMap(null);
                    }

                    const coordinates = event.overlay.getPath().getArray().map((latLng) =>
                        `(${latLng.lat()}, ${latLng.lng()})`
                    ).join(',');

                    document.getElementById('coordinates').value = coordinates;
                    last_latlong = event.overlay;
                    auto_grow();
                });
            }

            function auto_grow() {
                const element = document.getElementById('coordinates');
                element.style.height = '5px';
                element.style.height = `${element.scrollHeight}px`;
            }

            function loadGoogleMapsScript() {
                const script = document.createElement('script');
                script.src =
                    `https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&libraries=places,drawing&callback=initialize`;
                script.async = true;
                script.defer = true;
                script.onerror = () => {
                    console.error("Failed to load Google Maps script.");
                };
                document.head.appendChild(script);
            }

            document.addEventListener("DOMContentLoaded", () => {
                loadGoogleMapsScript();
            });
        </script>
    @endsection
</x-master-layout>
