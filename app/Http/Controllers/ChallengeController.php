<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Illuminate\Http\Request;
use App\Challenge;
use App\User;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;


class ChallengeController extends Controller
{

    public function store(Request $request)
    {
        
        $challenge = new Challenge();
        $challenge->cname = $request->get('cname');
        $challenge->desc = $request->get('desc');
        $challenge->statement = strip_tags($request->get('statement'));
        $challenge->ipformat = strip_tags($request->get('ipformat'));
        $challenge->constraints = strip_tags($request->get('constraints'));
        $challenge->opformat = strip_tags($request->get('opformat'));
        $challenge->testcaseipformat = $request->get('testcaseipformat');
        $challenge->testcaseopformat = $request->get('testcaseopformat');
        $challenge->tags = ($request->get('tags'));
        $challenge->user_id = Auth::user()->id;
        $challenge->save();
        MailController::createNewChallenge($challenge);
    }

    protected function validator(Request $request)
    {

        $request->validate([
            'cname' => 'required|min:5',
            'desc' => 'required|min:5',
            'statement' => 'required|min:5',
            'ipformat' => 'required|min:5',
            'constraints' => 'required|min:5',
            'opformat' => 'required|min:5',
            'testcaseipformat' => 'required|min:5',
            'testcaseopformat' => 'required|min:5',
            'tags' => 'required|min:5',
        ], [
            'cname.required' => 'Challenge  is required',

            'desc.required' => 'Description is required',
            'statement.required' => 'statement is required',


            'ipformat.required' => 'Input format  is required',
            'constraints.required' => 'Constraints is required',
            'opformat.required' => 'Output format is required',
            'testcaseipformat.required' => 'Test Case Input format  is required',
            'testcaseopformat.required' => 'Test Case Optput format  is required',
            'tags.required' => 'Tags is required',
        ]);

        $this->store($request);    
        return Redirect::to('home')->withInput()->withErrors(array('msg' => "Challenge Saved Sucessfully"));

    }

    public function challenge_details()
    {
        $prob = Challenge::find($_REQUEST['cid']);
        return view('partials.editor', ['challenge' => $prob]);
    }


    public function mychallenges()
    {

        $challenges = DB::select('select count(*) count,challenges.id,
       challenges.cname,challenges.desc,challenges.created_at FROM challenges    
       left join submissions  on challenges.id=submissions.challenge_id where challenges.user_id=' . Auth::user()->id . ' and challenges.active=1
       group by challenges.id,challenges.cname,challenges.desc,challenges.created_at  order by challenges.id DESC;
      ');
        foreach ($challenges as $challenge) {
            $parsed = Carbon::parse($challenge->created_at)->diffForHumans();
            $challenge->parsedTime = $parsed;

        }
        return view('Mychallenges', ['challenges' => $challenges]);

    }

    public function prevdetails()
    {
        $challenges = Challenge::find($_REQUEST['cid']);
        return view('Update', ['challenge' => $challenges]);

    }


    public function edit(Request $request)
    {

        $cid = $_REQUEST['cid'];
        $submit = Challenge::find($cid);
        $submit->cname = $request->get('challengename');
        $submit->desc = $request->get('description');
        $submit->statement = strip_tags($request->get('problemstatement'));
        $submit->ipformat = strip_tags($request->get('inputformat'));
        $submit->constraints = strip_tags($request->get('constraints'));
        $submit->opformat = strip_tags($request->get('outputformat'));
        $submit->tags = $request->get('tags');
        $submit->testcaseipformat = $request->get('testcaseipformat');
        $submit->testcaseopformat = $request->get('testcaseopformat');
        $submit->user_id = Auth::user()->id;
        $submit->save();

        return redirect()->route('mychallenges');
    }

    public function delete(Request $request)
    {

        $challenges = Challenge::find($request->get('cid'));
        $challenges->active = 0;
        $response= $challenges->save();

        if ($response) {
            return "true";
        } else {
            return "false";
        }


    }

}
