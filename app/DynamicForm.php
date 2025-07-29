<?php

namespace App;

use DB, Auth, Session;
use App\LogBook, App\CategoryFrontEnd, App\ServiceUserLogBook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;


class DynamicForm extends Model //FormBuilder
{
    protected $table = 'dynamic_form';

    public static function showForm($form_builder_id = null, $service_user_id = null)
    { //show empty form 

        //here $service_user_id is used to get care team, staff and contacts of a yp, if its send to value is yes
        //search for the customized user dynamic form, if present
        $home_id   =  Auth::user()->home_id;
        $home_idme = DB::table('service_user')->where('id', $service_user_id)->value('home_id');
        $admin_id = DB::table('home')->where('id', $home_idme)->value('admin_id');
        $image_id = DB::table('admin')->where('id', $admin_id)->value('image');
        $form      =  DynamicFormBuilder::where('id', $form_builder_id)
            ->where('home_id', $home_id)
            ->first();
        // dd($form);
        $form_pattern = (isset($form->pattern)) ? $form->pattern : '';
        //echo '<pre>'; print_r($image_id); die;

        if (!empty($form_pattern)) {
            //for showing selected date of dynamic form 
            $form_loc_ids   = explode(',', $form->location_ids);
            $form_type      = (in_array('5', $form_loc_ids)) ? 'MFC' : '';
            $mfc_today_date = ($form_type == 'MFC') ? date('d-m-Y') : '';
            $form_pattern   = json_decode($form_pattern);
            // echo "<pre>";  print_r($form->pattern);   die;

            $formdata = ' ';
            //static fields
            $static_fields    = ' <div class="col-md-12 col-sm-12 col-xs-12">
                                        <div class="below-divider"></div>
                                        <div class="prient-btn">
                                        <input type="button" onclick="PrintDiv(this)" data-id="' . $image_id . '"  data-image="' . $form->image . '"  value="download PDF" />
                                        </div>
                                </div>
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <h3 class="m-t-0 m-b-20 clr-blue fnt-20 dynamic_form_h3"> Fill Form Details </h3>
                                 </div>
                                 <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Title: </label>
                                         <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
                                             <div class="input-group popovr">
                                                 <input type="text" class="form-control trans static_title" placeholder="" name="title" value="" />
                                             </div>
                                         </div>
                                     </div>
                                 </div>

                                 <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Date: </label>
                                         <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
                                           <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date="" class="input-group date dpYears">
                                             <input name="date" size="16" readonly="" class="form-control trans" type="text" value="" >
                                             <span class="input-group-btn add-on">
                                               <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                             </span>
                                           </div>
                                         </div>
                                     </div>
                                 </div>

                                 <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Time: </label>
                                         <div class="col-md-10 col-sm-12 col-xs-12 r-p-0">
                                             <div class="input-group popovr">
                                                 <input type="text" class="form-control trans static_title" placeholder="" name="time" value="" />
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Detail: </label>
                                         <div class="col-md-10 col-sm-12 col-xs-12 r-p-0">
                                             <div class="input-group popovr">
                                                 <textarea class="form-control trans" placeholder="" name="details" ></textarea>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                ';
            $formdata .= $static_fields;
                                 
            $label_col_val = '1';
            $inp_col       = 10;
            $form_pattern = str_replace("'", "`", $form->pattern);
            //conditional fields
            $formdata .= "<input type='hidden' value='" . $form_builder_id . "' id='formid'>";
            $formdata .= "<input type='hidden' value='" . $home_id . "' id='home_id'>";
            $formdata .= "<input type='hidden' value='.$form_pattern.' id='getdatamodel'>";

            if ($form->is_image == "1") {
                $formdata .= '  <div class="col-md-12 col-sm-12 col-xs-12 form-group">
                                    <div class="uploadimg222">
                                        <input type="hidden" name="formImage" class="uploded_image" id="imageName">                           
                                        <div id="previewContainer" class="formImageHide" style="overflow: hidden; margin-bottom: 20px; height: 300px; display :none;">
                                            <img id="formImagePreview" style=" width:100%; object-fit: contain; height: 100%;" alt="Image Preview">  
                                        </div>
                                        <input type="file" multiple="false" accept="image/*" name="form_image" class="form-control finput" id="finput" onchange="upload()">
                                    </div> 
                                </div>';
            }

            //  echo "<pre>";   print_r($form->alert_field);  echo "<pre>";
            if ($form->alert_field == '1') {
                $static_field = '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                    <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Alert: </label>
                                    <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
                                        <div class="select-style">
                                             <select name="alert_status">
                                                <option value="0">No</option>
                                                <option value="1">Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                    <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Review Date: <!-- alert date --></label>
                                    <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
                                      <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date="" class="input-group date dpYears">
                                        <input name="alert_date" value="" size="16" readonly="" class="form-control" type="text">
                                        <span class="input-group-btn add-on">
                                          <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                      </div>
                                    </div>
                                </div>
                            </div>
                            ';

                $formdata .= $static_field;
            }

            $care_team = DB::table('su_care_team')
                ->select('id', 'job_title_id', 'name', 'email', 'phone_no', 'image', 'address')
                ->where('service_user_id', $service_user_id)
                ->where('is_deleted', '0')
                ->orderBy('id', 'desc')
                ->get();
            // echo "<pre>"; print_r($care_team); die;

            $su_contacts = DB::table('su_contacts')
                ->select('id', 'name', 'email')
                ->where('service_user_id', $service_user_id)
                ->where('is_deleted', '0')
                ->orderBy('id', 'desc')
                ->get();
            // echo "<pre>"; print_r($su_contacts); die;
            if ($form->send_to == 'Y') {
                $static_field = '
                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                        <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Send To</label>

                        <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-12 r-p-0">
                            <select name="send_to[]" class="send_to demo-default form-control" multiple="" placeholder="Select a person...">
                                ';

                $static_field .= '<optgroup label="Care team">';
                foreach ($care_team as $team_mem) {
                    $static_field .= '<option value="ct-' . $team_mem->id . '">' . $team_mem->name . '</option>';
                }
                $static_field .= '</optgroup>
                                <optgroup label="Contacts">';
                foreach ($su_contacts as $su_contact) {
                    $static_field .= '<option value="sc-' . $su_contact->id . '">' . $su_contact->name . '</option>';
                }
                $static_field .= '</optgroup>';

                $static_field .= '</select>
                        </div>
                    </div>
                </div>
                ';
            }
            $formdata .= "<div class='col-md-12 col-sm-12 col-xs-12 cog-panel' id='formiotest'></div>";
          
            $formdata .= '';


            $result['response']         = true;
            $result['form_builder_id']  = $form_builder_id;
            $result['pattern']          = $formdata;
            // $result['image']            = asset('public/images/formio/' . $form->image);
            // $result['imageName']        = $form->image;
        } else {
            $result['response']     = false;
        }
        return $result;
    }

