<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\services\Helper;
use App\Models\Cooperative;
use App\Models\LoanSuretee;
use App\Models\LoanApproval;
use Illuminate\Http\Request;
use App\services\VerificationServices;
use App\Http\Requests\Loan\LoanApplyRequest;

class LoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:members', ['except' => ['']]);
    }

    public function applyLoan(LoanApplyRequest $request)
    {
        // $member = Auth('members')->user();
        // $member_id = $member->member_id;
        $amount = (int) $request->amount;
        $duration = (int) $request->duration;
        $coop_id = $request->coop_id;
        $payment_reference = date('YmdHisu') . Helper::randomString(10);
        $loanid = "LN" . date("Ymdhis") . rand(1111, 9999);

        $loan_start_date = Date('Y-m-d');

        $real_duration = $duration + $request->repayment_start;
        $loan_end_date = Date('Y-m-t', strtotime("+$real_duration months"));

        if (!$coop = Cooperative::where('coop_id', $coop_id)->first()) {
            return response()->json(['status' => 'error', 'message' => 'Cooperative Not Found'], 400);
        }

        $no_of_suretees = count($request->suretees);
        if ($no_of_suretees != $coop->suretees) {
            return response()->json(['status' => 'error', 'message' => "Number Of Surety required is " . $coop->suretee], 400);
        }

        $fields = [];

        foreach ($request->suretees as $value) {
            $fields[] = [
                'loanid' => $loanid,
                'member_id' => $value,
                'created_at' => $loan_start_date,
                'updated_at' => $loan_start_date
            ];
        }
        LoanSuretee::insert($fields);

        $approvals = $coop->approvals;

        $interest_rate = $coop->interest_rate;

        $monthly_interest = $interest_rate * $amount;

        $monthly_repayment = ($interest_rate * $amount * $duration) + $amount;

        $monthly_repayment = $monthly_repayment / $duration;
        $fees = 0;

        for($i= 1; $i <= $approvals; $i++){
            $approval_field = [
                'loanid' => $loanid,
                'level' => $i
            ];
            LoanApproval::create($approval_field);
        }

        $loan_field = [
            'member_id' => $request->member_id,
            'coop_id' => $coop_id,
            'loanid' => $loanid,
            'loan_application_id' => $request->loan_application_id,
            'amount' => $amount,
            'monthly_interest' => $monthly_interest,
            'duration' => $duration,
            'repayment' => $monthly_repayment,
            'fees' => $fees,
            'loan_start_date' => $loan_start_date,
            'loan_end_date' => $loan_end_date,
            'required_approvals' => $approvals,
            'bankcode' => $request->bankcode,
            'account_number' => $request->account_number,
            'payment_reference' => $payment_reference

        ];

        $create = Loan::create($loan_field);
        if ($create) {
            return response()->json(['status' => 'success', 'message' => "Successful", 'id' => $create->id], 200);
        }

    }

    

}
