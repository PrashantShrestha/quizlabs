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
                                    <th>@lang('Level')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $lastLevel = 0;
                                @endphp
                                @forelse(@$data as $level)
                                    <tr>
                                        <td><span class="fw-bold">{{ __($level->title) }}</span></td>
                                        <td>{{ $level->level }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline--primary editBtn"
                                            data-title="Edit Level"
                                            data-level="{{ $level->level }}"
                                            data-level_title="{{ $level->title }}"
                                            data-id="{{ $level->id }}">
                                                <i class="la la-pencil"></i>
                                                @lang('Edit')
                                            </button>
                                        </td>
                                        @if ($loop->last)
                                            @php
                                                $lastLevel = $level->level;
                                            @endphp
                                        @endif
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


    <div class="modal fade" id="cuModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.level.store') }}" method="post">
                    @csrf

                    <input type="hidden" name="id" value="">

                    <div class="modal-body">
                        <div class="bg--warning py-3 px-3 mb-3 warning">
                            <small class="text--small"><i class="las la-info-circle"></i>
                                <i>@lang('Once you create this level, you will not able to change or delete this.')</i></small>
                        </div>
                        <div class="form-group">
                            <label class="required">@lang('Title')</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="required">@lang('Level')</label>
                            <input type="number" name="level" value="{{ old('level') }}" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <button type="button" class="btn btn-sm btn-outline--primary addBtn"
    data-title="Add New Level">
        <i class="las la-plus"></i>
        @lang('Add New')
    </button>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $('.addBtn').on('click', function(){
                    let newLevel = {{ count($data) + 1 }};
                    let modal = $('#cuModal');
                    let data = $(this).data();
                    modal.find('input[name="level"]').val(newLevel).attr('readonly', true);
                    modal.find('input[name="title"]').val('');
                    modal.find('#title').text(data.title);
                    modal.find('input[name="id"]').val(0);
                    modal.find('.warning').removeClass('d-none');
                    modal.find('.warning').addClass('d-block');
                    modal.modal('show');
                });

                $('.editBtn').on('click', function(){
                    let modal = $('#cuModal');
                    let data = $(this).data();
                    modal.find('input[name="title"]').val(data.level_title);
                    modal.find('input[name="level"]').val(data.level).attr('readonly', true);
                    modal.find('#title').text(data.title);
                    modal.find('input[name="id"]').val(data.id);
                    modal.find('.warning').removeClass('d-block');
                    modal.find('.warning').addClass('d-none');
                    modal.modal('show');
                });
            });
        })(jQuery)
    </script>
@endpush
