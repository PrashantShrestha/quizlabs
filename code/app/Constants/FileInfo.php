<?php

namespace App\Constants;

class FileInfo
{

    /*
    |--------------------------------------------------------------------------
    | File Information
    |--------------------------------------------------------------------------
    |
    | This class basically contain the path of files and size of images.
    | All information are stored as an array. Developer will be able to access
    | this info as method and property using FileManager class.
    |
    */

    public function fileInfo(){
        $data['withdrawVerify'] = [
            'path'=>'assets/images/verify/withdraw'
        ];
        $data['depositVerify'] = [
            'path'      =>'assets/images/verify/deposit'
        ];
        $data['verify'] = [
            'path'      =>'assets/verify'
        ];
        $data['default'] = [
            'path'      => 'assets/images/default.png',
        ];
        $data['withdrawMethod'] = [
            'path'      => 'assets/images/withdraw/method',
            'size'      => '800x800',
        ];
        $data['ticket'] = [
            'path'      => 'assets/support',
        ];
        $data['logoIcon'] = [
            'path'      => 'assets/images/logoIcon',
        ];
        $data['favicon'] = [
            'size'      => '128x128',
        ];
        $data['extensions'] = [
            'path'      => 'assets/images/extensions',
            'size'      => '36x36',
        ];
        $data['seo'] = [
            'path'      => 'assets/images/seo',
            'size'      => '1180x600',
        ];
        $data['userProfile'] = [
            'path'      =>'assets/images/user/profile',
            'size'      =>'350x300',
        ];
        $data['adminProfile'] = [
            'path'      =>'assets/admin/images/profile',
            'size'      =>'400x400',
        ];
        $data['quiz'] = [
            'path'      =>'assets/admin/images/quiz',
            'size'      =>'125x125',
        ];
        $data['category'] = [
            'path'      =>'assets/admin/images/category',
            'size'      =>'125x125',
        ];
        $data['subcategory'] = [
            'path'      =>'assets/admin/images/subcategory',
            'size'      =>'125x125',
        ];
        $data['question'] = [
            'path'      =>'assets/admin/images/question',
        ];
        $data['contest']= [
            'path'      =>'assets/admin/images/contest',
            'size'      =>'125x125'
        ];
        $data['exam']= [
            'path'      =>'assets/admin/images/exam',
            'size'      =>'125x125'
        ];
        $data['plan']= [
            'path'      =>'assets/admin/images/plan',
            'size'      =>'125x125'
        ];
        $data['advertisement'] = [
            'path'      =>'assets/images/advertisement',
        ];
        $data['promotional_notify'] = [
            'path'      =>'assets/images/promotional_notify',
            'size'      => '340x110'
        ];
        return $data;
	}

}
