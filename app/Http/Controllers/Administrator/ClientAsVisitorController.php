<?php

namespace App\Http\Controllers\Administrator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Database\AdminRoles;
use App\Database\ClientsUsage;
use App\CustomFunction;
use App\RadiusAPI;
use App\Http\Controllers\Controller;
use DateTime;
use DateInterval;
use DatePeriod;

class ClientAsVisitorController extends Controller
{
  use CustomFunction;
  use RadiusAPI;

  public function index( Request $request, AdminRoles $roles )
  {
    if( $request->session()->has('admin_login') )
    {
      $getroles = $this->getroles( new AdminRoles, $request->session()->get('admin_userid') );
      return response()->view('administrator.pages.client_as_visitor', [
        'request' => $request,
        'getsession' => $request->session()->all(),
        'roles' => $getroles
      ]);
    }
    else
    {
      return redirect()->route('admin_login');
    }
  }

  public function data_clientAsVisitor( Request $request, ClientsUsage $clients )
  {
    $keywords = $request->keywords;
    $rows = isset( $request->rows ) ? $request->rows : 10;
    $filterdate = isset( $request->filterdate ) ? $request->filterdate : 'today';
    $startDate = isset( $request->startDate ) ? $request->startDate : date('Y-m-d');
    $endDate = isset( $request->endDate ) ? $request->endDate : date('Y-m-d');
    $filterdevice = isset( $request->device ) ? $request->device : 'all';
    $filterap = isset( $request->ap ) ? $request->ap : 'all';

    if( isset( $request->filterdate ) )
    {
      if( $filterdate == 'this_month' OR $filterdate == 'last_month' )
      {
        $filtermonth = $filterdate == 'this_month' ? 'this month' : 'last month';
        $currentMonth = new DateTime( 'first day of ' . $filtermonth );

        if( empty( $keywords ) )
        {
          if( $filterdevice == 'all' )
          {
            if( $filterap == 'all' )
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m")'), '=', $currentMonth->format('Y-m')],
                ['connection_type', '=', 'visitor']
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
            else
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m")'), '=', $currentMonth->format('Y-m')],
                ['ap', '=', $filterap],
                ['connection_type', '=', 'visitor']
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
          }
          else
          {
            if( $filterap == 'all' )
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m")'), '=', $currentMonth->format('Y-m')],
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor']
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
            else
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m")'), '=', $currentMonth->format('Y-m')],
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor'],
                ['ap', '=', $filterap],
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
          }
        }
        else
        {
          if( $filterdevice == 'all' )
          {
            if( $filterap == 'all' )
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m")'), '=', $currentMonth->format('Y-m')],
                ['client_mac', 'like', '%' . $keywords . '%'],
                ['connection_type', '=', 'visitor']
              ])
              ->orWhere([
                [DB::raw('date_format(updated_at, "%Y-%m")'), '=', $currentMonth->format('Y-m')],
                ['client_ip', 'like', '%' . $keywords . '%'],
                ['connection_type', '=', 'visitor']
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
            else
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m")'), '=', $currentMonth->format('Y-m')],
                ['client_mac', 'like', '%' . $keywords . '%'],
                ['connection_type', '=', 'visitor'],
                ['ap', '=', $filterap]
              ])
              ->orWhere([
                [DB::raw('date_format(updated_at, "%Y-%m")'), '=', $currentMonth->format('Y-m')],
                ['client_ip', 'like', '%' . $keywords . '%'],
                ['connection_type', '=', 'visitor'],
                ['ap', '=', $filterap]
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
          }
          else
          {
            if( $filterap == 'all' )
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m")'), '=', $currentMonth->format('Y-m')],
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor'],
                ['client_mac', 'like', '%' . $keywords . '%']
              ])
              ->orWhere([
                [DB::raw('date_format(updated_at, "%Y-%m")'), '=', $currentMonth->format('Y-m')],
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor'],
                ['client_ip', 'like', '%' . $keywords . '%']
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
            else
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m")'), '=', $currentMonth->format('Y-m')],
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor'],
                ['client_mac', 'like', '%' . $keywords . '%'],
                ['ap', '=', $filterap]
              ])
              ->orWhere([
                [DB::raw('date_format(updated_at, "%Y-%m")'), '=', $currentMonth->format('Y-m')],
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor'],
                ['client_ip', 'like', '%' . $keywords . '%'],
                ['ap', '=', $filterap]
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
          }
        }
      }
      else if( $filterdate == 'today' )
      {
        $today = date('Y-m-d');
        if( empty( $keywords ) )
        {
          if( $filterdevice == 'all' )
          {
            if( $filterap == 'all' )
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', $today],
                ['connection_type', '=', 'visitor']
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
            else
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', $today],
                ['connection_type', '=', 'visitor'],
                ['ap', '=', $filterap]
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
          }
          else
          {
            if( $filterap == 'all' )
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', $today],
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor']
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
            else
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', $today],
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor'],
                ['ap', '=', $filterap]
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
          }
        }
        else
        {
          if( $filterdevice == 'all' )
          {
            if( $filterap == 'all' )
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', $today],
                ['connection_type', '=', 'visitor'],
                ['client_mac', 'like', '%' . $keywords . '%']
              ])
              ->orWhere([
                [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', $today],
                ['connection_type', '=', 'visitor'],
                ['client_ip', 'like', '%' . $keywords . '%']
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
            else
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', $today],
                ['connection_type', '=', 'visitor'],
                ['client_mac', 'like', '%' . $keywords . '%'],
                ['ap', '=', $filterap]
              ])
              ->orWhere([
                [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', $today],
                ['connection_type', '=', 'visitor'],
                ['client_ip', 'like', '%' . $keywords . '%'],
                ['ap', '=', $filterap]
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
          }
          else
          {
            if( $filterap == 'all' )
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', $today],
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor'],
                ['client_mac', 'like', '%' . $keywords . '%']
              ])
              ->orWhere([
                [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', $today],
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor'],
                ['client_ip', 'like', '%' . $keywords . '%']
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
            else
            {
              $query = $clients->where([
                [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', $today],
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor'],
                ['client_mac', 'like', '%' . $keywords . '%'],
                ['ap', '=', $filterap]
              ])
              ->orWhere([
                [DB::raw('date_format(updated_at, "%Y-%m-%d")'), '=', $today],
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor'],
                ['client_ip', 'like', '%' . $keywords . '%'],
                ['ap', '=', $filterap]
              ])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
          }
        }
      }
      else
      {
        if( $filterdate == '7days' )
        {
          $previousDate = new DateTime('7 days ago');
        }
        else if( $filterdate == '28days' )
        {
          $previousDate = new DateTime('28 days ago');
        }
        else
        {
          $previousDate = new DateTime('30 days ago');
        }

        $currentDate = new DateTime('today');
        $interval = new DateInterval('P1D');
        $period = new DatePeriod( $previousDate, $interval, $currentDate );
        $rangeDate = [];
        foreach( $period as $date )
        {
          $rangeDate[] = $date->format('Y-m-d');
        }
        $beginDate = $rangeDate[0];
        $lastDate = end( $rangeDate );

        if( empty( $keywords ) )
        {
          if( $filterdevice == 'all' )
          {
            if( $filterap == 'all' )
            {
              $query = $clients->where('connection_type', '=', 'visitor')
              ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$beginDate, $lastDate])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
            else
            {
              $query = $clients->where([
                ['connection_type', '=', 'visitor'],
                ['ap', '=', $filterap]
              ])
              ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$beginDate, $lastDate])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
          }
          else
          {
            if( $filterap == 'all' )
            {
              $query = $clients->where([
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor']
              ])
              ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$beginDate, $lastDate])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
            else
            {
              $query = $clients->where([
                ['client_os', '=', $filterdevice],
                ['connection_type', '=', 'visitor'],
                ['ap', '=', $filterap]
              ])
              ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$beginDate, $lastDate])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
          }
        }
        else
        {
          if( $filterdevice == 'all' )
          {
            if( $filterap == 'all' )
            {
              $query = $clients->where([
                ['connection_type', '=', 'visitor'],
                ['client_mac', 'like', '%' . $keywords . '%']
              ])
              ->orWhere([
                ['connection_type', '=', 'visitor'],
                ['client_ip', 'like', '%' . $keywords . '%']
              ])
              ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$beginDate, $lastDate])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
            else
            {
              $query = $clients->where([
                ['connection_type', '=', 'visitor'],
                ['client_mac', 'like', '%' . $keywords . '%'],
                ['ap', '=', $filterap]
              ])
              ->orWhere([
                ['connection_type', '=', 'visitor'],
                ['client_ip', 'like', '%' . $keywords . '%'],
                ['ap', '=', $filterap]
              ])
              ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$beginDate, $lastDate])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
          }
          else
          {
            if( $filterap == 'all' )
            {
              $query = $clients->where([
                ['connection_type', '=', 'visitor'],
                ['client_mac', 'like', '%' . $keywords . '%'],
                ['client_os', '=', $filterdevice]
              ])
              ->orWhere([
                ['connection_type', '=', 'visitor'],
                ['client_ip', 'like', '%' . $keywords . '%'],
                ['client_os', '=', $filterdevice]
              ])
              ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$beginDate, $lastDate])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
            else
            {
              $query = $clients->where([
                ['connection_type', '=', 'visitor'],
                ['client_mac', 'like', '%' . $keywords . '%'],
                ['client_os', '=', $filterdevice],
                ['ap', '=', $filterap]
              ])
              ->orWhere([
                ['connection_type', '=', 'visitor'],
                ['client_ip', 'like', '%' . $keywords . '%'],
                ['client_os', '=', $filterdevice],
                ['ap', '=', $filterap]
              ])
              ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$beginDate, $lastDate])
              ->orderBy('updated_at','desc')
              ->paginate( $rows );
            }
          }
        }
      }
    }
    else
    {
      if( empty( $keywords ) )
      {
        if( $filterdevice == 'all' )
        {
          if( $filterap == 'all' )
          {
            $query = $clients->where('connection_type', '=', 'visitor')
            ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$startDate, $endDate])
            ->orderBy('updated_at','desc')
            ->paginate( $rows );
          }
          else
          {
            $query = $clients->where([
              ['connection_type', '=', 'visitor'],
              ['ap', '=', $filterap]
            ])
            ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$startDate, $endDate])
            ->orderBy('updated_at','desc')
            ->paginate( $rows );
          }
        }
        else
        {
          if( $filterap == 'all' )
          {
            $query = $clients->where([
              ['client_os', '=', $filterdevice],
              ['connection_type', '=', 'visitor']
            ])
            ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$startDate, $endDate])
            ->orderBy('updated_at','desc')
            ->paginate( $rows );
          }
          else
          {
            $query = $clients->where([
              ['client_os', '=', $filterdevice],
              ['connection_type', '=', 'visitor'],
              ['ap', '=', $filterap]
            ])
            ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$startDate, $endDate])
            ->orderBy('updated_at','desc')
            ->paginate( $rows );
          }
        }
      }
      else
      {
        if( $filterdevice == 'all' )
        {
          if( $filterap == 'all' )
          {
            $query = $clients->where([
              ['connection_type', '=', 'visitor'],
              ['client_mac', 'like', '%' . $keywords . '%']
            ])
            ->orWhere([
              ['connection_type', '=', 'visitor'],
              ['client_ip', 'like', '%' . $keywords . '%']
            ])
            ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$startDate, $endDate])
            ->orderBy('updated_at','desc')
            ->paginate( $rows );
          }
          else
          {
            $query = $clients->where([
              ['connection_type', '=', 'visitor'],
              ['client_mac', 'like', '%' . $keywords . '%'],
              ['ap', '=', $filterap]
            ])
            ->orWhere([
              ['connection_type', '=', 'visitor'],
              ['client_ip', 'like', '%' . $keywords . '%'],
              ['ap', '=', $filterap]
            ])
            ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$startDate, $endDate])
            ->orderBy('updated_at','desc')
            ->paginate( $rows );
          }
        }
        else
        {
          if( $filterap == 'all' )
          {
            $query = $clients->where([
              ['connection_type', '=', 'visitor'],
              ['client_mac', 'like', '%' . $keywords . '%'],
              ['client_os', '=', $device]
            ])
            ->orWhere([
              ['connection_type', '=', 'visitor'],
              ['client_ip', 'like', '%' . $keywords . '%'],
              ['client_os', '=', $device]
            ])
            ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$startDate, $endDate])
            ->orderBy('updated_at','desc')
            ->paginate( $rows );
          }
          else
          {
            $query = $clients->where([
              ['connection_type', '=', 'visitor'],
              ['client_mac', 'like', '%' . $keywords . '%'],
              ['client_os', '=', $device],
              ['ap', '=', $filterap]
            ])
            ->orWhere([
              ['connection_type', '=', 'visitor'],
              ['client_ip', 'like', '%' . $keywords . '%'],
              ['client_os', '=', $device],
              ['ap', '=', $filterap]
            ])
            ->whereBetween(DB::raw('date_format(updated_at, "%Y-%m-%d")'), [$startDate, $endDate])
            ->orderBy('updated_at','desc')
            ->paginate( $rows );
          }
        }
      }
    }

    return response()->json( $query, 200 );
  }
}
