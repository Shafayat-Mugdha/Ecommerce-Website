<?php
namespace App\Http\Controllers\frontEnd;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Slider;
use App\Brand;
use App\Product;
use App\Category;
use App\Subcategory;
use App\Childcategory;
use App\Productslider;
use App\Customer;
use App\News;;
use App\Contact;
use App\About;
use App\Returnpolicy;
use App\Privacypolicy;
use App\Createpage;
use App\Offercategory;
use DB;
use Session;
class frontEndController extends Controller
{
    
    public function index(){
    	$mainslider=Slider::where(['status'=>1])
        ->orderBy('id','DESC')
        ->limit(10,0)
        ->get(); 
        $brands =Brand::where('status',1)->get();
        $newarrival = DB::table('products')->where(['status'=>1])->orderBy('id','DESC')->limit(16)->get();
        
        return view('frontEnd.index',compact('mainslider','brands','newarrival'));
    }
    public function offerproduct($offer,$id){
        if($offer==30){
                $products=Product::where('status',1)
                ->where('proOffer', '<=','30')
                ->where('proOffer', '>','1')
                ->orderBy('proOffer','DESC')
                 ->paginate(20);
                return view('frontEnd.layouts.pages.offerproduct',compact('products'));
        }elseif($offer==40){
           $products=Product::where('status',1)
                ->where('proOffer', '<=','40')
                ->where('proOffer', '>','31')
                ->orderBy('proOffer','DESC')
                 ->paginate(20);
                return view('frontEnd.layouts.pages.offerproduct',compact('products'));
        }elseif($offer==50){
            $products=Product::where('status',1)
                ->where('proOffer', '<=','50')
                ->where('proOffer', '>','41')
                ->orderBy('proOffer','DESC')
                 ->paginate(20);
                return view('frontEnd.layouts.pages.offerproduct',compact('products'));
        }elseif($offer==60){
            $products=Product::where('status',1)
                ->where('proOffer', '<=','60')
                ->where('proOffer', '>','51')
                ->orderBy('proOffer','DESC')
                 ->paginate(20);
                return view('frontEnd.layouts.pages.offerproduct',compact('products'));
        }elseif($offer==70){
            $products=Product::where('status',1)
                ->where('proOffer', '<=','70')
                ->where('proOffer', '>','61')
                ->orderBy('proOffer','DESC')
                ->paginate(20);
                return view('frontEnd.layouts.pages.offerproduct',compact('products'));
        }
        
    }

    public function viewallcategory(){
        return view('frontEnd.layouts.pages.viewcat');
    }
    public function complain(){
        return view('frontEnd.layouts.pages.complain');
    }
    public function howtoorder(){
        return view('frontEnd.layouts.pages.howtoorder');
    }
    public function category($slug,$id){
    	$products = DB::table('products')
        ->join('categories','products.proCategory','=','categories.id')
        ->where('products.proCategory',$id)
        ->orderBy('products.id','DESC')
        ->select('products.*','categories.name')
        ->paginate(20);

        $bredcrumb=Category::find($id);
        $subcategories = Subcategory::where(['category_id'=>$id,'status'=>1])->get();
    	if($bredcrumb){
        return view('frontEnd.layouts.pages.category',compact('products','subcategories','bredcrumb'));
        }else{
             return view('errors.404');
        }
    }
     public function subcategory($id){
    	$products = DB::table('products')
        ->join('subcategories','products.proSubcategory','=','subcategories.id')
        ->select('products.*','subcategories.subcategoryName')
        ->where('products.proSubcategory',$id)
        ->orderBy('products.id','DESC')
         ->paginate(20);
        $bredcrumb=Subcategory::find($id);
        $childcategories = Childcategory::where(['subcategory_id'=>$id,'status'=>1])->get();
        if($bredcrumb){
    	return view('frontEnd.layouts.pages.subcategory',compact('products','bredcrumb','childcategories'));
        }else{
             return view('errors.404');
        }
    }
     public function products($id){
    	$products = DB::table('products')
        ->join('childcategories','products.proChildCategory','=','childcategories.id')
        ->select('products.*','childcategories.childcategoryName')
        ->where('products.proChildCategory',$id)
        ->orderBy('products.id','DESC')
         ->paginate(20);
        $bredcrumb=Childcategory::find($id);
        $subcategories = Childcategory::where(['subcategory_id'=>$id,'status'=>1])->get();
        if($bredcrumb){
    	return view('frontEnd.layouts.pages.childproduct',compact('products','bredcrumb','subcategories'));
        }
        else{
             return view('errors.404');
        }
    }
    public function details($id){
        $productdetails = DB::table('products')
        ->where('products.id',$id)
        ->orderBy('products.id','DESC')
        ->first();
       
        $selectcolors = DB::table('productcolors')
          ->join('colors','productcolors.color_id','=','colors.id')
            ->where('productcolors.product_id',$id)
            ->orderBy('id','DESC')
            ->select('productcolors.*','colors.colorName','colors.color')
            ->get();
            $selectsizes = DB::table('productsizes')
          ->join('sizes','productsizes.size_id','=','sizes.id')
            ->where('productsizes.product_id',$id)
            ->orderBy('id','DESC')
            ->select('productsizes.*','sizes.sizeName')
            ->get();

        $relatedproduct = DB::table('products')
        ->where('products.proCategory',$productdetails->proCategory)
        ->orderBy('products.id','DESC')
        ->paginate(9);
        // return $relatedproduct;
        
        if($productdetails){
        $productbrand = DB::table('products')
        ->join('brands','products.proBrand','=','brands.id')
        ->where('products.id',$id)
        ->select('products.*','brands.brandName')
        ->first();
        return view('frontEnd.layouts.pages.details',compact('productdetails','relatedproduct','selectcolors','selectsizes'));
        }
        else{
             return view('errors.404');
        }
    }


    public function shipping(){
        $customerId = Session::get('customerId');
        $socialcustomerId = Session::get('socialcustomerId');
        if($customerId||$socialcustomerId){
            return view('frontEnd.layouts.pages.shipping');
        }else{
            Toastr::error('message', 'Sorry! please login first');
            return redirect('customer/login');
        }
    }
    public function brand($id){
        $checkId =Product::where('proBrand',$id)->first();
        if($checkId){
        $products = DB::table('products')
        ->join('brands','products.proBrand','=','brands.id')
        ->where('products.proBrand',$id)
        ->orderBy('products.id','DESC')
        ->select('products.*','brandName')
        ->paginate(40);
        $bredcrumb=Brand::find($id);
        $subcategories = Subcategory::where(['category_id'=>$id,'status'=>1])->get();
        return view('frontEnd.layouts.pages.brandproduct',compact('products','bredcrumb','subcategories'));
    }else{
        return redirect('error-page');
        }
    }
    
    public function moreinfo($slug,$id){
        $moreinfoes=Createpage::where(['id'=>$id,'status'=>1])->first();
        if($moreinfoes){
        return view('frontEnd.layouts.pages.moreinfo',compact('moreinfoes'));
    }else{
        return view('errors.404');
    }
    }
    public function errorpage(){
        return view('errors.404');
    }
    public function contact(){
        return view('frontEnd.layouts.pages.contact');
    }
    
    
    
}
