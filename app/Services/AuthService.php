<?php

namespace App\Services;

use App\Jobs\Email\PasswordRecoveryJob;
use App\Jobs\Email\SendResetPasswordJob;
use App\Mail\ResetEmail;
use App\Mail\ResetPasswordEmail;
use App\Models\User;
use App\Utils\SMS;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthService
{
    use ServiceTrait;

    public function model()
    {
        return User::class;
    }

    public function relationships()
    {
        return [
            'roles',
        ];
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return JsonResponse [string] access_token
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login'       => 'required|string',
            'password'    => 'required|string',
            'remember_me' => 'boolean'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $credentials = ['email' => strtolower($request->login), 'password' => $request->password];
        if (!Auth::attempt($credentials)) {
            $credentials = ['phone' => $request->login, 'password' => $request->password];
            if (!Auth::attempt($credentials)) {
                $credentials = ['document' => $request->login, 'password' => $request->password];
                if (!Auth::attempt($credentials)) {
                    abort(401, 'Usuário ou senha incorretos');
                }
            }
        }

        $user = $request->user();

        $loggedUser = $this->getLoggedUser($user);

        return response()->json($loggedUser);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return string [string] message
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $user->token()->revoke();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get current user
     *
     * @param Request $request
     * @return JsonResponse|string
     */
    public function user(Request $request)
    {
        try {
            $user       = $request->user();
            $loggedUser = $this->getLoggedUser($user);

            return response()->json($loggedUser);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Password Recovery
     *
     * @param Request $request
     * @return JsonResponse|string [string] message
     */
    public function passwordRecovery (Request $request)
    {
        $user     = null;
        $password = null;

        DB::transaction(function () use ($request, &$user, &$password) {
            Validator::make($request->all(), [
                'email' => 'required|string|email|exists:users',
                'phone' => 'required_without:|string|exists:users',
            ]);

            $user = User::where('email', $request->email)->orWhere('phone', $request->phone)->firstOrFail();

            $password = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

            DB::table('password_resets')->insert([
                'email'      => $user->email,
                'phone'      => $user->phone,
                'token'      => bcrypt($password),
                'created_at' => Carbon::now()
            ]);
        });

        try {
            dispatch(new PasswordRecoveryJob($user, $password));
        } catch (\Exception $e) {
        }
    }

    /**
     * Password Reset
     *
     * @param Request $request
     * @return JsonResponse [string] message
     * @throws ValidationException
     */
    public function resetPassword (Request $request)
    {
        $user = null;

        //Validate input
        $validator = Validator::make($request->all(), [
            'password'         => 'required|confirmed',
            'token'            => 'required_without:password_current',
            'password_current' => 'required_without:token'
        ]);

        //check if payload is valid before moving on
        if ($validator->fails())
            throw new ValidationException($validator);

        DB::transaction(function () use ($request, &$user) {

            if ($request->token) {
                $tokenData       = null;
                $password_resets = DB::table('password_resets')->get();

                foreach ($password_resets as $password_reset) {
                    if (Hash::check($request->token, $password_reset->token)) {
                        $tokenData = $password_reset;
                        break;
                    }
                }

                if (!$tokenData)
                    abort(404, 'Código inválido');

                $user = User::where('email', $tokenData->email)->firstOrFail();

            } elseif ($user = Auth::user()) {
                if (!Hash::check($request->password_current, $user->password)) {
                    abort(403, 'Senha inválida');
                }
            } else {
                abort(500, 'Falha no servidor');
            }

            //Hash and update the new password
            $user->password = bcrypt($request->password);
            $user->update();

            if ($user->token())
                $user->token()->revoke();

            //Delete the token
            DB::table('password_resets')->where('email', $user->email)->where('phone', $user->phone)
                ->delete();

            try {

                dispatch(new SendResetPasswordJob($user));

                return response()->json(array_merge(['message' => 'Email e SMS de pré cadastro enviados!']), 201);

            } catch (\Exception $e) {
                return $e->getMessage();
            }
        });

        return response()->json($this->getLoggedUser($user));

    }

    /**
     * @param $user
     * @param null $remember_me
     * @return array
     */
    public function getLoggedUser($user, $remember_me = null)
    {
        $tokenResult = $user->createToken('Joguei.bet');
        $token       = $tokenResult->token;

        if ($remember_me) {
            $token->expires_at = Carbon::now()->addHours(3);
        }

        $token->save();

        return [
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'          => $user->id,
                'name'        => $user->name,
                'role'        => strtoupper($user->role),
                'email'       => $user->email,
                'permissions' => $this->permissions($user),
            ],
            'expires_at'   => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ];
    }


    // PRIVATE FUNCTIONS

    private function sendResetTokenEmail ($user, $token)
    {
        try {
            //Here send the link with CURL with an external email API
            Mail::to($user)->locale('pt - BR')->send(new ResetEmail($user, $token));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function sendResetPasswordEmail ($user)
    {
        try {
            //Here send the link with CURL with an external email API
            Mail::to($user)->locale('pt - BR')->send(new ResetPasswordEmail($user));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $user
     * @return array
     */
    private function permissions($user)
    {
        $permissions = $user->getAllPermissions();

        return $permissions->values()->map(function ($value) use ($user) {
            return $value->name;
        })->toArray();
    }

    private function sendPasswordResetSMS ($user)
    {
        try {
            return SMS::send([
                'phone'   => $user->phone,
                'message' => "Olá $user->name, sua senha na Notificar foi redefinida com sucesso."
            ]);

        } catch (\Exception $e) {
            return false;
        }
    }
}
