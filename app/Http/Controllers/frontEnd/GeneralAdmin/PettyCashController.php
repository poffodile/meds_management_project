<?php

namespace App\Http\Controllers\frontEnd\GeneralAdmin;

use App\Http\Controllers\frontEnd\GeneralAdminController;
use Illuminate\Http\Request;
use App\PettyCash, App\AccessRight, App\User;
use DB, Auth;
use Illuminate\Support\Facades\Mail;

class PettyCashController extends GeneralAdminController
{
    public function index()
    {
        //in search 
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        $expense_reports = PettyCash::where('home_id', $home_id)->where('txn_type', 'W');

        $today = date('Y-m-d 00:0:00');

        $pagination  = '';
        if (isset($_GET['search'])) {

            $expnse_rep_srch_type = $_GET['expnse_rep_srch_type'];

            if ($expnse_rep_srch_type == 'expnse_title') {

                $expense_reports = $expense_reports->where('petty_cash.title', 'like', '%' . $_GET['expnse_rep_title_srch'] . '%');
            } else if ($expnse_rep_srch_type == 'expnse_date') {
                $search_date = date('Y-m-d', strtotime($_GET['expnse_rep_date_srch'])) . ' 00:00:00';
                $search_date_next = date('Y-m-d', strtotime('+1 day', strtotime($_GET['expnse_rep_date_srch']))) . ' 00:00:00';

                $expense_reports = $expense_reports->where('petty_cash.created_at', '>', $search_date)
                    ->where('petty_cash.created_at', '<', $search_date_next);
            } else {
                $expense_reports = $expense_reports->whereBetween('petty_cash.balance', [$_GET['expnse_rep_amount1_srch'], $_GET['expnse_rep_amount2_srch']]);
            }
        }

        $expense_reports  = $expense_reports->orderBy('petty_cash.id', 'desc')
            ->orderBy('petty_cash.created_at', 'desc');
        //->paginate(50);

        if (isset($_GET['search'])) {

            $expense_reports = $expense_reports->get();
        } else {
            $expense_reports = $expense_reports->paginate(5);

            if ($expense_reports->links() != '') {
                $pagination .= '</div><div class="expense_reports_paginate m-l-15 position-botm">';
                $pagination .= $expense_reports->links();
                $pagination .= '</div>';
            }
        }

        if (!$expense_reports->isEmpty()) {
            $pre_date = date('y-m-d', strtotime($expense_reports['0']->created_at));
        }

        foreach ($expense_reports as $key => $value) {
            // echo "<pre>"; print_r($expense_reports); die;
            $check = (!empty($value->details)) ? '<i class="fa fa-check"></i>' : '';

            $first = 0;

            if (isset($_GET['logged']) ||  isset($_GET['expnse_rep_title_srch'])) {
                $record_date = date('Y-m-d', strtotime($value->created_at));

                if ($record_date != $pre_date) {
                    $pre_date = $record_date;

                    echo '</div>
                    <div class="daily-rcd-head">
                        <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 record_row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <a  class="date-tab">
                                    <span class="pull-left">
                                        ' . date('d F Y', strtotime($record_date)) . '
                                    </span>
                                    <i class="fa fa-angle-right pull-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="daily-rcd-content">';
                } else {
                }
            }

            echo '
                <div class="col-md-12 col-sm-12 col-xs-12 cog-panel p-0 r-p-15 record_row">
                    <div class="form-group col-md-11 col-sm-11 col-xs-12 r-p-0 pull-center">
                        <div class="input-group popovr">
                            <input type="text" name="edit_su_record_desc[]"  class="form-control cus-control edit_record_desc_' . $value->id . ' edit_rcrd"  disabled  value="' . $value->title . '" />';

            if (!empty($value->details)) {
                echo '<div class="input-plus color-green"> <i class="fa fa-plus"></i> </div>';
            }
            echo '<input type="hidden" name="edit_su_record_id[]" value="' . $value->id . '" disabled="disabled" class="edit_record_id_' . $value->id . '" />
                                <span class="input-group-addon cus-inpt-grp-addon clr-blue settings">
                                <i class="fa fa-cog"></i>
                                <div class="pop-notifbox">
                                    <ul class="pop-notification" type="none"> 
                                        <li> <a href="#" expnse_rep_id="' . $value->id . '" class="view-expnse-rep"> <span> <i class="fa fa-eye"></i> </span> View </a> </li>
                                       
                                        <li> <a href="#" expnse_rep_id="' . $value->id . '" class="edit-expnse-rep""> <span class="color-red"> <i class="fa fa-pencil color-blue"></i> </span> Edit </a> </li>
                                        <li> <a href="#" expnse_rep_id="' . $value->id . '" class="expense-detail"> <span class="color-red"> <i class="fa fa-plus color-green"></i> </span> Details </a> </li>
                                    </ul>
                                </div>
                            </span>
                        </div>
                    </div>

                    <div class="input-plusbox form-group col-xs-11 p-0 detail">
                        <label class="cus-label color-themecolor"> Details: </label>
                        <div class="cus-input p-r-10">
                            <div class="input-group">
                                <textarea rows="5" name="edit_su_record_detail[]" disabled class="form-control tick_text txtarea edit_detail_' . $value->id . ' edit_rcrd " value="">' . $value->details . '</textarea>
                                <span class="input-group-addon cus-inpt-grp-addon color-grey settings tick_show">' . $check . '</span>
                            </div>
                        </div>
                    </div>
                </div>
                ';
        }

        echo $pagination;
    }

