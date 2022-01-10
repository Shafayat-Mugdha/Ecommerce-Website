<?php

namespace App\Http\Controllers\editor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Orderdetails;
use App\Prescription;
use App\Customermessage;
use App\Comment;
use App\Order;
use App\Guestorder;
use DB;
class ReportsController extends Controller
{
     public function ordermanage(){
    	$show_data = DB::table('orders')
        ->join('customers','orders.customerId','=','customers.id')
        ->select('orders.*','customers.fullName','customers.phoneNumber')
        ->orderBy('orderIdPrimary','DESC')
        ->get();
        return view('backEnd.reports.ordermanage',compact('show_data'));
    }
     public function guestorder(){
      $guestorder = Guestorder::orderBy('id','DESC')
        ->get();
        return view('backEnd.reports.guestorder',compact('guestorder'));
    }
    public function details($shippingId,$customerId,$orderIdPrimary){
	    	$customerInfo = DB::table('orders')
	        ->join('customers','orders.customerId','=','customers.id')
	        ->join('payments','orders.orderIdPrimary','=','payments.orderId')
	        ->where('customers.id',$customerId)
	        ->select('orders.*','customers.fullName','customers.phoneNumber','customers.address','payments.paymentType')
	        ->first();

	        $paymentmethod = DB::table('payments')
	        ->join('orders','payments.orderId','=','orders.orderIdPrimary')
	        ->where('payments.orderId',$orderIdPrimary)
	        ->select('payments.*','orders.orderIdPrimary')
	        ->first();

	        $shippingInfo = DB::table('shippings')
	        ->join('orders','shippings.shippingPrimariId','=','orders.shippingId')
	        ->where('shippings.shippingPrimariId',$shippingId)
	        ->select('shippings.*','orders.*')
	        ->first();
	        $orderDetails = DB::table('orderdetails')
	        ->join('products','orderdetails.ProductId','=','products.id')
	        ->where('orderdetails.orderId',$orderIdPrimary)
	        ->select('orderdetails.*','products.*')
	        ->get();
	        return view('backEnd.reports.invoice', [
	            'shippingInfo'=> $shippingInfo,
	            'customerInfo'=> $customerInfo,
	            'orderDetails'=> $orderDetails,
	            'paymentmethod'=> $paymentmethod,
	        ]);
	    }

	    
	   public function adminmessage(Request $request){
	   	$this->validate($request,[
            'message'=>'required',
        ]);
       $conversion              		=   new Adminmessage();
       $conversion->message     		=   $request->message;
       $conversion->prescription_id     =   $request->prescriptionId;
       $conversion->admin     			=   $request->admin;
       $conversion->save();
       Toastr::success('Message send successfully','success');
       return redirect()->back();
	   }

	   public function comment(){
	   	$comments = DB::table('comments')
          ->join('customers','comments.customer_id','=','customers.id')
          ->join('products','comments.product_id','=','products.id')
            ->where('comments.status',0)           
             ->select('comments.*','customers.fullName','products.id as product_id')
            ->get();
	    return view('backEnd.reports.comment',compact('comments'));
	   }

	   public function commentAnswer($id){
	   	$acomments = DB::table('comments')
          ->join('customers','comments.customer_id','=','customers.id')
          ->join('products','comments.product_id','=','products.id')
            ->where('comments.id',$id)        
             ->select('comments.*','customers.fullName','products.id as product_id')
            ->first();
	    return view('backEnd.reports.answer',compact('acomments'));
	   }

	   public function sendAnswer(Request $request){
        $update_data             =   Comment::find($request->hidden_id);
        $update_data->answer     =    $request->answer;
        $update_data->admin_id   =    $request->admin_id;
        $update_data->status     =    1;
        $update_data->save();
        Toastr::success('message', 'Your message send successfully!');
        return redirect('editor/comment/unread');
    }
       public function allComments(){
	   	$allComments = DB::table('comments')
          ->join('customers','comments.customer_id','=','customers.id')
          ->join('products','comments.product_id','=','products.id')      
          ->join('users','comments.admin_id','=','users.id')      
             ->select('comments.*','customers.fullName','products.id as product_id','users.name')
            ->get();
	    return view('backEnd.reports.allcomment',compact('allComments'));
	   }
	   public function deleteComment(Request $request){
        $delete_data = Comment::find($request->hidden_id);
        $delete_data->delete();
        Toastr::success('message', 'Comment delete successfully!');
        return redirect('/editor/comment/all');
    }
    
     public function orderPaid($orderIdPrimary){
        $published_data = Order::where('orderIdPrimary',$orderIdPrimary)->update(['orderStatus' => 1]);
        Toastr::success('message', 'payment paid successfully!');
        return redirect()->back();
    }
}
