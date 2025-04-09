<?php

namespace App\Http\Controllers\frontend\salesFinance\item;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductGroup;
use App\Models\ProductGroupProduct;
use App\Http\Requests\ProductGroupRequest;


class ProductGroupController extends Controller
{
    public function productGroupList()
    {
        $data['page'] = "item";
        $data['productGroups'] = ProductGroup::getProductGroupData(Auth::user()->home_id);
        return view('frontEnd.salesAndFinance.item.product_group', $data);
    }

    public function saveProductGroup(ProductGroupRequest $request)
    {
        // echo "<pre>";print_r($request->all());die;
        $validated = $request->validated();
        parse_str($request->input('FormData'), $formData);
        $productsData = json_decode($request->input('products'), true);
        $isUpdate = !empty($formData['id']);
        $saveData = ProductGroup::saveProductGroup($formData, Auth::user()->home_id, Auth::user()->id);

        if ($saveData) {
            // Check if products data exists
            if (!empty($productsData['products']) && is_array($productsData['products'])) {
                // Save the ProductGroupProduct data only if products are provided
                $saveProduct = ProductGroupProduct::saveProductGroupData($saveData->id, $productsData);
                if($isUpdate){
                    return response()->json([
                        'success' => true,
                        'message' =>'Product group updated successfully.',
                    ]);
                }else{
                    return response()->json([
                        'success' => (bool) $saveProduct,
                        'message' => $saveProduct ? 'Product group and products added successfully.' : 'Product Group products could not be added.',
                    ]);
                }
                
            } else {
                // No products data, return success for ProductGroup only
                return response()->json([
                    'success' => true,
                    'message' => 'Product group added successfully (no products provided).',
                ]);
            }
        } else {
            // If ProductGroup is not saved, return a failure response
            return response()->json([
                'success' => false,
                'message' => 'Product Group could not be added.',
            ]);

            // return response()->json(['errors' => $validator->errors()], 422);
        }
    }

    public function ProductGroupProductsList(Request $request){
        $home_id=Auth::user()->home_id;
        $ProductGroupProduct=ProductGroupProduct::getProductGroupProductData($home_id)->where('product_group_id',$request->id)->get();
        return response()->json(['data'=>$ProductGroupProduct]);
    }
    public function ProductGroupProductsdetails(Request $request){
        $home_id=Auth::user()->home_id;
        $ProductGroupProduct=ProductGroup::with('productGroupProduct')->where('id',$request->id)->get();
        return response()->json(['data'=>$ProductGroupProduct]);
    }
    
}
