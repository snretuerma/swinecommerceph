<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Carbon\Carbon;

use App\Jobs\AddToTransactionLog;
use App\Jobs\SendSMS;
use App\Http\Requests;
use App\Models\Customer;
use App\Models\Breeder;
use App\Models\FarmAddress;
use App\Models\Breed;
use App\Models\Image;
use App\Models\SwineCartItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\TransactionLog;
use App\Models\ProductReservation;
use App\Notifications\BreederRated;
use App\Notifications\ProductRequested;
use App\Repositories\CustomHelpers;

use Auth;

class SwineCartController extends Controller
{
    use CustomHelpers {
        transformBreedSyntax as private;
        transformDateSyntax as private;
        computeAge as private;
    }
    
    protected $user;

    /**
     * Create new CustomerController instance
     */
    public function __construct()
    {
        $this->middleware('role:customer');
        $this->middleware('updateProfile:customer');
        $this->middleware(function($request, $next){
            $this->user = Auth::user();

            return $next($request);
        });
    }

    /**
     * Add to Swine Cart the product picked by the user
     * AJAX
     *
     * @param  Request $request
     * @return Array
     */
    public function addToSwineCart(Request $request)
    {
        if($request->ajax()){
            $customer = $this->user->userable;
            $swineCartItems = $customer->swineCartItems();
            $checkProduct = $swineCartItems->where('product_id',$request->productId)->where('reservation_id', 0)->get();

            // --------- WEBSOCKET SEND DATA -------------
            // $product = Product::find($request->productId);
            // $breeder = $product->breeder;
            // $topic = $breeder->users()->first()->name;
            // $data = $repo->forBreeder($breeder);
            // $data['topic'] = str_slug($topic);
            //
            // // This is our new stuff
    	    // $context = new \ZMQContext();
    	    // $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'Breeder Dashboard Pusher');
    	    // $socket->connect("tcp://127.0.0.1:5555");
            //
    	    // $socket->send(collect($data)->toJson());

            // Check first if product is already in Swine Cart
            if(!$checkProduct->isEmpty()){
                // Then check if it is already requested
                if($checkProduct->first()->ifRequested) $returnArray = ['requested', Product::find($request->productId)->name];
                else $returnArray = ['fail', Product::find($request->productId)->name];
                return $returnArray;
            }
            else{
                $item = new SwineCartItem;
                $item->product_id = $request->productId;
                if(Product::find($request->productId)->type == 'semen') $item->quantity = 2;
                else $item->quantity = 1;

                $swineCartItems->save($item);

                $returnArray = ['success', Product::find($request->productId)->name, $customer->swineCartItems()->where('if_requested',0)->count()];
                return $returnArray;
            }
        }
    }

    /**
     * Rates breeder from Swine Cart
     * AJAX
     *
     * @param  Request $request
     */
    public function rateBreeder(Request $request){
        if($request->ajax()){
            $customer = $this->user->userable;
            $reviews = Breeder::find($request->breederId)->reviews();

            $review = new Review;
            $review->customer_id = $request->customerId;
            $review->comment = $request->comment;
            $review->rating_delivery = $request->delivery;
            $review->rating_transaction = $request->transaction;
            $review->rating_productQuality = $request->productQuality;

            $swineCartItems = $customer->swineCartItems();
            $reviewed = $swineCartItems->where('product_id',$request->productId)->first();
            $reviewed->if_rated = 1;
            $reviewed->save();
            $reviews->save($review);

            $breeder = Breeder::find($request->breederId);

            $transactionDetails = [
                'swineCart_id' => $reviewed->id,
                'customer_id' => $request->customerId,
                'breeder_id' => $request->breederId,
                'product_id' => $request->productId,
                'status' => 'rated',
                'created_at' => Carbon::now()
            ];

            $notificationDetails = [
                'description' => 'Customer <b>' . $this->user->name . ' rated</b> you with ' . round(($review->rating_delivery + $review->rating_transaction + $review->rating_productQuality)/3, 2) . ' (overall average).',
                'time' => $transactionDetails['created_at'],
                'url' => route('dashboard')
            ];

            $smsDetails = [
                'message' => 'SwineCart ['. $this->transformDateSyntax($transactionDetails['created_at'], 1) .']: Customer ' . $this->user->name . ' rated you with ' . round(($review->rating_delivery + $review->rating_transaction + $review->rating_productQuality)/3, 2) . ' (overall average).',
                'recepient' => $breeder->office_mobile
            ];

            // Add new Transaction Log. Queue AddToTransactionLog job
            dispatch(new AddToTransactionLog($transactionDetails));
            // Queue SendSMS job
            dispatch(new SendSMS($smsDetails['message'], $smsDetails['recepient']));

            // Notify Breeder of the rating
            $breederUser = $breeder->users()->first();
            $breederUser->notify(new BreederRated($notificationDetails));

            return "OK";
        }
    }

