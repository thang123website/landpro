<?php
/**
 * Bài viết tin tức demo (nội dung minh họa, không dùng số liệu thị trường thật).
 * Mỗi bài: slug, chuyên mục, ảnh (tên vị trí ảnh mới), tiêu đề, tóm tắt, nội dung VI; bản EN.
 *
 * @package LandPro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'categories' => array(
		'kinh-nghiem' => array( 'Kinh nghiệm mua nhà', 'buying-tips', 'Buying tips' ),
		'phap-ly'     => array( 'Pháp lý nhà đất', 'legal-guide', 'Legal guide' ),
		'thi-truong'  => array( 'Thị trường', 'market', 'Market' ),
		'phong-thuy'  => array( 'Phong thủy nhà ở', 'feng-shui', 'Feng shui' ),
	),
	'posts'      => array(
		array(
			'slug'  => 'kinh-nghiem-mua-can-ho-lan-dau',
			'cat'   => 'kinh-nghiem',
			'image' => 'tin-192',
			'days'  => 2,
			'title' => 'Kinh nghiệm mua căn hộ lần đầu: 7 bước để không mất tiền oan',
			'excerpt' => 'Từ xác định ngân sách, chọn khu vực đến kiểm tra pháp lý và nhận bàn giao, đây là quy trình gọn gàng cho người mua căn hộ lần đầu.',
			'body'  => '<p>Mua căn hộ đầu tiên là quyết định tài chính lớn. Chuẩn bị kỹ từng bước giúp bạn tránh những khoản chi phát sinh và rủi ro pháp lý không đáng có.</p>
<h2>1. Xác định ngân sách thực tế</h2>
<p>Ngoài giá căn hộ, hãy cộng thêm phí bảo trì 2%, thuế phí sang tên, chi phí nội thất và một khoản dự phòng khoảng 5–10%. Nếu vay ngân hàng, tổng tiền trả góp hằng tháng nên dưới 40% thu nhập.</p>
<h2>2. Chọn khu vực phù hợp nhu cầu</h2>
<p>Ưu tiên quãng đường đi làm, trường học của con và tiện ích hằng ngày. Một căn hộ rẻ hơn nhưng mỗi ngày mất thêm một giờ di chuyển có thể không còn rẻ.</p>
<h2>3. Kiểm tra chủ đầu tư và pháp lý dự án</h2>
<h3>Với dự án mới</h3>
<p>Yêu cầu xem quyết định giao đất, giấy phép xây dựng, văn bản đủ điều kiện bán nhà ở hình thành trong tương lai và bảo lãnh ngân hàng.</p>
<h3>Với căn hộ đã có sổ</h3>
<p>Đối chiếu sổ hồng bản gốc, kiểm tra tình trạng thế chấp và tranh chấp tại văn phòng đăng ký đất đai.</p>
<h2>4. Đi xem nhà vào nhiều thời điểm</h2>
<p>Xem căn hộ vào buổi sáng, buổi chiều và cả buổi tối để biết ánh sáng, tiếng ồn và tình trạng thang máy giờ cao điểm.</p>
<h2>5. Đọc kỹ hợp đồng</h2>
<p>Chú ý tiến độ thanh toán, thời hạn bàn giao, mức phạt chậm bàn giao, danh mục vật liệu và diện tích tính theo thông thủy hay tim tường.</p>
<h2>6. Thanh toán qua tài khoản</h2>
<p>Luôn chuyển khoản vào tài khoản đứng tên chủ đầu tư hoặc chủ nhà, ghi rõ nội dung, giữ lại toàn bộ chứng từ.</p>
<h2>7. Nghiệm thu kỹ khi nhận nhà</h2>
<p>Kiểm tra tường, trần, sàn, thiết bị điện nước, cửa và ban công. Ghi lỗi vào biên bản và yêu cầu sửa trước khi ký nhận.</p>
<p><em>Nội dung mang tính tham khảo (dữ liệu demo).</em></p>',
			'en_title' => 'Buying your first apartment: 7 steps to avoid costly mistakes',
			'en_excerpt' => 'From setting a budget and choosing a location to checking the paperwork and inspecting the handover, a simple process for first-time buyers.',
			'en_body' => '<p>Buying your first apartment is a big financial decision. Preparing each step carefully helps you avoid unexpected costs and legal risks.</p>
<h2>1. Set a realistic budget</h2>
<p>On top of the apartment price, add the 2% maintenance fee, transfer taxes and fees, furniture and a contingency of about 5–10%. If you take a bank loan, keep monthly repayments below 40% of your income.</p>
<h2>2. Choose a location that fits your life</h2>
<p>Prioritise your commute, your children\'s school and everyday amenities. A cheaper apartment that adds an hour of travel every day may not be cheaper after all.</p>
<h2>3. Check the developer and the project\'s legal status</h2>
<h3>For new projects</h3>
<p>Ask to see the land allocation decision, the building permit, the approval to sell off-plan housing and the bank guarantee.</p>
<h3>For apartments with a title</h3>
<p>Check the original pink book and confirm there is no mortgage or dispute at the land registration office.</p>
<h2>4. Visit at different times of day</h2>
<p>View the apartment in the morning, afternoon and evening to understand the light, the noise and the lifts at rush hour.</p>
<h2>5. Read the contract carefully</h2>
<p>Pay attention to the payment schedule, handover date, late-handover penalties, the list of finishes and whether the area is measured net or gross.</p>
<h2>6. Pay by bank transfer</h2>
<p>Always transfer to an account in the name of the developer or the owner, write a clear description and keep every receipt.</p>
<h2>7. Inspect thoroughly at handover</h2>
<p>Check walls, ceilings, floors, electrical and plumbing fittings, doors and balconies. Record any defects in the handover minutes and have them fixed before you sign.</p>
<p><em>For reference only (demo data).</em></p>',
		),
		array(
			'slug'  => 'checklist-phap-ly-truoc-khi-dat-coc-mua-nha',
			'cat'   => 'phap-ly',
			'image' => 'tin-7579',
			'days'  => 5,
			'title' => 'Checklist pháp lý cần kiểm tra trước khi đặt cọc mua nhà',
			'excerpt' => 'Những giấy tờ bắt buộc phải xem tận mắt và những điều khoản cần có trong hợp đồng đặt cọc để bảo vệ người mua.',
			'body'  => '<p>Đặt cọc là bước dễ phát sinh tranh chấp nhất trong giao dịch nhà đất. Dưới đây là danh sách cần kiểm tra trước khi chuyển bất kỳ khoản tiền nào.</p>
<h2>Giấy tờ của bất động sản</h2>
<h3>Sổ hồng / sổ đỏ bản gốc</h3>
<p>Đối chiếu tên chủ sở hữu, số thửa, diện tích, mục đích sử dụng và các trang ghi biến động phía sau.</p>
<h3>Quy hoạch và tình trạng pháp lý</h3>
<p>Tra cứu thông tin quy hoạch tại cơ quan quản lý đất đai để biết nhà có nằm trong diện giải tỏa, lộ giới hay không.</p>
<h2>Giấy tờ của người bán</h2>
<p>Căn cước công dân, giấy xác nhận tình trạng hôn nhân. Nếu là tài sản chung của vợ chồng, cần cả hai người cùng ký.</p>
<h2>Điều khoản bắt buộc trong hợp đồng đặt cọc</h2>
<ul>
<li>Số tiền cọc, thời hạn ký hợp đồng mua bán công chứng.</li>
<li>Mức phạt cọc khi một bên không thực hiện.</li>
<li>Bên chịu thuế, phí sang tên.</li>
<li>Thời điểm bàn giao nhà và tình trạng nhà khi bàn giao.</li>
</ul>
<h2>Nên công chứng hợp đồng đặt cọc</h2>
<p>Pháp luật không bắt buộc, nhưng công chứng giúp xác minh danh tính, năng lực của các bên và là bằng chứng chắc chắn nếu có tranh chấp.</p>
<p><em>Nội dung mang tính tham khảo, không thay thế tư vấn pháp lý (dữ liệu demo).</em></p>',
			'en_title' => 'Legal checklist before paying a deposit on a property',
			'en_excerpt' => 'The documents you must see in person and the clauses your deposit agreement needs to protect you as a buyer.',
			'en_body' => '<p>The deposit is the step where property disputes most often arise. Here is what to check before you transfer any money.</p>
<h2>Property documents</h2>
<h3>Original pink or red book</h3>
<p>Check the owner\'s name, plot number, area, land use purpose and the change records on the back pages.</p>
<h3>Zoning and legal status</h3>
<p>Look up the zoning information at the land management office to see whether the property is affected by road widening or clearance plans.</p>
<h2>Seller\'s documents</h2>
<p>Citizen ID and marital status certificate. If the property is jointly owned by a married couple, both spouses must sign.</p>
<h2>Essential clauses in the deposit agreement</h2>
<ul>
<li>Deposit amount and deadline to sign the notarised sale contract.</li>
<li>Penalty if either party fails to proceed.</li>
<li>Which party pays the transfer taxes and fees.</li>
<li>Handover date and the condition of the property at handover.</li>
</ul>
<h2>Notarise the deposit agreement</h2>
<p>It is not mandatory, but notarisation verifies the identity and capacity of both parties and gives you solid evidence in case of a dispute.</p>
<p><em>For reference only, not legal advice (demo data).</em></p>',
		),
		array(
			'slug'  => 'nen-mua-hay-thue-nha-o-thanh-pho',
			'cat'   => 'kinh-nghiem',
			'image' => 'tin-195',
			'days'  => 9,
			'title' => 'Nên mua hay thuê nhà khi sống ở thành phố lớn?',
			'excerpt' => 'So sánh chi phí, sự linh hoạt và giá trị dài hạn giữa mua và thuê để chọn phương án phù hợp với từng giai đoạn cuộc sống.',
			'body'  => '<p>Không có câu trả lời đúng cho mọi người. Lựa chọn phụ thuộc vào tài chính, công việc và kế hoạch gia đình của bạn trong 5–10 năm tới.</p>
<h2>Khi nào nên thuê nhà</h2>
<ul>
<li>Công việc chưa ổn định hoặc có thể chuyển nơi làm việc.</li>
<li>Chưa tích lũy đủ khoản trả trước khoảng 30% giá trị căn nhà.</li>
<li>Muốn thử sống ở nhiều khu vực trước khi quyết định.</li>
</ul>
<h2>Khi nào nên mua nhà</h2>
<ul>
<li>Thu nhập ổn định, dự định gắn bó lâu dài với thành phố.</li>
<li>Khoản trả góp hằng tháng tương đương tiền thuê nhà cùng loại.</li>
<li>Muốn chủ động sửa sang, trang trí theo ý mình.</li>
</ul>
<h2>Cách so sánh chi phí</h2>
<h3>Chi phí thuê</h3>
<p>Tiền thuê, tiền cọc, chi phí chuyển nhà định kỳ.</p>
<h3>Chi phí sở hữu</h3>
<p>Lãi vay, phí quản lý, bảo trì, thuế, cùng chi phí cơ hội của khoản tiền trả trước.</p>
<h2>Lời khuyên</h2>
<p>Hãy lập bảng so sánh cụ thể theo thu nhập của bạn. Chuyên viên LandPro có thể giúp bạn tính toán miễn phí.</p>
<p><em>Nội dung mang tính tham khảo (dữ liệu demo).</em></p>',
			'en_title' => 'Should you buy or rent a home in a big city?',
			'en_excerpt' => 'Comparing costs, flexibility and long-term value of buying versus renting to choose what fits each stage of life.',
			'en_body' => '<p>There is no single right answer. The choice depends on your finances, your job and your family plans for the next 5–10 years.</p>
<h2>When renting makes sense</h2>
<ul>
<li>Your job is not yet stable or you may relocate.</li>
<li>You have not saved a down payment of about 30% of the home\'s value.</li>
<li>You want to try living in different areas before deciding.</li>
</ul>
<h2>When buying makes sense</h2>
<ul>
<li>You have a stable income and plan to stay in the city long term.</li>
<li>Monthly repayments are close to the rent for a similar home.</li>
<li>You want the freedom to renovate and decorate as you like.</li>
</ul>
<h2>How to compare the costs</h2>
<h3>Cost of renting</h3>
<p>Rent, deposit and the periodic cost of moving.</p>
<h3>Cost of owning</h3>
<p>Loan interest, management fees, maintenance, taxes and the opportunity cost of your down payment.</p>
<h2>Our advice</h2>
<p>Build a side-by-side comparison based on your own income. A LandPro consultant can help you work it out free of charge.</p>
<p><em>For reference only (demo data).</em></p>',
		),
		array(
			'slug'  => 'cach-doc-bang-gia-du-an-can-ho',
			'cat'   => 'thi-truong',
			'image' => 'tin-185',
			'days'  => 14,
			'title' => 'Cách đọc bảng giá dự án căn hộ để so sánh đúng',
			'excerpt' => 'Giá thông thủy hay tim tường, đã gồm VAT chưa, chính sách chiết khấu tính thế nào: những điểm cần hiểu khi đọc bảng giá.',
			'body'  => '<p>Hai dự án cùng ghi “50 triệu/m²” có thể chênh nhau đáng kể khi quy về số tiền thực trả. Đây là cách đọc bảng giá cho chính xác.</p>
<h2>Diện tích thông thủy và tim tường</h2>
<p>Diện tích thông thủy là phần sử dụng thực tế, thường nhỏ hơn diện tích tim tường 5–8%. Hãy quy đổi về cùng một cách tính trước khi so sánh.</p>
<h2>Giá đã gồm những gì</h2>
<ul>
<li>Thuế VAT 10% (thường chưa tính vào giá đất).</li>
<li>Phí bảo trì 2% giá trị căn hộ.</li>
<li>Gói nội thất bàn giao.</li>
</ul>
<h2>Chính sách bán hàng</h2>
<h3>Chiết khấu thanh toán sớm</h3>
<p>Tính xem khoản chiết khấu có lớn hơn lãi suất bạn phải trả nếu vay để thanh toán sớm hay không.</p>
<h3>Hỗ trợ lãi suất</h3>
<p>Đọc kỹ thời gian hỗ trợ và lãi suất thả nổi sau khi hết ưu đãi.</p>
<h2>Vị trí căn trong dự án</h2>
<p>Tầng, hướng, view và căn góc thường có hệ số giá khác nhau. Hỏi bảng hệ số để hiểu vì sao hai căn cùng diện tích lại khác giá.</p>
<p><em>Nội dung mang tính tham khảo (dữ liệu demo).</em></p>',
			'en_title' => 'How to read an apartment price list and compare correctly',
			'en_excerpt' => 'Net or gross area, VAT included or not, how discounts work: what to understand when reading a project price list.',
			'en_body' => '<p>Two projects that both say “50 million per m²” can differ significantly once you work out what you actually pay. Here is how to read a price list correctly.</p>
<h2>Net area and gross area</h2>
<p>Net (carpet) area is the usable space and is usually 5–8% smaller than gross area measured to the wall centre. Convert both projects to the same basis before comparing.</p>
<h2>What the price includes</h2>
<ul>
<li>10% VAT (usually not charged on the land portion).</li>
<li>2% maintenance fee.</li>
<li>Furniture package at handover.</li>
</ul>
<h2>Sales policy</h2>
<h3>Early payment discount</h3>
<p>Check whether the discount is larger than the interest you would pay if you borrowed to pay early.</p>
<h3>Interest rate support</h3>
<p>Read the support period carefully, and the floating rate that applies afterwards.</p>
<h2>Position of the unit</h2>
<p>Floor, orientation, view and corner units usually carry different price coefficients. Ask for the coefficient table to understand why two units of the same size cost different amounts.</p>
<p><em>For reference only (demo data).</em></p>',
		),
		array(
			'slug'  => 'huong-nha-va-phong-thuy-co-ban',
			'cat'   => 'phong-thuy',
			'image' => 'tin-198',
			'days'  => 20,
			'title' => 'Hướng nhà và phong thủy cơ bản cho người mua nhà',
			'excerpt' => 'Những nguyên tắc phong thủy phổ biến khi chọn hướng nhà, bố trí cửa chính, phòng ngủ và bếp, kèm công cụ tra cứu miễn phí.',
			'body'  => '<p>Nhiều gia đình Việt quan tâm đến phong thủy khi chọn nhà. Dưới đây là những nguyên tắc phổ biến, nên kết hợp với điều kiện thực tế như ánh sáng và thông gió.</p>
<h2>Chọn hướng nhà theo tuổi</h2>
<p>Theo quan niệm Bát trạch, mỗi người thuộc Đông tứ mệnh hoặc Tây tứ mệnh, tương ứng với các hướng tốt khác nhau. Bạn có thể tra cứu nhanh bằng công cụ <a href="/xem-tuoi-xay-dung-huong-nha/">Xem tuổi – Hướng nhà</a>.</p>
<h2>Cửa chính và lối vào</h2>
<p>Cửa chính nên thoáng, không bị vật cản lớn che chắn, không đối diện trực tiếp cầu thang hay nhà vệ sinh.</p>
<h2>Bố trí phòng trong nhà</h2>
<h3>Phòng ngủ</h3>
<p>Đầu giường tựa tường vững, tránh đặt dưới xà ngang và đối diện gương.</p>
<h3>Bếp</h3>
<p>Bếp không đặt đối diện cửa chính, tránh nằm sát chậu rửa (thủy – hỏa xung khắc).</p>
<h2>Kích thước theo thước Lỗ Ban</h2>
<p>Cửa, bàn thờ, bếp thường được chọn theo cung tốt của thước Lỗ Ban. Tra cứu tại công cụ <a href="/thuoc-lo-ban/">Thước Lỗ Ban</a>.</p>
<p><em>Nội dung mang tính tham khảo theo quan niệm dân gian (dữ liệu demo).</em></p>',
			'en_title' => 'House orientation and basic feng shui for home buyers',
			'en_excerpt' => 'Common feng shui principles for choosing a house direction and arranging the main door, bedrooms and kitchen.',
			'en_body' => '<p>Many Vietnamese families consider feng shui when choosing a home. These are common principles, best combined with practical factors such as light and ventilation.</p>
<h2>Choosing the orientation by birth year</h2>
<p>In the Eight Mansions tradition, each person belongs to the East or the West group, each with its own favourable directions. You can check quickly with our <a href="/xem-tuoi-xay-dung-huong-nha/">feng shui calculator</a> (in Vietnamese).</p>
<h2>Main door and entrance</h2>
<p>The main door should be open and unobstructed, and should not face a staircase or a toilet directly.</p>
<h2>Arranging the rooms</h2>
<h3>Bedrooms</h3>
<p>Place the bed head against a solid wall, avoid sleeping under a beam and avoid facing a mirror.</p>
<h3>Kitchen</h3>
<p>The stove should not face the main door or sit right next to the sink (fire and water clash).</p>
<h2>Sizes by the Lo Ban ruler</h2>
<p>Doors, altars and stoves are often sized to the auspicious sections of the Lo Ban ruler. Try our <a href="/thuoc-lo-ban/">Lo Ban ruler</a> tool (in Vietnamese).</p>
<p><em>For reference only, based on folk beliefs (demo data).</em></p>',
		),
		array(
			'slug'  => 'dau-tu-dat-nen-vung-ven-luu-y',
			'cat'   => 'thi-truong',
			'image' => 'tin-201',
			'days'  => 27,
			'title' => 'Đầu tư đất nền vùng ven: 5 lưu ý trước khi xuống tiền',
			'excerpt' => 'Đất nền vùng ven hấp dẫn nhờ giá thấp, nhưng cần kiểm tra kỹ quy hoạch, hạ tầng và tính thanh khoản để tránh rủi ro.',
			'body'  => '<p>Đất nền ở các khu vực vùng ven thường có giá dễ tiếp cận hơn nội thành. Tuy nhiên đây cũng là phân khúc cần thận trọng nhất.</p>
<h2>1. Kiểm tra quy hoạch sử dụng đất</h2>
<p>Đảm bảo thửa đất là đất ở, không nằm trong quy hoạch giao thông, công viên hoặc đất nông nghiệp chưa chuyển đổi.</p>
<h2>2. Hạ tầng đã có hay chỉ là kế hoạch</h2>
<p>Đường, điện, nước, thoát nước đã hoàn thiện sẽ quyết định khả năng xây dựng và giá trị thực của lô đất.</p>
<h2>3. Pháp lý từng lô</h2>
<p>Ưu tiên lô đã có sổ riêng. Với dự án phân lô, kiểm tra quyết định phê duyệt và biên bản bàn giao hạ tầng.</p>
<h2>4. Tính thanh khoản</h2>
<p>Khu vực có dân cư hiện hữu, trường học, chợ sẽ dễ bán lại hơn khu chỉ có đất trống.</p>
<h2>5. Không dùng đòn bẩy quá cao</h2>
<p>Đất nền thường cần thời gian để tăng giá. Hãy đảm bảo bạn có thể giữ đất trong nhiều năm mà không áp lực trả nợ.</p>
<p><em>Nội dung mang tính tham khảo, không phải khuyến nghị đầu tư (dữ liệu demo).</em></p>',
			'en_title' => 'Investing in suburban land plots: 5 things to check first',
			'en_excerpt' => 'Suburban land is attractive for its lower price, but zoning, infrastructure and liquidity need careful checking.',
			'en_body' => '<p>Land plots in suburban areas are usually more affordable than in the city centre, but this is also the segment that calls for the most caution.</p>
<h2>1. Check the land use zoning</h2>
<p>Make sure the plot is residential land and is not reserved for roads, parks or unconverted agricultural use.</p>
<h2>2. Is the infrastructure built or only planned?</h2>
<p>Completed roads, power, water and drainage determine whether you can build and what the plot is really worth.</p>
<h2>3. Legal status of each plot</h2>
<p>Prefer plots with an individual title. For subdivided projects, check the approval decision and the infrastructure handover record.</p>
<h2>4. Liquidity</h2>
<p>Areas with existing residents, schools and markets are easier to resell than areas of empty land.</p>
<h2>5. Do not over-leverage</h2>
<p>Land often takes time to appreciate. Make sure you can hold it for several years without repayment pressure.</p>
<p><em>For reference only, not investment advice (demo data).</em></p>',
		),
	),
);
