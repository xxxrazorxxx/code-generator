<?php

namespace App\Core\Catalog\Entities;

[% use_command_placeholder %]

class Product extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'products';

    /**
    * The database primary key value.
    *
    * @var array
    */
    protected $primaryKey = ['id'];


    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Get the reviewResult for this model.
     *
     * @return [% relation_return_type %]
     */
    public function reviewResult()
    {
        return $this->hasOne('App\Addons\VendorDataModeration\Entities\ReviewResult', 'product_id', 'id');
    }

    /**
     * Get the parentProduct for this model.
     *
     * @return [% relation_return_type %]
     */
    public function parentProduct()
    {
        return $this->hasOne('App\Core\Catalog\Entities\Product', 'id', 'parent_product_id');
    }

    /**
     * Get the company for this model.
     *
     * @return [% relation_return_type %]
     */
    public function company()
    {
        return $this->belongsTo('App\Core\Company\Entities\Company');
    }


}
