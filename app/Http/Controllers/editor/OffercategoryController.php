<?php

namespace App\Http\Controllers\editor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Offercategory;
class OffercategoryController extends Controller
{
     public function index(){
    	return view('backEnd.offercategory.add');

    }
    public function store(Request $request){
    	$this->validate($request,[
            'offer'=>'required',
    		'status'=>'required',
    	]);

    	// image01 upload
		$image = $request->file('image');
        $name =  time().'-'.$image->getClientOriginalName();
        $uploadpath = 'public/uploads/offercategory/';
        $image->move($uploadpath, $name);
        $imageUrl = $uploadpath.$name;	

    	$store_data                    = new Offercategory();
    	$store_data->offer   	   = $request->offer;
    	$store_data->image             = $imageUrl;
    	$store_data->status            = $request->status;
    	$store_data->save();
    	Toastr::success('message', 'Offer category add successfully!');
    	return redirect('/editor/offer-category/manage');
    }
     public function manage(){
        $show_data =Offercategory::
             orderBy('id','DESC')
            ->get();
    	return view('backEnd.offercategory.manage',compact('show_data'));
    }
     public function edit($id){
        $edit_data = Offercategory::find($id);
        return view('backEnd.offercategory.edit',compact('edit_data'));
    }
      public function update(Request $request){
      	$update_data = Offercategory::find($request->hidden_id);
      	$image = $request->file('image');
	       if($image) {
		       	$image = $request->file('image');
		       	File::delete(public_path().'public/upload/offer',$update_data->image);
		        $name =  time().'-'.$image->getClientOriginalName();
		        $uploadpath = 'public/uploads/offer/';
		        $image->move($uploadpath, $name);
		        $update_fileUrl = $uploadpath.$name;
	        }else{
	            $update_fileUrl = $update_data->image;
	        }

        $update_data->offer   	    = $request->offer;
    	$update_data->image             = $update_fileUrl;
    	$update_data->status            = $request->status;
    	$update_data->save();
        Toastr::success('message', 'Offer category Update successfully!');
        return redirect('editor/offer-category/manage');
    }

    public function inactive(Request $request){
        $unpublish_data = Offercategory::find($request->hidden_id);
        $unpublish_data->status=0;
        $unpublish_data->save();
        Toastr::success('message', 'Offer category active successfully!');
        return redirect('/editor/offer-category/manage');
    }

    public function active(Request $request){
        $publishId = Offercategory::find($request->hidden_id);
        $publishId->status=1;
        $publishId->save();
        Toastr::success('message', 'Offer category active successfully!');
        return redirect('/editor/offer-category/manage');
    }
     public function destroy(Request $request){
        $deleteId = Offercategory::find($request->hidden_id);
        $deleteId->delete();
        Toastr::success('message', 'Offer category delete successfully!');
        return redirect('/editor/offer-category/manage');
    }

}
