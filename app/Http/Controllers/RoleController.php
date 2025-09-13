<?php
namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Role::query();

        // Search functionality
        if ($request->has('q') && ! empty($request->q)) {
            $query->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('description', 'like', '%' . $request->q . '%');
        }

        // Filter by del_status
        $query->where('roles.company_id', Auth::user()->company_id);
        
        // Sorting
        if ($request->has('sortBy') && ! empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $roles   = $query->paginate($perPage);

        return $this->successResponse([
            'roles' => $roles->items(),
            'total' => $roles->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
                'description' => $request->description,
                'company_id' => Auth::user()->company_id,
                'user_id' => Auth::user()->id,
                'del_status' => 'Live',
            ]);

            $permissionsID = array_map(
                function ($value) {return (int) $value;},
                $request->input('permissions')
            );

            $role->syncPermissions($permissionsID);
            DB::commit();
            return $this->successResponse($role, 'Role created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role            = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
            ->where("role_has_permissions.role_id", $id)
            ->get();
        $role->permissions = $rolePermissions;
        return $this->successResponse($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $role = Role::find($id);
            $role->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => now(),
                'company_id' => Auth::user()->company_id,
                'user_id' => Auth::user()->id,
                'del_status' => 'Live',
            ]);

            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
            DB::commit();
            return $this->successResponse($role, 'Role updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::table("roles")->where('id', $id)->delete();
        return $this->successResponse(null, 'Role deleted successfully');
    }

    public function permissions()
    {
        $permissions = Permission::all()->groupBy('group_name');
        return $this->successResponse($permissions);
    }
}
