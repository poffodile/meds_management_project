<?php

namespace App\Http\Controllers\backEnd\systemManage;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\DynamicFormBuilder, App\DynamicForm, App\DynamicFormLocation;
use DB, Auth;
use Hash;
use Illuminate\Support\Facades\Mail;

class FormBuilderController extends Controller
{
    public function index(Request $request)
    {

        $home_id = Session::get('scitsAdminSession')->home_id;


        if (empty($home_id)) {
            return redirect('admin/')->with('error', NO_HOME_ERR);
        }
        $home_id = Session::get('scitsAdminSession')->home_id;
        //$forms = DynamicForm::select('id','home_id','title')->where('home_id',$home_id)->orderBy('title','asc');
        $forms = DynamicFormBuilder::select('id', 'title')
            ->where('home_id', $home_id)
            ->orderBy('title', 'asc');


        $search = '';

        if (isset($request->limit)) {
            $limit = $request->limit;
            Session::put('page_record_limit', $limit);
        } else {
            if (Session::has('page_record_limit')) {
                $limit = Session::get('page_record_limit');
            } else {
                $limit = 25;
            }
        }
        if (isset($request->search)) {
            $search     = trim($request->search);
            $forms      = $forms->where('title', 'like', '%' . $search . '%');
        }
        $forms          = $forms->paginate($limit);

        /*foreach($forms as $form){
            $form->form_builder_id = DynamicFormBuilder::getDynamicFormId($form->id);
        }*/

        //echo '<pre>'; print_r($forms); die;

        $page = 'form-builder';
        // echo "<pre>";  print_r($forms); die(); 
        // echo "<pre>";  print_r(compact('forms'));  die();
        return view('backEnd/systemManage/formBuilder/form_builder', compact('page', 'limit', 'forms', 'search')); //users.blade.php
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            // dd($request);
            $data = $request->input();
            // dd($request);
            // echo "<pre>"; print_r($data); die;

            if (isset($data['formdata'])) {
                $data['formdata'] = is_array(json_decode($data['formdata'])) ? array_values(json_decode($data['formdata'])) : array();
            } else {
                return redirect()->back()->with('error', 'No input field added in the form.');
            }

            // $image = $request->file('form_image');

            // // Create a unique file name
            // $imageName = time() . '.' . $image->extension();

            // // Move the file to the public/images directory
            // $image->move(public_path('images/formio'), $imageName);

            if(isset($request->is_imageUpload)){
                $is_imageUpload = $request->is_imageUpload;
            } else {
                $is_imageUpload = null;
            }



            foreach (explode(',', $data['form_home_ids']) as $homeId) {
                $home_id            = $homeId;
                $form               = new DynamicFormBuilder;
                $form->home_id      = $home_id;
                $form->home_ids     = $data['form_home_ids'];
                $form->title        = $data['form_title'];
                $form->detail       = $data['form_detail'];
                $form->location_ids = $data['form_location_ids'];
                $form->pattern      = json_encode($data['formdata']);
                $form->alert_field  =  $data['alert_field'];
                $form->is_image     = $is_imageUpload;
                $form->reminder_day =  $data['form_reminder_day'];
                $form->send_to      =  $data['send_to'];

                if (!$form->save()) {
                    return redirect()->back()->with('error', 'Some error occurred. Please try again later.');
                }

                // if($form->save()) {
                //     return redirect('admin/form-builder')->with('success', 'New Form added successfully.');
                // } else {
                //     return redirect()->back()->with('error','Some error occurred. Please try after sometime.'); 
                // }
            }

            return redirect('admin/form-builder')->with('success', 'New Form added successfully.');
        }

        $locations = DynamicFormLocation::get()->toArray();

