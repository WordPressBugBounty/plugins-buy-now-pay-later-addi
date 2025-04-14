	jQuery(document).ready( function($) {

		const widgetPositionValue = $('select#woocommerce_addi_field_home_banner_position').val();
		
		$('label[for=woocommerce_addi_element_reference]').parent().parent().addClass('hidden');
		
		if (widgetPositionValue === 'custom') {        			
			$('label[for=woocommerce_addi_element_reference]').parent().parent().removeClass('hidden');
		}
		
		$('select#woocommerce_addi_field_home_banner_position').on('change', function() {
			const option = this.value;
			
			if(option === 'custom') {
				$('label[for=woocommerce_addi_element_reference]').parent().parent().removeClass('hidden');
			}
			else {
				$('label[for=woocommerce_addi_element_reference]').parent().parent().addClass('hidden');
			}
		});
		
		var billingField = getCookie("billingField");
		
		if(billingField && billingField !== null && billingField !== "null") {
			$('#woocommerce_addi_field_id').val(billingField);
			$('#woocommerce_addi_field_id').attr("disabled", "disabled");
			deleteCookie("billingField");
		} 

		var slug_value = $("#woocommerce_addi_widget_slug").val();

		if (slug_value === '' || slug_value === ' ') {
			$('#woocommerce_addi_widget_enabled').attr("disabled", "disabled");
			$('#woocommerce_addi_widget_enabled').removeAttr("checked");
		}
		else{
			$('#woocommerce_addi_widget_enabled').removeAttr("disabled");
		}

		$("#woocommerce_addi_widget_slug").keyup(function(e){

			if(e.target.value === '' || e.target.value === ' ') {
				$('#woocommerce_addi_widget_enabled').attr("disabled", "disabled");
				$('#woocommerce_addi_widget_enabled').removeAttr("checked");
			}
			else{
				$('#woocommerce_addi_widget_enabled').removeAttr("disabled");
			}
		});

		//Disable autocomplete
		var inputs = document.querySelectorAll('input.input-text');
		inputs.forEach(function(input) {
			input.setAttribute('autocomplete', 'off');
		});

		//Hide description in the options
		var textareas = document.querySelectorAll('textarea.description-hidden');
		textareas.forEach(function(textarea) {
			var tr = textarea.closest('tr[valign="top"]');
			
			if (tr) {
				tr.style.display = 'none';
			}
		});

		// Helper function to safely handle jQuery selections and DOM manipulations
		function safelyUpdateElement(selector, action, content) {
			try {
				const $element = $(selector);
				if ($element.length === 0) {
					console.warn(`Element not found: ${selector}`);
					return false;
				}
				switch (action) {
					case 'before':
						$element.before(content);
						break;
					case 'after':
						$element.after(content);
						break;
					default:
						console.warn(`Unknown action: ${action}`);
						return false;
				}
				return true;
			} catch (error) {
				console.error(`Error updating element ${selector}:`, error);
				return false;
			}
		}

		// Add logo
		safelyUpdateElement('label[for=woocommerce_addi_addi_logo]', 'before',
			'<div style="display: flex; align-items: center;">' +
			`<img src="${addiPlugin.url}assets/icon-128x128.png" alt="Addi" style="width: 70px;" />` +
			`<img src="${addiPlugin.url}assets/ADDI_logo.png" alt="Addi" style="width: 128px;" />` +
			'</div>'
		);

		// Add checkout example
		safelyUpdateElement('label[for=woocommerce_addi_description_checkout_page]', 'after',
			'<img class="description-image" src="https://s3.amazonaws.com/statics.addi.com/assets/woocommerce/checkout-description-options.png" alt="Addi" />' +
			'<br><div class="description-image">*En caso de querer activar la opcion de debito, Comunícate con el equipo de soporte de Addi para aliados al whatsapp: ' +
			'<a class="whatsapp-link" target="_blank" href="https://api.whatsapp.com/send?phone=5715806869">+57 1 580 6869</a></div>'
		);

		// Add banner image
		safelyUpdateElement('label[for=woocommerce_addi_section_home_banner]', 'after',
			`<br><img class="description-image" src="${addiPlugin.url}assets/banner-settings.png" alt="Addi" />`
		);

		// Add widget image
		safelyUpdateElement('label[for=woocommerce_addi_section_widget]', 'after',
			'<br><img src="' + addiPlugin.url + 'assets/widget.png" alt="Addi" style="width: 300px;" /> ' +
			'<br><br> <a href="https://s3.amazonaws.com/statics.addi.com/assets/manuals/guide-widget.png" target="_blank">Ver Ejemplo</a>'
		);

		// Add see more links
		const popupParams = 'width=730,height=755,top=100,left=100,resizable=no,scrollbars=no';
		
		safelyUpdateElement('#woocommerce_addi_section_widget_header', 'after',
			`<a href='https://s3.amazonaws.com/statics.addi.com/assets/manuals/guide-widget.png' ` +
			`onclick="window.open('https://s3.amazonaws.com/statics.addi.com/assets/manuals/guide-widget.png', 'popup', '${popupParams}'); return false;">Ver Ejemplo</a>`
		);

		safelyUpdateElement('#woocommerce_addi_widget_section_modal_header', 'after',
			`<a href='https://s3.amazonaws.com/statics.addi.com/assets/manuals/guide-modal.png' ` +
			`onclick="window.open('https://s3.amazonaws.com/statics.addi.com/assets/manuals/guide-modal.png', 'popup', '${popupParams}'); return false;">Ver Ejemplo</a>`
		);

		safelyUpdateElement('label[for=woocommerce_addi_field_home_banner_type]', 'after',
			`<br><a href='https://s3.amazonaws.com/statics.addi.com/addi-home-banner/addiHomeBannerOptions.png' ` +
			`onclick="window.open('https://s3.amazonaws.com/statics.addi.com/addi-home-banner/addiHomeBannerOptions.png', 'popup', '${popupParams}'); return false;">Ver Ejemplo</a>`
		);

		/* widget style*/
		safelyUpdateElement('#woocommerce_addi_widgetBorderColor', 'after', "<span class='customCircle addiWidget step1'>1</span>");
		safelyUpdateElement('#woocommerce_addi_widgetBorderRadius', 'after', "<span class='customCircle addiWidget step2'>2</span>");
		safelyUpdateElement('#woocommerce_addi_widgetFontColor', 'after', "<span class='customCircle addiWidget step3'>A</span>");
		safelyUpdateElement('#woocommerce_addi_widgetFontFamily', 'after', "<span class='customCircle addiWidget step4'>B</span>");
		safelyUpdateElement('#woocommerce_addi_widgetFontSize', 'after', "<span class='customCircle addiWidget step5'>C</span>");
		safelyUpdateElement('#woocommerce_addi_widgetBadgeBackgroundColor', 'after', "<span class='customCircle addiWidget step6'>4</span>");
		safelyUpdateElement('#woocommerce_addi_widgetInfoBackgroundColor', 'after', "<span class='customCircle addiWidget step7'>5</span>");
		safelyUpdateElement('#woocommerce_addi_widgetMargin', 'after', "<span class='customCircle addiWidget step8'>6</span>");
		safelyUpdateElement('#woocommerce_addi_modalBadgeLogoStyle', 'after', "<span class='customCircle addiWidget step9'>7</span>");
		
		/* modal style*/
		safelyUpdateElement('#woocommerce_addi_modalBackgroundColor', 'after', "<span class='customCircle addiWidgetModal step1'>1</span>");
		safelyUpdateElement('#woocommerce_addi_modalFontColor', 'after', "<span class='customCircle addiWidgetModal step2'>A</span>");
		safelyUpdateElement('#woocommerce_addi_modalPriceColor', 'after', "<span class='customCircle addiWidgetModal step3'>3</span>");
		safelyUpdateElement('#woocommerce_addi_modalBadgeBackgroundColor', 'after', "<span class='customCircle addiWidgetModal step4'>A</span>");
		safelyUpdateElement('#woocommerce_addi_modalBadgeBorderRadius', 'after', "<span class='customCircle addiWidgetModal step5'>B</span>");
		safelyUpdateElement('#woocommerce_addi_modalBadgeFontColor', 'after', "<span class='customCircle addiWidgetModal step6'>C</span>");
		safelyUpdateElement('#woocommerce_addi_modalCardColor', 'after', "<span class='customCircle addiWidgetModal step7'>5</span>");
		safelyUpdateElement('#woocommerce_addi_modalButtonBorderColor', 'after', "<span class='customCircle addiWidgetModal step8'>A</span>");
		safelyUpdateElement('#woocommerce_addi_modalButtonBorderRadius', 'after', "<span class='customCircle addiWidgetModal step9'>B</span>");
		safelyUpdateElement('#woocommerce_addi_modalButtonBackgroundColor', 'after', "<span class='customCircle addiWidgetModal step10'>C</span>");
		safelyUpdateElement('#woocommerce_addi_modalButtonFontColor', 'after', "<span class='customCircle addiWidgetModal step11'>D</span>");
		

		/*Functions for handle cookies*/
		function getCookie(cname) {
		let name = cname + "=";
		let decodedCookie = decodeURIComponent(document.cookie);
		let ca = decodedCookie.split(';');
		for(let i = 0; i <ca.length; i++) {
			let c = ca[i];
			while (c.charAt(0) == ' ') {
			c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
			}
		}
		return "";
		}
		
		function setCookie(cname, cvalue, exdays) {
		const d = new Date();
		d.setTime(d.getTime() + (exdays*24*60*60*1000));
		let expires = "expires="+ d.toUTCString();
		document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
		}
		
		function deleteCookie(name) {
		document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
		}
	});