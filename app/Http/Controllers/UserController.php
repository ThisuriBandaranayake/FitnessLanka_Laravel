<?php

namespace App\Http\Controllers;

use App\Admin;
use App\Customer;
use App\Institute;
use App\Session;
use App\User;
use Carbon\Carbon;
use hisorange\BrowserDetect\Facade as Browser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\String_;

class UserController extends Controller
{
    /**
     * Validate info
     * @var array
     */
    private $adminValidate = [
        'name' => 'required|unique:users|min:6|max:32',
        'email' => 'required|unique:users|email',
        'avatar' => 'image|max:1999',
        'user_type' => 'required|in:admin,customer,institute',
        'password' => 'required|min:6|max:32',
        'confirm_password' => 'same:password'
    ];
    private $customerValidate = [
        'name' => 'required|unique:users|min:6|max:32',
        'email' => 'required|unique:users|email',
        'avatar' => 'image|max:1999',
        'user_type' => 'required|in:admin,customer,institute',
        'password' => 'required|min:6|max:32',
        'confirm_password' => 'same:password',
       // 'first_name' => 'required',
       // 'last_name' => 'required',
        'phone_number' => 'required',
        'birthday' => 'required|date_format:Y-m-d',
        'gender' => 'required|in:male,female'
    ];
    private $instituteValidate = [
       'name' => 'required|unique:users|min:6|max:32',
        'email' => 'required|unique:users|email',
        'avatar' => 'image|max:1999',
        'user_type' => 'required|in:admin,customer,institute',
        'password' => 'required|min:6|max:32',
        'confirm_password' => 'same:password',
        'institute_name' => 'required',
        'address_line1' => 'required',
        'address_line2' => 'required',
        'city' => 'required',
        'province' => 'required',
      //  'country' => 'required',
        'postal_code' => 'required'
    ];
    private $adminEditValidate = [
        'avatar' => 'image|max:1999'
    ];
    private $customerEditValidate = [
        'avatar' => 'image|max:1999',
        'first_name' => 'required',
        'last_name' => 'required',
        'phone_number' => 'required',
        'birthday' => 'required|date_format:Y-m-d',
        'gender' => 'required|in:male,female'
    ];
    private $instituteEditValidate = [
        'avatar' => 'image|max:1999',
        'institute_name' => 'required',
        'address_line1' => 'required',
        'address_line2' => 'required',
        'city' => 'required',
        'province' => 'required',
        'country' => 'required',
        'postal_code' => 'required'
    ];

