<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use App\adminArticles;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\String_;

class Articles extends Controller
{
   


    public function articleStore(Request $request){

        $article=new adminArticles([
            'caption' =>$request->caption,
            'img'=>$this->saveAvatar($request),
            'description'=>$request->description,
        ]);
       
        $article->save();
        $articles=adminArticles::all();
    return response()->json($articles) ;
    }


    public function articleDelete($id){ 
        
        adminArticles::where('id',$id)->delete();
       $response =array('response'=>'Article deleted!','success'=>true);
       return $response;
    }    

    public function getArticle($id){
$select = adminArticles::where('id',$id)->get();
return response()->json($select);

    }

    private function saveAvatar(Request $request)
    {
        if ($request->hasFile('img')) {
            $file = $request->file('img');
            $fileNameWithExt = $file->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $fileExtension = $file->getClientOriginalExtension();
            $fileNameToStore = $fileName . '_' . time() . '.' . $fileExtension;
            $request->file('img')->storeAs('public/images', $fileNameToStore);
            return $fileNameToStore;
        }
        //  else {
        //     return 'default_avatar.png';
        // }
    }

    public function updateArticle($id,Request $request){
  $article=adminArticles::findOrFail($id);
  $this->validate($request, [
    'caption' => 'required',
    'description' => 'required'
]);

  $input  =['caption' =>$request->caption,
    'img'=>$this->saveAvatar($request),
    'description'=>$request->description,
  ];
  $article->fill($input)->save();

  return  response()->json($article);
    }
public function update($id){
    $article=adminArticles::find($id);
    $article->fill(Input::all())->save();
}
}
