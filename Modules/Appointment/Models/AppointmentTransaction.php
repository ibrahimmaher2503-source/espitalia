<?php

namespace Modules\Appointment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Commission\Models\CommissionEarning;
use Modules\Tip\Models\TipEarning;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AppointmentTransaction extends BaseModel
{
    use HasFactory;
    use SoftDeletes,LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }

    protected $fillable = ['appointment_id', 'external_transaction_id', 'transaction_type', 'payment_method', 'payment_gateway', 'discount_type','discount_value','discount_amount','discount_amount', 'tip_amount', 'tax_percentage', 'payment_status','total_amount', 'currency', 'refund_amount', 'refund_status', 'paid_at', 'inclusive_tax','inclusive_tax_price'];

    protected $casts = [
        'tax_percentage' => 'array',
        'discount_percentage' => 'decimal:2',
        'appointment_id' => 'integer',
        'discount_value'=> 'decimal:2',
        'discount_amount'=> 'decimal:2',
        'tip_amount'=> 'decimal:2',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'created_by'=> 'integer',
        'updated_by'=> 'integer',
        'deleted_by' => 'integer',
    ];

    protected static function newFactory()
    {
        return \Modules\Booking\Database\factories\BookingTransactionFactory::new();
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class)->with('services');
    }

    public function commissions()
    {
        return $this->hasMany(CommissionEarning::class, 'employee_id');
    }

    public function tipEarnings()
    {
        return $this->hasMany(TipEarning::class, 'tippable_id', 'booking_id');
    }

    public function commissionEarnings()
    {
        return $this->hasMany(CommissionEarning::class, 'commissionable_id', 'booking_id');
    }
}
