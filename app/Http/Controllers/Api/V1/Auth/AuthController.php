<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Helpers\AuthHelper;
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
use App\Http\Requests\UpdateProfileRequest;
use App\Models\File;
use App\Repository\UserProfileRepository;
use App\Services\FileService;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    private $userRepository, $fileRepository, 
        $userProfileRepository, $fileService;

    public function __construct(
        UserRepository $userRepository,
        FileRepository $fileRepository,
        UserProfileRepository $userProfileRepository,
        FileService $fileService
    ) 
    {
        $this->userRepository = $userRepository;
        $this->fileRepository = $fileRepository;
        $this->userProfileRepository = $userProfileRepository;
        $this->fileService = $fileService;
    }

    public function register(RegisterRequest $request)
    {
        // Check Role
        $userRole = Role::where('name', 'user')->first();

        // Check is email exists
        $emailCheck = User::where('email', $request->email)->first();
        
        if ($emailCheck) {

            // Log
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
            'name', 'email', 'password', 'role_id', 'address', 'phone_number', 'file_id'
        ]);

        try {
            DB::beginTransaction();
            $user = new User($userData);
            $user = $this->userRepository->store($user);

            // Create or Update data User Profile
            $user->userProfile()->updateOrCreate([
                'user_id' => $user->id,
                'file_id' => $userData['file_id'] ?? null,
                'address' => $userData['address'],
                'phone_number' => $userData['phone_number']
            ]);

            DB::commit();

            // Log
            LoggerHelper::info('User has been Successfully Registered.', [
                'action' => 'register',
                'model' => 'User',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_id' => $user->role->id 
                ]
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed Registration.' ,[
                'request_data' => $userData,
                'error' => $th->getMessage(),
            ]);

            return ResponseApiHelper::error('An error occurred during registration. Please try again later.');
        }

        return ResponseApiHelper::success("Welcome" . ' ' . ucfirst($user->name) . ' ' . "Your Account has been Successfully Created.", [
            'name' => $user->name,
            'email' => $user->email
        ]);
    }

    public function login(LoginRequest $request)
    {
        // Find User By Email in Databases
        $user = User::where('email', $request->email)->first();

        // Validate if user exists and password is correct
        if (! $user || !Hash::check($request->password, $user->password)) {
            
            // Log
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

        // Log
        LoggerHelper::info('User Successfully Logged In.', [
            'email' => $user->email,
            'token' => substr($token, 0, 5) . '...' . substr($token, -5)
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

            // Log
            LoggerHelper::info('User Successfully Logged Out, and Token was Revoked.', [
                'action' => 'logout',
                'model' => 'PersonalAccessToken',
                'token' => substr($token, 0, 5) . '...' . substr($token, -5)
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed Logout.', [
                'token' => substr($token, 0, 5) . '...' . substr($token, -5),
                'error' => $th->getMessage(),
            ]);

            return ResponseApiHelper::error('An error occurred during logout process, please try again later.');
        }

        return ResponseApiHelper::success('Successfully Logged Out.',);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $userLogin = AuthHelper::getUserFromToken(request()->bearerToken());
        $userId = $userLogin->id;
        
        $request->merge([
            'user_id' => $userId
        ]);

        $data = $request->only([
            'name', 'phone_number', 'address', 'file_id', 'user_id'
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($userId);
            $user->update([
                'name' => $data['name'],
            ]);

            $oldFileId = $user->userProfile()->pluck('file_id')->toArray();
            $newFileId = $data['file_id'];

            $user->userProfile()->updateOrCreate([
                'user_id' => $userId,
                'phone_number' => $data['phone_number'],
                'address' => $data['address'],
                'file_id' =>  $newFileId 
            ]);

            DB::commit();

            // Log
            LoggerHelper::info('User Profile successfully updated.', [
                'action' => 'update',
                'model' => 'UserProfile',
                'data' => $data
            ]);
            
        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed update profile.', [
                'request_data' => $data,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occured during upload profile process, please try again later.');
            
        } finally {

            $unusedFileId = array_diff($oldFileId, [$newFileId]);
            $deleteFile = $this->fileService->deleteUnusedFiles($unusedFileId);

            // Log unused file
            LoggerHelper::info('Unused file id has been deleted.', [
                'action' => 'delete',
                'model' => 'File',
                'deleted_file_id' => $unusedFileId
            ]);
        }

        return ResponseApiHelper::success('User Profile successfully updated.');
    }
}