<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\LoanApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Loan\ApproveLoanRequest;

class LoanApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:members', ['except' => []]);
    }

    public function approveLoan(ApproveLoanRequest $request)
    {
        $admin = Auth('members')->user();
        if($admin->admin_status != "1"){
            return response(['status'=>'error','message'=>'You do not permission to access this resource'], 400);
        }
        if(!$loan = Loan::where('id',$request->id)->first()){
            return response(['status'=>'error','message'=>'Loan Not Found'], 400);
        }
        $no_of_approvals = $loan->required_approvals;
        if($no_of_approvals == "0"){
            return response(['status'=>'error','message'=>'All Approvals For This Loan Has Been Done'], 400);
        }

        if(LoanApproval::where('loanid',$loan->loanid)->where('authid',$admin->member_id)->first()){
            return response(['status'=>'error','message'=>'Loan Has Been Approved Already'], 400);
        }


        if(!$available_approval = LoanApproval::where('loanid',$loan->loanid)->where('authid',null)->first()){
            return response(['status'=>'error','message'=>'All Approvals For This Loan Has Been Done'], 400);
        }
        $new_field = [
            'authid' => $admin->member_id
        ];
        $available_approval->update($new_field);

        $available_approvals = LoanApproval::where('loanid',$loan->loanid)->where('authid',null)->get();
        if($available_approvals->isEmpty()){
            return response(['status'=>'success','message'=>'Loan Approved Successfully'], 200);
        }else{
            $loan->status = "1";
            $loan->save();
            return response(['status'=>'success','message'=>'Loan Approved Successfully'], 200);
        }


    }
}