    /**
     * Requests item from Swine Cart
     * AJAX
     *
     * @param  Request $request
     * @return Array
     */
    public function requestSwineCartItem(Request $request)
    {
        if ($request->ajax()) {
            $customer = $this->user->userable;
            $swineCartItems = $customer->swineCartItems();

            // Update Swine Cart
            $requested = $swineCartItems->find($request->itemId);
            $requested->if_requested = 1;
            $requested->quantity = $request->requestQuantity;
            $requested->date_needed = ($request->dateNeeded) ? date_format(date_create($request->dateNeeded), 'Y-n-j') : '';
            $requested->special_request = $request->specialRequest;
            $requested->save();

            // Update Product
            $product = Product::find($request->productId);
            $product->status = "requested";
            $product->save();

            $breeder = Breeder::find($product->breeder_id);

            $transactionDetails = [
                'swineCart_id' => $requested->id,
                'customer_id' => $requested->customer_id,
                'breeder_id' => $product->breeder_id,
                'product_id' => $product->id,
                'status' => 'requested',
                'created_at' => Carbon::now()
            ];

            $notificationDetails = [
                'description' => '<b>' . $this->user->name . '</b> requested for Product <b>' . $product->name . '</b>.',
                'time' => $transactionDetails['created_at'],
                'url' => route('dashboard.productStatus')
            ];

            $smsDetails = [
                'message' => 'SwineCart ['. $this->transformDateSyntax($transactionDetails['created_at'], 1) .']: ' . $this->user->name . ' requested for Product ' . $product->name . '.',
                'recepient' => $breeder->office_mobile
            ];

            // Add new Transaction Log. Queue AddToTransactionLog job
            dispatch(new AddToTransactionLog($transactionDetails));
            // Queue SendSMS job
            dispatch(new SendSMS($smsDetails['message'], $smsDetails['recepient']));

            // Notify Breeder of the request
            $breederUser = $breeder->users()->first();
            $breederUser->notify(new ProductRequested($notificationDetails));

            return [$customer->swineCartItems()->where('if_requested',0)->count(), $transactionDetails['created_at']];
        }
    }

    /**
     * Delete item from Swine Cart
     * AJAX
     *
     * @param  Request $request
     * @return Array
     */
    public function deleteFromSwineCart(Request $request)
    {
        if($request->ajax()){
            $customer = $this->user->userable;
            $item = $customer->swineCartItems()->where('id',$request->itemId)->get()->first();
            $productName = Product::find($item->product_id)->name;
            if($item) {
                $item->delete();
                return ["success", $productName, $customer->swineCartItems()->where('if_requested',0)->count()];
            }
            else return ["not found", $item->product_id];

        }
        else {
            $customer = $this->user->userable;
            $item = $customer->swineCartItems()->where('id',$request->itemId)->get()->first();
            $productName = Product::find($item->product_id)->name;
            if($item) {
                $item->delete();
                return ["success", $productName, $customer->swineCartItems()->where('if_requested',0)->count()];
            }
            else return ["not found", $item->product_id];
        }

    }