        $page = 'form-builder';
        return view('backEnd.systemManage.formBuilder.form_builder_form', compact('page', 'locations'));
    }

    public function edit(Request $request, $form_builder_id = null)
    {

        $home_id = Session::get('scitsAdminSession')->home_id;

        if ($request->isMethod('post')) {

            $data = $request->input();
            //echo '<pre>'; print_r($data); die;

            if (isset($data['formdata'])) {
                $datatableedit = DynamicFormBuilder::where('id', $data['dynamic_form_builder_id'])->first();
                $data['formdata'] = is_array(json_decode($data['formdata'])) ? array_values(json_decode($data['formdata'])) : json_decode($datatableedit->pattern);
                //    /is_array($assoc_array)? array_values($assoc_array): array();
            } else {
                return redirect()->back()->with('error', 'No input field added in the form.');
            }
            $form  = DynamicFormBuilder::where('id', $data['dynamic_form_builder_id'])->first();

            if ($data['form_reminder_day'] === NULL) {
                $form_reminder_day = "";
            } else {
                $form_reminder_day = $data['form_reminder_day'];
            }

            // if (isset($request->form_image)) {
            //     $image = $request->file('form_image');
            //     $imageName = time() . '.' . $image->extension();

            //     // Move the file to the public/images directory
            //     $image->move(public_path('images/formio'), $imageName);
            // } else {
            //     $imageName = $request->form_img;
            // }

            
            if(isset($request->is_imageUpload)){
                $is_imageUpload = $request->is_imageUpload;
            } else {
                $is_imageUpload = null;
            }

            if (!empty($form)) {

                $form->title        = $data['form_title'];
                $form->detail       = $data['form_detail'];
                $form->location_ids = $data['form_location_ids'];
                $form->pattern      = json_encode($data['formdata']);
                $form->alert_field  =  $data['alert_field'];
                $form->home_ids     =  $data['form_home_ids'];
                $form->is_image     =  $is_imageUpload;
                $form->reminder_day  =  $form_reminder_day;
                $form->send_to       =  $data['send_to'];
                $form->logtype      =  $data['logtypes'];

                if ($form->save()) {
                    return redirect('admin/form-builder')->with('success', 'Form updated successfully.');
                } else {
                    return redirect()->back()->with('error', 'Some error occurred. Please try after sometime.');
                }
            } else {
                return redirect('admin/')->with('error', UNAUTHORIZE_ERR);
            }
        }

        /*$form_default = DynamicFormBuilder::where('tag',$form_tag)->first();
        if(!empty($form_default)){            
            
            $form_builder = DynamicForm::where('form_default_id',$form_default->id)
                                    ->where('home_id',$home_id)
                                    ->first();
            
            if(!empty($form_builder))                           
        }*/

        $form = DynamicFormBuilder::where('id', $form_builder_id)->where('home_id', $home_id)->first();
        // echo "<pre>"; print_r($form); die;
        //$form             = DynamicForm::where('id', $form_id)->first();
        //echo '<pre>'; print_r($form); die;
        if (!empty($form)) {

            $form_info        = $this->_view($form->id);
            $dynamic_formdata = $form_info['formdata'];
            $already_fields   = $form_info['total_fields'];
        } else {
            return redirect('admin/')->with('error', UNAUTHORIZE_ERR);
        }

        //$dynamic_formdata = implode();
        /*echo "<pre>";
        print_r($already_fields);
        die;*/
        $locations = DynamicFormLocation::get()->toArray();
        $page = 'form-builder';
        return view('backEnd/systemManage/formBuilder/form_builder_form', compact('form', 'page', 'dynamic_formdata', 'already_fields', 'locations'));
    }

    public function _view($form_builder_id = null)
    {

        $home_id = Session::get('scitsAdminSession')->home_id;
        $form = DynamicFormBuilder::where('id', $form_builder_id)
            //->join('form_default as fd','fd.id','form_builder.form_default_id')
            ->where('dynamic_form_builder.home_id', $home_id)
            ->first();
        //echo '<pre>'; print_r($form); die;

        if (!empty($form)) {
            //$form->pattern = $form->pattern;
            // echo "<pre>";
            // print_r($form);     
            // die;        
            //$formdata     = '';
            $total_fields = 0;

            // foreach ($form->pattern as $key => $value) { //print_r($value); die;
            //     $total_fields++;
            //     $field_name   = $value->label;
            //     $field_type   = $value->column_type;
            //     //print_r($field_type); //die;   
            //     $column_name  = $value->column_name;
            //     //print_r($column_name); //die;  
            //     $column_type  = $value->column_type; 
            //     //print_r($column_type); //die;  
            //     if($field_type == 'Textbox') { //echo "12"; die;
            //         /*$formdata .= '<div class="form-group cus-field"><label class="col-lg-2 col-md-2 col-sm-2 control-label" type="'.$field_type.'"> '.$field_name.' </label><div class="col-lg-8 col-md-8"><input type="text" name="'.$column_name.'" class="form-control trans" placeholder="" readonly><input type="hidden" name="formdata['.$key.'][label]" value="'.$field_name.'"><input type="hidden" name="formdata['.$key.'][column_name]" value="'.$column_name.'"><input type="hidden" name="formdata['.$key.'][column_type]" value="'.$field_type.'"></div><span class="group-ico field-remove-btn m-r-10 flot-lft"><i class="fa fa-minus"></i></span>  <div class="col-md-1 col-sm-1 col-xs-1 m-t-5 p-l-10"><span class="sort-sp"><i class="fa fa-sort"></i></span></div> </div>';*/

            //         $formdata .= 
            //             '<div class="form-group cus-field"> 
            //                 <div class="col-lg-2 col-md-2 col-xs-12 m-b-15"> 
            //                     <input name="formdata['.$key.'][label]" value="'.$field_name.'" type="text" class="form-control" title="Edit"> 
            //                 </div> 
            //                 <div class="col-lg-8 col-md-8 col-xs-12 m-b-15">
            //                     <input type="text" name="'.$column_name.'" class="form-control trans" placeholder="" readonly=""> 
            //                     <input type="hidden" name="formdata['.$key.'][column_name]" value="'.$column_name.'">
            //                     <input type="hidden" name="formdata['.$key.'][column_type]" value="'.$field_type.'">
            //                 </div> 
            //                 <div class="col-md-2 col-sm-12 col-xs-12 p-0 r-p-15">
            //                     <span class="group-ico field-remove-btn m-r-10 flot-lft" title="Remove"> <i class="fa fa-minus"></i> </span> 
            //                     <span class="sort-sp" title="sort"> <i class="fa fa-sort"></i> </span>
            //                 </div> 
            //             </div>';

            //     } elseif ($field_type == 'Selectbox') {
            //         $options = '';
            //         $option_name = '';
            //         $option_value = '';
            //         $opt = '';
            //         $j = 0;

            //         //for($i=1; $i <= $option_count; $i++){
            //         foreach($value->select_options as $select_option){
            //             //echo '<pre>'; print_r($select_option);
            //             $select_option = (array)$select_option;
            //             //print_r($se['option_name']); die;

            //             $option_name    = $select_option['option_name'];
            //             $option_value   = $select_option['option_value'];

            //             //if( ($option_name !== '') && ($option_value !== '') ) {            
            //                 $options .= '<option value="'.$option_value.'">'.$option_name.'</option>';

            //                 //hidden input fields for select options  saving
            //                 $opt .= '<input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_name]" value="'.$option_name.'"> <input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_value]" value="'.$option_value.'">';
            //                 $j++;
            //             //}
            //         }

            //         /*<div class="form-group cus-field"><label class="col-lg-2 col-md-2 control-label" type="'.$field_type.'"> '.$field_name.' </label>*/

            //         $formdata .= 
            //             '<div class="form-group cus-field"> 
            //                 <div class="col-lg-2 col-md-2 col-xs-12 m-b-15"> 
            //                     <input name="formdata['.$key.'][label]" value="'.$field_name.'" type="text" class="form-control"> 
            //                 </div> 
            //                 <div class="col-lg-8 col-md-8 col-xs-12 m-b-15">
            //                     <select class="form-control" name="'.$column_name.'"> '.$options.' </select>
            //                     <input type="hidden" name="formdata['.$key.'][column_name]" value="'.$column_name.'">
            //                     <input type="hidden" name="formdata['.$key.'][column_type]" value="'.$field_type.'"> '.$opt.' 
            //                 </div> 
            //                 <div class="col-md-2 col-sm-12 col-xs-12 p-0 r-p-15">
            //                     <span class="group-ico field-remove-btn m-r-10 flot-lft" title="Remove"><i class="fa fa-minus"></i></span> 
            //                     <span class="sort-sp" title="sort"><i class="fa fa-sort"></i></span>
            //                 </div> 
            //             </div>';

            //     } else if($field_type == 'Textarea') { 
            //         $formdata .= 
            //             '<div class="form-group cus-field"> 
            //                 <div class="col-lg-2 col-md-2 col-xs-12 m-b-15"> 
            //                     <input name="formdata['.$key.'][label]" value="'.$field_name.'" type="text" class="form-control"> 
            //                 </div> 
            //                 <div class="col-lg-8 col-md-8 col-xs-12 m-b-15">
            //                     <textarea name="'.$column_name.'" class="form-control trans" placeholder="" readonly rows="2"></textarea>
            //                     <input type="hidden" name="formdata['.$key.'][column_name]" value="'.$column_name.'">
            //                     <input type="hidden" name="formdata['.$key.'][column_type]" value="'.$field_type.'">
            //                 </div>  
            //                 <div class="col-md-2 col-sm-12 col-xs-12 p-0 r-p-15">
            //                     <span class="group-ico field-remove-btn m-r-10 flot-lft" title="Remove"><i class="fa fa-minus"></i></span>
            //                     <span class="sort-sp" title="sort"><i class="fa fa-sort"></i></span>
            //                 </div> 
            //             </div>'; 

            //     } else if($field_type == 'Date') {
            //         $formdata .= 
            //             '<div class="form-group cus-field"> 
            //                 <div class="col-lg-2 col-md-2 col-xs-12 m-b-15"> 
            //                     <input name="formdata['.$key.'][label]" value="'.$field_name.'" type="text" class="form-control"> 
            //                 </div> 
            //                 <div class="col-lg-8 col-md-8 col-xs-12 m-b-15">  
            //                     <div data-date-format="dd-mm-yyyy" data-date="" class="input-group date dpYears">  
            //                         <input name="date" readonly="" value="" size="16" class="form-control trans" type="text"> 
            //                         <input type="hidden" name="formdata['.$key.'][column_name]" value="'.$column_name.'"> 
            //                         <input type="hidden" name="formdata['.$key.'][column_type]" value="'.$field_type.'"> 
            //                         <span class="input-group-btn "> <button class="btn btn-primary calndr-btn" type="button"><i class="fa fa-calendar"></i></button> </span>  
            //                     </div> 
            //                 </div>  
            //                 <div class="col-md-2 col-sm-12 col-xs-12 p-0 r-p-15"> 
            //                     <span class="group-ico field-remove-btn m-r-10 flot-lft" title="Remove"><i class="fa fa-minus"></i></span>
            //                     <span class="sort-sp" title="sort"><i class="fa fa-sort"></i></span>
            //                 </div> 
            //             </div>';

            //     } else if($field_type == 'Checkbox') {

            //         $checkbox = '';
            //         $checkbox_name = '';
            //         $checkbox_value = '';
            //         $chk = '';
            //         $j = 0;

            //         foreach ($value->select_checkboxs as $select_checkbox) {

            //             $select_checkbox = (array)$select_checkbox;

            //             $checkbox_name   = $select_checkbox['checkbox_name'];

            //             $chk .='<div class="col-md-6">
            //                         <div class="checkbox ">
            //                             <label>
            //                                 <input type="checkbox" name="formdata['.$key.'][select_checkboxs]['.$j.'][checkbox_name]" class="form-control chk_hobs trans" value="'.$checkbox_name.'" readonly> '.$checkbox_name.'
            //                                 <input type="hidden" name="formdata['.$key.'][select_checkboxs]['.$j.'][checkbox_name]" value="'.$checkbox_name.'">
            //                             </label>
            //                         </div>
            //                     </div>';
            //             $j++;
            //         }

            //         $formdata .= '<div class="form-group cus-field">
            //                         <div class="col-lg-2 col-md-2 col-xs-12 m-b-15">
            //                             <input name="formdata['.$key.'][label]" value="'.$field_name.'" class="form-control" type="text">
            //                             <input type="hidden" name="formdata['.$key.'][column_name]" value="'.$field_name.'">
            //                             <input type="hidden" name="formdata['.$key.'][column_type]" value="'.$field_type.'"> 
            //                         </div>
            //                         <div class="col-lg-8 col-md-8 col-xs-12 m-b-15">
            //                             <div class="wrap_checks">
            //                                 <div class="row">
            //                                     '.$chk.'
            //                                 </div>
            //                             </div>
            //                         </div>
            //                         <div class="col-md-2 col-sm-12 col-xs-12 p-0 r-p-15">
            //                             <span class="group-ico field-remove-btn m-r-10 flot-lft"><i class="fa fa-minus"></i></span>
            //                             <span class="sort-sp"><i class="fa fa-sort"></i></span>
            //                         </div>
            //                     </div>';


            //     } else {
            //         $formdata .='';
            //     }
            // }

            $result['response'] = true;
            $result['total_fields'] = $total_fields;
            $result['title']    = $form->title;
            //$result['detail']   = $plan->detail;
            $result['formdata'] = $form->pattern;
            //  echo $result['formdata'] ;
            //  die();

        } else {
            $result['response'] = false;
        }

        return $result;
    }

    public function delete($form_id)
    {

        $home_id = Session::get('scitsAdminSession')->home_id;
        if (!empty($form_id)) {
            $delete_form = DynamicFormBuilder::where('id', $form_id)->where('home_id', $home_id)->delete();
            DynamicForm::where('form_builder_id', $form_id)->where('home_id', $home_id)->delete();
            if (!empty($delete_form)) {
                return redirect('admin/form-builder')->with('success', 'Form deleted Successfully.');
            } else {
                return redirect('admin/')->with('error', UNAUTHORIZE_ERR);
            }
        } else {
            return redirect('admin/')->with('error', 'Form does not exists.');
        }
    }
}
