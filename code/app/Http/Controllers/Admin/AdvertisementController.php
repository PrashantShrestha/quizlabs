<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Advertisement;
use App\Rules\FileTypeValidate;
use App\Http\Controllers\Controller;

class AdvertisementController extends Controller
{
    public function index()
    {
        $pageTitle      = 'All Advertisement';
        $advertisements = Advertisement::searchable(['type', 'size'])->orderByDesc('id')->paginate(getPaginate());
        return view('admin.advertisement.index', compact('pageTitle', 'advertisements'));
    }

    public function store(Request $request, $id = 0)
    {
        $request->validate([
            'type'         => 'required|in:image,script',
            'size'         => 'required|in:728x90,300x600,300x250',
            'redirect_url' => 'required',
            'script'       => 'required_if:type,script',
        ]);

        if ($request->type == 'image' && $request->hasFile('image')) {
            $this->imageValidation($request, 'nullable');
        }

        if ($id) {
            $advertisement = Advertisement::findOrFail($id);
            $value = $advertisement->value;
            $notification = 'Advertisement updated successfully';
        } else {
            $advertisement = new Advertisement();
            $advertisement->type = $request->type;
            $advertisement->size = $request->size;
            $notification = 'Advertisement created successfully';
        }

        if ($request->hasFile('image')) {
            try {
                $oldImage = $advertisement->type == 'image' ? $advertisement->value : null;
                $value = fileUploader($request->image, getFilePath('advertisement'), null, $oldImage);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload advertisement image'];
                return back()->withNotify($notify);
            }
        }

        if ($request->type == "script") {
            $value = $request->script;
        }

        $advertisement->value = $value;
        $advertisement->redirect_url = $request->redirect_url;
        $advertisement->save();
        $notify[] = ['success', $notification];

        return back()->withNotify($notify);
    }

    public function imageValidation($request, $isRequired = 'required')
    {
        $size = explode('x', $request->size);
        $request->validate([
            'image'  => [$isRequired, new FileTypeValidate(['jpeg', 'jpg', 'png', 'gif']), 'dimensions:width=' . $size[0] . ',height=' . $size[1]],
        ]);
    }

    public function status($id)
    {
        return Advertisement::changeStatus($id);
    }
}
