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
                                    <th>@lang('Title')</th>
                                    <th>@lang('Image')</th>
                                    <th>@lang('Date')</th>
                                    <th>@lang('Participate Point')</th>
                                    <th>@lang('Prize')</th>
                                    <th>@lang('Start Time')</th>
                                    <th>@lang('Duration') (@lang('Minute'))</th>
                                    <th>@lang('Exam Key')</th>
                                    <th>@lang('Winning Mark')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exams as $exam)
                                    <tr>
                                        <td><span class="fw-bold">{{ __(strLimit($exam->title, 15)) }}</span></td>
                                        <td>
                                            <img src="{{ getImage(getFilePath('exam') . '/' . $exam->image) }}" class="question_img">
                                        </td>
                                        <td>{{ showDateTime($exam->start_date, 'Y-m-d') }}</td>
                                        <td>{{ $exam->point }}</td>
                                        <td>{{ $exam->prize }}</td>
                                        <td>{{ $exam->exam_start_time }}</td>
                                        <td>{{ $exam->exam_duration }}</td>
                                        <td>{{ $exam->exam_key }}</td>
                                        <td>{{ $exam->winning_mark }}%</td>
                                        <td>@php echo $exam->statusBadge; @endphp</td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.exam.edit', $exam->id) }}" class="btn btn-sm btn-outline--primary ms-1">
                                                    <i class="la la-pencil"></i>
                                                    @lang('Edit')
                                                </a>

                                                <button class="btn btn-sm btn-outline--info" data-bs-toggle="dropdown"
                                                    type="button" aria-expanded="false"><i
                                                        class="las la-ellipsis-v"></i>@lang('More')</button>
                                                <div class="dropdown-menu">
                                                    <a href="{{ route('admin.exam.details', $exam->id) }}" class="dropdown-item threshold">
                                                        <i class="las la-question"></i>
                                                        @lang('Questions')
                                                    </a>

                                                    @if ($exam->status == Status::DISABLE)
                                                        <a class="dropdown-item threshold confirmationBtn"
                                                            data-question="@lang('Are you sure to enable this Exam?')"
                                                            data-action="{{ route('admin.exam.status', $exam->id) }}" href="javascript:void(0)">
                                                            <i class="la la-eye"></i> @lang('Enable')
                                                        </a>
                                                    @else
                                                        <a class="dropdown-item threshold confirmationBtn"
                                                            data-question="@lang('Are you sure to disable this Exam?')"
                                                            data-action="{{ route('admin.exam.status', $exam->id) }}" href="javascript:void(0)">
                                                            <i class="la la-eye-slash"></i> @lang('Disable')
                                                        </a>
                                                    @endif

                                                    <a class="dropdown-item threshold confirmationBtn" data-action="{{ route('admin.exam.send.notification', $exam->id) }}"
                                                        data-question="@lang('Are you sure to send notifications to all users?')" href="javascript:void(0)">
                                                        <i class="las la-bell"></i> @lang('Send Notification')
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
                @if ($exams->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($exams) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Search Exam" />

    <a href="{{ route('admin.exam.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="la la-fw la-plus"></i>
        @lang('Add New Exam')
    </a>
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
