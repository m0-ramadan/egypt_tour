<?php

namespace App\Http\Repositories\Admin;

use App\Http\Interfaces\Admin\SubscribeInterface;
use App\Models\Subscribe;

class SubscribeRepository implements SubscribeInterface{

    protected $subscribeModel;
    public function __construct(Subscribe $subscribe)
    {
       $this->subscribeModel= $subscribe;
    }
    public function index(){


        $subscribes=$this->subscribeModel::get();

        return view('Admin.subscribe.index',compact('subscribes'));
    }
}
