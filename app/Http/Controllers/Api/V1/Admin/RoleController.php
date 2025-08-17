<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\LoggerHelper;
use App\Helpers\ResponseApiHelper;
use App\Repository\RoleRepository;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Resources\RoleCollection;
use App\Http\Resources\RoleResource;

class RoleController extends Controller
{
    private $roleRepository;

    public function __construct(RoleRepository $roleRepository) {
        $this->roleRepository = $roleRepository;
    }

    public function index()
    {
        $roles= $this->roleRepository->get([
            'search' => [
                'name' => request()->name
            ],
            'page' => 5
        ]);
        
        // Message for response
        $message = request()->name 
        ? 'Filtered roles retrieved successfully.'
        : 'All roles retrieved successfully.';

        return ResponseApiHelper::success($message, new RoleCollection($roles));
    }

    public function store(RoleStoreRequest $request)
    {
        $request->merge([
            'slug' => Str::slug($request->name)
        ]);

        $data = $request->only([
            'name', 'slug'
        ]);

        try {
            DB::beginTransaction();

            $role = new Role($data);
            $role = $this->roleRepository->store($role);
 
            DB::commit();

            // Log
            LoggerHelper::info('Role data successfully stored in the database.', [
                'action' => 'store',
                'model' => 'Role',
                'data' => $role
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to store role data in database.', [
                'request_data' => $data,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing store role data. Please try again later.');
        }

        return ResponseApiHelper::success('New Role successfully created.', [
            'role' => [
                'id' => $role->id,
                'slug' => $role->slug
            ]
        ]);
    }

    public function show(Role $role)
    {
        return ResponseApiHelper::success('Role retrived successully.', new RoleResource($role));
    }
    
    public function update(RoleUpdateRequest $request, Role $role)
    {
        $request->merge([
            'slug' => Str::slug($request->name)
        ]);

        $data = $request->only([
            'name', 'slug'
        ]);

        try {
            DB::beginTransaction();

            $role = $role->fill($data);
            $role = $this->roleRepository->update($role);

            DB::commit();

            // Log
            LoggerHelper::info('Role data successfully updated in the database.', [
                'action' => 'update',
                'model' => 'Role',
                'data' => $role
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to update role data in database.', [
                'request_data' => $data,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing update role data. Please try again later.');
        }

        return ResponseApiHelper::success('Role has been successfully updated.', [
            'role' => [
                'id' => $role->id,
                'slug' => $role->slug
            ]
        ]);
    }

    public function destroy(Role $role)
    {
        // Prevent deletion if the role has existing relationships with users
        if ($role->users()->exists()) {
            return ResponseApiHelper::error("Can't Delete Role: This Role has existing relationship with other entities.", [
                'error' => 'This role is currently assigned to users and cannot be deleted.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $role = $this->roleRepository->destroy($role);

            DB::commit();

            // Log
            LoggerHelper::info('Role data successfully deleted from the database.', [
                'action' => 'delete',
                'model' => 'Role',
                'deleted_id' => $role->id
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            // Log
            LoggerHelper::error('Failed to delete role data from database.', [
                'data' => $role,
                'error' => $th->getMessage()
            ]);

            return ResponseApiHelper::error('An error occurred while processing delete role data. Please try again later.');
        }

        return ResponseApiHelper::success('Role data has been successfully deleted.');
    }
}
