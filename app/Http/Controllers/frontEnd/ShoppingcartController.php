<?php

namespace App\Http\Controllers\frontEnd;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use DB;
use Session;
use Cart;
class ShoppingcartController extends Controller
{
    //======= Add To Cart Product Start=========== 
    public function addTocartGet($id,$quantity){
        $qty=$quantity;
        $productInfo = DB::table('products')->where('id',$id)->first();
        $productImage = DB::table('productimages')->where('product_id',$id)->first();

        $cartinfo=Cart::instance('shopping')->add(['id'=>$productInfo->id,'name'=>$productInfo->proName,'qty'=>$qty,'price'=>$productInfo->proNewprice,'options' => ['image'=>$productImage->image]]);
        return response()->json($cartinfo);  
    } 

    public function addToCartPost(Request $request, $id){
        $qty = $request->qty;
        $productInfo = DB::table('products')->where('id',$id)->first();
        $productImage = DB::table('productimages')->where('product_id',$id)->first();

         if($request->proColor && $request->proSize){
            Cart::instance('shopping')->add(['id'=>$productInfo->id,'name'=>$productInfo->proName,'qty'=>$qty,'price'=>$productInfo->proNewprice,'options' => ['image'=>$productImage->image,'size'=>$request->proSize,'color'=>$request->proColor]]);
         Toastr::success('Cart added successfully', 'Successfully');
         }
         elseif($request->proSize && $request->proColor==0){
         Cart::instance('shopping')->add(['id'=>$productInfo->id,'name'=>$productInfo->proName,'qty'=>$qty,'price'=>$productInfo->proNewprice,'options' => ['image'=>$productImage->image,'size'=>$request->proSize]]);
         Toastr::success('Cart added successfully', 'Successfully');
         }
         elseif($request->proColor && $request->proSize==0){
            Cart::instance('shopping')->add(['id'=>$productInfo->id,'name'=>$productInfo->proName,'qty'=>$qty,'price'=>$productInfo->proNewprice,'options' => ['image'=>$productImage->image,'color'=>$request->proColor]]);
         Toastr::success('Cart added successfully', 'Successfully');
         }else{
            Cart::instance('shopping')->add(['id'=>$productInfo->id,'name'=>$productInfo->proName,'qty'=>$qty,'price'=>$productInfo->proNewprice,'options' => ['image'=>$productImage->image]]);
             Toastr::success('Cart added successfully', 'Successfully');
         }

        // $cartinfo=Cart::instance('shopping')->add(['id'=>$productInfo->id,'name'=>$productInfo->proName,'qty'=>$qty,'price'=>$productInfo->proNewprice,'options' => ['image'=>$productImage->image]]);
         // $info = cart::content();
         // return $info;
         return redirect()->back(); 
    }

     public function cartorderNow($id){
        $qty = 1;
        $productInfo = DB::table('products')->where('id',$id)->first();
        $productImage = DB::table('productimages')->where('product_id',$id)->first();

        $cartinfo=Cart::instance('shopping')->add(['id'=>$productInfo->id,'name'=>$productInfo->proName,'qty'=>$qty,'price'=>$productInfo->proNewprice,'options' => ['image'=>$productImage->image]]);
         return redirect('checkout'); 
    } 


    public function cartContent() {
        return view('frontEnd.layouts.includes.cartcontent');
    }


    public function showCart(){
        $totalProduct = Cart::instance('shopping')->content()->count();
        if($totalProduct != 0){
    	$cartInfos = Cart::instance('shopping')->content();
    	return view('frontEnd.layouts.pages.showcart', compact('cartInfos'));
    	}else
        {
        Toastr::success('Your shopping cart empty', 'Sorry');
        return redirect('/');
         }
	}

	 public function updateCart(Request $request){
    	 $rowId = $request->rowId;
    	 $quantity = $request->quantity;
    	 $cart=Cart::instance('shopping')->update($rowId,$quantity);
    	Toastr::success('Cart Updated successfully','Thanks');
    	return redirect()->back();
    }


