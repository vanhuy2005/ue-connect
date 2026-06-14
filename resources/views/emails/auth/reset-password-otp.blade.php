<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mã xác nhận đặt lại mật khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f5;
            margin: 0;
            padding: 0;
            color: #3f3f46;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #2563eb;
            padding: 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 32px 24px;
        }
        .content p {
            margin-top: 0;
            margin-bottom: 16px;
            line-height: 1.6;
            font-size: 16px;
        }
        .otp-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            margin: 32px 0;
        }
        .otp-code {
            font-family: monospace;
            font-size: 36px;
            font-weight: 700;
            letter-spacing: 8px;
            color: #1e40af;
            margin: 0;
        }
        .footer {
            background-color: #f8fafc;
            padding: 24px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>UEConnect</h1>
        </div>
        <div class="content">
            <p>Chào bạn,</p>
            <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản UEConnect của bạn. Vui lòng sử dụng mã xác nhận (OTP) gồm 6 chữ số dưới đây để tiếp tục:</p>
            
            <div class="otp-box">
                <p class="otp-code">{{ $otp }}</p>
            </div>
            
            <p>Mã xác nhận này sẽ hết hạn trong vòng <strong>15 phút</strong>. Tuyệt đối không chia sẻ mã này cho bất kỳ ai, kể cả nhân viên quản trị.</p>
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, xin hãy bỏ qua email này hoặc liên hệ bộ phận hỗ trợ nếu cảm thấy tài khoản của mình bị đe doạ.</p>
            <br>
            <p>Trân trọng,<br>Ban quản trị UEConnect.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} UEConnect. All rights reserved.
        </div>
    </div>
</body>
</html>
