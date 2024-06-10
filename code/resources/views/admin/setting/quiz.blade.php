@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12 col-md-12 mb-30">
            <form action="" method="POST">
                @csrf

                <div class="card">
                    <div class="card-body">
                        <h5>@lang('Time Setting')</h5>
                        <div class="row mt-3">
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('General Quiz Answer Duration') <small>(@lang('In second'))</small></label>
                                    <input class="form-control" type="text" name="gq_ans_duration" required
                                        value="{{ $general->gq_ans_duration }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Contest Answer Duration') <small>(@lang('In second'))</small></label>
                                    <input class="form-control" type="text" name="contest_ans_duration" required
                                        value="{{ $general->contest_ans_duration }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Fun \'N\' Learn Answer Duration') <small>(@lang('In second'))</small></label>
                                    <input class="form-control" type="text" name="fun_ans_duration" required
                                        value="{{ $general->fun_ans_duration }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Guess Word Answer Duration') <small>(@lang('In second'))</small></label>
                                    <input class="form-control" type="text" name="guess_ans_duration" required
                                        value="{{ $general->guess_ans_duration }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Daily Quiz Answer Duration') <small>(@lang('In second'))</small></label>
                                    <input class="form-control" type="text" name="daily_quiz_ans_duration" required
                                        value="{{ $general->daily_quiz_ans_duration }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h5>@lang('Correct Answer Score Setting')</h5>
                        <div class="row mt-3">
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('General Quiz Score') <small>(@lang('Minimum score 1'))</small></label>
                                    <input class="form-control" type="text" name="gq_score" required
                                        value="{{ $general->gq_score }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Contest Score') <small>(@lang('Minimum score 1'))</small></label>
                                    <input class="form-control" type="text" name="contest_score" required
                                        value="{{ $general->contest_score }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Guess Word Score') <small>(@lang('Minimum score 1'))</small></label>
                                    <input class="form-control" type="text" name="guess_score" required
                                        value="{{ $general->guess_score }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Daily Quiz Score') <small>(@lang('Minimum score 1'))</small></label>
                                    <input class="form-control" type="text" name="daily_quiz_score" required
                                        value="{{ $general->daily_quiz_score }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Fun \'N\' Learn Score') <small>(@lang('Minimum score 1'))</small></label>
                                    <input class="form-control" type="text" name="fun_score" required
                                        value="{{ $general->fun_score }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Live exam Score') <small>(@lang('Minimum score 1'))</small></label>
                                    <input class="form-control" type="text" name="exam_score" required
                                        value="{{ $general->exam_score }}">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <div class="row mt-3">
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Welcome Bonus')</label>
                                    <input class="form-control" type="text" name="welcome_bonus" required
                                        value="{{ $general->welcome_bonus }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('On Select Answer Status')</label>
                                    <select name="on_select_ans_status" class="form-control">
                                        <option value="1" @selected($general->on_select_ans_status == 1)>@lang('On')</option>
                                        <option value="0" @selected($general->on_select_ans_status == 0)>@lang('Off')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Single Battle Coin')</label>
                                    <input class="form-control" type="text" name="battle_participate_point" required
                                        value="{{ $general->battle_participate_point }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Per Level Question')</label>
                                    <input class="form-control" type="text" name="per_level_question" required
                                        value="{{ $general->per_level_question }}">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Per Battle Question')</label>
                                    <input class="form-control" type="text" name="per_battle_question" required
                                        value="{{ $general->per_battle_question }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
