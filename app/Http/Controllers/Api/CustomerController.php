<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\DynamicMail;
use App\Models\Cart;
use App\Models\CartAddress;
use App\Models\Category\MainCategory;
use App\Models\GlobalSettings;
use App\Models\Master\Customer;
use App\Models\Master\CustomerAddress;
use App\Models\Master\CustomerOtp;
use App\Models\Newsletter;
use App\Models\Master\EmailTemplate;
use Illuminate\Support\Facades\File;
use App\Models\Master\State;
use App\Services\ShipRocketService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Mail;

class CustomerController extends Controller
{

    public function verifyAccount(Request $request)
    {

        $email = $request->token;

        $email = base64_decode($email);
        $error = 1;
        $message = 'Token Expired';

        $customer = Customer::with('customerAddress')->where('email', $email)->whereNull('deleted_at')->first();
        if ($customer) {
            if (!empty($customer->verification_token)) {
                $customer->email_verified_at = Carbon::now();
                $customer->verification_token = null;
                $customer->update();
                $error = 0;
                $message = 'Your Account has been successfully created';
            }
        }
        return new Response(array('error' => $error, 'status_code' => 200, 'message' => $message, 'status' => 'success', 'data' => $customer), 200);
    }

    public function registerCustomer(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'firstName' => 'required|string',
                'email' => ['required', 'email:filter', 'unique:customers,email,NULL,id,deleted_at,NULL'],
                'mobile_no' => ['required', 'numeric', 'unique:customers,mobile_no,NULL,id,deleted_at,NULL'],
                'password' => 'required|string',

            ],
            ['email.unique' => 'Email id is already registered.Please try to login']
        );

        if ($validator->fails()) {
            return new Response(array('error' => 1, 'status_code' => 422, 'message' => $validator->errors(), 'status' => 'failed', 'data' => []), 422);
        }

        $customer = Customer::where('email', $request->email)->whereNull('deleted_at')->first();
        if (!$customer) {
            $ins['first_name'] = $request->firstName;
            $ins['last_name'] = $request->lastName;
            $ins['email'] = $request->email;
            $ins['mobile_no'] = $request->mobile_no ?? null;
            $ins['customer_no'] = getCustomerNo();
            $ins['password'] = Hash::make($request->password);
            $ins['status'] = 'published';
            $ins['address'] = $request->pincode;

            $customer_data = Customer::create($ins);

            $token_id = base64_encode($request->email);

            /** send email for new customer */
            $emailTemplate = EmailTemplate::select('email_templates.*')
                ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
                ->where('sub_categories.slug', 'new-registration')->first();

            $globalInfo = GlobalSettings::first();

            // $link = 'http://192.168.0.35:3000/#/verify-account/' . $token_id;
            $link = env('FRONTEND_URL') . 'verify-account/' . $token_id;

            $customer_data->verification_token = $token_id;
            $customer_data->update();

            $extract = array(
                'name' => $request->firstName . ' ' . $request->lastName,
                'regards' => $globalInfo->site_name,
                'link' => '<a href="' . $link . '"> Verify Account </a>',
                'company_website' => $globalInfo->site_name,
                'company_mobile_no' => $globalInfo->site_mobile_no,
                'company_address' => $globalInfo->address,
                'user_name' => $request->email,
                'password' => $request->password
            );

            $templateMessage = $emailTemplate->message;
            $templateMessage = str_replace("{", "", addslashes($templateMessage));
            $templateMessage = str_replace("}", "", $templateMessage);
            extract($extract);
            eval("\$templateMessage = \"$templateMessage\";");

            $send_mail = new DynamicMail($templateMessage, $emailTemplate->title);
            // return $send_mail->render();
            sendEmailWithBcc($request->email, $send_mail);
            /** send sms for new customer */
            if ($request->mobile_no) {

                $sms_params = array(
                    'name' => $request->firstName . '' . $request->lastName,
                    'reference_id' => $ins['customer_no'],
                    'company_name' => env('APP_NAME'),
                    'login_details' => $ins['email'] . '/' . $request->password,
                    'mobile_no' => [$request->mobile_no]
                );

                sendMuseeSms('register', $sms_params);
            }
            $error = 0;
            $message = 'Congratulations! Your account has been successfully created. To confirm your account, please take a moment to check your email and complete the verification process.';
            $status = 'success';
            $status_code = '201';
        } else {
            $error = 1;
            $status_code = '400';
            $message = ['Email Id Already Exists'];
            $status = 'error';
        }


        return new Response(array('error' => $error, 'status_code' => $status_code, 'message' => $message, 'status' => $status, 'data' => $customer_data ?? ''), $status_code);
    }

    public function createNewToken($token)
    {
        return array(
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth()->guard('api')->factory()->getTTL() * 1
        );
    }

    public function doLogin(Request $request)
    {
        if (is_numeric($request->email)) {
            $field = 'mobile_no'; //return ['phone'=>$request->get('email'),'password'=>$request->get('password')];
        } elseif (filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {
            $field = 'email'; //return ['email' => $request->get('email'), 'password'=>$request->get('password')];
        }
        $email = $request->email;
        $password = $request->password;
        $guest_token = $request->guest_token;
        $data = [];

        if ($field == 'mobile_no') {
            $validator = Validator::make($request->all(), [
                'email' => 'required|numeric',
                'password' => 'required|string',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
        }


        if ($validator->fails()) {
            return new Response(array('error' => 1, 'status_code' => 422, 'message' => $validator->errors(), 'status' => 'failed', 'data' => []), 422);
        }

        if (!$token = auth()->guard('api')->attempt([$field => $email, 'password' => $password])) {
            return new Response(array('error' => 1, 'status_code' => 401, 'message' => 'Incorrect username or password', 'status' => 'failed', 'data' => $token), 401);
        } else {
            $checkCustomer = Customer::with(['customerAddress', 'customerAddress.subCategory'])->where($field, $email)->first();

            if ($checkCustomer) {
                if (Hash::check($password, $checkCustomer->password)) {
                    if ($checkCustomer->email_verified_at == null) {
                        return new Response(array('error' => 1, 'status_code' => 400, 'message' => 'Verification pending check your mail', 'status' => 'failed', 'data' => []), 400);
                    } else {
                        $customer_address = $checkCustomer->customerAddress ?? [];
                        $data = ['customer_data' => $checkCustomer, 'customer_address' => $customer_address, 'authorization' => $this->createNewToken($token)];

                        if ($guest_token) {
                            $cartData = Cart::where('token', $guest_token)->get();
                            if (isset($cartData) && count($cartData) > 0) {
                                Cart::where('token', $guest_token)->update(['token' => null, 'customer_id' => $checkCustomer->id]);
                            }
                            $cartItems = Cart::where('customer_id', $checkCustomer->id)->get();

                            if ($cartItems->count() > 0) {
                                // Aggregate quantities for duplicate products
                                $aggregatedCart = [];
                                foreach ($cartItems as $item) {
                                    $product_id = $item->product_id;
                                    if (isset($aggregatedCart[$product_id])) {
                                        $aggregatedCart[$product_id]['quantity'] += $item->quantity;
                                        $aggregatedCart[$product_id]['ids'][] = $item->id;
                                    } else {
                                        $aggregatedCart[$product_id] = [
                                            'quantity' => $item->quantity,
                                            'ids' => [$item->id],
                                            'first_id' => $item->id,
                                            'other_ids' => [],
                                        ];
                                    }
                                }

                                // Update quantities for the first occurrence and collect IDs for duplicates
                                foreach ($aggregatedCart as $product_id => $data) {
                                    // Update the quantity of the first occurrence
                                    Cart::where('id', $data['first_id'])->update(['quantity' => $data['quantity']]);

                                    // Collect other IDs for deletion
                                    $data['other_ids'] = array_slice($data['ids'], 1);

                                    if (!empty($data['other_ids'])) {
                                        // Remove duplicates
                                        Cart::whereIn('id', $data['other_ids'])->delete();
                                    }
                                }
                            }
                        }
                        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Login Success', 'status' => 'success', 'data' => $data), 200);
                    }
                } else {
                    return new Response(array('error' => 1, 'status_code' => 400, 'message' => 'Invalid credentials', 'status' => 'failed', 'data' => []), 400);
                }
            } else {
                return new Response(array('error' => 1, 'status_code' => 400, 'message' => 'Invalid credentials', 'status' => 'failed', 'data' => []), 400);
            }
        }
    }

    public function addCustomerAddress(Request $request, ShipRocketService $service)
    {

        if ($request->state) {
            $state_info = State::find($request->state);
            $ins['state'] = $state_info->state_name;
            $ins['stateid'] = $state_info->id;
        }
        // dd( $request->all() );
        // $details = $service->getShippingRocketOrderDimensions($request->customer_id);
        // echo 'duraira';die;
        $from_address_type = $request->from_address_type;
        $cart_info = Cart::where('customer_id', $request->customer_id)->first();
        if ($request->is_default == 1) {
            CustomerAddress::where('customer_id', $request->customer_id)->update(['is_default' => 0]);
        }
        $ins['customer_id'] = $request->customer_id;
        $ins['address_type_id'] = $request->address_type;
        $ins['first_name'] = $request->first_name;
        $ins['last_name'] = $request->last_name;
        $ins['email'] = $request->email;
        $ins['mobile_no'] = $request->mobile_no;
        $ins['address_line1'] = $request->address_line1;
        $ins['address_line2'] = $request->address_line2;
        $ins['is_default'] = $request->is_default;
        $ins['country'] = 'india';
        $ins['post_code'] = $request->post_code;
        $ins['city'] = $request->city;

        $address_info = CustomerAddress::create($ins);

        $address = CustomerAddress::where('customer_id', $request->customer_id)->get();

        if (isset($cart_info) && !empty($cart_info)) {
            CartAddress::where('customer_id', $request->customer_id)
                ->where('address_type', $from_address_type)->delete();
            $ins_cart = [];
            $ins_cart['cart_token'] = $cart_info->guest_token;
            $ins_cart['customer_id'] = $request->customer_id;
            $ins_cart['address_type'] = $from_address_type;
            $ins_cart['name'] = $request->first_name . ' ' . $request->last_name;
            $ins_cart['first_name'] = $request->first_name;
            $ins_cart['last_name'] = $request->last_name;
            $ins_cart['email'] = $request->email;
            $ins_cart['mobile_no'] = $request->mobile_no;
            $ins_cart['address_line1'] = $request->address_line1;
            $ins_cart['address_line2'] = $request->address_line2;
            $ins_cart['country'] = 'india';
            $ins_cart['post_code'] = $request->post_code;
            $ins_cart['state'] = $ins['state'];
            $ins_cart['city'] = $request->city;

            CartAddress::create($ins_cart);
        }
        $shipRocketDetails = [];
        // if ($from_address_type == 'shipping') {
        //     // $details = $service->getShippingRocketOrderDimensions($request->customer_id);
        // }
        $data = array('shipRocketDetails' => $shipRocketDetails, 'address_info' => $address_info);
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Address added successfully', 'status' => 'success', 'data' => $data), 200);
    }


    public function updateProfile(Request $request)
    {
        $customer_id = $request->customer_id;
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $email = $request->email;
        $mobile_no = $request->mobile_no;

        $customerInfo = Customer::find($customer_id);
        $customerInfo->first_name = $first_name;
        $customerInfo->last_name = $last_name;
        $customerInfo->email = $email;
        $customerInfo->mobile_no = $mobile_no;
        $customerInfo->update();
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Profile updated successfully', 'status' => 'success', 'data' => $customerInfo), 200);
    }
    public function updateProfileImage(Request $request)
    {
        $customerId = $request->customer_id;
        $customerInfo = Customer::find($customerId);
        $request->profile_image;
        if ($request->hasFile('profile_image')) {
            $filename       = time() . '_' . $request->profile_image->getClientOriginalName();
            $directory      = 'customer/' . $customerId;
            $filename       = $directory . '/' . $filename;
            Storage::deleteDirectory('public/' . $directory);
            Storage::disk('public')->put($filename, File::get($request->profile_image));

            $customerInfo->profile_image = $filename;
            $customerInfo->save();
        }
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Profile image updated successfully', 'status' => 'success', 'data' => $customerInfo), 200);
    }

    public function changePassword(Request $request)
    {

        $customer_id = $request->customer_id;
        $current_password = $request->currentPassword;
        $newPassword = $request->password;

        $customerInfo = Customer::find($customer_id);
        if ($current_password == $newPassword) {
            $error = 1;
            $status_code = 400;
            $status = "failed";
            $message = 'New password cannot be same as current password';
        } else if (isset($customerInfo) && !empty($customerInfo)) {

            if (Hash::check($current_password, $customerInfo->password)) {
                $error = 0;
                $status_code = 200;
                $status = "success";
                $customerInfo->password = Hash::make($newPassword);
                $customerInfo->update();

                $message = 'Password changed successfully';
            } else {
                $error = 1;
                $message = 'Current password is not match';
                $status_code = 400;
                $status = "failed";
            }
        }
        return new Response(array('error' => $error, 'status_code' => $status_code, 'message' => $message, 'status' => $status, 'data' => []), 200);

        return array('error' => $error, 'message' => $message);
    }

    public function deleteCustomerAddress(Request $request)
    {
        $default_address_deleted = false;
        $address_id = $request->address_id;
        $addressInfo = CustomerAddress::find($address_id);
        if ($addressInfo->is_default == 1) {
            $default_address_deleted = true;
        }
        $addressInfo->delete();
        $address = CustomerAddress::where('customer_id', $request->customer_id)->get();
        if ($default_address_deleted) {
            $get_customer = CustomerAddress::where('customer_id', $request->customer_id)->first();
            $get_customer->is_default = 1;
            $get_customer->save();
        }
        return array('error' => 0, 'message' => 'Address deleted successfully', 'status' => 'success', 'customer_address' => $address);
    }

    public function updateCustomerAddress(Request $request)
    {
        $address_id = $request->address_id;
        if ($request->stateid) {
            $state_info = State::find($request->stateid);
            $ins['state'] = $state_info->state_name;
            $ins['stateid'] = $state_info->id;
        }
        if ($request->is_default == 1) {
            CustomerAddress::where('customer_id', $request->customer_id)->update(['is_default' => 0]);
        }
        $ins['customer_id'] = $request->customer_id;
        $ins['address_type_id'] = $request->address_type_id;
        $ins['first_name'] = $request->first_name;
        $ins['last_name'] = $request->last_name;
        $ins['email'] = $request->email;
        $ins['mobile_no'] = $request->mobile_no;
        $ins['address_line1'] = $request->address_line1;
        $ins['address_line2'] = $request->address_line2;
        $ins['is_default'] = $request->is_default;
        $ins['country'] = 'india';
        $ins['post_code'] = $request->post_code;
        $ins['is_default'] = $request->is_default;

        $ins['city'] = $request->city;

        CustomerAddress::updateOrCreate(['id' => $address_id], $ins);

        $address = CustomerAddress::where('customer_id', $request->customer_id)->get();
        return array('error' => 0, 'message' => 'Address updated successfully', 'status' => 'success', 'customer_address' => $address);
    }

    public function getCustomerAddress(Request $request)
    {
        $address_id = $request->address_id;
        $res = [];
        if (isset($address_id) && !empty($address_id)) {
            $addressInfo = CustomerAddress::find($address_id);
            $res['address_id'] = $addressInfo->id;
            $res['address_line'] = $addressInfo->address_line1 ?? '';
            $res['address_type_id'] = (string)$addressInfo->address_type_id;
            $res['address_type_name'] = $addressInfo->subCategory->name ?? '';
            $res['city'] = $addressInfo->city ?? '';
            $res['customer_id'] = $addressInfo->customer_id;
            $res['is_default'] = $addressInfo->is_default;
            $res['email'] = $addressInfo->email;
            $res['mobile_no'] = $addressInfo->mobile_no;
            $res['name'] = $addressInfo->name;
            $res['post_code'] = $addressInfo->post_code ?? '';
            $res['state'] = $addressInfo->state ?? '';
            $res['stateid'] = $addressInfo->stateid ?? '';
        }

        $address_type       = MainCategory::where('slug', 'address-type')->first();
        $res['address_type'] = $address_type->subCategory ?? [];

        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Data loaded successfully', 'status' => 'success', 'data' => $res), 200);
    }

    public function sendPasswordLink(Request $request)
    {
        $email = $request->email;
        $token_id = base64_encode($email);

        $customer_info = Customer::where('email', $email)->first();

        if (isset($customer_info) && !empty($customer_info)) {
            $error = 0;
            $message = '';
            $customer_info->forgot_token = $token_id;
            $customer_info->update();
            /** send email for new customer */
            $emailTemplate = EmailTemplate::select('email_templates.*')
                ->join('sub_categories', 'sub_categories.id', '=', 'email_templates.type_id')
                ->where('sub_categories.slug', 'forgot-password')->first();

            $globalInfo = GlobalSettings::first();
            // $link = 'http://192.168.0.35:3000/#/reset-password/' . $token_id;
            $link = env('FRONTEND_URL') . 'reset-password?token=' . $token_id;
            $extract = array(
                'name' => $customer_info->firstName . ' ' . $customer_info->last_name,
                'link' => '<a href="' . $link . '"> Reset Password </a>',
                'regards' => $globalInfo->site_name,
                'company_website' => $globalInfo->site_name,
                'company_mobile_no' => $globalInfo->site_mobile_no,
                'company_address' => $globalInfo->address
            );

            $templateMessage = $emailTemplate->message;
            $templateMessage = str_replace("{", "", addslashes($templateMessage));
            $templateMessage = str_replace("}", "", $templateMessage);
            extract($extract);
            eval("\$templateMessage = \"$templateMessage\";");

            $send_mail = new DynamicMail($templateMessage, $emailTemplate->title);
            // return $send_mail->render();
            sendEmailWithBcc($request->email, $send_mail);
            $message = 'Reset password link sent successfully';
        } else {
            $message = 'Email id not exists';
            return new Response(array('error' => 1, 'status_code' => 400, 'message' => $message, 'status' => 'failed', 'data' => []), 400);
        }
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => $message, 'status' => 'success', 'data' => []), 200);
    }

    public function resetPasswordLink(Request $request)
    {
        $customer_id = $request->customer_id;
        $password = $request->password;

        $customerInfo = Customer::find($customer_id);

        if (isset($customerInfo) && !empty($customerInfo)) {

            $customerInfo->password = Hash::make($password);
            $customerInfo->forgot_token = null;
            $customerInfo->update();

            $message = 'Password reset successful';
        } else {

            $message = 'Customer not found, Please try register';
            return new Response(array('error' => 1, 'status_code' => 400, 'message' => $message, 'status' => 'failed', 'data' => []), 400);
        }
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => $message, 'status' => 'success', 'data' => []), 200);
    }

    public function checkValidToken(Request $request)
    {
        $token_id = $request->token_id;
        $customerInfo = Customer::select('id', 'first_name', 'last_name')->where('forgot_token', $token_id)->first();

        if (isset($customerInfo) && !empty($customerInfo)) {
            $error = 0;
            $message = 'Token is valid';
            $status = "success";
            $status_code = 200;
        } else {
            $error = 1;
            $message = 'Token is invalid';
            $status = "failure";
            $status_code = 400;
        }
        return new Response(array('error' => $error, 'status_code' => $status_code, 'message' => $message, 'status' => $status, 'data' => $customerInfo), $status_code);

        return array('error' => $error, 'message' => $message, 'data' => $customerInfo);
    }

    public function generate(Request $request)
    {
        /* Validate Data */
        $validator = $validator = Validator::make(
            $request->all(),
            [
                'mobile_no' => 'required|exists:customers,mobile_no'
            ]
        );
        if ($validator->fails()) {
            return new Response(array('error' => 1, 'status_code' => 422, 'message' => $validator->errors(), 'status' => 'failed', 'data' => []), 422);
        }
        /* Generate An OTP */
        $userOtp = $this->generateOtp($request->mobile_no);

        $sms_params = array(
            'customer_otp' => $userOtp->otp,
            'mobile_no' => [$request->mobile_no]
        );

        sendMuseeSms('login', $sms_params);
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => "OTP has been sent on Your Mobile Number.", 'status' => 'success', 'data' => []), 200);
    }

    public function generateOtp($mobile_no)
    {
        $customer = Customer::where('mobile_no', $mobile_no)->first();

        /* User Does not Have Any Esting OTP */
        $customerOtp = CustomerOtp::where('customer_id', $customer->id)->latest()->first();

        $now = now();

        if ($customerOtp && $now->isBefore($customerOtp->expire_at)) {
            return $customerOtp;
        }
        /* Create a New OTP */
        return CustomerOtp::create([
            'mobile_no' => $mobile_no,
            'customer_id' => $customer->id,
            'otp' => rand(123456, 999999),
            'expire_at' => $now->addMinutes(10)
        ]);
    }

    public function loginWithOtp(Request $request)
    {
        $guest_token = $request->guest_token;
        $validator = $validator = Validator::make(
            $request->all(),
            [
                'mobile_no' => 'required|exists:customers,mobile_no',
                'otp' => 'required'
            ]
        );
        if ($validator->fails()) {
            return new Response(array('error' => 1, 'status_code' => 422, 'message' => $validator->errors(), 'status' => 'failed', 'data' => []), 422);
        }
        /* Validation Logic */
        $userOtp   = CustomerOtp::where('mobile_no', $request->mobile_no)->where('otp', $request->otp)->first();

        $now = now();
        if (!$userOtp) {

            return new Response(array('error' => 1, 'status_code' => 400, 'message' => "Your OTP is not correct.", 'status' => 'failure', 'data' => []), 400);
        } else if ($userOtp && $now->isAfter($userOtp->expire_at)) {

            return new Response(array('error' => 1, 'status_code' => 400, 'message' => "Your OTP has been expired.", 'status' => 'failure', 'data' => []), 400);
        }

        $user = Customer::whereId($userOtp->customer_id)->first();

        if ($user) {

            $userOtp->update([
                'expire_at' => now()
            ]);

            if (!$token = auth()->guard('api')->fromUser($user)) {
                return new Response(array('error' => 1, 'status_code' => 401, 'message' => 'Unauthorised', 'status' => 'failed', 'data' => []), 401);
            } else {
                $checkCustomer = Customer::with(['customerAddress', 'customerAddress.subCategory'])->where('email', $user->email)->first();

                if ($checkCustomer) {
                    $customer_address = $checkCustomer->customerAddress ?? [];
                    $data = ['customer_data' => $checkCustomer, 'customer_address' => $customer_address, 'authorization' => $this->createNewToken($token)];
                    if ($guest_token) {
                        $cartData = Cart::where('token', $guest_token)->get();
                        if (isset($cartData) && count($cartData) > 0) {
                            Cart::where('token', $guest_token)->update(['token' => null, 'customer_id' => $checkCustomer->id]);
                        }
                    }
                    return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Login Success', 'status' => 'success', 'data' => $data), 200);
                }
            }
        }
        return new Response(array('error' => 1, 'status_code' => 400, 'message' => 'Login Success', 'status' => 'success', 'data' => []), 400);
    }

    public function googleCallback(Request $request)
    {
        // $user = Socialite::driver('google')->userFromToken($request->token);   //watch out with changes due to stateless user
        // var_dump($user);
        $customer_data = Customer::where('email', $request->email)->first();
        $guest_token = $request->guest_token;
        if ($customer_data) {
            if (!$token = auth()->guard('api')->fromUser($customer_data)) {
                return new Response(array('error' => 1, 'status_code' => 401, 'message' => 'Unauthorised', 'status' => 'failed', 'data' => $customer_data), 401);
            } else {
                $checkCustomer = Customer::with(['customerAddress', 'customerAddress.subCategory'])->where('email', $request->email)->first();

                if ($checkCustomer) {
                    $customer_address = $checkCustomer->customerAddress ?? [];
                    $data = ['customer_data' => $checkCustomer, 'customer_address' => $customer_address, 'authorization' => $this->createNewToken($token)];
                    if ($guest_token) {
                        $cartData = Cart::where('token', $guest_token)->get();
                        if (isset($cartData) && count($cartData) > 0) {
                            Cart::where('token', $guest_token)->update(['token' => null, 'customer_id' => $checkCustomer->id]);
                        }
                    }
                    return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Login Success', 'status' => 'success', 'data' => $data), 200);
                }
            }
        } else {
            $customer_data = Customer::create([
                'first_name' => $request->name,
                'email' => $request->email,
                'mobile_no' => '',
                'google_id' => $request->sub,
                'customer_no' => getCustomerNo(),
                'password' => Hash::make(''),
                'profile_image' => $request->picture,
                'status' => 'published'
            ]);
            if (!$token = auth()->guard('api')->fromUser($customer_data)) {
                return new Response(array('error' => 1, 'status_code' => 401, 'message' => 'Unauthorised', 'status' => 'failed', 'data' => $customer_data), 401);
            } else {
                $checkCustomer = Customer::with(['customerAddress', 'customerAddress.subCategory'])->where('email', $request->email)->first();

                if ($checkCustomer) {
                    $customer_address = $checkCustomer->customerAddress ?? [];
                    $data = ['customer_data' => $checkCustomer, 'customer_address' => $customer_address, 'authorization' => $this->createNewToken($token)];

                    return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Login Success', 'status' => 'success', 'data' => $data), 200);
                }
            }
        }
    }

    public function getGoogleRedirectUrl()
    {
        $data['url'] = Socialite::with('google')->stateless()->redirect()->getTargetUrl();
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Success', 'status' => 'success', 'data' => $data), 200);
    }

    public function subscribeNewsleter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email:filter', 'unique:newsletters,email'],

        ]);
        if ($validator->fails()) {
            return new Response(array('error' => 1, 'status_code' => 400, 'message' => $validator->errors(), 'status' => 'failure', 'data' => []), 400);
        } else {
            Newsletter::create(['email' => $request->email]);
            // Register the new user or whatever.
            return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Email added to subscription list', 'status' => 'success', 'data' => []), 200);
        }
    }

    public function loginByUser($user)
    {
        if (!$token = auth()->guard('api')->fromUser($user)) {
            return new Response(array('error' => 1, 'status_code' => 401, 'message' => 'Unauthorised', 'status' => 'failed', 'data' => []), 401);
        } else {
            $checkCustomer = Customer::with(['customerAddress', 'customerAddress.subCategory'])->where('email', $user->email)->first();

            if ($checkCustomer) {
                $customer_address = $checkCustomer->customerAddress ?? [];
                $data = ['customer_data' => $checkCustomer, 'customer_address' => $customer_address, 'authorization' => $this->createNewToken($token)];

                return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Login Success', 'status' => 'success', 'data' => $data), 200);
            }
        }
    }

    public function getCustomerData(Request $request)
    {
        if (Auth()->guard('api')->check()) {
            $customer_id = auth()->guard('api')->user()->id;
            $user = Customer::whereId($customer_id)->first();
            $checkCustomer = Customer::with(['customerAddress', 'customerAddress.subCategory'])->where('email', $user->email)->first();

            if ($checkCustomer) {
                $customer_address = $checkCustomer->customerAddress ?? [];
                $data = ['customer_data' => $checkCustomer, 'customer_address' => $customer_address];

                return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Customer data loaded successfully', 'status' => 'success', 'data' => $data), 200);
            } else {
                return new Response(array('error' => 1, 'status_code' => 400, 'message' => 'Customer not found', 'status' => 'failure', 'data' => []), 400);
            }
        } else {
            return new Response(array('error' => 1, 'status_code' => 400, 'message' => 'Customer not found', 'status' => 'failure', 'data' => []), 400);
        }
    }

    public function setDefaultAddress(Request $request)
    {
        $customer_id = $request->customer_id;
        $address_id = $request->address_id;
        $is_default = $request->is_default;
        $address_not_default = CustomerAddress::where('customer_id', $customer_id)->update(['is_default' => 0]);
        $address_set_default = CustomerAddress::find($address_id)->update(['is_default' => $is_default]);
        return new Response(array('error' => 0, 'status_code' => 200, 'message' => 'Success! The selected Address has been set as your default address', 'status' => 'success', 'data' => []), 200);
    }
}
