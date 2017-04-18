<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Requests\BreederProfileRequest;
use App\Http\Requests\BreederPersonalProfileRequest;
use App\Http\Requests\BreederFarmProfileRequest;
use App\Http\Requests\ChangePasswordRequest;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Breeder;
use App\Models\Customer;
use App\Models\FarmAddress;
use App\Models\Product;
<<<<<<< HEAD
use App\Models\Image;
use App\Models\Sessions;
=======
use App\Models\User;
>>>>>>> upstream/master

use Auth;
use Mail;
use App\Models\Image;

use Storage;

class BreederController extends Controller
{
    protected $user;

	/**
     * Create new BreederController instance
     */
    public function __construct()
    {
        $this->middleware('role:breeder');
        $this->middleware('updateProfile:breeder',['except' => ['index','storeProfile']]);
        $this->middleware(function($request, $next){
            $this->user = Auth::user();

            return $next($request);
        });
    }

	/**
	 * Show Home Page of breeder
	 *
	 * @param  Request $request
	 * @return View
	 */
    public function index(Request $request)
    {
        if($request->user()->updateProfileNeeded()) return view('user.breeder.createProfile');
        return view('user.breeder.home');
    }

    /**
     * Change password of Breeder user
     *
     * @param   ChangePasswordRequest $request
     * @return  String
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        if($request->ajax()){

            $this->user->password = bcrypt($request->new_password);
            $this->user->save();

            return "OK";
        }
    }

    /**
     * Show Page for Breeder to complete profile
     *
     * @return View
     */
    public function createProfile()
    {
        return view('user.breeder.createProfile');
    }

    /**
     * Create and store Breeder profile data to database and
     * Associate User to Breeder user type as well
     *
     * @param  BreederProfileRequest $request
     * @return Redirect
     */
    public function storeProfile(BreederProfileRequest $request)
    {
        $user = $this->user;
        $breeder = Breeder::create($request->only(['officeAddress_addressLine1',
            'officeAddress_addressLine2',
            'officeAddress_province',
            'officeAddress_zipCode',
            'office_landline',
            'office_mobile',
            'website',
            'produce',
            'contactPerson_name',
            'contactPerson_mobile']
        ));

        $farmAddressArray = [];

        for ($i = 1; $i <= count($request->input('farmAddress.*.*'))/8; $i++) {
            $farmAddress = new FarmAddress;
            $farmAddress->name = $request->input('farmAddress.'.$i.'.name');
            $farmAddress->addressLine1 = $request->input('farmAddress.'.$i.'.addressLine1');
            $farmAddress->addressLine2 = $request->input('farmAddress.'.$i.'.addressLine2');
            $farmAddress->province = $request->input('farmAddress.'.$i.'.province');
            $farmAddress->zipCode = $request->input('farmAddress.'.$i.'.zipCode');
            $farmAddress->farmType = $request->input('farmAddress.'.$i.'.farmType');
            $farmAddress->landline = $request->input('farmAddress.'.$i.'.landline');
            $farmAddress->mobile = $request->input('farmAddress.'.$i.'.mobile');
            array_push($farmAddressArray,$farmAddress);
        }

        $breeder->users()->save($user);
        $breeder->farmAddresses()->saveMany($farmAddressArray);
        $user->update_profile = 0;
        $user->save();
        return redirect()->route('breeder.edit')
            ->with('message','Profile completed.');
    }

    /**
     * Show Page for Breeder to update profile
     *
     * @param  Request $request
     * @return View
     */
    public function editProfile(Request $request)
    {
        $breeder = $this->user->userable;
        $breeder->logoImage = ($breeder->logo_img_id) ? '/images/breeder/'.Image::find($breeder->logo_img_id)->name : '/images/default_logo.png' ;
        $farmAddresses = $breeder->farmAddresses;
        return view('user.breeder.editProfile', compact('breeder', 'farmAddresses'));
    }

