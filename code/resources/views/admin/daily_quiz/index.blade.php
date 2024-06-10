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
                                    <th>@lang('Date')</th>
                                    <th>@lang('Winning Mark')</th>
                                    <th>@lang('Participate Point')</th>
                                    <th>@lang('Prize')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyQuizzes as $dailyQuiz)
                                    <tr>
                                        <td><span class="fw-bold">{{ showDateTime($dailyQuiz->title, 'Y-m-d') }}</span></td>
                                        <td>{{ $dailyQuiz->winning_mark }}%</td>
                                        <td>{{ $dailyQuiz->point }}</td>
                                        <td>{{ $dailyQuiz->prize }}</td>
                                        <td>@php echo $dailyQuiz->statusBadge; @endphp</td>
                                        <td>
                                            <div class="button--group">
                                                <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn"
                                                data-resource="{{ $dailyQuiz }}"
                                                data-modal_title="@lang('Edit Daily Quiz')"
                                                data-has_status="1">
                                                    <i class="la la-pencil"></i>@lang('Edit')
                                                </button>

                                                <button class="btn btn-sm btn-outline--info" data-bs-toggle="dropdown"
                                                    type="button" aria-expanded="false"><i
                                                        class="las la-ellipsis-v"></i>@lang('More')</button>
                                                <div class="dropdown-menu">
                                                    <a href="{{ route('admin.daily.quiz.details', $dailyQuiz->id) }}" class="dropdown-item threshold">
                                                        <i class="las la-question"></i>
                                                        @lang('Questions')
                                                    </a>

                                                    @if ($dailyQuiz->status == Status::DISABLE)
                                                        <a class="dropdown-item threshold confirmationBtn"
                                                            data-question="@lang('Are you sure to enable this Daily Quiz?')"
                                                            data-action="{{ route('admin.daily.quiz.status', $dailyQuiz->id) }}" href="javascript:void(0)">
                                                            <i class="la la-eye"></i> @lang('Enable')
                                                        </a>
                                                    @else
                                                        <a class="dropdown-item threshold confirmationBtn"
                                                            data-question="@lang('Are you sure to disable this Daily Quiz?')"
                                                            data-action="{{ route('admin.daily.quiz.status', $dailyQuiz->id) }}" href="javascript:void(0)">
                                                            <i class="la la-eye-slash"></i> @lang('Disable')
                                                        </a>
                                                    @endif

                                                    <a class="dropdown-item threshold confirmationBtn"
                                                    data-action="{{ route('admin.daily.quiz.send.notification', $dailyQuiz->id) }}"
                                                    data-question="@lang('Are you sure to send notifications to all users?')"
                                                    href="javascript:void(0)">
                                                        <i class="las la-bell"></i>
                                                        @lang('Send Notification')
                                                    </a>
                                                </div>
                                            </div>
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
                @if ($dailyQuizzes->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($dailyQuizzes) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="cuModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.daily.quiz.store') }}" method="post">
                    @csrf

                    <input type="hidden" name="type_id" value="{{ $type->id }}">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Date')</label>
                            <input name="title" type="text"
                                data-range="true"
                                data-language="en"
                                data-position='bottom left'
                                class="datepicker-here form-control"
                                autocomplete="off"
                                value="{{ old('title') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="winning_mark">@lang('Winning Mark') <small>(@lang('Percentage'))</small></label>
                            <input type="number" class="form-control" name="winning_mark" value="{{ old('winning_mark') }}" required>
                        </div>

                        <div class="form-group">
                            <label>@lang('Participate Point')</label>
                            <input type="number" name="point" class="form-control" value="{{ old('point') }}" required>
                            <small class="text--small text-muted">
                                <i class="las la-info-circle"></i>
                                <i>@lang('This point will be deduct from player account.')</i>
                            </small>
                        </div>

                        <div class="form-group">
                            <label>@lang('Prize')</label>
                            <input type="number" class="form-control " name="prize" value="{{ old('prize') }}"
                                required>
                            <small class="text--small text-muted">
                                <i class="las la-info-circle"></i>
                                <i>@lang('This point will be added to the players account if win.')</i>
                            </small>
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
    <x-search-form placeholder="Search Daily Quiz" />

    <button type="button" class="btn btn-sm btn-outline--primary me-2 h-45 cuModalBtn" data-modal_title="@lang('Add Daily Quiz')">
        <i class="las la-plus"></i>@lang('Add New')
    </button>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/vendor/datepicker.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/vendor/datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/datepicker.en.js') }}"></script>
@endpush

@push('style')
    <style>
        .datepicker {
            z-index: 9999
        }
    </style>
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
            });
        })(jQuery);
    </script>
@endpush
