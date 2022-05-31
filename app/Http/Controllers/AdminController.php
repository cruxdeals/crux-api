<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Member;
use App\Models\Cooperative;
use App\Models\LoanProduct;
use Illuminate\Http\Request;
use App\services\MonnifyServices;
use App\services\VerificationServices;
use App\Http\Requests\Loan\OneLoanRequest;
use App\Http\Requests\Loan\AllLoansRequest;
use App\Http\Requests\Loan\CancelLoanRequest;
use App\Http\Requests\Loan\DisburseLoanRequest;
use App\Http\Requests\Member\CreateMemberRequest;
use App\Http\Requests\Loan\DisbursementCheckRequest;
use App\Http\Requests\Cooperative\OneCooperativeRequest;
use App\Http\Requests\Cooperative\ListCooperativeMembersRequest;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admins', ['except' => []]);
    }

    public function createMember(CreateMemberRequest $request)
    {
        $bvn = $request->bvn;
        $bvn_data = VerificationServices::resolveBVN($bvn);
        $bvn_data = $bvn_data->getOriginalContent();
        if($bvn_data['status'] != 'success'){
            return response()->json(['status'=>'error','message'=>'Error verifying BVN'],400);
        }

        if($request->admin_status == "0"){
            $admin_status = "0";
        }else{
            $admin_status = "1";
        }

        $firstname = $bvn_data['first_name'];
        $lastname = $bvn_data['last_name'];
        $dob = $bvn_data['formatted_dob'];
        $telephone = $bvn_data['mobile'];
        $bvn_image = $bvn_data['image'];

        $member_id = date("Ymdhis") . rand(111111, 999999);
        $field = [
            'member_id' => $member_id,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $request->email,
            'dob' => $dob,
            'telephone' => $telephone,
            'bvn_image' => $bvn_image,
            'bvn' => $request->bvn,
            'nin' => $request->nin,
            'coop_id' => $request->coop_id,
            'password' => md5($request->password),
            'admin_status' => $admin_status
        ];

        $create = Member::create($field);
        if($create){
            return response()->json(['status'=>'success','message'=>'Member Created Successfully','id'=>$create->id],200);
        }
    }

    public function disbursementCheck(Request $request)
    {

        if (!$loan = Loan::where('id', $request->id)->first()) {
            return response()->json(['status' => 'error', 'message' => "Loan Not Found"], 400);
        }

        $account_number = $loan->account_number;
        $bankcode = $loan->bankcode;

        $data = VerificationServices::getBVNFromBankDetails($account_number, $bankcode);
        if (!$data) {
            return response(['status' => 'error', 'message' => 'Error verifying account details'], 400);
        }

        $bvn = $data['bvn'];

        return $data;
    }

    public function disbursementCheckAuthorization(DisbursementCheckRequest $request)
    {
        if (!$loan = Loan::where('id', $request->id)->first()) {
            return response()->json(['status' => 'error', 'message' => "Loan Not Found"], 400);
        }
        $loan->disbursement_check = "1";
        $loan->save();
        return response()->json(['status' => 'success', 'message' => "Loan Authorized For Disbursement"], 200);
    }

    public function disburseLoan(DisburseLoanRequest $request)
    {
        if (!$loan = Loan::where('id', $request->id)->first()) {
            return response()->json(['status' => 'error', 'message' => "Loan Not Found"], 400);
        }

        if ($loan->status != "1") {
            return response()->json(['status' => 'error', 'message' => "Loan is not pending disbursement"], 400);
        }

        if ($loan->disbursement_check != "1") {
            return response()->json(['status' => 'error', 'message' => "Loan is not authorized for disbursement"], 400);
        }

        if (!$product = LoanProduct::where('coop_id', $loan->coop_id)->where('product_id', $loan->loan_product_id)->first()) {
            return response()->json(['status' => 'error', 'message' => "Loan Product Not Found"], 400);
        }

        if ($product->balance < $loan->amount) {
            return response()->json(['status' => 'error', 'message' => "Insufficient Funds"], 400);
        }

        $bankcode = $loan->bankcode;
        $account_number = $loan->account_number;
        $payment_reference = $loan->payment_reference;

        if (!$coop = Cooperative::where('coop_id', $loan->coop_id)->first()) {
            return response()->json(['status' => 'error', 'message' => "Cooperative Not Found"], 400);
        }

        $commission = ($coop->commission_percent * $loan->amount) + $coop->commission_balance;
        $coop->commission_balance = $commission;
        $coop->save();

        //make payment

        if (!$loan->payment_reference) {
            $payment_reference = "LOAN" . $loan->loanid . "_" . date("YmdHis");
            $loan->payment_reference = $payment_reference;
            $loan->save();
        }

        $amount = filter_var($loan->amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $narration = $loan->loanid . "- CRUX Disbursed Loan";
        $giroreference = MonnifyServices::disburse($bankcode, $account_number, $amount, $narration, $loan->payment_reference);

        if ($giroreference['status'] == "error") {
            $response = "Payment Error with Monnify, please check payment reference for transaction status!";
            $loan->monnify_response = $response;
            $loan->save();
            return response()->json(['status' => 'error', 'message' => 'Error disbursing money with monify'], 400);
        } else {
            $response = "Payment Completed with Monnify!";
            $loan->status = "2";
            $loan->monnify_response = $response;
            $loan->save();

            return response()->json(['status' => 'success', 'message' => 'Loan Disbursed Successfully'], 200);
        }

    }

    public function oneLoan(OneLoanRequest $request)
    {
        if (!$loan = Loan::where('id', $request->id)->with('member')->first()) {
            return response()->json(['status' => 'error', 'message' => "Loan Not Found"], 400);
        }

        return response()->json(['status' => 'success', 'loan' => $loan], 200);
    }

    public function allLoans(AllLoansRequest $request)
    {
        $loans = Loan::query();

        if ($request->search_text) {
            $search_text = $request->search_text;
            $loans = $loans->where(function ($query) use ($search_text) {
                $query->where('loanid', $search_text)
                    ->orWhereHas('member', function ($query) use ($search_text) {
                        $query->where('firstname', 'LIKE', "%{$search_text}%")
                            ->orWhere('lastname', 'LIKE', "%{$search_text}%")
                            ->orWhere('email', 'LIKE', "%{$search_text}%")
                            ->orWhere('telephone', 'LIKE', "%{$search_text}%");
                    });
            });
        }

        $loans = $loans->where('status',$request->status)->where('loan_product_id',$request->product_id)->orderBy('id','DESC')->with('member')->paginate($request->page_size);

        // $loans = $loans->where('status',$request->status)->orderBy('id','DESC')->with(['member'=> function($query){
        //     $query->select('firstname','lastname','email','telephone');
        // }])->paginate($request->page_size);
        return response()->json(['status'=>'success','loans'=>$loans],200);

    }

    public function cancelLoan(CancelLoanRequest $request)
    {
        if (!$loan = Loan::where('id', $request->id)->with('member')->first()) {
            return response()->json(['status' => 'error', 'message' => "Loan Not Found"], 400);
        }
        $loan->status = "3";
        $loan->save();
        return response()->json(['status'=>'success', 'message'=>'Loan Cancelled Successfully'],400);
    }

    public function viewOneCooperative(OneCooperativeRequest $request)
    {
        if (!$coop = Cooperative::where('id', $request->id)->first()) {
            return response()->json(['status' => 'error', 'message' => "Cooperative Not Found"], 400);
        }

        return response()->json(['status'=>'success','cooperative'=>$coop],200);
    }

    public function listCooperativeMembers(ListCooperativeMembersRequest $request)
    {
        $members = Member::query();
        if($request->search_text){
            $search_text = $request->search_text;
            $members = $members->where(function($query) use ($search_text){
                $query->where('member_id',$search_text)
                ->orWhere('firstname','LIKE',"%{$search_text}%")
                ->orWhere('lastname','LIKE', "%{$search_text}%")
                ->orWhere('email',$search_text)
                ->orWhere('telephone', $search_text)
                ->orWhere('bvn',$search_text)
                ->orWhere('nin',$search_text);
            });
        }

        $members = $members->where('coop_id',$request->coop_id)->orderBy('id','DESC')->paginate($request->page_size);

        return response()->json(['status'=>'success','members'=>$members],200);

    }
}
