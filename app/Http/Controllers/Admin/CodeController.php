<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Interfaces\Admin\CodeInterface;
use App\Http\Requests\Admin\Code\CreateCodeRequest;
use App\Http\Requests\Admin\Code\UpdateCodeRequest;

class CodeController extends Controller
{
   protected $codeInterface;

    public function __construct(CodeInterface $codeInterface)
    {
      $this->codeInterface = $codeInterface;
    }

    public function index(){
       return  $this->codeInterface->index();
    }


    public function create(){
        return  $this->codeInterface->create();
    }

    public function store( CreateCodeRequest $request){
        return  $this->codeInterface->store($request);
    }

    public function edit($id){
        return  $this->codeInterface->edit($id);
    }

    public function update(UpdateCodeRequest $request){
        return  $this->codeInterface->update($request);
    }

    public function destroy($id){
        return  $this->codeInterface->destroy($id);
    }
}
