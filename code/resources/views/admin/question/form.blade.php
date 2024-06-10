@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <form action="{{ route('admin.question.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="question_id" value="{{ @$question->id }}">
                    <input type="hidden" name="quizInfo_id" value="{{ @$quizInfo->id }}">

                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-4">
                                <div class="form-group">
                                    <label>@lang('Question Image') <small
                                            class="text--small text-muted">(<i>@lang('If any')</i>)</small></label>
                                    <div class="image-upload">
                                        <div class="thumb">
                                            <div class="avatar-preview mb-4">
                                                <div class="profilePicPreview"
                                                    style="background-image: url({{ getImage(getFilePath('question') . '/' . @$question->image) }})">
                                                    <button type="button" class="remove-image"><i
                                                            class="fa fa-times"></i></button>
                                                </div>
                                            </div>
                                            <div class="avatar-edit">
                                                <input type="file" class="profilePicUpload d-none" name="question_image"
                                                    id="profilePicUpload1" accept=".png, .jpg, .jpeg">
                                                <label for="profilePicUpload1" class="bg--primary">@lang('Upload Image')</label>
                                                <small class="mt-2">@lang('Supported files'): <b>@lang('jpeg'),
                                                        @lang('jpg'), @lang('png')</b>.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-8">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>@lang('Question')</label>
                                            <input type="text" class="form-control" name="question"
                                                value="{{ $question->question ?? old('question') }}" required>
                                        </div>
                                    </div>
                                </div>

                                @if (@$type->act == 'guess_word')
                                    <div class="form-group">
                                        <label>@lang('Answer')</label>
                                        <input type="hidden" name="is_answer[]" value="1">
                                        <input type="text" class="form-control" name="option[]"
                                            value="{{ $question->options[0]->option ?? old('answer') }}" required>
                                    </div>
                                @else
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <button type="button" class="btn btn--sm btn--primary py-2 my-3 float-end"
                                                id="addNewBtn">
                                                <i class="las la-plus"></i>
                                                @lang('Add Option')</button>
                                        </div>
                                    </div>

                                    <div class="row options">
                                        @foreach ($question->options ?? old('option', ['', '']) as $key => $option)
                                            <div class="col-md-6 optionCol">
                                                <div class="form-group">
                                                    <label
                                                        class="required optionLabel">@lang('Option '){{ $key + 1 }}</label>
                                                    <div class="input-group">
                                                        <div class="input-group-text">
                                                            <input class="form-check-input mt-0" type="radio"
                                                                name="is_answer[]" value="{{ $key + 1 }}"
                                                                {{ (@$option->id ? $option->is_answer == 1 : in_array($key + 1, old('is_answer', []))) ? 'checked' : '' }}>
                                                        </div>
                                                        <input type="text" class="form-control" name="option[]"
                                                            value="{{ $option->option ?? $option }}" required>
                                                        @if (@$question)
                                                            <button
                                                                class="btn btn--danger confirmationBtn
                                                            @if (!($loop->count > 2)) {{ 'disabled' }} @endif
                                                            "
                                                                type="button" data-question="@lang('Are you sure to delete this Option?')"
                                                                data-action="{{ route('admin.question.option.delete', $option->id) }}">
                                                                <i class="la la-times ms-0"></i>
                                                            </button>
                                                        @else
                                                            <button type="button"
                                                                class="btn btn--danger disabled closeOption">
                                                                <i class="la la-times ms-0"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
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

            $(document).ready(function() {
                let optionLimit = 2;
                $('#addNewBtn').on('click', function() {
                    if (optionLimit >= 20) {
                        notify('error', 'You\'ve added maximum number of file');
                        return false;
                    }
                    optionLimit++;
                    if (optionLimit > 2) {
                        $('.closeOption').removeClass('disabled');
                    }
                    $('.options').append(`<div class="col-md-6 optionCol">
                                            <div class="form-group">
                                                <label class="required optionLabel">@lang('Option ${optionLimit}')</label>
                                                <div class="input-group">
                                                    <div class="input-group-text">
                                                        <input class="form-check-input mt-0" type="radio" name="is_answer[]" value="${optionLimit}" {{ in_array('${optionLimit}', old('is_answer', [])) ? 'checked' : '' }}>
                                                    </div>
                                                    <input type="text" class="form-control" name="option[]" value="{{ old('option[]') }}" required>
                                                    <button type="button" class="btn btn--danger closeOption"><i class="la la-times ms-0"></i></button>
                                                </div>
                                            </div>
                                        </div>`);

                });

                $(document).on('click', '.closeOption', function() {
                    optionLimit--;
                    $(this).closest('.optionCol').remove();
                    $.each($('.optionLabel'), function(index, value) {
                        let label = 'Option ' + (index + 1);
                        $(this).text(label);
                    })
                    if (optionLimit == 2) {
                        $('.closeOption').addClass('disabled');
                    }
                });
            });
        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .form-check-input:focus {
            box-shadow: 0 0 0 0 rgba(13, 110, 253, .25);
        }
    </style>
@endpush
