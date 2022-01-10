<?php

namespace App\Http\Controllers\frontend;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Brian2694\Toastr\Facades\Toastr;
use App\Library\SslCommerz\SslCommerzNotification;
use Carbon\Carbon;
use App\Customer;
use App\Shipping;
use App\Order;
use App\Payment;
use App\Orderdetails;
use App\Comment;
use App\Ratting;
use Cart;
use Session;
use App\Post;
use App\Guestorder;
use App\Guestorderdetails;
use Mail;
use File;
use Auth;
use Exception;
class CustomerController extends Controller
{
    private function customers(){
        $customer=Customer::get();
        return $customer;
    }
    public function customerRegister(Request $request){
		
    	 $customerEmail=Customer::where('email',$request->email)->first();
    	 $customerPhone=Customer::where('phoneNumber',$request->phoneNumber)->first();
    	 if($customerEmail && $customerPhone){
    	   Toastr::error('message', 'Email Already Exist');
    	   Toastr::error('message', 'Phone Number Already Exist');
    	     $this->validate($request,[
                'email'=>'required|unique:customers',
                'phoneNumber'=>'required|unique:customers',
            ]);
    	   return redirect()->back();
    	 
    	 }elseif($customerEmail){
    	     Toastr::error('message', 'Email Already Exist');
    	     $this->validate($request,[
                'email'=>'required|unique:customers',
            ]);
    	   return redirect()->back();
    	 }elseif($customerPhone){
    	   Toastr::error('message', 'Phone Number Already Exist');
    	   $this->validate($request,[
                'phoneNumber'=>'required|unique:customers',
            ]);
    	   return redirect()->back();
    	 }else{
      		 $verifyToken=rand(111111,999999);
      		 $store_data				= 	new Customer();
             $store_data->fullName      =   $request->fullName;
    	     $store_data->phoneNumber 	=	$request->phoneNumber;
             $store_data->email       = $request->email;
    	     $store_data->verifyToken 	=	$verifyToken;
    	     $store_data->password 		=	bcrypt(request('password'));
    	     $store_data->save();
          
          // verify code send to customer mail
          $data=$store_data->toArray();
            $send = Mail::send('frontEnd.emails.email', $data, function($textmsg) use ($data){
              $textmsg->to($data['email']);
              $textmsg->subject('account veriry code');
            });
    
          // customer id put
          $customerId=$store_data->id;
          Session::put('customerId',$customerId);
          Toastr::success('message', 'Your information add successfully!');
          return redirect('customer/verify');
    	 }
    }
    public function registerPage(){
      return view('frontEnd.layouts.pages.customer.register');
    }
    public function customerLoginPage(){
      return view('frontEnd.layouts.pages.customer.login');
    }
    public function customerLogin(Request $request){
    	 $customerCheck =Customer::orWhere('email',$request->phoneOremail)
       ->orWhere('phoneNumber',$request->phoneOremail)
       ->first();
        if($customerCheck){
        	if($customerCheck->status == 0){
	          Toastr::success('message', 'Opps! your account has been suspends');
	           return redirect()->back();
	       }else{
	       	if(password_verify($request->password,$customerCheck->password)){
	       		if(Cart::instance('shopping')->count()!=0){
                    $customerId = $customerCheck->id;
                    Session::put('customerId',$customerId);
		       	   Toastr::success('congratulation you login successfully', 'success!');
	        	   return redirect('/checkout');
	        	}else{
	        		$customerId = $customerCheck->id;
                   Session::put('customerId',$customerId);
                   Toastr::success('congratulation you login successfully', 'success!');
	        		return redirect('/customer/account');
	        	}
	       	}else{
        	  Toastr::error('message', 'Opps! your password wrong');
          	  return redirect()->back();
	       	}

        	 }
        }else{
        	Toastr::error('message', 'Sorry! You have no account');
        	return redirect()->back();
        }
       
    }

    public function customerVerifyForm(){
        $customerId = Session::get('customerId');
        if($customerId==!Null){
        return view('frontEnd.layouts.pages.customer.verify');
        }
        return redirect('/');
    }

    public function customerVerify(Request $request){
        $this->validate($request,[
            'verifyPin'=>'required',
        ]);
        $customerId = Session::get('customerId');
        $verified=Customer::where('id',$customerId)->first();

        $verifydbtoken = $verified->verifyToken;
        $verifyformtoken= $request->verifyPin;
       if($verifydbtoken==$verifyformtoken){
             $verified->verifyToken = 1;
            $verified->save();
            Toastr::success('Your account is verified', 'success!');
            return redirect('customer/account');
       }else{
        Toastr::error('sorry your verify token wrong', 'Opps!');
        return redirect()->back();
       }
    }