    public static function showFormWithValue($dynamic_form_id = null, $enable = false)
    { 
        //show filled form

        /* Note:
            the form fields will be shown according to the latest form pattern 
        */
        if (Auth::check()) {
            $home_id  = Auth::user()->home_id;
        } else if (Session::has('scitsAdminSession')) {
            $home_id = Session::get('scitsAdminSession')->home_id;
        } else {
            $result['response'] = false;
            return $result;
        }

        $home_idme = DB::table('dynamic_form')->where('id', $dynamic_form_id)->value('home_id');
        // echo "<pre>";  print_r($home_idme); die;
        $admin_id  = DB::table('home')->where('id', $home_idme)->value('admin_id');
        $image_id  = DB::table('admin')->where('id', $admin_id)->value('image');

        $form_info    = DynamicForm::select('dynamic_form.pattern_data', 'dynamic_form.form_builder_id', 'dynamic_form.date', 'dynamic_form.created_at', 'u.name', 'dynamic_form.title', 'dynamic_form.time', 'dynamic_form.details', 'dynamic_form.alert_status', 'dynamic_form.form_builder_id', 'dynamic_form.service_user_id', 'dynamic_form.image_path')
            ->join('user as u', 'u.id', 'dynamic_form.user_id')
            ->where('dynamic_form.id', $dynamic_form_id)
            // ->where('su.home_id',$home_id)
            ->where('dynamic_form.home_id', $home_id)
            ->first();

            // dd($form_info);
        $form_values  = array();

        if (!empty($form_info)) {

            $form_values = $form_info->pattern_data;
            $form_values = trim($form_values);

            // if(!empty($form_values)){ 
            //     $form_values = json_decode($form_values);
            // }

            $form_builder_id = $form_info->form_builder_id;



        }

        //first get the form default id from tag
        $form_builder   =   DynamicFormBuilder::where('id', $form_builder_id)->where('home_id', $home_id)->first();

        if (!empty($form_builder)) {

            //$form_builder->pattern = json_decode($form_builder->pattern);
            // echo "<pre>";
            //  print_r($form_values);
            //  die;
            //static fields
            if ($enable == true) {
                $disabled = '';
            } else {
                $disabled = 'disabled';
            }

            $form_date = '';
            if (!empty($form_info->date)) {
                $form_date = date('d-m-Y', strtotime($form_info->date));
            }
            // echo $form_date; die;
            $static_fields    = '<div class="col-md-12 col-sm-12 col-xs-12">
                                    <h3 class="m-t-0 m-b-20 clr-blue fnt-20 dynamic_form_h3">Details </h3>
                                </div>
                                <div class="prient-btn">
                                        <input type="button" onclick="PrintDivwithvalue(this)" data-id="" value="download PDF" />
                                        </div>
                                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                        <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Staff Created: </label>
                                        <div class="col-md-10 col-sm-10 col-xs-12 p-r-0">
                                            <div class="input-group popovr">
                                                <input type="text" class="form-control trans" placeholder="" name="" value="' . $form_info->name . ' (' . date('d-m-Y h:i a', strtotime($form_info->created_at)) . ')" ' . $disabled . ' readonly/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Title: </label>
                                         <div class="col-md-10 col-sm-10 col-xs-12 p-r-0">
                                             <div class="input-group popovr">
                                                 <input type="text" class="form-control trans static_title" placeholder="" '.$disabled.' name="title" value="'.$form_info->title.'" readonly/>
                                             </div>
                                         </div>
                                     </div>
                                 </div>

                                 <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Date: </label>
                                         <div class="col-md-10 col-sm-10 col-xs-12 p-r-0">
                                           <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date="" class="input-group date dpYears">
                                             <input name="date" size="16" readonly="" class="form-control trans" type="text" '.$disabled.' value="'.date('d-m-Y h:i a', strtotime($form_info->date)).'" readonly>
                                             <span class="input-group-btn add-on">
                                               <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                             </span>
                                           </div>
                                         </div>
                                     </div>
                                 </div>

                                 <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Time: </label>
                                         <div class="col-md-10 col-sm-12 col-xs-12 p-r-0">
                                             <div class="input-group popovr">
                                                 <input type="text" class="form-control trans static_title" placeholder="" '.$disabled.' name="time" value="'. $form_info->time .'" readonly/>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Detail: </label>
                                         <div class="col-md-10 col-sm-12 col-xs-12 p-r-0">
                                             <div class="input-group popovr">
                                                <textarea class="form-control trans" placeholder="" '.$disabled.' name="details" readonly>'. $form_info->details .'</textarea>
                                             </div>
                                         </div>
                                     </div>
                                 </div>

                             ';

            $formdata = '';
            $formdata .= $static_fields;
            $total_fields = 0;
            $formdata .= "<input type='hidden' value='" . $dynamic_form_id . "' id='dynamic_form_idformio'>";


            if ($form_builder->is_image == "1") {
                if ($form_info->image_path !== null) {
                    $formdata .= '<div class="col-md-12 col-sm-12 col-xs-12 form-group">
                                    <div class="uploadimg222"> 
                                        <input type="hidden" name="formImage" class="uploded_image" id="imageName">                           
                                        <input type="hidden" name="formImage2" class="" value="' . $form_info->image_path . '">                           
                                        <div id="previewContainer2" style="overflow: hidden; margin-bottom: 20px; height: 300px;">';
                    // Check if a previous image exists
                    if (!empty($form_info->image_path)) {
                        $formdata .= '<img src="' . url("/public/" . $form_info->image_path) . '" alt="Uploaded Image" id="previousImage" style=" width:100%; object-fit: contain; height: 100%;">';
                    }

                    $formdata .= '      </div>
                                        <input type="file" multiple="false" accept="image/*" name="form_image" class="form-control finput" disabled id="finput2" onchange="uploadImageFun()">
                                    </div>
                                </div>';
                } else {
                    $formdata .= '<div class="col-md-12 col-sm-12 col-xs-12 form-group">
                        <div class="uploadimg222">
                            <input type="hidden" name="formImage" class="uploded_image" id="imageName">                           
                            <div id="previewContainer" class="formImageHide" style="overflow: hidden; margin-bottom: 20px; height: 300px; display :none;">
                                <img id="formImagePreview" style=" width:100%; object-fit: contain; height: 100%;" alt="Image Preview">  
                            </div>
                            <input type="file" multiple="false" accept="image/*" name="form_image" class="form-control finput" disabled id="finput" onchange="upload()">
                        </div> 
                    </div>';
                }
            }

            //echo '<pre>'; print_r($static_fields); die;

            $inp_col = 10;

            //echo '<pre>'; print_r( $form_builder->pattern); die;
            // foreach($form_builder->pattern as $key => $value){

            //     $field_label = $value->label;
            //     $column_name = $value->column_name;
            //     $field_type  = $value->column_type;  

            //     $match_found = 0;
            //     foreach($form_values as $key => $value2){

            //         $form_column     = $key; 
            //         $form_column_val = $value2;
            //         if($column_name == $form_column){

            //             $field_value = $form_column_val;  
            //             $total_fields++;
            //             $match_found = 1;

            //             if($field_type == 'Textbox'){
            //                 $formdata .= '
            //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
            //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                                     <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
            //                                         <div class="input-group popovr">
            //                                             <input name="formdata['.$column_name.']" value="'.$field_value.'" type="text" class="form-control trans" '.$disabled.' />
            //                                         </div>
            //                                     </div>
            //                                 </div>
            //                             </div>';
            //             //p-l-30
            //             } else if($field_type == 'Selectbox'){
            //                 $options = '';
            //                 $option_name = '';
            //                 $option_value = '';
            //                 $opt = '';
            //                 $j = 0;
            //                 ///alert(option_count);
            //                 //for($i=1; $i <= $option_count; $i++){
            //                 foreach($value->select_options as $select_option){
            //                     //echo '<pre>'; print_r($select_option);
            //                     $select_option = (array)$select_option;
            //                     //print_r($se['option_name']); die;

            //                     $option_name    = $select_option['option_name'];
            //                     $option_value   = $select_option['option_value'];

            //                     $selected = '';
            //                     if($form_column_val == $option_value){
            //                         $selected = 'selected';
            //                     } 

            //                     //if( ($option_name !== '') && ($option_value !== '') ) {            
            //                         $options .= '<option value="'.$option_value.'" '.$selected.'>'.$option_name.'</option>';

            //                         $opt .= '<input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_name]" value="'.$option_name.'"> <input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_value]" value="'.$option_value.'">';
            //                         $j++;
            //                     //}
            //                 }

            //                 $formdata .= '
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-10 ">
            //                                 <div class="select-style">
            //                                     <select name="formdata['.$column_name.']" '.$disabled.' >
            //                                         '.$options.'
            //                                     </select>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>'; 

            //             } else if($field_type == 'Textarea'){
            //                 $formdata .='
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
            //                                 <div class="input-group popovr">
            //                                     <textarea name="formdata['.$column_name.']" class="form-control trans" '.$disabled.' >'.$field_value.'</textarea>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>';

            //             } else if($field_type == 'Date'){
            //                 $formdata .='
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
            //                                 <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date dpYears">
            //                                     <input name="formdata['.$column_name.']" type="text" value="'.$field_value.'" size="16" class="form-control trans" readonly="">
            //                                     <span class="input-group-btn add-on">
            //                                         <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
            //                                     </span>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>';

            //             } else if($field_type == 'Checkbox'){

            //                 //print_r($field_value); die;

            //                 $checkbox       = '';
            //                 $checkbox_name  = '';
            //                 $checkbox_value = '';
            //                 $chk = '';
            //                 $j   = 0;

            //                 foreach($value->select_checkboxs as $select_checkbox){

            //                     //print_r($form_column_val); die;
            //                     $select_checkbox = (array)$select_checkbox;

            //                     $checkbox_name  = $select_checkbox['checkbox_name'];


            //                     $checked = '';
            //                     if(in_array($checkbox_name, $form_column_val)){
            //                         $checked = 'checked';
            //                     } 
            //                     // echo 'checked='.$checked; die;

            //                     $chk .= '<div class="col-md-6">
            //                                 <div class="checkbox ">
            //                                     <label>
            //                                         <input class="trans" name="formdata['.$column_name.'][]" value="'.$checkbox_name.'" type="checkbox" '.$checked.' '.$disabled.'>'.$checkbox_name.'
            //                                     </label>
            //                                 </div>
            //                             </div>';
            //                     $j++;
            //                 }

            //                 $formdata .= '
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0">
            //                                 <div class="wrap_checks">
            //                                     <div class="row">
            //                                         '.$chk.'
            //                                     </div>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>'; 

            //             } else{
            //                 $formdata .='';
            //             }                    
            //         }

            //     }

            //     if($match_found == 0){

            //             $field_value = '';  
            //             $total_fields++;

            //             if($field_type == 'Textbox'){
            //                 $formdata .= '
            //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
            //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                                     <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
            //                                         <div class="input-group popovr">
            //                                             <input name="formdata['.$column_name.']" value="'.$field_value.'" type="text" class="form-control trans" '.$disabled.' />
            //                                         </div>
            //                                     </div>
            //                                 </div>
            //                             </div>';

            //             } else if($field_type == 'Selectbox'){
            //                 $options = '';
            //                 $option_name = '';
            //                 $option_value = '';
            //                 $opt = '';
            //                 $j = 0;
            //                 ///alert(option_count);
            //                 //for($i=1; $i <= $option_count; $i++){
            //                 foreach($value->select_options as $select_option){
            //                     //echo '<pre>'; print_r($select_option);
            //                     $select_option = (array)$select_option;
            //                     //print_r($se['option_name']); die;

            //                     $option_name    = $select_option['option_name'];
            //                     $option_value   = $select_option['option_value'];

            //                     $selected = '';
            //                     /*if($form_column_val == $option_value){
            //                         $selected = 'selected';
            //                     }*/ 
            //                     $selected = '';

            //                     //if( ($option_name !== '') && ($option_value !== '') ) {            
            //                         $options .= '<option value="'.$option_value.'" '.$selected.'>'.$option_name.'</option>';

            //                         $opt .= '<input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_name]" value="'.$option_name.'"> <input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_value]" value="'.$option_value.'">';
            //                         $j++;
            //                     //}
            //                 }

            //                 $formdata .= '
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-10 ">
            //                                 <div class="select-style">
            //                                     <select name="formdata['.$column_name.']" '.$disabled.' >
            //                                         '.$options.'
            //                                     </select>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>'; 

            //             } else if($field_type == 'Textarea'){
            //                 $formdata .='
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
            //                                 <div class="input-group popovr">
            //                                     <textarea name="formdata['.$column_name.']" class="form-control trans" '.$disabled.' >'.$field_value.'</textarea>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>';

            //             } else if($field_type == 'Date'){
            //                 $formdata .='
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
            //                                 <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date dpYears">
            //                                     <input name="formdata['.$column_name.']" type="text" value="'.$field_value.'" size="16" class="form-control trans" readonly="">
            //                                     <span class="input-group-btn add-on">
            //                                         <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
            //                                     </span>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>';

            //             } else if($field_type == 'Checkbox') {

            //                 $checkbox       = '';
            //                 $checkbox_name  = '';
            //                 $checkbox_value = '';
            //                 $chk = '';
            //                 $j   = 0;

            //                 foreach ($value->select_checkboxs as $select_checkbox) {

            //                     $select_checkbox = (array)$select_checkbox;

            //                     $checkbox_name   = $select_checkbox['checkbox_name'];

            //                     $chk .= '<div class="col-md-6">
            //                                 <div class="checkbox ">
            //                                     <label>
            //                                         <input class="trans" name="formdata['.$column_name.'][]" value="'.$checkbox_name.'" type="checkbox" '.$disabled.'>'.$checkbox_name.'
            //                                     </label>
            //                                 </div>
            //                             </div>';

            //                     $j++;
            //                 }   

            //                 $formdata .= '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
            //                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                                         <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0">
            //                                             <div class="wrap_checks">
            //                                                 <div class="row">
            //                                                     '.$chk.'
            //                                                 </div>
            //                                             </div>
            //                                         </div>
            //                                     </div>
            //                             </div>';
            //             } 
            //             else{
            //                 $formdata .='';
            //             }

            //     }

            // }

            $selected = '';
            if ($form_info->alert_status == '1') {
                $selected = 'selected';
            }

            $alert_date = '';
            if (!empty($form_info->alert_date)) {
                $alert_date = date('d-m-Y', strtotime($form_info->alert_date));
            }
            if ($form_builder->alert_field == '1') {
                $static_field = '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                    <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Alert: </label>
                                    <div class="col-md-10 col-sm-10 col-xs-12 p-r-0">
                                        <div class="select-style" style="pointer-events:none">
                                            <select name="alert_status" ' . $disabled . '>
                                                <option value="0">No</option>
                                                <option value="1" ' . $selected . '>Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                    <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Alert Date: </label>
                                    <div class="col-md-10 col-sm-10 col-xs-12 p-r-0">
                                      <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date="" class="input-group date custom-input-class">
                                        <input name="alert_date" value="' . $alert_date . '" size="16" readonly="" class="form-control" type="text">
                                        <span class="input-group-btn add-on">
                                          <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                      </div>
                                    </div>
                                </div>
                            </div>
                            ';

                $formdata .= $static_field;
            }



            $result['response']         = true;
            $formdata .= "<div class='col-md-12 col-sm-12 col-xs-12' id='formioView'></div>";
            $result['form_builder_id']  = $form_info->form_builder_id;
            //$result['title']            = $form_builder->title;
            $result['service_user_id']  = $form_info->service_user_id;
            //$result['detail']     = `->detail;
            $result['form_data']        = $formdata;
        } else {
            $result['response']     = false;
        }
        return $result;
    }

