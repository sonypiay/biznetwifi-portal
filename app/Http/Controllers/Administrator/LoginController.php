<?php

namespace App\Http\Controllers\Administrator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Database\UsersPanel;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
  public function index( Request $request, UsersPanel $users )
  {
    $data = [
      'request' => $request->all(),
      'session' => $request->session()->all()
    ];
    return response()->view('administrator.login', $data);
  }

  public function dologin( Request $request, UsersPanel $users )
  {
    $username = $request->username;
    $password = $request->password;
    $query = $users->where( 'username', $username );
    if( $query->count() === 1 )
    {
      $result = $query->first();
      if( Hash::check( $password, $result->password_ ) )
      {
        $data = [
          'statusText' => 'Login success',
          'status' => 200
        ];
      }
      else
      {
        $data = [
          'statusText' => 'Invalid password',
          'status' => 403
        ];
      }
    }
    else
    {
      $data = [
        'statusText' => 'Username did not match',
        'status' => 403
      ];
    }
    return response()->json($data, $data['status']);
  }
}
