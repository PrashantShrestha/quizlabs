@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form action="{{ route('admin.coin.plan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="id" value="{{ @$data->id ?? 0 }}">

                    <div class="card-body">
                        <div class="row mt-4">
                            <div class="col-xl-4">
                                <div class="form-group">
                                    <label>@lang('Plan Image') <small class="text--small text-muted">(<i>@lang('If any')</i>)</small></label>
                                    <div class="image-upload">
                                        <div class="thumb">
                                            <div class="avatar-preview mb-4">
                                                <div class="profilePicPreview"
                                                    style="background-image: url({{ getImage(getFilePath('plan') . '/' . @$data->image, getFileSize('plan')) }})">
                                                    <button type="button" class="remove-image">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="avatar-edit">
                                                <input type="file" class="profilePicUpload d-none" name="image"
                                                    id="image" accept=".png, .jpg, .jpeg">
                                                <label for="image" class="bg--primary">@lang('Upload Image')</label>
                                                <small class="mt-2">@lang('Supported files'): <b>@lang('jpeg'),
                                                    @lang('jpg'), @lang('png')</b>.
                                                    @lang('Image will be resized into')
                                                    {{ getFileSize('plan') }}@lang('px').
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-8">
                                <div class="form-group">
                                    <label>@lang('Title')</label>
                                    <input type="text" class="form-control" name="title" value="{{ @$data->title ?? old('title') }}" required>
                                </div>

                                <div class="form-group">
                                    <label>@lang('Amount of Coins')</label>
                                    <input type="number" class="form-control " name="coins_amount" value="{{ @$data->coins_amount ?? old('coins_amount') }}" required>
                                </div>

                                <div class="form-group">
                                    <label>@lang('Price')</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control " name="price" value="{{ @$data->price ?? old('price') }}" required>
                                        <span class="input-group-text">{{ $general->cur_text }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.coin.plan.index') }}" />
@endpush
