@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-none-30">
        <div class="col-md-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Icon')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(@$data as $category)
                                    <tr>
                                        <td><span class="fw-bold">{{ @$category->name }}</span></td>
                                        <td>
                                            <img src="{{ getImage(getFilePath('category') . '/' . @$category->image) }}" class="question_img">
                                        </td>
                                        <td>@php echo $category->statusBadge; @endphp</td>
                                        <td>

                                            @php
                                                $category->image_with_path = getImage(getFilePath('category') . '/' . @$category->image);
                                            @endphp

                                           <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn" data-resource="{{ $category }}" data-modal_title="@lang('Edit Category')" data-has_status="1">
                                               <i class="la la-pencil"></i>@lang('Edit')
                                           </button>

                                            @if ($category->status == Status::DISABLE)
                                                <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to enable this category?')"
                                                    data-action="{{ route('admin.category.status', $category->id) }}">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to disable this category?')"
                                                    data-action="{{ route('admin.category.status', $category->id) }}">
                                                    <i class="la la-eye-slash"></i> @lang('Disable')
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%" class="text-center">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($data->hasPages())
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
                <form action="{{ route('admin.category.store') }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Icon')</label>
                            <div class="image-upload">
                                <div class="thumb">
                                    <div class="avatar-preview mb-4">
                                        <div class="profilePicPreview">
                                            <button type="button" class="remove-image"><i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                    <div class="avatar-edit">
                                        <input type="file" class="profilePicUpload d-none" name="image" id="image" accept=".png, .jpeg, .jpg" required>
                                        <label for="image" class="bg--primary">@lang('Upload Image')</label>
                                        <small class="mt-2">@lang('Supported files'): <b>@lang('jpeg'),
                                            @lang('jpg'), @lang('png')</b>.
                                            @lang('Image will be resized into')
                                            {{ getFileSize('category') }}@lang('px').
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required">@lang('Category Name')</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
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
    <x-search-form placeholder="Search Category" />

    <button type="button" class="btn btn-sm btn-outline--primary me-2 h-45 cuModalBtn" data-image_path="{{ getImage(null, getFileSize('category')) }}" data-modal_title="@lang('Add Category')">
        <i class="las la-plus"></i>@lang('Add New')
    </button>

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
