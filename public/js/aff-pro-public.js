(function( $ ) {
	'use strict';
	!function(e){var n;if("function"==typeof define&&define.amd&&(define(e),n=!0),"object"==typeof exports&&(module.exports=e(),n=!0),!n){var t=window.Cookies,o=window.Cookies=e();o.noConflict=function(){return window.Cookies=t,o}}}(function(){function f(){for(var e=0,n={};e<arguments.length;e++){var t=arguments[e];for(var o in t)n[o]=t[o]}return n}function a(e){return e.replace(/(%[0-9A-Z]{2})+/g,decodeURIComponent)}return function e(u){function c(){}function t(e,n,t){if("undefined"!=typeof document){"number"==typeof(t=f({path:"/"},c.defaults,t)).expires&&(t.expires=new Date(1*new Date+864e5*t.expires)),t.expires=t.expires?t.expires.toUTCString():"";try{var o=JSON.stringify(n);/^[\{\[]/.test(o)&&(n=o)}catch(e){}n=u.write?u.write(n,e):encodeURIComponent(String(n)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g,decodeURIComponent),e=encodeURIComponent(String(e)).replace(/%(23|24|26|2B|5E|60|7C)/g,decodeURIComponent).replace(/[\(\)]/g,escape);var r="";for(var i in t)t[i]&&(r+="; "+i,!0!==t[i]&&(r+="="+t[i].split(";")[0]));return document.cookie=e+"="+n+r}}function n(e,n){if("undefined"!=typeof document){for(var t={},o=document.cookie?document.cookie.split("; "):[],r=0;r<o.length;r++){var i=o[r].split("="),c=i.slice(1).join("=");n||'"'!==c.charAt(0)||(c=c.slice(1,-1));try{var f=a(i[0]);if(c=(u.read||u)(c,f)||a(c),n)try{c=JSON.parse(c)}catch(e){}if(t[f]=c,e===f)break}catch(e){}}return e?t[e]:t}}return c.set=t,c.get=function(e){return n(e,!1)},c.getJSON=function(e){return n(e,!0)},c.remove=function(e,n){t(e,"",f(n,{expires:-1}))},c.defaults={},c.withConverter=e,c}(function(){})});
	jQuery(document).ready(function ($) {
	
	function aff_copyToClipboard(str){
		const el = document.createElement('textarea');
		el.value = str;
		el.setAttribute('readonly', '');
		el.style.position = 'absolute';
		el.style.left = '-9999px';
		document.body.appendChild(el);
		el.select();
		document.execCommand('copy');
		document.body.removeChild(el);
	}

	function NOTIFY(msg = '',type= 'success') { Swal.fire( msg, '', type ); }

	const App = {
		ref_field: 'ref',
		ref_coupon: 'mhcoupon',
		settings: $('#aff_settings').data('settings'),
		getUrlPath: function()
		{
			return location.protocol + '//' + location.host + location.pathname;
		},
		getParam: function(key)
		{
			var url_string = window.location.href;
			var url = new URL(url_string);
			return url.searchParams.get(key);
		},
		setRefIdCookie: function()
		{
			const mhref = this.getParam(this.ref_field);
			if(mhref)
			{
				const cookie_time = parseInt(this.settings.time);
				let product_id = 0;
				if($('.single_add_to_cart_button').length > 0)
					product_id = $('.single_add_to_cart_button').val();
				if($('[name=product_id]').length > 0)
					product_id = $('[name=product_id]').val();

				if(product_id > 0 || true)
				{
					const ref_info = {};
					ref_info.mhref = this.getParam(this.ref_field);
					ref_info.mhcoupon = this.getParam(this.ref_coupon) ? this.getParam(this.ref_coupon) : '';
					ref_info.mhproduct = product_id;
					ref_info.mhpath = this.getUrlPath();

					Cookies.set(this.ref_field, JSON.stringify(ref_info) , { expires: cookie_time });
				}
				
			}
		},
		setRefFieldInCheckOut: function()
		{
			const ref_field = $('#ref_id');
			const ref_path = $('#ref_path');
			const ref_coupon = $('#ref_coupon');
			const ref_product = $('#ref_product');
			if(ref_field.length > 0)
			{
				let mhref =  Cookies.get(this.ref_field);
				if(mhref)
				{
					mhref = JSON.parse(mhref);
					if(mhref){
						ref_field.val(mhref.mhref);
						ref_path.val(mhref.mhpath)
						ref_product.val(mhref.mhproduct)
						// ref_coupon.val(mhref.mhcoupon)
					}
				}
			}
		},
		removeCookieOnThankyouPage: function()
		{
			
			const thankyou = $('.woocommerce-order-received');
			if(thankyou.length > 0 && this.settings.once === 'true')
			{
				Cookies.remove(this.ref_field);
			}	
		},
		copyAffLink: function(){
			$(document).on('click', '.aff-copy-link', function(){
				const href = $(this).attr('href');
				aff_copyToClipboard(href);
				Swal.fire({
					title: 'Chia sẻ đường dẫn này',
					text: href,
					icon: 'success',
					confirmButtonText: 'Sao chép'
				});
				return false;
			})
		},
		setTraffic(){
			if(this.settings.cookie_traffic_mode == 'true'){
				let cookie = Cookies.get(this.ref_field);
				if(!cookie)
					return;
				if(!$('.post-type-archive-product').length && !$('.single-product').length)
					return;
				
				cookie = JSON.parse(cookie)
				$.ajax({
					type: "POST",
					url: this.settings.ajax_url,
					data: {
						action: 'aff_set_traffic',
						user_login: cookie.mhref,
						path: this.getUrlPath()
					},
					dataType: "json",
					success: function (res) {
						console.log(res);
					}
				});
					// NOTIFY('OK')
			}
			
		},
		init(){

			this.ref_field = this.settings.ref_name

			this.setRefIdCookie();
			this.setRefFieldInCheckOut();
			this.removeCookieOnThankyouPage();
			this.copyAffLink();
			this.setTraffic();
		}
	}
	App.init();
	});

})( jQuery );