    public static function showFormLogWithValue($dynamic_form_id = null, $enable = false)
    { 
        //show filled form

        /* Note:
            the form fields will be shown according to the latest form pattern 
        */
        if (Auth::check()) {
            $home_id  = Auth::user()->home_id;
        } else if (Session::has('scitsAdminSession')) {
            $home_id = Session::get('scitsAdminSession')->home_id;
        } else {
            $result['response'] = false;
            return $result;
        }

        $home_idme = DB::table('dynamic_form')->where('id', $dynamic_form_id)->value('home_id');
        // echo "<pre>";  print_r($home_idme); die;
        $admin_id  = DB::table('home')->where('id', $home_idme)->value('admin_id');
        $image_id  = DB::table('admin')->where('id', $admin_id)->value('image');

        $form_info    = DynamicForm::select('dynamic_form.pattern_data', 'dynamic_form.form_builder_id', 'dynamic_form.date', 'dynamic_form.created_at', 'u.name', 'dynamic_form.title', 'dynamic_form.time', 'dynamic_form.details', 'dynamic_form.alert_status', 'dynamic_form.form_builder_id', 'dynamic_form.service_user_id', 'dynamic_form.image_path')
            ->join('user as u', 'u.id', 'dynamic_form.user_id')
            ->join('log_book')
            ->where('dynamic_form.id', $dynamic_form_id)
            // ->where('su.home_id',$home_id)
            ->where('dynamic_form.home_id', $home_id)
            ->first();

            dd($form_info);
        $form_values  = array();

        if (!empty($form_info)) {

            $form_values = $form_info->pattern_data;
            $form_values = trim($form_values);

            // if(!empty($form_values)){ 
            //     $form_values = json_decode($form_values);
            // }

            $form_builder_id = $form_info->form_builder_id;

        }

        //first get the form default id from tag
        $form_builder   =   DynamicFormBuilder::where('id', $form_builder_id)->where('home_id', $home_id)->first();

        if (!empty($form_builder)) {

            //$form_builder->pattern = json_decode($form_builder->pattern);
            // echo "<pre>";
            //  print_r($form_values);
            //  die;
            //static fields
            if ($enable == true) {
                $disabled = '';
            } else {
                $disabled = 'disabled';
            }

            $form_date = '';
            if (!empty($form_info->date)) {
                $form_date = date('d-m-Y', strtotime($form_info->date));
            }
            // echo $form_date; die;
            $static_fields    = '<div class="col-md-12 col-sm-12 col-xs-12">
                                    <h3 class="m-t-0 m-b-20 clr-blue fnt-20 dynamic_form_h3">Details </h3>
                                </div>
                                <div class="prient-btn">
                                        <input type="button" onclick="PrintDivwithvalue(this)" data-id="" value="download PDF" />
                                        </div>
                                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                        <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Staff Created: </label>
                                        <div class="col-md-10 col-sm-10 col-xs-12 p-r-0">
                                            <div class="input-group popovr">
                                                <input type="text" class="form-control trans" placeholder="" name="" value="' . $form_info->name . ' (' . date('d-m-Y h:i a', strtotime($form_info->created_at)) . ')" ' . $disabled . ' readonly/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Title: </label>
                                         <div class="col-md-10 col-sm-10 col-xs-12 p-r-0">
                                             <div class="input-group popovr">
                                                 <input type="text" class="form-control trans static_title" placeholder="" '.$disabled.' name="title" value="'.$form_info->title.'" readonly/>
                                             </div>
                                         </div>
                                     </div>
                                 </div>

                                 <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Date: </label>
                                         <div class="col-md-10 col-sm-10 col-xs-12 p-r-0">
                                           <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date="" class="input-group date dpYears">
                                             <input name="date" size="16" readonly="" class="form-control trans" type="text" '.$disabled.' value="'.date('d-m-Y h:i a', strtotime($form_info->date)).'" readonly>
                                             <span class="input-group-btn add-on">
                                               <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                             </span>
                                           </div>
                                         </div>
                                     </div>
                                 </div>

                                 <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Time: </label>
                                         <div class="col-md-10 col-sm-12 col-xs-12 p-r-0">
                                             <div class="input-group popovr">
                                                 <input type="text" class="form-control trans static_title" placeholder="" '.$disabled.' name="time" value="'. $form_info->time .'" readonly/>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Detail: </label>
                                         <div class="col-md-10 col-sm-12 col-xs-12 p-r-0">
                                             <div class="input-group popovr">
                                                <textarea class="form-control trans" placeholder="" '.$disabled.' name="details" readonly>'. $form_info->details .'</textarea>
                                             </div>
                                         </div>
                                     </div>
                                 </div>

                             ';

            $formdata = '';
            $formdata .= $static_fields;
            $total_fields = 0;
            $formdata .= "<input type='hidden' value='" . $dynamic_form_id . "' id='dynamic_form_idformio'>";


            if ($form_builder->is_image == "1") {
                if ($form_info->image_path !== null) {
                    $formdata .= '<div class="col-md-12 col-sm-12 col-xs-12 form-group">
                                    <div class="uploadimg222"> 
                                        <input type="hidden" name="formImage" class="uploded_image" id="imageName">                           
                                        <input type="hidden" name="formImage2" class="" value="' . $form_info->image_path . '">                           
                                        <div id="previewContainer2" style="overflow: hidden; margin-bottom: 20px; height: 300px;">';
                    // Check if a previous image exists
                    if (!empty($form_info->image_path)) {
                        $formdata .= '<img src="' . url("/public/" . $form_info->image_path) . '" alt="Uploaded Image" id="previousImage" style=" width:100%; object-fit: contain; height: 100%;">';
                    }

                    $formdata .= '      </div>
                                        <input type="file" multiple="false" accept="image/*" name="form_image" class="form-control finput" disabled id="finput2" onchange="uploadImageFun()">
                                    </div>
                                </div>';
                } else {
                    $formdata .= '<div class="col-md-12 col-sm-12 col-xs-12 form-group">
                        <div class="uploadimg222">
                            <input type="hidden" name="formImage" class="uploded_image" id="imageName">                           
                            <div id="previewContainer" class="formImageHide" style="overflow: hidden; margin-bottom: 20px; height: 300px; display :none;">
                                <img id="formImagePreview" style=" width:100%; object-fit: contain; height: 100%;" alt="Image Preview">  
                            </div>
                            <input type="file" multiple="false" accept="image/*" name="form_image" class="form-control finput" disabled id="finput" onchange="upload()">
                        </div> 
                    </div>';
                }
            }

            //echo '<pre>'; print_r($static_fields); die;

            $inp_col = 10;

            //echo '<pre>'; print_r( $form_builder->pattern); die;
            // foreach($form_builder->pattern as $key => $value){

            //     $field_label = $value->label;
            //     $column_name = $value->column_name;
            //     $field_type  = $value->column_type;  

            //     $match_found = 0;
            //     foreach($form_values as $key => $value2){

            //         $form_column     = $key; 
            //         $form_column_val = $value2;
            //         if($column_name == $form_column){

            //             $field_value = $form_column_val;  
            //             $total_fields++;
            //             $match_found = 1;

            //             if($field_type == 'Textbox'){
            //                 $formdata .= '
            //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
            //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                                     <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
            //                                         <div class="input-group popovr">
            //                                             <input name="formdata['.$column_name.']" value="'.$field_value.'" type="text" class="form-control trans" '.$disabled.' />
            //                                         </div>
            //                                     </div>
            //                                 </div>
            //                             </div>';
            //             //p-l-30
            //             } else if($field_type == 'Selectbox'){
            //                 $options = '';
            //                 $option_name = '';
            //                 $option_value = '';
            //                 $opt = '';
            //                 $j = 0;
            //                 ///alert(option_count);
            //                 //for($i=1; $i <= $option_count; $i++){
            //                 foreach($value->select_options as $select_option){
            //                     //echo '<pre>'; print_r($select_option);
            //                     $select_option = (array)$select_option;
            //                     //print_r($se['option_name']); die;

            //                     $option_name    = $select_option['option_name'];
            //                     $option_value   = $select_option['option_value'];

            //                     $selected = '';
            //                     if($form_column_val == $option_value){
            //                         $selected = 'selected';
            //                     } 

            //                     //if( ($option_name !== '') && ($option_value !== '') ) {            
            //                         $options .= '<option value="'.$option_value.'" '.$selected.'>'.$option_name.'</option>';

            //                         $opt .= '<input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_name]" value="'.$option_name.'"> <input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_value]" value="'.$option_value.'">';
            //                         $j++;
            //                     //}
            //                 }

            //                 $formdata .= '
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-10 ">
            //                                 <div class="select-style">
            //                                     <select name="formdata['.$column_name.']" '.$disabled.' >
            //                                         '.$options.'
            //                                     </select>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>'; 

            //             } else if($field_type == 'Textarea'){
            //                 $formdata .='
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
            //                                 <div class="input-group popovr">
            //                                     <textarea name="formdata['.$column_name.']" class="form-control trans" '.$disabled.' >'.$field_value.'</textarea>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>';

            //             } else if($field_type == 'Date'){
            //                 $formdata .='
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
            //                                 <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date dpYears">
            //                                     <input name="formdata['.$column_name.']" type="text" value="'.$field_value.'" size="16" class="form-control trans" readonly="">
            //                                     <span class="input-group-btn add-on">
            //                                         <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
            //                                     </span>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>';

            //             } else if($field_type == 'Checkbox'){

            //                 //print_r($field_value); die;

            //                 $checkbox       = '';
            //                 $checkbox_name  = '';
            //                 $checkbox_value = '';
            //                 $chk = '';
            //                 $j   = 0;

            //                 foreach($value->select_checkboxs as $select_checkbox){

            //                     //print_r($form_column_val); die;
            //                     $select_checkbox = (array)$select_checkbox;

            //                     $checkbox_name  = $select_checkbox['checkbox_name'];


            //                     $checked = '';
            //                     if(in_array($checkbox_name, $form_column_val)){
            //                         $checked = 'checked';
            //                     } 
            //                     // echo 'checked='.$checked; die;

            //                     $chk .= '<div class="col-md-6">
            //                                 <div class="checkbox ">
            //                                     <label>
            //                                         <input class="trans" name="formdata['.$column_name.'][]" value="'.$checkbox_name.'" type="checkbox" '.$checked.' '.$disabled.'>'.$checkbox_name.'
            //                                     </label>
            //                                 </div>
            //                             </div>';
            //                     $j++;
            //                 }

            //                 $formdata .= '
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0">
            //                                 <div class="wrap_checks">
            //                                     <div class="row">
            //                                         '.$chk.'
            //                                     </div>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>'; 

            //             } else{
            //                 $formdata .='';
            //             }                    
            //         }

            //     }

            //     if($match_found == 0){

            //             $field_value = '';  
            //             $total_fields++;

            //             if($field_type == 'Textbox'){
            //                 $formdata .= '
            //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
            //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                                     <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
            //                                         <div class="input-group popovr">
            //                                             <input name="formdata['.$column_name.']" value="'.$field_value.'" type="text" class="form-control trans" '.$disabled.' />
            //                                         </div>
            //                                     </div>
            //                                 </div>
            //                             </div>';

            //             } else if($field_type == 'Selectbox'){
            //                 $options = '';
            //                 $option_name = '';
            //                 $option_value = '';
            //                 $opt = '';
            //                 $j = 0;
            //                 ///alert(option_count);
            //                 //for($i=1; $i <= $option_count; $i++){
            //                 foreach($value->select_options as $select_option){
            //                     //echo '<pre>'; print_r($select_option);
            //                     $select_option = (array)$select_option;
            //                     //print_r($se['option_name']); die;

            //                     $option_name    = $select_option['option_name'];
            //                     $option_value   = $select_option['option_value'];

            //                     $selected = '';
            //                     /*if($form_column_val == $option_value){
            //                         $selected = 'selected';
            //                     }*/ 
            //                     $selected = '';

            //                     //if( ($option_name !== '') && ($option_value !== '') ) {            
            //                         $options .= '<option value="'.$option_value.'" '.$selected.'>'.$option_name.'</option>';

            //                         $opt .= '<input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_name]" value="'.$option_name.'"> <input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_value]" value="'.$option_value.'">';
            //                         $j++;
            //                     //}
            //                 }

            //                 $formdata .= '
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-10 ">
            //                                 <div class="select-style">
            //                                     <select name="formdata['.$column_name.']" '.$disabled.' >
            //                                         '.$options.'
            //                                     </select>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>'; 

            //             } else if($field_type == 'Textarea'){
            //                 $formdata .='
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
            //                                 <div class="input-group popovr">
            //                                     <textarea name="formdata['.$column_name.']" class="form-control trans" '.$disabled.' >'.$field_value.'</textarea>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>';

            //             } else if($field_type == 'Date'){
            //                 $formdata .='
            //                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
            //                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
            //                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
            //                                 <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date dpYears">
            //                                     <input name="formdata['.$column_name.']" type="text" value="'.$field_value.'" size="16" class="form-control trans" readonly="">
            //                                     <span class="input-group-btn add-on">
            //                                         <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
            //                                     </span>
            //                                 </div>
            //                             </div>
            //                         </div>
            //                     </div>';

            //             } else if($field_type == 'Checkbox') {

            //                 $checkbox       = '';
            //                 $checkbox_name  = '';
            //                 $checkbox_value = '';
            //                 $chk = '';
            //                 $j   = 0;

            //                 foreach ($value->select_checkboxs as $select_checkbox) {

            //                     $select_checkbox = (array)$select_checkbox;

            //                     $checkbox_name   = $select_checkbox['checkbox_name'];

            //                     $chk .= '<div class="col-md-6">
            //                                 <div class="checkbox ">
            //                                     <label>
            //                                         <input class="trans" name="formdata['.$column_name.'][]" value="'.$checkbox_name.'" type="checkbox" '.$disabled.'>'.$checkbox_name.'
            //                                     </label>
            //                                 </div>
            //                             </div>';

            //                     $j++;
            //                 }   

            //                 $formdata .= '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
            //                                     <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
            //                                         <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
            //                                         <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0">
            //                                             <div class="wrap_checks">
            //                                                 <div class="row">
            //                                                     '.$chk.'
            //                                                 </div>
            //                                             </div>
            //                                         </div>
            //                                     </div>
            //                             </div>';
            //             } 
            //             else{
            //                 $formdata .='';
            //             }

            //     }

            // }

            $selected = '';
            if ($form_info->alert_status == '1') {
                $selected = 'selected';
            }

            $alert_date = '';
            if (!empty($form_info->alert_date)) {
                $alert_date = date('d-m-Y', strtotime($form_info->alert_date));
            }
            if ($form_builder->alert_field == '1') {
                $static_field = '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                    <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Alert: </label>
                                    <div class="col-md-10 col-sm-10 col-xs-12 p-r-0">
                                        <div class="select-style" style="pointer-events:none">
                                            <select name="alert_status" ' . $disabled . '>
                                                <option value="0">No</option>
                                                <option value="1" ' . $selected . '>Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
                                <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
                                    <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Alert Date: </label>
                                    <div class="col-md-10 col-sm-10 col-xs-12 p-r-0">
                                      <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date="" class="input-group date custom-input-class">
                                        <input name="alert_date" value="' . $alert_date . '" size="16" readonly="" class="form-control" type="text">
                                        <span class="input-group-btn add-on">
                                          <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                      </div>
                                    </div>
                                </div>
                            </div>
                            ';

                $formdata .= $static_field;
            }



            $result['response']         = true;
            $formdata .= "<div class='col-md-12 col-sm-12 col-xs-12' id='formioView'></div>";
            $result['form_builder_id']  = $form_info->form_builder_id;
            //$result['title']            = $form_builder->title;
            $result['service_user_id']  = $form_info->service_user_id;
            //$result['detail']     = `->detail;
            $result['form_data']        = $formdata;
        } else {
            $result['response']     = false;
        }
        return $result;
    }

