/* LandPro – chọn ảnh thư viện trong trang sửa bài. */
jQuery(function ($) {
	'use strict';

	function sync($wrap) {
		var ids = $wrap.find('.lp-gallery-list li').map(function () { return $(this).data('id'); }).get();
		$wrap.find('.lp-gallery-input').val(ids.join(','));
	}

	$('.lp-field--gallery').each(function () {
		var $wrap = $(this);
		$wrap.find('.lp-gallery-list').sortable({ update: function () { sync($wrap); } });
	});

	$(document).on('click', '.lp-gallery-add', function (e) {
		e.preventDefault();
		var $wrap = $(this).closest('.lp-field--gallery');
		var frame = wp.media({ title: 'Chọn ảnh', button: { text: 'Thêm vào thư viện ảnh' }, multiple: 'add', library: { type: 'image' } });
		frame.on('select', function () {
			frame.state().get('selection').each(function (att) {
				att = att.toJSON();
				if ($wrap.find('li[data-id="' + att.id + '"]').length) { return; }
				var url = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
				$wrap.find('.lp-gallery-list').append(
					$('<li>').attr('data-id', att.id)
						.append($('<img>').attr({ src: url, alt: '' }))
						.append('<button type="button" class="lp-gallery-remove" aria-label="Xóa ảnh">×</button>')
				);
			});
			sync($wrap);
		});
		frame.open();
	});

	$(document).on('click', '.lp-gallery-remove', function (e) {
		e.preventDefault();
		var $wrap = $(this).closest('.lp-field--gallery');
		$(this).closest('li').remove();
		sync($wrap);
	});
});
