<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\SwineCartItem;
use App\Models\TransactionLog;

class AddToTransactionLog implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $transactionDetails;

    /**
     * Create a new job instance.
     *
     * @param  Array    $transactionDetails
     * @return void
     */
    public function __construct($transactionDetails)
    {
        $this->transactionDetails = $transactionDetails;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $swineCartItem = SwineCartItem::findOrFail($this->transactionDetails['swineCart_id']);

        $transactionLog = new TransactionLog;
        $transactionLog->customer_id = $this->transactionDetails['customer_id'];
        $transactionLog->breeder_id = $this->transactionDetails['breeder_id'];
        $transactionLog->product_id = $this->transactionDetails['product_id'];
        $transactionLog->status = $this->transactionDetails['status'];
        $transactionLog->created_at = $this->transactionDetails['created_at'];

        $swineCartItem->transactionLogs()->save($transactionLog);
    }
}
