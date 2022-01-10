<?php

namespace App\Http\Controllers\editor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Brian2694\Toastr\Facades\Toastr;
use App\Category;
use App\Brand;
use File;
use DB;
class BrandController extends Controller
{
    public function index(){
     	$category = Category::where('status',1)->get();
    	return view('backEnd.brand.add',compact('category'));

    }
    public function store(Request $request){
    	$this->validate($request,[
            'brandName'=>'required',
    		'status'=>'required',
    	]);

    	// image01 upload
		$image = $request->file('image');
        $name =  time().'-'.$image->getClientOriginalName();
        $uploadpath = 'public/uploads/brand/';
        $image->move($uploadpath, $name);
        $imageUrl = $uploadpath.$name;	

    	$store_data                    = new Brand();
    	$store_data->brandName   	   = $request->brandName;
        $store_data->slug              =   Str::slug($request->get('brandName'));
    	$store_data->image             = $imageUrl;
    	$store_data->status            = $request->status;
    	$store_data->save();
    	Toastr::success('message', 'brand add successfully!');
    	return redirect('/editor/brand/manage');
    }
     public function manage(){
        $show_data =Brand::
             orderBy('id','DESC')
            ->get();
    	return view('backEnd.brand.manage',compact('show_data'));
    }
     public function edit($id){
        $edit_data = Brand::find($id);
        return view('backEnd.brand.edit',compact('edit_data'));
    }
      public function update(Request $request){
      	$update_data = Brand::find($request->hidden_id);
      	$image = $request->file('image');
	       if($image) {
		       	$image = $request->file('image');
		       	File::delete(public_path().'public/upload/brand',$update_data->image);
		        $name =  time().'-'.$image->getClientOriginalName();
		        $uploadpath = 'public/uploads/product/';
		        $image->move($uploadpath, $name);
		        $update_fileUrl = $uploadpath.$name;
	        }else{
	            $update_fileUrl = $update_data->image;
	        }

        $update_data->brandName   	    = $request->brandName;
        $update_data->slug              =   Str::slug($request->get('brandName'));
    	$update_data->image             = $update_fileUrl;
    	$update_data->status            = $request->status;
    	$update_data->save();
        Toastr::success('message', 'brand Update successfully!');
        return redirect('editor/brand/manage');
    }

    public function inactive(Request $request){
        $unpublish_data = Brand::find($request->hidden_id);
        $unpublish_data->status=0;
        $unpublish_data->save();
        Toastr::success('message', 'brand active successfully!');
        return redirect('/editor/brand/manage');
    }

    public function active(Request $request){
        $publishId = Brand::find($request->hidden_id);
        $publishId->status=1;
        $publishId->save();
        Toastr::success('message', 'brand active successfully!');
        return redirect('/editor/brand/manage');
    }
}