    // public static function showFormWithValue($dynamic_form_id = null,$enable = false){ //show filled form

    //     // echo $dynamic_form_id; die;
    //     // echo $tag; echo "<br><br>"; 
    //     // echo "<pre>"; print_r($form_values); die;
    //     /* Note:
    //         the form fields will be shown according to the latest form pattern 
    //     */

    //     if(Auth::check()) {
    //         $home_id   = Auth::user()->home_id;
    //     } else if(Session::has('scitsAdminSession')) {
    //         $home_id = Session::get('scitsAdminSession')->home_id; 
    //     } else {
    //         $result['response']     = false;
    //         return $result;
    //     }

    //     // $form_info = DynamicForm::join('service_user as su','su.id','=','dynamic_form.service_user_id')
    //     //             ->join('user as u','u.id','dynamic_form.user_id')
    //     //             ->where('dynamic_form.id',$dynamic_form_id)
    //     //             ->where('su.home_id',$home_id)
    //     //             ->first()

    //     $form_info    = DynamicForm::select('dynamic_form.pattern_data','dynamic_form.form_builder_id','dynamic_form.date','dynamic_form.created_at','u.name','dynamic_form.title','dynamic_form.time','dynamic_form.details','dynamic_form.alert_status','dynamic_form.form_builder_id','dynamic_form.service_user_id')
    //                         // ->join('service_user as su','su.id','=','dynamic_form.service_user_id')  /*----- June 07,2018 (Akhil) ---*/
    //                         ->join('user as u','u.id','dynamic_form.user_id')
    //                         ->where('dynamic_form.id',$dynamic_form_id)
    //                         // ->where('su.home_id',$home_id)   /*----- June 07,2018 (Akhil) ---*/
    //                         // ->where('dynamic_form.home_id',$home_id) /*----- June 07,2018 (Akhil) ---*/
    //                         ->leftJoin('service_user as su', function($join) use($home_id) {
    //                             $join->on('su.id','=','dynamic_form.service_user_id');
    //                             $join->on('su.home_id','=',DB::raw("$home_id"));
    //                         })
    //                         ->orWhere('su.home_id',$home_id)
    //                         ->orWhere('su.home_id',null)
    //                         ->first();