    /**
     * Update Breeder's personal information
     * AJAX
     *
     * @param  BreederPersonalProfileRequest $request
     * @return JSON / View
     */
    public function updatePersonal(BreederPersonalProfileRequest $request)
    {
        $breeder = Auth::user()->userable;
        $breeder->fill($request->only(['officeAddress_addressLine1',
            'officeAddress_addressLine2',
            'officeAddress_province',
            'officeAddress_zipCode',
            'office_landline',
            'office_mobile',
            'website',
            'produce',
            'contactPerson_name',
            'contactPerson_mobile']
        ))->save();

        if($request->ajax()) return $breeder->toJson();
        else return redirect()->route('breeder.edit');
    }

    /**
     * Add Breeder's farm information instance
     * AJAX
     *
     * @param  BreederFarmProfileRequest $request
     * @return JSON / View
     */
    public function addFarm(BreederFarmProfileRequest $request)
    {
        $breeder = $this->user->userable;
        $farmAddressArray = [];

        for ($i = 1; $i <= count($request->input('farmAddress.*.*'))/8; $i++) {
            $farmAddress = new FarmAddress;
            $farmAddress->name = $request->input('farmAddress.'.$i.'.name');
            $farmAddress->addressLine1 = $request->input('farmAddress.'.$i.'.addressLine1');
            $farmAddress->addressLine2 = $request->input('farmAddress.'.$i.'.addressLine2');
            $farmAddress->province = $request->input('farmAddress.'.$i.'.province');
            $farmAddress->zipCode = $request->input('farmAddress.'.$i.'.zipCode');
            $farmAddress->farmType = $request->input('farmAddress.'.$i.'.farmType');
            $farmAddress->landline = $request->input('farmAddress.'.$i.'.landline');
            $farmAddress->mobile = $request->input('farmAddress.'.$i.'.mobile');
            array_push($farmAddressArray,$farmAddress);
        }

        $breeder->farmAddresses()->saveMany($farmAddressArray);

        if($request->ajax()) return collect($farmAddressArray)->toJson();
        else return redirect()->route('breeder.edit');

    }

    /**
     * Update Breeder's farm information instance
     * AJAX
     *
     * @param  BreederFarmProfileRequest $request
     * @return JSON / View
     */
    public function updateFarm(BreederFarmProfileRequest $request)
    {
        $farmAddress = FarmAddress::find($request->id);

        $farmAddress->name = $request->input('farmAddress.1.name');
        $farmAddress->addressLine1 = $request->input('farmAddress.1.addressLine1');
        $farmAddress->addressLine2 = $request->input('farmAddress.1.addressLine2');
        $farmAddress->zipCode = $request->input('farmAddress.1.zipCode');
        $farmAddress->farmType = $request->input('farmAddress.1.farmType');
        $farmAddress->landline = $request->input('farmAddress.1.landline');
        $farmAddress->mobile = $request->input('farmAddress.1.mobile');
        $farmAddress->save();

        if($request->ajax()) return $farmAddress->toJson();
        else return redirect()->route('breeder.edit');
    }

    /**
     * Delete Breeder's farm information instance
     * AJAX
     *
     * @param  Request $request
     * @return String / View
     */
    public function deleteFarm(Request $request)
    {
        $farmAddress = FarmAddress::find($request->id);
        $farmAddress->delete();
        if($request->ajax()) return "OK";
        else return redirect()->route('breeder.edit');
    }

    /**
     * Show Breeder's Notification Page
     *
     * @return  View
     */
    public function showNotificationsPage()
    {
        return view('user.breeder.notifications');
    }

    /**
     * Get Breeder's notification instances
     * AJAX
     *
     * @param  Request $request
     * @return JSON
     */
    public function getNotifications(Request $request)
    {
        if($request->ajax()){
            $notificationInstances = [];

            foreach ($this->user->notifications as $notification) {
                $notificationInstance = [];
                $notificationInstance['id'] = $notification->id;
                $notificationInstance['data'] = $notification->data;
                $notificationInstance['read_at'] = $notification->read_at;
                array_push($notificationInstances, $notificationInstance);
            }

            return [collect($notificationInstances)->toJson(), csrf_token()];
        }

    }

