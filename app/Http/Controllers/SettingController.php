<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SettingController extends Controller
{
    public function index()
    {
        return view('settings.edit');
    }

    
    public function store(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            $setting = Setting::firstOrCreate(['key' => $key]);
            $setting->value = $value;
            $setting->save();

            if ($key === 'super_admin_first_name' || $key === 'super_admin_last_name') {
                $user = Auth::user();
                if ($key === 'super_admin_first_name') {
                    $user->first_name = $value;
                } else {
                    $user->last_name = $value;
                }
                $user->save();
            }
        }

        return redirect()->route('settings.index');
    }
}
