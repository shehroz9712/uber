<x-master-layout :assets="$assets ?? []">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch card-height">
                    <div class="card-header d-flex justify-content-between">
                        <div class="header-title">
                            <h4 class="card-title mb-0">{{ $pageTitle ?? ''}}</h4>
                        </div>
                        <div class="card-header-toolbar">
                            <?php echo $button; ?>
                            @if(isset($pdfbutton))
                                {!! $pdfbutton !!}
                            @endif
                            @if(isset($import_file_button))
                                {!! $import_file_button !!}
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="card-header-toolbar">
                            @include('app-language-setting.defaultkeyword.defaultkeyword-filter')

                            @if(isset($delete_checkbox_checkout))
                               {!! $delete_checkbox_checkout !!}
                            @endif
                        </div>
                        {{ $dataTable->table(['class' => 'table  w-100'],false) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('bottom_script')
       {{ $dataTable->scripts() }}
        <script>
            $(document).ready(function() {
                $(document).find('.select2Clear').select2({
                    width: '100%',
                    allowClear: true
                });

                $('#default_keyword_filter_form').on('submit', function(e) {
                    e.preventDefault();
                    var formData = $(this).serialize();
                    var table = $('#dataTableBuilder').DataTable();
                    table.ajax.url('{{ route("defaultkeyword.index") }}?' + formData).load();
                });
        
                $('#reset-filter-btn').on('click', function() {
                    $('#keyword_title').val('').trigger('change');
                    $('#screen_id').val('').trigger('change');
                    var table = $('#dataTableBuilder').DataTable();
                    table.ajax.url('{{ route("defaultkeyword.index") }}').load();
                });
            });
        </script>
    @endsection
</x-master-layout>
