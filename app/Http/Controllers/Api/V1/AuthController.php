<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

use App\Http\Requests\AuthRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login']]);
    }

    public function login(AuthRequest $request)
    {
        try {
            $credentials = [
                'email' => $request->input('email'),
                'password' => $request->input('password')
            ];


            if (! $token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Tài khoản hoặc mật khẩu không chính xác'], Response::HTTP_UNAUTHORIZED);
            }

            $user = auth()->user();


            $refreshTokenData = $this->refreshTokenData($user);
            $refresh_token = JWTAuth::getJWTProvider()->encode($refreshTokenData);

            $cookie = $this->setTokenAndRefreshTokenCookie($token, $refresh_token);
            $tokenCookie = $cookie['tokenCookie'];
            $refreshCookie = $cookie['refreshTokenCookie'];

            return $this->respondWithToken($token, $refresh_token, $user)->withCookie($tokenCookie)->withCookie($refreshCookie);
        } catch (Exception $e) {
            return response()->json(['message' => $e], Response::HTTP_UNAUTHORIZED);
        }
    }


    public function refresh(Request $request)
    {
        try {

            if ($request->hasCookie('access_token')) {
                $token = $request->cookie('access_token');
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }

            $user = JWTAuth::parseToken()->authenticate();

            $token = auth()->refresh();
            auth()->invalidate(true);

            $refreshTokenData = $this->refreshTokenData($user);
            $refreshToken = JWTAuth::getJWTProvider()->encode($refreshTokenData);
            $cookie = $this->setTokenAndRefreshTokenCookie($token, $refreshToken);
            $tokenCookie = $cookie['tokenCookie'];
            $refreshCookie = $cookie['refreshTokenCookie'];

            return $this->respondWithToken($token, $refreshToken, $user)->withCookie($tokenCookie)->withCookie($refreshCookie);
        } catch (TokenExpiredException $e) {
            if ($request->hasCookie('refresh_token')) {
                if (!$request->cookie('refresh_token')) {
                    return response()->json(['message' => 'Token đã hết hạn'], Response::HTTP_UNAUTHORIZED);
                }

                $refreshTokenCookie = $request->cookie('refresh_token');
                $refreshTokenDecode = JWTAuth::getJWTProvider()->decode($refreshTokenCookie);
                $user = User::find($refreshTokenDecode['user_id']);
                $token = auth()->login($user);
                $refreshTokenData = $this->refreshTokenData($user);
                $refreshToken = JWTAuth::getJWTProvider()->encode($refreshTokenData);
                $cookie = $this->setTokenAndRefreshTokenCookie($token, $refreshToken);
                $tokenCookie = $cookie['tokenCookie'];
                $refreshCookie = $cookie['refreshTokenCookie'];

                return $this->respondWithToken($token, $refreshToken, $user)->withCookie($tokenCookie)->withCookie($refreshCookie);
            }
            return response()->json(['message' => 'Token đã hết hạn'], Response::HTTP_UNAUTHORIZED);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token không hợp lệ'], Response::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Không có Token'], Response::HTTP_UNAUTHORIZED);
        }
    }


    protected function respondWithToken($token, $refresh_token, $user)
    {
        return response()->json([
            'user' => new UserResource($user),
            'access_token' => $token,
            'refresh_token' => $refresh_token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 1 // 60 phút
        ]);
    }

    public function me()
    {
        return response()->json([
            'user' => new UserResource(auth()->user())
        ]);
    }


    private function setTokenAndRefreshTokenCookie($token, $refreshToken)
    {
        $cookie = Cookie::make(
            'access_token',
            $token,
            config('jwt.refresh_ttl'), // 1 day
            '/',
            '103.155.161.244',
            false,
            true,
            false,
            'None'
        );

        $refreshCookie = Cookie::make(
            'refresh_token',
            $refreshToken,
            config('jwt.refresh_ttl'), // 2 weeks
            '/',
            '103.155.161.244',
            false,
            true,
            false,
            'None'
        );

        return [
            'tokenCookie' => $cookie,
            'refreshTokenCookie' => $refreshCookie
        ];
    }

    private function refreshTokenData($user)
    {
        return [
            'user_id' => $user->id,
            'expires_in' => time() + config('jwt.refresh_ttl'),
            // 'expires_in' => time() + 1,
            'random' => time() . md5(rand())
        ];
    }

    private function refreshToken($token, $refreshToken) {}


    public function logout()
    {
        auth()->logout();
        // Xóa cookie access_token
        $cookie = Cookie::forget('access_token');
        // Xóa cookie refresh_token
        $refreshCookie = Cookie::forget('refresh_token');


        return response()->json(['message' => 'Successfully logged out'], Response::HTTP_OK)->withCookie($cookie)
            ->withCookie($refreshCookie);;
    }
}