    //     $form_values  = array();

    //     if(!empty($form_info)) {

    //         $form_values = $form_info->pattern_data;
    //         $form_values = trim($form_values);

    //         if(!empty($form_values)){ 
    //             $form_values = json_decode($form_values);
    //         }

    //         $form_builder_id = $form_info->form_builder_id;
    //     }

    //     //first get the form default id from tag
    //     $form_builder   = DynamicFormBuilder::where('id',$form_builder_id)
    //                             ->where('home_id',$home_id)
    //                             //->value('pattern');
    //                             ->first();

    //     if(!empty($form_builder)){

    //         $form_builder->pattern = json_decode($form_builder->pattern);
    //         // echo "<pre>";
    //         // print_r($form_builder->pattern);
    //         // die;
    //         //static fields
    //         if($enable == true) {
    //             $disabled = '';
    //         } else {
    //             $disabled = 'disabled';
    //         }

    //         $form_date = '';
    //         if(!empty($form_info->date)){
    //             $form_date = date('d-m-Y',strtotime($form_info->date));
    //         }
    //         //echo $form_date; die;
    //         $static_fields    = '<div class="col-md-12 col-sm-12 col-xs-12">
    //                                 <h3 class="m-t-0 m-b-20 clr-blue fnt-20 dynamic_form_h3">Details </h3>
    //                             </div>
    //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
    //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
    //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Staff Created: </label>
    //                                     <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
    //                                         <div class="input-group popovr">
    //                                             <input type="text" class="form-control trans" placeholder="" name="" value="'.$form_info->name .' ('.date('d-m-Y h:i a', strtotime($form_info->created_at)).')" '.$disabled.' readonly/>
    //                                         </div>
    //                                     </div>
    //                                 </div>
    //                             </div>
    //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
    //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
    //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Title: </label>
    //                                     <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
    //                                         <div class="input-group popovr">
    //                                             <input type="text" class="form-control trans static_title" placeholder="" name="title" value="'.$form_info->title .'" '.$disabled.' />
    //                                         </div>
    //                                     </div>
    //                                 </div>
    //                             </div>

    //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
    //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
    //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Date: </label>
    //                                     <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
    //                                       <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date="" class="input-group date dpYears">
    //                                         <input name="date" size="16" readonly="" class="form-control trans" type="text" value="'.$form_date.'" '.$disabled.' >
    //                                         <span class="input-group-btn add-on">
    //                                           <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
    //                                         </span>
    //                                       </div>
    //                                     </div>
    //                                 </div>
    //                             </div>

    //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
    //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
    //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Time: </label>
    //                                     <div class="col-md-10 col-sm-12 col-xs-12 r-p-0">
    //                                         <div class="input-group popovr">
    //                                             <input type="text" class="form-control trans static_title" placeholder="" name="time" value="'.$form_info->time .'" '.$disabled.' />
    //                                         </div>
    //                                     </div>
    //                                 </div>
    //                             </div>

    //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
    //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
    //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Detail: </label>
    //                                     <div class="col-md-10 col-sm-12 col-xs-12 r-p-0">
    //                                         <div class="input-group popovr">
    //                                             <textarea class="form-control trans" placeholder="" name="details" '.$disabled.' >'.$form_info->details .'</textarea>
    //                                         </div>
    //                                     </div>
    //                                 </div>
    //                             </div>';
    //         $formdata = '';
    //         $formdata .= $static_fields;
    //         $total_fields = 0;
    //         //echo '<pre>'; print_r($static_fields); die;

    //         $inp_col = 10;

    //         foreach($form_builder->pattern as $key => $value){

    //             $field_label = $value->label;
    //             $column_name = $value->column_name;
    //             $field_type  = $value->column_type;  

    //             $match_found = 0;
    //             foreach($form_values as $key => $value2){

    //                 $form_column     = $key; 
    //                 $form_column_val = $value2;

    //                 if($column_name == $form_column){

    //                     $field_value = $form_column_val;  
    //                     $total_fields++;
    //                     $match_found = 1;

    //                     if($field_type == 'Textbox'){
    //                         $formdata .= '
    //                                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
    //                                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
    //                                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
    //                                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
    //                                                 <div class="input-group popovr">
    //                                                     <input name="formdata['.$column_name.']" value="'.$field_value.'" type="text" class="form-control trans" '.$disabled.' />
    //                                                 </div>
    //                                             </div>
    //                                         </div>
    //                                     </div>';
    //                     //p-l-30
    //                     } else if($field_type == 'Selectbox'){
    //                         $options = '';
    //                         $option_name = '';
    //                         $option_value = '';
    //                         $opt = '';
    //                         $j = 0;
    //                         ///alert(option_count);
    //                         //for($i=1; $i <= $option_count; $i++){
    //                         foreach($value->select_options as $select_option){
    //                             //echo '<pre>'; print_r($select_option);
    //                             $select_option = (array)$select_option;
    //                             //print_r($se['option_name']); die;

    //                             $option_name    = $select_option['option_name'];
    //                             $option_value   = $select_option['option_value'];

    //                             $selected = '';
    //                             if($form_column_val == $option_value){
    //                                 $selected = 'selected';
    //                             } 

    //                             //if( ($option_name !== '') && ($option_value !== '') ) {            
    //                                 $options .= '<option value="'.$option_value.'" '.$selected.'>'.$option_name.'</option>';

    //                                 $opt .= '<input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_name]" value="'.$option_name.'"> <input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_value]" value="'.$option_value.'">';
    //                                 $j++;
    //                             //}
    //                         }

    //                         $formdata .= '
    //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
    //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
    //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
    //                                     <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-10 ">
    //                                         <div class="select-style">
    //                                             <select name="formdata['.$column_name.']" '.$disabled.' >
    //                                                 '.$options.'
    //                                             </select>
    //                                         </div>
    //                                     </div>
    //                                 </div>
    //                             </div>'; 

    //                     } else if($field_type == 'Textarea'){
    //                         $formdata .='
    //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
    //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
    //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
    //                                     <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
    //                                         <div class="input-group popovr">
    //                                             <textarea name="formdata['.$column_name.']" class="form-control trans" '.$disabled.' >'.$field_value.'</textarea>
    //                                         </div>
    //                                     </div>
    //                                 </div>
    //                             </div>';

