<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;

class UsersController extends Controller
{
    //过滤
    public function __construct()
    {
        $this->middleware('auth',[
            'except'    => ['show','create','store','index']
        ]);
        $this->middleware('guest',[
            'only' => ['create']
        ]);
    }

    //用户个人信息
    public function show(User $user)
    {
        return view('users.show',compact('user'));
    }

    //注册视图
    public function create()
    {
        return view('users.create');
    }

    //注册处理
    public function store(Request $request)
    {
        $this->validate($request,[
            'name'  =>  'required|max:50',
            'email' =>  'required|email|unique:users|max:255',
            'password'  =>  'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name'  =>  $request->name,
            'email' =>  $request->email,
            'password'  =>  bcrypt($request->password),
        ]);

        Auth::login($user);
        session()->flash('success','欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show',[$user]);
    }

    //编辑用户视图
    public function edit(User $user)
    {
//        $this->authorize('update',$user);
        try {
            $this->authorize('update', $user);
        } catch (AuthorizationException $authorizationException) {
            return abort(403, '对不起，你无权访问此页面！');
        }
        return view('users.edit',compact('user'));
    }

    //编辑用户处理
    public function update(User $user,Request $request)
    {
        $this->validate($request,[
            'name'     => 'required|max:50',
            'password' => 'nullable|confirmed"min:6'
        ]);



        $this->authorize('update',$user);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password){
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);
        session()->flash('success','个人资料更新成功！');
        return redirect()->route('users.show',$user->id);
    }

    //用户列表
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index',compact('users'));
    }

    //删除处理
    public function destroy(User $user)
    {
        $this->authorize('destroy',$user);
        $user->delete();
        session()->flash('success','成功删除用户！');
        return back();
    }

}