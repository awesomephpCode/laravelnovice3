<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{
    //过滤
    public function __construct()
    {
        $this->middleware('auth',[
            'except'    => ['show','create','store','index','confirmEmail']
        ]);
        $this->middleware('guest',[
            'only' => ['create']
        ]);
    }

    //用户个人信息
    public function show(User $user)
    {
        $statuses = $user->statuses()
            ->orderBy('created_at','desc')
            ->paginate(10);
        return view('users.show',compact('user','statuses'));
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

//        Auth::login($user);
//        session()->flash('success','欢迎，您将在这里开启一段新的旅程~');
//        return redirect()->route('users.show',[$user]);

        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
    }

    //发送账号激活邮件
    public function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }

    //邮件跳转地址
    public function confirmEmail($token)
    {
        $user = User::where('activation_token',$token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success','恭喜你，激活成功！');
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