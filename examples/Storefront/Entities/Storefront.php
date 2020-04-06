<?php

namespace App\Core\Storefront\Entities;

[% use_command_placeholder %]

class Storefront extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'storefronts';

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
     * Get the companies for this model.
     *
     * @return [% relation_return_type %]
     */
    public function companies()
    {
        return $this->belongsToMany('App\Core\Company\Entities\Company', 'storefront_id', 'id');
    }


}
