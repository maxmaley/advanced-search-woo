(function ($, app) {
"use strict";
	$(document).ajaxComplete(function (event, request, settings) {
		if (
			typeof settings != 'undefined'
			&& typeof settings.data == 'string'
			&& settings.data.indexOf('action=add-menu-item') !== -1
			&& settings.data.indexOf('afsw_search_field_nav_item') !== -1
		) {
			correctAfswItems();
		}
	});
	$(document).ready(function () {
		correctAfswItems();
	});
	function correctAfswItems() {
		$('.afsw-search-description').each(function() {
			var block = $(this).closest('.menu-item-settings');
			if (block.length == 1) {
				block.find('p.description.description-wide:not(.afsw-search-description)').hide();
				block.find('.afsw-search-select').css({'width': '100%'});
			}
		});
	}
}(window.jQuery, window));
