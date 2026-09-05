<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Interfaces\Admin\DueInterface;
use App\Http\Requests\Admin\Due\UpdateDueRequest;

class DueController extends Controller
{
    protected $dueInterface;

    public function __construct(DueInterface $dueInterface)
    {
      $this->dueInterface = $dueInterface;
    }

    public function index(){
        return  $this->dueInterface->index();
    }
    public function edit($id){
        return  $this->dueInterface->edit($id);
    }

    public function reject($id){
        return  $this->dueInterface->reject($id);
    }
    
    public function update(UpdateDueRequest $request){
        return  $this->dueInterface->update($request);
    }

}
