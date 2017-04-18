<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\BreederProfileRequest;
use App\Http\Requests\BreederPersonalProfileRequest;
use App\Http\Requests\BreederFarmProfileRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Breeder;
use App\Models\Customer;
use App\Models\FarmAddress;
use App\Models\Product;
use App\Models\Image;
use App\Models\Video;
use App\Models\Breed;
use App\Models\Admin;
use App\Models\User;
use App\Models\Sessions;
use App\Models\HomeImage;
use App\Models\AdministratorLog;
use App\Repositories\AdminRepository;
use App\Mail\SwineCartAccountNotification;
use App\Mail\SwineCartBreederCredentials;

use DB;
use Auth;
use Input;
use Storage;
use File;
use Carbon\Carbon;

class AdminController extends Controller
{
    protected $user;

    /**
    * Create new AdminController instance
    */
    public function __construct()
    {
      $this->middleware('role:admin');
      $this->middleware(function($request, $next){
          $this->user = Auth::user();

          return $next($request);
      });
    }

    /**
     * Helper functions for retrieving all user data
     *
     * @param  none
     * @return users
     */
    public function retrieveAllUsers(){
        $users = DB::table('users')->join('role_user', 'users.id', '=' , 'role_user.user_id')->join('roles', 'role_user.role_id','=','roles.id')->get();
        return $users;
    }

    /**
     * Helper functions for retrieving all user data
     *
     * @param  none
     * @return count of all approved user
     */
    public function userCount(){
    $count = DB::table('users')
                    ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                    ->join('roles', 'role_user.role_id','=','roles.id')
                    ->where('role_user.role_id','!=',1)
                    ->where('users.email_verified','=', 1)
                    ->where('users.deleted_at','=', NULL)
                    //->where('users.is_blocked','=', 0)
                    ->count();
    return $count;
    }

    /**
     * Helper functions for retrieving all user data
     *
     * @param  none
     * @return count of all approved breeders
     */
    public function breederCount(){
        $count = DB::table('users')
                        ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                        ->join('roles', 'role_user.role_id','=','roles.id')
                        ->where('role_user.role_id','=',2)
                        ->where('users.email_verified','=', 1)
                        ->where('users.blocked_at','=', NULL)
                        ->where('users.deleted_at','=', NULL)
                        ->count();
        return $count;
    }

    /**
     * Helper functions for retrieving all user data
     *
     * @param  none
     * @return count of all customers
     */
    public function customerCount(){
        $count = DB::table('users')
                        ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                        ->join('roles', 'role_user.role_id','=','roles.id')
                        ->where('role_user.role_id','=',3)
                        ->where('users.email_verified','=', 1)
                        ->where('users.blocked_at','=', NULL)
                        ->where('users.deleted_at','=', NULL)
                        ->count();
        return $count;
    }

    /**
     * Helper functions for retrieving the number of pending breeders
     *
     * @param  none
     * @return count of all users that are pending
     */
    public function pendingUserCount(){
        $count = DB::table('users')
                        ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                        ->join('roles', 'role_user.role_id','=','roles.id')
                        ->where('role_user.role_id','=',2)
                        ->where('users.approved_at','=', NULL)
                        ->where('users.deleted_at','=', NULL)
                        ->count();
        return $count;
    }

    /**
     * Helper functions for retrieving the number of blocked users
     *
     * @param  none
     * @return count of all users that are blocked
     */
    public function blockedUserCount(){
        $count = DB::table('users')
                        ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                        ->join('roles', 'role_user.role_id','=','roles.id')
                        ->where('role_user.role_id','!=',1)
                        ->where('users.email_verified','=', 1)
                        ->whereNotNull('users.blocked_at')
                        ->where('users.deleted_at','=', NULL)
                        ->count();
        return $count;
    }


    /**
     * Show Home Page of breeder
     *
     * @param  Request $request
     * @return View
     */
    public function index(Request $request)
    {
        $all = $this->userCount();
        $breeders = $this->breederCount();
        $customers = $this->customerCount();
        $pending = $this->pendingUserCount();
        $blocked = $this->blockedUserCount();
        $messages = DB::table('messages')->where('admin_id','=', $this->user->id)->whereNull('read_at')->count();
        $summary = array($all, $blocked, $pending, $messages);

        // $customers = DB::table('swine_cart_items')->join('transaction_logs', 'transaction_logs.product_id','=','swine_cart_items.product_id')
        //             ->whereMonth('date_needed', Carbon::now()->month)
        //             ->whereYear('date_needed',  Carbon::now()->year)
        //             ->whereDay('date_needed', Carbon::now()->day-1)
        //             ->get();
        //
        // dd($customers);
        return view(('user.admin.homeDashboard'), compact('summary'));
    }

    /**
     * Function to get all users in the database that are not deleted and blocked
     *
     * @param  none
     * @return array of user
     */
    public function displayAllUsers(){
        $users = DB::table('users')->join('role_user', 'users.id', '=' , 'role_user.user_id')->join('roles', 'role_user.role_id','=','roles.id')
                ->whereNotNull('approved_at')
                ->whereNull('deleted_at')
                ->paginate(10);

        return view('user.admin._displayUsers',compact('users'));
    }

    /**
     * Function to get all approved breeders
     *
     * @param  none
     * @return array of breeders
     */
    public function displayApprovedBreeders(){
        $users = $this->retrieveAllUsers();
        $userArray = [];
        foreach ($users as $user) {
            if($user->role_id==2 && $user->email_verified==1 && $user->deleted_at==NULL){
              $user->title = ucfirst($user->title);
              $user->token = csrf_token();
              $userArray[] = $user;
            }
        }
        return $userArray;
    }

    /**
     * Function to get all approved customers (not used in the website yet)
     *
     * @param  none
     * @return array of customers
     */
    public function displayApprovedCustomer(){
        $users = $this->retrieveAllUsers();
        $userArray = [];
        foreach ($users as $user) {
            if($user->role_id==3 && $user->email_verified==1 && $user->deleted_at==NULL){
              $user->title = ucfirst($user->title);
              $user->token = csrf_token();
              $userArray[] = $user;
            }
        }
        return $userArray;
    }

    /**
     * Function to get all pending users
     *
     * @param  none
     * @return array of pending users
     */
    public function displayPendingUsers(){
        $users = DB::table('users')->join('role_user', 'users.id', '=' , 'role_user.user_id')->join('roles', 'role_user.role_id','=','roles.id')
                    ->where('role_id','=',2)
                    ->whereNull('approved_at')
                    ->whereNull('deleted_at')
                    ->paginate(10);
        return view('user.admin._pendingUsers',compact('users'));
    }

    /**
     * Function to delete a user (triggers soft delete)
     *
     * @param  none
     * @return OK (string) status
     */
    public function deleteUser(Request $request){
        $adminID = Auth::user()->id;
        $adminName = Auth::user()->name;
        $user = User::find($request->id);           // find the user
        $user->delete();                                // delete it in the database
        // create a AdministratorLog entry for the action done
        AdministratorLog::create([
            'admin_id' => $adminID,
            'admin_name' => $adminName,
            'user' => $user->name,
            'category' => 'Delete',
            'action' => 'Deleted '.$user->name,
        ]);
        $time = Carbon::now()->addMinutes(10);
        $notificationType = 2;
        // Send an email to the user after 10 minutes
        Mail::to($user->email)
            ->later($time, new SwineCartAccountNotification($user, $notificationType));

        return Redirect::back()->with('message','User Deleted');
    }

    /**
     * Function to get the blocked user list
     *
     * @param  none
     * @return array of blocked users
     */
    public function displayBlockedUsers(){
        // get all the user with the blocked status
        $users = DB::table('users')
                      ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                      ->join('roles', 'role_user.role_id','=','roles.id')
                      ->where('role_user.role_id','!=',1)
                      ->where('users.email_verified','=', 1)
                      ->where('users.deleted_at','=', NULL )
                      ->whereNotNull('users.blocked_at')
                      ->paginate(10);
        // foreach ($blockedUsers as $blockedUser) {
        //     $blockedUser->title = ucfirst($blockedUser->title);     // fix the format for the role in each of the users queried
        //     $blockedUser->token = csrf_token();                     // get the token
        // }
        return view('user.admin._blockedUsers',compact('users'));
    }

    /**
     * Function to get the searched approved user
     *
     * @param  request array from search form containing string for name and values for checkboxes
     * @return array of approved users that match the search criterion
     */
    public function searchUser(Request $request){
        //$values = [$request->admin, $request->breeder, $request->customer];
        if($request->breeder==null && $request->customer==null){
            $users = DB::table('users')
                          ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                          ->join('roles', 'role_user.role_id','=','roles.id')
                          ->where('users.name','LIKE', "%$request->search%")
                          ->where('users.email_verified','=', 1)
                          ->where('users.deleted_at','=', NULL )
                          ->paginate(10);
        }else{
            $values = [$request->breeder, $request->customer];
            $search = [];
            for($i = 0; $i < 2; $i++){
                if($values[$i] != null){
                    $search[] = $values[$i];
                }
            }
            $users = DB::table('users')
                          ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                          ->join('roles', 'role_user.role_id','=','roles.id')
                          ->where('users.name','LIKE', "%$request->search%")
                          ->where('users.email_verified','=', 1)
                          ->where('users.deleted_at','=', NULL )
                          ->whereIn('role_user.role_id', $search)
                          ->paginate(10);
        }


        return view('user.admin._displayUsers',compact('users'));
    }

