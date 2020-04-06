<?php

namespace App\Core\Company\Entities;

[% use_command_placeholder %]

class Company extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'Company';

    /**
    * The database primary key value.
    *
    * @var array
    */
    protected $primaryKey = [];


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
     * Get the products for this model.
     *
     * @return [% relation_return_type %]
     */
    public function products()
    {
        return $this->hasMany('App\Core\Catalog\Entities\Product', 'company_id', 'id');
    }

    /**
     * Get the storefronts for this model.
     *
     * @return [% relation_return_type %]
     */
    public function storefronts()
    {
        return $this->belongsToMany('App\Core\Storefront\Entities\Storefront', 'company_id', 'id');
    }


}
