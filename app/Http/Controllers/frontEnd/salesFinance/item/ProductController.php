<?php

namespace App\Http\Controllers\frontEnd\salesFinance\Item;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Product_category;
use App\Models\Product;
use App\Models\Construction_tax_rate;
use App\Models\Construction_account_code;
use App\Models\ProductImage;
use App\Models\ProductGroup;
// use App\Customer;
use App\Models\Customer;
use App\User;

class ProductController extends Controller
{
    function productlist(Request $request){
        $page = "item";
        $path = $request->path();
        $segments = explode('/', $path);
        $lastSegment = end($segments);
        $users = User::getHomeUsers(Auth::user()->home_id);
        $product = Product::where('home_id',Auth::user()->home_id)->where('adder_id',Auth::user()->id)->where('status',1)->where('deleted_at',NULL)->get();
        $product_inactive = Product::where('home_id',Auth::user()->home_id)->where('adder_id',Auth::user()->id)->where('status',0)->where('deleted_at',NULL)->get();
        $product_categories = Product_category::with('parent', 'children')->where('home_id',Auth::user()->home_id)->where('status',1)->where('deleted_at',NULL)->get();
        $productlist_array = array();
        if($lastSegment == "active"){
            $productstatus = 1;
        }else if($lastSegment == "inactive"){
            $productstatus = 0;
        }else{
            $productstatus = 1;
        }
        $productlist = Product::where('home_id',Auth::user()->home_id)->where('adder_id',Auth::user()->id)->where('status',$productstatus)->where('deleted_at',NULL)->orderBy('id','DESC')->get();
        foreach($productlist as $product_val){
            $arr['id'] = $product_val->id;
            $arr['customer_only'] = $product_val->customer_only;
            if($product_val->customer_only!=""){
                $arr['customer_name'] = Customer::where('id',$product_val->customer_only)->value('name');
            }else{
                $arr['customer_name'] = "";
            }            
            $arr['cat_id'] = $product_val->cat_id;
            if($product_val->cat_id!=""){
                $arr['cat_name'] = Product_category::where('id',$product_val->cat_id)->value('name');
            }else{
                $arr['cat_name'] = "";
            }
            $arr['product_type'] = $product_val->product_type;
            switch ($product_val->product_type) {
                case "1":
                  $pro_type_name = "Product";
                  break;
                case "2":
                    $pro_type_name = "Services";
                  break;
                case "3":
                    $pro_type_name = "Consumable";
                  break;
                default:
                    $pro_type_name = "";
              }
            $arr['product_type_name'] = $pro_type_name;
            $arr['product_name'] = $product_val->product_name;
            $arr['cost_price'] = $product_val->cost_price;
            $arr['margin'] = $product_val->margin;
            $arr['price'] = $product_val->price;
            $arr['tax_rate'] = $product_val->tax_rate;
            if($product_val->tax_rate!=""){
                $arr['tax_rate_value'] = $product_val->tax_rate;
            }else{
                $arr['tax_rate_value'] = "";
            }
            $arr['qty'] = $product_val->qty;
            $arr['description'] = $product_val->description;
            $arr['product_code'] = $product_val->product_code;
            $arr['show_temp'] = $product_val->show_temp;
            $arr['bar_code'] = $product_val->bar_code;
            $arr['tax_id'] = $product_val->tax_id;
            $arr['nominal_code'] = $product_val->nominal_code;
            $arr['sales_acc_code'] = $product_val->sales_acc_code;
            $arr['purchase_acc_code'] = $product_val->purchase_acc_code;
            $arr['expense_acc_code'] = $product_val->expense_acc_code;
            $arr['location'] = $product_val->location;
            $arr['attachment'] = $product_val->attachment;
            $arr['status'] = $product_val->status;
            $arr['created_at'] = $product_val->created_at;
            $arr['updated_at'] = $product_val->updated_at;
            array_push($productlist_array,$arr);
        }
        $product_list_array = $productlist_array;

        
        // $product_category = Product_category::with('parent', 'children')->where('home_id',Auth::user()->home_id)->where('deleted_at',NULL)->get();
        // // print_r($product_categories);
        // // die;->where('deleted_at',"NULL")
        // $productcategory_array = array();
        // foreach($product_category as $value){
        //     $arr['id'] = $value->id;
        //     $arr['home_id'] = $value->home_id;
        //     $arr['product_name'] = $value->name;
        //     $arr['level'] = $value->full_category;
        //     $arr['cat_id'] = $value->cat_id;
        //     $arr['status'] = $value->status;
        //     $arr['number_of_products'] = 0;
        //     $arr['number_of_children'] = Product_category::where('home_id',Auth::user()->home_id)->where('cat_id',$value->id)->where('deleted_at',NULL)->count();
        //     array_push($productcategory_array,$arr);
        // }
        // $product_categories_list = $productcategory_array;
        return view('frontEnd.salesAndFinance.item.product', compact('product', 'page', 'lastSegment', 'users', 'product_inactive','product_categories','product_list_array'));
    }