    /**
     * Signup user
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function signup(Request $request)
    {
        $userType = $request->user_type;
 
        if ($userType === 'admin') {
            //validate
            $request->validate($this->adminValidate);
            //create user
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'avatar' => $this->saveAvatar($request),
                'user_type' => $request->user_type,
                'password' => bcrypt($request->password)
            ]);

            DB::beginTransaction();
            try {
                $user->saveOrFail();
                //create admin
                $admin = new Admin([
                    'user_id' => $user->id
                ]);
                $admin->saveOrFail();
                DB::commit();
                return $this->returnSuccessResponse("Successfully created $userType account.", 201);

            } catch (\Throwable $e) {
                DB::rollBack();
                return $this->returnErrorResponse(
                    "Failed to create $userType account.",
                    'Database operations failed.', 500);
            }

        } else if ($userType === 'customer') {
            //validate
            $request->validate($this->customerValidate);
            //create user
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'avatar' => $this->saveAvatar($request),
                'user_type' => $request->user_type,
                'password' => bcrypt($request->password)
            ]);
            DB::beginTransaction();
            try {
                $user->saveOrFail();
                //create customer
                $customer = new Customer([
                    'user_id' => $user->id,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone_number' => $request->phone_number,
                    'birthday' => $request->birthday,
                    'gender' => $request->gender
                ]);
                $customer->saveOrFail();
                DB::commit();
                return $this->returnSuccessResponse("Successfully created $userType account.", 201);

            } catch (\Throwable $e) {
                DB::rollBack();
                return $this->returnErrorResponse(
                    "Failed to create $userType account.",
                    'Database operations failed.');
            }

        } else if ($userType === 'institute') {
            //validate
            $request->validate($this->instituteValidate);
            //create user
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'avatar' => $this->saveAvatar($request),
                'user_type' => $request->user_type,
                'password' => bcrypt($request->password)
            ]);
            DB::beginTransaction();
            try {
                $user->saveOrFail();
                //create institute
                $institute = new Institute([
                    'user_id' => $user->id,
                    'institute_name' => $request->institute_name,
                    'address_line1' => $request->address_line1,
                    'address_line2' => $request->address_line2,
                    'city' => $request->city,
                    'province' => $request->province,
                   // 'country' => $request->country,
                    'postal_code' => $request->postal_code
                ]);
                $institute->saveOrFail();
                DB::commit();
                return $this->returnSuccessResponse("Successfully created $userType account.", 201);

            } catch (\Throwable $e) {
                DB::rollBack();
                return $this->returnErrorResponse(
                    "Failed to create $userType account.",
                    'Database operations failed.');
            }

        } else {
            return $this->returnErrorResponse("Failed to create account.", 'Wrong user type.', 400);
        }
    }

    /**
     * Login user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required_without:email',
            'email' => 'required_without:name|email',
            'password' => 'required'
        ]);
        $credentials = ['password' => $request->password];
        if ($request->has('name')) $credentials['name'] = $request->name;
        if ($request->has('email')) $credentials['email'] = $request->email;

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            //create token
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            $token->fill([
                'browser_name' => Browser::browserName(),
                'platform_name' => Browser::platformName(),
                'device_family' => Browser::deviceFamily(),
                'device_model' => Browser::deviceModel()
            ]);
            $token->save();
            return response()->json([
                'user_type' => $user->user_type,
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString()
            ]);

        } else {
            return $this->returnErrorResponse("Failed to login.", "Invalid credentials.", 401);
        }
    }

    /**
     * Get user details
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetails(Request $request)
    {
        $user = $request->user();
        $user->avatar = env('APP_URL', 'http://localhost') . ':8000/storage/avatars/' . $user->avatar;
        $userType = $user->user_type;

        if ($userType === 'admin') {
            $array = array_merge($user->toArray(), $user->admin->toArray());
            return response()->json($array);

        } else if ($userType === 'customer') {
            $array = array_merge($user->toArray(), $user->customer->toArray());
            return response()->json($array);

        } else if ($userType === 'institute') {
            $array = array_merge($user->toArray(), $user->institute->toArray());
            return response()->json($array);

        } else {
            return $this->returnErrorResponse('Failed to get details', 'Wrong user type.', 400);
        }
    }

    /**
     * Edit user profile
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function editDetails(Request $request)
    {
        $user = $request->user();
        $userType = $user->user_type;

        if ($request->hasFile('avatar')) {
            $user->update(['avatar' => $this->editAvatar($request, $user)]);
        }

        if ($userType === 'admin') {
            $request->validate($this->adminEditValidate);
            $admin = $user->admin;
            $admin->fill([

            ]);
            $isSaved = $admin->save();

        } else if ($userType === 'customer') {
            $request->validate($this->customerEditValidate);
            $customer = $user->customer;
            $customer->fill([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'birthday' => $request->birthday,
                'gender' => $request->gender
            ]);
            $isSaved = $customer->save();

        } else if ($userType === 'institute') {
            $request->validate($this->instituteEditValidate);
            $institute = $user->institute;
            $institute->fill([
                'institute_name' => $request->institute_name,
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'city' => $request->city,
                'province' => $request->province,
                'country' => $request->country,
                'postal_code' => $request->postal_code
            ]);
            $isSaved = $institute->save();

        } else {
            return $this->returnErrorResponse('Wrong user type.', 400);
        }

        if ($isSaved) {
            return $this->returnSuccessResponse("Successfully edited $userType account.");
        } else {
            return $this->returnErrorResponse("Failed to edit $userType account.", 'Database operations failed.');
        }
    }

    /**
     * Logout from specified session
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        if ($request->has('session_id')) {
            $session = Session::where('session_id', $request->session_id)->first();
            if ($session != null) {
                if ($session->revoked) {
                    return $this->returnErrorResponse('Failed to revoke session.', 'Session already revoked.', 422);
                } else {
                    $session->update(['revoked' => '1']);
                    return $this->returnSuccessResponse('Session revoked.');
                }

            } else {
                return $this->returnErrorResponse('Failed to revoke session.', 'Session not available.', 422);
            }

        } else {
            $accessToken = $request->user()->token();
            $accessToken->revoke();
            return $this->returnSuccessResponse('Logout successful.');
        }
    }

    /**
     * Logout from all active sessions
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutAll(Request $request)
    {
        $accessTokens = $request->user()->tokens;
        foreach ($accessTokens as $token) {
            $token->revoke();
        }
        return $this->returnSuccessResponse('Logout all successful.');
    }

    /**
     * Get all sessions
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllSessions(Request $request)
    {
        $user = $request->user();
        $currentSessionId = $user->token()->session_id;
        $sessions = Session::where('user_id', $user->id)->get();
        return response()->json([
            'current' => $currentSessionId,
            'sessions' => $sessions
        ]);
    }

    /**
     * Get customized error response
     * @param $message
     * @param null $error
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    private function returnErrorResponse($message, $error = null, $statusCode = 500)
    {
        if (is_string($error)) {
            return response()->json([
                'message' => $message,
                'error' => $error
            ], $statusCode);

        } elseif (is_array($error)) {
            return response()->json([
                'message' => $message,
                'errors' => $error
            ], $statusCode);

        } else {
            return response()->json([
                'message' => $message
            ], $statusCode);
        }
    }

    /**
     * Get customized success response
     * @param $message
     * @param string $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    private function returnSuccessResponse($message, $statusCode = '200')
    {
        return response()->json([
            'message' => $message
        ], $statusCode);
    }

    /**
     * Save avatar and return file name
     * @param Request $request
     * @return string
     */
    private function saveAvatar(Request $request)
    {
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $fileNameWithExt = $file->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $fileExtension = $file->getClientOriginalExtension();
            $fileNameToStore = $fileName . '_' . time() . '.' . $fileExtension;
            $request->file('avatar')->storeAs('public/avatars', $fileNameToStore);
            return $fileNameToStore;

        } else {
            return 'default_avatar.png';
        }
    }

    /**
     * Edit user avatar, delete previous avatar before save
     * @param Request $request
     * @param User $user
     * @return string
     */
    private function editAvatar(Request $request, User $user)
    {
        if ($user->avatar !== 'default_avatar.png') {
            Storage::delete('public/avatars/' . $user->avatar);
        }

        return $this->saveAvatar($request);
    }
}
