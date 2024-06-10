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
                                    <th>@lang('Type')</th>
                                    <th>@lang('Icon')</th>
                                    <th>@lang('Short Description')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $type)
                                    <tr>
                                        <td><span class="fw-bold">{{ __($type->name) }}</span></td>
                                        <td>
                                            <img src="{{ getImage(getFilePath('quiz') . '/' . $type->image) }}" class="question_img">
                                        </td>
                                        <td>{{ __($type->short_description) }}</td>
                                        <td>@php echo $type->statusBadge; @endphp</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline--primary editBtn"
                                                data-short_description="{{ $type->short_description }}"
                                                data-id="{{ $type->id }}"
                                                data-name="{{ $type->name }}"
                                                data-icon="{{ getImage(getFilePath('quiz') . '/' . $type->image) }}">
                                                <i class="la la-pencil"></i>
                                                @lang('Edit')
                                            </button>

                                            @if ($type->status == Status::DISABLE)
                                                <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to enable this Quiz type?')"
                                                    data-action="{{ route('admin.type.status', $type->id) }}">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to disable this Quiz type?')"
                                                    data-action="{{ route('admin.type.status', $type->id) }}">
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
            </div>
        </div>
    </div>

    <x-confirmation-modal />

    <div class="modal fade" id="editModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title">@lang('Edit Quiz Type')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.type.store') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="id" value="">

                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Icon')</label>
                            <div class="image-upload">
                                <div class="thumb">
                                    <div class="avatar-preview mb-4">
                                        <div class="profilePicPreview" style="background-image: url('{{ getImage(null) }}')">
                                            <button type="button" class="remove-image"><i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                    <div class="avatar-edit">
                                        <input type="file" class="profilePicUpload d-none" name="image" id="image" accept=".png, .jpeg, .jpg">
                                        <label for="image" class="bg--primary">@lang('Upload Image')</label>
                                        <small class="mt-2">@lang('Supported files'): <b>@lang('jpeg'),
                                            @lang('jpg'), @lang('png')</b>.
                                        @lang('Image will be resized into')
                                        {{ getFileSize('quiz') }}@lang('px'). </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Title')</label>
                            <input type="text" class="form-control" name="name" value="" required disabled>
                        </div>

                        <div class="form-group">
                            <label>@lang('Short Description')</label>
                            <input type="text" class="form-control" name="short_description" value="" required>
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


@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $('.editBtn').on('click', function() {
                    var modal = $('#editModal');
                    let data = $(this).data();
                    modal.find('input[name="short_description"]').val(`${data.short_description}`);
                    modal.find('input[name="id"]').val(`${data.id}`);
                    modal.find('input[name="name"]').val(`${data.name}`);
                    modal.find('.profilePicPreview').css('background-image', 'url(' + data.icon +')')
                    modal.modal('show');
                });
            });
        })(jQuery);
    </script>
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
