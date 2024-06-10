<?php

namespace App\Traits;

use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

trait Crud
{

    protected $hasImage = true;
    protected $requestData = null;

    public function index()
    {
        $data['pageTitle'] = $this->title;
        $query = $this->model::searchable($this->searchable)->orderBy('id', 'desc');
        if($this->relation){
            $query->with($this->relation);
        }
        $data['data'] = $query->paginate(getPaginate());
        return view($this->view . 'index', $data);
    }

    public function create()
    {
        $pageTitle = 'Add New ' . $this->operationFor;
        return view($this->view . '.form', compact('pageTitle'));
    }

    public function store(Request $request, $id = 0)
    {
        if (@$this->id) {
            $id = $this->id;
        }

        $reqData = $this->requestData;
        if (!$reqData && !$id) {
            $reqData = $request->except('_token', 'image', 'id');
        }elseif (!$reqData && $id) {
            $reqData = $request->except('_token', 'image');
        }

        $validationRule = [];

        foreach ($reqData as $key => $data) {
            $validationRule[$key] = 'required';
        }

        if ($this->hasImage) {
            $imageValidation = !$id ? 'required' : 'nullable';
            $validationRule = array_merge($validationRule, ['image' => [$imageValidation, 'image', new FileTypeValidate(['png', 'jpeg', 'jpg'])]]);
        }

        $request->validate($validationRule);

        if ($id) {
            $model = $this->model::findOrFail($id);
            $old = @$model->image;
            $notification = $this->operationFor . ' updated successfully';
        } else {
            $model = new $this->model;
            $old = null;
            $notification = $this->operationFor . ' added successfully';
        }

        if ($this->hasImage && $request->hasFile('image')) {
            try {
                $model->image = fileUploader($request->image, getFilePath(strtolower($this->operationFor)), getFileSize(strtolower($this->operationFor)), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        foreach ($reqData as $key => $data) {
            $model->$key = $data;
        }
        $model->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit ' . $this->operationFor;
        $data = $this->model::findOrFail($id);
        return view($this->view.'form', compact('pageTitle', 'data'));
    }

    public function changeStatus($id)
    {
        return $this->model::changeStatus($id);
    }
}
