<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['title','first_name','middle_name','last_name','suffix','display_name','company_name','email','phone','mobile','fax','other','website','street_address_one','street_address_two','city','province','postal_code','country','notes','attachment','payment_method','terms','delivery_option','language','taxes','opening_balance','date'];
}
