<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CurrencyService;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'price', 'description'];

    public function getIDRPrice()
    {
        return (new CurrencyService())->convert($this->price, 'usd', 'idr');
    }
}