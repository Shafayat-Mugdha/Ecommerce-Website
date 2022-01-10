<?php

namespace App\Http\Controllers\editor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Str;
use App\Category;
use App\Subcategory;
use App\Childcategory;
use App\Product;
use App\Productimage;
use App\Size;
use App\Color;
use App\Productcolor;
use App\Productsize;
use DB;
use Alert;
class ProductController extends Controller
{
	// ajax code
	public function getSubcategory(Request $request){
            $category = DB::table("subcategories")
                        ->where("category_id",$request->category_id)
                        ->pluck('subcategoryName','id');
            return response()->json($category);
        }
        public function getChildcategory(Request $request){
            $childcategory = DB::table("childcategories")
                        ->where("subcategory_id",$request->category_id)
                        ->pluck("childcategoryName","id");
            return response()->json($childcategory);
        }
		// ajax code
    public function add(){
    	$categories=Category::where('status',1)->get();
        $productSize = Size::where('status',1)->get();
        $productColors = Color::where('status',1)->get();
    	return view('backEnd.product.add',compact('categories','productSize','productColors'));
    }
     public function store(Request $request){
        $this->validate($request,[
            'proCategory'			=>	'required',
            'proSubcategory'		=>	'required',
            'proName'				=>	'required',
    		'proNewprice'			=>	'required',
    		'image'					=>	'required',
    		'proDescription'		=>	'required',
    		'status'		=>	'required',

    	]);


    	$store_data           	 		  = 	new Product();
    	$store_data->proCategory      	  = 	$request->proCategory;
    	$store_data->proSubcategory  	  = 	$request->proSubcategory;
    	$store_data->proChildcategory 	  = 	$request->proChildcategory;
    	$store_data->proBrand  		      = 	$request->proBrand;
    	$store_data->proName  			  = 	$request->proName;
    	$store_data->proCode    		  = 	$request->proCode;
    	$store_data->slug  			      = 	Str::slug($request->get('proName'));
    	$store_data->proOldprice  		  = 	$request->proOldprice;
    	$store_data->proNewprice  		  = 	$request->proNewprice;
    	$store_data->proDescription       = 	$request->proDescription;
    	$store_data->proQuantity    	  = 	$request->proQuantity;
    	$store_data->status    		      = 	$request->status;
    	$store_data->save();

        $productId=$store_data->id;
        $images = $request->file('image');
        foreach($images as $image)
        {
           // image01 upload
            $name =  time().'-'.$image->getClientOriginalName();
            $uploadpath = 'public/uploads/product/';
            $image->move($uploadpath, $name);
            $imageUrl = $uploadpath.$name;  

             $proimage= new Productimage();
             $proimage->product_id = $productId;
             $proimage->image=$imageUrl;
             $proimage->save(); 
        }
        $store_data->sizes()->attach($request->proSize);
        $store_data->colors()->attach($request->proColor);


    	Toastr::success('message', 'Product information upload successfully!');
    	return redirect('/editor/product/manage');
        }
     public function manage(){
     	$show_data = DB::table('products')
        ->join('categories','products.proCategory','=','categories.id')
        ->join('subcategories','products.proSubcategory','=','subcategories.id')
        ->select('products.*','categories.name','subcategories.subcategoryName')
        ->orderBy('id','DESC')
        ->get();
        $productimage = DB::table('productimages')
        ->join('products','productimages.product_id','=','products.id')
        ->select('products.proName','productimages.*')
        ->orderBy('productimages.id','DESC')
        ->get();
     	return view('backEnd.product.manage',compact('show_data','productimage'));
     }
     public function edit($id){
     	$edit_data = Product::find($id);

     	$productimage = DB::table('productimages')
        ->join('products','productimages.product_id','=','products.id')
        ->select('products.proName','productimages.*')
        ->orderBy('productimages.id','DESC')
        ->get();

     	$category=Category::where('status',1)->get();
        $categoryId = Product::find($id)->proCategory;
        $subcategory = Subcategory::where('category_id','=',$categoryId)->get();
        $subcategoryId=Product::find($id)->proSubcategory;
        $childcategory = Childcategory::where('subcategory_id','=',$subcategoryId)->get();

        $totalsizes = Size::where('status',1)->get();
        $totalcolors = Color::where('status',1)->get();
        $selectcolors = Productcolor::where('product_id',$id)->get();
        $selectsizes = Productsize::where('product_id',$id)->get();

        return view('backEnd.product.edit',compact('category','subcategory','edit_data','productimage','childcategory','totalsizes','totalcolors','selectcolors','selectsizes'));
     }
     public function update(Request $request){
            $this->validate($request,[
                'proCategory'           =>  'required',
                'proSubcategory'        =>  'required',
                'proName'               =>  'required',
                'proNewprice'           =>  'required',
                'proDescription'        =>  'required',
                'status'        =>  'required',

            ]);
     	$update_data = Product::find($request->hidden_id);

    	$update_data->proCategory         =      $request->proCategory;
        $update_data->proSubcategory       =     $request->proSubcategory;
        $update_data->proChildcategory     =     $request->proChildcategory;
        $update_data->proBrand             =     $request->proBrand;
        $update_data->proName              =     $request->proName;
        $update_data->proCode              =     $request->proCode;
        $update_data->slug                 =     Str::slug($request->get('proName'));
        $update_data->proOldprice             =     $request->proOldprice;
        $update_data->proNewprice          =     $request->proNewprice;
        $update_data->proDescription       =     $request->proDescription;
        $update_data->proQuantity          =     $request->proQuantity;
        $update_data->status               =     $request->status;
    	$update_data->save(); 

    	$productId=$update_data->id;
    	$update_images=Productimage::where('product_id',$productId)->get();
		$images = $request->file('image');
		if($images){
	        foreach($images as $image)
		        {
		           // image01 upload
		        $name =  time().'-'.$image->getClientOriginalName();
		        $uploadpath = 'public/uploads/product/';
		        $image->move($uploadpath, $name);
		        $imageUrl = $uploadpath.$name;	

	             $proimage= new Productimage();
		         $proimage->product_id = $productId;
		         $proimage->image=$imageUrl;
		         $proimage->save(); 
		        }
        }else{
        	foreach($update_images as $update_image){
        	$uimage=$update_image->image;
        	$update_image->image 	=	$uimage;
			$update_image->save();
        	}
        }
        $update_data->sizes()->sync($request->proSize);
        $update_data->colors()->sync($request->proSize);
       
    	Toastr::success('message', 'Product information update successfully!');
    	return redirect('/editor/product/manage');
     }
      public function inactive(Request $request){
		$Unpublished_data			=	Product::find($request->hidden_id);
		$Unpublished_data->status 	=	0;
		$Unpublished_data->save();
		Toastr::success('message', 'Product unpublished successfully!');
		return redirect('editor/product/manage');	
	}
	public function active(Request $request){
		$published_data			=	Product::find($request->hidden_id);
		$published_data->status 	=	1;
		$published_data->save();
		Toastr::success('message', 'Product published successfully!');
		return redirect('editor/product/manage');	
	}
     public function productimgdel($id){
        $delete_img = Productimage::find($id);
        $delete_img->delete();
        Toastr::success('message', 'advertisments image  delete successfully!');
        return redirect()->back();
    }

	public function destroy(Request $request){
		$delete_data = Product::find($request->hidden_id);
		$delete_data->delete();
		Toastr::success('message', 'Product delete successfully!');
		return redirect('editor/product/manage');	
	}
    
}
