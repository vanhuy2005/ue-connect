import os
from PIL import Image, ImageDraw, ImageOps

def crop_main_logo_with_threshold(logo_path, padding=30):
    if not os.path.exists(logo_path):
        print(f"Error: File not found at {logo_path}")
        return

    # Mở ảnh logo gốc
    img = Image.open(logo_path)
    
    if img.mode != 'RGBA' and img.mode != 'RGB':
        img = img.convert('RGBA')

    # Bước 1: Chuyển sang ảnh xám để phân ngưỡng
    gray = img.convert('L')

    # Phân ngưỡng (Threshold): các điểm ảnh có độ sáng > 250 (gần trắng) -> 255 (trắng tinh)
    # Các điểm ảnh tối hơn (logo) -> 0 (đen)
    binary = gray.point(lambda p: 255 if p > 250 else 0)

    # Bước 2: Xóa vùng "PRIMARY LOGO" ở góc trên bên trái
    # Tô màu trắng tinh (255) lên vùng này trong ảnh nhị phân
    draw = ImageDraw.Draw(binary)
    erase_width = int(img.width * 0.35)
    erase_height = int(img.height * 0.18)
    draw.rectangle([0, 0, erase_width, erase_height], fill=255)

    # Bước 3: Đảo ngược màu để dùng getbbox
    # getbbox() tìm hộp giới hạn của các điểm ảnh KHÁC KHÔNG (non-zero / > 0)
    # Sau khi đảo ngược, logo (đen -> trắng = 255), nền (trắng -> đen = 0)
    inverted = ImageOps.invert(binary)
    bbox = inverted.getbbox()
    
    if bbox:
        left, top, right, bottom = bbox
        
        # Thêm khoảng đệm padding xung quanh logo chính
        width, height = img.size
        left = max(0, left - padding)
        top = max(0, top - padding)
        right = min(width, right + padding)
        bottom = min(height, bottom + padding)
        
        # Cắt từ ảnh gốc (chất lượng màu gốc) bằng tọa độ đã xác định
        cropped_img = img.crop((left, top, right, bottom))
        
        # Lưu đè trực tiếp lên ảnh gốc để cập nhật
        cropped_img.save(logo_path)
        print(f"Success! Logo cropped and replaced at: {logo_path}")
        print(f"Final size: {cropped_img.size} (bbox: left={left}, top={top}, right={right}, bottom={bottom})")
    else:
        print("Error: Could not detect logo contents after thresholding.")

if __name__ == "__main__":
    base_dir = os.path.dirname(os.path.abspath(__file__))
    logo_path = os.path.join(base_dir, "primary-logo.png")
    
    crop_main_logo_with_threshold(logo_path, padding=30)
