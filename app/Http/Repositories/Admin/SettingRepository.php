<?php
namespace App\Http\Repositories\Admin;

use App\Models\Setting;
use App\Http\Traits\ImageTrait;
use RealRashid\SweetAlert\Facades\Alert;
use App\Http\Interfaces\Admin\SettingInterface;

class SettingRepository  implements SettingInterface{

    use ImageTrait;

  protected $settingModel;
    public function __construct(Setting $settingmodel)
    {

        $this->settingModel=$settingmodel;
    }

    public function edit(){
        $setting = $this->settingModel::first();
        return view('Admin.setting.edit',compact('setting'));
    }

    public function update($request){
        
        dd($request);


            $setting=$this->settingModel::first();

        if ($request->hasFile('logo')) {
            $filename = time() . '.' . $request->logo->extension();
            $logoname =  $this->uploadImage($request->logo, $filename, 'setting');
            $logo='images/setting/'.$logoname;

        }
        if ($request->hasFile('about_image')) {
            $filename = time() . '.' . $request->about_image->extension();
            $about_image_name =  $this->uploadImage($request->about_image, $filename, 'setting');
            $about_image='images/setting/'.$about_image_name;

        }
        if ($request->hasFile('home_image')) {
            $filename = time() . '.' . $request->home_image->extension();
            $home_image_name =  $this->uploadImage($request->home_image, $filename, 'setting');
            $home_image='images/setting/'.$home_image_name;

        }
        if ($request->hasFile('pages_image')) {
            $filename = time() . '.' . $request->pages_image->extension();
            $pages_image_name =  $this->uploadImage($request->pages_image, $filename, 'setting');
            $pages_image='images/setting/'.$pages_image_name;

        }

          $setting->update([

                 'logo'                        =>  $logo   ?? $setting->logo,
                 'phone'                       =>  $request->phone,
                 'email'                       =>  $request->email,
                 'facebook'                    => $request->facebook,
                 'linkedin'                    => $request->linkedin,
                 'twitter'                     => $request->twitter,
                 'work_time'                   => $request->work_time,
                 'about_title'                 => $request->about_title,
                 'about_description'           => $request->about_description,
                 'about_image'                 => $about_image ?? $setting->about_image,
                 'home_cost'                   => $request->home_cost,
                 'home_speed'                  => $request->home_speed,
                 'home_pay'                    => $request->home_pay,
                 'home_image'                  => $home_image ?? $setting->home_image,
                 'home_work'                   => $request->home_work,
                 'home_create_description'     => $request->home_create_description,
                 'home_start_description'      => $request->home_start_description,
                 'home_pay_description'        => $request->home_pay_description,
                 'home_recive_description'     => $request->home_recive_description,
                 'home_title'                  => $request->home_title,
                 'home_description'            => $request->home_description,
                 'slider_title'                => $request->slider_title,
                 'slider_description'          => $request->slider_description,
                 'privacy_policy'              => $request->privacy_policy,
                 'terms_conditions'            => $request->terms_conditions,
                 'pages_image'                 => $pages_image ?? $setting->pages_image,
                 'whatsapp'                    => $request->whatsapp,
                 'messenger'                   => $request->messenger,
                 'calc_description'            => $request->calc_description,
                 'support_description'         => $request->support_description,
                 'no_payment'                  =>$request->no_payment,
                 'track_description'           => $request->track_description,
                  'our_goal_title'             =>$request->our_goal_title,
                 'our_goals_description'       => $request->our_goals_description,
                  'meta_description'       => $request->meta_description,
                 'meta_keyword'       => $request->meta_keyword,

          ]);
          Alert::success('success', 'setting updated successfully');
          return redirect()->back();

    }

}
