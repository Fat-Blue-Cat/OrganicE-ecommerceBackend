<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Agent;



class AuthenticationController extends Controller
{
    //đăng ký

    function register(Request $request)
    {

        $customMessages = [
            'name.required' => 'Tên là trường bắt buộc.',
            'name.min' => 'Tên phải có ít nhất 5 ký tự.',
            'phone_number.required'=>'Số điện thoại là trường bắt buộc.',
            'phone_number.size'=>'Số điện thoại đủ 11 số.',
            'phone_number.unique'=>"Số điện thoại đã được đăng ký",
            'email.required' => 'Email là trường bắt buộc.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã tồn tại trong hệ thống.',
            'password.required' => "Dm đéo điền password à",
            'password.confirmed' => "Mật khẩu nhập lại không trùng khớp"
        ];

        $validator = Validator::make($request->all(), [
            "name" => 'required|min:5',
            "email" => 'required|email|unique:users,email',
            "phone_number"=>'required|size:10|unique:users,phone_number',
            "password" => 'bail|required|confirmed|min:8',
        ], $customMessages);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(["errors" => $errors], 200);
        } else {
            $user = new User();
            $user->fill($request->all());
            $user->password = Hash::make($request['password']);
            $user->save();
            Auth::login($user);
            $agent = new Agent();
            // Lấy thông tin kiểu thiết bị đăng nhập
            $deviceType = $agent->deviceType();
            $token = $user->createToken($deviceType)->plainTextToken;
            $user->token = $token;
            return response()->json([
                'message' => 'Đăng ký thành công',
                'token' => $token
            ], 200);
        }
    }
    //Đăng nhập
    function login(Request $request)
    {
        $customMessages = [
            'email.required' => 'Email là trường bắt buộc.',
            'email.email' => 'Email không hợp lệ.',
            'password.required' => "Dm đéo điền password à",
            'password.confirmed' => "Mật khẩu nhập lại không trùng khớp"
        ];
        $validator = Validator::make(
            $request->all(),
            [
                "email" => 'required|email',
                "password" => 'required|confirmed|min:8',
            ],
            $customMessages

        );
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(["errors" => $errors], 200);
        } else {
            $email = $request->email;
            $password = $request->password;
            $remember_password = $request->remember_password;
            $credential = Auth::attempt(['email' => $email, 'password' => $password],$remember_password);
            if ($credential) {
                $user = Auth::user();

                // Khởi tạo đối tượng Agent
                $agent = new Agent();
    
                // Lấy thông tin kiểu thiết bị đăng nhập
                $deviceType = $agent->deviceType();

                $token = $user->createToken($deviceType)->plainTextToken;
                $user->tokens = $token;
                return response()->json([
                    'message'=>"Đăng nhập thành công",
                    'token' => $token,
                ], 200);
            }
            else{
                return response()->json(["errors" => "Tài khoản hoặc mật khẩu éo chính xác"], 200);
            }
        }
    }
    // đăng xuất
    function logout() {
        $agent = new Agent();
        $deviceType = $agent->deviceType();
        $user=Auth::user();

        if ($user) {
            $user->tokens()->where('name', $deviceType)->delete();
            return response()->json(['message' => 'Đăng xuất thành công', "deviceType" => $deviceType], 200);
            // Xoá các mã thông báo liên kết với loại thiết bị hiện tại
        } else {
            // Xử lý trường hợp không có người dùng được xác thực thành công
            return response()->json(['message' => 'Không thể đăng xuất'], 400);

        }
    }
    //Lấy user đang đăng nhập
    function getCurrentUser(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            return response()->json([
                "user" => $user
            ], 200);
        } else {
            return response()->json([
                "message" => "Unauthorized"
            ], 401);
        }
    }
}
