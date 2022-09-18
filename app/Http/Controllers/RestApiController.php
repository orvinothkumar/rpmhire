<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RestApiController extends Controller
{

    private function generateUniquePIN()
    {
        $regenerateNumber = true;
        do {
            $ranNumber = rand(100000, 999999);
            $query = "SELECT * FROM users WHERE otp = '$ranNumber'";
            $results = DB::select($query);
            $total = count($results);
            if ($total == 0) {
                $regenerateNumber = false;
            }
        } while ($regenerateNumber);
        return $ranNumber;
    }

    private function validateUserId($userUUID)
    {
        $query = "SELECT id FROM `users` where id='$userUUID' and status=1 LIMIT 1";
        $results = DB::select($query);
        return $results;
    }

    public function signUpOtp(Request $req)
    {
        $response = array();
        $responseCode = 500;
        $rules = array(
            "emailAddress" => "required",
            "firstName" => "required",
            "lastName" => "required",
            "birthDate" => "required",
            "mobileNumber" => "required",
            "username" => "required",
            "password" => "required",
            "type" => "required",
            "address" => "required",
        );
        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            $response = array('status' => 'error', 'message' => 'Invalid parameters', "errors" => $validator->errors());
            $responseCode = 400;
        } else {
            $emailAddress = $req->emailAddress;
            $firstName = $req->firstName;
            $lastName = $req->lastName;
            $birthDate = $req->birthDate;
            $mobileNumber = $req->mobileNumber;
            $username = $req->username;
            $password = $req->password;
            $type = $req->type;
            $address = $req->address;
            $otp = $this->generateUniquePIN();

            $query = "SELECT * FROM `users` WHERE (email='$emailAddress' OR username='$username')";
            $results = DB::select($query);
            $total = count($results);

            if ($total == 0) {
                try {
                    $Insertid = User::create([
                        'name' => trim($firstName . ' ' . $lastName),
                        'first_name' => trim($firstName),
                        'last_name' => trim($lastName),
                        'username' => $username,
                        'user_type' => $type,
                        'email' => $emailAddress,
                        'mobile' => $mobileNumber,
                        'dob' => $birthDate,
                        'otp' => $otp,
                        'address' => $address,
                        'validity_date' => date('Y-m-d H:i:s', strtotime('+1 year')),
                        'password' => Hash::make($password),
                        'role_id' => 2,
                        'created_by' => 1,
                        'is_verified' => 0,
                        'status' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ])->id;

                    if ($Insertid > 0) {
                        // $hashed_token = password_hash($Insertid, PASSWORD_BCRYPT, array('cost' => 5));
                        $data = [
                            'subject' => 'OTP Verification Email',
                            'email' => $req->emailAddress,
                            'content' => 'OTP Verification Code is : ' . $otp,
                        ];

                        Mail::to($data['email'])->send(new SendMail($data));
                        $response = array('status' => 'success', 'message' => 'An account has been created for ' . $emailAddress . ' successfully. Please check your email for OTP verification');
                        $responseCode = 200;
                    } else {
                        $response = array('status' => 'error', "message" => "Error on signing up");
                        $responseCode = 200;
                    }
                } catch (QueryException | \Exception $e) {
                    $response = array('status' => 'error', "message" => "Error on signing up", "errors" => $e->getMessage());
                    $responseCode = 200;
                }
            } else {
                if ($results[0]->is_verified == 1) {
                    $response = array('status' => 'error', 'message' => 'EmailAddress/Username already exists!');
                    $responseCode = 200;
                } else {
                    try {
                        $updated = DB::table('users')
                            ->where('id', $results[0]->id)
                            ->update([
                                'name' => trim($firstName . ' ' . $lastName),
                                'first_name' => trim($firstName),
                                'last_name' => trim($lastName),
                                'username' => $username,
                                'user_type' => $type,
                                'email' => $emailAddress,
                                'mobile' => $mobileNumber,
                                'dob' => $birthDate,
                                'otp' => $otp,
                                'address' => $address,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                        if ($updated) {
                            $data = array();
                            $data['userUUID'] = $results[0]->id;
                            $data['fullName'] = trim($firstName . ' ' . $lastName);
                            $data['emailAddress'] = $emailAddress;
                            $data = [
                                'subject' => 'OTP Verification Email',
                                'email' => $req->emailAddress,
                                'content' => 'OTP Verification Code is : ' . $otp,
                            ];

                            Mail::to($data['email'])->send(new SendMail($data));
                            $response = array('status' => 'success', 'message' => 'An account has been created for ' . $emailAddress . ' successfully. Please check your email for OTP verification', 'data' => $data);
                            $responseCode = 200;
                        } else {
                            $response = array('status' => 'error', "Error on signing up");
                            $responseCode = 200;
                        }
                    } catch (QueryException | \Exception $e) {
                        $response = array('status' => 'error', "message" => "Error on signing up", "errors" => $e->getMessage());
                        $responseCode = 200;
                    }
                }
            }
        }
        return response()->json($response, $responseCode);
    }

    public function signUp(Request $req)
    {
        $response = array();
        $responseCode = 500;
        $rules = array(
            "emailAddress" => "required",
            "firstName" => "required",
            "lastName" => "required",
            "birthDate" => "required",
            "mobileNumber" => "required",
            "username" => "required",
            "password" => "required",
            "type" => "required",
            "address" => "required",
            "otp" => "required",
        );
        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            $response = array('status' => 'error', 'message' => 'Invalid parameters', "errors" => $validator->errors());
            $responseCode = 400;
        } else {
            $emailAddress = $req->emailAddress;
            $firstName = $req->firstName;
            $lastName = $req->lastName;
            $birthDate = $req->birthDate;
            $mobileNumber = $req->mobileNumber;
            $username = $req->username;
            $password = $req->password;
            $type = $req->type;
            $address = $req->address;
            $otp = $req->otp;

            $query = "SELECT id FROM `users` WHERE (email='$emailAddress' AND otp='$otp')";
            $results = DB::select($query);
            $total = count($results);

            if ($total > 0) {
                try {
                    $updated = DB::table('users')
                        ->where('id', $results[0]->id)
                        ->update([
                            'name' => trim($firstName . ' ' . $lastName),
                            'first_name' => trim($firstName),
                            'last_name' => trim($lastName),
                            'username' => $username,
                            'user_type' => $type,
                            'email' => $emailAddress,
                            'mobile' => $mobileNumber,
                            'dob' => $birthDate,
                            'address' => $address,
                            'otp' => '',
                            'is_verified' => 1,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                    if ($updated) {
                        $data = array();
                        $data['userUUID'] = $results[0]->id;
                        $data['fullName'] = trim($firstName . ' ' . $lastName);
                        $data['emailAddress'] = $emailAddress;
                        $response = array('status' => 'success', 'message' => 'Signed up successfully.', 'data' => $data);
                        $responseCode = 200;
                    } else {
                        $response = array('status' => 'error', "Error on OTP verification");
                        $responseCode = 200;
                    }
                } catch (QueryException | \Exception $e) {
                    $response = array('status' => 'error', "message" => "Error on OTP verification", "errors" => $e->getMessage());
                    $responseCode = 200;
                }
            } else {
                $response = array('status' => 'error', 'message' => 'Invalid OTP!');
                $responseCode = 200;
            }
        }
        return response()->json($response, $responseCode);
    }

    public function resendSignupOtp(Request $req)
    {
        $response = array();
        $responseCode = 500;
        $rules = array(
            "emailAddress" => "required",
        );
        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            $response = array('status' => 'error', 'message' => 'Invalid parameters', "errors" => $validator->errors());
            $responseCode = 400;
        } else {
            $emailAddress = $req->emailAddress;
            $otp = $this->generateUniquePIN();

            $query = "SELECT * FROM `users` WHERE (email='$emailAddress' AND is_verified = 0)";
            $results = DB::select($query);
            $total = count($results);

            if ($total > 0) {
                try {
                    $updated = DB::table('users')
                        ->where('id', $results[0]->id)
                        ->update([
                            'otp' => $otp,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                    if ($updated) {
                        $data = [
                            'subject' => 'OTP Verification Email',
                            'email' => $req->emailAddress,
                            'content' => 'OTP Verification Code is : ' . $otp,
                        ];

                        Mail::to($data['email'])->send(new SendMail($data));
                        $response = array('status' => 'success', 'message' => 'OTP was sent to ' . $req->emailAddress . ' successfully.');
                        $responseCode = 200;
                    } else {
                        $response = array('status' => 'error', "Error on resend otp");
                        $responseCode = 200;
                    }
                } catch (QueryException | \Exception $e) {
                    $response = array('status' => 'error', "message" => "Error on resend otp", "errors" => $e->getMessage());
                    $responseCode = 200;
                }
            } else {
                $response = array('status' => 'error', 'message' => 'Invalid EmailAddress/Username/MobileNumber');
                $responseCode = 200;
            }
        }
        return response()->json($response, $responseCode);
    }

    public function signIn(Request $req)
    {
        $response = array();
        $responseCode = 500;
        $rules = array(
            "password" => "required",
        );
        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            $response = array('status' => 'error', 'message' => 'Invalid parameters', "errors" => $validator->errors());
            $responseCode = 400;
        } else {
            $emailAddress = '';
            if ($req->emailAddress != '') {
                $emailAddress = $req->emailAddress;
            }
            $mobileNumber = '';
            if ($req->mobileNumber != '') {
                $mobileNumber = $req->mobileNumber;
            }
            $username = '';
            if ($req->username != '') {
                $username = $req->username;
            }
            $password = $req->password;

            $query = "SELECT * FROM `users` WHERE (email = '$emailAddress' OR username = '$username' OR mobile = '$mobileNumber') AND role_id = 2";
            $results = DB::select($query);
            $total = count($results);

            if ($total > 0) {
                if (Hash::check($req->password, $results[0]->password)) {
                    // $hashed_token = password_hash($results[0]->id, PASSWORD_BCRYPT, array('cost' => 5));
                    $data = array();
                    $data['userUUID'] = $results[0]->id;
                    $data['emailAddress'] = $results[0]->email;
                    $data['fullName'] = $results[0]->first_name . ' ' . $results[0]->last_name;
                    $data['birthDate'] = $results[0]->dob;
                    $data['mobileNumber'] = $results[0]->mobile;
                    $data['username'] = $results[0]->username;
                    $data['type'] = $results[0]->user_type;
                    $data['Address'] = $results[0]->address;
                    $response = array('status' => 'success', 'message' => 'Logged in successfully', 'data' => $data);
                    $responseCode = 200;
                } else {
                    $response = array('status' => 'error', 'message' => 'Invalid Password');
                    $responseCode = 200;
                }
            } else {
                $response = array('status' => 'error', 'message' => 'Invalid Email/Username/Mobile or Password');
                $responseCode = 200;
            }
        }
        return response()->json($response, $responseCode);
    }

    public function forgotOtp(Request $req)
    {
        $response = array();
        $responseCode = 500;
        $rules = array(
            "emailAddress" => "required",
        );
        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            $response = array('status' => 'error', 'message' => 'Invalid parameters', "errors" => $validator->errors());
            $responseCode = 400;
        } else {
            $query = "SELECT * FROM `users` WHERE email='$req->emailAddress' AND is_verified = 1";
            $results = DB::select($query);
            $uCount = count($results);
            if ($uCount > 0) {
                $otp = $this->generateUniquePIN();
                try {
                    $updated = DB::table('users')
                        ->where('id', $results[0]->id)
                        ->update([
                            'otp' => $otp,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                    if ($updated) {
                        $data = [
                            'subject' => 'OTP Verification Email',
                            'email' => $req->emailAddress,
                            'content' => 'OTP Verification Code is : ' . $otp,
                        ];

                        Mail::to($data['email'])->send(new SendMail($data));
                        $response = array('status' => 'success', 'message' => 'OTP was sent to ' . $req->emailAddress . ' successfully.');
                        $responseCode = 200;
                    } else {
                        $response = array('status' => 'error', "Error on OTP verification");
                        $responseCode = 200;
                    }
                } catch (QueryException | \Exception $e) {
                    $response = array('status' => 'error', "message" => "Error on OTP verification", "errors" => $e->getMessage());
                    $responseCode = 200;
                }
            } else {
                $response = array('status' => 'error', 'message' => 'Invalid Email Address');
                $responseCode = 200;
            }
        }
        return response()->json($response, $responseCode);
    }

    public function resendForgotOtp(Request $req)
    {
        $response = array();
        $responseCode = 500;
        $rules = array(
            "emailAddress" => "required",
        );
        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            $response = array('status' => 'error', 'message' => 'Invalid parameters', "errors" => $validator->errors());
            $responseCode = 400;
        } else {
            $emailAddress = $req->emailAddress;
            $otp = $this->generateUniquePIN();

            $query = "SELECT * FROM `users` WHERE (email='$emailAddress' AND is_verified = 1)";
            $results = DB::select($query);
            $total = count($results);

            if ($total > 0) {
                try {
                    $updated = DB::table('users')
                        ->where('id', $results[0]->id)
                        ->update([
                            'otp' => $otp,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                    if ($updated) {
                        $data = [
                            'subject' => 'OTP Verification Email',
                            'email' => $req->emailAddress,
                            'content' => 'OTP Verification Code is : ' . $otp,
                        ];

                        Mail::to($data['email'])->send(new SendMail($data));
                        $response = array('status' => 'success', 'message' => 'OTP was sent to ' . $req->emailAddress . ' successfully.');
                        $responseCode = 200;
                    } else {
                        $response = array('status' => 'error', "Error on resend otp");
                        $responseCode = 200;
                    }
                } catch (QueryException | \Exception $e) {
                    $response = array('status' => 'error', "message" => "Error on resend otp", "errors" => $e->getMessage());
                    $responseCode = 200;
                }
            } else {
                $response = array('status' => 'error', 'message' => 'Invalid Email Address');
                $responseCode = 200;
            }
        }
        return response()->json($response, $responseCode);
    }

    public function validateForgotOtp(Request $req)
    {
        $response = array();
        $responseCode = 500;
        $rules = array(
            "emailAddress" => "required",
            "otp" => "required",
        );
        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            $response = array('status' => 'error', 'message' => 'Invalid parameters', "errors" => $validator->errors());
            $responseCode = 400;
        } else {
            $emailAddress = $req->emailAddress;
            $otp = $req->otp;

            $query = "SELECT * FROM `users` WHERE (email='$emailAddress' AND otp='$otp')";
            $results = DB::select($query);
            $total = count($results);

            if ($total > 0) {
                $response = array('status' => 'success', 'message' => 'valid OTP');
                $responseCode = 200;
            } else {
                $response = array('status' => 'error', 'message' => 'Invalid OTP');
                $responseCode = 200;
            }
        }
        return response()->json($response, $responseCode);
    }

    public function resetPassword(Request $req)
    {
        $response = array();
        $responseCode = 500;
        $rules = array(
            "emailAddress" => "required",
            "otp" => "required",
            "password" => "required",
        );
        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            $response = array('status' => 'error', 'message' => 'Invalid parameters', "errors" => $validator->errors());
            $responseCode = 400;
        } else {
            $emailAddress = $req->emailAddress;
            $otp = $req->otp;
            $password = $req->password;

            $query = "SELECT * FROM `users` WHERE (email='$emailAddress' AND otp='$otp')";
            $results = DB::select($query);
            $total = count($results);

            if ($total > 0) {
                $user_id = $results[0]->id;

                try {
                    $updated = DB::table('users')
                        ->where('id', $user_id)
                        ->update([
                            'otp' => '',
                            'password' => Hash::make($password),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);

                    if ($updated) {
                        $response = array('status' => 'success', 'message' => 'Password changed successfully');
                        $responseCode = 200;
                    } else {
                        $response = array('status' => 'error', "message" => "Error on reset password");
                        $responseCode = 200;
                    }
                } catch (QueryException | \Exception $e) {
                    $response = array('status' => 'error', "message" => "Error on reset password", "errors" => $e->getMessage());
                    $responseCode = 200;
                }
            } else {
                $response = array('status' => 'error', 'message' => 'Invalid Email/OTP');
                $responseCode = 200;
            }
        }
        return response()->json($response, $responseCode);
    }

    public function userProfile(Request $req)
    {
        $response = array();
        $responseCode = 500;
        $rules = array(
            "userUUID" => "required",
        );
        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            $response = array('status' => 'error', 'message' => 'Invalid parameters', "errors" => $validator->errors());
            $responseCode = 400;
        } else {
            $userUUID = $req->userUUID;

            $query = "SELECT * FROM `users` WHERE (id = '$userUUID')";
            $results = DB::select($query);
            $total = count($results);

            if ($total > 0) {
                $data = array();
                $data['userUUID'] = $results[0]->id;
                $data['emailAddress'] = $results[0]->email;
                $data['firstName'] = $results[0]->first_name;
                $data['lastName'] = $results[0]->last_name;
                $data['birthDate'] = $results[0]->dob;
                $data['mobileNumber'] = $results[0]->mobile;
                $data['username'] = $results[0]->username;
                $data['type'] = $results[0]->user_type;
                $data['Address'] = $results[0]->address;
                $response = array('status' => 'success', 'data' => $data);
                $responseCode = 200;
            } else {
                $response = array('status' => 'error', 'message' => 'Invalid Email/Username/Mobile or Password');
                $responseCode = 200;
            }
        }
        return response()->json($response, $responseCode);
    }

    public function updateProfile(Request $req)
    {
        $response = array();
        $responseCode = 500;
        $rules = array(
            "userUUID" => "required",
            "firstName" => "required",
            "lastName" => "required",
            "mobileNumber" => "required",
            "birthDate" => "required",
            "address" => "required",
        );
        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            $response = array('status' => 'error', 'message' => 'Invalid parameters', "errors" => $validator->errors());
            $responseCode = 400;
        } else {
            $userUUID = $req->userUUID;
            $firstName = $req->firstName;
            $lastName = $req->lastName;
            $mobileNumber = $req->mobileNumber;
            $birthDate = $req->birthDate;
            $address = $req->address;

            $userRes = $this->validateUserId($userUUID);
            $uCount = count($userRes);
            if ($uCount > 0) {
                try {
                    $updated = DB::table('users')
                        ->where('id', $userUUID)
                        ->update([
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'mobile' => $mobileNumber,
                            'dob' => $birthDate,
                            'address' => $address,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    if ($updated) {
                        $response = array('status' => 'success', 'message' => 'User profile details updated successfully');
                        $responseCode = 200;
                    } else {
                        $response = array('status' => 'error', "message" => "Error on updating profile details");
                        $responseCode = 200;
                    }
                } catch (QueryException | \Exception $e) {
                    $response = array('status' => 'error', "message" => "Error on updating profile details", "errors" => $e->getMessage());
                    $responseCode = 200;
                }
            } else {
                $response = array('status' => 'error', 'message' => 'Invalid User ID');
                $responseCode = 200;
            }
        }
        return response()->json($response, $responseCode);
    }

    public function syncContacts(Request $req)
    {
        $response = array();
        $responseCode = 200;

        $pageNo = 1;
        $totalRecords = 0;
        $query = "SELECT * FROM `configurations` WHERE (`key` = 'CONTACT_PAGE_NO')";
        $results = DB::select($query);
        $total = count($results);

        if ($total > 0) {
            $pageNo = intval($results[0]->value);
        }
        $posApiURL = env('POR_APIURL', '');
        $options = array(
            'http' => array(
                'method' => 'GET',
                'header' => 'Authorization: Basic QWRtaW46cGFzc3dvcmQ=',
            ),
        );
        $streamContext = stream_context_create($options);
        $posResult = file_get_contents($posApiURL . "Auth", false, $streamContext);
        $posResult = json_decode($posResult, true);
        // print_r($posResult['access_Token']);

        do {
            $UserCode = env('CRM_USERCODE', '');
            $APIToken = env('CRM_APIKEY', '');
            $EndpointURL = env('CRM_APIURL', '');
            $Function = "SearchContacts";
            $Parameters = array(
                "SearchTerms" => "",
                "NumRows" => 500,
                "Page" => $pageNo,
            );
            $PostData = array(
                'UserCode' => $UserCode,
                'APIToken' => $APIToken,
                'Function' => $Function,
                'Parameters' => json_encode($Parameters),
            );
            $Options = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($PostData),
                ),
            );
            $StreamContext = stream_context_create($Options);
            $APIResult = file_get_contents("$EndpointURL?UserCode=$UserCode", false, $StreamContext);
            $APIResult = json_decode($APIResult, true);

            if (count($APIResult['Result']) == 500) {
                $pageNo = $pageNo + 1;
                try {
                    $updated = DB::table('configurations')
                        ->where('key', 'CONTACT_PAGE_NO')
                        ->update([
                            'value' => $pageNo . "",
                        ]);
                } catch (QueryException | \Exception $e) {
                    $response = array('status' => 'error', "message" => "Error on OTP verification", "errors" => $e->getMessage());
                    $responseCode = 200;
                }
            } else {
                $pageNo = $pageNo + 1;
            }
            $totalRecords = $totalRecords + count($APIResult['Result']);
            for ($i = 0; $i < count($APIResult['Result']); $i++) {

                $query = "SELECT * FROM `contacts` WHERE contactId='" . $APIResult['Result'][$i]['ContactId'] . "'";
                $results = DB::select($query);
                $total = count($results);

                if ($total == 0) {

                    try {
                        $Insertid = DB::table('contacts')->insertGetId([
                            'contactId' => $APIResult['Result'][$i]['ContactId'],
                            'contactData' => json_encode($APIResult['Result'][$i]),
                            'status' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);

                        $response = array('status' => 'success', 'data' => $totalRecords . ' contacts processed');
                        $responseCode = 200;
                    } catch (QueryException | \Exception $e) {
                        $response = array('status' => 'error', "message" => "Error on inserting contact", "errors" => $e->getMessage());
                        $responseCode = 200;
                    }
                } else {
                    try {
                        $updated = DB::table('contacts')
                            ->where('contactId', $APIResult['Result'][$i]['ContactId'])
                            ->update([
                                'contactData' => json_encode($APIResult['Result'][$i]),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                        $response = array('status' => 'success', 'data' => $totalRecords . ' contacts processed');
                        $responseCode = 200;
                    } catch (QueryException | \Exception $e) {
                        $response = array('status' => 'error', "message" => "Error on updating contact", "errors" => $e->getMessage());
                        $responseCode = 200;
                    }
                }

                if ($APIResult['Result'][$i]['IsCompany'] == '1') {
                    if ($APIResult['Result'][$i]['Email'] != '') {

                        if (count($APIResult['Result'][$i]['Email']) > 0) {
                            if ($APIResult['Result'][$i]['Email'][0]['Text'] == 'info@testorg.com') {

                                $posApiURL = env('POR_APIURL', '');
                                $postData = array(
                                    array(
                                        'name' => $APIResult['Result'][$i]['CompanyName'],
                                        'type' => 'A',
                                        'accNo' => 'X0002',
                                        'vatNumber' => '',
                                        'email' => $APIResult['Result'][$i]['Email'][0]['Text'],
                                        "taxCode" => null,
                                        "locTaxCode" => null,
                                        "internal" => false,
                                        "companyType" => "Company",
                                        'address' => array(
                                            "name" => "DEPOT",
                                            "line1" => "Head Office",
                                            "line2" => null,
                                            "line3" => "UK",
                                            "town" => "CAVERSHAM",
                                            "county" => "BERKSHIRE",
                                            "postcode" => "RG4 5BB",
                                            "telephone" => "07783022961",
                                        ),
                                        "marketingCategory" => "Construction",
                                        "prop1" => "XYZ1",
                                        "prop6" => "XYZ61",
                                        "prop7" => "",
                                        "prop8" => "",
                                        "prop9" => "XYZ91",
                                        "prop10" => "XYZ010",
                                        "currentFlag" => true,
                                        "canUseAnyDepot" => true,
                                        "onHold" => false,
                                        "depot" => array(
                                            "name" => "VIC",
                                            "shortCode" => "VIC",
                                            "accountsDept" => "VIC",
                                        ),
                                        "exportToAccounts" => false,
                                        "creditLimit" => 1000.00,
                                        "accountBalance" => 500.00,
                                    ),
                                );

                                $postdata = json_encode($postData, true);

                                // API URL
                                $url = $posApiURL . "Customers";

                                // Create a new cURL resource
                                $ch = curl_init($url);

                                // Attach encoded JSON string to the POST fields
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

                                // Set the content type to application/json
                                $authorization = "Authorization: Bearer " . $posResult['access_Token'];
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
                                // Return response instead of outputting
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                                // Execute the POST request
                                $result = curl_exec($ch);

                                // Close cURL resource
                                curl_close($ch);
                            }
                        }
                    }

                } else {

                    if ($APIResult['Result'][$i]['Email'] != '') {

                        if (count($APIResult['Result'][$i]['Email']) > 0) {
                            if ($APIResult['Result'][$i]['Email'][0]['Text'] == 'orvinothkumar@gmail.com') {

                                $posApiURL = env('POR_APIURL', '');
                                $postData = array(
                                    array(
                                        'title' => $APIResult['Result'][$i]['Salutation'],
                                        'firstNames' => $APIResult['Result'][$i]['FirstName'],
                                        'surname' => $APIResult['Result'][$i]['LastName'],
                                        'telephone' => $APIResult['Result'][$i]['Phone'][0]['Text'],
                                        'mobile' => $APIResult['Result'][$i]['Phone'][0]['Text'],
                                        'email' => $APIResult['Result'][$i]['Email'][0]['Text'],
                                        'userLogin' => array(
                                            'userName' => $APIResult['Result'][$i]['FirstName'],
                                            'loweredUserName' => $APIResult['Result'][$i]['FirstName'],
                                            'isLockedOut' => true,
                                            'resetPassword' => true,
                                        ),
                                        'fax' => '',
                                        'prop1' => '',
                                        'prop2' => '',
                                        'mainContact' => true,
                                        'marketingContact' => true,
                                        'csjContact' => true,
                                        'canOrderOnline' => true,
                                        'dateAdded' => date('Y-m-d H:i:s'),
                                        'salutation' => $APIResult['Result'][$i]['Salutation'],
                                        'notes' => '',
                                        'onHireReport' => '',
                                        'addedBy' => 'admin',
                                        'site' => 'Head Office',
                                        'company' => 'testorg',
                                        'companyAccNo' => 'X0001',
                                        'customerId' => 1340,
                                        'supplierId' => null,
                                        'isCurrent' => true,
                                    ),
                                );

                                $postdata = json_encode($postData, true);

                                // API URL
                                $url = $posApiURL . "Contact";

                                // Create a new cURL resource
                                $ch = curl_init($url);

                                // Attach encoded JSON string to the POST fields
                                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

                                // Set the content type to application/json
                                $authorization = "Authorization: Bearer " . $posResult['access_Token'];
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
                                // Return response instead of outputting
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                                // Execute the POST request
                                $result = curl_exec($ch);

                                // Close cURL resource
                                curl_close($ch);
                            }
                        }
                    }
                }
            }
        } while (count($APIResult['Result']) > 0);
        return response()->json($response, $responseCode);
    }

    public function syncContracts(Request $req)
    {
        $response = array();
        $responseCode = 200;

        $pageNo = 1;
        $totalRecords = 0;
        $query = "SELECT * FROM `configurations` WHERE (`key` = 'CONTRACT_PAGE_NO')";
        $results = DB::select($query);
        $total = count($results);

        if ($total > 0) {
            $pageNo = intval($results[0]->value);
        }

        $posApiURL = env('POR_APIURL', '');
        $authOptions = array(
            'http' => array(
                'method' => 'GET',
                'header' => 'Authorization: Basic QWRtaW46cGFzc3dvcmQ=',
            ),
        );
        $streamContextAuth = stream_context_create($authOptions);
        $posAuthResult = file_get_contents($posApiURL . "Auth", false, $streamContextAuth);
        $posAuthResult = json_decode($posAuthResult, true);
        // print_r($posAuthResult['access_Token']);

        do {
            $contractOptions = array(
                'http' => array(
                    'method' => 'GET',
                    'header' => 'Authorization: Bearer ' . $posAuthResult['access_Token'],
                ),
            );
            $streamContextContract = stream_context_create($contractOptions);
            $posContractResult = file_get_contents($posApiURL . "HireContracts?PageNo=" . $pageNo, false, $streamContextContract);
            $posContractResult = json_decode($posContractResult, true);
            if (count($posContractResult['results']) == 200) {
                $pageNo = $pageNo + 1;
                try {
                    $updated = DB::table('configurations')
                        ->where('key', 'CONTRACT_PAGE_NO')
                        ->update([
                            'value' => $pageNo . "",
                        ]);
                } catch (QueryException | \Exception $e) {
                    $response = array('status' => 'error', "message" => "Error on OTP verification", "errors" => $e->getMessage());
                    $responseCode = 200;
                }
            } else {
                $pageNo = $pageNo + 1;
            }
            // echo "<pre>";
            // print_r($posContractResult['results']);
            for ($i = 0; $i < count($posContractResult['results']); $i++) {

                $query = "SELECT * FROM `contracts` WHERE contractId='" . $posContractResult['results'][$i]['contractNumber'] . "'";
                $results = DB::select($query);
                $total = count($results);

                if ($total == 0) {

                    try {
                        $Insertid = DB::table('contracts')->insertGetId([
                            'contractId' => $posContractResult['results'][$i]['contractNumber'],
                            'contractData' => json_encode($posContractResult['results'][$i]),
                            'status' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);

                        // if ($Insertid > 0) {
                        //     echo "contactId inserted";
                        // } else {
                        //     echo "contactId not inserted";
                        // }

                        $response = array('status' => 'success', 'data' => $posContractResult['results']);
                        $responseCode = 200;
                    } catch (QueryException | \Exception $e) {
                        $response = array('status' => 'error', "message" => "Error on inserting contract", "errors" => $e->getMessage());
                        $responseCode = 200;
                    }
                } else {
                    try {
                        $updated = DB::table('contracts')
                            ->where('contractId', $posContractResult['results'][$i]['contractNumber'])
                            ->update([
                                'contractData' => json_encode($posContractResult['results'][$i]),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                        // if ($updated) {
                        //     echo "contactId updated";
                        // } else {
                        //     echo "contactId not updated";
                        // }
                        if ($posContractResult['results'][$i]['depot']['shortCode'] == 'QLD') {
                            echo '<pre>';
                            echo $posContractResult['results'][$i]['depot']['shortCode'] . '<br/>';
                            // $UserCode = env('CRM_USERCODE', '');
                            // $APIToken = env('CRM_APIKEY', '');
                            // $EndpointURL = env('CRM_APIURL', '');
                            // $Function = "CreatePipeline";

                            // $Parameters = array(
                            //     "ContactId" => "3807894511676232218439000044690",
                            //     "Note" => "This is a test note",
                            //     "PipelineId" => "3764788017911920143572403834037",
                            //     "StatusId" => "3764788017911920143572403834037",
                            //     "Priority" => 1,
                            //     "CustomFields" => array(
                            //         "3788890953338245703431108724278" => "Contact",
                            //         "3788891001498082793868320415071" => "State",
                            //         "3788891056409428215283227850644" => "Equipment type 1",
                            //         "3788891107852785750840740255273" => "Quoted Amount",
                            //         "3788891297130215005156021626464" => "Close Date",
                            //         "3794809875214954817037563293922" => "Estimate Hire end date",
                            //         "3788891381556350944506211486863" => "Assigned to",
                            //         "3788892241748779688665526392157" => "POR Contract number (HCxxxx)",
                            //         "3788890976484297829918168728851" => "Company",
                            //         "3788891028683971872497771452690" => "Probability",
                            //         "3788891078181197908278926525591" => "Qty of Equipment 1",
                            //         "3788891251087141797176979807774" => "Lead Source",
                            //         "3788891328602666237913730117954" => "Estimated Hire start date",
                            //         "3788891408668453046840824179296" => "Equipment type 2",
                            //         "3794809706648607471479680682278" => "Barrier price per m (if applicable) c/m",
                            //         "3794809852530071292393242578615" => "Barrier Type",
                            //     ),
                            // );
                            // $PostData = array(
                            //     'UserCode' => $UserCode,
                            //     'APIToken' => $APIToken,
                            //     'Function' => $Function,
                            //     'Parameters' => json_encode($Parameters),
                            // );
                            // $Options = array(
                            //     'http' => array(
                            //         'method' => 'POST',
                            //         'header' => 'Content-type: application/x-www-form-urlencoded',
                            //         'content' => http_build_query($PostData),
                            //     ),
                            // );
                            // $StreamContext = stream_context_create($Options);
                            // $APIResult = file_get_contents("$EndpointURL?UserCode=$UserCode", false, $StreamContext);
                            // $APIResult = json_decode($APIResult, true);

                        }

                        $response = array('status' => 'success', 'data' => $posContractResult['results']);
                        $responseCode = 200;
                    } catch (QueryException | \Exception $e) {
                        $response = array('status' => 'error', "message" => "Error on updating contract", "errors" => $e->getMessage());
                        $responseCode = 200;
                    }
                }
            }
        } while (count($posContractResult['results']) > 0);
        return response()->json($response, $responseCode);
    }

}
