<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\RegisterMail;
// use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use DateTime;
use DateTimeZone;
use Carbon\Carbon;
use Auth;


class AuthController extends Controller
{
    public function register( Request $request )
    {
        $fields = $request->validate([
            'fullname' => 'required|string',
            'email'    => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);
            
        $user = User::create([
            'name'      => $fields['fullname'],
            'email'     => $fields['email'],
            'password'  => Hash::make($fields['password']),
        ]);

        $token                       = $user->createToken( 'articleapp' )->plainTextToken;
        $user_personal_access_tokens = DB::table( 'personal_access_tokens' )->where( 'tokenable_id', $user->id )->first();
        
        $user_mail_data = [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'token' => $user_personal_access_tokens->token,
        ];

        Mail::to( $user->email )->send( new RegisterMail( $user_mail_data ) );

        $response = [
            "message" => 'Registration has been successfully completed!',
            'url' => "http://testapi.test/api/email/verify/?hash={$user_personal_access_tokens->token}&id={$user->id}"
        ];

        return response( $response, 201 );
    }

    public function verify( Request $request )
    {
        $now                         = new Carbon( new DateTime(), new DateTimeZone( 'Europe/Sofia' ) );
        $user                        = new User;
        $id                          = $request->validate( ['id' => 'required|int'] );
        $hash                        = $request->validate( ['hash' => 'required|string'] );
        $response                    = ["message" => 'Email address verification has\'t been successfull!'];
        $existed_user                = $user->find( $id )->first();
        $user_personal_access_tokens = DB::table( 'personal_access_tokens' )->where( 'tokenable_id', $id )->first();

        if( hash_equals( $hash['hash'], $user_personal_access_tokens->token ) && empty( $existed_user->email_verified_at ) )
        {
            $existed_user->email_verified_at = $now;
            $existed_user->save();
            
            $response = [
                "message" => 'Email address verification has been successfull!'
            ];
        }
        elseif( !empty( $existed_user->email_verified_at ) )
        {
            $response = [
                "message" => 'Email address has already been verified!'
            ];
        }

        return response( $response, 201 );
    }

    public function login( Request $request )
    {
        $inputs = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user         = new User;
        $existed_user = $user->where( 'email', $inputs['email'] )->first();
       
        $response = [
            "message" => 'Unauthenticated!'
        ];

        if( Hash::check( $inputs['password'], $existed_user->password ) )
        {
            if( !empty( $existed_user->email_verified_at ) )
            {
                $token = $existed_user->createToken( 'articleapp' )->plainTextToken;
                
                $response = [
                    'user' => [
                        'id'        => $existed_user->id, 
                        'email'     => $existed_user->email, 
                        'name'      => $existed_user->name, 
                        'api_token' => $token 
                    ]
                ];
            }
            else{
                $response = [
                    "message" => 'Email address already has`t been verified!'
                ];
            }
        }
        
        return response( $response, 201 );
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response([
            'message' => 'Logged out!'
        ]);
    }


}
