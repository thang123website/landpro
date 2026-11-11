
<!DOCTYPE html>
<html lang="vi" class="no-js">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="stylesheet" href="loban.css"/>
    <script type="text/javascript" src="iscroll.js"></script>
    <script type="text/javascript" src="functions.js"></script>
    <title>Thước lỗ ban, Bảng tra thanh thước lỗ ban</title>
</head>
<body>
<div id="main">
    <div class="container">
        <div class="content">
            <div id="lobanOuter" style="height:400px;">
                <div id="abc"></div>
                <div class="loban-touch-left"></div>
                <div class="loban-touch-right"></div>
                <div id="sodoLoban"></div>
                <div id="container-sodo"><input type="text" value="0" name="sodo" id="sodo" /> mm (nhập số)</div>
                <div id="thanhdo"></div>
                <p class="loban-note">Hãy kéo thước</p>
                <p class="loban-t loban-522"><strong>Thước Lỗ Ban 52.2cm:</strong> Khoảng thông thủy (cửa, cửa sổ...)</p>
                <p class="loban-t loban-429"><strong>Thước Lỗ Ban 42.9cm (Dương trạch):</strong> Khối xây dựng (bếp, bệ, bậc...)</p>
                <p class="loban-t loban-388"><strong>Thước Lỗ Ban 38.8cm (Âm phần):</strong> Đồ nội thất (bàn thờ, tủ...)</p>
                <div id="loban-wrapper">
                    <div id="loban-scroller">
                        <div id="pullRight" style="display:none;">
                            <span class="pullRightIcon"></span><span class="pullRightLabel">Kéo qua phải...</span>
                        </div>
                        <ul id="loban-thelist">
                            <li>
                                <img src="thuoc522.php" nopin="nopin" />
                                <img src="thuoc429.php" nopin="nopin" />
                                <img src="thuoc388.php" nopin="nopin" />
                            </li>
                        </ul>
                        <div id="pullLeft" style="display:none;">
                            <span class="pullLeftIcon"></span><span class="pullLeftLabel">Kéo qua trái...</span>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div style="text-align: right; padding-top: 5px">Đơn vị tính: mm</div>
        </div>
    </div>
    <div class="box_huongdan">
        <div class="tdbox_hd">
            <span>Thước Lỗ Ban 52.2cm: Khoảng không thông thủy (cửa, cửa sổ...)</span>
        </div>
        <div class="noidung_boxhd">
            <span id="nd522-text"></span>
        </div>
        <div class="tdbox_hd">
            <span>Thước Lỗ Ban 42.9cm (Dương trạch): Khối xây dựng (bếp, bệ, bậc...)</span>
        </div>
        <div class="noidung_boxhd">
            <span id="nd429-text"></span>
        </div>
        <div class="tdbox_hd">
            <span>Thước Lỗ Ban 38.8cm (Âm phần): Đồ nội thất (bàn thờ, tủ...)</span>
        </div>
        <div class="noidung_boxhd">
            <span id="nd388-text"></span>
        </div>
    </div>
    <div class="box_huongdan" style="margin-top: 30px">
        <div class="tdbox_hd">
            <span>Hướng dẫn xem Thước Lỗ Ban</span>
        </div>
        <div class="noidung_boxhd">
            <p>Thước Lỗ ban là cây thước được Lỗ Ban, ông Tổ nghề mộc ở Trung Quốc thời Xuân Thu phát minh ra. Nhưng
                trên thực tế, trong ngành địa lý cổ phương Đông, ngoài thước Lỗ Ban (Lỗ Ban xích) còn có nhiều loại
                thước khác được áp dụng như thước Đinh Lan (Đinh Lan xích), thước Áp Bạch (Áp Bạch xích), bản thân thước
                Lỗ ban cũng bao gồm nhiều phiên bản khác nhau như các bản 52,2 cm; 42,9 cm…</p>
            <p>Do có nhiều bài viết, thông tin về thước Lỗ ban có các kích thước khác nhau. Ở đây chúng tôi chỉ giới
                thiệu 3 loại thước phổ biến nhất trên thị trường Việt Nam hiện nay là loại kích thước Lỗ Ban 52,2 cm;
                42,9 cm và 38,8 cm.</p>
            <p>- Đo kích thước rỗng (thông thủy): Thước Lỗ Ban 52,2 cm</p>
            <p>- Đo kích thước đặc: khối xây dựng (bếp, bệ, bậc…): Thước Lỗ Ban 42,9 cm</p>
            <p>- Đo Âm phần: mồ mả, đồ nội thất (bàn thờ, tủ thờ, khuôn khổ bài vị…): Thước Lỗ Ban 38,3 cm</p>
        </div>
    </div>
    <div id="thuocloban">
        <p><strong>Bảng tra nhanh thước Lỗ Ban 52.2</strong></p>
        <div id="loban">
            <div class="loban-h">
                <div class="khoan-h tot">
                    <div class="tenkhoan"><span>Quý nhân</span></div>
                    <div class="cung-h">
                        <div class="tencung">Quyền lộc</div>
                        <div class="tencung">Trung tín</div>
                        <div class="tencung">Tác quan</div>
                        <div class="tencung">Phát đạt</div>
                        <div class="tencung">Thông minh</div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan-h xau">
                    <div class="tenkhoan"><span>Hiểm họa</span></div>
                    <div class="cung-h">
                        <div class="tencung">Án thành</div>
                        <div class="tencung">Hỗn nhân</div>
                        <div class="tencung">Thất hiếu</div>
                        <div class="tencung">Tai họa</div>
                        <div class="tencung">Thường bệnh</div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan-h xau">
                    <div class="tenkhoan"><span>Thiên tai</span></div>
                    <div class="cung-h">
                        <div class="tencung">Hoàn tử</div>
                        <div class="tencung">Quan tài</div>
                        <div class="tencung">Thân tàn</div>
                        <div class="tencung">Thất tài</div>
                        <div class="tencung">Hệ quả</div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan-h tot">
                    <div class="tenkhoan"><span>Thiên tài</span></div>
                    <div class="cung-h">
                        <div class="tencung">Thi thơ</div>
                        <div class="tencung">Văn học</div>
                        <div class="tencung">Thanh quý</div>
                        <div class="tencung">Tác lộc</div>
                        <div class="tencung">Thiên lộc</div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan-h tot">
                    <div class="tenkhoan"><span>Nhân lộc</span></div>
                    <div class="cung-h">
                        <div class="tencung">Trí tồn</div>
                        <div class="tencung">Phú quý</div>
                        <div class="tencung">Tiến bửu</div>
                        <div class="tencung">Thập thiện</div>
                        <div class="tencung">Văn chương</div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan-h xau">
                    <div class="tenkhoan"><span>Cô độc</span></div>
                    <div class="cung-h">
                        <div class="tencung">Bạc nghịch</div>
                        <div class="tencung">Vô vọng</div>
                        <div class="tencung">Ly tán</div>
                        <div class="tencung">Tửu thục</div>
                        <div class="tencung">Dâm dục</div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan-h xau">
                    <div class="tenkhoan"><span>Thiên tặc</span></div>
                    <div class="cung-h">
                        <div class="tencung">Phong bệnh</div>
                        <div class="tencung">Chiêu ôn</div>
                        <div class="tencung">Ôn tài</div>
                        <div class="tencung">Ngục tù</div>
                        <div class="tencung">Quang tài</div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan-h tot">
                    <div class="tenkhoan"><span>Tể tướng</span></div>
                    <div class="cung-h">
                        <div class="tencung">Đại tài</div>
                        <div class="tencung">Thi thơ</div>
                        <div class="tencung">Hoạch tài</div>
                        <div class="tencung">Hiếu tử</div>
                        <div class="tencung">Quý nhân</div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="loban-d">
                <div class="khoan">
                    <div class="cung">
                        <div class="sodo tot">
                            <div class="sodo1">13,05</div>
                            <div class="sodo2">533,05</div>
                            <div class="sodo3">1.053,05</div>
                            <div class="sodo4">1.573,05</div>
                            <div class="sodo5">2.093,05</div>
                            <div class="sodo6">2.613,05</div>
                            <div class="sodo7">3.133,05</div>
                            <div class="sodo8">3.653,05</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">26,10</div>
                            <div class="sodo2">546,10</div>
                            <div class="sodo3">1.066,10</div>
                            <div class="sodo4">1.586,10</div>
                            <div class="sodo5">2.106,10</div>
                            <div class="sodo6">2.626,10</div>
                            <div class="sodo7">3.146,10</div>
                            <div class="sodo8">3.666,10</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">39,15</div>
                            <div class="sodo2">559,15</div>
                            <div class="sodo3">1.079,15</div>
                            <div class="sodo4">1.599,15</div>
                            <div class="sodo5">2.119,15</div>
                            <div class="sodo6">2.639,15</div>
                            <div class="sodo7">3.159,15</div>
                            <div class="sodo8">3.679,15</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">52,20</div>
                            <div class="sodo2">572,20</div>
                            <div class="sodo3">1.092,20</div>
                            <div class="sodo4">1.612,20</div>
                            <div class="sodo5">2.132,20</div>
                            <div class="sodo6">2.652,20</div>
                            <div class="sodo7">3.172,20</div>
                            <div class="sodo8">3.692,20</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">65,25</div>
                            <div class="sodo2">585,25</div>
                            <div class="sodo3">1.105,25</div>
                            <div class="sodo4">1.625,25</div>
                            <div class="sodo5">2.145,25</div>
                            <div class="sodo6">2.665,25</div>
                            <div class="sodo7">3.185,25</div>
                            <div class="sodo8">3.705,25</div>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan">
                    <div class="cung">
                        <div class="sodo xau">
                            <div class="sodo1">78,30</div>
                            <div class="sodo2">598,30</div>
                            <div class="sodo3">1.118,30</div>
                            <div class="sodo4">1.638,30</div>
                            <div class="sodo5">2.158,30</div>
                            <div class="sodo6">2.678,30</div>
                            <div class="sodo7">3.198,30</div>
                            <div class="sodo8">3.718,30</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">91,35</div>
                            <div class="sodo2">611,35</div>
                            <div class="sodo3">1.131,35</div>
                            <div class="sodo4">1.651,35</div>
                            <div class="sodo5">2.171,35</div>
                            <div class="sodo6">2.691,35</div>
                            <div class="sodo7">3.211,35</div>
                            <div class="sodo8">3.731,35</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">104,40</div>
                            <div class="sodo2">624,40</div>
                            <div class="sodo3">1.144,40</div>
                            <div class="sodo4">1.664,40</div>
                            <div class="sodo5">2.184,40</div>
                            <div class="sodo6">2.704,40</div>
                            <div class="sodo7">3.224,40</div>
                            <div class="sodo8">3.744,40</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">117,45</div>
                            <div class="sodo2">637,45</div>
                            <div class="sodo3">1.157,45</div>
                            <div class="sodo4">1.677,45</div>
                            <div class="sodo5">2.197,45</div>
                            <div class="sodo6">2.717,45</div>
                            <div class="sodo7">3.237,45</div>
                            <div class="sodo8">3.757,45</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">130,50</div>
                            <div class="sodo2">650,50</div>
                            <div class="sodo3">1.170,50</div>
                            <div class="sodo4">1.690,50</div>
                            <div class="sodo5">2.210,50</div>
                            <div class="sodo6">2.730,50</div>
                            <div class="sodo7">3.250,50</div>
                            <div class="sodo8">3.770,50</div>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan">
                    <div class="cung">
                        <div class="sodo xau">
                            <div class="sodo1">143,55</div>
                            <div class="sodo2">663,55</div>
                            <div class="sodo3">1.183,55</div>
                            <div class="sodo4">1.703,55</div>
                            <div class="sodo5">2.223,55</div>
                            <div class="sodo6">2.743,55</div>
                            <div class="sodo7">3.263,55</div>
                            <div class="sodo8">3.783,55</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">156,60</div>
                            <div class="sodo2">676,60</div>
                            <div class="sodo3">1.196,60</div>
                            <div class="sodo4">1.716,60</div>
                            <div class="sodo5">2.236,60</div>
                            <div class="sodo6">2.756,60</div>
                            <div class="sodo7">3.276,60</div>
                            <div class="sodo8">3.796,60</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">169,65</div>
                            <div class="sodo2">689,65</div>
                            <div class="sodo3">1.209,65</div>
                            <div class="sodo4">1.729,65</div>
                            <div class="sodo5">2.249,65</div>
                            <div class="sodo6">2.769,65</div>
                            <div class="sodo7">3.289,65</div>
                            <div class="sodo8">3.809,65</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">182,70</div>
                            <div class="sodo2">702,70</div>
                            <div class="sodo3">1.222,70</div>
                            <div class="sodo4">1.742,70</div>
                            <div class="sodo5">2.262,70</div>
                            <div class="sodo6">2.782,70</div>
                            <div class="sodo7">3.302,70</div>
                            <div class="sodo8">3.822,70</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">195,75</div>
                            <div class="sodo2">715,75</div>
                            <div class="sodo3">1.235,75</div>
                            <div class="sodo4">1.755,75</div>
                            <div class="sodo5">2.275,75</div>
                            <div class="sodo6">2.795,75</div>
                            <div class="sodo7">3.315,75</div>
                            <div class="sodo8">3.835,75</div>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan">
                    <div class="cung">
                        <div class="sodo tot">
                            <div class="sodo1">208,80</div>
                            <div class="sodo2">728,80</div>
                            <div class="sodo3">1.248,80</div>
                            <div class="sodo4">1.768,80</div>
                            <div class="sodo5">2.288,80</div>
                            <div class="sodo6">2.808,80</div>
                            <div class="sodo7">3.328,80</div>
                            <div class="sodo8">3.848,80</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">221,85</div>
                            <div class="sodo2">741,85</div>
                            <div class="sodo3">1.261,85</div>
                            <div class="sodo4">1.781,85</div>
                            <div class="sodo5">2.301,85</div>
                            <div class="sodo6">2.821,85</div>
                            <div class="sodo7">3.341,85</div>
                            <div class="sodo8">3.861,85</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">234,90</div>
                            <div class="sodo2">754,90</div>
                            <div class="sodo3">1.274,90</div>
                            <div class="sodo4">1.794,90</div>
                            <div class="sodo5">2.314,90</div>
                            <div class="sodo6">2.834,90</div>
                            <div class="sodo7">3.354,90</div>
                            <div class="sodo8">3.874,90</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">247,95</div>
                            <div class="sodo2">767,95</div>
                            <div class="sodo3">1.287,95</div>
                            <div class="sodo4">1.807,95</div>
                            <div class="sodo5">2.327,95</div>
                            <div class="sodo6">2.847,95</div>
                            <div class="sodo7">3.367,95</div>
                            <div class="sodo8">3.887,95</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">261,00</div>
                            <div class="sodo2">781,00</div>
                            <div class="sodo3">1.301,00</div>
                            <div class="sodo4">1.821,00</div>
                            <div class="sodo5">2.341,00</div>
                            <div class="sodo6">2.861,00</div>
                            <div class="sodo7">3.381,00</div>
                            <div class="sodo8">3.901,00</div>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan">
                    <div class="cung">
                        <div class="sodo tot">
                            <div class="sodo1">274,05</div>
                            <div class="sodo2">794,05</div>
                            <div class="sodo3">1.314,05</div>
                            <div class="sodo4">1.834,05</div>
                            <div class="sodo5">2.354,05</div>
                            <div class="sodo6">2.874,05</div>
                            <div class="sodo7">3.394,05</div>
                            <div class="sodo8">3.914,05</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">287,10</div>
                            <div class="sodo2">807,10</div>
                            <div class="sodo3">1.327,10</div>
                            <div class="sodo4">1.847,10</div>
                            <div class="sodo5">2.367,10</div>
                            <div class="sodo6">2.887,10</div>
                            <div class="sodo7">3.407,10</div>
                            <div class="sodo8">3.927,10</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">300,15</div>
                            <div class="sodo2">820,15</div>
                            <div class="sodo3">1.340,15</div>
                            <div class="sodo4">1.860,15</div>
                            <div class="sodo5">2.380,15</div>
                            <div class="sodo6">2.900,15</div>
                            <div class="sodo7">3.420,15</div>
                            <div class="sodo8">3.940,15</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">313,20</div>
                            <div class="sodo2">833,20</div>
                            <div class="sodo3">1.353,20</div>
                            <div class="sodo4">1.873,20</div>
                            <div class="sodo5">2.393,20</div>
                            <div class="sodo6">2.913,20</div>
                            <div class="sodo7">3.433,20</div>
                            <div class="sodo8">3.953,20</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">326,25</div>
                            <div class="sodo2">846,25</div>
                            <div class="sodo3">1.366,25</div>
                            <div class="sodo4">1.886,25</div>
                            <div class="sodo5">2.406,25</div>
                            <div class="sodo6">2.926,25</div>
                            <div class="sodo7">3.446,25</div>
                            <div class="sodo8">3.966,25</div>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan">
                    <div class="cung">
                        <div class="sodo xau">
                            <div class="sodo1">339,30</div>
                            <div class="sodo2">859,30</div>
                            <div class="sodo3">1.379,30</div>
                            <div class="sodo4">1.899,30</div>
                            <div class="sodo5">2.419,30</div>
                            <div class="sodo6">2.939,30</div>
                            <div class="sodo7">3.459,30</div>
                            <div class="sodo8">3.979,30</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">352,35</div>
                            <div class="sodo2">872,35</div>
                            <div class="sodo3">1.392,35</div>
                            <div class="sodo4">1.912,35</div>
                            <div class="sodo5">2.432,35</div>
                            <div class="sodo6">2.952,35</div>
                            <div class="sodo7">3.472,35</div>
                            <div class="sodo8">3.992,35</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">365,40</div>
                            <div class="sodo2">885,40</div>
                            <div class="sodo3">1.405,40</div>
                            <div class="sodo4">1.925,40</div>
                            <div class="sodo5">2.445,40</div>
                            <div class="sodo6">2.965,40</div>
                            <div class="sodo7">3.485,40</div>
                            <div class="sodo8">4.005,40</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">378,45</div>
                            <div class="sodo2">898,45</div>
                            <div class="sodo3">1.418,45</div>
                            <div class="sodo4">1.938,45</div>
                            <div class="sodo5">2.458,45</div>
                            <div class="sodo6">2.978,45</div>
                            <div class="sodo7">3.498,45</div>
                            <div class="sodo8">4.018,45</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">391,50</div>
                            <div class="sodo2">911,50</div>
                            <div class="sodo3">1.431,50</div>
                            <div class="sodo4">1.951,50</div>
                            <div class="sodo5">2.471,50</div>
                            <div class="sodo6">2.991,50</div>
                            <div class="sodo7">3.511,50</div>
                            <div class="sodo8">4.031,50</div>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan">
                    <div class="cung">
                        <div class="sodo xau">
                            <div class="sodo1">404,55</div>
                            <div class="sodo2">924,55</div>
                            <div class="sodo3">1.444,55</div>
                            <div class="sodo4">1.964,55</div>
                            <div class="sodo5">2.484,55</div>
                            <div class="sodo6">3.004,55</div>
                            <div class="sodo7">3.524,55</div>
                            <div class="sodo8">4.044,55</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">417,60</div>
                            <div class="sodo2">937,60</div>
                            <div class="sodo3">1.457,60</div>
                            <div class="sodo4">1.977,60</div>
                            <div class="sodo5">2.497,60</div>
                            <div class="sodo6">3.017,60</div>
                            <div class="sodo7">3.537,60</div>
                            <div class="sodo8">4.057,60</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">430,65</div>
                            <div class="sodo2">950,65</div>
                            <div class="sodo3">1.470,65</div>
                            <div class="sodo4">1.990,65</div>
                            <div class="sodo5">2.510,65</div>
                            <div class="sodo6">3.030,65</div>
                            <div class="sodo7">3.550,65</div>
                            <div class="sodo8">4.070,65</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">443,70</div>
                            <div class="sodo2">963,70</div>
                            <div class="sodo3">1.483,70</div>
                            <div class="sodo4">2.003,70</div>
                            <div class="sodo5">2.523,70</div>
                            <div class="sodo6">3.043,70</div>
                            <div class="sodo7">3.563,70</div>
                            <div class="sodo8">4.083,70</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo xau">
                            <div class="sodo1">456,75</div>
                            <div class="sodo2">976,75</div>
                            <div class="sodo3">1.496,75</div>
                            <div class="sodo4">2.016,75</div>
                            <div class="sodo5">2.536,75</div>
                            <div class="sodo6">3.056,75</div>
                            <div class="sodo7">3.576,75</div>
                            <div class="sodo8">4.096,75</div>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="khoan">
                    <div class="cung">
                        <div class="sodo tot">
                            <div class="sodo1">469,80</div>
                            <div class="sodo2">989,80</div>
                            <div class="sodo3">1.509,80</div>
                            <div class="sodo4">2.029,80</div>
                            <div class="sodo5">2.549,80</div>
                            <div class="sodo6">3.069,80</div>
                            <div class="sodo7">3.589,80</div>
                            <div class="sodo8">4.109,80</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">482,85</div>
                            <div class="sodo2">1.002,85</div>
                            <div class="sodo3">1.522,85</div>
                            <div class="sodo4">2.042,85</div>
                            <div class="sodo5">2.562,85</div>
                            <div class="sodo6">3.082,85</div>
                            <div class="sodo7">3.602,85</div>
                            <div class="sodo8">4.122,85</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">495,90</div>
                            <div class="sodo2">1.015,90</div>
                            <div class="sodo3">1.535,90</div>
                            <div class="sodo4">2.055,90</div>
                            <div class="sodo5">2.575,90</div>
                            <div class="sodo6">3.095,90</div>
                            <div class="sodo7">3.615,90</div>
                            <div class="sodo8">4.135,90</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">508,95</div>
                            <div class="sodo2">1.028,95</div>
                            <div class="sodo3">1.548,95</div>
                            <div class="sodo4">2.068,95</div>
                            <div class="sodo5">2.588,95</div>
                            <div class="sodo6">3.108,95</div>
                            <div class="sodo7">3.628,95</div>
                            <div class="sodo8">4.148,95</div>
                            <div class="clear"></div>
                        </div>
                        <div class="sodo tot">
                            <div class="sodo1">522,00</div>
                            <div class="sodo2">1.042,00</div>
                            <div class="sodo3">1.562,00</div>
                            <div class="sodo4">2.082,00</div>
                            <div class="sodo5">2.602,00</div>
                            <div class="sodo6">3.122,00</div>
                            <div class="sodo7">3.642,00</div>
                            <div class="sodo8">4.162,00</div>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div>
        <p><!-- #loban --></p>
    </div>
</div>
</body>
</html>