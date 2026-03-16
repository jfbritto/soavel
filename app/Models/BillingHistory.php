<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingHistory extends Model
{
    protected $table = 'billing_history';

    protected $fillable = [
        'asaas_payment_id',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'billing_type',
        'invoice_url',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'date',
        'amount' => 'decimal:2',
    ];
}
