<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice',
        'customer_id',
        'user_id',
        'warehouse_id',
        'grand_total',
        'discount',
        'discount_type',
        'discount_percent',
        'event_discount',
        'event_discount_type',
        'event_discount_percent',
        'shipping_cost',
        'other_cost',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'sender_name',
        'sender_phone',
        'payment_type',
        'shipping_type',
        'status',
        'approval_status',
        'rejection_notes',
        'is_preorder',
        'preorder_status',
        'estimated_ready_date',
        'paid_amount',
        'finalized_at',
        'notes',
    ];

    protected $casts = [
        'is_preorder' => 'boolean',
        'finalized_at' => 'datetime',
        'estimated_ready_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function profits()
    {
        return $this->hasMany(Profit::class);
    }

    public function stockMutations()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function approvals()
    {
        return $this->hasMany(SaleApproval::class);
    }

    public function latestApproval()
    {
        return $this->hasOne(SaleApproval::class)->latestOfMany();
    }

    public function financeApproval()
    {
        return $this->hasOne(SaleApproval::class)->where('type', 'finance')->latestOfMany();
    }

    public function warehouseApproval()
    {
        return $this->hasOne(SaleApproval::class)->where('type', 'warehouse')->latestOfMany();
    }

    public function journal()
    {
        return $this->morphOne(Journal::class, 'source');
    }

    public function payments()
    {
        return $this->hasMany(ReceivablePayment::class);
    }

    /**
     * Check if sale can be edited by sales team
     */
    public function canBeEditedBySales(): bool
    {
        return in_array($this->approval_status, ['draft', 'rejected']);
    }

    /**
     * Check if sale is pending finance approval
     */
    public function isPendingFinance(): bool
    {
        return $this->approval_status === 'pending_finance';
    }

    /**
     * Check if sale is pending warehouse approval
     */
    public function isPendingWarehouse(): bool
    {
        return $this->approval_status === 'pending_warehouse';
    }

    /**
     * Check if sale is completed (all approvals done)
     */
    public function isCompleted(): bool
    {
        return $this->approval_status === 'completed';
    }

    /**
     * Check if sale was rejected
     */
    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }
}
