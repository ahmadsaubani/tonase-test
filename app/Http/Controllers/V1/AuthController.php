<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\CustomMessagesException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Transformers\UserTransformer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function register(Request $request) 
    {    
        DB::beginTransaction();
        try {
            $input = $request->only("name", "email", "password", "confirmation_password");
            $validator = validator($request->all(), [ 
                'name'                  => 'required',
                'email'                 => 'required|email',
                'password'              => 'required|min:5',  
                'confirmation_password' => 'required|same:password', 
            ]); 
    
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors());
            }    

            $check = User::whereEmail($input['email'])->exists();

            if ($check) {
                throw new CustomMessagesException("Duplicate Entry Exception", 403);
            }

            $input['password'] = Hash::make($input['password']);
            $input['email']     = trim(strtolower($input['email']));
            $user = User::create($input);
            
            $user->wallet()->create([
                "wallet_id" => Str::random(30),
                "balance"   => 0
            ]);

            DB::commit();
            return $this->showResult('data created', $user);

        } catch(Exception $e) {
            DB::rollBack();
            return $this->realErrorResponse($e);
        }
        
    }

    public function login(Request $request)
    {
        $input = $request->only('email', 'password');

        $user = User::whereEmail(strtolower($input['email']))->first();

        if (!empty($user)) {
            if ($user->validateForPassportPasswordGrant($input['password'])) {
                if (! empty($user->tokens)) {
                    foreach ($user->tokens as $token) {
                        $token->revoked = true;
                        $token->save();
                    }
                }

                $success['token']       = $user->createToken('tonase')->accessToken;
                $user->loginLog();
                return $this->showResult('Data Found', [ 'data' => $success ]);
            
            } else {
                $user->loginLog(2);
                return $this->errorResponse('Wrong password', 401);
            }
        } else {
            return $this->errorResponse('User not found', 401);
        }
    }


    public function logout()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->loginLog(3);
            $user->token()->revoke();

            return $this->showResult('Logout success', [ 'data' => [] ]);
        } else {
            return $this->errorResponse('Unauthorised', 401);
        }
    }
        
    public function getUserProfile()
    {
        $user = Auth::user();

        if (empty($user)) {
            return $this->errorResponse('User not found', 404);
        }

        $result = $this->item($user, new UserTransformer(), 'activities,wallet');

        return $this->showResultV2('Data Found', $result);
    }

    public function showAll()
    {
        $users = User::get();
        
        if (empty($users)) {
            return $this->errorResponse('Users is empty', 403);
        }

        $result = $this->collection($users, new UserTransformer(), 'activities,wallet');

        return $this->showResultV2('Data Found', $result);
    }

} 

