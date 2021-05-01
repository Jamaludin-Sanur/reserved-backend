<?php
namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;

class ApiController extends BaseController
{
    public function register(Request $request)
    {
        try{
            $faker = \Faker\Factory::create();

            //Validate data from request
            $data = $request->only('name', 'email', 'password');
            $validator = Validator::make($data, [
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|max:50'
            ]);
    
            // Validation - Send failed response if request is not valid
            if ($validator->fails()) {
                return $this->sendError($validator->messages());       
            }
    
            // Request is valid, create new user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'urlImage' => $faker->imageUrl($width = 300, $height = 300)
            ]);
    
            //Create token
            try {
                $credentials = [
                    'email' => $data['email'],
                    'password' => $data['password']
                ];
    
                if(! $token = JWTAuth::attempt($credentials)) {
                    return $this->sendError('Invalid email or password'); 
                }
            } catch (JWTException $e) {
                    return $this->sendError('Could not create token.'); 
            }
         
             //Token created, return with success response and jwt token
             $user = JWTAuth::user();
             return $this->sendResponse([
                'token' => $token,
                'user' => $user
            ], 'User created successfully.');
        }catch(Exception $err){
            return $this->sendError($err->getMessage()); 
        }
    }
 
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());  
        }

        //Request is validated
        //Create token
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->sendError('Invalid email or password'); 
            }
        } catch (JWTException $e) {
                return $this->sendError('Could not create token.'); 
        }
 	
 		//Token created, return with success response and jwt token
         $user = JWTAuth::user();
         return $this->sendResponse([
            'token' => $token,
            'user' => $user
        ], 'Newss retrieved successfully.');
    }
 
    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

		//Request is validated, do logout        
        try {
            JWTAuth::invalidate($request->token);
 
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
 
    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
 
        $user = JWTAuth::authenticate($request->token);
 
        return response()->json(['user' => $user]);
    }

}