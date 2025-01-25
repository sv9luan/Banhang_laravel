<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\Transaction;
use Carbon\Carbon;

use Auth;


class UserController extends Controller
{
    public function index()
    {
        return view('user.index');
    }

    public function orders()
    {
        $orders = Order::where('user_id',Auth::user()->id)->orderBy('created_at','DESC')->paginate(10);
        return view('user.orders',compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::where('user_id',Auth::user()->id)->where('id',$order_id)->first();
        if($order)
        {
            $orderItems = OrderItem::where('order_id',$order_id)->orderBy('id')->paginate(12);
            $transaction = Transaction::where('order_id',$order_id)->first();
            return view('user.order-details',compact('order', 'orderItems', 'transaction'));
        }
        else
        {
            return redirect()->route('login');
        }

    }

    public function order_cancel(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = "canceled";
        $order->canceled_date = Carbon::now();
        $order->save();
        return back()->with("status", "Order has been cancelled successfully!");
    }

    public function account_address()
    {
        $address = Address::where('user_id',Auth::user()->id)->orderBy('created_at','DESC')->paginate(10);
        return view('user.account-address', compact('address'));
    }

    public function account_address_add()
    {
        return view('user.account-address-add');
    }

    public function account_address_add_store(Request $request)
    {
        // Xác thực đầu vào từ người dùng
        $request->validate([
            'name' => 'required|max:100',
            'phone' => 'required|numeric|digits:10',
            'zip' => 'required|numeric|digits:6',
            'state' => 'required',
            'city' => 'required',
            'address' => 'required',
            'locality' => 'required',
            'landmark' => 'required',
            'isdefault' => 'nullable|boolean',  // Có thể là true hoặc false
        ]);

        // Lấy thông tin người dùng đã đăng nhập
        $user_id = Auth::user()->id;

        // Tạo một đối tượng Address mới
        $address = new Address();
        $address->user_id = $user_id;
        $address->name = $request->name;
        $address->phone = $request->phone;
        $address->zip = $request->zip;
        $address->state = $request->state;
        $address->city = $request->city;
        $address->address = $request->address;
        $address->locality = $request->locality;
        $address->landmark = $request->landmark;
        $address->country = '';  // Nếu cần thì thêm country vào đây
        $address->isdefault = $request->isdefault ?? false;  // Mặc định là false nếu không chọn

        // Lưu địa chỉ mới vào cơ sở dữ liệu
        $address->save();

        // Nếu địa chỉ này được chọn làm mặc định, cập nhật các địa chỉ khác của người dùng
        if ($address->isdefault) {
            Address::where('user_id', $user_id)
                ->where('id', '!=', $address->id)  // Đảm bảo không cập nhật chính địa chỉ vừa tạo
                ->update(['isdefault' => false]);
        }

        // Redirect hoặc trả về phản hồi sau khi lưu thành công
        return redirect()->route('user.acc_add')->with('success', 'Address added successfully!');
    }

    // Hiển thị form sửa địa chỉ
    public function editAddress($id)
    {
        $address = Address::findOrFail($id);  // Tìm địa chỉ theo ID
        return view('user.edit-address', compact('address'));
    }

    // Cập nhật thông tin địa chỉ
    public function updateAddress(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:100',
            'phone' => 'required|numeric|digits:10',
            'zip' => 'required|numeric|digits:6',
            'state' => 'required',
            'city' => 'required',
            'address' => 'required',
            'locality' => 'required',
            'landmark' => 'required',
            'isdefault' => 'nullable|boolean',
        ]);

        $address = Address::findOrFail($id);
        $address->name = $request->name;
        $address->phone = $request->phone;
        $address->zip = $request->zip;
        $address->state = $request->state;
        $address->city = $request->city;
        $address->address = $request->address;
        $address->locality = $request->locality;
        $address->landmark = $request->landmark;
        $address->country = '';  // Nếu cần, bạn có thể thêm country vào đây.
        $address->isdefault = $request->isdefault ?? false;  // Nếu không chọn mặc định thì là false.

        // Nếu địa chỉ này được chọn làm mặc định, cập nhật các địa chỉ khác thành không phải mặc định
        if ($address->isdefault) {
            Address::where('user_id', $address->user_id)
                ->where('id', '!=', $address->id)
                ->update(['isdefault' => false]);
        }

        $address->save();

        return redirect()->route('user.acc_add')->with('success', 'Address updated successfully!');
    }


    public function acc_details()
    {
        return view('user.account-details');
    }


}
