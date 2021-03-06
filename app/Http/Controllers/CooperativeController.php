<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Cooperative;
use App\Models\LoanProduct;
use Illuminate\Http\Request;
use App\Http\Requests\Loan\EditLoanProductRequest;
use App\Http\Requests\Loan\CreateLoanProductRequest;
use App\Http\Requests\Cooperative\LoginCooperativeRequest;
use App\Http\Requests\Cooperative\CreateCooperativeRequest;

class CooperativeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admins', ['except' => ['loginCooperative']]);
    }

    public function createCooperative(CreateCooperativeRequest $request)
    {
        $coop_authid = date("Ymdhis") . rand(111111, 999999);
        $field = [
            'coop_id' => $request->coop_id,
            'coop_name' => $request->coop_name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'coop_type' => $request->coop_type,
            'affiliate_organization' => $request->affiliate_organization,
            'address' => $request->address,
            'country' => $request->country,
            'city' => $request->city,
            'state' => $request->state,
            'coop_authid' => $coop_authid,
            'interest_rate' => $request->interest_rate,
            'minimum_tenor' => $request->minimum_tenor,
            'maximum_tenor' => $request->maximum_tenor,
            'wallet_balance' => $request->wallet_balance,
            'commission_balance' => $request->commission_balance
        ];

        $create = Cooperative::create($field);
        if($create){
            return response()->json(['status'=>'success','message'=>'Cooperative created successfully', 'id'=>$create->id],200);
        }


    }


    public function loginCooperative(LoginCooperativeRequest $request)
    {
        $member = Member::where('email', $request->email)
            ->where('password', md5($request->password))->where('admin_status',"1")->first();
        if (!$member) {
            return response(['status'=>'error','message'=>'Invalid details Provided'], 400);
        }

        $token = auth('members')->setTTL('1440')->login($member);
        return $this->createNewToken($token);
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('members')->factory()->getTTL(),
            // 'user' => auth('members')->user(),
            'status' => 'success',
            "message" => "Successful login",
        ], 200);
    }

    //create cooperative loan product
    public function createLoanProduct(CreateLoanProductRequest $request)
    {
        $field = [
            'coop_id' => $request->coop_id,
            'product_name' => $request->product_name,
            'interest_rate' => $request->interest_rate,
            'maximum_amount' => $request->maximum_amount,
            'minimum_amount' => $request->minimum_amount,
            'product_id' => $request->product_id,
            'balance' => $request->balance
        ];

        $create = LoanProduct::create($field);
        if($create){
            return response()->json(['status'=>'success','message'=>'Loan Product Created Successfully'],200);
        }
    }

    public function editLoanProduct(EditLoanProductRequest $request)
    {
        if (!$loanproduct = LoanProduct::where('id', $request->id)->first()) {
            return response()->json(['status' => 'error', 'message' => "Loan Product Not Found"], 400);
        }

        $field = [
            'product_name' => $request->product_name,
            'interest_rate' => $request->interest_rate,
            'maximum_amount' => $request->maximum_amount,
            'minimum_amount' => $request->minimum_amount,
            'product_id' => $request->product_id,
            'balance' => $request->balance
        ];

        $update = $loanproduct->update($field);
        if($update){
            return response()->json(['status'=>'success','message'=>'Loan Product Updated Successfully'],200);
        }
    }
}
