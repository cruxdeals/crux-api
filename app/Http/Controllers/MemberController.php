<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Member;
use Illuminate\Http\Request;
use App\services\VerificationServices;
use App\Http\Requests\Member\OneMemberRequest;
use App\Http\Requests\Member\LoginMemberRequest;
use App\Http\Requests\Member\CreateMemberRequest;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:members', ['except' => ['']]);
    }

    public function oneMember(OneMemberRequest $request)
    {
        if(!$member = Member::where('id',$request->id)->first()){
            return response()->json(['status'=>'error','message'=>'member not found'],400);
        }

        return response()->json(['status'=>'success','member'=>$member],200);
    }

    public function oneLoan(Request $request)
    {
        $member = Auth('members')->user();
        $coop_id = $member->coop_id;
        if (!$loan = Loan::where('id', $request->id)->where('coop_id',$coop_id)->first()) {
            return response()->json(['status' => 'error', 'message' => "Loan Not Found"], 400);
        }

        return response()->json(['status' => 'success', 'loan' => $loan], 200);
    }

    public function allLoans(Request $request)
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

        $loans = $loans->where('status',$request->status)->where('coop_id',$request->coop_id)->orderBy('id','DESC')->with('member')->paginate($request->page_size);

        // $loans = $loans->where('status',$request->status)->orderBy('id','DESC')->with(['member'=> function($query){
        //     $query->select('firstname','lastname','email','telephone');
        // }])->paginate($request->page_size);
        return response()->json(['status'=>'success','loans'=>$loans],200);

    }
}
