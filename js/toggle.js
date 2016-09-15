// JSLint Comments
/*global $:false, document */

$(function () {
	
	"use strict";
	
	var dropdown_button = $('.dropdown button'),
		dropdown_menu = $('.dropdown-menu'),
		hitbox = $('.dropdown-menu-hitbox');
	
	dropdown_menu.addClass("js");
	
	dropdown_button.click(function () {
		dropdown_menu.toggleClass("nav-visible");
	});
	
	dropdown_menu.hover(function () {
		$(this).addClass("nav-hovered");
	}, function () {
		$(this).removeClass("nav-hovered");
	});
	
	dropdown_menu.find('a').blur(function () {
		dropdown_menu.removeClass("nav-focused");
	});
	
	dropdown_menu.find('a').focus(function () {
		dropdown_menu.addClass("nav-focused");
	});
	
	hitbox.click(function () {
		dropdown_menu.removeClass("nav-visible");
	});
	
});