<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Database\AccountSubscriber;
use App\Database\AccountMember;
use App\Database\ClientsUsage;
use App\RadiusAPI;
use App\CustomFunction;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class PortalController extends Controller
{
  use RadiusAPI;
  use CustomFunction;

  public function connectmikrotik( Request $request )
  {
    if( ! $request->session()->has('session_locale') )
    {
      $locale = app()->getLocale();
      session()->put('session_locale', $locale);
    }

    $getlocale = session()->get('session_locale');
    app()->setLocale( $getlocale );

    $ap = $request->ap;
    $client_mac = $request->client_mac;
    $uip = $request->uip;
    $ssid = $request->ssid;
    $startUrl = route('you_are_connected_page');
    $location = $request->loc;
    $shaping = $request->shaping;

    if( isset( $client_mac ) AND ! empty( $client_mac ) )
    {
      $request->session()->put('ap', 'mikrotik');
      $request->session()->put('location_id', $location);
      $request->session()->put('client_mac', $client_mac);
      $request->session()->put('uip', $uip);
      $request->session()->put('ssid', $ssid);
      $request->session()->put('starturl', $startUrl);
    }

    $filter_location = explode('-', $location);
    $merchant = $filter_location[1];

    $merchantDetail = $this->merchant_detail($merchant);

    return response()->view('portal.connect', [
      'mac' => $client_mac,
      'uip' => $uip,
      'ssid' => $ssid,
      'startUrl' => $startUrl,
      'loc' => [
        'origin' => $location,
        'merchant' => $merchantDetail
      ],
      'ap' => $ap,
      'shaping' => $shaping
    ])
    ->header('Content-Type', 'text/html; charset=utf8')
    ->header('Accepts', 'text/html; charset=utf8')
    ->header('Access-Control-Allow-Headers', 'POST');
  }

  public function connectruckus( Request $request )
  {
    if( ! $request->session()->has('session_locale') )
    {
      $locale = app()->getLocale();
      session()->put('session_locale', $locale);
    }

    $getlocale = session()->get('session_locale');
    app()->setLocale( $getlocale );

    $ap = $request->ap;
    $client_mac = $request->client_mac;
    $uip = $request->uip;
    $ssid = $request->ssid;
    $startUrl = route('you_are_connected_page');
    $location = $request->loc;
    $shaping = $request->shaping;

    if( isset( $client_mac ) AND ! empty( $client_mac ) )
    {
      $convert_location_id = hex2bin( $location );
      $request->session()->put('ap', $ap);
      $request->session()->put('location_id', $convert_location_id);
      $request->session()->put('client_mac', $client_mac);
      $request->session()->put('uip', $uip);
      $request->session()->put('ssid', $ssid);
      $request->session()->put('starturl', $startUrl);
    }
    $filter_location = explode('-', $convert_location_id);
    $merchant = $filter_location[1];

    $merchantDetail = $this->merchant_detail( $merchant );

    return response()->view('portal.connect', [
      'mac' => $client_mac,
      'uip' => $uip,
      'ssid' => $ssid,
      'startUrl' => $startUrl,
      'loc' => [
        'origin' => $location,
        'merchant' => $merchantDetail
      ],
      'ap' => $ap,
      'shaping' => $shaping
    ])
    ->header('Content-Type', 'text/html; charset=utf8')
    ->header('Accepts', 'text/html; charset=utf8')
    ->header('Access-Control-Allow-Headers', 'GET');
  }

  public function merchant_detail($merchantId)
  {
    $merchantDetail = DB::connection('sqlsrv208')->table('Wifi_Zone_New')
                          ->where('Merchant_ID', $merchantId)
                          ->first();

    if($merchantDetail) 
    {
      $res = [
        'name' => $merchantDetail->Zone_Name,
        'logo' => 'http://www.biznethotspot.com/img/logos/merchants/' .$merchantDetail->Logo_Image
      ];
    }
    else
    {
      $res = [
        'name' => '',
        'logo' => ''
      ];
    }

    return $res;
  }

  public function afterlogin( Request $request, AccountSubscriber $subscriber, AccountMember $member, ClientsUsage $clientusage )
  {
    if( ! $request->session()->has('client_mac') AND ! $request->session()->has('uip') )
    {
      return redirect()->route('you_are_connected_page');
    }

    $connection_type = $request->session()->get('connect') == 'freehotspot' ? 'visitor' : 'subscriber';
    $client_mac = strtolower( $request->session()->get('client_mac') );
    
    $clientIfExists = $clientusage->select(
      'client_mac',
      DB::raw('date_format(created_at, "%Y-%m-%d") as start_connected'),
      DB::raw('date_format(updated_at, "%Y-%m-%d") as last_connected')
    )
    ->where('client_mac', '=', $client_mac)
    ->orderBy(DB::raw('date_format(updated_at, "%Y-%m-%d")'), 'desc');
    if( $clientIfExists->count() != 0 )
    {
      $clients = $clientIfExists->first();
      if( $clients->last_connected == date('Y-m-d') )
      {
        $updated = $clientusage->where([
          ['client_mac', '=', $client_mac],
          [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', date('Y-m-d')]
        ])->first();
        $updated->client_ip = $request->session()->get('uip');
        $updated->client_mac = $client_mac;
        $updated->client_os = $this->getOsInfo( $request->server('HTTP_USER_AGENT') );
        $updated->location_id = $request->session()->get('location_id');
        $updated->connection_type = $connection_type;
        $updated->ap = $request->session()->get('ap');
        $updated->save();
      }
      else
      {
        $clients = new $clientusage;
        $clients->client_ip = $request->session()->get('uip');
        $clients->client_mac = $client_mac;
        $clients->client_os = $this->getOsInfo( $request->server('HTTP_USER_AGENT') );
        $clients->location_id = $request->session()->get('location_id');
        $clients->connection_type = $connection_type;
        $clients->ap = $request->session()->get('ap');
        $clients->updated_at = date('Y-m-d H:i:s');
        $clients->save();
      }
    }
    else
    {
      $clients = new $clientusage;
      $clients->client_ip = $request->session()->get('uip');
      $clients->client_mac = $client_mac;
      $clients->client_os = $this->getOsInfo( $request->server('HTTP_USER_AGENT') );
      $clients->location_id = $request->session()->get('location_id');
      $clients->connection_type = $connection_type;
      $clients->ap = $request->session()->get('ap');
      $clients->updated_at = date('Y-m-d H:i:s');
      $clients->save();
    }

    if( $request->session()->get('connect') == 'freehotspot' )
    {
      $request->session()->forget('connect');
      $request->session()->flush();
      return redirect()->route('afterlogin_page');
    }
    else if( $request->session()->get('connect') == 'biznetwifi' )
    {
      if( $request->session()->has('client_mac') AND $request->session()->has('uip') )
      {
        $mac = $request->session()->get('client_mac');
        $username = $request->session()->get('username');
        $displayname = $request->session()->get('displayname');
        $agent = $request->session()->get('agent');

        if( $username === 'SI20096955-51080' OR empty( $username ) OR $username === null ) {
          abort(401);
        }

        if( $request->session()->get('login_type') == 'member' ) 
        {
          $devices = $member->getUserDevices($username);
          $checkmacaddress = $member->checkMacAddress($username, $mac);
          $getlastmac = $member->getUserLastDevice($username);
          $loginId = $member->getLoginId($username);

          if( $checkmacaddress == 0 )
          {
            if ( $devices == 2 )
            {
              $this->add_radcheck( '182.253.238.66:8080', $mac, $username );
              $this->delete_radcheck( '182.253.238.66:8080', $getlastmac->MAC_ADDRESS );
              if( $checkmacaddress == 0 )
              {
                $member->saveUserDevice([
                  'ID_LOGIN' => $loginId,
                  'MAC_ADDRESS' => $mac,
                  'DEVICE_AGENT' => $this->userAgent( $agent ),
                  'LOGIN_DATE' => date('Y-m-d H:i:s')
                ]);
  
                $member->deleteUserDevice($username, $getlastmac->MAC_ADDRESS);
              }
            }
            else
            {
              $this->add_radcheck( '182.253.238.66:8080', $mac, $username );
              if( $checkmacaddress == 0 )
              {
                $member->saveUserDevice([
                  'ID_LOGIN' => $loginId,
                  'MAC_ADDRESS' => $mac,
                  'DEVICE_AGENT' => $this->userAgent( $agent ),
                  'LOGIN_DATE' => date('Y-m-d H:i:s')
                ]);
              }
            }
          }
          else
          {
            $this->add_radcheck( '182.253.238.66:8080', $mac, $username );
          }
        } 
        else 
        {
          $checksubs = $subscriber->where('account_id', '=', $username);
          $checkmacaddress = $subscriber->select('mac_address')->where([
            ['account_id', $username],
            ['mac_address', $mac]
          ]);
          $getlastmac = $subscriber->where('account_id', $username)
          ->orderBy('login_date', 'asc')->first();
  
          if( $checkmacaddress->count() == 0 )
          {
            if( $checksubs->count() == 5 )
            {
              $this->add_radcheck( '182.253.238.66:8080', $mac, $username );
              $this->delete_radcheck( '182.253.238.66:8080', $getlastmac->mac_address );
              if( $checkmacaddress->count() == 0 )
              {
                $subscriber->account_name = $displayname;
                $subscriber->account_id = $username;
                $subscriber->mac_address = $mac;
                $subscriber->login_date = date('Y-m-d H:i:s');
                $subscriber->device_agent = $this->userAgent( $agent );
                $subscriber->save();
  
                $deletedevice = $subscriber->where('mac_address', '=', $getlastmac->mac_address);
                if( $deletedevice->count() != 0 )
                {
                  $deletedevice->delete();
                }
              }
            }
            else
            {
              $this->add_radcheck( '182.253.238.66:8080', $mac, $username );
              if( $checkmacaddress->count() == 0 )
              {
                $subscriber->account_name = $displayname;
                $subscriber->account_id = $username;
                $subscriber->mac_address = $mac;
                $subscriber->login_date = date('Y-m-d H:i:s');
                $subscriber->device_agent = $this->userAgent( $agent );
                $subscriber->save();
              }
            }
          }
          else
          {
            $this->add_radcheck( '182.253.238.66:8080', $mac, $username );
          }
          
        }
        
        return redirect()->route('hmpgcustomer');
      }
      else
      {
        if( $request->session()->has('connect') )
        {
          if( $request->session()->get('connect') == 'freehotspot' )
          {
            return redirect()->route('connect_mikrotik');
          }
          else
          {
            return redirect()->route('connect_ruckus');
          }
        }
        else
        {
          return redirect()->route('connect_mikrotik');
        }
      }
    }
  }

  public function hotspot( Request $request )
  {
    $request->session()->put('connect', 'freehotspot');
    $ap = $request->session()->get('ap');
    $client_mac = $request->session()->get('client_mac');
    $uip = $request->session()->get('uip');
    $ssid = $request->session()->get('ssid');
    $starturl = $request->session()->get('starturl');
    $location = $request->session()->get('location_id');

    $username_radius = 'newhotspot';
    $password_radius = 'biznet';

    if ($ap == 'ruckus')
    {
      $redirect = 'http://10.132.0.5:9997/SubscriberPortal/hotspotlogin?username=' . $username_radius . '&password=' . $password_radius . '&uip=' . $uip . '&client_mac=' . $client_mac . '&ssid=' . $ssid . '&starturl=' . $starturl;
    }
    else
    {
      $redirect = 'http://10.10.10.10/login?username=' . $username_radius .'&password=' .$password_radius .'&client_mac=' .$client_mac .'&uip=' .$uip;
    }

    return redirect( $redirect );
  }

  public function youareconnected( Request $request )
  {
    return response()->view('portal.you-are-connected', [
      'request' => $request
    ]);
  }

  public function testing( Request $request )
  {
    dd( $request->session()->all() );
  }
}
