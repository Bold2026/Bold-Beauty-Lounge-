<?php

namespace App\Http\Controllers;

use App\Events\CreateUser;
use App\Events\DefaultData;
use App\Events\DestroyUser;
use App\Events\EditProfileUser;
use App\Events\UpdateUser;
use App\Models\EmailTemplate;
use App\Models\LoginDetail;
use App\Models\Plan;
use App\Models\ReferralTransaction;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSpace;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Auth\Events\Registered;
use Lab404\Impersonate\Impersonate;
use App\DataTables\UsersDataTable;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if(Auth::user()->isAbleTo('user manage'))
        {
            if(Auth::user()->type == 'super admin')
            {
                $roles =[];
                $users = User::where('type','company')->paginate(11);
            }
            else
            {
                $roles = Role::where('created_by',creatorId())->pluck('name','id')->map(function ($name) {
                    return ucfirst($name);
                });
                if(Auth::user()->isAbleTo('workspace manage'))
                {
                    $users = User::where('created_by',creatorId())->where('workspace_id',getActiveWorkSpace());
                }
                else
                {
                    $users = User::where('created_by',creatorId());
                }

                if($request->name)
                {
                    $users->where('name', 'like', '%' . $request->name . '%');
                }
                if($request->email)
                {
                    $users->where('email', 'like', '%' . $request->email . '%');
                }
                if($request->role)
                {
                    $role = Role::find($request->role);
                    $users = $users->where('type',$role->name);
                }
                $users = $users->paginate(11);
            }
            return view('users.index',compact('users','roles'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function List(UsersDataTable $dataTable)
    {
        if(Auth::user()->isAbleTo('user manage'))
        {
            $roles = [];
            if(Auth::user()->type != 'super admin')
            {
                $roles = Role::where('created_by',creatorId())->pluck('name','id')->map(function ($name) {
                    return ucfirst($name);
                });
            }
            return $dataTable->render('users.list',compact('roles'));
        }
        else
        {
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
        if(Auth::user()->isAbleTo('user create'))
        {
            $roles = Role::where('created_by',creatorId())->pluck('name','id');
            return view('users.create',compact('roles'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
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
        if(Auth::user()->isAbleTo('user create'))
        {
            if(Auth::user()->type != 'super admin'){
                $canUse=  PlanCheck('User',Auth::user()->id);
                if($canUse == false)
                {
                    return redirect()->back()->with('error', __('You have maxed out the total number of User allowed on your current plan'));
                }
            }
            if (Auth::user()->type == 'super admin') {
                $validatorArray = [
                    'name' => 'required|max:120',
                    'email' => ['required', 'email',
                        Rule::unique('users')->where(function ($query) {
                            return $query->where('created_by', creatorId());
                        })
                    ],
                ];
            } else {
                $validatorArray = [
                    'name' => 'required|max:120',
                    'roles' => 'required|exists:roles,id',
                    'email' => ['required', 'email',
                        Rule::unique('users')->where(function ($query) {
                            return $query->where('created_by', creatorId())
                                         ->where('workspace_id', getActiveWorkSpace());
                        })
                    ],
                ];
            }

            $validator = Validator::make($request->all(), $validatorArray);

            if($validator->fails())
            {
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            $user['is_enable_login']       = 0;
            if(!empty($request->password_switch) && $request->password_switch == 'on')
            {
                $user['is_enable_login']   = 1;
                $validator = Validator::make(
                    $request->all(), ['password' => 'required|min:6']
                );

                if($validator->fails())
                {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }

                $userpassword               = $request->input('password');
            }
            if($request->input('mobile_no')){
                $validator = Validator::make(
                    $request->all(), ['mobile_no' => 'nullable|regex:/^\+\d{1,3}\d{9,13}$/',]
                );
                if($validator->fails())
                {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }
            }
            if(Auth::user()->type == 'super admin')
            {
                $roles = Role::where('name','company')->first();
            }
            else
            {
                $roles = Role::find($request->input('roles'));
            }
            $company_settings = getCompanyAllSetting();


            $user['name']               = $request->input('name');
            $user['email']              = $request->input('email');
            $user['mobile_no']          = $request->input('mobile_no');
            $user['password']           = !empty($userpassword) ? \Hash::make($userpassword) : null;
            $user['lang']               = !empty($company_settings['defult_language']) ? $company_settings['defult_language'] : 'en';
            $user['type']               = $roles->name;
            $user['created_by']         = creatorId();
            $user['workspace_id']       = getActiveWorkSpace();
            $user['active_workspace']   = getActiveWorkSpace();
            $user = User::create($user);
            if(Auth::user()->type == 'super admin')
            {
                    do {
                        $code = rand(100000, 999999);
                    } while (User::where('referral_code', $code)->exists());

                $company = User::find($user->id);

                 // create  WorkSpace
                $workspace = new WorkSpace();
                $workspace->name       = !empty($request->workSpace_name) ? $request->workSpace_name : $request->name;
                $workspace->created_by = $company->id;
                $workspace->save();

                $company->referral_code  = $code;
                $company->active_workspace = $workspace->id;
                $company->workspace_id = $workspace->id;
                $company->save();

                // comapny setting
                User::CompanySetting($company->id);

                //  create role
                $user->MakeRole();

                $plan = Plan::where('is_free_plan',1)->first();
                if($plan)
                {
                    $user->assignPlan($plan->id,'Month',$plan->modules,0,$user->id);
                }


                $role_r = Role::where('name','company')->first();
            }
            else
            {
                $role_r = Role::find($roles->id);
            }

            $user->addRole($role_r);
            event(new CreateUser($user,$request));

            SetConfigEmail(Auth::user()->id);
            if ( admin_setting('email_verification') == 'on')
            {
                try {
                    //code...
                    $user->sendEmailVerificationNotification();

                    // event(new Registered($user));
                } catch (\Throwable $th) {

                }
            }
            else
            {
                $user_data = User::find($user->id);
                $user_data->email_verified_at = date('Y-m-d h:i:s');
                $user_data->save();
            }


            //Email notification

            if(Auth::user()->type == 'super admin'){
                $msg =  __('The customer has been created successfully.');
            }
            else{
                $msg =  __('The user has been created successfully.');
            }
            if( (!empty($company_settings['Create User']) && $company_settings['Create User']  == true ))
            {
                $uArr = [
                    'email'=>$request->input('email'),
                    'password'=> $request->input('password'),
                ];
                $resp = EmailTemplate::sendEmailTemplate('New User', [$user->email], $uArr);
                return redirect()->back()->with('success', $msg. ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            }

            return redirect()->back()->with('success', $msg);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return redirect()->route('users.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(Auth::user()->isAbleTo('user edit'))
        {
            $user = User::find($id);
            $roles = Role::where('created_by',creatorId())->pluck('name','id');
            return view('users.edit',compact('user','roles'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if(Auth::user()->isAbleTo('user edit'))
        {
            if (Auth::user()->type == 'super admin') {
                $validatorArray = [
                    'name' => 'required|max:120',
                    'email' => [
                        'required',
                        'email',
                        Rule::unique('users')->ignore($id)->where(function ($query) {
                            return $query->where('created_by', creatorId());
                        }),
                    ],
                ];
            } else {
                $validatorArray = [
                    'name' => 'required|max:120',
                    'email' => [
                        'required',
                        'email',
                        Rule::unique('users')->ignore($id)->where(function ($query) {
                            return $query->where('created_by', creatorId())
                                         ->where('workspace_id', getActiveWorkSpace());
                        }),
                    ],
                ];
            }

            $validator = Validator::make(
                $request->all(), $validatorArray
            );

            if($validator->fails())
            {
                return redirect()->back()->with('error', $validator->errors()->first());
            }
            if($request->input('mobile_no')){
                $validator = Validator::make(
                    $request->all(), ['mobile_no' => 'nullable|regex:/^\+\d{1,3}\d{9,13}$/']
                );
                if($validator->fails())
                {
                    return redirect()->back()->with('error', $validator->errors()->first());
                }
            }
            $user = User::find($id);
            if(!empty($user))
            {
                if(Auth::user()->type == 'super admin')
                {
                    $role = Role::where('name','company')->first();
                }
                $user->name         = $request->name;
                $user->email        = $request->email;
                $user->mobile_no    = $request->mobile_no;
                $user->save();

                event(new UpdateUser($user,$request));
                if(Auth::user()->type == 'super admin'){
                    $msg =  __('The customer details are updated successfully.');
                }
                else{
                    $msg =  __('The user details are updated successfully.');
                }
                return redirect()->back()->with(
                    'success', $msg
                );
            }
            return redirect()->back()->with('error', __('Something is wrong.'));
        }
        else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(Auth::user()->isAbleTo('user delete'))
        {
            $user = User::findOrFail($id);

             // first parameter user
             event(new DestroyUser($user));

            try
            {
                // get all table
                $tables_in_db = \DB::select('SHOW TABLES');
                $db = "Tables_in_" . env('DB_DATABASE');
                foreach($tables_in_db as $table)
                {
                    if (Schema::hasColumn($table->{$db}, 'created_by'))
                    {
                        \DB::table($table->{$db})->where('created_by', $user->id)->delete();
                    }
                }
                ReferralTransaction::where('company_id' , $id)->delete();
                $user->delete();
            }
            catch (\Exception $e)
            {

            }
            if(Auth::user()->type == 'super admin'){
                $msg =  __('The customer has been deleted.');
         }
            else{
                $msg =  __('The user has been deleted');
            }
            return redirect()->back()->with('success',$msg);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function profile()
    {
        if(Auth::user()->isAbleTo('user profile manage'))
        {
            $userDetail = \Auth::user();

            return view('users.profile')->with('userDetail', $userDetail);
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function editprofile(Request $request)
    {
        if(Auth::user()->isAbleTo('user profile manage'))
        {
            $userDetail = \Auth::user();
            $user = User::findOrFail($userDetail['id']);

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:120',
                    'mobile_no' => 'nullable|regex:/^\+\d{1,3}\d{9,13}$/',
                    'email' => [
                        'required',
                        Rule::unique('users')->where(function ($query) use ($user) {
                            return $query->whereNotIn('id', [$user->id])->where('created_by', $user->created_by)->where('workspace_id', $user->workspace_id);
                        })
                    ],
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            if ($request->hasFile('profile')) {
                $filenameWithExt = $request->file('profile')->getClientOriginalName();
                $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension = $request->file('profile')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;

                $path = upload_file($request, 'profile', $fileNameToStore, 'users-avatar');

                if($path['flag'] == 0)
                {
                    return redirect()->back()->with('error',$path['msg']);
                }

                // Old img delete
                if (!empty($userDetail['avatar']) && strpos($userDetail['avatar'], 'avatar.png') == false && check_file($userDetail['avatar'])) {
                    delete_file($userDetail['avatar']);
                }

                $user->avatar = $path['url'];
            }

            $user->name = $request['name'];
            $user->email = $request['email'];
            $user->mobile_no = $request['mobile_no'];
            $user->save();
            // Update the student's profile if the user is a student
            if ($user->hasRole('student')) {
                $student = $user->musicStudent;

                if ($student) {
                    $student->avatar = $user->avatar;
                    $student->save();
                }
            }
            if ($user->hasRole('staff')) {
                $teacher = $user->musicTeacher;

                if ($teacher) {
                    $teacher->avatar = $user->avatar;
                    $teacher->save();
                }
            }

            // Trigger events
            event(new EditProfileUser($request, $user));

            return redirect()->back()->with('success',__('Profile details are updated successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function updatePassword(Request $request)
    {
        if(Auth::user()->isAbleTo('user profile manage'))
        {
            if (\Auth::Check()) {
                $request->validate(
                    [
                        'current_password' => 'required',
                        'new_password' => 'required|min:6',
                        'confirm_password' => 'required|same:new_password',
                    ]
                );
                $objUser          = Auth::user();
                $request_data     = $request->All();
                $current_password = $objUser->password;
                if (Hash::check($request_data['current_password'], $current_password)) {
                    $user_id            = Auth::User()->id;
                    $obj_user           = User::find($user_id);
                    $obj_user->password = Hash::make($request_data['new_password']);;
                    $obj_user->save();

                    return redirect()->route('profile', $objUser->id)->with('success', __('Password updated successfully'));
                } else {
                    return redirect()->route('profile', $objUser->id)->with('error', __('Please enter correct current password.'));
                }
            } else {
                return redirect()->route('profile', \Auth::user()->id)->with('error', __('Something is wrong.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function ajaxUserList(Request $request){

        if ($request->ajax()) {
            $usersQuery = User::query();

            if(!empty($request->get('name'))){
                $usersQuery->where('id', $request->get('name'));
            }

            $data = $usersQuery->select('*');

            return Datatables::of($data)
                    ->addIndexColumn()

                    ->addColumn('action', function($row){

                           $btn = '<a href="javascript:void(0)" class="edit-icon bg-info"><i class="fas fa-eye"></a>';

                            return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);

        }
    }

    public function UserPassword($id)
    {
        if(Auth::user()->isAbleTo('user reset password'))
        {
            try {
                $eId = \Crypt::decrypt($id);

                if(Auth::user()->hasRole('super admin'))
                {
                    $user = User::where('id', $eId)->where('type','company')->first();
                }
                else
                {
                    $user = User::where('id', $eId)->where('workspace_id',getActiveWorkSpace())->where('created_by', creatorId())->first();
                }
                if($user)
                {
                    return view('users.reset',compact('user'));
                }
                return response()->json(['error' => __('Something Went Wrong, User Not Found!')], 401);
            } catch (\Throwable $th) {
                return response()->json(['error' => $th->getMessage()], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

    }

    public function UserPasswordReset(Request $request, $id)
    {
        if(Auth::user()->isAbleTo('user reset password'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'password' => 'required|confirmed|same:password_confirmation|min:6',
                            ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            try {
                $eId = \Crypt::decrypt($id);

                if(Auth::user()->hasRole('super admin'))
                {
                    $user = User::where('id', $eId)->where('type','company')->first();
                }
                else
                {
                    $user = User::where('id', $eId)->where('workspace_id',getActiveWorkSpace())->where('created_by', creatorId())->first();
                }
                if($user)
                {
                    if(isset($request->login_enable))
                    {
                        $user->forceFill([
                            'password' => Hash::make($request->password),
                            'is_enable_login' => 1,
                        ])->save();
                    }
                    else
                    {
                        $user->forceFill([
                                'password' => Hash::make($request->password),
                            ])->save();
                    }

                    return redirect()->route('users.index')->with(
                        'success', __('The user password updated successfully')
                    );
                }
                return redirect()->back()->with('error', __('Something Went Wrong, User Not Found!'));
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', $th->getMessage());
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function LoginManage($id)
    {
        if(Auth::user()->isAbleTo('user reset password'))
        {
            $eId        = \Crypt::decrypt($id);
            $user = User::find($eId);
            if($user->is_enable_login == 1)
            {
                $user->is_enable_login = 0;
                $user->save();
                return redirect()->route('users.index')->with('success', __('User login disable successfully.'));
            }
            else
            {
                $user->is_enable_login = 1;
                $user->save();
                return redirect()->route('users.index')->with('success', __('User login enable successfully.'));
            }

        }
        else
        {
            return redirect()->route('users.index')->with('error', __('Permission denied.'));
        }
    }
    public function fileImportExport()
    {
        if(Auth::user()->isAbleTo('user import'))
        {
            return view('users.import');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

    }
    public function fileImport(Request $request)
    {
        if(Auth::user()->isAbleTo('user import'))
        {
            session_start();

            $error = '';

            $html = '';
            if($request->hasFile('file'))
            {
                $file_array = explode(".", $request->file->getClientOriginalName());

                $extension = end($file_array);

                if ($extension == 'csv')
                {
                    $file_data = fopen($request->file->getRealPath(), 'r');

                    $file_header = fgetcsv($file_data);
                    $html .= '<table class="table table-bordered"><tr>';

                    for ($count = 0; $count < count($file_header); $count++)
                    {
                        $html .= '
                                <th>
                                        <select name="set_column_data" class="form-control set_column_data" data-column_number="' . $count . '">
                                            <option value="">Set Count Data</option>
                                            <option value="name">Name</option>
                                            <option value="email">Email</option>
                                        </select>
                                </th>
                                ';
                    }
                    $html .= '
                                <th>
                                        <select name="set_column_data" class="form-control set_column_data role-name" data-column_number="' . $count+1 . '">
                                            <option value="role">Role</option>
                                        </select>
                                </th>
                                ';
                    $html .= '</tr>';
                    $limit = 0;
                    while (($row = fgetcsv($file_data)) !== false) {
                        $limit++;

                        $html .= '<tr>';

                        for ($count = 0; $count < count($row); $count++) {
                            $html .= '<td>' . $row[$count] . '</td>';
                        }
                        $html .= '<td>
                                    <select name="role" class="form-control role-name-value">;';
                                    $roles = Role::where('created_by',\Auth::user()->id)->pluck('name','id');
                                        foreach ($roles as $key => $role)
                                        {
                                            $html .=' <option value="'.$key.'">'.$role.'</option>';
                                        }
                                    $html .='  </select>
                                </td>';
                        $html .= '</tr>';

                        $temp_data[] = $row;

                    }
                    $_SESSION['file_data'] = $temp_data;
                }
                else
                {
                    $error = 'Only <b>.csv</b> file allowed';
                }
            }
            else
            {
                $error = __('Please Select File');
            }
            $output = array(
                'error' => $error,
                'output' => $html,
            );

            return json_encode($output);
        }
        else
        {
            $output = array(
                'error' => __('Permission denied.'),
                'output' => '',
            );

            return json_encode($output);
        }

    }

    public function fileImportModal()
    {
        if(Auth::user()->isAbleTo('user import'))
        {
            return view('users.import_modal');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function UserImportdata(Request $request)
    {
        if(Auth::user()->isAbleTo('user import'))
        {
            session_start();
            $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
            $flag = 0;
            $html .= '<table class="table table-bordered"><tr>';
            $file_data = $_SESSION['file_data'];

            unset($_SESSION['file_data']);

            $users_count = 0;
            $status =  admin_setting('email_verification');
            foreach ($file_data as $key=>$row) {

                if(Auth::user()->type == 'super admin')
                {
                    $validatorArray = [
                        'name' => 'required|max:120',
                        'email' => 'required|email|max:100|unique:users,email',
                    ];
                }
                else{
                    $validatorArray = [
                        'name' => 'required|max:120',
                        'role' => 'required|exists:roles,id',
                        'email' => ['required',
                                        Rule::unique('users')->where(function ($query) {
                                        return $query->where('created_by', creatorId())->where('workspace_id',getActiveWorkSpace());
                                    })
                        ],
                    ];
                }

                $validator = Validator::make(
                    $request->all(), $validatorArray
                );

                if ($validator->fails()) {
                    return response()->json([
                        'html'  => true,
                        'response' => $validator->errors()->first(),
                    ]);
                }

                if(Auth::user()->type != 'super admin'){
                    $canUse=  PlanCheck('User',Auth::user()->id);
                    if($canUse == false)
                    {
                        return response()->json([
                            'html' => false,
                            'response' =>'Total ' .   $users_count  . ' Number of users Imported , You have maxed out the total number of User allowed on your current plan',
                        ]);
                    }
                }
                $check_user = user::where('created_by', creatorId())->where('workspace_id',getActiveWorkSpace())->Where('email',$row[$request->email])->get();
                if($check_user->isEmpty())
                {
                    try {

                        $role_r = Role::find($request->role[$key]);
                        if(empty($role_r))
                        {
                            $role_r = Role::where('created_by',creatorId())->where('name','staff')->first();
                        }

                         $user_data = new user();

                        $user_data->name                = $row[$request->name];
                        $user_data->email               = $row[$request->email];
                        $user_data->password            = null;
                        $user_data->lang                = 'en';
                        $user_data->type                = !empty($role_r) ? $role_r->name : 'staff';
                        $user_data->is_enable_login     = 0;
                        $user_data->created_by          = creatorId();
                        $user_data->workspace_id        = getActiveWorkSpace();
                        $user_data->active_workspace    = getActiveWorkSpace();

                        if (empty($status) || $status != 'on')
                        {
                            $user_data->email_verified_at = date('Y-m-d h:i:s');
                        }
                        $user_data->save();
                        $user_data->addRole($role_r);
                        $users_count = $users_count + 1;

                        if(\Auth::user()->type == 'super admin'){
                            $plan = Plan::where('is_free_plan',1)->first();
                            if($plan)
                            {
                                $user_data->assignPlan($plan->id,'Month',$plan->modules,0,$user_data->id);
                            }
                        }
                    }
                    catch (\Exception $e)
                    {
                        $flag = 1;
                        $html .= '<tr>';
                            $html .= '<td>' . $row[$request->name] . '</td>';
                            $html .= '<td>' . $row[$request->email] . '</td>';
                        $html .= '</tr>';
                    }
                }
                else
                {
                    $flag = 1;
                    $html .= '<tr>';
                        $html .= '<td>' . $row[$request->name] . '</td>';
                        $html .= '<td>' . $row[$request->email] . '</td>';
                    $html .= '</tr>';
                }
            }

            $html .= '
                            </table>
                            <br />
                            ';
            if ($flag == 1)
            {
                return response()->json([
                    'html' => true,
                    'response' => $html,
                ]);
            }
            else
            {
                return response()->json([
                    'html' => false,
                    'response' => __('Data Imported Successfully'),
                ]);
            }
        }
        else
        {
            return response()->json([
                'html' => false,
                'response' => __('Permission denied.'),
            ]);
        }
    }
    public function UserLogHistory(Request $request)
    {
        if(Auth::user()->isAbleTo('user logs history'))
        {
            $filteruser = User::where('created_by', creatorId())->get()->pluck('name', 'id');
            $filteruser->prepend('Select User', '');

            if(Auth::user()->type == 'super admin')
            {
                $filteruser = User::where('type', 'company')->get()->pluck('name', 'id');

                $query = \DB::table('login_details')
                ->join('users', 'login_details.user_id', '=', 'users.id')
                ->select(\DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))
                ->where('login_details.type','company');
            }
            elseif(Auth::user()->type == 'company')
            {
                $query = \DB::table('login_details')
                ->join('users', 'login_details.user_id', '=', 'users.id')
                ->select(\DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))
                ->where(['login_details.created_by' => creatorId()]);
            }
            else
            {
                $query = \DB::table('login_details')
                ->join('users', 'login_details.user_id', '=', 'users.id')
                ->select(\DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))
                ->where(['login_details.user_id' => \Auth::user()->id]);
            }


            if(!empty($request->month))
            {
                $query->whereMonth('date', date('m',strtotime($request->month)));
                $query->whereYear('date', date('Y',strtotime($request->month)));
            }else{
                $query->whereMonth('date', date('m'));
                $query->whereYear('date', date('Y'));
            }

            if(!empty($request->users))
            {
                $query->where('user_id', '=', $request->users);
            }
            $userdetails = $query->get()->sortDesc();

            return view('users.userlog', compact( 'userdetails','filteruser'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function UserLogView($id)
    {
        $users_log = LoginDetail::find($id);

        return view('users.userlogview', compact('users_log'));
    }

    public function UserLogDestroy($id)
    {
        if(Auth::user()->isAbleTo('user delete'))
        {
            LoginDetail::where('id', $id)->delete();

            return redirect()->route('users.userlog.history')->with('success', __('The user logs has been deleted'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function LoginWithCompany(Request $request, User $user,  $id)
    {
        $user = User::find($id);
        if ($user && auth()->check()) {
            Impersonate::take($request->user(), $user);
            return redirect('/home');
        }
    }

    public function ExitCompany(Request $request)
    {
        \Auth::user()->leaveImpersonation($request->user());
        return redirect('/dashboard');
    }

    public function CompnayInfo($id)
    {
        if(!empty($id)){
            $data = $this->Counter($id);
            if($data['is_success']){
                $users_data = $data['response']['users_data'];
                $workspce_data = $data['response']['workspce_data'];
                return view('users.companyinfo', compact('id','users_data','workspce_data'));
            }
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function UserUnable(Request $request)
    {
        if(!empty($request->id) && !empty($request->company_id))
        {
            if($request->name == 'user')
            {
                User::where('id', $request->id)->update(['is_disable' => $request->is_disable]);
                $data = $this->Counter($request->company_id);

            }
            elseif($request->name == 'workspace')
            {
                $company = User::find($request->company_id);
                if($company->active_workspace != $request->id )
                {
                    WorkSpace::where('id',$request->id)->update(['is_disable' => $request->is_disable]);
                }
                else
                {
                    return response()->json(['error' => __('Active Workspace can not disable.')]);
                }

                if($request->is_disable == 0)
                {
                    User::where('workspace_id',$request->id)->where('type','!=','company')->update(['is_disable' => $request->is_disable]);
                }
                $data = $this->Counter($request->company_id);
            }
            if(isset($data['is_success']))
            {
                $users_data = $data['response']['users_data'];
                $workspce_data = $data['response']['workspce_data'];
                if($request->is_disable == 1){

                    return response()->json(['success' => __('Successfully Unable.'),'users_data' => $users_data, 'workspce_data' => $workspce_data]);
                }else
                {
                    return response()->json(['success' => __('Successfull Disable.'),'users_data' => $users_data, 'workspce_data' => $workspce_data]);
                }
            }
        }
        return response()->json('error');
    }

    public function Counter($id)
    {
        $response = [];
        if(!empty($id))
        {
            $workspces= WorkSpace::where('created_by', $id)
            ->selectRaw('COUNT(*) as total_workspace, SUM(CASE WHEN is_disable = 0 THEN 1 ELSE 0 END) as disable_workspace, SUM(CASE WHEN is_disable = 1 THEN 1 ELSE 0 END) as active_workspace')
            ->first();
            $workspaces = WorkSpace::where('created_by',$id)->get();
            $users_data = [];
            foreach($workspaces as $workspce)
            {
                $users = User::where('created_by',$id)->where('workspace_id',$workspce->id)->selectRaw('COUNT(*) as total_users, SUM(CASE WHEN is_disable = 0 THEN 1 ELSE 0 END) as disable_users, SUM(CASE WHEN is_disable = 1 THEN 1 ELSE 0 END) as active_users')->first();

                $users_data[$workspce->name] = [
                    'workspace_id' => $workspce->id,
                    'total_users' => !empty($users->total_users) ? $users->total_users : 0,
                    'disable_users' => !empty($users->disable_users) ? $users->disable_users : 0,
                    'active_users' => !empty($users->active_users) ? $users->active_users : 0,
                ];
            }
            $workspce_data =[
                'total_workspace' =>  $workspces->total_workspace,
                'disable_workspace' => $workspces->disable_workspace,
                'active_workspace' => $workspces->active_workspace,
            ];
            $response['users_data'] = $users_data;
            $response['workspce_data'] = $workspce_data;

            return [
                'is_success' => true,
                'response' => $response,
            ];
        }
        return [
            'is_success' => false,
            'error' => __('Plan is deleted.'),
        ];
    }

    public function verifeduser($id)
    {
        $user                    = User::find($id);
        $user->email_verified_at = date('Y-m-d h:i:s');
        $user->save();

        if(Auth::user()->type == 'super admin'){
            $msg =  __('The customer has been verifed successfully.');
        }
        else{
            $msg =  __('The user has been verifed successfully.');
        }

        return redirect()->back()->with('success', $msg);
    }
}
