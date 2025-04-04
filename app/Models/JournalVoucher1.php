<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalVoucher1 extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jv1';

    protected $fillable = ['debit_acc_id', 'credit_acc_id', 'amount', 'date', 'narration', 'ref_doc_id', 'ref_doc_code'];

    public function debitAccount()
    {
        return $this->belongsTo(ChartOfAccounts::class, 'debit_acc_id');
    }

    public function creditAccount()
    {
        return $this->belongsTo(ChartOfAccounts::class, 'credit_acc_id');
    }

    public function voucherDetails()
    {
        return $this->hasMany(PurFGPOVoucherDetails::class, 'voucher_id');
    }
}
