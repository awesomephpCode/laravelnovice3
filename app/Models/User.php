<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password',];
    protected $hidden = ['password', 'remember_token',];

    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function($user){
            $user->activation_token = str_random(30);
        });
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    //用户微博动态
    public function feed()
    {
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
        array_push($user_ids, Auth::user()->id);
        return Status::whereIn('user_id', $user_ids)
            ->with('user')
            ->orderBy('created_at', 'desc');
    }

    //一个用户有多条微博
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }


    //  user_id 被关注者ID  follower_id 粉丝ID
    //获取用户粉丝列表
    public function followers()
    {
        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }

    //获取用户关注列表
    public function followings()
    {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }

    //关注
    public function follow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids,false);
    }

    //取消关注
    public function unfollow($user_ids)
    {
        if (!is_array($user_ids)){
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    //判断是否关注
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }

}
