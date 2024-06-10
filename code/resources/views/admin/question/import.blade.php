@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <form action="{{ route('admin.question.import.update') }}" method="POST">
                    @csrf

                    <input type="hidden" name="quizInfo_id" value="{{ @$id }}">

                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mt-xl-0 mt-4">
                                <div class="form-group select2-parent position-relative">
                                    <label>@lang('Select Questions')</label>
                                    <select name="question[]" class="form-control select2-auto-tokenize" id="selectQuestion"
                                        multiple="multiple" required>

                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Import')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ $backUrl }}" />
@endpush


@push('script')
    <script>
        (function($) {
            "use strict";
            $('.select2-auto-tokenize').select2({
                dropdownParent: $('.select2-parent'),
                tags: true,
                tokenSeparators: [',']
            });

            $('#selectQuestion').select2({
                ajax: {
                    url: `{{ route('admin.question.import', $id) }}`,
                    type: "get",
                    dataType: 'json',
                    delay: 1000,
                    data: function (params) {
                        return {
                            search: params.term,
                            page: params.page
                        };
                    },
                    processResults: function (response, params) {
                        params.page = params.page || 1;
                        let data = response.questions.data;
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.code + ' - ' + item.question,
                                    id: item.id
                                }
                            }),
                            pagination: {
                                more: response.more
                            }
                        };
                    },
                    cache: false
                },
            });
        })(jQuery);
    </script>
@endpush
