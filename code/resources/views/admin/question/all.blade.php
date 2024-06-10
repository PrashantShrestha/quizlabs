@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="show-filter mb-3 text-end">
                <button type="button" class="btn btn-outline--primary showFilterBtn btn-sm"><i class="las la-filter"></i>
                    @lang('Filter')</button>
            </div>
            <div class="card responsive-filter-card mb-4">
                <div class="card-body">
                    <form action="">
                        <div class="d-flex flex-wrap gap-4">
                            <div class="flex-grow-1">
                                <label>@lang('Question')</label>
                                <input type="text" name="search" value="{{ request()->search }}" class="form-control">
                            </div>
                            <div class="flex-grow-1">
                                <label>@lang('Quiz Type')</label>
                                <select name="type_id" class="form-control quizType">
                                    <option value="">@lang('Any')</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}" @selected(request()->type_id == $type->id)>
                                            {{ __($type->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-grow-1">
                                <label>@lang('Category')</label>
                                <select class="form-control category" name="category_id">
                                    <option value="">@lang('Any')</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" data-category="{{ $category->subcategories }}"
                                            @selected(request()->category_id == $category->id)>
                                            {{ __(keyToTitle($category->name)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex-grow-1">
                                <label>@lang('Subcategory')</label>
                                <select class="form-control subcategory" name="sub_category_id">
                                    <option value="" disabled selected>@lang('Any')</option>
                                </select>
                            </div>

                            <div class="flex-grow-1 align-self-end">
                                <button class="btn btn--primary w-100 h-45"><i class="fas fa-filter"></i>
                                    @lang('Filter')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Question')</th>
                                    <th>@lang('Code')</th>
                                    <th>@lang('image')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($questions as $question)
                                    <tr>
                                        <td><span class="fw-bold">{{ __(strLimit($question->question, 40)) }}</span></td>
                                        <td>{{ $question->code }}</td>
                                        <td>
                                            <span>
                                                <img src="{{ getImage(getFilePath('question') . '/' . @$question->image) }}" class="question_img">
                                            </span>
                                        </td>
                                        <td>@php echo $question->statusBadge; @endphp</td>
                                        <td>
                                            <a href="{{ route('admin.question.edit', $question->id) }}" class="btn btn-sm btn-outline--primary ms-1">
                                                <i class="la la-pencil"></i> @lang('Edit')
                                            </a>
                                            @if ($question->status == Status::DISABLE)
                                                <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to enable this Question?')"
                                                    data-action="{{ route('admin.question.status', $question->id) }}">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to disable this Question?')"
                                                    data-action="{{ route('admin.question.status', $question->id) }}">
                                                    <i class="la la-eye-slash"></i> @lang('Disable')
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($questions->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($questions) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="importModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">@lang('Import Question')</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="la la-times" aria-hidden="true"></i>
                    </button>
                </div>
                <form method="post" action="{{ route('admin.question.csv.import') }}" id="importForm"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <div class="form-group">
                            <div class="alert alert-warning p-3" role="alert">
                                <p>
                                    @lang('The file you wish to upload has to be formatted as we provided template files.Any changes to these files will be considered as an invalid file format. Download links are provided below.')
                                </p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="fw-bold required" for="file">@lang('Select File')</label>
                            <input type="file" class="form-control" name="file" accept=".csv,.xlsx"
                                id="file">
                            <div class="mt-1">
                                <small class="d-block">
                                    @lang('Supported files:') <b class="fw-bold">@lang('csv, excel')</b>
                                </small>
                                <small>
                                    @lang('Download all of the template files from here')
                                    <a href="{{ asset('/assets/admin/file_template/all/sample.csv') }}" title=""
                                        class="text--primary" download="" data-bs-original-title="Download csv file"
                                        target="_blank">
                                        <b>@lang('csv'),</b>
                                    </a>
                                    <a href="{{ asset('/assets/admin/file_template/all/sample.xlsx') }}"
                                        title="" class="text--primary" download=""
                                        data-bs-original-title="Download excel file" target="_blank">
                                        <b>@lang('excel')</b>
                                    </a>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="Submit" class="btn btn--primary w-100 h-45">@lang('Upload')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.question.create') }}" class="btn btn-outline--primary h-45">
        <i class="la la-fw la-plus"></i>
        @lang('Add Question')
    </a>
    <button type="button" class="btn  btn-outline--info h-45 importBtn">
        <i class="las la-cloud-upload-alt"></i> @lang('Import')
    </button>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $('.importBtn').on('click', function() {
                    var modal = $('#importModal');
                    $('#importModal').modal('show');
                });

                let subcategory_id = `{{ request()->sub_category_id }}`;
                let categoryData = $('.category').find(':selected').data();
                let quizTypeData = $('.quizType').find(':selected').text().trim();

                let count = 0;
                if ((quizTypeData == 'Contest') && (count == 0)) {
                    count++;
                    $('.category').attr('disabled', true);
                    $('.subcategory').attr('disabled', true);
                }

                $('.quizType').on('change', function() {
                    let quizTypeData = $('.quizType').find(':selected').text().trim();
                    if (quizTypeData == 'Contest') {
                        $('.category').attr('disabled', true);
                        $('.subcategory').attr('disabled', true);
                    } else {
                        $('.category').attr('disabled', false);
                        $('.subcategory').attr('disabled', false);
                    }
                });

                if (!subcategory_id && categoryData) {
                    let html = `<option value="0" selected disabled>@lang('Any')</option>`;
                    $.each(categoryData.category, function(index, value) {
                        html += `<option value="` + value.id + `")>
                                ` + value.name + `</option>`;
                    });
                    $('.subcategory').html(html);
                }

                if (subcategory_id && categoryData) {
                    let html = `<option value="0" disabled>@lang('Any')</option>`;
                    $.each(categoryData.category, function(index, value) {
                        html += `<option value="` + value.id + `")>
                                ` + value.name + `</option>`;
                    });
                    $('.subcategory').html(html);
                    $(`.subcategory option[value="${subcategory_id}"]`).attr('selected', true);
                }

                $('.category').on('change', function() {
                    let data = $(this).find(':selected').data();
                    let index = $(this).find(':selected').val();
                    let html = `<option value="" disabled selected>@lang('Any')</option>`;
                    $.each(data.category, function(index, value) {
                        html += `<option value="` + value.id + `")>
                                            ` + value.name + `</option>`;
                    });
                    $('.subcategory').html(html);
                });
            });
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .question_img {
            height: 40px;
            width: 40px;
            border-radius: 50%;
        }
    </style>
@endpush
