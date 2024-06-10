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
                                    <th>@lang('Category')</th>
                                    <th>@lang('Winning Mark')</th>
                                    <th>@lang('Participate Point')</th>
                                    <th>@lang('Prize')</th>
                                    <th>@lang('Comprehension')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($funs as $fun)
                                    <tr>
                                        <td><span class="fw-bold">{{ __(strLimit($fun->title, 15)) }}</span></td>
                                        <td>{{ __($fun->category->name) }}</td>
                                        <td>{{ $fun->winning_mark }}%</td>
                                        <td>{{ @$fun->point }}</td>
                                        <td>{{ @$fun->prize }}</td>
                                        <td>{{ __(strLimit($fun->description, 40)) }}</td>
                                        <td>@php echo $fun->statusBadge; @endphp</td>
                                        <td>
                                            <div class="button--group">
                                                <button class="btn btn-sm btn-outline--primary editBtn"
                                                    data-title="@lang('Edit Fun \'N\' Learn')" data-fun_title="{{ $fun->title }}"
                                                    data-category="{{ $fun->category->id }}"
                                                    data-subcategory="{{ @$fun->sub_category_id }}"
                                                    data-description="{{ $fun->description }}"
                                                    data-point="{{ @$fun->point }}" data-prize="{{ @$fun->prize }}"
                                                    data-winning_mark="{{ @$fun->winning_mark }}"
                                                    data-fun_id="{{ $fun->id }}">
                                                    <i class="la la-pencil"></i> @lang('Edit')
                                                </button>

                                                <button class="btn btn-sm btn-outline--info" data-bs-toggle="dropdown"
                                                    type="button" aria-expanded="false"><i
                                                        class="las la-ellipsis-v"></i>@lang('More')</button>
                                                <div class="dropdown-menu">
                                                    <a href="{{ route('admin.fun.details', $fun->id) }}" class="dropdown-item threshold">
                                                        <i class="las la-question"></i>
                                                        @lang('Questions')
                                                    </a>

                                                    @if ($fun->status == Status::DISABLE)
                                                        <a class="dropdown-item threshold confirmationBtn"
                                                            data-question="@lang('Are you sure to enable this Fun \'N\' Learn?')"
                                                            data-action="{{ route('admin.fun.status', $fun->id) }}" href="javascript:void(0)">
                                                            <i class="la la-eye"></i> @lang('Enable')
                                                        </a>
                                                    @else
                                                        <a class="dropdown-item threshold confirmationBtn"
                                                            data-question="@lang('Are you sure to disable this Fun \'N\' Learn?')"
                                                            data-action="{{ route('admin.fun.status', $fun->id) }}" href="javascript:void(0)">
                                                            <i class="la la-eye-slash"></i> @lang('Disable')
                                                        </a>
                                                    @endif

                                                    <a class="dropdown-item threshold confirmationBtn"
                                                    data-action="{{ route('admin.fun.send.notification', $fun->id) }}"
                                                    data-question="@lang('Are you sure to send notifications to all users?')"
                                                    href="javascript:void(0)">
                                                        <i class="las la-bell"></i>
                                                        @lang('Send Notification')
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
                @if ($funs->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($funs) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="createModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.fun.store') }}" method="post">
                    @csrf

                    <input type="hidden" name="type_id" value="{{ $type->id }}">
                    <input type="hidden" name="fun_id" value="">

                    <div class="modal-body">
                        <div class="form-group">
                            <label class="required">@lang('Category')</label>
                            <select class="form-control category" name="category_id">
                                <option value="0" selected disabled>@lang('Select One')</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-category="{{ $category->subcategories }}"
                                        @selected(old('category_id') == $category->id)>
                                        {{ __(keyToTitle($category->name)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>@lang('Subcategory')</label>
                            <select class="form-control subcategory" name="subcategory_id">
                                <option value="0" disabled selected>@lang('Select One')</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>@lang('Title')</label>
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
                        </div>

                        <div class="form-group">
                            <label>@lang('Participate Point')</label>
                            <input type="number" name="point" class="form-control" value="{{ old('point') }}" required>
                            <small class="text--small text-muted"><i class="las la-info-circle"></i>
                                    <i>@lang('This point will be deduct from player account.')</i></small>
                        </div>

                        <div class="form-group">
                            <label>@lang('Prize')</label>
                            <input type="number" class="form-control " name="prize" value="{{ old('prize') }}" required>
                            <small class="text--small text-muted"><i class="las la-info-circle"></i>
                                    <i>@lang('This point will be added to the players account if win.')</i></small>
                        </div>

                        <div class="form-group">
                            <label for="winning_mark">@lang('Winning Mark') <small>(@lang('Percentage'))</small></label>
                            <input type="number" class="form-control" name="winning_mark" value="{{ @$contest->winning_mark ?? old('winning_mark') }}" required>
                        </div>

                        <div class="form-group">
                            <label>@lang('Comprehensive Detail')</label>
                            <textarea name="detail" class="form-control" rows="5" required>{{ old('detail') }}</textarea>
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
    <x-search-form placeholder="Search Fun N Learn"/>

    <button type="button" class="btn btn-sm btn-outline--primary float-end openForm"
        data-title="@lang('Add New Fun \'N\' Learn')"
        data-fun_title=""
        data-category=""
        data-description=""
        data-point=""
        data-prize=""
        data-winning_mark=""
        data-fun_id="">
        <i class="la la-fw la-plus"></i>
        @lang('Add Fun \'N\' Learn')
    </button>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                $('.editBtn').on('click', function() {
                    var modal = $('#createModal');
                    let data = $(this).data();
                    modal.find('#title').text(`${data.title}`);
                    modal.find('input[name="fun_id"]').val(`${data.fun_id}`);
                    modal.find('input[name="title"]').val(`${data.fun_title}`);
                    $(`.category option[value=${data.category}]`).attr('selected', true);

                    let categoryData = $('.category').find(':selected').data();
                    let html = `<option value="" selected disabled>@lang('Select One')</option>`;
                    $.each(categoryData.category, function(index, value) {
                        html += `<option value="` + value.id + `">
                                ` + value.name + `</option>`;
                    });
                    $('.subcategory').html(html);

                    $(`.subcategory option[value="${data.subcategory}"]`).attr('selected', true);
                    modal.find('input[name="point"]').val(`${data.point}`);
                    modal.find('input[name="prize"]').val(`${data.prize}`);
                    modal.find('input[name="winning_mark"]').val(`${data.winning_mark}`);
                    modal.find('textarea[name="detail"]').val(`${data.description}`);
                    modal.modal('show');
                });

                $('.openForm').on('click', function() {
                    var modal = $('#createModal');
                    let data = $(this).data();
                    modal.find('#title').text(`${data.title}`);
                    modal.find('input[name="fun_id"]').val(`${data.fun_id}`);
                    modal.find('input[name="title"]').val(`${data.fun_title}`);
                    $(`.category option[selected]`).attr('selected', false);
                    $(`.category option[value=0]`).attr('selected', true);
                    $('.subcategory').html(`<option value="" selected disabled>@lang('Select One')</option>`);
                    modal.find('input[name="point"]').val(`${data.point}`);
                    modal.find('input[name="prize"]').val(`${data.prize}`);
                    modal.find('input[name="winning_mark"]').val(`${data.winning_mark}`);
                    modal.find('textarea[name="detail"]').val(`${data.description}`);
                    modal.modal('show');
                })

                let categoryData = $('.category').find(':selected').data();
                let html = `<option value="0" selected disabled>@lang('Select One')</option>`;
                $.each(categoryData.category, function(index, value) {
                    html += `<option value="` + value.id + `" @selected(old('subcategory_id') == `+value.id+`)>
                            ` + value.name + `</option>`;
                });
                $('.subcategory').html(html);

                $('.category').on('change', function() {
                    let data = $(this).find(':selected').data();
                    let html = `<option value="0" selected disabled>@lang('Select One')</option>`;
                    $.each(data.category, function(index, value) {
                        html += `<option value="` + value.id + `")>
                                ` + value.name + `</option>`;
                    });
                    $('.subcategory').html(html);
                });
            });
        })(jQuery);
    </script>
@endpush
