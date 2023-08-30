<?php

namespace App\Http\Controllers\Swagger;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Email\NullMail;
use App\Http\Requests\ApplicationRequest;
use App\Http\Requests\ApplicationResponceRequest;
use App\Http\Requests\RegisterRequest;
use App\Jobs\ApplicationJob;
use App\Models\Application;
use App\Models\User;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="my doc Api"
 * )
 * @OA\PathItem(
 *     path="/api/"
 * )
 * @OA\Components(
 *     @OA\SecurityScheme(
 *        securityScheme="bearerAuth",
 *        type="http",
 *        scheme="bearer"
 *     )
 * )
 */
class ApiController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/requests/register",
     *     summary="Регистрация пользователя",
     *     tags={"application"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *            allOf={
     *               @OA\Schema(
     *                   @OA\Property(property="name", type="string",example="den"),
     *                   @OA\Property(property="email", type="string",example="den@test.com"),
     *                   @OA\Property(property="password", type="string",example="123456"),
     *                   @OA\Property(property="password_confirmation", type="string",example="123456"),
     *                   @OA\Property(property="role", type="string",example="manager"),
     *               )
     *            }
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *            @OA\JsonContent(
     *               @OA\Property(property="access_token", type="string",example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiNGE4MjYyY2Y5MWIwOGYyZGY5Y..."),
     *               @OA\Property(property="message", type="string",example="Successfully created user!"),
     *               )
     *     )
     * )
     */
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
            return response()->json([
                'access_token' => $accessToken,
                'message' => 'Successfully created user!'
            ], 201);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/requests/",
     *     summary="Оставляем заявку",
     *     tags={"application"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *            allOf={
     *               @OA\Schema(
     *                   @OA\Property(property="message", type="string",example="hello"),
     *               )
     *            }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *            @OA\JsonContent(
     *               @OA\Property(property="message", type="string",example="application successfully sent for processing"),
     *            )
     *     )
     * )
     */
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
    /**
     * @OA\Put(
     *     path="/api/requests/{id}",
     *     summary="ответ на конкретную задачу ответственным лицом",
     *     tags={"application"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *        description="id apllication",
     *        in="path",
     *        name="id",
     *        required=true,
     *        example=1,
     *     ),
     *      @OA\RequestBody(
     *         @OA\JsonContent(
     *            allOf={
     *               @OA\Schema(
     *                   @OA\Property(property="comments", type="string",example="test"),
     *               )
     *            }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *            @OA\JsonContent(
     *               @OA\Property(property="message", type="string",example="Application response sent successfully"),
     *            )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/requests/",
     *     summary="Оставляем заявку",
     *     tags={"application"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *            @OA\JsonContent(
     *
     *                  @OA\Property(property="id", type="inteher",example=1),
     *                  @OA\Property(property="name", type="string",example="deb"),
     *                  @OA\Property(property="email", type="string",example="den@mail.com"),
     *                  @OA\Property(property="status", type="string",example="Resolved"),
     *                  @OA\Property(property="message", type="string",example="test"),
     *                  @OA\Property(property="comments", type="string",example="test"),
     *                  @OA\Property(property="user_id", type="string",example=1),
     *                  @OA\Property(property="created_at", format="date-time",example="2023-08-29T15:37:29.000000Z"),
     *                  @OA\Property(property="updated_at",format="date-time",example="2023-08-30T13:32:33.000000Z"),
     *                  )
     *
     *            )
     *     )
     * )
     */
    public function getApplication()
    {
        return response()->json(Application::all(), 200);
    }
}
