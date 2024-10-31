/*!
 * @package Sectors
 * @author Joachim Jensen <jv@intox.dk>
 * @license GPLv3
 * @copyright 2018 by Joachim Jensen
 */

(function($) {

	var sct_settings = {

		current_section: 0,
		sections: [],

		init: function() {
			this.tabController();
			this.scheduleCalendar();
		},

		scheduleCalendar: function() {
			flatpickr.l10ns.default.weekdays = SCTAdmin.weekdays;
			flatpickr.l10ns.default.months = SCTAdmin.months;
			flatpickr.l10ns.default.firstDayOfWeek = SCTAdmin.weekStart;

			var activate = flatpickr('.js-sct-activation',{
				wrap: true,
				clickOpens: true,
				enableTime: true,
				time_24hr: true,
				allowInput: true,
				//enableSeconds: true,
				//altInput: true,
				//altFormat: SCTAdmin.dateFormat + ' @ H:i:S',
				onChange: function(dateObj, dateStr, instance) {
					console.log("activate");
					if(dateStr) {
						$toggle.prop('checked',false);
					}
				}
			}),
			$toggle = $('.js-sct-status');

			$toggle.on('change',function(e) {
				if($(this).is(':checked')) {
					activate.clear();
				}
			});
		},

		/**
		 * Initiate tabs dynamically
		 *
		 * @since  1.0
		 * @return {void}
		 */
		initTabSections: function() {
			$(".js-sct-tabs").find(".nav-tab").each(function() {
				var start = this.href.lastIndexOf("#");
				if(start >= 0) {
					var section = this.href.substr(start);
					sct_settings.sections.push(section);
					$(section).hide();
				}
			});
		},

		/**
		 * Manage tab clicks
		 *
		 * @since  1.0
		 * @return {void}
		 */
		tabController: function() {
			this.initTabSections();
			this.setCurrentSection(window.location.hash);
			$("#poststuff")
			.on("click",".js-nav-link",function(e) {
				sct_settings.setCurrentSection(this.href);
			});
		},

		/**
		 * Find section index based on
		 * hash in a URL string
		 *
		 * @since  1.0
		 * @param  {string} url
		 * @return {int}
		 */
		findSectionByURL: function(url) {
			var section = this.sections.indexOf(url.substring(url.lastIndexOf("#")));
			return section >= 0 ? section : null;
		},

		/**
		 * Set and display current section and tab
		 * hide previous current section
		 *
		 * @since 1.0
		 * @param {string} url
		 */
		setCurrentSection: function(url) {
			var section = this.findSectionByURL(url) || 0,
				$tabs = $(".js-sct-tabs").find(".nav-tab");
			if($tabs.eq(section).is(":visible")) {
				$(this.sections[this.current_section])
				.hide();
				//.find("input, select").attr("disabled",true);
				this.current_section = section;
				$(this.sections[this.current_section])
				.show();
				//.find("input, select").attr("disabled",false);

				$tabs.removeClass("nav-tab-active");
				$tabs.eq(this.current_section).addClass("nav-tab-active");
			}
		}
	};

	$(document).ready(function(){
		sct_settings.init();
	});

})(jQuery);