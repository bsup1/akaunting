<?php

namespace App\Models\Contractor;

use App\Models\Model;
use App\Traits\Media;
use Bkwld\Cloner\Cloneable;
use Illuminate\Notifications\Notifiable;
use Sofa\Eloquence\Eloquence;

class Contractor extends Model
{
    use Cloneable, Eloquence, Notifiable, Media;

    protected $table = 'contractors';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['company_id', 'user_id', 'name', 'email', 'tax_number', 'phone', 'address', 'website', 'currency_code', 'enabled'];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = ['name', 'email', 'phone', 'enabled'];

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchableColumns = [
        'name'    => 10,
        'email'   => 5,
        'phone'   => 2,
        'website' => 2,
        'address' => 1,
    ];

    public function invoices()
    {
        return $this->hasMany('App\Models\Income\Invoice', 'customer_id');
    }

    public function revenues()
    {
        return $this->hasMany('App\Models\Income\Revenue', 'customer_id');
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Setting\Currency', 'currency_code', 'code');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Auth\User', 'user_id', 'id');
    }

    public function bills()
    {
        return $this->hasMany('App\Models\Expense\Bill', 'vendor_id');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Expense\Payment', 'vendor_id');
    }

    /**
     * Get the current balance.
     *
     * @return string
     */
    public function getLogoAttribute($value)
    {
        if (!empty($value) && !$this->hasMedia('logo')) {
            return $value;
        } elseif (!$this->hasMedia('logo')) {
            return false;
        }

        return $this->getMedia('logo')->last();
    }

    public function onCloning($src, $child = null)
    {
        $this->user_id = null;
    }
}
