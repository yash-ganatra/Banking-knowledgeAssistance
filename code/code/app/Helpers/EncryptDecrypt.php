<?php
namespace App\Helpers;

use App\Helpers\CommonFunctions;
use App\Helpers\Api;
use Sop\JWX\JWE\JWE;
use Sop\JWX\JWE\CompressionAlgorithm\DeflateAlgorithm;
use Sop\JWX\JWE\KeyAlgorithm\A256KWAlgorithm;
use Sop\JWX\JWE\EncryptionAlgorithm\A128CBCHS256Algorithm;
use Sop\JWX\JWK\Symmetric\SymmetricKeyJWK;

class EncryptDecrypt {

    /*
     * Method Name: AES128Encryption
     * Created By : Sharanya T
    
     * Description:
     * This function is used to encrypt data
     *
     * Input Params:
     * @params $data, $key
     *
     * Output:
     * Returns encrypted data
     */
    public static function AES128Encryption($data = '', $key = "")
    {
        try {
            //checking data and key are not empty
            if($key != NULL && $data != ""){
                //method to encrypt data
                $method = "AES-128-CBC";
                //Encoding to UTF-8
                $key = mb_convert_encoding($key, "UTF-8");
                //Randomly generate IV and salt
                $salt = random_bytes(20);
                $IVbytes = random_bytes(16);
                //Derive the SecretKey
                $hash = openssl_pbkdf2($key,$salt,'128','50', 'sha1'); 
                //Encrypt Data
                $encrypted = openssl_encrypt($data, $method, $hash, OPENSSL_RAW_DATA, $IVbytes);
                //Concatenate salt, IV and encrypted text and base64-encode the result
                $result = base64_encode($salt.$IVbytes.$encrypted);
                return $result;
            }else{
                return "String to encrypt, Key is required.";
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/EncryptDecrypt','AES128Encryption',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
     * Method Name: AES128Decryption
     * Created By : Sharanya T
    
     * Description:
     * This function is used to decrypt data
     *
     * Input Params:
     * @params $encryptedString, $key
     *
     * Output:
     * Returns decrypted data
     */
    public static function AES128Decryption($encryptedString='', $key='')
    {
        try {
            if($key != NULL && $encryptedString != ""){
                //method to decrypt data
                $method = "AES-128-CBC";
                //Encoding to UTF-8
                $key = mb_convert_encoding($key, "UTF-8");
                //Base64-decode encryptedString
                $dataDecoded = base64_decode($encryptedString);
                //Derive salt, IV and encrypted text from decoded data
                $salt = substr($dataDecoded,0,20); 
                $IVbytes = substr($dataDecoded,20,16); 
                $dataEncrypted = substr($dataDecoded,36);
                //Derive the SecretKey
                $hash = openssl_pbkdf2($key,$salt,'128','50', 'sha1'); 
                //Decrypt encoded data
                $decrypted = openssl_decrypt($dataEncrypted, $method, $hash, OPENSSL_RAW_DATA, $IVbytes);
                return $decrypted;
            }else{
                return "Encrypted String to decrypt, Key is required.";
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/EncryptDecrypt','AES128Decryption',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
     * Method Name: AES256Encryption
     * Created By : Sharanya T
    
     * Description:
     * This function is used to encrypt data
     *
     * Input Params:
     * @params $data, $key
     *
     * Output:
     * Returns encrypted data
     */
    public static function AES256Encryption($data = '', $key = "")
    {
        try {
            //checking data and key are not empty
            if($key != NULL && $data != ""){
                //method to encrypt data
                $method = "AES-256-CBC";
                //Encoding to UTF-8
                $key = mb_convert_encoding($key, "UTF-8");
                //Randomly generate IV and salt
                $salt = random_bytes(20);
                $IVbytes = random_bytes(16);
                //Derive the SecretKey
                $hash = openssl_pbkdf2($key,$salt,'256','50', 'sha1'); 
                //Encrypt Data
                $encrypted = openssl_encrypt($data, $method, $hash, OPENSSL_RAW_DATA, $IVbytes);
                //Concatenate salt, IV and encrypted text and base64-encode the result
                $result = base64_encode($salt.$IVbytes.$encrypted);
                return $result;
            }else{
                return "String to encrypt, Key is required.";
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/EncryptDecrypt','AES256Encryption',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    /*
     * Method Name: AES256Decryption
     * Created By : Sharanya T
    
     * Description:
     * This function is used to decrypt data
     *
     * Input Params:
     * @params $encryptedString, $key
     *
     * Output:
     * Returns decrypted data
     */
    public static function AES256Decryption($encryptedString='', $key='')
    {
        try {
            if($key != NULL && $encryptedString != ""){
                //method to decrypt data
                $method = "AES-256-CBC";
                //Encoding to UTF-8
                $key = mb_convert_encoding($key, "UTF-8");
                //Base64-decode encryptedString
                $dataDecoded = base64_decode($encryptedString);
                //Derive salt, IV and encrypted text from decoded data
                $salt = substr($dataDecoded,0,20); 
                $IVbytes = substr($dataDecoded,20,16);
                $dataEncrypted = substr($dataDecoded,36);
                //Derive the SecretKey
                $hash = openssl_pbkdf2($key,$salt,'256','50', 'sha1'); 
                //Decrypt encoded data
                $decrypted = openssl_decrypt($dataEncrypted, $method, $hash, OPENSSL_RAW_DATA, $IVbytes);
                return $decrypted;
            }else{
                return "Encrypted String to decrypt, Key is required.";
            }
        }
        catch(\Illuminate\Database\QueryException $e) {
            if(env('APP_CUBE_DEBUG')) {dd($e->getMessage());}
            $eMessage = $e->getMessage();
            //CommonFunctions::addExceptionLog($eMessage, $request);
            CommonFunctions::addLogicExceptionLog('Helpers/EncryptDecrypt','AES256Decryption',$eMessage);
            return json_encode(['status'=>'fail','msg'=>'Error! Please try again','data'=>[]]);
        }
    }

    public static function JWEEncryption($payload)
    {
        //$key = "BECEDE9F047E88314D6A9347B90E2BECF2AF3805F5DCE0DC2DA33713884A1D9A";
        $key = config('constants.APPLICATION_SETTINGS.JWE_ENC_KEY');
        $key = hex2bin($key);
        $key_algo = new A256KWAlgorithm($key);
        $enc_algo = new A128CBCHS256Algorithm();
        // DEF as a compression algorithm
        $zip_algo = new DeflateAlgorithm();
        // encrypt payload to produce JWE
        $jwe = JWE::encrypt($payload, $key_algo, $enc_algo, $zip_algo);
        $jwe = explode('.', $jwe);
        $response = array();
        $response['recipients'][]['encrypted_key'] = $jwe[1];
        $response['protected'] = $jwe[0];
        $response['iv'] = $jwe[2];
        $response['ciphertext'] = $jwe[3];
        $response['tag'] = $jwe[4];
        return json_encode($response);       // echo "<pre>";print_r(json_encode($response));exit;
    }


    public static function JWEDecryption($decodeString)
    {
        $response = json_decode($decodeString,true);
        $string = $response['protected'].'.'.$response['recipients'][0]['encrypted_key'].'.'.$response['iv'].'.'.
                    $response['ciphertext'].'.'.$response['tag'];
        //$key = "BECEDE9F047E88314D6A9347B90E2BECF2AF3805F5DCE0DC2DA33713884A1D9A";
        $key = config('constants.APPLICATION_SETTINGS.JWE_ENC_KEY');
        $key = hex2bin($key);
        // create a JSON Web Key from password
        $jwk = SymmetricKeyJWK::fromKey($key);
        // read JWE token from the first argument
        $jwe = JWE::fromCompact($string);
        // decrypt the payload using a JSON Web Key
        // $payload = $jwe->decryptWithJWK($jwk);

        $key_algo = new A256KWAlgorithm($key);
        $enc_algo = new A128CBCHS256Algorithm();
        $payload = $jwe->decrypt($key_algo, $enc_algo);     
        return json_decode($payload,true);
    }

    public static function JWERSAEncryption($payload)
    {
        //$key = "BECEDE9F047E88314D6A9347B90E2BECF2AF3805F5DCE0DC2DA33713884A1D9A";
        $key = config('constants.APPLICATION_SETTINGS.JWE_ENC_KEY');
        $key = hex2bin($key);
        // $key_algo = new A256KWAlgorithm($key);
        $key_algo = new RSAOAEP256($key);
        $enc_algo = new A128CBCHS256Algorithm();
        // DEF as a compression algorithm
        $zip_algo = new DeflateAlgorithm();
        // encrypt payload to produce JWE
        $jwe = JWE::encrypt($payload, $key_algo, $enc_algo, $zip_algo);
        $jwe = explode('.', $jwe);
        $response = array();
        $response['recipients'][]['encrypted_key'] = $jwe[1];
        $response['protected'] = $jwe[0];
        $response['iv'] = $jwe[2];
        $response['ciphertext'] = $jwe[3];
        $response['tag'] = $jwe[4];
        return json_encode($response);
    }

    public static function Encryption($formId)
    {
        $command = env('JAVA_BINARY').' -jar '.public_path().'/RSAcli.jar ENC /var/www/html/CUBE/storage/uploads/Api/form_'.$formId.'.xml '.config('constants.APPLICATION_SETTINGS.FTR_ENC_KEY');
        // $command = 'java -jar '.public_path().'/RSAcli.jar ENC '.public_path().'/uploads/Api/form_'.$formId.'.xml 30820122300D06092A864886F70D01010105000382010F003082010A0282010100A266665E8EBD10B6E3014B2E6AEF0E6CA05B100F4FB3D129564DBF4C8415C257EC4D327E319ED3B42244B6CE3A3132B152FC0BC7FAC9127D004323945CD62506CD7DA4942E446F781D652369B79A7A694BEDB38909CE684A85F727ED0DEBFAF524D485145A3266D28EE4C04038481C4DEC528B345A6F143F4D82DE5CF5CCD6230665B2BC213909D369043AC638453D33AFF4B4570D7819EF242F8A326B8DF927F9029ED465E2007E38CE7506DC0A48F3A3C0D23F2B67B8180E325281268B142B69650A531AF5DB6C69649FE0E65F32399148471FFA0CA80E555F1B73E964D4C6E38CB428778370B5F5B84975C42556DA647EE41266D64162EE188759DC5724DF0203010001';
        exec($command,$output);
        return $output;
    }

    public static function Decryption($formId)
    {
        // $command = 'java -jar '.public_path().'/RSAcli.jar DEC '.public_path().'/uploads/Api/form_response_'.$formId.'.txt 308204be020100300d06092a864886f70d0101010500048204a8308204a402010002820101008b224cd84b3d81f26041169d0ab7de964492e865e47152f232beac7a5a3a9ab44743c4fa3c11bea0ecc7c5dda4d5bdb2697c030e8c5f3b2d0b2bbc0bb4316e7409aaf167ad12a48a96a738e509a9755ddd2b8ff4dd63814f1f9916e92a22e55dc2e50a43e4e42c3d22482cb2005a4cd27f2a5c4a19e4f3bf94a3b010e1c1ecec44769e5b279358f02b921ac2c76e3c9b5d7f16c1d98fa68c4e08b5b9457f5766a73d3e856a2fa40bb104da384a68cebe344fffd28f741fb3e01dbc4122a9b73b89fb619d7c06586baa1119b744cec0134d5e01e3e4773a4dd924121479f093063bcf317f863cdfa5204f69fe874db02a89f93bf38f3cbc5b83c5f2d2d015109d0203010001028201002e9f72d2b3fbcb74b5ce79ce6c0e0b379d8900f94be0b1a3d95f4d9abe6b8ef5c43fb05d0e0e002c1acc22c0d00093cd9a4d6514d5f2ff786b9adf2ba8d93ba304ae54b0a22d7217c2be0c71ab982e6d22b4fb4b5978839749065ac80d479e469a0d7830221e57b8b0a930d901074160f41df4c91bfe3db3c0124de88a4e1a9edad36c5e6b8d4f2a5f4687468fefc96d8be7caa728d4a5d608c9929a6f9fbacbe612dc729ed482cf6dd8190f4c8b35e3f7787ede1c417fa8e74fb0777bff13e2026296ffb677027a9c5dd6c51579f9482a1579f5d06052bfb7f46218062a96ab372b42a830b5a06b31a690a88f54b268e35ccf93cdcc066086d45a246139a1a102818100f4d3c97c7007ee0394f0eb44ffefc7611a4fc8846fbf220c0a96e007fcd278a2a246b30ed8146c0348319740c3d07a205cdd169b85e088ebec18949a448db1bb777117d0ed924006d584f7e1f3740cc374f3e978dc65c2fc8c2a7bf0c23757b18cb93ca89df30153574280f0930f886fae079d19ca82068956204938c8c8abc902818100917bbe44d261da1cd4ea0f0f7761ffb58e10caec8df71836003c1d56258703d0e9dffb7b49e0ba64e38262e11435d8ce21e898ae6170aa5f311b92edef63e96ae0775d4c380516082b9bc638b9dedbe66a39881cc32678789c94612d48f982b2895baaded1775a484bb488eae3cdaafb326b4f160ae819c60a7ab1ed8bfd803502818100f1f17db3208f65925a94ff1b7005883618a77194174e7e1a0238f96a4b59bf67911067112b4e7b337f1baaca90f82c48611d07a367d8edd69fb9e6d8d11869e4c5f398429b14257bdb740cf758fc7d44870627da8d8b6ef6de99796402ba684a08b462128c0cc26996d91ccd21b77ef046be356d7067b087f5f9f00252c1a4a90281803ff29b8cd6c8ea3db81cf5e7bf7b151231d311cf6f0b88d9dbba90ce980a43d425a92d7a60dbe632888a7c7a210f16306d59371b977b157868368d9c0698ed35ec5aab68e04ae4074d5bd88280b90401c0f96ee9eda1d05c7bbf01040dca96f7714718d17e532f1d0e21d9f91d8efeb411d2775de20769a4d260622b5f585b6d028181008f9207e209d849fb789688a8d065fc93d5e1d120c5871dc16fb7cb0e43102f4a777bccab4c14e9d5b6ffcd23bf7db12f8ec4c1371116ca9307d32a6b1b398578c958668fd074afb140259d633e5cd3932f294415f304806e8265cb84a7cefe031200f3b195616231d01b23b0e86ec88e3e66e11e65fcd4c20d17bbfa360c9e3d'; 
        $command = env('JAVA_BINARY').' -jar '.public_path().'/RSAcli.jar DEC /var/www/html/CUBE/storage/uploads/Api/form_response_'.$formId.'.txt '.config('constants.APPLICATION_SETTINGS.FTR_DEC_KEY');
		
        exec($command,$output);
        $output = implode('',$output);
        $output = str_replace("NS2:","",$output);
        $output = str_replace("NS1:","",$output);
        $output = simplexml_load_string($output);
        $output = json_decode(json_encode($output),true);		
        return $output; 
    }
}
?>