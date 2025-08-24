<?php

namespace App\Http\Controllers;

use App\DataTables\SupportDataTable;
use App\Models\EmailTemplate;
use App\Models\HelpdeskConversion;
use App\Models\HelpdeskTicket;
use App\Models\HelpdeskTicketCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class HelpdeskTicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SupportDataTable $dataTable)
    {
        if (Auth::user()->isAbleTo('helpdesk ticket manage')) {
            return $dataTable->render('helpdesk_ticket.index');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Auth::user()->isAbleTo('helpdesk ticket create')) {
            $categories = HelpdeskTicketCategory::get();
            $users = [];
            if(Auth::user()->type =='super admin')
            {
                $users = User::where('type', 'company')->get()->pluck('name', 'id');
            }

            return view('helpdesk_ticket.create', compact('categories', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('helpdesk ticket create')) {
            if(Auth::user()->type =='super admin')
            {
                $validation = [
                    'name' => 'required',
                    'email' => 'required|string|email|max:255',
                    'category' => 'required|string|max:255',
                    'subject' => 'required|string|max:255',
                    'status' => 'required|string|max:100',
                ];
                $validator = \Validator::make(
                    $request->all(),
                    $validation
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->withInput()->with('error', $messages->first());
                }

                $user = User::find($request->name);
            }
            else
            {
                $validation = [
                    'category' => 'required|string|max:255',
                    'subject' => 'required|string|max:255',
                    'status' => 'required|string|max:100',
                ];
                $validator = \Validator::make(
                    $request->all(),
                    $validation
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->withInput()->with('error', $messages->first());
                }

                $user = Auth::user();
            }

            $ticket                  = new HelpdeskTicket();
            $ticket->ticket_id       = time() ;
            $data = [];
            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {

                    $name = $file->getClientOriginalName();
                    $data[] = [
                        'name' => $name,
                        'path' => 'uploads/helpdesk/' . $ticket->ticket_id . '/' . $name,
                    ];
                    multi_upload_file($file, 'attachments', $name, 'helpdesk/' . $ticket->ticket_id);
                }
            }
            $ticket->name           = !empty($user) ? $user->name : '';
            $ticket->email          = $user->email;
            $ticket->attachments    = json_encode($data) ;
            $ticket->category       = $request->category ;
            $ticket->status         = $request->status ;
            $ticket->subject        = $request->subject ;
            $ticket->description    = !empty($request->description) ? $request->description : ''  ;
            $ticket->user_id        = $user->id;
            $ticket->created_by     = creatorId() ;
            if(Auth::user()->type == 'super admin'){

                $ticket->workspace      = getActiveWorkSpace($user->id);
            }else{
                $ticket->workspace      = getActiveWorkSpace();
            }
            $ticket->save();
            $user = User::where('id', $ticket->created_by)->first();
            $ticket_url = route('helpdesk.view', [Crypt::encrypt($ticket->ticket_id)]);
            if(!empty(admin_setting('New Helpdesk Ticket')) && admin_setting('New Helpdesk Ticket')  == true)
            {
                $uArr = [
                    'ticket_name' => $ticket->name,
                    'email' => $ticket->email,
                    'ticket_id' => $ticket->ticket_id,
                    'ticket_url' => $ticket_url,
                    'company_name' => company_setting('company_name',$ticket->user_id)
                ];

                try
                {
                    if(Auth::user()->type == 'super admin')
                    {
                        $resp = EmailTemplate::sendEmailTemplate('New Helpdesk Ticket', [$ticket->email], $uArr);
                    }else
                    {
                        if($user->type != 'super admin'){
                            $user        = User::where('type', 'super admin')->first();
                        }
                        $resp = EmailTemplate::sendEmailTemplate('New Helpdesk Ticket', [$user->email], $uArr);
                    }
                }
                catch(\Exception $e)
                {
                    $resp['error'] = $e->getMessage();
                }
                return redirect()->route('helpdesk.index')->with('success', __('The ticket has been created successfully') . ((isset($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }
            return redirect()->route('helpdesk.index')->with('success', __('The ticket has been created successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HelpdeskTicket  $helpdeskTicket
     * @return \Illuminate\Http\Response
     */
    public function show(HelpdeskTicket $helpdeskTicket, $ticket_id)
    {
        $ticket_id = Crypt::decrypt($ticket_id);
        $ticket    = HelpdeskTicket::where('ticket_id', '=', $ticket_id)->first();

        if ($ticket) {
            return view('helpdesk_ticket.show', compact('ticket'));
        } else {
            return redirect()->back()->with('error', __('Some thing is wrong'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HelpdeskTicket  $helpdeskTicket
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = \Auth::user();
        if (Auth::user()->isAbleTo('helpdesk ticket show')) {
            $ticket = HelpdeskTicket::find($id);
            if ($ticket) {
                $categories = HelpdeskTicketCategory::get();
                $users = [];
                if(Auth::user()->type =='super admin')
                {
                    $users = User::where('type', 'company')->get()->pluck('name', 'id');
                }
                return view('helpdesk_ticket.edit', compact('ticket', 'categories','users'));
            } else {
                return view('403');
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HelpdeskTicket  $helpdeskTicket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        if (Auth::user()->isAbleTo('helpdesk ticket edit')) {
            $ticket                 = HelpdeskTicket::find($id);
            if(Auth::user()->type =='super admin')
            {
                $validation = [
                    'name' => 'required',
                    'email' => 'required|string|email|max:255',
                    'category' => 'required|string|max:255',
                    'subject' => 'required|string|max:255',
                    'status' => 'required|string|max:100',
                ];
                $validator = \Validator::make(
                    $request->all(),
                    $validation
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->withInput()->with('error', $messages->first());
                }

                $user = User::find($request->name);
            }
            else
            {
                $validation = [
                    'category' => 'required|string|max:255',
                    'subject' => 'required|string|max:255',
                    'status' => 'required|string|max:100',
                ];
                $validator = \Validator::make(
                    $request->all(),
                    $validation
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->withInput()->with('error', $messages->first());
                }

                $user = Auth::user();
            }



            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $name = $file->getClientOriginalName();
                    $data[] = [
                        'name' => $name,
                        'path' => 'uploads/helpdesk/' . $ticket->ticket_id . '/' . $name,
                    ];
                    multi_upload_file($file, 'attachments', $name, 'helpdesk/' . $ticket->ticket_id);
                }
                if ($request->hasfile('attachments')) {
                    $json_decode = json_decode($ticket->attachments);
                    $attachments = json_encode(array_merge($json_decode, $data));
                } else {
                    $attachments = json_encode($data);
                }
                $ticket->attachments = isset($attachments) ? $attachments : null;
            }


            $ticket->name           = $user->name;
            $ticket->user_id        = $user->id;
            $ticket->email          = !empty($user->email) ? $user->email : '';
            $ticket->category       = !empty($request->category) ? $request->category : '';
            $ticket->subject        = !empty($request->subject) ? $request->subject : '';
            $ticket->status         = !empty($request->status) ? $request->status : '';
            $ticket->description    = !empty($request->description) ? $request->description : '';
            if(Auth::user()->type == 'super admin'){

                $ticket->workspace      = getActiveWorkSpace($user->id);
            }else{
                $ticket->workspace      = getActiveWorkSpace();
            }
            $ticket->save();

            return redirect()->back()->with('success', __('The ticket details are updated successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HelpdeskTicket  $helpdeskTicket
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('helpdesk ticket delete')) {
            $ticket = HelpdeskTicket::find($id);
            $conversions = HelpdeskConversion::where('ticket_id', $ticket->id)->get();
            if (count($conversions) > 0) {
                $conversions = HelpdeskConversion::where('ticket_id', $ticket->id)->delete();
            }
            delete_folder('helpdesk/' . $ticket->ticket_id);
            $ticket->delete();
            return redirect()->back()->with('success', __('The ticket has been deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function  getUser(Request $request)
    {
        $user = User::find($request->user_id);
        if ($user) {
            $userData = [
                'name' => $user->name,
                'email' => $user->email,
            ];
            return response()->json($userData);
        } else {
            return response()->json(['error' => 'User not found']);
        }

    }

    public function attachmentDestroy($ticket_id, $id)
    {
        $ticket = HelpdeskTicket::find($ticket_id);
        $attachments = json_decode($ticket->attachments);
        if (isset($attachments[$id])) {
            delete_file($attachments[$id]->path);
            unset($attachments[$id]);

            $ticket->attachments = json_encode(array_values($attachments));
            $ticket->save();

            return redirect()->back()->with('success', __('Attachment has been deleted'));
        } else {
            return redirect()->back()->with('error', __('Attachment is missing'));
        }
    }
    public function storeNote($ticketID, Request $request)
    {

        $ticket = HelpdeskTicket::find($ticketID);
        if ($ticket) {
            $ticket->note = $request->note;
            $ticket->save();

            return redirect()->back()->with('success', __('Ticket note saved successfully'));
        } else {
            return view('403');
        }
    }

    public function reply($ticket_id, Request $request)
    {

        $ticket = HelpdeskTicket::where('ticket_id', '=', $ticket_id)->first();
        if ($ticket) {
            $validation = [
                'reply_description' => 'required'
            ];
            $validator = \Validator::make(
                $request->all(),
                $validation
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->withInput()->with('error', $messages->first());
            }

            $post                = [];
            $post['sender']      = 'user';
            $post['ticket_id']   = $ticket->id;
            $post['description'] = $request->reply_description;
            $data                = [];
            if ($request->hasfile('reply_attachments')) {
                foreach ($request->file('reply_attachments') as $file) {

                    $name = $file->getClientOriginalName();
                    $data[] = [
                        'name' => $name,
                        'path' => 'uploads/helpdesk/' . $ticket->ticket_id . '/' . $name
                    ];
                    $path = multi_upload_file($file, 'attachments', $name, 'helpdesk/' . $ticket->ticket_id);
                    if($path['flag'] == '0')
                    {
                         return redirect()->back()->with('error',$path['msg']);
                    }
                }
            }
            $post['attachments'] = json_encode($data);
            HelpdeskConversion::create($post);

            // Send Email to User
            try {
                if(Auth::check())
                {
                    $user        = User::where('id', Auth::user()->id)->first();
                }
                else
                {
                    $user        = User::where('id', $ticket->created_by)->first();
                }
                if(!empty(admin_setting('New Helpdesk Ticket Reply')) && admin_setting('New Helpdesk Ticket Reply')  == true)
                {
                    $uArr = [
                        'ticket_name' => $ticket->name,
                        'ticket_id' => $ticket->ticket_id,
                        'email' => $ticket->email,
                        'reply_description' => $request->reply_description,
                        'company_name' => company_setting('company_name',$ticket->user_id)
                    ];
                    try
                    {
                        if(Auth::check()){
                            if(Auth::user()->type == 'super admin')
                            {
                                EmailTemplate::sendEmailTemplate('New Helpdesk Ticket Reply', [$ticket->email], $uArr,$user->id);
                            }else
                            {
                                if($user->type != 'super admin'){
                                    $user        = User::where('type', 'super admin')->first();
                                }
                                EmailTemplate::sendEmailTemplate('New Helpdesk Ticket Reply', [$user->email], $uArr,$user->id);
                            }
                        }else
                        {
                            if($user->type != 'super admin'){
                                $user        = User::where('type', 'super admin')->first();
                            }
                            EmailTemplate::sendEmailTemplate('New Helpdesk Ticket Reply', [$user->email], $uArr,$user->id);
                        }
                    }
                    catch(\Exception $e)
                    {
                        $resp['error'] = $e->getMessage();
                    }
                }
            } catch (\Exception $e) {
                $resp['status'] = false;
                $resp['msg'] = $e->getMessage();
            }
            return redirect()->back()->with('success', __('Reply added successfully.') . ((isset($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));

        } else {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
    }
}
