<?php

if (!function_exists('isProfileIncomplete')) {
    function isProfileIncomplete($user)
    {
        $customer = $user->customer;

        if (!$customer) {
            return false;
        }else{
            return empty($customer->date_of_birth) || empty($customer->gender) || empty($customer->phone_number) || empty($customer->address) ||
                empty($customer->occupation) || empty($customer->employer) || empty($customer->annual_income) || empty($customer->marital_status);
        }
}
}
