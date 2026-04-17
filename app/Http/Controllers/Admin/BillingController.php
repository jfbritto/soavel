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

        $history = BillingHistory::where('environment', 'production')
            ->orderBy('due_date', 'desc')
            ->get();

        if ($billing['due_date']) {
            $dueDate = \Carbon\Carbon::parse($billing['due_date'])->toDateString();
            $paidRecord = $history->first(function ($item) use ($dueDate) {
                return $item->due_date->toDateString() === $dueDate
                    && in_array(strtolower((string) $item->status), ['confirmed', 'received']);
            });
            if ($paidRecord) {
                $billing['status'] = 'received';
                $billing['billing_type'] = $paidRecord->billing_type ?: $billing['billing_type'];
            }
        }

        return view('admin.billing.index', compact('billing', 'history'));
    }
}