    //                     } else if($field_type == 'Date'){
    //                         $formdata .='
    //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
    //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
    //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
    //                                     <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
    //                                         <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date dpYears">
    //                                             <input name="formdata['.$column_name.']" type="text" value="'.$field_value.'" size="16" class="form-control trans" readonly="">
    //                                             <span class="input-group-btn add-on">
    //                                                 <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
    //                                             </span>
    //                                         </div>
    //                                     </div>
    //                                 </div>
    //                             </div>';

    //                     } else{
    //                         $formdata .='';
    //                     }



    //                 }

    //             }

    //             if($match_found == 0){

    //                     $field_value = '';  
    //                     $total_fields++;

    //                     if($field_type == 'Textbox'){
    //                         $formdata .= '
    //                                     <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
    //                                         <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
    //                                             <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
    //                                             <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
    //                                                 <div class="input-group popovr">
    //                                                     <input name="formdata['.$column_name.']" value="'.$field_value.'" type="text" class="form-control trans" '.$disabled.' />
    //                                                 </div>
    //                                             </div>
    //                                         </div>
    //                                     </div>';

    //                     } else if($field_type == 'Selectbox'){
    //                         $options = '';
    //                         $option_name = '';
    //                         $option_value = '';
    //                         $opt = '';
    //                         $j = 0;
    //                         ///alert(option_count);
    //                         //for($i=1; $i <= $option_count; $i++){
    //                         foreach($value->select_options as $select_option){
    //                             //echo '<pre>'; print_r($select_option);
    //                             $select_option = (array)$select_option;
    //                             //print_r($se['option_name']); die;

    //                             $option_name    = $select_option['option_name'];
    //                             $option_value   = $select_option['option_value'];

    //                             $selected = '';
    //                             /*if($form_column_val == $option_value){
    //                                 $selected = 'selected';
    //                             }*/ 
    //                             $selected = '';

    //                             //if( ($option_name !== '') && ($option_value !== '') ) {            
    //                                 $options .= '<option value="'.$option_value.'" '.$selected.'>'.$option_name.'</option>';

    //                                 $opt .= '<input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_name]" value="'.$option_name.'"> <input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_value]" value="'.$option_value.'">';
    //                                 $j++;
    //                             //}
    //                         }

    //                         $formdata .= '
    //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
    //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
    //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
    //                                     <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-10 ">
    //                                         <div class="select-style">
    //                                             <select name="formdata['.$column_name.']" '.$disabled.' >
    //                                                 '.$options.'
    //                                             </select>
    //                                         </div>
    //                                     </div>
    //                                 </div>
    //                             </div>'; 

    //                     } else if($field_type == 'Textarea'){
    //                         $formdata .='
    //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
    //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
    //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
    //                                     <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
    //                                         <div class="input-group popovr">
    //                                             <textarea name="formdata['.$column_name.']" class="form-control trans" '.$disabled.' >'.$field_value.'</textarea>
    //                                         </div>
    //                                     </div>
    //                                 </div>
    //                             </div>';

    //                     } else if($field_type == 'Date'){
    //                         $formdata .='
    //                             <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
    //                                 <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
    //                                     <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
    //                                     <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 ">
    //                                         <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date dpYears">
    //                                             <input name="formdata['.$column_name.']" type="text" value="'.$field_value.'" size="16" class="form-control trans" readonly="">
    //                                             <span class="input-group-btn add-on">
    //                                                 <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
    //                                             </span>
    //                                         </div>
    //                                     </div>
    //                                 </div>
    //                             </div>';

    //                     } else{
    //                         $formdata .='';
    //                     }

    //             }

    //         }

    //         $selected = '';
    //         if($form_info->alert_status == '1') {
    //             $selected = 'selected'; 
    //         } 

    //         $alert_date = '';
    //         if(!empty($form_info->alert_date)) {
    //             $alert_date = date('d-m-Y',strtotime($form_info->alert_date));
    //         } 
    //         if($form_builder->alert_field == '1') {
    //             $static_field = '<div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
    //                             <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
    //                                 <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Alert: </label>
    //                                 <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
    //                                     <div class="select-style" style="pointer-events:none">
    //                                         <select name="alert_status" '.$disabled.'>
    //                                             <option value="0">No</option>
    //                                             <option value="1" '.$selected.'>Yes</option>
    //                                         </select>
    //                                     </div>
    //                                 </div>
    //                             </div>
    //                         </div>
    //                         <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
    //                             <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0">
    //                                 <label class="col-md-2 col-sm-2 col-xs-12 p-t-7"> Alert Date: </label>
    //                                 <div class="col-md-10 col-sm-10 col-xs-12 r-p-0">
    //                                   <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date="" class="input-group date custom-input-class">
    //                                     <input name="alert_date" value="'.$alert_date.'" size="16" readonly="" class="form-control" type="text">
    //                                     <span class="input-group-btn add-on">
    //                                       <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
    //                                     </span>
    //                                   </div>
    //                                 </div>
    //                             </div>
    //                         </div>
    //                         ';

    //             $formdata .= $static_field;
    //         }


    //         $result['response']         = true;

    //         $result['form_builder_id']  = $form_info->form_builder_id;
    //         //$result['title']            = $form_builder->title;
    //         $result['service_user_id']  = $form_info->service_user_id;
    //         //$result['detail']     = `->detail;
    //         $result['form_data']          = $formdata;

    //     } else{
    //         $result['response']     = false;
    //     }
    //     return $result;     
    // }

    //For Admin
    public static function showFormAdmin($tag = null)
    {
        //$form = FormBuilder::where('tag',$tag)->first();
        //first get the form default id from tag

        $form_default  = FormDefault::where('tag', $tag)->first();

        if (!empty($form_default)) {

            //search for the customized user dynamic form, if present

            // HomeID in case of backEnd 
            $home_id = Session::get('scitsAdminSession')->home_id;

            $form    = FormBuilder::where('form_default_id', $form_default->id)
                ->where('home_id', $home_id)
                ->first();

            //if customized form is not present then show the by default form
            if (empty($form)) {
                $form = $form_default;
            }
        }

        if (!empty($form)) {

            $form->pattern = json_decode($form->pattern);

            $formdata = '';

            $total_fields = 0;
            foreach ($form->pattern as $key => $value) {
                $total_fields++;
                $field_label = $value->label;
                $column_name = $value->column_name;
                $field_type  = $value->column_type;
                $field_value = '';

                $label_col_val = '1';
                $inp_col = 10;

                if ($field_type == 'Textbox') {
                    $formdata .= '<div class="form-group">
                                    <label class="col-lg-2 col-md-2 col-sm-2 col-xs-12 control-label"> ' . $field_label . ': </label>
                                    <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-12 r-p-0">
                                        <input name="formdata[' . $column_name . ']" value="' . $field_value . '" type="text" class="form-control"  />
                                    </div>
                                </div>';
                } else if ($field_type == 'Selectbox') {
                    $options = '';
                    $option_name = '';
                    $option_value = '';
                    $opt = '';
                    $j = 0;
                    ///alert(option_count);
                    //for($i=1; $i <= $option_count; $i++){
                    foreach ($value->select_options as $select_option) {
                        //echo '<pre>'; print_r($select_option);
                        $select_option = (array)$select_option;
                        //print_r($se['option_name']); die;

                        $option_name    = $select_option['option_name'];
                        $option_value   = $select_option['option_value'];

                        //if( ($option_name !== '') && ($option_value !== '') ) {            
                        $options .= '<option value="' . $option_value . '">' . $option_name . '</option>';

                        $opt .= '<input type="hidden" name="formdata[' . $key . '][select_options][' . $j . '][option_name]" value="' . $option_name . '" class="form-control" > <input type="hidden" name="formdata[' . $key . '][select_options][' . $j . '][option_value]" value="' . $option_value . '">';
                        $j++;
                        //}
                    }

                    $formdata .= '
                            <div class="form-group">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 control-label"> ' . $field_label . ': </label>
                                <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-12">
                                    <div class="select-style">
                                        <select name="formdata[' . $column_name . ']"  class="form-control" >
                                            ' . $options . '
                                        </select>
                                    </div>
                                </div>
                            </div>';
                } else if ($field_type == 'Textarea') {
                    $formdata .= '
                            <div class="form-group">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 control-label"> ' . $field_label . ': </label>
                                <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-12 r-p-0">
                                    <textarea name="formdata[' . $column_name . ']" class="form-control"></textarea>
                                </div>
                            </div>';
                } else if ($field_type == 'Date') {
                    $formdata .= '
                            <div class="form-group">
                                <label class="col-md-2 col-sm-2 col-xs-12 p-t-7 control-label"> ' . $field_label . ': </label>
                                <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-12 r-p-0">
                                    <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date dpYears">
                                        <input name="formdata[' . $column_name . ']" type="text" value="" size="16" readonly class="form-control">
                                        <span class="input-group-btn ">
                                            <button class="btn btn-primary add-on" type="button"><i class="fa fa-calendar"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>';
                } else {
                    $formdata .= '';
                }
            }

            $result['response']     = true;

            $result['form_id']      = $form->id;
            //$result['title']      = $form->title;
            //$result['detail']     = $form->detail;
            $result['pattern']      = $formdata;
        } else {
            $result['response']     = false;
        }
        return $result;
    }

