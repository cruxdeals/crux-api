<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\CreateAdminRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;

class AdminAuth extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admins', ['except' => ['createAdmin','login']]);
    }

    public function createAdmin(CreateAdminRequest $request)
    {
        $merchantkey = date("Y-m-d h:i:sa") . rand(111111, 999999);
        $authid = md5($merchantkey);
        $password = $request->password;

        $insert_fields = array('email' => $request->email, 'authid' => $authid, 'firstname' => $request->firstname, 'lastname' => $request->lastname, 'password' => md5($password));

        $admin = Admin::create($insert_fields);

        foreach ($request->permission as $value) {
            $insert_fields = array('authid' => $authid, 'path' => $value);
            Permission::create($insert_fields);
        }
        return response()->json(['status' => 'success', 'message' => 'User created successfully', 'admin' => $admin], 200);
    }


    public function login(LoginRequest $request)
    {
        $user = Admin::where('email', $request->email)
            ->where('password', md5($request->password))->first();
        if (!$user) {
            return response(['status'=>'error','message'=>'Invalid details Provided'], 400);
        }

        $token = auth('admins')->setTTL('1440')->login($user);
        return $this->createNewToken($token);
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('admins')->factory()->getTTL(),
            'user' => auth('admins')->user(),
            'status' => 'success',
            "message" => "Successful login",
        ], 200);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        if(!$admin = Admin::where('id',$request->id)->where('password', md5($request->old_password))->first()){
            return response()->json(['status'=>'error','message'=>'Incorrect Old Password or Admin not found'],400);
        }
        $insert_fields = array('password' => md5($request->new_password));
        $admin->update($insert_fields);
        return response(['status' => 'success', 'message' => 'Password Successfull chaged'], 200);
    }
}
