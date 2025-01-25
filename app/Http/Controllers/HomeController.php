<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slide;
use App\Models\Category;
use App\Models\Product;
use App\Models\Contact;



class HomeController extends Controller
{
    public function index()
    {
        $slides = Slide::where('status', 1)->get()->take(3);
        $categories = Category::orderBy('name')->get();
        $sproducts = Product::whereNotNull('sale_price')->where('sale_price', '<>', '')->inRandomOrder()->get()->take(8);
        $fproducts = Product::where('featured', 1)->get()->take(8);
        return view('index', compact('slides', 'categories', 'sproducts', 'fproducts'));
    }

    public function contact()
    {
        return view('contact');
    }

    public function contact_store(Request $request )
    {
        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email',
            'phone' => 'required|numeric|digits:10',
            'comment' => 'required',
        ]);

        $contact = new Contact();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->phone = $request->phone;
        $contact->comment = $request->comment;
        $contact->save();
        return redirect()->back()->with('status','Your messge has been sent successfully');
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        // Trả về rỗng nếu không có giá trị tìm kiếm
        if (!$query) {
            return response()->json([]);
        }

        // Truy vấn với giới hạn 8 kết quả
        $results = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%") // Nếu muốn tìm theo mô tả
            ->limit(8) // Giới hạn kết quả trả về
            ->get();

        // Trả kết quả dưới dạng JSON
        return response()->json($results);
    }

    public function about()
    {
        return view('about');
    }


}
