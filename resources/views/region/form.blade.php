<x-master-layout :assets="$assets ?? []">
    <div>
        <?php $id = $id ?? null;?>
        @if(isset($id))
            {!! Form::model($data, ['route' => ['region.update', $id], 'method' => 'patch' , 'enctype' => 'multipart/form-data']) !!}
        @else
            {!! Form::open(['route' => ['region.store'], 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}
        @endif
        <div class="row">
            <div class="col-12">
                <a href="{{route('region.index')}}" class="btn border-radius-10 btn-dark float-right" role="button"><i class="fas fa-arrow-circle-left"></i> {{ __('message.back') }}</a>
            </div>
            <div class="col-lg-12 mt-3">
                <div class="card border-radius-20">
                    <div class="card-header d-flex justify-content-between"  style="border-top-left-radius: 20px; border-top-right-radius: 20px;">
                        <div class="header-title">
                            <h4 class="card-title">{{ $pageTitle }}</h4>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="new-user-info">
                            <div class="row">
                                {{ Form::hidden('coordinates', old('coordinates'), [ 'id' => 'coordinates' ] ) }}
                                <div class="form-group col-md-4">
                                    {{ Form::label('name',__('message.name').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('name',old('name'),['placeholder' => __('message.name'),'class' =>'form-control','required']) }}
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('distance_unit',__('message.distance_unit').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                    {{ Form::select('distance_unit',[ 'km' => __('message.km') ,'mile' => __('message.mile') ], old('distance_unit') ,[ 'class' =>'form-control select2js','required']) }}
                                </div>
                                
                                <div class="form-group col-md-4">
                                    {{ Form::label('timezone', __('message.timezone'), ['class' => 'form-control-label']) }}
                                    {{ Form::select('timezone', [], old('timezone') , [
                                        'data-ajax--url' => route('ajax-list', [ 'type' => 'timezone' ]),
                                        'data-placeholder' => __('message.select_field', [ 'name' => __('message.timezone') ]),
                                        'class' =>'form-control select2js', 'required'
                                        ])
                                    }}
                                </div>
                                
                                <div class="form-group col-md-4">
                                    {{ Form::label('status',__('message.status').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                    {{ Form::select('status',[ '1' => __('message.active') ,'0' => __('message.inactive') ], old('status'), [ 'class' =>'form-control select2js','required']) }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <img class="w-100 border-radius-10" src="{{asset('images/region.gif')}}" alt="">
                                    <p><i class="far fa-hand-paper"></i> {{ __('message.drag_map_area') }} </p>
                                    <p>{{ __('message.connect_dot_draw_area') }} </p>
                                </div>
                                <div class="form-group col-md-8" style="height:500px;">
                                        <input id="map-search" type="text" class="form-control" placeholder="{{ __('message.search_location') }}">

                                    <div id="map-canvas" class="border-radius-20"></div>
                                </div>
                            </div>
                            <hr>
                            {{ Form::button('<span id="button-loader" style="display:none;"><div class="spinner-border spinner-border-sm text-light" role="status"></div></span> ' . __('message.save'), [
                                'type' => 'submit',
                                'class' => 'btn border-radius-10 btn-success float-right',
                                'id' => 'submit-btn'
                            ]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    @section('bottom_script')
    <script>
   $(document).ready(function () {
    let map;
    let drawingManager;
    let lastOverlay = null;

    function initializeMap() {
        const defaultLatLng = new google.maps.LatLng(20.947940, 72.955786);
        const mapOptions = {
            zoom: 13,
            center: defaultLatLng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };

        map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

        // Initialize Drawing Manager
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

        // Handle Overlay Completion
        google.maps.event.addListener(drawingManager, 'overlaycomplete', function (event) {
            if (lastOverlay) {
                lastOverlay.setMap(null); // Remove the previous overlay
            }
            lastOverlay = event.overlay;
            const coordinates = lastOverlay.getPath().getArray().map(coord => ({
                lat: coord.lat(),
                lng: coord.lng()
            }));
            $('#coordinates').val(JSON.stringify(coordinates));
        });

        // Search Box Initialization
        const input = document.getElementById('map-search');
        const searchBox = new google.maps.places.SearchBox(input);
        alert(searchBox);

        map.addListener('bounds_changed', function () {
            searchBox.setBounds(map.getBounds());
        });

        searchBox.addListener('places_changed', function () {
            const places = searchBox.getPlaces();
            if (places.length === 0) return;

            // Clear last overlay
            if (lastOverlay) lastOverlay.setMap(null);

            const bounds = new google.maps.LatLngBounds();
            places.forEach(place => {
                if (!place.geometry) return;

                // Create a marker for the search result
                new google.maps.Marker({
                    map,
                    position: place.geometry.location
                });

                bounds.extend(place.geometry.location);
            });

            map.fitBounds(bounds);
        });
    }

    // Initialize the map
    initializeMap();
});


    </script>
    @endsection
</x-master-layout>
