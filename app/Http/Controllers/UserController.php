<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use App\Mail\SendInvitation;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as Image;

class UserController extends Controller
{

    public function sendInvitiation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(["code" => -1, "data" => [], "soft_message" => "Please fix required fields", "hard_message" => $validator->errors()]);
        }

        $email = $request->get("email");

        $user = User::where("email", $email)->where("user_role", 0)->first();
        if ($user) {
            return response()->json(["code" => -1, "data" => [], "soft_message" => "We had already send the invitation to given email address"]);
        }

        // please enable once we have email for sending message
        try {
            \Mail::to($email)->send(new SendInvitation());
            return response()->json(["code" => 1, "data" => [], "soft_message" => "We send invite to user"]);
        } catch (\Exceptions $e) {
            return response()->json(["code" => -1, "data" => [], "soft_message" => "Oops, something went wrong please contact administrator"]);
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'     => 'required|email|unique:users',
            'user_name' => 'required|min:4|max:20',
            'avatar'    => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(["code" => -1, "data" => [], "soft_message" => "Please fix required fields", "hard_message" => $validator->errors()]);
        }

        // we can remove name or we can setup a name so name can be assigned or default put null
        $user = \App\User::create([
            "name"          => "",
            "email"         => $request->email,
            "user_name"     => $request->user_name,
            "registered_at" => date("Y-m-d H:i:s"),
            "user_role"     => $request->get("user_role", 0),
            "password"      => \Hash::make("randompassword"),
        ]);

        $avatarName = $user->id . '_avatar' . time() . '.' . request()->avatar->getClientOriginalExtension();

        $image = $request->file('avatar');

        $image_resize = Image::make($image->getRealPath());
        $image_resize->resize(256, 256);
        $image_resize->save(storage_path('app/public/avatars/' . $avatarName));

        $user->avatar = $avatarName;
        $user->save();

        return response()->json(["code" => 1, "data" => ["user" => $user, "token" => $user->createToken(env('APP_NAME'))->accessToken], "soft_message" => "User has been created succesfully"]);

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(["code" => -1, "data" => [], "soft_message" => "Please fix required fields", "hard_message" => $validator->errors()]);
        }

        $userdata = array(
            'email'    => $request->get('email'),
            'password' => $request->get('password'),
        );
        // attempt to do the login
        if (Auth::attempt($userdata)) {

            $user = auth()->user();

            return response()->json(["code" => 1, "data" => ["user" => $user, "token" => $user->createToken(env('APP_NAME'))->accessToken], "soft_message" => "User has been created succesfully"]);

        } else {
            // validation not successful, send back to form
            return response()->json(["code" => -1, "data" => [], "soft_message" => "Username/password is invalid"]);
        }

    }

    public function update(Request $request)
    {
        $user = auth()->user();

        if ($user) {

            $user->fill($request->except(['email', 'user_role']));
            $user->save();

            // check avtar file need to update

            if ($request->hasFile('avatar')) {
                $avatarName = $user->id . '_avatar' . time() . '.' . request()->avatar->getClientOriginalExtension();

                $image       = $request->file('avatar');
                $imageResize = Image::make($image->getRealPath());
                $imageResize->resize(256, 256);
                $imageResize->save(storage_path('app/public/avatars/' . $avatarName));

                $user->avatar = $avatarName;
                $user->save();
            }

            /*, "token" => $user->createToken(env('APP_NAME'))->accessToken*/
            return response()->json(["code" => 1, "data" => ["user" => $user], "soft_message" => "User has been updated succesfully"]);

        } else {
            return response()->json(["code" => -1, "data" => [], "soft_message" => "User is not authanticated"]);
        }
    }

}