    function productcategorylist(Request $request){
        $product_categories = Product_category::with('parent', 'children')
        ->where('home_id', Auth::user()->home_id)
        ->where('status', 1)
        ->where('deleted_at', NULL)
        ->get();
        return response()->json($product_categories);
    }

    function taxratelist(Request $request){
        $taxrate = Construction_tax_rate::where('home_id', Auth::user()->home_id)
        ->where('status', 1)
        ->where('deleted_at', NULL)
        ->get();
        return response()->json($taxrate);
    }
    function account_code(Request $request){
        $account_code = Construction_account_code::where('home_id', Auth::user()->home_id)
        ->where('status', 1)
        ->where('deleted_at', NULL)
        ->get();
        return response()->json($account_code);
    }

    function generateproductcode(Request $request){
        $product_name = strtoupper($request->productname);
        $pro_name = Product::genrateproductcode($product_name);
        return response()->json($pro_name);
    }

    function saveTaxrateData(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        //echo Product_category::checkproductcategoryname($request->name,$request->productCategoryID);
        //die;
       // Call the model's method to save product category data
        if(Construction_tax_rate::checkTaxRatename($request->taxratename,$request->taxrateID)==0){
            $saveData = Construction_tax_rate::saveTaxRateData($request->all(), $request->taxrateID);

            // Return the appropriate response
            return response()->json([
                'success' => (bool)1,
                'message' => $saveData ? 'The Tax Rate has been saved successfully.' : 'Tax Rate could not be created.',
                'lastid' => $saveData->id
            ]);
        }else{
            return response()->json([
                'success' => 0,
                'message' => 'This Tax Rate already exist.',
                'lastid' => 0
            ]);
        }
        
    }
    function saveproductdata(Request $request){
        $validator = Validator::make($request->all(), [
            'product_name' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        //echo Product_category::checkproductcategoryname($request->name,$request->productCategoryID);
        //die;
       // Call the model's method to save product category data
        //if(Product::checkTaxRatename($request->taxratename,$request->taxrateID)==0){
            $saveData = Product::saveProductdata($request->all(), $request->productID);
            if($request->attachment!=''){
                $imageName = time().'.'.$request->attachment->extension();      
                $request->attachment->move(public_path('product'), $imageName); 
                $productimg = new ProductImage;
                $productimg->productID = $saveData->id;
                $productimg->imagename = $imageName;
                $productimg->save();
            }
            
            
            // $data['attachment'] = $imageName;
            // Return the appropriate response
            return response()->json([
                'success' => (bool) $saveData,
                'message' => $saveData ? 'The Product has been saved successfully.' : 'Product could not be created.',
                'lastid' => $saveData->id
            ]);
        // }else{
        //     return response()->json([
        //         'success' => 0,
        //         'message' => 'This Tax Rate already exist.',
        //         'lastid' => 0
        //     ]);
        // }
        
    }
    function changeProductStatus(Request $request){
        $changestatus = Product::changeProductStatus($request->id, $request->status);
        return response()->json([
            'success' => (bool) $changestatus,
            'message' => $changestatus ? 'Product status changed successfully.' : 'Product status could not be changed.'
        ]);
    }

    function deleteProduct(Request $request){
        //echo $request->id;
        $productID = explode(",",$request->id);
        $delete = Product::deleteProduct($productID);
        return response()->json([
            'success' => (bool) $delete,
            'message' => $delete ? 'Product deletd successfully.' : 'Product could not be deletd.'
        ]);
    }

    function getproductdata(Request $request){
        $product_id = $request->product_id;
        $product_val = Product::where('id',$product_id)->first();
        $arr['id'] = $product_val->id;
        $arr['customer_only'] = $product_val->customer_only;
        if($product_val->customer_only!=""){
            $arr['customer_name'] = Customer::where('id',$product_val->customer_only)->value('name');
        }else{
            $arr['customer_name'] = "";
        }            
        $arr['cat_id'] = $product_val->cat_id;
        if($product_val->cat_id!=""){
            $arr['cat_name'] = Product_category::where('id',$product_val->cat_id)->value('name');
        }else{
            $arr['cat_name'] = "";
        }
        $arr['product_type'] = $product_val->product_type;
        switch ($product_val->product_type) {
            case "1":
                $pro_type_name = "Product";
                break;
            case "2":
                $pro_type_name = "Services";
                break;
            case "3":
                $pro_type_name = "Consumable";
                break;
            default:
                $pro_type_name = "";
            }
        $arr['product_type_name'] = $pro_type_name;
        $arr['product_name'] = $product_val->product_name;
        $arr['cost_price'] = $product_val->cost_price;
        $arr['margin'] = $product_val->margin;
        $arr['price'] = $product_val->price;
        $arr['tax_rate'] = $product_val->tax_rate;
        if($product_val->tax_rate!=""){
            $arr['tax_rate_value'] = $product_val->tax_rate;
            $arr['tax_rate_name'] = Construction_tax_rate::where('tax_rate', $product_val->tax_rate)->value('name');
        }else{
            $arr['tax_rate_value'] = "";
            $arr['tax_rate_name'] = "";
        }
        $arr['qty'] = $product_val->qty;
        $arr['description'] = $product_val->description;
        $arr['product_code'] = $product_val->product_code;
        $arr['show_temp'] = $product_val->show_temp;
        $arr['bar_code'] = $product_val->bar_code;

        $arr['tax_id'] = $product_val->tax_id;
        if($product_val->tax_id!=""){
            $arr['ptax_rate_value'] = Construction_tax_rate::where('id', $product_val->tax_id)->value('tax_rate');
            $arr['ptax_rate_name'] = Construction_tax_rate::where('id', $product_val->tax_id)->value('name');
        }else{
            $arr['ptax_rate_value'] = "";
            $arr['ptax_rate_name'] = "";
        }
        $arr['nominal_code'] = $product_val->nominal_code;
        $arr['sales_acc_code'] = $product_val->sales_acc_code;
        if($product_val->sales_acc_code!=""){
            $arr['sales_acc_name'] = Construction_account_code::where('id',$product_val->sales_acc_code)->value('name');
        }else{
            $arr['sales_acc_name'] = "";
        }
        $arr['purchase_acc_code'] = $product_val->purchase_acc_code;
        if($product_val->purchase_acc_code!=""){
            $arr['purchase_acc_name'] = Construction_account_code::where('id',$product_val->purchase_acc_code)->value('name');
        }else{
            $arr['purchase_acc_name'] = "";
        }
        $arr['expense_acc_code'] = $product_val->expense_acc_code;
        if($product_val->purchase_acc_code!=""){
            $arr['expense_acc_name'] = Construction_account_code::where('id',$product_val->expense_acc_code)->value('name');
        }else{
            $arr['expense_acc_name'] = "";
        }
        $arr['location'] = $product_val->location;
        $arr['attachment'] = $product_val->attachment;
        $arr['status'] = $product_val->status;
        $arr['created_at'] = $product_val->created_at;
        $arr['updated_at'] = $product_val->updated_at;
        return response()->json($arr);
    }

    public function getproductimage(Request $request){
        $image = ProductImage::getallproductimage($request->product_id);
        return response()->json($image);
    }

    public function saveproductimages(Request $request){
        $saveData = ProductImage::saveproductimages($request->all());
        // Return the appropriate response
        return response()->json([
            'success' => (bool) $saveData,
            'message' => $saveData ? 'The Product Image has been saved successfully.' : 'Product Image could not be created.',
            'lastid' => $saveData->id
        ]);
    }

    public function deleteproductimage(Request $request){
        ProductImage::deleteproductimage($request->productimgid);
    }

    public function getProductList(Request $request){
        // echo "<pre>";print_r($request->all());die;
        if($request->type == 4){
            $data = ProductGroup::getProductGroupData(Auth::user()->home_id);   
        }else{
            $data = Product::getProductList($request->type);
        }

        return response()->json([
            'success' => (bool) $data,
            'type'=>$request->type ?? null,
            'data' => $data ? $data : 'No data.'
        ]);
    }

    public function getProductCounts(){
        $data['product'] = Product::getProductListCountType(1);
        $data['service'] = Product::getProductListCountType(2);
        $data['consumable'] = Product::getProductListCountType(3);
        $product_group= ProductGroup::getProductGroupData(Auth::user()->home_id); 
         $data['product_group']=count($product_group);
        return response()->json([
            'success' => (bool) $data,
            'data' => $data ? $data : 'No data.'
        ]);
    }  
    
    public function searchProduct(Request $request){
        $query = $request->get('query');

        // Fetch data from database (replace `YourModel` with your actual model)
        $results = Product::where('product_name', 'LIKE', "%{$query}%")->select('id', 'product_name')->get();

        // Return the results as JSON or a rendered view
        return response()->json($results); // For JSON response
    }

    public function getProductFromId(Request $request){
       $data =  Product::getProductFromId($request->id);

       return response()->json([
        'success' => (bool) $data,
        'data' => $data ? $data : 'No data.'
    ]);

    }

}
