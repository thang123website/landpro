# LandPro – website bất động sản (WordPress)

Mã nguồn tự viết cho website bất động sản LandPro, chạy trên **Hello Elementor + Elementor (free)**, Polylang, Fluent Forms, Yoast SEO.

| Thư mục | Mô tả |
|---|---|
| `themes/landpro` | Theme con của Hello Elementor: font Be Vietnam Pro tự host, thang cỡ chữ, header/menu mobile, banner trang con, tin tức có sidebar + mục lục, hiệu ứng cuộn. |
| `plugins/landpro-realestate` | Plugin BĐS: tin nhà đất / dự án, phân loại, bộ lọc tức thì, widget Elementor, bản đồ (Leaflet / OpenFreeMap / Google Maps), trang Ký gửi + duyệt tin, đa ngôn ngữ, SEO schema, tương thích plugin cache, dữ liệu demo (`wp landpro demo --reset`). |
| `plugins/wp-phong-thuy` | Công cụ phong thủy: xem tuổi xây dựng, hướng nhà. |
| `plugins/wp-thuoc-lo-ban` | Thước Lỗ Ban. |

## Cài đặt

1. Cài WordPress, theme **Hello Elementor**, plugin **Elementor**, **Header Footer Elementor**, **Polylang**, **Fluent Forms**, **Yoast SEO**.
2. Chép `themes/landpro` và các thư mục trong `plugins/` vào `wp-content` tương ứng, kích hoạt theme LandPro và 3 plugin.
3. (Tùy chọn) Tạo dữ liệu demo: `wp landpro demo --reset`.
4. Cấu hình ở **Nhà đất → Cài đặt** và **Nhà đất → Bản đồ**.

Thư viện bên thứ ba kèm theo: Leaflet (BSD-2), MapLibre GL (BSD-3), maplibre-gl-leaflet (ISC), Leaflet.GridLayer.GoogleMutant (Beerware), font Be Vietnam Pro (OFL).
