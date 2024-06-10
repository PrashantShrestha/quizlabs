@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form action="{{ route('admin.contest.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="type_id" value="{{ $type->id }}">
                    <input type="hidden" name="contest_id" value="{{ @$contest->id }}">

                    <div class="card-body">
                        <div class="row mt-4">
                            <div class="col-xl-4">
                                <div class="form-group">
                                    <label>@lang('Contest Image') <small class="text--small text-muted">(<i>@lang('If any')</i>)</small></label>
                                    <div class="image-upload">
                                        <div class="thumb">
                                            <div class="avatar-preview mb-4">
                                                <div class="profilePicPreview"
                                                    style="background-image: url({{ getImage(getFilePath('contest') . '/' . @$contest->image, getFileSize('contest')) }})">
                                                    <button type="button" class="remove-image">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="avatar-edit">
                                                <input type="file" class="profilePicUpload d-none" name="contest_image"
                                                    id="profilePicUpload1" accept=".png, .jpg, .jpeg">
                                                <label for="profilePicUpload1" class="bg--primary">@lang('Upload Image')</label>
                                                <small class="mt-2">@lang('Supported files'): <b>@lang('jpeg'),
                                                    @lang('jpg'), @lang('png')</b>.
                                                    @lang('Image will be resized into')
                                                    {{ getFileSize('contest') }}@lang('px').
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-8">
                                <div class="form-group">
                                    <label>@lang('Title')</label>
                                    <input type="text" class="form-control" name="title" value="{{ @$contest->title ?? old('title') }}" required>
                                </div>

                                 <div class="form-group">
                                    <label>@lang('Description')</label>
                                    <textarea class="form-control" rows="3" name="description" required>{{ @$contest->description ?? old('description') }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>@lang('Participate Point')</label>
                                    <input type="number" class="form-control " name="point" value="{{ @$contest->point ?? old('point') }}" required>
                                    <small class="text--small text-muted">
                                        <i class="las la-info-circle"></i>
                                        <i>@lang('This point will be deduct from player account.')</i>
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label>@lang('Prize')</label>
                                    <input type="number" class="form-control " name="prize" value="{{ @$contest->prize ?? old('prize') }}" required>
                                    <small class="text--small text-muted">
                                        <i class="las la-info-circle"></i>
                                        <i>@lang('This point will be added to the players account if win.')</i>
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label>@lang('Date')</label>
                                    <input name="date" type="text"
                                        data-range="true"
                                        data-multiple-dates-separator=" - "
                                        data-language="en"
                                        class="datepicker-here form-control"
                                        data-position='bottom left'
                                        placeholder="@lang('Start date - End date')"
                                        autocomplete="off"
                                        value="{{ @$contest->start_date ? showDateTime(@$contest->start_date, 'm/d/Y') . ' - ' . showDateTime(@$contest->end_date, 'm/d/Y') : request()->date }}"
                                        required>
                                </div>

                                <div class="form-group">
                                    <label for="winning_mark">@lang('Winning Mark') <small>(@lang('Percentage'))</small></label>
                                    <input type="number" class="form-control" name="winning_mark" value="{{ @$contest->winning_mark ?? old('winning_mark') }}" required>
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
    <x-back route="{{ route('admin.contest.index') }}" />
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
            if (!$('.datepicker-here').val()) {
                $('.datepicker-here').datepicker();
            }
        })(jQuery)
    </script>
@endpush