    public function resendcode($id){
        $findcustomer=Customer::find($id);
        $verifyToken=rand(111111,999999);
        $findcustomer->verifyToken=$verifyToken;
        $findcustomer->save();

        // verify code send to customer mail
      $data=$findcustomer->toArray();
        $send = Mail::send('frontEnd.emails.email', $data, function($textmsg) use ($data){
          $textmsg->to($data['email']);
          $textmsg->subject('account veriry code');
        });
      return redirect('customer/verify');
    }
    public function shippingInfo(Request $request){
        $this->validate($request,[
            'name'=>'required',
            'phone'=>'required',
            'location'=>'required',
            'address'=>'required',
        ]);

       $shipping              =   new Shipping();
       $shipping->name        =   $request->name;
       $shipping->phone       =   $request->phone;
       $shipping->address     =   $request->address;
       $shipping->location     =   $request->location;
       $shipping->note        =   $request->note;
       $shipping->save();
       Session::put('shippingId',$shipping->id);
       Toastr::success('Thanks! your shipping address save successfull','success');
       return redirect('/payment');
    }
    public function paymentForm(){
        $cartproduct= Cart::instance('shopping')->content()->count();
        if($cartproduct!=NULL){
        if(Session::get('customerId')!=NULL || Session::get('socialcustomerId')!=NULL){
             if(Session::get('shippingId')!=NULL){
             return view('frontEnd.layouts.pages.payment');
            }
            else{
                Toastr::warning('Please write your shipping information','Opps!');
                return redirect('/checkout');
            }
         }
         else
         {
            Toastr::error('Sorry, Please login first','Opps!!');
             return redirect('/customer/login');
         } 
     }else{
        Toastr::error('Sorry, Your cart is empty','Opps!!');
        return redirect('/');
     }
    }
      public function shippingcharge($shipping){
        $shippingfee=Session::put('totalshipping',$shipping);
        return response()->json($shippingfee);
    }

      public function shippingcontent(){
       return view('frontEnd.layouts.pages.shippingcontent');
    }
    // guestorder

