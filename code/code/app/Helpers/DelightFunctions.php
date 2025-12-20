<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DelightFunctions {

    public static function delightKitCountsByStatus($branchId, $status)
    {
        $count = DB::table('DELIGHT_KIT')
                                ->where(['STATUS' => $status,'SOL_ID' => $branchId])
                                ->count();

        return $count;
    }

    public static function delightKitCountsByStatusAndScheme($branchId, $schemeCode, $status)
    {
        $statusType = gettype($status);
        if($statusType == "integer")
        {
            $status = (array) $status;
        }

        $kitCount = DB::table('DELIGHT_KIT')
                                ->whereIn('STATUS',$status)
                                ->where(['SOL_ID' => $branchId,'SCHEME_CODE' => $schemeCode])
                                ->count();
        return $kitCount;
    }

    public static function getDelightSchemeCodes()
    {
        // Currently only savings scheme considered!
        $schemeCodes = DB::table('SCHEME_CODES')
                                    ->select('id',DB::raw("SCHEME_CODE || '-' || SCHEME_DESC as scheme_code"))
                                    ->where('ACCOUNT_TYPE',1)
                                    ->where('IS_ACTIVE',1)
                                    ->where('DELIGHT_SCHEME', 'Y')
                                    ->pluck('scheme_code','id')->toArray();

        return $schemeCodes;
    }

    public static function delightSchemeCodes()
    {
        // Currently only savings scheme considered!
        $schemeCodes = DB::table('SCHEME_CODES')
                                    ->where('ACCOUNT_TYPE',1)
                                    ->where('IS_ACTIVE',1)
                                    ->where('DELIGHT_SCHEME', 'Y')
                                    ->pluck('scheme_code','id')->toArray();

        return $schemeCodes;
    }

    public static function getDelightKitStatus()
    {
        // Currently only savings scheme considered!
        $delightSchemes = DB::table('DKIT_STATUS_LIST')->where(['IS_ACTIVE'=>1,'IS_VISIBLE'=>1])
                                                ->orderBy('RANK')
                                                ->pluck('dkit_status','id')->toArray();

        return $delightSchemes;
    }

    public static function getDelightKitStatusById($statusId)
    {
        $kitStatus = DB::table('DKIT_STATUS_LIST')
                                ->where('ID', $statusId)
                                ->get('dkit_status as kitstatus')->toArray();
        $kitStatus = (array) current($kitStatus);
        return $kitStatus;
    }

    public static function getDRStatusList()
    {
        $drStatusList = DB::table('DR_STATUS_LIST')
                                ->pluck('dr_status','id')->toArray();
        
        return $drStatusList;
    }

    public static function buildInsertData($data)
    {
        $dateOfBirth = Carbon::create(2000, 01, 01);
        $sysdate = Carbon::now();
        $aof_number = "9".str_pad($data['batchNumber'], 6, "0", STR_PAD_LEFT);

        $insertData = [
                        'BATCH_NUM' => $data['batchNumber'],
                        'CUST_ID' => '',
                        'TEMP_ACCT' => '',
                        'FORACID' => '',
                        'CUST_TITLE_CODE' => 'MR.',
                        'CUST_NAME' => 'DCB DELIGHT ACCOUNT',
                        'SHORT_NAME' => 'DCBDEL',
                        'CUST_STAFF_FLG' => $data['schemeCode'] == 'SB106' ? 'Y' : 'N',
						'CUST_STAFF_NUM' => $data['schemeCode'] == 'SB106' ? '0000000001' : '',
                        // 'CUST_STAFF_NUM' => '',
                        'CUST_MINOR_FLG' => 'N',
                        'CUST_ADDR_TYPE' => 'C',
                        'CUST_COMM_ADDRESS' => $data['batchNumber'],
                        'CUST_COMM_ADDRESS1' => '.',
                        'CUST_COMM_CITY_CODE' => '.',
                        'CUST_COMM_STATE_CODE' => '.',
                        'CUST_COMM_POSTAL_CODE' => 1000,
                        'CUST_COMM_COUNTRY_CODE' => 'IN',
                        'CUST_COMU_PHONE_NUM' => 0,
                        'CUST_PERM_ADDRESS' => '.',
                        'CUST_PERM_ADDRESS1' => '.',
                        'CUST_PERM_CITY_CODE' => '.',
                        'CUST_PERM_STATE_CODE' => '.',
                        'CUST_PERM_POSTAL_CODE' => 1000,
                        'CUST_PERM_COUNTRY_CODE' => 'IN',
                        'CUST_PERM_PHONE_NUM' => 0,
                        'CUST_EMP_ADDRESS' => '',
                        'CUST_EMP_ADDRESS1' => '',
                        'CUST_EMP_CITY_CODE' => '',
                        'CUST_EMP_STATE_CODE' => '',
                        'CUST_EMP_POSTAL_CODE' => '',
                        'CUST_EMP_COUNTRY_CODE' => '',
                        'CUST_EMP_PHONE_NUM' => '',
                        'CUST_OCCUPATION_CODE' => '000',
                        'CUST_CONSTITUTION_CODE' => '001',
                        'FREE_TXT2' => 'MOTHER-MAIDEN',
                        'FREE_TXT3' => 'N',
                        'FREE_TXT4' => '',
                        'FREE_TXT6' => '000',
                        'FREE_TXT7' => '',
                        'FREE_TXT8' => '',
                        'FREE_TXT9' => '',
                        'FREE_TXT11' => 'Y',
                        'FREE_TXT12' => '',
                        'FREE_TXT13' => '',
                        'FREE_TXT14' => '',
                        'FREE_TXT15' => '',
                        'DATE_OF_BIRTH' => $dateOfBirth,//to_date('01-JAN-00')
                        'CUST_SEX' => 'O',
                        'CUST_INTROD_CUST_ID' => '',
                        'CUST_INTRO_TITLE' => 'MR.',
                        'CUST_INTRO_NAME' => '.',
                        'CRNCY_CODE' => 'INR',
                        'CUST_MINOR_GUARDIAN' => '',
                        'SCHM_CODE' => $data['schemeCode'],
                        'MODE_OF_OPERATION' => '000',
                        'FREE_CODE_1' => '',
                        'FREE_CODE_2' => '',
                        'FREE_CODE_3' => '002',
                        'FREE_CODE_4' => '',
                        'FREE_CODE_5' => '',
                        'FREE_CODE_6' => '',
                        'FREE_CODE_8' => '',
                        'FREE_CODE_9' => '',
                        'FREE_CODE_10' => '',
                        'SWEEP_FLG' => 'N',
                        'DEBIT_ATM_FLG' => 'Y',
                        'CUST_EMBOSS_NAME' => 'DCB DELIGHT',//CUBE_shortname
                        'SOL_ID' => $data['solId'],
                        'DOC_RECEIVED_DATE' => $sysdate,//to_date(sysdate DD-MM-RRRR)
                        'TEXT' => '',
                        'PRE_OPN_FLG' => 'T',
                        'OPN_COMP_FLG' => '',
                        'UPL_USER_ID' => 'CUBE',
                        'UPL_TIME' => $sysdate,//SYSDATE
                        'UPLOAD_SOL' => '000',
                        'LCHG_USER_ID' => 'CUBE',
                        'LCHG_TIME' => $sysdate,//SYSDATE
                        'DOC_DUE_DATE' => '',
                        'REMARKS' => '',
                        'PAN_GIR_NUM' => '',
                        'NOMINEE_FLG' => 'N',
                        'NOMINEE_NAME' => '',
                        'NOM_RELATION' => '',
                        'NOM_ADDR1' => '',
                        'NOM_ADDR2' => '',
                        'NOM_CITY_CODE' => '',
                        'NOM_STATE_CODE' => '',
                        'NOM_CNTRY_CODE' => '',
                        'NOM_PIN_CODE' => '',
                        'NOM_MINOR_FLG' => '',
                        'NOM_DOB' => '',
                        'NOM_GUARD_CODE' => '',
                        'NOM_GUARD_NAME' => '',
                        'NOM_GUARD_ADDR_1' => '',
                        'NOM_GUARD_ADDR_2' => '',
                        'NOM_GUARD_CITY' => '',
                        'NOM_GUARD_STATE' => '',
                        'NOM_GUARD_COUNTRY' => '',
                        'NOM_GUARD_POSTAL' => '',
                        'ACCT_OPN_DATE' => '',
                        'MOBILE_NO' => '',
                        'EMAIL_ID' => '',
                        'GROSS_ANNUAL_INCOME' => 0,
                        'ACCT_MANAGER' => '',
                        'TELEX_FAX_NO' => '',
                        'REMARKS_FLD' => '.',
                        'AOF_NUMBER' => $aof_number,//CUBE_AOF 9_____batchnum
                        'CARD_TYPE' => $data['schemeCode'] == 'SB121' ? 'DEBIT-RUPAY-EMV' : 'DEBIT-VCLC-EMV',//DEBIT_CARD 
                        'ACCT_LABEL' => '',
                        'PASSBOOK_STMT' => 'S',//confirm as P
                        'TURNOVER' => 0,
                        'REL_TYPE' => '',
                        'REL_CODE' => '000',
                        'TRADE_FINANCE_CUST' => 'N',
                        'INLAND_TRADE' => 'N',
                        'EXPORT_IMP_FLAG' => '',
                        'CHRG_TURNOVER' => '',
                        'CHRG_CODE_FLAG' => '',
                        'NON_RESIDENT' => 'N',
                        'NAT_ID_CARD' => '',
                        'PASSPORT_NUM' => 'X',
                        'PASSPORT_DETAILS' => 'X',
                        'PASSPORT_EXPIRY' => '',
                        'DATE_NON_RESIDENT' => '',
                        'NATIONALITY' => 'IN',
                        'COUNTRY_CODE' => '',
                        'CASTE_CODE' => '006',
                        'CUST_STATUS' => 999,
                        'MARITAL_STAT' => 'SINGL',
                        'EDUCATION_CD' => '04',
                        'RESI_TYPE' => '01',
                        'CREDIT_FAC' => '00',
                        'VEHICLE_OWNED' => '04',
                        'JOINT_ACCT_CODE' => '001',
                        'FREE_TXT1' => $data['segment_code'], //Based on Input from Prasad
                        'FREE_CODE_7' => '0001',
                        'FREE_TXT10' => 1,
                        'FREE_TXT5' => '00000',
                        'TDS_TBL_CODE' => 'TDS01',
                        'OPN_TYPE_FLG' => 'B',
                        'GUARDIAN_NAME' => '',
                        'GUARD_ADDR1' => '',
                        'GUARD_ADDR2' => '',
                        'GUARD_CITY_CODE' => '',
                        'GUARD_STATE_CODE' => '',
                        'GUARD_CNTRY_CODE' => '',
                        'GUARD_PIN_CODE' => '',
                        'AADHAR_NO' => '',
                        'CHQ_REQ' => 'Y',
                        'PURGE_TEXT' => '',
                        'NRE_LCL_RELTN_PIN_CODE' => '',
                        'NRE_LCL_RELTN_PHONE_NUM' => '',
                        'MP_MEMO_TEXT' => '',
                        'NRE_LCL_RELTN_ADDR1' => '',
                        'NRE_LCL_RELTN_ADDR2' => '',
                        'NRE_LCL_RELTN_CITY_CODE' => '',
                        'NRE_LCL_RELTN_STATE_CODE' => '',
                        'NRE_LCL_RELTN_CNTRY_CODE' => '',
                        'ACCT_STATUS' => '',
                        'CUST_ISAUPD_FLG' => '',
                        'CHK_ALWD_FLG' => '',
                        'DEL_NOMINEE_FLG' => '',
                        'CUST_TYPE_CODE' => '095',
                        'FATHER_NAME' => '',
                        'SPOUSE_NAME' => '',
                        'IDENTIFICATION_TYPE' => 'Z',
                        'IDENTIFICATION_NUMBER' => 0,
                        'OCCUPATION_FATCA' => '',
                        'NATIONALITY_FATCA' => 'IN',
                        'CNTRY_OF_RESIDENCE' => 'IN',
                        'PLACE_OF_BIRTH' => 'IN',
                        'CNTRY_OF_BIRTH' => 'IN',
                        'TIN_NUMBER' => '',
                        'TIN_ISSUE_CNTRY' => '',
                        'ADDRESS_TYPE' => 1,
                        'MBL_TEL_NO' => 0,
                        'OTH_MBL_TEL_NO' => '',
                        'MBL_TEL_CNTRY_CD' => '',
                        'OTH_MBL_CNTRY_CD' => '',
                        'CKYC_UPL_TEAM' => '01',
                        'SEGMENT' => $data['segment_code'],
                        'SUB_SEGMENT' => ''
        ];
        return $insertData;
    }

    public static function kitsgeneratedbyfinacle($batchId, $solId, $schemeCode ){
            $table = env('FINACLE_TABLE_DELIGHT_KIT');
            $delightFinacleQueryInstance = DB::connection('oracle2')->table($table);

            $generatedByFinacle = $delightFinacleQueryInstance
                                                            ->where('BATCH_NUM', '1'.str_pad($batchId, 6, '0', STR_PAD_LEFT))
                                                            ->where('SOL_ID', $solId)
                                                            ->where('SCHM_CODE', $schemeCode)
                                                            ->where('CUST_ID','!=', null)
                                                            ->where('FORACID','!=', null)
                                                            ->get()->toArray();
            DB::disconnect('oracle2');
            //echo "<pre>";print_r(substr($batch_Num,-3));exit;
            return $generatedByFinacle;
    }
}
?>
