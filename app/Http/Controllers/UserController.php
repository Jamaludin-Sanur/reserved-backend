<?php

namespace App\Http\Controllers;

use Validator;
use JWTAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Models\User;

class UserController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $users = User::orderBy('created_at', 'DESC')->paginate(10);;
            return $this->sendResponse($users, 'Users retrieved successfully.');
        }catch(\Exception $err){
            return $this->sendError($err->getMessage()); 
        }
    }

    /**
     * Update the specified resource in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try{
            // Get user
            $user = JWTAuth::parseToken()->authenticate();

            //Validate data
            $data = $request->only('name', 'email', 'password');
            $validator = Validator::make($data, [
                'name' => 'required|string',
                'email' => 'required|email',
                'password' => 'string|min:6|max:50'
            ]);

            //Send failed response if request is not valid
            if ($validator->fails()) {
                return $this->sendError($validator->messages());       
            }

            // Get data
            $idUser = $user['id'];
            $user = User::where('id', $idUser)->first();
            if(!$user) {
                return $this->sendError("User id:".$idUser." not Found");       
            }

            // Edit Data
            $input = $request->all();
            if($input['name']) $user->name = $input['name'];
            if($input['email']) $user->email = $input['email'];
            $user->save();
    
            return $this->sendResponse($user, 'User updated successfully.');
        }catch(\Exception $err){
            return $this->sendError($err->getMessage()); 
        }
    }
}