    public function payment(Request $request){
        
        $this->validate($request,[
            'paymentType'=>'required',
        ]);
        
         $paymentType=$request->paymentType;
         if($paymentType=="cash"){
          $cartProducts = Cart::instance('shopping')->content();
          $order = new Order();
          $order->customerId = Session::get('customerId')||Session::get('socialcustomerId');
          $order->shippingId = Session::get('shippingId');
          $order->orderTotal = Session::get('totalprice');
          $order->created_at = Carbon::now();
          $order->save();

          $payment = new Payment();
          $payment->orderId = $order->id;
          $payment->paymentType = $paymentType;
          $payment->created_at = Carbon::now();
          $payment-> save();

          $cartProducts = Cart::instance('shopping')->content();
          foreach($cartProducts as $cartProduct){
              $orderDetails = new Orderdetails();
              $orderDetails->orderId=$order->id;
              $orderDetails->ProductId=$cartProduct->id;
              $orderDetails->productName=$cartProduct->name;
              $orderDetails->productPrice=$cartProduct->price;
              $orderDetails->productSize=$cartProduct->options->size? $cartProduct->options->size:'';
              $orderDetails->productColor=$cartProduct->options->color? $cartProduct->options->color:'';
              $orderDetails->productQuantity=$cartProduct->qty;
              $orderDetails->created_at = Carbon::now();
              $orderDetails->save();
          }
        Cart::destroy();
        Toastr::success('Your order send successfully', 'success!');
        return redirect('/');
            
       }elseif($paymentType=="online"){

          $customer = Customer::where('id', Session::get('customerId'))->get();
          $shipping = Shipping::where('shippingPrimariId', Session::get('shippingId'))->get();

          $order = new Order();
          $order->customerId = Session::get('customerId');
          $order->shippingId = Session::get('shippingId');
          $order->orderTotal = Session::get('totalprice');
          $order->created_at = Carbon::now();
          $order->save();
          
          $payment = new Payment();
          $payment->orderId = $order->id;
          $payment->paymentType = $paymentType;
          $payment->created_at = Carbon::now();
          $payment-> save();
          
          $cartProducts = Cart::instance('shopping')->content();
          $productName = array();
          foreach($cartProducts as $cartProduct){
              array_push($productName, $cartProduct->name);
              $orderDetails = new Orderdetails();
              $orderDetails->orderId=$order->id;
              $orderDetails->ProductId=$cartProduct->id;
              $orderDetails->productName=$cartProduct->name;
              $orderDetails->productPrice=$cartProduct->price;
              $orderDetails->productSize=$cartProduct->options->size? $cartProduct->options->size:'';
              $orderDetails->productColor=$cartProduct->options->color? $cartProduct->options->color:'';
              $orderDetails->productQuantity=$cartProduct->qty;
              $orderDetails->created_at = Carbon::now();
              $orderDetails->save();
          }
          
          Cart::destroy();

          $post_data                  = array();
          $post_data['total_amount']  = Session::get('totalprice');
          $post_data['currency']      = "BDT";
          $post_data['tran_id']       = $order->id;

          # CUSTOMER INFORMATION
          $post_data['cus_name']      = $customer[0]->fullName;
          $post_data['cus_email']     = $customer[0]->email;
          $post_data['cus_add1']      = "Dhaka";
          $post_data['cus_add2']      = "";
          $post_data['cus_city']      = "Dhaka";
          $post_data['cus_state']     = "";
          $post_data['cus_postcode']  = "1000";
          $post_data['cus_country']   = "Bangladesh";
          $post_data['cus_phone']     = $customer[0]->phoneNumber;
          $post_data['cus_fax']       = "";

          # SHIPMENT INFORMATION
          $post_data['ship_name']     = $shipping[0]->name;
          $post_data['ship_add1']     = $shipping[0]->address;
          $post_data['ship_add2']     = "";
          $post_data['ship_city']     = "Dhaka";
          $post_data['ship_state']    = "";
          $post_data['ship_postcode'] = "1000";
          $post_data['ship_phone']    = $shipping[0]->phone;
          $post_data['ship_country']  = "Bangladesh";

          $post_data['shipping_method']   = "NO";
          $post_data['product_name']      = implode(" ",$productName);
          $post_data['product_category']  = "Goods";
          $post_data['product_profile']   = "physical-goods";
          

          $sslc = new SslCommerzNotification();
          # initiate(Transaction Data , false: Redirect to SSLCOMMERZ gateway/ true: Show all the Payement gateway here )
          $payment_options = $sslc->makePayment($post_data, 'hosted');
          
          if (!is_array($payment_options)) {
              print_r($payment_options);
              $payment_options = array();
          }
          
       }
    }
    
    public function successPayment(Request $request)
    {

        $tran_id = $request->input('tran_id');
        $amount = $request->input('amount');
        $currency = $request->input('currency');
        
         $update_product = DB::table('orders')
                    ->where('orderIdPrimary', $tran_id)
                    ->update(['orderStatus' => 1]);
                    
        Cart::destroy();
        Toastr::success('Your order send successfully', 'success!');
        return redirect('/');

    }
    
    
    
    public function customerAccount(){
      $customerId=Session::get('customerId');
      $customerInfo=Customer::where('id',$customerId)->first();
      if($customerId!=NULL){
          if($customerInfo->verifyToken==1){
          return view('frontEnd.layouts.pages.customer.customerProfile');
        }else{
          Toastr::error('Sorry, Your account is not verified.','Opps!!');
          return redirect('customer/verify');
        }
        
        }
        else{
           Toastr::error('Sorry, Please login first','Opps!!');
             return redirect('/customer/login');
        }
    }


    public function customerQuestion(Request $request){
      $this->validate($request,[
          'question'=>'required',
          ]);
              $question = new Comment();
              $question->product_id=$request->product_id;
              $question->question=$request->question;
              $question->answer='no answer';
              $question->customer_id=$request->customer_id;
              $question->created_at = Carbon::now();
              $question->save();

        Toastr::success('your comments added successfully', 'success!');
        return redirect()->back();
    }
    public function customerReview(Request $request){
      $this->validate($request,[
          'review'=>'required',
          ]);
              $question = new Ratting();
              $question->product_id=$request->product_id;
              $question->review=$request->review;
              $question->customer_id=$request->customer_id;
              $question->created_at = Carbon::now();
              $question->save();

        Toastr::success('your comments added successfully', 'success!');
        return redirect()->back();
    }

    public function customerLogout(){
        Session::flush();
        Toastr::success('You are logout successfully', 'success!');
        return redirect('/');
    }

}
