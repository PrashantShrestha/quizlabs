@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-md-12">
            <div class="card overflow-hidden">
                <div class="card-body">
                    <form action="{{ route('admin.setting.notification.push.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('API Key')</label>
                                    <input class="form-control" name="apiKey" type="text"
                                        value="{{ @$general->push_config->apiKey }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Auth Domain')</label>
                                    <input class="form-control" name="authDomain" type="text"
                                        value="{{ @$general->push_config->authDomain }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Project ID')</label>
                                    <input class="form-control" name="projectId" type="text"
                                        value="{{ @$general->push_config->projectId }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Storage Bucket')</label>
                                    <input class="form-control" name="storageBucket" type="text"
                                        value="{{ @$general->push_config->storageBucket }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Messageing Sender ID')</label>
                                    <input class="form-control" name="messagingSenderId" type="text"
                                        value="{{ @$general->push_config->messagingSenderId }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('App ID')</label>
                                    <input class="form-control" name="appId" type="text"
                                        value="{{ @$general->push_config->appId }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Measurment ID')</label>
                                    <input class="form-control" name="measurementId" type="text"
                                        value="{{ @$general->push_config->measurementId }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Server Key')</label>
                                    <input class="form-control" name="serverKey" type="text"
                                        value="{{ @$general->push_config->serverKey }}" required>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn--primary h-45 w-100" type="submit">@lang('Submit')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div id="pushNotifyModal" class="modal fade">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Firebase Setup')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="steps-tab" data-bs-toggle="tab" data-bs-target="#steps" type="button" role="tab" aria-controls="steps" aria-selected="true">@lang('Steps')</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="configs-tab" data-bs-toggle="tab" data-bs-target="#configs" type="button" role="tab" aria-controls="configs" aria-selected="false">@lang('Configs')</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="server-tab" data-bs-toggle="tab" data-bs-target="#server" type="button" role="tab" aria-controls="server" aria-selected="false">@lang('Server Key')
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="steps" role="tabpanel" aria-labelledby="steps-tab">
                            <div class="table-responsive overflow-hidden">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>@lang('To Do')</th>
                                            <th>@lang('Description')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td data-label="To Do">@lang('Step 1')</td>
                                            <td data-label="Description">@lang('Go to your Firebase account and select')
                                                <span class="text--primary">"@lang('Go to console')</span>"
                                                @lang('in the upper-right corner of the page').
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-label="To Do">@lang('Step 2')</td>
                                            <td data-label="Description">
                                                @lang('Select Add project and do the following to create your project'). <br>
                                                <code class="text--primary">
                                                   @lang(' Use the name, Enable Google Analytics, Choose a name and the country for Google Analytics, Use the default analytics settings ')
                                                </code>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-label="To Do">@lang('Step 3')</td>
                                            <td data-label="Description">
                                                @lang('Within your Firebase project, select the gear next to Project Overview and choose Project settings.')
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-label="To Do">@lang('Step 4')</td>
                                            <td data-label="Description">
                                                @lang('Next, set up a web app under the General section of your project settings.')
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-label="To Do">@lang('Step 5')</td>
                                            <td data-label="Description">
                                                @lang('Next, go to Cloud Messaging in your Firebase project settings and enable Cloud Messaging API.')
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade mt-3 ms-2 text-center" id="configs" role="tabpanel" aria-labelledby="configs-tab">
                            <img src="{{ asset('assets/admin/images/push_notification/configs.png') }}">
                        </div>
                        <div class="tab-pane fade mt-3 ms-2 text-center" id="server" role="tabpanel" aria-labelledby="server-tab">
                            <img src="{{ asset('assets/admin/images/push_notification/server.png') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <button type="button" data-bs-target="#pushNotifyModal" data-bs-toggle="modal" class="btn btn-outline--info">
        <i class="las la-question"></i>
        @lang('Help')
    </button>
@endpush
