<?php

namespace App\Http\Controllers\user_portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\User;
use App\Models\Company;
use App\Models\Power;
use App\Models\ErrorInfo;
use App\Models\Accessories;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function createUser()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Create User"]
        ];
        $user_roles = DB::table('roles')->get();
        return view('user_portal.User.create-new-user',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'user_roles'=>$user_roles]);
    }
    
    public function saveUser(Request $request)
    {

        $created_by = Auth::user()->id;
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role,
            'status' => 1,
            'password' => Hash::make($request->password),
        ];

        $create_user = User::create($data);
        if($create_user){
            $notification = array(
                'message' => 'User Created Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('UserList')->with($notification);
        }
    }

    public function UserList()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "User List"]
        ];
        $users_list = User::with('Role')->where('id','!=',Auth()->user()->id)->get();
        return view('user_portal.User.user_list',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'users_list'=>$users_list]);
    }

    public function editUser($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/users", "name" => "User List"],["name" => "Edit User"]
        ];
        $user_roles = DB::table('roles')->get();
        $user = User::where('id',$id)->first();
        return view('user_portal.User.edit-new-user',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'user_roles'=>$user_roles,'user'=>$user]);
    }

    public function updateUser(Request $request,$id)
    {
        $update_user = User::find($id);

        $data = [
            'name' => $request->name,
            'role_id' => $request->role,
            'status' => $request->status,
        ];
        $update_user->update($data);
        if($update_user){
            $notification = array(
                'message' => 'User Updated Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('UserList')->with($notification);
        }
    }

    public function updatePassword($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/users", "name" => "User List"],["name" => "Update User Password"]
        ];
        $user = User::find($id);
        return view('user_portal.User.edit-password',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'user'=>$user]);
    }

    public function updateUserPassword(Request $request)
    {
        $find_user = User::where('id', $request->user_id)->first();
        $user_password = $request->user_password;

        $data = [
            'password' => Hash::make($user_password),
        ];

        $find_user->update($data);
        $message = 'Password has been updated for '. $find_user->email;

        return redirect()->route('UserList')->with('message', $message);
    }

    //comapny
    public function createCompany()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Create Company"]
        ];
        return view('user_portal.Company.create',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs]);
    }
    
    public function saveCompany(Request $request)
    {
        $data = [
            'name' => $request->name,
            'status' => 1,
        ];

        $create_company = Company::create($data);
        if($create_company){
            $notification = array(
                'message' => 'Company Created Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('CompanyList')->with($notification);
        }
    }

    public function CompanyList()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Company List"]
        ];
        $list = Company::all();
        return view('user_portal.Company.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'list'=>$list]);
    }

    public function editCompany($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/company-list", "name" => "Company List"],["name" => "Edit Company"]
        ];
        $data = Company::where('id',$id)->first();
        return view('user_portal.Company.edit',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'data'=>$data]);
    }

    public function updateCompany(Request $request,$id)
    {
        $find_data = Company::find($id);

        $data = [
            'name' => $request->name,
            'status' => $request->status,
        ];
        $find_data->update($data);
        if($find_data){
            $notification = array(
                'message' => 'Company Updated Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('CompanyList')->with($notification);
        }
    }

    //power
    public function createPower()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Create Power"]
        ];
        return view('user_portal.Power.create',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs]);
    }
    
    public function savePower(Request $request)
    {
        $data = [
            'name' => $request->name,
        ];

        $create_power = Power::create($data);
        if($create_power){
            $notification = array(
                'message' => 'Power Created Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('PowerList')->with($notification);
        }
    }

    public function PowerList()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Power List"]
        ];
        $list = Power::all();
        return view('user_portal.Power.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'list'=>$list]);
    }

    public function editPower($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/power-list", "name" => "Power List"],["name" => "Edit Power"]
        ];
        $data = Power::where('id',$id)->first();
        return view('user_portal.Power.edit',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'data'=>$data]);
    }

    public function updatePower(Request $request,$id)
    {
        $find_data = Power::find($id);

        $data = [
            'name' => $request->name,
        ];
        $find_data->update($data);
        if($find_data){
            $notification = array(
                'message' => 'Power Updated Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('PowerList')->with($notification);
        }
    }

    //error
    public function createError()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Create Error"]
        ];
        return view('user_portal.Error.create',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs]);
    }
    
    public function saveError(Request $request)
    {
        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'status' => 1,
        ];

        $create = ErrorInfo::create($data);
        if($create){
            $notification = array(
                'message' => 'Error Created Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('ErrorList')->with($notification);
        }
    }

    public function ErrorList()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Error List"]
        ];
        $list = ErrorInfo::all();
        return view('user_portal.Error.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'list'=>$list]);
    }

    public function editError($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/error-list", "name" => "Error List"],["name" => "Edit Error"]
        ];
        $data = ErrorInfo::where('id',$id)->first();
        return view('user_portal.Error.edit',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'data'=>$data]);
    }

    public function updateError(Request $request,$id)
    {
        $find_data = ErrorInfo::find($id);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
        ];
        $find_data->update($data);
        if($find_data){
            $notification = array(
                'message' => 'Error Updated Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('ErrorList')->with($notification);
        }
    }

    //accessory
    public function createAccessory()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Create Accessory"]
        ];
        return view('user_portal.Accessory.create',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs]);
    }
    
    public function saveAccessory(Request $request)
    {
        $data = [
            'name' => $request->name,
            'status' => 1,
        ];

        $create = Accessories::create($data);
        if($create){
            $notification = array(
                'message' => 'Accessory Created Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('AccessoryList')->with($notification);
        }
    }

    public function AccessoryList()
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["name" => "Accessory List"]
        ];
        $list = Accessories::all();
        return view('user_portal.Accessory.index',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'list'=>$list]);
    }

    public function editAccessory($id)
    {
        $pageConfigs = ['pageHeader' => true];
        $breadcrumbs = [
          ["link" => "/", "name" => "Home"],["link" => "/accessory-list", "name" => "Accessory List"],["name" => "Edit Accessory"]
        ];
        $data = Accessories::where('id',$id)->first();
        return view('user_portal.Accessory.edit',['pageConfigs'=>$pageConfigs,'breadcrumbs'=>$breadcrumbs,'data'=>$data]);
    }

    public function updateAccessory(Request $request,$id)
    {
        $find_data = Accessories::find($id);

        $data = [
            'name' => $request->name,
            'status' => $request->status,
        ];
        $find_data->update($data);
        if($find_data){
            $notification = array(
                'message' => 'Accessory Updated Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('AccessoryList')->with($notification);
        }
    }
}
