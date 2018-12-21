<?php

namespace App\Http\Controllers;

use App\User;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Requests\UserCreateFormRequest;
use App\Http\Requests\UserUpdateFormRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Department;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = User::all();
        return view('user.index', ['users' => $data]);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = new User();
        $all_departments = Department::all();
        return view('user.create', ['user'=>$user,
            'all_departments'=>$all_departments,
            'roleadmin'=>false,
            'rolemanager'=>false,
            'rolemtopmanager'=>false]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserCreateFormRequest $request)
    {
        error_log(json_encode($request->all()));
        $user = User::create($request->validated());
        $this->setRoles($request, $user);
        $this->setDepartments($request, $user);
        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }
    
    private function setDepartments(Request $request, User $user) {
        if ($request->has('departments')) {
            $deps_inputs = $request->input('departments');
            $all_departments = Department::all();
            foreach ($all_departments as $current_dep) {
                if (array_key_exists($current_dep->code, $deps_inputs)) {
                    error_log('attaching');
                    error_log(json_encode($current_dep));
                    $user->departments()->attach($current_dep);
                    error_log('contains');
                    //$user->refresh();
                    error_log(json_encode($user->departments->contains($current_dep)));
                }
                else {
                    error_log('detaching');
                    error_log(json_encode($current_dep));
                    $user->departments()->detach($current_dep);
                }
            }
        }
        else {
            $user->departments()->detach();
        }
    }
    
    private function setRoles(Request $request, User $user) {
        if ($request->has('roleadmin')) {
            $user
            ->roles()
            ->attach(Role::where('name', Role::getAdminRoleName())->first());
        }
        else {
            $user
            ->roles()
            ->detach(Role::where('name', Role::getAdminRoleName())->first());
        }
        if ($request->has('rolemanager')) {
            $user
            ->roles()
            ->attach(Role::where('name', Role::getManagerRoleName())->first());
        }
        else {
            $user
            ->roles()
            ->detach(Role::where('name', Role::getManagerRoleName())->first());
        }
        if ($request->has('rolemtopmanager')) {
            $user
            ->roles()
            ->attach(Role::where('name', Role::getMtopManagerRoleName())->first());
        }
        else {
            $user
            ->roles()
            ->detach(Role::where('name', Role::getMtopManagerRoleName())->first());
        }
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $all_departments = Department::all();
        $user->load(['departments', 'roles']);
        $the_departments = [];
        foreach ($user->departments as $current_dep) {
            $the_departments[$current_dep->code] = true;
        }
        $roleadmin = ($user->roles()->where('name', Role::getAdminRoleName())->count()>0);
        $rolemanager = ($user->roles()->where('name', Role::getManagerRoleName())->count()>0);
        $rolemtopmanager = ($user->roles()->where('name', Role::getMtopManagerRoleName())->count()>0);
        
        return view('user.edit', ['user'=>$user,
            'all_departments'=>$all_departments,
            'departments'=>$the_departments,
            'roleadmin'=>$roleadmin,
            'rolemanager'=>$rolemanager,
            'rolemtopmanager'=>$rolemtopmanager
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserUpdateFormRequest $request, User $user)
    {
        // $user->update($request->validated());
        $user->name = $request->input('name');
        $user->phone = $request->input('phone');
        $user->email = $request ->input('email');
        $pass = $request ->input('password');
        if ($pass!='') {
            $user->password = $pass;
        }
        $user->save();
        
        $this->setRoles($request, $user);
        $this->setDepartments($request, $user);
        //$departments = $request->validated()['departments'];
        //$user->departments()->sync($departments);
        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index');
    }
    
    public function forcedPasswordReset(Request $request) {
        $email = $request->all()['email'];
        $password = $request->all()['password'];
        $user = User::find($email);
        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));
        $user->save();
        event(new PasswordReset($user));
    }
}
