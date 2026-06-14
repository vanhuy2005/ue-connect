@extends('legal.layout')

@section('title', 'Tiêu chuẩn cộng đồng')
@section('subtitle', 'Cập nhật lần cuối: ' . date('d/m/Y'))

@section('content')
<p>Để duy trì <strong>UEConnect</strong> như một không gian trực tuyến tích cực, an toàn và chuyên nghiệp dành cho sinh viên HCMUE, chúng tôi thiết lập các Tiêu chuẩn cộng đồng này. Mọi thành viên tham gia nền tảng (bao gồm sinh viên, cựu sinh viên, và giảng viên) đều phải tuân thủ.</p>

<h2>1. Tính xác thực và Minh bạch</h2>
<p>UEConnect là cộng đồng dựa trên danh tính thật. Chúng tôi yêu cầu sự trung thực để xây dựng lòng tin giữa các thành viên.</p>
<ul>
    <li><strong>Cấm mạo danh:</strong> Bạn không được phép sử dụng tên, hình ảnh hoặc thông tin định danh của người khác (đặc biệt là mạo danh giảng viên hoặc Ban giám hiệu nhà trường).</li>
    <li><strong>Chỉ sử dụng một tài khoản:</strong> Mỗi người chỉ được phép sở hữu một tài khoản định danh duy nhất. Không được phép tạo nhiều tài khoản hoặc sử dụng tài khoản ảo (clone).</li>
    <li><strong>Thông tin hồ sơ:</strong> Khuyến khích sử dụng ảnh đại diện thật và thông tin giới thiệu chính xác để tạo điều kiện thuận lợi cho việc kết nối và tìm kiếm Mentor.</li>
</ul>

<h2>2. Môi trường an toàn và Tôn trọng (Không bạo lực mạng)</h2>
<p>Với tư cách là sinh viên trường Sư phạm, chúng ta đề cao văn hóa ứng xử văn minh và tôn trọng sự khác biệt.</p>
<ul>
    <li><strong>Cấm ngôn từ thù ghét:</strong> Không được phép có những bài viết, bình luận mang tính xúc phạm, kỳ thị tôn giáo, giới tính, vùng miền, hoặc khiếm khuyết cơ thể.</li>
    <li><strong>Cấm bắt nạt và quấy rối (Cyberbullying):</strong> Không nhắm mục tiêu vào các cá nhân để chế nhạo, bêu rếu hoặc phát tán thông tin riêng tư của họ mà không có sự cho phép (doxing).</li>
    <li><strong>Tôn trọng trong tranh luận:</strong> Mọi cuộc thảo luận học thuật hay xã hội đều phải diễn ra trên tinh thần xây dựng. Các hành vi công kích cá nhân (ad hominem) sẽ bị gỡ bỏ bài viết.</li>
</ul>

<h2>3. Nội dung bị cấm và Giới hạn</h2>
<p>Để không gian chung luôn mang lại giá trị, chúng tôi không cho phép các loại nội dung sau:</p>
<ul>
    <li><strong>Tài liệu thi cử trái phép:</strong> Nghiêm cấm chia sẻ đề thi đang trong thời gian bảo mật, đáp án nội bộ hoặc các hành vi tổ chức gian lận thi cử, học hộ, thi thuê.</li>
    <li><strong>Thương mại hóa và Spam:</strong> Không đăng tải liên tục các bài quảng cáo bán hàng, đa cấp, hoặc các dịch vụ không liên quan đến sinh viên. Nội dung spam sẽ bị hệ thống tự động khóa.</li>
    <li><strong>Nội dung 18+ và Bạo lực:</strong> Cấm chia sẻ hình ảnh, video có tính chất khiêu dâm, bạo lực máu me hoặc cổ xúy các hành vi vi phạm pháp luật Việt Nam.</li>
    <li><strong>Tin giả (Fake news):</strong> Không lan truyền thông tin sai lệch gây hoang mang dư luận, đặc biệt là các thông tin liên quan đến quy chế đào tạo, học phí của Nhà trường khi chưa có thông báo chính thức.</li>
</ul>

<h2>4. Quy định đối với Mentor và Mentee</h2>
<p>Hệ thống Cố vấn là tính năng đặc quyền của UEConnect, yêu cầu sự nghiêm túc từ cả hai phía:</p>
<ul>
    <li><strong>Trách nhiệm:</strong> Mentor cần đưa ra những lời khuyên trung thực, không hứa hẹn những kết quả không có thật (ví dụ: "đảm bảo qua môn", "đảm bảo có việc làm").</li>
    <li><strong>Ranh giới chuyên môn:</strong> Mối quan hệ Mentor-Mentee nên giới hạn trong khuôn khổ học thuật và chia sẻ kinh nghiệm nghề nghiệp. Việc lợi dụng hệ thống để thực hiện các hành vi có dấu hiệu lạm dụng quyền lực sẽ bị xử lý nghiêm khắc.</li>
</ul>

<h2>5. Xử lý vi phạm</h2>
<p>Ban Quản Trị UEConnect hoạt động với tiêu chí <strong>"Cộng đồng tự quản và AI hỗ trợ"</strong>:</p>
<ul>
    <li>Hệ thống AI sẽ tự động rà soát và đánh dấu (flag) các ngôn từ nhạy cảm.</li>
    <li>Bất kỳ thành viên nào cũng có quyền sử dụng tính năng <strong>"Báo cáo" (Report)</strong> để thông báo cho Quản trị viên về nội dung/tài khoản vi phạm.</li>
    <li><strong>Các mức xử lý:</strong> Tùy theo mức độ vi phạm, chúng tôi có thể áp dụng các biện pháp: Nhắc nhở, gỡ bài, tạm khóa tài khoản (7-30 ngày), hoặc <strong>Cấm vĩnh viễn (Ban)</strong> khỏi hệ thống.</li>
    <li>Đối với các vi phạm đặc biệt nghiêm trọng (gian lận thi cử, chống phá nhà nước), thông tin có thể được chuyển giao cho các phòng ban chức năng của HCMUE xử lý theo quy chế công tác Sinh viên.</li>
</ul>
@endsection
