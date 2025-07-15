<?php

namespace App\Http\Controllers\frontEnd\salesFinance\asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\DepreciationType;
use App\Models\AssetCategory;
use App\Models\AssetRegistration;
use App\Home;
use Validator;

class AssetController extends Controller
{
    public function asset_category(Request $request){
        $data['page']='assets';
        $data['list']=AssetCategory::getAllAssetCategory()->get();
        return view('frontEnd.salesAndFinance.asset.assetCategoryList',$data);
    }
    public function asset_register(Request $request){

        $data['page']='assets';
        $cat_id=base64_decode($request->cat);
        $query=AssetRegistration::getAllAssetRegistration();
        $selected_cat_id=0;
        if(isset($cat_id) && $cat_id !=''){
            $selected_cat_id=$cat_id;
            $query->where('asset_type',$cat_id);
        }
        $data['list']=$query->orderBy('id','desc')->get();
        // echo "<pre>";print_r($data['list']);die;
        $data['AssetCategoryList']=AssetCategory::getAllAssetCategory()->where('status',1)->get();
        $data['DepreciationTypeList']=DepreciationType::getDepreciationType()->where('status',1)->get();
        $data['selected_cat_id']=$selected_cat_id;
        return view('frontEnd.salesAndFinance.asset.assetRegisterList',$data);
    }
    public function asset_regiser_add(Request $request){
        $id=base64_decode($request->key);
        $data['register']=AssetRegistration::find($id);
        // echo "<pre>";print_r($data['register']);die;
        $data['page']='assets';
        $data['AssetCategoryList']=AssetCategory::getAllAssetCategory()->where('status',1)->get();
        $data['DepreciationTypeList']=DepreciationType::getDepreciationType()->where('status',1)->get();
        return view('frontEnd.salesAndFinance.asset.assetRegisterForm',$data);
    }
    public function asset_regiser_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'asset_name'=>'required',
            'asset_type'=>'required',
            'date'=>'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        
        try {
            $request['company_id'] = Home::getCompanyIdFromHome();            
            $asset_registration=AssetRegistration::saveAssetRegistration($request->all());
            if($request->id == ''){
                return response()->json(['success' => true,'message'=>'The Aseet Registration has been saved succesfully.', 'data' => $asset_registration]);
            }else{
                return response()->json(['success' => true,'message'=>'The Aseet Registration has been updated succesfully.', 'data' => $asset_registration]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function depreciation_type(Request $request){
        $data['page'] = 'assets';
        $data['list'] = DepreciationType::getDepreciationType()->get();
        return view('frontEnd.salesAndFinance.asset.depreciation_typeList',$data);
    }
    public function asset_category_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'name'=>'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        
        try {
            $asset_category = AssetCategory::saveAssetCategory($request->all());
            if($request->id == ''){
                return response()->json(['success' => true,'message'=>'The Aseet Category has been saved successfully.', 'data' => $asset_category]);
            }else{
                return response()->json(['success' => true,'message'=>'The Aseet Category has been updated successfully.', 'data' => $asset_category]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function depreciation_type_save(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $validator = Validator::make($request->all(), [
            'name'=>'required',
            'percentage'=>'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['vali_error' => $validator->errors()->first()]);
        }
        
        try {
    
            $DepreciationType=DepreciationType::saveDepreciationType($request->all());
            if($request->id == ''){
                return response()->json(['success' => true,'message'=>'The Depreciation Type has been saved succesfully.', 'data' => $DepreciationType]);
            }else{
                return response()->json(['success' => true,'message'=>'The Depreciation Type has been updated succesfully.', 'data' => $DepreciationType]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function asset_register_search(Request $request){
        // echo "<pre>";print_r($request->all());die;
        $startDate=Carbon::parse($request->start_date)->format('Y-m-d');
        $endDate=Carbon::parse($request->end_date)->format('Y-m-d');
        $query = AssetRegistration::getAllAssetRegistration();
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }
        $list = $query->get();
        return response()->json(['success' => true,'message'=>'Search List.', 'data' => $list]);
    }
    
    public function asset_register_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try{
            AssetRegistration::find($request->id)->update(['deleted_at' => now()]);
            return response()->json(['success'=>true,'message'=>'Deleted Successfully done']);
        }catch (\Exception $e) {
            // Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function asset_category_delete(Request $request){
        // echo "<pre>";print_r($request->all());die;
        try{
            AssetCategory::find($request->id)->update(['deleted_at' => now()]);
            return response()->json(['success'=>true,'message'=>'Deleted Successfully done']);
        }catch (\Exception $e) {
            // Log::error('Error saving Tag: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