    /**
     * Function to get the searched pending users
     *
     * @param  request array from search form containing string for name and values for checkboxes
     * @return array of pending users that match the search criterion
     */
    public function searchPendingUser(Request $request){
        $users = DB::table('users')
                      ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                      ->join('roles', 'role_user.role_id','=','roles.id')
                      ->where('role_id','=', 2)
                      ->where('users.name','LIKE', "%$request->search%")
                    //   ->where('users.email_verified','=', 0)
                      ->where('users.deleted_at','=', NULL )
                      ->whereNull('approved_at')
                      ->paginate(10);

        return view('user.admin._pendingUsers',compact('users'));
    }

    /**
     * Function to search blocked user
     *
     * @param  request string
     * @return collection of blocked user
     */
    public function searchBlockedUsers(Request $request){
        if($request->breeder==null && $request->customer==null){
            $users = DB::table('users')
                          ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                          ->join('roles', 'role_user.role_id','=','roles.id')
                          ->where('users.name','LIKE', "%$request->search%")
                          ->where('users.email_verified','=', 1)
                          ->where('users.deleted_at','=', NULL )
                          ->whereNotNull('users.blocked_at')
                          ->paginate(10);
        }else{
            $values = [$request->breeder, $request->customer];
            $search = [];
            for($i = 0; $i < 2; $i++){
                if($values[$i] != null){
                    $search[] = $values[$i];
                }
            }
            $users = DB::table('users')
                          ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                          ->join('roles', 'role_user.role_id','=','roles.id')
                          ->where('users.name','LIKE', "%$request->search%")
                          ->where('users.email_verified','=', 1)
                          ->where('users.deleted_at','=', NULL )
                          ->whereNotNull('users.blocked_at')
                          ->whereIn('role_user.role_id', $search)
                          ->paginate(10);
        }


        return view('user.admin._blockedUsers',compact('users'));
    }



