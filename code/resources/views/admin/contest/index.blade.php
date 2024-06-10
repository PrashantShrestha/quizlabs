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
                                    <th>@lang('Winning Mark')</th>
                                    <th>@lang('Participate Point')</th>
                                    <th>@lang('Prize')</th>
                                    <th>@lang('Start')</th>
                                    <th>@lang('End')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(@$contests as $contest)
                                    <tr>
                                        <td><span class="fw-bold">{{ __(strLimit($contest->title, 15)) }}</span></td>
                                        <td>
                                            <span>
                                                <img src="{{ getImage(getFilePath('contest') . '/' . @$contest->image) }}" class="question-img">
                                            </span>
                                        </td>
                                        <td>{{ $contest->winning_mark }}%</td>
                                        <td>{{ @$contest->point }}</td>
                                        <td>{{ @$contest->prize }}</td>
                                        <td>{{ showDateTime($contest->start_date, 'Y-m-d') }}</td>
                                        <td>{{ showDateTime($contest->end_date, 'Y-m-d') }}</td>
                                        <td>@php echo $contest->statusBadge; @endphp</td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.contest.edit', $contest->id) }}" class="btn btn-sm btn-outline--primary ms-1">
                                                    <i class="la la-pencil"></i>
                                                    @lang('Edit')
                                                </a>

                                                <button class="btn btn-sm btn-outline--info" data-bs-toggle="dropdown"
                                                    type="button" aria-expanded="false"><i
                                                        class="las la-ellipsis-v"></i>@lang('More')</button>
                                                <div class="dropdown-menu">
                                                    <a href="{{ route('admin.contest.details', $contest->id) }}" class="dropdown-item threshold">
                                                        <i class="las la-question"></i>
                                                        @lang('Questions')
                                                    </a>

                                                    @if ($contest->status == Status::DISABLE)
                                                        <a class="dropdown-item threshold confirmationBtn"
                                                            data-question="@lang('Are you sure to enable this Contest?')"
                                                            data-action="{{ route('admin.contest.status', $contest->id) }}" href="javascript:void(0)">
                                                            <i class="la la-eye"></i> @lang('Enable')
                                                        </a>
                                                    @else
                                                        <a class="dropdown-item threshold confirmationBtn"
                                                            data-question="@lang('Are you sure to disable this Contest?')"
                                                            data-action="{{ route('admin.contest.status', $contest->id) }}" href="javascript:void(0)">
                                                            <i class="la la-eye-slash"></i> @lang('Disable')
                                                        </a>
                                                    @endif

                                                    <a class="dropdown-item threshold confirmationBtn" data-action="{{ route('admin.exam.send.notification', $contest->id) }}"
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
                @if ($contests->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($contests) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Search Contest" />

    <a href="{{ route('admin.contest.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="la la-fw la-plus"></i>
        @lang('Add Contest')
    </a>
@endpush

@push('style')
    <style>
        .question-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
    </style>
@endpush
