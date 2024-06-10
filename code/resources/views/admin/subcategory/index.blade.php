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
                                    <th>@lang('Subcategory')</th>
                                    <th>@lang('Icon')</th>
                                    <th>@lang('Category')</th>
                                    <th>@lang('status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $subcategory)
                                    <tr>
                                        <td><span class="fw-bold">{{ @$subcategory->name }}</span></td>
                                        <td>
                                            <img src="{{ getImage(getFilePath('subcategory') . '/' . @$subcategory->image) }}" class="question_img">
                                        </td>
                                        <td>{{ @$subcategory->category->name }}</td>
                                        <td>@php echo $subcategory->statusBadge; @endphp</td>
                                        <td>
                                            @php
                                                $subcategory->image_with_path = getImage(getFilePath('subcategory') . '/' . @$subcategory->image);
                                            @endphp

                                            <button type="button" class="btn btn-sm btn-outline--primary cuModalBtn" data-resource="{{ $subcategory }}" data-modal_title="@lang('Edit Subcategory')" data-has_status="1">
                                                <i class="la la-pencil"></i>@lang('Edit')
                                            </button>

                                            @if ($subcategory->status == Status::DISABLE)
                                                <button class="btn btn-sm btn-outline--success ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to enable this Subcategory?')"
                                                    data-action="{{ route('admin.subcategory.status', $subcategory->id) }}">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to disable this Subcategory?')"
                                                    data-action="{{ route('admin.subcategory.status', $subcategory->id) }}">
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

@php
    $categories = App\Models\Category::get();
@endphp

    <div class="modal fade" id="cuModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title">@lang('Create New Subcategory')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.subcategory.store') }}" method="post" enctype="multipart/form-data">
                    @csrf

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
                                        <input type="file" class="profilePicUpload d-none" name="image" id="subcategory_image" accept=".png, .jpeg, .jpg" required>
                                        <label for="subcategory_image" class="bg--primary">@lang('Upload Image')</label>
                                        <small class="mt-2">@lang('Supported files'): <b>@lang('jpeg'),
                                            @lang('jpg'), @lang('png')</b>.
                                            @lang('Image will be resized into')
                                            {{ getFileSize('subcategory') }}@lang('px').
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="required">@lang('Category')</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">@lang('Select One')</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach

                            </select>
                        </div>
                        <div class="form-group">
                            <label class="required">@lang('Subcategory Name')</label>
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
    <x-search-form placeholder="Search Subcategory" />

    <button type="button" class="btn btn-sm btn-outline--primary me-2 h-45 cuModalBtn" data-image_path="{{ getImage(null, getFileSize('subcategory')) }}" data-modal_title="@lang('Add Subcategory')">
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
