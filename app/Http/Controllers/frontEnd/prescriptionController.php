<?php

namespace App\Http\Controllers\frontEnd;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Customer;
use App\Prescription;
use App\Customermessage;
use App\Adminmessage;
use DB;
use Session;
class prescriptionController extends Controller
{
     public function perscription(){
      return view('frontEnd.layouts.pages.perscription');
      
    }
    public function newprescription(){
       if(Session::get('customerId')!=NULL){
      return view('frontEnd.layouts.pages.newprescription');
      } 
      else
       {
          Toastr::error('Sorry, Please login first','Opps!!');
           return redirect('/customer/login');
       } 
    }

    public function newupload(Request $request){
			$this->validate($request,[
			'address'=>'required',
			'image'=>'required|unique:customers',
    		]);

			 // image upload
	    	$file = $request->file('image');
	    	$name = time().'-'.$file->getClientOriginalName();
	    	$uploadPath = 'public/uploads/prescription/';
	    	$file->move($uploadPath,$name);
	    	$fileUrl =$uploadPath.$name;

  		 $verifyToken=rand(1111,9999);
  		 $store_data			= 	new Prescription();
         $store_data->customer   =   $request->customerId;
         $store_data->address   =   $request->address;
         $store_data->image     =   $fileUrl;
	     $store_data->save();


	     // only message save
	     $pId=$store_data->id;
	     $prescription						= 	new Customermessage();
	   	 $prescription->prescription_id     =   $pId;
         $prescription->message  		    =   $request->message;
         $prescription->customer   			=   $request->customerId;
	     $prescription->save();


	    $customer=Customer::find($request->customerId);
	    $customername=$customer->fullName;
	    $api_key = "C20023825c0e4e9eebcfc2.68612628";
        $contacts = '8801742892725';
        $senderid = '24541';
        $sms = "$customername send a new prescription";
        $URL ="http://bangladeshsms.com/smsapi?api_key=".urlencode($api_key)."&type=text&contacts=".urlencode($contacts)."&senderid=".urlencode($senderid)."&msg=".urlencode($sms);
        return $this->SendSMS($URL);
    }
    function SendSMS($URL) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$URL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 0);
        try{
        $output = $content=curl_exec($ch);
        Toastr::success('Thank you. Your prescription upload successfully', 'success!');
         return redirect('/customer/prescription');
         $customerId = $store_data->id;
       	 Session::put('customerId',$customerId);
        }catch(Exception $ex){
        $output = "-100";
        }
        return $output;
    }
    public function sendmessage($id){
    	if(Session::get('customerId')!=NULL){
    	$sendmessage = DB::table('prescriptions')
    	->join('customers','prescriptions.customer','=','customers.id')
    	->where('prescriptions.id',$id)
    	->select('prescriptions.*','customers.fullName','customers.image as cimage')
    	->first();
	    	
	    	$customermessages=Customermessage::where('prescription_id',$id)
	    	->get();

	    	$customermessages_id=Customermessage::where('prescription_id',$id)
	    	->orderby('id','DESC')
	    	->first();
	    	// return $customermessages_id;
	    	$adminmessages=Adminmessage::where('prescription_id',$id)
	    	->get();
	    	return view('frontEnd.layouts.pages.customer.sendmessage',compact('sendmessage','customermessages','adminmessage_id','adminmessages','customermessages_id'));
	    }
	    else{
        Toastr::error('Sorry, Please login first','Opps!!');
           return redirect('/customer/login');
      }
    }
    public function customermessage(Request $request){
    	$this->validate($request,[
            'message'=>'required',
        ]);

       $cmessage              		=   new Customermessage();
       $cmessage->message     		=   $request->message;
       $cmessage->prescription_id   =   $request->prescriptionId;
       $cmessage->customer     		=   $request->customer;
       // return $cmessage;
       $cmessage->save();
       Toastr::success('Message send successfully','success');
       return redirect()->back();
    }

}
