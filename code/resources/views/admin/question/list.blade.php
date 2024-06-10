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
                                <th>@lang('Question')</th>
                                <th>@lang('Image')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($questions as $question)
                                <tr>
                                    <td><span class="fw-bold">{{ __(strLimit($question->question, 35)) }}</span></td>
                                    <td>
                                        <span>
                                            <img src="{{ getImage(getFilePath('question') . '/' . @$question->image) }}" class="question-img">
                                        </span>
                                    </td>
                                    <td>@php echo $question->statusBadge; @endphp</td>
                                    <td>
                                        <a href="{{ route($route['edit'], [@$question->id, @$quizInfo->id]) }}" class="btn btn-sm btn-outline--primary ms-1">
                                            <i class="la la-pencil"></i>
                                            @lang('Edit')
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

<x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Search Question" />

    <a href="{{ route($route['create'], $quizInfo->id) }}" class="btn btn-sm btn-outline--primary">
        <i class="la la-fw la-plus"></i>
        @lang('Add Question')
    </a>

    @if(@$route['import'])
        <a href="{{ route($route['import'], $quizInfo->id) }}" class="btn btn-sm btn-outline--warning">
            <i class="las la-cloud-upload-alt"></i>
            @lang('Import')
        </a>
    @endif
@endpush

@push('style')
    <style>
        .question-img{
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
    </style>
@endpush
