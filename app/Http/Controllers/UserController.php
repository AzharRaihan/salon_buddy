<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use ApiResponse, FileUploadTrait;
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->has('q') && ! empty($request->q)) {
            $query->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('email', 'like', '%' . $request->q . '%');
        }

        // Sorting
        if ($request->has('sortBy') && ! empty($request->sortBy)) {
            $direction = $request->orderBy === 'desc' ? 'desc' : 'asc';
            $query->orderBy($request->sortBy, $direction);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Pagination
        $perPage = $request->itemsPerPage ?? 10;
        $users   = $query->paginate($perPage);

        foreach ($users as $user) {
            $user->actual_role_name = DB::table('roles')->where('id', $user->role)->first()->name;
        }


        return $this->successResponse([
            'users' => $users->items(),
            'total' => $users->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Handle JSON strings from FormData
        $data = $request->all();
        
        // Parse JSON strings back to arrays
        if (isset($data['branch_id']) && is_string($data['branch_id'])) {
            $data['branch_id'] = json_decode($data['branch_id'], true);
        }
        
        if (isset($data['service_id']) && is_string($data['service_id'])) {
            $data['service_id'] = json_decode($data['service_id'], true);
        }
        
        if (isset($data['social_media']) && is_string($data['social_media'])) {
            $data['social_media'] = json_decode($data['social_media'], true);
        }

        $validationRules = [
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'role'       => 'required|integer',
            'status'     => 'required|string|in:Active,Inactive',
            'phone'      => 'required|string|max:30|unique:users',
            'salary' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'overtime_hour_rate' => 'nullable|numeric|min:0',
            'service_id' => 'required|array',
            'will_login' => 'required|string|in:Yes,No',
            'designation' => 'required|string|max:55',
            'social_media' => 'required|array',
            'social_media.*.name' => 'required|string',
            'social_media.*.url' => 'nullable|string|url',
            'social_media.*.is_active' => 'required|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'age' => 'nullable|string|max:10',
            'gender' => 'required|string|in:Male,Female,Other',
            'qualification' => 'nullable|string|max:255',
            'experience' => 'required|string|max:25',
            'description' => 'nullable|string|max:255',
        ];

        if ($request->will_login === 'Yes') {
            $validationRules['password'] = 'required|string|min:6|confirmed';
        }
        if ($request->will_login === 'Yes') {
            $validationRules['branch_id'] = 'required|array';
        }

        $validator = Validator::make($data, $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $userData = [
                'name'     => $data['name'],
                'email'    => $data['email'],
                'role'    => $data['role'],
                'phone'    => $data['phone'],
                'salary'   => $data['salary'],
                'commission' => $data['commission'],
                'status' => $data['status'],
                'designation' => $data['designation'],
                'overtime_hour_rate' => $data['overtime_hour_rate'],
                'branch_id' => implode(',', $data['branch_id']),
                'service_id' => implode(',', $data['service_id']),
                'will_login' => $data['will_login'],
                'social_media' => json_encode($data['social_media']),
                'age' => $data['age'],
                'gender' => $data['gender'],
                'qualification' => $data['qualification'],
                'experience' => $data['experience'],
                'description' => $data['description'],
            ];

            if ($request->will_login === 'Yes') {
                $userData['password'] = Hash::make($data['password']);
            }

            if ($request->hasFile('photo')) {
                $userData['photo'] = $this->imageUpload($request->file('photo'), null, 'users');
            }

            $user = User::create($userData);

            $roleName = Role::find($data['role'])->name;
            $user->assignRole($roleName);

            DB::commit();
            return $this->successResponse($user, 'User created successfully');
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
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }
        return $this->successResponse($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }
        // Handle JSON strings from FormData
        $data = $request->all();
        
        // Parse JSON strings back to arrays
        if (isset($data['branch_id']) && is_string($data['branch_id'])) {
            $data['branch_id'] = json_decode($data['branch_id'], true);
        }
        
        if (isset($data['service_id']) && is_string($data['service_id'])) {
            $data['service_id'] = json_decode($data['service_id'], true);
        }
        
        if (isset($data['social_media']) && is_string($data['social_media'])) {
            $data['social_media'] = json_decode($data['social_media'], true);
        }

        $validationRules = [
            'name'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users,email,' . $id,
            'role'       => 'required|integer',
            'status'     => 'required|string|in:Active,Inactive',
            'phone'      => 'required|string|max:30|unique:users,phone,' . $id,
            'salary' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'overtime_hour_rate' => 'nullable|numeric|min:0',
            'service_id' => 'required|array',
            'will_login' => 'required|string|in:Yes,No',
            'designation' => 'required|string|max:55',
            'social_media' => 'required|array',
            'social_media.*.name' => 'required|string',
            'social_media.*.url' => 'nullable|string|url',
            'social_media.*.is_active' => 'required|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024',
            'age' => 'nullable|string|max:10',
            'gender' => 'required|string|in:Male,Female,Other',
            'qualification' => 'nullable|string|max:255',
            'experience' => 'required|string|max:25',
            'description' => 'nullable|string|max:255',
        ];

        if ($request->will_login === 'Yes' && $user->password === null) {
            $validationRules['password'] = 'required|string|min:6|confirmed';
        }
        if ($request->will_login === 'Yes') {
            $validationRules['branch_id'] = 'required|array';
        }

        $validator = Validator::make($data, $validationRules);
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        DB::beginTransaction();
        try {
            $userData = [
                'name'     => $data['name'],
                'email'    => $data['email'],
                'role'    => $data['role'],
                'phone'    => $data['phone'],
                'salary'   => $data['salary'],
                'commission' => $data['commission'],
                'status' => $data['status'],
                'overtime_hour_rate' => $data['overtime_hour_rate'],
                'branch_id' => implode(',', $data['branch_id']),
                'service_id' => implode(',', $data['service_id']),
                'will_login' => $data['will_login'],
                'social_media' => json_encode($data['social_media']),
                'designation' => $data['designation'],
                'age' => $data['age'],
                'gender' => $data['gender'],
                'qualification' => $data['qualification'],
                'experience' => $data['experience'],
                'description' => $data['description'],
            ];

            if ($request->will_login === 'Yes' && isset($data['password']) && !empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            if ($request->hasFile('photo')) {
                $userData['photo'] = $this->imageUpload($request->file('photo'), $user->photo, 'users');
            }

            $user->update($userData);
            $roleName = Role::find($data['role'])->name;
            $user->assignRole($roleName);
            DB::commit();
            return $this->successResponse($user, 'User updated successfully');
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
        $user = User::find($id);
        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }
        
        // Delete user photo if exists
        if ($user->photo) {
            $this->delete($user->photo);
        }
        
        $user->delete();
        return $this->successResponse($user, 'User deleted successfully');
    }
}