    public function deleteCart(Request $request) {
    	$totalProduct = Cart::instance('shopping')->count();
    	if ($totalProduct) {
    		$rowId =$request->rowId;
	    	Cart::instance('shopping')->update($rowId,0);
	    	Toastr::success('Product remove from cart', 'Thanks');
	    	return redirect()->back();
    	}
    	else{
    		return redirect('/');
    	}
    	
    }

     public function shippingcharge(Request $request){
        Session::put('totalshipping',$request->totalshipping);
    }
    // =========== Add To Cart Oparation End =============

    // ========wish list oparation end================

        public function addwishlist($id){
             $qty =1;
             $productInfo = DB::table('products')->where('id',$id)->first();
             $productImage = DB::table('productimages')->where('product_id',$id)->first();
             Cart::instance('wishlist')->add(['id'=>$productInfo->id,'name'=>$productInfo->proName,'qty'=>$qty,'price'=>$productInfo->proNewprice,'options' => ['image'=>$productImage->image]]);
             Toastr::success('product add to wishlist', 'Notification');
            return redirect()->back();
             
        }
        
        public function wishlistProduct() {
            $wishlistproducts = Cart::instance('wishlist')->content();
            if($wishlistproducts->count()){
            return view('frontEnd.layouts.pages.wishlistproduct',compact('wishlistproducts'));
            }else{
                Toastr::info('You have no product in wishlist', 'Opps!');
                return redirect('/');
            }
        }
        public function addcartTowishlist($id,$rowId){
        $totalProduct = Cart::content()->count();
        $qty = 1;
         $productInfo = DB::table('products')->where('id',$id)->first();
         $productImage = DB::table('productimages')->where('product_id',$id)->first();

         $cartinfo= Cart::instance('shopping')->add(['id'=>$productInfo->id,'name'=>$productInfo->proName,'qty'=>$qty,'price'=>$productInfo->proNewprice,'options' => ['image'=>$productImage->image]]);
            Cart::instance('wishlist')->update($rowId,0);
             Toastr::success('Cart added successfully', 'Added');
        return redirect()->back();  
        }


    // ========compare product oparation end=============
    public function addCompare($id){
             $qty =1;
             $productInfo = DB::table('products')->where('id',$id)->first();
             $productImage = DB::table('productimages')->where('product_id',$id)->first();
             Cart::instance('compare')->add(['id'=>$productInfo->id,'name'=>$productInfo->proName,'qty'=>$qty,'price'=>$productInfo->proNewprice,'options' => ['image'=>$productImage->image,'description'=>$productInfo->proDescription]]);
             Toastr::success('product add to compare', 'Thanks');
            return redirect()->back();
             
        }
    public function compareproduct() {
        $compareproduct = Cart::instance('compare')->content();
        if($compareproduct->count()){
        return view('frontEnd.layouts.pages.compareproduct',compact('compareproduct'));
        }else{
            Toastr::info('You have no product in compare', 'Opps!');
            return redirect('/');
        }
    }
    public function compareProductadd($id,$rowId){
            $totalProduct = Cart::instance('shopping')->content()->count();
            $qty =1;
             $productInfo = DB::table('products')->where('id',$id)->first();
             $productImage = DB::table('productimages')->where('product_id',$id)->first();
             Cart::instance('shopping')->add(['id'=>$productInfo->id,'name'=>$productInfo->proName,'qty'=>$qty,'price'=>$productInfo->proNewprice,'options' => ['image'=>$productImage->image]]);
             Toastr::success('product add to cart', 'successfully');
             Cart::instance('compare')->update($rowId,0);
             return redirect()->back();
             
        }
    public function removeCompare(Request $request) {
        $compareproduct = Cart::instance('compare')->content();
            if ($compareproduct) {
                $rowId =$request->rowId;
                Cart::instance('compare')->update($rowId,0);
                Toastr::success('Compare product remove successfully', 'successfully');
                return redirect()->back();
            }
            else{
                return redirect('/');
            }
    }
    //=========compare produc end=============

}
