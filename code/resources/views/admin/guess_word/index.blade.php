@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Category')</th>
                                    <th>@lang('Subcategory')</th>
                                    <th>@lang('Level')</th>
                                    <th>@lang('Winning Mark')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quizInfos as $quizInfo)
                                    <tr>
                                        <td>{{ __(@$quizInfo->category->name) }}</td>
                                        <td>{{ __(@$quizInfo->subcategory->name) ?? 'N/A' }}</td>
                                        <td>{{ @$quizInfo->level->level }}</td>
                                        <td>{{ $quizInfo->winning_mark }}%</td>
                                        <td>@php echo $quizInfo->statusBadge; @endphp</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline--primary editBtn"
                                                data-title="@lang('Edit General Quiz')"
                                                data-category="{{ $quizInfo->category->id }}"
                                                data-subcategory = "{{ @$quizInfo->sub_category_id }}"
                                                data-level="{{ @$quizInfo->level->id }}"
                                                data-general_quiz_id="{{ $quizInfo->id }}"
                                                data-winning_mark="{{ $quizInfo->winning_mark }}">
                                                <i class="la la-pencil"></i> @lang('Edit')
                                            </button>

                                            <a href="{{ route('admin.guess.list', $quizInfo->id) }}"
                                                class="btn btn-sm btn-outline--info ms-1">
                                                <i class="las la-question"></i>
                                                @lang('Questions')
                                            </a>

                                            @if ($quizInfo->status == Status::DISABLE)
                                                <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to enable this Question?')"
                                                    data-action="{{ route('admin.guess.status', $quizInfo->id) }}">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to disable this Question?')"
                                                    data-action="{{ route('admin.guess.status', $quizInfo->id) }}">
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
                @if ($quizInfos->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($quizInfos) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="createModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title">@lang('')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.guess.store') }}" method="post">
                    @csrf

                    <input type="hidden" name="type_id" value="{{ $type->id }}">
                    <input type="hidden" name="general_quiz_id" value="">

                    <div class="modal-body">
                        <div class="form-group">
                            <label class="required">@lang('Category')</label>
                            <select class="form-control category" name="category_id">
                                <option value="0" selected disabled>@lang('Select One')</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-category="{{ $category->subcategories }}"
                                        @selected(old('category_id') == $category->id)>
                                        {{ __(keyToTitle($category->name)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('Subcategory')</label>
                            <select class="form-control subcategory" name="subcategory_id">
                                <option value="0" disabled selected>@lang('Select One')</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="required">@lang('Level')</label>
                            <select name="level" id="modalLevel" class="form-control">
                                <option value="0" disabled selected>@lang('Select One')</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}" @selected(old('level') == $level->id)>{{ $level->level }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="winning_mark">@lang('Winning Mark') <small>(@lang('Percentage'))</small></label>
                            <input type="number" class="form-control" name="winning_mark" value="{{ old('winning_mark') }}" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Category/Subcategory" />

    <button class="btn btn-sm btn-outline--primary openForm"
        data-category=""
        data-title="Add Guess Word"
        data-level=""
        data-general_quiz_id=""
        data-winning_mark="">
        <i class="la la-fw la-plus"></i>
        @lang('Add Guess Word')
    </button>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $('.editBtn').on('click', function() {
                    var modal = $('#createModal');
                    let data = $(this).data();
                    modal.find('#title').text(`${data.title}`);
                    modal.find('input[name="general_quiz_id"]').val(`${data.general_quiz_id}`);
                    modal.find('input[name="winning_mark"]').val(`${data.winning_mark}`);
                    $(`.category option[value=${data.category}]`).attr('selected', true);
                    $(`#modalLevel option[value=${data.level}]`).attr('selected', true);

                    let categoryData = $('.category').find(':selected').data();
                    let html = `<option value="" selected disabled>@lang('Select One')</option>`;
                    $.each(categoryData.category, function(index, value) {
                        html += `<option value="` + value.id + `">
                                ` + value.name + `</option>`;
                    });
                    $('.subcategory').html(html);

                    $(`.subcategory option[value="${data.subcategory}"]`).attr('selected', true);
                    modal.modal('show');
                });

                $('.openForm').on('click', function() {
                    var modal = $('#createModal');
                    let data = $(this).data();
                    modal.find('#title').text(`${data.title}`);
                    modal.find('input[name="general_quiz_id"]').val(`${data.general_quiz_id}`);
                    modal.find('#modalLevel option[selected]').attr('selected', false);
                    modal.find('#modalLevel option[value=0]').attr('selected', true);
                    $(`.category option[selected]`).attr('selected', false);
                    $(`.category option[value=0]`).attr('selected', true);
                    $('.subcategory').html(`<option value="" selected disabled>@lang('Select One')</option>`);
                    modal.modal('show');
                })

                let categoryData = $('.category').find(':selected').data();
                let html = `<option value="0" selected disabled>@lang('Select One')</option>`;
                $.each(categoryData.category, function(index, value) {
                    html += `<option value="` + value.id + `" @selected(old('subcategory_id') == `+value.id+`)>
                            ` + value.name + `</option>`;
                });
                $('.subcategory').html(html);

                $('.category').on('change', function() {
                    let data = $(this).find(':selected').data();
                    let html = `<option value="0" selected disabled>@lang('Select One')</option>`;
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
