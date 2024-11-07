<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email</title>
    <style>
        html, body {
            padding: 0;
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.5;
            font-weight: normal;
            font-size: 15px;
            color: #2F3044;
            background-color: #edf2f7;
            min-height: 100%;
            width: 100%;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 40px;
            background-color: #f7f7f7;
        }
        .header img {
            width: 200px;
            height: auto;
        }
        .content {
            padding: 40px;
        }
        .content h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .content p {
            margin-bottom: 30px;
            font-size: 16px;
        }
        .button {
            display: inline-block;
            padding: 12px 20px;
            font-size: 16px;
            color: #ffffff;
            background-color: #009ef7;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #007bb5;
        }
        .footer {
            text-align: center;
            font-size: 13px;
            color: #6d6e7c;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="#" rel="noopener" target="_blank">
                <img alt="Logo" src="{{ asset('logo1.png') }}" />
            </a>
        </div>
        <div class="content">
            <h1>Welcome aboard, {{ $user->name }}!</h1>
            <p>To activate your account, please click on the button below to verify your email address. Once activated, you’ll have full access to our free and premium products.</p>
            <div style="text-align:center; margin-bottom: 40px;">
                <a href="{{ $link }}" rel="noopener" class="button" target="_blank">Activate Account</a>
            </div>
            <p>This password reset link will expire in 60 minutes. If you did not request a password reset, no further action is required.</p>
            <p>If the button does not work, copy and paste this URL into your browser:</p>
            <p>
                <a href="{{ $link }}" rel="noopener" target="_blank" style="color: #009ef7; text-decoration: none;">{{ $link }}</a>
            </p>
            <p>Kind regards,<br>The SofTech MicroFinance Team.</p>
        </div>
        <div class="footer">
            <p>Floor 5, 450 Avenue of the Red Field, SF, 10050, Ntinda.</p>
            <p>&copy; {{ date('Y') }}
                <a href="https://softechmicrofinance.com" rel="noopener" target="_blank">SofTech Microfinance</a>. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
