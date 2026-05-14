<?php

namespace App\Http\Controllers\frontEnd\Roster\PayrollFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\ServiceUser;
use App\Home;
use App\Models\ShiftCategory;
use App\Models\ScheduledShift;
use App\Models\RosterDailyLog;
use App\Models\Timesheet;
use App\Models\UserEmergencyContact;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceProduct;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MasterDataImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('import_file');
        $filePath = $file->getRealPath();

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            return back()->with('error', 'The uploaded file is empty or invalid.');
        }

        // Map headers to indices
        $headerMap = array_flip(array_map('trim', $header));

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        // Get current home ID as fallback
        $currentHomeId = Auth::user()->home_id ?? 1;

        while (($row = fgetcsv($handle)) !== false) {
            DB::beginTransaction();
            try {
                // 0. Process Home/Company
                $homeName = $this->getVal($row, $headerMap, 'home') ?: $this->getVal($row, $headerMap, 'Home');
                $homeId = $currentHomeId;
                if ($homeName) {
                    $home = Home::updateOrCreate(
                        ['title' => $homeName],
                        [
                            'weekly_changes' => $this->getVal($row, $headerMap, 'Weekly changes'),
                            'monthly_home_changes' => $this->getVal($row, $headerMap, 'Monthly home changes'),
                        ]
                    );
                    $homeId = $home->id;
                }

                // 1. Process Staff (User)
                $userName = $this->getVal($row, $headerMap, 'user name');
                $userEmail = $this->getVal($row, $headerMap, 'user email');
                $user = null;

                if ($userName && $userEmail) {
                    $user = User::updateOrCreate(
                        ['email' => $userEmail],
                        [
                            'name' => $userName,
                            'user_name' => $this->getVal($row, $headerMap, 'login user_name', explode('@', $userEmail)[0]),
                            'password' => Hash::make($this->getVal($row, $headerMap, 'user password', 'password123')),
                            'phone_no' => $this->getVal($row, $headerMap, 'user phone_no'),
                            'job_title' => $this->getVal($row, $headerMap, 'user job_title'),
                            'description' => $this->getVal($row, $headerMap, 'User description'),
                            'status' => $this->getVal($row, $headerMap, 'User status', 'Active') == 'Active' ? 1 : 0,
                            'personal_info' => $this->getVal($row, $headerMap, 'User personal_info'),
                            'banking_info' => $this->getVal($row, $headerMap, 'User banking_info'),
                            'user_type' => $this->getVal($row, $headerMap, 'user_type', 'Staff'),
                            'hourly_rate' => $this->getVal($row, $headerMap, 'User hourly_rate', 0),
                            'street' => $this->getVal($row, $headerMap, 'User street'),
                            'city' => $this->getVal($row, $headerMap, 'User city'),
                            'postcode' => $this->getVal($row, $headerMap, 'User postcode'),
                            'date_of_joining' => $this->getVal($row, $headerMap, 'User date_of_joining'),
                            'department' => $this->getVal($row, $headerMap, 'User department'),
                            'available_for_overtime' => $this->getVal($row, $headerMap, 'User available_for_overtime') == 'Yes' ? 1 : 0,
                            'employment_type' => $this->getVal($row, $headerMap, 'User employment_type'),
                            'dbs_certificate_number' => $this->getVal($row, $headerMap, 'User dbs_certificate_number'),
                            'dbs_expiry_date' => $this->getVal($row, $headerMap, 'User dbs_expiry_date'),
                            'home_id' => $homeId,
                        ]
                    );

                    // User Emergency Contact
                    $emNameUser = $this->getVal($row, $headerMap, 'User Emergency Contact Name');
                    if ($emNameUser) {
                        UserEmergencyContact::updateOrCreate(
                            ['user_id' => $user->id],
                            [
                                'name' => $emNameUser,
                                'phone' => $this->getVal($row, $headerMap, 'User Emergency Contact Phone'),
                                'relationship' => $this->getVal($row, $headerMap, 'User Emergency Contact relationship'),
                            ]
                        );
                    }
                }

                // 2. Process Service User (Client)
                $suName = $this->getVal($row, $headerMap, 'Service user name') ?: $this->getVal($row, $headerMap, 'Client Name');
                $suEmail = $this->getVal($row, $headerMap, 'service user email');
                $serviceUser = null;

                if ($suName) {
                    $serviceUser = ServiceUser::updateOrCreate(
                        ['name' => $suName],
                        [
                            'user_name' => $this->getVal($row, $headerMap, 'service login user_name'),
                            'email' => $suEmail,
                            'password' => Hash::make($this->getVal($row, $headerMap, 'Service user password', 'password123')),
                            'date_of_birth' => $this->getVal($row, $headerMap, 'Service User date_of_birth'),
                            'phone_no' => $this->getVal($row, $headerMap, 'Service User phone_no'),
                            'admission_number' => $this->getVal($row, $headerMap, 'Service user admission_number'),
                            'start_date' => $this->getVal($row, $headerMap, 'Service User start_date'),
                            'end_date' => $this->getVal($row, $headerMap, 'Service User end_date'),
                            'short_description' => $this->getVal($row, $headerMap, 'Service User short_description'),
                            'status' => $this->getVal($row, $headerMap, 'Service User status', 'Active'),
                            'street' => $this->getVal($row, $headerMap, 'Service User street'),
                            'city' => $this->getVal($row, $headerMap, 'Service User city'),
                            'postcode' => $this->getVal($row, $headerMap, 'Service User postcode'),
                            'department' => $this->getVal($row, $headerMap, 'Service User Department'),
                            'height_unit' => $this->getVal($row, $headerMap, 'height_unit'),
                            'height_ft' => $this->getVal($row, $headerMap, 'height_ft'),
                            'height_in' => $this->getVal($row, $headerMap, 'height_in'),
                            'weight_unit' => $this->getVal($row, $headerMap, 'weight_unit'),
                            'weight' => $this->getVal($row, $headerMap, 'weight'),
                            'hair_and_eyes' => $this->getVal($row, $headerMap, 'hair_and_eyes'),
                            'markings' => $this->getVal($row, $headerMap, 'markings'),
                            'suMobility' => $this->getVal($row, $headerMap, 'Mobility'),
                            'suFundingType' => $this->getVal($row, $headerMap, 'FundingType'),
                            'ethnicity_id' => $this->getVal($row, $headerMap, 'ethnicity'), // Raw value for now
                            'allergies' => $this->getVal($row, $headerMap, 'allergies'),
                            'medical_notes' => $this->getVal($row, $headerMap, 'medical_notes'),
                            'care_needs' => $this->getVal($row, $headerMap, 'care_needs'),
                            'em_name' => $this->getVal($row, $headerMap, 'em_name'),
                            'em_phone' => $this->getVal($row, $headerMap, 'em_phone'),
                            'relationship' => $this->getVal($row, $headerMap, 'Service User relationship'),
                            'home_id' => $homeId,
                        ]
                    );
                }

                // 3. Process Shift Category
                $catName = $this->getVal($row, $headerMap, 'Category Name');
                if ($catName) {
                    ShiftCategory::updateOrCreate(
                        ['name' => $catName, 'home_id' => $homeId],
                        [
                            'color' => $this->getVal($row, $headerMap, 'Color', '#000000'),
                            'start_time' => $this->getVal($row, $headerMap, 'Start Time'),
                            'end_time' => $this->getVal($row, $headerMap, 'End Time'),
                            'rate' => $this->getVal($row, $headerMap, 'Rate', 0),
                        ]
                    );
                }

                // 4. Process Scheduled Shift
                $shiftStartDate = $this->getVal($row, $headerMap, 'shift start_date');
                $shift = null;
                if ($shiftStartDate && $user && $serviceUser) {
                    $shift = ScheduledShift::create([
                        'service_user_id' => $serviceUser->id,
                        'staff_id' => $user->id,
                        'home_id' => $homeId,
                        'start_date' => $shiftStartDate,
                        'end_date' => $this->getVal($row, $headerMap, 'Shift end_date'),
                        'start_time' => $this->getVal($row, $headerMap, 'Shift start_time'),
                        'end_time' => $this->getVal($row, $headerMap, 'Shift end_time'),
                        'hourly_rate' => $this->getVal($row, $headerMap, 'Shift hourly_rate', 0),
                        'location_name' => $this->getVal($row, $headerMap, 'Location Name'),
                        'location_address' => $this->getVal($row, $headerMap, 'Location Address'),
                        'assignment' => $this->getVal($row, $headerMap, 'assignment', 'Client'),
                        'tasks' => $this->getVal($row, $headerMap, 'tasks'),
                        'status' => $this->getVal($row, $headerMap, 'Shift status', 'assigned'),
                        'notes' => $this->getVal($row, $headerMap, 'Shift notes'),
                        'is_recurring' => $this->getVal($row, $headerMap, 'is_recurring', 0),
                    ]);
                }

                // 5. Process Login Activity (Logs)
                $loginDate = $this->getVal($row, $headerMap, 'login_date');
                if ($loginDate && $user) {
                    RosterDailyLog::create([
                        'user_id' => $user->id,
                        'shift_id' => $shift ? $shift->id : null,
                        'home_id' => $homeId,
                        'date' => $loginDate,
                        'check_in' => $this->getVal($row, $headerMap, 'check_in_time'),
                        'check_out' => $this->getVal($row, $headerMap, 'check_out_time'),
                        'latitude_in' => $this->getVal($row, $headerMap, 'latitude_in'),
                        'longitude_in' => $this->getVal($row, $headerMap, 'longitude_in'),
                        'latitude_out' => $this->getVal($row, $headerMap, 'latitude_out'),
                        'longitude_out' => $this->getVal($row, $headerMap, 'longitude_out'),
                        'check_in_reason' => $this->getVal($row, $headerMap, 'check_in_reason'),
                        'check_out_reason' => $this->getVal($row, $headerMap, 'check_out_reason'),
                        'status' => $this->getVal($row, $headerMap, 'login activity status', 1),
                    ]);
                }

                // 6. Process Timesheet
                $tsDate = $this->getVal($row, $headerMap, 'Timesheet date');
                if ($tsDate && $user) {
                    Timesheet::create([
                        'staff_id' => $user->id,
                        'home_id' => $homeId,
                        'date' => $tsDate,
                        'clock_in' => $this->getVal($row, $headerMap, 'Time sheet clock_in'),
                        'clock_out' => $this->getVal($row, $headerMap, 'Time Sheet clock_out'),
                        'status' => $this->getVal($row, $headerMap, 'Time Sheet status', 'Approved'),
                        'notes' => $this->getVal($row, $headerMap, 'Time Sheet notes'),
                        'category_id' => $this->getVal($row, $headerMap, 'Time Sheet category'),
                    ]);
                }

                // 7. Process Invoices (Staff/General)
                $invRef = $this->getVal($row, $headerMap, 'invoice_ref');
                if ($invRef) {
                    $invoice = Invoice::updateOrCreate(
                        ['invoice_ref' => $invRef, 'home_id' => $homeId],
                        [
                            'invoice_date' => $this->getVal($row, $headerMap, 'invoice_date'),
                            'due_date' => $this->getVal($row, $headerMap, 'invoice due_date'),
                            'sub_total' => $this->getVal($row, $headerMap, 'sub_total', 0),
                            'VAT_amount' => $this->getVal($row, $headerMap, 'VAT_amount', 0),
                            'Total' => $this->getVal($row, $headerMap, 'Total', 0),
                            'outstanding' => $this->getVal($row, $headerMap, 'outstanding', 0),
                            'status' => $this->getVal($row, $headerMap, 'invoice status', 'Paid'),
                            'customer_notes' => $this->getVal($row, $headerMap, 'invoice notes'),
                            'customer_id' => $serviceUser ? $serviceUser->id : null,
                        ]
                    );

                    // Add Invoice Item
                    $itemName = $this->getVal($row, $headerMap, 'item_name');
                    if ($itemName) {
                        InvoiceProduct::create([
                            'invoice_id' => $invoice->id,
                            'item_name' => $itemName,
                            'item_description' => $this->getVal($row, $headerMap, 'item_description'),
                            'qty' => $this->getVal($row, $headerMap, 'quantity', 1),
                            'price' => $this->getVal($row, $headerMap, 'rate', 0),
                            'sub_total' => $this->getVal($row, $headerMap, 'sub_total', 0),
                            'VAT_amount' => $this->getVal($row, $headerMap, 'VAT_amount', 0),
                            'Total' => $this->getVal($row, $headerMap, 'Total', 0),
                            'home_id' => $homeId,
                        ]);
                    }
                }

                // 8. Process Client Invoicing (Specific)
                $clientInvRef = $this->getVal($row, $headerMap, 'Invoice Ref');
                if ($clientInvRef && $serviceUser) {
                    $clientInvoice = Invoice::updateOrCreate(
                        ['invoice_ref' => $clientInvRef, 'home_id' => $homeId],
                        [
                            'customer_id' => $serviceUser->id,
                            'invoice_date' => $this->getVal($row, $headerMap, 'Invoice Date'),
                            'due_date' => $this->getVal($row, $headerMap, 'Due Date'),
                            'status' => $this->getVal($row, $headerMap, 'Status', 'Draft'),
                            'Total' => $this->getVal($row, $headerMap, 'Price', 0),
                        ]
                    );

                    $clientItemName = $this->getVal($row, $headerMap, 'Item Name');
                    if ($clientItemName) {
                        InvoiceProduct::create([
                            'invoice_id' => $clientInvoice->id,
                            'item_name' => $clientItemName,
                            'item_description' => $this->getVal($row, $headerMap, 'Item Description'),
                            'item_code' => $this->getVal($row, $headerMap, 'Item Code'),
                            'qty' => $this->getVal($row, $headerMap, 'Quantity', 1),
                            'price' => $this->getVal($row, $headerMap, 'Price', 0),
                            'vat' => $this->getVal($row, $headerMap, 'VAT %', 0),
                            'discount' => $this->getVal($row, $headerMap, 'Discount', 0),
                            'is_care_service' => $this->getVal($row, $headerMap, 'Is Care Service') == 'Yes' ? 1 : 0,
                            'is_expense' => $this->getVal($row, $headerMap, 'Is Expense') == 'Yes' ? 1 : 0,
                            'funding_name' => $this->getVal($row, $headerMap, 'Funding Name'),
                            'funding_value' => $this->getVal($row, $headerMap, 'Funding Value'),
                            'funding_type' => $this->getVal($row, $headerMap, 'Funding Type (Percentage/Fixed)'),
                            'home_id' => $homeId,
                        ]);
                    }
                }

                DB::commit();
                $successCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $errorCount++;
                $errors[] = "Row error at row " . ($successCount + $errorCount + 1) . ": " . $e->getMessage();
                Log::error("Import Row Error: " . $e->getMessage());
            }
        }

        fclose($handle);

        $message = "Import complete. $successCount records processed successfully.";
        if ($errorCount > 0) {
            $message .= " $errorCount rows skipped due to errors.";
        }

        return back()->with('success', $message)->with('import_errors', $errors);
    }

    private function getVal($row, $map, $key, $default = null)
    {
        if (isset($map[$key]) && isset($row[$map[$key]])) {
            $val = trim($row[$map[$key]]);
            return $val === '' ? $default : $val;
        }
        return $default;
    }
}
