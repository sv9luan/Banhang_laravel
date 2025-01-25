<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Models\Slide;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Laravel\Facades\Image;


class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'DESC')->get()->take(10);
        $dashboardDatas = DB::select("Select sum(total) As TotalAmount,
                            sum(if(status='ordered', total, 0)) As TotalOrderedAmount,
                            sum(if(status='delivered', total, 0)) As TotalDeliveredAmount,
                            sum(if(status='canceled', total, 0)) As TotalCanceledAmount,
                            Count(*) As Total,
                            sum(if(status='ordered', 1, 0)) As TotalOrdered,
                            sum(if(status='delivered', 1, 0)) As TotalDelivered,
                            sum(if(status='canceled', 1, 0)) As TotalCanceled
                            From Orders");


        $monthlyDatas = DB::select("SELECT M.id As MonthNo, M.name As MonthName,
        IFNULL(D.TotalAmount, 0) As TotalAmount,
        IFNULL(D.TotalOrderedAmount, 0) As TotalOrderedAmount,
        IFNULL(D.TotalDeliveredAmount,0) As TotalDeliveredAmount,
        IFNULL(D.TotalCanceledAmount,0) As TotalCanceledAmount
        FROM month_names M LEFT JOIN (
        Select DATE_FORMAT(created_at, '%b') As MonthName, MONTH(created_at) As MonthNo, sum(total) As TotalAmount, sum(if(status='ordered', total,0)) As TotalOrderedAmount,
        sum(if(status='delivered', total,0)) As TotalDeliveredAmount,
        sum(if (status='canceled', total,0)) As TotalCanceledAmount
        From Orders
        WHERE YEAR(created_at)=YEAR(NOW())
        GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b')
        Order By MONTH(created_at)) D on D.MonthNo=M.id;");


        $AmountM = implode(',', collect ($monthlyDatas)->pluck('TotalAmount')->toArray());
        $OrderedAmountM = implode(',', collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
        $DeliveredAmountM = implode(',', collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
        $CanceledAmountM = implode(',', collect ($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

        $TotalAmount = collect($monthlyDatas)->sum("TotalAmount");
        $TotalOrderedAmount = collect($monthlyDatas) ->sum( 'TotalOrderedAmount');
        $TotalDeliveredAmount = collect($monthlyDatas) ->sum("TotalDeliveredAmount");
        $TotalCanceledAmount = collect($monthlyDatas) ->sum("TotalCanceledAmount");
        return view('admin.index', compact('orders', 'dashboardDatas', 'monthlyDatas', 'AmountM', 'OrderedAmountM', 'DeliveredAmountM', 'CanceledAmountM', 'TotalAmount', 'TotalOrderedAmount', 'TotalDeliveredAmount', 'TotalCanceledAmount'));

    }

    // Brand
    public function brands()
    {
        $brands = Brand::orderBy('id','DESC')->paginate(10);
        return view("admin.brands",compact('brands'));
    }

    public function add_brand()
    {
        return view("admin.brand-add");
    }

    public function brand_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateBrandThumbailImage($image,$file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status','Record has been added successfully !');
    }

    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit',compact('brand'));
    }

    public function update_brand(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,'.$request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->slug = $request->slug;
        if($request->hasFile('image'))
        {
            if (File::exists(public_path('uploads/brands').'/'.$brand->image)) {
                File::delete(public_path('uploads/brands').'/'.$brand->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;
            $this->GenerateBrandThumbailImage($image,$file_name);
            $brand->image = $file_name;
        }
        $brand->save();
        return redirect()->route('admin.brands')->with('status','Record has been updated successfully !');
    }

    public function GenerateBrandThumbailImage($image,$imageName)
    {
        $destinationPath = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124, 124, 'top');
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function brand_delete($id)
    {
        $brand = Brand::find($id);
        if (File::exists(public_path('uploads/brands').'/'.$brand->image)) {
            File::delete(public_path('uploads/brands').'/'.$brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status','Record has been deleted successfully !');
    }

    // Category
    public function categories()
    {
        $categories = Category::orderBy('id','DESC')->paginate(10);
        return view("admin.categories",compact('categories'));
    }

    public function category_add()
    {
        return view("admin.category-add");
    }

    public function category_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateCategoryThumbailImage($image,$file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status','Record has been added successfully !');
    }

    public function GenerateCategoryThumbailImage($image,$imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124, 124, 'top');
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit',compact('category'));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$request->id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $category = Category::find($request->id);
        $category->name = $request->name;
        $category->slug = $request->slug;
        if($request->hasFile('image'))
        {
            if (File::exists(public_path('uploads/categories').'/'.$category->image)) {
                File::delete(public_path('uploads/categories').'/'.$category->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;
            $this->GenerateCategoryThumbailImage($image,$file_name);
            $category->image = $file_name;
        }
        $category->save();
        return redirect()->route('admin.categories')->with('status','Record has been updated successfully !');
    }

    public function category_delete($id)
    {
        $category = Category::find($id);
        if (File::exists(public_path('uploads/categories').'/'.$category->image)) {
            File::delete(public_path('uploads/categories').'/'.$category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('status','Record has been deleted successfully !');
    }


    // Product
    public function products()
    {
        $products = Product::orderBy('created_at','DESC')->paginate(10);
        return view("admin.products",compact('products'));
    }

    public function product_add()
    {
        $categories = Category::Select('id','name')->orderBy('name')->get();
        $brands = Brand::Select('id','name')->orderBy('name')->get();
        return view("admin.product-add",compact('categories','brands'));
    }

    public function product_store(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:products,slug',
            'category_id'=>'required',
            'brand_id'=>'required',
            'short_description'=>'required',
            'description'=>'required',
            'regular_price'=>'required',
            'sale_price'=>'required',
            'SKU'=>'required',
            'stock_status'=>'required',
            'featured'=>'required',
            'quantity'=>'required',
            'image'=>'required|mimes:png,jpg,jpeg|max:2048'
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image'))
        {
            $image = $request->file('image');
            $imageName = $current_timestamp.'.'.$image->extension();

            $this->GenerateProductThumbnailImage($image,$imageName);
            $product->image = $imageName;
        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if($request->hasFile('images'))
        {
            $allowedfileExtension=['jpg','png','jpeg'];
            $files = $request->file('images');
            foreach($files as $file){
                $gextension = $file->getClientOriginalExtension();
                $gcheck=in_array($gextension,$allowedfileExtension);
                if($gcheck)
                {
                    $gfilename = $current_timestamp . "-" . $counter . "." . $gextension;
                    $this->GenerateProductThumbnailImage($file,$gfilename);
                    array_push($gallery_arr,$gfilename);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(', ', $gallery_arr);
        }
        $product->images = $gallery_images;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;
        $product->save();
        return redirect()->route('admin.products')->with('status','Record has been added successfully !');
    }

    public function GenerateProductThumbnailImage($image,$imageName)
    {
        $destinationPathThumbnail = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image->path());
        $img->cover(540, 689, 'top');
        $img->resize(540, 689, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
        $img->resize(140, 140, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPathThumbnail.'/'.$imageName);
    }

    public function product_edit($id)
    {
        $product = Product::find($id);
        $categories = Category::Select('id','name')->orderBy('name')->get();
        $brands = Brand::Select('id','name')->orderBy('name')->get();
        return view('admin.product-edit',compact('product','categories','brands'));
    }

    public function product_update(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:products,slug,'.$request->id,
            'category_id'=>'required',
            'brand_id'=>'required',
            'short_description'=>'required',
            'description'=>'required',
            'regular_price'=>'required',
            'sale_price'=>'required',
            'SKU'=>'required',
            'stock_status'=>'required',
            'featured'=>'required',
            'quantity'=>'required',
            'image'=>'mimes:png,jpg,jpeg|max:2048'
        ]);

        $product = Product::find($request->id);
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;

        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image'))
        {
            if (File::exists(public_path('uploads/products').'/'.$product->image)) {
                File::delete(public_path('uploads/products').'/'.$product->image);
            }
            if (File::exists(public_path('uploads/products/thumbnails').'/'.$product->image)) {
                File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
            }

            $image = $request->file('image');
            $imageName = $current_timestamp.'.'.$image->extension();
            $this->GenerateProductThumbnailImage($image,$imageName);
            $product->image = $imageName;

        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if($request->hasFile('images'))
        {
            foreach(explode(",",$product->images) as $ofile)
            {
                if (File::exists(public_path('uploads/products').'/'.$ofile)) {
                    File::delete(public_path('uploads/products').'/'.$ofile);
                }

                if (File::exists(public_path('uploads/products/thumbnails').'/'.$ofile)) {
                    File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
                }
            }

            $allowedfileExtension=['jpg','png','jpeg'];
            $files = $request->file('images');
            foreach($files as $file){
                $gextension = $file->getClientOriginalExtension();
                $gcheck=in_array($gextension,$allowedfileExtension);
                if($gcheck)
                {
                    $gfilename = $current_timestamp . "-" . $counter . "." . $gextension;
                    $this->GenerateProductThumbnailImage($file,$gfilename);
                    array_push($gallery_arr,$gfilename);
                    $counter = $counter + 1;
                }
            }
            $gallery_images = implode(', ', $gallery_arr);
            $product->images = $gallery_images;
        }
        $product->save();
        return redirect()->route('admin.products')->with('status','Record has been updated successfully !');
    }

    public function product_delete($id)
    {
        $product = Product::find($id);
        if (File::exists(public_path('uploads/products').'/'.$product->image)) {
            File::delete(public_path('uploads/products').'/'.$product->image);
        }
        if (File::exists(public_path('uploads/products/thumbnails').'/'.$product->image)) {
            File::delete(public_path('uploads/products/thumbnails').'/'.$product->image);
        }

        foreach(explode(",",$product->images) as $ofile)
        {
            if (File::exists(public_path('uploads/products').'/'.$ofile)) {
                File::delete(public_path('uploads/products').'/'.$ofile);
            }
            if (File::exists(public_path('uploads/products/thumbnails').'/'.$ofile)) {
                File::delete(public_path('uploads/products/thumbnails').'/'.$ofile);
            }
        }
        $product->delete();
        return redirect()->route('admin.products')->with('status','Record has been deleted successfully !');
    }

    // Coupons
    public function coupons()
    {
        $coupons = Coupon::orderBy("expiry_date","DESC")->paginate(12);
        return view("admin.coupons",compact("coupons"));
    }

    public function add_coupon()
    {
        return view("admin.coupon-add");
    }

    public function add_coupon_store(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date'
        ]);

        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route("admin.coupons")->with('status','Record has been added successfully !');
    }


    public function coupon_edit($id)
    {
        $coupon = Coupon::find($id);
        return view('admin.coupon-edit',compact('coupon'));
    }

    public function update_coupon(Request $request)
    {
       $request->validate([
       'code' => 'required',
       'type' => 'required',
       'value' => 'required|numeric',
       'cart_value' => 'required|numeric',
       'expiry_date' => 'required|date'
       ]);

       $coupon = Coupon::find($request->id);
       $coupon->code = $request->code;
       $coupon->type = $request->type;
       $coupon->value = $request->value;
       $coupon->cart_value = $request->cart_value;
       $coupon->expiry_date = $request->expiry_date;
       $coupon->save();
       return redirect()->route('admin.coupons')->with('status','Record has been updated successfully !');
    }

    public function coupon_delete($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();
        return redirect()->route('admin.coupons')->with('status','Record has been deleted successfully !');
    }

    // Orders
    public function orders()
    {
        $orders = Order::orderBy('created_at','DESC')->paginate(12);
        return view("admin.orders",compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::find($order_id);
        $orderItems = OrderItem::where('order_id',$order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id',$order_id)->first();
        return view("admin.order-details",compact('order','orderItems','transaction'));
    }

    public function update_order_status(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = $request->order_status;
        if($request->order_status=='delivered')
        {
            $order->delivered_date = Carbon::now();
        }
        else if($request->order_status=='canceled')
        {
            $order->canceled_date = Carbon::now();
        }
        $order->save();
        if($request->order_status=='delivered')
        {
            $transaction = Transaction::where('order_id',$request->order_id)->first();
            $transaction->status = "approved";
            $transaction->save();
        }
        return back()->with("status", "Status changed successfully!");
    }

    //Slider
    public function slides()
    {
        $slides = Slide::orderBy('id','DESC')->paginate(12);
        return view("admin.slides", compact('slides'));
    }

    public function slide_add()
    {
        return view("admin.slide-add");
    }

    public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048'
        ]);
        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;
        $this->GenerateSlideThumbailImage($image,$file_name);
        $slide->image = $file_name;
        $slide->save();
        return redirect()->route('admin.slides')->with("status", "Slide adder successfully!");
    }

    public function slide_edit($id)
    {
        $slide = Slide::find($id);
        return view("admin.slide-edit", compact('slide'));
    }

    public function slide_update(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);
        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if($request->hasFile('image'))
        {
            if (File::exists(public_path('uploads/sildes').'/'.$slide->image)) {
                File::delete(public_path('uploads/slides').'/'.$slide->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;
            $this->GenerateSlideThumbailImage($image,$file_name);
            $slide->image = $file_name;
        }

        $slide->save();
        return redirect()->route('admin.slides')->with("status", "Slide upload successfully!");
    }

    public function GenerateSlideThumbailImage($image,$imageName)
    {
        $destinationPath = public_path('uploads/slides');
        $img = Image::read($image->path());
        $img->cover(400, 690, 'top');
        $img->resize(400, 690, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function slide_delete($id)
    {
        $slide = Slide::find($id);
        if (File::exists(public_path('uploads/slides').'/'.$slide->image)) {
            File::delete(public_path('uploads/slides').'/'.$slide->image);
        }
        $slide->delete();
        return redirect()->route('admin.slides')->with('status','Slide has been deleted successfully !');
    }

    //Contact
    public function contacts()
    {
        $contacts = Contact::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.contacts', compact('contacts'));
    }

    public function contact_delete($id)
    {
        $contact = Contact::find($id);
        $contact->delete();
        return redirect()->route('admin.contacts')->with('status','Message has been deleted successfully !');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Product::where('name', 'LIKE', "%{$query}%")->get()->take(8);
        return response()->json($results);
    }

    public function users()
    {
        $users = User::where('utype', '!=', 'ADM') // Loại trừ utype là ADM
                     ->orderBy('created_at', 'desc')
                     ->paginate(10);

        return view('admin.users', compact('users'));
    }

    public function user_edit()
    {
        $user = User::find(request()->id);
        return view('admin.users-edit', compact('user'));
    }

    public function user_update(Request $request)
    {
        $user = User::find($request->id);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // Xác thực dữ liệu
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'required|digits:10',
            'password' => 'nullable|min:6|confirmed', // Kiểm tra mật khẩu và xác nhận
        ]);

        // Cập nhật dữ liệu người dùng
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;

        // Kiểm tra nếu cả password và password_confirmation được nhập
        if ($request->filled('password') && $request->filled('password_confirmation')) {
            if ($request->password === $request->password_confirmation) {
                $user->password = bcrypt($request->password);
            } else {
                return redirect()->back()->with('error', 'Password confirmation does not match.');
            }
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', 'User information updated successfully.');
    }

    public function setting()
    {
        return view('admin.setting');
    }

    public function updateSetting(Request $request)
    {
        // Xác thực các trường
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'mobile' => 'required|digits:10|unique:users,mobile,' . Auth::id(), // Kiểm tra trùng số điện thoại
            'old_password' => 'nullable|required_with:new_password|min:6',
            'new_password' => 'nullable|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Kiểm tra và cập nhật thông tin
        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;

        // Kiểm tra và cập nhật mật khẩu nếu có thay đổi
        if ($request->filled('old_password') && $request->filled('new_password')) {
            if (Hash::check($request->old_password, $user->password)) {
                $user->password = bcrypt($request->new_password);
            } else {
                return back()->withErrors(['old_password' => 'The old password is incorrect.']);
            }
        }

        // Lưu thay đổi
        $user->save();

        return redirect()->route('admin.setting')->with('success', 'Settings updated successfully.');
    }

}