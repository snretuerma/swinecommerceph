<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use App\Http\Requests;
use App\Models\Customer;
use App\Models\Breeder;
use App\Models\Breed;
use App\Models\Image;
use App\Models\SwineCartItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\TransactionLog;

use Auth;

class SwineCartController extends Controller
{
    protected $user;

    /**
     * Create new CustomerController instance
     */
    public function __construct()
    {
        $this->middleware('role:customer');
        $this->middleware('updateProfile:customer');
        $this->user = Auth::user();
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
            $checkProduct = $swineCartItems->where('product_id',$request->productId)->get();

            // --------- WEBSOCKET SEND DATA -------------
            // $product = Product::find($request->productId);
            // $breeder = $product->breeder;
            // $topic = $breeder->users()->first()->name;
            // $data = $productsDashboard->getSoldProducts($breeder);
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
                $item->quantity = 1;

                $swineCartItems->save($item);

                $returnArray = ['success', Product::find($request->productId)->name, $customer->swineCartItems()->where('if_requested',0)->count()];
                return $returnArray;
            }
        }
    }

    /**
     * Record activity to Logs
     * AJAX
     *
     * @param  Request $request
     */
    public function record(Request $request){
        if($request->ajax()){
            $history = Customer::find($request->customerId)->transactionLogs();
            $log = new TransactionLog;
            $log->product_id = $request->productId;
            $log->breeder_id = $request->breederId;
            $log->status = $request->status;
            $history->save($log);
        }
    }

    /**
     * Rates breeder from Swine Cart
     * AJAX
     *
     * @param  Request $request
     */
    public function rate(Request $request){
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
        }
        return $request->productId;
    }

    /**
     * Requests item from Swine Cart
     * AJAX
     *
     * @param  Request $request
     */
    public function requestSwineCart(Request $request)
    {
        if ($request->ajax()) {
            $customer = $this->user->userable;
            $swineCartItems = $customer->swineCartItems();
            $requested = $swineCartItems->find($request->itemId);
            $requested->if_requested = 1;
            $product = Product::find($request->productId);
            $product->status = "requested";
            $product->save();
            $requested->save();
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
                $itemDetail['img_path'] = '/images/product/'.Image::find($product->primary_img_id)->name;
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
            $log = $customer->transactionLogs()->get();
            $history = [];

            foreach ($swineCartItems as $item) {
                $itemDetail = [];
                $product = Product::find($item->product_id);
                $reviews = Breeder::find($product->breeder_id)->reviews()->get();

                // Check if product is reserved to another customer
                // Then skip to the next product
                if($product->customer_id && $product->customer_id != $customer->id) continue;
                $itemDetail['request_status'] = $item->if_requested;
                $itemDetail['status'] = $product->status;
                $itemDetail['item_id'] = $item->id;
                $itemDetail['customer_id'] = $customer->id;
                $itemDetail['breeder_id'] = $product->breeder_id;
                $itemDetail['product_id'] = $item->product_id;
                $itemDetail['product_name'] = $product->name;
                $itemDetail['product_type'] = $product->type;
                $itemDetail['product_quantity'] = $product->quantity;
                $itemDetail['product_breed'] = $this->transformBreedSyntax(Breed::find($product->breed_id)->name);
                $itemDetail['product_age'] = $product->age;
                $itemDetail['product_adg'] = $product->adg;
                $itemDetail['product_fcr'] = $product->fcr;
                $itemDetail['other_details'] = $product->other_details;
                $itemDetail['product_backfat_thickness'] = $product->backfat_thickness;
                $itemDetail['avg_delivery'] = $reviews->avg('rating_delivery');
                $itemDetail['avg_transaction'] = $reviews->avg('rating_transaction');
                $itemDetail['avg_productQuality'] = $reviews->avg('rating_productQuality');
                $itemDetail['img_path'] = '/images/product/'.Image::find($product->primary_img_id)->name;
                $itemDetail['breeder'] = Breeder::find($product->breeder_id)->users()->first()->name;
                $itemDetail['token'] = csrf_token();
                array_push($products,(object) $itemDetail);
            }

            foreach ($log as $item) {
                $itemDetail = [];
                $product = Product::find($item->product_id);
                $reviews = Breeder::find($product->breeder_id)->reviews()->get();
                $itemDetail['product_name'] = $product->name;
                $itemDetail['product_type'] = $product->type;
                $itemDetail['product_quantity'] = $product->quantity;
                $itemDetail['img_path'] = '/images/product/'.Image::find($product->primary_img_id)->name;
                $itemDetail['breeder'] = Breeder::find($product->breeder_id)->users()->first()->name;
                $itemDetail['product_breed'] = $this->transformBreedSyntax(Breed::find($product->breed_id)->name);
                $itemDetail['product_age'] = $product->age;
                $itemDetail['product_adg'] = $product->adg;
                $itemDetail['product_fcr'] = $product->fcr;
                $itemDetail['other_details'] = $product->other_details;
                $itemDetail['product_backfat_thickness'] = $product->backfat_thickness;
                $itemDetail['avg_delivery'] = $reviews->avg('rating_delivery');
                $itemDetail['avg_transaction'] = $reviews->avg('rating_transaction');
                $itemDetail['avg_productQuality'] = $reviews->avg('rating_productQuality');
                $itemDetail['breeder'] = Breeder::find($product->breeder_id)->users()->first()->name;
                $dateArray = date_parse($item->created_at->toDateTimeString());
                $itemDetail['date'] = date('j M Y (D) g:iA', mktime( $dateArray['hour'], $dateArray['minute'], $dateArray['second'], $dateArray['month'], $dateArray['day'], $dateArray['year']) );
                $itemDetail['token'] = csrf_token();
                array_push($history,(object) $itemDetail);
            }

            return view('user.customer.swineCart', compact('products', 'history'));
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

    /**
    * Parse $breed if it contains '+' (ex. landrace+duroc)
    * to "Landrace x Duroc"
    *
    * @param  String   $breed
    * @return String
    */
    private function transformBreedSyntax($breed)
    {
       if(str_contains($breed,'+')){
           $part = explode("+", $breed);
           $breed = ucfirst($part[0])." x ".ucfirst($part[1]);
           return $breed;
       }
       return ucfirst($breed);
    }
}
