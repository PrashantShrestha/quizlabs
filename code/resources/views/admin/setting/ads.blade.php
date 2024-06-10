@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12 col-md-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.ads.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Android Banner Id')</label>
                                    <input class="form-control" type="text" name="banner_ads_id" value="{{$general->banner_ads_id}}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Android Interstitial Id')</label>
                                    <input class="form-control" type="text" name="interstitial_unit_id" value="{{$general->interstitial_unit_id}}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Android Rewarded Id')</label>
                                    <input class="form-control" type="text" name="rewarded_unit_id" value="{{$general->rewarded_unit_id}}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('IOS Banner Id')</label>
                                    <input class="form-control" type="text" name="ios_banner_ads_id" value="{{$general->ios_banner_ads_id}}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('IOS Interstitial Id')</label>
                                    <input class="form-control" type="text" name="ios_interstitial_unit_id" value="{{$general->ios_interstitial_unit_id}}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('IOS Rewarded Id')</label>
                                    <input class="form-control" type="text" name="ios_rewarded_unit_id" value="{{$general->ios_rewarded_unit_id}}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Banner Status')</label>
                                    <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-success"
                                    data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('On')"
                                    data-off="@lang('Off')" name="banner_ads_status"
                                    @if ($general->banner_ads_status == 1) checked @endif>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Interstitial Status')</label>
                                    <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-success"
                                    data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('On')"
                                    data-off="@lang('Off')" name="interstitial_unit_status"
                                    @if ($general->interstitial_unit_status == 1) checked @endif>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Rewarded Status')</label>
                                    <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-success"
                                    data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('On')"
                                    data-off="@lang('Off')" name="rewarded_unit_status"
                                    @if ($general->rewarded_unit_status == 1) checked @endif>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
