@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10 ">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('User')</th>
                                    <th>@lang('Avatar')</th>
                                    <th>@lang('Rank')</th>
                                    <th>@lang('Score')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allTimeRank as $user)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $user->fullname }}</span>
                                            <br>
                                            <span class="small">
                                                <a href="{{ route('admin.users.detail', $user->id) }}"><span>@</span>{{ $user->username }}</a>
                                            </span>
                                        </td>
                                        <td>
                                            <img src="{{ getImage(getFilePath('userProfile') . '/' . $user->avatar) }}" alt="" class="question_img">
                                        </td>
                                        <td>{{ $user->user_rank }}</td>
                                        <td>{{ $user->score }}</td>

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
                @if ($allTimeRank->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($allTimeRank) }}
                    </div>
                @endif
            </div>
        </div>


    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Username" />
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
