<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index($i)
    {
        if ($i == 0) {
            $listEmployee = DB::table('employees')->whereNull('day_quit')->get();
        }
        if ($i == 1) {
            $listEmployee = DB::table('employees')->whereNotNull('day_quit')->get();
        }
        return response()->json([
            'message' => 'Query successfully!',
            'status' => 200,
            'list_employee' => $listEmployee,
        ]);
    }

    /**
     * Display the specified resource.
     */

    public function getEmployeeByAccountId()
    {
        $user = auth()->user();
        // Kiểm tra token hợp lệ và người dùng đã đăng nhập
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        } else {
            $employee = DB::table('employees')->where('account_id', '=', $user->id)->first();

            if ($employee) {
                $position = Position::find($employee->position_id);
                if ($position) {
                    $department = Department::find($position->department_id);

                    $data = [
                        "avatar" => $user->avatar,
                        "username" => $user->username,
                        "email" => $user->email,
                        "image" => $employee->image,
                        "name" => $employee->full_name,
                        "gender" => $employee->gender,
                        "birthday" => $employee->birthday,
                        "CMND" => $employee->CMND,
                        "address" => $employee->address,
                        "phone" => $employee->phone,
                        "account_bank" => $employee->account_bank,
                        "name_bank" => $employee->name_bank,
                        "day_start" => $employee->day_start,
                        "position_name" => $position->position_name,
                        "department_name" => $department->department_name
                    ];

                    return response()->json([
                        'message' => 'Query successfully!',
                        'status' => 200,
                        'customer' => $data,
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Data not found!',
                        'status' => 401,
                    ], 401);
                }
            }
        }
    }
    public function updateEmployeeByAccountId(Request $request)
    {
        $user = auth()->user();
        // Kiểm tra token hợp lệ và người dùng đã đăng nhập
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $data = Account::find($user->id);
        $employee = DB::table('employees')->where('account_id', '=', $user->id)->first();
        $employeeModel = Employee::find($employee->id);
        if ($data && $employeeModel) {
            if ($request->avatar) {
                $data->avatar = $request->avatar;
                $data->update();
            }
            if ($request->address) {
                $employeeModel->address = $request->address;
            }
            if ($request->phone) {
                $employeeModel->phone = $request->phone;
            }
            if ($request->account_bank) {
                $employeeModel->account_bank = $request->account_bank;
            }
            if ($request->name_bank) {
                $employeeModel->name_bank = $request->name_bank;
            }

            $employeeModel->update();
            return response()->json([
                'message' => 'Update successfully!',
                'status' => 200,
                'data' => $data,
                'customer' => $employeeModel,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Data not found!',
                'status' => 401,
            ], 401);
        }
    }
    public function searchByParams($search)
    {
        if ($search) {
            $result  = Employee::where('full_name', 'LIKE', "%{$search}%")->get();

            if (count($result) > 0) {
                return response()->json([

                    'status' => 200,
                    'data' => $result,
                ]);
            } else {
                return response()->json([
                    'message' => 'Not search',
                    'status' => 400,
                    'data' => $result,
                ]);
            }
        }
    }
    public function employeeFindID($id)
    {
        $employee = Employee::find($id);
        if ($employee) {
            $position = Position::find($employee->position_id);
            if ($position) {
                $department = Department::find($position->department_id);

                $data = [
                    "id" => $employee->id,
                    "name" => $employee->full_name,
                    "gender" => $employee->gender,
                    "birthday" => $employee->birthday,
                    "image" => $employee->image,
                    "CMND" => $employee->CMND,
                    "address" => $employee->address,
                    "phone" => $employee->phone,
                    "account_bank" => $employee->account_bank,
                    "name_bank" => $employee->name_bank,
                    "day_start" => $employee->day_start,
                    "day_quit" => $employee->day_quit,
                    "position_name" => $position->position_name,
                    "department_name" => $department->department_name
                ];

                return response()->json([
                    'message' => 'Query successfully!',
                    'status' => 200,
                    'data' => $data,
                ]);
            }
        } else {
            return response()->json([
                'message' => 'No ID Found',
                'status' => 404,
            ]);
        }
    }

    public function updateEmployeeByAdmin(Request $request, string $id)
    {
        $employee = Employee::find($id);
        if ($employee) {
            $position = DB::table('positions')->where('position_name', '=', $request->position_name)->first();
            if ($request->full_name) {
                $employee->full_name = $request->full_name;
            }
            if ($request->gender) {
                $employee->gender = $request->gender;
            }
            if ($request->birthday) {
                $employee->birthday = $request->birthday;
            }
            if ($request->CMND) {
                $employee->CMND = $request->CMND;
            }
            if ($request->position_name) {
                $employee->position_id = $position->id;
            }
            $employee->update();

            if ($employee->account_id) {
                $account = Account::find($employee->account_id);
                if ($position->permission == 0) {
                    $account->enabled = '0';
                    $account->update();
                }
                if ($position->permission == 1) {
                    if ($account->enabled == 0) {
                        $account->enabled = '1';
                        $account->update();
                    }
                }
            } else {
                if ($position->permission == 1) {
                    $accountData = [
                        'username' => $request->username,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'enabled' => $request->enabled | '1',
                        'role_id' => $request->role_id | '2'
                    ];
                    $account = Account::create($accountData);
                    $token = Auth::guard('api')->login($account);
                    $employee->account_id = $account->id;
                    $employee->update();
                }
            }
            return response()->json([
                'message' => 'Update successfully!',
                'status' => 200,
                'customer' => $employee,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Data not found!',
                'status' => 401,
            ], 401);
        }
    }
    public function updateAccountEmployeeByAdmin($id, $position_name)
    {
        $employee = Employee::find($id);
        $position = DB::table('positions')->where('position_name', '=', $position_name)->first();
        if ($employee->account_id == null) {
            $account = Account::find($employee->account_id);
            if ($position->permission == 0) {
                return response()->json([
                    'status' => 200,
                    'message' => false,
                ]);
            }
            if ($position->permission == 1) {
                if ($account->enabled == 0) {
                    return response()->json([
                        'status' => 200,
                        'message' => false,
                    ]);
                }
            }
        } else {
            if ($position->permission == 1) {

                return response()->json([
                    'status' => 200,
                    'message' => true,
                ]);
            }
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required',
            'gender' => 'required',
            'birthday' => 'required',
            'CMND' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'account_bank' => 'required',
            'name_bank' => 'required',
            'department_name' => 'required',
            'position_name' =>  'required'
        ]);
        if ($validator->fails()) {
            $response = [
                'status_code' => 400,
                'message' => $validator->errors(),
            ];
            return response()->json($response, 400);
        }
        $position = DB::table('positions')->where('position_name', '=', $request->position_name)->first();

        $data = [
            'full_name' => $request->full_name,
            'gender' => $request->gender,
            'birthday' => $request->birthday,
            'CMND' => $request->CMND,
            'address' => $request->address,
            'phone' => $request->phone,
            'account_bank' => $request->account_bank,
            'name_bank' => $request->name_bank,
            'day_start' => Carbon::now($request->day_start),
            'status' => $request->status | 1,
            'position_id' => $position->id,
        ];


        if ($position->permission == '1') {
            $accountData = [
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'enabled' => $request->enabled | '1',
                'role_id' => $request->role_id | '2'
            ];

            $account = Account::create($accountData);
            $token = Auth::guard('api')->login($account);
            $data['account_id'] = $account->id;
        } else {
            $data['account_id'] = null;
        }
        $employee = Employee::create($data);
        return response()->json([
            'status' => 200,
            'message' => 'Employee created Successfully',
            'account' => $position,
            'employee' => $employee,
        ]);
    }
    public function storeAccountbyEmployee($i)
    {
        $position = DB::table('positions')->where('position_name', '=', $i)->first();

        if ($position->permission == '1') {
            return response()->json([
                'status' => 200,
                'message' => true,
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => false,
            ]);
        }
    }
    public function quitEmployeeByID(Request $request, string $id)
    {
        $account_id = DB::table('employees')->where('id', '=', $id)->value('account_id');

        if ($account_id) {

            $employee = DB::table('employees')->where('id', '=', $id)->update([
                'day_quit' => Carbon::now(),
                'status' => 0,
            ]);

            $account = DB::table('accounts')->where('id', '=', $account_id)->update([
                'enabled' => 0,
            ]);

            if ($employee && $account) {
                return response()->json([
                    'message' => 'Employee Updated Successfully',
                    'status' => 200,
                    'employee' => $id,
                    'account' => $account_id,
                ]);
            } else {
                return response()->json([
                    'message' => 'Updated Failed!',
                    'status' => 400,
                ]);
            }
        } else {
            $employee = DB::table('employees')->where('id', '=', $id)->update([
                'day_quit' => Carbon::now(),
                'status' => 0,
            ]);
            if ($employee) {
                return response()->json([
                    'message' => 'Employee Updated Successfully',
                    'status' => 200,
                    'employee' => $id,

                ]);
            } else {
                return response()->json([
                    'message' => 'Updated Failed!',
                    'status' => 400,
                    'employee' => $id,
                ]);
            }
        }
    }
}
