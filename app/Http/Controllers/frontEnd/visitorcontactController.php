<?php

namespace App\Http\Controllers\frontEnd;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use App\Post;
 use Mail;
class visitorcontactController extends Controller
{
   public function visitorcontact(Request $request){
      $this->validate($request, [
         'contact_subject'=>'required',
         'contact_email'=>'required',
         'name'=>'required',
         'contact_phone'=>'required',
         'contact_text'=>'required',
        ]);
      $data = array(
         'contact_subject' => $request->contact_subject,
         'contact_email' => $request->contact_email,
         'name' => $request->name,
         'contact_phone' => $request->contact_phone,
         'contact_text' => $request->contact_text,
        );
        
        // return $data;

        $send = Mail::send('frontEnd.emails.email', $data, function($textmsg) use ($data){
         $textmsg->from($data['contact_email']);
         $textmsg->to('info@ayshaenterprise.com');
         $textmsg->subject($data['contact_text']);
        });

        Toastr::success('message', 'Thanks! your message send successfully!');
        return redirect()->back();
        }
}
