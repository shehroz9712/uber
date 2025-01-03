<x-master-layout :assets="$assets ?? []">
    <div>
        <?php $id = $id ?? null;?>
        @if(isset($id))
            {!! Form::model($data, ['route' => ['document.update', $id], 'method' => 'patch' ]) !!}
        @else
            {!! Form::open(['route' => ['document.store'], 'method' => 'post' ]) !!}
        @endif
        <div class="row">
            <div class="col-12">
                <a href="{{route('document.index')}}" class="btn border-radius-10 btn-dark float-right" role="button"><i class="fas fa-arrow-circle-left"></i> {{ __('message.back') }}</a>
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
                                <div class="form-group col-md-4">
                                    {{ Form::label('name', __('message.name').' <span class="text-danger">*</span>',['class' => 'form-control-label'], false ) }}
                                    {{ Form::text('name', old('name'),[ 'placeholder' => __('message.name'),'class' =>'form-control','required']) }}
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('is_required',__('message.is_required'), [ 'class'=>'form-control-label' ]) }}
                                    {{ Form::select('is_required',[ '0' => __('message.no'), '1' => __('message.yes') ], old('is_required'), [ 'class' =>'form-control select2js','required']) }}
                                </div>
                                
                                <div class="form-group col-md-4">
                                    {{ Form::label('has_expiry_date',__('message.has_expiry_date'), [ 'class' => 'form-control-label' ]) }}
                                    {{ Form::select('has_expiry_date',[ '0' => __('message.no'), '1' => __('message.yes') ], old('has_expiry_date'), [ 'class' =>'form-control select2js','required']) }}
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('status',__('message.status'), [ 'class'=>'form-control-label' ]) }}
                                    {{ Form::select('status',[ '1' => __('message.active'), '0' => __('message.inactive') ], old('status'), [ 'class' =>'form-control select2js','required']) }}
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
    @endsection
</x-master-layout>