    /**
     * Get count of Breeder's notification instances
     *
     * @param  Request $request
     * @return Integer
     */
    public function getNotificationsCount(Request $request)
    {
        if($request->ajax()){
            return $this->user->unreadNotifications->count();
        }
    }

    /**
     * Mark the notification instance as read
     *
     * @param  Request $request
     * @return String
     */
    public function seeNotification(Request $request)
    {
        if($request->ajax()){
            $notification = $this->user->notifications()->where('id', $request->notificationId)->get()->first();
            $notification->markAsRead();
            return 'OK';
        }
    }

    /**
     * View Customers of Breeder
     *
     * @return View
     */
    public function viewCustomers(){
        $breeder = $this->user->userable;

        $temp = $breeder->transactionLogs()->first();

        $customers = ($temp==NULL)? []: $temp->customer()->get();

        return view('user.breeder.viewCustomers', compact('customers'));
    }

    /**
     * Upload logo of Breeder
     *
     * @param   Request $request
     * @return  JSON
     */
    public function uploadLogo(Request $request)
    {
        // Check request if it contains logo file input
        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $imageDetails = [];

            // Check if the file had a problem in uploading
            if($file->isValid()){
                $fileExtension = $file->getClientOriginalExtension();

                if($this->isImage($fileExtension)) $imageInfo = $this->createImageInfo($fileExtension);
                else return response()->json('Invalid file extension', 500);

                Storage::disk('public')->put($imageInfo['directoryPath'].$imageInfo['filename'], file_get_contents($file));

                if($file){
                    $breeder = $this->user->userable;

                    // Make Image instance
                    $image = $imageInfo['type'];
                    $image->name = $imageInfo['filename'];

                    $breeder->images()->save($image);

                    $imageDetails['id'] = $image->id;
                    $imageDetails['name'] = $image->name;
                }
                else return response()->json('Move file failed', 500);
            }
            else return response()->json('Upload failed', 500);

            return response()->json(collect($imageDetails)->toJson(), 200);
        }
        else return response()->json('No files detected', 500);
    }

    /**
     * Set logo image of Breeder
     *
     * @param   Request  $request
     * @return  String
     */
    public function setLogo(Request $request)
    {
        if($request->ajax()){
            $breeder = $this->user->userable;
            $breeder->logo_img_id = $request->imageId;
            $breeder->save();

            $redundantImages = $breeder->images->where('id', '<>', $breeder->logo_img_id);

            if(!$redundantImages->isEmpty()){
                $redundantImages->each(function($item, $key){
                    $fullFilePath = '/images/breeder/' . $item->name;

                    // Check if file exists in the storage
                    if(Storage::disk('public')->exists($fullFilePath)) Storage::disk('public')->delete($fullFilePath);

                    $item->delete();
                });
            }

            return '/images/breeder/'.Image::find($request->imageId)->name;
        }

    }

    /**
     * Delete logo of Breeder
     *
     * @param   Request $request
     * @return  JSON
     */
    public function deleteLogo(Request $request)
    {
        if($request->ajax()){
            $image = Image::find($request->imageId);
            $fullFilePath = '/images/breeder/' . $image->name;

            // Check if file exists in the storage
            if(Storage::disk('public')->exists($fullFilePath)) Storage::disk('public')->delete($fullFilePath);

            $image->delete();

            return response()->json('Logo deleted', 200);
        }
    }

    /**
     * Get appropriate image info depending on extension
     *
     * @param  String           $extension
     * @return AssociativeArray $imageInfo
     */
    private function createImageInfo($extension)
    {
        $imageInfo = [];

        $imageInfo['filename'] = snake_case($this->user->name) . '_logo_' . md5(time()) . '_' . $extension;
        $imageInfo['directoryPath'] = '/images/breeder/';
        $imageInfo['type'] = new Image;

        return $imageInfo;
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
}
