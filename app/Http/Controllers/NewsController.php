<?php

namespace App\Http\Controllers;

use Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\Models\News;
use App\Models\User;


class NewsController extends BaseController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try{
            // Get data from database
            if($request->get("user_id")){
                $news = News::where('user_id', $request->get("user_id"))->paginate(10);
            }else{
                $news = News::orderBy('created_at', 'DESC')->paginate(10);;
            }

            // Append user data to news data
            foreach ($news as &$data) {
                $data['author'] = User::find($data['user_id']);
            }
        
            return $this->sendResponse($news, 'Newss retrieved successfully.');
        }catch(\Exception $err){
            return $this->sendError($err->getMessage()); 
        }
    }
    /**
     * Store a newly created resource in database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $faker = \Faker\Factory::create();

            $input = $request->all();
            $user = JWTAuth::parseToken()->authenticate();
       
            $validator = Validator::make($input, [
                'title' => 'required',
                'description' => 'required'
            ]);
       
            if($validator->fails()){
                return $this->sendError($validator->errors());       
            }
       
            $input['user_id'] = $user['id'];
            $input['urlImage'] = $faker->imageUrl($width = 600, $height = 600);
            $news = News::create($input);
            return $this->sendResponse($news, 'News created successfully.');
        }catch(\Exception $err){
            return $this->sendError($err->getMessage()); 
        }
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            $news = News::find($id);
  
            if (is_null($news)) {
                return $this->sendError('News not found.');
            }
       
            return $this->sendResponse($news, 'News retrieved successfully.');
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
    public function update(Request $request, $id)
    {
        try{
            $input = $request->all();
            $user = JWTAuth::parseToken()->authenticate();

            // Validate 
            $validator = Validator::make($input, [
                'title' => 'required',
                'description' => 'required'
            ]);
            if($validator->fails()){
                return $this->sendError('Validation Error.');       
            }
    
            // Get data
            $news = News::where('id', $id)->first();
            if(!$news) {
                return $this->sendError("News id:".$id." not Found");       
            }

            // Validate user own the news
            if($news['user_id'] !== $user['id']){
                return $this->sendError("Unauthorized User");       
            }
    
            $news->title = $input['title'];
            $news->description = $input['description'];
            $news->save();
       
            return $this->sendResponse($news, 'News updated successfully.');
        }catch(\Exception $err){
            return $this->sendError($err->getMessage()); 
        }
    }
   
    /**
     * Remove the specified resource from database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try{
            $news = News::where('id', $id)->first();
            if(!$news) {
                return $this->sendError("News id:".$id." not Found");       
            }

            // Validate user own the news
            $user = JWTAuth::parseToken()->authenticate();
            if($news['user_id'] !== $user['id']){
                return $this->sendError("Unauthorized User");       
            }
    
            $news->delete();
            return $this->sendResponse([], 'News deleted successfully.');
        }catch(\Exception $err){
            return $this->sendError($err->getMessage()); 
        }
    }
}
