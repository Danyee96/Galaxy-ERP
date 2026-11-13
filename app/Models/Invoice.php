<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = ['invoice_name','invoice_number','total_count','created_by'];

    public static function nextNumber(): int
    {
        // Imported invoices may have newer IDs but lower invoice numbers.
        $highest = static::query()->selectRaw('MAX(CAST(invoice_number AS DECIMAL(20, 0))) as highest')->value('highest');
        return $highest === null ? 1001 : (int) $highest + 1;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetails::class, 'invoice_id');
    }
}
