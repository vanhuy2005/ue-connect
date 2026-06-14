@extends('legal.layout')

@section('title', 'Chính sách bảo mật')
@section('subtitle', 'Cập nhật lần cuối: ' . date('d/m/Y'))

@section('content')
<p>Bảo vệ dữ liệu cá nhân của bạn là ưu tiên hàng đầu tại <strong>UEConnect</strong>. Chính sách bảo mật này giải thích cách chúng tôi thu thập, sử dụng, lưu trữ và bảo vệ thông tin của bạn khi bạn sử dụng nền tảng mạng xã hội sinh viên HCMUE.</p>

<h2>1. Thông tin chúng tôi thu thập</h2>
<p>Để đảm bảo tính xác thực và an toàn cho cộng đồng, chúng tôi thu thập các loại thông tin sau:</p>
<ul>
    <li><strong>Thông tin định danh:</strong> Bao gồm Mã số sinh viên (MSSV), Họ và tên, Khoa, Khóa học, Email trường cấp và hình ảnh Thẻ sinh viên/CCCD (chỉ dùng một lần trong quá trình duyệt tài khoản).</li>
    <li><strong>Thông tin hồ sơ:</strong> Giới thiệu bản thân (Bio), kỹ năng, kinh nghiệm làm việc, và các thông tin bạn chủ động cung cấp trên trang cá nhân.</li>
    <li><strong>Dữ liệu hoạt động:</strong> Bài viết, bình luận, lượt thích, cộng đồng bạn tham gia, và tương tác của bạn trên nền tảng.</li>
    <li><strong>Tin nhắn trực tiếp:</strong> Dữ liệu tin nhắn giữa bạn và người dùng khác (bao gồm hệ thống Mentor) được lưu trữ mã hóa an toàn trên máy chủ.</li>
</ul>

<h2>2. Cách chúng tôi sử dụng thông tin</h2>
<p>Thông tin của bạn được sử dụng vào các mục đích nội bộ sau đây:</p>
<ul>
    <li><strong>Xác thực danh tính:</strong> Đảm bảo 100% người dùng trên UEConnect là sinh viên/cựu sinh viên/giảng viên hợp lệ của HCMUE.</li>
    <li><strong>Cá nhân hóa trải nghiệm:</strong> Gợi ý các bài viết, cộng đồng (Community) và Cố vấn (Mentor) phù hợp với ngành học và sở thích của bạn.</li>
    <li><strong>Hỗ trợ AI:</strong> Sử dụng dữ liệu để trợ lý AI (Gemini Flash & Local LLM) của UEConnect có thể hỗ trợ bạn tốt hơn về quy chế học vụ, gợi ý lộ trình học tập (Không sử dụng dữ liệu cá nhân nhạy cảm để huấn luyện AI).</li>
    <li><strong>Duy trì môi trường an toàn:</strong> Rà soát và xử lý các hành vi vi phạm Tiêu chuẩn cộng đồng, ngăn chặn tài khoản giả mạo hoặc lừa đảo.</li>
</ul>

<h2>3. Chia sẻ thông tin</h2>
<p><strong>UEConnect cam kết KHÔNG BÁN dữ liệu cá nhân của bạn cho bất kỳ bên thứ ba nào.</strong> Chúng tôi chỉ chia sẻ thông tin trong các trường hợp thật sự cần thiết:</p>
<ul>
    <li><strong>Dịch vụ bên thứ ba:</strong> Chúng tôi có thể chia sẻ một số thông tin giới hạn với các đối tác hạ tầng (như hệ thống gửi email Resend, lưu trữ ảnh Cloudinary) chỉ nhằm mục đích vận hành kỹ thuật. Các đối tác này bị ràng buộc bởi các điều khoản bảo mật nghiêm ngặt.</li>
    <li><strong>Yêu cầu pháp lý:</strong> Khi có yêu cầu chính thức từ Nhà trường (Ban Giám Hiệu, Phòng Công tác Học sinh Sinh viên) liên quan đến các vấn đề vi phạm nghiêm trọng hoặc theo yêu cầu của pháp luật.</li>
</ul>

<h2>4. Bảo mật dữ liệu</h2>
<p>Chúng tôi áp dụng các tiêu chuẩn mã hóa công nghiệp (như HTTPS) để bảo vệ dữ liệu truyền tải. Mật khẩu và các thông tin xác thực OTP của bạn được băm (hashing) một chiều và không thể phục hồi. Tuy nhiên, không có hệ thống nào an toàn 100%, bạn cũng cần có trách nhiệm bảo vệ mật khẩu của mình.</p>

<h2>5. Quyền của bạn</h2>
<p>Bạn hoàn toàn có quyền kiểm soát thông tin cá nhân của mình trên UEConnect:</p>
<ul>
    <li><strong>Quyền truy cập và chỉnh sửa:</strong> Bạn có thể tự do thay đổi thông tin hồ sơ cá nhân (trừ các thông tin định danh cứng như MSSV) bất cứ lúc nào.</li>
    <li><strong>Quyền ẩn hồ sơ:</strong> Bạn có thể cấu hình tài khoản ở chế độ riêng tư hoặc ẩn khỏi kết quả tìm kiếm nếu không muốn bị người khác phát hiện.</li>
    <li><strong>Quyền xóa dữ liệu:</strong> Bạn có quyền gửi yêu cầu xóa hoàn toàn tài khoản và dữ liệu liên quan khỏi hệ thống bằng cách liên hệ với Quản trị viên.</li>
</ul>

<h2>6. Thay đổi Chính sách bảo mật</h2>
<p>Chính sách này có thể được cập nhật để phản ánh các thay đổi về công nghệ hoặc quy định. Chúng tôi sẽ thông báo nổi bật trên Bảng tin khi có sự thay đổi lớn.</p>

<p>Nếu có thắc mắc về quyền riêng tư, vui lòng liên hệ Ban Quản Trị tại: <strong>privacy@ueconnect.edu.vn</strong>.</p>
@endsection