    public static function showFormWithValueAdmin($tag = null, $form_values = null, $enable = false)
    {

        // echo $tag; echo "<br><br>"; 

        $form_values = trim($form_values);

        if (!empty($form_values)) {
            $form_values = json_decode($form_values);
        } else {
            $form_values  = array();
        }

        //first get the form default id from tag
        $form_default  = FormDefault::where('tag', $tag)->first();

        if (!empty($form_default)) {

            //search for the customized user dynamic form, if present
            $home_id    =   Session::get('scitsAdminSession')->home_id;
            $form       =   FormBuilder::where('form_default_id', $form_default->id)
                ->where('home_id', $home_id)
                ->first();

            //if customized form is not present then show the by default form
            if (empty($form)) {
                $form = $form_default;
            }
        }

        if (!empty($form)) {

            $form->pattern = json_decode($form->pattern);

            $formdata = '';
            $total_fields = 0;

            if ($enable == true) {
                $disabled = '';
            } else {
                $disabled = 'disabled';
            }
            $inp_col = 10;

            foreach ($form->pattern as $key => $value) {

                $field_label = $value->label;
                $column_name = $value->column_name;
                $field_type  = $value->column_type;

                $match_found = 0;
                foreach ($form_values as $key => $value2) {

                    $form_column     = $key;
                    $form_column_val = $value2;

                    if ($column_name == $form_column) {

                        $field_value = $form_column_val;
                        $total_fields++;
                        $match_found = 1;

                        if ($field_type == 'Textbox') {
                            $formdata .= '
                                        <div class="form-group">
                                            <label class="col-md-2 col-sm-2 col-xs-12 control-label"> ' . $field_label . ': </label>
                                            <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-12 ">
                                                <input name="formdata[' . $column_name . ']" value="' . $field_value . '" type="text" class="form-control trans" ' . $disabled . ' />
                                            </div>
                                        </div>';
                        } else if ($field_type == 'Selectbox') {
                            $options = '';
                            $option_name = '';
                            $option_value = '';
                            $opt = '';
                            $j = 0;
                            ///alert(option_count);
                            //for($i=1; $i <= $option_count; $i++){
                            foreach ($value->select_options as $select_option) {
                                //echo '<pre>'; print_r($select_option);
                                $select_option = (array)$select_option;
                                //print_r($se['option_name']); die;

                                $option_name    = $select_option['option_name'];
                                $option_value   = $select_option['option_value'];

                                $selected = '';
                                if ($form_column_val == $option_value) {
                                    $selected = 'selected';
                                }

                                //if( ($option_name !== '') && ($option_value !== '') ) {            
                                $options .= '<option value="' . $option_value . '" ' . $selected . '>' . $option_name . '</option>';

                                $opt .= '<input type="hidden" name="formdata[' . $key . '][select_options][' . $j . '][option_name]" value="' . $option_name . '"> <input type="hidden" name="formdata[' . $key . '][select_options][' . $j . '][option_value]" value="' . $option_value . '">';
                                $j++;
                                //}
                            }

                            $formdata .= '
                                    <div class="form-group">
                                        <label class="col-md-2 col-sm-2 col-xs-12 control-label"> ' . $field_label . ': </label>
                                        <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-10">
                                            <div class="select-style">
                                                <select name="formdata[' . $column_name . ']" ' . $disabled . ' class="form-control">
                                                    ' . $options . '
                                                </select>
                                            </div>
                                        </div>
                                    </div>';
                        } else if ($field_type == 'Textarea') {
                            $formdata .= '
                                    <div class="form-group">
                                        <label class="col-md-2 col-sm-2 col-xs-12 control-label"> ' . $field_label . ': </label>
                                        <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-12 ">
                                            <textarea name="formdata[' . $column_name . ']" class="form-control trans" ' . $disabled . ' >' . $field_value . '2</textarea>
                                        </div>
                                    </div>';
                        } else if ($field_type == 'Date') {
                            $formdata .= '
                                    <div class="form-group">
                                        <label class="col-md-2 col-sm-2 col-xs-12 control-label"> ' . $field_label . ': </label>
                                        <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-12 ">
                                            <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date dpYears">
                                                <input name="formdata[' . $column_name . ']" type="text" value="' . $field_value . '" size="16" class="form-control trans" readonly="">
                                                <span class="input-group-btn">
                                                    <button class="btn btn-primary add-on" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>';
                        } else {
                            $formdata .= '';
                        }
                    }
                }

                // the fields which were new
                // and were not present at the time of saving dynamic form data 
                if ($match_found == 0) {

                    $field_value = '';
                    $total_fields++;

                    if ($field_type == 'Textbox') {
                        $formdata .= '
                                        <div class="form-group">
                                            <label class="col-md-2 col-sm-2 col-xs-12 control-label"> ' . $field_label . ': </label>
                                            <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-12">
                                                <input name="formdata[' . $column_name . ']" value="' . $field_value . '" type="text" class="form-control trans" ' . $disabled . ' />
                                            </div>
                                        </div>';
                    } else if ($field_type == 'Selectbox') {
                        $options = '';
                        $option_name = '';
                        $option_value = '';
                        $opt = '';
                        $j = 0;
                        ///alert(option_count);
                        //for($i=1; $i <= $option_count; $i++){
                        foreach ($value->select_options as $select_option) {
                            //echo '<pre>'; print_r($select_option);
                            $select_option = (array)$select_option;
                            //print_r($se['option_name']); die;

                            $option_name    = $select_option['option_name'];
                            $option_value   = $select_option['option_value'];

                            $selected = '';
                            if ($form_column_val == $option_value) {
                                $selected = 'selected';
                            }

                            //if( ($option_name !== '') && ($option_value !== '') ) {            
                            $options .= '<option value="' . $option_value . '" ' . $selected . '>' . $option_name . '</option>';

                            $opt .= '<input type="hidden" name="formdata[' . $key . '][select_options][' . $j . '][option_name]" value="' . $option_name . '"> <input type="hidden" name="formdata[' . $key . '][select_options][' . $j . '][option_value]" value="' . $option_value . '">';
                            $j++;
                            //}
                        }

                        $formdata .= '
                                    <div class="form-group">
                                        <label class="col-md-2 col-sm-2 col-xs-12 control-label"> ' . $field_label . ': </label>
                                        <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-12">
                                            <div class="select-style">
                                                <select name="formdata[' . $column_name . ']" ' . $disabled . ' >
                                                    ' . $options . '
                                                </select>
                                            </div>
                                        </div>
                                    </div>';
                    } else if ($field_type == 'Textarea') {
                        $formdata .= '
                                    <div class="form-group">
                                        <label class="col-md-2 col-sm-2 col-xs-12 control-label"> ' . $field_label . ': </label>
                                        <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-12">
                                            <textarea name="formdata[' . $column_name . ']" class="form-control trans" ' . $disabled . ' >' . $field_value . '</textarea>
                                        </div>
                                    </div>';
                    } else if ($field_type == 'Date') {
                        $formdata .= '
                                    <div class="form-group">
                                        <label class="col-md-2 col-sm-2 col-xs-12 control-label"> ' . $field_label . ': </label>
                                        <div class="col-md-' . $inp_col . ' col-sm-' . $inp_col . ' col-xs-12 ">
                                            <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date dpYears">
                                                <input name="formdata[' . $column_name . ']" type="text" value="' . $field_value . '" size="16" class="form-control trans" readonly="">
                                                <span class="input-group-btn ">
                                                    <button class="btn btn-primary add-on" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>';
                    } else {
                        $formdata .= '';
                    }
                }
            }

            $result['response']     = true;

            //$result['form_id']    = $form->id;
            //$result['title']      = $form->title;
            //$result['detail']     = $form->detail;
            $result['pattern']      = $formdata;
        } else {
            $result['response']     = false;
        }
        return $result;
    }

    public static function saveForm($data)
    {
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];

        if (isset($data['data'])) {
            $formdata = json_encode($data['data']);
        } else {
            $formdata = '';
        }
        // return $data['title'];
        // return $formdata; die;
        //  print_r($formdata); die();
        /*----- June 07,2018 (Akhil) ---*/
        if ($data['service_user_id'] != '') {
            $service_user_id = $data['service_user_id'];
        }
        // return $data['title'];
        // return $formdata; die;
        if (isset($data['formImage'])) {
            $formImage = $data['formImage'];
        } else {
            $formImage = null;
        }
        /*----- June 07,2018 End ---*/
        
        $form                   = new DynamicForm;
        $form->home_id          = $home_id;
        $form->user_id          = Auth::user()->id;
        $form->form_builder_id  = $data['dynamic_form_builder_id'];
        $form->image_path       = $formImage;
        // $form->user_id          = $data['user_id']; 
        /*----- June 07,2018 (Akhil) -----*/
        if ($data['service_user_id'] != '') {
            $form->service_user_id = $data['service_user_id'];
        }

        /*----- June 07,2018 End ---*/
        // $form->service_user_id  = $service_user_id; 
        $form->location_id      = $data['location_id'];
        $form->title            = $data['title'];
        $form->time             = $data['time']; 
        $form->details          = $data['details']; 
        $form->pattern_data     = $formdata;

        if (isset($data['alert_status'])) {
            $form->alert_status     = $data['alert_status'];

            if ($data['alert_status'] == '1') {
                if (!empty($data['alert_date'])) {
                    $form->alert_date   = date('Y-m-d', strtotime($data['alert_date']));
                } else {
                    $form->alert_date   = date('Y-m-d');
                }
            }
        }