    /**
     * Get items in the Swine Cart
     * [!]AJAX
     *
     * @param  Request $request
     * @return JSON/Array
     */
    public function getSwineCartItems(Request $request)
    {
        if($request->ajax()){
            $customer = $this->user->userable;
            $swineCartItems = $customer->swineCartItems()->where('if_requested',0)->get();
            $items = [];

            foreach ($swineCartItems as $item) {
                $itemDetail = [];
                $product = Product::find($item->product_id);
                $itemDetail['item_id'] = $item->id;
                $itemDetail['product_id'] = $item->product_id;
                $itemDetail['product_name'] = $product->name;
                $itemDetail['product_type'] = $product->type;
                $itemDetail['product_breed'] = Breed::find($product->breed_id)->name;
                $itemDetail['img_path'] = route('serveImage', ['size' => 'small', 'filename' => Image::find($product->primary_img_id)->name]);
                $itemDetail['breeder'] = Breeder::find($product->breeder_id)->users()->first()->name;
                $itemDetail['token'] = csrf_token();
                array_push($items,$itemDetail);
            }

            $itemsCollection = collect($items);
            return $itemsCollection->toJson();
        }
        else {
            $customer = $this->user->userable;
            $swineCartItems = $customer->swineCartItems()->where('if_rated',0)->get();
            $products = [];

            foreach ($swineCartItems as $item) {
                $itemDetail = [];
                $product = Product::find($item->product_id);
                $reviews = Breeder::find($product->breeder_id)->reviews()->get();

                $itemDetail['request_status'] = $item->if_requested;
                $itemDetail['request_quantity'] = $item->quantity;
                $itemDetail['status'] = ($item->reservation_id) ? ProductReservation::find($item->reservation_id)->order_status : $product->status;
                $itemDetail['item_id'] = $item->id;
                $itemDetail['customer_id'] = $customer->id;
                $itemDetail['breeder_id'] = $product->breeder_id;
                $itemDetail['breeder'] = Breeder::find($product->breeder_id)->users()->first()->name;
                $itemDetail['product_id'] = $item->product_id;
                $itemDetail['product_province'] = FarmAddress::find($product->farm_from_id)->province;
                $itemDetail['product_name'] = $product->name;
                $itemDetail['product_type'] = $product->type;
                $itemDetail['product_quantity'] = $product->quantity;
                $itemDetail['product_breed'] = $this->transformBreedSyntax(Breed::find($product->breed_id)->name);
                $itemDetail['product_birthdate'] = $this->transformDateSyntax($product->birthdate);
                $itemDetail['product_age'] = $this->computeAge($product->birthdate);
                $itemDetail['product_adg'] = $product->adg;
                $itemDetail['product_fcr'] = $product->fcr;
                $itemDetail['product_backfat_thickness'] = $product->backfat_thickness;
                $itemDetail['other_details'] = $product->other_details;
                $itemDetail['avg_delivery'] = $reviews->avg('rating_delivery');
                $itemDetail['avg_transaction'] = $reviews->avg('rating_transaction');
                $itemDetail['avg_productQuality'] = $reviews->avg('rating_productQuality');
                $itemDetail['date_needed'] = ($item->date_needed == '0000-00-00') ? '' : $this->transformDateSyntax($item->date_needed);
                $itemDetail['special_request'] = $item->special_request;
                $itemDetail['img_path'] = route('serveImage', ['size' => 'medium', 'filename' => Image::find($product->primary_img_id)->name]);
                $itemDetail['expiration_date'] = (ProductReservation::find($item->reservation_id)->expiration_date) ?? '';
                $itemDetail['status_transactions'] = [
                    "requested" => ($item->transactionLogs()->where('status', 'requested')->latest()->first()->created_at) ?? '',
                    "reserved" => ($item->transactionLogs()->where('status', 'reserved')->latest()->first()->created_at) ?? '',
                    "on_delivery" => ($item->transactionLogs()->where('status', 'on_delivery')->first()->created_at) ?? '',
                    "paid" => ($item->transactionLogs()->where('status', 'paid')->first()->created_at) ?? '',
                    "sold" => ($item->transactionLogs()->where('status', 'sold')->first()->created_at) ?? ''
                ];

                array_push($products,(object) $itemDetail);
            }

            $products = collect($products);
            $token = csrf_token();
            $customerId = $customer->id;
            return view('user.customer.swineCart', compact('products', 'token', 'customerId'));
        }
    }

    /**
     * Get Transaction History of
     * AJAX
     *
     * @param  Request $request
     */
    public function getTransactionHistory(Request $request){
        if($request->ajax()){
            $history = Customer::find($request->customerId)->transactionLogs;

            $restructuredHistory = $history->groupBy('product_id')->map(function($item, $key){
                $restructuredItem = [];
                $product = Product::find($key);
                $reviews = $product->breeder->reviews;

                $restructuredItem['showFullLogs'] = false;
                $restructuredItem['logs'] = $item->toArray();
                $restructuredItem['product_details'] = [
                    "quantity" => (SwineCartItem::find($restructuredItem['logs'][0]['swineCart_id'])->quantity) ?? '',
                    "name" => $product->name,
                    "type" => $product->type,
                    "s_img_path" => route('serveImage', ['size' => 'small', 'filename' => Image::find($product->primary_img_id)->name]),
                    "l_img_path" => route('serveImage', ['size' => 'large', 'filename' => Image::find($product->primary_img_id)->name]),
                    "breed" => $this->transformBreedSyntax(Breed::find($product->breed_id)->name),
                    "breeder_name" => Breeder::find($product->breeder_id)->users()->first()->name,
                    "farm_from" => FarmAddress::find($product->farm_from_id)->province,
                    "birthdate" => $product->birthdate,
                    "adg" => $product->adg,
                    "fcr" => $product->fcr,
                    "bft" => $product->backfat_thickness,
                    "other_details" => $product->other_details,
                    "avg_delivery" => ($reviews->avg('rating_delivery')) ? $reviews->avg('rating_delivery') : 0,
                    "avg_transaction" => ($reviews->avg('rating_transaction')) ? $reviews->avg('rating_transaction') : 0,
                    "avg_productQuality" => ($reviews->avg('rating_productQuality')) ? $reviews->avg('rating_productQuality') : 0
                ];

                return $restructuredItem;
            });

            return collect($restructuredHistory)->toJson();
        }
    }

    /**
     * Get number of items in the Swine Cart
     * AJAX
     *
     * @param  Request $request
     * @return Integer
     */
    public function getSwineCartQuantity(Request $request)
    {
        if($request->ajax()){
            $customer = $this->user->userable;
            return $customer->swineCartItems()->where('if_requested',0)->count();
        }
    }

}
