<?php

namespace App\Http\Controllers\BiznetWifi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use App\RadiusAPI;
use App\Database\AccountSubscriber;
use App\Http\Resources\DevicesResource;
use App\Http\Controllers\Controller;

class AccountSubscriberController extends Controller
{
  use RadiusAPI;

  public function datadevice( Request $request, AccountSubscriber $subscriber, $customerid )
  {
    $subscriber = $subscriber->select('account_id',DB::raw('date_format(login_date, "%b %d, %Y %H:%i") as logindate'),'mac_address','device_agent')
    ->where('account_id', $customerid)
    ->orderBy('login_date');
    $data = [
      'total' => $subscriber->count(),
      'data' => $subscriber->get()
    ];
    return response()->json($data);
  }

  public function destroy( Request $request, AccountSubscriber $subscriber, $customerid, $mac )
  {
    $subscriber = $subscriber->where([
      ['account_id', $customerid],
      ['mac_address', $mac]
    ]);

    if( $subscriber->count() != 0 )
    {
      $getsubscriber = $subscriber->first();
      $this->timeout_socket = 3;
      $radprimary = $this->check_connection('182.253.238.66', 3306);
      $radbackup = $this->check_connection('202.169.53.9', 3306);

      if( $radbackup['status'] == null )
      {
        $this->delete_radcheck( '202.169.53.9', $getuser->mac_address );
        $subscriber->delete();
      }
      else
      {
        $this->delete_radcheck( '182.253.238.66:8080', $getuser->mac_address );
        $subscriber->delete();
      }
      return response()->json([ 'statusText' => strtoupper( $mac ) . ' berhasil dihapus.' ], 200);
    }
  }
}