        if (!empty($data['date'])) {
            $form->date = date('Y-m-d', strtotime($data['date']));
        }

        if ($form->save()) {

            $location_id = $data['location_id'];
            $location_tag = DynamicFormLocation::where('id', $location_id)->value('tag');
            //echo "<pre>"; print_r($form_location); die;
            $notification_event_type_id = DB::table('notification_event_type')->where('table_linked', 'LIKE', 'su_' . $location_tag)->value('id');
            if (!empty($notification_event_type_id)) {
                //echo $notification_event_type_id; die;

                //saving notification start
                $notification                             = new Notification;
                $notification->service_user_id            = $data['service_user_id'];
                $notification->event_id                   = $form->id;
                $notification->notification_event_type_id = $notification_event_type_id;
                $notification->event_action               = 'ADD';
                $notification->home_id                    = $home_id;
                $notification->user_id                    = Auth::user()->id;
                $notification->save();
                //saving notification end

            }
            //return $data['send_to'];
            //return $formdata;
            // die;

            if (!empty($data['send_to'])) {
                $senders = $data['send_to'];
                foreach ($senders as $key => $sender) {
                    $s_type = explode('-', $sender);

                    if ($s_type[0] == 'ct') {
                        // return $s_type;
                        //die;
                        // echo "ct_yes";  
                        $type = 'ct';
                        $care_team_id = $s_type[1];
                        Parent::sendEmailNotificationDynamicForm($care_team_id, $type, $data['service_user_id'], $data['dynamic_form_builder_id']);
                    } else if ($s_type[0] ==  'sc') {
                        // echo "sc_yes";
                        $type = 'sc';
                        $su_contact_id = $s_type[1];
                        Parent::sendEmailNotificationDynamicForm($su_contact_id, $type, $data['service_user_id'], $data['dynamic_form_builder_id']);
                    }
                }
            }
            //return $data['title'];
            //return $formdata;
            //die;

            return $form->id;
        } else {
            return '0';
        }
    }

    //show form according to filled values
    /*    public static function showFormWithValue($tag = null,$form_values = null,$enable=false){

        // echo $tag; echo "<br><br>"; 
        // echo "<pre>"; print_r($form_values); 

        $form_values = trim($form_values);

        if(!empty($form_values)){ 
            $form_values = json_decode($form_values);
        } else{
           $form_values  = array();
        }

        //first get the form default id from tag
        $form_default  = FormDefault::where('tag',$tag)->first();

        if(!empty($form_default)){            
            
            //search for the customized user dynamic form, if present
            $home_id = Auth::user()->home_id;
            $form = FormBuilder::where('form_default_id',$form_default->id)
                                    ->where('home_id',$home_id)
                                    ->first();

            //if customized form is not present then show the by default form
            if(empty($form)){
                $form = $form_default;
            }
        }
        
        //echo '<pre>'; print_r($form); die;  
        if(!empty($form)){

            $form->pattern = json_decode($form->pattern);
            // echo "<pre>";
            // print_r($form->pattern);
            // die;
            $formdata = '';
            $total_fields = 0;
            
            if($enable == true){
                $disabled = '';

            } else{
                $disabled = 'disabled';
            }
            $inp_col = 11;
            
            foreach($form_values as $key => $value){
                $form_column     = $key; 
                $form_column_val = $value;

                foreach($form->pattern as $key => $value){
                    
                    $field_label = $value->label;
                    $column_name = $value->column_name;
                    $field_type  = $value->column_type;  
                    
                    if($column_name == $form_column){

                        $field_value = $form_column_val;  
                        $total_fields++;

                        if($field_type == 'Textbox'){
                            $formdata .= '
                                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                            <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                                <label class="col-md-1 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
                                                <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 p-l-30">
                                                    <div class="input-group popovr">
                                                        <input name="formdata['.$column_name.']" value="'.$field_value.'" type="text" class="form-control trans" '.$disabled.' />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>';
                        
                        } else if($field_type == 'Selectbox'){
                            $options = '';
                            $option_name = '';
                            $option_value = '';
                            $opt = '';
                            $j = 0;
                            ///alert(option_count);
                            //for($i=1; $i <= $option_count; $i++){
                            foreach($value->select_options as $select_option){
                                //echo '<pre>'; print_r($select_option);
                                $select_option = (array)$select_option;
                                //print_r($se['option_name']); die;

                                $option_name    = $select_option['option_name'];
                                $option_value   = $select_option['option_value'];
                                
                                $selected = '';
                                if($form_column_val == $option_value){
                                    $selected = 'selected';
                                } 

                                //if( ($option_name !== '') && ($option_value !== '') ) {            
                                    $options .= '<option value="'.$option_value.'" '.$selected.'>'.$option_name.'</option>';

                                    $opt .= '<input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_name]" value="'.$option_name.'"> <input type="hidden" name="formdata['.$key.'][select_options]['.$j.'][option_value]" value="'.$option_value.'">';
                                    $j++;
                                //}
                            }

                            $formdata .= '
                                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                        <label class="col-md-1 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
                                        <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-10 p-l-30">
                                            <div class="select-style">
                                                <select name="formdata['.$column_name.']" '.$disabled.' >
                                                    '.$options.'
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>'; 

                        } else if($field_type == 'Textarea'){
                            $formdata .='
                                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel">
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 ">
                                        <label class="col-md-1 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
                                        <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 p-l-30">
                                            <div class="input-group popovr">
                                                <textarea name="formdata['.$column_name.']" class="form-control trans" '.$disabled.' >'.$field_value.'</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>';

                        } else if($field_type == 'Date'){
                            $formdata .='
                                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel datepicker-sttng">      
                                    <div class="form-group col-md-12 col-sm-12 col-xs-12 p-0 add-rcrd">
                                        <label class="col-md-1 col-sm-2 col-xs-12 p-t-7"> '.$field_label.': </label>
                                        <div class="col-md-'.$inp_col.' col-sm-'.$inp_col.' col-xs-12 r-p-0 p-l-30">
                                            <div data-date-viewmode="" data-date-format="dd-mm-yyyy" data-date=""  class="input-group date dpYears">
                                                <input name="formdata['.$column_name.']" type="text" value="'.$field_value.'" size="16" class="form-control trans" readonly="">
                                                <span class="input-group-btn add-on">
                                                    <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>';

                        } else{
                            $formdata .='';
                        }
                    }
                }
            }

            $result['response']     = true;
            
            //$result['form_id']    = $form->id;
            //$result['title']      = $form->title;
            //$result['detail']     = $form->detail;
            $result['pattern']      = $formdata;

        } else{
            $result['response']     = false;
        }
        return $result;     
    }*/

    public static function countIncidentReport($service_user_id = null, $start_date = null, $end_date = null)
    {

        $this_location_id = DynamicFormLocation::getLocationIdByTag('incident_report');

        $dynamic_query = DynamicForm::where('location_id', $this_location_id)
            ->where('service_user_id', $service_user_id)
            ->where('is_deleted', '0')
            ->where('date', '!=', '');

        if ((!empty($start_date)) && (!empty($end_date))) {

            $start_date = date('y-m-d', strtotime($start_date));
            $end_date   = date('y-m-d', strtotime('+1 day', strtotime($end_date)));
            $dynamic_query = $dynamic_query->where('date', '>=', $start_date)
                ->where('date', '<', $end_date);
        }

        $incidents_count = $dynamic_query->count();
        return $incidents_count;
    }


    public static function alertDynamicForm()
    {

        $form_alert = DynamicForm::select('dynamic_form.id', 'dynamic_form.alert_status', 'dynamic_form.title', 'dynamic_form.alert_date', 'su.name', 'dynamic_form.service_user_id')
            ->join('service_user as su', 'su.id', 'dynamic_form.service_user_id')
            ->where('dynamic_form.alert_status', 1)
            ->where('dynamic_form.alert_date', date('Y-m-d'))
            ->get()
            ->toArray();
        // echo "<pre>"; print_r($form_alert); die;
        return $form_alert;
    }


    public static function sendEmailNotificationDynamicForm($member_id = null, $type = null, $service_user_id = null, $dynamic_form_builder_id = null)
    {

        $su_name     = DB::table('service_user')->where('id', $service_user_id)->value('name');

        $d_form_name = DB::table('dynamic_form_builder')->where('id', $dynamic_form_builder_id)->value('title');



        if ($type == 'ct') {
            $care_team = DB::table('su_care_team')
                ->select('id', 'name', 'email')
                ->where('is_deleted', '0')
                ->where('id', $member_id)
                ->first();

            if (!empty($care_team)) {
                $name  = $care_team->name;
                $email = $care_team->email;
            }
        } else if ($type == 'sc') {
            $su_contact = DB::table('su_contacts')
                ->select('id', 'name', 'email')
                ->where('is_deleted', '0')
                ->where('id', $member_id)
                ->first();

            if (!empty($su_contact)) {
                $name  = $su_contact->name;
                $email = $su_contact->email;
            }
        } else {
            $name  = '';
            $email = '';
        }

        // $company_name = PROJECT_NAME;       
        // if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        //     Mail::send('emails.dynamic_form_send_mail',['name'=>$name,'su_name'=> $su_name, 'd_form_name' => $d_form_name], function($message) use ($email,$company_name)
        //     {
        //         $message->to($email,$company_name)->subject('Child Mail');
        //     });
        // }


    }
}
