<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\Role;
use App\Models\User;
use App\Helpers\FileHelper;
use App\Models\UserProfile;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use App\Helpers\ResponseApiHelper;
use App\Repository\FileRepository;
use App\Repository\UserRepository;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Models\File;
use App\Repository\UserProfileRepository;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    private $userRepository, $fileRepository, $userProfileRepository;

    public function __construct(
        UserRepository $userRepository,
        FileRepository $fileRepository,
        UserProfileRepository $userProfileRepository
    ) 
    {
        $this->userRepository = $userRepository;
        $this->fileRepository = $fileRepository;
        $this->userProfileRepository = $userProfileRepository;
    }

    public function register(RegisterRequest $request)
    {
        // Check Role
        $userRole = Role::where('name', 'user')->first();
        $emailCheck = User::where('email', $request->email)->first();

        if ($emailCheck) {
            LoggerHelper::error('Email Already Taken.' , [
                'email' => $request->email,
                'register_at' => now()
            ]);

            return ResponseApiHelper::error('Email Already Taken.');
        }

        $request->merge([
            'password' => Hash::make($request->password),
            'role_id' => $userRole->id
        ]);
    
        $userData = $request->only([
            'name', 'email', 'password', 'role_id', 'address', 'phone_number'
        ]);

        try {
            DB::beginTransaction();
            $user = new User($userData);
            $user = $this->userRepository->store($user);

            if ($request->hasFile('image')) {
                $file = $request->file('image');

                new FileHelper($file, [
                    'file_name' => 'name',
                    'field_name' => 'directory',
                    'extension' => $request->file('image')->getClientOriginalExtension(),
                    'directory' => 'profile/',
                    'upload_at' => 'upload_at'
                ], $request);

                $fileData = $request->only([
                    'name', 'directory', 'upload_at', 'image'
                ]);
                $newFile = new File($fileData);
                $uploadedFile = $this->fileRepository->store($newFile);

                $request->merge([
                    'file_id' => $uploadedFile->id
                ]);
            }
            
            // Create or Update data User Profile
            $user->userProfile()->updateOrCreate([
                'user_id' => $user->id,
                'file_id' => $uploadedFile->id,
                'address' => $userData['address'],
                'phone_number' => $userData['phone_number']
            ]);

            DB::commit();

            LoggerHelper::info('User has been Successfully Registered.', [
                'action' => 'Register',
                'model' => 'User',
                'data' => $userData
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            LoggerHelper::error('Failed Registration.' ,[
                'request_data' => $userData,
                'error' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);

            return ResponseApiHelper::error('An error occurred during registration. Please try again later.');
        }

        return ResponseApiHelper::success("Welcome" . ' ' . ucfirst($user->name) . ' ' . "Your Account has been Successfully Created.", [
            'name' => $user->email,
            'email' => $user->email
        ]);
    }

    public function login(LoginRequest $request)
    {
        // Find User By Email in Databases
        $user = User::where('email', $request->email)->first();

        // Validate if user exists and password is correct
        if (! $user || !Hash::check($request->password, $user->password)) {
            
            // Log failed login attempt for monitoring security issues
            LoggerHelper::notice('Email Or Password is Incorrect!.', [
                'email' => $request->email,
                'login_at' => now()
            ]);

            // Return error response with 401 Unauthorized status
            return ResponseApiHelper::error('Invalid Email or Password.', [
                'credentials' => 'Email or Password is incorrect.'
            ], 401);
        }

        // Genereate Token Authentication for User After Success Logged In
        $token = $user->createToken('auth-token', [$user->role->name], now()->addHour())->plainTextToken;

        // Log successful login attempt for auditing purposes
        LoggerHelper::info('User Successfully Logged In.', [
            'email' => $user->email,
            'token' => $token
        ]);
        
        // Return Success Response with User Data and Token
        return ResponseApiHelper::success('Successfully Logged In.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role->name,
            'token' => $token
        ]);
    }

    public function logout()
    {
        $token = request()->bearerToken();

        try {
            DB::beginTransaction();

            $accessToken = PersonalAccessToken::findToken($token)->delete();

            DB::commit();

            LoggerHelper::info('User Successfully Logged Out, and Token was Revoked.', [
                'action' => 'Logout',
                'model' => 'PersonalAccessToken',
                'token' => $token
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            LoggerHelper::error('Failed Logout.', [
                'token' => $token,
                'error' => $th->getMessage(),
            ]);

            return ResponseApiHelper::error('An error occurred during logout process, please try again later.');
        }

        return ResponseApiHelper::success('Successfully Logged Out.',);
    }
}
