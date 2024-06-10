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
                                    <th>@lang('Coins')</th>
                                    <th>@lang('Price')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(@$data as $plan)
                                    <tr>
                                        <td><span class="fw-bold">{{ __($plan->title) }}</span></td>
                                        <td>
                                            <span>
                                                <img src="{{ getImage(getFilePath('plan') . '/' . @$plan->image) }}" class="question-img">
                                            </span>
                                        </td>
                                        <td>{{ $plan->coins_amount }}</td>
                                        <td>{{ @$plan->price }} {{ $general->cur_text }}</td>
                                        <td>@php echo $plan->statusBadge; @endphp</td>
                                        <td>
                                           <a href="{{ route('admin.coin.plan.edit', $plan->id) }}" type="button" class="btn btn-sm btn-outline--primary">
                                               <i class="la la-pencil"></i>@lang('Edit')
                                           </a>

                                            @if ($plan->status == Status::DISABLE)
                                                <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to enable this plan?')"
                                                    data-action="{{ route('admin.coin.plan.status', $plan->id) }}">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to disable this plan?')"
                                                    data-action="{{ route('admin.coin.plan.status', $plan->id) }}">
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
                @if ($data->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($data) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Search Contest" />

    <a href="{{ route('admin.coin.plan.create') }}" type="button" class="btn btn-sm btn-outline--primary me-2 h-45">
        <i class="las la-plus"></i>@lang('Add New')
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
