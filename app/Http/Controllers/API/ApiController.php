<?php

namespace App\Http\Controllers\API;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Email\NullMail;
use App\Http\Requests\ApplicationRequest;
use App\Http\Requests\ApplicationResponceRequest;
use App\Http\Requests\RegisterRequest;
use App\Jobs\ApplicationJob;
use App\Models\Application;
use App\Models\User;


class ApiController extends Controller
{
    public function register(RegisterRequest $request)
    {

        $roleId = $request->role == 'manager' ? 1 : 2;
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $roleId
        ]);
        if ($user->save()) {
            $tokenResult = $user->createToken('Personal Access Token');
            $accessToken = $tokenResult->accessToken;
            // Вставить $accessToken в заголовок Authorization: Bearer $accessToken
            return response()->json([
                'access_token' => $accessToken,
                'message' => 'Successfully created user!'
            ], 201);
        }
    }

    public function sendApplication(ApplicationRequest $request)
    {
        $user = auth()->user();

        $application = [
            'name' => $user->name,
            'email' => $user->email,
            'status' => ApplicationStatus::Active->name,
            'message' => $request->message,
            'user_id' => $user->id
        ];
        ApplicationJob::dispatch($application);
        return response()->json([
            'message' => 'application successfully sent for processing'
        ], 200);

    }

    public function aplicationResponce(ApplicationResponceRequest $request)
    {

        $aplication = Application::find($request->id);

        $aplication->status = ApplicationStatus::Resolved->name;
        $aplication->comments = $request->comments;

        $mailer = new NullMail();
        $mail = $mailer->to($aplication->email)->send($aplication->comments);
        if ($aplication->save() && $mail) {
            return response()->json([
                'message' => 'Application response sent successfully'
            ], 200);

        }
    }


    public function getApplication()
    {
        return response()->json(Application::all(),200);
    }
}
