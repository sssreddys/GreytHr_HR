<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLeaveBalances extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'employee_leave_balances';
    // Fields that can be mass-assigned
    protected $fillable = [
        'emp_id',
        'hr_emp_id',
        'leave_scheme',
        'period',
        'status',
        'periodicity',
        'leave_policy_id',
        'granted_for_year',
        'is_lapsed',
        'lapsed_date',
        'batch_id',
        'deleted_at',
        'from_date',
        'to_date'
    ];

    protected static function boot()
    {
        parent::boot();
    }
    /**
     * Get the employee associated with the leave balance.
     */

    public function employee()
    {
        return $this->belongsTo(EmployeeDetails::class, 'emp_id', 'emp_id');
    }
    public function HrEmployee()
    {
        return $this->belongsTo(HR::class, 'hr_emp_id', 'hr_emp_id');
    }

    /**
     * Get the leave balance for a given year, leave type, and employee.
     *
     * @param string $employeeId
     * @param string $leaveType
     * @param int $year
     * @return int
     */
    public static function getLeaveBalancePerYear($employeeId, $leaveName, $year)
    {
        // Retrieve all records for the specific employee and year
        $balances = self::where('emp_id', $employeeId)
        ->where('period', 'like', "%$year%")
        ->get();
        // Initialize variables
        $totalGrantDays = 0;

        // Loop through each balance record
        foreach ($balances as $balance) {
            // Decode JSON leave_policy_id column
            $leavePolicies = is_string($balance->leave_policy_id) ? json_decode($balance->leave_policy_id, true) : $balance->leave_policy_id;

            if (is_array($leavePolicies)) {
                foreach ($leavePolicies as $policy) {
                    // Check if the leave_name matches the specified leave name
                    if (isset($policy['leave_name']) && $policy['leave_name'] == $leaveName) {
                        // Add the grant_days for the specified leave_name
                        $totalGrantDays += $policy['grant_days'];
                    }
                }
            }
        }

        // Ensure the last month rounds to the nearest whole number
        return round($totalGrantDays, 2);
    }
}
