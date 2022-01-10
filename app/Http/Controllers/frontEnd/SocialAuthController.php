<?php

namespace App\Http\Controllers\frontEnd;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Socialite;
use Session;
use Cart;
use App\SocialCustomer;
class SocialAuthController extends Controller
{
     public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }
    public function callback($provider)
    {
        $getInfo = Socialite::driver($provider)->user(); 
        $findcustomer= SocialCustomer::where('provider_id', $getInfo->id)->first();
        if(!$findcustomer){
        	 $user				= 	new SocialCustomer();
             $user->name     	=   $getInfo->name;
    	     $user->email 		=	$getInfo->email;
             $user->provider    =   $provider;
             $user->provider_id =   $getInfo->id;
             $user->status      =   1;
             $user->save();
		    Toastr::success('Thanks', 'Thanks you are login successfully');
		    Session::flush();
		    Session::put('socialcustomerId',$user->provider_id);
		    if(Cart::instance('shopping')->count()!=0){
            return redirect('checkout');
        	}else{
        		return redirect('/');
        	}
        	
        }else{
        	$customerprovider_id= $findcustomer->provider_id;
        	Session::forget('customerId');
        	Session::put('socialcustomerId',$customerprovider_id);
        	if(Cart::instance('shopping')->count()!=0){
        		return redirect('checkout');
        	}else{
        		return redirect('/');
        	}
        }
        
    }
     public function socialcustomerLogout(){
         Session::flush();
         Toastr::success('You are logout successfully', 'success!');
        return redirect('/');
    }
}
