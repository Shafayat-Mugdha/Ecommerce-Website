<?php

namespace App\Http\Controllers\editor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Category;
use App\Productslider;
use DB;
use File;
class ProductSliderController extends Controller
{
     public function add(){
     	$sectioncategory = Category::where('status',1)
     	->get();
    	return view('backEnd.productslider.add',compact('sectioncategory'));
    }
    public function store(Request $request){
    	$this->validate($request,[
    		'image'=>'required',
    		'sectionId'=>'required',
            'link'=>'required',
    		'status'=>'required',
    	]);

    	// image upload
    	$file = $request->file('image');
    	$name = time().'-'.$file->getClientOriginalName();
    	$uploadPath = 'public/uploads/productslider/';
    	$file->move($uploadPath,$name);
    	$fileUrl =$uploadPath.$name;

    	$store_data = new Productslider();
    	$store_data->image 		= $fileUrl;
    	$store_data->sectionId  = $request->sectionId;
        $store_data->link       = $request->link;
    	$store_data->status 	= $request->status;
    	$store_data->save();
        Toastr::success('message', 'productslider  add successfully!');
    	return redirect('/editor/productslider/manage');
    }
    public function manage(){
    	$show_data = DB::table('productsliders')
    	->join('categories','productsliders.sectionId','=','categories.id')
    	->select('productsliders.*','categories.name')
    	->get();
        return view('backEnd.productslider.manage', [
            'show_data'=> $show_data,
        ]);
    }
    public function edit($id){
        $sectioncategory = Category::where('status',1)
        ->get();
        $edit_data = Productslider::find($id);
        return view('backEnd.productslider.edit',compact('sectioncategory','edit_data'));
    }
     public function update(Request $request){
        $this->validate($request,[
            'status'=>'required',
        ]);

        $update_data = Productslider::find($request->hidden_id);
        $update_image = $request->file('image');
        if ($update_image) {
           $file = $request->file('image');
            File::delete(public_path() . 'public/uploads/productslider', $update_data->image); 
            $name = time().'-'.$file->getClientOriginalName();
            $uploadPath = 'public/uploads/productslider/';
            $file->move($uploadPath,$name);
            $fileUrl =$uploadPath.$name;
        }else{
            $fileUrl = $update_data->image;
        }

        $update_data->image = $fileUrl;
    	$update_data->sectionId  = $request->sectionId;
        $update_data->link  = $request->link;
        $update_data->status = $request->status;
        $update_data->save();
        Toastr::success('message', 'productslider  update successfully!');
        return redirect('/editor/productslider/manage');
    }

    public function inactive(Request $request){
        $unpublish_data = Productslider::find($request->hidden_id);
        $unpublish_data->status=0;
        $unpublish_data->save();
        Toastr::success('message', 'productslider  inactive successfully!');
        return redirect('/editor/productslider/manage');
    }

    public function active(Request $request){
        $publishId = Productslider::find($request->hidden_id);
        $publishId->status=1;
        $publishId->save();
        Toastr::success('message', 'productslider  active successfully!');
        return redirect('/editor/productslider/manage');
    }
     public function destroy(Request $request){
        $deleteId = Productslider::find($request->hidden_id);
         File::delete(public_path() . 'public/uploads/productslider', $deleteId->image);
        $deleteId->delete();
        Toastr::success('message', 'productslider  delete successfully!');
        return redirect('/editor/productslider/manage');
    }
}
