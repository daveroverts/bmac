<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;
class VotingController extends Controller
{
    //Views
    public function showAdminPage(){
        //Fetch existing polls
        $polls = DB::table('poll')->get();
        return view('voting.admin',compact('polls'));
    }
    public function viewPoll(Request $request,$poll_id){
        //$poll_id = $request->input('poll_id');
        $poll = DB::table('poll')->where('id',$poll_id)->first();
        $poll_choices = DB::table('poll_choices')->where('poll_id',$poll_id)->get();
        /*$votes = DB::table('votes')->selectRaw('votes.vote_id as id, poll_choices.option_name as name, votes.order as priority, count(*) as votes')
            ->join('poll_choices','votes.vote_id','=','poll_choices.id')
            ->where('votes.poll_id',$poll_id)->groupBy('votes.vote_id','votes.order')->get();*/
        //This was using a mysql stored procedure.. that i forgot to save
        //$votes = DB::select('call pull_pollresults (?)',array($poll_id));
        //POSTGRES compatbility
        $votes = DB::select('
        select poll_choices.option_name as name, count(1) filter (where votes.order=0) as first,count(1) filter (where votes.order=1) as second,count(1) filter (where votes.order=2) as third
from poll_choices, votes
where
CAST(poll_choices.poll_id AS INTEGER) = ? and
poll_choices.id = CAST(votes.vote_id AS INTEGER)
GROUP BY
poll_choices.option_name
        ',array($poll_id));
        return view('voting.viewmore',compact('poll','poll_choices','votes'));
    }
    public function editPoll(Request $request){
        $poll_id = $request->input('poll_id');
        $hidden = $request->input('hidden');
        $update = DB::table('poll')->where('id',$poll_id)->update(['hidden'=>$hidden]);
        if ($update){
            Session::flash("title","Success!");
            Session::flash("text","Poll Visibility has been updated");
            Session::flash("type","success");
            return redirect()->route('admin.voting.viewpoll',[$poll_id]);
        } else{
            Session::flash("title","Error!");
            Session::flash("text","There was a problem trying to update the poll");
            Session::flash("type","error");
            return redirect()->route('admin.voting.viewpoll',[$poll_id]);
        }
    }
    public function showAddPage(){
        return view('voting.addnew');
    }
    //Administrative Functions
    public function addNewPoll(Request $request){
        $poll_name = $request->input('name');
        $poll_description = $request->input('description');
        $poll_options = $request->input('option');

        //add into poll table..
        $poll_id = DB::table("poll")->insertGetId(["poll_name"=>$poll_name,"poll_description"=>$poll_description,"hidden"=>1]);

        //add the options in..
        foreach ($poll_options as $x){
            DB::table("poll_choices")->insert(["poll_id"=>$poll_id,"option_name"=>$x]);
        }
        Session::flash("title","Success!");
        Session::flash("text","Poll has been added");
        Session::flash("type","success");
        return view("voting.addnew");
    }

    //Public Functions
    public function pubVotingPage(Request $request){
        //Fetch polls and voting options
        //Also Fetch if user has already voted
        //Fetch polls first


        $polls = DB::table('poll')->where('hidden','0')->get();
        $data = [];
        foreach ($polls as $poll){

            $poll_id = $poll->id;
            $poll_name = $poll->poll_name;
            $poll_description = $poll->poll_description;
            $votes = DB::table('votes')->where(["votes.user_id"=>Auth::id(),"votes.poll_id"=>$poll_id])->selectRaw('poll_choices.option_name as name, votes.order as order')
                ->join('poll_choices','poll_choices.id','=',DB::raw('CAST(votes.vote_id AS INTEGER)'))->orderBy('votes.order')->get();

            //fetch poll options...
            $temp2 = [];
            $poll_options = DB::table("poll_choices")->where("poll_id",$poll_id)->get();
            foreach ($poll_options as $option){
                $option_id = $option->id;
                $option_name = $option->option_name;
                $temp3 = ["option_id"=>$option_id,"option_name"=>$option_name];
                $temp2[] = $temp3;
            }
            $data[] = ["poll_id"=>$poll_id,"poll_name"=>$poll_name,"votes"=>$votes,"poll_description"=>$poll_description,"poll_options"=>$temp2];

        }
        return view("voting.publicvoting",compact('data'));
    }
    public function pubVote(Request $request){
        $poll_id=$request->input('poll_id');
        $first=$request->input('first');
        $second=$request->input('second');
        $third=$request->input('third');
        //Make sure they don't vote all to be the same..
        if($first == $second|$second == $third|$first == $third){
            //if they do
            Session::flash("title","Error!");
            Session::flash("text","You cannot have the same airports for your 3 choices!");
            Session::flash("type","error");
            return redirect()->route('voting.main');
        } else {
            //if they don't
            //Insert insert
            $result1 = DB::table('votes')->insert(
                [
                    'user_id'=>Auth::id(),
                    'poll_id'=>$poll_id,
                    'vote_id'=>$first,
                    'order'=>0
                ]
            );
            $result2 = DB::table('votes')->insert(
                [
                    'user_id'=>Auth::id(),
                    'poll_id'=>$poll_id,
                    'vote_id'=>$second,
                    'order'=>1
                ]
            );
            $result3 = DB::table('votes')->insert(
                [
                    'user_id'=>Auth::id(),
                    'poll_id'=>$poll_id,
                    'vote_id'=>$third,
                    'order'=>2
                ]
            );
            if($result1 && $result2 && $result3){
                Session::flash("title","Success!");
                Session::flash("text","Your vote has been submitted!");
                Session::flash("type","success");
            }  else{
                Session::flash("title","Error!");
                Session::flash("text","Something bad happened!");
                Session::flash("type","error");
            }
            return redirect()->route('voting.main');
        }
    }
}
