<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\StaffCheckIns;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorHistories;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{

    public function index(Request $request): View
    {
        // Get the selected date from the request or default to today
        $selectedDate = $request->input('selected_date', date('Y-m-d')); // Defaults to today's date

        // Get the month and year from the selected date
        $month = date('m', strtotime($selectedDate));
        $year = date('Y', strtotime($selectedDate));

        // Get checked-in staff for the month
        $checked_in_staff_for_the_month = StaffCheckIns::whereYear('check_in_time', $year)
            ->whereMonth('check_in_time', $month)
            ->with('user')
            ->get();

        // Get checked-in visitors for the month
        $checked_in_visitors_for_the_month = VisitorHistories::whereYear('check_in_time', $year)
            ->whereMonth('check_in_time', $month)
            ->with(['visitor.user'])
            ->get();

        // Get checked-in visitors for the selected date
        $checked_in_visitors_today = VisitorHistories::whereDate('check_in_time', $selectedDate)
            ->with(['visitor.user'])
            ->paginate(10);

        // Get checked-in staff for the selected date
        $checked_in_staff_today = StaffCheckIns::whereDate('check_in_time', $selectedDate)
            ->with('user')
            ->paginate(10);

     $staffs_for_today = StaffCheckIns::whereDate('check_in_time', Carbon::today())
    ->with('user') // Eager load the 'user' relationship to get staff details
    ->get();


                // Get 10 most recent staff check-ins
                $recent_checked_in_staff = StaffCheckIns::with('user')
                ->whereDate('check_in_time', Carbon::today()) // Filter by today's date
                ->latest('check_in_time') // Sort by check_in_time in descending order
                ->take(10) // Limit to 10 results
                ->get();
            // Get 10 oldest staff check-ins for today
            $oldest_checked_in_staff = StaffCheckIns::with('user')
                ->whereDate('check_in_time', Carbon::today()) // Filter by today's date
                ->oldest('check_in_time') // Sort by check_in_time in ascending order
                ->take(10) // Limit to 10 results
                ->get();



        // Get checked-in visitors for yesterday (if needed)
        $checked_in_visitors_yesterday = VisitorHistories::whereDate('check_in_time', now()->subDay())
            ->with(['visitor.user'])
            ->get();

        // Get checked-in staff for yesterday (if needed)
        $checked_in_staff_yesterday = StaffCheckIns::whereDate('check_in_time', now()->subDay())
            ->with('user')
            ->get();

        // Count the number of staff checked in for the selected date
        $number_of_checked_in_staff_today = $checked_in_staff_today->count();
        $staffs = User::with('role')->get();
        $recent = StaffCheckIns::with('user')->whereDate('check_in_time', '=', Carbon::today()->toDateString())->limit(5)->latest();

        return view('dashboard.index', [
            'checked_in_visitors_today' => $checked_in_visitors_today,
            'checked_in_staff_today' => $checked_in_staff_today,
            'staff_for_the_month' => $checked_in_staff_for_the_month,
            'visitor_for_the_month' => $checked_in_visitors_for_the_month,
            'number_of_checked_in_staff_today' => $number_of_checked_in_staff_today,
            'checked_in_staff_yesterday' => $checked_in_staff_yesterday,
            'checked_in_visitors_yesterday' => $checked_in_visitors_yesterday,  
            'selectedDate' => $selectedDate, 

            'recent_checked_in_staff' => $recent_checked_in_staff,
            'oldest_checked_in_staff' => $oldest_checked_in_staff,
            'staffs_for_today' => $staffs_for_today,
            'staffs' => $staffs,
            'recent' => $recent

        ]);
    }

    public function getAllStaffsHistory(Request $request)
    {
        $staffs = User::with('role')
        ->when($request->search, function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->search . '%');
        })
        ->paginate($request->get('per_page', 10));
        
        // Default to 10 entries per page
    return view('staffs.index', ['staffs' => $staffs]);
    }

    function geofence() {
        return view('geofencing.index');
    }

    public function storeGeofence(Request $request)
    {
        dd($request->all());
    }

    public function getAllTheVisitorForTheMonth(Request $request)
    {
        // Get the number of entries per page from the request, defaulting to 10
        $perPage = $request->input('per_page', 10);

        // Get the search query from the request
        $search = $request->input('search', '');

        $staffs = Role::where('name', 'Staff')->with('users')->get();

        $visitors_for_the_month = VisitorHistories::whereMonth('check_in_time', date('m'))
            ->whereYear('check_in_time', date('Y'))
            ->with(['visitor.user'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('visitor', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->paginate($perPage);

            

        return view('visitors.index', [
            'visitors_for_the_month' => $visitors_for_the_month,
            'search' => $search, // Pass the search query to the view
            'perPage' => $perPage, // Pass the per page value to the view
        ]); 
    }


}