    /**
     * Function to add the user to the blocked list, changes blocked status to 1
     *
     * @param  none
     * @return status string "OK"
     */
    public function blockUser(Request $request){
        $adminID = Auth::user()->id;
        $adminName = Auth::user()->name;
        $user = User::find($request->id);       // find the user using the user id
        $notificationType = NULL;
        if($user->blocked_at != NULL){
            $user->blocked_at = NULL;
            $notificationType = 1;
        }else{
            $user->blocked_at = Carbon::now();     // change the status for is_blocked column
            $notificationType = 0;
        }
        $user->save();                              // save the change to the database
        // create a log entry for the action done
        if($user->blocked_at!=NULL){
            AdministratorLog::create([
                'admin_id' => $adminID,
                'admin_name' => $adminName,
                'user' => $user->name,
                'category' => 'Block',
                'action' => 'Blocked '.$user->name,
            ]);
        }else{
            AdministratorLog::create([
                'admin_id' => $adminID,
                'admin_name' => $adminName,
                'user' => $user->name,
                'category' => 'Unblock',
                'action' => 'Unblocked '.$user->name,
            ]);
        }

        // send an email notification to the user's email
        $time = Carbon::now()->addMinutes(10);
        Mail::to($user->email)
            ->later($time, new SwineCartAccountNotification($user, $notificationType));

        return Redirect::back()->with('message','Action Success');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
        ]);
    }

    /**
     * Create a new user instance in the database.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data, $verCode, $password)
    {
     return User::create([
         'name' => $data['name'],
         'email' => $data['email'],
         'password' => bcrypt($password),
         'verification_code' => $verCode
     ]);
    }

    /**
     * Create an initial account for the breeder
     *
     * @param  \Illuminate\Http\Request  $request
     * @return View
     */
    public function createUser(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            $this->throwValidationException(
              $request, $validator
            );
        }
        $verCode = str_random('10');        // create a verification code
        $password = $this->generatePassword();  // generate the initial password for the breeder and save it to a variable to get the original password before encryption
        $user = $this->create($request->all(), $verCode, $password); // create a user instance
        $user->assignRole('breeder');       // assign a breeder role to it
        $breeder = Breeder::create([])->users()->save($user);   // create a breeder instance for that user

        // data to be passed in the email
        $data = [
            'email' => $request->input('email'),
            'password' => $password
        ];

        $adminID = Auth::user()->id;
        $adminName = Auth::user()->name;
        // create a log entry for the action done
        AdministratorLog::create([
            'admin_id' => $adminID,
            'admin_name' => $adminName,
            'user' => $data['email'],
            'category' => 'Create',
            'action' => 'Created user account for '.$data['email'],
        ]);

        $time = Carbon::now()->addMinutes(10);
        Mail::to($user->email)
            ->later($time, new SwineCartBreederCredentials($user->email, $password));

        return Redirect::back()->withMessage('User Created!'); // redirect to the page and display a toast notification that a user is created
    }

    /**
     * Generates a random password with a length of 10 characters
     * @return String
     */
    public function generatePassword(){
        $password = str_random(10);;
        return $password;
    }

    /**
     * Accept a pending user request and send an email verification to the user's email
     *
     * @return none (redirect)
     */
    public function acceptUser(Request $request){
        $adminID = Auth::user()->id;
        $adminName = Auth::user()->name;
        $user = User::find($request->id);
        $user->approved_at = Carbon::now();     // negate the status to approve user
        $notificationType = 3;
        // create a log entry for the action done
        AdministratorLog::create([
            'admin_id' => $adminID,
            'admin_name' => $adminName,
            'user' => $user->name,
            'category' => 'Accept',
            'action' => 'Approved '.$user->name,
        ]);

        $time = Carbon::now()->addMinutes(10);
        // send an email notification to the user's email
        Mail::to($user->email)
            ->later($time, new SwineCartAccountNotification($user, $notificationType));
        $user->save();  // save changes to the database

        return Redirect::back()->withMessage('User Accepted!'); // redirect to the page and display a toast notification that a user is created
    }

    /**
    *
    * Reject a pending user request and send an email verification to the user's email
    *
    * @return none (redirect)
    */
    public function rejectUser(Request $request){
        $adminID = Auth::user()->id;
        $adminName = Auth::user()->name;
        $user = User::find($request->id);
        $user->approved_at = NULL;     // negate the status to approve user
        $user->deleted_at = Carbon::now();
        $notificationType = 4;
        // create a log entry for the action done
        AdministratorLog::create([
            'admin_id' => $adminID,
            'admin_name' => $adminName,
            'user' => $user->name,
            'category' => 'Reject',
            'action' => 'Reject '.$user->name,
        ]);
        $time = Carbon::now()->addMinutes(10);
        // send an email notification to the user's email
        Mail::to($user->email)
            ->later($time, new SwineCartAccountNotification($user, $notificationType));

        $user->save();  // save changes to the database

        return Redirect::back()->withMessage('User Rejected!'); // redirect to the page and display a toast notification that a user is created
    }

    /**
     * Displays form for the registration of breeder
     * @return View
     *
     * @TODO Registration form file checker
     */
    public function getRegistrationForm(){
        return view('user.admin.form');
    }

    /**
     * @todo Submit the registration form for breeder accreditation
     * @return View
     *
     * @TODO Checker for submitted form
     */
    public function submitRegistrationForms(Request $request){
        dd($request);
    }

    /**
     * Displays the administrator logs
     *
     * @param none
     * @return View the administrator logs
     *
     */
    public function getAdministratorLogs(){
        $logs = DB::table('administrator_logs')->paginate(10);
        return view('user.admin._adminLogs',compact('logs'));
    }

    /**
     * Function to search administrator logs
     *
     * @param  search string
     * @return collection of logs from query
     */
    public function searchAdministratorLogs(Request $request){

        if($request->option!=null){
            $logs = DB::table('administrator_logs')
                    ->whereIn('category', $request->option)
                    ->where(function ($query) use ($request) {
                        $query->where('user', 'LIKE', "%$request->search%")
                              ->orWhere('admin_name', 'LIKE', "%$request->search%");
                    })
                    ->paginate(10);

        }else{
            $logs = DB::table('administrator_logs')
                    ->where('user', 'LIKE', "%$request->search%")
                    ->orWhere('admin_name', 'LIKE', "%$request->search%")
                    ->paginate(10);
        }

        return view('user.admin._adminLogs',compact('logs'));
    }

    /**
     * Check if media is Image depending on extension
     *
     * @param  String   $extension
     * @return Boolean
     */
    private function isImage($extension)
    {
        return ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png') ? true : false;
    }

    /**
     * Displays the manage pages view
     *
     * @param none
     * @return View and All content homepage images and texts
     *
     */
    public function manageHomePage(){
        $homeContent = DB::table('home_images')->get();
        return view('user.admin._manageImages',compact('homeContent'));
    }

     /**
      * Add an image and/or text to he home page slider
      *
      * @param form request
      * @return String
      * @todo Error detection
      */
     public function addHomeImage(Request $request){
         if (Input::hasFile('image')) {
            $filename = date('d-m-y-H-i-s',time()).'-'.Input::file('image')->getClientOriginalName();
            Input::file('image')->move(public_path('/images/homeimages/'), $filename);
            $content = new HomeImage;
            $content->text = $request->textContent;
            $content->title = $request->title;
            $content->name = $filename;
            $content->path = '/images/homeimages/';
            $content->save();
            return Redirect::back()->with('message','Image Added');
        } else {
            return Redirect::back()->with('message','Operation Failed');
        }
     }

     /**
      * Delete image and text in the home page
      *
      * @param form request
      * @return String
      * @todo Error detection
      */
     public function deleteContent(Request $request){
         $content = HomeImage::find($request->content_id);
         //File::delete($content->path.$content->name);
         unlink(public_path($content->path.$content->name));
         //dd($content->path.$content->name);
         $content->delete();
         return Redirect::back()->with('message','Image Deleted');
     }

     /**
      * Edit an image and/or text in the home page
      *
      * @param form request
      * @return String
      * @todo Error detection
      */
     public function editContent(Request $request){
         $content = HomeImage::find($request->content_id);
         if (Input::hasFile('image')) {
            unlink(public_path($content->path.$content->name));
            $filename = date('d-m-y-H-i-s',time()).'-'.Input::file('image')->getClientOriginalName();
            Input::file('image')->move(public_path('/images/homeimages/'), $filename);
            $content->name = $filename;
            $content->path = '/images/homeimages/';
        }

        if(!empty($request->title)){
            $content->title = $request->title;
        }

        if(!empty($request->textContent)){
            $content->text = $request->textContent;
        }
        $content->save();
        return Redirect::back()->with('message','Content Edited');
     }

     public function goToUserlist(){
         return redirect()->route('home/userlist');
     }

     public function goToPending(){
         return redirect()->route('home/pending/users');
     }

     /*
      * Helper function to get the monthly count in the count collection
      *
      * @param collection count
      * @return array count
      *
      */
     public function getMonthlyCount($counts){
         $monthlyCount = array_fill(0, 12, 0);
         foreach($counts as $count){
             if($count->month == 1){
                 $monthlyCount[0] = $count->user_count;
             }
             if($count->month == 2){
                 $monthlyCount[1] = $count->user_count;
             }
             if($count->month == 3){
                 $monthlyCount[2] = $count->user_count;
             }
             if($count->month == 4){
                 $monthlyCount[3] = $count->user_count;
             }
             if($count->month == 5){
                 $monthlyCount[4] = $count->user_count;
             }
             if($count->month == 6){
                 $monthlyCount[5] = $count->user_count;
             }
             if($count->month == 7){
                 $monthlyCount[6] = $count->user_count;
             }
             if($count->month == 8){
                 $monthlyCount[7] = $count->user_count;
             }
             if($count->month == 9){
                 $monthlyCount[8] = $count->user_count;
             }
             if($count->month == 10){
                 $monthlyCount[9] = $count->user_count;
             }
             if($count->month == 11){
                 $monthlyCount[10] = $count->user_count;
             }
             if($count->month == 12){
                 $monthlyCount[11] = $count->user_count;
             }
         }
         return $monthlyCount;
     }


     /*
      * Helper function that gets the count of active users and returns the array of count
      *
      * @param Integer user type or role, Carbon->year
      * @return count array
      */
     public function getActiveUserStatistics($role, $year){
         $counts = DB::table('users')
                 ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                 ->join('roles', 'role_user.role_id','=','roles.id')
                 ->where('role_user.role_id','=', $role)
                //  ->where('users.email_verified','=', 1)
                 ->whereNull('blocked_at')
                 ->whereNull('deleted_at')
                 ->select(DB::raw('YEAR(approved_at) year, MONTH(approved_at) month, MONTHNAME(approved_at) month_name, COUNT(*) user_count'))
                 ->groupBy('year')
                 ->groupBy('month')
                 ->whereYear('approved_at', $year)
                 ->get();

        return $counts;
     }

     /*
      * Helper function that gets the count of deleted users and returns the array of count
      *
      * @param Integer user type or role, Carbon->year
      * @return count array
      */
     public function getDeletedUserStatistics($role, $year){
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_user.role_id','=', $role)
                // ->where('users.email_verified','=', 1)
                ->whereNotNull('approved_at')
                ->select(DB::raw('YEAR(deleted_at) year, MONTH(deleted_at) month, MONTHNAME(deleted_at) month_name, COUNT(*) user_count'))
                ->groupBy('year')
                ->groupBy('month')
                ->whereYear('deleted_at', $year)
                ->get();

        return $counts;
     }

     /*
      * Helper function that gets the count of blocked users and returns the array of count
      *
      * @param Integer user type or role, Carbon->year
      * @return count array
      */
     public function getBlockedUserStatistics($role, $year){
         $counts = DB::table('users')
                 ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                 ->join('roles', 'role_user.role_id','=','roles.id')
                 ->where('role_user.role_id','=',$role)
                //  ->where('users.email_verified','=', 1)
                 ->whereNotNull('approved_at')
                 ->select(DB::raw('YEAR(blocked_at) year, MONTH(blocked_at) month, MONTHNAME(blocked_at) month_name, COUNT(*) user_count'))
                 ->groupBy('year')
                 ->groupBy('month')
                 ->whereYear('blocked_at', $year)
                 ->get();

        return $counts;
     }

     /*
      * Shows active breeders per month from current year
      *
      * @param none
      * @return array of counts
      */
     public function showStatisticsActiveBreeder(){
         $date = Carbon::now();
         $year = $date->year;
         $counts = $this->getActiveUserStatistics(2, $year);
         $month = $this->getMonthlyCount($counts);

        return view('user.admin.statisticsBreederActive', compact('month', 'year'));
     }

     /*
      * Shows the active breeders per month from certain year
      *
      * @param year
      * @return array of counts
      */
     public function showStatisticsActiveBreederYear(Request $request){
         $year = $request->year;
         $counts = $counts = $this->getActiveUserStatistics(2, $year);
         $month = $this->getMonthlyCount($counts);

         return view('user.admin.statisticsBreederActive', compact('month', 'year'));
     }

     /**
      * Shows the deleted breeders statistics per month
      *
      * @param none
      * @return array of counts
      *
      */
     public function showStatisticsDeletedBreeder(){
         $date = Carbon::now();
         $year = $date->year;
         $counts = $this->getDeletedUserStatistics(2, $year);
         $month = $this->getMonthlyCount($counts);

        return view('user.admin.statisticsBreederDeleted', compact('month', 'year'));
     }

     /*
      * Shows the deleted breeders statistics per month from certain year
      *
      * @param year
      * @return array of counts
      * @todo optimize the method of getting the count of created users per month, change query to filter the year
      */
     public function showStatisticsDeletedBreederYear(Request $request){
         $year = $request->year;
         $counts = $this->getDeletedUserStatistics(2, $year);
         $month = $this->getMonthlyCount($counts);

         return view('user.admin.statisticsBreederDeleted', compact('month', 'year'));
     }


     /**
      * Shows the blocked breeders statistics per month from current year
      *
      * @param none
      * @return array of counts
      * @todo optimize the method of getting the count of blocked users per month, change query to filter the year
      */
      public function showStatisticsBlockedBreeder(){
          $date = Carbon::now();
          $year = $date->year;
          $counts = $this->getBlockedUserStatistics(2, $year);
          $month = $this->getMonthlyCount($counts);

          return view('user.admin.statisticsBreederBlocked', compact('month', 'year'));
      }

      /*
       * Shows the blocked breeders statistics per month from input year
       *
       * @param input year
       * @return array of counts
       * @todo optimize the method of getting the count of blocked users per month, change query to filter the year
       */
      public function showStatisticsBlockedBreederYear(Request $request){
          $year = $request->year;
          $counts = $this->getBlockedUserStatistics(2, $year);
          $month = $this->getMonthlyCount($counts);

          return view('user.admin.statisticsBreederBlocked', compact('month', 'year'));
      }

      /*
       * Shows the Statistics dashboard
       *
       * @param none
       * @return summary of statistics, month labels, monthly counts
       *
       */
      public function showStatisticsDashboard(){
        $date = Carbon::now();
        $year = $date->year;
        $month = $date->month;
        $yesterday = new Carbon('yesterday');
        $monthStart = Carbon::now()->startOfMonth();

        $stats = [];
        $monthNames = [
                Carbon::now()->startOfMonth()->subMonth(4)->format('F'),
                Carbon::now()->startOfMonth()->subMonth(3)->format('F'),
                Carbon::now()->startOfMonth()->subMonth(2)->format('F'),
                Carbon::now()->startOfMonth()->subMonth()->format('F'),
                Carbon::now()->startOfMonth()->format('F')];
        $monthlyCount = array_fill(0, 5, 0);

        $activeLastFiveMonths = DB::table('users')
                                ->whereNull('deleted_at')
                                ->whereNull('blocked_at')
                                ->whereNotNull('approved_at')
                                ->whereBetween('created_at', [Carbon::now()->startOfMonth()->subMonth(4), Carbon::now()->endOfMonth()])
                                ->select(DB::raw('YEAR(created_at) AS year, MONTH(created_at) AS month, MONTHNAME(created_at) AS month_name ,COUNT(*) AS count'))
                                ->groupBy('year')
                                ->groupBy('month')
                                ->orderBy('year', 'asc')
                                ->get();

        foreach ($activeLastFiveMonths as $months) {
            if($months->month_name == $monthNames[0]){
                $monthlyCount[0] = $months->count;
            }
            else if($months->month_name == $monthNames[1]){
                $monthlyCount[1] = $months->count;
            }
            else if($months->month_name == $monthNames[2]){
                $monthlyCount[2] = $months->count;
            }
            else if($months->month_name == $monthNames[3]){
                $monthlyCount[3] = $months->count;
            }else{
                $monthlyCount[4] = $months->count;
            }
        }

        $deleted = DB::table('users')
                    ->whereNotNull('approved_at')
                    ->whereMonth('deleted_at', '=', $month)
                    ->whereYear('deleted_at', '=', $year)
                    ->count();

        $blocked = DB::table('users')
                    ->whereNotNull('approved_at')
                    ->whereMonth('blocked_at', '=', $month)
                    ->whereYear('blocked_at', '=', $year)
                    ->count();
        $new = DB::table('users')
                    ->whereNotNull('approved_at')
                    ->whereMonth('created_at', '=', $month)
                    ->whereYear('created_at', '=', $year)
                    ->count();
        $adminLogs = DB::table('administrator_logs')
                    ->whereMonth('created_at', '=', $month)
                    ->whereYear('created_at', '=', $year)
                    ->whereDay('created_at', '=', $yesterday)
                    ->orderBy('created_at', 'ASC')
                    ->get();

        foreach ($adminLogs as $logs) {
            $logs->created_at = Carbon::parse($logs->created_at)->toDayDateTimeString();
        }

        $products = DB::table('products')
                        ->where('status', '=', 'displayed')
                        ->whereNull('deleted_at')
                        ->select('type',DB::raw('COUNT(*) as count'))
                        ->groupBy('type')
                        ->get();

        $total = 0;
        $boar = 0;
        $gilt = 0;
        $sow = 0;
        $semen = 0;
        foreach ($products as $type) {
            $total = $total + $type->count;
            if($type->type == 'boar'){
                $boar = $type->count;
            }
            else if($type->type == 'boar'){
                $boar = $type->count;
            }
            else if($type->type == 'sow'){
                $sow = $type->count;
            }
            else if($type->type == 'gilt'){
                $gilt = $type->count;
            }
            else if($type->type == 'semen'){
                $semen = $type->count;
            }
        }

        $transactions =DB::table('transaction_logs')
                        ->leftJoin('product_reservations', 'product_reservations.product_id', '=','transaction_logs.product_id')
                        ->select(DB::raw('MAX(transaction_logs.created_at) latest_update, product_reservations.*, transaction_logs.*'))
                        ->groupBy('transaction_logs.product_id')
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', $year)
                        ->get();


        $requested = 0;
        $reserved = 0;
        $paid = 0;
        $onDelivery = 0;
        $sold = 0;
        foreach($transactions as $transaction) {
            if($transaction->order_status == 'requested'){
                $requested++;
            }else if($transaction->order_status == 'reserved'){
                $reserved++;
            }else if($transaction->order_status == 'paid'){
                $paid++;
            }else if($transaction->order_status == 'on_delivery'){
                $onDelivery++;
            }else if($transaction->order_status == 'sold'){
                $sold++;
            }
        }

        $stats = [$deleted, $blocked, $new, $adminLogs, $total, $boar, $gilt, $sow, $semen, count($transactions), $requested, $reserved, $paid,  $onDelivery, $sold];
        return view('user.admin.statisticsDashboard',compact('stats','monthNames', 'monthlyCount'));
      }

      /*
       * Shows active customers per month from current year
       *
       * @param none
       * @return array of count
       *
       */
      public function showStatisticsActiveCustomer(){
        $date = Carbon::now();
        $year = $date->year;
        $counts = $counts = $this->getActiveUserStatistics(3, $year);
        $month = $this->getMonthlyCount($counts);

       return view('user.admin.statisticsCustomerActive', compact('month', 'year'));
    }

    /*
     * Shows active customers per month from current input
     *
     * @param input year
     * @return array of count
     *
     */
    public function showStatisticsActiveCustomerYear(Request $request){
        $year = $request->year;
        $counts = $counts = $this->getActiveUserStatistics(3, $year);
        $month = $this->getMonthlyCount($counts);

        return view('user.admin.statisticsCustomerActive', compact('month', 'year'));
    }

    /**
     * Shows the deleted customers per month of the current year
     *
     * @param none
     * @return array of counts
     * @todo optimize the method of getting the count of deleted users per month, change query to filter the year
     */
    public function showStatisticsDeletedCustomer(){
        $date = Carbon::now();
        $year = $date->year;
        $counts = $this->getDeletedUserStatistics(3, $year);
        $month = $this->getMonthlyCount($counts);

       return view('user.admin.statisticsCustomerDeleted', compact('month', 'year'));
    }

    /*
     * Shows count of deleted customers per month from input year
     *
     * @param input year
     * @return array of count
     *
     */
    public function showStatisticsDeletedCustomerYear(Request $request){
        $year = $request->year;
        $counts = $this->getDeletedUserStatistics(3, $year);
        $month = $this->getMonthlyCount($counts);

        return view('user.admin.statisticsCustomerDeleted', compact('month', 'year'));
    }


    /**
     * Shows the count of blocked customers per month from the current year
     *
     * @param none
     * @return array of counts
     * @todo optimize the method of getting the count of blocked users per month, change query to filter the year
     */
     public function showStatisticsBlockedCustomer(){
         $date = Carbon::now();
         $year = $date->year;
         $counts = $this->getBlockedUserStatistics(3, $year);
         $month = $this->getMonthlyCount($counts);

         return view('user.admin.statisticsCustomerBlocked', compact('month', 'year'));
     }

     /*
      * Shows the count of blocked customers per month from the input year
      *
      * @param input year
      * @return array of counts
      * @todo optimize the method of getting the count of blocked users per month, change query to filter the year
      */
     public function showStatisticsBlockedCustomerYear(Request $request){
         $year = $request->year;
         $counts = $this->getBlockedUserStatistics(3, $year);
         $month = $this->getMonthlyCount($counts);

         return view('user.admin.statisticsCustomerBlocked', compact('month', 'year'));
     }

     /*
      * Get all entries in the administrator logs in the current day
      *
      * @param none
      * @return collection of logs
      *
      */
     public function showStatisticsTimeline(){
         $date = Carbon::now();
         $year = $date->year;
         $month = $date->month;
         $day = $date->day;
         $adminLogs = DB::table('administrator_logs')
                     ->whereDay('created_at', '=', $day)
                     ->whereMonth('created_at', '=', $month)
                     ->whereYear('created_at', '=', $year)
                     ->get();

         foreach ($adminLogs as $logs) {
             $logs->created_at = Carbon::parse($logs->created_at)->toTimeString();
         }

         $dateNow = $date->toFormattedDateString();
         return view('user.admin.statisticsTimeline', compact('dateNow','adminLogs'));
     }

     /*
      * Get all entries in the administrator logs in the input day
      *
      * @param date
      * @return collection of logs
      *
      */
     public function showStatisticsTimelineDate(Request $request){
         $date = new Carbon($request->date);
         // dd($date);
         $year = $date->year;
         $month = $date->month;
         $day = $date->day;
         $adminLogs = DB::table('administrator_logs')
                     ->whereDay('created_at', '=', $day)
                     ->whereMonth('created_at', '=', $month)
                     ->whereYear('created_at', '=', $year)
                     ->get();

         foreach ($adminLogs as $logs) {
             $logs->created_at = Carbon::parse($logs->created_at)->toTimeString();
         }
         $dateNow = $date->toFormattedDateString();
         return view('user.admin.statisticsTimeline', compact('dateNow','adminLogs'));
     }

     /*
      * Get user information and return the data to ajax function
      *
      * @param $request (user_id, role_id, userable_id)
      * @return collection user data
      *
      */
     public function fetchUserInformation(Request $request){
         if($request->userRole == 2){
             $details = DB::table('users')
                     ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                     ->join('roles', 'role_user.role_id','=','roles.id')
                     ->where('role_id', '=', 2)
                     ->where('users.id', '=', $request->userId)
                     ->join('breeder_user', 'breeder_user.id', '=', 'users.userable_id')
                     ->select('users.id as user_id', 'users.name as user_name', 'users.email', 'roles.title as role',
                             'breeder_user.officeAddress_addressLine1 as addressLine1', 'breeder_user.officeAddress_addressLine2 as addressLine2',
                             'breeder_user.officeAddress_province as province', 'breeder_user.officeAddress_zipCode as zipcode', 'breeder_user.office_landline',
                             'breeder_user.office_mobile', 'breeder_user.website', 'breeder_user.produce', 'breeder_user.contactPerson_name as contact_person',
                             'breeder_user.contactPerson_mobile as contact_person_mobile', 'breeder_user.status_instance'
                             )
                     ->get();

         }else{
             $details = DB::table('users')
                     ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                     ->join('roles', 'role_user.role_id','=','roles.id')
                     ->whereIn('role_id', [3])
                     ->where('users.id', '=', $request->userId)
                     ->join('customer_user', 'customer_user.id', '=', 'users.userable_id')
                     ->select('users.id as user_id', 'users.name as user_name', 'users.email', 'roles.title as role',
                             'customer_user.address_addressLine1 as addressLine1', 'customer_user.address_addressLine2 as addressLine2',
                             'customer_user.address_province as province', 'customer_user.address_zipCode as zipcode', 'customer_user.landline',
                             'customer_user.mobile', 'customer_user.status_instance'
                             )
                     ->get();


         }


         $details->first()->role = ucfirst($details->first()->role);
         $details->first()->status_instance = ucfirst($details->first()->status_instance);
         return $details;
     }

     /*
      * Get user's last 5 transactions
      *
      * @param $request (user_id, role_id, userable_id)
      * @return collection tranasaction data
      *
      */
     public function fetchUserTransaction(Request $request){
          if($request->userRole == 2){
              $transactions = DB::table('users')
                          ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                          ->join('roles', 'role_user.role_id','=','roles.id')
                          ->where('role_id', '=', 3)
                          ->join('transaction_logs', 'transaction_logs.customer_id', '=' , 'users.userable_id')
                          ->join('product_reservations', 'product_reservations.product_id', '=' , 'transaction_logs.product_id')
                          ->join('products', 'products.id', '=', 'product_reservations.product_id')
                          ->select('product_reservations.id as transaction_id', 'transaction_logs.customer_id as customer_id', 'users.name as dealer_name',
                                  'transaction_logs.breeder_id as breeder_id','product_reservations.product_id as product_id',
                                   'products.name as product_name','product_reservations.order_status', 'transaction_logs.created_at as date')
                          ->where('transaction_logs.breeder_id', '=', $request->userUserableId)
                          ->groupBy('product_reservations.product_id')
                          ->orderBy('product_reservations.id', 'desc')
                          ->take(5)
                          ->get();

          }else{
              $transactions = DB::table('users')
                          ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                          ->join('roles', 'role_user.role_id','=','roles.id')
                          ->where('role_id', '=', 2)
                          ->join('transaction_logs', 'transaction_logs.breeder_id', '=' , 'users.userable_id')
                          ->join('product_reservations', 'product_reservations.product_id', '=' , 'transaction_logs.product_id')
                          ->join('products', 'products.id', '=', 'product_reservations.product_id')
                          ->select('product_reservations.id as transaction_id', 'transaction_logs.customer_id as customer_id', 'users.name as dealer_name',
                                  'transaction_logs.breeder_id as breeder_id','product_reservations.product_id as product_id',
                                   'products.name as product_name','product_reservations.order_status', 'transaction_logs.created_at as date')
                          ->where('transaction_logs.customer_id', '=', $request->userUserableId)
                          ->groupBy('product_reservations.product_id')
                          ->orderBy('product_reservations.id', 'desc')
                          ->take(5)
                          ->get();
          }

          foreach ($transactions as $transaction) {
              $transaction->order_status = ucfirst($transaction->order_status);
          }

          return $transactions;
     }

     /*
      * Get user's transaction history
      *
      * @param $request (username, user_id, role_id, userable_id)
      * @return collection tranasaction data
      *
      */
     public function fetchUserTransactionHistory(Request $request){
         $username = $request->name;
         $userable = $request->userable;
         $role = $request->role;
         if($request->role == 2){
             $transactions = DB::table('users')
                         ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                         ->join('roles', 'role_user.role_id','=','roles.id')
                         ->where('role_id', '=', 3)
                         ->join('transaction_logs', 'transaction_logs.customer_id', '=' , 'users.userable_id')
                         ->join('product_reservations', 'product_reservations.product_id', '=' , 'transaction_logs.product_id')
                         ->join('products', 'products.id', '=', 'product_reservations.product_id')
                         ->select('product_reservations.id as transaction_id', 'transaction_logs.customer_id as customer_id', 'users.name as dealer_name',
                                 'transaction_logs.breeder_id as breeder_id','product_reservations.product_id as product_id',
                                  'products.name as product_name','product_reservations.order_status', 'transaction_logs.created_at as date')
                         ->where('transaction_logs.breeder_id', '=', $request->userable)
                         ->groupBy('product_reservations.product_id')
                         ->orderBy('product_reservations.id', 'desc')
                         ->paginate(10);
         }else{
             $transactions = DB::table('users')
                         ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                         ->join('roles', 'role_user.role_id','=','roles.id')
                         ->where('role_id', '=', 2)
                         ->join('transaction_logs', 'transaction_logs.breeder_id', '=' , 'users.userable_id')
                         ->join('product_reservations', 'product_reservations.product_id', '=' , 'transaction_logs.product_id')
                         ->join('products', 'products.id', '=', 'product_reservations.product_id')
                         ->select('product_reservations.id as transaction_id', 'transaction_logs.customer_id as customer_id', 'users.name as dealer_name',
                                 'transaction_logs.breeder_id as breeder_id','product_reservations.product_id as product_id',
                                  'products.name as product_name','product_reservations.order_status', 'transaction_logs.created_at as date')
                         ->where('transaction_logs.customer_id', '=', $request->userable)
                         ->groupBy('product_reservations.product_id')
                         ->orderBy('product_reservations.id', 'desc')
                         ->paginate(10);
         }

         return view('user.admin.usersTransactionHistory', compact('username', 'userable', 'role','transactions'));
     }

     /*
      * Get user's transaction history
      *
      * @param $request (username, user_id, role_id, userable_id, search string, category array)
      * @return collection tranasaction data
      *
      */
     public function searchUserTransactionHistory(Request $request){
         $username = $request->name;
         $userable = $request->userable;
         $role = $request->role;
         if($request->role == 2){
             $transactions = DB::table('users')
                         ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                         ->join('roles', 'role_user.role_id','=','roles.id')
                         ->where('role_id', '=', 3)
                         ->join('transaction_logs', 'transaction_logs.customer_id', '=' , 'users.userable_id')
                         ->join('product_reservations', 'product_reservations.product_id', '=' , 'transaction_logs.product_id')
                         ->join('products', 'products.id', '=', 'product_reservations.product_id')
                         ->select('product_reservations.id as transaction_id', 'transaction_logs.customer_id as customer_id', 'users.name as dealer_name',
                                 'transaction_logs.breeder_id as breeder_id','product_reservations.product_id as product_id',
                                  'products.name as product_name','product_reservations.order_status', 'transaction_logs.created_at as date')
                         ->where('transaction_logs.breeder_id', '=', $userable)
                         ->groupBy('product_reservations.product_id')
                         ->when(!empty($request->option), function ($query) use ($request) {
                                return $query->whereIn('product_reservations.order_status', $request->option);
                            })
                         ->where(function ($query) use ($request) {
                             $query->where('users.name', 'LIKE', "%$request->search%")
                                   ->orWhere('products.name', 'LIKE', "%$request->search%");
                         })
                         ->orderBy('product_reservations.id', 'desc')
                         ->paginate(10);

         }else{
             $transactions = DB::table('users')
                         ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                         ->join('roles', 'role_user.role_id','=','roles.id')
                         ->where('role_id', '=', 2)
                         ->join('transaction_logs', 'transaction_logs.breeder_id', '=' , 'users.userable_id')
                         ->join('product_reservations', 'product_reservations.product_id', '=' , 'transaction_logs.product_id')
                         ->join('products', 'products.id', '=', 'product_reservations.product_id')
                         ->select('product_reservations.id as transaction_id', 'transaction_logs.customer_id as customer_id', 'users.name as dealer_name',
                                 'transaction_logs.breeder_id as breeder_id','product_reservations.product_id as product_id',
                                  'products.name as product_name','product_reservations.order_status', 'transaction_logs.created_at as date')
                         ->where('transaction_logs.customer_id', '=', $request->userable)
                         ->groupBy('product_reservations.product_id')
                         ->when(!empty($request->option), function ($query) use ($request) {
                                return $query->whereIn('product_reservations.order_status', $request->option);
                            })
                         ->where(function ($query) use ($request) {
                             $query->where('users.name', 'LIKE', "%$request->search%")
                                   ->orWhere('products.name', 'LIKE', "%$request->search%");
                         })
                         ->orderBy('product_reservations.id', 'desc')
                         ->paginate(10);
         }

         return view('user.admin.usersTransactionHistory', compact('username', 'userable', 'role','transactions'));
     }

     /*
      * Helper function to get the monthly count of transaction using the latest update
      *
      * @param integer
      * @return array of count
      *
      */
     public function getTransactionCountPerMonth($year){
         $transactions =  DB::table('transaction_logs')
                         ->groupBy('product_id')
                         ->select('product_id', DB::raw('MAX(YEAR(created_at)) latest_year, MAX(MONTH(created_at)) latest_month, YEAR(created_at) year, MONTH(created_at) month, MONTHNAME(created_at) month_name'))
                         ->whereYear('created_at', $year)
                         ->get();
        $monthlyCount = array_fill(0, 12, 0);
        foreach($transactions as $transaction){
            if(($transaction->latest_year == $year) && ($transaction->latest_month == 1)){
                $monthlyCount[0]++;
            }else if(($transaction->latest_year == $year) && ($transaction->latest_month == 2)){
                $monthlyCount[1]++;
            }else if(($transaction->latest_year == $year) && ($transaction->latest_month == 3)){
                $monthlyCount[2]++;
            }else if(($transaction->latest_year == $year) && ($transaction->latest_month == 4)){
                $monthlyCount[3]++;
            }else if(($transaction->latest_year == $year) && ($transaction->latest_month == 5)){
                $monthlyCount[4]++;
            }else if(($transaction->latest_year == $year) && ($transaction->latest_month == 6)){
                $monthlyCount[5]++;
            }else if(($transaction->latest_year == $year) && ($transaction->latest_month == 7)){
                $monthlyCount[6]++;
            }else if(($transaction->latest_year == $year) && ($transaction->latest_month == 8)){
                $monthlyCount[7]++;
            }else if(($transaction->latest_year == $year) && ($transaction->latest_month == 9)){
                $monthlyCount[8]++;
            }else if(($transaction->latest_year == $year) && ($transaction->latest_month == 10)){
                $monthlyCount[9]++;
            }else if(($transaction->latest_year == $year) && ($transaction->latest_month == 11)){
                $monthlyCount[10]++;
            }else if(($transaction->latest_year == $year) && ($transaction->latest_month == 12)){
                $monthlyCount[11]++;
            }

        }
        return($monthlyCount);
     }

     /*
      * Function to get the monthly transactions per year
      *
      * @param none
      * @return array of count, integer year
      *
      */
     public function showStatisticsTransactions(){
        $date = Carbon::now();
        $year = $date->year;
        $transactions = $this->getTransactionCountPerMonth($year);
        return view('user.admin.statisticsTransaction', compact('transactions', 'year'));
     }

     /*
      * Function to get the monthly transactions per year
      *
      * @param integer
      * @return array of count, integer year
      *
      */
     public function showStatisticsTransactionsYear(Request $request){
        $year = $request->year;
        $transactions = $this->getTransactionCountPerMonth($year);

        return view('user.admin.statisticsTransaction', compact('transactions', 'year'));
     }

     public function getTransactionsCounts(){
         $transactions =  DB::table('transaction_logs')
                         ->where('status', '=', 'sold')
                         ->select(DB::raw('YEAR(created_at) year, COUNT(*) count'))
                         ->groupBy('year')
                         ->orderBy('year', 'desc')
                         ->get();
        return $transactions;
     }

     /*
      * Function to get total completed transactions per year
      *
      * @param none
      * @return collection of transaction and count, min year shown, max year shown
      *
      */
     public function showStatisticsTotalTransactions(){
        $transactions =  DB::table('transaction_logs')
                        ->where('status', '=', 'sold')
                        ->select(DB::raw('YEAR(created_at) year, COUNT(*) count'))
                        ->groupBy('year')
                        ->orderBy('year', 'desc')
                        ->get();
        if(count($transactions)!=0){
            $lastTransactions = $transactions->first()->year; //most recent transaction year in the database
            $firstTransactions = $transactions->take(5)->last()->year; //oldest transaction year in the last 5 transaction years
            $showTransactions = $transactions->take(5); // get the last 5 transaction years
            $selectedMin = $firstTransactions;
            $selectedMax = $lastTransactions;
        }
        else{
            $showTransactions = $transactions->take(5); // get the last 5 transaction years
            $selectedMin = Carbon::now()->year;
            $selectedMax = Carbon::now()->year;
        }
        return view('user.admin.statisticsTotalTransaction',compact('showTransactions', 'selectedMin', 'selectedMax'));
     }

     /*
      * Function to get total completed transactions per year in between years in the user request
      *
      * @param year string min and max
      * @return collection of transaction and count, min year shown, max year shown
      *
      */
    public function showStatisticsTotalTransactionsModified(Request $request){
        $start = $request->minyear."-01-01";
        $end =  $request->maxyear."-12-31";

        $transactions =  DB::table('transaction_logs')
                        ->where('status', '=', 'sold')
                        ->whereBetween('created_at', [$start,$end])
                        ->select(DB::raw('YEAR(created_at) AS year, COUNT(*) AS count'))
                        ->groupBy('year')
                        ->orderBy('year', 'desc')
                        ->get();

        $lastTransactions = $transactions->first()->year; //most recent transaction year in the database
        $firstTransactions = $transactions->take(5)->last()->year; //oldest transaction year in the last 5 transaction years
        $showTransactions = $transactions->take(5); // get the last 5 transaction years
        $selectedMin = $firstTransactions;
        $selectedMax = $lastTransactions;
        return view('user.admin.statisticsTotalTransaction',compact('showTransactions', 'selectedMin', 'selectedMax'));
    }

    /*
     * Function get all the spectator users
     *
     * @param none
     * @return collection of spectators
     *
     */
    public function displaySpectators(){
        $spectators = DB::table('users')->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id', '=', 4)
                ->whereNull('deleted_at')
                ->paginate(10);

        return view('user.admin.spectatorList', compact('spectators'));
    }

    /*
     * Function search for a spectator using a search string to match the email address or name of the user
     *
     * @param search string
     * @return collection of spectators
     *
     */
    public function searchSpectators(Request $request){
        $spectators = DB::table('users')->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where(function ($query) use ($request) {
                    $query->where('name', 'LIKE', "%$request->search%")
                          ->orWhere('email', 'LIKE', "%$request->search%");
                })
                ->where('role_id', '=', 4)
                ->whereNull('deleted_at')
                ->paginate(10);

        return view('user.admin.spectatorList', compact('spectators'));
    }

    public function averageMonthlyNewBreeders(){
        $roleSelector = ['selected', ''];
        $chartSelector = ['selected','',''];
        $route = 'admin.statistics.averageNewBreederYear';
        $chartRoute = ['admin.statistics.averageNewBreeder','admin.statistics.averageBlockedBreeder', 'admin.statistics.averageDeletedBreeder'];
        $now = Carbon::now()->endOfYear();
        $yearmaximum = $now->year;
        $pastYear = Carbon::now()->subYear(5)->startofYear();
        $yearminimum = $pastYear->year;
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id','=',2)
                // ->whereNotNull('approved_at')
                // ->whereNull('deleted_at')
                // ->whereNull('blocked_at')
                ->whereBetween('created_at', [$pastYear, $now])
                ->select(DB::raw('YEAR(created_at) year, MONTH(created_at) month, MONTHNAME(created_at) month_name, COUNT(*)/12 as average_count'))
                ->groupBy('year')
                ->get();
        // make an array and fill it with 0, starting index from the last year, size of year now-last year
        $averageCount = array_fill($yearminimum,($yearmaximum-$yearminimum)+1 ,0);
        // fill the array of counts using the year from the query as the index while the value is the cieling of count average to allow integers only.
        foreach ($counts as $count) {
            $averageCount[$count->year] = ceil($count->average_count);
        }

        return view('user.admin.averageStatistics', compact('roleSelector', 'chartSelector', 'route', 'chartRoute', 'yearminimum', 'yearmaximum', 'averageCount'));
    }

    public function averageMonthlyNewBreedersYear(Request $request){

        $roleSelector = ['selected', ''];
        $chartSelector = ['selected','',''];
        $route = 'admin.statistics.averageNewBreederYear';
        $chartRoute = ['admin.statistics.averageNewBreeder','admin.statistics.averageBlockedBreeder', 'admin.statistics.averageDeletedBreeder'];
        $now = $request->yearmax."-12-31";
        $now = Carbon::parse($now);
        $pastYear =  $request->yearmin."-01-01";
        $pastYear = Carbon::parse($pastYear);
        $temp = [$pastYear, $now];
        sort($temp);
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id','=',2)
                // ->whereNotNull('approved_at')
                // ->whereNull('deleted_at')
                // ->whereNull('blocked_at')
                ->whereBetween('created_at', $temp)
                ->select(DB::raw('YEAR(created_at) year, MONTH(created_at) month, MONTHNAME(created_at) month_name, COUNT(*)/12 as average_count'))
                ->groupBy('year')
                ->get();
        $yearminimum = $temp[0]->year;
        $yearmaximum = $temp[1]->year;
        // make an array and fill it with 0, starting index from the last year, size of year now-last year
        $averageCount = array_fill($yearminimum,($yearmaximum-$yearminimum)+1 ,0);
        // fill the array of counts using the year from the query as the index while the value is the cieling of count average to allow integers only.
        foreach ($counts as $count) {
            $averageCount[$count->year] = ceil($count->average_count);
        }

        return view('user.admin.averageStatistics', compact('roleSelector', 'chartSelector', 'route', 'chartRoute', 'yearminimum', 'yearmaximum', 'averageCount'));
    }

    public function averageMonthlyBlockedBreeders(){
        $roleSelector = ['selected', ''];
        $chartSelector = ['','selected',''];
        $route = 'admin.statistics.averageBlockedBreederYear';
        $chartRoute = ['admin.statistics.averageNewBreeder','admin.statistics.averageBlockedBreeder', 'admin.statistics.averageDeletedBreeder'];
        $now = Carbon::now()->endOfYear();
        $yearmaximum = $now->year;
        $pastYear = Carbon::now()->subYear(5)->startofYear();
        $yearminimum = $pastYear->year;
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id','=',2)
                ->whereNotNull('approved_at')
                ->whereNotNull('blocked_at')
                // ->whereNull('deleted_at')
                ->whereBetween('blocked_at', [$pastYear, $now])
                ->select(DB::raw('YEAR(blocked_at) year, MONTH(blocked_at) month, MONTHNAME(blocked_at) month_name, COUNT(*)/12 as average_count'))
                ->groupBy('year')
                ->get();

        $averageCount = array_fill($yearminimum,($yearmaximum-$yearminimum)+1 ,0);
        // fill the array of counts using the year from the query as the index while the value is the cieling of count average to allow integers only.
        foreach ($counts as $count) {
            $averageCount[$count->year] = ceil($count->average_count);
        }

        return view('user.admin.averageStatistics', compact('roleSelector', 'chartSelector', 'route', 'chartRoute', 'yearminimum', 'yearmaximum', 'averageCount'));

    }
    public function averageMonthlyBlockedBreedersYear(Request $request){
        $roleSelector = ['selected', ''];
        $chartSelector = ['','selected',''];
        $route = 'admin.statistics.averageBlockedBreederYear';
        $chartRoute = ['admin.statistics.averageNewBreeder','admin.statistics.averageBlockedBreeder', 'admin.statistics.averageDeletedBreeder'];
        $now = $request->yearmax."-12-31";
        $now = Carbon::parse($now);
        $pastYear =  $request->yearmin."-01-01";
        $pastYear = Carbon::parse($pastYear);
        $temp = [$pastYear, $now];
        sort($temp);
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id','=',2)
                ->whereNotNull('approved_at')
                ->whereNotNull('blocked_at')
                // ->whereNull('deleted_at')
                ->whereBetween('blocked_at', $temp)
                ->select(DB::raw('YEAR(blocked_at) year, MONTH(blocked_at) month, MONTHNAME(blocked_at) month_name, COUNT(*)/12 as average_count'))
                ->groupBy('year')
                ->get();
        $yearminimum = $temp[0]->year;
        $yearmaximum = $temp[1]->year;
        $averageCount = array_fill($yearminimum,($yearmaximum-$yearminimum)+1 ,0);
        // fill the array of counts using the year from the query as the index while the value is the cieling of count average to allow integers only.
        foreach ($counts as $count) {
            $averageCount[$count->year] = ceil($count->average_count);
        }

        return view('user.admin.averageStatistics', compact('roleSelector', 'chartSelector', 'route', 'chartRoute', 'yearminimum', 'yearmaximum', 'averageCount'));
    }
    public function averageMonthlyDeletedBreeders(){
        $roleSelector = ['selected', ''];
        $chartSelector = ['','','selected'];
        $route = 'admin.statistics.averageDeletedBreederYear';
        $chartRoute = ['admin.statistics.averageNewBreeder','admin.statistics.averageBlockedBreeder', 'admin.statistics.averageDeletedBreeder'];
        $now = Carbon::now()->endOfYear();
        $yearmaximum = $now->year;
        $pastYear = Carbon::now()->subYear(5)->startofYear();
        $yearminimum = $pastYear->year;
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id','=',2)
                ->whereNotNull('approved_at')
                ->whereNotNull('deleted_at')
                ->whereBetween('deleted_at', [$pastYear, $now])
                ->select(DB::raw('YEAR(deleted_at) year, MONTH(deleted_at) month, MONTHNAME(deleted_at) month_name, COUNT(*)/12 as average_count'))
                ->groupBy('year')
                ->get();

        $averageCount = array_fill($yearminimum,($yearmaximum-$yearminimum)+1 ,0);
        // fill the array of counts using the year from the query as the index while the value is the cieling of count average to allow integers only.
        foreach ($counts as $count) {
            $averageCount[$count->year] = ceil($count->average_count);
        }

        return view('user.admin.averageStatistics', compact('roleSelector', 'chartSelector', 'route', 'chartRoute', 'yearminimum', 'yearmaximum', 'averageCount'));
    }
    public function averageMonthlyDeletedBreedersYear(Request $request){
        $roleSelector = ['selected', ''];
        $chartSelector = ['','','selected'];
        $route = 'admin.statistics.averageDeletedBreederYear';
        $chartRoute = ['admin.statistics.averageNewBreeder','admin.statistics.averageBlockedBreeder', 'admin.statistics.averageDeletedBreeder'];
        $now = $request->yearmax."-12-31";
        $now = Carbon::parse($now);
        $pastYear =  $request->yearmin."-01-01";
        $pastYear = Carbon::parse($pastYear);
        $temp = [$pastYear, $now];
        sort($temp);
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id','=',2)
                ->whereNotNull('approved_at')
                ->whereNotNull('deleted_at')
                ->whereBetween('deleted_at', $temp)
                ->select(DB::raw('YEAR(deleted_at) year, MONTH(deleted_at) month, MONTHNAME(deleted_at) month_name, COUNT(*)/12 as average_count'))
                ->groupBy('year')
                ->get();
        $yearminimum = $temp[0]->year;
        $yearmaximum = $temp[1]->year;
        $averageCount = array_fill($yearminimum,($yearmaximum-$yearminimum)+1 ,0);
        // fill the array of counts using the year from the query as the index while the value is the cieling of count average to allow integers only.
        foreach ($counts as $count) {
            $averageCount[$count->year] = ceil($count->average_count);
        }

        return view('user.admin.averageStatistics', compact('roleSelector', 'chartSelector', 'route', 'chartRoute', 'yearminimum', 'yearmaximum', 'averageCount'));
    }

    public function averageMonthlyNewCustomers(){
        $roleSelector = ['', 'selected'];
        $chartSelector = ['selected','',''];
        $route = 'admin.statistics.averageNewCustomerYear';
        $chartRoute = ['admin.statistics.averageNewCustomers','admin.statistics.averageBlockedCustomers', 'admin.statistics.averageDeletedCustomers'];
        $now = Carbon::now()->endOfYear();
        $yearmaximum = $now->year;
        $pastYear = Carbon::now()->subYear(5)->startofYear();
        $yearminimum = $pastYear->year;
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id','=',3)
                // ->whereNotNull('approved_at')
                // ->whereNull('deleted_at')
                // ->whereNull('blocked_at')
                ->whereBetween('created_at', [$pastYear, $now])
                ->select(DB::raw('YEAR(created_at) year, MONTH(created_at) month, MONTHNAME(created_at) month_name, COUNT(*)/12 as average_count'))
                ->groupBy('year')
                ->get();
        // make an array and fill it with 0, starting index from the last year, size of year now-last year
        $averageCount = array_fill($yearminimum,($yearmaximum-$yearminimum)+1 ,0);
        // fill the array of counts using the year from the query as the index while the value is the cieling of count average to allow integers only.
        foreach ($counts as $count) {
            $averageCount[$count->year] = ceil($count->average_count);
        }

        return view('user.admin.averageStatistics', compact('roleSelector', 'chartSelector', 'route', 'chartRoute', 'yearminimum', 'yearmaximum', 'averageCount'));
    }

    public function averageMonthlyNewCustomersYear(Request $request){
        $roleSelector = ['', 'selected'];
        $chartSelector = ['selected','',''];
        $route = 'admin.statistics.averageNewCustomerYear';
        $chartRoute = ['admin.statistics.averageNewCustomers','admin.statistics.averageBlockedCustomers', 'admin.statistics.averageDeletedCustomers'];
        $now = $request->yearmax."-12-31";
        $now = Carbon::parse($now);
        $pastYear =  $request->yearmin."-01-01";
        $pastYear = Carbon::parse($pastYear);
        $temp = [$pastYear, $now];
        sort($temp);
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id','=',3)
                // ->whereNotNull('approved_at')
                // ->whereNull('deleted_at')
                // ->whereNull('blocked_at')
                ->whereBetween('created_at', $temp)
                ->select(DB::raw('YEAR(created_at) year, MONTH(created_at) month, MONTHNAME(created_at) month_name, COUNT(*)/12 as average_count'))
                ->groupBy('year')
                ->get();
        $yearminimum = $temp[0]->year;
        $yearmaximum = $temp[1]->year;
        // make an array and fill it with 0, starting index from the last year, size of year now-last year
        $averageCount = array_fill($yearminimum,($yearmaximum-$yearminimum)+1 ,0);
        // fill the array of counts using the year from the query as the index while the value is the cieling of count average to allow integers only.
        foreach ($counts as $count) {
            $averageCount[$count->year] = ceil($count->average_count);
        }

        return view('user.admin.averageStatistics', compact('roleSelector', 'chartSelector', 'route', 'chartRoute', 'yearminimum', 'yearmaximum', 'averageCount'));
    }

    public function averageMonthlyBlockedCustomers(){
        $roleSelector = ['', 'selected'];
        $chartSelector = ['','selected',''];
        $route = 'admin.statistics.averageBlockedCustomerYear';
        $chartRoute = ['admin.statistics.averageNewCustomers','admin.statistics.averageBlockedCustomers', 'admin.statistics.averageDeletedCustomers'];
        $now = Carbon::now()->endOfYear();
        $yearmaximum = $now->year;
        $pastYear = Carbon::now()->subYear(5)->startofYear();
        $yearminimum = $pastYear->year;
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id','=',3)
                ->whereNotNull('approved_at')
                ->whereNotNull('blocked_at')
                // ->whereNull('deleted_at')
                ->whereBetween('blocked_at', [$pastYear, $now])
                ->select(DB::raw('YEAR(blocked_at) year, MONTH(blocked_at) month, MONTHNAME(blocked_at) month_name, COUNT(*)/12 as average_count'))
                ->groupBy('year')
                ->get();
        // make an array and fill it with 0, starting index from the last year, size of year now-last year
        $averageCount = array_fill($yearminimum,($yearmaximum-$yearminimum)+1 ,0);
        // fill the array of counts using the year from the query as the index while the value is the cieling of count average to allow integers only.
        foreach ($counts as $count) {
            $averageCount[$count->year] = ceil($count->average_count);
        }

        return view('user.admin.averageStatistics', compact('roleSelector', 'chartSelector', 'route', 'chartRoute', 'yearminimum', 'yearmaximum', 'averageCount'));
    }

    public function averageMonthlyBlockedCustomersYear(Request $request){
        $roleSelector = ['', 'selected'];
        $chartSelector = ['','selected',''];
        $route = 'admin.statistics.averageBlockedCustomerYear';
        $chartRoute = ['admin.statistics.averageNewCustomers','admin.statistics.averageBlockedCustomers', 'admin.statistics.averageDeletedCustomers'];
        $now = $request->yearmax."-12-31";
        $now = Carbon::parse($now);
        $pastYear =  $request->yearmin."-01-01";
        $pastYear = Carbon::parse($pastYear);
        $temp = [$pastYear, $now];
        sort($temp);
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id','=',3)
                ->whereNotNull('approved_at')
                ->whereNotNull('blocked_at')
                // ->whereNull('deleted_at')
                ->whereBetween('blocked_at', $temp)
                ->select(DB::raw('YEAR(blocked_at) year, MONTH(blocked_at) month, MONTHNAME(blocked_at) month_name, COUNT(*)/12 as average_count'))
                ->groupBy('year')
                ->get();

        $yearminimum = $temp[0]->year;
        $yearmaximum = $temp[1]->year;
        // make an array and fill it with 0, starting index from the last year, size of year now-last year
        $averageCount = array_fill($yearminimum,($yearmaximum-$yearminimum)+1 ,0);
        // fill the array of counts using the year from the query as the index while the value is the cieling of count average to allow integers only.
        foreach ($counts as $count) {
            $averageCount[$count->year] = ceil($count->average_count);
        }

        return view('user.admin.averageStatistics', compact('roleSelector', 'chartSelector', 'route', 'chartRoute', 'yearminimum', 'yearmaximum', 'averageCount'));
    }

    public function averageMonthlyDeletedCustomers(){
        $roleSelector = ['', 'selected'];
        $chartSelector = ['','','selected'];
        $route = 'admin.statistics.averageDeletedCustomerYear';
        $chartRoute = ['admin.statistics.averageNewCustomers','admin.statistics.averageBlockedCustomers', 'admin.statistics.averageDeletedCustomers'];
        $now = Carbon::now()->endOfYear();
        $yearmaximum = $now->year;
        $pastYear = Carbon::now()->subYear(5)->startofYear();
        $yearminimum = $pastYear->year;
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id','=',3)
                ->whereNotNull('approved_at')
                ->whereNotNull('deleted_at')
                ->whereBetween('deleted_at', [$pastYear, $now])
                ->select(DB::raw('YEAR(deleted_at) year, MONTH(deleted_at) month, MONTHNAME(deleted_at) month_name, COUNT(*)/12 as average_count'))
                ->groupBy('year')
                ->get();
        // make an array and fill it with 0, starting index from the last year, size of year now-last year
        $averageCount = array_fill($yearminimum,($yearmaximum-$yearminimum)+1 ,0);
        // fill the array of counts using the year from the query as the index while the value is the cieling of count average to allow integers only.
        foreach ($counts as $count) {
            $averageCount[$count->year] = ceil($count->average_count);
        }

        return view('user.admin.averageStatistics', compact('roleSelector', 'chartSelector', 'route', 'chartRoute', 'yearminimum', 'yearmaximum', 'averageCount'));
    }

    public function averageMonthlyDeletedCustomersYear(Request $request){
        $roleSelector = ['', 'selected'];
        $chartSelector = ['','','selected'];
        $route = 'admin.statistics.averageDeletedCustomerYear';
        $chartRoute = ['admin.statistics.averageNewCustomers','admin.statistics.averageBlockedCustomers', 'admin.statistics.averageDeletedCustomers'];
        $now = $request->yearmax."-12-31";
        $now = Carbon::parse($now);
        $pastYear =  $request->yearmin."-01-01";
        $pastYear = Carbon::parse($pastYear);
        $temp = [$pastYear, $now];
        sort($temp);
        $counts = DB::table('users')
                ->join('role_user', 'users.id', '=' , 'role_user.user_id')
                ->join('roles', 'role_user.role_id','=','roles.id')
                ->where('role_id','=',3)
                ->whereNotNull('approved_at')
                ->whereNotNull('deleted_at')
                ->whereBetween('deleted_at', $temp)
                ->select(DB::raw('YEAR(deleted_at) year, MONTH(deleted_at) month, MONTHNAME(deleted_at) month_name, COUNT(*)/12 as average_count'))
                ->groupBy('year')
                ->get();
        $yearminimum = $temp[0]->year;
        $yearmaximum = $temp[1]->year;
        // make an array and fill it with 0, starting index from the last year, size of year now-last year
        $averageCount = array_fill($yearminimum,($yearmaximum-$yearminimum)+1 ,0);
        // fill the array of counts using the year from the query as the index while the value is the cieling of count average to allow integers only.
        foreach ($counts as $count) {
            $averageCount[$count->year] = ceil($count->average_count);
        }

        return view('user.admin.averageStatistics', compact('roleSelector', 'chartSelector', 'route', 'chartRoute', 'yearminimum', 'yearmaximum', 'averageCount'));
    }

    /*
     * Get current user's information
     *
     * @param none
     * @return array
     *
     */
    public function getAdminInformation(){
        $user_data = [$this->user->id, $this->user->userable_id, $this->user->name, $this->user->email];
        return $user_data;
    }

    public function viewUsers(){
        $breeders = Breeder::all();
        $customers = Customer::all();
        return view('user.admin.viewUsers', compact('breeders', 'customers'));
    }

    public function messenger(){
        $users = User::all();
        return view('user.admin.messenger', compact('users'));
    }

    public function send(Request $request){
        if($request->ajax()){
            $rcpts = User::whereIn('id', json_decode($_POST['receipients']))->get();
            if($request->type == 'mail'){
                $this->sendMail($request->message, $rcpts);
            }
            else{
                $temp;
                foreach ($rcpts as $rcpt) {
                    if($rcpt->userable_type == 'App\Models\Customer'){
                        $temp = Customer::where('id', $rcpt->userable_id)->first();
                        $temp = $temp->mobile;
                    }
                    else if($rcpt->userable_type == 'App\Models\Breeder'){
                        $temp = Breeder::where('id', $rcpt->userable_id)->first();
                        $temp = $temp->office_mobile;
                    }
                    $this->sendSMS($request->message, $temp);
                }
            }
        }
    }

    public function sendMail($message, $rcpts){
        $data = ['message_body' => $message];
        foreach ($rcpts as $rcpt) {
            Mail::send(['html'=>'emails.email'], $data, function ($message) use($rcpt, $data){
               $message->to($rcpt['email'])->subject('Announcement from Swine E-Commerce PH');
            });
        }
    }

    public function str_replace_first($from, $to, $subject){
        $from = '/'.preg_quote($from, '/').'/';
        return preg_replace($from, $to, $subject, 1);
    }

    public function sendSMS($message, $rcpt){
        $arr_post_body = array(
            "message_type" => "SEND",
            "mobile_number" => $this->str_replace_first('0', '63', $rcpt),
            "shortcode" => "292909000",
            "message_id" => rand(0,1000000), //to be improved if messages will be stored in db
            "message" => urlencode($message),
            "client_id" => "812afb6beff25f87b60176da02b70e092bbc886671d9bc144b1d1e327c47d28e",
            "secret_key" => "db0f8557a41795d530c04d8cd371c75ce9a869a363078415bdc0cb2f149cbe0a"
        );

        $query_string = http_build_query($arr_post_body);
        $URL = "https://post.chikka.com/smsapi/request";

        $curl_handler = curl_init($URL);
        curl_setopt($curl_handler, CURLOPT_URL, $URL);
        curl_setopt($curl_handler, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_handler, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_handler, CURLOPT_POST, count($arr_post_body));
        curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $query_string);
        curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($curl_handler);
        $info = curl_getinfo($curl_handler, CURLINFO_HTTP_CODE);
        curl_close($curl_handler);
    }
}
