<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingHistory;
use App\Models\Setting;

class BillingController extends Controller
{
    public function index()
    {
        $billing = [
            'status' => Setting::get('billing_status', 'inactive'),
            'amount' => Setting::get('billing_amount'),
            'due_date' => Setting::get('billing_due_date'),
            'invoice_url' => Setting::get('billing_invoice_url'),
            'billing_type' => Setting::get('billing_type'),
            'subscription_status' => Setting::get('billing_subscription_status', 'inactive'),
        ];

        $history = BillingHistory::orderBy('due_date', 'desc')->get();

        return view('admin.billing.index', compact('billing', 'history'));
    }
}
