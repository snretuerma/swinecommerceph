<?php

namespace App\Models;

use App\Models\Breeder;
use App\Models\Image;
use App\Models\Video;
use App\Models\TransactionLog;
use App\Models\ProductReservation;
use App\Observers\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, Searchable;

    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['farm_from_id',
        'primary_img_id',
        'name',
        'type',
        'age',
        'breed_id',
        'price',
        'quantity',
        'adg',
        'fcr',
        'backfat_thickness',
        'other_details',
        'status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'];

    /**
     * Get the breeder that owns this product
     */
    public function breeder()
    {
        return $this->belongsTo(Breeder::class);
    }

    /**
     * Get all of the Product's images
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Get all of the Product's videos
     */
    public function videos()
    {
        return $this->morphMany(Video::class, 'videoable');
    }

    /**
     * Get all of the Product's reservations
     */
    public function reservations()
    {
        return $this->hasMany(ProductReservation::class);
    }

    /**
     * Get the respective Transaction Log of the Product
     */
    public function transactionLog()
    {
        return $this->hasOne(TransactionLog::class);
    }
}
