@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form action="{{ route('admin.exam.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="type_id" value="{{ $type->id }}">
                    <input type="hidden" name="exam_id" value="{{ @$exam->id }}">

                    <div class="card-body">
                        <div class="row mt-4">
                            <div class="col-xl-4">
                                <div class="form-group">
                                    <label>@lang('Exam Image') <small class="text--small text-muted">(<i>@lang('If any')</i>)</small></label>
                                    <div class="image-upload">
                                        <div class="thumb">
                                            <div class="avatar-preview mb-4">
                                                <div class="profilePicPreview"
                                                    style="background-image: url({{ getImage(getFilePath('exam') . '/' . @$exam->image, getFileSize('exam')) }})">
                                                    <button type="button" class="remove-image">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="avatar-edit">
                                                <input type="file" class="profilePicUpload d-none" name="exam_image"
                                                    id="profilePicUpload1" accept=".png, .jpg, .jpeg">
                                                <label for="profilePicUpload1" class="bg--primary">@lang('Upload Image')</label>
                                                <small class="mt-2">@lang('Supported files'): <b>@lang('jpeg'),
                                                    @lang('jpg'), @lang('png')</b>.
                                                    @lang('Image will be resized into')
                                                    {{ getFileSize('exam') }}@lang('px').
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-8">
                                <div class="form-group">
                                    <label>@lang('Title')</label>
                                    <input type="text" class="form-control" name="title" value="{{ $exam->title ?? old('title') }}" required>
                                </div>
                                <div class="form-group">
                                    <label>@lang('Description')</label>
                                    <textarea class="form-control" rows="3" name="description" required>{{ @$exam->description ?? old('description') }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>@lang('Participate Point')</label>
                                    <input type="number" name="point" class="form-control" value="{{ $exam->point ?? old('point') }}" required>
                                    <small class="text--small text-muted">
                                        <i class="las la-info-circle"></i>
                                        <i>@lang('This point will be deduct from player account.')</i>
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label>@lang('Prize')</label>
                                    <input type="number" class="form-control " name="prize" value="{{ $exam->prize ?? old('prize') }}"
                                        required>
                                    <small class="text--small text-muted">
                                        <i class="las la-info-circle"></i>
                                        <i>@lang('This point will be added to the players account if win.')</i>
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label>@lang('Start Date')</label>
                                    <input name="start_date" type="text"
                                        data-language="en"
                                        data-position='bottom left'
                                        class="datepicker-here form-control"
                                        autocomplete="off"
                                        value="{{ $exam->start_date ?? old('start_date') }}" required>
                                </div>
                                <div class="form-group">
                                    <label>@lang('Start Time')</label>
                                    <input type="text" name="exam_start_time" value="{{ $exam->exam_start_time ?? old('exam_start_time') }}" class="form-control time-picker" autocomplete="off" required>
                                </div>
                                <div class="form-group">
                                    <label>@lang('Duration') (@lang('Minute'))</label>
                                    <input type="number" name="exam_duration" value="{{ $exam->exam_duration ?? old('exam_duration') }}" class="form-control" autocomplete="off" required>
                                </div>
                                <div class="form-group">
                                    <label>@lang('Winning Mark') <small>(@lang('Percentage'))</small></label>
                                    <input type="number" class="form-control" name="winning_mark" value="{{ $exam->winning_mark ?? old('winning_mark') }}" required>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12">
                                        <button type="button" class="btn btn--sm btn--primary py-2 my-3 float-end"
                                            id="addNewBtn">
                                            <i class="las la-plus"></i>
                                            @lang('Add Exam Rules')
                                        </button>
                                    </div>
                                </div>

                                <div class="row rule">
                                    @if (@$exam->exam_rule)
                                        @foreach ($exam->exam_rule as $key => $rule)
                                            <div class="col-md-12 ruleCol">
                                                <div class="form-group">
                                                    <div class="input-group">
                                                        <div class="input-group-text">
                                                            <span class="rule_count">@lang('Rule') {{ $key + 1 }}</span>
                                                        </div>
                                                        <input type="text" class="form-control" name="exam_rule[]" value="{{ $rule }}" required>
                                                        <button type="button" class="btn btn--danger closeRule"><i class="la la-times ms-0"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

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
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.exam.index') }}" />
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/vendor/datepicker.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/vendor/datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/datepicker.en.js') }}"></script>
@endpush

@push('script')
<script>
        (function($) {
            "use strict";

            $(document).ready(function() {
                initTimePicker();

            function initTimePicker() {
                var start = new Date();
                start.setHours(9);
                start.setMinutes(0);
                $('.time-picker').datepicker({
                    onlyTimepicker: true,
                    timepicker: true,
                    startDate: start,
                    language: 'en',
                    minHours: 0,
                    maxHours: 23,
                });
            }

            let ruleCount = 1;
                $('#addNewBtn').on('click', function() {
                    $('.rule').append(`<div class="col-md-12 ruleCol">
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text">
                                                        <span class="rule_count">Rule ${ruleCount++}</span>
                                                    </div>
                                                    <input type="text" class="form-control" name="exam_rule[]" value="{{ old('exam_rule[]') }}" required>
                                                    <button type="button" class="btn btn--danger closeRule"><i class="la la-times ms-0"></i></button>
                                                </div>
                                            </div>
                                        </div>`);

                });

                $(document).on('click', '.closeRule', function() {
                    ruleCount--;
                    $(this).closest('.ruleCol').remove();
                    $.each($('.rule_count'), function(index, value) {
                        let label = 'Rule ' + (index + 1);
                        $(this).text(label);
                    })
                });
        });
    })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .datepicker {
            z-index: 9999
        }
    </style>
@endpush