    public function add(Request $request)
    {

        // echo "<pre>"; print_r($request->input()); die;
        if ($request->isMethod('post')) {
            $data = $request->all();
            $home_ids = Auth::user()->home_id;
            $ex_home_ids = explode(',', $home_ids);
            $home_id=$ex_home_ids[0];
            $balance = (float)PettyCash::where('home_id', $home_id)->orderBy('id', 'desc')->value('balance');
            $expense_report     = new PettyCash;
            $transaction_amount = (float)$data['expense_amount'];
            //echo $remaining_balance; die;

            if ($transaction_amount > $balance) {
                return redirect()->back()->with('error', 'Not enough balance to carry out this process.');
            } else {
                $remaining_balance = $balance - $transaction_amount;
                //echo $remaining_balance; die;

                $expense_report->home_id    = $home_id;
                $expense_report->user_id    = Auth::user()->id;
                $expense_report->title      = $data['expense_title'];
                $expense_report->details    = $data['expense_detail'];
                $expense_report->txn_type   = 'W';
                $expense_report->txn_amount = $transaction_amount;
                $expense_report->balance    = $remaining_balance;

                if (!empty($_FILES['receipt_file']['name'])) {
                    $tmp_file   =   $_FILES['receipt_file']['tmp_name'];
                    $file_info  =   pathinfo($_FILES['receipt_file']['name']);
                    $ext        =   strtolower($file_info['extension']);
                    $random_no  =   rand('11111', '99999');
                    $new_name   =   time() . $random_no . '.' . $ext;

                    if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'doc' || $ext == 'docx' || $ext == 'pdf') {
                        $destination = base_path() . pettyCashFilesBasePath;

                        if (move_uploaded_file($tmp_file, $destination . '/' . $new_name)) {
                            $expense_report->receipt = $new_name;
                        }
                    }
                }
                if (!isset($expense_report->receipt)) {
                    $expense_report->receipt = '';
                }

                if ($expense_report->save()) {

                    if ($remaining_balance <= MIN_PETTY_CASH_BALANCE) {
                        //send email alert to staff members who has authority to add petty cash
                        $access_right_id      = AccessRight::where('tag', 'PROFILE_PETTY_CASH_ADD')->value('id');
                        $users                = User::select('id', 'name', 'email', 'user_name')
                            ->where('home_id', $home_id)
                            ->whereRaw('FIND_IN_SET(?,access_rights)', $access_right_id)
                            ->where('is_deleted', 0)
                            ->get();

                        foreach ($users as $user) {
                            $email               = $user->email;
                            $name                = $user->name;
                            $user_name           = $user->user_name;
                            $company_name        = PROJECT_NAME;

                            if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                                Mail::send('emails.petty_cash_low_alert', ['name' => $name, 'remaining_balance' => $remaining_balance], function ($message) use ($email, $company_name) {
                                    $message->to($email, $company_name)->subject('SCITS Low petty cash alert');
                                });
                            }
                        }
                    }
                    return redirect()->back()->with('success', 'Expenditure Report submitted successfully.');
                } else {
                    return redirect()->back()->with('error', COMMON_ERROR);
                }
            }
            die;
        } else {
            echo '0';
            die;
        }
    }

    public function get_petty_balance()
    {
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        // $home_id = Auth::user()->home_id;

        $balance = PettyCash::where('home_id', $home_id)->orderBy('id', 'desc')->value('balance');

        $balance = (float)$balance;

        echo $balance;
        die;
    }


    public function view($expnse_rep_id = null)
    {
        $user_id = Auth::user()->id;
        $home_ids = Auth::user()->home_id;
        $ex_home_ids = explode(',', $home_ids);
        $home_id=$ex_home_ids[0];
        // $home_id = Auth::user()->home_id;

        $expense_report = PettyCash::where('home_id', $home_id)->where('user_id', $user_id)->where('id', $expnse_rep_id)->first();

        if ($expense_report->home_id != $home_id) {

            $result['response'] = 'AUTH_ERR';
        }

        if ($expense_report->user_id != $user_id) {

            $result['response'] = 'AUTH_ERR';
        }

        echo '<div class="form-group col-md-12 col-sm-12 col-xs-12">
                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7 text-right"> Title: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                        <input type="text" class="form-control expnse-fields" placeholder="" name="expense_title" value="' . $expense_report->title . '" disabled/>
                    <p class="help-block"> Enter the Title of Expense made and add details below.</p>
                </div>
            </div>

            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7 text-right"> Details: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                        <textarea  class="form-control expnse-fields detail-info-txt" rows="3" name="expense_detail" disabled>' . $expense_report->details . '</textarea>
                </div>
            </div>

            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                <label class="col-md-2 col-sm-1 col-xs-12 p-t-7 text-right"> Expense: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                        <input name="expense_amount" type="text" disabled class="form-control expnse-fields" placeholder="" value="' . $expense_report->txn_amount . '"/>
                </div>
            </div>

            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                <label class="col-md-2 col-sm-1 col-xs-12 text-right"> Receipt: </label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                    <a href="' . pettyCashReceiptPath . '/' . $expense_report->receipt . '" target="_blank" class="clr-blue">' . $expense_report->receipt . '</a>
                </div>
            </div>

            <div class="form-group col-md-12 col-sm-12 col-xs-12">
                <label class="col-md-2 col-sm-1 col-xs-12 text-right">New Receipt:</label>
                <div class="col-md-10 col-sm-10 col-xs-10">
                        <span class="btn btn-white btn-file">
                            <span class="fileupload-new"><i class="fa fa-upload"></i> Upload</span>
                            <input name="receipt_file" type="file" id="edit_file_upload" value="" class="receipt expnse-fields" disabled/>
                        </span>
                </div>
            </div>

            <div class="form-group modal-footer m-t-0 modal-bttm">
                <button class="btn btn-default" type="button" data-dismiss="modal" aria-hidden="true"> Cancel </button>

                <input type="hidden" name="expense_rep_id" value="' . $expense_report->id . '">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <button class="btn btn-warning submit-expense-report expnse-fields" disabled type="submit"> Submit </button>
            </div>';
    }

    public function edit(Request $request)
    {
        $data = $request->all();
        $expense_rep_id = $data['expense_rep_id'];

        if ($request->isMethod('post')) {


            $expense_report          = PettyCash::find($expense_rep_id);
            $old_receipt_file        = $expense_report->receipt;
            $expense_report->title   = $data['expense_title'];
            $expense_report->details = $data['expense_detail'];
            $expense_report->txn_amount = $data['expense_amount'];

            /*$expense_report->user_id    = Auth::user()->id;
            $expense_report->title      = $data['expense_title'];
            $expense_report->details    = $data['expense_detail'];
            $expense_report->txn_type   = 'W';
            $expense_report->txn_amount = $data['expense_amount'];
            $expense_report->balance    = $remaining_balance;*/

            if (!empty($_FILES['receipt_file']['name'])) {

                $tmp_file   =   $_FILES['receipt_file']['tmp_name'];
                $file_info  =   pathinfo($_FILES['receipt_file']['name']);
                $ext        =   strtolower($file_info['extension']);
                $random_no  =   rand('11111', '99999');
                $new_name   =   time() . $random_no . '.' . $ext;

                if ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'doc' || $ext == 'docx' || $ext == 'pdf') {
                    $destination =   base_path() . pettyCashFilesBasePath;
                    if (move_uploaded_file($tmp_file, $destination . '/' . $new_name)) {
                        if (!empty($old_receipt_file)) {
                            if (file_exists($destination . '/' . $old_receipt_file)) {
                                unlink($destination . '/' . $old_receipt_file);
                            }
                        }
                        $expense_report->receipt = $new_name;
                    }
                }
            }

            if ($expense_report->save()) {

                return redirect()->back()->with('success', 'Report edited successfully.');
            } else {
                return redirect()->back()->with('error', COMMON_ERROR);
            }
        } else {
            echo '0';
            die;
        }
    }
}
